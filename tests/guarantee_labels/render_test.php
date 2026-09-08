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

// die Metriken stehen erst mit den offiziellen Vorlagen fest, hier fuer den Test gesetzt

echo "== Umgebung ==\n";
$r = new guarantee_labels_renderer();
ok('is_available()', $r->is_available());
ok('is_ready() mit vollstaendigen Assets', $r->is_ready(), implode(', ', $r->missing_requirements()));
ok('Metriken aus den Vorlagen gesetzt', count(array_filter($r->missing_requirements(), function($m) { return strpos($m, 'metrics:') === 0; })) === 0);

echo "\n== normalize_duration ==\n";
$cases = array(
  '2.0' => '2.0', '2' => '2.0', '1.5' => '1.5', '0,5' => '0.5', '1' => '1.0', '0' => false, '2.5' => '2.5', '2,5' => '2.5', '99,5' => '99.5', '12,5' => '12.5',
  '3' => '3.0', '5' => '5.0', '10' => '10.0', '99' => '99.0',
  '4.1' => false, '100' => false, '9.5' => '9.5', '10.5' => '10.5', '100,5' => false,
  '' => false, 'abc' => false, '-3' => false, '3.55' => false,
);
foreach ($cases as $in => $expected) {
  $got = $r->normalize_duration($in);
  ok(sprintf('%-6s -> %s', "'$in'", var_export($expected, true)), $got === $expected, 'got '.var_export($got, true));
}
ok("'2,5' und '2.5' identisch", $r->normalize_duration('2,5') === $r->normalize_duration('2.5'));

echo "\n== qualifies ==\n";
ok('0.5 qualifiziert nicht', $r->qualifies('0.5') === false);
ok('2.0 qualifiziert nicht', $r->qualifies('2.0') === false);
ok("'2' qualifiziert nicht", $r->qualifies('2') === false);
ok('2.5 qualifiziert', $r->qualifies('2.5') === true);
ok('3 qualifiziert', $r->qualifies('3') === true);
ok('ungueltiger Wert qualifiziert nicht', $r->qualifies('abc') === false);
ok('leerer Wert qualifiziert nicht', $r->qualifies('') === false);

echo "\n== duration_text ==\n";
ok("3.0 -> '3'", $r->duration_text('3.0') === '3');
ok("2.5 -> '2,5' mit Komma", $r->duration_text('2.5') === '2,5');
ok("12.5 -> '12,5'", $r->duration_text('12.5') === '12,5');
ok('Label traegt das Komma', strpos($r->render('ACME', 'X-1', '2.5')['colour.svg'], '>2,5</tspan>') !== false);

echo "\n== Textbreite ==\n";
ok('kurzer Herstellername passt', $r->fits('manufacturer', 'ACME'));
ok('sehr langer Herstellername passt nicht', $r->fits('manufacturer', str_repeat('ACME Corporation ', 5)) === false);
ok('unbekannter Bereich', $r->fits('gibtsnicht', 'x') === false);

echo "\n== Hash ==\n";
$h1 = $r->garan_hash('ACME', 'X-1', '3.0');
$h2 = $r->garan_hash('ACME', 'X-1', '3.0');
$h3 = $r->garan_hash('ACME', 'X-2', '3.0');
$h4 = $r->garan_hash('ACMEX', '-1', '3.0');
ok('stabil', $h1 === $h2);
ok('64 Hex-Zeichen', preg_match('/^[0-9a-f]{64}$/', $h1) === 1);
ok('anderes Modell -> anderer Hash', $h1 !== $h3);
ok('verschobene Feldgrenze -> anderer Hash', $h1 !== $h4);

