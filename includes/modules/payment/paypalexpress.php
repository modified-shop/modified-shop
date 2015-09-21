<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project (earlier name of osCommerce)
   (c) 2002-2003 osCommerce www.oscommerce.com
   (c) 2007 XT-Commerce
   (c) 2008 Hamburger-Internetdienst Support www.forum.hamburger-internetdienst.de

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

defined('TABLE_PAYPAL') OR define('TABLE_PAYPAL', 'paypal');
defined('TABLE_PAYPAL_STATUS_HISTORY') OR define('TABLE_PAYPAL_STATUS_HISTORY', 'paypal_status_history');

class paypalexpress {
  var $code, $title, $description, $extended_description, $enabled;

  function __construct() {
    global $order;
    
    $this->code = 'paypalexpress';
    $this->title = MODULE_PAYMENT_PAYPALEXPRESS_TEXT_TITLE;
    $this->description = MODULE_PAYMENT_PAYPALEXPRESS_TEXT_DESCRIPTION.((defined('_VALID_XTC')) ? MODULE_PAYMENT_PAYPALEXPRESS_LP : '');
    $this->extended_description = MODULE_PAYMENT_PAYPAL_TEXT_EXTENDED_DESCRIPTION;
    if(MODULE_PAYMENT_PAYPALEXPRESS_SORT_ORDER!=''){
      $this->sort_order = MODULE_PAYMENT_PAYPALEXPRESS_SORT_ORDER;
    }else{
      $this->sort_order = 3;
    }
    $this->enabled = ((MODULE_PAYMENT_PAYPALEXPRESS_STATUS == 'True') ? true : false);
    $this->info = MODULE_PAYMENT_PAYPALEXPRESS_TEXT_INFO;
    $this->order_status_success = PAYPAL_ORDER_STATUS_SUCCESS_ID;
    $this->order_status_rejected = PAYPAL_ORDER_STATUS_REJECTED_ID;
    $this->order_status_pending = PAYPAL_ORDER_STATUS_PENDING_ID;
    $this->order_status_tmp = PAYPAL_ORDER_STATUS_TMP_ID;
    $this->tmpStatus = PAYPAL_ORDER_STATUS_TMP_ID;
    $this->tmpOrders = true;
    $this->debug = true;

    if (is_object($order)) {
      $this->update_status();
    }
  }

  function update_status() {
    global $order;
  }

  function javascript_validation() {
    return false;
  }

