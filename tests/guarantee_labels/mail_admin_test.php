<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Wiederversand aus dem Admin: dort sind die Storefront-Sprachdateien nicht geladen.
error_reporting(E_ALL & ~E_DEPRECATED);
$guarantee_labels_paths = require __DIR__.'/bootstrap.php';
$root = $guarantee_labels_paths['shop'];
$repo = $guarantee_labels_paths['repo'];
$repo = $guarantee_labels_paths['repo'];

define('DIR_FS_CATALOG', $root.'/');
define('DIR_FS_DOCUMENT_ROOT', $root.'/');
define('DIR_FS_INC', $root.'/inc/');
define('DIR_WS_INCLUDES', 'includes/');
define('DIR_WS_CLASSES', 'includes/classes/');
define('DIR_FS_LOG', $root.'/log/');
define('DIR_FS_EXTERNAL', $root.'/includes/external/');
define('TABLE_MANUFACTURERS', 'manufacturers');
define('TABLE_ORDERS', 'orders');
define('TABLE_ORDERS_PRODUCTS', 'orders_products');
define('TABLE_PRODUCTS_ATTRIBUTES', 'products_attributes');
define('TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD', 'products_attributes_download');
define('TABLE_ORDERS_PRODUCTS_ATTRIBUTES', 'orders_products_attributes');
define('TABLE_ORDERS_PRODUCTS_DOWNLOAD', 'orders_products_download');
define('TABLE_ORDERS_GUARANTEE', 'orders_guarantee');
define('TABLE_ORDERS_PRODUCTS_GUARANTEE', 'orders_products_guarantee');
define('TABLE_PRODUCTS_CONTENT', 'products_content');
define('MODULE_GUARANTEE_LABELS_STATUS', 'true');
$_SESSION['language_charset'] = 'UTF-8';
require $repo.'/inc/html_encoding.php';

$hash = str_repeat('a', 64);
$dir = $root.'/media/guarantee_labels/archive/notice/'.$hash.'/';
@mkdir($dir, 0777, true);
file_put_contents($dir.'notice.svg', '<svg></svg>');
file_put_contents($dir.'notice.json', json_encode(array(
  'version' => '1.00', 'language' => 'german',
  'text' => 'Historischer Text.', 'link' => 'Nachlesen', 'url' => 'https://europa.eu/youreurope/garantien',
)));

$GLOBALS['tables'] = array('orders_guarantee', 'orders_products_guarantee');
function xtc_db_query($sql) {
  if (strpos($sql, 'SHOW TABLES LIKE') === 0) {
    preg_match("/LIKE '([^']+)'/", $sql, $m);
    return in_array(str_replace('\\_', '_', $m[1]), $GLOBALS['tables'], true) ? array(array(1)) : array();
  }
  if (strpos($sql, 'notice_hash') !== false) return array(array('notice_hash' => str_repeat('a', 64)));
  // eine koerperliche Position, sonst gibt es keinen Hinweis
  if (strpos($sql, 'AS downloads') !== false) {
    return array(array('orders_products_id' => '10', 'attributes' => '0', 'downloads' => '0'));
  }
  return array();
}
function xtc_db_fetch_array(&$r) { return array_shift($r); }
function xtc_db_num_rows($r) { return count($r); }

require DIR_FS_INC.'guarantee_labels_order.inc.php';

$pass = 0; $fail = 0;
function ok($n, $c, $e = '') { global $pass, $fail; if ($c) { $pass++; echo "  ok    $n\n"; } else { $fail++; echo "  FAIL  $n".($e!==''?"  ($e)":'')."\n"; } }

class order_stub { public $info = array('order_id' => 4711, 'language' => 'german'); }
class smarty_stub { public $vars = array(); function assign($k, $v) { $this->vars[$k] = $v; } }
$order = new order_stub();
$smarty = new smarty_stub();
$email_attachments = '';

echo "== Wiederversand aus dem Admin ==\n";
ok('Storefronttexte vorher nicht geladen', !defined('TEXT_GUARANTEE_NOTICE_TITLE'));
require $root.'/includes/extra/send_order/data/guarantee_labels.php';
ok('historischer Text aus dem Archiv', strpos($smarty->vars['GUARANTEE_NOTICE_HTML'], 'Historischer Text.') !== false);
ok('Ueberschrift trotzdem gesetzt', $smarty->vars['GUARANTEE_NOTICE_HEADING_HTML'] !== '', var_export($smarty->vars['GUARANTEE_NOTICE_HEADING_HTML'], true));
ok('Ueberschrift mit Entities', strpos($smarty->vars['GUARANTEE_NOTICE_HEADING_HTML'], '&auml;') !== false);
ok('Textfassung aufgeloest', strpos($smarty->vars['GUARANTEE_NOTICE_HEADING_TXT'], '&auml;') === false && strpos($smarty->vars['GUARANTEE_NOTICE_HEADING_TXT'], 'hrleistung') !== false);

echo "\n----------------------------------------\n";
echo "bestanden: $pass   fehlgeschlagen: $fail\n";
exit($fail > 0 ? 1 : 0);
