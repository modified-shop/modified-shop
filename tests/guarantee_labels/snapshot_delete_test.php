<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Prueft das Aufraeumen: geloeschte Position und geloeschte Bestellung.
error_reporting(E_ALL & ~E_DEPRECATED);
$guarantee_labels_paths = require __DIR__.'/bootstrap.php';
$root = $guarantee_labels_paths['shop'];
$repo = $guarantee_labels_paths['repo'];

define('DIR_FS_CATALOG', $root.'/');
define('DIR_FS_INC', $root.'/inc/');
define('DIR_WS_INCLUDES', 'includes/');
define('DIR_WS_CLASSES', 'includes/classes/');
define('DIR_FS_LOG', $root.'/log/');
define('DIR_FS_EXTERNAL', $root.'/includes/external/');
define('TABLE_MANUFACTURERS', 'manufacturers');
define('TABLE_ORDERS_GUARANTEE', 'orders_guarantee');
define('TABLE_ORDERS_PRODUCTS_GUARANTEE', 'orders_products_guarantee');
define('TABLE_PRODUCTS_CONTENT', 'products_content');
define('MODULE_GUARANTEE_LABELS_STATUS', 'true');

$GLOBALS['tables'] = array('orders_guarantee', 'orders_products_guarantee');
$GLOBALS['queries'] = array();
function xtc_db_query($sql) {
  $GLOBALS['queries'][] = preg_replace('/\s+/', ' ', trim($sql));
  if (strpos($sql, 'SHOW TABLES LIKE') === 0) {
    preg_match("/LIKE '([^']+)'/", $sql, $m);
    $name = str_replace('\\_', '_', $m[1]);
    return in_array($name, $GLOBALS['tables'], true) ? array(array($name)) : array();
  }
  return array();
}
function xtc_db_fetch_array(&$r) { return array_shift($r); }
function xtc_db_num_rows($r) { return count($r); }

require DIR_FS_INC.'guarantee_labels_snapshot.inc.php';

$pass = 0; $fail = 0;
function ok($n, $c, $e = '') { global $pass, $fail; if ($c) { $pass++; echo "  ok    $n\n"; } else { $fail++; echo "  FAIL  $n".($e!==''?"  ($e)":'')."\n"; } }
function deletes() {
  return array_values(array_filter($GLOBALS['queries'], function ($q) { return strpos($q, 'DELETE') === 0; }));
}

echo "== Geloeschte Bestellposition ==\n";
$GLOBALS['queries'] = array();
guarantee_labels_product_snapshot_delete(4711, 99);
$d = deletes();
ok('eine Loeschabfrage', count($d) === 1, print_r($d, true));
// Bestellung und Position kommen beide aus der Anfrage. Der Kern loescht die Position selbst mit
// beiden Spalten; dieser Haken laeuft vorher und muss genauso eng sein, sonst nimmt ein falsches
// Paar den Snapshot einer fremden Bestellung mit, waehrend deren Position stehen bleibt.
ok('nach Bestellung und Position geloescht',
   isset($d[0]) && strpos($d[0], "orders_products_guarantee WHERE orders_id = '4711' AND orders_products_id = '99'") !== false,
   isset($d[0]) ? $d[0] : '');

$GLOBALS['queries'] = array();
guarantee_labels_product_snapshot_delete(4711, 0);
ok('ohne Positionsnummer keine Abfrage', count(deletes()) === 0);

$GLOBALS['queries'] = array();
guarantee_labels_product_snapshot_delete(0, 99);
ok('ohne Bestellnummer keine Abfrage', count(deletes()) === 0);

echo "\n== Ohne installiertes Modul ==\n";
$GLOBALS['tables'] = array();
// der Puffer merkt sich das Ergebnis, deshalb ein eigener Prozess fuer diesen Fall
$out = array();
// der Unterprozess erbt den Arbeitspfad ueber die Umgebung
putenv('GARAN_TEST_SHOP='.$root);
exec(escapeshellcmd(PHP_BINARY).' -r '.escapeshellarg('
  error_reporting(E_ALL & ~E_DEPRECATED);
  $root = getenv("GARAN_TEST_SHOP");
  define("DIR_FS_CATALOG", $root."/");
  define("DIR_FS_INC", $root."/inc/");
  define("DIR_WS_INCLUDES", "includes/");
  define("DIR_WS_CLASSES", "includes/classes/");
  define("DIR_FS_LOG", $root."/log/");
  define("DIR_FS_EXTERNAL", $root."/includes/external/");
  define("TABLE_MANUFACTURERS", "manufacturers");
  define("TABLE_ORDERS_GUARANTEE", "orders_guarantee");
  define("TABLE_ORDERS_PRODUCTS_GUARANTEE", "orders_products_guarantee");
  define("TABLE_PRODUCTS_CONTENT", "products_content");
  define("MODULE_GUARANTEE_LABELS_STATUS", "true");
  $GLOBALS["q"] = array();
  function xtc_db_query($sql) { $GLOBALS["q"][] = $sql; return array(); }
  function xtc_db_fetch_array(&$r) { return array_shift($r); }
  function xtc_db_num_rows($r) { return count($r); }
  require DIR_FS_INC."guarantee_labels_snapshot.inc.php";
  guarantee_labels_product_snapshot_delete(4711, 99);
  $deletes = array_filter($GLOBALS["q"], function ($s) { return strpos($s, "DELETE") === 0; });
  echo count($deletes);
').' 2>&1', $out);
ok('fehlende Tabelle wird nicht geloescht', trim(implode('', $out)) === '0', implode('', $out));

echo "\n== Geloeschte Bestellung ==\n";
$source = file_get_contents($guarantee_labels_paths['repo'].'/inc/xtc_remove_order.inc.php');
ok('GARAN-Block vorhanden', strpos($source, 'TABLE_ORDERS_GUARANTEE') !== false);
ok('beide Tabellen abgedeckt', strpos($source, 'TABLE_ORDERS_PRODUCTS_GUARANTEE') !== false);
ok('per SHOW TABLES abgesichert', preg_match('/guarantee_tables.*?SHOW TABLES LIKE/s', $source) === 1);
ok('nicht vom Modulstatus abhaengig', strpos($source, 'MODULE_GUARANTEE_LABELS_STATUS') === false);

echo "\n----------------------------------------\n";
echo "bestanden: $pass   fehlgeschlagen: $fail\n";
exit($fail > 0 ? 1 : 0);
