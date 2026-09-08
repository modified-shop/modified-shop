<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Prueft die Bestellsnapshots: Hinweis je Bestellung, GARAN-Daten je Position.
error_reporting(E_ALL & ~E_DEPRECATED);
$guarantee_labels_paths = require __DIR__.'/bootstrap.php';
$root = $guarantee_labels_paths['shop'];
$repo = $guarantee_labels_paths['repo'];
$repo = $guarantee_labels_paths['repo'];

define('DIR_FS_CATALOG', $root.'/');
define('DIR_WS_CATALOG', '/');
define('DIR_FS_INC', $root.'/inc/');
define('DIR_WS_INCLUDES', 'includes/');
define('DIR_WS_CLASSES', 'includes/classes/');
define('DIR_FS_LOG', $root.'/log/');
define('DIR_FS_EXTERNAL', $root.'/includes/external/');
define('TABLE_MANUFACTURERS', 'manufacturers');
define('TABLE_PRODUCTS', 'products');
define('GROUP_CHECK', 'true');
define('TABLE_PRODUCTS_CONTENT', 'products_content');
define('TABLE_ORDERS_GUARANTEE', 'orders_guarantee');
define('TABLE_ORDERS_PRODUCTS_GUARANTEE', 'orders_products_guarantee');
define('MODULE_GUARANTEE_LABELS_STATUS', 'true');
$_SESSION['language'] = 'german';
$_SESSION['language_charset'] = 'UTF-8';
require $repo.'/lang/german/extra/guarantee_labels.php';

// ---- Datenbank-Stub ---------------------------------------------------------
$GLOBALS['rows'] = array();          // geschriebene Zeilen je Tabelle
$GLOBALS['man'] = array(1 => 'ACME GmbH');
// der Kunde gehoert zur Gruppe 1, wie der Anhang es verlangt
$_SESSION['customers_status'] = array('customers_status_id' => 1);
$GLOBALS['terms'] = array();         // products_id => content_file
$GLOBALS['terms_groups'] = 'c_1_group,';  // Kundengruppen, die den Anhang sehen duerfen
$GLOBALS['orders_guarantee'] = array();

function xtc_db_query($sql) {
  // die Schreibwege fragen jetzt wie die Lesewege zuerst nach der Tabelle
  if (strpos($sql, 'SHOW TABLES LIKE') === 0) {
    return isset($GLOBALS['tabellen_fehlen']) ? array() : array(array(1));
  }
  if (strpos($sql, 'manufacturers_name') !== false && strpos($sql, 'IN (') !== false) {
    preg_match_all("/\d+/", substr($sql, strpos($sql, 'IN (')), $m);
    $rows = array();
    foreach ($m[0] as $id) if (isset($GLOBALS['man'][(int)$id])) $rows[] = array('manufacturers_id' => $id, 'manufacturers_name' => $GLOBALS['man'][(int)$id]);
    return $rows;
  }
  if (strpos($sql, 'orders_guarantee_id') !== false) {
    preg_match("/orders_id = '(\d+)'/", $sql, $m);
    return isset($GLOBALS['orders_guarantee'][(int)$m[1]]) ? array(array('orders_guarantee_id' => 1)) : array();
  }
  if (strpos($sql, 'content_file') !== false) {
    preg_match("/products_id = '(\d+)'/", $sql, $m);
    $id = (int)$m[1];
    // group_ids wie im Shop: die Kundengruppe der Bestellung muss darin stehen
    return isset($GLOBALS['terms'][$id])
           ? array(array('content_file' => $GLOBALS['terms'][$id],
                         'group_ids' => isset($GLOBALS['terms_groups']) ? $GLOBALS['terms_groups'] : 'c_1_group,'))
           : array();
  }
  return array();
}
function xtc_db_fetch_array(&$r) { return array_shift($r); }
function xtc_db_num_rows($r) { return count($r); }
function xtc_db_perform($table, $data) {
  $GLOBALS['rows'][$table][] = $data;
  if ($table === 'orders_guarantee') $GLOBALS['orders_guarantee'][(int)$data['orders_id']] = true;
}

require DIR_FS_INC.'guarantee_labels_snapshot.inc.php';

$pass = 0; $fail = 0;
function ok($n, $c, $e = '') { global $pass, $fail; if ($c) { $pass++; echo "  ok    $n\n"; } else { $fail++; echo "  FAIL  $n".($e!==''?"  ($e)":'')."\n"; } }

