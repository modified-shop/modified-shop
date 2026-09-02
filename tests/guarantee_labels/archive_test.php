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
define('DIR_FS_LOG', $root.'/log/');
define('DIR_FS_EXTERNAL', $root.'/includes/external/');
require DIR_FS_CATALOG.DIR_WS_CLASSES.'guarantee_labels_renderer.php';

$pass = 0; $fail = 0;
function ok($name, $cond, $extra = '') {
  global $pass, $fail;
  if ($cond) { $pass++; echo "  ok    $name\n"; }
  else { $fail++; echo "  FAIL  $name".($extra !== '' ? "  ($extra)" : '')."\n"; }
}
function rrm($d) { if (!is_dir($d)) return; foreach (scandir($d) as $e) { if ($e=='.'||$e=='..') continue; is_dir("$d/$e") ? rrm("$d/$e") : unlink("$d/$e"); } rmdir($d); }
function tempdirs($d) { $n = 0; foreach ((array)@scandir($d) as $e) if (strpos($e, 'tmp_') === 0) $n++; return $n; }

rrm($root.'/cache/guarantee_labels');
rrm($root.'/media/guarantee_labels/archive/garan');
rrm($root.'/media/guarantee_labels/archive/notice');
rrm($root.'/media/products/garan_archive');

// Hashverzeichnisse heissen wie ein sha256, sonst lehnt das Archiv sie ab
$hash_bbb = '3e744b9dc39389baf0c5a0660589b8402f3dbb49b89b3e75f2c9355852a3c677';
$hash_aaa = '9834876dcfb05cb167a5c24953eba58c4ac89b1adf57f28f2f9d09af107ee8f0';
$hash_n1 = '676b8bb84ce7267dd520deca4811c8f10a53e636352f06987f42fe425acedd80';
$hash_h1 = '33112ee14ee469c3eb52fe90322ec81dd404a0093d565a6d71ce77cbc8124e3b';
$hash_h_alt = 'f2afd1c5d4b60eb48f7f097e56c645d073a4101b73f72698f87d4f371fae78d5';
$hash_h_halb = '958f2c635d3932f1ab5983aa5028f6c0c01b7e92a3e918e134e8ea259cee5655';
$hash_h_json = '94417589da72ca8aa1cd1f867eb27bf49dc2552daa112211fd00ddb042d0353b';
$hash_h_luecke = 'aecd245ec825d8dd92c323c418f8c7e6a8aa364ebf035ee8db7549ad81f68631';
$hash_h_pruef = 'eb082cce6562c5c0212c80ee4c7094fed518b02d6ad1c49467bc5796a30ad1e7';
$hash_h_sperr = '86a0805641274a2ba327f4de34ab2f19516da44c16fd3a26e99f42010804b34d';
$a = new guarantee_labels_archive();
$files = array('colour.svg' => '<svg>c</svg>', 'nested.svg' => '<svg>n</svg>');

echo "== Cache ==\n";
ok('leerer Cache liefert false', $a->cache_read($hash_aaa, array_keys($files)) === false);
ok('Basisverzeichnis fehlt und wird angelegt', is_dir($root.'/cache/guarantee_labels') === false);
ok('cache_write', $a->cache_write($hash_aaa, $files));
ok('cache_read liefert identischen Inhalt', $a->cache_read($hash_aaa, array_keys($files)) === $files);
ok('keine temporaeren Reste', tempdirs($root.'/cache/guarantee_labels') === 0);
ok('zweiter Schreibvorgang auf vorhandenes Ziel', $a->cache_write($hash_aaa, $files));
unlink($root.'/cache/guarantee_labels/'.$hash_aaa.'/nested.svg');
ok('unvollstaendiger Cache wird nicht verwendet', $a->cache_read($hash_aaa, array_keys($files)) === false);

rrm($root.'/cache/guarantee_labels');
ok('nach delcache: Verzeichnis weg', is_dir($root.'/cache/guarantee_labels') === false);
ok('Renderer legt es neu an', $a->cache_write($hash_bbb, $files) && is_dir($root.'/cache/guarantee_labels/'.$hash_bbb));

