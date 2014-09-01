<?php
/**

 * @version SOFORT Gateway 5.2.0 - $Date: 2012-09-06 13:49:09 +0200 (Thu, 06 Sep 2012) $

 * @author SOFORT AG (integration@sofort.com)

 * @link http://www.sofort.com/

 *

 * Copyright (c) 2012 SOFORT AG

 *

 * $Id: xtcmod_1.05_sofortOrderhandling.php 3751 2012-10-10 08:36:20Z gtb-modified $

 */

/******************************************************************
 * This class includes function to
 * - insert orders/attributes into the shop-DB
 * - update orders/attributes in the shop-DB
 * - update the stock
 * - delete the cart from session
 * - delete sofortData from session
 * - trigger customer- and seller-email
 ******************************************************************/

class SofortOrderhandling{
	
	private $Order;
	private $Smarty;
	private $orderTotals;
	private $orderTotalModules;
	
	/**
	 * constructor
	 */
	public function __construct() {
		$this->Order = '';
		$this->Smarty = '';
		$this->orderTotals = array();
		$this->orderTotalModules = '';
	}
	
	
	/**
	 * Use $_SESSION to insert the order into the shop-db --- $_SESSION must contain all order-data! $GLOBALS must be set with all needed vars!
	 * incl. attributes and stockupdate
	 * @return array with orderId and orderProductsIds
	 */
	public function insertOrderIntoShop() {
		global $order, $order_total_modules, $order_totals, $insert_id;
		
		if (!is_object($order)) { //$order doesnt exist if called by notification!
			$order = $this->Order;
		}
		
		$order_total_modules = $this->orderTotalModules;
		$order_totals = $this->orderTotals;
		
		$tmp_status = $order->info['order_status'];
		
		if ($_SESSION['customers_status']['customers_status_ot_discount_flag'] == 1) {
			$discount = $_SESSION['customers_status']['customers_status_ot_discount'];
		} else {
			$discount = '0.00';
		}
		
		if ($_SERVER["HTTP_X_FORWARDED_FOR"]) {
			$customers_ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		} else {
			$customers_ip = $_SERVER["REMOTE_ADDR"];
		}
		if ($_SESSION['credit_covers'] != '1') {
			$sql_data_array = array ('customers_id' => $_SESSION['customer_id'], 'customers_name' => $order->customer['firstname'].' '.$order->customer['lastname'], 'customers_firstname' => $order->customer['firstname'], 'customers_lastname' => $order->customer['lastname'], 'customers_cid' => $order->customer['csID'], 'customers_vat_id' => $_SESSION['customer_vat_id'], 'customers_company' => $order->customer['company'], 'customers_status' => $_SESSION['customers_status']['customers_status_id'], 'customers_status_name' => $_SESSION['customers_status']['customers_status_name'], 'customers_status_image' => $_SESSION['customers_status']['customers_status_image'], 'customers_status_discount' => $discount, 'customers_street_address' => $order->customer['street_address'], 'customers_suburb' => $order->customer['suburb'], 'customers_city' => $order->customer['city'], 'customers_postcode' => $order->customer['postcode'], 'customers_state' => $order->customer['state'], 'customers_country' => $order->customer['country']['title'], 'customers_telephone' => $order->customer['telephone'], 'customers_email_address' => $order->customer['email_address'], 'customers_address_format_id' => $order->customer['format_id'], 'delivery_name' => $order->delivery['firstname'].' '.$order->delivery['lastname'], 'delivery_firstname' => $order->delivery['firstname'], 'delivery_lastname' => $order->delivery['lastname'], 'delivery_company' => $order->delivery['company'], 'delivery_street_address' => $order->delivery['street_address'], 'delivery_suburb' => $order->delivery['suburb'], 'delivery_city' => $order->delivery['city'], 'delivery_postcode' => $order->delivery['postcode'], 'delivery_state' => $order->delivery['state'], 'delivery_country' => $order->delivery['country']['title'], 'delivery_country_iso_code_2' => $order->delivery['country']['iso_code_2'], 'delivery_address_format_id' => $order->delivery['format_id'], 'billing_name' => $order->billing['firstname'].' '.$order->billing['lastname'], 'billing_firstname' => $order->billing['firstname'], 'billing_lastname' => $order->billing['lastname'], 'billing_company' => $order->billing['company'], 'billing_street_address' => $order->billing['street_address'], 'billing_suburb' => $order->billing['suburb'], 'billing_city' => $order->billing['city'], 'billing_postcode' => $order->billing['postcode'], 'billing_state' => $order->billing['state'], 'billing_country' => $order->billing['country']['title'], 'billing_country_iso_code_2' => $order->billing['country']['iso_code_2'], 'billing_address_format_id' => $order->billing['format_id'], 'payment_method' => $order->info['payment_method'], 'payment_class' => $order->info['payment_class'], 'shipping_method' => $order->info['shipping_method'], 'shipping_class' => $order->info['shipping_class'], 'cc_type' => $order->info['cc_type'], 'cc_owner' => $order->info['cc_owner'], 'cc_number' => $order->info['cc_number'], 'cc_expires' => $order->info['cc_expires'], 'cc_start' => $order->info['cc_start'], 'cc_cvv' => $order->info['cc_cvv'], 'cc_issue' => $order->info['cc_issue'], 'date_purchased' => 'now()', 'orders_status' => $tmp_status, 'currency' => $order->info['currency'], 'currency_value' => $order->info['currency_value'], 'customers_ip' => $customers_ip, 'language' => $_SESSION['language'], 'comments' => $order->info['comments']);
		} else {
			// free gift , no paymentaddress
			$sql_data_array = array ('customers_id' => $_SESSION['customer_id'], 'customers_name' => $order->customer['firstname'].' '.$order->customer['lastname'], 'customers_firstname' => $order->customer['firstname'], 'customers_lastname' => $order->customer['lastname'], 'customers_cid' => $order->customer['csID'], 'customers_vat_id' => $_SESSION['customer_vat_id'], 'customers_company' => $order->customer['company'], 'customers_status' => $_SESSION['customers_status']['customers_status_id'], 'customers_status_name' => $_SESSION['customers_status']['customers_status_name'], 'customers_status_image' => $_SESSION['customers_status']['customers_status_image'], 'customers_status_discount' => $discount, 'customers_street_address' => $order->customer['street_address'], 'customers_suburb' => $order->customer['suburb'], 'customers_city' => $order->customer['city'], 'customers_postcode' => $order->customer['postcode'], 'customers_state' => $order->customer['state'], 'customers_country' => $order->customer['country']['title'], 'customers_telephone' => $order->customer['telephone'], 'customers_email_address' => $order->customer['email_address'], 'customers_address_format_id' => $order->customer['format_id'], 'delivery_name' => $order->delivery['firstname'].' '.$order->delivery['lastname'], 'delivery_firstname' => $order->delivery['firstname'], 'delivery_lastname' => $order->delivery['lastname'], 'delivery_company' => $order->delivery['company'], 'delivery_street_address' => $order->delivery['street_address'], 'delivery_suburb' => $order->delivery['suburb'], 'delivery_city' => $order->delivery['city'], 'delivery_postcode' => $order->delivery['postcode'], 'delivery_state' => $order->delivery['state'], 'delivery_country' => $order->delivery['country']['title'], 'delivery_country_iso_code_2' => $order->delivery['country']['iso_code_2'], 'delivery_address_format_id' => $order->delivery['format_id'], 'payment_method' => $order->info['payment_method'], 'payment_class' => $order->info['payment_class'], 'shipping_method' => $order->info['shipping_method'], 'shipping_class' => $order->info['shipping_class'], 'cc_type' => $order->info['cc_type'], 'cc_owner' => $order->info['cc_owner'], 'cc_number' => $order->info['cc_number'], 'cc_expires' => $order->info['cc_expires'], 'date_purchased' => 'now()', 'orders_status' => $tmp_status, 'currency' => $order->info['currency'], 'currency_value' => $order->info['currency_value'], 'customers_ip' => $customers_ip, 'comments' => $order->info['comments']);
		}
		
		xtc_db_perform(TABLE_ORDERS, $sql_data_array);
		$insert_id = xtc_db_insert_id();
		for ($i = 0, $n = sizeof($order_totals); $i < $n; $i ++) {
			$sql_data_array = array ('orders_id' => $insert_id, 'title' => $order_totals[$i]['title'], 'text' => $order_totals[$i]['text'], 'value' => $order_totals[$i]['value'], 'class' => $order_totals[$i]['code'], 'sort_order' => $order_totals[$i]['sort_order']);
			xtc_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);
		}
		
