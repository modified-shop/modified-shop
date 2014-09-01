<?php
  /* --------------------------------------------------------------
   $Id: check_requirements.php 3584 2012-08-31 12:47:10Z web28 $
   
    modified 1.06 rev7

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------*/

  define('PHP_VERSION_MIN', '5.0.0');
  define('PHP_VERSION_MAX', '5.4.99');

  //BOF *************  check PHP-Version *************
  //BOF - Dokuman - 2012-11-19: remove irritating PHP-Version message
  if (function_exists('version_compare')) {
    if(version_compare(phpversion(), PHP_VERSION_MIN, "<")){
      $error_flag = true;
      $php_flag = true;
      $message .= '<strong>'. sprintf(TEXT_PHPVERSION_TOO_OLD,PHP_VERSION_MIN) . phpversion() . '</strong>.';
    }
    /*
    if(version_compare(phpversion(), PHP_VERSION_MAX, ">")){
      $error_flag = true;
      $php_flag = true;
      $message .= '<strong>'.sprintf(TEXT_ERROR_PHP_MAX,PHP_VERSION_MAX) . phpversion() . '</strong>.';
    }
    */
    //EOF - Dokuman - 2012-11-19: remove irritating PHP-Version message
  } else {
    $error_flag = true;
    $php_flag = true;
    $message .= '<strong>'. sprintf(TEXT_PHPVERSION_TOO_OLD,PHP_VERSION_MIN) . phpversion() . '</strong>.';
  }
  $status='<strong>OK</strong>';
  if ($php_flag==true)
    $status='<strong><font color="#ff0000">'.TEXT_ERROR.'</font></strong>';
  $ok_message.='PHP VERSION ............................... '.$status.' ('.phpversion().')<br /><hr noshade />';
  //EOF *************  check PHP-Version *************
  
  //BOF *************  check cURL-Support *************
  $curl_version = array();
  if (function_exists('curl_init')) {
    $status='<strong>OK</strong>';
    $curl_version = curl_version();
  } else {
    $status='<strong><font color="#ff0000">'.TEXT_WARNING.'</font></strong><br />'.TEXT_CURL_NOT_SUPPORTED;
  }
  $ok_message.='CURL VERSION ............................ '.$status.' ('.$curl_version['version'].')<br /><hr noshade />';
  //EOF *************  check cURL-Support *************
  
  //BOF *************  check fsockopen *************
  if (function_exists('fsockopen')) {
    $status='<strong>OK</strong>';
  } else {
    $status='<strong><font color="#ff0000">'.TEXT_WARNING.'</font></strong><br />'.TEXT_FSOCKOPEN_NOT_SUPPORTED;
  }
  $ok_message.='FSOCKOPEN ................................. '.$status.'<br /><hr noshade />';
  //EOF *************  check fsockopen *************
  $gd=gd_info();
  if ($gd['GD Version']=='')
    $gd['GD Version']='<strong><font color="#ff0000">'.TEXT_ERROR.TEXT_NO_GDLIB_FOUND.'</font></strong>';
  $status= '<strong>'.$gd['GD Version'].'</strong> ('.TEXT_GDLIBV2_SUPPORT.')';
  // display GDlibversion
  $ok_message.='GDlib VERSION .............................. '.$status.'<br /><hr noshade />';
  if ($gd['GIF Read Support']==1 or $gd['GIF Support']==1) {
    $status='<strong>OK</strong>';
  } else {
    $status='<strong><font color="#ff0000">'.TEXT_ERROR.'</font></strong><br />'.TEXT_GDLIB_MISSING_GIF_SUPPORT;
  }
  $ok_message.= TEXT_GDLIB_GIF_VERSION .' .............. '.$status.'<br /><hr noshade />';
