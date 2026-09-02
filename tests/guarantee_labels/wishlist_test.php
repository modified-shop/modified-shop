<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Prueft die Erweiterungsstelle des Merkzettels. Er fuehrt direkt in den Warenkorb und
// bekommt deshalb dasselbe kompakte Label wie eine Artikelliste.
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
define('DIR_WS_CATALOG', '/');
define('DIR_FS_INC', $root.'/inc/');
define('DIR_WS_INCLUDES', 'includes/');
define('DIR_WS_CLASSES', 'includes/classes/');
define('DIR_FS_LOG', $root.'/log/');
define('DIR_FS_EXTERNAL', $root.'/includes/external/');
define('TABLE_MANUFACTURERS', 'manufacturers');
define('TABLE_PRODUCTS_ATTRIBUTES', 'products_attributes');
define('TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD', 'products_attributes_download');
define('MODULE_GUARANTEE_LABELS_STATUS', $mode === 'modul_aus' ? 'false' : 'true');
define('DOWNLOAD_ENABLED', 'true');
define('DOWNLOAD_MULTIPLE_ATTRIBUTES_ALLOWED', 'false');
$_SESSION['language_charset'] = 'UTF-8';

// Wert 5 ist ein Download, Wert 6 koerperlich
$GLOBALS['downloads'] = array(5);
$GLOBALS['man'] = array(1 => 'ACME GmbH');
$GLOBALS['queries'] = 0;
function xtc_db_query($sql) {
  $GLOBALS['queries']++;
  if (strpos($sql, 'AS downloads') !== false) {
    return array(array('total' => 2, 'downloads' => count($GLOBALS['downloads'])));
  }
  if (strpos($sql, 'products_attributes_download') !== false) {
    preg_match_all("/'(\d+)'/", $sql, $m);
    $ids = array_map('intval', $m[1]);
    array_shift($ids); // products_id
    return array(array('total' => count(array_intersect($ids, $GLOBALS['downloads']))));
  }
  preg_match_all("/\d+/", substr($sql, strpos($sql, 'IN (')), $m);
  $rows = array();
  foreach ($m[0] as $id) if (isset($GLOBALS['man'][(int)$id])) $rows[] = array('manufacturers_id' => $id, 'manufacturers_name' => $GLOBALS['man'][(int)$id]);
  return $rows;
}
function xtc_db_fetch_array(&$r) { return array_shift($r); }
function xtc_db_num_rows($r) { return count($r); }

require $repo.'/lang/german/extra/guarantee_labels.php';

$pass = 0; $fail = 0;
function ok($n, $c, $e = '') { global $pass, $fail; if ($c) { $pass++; echo "  ok    $n\n"; } else { $fail++; echo "  FAIL  $n".($e!==''?"  ($e)":'')."\n"; } }

// die Felder, die shoppingCart ueber ADD_SELECT_CART liefert und der Merkzettel voranstellt
function eintrag($id, $dauer = '3.0') {
  return array(
    'PRODUCTS_ID' => $id,
    'PRODUCTS_NAME' => 'Waschmaschine',
    'PRODUCTS_GARAN_DURATION' => $dauer,
    'PRODUCTS_MANUFACTURERS_MODEL' => 'WAU28T20',
    'PRODUCTS_MANUFACTURERS_ID' => 1,
  );
}

echo "\n== $mode ==\n";
$module_data = array(0 => eintrag('1'));
$i = 0;
require $root.'/includes/extra/modules/wishlist_content/guarantee_labels.php';
ok('Variable immer gesetzt', array_key_exists('GUARANTEE_LABEL', $module_data[0]));
ok('uebrige Daten unveraendert', $module_data[0]['PRODUCTS_NAME'] === 'Waschmaschine');

if ($mode === 'modul_aus') {
  ok('inaktives Modul liefert kein Label', $module_data[0]['GUARANTEE_LABEL'] === '');
  echo "\nbestanden: $pass   fehlgeschlagen: $fail\n";
  exit($fail > 0 ? 1 : 0);
}

ok('kompaktes Label erzeugt', strpos($module_data[0]['GUARANTEE_LABEL'], 'guarantee-label__compact') !== false);
ok('Herstellername im Label', strpos($module_data[0]['GUARANTEE_LABEL'], 'ACME GmbH') !== false);

// ohne GARAN-Daten kein Label
$module_data = array(0 => eintrag('1', null));
require $root.'/includes/extra/modules/wishlist_content/guarantee_labels.php';
ok('Artikel ohne GARAN-Daten ohne Label', $module_data[0]['GUARANTEE_LABEL'] === '');

// zwei Jahre begruenden keine gewerbliche Zusage
$module_data = array(0 => eintrag('1', '2.0'));
require $root.'/includes/extra/modules/wishlist_content/guarantee_labels.php';
ok('zwei Jahre ohne Label', $module_data[0]['GUARANTEE_LABEL'] === '');

// Der Merkzettel haelt Warenkorbkennungen. Eine reine Downloadvariante bekommt kein Label,
// die koerperliche behaelt es.
$module_data = array(0 => eintrag('1{1}5'));
require $root.'/includes/extra/modules/wishlist_content/guarantee_labels.php';
ok('reine Downloadvariante ohne Label', $module_data[0]['GUARANTEE_LABEL'] === '');

$module_data = array(0 => eintrag('1{1}6'));
require $root.'/includes/extra/modules/wishlist_content/guarantee_labels.php';
ok('gewaehlte koerperliche Variante mit Label', strpos($module_data[0]['GUARANTEE_LABEL'], 'guarantee-label__compact') !== false);

echo "\nbestanden: $pass   fehlgeschlagen: $fail\n";
exit($fail > 0 ? 1 : 0);
