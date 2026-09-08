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

// Artikel 1 ist gemischt. Download ist genau die Kombination Option 1 mit Wert 5; derselbe
// Wert 5 unter Option 2 ist koerperlich, weil options_values_id eine gemeinsame Liste ist.
$GLOBALS['downloads'] = array('1:5');
function xtc_db_query($sql) {
  // die Artikelfrage: alle Attributwerte und davon die Downloads, in einer Abfrage
  if (strpos($sql, 'AS downloads') !== false) {
    return array(array('total' => 2, 'downloads' => count($GLOBALS['downloads'])));
  }
  // die Positionsfrage: wie viele der gewaehlten Kombinationen sind Downloads
  if (strpos($sql, 'products_attributes_download') !== false) {
    preg_match_all("/options_id = '(\d+)'\s*AND pa\.options_values_id = '(\d+)'/", $sql, $m);
    $total = 0;

    foreach ($m[1] as $index => $option) {
      if (in_array($option.':'.$m[2][$index], $GLOBALS['downloads'], true)) $total++;
    }

    return array(array('total' => $total));
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
// Der Artikel wird ohne Auswahl gefragt: Er hat eine koerperliche Variante, gilt also als
// gemischt und bekommt in der Liste ein Label. DOWNLOAD_MULTIPLE_ATTRIBUTES_ALLOWED entscheidet
// erst fuer eine gewaehlte Kombination.
ok('gemischter Artikel bekommt in der Liste ein Label', guarantee_labels_candidate($row) === true);
ok('ohne Attribute im Warenkorb entscheidet der Artikel', guarantee_labels_candidate($row, '1') === true);
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

// options_values_id ist eine gemeinsame Liste: derselbe Wert kann unter zwei Optionen stehen.
// Frueher fasste array_unique() beide zu einer Wahl zusammen, und eine gemischte Position galt
// als digital.
if ($mode !== 'downloads_aus') {
  ok('zwei Optionen mit demselben Wert zaehlen doppelt',
     guarantee_labels_candidate($row, '1{1}5{2}5') === ($mode !== 'mehrfach'),
     $mode);
}

// Dieselbe Frage an die Abfrage: Sie muss Option und Wert zusammen pruefen. Wer nur den Wert
// vergleicht, zaehlt hier den Download der Option 1 mit und nimmt der Position ihr Label.
ok('Download einer anderen Option zaehlt nicht mit', guarantee_labels_candidate($row, '1{2}5') === true, $mode);

ok('Position ohne GARAN-Daten bleibt ohne Label',
   guarantee_labels_candidate(array('products_id' => 1, 'products_garan_duration' => null,
                                    'manufacturers_id' => 1, 'products_manufacturers_model' => 'X'), '1{1}6') === false);

// Der fruehere Fehler entstand nicht in guarantee_labels_candidate(), sondern erst danach:
// guarantee_labels_product_label() fragte ohne Warenkorbkennung noch einmal und fiel dabei auf
// den Artikel zurueck. Der Weg bis zur fertigen Grafik muss deshalb mitgeprueft werden.
echo "\n-- ueber guarantee_labels_product_label() --\n";
$names = guarantee_labels_manufacturer_names(array(1));

ok('Artikel ohne Auswahl liefert ein Label', is_array(guarantee_labels_product_label($row, $names)));
ok('gewaehlte koerperliche Variante liefert ein Label', is_array(guarantee_labels_product_label($row, $names, '1{1}6')));

if ($mode === 'downloads_aus') {
  ok('ohne Downloads liefert jede Variante ein Label', is_array(guarantee_labels_product_label($row, $names, '1{1}5')));
} else {
  ok('reine Downloadvariante liefert kein Label', guarantee_labels_product_label($row, $names, '1{1}5') === false);
}

if ($mode === 'mehrfach') {
  ok('gemischte Auswahl liefert kein Label', guarantee_labels_product_label($row, $names, '1{1}5{2}6') === false);
} else {
  ok('gemischte Auswahl liefert ein Label', is_array(guarantee_labels_product_label($row, $names, '1{1}5{2}6')));
}

echo "\n----------------------------------------\nbestanden: $pass   fehlgeschlagen: $fail\n";
exit($fail > 0 ? 1 : 0);
