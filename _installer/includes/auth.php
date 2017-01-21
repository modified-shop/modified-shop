<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   ----------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

  function check_auth() {
    if (isset($_POST) && count($_POST) > 0) {    
      // include functions
      require_once(DIR_FS_INC.'auto_include.inc.php');
      require_once(DIR_WS_INCLUDES . 'database_tables.php');
  
      require_once (DIR_FS_INC.'xtc_not_null.inc.php');
      require_once (DIR_FS_INC.'xtc_validate_password.inc.php');

      // Database
      defined('DB_MYSQL_TYPE') OR define('DB_MYSQL_TYPE', 'mysql');
      require_once (DIR_FS_INC.'db_functions_'.DB_MYSQL_TYPE.'.inc.php');
      require_once (DIR_FS_INC.'db_functions.inc.php');

      // make a connection to the database... now
      xtc_db_connect() or die('Unable to connect to database server!');
  
      $email_address = $_POST['email_address'];
      $password = $_POST['password'];
      
      // check if email exists
      $check_customer_query = xtc_db_query("SELECT customers_id, 
                                                   customers_password
                                              FROM ".TABLE_CUSTOMERS." 
                                             WHERE customers_email_address = '".xtc_db_input($email_address)."' 
                                               AND customers_status = '0'
                                               AND account_type = '0'");

      if (xtc_db_num_rows($check_customer_query) > 0) {
        $check_customer = xtc_db_fetch_array($check_customer_query);      
        // Check that password is good
        if (xtc_validate_password($password, $check_customer['customers_password'], $check_customer['customers_id']) !== true) {
          return false;
        }
      } else {
        return false;
      }
    } elseif (!isset($_SESSION['auth']) || $_SESSION['auth'] === false) {
      return false;
    }
    return true;
  }

  function show_auth() {
    define('_MODIFIED_SHOP_LOGIN', true);
    include(DIR_FS_CATALOG.'includes/login_admin.php');
    exit();
  }  
?>