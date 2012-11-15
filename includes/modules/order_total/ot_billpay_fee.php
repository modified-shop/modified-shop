<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   xtcModified - community made shopping
   http://www.xtc-modified.org

   Copyright (c) 2011 xtcModified
   -----------------------------------------------------------------------------------------
   based on:
   Copyright (c) 2010 Billpay GmbH

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  class ot_billpay_fee {
    var $title, $output;
    var $_paymentIdentifier = 'BILLPAY';

    function ot_billpay_fee() {
      global $xtPrice;

      $this->code = 'ot_'.strtolower($this->_paymentIdentifier).'_fee';
      $this->title = defined('MODULE_ORDER_TOTAL_'.$this->_paymentIdentifier.'_FEE_TITLE') ? constant('MODULE_ORDER_TOTAL_'.$this->_paymentIdentifier.'_FEE_TITLE') : '';
      $this->description = defined('MODULE_ORDER_TOTAL_'.$this->_paymentIdentifier.'_FEE_DESCRIPTION') ? constant('MODULE_ORDER_TOTAL_'.$this->_paymentIdentifier.'_FEE_DESCRIPTION') : '';
      $this->type = defined('MODULE_ORDER_TOTAL_'.$this->_paymentIdentifier.'_FEE_TYPE') ? constant('MODULE_ORDER_TOTAL_'.$this->_paymentIdentifier.'_FEE_TYPE') : '';
      $this->enabled = defined('MODULE_ORDER_TOTAL_'.$this->_paymentIdentifier.'_FEE_STATUS') ? ((constant('MODULE_ORDER_TOTAL_'.$this->_paymentIdentifier.'_FEE_STATUS') == 'true') ? true : false) : false;
      $this->sort_order = defined('MODULE_ORDER_TOTAL_'.$this->_paymentIdentifier.'_FEE_SORT_ORDER') ? constant('MODULE_ORDER_TOTAL_'.$this->_paymentIdentifier.'_FEE_SORT_ORDER') : '';
      $this->output = array();
    }
    
    /**
     * 
     * checks the POST and GET values for onepage-checkout specific variables
     * expects 0 if called from invoice b2c module or 1 from invoice b2b module
     */
    function _checkFeeGroup($group)
    {
    	if (is_array($_GET['xajaxargs']) && count($_GET['xajaxargs']) > 0) { // xajax one page checkout
			if (strpos($_GET['xajaxargs'][0], 'b2bflag='.$group)) {
				$_SESSION['billpay_preselect'] = 'b2c';
				return true;
			}
			else {
				return false;
			}
		}
		if (is_array($_POST['xajaxargs']) && count($_POST['xajaxargs']) > 0) { // xajax one page checkout
			if (strpos($_POST['xajaxargs'][0], 'b2bflag='.$group)) {
				$_SESSION['billpay_preselect'] = 'b2c';
				return true;
			}
			else {
				return false;
			}
		}
		return 2;
    }

	function addFee() {
		if ($_SESSION['payment'] == 'billpay' || $_POST['payment'] == 'billpay') {
			if($this->_checkFeeGroup(0)==2)
				return $_SESSION['billpay_preselect'] == 'b2c';
			else
				return $this->_checkFeeGroup(0);
		}
		return false;
	}

    function process() {
		if ($this->addFee()) {
			return $this->_process();
		}
		else {
			return false;
		}
    }

	function _process() {
	  global $order, $xtPrice, $billpay_cost, $billpay_country, $shipping;
				  
      if(constant('MODULE_ORDER_TOTAL_'.$this->_paymentIdentifier.'_FEE_STATUS') == 'true')
      {
        $value = $this->calculateFee();
        $tax_value = 0;

        if($_SESSION['customers_status']['customers_status_show_price_tax'] == 1)
        {
        	$tax_value = $this->calculateTax();
             $tax = xtc_get_tax_rate(constant('MODULE_ORDER_TOTAL_'.$this->_paymentIdentifier.'_FEE_TAX_CLASS'), $order->delivery['country']['id'], $order->delivery['zone_id']);
                $tax_description = xtc_get_tax_description(constant('MODULE_ORDER_TOTAL_'.$this->_paymentIdentifier.'_FEE_TAX_CLASS'), $order->delivery['country']['id'], $order->delivery['zone_id']);
        	$order->info['tax_groups'][TAX_ADD_TAX . "$tax_description"] += $this->calculateTax();
        }
        else if($_SESSION['customers_status']['customers_status_show_price_tax'] == 0
        		&& $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1)
        {
             $tax = xtc_get_tax_rate(constant('MODULE_ORDER_TOTAL_'.$this->_paymentIdentifier.'_FEE_TAX_CLASS'), $order->delivery['country']['id'], $order->delivery['zone_id']);
                $tax_description = xtc_get_tax_description(constant('MODULE_ORDER_TOTAL_'.$this->_paymentIdentifier.'_FEE_TAX_CLASS'), $order->delivery['country']['id'], $order->delivery['zone_id']);
        	$order->info['tax_groups'][TAX_NO_TAX . "$tax_description"] += $this->calculateTax();
        	$order->info['subtotal'] += $value;
        	$tax_value = $this->calculateTax();
        }
        if($value > 0)
        {
        	$value += $tax_value;
        	$order->info['total'] += $value;
			$this->output[] = array('title' => $this->title . ':',
                                    'text' => $xtPrice->xtcFormat($value,true),
                                    'value' => $value);
        }
        else
        {
        	return false;
        }
      }
	}

    function display() {
    	$value = $this->calculateFee();
       	if($_SESSION['customers_status']['customers_status_show_price_tax'] == 1
  			|| $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1)
  		{
       		$value += $this->calculateTax();
  		}
  		return $value;
    }

    function display_formated()
    {
   		global  $xtPrice, $order;

        if($this->type == "prozentual")
    	{
	 		$arr = explode(";", constant('MODULE_ORDER_TOTAL_'.$this->_paymentIdentifier.'_FEE_PERCENT'));
        	return ' '.$this->calculateFeeByCountry($arr, $order->billing['country']['iso_code_2']).'% '.constant('MODULE_ORDER_TOTAL_'.$this->_paymentIdentifier.'_FEE_FROM_TOTAL');
    	}
    	$value = $this->display();
       	return $xtPrice->xtcFormat($value, true);
    }

    function calculateFeeByCountry($arr, $country)
    {
        foreach($arr as $val)
        {
        	$element = explode(":", $val);
        	if($element[0] == $country)
        	{
				$value = $element[1];
				return $value;
        	}
        }
    }

    function calculateFee($total = NULL)
    {
    	global $order;

    	if(!isset($total))
    	{
    		$total = $order->info['total'];
    	}
    	
    	$value = 0;
        if($this->type == "fest")
        {
        	$arr = explode(";", constant('MODULE_ORDER_TOTAL_'.$this->_paymentIdentifier.'_FEE_VALUE'));
        	$value = $this->calculateFeeByCountry($arr, $order->billing['country']['iso_code_2']);
        }
        else if($this->type == "prozentual")
        {
        	$arr = explode(";", constant('MODULE_ORDER_TOTAL_'.$this->_paymentIdentifier.'_FEE_PERCENT'));
        	$value = $this->calculateFeeByCountry($arr, $order->billing['country']['iso_code_2']);
        	$value = $total / 100 * $value;
        	$value = round($value, 2);
        }
//        else if($this->type == "gestaffelt")
//        {
//        	$arr = explode(";", constant('MODULE_ORDER_TOTAL_'.$this->_paymentIdentifier.'_FEE_GRADUATE'));
//        	foreach($arr as $val)
//        	{
//        		$element = explode("=", $val);
//        		if($total <= $element[0])
//        		{
//        			$value = $element[1];
//        			break;
//        		}
//        		$value = $element[1];
//        	}
//        }
        return $value;
    }

    function calculateTax($total = NULL) {
    	global $order;

		// include needed functions
		require_once(DIR_FS_INC . 'xtc_calculate_tax.inc.php');
    	
        if(!isset($total)) {
    		$total = $order->info['total'];
    	}
    	$value = 0;
    	$billpay_tax = xtc_get_tax_rate(constant('MODULE_ORDER_TOTAL_'.$this->_paymentIdentifier.'_FEE_TAX_CLASS'), $order->delivery['country']['id'], $order->delivery['zone_id']);
    	$value = xtc_calculate_tax($this->calculateFee($total), $billpay_tax);
        $value = round($value, 2);
    	return $value;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_".$this->_paymentIdentifier."_FEE_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function keys() {
      return array('MODULE_ORDER_TOTAL_'.$this->_paymentIdentifier.'_FEE_STATUS',
      				'MODULE_ORDER_TOTAL_'.$this->_paymentIdentifier.'_FEE_SORT_ORDER',
      				'MODULE_ORDER_TOTAL_'.$this->_paymentIdentifier.'_FEE_TYPE',
      				'MODULE_ORDER_TOTAL_'.$this->_paymentIdentifier.'_FEE_PERCENT',
      				'MODULE_ORDER_TOTAL_'.$this->_paymentIdentifier.'_FEE_VALUE',
      				//'MODULE_ORDER_TOTAL_'.$this->_paymentIdentifier.'_FEE_GRADUATE',
      				'MODULE_ORDER_TOTAL_'.$this->_paymentIdentifier.'_FEE_TAX_CLASS');
    }

    function install() {
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_ORDER_TOTAL_".$this->_paymentIdentifier."_FEE_STATUS', 'true', '6', '0', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
	  //xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_ORDER_TOTAL_".$this->_paymentIdentifier."_FEE_TYPE', 'fest', '6', '0', 'xtc_cfg_select_option(array(\'fest\', \'prozentual\', \'gestaffelt\'), ', now())");
	  xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_ORDER_TOTAL_".$this->_paymentIdentifier."_FEE_TYPE', 'fest', '6', '0', 'xtc_cfg_select_option(array(\'fest\', \'prozentual\'), ', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_ORDER_TOTAL_".$this->_paymentIdentifier."_FEE_SORT_ORDER', '90', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_ORDER_TOTAL_".$this->_paymentIdentifier."_FEE_PERCENT', '', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_ORDER_TOTAL_".$this->_paymentIdentifier."_FEE_VALUE', '', '6', '0', now())");
      //xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_ORDER_TOTAL_".$this->_paymentIdentifier."_FEE_GRADUATE', '', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_ORDER_TOTAL_".$this->_paymentIdentifier."_FEE_TAX_CLASS', '0', '6', '0', 'xtc_get_tax_class_title', 'xtc_cfg_pull_down_tax_classes(', now())");
    }

    function remove() {
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }
  }
?>