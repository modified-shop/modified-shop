<?php
/* -----------------------------------------------------------------------------------------
   $Id: sessions.php 3570 2012-08-30 16:15:47Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(sessions.php,v 1.16 2003/04/02); www.oscommerce.com
   (c) 2003	nextcommerce (sessions.php,v 1.5 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce (sessions.php 1195 2005-08-28)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

   define('SESSION_LIFE_ADMIN_DEFAULT', 7200);

   @ini_set("session.gc_maxlifetime", 1440);
   @ini_set("session.gc_probability", 100);

  if (STORE_SESSIONS == 'mysql') {  
    if (!$SESS_LIFE = get_cfg_var('session.gc_maxlifetime')) {
      $SESS_LIFE = 1440;
    }
    if (defined('SESSION_LIFE_CUSTOMERS')) {
      $SESS_LIFE = (int)SESSION_LIFE_CUSTOMERS;
    }

    function _sess_open($save_path, $session_name) {
      return true;
    }

    function _sess_close() {
      return true;
    }

    function _sess_read($key) {
      $value_query = xtc_db_query("-- includes/functions/sessions.php
                                   SELECT value
                                     FROM " . TABLE_SESSIONS . "
                                    WHERE sesskey = '" . xtc_db_input($key) . "'
                                      AND expiry > '" . time() . "'");
      $value = xtc_db_fetch_array($value_query);

      if (isset($value['value']) && $value['value']!='') {
        return base64_decode($value['value']);
      }
      return '';
    }

    function _sess_write($key, $val) {
      global $SESS_LIFE;

      $flag = '';
      if (isset($_SESSION['customers_status']['customers_status']) && $_SESSION['customers_status']['customers_status'] == '0') {
        $SESS_LIFE = defined('SESSION_LIFE_ADMIN') ? (int)SESSION_LIFE_ADMIN : (int)SESSION_LIFE_ADMIN_DEFAULT;
        $flag = 'admin';
      }
      $expiry = time() + (int)$SESS_LIFE;
      //$value = addslashes($val);
      $value = base64_encode($val);

      $check_query = xtc_db_query("-- includes/functions/sessions.php
                                   SELECT count(*) as total
                                     FROM " . TABLE_SESSIONS . "
                                    WHERE sesskey = '" . xtc_db_input($key) . "'");
      $total = xtc_db_fetch_array($check_query);

      if ($total['total'] > 0) {
        return xtc_db_query("-- includes/functions/sessions.php
                                   UPDATE " . TABLE_SESSIONS . "
                                SET expiry = '". $expiry ."',
                                     value = '". xtc_db_input($value) ."',
                                      flag = '". xtc_db_input($flag) ."'
                             WHERE sesskey = '". xtc_db_input($key) ."'");
      } else {
        $sql_data_array = array('sesskey' => $key,
                                'expiry'  => (int)$expiry,
                                'value'   => $value,
                                'flag'    => $flag
                               );
        return xtc_db_perform(TABLE_SESSIONS,$sql_data_array);
      }
    }

    function _sess_destroy($key) {
      return xtc_db_query("-- includes/functions/sessions.php
                           DELETE FROM " . TABLE_SESSIONS . "
                            WHERE sesskey = '" . xtc_db_input($key) . "'");
    }

    function _sess_gc($maxlifetime) {
      xtc_db_query("-- includes/functions/sessions.php
                    DELETE FROM " . TABLE_SESSIONS . "
                    WHERE expiry < '" . time() . "'");
      return true;
    }

    session_set_save_handler('_sess_open', '_sess_close', '_sess_read', '_sess_write', '_sess_destroy', '_sess_gc');
    register_shutdown_function('session_write_close');
  }


  function xtc_session_start() {
    if (preg_replace('/[a-zA-Z0-9]/', '', session_id()) != '') {
      xtc_session_id(md5(uniqid(rand(), true)));
    }
    $temp = session_start();

    return $temp;
  }


//removed deprecated function session_register to be ready for PHP >= 5.3
/*
  function xtc_session_register($variable) {
    global $session_started;
    if ($session_started == true) {
      return session_register($variable);
    }
  }
*/

  function xtc_session_is_registered($variable) {
    return isset($_SESSION[$variable]);
  }

//removed deprecated function session_unregister to be ready for PHP >= 5.3
/*
  function xtc_session_unregister($variable) {
    return session_unregister($variable);
  }
*/


  function xtc_session_id($sessid = '') {
    if (!empty($sessid)) {
      $tempSessid = $sessid;
      if (preg_replace('/[a-zA-Z0-9]/', '', $tempSessid) != '') {
       $sessid = md5(uniqid(rand(), true));
      }
      return session_id($sessid);
    } else {
      return session_id();
    }
  }

  function xtc_session_name($name = '') {
    if (!empty($name)) {
      $tempName = $name;
      if (preg_replace('/[a-zA-Z]/', '', $tempName) == '') {
        return session_name($name);
      }
      return false;
    } else {
      return session_name();
    }
  }

  function xtc_session_close() {
    if (function_exists('session_close')) {
      return session_close();
    }
  }

  function xtc_session_destroy() {
    return session_destroy();
  }

  function xtc_session_save_path($path = '') {
    if (!empty($path)) {
      return session_save_path($path);
    } else {
      return session_save_path();
    }
  }

  function xtc_session_recreate() {
    global $http_domain, $https_domain;
    
    if ($http_domain == $https_domain) {
      $session_backup = $_SESSION;
      $old_session_id = session_id();
      session_regenerate_id(true);
      $new_session_id = xtc_session_id();
      //session_id($old_session_id);
      session_id($new_session_id);
      $_SESSION = $session_backup;
      
      if (STORE_SESSIONS == 'mysql') {
        session_set_save_handler('_sess_open', '_sess_close', '_sess_read', '_sess_write', '_sess_destroy', '_sess_gc');
        register_shutdown_function('session_write_close');
      }
            
      // update whos_online
      xtc_db_query("UPDATE " . TABLE_WHOS_ONLINE . "
                       SET session_id = '".xtc_db_input($new_session_id)."' 
                     WHERE session_id = '".xtc_db_input($old_session_id)."'");
    }
  }
?>