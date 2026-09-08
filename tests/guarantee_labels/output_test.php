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
$root = $guarantee_labels_paths['shop'];
$repo = $guarantee_labels_paths['repo'];
define('DIR_FS_CATALOG', $root.'/');
define('DIR_WS_INCLUDES', 'includes/');
define('DIR_WS_CLASSES', 'includes/classes/');
define('DIR_FS_INC', $root.'/inc/');
define('DIR_FS_LOG', $root.'/log/');
define('DIR_FS_EXTERNAL', $root.'/includes/external/');
define('TABLE_MANUFACTURERS', 'manufacturers');
define('MODULE_GUARANTEE_LABELS_STATUS', 'true');
// wie in einem UTF-8-Shop; die Shop-Helfer richten sich nach diesem Wert
$_SESSION['language_charset'] = 'UTF-8';

// Hersteller 1 und 2 aktiv, 3 inaktiv
$GLOBALS['man'] = array(1 => 'ACME GmbH', 2 => 'Miele & Cie. KG');
$GLOBALS['queries'] = 0;
function xtc_db_query($sql) {
  $GLOBALS['queries']++;
  preg_match_all("/\d+/", substr($sql, strpos($sql, 'IN (')), $m);
  $rows = array();
  foreach ($m[0] as $id) if (isset($GLOBALS['man'][(int)$id])) $rows[] = array('manufacturers_id' => $id, 'manufacturers_name' => $GLOBALS['man'][(int)$id]);
  return $rows;
}
function xtc_db_fetch_array(&$r) { return array_shift($r); }
require DIR_FS_INC.'guarantee_labels_output.inc.php';

$pass = 0; $fail = 0;
function ok($n, $c, $e = '') { global $pass, $fail; if ($c) { $pass++; echo "  ok    $n\n"; } else { $fail++; echo "  FAIL  $n".($e!==''?"  ($e)":'')."\n"; } }
function p($duration, $man = 1, $model = 'WAU28T20') {
  return array('products_id' => 1, 'products_garan_duration' => $duration, 'manufacturers_id' => $man, 'products_manufacturers_model' => $model);
}

echo "== Kandidatenpruefung ==\n";
ok('qualifizierende Dauer',            guarantee_labels_candidate(p('3.0')) === true);
ok('genau zwei Jahre nicht',           guarantee_labels_candidate(p('2.0')) === false);
ok('ein Jahr nicht',                   guarantee_labels_candidate(p('1.0')) === false);
ok('NULL nicht',                       guarantee_labels_candidate(p(null)) === false);
ok('ohne Hersteller nicht',            guarantee_labels_candidate(p('3.0', 0)) === false);
ok('ohne Modellkennung nicht',         guarantee_labels_candidate(p('3.0', 1, '')) === false);
ok('fehlende Spalte nicht',            guarantee_labels_candidate(array('products_id' => 1)) === false);

echo "\n== Sammelabfrage ==\n";
$GLOBALS['queries'] = 0;
$block = array(p('3.0', 1), p('5.0', 1), p('2.5', 2), p('4.0', 3), p('2.0', 1), p(null, 2));
$names = guarantee_labels_collect_manufacturers($block);
ok('genau eine Abfrage fuer den ganzen Block', $GLOBALS['queries'] === 1, 'Abfragen: '.$GLOBALS['queries']);
ok('aktive Hersteller geladen', $names === array(1 => 'ACME GmbH', 2 => 'Miele & Cie. KG'), var_export($names, true));
$GLOBALS['queries'] = 0;
guarantee_labels_collect_manufacturers($block);
ok('zweiter Block ohne weitere Abfrage', $GLOBALS['queries'] === 0);
$GLOBALS['queries'] = 0;
ok('Block ohne Kandidaten fragt nicht', guarantee_labels_collect_manufacturers(array(p('2.0'), p(null))) === array() && $GLOBALS['queries'] === 0);

