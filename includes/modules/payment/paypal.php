<?php
/* -----------------------------------------------------------------------------------------
   $Id: paypal.php 2030 2011-07-07 15:27:00Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project (earlier name of osCommerce)
   (c) 2002-2003 osCommerce; www.oscommerce.com
   (c) 2007 XT-Commerce (paypal.php)
   @license http://www.xt-commerce.com.com/license/2_0.txt GNU Public License V2.0

   ab 15.08.2008 Teile vom Hamburger-Internetdienst geändert
   Hamburger-Internetdienst Support Forums at www.forum.hamburger-internetdienst.de
   Stand 04.03.2012
*/
class paypal {
	var $code, $title, $description, $enabled;
/**************************************************************/
	// BOF - Hendrik - 2010-08-11 - php5 compatible
	//function paypal() {
	function __construct() {
	// EOF - Hendrik - 2010-08-11 - php5 compatible
		// Stand: 29.04.2009
		global $order;
		$this->code = 'paypal';
		$this->title = MODULE_PAYMENT_PAYPAL_TEXT_TITLE;
		$this->description = MODULE_PAYMENT_PAYPAL_TEXT_DESCRIPTION;
		if(MODULE_PAYMENT_PAYPAL_SORT_ORDER!=''){
			$this->sort_order = MODULE_PAYMENT_PAYPAL_SORT_ORDER;
		}else{
			$this->sort_order = 2;
		}
		$this->enabled = ((MODULE_PAYMENT_PAYPAL_STATUS == 'True') ? true : false);
		$this->info = MODULE_PAYMENT_PAYPAL_TEXT_INFO;
		$this->order_status_success = PAYPAL_ORDER_STATUS_SUCCESS_ID;
		$this->order_status_rejected = PAYPAL_ORDER_STATUS_REJECTED_ID;
		$this->order_status_pending = PAYPAL_ORDER_STATUS_PENDING_ID;
		$this->order_status_tmp = PAYPAL_ORDER_STATUS_TMP_ID;
		$this->tmpOrders = false;
		$this->debug = true;
		$this->tmpStatus = PAYPAL_ORDER_STATUS_TMP_ID;
		if(is_object($order))
			$this->update_status();
	}
/**************************************************************/
	function update_status() {
		// Stand: 29.04.2009
		global $order;
		if(($this->enabled == true) && ((int) MODULE_PAYMENT_PAYPAL_ZONE > 0)) {
			$check_flag = false;
			$check_query = xtc_db_query("select zone_id from ".TABLE_ZONES_TO_GEO_ZONES." where geo_zone_id = '".MODULE_PAYMENT_PAYPAL_ZONE."' and zone_country_id = '".$order->billing['country']['id']."' order by zone_id");
			while($check = xtc_db_fetch_array($check_query)) {
				if($check['zone_id'] < 1) {
					$check_flag = true;
					break;
				} elseif($check['zone_id'] == $order->billing['zone_id']) {
					$check_flag = true;
					break;
				}
			}
			if($check_flag == false) {
				$this->enabled = false;
			}
		}
	}
/**************************************************************/
	function javascript_validation() {
		// Stand: 29.04.2009
		return false;
	}
/**************************************************************/
	function selection() {
		// Stand: 29.04.2009
		return array('id' => $this->code, 'module' => $this->title, 'description' => $this->info);
	}
/**************************************************************/
	function pre_confirmation_check() {
		// Stand: 29.04.2009
		return false;
	}
/**************************************************************/
  function confirmation() {
    // Stand: 19.07.2010
    return array ('title' => $this->description);
  }
/**************************************************************/
	function process_button() {
		// Stand: 29.04.2009
		return false;
	}
/**************************************************************/
	function before_process() {
		// Stand: 29.04.2009
		// Bereits geholt und bestätigt
		if($_GET['PayerID']!='' AND $_SESSION['reshash']['TOKEN']!='' AND (strtoupper($_SESSION['reshash']["ACK"])=="SUCCESS" OR strtoupper($_SESSION['reshash']["ACK"])=="SUCCESSWITHWARNING"))
			return;
		// Den PayPal Token holen, ohne das commit keine Preisanzeige !
		global $order, $o_paypal;
		$o_paypal->paypal_auth_call();
		// Gleich auf Bezahl-Bestätigt setzten bei PayPal
		xtc_redirect($o_paypal->payPalURL.'&useraction=commit');
		return;
	}
/**************************************************************/
	function payment_action() {
		// Stand: 29.04.2009
		global $order, $o_paypal, $tmp, $insert_id;
		$tmp = false;
		return;
	}
/**************************************************************/
	function after_process() {
		// Stand: 29.04.2009
		global $order, $insert_id, $o_paypal;
		$o_paypal->complete_ceckout($insert_id, $_GET);
		$o_paypal->write_status_history($insert_id);
		$o_paypal->logging_status($insert_id);
		return;
	}
/**************************************************************/
	function giropay_process() {
		// Stand: 29.04.2009
		global $o_paypal;
		$o_paypal->giropay_confirm($_GET);
		return;
	}
/**************************************************************/
	function output_error() {
		// Stand: 29.04.2009
		return false;
	}
/**************************************************************/
	function check() {
		// Stand: 29.04.2009
		if(!isset($this->_check)) {
			$check_query = xtc_db_query("select configuration_value from ".TABLE_CONFIGURATION." where configuration_key = 'MODULE_PAYMENT_PAYPAL_STATUS'");
			$this->_check = xtc_db_num_rows($check_query);
		}
		return $this->_check;
	}
/**************************************************************/
	function admin_order($oID) {
		// Stand: 29.04.2009
		return false;
	}
/**************************************************************/
	function install() {
		// Stand: 04.03.2012
		if(!defined('TABLE_PAYPAL'))define('TABLE_PAYPAL', 'paypal');
		if(!defined('TABLE_PAYPAL_STATUS_HISTORY'))define('TABLE_PAYPAL_STATUS_HISTORY', 'paypal_status_history');
		// nur zur Sicherheit falls noch alte Module da sind...
		$this->remove(1);
		// Bestell Status prüfen oder erfassen
		$stati=array(
					'PAYPAL_INST_ORDER_STATUS_TMP_NAME'=>'PAYPAL_INST_ORDER_STATUS_TMP_ID',
					'PAYPAL_INST_ORDER_STATUS_SUCCESS_NAME'=>'PAYPAL_INST_ORDER_STATUS_SUCCESS_ID',
					'PAYPAL_INST_ORDER_STATUS_PENDING_NAME'=>'PAYPAL_INST_ORDER_STATUS_PENDING_ID',
					'PAYPAL_INST_ORDER_STATUS_REJECTED_NAME'=>'PAYPAL_INST_ORDER_STATUS_REJECTED_ID');
		foreach($stati as $statusname=>$statusid) {
			$languages_query = xtc_db_query("select * from " . TABLE_LANGUAGES . " order by sort_order");
			while($languages = xtc_db_fetch_array($languages_query)) {
				if(file_exists(DIR_FS_LANGUAGES.$languages['directory'].'/admin/paypal.php'))
					include(DIR_FS_LANGUAGES.$languages['directory'].'/admin/paypal.php');
				if($$statusname!=''):
					$check_query = xtc_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name = '" .$$statusname. "' AND language_id='".$languages['languages_id']."' limit 1");
					$status = xtc_db_fetch_array($check_query);
					if(xtc_db_num_rows($check_query) < 1 OR ($$statusid AND $status['orders_status_id']!=$$statusid) ):
						if(!$$statusid):
							$status_query = xtc_db_query("select max(orders_status_id) as status_id from " . TABLE_ORDERS_STATUS);
							$status = xtc_db_fetch_array($status_query);
							$$statusid = $status['status_id']+1;
						endif;
						$check_query = xtc_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_id = '".$$statusid ."' AND language_id='".$languages['languages_id']."'");
						if(xtc_db_num_rows($check_query)<1):
							xtc_db_query("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $$statusid . "', '" . $languages['languages_id'] . "', '" .$$statusname. "')");
						endif;
					else:
						$$statusid = $status['orders_status_id'];
					endif;
				endif;
			}
		}
		$m_fields=' ( configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function ) ';
		// Grund Install des PayPals
		xtc_db_query("insert into ".TABLE_CONFIGURATION.$m_fields."values ('MODULE_PAYMENT_PAYPAL_STATUS', 'True', '6', '3', NULL, now(), '', 'xtc_cfg_select_option(array(\'True\', \'False\'),' )");
		xtc_db_query("insert into ".TABLE_CONFIGURATION.$m_fields."values ('MODULE_PAYMENT_PAYPAL_SORT_ORDER', '0', '6', '0', NULL, now(), '', '')");
		xtc_db_query("insert into ".TABLE_CONFIGURATION.$m_fields."values ('MODULE_PAYMENT_PAYPAL_ALLOWED', '', '6', '0', NULL, now(), '', '')");
		xtc_db_query("insert into ".TABLE_CONFIGURATION.$m_fields."values ('MODULE_PAYMENT_PAYPAL_ZONE', '0', '6', '2', NULL, now(), 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(')");
		// Config Daten auslesen - falls schon vorhanden durch PayPal Modul
		$rest_query=xtc_db_query("select * from ".TABLE_CONFIGURATION." where configuration_key like 'PAYPAL\_%'");
		$rest_array=array();
		while($rest_values = xtc_db_fetch_array($rest_query)) {
			$rest_array[] = $rest_values;
		}
		// Config Daten löschen - falls noch Teile von alten Modulen da sind
		xtc_db_query("delete from ".TABLE_CONFIGURATION." where configuration_key like 'PAYPAL\_%'");
		// Config Daten restaurieren oder installieren mit Standard Werten
		$new_config=array();
		$new_config[]=array('','PAYPAL_MODE', 'live', 111125, 1, '', 'xtc_cfg_select_option(array("live", "sandbox"),');
		$new_config[]=array('','PAYPAL_API_USER', '', 111125, 2, '', '');
		$new_config[]=array('','PAYPAL_API_PWD', '', 111125, 3, '', '');
		$new_config[]=array('','PAYPAL_API_SIGNATURE', '', 111125, 4, '', '');
		$new_config[]=array('','PAYPAL_API_SANDBOX_USER', '', 111125, 5, '', '');
		$new_config[]=array('','PAYPAL_API_SANDBOX_PWD', '', 111125, 6, '', '');
		$new_config[]=array('','PAYPAL_API_SANDBOX_SIGNATURE', '', 111125, 7, '', '');
		$new_config[]=array('','PAYPAL_ORDER_STATUS_TMP_ID', (($PAYPAL_INST_ORDER_STATUS_TMP_ID)?$PAYPAL_INST_ORDER_STATUS_TMP_ID:'1'), 111125, 9, 'xtc_get_order_status_name', 'xtc_cfg_pull_down_order_statuses(');
		$new_config[]=array('','PAYPAL_ORDER_STATUS_SUCCESS_ID', (($PAYPAL_INST_ORDER_STATUS_SUCCESS_ID)?$PAYPAL_INST_ORDER_STATUS_SUCCESS_ID:'2'), 111125, 10, 'xtc_get_order_status_name', 'xtc_cfg_pull_down_order_statuses(');
		$new_config[]=array('','PAYPAL_ORDER_STATUS_PENDING_ID', (($PAYPAL_INST_ORDER_STATUS_PENDING_ID)?$PAYPAL_INST_ORDER_STATUS_PENDING_ID:'5'), 111125, 11, 'xtc_get_order_status_name', 'xtc_cfg_pull_down_order_statuses(');
		$new_config[]=array('','PAYPAL_ORDER_STATUS_REJECTED_ID',(($PAYPAL_INST_ORDER_STATUS_REJECTED_ID)?$PAYPAL_INST_ORDER_STATUS_REJECTED_ID:'4'), 111125, 12, 'xtc_get_order_status_name', 'xtc_cfg_pull_down_order_statuses(');
		$new_config[]=array('','PAYPAL_COUNTRY_MODE', 'de', 111125, 16, '', 'xtc_cfg_select_option(array("de", "uk"),');
		$new_config[]=array('','PAYPAL_EXPRESS_ADDRESS_CHANGE', 'true', 111125, 17, '', 'xtc_cfg_select_option(array("true", "false"),');
		$new_config[]=array('','PAYPAL_EXPRESS_ADDRESS_OVERRIDE', 'true', 111125, 18, '', 'xtc_cfg_select_option(array("true", "false"),');
		$new_config[]=array('','PAYPAL_API_VERSION', '84.0', 111125, 20, '', '');
		$new_config[]=array('','PAYPAL_API_IMAGE', '', 111125, 21,  '', '');
		$new_config[]=array('','PAYPAL_API_CO_BACK', '', 111125, 22, '', '');
		$new_config[]=array('','PAYPAL_API_CO_BORD', '', 111125, 23, '', '');
		$new_config[]=array('','PAYPAL_ERROR_DEBUG', 'false', 111125, 24, '', 'xtc_cfg_select_option(array("true", "false"),');
		$new_config[]=array('','PAYPAL_INVOICE', '', 111125, 25, '', '');
		$new_config[]=array('','PAYPAL_API_KEY', '109,111,100,105,102,105,101,100,95,67,97,114,116,95,69,67,77', 6, 5, '', '');
		// Config Daten speichern
		foreach($new_config as $v1) {
			$old_config=$this->mn_confsearch($v1[1],$rest_array);
			xtc_db_query("insert into ".TABLE_CONFIGURATION.$m_fields."values (".
									"'".(($old_config)?$old_config[1]:$v1[1])."', ".
									"'".(($old_config)?$old_config[2]:$v1[2])."', ".
									(($old_config)?$old_config[3]:$v1[3]).", ".
									$v1[4].", NULL, now(), '".$v1[5]."', '".$v1[6]."' )");
		}
		//API Version auf jeden Fall erneuern
		xtc_db_query("update ".TABLE_CONFIGURATION." SET configuration_value='84.0' where configuration_key='PAYPAL_API_VERSION'");
		//Session setzen
		xtc_db_query("update ".TABLE_CONFIGURATION." SET configuration_value='False' where configuration_key='SESSION_CHECK_USER_AGENT'");
		xtc_db_query("update ".TABLE_CONFIGURATION." SET configuration_value='False' where configuration_key='SESSION_CHECK_IP_ADDRESS'");
		$check_query = xtc_db_query("select configuration_group_title from ".TABLE_CONFIGURATION_GROUP." where configuration_group_title = 'PayPal'");
		if(xtc_db_num_rows($check_query)==0):
			$m_fields=' ( configuration_group_id, configuration_group_title, configuration_group_description, sort_order, visible ) ';
			xtc_db_query("insert into ".TABLE_CONFIGURATION_GROUP.$m_fields."values (111125, 'PayPal', 'PayPal', 25, 1 )");
		endif;
		$check_query = xtc_db_query("show columns from ".TABLE_ADDRESS_BOOK." like 'address\_class'");
		if(xtc_db_num_rows($check_query) < 1):
			xtc_db_query("alter table ".TABLE_ADDRESS_BOOK." ADD address_class VARCHAR( 32 ) NOT NULL");
		else:
			// Falls sich durch ein Update mal was ändert
			xtc_db_query("alter table ".TABLE_ADDRESS_BOOK." MODIFY address_class VARCHAR( 32 ) NOT NULL");
		endif;
		$check_query = xtc_db_query("show columns from ".TABLE_ADMIN_ACCESS." like 'paypal'");
		if(xtc_db_num_rows($check_query) < 1):
			xtc_db_query("alter table ".TABLE_ADMIN_ACCESS." ADD paypal INT( 1 ) NOT NULL");
			xtc_db_query("update ".TABLE_ADMIN_ACCESS." SET paypal=1 where customers_id=1 LIMIT 1");
			if($_SESSION['customer_id']!=1)
				xtc_db_query("update ".TABLE_ADMIN_ACCESS." SET paypal=1 where customers_id=".$_SESSION['customer_id']." LIMIT 1");
		endif;
		$check_query = xtc_db_query("show columns from ".TABLE_ADMIN_ACCESS." like 'module\_paypal\_install'");
		if(xtc_db_num_rows($check_query) < 1):
			xtc_db_query("alter table ".TABLE_ADMIN_ACCESS." ADD module_paypal_install INT( 1 ) NOT NULL");
			xtc_db_query("update ".TABLE_ADMIN_ACCESS." SET module_paypal_install=1 where customers_id=1 LIMIT 1");
			if($_SESSION['customer_id']!=1)
				xtc_db_query("update ".TABLE_ADMIN_ACCESS." SET module_paypal_install=1 where customers_id=".$_SESSION['customer_id']." LIMIT 1");
		endif;
		// Table paypal
		$m_fields="paypal_ipn_id int(11) unsigned NOT NULL auto_increment, xtc_order_id int(11) unsigned NOT NULL default '0', txn_type varchar(32) NOT NULL default '', reason_code varchar(15) default NULL, payment_type varchar(7) NOT NULL default '', payment_status varchar(17) NOT NULL default '', pending_reason varchar(14) default NULL, invoice varchar(64) default NULL, mc_currency char(3) NOT NULL default '', first_name varchar(32) NOT NULL default '', last_name varchar(32) NOT NULL default '', payer_business_name varchar(64) default NULL, address_name varchar(32) default NULL, address_street varchar(64) default NULL, address_city varchar(32) default NULL, address_state varchar(32) default NULL, address_zip varchar(10) default NULL, address_country varchar(64) default NULL, address_status varchar(11) default NULL, payer_email varchar(96) NOT NULL default '', payer_id varchar(32) NOT NULL default '', payer_status varchar(10) NOT NULL default '', payment_date datetime NOT NULL default '0001-01-01 00:00:00', business varchar(96) NOT NULL default '', receiver_email varchar(96) NOT NULL default '', receiver_id varchar(32) NOT NULL default '', txn_id varchar(40) NOT NULL default '', parent_txn_id varchar(17) default NULL, num_cart_items tinyint(4) unsigned NOT NULL default '1', mc_gross decimal(7,2) NOT NULL default '0.00', mc_fee decimal(7,2) NOT NULL default '0.00', mc_shipping decimal(7,2) NOT NULL default '0.00', payment_gross decimal(7,2) default NULL, payment_fee decimal(7,2) default NULL, settle_amount decimal(7,2) default NULL, settle_currency char(3) default NULL, exchange_rate decimal(4,2) default NULL, notify_version decimal(2,1) NOT NULL default '0.0', verify_sign varchar(128) NOT NULL default '', last_modified datetime NOT NULL default '0001-01-01 00:00:00', date_added datetime NOT NULL default '0001-01-01 00:00:00', memo text, mc_authorization decimal(7,2) NOT NULL, mc_captured decimal(7,2) NOT NULL";
		$db_installed = false;
		//BOF - Hetfield - 2010-01-28 - replace mysql_list_tables with query SHOW TABLES -> PHP5.3 deprecated
		$tables = xtc_db_query("SHOW TABLES LIKE '".TABLE_PAYPAL."'");
		if ($checktables = mysql_fetch_array($tables, MYSQL_NUM)) { //mysql_fetch_array used with "MYSQL_NUM", xtc_db_fetch_array() not applicable
			$db_installed = ($checktables[0] == TABLE_PAYPAL);
		}
		//EOF - Hetfield - 2010-01-28 - replace mysql_list_tables with query SHOW TABLES -> PHP5.3 deprecated
		if($db_installed==false):
			xtc_db_query("create table ".TABLE_PAYPAL." ( ".$m_fields.", PRIMARY KEY (paypal_ipn_id, txn_id), KEY xtc_order_id (xtc_order_id) ) ENGINE = MYISAM;");
		else:
			xtc_db_query("alter table ".TABLE_PAYPAL." MODIFY ".str_replace(', ',', MODIFY ',$m_fields));
		endif;
		// Table paypal_status_history
		$m_fields="payment_status_history_id int(11) NOT NULL auto_increment, paypal_ipn_id int(11) NOT NULL default '0', txn_id varchar(64) NOT NULL default '', parent_txn_id varchar(64) NOT NULL default '', payment_status varchar(17) NOT NULL default '', pending_reason varchar(64) default NULL, mc_amount decimal(7,2) NOT NULL, date_added datetime NOT NULL default '0001-01-01 00:00:00'";
		$db_installed = false;
		//BOF - Hetfield - 2010-02-04 - replace mysql_list_tables with query SHOW TABLES -> PHP5.3 deprecated
		//$tables = mysql_list_tables(DB_DATABASE);
		$tables = xtc_db_query("SHOW TABLES LIKE '".TABLE_PAYPAL_STATUS_HISTORY."'");
		if ($checktables = mysql_fetch_array($tables, MYSQL_NUM)) { //mysql_fetch_array used with "MYSQL_NUM", xtc_db_fetch_array() not applicable
			$db_installed = ($checktables[0] == TABLE_PAYPAL_STATUS_HISTORY);
		}
		//EOF - Hetfield - 2010-02-04 - replace mysql_list_tables with query SHOW TABLES -> PHP5.3 deprecated
		if($db_installed==false):
			xtc_db_query("create table ".TABLE_PAYPAL_STATUS_HISTORY." ( ".$m_fields.", PRIMARY KEY ( payment_status_history_id), KEY paypal_ipn_id (paypal_ipn_id) ) ENGINE = MYISAM;");
		else:
			xtc_db_query("alter table ".TABLE_PAYPAL_STATUS_HISTORY." MODIFY ".str_replace(', ',', MODIFY ',$m_fields));
		endif;
		//BOF - Dokuman - 2009-10-04 - Disable CRC-Check on paypal edited files
		/*
		if(file_exists('module_paypal_install.php'))
			xtc_redirect(xtc_href_link('module_paypal_install.php', 'set=' . $_GET['set'] . '&module=' . $this->code.'&ppauto=1'));
		*/
		//EOF - Dokuman - 2009-10-04 - Disable CRC-Check on paypal edited files		
	}
/**************************************************************/
	function remove($pre_inst=0) {
		// Stand: 17.05.2009
		if(!defined('TABLE_PAYPAL'))define('TABLE_PAYPAL', 'paypal');
		if(!defined('TABLE_PAYPAL_STATUS_HISTORY'))define('TABLE_PAYPAL_STATUS_HISTORY', 'paypal_status_history');
		if(!$_POST['paypaldelete'] AND !$pre_inst):
			$check_query = xtc_db_query("select configuration_key from ".TABLE_CONFIGURATION." where configuration_key = 'MODULE_PAYMENT_PAYPALEXPRESS_STATUS'");
			if(xtc_db_num_rows($check_query)==0)
				xtc_redirect(xtc_href_link(FILENAME_MODULES, 'set=' . $_GET['set'] . '&module=' . $this->code . '&action=removepaypal'));
		endif;
		// Grund Install des PayPal
		xtc_db_query("delete from ".TABLE_CONFIGURATION." where configuration_key like 'MODULE\_PAYMENT\_PAYPAL\_%'");
		// Config Install für PayPal + Express - NUR falls das Express nicht installiert ist
		$check_query = xtc_db_query("select configuration_key from ".TABLE_CONFIGURATION." where configuration_key = 'MODULE_PAYMENT_PAYPALEXPRESS_STATUS'");
		if(xtc_db_num_rows($check_query)==0):
			xtc_db_query("delete from ".TABLE_CONFIGURATION." where configuration_key like 'PAYPAL\_%'");
			xtc_db_query("delete from ".TABLE_CONFIGURATION_GROUP." where configuration_group_title = 'PayPal'");
			$check_query = xtc_db_query("show columns from ".TABLE_ADMIN_ACCESS." like 'paypal'");
			if(xtc_db_num_rows($check_query) > 0):
				xtc_db_query("alter table ".TABLE_ADMIN_ACCESS." DROP COLUMN paypal");
			endif;
			$check_query = xtc_db_query("show columns from ".TABLE_ADMIN_ACCESS." like 'module\_paypal\_install'");
			if(xtc_db_num_rows($check_query) > 0):
				xtc_db_query("alter table ".TABLE_ADMIN_ACCESS." DROP COLUMN module_paypal_install");
			endif;
			xtc_db_query("DROP TABLE if EXISTS ".TABLE_PAYPAL);
			xtc_db_query("DROP TABLE if EXISTS ".TABLE_PAYPAL_STATUS_HISTORY);
		endif;
	}
/**************************************************************/
	function keys() {
		// Stand: 29.04.2009
		return array('MODULE_PAYMENT_PAYPAL_STATUS', 'MODULE_PAYMENT_PAYPAL_ALLOWED', 'MODULE_PAYMENT_PAYPAL_ZONE','MODULE_PAYMENT_PAYPAL_SORT_ORDER');
	}
/**************************************************************/
	function mn_confsearch($needle, $haystack ){
		// Stand: 29.04.2009
		foreach($haystack as $key1=>$value1){
			if(is_array($value1)):
				$nodes = array_search($needle, $value1);
				if($nodes):
					$old_config=array();
					foreach($value1 as $key2=>$value2){
						$old_config[]=$value2;
					}
					return($old_config);
				endif;
			endif;
		}
		return;
	}
}
?>