echo "\n== Grafikarchiv ==\n";
ok('garan_write', $a->garan_write($hash_h1, $files));
ok('garan_read', $a->garan_read($hash_h1) === $files);
ok('vorhandenes Archiv wird nicht ueberschrieben',
   $a->garan_write($hash_h1, array('colour.svg' => 'ANDERS', 'nested.svg' => 'ANDERS'))
   && file_get_contents($a->garan_path($hash_h1).'colour.svg') === '<svg>c</svg>');
ok('keine temporaeren Reste', tempdirs($root.'/media/guarantee_labels/archive/garan') === 0);

echo "\n== Hinweisarchiv ==\n";
$notice = array('notice.svg' => '<svg>notice</svg>', 'notice.json' => '{"language":"german"}');
ok('notice_write', $a->notice_write($hash_n1, $notice));
ok('notice_read', $a->notice_read($hash_n1) === $notice);

echo "\n== Pruefsummen ==\n";
$sidecar = $a->garan_path($hash_h1).guarantee_labels_archive::CHECKSUM_FILE;
ok('Sidecar geschrieben', is_file($sidecar));
$sums = json_decode(file_get_contents($sidecar), true);
ok('Sidecar nennt beide Dateien', isset($sums['colour.svg'], $sums['nested.svg']));
ok('Sidecar traegt den Inhaltshash', $sums['colour.svg'] === hash('sha256', $files['colour.svg']));

file_put_contents($a->garan_path($hash_h1).'colour.svg', 'MANIPULIERT');
ok('manipulierte Datei wird abgewiesen', $a->garan_read($hash_h1) === false);

// Archiv ohne Sidecar bleibt lesbar
$a->garan_write($hash_h_alt, $files);
unlink($a->garan_path($hash_h_alt).guarantee_labels_archive::CHECKSUM_FILE);
ok('Archiv ohne Sidecar bleibt lesbar', $a->garan_read($hash_h_alt) === $files);

// kaputter Sidecar ist ein Schaden, kein Archiv ohne Sidecar
$a->garan_write($hash_h_json, $files);
file_put_contents($a->garan_path($hash_h_json).guarantee_labels_archive::CHECKSUM_FILE, '{kaputt');
ok('unlesbarer Sidecar gilt als Schaden', $a->garan_read($hash_h_json) === false);

// ein Eintrag entfernt, die Datei ersetzt: das darf nicht durchgehen
$a->garan_write($hash_h_luecke, $files);
$sums2 = json_decode(file_get_contents($a->garan_path($hash_h_luecke).guarantee_labels_archive::CHECKSUM_FILE), true);
unset($sums2['colour.svg']);
file_put_contents($a->garan_path($hash_h_luecke).guarantee_labels_archive::CHECKSUM_FILE, json_encode($sums2));
file_put_contents($a->garan_path($hash_h_luecke).'colour.svg', 'ERSETZT');
ok('fehlender Sidecar-Eintrag gilt als Schaden', $a->garan_read($hash_h_luecke) === false);

// beschaedigtes Verzeichnis wird beim naechsten Schreiben ersetzt
ok('Schreiben ersetzt das beschaedigte Verzeichnis', $a->garan_write($hash_h_luecke, $files) === true);
ok('danach wieder lesbar', $a->garan_read($hash_h_luecke) === $files);

// remove_dir() muss melden, ob es geklappt hat
ok('remove_dir meldet Erfolg', $a->remove_dir($a->garan_path($hash_h_luecke)) === true);
ok('nicht vorhandenes Verzeichnis gilt als entfernt', $a->remove_dir($a->garan_path('gibtsnicht')) === true);
$a->garan_write($hash_h_sperr, $files);
@mkdir($a->garan_path($hash_h_sperr).'unterordner', 0777, true);
ok('Unterverzeichnis wird nicht angetastet', $a->remove_dir($a->garan_path($hash_h_sperr)) === false);
@rmdir($a->garan_path($hash_h_sperr).'unterordner');