echo "\n== Label je Artikel ==\n";
$label = guarantee_labels_product_label(p('3.0', 1), $names);
ok('Label erzeugt', is_array($label) && isset($label['hash'], $label['colour.svg'], $label['nested.svg']));
ok('Herstellername aus der Sammelabfrage', strpos($label['colour.svg'], '>ACME GmbH</tspan>') !== false);
ok('Modellkennung gesetzt', strpos($label['colour.svg'], '>WAU28T20</tspan>') !== false);
ok('inaktiver Hersteller ergibt kein Label', guarantee_labels_product_label(p('4.0', 3), $names) === false);
ok('nicht qualifizierende Dauer ergibt kein Label', guarantee_labels_product_label(p('2.0', 1), $names) === false);
$half = guarantee_labels_product_label(p('2.5', 2), $names);
ok('Sonderzeichen im Herstellernamen maskiert', strpos($half['colour.svg'], 'Miele &amp; Cie. KG') !== false);
ok('halbes Jahr mit Komma', strpos($half['colour.svg'], '>2,5</tspan>') !== false);

echo "\n== Cachefehler erreicht den Admin ==\n";
// Ein fehlgeschlagener Cacheschreibvorgang haelt das Label nicht auf, darf aber nicht nur im
// Log stehen: eine Adminaktion muss ihn ueber den messageStack melden koennen.
require DIR_FS_INC.'guarantee_labels_snapshot.inc.php';
guarantee_labels_snapshot_failures();
$cache_root = $root.'/cache/guarantee_labels';
@mkdir($cache_root, 0777, true);
@chmod($cache_root, 0555);
$gesperrt = guarantee_labels_product_label(p('7.0', 1), $names);
@chmod($cache_root, 0777);
$gemeldet = guarantee_labels_snapshot_failures();
ok('Label trotz Cachefehler erzeugt', is_array($gesperrt) && isset($gesperrt['colour.svg']));
ok('Cachefehler gesammelt', count($gemeldet) > 0, implode(' | ', $gemeldet));

echo "\n== Markup ==\n";
define('TEXT_GUARANTEE_LABEL_TITLE', 'EU-Haltbarkeitsgarantie');
define('TEXT_GUARANTEE_LABEL_OPEN', 'Vollst&auml;ndiges Label anzeigen');
define('TEXT_GUARANTEE_LABEL_CLOSE', 'Schliessen');
define('TEXT_GUARANTEE_LABEL_LINK', 'Weitere Informationen');
define('TEXT_GUARANTEE_LABEL_RELOAD', 'Bitte neu laden');
define('TEXT_GUARANTEE_LABEL_URL', 'https://europa.eu/youreurope/commercial-guarantee-durability');
define('TEXT_GUARANTEE_LABEL_ALT', 'EU-Label: %1$s Jahre Garantie von %2$s fuer %3$s.');
define('TEXT_GUARANTEE_LABEL_ALT_COMPACT', 'EU-Label: %s Jahre.');
define('TEXT_GUARANTEE_NOTICE_TITLE', 'Gesetzliche Gewaehrleistung');
define('TEXT_GUARANTEE_NOTICE_TEXT', 'Mindestens zwei Jahre.');
// gehoert zur Vollstaendigkeit: ohne Mailtext entstuende kein Snapshot zur Bestellung
define('TEXT_GUARANTEE_NOTICE_MAIL', 'Mindestens zwei Jahre, Mailfassung.');
define('TEXT_GUARANTEE_NOTICE_MIXED', 'Gilt fuer die koerperlichen Waren.');
define('TEXT_GUARANTEE_NOTICE_OPEN', 'Vergroessert anzeigen');
define('TEXT_GUARANTEE_NOTICE_LINK', 'Ihre Rechte nachlesen');
define('TEXT_GUARANTEE_NOTICE_ALT', 'Hinweis zur gesetzlichen Gewaehrleistung.');
define('TEXT_GUARANTEE_NOTICE_URL', 'https://europa.eu/youreurope/garantien');
define('DIR_WS_CATALOG', '/');
$m = guarantee_labels_markup($label);
ok('ohne Label leeres Markup', guarantee_labels_markup(false) === '');
ok('kompaktes Label als Schaltflaeche', substr_count($m, 'guarantee-label__compact') === 1);
ok('vollstaendiges Label im Dialog', strpos($m, '<dialog class="guarantee-label__dialog"') !== false);
ok('nur das kompakte Label inline', substr_count($m, '<svg') === 1);
ok('kompakt traegt nur die Dauer', substr_count($m, '<text') === 1);
ok('Markup bleibt klein', strlen($m) < 12000, strlen($m).' Bytes');
ok('Quelle des vollen Labels hinterlegt', strpos($m, 'data-guarantee-label-src="/cache/guarantee_labels/'.$label['hash'].'/colour.svg"') !== false);
ok('Meldung fuer den Fehlerfall hinterlegt', strpos($m, 'data-guarantee-label-error=') !== false);
ok('Platzhalter fuer die Grafik vorhanden', strpos($m, 'guarantee-label__graphic') !== false);
ok('Inhalt fuer die Lightbox adressierbar', preg_match('/data-guarantee-label-content="(guarantee-label-content-\\d+)"/', $m, $cid) === 1 && strpos($m, 'id="'.$cid[1].'"') !== false);
ok('Titel fuer die Lightbox hinterlegt', strpos($m, 'data-guarantee-label-title="EU-Haltbarkeitsgarantie"') !== false);

