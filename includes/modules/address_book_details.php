<?php
/* -----------------------------------------------------------------------------------------
   $Id: address_book_details.php 1239 2005-09-24 20:09:56Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(address_book_details.php,v 1.9 2003/05/22); www.oscommerce.com 
   (c) 2003	 nextcommerce (address_book_details.php,v 1.9 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------/-----*/

  // include needed functions
  $module_smarty=new Smarty;
  $module_smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');
  
  // include needed functions
  require_once (DIR_FS_INC.'xtc_get_zone_name.inc.php');
  require_once (DIR_FS_INC.'xtc_get_country_list.inc.php');
  require_once (DIR_FS_INC.'get_customers_gender.inc.php');
  require_once (DIR_FS_INC.'check_country_required_zones.inc.php');

  if (!isset($process)) $process = false;

  if (isset($_POST['country'])) {
    $selected = $_POST['country'];
  } elseif (isset($entry['entry_country_id'])) {
    $selected = $entry['entry_country_id'];
  } else {
    $selected = STORE_COUNTRY;
  }
  
  $required_zones = check_country_required_zones($selected);

  if (ACCOUNT_GENDER == 'true') {
    $male = ($entry['entry_gender'] == 'm') ? true : false;
    $female = ($entry['entry_gender'] == 'f') ? true : false;
    $module_smarty->assign('gender','1');
    $module_smarty->assign('INPUT_MALE',xtc_draw_radio_field(array('name'=>'gender','suffix'=>MALE.'&nbsp;'), 'm',$male));
    $module_smarty->assign('INPUT_FEMALE',xtc_draw_radio_field(array('name'=>'gender','suffix'=>FEMALE.'&nbsp;','text'=>(xtc_not_null(ENTRY_GENDER_TEXT) ? '<span class="inputRequirement">&nbsp;' . ENTRY_GENDER_TEXT . '</span>': '')), 'f',$female));
    // Gender Dropdown
    $module_smarty->assign('INPUT_GENDER', xtc_draw_pull_down_menuNote(array ('name' => 'gender', 'text' => '&nbsp;'. (xtc_not_null(ENTRY_GENDER_TEXT) ? '<span class="inputRequirement">'.ENTRY_GENDER_TEXT.'</span>' : '')), get_customers_gender(), $entry['entry_gender']));
  }

  $module_smarty->assign('INPUT_FIRSTNAME',xtc_draw_input_fieldNote(array('name'=>'firstname','text'=>'&nbsp;' . (xtc_not_null(ENTRY_FIRST_NAME_TEXT) ? '<span class="inputRequirement">' . ENTRY_FIRST_NAME_TEXT . '</span>': '')),$entry['entry_firstname']));
  $module_smarty->assign('INPUT_LASTNAME',xtc_draw_input_fieldNote(array('name'=>'lastname','text'=>'&nbsp;' . (xtc_not_null(ENTRY_LAST_NAME_TEXT) ? '<span class="inputRequirement">' . ENTRY_LAST_NAME_TEXT . '</span>': '')),$entry['entry_lastname']));

  if (ACCOUNT_COMPANY == 'true') {
    $module_smarty->assign('company','1');
    $module_smarty->assign('INPUT_COMPANY',xtc_draw_input_fieldNote(array('name'=>'company','text'=>'&nbsp;' . (xtc_not_null(ENTRY_COMPANY_TEXT) ? '<span class="inputRequirement">' . ENTRY_COMPANY_TEXT . '</span>': '')), $entry['entry_company']));
  }

  $module_smarty->assign('INPUT_STREET',xtc_draw_input_fieldNote(array('name'=>'street_address','text'=>'&nbsp;' . (xtc_not_null(ENTRY_STREET_ADDRESS_TEXT) ? '<span class="inputRequirement">' . ENTRY_STREET_ADDRESS_TEXT . '</span>': '')), $entry['entry_street_address']));

  if (ACCOUNT_SUBURB == 'true') {
    $module_smarty->assign('suburb','1');
    $module_smarty->assign('INPUT_SUBURB',xtc_draw_input_fieldNote(array('name'=>'suburb','text'=>'&nbsp;' . (xtc_not_null(ENTRY_SUBURB_TEXT) ? '<span class="inputRequirement">' . ENTRY_SUBURB_TEXT . '</span>': '')), $entry['entry_suburb']));
  }
  $module_smarty->assign('INPUT_CODE',xtc_draw_input_fieldNote(array('name'=>'postcode','text'=>'&nbsp;' . (xtc_not_null(ENTRY_POST_CODE_TEXT) ? '<span class="inputRequirement">' . ENTRY_POST_CODE_TEXT . '</span>': '')), $entry['entry_postcode']));
  $module_smarty->assign('INPUT_CITY',xtc_draw_input_fieldNote(array('name'=>'city','text'=>'&nbsp;' . (xtc_not_null(ENTRY_CITY_TEXT) ? '<span class="inputRequirement">' . ENTRY_CITY_TEXT . '</span>': '')), $entry['entry_city']));

  if (ACCOUNT_STATE == 'true') {
    $module_smarty->assign('state','1');
    $smarty->assign('display_state', '');
    if ($process == true) {
      if ($entry_state_has_zones == true) {
        $zones_array = array();
        $zones_query = xtc_db_query("select zone_name from " . TABLE_ZONES . " where zone_country_id = '" . xtc_db_input($country) . "' order by zone_name");
        while ($zones_values = xtc_db_fetch_array($zones_query)) {
          $zones_array[] = array('id' => $zones_values['zone_name'], 'text' => $zones_values['zone_name']);
        }
        $state_input= xtc_draw_pull_down_menuNote(array('name'=>'state','text'=>'&nbsp;' .(xtc_not_null(ENTRY_STATE_TEXT) ? '<span class="inputRequirement">' . ENTRY_STATE_TEXT . '</span>': '')), $zones_array);
      } else {
        $state_input= xtc_draw_input_fieldNote(array('name'=>'state','text'=>'&nbsp;' .(xtc_not_null(ENTRY_STATE_TEXT) ? '<span class="inputRequirement">' . ENTRY_STATE_TEXT . '</span>': '')));
        if (!$required_zones) {
          $state_input = '<input type="hidden" value="0" name="state">';
          $smarty->assign('display_state', ' style="display:none"');        
        }
      }
    } else {
      $state_input= xtc_draw_input_fieldNote(array('name'=>'state','text'=>'&nbsp;' .(xtc_not_null(ENTRY_STATE_TEXT) ? '<span class="inputRequirement">' . ENTRY_STATE_TEXT . '</span>': '')), xtc_get_zone_name($entry['entry_country_id'], $entry['entry_zone_id'], $entry['entry_state']));
      if (!$required_zones) {
        $state_input = '<input type="hidden" value="0" name="state">';
        $smarty->assign('display_state', ' style="display:none"');        
      }
      $state_input.= xtc_draw_hidden_field('state_zone_id', $entry['entry_zone_id']);
    }
    $module_smarty->assign('INPUT_STATE',$state_input);
  } else {
    $smarty->assign('state', '0');
  }

  $module_smarty->assign('SELECT_COUNTRY',xtc_get_country_list(array('name'=>'country','text'=>'&nbsp;' . (xtc_not_null(ENTRY_COUNTRY_TEXT) ? '<span class="inputRequirement">' . ENTRY_COUNTRY_TEXT . '</span>': '')), $selected));

  if ((isset($_GET['edit']) && ($_SESSION['customer_default_address_id'] != $_GET['edit'])) || (isset($_GET['edit']) == false) ) {
    $module_smarty->assign('new','1');
    $module_smarty->assign('CHECKBOX_PRIMARY',xtc_draw_checkbox_field('primary', 'on', false, 'id="primary"'));
  }

  $module_smarty->assign('language', $_SESSION['language']);
  $module_smarty->caching = 0;
  $main_content=$module_smarty->fetch(CURRENT_TEMPLATE . '/module/address_book_details.html');
  $smarty->assign('MODULE_address_book_details',$main_content);
?>
