<?php

/* -----------------------------------------------------------------------------------------
   $Id: download.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(download.php,v 1.9 2003/02/13); www.oscommerce.com 
   (c) 2003 nextcommerce (download.php,v 1.7 2003/08/17); www.nextcommerce.org
   (c) 2006 xtCommerce (download.php 831 2005-03-13)

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

// For downloads we don't need gzip
$gzip_off = true;
include ('includes/application_top.php');

// include needed functions
require_once (DIR_FS_INC.'xtc_random_name.inc.php');
require_once (DIR_FS_INC.'xtc_unlink_temp_dir.inc.php');

if (!isset ($_SESSION['customer_id']))
  die('not allowed');

// Check download.php was called with proper GET parameters
//BOF - DokuMan - 2010-03-19 - check for correct and set GET-Parameters
//if ((isset ($_GET['order']) && !is_numeric($_GET['order'])) || (isset ($_GET['id']) && !is_numeric($_GET['id']))) {
if (!isset ($_GET['order']) || !is_numeric($_GET['order']) || !isset ($_GET['id']) || !is_numeric($_GET['id'])) {
//EOF - DokuMan - 2010-03-19 - check for correct and set GET-Parameters
  die('not allowed');
}

// Check that order_id, customer_id and filename match
//BOF - DokuMan - 2010-03-19 - call everything in SQL
//BOF - web28 - 2010-07-07 - BUGFIX o.orders_status >
//$downloads_query = xtc_db_query("select date_format(o.date_purchased, '%Y-%m-%d') as date_purchased_day, opd.download_maxdays, opd.download_count, opd.download_maxdays, opd.orders_products_filename from ".TABLE_ORDERS." o, ".TABLE_ORDERS_PRODUCTS." op, ".TABLE_ORDERS_PRODUCTS_DOWNLOAD." opd where o.customers_id = '".$_SESSION['customer_id']."' and o.orders_id = '".(int) $_GET['order']."' and o.orders_id = op.orders_id and op.orders_products_id = opd.orders_products_id and opd.orders_products_download_id = '".(int) $_GET['id']."' and opd.orders_products_filename != ''");
$downloads_query = xtc_db_query("
SELECT opd.orders_products_filename
FROM ".TABLE_ORDERS." o,
     ".TABLE_ORDERS_PRODUCTS." op,
     ".TABLE_ORDERS_PRODUCTS_DOWNLOAD." opd
WHERE o.customers_id = ".(int)$_SESSION['customer_id']."
AND o.orders_id = ".(int) $_GET['order']."
AND o.orders_id = op.orders_id
AND op.orders_products_id = opd.orders_products_id
AND opd.orders_products_download_id = ".(int) $_GET['id']."
AND opd.orders_products_filename != ''
AND DATE_SUB(CURDATE(), INTERVAL opd.download_maxdays DAY) <= o.date_purchased
AND opd.download_count > 0
AND o.orders_status >= " . DOWNLOAD_MIN_ORDERS_STATUS
);
//EOF - web28 - 2010-07-07 - BUGFIX o.orders_status >
//EOF - DokuMan - 2010-03-19 - call everything in SQL
if (!xtc_db_num_rows($downloads_query))
  die();
$downloads = xtc_db_fetch_array($downloads_query);
//BOF - DokuMan - 2010-03-19 - call everything in SQL
// MySQL 3.22 does not have INTERVAL
/*
list ($dt_year, $dt_month, $dt_day) = explode('-', $downloads['date_purchased_day']);
$download_timestamp = mktime(23, 59, 59, $dt_month, $dt_day + $downloads['download_maxdays'], $dt_year);

// Die if time expired (maxdays = 0 means no time limit)
if (($downloads['download_maxdays'] != 0) && ($download_timestamp <= time()))
	die;
// Die if remaining count is <=0
if ($downloads['download_count'] <= 0)
	die;
*/
//EOF - DokuMan - 2010-03-19 - call everything in SQL
	
// Die if file is not there
if (!file_exists(DIR_FS_DOWNLOAD.$downloads['orders_products_filename']))
  die('file not found');

// Now decrement counter
xtc_db_query("update ".TABLE_ORDERS_PRODUCTS_DOWNLOAD." set download_count = download_count-1 where orders_products_download_id = '".(int) $_GET['id']."'");

if (DOWNLOAD_BY_REDIRECT == 'true') {
  // This will work only on Unix/Linux hosts
  xtc_unlink_temp_dir(DIR_FS_DOWNLOAD_PUBLIC);
  $tempdir = xtc_random_name();
  umask(0000);
  mkdir(DIR_FS_DOWNLOAD_PUBLIC.$tempdir, 0777);
  if (!symlink(DIR_FS_DOWNLOAD.$downloads['orders_products_filename'], DIR_FS_DOWNLOAD_PUBLIC.$tempdir.'/'.$downloads['orders_products_filename'])) {
    link(DIR_FS_DOWNLOAD.$downloads['orders_products_filename'], DIR_FS_DOWNLOAD_PUBLIC.$tempdir.'/'.$downloads['orders_products_filename']); 
  }
  xtc_redirect(DIR_WS_DOWNLOAD_PUBLIC.$tempdir.'/'.$downloads['orders_products_filename']);
  
} else {
  //Set chunk size for download
  $chunksize = 1 * (1024 * 1024);
  // Now send the file with header() magic
  header("Expires: Mon, 26 Nov 1962 00:00:00 GMT");
  header("Last-Modified: ".gmdate("D,d M Y H:i:s")." GMT");
  header("Cache-Control: no-cache, must-revalidate");
  header("Pragma: no-cache");
  header("Content-Type: Application/octet-stream");
  header("Content-Length: ".filesize(DIR_FS_DOWNLOAD.$downloads['orders_products_filename']));
  header("Content-disposition: attachment; filename=\"".$downloads['orders_products_filename']."\"");
  //BOF - h-h-h - 2010-02-03 - add support for large download files
  // This will work on all systems, but will need considerable resources
  // We could also loop with fread($fp, 4096) to save memory
  //readfile(DIR_FS_DOWNLOAD.$downloads['orders_products_filename']);
  function readfile_chunked($file, $chunksize) {
    $buffer = '';
    // Small files don't need to be chunked
    if (filesize($file) <= $chunksize) {
      $status = readfile($file);
      return $status;
    }
    $handle = fopen($file, 'rb');
    if ($handle === false)
      return false;
    while (!feof($handle)) {
      echo fread($handle, $chunksize);
    }
    $status = fclose($handle);

    return $status;
  }
  readfile_chunked(DIR_FS_DOWNLOAD . $downloads['orders_products_filename'], $chunksize);
  //EOF - h-h-h - 2010-02-03 - add support for large download files
}
?>
