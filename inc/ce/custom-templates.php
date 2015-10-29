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
$landingTemplate = new MPCETemplate(MPCEShortcode::PREFIX . 'landing_page', $motopressCELang->CELandingTemplate . ' ' . $motopressCELang->CEPage, $landingContent, 'landing-page.png');

$callToActionTemplate = new MPCETemplate(MPCEShortcode::PREFIX . 'call_to_action_page', $motopressCELang->CECallToActionTemplate . ' ' . $motopressCELang->CEPage, $callToActionContent, 'call-to-action-page.png');

$featureTemplate = new MPCETemplate(MPCEShortcode::PREFIX . 'feature_list', $motopressCELang->CEFeatureTemplate . ' ' . $motopressCELang->CEList, $featureContent, 'feature-list.png');

$descriptionTemplate = new MPCETemplate(MPCEShortcode::PREFIX . 'description_page', $motopressCELang->CEDescriptionTemplate . ' ' . $motopressCELang->CEPage, $descriptionContent, 'description-page.png');

$serviceTemplate = new MPCETemplate(MPCEShortcode::PREFIX . 'service_list', $motopressCELang->CEServiceTemplate . ' ' . $motopressCELang->CEList, $serviceContent, 'service-list.png');

$productTemplate = new MPCETemplate(MPCEShortcode::PREFIX . 'product_page', $motopressCELang->CEProductTemplate . ' ' . $motopressCELang->CEPage, $productContent, 'product-page.png');

//Add template calling addTemplate method
$motopressCELibrary->addTemplate(array($landingTemplate, $callToActionTemplate, $featureTemplate, $descriptionTemplate, $serviceTemplate, $productTemplate));