echo "== Dateiname des Anhangs ==\n";
ok('normaler Name erlaubt',        guarantee_labels_terms_filename('garantie.pdf') === 'garantie.pdf');
ok('Pfadanteil abgelehnt',         guarantee_labels_terms_filename('sub/garantie.pdf') === false);
ok('Rueckwaertspfad abgelehnt',    guarantee_labels_terms_filename('../garantie.pdf') === false);
ok('Komma abgelehnt',              guarantee_labels_terms_filename('garantie,2024.pdf') === false);
ok('Steuerzeichen abgelehnt',      guarantee_labels_terms_filename("garantie\n.pdf") === false);
ok('leerer Name abgelehnt',        guarantee_labels_terms_filename('') === false);
ok('ohne Endung abgelehnt',        guarantee_labels_terms_filename('garantie') === false);
ok('Umlaut bleibt erlaubt',        guarantee_labels_terms_filename('garantie_äöü.pdf') === 'garantie_äöü.pdf');
// der Mailversand schneidet den ganzen Anhangspfad, ein Randleerzeichen liesse ihn ins Leere laufen
ok('Leerzeichen am Ende abgelehnt',  guarantee_labels_terms_filename('garantie.pdf ') === false);
ok('Leerzeichen am Anfang abgelehnt', guarantee_labels_terms_filename(' garantie.pdf') === false);
ok('Leerzeichen in der Mitte erlaubt', guarantee_labels_terms_filename('garantie 2026.pdf') === 'garantie 2026.pdf');

echo "\n== Dateityp des Anhangs ==\n";
$upload_dir = $root.'/upload/';
@mkdir($upload_dir, 0777, true);
file_put_contents($upload_dir.'garantie.pdf', "%PDF-1.4\n1 0 obj\n<</Type/Catalog>>\nendobj\ntrailer\n<</Root 1 0 R>>\n%%EOF\n");
file_put_contents($upload_dir.'garantie.txt', "Garantiebedingungen\n");
file_put_contents($upload_dir.'schad.php', "<?php echo 'x';\n");

ok('ohne Typliste nichts angenommen', guarantee_labels_terms_accepted('garantie.pdf', $upload_dir.'garantie.pdf') === false);

define('DIR_FS_ADMIN', $repo.'/admin/');
ok('PDF angenommen',   guarantee_labels_terms_accepted('garantie.pdf', $upload_dir.'garantie.pdf') === true);
ok('Text angenommen',  guarantee_labels_terms_accepted('garantie.txt', $upload_dir.'garantie.txt') === true);
ok('Skript abgelehnt', guarantee_labels_terms_accepted('schad.php', $upload_dir.'schad.php') === false);
// Endung und Inhalt muessen zusammenpassen
ok('Skript mit PDF-Endung abgelehnt', guarantee_labels_terms_accepted('schad.pdf', $upload_dir.'schad.php') === false);
ok('PDF mit Skriptendung abgelehnt',  guarantee_labels_terms_accepted('garantie.php', $upload_dir.'garantie.pdf') === false);
// alles, was am Artikel als Anhang zulaessig ist, muss auch in der Bestellung ersetzbar sein
$zip = $upload_dir.'garantie.zip';
$z = new ZipArchive();
$z->open($zip, ZipArchive::CREATE | ZipArchive::OVERWRITE);
$z->addFromString('garantie.txt', 'Garantiebedingungen');
$z->close();
ok('ZIP angenommen', guarantee_labels_terms_accepted('garantie.zip', $zip) === true);

echo "\n== Hinweis-Hash ==\n";
$hash = guarantee_labels_notice_hash('german');
ok('Hash erzeugt', is_string($hash) && strlen($hash) === 64, var_export($hash, true));
ok('stabil bei Wiederholung', guarantee_labels_notice_hash('german') === $hash);
ok('andere Sprache anderer Hash', guarantee_labels_notice_hash('english') !== $hash);
ok('fehlende Sprache ohne Hash', guarantee_labels_notice_hash('klingonisch') === false);
ok('Pfadanteil in der Sprache abgelehnt', guarantee_labels_notice_hash('../german') === false);

// Jedes archivierte Feld muss den Hash veraendern, sonst bekaeme eine neue Bestellung
// stillschweigend den alten Archivstand.
function garan_hash_ohne($weglassen) {
  $felder = array(
    'grafik' => hash_file('sha256', DIR_FS_CATALOG.'lang/german/notice.svg'),
    'sprache' => 'german',
    'text' => TEXT_GUARANTEE_NOTICE_MAIL,
    'link' => TEXT_GUARANTEE_NOTICE_LINK,
    'url' => TEXT_GUARANTEE_NOTICE_URL,
    'titel' => TEXT_GUARANTEE_NOTICE_TITLE,
    'version' => GUARANTEE_LABELS_NOTICE_VERSION,
  );
  $felder[$weglassen] = 'ANDERS';
  return guarantee_labels_hash_fields(array_values($felder));
}
$voll = guarantee_labels_hash_fields(array(
  hash_file('sha256', DIR_FS_CATALOG.'lang/german/notice.svg'),
  'german',
  TEXT_GUARANTEE_NOTICE_MAIL,
  TEXT_GUARANTEE_NOTICE_LINK,
  TEXT_GUARANTEE_NOTICE_URL,
  TEXT_GUARANTEE_NOTICE_TITLE,
  GUARANTEE_LABELS_NOTICE_VERSION,
));
ok('Hash deckt genau die archivierten Felder', $voll === $hash, $voll.' vs '.$hash);
foreach (array('grafik', 'sprache', 'text', 'link', 'url', 'titel', 'version') as $feld) {
  ok('geaendertes Feld '.$feld.' ergibt einen neuen Hash', garan_hash_ohne($feld) !== $hash);
}

