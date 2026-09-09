<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  /**
   * guarantee_labels_log()
   *
   * Writes to mod_guarantee_labels_<level>_<date>.log, so the existing log administration
   * lists and cleans the file like any other shop log. Logging never interrupts the caller:
   * a checkout has to run on even when the log cannot be written.
   *
   * @param string $level PSR-3 level, e.g. error or warning
   * @param string $message
   * @param array $context values replacing the {placeholders} of the message
   * @return void
   */
  function guarantee_labels_log($level, $message, $context = array()) {
    static $logger;

    if (!isset($logger)) {
      $logger = false;

      if (is_file(DIR_FS_CATALOG.'includes/classes/class.logger.php')) {
        require_once(DIR_FS_CATALOG.'includes/classes/class.logger.php');

        if (class_exists('LoggingManager')) {
          $logger = new LoggingManager(DIR_FS_LOG.'mod_guarantee_labels_%s_%s.log', 'guarantee_labels', 'debug');
        }
      }
    }

    if ($logger === false) {
      return;
    }

    $logger->log($level, $message, (array)$context);
  }
