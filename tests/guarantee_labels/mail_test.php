<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Prueft den Leseweg der Bestellbestaetigung: historischer Hinweis und archivierte Anhaenge.
error_reporting(E_ALL & ~E_DEPRECATED);
$guarantee_labels_paths = require __DIR__.'/bootstrap.php';
$root = $guarantee_labels_paths['shop'];
$repo = $guarantee_labels_paths['repo'];
$repo = $guarantee_labels_paths['repo'];

define('DIR_FS_CATALOG', $root.'/');
define('DIR_FS_DOCUMENT_ROOT', $root.'/');
define('DIR_WS_CATALOG', '/');
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
// derselbe Pfad, den der Produktivcode nimmt, sonst laedt require_once die Datei zweimal
require_once $root.'/inc/html_encoding.php';
require $repo.'/lang/german/extra/guarantee_labels.php';

// ---- Archivstand vorbereiten ------------------------------------------------
$hash = str_repeat('a', 64);
$notice_dir = $root.'/media/guarantee_labels/archive/notice/'.$hash.'/';
@mkdir($notice_dir, 0777, true);
file_put_contents($notice_dir.'notice.svg', '<svg></svg>');
// die archivierte Ueberschrift weicht bewusst von der aktuellen Sprachdatei ab
$guarantee_archive_title = 'Historische Gew&auml;hrleistung';
file_put_contents($notice_dir.'notice.json', json_encode(array(
  'version' => '1.01', 'language' => 'german',
  'text' => 'Gesetzliche Gew&auml;hrleistung von mindestens zwei Jahren.',
  'link' => 'Ihre Rechte nachlesen',
  'url' => 'https://europa.eu/youreurope/garantien',
  'title' => $guarantee_archive_title,
)));

// ein Archiv nach altem Stand, ohne Ueberschrift
$hash_alt = str_repeat('f', 64);
$notice_dir_alt = $root.'/media/guarantee_labels/archive/notice/'.$hash_alt.'/';
@mkdir($notice_dir_alt, 0777, true);
file_put_contents($notice_dir_alt.'notice.svg', '<svg></svg>');
file_put_contents($notice_dir_alt.'notice.json', json_encode(array(
  'version' => '1.00', 'language' => 'german',
  'text' => 'Alter Text.', 'link' => 'Alter Link',
  'url' => 'https://europa.eu/youreurope/garantien',
)));
$garan_hash = str_repeat('c', 64);
$garan_dir = $root.'/media/guarantee_labels/archive/garan/'.$garan_hash.'/';
@mkdir($garan_dir, 0777, true);
file_put_contents($garan_dir.'colour.svg', '<svg id="colour"></svg>');
file_put_contents($garan_dir.'nested.svg', '<svg id="nested"></svg>');
// der Anhang wird beim Versand gegen terms_hash geprueft, also echter Inhaltshash
$terms_hash = hash('sha256', 'PDF');
$terms_dir = $root.'/media/products/garan_archive/'.$terms_hash.'/';
@mkdir($terms_dir, 0777, true);
file_put_contents($terms_dir.'garantie.pdf', 'PDF');

// zweiter Anhang, dessen Inhalt spaeter nicht mehr zum Hash passt
$broken_hash = hash('sha256', 'ORIGINAL');
$broken_dir = $root.'/media/products/garan_archive/'.$broken_hash.'/';
@mkdir($broken_dir, 0777, true);
file_put_contents($broken_dir.'ersetzt.pdf', 'MANIPULIERT');