echo "\n== Hinweis-Snapshot ==\n";
$GLOBALS['rows'] = array();
ok('Snapshot geschrieben', guarantee_labels_notice_snapshot(4711, 'german') === true);
ok('genau eine Zeile', count($GLOBALS['rows']['orders_guarantee']) === 1);
$row = $GLOBALS['rows']['orders_guarantee'][0];
ok('Bestellnummer gesetzt', $row['orders_id'] === 4711);
ok('Hash gespeichert', $row['notice_hash'] === $hash);
ok('zweiter Aufruf legt nichts an', guarantee_labels_notice_snapshot(4711, 'german') === true && count($GLOBALS['rows']['orders_guarantee']) === 1);
ok('Grafik archiviert', is_file($root.'/media/guarantee_labels/archive/notice/'.$hash.'/notice.svg'));
ok('Sprachstand archiviert', is_file($root.'/media/guarantee_labels/archive/notice/'.$hash.'/notice.json'));
$json = json_decode(file_get_contents($root.'/media/guarantee_labels/archive/notice/'.$hash.'/notice.json'), true);
ok('Mailtext im Archiv', $json['text'] === TEXT_GUARANTEE_NOTICE_MAIL);
ok('Link im Archiv', $json['url'] === TEXT_GUARANTEE_NOTICE_URL);
ok('unvollstaendige Sprache ohne Snapshot', guarantee_labels_notice_snapshot(4712, 'klingonisch') === false);
ok('ohne Bestellnummer kein Snapshot', guarantee_labels_notice_snapshot(0, 'german') === false);

echo "\n== GARAN-Snapshot je Position ==\n";
$GLOBALS['rows'] = array();
$product = array('products_id' => 1, 'products_garan_duration' => '3.0', 'products_manufacturers_model' => 'WAU28T20', 'manufacturers_id' => 1);
ok('Snapshot geschrieben', guarantee_labels_product_snapshot(4711, 99, $product, 2) === true);
$row = $GLOBALS['rows']['orders_products_guarantee'][0];
ok('Bestellnummer gesetzt', $row['orders_id'] === 4711);
ok('Positionsnummer gesetzt', $row['orders_products_id'] === 99);
ok('Herstellername aus der aktiven Abfrage', $row['manufacturers_name'] === 'ACME GmbH');
ok('Modellkennung gesetzt', $row['manufacturers_model'] === 'WAU28T20');
ok('Dauer kanonisch normalisiert', $row['garan_duration'] === '3.0', var_export($row['garan_duration'], true));
ok('Hash gesetzt', strlen($row['garan_hash']) === 64);
ok('ohne Anhang bleibt terms leer', $row['terms_hash'] === 'null' && $row['terms_filename'] === 'null');
ok('Label archiviert', is_file($root.'/media/guarantee_labels/archive/garan/'.$row['garan_hash'].'/colour.svg'));
ok('kompaktes Label archiviert', is_file($root.'/media/guarantee_labels/archive/garan/'.$row['garan_hash'].'/nested.svg'));

$GLOBALS['rows'] = array();
ok('zwei Jahre ohne Snapshot', guarantee_labels_product_snapshot(4711, 100, array('products_id' => 1, 'products_garan_duration' => '2.0', 'products_manufacturers_model' => 'X', 'manufacturers_id' => 1), 2) === false);
ok('ohne Modellkennung kein Snapshot', guarantee_labels_product_snapshot(4711, 101, array('products_id' => 1, 'products_garan_duration' => '3.0', 'products_manufacturers_model' => '', 'manufacturers_id' => 1), 2) === false);

ok('inaktiver Hersteller kein Snapshot', guarantee_labels_product_snapshot(4711, 102, array('products_id' => 1, 'products_garan_duration' => '3.0', 'products_manufacturers_model' => 'X', 'manufacturers_id' => 9), 2) === false);
ok('ohne Positionsnummer kein Snapshot', guarantee_labels_product_snapshot(4711, 0, $product, 2) === false);
ok('nichts geschrieben', !isset($GLOBALS['rows']['orders_products_guarantee']));

