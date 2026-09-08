<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Prueft, dass ein Umschalten von DOWNLOAD_MULTIPLE_ATTRIBUTES_ALLOWED eine abgeschlossene
// Bestellung nicht nachtraeglich umstuft und eine manuell angelegte weiterhin mitgeht.
error_reporting(E_ALL & ~E_DEPRECATED);
$guarantee_labels_paths = require __DIR__.'/bootstrap.php';
$root = $guarantee_labels_paths['shop'];
$repo = $guarantee_labels_paths['repo'];
$mode = isset($argv[1]) ? $argv[1] : 'driver';

// Die Einstellung steht nur einmal je Prozess fest, deshalb laeuft jeder Fall fuer sich.
if ($mode === 'driver') {
  $pass = 0; $fail = 0;
  foreach (array('einfach', 'mehrfach', 'downloads_aus', 'mehrfach_aus') as $case) {
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
define('DIR_FS_DOCUMENT_ROOT', $root.'/');
define('DIR_WS_CATALOG', '/');
define('DIR_FS_INC', $root.'/inc/');
define('DIR_WS_INCLUDES', 'includes/');
define('DIR_WS_CLASSES', 'includes/classes/');
define('DIR_FS_LOG', $root.'/log/');
define('DIR_FS_EXTERNAL', $root.'/includes/external/');
define('TABLE_MANUFACTURERS', 'manufacturers');
define('TABLE_ORDERS', 'orders');
define('TABLE_ORDERS_GUARANTEE', 'orders_guarantee');
define('TABLE_ORDERS_PRODUCTS', 'orders_products');
define('TABLE_ORDERS_PRODUCTS_ATTRIBUTES', 'orders_products_attributes');
define('TABLE_ORDERS_PRODUCTS_DOWNLOAD', 'orders_products_download');
define('TABLE_ORDERS_PRODUCTS_GUARANTEE', 'orders_products_guarantee');
define('TABLE_PRODUCTS_CONTENT', 'products_content');
define('MODULE_GUARANTEE_LABELS_STATUS', 'true');
$aus = ($mode === 'downloads_aus' || $mode === 'mehrfach_aus');
define('DOWNLOAD_ENABLED', $aus ? 'false' : 'true');
if ($mode === 'mehrfach' || $mode === 'mehrfach_aus') define('DOWNLOAD_MULTIPLE_ATTRIBUTES_ALLOWED', 'true');
$_SESSION['language_charset'] = 'UTF-8';

$garan_hash = str_repeat('c', 64);
$garan_dir = $root.'/media/guarantee_labels/archive/garan/'.$garan_hash.'/';
@mkdir($garan_dir, 0777, true);
file_put_contents($garan_dir.'colour.svg', '<svg id="colour"></svg>');
file_put_contents($garan_dir.'nested.svg', '<svg id="nested"></svg>');

$terms_hash = hash('sha256', 'BEDINGUNGEN');
$terms_dir = $root.'/media/guarantee_labels/archive/terms/'.$terms_hash.'/';
@mkdir($terms_dir, 0777, true);
file_put_contents($terms_dir.'garantie.pdf', 'BEDINGUNGEN');

// 8001 kommt aus dem Checkout und traegt seine Einstufung, 8002 wurde manuell angelegt.
// Beide fuehren dieselbe Position: zwei Attribute, davon eines mit Downloadzeile.
$GLOBALS['tables'] = array('orders_guarantee', 'orders_products_guarantee');
$GLOBALS['content_types'] = array(8001 => 'mixed', 8002 => '');
$GLOBALS['positions'] = array(
  8001 => array(array('orders_products_id' => '81', 'attributes' => '2', 'downloads' => '1')),
  8002 => array(array('orders_products_id' => '82', 'attributes' => '2', 'downloads' => '1')),
);
$GLOBALS['snapshots'] = array(
  8001 => array(array('orders_products_id' => '81', 'manufacturers_name' => 'ACME GmbH', 'manufacturers_model' => 'X-1',
                      'garan_duration' => '3.0', 'garan_hash' => $garan_hash,
                      'terms_hash' => $terms_hash, 'terms_filename' => 'garantie.pdf')),
  8002 => array(array('orders_products_id' => '82', 'manufacturers_name' => 'ACME GmbH', 'manufacturers_model' => 'X-1',
                      'garan_duration' => '3.0', 'garan_hash' => $garan_hash,
                      'terms_hash' => $terms_hash, 'terms_filename' => 'garantie.pdf')),
);

function xtc_db_query($sql) {
  preg_match("/orders_id = '(\d+)'/", $sql, $m);
  $oid = isset($m[1]) ? (int)$m[1] : 0;
  if (strpos($sql, 'SHOW TABLES LIKE') === 0) {
    preg_match("/LIKE '([^']+)'/", $sql, $m);
    return in_array(str_replace('\_', '_', $m[1]), $GLOBALS['tables'], true) ? array(array(1)) : array();
  }
  if (strpos($sql, 'SELECT language') !== false)     return array(array('language' => 'german'));
  if (strpos($sql, 'SELECT content_type') !== false) return array(array('content_type' => isset($GLOBALS['content_types'][$oid]) ? $GLOBALS['content_types'][$oid] : ''));
  if (strpos($sql, 'AS downloads') !== false)        return isset($GLOBALS['positions'][$oid]) ? $GLOBALS['positions'][$oid] : array();
  return isset($GLOBALS['snapshots'][$oid]) ? $GLOBALS['snapshots'][$oid] : array();
}
function xtc_db_fetch_array(&$r) { return array_shift($r); }
function xtc_db_num_rows($r) { return count($r); }

require DIR_FS_INC.'guarantee_labels_order.inc.php';

$pass = 0; $fail = 0;
function ok($n, $c, $e = '') { global $pass, $fail; if ($c) { $pass++; echo "  ok    $n\n"; } else { $fail++; echo "  FAIL  $n".($e!==''?"  ($e)":'')."\n"; } }

echo "\n== $mode ==\n";

// Die Checkout-Bestellung darf sich nie aendern: sonst schriebe ein Umschalten der Einstellung
// die Geschichte einer abgeschlossenen Bestellung um.
$checkout = guarantee_labels_order_products(8001);
ok('Checkout-Bestellung behaelt ihren Snapshot', isset($checkout[81]));
ok('Checkout-Bestellung behaelt ihren Anhang', count(guarantee_labels_order_terms(8001)) === 1);
ok('Checkout-Bestellung bleibt Ware', guarantee_labels_order_physical(8001) === true);

// Die manuell angelegte Bestellung wird weiter aus ihren Positionen eingestuft, damit ein im
// Admin ergaenztes Downloadattribut sofort wirkt.
// Nur bei eingeschalteten Downloads und erlaubten Mehrfachdownloads faellt die gemischte
// Position weg. Sind Downloads abgeschaltet, ist jede Position Ware und eine liegengebliebene
// Downloadzeile darf die Zusage nicht wegnehmen; das folgt shopping_cart::get_content_type().
$manuell = guarantee_labels_order_products(8002);
$erwartet = ($mode !== 'mehrfach');
ok('manuelle Bestellung folgt ihren Positionen', isset($manuell[82]) === $erwartet);
ok('Anhang folgt derselben Entscheidung', (count(guarantee_labels_order_terms(8002)) === 1) === $erwartet);
ok('Hinweis folgt derselben Entscheidung', guarantee_labels_order_physical(8002) === $erwartet);

// Die Bearbeitung fragt dieselbe Funktion. Ein vorhandener Snapshot der gemischten Position
// bleibt korrigierbar, auch wenn die heutige Einstellung sie als digital einstufen wuerde.
ok('vorhandener Snapshot bleibt bearbeitbar', guarantee_labels_order_position_goods(8001, 81) === true);

// Eine gemischte Bestellung besteht aus Positionen unterschiedlicher Art. Fuer eine Position
// ohne Snapshot sagt content_type nichts aus, sie muss selbst antworten.
$GLOBALS['content_types'][8004] = 'mixed';
$GLOBALS['positions'][8004] = array(array('orders_products_id' => '84', 'attributes' => '1', 'downloads' => '1'),
                                    array('orders_products_id' => '85', 'attributes' => '0', 'downloads' => '0'));
$GLOBALS['snapshots'][8004] = array();
$erwartet_neu = ($mode === 'downloads_aus' || $mode === 'mehrfach_aus');
ok('neue Zusage folgt der reinen Downloadposition', guarantee_labels_order_position_goods(8004, 84) === $erwartet_neu);
ok('koerperliche Position derselben Bestellung bleibt erlaubt', guarantee_labels_order_position_goods(8004, 85) === true);

// Ein Snapshot ohne Position darf nie in eine Mail geraten
$GLOBALS['snapshots'][8003] = $GLOBALS['snapshots'][8001];
$GLOBALS['positions'][8003] = array();
$GLOBALS['content_types'][8003] = 'physical';
ok('verwaister Snapshot faellt heraus', guarantee_labels_order_products(8003) === array());
ok('verwaister Snapshot ohne Anhang', guarantee_labels_order_terms(8003) === array());

echo "\nbestanden: $pass   fehlgeschlagen: $fail\n";
exit($fail > 0 ? 1 : 0);
