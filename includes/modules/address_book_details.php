<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(address_book_details.php,v 1.9 2003/05/22); www.oscommerce.com
   (c) 2003 nextcommerce (address_book_details.php,v 1.9 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------/-----*/

// include needed functions
$module_smarty=new Smarty;
$module_smarty->caching = false; //DokuMan - 2012-10-30 - avoid Smarty caching in order to display the correct data, if caching is enabled in shop backend

//BOF - GTB - 2010-08-03 - Security Fix - Base
$module_smarty->assign('tpl_path',DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');
//$module_smarty->assign('tpl_path','templates/'.CURRENT_TEMPLATE.'/');
//EOF - GTB - 2010-08-03 - Security Fix - Base
include_once(DIR_FS_INC . 'xtc_get_zone_name.inc.php');
include_once(DIR_FS_INC . 'xtc_get_country_list.inc.php');

if (!isset($process)) $process = false;

if (ACCOUNT_GENDER == 'true') {
  $male = (isset($entry['entry_gender']) && $entry['entry_gender'] == 'm') ? true : false;
  $female = (isset($entry['entry_gender']) && $entry['entry_gender'] == 'f') ? true : false;

  $module_smarty->assign('gender','1');
  $module_smarty->assign('INPUT_MALE',xtc_draw_radio_field(array('name'=>'gender','suffix'=>MALE.'&nbsp;'), 'm',$male));
  $module_smarty->assign('INPUT_FEMALE',xtc_draw_radio_field(array('name'=>'gender','suffix'=>FEMALE.'&nbsp;','text'=>(xtc_not_null(ENTRY_GENDER_TEXT) ? '<span class="inputRequirement">&nbsp;' . ENTRY_GENDER_TEXT . '</span>': '')), 'f',$female));
}

$module_smarty->assign('INPUT_FIRSTNAME',xtc_draw_input_fieldNote(array('name'=>'firstname','text'=>'&nbsp;' . (xtc_not_null(ENTRY_FIRST_NAME_TEXT) ? '<span class="inputRequirement">' . ENTRY_FIRST_NAME_TEXT . '</span>': '')),isset($entry['entry_firstname']) ? $entry['entry_firstname'] : ''));
$module_smarty->assign('INPUT_LASTNAME',xtc_draw_input_fieldNote(array('name'=>'lastname','text'=>'&nbsp;' . (xtc_not_null(ENTRY_LAST_NAME_TEXT) ? '<span class="inputRequirement">' . ENTRY_LAST_NAME_TEXT . '</span>': '')),isset($entry['entry_lastname']) ? $entry['entry_lastname'] : ''));

if (ACCOUNT_COMPANY == 'true') {
  $module_smarty->assign('company','1');
  $module_smarty->assign('INPUT_COMPANY',xtc_draw_input_fieldNote(array('name'=>'company','text'=>'&nbsp;' . (xtc_not_null(ENTRY_COMPANY_TEXT) ? '<span class="inputRequirement">' . ENTRY_COMPANY_TEXT . '</span>': '')), isset($entry['entry_company']) ? $entry['entry_company'] : ''));
}

$module_smarty->assign('INPUT_STREET',xtc_draw_input_fieldNote(array('name'=>'street_address','text'=>'&nbsp;' . (xtc_not_null(ENTRY_STREET_ADDRESS_TEXT) ? '<span class="inputRequirement">' . ENTRY_STREET_ADDRESS_TEXT . '</span>': '')), isset($entry['entry_street_address']) ? $entry['entry_street_address'] : ''));

if (ACCOUNT_SUBURB == 'true') {
  $module_smarty->assign('suburb','1');
  $module_smarty->assign('INPUT_SUBURB',xtc_draw_input_fieldNote(array('name'=>'suburb','text'=>'&nbsp;' . (xtc_not_null(ENTRY_SUBURB_TEXT) ? '<span class="inputRequirement">' . ENTRY_SUBURB_TEXT . '</span>': '')), isset($entry['entry_suburb']) ? $entry['entry_suburb'] : ''));
}
$module_smarty->assign('INPUT_CODE',xtc_draw_input_fieldNote(array('name'=>'postcode','text'=>'&nbsp;' . (xtc_not_null(ENTRY_POST_CODE_TEXT) ? '<span class="inputRequirement">' . ENTRY_POST_CODE_TEXT . '</span>': '')), isset($entry['entry_postcode']) ? $entry['entry_postcode'] : ''));
$module_smarty->assign('INPUT_CITY',xtc_draw_input_fieldNote(array('name'=>'city','text'=>'&nbsp;' . (xtc_not_null(ENTRY_CITY_TEXT) ? '<span class="inputRequirement">' . ENTRY_CITY_TEXT . '</span>': '')), isset($entry['entry_city']) ? $entry['entry_city'] : ''));

if (ACCOUNT_STATE == 'true') {
$module_smarty->assign('state','1');

  if ($process == true) {
    if ($entry_state_has_zones == true) {
      $zones_array = array();
      $zones_query = xtc_db_query("select zone_id, zone_name from " . TABLE_ZONES . " where zone_country_id = '" . xtc_db_input($country) . "' order by zone_name");
      while ($zones_values = xtc_db_fetch_array($zones_query)) {
        $zones_array[] = array('id' => $zones_values['zone_id'], 'text' => $zones_values['zone_name']);
      }
      $state_input= xtc_draw_pull_down_menuNote(array('name'=>'state','text'=>'&nbsp;' .(xtc_not_null(ENTRY_STATE_TEXT) ? '<span class="inputRequirement">' . ENTRY_STATE_TEXT . '</span>': '')), $zones_array, $zone_id); //DokuMan - 2011-12-28 - Use $zone_id from address_book_process.php
    } else {
      $state_input= xtc_draw_input_fieldNote(array('name'=>'state','text'=>'&nbsp;' .(xtc_not_null(ENTRY_STATE_TEXT) ? '<span class="inputRequirement">' . ENTRY_STATE_TEXT . '</span>': '')));
    }
  } else {
      $state_input= xtc_draw_input_fieldNote(array('name'=>'state','text'=>'&nbsp;' .(xtc_not_null(ENTRY_STATE_TEXT) ? '<span class="inputRequirement">' . ENTRY_STATE_TEXT . '</span>': '')),
      xtc_get_zone_name(isset($entry['entry_country_id'])?$entry['entry_country_id']:'',
                        isset($entry['entry_zone_id'])?$entry['entry_zone_id']:'',
                        isset($entry['entry_state'])?$entry['entry_state']:''));
  }

$module_smarty->assign('INPUT_STATE',$state_input);
}

if (isset($_POST['country'])) {
  $selected = $_POST['country'];
//BOF - DokuMan - 2010-10-13 - check for $entry['entry_country_id']
} elseif (isset($entry['entry_country_id'])) {
  $selected = $entry['entry_country_id'];
//EOF - DokuMan - 2010-10-13 - check for $entry['entry_country_id']
} else {
// BOF - Tomcraft - 2009-11-15 - Use STORE_COUNTRY as default country
//  $selected = $entry['entry_country_id'];
  $selected = STORE_COUNTRY;
// EOF - Tomcraft - 2009-11-15 - Use STORE_COUNTRY as default country
}

$module_smarty->assign('SELECT_COUNTRY',xtc_get_country_list(array('name'=>'country','text'=>'&nbsp;' . (xtc_not_null(ENTRY_COUNTRY_TEXT) ? '<span class="inputRequirement">' . ENTRY_COUNTRY_TEXT . '</span>': '')), $selected));

if ((isset($_GET['edit']) && ($_SESSION['customer_default_address_id'] != $_GET['edit'])) || (isset($_GET['edit']) == false) ) {
  $module_smarty->assign('new','1');
  $module_smarty->assign('CHECKBOX_PRIMARY',xtc_draw_checkbox_field('primary', 'on', false, 'id="primary"'));
}

$module_smarty->assign('language', $_SESSION['language']);
$main_content=$module_smarty->fetch(CURRENT_TEMPLATE . '/module/address_book_details.html');
$smarty->assign('MODULE_address_book_details',$main_content);
?>