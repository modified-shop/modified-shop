<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Eine Bestellung wird in ihrer eigenen Sprache beschriftet. Blaettert der Kunde in einer
// anderen Sprache, duerfen Label und historischer Hinweis trotzdem nicht verschwinden.
error_reporting(E_ALL & ~E_DEPRECATED);
$guarantee_labels_paths = require __DIR__.'/bootstrap.php';
$root = $guarantee_labels_paths['shop'];
$repo = $guarantee_labels_paths['repo'];
$mode = isset($argv[1]) ? $argv[1] : 'driver';

if ($mode === 'driver') {
  $pass = 0; $fail = 0;
  foreach (array('gleiche_sprache', 'fremde_sprache') as $case) {
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
define('TABLE_ORDERS', 'orders');
define('TABLE_ORDERS_PRODUCTS', 'orders_products');
define('TABLE_PRODUCTS_ATTRIBUTES', 'products_attributes');
define('TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD', 'products_attributes_download');
define('TABLE_ORDERS_PRODUCTS_ATTRIBUTES', 'orders_products_attributes');
define('TABLE_ORDERS_PRODUCTS_DOWNLOAD', 'orders_products_download');
define('TABLE_ORDERS_GUARANTEE', 'orders_guarantee');
define('TABLE_ORDERS_PRODUCTS_GUARANTEE', 'orders_products_guarantee');
define('TABLE_MANUFACTURERS', 'manufacturers');
define('MODULE_GUARANTEE_LABELS_STATUS', 'true');
define('MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS', '');

// Der Kunde blaettert englisch, die Bestellung ist deutsch.
$_SESSION['language'] = ($mode === 'fremde_sprache') ? 'english' : 'german';
require $root.'/lang/'.$_SESSION['language'].'/extra/guarantee_labels.php';
require $root.'/inc/html_encoding.php';

// Archiv der Bestellung: deutscher Stand
require $root.'/includes/classes/guarantee_labels_archive.php';
$archive = new guarantee_labels_archive();
// eigener Hash je Lauf, damit ein Archiv aus einem frueheren Lauf nicht gewinnt
$notice_hash = hash('sha256', 'notice-'.$mode.'-'.getmypid());
$archive->notice_write($notice_hash, array(
  'notice.svg' => '<svg id="notice"></svg>',
  'notice.json' => json_encode(array(
    'text' => 'Historischer deutscher Gewaehrleistungstext.',
    'link' => 'Mehr erfahren',
    'url' => 'https://europa.eu/youreurope/garantien',
    'title' => 'Gesetzliche Gewaehrleistung',
  )),
));
$garan_hash = hash('sha256', 'garan-'.$mode.'-'.getmypid());
$archive->garan_write($garan_hash, array('colour.svg' => '<svg id="colour"></svg>',
                                         'nested.svg' => '<svg id="nested"></svg>'));

$GLOBALS['tables'] = array('orders_guarantee', 'orders_products_guarantee');
function xtc_db_query($sql) {
  global $notice_hash, $garan_hash;
  if (strpos($sql, 'SHOW TABLES LIKE') === 0) {
    preg_match("/LIKE '([^']+)'/", $sql, $m);
    return in_array(str_replace('\\_', '_', $m[1]), $GLOBALS['tables'], true) ? array(array(1)) : array();
  }
  if (strpos($sql, 'SELECT language') !== false)     { return array(array('language' => 'german')); }
  if (strpos($sql, 'SELECT content_type') !== false) { return array(array('content_type' => 'physical')); }
  if (strpos($sql, 'notice_hash') !== false)         { return array(array('notice_hash' => $notice_hash)); }
  if (strpos($sql, 'AS downloads') !== false)        { return array(array('orders_products_id' => '10', 'attributes' => '0', 'downloads' => '0')); }
  return array(array('orders_products_id' => '10', 'manufacturers_name' => 'ACME GmbH',
                     'manufacturers_model' => 'X-1', 'garan_duration' => '3.0',
                     'garan_hash' => $garan_hash, 'terms_hash' => null, 'terms_filename' => null));
}
function xtc_db_fetch_array(&$r) { return array_shift($r); }
function xtc_db_num_rows($r) { return count($r); }

require DIR_FS_INC.'guarantee_labels_order.inc.php';

$pass = 0; $fail = 0;
function ok($n, $c, $e = '') { global $pass, $fail; if ($c) { $pass++; echo "  ok    $n\n"; } else { $fail++; echo "  FAIL  $n".($e!==''?"  ($e)":'')."\n"; } }

echo "\n== $mode ==\n";
$label = guarantee_labels_order_label(4711, 10);
ok('GARAN-Label erscheint', $label !== '');
ok('Grafik enthalten', strpos($label, '<svg') !== false || strpos($label, 'data-guarantee-label-src') !== false);

$parts = guarantee_labels_order_notice_parts(4711);
ok('Hinweisblock erscheint', $parts !== false);
ok('historischer Text der Bestellung', $parts !== false && strpos($parts['body'], 'Historischer deutscher') !== false);
ok('archivierte Ueberschrift', $parts !== false && strpos($parts['title'], 'Gesetzliche Gewaehrleistung') !== false);
ok('archivierter Linktext', $parts !== false && strpos($parts['body'], 'Mehr erfahren') !== false);
ok('Schliessen beschriftet', $parts !== false && preg_match('/guarantee-label__close">.+?<\/button>/', $parts['body']) === 1);

$text = guarantee_labels_order_text(4711, 10);
ok('Zusage erscheint', $text !== false && $text['label']['html'] !== '');

echo "\n----------------------------------------\n";
echo "bestanden: $pass   fehlgeschlagen: $fail\n";
exit($fail > 0 ? 1 : 0);
