<?php
/**
 * @version sofortüberweisung.de 4.1.0 - $Date: 2010-09-09 17:18:09 +0200 (Do, 09 Sep 2010) $
 * @author Payment Network AG (integration@payment-network.com)
 * @link http://www.payment-network.com/
 *
 * @copyright 2006 - 2007 Henri Schmidhuber
 * @link http://www.in-solution.de
 *
 * @link http://www.xt-commerce.com
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 2 of the License
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 *
 ***********************************************************************************
 * this file contains code based on:
 * (c) 2000 - 2001 The Exchange Project
 * (c) 2001 - 2003 osCommerce, Open Source E-Commerce Solutions
 * (c) 2003	 nextcommerce (account_history_info.php,v 1.17 2003/08/17); www.nextcommerce.org
 * (c) 2003 - 2006 XT-Commerce
 * Released under the GNU General Public License
 ***********************************************************************************
 *
 * $Id: pn_sofortueberweisung.php 304 2010-09-09 15:18:09Z poser $
 *
 */

require_once(DIR_FS_CATALOG.'callback/pn_sofortueberweisung/classPnSofortueberweisung.php');

class pn_sofortueberweisung {

	var $code, $title, $description, $enabled, $pnSofortueberweisung;
	function pn_sofortueberweisung () {
		global $order;
		$this->code = 'pn_sofortueberweisung';
		$this->version = 'pn_modified_v1.06';
		$this->title = MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_TITLE;
		if(MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_KS_STATUS == 'True') {
			$this->title = MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_KS_TEXT_TITLE;
			$this->version .= 'k';
		}
			
		$this->description = MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_DESCRIPTION;
		$this->sort_order = MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_SORT_ORDER;
		$this->enabled = ((MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_STATUS == 'True') ? true : false);
		$this->info = MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_INFO;
		$this->tmpOrders = true;
		$this->tmpStatus = MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TMP_STATUS_ID;
		if ((int) MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_ORDER_STATUS_ID > 0) {
			$this->order_status = MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_ORDER_STATUS_ID;
		}
		if (is_object($order))
			$this->update_status();


		$this->defaultCurrency = DEFAULT_CURRENCY;
		$this->pnSofortueberweisung = new classPnSofortueberweisung(MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_PROJECT_PASSWORD, MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_HASH_ALGORITHM);
		$this->form_action_url = $this->pnSofortueberweisung->formActionUrl;
		$this->pnSofortueberweisung->version = $this->version; 

	}
	function update_status ()
	{
		global $order;
		if (($this->enabled == true) && ((int) MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_ZONE > 0)) {
			$check_flag = false;
			$check_query = xtc_db_query("SELECT zone_id FROM " . TABLE_ZONES_TO_GEO_ZONES . " WHERE geo_zone_id = '" . MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' ORDER BY zone_id");
			while ($check = xtc_db_fetch_array($check_query)) {
				if ($check['zone_id'] < 1) {
					$check_flag = true;
					break;
				} elseif ($check['zone_id'] == $order->billing['zone_id']) {
					$check_flag = true;
					break;
				}
			}
			if ($check_flag == false) {
				$this->enabled = false;
			}
		}
	}
	function javascript_validation () {
		return false;
	}
	function selection () {
		/* If temporary order is still in session, check if order ID exists and delete order and all relating (session) data
		 * User might have returned to the shop for changing the order or payment method
		 */
		if (! empty($_SESSION['cart_pn_sofortueberweisung_ID'])) {
			$order_id = substr($_SESSION['cart_pn_sofortueberweisung_ID'], strpos($_SESSION['cart_pn_sofortueberweisung_ID'], '-') + 1);
			$check_query = xtc_db_query('SELECT orders_status FROM ' . TABLE_ORDERS . ' WHERE orders_id = "' . (int) $order_id . '" LIMIT 1');
			if ($result = xtc_db_fetch_array($check_query)) {
				if ($result['orders_status'] == MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TMP_STATUS_ID) {
					$this->_remove_order( (int) $order_id, 'on');
					unset($_SESSION['cart_pn_sofortueberweisung_ID']);
					unset($_SESSION['tmp_oID']);
				}
			}
		}
		$title = '';
		switch (MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_IMAGE) {
			case 'Logo & Text':
				$image = xtc_image(sprintf('lang/%s/modules/payment/images/sofortueberweisung_logo.gif', $_SESSION['language']), MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGEALT);
				if(MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_KS_STATUS == 'True')
					$title = str_replace('{{image}}', $image, sprintf(MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGE, MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_KS_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_TEXT));
				else
					$title = str_replace('{{image}}', $image, sprintf(MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGE, MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_TEXT));
				break;
			case 'Logo':
				$image = xtc_image(sprintf('lang/%s/modules/payment/images/sofortueberweisung_logo.gif', $_SESSION['language']), MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGEALT);
				$title = str_replace('{{image}}', $image, sprintf(MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGE, ''));
				break;
			case 'Infographic':
				if(MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_KS_STATUS == 'True')
					$image = xtc_image(sprintf('lang/%s/modules/payment/images/sofortueberweisungk_banner.jpg', $_SESSION['language']), MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGEALT);
				else
					$image = xtc_image(sprintf('lang/%s/modules/payment/images/sofortueberweisung_info.gif', $_SESSION['language']), MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGEALT);
				$title = str_replace('{{image}}', $image, sprintf(MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGE, ''));
				break;
		}
		return array('id' => $this->code , 'module' => $this->title , 'description' => $title);
	}
	function pre_confirmation_check () {
		// Fix for XTC Bug
		// We need a cartID
		if (empty($_SESSION['cart']->cartID)) {
			$_SESSION['cart']->cartID = $_SESSION['cart']->generate_cart_id();
		}
		return false;
	}
	function confirmation () {
		global $order;

		/* If temporary order is still in session, check if order ID exists and delete order and all relating (session) data
		 * User might have returned to the shop for changing the order or payment method
		 */
		if (! empty($_SESSION['cart_pn_sofortueberweisung_ID'])) {
			$order_id = substr($_SESSION['cart_pn_sofortueberweisung_ID'], strpos($_SESSION['cart_pn_sofortueberweisung_ID'], '-') + 1);
			$cartID = substr($_SESSION['cart_pn_sofortueberweisung_ID'], 0, strlen($_SESSION['cart']->cartID));
			$check_query = xtc_db_query("SELECT currency, orders_status FROM " . TABLE_ORDERS . " WHERE orders_id = '" . (int) $order_id . "'");
			$result = xtc_db_fetch_array($check_query);
			if (($result['orders_status'] == MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TMP_STATUS_ID) || ($result['currency'] != $order->info['currency']) || ($_SESSION['cart']->cartID != $cartID)) {
				$this->_remove_order( (int) $order_id, 'on');
				unset($_SESSION['cart_pn_sofortueberweisung_ID']);
				unset($_SESSION['tmp_oID']);
			}
		}
		return false;
	}
	function process_button () {
		return false;
	}
	function before_process () {
		return false;
	}
	function payment_action () {
		global $order, $xtPrice, $insert_id;

		$customer_id = $_SESSION['customer_id'];
		$order_id = $insert_id;
		$_SESSION['cart_pn_sofortueberweisung_ID'] = $_SESSION['cart']->cartID . '-' . $insert_id;

		if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
			$total = $order->info['total'] + $order->info['tax'];
		} else {
			$total = $order->info['total'];
		}
		// Fix for XTC Bug
		// $order->info['total'] is in 'before_process' String without Tax, after email it is TEXT with currency
		// so it has to be set here

		
		$amount = round($total, $xtPrice->get_decimal_places($_SESSION['currency']));
		$amount = number_format($amount, 2, '.', '');
		
		$_SESSION['sofortueberweisung_total'] = $amount;
		$parameter = array();
		$currency = $_SESSION['currency'];

		$reason_1 = str_replace('{{order_id}}', $order_id, MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_REASON_1);
		$reason_1 = str_replace('{{customer_id}}', $customer_id, $reason_1);
		$reason_1 = substr($reason_1, 0, 27);
		
		$reason_2 = str_replace('{{order_id}}', $order_id, MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_REASON_2);
		$reason_2 = str_replace('{{customer_id}}', $customer_id, $reason_2);
		$reason_2 = str_replace('{{order_date}}', strftime(DATE_FORMAT_SHORT), $reason_2);
		$reason_2 = str_replace('{{customer_name}}', $order->customer['firstname'] . ' ' . $order->customer['lastname'], $reason_2);
		$reason_2 = str_replace('{{customer_company}}', $order->customer['company'], $reason_2);
		$reason_2 = str_replace('{{customer_email}}', $order->customer['email_address'], $reason_2);
		$reason_2 = substr($reason_2, 0, 27);



		$user_variable_0 = $order_id;
		$user_variable_1 = $customer_id;

		$session = session_name() . '=' . session_id();

		if (ENABLE_SSL == true)
			$server = HTTPS_SERVER;
		else
			$server = HTTP_SERVER;
			
		$server = str_replace('https://', '', $server);
		$server = str_replace('http://', '', $server);
		

		// success return url:
		$user_variable_2 = $server . DIR_WS_CATALOG . FILENAME_CHECKOUT_PROCESS . '?' . $session;

		// cancel return url:
		$user_variable_3 = $server . DIR_WS_CATALOG . FILENAME_CHECKOUT_PAYMENT . '?payment_error=pn_sofortueberweisung&' . $session;

		// notification url: 
		$user_variable_4 = $server . DIR_WS_CATALOG . 'callback/pn_sofortueberweisung/callback.php'; //deprecated

		$user_variable_5 = $_SESSION['cart']->cartID;
		

		// Additionally update status
		$sql_data_array = array('orders_id' => (int) $order_id , 'orders_status_id' => MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TMP_STATUS_ID , 'date_added' => 'now()' , 'customer_notified' => '0' , 'comments' => MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_TITLE);
		xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

		$url = $this->pnSofortueberweisung->getPaymentUrl(MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_USER_ID, 
			MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_PROJECT_ID, $amount, $currency, $reason_1, $reason_2, 
			$user_variable_0, $user_variable_1, $user_variable_2, $user_variable_3, $user_variable_4, $user_variable_5);

		xtc_redirect($url);
	}
	function after_process () {
		/* Clear our session data
		 * All other session data will be handled in checkout_process.php
		 */
		if (isset($_SESSION)) {
			if (isset($_SESSION['cart_pn_sofortueberweisung_ID'])) 
				unset($_SESSION['cart_pn_sofortueberweisung_ID']);
			if (isset($_SESSION['sofortueberweisung_total'])) 
				unset($_SESSION['sofortueberweisung_total']);
		}
	}
	function output_error (){
		return false;
	}
	function check () {
		if (!isset($this->_check)) {
			$check_query = xtc_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_STATUS'");
			$this->_check = xtc_db_num_rows($check_query);

			//if old installation and notification password not set we need to upgrade the database
			if (defined('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_STATUS')	&& (MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_STATUS == 'True')) {
				if(!defined('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_PROJECT_NOTIF_PASSWORD')) {
					$check_query = xtc_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_PROJECT_NOTIF_PASSWORD'");
					if(xtc_db_num_rows($check_query) < 1) {
						xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_PROJECT_NOTIF_PASSWORD', '".MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_PROJECT_PASSWORD."',  '6', '4', now())");
						xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_HASH_ALGORITHM', 'sha1',  '6', '4', now())");
					}
				}
				if(!defined('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_RECEIVED_STATUS_ID')) {
					xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_RECEIVED_STATUS_ID', '0',  '6', '11', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
					xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_LOSS_STATUS_ID', '0',  '6', '12', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
					xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_KS_STATUS', 'False', '6', '3', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
				}
			}
		}
		return $this->_check;
	}
	function get_error () {
		$error = false;
		if (! empty($_GET['payment_error'])) {
			$error = array('title' => MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_ERROR_HEADING , 'error' => MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_ERROR_MESSAGE);
		}
		return $error;
	}

