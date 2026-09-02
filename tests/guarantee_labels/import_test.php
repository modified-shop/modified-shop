<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

error_reporting(E_ALL & ~E_DEPRECATED);
$guarantee_labels_paths = require __DIR__.'/bootstrap.php';
$R = $guarantee_labels_paths['repo'].'/';
$root = $guarantee_labels_paths['shop'];
define('_VALID_XTC', true);
define('DIR_FS_CATALOG', $root.'/');
define('DIR_WS_INCLUDES', 'includes/');
define('DIR_WS_CLASSES', 'includes/classes/');
define('DIR_FS_INC', $root.'/inc/');
define('DIR_FS_LOG', $root.'/log/');
define('DIR_FS_EXTERNAL', $root.'/includes/external/');
define('DIR_FS_ADMIN', $R.'admin/');
define('TABLE_PRODUCTS', 'products');
define('TABLE_MANUFACTURERS', 'manufacturers');
define('MODULE_GUARANTEE_LABELS_STATUS', 'true');
require $guarantee_labels_paths['repo'].'/lang/german/extra/admin/guarantee_labels.php';

// Hersteller 1 aktiv, Artikel ABC hat Hersteller 1 und Modellkennung X-1
function xtc_db_input($s) { return $s; }
function xtc_db_prepare_input($s) { return $s; }
function xtc_db_query($sql) {
  if (stripos($sql, 'FROM manufacturers') !== false) {
    return preg_match("/manufacturers_id = '1'/", $sql) ? array(array('manufacturers_name' => 'ACME GmbH')) : array();
  }
  if (stripos($sql, 'FROM products') !== false) {
    return preg_match("/products_model = 'ABC'/", $sql)
      ? array(array('manufacturers_id' => 1, 'products_manufacturers_model' => 'X-1'))
      : array();
  }
  return array();
}
function xtc_db_num_rows($r) { return count($r); }
function xtc_db_fetch_array($r) { return $r[0]; }
class stack { public $msgs = array(); function add($t, $c = 'info') { $this->msgs[] = html_entity_decode(strip_tags($t)); } }
$messageStack = new stack();

// minimaler Stand-in fuer die Importklasse
class import_stub {
  var $FileSheme = array('p_garan_duration' => 'Y');
  function run($dataArray, $products_array) {
    global $messageStack;
    require DIR_FS_ADMIN.'includes/extra/modules/import/insert_before/guarantee_labels.php';
    return $products_array;
  }
  function layout() {
    $file_layout = array();
    require DIR_FS_ADMIN.'includes/extra/modules/import/file_layout/guarantee_labels.php';
    return $file_layout;
  }
  function encode($v) { return $v.';'; }
  function head() {
    $line = '';
    require DIR_FS_ADMIN.'includes/extra/modules/export/file_layout/guarantee_labels.php';
    return $line;
  }
  function row($export_data) {
    $line = '';
    require DIR_FS_ADMIN.'includes/extra/modules/export/export_end/guarantee_labels.php';
    return $line;
  }
}

$pass = 0; $fail = 0;
function ok($n, $c, $e = '') { global $pass, $fail; if ($c) { $pass++; echo "  ok    $n\n"; } else { $fail++; echo "  FAIL  $n".($e!==''?"  ($e)":'')."\n"; } }

$imp = new import_stub();

echo "== Dateischema ==\n";
ok('p_garan_duration im Import-Layout', array_key_exists('p_garan_duration', $imp->layout()));
ok('p_garan_duration im Export-Kopf', $imp->head() === 'p_garan_duration;');

echo "\n== Export ==\n";
ok('NULL wird leer exportiert', $imp->row(array('products_garan_duration' => null)) === ';');
ok('Wert wird exportiert', $imp->row(array('products_garan_duration' => '2.5')) === '2.5;');

echo "\n== Import mit Hersteller und Modell aus der CSV ==\n";
$messageStack->msgs = array();
$r = $imp->run(array('p_model' => 'NEU', 'p_garan_duration' => '2,5'),
               array('manufacturers_id' => 1, 'products_manufacturers_model' => 'X-9'));
ok('Komma normalisiert', $r['products_garan_duration'] === '2.5', var_export($r['products_garan_duration'], true));
ok('keine Meldung', count($messageStack->msgs) === 0, implode(' | ', $messageStack->msgs));

echo "\n== Import ohne Hersteller und Modell in der CSV ==\n";
$messageStack->msgs = array();
$r = $imp->run(array('p_model' => 'ABC', 'p_garan_duration' => '3'), array());
ok('faellt auf die Artikeldaten zurueck', $r['products_garan_duration'] === '3.0', var_export($r['products_garan_duration'], true));
ok('keine Meldung', count($messageStack->msgs) === 0, implode(' | ', $messageStack->msgs));

echo "\n== Zwei Jahre aus einem Fremdsystem ==\n";
$messageStack->msgs = array();
$r = $imp->run(array('p_model' => 'ABC', 'p_garan_duration' => '2'), array());
ok('2 wird gespeichert', $r['products_garan_duration'] === '2.0', var_export($r['products_garan_duration'], true));
$r2 = $imp->run(array('p_model' => 'ABC', 'p_garan_duration' => '1'), array());
ok('auch ein Jahr wird gespeichert', $r2['products_garan_duration'] === '1.0', var_export($r2['products_garan_duration'], true));
ok('ohne Meldung', count($messageStack->msgs) === 0, implode(' | ', $messageStack->msgs));
$messageStack->msgs = array();
$r = $imp->run(array('p_model' => 'UNBEKANNT', 'p_garan_duration' => '2'), array());
ok('auch ohne bekannten Artikel', $r['products_garan_duration'] === '2.0' && count($messageStack->msgs) === 0);

echo "\n== Ungueltige Werte ==\n";
$messageStack->msgs = array();
$r = $imp->run(array('p_model' => 'ABC', 'p_garan_duration' => '0.4'), array());
ok('0.4 laesst die Spalte unangetastet', !array_key_exists('products_garan_duration', $r));
ok('Meldung nennt den Artikel', count($messageStack->msgs) === 1 && strpos($messageStack->msgs[0], 'ABC') === 8, $messageStack->msgs ? $messageStack->msgs[0] : '');

$messageStack->msgs = array();
$r = $imp->run(array('p_model' => 'UNBEKANNT', 'p_garan_duration' => '3'), array());
ok('unvollstaendige Kerndaten lassen die Spalte unangetastet', !array_key_exists('products_garan_duration', $r));
ok('Meldung je fehlendem Kerndatum', count($messageStack->msgs) === 2, implode(' | ', $messageStack->msgs));

echo "\n== Leeres Feld ==\n";
$messageStack->msgs = array();
$r = $imp->run(array('p_model' => 'ABC', 'p_garan_duration' => ''), array());
ok('leer wird NULL', $r['products_garan_duration'] === 'null');
ok('keine Meldung', count($messageStack->msgs) === 0);

echo "\n== Spalte fehlt in der CSV ==\n";
$imp->FileSheme['p_garan_duration'] = 'N';
$r = $imp->run(array('p_model' => 'ABC'), array('products_price' => '1.00'));
ok('Feld bleibt unangetastet', !array_key_exists('products_garan_duration', $r));

echo "\n----------------------------------------\nbestanden: $pass   fehlgeschlagen: $fail\n";
exit($fail > 0 ? 1 : 0);
