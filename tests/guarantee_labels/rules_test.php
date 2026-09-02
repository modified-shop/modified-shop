<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Wachtposten fuer die Regeln, die sich mechanisch pruefen lassen. Jede dieser Regeln wurde
// mindestens einmal an einer zweiten Stelle vergessen; der Test findet die naechste selbst.
error_reporting(E_ALL & ~E_DEPRECATED);
$guarantee_labels_paths = require __DIR__.'/bootstrap.php';
$repo = $guarantee_labels_paths['repo'];

$pass = 0; $fail = 0;
function ok($n, $c, $e = '') { global $pass, $fail; if ($c) { $pass++; echo "  ok    $n\n"; } else { $fail++; echo "  FAIL  $n".($e!==''?"\n        ".$e:'')."\n"; } }

// alle Moduldateien, ohne Sprachdateien und ohne die Tests selbst
function garan_files($repo) {
  $files = array();
  $roots = array($repo.'/inc', $repo.'/includes', $repo.'/admin');

  foreach ($roots as $root) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));

    foreach ($it as $file) {
      $path = $file->getPathname();

      if (substr($path, -4) === '.php' && strpos(basename($path), 'guarantee_labels') !== false) {
        $files[] = $path;
      }
    }
  }

  sort($files);
  return $files;
}

function kurz($repo, $path) { return ltrim(str_replace($repo, '', $path), '/'); }

$files = garan_files($repo);

echo "== Umfang ==\n";
ok('Moduldateien gefunden', count($files) > 20, count($files).' Dateien');

echo "\n== Der Shop hat eigene Hilfsfunktionen fuer HTML ==\n";
// inc/html_encoding.php kennt die Shopcodierung, die PHP-Funktionen nicht
$treffer = array();
foreach ($files as $path) {
  $inhalt = file_get_contents($path);
  $inhalt = str_replace(array('encode_htmlspecialchars', 'decode_htmlentities', 'encode_utf8', 'decode_utf8'), '', $inhalt);

  if (preg_match('/\b(htmlspecialchars|htmlentities|html_entity_decode|utf8_encode|utf8_decode)\s*\(/', $inhalt, $m)) {
    $treffer[] = kurz($repo, $path).': '.$m[1].'()';
  }
}
ok('keine rohen HTML-Funktionen', count($treffer) === 0, implode("\n        ", $treffer));

echo "\n== Ein Archivschreibvorgang wird immer geprueft ==\n";
// ein ignoriertes false hiesse: eine Datenbankzeile zeigt auf ein Archiv, das es nicht gibt
$treffer = array();
foreach ($files as $path) {
  if (basename($path) === 'guarantee_labels_archive.php') {
    continue;
  }

  foreach (file($path) as $nr => $zeile) {
    if (!preg_match('/->(garan_write|notice_write|terms_write|cache_write)\s*\(/', $zeile, $m)) {
      continue;
    }

    // geprueft heisst: einem Ergebnis zugewiesen oder direkt in einer Bedingung verwendet
    if (preg_match('/(\$[a-z_]+\s*=|if\s*\(|&&|\|\||return)/i', $zeile)) {
      continue;
    }

    // eine einzige bewusste Ausnahme, sie ist im Renderer benannt
    if (basename($path) === 'guarantee_labels_renderer.php' && $m[1] === 'cache_write') {
      continue;
    }

    $treffer[] = kurz($repo, $path).':'.($nr + 1).': '.trim($zeile);
  }
}
ok('kein ungeprueftes Schreiben', count($treffer) === 0, implode("\n        ", $treffer));

echo "\n== Historische Ausgabe fragt nicht die heutige Kundengruppe ==\n";
// guarantee_labels_active() liest die Sitzung, guarantee_labels_order_active() nur den Modulstatus
$historisch = array(
  'inc/guarantee_labels_order.inc.php',
  'includes/extra/send_order/data/guarantee_labels.php',
  'includes/extra/send_order/mail/guarantee_labels.php',
  'includes/extra/header/header_head/guarantee_labels.php',
);
$treffer = array();
foreach ($historisch as $rel) {
  $path = $repo.'/'.$rel;

  if (!is_file($path)) {
    $treffer[] = $rel.': Datei fehlt';
    continue;
  }

  foreach (file($path) as $nr => $zeile) {
    if (preg_match('/(?<!order_)\bguarantee_labels_active\s*\(/', $zeile)) {
      $treffer[] = $rel.':'.($nr + 1).': '.trim($zeile);
    }
  }
}
ok('nur der Modulstatus entscheidet', count($treffer) === 0, implode("\n        ", $treffer));

echo "\n== Ein Erweiterungshaken prueft den Modulstatus selbst ==\n";
// Die Dateien liegen auch dann im Shop, wenn das Modul nie installiert wurde. Ein Haken darf
// deshalb keine Moduldatei laden, bevor er die Konstante geprueft hat: sonst laedt jeder
// Seitenaufruf Modulcode, den niemand braucht.
//
// Zwei Haken raeumen bewusst auch bei deaktiviertem Modul auf. Die Modultabellen ueberleben
// eine Deinstallation, verwaiste Zeilen duerfen nicht liegen bleiben.
$aufraeumer = array(
  'admin/includes/extra/modules/orders/orders_functions/product_delete/guarantee_labels.php',
  'admin/includes/extra/modules/orders/orders_edit_products/button/guarantee_labels.php',
);
$treffer = array();
foreach ($files as $path) {
  if (strpos($path, '/extra/') === false || in_array(kurz($repo, $path), $aufraeumer, true)) {
    continue;
  }

  $inhalt = file_get_contents($path);
  $status = strpos($inhalt, 'MODULE_GUARANTEE_LABELS_STATUS');
  $laden = strpos($inhalt, "require_once(DIR_FS_INC.'guarantee_labels");

  if ($laden !== false && ($status === false || $status > $laden)) {
    $treffer[] = kurz($repo, $path);
  }
}
ok('Statuspruefung steht vor dem Laden', count($treffer) === 0, implode("\n        ", $treffer));

// die Ausnahmen muessen Ausnahmen bleiben: sie duerfen nur aufraeumen, nichts ausgeben
$treffer = array();
foreach ($aufraeumer as $rel) {
  $path = $repo.'/'.$rel;

  if (!is_file($path)) {
    $treffer[] = $rel.': Datei fehlt';
    continue;
  }

  if (preg_match('/guarantee_labels_(product_label|markup|order_text|order_label|notice_parts)\s*\(/', file_get_contents($path))) {
    $treffer[] = $rel.': gibt aus, statt nur aufzuraeumen';
  }
}
ok('die Ausnahmen raeumen nur auf', count($treffer) === 0, implode("\n        ", $treffer));

echo "\n== Ein gesammelter Fehler wird auch ausgegeben ==\n";
// guarantee_labels_snapshot_failures() ohne Argument leert den Speicher. Wer leert, muss melden.
$treffer = array();
foreach ($files as $path) {
  if (strpos($path, '/admin/') === false && strpos($path, '/send_order/') === false) {
    continue;
  }

  $inhalt = file_get_contents($path);

  if (strpos($inhalt, 'guarantee_labels_snapshot_failures()') !== false
      && strpos($inhalt, 'messageStack') === false
      )
  {
    $treffer[] = kurz($repo, $path);
  }
}
ok('geleert heisst gemeldet', count($treffer) === 0, implode("\n        ", $treffer));

echo "\n----------------------------------------\n";
echo "bestanden: $pass   fehlgeschlagen: $fail\n";
exit($fail > 0 ? 1 : 0);
