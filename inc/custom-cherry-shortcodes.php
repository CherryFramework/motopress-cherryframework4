<?php

if ( ! class_exists( 'Custom_Cherry_Shortcodes' ) ) {

	class Custom_Cherry_Shortcodes {

		/**
		 * A reference to an instance of this class.
		 */
		private static $instance = null;

		function __construct() {

			if ( ! class_exists( 'Cherry_Shortcodes_Handler' ) ) {
				return;
			}

			add_action( 'admin_head',   array( $this, 'admin_register_assets' ) );
			add_action( 'admin_footer', array( $this, 'admin_enqueue_assets' ) );
			add_action( 'wp_ajax_custom_shortcodes_generator_get_bg_type', array( $this, 'ajax_get_bg_type' ) );
			add_filter( 'cherry_shortcodes/data/shortcodes', array( $this, 'data' ), 99, 1 );
			add_action( 'mp_library', array( $this, 'mpce_custom_cherry_shortcodes_library_extend' ), 12, 1 );
		}

		/**
		 * Register admin-specific javascript.
		 */
		public function admin_register_assets() {
			wp_register_script( 'custom-cherry-shortcodes-generator', plugins_url( MOTO_CHERRY4_SLUG . '/assets/js/scripts.js' ), array( 'cherry-shortcodes-generator' ), MOTO_CHERRY4_VERSION, true );
		}

		/**
		 * Enqueue admin-specific javascript.
		 */
		public function admin_enqueue_assets() {
			wp_enqueue_script( 'custom-cherry-shortcodes-generator' );
		}

		/**
		 * Rewrite callback-function attribute
		 * for [row], [row_inner], [col], [col_inner] shortcodes.
		 */
		public function data( $shortcodes ) {

			// [row], [row_inner]
			if ( ! empty( $shortcodes['row'] ) ) {
				$shortcodes['row']['function'] = array( $this, 'row' );
			}

			if ( ! empty( $shortcodes['row_inner'] ) ) {
				$shortcodes['row_inner']['function'] = array( $this, 'row' );
			}

			if ( ! empty( $shortcodes['row']['atts'] ) ) {
				$shortcodes['row']['atts'] = $this->get_the_row_atts();
			}

			if ( ! empty( $shortcodes['row_inner']['atts'] ) ) {
				$shortcodes['row_inner']['atts'] = $this->get_the_row_atts();
			}

			// [col], [col_inner]
			if ( ! empty( $shortcodes['col'] ) ) {
				$shortcodes['col']['function'] = array( $this, 'col' );
			}

			if ( ! empty( $shortcodes['col_inner'] ) ) {
				$shortcodes['col_inner']['function'] = array( $this, 'col' );
			}

			if ( ! empty( $shortcodes['col']['atts'] ) ) {
				$shortcodes['col']['atts'] = $this->get_the_col_atts( $shortcodes );
			}

			if ( ! empty( $shortcodes['col_inner']['atts'] ) ) {
				$shortcodes['col_inner']['atts'] = $this->get_the_col_atts( $shortcodes );
			}

			return $shortcodes;
		}

		/**
		 * Retrieve a `atts` array for [row], [row_inner].
		 */
		public function get_the_row_atts() {
			return array(
					'type' => array(
						'type'   => 'select',
						'values' => array(
							'fixed-width' => __( 'Fixed Width', 'motopress-cherryframework4' ),
							'full-width'  => __( 'Full Width', 'motopress-cherryframework4' ),
						),
						'default' => 'full-width',
						'name'    => __( 'Width Type', 'motopress-cherryframework4' ),
						'desc'    => __( 'Select Width Type', 'motopress-cherryframework4' ),
					),
					'bg_type' => array(
						'type'   => 'select',
						'values' => array(
							'none'           => __( 'None', 'motopress-cherryframework4' ),
							'image'          => __( 'Color/Image', 'motopress-cherryframework4' ),
							'parallax_image' => __( 'Parallax Image', 'motopress-cherryframework4' ),
							'parallax_video' => __( 'Parallax Video', 'motopress-cherryframework4' ),
						),
						'default' => 'none',
						'name'    => __( 'Background Type', 'motopress-cherryframework4' ),
						'desc'    => __( 'Select Background Type', 'motopress-cherryframework4' ),
					),
					'anchor' => array(
						'default' => '',
						'name'    => __( 'Anchor', 'motopress-cherryframework4' ),
						'desc'    => __( 'This option defines menu item marker.', 'motopress-cherryframework4' ),
					),
					'class' => array(
						'default' => '',
						'name'    => __( 'Class', 'motopress-cherryframework4' ),
						'desc'    => __( 'Extra CSS class', 'motopress-cherryframework4' ),
					),
				);
		}

		/**
		 * Retrieve a `atts` array for [col], [col_inner].
		 */
		public function get_the_col_atts( $shortcodes ) {
			return array_merge( $shortcodes[ 'col' ]['atts'], array( 'bg_type' => array(
						'type'   => 'select',
						'values' => array(
							'none'  => __( 'None', 'motopress-cherryframework4' ),
							'image' => __( 'Color/Image', 'motopress-cherryframework4' ),
						),
						'default' => 'none',
						'name'    => __( 'Background Type', 'motopress-cherryframework4' ),
						'desc'    => __( 'Select Background Type', 'motopress-cherryframework4' ),
					) )
				);
		}

		/**
		 * Callback-function for [row], [row_inner] shortcode.
		 */
		public function row( $atts = null, $content = null ) {
			$original_atts = $atts;

			$atts = shortcode_atts( array(
				'type'    => 'full-width',
				'bg_type' => 'none',
				'anchor'  => '',
				'class'   => '',

				// image
				'preset'        => '',
				'bg_color'      => '',
				'bg_image'      => '',
				'bg_position'   => 'center',
				'bg_repeat'     => 'no-repeat',
				'bg_attachment' => 'scroll',
				'bg_size'       => 'auto',

				// parallax image
				'speed'             => '1.5',
				'invert'            => 'no',
				'min_height'        => '300',

				// parallax video
				'poster'            => '',
				'mp4'               => '',
				'webm'              => '',
				'ogv'               => '',
			), $atts, 'row' );

			$type        = sanitize_key( $atts['type'] );
			$bg_type     = sanitize_key( $atts['bg_type'] );
			$class       = ( 'fixed-width' == $type ) ? '' : cherry_esc_class_attr( $atts );
			$anchor_data = '';
			$row_class   = apply_filters( 'cherry_shortcodes_output_row_class', 'row', $atts );
			$output      = false;
			$id          = '';
			$anchor_data = '';

			if ( $atts[ 'anchor' ] ) {
				$anchor = preg_replace( '/[^A-Za-z0-9-_]/', '',  $atts[ 'anchor' ] );
				$anchor = str_replace( ' ', '-', $anchor );

				$id = 'data-id="' . $anchor . '" ';
				$anchor_data = 'data-anchor="true"';

				wp_localize_script( 'page-anchor', 'anchor_scroll_speed', array( apply_filters( 'cherry_anchor_scroll_speed', 300 ) ) );
				cherry_query_asset( 'js', 'page-anchor' );
			}

			$container = ( 'fixed-width' == $type ) ? '<div class="container"><div class="%2$s">%1$s</div></div>' : '<div class="container-fluid"><div class="%2$s">%1$s</div></div>';

			$default_bg_type = false;

			switch ( $bg_type ) {

				case 'image':
					$_atts = $atts;

					if ( ! empty( $_atts['class'] ) ) {
						$_atts['class'] = '';
					}

					$_content = Cherry_Shortcodes_Handler::box( $_atts, sprintf( $container, $content, $row_class ) );
					break;

				case 'parallax_image':

					if ( ! empty( $original_atts['image_src'] ) ) $atts['bg_image'] = $original_atts['image_src'];
					if ( ! empty( $original_atts['parallax_speed'] ) ) $atts['speed'] = $original_atts['parallax_speed'];
					if ( ! empty( $original_atts['parallax_invert'] ) ) $atts['invert'] = $original_atts['parallax_invert'];

					$_content = Cherry_Shortcodes_Handler::paralax_image( $atts, sprintf( $container, $content, $row_class ) );
					break;

				case 'parallax_video':
					$_content = Cherry_Shortcodes_Handler::paralax_html_video( $atts, sprintf( $container, $content, $row_class ) );
					break;

				default:
					$default_bg_type = true && ( 'full-width' == $type );
					$container = ( 'fixed-width' == $type ) ? '<div class="container"><div class="%2$s">%1$s</div></div>' : '%s';

					$_content = sprintf( $container, do_shortcode( $content ), $row_class );
					break;
			}
			$output = '<div class="' . ( $default_bg_type ? $row_class : 'row' ) . cherry_esc_class_attr( $atts ) . '" ' . $id . $anchor_data . '>' . $_content . '</div>';

			return apply_filters( 'cherry_shortcodes_output', $output, $atts, 'row' );
		}

		/**
		 * Callback-function for [col], [col_inner] shortcode.
		 */
		public function col( $original_atts = null, $content = null ) {
			$atts = shortcode_atts( array(
				'size_xs'   => 'none',
				'size_sm'   => 'none',
				'size_md'   => 'none',
				'size_lg'   => 'none',
				'offset_xs' => '',
				'offset_sm' => '',
				'offset_md' => '',
				'offset_lg' => '',
				'pull_xs'   => '',
				'pull_sm'   => '',
				'pull_md'   => '',
				'pull_lg'   => '',
				'push_xs'   => '',
				'push_sm'   => '',
				'push_md'   => '',
				'push_lg'   => '',
				'collapse'  => 'no',
				'class'     => '',

				// image
				'bg_type'       => 'none',
				'preset'        => '',
				'bg_color'      => '',
				'bg_image'      => '',
				'bg_position'   => 'center',
				'bg_repeat'     => 'no-repeat',
				'bg_attachment' => 'scroll',
				'bg_size'       => 'auto',
			), $original_atts, 'col' );

			$class = '';

			// Size
			$class .= ( 'none' == $atts['size_xs'] )   ? '' : ' col-xs-' . sanitize_key( $atts['size_xs'] );
			$class .= ( 'none' == $atts['size_sm'] )   ? '' : ' col-sm-' . sanitize_key( $atts['size_sm'] );
			$class .= ( 'none' == $atts['size_md'] )   ? '' : ' col-md-' . sanitize_key( $atts['size_md'] );
			$class .= ( 'none' == $atts['size_lg'] )   ? '' : ' col-lg-' . sanitize_key( $atts['size_lg'] );

			// Offset
			if ( ! empty( $original_atts['offset_xs'] ) ) {
				$class .= ( 'none' == $original_atts['offset_xs'] ) ? ' col-xs-offset-0' : ' col-xs-offset-' . sanitize_key( $atts['offset_xs'] );
			}

			if ( ! empty( $original_atts['offset_sm'] ) ) {
				$class .= ( 'none' == $original_atts['offset_sm'] ) ? ' col-sm-offset-0' : ' col-sm-offset-' . sanitize_key( $atts['offset_sm'] );
			}

			if ( ! empty( $original_atts['offset_md'] ) ) {
				$class .= ( 'none' == $original_atts['offset_md'] ) ? ' col-md-offset-0' : ' col-md-offset-' . sanitize_key( $atts['offset_md'] );
			}

			if ( ! empty( $original_atts['offset_lg'] ) ) {
				$class .= ( 'none' == $original_atts['offset_lg'] ) ? ' col-lg-offset-0' : ' col-lg-offset-' . sanitize_key( $atts['offset_lg'] );
			}

			// Pull
			if ( ! empty( $original_atts['pull_xs'] ) ) {
				$class .= ( 'none' == $original_atts['pull_xs'] ) ? ' col-xs-pull-0' : ' col-xs-pull-' . sanitize_key( $atts['pull_xs'] );
			}

			if ( ! empty( $original_atts['pull_sm'] ) ) {
				$class .= ( 'none' == $original_atts['pull_sm'] ) ? ' col-sm-pull-0' : ' col-sm-pull-' . sanitize_key( $atts['pull_sm'] );
			}

			if ( ! empty( $original_atts['pull_md'] ) ) {
				$class .= ( 'none' == $original_atts['pull_md'] ) ? ' col-md-pull-0' : ' col-md-pull-' . sanitize_key( $atts['pull_md'] );
			}

			if ( ! empty( $original_atts['pull_lg'] ) ) {
				$class .= ( 'none' == $original_atts['pull_lg'] ) ? ' col-lg-pull-0' : ' col-lg-pull-' . sanitize_key( $atts['pull_lg'] );
			}

			// Push
			if ( ! empty( $original_atts['push_xs'] ) ) {
				$class .= ( 'none' == $original_atts['push_xs'] ) ? ' col-xs-push-0' : ' col-xs-push-' . sanitize_key( $atts['push_xs'] );
			}

			if ( ! empty( $original_atts['push_sm'] ) ) {
				$class .= ( 'none' == $original_atts['push_sm'] ) ? ' col-sm-push-0' : ' col-sm-push-' . sanitize_key( $atts['push_sm'] );
			}

			if ( ! empty( $original_atts['push_md'] ) ) {
				$class .= ( 'none' == $original_atts['push_md'] ) ? ' col-md-push-0' : ' col-md-push-' . sanitize_key( $atts['push_md'] );
			}

			if ( ! empty( $original_atts['push_lg'] ) ) {
				$class .= ( 'none' == $original_atts['push_lg'] ) ? ' col-lg-push-0' : ' col-lg-push-' . sanitize_key( $atts['push_lg'] );
			}

			// Collapse?
			$class .= ( 'yes' != $atts['collapse']  )  ? '' : ' collapse-col';
			$class .= cherry_esc_class_attr( $atts );

			// Backgroud Type
			$bg_type = sanitize_key( $atts['bg_type'] );

			if ( 'image' == $bg_type ) {

				$_atts = $atts;

				if ( ! empty( $_atts['class'] ) ) {
					$_atts['class'] = '';
				}

				$_content = Cherry_Shortcodes_Handler::box( $_atts, $content );

			} else {
				$_content = do_shortcode( $content );
			}

			$output = '<div class="' . trim( esc_attr( $class ) ) . '">' . $_content . '</div>';

			return apply_filters( 'cherry_shortcodes_output', $output, $atts, 'col' );
		}

		/**
		 * Prepare a settings.
		 */
		public static function print_settings( $atts ) {

			if ( empty( $atts ) ) {
				return;
			}

			$return = '';

			foreach ( $atts as $attr_name => $attr_info ) {

				// Prepare default value.
				$default           = (string) ( isset( $attr_info['default'] ) ) ? $attr_info['default'] : '';
				$attr_info['name'] = ( isset( $attr_info['name'] ) ) ? $attr_info['name'] : $attr_name;
				$return .= '<div class="cherry-generator-attr-container custom-generator-group-item" data-default="' . esc_attr( $default ) . '">';
				$return .= '<h5>' . $attr_info['name'] . '</h5>';

				// Create field types.
				if ( ! isset( $attr_info['type'] )
					&& isset( $attr_info['values'] )
					&& is_array( $attr_info['values'] )
					&& count( $attr_info['values'] )
					) {
					$attr_info['type'] = 'select';

				} elseif ( ! isset( $attr_info['type'] ) ) {
					$attr_info['type'] = 'text';
				}

				if ( is_callable( array( 'Cherry_Shortcodes_Generator_Views', $attr_info['type'] ) ) ) {
					$return .= call_user_func( array( 'Cherry_Shortcodes_Generator_Views', $attr_info['type'] ), $attr_name, $attr_info );

				} elseif ( isset( $attr_info['callback'] ) && is_callable( $attr_info['callback'] ) ) {
					$return .= call_user_func( $attr_info['callback'], $attr_name, $attr_info );
				}

				if ( isset( $attr_info['desc'] ) ) {
					$return .= '<div class="cherry-generator-attr-desc">' . str_replace( array( '<b%value>', '<b_>' ), '<b class="cherry-generator-set-value" title="' . __( 'Click to set this value', 'motopress-cherryframework4' ) . '">', $attr_info['desc'] ) . '</div>';
				}

				$return .= '</div>';
			}

			return '<div id="custom-shortcodes-bg-type-atts">' . $return . '</div>';
		}

		/**
		 * AJAX-callback for `Backgrund Type` attribute.
		 */
		public function ajax_get_bg_type() {

			$bg_type = 'none';

			if ( isset( $_REQUEST['bg_type'] ) ) {
				$bg_type = sanitize_key( $_REQUEST['bg_type'] );
			}

			$atts = $this->get_bg_type($bg_type);

			die( self::print_settings( $atts ) );
		}


		public function get_bg_type( $bg_type = 'none' ) {

			switch ( $bg_type ) {

				case 'image':
					$atts = array(
						'preset' => array(
							'type'   => 'select',
							'values' => array(
								''                 => __( 'No preset', 'motopress-cherryframework4' ),
								'primary'          => __( 'Primary', 'motopress-cherryframework4' ),
								'secondary'        => __( 'Secondary', 'motopress-cherryframework4' ),
								'gray'             => __( 'Gray', 'motopress-cherryframework4' ),
								'primary-border'   => __( 'Primary border', 'motopress-cherryframework4' ),
								'secondary-border' => __( 'Secondary border', 'motopress-cherryframework4' ),
								'gray-border'      => __( 'Gray border', 'motopress-cherryframework4' ),
							),
							'default' => '',
							'name'    => __( 'Styling preset', 'motopress-cherryframework4' ),
							'desc'    => __( 'Select styling preset', 'motopress-cherryframework4' ),
						),
						'bg_color' => array(
							'type'    => 'color',
							'values'  => array(),
							'default' => '#ffffff',
							'name'    => __( 'Background Color', 'motopress-cherryframework4' ),
							'desc'    => __( 'Select background color', 'motopress-cherryframework4' ),
						),
						'bg_image' => array(
							'type'    => 'upload',
							'default' => '',
							'name'    => __( 'Background Image', 'motopress-cherryframework4' ),
							'desc'    => __( 'Upload background image', 'motopress-cherryframework4' ),
						),
						'bg_position' => array(
							'type'   => 'select',
							'values' => array(
								'top-left'      => __( 'Top Left', 'motopress-cherryframework4' ),
								'top-center'    => __( 'Top Center', 'motopress-cherryframework4' ),
								'top-right'     => __( 'Top Right', 'motopress-cherryframework4' ),
								'left'          => __( 'Middle Left', 'motopress-cherryframework4' ),
								'center'        => __( 'Middle Center', 'motopress-cherryframework4' ),
								'right'         => __( 'Middle Right', 'motopress-cherryframework4' ),
								'bottom-left'   => __( 'Bottom Left', 'motopress-cherryframework4' ),
								'bottom-center' => __( 'Bottom Center', 'motopress-cherryframework4' ),
								'bottom-right'  => __( 'Bottom Right', 'motopress-cherryframework4' ),
							),
							'default' => 'center',
							'name'    => __( 'Background image position', 'motopress-cherryframework4' ),
							'desc'    => __( 'Select background image position', 'motopress-cherryframework4' ),
						),
						'bg_repeat' => array(
							'type'   => 'select',
							'values' => array(
								'no-repeat' => __( 'No Repeat', 'motopress-cherryframework4' ),
								'repeat'    => __( 'Repeat All', 'motopress-cherryframework4' ),
								'repeat-x'  => __( 'Repeat Horizontally', 'motopress-cherryframework4' ),
								'repeat-y'  => __( 'Repeat Vertically', 'motopress-cherryframework4' ),
							),
							'default' => 'no-repeat',
							'name'    => __( 'Background image repeat', 'motopress-cherryframework4' ),
							'desc'    => __( 'Select background image repeat', 'motopress-cherryframework4' ),
						),
						'bg_attachment' => array(
							'type'   => 'select',
							'values' => array(
								'scroll' => __( 'Scroll normally', 'motopress-cherryframework4' ),
								'fixed'  => __( 'Fixed in place', 'motopress-cherryframework4' ),
							),
							'default' => 'scroll',
							'name'    => __( 'Background image attachment', 'motopress-cherryframework4' ),
							'desc'    => __( 'Select background image attachment', 'motopress-cherryframework4' ),
						),
						'bg_size' => array(
							'type'   => 'select',
							'values' => array(
								'auto'    => __( 'Auto', 'motopress-cherryframework4' ),
								'cover'   => __( 'Cover', 'motopress-cherryframework4' ),
								'contain' => __( 'Contain', 'motopress-cherryframework4' ),
							),
							'default' => 'auto',
							'name'    => __( 'Background image size', 'motopress-cherryframework4' ),
							'desc'    => __( 'Select background image size', 'motopress-cherryframework4' ),
						),
					);
					break;

				case 'parallax_image':
					$atts = array(
						'image_src' => array(
							'type'    => 'upload',
							'default' => '',
							'name'    => __( 'Background Image', 'motopress-cherryframework4' ),
							'desc'    => __( 'Upload background image', 'motopress-cherryframework4' ),
						),
						'parallax_speed' => array(
							'type'    => 'number',
							'min'     => 0,
							'max'     => 10,
							'step'    => 0.1,
							'default' => 1.5,
							'name'    => __( 'Parallax speed', 'motopress-cherryframework4' ),
							'desc'    => __( 'Parallax speed value (s)', 'motopress-cherryframework4' ),
						),
						'parallax_invert' => array(
							'type'    => 'select',
							'values' => array(
								'yes' => __( 'Yes', 'motopress-cherryframework4' ),
								'no'  => __( 'No', 'motopress-cherryframework4' ),
							),
							'default' => 'no',
							'name'    => __( 'Parallax invert', 'motopress-cherryframework4' ),
							'desc'    => __( 'Parallax invert direction move', 'motopress-cherryframework4' ),
						),
						'min_height' => array(
							'type'    => 'number',
							'min'     => 0,
							'max'     => 1000,
							'step'    => 1,
							'default' => 300,
							'name'    => __( 'Parallax container min-height', 'motopress-cherryframework4' ),
							'desc'    => __( 'Container min-height value (px)', 'motopress-cherryframework4' ),
						),
					);
					break;

				case 'parallax_video':
					$atts = array(
						'poster' => array(
							'type'    => 'upload',
							'default' => '',
							'name'    => __( 'Poster', 'motopress-cherryframework4' ),
							'desc'    => __( 'Upload poster image', 'motopress-cherryframework4' ),
						),
						'mp4' => array(
							'type'    => 'upload',
							'default' => '',
							'name'    => __( 'mp4 file', 'motopress-cherryframework4' ),
							'desc'    => __( 'URL to mp4 video-file', 'motopress-cherryframework4' ),
						),
						'webm' => array(
							'type'    => 'upload',
							'default' => '',
							'name'    => __( 'webm file', 'motopress-cherryframework4' ),
							'desc'    => __( 'URL to webm video-file', 'motopress-cherryframework4' ),
						),
						'ogv' => array(
							'type'    => 'upload',
							'default' => '',
							'name'    => __( 'ogv file', 'motopress-cherryframework4' ),
							'desc'    => __( 'URL to ogv video-file', 'motopress-cherryframework4' ),
						),
						'speed' => array(
							'type'    => 'number',
							'min'     => 0,
							'max'     => 10,
							'step'    => 0.1,
							'default' => 1.5,
							'name'    => __( 'Parallax speed', 'motopress-cherryframework4' ),
							'desc'    => __( 'Parallax speed value (s)', 'motopress-cherryframework4' ),
						),
						'invert' => array(
							'type'    => 'select',
							'values' => array(
								'yes' => __( 'Yes', 'motopress-cherryframework4' ),
								'no'  => __( 'No', 'motopress-cherryframework4' ),
							),
							'default' => 'no',
							'name'    => __( 'Parallax invert', 'motopress-cherryframework4' ),
							'desc'    => __( 'Parallax invert direction move', 'motopress-cherryframework4' ),
						),
					);
					break;

				default:
					$atts = array();
					break;
			}

			return apply_filters( 'custom_cherry_shortcodes_bg_atts', $atts, $bg_type );
		}

		public function mpce_custom_cherry_shortcodes_library_extend($motopressCELibrary) {

			if ( !class_exists('Cherry_Shortcodes_Data') || !class_exists('MPCE_Cherry4') ) return;

			$shortcodes = Cherry_Shortcodes_Data::shortcodes();

			$grid_shortcodes = array('row', 'row_inner', 'col', 'col_inner');
			$prefix = CHERRY_SHORTCODES_PREFIX;

			foreach ($grid_shortcodes as $shortcode) {

				$mp_object = &$motopressCELibrary->getObject($prefix . $shortcode);

				foreach ($mp_object->parameters['bg_type']['list'] as $bg_type_key => $bg_type_label) {

					$mpce_cherry4 = MPCE_Cherry4::instance();

					$mp_object_new_parameters = $mpce_cherry4->cherry_attributes_to_parameters($this->get_bg_type($bg_type_key));
					foreach ( $mp_object_new_parameters as $parameter_key => &$parameter_value ) {
						$parameter_value['dependency'] = array(
							'parameter' => 'bg_type',
							'value' => $bg_type_key
						);
					}

					$mp_object->parameters = array_merge($mp_object->parameters, $mp_object_new_parameters);
				}
			}
		}

		/**
		 * Returns the instance.
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance )
				self::$instance = new self;

			return self::$instance;
		}
	}

	Custom_Cherry_Shortcodes::get_instance();
}
