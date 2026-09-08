<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Prueft die Modulddiagnose: erkennt sie fehlende Teile und meldet sie sauber?
error_reporting(E_ALL & ~E_DEPRECATED);
$guarantee_labels_paths = require __DIR__.'/bootstrap.php';
$root = $guarantee_labels_paths['shop'];
$repo = $guarantee_labels_paths['repo'];
$repo = $guarantee_labels_paths['repo'];
$mode = isset($argv[1]) ? $argv[1] : 'driver';

if ($mode === 'driver') {
  $pass = 0; $fail = 0;
  foreach (array('vollstaendig', 'luecken', 'schema') as $case) {
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

define('_VALID_XTC', true);
define('DIR_FS_CATALOG', $root.'/');
define('DIR_FS_ADMIN', $repo.'/admin/');
define('DIR_FS_INC', $root.'/inc/');
define('DIR_WS_INCLUDES', 'includes/');
define('DIR_WS_CLASSES', 'includes/classes/');
define('DIR_FS_LOG', $root.'/log/');
define('DIR_FS_EXTERNAL', $root.'/includes/external/');
define('TABLE_CONFIGURATION', 'configuration');
define('TABLE_LANGUAGES', 'languages');
define('TABLE_ORDERS_GUARANTEE', 'orders_guarantee');
define('TABLE_ORDERS_PRODUCTS_GUARANTEE', 'orders_products_guarantee');
define('TABLE_PRODUCTS', 'products');
define('TABLE_PRODUCTS_CONTENT', 'products_content');
define('TABLE_MANUFACTURERS', 'manufacturers');
define('TABLE_CUSTOMERS_STATUS', 'customers_status');
// im Luecken-Modus steht eine geloeschte Kundengruppe in der Einstellung
define('MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS', ($mode === 'luecken') ? '4,9' : '4');
$_SESSION['languages_id'] = 2;
define('FILENAME_MODULE_EXPORT', 'module_export.php');
define('BUTTON_UPDATE', 'U'); define('BUTTON_SAVE', 'S'); define('BUTTON_CANCEL', 'C');
define('MODULE_GUARANTEE_LABELS_STATUS', 'true');
define('USE_CACHE', ($mode === 'luecken') ? 'true' : 'false');

// vollstaendig oder mit Luecken
define('MODULE_CATEGORIES_INSTALLED', 'guarantee_labels_product.php');
define('MODULE_PRODUCT_INSTALLED', ($mode === 'luecken') ? '' : 'guarantee_labels_listing.php');
define('MODULE_ORDER_INSTALLED', 'guarantee_labels_order.php');
define('ADD_SELECT_DEFAULT', 'p.products_manufacturers_model, p.products_garan_duration, ');
define('ADD_SELECT_SEARCH', 'p.products_garan_duration, ');
define('ADD_SELECT_CART', ($mode === 'luecken') ? 'p.products_model, ' : 'p.products_garan_duration, ');
define('ADD_SELECT_PRODUCT', 'p.products_garan_duration, ');

require $repo.'/lang/german/modules/system/guarantee_labels.php';
require $repo.'/inc/html_encoding.php';

function xtc_db_query($sql) {
  global $mode;

  if (strpos($sql, 'SHOW TABLES') !== false) {
    // im Luecken-Modus fehlt orders_products_guarantee
    return ($mode === 'schema' && strpos($sql, 'orders\\_products\\_guarantee') !== false)
           ? array() : array(array('t' => 1));
  }
  if (strpos($sql, 'SHOW KEYS') !== false) {
    preg_match("/Key_name = '([^']+)'/", $sql, $k);
    if ($k[1] === 'PRIMARY') {
      $spalte = (strpos($sql, 'orders_products_guarantee') !== false) ? 'orders_products_guarantee_id' : 'orders_guarantee_id';
      return array(array('Column_name' => $spalte, 'Non_unique' => '0', 'Seq_in_index' => '1'));
    }
    // idx_orders_id ist nur auf orders_guarantee eindeutig
    $unique = ($k[1] === 'idx_orders_products_id'
               || ($k[1] === 'idx_orders_id' && strpos($sql, 'orders_products_guarantee') === false)) ? '0' : '1';
    $column = ($k[1] === 'idx_orders_products_id') ? 'orders_products_id' : 'orders_id';
    return array(array('Column_name' => $column, 'Non_unique' => $unique, 'Seq_in_index' => '1'));
  }
  if (strpos($sql, 'SHOW COLUMNS') !== false) {
    preg_match("/LIKE '([^']+)'/", $sql, $c);
    return array(garan_spalte(str_replace('\\_', '_', $c[1])));
  }
  if (strpos($sql, 'FROM languages') !== false) {
    // inaktive Sprachen darf die Diagnose gar nicht erst laden
    if (strpos($sql, "status = '1'") === false) {
      $GLOBALS['alle_sprachen_geladen'] = true;
    }
    return array(array('directory' => 'german', 'name' => 'Deutsch'),
                 array('directory' => 'klingonisch', 'name' => '<b>Klingonisch</b>'));
  }
  if (strpos($sql, 'MOD(ROUND(') !== false) {
    return array(array('total' => ($mode === 'luecken') ? 4 : 0));
  }
  if (strpos($sql, 'p.products_garan_duration > 2.0') !== false) {
    return array(array('total' => ($mode === 'luecken') ? 3 : 0));
  }
  if (strpos($sql, 'AS ambiguous') !== false) {
    return array(array('total' => ($mode === 'luecken') ? 2 : 0));
  }
  if (strpos($sql, 'pc.group_ids') !== false) {
    // im Luecken-Modus ist ein Anhang auf eine Gruppe eingeschraenkt, die das Label nicht sieht
    return ($mode === 'luecken') ? array(array('content_id' => 1, 'group_ids' => 'c_4_group,')) : array();
  }
  if (strpos($sql, 'customers_status') !== false) {
    return array(array('customers_status_id' => '1', 'customers_status_name' => 'Endkunde'),
                 array('customers_status_id' => '4', 'customers_status_name' => 'Haendler'));
  }
  if (strpos($sql, 'AS shared') !== false) {
    return array(array('total' => ($mode === 'luecken') ? 1 : 0));
  }
  if (strpos($sql, 'notice_hash FROM') !== false) {
    return ($mode === 'luecken') ? array(array('notice_hash' => 'fehlt')) : array();
  }
  if (strpos($sql, 'garan_hash FROM') !== false) { return array(); }
  if (strpos($sql, 'terms_hash') !== false)      { return array(); }
  return array();
}
// dieselben Typen, die schema_columns() erwartet
// eine vollstaendige SHOW COLUMNS Zeile, nicht nur der Typ
function garan_spalte($name) {
  $auto = in_array($name, array('orders_guarantee_id', 'orders_products_guarantee_id'), true);
  $nullbar = in_array($name, array('products_garan_duration', 'terms_hash', 'terms_filename'), true);

  return array(
    'Type' => garan_spaltentyp($name),
    'Null' => $nullbar ? 'YES' : 'NO',
    'Key' => $auto ? 'PRI' : '',
    'Default' => ($name === 'content_type') ? '' : null,
    'Extra' => $auto ? 'auto_increment' : '',
  );
}
function garan_spaltentyp($name) {
  $typen = array(
    'products_garan_duration' => 'decimal(4,1)', 'content_type' => 'varchar(32)',
    'orders_guarantee_id' => 'int(11)', 'orders_id' => 'int(11)', 'notice_hash' => 'varchar(64)',
    'date_added' => 'datetime', 'orders_products_guarantee_id' => 'int(11)',
    'orders_products_id' => 'int(11)', 'manufacturers_name' => 'varchar(255)',
    'manufacturers_model' => 'varchar(64)', 'garan_duration' => 'decimal(4,1)',
    'garan_hash' => 'varchar(64)', 'terms_hash' => 'varchar(64)', 'terms_filename' => 'varchar(255)',
  );
  return isset($typen[$name]) ? $typen[$name] : 'varchar(32)';
}
function xtc_db_fetch_array(&$r) { $row = array_shift($r); return is_array($row) ? $row : array(); }
function xtc_db_num_rows($r) { return count($r); }
function xtc_db_input($v) { return $v; }
function xtc_href_link($f, $p = '') { return $f.'?'.$p; }

$_GET['module'] = 'guarantee_labels';
require $repo.'/admin/includes/modules/system/guarantee_labels.php';
$m = new guarantee_labels();
$out = isset($m->properties['add_content']) ? $m->properties['add_content'] : '';

$pass = 0; $fail = 0;
function ok($n, $c, $e = '') { global $pass, $fail; if ($c) { $pass++; echo "  ok    $n\n"; } else { $fail++; echo "  FAIL  $n".($e!==''?"  ($e)":'')."\n"; } }

echo "\n== $mode ==\n";
ok('Diagnose erzeugt', $out !== '');
ok('nur aktive Sprachen abgefragt', !isset($GLOBALS['alle_sprachen_geladen']));
ok('Cachehinweis immer vorhanden', strpos($out, 'leert von sich aus keinen Cache') !== false);

// Die Box zeigt nur, was Aufmerksamkeit braucht. Siebzehn gruene Zeilen verstecken die eine
// rote, und der Shopbetreiber liest hier, um ein Problem zu finden.
ok('kein "in Ordnung" in der Box', strpos($out, MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_OK) === false, $out);
ok('div_box nicht verwendet', strpos($out, 'div_box') === false);

ok('mit Befund eine rote Box', strpos($out, 'error_message') !== false);
ok('dabei keine Erfolgsmeldung', strpos($out, MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_COMPLETE) === false);

// Bestandene Pruefungen tauchen nicht auf. Das ist die eigentliche Regel, und sie laesst sich
// je Betriebsart an dem festmachen, was die Attrappe bewusst heil laesst.
foreach (array('Schreibrecht auf', MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_SCHEMA) as $heil) {
  if ($mode !== 'schema') {
    ok('bestandene Pruefung fehlt: '.substr($heil, 0, 24), strpos($out, $heil) === false, $heil);
  }
}

ok('Sprachname maskiert', strpos($out, '<b>Klingonisch</b>') === false);

if ($mode === 'schema') {
  ok('Schemazeile vorhanden', strpos($out, MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_SCHEMA) !== false);
  ok('fehlende Tabelle gemeldet', strpos($out, MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_INCOMPLETE) !== false);
  ok('keine Datenabfrage bei kaputtem Schema', strpos($out, MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_PRODUCTS) === false);
  ok('keine Archivzeile bei kaputtem Schema', strpos($out, MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_ARCHIVE) === false);
  ok('Cachehinweis auch beim Abbruch', strpos($out, MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_CACHE) !== false);
} elseif ($mode === 'luecken') {
  ok('Artikelzeile vorhanden', strpos($out, MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_PRODUCTS) !== false);
  ok('Archivzeile vorhanden', strpos($out, MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_ARCHIVE) !== false);
  // Diese Attrappe fuehrt keinen unerreichbaren Anhang, die Pruefung besteht also und faellt
  // aus der Box. Vorher prueften wir nur, dass die Zeile ueberhaupt existiert; das war
  // richtig, solange alles gelistet wurde, sagte aber nichts ueber das Ergebnis aus.
  ok('erreichbare Anhaenge fallen aus der Box', strpos($out, MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_GROUPS) === false);
  ok('unvollstaendige Artikel gezaehlt', strpos($out, MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_AFFECTED.' 3') !== false);
  ok('doppelte Bedingungen gezaehlt', strpos($out, MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_AFFECTED.' 2') !== false);
  ok('mehrsprachige Datei gemeldet', strpos($out, MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_SHARED) !== false);
  ok('ungueltige Dauer gezaehlt', strpos($out, MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_AFFECTED.' 4') !== false);
  ok('beschaedigtes Archiv gezaehlt', strpos($out, MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_AFFECTED.' 1') !== false);
  ok('Schema trotz Datenfehlern in Ordnung', strpos($out, MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_INCOMPLETE) === false);
  // Gruppe 9 gibt es nicht mehr, ihre Id steht aber weiter in der Einstellung
  ok('geloeschte B2B-Gruppe gemeldet', strpos($out, MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_UNKNOWN.' 9') !== false, $out);
} else {
  // vollstaendig: die Daten sind heil, nur die Registrierung fehlt in dieser Attrappe
  foreach (array(MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_PRODUCTS,
                 MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_ARCHIVE,
                 MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_DURATION,
                 'Garantiedauer in ADD_SELECT') as $zeile) {
    ok('bestandene Pruefung fehlt: '.substr($zeile, 0, 24), strpos($out, $zeile) === false, $zeile);
  }
  ok('keine betroffenen Datensaetze', strpos($out, MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_AFFECTED) === false);
  ok('vorhandene B2B-Gruppe faellt aus der Box', strpos($out, MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_B2B) === false);
}

if ($mode === 'luecken') {
  ok('fehlende Listing-Erweiterung erkannt', preg_match('/guarantee_labels_listing\.php.*?fehlt/s', $out) === 1);
  ok('fehlende Warenkorbspalte erkannt', preg_match('/ADD_SELECT_CART.*?fehlt/s', $out) === 1);
  ok('bestandene SELECT-Listen fehlen', strpos($out, 'ADD_SELECT_DEFAULT') === false);
  ok('Sprache ohne Grafik faellt auf', strpos($out, 'Klingonisch') !== false);
  ok('vollstaendige Sprache faellt nicht auf', strpos($out, 'Deutsch') === false);
}

// Der Erfolgsfall laesst sich an der Attrappe nicht herstellen, deshalb direkt gepruefte Zeilen
if ($mode === 'vollstaendig') {
  $modul = new guarantee_labels();
  $gruen = $modul->diagnosis_table(array(array('Alles heil', true), array('Auch das', true)));
  ok('ohne Befund eine gruene Meldung', strpos($gruen, 'info_message') !== false
     && strpos($gruen, MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_COMPLETE) !== false, $gruen);
  ok('dabei keine Fehlerbox', strpos($gruen, 'error_message') === false);
  ok('Cachehinweis auch dort', strpos($gruen, MODULE_GUARANTEE_LABELS_TEXT_DIAGNOSIS_CACHE) !== false);

  $rot = $modul->diagnosis_table(array(array('Alles heil', true), array('Das nicht', false)));
  ok('eine einzige Abweichung ergibt die rote Box', strpos($rot, 'error_message') !== false
     && strpos($rot, 'Das nicht') !== false && strpos($rot, 'Alles heil') === false, $rot);
}

echo "\nbestanden: $pass   fehlgeschlagen: $fail\n";
exit($fail > 0 ? 1 : 0);
