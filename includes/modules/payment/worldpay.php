<?php

/* -----------------------------------------------------------------------------------------
   $Id: worldpay.php,v 1.0 

    modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(world.php,v Version 1.6); www.oscommerce.com

   Released under the GNU General Public License

   Third Party contribution:

************************************************************************
  $Id: worldpay.php,v MS1a 2003/04/06 21:30
  Author : Graeme Conkie (graeme@conkie.net)
  Title: WorldPay Payment Callback Module V4.0 Version 1.6

  Revisions:
  
Paulz added minor changes to enable control of 'Payment Zone' added function update_status
Version MS1a Cleaned up code, moved static English to language file to allow for bi-lingual use,
        Now posting language code to WP, Redirect on failure now to Checkout Payment,
Reduced re-direct time to 8 seconds, added MD5, made callback dynamic
NOTE: YOU MUST CHANGE THE CALLBACK URL IN WP ADMIN TO <wpdisplay item="MC_callback">
Version 1.4 Removes boxes to prevent users from clicking away before update,
Fixes currency for Yen,
Redirects to Checkout_Process after 10 seconds or click by user
Version 1.3 Fixes problem with Multi Currency
Version 1.2 Added Sort Order and Default order status to work with snapshots after 14 Jan 2003
Version 1.1 Added Worldpay Pre-Authorisation ability
Version 1.0 Initial Payment Module

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003
  Released under the GNU General Public License
-----------------------------------------------------------------------------------------*/

class worldpay {
	var $code, $title, $description, $enabled;

	// class constructor
	function worldpay() {
		global $order;
		$this->code = 'worldpay';
		$this->title = MODULE_PAYMENT_WORLDPAY_TEXT_TITLE;
		$this->description = MODULE_PAYMENT_WORLDPAY_TEXT_DESC;
		$this->sort_order = MODULE_PAYMENT_WORLDPAY_SORT_ORDER;
		$this->enabled = ((MODULE_PAYMENT_WORLDPAY_STATUS == 'True') ? true : false);
		$this->info = MODULE_PAYMENT_WORLDPAY_TEXT_INFO;
		if ((int) MODULE_PAYMENT_WORLDPAY_ORDER_STATUS_ID > 0) {
			$this->order_status = MODULE_PAYMENT_WORLDPAY_ORDER_STATUS_ID;
		}

		if (is_object($order))
			$this->update_status();

		$this->form_action_url = 'https://select.worldpay.com/wcc/purchase';

	}

