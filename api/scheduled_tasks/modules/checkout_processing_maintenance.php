<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function cron_checkout_processing_maintenance() {
    $timeout = defined('CHECKOUT_PROCESSING_TIMEOUT') ? (int)CHECKOUT_PROCESSING_TIMEOUT : 1800;
    $timeout = max(1, $timeout);

    xtc_db_query("UPDATE ".TABLE_CHECKOUT_PROCESSING."
                     SET processing_status = 'failed',
                         last_modified = NOW()
                   WHERE processing_status = 'processing'
                     AND last_modified < DATE_SUB(NOW(), INTERVAL ".$timeout." SECOND)");

    $retention_days = defined('CHECKOUT_PROCESSING_RETENTION_DAYS') ? (int)CHECKOUT_PROCESSING_RETENTION_DAYS : 30;
    $retention_days = max(1, $retention_days);

    do {
      xtc_db_query("DELETE FROM ".TABLE_CHECKOUT_PROCESSING."
                          WHERE processing_status IN ('completed', 'failed')
                            AND last_modified < DATE_SUB(NOW(), INTERVAL ".$retention_days." DAY)
                          LIMIT 1000");
      $deleted = xtc_db_affected_rows();
    } while ($deleted === 1000);

    return true;
  }