// ---- Datenbank-Stub ---------------------------------------------------------
$GLOBALS['tables'] = array('orders_guarantee', 'orders_products_guarantee');
$GLOBALS['notice_rows'] = array(4711 => $hash);
// 4711 ist eine Storefront-Bestellung, der Shop hat content_type gesetzt
$GLOBALS['sql_log'] = array();
$GLOBALS['content_types'] = array(4711 => 'physical', 4712 => 'physical');
$GLOBALS['download_rows'] = array();
$GLOBALS['product_rows'] = array(
  4711 => array(
    array('orders_products_id' => '10', 'manufacturers_name' => 'ACME GmbH', 'manufacturers_model' => 'WAU28T20', 'garan_duration' => '3.0', 'garan_hash' => str_repeat('c', 64), 'terms_hash' => $terms_hash, 'terms_filename' => 'garantie.pdf'),
    array('orders_products_id' => '11', 'manufacturers_name' => 'ACME GmbH', 'manufacturers_model' => 'X-2', 'garan_duration' => '5.0', 'garan_hash' => str_repeat('d', 64), 'terms_hash' => $terms_hash, 'terms_filename' => 'garantie.pdf'),
    array('orders_products_id' => '12', 'manufacturers_name' => 'ACME GmbH', 'manufacturers_model' => 'X-3', 'garan_duration' => '4.0', 'garan_hash' => str_repeat('e', 64), 'terms_hash' => null, 'terms_filename' => null),
  ),
);
$GLOBALS['queries'] = 0;

function xtc_db_query($sql) {
  if (strpos($sql, 'SHOW TABLES LIKE') === 0) {
    preg_match("/LIKE '([^']+)'/", $sql, $m);
    $name = str_replace('\\_', '_', $m[1]);
    return in_array($name, $GLOBALS['tables'], true) ? array(array($name)) : array();
  }
  $GLOBALS['queries']++;
  $GLOBALS['sql_log'][] = $sql;
  preg_match("/orders_id = '(\d+)'/", $sql, $m);
  $oid = isset($m[1]) ? (int)$m[1] : 0;
  if (strpos($sql, 'SELECT language') !== false) {
    return array(array('language' => 'german'));
  }
  if (strpos($sql, 'SELECT content_type') !== false) {
    // leer = manuell angelegt, dann entscheiden die Positionen
    return array(array('content_type' => isset($GLOBALS['content_types'][$oid]) ? $GLOBALS['content_types'][$oid] : ''));
  }
  if (strpos($sql, 'AS downloads') !== false) {
    return isset($GLOBALS['download_rows'][$oid]) ? $GLOBALS['download_rows'][$oid] : array();
  }
  if (strpos($sql, 'notice_hash') !== false) {
    return isset($GLOBALS['notice_rows'][$oid]) ? array(array('notice_hash' => $GLOBALS['notice_rows'][$oid])) : array();
  }
  return isset($GLOBALS['product_rows'][$oid]) ? $GLOBALS['product_rows'][$oid] : array();
}
function xtc_db_fetch_array(&$r) { return array_shift($r); }
function xtc_db_num_rows($r) { return count($r); }

require DIR_FS_INC.'guarantee_labels_order.inc.php';

$pass = 0; $fail = 0;
function ok($n, $c, $e = '') { global $pass, $fail; if ($c) { $pass++; echo "  ok    $n\n"; } else { $fail++; echo "  FAIL  $n".($e!==''?"  ($e)":'')."\n"; } }

echo "== Positionen der Bestellung ==\n";
$GLOBALS['queries'] = 0;
$products = guarantee_labels_order_products(4711);
ok('eine Abfrage fuer die ganze Bestellung', $GLOBALS['queries'] === 1, 'Abfragen: '.$GLOBALS['queries']);
ok('nach Positionsnummer geschluesselt', isset($products[10], $products[11], $products[12]));
ok('historische Werte', $products[10]['manufacturers_name'] === 'ACME GmbH' && $products[10]['garan_duration'] === '3.0');
$GLOBALS['queries'] = 0;
guarantee_labels_order_products(4711);
ok('zweiter Zugriff ohne Abfrage', $GLOBALS['queries'] === 0);
ok('Bestellung ohne Snapshot bleibt leer', guarantee_labels_order_products(9999) === array());