// nach dem Schreiben wird das ganze Verzeichnis so gelesen, wie es spaeter gelesen wird
$a->garan_write($hash_h_pruef, $files);
ok('Sidecar ist Teil der Schreibpruefung', $a->garan_read($hash_h_pruef) === $files);
// ein Verzeichnis, dessen Sidecar beim Schreiben zerstoert wird, darf nicht umbenannt werden
class halbschreiber extends guarantee_labels_archive {
  function read_files($dir, $names) {
    // nur das temporaere Verzeichnis scheitern lassen, nicht das fertige
    if (strpos($dir, '/tmp_') !== false) { return false; }
    return parent::read_files($dir, $names);
  }
}
$h = new halbschreiber();
ok('unvollstaendiges Verzeichnis wird nicht uebernommen', $h->garan_write($hash_h_halb, $files) === false);
ok('kein Zielverzeichnis zurueckgeblieben', !is_dir($h->garan_path($hash_h_halb)));
ok('Fehler protokolliert', $h->has_errors() === true, implode(' | ', $h->get_errors()));
@rmdir($a->garan_path($hash_h_sperr).'unterordner');

echo "\n== Garantiebedingungen ==\n";
$src = $root.'/terms_source.pdf';
file_put_contents($src, 'PDF-INHALT');
$hash = hash('sha256', 'PDF-INHALT');
ok('terms_write', $a->terms_write($hash, 'Garantie.pdf', $src));
ok('Datei traegt ihren Namen', is_file($a->terms_path($hash, 'Garantie.pdf')));
ok('Inhalt unveraendert', file_get_contents($a->terms_path($hash, 'Garantie.pdf')) === 'PDF-INHALT');
ok('erneuter Aufruf ist idempotent', $a->terms_write($hash, 'Garantie.pdf', $src));
ok('gleicher Inhalt, anderer Name', $a->terms_write($hash, 'Warranty.pdf', $src) && is_file($a->terms_path($hash, 'Warranty.pdf')));
ok('beide Dateien im selben Hashverzeichnis', count(glob($root.'/media/products/garan_archive/'.$hash.'/*.pdf')) === 2);
ok('fehlende Quelldatei', $a->terms_write($hash, 'X.pdf', $root.'/gibtsnicht.pdf') === false);
ok('falscher Hash wird abgelehnt', $a->terms_write(hash('sha256', 'ANDERS'), 'Y.pdf', $src) === false);
ok('keine temporaeren Reste', tempdirs($root.'/media/products/garan_archive/'.$hash) === 0);

echo "\n== Gescheitertes Umbenennen meldet sich ==\n";
// Ein fehlgeschlagenes rename() darf nicht stumm false liefern: ohne Eintrag in der Fehlerliste
// greifen weder Log noch messageStack. Eine Datei am Zielpfad laesst rename() scheitern.
$hash_stolper = hash('sha256', 'STOLPER');
$ziel = $root.'/media/guarantee_labels/archive/garan/'.$hash_stolper;
@mkdir(dirname($ziel), 0777, true);
file_put_contents($ziel, 'BLOCKIERT');
$st = new guarantee_labels_archive();
ok('blockiertes Zielverzeichnis wird abgelehnt', $st->garan_write($hash_stolper, $files) === false);
ok('und dabei protokolliert', $st->has_errors() === true, implode(' | ', $st->get_errors()));
ok('kein temporaeres Verzeichnis zurueckgeblieben', tempdirs($root.'/media/guarantee_labels/archive/garan') === 0);
@unlink($ziel);

// dasselbe fuer eine Anhangsdatei: ein Verzeichnis am Zielpfad
$hash_stolper_t = hash('sha256', 'PDF-INHALT');
$blockiert = $root.'/media/products/garan_archive/'.$hash_stolper_t.'/Blockiert.pdf';
@mkdir($blockiert, 0777, true);
$st2 = new guarantee_labels_archive();
ok('blockierter Dateiname wird abgelehnt', $st2->terms_write($hash_stolper_t, 'Blockiert.pdf', $src) === false);
ok('und dabei protokolliert', $st2->has_errors() === true, implode(' | ', $st2->get_errors()));
@rmdir($blockiert);

