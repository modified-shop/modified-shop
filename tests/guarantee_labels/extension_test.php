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
define('_VALID_XTC', true);
define('REPO', $guarantee_labels_paths['repo'].'/');
define('DIR_FS_CATALOG', $guarantee_labels_paths['ext'].'/');
define('DIR_WS_INCLUDES', 'includes/');
define('DIR_WS_CLASSES', DIR_WS_INCLUDES.'classes/');
define('DIR_FS_INC', DIR_FS_CATALOG.'inc/');
define('DIR_FS_LOG', DIR_FS_CATALOG.'log/');
define('DIR_FS_EXTERNAL', DIR_FS_CATALOG.'includes/external/');
define('TABLE_CONFIGURATION', 'configuration');
define('TABLE_ORDERS_GUARANTEE', 'orders_guarantee');
define('TABLE_ORDERS_PRODUCTS_GUARANTEE', 'orders_products_guarantee');
define('TABLE_PRODUCTS', 'products');
define('TABLE_PRODUCTS_CONTENT', 'products_content');
define('TABLE_MANUFACTURERS', 'manufacturers');
define('FILENAME_MODULE_EXPORT', 'module_export.php');
define('BUTTON_UPDATE', 'U'); define('BUTTON_SAVE', 'S'); define('BUTTON_CANCEL', 'C');
define('TEXT_DEFAULT_STATUS_TITLE', 'Status'); define('TEXT_DEFAULT_STATUS_DESC', 'Status');
define('TEXT_DEFAULT_SORT_ORDER_TITLE', 'Sort'); define('TEXT_DEFAULT_SORT_ORDER_DESC', 'Sort');
define('DIR_ADMIN', 'admin/');
define('DIR_FS_ADMIN', DIR_FS_CATALOG.DIR_ADMIN);
define('DIR_FS_LANGUAGES', DIR_FS_CATALOG.'lang/');
$_SESSION['language'] = 'german';
require REPO.'inc/auto_include.inc.php';
// Stubs fuer die uebrigen Erweiterungen im selben Verzeichnis
function xtc_get_languages() { return array(array('id' => 1, 'code' => 'de', 'name' => 'Deutsch', 'directory' => 'german', 'image' => 'icon.gif')); }
function xtc_get_language_directory($id) { return 'german'; }
function xtc_image($s, $a = '') { return ''; }
function xtc_draw_form() { return ''; }
function xtc_cfg_select_option() { return ''; }
function xtc_not_null($v) { return ($v !== '' && $v !== null && $v !== false); }
require REPO.'lang/german/modules/system/guarantee_labels.php';
require REPO.'lang/german/modules/categories/guarantee_labels_product.php';
require $guarantee_labels_paths['repo'].'/lang/german/extra/admin/guarantee_labels.php';

class db {
  public static $config = array('MODULE_CATEGORIES_INSTALLED' => '');
  public static $schema_ok = true;
  public static $catalogue_data = false;
  public static $primary_column;   // fuer den Fall eines abweichenden Primaerschluessels
  public static $content_default = '';
  public static $duration_default;
  public static $log = array();
}
function xtc_db_input($s) { return $s; }
function xtc_href_link($f, $p = '') { return $f.'?'.$p; }
function xtc_button($t) { return $t; } function xtc_button_link($t, $l) { return $t; }