	function autoinstall() {
		$backlink = xtc_href_link(FILENAME_MODULES, 'set=payment&module=pn_sofortueberweisung&action=install', 'SSL');
		
		$header_redir_url = 'http://-USER_VARIABLE_2-';
		if (ENABLE_SSL_CATALOG == 'true' && strpos(HTTPS_CATALOG_SERVER, 'tps://') === 2) {
			$header_redir_url = 'https://-USER_VARIABLE_2-'; //
		}
		$html_abortlink = 'http://-USER_VARIABLE_3-';
		if (ENABLE_SSL_CATALOG == 'true' && strpos(HTTPS_CATALOG_SERVER, 'tps://') === 2) {
			$html_abortlink = 'https://-USER_VARIABLE_3-'; //
		}
		$alert_http_url = HTTP_SERVER . DIR_WS_CATALOG . 'callback/pn_sofortueberweisung/callback.php';
		if (ENABLE_SSL_CATALOG == 'true' && strpos(HTTPS_CATALOG_SERVER, 'tps://') === 2) {
			$alert_http_url = HTTPS_SERVER . DIR_WS_CATALOG . 'callback/pn_sofortueberweisung/callback.php';
		}

		
		$html = $this->pnSofortueberweisung->getAutoInstallPage(STORE_NAME, xtc_catalog_href_link(), STORE_OWNER_EMAIL_ADDRESS, DEFAULT_LANGUAGE,
			DEFAULT_CURRENCY, $html_abortlink, $header_redir_url, $alert_http_url, $backlink, 208);
			
		$_SESSION['pn_sofortueberweisung_pw'] = $this->pnSofortueberweisung->password;
		$_SESSION['pn_sofortueberweisung_pw2'] = $this->pnSofortueberweisung->password2;
		$_SESSION['pn_sofortueberweisung_hashAlgorithm'] = $this->pnSofortueberweisung->hashfunction;
		
		return $html;
	}
	
