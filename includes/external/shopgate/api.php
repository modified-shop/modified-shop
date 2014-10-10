<?php
/*
* Shopgate GmbH
*
* URHEBERRECHTSHINWEIS
*
* Dieses Plugin ist urheberrechtlich geschützt. Es darf ausschließlich von Kunden der Shopgate GmbH
* zum Zwecke der eigenen Kommunikation zwischen dem IT-System des Kunden mit dem IT-System der
* Shopgate GmbH über www.shopgate.com verwendet werden. Eine darüber hinausgehende Vervielfältigung, Verbreitung,
* öffentliche Zugänglichmachung, Bearbeitung oder Weitergabe an Dritte ist nur mit unserer vorherigen
* schriftlichen Zustimmung zulässig. Die Regelungen der §§ 69 d Abs. 2, 3 und 69 e UrhG bleiben hiervon unberührt.
*
* COPYRIGHT NOTICE
*
* This plugin is the subject of copyright protection. It is only for the use of Shopgate GmbH customers,
* for the purpose of facilitating communication between the IT system of the customer and the IT system
* of Shopgate GmbH via www.shopgate.com. Any reproduction, dissemination, public propagation, processing or
* transfer to third parties is only permitted where we previously consented thereto in writing. The provisions
* of paragraph 69 d, sub-paragraphs 2, 3 and paragraph 69, sub-paragraph e of the German Copyright Act shall remain unaffected.
*
*  @author Shopgate GmbH <interfaces@shopgate.com>
*/
/**
 * Entry point for this plugin is now the shopgate.php file in root directory.
 * This file is only for compatibility to older versions.
 */
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
