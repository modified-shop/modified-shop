<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
 	 based on:
	  (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
	  (c) 2002-2003 osCommerce - www.oscommerce.com
	  (c) 2001-2003 TheMedia, Dipl.-Ing Thomas Plänkers - http://www.themedia.at & http://www.oscommerce.at
	  (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com
    (c) 2013 Gambio GmbH - http://www.gambio.de
  
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

chdir('../../');
include('includes/application_top_callback.php');

require_once (DIR_FS_EXTERNAL.'payone/classes/PayoneModified.php');
$payone = new PayoneModified();

$logfile = 'payone_tx_status.log';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	$payone->log("not a POST request!", $logfile);
	echo "NACK\n";
	exit;
}

$payone->log("received status from ".$_SERVER['REMOTE_ADDR'], $logfile);
$payone->log(print_r($_POST, true), $logfile);

// include language
include (DIR_WS_CLASSES.'language.php');
$lng = new language(xtc_input_validation(DEFAULT_LANGUAGE, 'char', ''));
require_once (DIR_FS_EXTERNAL.'payone/lang/'.$lng->language['directory'].'.php');

// make callback
$payone->saveTransactionStatus($_POST);

echo "TSOK\n";
?>