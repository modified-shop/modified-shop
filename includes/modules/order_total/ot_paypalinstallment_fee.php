<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(ot_subtotal.php,v 1.7 2003/02/13); www.oscommerce.com
   (c) 2003	 nextcommerce (ot_discount.php,v 1.11 2003/08/24); www.nextcommerce.org
   (c) 2006 xt:Commerce (ot_discount.php 1277 2005-10-01 ); www.xt-commerce.de

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  class ot_paypalinstallment_fee {
    var $title, $output;

    function __construct() {
    	global $xtPrice;
    	
      $this->code = 'ot_paypalinstallment_fee';
      $this->title = MODULE_ORDER_TOTAL_PAYPALINSTALLMENT_FEE_TITLE;
      $this->total_title = MODULE_ORDER_TOTAL_PAYPALINSTALLMENT_FEE_TOTAL_TITLE;
      $this->description = MODULE_ORDER_TOTAL_PAYPALINSTALLMENT_FEE_DESCRIPTION;
      $this->enabled = ((defined('MODULE_ORDER_TOTAL_PAYPALINSTALLMENT_FEE_STATUS') && MODULE_ORDER_TOTAL_PAYPALINSTALLMENT_FEE_STATUS == 'true') ? true : false);
      $this->sort_order = ((defined('MODULE_ORDER_TOTAL_PAYPALINSTALLMENT_FEE_SORT_ORDER')) ? MODULE_ORDER_TOTAL_PAYPALINSTALLMENT_FEE_SORT_ORDER : '');
      
      $this->output = array();
    }

    function process() {
      global $order, $xtPrice;

      if (isset($_SESSION['paypal'])
          && isset($_SESSION['paypal']['installment'])
          )
      {
        $this->output[] = array(
            'title' => '<br/>'.$this->title . ':',
            'text'  => '<br/>'.$xtPrice->xtcFormat($_SESSION['paypal']['installment']['total_interest'], true),
            'value' => $_SESSION['paypal']['installment']['total_interest'],
            'sort_order' => $this->sort_order,
          );

        $this->output[] = array(
            'title' => '<b>'.$this->total_title . ':</b>',
            'text'  => '<b>'.$xtPrice->xtcFormat($_SESSION['paypal']['installment']['total_cost'], true).'</b>',
            'value' => $_SESSION['paypal']['installment']['total_cost'],
            'sort_order' => $this->sort_order + 1,
          );

      }
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_PAYPALINSTALLMENT_FEE_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }

      return $this->_check;
    }

    function keys() {
      return array(
        'MODULE_ORDER_TOTAL_PAYPALINSTALLMENT_FEE_STATUS',
        'MODULE_ORDER_TOTAL_PAYPALINSTALLMENT_FEE_SORT_ORDER'
      );
    }

    function install() {
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_ORDER_TOTAL_PAYPALINSTALLMENT_FEE_STATUS', 'true','6', '1','xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_ORDER_TOTAL_PAYPALINSTALLMENT_FEE_SORT_ORDER', '999', '6', '2', now())");      
    }

    function remove() {
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }
  }
?>