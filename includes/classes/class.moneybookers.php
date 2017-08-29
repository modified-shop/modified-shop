<?php
/* -----------------------------------------------------------------------------------------
   $Id: class.moneybookers.php 29 2009-01-19 15:37:52Z mzanier $

   xt:Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2009 xt:Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(moneybookers.php,v 1.00 2003/10/27); www.oscommerce.com

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   Moneybookers v1.0                       Autor:    Gabor Mate  <gabor(at)jamaga.hu>

   Released under the GNU General Public License
   
   // Version History
    * 2.0 xt:Commerce Adaption
    * 2.1 new workflow, tmp orders
    * 2.2 new modules
    * 2.3 updates
    * 2.4 major update, iframe integration
   
   
   ---------------------------------------------------------------------------------------*/


class fcnt_moneybookers {
	var $code, $title, $description, $enabled, $auth_num, $transaction_id,$allowed;
	
	var $version = '2.4';
	var	$tmpOrders = true;
	var	$repost = false;
	var	$debug = false;
	var $form_action_url = 'https://pay.skrill.com';
	var $tmpStatus = _PAYMENT_MONEYBOOKERS_TMP_STATUS_ID;
	
	function __construct(){
		$this->Error = '';
		$this->oID = 0;
		$this->transaction_id = '';
	}
	
	function _setCode($code='CC',$payment_method='ACC') {
		
		$this->module = $code;
		$this->method = $payment_method;
		
		$this->code = 'moneybookers_'.strtolower($code);
		
		if (defined('MODULE_PAYMENT_MONEYBOOKERS_'.strtoupper($code).'_TEXT_TITLE')) {
			$this->title = constant('MODULE_PAYMENT_MONEYBOOKERS_'.strtoupper($code).'_TEXT_TITLE');
			$this->description = constant('MODULE_PAYMENT_MONEYBOOKERS_'.strtoupper($code).'_TEXT_DESCRIPTION');
			$this->info = constant('MODULE_PAYMENT_MONEYBOOKERS_'.strtoupper($code).'_TEXT_INFO');
		}
		
		if (defined('MODULE_PAYMENT_MONEYBOOKERS_'.strtoupper($code).'_STATUS')) {
			$this->sort_order = constant('MODULE_PAYMENT_MONEYBOOKERS_'.strtoupper($code).'_SORT_ORDER');
			$this->enabled = ((constant('MODULE_PAYMENT_MONEYBOOKERS_'.strtoupper($code).'_STATUS') == 'True') ? true : false);
			$this->tmpStatus = constant('_PAYMENT_MONEYBOOKERS_TMP_STATUS_ID');
		}

		if (defined('_VALID_XTC')) {
			$icons = explode(',', $this->images);
			$accepted='';
			foreach ($icons as $key => $val)
				$accepted .= xtc_image('../images/icons/moneybookers/'. $val) . ' ';
			if ($this->allowed!='') $this->title.=' ('.$this->allowed.')';
				$this->title .='<br />'.$accepted;
		}
		
	}
	
	function javascript_validation() {
		return false;
	}
	
	function update_status() {
		global $order;

		if (($this->enabled == true) && ((int) constant('MODULE_PAYMENT_MONEYBOOKERS_'.strtoupper($this->module).'_ZONE') > 0)) {
			$check_flag = false;
			$check_query = xtc_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . constant('MODULE_PAYMENT_MONEYBOOKERS_'.strtoupper($this->module).'_ZONE') . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
			while ($check = xtc_db_fetch_array($check_query)) {
				if ($check['zone_id'] < 1) {
					$check_flag = true;
					break;
				}
				elseif ($check['zone_id'] == $order->billing['zone_id']) {
					$check_flag = true;
					break;
				}
			}

			if ($check_flag == false) {
				$this->enabled = false;
			}
		}
	}
	
	function iframeAction() {
		global $order, $xtPrice;

		$result = xtc_db_query("SELECT code FROM languages WHERE languages_id = '" . (int)$_SESSION['languages_id'] . "'");

		$mbLanguage = strtoupper($_SESSION['language_code']);

		$mbCurrency = $_SESSION['currency'];

		if (!isset($_SESSION['transaction_id']))
			$_SESSION['transaction_id'] = $this->generate_trid();
			
			$this->insert_trid();
		
		if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
			$total = $order->info['total'] + $order->info['tax'];
		} else {
			$total = $order->info['total'];
		}
		if ($_SESSION['currency'] == $mbCurrency) {
			$amount = round($total, $xtPrice->get_decimal_places($mbCurrency));
		} else {
			$amount = round($xtPrice->xtcCalculateCurrEx($total, $mbCurrency), $xtPrice->get_decimal_places($mbCurrency));
		}

//		$process_button_string = 
		
