<?php
  /* --------------------------------------------------------------
   $Id: check_requirements.php 3584 2012-08-31 12:47:10Z web28 $
   
    modified 1.06 rev7

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------*/

  define('SSL_VERSION_MIN', '1.2');
  define('PHP_VERSION_MIN', '5.3.0');
  define('PHP_VERSION_MAX', '5.6.99');

  //BOF *************  check PHP-Version *************
  //BOF - Dokuman - 2012-11-19: remove irritating PHP-Version message
  if (function_exists('version_compare')) {
    if(version_compare(phpversion(), PHP_VERSION_MIN, "<")){
      $error_flag = true;
      $php_flag = true;
      $messageStack->add('requirement', '<strong>'. sprintf(TEXT_PHPVERSION_TOO_OLD,PHP_VERSION_MIN) . phpversion() . '</strong>.');
    }
    if(version_compare(phpversion(), PHP_VERSION_MAX, ">")){
      $error_flag = true;
      $php_flag = true;
      $continue = true;
      $messageStack->add('requirement', '<strong>'.sprintf(TEXT_ERROR_PHP_MAX,PHP_VERSION_MAX) . phpversion() . '</strong>.');
    }
  } else {
    $error_flag = true;
    $php_flag = true;
    $messageStack->add('requirement', '<strong>'. sprintf(TEXT_PHPVERSION_TOO_OLD,PHP_VERSION_MIN) . phpversion() . '</strong>.');
  }
  
  $ok_message.= '<table cellpadding="5" border="1" style="width:100%;border-collapse:collapse;">';
  $status='<strong>OK</strong>';
  if ($php_flag==true)
    $status='<strong><font color="#A94442">'.TEXT_ERROR.'</font></strong>';
  $ok_message.='<tr><td>PHP VERSION</td><td>'.$status.' ('.phpversion().')</td></tr>';
  //EOF *************  check PHP-Version *************
  
  //BOF *************  check cURL-Support *************
  $ssl_version = 'undefined';
  $curl_version = array();
  if (function_exists('curl_init')) {
    $status='<strong>OK</strong>';
    $curl_version = curl_version();

    // check for SSL Version
    $ch = curl_init('https://www.howsmyssl.com/a/check');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($ch);
    curl_close($ch);
    $json = json_decode($data);
    if (is_object($json)) {
      $ssl_version = $json->tls_version;
    }
    if(version_compare(preg_replace('/[^0-9.]/', '', $ssl_version), SSL_VERSION_MIN, "<")){
      $status_tls = '<strong><font color="#A94442">'.TEXT_WARNING.'</font></strong><br />'.sprintf(TEXT_ERROR_SSLVERSION, SSL_VERSION_MIN, $ssl_version);
    } else {
      $status_tls = '<strong>OK</strong>';
    }
  } else {
    $status='<strong><font color="#A94442">'.TEXT_WARNING.'</font></strong><br />'.TEXT_CURL_NOT_SUPPORTED;
    $status_tls = '<strong><font color="#A94442">'.TEXT_WARNING.'</font></strong><br />'.sprintf(TEXT_ERROR_SSLVERSION, SSL_VERSION_MIN, $ssl_version);
  }
  $ok_message.='<tr><td>CURL VERSION</td><td>'.$status.' ('.$curl_version['version'].')</td></tr>';
  $ok_message.='<tr><td>SSL VERSION</td><td>'.$status_tls.' ('.$ssl_version.')</td></tr>';
  //EOF *************  check cURL-Support *************
  
  //BOF *************  check fsockopen *************
  if (function_exists('fsockopen')) {
    $status='<strong>OK</strong>';
  } else {
    $status='<strong><font color="#A94442">'.TEXT_WARNING.'</font></strong><br />'.TEXT_FSOCKOPEN_NOT_SUPPORTED;
  }
  $ok_message.='<tr><td>FSOCKOPEN</td><td>'.$status.'</td></tr>';
  //EOF *************  check fsockopen *************
  $gd=gd_info();
  if ($gd['GD Version']=='')
    $gd['GD Version']='<strong><font color="#A94442">'.TEXT_ERROR.TEXT_NO_GDLIB_FOUND.'</font></strong>';
  $status= '<strong>'.$gd['GD Version'].'</strong> ('.TEXT_GDLIBV2_SUPPORT.')';
  // display GDlibversion
  $ok_message.='<td>GDlib VERSION</td><td>'.$status.'</td>';
  if ($gd['GIF Read Support']==1 or $gd['GIF Support']==1) {
    $status='<strong>OK</strong>';
  } else {
    $status='<strong><font color="#A94442">'.TEXT_ERROR.'</font></strong><br />'.TEXT_GDLIB_MISSING_GIF_SUPPORT;
  }
  $ok_message.= '<tr><td>'.TEXT_GDLIB_GIF_VERSION .'</td><td>'.$status.'</td></tr>';
  $ok_message.= '</table>';