echo "\n== Namensraum je eingebetteter Grafik ==\n";
$one = guarantee_labels_inline_svg('<svg><style>.cls-1{fill:red}</style><g id="clippath" class="cls-1" clip-path="url(#clippath)"/></svg>');
$two = guarantee_labels_inline_svg('<svg><style>.cls-1{fill:blue}</style><g id="clippath" class="cls-1" clip-path="url(#clippath)"/></svg>');
ok('Klassen erhalten eigene Praefixe', $one !== $two && strpos($one, '.cls-1{') === false);
ok('IDs erhalten eigene Praefixe', substr_count($one.$two, 'id="clippath"') === 0);
ok('Referenz zeigt auf die eigene ID', preg_match('/id="(gl\\d+-clippath)"/', $one, $a) && strpos($one, 'url(#'.$a[1].')') !== false);
ok('zweite Grafik referenziert ihre eigene', preg_match('/id="(gl\\d+-clippath)"/', $two, $b) && strpos($two, 'url(#'.$b[1].')') !== false && $a[1] !== $b[1]);
ok('fremde Klassen unangetastet', strpos(guarantee_labels_inline_svg('<svg class="foo cls-2"/>'), 'class="foo gl') !== false);

// Ohne beschreibbaren Cache muss das volle Label wieder inline stehen. Das Label traegt seit dem
// Umbau selbst, ob die Cachekopie steht: der Renderer hat sie gerade gelesen oder geschrieben,
// und ein zweites Nachsehen wuerde dieselben 294 kB erneut lesen und hashen.
$dir = $root.'/cache/guarantee_labels/'.$label['hash'];
$keep = array('colour.svg' => file_get_contents($dir.'/colour.svg'));
$cache_root = $root.'/cache/guarantee_labels';
foreach (glob($dir.'/*') as $datei) { @unlink($datei); }
@rmdir($dir);
@chmod($cache_root, 0555);
$r_ohne = new guarantee_labels_renderer();
$label_ohne = $r_ohne->label('ACME GmbH', 'WAU28T20', '3.0');
@chmod($cache_root, 0777);
ok('ohne Cache meldet das Label das auch', is_array($label_ohne) && $label_ohne['cached'] === false,
   is_array($label_ohne) ? var_export($label_ohne['cached'], true) : 'kein Label');
$m2 = guarantee_labels_markup($label_ohne);
ok('ohne Cachedatei beide Varianten inline', substr_count($m2, '<svg') === 2);
ok('dann keine Quelle hinterlegt', strpos($m2, 'data-guarantee-label-src') === false);
@mkdir($dir, 0777, true);
file_put_contents($dir.'/colour.svg', $keep['colour.svg']);
ok('Link auf das QR-Ziel gesetzt', strpos($m, 'href="https://europa.eu/youreurope/commercial-guarantee-durability"') !== false);