echo "\n== Historischer Hinweis ==\n";
$notice = guarantee_labels_order_notice(4711);
ok('Snapshot gelesen', is_array($notice));
ok('Text aus dem Archiv', $notice['text'] === 'Gesetzliche Gew&auml;hrleistung von mindestens zwei Jahren.');
ok('Link aus dem Archiv', $notice['url'] === 'https://europa.eu/youreurope/garantien');
$GLOBALS['queries'] = 0;
guarantee_labels_order_notice(4711);
ok('zweiter Zugriff ohne Abfrage', $GLOBALS['queries'] === 0);
ok('Bestellung ohne Zeile ohne Hinweis', guarantee_labels_order_notice(9999) === false);

echo "\n== Anhaenge ==\n";
$terms = guarantee_labels_order_terms(4711);
ok('genau eine Datei trotz zweier Positionen', count($terms) === 1, print_r($terms, true));
ok('absoluter Pfad', isset($terms[0]) && $terms[0] === $terms_dir.'garantie.pdf');
ok('Position ohne Bedingungen liefert nichts dazu', count($terms) === 1);
ok('Bestellung ohne Snapshot ohne Anhang', guarantee_labels_order_terms(9999) === array());

// Bestellung 4712 verweist auf einen Anhang, dessen Inhalt ersetzt wurde
$GLOBALS['product_rows'][4712] = array(
  array('orders_products_id' => '20', 'manufacturers_name' => 'ACME GmbH', 'manufacturers_model' => 'Y-1',
        'garan_duration' => '2.0', 'garan_hash' => $garan_hash,
        'terms_hash' => $broken_hash, 'terms_filename' => 'ersetzt.pdf'),
);
ok('ersetzter Anhang wird nicht versendet', guarantee_labels_order_terms(4712) === array());
ok('fehlender Anhang wird nicht versendet', guarantee_labels_order_terms(4713) === array());

// Text und Anhangsliste muessen zur selben Aussage kommen
$broken_text = guarantee_labels_order_text(4712, 20);
ok('Text nennt den beschaedigten Anhang nicht', $broken_text !== false && $broken_text['terms']['html'] === '',
   var_export($broken_text === false ? false : $broken_text['terms']['html'], true));
ok('Zusage steht trotzdem', $broken_text !== false && $broken_text['label']['html'] !== '');
$good_text = guarantee_labels_order_text(4711, 10);
ok('Text nennt den intakten Anhang', $good_text !== false && strpos($good_text['terms']['html'], 'garantie.pdf') !== false);
ok('intakter Anhang ist auch in der Liste', in_array($terms_dir.'garantie.pdf', guarantee_labels_order_terms(4711), true));

echo "\n== Datenaufbereitung fuer die Mail ==\n";
class order_stub { public $info = array('order_id' => 4711, 'language' => 'german'); }
class smarty_stub { public $vars = array(); function assign($k, $v) { $this->vars[$k] = $v; } }
$order = new order_stub();
$smarty = new smarty_stub();
$email_attachments = 'agb.pdf';
require $root.'/includes/extra/send_order/data/guarantee_labels.php';
ok('HTML-Variante mit Entities', strpos($smarty->vars['GUARANTEE_NOTICE_HTML'], 'Gew&auml;hrleistung') !== false);
ok('HTML-Variante mit Link', strpos($smarty->vars['GUARANTEE_NOTICE_HTML'], '<a href="https://europa.eu/youreurope/garantien">') !== false, $smarty->vars['GUARANTEE_NOTICE_HTML']);
ok('Textvariante ohne Entities', strpos($smarty->vars['GUARANTEE_NOTICE_TXT'], '&auml;') === false && strpos($smarty->vars['GUARANTEE_NOTICE_TXT'], 'hrleistung') !== false);
ok('Textvariante mit URL', strpos($smarty->vars['GUARANTEE_NOTICE_TXT'], 'https://europa.eu/youreurope/garantien') !== false);
ok('Ueberschrift mit Entities', strpos($smarty->vars['GUARANTEE_NOTICE_HEADING_HTML'], '&auml;') !== false, $smarty->vars['GUARANTEE_NOTICE_HEADING_HTML']);
ok('Ueberschrift stammt aus dem Archiv', $smarty->vars['GUARANTEE_NOTICE_HEADING_HTML'] === $guarantee_archive_title,
   $smarty->vars['GUARANTEE_NOTICE_HEADING_HTML'].' vs '.$guarantee_archive_title);
