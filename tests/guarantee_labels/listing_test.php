<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Prueft die Produkt-Klassenerweiterung fuer Artikellisten. Die Schalter sind Konstanten,
// deshalb laeuft jede Kombination in einem eigenen Prozess.
error_reporting(E_ALL & ~E_DEPRECATED);
$guarantee_labels_paths = require __DIR__.'/bootstrap.php';
$root = $guarantee_labels_paths['shop'];
$repo = $guarantee_labels_paths['repo'];
$mode = isset($argv[1]) ? $argv[1] : 'driver';

if ($mode === 'driver') {
  $pass = 0; $fail = 0;
  foreach (array('aktiv', 'kein_button', 'modul_aus') as $case) {
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
define('DIR_WS_INCLUDES', 'includes/');
define('DIR_WS_CLASSES', 'includes/classes/');
define('DIR_FS_INC', $root.'/inc/');
define('DIR_FS_LOG', $root.'/log/');
define('DIR_FS_EXTERNAL', $root.'/includes/external/');
define('TABLE_MANUFACTURERS', 'manufacturers');
define('TABLE_CONFIGURATION', 'configuration');
define('MODULE_GUARANTEE_LABELS_STATUS', $mode === 'modul_aus' ? 'false' : 'true');
define('SHOW_BUTTON_BUY_NOW', $mode === 'kein_button' ? 'false' : 'true');
$_SESSION['language_charset'] = 'UTF-8';

$GLOBALS['man'] = array(1 => 'ACME GmbH');
$GLOBALS['queries'] = 0;
function xtc_db_query($sql) {
  $GLOBALS['queries']++;
  // die Sammelabfrage hat kein IN (): sie holt alle aktiven Hersteller auf einmal
  if (strpos($sql, 'manufacturers_status') !== false && strpos($sql, 'IN (') === false) {
    $rows = array();
    foreach ($GLOBALS['man'] as $id => $name) $rows[] = array('manufacturers_id' => $id, 'manufacturers_name' => $name);
    return $rows;
  }
  preg_match_all("/\d+/", substr($sql, strpos($sql, 'IN (')), $m);
  $rows = array();
  foreach ($m[0] as $id) if (isset($GLOBALS['man'][(int)$id])) $rows[] = array('manufacturers_id' => $id, 'manufacturers_name' => $GLOBALS['man'][(int)$id]);
  return $rows;
}
function xtc_db_fetch_array(&$r) { return array_shift($r); }

// german.php laedt lang/german/extra/ auf jeder Storefrontseite, hier nur die eine Datei
require $guarantee_labels_paths['repo'].'/lang/german/extra/guarantee_labels.php';
require $root.'/includes/modules/product/guarantee_labels_listing.php';

$pass = 0; $fail = 0;
function ok($n, $c, $e = '') { global $pass, $fail; if ($c) { echo "  ok    $n\n"; } else { echo "  FAIL  $n".($e!==''?"  ($e)":'')."\n"; } }

$ext = new guarantee_labels_listing();
$row = array('products_id' => 1, 'products_garan_duration' => '3.0', 'manufacturers_id' => 1, 'products_manufacturers_model' => 'WAU28T20');
$plain = array('products_id' => 1, 'products_garan_duration' => null, 'manufacturers_id' => 1, 'products_manufacturers_model' => '');

echo "\n== $mode ==\n";
$out = $ext->buildDataArray(array('PRODUCTS_NAME' => 'X'), $row, 'thumbnail', true);
ok('Variable immer gesetzt', array_key_exists('GUARANTEE_LABEL', $out));
ok('uebrige Daten unveraendert', $out['PRODUCTS_NAME'] === 'X');

if ($mode === 'aktiv') {
  ok('Label erzeugt', strpos($out['GUARANTEE_LABEL'], 'guarantee-label__compact') !== false);
  ok('Herstellername im Label', strpos($out['GUARANTEE_LABEL'], 'ACME GmbH') !== false);
  $out2 = $ext->buildDataArray(array(), $plain, 'thumbnail', true);
  ok('Artikel ohne GARAN-Daten ohne Label', $out2['GUARANTEE_LABEL'] === '');
  $GLOBALS['queries'] = 0;
  $ext->buildDataArray(array(), $row, 'thumbnail', true);
  ok('Hersteller nur einmal abgefragt', $GLOBALS['queries'] === 0, 'Abfragen: '.$GLOBALS['queries']);
  $GLOBALS['queries'] = 0;
  $ext->buildDataArray(array(), $plain, 'thumbnail', true);
  ok('Artikel ohne GARAN-Daten fragt nicht', $GLOBALS['queries'] === 0);
} else {
  ok('kein Label', $out['GUARANTEE_LABEL'] === '');
  $GLOBALS['queries'] = 0;
  $ext->buildDataArray(array(), $row, 'thumbnail', true);
  ok('keine Nachladeabfrage', $GLOBALS['queries'] === 0);
}

if ($mode === 'aktiv') {
  $info = $ext->buildDataArray(array(), $row, 'info', false);
  ok('Detailseite bleibt der Erweiterungsstelle ueberlassen', $info['GUARANTEE_LABEL'] === '');
}
