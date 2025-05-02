<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_address_format.inc.php,v 1.5 2003/08/13); www.nextcommerce.org
   (c) 2003 XT-Commerce
   
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  require_once(DIR_FS_INC . 'xtc_get_zone_code.inc.php');
  require_once(DIR_FS_INC . 'xtc_get_zone_name.inc.php');
  require_once(DIR_FS_INC . 'xtc_get_country_name.inc.php');
   
  function xtc_address_format($address_format_id, $address, $html, $boln, $eoln, $add_contact = false, $key_replacement = '') {
    $address_format_query = xtDBquery("SELECT address_format as format 
                                         FROM ".TABLE_ADDRESS_FORMAT." 
                                        WHERE address_format_id = '".(int)$address_format_id."'");
    $address_format = xtc_db_fetch_array($address_format_query, true);
    
    $account_company = ((ACCOUNT_COMPANY == 'true') ? true : false);
    if ($key_replacement != '') {
      $account_company = true;
      foreach ($address as $k => $v) {
        $address[str_replace($key_replacement, '', $k)] = $v;
      }
      if (isset($address['name'])) {
        unset($address['name']);
      }
    }
    
    $company = isset($address['company']) ? addslashes($address['company']) : '';
    $firstname = isset($address['firstname']) ? addslashes($address['firstname']) : '';
    $cid = isset($address['csID']) ? addslashes($address['csID']) : '';
    $lastname = isset($address['lastname']) ? addslashes($address['lastname']) : '';
    $street = isset($address['street_address']) ? addslashes($address['street_address']) : '';
    $suburb = isset($address['suburb']) ? addslashes($address['suburb']) : '';
    $city = isset($address['city']) ? addslashes($address['city']) : '';
    $state = isset($address['state']) ? addslashes($address['state']) : '';
    $country_id = isset($address['country_id']) ? $address['country_id'] : '';
    $zone_id = isset($address['zone_id']) ? $address['zone_id'] : '';
    $postcode = isset($address['postcode']) ? addslashes($address['postcode']) : '';
    $zip = $postcode;
    $country = isset($address['country_id']) ? xtc_get_country_name($country_id) : '';
    $zone = xtc_get_zone_name($country_id, $zone_id, $state);
    $state = xtc_get_zone_code($country_id, $zone_id, $state);

    $telephone = isset($address['telephone']) ? addslashes($address['telephone']) : '';
    $email_address = isset($address['email_address']) ? addslashes($address['email_address']) : '';

    if ($html) {
      // HTML Mode
      $HR = '<hr />';
      $hr = '<hr />';
      if ((empty($boln)) && ($eoln == "\n")) { // Values not specified, use rational defaults
        $CR = '<br />';
        $cr = '<br />';
        $eoln = $cr;
      } else { // Use values supplied
        $CR = $eoln . $boln;
        $cr = $CR;
      }
    } else {
      // Text Mode
      $CR = $eoln;
      $cr = $CR;
      $HR = '----------------------------------------';
      $hr = '----------------------------------------';
    }

    $statecomma = '';
    $streets = $street;
    if ($suburb != '') $streets = $street . $cr . $suburb;
    if ($firstname == '' && isset($address['name'])) $firstname = addslashes($address['name']);
    if ($country == '' && isset($address['country'])) $country = addslashes((is_array($address['country']) && array_key_exists('title', $address['country'])) ? $address['country']['title'] : $address['country']);
    if ($state != '') $statecomma = $state . ', ';
    
    if (defined('CAPITALIZE_ADDRESS_FORMAT') && CAPITALIZE_ADDRESS_FORMAT == 'true') {
      $city = strtoupper($city);
      $country = strtoupper($country);
    }
  
    $address = $address_format['format'];
    preg_match_all('#\$([a-zA-Z0-9]+)#', $address, $matches);
    if (isset($matches[1])) {
      $matches = compact($matches[1]);
      foreach ($matches as $k => $v) {
        $address = str_replace('$'.$k, $v, $address);
      }
    }

    if ($account_company == true && xtc_not_null($company)) {
      if (xtc_not_null($firstname) || xtc_not_null($lastname)) $company .= $cr;
      $address = $company . $address;
    }

    if ($add_contact === true) {
      $contact = '';
      if ($telephone != '') $contact .=  $cr . $telephone;
      if ($email_address != '') $contact .=  $cr . $email_address;
      if ($contact != '') $address .= $cr . $contact;
    }
    
    $address = stripslashes($address);

    return $address;
  }