echo "\n== Barrierefreiheit ==\n";
ok('Schaltflaeche nennt Inhalt und Aktion', preg_match('/<button[^>]*aria-label="EU-Label: 3 Jahre\\. [^"]+"/', $m) === 1);
ok('kompaktes SVG ist dekorativ', strpos($m, '<svg aria-hidden="true"') !== false);
ok('volles Label hat eine Textalternative', preg_match('/__graphic" role="img" aria-label="EU-Label: 3 Jahre Garantie von ACME GmbH fuer WAU28T20\\."/', $m) === 1);
ok('Umlaut im Attribut nicht doppelt maskiert', strpos($m, '&amp;auml;') === false);
ok('Umlaut kommt entschluesselt an', preg_match('/title="Vollst\xc3\xa4ndiges Label anzeigen"/', $m) === 1);
ok('Helfer: Entity wird Zeichen, echtes Kaufmanns-Und bleibt maskiert',
   guarantee_labels_attribute('a &auml; &amp; "b"') === "a \u{00e4} &amp; &quot;b&quot;",
   guarantee_labels_attribute('a &auml; &amp; "b"'));
ok('Schliessen-Schaltflaeche vorhanden', strpos($m, 'guarantee-label__close') !== false);

echo "\n== Inline-SVG ==\n";
ok('XML-Deklaration entfernt', strpos(guarantee_labels_inline_svg('<?xml version="1.0"?><svg><g/></svg>'), '<?xml') === false);
ok('Doctype entfernt', strpos(guarantee_labels_inline_svg('<!DOCTYPE svg><svg/>'), 'DOCTYPE') === false);
ok('Kommentar entfernt', strpos(guarantee_labels_inline_svg('<!-- x --><svg/>'), '<!--') === false);
ok('Grafikinhalt unveraendert', guarantee_labels_inline_svg('<svg><path d="M1 1"/></svg>') === '<svg aria-hidden="true"><path d="M1 1"/></svg>');
ok('mit Alternativtext wird sie vorgelesen', guarantee_labels_inline_svg('<svg><path d="M1 1"/></svg>', 'Ein Label') === '<svg role="img" aria-label="Ein Label"><path d="M1 1"/></svg>');

echo "\n== Zeichensatz des Shops ==\n";
ok('UTF-8-Shop liefert UTF-8', guarantee_labels_attribute('&auml;') === "\u{00e4}");
$_SESSION['language_charset'] = 'ISO-8859-1';
ok('Latin-1-Shop liefert Latin-1', guarantee_labels_attribute('&auml;') === chr(0xE4), bin2hex(guarantee_labels_attribute('&auml;')));
ok('echtes Kaufmanns-Und bleibt maskiert', guarantee_labels_attribute('Miele & Cie.') === 'Miele &amp; Cie.');
$_SESSION['language_charset'] = 'UTF-8';

echo "\n== B2B-Ausschluss ==\n";
ok('ohne Ausschluss aktiv', guarantee_labels_active() === true);
define('MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS', '3,7');
$_SESSION['customers_status']['customers_status_id'] = 2;
ok('B2C-Gruppe sieht Label', guarantee_labels_active() === true);
$_SESSION['customers_status']['customers_status_id'] = 3;
ok('B2B-Gruppe sieht keins', guarantee_labels_active() === false);
$_SESSION['customers_status']['customers_status_id'] = 7;
ok('zweite B2B-Gruppe ebenso', guarantee_labels_active() === false);
unset($_SESSION['customers_status']);
ok('Gast gilt als B2C', guarantee_labels_active() === true);