ok('Ueberschrift nicht aus der Sprachdatei', $smarty->vars['GUARANTEE_NOTICE_HEADING_HTML'] !== TEXT_GUARANTEE_NOTICE_TITLE);
ok('Ueberschrift ohne Entities', strpos($smarty->vars['GUARANTEE_NOTICE_HEADING_TXT'], '&auml;') === false && strpos($smarty->vars['GUARANTEE_NOTICE_HEADING_TXT'], 'hrleistung') !== false);
ok('keine Grafik in der Mail', strpos($smarty->vars['GUARANTEE_NOTICE_HTML'], 'notice.svg') === false && strpos($smarty->vars['GUARANTEE_NOTICE_HTML'], '<img') === false);
ok('Anhang ergaenzt', strpos($email_attachments, $terms_dir.'garantie.pdf') !== false);
ok('vorhandener Anhang bleibt', strpos($email_attachments, 'agb.pdf') === 0);

$order->info['order_id'] = 9999;
$smarty = new smarty_stub();
$email_attachments = '';
require $root.'/includes/extra/send_order/data/guarantee_labels.php';
ok('ohne Snapshot bleibt die Variable leer', $smarty->vars['GUARANTEE_NOTICE_HTML'] === '' && $smarty->vars['GUARANTEE_NOTICE_TXT'] === '');
ok('ohne Snapshot keine Ueberschrift', $smarty->vars['GUARANTEE_NOTICE_HEADING_HTML'] === '' && $smarty->vars['GUARANTEE_NOTICE_HEADING_TXT'] === '');
ok('ohne Snapshot kein Anhang', $email_attachments === '');


echo "\n== Garantie an der Bestellposition ==\n";
$order->info['order_id'] = 4711;
$text = guarantee_labels_order_text(4711, 10);
ok('Zeile erzeugt', is_array($text) && isset($text['label'], $text['terms']), var_export($text, true));
ok('Dauer ohne Nachkomma', is_array($text) && strpos($text['label']['txt'], '3 Jahre') !== false);
ok('Hersteller genannt', is_array($text) && strpos($text['label']['txt'], 'ACME GmbH') !== false);
ok('Modellkennung genannt', is_array($text) && strpos($text['label']['txt'], 'WAU28T20') !== false);
ok('Zusage nennt den Anhang nicht', is_array($text) && strpos($text['label']['txt'], 'garantie.pdf') === false);
ok('Anhang getrennt ausgewiesen', is_array($text) && strpos($text['terms']['txt'], 'garantie.pdf') !== false);
ok('Textfassung ohne Entities', is_array($text) && strpos($text['label']['txt'], '&uuml;') === false && strpos($text['terms']['txt'], '&uuml;') === false);
ok('kein Label als Grafik', is_array($text) && strpos($text['label']['html'], '<svg') === false && strpos($text['label']['html'], '<img') === false);
$plain = guarantee_labels_order_text(4711, 12);
ok('Position ohne Bedingungen ohne Anhangszeile', is_array($plain) && $plain['terms']['html'] === '' && $plain['terms']['txt'] === '');
ok('Position ohne Snapshot ohne Zeile', guarantee_labels_order_text(4711, 99) === false);


echo "\n== Grafik aus dem Archiv ==\n";
$markup = guarantee_labels_order_label(4711, 10);
ok('Markup erzeugt', $markup !== '' && strpos($markup, 'guarantee-label__compact') !== false);
// inline_svg() stellt den ids einen Praefix je Vorkommen voran
ok('kompakte Grafik aus dem Archiv', preg_match('/id="gl\d+-nested"/', $markup) === 1);
ok('historische Werte im Alternativtext', strpos($markup, 'ACME GmbH') !== false && strpos($markup, 'WAU28T20') !== false);
ok('Cachekopie angelegt', is_file($root.'/cache/guarantee_labels/'.$garan_hash.'/colour.svg'));
ok('Position ohne Snapshot ohne Grafik', guarantee_labels_order_label(4711, 99) === '');