		      $params = array('pay_to_email'=>  _PAYMENT_MONEYBOOKERS_EMAILID,
		'transaction_id'=> $_SESSION['transaction_id'],
		'return_url'=> xtc_href_link(FILENAME_CHECKOUT_PROCESS, 'trid=' . $_SESSION['transaction_id'], 'NONSSL', true, false),
		'cancel_url'=>  xtc_href_link(FILENAME_CHECKOUT_PAYMENT, constant('MODULE_PAYMENT_MONEYBOOKERS_'.strtoupper($this->module).'_ERRORTEXT1') . $this->code . constant('MODULE_PAYMENT_MONEYBOOKERS_'.strtoupper($this->module).'_ERRORTEXT2'), 'SSL', true, false),
		'status_url'=>  xtc_href_link('callback/moneybookers/callback_mb.php'),
		'language'=>  strtoupper($_SESSION['language_code']),
		'pay_from_email'=>  $order->customer['email_address'],
		'amount'=>  $amount,
		'currency'=>  $mbCurrency,
		'detail1_description'=>  'Shop:',
		'detail1_text'=>  STORE_NAME.' Order:'.$_SESSION['tmp_oID'],
		'recipient_description' => STORE_NAME,
		'hide_login'=>'1',

		'detail2_description'=>  'Datum:',
		'detail2_text'=>  strftime(DATE_FORMAT_LONG),

		'amount2_description'=>  'Summe:',
		'amount2'=>  round($amount,2),
		'payment_methods'=>$this->method,

		'merchant_fields'=>  'Field1,platform',
		'Field1'=>  md5(_PAYMENT_MONEYBOOKERS_MERCHANTID),
    'platform' => '87039772',

		'firstname'=>  $order->billing['firstname'],
		'lastname'=>  $order->billing['lastname'],
		'address'=>  $order->billing['street_address'],
		'postal_code'=>  $order->billing['postcode'],
		'city'=>  $order->billing['city'],
		'state'=>  $order->billing['state'],
		'country'=>  $order->billing['country']['iso_code_3'],
		'return_url_target'=>'2',
		'cancel_url_target'=>'2',
		'new_window_redirect'=>'1',
		'confirmation_note'=>  constant('MODULE_PAYMENT_MONEYBOOKERS_'.strtoupper($this->module).'_CONFIRMATION_TEXT'));
		
		$data = '';
        foreach ($params as $key => $value) {
          //$value = strtr($value, "áéíóöõúüûÁÉÍÓÖÕÚÜÛ", "aeiooouuuAEIOOOUUU"); //web28 -2011-05-24 - Fix special characters          
          if ($key!='status_url') {
            $value=urlencode(utf8_encode($value)); //web28 -2011-05-24 - Fix special characters
          }
          $data .= $key . '=' . $value . "&";
        }


