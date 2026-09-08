<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Prueft den Admin-Weg: ohne vorgeladene Storefront-Sprachdatei und mit der Kundengruppe
// der Bestellung statt der Sitzung des Admins.
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
define('TABLE_ORDERS_GUARANTEE', 'orders_guarantee');
define('TABLE_ORDERS_PRODUCTS_GUARANTEE', 'orders_products_guarantee');
define('TABLE_PRODUCTS_CONTENT', 'products_content');
define('MODULE_GUARANTEE_LABELS_STATUS', 'true');
define('MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS', '3,4');
// wie im Admin: die Sitzung gehoert dem Administrator, nicht dem Kunden
$_SESSION['customers_status']['customers_status_id'] = 0;
$_SESSION['language'] = 'german';
$_SESSION['language_charset'] = 'UTF-8';

$GLOBALS['rows'] = array();
$GLOBALS['orders_guarantee'] = array();
function xtc_db_query($sql) {
  // die Schreibwege fragen jetzt wie die Lesewege zuerst nach der Tabelle
  if (strpos($sql, 'SHOW TABLES LIKE') === 0) {
    return isset($GLOBALS['tabellen_fehlen']) ? array() : array(array(1));
  }
  if (strpos($sql, 'orders_guarantee_id') !== false) {
    preg_match("/orders_id = '(\d+)'/", $sql, $m);
    return isset($GLOBALS['orders_guarantee'][(int)$m[1]]) ? array(array('orders_guarantee_id' => 1)) : array();
  }
  return array();
}
function xtc_db_fetch_array(&$r) { return array_shift($r); }
function xtc_db_num_rows($r) { return count($r); }
function xtc_db_perform($table, $data) {
  $GLOBALS['rows'][$table][] = $data;
  if ($table === 'orders_guarantee') $GLOBALS['orders_guarantee'][(int)$data['orders_id']] = true;
}

require DIR_FS_INC.'guarantee_labels_snapshot.inc.php';

$pass = 0; $fail = 0;
function ok($n, $c, $e = '') { global $pass, $fail; if ($c) { $pass++; echo "  ok    $n\n"; } else { $fail++; echo "  FAIL  $n".($e!==''?"  ($e)":'')."\n"; } }

echo "== Sprachtexte im Admin ==\n";
ok('Storefronttexte vorher nicht geladen', !defined('TEXT_GUARANTEE_NOTICE_MAIL'));
$texts = guarantee_labels_notice_texts('german');
ok('Texte werden nachgeladen', is_array($texts) && $texts['text'] !== '');
ok('Ueberschrift kommt mit', is_array($texts) && isset($texts['title']) && $texts['title'] !== '');
ok('Konstante jetzt da', defined('TEXT_GUARANTEE_NOTICE_MAIL'));
ok('zweite Sprache liefert nichts statt falscher Texte', guarantee_labels_notice_texts('english') === false);
ok('Pfadanteil abgelehnt', guarantee_labels_notice_texts('../german') === false);

echo "\n== Kundengruppe der Bestellung ==\n";
$GLOBALS['rows'] = array();
ok('B2C-Kunde bekommt den Snapshot', guarantee_labels_notice_snapshot(5001, 'german', 1) === true);
ok('B2B-Kunde bekommt keinen', guarantee_labels_notice_snapshot(5002, 'german', 3) === false);
ok('zweite B2B-Gruppe ebenso', guarantee_labels_notice_snapshot(5003, 'german', 4) === false);
ok('nur eine Zeile geschrieben', count($GLOBALS['rows']['orders_guarantee']) === 1);
ok('ohne Angabe zaehlt die Sitzung', guarantee_labels_notice_snapshot(5004, 'german') === true);

echo "\n----------------------------------------\n";
echo "bestanden: $pass   fehlgeschlagen: $fail\n";
exit($fail > 0 ? 1 : 0);
