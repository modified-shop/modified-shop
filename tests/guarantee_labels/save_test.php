<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Bildet den Speicherlauf der Modulverwaltung nach: Schluessel-Vorbelegung plus die
// vorhandene Schleife, wie sie in admin/module_export.php steht.
$pass = 0; $fail = 0;
function ok($n, $c, $e = '') { global $pass, $fail; if ($c) { $pass++; echo "  ok    $n\n"; } else { $fail++; echo "  FAIL  $n".($e!==''?"  ($e)":'')."\n"; } }
function xtc_db_input($s) { return $s; }
function xtc_not_null($v) { return ($v !== '' && $v !== null && $v !== false); }

class module_stub {
  function keys() { return array('MOD_STATUS', 'MOD_GROUPS'); }
}

function save($post, $with_fix) {
  $saved = array();
  $module = new module_stub();
  if (isset($post['configuration']) && is_array($post['configuration'])) {
    if ($with_fix && method_exists($module, 'keys')) {
      foreach ((array)$module->keys() as $module_key) {
        if (!isset($post['configuration'][$module_key])) {
          $post['configuration'][$module_key] = '';
        }
      }
    }
    foreach ($post['configuration'] as $key => $value) {
      if (is_array($post['configuration'][$key])) {
        $keys = array_keys($post['configuration'][$key]);
        if (gettype(array_shift($keys)) == 'string') {
          $config_value = array();
          foreach ($post['configuration'][$key] as $k => $v) {
            if (xtc_not_null($v)) { $config_value[] = $k.'::'.$v; }
          }
          $value = implode('||', $config_value);
        } else {
          $value = implode(',', $post['configuration'][$key]);
        }
      }
      $saved[$key] = $value;
    }
  }
  return $saved;
}

echo "== Ohne die Korrektur ==\n";
$r = save(array('configuration' => array('MOD_STATUS' => 'true')), false);
ok('abgewaehlte Gruppen werden nicht geschrieben', !array_key_exists('MOD_GROUPS', $r));

echo "\n== Mit der Korrektur ==\n";
$r = save(array('configuration' => array('MOD_STATUS' => 'true')), true);
ok('abgewaehlte Gruppen werden geleert', array_key_exists('MOD_GROUPS', $r) && $r['MOD_GROUPS'] === '');
ok('andere Werte unveraendert', $r['MOD_STATUS'] === 'true');

$r = save(array('configuration' => array('MOD_STATUS' => 'true', 'MOD_GROUPS' => array('3', '5'))), true);
ok('Auswahl wird weiterhin gespeichert', $r['MOD_GROUPS'] === '3,5');

$r = save(array('configuration' => array('MOD_STATUS' => 'true', 'MOD_GROUPS' => array('de' => 'Text', 'en' => ''))), true);
ok('mehrsprachige Konfiguration unveraendert', $r['MOD_GROUPS'] === 'de::Text');

$r = save(array('configuration' => array('MOD_STATUS' => 'true', 'FREMD' => 'x')), true);
ok('fremde Schluessel bleiben erhalten', $r['FREMD'] === 'x');
ok('nur Modulschluessel werden vorbelegt', count($r) === 3);

$r = save(array(), true);
ok('ohne Formulardaten passiert nichts', $r === array());

echo "\n----------------------------------------\nbestanden: $pass   fehlgeschlagen: $fail\n";
exit($fail > 0 ? 1 : 0);