echo "\n== Garantiebedingungen ==\n";
@mkdir($root.'/media/products/', 0777, true);
file_put_contents($root.'/media/products/garantie.pdf', 'PDF-Inhalt');
$GLOBALS['terms'][1] = 'garantie.pdf';
$GLOBALS['rows'] = array();
ok('Snapshot mit Anhang', guarantee_labels_product_snapshot(4711, 103, $product, 2) === true);
$row = $GLOBALS['rows']['orders_products_guarantee'][0];
ok('Hash des Anhangs', $row['terms_hash'] === hash('sha256', 'PDF-Inhalt'));
ok('Dateiname des Anhangs', $row['terms_filename'] === 'garantie.pdf');
ok('Anhang archiviert', is_file($root.'/media/guarantee_labels/archive/terms/'.$row['terms_hash'].'/garantie.pdf'));

$GLOBALS['terms'][1] = 'sub/garantie.pdf';
$GLOBALS['rows'] = array();
guarantee_labels_product_snapshot(4711, 104, $product, 2);
$row = $GLOBALS['rows']['orders_products_guarantee'][0];
ok('unbrauchbarer Dateiname wird verworfen', $row['terms_hash'] === 'null' && $row['terms_filename'] === 'null');

$GLOBALS['terms'][1] = 'fehlt.pdf';
$GLOBALS['rows'] = array();
guarantee_labels_product_snapshot(4711, 105, $product, 2);
$row = $GLOBALS['rows']['orders_products_guarantee'][0];
ok('fehlende Datei wird verworfen', $row['terms_hash'] === 'null');

echo "\n== Eine Liste fuer Diagnose, Checkout und Snapshot ==\n";
require_once DIR_FS_INC.'guarantee_labels_output.inc.php';
$liste = guarantee_labels_notice_constants();
ok('Liste nennt Titel, Storefront- und Mailtext', in_array('TEXT_GUARANTEE_NOTICE_TITLE', $liste, true)
   && in_array('TEXT_GUARANTEE_NOTICE_TEXT', $liste, true)
   && in_array('TEXT_GUARANTEE_NOTICE_MAIL', $liste, true));
ok('Liste nennt Link und Adresse', in_array('TEXT_GUARANTEE_NOTICE_LINK', $liste, true)
   && in_array('TEXT_GUARANTEE_NOTICE_URL', $liste, true));
ok('Liste nennt keine Labelkonstante', count(preg_grep('/^TEXT_GUARANTEE_LABEL_/', $liste)) === 0);
ok('deutsche Sprachdatei ist vollstaendig', guarantee_labels_texts_ready($liste) === true);

echo "\n== Fehler erreichen den Admin ==\n";
guarantee_labels_snapshot_failures();
ok('anfangs nichts gesammelt', guarantee_labels_snapshot_failures() === array());
guarantee_labels_snapshot_log('garan', 4711, array('Archiv nicht beschreibbar'));
$gesammelt = guarantee_labels_snapshot_failures();
ok('Fehler gesammelt', count($gesammelt) === 1 && $gesammelt[0] === 'Archiv nicht beschreibbar');
ok('nach dem Abholen geleert', guarantee_labels_snapshot_failures() === array());
guarantee_labels_snapshot_log('garan', 4711, array());
ok('leere Fehlerliste sammelt nichts', guarantee_labels_snapshot_failures() === array());
guarantee_labels_snapshot_log('terms', 1, array('a'));
guarantee_labels_snapshot_log('notice', 2, array('b', 'c'));
ok('mehrere Aufrufe werden gesammelt', count(guarantee_labels_snapshot_failures()) === 3);

echo "\n== Sichtbarkeit des Anhangs ==\n";
// dieselbe Regel wie includes/define_conditions.php
ok('ausgewaehlte Gruppe sieht ihn', guarantee_labels_terms_visible('c_1_group,c_2_group,', 2) === true);
ok('nicht ausgewaehlte Gruppe sieht ihn nicht', guarantee_labels_terms_visible('c_1_group,', 2) === false);
ok('leere Auswahl erreicht niemanden', guarantee_labels_terms_visible('', 1) === false);
ok('Gastgruppe 0 nur wenn ausgewaehlt', guarantee_labels_terms_visible('c_0_group,', 0) === true);
ok('Gastgruppe 0 sonst nicht', guarantee_labels_terms_visible('c_1_group,', 0) === false);
ok('Teiltreffer zaehlt nicht', guarantee_labels_terms_visible('c_12_group,', 1) === false);

echo "\n----------------------------------------\n";
echo "bestanden: $pass   fehlgeschlagen: $fail\n";
exit($fail > 0 ? 1 : 0);
