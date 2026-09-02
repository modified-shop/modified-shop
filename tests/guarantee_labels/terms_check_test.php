<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Prueft die Zuordnung eines Anhangs als GARAN-Garantiebedingung.
error_reporting(E_ALL & ~E_DEPRECATED);
$guarantee_labels_paths = require __DIR__.'/bootstrap.php';
$root = $guarantee_labels_paths['shop'];
$repo = $guarantee_labels_paths['repo'];
$repo = $guarantee_labels_paths['repo'];

define('DIR_FS_CATALOG', $root.'/');
define('DIR_FS_INC', $root.'/inc/');
define('DIR_WS_INCLUDES', 'includes/');
define('DIR_WS_CLASSES', 'includes/classes/');
define('DIR_FS_LOG', $root.'/log/');
define('DIR_FS_EXTERNAL', $root.'/includes/external/');
define('TABLE_PRODUCTS_CONTENT', 'products_content');
define('TABLE_CUSTOMERS_STATUS', 'customers_status');
define('GROUP_CHECK', 'true');
define('TABLE_MANUFACTURERS', 'manufacturers');
define('MODULE_GUARANTEE_LABELS_STATUS', 'true');
// Gruppe 4 ist B2B und sieht das Label nicht
define('MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS', '4');

$_SESSION = array('languages_id' => 2);

require $repo.'/lang/german/extra/admin/guarantee_labels.php';
require $repo.'/inc/html_encoding.php';

$GLOBALS['duplicate'] = false;
function xtc_db_query($sql) {
  if (strpos($sql, 'customers_status') !== false) {
    if (isset($GLOBALS['gruppenabfragen'])) $GLOBALS['gruppenabfragen']++;
    return array(
      array('customers_status_id' => '1', 'customers_status_name' => 'Endkunde'),
      array('customers_status_id' => '2', 'customers_status_name' => 'Stammkunde'),
      array('customers_status_id' => '4', 'customers_status_name' => 'Haendler'),
    );
  }
  return $GLOBALS['duplicate'] ? array(array('content_id' => 9)) : array();
}
function xtc_db_fetch_array(&$r) { return array_shift($r); }
function xtc_db_num_rows($r) { return count($r); }

require DIR_FS_INC.'guarantee_labels_terms.inc.php';

$pass = 0; $fail = 0;
function ok($n, $c, $e = '') { global $pass, $fail; if ($c) { $pass++; echo "  ok    $n\n"; } else { $fail++; echo "  FAIL  $n".($e!==''?"  ($e)":'')."\n"; } }

// eine echte Datei unter media/products/
@mkdir(DIR_FS_CATALOG.'media/products/', 0777, true);
file_put_contents(DIR_FS_CATALOG.'media/products/bedingungen.pdf', 'PDF');

function check($file, $link = '', $groups = null, $id = 0) {
  return guarantee_labels_check_terms(1, 2, $file, $link, $id, $groups);
}

echo "\n== Datei ==\n";
ok('gueltige Datei ohne Befund', check('bedingungen.pdf') === array());
ok('reiner Link abgelehnt', count(check('', 'https://example.org/x.pdf')) === 1);
// products_media.php zeigt bei gesetztem Link die Datei nicht, die Mail versendet sie trotzdem
ok('Datei und Link zusammen abgelehnt', check('bedingungen.pdf', 'https://example.org/x.pdf') === array(ERROR_GUARANTEE_LABELS_TERMS_BOTH));
ok('Leerzeichen als Link zaehlt nicht', check('bedingungen.pdf', '   ') === array());
ok('fehlende Datei abgelehnt', count(check('gibtsnicht.pdf')) === 1);
ok('Pfadbestandteil abgelehnt', count(check('../secret.pdf')) === 1);
ok('Komma im Namen abgelehnt', count(check('a,b.pdf')) === 1);

echo "\n== Eindeutigkeit ==\n";
$GLOBALS['duplicate'] = true;
ok('zweiter Anhang derselben Sprache abgelehnt', count(check('bedingungen.pdf')) === 1);
$GLOBALS['duplicate'] = false;

echo "\n== Kundengruppen ==\n";
ok('leere Auswahl meldet alle B2C-Gruppen', count(check('bedingungen.pdf', '', '')) === 1);
ok('alle B2C-Gruppen ausgewaehlt', check('bedingungen.pdf', '', 'c_1_group,c_2_group,') === array());
ok('B2B-Gruppe darf fehlen', check('bedingungen.pdf', '', 'c_1_group,c_2_group,') === array());
$missing = check('bedingungen.pdf', '', 'c_1_group,');
ok('fehlende B2C-Gruppe faellt auf', count($missing) === 1, implode(' | ', $missing));
ok('die fehlende Gruppe wird benannt', count($missing) === 1 && strpos($missing[0], 'Stammkunde') !== false, implode(' | ', $missing));
ok('nur B2B ausgewaehlt meldet beide B2C-Gruppen', count($m2 = check('bedingungen.pdf', '', 'c_4_group,')) === 1
   && strpos($m2[0], 'Endkunde') !== false && strpos($m2[0], 'Stammkunde') !== false, implode(' | ', $m2));

echo "\n== Kundengruppen werden einmal geladen ==\n";
$GLOBALS['gruppenabfragen'] = 0;
for ($i = 0; $i < 20; $i++) {
  guarantee_labels_terms_missing_groups('c_1_group,');
}
ok('nur eine Abfrage trotz 20 Aufrufen', $GLOBALS['gruppenabfragen'] <= 1, $GLOBALS['gruppenabfragen'].' Abfragen');

echo "\n----------------------------------------\n";
echo "bestanden: $pass   fehlgeschlagen: $fail\n";
exit($fail > 0 ? 1 : 0);