echo "\n== label() Ende zu Ende ==\n";
class test_renderer extends guarantee_labels_renderer {
  function areas() {
    return array(
      // dieselben Schnitte wie die Vorlage, SemiBold gibt es nicht
      'duration' => array('token' => self::TOKEN_DURATION, 'font' => 'Inter-ExtraBold.ttf', 'font_size' => 80, 'max_width' => 190.43),
      'manufacturer' => array('token' => self::TOKEN_MANUFACTURER, 'font' => 'Inter-Regular.ttf', 'font_size' => 9, 'max_width' => 190.43),
      'model' => array('token' => self::TOKEN_MODEL, 'font' => 'Inter-Regular.ttf', 'font_size' => 9, 'max_width' => 66.22),
    );
  }
}
$r = new test_renderer();
$label = $r->label('ACME GmbH', 'X-1', '3');
ok('label liefert Hash und beide Varianten', is_array($label) && isset($label['hash'], $label['colour.svg'], $label['nested.svg']));
ok('Cacheverzeichnis angelegt', is_dir($root.'/cache/guarantee_labels/'.$label['hash']));
$label2 = $r->label('ACME GmbH', 'X-1', '3,0');
ok('Komma-Eingabe ergibt denselben Hash', $label2['hash'] === $label['hash']);
ok('zweiter Aufruf liefert identischen Inhalt', $label2['colour.svg'] === $label['colour.svg']);
ok('nicht qualifizierende Dauer erzeugt kein Label', $r->label('ACME', 'X-1', '2.0') === false);
ok('ungueltige Dauer', $r->label('ACME', 'X-1', '1.5') === false);
ok('leerer Hersteller', $r->label('', 'X-1', '3') === false);
class assetless_renderer extends guarantee_labels_renderer {
  function __construct() { parent::__construct(); $this->asset_dir = '/tmp/no_assets_here/'; }
}
$bare = new assetless_renderer();
ok('ohne Vorlagen nicht bereit', $bare->is_ready() === false);
ok('ohne Vorlagen kein Label', $bare->label('ACME', 'X-1', '3') === false);
ok('keine Fehler protokolliert', $r->has_errors() === false, implode(' | ', $r->get_errors()));

echo "\n== Breite ist auch im Renderer verbindlich ==\n";
$w = new test_renderer();
$long = str_repeat('Sehr langer Herstellername ', 4);
ok('zu langer Herstellername erzeugt kein Label', $w->label($long, 'X-1', '3') === false);
ok('Grund protokolliert', strpos(implode(' | ', $w->get_errors()), 'manufacturer') !== false, implode(' | ', $w->get_errors()));
$w2 = new test_renderer();
ok('zu lange Modellkennung erzeugt kein Label', $w2->label('ACME GmbH', str_repeat('X', 60), '3') === false);
ok('Grund protokolliert', strpos(implode(' | ', $w2->get_errors()), 'model') !== false, implode(' | ', $w2->get_errors()));
$w3 = new test_renderer();
ok('passende Werte erzeugen weiterhin ein Label', is_array($w3->label('ACME GmbH', 'X-1', '3')));
class fontless_renderer extends test_renderer {
  function __construct() { parent::__construct(); $this->font_dir = '/tmp/no_fonts_here/'; }
}
$w4 = new fontless_renderer();
ok('ohne Schrift kein Label', $w4->label('ACME GmbH', 'X-1', '3') === false);