		$customer_notification = (SEND_EMAILS == 'true') ? '1' : '0';
		$sql_data_array = array ('orders_id' => $insert_id, 'orders_status_id' => $order->info['order_status'], 'date_added' => 'now()', 'customer_notified' => $customer_notification, 'comments' => $order->info['comments']);
		xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
		
		// initialized for the email confirmation
		$products_ordered = '';
		$products_ordered_html = '';
		$subtotal = 0;
		$total_tax = 0;
		
		$sofortData = array();
		
		for ($i = 0, $n = sizeof($order->products); $i < $n; $i ++) {
			$sofortData[$i] = array();
			$sofortData[$i]['sofortItemId'] = $order->products[$i]['id'];
			
			if (STOCK_LIMITED == 'true') {
				if (DOWNLOAD_ENABLED == 'true') {
					$stock_query_raw = "SELECT products_quantity, pad.products_attributes_filename
								                            FROM ".TABLE_PRODUCTS." p
								                            LEFT JOIN ".TABLE_PRODUCTS_ATTRIBUTES." pa
								                             ON p.products_id=pa.products_id
								                            LEFT JOIN ".TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD." pad
								                             ON pa.products_attributes_id=pad.products_attributes_id
								                            WHERE p.products_id = '".HelperFunctions::escapeSql(xtc_get_prid($order->products[$i]['id']))."'";
					// Will work with only one option for downloadable products
					// otherwise, we have to build the query dynamically with a loop
					$products_attributes = $order->products[$i]['attributes'];
					if (is_array($products_attributes)) {
						$stock_query_raw .= " AND pa.options_id = '".HelperFunctions::escapeSql($products_attributes[0]['option_id'])."' AND pa.options_values_id = '".HelperFunctions::escapeSql($products_attributes[0]['value_id'])."'";
					}
					$stock_query = xtc_db_query($stock_query_raw);
				} else {
					$stock_query = xtc_db_query("select products_quantity from ".TABLE_PRODUCTS." where products_id = '".HelperFunctions::escapeSql(xtc_get_prid($order->products[$i]['id']))."'");
				}
				if (xtc_db_num_rows($stock_query) > 0) {
					$stock_values = xtc_db_fetch_array($stock_query);
					// do not decrement quantities if products_attributes_filename exists
					if ((DOWNLOAD_ENABLED != 'true') || (!$stock_values['products_attributes_filename'])) {
						$stock_left = $stock_values['products_quantity'] - $order->products[$i]['qty'];
					} else {
						$stock_left = $stock_values['products_quantity'];
					}
					
					// doppelbuchung der Artikel bei Rbs verhindern
					if($order->info['payment_method'] != 'sofort_sofortrechnung') {
						xtc_db_query("update ".TABLE_PRODUCTS." set products_quantity = '".HelperFunctions::escapeSql($stock_left)."' where products_id = '".HelperFunctions::escapeSql(xtc_get_prid($order->products[$i]['id']))."'");
					}
					
					if (($stock_left < 1) && (STOCK_ALLOW_CHECKOUT == 'false')) {
						xtc_db_query("update ".TABLE_PRODUCTS." set products_status = '0' where products_id = '".HelperFunctions::escapeSql(xtc_get_prid($order->products[$i]['id']))."'");
					}
				}
			}
			
			// Update products_ordered (for bestsellers list)
			xtc_db_query("update ".TABLE_PRODUCTS." set products_ordered = products_ordered + ".HelperFunctions::escapeSql(sprintf('%d', $order->products[$i]['qty']))." where products_id = '".HelperFunctions::escapeSql(xtc_get_prid($order->products[$i]['id']))."'");
			
			$sql_data_array = array ('orders_id' => $insert_id, 'products_id' => xtc_get_prid($order->products[$i]['id']), 'products_model' => $order->products[$i]['model'], 'products_name' => $order->products[$i]['name'],'products_shipping_time'=>$order->products[$i]['shipping_time'], 'products_price' => $order->products[$i]['price'], 'final_price' => $order->products[$i]['final_price'], 'products_tax' => $order->products[$i]['tax'], 'products_discount_made' => $order->products[$i]['discount_allowed'], 'products_quantity' => $order->products[$i]['qty'], 'allow_tax' => $_SESSION['customers_status']['customers_status_show_price_tax']);
			
			xtc_db_perform(TABLE_ORDERS_PRODUCTS, $sql_data_array);
			$order_products_id = xtc_db_insert_id();
			
			$sofortData[$i]['sofortOrderProductsId'] = $order_products_id;
			
			// Aenderung Specials Quantity Anfang
			$specials_result = xtc_db_query("SELECT products_id, specials_quantity from ".TABLE_SPECIALS." WHERE products_id = '".HelperFunctions::escapeSql(xtc_get_prid($order->products[$i]['id']))."' ");
			if (xtc_db_num_rows($specials_result)) {
				$spq = xtc_db_fetch_array($specials_result);
				
				$new_sp_quantity = ($spq['specials_quantity'] - $order->products[$i]['qty']);
				
				if ($new_sp_quantity >= 1) {
					xtc_db_query("update ".TABLE_SPECIALS." set specials_quantity = '".HelperFunctions::escapeSql($new_sp_quantity)."' where products_id = '".HelperFunctions::escapeSql(xtc_get_prid($order->products[$i]['id']))."' ");
				} else {
					xtc_db_query("update ".TABLE_SPECIALS." set status = '0', specials_quantity = '".HelperFunctions::escapeSql($new_sp_quantity)."' where products_id = '".HelperFunctions::escapeSql(xtc_get_prid($order->products[$i]['id']))."' ");
				}
			}
			// Aenderung Ende

			$order_total_modules->update_credit_account($i); // GV Code ICW ADDED FOR CREDIT CLASS SYSTEM
			//------insert customer choosen option to order--------
			$attributes_exist = '0';
			$products_ordered_attributes = '';
			if (isset ($order->products[$i]['attributes'])) {
				$attributes_exist = '1';
				for ($j = 0, $n2 = sizeof($order->products[$i]['attributes']); $j < $n2; $j ++) {
					if (DOWNLOAD_ENABLED == 'true') {
						$attributes_query = "select popt.products_options_name,
										                               poval.products_options_values_name,
										                               pa.options_values_price,
										                               pa.price_prefix,
										                               pad.products_attributes_maxdays,
										                               pad.products_attributes_maxcount,
										                               pad.products_attributes_filename
										                               from ".TABLE_PRODUCTS_OPTIONS." popt, ".TABLE_PRODUCTS_OPTIONS_VALUES." poval, ".TABLE_PRODUCTS_ATTRIBUTES." pa
										                               left join ".TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD." pad
										                                on pa.products_attributes_id=pad.products_attributes_id
										                               where pa.products_id = '".HelperFunctions::escapeSql($order->products[$i]['id'])."'
										                                and pa.options_id = '".HelperFunctions::escapeSql($order->products[$i]['attributes'][$j]['option_id'])."'
										                                and pa.options_id = popt.products_options_id
										                                and pa.options_values_id = '".HelperFunctions::escapeSql($order->products[$i]['attributes'][$j]['value_id'])."'
										                                and pa.options_values_id = poval.products_options_values_id
										                                and popt.language_id = '".HelperFunctions::escapeSql($_SESSION['languages_id'])."'
										                                and poval.language_id = '".HelperFunctions::escapeSql($_SESSION['languages_id'])."'";
						$attributes = xtc_db_query($attributes_query);
					} else {
						$attributes = xtc_db_query("select popt.products_options_name,
										                                             poval.products_options_values_name,
										                                             pa.options_values_price,
										                                             pa.price_prefix
										                                             from ".TABLE_PRODUCTS_OPTIONS." popt, ".TABLE_PRODUCTS_OPTIONS_VALUES." poval, ".TABLE_PRODUCTS_ATTRIBUTES." pa
										                                             where pa.products_id = '".HelperFunctions::escapeSql($order->products[$i]['id'])."'
										                                             and pa.options_id = '".HelperFunctions::escapeSql($order->products[$i]['attributes'][$j]['option_id'])."'
										                                             and pa.options_id = popt.products_options_id
										                                             and pa.options_values_id = '".HelperFunctions::escapeSql($order->products[$i]['attributes'][$j]['value_id'])."'
										                                             and pa.options_values_id = poval.products_options_values_id
										                                             and popt.language_id = '".HelperFunctions::escapeSql($_SESSION['languages_id'])."'
										                                             and poval.language_id = '".HelperFunctions::escapeSql($_SESSION['languages_id'])."'");
					}
					// update attribute stock
					xtc_db_query("UPDATE ".TABLE_PRODUCTS_ATTRIBUTES." set
								                               attributes_stock=attributes_stock - '".HelperFunctions::escapeSql($order->products[$i]['qty'])."'
								                               where
								                               products_id='".HelperFunctions::escapeSql($order->products[$i]['id'])."'
								                               and options_values_id='".HelperFunctions::escapeSql($order->products[$i]['attributes'][$j]['value_id'])."'
								                               and options_id='".HelperFunctions::escapeSql($order->products[$i]['attributes'][$j]['option_id'])."'
								                               ");
					
					$attributes_values = xtc_db_fetch_array($attributes);
					
					$sql_data_array = array ('orders_id' => $insert_id, 'orders_products_id' => $order_products_id, 'products_options' => $attributes_values['products_options_name'], 'products_options_values' => $attributes_values['products_options_values_name'], 'options_values_price' => $attributes_values['options_values_price'], 'price_prefix' => $attributes_values['price_prefix']);
					xtc_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $sql_data_array);
					
					if ((DOWNLOAD_ENABLED == 'true') && isset ($attributes_values['products_attributes_filename']) && xtc_not_null($attributes_values['products_attributes_filename'])) {
						$sql_data_array = array ('orders_id' => $insert_id, 'orders_products_id' => $order_products_id, 'orders_products_filename' => $attributes_values['products_attributes_filename'], 'download_maxdays' => $attributes_values['products_attributes_maxdays'], 'download_count' => $attributes_values['products_attributes_maxcount']);
						xtc_db_perform(TABLE_ORDERS_PRODUCTS_DOWNLOAD, $sql_data_array);
					}
				}
			}
			//------insert customer choosen option eof ----
			$total_weight += ($order->products[$i]['qty'] * $order->products[$i]['weight']);
			$total_tax += xtc_calculate_tax($total_products_price, $products_tax) * $order->products[$i]['qty'];
			$total_cost += $total_products_price;
			
		}
		
		if (isset ($_SESSION['tracking']['refID'])) {
		
			xtc_db_query("update ".TABLE_ORDERS." set
			                                 refferers_id = '".HelperFunctions::escapeSql($_SESSION['tracking']['refID'])."'
			                                 where orders_id = '".HelperFunctions::escapeSql($insert_id)."'");
			
			// check if late or direct sale
			$customers_logon_query = "SELECT customers_info_number_of_logons
						                            FROM ".TABLE_CUSTOMERS_INFO." 
						                            WHERE customers_info_id  = '".HelperFunctions::escapeSql($_SESSION['customer_id'])."'";
			$customers_logon_query = xtc_db_query($customers_logon_query);
			$customers_logon = xtc_db_fetch_array($customers_logon_query);
			
			if ($customers_logon['customers_info_number_of_logons'] == 0) {
				// direct sale
				xtc_db_query("update ".TABLE_ORDERS." set
				                                 conversion_type = '1'
				                                 where orders_id = '".HelperFunctions::escapeSql($insert_id)."'");
			} else {
				// late sale
				
				xtc_db_query("update ".TABLE_ORDERS." set
				                                 conversion_type = '2'
				                                 where orders_id = '".HelperFunctions::escapeSql($insert_id)."'");
			}
		
		} else {
			
			$customers_query = xtc_db_query("SELECT refferers_id as ref FROM ".TABLE_CUSTOMERS." WHERE customers_id='".HelperFunctions::escapeSql($_SESSION['customer_id'])."'");
			$customers_data = xtc_db_fetch_array($customers_query);
			if (xtc_db_num_rows($customers_query)) {
				
				xtc_db_query("update ".TABLE_ORDERS." set
				                                 refferers_id = '".HelperFunctions::escapeSql($customers_data['ref'])."'
				                                 where orders_id = '".HelperFunctions::escapeSql($insert_id)."'");
				// check if late or direct sale
				$customers_logon_query = "SELECT customers_info_number_of_logons
							                            FROM ".TABLE_CUSTOMERS_INFO." 
							                            WHERE customers_info_id  = '".HelperFunctions::escapeSql($_SESSION['customer_id'])."'";
				$customers_logon_query = xtc_db_query($customers_logon_query);
				$customers_logon = xtc_db_fetch_array($customers_logon_query);
				
				if ($customers_logon['customers_info_number_of_logons'] == 0) {
					// direct sale
					xtc_db_query("update ".TABLE_ORDERS." set
					                                 conversion_type = '1'
					                                 where orders_id = '".HelperFunctions::escapeSql($insert_id)."'");
				} else {
					// late sale
					
					xtc_db_query("update ".TABLE_ORDERS." set
					                                 conversion_type = '2'
					                                 where orders_id = '".HelperFunctions::escapeSql($insert_id)."'");
				}
			}
		}
		
		$order_total_modules->apply_credit();
		
		$return['orderId'] = $insert_id;
		$return['sofortData'] = $sofortData;
		
		return $return;
	}


	/**
	 * e.g. for cancelation (currently not implemented)
	 */
	public function updateStock($orderId, $articleId, $newStock) {

	}


	/**
	 *  e.g. articles will be deactivated, if stock =< 0 and stock-check is activ -> if articles will be added, aktivate the article (currently not implemented)
	 */
	public function updateSpecialSettings() {

	}


	/**
	 * get and return the array with the sessionData - needed ONLY for insertion of the order by notification
	 */
	public function getSavedSessionData($transactionId, $paymentSecret){
		// Notice: Sessiondata will be inserted into DB in callback/sofort/helperfunctions.php -> insertSofortOrder();
		$query = xtc_db_query( 'SELECT serialized_session FROM sofort_orders WHERE payment_secret = "'.HelperFunctions::escapeSql($paymentSecret).'" AND transaction_id = "'.HelperFunctions::escapeSql($transactionId).'" AND data_acquired = "0"');
		$result = xtc_db_fetch_array($query);
		
		if (isset($result['serialized_session']) && !empty($result['serialized_session'])) {
			xtc_db_query('UPDATE sofort_orders SET data_acquired = "1" WHERE payment_secret = "'.HelperFunctions::escapeSql($paymentSecret).'" AND transaction_id = "'.HelperFunctions::escapeSql($transactionId).'"');
			HelperFunctions::includeOrderTotalFiles();
			return unserialize($result['serialized_session']);
		}else{
			return false;
		}
	}
	
	
	/**
	 * search in the whole shop for the orderId for the given transId
	 * @return orderId OR empty string
	 */
	public function getOrderId($transactionId, $paymentSecret){
		//1st search in table sofort_orders
		$query = xtc_db_query( 'SELECT orders_id FROM sofort_orders WHERE payment_secret = "'.HelperFunctions::escapeSql($paymentSecret).'" AND transaction_id = "'.HelperFunctions::escapeSql($transactionId).'"');
		$result = xtc_db_fetch_array($query);
		if (isset($result['orders_id']) && $result['orders_id']) {
			return $result['orders_id'];
		}else{
			//2nd: if not found, search in table orders
			$query = xtc_db_query('SELECT orders_id FROM '.TABLE_ORDERS.' WHERE orders_ident_key = "'.HelperFunctions::escapeSql($transactionId).'"');
			$result = xtc_db_fetch_array($query);
			return $result['orders_id'];
		}
	}
	
	
	/**
	 * send order-verification to the seller and buyer
	 */
	public function sendOrderEmails($insert_id) {
		//must be set for send_order.php (also $insert_id)
		global $smarty, $order;
		
		if (!is_object($order)) { //$order doesnt exist if called by notification!
			$order = $this->Order;
		}
		
		if (!is_object($smarty)) { //$smarty doesnt exist if called by notification!
			$smarty = $this->Smarty;
		}
		
		if ($order->info['payment_method'] == 'sofort_sofortvorkasse') {
			$sofortVorkasseMailhtml  = "<br/><table style='margin-left:-3px;font-size:x-small;font-family:Verdana, Arial, Helvetica, sans-serif'>";
			$sofortVorkasseMailhtml .= "<tr><td colspan='2'><b>".MODULE_PAYMENT_SOFORT_SV_CHECKOUT_HEADING_TEXT."</b></td></tr>";
			$sofortVorkasseMailhtml .= "<tr><td colspan='2'><br/>".MODULE_PAYMENT_SOFORT_SV_CHECKOUT_TEXT."</td></tr>";
			$sofortVorkasseMailhtml .= "<tr><td>".MODULE_PAYMENT_SOFORT_SV_CHECKOUT_HOLDER_TEXT."</td><td><b>".HelperFunctions::htmlMask($_GET['holder'])."</b></td></tr>";
			$sofortVorkasseMailhtml .= "<tr><td>".MODULE_PAYMENT_SOFORT_SV_CHECKOUT_ACCOUNT_NUMBER_TEXT."</td><td><b>".HelperFunctions::htmlMask($_GET['account_number'])."</b></td></tr>";
			$sofortVorkasseMailhtml .= "<tr><td>".MODULE_PAYMENT_SOFORT_SV_CHECKOUT_IBAN_TEXT."</td><td><b>".HelperFunctions::htmlMask($_GET['iban'])."</b></td></tr>";
			$sofortVorkasseMailhtml .= "<tr><td>".MODULE_PAYMENT_SOFORT_SV_CHECKOUT_BANK_CODE_TEXT."</td><td><b>".HelperFunctions::htmlMask($_GET['bank_code'])."</b></td></tr>";
			$sofortVorkasseMailhtml .= "<tr><td>".MODULE_PAYMENT_SOFORT_SV_CHECKOUT_BIC_TEXT."</td><td><b>".HelperFunctions::htmlMask($_GET['bic'])."</b></td></tr>";
			$sofortVorkasseMailhtml .= "<tr><td>".MODULE_PAYMENT_SOFORT_SV_CHECKOUT_AMOUNT_TEXT."</td><td><b>".number_format(HelperFunctions::htmlMask($_GET['amount']),2,',','.'). ' &euro;'."</b></td></tr>";
			$sofortVorkasseMailhtml .= "<tr><td>".MODULE_PAYMENT_SOFORT_SV_CHECKOUT_REASON_1_TEXT."</td><td><b>".HelperFunctions::htmlMask($_GET['reason_1'])."</b></td></tr>";
			$sofortVorkasseMailhtml .= "<tr><td>".MODULE_PAYMENT_SOFORT_SV_CHECKOUT_REASON_2_TEXT."</td><td><b>".HelperFunctions::htmlMask($_GET['reason_2'])."</b></td></tr>";
			$sofortVorkasseMailhtml .= "<tr><td colspan='2'><span style='color:red;'><br/><b>".MODULE_PAYMENT_SOFORT_SV_CHECKOUT_REASON_HINT."</b></span></td></tr></table>";
			

			$sofortVorkasseMailtext  = "\n".MODULE_PAYMENT_SOFORT_SV_CHECKOUT_HEADING_TEXT."\n\n";

			$sofortVorkasseMailtext .= MODULE_PAYMENT_SOFORT_SV_CHECKOUT_TEXT."\n\n";

			$sofortVorkasseMailtext .= MODULE_PAYMENT_SOFORT_SV_CHECKOUT_HOLDER_TEXT."\n"		 .HelperFunctions::htmlMask($_GET['holder'])."\n\n";

			$sofortVorkasseMailtext .= MODULE_PAYMENT_SOFORT_SV_CHECKOUT_ACCOUNT_NUMBER_TEXT."\n".HelperFunctions::htmlMask($_GET['account_number'])."\n\n";

			$sofortVorkasseMailtext .= MODULE_PAYMENT_SOFORT_SV_CHECKOUT_IBAN_TEXT."\n"			 .HelperFunctions::htmlMask($_GET['iban'])."\n\n";

			$sofortVorkasseMailtext .= MODULE_PAYMENT_SOFORT_SV_CHECKOUT_BANK_CODE_TEXT."\n"	 .HelperFunctions::htmlMask($_GET['bank_code'])."\n\n";

			$sofortVorkasseMailtext .= MODULE_PAYMENT_SOFORT_SV_CHECKOUT_BIC_TEXT."\n"			 .HelperFunctions::htmlMask($_GET['bic'])."\n\n";

			$sofortVorkasseMailtext .= MODULE_PAYMENT_SOFORT_SV_CHECKOUT_AMOUNT_TEXT."\n"		 .HelperFunctions::htmlMask($_GET['amount']). ' EUR'."\n\n";

			$sofortVorkasseMailtext .= MODULE_PAYMENT_SOFORT_SV_CHECKOUT_REASON_1_TEXT."\n"		 .HelperFunctions::htmlMask($_GET['reason_1'])."\n\n";

			$sofortVorkasseMailtext .= MODULE_PAYMENT_SOFORT_SV_CHECKOUT_REASON_2_TEXT."\n"		 .HelperFunctions::htmlMask($_GET['reason_2'])."\n\n";
			$sofortVorkasseMailtext .= MODULE_PAYMENT_SOFORT_SV_CHECKOUT_REASON_HINT."\n";

			

			$smarty->assign('PAYMENT_INFO_HTML', $sofortVorkasseMailhtml);

			$smarty->assign('PAYMENT_INFO_TXT', $sofortVorkasseMailtext);
		}
		
		include ('send_order.php');
		
		return true;
	}


	public function deleteSofortSessionData() {
		if (isset($_SESSION['sofort']['sofort_conditions_sr']))
			unset($_SESSION['sofort']['sofort_conditions_sr']);
		if (isset($_SESSION['sofort']['sofort_conditions_sv']))
			unset($_SESSION['sofort']['sofort_conditions_sv']);
		if (isset($_SESSION['sofort']['sofort_conditions_ls']))
			unset($_SESSION['sofort']['sofort_conditions_ls']);
		if (isset($_SESSION['sofort']['ls_sender_holder']))
			unset($_SESSION['sofort']['ls_sender_holder']);
		if (isset($_SESSION['sofort']['ls_account_number']))
			unset($_SESSION['sofort']['ls_account_number']);
		if (isset($_SESSION['sofort']['ls_bank_code']))
			unset($_SESSION['sofort']['ls_bank_code']);
	}


	/**
	 * l�schen aller session-daten, warenkorb leeren u.�.
	 */
	public function deleteShopSessionData() {
		global $order_total_modules;
		
		$_SESSION['cart']->reset(true);
		
		if (isset($_SESSION['sendto'])) 		unset ($_SESSION['sendto']);
		if (isset($_SESSION['billto'])) 		unset ($_SESSION['billto']);
		if (isset($_SESSION['shipping'])) 		unset ($_SESSION['shipping']);
		if (isset($_SESSION['payment'])) 		unset ($_SESSION['payment']);
		if (isset($_SESSION['comments'])) 		unset ($_SESSION['comments']);
		if (isset($_SESSION['last_order']))		unset ($_SESSION['last_order']);
		if (isset($_SESSION['tmp_oID'])) 		unset ($_SESSION['tmp_oID']);
		if (isset($_SESSION['cc'])) 			unset ($_SESSION['cc']);
		if (isset($_SESSION['cc_id'])) 			unset ($_SESSION['cc_id']);
		if (isset ($_SESSION['credit_covers']))	unset ($_SESSION['credit_covers']);
		
		//GV Code Start
		
		if (is_object($order_total_modules)) {
			$order_total_modules->clear_posts(); //ICW ADDED FOR CREDIT CLASS SYSTEM
		}
		// GV Code End
		
		//modified eCommerce Shopsoftware Start
		if(@isset($_SESSION['xtb0']))
		{
			define('XTB_CHECKOUT_PROCESS', __LINE__);
			require_once (DIR_FS_CATALOG.'callback/xtbooster/xtbcallback.php');
		}
		//modified eCommerce Shopsoftware End
	}
	
	
	/**
	 * set an empty string to orders_sofort-table, where the serialized session was saved - field orders_id must be set before!
	 * @return always true
	 */
	public function deleteSavedSessionFromDb($transactionId, $paymentSecret){
		xtc_db_query('UPDATE sofort_orders
					  SET serialized_session = ""
					  WHERE payment_secret = "'.HelperFunctions::escapeSql($paymentSecret).'"
					  AND orders_id != "0"
					  AND transaction_id = "'.HelperFunctions::escapeSql($transactionId).'"');
		return true;
	}
	
	
	/**
	 * set the given orderId into orders_sofort-table to the affected dataset
	 * @return always true
	 */
	public function insertOrderIdInSofortTables($transactionId, $paymentSecret, $newOrderId){
		xtc_db_query('UPDATE sofort_orders SET orders_id =  "'.HelperFunctions::escapeSql($newOrderId).'" WHERE payment_secret = "'.HelperFunctions::escapeSql($paymentSecret).'" AND transaction_id = "'.HelperFunctions::escapeSql($transactionId).'"');
		
		return true;
	}
	
	
	/**
	 * saved info about the bought article-attributes for later article-sync
	 */
	public function insertOrderAttributesInSofortTables($ordersId, $sofortData) {
		foreach($sofortData as $oneDbEntry) {
			$sql_data_array = array (
				'orders_id' => $ordersId,
				'item_id' => $oneDbEntry['sofortItemId'],
				'orders_products_id' => $oneDbEntry['sofortOrderProductsId'],
			);
			xtc_db_perform('sofort_products', $sql_data_array);
		}
		return true;
	}
	
	/**
	 * set the given orderId into orders-table to the affected dataset
	 * @return always true
	 */
	public function insertTransIdInTableOrders($transactionId, $orderId) {
		xtc_db_query('UPDATE '.HelperFunctions::escapeSql(TABLE_ORDERS).' SET orders_ident_key=\''.HelperFunctions::escapeSql($transactionId).'\' WHERE orders_id=\''.HelperFunctions::escapeSql($orderId).'\'');
		return true;
	}
	
	
	/**
	 * set the given sessionData into $_SESSION and set $GLOBALS
	 * use ONLY, if order is inserted by notification (not by success-URL)
	 * @param array $sessionData must contain a copy of $_SESSION and the sofort-Sessiondata
	 * @return always true
	 */
	public function restoreGivenSessionDataToSession($sessionData) {
		$_SESSION = $sessionData['session'];
		foreach ($sessionData['globals']['GLOBALS'] as $key => $value) {
			$GLOBALS[$key] = $value;
		}
		$this->Order = $sessionData['order'];
		$this->Smarty = $sessionData['smarty'];
		$this->orderTotals = $sessionData['orderTotals'];
		$this->orderTotalModules = $sessionData['orderTotalModules'];

		return true;
	}
	
	
	/**
	 * do something, after order was successfully inserted (xtc3 + modified-shop: nothing to do here)
	 */
	public function doSpecialThingsAfterSuccessfulInsertion() {
		return true;
	}
}
?>