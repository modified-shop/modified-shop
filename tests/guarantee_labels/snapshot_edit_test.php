<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Prueft die Korrektur eines Bestellsnapshots: Pruefung, Schreiben, Loeschen, Protokoll.
error_reporting(E_ALL & ~E_DEPRECATED);
$guarantee_labels_paths = require __DIR__.'/bootstrap.php';
$root = $guarantee_labels_paths['shop'];
$repo = $guarantee_labels_paths['repo'];
$repo = $guarantee_labels_paths['repo'];

define('DIR_FS_CATALOG', $root.'/');
define('DIR_FS_INC', $root.'/inc/');
define('DIR_WS_INCLUDES', 'includes/');
define('DIR_WS_CLASSES', 'includes/classes/');
define('DIR_FS_LOG', $root.'/log/');
define('DIR_FS_EXTERNAL', $root.'/includes/external/');
define('TABLE_MANUFACTURERS', 'manufacturers');
define('TABLE_ORDERS', 'orders');
define('TABLE_ORDERS_STATUS_HISTORY', 'orders_status_history');
define('TABLE_ORDERS_GUARANTEE', 'orders_guarantee');
define('TABLE_ORDERS_PRODUCTS_GUARANTEE', 'orders_products_guarantee');
define('TABLE_PRODUCTS', 'products');
define('TABLE_ORDERS_PRODUCTS', 'orders_products');
define('TABLE_PRODUCTS_CONTENT', 'products_content');
define('MODULE_GUARANTEE_LABELS_STATUS', 'true');
$_SESSION['language_charset'] = 'UTF-8';
require $repo.'/inc/html_encoding.php';
require $repo.'/lang/german/extra/admin/guarantee_labels.php';

$GLOBALS['rows'] = array();
$GLOBALS['existing'] = array();
function xtc_db_query($sql) {
  if (strpos($sql, 'orders_products_guarantee_id') !== false) {
    preg_match("/orders_products_id = '(\d+)'/", $sql, $m);
    return isset($GLOBALS['existing'][(int)$m[1]]) ? array(array('orders_products_guarantee_id' => 1)) : array();
  }
  if (strpos($sql, 'orders_status') !== false) return array(array('orders_status' => 2));
  // die Zuordnungspruefung: gehoert die Position zur Bestellung?
  if (strpos($sql, 'SELECT orders_products_id') === 0) {
    preg_match("/orders_products_id = '(\d+)'/", $sql, $m);
    return (isset($m[1]) && (int)$m[1] !== 99) ? array(array('orders_products_id' => (int)$m[1])) : array();
  }
  if (strpos($sql, 'DELETE') === 0) { $GLOBALS['rows']['delete'][] = $sql; return array(); }
  return array();
}
function xtc_db_fetch_array(&$r) { return array_shift($r); }
function xtc_db_num_rows($r) { return count($r); }
function xtc_db_perform($table, $data, $mode = 'insert', $where = '') {
  $GLOBALS['rows'][$table][] = array('mode' => $mode, 'data' => $data, 'where' => $where);
}

require DIR_FS_INC.'guarantee_labels_snapshot.inc.php';

$pass = 0; $fail = 0;
function ok($n, $c, $e = '') { global $pass, $fail; if ($c) { $pass++; echo "  ok    $n\n"; } else { $fail++; echo "  FAIL  $n".($e!==''?"  ($e)":'')."\n"; } }

echo "== Pruefung der Eingaben ==\n";
$r = guarantee_labels_validate_snapshot(array('manufacturers_name' => 'ACME GmbH', 'manufacturers_model' => 'X-1', 'garan_duration' => '3'));
ok('gueltige Werte angenommen', is_array($r['values']) && $r['values']['garan_duration'] === '3.0' && count($r['errors']) === 0, print_r($r, true));
$r = guarantee_labels_validate_snapshot(array('manufacturers_name' => '', 'manufacturers_model' => '', 'garan_duration' => ''));
ok('alles leer entfernt die Zusage', $r['values'] === false && count($r['errors']) === 0);
$r = guarantee_labels_validate_snapshot(array('manufacturers_name' => 'ACME GmbH', 'manufacturers_model' => 'X-1', 'garan_duration' => '2'));
ok('zwei Jahre abgelehnt', $r['values'] === false && count($r['errors']) === 1);
$r = guarantee_labels_validate_snapshot(array('manufacturers_name' => '', 'manufacturers_model' => 'X-1', 'garan_duration' => '3'));
ok('Hersteller ist Pflicht', $r['values'] === false && count($r['errors']) === 1);
$r = guarantee_labels_validate_snapshot(array('manufacturers_name' => 'ACME GmbH', 'manufacturers_model' => '', 'garan_duration' => '3'));
ok('Modellkennung ist Pflicht', $r['values'] === false);
$r = guarantee_labels_validate_snapshot(array('manufacturers_name' => str_repeat('X', 200), 'manufacturers_model' => 'X-1', 'garan_duration' => '3'));
ok('zu breiter Herstellername abgelehnt', $r['values'] === false && count($r['errors']) === 1);
$r = guarantee_labels_validate_snapshot(array('manufacturers_name' => 'ACME GmbH', 'manufacturers_model' => 'X-1', 'garan_duration' => '4,5'));
ok('Komma als Trenner erlaubt', is_array($r['values']) && $r['values']['garan_duration'] === '4.5');