	function install () {

		if (isset($_GET['autoinstall']) && ($_GET['autoinstall'] == '1')) {
			// Module already installed
			if (defined('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_STATUS') && (MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_STATUS == 'True')) {
				xtc_redirect(xtc_href_link_admin('admin/modules.php', 'set=payment&module=pn_sofortueberweisung', 'SSL'));
			}
			print $this->autoinstall();
			exit();
		} else {

			$user_id = (! empty($_GET['user_id'])) ? xtc_db_prepare_input($_GET['user_id']) : '10000';
			$project_id = (! empty($_GET['project_id'])) ? xtc_db_prepare_input($_GET['project_id']) : '500000';
			if(!empty($_GET['consumer_protection']) && xtc_db_prepare_input($_GET['consumer_protection']) == '1') {
				$consumer_protection = 'True';
			} else {
				$consumer_protection = 'False';
			}
			
			if (isset($_SESSION['pn_sofortueberweisung_pw']) && !empty($_SESSION['pn_sofortueberweisung_pw'])) {
				$project_password = $_SESSION['pn_sofortueberweisung_pw'];
				unset($_SESSION['pn_sofortueberweisung_pw']);
			} else $project_password = '';
			if (isset($_SESSION['pn_sofortueberweisung_pw2']) && !empty($_SESSION['pn_sofortueberweisung_pw2'])) {
				$project_password2 = $_SESSION['pn_sofortueberweisung_pw2'];
				unset($_SESSION['pn_sofortueberweisung_pw2']);
			} else $project_password2 = '';
			if (isset($_SESSION['pn_sofortueberweisung_hashAlgorithm']) && !empty($_SESSION['pn_sofortueberweisung_hashAlgorithm'])) {
				$hashAlgorithm = $_SESSION['pn_sofortueberweisung_hashAlgorithm'];
				unset($_SESSION['pn_sofortueberweisung_hashAlgorithm']);
			} else $hashAlgorithm = $this->pnSofortueberweisung->getSupportedHashAlgorithm();

			
			xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_STATUS', 'True', '6', '3', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
			xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_KS_STATUS', '".$consumer_protection."', '6', '3', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
			xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_ALLOWED', '', '6', '0', now())");
			xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_USER_ID', '" . (int) $user_id . "',  '6', '4', now())");
			xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_PROJECT_ID', '" . (int) $project_id . "',  '6', '4', now())");
			xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_PROJECT_PASSWORD', '". $project_password ."',  '6', '4', now())");
			xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_PROJECT_NOTIF_PASSWORD', '". $project_password2 ."',  '6', '4', now())");
			xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_HASH_ALGORITHM', '". $hashAlgorithm ."',  '6', '4', now())");
			xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_SORT_ORDER', '1', '6', '20', now())");
			xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_ZONE', '0', '6', '2', 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(', now())");
			xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_ORDER_STATUS_ID', '0',  '6', '10', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
			xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TMP_STATUS_ID', '0',  '6', '8', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
			xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_UNC_STATUS_ID', '0',  '6', '9', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
			xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_RECEIVED_STATUS_ID', '0',  '6', '11', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
			xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_LOSS_STATUS_ID', '0',  '6', '12', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
			xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_REASON_1', 'Nr. {{order_id}} Kd-Nr. {{customer_id}}',  '6', '4', 'xtc_cfg_select_option(array(\'Nr. {{order_id}} Kd-Nr. {{customer_id}}\',\'-TRANSACTION-\'), ', now())");
			xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_REASON_2', '" . addslashes(STORE_NAME) . "', '6', '4', now())");
			xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_IMAGE', 'Infographic',  '6', '6', 'xtc_cfg_select_option(array(\'Infographic\',\'Logo & Text\',\'Logo\'), ', now())");
		}
	}
	
	function remove () {
		xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . implode("', '", $this->keys()) . "')");
		xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_HASH_ALGORITHM'");
	}
	
