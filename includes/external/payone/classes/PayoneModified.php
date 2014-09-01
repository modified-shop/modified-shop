<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
 	 based on:
	  (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
	  (c) 2002-2003 osCommerce - www.oscommerce.com
	  (c) 2001-2003 TheMedia, Dipl.-Ing Thomas Plänkers - http://www.themedia.at & http://www.oscommerce.at
	  (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com
    (c) 2013 Gambio GmbH - http://www.gambio.de
  
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

require_once (DIR_FS_EXTERNAL.'payone/php/Payone/Bootstrap.php');
//require_once (DIR_FS_EXTERNAL.'payone/classes/FileLog.php');

class PayoneModified {

	protected $_client_api_url;
	protected $_frontend_url;
	protected $_server_api_url;

	public function __construct() {
		$this->_client_api_url = 'https://secure.pay1.de/client-api/';
		$this->_frontend_url = 'https://secure.pay1.de/frontend/';
		$this->_server_api_url = 'https://api.pay1.de/post-gateway/';

		$bootstrap = new Payone_Bootstrap();
		$bootstrap->init();
	}

	public function log($message, $filename='payone.log') {
    error_log(strftime('%d/%m/%Y %H:%M:%S').' | '.$message."\n", 3, DIR_FS_LOG.$filename);
	}

	public function getPayoneConfig() {
		$payone_config = new Payone_Config();
		
		$payone_config->setValue('api/default/protocol/loggers/Payone_Protocol_Logger_ModifiedLog/mode', 'api');
		$payone_config->setValue('api/default/protocol/loggers/Payone_Protocol_Logger_Log4php/filename', DIR_FS_LOG.'payone_sdk_api.log');
		$payone_config->setValue('api/default/protocol/loggers/Payone_Protocol_Logger_Log4php/max_file_size', '5MB');
		
		$payone_config->setValue('transaction_status/default/protocol/loggers/Payone_Protocol_Logger_Log4php/filename', DIR_FS_LOG.'payone_sdk_transaction.log');
		$payone_config->setValue('transaction_status/default/protocol/loggers/Payone_Protocol_Logger_ModifiedLog/mode', 'transactions');

		return $payone_config;
	}

  public function checkConfig() {
    $check_query = xtc_db_query("SHOW TABLES LIKE 'payone_config'");
    if (xtc_db_num_rows($check_query) > 0) {
      return true;
    }
    
    return false;
  }
  
  public function installConfig() {
    if ($this->checkConfig() === false) {
      include(DIR_FS_EXTERNAL.'payone/install/payone_install.php');
    }
  }
  
	public function getStatusNames() {
		$names = array('approved', 
		               'appointed', 
		               'capture', 
		               'paid', 
		               'underpaid', 
		               'cancelation', 
		               'refund', 
		               'debit', 
		               'transfer', 
		               'reminder', 
		               'vauthorization', 
		               'vsettlement', 
		               'invoice');
		return $names;
	}

	public function getPaymentTypes() {
		$payment_types = array('creditcard' => array('visa', 
		                                             'mastercard', 
		                                             'amex', 
		                                             'cartebleue', 
		                                             'dinersclub', 
		                                             'discover', 
		                                             'jcb', 
		                                             'maestro'),
                           'onlinetransfer' => array('sofortueberweisung', 
                                                     'giropay', 
                                                     'eps', 
                                                     'pfefinance', 
                                                     'pfcard', 
                                                     'ideal'),
                           'ewallet' => array('paypal'),
                           'accountbased' => array('lastschrift', 
                                                   'invoice', 
                                                   'prepay', 
                                                   'cod'),
                           'installment' => array('billsafe', 
                                                  'commerzfinanz',
                                                  'klarna'),
		);
		return $payment_types;
	}

	public function getBankGroups() {
	  $bankgroups_array = array();
	  
	  $bankgroups_query = xtc_db_query("SELECT * FROM `payone_bankgroups` ORDER BY bank_name");
	  while ($bankgroups = xtc_db_fetch_array($bankgroups_query)) {
	    $bankgroups_array[$bankgroups['identifier']][$bankgroups['bank_code']] = $bankgroups['bank_name'];
	  }

		return $bankgroups_array;
	}

  public function getSepaCountries() {
    $sepa_countries_array = array();
    
    $sepa_countries_query = xtc_db_query("SELECT * FROM `payone_sepa_countries` ORDER BY countries_name");
    while ($sepa_countries = xtc_db_fetch_array($sepa_countries_query)) {
      $sepa_countries_array[] = $sepa_countries;
    }
    
    return $sepa_countries_array;
  }

	protected function _getDefaultConfig() {
		$config = array(
			'orders_status' => array(
				'tmp' => '1',
			),

			'global' => array(
				'merchant_id' => 'no_id',
				'portal_id' => 'no_id',
				'subaccount_id' => 'no_id',
				'key' => 'no_key',
				'operating_mode' => 'test',
				'authorization_method' => 'auth',
				'send_cart' => 'false',
			),

			'address_check' => array(
				'active' => 'false',
				'operating_mode' => 'test', // test | live
				'billing_address' => 'none', // none | basic | person
				'delivery_address' => 'none', // none | basic | person
				'automatic_correction' => 'no', // no | yes | user
				'error_mode' => 'abort', // abort | reenter | check | continue
				'min_cart_value' => '0',
				'max_cart_value' => '10000',
				'validity' => '3',
				'pstatus' => array(
					'nopcheck' => 'green',
					'fullnameknown' => 'green',
					'lastnameknown' => 'green',
					'nameunknown' => 'green',
					'nameaddrambiguity' => 'green',
					'undeliverable' => 'green',
					'dead' => 'green',
					'postalerror' => 'green',
				),
			),

			'credit_risk' => array(
				'active' => 'false',
				'operating_mode' => 'test',
				'timeofcheck' => 'before',
				'typeofcheck' => 'iscorehard',
				'newclientdefault' => 'green',
				'validity' => '3',
				'min_cart_value' => '100',
				'max_cart_value' => '5000',
				'checkforgenre' => array(),
				'error_mode' => 'continue',
				'notice' => array(
					'active' => 'false',
				),
				'confirmation' => array(
					'active' => 'false',
				),
				'abtest' => array(
					'active' => 'false',
					'value' => '3',
				),
			),
		);

		foreach($this->getStatusNames() as $sname) {
			$config['orders_status'][$sname] = '1';
		}

		return $config;
	}

	protected function _getGenreModuleMapping() {
		$mapping = array(
			'creditcard' => 'cc',
			'onlinetransfer' => 'otrans',
			'ewallet' => 'wlt',
			'accountbased' => 'account',
			'installment' => 'installment',
		);
		return $mapping;
	}

	public function _getKlarnaCountries() {
		$KlarnaCountries = array('DE', 'AT', 'NL', 'DK', 'FI', 'NO', 'SE');
    return $KlarnaCountries;
	}

	protected function _getPaymentGenreDefaultConfig($genre) {
		$payment_types = $this->getPaymentTypes();
		$valid_genres = array_keys($payment_types);
		if (!in_array($genre, $valid_genres)) {
			throw new Exception('invalid payment genre '.$genre);
		}
		$default_config = $this->_getDefaultConfig();
		$configuration = array(
			'genre' => $genre,
			'global_override' => 'false',
			'global' => $default_config['global'],
			'name' => constant('PAYGENRE_'.strtoupper($genre)).' '.uniqid(),
			'active' => 'false',
			'order' => 0,
			'min_cart_value' => 0,
			'max_cart_value' => 5000,
			'operating_mode' => 'test',
			'countries' => array(),
			'allow_red' => 'false',
			'allow_yellow' => 'false',
			'allow_green' => 'true',
			'genre_specific' => array(),
		);

		foreach($payment_types[$genre] as $pt) {
			$configuration['types'][$pt]['active'] = 'false';
			$configuration['types'][$pt]['name'] = 'paymenttype_'.$pt;
		}

		switch($genre) {
			case 'creditcard':
				$configuration['genre_specific']['check_cav'] = 'false';
				break;
			case 'accountbased':
				$configuration['genre_specific']['check_bankdata'] = 'none';
				$configuration['genre_specific']['sepa_account_countries'] = array();
				$configuration['genre_specific']['sepa_display_ktoblz'] = 'false';
				$configuration['genre_specific']['sepa_use_managemandate'] = 'false';
				$configuration['genre_specific']['sepa_download_pdf'] = 'false';
				break;
			case 'onlinetransfer':
			case 'ewallet':
			  break;
			case 'installment':
				$configuration['genre_specific']['klarna'] = array('storeid' => '',
                                                           'countries' => array()
                                                           );
				break;
		}

		return $configuration;
	}

	public function getConfig($identifier = null) {				
    if ($this->checkConfig()) {
      $configuration_flat = array();
      $query = xtc_db_query("SELECT * FROM `payone_config`");
      while($row = xtc_db_fetch_array($query)) {
        $configuration_flat[$row['path']] = $row['value'];
      }
      $configuration = $this->_inflateArray($configuration_flat);

      $default_config = $this->_getDefaultConfig();

      $configuration = $this->mergeConfigs($default_config, $configuration);
      if (!empty($identifier) && array_key_exists($identifier, $configuration)) {
        return $configuration[$identifier];
      }
      else {
        return $configuration;
      }
		} else {
		  return array();
		}
	}

	public function getGenresConfig() {
		$config = $this->getConfig();
		$genre_configs = array();
		$order_array = array();
		foreach($config as $topkey => $data) {
			if (strpos($topkey, 'paymentgenre') === false) {
				continue;
			}
			$order_key = sprintf('%05d_%s', $data['order'], $topkey);
			$order_array[$order_key] = $topkey;
		}
		ksort($order_array);
		foreach($order_array as $sort_key => $top_key) {
			$genre_configs[$top_key] = $config[$top_key];
		}
		return $genre_configs;
	}

	public function setConfig($configuration) {
		$flatconfig = $this->_flattenArray($configuration);
		xtc_db_query("TRUNCATE `payone_config`");
		foreach($flatconfig as $path => $value) {
			xtc_db_query("INSERT INTO `payone_config` SET `path` = '".xtc_db_input($path)."', `value` = '".xtc_db_input($value)."'");
		}

		$this->adjustSortOrders();
	}

	public function adjustSortOrders() {
		$gconfig = $this->getGenresConfig();
		$module_mapping = $this->_getGenreModuleMapping();
		foreach($gconfig as $gc) {
			$module = $module_mapping[$gc['genre']];
			$query = "UPDATE `configuration` SET `configuration_value` = ".(int)$gc['order']." WHERE `configuration_key` = 'MODULE_PAYMENT_PAYONE_".strtoupper($module)."_SORT_ORDER'";
			xtc_db_query($query);
		}
		$modules_order_result = xtc_db_query("SELECT `configuration_key`  FROM `configuration` WHERE `configuration_key` LIKE 'module_payment_%_sort_order' order by configuration_value asc");
		$payment_modules = array();
		while($row = xtc_db_fetch_array($modules_order_result)) {
			$module = strtolower(preg_replace('/MODULE_PAYMENT_(.*)_SORT_ORDER/', '$1', $row['configuration_key']));
			$payment_modules[] = $module.'.php';
		}
		xtc_db_query("UPDATE `configuration` SET `configuration_value` = '".xtc_db_input(implode(';', $payment_modules))."' WHERE `configuration_key` = 'MODULE_PAYMENT_INSTALLED'");
	}

	public function mergeConfigs($old_config, $new_config) {
		$old_keys = array_keys($old_config);
		if (is_array($old_keys) && isset($old_keys[0]) && $old_keys[0] === 0)
		{
			# special case: numerically indexed array, e.g. list of countries
			$merged = array_values(array_unique($new_config));
		}
		else
		{
			$merged = array();
			foreach($old_config as $key => $value) {
				if (empty($new_config[$key]) && !is_numeric($new_config[$key])) {
					if (array_key_exists($key, $new_config)) {
						if (is_array($value)) {
							$merged[$key] = array();
						}
						else if ($value == 'true' || $value == 'false') {
							$merged[$key] = 'false';
						}
						else {
							$merged[$key] = '';
						}
					}
					else {
						if ($value == 'true' || $value == 'false') {
							$merged[$key] = 'false';
						}
						else {
							$merged[$key] = $value;
						}
					}
				}
				else {
					if (is_array($value)) {
						$merged[$key] = $this->mergeConfigs($value, $new_config[$key]);
					}
					else if ($value == 'true' || $value == 'false') {
						$merged[$key] = $new_config[$key] == 'true' ? 'true' : 'false';
					}
					else {
						$merged[$key] = $new_config[$key];
					}
				}

				if ($value == 'true' || $value == 'false') {
					$merged[$key] = $new_config[$key] == 'true' ? 'true' : 'false';
				}
			}
			foreach($new_config as $nkey => $nvalue) {
				if (!array_key_exists($nkey, $merged)) {
					$merged[$nkey] = $nvalue;
				}
			}
		}
		return $merged;
	}

	protected function _flattenArray($input, $prefix = '') {
		$divider = '/';
		if (!empty($prefix)) {
			$prefix .= $divider;
		}
		$output = array();
		foreach($input as $key => $value) {
			if (is_array($value)) {
				if (empty($value)) {
					$output[$prefix.$key] = '';
				}
				else {
					$flattened = $this->_flattenArray($value, $key);
					foreach($flattened as $fkey => $fvalue) {
						$output[$prefix.$fkey] = $fvalue;
					}
				}
			}
			else {
				$output[$prefix.$key] = $value;
			}
		}
		return $output;
	}

	protected function _inflateArray($input) {
		$divider = '/';
		$output = array();
		foreach($input as $key => $value) {
			$keys = explode($divider, $key);
			$subarray =& $output;
			while(count($keys) > 1) {
				$subkey = array_shift($keys);
				if (is_array($subarray) && array_key_exists($subkey, $subarray) && !is_array($subarray[$subkey])) {
					$subarray[$subkey] = array();
				}
				$subarray =& $subarray[$subkey];
			}
			$final_key = array_shift($keys);
			$subarray[$final_key] = $value;
		}
		return $output;
	}

	public function dumpConfig() {
		$t_filename = DIR_FS_CATALOG.'cache/payone-config-'.uniqid().'.cfg';
		$t_fh = @fopen($t_filename, 'w');
		if ($t_fh == false)
		{
			return false;
		}
		$config_array = $this->getConfig();
		$config_flat_array = $this->_flattenArray($config_array);
		foreach($config_flat_array as $cfg_key => $cfg_value)
		{
			fwrite($t_fh, $cfg_key. "\t". $cfg_value ."\n");
		}
		fclose($t_fh);
		return $t_filename;
	}

	public function addPaymentGenreConfig($genre) {
		$genre_config = $this->_getPaymentGenreDefaultConfig($genre);
		$identifier = 'paymentgenre_'.uniqid();
		$configuration = $this->getConfig();
		$configuration[$identifier] = $genre_config;
		$this->setConfig($configuration);
	}

	public function getPaymentGenreIdentifiers() {
		$configuration = $this->getConfig();
		$config_identifiers = array_keys($configuration);
		$paymentgenre_identifiers = array();
		foreach($config_identifiers as $ci) {
			if (strpos($ci, 'paymentgenre_') === 0) {
				$paymentgenre_identifiers[] = $ci;
			}
		}
		return $paymentgenre_identifiers;
	}

	public function getTypesForGenre($genre_identifier) {
		$pgenre = $this->getConfig($genre_identifier);
		$types = array();
		if ($pgenre['genre'] == 'creditcard') {
			$cctypes = array('visa' => 'V', 
			                 'mastercard' => 'M', 
			                 'amex' => 'A', 
			                 'cartebleue' => 'B', 
			                 'dinersclub' => 'D', 
			                 'discover' => 'C', 
			                 'jcb' => 'J', 
			                 'maestro' => 'O');
			foreach($cctypes as $cctype => $shorttype) {
				if ($pgenre['types'][$cctype]['active'] != 'true') {
					continue;
				}
				$types[] = array(
					'typekey' => $cctype,
					'shorttype' => $shorttype,
					'typename' => $pgenre['types'][$cctype]['name'],
				);
			}
		}

		return $types;
	}

	public function getStandardParameters($request = null, $config_override = null) {
		$config = $this->getConfig('global');
		if ($config_override != null) {
			$config = array_merge($config, $config_override);
		}

    $query = xtc_db_query('select * from database_version');
    while ($row = xtc_db_fetch_array($query)) {
      $db_version_check = $row['version'];
    }

		$params = array(
			'mid' => $config['merchant_id'],
			'portalid' => $config['portal_id'],
			'aid' => $config['subaccount_id'],
			'mode' => $config['operating_mode'],
			'responsetype' => 'REDIRECT',
			'encoding' => 'ISO-8859-1',
			'language' => strtolower($_SESSION['language_code']),
			'solution_name' => PROJECT_VERSION,
			'solution_version' => $db_version_check,
			'integrator_name' => 'Modified',
			'integrator_version' => '1.00',
		);
		if ($request !== null) {
			$params['request'] = $request;
		}
		return $params;
	}

	public function computeHash($params, $key) {
		$hash_keys = array('access_aboperiod', 'access_aboprice', 'access_canceltime', 'access_expiretime', 'access_period', 'access_price', 'access_starttime',
			'access_vat', 'accesscode', 'accessname', 'addresschecktype', 'aid', 'amount', 'backurl', 'booking_date', 'checktype', 'clearingtype', 'consumerscoretype',
			'currency', 'customerid', 'document_date', 'due_time', 'eci', 'encoding', 'errorurl', 'exiturl', 'invoice_deliverymode', 'invoiceappendix',
			'invoiceid', 'mid', 'mode', 'narrative_text', 'param', 'portalid', 'productid', 'reference', 'request', 'responsetype', 'settleaccount',
			'settleperiod', 'settletime', 'storecarddata', 'successurl', 'userid', 'vaccountname', 'vreference');
		$varnum_hash_keys = array('de[\d+]', 'id[\d+]', 'no[\d+]', 'pr[\d+]', 'ti[\d+]', 'va[\d+]');
		$hash_data = array();
		foreach($params as $pkey => $pvalue) {
			if (in_array($pkey, $hash_keys) || preg_match('/^(de|id|no|pr|ti|va)\[\d+\]$/', $pkey) == 1) {
				$hash_data[$pkey] = $pvalue;
			}
		}
		ksort($hash_data);
		$hash_string = implode('', $hash_data);
		$hash_string .= $key;
		//$this->log("computing hash for $hash_string");
		$hash = md5($hash_string);
		return $hash;
	}

	public function getFormActionURL() {
		return $this->_client_api_url;
	}

	public function retrieveSepaMandate($file_reference)
	{
		$global_config = $this->getConfig('global');
		$standard_parameters = $this->getStandardParameters();
		$builder = new Payone_Builder($this->getPayoneConfig());
		$service = $builder->buildServiceManagementGetFile();
		$request_data = array
			(
				'key' => $global_config['key'],
				'file_reference' => $file_reference,
				'file_type' => 'SEPA_MANDATE',
				'file_format' => 'PDF',
			);
		$params = array_merge($standard_parameters, $request_data);
		$request = new Payone_Api_Request_GetFile($params);
		$this->log('getFile request:'.PHP_EOL.print_r($request, true));
		$result = $service->getFile($request);
		//$this->log('getFile result:'.PHP_EOL.print_r($result, true));
		if ($result instanceof Payone_Api_Response_Management_GetFile)
		{
			$t_pdf_data = $result->getRawResponse();
			$mandate_filename = 'sepa_mandate_'.$_SESSION['customer_id'].'_'.md5($file_reference).'.pdf';
			$bytes_written = file_put_contents(DIR_FS_DOWNLOAD_PUBLIC.$mandate_filename, $t_pdf_data);
			if ($bytes_written === false) {
				$this->log('ERROR writing mandate file '.DIR_FS_DOWNLOAD_PUBLIC.$mandate_filename);
				return false;
			}
			else
			{
				$this->log('SEPA mandate written to '.$mandate_filename.' ('.$bytes_written.' bytes)');
				return $mandate_filename;
			}

		}
		else
		{
			return false;
		}
	}

	public function getAvailablePaymentGenres() {
		$config = $this->getGenresConfig();
		$available = array();

		$cart_value = $_SESSION['cart']->show_total();
		$billto_address = $this->_getAddressBookEntry($_SESSION['billto'], $_SESSION['customer_id']);

		foreach($config as $topkey => $pgconfig) {
			if ($pgconfig['active'] != 'true') {
				$this->log("$topkey not active");
				continue;
			}
			if ($pgconfig['min_cart_value'] > $cart_value || $pgconfig['max_cart_value'] < $cart_value) {
				$this->log("$topkey cart value out of range");
				continue;
			}
			if (!is_array($pgconfig['countries']) || !in_array($billto_address['countries_iso_code_2'], $pgconfig['countries'])) {
				$this->log("$topkey country ".$billto_address['countries_iso_code_2']." not activated");
				continue;
			}
			$available[$topkey] = $pgconfig;
		}

		return $available;
	}

	protected function _getAddressBookEntry($ab_id, $customers_id = null) {
		$query = "SELECT ab.*, 
		                 c.customers_telephone, 
		                 DATE(c.customers_dob) AS dob_date, 
		                 cy.* 
		            FROM ".TABLE_ADDRESS_BOOK." ab
			     LEFT JOIN ".TABLE_CUSTOMERS." c 
			               ON c.customers_id = ab.customers_id
			     LEFT JOIN ".TABLE_COUNTRIES." cy 
			               ON cy.countries_id = ab.entry_country_id
			         WHERE ab.address_book_id = '".(int)$ab_id."'";
			         
		if ($customers_id !== null) {
			$query .= " AND c.customers_id = '".(int)$customers_id."'";
		}

		/* we need uncached data here because the database entry may have changed within the current request */
		$result = xtc_db_query($query, 'db_link', false);
		$entry = false;
		while($row = xtc_db_fetch_array($result)) {
			$entry = $row;
		}
		return $entry;
	}

	public function getAddressBookEntry($ab_id, $customer_id = null) {
		return $this->_getAddressBookEntry($ab_id, $customer_id);
	}

	public function getAddressHash($ab_id) {
		$hash_fields = array('entry_gender', 
		                     'entry_company', 
		                     'entry_firstname', 
		                     'entry_lastname', 
		                     'entry_street_address', 
		                     'entry_suburb',
			                   'entry_postcode', 
			                   'entry_city', 
			                   'entry_state', 
			                   'entry_country_id', 
			                   'entry_zone_id');
		$ab_entry = $this->_getAddressBookEntry($ab_id);
		$hash_input = '';
		foreach($hash_fields as $key) {
			$value = $ab_entry[$key];
			$hash_input .= $value;
		}
		$hash = md5($hash_input);
		return $hash;
	}

	public function saveTransaction($orders_id, $status, $txid, $userid) {
	  $sql_data_transactions_array = array('orders_id' => (int)$orders_id,
	                                       'status' => $status,
	                                       'txid' => $txid,
	                                       'userid' => $userid,
	                                       'created' => 'now()',
	                                       'last_modified' => 'now()');
	  xtc_db_perform('payone_transactions', $sql_data_transactions_array);  
		$this->log("transaction saved: orders_id $orders_id, status $status, txid $txid, userid $userid");
	}

	public function getOrdersData($orders_id) {
		$data = array();
		// transaction data
		$tx_query = xtc_db_query("SELECT * FROM `payone_transactions` WHERE `orders_id` = '".(int)$orders_id."'");
		$data['transactions'] = array();
		while($tx_row = xtc_db_fetch_array($tx_query)) {
			$data['transactions'][] = $tx_row;
		}

		$data['transaction_status'] = $this->getTransactionStatus($orders_id);

		return $data;
	}

  protected function sendTransactionStatus($url, $params, $timeout) {
    if ($timeout == '' || $timeout < 1) {
      $timeout = 30;
    }
    $urlArray = parse_url($url);

    $urlHost = $urlArray['host'];
    $urlPath = ((isset($urlArray['path'])) ? $urlArray['path'] : '');
    $urlScheme = ((isset($urlArray['scheme'])) ? $urlArray['scheme'] : 'http');
    $urlQuery = ((isset($urlArray['query'])) ? '?' . $urlArray['query'] : '');

    $curl = curl_init($urlScheme . "://" . $urlHost . $urlPath . $urlQuery);

    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params, null, '&'));
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_TIMEOUT, (int)$timeout);

    $result = curl_exec($curl);

    if (curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200) {
      $this->log("sendTransactionStatus invalid:\n".print_r($result, true));
    }
    elseif (curl_error($curl)) {
      $this->log("sendTransactionStatus error ".curl_errno($curl) . ": " . curl_error($curl));
    }
    else {
      $this->log("sendTransactionStatus success:\n".print_r($result, true));
    }
    curl_close($curl);
  }

	public function saveTransactionStatus($txstatus) {
		if (empty($txstatus['reference'])) {
			$this->log("received TxStatus w/o reference!");
			return;
		}
		$config = $this->getConfig();
		$key_valid = false;
		if (md5($config['global']['key']) == $txstatus['key']) {
			$key_valid = true;
		}
		else {
			$paymentgenre_identifiers = $this->getPaymentGenreIdentifiers();
			foreach($paymentgenre_identifiers as $pg_id) {
				if (md5($config[$pg_id]['global']['key']) == $txstatus['key']) {
					$key_valid = true;
				}
			}
		}
		if ($key_valid == true) {
		  $sql_data_status_array = array('orders_id' => (int)$txstatus['reference'],
		                                 'received' => 'now()');
		  xtc_db_perform('payone_txstatus', $sql_data_status_array);                              
			$txstatus_id = xtc_db_insert_id();
			
			foreach($txstatus as $key => $value) {
        $sql_data_statusdata_array = array('`payone_txstatus_id`' => $txstatus_id,
                                           '`key`' => $key,
                                           '`value`' => ((is_array($value)) ? implode('||', $value) : $value)
                                           );
        xtc_db_perform('payone_txstatus_data', $sql_data_statusdata_array);
			}

		  $sql_data_transactions_array = array('status' => strtoupper($txstatus['txaction']),
		                                       'last_modified' => 'now()');
		  xtc_db_perform('payone_transactions', $sql_data_transactions_array, 'update', "txid='".$txstatus['txid']."'");                              
			
			if (in_array($txstatus['txaction'], $this->getStatusNames())) {
        $sql_data_orders_array = array('orders_status' => (int)$config['orders_status'][$txstatus['txaction']],
                                       'last_modified' => 'now()');
        xtc_db_perform(TABLE_ORDERS, $sql_data_orders_array, 'update', "orders_id='".(int)$txstatus['reference']."'");                              

        $sql_data_array = array('orders_id' => (int)$txstatus['reference'],
                                'orders_status_id' => (int)$config['orders_status'][$txstatus['txaction']],
                                'date_added' => 'now()',
                                'customer_notified' => '0',
                                'comments' => STATUS_UPDATED_BY_PAYONE,
                                'comments_sent' => '0'
                                );
        xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

        // send Transaction Status
        if ($config['orders_status_redirect']['url'][$txstatus['txaction']] != '') {
          $this->sendTransactionStatus($config['orders_status_redirect']['url'][$txstatus['txaction']], $txstatus, $config['orders_status_redirect']['timeout'][$txstatus['txaction']]);
        }
			}
		}
		else {
			$this->log("received TxStatus with an invalid key! TxStatus will not be processed.");
		}

		// logging
		$message_parts = array();
		foreach($txstatus as $name => $value) {
			$message_parts[] = "$name=$value";
		}
		$message = implode('|', $message_parts);
		list($msec, $sec) = explode(' ', microtime());
		$sql_data_array = array('event_id' => (int)(($sec + $msec) * 1000),
		                        'date_created' => 'now()',
		                        'log_count' => '0',
		                        'log_level' => '0',
		                        'message' => $message,
		                        'customers_id' => '0');
		$this->log(print_r($sql_data_array, true));
		xtc_db_perform('payone_transactions_log', $sql_data_array);
	}

	public function getTransactionStatus($orders_id) {
		// get metadata first
		$txstatus = array();
		$txstatus_query = xtc_db_query("SELECT * FROM `payone_txstatus` WHERE orders_id = '".(int)$orders_id."'");
		while($txstatus_row = xtc_db_fetch_array($txstatus_query)) {
			$txstatus_row['data'] = array();
			$txstatus[] = $txstatus_row;
		}

		// get details
		foreach($txstatus as $idx => $txs) {
			$txstatusdata_query = xtc_db_query("SELECT * FROM `payone_txstatus_data` WHERE payone_txstatus_id = '".(int)$txs['payone_txstatus_id']."'");
			while($txsd_row = xtc_db_fetch_array($txstatusdata_query)) {
				$txstatus[$idx]['data'][$txsd_row['key']] = $txsd_row['value'];
			}
		}

		return $txstatus;
	}

	public function getCaptureData($orders_id) {
		// a transaction can be captured if it is "appointed"
		$capture_data = false; // i.e. cannot be captured
		$orders_data = $this->getOrdersData($orders_id);
		foreach($orders_data['transaction_status'] as $tstatus) {
			if (strtoupper($tstatus['data']['txaction']) == 'APPOINTED') {
				$capture_data = array(
					'txid' => $tstatus['data']['txid'],
					'price' => $tstatus['data']['price'],
					'portalid' => $tstatus['data']['portalid'],
					'aid' => $tstatus['data']['aid'],
					'currency' => $tstatus['data']['currency'],
					'sequencenumber' => $tstatus['data']['sequencenumber'],
				);
			}
		}

		return $capture_data;
	}

	protected function _getNextSequencenumber($txid) {
		$query = "SELECT MAX(`d`.`value`) AS max_sequence 
		            FROM `payone_transactions` t
			     LEFT JOIN `payone_txstatus` s 
			               ON s.orders_id = t.orders_id
			     LEFT JOIN payone_txstatus_data d 
			               ON d.payone_txstatus_id = s.payone_txstatus_id 
			                  AND d.key = 'sequencenumber'
			         WHERE t.txid = '".(int)$txid."'";
		$result = xtc_db_query($query);
		$next_seqnum = 0;
		while($row = xtc_db_fetch_array($result)) {
			$next_seqnum = $row['max_sequence'] + 1;
		}
		return $next_seqnum;
	}

  protected function _getInvoicingTransaction($data) {
    global $order;
    
    $this->amount = 0;
    $this->order = $order;
    $this->invtrans = new Payone_Api_Request_Parameter_Invoicing_Transaction();
    
    $p = 0;
    $products = array();
    for ($i = 0, $n = sizeof($this->order->products); $i < $n; $i ++) {
      foreach ($data['positions'] as $position) {
        if ($this->order->products[$i]['opid'] == $position['pid']) {
          $products[$p] = $this->order->products[$i];
          if ($this->order->products[$i]['qty'] >= $position['qty']) {
            $products[$p]['qty'] = $position['qty'];
          }
          $this->amount += $products[$p]['qty'] * $products[$p]['price'];
          $p ++;
        }
      }
    }
		$this->_getInvoicingTransaction_products($products);
		
		$p = 0;
		$totaldata = array('data' => array());
		for ($i = 0, $n = sizeof($this->order->totals); $i < $n; $i ++) {
      foreach ($data['totals'] as $total) {
        if ($this->order->totals[$i]['class'] == $total['class']) {
          $totaldata['data'][$p] = array('TITLE' => $this->order->totals[$i]['title'],
                                         'TEXT' => $this->order->totals[$i]['text'],
                                         'VALUE' => $this->order->totals[$i]['value'],
                                         'CLASS' => $this->order->totals[$i]['class'],
                                         );
          $this->amount += $totaldata['data'][$p]['VALUE'];
          $p ++;
        }
      }		
		}
		$this->_getInvoicingTransaction_totals($totaldata);
				
		return $this->invtrans;
  }
  
  public function _getInvoicingTransaction_products($products) {
		foreach($products as $product) {
			$item = new Payone_Api_Request_Parameter_Invoicing_Item();
			$item->setIt('goods');
			$item->setId($product['id']);
			$item->setPr(round($product['price'], 2));
			$item->setNo($product['qty']);
			$item->setDe($product['name']);
			$item->setVa(round($product['tax'], 2));
			$this->invtrans->addItem($item);
		}  
  }

  public function _getInvoicingTransaction_totals($totaldata) {
		foreach($totaldata['data'] as $td) {
		  switch ($td['CLASS']) {
		    case 'ot_shipping':
				  $item = new Payone_Api_Request_Parameter_Invoicing_Item();
          $item->setIt('shipment');
          $item->setId('SHIPMENT');
          $item->setPr(round($td['VALUE'], 2));
          $item->setNo(1);
          $item->setDe(strip_tags($td['TITLE']));
          $item->setVa($this->_get_order_total_tax('MODULE_SHIPPING_', $this->order->info['shipping_class']));
          $this->invtrans->addItem($item);
          break;
          
				case 'ot_payment':
				  $item = new Payone_Api_Request_Parameter_Invoicing_Item();
				  if ($td['VALUE'] > 0) {
            $item->setIt('handling');
            $item->setId('HANDLING');
          } else {
            $item->setIt('voucher');
            $item->setId('VCHRDSCNT');
          }
          $item->setPr(round($td['VALUE'], 2));
          $item->setNo(1);
          $item->setDe(strip_tags($td['TITLE']));
          $item->setVa(0);
          $this->invtrans->addItem($item);
          break;
          
        case 'ot_coupon':
        case 'ot_discount':
        case 'ot_gv':
        case 'ot_ps_fee':
        case 'ot_loworderfee':
        case 'ot_cod_fee':
        case 'ot_shippingfee':
				  $item = new Payone_Api_Request_Parameter_Invoicing_Item();
				  if ($td['VALUE'] > 0) {
            $item->setIt('handling');
            $item->setId('HANDLING');
          } else {
            $item->setIt('voucher');
            $item->setId('VCHRDSCNT');
          }
          $item->setPr(round($td['VALUE'], 2));
          $item->setNo(1);
          $item->setDe(strip_tags($td['TITLE']));
          $item->setVa($this->_get_order_total_tax('MODULE_ORDER_TOTAL_', $td['CLASS']));
          $this->invtrans->addItem($item);
          break;
			}
		}    
  }

  protected function _get_order_total_tax($path, $type) {
  
    require_once (DIR_FS_INC.'xtc_get_tax_rate.inc.php');

    if (!isset($this->order->customer['zone_id'])) {
      $customers_info = $this->_get_customers_infos($this->order->customer['id'], $this->order->delivery['country_iso_2']);
    } else {
      $customers_info = array('country_id' => $this->order->customer['country']['id'],
                              'zone_id' => $this->order->customer['zone_id']);
    }
        
    $class = explode('_', $type);
    if (defined(strtoupper($path.$class[0].'_TAX_CLASS'))) {
      return xtc_get_tax_rate(constant(strtoupper('MODULE_SHIPPING_'.$class[0].'_TAX_CLASS')), $customers_info['country_id'], $customers_info['zone_id']);
    } else {
      return '0';
    }
  }

  protected function _get_customers_infos($customers_id, $delivery_country_iso_code_2) {
    $countries_query = xtc_db_query("select c.countries_id
                                      from  " . TABLE_COUNTRIES . " c
                                     where c.countries_iso_code_2  = '" . $delivery_country_iso_code_2 . "'
                                   ");
    $countries = xtc_db_fetch_array($countries_query);

    $zone_id = '';
    if ($countries['countries_id'] > 0) {
      $zones_query = xtc_db_query("select z.zone_id
                                      from " . TABLE_ORDERS . " o,
                                           " . TABLE_ZONES . " z
                                     where o.customers_id  = '" . $customers_id . "'
                                       and z.zone_country_id = '" . $countries['countries_id'] . "'
                                       and z.zone_name = o.delivery_state
                                   ");
      $zones = xtc_db_fetch_array($zones_query);
      $zone_id = $zones['zone_id'];
    }

    $customers_info_array = array('country_id' => $countries['countries_id'],
                                  'zone_id' => $zone_id
                                  );

    return $customers_info_array;
  }

	public function captureAmount($data) {
		$this->log("capturing ".print_r($data, true));

		$config = $this->getConfig();
		$global_config = $config['global'];

		$standard_parameters = $this->getStandardParameters('capture', $global_config);
		unset($standard_parameters['responsetype']);
		unset($standard_parameters['successurl']);
		unset($standard_parameters['errorurl']);
		unset($standard_parameters['hash']);
		
		$request_parameters = array(
			'aid' => $global_config['subaccount_id'],
			'key' => $global_config['key'],
		);
		$params = array_merge($standard_parameters, $request_parameters);

		$builder = new Payone_Builder($this->getPayoneConfig());
		$service = $builder->buildServicePaymentCapture();

		$request = new Payone_Api_Request_Capture($params);
		$request->setTxid($data['txid']);
		$request->setPortalid($data['portalid']);
		$request->setSequencenumber($this->_getNextSequencenumber($data['txid']));
		$request->setCurrency($data['currency']);
    
    if (isset($data['positions'])) {
		  $request->setInvoicing($this->_getInvoicingTransaction($data));
		  $request->setAmount(round($this->amount, 2));
		} else {
		  $request->setAmount(round($data['amount'], 2));
		}
		
		$this->log("capture request:\n".print_r($request, true));
		$response = $service->capture($request);
    
    if ($response instanceof Payone_Api_Response_Capture_Approved) {
      $this->log("SUCCESS capture response:\n".print_r($response, true));
    } else if ($response instanceof Payone_Api_Response_Error) {
      $this->log("ERROR capture response:\n".print_r($response, true));
    }
    		
		return $response;
	}

	public function refundAmount($data) {
		$this->log("refunding amount\n".print_r($data, true));
		
		$config = $this->getConfig();
		$global_config = $config['global'];
		
		$standard_parameters = $this->getStandardParameters('debit', $global_config);
		unset($standard_parameters['responsetype']);
		unset($standard_parameters['successurl']);
		unset($standard_parameters['errorurl']);
		unset($standard_parameters['hash']);
		
		$request_parameters = array(
			'aid' => $global_config['subaccount_id'],
			'key' => $global_config['key'],
		);
		$params = array_merge($standard_parameters, $request_parameters);
		
		$builder = new Payone_Builder($this->getPayoneConfig());
		$service = $builder->buildServicePaymentDebit();
		
		$request = new Payone_Api_Request_Debit($params);
		$request->setCurrency($data['currency']);
		$request->setSequencenumber($this->_getNextSequencenumber($data['txid']));
		$request->setTxid($data['txid']);

    if (isset($data['positions'])) {
		  $request->setInvoicing($this->_getInvoicingTransaction($data));
		  $request->setAmount((round($this->amount, 2) * (-1)));
		} else {
		  $request->setAmount((round($data['amount'], 2) * (-1)));
		}
		
		if (false && !empty($data['bankaccount'])) {
			$payment = new Payone_Api_Request_Parameter_Refund_PaymentMethod_BankAccount();
			$payment->setBankaccount($data['bankaccount']);
			$payment->setBankbranchcode($data['bankbranchcode']);
			$payment->setBankcheckdigit($data['bankcheckdigit']);
			$payment->setBankcode($data['bankcode']);
			$payment->setBankcountry($data['bankcountry']);
			$request->setPayment($payment);
		}
		
		$this->log("debit request:\n".print_r($request, true));
		$response = $service->debit($request);
		
    if ($response instanceof Payone_Api_Response_Debit_Approved) {
      $this->log("SUCCESS refunding response:\n".print_r($response, true));
    } else if ($response instanceof Payone_Api_Response_Error) {
      $this->log("ERROR refunding response:\n".print_r($response, true));
    }
		
		return $response;
	}

	public function getBillToCountry() {
		if (!(isset($_SESSION['billto']) && is_numeric($_SESSION['billto']))) {
			return '';
		}
		$ab_id = $_SESSION['billto'];
		$customer_id = $_SESSION['customer_id'];
		$query = "SELECT ab.*, 
		                 c.* 
		            FROM ".TABLE_ADDRESS_BOOK." ab 
		       left join ".TABLE_COUNTRIES." c 
		                 on c.countries_id = ab.entry_country_id
			         WHERE ab.address_book_id = '".(int)$ab_id."' 
			           AND ab.customers_id = '".(int)$customer_id."'";
		$result = xtc_db_query($query);
		$country = '';
		while($row = xtc_db_fetch_array($result)) {
			$country = $row['countries_iso_code_2'];
		}
		return $country;
	}

	public function getClearingData($orders_id) {
		$result = xtc_db_query("SELECT * FROM `payone_clearingdata` WHERE `orders_id` = '".(int)$orders_id."'");
		$cd = false;
		while($row = xtc_db_fetch_array($result)) {
			$cd = $row;
		}
		return $cd;
	}

	public function addressCheck($ab_id, $checktype = 'BA') {
		$global_config = $this->getConfig('global');
		$config = $this->getConfig('address_check');
		$cdata = $this->_getAddressBookEntry($ab_id);

		if ($cdata === false) {
			throw new Exception('invalid address book entry');
		}

		$standard_parameters = $this->getStandardParameters();
		$builder = new Payone_Builder($this->getPayoneConfig());
		$service = $builder->buildServiceVerificationAddressCheck();
		$requestData = array(
			'key' => $global_config['key'],
			'addresschecktype' => $checktype, // BA|PE|NO (basic | person | no)
		);
		$addressData = array(
			'firstname' => $cdata['entry_firstname'],
			'lastname' => $cdata['entry_lastname'],
			'company' => $cdata['entry_company'],
			'street' => $cdata['entry_street_address'],
			'zip' => $cdata['entry_postcode'],
			'city' => $cdata['entry_city'],
			'country' => $cdata['countries_iso_code_2'],
			'birthday' => date('Ymd', strtotime($cdate['dob_date'])),
			'telephonenumber' => $cdata['customers_telephone'],
		);
		$address_hash = md5(implode('', $addressData));
		$response = $this->_retrieveCachedAddressCheckResponse($address_hash);
		if ($response == false) {
			$this->log("addressCheck cache miss");
			$requestData = array_merge($standard_parameters, $requestData, $addressData);
			$request = new Payone_Api_Request_AddressCheck($requestData);
			$this->log("addressCheck hash: ".$address_hash."\n");
			$this->log("addressCheck request:\n".print_r($request, true));
			$response = $service->check($request);
			$this->log("addressCheck response:\n".print_r($response, true));
		} else {
			$this->log("addressCheck cache hit");
		}
		if ($response instanceof Payone_Api_Response_AddressCheck_Valid || $response instanceof Payone_Api_Response_AddressCheck_Invalid) {
			$this->_storeAddressCheckResponse($response, $ab_id, $address_hash);
		}
		
    return $response;
	}

	protected function _retrieveCachedAddressCheckResponse($address_hash) {
		$config = $this->getConfig('address_check');
		$cache_days = $config['validity'];
		$query = "SELECT * 
		            FROM `payone_ac_cache` 
		           WHERE address_hash = '".xtc_db_input($address_hash)."' 
		             AND received >= DATE_SUB(NOW(), INTERVAL ".(int)$cache_days." DAY)";
		$cached_response = false;
		$result = xtc_db_query($query);
		while($row = xtc_db_fetch_array($result)) {
			if (empty($row['errorcode'])) {
				$cached_response = new Payone_Api_Response_AddressCheck_Valid($row);
			}
			else {
				$cached_response = new Payone_Api_Response_AddressCheck_Invalid($row);
			}
		}
		return $cached_response;
	}

	protected function _storeAddressCheckResponse($response, $ab_id, $address_hash) {
		if ($response instanceof Payone_Api_Response_AddressCheck_Valid) {
			$sql_data_array = array(
				'address_hash' => $address_hash,
				'address_book_id' => (int)$ab_id,
				'secstatus' => (int)$response->getSecstatus(),
				'status' => $response->getStatus(),
				'personstatus' => $response->getPersonstatus(),
				'street' => $response->getStreet(),
				'streetname' => $response->getStreetname(),
				'streetnumber' => $response->getStreetnumber(),
				'zip' => $response->getZip(),
				'city' => $response->getCity(),
			);
		}
		else if ($response instanceof Payone_Api_Response_AddressCheck_Invalid) {
			$sql_data_array = array(
				'address_hash' => $address_hash,
				'address_book_id' => (int)$ab_id,
				'secstatus' => (int)$response->getSecstatus(),
				'status' => $response->getStatus(),
				'errorcode' => $response->getErrorcode(),
				'errormessage' => $response->getErrormessage(),
				'customermessage' => $response->getCustomerMessage(),
			);
		}
		
		xtc_db_query("DELETE FROM `payone_ac_cache` WHERE address_hash = '".xtc_db_input($address_hash)."'");
		xtc_db_perform('payone_ac_cache', $sql_data_array);
	}

	public function scoreCustomer($ab_id) {
		$global_config = $this->getConfig('global');
		$config = $this->getConfig('credit_risk');
		$cdata = $this->_getAddressBookEntry($ab_id);

		if ($cdata === false) {
			throw new Exception('invalid address book entry');
		}

		switch($config['typeofcheck']) {
			case 'iscorehard':
				$scoretype = 'IH';
				break;
			case 'iscoreall':
				$scoretype = 'IA';
				break;
			case 'iscorebscore';
				$scoretype = 'IB';
				break;
			default:
				$scoretype = 'IH';
		}

		$standard_parameters = $this->getStandardParameters();
		$builder = new Payone_Builder($this->getPayoneConfig());
		$service = $builder->buildServiceVerificationConsumerscore();
		$requestData = array(
			'key' => $global_config['key'],
			'addresschecktype' => 'NO', // BA|PE|NO (basic | person | no)
			'consumerscoretype' => $scoretype, // IH|IA|IB (hart | alle | alle+boni)
		);
		$addressData = array(
			'firstname' => $cdata['entry_firstname'],
			'lastname' => $cdata['entry_lastname'],
			'company' => $cdata['entry_company'],
			'street' => $cdata['entry_street_address'],
			'zip' => $cdata['entry_postcode'],
			'city' => $cdata['entry_city'],
			'country' => $cdata['countries_iso_code_2'],
			'birthday' => date('Ymd', strtotime($cdate['dob_date'])),
			'telephonenumber' => $cdata['customers_telephone'],
		);
		$address_hash = md5(implode('', $addressData));
		$response = $this->_retrieveCachedCreditRiskResponse($address_hash, $scoretype);
		if ($response == false) {
			$this->log("creditRisk cache miss");
			$requestData = array_merge($standard_parameters, $requestData, $addressData);
			$request = new Payone_Api_Request_Consumerscore($requestData);
			$this->log("scoreCustomer request:\n".print_r($request, true));
			$response = $service->score($request);
			$this->log("scoreCustomer response:\n".print_r($response, true));
		}
		else {
			$this->log("creditRisk cache hit");
		}
		if ($response instanceof Payone_Api_Response_Consumerscore_Valid || $response instanceof Payone_Api_Response_Consumerscore_Invalid) {
			$this->_storeCreditRiskResponse($response, $ab_id, $address_hash, $scoretype);
			return $response;
		}
		else {
			return false;
		}
	}

	protected function _retrieveCachedCreditRiskResponse($address_hash, $scoretype) {
		$config = $this->getConfig('credit_risk');
		$cache_days = $config['validity'];
		$query = "SELECT * 
		            FROM `payone_cr_cache` 
		           WHERE address_hash = '".xtc_db_input($address_hash)."' 
		             AND `scoretype` = '".xtc_db_input($scoretype)."' 
		             AND `received` >= DATE_SUB(NOW(), INTERVAL ".(int)$cache_days." DAY)";
		$this->log("credit_risk checking cache:\n".$query);
		$cached_response = false;
		$result = xtc_db_query($query);
		while($row = xtc_db_fetch_array($result)) {
			if (empty($row['errorcode'])) {
				$cached_response = new Payone_Api_Response_Consumerscore_Valid($row);
			}
			else {
				$cached_response = new Payone_Api_Response_Consumerscore_Invalid($row);
			}
		}
		return $cached_response;
	}

	protected function _storeCreditRiskResponse($response, $ab_id, $address_hash, $scoretype) {
		if ($response instanceof Payone_Api_Response_Consumerscore_Valid) {
			$sql_data_array = array(
				'address_hash' => $address_hash,
				'address_book_id' => (int)$ab_id,
				'scoretype' => $scoretype,
				'secstatus' => (int)$response->getSecstatus(),
				'status' => $response->getStatus(),
				'score' => $response->getScore(),
				'scorevalue' => $response->getScorevalue(),
				'secscore' => $response->getSecscore(),
				'personstatus' => $response->getPersonstatus(),
				'firstname' => $response->getFirstname(),
				'lastname' => $response->getLastname(),
				'street' => $response->getStreet(),
				'streetname' => $response->getStreetname(),
				'streetnumber' => $response->getStreetnumber(),
				'zip' => $response->getZip(),
				'city' => $response->getCity(),
			);
		}
		else if ($response instanceof Payone_Api_Response_Consumerscore_Invalid) {
			$sql_data_array = array(
				'address_hash' => $address_hash,
				'address_book_id' => (int)$ab_id,
				'scoretype' => $scoretype,
				'secstatus' => (int)$response->getSecstatus(),
				'status' => $response->getStatus(),
				'errorcode' => $response->getErrorcode(),
				'errormessage' => $response->getErrormessage(),
				'customermessage' => $response->getCustomerMessage(),
			);
		}

		xtc_db_query("DELETE FROM `payone_cr_cache` WHERE address_hash = '".xtc_db_input($address_hash)."'");
		xtc_db_perform('payone_cr_cache', $sql_data_array);
	}

	public function getLogsCount($mode, $date_start = null, $date_end = null, $search = null) {
		$table = (($mode == 'api') ? 'payone_api_log' : 'payone_transactions_log');
		$query = "SELECT COUNT(*) AS logs_count
		            FROM ".$table." l
		       LEFT JOIN ".TABLE_ORDERS." o
		                 ON o.customers_id = l.customers_id";
		
		if ($date_start !== null && $date_end !== null) {
			$query .= " WHERE l.date_created BETWEEN '".date('Y-m-d 00:00:00', ($date_start))."' AND '".date('Y-m-d 23:59:59', ($date_end))."'";
		}
		
		if ($search != null) {
		  if (stripos($query, 'WHERE') === false) {
		    $query .= " WHERE ";
		  } else {
		    $query .= " AND ";
		  }
		  $query .= " (l.event_id LIKE '%".xtc_db_input($search)."%' OR o.customers_name LIKE '%".xtc_db_input($search)."%')";
		}
		
		$query .= " GROUP BY event_id";
		
		$result = xtc_db_query($query);
		
		$count = 0;
		while ($row = xtc_db_fetch_array($result)) {
		  $count += 1;
		}

		return $count;
	}

	public function getLogs($mode, $limit, $offset, $date_start = null, $date_end = null, $search = null) {
		$table = (($mode == 'api') ? 'payone_api_log' : 'payone_transactions_log');
		$query = "SELECT l.event_id, 
		                 l.date_created, 
		                 l.customers_id, 
		                 o.customers_name
		            FROM ".$table." l
			     LEFT JOIN ".TABLE_ORDERS." o 
			               ON o.customers_id = l.customers_id ";

		if ($date_start !== null && $date_end !== null) {
			$query .= "WHERE l.date_created BETWEEN '".date('Y-m-d 00:00:00', ($date_start))."' AND '".date('Y-m-d 23:59:59', ($date_end))."' ";
		}

		if ($search != null) {
		  if (stripos($query, 'WHERE') === false) {
		    $query .= " WHERE ";
		  } else {
		    $query .= " AND ";
		  }
		  $query .= " (l.event_id LIKE '%".xtc_db_input($search)."%' OR o.customers_name LIKE '%".xtc_db_input($search)."%')";
		}

		$query .= "GROUP BY l.event_id 
		           ORDER BY l.date_created ASC 
		           LIMIT ".$limit." OFFSET ".$offset;
		
		$result = xtc_db_query($query);
		$logs = array();
		while($row = xtc_db_fetch_array($result)) {
			$logs[] = $row;
		}
		
		return $logs;
	}

	public function getLogData($mode, $event_id) {
		$table = (($mode == 'api') ? 'payone_api_log' : 'payone_transactions_log');
		$query = xtc_db_query("SELECT * FROM ".$table." WHERE event_id = ".(int)$event_id." ORDER BY log_count");
		$data = array();
		while($row = xtc_db_fetch_array($query)) {
			$row['message'] = $this->_splitLogMessage($row['message']);
			$data[] = $row;
		}
		return $data;
	}

	protected function _splitLogMessage($message) {
		$parts = explode('|', $message);
		$message = array();
		foreach($parts as $part) {
			list($name, $value) = explode('=', $part);
			$message[$name] = $value;
		}
		return $message;
	}

  public function build_html($template, $content) {
    $module_smarty = new Smarty();
    $module_smarty->template_dir = DIR_FS_EXTERNAL.'payone/templates/';

    $module_smarty->assign('language', $_SESSION['language']);
    $module_smarty->assign('content_data', $content);

    $module_smarty->caching = 0;
    $module = $module_smarty->fetch($template);
    
    return $module;
  }
}
?>