echo "\n== Schreiben ==\n";
$GLOBALS['rows'] = array();
$errors = guarantee_labels_write_snapshot(4711, 10, array('manufacturers_name' => 'ACME GmbH', 'manufacturers_model' => 'X-1', 'garan_duration' => '3.0'));
ok('ohne Fehler geschrieben', count($errors) === 0, print_r($errors, true));
$w = $GLOBALS['rows']['orders_products_guarantee'][0];
ok('neue Zeile eingefuegt', $w['mode'] === 'insert' && $w['data']['orders_products_id'] === 10);
ok('Hash gesetzt', strlen($w['data']['garan_hash']) === 64);
ok('ohne Anhang bleibt terms leer', $w['data']['terms_hash'] === 'null');
ok('Grafik archiviert', is_file($root.'/media/guarantee_labels/archive/garan/'.$w['data']['garan_hash'].'/colour.svg'));

$GLOBALS['existing'][11] = true;
$GLOBALS['rows'] = array();
guarantee_labels_write_snapshot(4711, 11, array('manufacturers_name' => 'ACME GmbH', 'manufacturers_model' => 'X-2', 'garan_duration' => '5.0'));
$w = $GLOBALS['rows']['orders_products_guarantee'][0];
ok('vorhandene Zeile aktualisiert', $w['mode'] === 'update' && strpos($w['where'], "orders_products_id = '11'") !== false);
ok('Aktualisierung ohne terms-Spalten', !isset($w['data']['terms_hash']));

$GLOBALS['rows'] = array();
guarantee_labels_write_snapshot(4711, 11, false);
ok('leere Kerndaten loeschen die Zeile', isset($GLOBALS['rows']['delete']) && strpos($GLOBALS['rows']['delete'][0], "orders_products_id = '11'") !== false);
ok('dabei nichts geschrieben', !isset($GLOBALS['rows']['orders_products_guarantee']));

echo "\n== Fremde Position ==\n";
$GLOBALS['rows'] = array();
$errors = guarantee_labels_write_snapshot(4711, 99, array('manufacturers_name' => 'ACME GmbH', 'manufacturers_model' => 'X-1', 'garan_duration' => '3.0'));
ok('Position einer anderen Bestellung abgelehnt', count($errors) === 1);
ok('dabei nichts geschrieben', !isset($GLOBALS['rows']['orders_products_guarantee']));
ok('und nichts geloescht', !isset($GLOBALS['rows']['delete']));

echo "\n== Protokoll ==\n";
$GLOBALS['rows'] = array();
guarantee_labels_snapshot_history(4711, 10,
  array('manufacturers_name' => 'ACME GmbH', 'manufacturers_model' => 'X-1', 'garan_duration' => '3.0', 'terms_filename' => null),
  array('manufacturers_name' => 'ACME AG', 'manufacturers_model' => 'X-1', 'garan_duration' => '5.0'));
$h = $GLOBALS['rows']['orders_status_history'][0]['data'];
ok('Eintrag geschrieben', isset($h['comments']));
ok('alter und neuer Wert genannt', strpos($h['comments'], 'ACME GmbH') !== false && strpos($h['comments'], 'ACME AG') !== false);
ok('unveraendertes Feld nicht genannt', strpos($h['comments'], 'X-1') === false);
ok('Kunde nicht benachrichtigt', $h['customer_notified'] === '0');
ok('Bestellstatus uebernommen', $h['orders_status_id'] === 2);
ok('Kommentar ohne Entities', strpos($h['comments'], '&auml;') === false && strpos($h['comments'], '&quot;') === false, $h['comments']);
ok('Umlaut aufgeloest', strpos($h['comments'], 'geändert') !== false, $h['comments']);

$GLOBALS['rows'] = array();
guarantee_labels_snapshot_history(4711, 10,
  array('manufacturers_name' => 'ACME GmbH', 'manufacturers_model' => 'X-1', 'garan_duration' => '3.0', 'terms_filename' => null),
  array('manufacturers_name' => 'ACME GmbH', 'manufacturers_model' => 'X-1', 'garan_duration' => '3.0'));
ok('ohne Aenderung kein Eintrag', !isset($GLOBALS['rows']['orders_status_history']));

echo "\n----------------------------------------\n";
echo "bestanden: $pass   fehlgeschlagen: $fail\n";
exit($fail > 0 ? 1 : 0);
