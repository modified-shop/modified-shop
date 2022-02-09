<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com
   (c) 2003   nextcommerce (xtc_output_warning.inc.php,v 1.3 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function xtc_output_warning() {
    global $messageStack;
    
    if (isset($_SESSION['customers_status']['customers_status']) && $_SESSION['customers_status']['customers_status'] == '0' ) {

      // check if the 'install' directory exists, and warn of its existence
      if (WARN_INSTALL_EXISTENCE == 'true') {
        if (is_dir(DIR_FS_CATALOG . '/' . DIR_MODIFIED_INSTALLER)) {
          $messageStack->add('output_warning', sprintf(WARNING_INSTALL_DIRECTORY_EXISTS, DIR_FS_CATALOG . DIR_MODIFIED_INSTALLER));
        }
      }

      // check if the configure.php file is writeable
      if (WARN_CONFIG_WRITEABLE == 'true') {
        if ((is_file(DIR_WS_INCLUDES . 'configure.php')) && (is_writeable(DIR_WS_INCLUDES . 'configure.php'))) {
          $messageStack->add('output_warning', sprintf(WARNING_CONFIG_FILE_WRITEABLE, DIR_WS_INCLUDES . 'configure.php'));
        }
        if ((is_file(DIR_WS_INCLUDES . 'local/configure.php')) && (is_writeable(DIR_WS_INCLUDES . 'local/configure.php'))) {
          $messageStack->add('output_warning', sprintf(WARNING_CONFIG_FILE_WRITEABLE, DIR_WS_INCLUDES . 'local/configure.php'));
        }
      }

      // check if the session folder is writeable
      if (WARN_SESSION_DIRECTORY_NOT_WRITEABLE == 'true') {
        if (STORE_SESSIONS == '') {
          if (!is_dir(xtc_session_save_path())) {
            $messageStack->add('output_warning', WARNING_SESSION_DIRECTORY_NON_EXISTENT);
          } elseif (!is_writeable(xtc_session_save_path())) {
            $messageStack->add('output_warning', WARNING_SESSION_DIRECTORY_NOT_WRITEABLE);
          }
        }
      }

      // check session.auto_start is disabled
      if ( (WARN_SESSION_AUTO_START == 'true') && (function_exists('ini_get')) ) {
        if (ini_get('session.auto_start') == '1') {
          $messageStack->add('output_warning', WARNING_SESSION_AUTO_START);
        }
      }

      if ( (WARN_DOWNLOAD_DIRECTORY_NOT_READABLE == 'true') && (DOWNLOAD_ENABLED == 'true') ) {
        if (!is_dir(DIR_FS_DOWNLOAD)) {
          $messageStack->add('output_warning', WARNING_DOWNLOAD_DIRECTORY_NON_EXISTENT);
        }
      }
      
      if ($messageStack->size('output_warning') > 0) {
        echo '<div class="errormessage shopsystem">' . $messageStack->output('output_warning') . '</div>';
      }
    }
  }
