<?php
/* -----------------------------------------------------------------------------------------
   $Id: freeamount.php 1306 2005-10-14 10:32:31Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(freeamount.php,v 1.01 2002/01/24); www.oscommerce.com 
   (c) 2003	 nextcommerce (freeamount.php,v 1.12 2003/08/24); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/


  class freeamount {
    var $code, $title, $description, $icon, $enabled;


    function freeamount() {
      $this->code = 'freeamount';
      $this->title = MODULE_SHIPPING_FREEAMOUNT_TEXT_TITLE;
      $this->description = MODULE_SHIPPING_FREEAMOUNT_TEXT_DESCRIPTION;
      $this->icon ='';   // change $this->icon =  DIR_WS_ICONS . 'shipping_ups.gif'; to some freeshipping icon
      $this->sort_order = MODULE_SHIPPING_FREEAMOUNT_SORT_ORDER;
      $this->enabled = ((MODULE_SHIPPING_FREEAMOUNT_STATUS == 'True') ? true : false);
    }

    function quote($method = '') {
    	global $xtPrice;
	
	  if (( $xtPrice->xtcRemoveCurr($_SESSION['cart']->show_total()) < MODULE_SHIPPING_FREEAMOUNT_AMOUNT ) && MODULE_SHIPPING_FREEAMOUNT_DISPLAY == 'False')
	  return;

      $this->quotes = array('id' => $this->code,
                            'module' => MODULE_SHIPPING_FREEAMOUNT_TEXT_TITLE);

      if ( $xtPrice->xtcRemoveCurr($_SESSION['cart']->show_total()) < MODULE_SHIPPING_FREEAMOUNT_AMOUNT )
        $this->quotes['error'] = sprintf(MODULE_SHIPPING_FREEAMOUNT_TEXT_WAY,$xtPrice->xtcFormat(MODULE_SHIPPING_FREEAMOUNT_AMOUNT,true,0,true));
      else
 	$this->quotes['methods'] = array(array('id'    => $this->code,
                                               'title' => sprintf(MODULE_SHIPPING_FREEAMOUNT_TEXT_WAY,$xtPrice->xtcFormat(MODULE_SHIPPING_FREEAMOUNT_AMOUNT,true,0,true)),
                                               'cost'  => 0));

      if (xtc_not_null($this->icon)) $this->quotes['icon'] = xtc_image($this->icon, $this->title);

      return $this->quotes;
    }

    function check() {
      $check = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_FREEAMOUNT_STATUS'");
      $check = xtc_db_num_rows($check);

      return $check;
    }

    function install() {
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SHIPPING_FREEAMOUNT_STATUS', 'True', '6', '7', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_FREEAMOUNT_ALLOWED', '', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SHIPPING_FREEAMOUNT_DISPLAY', 'True', '6', '7', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_FREEAMOUNT_AMOUNT', '50.00', '6', '8', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_FREEAMOUNT_SORT_ORDER', '0', '6', '4', now())");
    }

    function remove() {
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_SHIPPING_FREEAMOUNT_STATUS','MODULE_SHIPPING_FREEAMOUNT_ALLOWED', 'MODULE_SHIPPING_FREEAMOUNT_DISPLAY', 'MODULE_SHIPPING_FREEAMOUNT_AMOUNT','MODULE_SHIPPING_FREEAMOUNT_SORT_ORDER');
    }
  }
?>
