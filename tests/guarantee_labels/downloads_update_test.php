<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Das Aufraeumen der Downloadzeilen darf nur in eindeutigen Faellen loeschen.
error_reporting(E_ALL & ~E_DEPRECATED);
$guarantee_labels_paths = require __DIR__.'/bootstrap.php';
$repo = $guarantee_labels_paths['repo'];

define('TABLE_ORDERS_PRODUCTS_DOWNLOAD', 'orders_products_download');
define('TABLE_ORDERS_PRODUCTS_ATTRIBUTES', 'orders_products_attributes');
define('WARNING_ORDERS_DOWNLOAD_LEFTOVER', 'WARNUNG');

class stack { public $msgs = array(); function add_session($m, $t = 'error') { $this->msgs[] = $t.': '.$m; } }
$messageStack = new stack();

function xtc_db_query($sql) {
  $GLOBALS['sql'][] = preg_replace('/\s+/', ' ', trim($sql));
  if (strpos($sql, 'orders_products_attributes') !== false) {
    // die Abfrage darf nicht auf einen Optionsnamen filtern, ein Fehlgriff wuerde loeschen
    if (strpos($sql, 'products_options') !== false) {
      $GLOBALS['namensfilter'] = true;
    }
    return array(array('total' => $GLOBALS['attribute']));
  }
  if (strpos($sql, 'SELECT COUNT(*) AS total') !== false) {
    return array(array('total' => count($GLOBALS['rows'])));
  }
  if (strpos($sql, 'DELETE FROM orders_products_download') !== false) {
    $GLOBALS['rows'] = array();
    return array();
  }
  return array();
}
function xtc_db_fetch_array(&$r) { return array_shift($r); }
function xtc_db_num_rows($r) { return count($r); }

$src = file_get_contents($repo.'/admin/includes/functions/orders_functions.php');
$start = strpos($src, '  function orders_product_downloads_cleanup(');
$end = strpos($src, "\n  }\n", $start) + 5;
eval(substr($src, $start, $end - $start));

$pass = 0; $fail = 0;
function ok($n, $c, $e = '') { global $pass, $fail; if ($c) { $pass++; echo "  ok    $n\n"; } else { $fail++; echo "  FAIL  $n".($e!==''?"  ($e)":'')."\n"; } }
// $attribute = alle verbliebenen Attribute der Position, unabhaengig von ihrer Bezeichnung
function lauf($attribute, $zeilen) {
  global $messageStack;
  $GLOBALS['attribute'] = $attribute;
  $GLOBALS['namensfilter'] = false;
  $GLOBALS['rows'] = array_fill(0, $zeilen, 'x');
  $GLOBALS['sql'] = array();
  $messageStack->msgs = array();
  orders_product_downloads_cleanup(10);
  return count($GLOBALS['rows']);
}

echo "\n== Eindeutige Faelle ==\n";
ok('kein Attribut mehr: alle Zeilen weg', lauf(0, 2) === 0);
ok('kein Attribut, keine Zeile: nichts zu tun', lauf(0, 0) === 0);
ok('keine Zeilen: nichts zu tun', lauf(3, 0) === 0);
ok('keine Zeilen: keine Warnung', count($messageStack->msgs) === 0);
ok('keine Zeilen: Attribute werden gar nicht gezaehlt', count(preg_grep('/orders_products_attributes/', $GLOBALS['sql'])) === 0);

// Ein umbenanntes Downloadattribut darf nicht als "kein Downloadattribut" gelten und
// damit alle gueltigen Zeilen der Position kosten.
ok('gezaehlt werden alle Attribute, nicht nur benannte', lauf(1, 1) !== null && $GLOBALS['namensfilter'] === false);

echo "\n== Mehrdeutige Faelle bleiben unangetastet und werden gemeldet ==\n";
ok('mehr Zeilen als Attribute: nichts geloescht', lauf(1, 2) === 2);
ok('mehr Zeilen als Attribute: gewarnt', count($messageStack->msgs) === 1, implode(' | ', $messageStack->msgs));
ok('Warnung als Session-Meldung', strpos(implode('', $messageStack->msgs), 'warning: ') === 0);

// koerperliches plus digitales Attribut, digitales geloescht: bleibt stehen, wird gemeldet
ok('ein Attribut, eine Zeile: nichts geloescht', lauf(1, 1) === 1);
ok('ein Attribut, eine Zeile: gewarnt', count($messageStack->msgs) === 1);
ok('zwei zu zwei: nichts geloescht', lauf(2, 2) === 2);
ok('zwei zu zwei: gewarnt', count($messageStack->msgs) === 1);
ok('weniger Zeilen als Attribute: nichts geloescht', lauf(3, 1) === 1);
ok('weniger Zeilen als Attribute: gewarnt', count($messageStack->msgs) === 1);

echo "\n== Es wird nie geschrieben ==\n";
lauf(1, 2);
ok('kein INSERT und kein UPDATE', count(preg_grep('/INSERT|UPDATE/i', $GLOBALS['sql'])) === 0);
// die Katalogtabellen duerfen nicht vorkommen, nur die Bestelltabellen
ok('kein Katalogzugriff', count(preg_grep('/(?<!orders_)products_attributes/i', $GLOBALS['sql'])) === 0,
   implode(' | ', $GLOBALS['sql']));

echo "\n----------------------------------------\nbestanden: $pass   fehlgeschlagen: $fail\n";
exit($fail > 0 ? 1 : 0);
