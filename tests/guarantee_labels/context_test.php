<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

$guarantee_labels_paths = require __DIR__.'/bootstrap.php';
// Prueft beide Laufzeitkontexte mit ihren echten Pfadkonstanten
error_reporting(E_ALL & ~E_DEPRECATED);
$R = $guarantee_labels_paths['repo'].'/';
$ctx = $guarantee_labels_paths['shop'];
define('DIR_FS_DOCUMENT_ROOT', $R);
define('DIR_FS_CATALOG', $R);
if ($ctx === 'admin') {
  define('DIR_WS_INCLUDES', 'includes/');            // wie admin/includes/paths.php
  define('DIR_ADMIN', 'admin/');
  define('DIR_FS_ADMIN', DIR_FS_DOCUMENT_ROOT.DIR_ADMIN);
} else {
  define('DIR_WS_INCLUDES', DIR_FS_CATALOG.'includes/'); // wie includes/paths.php
}
define('DIR_WS_CLASSES', DIR_WS_INCLUDES.'classes/');
define('DIR_FS_INC', DIR_FS_CATALOG.'inc/');
define('DIR_FS_LOG', DIR_FS_CATALOG.'log/');
define('DIR_FS_EXTERNAL', DIR_FS_CATALOG.'includes/external/');
define('TABLE_MANUFACTURERS', 'manufacturers');
define('MODULE_GUARANTEE_LABELS_STATUS', 'true');
function xtc_db_query($s) { return array(array('manufacturers_id' => 1, 'manufacturers_name' => 'ACME GmbH')); }
function xtc_db_fetch_array(&$r) { return array_shift($r); }
function xtc_db_num_rows($r) { return count($r); }
function xtc_db_input($s) { return $s; }
require $R.'lang/german/extra/guarantee_labels.php';
require DIR_FS_INC.'guarantee_labels_output.inc.php';

$pass = 0; $fail = 0;
function ok($n, $c, $e = '') { global $pass, $fail; if ($c) { $pass++; echo "  ok    $n\n"; } else { $fail++; echo "  FAIL  $n".($e!==''?"  ($e)":'')."\n"; } }

$p = array('products_garan_duration' => '3.0', 'manufacturers_id' => 1, 'products_manufacturers_model' => 'WAU28T20');
$names = guarantee_labels_collect_manufacturers(array($p));
$label = guarantee_labels_product_label($p, $names);
$markup = guarantee_labels_markup($label);

echo "\n== ".($ctx ?: 'storefront')." ==\n";
ok('Modul ist aktiv', guarantee_labels_active() === true);
ok('Label wird erzeugt', is_array($label));
ok('Markup entsteht', strlen($markup) > 0);
ok('Grafik ist enthalten', strpos($markup, '<svg') !== false);
ok('Herstellername im Markup', strpos($markup, 'ACME') !== false, $markup);

echo "\n----------------------------------------\n";
echo "bestanden: $pass   fehlgeschlagen: $fail\n";
exit($fail > 0 ? 1 : 0);