echo "\n== Rendern ==\n";
$files = $r->render('ACME GmbH', 'X-1', '3.0');
ok('beide Varianten', is_array($files) && isset($files['colour.svg'], $files['nested.svg']));
ok('Dauer ersetzt', strpos($files['colour.svg'], '>3<') !== false);
ok('Hersteller ersetzt', strpos($files['colour.svg'], '>ACME GmbH<') !== false);
ok('Modell ersetzt', strpos($files['colour.svg'], '>X-1<') !== false);
ok('kein Token uebrig', strpos($files['colour.svg'], 'Brand/Trademark') === false && strpos($files['colour.svg'], 'Model identifier') === false);
ok('uebriges Markup unveraendert', strpos($files['colour.svg'], '<path d="M10 10 L20 20"/>') !== false);
ok('nested behaelt eigene viewBox', strpos($files['nested.svg'], 'viewBox="0 0 368.5 56.69"') !== false);
ok('mehrteiliges Feld zusammengefuehrt', substr_count($files['colour.svg'], '<tspan') === 3);
ok('Position und Klasse erhalten', strpos($files['colour.svg'], '<text class="cls-5" transform="translate(6.32 74.52)"><tspan x="0" y="0">ACME GmbH</tspan></text>') !== false);
ok('nested traegt nur die Dauer', substr_count($files['nested.svg'], '<text') === 1);

$evil = $r->render('<script>alert(1)</script>', 'A&B "quoted"', '3.0');
ok('XML-Injektion maskiert', strpos($evil['colour.svg'], '<script>') === false && strpos($evil['colour.svg'], '&lt;script&gt;') !== false);
ok('Ampersand maskiert', strpos($evil['colour.svg'], 'A&amp;B') !== false);
$dollar = $r->render('Preis $1 $0 \\$', 'M', '3.0');
ok('Dollarzeichen bleiben erhalten', strpos($dollar['colour.svg'], 'Preis $1 $0 \\$') !== false, substr($dollar['colour.svg'], 0, 0));

echo "\n== Vorlage passt nicht ==\n";
$broken = $root.'/images/guarantee_labels/assets/garan_label_colour.svg';
$orig = file_get_contents($broken);
file_put_contents($broken, str_replace('>XX</tspan>', '>YY</tspan>', $orig));
$r2 = new guarantee_labels_renderer();
ok('fehlendes Token -> false', $r2->render('ACME', 'X-1', '3.0') === false);
ok('Fehler protokolliert', $r2->has_errors());
file_put_contents($broken, $orig);

echo "\n== Ein Wert darf nicht wie ein Platzhalter wirken ==\n";
// Frueher lief je Feld ein eigener Durchlauf ueber das schon geaenderte SVG. Hiess ein
// Hersteller wie der Platzhalter des naechsten Feldes, fand der zwei Treffer und das Label
// verschwand mit einer irrefuehrenden Meldung.
$r3 = new guarantee_labels_renderer();
$token_modell = 'Model identifier';
$mit_token = $r3->render('Model identifier', 'X-1', '3.0');
ok('Hersteller mit dem Namen eines Platzhalters', is_array($mit_token), implode(' | ', $r3->get_errors()));
ok('beide Werte stehen im Label', is_array($mit_token)
   && strpos($mit_token['colour.svg'], '>Model identifier</tspan>') !== false
   && strpos($mit_token['colour.svg'], '>X-1</tspan>') !== false);

echo "\n== Fehlende Vorlagen melden einen Grund ==\n";
class leerer_renderer extends guarantee_labels_renderer {
  function __construct() { parent::__construct(); $this->asset_dir = '/tmp/no_assets_here/'; }
}
$leer = new leerer_renderer();
ok('ohne Vorlagen kein Label', $leer->label('ACME', 'X-1', '3') === false);
ok('Grund protokolliert', $leer->has_errors() === true, implode(' | ', $leer->get_errors()));
ok('Grund nennt die fehlenden Teile', strpos(implode(' ', $leer->get_errors()), 'template:') !== false,
   implode(' | ', $leer->get_errors()));

echo "\n----------------------------------------\n";
echo "bestanden: $pass   fehlgeschlagen: $fail\n";
exit($fail > 0 ? 1 : 0);
