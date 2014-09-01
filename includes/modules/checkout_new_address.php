<?php
/* -----------------------------------------------------------------------------------------
   $Id: checkout_new_address.php 1338 2010-09-17 15:50:39Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(checkout_new_address.php,v 1.3 2003/05/19); www.oscommerce.com
   (c) 2003	 nextcommerce (checkout_new_address.php,v 1.8 2003/08/17); www.nextcommerce.org
   (c) 2006 xt:Commerce (checkout_new_address.php 1239 2005-09-24); www.xt-commerce.de

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

$module_smarty = new Smarty;
//BOF - GTB - 2010-08-03 - Security Fix - Base
$module_smarty->assign('tpl_path',DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');
//$module_smarty->assign('tpl_path', 'templates/'.CURRENT_TEMPLATE.'/');
//EOF - GTB - 2010-08-03 - Security Fix - Base
// include needed functions

require_once (DIR_FS_INC.'xtc_get_country_list.inc.php');

if (!isset ($process))
	$process = false;

if (ACCOUNT_GENDER == 'true') {
	$male = (isset($gender) && $gender == 'm') ? true : false;
	$female = (isset($gender) && $gender == 'f') ? true : false;
	$module_smarty->assign('gender', '1');
	$module_smarty->assign('INPUT_MALE', xtc_draw_radio_field(array ('name' => 'gender', 'suffix' => MALE), 'm'));
	$module_smarty->assign('INPUT_FEMALE', xtc_draw_radio_field(array ('name' => 'gender', 'suffix' => FEMALE, 'text' => (xtc_not_null(ENTRY_GENDER_TEXT) ? '<span class="inputRequirement">'.ENTRY_GENDER_TEXT.'</span>' : '')), 'f'));
}
$module_smarty->assign('INPUT_FIRSTNAME', xtc_draw_input_fieldNote(array ('name' => 'firstname', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_FIRST_NAME_TEXT) ? '<span class="inputRequirement">'.ENTRY_FIRST_NAME_TEXT.'</span>' : ''))));
$module_smarty->assign('INPUT_LASTNAME', xtc_draw_input_fieldNote(array ('name' => 'lastname', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_LAST_NAME_TEXT) ? '<span class="inputRequirement">'.ENTRY_LAST_NAME_TEXT.'</span>' : ''))));

if (ACCOUNT_COMPANY == 'true') {
	$module_smarty->assign('company', '1');
	$module_smarty->assign('INPUT_COMPANY', xtc_draw_input_fieldNote(array ('name' => 'company', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_COMPANY_TEXT) ? '<span class="inputRequirement">'.ENTRY_COMPANY_TEXT.'</span>' : ''))));
} else {
	$module_smarty->assign('company', '0');
}
$module_smarty->assign('INPUT_STREET', xtc_draw_input_fieldNote(array ('name' => 'street_address', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_STREET_ADDRESS_TEXT) ? '<span class="inputRequirement">'.ENTRY_STREET_ADDRESS_TEXT.'</span>' : ''))));

if (ACCOUNT_SUBURB == 'true') {
	$module_smarty->assign('suburb', '1');
	$module_smarty->assign('INPUT_SUBURB', xtc_draw_input_fieldNote(array ('name' => 'suburb', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_SUBURB_TEXT) ? '<span class="inputRequirement">'.ENTRY_SUBURB_TEXT.'</span>' : ''))));

}
$module_smarty->assign('INPUT_CODE', xtc_draw_input_fieldNote(array ('name' => 'postcode', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_POST_CODE_TEXT) ? '<span class="inputRequirement">'.ENTRY_POST_CODE_TEXT.'</span>' : ''))));
$module_smarty->assign('INPUT_CITY', xtc_draw_input_fieldNote(array ('name' => 'city', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_CITY_TEXT) ? '<span class="inputRequirement">'.ENTRY_CITY_TEXT.'</span>' : ''))));

if (ACCOUNT_STATE == 'true') {
	$module_smarty->assign('state', '1');

	if ($process == true) {
		if ($entry_state_has_zones == true) {
			$zones_array = array ();
			$zones_query = xtc_db_query("SELECT zone_id, zone_name FROM ".TABLE_ZONES." WHERE zone_country_id = '".(int)$country."' ORDER BY zone_name");
      while ($zones_values = xtc_db_fetch_array($zones_query)) {
        $zones_array[] = array (
          'id' => $zones_values['zone_id'],
          'text' => $zones_values['zone_name']
        );
      }
			$entry_state = xtc_draw_pull_down_menuNote(array ('name' => 'state', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_STATE_TEXT) ? '<span class="inputRequirement">'.ENTRY_STATE_TEXT.'</span>' : '')), $zones_array);
		} else {
			$entry_state = xtc_draw_input_fieldNote(array ('name' => 'state', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_STATE_TEXT) ? '<span class="inputRequirement">'.ENTRY_STATE_TEXT.'</span>' : '')));
		}
	} else {
		$entry_state = xtc_draw_input_fieldNote(array ('name' => 'state', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_STATE_TEXT) ? '<span class="inputRequirement">'.ENTRY_STATE_TEXT.'</span>' : '')));
	}

	$module_smarty->assign('INPUT_STATE', $entry_state);
} else {
	$module_smarty->assign('state', '0');
}

if (isset($_POST['country']) && $_POST['country']) {
  $selected = $_POST['country'];
} else {
  $selected = STORE_COUNTRY;
}

$module_smarty->assign('SELECT_COUNTRY', xtc_get_country_list('country', $selected).'&nbsp;'. (xtc_not_null(ENTRY_COUNTRY_TEXT) ? '<span class="inputRequirement">'.ENTRY_COUNTRY_TEXT.'</span>' : ''));

$module_smarty->assign('language', $_SESSION['language']);

$module_smarty->caching = 0;
$module = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/checkout_new_address.html');

$smarty->assign('MODULE_new_address', $module);
?>