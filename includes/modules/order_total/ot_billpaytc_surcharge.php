<?php
/* -----------------------------------------------------------------------------------------
   $Id: ot_billpaytc_surcharge.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   Copyright (c) 2012 Billpay GmbH

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

if(!class_exists('ot_billpaytc_surcharge')) {
	class ot_billpaytc_surcharge {
		var $title, $output;
		var $_moduleIdentifier = 'BILLPAYTC_SURCHARGE';
	
		function ot_billpaytc_surcharge() {
			global $xtPrice;
	
			$this->code = 'ot_'.strtolower($this->_moduleIdentifier);
			$this->title = defined('MODULE_ORDER_TOTAL_'.$this->_moduleIdentifier.'_TITLE') ? constant('MODULE_ORDER_TOTAL_'.$this->_moduleIdentifier.'_TITLE') : '';
			$this->description = defined('MODULE_ORDER_TOTAL_'.$this->_moduleIdentifier.'_DESCRIPTION') ? constant('MODULE_ORDER_TOTAL_'.$this->_moduleIdentifier.'_DESCRIPTION') : '';
			$this->enabled = defined('MODULE_ORDER_TOTAL_'.$this->_moduleIdentifier.'_STATUS') ? ((constant('MODULE_ORDER_TOTAL_'.$this->_moduleIdentifier.'_STATUS') == 'true') ? true : false) : false;
			$this->sort_order = defined('MODULE_ORDER_TOTAL_'.$this->_moduleIdentifier.'_SORT_ORDER') ? constant('MODULE_ORDER_TOTAL_'.$this->_moduleIdentifier.'_SORT_ORDER') : '';
			$this->output = array();
		}
	
		function process() {
			if ($this->isBillpayTransactionCreditPayment()) {
				return $this->_process();
			}
			else {
				return false;
			}
		}
	
		function _process() {
			global $order, $xtPrice, $billpay_cost, $billpay_country, $shipping;
	
			$surcharge 		= round($_SESSION['bp_rate_result']['rateplan'][$_SESSION['bp_rate_result']['numberRates']]['calculation']['surcharge'] / 100, PRICE_PRECISION);
			$transactionFee = round($_SESSION['bp_rate_result']['rateplan'][$_SESSION['bp_rate_result']['numberRates']]['calculation']['fee'] / 100, PRICE_PRECISION);
			$feeTax			= round($this->calculateTax($_SESSION['bp_rate_result']['rateplan'][$_SESSION['bp_rate_result']['numberRates']]['calculation']['fee']) / 100, PRICE_PRECISION); 
			$total          = round($_SESSION['bp_rate_result']['rateplan'][$_SESSION['bp_rate_result']['numberRates']]['calculation']['total'] / 100, PRICE_PRECISION);
			 
			if ($surcharge > 0) {
				$this->output[] = array(
                    'title'      => '<strong>' . MODULE_ORDER_TOTAL_BILLPAYTC_SURCHARGE . ':</strong>',
                    'text'       => '<strong>' . $xtPrice->xtcFormat($surcharge, true) . '</strong>',
                    'value'      => 0,
                    'sort_order' => $this->sort_order + 10,
                );
			}
				
			if ($transactionFee > 0) {
				$this->output[] = array(
                    'title'      => '<strong>' . MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_TRANSACTION_FEE
                        . ' (' . MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_TRANSACTION_FEE_TAX1
                        . ' ' . $xtPrice->xtcFormat($feeTax, true) . ' '
                        . MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_TRANSACTION_FEE_TAX2 . ')'
                        . ':</strong>',
                    'text'       => '<strong>' . $xtPrice->xtcFormat($transactionFee, true) . '</strong>',
                    'value'      => 0,
                    'sort_order' => $this->sort_order + 20,
                );
			}
			
			if ($total > 0) {
				$this->output[] = array(
                    'title'      => '<strong>' . MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_TOTAL . ':</strong>',
                    'text'       => '<strong>' . $xtPrice->xtcFormat($total, true) . '</strong>',
                    'value'      => 0,
                    'sort_order' => $this->sort_order + 30,
                );
			}
		}
	
	    function calculateTax($baseFee) {
	    	$value = 0;
	    	$billpayTax = $this->getTaxRate();
	    	// fee includes tax already: 
	    	$value = $baseFee * $billpayTax / (100 + $billpayTax);
	        $value = round($value, 2);
	    	return $value;
	    }
	
	    function getTaxRate() {
	    	return xtc_get_tax_rate(constant('MODULE_ORDER_TOTAL_'.$this->_moduleIdentifier.'_TAX_CLASS'),
	    											 $_SESSION['customer_country_id'], $_SESSION['customer_zone_id']);
	    }
		
		function check() {
			if (!isset($this->_check)) {
				$check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_" . $this->_moduleIdentifier."_STATUS'");
				$this->_check = xtc_db_num_rows($check_query);
			}
			return $this->_check;
		}
		
		function isBillpayTransactionCreditPayment() {
			return $_SESSION['payment'] == 'billpaytransactioncredit' || $_POST['payment'] == 'billpaytransactioncredit';
		}
	
		function keys() {
			return array('MODULE_ORDER_TOTAL_' . $this->_moduleIdentifier . '_STATUS',
	      			   'MODULE_ORDER_TOTAL_' . $this->_moduleIdentifier . '_SORT_ORDER',
	      			   'MODULE_ORDER_TOTAL_' . $this->_moduleIdentifier . '_TAX_CLASS');
		}
	
		function install() {
			xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_ORDER_TOTAL_" . $this->_moduleIdentifier . "_STATUS', 'true', '6', '0', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
			xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_ORDER_TOTAL_" . $this->_moduleIdentifier . "_SORT_ORDER', '1000', '6', '0', now())");
			xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_ORDER_TOTAL_" . $this->_moduleIdentifier . "_TAX_CLASS', '0', '6', '0', 'xtc_get_tax_class_title', 'xtc_cfg_pull_down_tax_classes(', now())");
			
			// add module to list of currently installed modules to allow the installation via call to this method from payment module
			$moduleFileName = __CLASS__ . '.php';
			
			$confKey = 'MODULE_ORDER_TOTAL_INSTALLED';
			$key_value_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = '" . $confKey . "' ");
			$key_value = xtc_db_fetch_array($key_value_query);
			
			$value = $key_value['configuration_value'];
			
			if  (!strstr($value, $moduleFileName)) {
				$value .= ';' . $moduleFileName;
				
				xtc_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . $value . "', last_modified = now() where configuration_key = '" . $confKey . "'");
			}
		}
	
		function remove() {
			xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
		}
	}
}
?>