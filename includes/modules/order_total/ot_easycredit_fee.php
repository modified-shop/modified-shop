<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


  class ot_easycredit_fee {

    var $code;
    var $title;
    var $total_title;
    var $description;
    var $enabled;
    var $sort_order;
    var $output;
    var $_check;

    function __construct() {    	
      $this->code = 'ot_easycredit_fee';
      $this->title = MODULE_ORDER_TOTAL_EASYCREDIT_FEE_TITLE;
      $this->total_title = MODULE_ORDER_TOTAL_EASYCREDIT_FEE_TOTAL_TITLE;
      $this->description = MODULE_ORDER_TOTAL_EASYCREDIT_FEE_DESCRIPTION;
      $this->enabled = ((defined('MODULE_ORDER_TOTAL_EASYCREDIT_FEE_STATUS') && MODULE_ORDER_TOTAL_EASYCREDIT_FEE_STATUS == 'true') ? true : false);
      $this->sort_order = defined('MODULE_ORDER_TOTAL_EASYCREDIT_FEE_SORT_ORDER') ? MODULE_ORDER_TOTAL_EASYCREDIT_FEE_SORT_ORDER : '';
      
      $this->output = array();
    }

    function process() {
      global $order, $xtPrice, $PHP_SELF;
      
     if (in_array(basename($PHP_SELF), array(FILENAME_CHECKOUT_CONFIRMATION, FILENAME_CHECKOUT_PROCESS))
          && isset($GLOBALS['easycredit']) 
          && is_object($GLOBALS['easycredit'])
          && $GLOBALS['easycredit']->ecProcess->getProcessData()->getStatus() == 'ACCEPTED'
          )
      {
        $easycredit = $GLOBALS['easycredit']->ecProcess->getFinancingDetails()->getInstallmentPlan();
                
        $total_cost = $easycredit->getAmount();
        $total_interest = $easycredit->getInterestRate()->getAccruingInterest();
      
        $this->output[] = array(
          'title' => '<br/>'.$this->title . ':',
          'text'  => '<br/>'.$xtPrice->xtcFormat($total_interest, true),
          'value' => $total_interest,
          'sort_order' => $this->sort_order,
        );

        $this->output[] = array(
          'title' => '<b>'.$this->total_title . ':</b>',
          'text'  => '<b>'.$xtPrice->xtcFormat($total_cost, true).'</b>',
          'value' => $total_cost,
          'sort_order' => $this->sort_order + 1,
        );
      }
    }

    function check() {
      if (!isset($this->_check)) {
        if (defined('MODULE_ORDER_TOTAL_EASYCREDIT_FEE_STATUS')) {
          $this->_check = true;
        } else {
          $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_EASYCREDIT_FEE_STATUS'");
          $this->_check = xtc_db_num_rows($check_query);
        }
      }
      return $this->_check;
    }

    function keys() {
      return array(
        'MODULE_ORDER_TOTAL_EASYCREDIT_FEE_STATUS',
        'MODULE_ORDER_TOTAL_EASYCREDIT_FEE_SORT_ORDER'
      );
    }

    function install() {
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_ORDER_TOTAL_EASYCREDIT_FEE_STATUS', 'true','6', '1','xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_ORDER_TOTAL_EASYCREDIT_FEE_SORT_ORDER', '999', '6', '2', now())");      
    }

    function remove() {
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }
  }
