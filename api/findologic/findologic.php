<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2009 FINDOLOGIC GmbH - Version: 4.1 (120)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
	
chdir('../../');
require ('includes/application_top.php');

// include needed functions
require_once (DIR_FS_EXTERNAL.'findologic/findologic_config.inc.php');
require_once (FL_FS_API.'includes/functions.php');

// authorized ?
if (!isset($_GET['shop']) || $_GET['shop'] != FL_SHOP_ID) {
  die('Unauthorized access!');
}

// set time limit
@set_time_limit(3000);

// set language
$language = new language(FL_LANG);
define("FL_LANG_ID", $language->language['id']);

echo 'Exporting prices for currency ' . CURRENCY . ' and customer group ' . CUSTOMER_GROUP . "\r\n<br />";

// init price
$xtcPrice = new xtcPrice(CURRENCY, CUSTOMER_GROUP);

// set variables
$useKeywords = true;
$debug = false;

// default query
$products_query_raw = "SELECT products_id
                         FROM ".TABLE_PRODUCTS."
                        WHERE products_status = '1'";

// print out database information about a certain product by passing ...&debug=<product_id> 
if (isset($_GET['debug']) && is_numeric($_GET['debug'])) {

  $debug = true;
  $result = xtc_db_query($products_query_raw . " AND products_id = '".(int)$_GET['debug']."'");

} else {

  // set filename
  $filename = DIR_FS_CATALOG.'export/'.MODULE_FINDOLOGIC_EXPORT_FILENAME;
  
  // check permission
  if (is_file($filename) && !is_writeable($filename)) {
    die('File "' . $filename . '" is not writeable!');
  } elseif (!file_exists($filename)) {
    $fp = fopen($filename, "w+");
    fclose($fp);
    if (is_file($filename) && !is_writeable($filename)) {
      die('Create "' . $filename . '" and make it writeable!');
    }
  }
      
  // set export type
  if (isset($_GET['first']) && is_numeric($_GET['first']) && isset($_GET['count']) && is_numeric($_GET['count'])) {
    $incremental = true;
    $first = (int) $_GET['first'];
    $count = (int) $_GET['count'];
  } else {
    $incremental = false;
    $first = null;
    $count = null;
  }

  // initial export
  if (!$incremental || $first === 0) {
    $fp = fopen($filename , "w");
    $header = implode(get_column_delimiter(), get_columns());
    fwrite($fp , $header."\n");
  } else {
    $fp = fopen($filename , "a");
  }
  
  // get total number of products
  $products_count_query = xtc_db_query($products_query_raw);
  $products_count = xtc_db_num_rows($products_count_query);

  echo "Found $products_count products...\r\n<br />";

  // set limit
  $limit = '';
  if ($incremental) {
    $limit = " LIMIT ".$count." OFFSET ".$first;
  }
  
  // process products
  $result = xtc_db_query($products_query_raw . $limit);
}

echo "\r\n";

$counter_exported = 0;
$products_exported = 0;
if (xtc_db_num_rows($result)) {
  while ($row = xtc_db_fetch_array($result)) {

    if ($debug === true) {
      output_row($row);
    }

    if (select_product($row['id'], $useKeywords, $debug)) {
      $products_exported++;
    }
  
    $counter_exported++;
    if ($counter_exported % 500 == 0) {
      echo "$counter_exported of $products_count products processed.\r\n<br />";
    }

  }
}

echo "\r\n";

echo $products_exported." products exported successfully.\r\n<br />";

// failes products ?
if ($products_exported < $counter_exported) {
  echo ($counter_exported - $products_exported)." products failed!\r\n<br />";
}

// products remainig ?
if ($incremental && (($counter_exported + $first) < $products_count)) {
  echo ($products_count - $counter_exported) . " products remaining!\r\n<br />";
}

// close
fclose($fp);

?>