function xtc_db_query($sql) {
  $sql = preg_replace('/\s+/', ' ', trim($sql));
  db::$log[] = $sql;
  // die Abfrage von update_set_function(): liefert eine Zeile, wenn der gespeicherte Ausdruck abweicht
  if (preg_match("/^SELECT configuration_id FROM configuration WHERE configuration_key = '(.*)' AND set_function != '(.*)'$/i", $sql, $m)) {
    $vorhanden = isset($GLOBALS['set_functions'][$m[1]]) ? $GLOBALS['set_functions'][$m[1]] : null;
    return ($vorhanden !== null && $vorhanden !== $m[2]) ? array(array('configuration_id' => 1)) : array();
  }
  if (preg_match("/^SELECT configuration_(?:id|value) FROM configuration WHERE configuration_key = '(.*)'$/i", $sql, $m)) {
    return isset(db::$config[$m[1]]) ? array(array('configuration_value' => db::$config[$m[1]], 'configuration_id' => 1)) : array();
  }
  if (preg_match("/^DELETE FROM configuration WHERE configuration_key LIKE '(.*)'$/i", $sql, $m)) {
    $prefix = str_replace(array('\\_', '%'), array('_', ''), $m[1]);
    foreach (array_keys(db::$config) as $k) if (strpos($k, $prefix) === 0) unset(db::$config[$k]);
    return array();
  }
  if (preg_match("/^UPDATE configuration SET configuration_value = '(.*)', last_modified = now\\(\\) WHERE configuration_key = '(.*)'$/i", $sql, $m)) {
    db::$config[$m[2]] = $m[1];
    return array();
  }
  if (preg_match("/^UPDATE configuration SET configuration_value = '(.*)' WHERE configuration_key = '(.*)'$/i", $sql, $m)) {
    db::$config[$m[2]] = $m[1];
    return array();
  }
  if (preg_match("/^INSERT INTO configuration .* VALUES \\('([A-Z_]+)', '([^']*)'/i", $sql, $m)) {
    db::$config[$m[1]] = $m[2];
    return array();
  }
  // GARAN-Daten im Katalog: entscheiden, ob der Duplikationsschutz bei der Deinstallation bleibt
  if (preg_match('/^SELECT products_id FROM products WHERE products_garan_duration IS NOT NULL/i', $sql)) {
    return db::$catalogue_data ? array(array('products_id' => 1)) : array();
  }
  if (preg_match("/^SELECT content_id FROM products_content WHERE content_type = 'garan_terms'/i", $sql)) {
    return db::$catalogue_data ? array(array('content_id' => 1)) : array();
  }
  if (preg_match('/^SHOW TABLES LIKE/i', $sql)) return db::$schema_ok ? array(1) : array();
  // MariaDB weist ORDER BY bei SHOW zurueck, MySQL nimmt es an. Die Attrappe verhaelt sich wie
  // MariaDB, sonst faellt so eine Abfrage erst im installierten Shop auf.
  if (preg_match('/^SHOW .*ORDER BY/i', $sql)) {
    echo "  FAIL  ORDER BY bei SHOW ist auf MariaDB ein Syntaxfehler\n        ".$sql."\n";
    $GLOBALS['show_order_by'] = true;
    return array();
  }
  if (preg_match('/^SHOW KEYS FROM (\S+)/i', $sql, $m)) {
    if (!db::$schema_ok) return array();
    preg_match("/Key_name = '([^']+)'/", $sql, $k);
    if ($k[1] === 'PRIMARY') {
      $spalte = (strpos($m[1], 'products') !== false) ? 'orders_products_guarantee_id' : 'orders_guarantee_id';
      return array(array('Column_name' => isset(db::$primary_column) ? db::$primary_column : $spalte, 'Non_unique' => '0', 'Seq_in_index' => '1'));
    }
    $unique = in_array($k[1], array('idx_orders_id', 'idx_orders_products_id'), true) ? '0' : '1';
    // idx_orders_id ist auf orders_products_guarantee bewusst nicht eindeutig
    if ($k[1] === 'idx_orders_id' && strpos($m[1], 'products') !== false) $unique = '1';
    $column = ($k[1] === 'idx_orders_products_id') ? 'orders_products_id' : 'orders_id';
    return array(array('Column_name' => $column, 'Non_unique' => $unique, 'Seq_in_index' => '1'));
  }
  if (preg_match('/^SHOW COLUMNS FROM (\S+) LIKE .(.*).$/i', $sql, $m)) {
    if (!db::$schema_ok) return array();
    return array(garan_spalte(str_replace('\\_', '_', $m[2])));
  }
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
    'Default' => ($name === 'content_type') ? db::$content_default : (($name === 'products_garan_duration') ? db::$duration_default : null),
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
function xtc_db_num_rows($r) { return is_array($r) ? count($r) : 1; }
// wie im Shop: jeder Aufruf liefert die naechste Zeile, sonst laeuft eine while-Schleife ewig
function xtc_db_fetch_array(&$r) {
  if (!is_array($r)) return array();
  $row = array_shift($r);
  return is_array($row) ? $row : array();
}
function xtc_db_perform($table, $data, $action = 'insert', $where = '') {
  if ($action == 'update' && isset($data['set_function']) && preg_match("/configuration_key = '(.*)'/", $where, $m)) {
    $GLOBALS['set_functions'][$m[1]] = $data['set_function'];
    return;
  }
  if ($action == 'update' && preg_match("/configuration_key = '(.*)'/", $where, $m)) {
    db::$config[$m[1]] = $data['configuration_value'];
  } else {
    db::$config[$data['configuration_key']] = $data['configuration_value'];
  }
}
class stack { public $msgs = array(); function add_session($t, $c = 'info') { $this->msgs[] = $c; } }
$messageStack = new stack();
$GLOBALS['set_functions'] = array();

require REPO.'admin/includes/modules/system/guarantee_labels.php';
require REPO.'admin/includes/modules/categories/guarantee_labels_product.php';

$pass = 0; $fail = 0;
function ok($n, $c, $e = '') { global $pass, $fail; if ($c) { $pass++; echo "  ok    $n\n"; } else { $fail++; echo "  FAIL  $n".($e!==''?"  ($e)":'')."\n"; } }

echo "== Registrierung durch das Systemmodul ==\n";
$m = new guarantee_labels();
$m->install();
ok('Statusschluessel angelegt', isset(db::$config['MODULE_GUARANTEE_LABELS_STATUS']),
   implode(' | ', array_merge($messageStack->msgs, $m->missing_requirements())));
ok('Klassenerweiterung aktiviert', isset(db::$config['MODULE_CATEGORIES_GUARANTEE_LABELS_PRODUCT_STATUS']));
ok('in MODULE_CATEGORIES_INSTALLED eingetragen', db::$config['MODULE_CATEGORIES_INSTALLED'] === 'guarantee_labels_product.php');
ok('Listing-Erweiterung aktiviert', isset(db::$config['MODULE_PRODUCT_GUARANTEE_LABELS_LISTING_STATUS']));
ok('in MODULE_PRODUCT_INSTALLED eingetragen', db::$config['MODULE_PRODUCT_INSTALLED'] === 'guarantee_labels_listing.php', db::$config['MODULE_PRODUCT_INSTALLED']);
ok('Bestell-Erweiterung aktiviert', isset(db::$config['MODULE_ORDER_GUARANTEE_LABELS_ORDER_STATUS']));
ok('in MODULE_ORDER_INSTALLED eingetragen', db::$config['MODULE_ORDER_INSTALLED'] === 'guarantee_labels_order.php', db::$config['MODULE_ORDER_INSTALLED']);

echo "\n== Wiederholte Installation ==\n";
$m->install();
ok('kein doppelter Eintrag', db::$config['MODULE_CATEGORIES_INSTALLED'] === 'guarantee_labels_product.php');

echo "\n== Fremde Erweiterungen bleiben erhalten ==\n";
// catCopyProductName als zweite installierte Erweiterung simulieren
db::$config['MODULE_CATEGORIES_OTHER_EXTENSION_STATUS'] = 'true';
$m2 = new guarantee_labels();
// die eigene Erweiterung ist bereits installiert, deshalb die Liste ausdruecklich neu aufbauen
$m2->update_class_extensions('categories');
ok('beide Erweiterungen gelistet', db::$config['MODULE_CATEGORIES_INSTALLED'] === 'guarantee_labels_product.php;other_extension.php', db::$config['MODULE_CATEGORIES_INSTALLED']);
$m->remove();
ok('nur der eigene Eintrag entfernt', db::$config['MODULE_CATEGORIES_INSTALLED'] === 'other_extension.php', db::$config['MODULE_CATEGORIES_INSTALLED']);
ok('Statusschluessel der Erweiterung entfernt', !isset(db::$config['MODULE_CATEGORIES_GUARANTEE_LABELS_PRODUCT_STATUS']));
ok('Listing-Erweiterung entfernt', !isset(db::$config['MODULE_PRODUCT_GUARANTEE_LABELS_LISTING_STATUS']));
ok('MODULE_PRODUCT_INSTALLED geleert', db::$config['MODULE_PRODUCT_INSTALLED'] === '', db::$config['MODULE_PRODUCT_INSTALLED']);
ok('Bestell-Erweiterung entfernt', !isset(db::$config['MODULE_ORDER_GUARANTEE_LABELS_ORDER_STATUS']));
ok('MODULE_ORDER_INSTALLED geleert', db::$config['MODULE_ORDER_INSTALLED'] === '', db::$config['MODULE_ORDER_INSTALLED']);
ok('Modulkonfiguration entfernt', !isset(db::$config['MODULE_GUARANTEE_LABELS_STATUS']));

echo "\n== Deinstallation mit GARAN-Daten im Katalog ==\n";
// duplicate_product() bietet keine andere Einhaengestelle, deshalb bleibt der Schutz stehen
db::$catalogue_data = true;
db::$config = array('MODULE_CATEGORIES_INSTALLED' => '', 'MODULE_PRODUCT_INSTALLED' => '', 'MODULE_ORDER_INSTALLED' => '',
                    'MODULE_GUARANTEE_LABELS_STATUS' => 'true', 'MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS' => '');
$m3 = new guarantee_labels();
$m3->update();
$m3->remove();
ok('Duplikationsschutz bleibt eingerichtet', db::$config['MODULE_CATEGORIES_INSTALLED'] === 'guarantee_labels_product.php', db::$config['MODULE_CATEGORIES_INSTALLED']);
ok('sein Status bleibt aktiv', isset(db::$config['MODULE_CATEGORIES_GUARANTEE_LABELS_PRODUCT_STATUS']));
ok('Listing-Erweiterung dennoch entfernt', db::$config['MODULE_PRODUCT_INSTALLED'] === '');
ok('Bestell-Erweiterung dennoch entfernt', db::$config['MODULE_ORDER_INSTALLED'] === '');
ok('Modulkonfiguration dennoch entfernt', !isset(db::$config['MODULE_GUARANTEE_LABELS_STATUS']));

echo "\n== Deinstallation ohne GARAN-Daten ==\n";
db::$catalogue_data = false;
db::$config = array('MODULE_CATEGORIES_INSTALLED' => '', 'MODULE_PRODUCT_INSTALLED' => '', 'MODULE_ORDER_INSTALLED' => '',
                    'MODULE_GUARANTEE_LABELS_STATUS' => 'true', 'MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS' => '');
$m4 = new guarantee_labels();
$m4->update();
$m4->remove();
ok('alle drei Erweiterungen entfernt', db::$config['MODULE_CATEGORIES_INSTALLED'] === ''
   && db::$config['MODULE_PRODUCT_INSTALLED'] === '' && db::$config['MODULE_ORDER_INSTALLED'] === '',
   db::$config['MODULE_CATEGORIES_INSTALLED']);

echo "\n== update() repariert eine entfernte Registrierung ==\n";
db::$config = array('MODULE_CATEGORIES_INSTALLED' => '', 'MODULE_PRODUCT_INSTALLED' => '', 'MODULE_ORDER_INSTALLED' => '', 'MODULE_GUARANTEE_LABELS_STATUS' => 'true', 'MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS' => '');
$r = $m->update();
ok('update erfolgreich', $r !== false);
ok('Registrierung wiederhergestellt', db::$config['MODULE_CATEGORIES_INSTALLED'] === 'guarantee_labels_product.php', db::$config['MODULE_CATEGORIES_INSTALLED']);
ok('Listing-Registrierung wiederhergestellt', db::$config['MODULE_PRODUCT_INSTALLED'] === 'guarantee_labels_listing.php', db::$config['MODULE_PRODUCT_INSTALLED']);
ok('Bestell-Registrierung wiederhergestellt', db::$config['MODULE_ORDER_INSTALLED'] === 'guarantee_labels_order.php', db::$config['MODULE_ORDER_INSTALLED']);
ok('Modulstatus unveraendert', db::$config['MODULE_GUARANTEE_LABELS_STATUS'] === 'true');

echo "\n== Die B2B-Auswahl bietet die Adminguppe nicht an ==\n";
// Gruppe 0 ist die Verwaltung, keine Kundengruppe. Sie anzubieten hiesse ein Kaestchen zeigen,
// das save_b2b_customers_status() beim Speichern wieder verwirft.
$ausdruck = $m->b2b_set_function();
ok('Auswahlliste filtert Gruppe 0', strpos($ausdruck, 'array_diff_key') !== false, $ausdruck);
ok('sie endet fuer den eval-Aufruf richtig', substr(trim($ausdruck), -1) === ',', $ausdruck);

// so wertet admin/module_export.php den gespeicherten Ausdruck aus
function xtc_get_customers_statuses() {
  return array(0 => array('id' => 0, 'text' => 'Admin'),
               1 => array('id' => 1, 'text' => 'Endkunde'),
               2 => array('id' => 2, 'text' => 'Stammkunde'));
}
function xtc_cfg_multi_checkbox($format, $separator, $checked, $key = '') {
  $format_array = (!is_array($format) && function_exists($format)) ? (array)$format() : (array)$format;
  $ids = array();
  foreach ($format_array as $k => $v) { $ids[] = isset($v['id']) ? $v['id'] : $k; }
  return implode(',', $ids);
}
eval('$angeboten = '.$ausdruck."'', 'KEY');");
ok('nur echte Kundengruppen im Kaestchen', $angeboten === '1,2', $angeboten);

echo "\n== update() zieht eine alte Auswahlliste nach ==\n";
db::$config['MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS'] = '';
$GLOBALS['set_functions'] = array('MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS' => 'xtc_cfg_multi_checkbox(\'xtc_get_customers_statuses\', \'chr(44)\',');
$m->update();
ok('alter Ausdruck ersetzt', isset($GLOBALS['set_functions']['MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS'])
   && strpos($GLOBALS['set_functions']['MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS'], 'array_diff_key') !== false,
   $GLOBALS['set_functions']['MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS']);

echo "\n== update() repariert einen abgeschalteten Status ==\n";
// Die Modullader lesen den Status jeder Erweiterung. Steht er auf false, fehlen Listenlabel,
// Bestelldaten oder der Duplikationsschutz, waehrend das Systemmodul sich aktiv meldet.
db::$config['MODULE_CATEGORIES_GUARANTEE_LABELS_PRODUCT_STATUS'] = 'false';
db::$config['MODULE_PRODUCT_GUARANTEE_LABELS_LISTING_STATUS'] = 'false';
db::$config['MODULE_ORDER_GUARANTEE_LABELS_ORDER_STATUS'] = 'false';
$m->update();
ok('Kategorieerweiterung wieder aktiv', db::$config['MODULE_CATEGORIES_GUARANTEE_LABELS_PRODUCT_STATUS'] === 'true');
ok('Listing-Erweiterung wieder aktiv', db::$config['MODULE_PRODUCT_GUARANTEE_LABELS_LISTING_STATUS'] === 'true');
ok('Bestell-Erweiterung wieder aktiv', db::$config['MODULE_ORDER_GUARANTEE_LABELS_ORDER_STATUS'] === 'true');
ok('Registrierung dabei unveraendert', db::$config['MODULE_CATEGORIES_INSTALLED'] === 'guarantee_labels_product.php');

echo "\n== Der Status steht nicht in der Maske ==\n";
// sonst haette der Shopbetreiber einen zweiten Schalter neben dem Systemmodul
$ext_keys = new guarantee_labels_product();
ok('kein Statusfeld in der Kategorieerweiterung',
   !in_array('MODULE_CATEGORIES_GUARANTEE_LABELS_PRODUCT_STATUS', $ext_keys->keys(), true),
   implode(' | ', $ext_keys->keys()));
ok('Sortierung bleibt einstellbar',
   in_array('MODULE_CATEGORIES_GUARANTEE_LABELS_PRODUCT_SORT_ORDER', $ext_keys->keys(), true));

echo "\n== Hook der Klassenerweiterung ==\n";
define('MODULE_GUARANTEE_LABELS_STATUS', 'true');
define('MODULE_CATEGORIES_GUARANTEE_LABELS_PRODUCT_STATUS', 'true');
$GLOBALS['manufacturers'] = array(1 => array('name' => 'ACME GmbH', 'status' => 1));
$ext = new guarantee_labels_product();
$in = array('products_garan_duration' => '2,5', 'products_manufacturers_model' => 'X-1', 'products_price' => '9.99');
$out = $ext->insert_product_before($in, array('manufacturers_id' => 1));
ok('Feld ohne Metriken bleibt unangetastet', !array_key_exists('products_garan_duration', $out));
ok('Fehlermeldung erzeugt', count($messageStack->msgs) > 0);
ok('andere Felder unangetastet', $out['products_price'] === '9.99');
$out = $ext->insert_product_before(array('products_garan_duration' => '', 'products_manufacturers_model' => 'X-1'), array('manufacturers_id' => 1));
ok('leeres Feld ergibt null', $out['products_garan_duration'] === 'null');
$untouched = array('products_price' => '1.00');
ok('ohne GARAN-Feld unveraendert durchgereicht', $ext->insert_product_before($untouched, array()) === $untouched);

echo "\n== Fehlermeldung an die Artikelverwaltung ==\n";
$bad = array('products_garan_duration' => '4.1', 'products_manufacturers_model' => 'X-1');
$ext->insert_product_before($bad, array('manufacturers_id' => 1));
ok('ungueltige Dauer meldet einen Fehler', $ext->insert_product_error(false, array(), 1) === true);
$ext->insert_product_before(array('products_garan_duration' => '', 'products_manufacturers_model' => 'X-1'), array('manufacturers_id' => 1));
ok('gueltiger Folgespeichervorgang meldet keinen Fehler', $ext->insert_product_error(false, array(), 1) === false);
ok('bestehender Fehler des Cores bleibt erhalten', $ext->insert_product_error(true, array(), 1) === true);

echo "\n== Durchreichung ueber categoriesModules ==\n";
define('MODULE_CATEGORIES_INSTALLED', 'guarantee_labels_product.php');
require REPO.'admin/includes/classes/categoriesModules.class.php';
$cat = new categoriesModules();
ok('Erweiterung geladen', in_array('guarantee_labels_product', $cat->modules));
$data = $cat->insert_product_before(array('products_garan_duration' => '4.1', 'products_manufacturers_model' => 'X-1'), array('manufacturers_id' => 1));
ok('Hook haelt den abgelehnten Wert aus dem Array', !array_key_exists('products_garan_duration', $data));
ok('Fehler wird durchgereicht', $cat->insert_product_error(false, array(), 1) === true);
$cat->insert_product_before(array('products_garan_duration' => '', 'products_manufacturers_model' => 'X-1'), array('manufacturers_id' => 1));
ok('kein Fehler bei leerem Feld', $cat->insert_product_error(false, array(), 1) === false);
ok('vorhandener Fehler bleibt true', $cat->insert_product_error(true, array(), 1) === true);

echo "\n== Artikel duplizieren ==\n";
$src = array('products_id' => 7, 'products_garan_duration' => '3.0', 'products_manufacturers_model' => 'X-1', 'products_price' => '9.99');
$dup = $ext->duplicate_product_before($src, 7, 1);
ok('Garantiedauer im Duplikat geleert', $dup['products_garan_duration'] === 'null');
ok('Modellkennung im Duplikat geleert', $dup['products_manufacturers_model'] === '');
ok('uebrige Daten unveraendert', $dup['products_price'] === '9.99' && $dup['products_id'] === 7);

$plain = array('products_id' => 8, 'products_garan_duration' => null, 'products_manufacturers_model' => 'Y-2');
ok('Artikel ohne Garantiedauer unveraendert', $ext->duplicate_product_before($plain, 8, 1) === $plain);
$empty = array('products_garan_duration' => '', 'products_manufacturers_model' => 'Y-2');
ok('leere Garantiedauer unveraendert', $ext->duplicate_product_before($empty, 8, 1) === $empty);

db::$log = array();
$ext->duplicate_product_end(42);
$deletes = array_filter(db::$log, function ($q) { return stripos($q, 'DELETE FROM products_content') === 0; });
ok('Garantiebedingungen des Duplikats entfernt', count($deletes) === 1);
ok('nur garan_terms und nur das Duplikat', count($deletes) === 1 && strpos(reset($deletes), "products_id = '42'") !== false && strpos(reset($deletes), "content_type = 'garan_terms'") !== false, reset($deletes));

echo "\n== B2B-Kundengruppen speichern ==\n";
define('DIR_FS_CATALOG_CACHE_STUB', 1);
function guarantee_labels_save($post) {
  $_POST = $post;
  db::$config['MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS'] = 'ALT';
  $m = new guarantee_labels();
  $m->save_b2b_customers_status();
  return db::$config['MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS'];
}
ok('Auswahl wird gespeichert', guarantee_labels_save(array('configuration' => array('MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS' => array('3', '5')))) === '3,5');
ok('alles abgewaehlt leert den Wert', guarantee_labels_save(array()) === '');
ok('leeres Formularfeld leert ebenso', guarantee_labels_save(array('configuration' => array())) === '');
ok('Reihenfolge normalisiert', guarantee_labels_save(array('configuration' => array('MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS' => array('5', '3')))) === '3,5');
ok('Doppelte entfernt', guarantee_labels_save(array('configuration' => array('MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS' => array('3', '3', '5')))) === '3,5');
ok('Muell verworfen', guarantee_labels_save(array('configuration' => array('MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS' => array('3', 'abc', '-1', '0')))) === '3');
ok('andere Modulkonfiguration unberuehrt', db::$config['MODULE_GUARANTEE_LABELS_STATUS'] !== '');

echo "\n== Aktivierung ohne Renderer ==\n";
class guarantee_labels_no_renderer extends guarantee_labels {
  function renderer_available() { return false; }
}
class guarantee_labels_with_renderer extends guarantee_labels {
  function renderer_available() { return true; }
}
$messageStack->msgs = array();
db::$config['MODULE_GUARANTEE_LABELS_STATUS'] = 'true';
$_POST = array();
$m = new guarantee_labels_no_renderer();
$m->process('guarantee_labels.php');
ok('Status wieder abgeschaltet', db::$config['MODULE_GUARANTEE_LABELS_STATUS'] === 'false');
ok('Grund gemeldet', count($messageStack->msgs) > 0);

$messageStack->msgs = array();
db::$config['MODULE_GUARANTEE_LABELS_STATUS'] = 'true';
$m = new guarantee_labels_with_renderer();
$m->process('guarantee_labels.php');
ok('mit Renderer bleibt der Status', db::$config['MODULE_GUARANTEE_LABELS_STATUS'] === 'true');
ok('keine Meldung', count($messageStack->msgs) === 0);

$messageStack->msgs = array();
db::$config['MODULE_GUARANTEE_LABELS_STATUS'] = 'false';
$m = new guarantee_labels_no_renderer();
$m->process('guarantee_labels.php');
ok('abgeschaltetes Modul wird nicht geprueft', count($messageStack->msgs) === 0);

echo "\n== Schema wird vollstaendig geprueft ==\n";
$s = new guarantee_labels();
ok('unveraendertes Schema ohne Befund', count($s->verify_schema()) === 0, implode(' | ', $s->verify_schema()));

db::$content_default = 'unerwartet';
ok('falscher Defaultwert faellt auf', count($s->verify_schema()) > 0);
db::$content_default = '';

// der Vergleich darf die Schreibweise des Defaults nicht veraendern
class grossschreiber extends guarantee_labels {
  function schema_columns() {
    return array(array('table' => 'products_content', 'column' => 'content_type',
                       'definition' => "VARCHAR(32) NOT NULL DEFAULT 'abc'", 'type' => 'varchar(32)', 'after' => 'content_link'));
  }
  function schema_indexes() { return array(); }
  function schema_primary_keys() { return array(); }
}
// eine Spalte ohne DEFAULT-Klausel darf auch keinen Default fuehren
class ohne_default extends guarantee_labels {
  function schema_columns() {
    return array(array('table' => 'products', 'column' => 'products_garan_duration',
                       'definition' => 'DECIMAL(4,1) NULL', 'type' => 'decimal(4,1)', 'after' => 'products_manufacturers_model'));
  }
  function schema_indexes() { return array(); }
  function schema_primary_keys() { return array(); }
}
db::$duration_default = '0.0';
$o = new ohne_default();
ok('unerwarteter Default der Garantiedauer faellt auf', count($o->verify_schema()) > 0, implode(' | ', $o->verify_schema()));
db::$duration_default = null;
ok('ohne Default kein Befund', count($o->verify_schema()) === 0, implode(' | ', $o->verify_schema()));

db::$content_default = 'abc';
$g = new grossschreiber();
ok('Default in Kleinschreibung gilt als gleich', count($g->verify_schema()) === 0, implode(' | ', $g->verify_schema()));
db::$content_default = '';

db::$primary_column = 'orders_id';
ok('abweichender Primaerschluessel faellt auf', count($s->verify_schema()) > 0, implode(' | ', $s->verify_schema()));
db::$primary_column = null;
ok('danach wieder ohne Befund', count($s->verify_schema()) === 0);

echo "\n== Meldung wird nicht doppelt kodiert ==\n";
$meldung = $s->incomplete_message(array('garan_label_colour.svg'), array('Die Spalte x der Tabelle y fehlt: &uuml;ber.'));
ok('Entities der Sprachdatei bleiben stehen', strpos($meldung, '&uuml;ber') !== false && strpos($meldung, '&amp;uuml;') === false, $meldung);
ok('Dateiname erscheint', strpos($meldung, 'garan_label_colour.svg') !== false);
$boese = $s->incomplete_message(array('<script>x</script>'));
ok('dynamischer Wert wird maskiert', strpos($boese, '<script>') === false && strpos($boese, '&lt;script&gt;') !== false, $boese);

echo "\n----------------------------------------\nbestanden: $pass   fehlgeschlagen: $fail\n";
exit($fail > 0 ? 1 : 0);
