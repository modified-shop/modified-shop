<?php
 
$shopgatePath = dirname(__FILE__).'/includes/external/shopgate';
date_default_timezone_set("Europe/Berlin");
 
include_once $shopgatePath.'/shopgate_library/shopgate.php';
 
include_once('includes/application_top.php');
include_once $shopgatePath.'/plugin.php';
 
$ShopgateFramework = new ShopgateModifiedPlugin();
$ShopgateFramework->handleRequest( $_REQUEST );