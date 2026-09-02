<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Die Bestellbearbeitung laeuft im Admin, dort sind die Storefronttexte nicht geladen.
error_reporting(E_ALL & ~E_DEPRECATED);
$guarantee_labels_paths = require __DIR__.'/bootstrap.php';
$root = $guarantee_labels_paths['shop'];
$repo = $guarantee_labels_paths['repo'];

define('DIR_FS_CATALOG', $root.'/');
define('DIR_WS_CATALOG', '/');
define('DIR_FS_INC', $root.'/inc/');
define('DIR_WS_INCLUDES', 'includes/');
define('DIR_WS_CLASSES', 'includes/classes/');
define('DIR_FS_LOG', $root.'/log/');
define('DIR_FS_EXTERNAL', $root.'/includes/external/');
define('TABLE_MANUFACTURERS', 'manufacturers');
define('TABLE_ORDERS', 'orders');
define('TABLE_ORDERS_GUARANTEE', 'orders_guarantee');
define('TABLE_ORDERS_PRODUCTS_GUARANTEE', 'orders_products_guarantee');
define('TABLE_PRODUCTS_CONTENT', 'products_content');
define('MODULE_GUARANTEE_LABELS_STATUS', 'true');
$_SESSION['language_charset'] = 'UTF-8';

$hash = str_repeat('c', 64);
$dir = $root.'/media/guarantee_labels/archive/garan/'.$hash.'/';
@mkdir($dir, 0777, true);
file_put_contents($dir.'colour.svg', '<svg id="colour"></svg>');
file_put_contents($dir.'nested.svg', '<svg id="nested"></svg>');

$GLOBALS['tables'] = array('orders_products_guarantee', 'orders_guarantee');
function xtc_db_query($sql) {
  if (strpos($sql, 'SHOW TABLES LIKE') === 0) {
    preg_match("/LIKE '([^']+)'/", $sql, $m);
    return in_array(str_replace('\\_', '_', $m[1]), $GLOBALS['tables'], true) ? array(array(1)) : array();
  }
  if (strpos($sql, 'SELECT language') !== false) return array(array('language' => 'german'));
  return array(array('orders_products_id' => '10', 'manufacturers_name' => 'ACME GmbH', 'manufacturers_model' => 'WAU28T20',
                     'garan_duration' => '3.0', 'garan_hash' => str_repeat('c', 64), 'terms_hash' => null, 'terms_filename' => null));
}
function xtc_db_fetch_array(&$r) { return array_shift($r); }
function xtc_db_num_rows($r) { return count($r); }

require DIR_FS_INC.'guarantee_labels_order.inc.php';

$pass = 0; $fail = 0;
function ok($n, $c, $e = '') { global $pass, $fail; if ($c) { $pass++; echo "  ok    $n\n"; } else { $fail++; echo "  FAIL  $n".($e!==''?"  ($e)":'')."\n"; } }

echo "\n== Label im Admin ==\n";
ok('Storefronttexte vorher nicht geladen', !defined('TEXT_GUARANTEE_LABEL_TITLE'));
$markup = guarantee_labels_order_label(4711, 10);
ok('Markup erzeugt statt Abbruch', $markup !== '', 'leer');
ok('Texte wurden nachgeladen', defined('TEXT_GUARANTEE_LABEL_TITLE'));
ok('kompakte Grafik enthalten', preg_match('/id="gl\d+-nested"/', $markup) === 1);
ok('Alternativtext gefuellt', strpos($markup, 'ACME GmbH') !== false);

echo "\n----------------------------------------\n";
echo "bestanden: $pass   fehlgeschlagen: $fail\n";
exit($fail > 0 ? 1 : 0);