  function selection() {
    return array('id' => $this->code, 'module' => $this->title, 'description' => $this->info);
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

  function before_process() {
    if (isset($_SESSION['nvpReqArray']) && is_array($_SESSION['nvpReqArray']) && $_SESSION['payment'] == 'paypalexpress') {
      if ($_POST['comments_added'] != '') {
        $_SESSION['comments'] = xtc_db_prepare_input($_POST['comments']);
      }
      $error_mess  = '';
      if (DISPLAY_CONDITIONS_ON_CHECKOUT == 'true' && $_POST['conditions'] != 'conditions') {
        $error_mess = '1';
      }
      if ($_POST['check_address'] != 'address') {
        $error_mess .= '2';
      }
      if($error_mess != '') {
        xtc_redirect(xtc_href_link(FILENAME_PAYPAL_CHECKOUT, 'error_message='.$error_mess, 'SSL', true, false));
      }
    }
    if(isset($_SESSION['payment']) && $_SESSION['payment'] == 'paypalexpress') {
      if (isset($_SESSION['cartID']) && $_SESSION['cart']->cartID != $_SESSION['cartID']) {
        xtc_redirect(xtc_href_link(FILENAME_PAYPAL_CHECKOUT, '', 'SSL'));
      }
      if (!isset($_SESSION['sendto'])) {
        xtc_redirect(xtc_href_link(FILENAME_PAYPAL_CHECKOUT, '', 'SSL'));
      }
    }
  }

  function payment_action(){
    global $order, $o_paypal, $tmp, $insert_id;
    
    $tmp = false;
    return;
  }

  function after_process() {
    global $insert_id, $o_paypal;
    
    $o_paypal->complete_ceckout($insert_id, $_GET);
    $o_paypal->write_status_history($insert_id);
    $o_paypal->logging_status($insert_id);

    if(isset($_SESSION['reshash']['ACK']) && strtoupper($_SESSION['reshash']['ACK']) != "SUCCESS" && strtoupper($_SESSION['reshash']['ACK']) != "SUCCESSWITHWARNING") {
      if($_SESSION['payment'] == 'paypalexpress') {
        xtc_redirect($o_paypal->EXPRESS_CANCEL_URL);
      } else {
        if(isset($_SESSION['reshash']['REDIRECTREQUIRED']) && strtoupper($_SESSION['reshash']['REDIRECTREQUIRED']) == "TRUE") {
          xtc_redirect($o_paypal->EXPRESS_CANCEL_URL);
        } else {
          xtc_redirect($o_paypal->CANCEL_URL);
        }
      }
    }

    if(isset($_SESSION['reshash']['REDIRECTREQUIRED'])  && strtoupper($_SESSION['reshash']['REDIRECTREQUIRED']) == "TRUE") {
      $this->giropay_process();
    } else {
      unset($_SESSION['payment']);
      unset($_SESSION['nvpReqArray']);
      unset($_SESSION['reshash']);
    }
  }

  function giropay_process() {
    global $o_paypal;
    
    $o_paypal->giropay_confirm($_GET);
    return;
  }

  function admin_order($oID) {
    return false;
  }

  function output_error() {
    return false;
  }

  function check() {
    if(!isset($this->_check)) {
      $check_query = xtc_db_query("SELECT configuration_value FROM ".TABLE_CONFIGURATION." WHERE configuration_key = 'MODULE_PAYMENT_PAYPALEXPRESS_STATUS'");
      $this->_check = xtc_db_num_rows($check_query);
    }
    return $this->_check;
  }

	function install() {
		// remove old installation
		$this->remove(true);

		// install paypal express
		xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_PAYMENT_PAYPALEXPRESS_STATUS', 'True', '6', '3', NULL, now(), '', 'xtc_cfg_select_option(array(\'True\', \'False\'),' )");
		xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_PAYMENT_PAYPALEXPRESS_SORT_ORDER', '0', '6', '0', NULL, now(), '', '')");
		xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_PAYMENT_PAYPALEXPRESS_ALLOWED', '', '6', '0', NULL, now(), '', '')");
		
		// install order status
		$stati = array('PAYPAL_INST_ORDER_STATUS_TMP_NAME' => 'PAYPAL_INST_ORDER_STATUS_TMP_ID',
                   'PAYPAL_INST_ORDER_STATUS_SUCCESS_NAME' => 'PAYPAL_INST_ORDER_STATUS_SUCCESS_ID',
                   'PAYPAL_INST_ORDER_STATUS_PENDING_NAME' => 'PAYPAL_INST_ORDER_STATUS_PENDING_ID',
                   'PAYPAL_INST_ORDER_STATUS_REJECTED_NAME' => 'PAYPAL_INST_ORDER_STATUS_REJECTED_ID');
		foreach($stati as $statusname => $statusid) {
			$languages_query = xtc_db_query("SELECT * FROM " . TABLE_LANGUAGES . " order by sort_order");
			while($languages = xtc_db_fetch_array($languages_query)) {
				if (file_exists(DIR_FS_LANGUAGES.$languages['directory'].'/admin/paypal.php')) {
					include(DIR_FS_LANGUAGES.$languages['directory'].'/admin/paypal.php');
				}
				if (${$statusname}!='') {
					$check_query = xtc_db_query("SELECT orders_status_id FROM " . TABLE_ORDERS_STATUS . " WHERE orders_status_name = '" .${$statusname}. "' AND language_id='".$languages['languages_id']."' limit 1");
					$status = xtc_db_fetch_array($check_query);
					if (xtc_db_num_rows($check_query) < 1 || (${$statusid} && $status['orders_status_id']!=${$statusid}) ) {
						if (!${$statusid}) {
							$status_query = xtc_db_query("SELECT max(orders_status_id) as status_id FROM " . TABLE_ORDERS_STATUS);
							$status = xtc_db_fetch_array($status_query);
							${$statusid} = $status['status_id']+1;
						}
						$check_query = xtc_db_query("SELECT orders_status_id FROM " . TABLE_ORDERS_STATUS . " WHERE orders_status_id = '".${$statusid} ."' AND language_id='".$languages['languages_id']."'");
						if (xtc_db_num_rows($check_query) < 1) {
							xtc_db_query("INSERT INTO " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) VALUES ('" . ${$statusid} . "', '" . $languages['languages_id'] . "', '" .${$statusname}. "')");
						}
					} else {
						${$statusid} = $status['orders_status_id'];
					}
				}
			}
		}
		
		// get old configs
		$rest_query=xtc_db_query("SELECT * FROM ".TABLE_CONFIGURATION." WHERE configuration_key LIKE 'PAYPAL\_%'");
		$rest_array=array();
		while($rest_values = xtc_db_fetch_array($rest_query)) {
			$rest_array[] = $rest_values;
		}
		
		// delete old configs
		xtc_db_query("DELETE FROM ".TABLE_CONFIGURATION." WHERE configuration_key LIKE 'PAYPAL\_%'");
		
		// build new config
		$new_config = array();
		$new_config[] = array('','PAYPAL_MODE', 'live', 111125, 1, '', 'xtc_cfg_select_option(array("live", "sandbox"),');
		$new_config[] = array('','PAYPAL_API_USER', '', 111125, 2, '', '');
		$new_config[] = array('','PAYPAL_API_PWD', '', 111125, 3, '', '');
		$new_config[] = array('','PAYPAL_API_SIGNATURE', '', 111125, 4, '', '');
		$new_config[] = array('','PAYPAL_API_SANDBOX_USER', '', 111125, 5, '', '');
		$new_config[] = array('','PAYPAL_API_SANDBOX_PWD', '', 111125, 6, '', '');
		$new_config[] = array('','PAYPAL_API_SANDBOX_SIGNATURE', '', 111125, 7, '', '');
		$new_config[] = array('','PAYPAL_ORDER_STATUS_TMP_ID', (($PAYPAL_INST_ORDER_STATUS_TMP_ID) ? $PAYPAL_INST_ORDER_STATUS_TMP_ID : '1'), 111125, 9, 'xtc_get_order_status_name', 'xtc_cfg_pull_down_order_statuses(');
		$new_config[] = array('','PAYPAL_ORDER_STATUS_SUCCESS_ID', (($PAYPAL_INST_ORDER_STATUS_SUCCESS_ID) ? $PAYPAL_INST_ORDER_STATUS_SUCCESS_ID : '2'), 111125, 10, 'xtc_get_order_status_name', 'xtc_cfg_pull_down_order_statuses(');
		$new_config[] = array('','PAYPAL_ORDER_STATUS_PENDING_ID', (($PAYPAL_INST_ORDER_STATUS_PENDING_ID) ? $PAYPAL_INST_ORDER_STATUS_PENDING_ID : '5'), 111125, 11, 'xtc_get_order_status_name', 'xtc_cfg_pull_down_order_statuses(');
		$new_config[] = array('','PAYPAL_ORDER_STATUS_REJECTED_ID',(($PAYPAL_INST_ORDER_STATUS_REJECTED_ID) ? $PAYPAL_INST_ORDER_STATUS_REJECTED_ID : '4'), 111125, 12, 'xtc_get_order_status_name', 'xtc_cfg_pull_down_order_statuses(');
		$new_config[] = array('','PAYPAL_COUNTRY_MODE', 'de', 111125, 16, '', 'xtc_cfg_select_option(array("de", "uk"),');
		$new_config[] = array('','PAYPAL_EXPRESS_ADDRESS_CHANGE', 'true', 111125, 17, '', 'xtc_cfg_select_option(array("true", "false"),');
		$new_config[] = array('','PAYPAL_EXPRESS_ADDRESS_OVERRIDE', 'true', 111125, 18, '', 'xtc_cfg_select_option(array("true", "false"),');
		$new_config[] = array('','PAYPAL_API_VERSION', '119.0', 111125, 20, '', '');
		$new_config[] = array('','PAYPAL_API_IMAGE', '', 111125, 21,  '', '');
		$new_config[] = array('','PAYPAL_API_CO_BACK', '', 111125, 22, '', '');
		$new_config[] = array('','PAYPAL_API_CO_BORD', '', 111125, 23, '', '');
		$new_config[] = array('','PAYPAL_ERROR_DEBUG', 'false', 111125, 24, '', 'xtc_cfg_select_option(array("true", "false"),');
		$new_config[] = array('','PAYPAL_INVOICE', '', 111125, 25, '', '');
		$new_config[] = array('','PAYPAL_BRANDNAME', '', 111125, 26, '', '');
		$new_config[] = array('','PAYPAL_API_KEY', '109,111,100,105,102,105,101,100,95,67,97,114,116,95,69,67,77', 6, 5, '', '');
		
		// save config
		foreach($new_config as $v1) {
			$old_config=$this->mn_confsearch($v1[1], $rest_array);
			xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('".(($old_config) ? $old_config[1] : $v1[1])."', '".(($old_config) ? $old_config[2] : $v1[2])."', ".(($old_config)?$old_config[3]:$v1[3]).", ".$v1[4].", NULL, now(), '".$v1[5]."', '".$v1[6]."')");
		}
		xtc_db_query("UPDATE ".TABLE_CONFIGURATION." SET configuration_value = '119.0' WHERE configuration_key = 'PAYPAL_API_VERSION'");
		xtc_db_query("UPDATE ".TABLE_CONFIGURATION." SET configuration_value = 'False' WHERE configuration_key = 'SESSION_CHECK_USER_AGENT'");
		xtc_db_query("UPDATE ".TABLE_CONFIGURATION." SET configuration_value = 'False' WHERE configuration_key = 'SESSION_CHECK_IP_ADDRESS'");
				
		$check_query = xtc_db_query("SHOW COLUMNS FROM ".TABLE_ADDRESS_BOOK." LIKE 'address\_class'");
		if (xtc_db_num_rows($check_query) < 1) {
			xtc_db_query("ALTER TABLE ".TABLE_ADDRESS_BOOK." ADD address_class VARCHAR(32) NOT NULL");
		} else {
			xtc_db_query("ALTER TABLE ".TABLE_ADDRESS_BOOK." MODIFY address_class VARCHAR(32) NOT NULL");
		}
		$check_query = xtc_db_query("SHOW COLUMNS FROM ".TABLE_ADMIN_ACCESS." LIKE 'paypal'");
		if (xtc_db_num_rows($check_query) < 1) {
			xtc_db_query("ALTER TABLE ".TABLE_ADMIN_ACCESS." ADD paypal INT(1) NOT NULL");
			xtc_db_query("UPDATE ".TABLE_ADMIN_ACCESS." SET paypal = '1' WHERE customers_id = '1' LIMIT 1");
			if ($_SESSION['customer_id'] != 1) {
				xtc_db_query("UPDATE ".TABLE_ADMIN_ACCESS." SET paypal = '1' WHERE customers_id = ".$_SESSION['customer_id']." LIMIT 1");
		  }
		}
		$check_query = xtc_db_query("SHOW COLUMNS FROM ".TABLE_ADMIN_ACCESS." LIKE 'module\_paypal\_install'");
		if (xtc_db_num_rows($check_query) < 1) {
			xtc_db_query("ALTER TABLE ".TABLE_ADMIN_ACCESS." ADD module_paypal_install INT(1) NOT NULL");
			xtc_db_query("UPDATE ".TABLE_ADMIN_ACCESS." SET module_paypal_install = '1' WHERE customers_id = '1' LIMIT 1");
			if ($_SESSION['customer_id'] != 1) {
				xtc_db_query("UPDATE ".TABLE_ADMIN_ACCESS." SET module_paypal_install = '1' WHERE customers_id = '".$_SESSION['customer_id']."' LIMIT 1");
		  }
		}

		if (xtc_db_num_rows(xtc_db_query("SHOW TABLES LIKE '".TABLE_PAYPAL."'")) != '1') { 
			xtc_db_query("CREATE TABLE ".TABLE_PAYPAL." ( 
			                paypal_ipn_id int(11) unsigned NOT NULL auto_increment, 
			                xtc_order_id int(11) unsigned NOT NULL default '0', 
			                txn_type varchar(32) NOT NULL default '', 
			                reason_code varchar(15) default NULL, 
			                payment_type varchar(7) NOT NULL default '', 
			                payment_status varchar(17) NOT NULL default '', 
			                pending_reason varchar(14) default NULL, 
			                invoice varchar(64) default NULL, 
			                mc_currency char(3) NOT NULL default '', 
			                first_name varchar(32) NOT NULL default '', 
			                last_name varchar(32) NOT NULL default '', 
			                payer_business_name varchar(64) default NULL, 
			                address_name varchar(32) default NULL, 
			                address_street varchar(64) default NULL, 
			                address_city varchar(32) default NULL, 
			                address_state varchar(32) default NULL, 
			                address_zip varchar(10) default NULL, 
			                address_country varchar(64) default NULL, 
			                address_status varchar(11) default NULL, 
			                payer_email varchar(96) NOT NULL default '', 
			                payer_id varchar(32) NOT NULL default '', 
			                payer_status varchar(10) NOT NULL default '', 
			                payment_date datetime NOT NULL default '0001-01-01 00:00:00', 
			                business varchar(96) NOT NULL default '', 
			                receiver_email varchar(96) NOT NULL default '', 
			                receiver_id varchar(32) NOT NULL default '', 
			                txn_id varchar(40) NOT NULL default '', 
			                parent_txn_id varchar(17) default NULL, 
			                num_cart_items tinyint(4) unsigned NOT NULL default '1', 
			                mc_gross decimal(7,2) NOT NULL default '0.00', 
			                mc_fee decimal(7,2) NOT NULL default '0.00', 
			                mc_shipping decimal(7,2) NOT NULL default '0.00', 
			                payment_gross decimal(7,2) default NULL, 
			                payment_fee decimal(7,2) default NULL, 
			                settle_amount decimal(7,2) default NULL, 
			                settle_currency char(3) default NULL, 
			                exchange_rate decimal(4,2) default NULL, 
			                notify_version decimal(2,1) NOT NULL default '0.0', 
			                verify_sign varchar(128) NOT NULL default '', 
			                last_modified datetime NOT NULL default '0001-01-01 00:00:00', 
			                date_added datetime NOT NULL default '0001-01-01 00:00:00', 
			                memo text, 
			                mc_authorization decimal(7,2) NOT NULL, 
			                mc_captured decimal(7,2) NOT NULL,
			                PRIMARY KEY (paypal_ipn_id, txn_id), 
			                KEY xtc_order_id (xtc_order_id)
			              ) ENGINE = MYISAM;");
		}
		
		if (xtc_db_num_rows(xtc_db_query("SHOW TABLES LIKE '".TABLE_PAYPAL_STATUS_HISTORY."'")) != '1') { 
			xtc_db_query("CREATE TABLE ".TABLE_PAYPAL_STATUS_HISTORY." (
			                payment_status_history_id int(11) NOT NULL auto_increment, 
			                paypal_ipn_id int(11) NOT NULL default '0', 
			                txn_id varchar(64) NOT NULL default '', 
			                parent_txn_id varchar(64) NOT NULL default '', 
			                payment_status varchar(17) NOT NULL default '', 
			                pending_reason varchar(64) default NULL, 
			                mc_amount decimal(7,2) NOT NULL, 
			                date_added datetime NOT NULL default '0001-01-01 00:00:00',
			                PRIMARY KEY ( payment_status_history_id), 
			                KEY paypal_ipn_id (paypal_ipn_id)
			              ) ENGINE = MYISAM;");
		}
	}

	function remove($pre_inst = false) {
		if (!$_POST['paypaldelete'] && $pre_inst === false) {
			$check_query = xtc_db_query("SELECT configuration_key FROM ".TABLE_CONFIGURATION." WHERE configuration_key = 'MODULE_PAYMENT_PAYPAL_STATUS'");
			if (xtc_db_num_rows($check_query) == 0) {
				xtc_redirect(xtc_href_link(FILENAME_MODULES, 'set=' . $_GET['set'] . '&module=' . $this->code . '&action=removepaypal'));
		  }
		}
		xtc_db_query("DELETE FROM ".TABLE_CONFIGURATION." WHERE configuration_key LIKE 'MODULE\_PAYMENT\_PAYPAL\_%'");
		$check_query = xtc_db_query("SELECT configuration_key FROM ".TABLE_CONFIGURATION." WHERE configuration_key = 'MODULE_PAYMENT_PAYPAL_STATUS'");
		if (xtc_db_num_rows($check_query) == 0) {
			xtc_db_query("DELETE FROM ".TABLE_CONFIGURATION." WHERE configuration_key LIKE 'PAYPAL\_%'");
			$check_query = xtc_db_query("SHOW COLUMNS FROM ".TABLE_ADMIN_ACCESS." LIKE 'paypal'");
			if (xtc_db_num_rows($check_query) > 0) {
				xtc_db_query("ALTER TABLE ".TABLE_ADMIN_ACCESS." DROP COLUMN paypal");
			}
			$check_query = xtc_db_query("SHOW COLUMNS FROM ".TABLE_ADMIN_ACCESS." LIKE 'module\_paypal\_install'");
			if (xtc_db_num_rows($check_query) > 0) {
				xtc_db_query("ALTER TABLE ".TABLE_ADMIN_ACCESS." DROP COLUMN module_paypal_install");
			}
			xtc_db_query("DROP TABLE if EXISTS ".TABLE_PAYPAL);
			xtc_db_query("DROP TABLE if EXISTS ".TABLE_PAYPAL_STATUS_HISTORY);
		} else {
      $check_query = xtc_db_query("SELECT configuration_key FROM ".TABLE_CONFIGURATION." WHERE configuration_key = 'MODULE_PAYMENT_PAYPAL_ID'");
      if (xtc_db_num_rows($check_query) > 0) {
        xtc_db_query("DELETE FROM ".TABLE_CONFIGURATION." WHERE configuration_key LIKE 'MODULE\_PAYMENT\_PAYPAL\_%'");
      }		
		}
	}

  function keys() {
    return array('MODULE_PAYMENT_PAYPALEXPRESS_STATUS');
  }

	function mn_confsearch($needle, $haystack) {
		foreach($haystack as $key1 => $value1) {
			if (is_array($value1)) {
				$nodes = array_search($needle, $value1);
				if ($nodes) {
					$old_config = array();
					foreach($value1 as $key2 => $value2) {
						$old_config[] = $value2;
					}
					return($old_config);
				}
			}
		}
		return;
	}

}
?>