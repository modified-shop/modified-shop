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
$root = $guarantee_labels_paths['shop'];
$repo = $guarantee_labels_paths['repo'];
define('DIR_FS_CATALOG', $root.'/');
define('DIR_WS_INCLUDES', 'includes/');
define('DIR_WS_CLASSES', DIR_WS_INCLUDES.'classes/');
define('DIR_FS_INC', $root.'/inc/');
define('DIR_FS_LOG', $root.'/log/');
define('DIR_FS_EXTERNAL', $root.'/includes/external/');
define('TABLE_MANUFACTURERS', 'manufacturers');
require $guarantee_labels_paths['repo'].'/lang/german/extra/admin/guarantee_labels.php';

// --- Datenbank-Stub: Hersteller 1 aktiv, 2 inaktiv, 3 mit sehr langem Namen --
$GLOBALS['manufacturers'] = array(
  1 => array('name' => 'ACME GmbH', 'status' => 1),
  2 => array('name' => 'Inaktiv AG', 'status' => 0),
  3 => array('name' => str_repeat('Sehr langer Herstellername ', 4), 'status' => 1),
);
function xtc_db_query($sql) {
  $man = $GLOBALS['manufacturers'];
  $rows = array();
  // die Sammelabfrage hat kein IN (): sie holt alle aktiven Hersteller auf einmal
  if (strpos($sql, 'manufacturers_status') !== false && strpos($sql, 'IN (') === false) {
    foreach ($man as $id => $data) if ($data['status'] == 1) $rows[] = array('manufacturers_id' => $id, 'manufacturers_name' => $data['name']);
    return $rows;
  }
  preg_match_all("/\d+/", substr($sql, (int)strpos($sql, 'IN (')), $m);
  foreach ($m[0] as $id) if (isset($man[(int)$id]) && $man[(int)$id]['status'] == 1) $rows[] = array('manufacturers_id' => (int)$id, 'manufacturers_name' => $man[(int)$id]['name']);
  return $rows;
}
function xtc_db_num_rows($r) { return count($r); }
function xtc_db_fetch_array(&$r) { return array_shift($r); }

require DIR_FS_INC.'guarantee_labels_validate_product.inc.php';

$pass = 0; $fail = 0;
function ok($name, $cond, $extra = '') {
  global $pass, $fail;
  if ($cond) { $pass++; echo "  ok    $name\n"; }
  else { $fail++; echo "  FAIL  $name".($extra !== '' ? "  ($extra)" : '')."\n"; }
}
function check($duration, $manufacturers_id = 1, $model = 'X-1', $products_id = 42) {
  $posted = array('manufacturers_id' => $manufacturers_id);

  // ein bestehender Artikel bringt seine Nummer mit, ein neuer nicht
  if ($products_id > 0) {
    $posted['products_id'] = $products_id;
  }

  return guarantee_labels_validate_product(
    array('products_garan_duration' => $duration,
          'products_manufacturers_model' => $model,
          'manufacturers_id' => $manufacturers_id,
          'products_price' => '9.99'),
    $posted
  );
}

echo "== Leeres Feld ==\n";
$r = check('');
ok('leer wird als NULL gespeichert', $r['data']['products_garan_duration'] === 'null');
ok('keine Fehlermeldung', count($r['errors']) === 0);
$r = check('   ');
ok('nur Leerzeichen ebenso', $r['data']['products_garan_duration'] === 'null' && count($r['errors']) === 0);

echo "\n== Gueltige Laufzeiten ==\n";
foreach (array('0,5' => '0.5', '1' => '1.0', '1,5' => '1.5', '2' => '2.0', '2,0' => '2.0', '2.5' => '2.5', '2,5' => '2.5', '3' => '3.0', '5' => '5.0', '10' => '10.0', '99' => '99.0', '9,5' => '9.5') as $in => $expected) {
  $r = check($in);
  ok(sprintf("%-5s -> %s", "'$in'", $expected), $r['data']['products_garan_duration'] === $expected && count($r['errors']) === 0, implode(' ', $r['errors']));
}

