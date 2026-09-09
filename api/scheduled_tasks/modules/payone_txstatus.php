<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function cron_payone_txstatus() {
    $language = ((isset($_SESSION['language'])) ? basename((string)$_SESSION['language']) : 'german');
    $language_file = DIR_FS_EXTERNAL.'payone/lang/'.$language.'.php';
    if (!is_file($language_file)) {
      $language_file = DIR_FS_EXTERNAL.'payone/lang/german.php';
    }
    require_once($language_file);
    require_once(DIR_FS_EXTERNAL.'payone/classes/PayoneModified.php');

    $payone = new PayoneModified();
    $payone->processPendingTransactionStatuses();

    return true;
  }
