<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Nicht der Katalogartikel entscheidet, sondern die gewaehlte Variante.
error_reporting(E_ALL & ~E_DEPRECATED);
$guarantee_labels_paths = require __DIR__.'/bootstrap.php';
$root = $guarantee_labels_paths['shop'];
$repo = $guarantee_labels_paths['repo'];
$mode = isset($argv[1]) ? $argv[1] : 'driver';

if ($mode === 'driver') {
  $pass = 0; $fail = 0;
  foreach (array('einzeln', 'mehrfach', 'downloads_aus') as $case) {
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
define('DIR_FS_INC', $root.'/inc/');
define('DIR_WS_INCLUDES', 'includes/');
define('DIR_WS_CLASSES', 'includes/classes/');
define('DIR_FS_LOG', $root.'/log/');
define('DIR_FS_EXTERNAL', $root.'/includes/external/');
define('TABLE_MANUFACTURERS', 'manufacturers');
define('TABLE_PRODUCTS_ATTRIBUTES', 'products_attributes');
define('TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD', 'products_attributes_download');
define('MODULE_GUARANTEE_LABELS_STATUS', 'true');
define('MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS', '');
define('DOWNLOAD_ENABLED', ($mode === 'downloads_aus') ? 'false' : 'true');
if ($mode === 'mehrfach') define('DOWNLOAD_MULTIPLE_ATTRIBUTES_ALLOWED', 'true');

require $root.'/lang/german/extra/guarantee_labels.php';
require $root.'/inc/html_encoding.php';

// Artikel 1 ist gemischt: Wert 5 ist ein Download, Wert 6 koerperlich.
$GLOBALS['downloads'] = array(5);
function xtc_db_query($sql) {
  if (strpos($sql, 'products_attributes_download') !== false) {
    preg_match_all("/'(\d+)'/", $sql, $m);
    $ids = array_map('intval', $m[1]);
    array_shift($ids); // products_id
    if (count($ids) < 1) { // Artikelabfrage ohne Werteliste
      return array(array('total' => count($GLOBALS['downloads'])));
    }
    return array(array('total' => count(array_intersect($ids, $GLOBALS['downloads']))));
  }
  if (strpos($sql, 'FROM products_attributes') !== false) {
    return array(array('total' => 2)); // der Artikel hat zwei Attributwerte
  }
  preg_match_all("/'(\d+)'/", $sql, $m);
  $rows = array();
  foreach ($m[1] as $id) $rows[] = array('manufacturers_id' => $id, 'manufacturers_name' => 'ACME GmbH');
  return $rows;
}
function xtc_db_fetch_array(&$r) { return array_shift($r); }
function xtc_db_num_rows($r) { return count($r); }

require DIR_FS_INC.'guarantee_labels_output.inc.php';

$pass = 0; $fail = 0;
function ok($n, $c, $e = '') { global $pass, $fail; if ($c) { $pass++; echo "  ok    $n\n"; } else { $fail++; echo "  FAIL  $n".($e!==''?"  ($e)":'')."\n"; } }

$row = array('products_id' => 1, 'products_garan_duration' => '3.0', 'manufacturers_id' => 1,
             'products_manufacturers_model' => 'WAU28T20');

echo "\n== $mode ==\n";
// bei erlaubten Mehrfachdownloads gilt schon der Artikel als virtuell
$artikel_koerperlich = ($mode !== 'mehrfach');
ok('Artikel ohne Auswahl richtig eingestuft', guarantee_labels_candidate($row) === $artikel_koerperlich);
ok('ohne Attribute im Warenkorb entscheidet der Artikel', guarantee_labels_candidate($row, '1') === $artikel_koerperlich);
ok('gewaehlte koerperliche Variante behaelt das Label', guarantee_labels_candidate($row, '1{1}6') === true);

if ($mode === 'downloads_aus') {
  ok('ohne Downloads bleibt jede Variante koerperlich', guarantee_labels_candidate($row, '1{1}5') === true);
} else {
  ok('reine Downloadvariante bekommt kein Label', guarantee_labels_candidate($row, '1{1}5') === false);
}

if ($mode === 'einzeln') {
  ok('gemischte Auswahl behaelt das Label', guarantee_labels_candidate($row, '1{1}5{2}6') === true);
} elseif ($mode === 'mehrfach') {
  ok('gemischte Auswahl gilt als digital', guarantee_labels_candidate($row, '1{1}5{2}6') === false);
} else {
  ok('gemischte Auswahl bleibt koerperlich', guarantee_labels_candidate($row, '1{1}5{2}6') === true);
}

ok('Position ohne GARAN-Daten bleibt ohne Label',
   guarantee_labels_candidate(array('products_id' => 1, 'products_garan_duration' => null,
                                    'manufacturers_id' => 1, 'products_manufacturers_model' => 'X'), '1{1}6') === false);

echo "\n----------------------------------------\nbestanden: $pass   fehlgeschlagen: $fail\n";
exit($fail > 0 ? 1 : 0);
