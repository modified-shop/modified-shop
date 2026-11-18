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
   * admin_action_token()
   *
   * token of the admin session for actions that must not start from a foreign page,
   * independent of CSRF_TOKEN_SYSTEM and CSRF_TOKEN_EXCLUSIONS
   *
   * @param string|null $value posted value to check, null to get the token for a form
   * @return string|bool the token, or whether the posted value matches it
   */
  function admin_action_token($value = null) {
    if (!isset($_SESSION['admin_action_token'])
        || !is_string($_SESSION['admin_action_token'])
        || strlen($_SESSION['admin_action_token']) != 32
        )
    {
      $_SESSION['admin_action_token'] = bin2hex(random_bytes(16));
    }

    if ($value === null) {
      return $_SESSION['admin_action_token'];
    }

    return (is_string($value) && hash_equals($_SESSION['admin_action_token'], $value));
  }
