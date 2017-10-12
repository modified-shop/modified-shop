<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

class SofortLibPayment {

  function __construct() {}

	function SofortPayment() {
		global $order;

		$this->code = get_class($this);
		$this->version = $this->get_version();
		$this->title = ((defined('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_TITLE')) ? constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_TITLE') : '');
		$this->description = ((defined('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_DESCRIPTION')) ? constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_DESCRIPTION') : '');
    $this->sort_order = ((defined('MODULE_PAYMENT_'.strtoupper($this->code).'_SORT_ORDER')) ? constant('MODULE_PAYMENT_'.strtoupper($this->code).'_SORT_ORDER') : '');
    $this->enabled = ((defined('MODULE_PAYMENT_'.strtoupper($this->code).'_STATUS') && constant('MODULE_PAYMENT_'.strtoupper($this->code).'_STATUS') == 'True') ? true : false);
    $this->info = ((defined('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_INFO')) ? constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_INFO') : '');

		if ($this->check() > 0) {
      $this->tmpStatus = constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TMP_STATUS_ID');
      $this->logging = ((constant('MODULE_PAYMENT_'.strtoupper($this->code).'_LOGGING') == 'True') ? true : false);

      $this->ks_status = false;
      if (defined('MODULE_PAYMENT_'.strtoupper($this->code).'_KS_STATUS') && constant('MODULE_PAYMENT_'.strtoupper($this->code).'_KS_STATUS') == 'True') {
        $this->title = constant('MODULE_PAYMENT_'.strtoupper($this->code).'_KS_TEXT_TITLE');
        $this->ks_status = true;
      }

      if ((int) constant('MODULE_PAYMENT_'.strtoupper($this->code).'_ORDER_STATUS_ID') > 0) {
        $this->order_status = constant('MODULE_PAYMENT_'.strtoupper($this->code).'_ORDER_STATUS_ID');
      }

      if ($this->tmpStatus < 0) {
        $this->tmpStatus = DEFAULT_ORDERS_STATUS_ID;
      }

      $this->tmpOrders = false;
      if(constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TMP_ORDER') == 'True') {
        $this->tmpOrders = true;
        $this->form_action_url = '';
      }

      if (!defined('RUN_MODE_ADMIN') && is_object($order)) {
        $this->update_status();
      }

      if (defined('RUN_MODE_ADMIN') && $this->enabled === true) {
        $this->description .= ((defined('MODULE_PAYMENT_'.strtoupper($this->code).'_DESCRIPTION_INSTALL')) ? constant('MODULE_PAYMENT_'.strtoupper($this->code).'_DESCRIPTION_INSTALL').'<a class="button btnbox" style="text-align:center;" onclick="this.blur();" href="' . xtc_href_link(FILENAME_MODULES, 'set=payment&module=' . $this->code . '&moduleaction=status') . '">' . 'Status '.BUTTON_MODULE_INSTALL . '</a>' : '');
        if (isset($_GET['moduleaction']) && $_GET['moduleaction'] == 'status') {
          $this->status_install();
        }
      }
	  }
	}


	function update_status() {
		global $order;

		if (($this->enabled == true) && ((int) constant('MODULE_PAYMENT_'.strtoupper($this->code).'_ZONE') > 0)) {
			$check_flag = false;
			$check_query = xtc_db_query("SELECT zone_id
			                               FROM " . TABLE_ZONES_TO_GEO_ZONES . "
			                              WHERE geo_zone_id = '" . constant('MODULE_PAYMENT_'.strtoupper($this->code).'_ZONE') . "'
			                                AND zone_country_id = '" . $order->billing['country']['id'] . "'
			                           ORDER BY zone_id");
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


	function javascript_validation() {
		return false;
	}


	function selection() {

    // check for temp order
    if ($this->tmpOrders === true) {
		  $this->_check_temp_order();
		}
		unset($_SESSION['sofort'][$this->code]);


		$description = '';
		switch (constant('MODULE_PAYMENT_'.strtoupper($this->code).'_IMAGE')) {
			case 'Logo & Text':
				if ($this->ks_status === true) {
					//$description = $this->_setImageText('logo_155x50.png', constant('MODULE_PAYMENT_'.strtoupper($this->code).'_KS_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_TEXT'));
					$description = $this->_setImageText('pink.svg', constant('MODULE_PAYMENT_'.strtoupper($this->code).'_KS_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_TEXT'));
				} else {
					//$description = $this->_setImageText('logo_155x50.png', constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_TEXT'));
					$description = $this->_setImageText('pink.svg', constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_TEXT'));
				}
				break;
			case 'Infographic':
				if ($this->ks_status === true) {
					//$description = $this->_setImageText('banner_400x100_ks.png');
					$description = $this->_setImageText('pink.svg');
				} else {
					//$description = $this->_setImageText('banner_300x100.png');
					$description = $this->_setImageText('pink.svg');
				}
				break;
		}

    $fields = array();
    if ($this->ideal === true) {
      //get all available banks from SOFORT-server
      $this->sofortLibIdealBanks = new sofortLibIdealBanks(constant('MODULE_PAYMENT_'.strtoupper($this->code).'_KEY'));
      $this->sofortLibIdealBanks->sendRequest();

      $bank_array = array_merge(array(array('id' => '0', 'text' => PULL_DOWN_DEFAULT)), $this->sofortLibIdealBanks->getBanks());

      $fields['fields'] = array(array('title' => '',
                                      'field' => xtc_draw_pull_down_menu('ideal_bank_name', $bank_array)
                                      )
                                );
    }

		return array_merge(array('id' => $this->code , 'module' => $this->title , 'description' => $description), $fields);
	}


	function pre_confirmation_check() {
		return false;
	}


	function confirmation() {
    // check for temp order
    if ($this->tmpOrders === true) {
		  $this->_check_temp_order();
    }
    unset($_SESSION['sofort'][$this->code]);

		return false;
	}


	function process_button() {
		return false;
	}


	function before_process() {
	  if ($this->tmpOrders === false && !isset($_SESSION['sofort'][$this->code]['tID'])) {
	    $this->_payment_action();
	  }

    if ($this->tmpOrders === false && isset($_SESSION['tmp_oID']) && is_numeric($_SESSION['tmp_oID'])) {
      $check_order_query = xtc_db_query("SELECT orders_id
                                           FROM ".TABLE_ORDERS."
                                          WHERE orders_id = '".(int)$_SESSION['tmp_oID']."'
                                            AND customers_id = '".$_SESSION['customer_id']."'");
      if (xtc_db_num_rows($check_order_query) > 0) {
        $_SESSION['cart']->reset(true);

        // unregister session variables used during checkout
        unset ($_SESSION['sendto']);
        unset ($_SESSION['billto']);
        unset ($_SESSION['shipping']);
        unset ($_SESSION['payment']);
        unset ($_SESSION['comments']);
        unset ($_SESSION['tmp_oID']);
        unset ($_SESSION['cc']);
        $last_order = $insert_id;
        //GV Code Start
        if (isset($_SESSION['credit_covers'])) {
          unset ($_SESSION['credit_covers']);
        }
        require_once(DIR_WS_CLASSES.'order_total.php');
        $order_total_modules = new order_total();
        $order_total_modules->clear_posts();
        // GV Code End

        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL'));
      }
    }

		return false;
	}


	function payment_action() {
	  if ($this->tmpOrders === true) {
	    $this->_payment_action();
	  }
	}


	function check() {
		if (!isset($this->_check)) {
			$check_query = xtc_db_query("SELECT configuration_value
			                               FROM " . TABLE_CONFIGURATION . "
			                              WHERE configuration_key = 'MODULE_PAYMENT_".strtoupper($this->code)."_STATUS'");
			$this->_check = xtc_db_num_rows($check_query);
		}
		return $this->_check;
	}


	function get_error() {
		$error = false;
		if (isset($_GET['payment_error']) && $_GET['payment_error'] != '') {
			$error = array('title' => constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_ERROR_HEADING'),
			               'error' => decode_htmlentities(constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_ERROR_MESSAGE'))
			               );
		}
		return $error;
	}


	function after_process() {
	  global $insert_id;

    if (!isset($insert_id) || $insert_id == '') {
		  $insert_id = $_SESSION['tmp_oID'];
		}

	  // set orders status
	  if ($this->tmpOrders === false) {
      $order_status_id = constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TMP_STATUS_ID');
      if ($order_status_id < 0) {
        $order_status_id = $this->_get_orders_status($insert_id);
      }
      $this->update_sofort_status($insert_id, $order_status_id);
    }

	  unset($_SESSION['sofort'][$this->code]);

	  $this->after_process_callback();
	}


  function after_process_callback() {
    if (isset($_GET['nonexistorder']) && $_GET['nonexistorder'] == 'true') {
      // sending 404 to trigger new callback
      header("HTTP/1.0 404 Not Found");
      header("Status: 404 Not Found");
      exit();
    }
  }


  function _payment_data() {
    global $xtPrice, $insert_id;

    // gateway
    $this->data['success_url'] = ((ENABLE_SSL == true) ? HTTPS_SERVER : HTTP_SERVER).DIR_WS_CATALOG.FILENAME_CHECKOUT_PROCESS.'?'.xtc_session_name().'='.xtc_session_id();
    $this->data['abort_url'] = ((ENABLE_SSL == true) ? HTTPS_SERVER : HTTP_SERVER).DIR_WS_CATALOG.FILENAME_CHECKOUT_PAYMENT.'?'.xtc_session_name().'='.xtc_session_id().'&payment_error='.$this->code;
    $this->data['timeout_url'] = ((ENABLE_SSL == true) ? HTTPS_SERVER : HTTP_SERVER).DIR_WS_CATALOG.FILENAME_CHECKOUT_PAYMENT.'?'.xtc_session_name().'='.xtc_session_id().'&payment_error='.$this->code;
    $this->data['callback_url'] = ((ENABLE_SSL == true) ? HTTPS_SERVER : HTTP_SERVER).DIR_WS_CATALOG.'callback/sofort/'.$this->code.'.php';
    $this->data['currency'] = $_SESSION['currency'];

    // amount
    if ($this->tmpOrders === false) {
      global $order;
      
      if (!class_exists('order_total')) {
        require(DIR_WS_CLASSES.'order_total.php');
      }
      $order_total_modules = new order_total();
      $order_total_modules->collect_posts();
      $order_total_modules->pre_confirmation_check();

      if (MODULE_ORDER_TOTAL_INSTALLED) {
        $order_total_array = $order_total_modules->process();
        if (count($order_total_array)) {
          foreach($order_total_array as $key => $entry) {
            if ($entry['code'] == 'ot_total') {
              $amount = round($entry['value'], $xtPrice->get_decimal_places(''));
            }
          }
        }
      }
    } else {
      $order = new order((int)$insert_id);
      $amount = round($order->info['pp_total'], $xtPrice->get_decimal_places(''));
    }
    $this->data['amount'] = number_format($amount, 2, '.', '');

    // reason 1
    $this->data['reason_1'] = str_replace(array('{{order_id}}',
                                                '{{customer_id}}'
                                                ),
                                          array($insert_id,
                                                $_SESSION['customer_id']
                                                ),
                                          constant('MODULE_PAYMENT_'.strtoupper($this->code).'_REASON_1'));

    // reason 2
    $this->data['reason_2'] = str_replace(array('{{order_id}}',
                                                '{{customer_id}}',
                                                '{{order_date}}',
                                                '{{customer_name}}',
                                                '{{customer_company}}',
                                                '{{customer_email}}'
                                                ),
                                          array($insert_id,
                                                $_SESSION['customer_id'],
                                                strftime(DATE_FORMAT_SHORT),
                                                $order->customer['firstname'] . ' ' . $order->customer['lastname'],
                                                $order->customer['company'],
                                                $order->customer['email_address']
                                                ),
                                          constant('MODULE_PAYMENT_'.strtoupper($this->code).'_REASON_2'));
  }


  function _check_temp_order() {
    global $order;

		if (isset($_SESSION['sofort'][$this->code])) {
			$check_query = xtc_db_query("SELECT currency, orders_status FROM " . TABLE_ORDERS . " WHERE orders_id = '" . (int)$_SESSION['sofort'][$this->code]['oID'] . "'");
			$result = xtc_db_fetch_array($check_query);
			if ($result['orders_status'] == constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TMP_STATUS_ID')
			    || $result['currency'] != $order->info['currency']
			    || $_SESSION['cart']->cartID != $_SESSION['sofort'][$this->code]['cartID'])
			{
        // delete canceled order
        $this->_remove_order((int)$_SESSION['sofort'][$this->code]['oID']);
				unset($_SESSION['sofort'][$this->code]);
				unset($_SESSION['tmp_oID']);
			}
		}
  }


  function _remove_order($order_id) {
    $check_query = xtc_db_query("SELECT * FROM ".TABLE_ORDERS." WHERE orders_id = '".(int)$order_id."'");
    if (xtc_db_num_rows($check_query) > 0) {
      $check = xtc_db_fetch_array($check_query);
      if ($_SESSION['customer_id'] == $check['customers_id']) {
        require_once(DIR_FS_INC.'xtc_remove_order.inc.php');
        xtc_remove_order((int)$order_id, ((STOCK_LIMITED == 'true') ? 'on' : false));

        // write to log
        if ($this->logging === true) {
          $this->logger->log('Order removed: '.$order_id, 'log');
        }
      }
    }
  }


	function _setImageText($image, $text='') {
	  switch ($_SESSION['language_code']) {
	    case 'de':
	      $code = 'de_de';
	      $lang = 'ger';
	      break;
	    default:
	      $code = 'en_gb';
	      $lang = 'eng';
	      break;
	  }
		//$image = 'https://images.sofort.com/'.$code.((isset($this->ideal) && $this->ideal === true) ? '/ideal/' : '/su/').$image;
		$image = 'https://cdn.klarna.com/1.0/shared/image/generic/badge/'.$code.'/pay_now/standard/'.$image;
		$title = constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGEALT');
		$image = '<img src="'.$image.'" '.(($title != '') ? 'alt="'.$title.'" title="'.$title.'"' : '').' />';
		$description = constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGE');
		$description = str_replace('{{image}}', $image, $description);
		$description = str_replace('{{text}}', $text, $description);

		//add ks-link, if ks is active
		$description = str_replace('[[link_beginn]]', '<a onclick="javascript:window.open(\'https://www.sofort-bank.com/'.$lang.'-'.strtoupper($code).'/general/kaeuferschutz/informationen-fuer-kaeufer\',\'Information\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1020, height=900\');" style="text-decoration:underline;">', $description);
		$description = str_replace('[[link_end]]', '</a>', $description);

		return $description;
	}


  function _get_orders_status($order_id) {
    $order_status_query = xtc_db_query("SELECT orders_status FROM ".TABLE_ORDERS." WHERE orders_id = '".(int) $order_id."'");
    $order_status = xtc_db_fetch_array($order_status_query);
    return $order_status['orders_status'];
  }


  function get_version() {
    if (!isset($this->_version)) {
      require_once(DIR_FS_INC.'get_database_version.inc.php');
      $db_version = get_database_version();
      $this->_version = 'modified_'.$db_version['full'].'_v1.00';
    }
    
    return $this->_version;
  }


  public function update_sofort_status($orders_id, $order_status_id, $comments = '', $status_update = false) {
    xtc_db_query("UPDATE ".TABLE_ORDERS." SET orders_status = '".$order_status_id."' WHERE orders_id = '".(int) $orders_id."'");

    if ($this->tmpOrders === false || $status_update === true) {
      $sql_data_array = array('orders_id' => (int) $orders_id,
                              'orders_status_id' => $order_status_id,
                              'date_added' => 'now()',
                              'customer_notified' => '0',
                              'comments' => decode_htmlentities($comments),
                              'comments_sent' => '0'
                              );
      xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

      $sql_data_array = array('transaction_id' => $_SESSION['sofort'][$this->code]['tID'],
                              'order_id' => $orders_id
                              );
      xtc_db_perform($this->code, $sql_data_array);
    }

  }


  function status_install() {
    if ($this->enabled === true) {
      $stati = array('SOFORT_INST_ORDER_STATUS_TMP_NAME' => 'MODULE_PAYMENT_'.strtoupper($this->code).'_TMP_STATUS_ID',
                     'SOFORT_INST_ORDER_STATUS_UNC_NAME' => 'MODULE_PAYMENT_'.strtoupper($this->code).'_UNC_STATUS_ID',
                     'SOFORT_INST_ORDER_STATUS_ORDER_NAME' => 'MODULE_PAYMENT_'.strtoupper($this->code).'_ORDER_STATUS_ID',
                     'SOFORT_INST_ORDER_STATUS_REC_NAME' => '',
                     'SOFORT_INST_ORDER_STATUS_REF_NAME' => '',
                     'SOFORT_INST_ORDER_STATUS_LOSS_NAME' => '',
                     );
      foreach($stati as $statusname => $statusid) {

        $languages_query = xtc_db_query("SELECT * FROM " . TABLE_LANGUAGES . " ORDER BY sort_order");
        while ($languages = xtc_db_fetch_array($languages_query)) {
          if (is_file(DIR_FS_LANGUAGES.$languages['directory'].'/modules/payment/sofort_payment.php')) {
            include(DIR_FS_LANGUAGES.$languages['directory'].'/modules/payment/sofort_payment.php');
          }
          if (${$statusname} != '') {
            $check_query = xtc_db_query("SELECT orders_status_id
                                           FROM " . TABLE_ORDERS_STATUS . "
                                          WHERE orders_status_name = '" .${$statusname}. "'
                                            AND language_id = '".$languages['languages_id']."'
                                          LIMIT 1");
            $status = xtc_db_fetch_array($check_query);
            if(xtc_db_num_rows($check_query) < 1 || (${$statusid} && $status['orders_status_id'] != ${$statusid})) {
              if (!${$statusid}) {
                $status_query = xtc_db_query("SELECT max(orders_status_id) as status_id FROM " . TABLE_ORDERS_STATUS);
                $status = xtc_db_fetch_array($status_query);
                ${$statusid} = $status['status_id']+1;
              }
              $check_query = xtc_db_query("SELECT orders_status_id
                                             FROM " . TABLE_ORDERS_STATUS . "
                                            WHERE orders_status_id = '".${$statusid} ."'
                                              AND language_id='".$languages['languages_id']."'");
              if(xtc_db_num_rows($check_query)<1) {
                // insert status
                $sql_data_array = array('orders_status_name' => ${$statusname},
                                        'orders_status_id' => ${$statusid},
                                        'language_id' => $languages['languages_id']
                                        );
                xtc_db_perform(TABLE_ORDERS_STATUS, $sql_data_array);

                // update status
                if ($statusid != '') {
                  xtc_db_query("UPDATE " . TABLE_CONFIGURATION . "
                                   SET configuration_value = '" . ${$statusid} . "',
                                       last_modified = NOW()
                                 WHERE configuration_key = '" . $statusid . "'");
                }
              }
            } else {
              ${$statusid} = $status['orders_status_id'];

              // update status
               if ($statusid != '') {
                xtc_db_query("UPDATE " . TABLE_CONFIGURATION . "
                                 SET configuration_value = '" . ${$statusid} . "',
                                     last_modified = NOW()
                               WHERE configuration_key = '" . $statusid . "'");
              }
            }
          }
        }
      }
    }
  }


	function install_default() {
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_".strtoupper($this->code)."_STATUS', 'True', '6', '3', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_".strtoupper($this->code)."_TMP_ORDER', 'True', '6', '3', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_".strtoupper($this->code)."_LOGGING', 'False', '6', '3', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_".strtoupper($this->code)."_ALLOWED', '', '6', '0', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_".strtoupper($this->code)."_SORT_ORDER', '1', '6', '20', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_PAYMENT_".strtoupper($this->code)."_ZONE', '0', '6', '2', 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_".strtoupper($this->code)."_ORDER_STATUS_ID', '0',  '6', '10', 'xtc_cfg_pull_down_order_statuses_sofort(', 'xtc_get_order_status_name', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_".strtoupper($this->code)."_TMP_STATUS_ID', '0',  '6', '8', 'xtc_cfg_pull_down_order_statuses_sofort(', 'xtc_get_order_status_name', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_".strtoupper($this->code)."_UNC_STATUS_ID', '0',  '6', '9', 'xtc_cfg_pull_down_order_statuses_sofort(', 'xtc_get_order_status_name', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_".strtoupper($this->code)."_REC_STATUS_ID', '0',  '6', '11', 'xtc_cfg_pull_down_order_statuses_sofort(', 'xtc_get_order_status_name', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_".strtoupper($this->code)."_REF_STATUS_ID', '0',  '6', '11', 'xtc_cfg_pull_down_order_statuses_sofort(', 'xtc_get_order_status_name', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_".strtoupper($this->code)."_LOSS_STATUS_ID', '0',  '6', '12', 'xtc_cfg_pull_down_order_statuses_sofort(', 'xtc_get_order_status_name', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_".strtoupper($this->code)."_REASON_1', 'Nr. {{order_id}} Kd-Nr. {{customer_id}}',  '6', '4', 'xtc_cfg_select_option(array(\'Nr. {{order_id}} Kd-Nr. {{customer_id}}\',\'-TRANSACTION-\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_".strtoupper($this->code)."_REASON_2', '" . xtc_db_input(STORE_NAME) . "', '6', '4', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_".strtoupper($this->code)."_IMAGE', 'Logo & Text',  '6', '6', 'xtc_cfg_select_option(array(\'Infographic\',\'Logo & Text\'), ', now())");

	  xtc_db_query("CREATE TABLE IF NOT EXISTS `".$this->code."` (
                  `transaction_id` VARCHAR( 128 ) NOT NULL ,
                  `order_id` INT( 11 ) NOT NULL ,
                  INDEX ( `transaction_id` )
                  )");

    $table_array = array(
      array('column' => 'payment_class', 'default' => 'VARCHAR(64) NOT NULL'),
      array('column' => 'payment_method', 'default' => 'VARCHAR(128) NOT NULL'),
    );
    foreach ($table_array as $table) {
      xtc_db_query("ALTER TABLE ".TABLE_ORDERS." MODIFY ".$table['column']." ".$table['default']."");
    }
	}


	function keys_default() {
		return array(0 => 'MODULE_PAYMENT_'.strtoupper($this->code).'_STATUS' ,
                 10 => 'MODULE_PAYMENT_'.strtoupper($this->code).'_TMP_ORDER',
                 11=> 'MODULE_PAYMENT_'.strtoupper($this->code).'_ALLOWED' ,
                 12 => 'MODULE_PAYMENT_'.strtoupper($this->code).'_ZONE' ,
                 13 => 'MODULE_PAYMENT_'.strtoupper($this->code).'_REASON_1',
                 14 => 'MODULE_PAYMENT_'.strtoupper($this->code).'_REASON_2' ,
                 15 => 'MODULE_PAYMENT_'.strtoupper($this->code).'_IMAGE' ,
                 16 => 'MODULE_PAYMENT_'.strtoupper($this->code).'_TMP_STATUS_ID' ,
                 17 => 'MODULE_PAYMENT_'.strtoupper($this->code).'_UNC_STATUS_ID' ,
                 18 => 'MODULE_PAYMENT_'.strtoupper($this->code).'_ORDER_STATUS_ID' ,
                 19 => 'MODULE_PAYMENT_'.strtoupper($this->code).'_REC_STATUS_ID',
                 20 => 'MODULE_PAYMENT_'.strtoupper($this->code).'_REF_STATUS_ID',
                 21 => 'MODULE_PAYMENT_'.strtoupper($this->code).'_LOSS_STATUS_ID',
                 22 => 'MODULE_PAYMENT_'.strtoupper($this->code).'_SORT_ORDER',
                 23 => 'MODULE_PAYMENT_'.strtoupper($this->code).'_LOGGING',
                 );
	}

}


/**
 * xtc_cfg_pull_down_order_statuses_sofort()
 *
 * @param mixed $order_status_id
 * @param string $key
 * @return
 */
if (!function_exists('xtc_cfg_pull_down_order_statuses_sofort')) {
  function xtc_cfg_pull_down_order_statuses_sofort($order_status_id, $key = '') {
    $name = (($key) ? 'configuration['.$key.']' : 'configuration_value');
    $statuses_array = array (array ('id' => '-1', 'text' => TEXT_NO_STATUSUPDATE));
    $statuses_array[] = array ('id' => '1', 'text' => TEXT_DEFAULT);
    $statuses_query = xtc_db_query("SELECT orders_status_id,
                                           orders_status_name
                                      FROM ".TABLE_ORDERS_STATUS."
                                     WHERE language_id = '".(int)$_SESSION['languages_id']."'
                                  ORDER BY orders_status_name");
    while ($statuses = xtc_db_fetch_array($statuses_query)) {
      $statuses_array[] = array ('id' => $statuses['orders_status_id'], 'text' => $statuses['orders_status_name']);
    }
    return xtc_draw_pull_down_menu($name, $statuses_array, $order_status_id);
  }
}
?>