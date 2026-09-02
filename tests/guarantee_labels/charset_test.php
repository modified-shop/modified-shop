<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Ein Shop laeuft auf utf8 oder auf latin1. Beide muessen dasselbe Label erzeugen.
error_reporting(E_ALL & ~E_DEPRECATED);
$guarantee_labels_paths = require __DIR__.'/bootstrap.php';
$root = $guarantee_labels_paths['shop'];
$repo = $guarantee_labels_paths['repo'];
$mode = isset($argv[1]) ? $argv[1] : 'driver';

if ($mode === 'driver') {
  $pass = 0; $fail = 0; $hashes = array();
  foreach (array('utf8', 'latin1') as $case) {
    $out = array();
    exec(escapeshellcmd(PHP_BINARY).' '.escapeshellarg(__FILE__).' '.escapeshellarg($case).' 2>&1', $out);
    foreach ($out as $line) {
      echo $line."\n";
      if (strpos($line, '  ok    ') === 0) $pass++;
      if (strpos($line, '  FAIL  ') === 0) $fail++;
      if (strpos($line, '  HASH  ') === 0) $hashes[] = substr($line, 8);
    }
  }
  echo "\n== Beide Shops erzeugen dasselbe Label ==\n";
  $paare = count($hashes) / 2;
  for ($i = 0; $i < $paare; $i++) {
    $gleich = ($hashes[$i] === $hashes[$paare + $i]);
    echo ($gleich ? "  ok    " : "  FAIL  ")."Hash $i stimmt ueberein\n";
    $gleich ? $pass++ : $fail++;
  }
  echo "\n----------------------------------------\n";
  echo "bestanden: $pass   fehlgeschlagen: $fail\n";
  exit($fail > 0 ? 1 : 0);
}

define('DB_SERVER_CHARSET', $mode);
define('DIR_FS_CATALOG', $root.'/');
define('DIR_WS_INCLUDES', 'includes/');
define('DIR_WS_CLASSES', 'includes/classes/');
define('DIR_FS_LOG', $root.'/log/');
define('DIR_FS_EXTERNAL', $root.'/includes/external/');
require DIR_FS_CATALOG.'includes/classes/guarantee_labels_renderer.php';

$pass = 0; $fail = 0;
function ok($n, $c, $e = '') { global $pass, $fail; if ($c) { $pass++; echo "  ok    $n\n"; } else { $fail++; echo "  FAIL  $n".($e!==''?"  ($e)":'')."\n"; } }

class test_renderer extends guarantee_labels_renderer {
  function areas() {
    return array(
      'duration' => array('token' => self::TOKEN_DURATION, 'font' => 'Inter-ExtraBold.ttf', 'font_size' => 80, 'max_width' => 190.43),
      'manufacturer' => array('token' => self::TOKEN_MANUFACTURER, 'font' => 'Inter-Regular.ttf', 'font_size' => 9, 'max_width' => 190.43),
      'model' => array('token' => self::TOKEN_MODEL, 'font' => 'Inter-Regular.ttf', 'font_size' => 9, 'max_width' => 66.22),
    );
  }
}
$r = new test_renderer();

// Werte so, wie der Katalog dieses Shops sie liefert
function shopwert($utf8) {
  return (DB_SERVER_CHARSET === 'latin1') ? mb_convert_encoding($utf8, 'ISO-8859-15', 'UTF-8') : $utf8;
}

echo "\n== $mode ==\n";
foreach (array('Müller', 'Straße', 'ACME & Söhne', '100 € Marke') as $utf8) {
  $label = $r->label(shopwert($utf8), 'X-1', '3');
  ok("'$utf8' erzeugt ein Label", $label !== false);
  ok("'$utf8' steht unverfaelscht in der Grafik",
     $label !== false && strpos($label['colour.svg'], htmlspecialchars($utf8, ENT_QUOTES | ENT_XML1, 'UTF-8')) !== false);
  echo "  HASH  ".($label === false ? 'false' : $label['hash'])."\n";
}
$breit = $r->fits('manufacturer', shopwert('Müller'));
ok('Breitenpruefung akzeptiert den Shopwert', $breit === true);
ok('Breitenpruefung lehnt zu langen Shopwert ab', $r->fits('model', shopwert(str_repeat('Ä', 60))) === false);

// Die Adminpruefung darf nichts zulassen, woraus spaeter kein Label wird: fits() und label()
// muessen denselben Wert messen, auch wenn label() ihn vorher schon umgewandelt hat.
foreach (array(2, 6, 10, 14) as $anzahl) {
  $wert = shopwert(str_repeat('Ä', $anzahl));
  $passt = $r->fits('model', $wert);
  $label = $r->label('ACME GmbH', $wert, '3');
  ok($anzahl.' Umlaute: Pruefung und Erzeugung stimmen ueberein', $passt === ($label !== false),
     'fits='.var_export($passt, true).' label='.var_export($label !== false, true));
}
// Einmal an der Grenze umwandeln, nie zweimal: manche ISO-8859-15-Folgen sind auch gueltiges
// UTF-8, eine Erkennung anhand der Byte-Gueltigkeit koennte sie nicht unterscheiden.
ok('measurable() liefert denselben Wert wie label() misst',
   $r->fits('model', $r->measurable(shopwert('ÄÄÄÄÄÄ'))) === ($r->label('ACME GmbH', shopwert('ÄÄÄÄÄÄ'), '3') !== false));

$doppeldeutig = "\xc3\xa4";   // ISO-8859-15 "Ã¤", zugleich gueltiges UTF-8 "ä"
$erwartet = (DB_SERVER_CHARSET === 'latin1') ? mb_convert_encoding($doppeldeutig, 'UTF-8', 'ISO-8859-15') : $doppeldeutig;
ok('mehrdeutige Bytefolge folgt dem Shopzeichensatz', $r->measurable($doppeldeutig) === $erwartet,
   bin2hex($r->measurable($doppeldeutig)).' statt '.bin2hex($erwartet));

echo "\n----------------------------------------\nbestanden: $pass   fehlgeschlagen: $fail\n";
exit($fail > 0 ? 1 : 0);
