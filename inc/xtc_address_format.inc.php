<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_address_format.inc.php 899 2005-04-29 02:40:57Z hhgag $   

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
  require_once(DIR_FS_INC . 'xtc_get_country_name.inc.php');
   
  function xtc_address_format($address_format_id, $address, $html, $boln, $eoln) {
    $address_format_query = xtc_db_query("SELECT address_format as format 
                                            FROM ".TABLE_ADDRESS_FORMAT." 
                                           WHERE address_format_id = '".(int)$address_format_id."'");
    $address_format = xtc_db_fetch_array($address_format_query);

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
    $state = xtc_get_zone_code($country_id, $zone_id, $state);

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
    if ($firstname == '') $firstname = addslashes($address['name']);
    if ($country == '') $country = addslashes((is_array($address['country']) && array_key_exists('title', $address['country'])) ? $address['country']['title'] : $address['country']);
    if ($state != '') $statecomma = $state . ', ';

    $fmt = $address_format['format'];
    eval("\$address = \"$fmt\";");

    if ( (ACCOUNT_COMPANY == 'true') && (xtc_not_null($company)) ) {
      $address = $company . $cr . $address;
    }

    $address = stripslashes($address);

    return $address;
  }
 
 ?>