	// class methods
	function update_status() {
		global $order;

		if (($this->enabled == true) && ((int) MODULE_PAYMENT_WORLDPAY_ZONE > 0)) {
			$check_flag = false;
			$check_query = xtc_db_query("select zone_id from ".TABLE_ZONES_TO_GEO_ZONES." where geo_zone_id = '".MODULE_PAYMENT_WORLDPAY_ZONE."' and zone_country_id = '".$order->billing['country']['id']."' order by zone_id");
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

	// class methods
	function javascript_validation() {
		return false;
	}

	function selection() {
		return array ('id' => $this->code, 'module' => $this->title, 'description' => $this->info);
	}

	function pre_confirmation_check() {
		return false;
	}

	function confirmation() {
		return false;
	}

	function process_button() {
		global $order, $xtPrice;

		$worldpay_url = xtc_session_name().'='.xtc_session_id();
		$total = number_format($xtPrice->xtcCalculateCurr($order->info['total']), $xtPrice->get_decimal_places($_SESSION['currency']), '.', '');

		$process_button_string = xtc_draw_hidden_field('instId', MODULE_PAYMENT_WORLDPAY_ID).xtc_draw_hidden_field('currency', $_SESSION['currency']).xtc_draw_hidden_field('desc', 'Purchase from '.STORE_NAME).xtc_draw_hidden_field('cartId', $worldpay_url).xtc_draw_hidden_field('amount', $total);

		// Pre Auth Mod 3/1/2002 - Graeme Conkie
		if (MODULE_PAYMENT_WORLDPAY_USEPREAUTH == 'True')
			$process_button_string .= xtc_draw_hidden_field('authMode', MODULE_PAYMENT_WORLDPAY_PREAUTH);

		// Ian-san: Create callback and language links here 6/4/2003:
		$language_code_raw = xtc_db_query("select code from ".TABLE_LANGUAGES." where languages_id ='".$_SESSION['languages_id']."'");
		$language_code_array = xtc_db_fetch_array($language_code_raw);
		$language_code = $language_code_array['code'];

		$address = encode_htmlspecialchars($order->customer['street_address']."\n".$order->customer['suburb']."\n".$order->customer['city']."\n".$order->customer['state'], ENT_QUOTES);

		$process_button_string .= xtc_draw_hidden_field('testMode', MODULE_PAYMENT_WORLDPAY_MODE).xtc_draw_hidden_field('name', $order->customer['firstname'].' '.$order->customer['lastname']).xtc_draw_hidden_field('address', $address).xtc_draw_hidden_field('postcode', $order->customer['postcode']).xtc_draw_hidden_field('country', $order->customer['country']['iso_code_2']).xtc_draw_hidden_field('tel', $order->customer['telephone']).xtc_draw_hidden_field('myvar', 'Y').xtc_draw_hidden_field('fax', $order->customer['fax']).xtc_draw_hidden_field('email', $order->customer['email_address']).

		// Ian-san: Added dynamic callback and languages link here 6/4/2003:
		xtc_draw_hidden_field('lang', $language_code).xtc_draw_hidden_field('MC_callback', xtc_href_link(wpcallback).'.php').xtc_draw_hidden_field('MC_XTCsid', $XTCsid);

		// Ian-san: Added MD5 here 6/4/2003:
		if (MODULE_PAYMENT_WORLDPAY_USEMD5 == '1') {
			$md5_signature_fields = 'amount:language:email';
			$md5_signature = MODULE_PAYMENT_WORLDPAY_MD5KEY.':'. (number_format($order->info['total'] * $currencies->get_value($currency), $currencies->get_decimal_places($currency), '.', '')).':'.$language_code.':'.$order->customer['email_address'];
			$md5_signature_md5 = md5($md5_signature);

			$process_button_string .= xtc_draw_hidden_field('signatureFields', $md5_signature_fields).xtc_draw_hidden_field('signature', $md5_signature_md5);
		}
		return $process_button_string;
	}

	function before_process() {
		return false;
	}

	function after_process() {
 	global $insert_id;
	if ($this->order_status) xtc_db_query("UPDATE ". TABLE_ORDERS ." SET orders_status='".$this->order_status."' WHERE orders_id='".$insert_id."'");
	}

	function output_error() {
		return false;
	}

	function check() {
		if (!isset ($this->_check)) {
			$check_query = xtc_db_query("select configuration_value from ".TABLE_CONFIGURATION." where configuration_key = 'MODULE_PAYMENT_WORLDPAY_STATUS'");
			$this->_check = xtc_db_num_rows($check_query);
		}
		return $this->_check;
	}

	function install() {
		xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_WORLDPAY_STATUS', 'True', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_WORLDPAY_ID', '00000', '6', '2', now())");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_WORLDPAY_MODE', '100', '6', '5', now())");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_WORLDPAY_ALLOWED', '', '6', '0', now())");
		// Ian-san: Added MD5 here 6/4/2003:
		xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_WORLDPAY_USEMD5', '0', '6', '4', now())");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_WORLDPAY_MD5KEY', '', '6', '5', now())");

		// Pre Auth Mod - Graeme Conkie 13/1/2003
		xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_WORLDPAY_SORT_ORDER', '0', '6', '0', now())");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_WORLDPAY_USEPREAUTH', 'False', '6', '3', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_WORLDPAY_ORDER_STATUS_ID', '0', '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_WORLDPAY_PREAUTH', 'A', '6', '4', now())");
		// Paulz zone control 04/04/2004        
		xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_PAYMENT_WORLDPAY_ZONE', '0', '6', '2', 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(', now())");
		// Ian-san: Added MD5 here 6/4/2003:
		xtc_db_query("delete from ".TABLE_CONFIGURATION." where configuration_key = 'MODULE_PAYMENT_WORLDPAY_USEMD5'");
		xtc_db_query("delete from ".TABLE_CONFIGURATION." where configuration_key = 'MODULE_PAYMENT_WORLDPAY_MD5KEY'");
	}

	function remove() {
		xtc_db_query("delete from ".TABLE_CONFIGURATION." where configuration_key in ('".implode("', '", $this->keys())."')");
	}

	function keys() {
		return array ('MODULE_PAYMENT_WORLDPAY_STATUS', 'MODULE_PAYMENT_WORLDPAY_ID', 'MODULE_PAYMENT_WORLDPAY_MODE', 'MODULE_PAYMENT_WORLDPAY_ALLOWED', 'MODULE_PAYMENT_WORLDPAY_USEPREAUTH', 'MODULE_PAYMENT_WORLDPAY_PREAUTH', 'MODULE_PAYMENT_WORLDPAY_ZONE', 'MODULE_PAYMENT_WORLDPAY_SORT_ORDER', 'MODULE_PAYMENT_WORLDPAY_ORDER_STATUS_ID');
	}
}
?>