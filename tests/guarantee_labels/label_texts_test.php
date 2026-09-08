<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Das GARAN-Label ist sprachneutral: eine unvollstaendig gepflegte Sprache darf es nicht kosten.
error_reporting(E_ALL & ~E_DEPRECATED);
$guarantee_labels_paths = require __DIR__.'/bootstrap.php';
$root = $guarantee_labels_paths['shop'];
$repo = $guarantee_labels_paths['repo'];
$mode = isset($argv[1]) ? $argv[1] : 'driver';

if ($mode === 'driver') {
  $pass = 0; $fail = 0;
  foreach (array('vollstaendig', 'ohne_texte', 'url_ohne_linktext') as $case) {
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
define('MODULE_GUARANTEE_LABELS_STATUS', 'true');
define('MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS', '');

if ($mode === 'vollstaendig') {
  require $root.'/lang/german/extra/guarantee_labels.php';
} elseif ($mode === 'url_ohne_linktext') {
  // URL gepflegt, Linktext vergessen: das darf keinen PHP-Fehler geben
  define('TEXT_GUARANTEE_LABEL_URL', 'https://europa.eu/youreurope/');
}

require $root.'/inc/html_encoding.php';
require $root.'/inc/guarantee_labels_output.inc.php';

$GLOBALS['manufacturers'] = array(1 => array('name' => 'ACME GmbH', 'status' => 1));
function xtc_db_query($sql) {
  preg_match_all("/'(\d+)'/", $sql, $m);
  $rows = array();
  foreach ($m[1] as $id) {
    if (isset($GLOBALS['manufacturers'][(int)$id]) && $GLOBALS['manufacturers'][(int)$id]['status'] == 1) {
      $rows[] = array('manufacturers_id' => $id, 'manufacturers_name' => $GLOBALS['manufacturers'][(int)$id]['name']);
    }
  }
  return $rows;
}
function xtc_db_fetch_array(&$r) { return array_shift($r); }
function xtc_db_num_rows($r) { return count($r); }

$pass = 0; $fail = 0;
function ok($n, $c, $e = '') { global $pass, $fail; if ($c) { $pass++; echo "  ok    $n\n"; } else { $fail++; echo "  FAIL  $n".($e!==''?"  ($e)":'')."\n"; } }

$row = array('products_id' => 1, 'products_garan_duration' => '3.0', 'manufacturers_id' => 1,
             'products_manufacturers_model' => 'WAU28T20');
$names = guarantee_labels_collect_manufacturers(array($row));
$label = guarantee_labels_product_label($row, $names);
$markup = guarantee_labels_markup($label);

echo "\n== $mode ==\n";
ok('Label wird erzeugt', $label !== false);
ok('Markup entsteht', $markup !== '');
ok('Grafik ist enthalten', strpos($markup, '<svg') !== false);
ok('Bedienelement vorhanden', strpos($markup, 'guarantee-label__compact') !== false);

if ($mode === 'vollstaendig') {
  ok('Titel aus der Sprachdatei', strpos($markup, 'Haltbarkeitsgarantie') !== false);
  ok('Link gesetzt', strpos($markup, 'guarantee-label__link') !== false);
} elseif ($mode === 'ohne_texte') {
  ok('faellt auf GARAN zurueck', strpos($markup, 'GARAN') !== false);
  ok('kein leeres aria-label', strpos($markup, 'aria-label=""') === false);
  ok('kein Link ohne Text', strpos($markup, 'guarantee-label__link') === false);
  ok('Schliessen hat eine Beschriftung', preg_match('/guarantee-label__close">.+?<\/button>/', $markup) === 1);
} else {
  ok('kein Link ohne Linktext', strpos($markup, 'guarantee-label__link') === false);
  ok('Dauer steht trotzdem im aria-label', strpos($markup, '3') !== false);
}

if ($mode === 'vollstaendig') {
  echo "\n== Beschaedigter Cache ==\n";
  $cache = DIR_FS_CATALOG.'cache/guarantee_labels/'.$label['hash'].'/';
  ok('Cache wurde beim Rendern gefuellt', is_file($cache.'colour.svg'));
  ok('URL verweist auf die Datei', guarantee_labels_cache_url($label['hash']) !== '');
  ok('Markup verlinkt statt einzubetten', strpos(guarantee_labels_markup($label), 'data-guarantee-label-src') !== false);

  file_put_contents($cache.'colour.svg', 'ERSETZT');
  ok('URL verweigert die beschaedigte Datei', guarantee_labels_cache_url($label['hash']) === '');

  // Der naechste Aufruf merkt es selbst: cache_read() prueft die Pruefsummen, faellt auf das
  // Rendern zurueck und schreibt die Kopie neu. Das Label sagt danach wieder, dass sie steht.
  $r_neu = new guarantee_labels_renderer();
  $label_neu = $r_neu->label('ACME GmbH', 'WAU28T20', '3.0');
  ok('beschaedigte Kopie wird neu geschrieben', is_array($label_neu) && $label_neu['cached'] === true,
     is_array($label_neu) ? var_export($label_neu['cached'], true) : 'kein Label');
  ok('und die Datei stimmt wieder', guarantee_labels_cache_url($label['hash']) !== '');

  $repaired = guarantee_labels_markup($label_neu);
  ok('Markup verlinkt danach wieder', strpos($repaired, 'data-guarantee-label-src') !== false);
}

echo "\n----------------------------------------\n";
echo "bestanden: $pass   fehlgeschlagen: $fail\n";
exit($fail > 0 ? 1 : 0);
