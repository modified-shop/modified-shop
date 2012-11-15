<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   xtcModified - community made shopping
   http://www.xtc-modified.org

   Copyright (c) 2009 - 2012 xtcModified
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_address_format.inc.php,v 1.5 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce (xtc_address_format.inc.php 899 2005-04-29)

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
   require_once(DIR_FS_INC . 'xtc_get_zone_code.inc.php');
   require_once(DIR_FS_INC . 'xtc_get_country_name.inc.php');
   
function xtc_address_format($address_format_id, $address, $html, $boln, $eoln) {
    $address_format_query = xtc_db_query("select address_format as format from " . TABLE_ADDRESS_FORMAT . " where address_format_id = '" . $address_format_id . "'");
    $address_format = xtc_db_fetch_array($address_format_query);

    $company = addslashes($address['company']);
    $firstname = addslashes($address['firstname']);
    $lastname = addslashes($address['lastname']);
    $street = addslashes($address['street_address']);
    $suburb = addslashes($address['suburb']);
    $city = addslashes($address['city']);
    $state = addslashes($address['state']);
    //BOF - h-h-h - 2011-01-27 - add is_array request
    //BOF - DokuMan - 2010-08-24 - set undefined index
    //$country_id = $address['country_id'];
    //$zone_id = $address['zone_id'];
    //$country_id = array_key_exists('country_id', $address) ? $address['country_id'] : 0;
    //$zone_id = array_key_exists('zone_id', $address) ? $address['zone_id'] : 0;
    $country_id = (is_array($address) && array_key_exists('country_id', $address)) ? $address['country_id'] : 0;
    $zone_id = (is_array($address) && array_key_exists('zone_id', $address)) ? $address['zone_id'] : 0;
    //EOF - DokuMan - 2010-08-24 - set undefined index
    //EOF - h-h-h - 2011-01-27 - add is_array request
    $postcode = addslashes($address['postcode']);
    $zip = $postcode;
    $country = xtc_get_country_name($country_id);
    $state = xtc_get_zone_code($country_id, $zone_id, $state);

    if ($html) {
// HTML Mode
      $HR = '<hr />';
      $hr = '<hr />';
      if ( ($boln == '') && ($eoln == "\n") ) { // Values not specified, use rational defaults
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
    if ($country == '') $country = addslashes($address['country']);
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