echo "\n== Historischer Hinweis fuer eine Bestellansicht ==\n";
$parts = guarantee_labels_order_notice_parts(4711);
ok('Bestandteile erzeugt', is_array($parts) && isset($parts['title'], $parts['body']), var_export($parts, true));
ok('historischer Text, nicht der aktuelle', is_array($parts) && strpos($parts['body'], 'Gesetzliche Gew&auml;hrleistung von mindestens zwei Jahren.') !== false);
ok('historischer Link', is_array($parts) && strpos($parts['body'], 'https://europa.eu/youreurope/garantien') !== false);
ok('Grafik aus dem Archiv in den Cache kopiert', is_file($root.'/cache/guarantee_labels/'.$hash.'/notice.svg'));
ok('Grafik nicht in die Seite geschrieben', is_array($parts) && strpos($parts['body'], '<svg') === false);
ok('Bestellung ohne Snapshot ohne Hinweis', guarantee_labels_order_notice_parts(9999) === false);

echo "\n== Koerperliche Ware ==\n";
$GLOBALS['content_types'] = array(5001 => 'physical', 5002 => 'virtual', 5003 => 'mixed', 5004 => 'virtual_weight');
foreach (array(5001 => true, 5002 => false, 5003 => true, 5004 => false) as $oid => $expected) {
  ok('orders.content_type '.$GLOBALS['content_types'][$oid], guarantee_labels_order_physical($oid) === $expected);
}

// manuell angelegte Bestellung: content_type leer, die Positionen entscheiden
$GLOBALS['content_types'] = array();
$GLOBALS['download_rows'] = array(
  6001 => array(array('orders_products_id' => '1', 'attributes' => '0', 'downloads' => '0')),
  6002 => array(array('orders_products_id' => '1', 'attributes' => '1', 'downloads' => '1')),
  6003 => array(array('orders_products_id' => '1', 'attributes' => '1', 'downloads' => '1'),
                array('orders_products_id' => '2', 'attributes' => '0', 'downloads' => '0')),
  // eine einzelne Position mit einem Download- und einem koerperlichen Attribut
  6005 => array(array('orders_products_id' => '1', 'attributes' => '2', 'downloads' => '1')),
  // Downloadattribut entfernt: die Bestellung fuehrt weder Attribut noch Downloadzeile
  6006 => array(array('orders_products_id' => '1', 'attributes' => '1', 'downloads' => '0')),
);
ok('leerer content_type, nur physische Position', guarantee_labels_order_physical(6001) === true);
ok('leerer content_type, nur Download', guarantee_labels_order_physical(6002) === false);
ok('leerer content_type, gemischt', guarantee_labels_order_physical(6003) === true);
ok('leerer content_type, ohne Positionen', guarantee_labels_order_physical(6004) === false);
ok('leerer content_type, gemischte Einzelposition', guarantee_labels_order_physical(6005) === true);
ok('entferntes Downloadattribut wirkt sofort', guarantee_labels_order_physical(6006) === true);
ok('Einstufung fragt nur Bestelldaten', strpos(implode(' ', $GLOBALS['sql_log']), 'products_attributes_download') === false,
   'Katalogtabelle in der Abfrage');

echo "\n== Bestellsprache gegen Shopsprache ==\n";
ok('geladene Sprache wird erkannt', guarantee_labels_language('german') === true);
ok('fremde Bestellsprache abgewiesen', guarantee_labels_language('english') === false);
ok('leere Sprache abgewiesen', guarantee_labels_language('') === false);
ok('Pfadangriff abgewiesen', guarantee_labels_language('../../etc') === false);

