<?php

date_default_timezone_set("Europe/Berlin");

include_once dirname(__FILE__).'/shopgate_library/shopgate.php';

// Change to a base directory to include all files from
$dir = realpath(dirname(__FILE__)."/../");
##### XTCM BOF #####
chdir( $dir );
##### XTCM EOF #####

// @chdir hack for warning: "open_basedir restriction in effect"
if(@chdir( $dir ) === FALSE){
	chdir( $dir .'/');
}

// fix for bot-trap. Sometimes they block requests by mistake.
define("PRES_CLIENT_IP", @$_SERVER["SERVER_ADDR"]);

/**
 * application_top.php must be included in this file because of errors on other xtc3 extensions
 *
 */
include_once('includes/application_top.php');
include_once dirname(__FILE__).'/plugin.php';

##### XTCM BOF #####
$ShopgateFramework = new ShopgateModifiedPlugin();
##### XTCM EOF #####
$ShopgateFramework->handleRequest($_REQUEST);