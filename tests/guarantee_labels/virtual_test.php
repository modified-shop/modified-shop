<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Prueft, dass ein rein virtueller Artikel kein Label bekommt.
error_reporting(E_ALL & ~E_DEPRECATED);
$guarantee_labels_paths = require __DIR__.'/bootstrap.php';
$root = $guarantee_labels_paths['shop'];
$repo = $guarantee_labels_paths['repo'];
$repo = $guarantee_labels_paths['repo'];
$mode = isset($argv[1]) ? $argv[1] : 'driver';

if ($mode === 'driver') {
  $pass = 0; $fail = 0;
  foreach (array('downloads_aus', 'einfach', 'mehrfach') as $case) {
    $out = array();
    exec(escapeshellcmd(PHP_BINARY).' '.escapeshellarg(__FILE__).' '.escapeshellarg($case).' 2>&1', $out);
    foreach ($out as $line) {
      echo $line."\n";
      if (strpos($line, '  ok    ') === 0) $pass++;
      if (strpos($line, '  FAIL  ') === 0) $fail++;
    }
  }
  echo "\n----------------------------------------\n";
  echo "bestanden: $pass   fehlgeschlagen: $fail\n";
  exit($fail > 0 ? 1 : 0);
}

define('DIR_FS_CATALOG', $root.'/');
define('DIR_WS_CATALOG', '/');
define('DIR_FS_INC', $root.'/inc/');
define('DIR_WS_INCLUDES', 'includes/');
define('DIR_WS_CLASSES', 'includes/classes/');
define('DIR_FS_LOG', $root.'/log/');
define('DIR_FS_EXTERNAL', $root.'/includes/external/');
define('TABLE_MANUFACTURERS', 'manufacturers');
define('TABLE_PRODUCTS_ATTRIBUTES', 'products_attributes');
define('TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD', 'products_attributes_download');
define('MODULE_GUARANTEE_LABELS_STATUS', 'true');
define('DOWNLOAD_ENABLED', ($mode === 'downloads_aus') ? 'false' : 'true');
if ($mode === 'mehrfach') define('DOWNLOAD_MULTIPLE_ATTRIBUTES_ALLOWED', 'true');
$_SESSION['language_charset'] = 'UTF-8';

// Artikel 1: nur Downloads, Artikel 2: Downloads und normale Attribute, Artikel 3: keine
$GLOBALS['attributes'] = array(1 => array('download' => 2, 'total' => 2),
                               2 => array('download' => 1, 'total' => 3),
                               3 => array('download' => 0, 'total' => 0),
                               4 => array('download' => 1, 'total' => 3));
$GLOBALS['queries'] = 0;
function xtc_db_query($sql) {
  $GLOBALS['queries']++;
  preg_match("/products_id = '(\d+)'/", $sql, $m);
  $id = isset($m[1]) ? (int)$m[1] : 0;
  $a = isset($GLOBALS['attributes'][$id]) ? $GLOBALS['attributes'][$id] : array('download' => 0, 'total' => 0);
  // die Artikelfrage liefert beide Zahlen in einer Abfrage
  if (strpos($sql, 'AS downloads') !== false) {
    return array(array('total' => $a['total'], 'downloads' => $a['download']));
  }
  return array(array('total' => (strpos($sql, 'ATTRIBUTES_DOWNLOAD') !== false || strpos($sql, 'products_attributes_download') !== false) ? $a['download'] : $a['total']));
}
function xtc_db_fetch_array(&$r) { return array_shift($r); }
function xtc_db_num_rows($r) { return count($r); }

require $repo.'/inc/guarantee_labels_output.inc.php';

$pass = 0; $fail = 0;
function ok($n, $c, $e = '') { global $pass, $fail; if ($c) { $pass++; echo "  ok    $n\n"; } else { $fail++; echo "  FAIL  $n".($e!==''?"  ($e)":'')."\n"; } }
class glt_price_stub {
  function get_content_type_product($products_id) { $GLOBALS['xtPrice_asked'] = true; return 'virtual'; }
}
function p($id) {
  return array('products_id' => $id, 'products_garan_duration' => '3.0', 'manufacturers_id' => 1, 'products_manufacturers_model' => 'X-1');
}

echo "\n== $mode ==\n";
if ($mode === 'downloads_aus') {
  $GLOBALS['queries'] = 0;
  ok('rein virtueller Artikel gilt als Ware', guarantee_labels_product_physical(1) === true);
  ok('ohne Downloads keine Abfrage', $GLOBALS['queries'] === 0, 'Abfragen: '.$GLOBALS['queries']);
  ok('Kandidat bleibt Kandidat', guarantee_labels_candidate(p(1)) === true);
} elseif ($mode === 'einfach') {
  ok('nur Downloads ist virtuell', guarantee_labels_product_physical(1) === false);
  ok('gemischt bleibt Ware', guarantee_labels_product_physical(2) === true);
  ok('ohne Attribute bleibt Ware', guarantee_labels_product_physical(3) === true);
  ok('virtueller Artikel ist kein Kandidat', guarantee_labels_candidate(p(1)) === false);
  ok('gemischter Artikel ist Kandidat', guarantee_labels_candidate(p(2)) === true);
  $GLOBALS['queries'] = 0;
  guarantee_labels_product_physical(1);
  ok('Ergebnis wird gepuffert', $GLOBALS['queries'] === 0);
  ok('ohne Artikelnummer bleibt Ware', guarantee_labels_product_physical(0) === true);
} else {
  // Mehrfachdownloads aendern nichts an der Artikelfrage, sie entscheiden erst die Auswahl
  ok('nur Downloads bleibt virtuell', guarantee_labels_product_physical(1) === false);
  ok('gemischt bleibt Ware', guarantee_labels_product_physical(2) === true);
  ok('ohne Download bleibt Ware', guarantee_labels_product_physical(3) === true);

  // xtcPrice faltet die Einstellung in den Artikel und wuerde 2 als virtuell melden
  $GLOBALS['xtPrice_asked'] = false;
  $xtPrice = new glt_price_stub();
  ok('Preisobjekt wird nicht gefragt', guarantee_labels_product_physical(4) === true && $GLOBALS['xtPrice_asked'] === false);
}

echo "\nbestanden: $pass   fehlgeschlagen: $fail\n";
exit($fail > 0 ? 1 : 0);
