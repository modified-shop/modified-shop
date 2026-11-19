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
   * admin_form_posted()
   *
   * an action that deletes data came from its form: a POST with the form token,
   * not a link and not an empty POST, which the token check lets through
   *
   * @return boolean
   */
  function admin_form_posted() {
    if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
      return false;
    }
    if (!defined('CSRF_TOKEN_SYSTEM') || CSRF_TOKEN_SYSTEM != 'true') {
      return true;
    }

    return (isset($_SESSION['CSRFName'], $_SESSION['CSRFToken'], $_POST[$_SESSION['CSRFName']])
            && is_string($_POST[$_SESSION['CSRFName']])
            && hash_equals((string)$_SESSION['CSRFToken'], $_POST[$_SESSION['CSRFName']])
            );
  }
?>
