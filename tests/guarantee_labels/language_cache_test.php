<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Prueft den Puffer von guarantee_labels_language() ohne Sitzungssprache. Zahlungs-Callbacks,
// Cron und CLI kommen ohne sie aus; wird dort eine Fehlanzeige gemerkt, antwortet die Funktion
// den ganzen Request lang falsch und die Mail geht ohne Gewaehrleistungshinweis hinaus.
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
$_SESSION['language_charset'] = 'UTF-8';

// genau der Fall: keine Sitzungssprache
unset($_SESSION['language']);

function xtc_db_query($sql) { return array(); }
function xtc_db_fetch_array(&$r) { return array_shift($r); }
function xtc_db_num_rows($r) { return count($r); }

// die deutschen Texte sind geladen, wie nach einem Storefront-Bootstrap
require $repo.'/lang/german/extra/guarantee_labels.php';
require DIR_FS_INC.'guarantee_labels_snapshot.inc.php';

$pass = 0; $fail = 0;
function ok($n, $c, $e = '') { global $pass, $fail; if ($c) { $pass++; echo "  ok    $n\n"; } else { $fail++; echo "  FAIL  $n".($e!==''?"  ($e)":'')."\n"; } }

echo "== Sprachpuffer ohne Sitzungssprache ==\n";
ok('Texte sind geladen', defined('TEXT_GUARANTEE_NOTICE_MAIL'));

// Zuerst nach einer fremden Sprache fragen. Frueher merkte sich der Puffer diese Fehlanzeige
// als leeren Wert, und weil isset('') wahr ist, blieb es fuer den Rest des Requests dabei.
ok('fremde Sprache abgewiesen', guarantee_labels_language('english') === false);
ok('geladene Sprache danach trotzdem erkannt', guarantee_labels_language('german') === true);

// und in der anderen Reihenfolge
ok('geladene Sprache erkannt', guarantee_labels_language('german') === true);
ok('fremde Sprache bleibt abgewiesen', guarantee_labels_language('english') === false);

echo "\n----------------------------------------\n";
echo "bestanden: $pass   fehlgeschlagen: $fail\n";
exit($fail > 0 ? 1 : 0);
