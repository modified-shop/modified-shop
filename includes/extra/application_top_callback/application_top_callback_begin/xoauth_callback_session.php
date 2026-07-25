<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

if (defined('RUN_MODE_XOAUTH_CALLBACK')) {
    require_once(DIR_FS_EXTERNAL . 'phpmailer/classes/oauth_callback_state.php');

    $oauthCallbackState = oauth_callback_state::consume(
        isset($_GET['state']) ? (string)$_GET['state'] : ''
    );

    if (is_array($oauthCallbackState)) {
        $_GET['MODsid'] = $oauthCallbackState['session_id'];
        $_REQUEST['MODsid'] = $oauthCallbackState['session_id'];
        define('XOAUTH_CALLBACK_MODULE', $oauthCallbackState['module']);
    }
}
