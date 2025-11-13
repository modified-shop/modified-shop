<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function ip_clearing($ip_address, $type = '') {
    if ($type == '' && defined('SAVE_IP_LOG')) { 
      $type = SAVE_IP_LOG;
    }
  
    if ($type == 'xxx') {
      if (strpos($ip_address, '.') !== false) {
        $ip_address = preg_replace('/(?!\d{1,3}\.)\d/', '', $ip_address);
        $ip_address .= 'xxx';
      } else {
        $ip_address = preg_replace('/(?!\w{1,4}\:)\w/', '', $ip_address);    
        $ip_address .= 'xxxx';
      }
    } elseif ($type == '' || $type == 'false') {
      $ip_address = '';
    }
  
    return $ip_address;
  }
