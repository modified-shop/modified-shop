<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Der Haken des Content Managers. Er schreibt die Markierung eines Garantiedokuments, nachdem
// der Core die Zeile gespeichert hat; der Core kennt das Modul nicht mehr.
error_reporting(E_ALL & ~E_DEPRECATED);
$guarantee_labels_paths = require __DIR__.'/bootstrap.php';
$R = $guarantee_labels_paths['repo'].'/';

// Attrappe statt der echten Pruefung: die steht in terms_check_test.php, hier zaehlt nur, was
// der Haken mit ihrem Ergebnis anstellt
$inc = $guarantee_labels_paths['work'].'/cm_inc/';
if (!is_dir($inc)) mkdir($inc, 0777, true);
file_put_contents($inc.'guarantee_labels_terms.inc.php', '<?php
  function guarantee_labels_check_terms($products_id, $languages_id, $content_file, $content_link, $content_id = 0, $group_ids = null) {
    $GLOBALS["args"] = func_get_args();
    return $GLOBALS["errors"];
  }');

define('_VALID_XTC', true);
define('DIR_FS_INC', $inc);
define('DIR_FS_ADMIN', $R.'admin/');
define('TABLE_PRODUCTS_CONTENT', 'products_content');
define('MODULE_GUARANTEE_LABELS_STATUS', 'true');

function xtc_db_input($s) { return str_replace("'", "\\'", (string)$s); }
function xtc_db_query($sql) { $GLOBALS['sql'][] = $sql; return array(); }
class stack { public $msgs = array(); function add_session($t, $c = 'info') { $this->msgs[] = $t; } }
$messageStack = new stack();

$pass = 0; $fail = 0;
function ok($n, $c, $e = '') { global $pass, $fail; if ($c) { $pass++; echo "  ok    $n\n"; } else { $fail++; echo "  FAIL  $n".($e!==''?"  ($e)":'')."\n"; } }

// ein Speichervorgang des Content Managers, so wie der Core ihn hinterlaesst
function speichern($subaction, $post, $errors = array(), $type = 'products') {
  global $messageStack;

  $GLOBALS['sql'] = array();
  $GLOBALS['args'] = array();
  $GLOBALS['errors'] = $errors;
  $messageStack->msgs = array();

  $_POST = array_merge(array('coID' => 55), $post);
  $_GET = array('coID' => 77);

  $coID = 55;
  $content_file_name = 'garantie.pdf';
  $content_link = '';
  $content_language_id = 2;
  $product = 42;
  $group_ids = 'c_1_group,c_2_group,';

  require DIR_FS_ADMIN.'includes/extra/modules/content_manager/action/guarantee_labels.php';
}

echo "== Markierung wird nach dem Speichern geschrieben ==\n";
speichern('insert', array('content_type' => 'garan_terms'));
ok('genau eine Abfrage', count($GLOBALS['sql']) === 1, count($GLOBALS['sql']).' Abfragen');
ok('Markierung gesetzt', strpos($GLOBALS['sql'][0], "content_type = 'garan_terms'") !== false, $GLOBALS['sql'][0]);
ok('neue Zeile per coID aus dem insert', strpos($GLOBALS['sql'][0], "content_id = '77'") !== false, $GLOBALS['sql'][0]);
ok('Pruefung kennt die eigene Zeile', isset($GLOBALS['args'][4]) && $GLOBALS['args'][4] === 77, var_export($GLOBALS['args'], true));

speichern('update', array('content_type' => 'garan_terms'));
ok('Aenderung per coID aus dem Formular', strpos($GLOBALS['sql'][0], "content_id = '55'") !== false, $GLOBALS['sql'][0]);

echo "\n== Abgelehnte Bedingungen bleiben ein normaler Anhang ==\n";
speichern('update', array('content_type' => 'garan_terms'), array('Datei fehlt', 'Gruppe fehlt'));
ok('Markierung geloescht', strpos($GLOBALS['sql'][0], "content_type = ''") !== false, $GLOBALS['sql'][0]);
ok('beide Meldungen gestellt', $messageStack->msgs === array('Datei fehlt', 'Gruppe fehlt'), var_export($messageStack->msgs, true));

echo "\n== Auswahl zurueckgenommen ==\n";
speichern('update', array('content_type' => ''));
ok('Markierung geloescht', count($GLOBALS['sql']) === 1 && strpos($GLOBALS['sql'][0], "content_type = ''") !== false, var_export($GLOBALS['sql'], true));
ok('ohne Auswahl keine Pruefung', $GLOBALS['args'] === array());

echo "\n== Ohne das Feld ruehrt der Haken nichts an ==\n";
// das Auswahlfeld erscheint nur bei laufendem Modul, eine gespeicherte Markierung ueberlebt es
speichern('update', array());
ok('keine Abfrage', count($GLOBALS['sql']) === 0, var_export($GLOBALS['sql'], true));

speichern('update', array('content_type' => 'garan_terms'), array(), 'content_manager');
ok('fremder Inhaltstyp bleibt unberuehrt', count($GLOBALS['sql']) === 0, var_export($GLOBALS['sql'], true));

echo "\n----------------------------------------\n";
echo "bestanden: $pass   fehlgeschlagen: $fail\n";
exit($fail > 0 ? 1 : 0);
