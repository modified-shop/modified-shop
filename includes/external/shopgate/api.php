<?php
/**
 * application_top.php must included in this File because errors on other xtc3 extensions
 *
 */

date_default_timezone_set("Europe/Berlin");

include_once dirname(__FILE__).'/shopgate_library/shopgate.php';

// Change to a base directory to include all files from
$dir = realpath(dirname(__FILE__)."/../");
chdir( $dir );

// @chdir hack for warning: "open_basedir restriction in effect"
if(@chdir( $dir ) === FALSE){
	chdir( $dir .'/');
}

include_once('includes/application_top.php');
include_once dirname(__FILE__).'/plugin.php';

$ShopgateFramework = new ShopgateModifiedPlugin();
$ShopgateFramework->handleRequest($_REQUEST);