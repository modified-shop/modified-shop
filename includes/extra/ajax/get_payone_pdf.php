<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

function get_payone_pdf() {
  $doc = ((isset($_GET['doc'])) ? $_GET['doc'] : '');

  $filename = '';
  if ($doc == 'contract') {
    $id = ((isset($_GET['id'])) ? (int)$_GET['id'] : 0);
    if (isset($_SESSION['payone_installment']['contracts'][$id])) {
      $filename = $_SESSION['payone_installment']['contracts'][$id];
    }
  } elseif ($doc == 'sepa_mandate') {
    if (isset($_SESSION['payone_elv_sepa_mandate_pdf'])) {
      $filename = $_SESSION['payone_elv_sepa_mandate_pdf'];
    }
  }

  $filename = basename($filename);
  if ($filename == '' || !is_file(SQL_CACHEDIR.$filename)) {
    header('HTTP/1.1 404 Not Found');
    exit();
  }

  // drop the gzip buffer so Content-Length matches the body
  while (ob_get_level() > 0) {
    ob_end_clean();
  }

  header('Content-Type: application/pdf');
  header('Content-Disposition: attachment; filename="'.$filename.'"');
  header('Content-Length: '.filesize(SQL_CACHEDIR.$filename));
  header('Cache-Control: private, no-store');
  header('X-Content-Type-Options: nosniff');
  readfile(SQL_CACHEDIR.$filename);
  exit();
}