echo "
== Gewaehrleistungshinweis ==
";
$_SESSION['language'] = 'german';
$notice = guarantee_labels_notice(false);
ok('Hinweis ohne Downloads erzeugt', strpos($notice, 'guarantee-notice') !== false);
ok('Grafik nur als Quelle hinterlegt', strpos($notice, 'data-guarantee-label-img="/lang/german/notice.svg"') !== false);
ok('Grafik nicht in die Seite geschrieben', strpos($notice, '<svg') === false);
ok('kein Bild vorab geladen', strpos($notice, '<img') === false);
ok('Bedienelement statt Vorschau', strpos($notice, 'guarantee-notice__open') !== false);
ok('direkter Link enthalten', strpos($notice, TEXT_GUARANTEE_NOTICE_URL) !== false);
ok('Hinweis bleibt schlank', strlen($notice) < 1500, strlen($notice).' Bytes');
ok('koerperliche Ware zeigt den Hinweis', strpos(guarantee_labels_notice('physical'), 'guarantee-notice') !== false);
$mixed = guarantee_labels_notice('mixed');
ok('gemischter Warenkorb zeigt den Hinweis', strpos($mixed, 'guarantee-notice') !== false);
ok('gemischter Warenkorb ordnet ihn zu', strpos($mixed, 'guarantee-notice__mixed') !== false);
ok('nur digitale Inhalte ohne Hinweis', guarantee_labels_notice('virtual') === '');
// Die Grafik illustriert den Hinweis, sie ist nicht der Hinweis. Fehlt sie, gehen Text und Link
// trotzdem hinaus: ein fehlendes Bild darf keine gesetzlich geforderte Angabe von der Seite
// nehmen. Nur die Schaltflaeche entfaellt, sie wuerde einen leeren Dialog oeffnen.
$_SESSION['language'] = 'klingonisch';
$ohne_grafik = guarantee_labels_notice(false);
ok('Sprache ohne Grafik behaelt den Hinweis', strpos($ohne_grafik, 'guarantee-notice') !== false);
ok('der Text steht drin', strpos($ohne_grafik, 'guarantee-notice__text') !== false);
ok('der Link steht drin', strpos($ohne_grafik, 'guarantee-notice__link') !== false);
ok('keine Schaltflaeche ohne Grafik', strpos($ohne_grafik, 'guarantee-notice__open') === false);
ok('kein leerer Dialog', strpos($ohne_grafik, '<dialog') === false);
$_SESSION['language'] = 'german';
$mit_grafik = guarantee_labels_notice(false);
ok('mit Grafik erscheint die Schaltflaeche', strpos($mit_grafik, 'guarantee-notice__open') !== false);
$parts = guarantee_labels_notice_parts(false);
ok('Bestandteile getrennt abrufbar', is_array($parts) && isset($parts['title'], $parts['body']));
ok('Titel ohne Markup', strpos($parts['title'], '<') === false);
ok('Rumpf ohne Rahmen', strpos($parts['body'], 'guarantee-notice__title') === false && strpos($parts['body'], '<div class="guarantee-notice">') === false);
ok('Rumpf traegt Bedienelement und Link', strpos($parts['body'], 'guarantee-notice__open') !== false && strpos($parts['body'], 'guarantee-notice__link') !== false);
ok('gerahmt ergibt denselben Rumpf', strpos(guarantee_labels_notice_wrap($parts), $parts['body']) !== false);
ok('ohne Bestandteile leerer Rahmen', guarantee_labels_notice_wrap(false) === '');
ok('nur digitale Inhalte ohne Bestandteile', guarantee_labels_notice_parts('virtual') === false);

// das Skript sucht den Inhalt ueber die id, nicht ueber einen Wrapper
function ok_target($name, $markup) {
  preg_match('/data-guarantee-label-content="([^"]+)"/', $markup, $c);
  preg_match('/class="guarantee-label__content" id="([^"]+)"/', $markup, $i);
  ok($name, isset($c[1], $i[1]) && $c[1] === $i[1], isset($c[1]) ? $c[1] : 'kein Bedienelement');
}
ok_target('Bedienelement zeigt auf den Hinweisinhalt', guarantee_labels_notice(false));
ok_target('Bedienelement zeigt auf den Labelinhalt', guarantee_labels_markup(guarantee_labels_product_label(p('3.0', 1), $names)));
$_SESSION['language'] = 'german';

echo "\n----------------------------------------\nbestanden: $pass   fehlgeschlagen: $fail\n";
exit($fail > 0 ? 1 : 0);