		return $this->form_action_url.'?'.$data;
		
	}
	
	function pre_confirmation_check() {
		return false;
	}

	function confirmation() {
		return false;
	}

	function process_button() {
		return false;
	}
	
	function payment_action() {
		xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT_IFRAME, '', 'SSL'));
	}


	function before_process() {
		return false;
	}

	function after_process() {
		return false;

	}

	function admin_order($oID) {
		$oID = (int) $oID;
		if (!is_int($oID)) return false;

		$query = "SELECT * FROM payment_moneybookers WHERE mb_ORDERID = '" . $oID . "'";
		$query = xtc_db_query($query);

		$data = xtc_db_fetch_array($query);

		$html = '
						<tr>
				            <td class="main">' . MB_TEXT_MBDATE . '</td>
				            <td class="main">' . $data['mb_DATE'] . '</td>
				        </tr>
						<tr>
				            <td class="main">' . MB_TEXT_MBTID . '</td>
				            <td class="main">' . $data['mb_MBTID'] . '</td>
				        </tr>
						<tr>
				            <td class="main">' . MB_TEXT_MBERRTXT . '</td>
				            <td class="main">' . $data['mb_ERRTXT'] . '</td>
				        </tr>';

		echo $html;

	}
	
	// Parse the predefinied array to be 'module install' friendly
	// as it is used for select in the module's install() function
	function show_array($aArray) {
		$aFormatted = "array(";
		foreach ($aArray as $key => $sVal) {
			$aFormatted .= "\'$sVal\', ";
		}
		$aFormatted = substr($aFormatted, 0, strlen($aFormatted) - 2);
		return $aFormatted;
	}

	function generate_trid() {

		do {
			$trid = xtc_create_random_value(16, "digits");
			$trid =  chr(88).chr(84).chr(67) . $trid;
			$result = xtc_db_query("SELECT mb_TRID FROM payment_moneybookers WHERE mb_TRID = '".$trid."'");
		} while (xtc_db_num_rows($result));

		return $trid;

	}
	
	function insert_trid() {
		$result = xtc_db_query("SELECT mb_TRID FROM payment_moneybookers WHERE mb_TRID = '".$_SESSION['transaction_id']."'");
		if (!xtc_db_num_rows($result)) {
			$result = xtc_db_query("INSERT INTO payment_moneybookers (mb_TRID, mb_DATE,mb_ORDERID) VALUES ('".$_SESSION['transaction_id']."', NOW(),'".(int)$_SESSION['tmp_oID']."')");
		}
	}
	
	function get_error() {
		global $_GET;

		$error = array (
			'title' => constant('MODULE_PAYMENT_MONEYBOOKERS_'.strtoupper($this->module).'_TEXT_ERROR'),
			'error' => stripslashes(urldecode($_GET['error']
		)));

		return $error;
	}
	
	function _setAllowed($allowed) {
		$this->allowed=$allowed;
	}
	
	function install() {


		$this->remove();
		
		//
		$mb_installed = false;
		//BOF - Hetfield - 2010-01-28 - replace mysql_list_tables with query SHOW TABLES -> PHP5.3 deprecated
		//$tables = mysql_list_tables(DB_DATABASE);
		$tables = xtc_db_query("SHOW TABLES LIKE 'payment_moneybookers'");			
		while ($checktables = xtc_db_fetch_array($tables, MYSQL_NUM)) {
			if ($checktables[0] == 'payment_moneybookers')  $mb_installed=true;
		}
		//EOF - Hetfield - 2010-01-28 - replace mysql_list_tables with query SHOW TABLES -> PHP5.3 deprecated

		if ($mb_installed==false) {
		xtc_db_query("CREATE TABLE payment_moneybookers (mb_TRID varchar(255) NOT NULL default '',mb_ERRNO smallint(3) unsigned NOT NULL default '0',mb_ERRTXT varchar(255) NOT NULL default '',mb_DATE datetime NOT NULL default '0000-00-00 00:00:00',mb_MBTID bigint(18) unsigned NOT NULL default '0',mb_STATUS tinyint(1) NOT NULL default '0',mb_ORDERID int(11) unsigned NOT NULL default '0',PRIMARY KEY  (mb_TRID))");
		}

		xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_MONEYBOOKERS_".strtoupper($this->module)."_STATUS', 'True',  '6', '0', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
		xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_MONEYBOOKERS_".strtoupper($this->module)."_SORT_ORDER', '0',  '6', '4', now())");
		xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_PAYMENT_MONEYBOOKERS_".strtoupper($this->module)."_ZONE', '0',  '6', '7', 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(', now())");
		xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_MONEYBOOKERS_".strtoupper($this->module)."_ALLOWED', '".$this->allowed."', '6', '0', now())");
		// tables
	}
	
	function remove() {
		xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
	}
	
	function keys() {
		return array (
			'MODULE_PAYMENT_MONEYBOOKERS_'.strtoupper($this->module).'_STATUS',
			'MODULE_PAYMENT_MONEYBOOKERS_'.strtoupper($this->module).'_SORT_ORDER',
			'MODULE_PAYMENT_MONEYBOOKERS_'.strtoupper($this->module).'_ALLOWED',
			'MODULE_PAYMENT_MONEYBOOKERS_'.strtoupper($this->module).'_ZONE'
		);
	}
	

	function check() {
		if (!isset ($this->_check)) {
			$check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_MONEYBOOKERS_".strtoupper($this->module)."_STATUS'");
			$this->_check = xtc_db_num_rows($check_query);
		}
		return $this->_check;
	}
	
	
}
?>