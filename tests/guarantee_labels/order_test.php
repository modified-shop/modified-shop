<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Prueft die Klassenerweiterung der Bestellklasse, die das Label in den Checkout bringt.
error_reporting(E_ALL & ~E_DEPRECATED);
$guarantee_labels_paths = require __DIR__.'/bootstrap.php';
$root = $guarantee_labels_paths['shop'];
$repo = $guarantee_labels_paths['repo'];
$mode = isset($argv[1]) ? $argv[1] : 'driver';

if ($mode === 'driver') {
  $pass = 0; $fail = 0;
  foreach (array('aktiv', 'modul_aus') as $case) {
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
$_SESSION['language_charset'] = 'UTF-8';
$_SESSION['language'] = 'german';

$GLOBALS['man'] = array(1 => 'ACME GmbH');
$GLOBALS['queries'] = 0;
function xtc_db_query($sql) {
  $GLOBALS['queries']++;
  preg_match_all("/\d+/", substr($sql, strpos($sql, 'IN (')), $m);
  $rows = array();
  foreach ($m[0] as $id) if (isset($GLOBALS['man'][(int)$id])) $rows[] = array('manufacturers_id' => $id, 'manufacturers_name' => $GLOBALS['man'][(int)$id]);
  return $rows;
}
function xtc_db_fetch_array(&$r) { return array_shift($r); }

require $guarantee_labels_paths['repo'].'/lang/german/extra/guarantee_labels.php';
require $root.'/includes/modules/order/guarantee_labels_order.php';

$pass = 0; $fail = 0;
function ok($n, $c, $e = '') { if ($c) { echo "  ok    $n\n"; } else { echo "  FAIL  $n".($e!==''?"  ($e)":'')."\n"; } }

$ext = new guarantee_labels_order();
// so liegen die Werte im Warenkorb: der products_ Praefix ist entfernt
$row = array('name' => 'Waschmaschine', 'garan_duration' => '3.0', 'manufacturers_id' => 1, 'manufacturers_model' => 'WAU28T20');

echo "\n== $mode ==\n";
$out = $ext->cart_products($row, 1);
ok('Variable immer gesetzt', array_key_exists('GUARANTEE_LABEL', $out));
ok('uebrige Daten unveraendert', $out['name'] === 'Waschmaschine');

if ($mode === 'aktiv') {
  ok('Label erzeugt', strpos($out['GUARANTEE_LABEL'], 'guarantee-label__compact') !== false);
  ok('Herstellername im Label', strpos($out['GUARANTEE_LABEL'], 'ACME GmbH') !== false);
  $plain = $ext->cart_products(array('garan_duration' => '2.0', 'manufacturers_id' => 1, 'manufacturers_model' => 'X'), 1);
  ok('zwei Jahre ergeben kein Label', $plain['GUARANTEE_LABEL'] === '');
  $none = $ext->cart_products(array('name' => 'Gutschein'), 2);
  ok('Artikel ohne GARAN-Felder ohne Label', $none['GUARANTEE_LABEL'] === '');
  $GLOBALS['queries'] = 0;
  $ext->cart_products($row, 1);
  ok('Hersteller nur einmal abgefragt', $GLOBALS['queries'] === 0, 'Abfragen: '.$GLOBALS['queries']);
} else {
  ok('kein Label', $out['GUARANTEE_LABEL'] === '');
  $GLOBALS['queries'] = 0;
  $ext->cart_products($row, 1);
  ok('keine Nachladeabfrage', $GLOBALS['queries'] === 0);
}
