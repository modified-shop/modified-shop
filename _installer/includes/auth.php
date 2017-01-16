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
    if (!isset($_SERVER['PHP_AUTH_USER'])) {
      return false;
    } else {    
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
  
      $email_address = $_SERVER['PHP_AUTH_USER'];
      $password = $_SERVER['PHP_AUTH_PW'];
      
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
    }

    return true;
  }

  function show_auth() {
    header('WWW-Authenticate: Basic realm="ADMIN Authentication required"');
    header('HTTP/1.0 401 Unauthorized');
    die('<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
         <html><head>
         <title>401 Unauthorized</title>
         </head><body>
         <h1>Unauthorized</h1>
         <p>This server could not verify that you
         are authorized to access the document
         requested.  Either you supplied the wrong
         credentials (e.g., bad password), or your
         browser doesn\'t understand how to supply
         the credentials required.</p>
        </body></html>'
    );
  }  
?>