echo "\n== Nur echte Hashes werden zu Pfaden ==\n";
$gut = str_repeat('a', 64);
ok('gueltiger Hash ergibt einen Pfad', $a->garan_path($gut) !== '' && strpos($a->garan_path($gut), $gut) !== false);
foreach (array('..', '../../etc', 'GROSS'.str_repeat('a', 59), str_repeat('a', 63), str_repeat('a', 65), '', 'a/b') as $boese) {
  ok("'".substr($boese, 0, 12)."' wird abgelehnt", $a->garan_path($boese) === '' && $a->notice_path($boese) === '');
}
ok('Anhangspfad lehnt Pfadanteile im Namen ab', $a->terms_path($gut, '../x.pdf') === '');
ok('Anhangspfad lehnt Komma ab', $a->terms_path($gut, 'a,b.pdf') === '');
ok('Anhangspfad mit gutem Namen entsteht', $a->terms_path($gut, 'garantie.pdf') !== '');
ok('leeres Verzeichnis wird nicht gelesen', $a->garan_read('..') === false);

// jeder Weg, nicht nur die Pfadbildner: auch Lesen und Schreiben pruefen den Hash
ok('cache_read lehnt ab', $a->cache_read('../x', array('colour.svg')) === false);
ok('notice_read lehnt ab', $a->notice_read('../x') === false);
ok('garan_write lehnt ab', $a->garan_write('../x', $files) === false);
ok('notice_write lehnt ab', $a->notice_write('..', $notice) === false);
ok('terms_write lehnt ab', $a->terms_write('..', 'garantie.pdf', $src) === false);
ok('terms_write lehnt schlechten Namen ab', $a->terms_write($gut, '../x.pdf', $src) === false);
ok('kein Verzeichnis ausserhalb angelegt', !is_dir($root.'/media/guarantee_labels/archive/x')
   && !is_dir($root.'/media/products/x'));
// jeder einzelne Fehlgriff muss einen Eintrag erzeugen, nicht erst der letzte
foreach (array('garan_read', 'notice_read', 'cache_read') as $methode) {
  $frisch = new guarantee_labels_archive();
  $frisch->$methode('../x', array('colour.svg'));
  ok($methode.' protokolliert den ungueltigen Hash', $frisch->has_errors() === true,
     implode(' | ', $frisch->get_errors()));
}
$frisch = new guarantee_labels_archive();
$frisch->terms_path('../x', 'a.pdf');
ok('terms_path protokolliert den Hash', $frisch->has_errors() === true);

// gueltiger Hash, unbrauchbarer Name: der zweite Grund muss ebenso auffallen
foreach (array('../x.pdf', 'a,b.pdf', "a\nb.pdf", 'sub/x.pdf', 'ohneendung', '   ', '.', '..', '') as $name) {
  $frisch = new guarantee_labels_archive();
  ok("terms_path lehnt '".str_replace("\n", '\\n', $name)."' ab und meldet es",
     $frisch->terms_path($gut, $name) === '' && $frisch->has_errors() === true,
     implode(' | ', $frisch->get_errors()));
}
$frisch = new guarantee_labels_archive();
ok('gueltiger Name meldet nichts', $frisch->terms_path($gut, 'garantie.pdf') !== '' && $frisch->has_errors() === false);

// Lesen und Schreiben muessen sich einig sein, sonst entsteht ein Pfad zu einem Namen,
// den das Archiv nie angenommen haette
require_once $root.'/inc/guarantee_labels_snapshot.inc.php';
foreach (array('garantie.pdf', 'garantie_äöü.pdf', '../x.pdf', 'a,b.pdf', 'ohneendung', '   ', '.', '..', '') as $name) {
  $schreibt = (guarantee_labels_terms_filename($name) !== false);
  $liest = ($a->usable_filename($name) === true);
  ok("'".str_replace(array("\n"), '\\n', $name)."': Schreib- und Leseregel gleich", $schreibt === $liest,
     'schreiben='.var_export($schreibt, true).' lesen='.var_export($liest, true));
}
$frisch = new guarantee_labels_archive();
ok('terms_write meldet den unbrauchbaren Namen', $frisch->terms_write($gut, '../x.pdf', $src) === false && $frisch->has_errors() === true);
ok('Fehler dazu protokolliert', $a->has_errors() === true);

echo "\n----------------------------------------\n";
echo "bestanden: $pass   fehlgeschlagen: $fail\n";
exit($fail > 0 ? 1 : 0);
