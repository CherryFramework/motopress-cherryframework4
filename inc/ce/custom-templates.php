<?php

global $motopressCELang;
$defaultText = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam eu hendrerit nunc. Proin tempus pulvinar augue, quis ultrices urna consectetur non.";
$prefix = CHERRY_SHORTCODES_PREFIX;

require_once 'templates/landing.php';
require_once 'templates/callToAction.php';
require_once 'templates/feature.php';
require_once 'templates/description.php';
require_once 'templates/service.php';
require_once 'templates/product.php';

//Add new example of MPCETemplate
$landingTemplate = new MPCETemplate(MPCEShortcode::PREFIX . 'landing_page', __( 'Landing', 'motopress-content-editor' ) . ' ' . __( 'Page', 'motopress-content-editor' ), $landingContent, 'landing-page.png');

$callToActionTemplate = new MPCETemplate(MPCEShortcode::PREFIX . 'call_to_action_page', __( 'Call To Action', 'motopress-content-editor' ) . ' ' . __( 'Page', 'motopress-content-editor' ), $callToActionContent, 'call-to-action-page.png');

$featureTemplate = new MPCETemplate(MPCEShortcode::PREFIX . 'feature_list', __( 'Feature', 'motopress-content-editor' ) . ' ' . __( 'List', 'motopress-content-editor' ), $featureContent, 'feature-list.png');

$descriptionTemplate = new MPCETemplate(MPCEShortcode::PREFIX . 'description_page', __( 'Description', 'motopress-content-editor' ) . ' ' . __( 'Page', 'motopress-content-editor' ), $descriptionContent, 'description-page.png');

$serviceTemplate = new MPCETemplate(MPCEShortcode::PREFIX . 'service_list', __( 'Service', 'motopress-content-editor' ) . ' ' . __( 'List', 'motopress-content-editor' ), $serviceContent, 'service-list.png');

$productTemplate = new MPCETemplate(MPCEShortcode::PREFIX . 'product_page', __( 'Product', 'motopress-content-editor' ) . ' ' . __( 'Page', 'motopress-content-editor' ), $productContent, 'product-page.png');

//Add template calling addTemplate method
$motopressCELibrary->addTemplate(array($landingTemplate, $callToActionTemplate, $featureTemplate, $descriptionTemplate, $serviceTemplate, $productTemplate));