echo "\n== Abgelehnte Laufzeiten ==\n";
foreach (array('0', '0.4', '4.1', '100', '100.5', 'abc', '-3') as $in) {
  $r = check($in);
  ok(sprintf("%-6s abgelehnt", "'$in'"), !isset($r['data']['products_garan_duration']) && count($r['errors']) === 1);
}

echo "\n== Zwei Jahre werden dokumentiert, nicht beworben ==\n";
$r = check('1', 0, '');
ok('1 wird ohne Hersteller und Modell gespeichert', $r['data']['products_garan_duration'] === '1.0');
ok('keine Fehlermeldung', count($r['errors']) === 0, implode(' | ', $r['errors']));
$r = check('2.5', 0, '');
ok('2,5 verlangt weiterhin die Kerndaten', !isset($r['data']['products_garan_duration']) && count($r['errors']) === 2);

echo "\n== Abgelehnte Eingabe laesst die Spalte unangetastet ==\n";
$r = check('abc');
ok('Schluessel entfernt statt auf null gesetzt', !array_key_exists('products_garan_duration', $r['data']));
ok('beim bestehenden Artikel alle drei zurueckgehalten', !array_key_exists('products_garan_duration', $r['data'])
   && !array_key_exists('products_manufacturers_model', $r['data'])
   && !array_key_exists('manufacturers_id', $r['data']));

// Ein neuer Artikel hat keinen gespeicherten Stand zu schuetzen. Wuerden Hersteller und
// Modellkennung mitverworfen, entstuende er ohne beides und der Admin muesste gueltige
// Eingaben neu tippen, obwohl nur die Dauer abgelehnt wurde.
$neu = check('abc', 1, 'X-1', 0);
ok('beim neuen Artikel nur die Dauer zurueckgehalten', !array_key_exists('products_garan_duration', $neu['data'])
   && $neu['data']['products_manufacturers_model'] === 'X-1'
   && $neu['data']['manufacturers_id'] === 1);
ok('und die Meldung kommt trotzdem', count($neu['errors']) === 1);
ok('uebrige Artikeldaten bleiben im Array', isset($r['data']['products_price']));
$r = check('');
ok('bewusst geleertes Feld setzt weiterhin null', $r['data']['products_garan_duration'] === 'null' && count($r['errors']) === 0);

echo "\n== Kerndaten ==\n";
$r = check('3', 0);
ok('kein Hersteller ausgewaehlt', !isset($r['data']['products_garan_duration']) && strpos(implode(' ', $r['errors']), 'kein aktiver Hersteller') !== false);
$r = check('3', 2);
ok('inaktiver Hersteller', !isset($r['data']['products_garan_duration']) && count($r['errors']) === 1);
$r = check('3', 1, '');
ok('leere Modellkennung', !isset($r['data']['products_garan_duration']) && strpos(implode(' ', $r['errors']), 'Modellkennung ist leer') !== false);
$r = check('3', 0, '');
ok('beide Fehler zugleich', count($r['errors']) === 2);

echo "\n== Textbreite ==\n";
$r = check('3', 3);
ok('zu langer Herstellername', !isset($r['data']['products_garan_duration']) && strpos(implode(' ', $r['errors']), 'passt nicht') !== false);
$r = check('3', 1, str_repeat('MODELL-NUMMER-', 5));
ok('zu lange Modellkennung', !isset($r['data']['products_garan_duration']) && strpos(implode(' ', $r['errors']), 'passt nicht') !== false);

echo "\n== Andere Felder unangetastet ==\n";
$r = guarantee_labels_validate_product(
  array('products_garan_duration' => '3', 'products_manufacturers_model' => 'X-1', 'products_model' => 'INTERN-1', 'products_price' => '9.99'),
  array('manufacturers_id' => 1)
);
ok('products_model unveraendert', $r['data']['products_model'] === 'INTERN-1');
ok('products_price unveraendert', $r['data']['products_price'] === '9.99');
ok('products_manufacturers_model unveraendert', $r['data']['products_manufacturers_model'] === 'X-1');

echo "\n----------------------------------------\n";
echo "bestanden: $pass   fehlgeschlagen: $fail\n";
exit($fail > 0 ? 1 : 0);
