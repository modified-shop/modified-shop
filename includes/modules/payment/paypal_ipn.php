<?php

/* -----------------------------------------------------------------------------------------
   $Id: paypal.php 998 2005-07-07 14:18:20Z mz $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(paypal.php,v 1.39 2003/01/29); www.oscommerce.com
   (c) 2003	 nextcommerce (paypal.php,v 1.8 2003/08/24); www.nextcommerce.org

   Released under the GNU General Public License
   VERSION 2011-11-29 by web28
   ---------------------------------------------------------------------------------------*/

class paypal_ipn {
	var $code, $title, $description, $enabled;
/**************************************************************/
	function paypal_ipn() {
		global $order;

		$this->code = 'paypal_ipn';
		$this->logo = xtc_image('../'.DIR_WS_ICONS . 'pp_logo_100x31.gif');
		$this->title = MODULE_PAYMENT_PAYPAL_IPN_TEXT_TITLE;
		$this->description = MODULE_PAYMENT_PAYPAL_IPN_TEXT_DESCRIPTION;
		$this->sort_order = MODULE_PAYMENT_PAYPAL_IPN_SORT_ORDER;
		$this->enabled = ((MODULE_PAYMENT_PAYPAL_IPN_STATUS == 'True') ? true : false);
		$this->info = MODULE_PAYMENT_PAYPAL_IPN_TEXT_INFO;		
		if ((int) MODULE_PAYMENT_PAYPAL_IPN_TMP_STATUS_ID > 0) {
			$this->order_status = MODULE_PAYMENT_PAYPAL_IPN_TMP_STATUS_ID;
		}

		if (is_object($order))
			$this->update_status();        
		
		
	}
/**************************************************************/
	function update_status() {
		global $order;

		if (($this->enabled == true) && ((int) MODULE_PAYMENT_PAYPAL_IPN_ZONE > 0)) {
			$check_flag = false;
			$check_query = xtc_db_query("select zone_id from ".TABLE_ZONES_TO_GEO_ZONES." where geo_zone_id = '".MODULE_PAYMENT_PAYPAL_IPN_ZONE."' and zone_country_id = '".$order->billing['country']['id']."' order by zone_id");
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
/**************************************************************/
	function javascript_validation() {
		return false;
	}
/**************************************************************/
	function selection() {
		return array ('id' => $this->code, 'module' => $this->title, 'description' => $this->info);
	}
/**************************************************************/
	function pre_confirmation_check() {
		return false;
	}
/**************************************************************/
	function confirmation() {
		return false;
	}
/**************************************************************/
	function process_button() {
		return false;
	}
/**************************************************************/
	function before_process() {
		return false;
	}
/**************************************************************/
	function payment_action() {
		global $tmp;
		$tmp = false; //WICHITG, damit es in checkout_process.php weiter geht
	}
/**************************************************************/
	function after_process() {		
		global $insert_id;
		if ($this->order_status)
			xtc_db_query("UPDATE ".TABLE_ORDERS." SET orders_status='".$this->order_status."' WHERE orders_id='".$insert_id."'");			
	}
/**************************************************************/
	function output_error() {
		return false;
	}
/**************************************************************/
	function check() {
		if (!isset ($this->_check)) {
			$check_query = xtc_db_query("select configuration_value from ".TABLE_CONFIGURATION." where configuration_key = 'MODULE_PAYMENT_PAYPAL_IPN_TMP_STATUS_ID'");
			$this->_check = xtc_db_num_rows($check_query);
		}
		return $this->_check;
	}
/**************************************************************/
	function create_paypal_link() {
		global $order, $order_id, $order_total, $xtPrice, $paypal_link, $send_by_admin; //BOF - web28 - 2011-01-20 - FIX SEND ORDERS FROM ADMIN
		
		//Daten aus Tabelle orders und orders_total 	
		
		//ACHTUNG LINK darf keine Leerzeichen enthalten, deshalb die Problemvariablen mit urlencode codieren
		
		//Table 8. HTML Variables for Prepopulating Checkout Pages With Payer Information - alle Variablen optional
		$first_name = urlencode($order->delivery['firstname']); //Customer’s first name Length: 64 characters
		$last_name = urlencode($order->delivery['lastname']); //Customer’s last name Length: 64 characters
		$address1 = urlencode($order->delivery['street_address']); //Street (1 of 2 fields) Length: 100 characters		
		$city = urlencode($order->delivery['city']); // City Length: 100 characters
		$country = urlencode($order->delivery['country_iso_2']); //Sets shipping and billing country.  PayPal uses 2-character IS0-3166-1 codes for specifying countries and regions that are supported in fields and variables. 
		$zip = urlencode($order->delivery['postcode']); //Postal code Length: 32 characters

		//Table 4. HTML Variables for Payment Transactions
		$address_override = 1; //1 – The address specified in prepopulation variables overrides the PayPal member’s stored address. The payer is shown the passed-in address but cannot edit it. No address is shown if the address is not valid, such as missing required fields like country, or is not included at all.
		$currency_code = $order->info['currency']; //PayPal uses 3-character ISO-4217 codes for specifying currencies in fields and variables. 
		$shipping = round($order_total['shipping'], $xtPrice->get_decimal_places($order->info['currency'])); //The cost of shipping this item. If you specify shipping  and shipping2 is not defined, this flat amount is charged regardless of the quantity of items purchased. 
		
		//Table 5. HTMLVariables for Shopping Carts
		$amount = round($order_total['total'], $xtPrice->get_decimal_places($order->info['currency'])); //The price or amount of the product, service, or contribution, not including shipping, handling, or tax. 
		//$business = trim(MODULE_PAYMENT_PAYPAL_IPN_ID); //Your PayPal ID or an email address associated with your PayPal account. Email addresses must be confirmed. 
		$item_number = urlencode($order_id); //Name of the item or a name for the entire Shopping Cart			
		
		//Table 7. HTML Variables for Displaying PayPal Checkout Pages
		$cpp_headerback_color= trim(MODULE_PAYMENT_PAYPAL_IPN_CO_BACK); //The background color for the header of the checkout page. Valid value is case-insensitive six-character HTML hexadecimal color code in ASCII.
		$cpp_headerborder_color= trim(MODULE_PAYMENT_PAYPAL_IPN_CO_BORD); //The border color around the header of the checkout page. The border is a 2-pixel perimeter around the header space, which has a maximum size of 750 pixels wide by 90 pixels high. Valid value is case-insensitive six-character HTML hexadecimal color code in ASCII.
		$cpp_payflow_color= trim(MODULE_PAYMENT_PAYPAL_IPN_CO_SITE); //The background color for the checkout page below the header. Valid value is case-insensitive six-character HTML hexadecimal color code in ASCII. Note: Background colors that conflict with PayPal’s error messages are not allowed; in these cases, the default color is white.

    $lc= urlencode($order->delivery['country_iso_2']); //The language of the login or sign-up page that subscribers see when they click the Subscribe button. If unspecified, the language is determined by a PayPal cookie in the subscriber’s browser. If there is no PayPal cookie, the default language is U.S. English.  PayPal uses 2-character IS0-3166-1 codes for specifying countries and regions that are supported in fields and variables. 
    //EN mit US ersetzen, sonst wird die Paypal Seite nicht in Englisch angezeigt
    if ($lc == 'EN') $lc= 'US';
    
    $no_shipping = 0; //Do not prompt payers for shipping address. Allowable values:   * 0 – prompt for an address, but do not require one    *  1 – do not prompt for an address    *     2 – prompt for an address, and require one --- The default is 0.
    $rm= 2; //Return method. The FORM METHOD used to send data to the URL specified by the return variable after payment completion. Allowable values:    * 0 – all shopping cart transactions use the GET method   * 1 – the payer’s browser is redirected to the return URL by the GET method, and no transaction variables are sent   *  2 – the payer’s browser is redirected to the return URL by the POST method, and all transaction variables are also posted --- The default is 0. ---  Note: The rm variable takes effect only if the return variable is also set.
    $cbt= urlencode(MODULE_PAYMENT_PAYPAL_IPN_CBT); // Sets the text for the Return to Merchant  button on the PayPal Payment Complete page. For Business accounts, the return button displays your business name in place of the word “Merchant” by default. For Donate buttons, the text reads “Return to donations coordinator” by default. Note: The return variable must also be set.
        
    //BOF - web28 - 2011-01-20 - FIX SEND ORDERS FROM ADMIN
    if (trim(MODULE_PAYMENT_PAYPAL_IPN_IMAGE) != '') { //web28 fix problem with empty xtc_href_link
      $cpp_header_image= xtc_href_link('templates/'.CURRENT_TEMPLATE.'/img/'.urlencode(MODULE_PAYMENT_PAYPAL_IPN_IMAGE), '', 'SSL'); //The image at the top left of the checkout page. The image’s maximum size is 750 pixels wide by 90 pixels high. PayPal recommends that you provide an image that is stored only on a secure (https) server. 
      if($send_by_admin) $cpp_header_image= xtc_href_link_from_admin('templates/'.CURRENT_TEMPLATE.'/img/'.urlencode(MODULE_PAYMENT_PAYPAL_IPN_IMAGE), '', 'SSL');
    }    
    if (trim(MODULE_PAYMENT_PAYPAL_IPN_RETURN) != '') { //web28 fix problem with empty xtc_href_link
      $return = xtc_href_link(urlencode(MODULE_PAYMENT_PAYPAL_IPN_RETURN)); //The URL to which the payer’s browser is redirected after completing the payment; for example, a URL on your site that displays a “Thank you for your payment” page. --- Default – The browser is redirected to a PayPal web page.
      if($send_by_admin) $return = xtc_href_link_from_admin (urlencode(MODULE_PAYMENT_PAYPAL_IPN_RETURN));
    }
    if (trim(MODULE_PAYMENT_PAYPAL_IPN_NOTIFY) != '') { //web28 fix problem with empty xtc_href_link
      $notify_url = xtc_href_link(urlencode(MODULE_PAYMENT_PAYPAL_IPN_NOTIFY)); //The URL to which PayPal posts information about the transaction, in the form of Instant Payment Notification messages. 
      if($send_by_admin) $notify_url = xtc_href_link_from_admin(urlencode(MODULE_PAYMENT_PAYPAL_IPN_NOTIFY));
    }    
    if (trim(MODULE_PAYMENT_PAYPAL_IPN_CANCEL) != '') { //web28 fix problem with empty xtc_href_link
      $cancel_return= xtc_href_link(urlencode(MODULE_PAYMENT_PAYPAL_IPN_CANCEL)); //A URL to which the payer’s browser is redirected if payment is cancelled; for example, a URL on your website that displays a “Payment Canceled” page. Default – The browser is redirected to a PayPal web page.
      if($send_by_admin) $cancel_return= xtc_href_link_from_admin(urlencode(MODULE_PAYMENT_PAYPAL_IPN_CANCEL));      
    }
    //EOF - web28 - 2011-01-20 - FIX SEND ORDERS FROM ADMIN
		
		//Testbetrieb
		if(MODULE_PAYMENT_PAYPAL_IPN_USE_SANDBOX == 'True') {
			$business = trim(MODULE_PAYMENT_PAYPAL_IPN_SBID); //Your PayPal ID or an email address associated with your PayPal account. Email addresses must be confirmed. 
			$href= 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_xclick'; //Testbetrieb mit Sandbox
		} else {			
			$business = trim(MODULE_PAYMENT_PAYPAL_IPN_ID); //Your PayPal ID or an email address associated with your PayPal account. Email addresses must be confirmed. 		
			$href= 'https://www.paypal.com/cgi-bin/webscr?cmd=_xclick';
		}
		
		//Linkerstellung
		$style = MODULE_PAYMENT_PAYPAL_IPN_STYLE_LINK;
		$data_string = MODULE_PAYMENT_PAYPAL_IPN_STYLE_LOGO;
		$data_string .= MODULE_PAYMENT_PAYPAL_IPN_STYLE_TOP . '<a '. $style . ' href="';		
				
		$store_name = urlencode(STORE_NAME . MODULE_PAYMENT_PAYPAL_IPN_TXT_ORDER); 
		//BOF - web28 - 2010-07-14 - change & to &amp;		
		//$data_link = $href .'&business='.$business.'&item_name='. $store_name. $item_number .'&item_number='.$item_number.'&amount='. ($amount - $shipping).'&shipping='.$shipping.'&currency_code='.$currency_code.'&return='. $return .'&no_shipping='. $no_shipping .'&cbt='. $cbt .'&cancel_return='. $cancel_return. '&cpp_headerback_color='. $cpp_headerback_color.'&cpp_headerborder_color='.$cpp_headerborder_color.'&cpp_payflow_color='.$cpp_payflow_color.'&cpp_header_image='.$cpp_header_image.'&cbt='.$cbt.'&lc='.$lc;     
    $data_link = $href .'&amp;business='.$business.'&amp;item_name='. $store_name. $item_number .'&amp;item_number='.$item_number.'&amp;amount='. ($amount - $shipping).'&amp;shipping='.$shipping.'&amp;currency_code='.$currency_code.'&amp;return='. $return .'&amp;no_shipping='. $no_shipping .'&amp;cbt='. $cbt .'&amp;cancel_return='. $cancel_return. '&amp;cpp_headerback_color='. $cpp_headerback_color.'&amp;cpp_headerborder_color='.$cpp_headerborder_color.'&amp;cpp_payflow_color='.$cpp_payflow_color.'&amp;cpp_header_image='.$cpp_header_image.'&amp;lc='.$lc;     
    //$data_link .= '&amp;notify_url='.$notify_url.'&amp;first_name='.$first_name.'&amp;last_name='.$last_name.'&amp;address1='.$address1.'&amp;zip='.$zip.'&amp;city='.$city.'&amp;country='.$country;  //only for PayPal IPN Advanced
		//EOF - web28 - 2010-07-14 - change & to &amp;
		
		$data_string .= $data_link .'">'.MODULE_PAYMENT_PAYPAL_IPN_TXT_CHECKOUT.'</a>'.'</div>';
		$data_string2 = MODULE_PAYMENT_PAYPAL_IPN_STYLE_TEXT;
        
		
		$paypal_link['checkout'] = $data_string . $data_string2;
		$paypal_link['html'] = $data_string;
		$paypal_link['text'] = $data_link;	
	    
	}
/**************************************************************/
	function install() {
		xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_PAYPAL_IPN_STATUS', 'True', '6', '3', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PAYPAL_IPN_ALLOWED', '', '6', '0', now())");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PAYPAL_IPN_ID', 'you@yourbusiness.com',  '6', '4', now())");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_PAYPAL_IPN_CURRENCY', 'Selected Currency',  '6', '6', 'xtc_cfg_select_option(array(\'Selected Currency\',\'Only USD\',\'Only CAD\',\'Only EUR\',\'Only GBP\',\'Only JPY\',\'Only CHF\'), ', now())");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PAYPAL_IPN_SORT_ORDER', '0', '6', '0', now())");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_PAYMENT_PAYPAL_IPN_ZONE', '0', '6', '2', 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(', now())");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_PAYPAL_IPN_ORDER_STATUS_ID', '0',  '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_PAYPAL_IPN_TMP_STATUS_ID', '0',  '6', '8', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
		
		xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_PAYPAL_IPN_USE_CHECKOUT', 'True', '6', '10', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_PAYPAL_IPN_USE_EMAIL', 'True', '6', '11', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_PAYPAL_IPN_USE_ACCOUNT', 'True', '6', '12', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_PAYPAL_IPN_USE_SANDBOX', 'False', '6', '13', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
		
		xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PAYPAL_IPN_IMAGE', '', '6', '14', now())");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PAYPAL_IPN_CO_BACK', '', '6', '15', now())");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PAYPAL_IPN_CO_BORD', '', '6', '16', now())");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PAYPAL_IPN_CO_SITE', '', '6', '17', now())");
		
		xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PAYPAL_IPN_RETURN', '', '6', '18', now())");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PAYPAL_IPN_CANCEL', '', '6', '19', now())");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PAYPAL_IPN_NOTIFY', '', '6', '20', now())");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PAYPAL_IPN_CBT', '', '6', '21', now())");
	    
		xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PAYPAL_IPN_SBID', 'sandbox@yourbusiness.com',  '6', '4', now())");
		
	}
/**************************************************************/
	function remove() {
		xtc_db_query("delete from ".TABLE_CONFIGURATION." where configuration_key in ('".implode("', '", $this->keys())."')");
	}
/**************************************************************/
	function keys() {
		return array ('MODULE_PAYMENT_PAYPAL_IPN_STATUS', 
					  'MODULE_PAYMENT_PAYPAL_IPN_ALLOWED', 
					  'MODULE_PAYMENT_PAYPAL_IPN_ID', 
					  'MODULE_PAYMENT_PAYPAL_IPN_CURRENCY', 
					  'MODULE_PAYMENT_PAYPAL_IPN_ZONE',
					  'MODULE_PAYMENT_PAYPAL_IPN_TMP_STATUS_ID',
					  //'MODULE_PAYMENT_PAYPAL_IPN_ORDER_STATUS_ID',					  
					  'MODULE_PAYMENT_PAYPAL_IPN_SORT_ORDER',
					  'MODULE_PAYMENT_PAYPAL_IPN_IMAGE',
					  'MODULE_PAYMENT_PAYPAL_IPN_CO_BACK',
					  'MODULE_PAYMENT_PAYPAL_IPN_CO_BORD',
					  'MODULE_PAYMENT_PAYPAL_IPN_CO_SITE',
					  'MODULE_PAYMENT_PAYPAL_IPN_CBT',
					  'MODULE_PAYMENT_PAYPAL_IPN_RETURN',
					  //'MODULE_PAYMENT_PAYPAL_IPN_NOTIFY',
					  'MODULE_PAYMENT_PAYPAL_IPN_CANCEL',					  
					  'MODULE_PAYMENT_PAYPAL_IPN_USE_CHECKOUT',
					  'MODULE_PAYMENT_PAYPAL_IPN_USE_EMAIL',
					  'MODULE_PAYMENT_PAYPAL_IPN_USE_ACCOUNT',
					  'MODULE_PAYMENT_PAYPAL_IPN_USE_SANDBOX',
					  'MODULE_PAYMENT_PAYPAL_IPN_SBID');
	}
}
?>