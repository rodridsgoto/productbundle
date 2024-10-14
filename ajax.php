<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');

$module_name = 'productbundle';
$token = pSQL(Tools::encrypt($module_name.'/ajax.php'));
$token_url = pSQL(Tools::getValue('token'));
if ($token != $token_url || !Module::isInstalled($module_name)) {
    echo('Error al ejecutar el ajax');
}
$module = Module::getInstanceByName($module_name);
if ($module->active) {
	$action = pSQL(Tools::getValue('action'));
    switch ($action) {
        case 'search':
            echo json_encode($module->searchProducts(pSQL(Tools::getValue('valor'))));
            break;
        case 'load':
            echo json_encode($module->getBundleProducts(pSQL(Tools::getValue('valor'))));
            break;
        case 'addToCart':
            echo json_encode($module->addBundleProductsToCart(Tools::getValue('valor')));
            break;
        default:
            echo("HOLA");
            break;
    }
}