	function keys () {
		return array('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_STATUS' ,
		'MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_KS_STATUS', 
		'MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_ALLOWED' , 
		'MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_USER_ID' , 
		'MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_PROJECT_ID' , 
		'MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_PROJECT_PASSWORD',  
		'MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_PROJECT_NOTIF_PASSWORD', 
		'MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_ZONE' , 
		'MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_REASON_1', 
		'MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_REASON_2' , 
		'MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_IMAGE' , 
		'MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TMP_STATUS_ID' , 
		'MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_UNC_STATUS_ID' , 
		'MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_ORDER_STATUS_ID' , 
		'MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_RECEIVED_STATUS_ID',
		'MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_LOSS_STATUS_ID',
		'MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_SORT_ORDER');
	}

	// xtc_remove_order() in admin/includes/functions/general.php
	// mods by Gambio
	function _remove_order($order_id, $restock = false, $canceled = false) {
		if ($restock == 'on') {
			// BOF GM_MOD:
			$order_query = xtc_db_query("select orders_products_id, products_id, products_quantity from ".TABLE_ORDERS_PRODUCTS." where orders_id = '".xtc_db_input($order_id)."'");
			while ($order = xtc_db_fetch_array($order_query)) {
				xtc_db_query("update ".TABLE_PRODUCTS." set products_quantity = products_quantity + ".$order['products_quantity'].", products_ordered = products_ordered - ".$order['products_quantity']." where products_id = '".$order['products_id']."'");
				// BOF GM_MOD
				if(ATTRIBUTE_STOCK_CHECK == 'true'){
					$gm_get_orders_attributes = xtc_db_query("SELECT
																											products_options, 
																											products_options_values 
																										FROM orders_products_attributes 
																										WHERE orders_id = '" . xtc_db_input($order_id) . "'
																										AND orders_products_id = '" . $order['orders_products_id'] . "'");
					while($gm_orders_attributes = xtc_db_fetch_array($gm_get_orders_attributes)) {
						$gm_get_attributes_id = xtc_db_query("SELECT
																										pa.products_attributes_id	
																									FROM 
																										products_options_values pov, 
																										products_options po, 
																										products_attributes pa 
																									WHERE 
																										po.products_options_name = '" . $gm_orders_attributes['products_options'] . "'
																										AND po.products_options_id = pa.options_id
																										AND pov.products_options_values_id = pa.options_values_id
																										AND pov.products_options_values_name = '" . $gm_orders_attributes['products_options_values'] . "'
																										AND pa.products_id = '" . $order['products_id'] . "'
																									LIMIT 1");
						if(xtc_db_num_rows($gm_get_attributes_id) == 1){
							$gm_attributes_id = xtc_db_fetch_array($gm_get_attributes_id);
							xtc_db_query("UPDATE products_attributes
														SET attributes_stock = attributes_stock + ".$order['products_quantity']." 
														WHERE products_attributes_id = '" . $gm_attributes_id['products_attributes_id'] . "'");
						}
					}
				}
				// EOF GM_MOD
			}
		}

		if(!$canceled) {
			xtc_db_query("delete from ".TABLE_ORDERS." where orders_id = '".xtc_db_input($order_id)."'");
			xtc_db_query("delete from ".TABLE_ORDERS_PRODUCTS." where orders_id = '".xtc_db_input($order_id)."'");
			xtc_db_query("delete from ".TABLE_ORDERS_PRODUCTS_ATTRIBUTES." where orders_id = '".xtc_db_input($order_id)."'");
			xtc_db_query("delete from ".TABLE_ORDERS_STATUS_HISTORY." where orders_id = '".xtc_db_input($order_id)."'");
			xtc_db_query("delete from ".TABLE_ORDERS_TOTAL." where orders_id = '".xtc_db_input($order_id)."'");
		}
	}
}

?>
