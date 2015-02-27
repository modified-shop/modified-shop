<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_db_error.inc.php 5008 2013-07-04 12:37:12Z GTB $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(database.php,v 1.19 2003/03/22); www.oscommerce.com
   (c) 2003 nextcommerce (xtc_db_error.inc.php,v 1.4 2003/08/19); www.nextcommerce.org
   (c) 2006 XT-Commerce (xtc_db_error.inc.php 899 2005-04-29)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  die('Deprecated File: '.basename(__FILE__).'. Use db_functions_mysql(i) instead.');
/*
  function xtc_db_error($query, $errno, $error) {

    // Deliver 503 Error on database error (so crawlers won't index the error page)
    if (!defined('DIR_FS_ADMIN')) {
      header("HTTP/1.1 503 Service Temporarily Unavailable");
      header("Status: 503 Service Temporarily Unavailable");
      header("Connection: Close");
    }
    
    // Send an email to the shop owner if a sql error occurs
    if (defined('EMAIL_SQL_ERRORS') && EMAIL_SQL_ERRORS == 'true') {
      if (defined('RUN_MODE_ADMIN')) {
        // PHPMailer
        require_once (DIR_FS_EXTERNAL.'phpmailer/class.phpmailer.php');
        require_once (DIR_FS_INC.'xtc_php_mail.inc.php');
      }
      $subject = 'DATA BASE ERROR AT - ' . STORE_NAME;
      $message = '<font color="#000000"><strong>' . $errno . ' - ' . $error . '<br /><br />' . $query . '<br /><br />Request URL: ' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'].'<br /><br /><small><font color="#ff0000">[XT SQL Error]</font></small><br /><br /></strong></font>';
      xtc_php_mail(STORE_OWNER_EMAIL_ADDRESS, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, '', '', STORE_OWNER_EMAIL_ADDRESS, STORE_OWNER, '', '', $subject, nl2br($message), $message);
    }
    
    // show the full sql error + full query only to logged-in admins or error_reporting() != 0
    if (isset($_SESSION['customers_status']['customers_status']) && $_SESSION['customers_status']['customers_status'] == '0' || error_reporting() != 0) {
      die('<font color="#000000"><strong>' . $errno . ' - ' . $error . '<br /><br />' . $query . '<br /><br /><small><font color="#ff0000">[MOD SQL Error]</font></small><br /><br /></strong></font>');
    } else {
      die('<font color="#ff0000"><strong>Es ist ein Fehler aufgetreten!<br />There was an error!<br />Il y avait une erreur!</strong></font>');
    }

    //and display an info message for the shop customer and redirect him
    echo '<p>'.ERROR_SQL_DB_QUERY.'</p>';    
    if ($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] != $_SERVER['HTTP_HOST']) {
      $redirect_time = 5; // in seconds
      echo '<p>'.sprintf(ERROR_SQL_DB_QUERY_REDIRECT, $redirect_time).'</p>';      
      echo '<script language="javascript">';
      $redirect_time = $redirect_time * 1000; // convert to milliseconds for javascript redirect
      echo 'setTimeout(\'location.href="http://' . $_SERVER['HTTP_HOST'] . '"\','.$redirect_time.');';
      echo '</script>';
    }
    exit(); 
  }
*/
?>
