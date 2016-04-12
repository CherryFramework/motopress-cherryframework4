<?php
/**
 * Plugin Name: MotoPress and CherryFramework 4 Integration
 * Plugin URI: http://www.getmotopress.com/
 * Description: Extend MotoPress Content Editor plugin with CherryFramework 4 shortcodes.
 * Version: 1.1.6.2
 * Author: MotoPress & Cherry Team
 * Author URI: http://www.getmotopress.com/
 * License: GPL2 or later
 * Text Domain: motopress-cherryframework4
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class MPCE_Cherry4 {

	private static $instance;

	private $prefix;
	private $grid_shortcodes;
	private $skip_shortcodes;

	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof MPCE_Cherry4 ) ) {
			self::$instance = new MPCE_Cherry4;
		}
		return self::$instance;
	}

	function __construct() {

		$this->grid_shortcodes = array('row', 'row_inner', 'col', 'col_inner');
		$this->skip_shortcodes = array('tab', 'spoiler', 'clear', 'box_inner');

		add_action( 'mp_library', array( $this, 'mpce_cherry4_library_extend' ), 11, 1);

		add_action( 'plugins_loaded', array( $this, 'constants' ), 1 );
		add_action( 'plugins_loaded', array( $this, 'lang' ), 2 );
		add_action( 'plugins_loaded', array( $this, 'mpce_cherry4_plugins_loaded' ), 11 );
		add_action( 'plugins_loaded', array( $this, 'admin' ), 11 );

		//add_action('motopress_render_shortcode', array( $this, 'mpce_cherry4_shortcode_atts' ));
		add_action( 'init', array( $this, 'mpce_cherry4_shortcode_atts' ), 11);

		add_filter( 'cherry_shortcodes_output_row_class', array( $this, 'mpce_cherry4_row_class' ) );
		add_filter( 'cherry_shortcodes_output_row_inner_class', array( $this, 'mpce_cherry4_row_class' ) );

		add_action( 'after_setup_theme', array( $this, 'custom_cherry_shortcodes' ), 99 );

		if ( isset( $_GET['motopress-ce'] ) && $_GET['motopress-ce'] === '1' ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'mpce_cherry4_scripts' ) );
			add_filter( 'cherry_shortcodes_use_generated_style', '__return_false' );
		} else {
			add_action( 'wp_print_styles', array( $this, 'mpce_cherry4_wp_print_styles' ) );
		}

	}

	public function constants() {
		define( 'MOTO_CHERRY4_VERSION', '1.1.6.2' );
		define( 'MOTO_CHERRY4_SLUG', basename( dirname( __FILE__ ) ) );
	}

	/**
	 * Loads the translation files.
	 *
	 * @since 1.1.7
	 */
	public function lang() {
		load_plugin_textdomain( 'motopress-cherryframework4', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	public function mpce_cherry4_wp_print_styles() {
		wp_dequeue_style( 'mpce-bootstrap-grid' );
	}

	public function mpce_cherry4_row_class($class) {
		return $class . ' row-edge';
	}

	public function mpce_cherry4_plugins_loaded() {
		if (defined('CHERRY_SHORTCODES_PREFIX'))
			$this->prefix = CHERRY_SHORTCODES_PREFIX;
	}

	public function mpce_cherry4_library_extend($motopressCELibrary) {

		if ( !class_exists('Cherry_Shortcodes_Data') ) return;

		if (defined('CHERRY_TEMPLATER_DIR'))
			require_once( CHERRY_TEMPLATER_DIR . 'admin/class-cherry-shortcode-editor.php' );

		$shortcodes = Cherry_Shortcodes_Data::shortcodes();

		$cherry_row = $shortcodes['row'];
		$cherry_row_inner = $shortcodes['row_inner'];
		$cherry_column = $shortcodes['col'];
		$cherry_column_inner = $shortcodes['col_inner'];

		$rowObj = new MPCEObject($this->prefix . 'row', $cherry_row['name'], null, $this->cherry_attributes_to_parameters($cherry_row['atts']), null, MPCEObject::ENCLOSED, MPCEObject::RESIZE_NONE);
		$rowObj->addStyle(array(
			'mp_style_classes' => array(
				'basic' => array(
					'class' => 'motopress-row',
					'label' => $cherry_row['name']
				)
			)
		));

		$rowInnerObj = new MPCEObject($this->prefix . 'row_inner', $cherry_row_inner['name'], null, $this->cherry_attributes_to_parameters($cherry_row_inner['atts']), null, MPCEObject::ENCLOSED, MPCEObject::RESIZE_NONE);
		$rowInnerObj->addStyle(array(
			'mp_style_classes' => array(
				'basic' => array(
					'class' => 'motopress-row',
					'label' => $cherry_row_inner['name']
				)
			)
		));

		$spanObj = new MPCEObject($this->prefix . 'col', $cherry_column['name'], null, $this->cherry_attributes_to_parameters($cherry_column['atts']), null, MPCEObject::ENCLOSED, MPCEObject::RESIZE_NONE, false);
		$spanObj->addStyle(array(
			'mp_style_classes' => array(
				'basic' => array(
					'class' => 'motopress-clmn',
					'label' => $cherry_column['name']
				)
			)
		));

		$spanInnerObj = new MPCEObject($this->prefix . 'col_inner', $cherry_column_inner['name'], null, $this->cherry_attributes_to_parameters($cherry_column_inner['atts']), null, MPCEObject::ENCLOSED, MPCEObject::RESIZE_NONE);
		$spanInnerObj->addStyle(array(
			'mp_style_classes' => array(
				'basic' => array(
					'class' => 'motopress-clmn',
					'label' => $cherry_column_inner['name']
				)
			)
		));

		$mpGridGroup = $motopressCELibrary->getGroup('mp_grid');
		$mpGridGroup->removeObject('mp_row');
		$mpGridGroup->removeObject('mp_row_inner');
		$mpGridGroup->removeObject('mp_span');
		$mpGridGroup->removeObject('mp_span_inner');
		$mpGridGroup->addObject(array($rowObj, $rowInnerObj, $spanObj, $spanInnerObj));

		$motopressCELibrary->setGrid(array(
			'row' => array(
				'shortcode' => $this->prefix . 'row',
				'inner' => $this->prefix . 'row_inner',
				'class' => 'row',
				'edgeclass' => 'row-edge',
				'col' => '12'
			),
			'span' => array(
				'type' => 'single',
				'shortcode' => $this->prefix . 'col',
				'inner' => $this->prefix . 'col_inner',
				'class' => 'col-md-',
				'attr' => 'size_md',
				'custom_class_attr' => 'class'
			)
		));

		//remove all objects
		/*$motopressCELibrary->getObject('mp_code')->setShow(false);
		$motopressCELibrary->getObject('mp_text')->setShow(false);
		$motopressCELibrary->removeObject('mp_heading');*/
		//$motopressCELibrary->removeObject('mp_image');
		//$motopressCELibrary->removeObject('mp_grid_gallery');
		//$motopressCELibrary->removeObject('mp_image_slider');
		//$motopressCELibrary->removeObject('mp_video');
		$motopressCELibrary->removeObject('mp_space');
		$motopressCELibrary->removeObject('mp_button');
		$motopressCELibrary->removeObject('mp_gmap');
		//$motopressCELibrary->removeObject('mp_embed');
		//$motopressCELibrary->removeObject('mp_quote');
		//$motopressCELibrary->removeObject('mp_members_content');
		$motopressCELibrary->removeObject('mp_social_buttons');
		$motopressCELibrary->removeObject('mp_social_profile');
		$motopressCELibrary->removeObject('mp_google_chart');
		//$motopressCELibrary->removeObject('mp_wp_audio');
		$motopressCELibrary->removeObject('mp_tabs');
		$motopressCELibrary->removeObject('mp_accordion');
		//$motopressCELibrary->removeObject('mp_table');
		$motopressCELibrary->removeObject('mp_posts_grid');

		$motopressCELibrary->removeObject('mp_wp_archives');
		$motopressCELibrary->removeObject('mp_wp_calendar');
		$motopressCELibrary->removeObject('mp_wp_categories');
		$motopressCELibrary->removeObject('mp_wp_navmenu');
		$motopressCELibrary->removeObject('mp_wp_meta');
		$motopressCELibrary->removeObject('mp_wp_pages');
		$motopressCELibrary->removeObject('mp_wp_posts');
		$motopressCELibrary->removeObject('mp_wp_comments');
		$motopressCELibrary->removeObject('mp_wp_rss');
		$motopressCELibrary->removeObject('mp_wp_search');
		$motopressCELibrary->removeObject('mp_wp_tagcloud');
		$motopressCELibrary->removeObject('mp_wp_widgets_area');

		// register shortcodes
		foreach ($shortcodes as $id => $shortcode) {

			if ( in_array($id, $this->grid_shortcodes) || in_array($id, $this->skip_shortcodes))
				continue;

			$label = $shortcode['name'];
			$icon = $this->get_icon($id);
			$shortcode_id = $this->prefix . $id;
			$closeType = ($shortcode['type'] != 'single') ? MPCEObject::ENCLOSED : MPCEObject::SELF_CLOSED;

			$params = $this->cherry_attributes_to_parameters($shortcode['atts'], null, $id);

			if ( $shortcode['type'] != 'single' ) {
				$shortcode_content = isset($shortcode['content']) ? $shortcode['content'] : '';
				$shortcode_content = str_replace( array( '%prefix_', '__' ), $this->prefix, $shortcode_content );

				$params['content'] = array(
					'type' => 'longtext-tinymce',
					'label' => __( 'Content', 'motopress-cherryframework4' ),
					'text' => empty($motopressCELang) ? 'Open in WordPress Editor' : $motopressCELang->CEOpenInWPEditor,
					'default' => $shortcode_content,
					'saveInContent' => 'true'
				);

				// custom templates
				/*if ($id == 'testimonials') {*/
				/*require_once( CHERRY_TEMPLATER_DIR . 'admin/class-cherry-shortcode-editor.php' );
				$templates = Cherry_Shortcode_Editor::dirlist( $id );
				if ($templates) {
					var_export($id);
					var_export($templates);
				}*/
				/*exit;
			}*/
			}

			$mpObject = new MPCEObject($shortcode_id, $label, $icon, $params, 0, $closeType);

			$group = 'mp_other';
			if ( isset($shortcode['group']) ) {
				switch ($shortcode['group']) {
					case 'typography': {
						$group = 'text';
						break;
					}
					case 'media': {
						$group = 'image';
						break;
					}
				}
			}
			switch ($id) {
				case 'button':
				case 'sharing':
				case 'follow':
				{
					$group = 'button';
					break;
				}
			}

			/*if  ($id == 'button') {
				echo "<pre>";
				var_export($mpObject);
				exit;
			}*/

			$motopressCELibrary->addObject($mpObject, $group);
		}

		// register tabs
		/*$tabs_parameters = array(
			'elements' => array(
				'type' => 'group',
				'contains' => $this->prefix . 'tab',
				'items' => array(
					'label' => array(
						'default' => 'Tab',
						'parameter' => 'title'
					),
					'count' => 2
				),
				'text' => 'Add New Tab',
				'disabled' => 'false',
				'rules' => array(
					'rootSelector' => '.cherry-tabs-nav > span',
					'activeSelector' => '',
					'activeClass' => 'cherry-tabs-current'
				),
				/*'events' => array(
					'onActive' => array(
						'selector' => '> span',
						'event' => 'click'
					),
					'onInactive' => array(
						'selector' => '> span',
						'event' => 'click'
					)
				)*/
		/*)
	);
	$tabs_parameters = $this->cherry_attributes_to_parameters($shortcodes['tabs']['atts'], $tabs_parameters);
	$tabsObj = new MPCEObject($this->prefix . 'tabs', 'Tabs', null, $tabs_parameters, 11, MPCEObject::ENCLOSED);

	$tab_parameters = array(
		'content' => array(
			'type' => 'longtext-tinymce',
			'label' => __( 'Content', 'cherry-shortcodes' ),
			'text' => empty($motopressCELang) ? 'Open in WordPress Editor' : $motopressCELang->CEOpenInWPEditor,
			'default' => 'Tab Content',
			'saveInContent' => 'true'
		)
	);
	$tab_parameters = $this->cherry_attributes_to_parameters($shortcodes['tab']['atts'], $tab_parameters);
	$tabItemObj = new MPCEObject($this->prefix . 'tab', 'Tab', null, $tab_parameters, null, MPCEObject::ENCLOSED, MPCEObject::RESIZE_NONE, false);

	/*echo "<pre>";
	var_export($tabItemObj);
	exit;*/

		/*$motopressCELibrary->addObject($tabsObj);
		$motopressCELibrary->addObject($tabItemObj);*/

		// classes example
		/*$buttonObj = &$motopressCELibrary->getObject($this->prefix . 'button');
		$styleClasses = &$buttonObj->getStyle('mp_style_classes');

		//add 'lavender' predefined class
		$styleClasses['predefined']['color']['values']['lavender'] = array(
			'class' => 'motopress-button-color-lavender',
			'label' => __('Lavender', 'domain'),
		);

		$styleClasses['default'] = '';

		// change basic class
		$styleClasses['basic'] = array(
			'class' => 'motopress-button',
			'label' => __('Button', 'domain'),
		);*/

		//remove predefined templates
		$page_templates = array('landing_page', 'call_to_action_page', 'feature_list', 'description_page', 'service_list', 'product_page');
		foreach ($page_templates as $page_template)
			$motopressCELibrary->removeTemplate( MPCEShortcode::PREFIX . $page_template );

		//add cherry-predefined templates
		require_once ( plugin_dir_path( __FILE__ ) . '/inc/ce/custom-templates.php' );

	}

	public function mpce_cherry4_shortcode_atts() {

		if ( !class_exists('Cherry_Shortcodes_Data') || !class_exists('MPCEShortcode')) return;

		$shortcodes = Cherry_Shortcodes_Data::shortcodes();

		foreach ($shortcodes as $id => $data) {
			$shortcode_atts_filter = new MotoPress_Cherry4_Shortcode_Atts_Filter($id);
			add_filter( 'shortcode_atts_'  . $id, array($shortcode_atts_filter, 'shortcode_atts_common_filter'), 10, 3);
		}
	}

	public function cherry_attributes_to_parameters($atts, $params = null, $shortcode = '') {

		if ( $params == NULL )
			$params = array();

		if (is_array($atts) && count($atts)) {
			foreach ($atts as $att_id => $att) {

				$param = array();
				$type = isset($att['type']) ? $att['type'] : '';

				switch ($type) {
					case 'select': {
						$param['type']        = (isset($att['multiple']) && $att['multiple'] == TRUE) ? 'select-multiple' : 'select';
						$param['label']       = $att['name'];
						$param['default']     = $att['default'];
						$param['list']        = $att['values'];
						$param['description'] = empty( $att['desc'] ) ? '' : $att['desc'];
						break;
					}
					case 'responsive': {
						$responsive_sizes = array('_xs', '_sm', '_md', '_lg');
						foreach ($responsive_sizes as $size) {

							// skip size_md while we set it visually
							if ( ($att_id . $size) == 'size_md') continue;

							$params[$att_id . $size] = array(
								'type' => 'text',
								'label' => $att['name'] . $size,
								'default' => 'none',
								'description' =>  $this->responsive_size_to_label($size),
							);
						}
						break;
					}
					case 'color': {
						$param['type']        = 'color-picker';
						$param['label']       = $att['name'];
						$param['description'] = empty( $att['desc'] ) ? '' : $att['desc'];
						//$param['default'] = $att['default'];
						break;
					}
					case 'slider': {
						$param['type']        = 'slider';
						$param['label']       = $att['name'];
						$param['description'] = empty( $att['desc'] ) ? '' : $att['desc'];
						$param['default']     = $att['default'];
						$param['min']         = $att['min'];
						$param['max']         = $att['max'];
						break;
					}
					case 'number': {
						$param['type']        = 'spinner';
						$param['label']       = $att['name'];
						$param['description'] = empty( $att['desc'] ) ? '' : $att['desc'];
						$param['default']     = $att['default'];
						$param['min']         = $att['min'];
						$param['max']         = $att['max'];
						break;
					}
					case 'bool': {
						$param['type']        = 'radio-buttons';
						$param['label']       = $att['name'];
						$param['description'] = empty( $att['desc'] ) ? '' : $att['desc'];
						$param['default']     = $att['default'];
						$param['list']        = array(
							'yes' => __('Yes'),
							'no'  => __('No'),
						);
						break;
					}
					case 'upload': {
						$param['type']        = 'media';
						$param['label']       = $att['name'];
						$param['description'] = empty( $att['desc'] ) ? '' : $att['desc'];
						$param['default']     = $att['default'];
						break;
					}
					case 'icon': {
						$param['type']        = 'icon-picker';
						$param['label']       = $att['name'];
						$param['description'] = empty( $att['desc'] ) ? '' : $att['desc'];
						$param['default']     = '';

						//delete_transient('mpce-cherry-icons-list');
						$icons_list = get_transient('mpce-cherry-icons-list');

						if ($icons_list == FALSE) {

							$icons = Cherry_Shortcodes_Data::icons();
							$icons_list = array(
								'none' => array(
									'class' => 'fa',
									'label' => ''
								)
							);
							foreach($icons as $icon) {
								$icons_list['icon:' . $icon] =
									array(
										'label' => str_replace('fa fa-', '', $icon),
										'class' => $icon
									);
							}
							//asort($icons_list);

							$expiration = 60 * 60; // one hour
							set_transient('mpce-cherry-icons-list', $icons_list, $expiration);
						}

						$param['list'] = $icons_list;
						/*echo "<pre>";
						var_export($param); exit;*/
						break;
					}
					default: {
					$param['type'] = 'text';
					if ( $att_id == 'url' )
						$param['type'] = 'link';
					$param['label']       = $att['name'];
					$param['default']     = $att['default'];
					$param['description'] = empty( $att['desc'] ) ? '' : $att['desc'];
					}
				}

				// Cherry Shortcodes Templater
				if ( $att_id == 'template' && class_exists('Cherry_Shortcode_Editor') && strlen($shortcode) ) {
					$templates = Cherry_Shortcode_Editor::dirlist( $shortcode );
					if ( $templates && !empty( $templates ) ) {
						$param['list'] = $templates;
					}
				}

				// Fill group list of team shortcode
				if ( $shortcode == 'team' && ( $att_id == 'group' ) ) {

					$terms = get_terms( 'group' );
					if ( ! is_wp_error( $terms ) ) {
						$param['list'] = wp_list_pluck( $terms, 'name', 'slug' );
					}
				}

				// Fill services list of categories shortcode
				if ( $shortcode == 'services' && ( $att_id == 'categories' ) ) {

					$terms = get_terms( CHERRY_SERVICES_NAME . '_category' );
					if ( ! is_wp_error( $terms ) ) {
						$param['list'] = wp_list_pluck( $terms, 'name', 'slug' );
					}
				}

				// Fill list of categories for `[cherry_blog]` shortcode
				if ( $shortcode == 'blog' && ( $att_id == 'category' ) ) {

					$terms = get_terms( 'category' );
					if ( ! is_wp_error( $terms ) ) {
						$param['list'] = wp_list_pluck( $terms, 'name', 'slug' );
					}
				}

				// Fill list of categories for `[cherry_testimonials]` shortcode
				if ( $shortcode == 'testimonials' && ( $att_id == 'category' ) ) {

					$terms = get_terms( CHERRY_TESTI_NAME . '_category' );
					if ( ! is_wp_error( $terms ) ) {
						$param['list'] = wp_list_pluck( $terms, 'name', 'slug' );
					}
				}

				if ( count( $param ) ) {
					$params[ $att_id ] = $param;
				}
			}
		}

		return $params;
	}

	private function responsive_size_to_label($size) {
		$size_label = $size;
		switch ($size) {
			case '_xs': {
				$size_label = __( 'Extra small devices (Phones)', 'motopress-cherryframework4' );
				break;
			}
			case '_sm': {
				$size_label = __( 'Small devices (Tablets)', 'motopress-cherryframework4' );
				break;
			}
			case '_md': {
				$size_label = __( 'Medium devices (Desktops)', 'motopress-cherryframework4' );
				break;
			}
			case '_lg': {
				$size_label = __( 'Large devices (Desktops)', 'motopress-cherryframework4' );
				break;
			}
		}
		return $size_label;
	}

	public function mpce_cherry4_scripts() {
		wp_register_script( 'mpce-cherry-controller', plugins_url('assets/js/controller.js', __FILE__), array('jquery'), '1.0', true);
		wp_localize_script( 'mpce-cherry-controller', 'mpce_cherry4_prefix', $this->prefix );
		wp_enqueue_script( 'mpce-cherry-controller');

		//assets
		cherry_query_asset( 'js', 'cherry-shortcodes-init' );
		cherry_query_asset( 'js', array( 'swiper', 'cherry-shortcodes-init' ) );
		cherry_query_asset( 'js', 'cherry-shortcodes' );
		cherry_query_asset( 'js', array( 'cherry-google-map', 'cherry-shortcodes-init' ) );
		cherry_query_asset( 'js', 'cherry-parallax' );
		cherry_query_asset( 'js', array( 'jquery-counterup', 'cherry-shortcodes-init' ) );
		cherry_query_asset( 'js', 'cherry-lazy-load-effect' );
		cherry_query_asset( 'css', 'font-awesome' );
	}

	private function get_icon($shortcode) {

		if ( file_exists( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . $shortcode . '.png' ) ) {
			return ltrim( (str_replace( content_url(), '' , plugin_dir_url( __FILE__ ) ) . 'assets/' . $shortcode . '.png'), '/');
		}

		return NULL;
	}

	public function admin() {

		if ( is_admin() ) {
			require_once( plugin_dir_path( __FILE__ ) . '/admin/includes/class-cherry-update/class-cherry-plugin-update.php' );

			$Cherry_Plugin_Update = new Cherry_Plugin_Update();
			$Cherry_Plugin_Update->init( array(
				'version'         => MOTO_CHERRY4_VERSION,
				'slug'            => MOTO_CHERRY4_SLUG,
				'repository_name' => MOTO_CHERRY4_SLUG,
			));
		}
	}

	public function custom_cherry_shortcodes() {
		$pre = apply_filters( 'custom_cherry4_shortcodes', false );

		if ( false === $pre ) {
			return;
		}

		require_once( plugin_dir_path( __FILE__ ) . '/inc/custom-cherry-shortcodes.php' );
	}

}

class MotoPress_Cherry4_Shortcode_Atts_Filter {

	private $shortcode;
	private $prefix;

	function __construct($shortcode)
	{
		$this->shortcode = $shortcode;
		$this->prefix = CHERRY_SHORTCODES_PREFIX;
	}

	public function shortcode_atts_common_filter($out, $pairs, $atts)
	{

		$basicClasses = trim( MPCEShortcode::getBasicClasses($this->prefix . $this->shortcode) );
		if ( !$this->isContentEditor() && in_array( $this->shortcode, array('row', 'row_inner', 'col', 'col_inner') )
			&& !(isset($_POST['action']) && ( $_POST['action'] == 'motopress_ce_render_template' ))) {

			$basicClasses = '';
		}

		$mpClasses =
			((isset($atts['margin'])) ? (trim( MPCEShortcode::getMarginClasses($atts['margin']) )) : '') . ' ' .
			$basicClasses . ' ' .
			((isset($atts['mp_style_classes'])) ? (trim( $atts['mp_style_classes'] )) : '');

		if (method_exists('MPCEShortcode', 'handleCustomStyles')) {
			$mpClasses .= MPCEShortcode::handleCustomStyles( ( isset($atts['mp_custom_style']) ? $atts['mp_custom_style'] : '' ), $this->prefix . $this->shortcode, true );
		}

		$out['class'] = isset($out['class']) ? ($out['class'] . ' ' . trim($mpClasses)) : trim($mpClasses);

		return $out;
	}

	private function isContentEditor() {
		global $isMotoPressCEPage;
		if (
			(isset($isMotoPressCEPage) && $isMotoPressCEPage === TRUE) ||
			(isset($_GET['motopress-ce']) && $_GET['motopress-ce'] === '1') ||
			(isset($_POST['action']) && ( $_POST['action'] == 'motopress_ce_render_shortcode' ))
		) {
			return true;
		}
		return false;
	}
}


function MPCECherry4() {
	return MPCE_Cherry4::instance();
}
MPCECherry4();