// altes Archiv ohne Ueberschrift faellt auf die Sprachdatei zurueck
$GLOBALS['notice_rows'][4714] = $hash_alt;
$GLOBALS['content_types'][4714] = 'physical';
$GLOBALS['product_rows'][4714] = $GLOBALS['product_rows'][4711];
class order_stub_alt { public $info = array('order_id' => 4714, 'language' => 'german'); }
$order = new order_stub_alt();
$smarty = new smarty_stub();
$email_attachments = '';
require $root.'/includes/extra/send_order/data/guarantee_labels.php';
ok('altes Archiv nutzt die Sprachdatei', $smarty->vars['GUARANTEE_NOTICE_HEADING_HTML'] === TEXT_GUARANTEE_NOTICE_TITLE,
   $smarty->vars['GUARANTEE_NOTICE_HEADING_HTML']);
ok('alter Text bleibt historisch', strpos($smarty->vars['GUARANTEE_NOTICE_HTML'], 'Alter Text') !== false);

echo "\n== Maskierung der Bestellwerte ==\n";
$evil_hash = hash('sha256', 'EVIL');
$evil_dir = $root.'/media/products/garan_archive/'.$evil_hash.'/';
@mkdir($evil_dir, 0777, true);
// ein schliessendes Tag traegt einen Slash und scheitert schon am Dateinamenfilter,
// dieser Name kommt dagegen durch und muss bei der Ausgabe maskiert werden
$evil_name = '<img src=x onerror=alert(1)>.pdf';
file_put_contents($evil_dir.$evil_name, 'EVIL');
$GLOBALS['product_rows'][7001] = array(
  array('orders_products_id' => '70',
        'manufacturers_name' => 'ACME <script>alert(1)</script>',
        'manufacturers_model' => 'X"1 & <b>',
        'garan_duration' => '3.0', 'garan_hash' => $garan_hash,
        'terms_hash' => $evil_hash, 'terms_filename' => $evil_name),
);
$GLOBALS['content_types'][7001] = 'physical';
$evil = guarantee_labels_order_text(7001, 70);

ok('HTML-Fassung maskiert das Skript', $evil !== false && strpos($evil['label']['html'], '<script>') === false
   && strpos($evil['label']['html'], '&lt;script&gt;') !== false, $evil === false ? 'false' : $evil['label']['html']);
ok('HTML-Fassung maskiert Anfuehrungszeichen', $evil !== false && strpos($evil['label']['html'], 'X"1') === false);
ok('HTML-Fassung maskiert das Und-Zeichen', $evil !== false && strpos($evil['label']['html'], '&amp;') !== false);
ok('Textfassung behaelt die Rohwerte', $evil !== false && strpos($evil['label']['txt'], '<script>alert(1)</script>') !== false);
ok('Textfassung ohne Entities', $evil !== false && strpos($evil['label']['txt'], '&auml;') === false
   && strpos($evil['label']['txt'], '&amp;') === false);
ok('Dateiname besteht den Filter', guarantee_labels_terms_filename($evil_name) === $evil_name);
ok('Anhangsname maskiert', $evil !== false && strpos($evil['terms']['html'], '<img') === false
   && strpos($evil['terms']['html'], '&lt;img') !== false, $evil === false ? 'false' : $evil['terms']['html']);
ok('Anhangsname im Text unveraendert', $evil !== false && strpos($evil['terms']['txt'], $evil_name) !== false);

echo "\n== Hinweisblock der Bestellung ==\n";
$parts = guarantee_labels_order_notice_parts(4711);
ok('Block wird erzeugt', $parts !== false);
ok('Schliessen hat eine Beschriftung', $parts !== false
   && preg_match('/guarantee-label__close">.+?<\/button>/', $parts['body']) === 1);
ok('kein leerer Schliessen-Button', $parts !== false && strpos($parts['body'], '__close"></button>') === false);
ok('Ueberschrift gesetzt', $parts !== false && trim($parts['title']) !== '');
ok('historischer Text im Block', $parts !== false && strpos($parts['body'], 'hrleistung') !== false);


echo "\n----------------------------------------\n";
echo "bestanden: $pass   fehlgeschlagen: $fail\n";
exit($fail > 0 ? 1 : 0);
