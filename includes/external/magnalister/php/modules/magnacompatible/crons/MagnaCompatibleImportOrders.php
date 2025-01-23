<?php
/**
 * 888888ba                 dP  .88888.                    dP                
 * 88    `8b                88 d8'   `88                   88                
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b. 
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88 
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88 
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P' 
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * $Id$ osC
 *
 * (c) 2010 - 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleCronBase.php');

abstract class MagnaCompatibleImportOrders extends MagnaCompatibleCronBase {
	protected $hasNext = true;
	protected $offset = array();
	protected $beginImportDate = false;

	/**
	 * @var MagnaDB $db
	 */
	protected $db = null;
	protected $dbCharSet = '';
	
	protected $simplePrice = null;
	
	/* specific to one order only */
	protected $cur = array();
	protected $o = array(); /* the current order */
	protected $p = array(); /* the current product */
	protected $stock_left = 0; /* the stock of current product */
	protected $variation_stock_left = 0; /* the stock of current variation */
	protected $taxValues = array(); /* tax values for the current order */
	protected $mailOrderSummary = array();
	protected $comment = '';
	
	/* specific to all orders */
	protected $syncBatch = array(); /* sync batch for other marketplaces */
	protected $allCurrencies = array(); /* list of different currencies */
	protected $blDeleteCustomerAfterwards = false; /* if true, delete account after order completed (guest accounts) */
	
	/* For acknowledging */
	protected $processedOrders = array ();
	protected $lastOrderDate = false;

	/* multivariations, set to true for modules which support it */
	protected $multivariationsEnabled = false;

	/* gambio properties, set to true if enabled, for modules which support it */
	/* (currently Amazon, eBay, Hood, Hitmeister) */
	protected $gambioPropertiesEnabled = false;

	/* set to true if we find the needed classes and libraries for gambio order confirmation */
	protected $blGambioOrderConfirmation = false;
	
	protected $verbose = false;

    /** @var bool Checking if more orders are ready to import - if set to false it does only one iteration */
    protected $continueMode = true;

	public function __construct($mpID, $marketplace) {
		parent::__construct($mpID, $marketplace);

		$this->initImport();
	}

	/*
	 * Display messages, if called in standalone mode
	 */
	protected function out($str) {
		if (    !defined('MAGNA_CALLBACK_MODE')
		     || MAGNA_CALLBACK_MODE != 'STANDALONE') {
			return;
		}
		echo $str;
		flush();
	}
	
	protected function initImport() {
		#$_GET['MLDEBUG'] = 'true'; #hack
		
		if (isset($_GET['MLDEBUG']) && ($_GET['MLDEBUG'] == 'true')) {
			require_once(DIR_MAGNALISTER_INCLUDES . 'lib/MagnaTestDB.php');
			$this->db = MagnaTestDB::gi();
		} else {
			$this->db = MagnaDB::gi();
		}

		$this->dbCharSet = MagnaDB::gi()->mysqlVariableValue('character_set_client');
		if (('utf8mb3' == $this->dbCharSet) || ('utf8mb4' == $this->dbCharSet)) {
			# means the same for us
			$this->dbCharSet = 'utf8';
		}
		$this->verbose = (
				(MAGNA_CALLBACK_MODE == 'STANDALONE') 
				|| (defined('MAGNALISTER_PLUGIN') && (MAGNALISTER_PLUGIN == true))
			) && (get_class($this->db) == 'MagnaTestDB');
		
		require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');
		$this->simplePrice = new SimplePrice();
		
		if (   ( file_exists(DIR_WS_FUNCTIONS.'password_funcs.php'))
			&& (!function_exists('tep_encrypt_password'))
		) {
			require_once(DIR_WS_FUNCTIONS.'password_funcs.php');
		}

		if (SHOPSYSTEM == 'gambio') {
			$this->requireLibsForGambioOrderConfirmation();
			if (file_exists(DIR_FS_CATALOG . 'gm/inc/set_shipping_status.php')) {
				require_once(DIR_FS_CATALOG . 'gm/inc/set_shipping_status.php');
			}
		}
	}

	private function requireLibsForGambioOrderConfirmation() {
		if(    !class_exists('MainFactory')
		    || !class_exists('RecreateOrder')
		) return;

		// RecreateOrder in older Gambio versions needs these xtc files,
		// but in newer versions they aren't present, so we do require if file exists
		$usedFiles0 = array(
		    DIR_FS_INC     . 'xtc_get_order_data.inc.php',
		    DIR_FS_INC     . 'xtc_get_attributes_model.inc.php',
		    DIR_FS_INC     . 'xtc_not_null.inc.php',
		    DIR_FS_INC     . 'xtc_format_price_order.inc.php',
		);
		foreach($usedFiles0 as $fFile) {
			if(file_exists($fFile)) require_once($fFile);
		}
		unset($fFile);

		// need order.php from the admin area, cos the other doesn't provide the necessary data
		if (false === strpos(DIR_WS_CLASSES, 'admin')) {
			$classesDir = str_replace('includes', 'admin/includes', DIR_WS_CLASSES);
		} else {
			$classesDir = DIR_WS_CLASSES;
		}
		if (false === strpos($classesDir, DIR_FS_CATALOG)) {
			$classesDir = DIR_FS_CATALOG . $classesDir;
		}

		// older xtc functionality
		if ((!function_exists('xtc_date_long')) && file_exists(DIR_FS_INC . 'xtc_date_long.inc.php')) {
			require_once(DIR_FS_INC . 'xtc_date_long.inc.php');
		}
		if ((!function_exists('xtc_address_format')) && file_exists(DIR_FS_INC . 'xtc_address_format.inc.php')) {
			require_once(DIR_FS_INC . 'xtc_address_format.inc.php');
		}
		$usedFiles1 = array (
		    $classesDir    . 'order.php',
		    DIR_FS_CATALOG . 'gm/inc/gm_prepare_number.inc.php',
		    DIR_FS_CATALOG . 'gm/inc/gm_save_order.inc.php',
		);
		foreach($usedFiles1 as $fFile) {
			if(file_exists($fFile)) require_once($fFile);
		}
		unset($fFile);
		$this->blGambioOrderConfirmation = true;
		if ($this->verbose) {
			echo '$this->blGambioOrderConfirmation = true'."\n";
		}
	}
	
	protected function getConfigKeys() {
		return array (
			'KeyType' => array (
				'key' => 'general.keytype',
				'default' => 'artNr',
			),
			'VariationType' => array (
				'key' => 'general.options',
				'default' => 'old',
			),
			'UpdateExchangeRate' => array (
				'key' => array('exchangerate', 'update'),
				'default' => false,
			),
			'LastImport' => array (
				'key' => 'orderimport.lastrun',
				'default' => 0,
			),
			'FirstImportDate' => array (
				'key' => 'preimport.start',
				'default' => '1970-01-01',
			),
			'CustomerGroup' => array (
				'key' => 'CustomerGroup',
				'default' => 1
			),
			'MwStFallback' => array (
				'key' => 'mwst.fallback',
				'default' => 0
			),
			/*//{search: 1427198983}
			'MwStShipping' => array (
				'key' => 'mwst.shipping',
				'default' => 0
			),
			 //*/
			'StockSync.FromMarketplace' => array (
				'key' => 'stocksync.frommarketplace',
				'default' => 'no'
			),
			'MailSend' => array (
				'key' => 'mail.send',
				'default' => 'false',
			),
			'ShippingMethod' => array (
				'key' => 'orderimport.shippingmethod',
				'default' => 'textfield',
			),
			'ShippingMethodName' => array (
				'key' => 'orderimport.shippingmethod.name',
				'default' => 'marketplace',
			),
			'PaymentMethod' => array (
				'key' => 'orderimport.paymentmethod',
				'default' => 'textfield',
			),
			'PaymentMethodName' => array (
				'key' => 'orderimport.paymentmethod.name',
				'default' => 'marketplace',
			),
            'OrderStatusOpen' => array (
                'key' => 'orderstatus.open',
                'default' => '',
            ),
		);
	}
	
	protected function initConfig() {
		$this->config['CIDAssignment'] = getDBConfigValue('customers_cid.assignment', '0', 'none'); // none is default in global config

		parent::initConfig();

		if ($this->config['ShippingMethod'] == 'textfield') {
			$this->config['ShippingMethod'] = trim($this->config['ShippingMethodName']);
		}
		if (empty($this->config['ShippingMethod'])) {
			$k = $this->getConfigKeys();
			$this->config['ShippingMethod'] = $k['ShippingMethodName']['default'];
		}
		if ($this->config['PaymentMethod'] == 'textfield') {
			$this->config['PaymentMethod'] = trim($this->config['PaymentMethodName']);
		}
		if (empty($this->config['PaymentMethod'])) {
			$k = $this->getConfigKeys();
			$this->config['PaymentMethod'] = $k['PaymentMethodName']['default'];
		}
		$this->config['DBColumnExists'] = array (
			'customers.customers_cid' => MagnaDB::gi()->columnExistsInTable('customers_cid', TABLE_CUSTOMERS),
			'customers.customers_status' => MagnaDB::gi()->columnExistsInTable('customers_status', TABLE_CUSTOMERS),
			'orders.customers_cid' => MagnaDB::gi()->columnExistsInTable('customers_cid', TABLE_ORDERS),
			'orders.customers_status' => MagnaDB::gi()->columnExistsInTable('customers_status', TABLE_ORDERS),
			'orders.customers_status_name' => MagnaDB::gi()->columnExistsInTable('customers_status_name', TABLE_ORDERS),
			'orders.customers_status_image' => MagnaDB::gi()->columnExistsInTable('customers_status_image', TABLE_ORDERS),
			'orders.customers_status_discount' => MagnaDB::gi()->columnExistsInTable('customers_status_discount', TABLE_ORDERS),
			'orders.gm_send_order_status' => MagnaDB::gi()->columnExistsInTable('gm_send_order_status', TABLE_ORDERS),
			'orders.orders_hash' => MagnaDB::gi()->columnExistsInTable('orders_hash', TABLE_ORDERS),
		);
		$this->setCustomerGroupProperties($this->config['CustomerGroup']);
		
		foreach (array(
			'products_options_id', 'products_attributes_id', 'products_attributes_model',
			'options_id', 'options_values_id', // gambio
			'attributes_model', 'attributes_ean'// modified 2.0.0
		) as $col) {
			$this->config['DBColumnExists']['orders_products_attributes.'.$col] = defined('TABLE_ORDERS_PRODUCTS_ATTRIBUTES')
				&& MagnaDB::gi()->tableExists(TABLE_ORDERS_PRODUCTS_ATTRIBUTES)
				&& MagnaDB::gi()->columnExistsInTable($col, TABLE_ORDERS_PRODUCTS_ATTRIBUTES);
			
		}
		$this->config['DBColumnExists'][TABLE_PRODUCTS_ATTRIBUTES.'.attributes_stock'] = 
			MagnaDB::gi()->columnExistsInTable('attributes_stock', TABLE_PRODUCTS_ATTRIBUTES);
		
		/*//{search: 1427198983}
		//Bugfix for floats as array keys
		$this->config['MwStShipping'] = (string)round($this->config['MwStShipping'], 2);
		//*/

		// store country
		if (defined('STORE_COUNTRY') && (int)STORE_COUNTRY > 0) {
			$this->config['StoreCountry'] = strtolower(MagnaDB::gi()->fetchOne('
			SELECT countries_iso_code_2
			  FROM '.TABLE_COUNTRIES.'
			 WHERE countries_id = '.STORE_COUNTRY));
		} else {
            if (defined('ML_GAMBIO_41_NEW_CONFIG_TABLE')) {
                $this->config['StoreCountry'] = strtolower(MagnaDB::gi()->fetchOne("
                    SELECT ctr.`countries_iso_code_2`
                      FROM ".TABLE_CONFIGURATION." config, ".TABLE_COUNTRIES." ctr
                     WHERE     config.`key` = 'configuration/STORE_COUNTRY'
                           AND config.`value` = ctr.`countries_id`'
                "));
            } else {
                $this->config['StoreCountry'] = strtolower(MagnaDB::gi()->fetchOne('
                    SELECT ctr.`countries_iso_code_2`
                      FROM '.TABLE_CONFIGURATION.' config, '.TABLE_COUNTRIES.' ctr
                     WHERE     config.`configuration_key` = \'STORE_COUNTRY\'
                           AND config.`configuration_value` = ctr.`countries_id`'
                ));
            }
		}
		$this->config['StoreLanguage'] = getLanguageIsoForCountryIso($this->config['StoreCountry']);

		#echo var_dump_pre($this->config['PaymentMethod'], 'PaymentMethod');
		#echo var_dump_pre($this->config['ShippingMethod'], 'ShippingMethod');
		#echo print_m($this->config);
	}

	private function setCustomerGroupProperties($iCustomerGroup) {
		if (    $this->config['DBColumnExists']['orders.customers_status_name']
		     || $this->config['DBColumnExists']['orders.customers_status_image']
        ) {
			$aResult = MagnaDB::gi()->fetchRow(eecho('
				SELECT IFNULL(customers_status_name, \'\') AS customers_status_name, IFNULL(customers_status_image, \'\') AS customers_status_image
				  FROM '.TABLE_CUSTOMERS_STATUS.' cs, '.TABLE_LANGUAGES.' lng
				 WHERE cs.customers_status_id = '.$iCustomerGroup.'
				   AND cs.language_id = lng.languages_id
				   AND lng.directory = \''.$this->language.'\'
				LIMIT 1', false));

            if (!empty($aResult)) {
                $this->config['CustomerGroupProperties'] = $aResult;
            } else {
                $this->config['CustomerGroupProperties'] = array('customers_status_name' => '', 'customers_status_image' => '');
            }
		}
	}

	/**
	 * How many hours, days, weeks or whatever we go back in time to request older orders?
	 * @return int - time in seconds
	 */ 
	protected function getPastTimeOffset() {
		return 60 * 60 * 24 * 7;
	}

	protected function getBeginDate() {
		global $_modules;
		if ($this->beginImportDate !== false) {
			return $this->beginImportDate;
		}
		$begin = strtotime($this->config['FirstImportDate']);
		if ($begin <= strtotime('1970-01-01 00:00:00')) {
			# not configured. Check if this is a required key for the platform.
			# If so, return false, which stops the import.
			if (in_array($this->marketplace.'.preimport.start', $_modules[$this->marketplace]['requiredConfigKeys'])) {
				return false;
			}
		}
		if ($begin > time()) {
			if ($this->verbose) echo "Date in the future --> no import\n";
			return false;
		}
		
		$dateRegexp = '/^([1-2][0-9]{3})-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])'.
			'(\s([0-1][0-9]|2[0-4]):([0-5][0-9]):([0-5][0-9]))?$/';
		
		$lastImport = $this->config['LastImport'];
		if (preg_match($dateRegexp, $lastImport)) {
			# Since we only request non acknowledged orders, we go back in time by 7 days.
			$lastImport = strtotime($lastImport.' +0000') - $this->getPastTimeOffset();
		} else {
			$lastImport = 0;
		}
	
		if ( ($lastImport > 0) && ($begin < $lastImport) ) {
			$begin = $lastImport;
		}
		
		if (isset($_GET['ForceBeginImportDate']) && preg_match($dateRegexp, $_GET['ForceBeginImportDate'])) {
			$begin = strtotime($_GET['ForceBeginImportDate']);
		}
		
		return $this->beginImportDate = gmdate('Y-m-d H:i:s', $begin);
	}

	protected function buildRequest() {
		/* {Hook} "MagnaCompatibleImportOrders_buildRequest": Modify or replace the GetOrdersForDateRange request to magnalister API.
				Here, you can modify or the request for orders to magnalister API. Useful e.g. if your shop server can't process 200 orders at once (you can reduce the count).
				You can also change the begin date (not recommended, but can be useful e.g. if your shop was offline for more than a week), and then return your changed request.
				Variables that can be used:
				<ul>
				    <li>$this->marketplace: The name of the marketplace.</li>
				    <li>$this->mpID: The ID of the marketplace.</li>
				    <li>$this->offset: array(int 'COUNT', int 'START') for requesting orders.</li>
				</ul>
		*/
		if (function_exists('magnaContribVerify') && (($hp = magnaContribVerify('MagnaCompatibleImportOrders_buildRequest', 1)) !== false)) {
			require($hp);
		}
		if (empty($this->offset)) {
			$this->offset = array (
				'COUNT' => 200,
				'START' => 0,
			);
		}
		return array (
			'ACTION' => 'GetOrdersForDateRange',
			'SUBSYSTEM' => $this->marketplace,
			'MARKETPLACEID' => $this->mpID,
			'BEGIN' => $this->getBeginDate(),
			'OFFSET' => $this->offset,
		);
	}

	protected function getOrders() {
		if ($this->hasNext != true) {
			return false;
		}
		$request = $this->buildRequest();
		if ($this->verbose) {
			echo print_m($request, '$request');
		}
		if ($request['BEGIN'] === false) {
			echo "No BEGIN Date has been set, so no import yet.\n";
			return false;
		}
		try {
			$res = MagnaConnector::gi()->submitRequest($request);
		} catch (MagnaException $e) {
			if ((MAGNA_CALLBACK_MODE == 'STANDALONE') || $this->verbose) {
				echo print_m($e->getErrorArray(), 'Error: '.$e->getMessage());
			}
			if (MAGNA_DEBUG && ($e->getMessage() == ML_INTERNAL_API_TIMEOUT)) {
				$e->setCriticalStatus(false);
			}
			return false;
		}
		if (!array_key_exists('DATA', $res) || empty($res['DATA'])) {
			$this->out($this->marketplace.' ('.$this->mpID.") order import: No data.\n");
			return false;
		}
		$this->hasNext = ($this->continueMode) ? $res['HASNEXT'] : false;
		$this->offset['START'] += $this->offset['COUNT'];
		
		$orders = $res['DATA'];
		$res['DATA'] = 'Cleaned';
		
		if ($this->verbose) echo print_m($res, '$res');

		if (!is_array($orders)) return false;

		# ggf. Zeichensatz korrigieren
		if ($this->dbCharSet != 'utf8') {
			arrayEntitiesToLatin1($orders);
		}

		return $orders;
	}
	
	protected function updateOrderCurrency($currency) {
		// Does the currency exist in the shop?
		if (!$this->simplePrice->currencyExists($currency)) {
			echo "Currency does not exist.\n";
			return false;
		}
		#if ($this->verbose) echo 'Set Currency to: ['.$currency."]\n";
		$this->simplePrice->setCurrency($currency);

		if (array_key_exists($currency, $this->allCurrencies)) {
			return true;
		}

		if ($this->config['UpdateExchangeRate']) {
			$this->simplePrice->updateCurrencyByService();
		}

		$currencyValue = $this->simplePrice->getCurrencyValue();
		if ((float)$currencyValue <= 0.0) {
			if ($this->verbose) echo "CurrencyValue <= 0.\n";
			return false;
		}
		$this->allCurrencies[$currency] = $currencyValue;
		return true;
	}
	
	protected function getCountryByISOCode($code, $fallbackName = '') {
		$c = MagnaDB::gi()->fetchRow('
			SELECT countries_id as ID, countries_name AS Name
			  FROM '.TABLE_COUNTRIES.'
			 WHERE countries_iso_code_2="'.$code.'" 
			 LIMIT 1
		');
		if (!is_array($c)) {
			$c = array (
				'ID' => 0,
				'Name' => empty($fallbackName) ? $code : $fallbackName,
			);
		}
		return $c;
	}

	protected function getAddressFormatID($country) {
		$ret = (int)MagnaDB::gi()->fetchOne(eecho('
			SELECT address_format_id 
			  FROM '.TABLE_COUNTRIES.'
			 WHERE countries_id="'.$country['ID'].'"
		', $this->verbose));
		if ($ret < 1) {
			return 1;
		}
		return $ret;
	}

	protected function getCustomer($email) {
		$fields = array('customers_id as ID');
		if (($this->config['CIDAssignment'] != 'none') &&
			$this->config['DBColumnExists']['customers.customers_cid']
		) {
			$fields[] = 'customers_cid as CID';
		}
		if ($this->config['DBColumnExists']['customers.customers_status']) {
			$fields[] = 'customers_status';
		}
		$c = MagnaDB::gi()->fetchRow('
		    SELECT '.implode(',', $fields).'
		      FROM '.TABLE_CUSTOMERS.' 
		     WHERE customers_email_address="'.$email.'" 
		     LIMIT 1
		');
		if (!is_array($c)) {
			return false;
		}
		if ($this->config['DBColumnExists']['customers.customers_status']) {
			$this->setCustomerGroupProperties($c['customers_status']);
			unset($c['customers_status']); // was needed only here
		}
		$this->blDeleteCustomerAfterwards = false; // old account, don't delete
		return $c;
	}

	protected function insert($table, $data) {
		/* {Hook} "MagnaCompatibleImportOrders_DBInsert": Is called right before data is inserted into the database.
				Variables that can be used:
				<ul><li>$table: The database table where the data will be inserted.</li>
				    <li>$data: An associative array where the keys equal the tables colums.</li>
				    <li>$this->mpID: The ID of the marketplace.</li>
				    <li>$this->marketplace: The name of the marketplace.</li>
				    <li>$this->cur['OrderID']: The Order ID of the shop (<code>orders_id</code>).</li>
				    <li>$this->o['order']['customers_id']: The Customers ID of the shop (<code>customers_id</code>).</li>
				    <li>$this->db: Instance of the magnalister database class. USE THIS for accessing the database during the
				        order import. DO NOT USE the shop functions for database access or MagnaDB::gi()!</li>
				</ul>
		*/
		if (function_exists('magnaContribVerify') && (($hp = magnaContribVerify('MagnaCompatibleImportOrders_DBInsert', 1)) !== false)) {
			require($hp);
		}
		$this->db->insert($table, $data);
	}

	protected function insertCustomer() {
		/* {Hook} "MagnaCompatibleImportOrders_replaceInsertCustomer": Replace the insertCustomer functionality.
				Variables that can be used:
				<ul>
				    <li>$this->mpID: The ID of the marketplace.</li>
				    <li>$this->marketplace: The name of the marketplace.</li>
				    <li>$this->o['customer']: An associative array with the customer's data which come along with the order.</li>
				</ul>
		*/
		if (function_exists('magnaContribVerify') && (($hp = magnaContribVerify('MagnaCompatibleImportOrders_replaceInsertCustomer', 1)) !== false)) {
			require($hp);
		}
		$customer = array();
		$customer['Password'] = randomString(10);
		$this->o['customer']['customers_password'] = md5($customer['Password']);
		
		if (SHOPSYSTEM != 'oscommerce') {
			$this->o['customer']['customers_status'] = $this->config['CustomerGroup'];
			if (defined('DEFAULT_CUSTOMERS_STATUS_ID_GUEST') && $this->config['CustomerGroup'] == DEFAULT_CUSTOMERS_STATUS_ID_GUEST) {
				$this->o['customer']['account_type'] = '1';//guest_account
				$this->blDeleteCustomerAfterwards = true;
			} else {
				$this->o['customer']['account_type'] = '0';
				$this->blDeleteCustomerAfterwards = false;
			}
		} else if (function_exists('tep_encrypt_password')) {
			$this->o['customer']['customers_password'] = tep_encrypt_password($customer['Password']);
		}
        MagnaDB::gi()->validateDataLength($this->o['customer'], TABLE_CUSTOMERS);
		MagnaDB::gi()->addNonNullableEntries($this->o['customer'], TABLE_CUSTOMERS);
		$this->insert(TABLE_CUSTOMERS, $this->o['customer']);
		$cupdate = array();

		# Kunden-ID herausfinden
		$customer['ID'] = $this->db->getLastInsertID();
		# Falls es doch verlorengeht (passiert)
		if (empty($customer['ID'])) {
			$iCustomerId = (int)$this->db->fetchOne("SELECT LAST_INSERT_ID()");
			$sCustomerId = (int)$this->db->fetchOne("
				SELECT customers_id
				  FROM ".TABLE_CUSTOMERS."
				 WHERE customers_email_address = '".MagnaDB::gi()->escape($this->o['customer']['customers_email_address'])."'
				ORDER BY customers_id DESC
				LIMIT 1
			");
			if ($iCustomerId == $sCustomerId) {
				$customer['ID'] = $iCustomerId;
			} elseif (isset($sCustomerId)) {
				$customer['ID'] = $sCustomerId;
			}
		}
		# customers_cid bestimmen
		if ($this->config['DBColumnExists']['customers.customers_cid']) {
			switch ($this->config['CIDAssignment']) {
				case 'sequential': {
					$iLastFromCustomers = (int)MagnaDB::gi()->fetchOne('
					    SELECT MAX(CAST(IFNULL(customers_cid,0) AS SIGNED))
					      FROM '.TABLE_CUSTOMERS
					);
		     			if($this->config['DBColumnExists']['orders.customers_cid']) {
						$iLastFromOrders = (int)MagnaDB::gi()->fetchOne('
					    SELECT MAX(CAST(IFNULL(customers_cid,0) AS SIGNED))
					      FROM '.TABLE_ORDERS
						);
					} else {
						$iLastFromOrders = 0;
					}
					$customer['CID'] = (string)(max($iLastFromCustomers, $iLastFromOrders) + 1);
					break;
				}
				case 'customers_id': {
					$customer['CID'] = $customer['ID'];
					break;
				}
			}
			if (isset($customer['CID'])) {
				$cupdate['customers_cid'] = $customer['CID'];
			}
		}
		
		# Infodatensatz erzeugen
		$this->insert(TABLE_CUSTOMERS_INFO, array(
			'customers_info_id' => $customer['ID'],
			'customers_info_number_of_logons' => 0,
			'customers_info_date_account_created' => date('Y-m-d H:i:s', strtotime($this->o['order']['date_purchased']) - 1),
			'customers_info_date_account_last_modified' => date('Y-m-d H:i:s'),
		));
		// echo 'DELETE FROM '.TABLE_CUSTOMERS_INFO.' WHERE customers_info_id="'.$customersId.'";'."\n\n";

		# Adressbuchdatensatz ergaenzen.
		$this->o['adress']['customers_id'] = $customer['ID'];
		$this->o['adress']['entry_country_id'] = $this->cur['BuyerCountry']['ID'];

        MagnaDB::gi()->validateDataLength($this->o['adress'], TABLE_ADDRESS_BOOK);
		MagnaDB::gi()->addNonNullableEntries($this->o['adress'], TABLE_ADDRESS_BOOK);
		$this->insert(TABLE_ADDRESS_BOOK, $this->o['adress']);

		# Adressbuchdatensatz-Id herausfinden.
		$abId = $this->db->getLastInsertID();
		# Falls es doch verlorengeht (passiert)
		if (!isset($abId)) {
			$iAbId = (int)$this->db->fetchOne("SELECT LAST_INSERT_ID()");
			$sAbId = (int)$this->db->fetchOne("
				SELECT address_book_id
				  FROM ".TABLE_ADDRESS_BOOK."
				 WHERE customers_id = ".$customer['ID']."
				ORDER BY address_book_id DESC
				LIMIT 1
			");
			if ($iAbId == $sAbId) {
				$abId = $iAbId;
			} elseif (isset($sAbId)) {
				$abId = $sAbId;
			}
		}
		// echo 'DELETE FROM '.TABLE_ADDRESS_BOOK.' WHERE customers_id="'.$customersId.'";'."\n\n";

		# Kundendatensatz updaten.
		$cupdate['customers_default_address_id'] = $abId;
		$this->db->update(TABLE_CUSTOMERS, $cupdate, array (
			'customers_id' => $customer['ID']
		));

		return $customer;
	}

	protected function updateCustomer() {
		$customer = $this->o['customer'];
		unset($customer['customers_date_added']);
		unset($customer['account_type']);
		$this->db->update(TABLE_CUSTOMERS, $customer, array (
			'customers_id' => $this->cur['customer']['ID'],
		));
		
		# Adressbuchdatensatz aktualisieren.
		if (isset($this->o['adress']['address_date_added'])) {
			unset($this->o['adress']['address_date_added']);
		}
		$this->o['adress']['entry_country_id'] = $this->cur['BuyerCountry']['ID'];

        MagnaDB::gi()->validateDataLength($this->o['adress'], TABLE_ADDRESS_BOOK);
		$this->db->update(TABLE_ADDRESS_BOOK, $this->o['adress'], array (
			'customers_id' => $this->cur['customer']['ID'],
		));
	}

	protected function processCustomer() {
		$customer = $this->getCustomer($this->o['customer']['customers_email_address']);
		if (!is_array($customer)) {
			$this->cur['customer'] = $this->insertCustomer($this->o);
			return;
		}
		$this->cur['customer'] = $customer;
		$this->updateCustomer();
		switch ($this->o['order']['billing_country_iso_code_2']) {
			case 'AT':
			case 'DE': {
				$this->cur['customer']['Password'] = '(wie bekannt)';
				break;
			}
			default: {
				$this->cur['customer']['Password'] = '(as known)';
				break;
			}
		}
	}

	protected function deleteGuestCustomer() {
		if (!$this->blDeleteCustomerAfterwards) return;
		MagnaDB::gi()->delete(TABLE_CUSTOMERS, array ('customers_id' => $this->cur['customer']['ID']));
		MagnaDB::gi()->delete(TABLE_CUSTOMERS_INFO, array ('customers_info_id' => $this->cur['customer']['ID']));
		MagnaDB::gi()->delete(TABLE_ADDRESS_BOOK, array ('customers_id' => $this->cur['customer']['ID']));
		if (SHOPSYSTEM == 'gambio') {
			MagnaDB::gi()->update(TABLE_ORDERS, array('customers_id' => 0), array ('orders_id' => $this->cur['OrderID']));
		}
	}
	
	/**
	 * Load some basic info, e.g. country etc from DB
	 */
	protected function prepareOrderInfo() {
		$this->cur['BuyerCountry'] = $this->getCountryByISOCode(
			$this->o['orderInfo']['BuyerCountryISO'],
			isset($this->o['order']['customers_country'])
				? $this->o['order']['customers_country']
				: false
		);
		$this->cur['ShippingCountry'] = $this->getCountryByISOCode(
			$this->o['orderInfo']['ShippingCountryISO'],
			isset($this->o['order']['delivery_country'])
				? $this->o['order']['delivery_country']
				: false
		);
	}
	
	/**
	 * Returns the marketplace specific order ID from $this->o.
	 *
	 * @return string
	 *    OrderID of the marketplace used in magnalister_orders.special (Database)
	 */
	protected function getMarketplaceOrderID() {
		return $this->o['orderInfo']['MOrderID'];
	}

	protected function addCurrentOrderToProcessed() {
		$this->processedOrders[] = array (
			'MOrderID' => $this->getMarketplaceOrderID(),
			'ShopOrderID' => $this->cur['OrderID'],
		);
		$this->out($this->marketplace.' ('.$this->mpID.') order '.$this->getMarketplaceOrderID().' imported with No. '.$this->cur['OrderID']."\n");
	}

	protected function orderExists() {
		$mOID = $this->getMarketplaceOrderID();
		$oID = MagnaDB::gi()->fetchOne(eecho('
			SELECT orders_id
			  FROM '.TABLE_MAGNA_ORDERS.'
			 WHERE mpID = '.$this->mpID.'
			   AND special="'.MagnaDB::gi()->escape($mOID).'"
			 LIMIT 1
		', false));
		if ($oID === false) {
			return false;
		}
		echo 'orderExists(MOrderID: '.$mOID.', OrderID: '.$oID.')'."\n";
		$this->cur['OrderID'] = $oID;
		
		/* Ack again */
		$this->addCurrentOrderToProcessed();
		return true;
	}
	
	/**
	 * Returns the status that the order should have as string.
	 * Use $this->o['order'].
	 *
	 * @return String	The order status for the currently processed order.
	 */
	protected abstract function getOrdersStatus();
	
	/**
	 * Returns the comment for orders.comment (Database). 
	 * E.g. the comment from the customer or magnalister related information.
	 * Use $this->o['order'].
	 *
	 * @return String
	 *    The comment for the order.
	 */
	protected function generateOrderComment($blForce = false) {
		if (!$blForce && !getDBConfigValue(array('general.order.information', 'val'), 0, true)) {
			return ''; 
		}
		return trim(
			sprintf(ML_GENERIC_AUTOMATIC_ORDER_MP_SHORT, $this->marketplaceTitle)."\n".
			ML_LABEL_MARKETPLACE_ORDER_ID.': '.$this->getMarketplaceOrderID()."\n\n".
			$this->comment
		);
	}
	
	/**
	 * Returns the comment for orders_status.comment (Database). 
	 * E.g. the comment from the customer or magnalister related information.
	 * May differ from self::generateOrderComment()
	 * Use $this->o['order'].
	 *
	 * @return String
	 *    The comment for the order.
	 */
	protected function generateOrdersStatusComment() {
		return $this->generateOrderComment(true);
	}
	
	/**
	 * In child classes this method can be used to extend the data for the DB-table
	 * orders before it is inserted.
	 * Use $this->o['order'].
	 */
	protected function doBeforeInsertOrder() {
		/* Do nothing here. */
	}

	protected function insertBankData() {
		# Bankdaten hinterlegen
		if (empty($this->o['bank'])) {
			return;
		}
		if (MagnaDB::gi()->tableExists('banktransfer')) {
			$this->o['bank']['orders_id'] = $this->cur['OrderID'];
			$this->insert('banktransfer', $this->o['bank']);
			//echo 'DELETE FROM '.'banktransfer'.' WHERE orders_id="'.$ordersId.'";'."\n\n";
			
		} else {
			$this->o['magnaOrders']['ML_LABEL_ACCOUNTING_OWNER']  = $this->o['bank']['banktransfer_owner'];
			$this->o['magnaOrders']['ML_LABEL_ACCOUNTING_NUMBER'] = $this->o['bank']['banktransfer_number'];
			$this->o['magnaOrders']['ML_LABEL_ACCOUNTING_BLZ']    = $this->o['bank']['banktransfer_blz'];
			$this->o['magnaOrders']['ML_LABEL_ACCOUNTING_NAME']   = $this->o['bank']['banktransfer_bankname'];
		}
		
	}

	/**
	 * In child classes this method can be used to extend the data for the DB-table
	 * magnalister_orders before it is inserted.
	 *
	 * @return array
	 *     Associative array that will be stored serialized
	 *     in magnalister_orders.internaldata (Database)
	 */
	protected function doBeforeInsertMagnaOrder() {
		/* Do nothing here. */
		return array();
	}

	/**
	 * In child classes this method can be used to extend the data for the DB-table
	 * orders_history before it is inserted.
	 * Use $this->o['orderStatus']
	 */
	protected function doBeforeInsertOrderHistory() {
		/* Do nothing here. */
	}
	
	/**
	 * Returns the payment method for the current order.
	 * @return string
	 */
	protected function getPaymentMethod() {
		return $this->config['PaymentMethod'];
	}
	
	/**
	 * Returns the shipping method for the current order.
	 * @return string
	 */
	protected function getShippingMethod() {
		return $this->config['ShippingMethod'];
	}
	
	protected function insertOrder() {
		$this->comment = $this->o['order']['comments'];
		$this->o['order']['customers_id'] = $this->cur['customer']['ID'];

		$this->o['order']['customers_address_format_id'] = 
				$this->o['order']['billing_address_format_id'] = 
				$this->getAddressFormatID($this->cur['BuyerCountry']);
		$this->o['order']['delivery_address_format_id'] = 
				$this->getAddressFormatID($this->cur['ShippingCountry']);

		$this->o['order']['orders_status'] = $this->getOrdersStatus();

		$this->o['order']['customers_country'] = $this->cur['BuyerCountry']['Name'];
		$this->o['order']['delivery_country'] = $this->cur['ShippingCountry']['Name'];
		$this->o['order']['billing_country'] = $this->cur['BuyerCountry']['Name'];

		if (SHOPSYSTEM != 'oscommerce') {
			if (isset($this->cur['customer']['CID'])) {
				$this->o['order']['customers_cid'] = $this->cur['customer']['CID'];
			}
			$this->o['order']['customers_status'] = $this->config['CustomerGroup'];
			$this->o['order']['language'] = $this->language;
			$this->o['order']['comments'] = $this->generateOrderComment();
		}
		
		if ($this->config['DBColumnExists']['orders.customers_status_name']) {
			$this->o['order']['customers_status_name'] = $this->config['CustomerGroupProperties']['customers_status_name'];
		}
		if ($this->config['DBColumnExists']['orders.customers_status_image']) {
			$this->o['order']['customers_status_image'] = $this->config['CustomerGroupProperties']['customers_status_image'];
		}
		if ($this->config['DBColumnExists']['orders.gm_send_order_status']) {
			$this->o['order']['gm_send_order_status'] = 1;
		}
		if ($this->config['DBColumnExists']['orders.customers_status_discount']) {
			$this->o['order']['customers_status_discount'] = '0.0';
		}
		if ($this->config['DBColumnExists']['orders.orders_hash']) {
			$this->o['order']['orders_hash'] = md5(strtotime($this->o['order']['date_purchased']) + mt_rand());
		}
		
		/* Change Shipping and Payment Methods */
		$this->o['order']['payment_method'] = $this->getPaymentMethod();
		if (SHOPSYSTEM != 'oscommerce') {
			$this->o['order']['payment_class'] = $this->o['order']['payment_method'];
			$this->o['order']['shipping_class'] = $this->o['order']['shipping_method'] = $this->getShippingMethod();
		}
		// set currency_value
		$this->o['order']['currency_value'] = $this->allCurrencies[$this->o['order']['currency']];


		$this->doInsertOrder();
		# Statuseintrag fuer Historie vornehmen.
		$this->o['orderStatus']['orders_id'] = $this->cur['OrderID'];
		$this->o['orderStatus']['orders_status_id'] = $this->o['order']['orders_status'];
		
		$this->o['orderStatus']['comments'] = $this->generateOrdersStatusComment();

		$this->doBeforeInsertOrderHistory();
		$this->insert(TABLE_ORDERS_STATUS_HISTORY, $this->o['orderStatus']);
		// echo 'DELETE FROM '.TABLE_ORDERS_STATUS_HISTORY.' WHERE orders_id="'.$this->cur['OrderID'].'";'."\n\n";

		/* {Hook} "MagnaCompatibleImportOrders_PostInsertOrder": Is called after the order in <code>$this->o['order']</code> is imported.
				Usefull to manipulate some of the data in the database
				Variables that can be used:
				<ul><li>$this->o['order']: The order that is going to be imported. The order is an 
				        associative array representing the structures of the order and customer related shop tables.</li>
				    <li>$this->mpID: The ID of the marketplace.</li>
					<li>$this->marketplace: The name of the marketplace.</li>
				    <li>$this->cur['OrderID']: The Order ID of the shop (<code>orders_id</code>).</li>
				    <li>$this->o['order']['customers_id']: The Customers ID of the shop (<code>customers_id</code>).</li>
				    <li>$this->db: Instance of the magnalister database class. USE THIS for accessing the database during the
				        order import. DO NOT USE the shop functions for database access or MagnaDB::gi()!</li>
				</ul>
		*/
		if (function_exists('magnaContribVerify') && (($hp = magnaContribVerify('MagnaCompatibleImportOrders_PostInsertOrder', 1)) !== false)) {
			require($hp);
		}
	}

	protected function doInsertOrder() {
		$this->doBeforeInsertOrder();
		//if ($this->verbose) {
		//	echo print_m($this->o['order'], 'InsertOrder');
		//}
		// for modified: gender (set to 'm', we don't get the info from MPs), customers_country_iso_code_2, customers_ip, languages_id, campaign, ibn_billdate
		$this->o['order']['customers_gender'] = 'm';
		$this->o['order']['customers_country_iso_code_2'] = $this->o['order']['billing_country_iso_code_2'];
		$this->o['order']['billing_gender'] = 'm';
		$this->o['order']['delivery_gender'] = 'm';
		$this->o['order']['customers_ip'] = '0.0.0.0';
		$this->o['order']['languages_id'] = MagnaDB::gi()->fetchOne("
			SELECT languages_id FROM ".TABLE_LANGUAGES." WHERE directory = '".$this->language."'
                ");
		$this->o['order']['campaign'] = '';
		$this->o['order']['ibn_billdate'] = '0000-00-00';
        MagnaDB::gi()->validateDataLength($this->o['order'], TABLE_ORDERS);
        MagnaDB::gi()->addNonNullableEntries($this->o['order'], TABLE_ORDERS);
		$this->insert(TABLE_ORDERS, array_filter_keys($this->o['order'], MagnaDB::gi()->getTableColumns(TABLE_ORDERS)));

		# OrderId merken
		$this->cur['OrderID'] = $this->db->getLastInsertID();
		// echo 'DELETE FROM '.TABLE_ORDERS.' WHERE orders_id="'.$this->cur['OrderID'].'";'."\n\n";
		# Falls es doch verlorengeht (passiert)
		if (empty($this->cur['OrderID'])) {
			$iInsertId = (int)$this->db->fetchOne("SELECT LAST_INSERT_ID()");
			$sOrderId = (int)$this->db->fetchOne("
				SELECT orders_id
				  FROM ".TABLE_ORDERS."
				 WHERE customers_email_address = '".MagnaDB::gi()->escape($this->o['order']['customers_email_address'])."'
				ORDER BY orders_id DESC
				LIMIT 1
			");
			if ($iInsertId == $sOrderId) {
				$this->cur['OrderID'] = $iInsertId;
			} elseif (isset($sOrderId)) {
				$this->cur['OrderID'] = $sOrderId;
			}
		}

		$this->insertBankData();

		/* Bestellung in unserer Tabelle registrieren */
		$internalData = $this->doBeforeInsertMagnaOrder();
		$this->insert(TABLE_MAGNA_ORDERS, array(
			'mpID' => $this->mpID,
			'orders_id' => $this->cur['OrderID'],
			'orders_status' => $this->o['order']['orders_status'],
			'data' => serialize($this->o['magnaOrders']),
			'internaldata' => is_array($internalData) ? serialize($internalData) : '',
			'special' => $this->getMarketplaceOrderID(),
			'platform' => $this->marketplace
		));
		// echo 'DELETE FROM '.TABLE_MAGNA_ORDERS.' WHERE orders_id="'.$this->cur['OrderID'].'";'."\n\n";

	}

	/**
	 * May be overwritten to allow additional identification of the product based on EAN or title.
	 * @todo: Replace products_ean in the query with the constant.
	 */
	protected function additionalProductsIdentification() {

	}
	
	/**
	 * Converts whatever the API has submitted in $this->p['products_tax'] to a
	 * real tax value.
	 * Here it just returns the parameter. Child Clases however may override this
	 * mehtod and convert the parameter.
	 *
	 * @parameter mixed $tax
	 *    Something that represents a tax value
	 * @return float
	 *    The actual tax value
	 */
	protected function getTaxValue($tax) {
		return $tax;
	}
	
	protected function doBeforeInsertOrdersProducts() {}
	
	/**
	 * Returns true if the stock of the imported and identified item has to be reduced.
	 * @return bool
	 */
	protected function hasReduceStock() {
		return $this->config['StockSync.FromMarketplace'] != 'no';
	}

	protected function insertProduct() {
		$this->p['orders_id'] = $this->cur['OrderID'];

		if (isset($this->p['products_model']) && !empty($this->p['products_model'])) {
			$sSKU = $this->p['products_model'];
		} else {
			$sSKU = $this->p['products_id'];
		}

		$aOptions = magnaSKU2ProductOptions($sSKU, magnaGetLanguageIdByCountryIso($this->o['orderInfo']['BuyerCountryISO']), $this->multivariationsEnabled);
		$this->p['products_id'] = magnaSKU2pID($sSKU);
		// case: html coded Umlaut in SKU on marketplace, not coded in the shop DB
		if (    empty($aOptions)
		     && (0 == $this->p['products_id'])
		     && strpos($sSKU, '&')
		     && strpos($sSKU, 'uml;')
		) {
			$aOptions = magnaSKU2ProductOptions(html_entity_decode($sSKU, ENT_NOQUOTES), magnaGetLanguageIdByCountryIso($this->o['orderInfo']['BuyerCountryISO']), $this->multivariationsEnabled);
			$this->p['products_id'] = magnaSKU2pID(html_entity_decode($sSKU, ENT_NOQUOTES));
		}
		$this->additionalProductsIdentification();

		// mailOrderSummary
		$this->mailOrderSummary[] = array(
			'quantity' => $this->p['products_quantity'],
			'name' => $this->p['products_name'],
			'price' => $this->simplePrice->setPrice($this->p['products_price'])->format(),
			'finalprice' => $this->simplePrice->setPrice($this->p['final_price'])->format(),
		);

		// SyncBatch
		if (array_key_exists($this->p['products_id'], $this->syncBatch)) {
			$this->syncBatch[$this->p['products_id']]['NewQuantity']['Value'] += (int)$this->p['products_quantity'];
		} else {
			$this->syncBatch[$this->p['products_id']] = array(
				'SKU' => ('artNr' == $this->config['KeyType'])
					? $sSKU
					: magnaPID2SKU($this->p['products_id']), /* add ML */
				'NewQuantity' => array(
					'Mode' => 'SUB',
					'Value' => (int)$this->p['products_quantity']
				),
			);
		}
		
		$reduceStock = $this->hasReduceStock();
		
		// Product Main
		if (!MagnaDB::gi()->recordExists(TABLE_PRODUCTS, array('products_id' => (int)$this->p['products_id']))) {
			$this->p['products_id'] = 0;
		} else {
            // Gambio specific check - will return (int)0 when product has no gambio properties
            $gambioPropertiesQuantityOption = $this->getGambioPropertiesQuantityOption($aOptions);

			// set the products_model
			$this->setProductsModel();

			if ($reduceStock) {
				// reduces main product stock (or magnalister_variations, if enabled)
				$this->reduceStockBySKU($sSKU, in_array($gambioPropertiesQuantityOption, array(0, 1)));
			}
			// set products.products_ordered for statistics
			$this->increaseOrdered();
		}

		// set the products_tax
		$this->setProductsTax();

		// additional columns for modified (after tax, need the tax value)
		if ('xtcmodified' == SHOPSYSTEM) {
			$this->setAdditionalOrdersProductsColumnsForModified();
		}

		$this->doBeforeInsertOrdersProducts();
		$this->o['_processingData']['ProductsCount'] += (int)$this->p['products_quantity'];

		// insert product
		MagnaDB::gi()->addNonNullableEntries($this->p, TABLE_ORDERS_PRODUCTS);
		$this->insert(TABLE_ORDERS_PRODUCTS, $this->p);
		$iOrdersProductsId = $this->db->getLastInsertID();

		// iterate the Options
		foreach($aOptions as $aOption) {
			if (!$this->gambioPropertiesEnabled) {
				// attribute system
				if ($reduceStock) {
					$this->reduceAttributeStockByOption($aOption);
				}
				$this->insertProductAttribute($iOrdersProductsId, $aOption, $sSKU);
			} else {
				// Gambio properties - check for properties quantity option
				if ($reduceStock && in_array($gambioPropertiesQuantityOption, array(0, 2))) {
					$this->reducePropertyStockByOptionsID($aOption['id']);
				}
				$this->setProductsModelForGambioProperties($iOrdersProductsId, $aOption['id']);
				$this->insertProductProperty($iOrdersProductsId, $aOption['id']);
				break; // only iterate once because we only want to reduce the property once
			}
		}

		// if products quantity = 0 after order, set product inactive if configured so
		if (    ($reduceStock)
		     && ($this->stock_left == 0)
		     && (    (defined('GM_SET_OUT_OF_STOCK_PRODUCTS') && (GM_SET_OUT_OF_STOCK_PRODUCTS == 'true')) // gambio
		          || (defined('STOCK_CHECKOUT_UPDATE_PRODUCTS_STATUS') && (STOCK_CHECKOUT_UPDATE_PRODUCTS_STATUS == 'true')) // modified
		          || (defined('STOCK_ALLOW_CHECKOUT_DEACTIVATE') && (STOCK_ALLOW_CHECKOUT_DEACTIVATE == 'true')) // commerceSEO
		          || (    !defined('GM_SET_OUT_OF_STOCK_PRODUCTS')
                               && !defined('STOCK_CHECKOUT_UPDATE_PRODUCTS_STATUS')
                               && !defined('STOCK_ALLOW_CHECKOUT_DEACTIVATE')
                               && defined('STOCK_ALLOW_CHECKOUT')
                               && (STOCK_ALLOW_CHECKOUT == false)
		             ) // xtc 3
		        )) {
			$this->db->update(TABLE_PRODUCTS,	
				array(
					'products_status' => '0'
				),
				array(
					'products_id' => (int)$this->p['products_id']
				)
			);
		}
		if (    ($reduceStock)
		     && (SHOPSYSTEM == 'gambio')) {
			$this->sendGambioOutOfStockNotification();
			if (function_exists('set_shipping_status')) {
			// set shipping status the same way as in system/classes/checkout/CheckoutProcessProcess.inc.php
			// (otherwise we'd need to use ProductWriteService for simple items
			// and DB queries for variations)
				$aOption = current($aOptions);
				$iOptionID = (    $this->gambioPropertiesEnabled
				               && is_array($aOption)
				               && array_key_exists('id', $aOption)
				               && $aOption['id'] != 0)
					? $aOption['id']
					: false;
				set_shipping_status($this->p['products_id'], $iOptionID);
			}
		}
	}

	/*
	 * sets the products model (from master product)
	 */
	protected function setProductsModel() {
		// fetch product model (needed if customer use id)
		$sProductsModel = MagnaDB::gi()->fetchOne("
			SELECT products_model
			  FROM ".TABLE_PRODUCTS."
			 WHERE products_id = '".(int)$this->p['products_id']."'
		");
		if ($sProductsModel !== false) {
			$this->p['products_model'] = $sProductsModel;
		}
	}

	/*
	 * sets additional columns for modified (from master product)
	 */
	protected function setAdditionalOrdersProductsColumnsForModified() {
		$aOrdersProductsColumns = MagnaDB::gi()->getTableColumns(TABLE_ORDERS_PRODUCTS);
		/*
		products_price_origin is price without tax
		products_weight is taken from products table
		*/
		if (in_array('products_price_origin', $aOrdersProductsColumns)) {
			$this->p['products_price_origin'] = (100.00 * $this->p['products_price']) / (100.00 + $this->p['products_tax']);
		}
		if (in_array('products_weight', $aOrdersProductsColumns)) {
			if ((int)$this->p['products_id'] != 0) {
				$this->p['products_weight'] = MagnaDB::gi()->fetchOne('
					SELECT products_weight
					  FROM '.TABLE_PRODUCTS.'
					WHERE products_id = '.(int)$this->p['products_id']);
			} else {
				$this->p['products_weight'] = 0.00;
			}
		}
	}

	/*
	 * sets the products model and properties_combi_model
	 */
	protected function setProductsModelForGambioProperties($iOrdersProductsId, $iOptionsId) {
		$sCombModel = MagnaDB::gi()->fetchOne("
			SELECT combi_model
			  FROM products_properties_combis
			 WHERE products_properties_combis_id = '".$iOptionsId."'
		");

		$this->p['products_model'] = $this->p['products_model'].'-'.$sCombModel;
		if (defined('APPEND_PROPERTIES_MODEL') && APPEND_PROPERTIES_MODEL != "true") {
			$this->p['products_model'] = $sCombModel;
		}
		$this->p['properties_combi_model'] = $sCombModel;

		$this->db->update(TABLE_ORDERS_PRODUCTS,
			array(
				'products_model' => $this->p['products_model'],
				'properties_combi_model' => $this->p['properties_combi_model'],
			),
			array(
				'orders_products_id' => (int)$iOrdersProductsId,
				'products_id' => (int)$this->p['products_id'],
			)
		);
	}

	/*
	 * reduces the main product stock or magnalister variants stock
	 */
	protected function reduceStockBySKU($sSKU, $reduceMainStock = true) {
		$this->stock_left = 1;
		if ($this->multivariationsEnabled) {
			$this->db->query("
				UPDATE ".TABLE_MAGNA_VARIATIONS."
				   SET variation_quantity = variation_quantity - ".(int)$this->p['products_quantity']."
				 WHERE     products_id = '".(int)$this->p['products_id']."'
				 AND ".mlGetVariationSkuField()." = '".MagnaDB::gi()->escape($sSKU)."'
			");
			if ($this->db->affectedRows() > 0) {
				$this->variation_stock_left = MagnaDB::gi()->fetchOne("
					SELECT variation_quantity FROM ".TABLE_MAGNA_VARIATIONS."
					 WHERE     products_id = '".(int)$this->p['products_id']."'
					 AND ".mlGetVariationSkuField()." = '".MagnaDB::gi()->escape($sSKU)."'
				");
				$this->stock_left = MagnaDB::gi()->fetchOne("
					SELECT SUM(variation_quantity) FROM ".TABLE_MAGNA_VARIATIONS."
					 WHERE     products_id = '".(int)$this->p['products_id']."'
				");
			}
		}

        if ($reduceMainStock) {
            // not a variation => reduce main
            $this->db->query("
                UPDATE ".TABLE_PRODUCTS."
                   SET products_quantity = products_quantity - ".(int)$this->p['products_quantity']."
                 WHERE products_id = '".(int)$this->p['products_id']."'
            ");
            $this->stock_left = MagnaDB::gi()->fetchOne("
                SELECT products_quantity FROM ".TABLE_PRODUCTS."
                 WHERE products_id = '".(int)$this->p['products_id']."'
            ");
            }
	}

	/*
	 * adds the ordered count to products.products_ordered for statistics
	 */
	protected function increaseOrdered() {
		$this->db->query("
			UPDATE ".TABLE_PRODUCTS."
			   SET products_ordered = products_ordered + ".(int)$this->p['products_quantity']."
			 WHERE products_id = '".(int)$this->p['products_id']."'
		");
	}

	/*
	 * reduces an attribute stock if attributes are used
	 */
	protected function reduceAttributeStockByOption($aOption) {
		if (   !$this->gambioPropertiesEnabled
			&& ($this->config['DBColumnExists'][TABLE_PRODUCTS_ATTRIBUTES.'.attributes_stock'])
			&& (((int)$aOption['options_id'] > 0) && ((int)$aOption['options_values_id'] > 0))
		) {
			$this->stock_left = 1;
			$this->db->query("
				UPDATE ".TABLE_PRODUCTS_ATTRIBUTES."
				   SET attributes_stock = attributes_stock - ".(int)$this->p['products_quantity']."
				 WHERE     products_id = '".(int)$this->p['products_id']."'
				       AND options_id = '".(int)$aOption['options_id']."'
				       AND options_values_id = '".(int)$aOption['options_values_id']."'
			");
			if ($this->db->affectedRows() > 0) {
				$this->variation_stock_left = MagnaDB::gi()->fetchOne("
					SELECT attributes_stock FROM ".TABLE_PRODUCTS_ATTRIBUTES."
					 WHERE     products_id = '".(int)$this->p['products_id']."'
					       AND options_id = '".(int)$aOption['options_id']."'
					       AND options_values_id = '".(int)$aOption['options_values_id']."'
				");
				$this->stock_left = MagnaDB::gi()->fetchOne("
					SELECT SUM(attributes_stock) FROM ".TABLE_PRODUCTS_ATTRIBUTES."
					 WHERE     products_id = '".(int)$this->p['products_id']."'
				");
			}

		}
	}

	/*
	 * reduces an property stock if gambio properties are used
	 */
	protected function reducePropertyStockByOptionsID($iOptionsId) {
		if ($this->gambioPropertiesEnabled) {
			$this->stock_left = 1;
			$this->db->query(eecho("
				UPDATE products_properties_combis
				   SET combi_quantity = combi_quantity - ".(int)$this->p['products_quantity']."
				 WHERE products_properties_combis_id = '".$iOptionsId."'
			", false));
			if ($this->db->affectedRows() > 0) {
				$this->variation_stock_left = MagnaDB::gi()->fetchOne("
					SELECT combi_quantity FROM products_properties_combis
					 WHERE products_properties_combis_id = '".$iOptionsId."'
				");
				$pID = (int)MagnaDB::gi()->fetchOne("SELECT products_id FROM products_properties_combis
					 WHERE products_properties_combis_id = '".$iOptionsId."'
				");
				$this->stock_left = MagnaDB::gi()->fetchOne("
					SELECT SUM(combi_quantity) FROM products_properties_combis
					 WHERE products_id = ".$pID
				);
			}
		}
	}

	/*
	 * sets the tax
	 * 	special for oscommerce we update products_price and final_price
	 */
	protected function setProductsTax() {
		/* {Hook} "MagnaCompatibleImportOrders_setProductsTax": Is called before the product tax is determined.
			You can e.g. replace / calculate an own value for $this->config['MwStFallback'] (fallback for not known products)
			or replace the whole setProductsTaxc functionality
			(to do that, read the code of setProductsTax(), and write an own version with return)
		*/
		if (($hp = magnaContribVerify('MagnaCompatibleImportOrders_setProductsTax', 1)) !== false) {
			require($hp);
		}
		$fTax = false;
		if (isset($this->p['products_tax'])) {
			$fTax = $this->getTaxValue($this->p['products_tax']);
		}

		// fetch the tax
		$iTaxID = MagnaDB::gi()->fetchOne(eecho("
			SELECT products_tax_class_id
			  FROM ".TABLE_PRODUCTS."
			 WHERE products_id = '".(int)$this->p['products_id']."'
		", false));
		if (
			$iTaxID !== false
			&& (
				$fTax === false 
				|| !array_key_exists('ForceMPTax', $this->o['orderInfo']) 
				|| !$this->o['orderInfo']['ForceMPTax']
			)
		) {
			// use fallback for external items; Shop items may have "no tax"
			$fTax = SimplePrice::getTaxByClassID((int)$iTaxID, (int)$this->cur['ShippingCountry']['ID'],  (((int)$this->p['products_id'] == 0) ? (float)$this->config['MwStFallback'] : 0.00));
		}

		if ($fTax === false) {
			$fTax = (float)$this->config['MwStFallback'];
		} else {
			$fTax = (float)$fTax;
		}

		// Bug fix for floats as array keys
		$fTax = (string)round($fTax, 2);
		$this->p['products_tax'] = $fTax;

		$fPriceWithoutTax = $this->simplePrice->setPrice($this->p['products_price'])->removeTax($fTax)->getPrice();

		if (!isset($this->taxValues[$fTax])) {
			$this->taxValues[$fTax] = 0.00;
		}
		$this->taxValues[$fTax] += $fPriceWithoutTax * (int)$this->p['products_quantity'];

		if (SHOPSYSTEM != 'oscommerce') {
			$this->p['allow_tax'] = 1;
		} else {
			$this->p['products_price'] = $fPriceWithoutTax;
			$this->p['final_price'] = $this->p['products_price'];
			if (MagnaDB::gi()->columnExistsInTable('allow_tax', TABLE_ORDERS_PRODUCTS)) {
				$this->p['allow_tax'] = 1;
			}
		}
	}
	
	/**
	 * get attribute data in modified 2.0.0
	 * 
	 * @param int $iProductId
	 * @param int $iOptionId
	 * @param int $iOptionValueId
	 * @param string $sFieldName
	 * @return string
	 */
        protected function getProductAttributeData($iProductId, $iOptionId, $iOptionValueId, $sFieldName) {
		return MagnaDB::gi()->fetchOne("
			SELECT ".$sFieldName."
			  FROM ".TABLE_PRODUCTS_ATTRIBUTES."
			 WHERE     products_id = '".$iProductId."'
			       AND options_id = '".$iOptionId."'
			       AND options_values_id = '".$iOptionValueId."'
		");
        }
	
        protected function insertProductAttribute($iProductsId, $aOption, $sSKU) {
		if (empty($aOption['options_name'])) {
			return;
		}

		$aOrderProductsAttribute = array(
			'orders_id' => $this->p['orders_id'],
			'orders_products_id' => $iProductsId,
			'products_options' => $aOption['options_name'],
			'products_options_values' => $aOption['options_values_name'],
			'options_values_price' => 0.0,
			'price_prefix' => ''
		);

		if ($this->config['DBColumnExists']['orders_products_attributes.products_options_id']) {
			$aOrderProductsAttribute['products_options_id'] = $aOption['options_id'];
			$aOrderProductsAttribute['products_options_values_id'] = $aOption['options_values_id'];
		}

		if ($this->config['DBColumnExists']['orders_products_attributes.products_attributes_model']) {
			$aOrderProductsAttribute['products_attributes_model'] = ($this->config['KeyType'] == 'artNr')? 
				$sSKU : 
				$this->getProductAttributeData((int)$this->p['products_id'],(int)$aOption['options_id'], (int)$aOption['options_values_id'], 'attributes_model');
		}
		
		if ($this->config['DBColumnExists']['orders_products_attributes.options_id']) {//gambio
			$aOrderProductsAttribute['options_id'] = $aOption['options_id'];
		}
		if ($this->config['DBColumnExists']['orders_products_attributes.options_values_id']) {//gambio
			$aOrderProductsAttribute['options_values_id'] = $aOption['options_values_id'];
		}

		if ($this->config['DBColumnExists']['orders_products_attributes.attributes_model']) {//modified 2.0.0
			$aOrderProductsAttribute['attributes_model'] = ($this->config['KeyType'] == 'artNr')? 
				$sSKU : 
				$this->getProductAttributeData((int)$this->p['products_id'],(int)$aOption['options_id'], (int)$aOption['options_values_id'], 'attributes_model');
		}
		
		if ($this->config['DBColumnExists']['orders_products_attributes.attributes_ean']) {//modified 2.0.0
			$aOrderProductsAttribute['attributes_ean'] = $this->getProductAttributeData((int)$this->p['products_id'],(int)$aOption['options_id'], (int)$aOption['options_values_id'], 'attributes_ean');
		}

		if ($this->config['DBColumnExists']['orders_products_attributes.products_attributes_id']) {
			$aOrderProductsAttribute['products_attributes_id'] = ($aOption['id'] == false) ? 0 : $aOption['id'];
		}

		MagnaDB::gi()->addNonNullableEntries($aOrderProductsAttribute, TABLE_ORDERS_PRODUCTS_ATTRIBUTES);
		$this->insert(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $aOrderProductsAttribute);
	}

	protected function insertProductProperty($iProductsId, $aOptionId) {
		$iLanguageId = MagnaDB::gi()->fetchOne("
			SELECT languages_id FROM ".TABLE_LANGUAGES." WHERE directory = '".$this->language."'
		");
		$this->db->query(eecho("
			INSERT INTO orders_products_properties (
				orders_products_id, products_properties_combis_id, properties_name, values_name,
				properties_price_type, properties_price
			) (
				SELECT '".$iProductsId."' AS orders_products_id,
				       '".$aOptionId."' AS products_properties_combis_id,
				       ppi.properties_name, ppi.values_name,
				       ppc.combi_price_type AS properties_price_type,
				       ppi.values_price AS properties_price
				  FROM products_properties_index ppi,
				       products_properties_combis ppc
				 WHERE     ppi.products_properties_combis_id = ppc.products_properties_combis_id
				       AND ppi.language_id = '".$iLanguageId."'
				       AND ppc.products_properties_combis_id = '".$aOptionId."'
			)
		", false));
	}
	
	/**
	 *
	 */
	protected function insertProductOld() {
		$this->p['orders_id'] = $this->cur['OrderID'];
		
		/* Attribute Values ermitteln aus der SKU, nicht aus dem Hauptprodukt.
		   Daher bevor products_id ermittelt wird. */
		/* sku needed later */
		if (isset($this->p['products_model'])) {
			$sku = $this->p['products_model']; 
		} else {
			$sku = $this->p['products_id']; 
		}
		$customersLanguage = getLanguageIsoForCountryIso($this->o['orderInfo']['BuyerCountryISO']);
		#if ($this->verbose) echo "magnaSKU2pOpt($sku, $customersLanguage, multivariationsEnabled == ".$this->multivariationsEnabled.")\n";;
		$attrValues = magnaSKU2pOpt($sku, $customersLanguage, $this->multivariationsEnabled);
		if (array_key_exists('options_name', $attrValues)) {
			$attrValues = array($attrValues);
		}
		#if ($this->verbose) echo print_m($attrValues, '$attrValues');
		
		$this->p['products_id'] = magnaSKU2pID($sku);
		
		$this->additionalProductsIdentification();

		$this->mailOrderSummary[] = array(
			'quantity' => $this->p['products_quantity'],
			'name' => $this->p['products_name'],
			'price' => $this->simplePrice->setPrice($this->p['products_price'])->format(),
			'finalprice' => $this->simplePrice->setPrice($this->p['final_price'])->format(),
		);
		if (array_key_exists($this->p['products_id'], $this->syncBatch)) {
			$this->syncBatch[$this->p['products_id']]['NewQuantity']['Value'] += (int)$this->p['products_quantity'];
		} else {
			$this->syncBatch[$this->p['products_id']] = array (
				'SKU' => ('artNr' == $this->config['KeyType'])
					? $sku
					: magnaPID2SKU($this->p['products_id']), /* add ML */
				'NewQuantity' => array (
					'Mode' => 'SUB',
					'Value' => (int)$this->p['products_quantity']
				),
			);
		}

		$tax = false;
		if (isset($this->p['products_tax'])) {
			$tax = $this->getTaxValue($this->p['products_tax']);
		}

		if (!MagnaDB::gi()->recordExists(TABLE_PRODUCTS, array('products_id' => (int)$this->p['products_id']))) {
			$this->p['products_id'] = 0;
		} else {
			$attributeId = magnaSKU2aID($this->p['products_model'], $this->p['products_id'], true);
			if (empty($attributeId)) {
				$attributeId = false;
			}
			/* Lagerbestand reduzieren */
			if ($this->hasReduceStock()) {
				$this->db->query('
					UPDATE '.TABLE_PRODUCTS.'
					   SET products_quantity = products_quantity - '.(int)$this->p['products_quantity'].'
					 WHERE products_id='.(int)$this->p['products_id'].'
				');
				if ($this->multivariationsEnabled) {
					$this->db->query('
						UPDATE '.TABLE_MAGNA_VARIATIONS.'
						   SET variation_quantity = variation_quantity - '.(int)$this->p['products_quantity'].'
						 WHERE products_id='.(int)$this->p['products_id'].'
						       AND '.mlGetVariationSkuField().'="'.$sku.'"
					');
				}
				/* Varianten-Bestand reduzieren, falls Produkt mit Varianten (gibt es bei osCommerce nicht) */
				if (  !$this->gambioPropertiesEnabled
				    &&($this->config['DBColumnExists'][TABLE_PRODUCTS_ATTRIBUTES.'.attributes_stock'])) {
					foreach ($attrValues as $attrV) {
						if (!empty($attrV['options_name'])) {
							$this->db->query('
							    UPDATE '.TABLE_PRODUCTS_ATTRIBUTES.'
							       SET attributes_stock = attributes_stock - '.(int)$this->p['products_quantity'].'
							     WHERE products_id='.(int)$this->p['products_id'].'
							           AND options_id='.(int)$attrV['options_id'].'
							           AND options_values_id='.(int)$attrV['options_values_id'].'
							');
						}
					}
				}
				if ($this->gambioPropertiesEnabled && ($attributeId != false)) {
					$this->db->query(eecho('
						UPDATE products_properties_combis
						   SET combi_quantity = combi_quantity - '.(int)$this->p['products_quantity'].'
						 WHERE products_properties_combis_id = '.$attributeId
					, false));
				}
			}
			/* Steuersatz und Model holen */
			$row = MagnaDB::gi()->fetchRow('
				SELECT products_tax_class_id, products_model
				  FROM '.TABLE_PRODUCTS.' 
				 WHERE products_id="'.(int)$this->p['products_id'].'"
			');
			if ($row !== false) {
				$tax = SimplePrice::getTaxByClassID((int)$row['products_tax_class_id'], (int)$this->cur['ShippingCountry']['ID'],  (float)$this->config['MwStFallback']);
				if ($this->gambioPropertiesEnabled && ($attributeId != false)) {
					$sCombModel = MagnaDB::gi()->fetchOne("
						SELECT combi_model
						  FROM products_properties_combis
						 WHERE products_properties_combis_id = '".$attributeId."'
					");
					$this->p['products_model'] = $this->p['products_model'].'-'.$sCombModel;
					$this->p['properties_combi_model'] = $sCombModel;
				} else {
					$this->p['products_model'] = $row['products_model'];
				}
			}
		}
		if ($tax === false) {
			$tax = (float)$this->config['MwStFallback'];
		} else {
			$tax = (float)$tax;
		}
		//Bugfix for floats as array keys
		$tax = (string)round($tax, 2);

		$this->p['products_tax'] = $tax;

		$priceWOTax = $this->simplePrice->setPrice($this->p['products_price'])->removeTax($tax)->getPrice();

		if (!isset($this->taxValues[$tax])) {
			$this->taxValues[$tax] = 0.0;
		}
		$this->taxValues[$tax] += $priceWOTax * (int)$this->p['products_quantity'];

		if (SHOPSYSTEM != 'oscommerce') {
			$this->p['allow_tax'] = 1;
		} else {
			$this->p['products_price'] = $priceWOTax;
			$this->p['final_price'] = $this->p['products_price'];
		}

		$this->doBeforeInsertOrdersProducts();
		# Produktdatensatz in Tabelle "orders_products".
		$this->insert(TABLE_ORDERS_PRODUCTS, $this->p);
		$ordersProductsId = $this->db->getLastInsertID();

		// orders_products_attributes:
		if (!$this->gambioPropertiesEnabled) {
			foreach ($attrValues as $attrV) {
				$prodOrderAttrData = array(
					'orders_id' => $this->p['orders_id'],
					'orders_products_id' => $ordersProductsId,
					'products_options' => $attrV['options_name'],
					'products_options_values' => $attrV['options_values_name'],
					'options_values_price' => 0.0,
					'price_prefix' => ''
				);
				if (!empty($attrV['options_name'])) {
					if ($this->config['DBColumnExists']['orders_products_attributes.products_options_id']) {
						$prodOrderAttrData['products_options_id'] = $attrV['options_id'];
						$prodOrderAttrData['products_options_values_id'] = $attrV['options_values_id'];
					}
					if ($this->config['DBColumnExists']['orders_products_attributes.products_attributes_model']) {
						$prodOrderAttrData['products_attributes_model'] = ($this->config['KeyType'] == 'artNr')
							? $sku
							: MagnaDB::gi()->fetchOne("
									SELECT attributes_model
									  FROM '.TABLE_PRODUCTS_ATTRIBUTES.'
									 WHERE     products_id = '".(int)$this->p['products_id']."'
										   AND options_id = '".(int)$attrV['options_id']."'
										   AND options_values_id = '".(int)$attrV['options_values_id']."'
							");
					}
					if ($this->config['DBColumnExists']['orders_products_attributes.products_attributes_id']) {
						$prodOrderAttrData['products_attributes_id'] = $attributeId == false ? 0 : $attributeId;
					}
					MagnaDB::gi()->addNonNullableEntries($prodOrderAttrData, TABLE_ORDERS_PRODUCTS_ATTRIBUTES);
					$this->insert(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $prodOrderAttrData);
				}
			}
		}
		if ($this->gambioPropertiesEnabled && ($attributeId != false)) {
			$languageId = MagnaDB::gi()->fetchOne("
				SELECT languages_id FROM ".TABLE_LANGUAGES." WHERE directory = '".$this->language."'
			");
			$this->db->query(eecho("
				INSERT INTO orders_products_properties (
				    orders_products_id, products_properties_combis_id, properties_name, values_name, 
				    properties_price_type, properties_price
				) (
				    SELECT '".$ordersProductsId."' AS orders_products_id,
				           '".$attributeId."' AS products_properties_combis_id,
				           ppi.properties_name, ppi.values_name,
				           ppc.combi_price_type AS properties_price_type,
				           ppi.values_price AS properties_price
				      FROM products_properties_index ppi, products_properties_combis ppc
				     WHERE ppi.products_properties_combis_id = ppc.products_properties_combis_id
				           AND ppi.language_id='".$languageId."'
				           AND ppc.products_properties_combis_id='".$attributeId."'
				)
			", false));
		}
	}

	protected function processShippingTax() {
		$fTax = $this->db->fetchOne("
			SELECT max(products_tax)
			FROM ".TABLE_ORDERS_PRODUCTS."
			WHERE orders_id = '".$this->cur['OrderID']."'
		");
		$this->taxValues[(string)round($fTax, 2)] = array_key_exists((string)round($fTax, 2), $this->taxValues) ? $this->taxValues[(string)round($fTax, 2)] : 0.0;
		$this->taxValues[(string)round($fTax, 2)] += $this->simplePrice->setPrice(
			$this->o['orderTotal']['Shipping']['value']
		)->removeTax($fTax)->getPrice();
		/*//{search: 1427198983}
		if (($this->config['MwStShipping'] <= 0) 
			|| !array_key_exists('Shipping', $this->o['orderTotal'])
		) {
			return;
		}

		if (!isset($this->taxValues[$this->config['MwStShipping']])) {
			$this->taxValues[$this->config['MwStShipping']] = 0.0;
		}
		$this->taxValues[$this->config['MwStShipping']] += $this->simplePrice->setPrice(
			$this->o['orderTotal']['Shipping']['value']
		)->removeTax($this->config['MwStShipping'])->getPrice();
		//*/
	}
	
	protected function addTaxValuesToOrdersTotal() {
		if (empty($this->taxValues)) return;
		if (!defined('MODULE_ORDER_TOTAL_TAX_STATUS') || (MODULE_ORDER_TOTAL_TAX_STATUS != 'true')) {
			return;
		}
		ksort($this->taxValues);
		
		/* fuer summe netto erst brutto-wert nehmen */
		$netto = $this->o['orderTotal']['Total']['value'];
		
		$otc = defined('MODULE_ORDER_TOTAL_TAX_SORT_ORDER') ? MODULE_ORDER_TOTAL_TAX_SORT_ORDER : 60;
		foreach ($this->taxValues as $tax => $value) {
			$this->o['orderTotal']['Tax'.$tax] = array (
				'title' => ML_LABEL_INCL.' '.round($tax, 2).'% '.MAGNA_LABEL_ORDERS_TAX,
				'value' => $this->simplePrice->setPrice($value)->getTaxValue($tax),
				'class' => 'ot_tax',
				'sort_order' => $otc,
			);
			/* steuerbetrag von netto abziehen */
			$netto -= $this->o['orderTotal']['Tax'.$tax]['value'];
		}
		/* Netto Betrag-Eintrag einfuegen. */
		if (SHOPSYSTEM == 'gambio') {
			$this->o['orderTotal']['Netto'] = array (
				'title' => (defined('MODULE_ORDER_TOTAL_TOTAL_NETTO_TITLE') ? MODULE_ORDER_TOTAL_TOTAL_NETTO_TITLE : 'Summe netto') . ':',
				'value' => $netto,
				'class' => 'ot_total_netto',
				'sort_order' => defined('MODULE_ORDER_TOTAL_TOTAL_NETTO_SORT_ORDER')? MODULE_ORDER_TOTAL_TOTAL_NETTO_SORT_ORDER : $otc+1,
			);
		}
	}
	
	/**
	 * This method prepares and inserts data for orders_total.
	 * Child-Classes may extend this method. However this method should be called
	 * at the end to do the actual inertion of data in the database.
	 * parent::insertOrdersTotal(); as last statement.
	 */
	protected function insertOrdersTotal() {
		if (($hp = magnaContribVerify('MagnaCompatibleImportOrders_PreInsertOrdersTotal', 1)) !== false) {
			require($hp);
		}
		//echo print_m($this->o['orderTotal']);
		foreach ($this->o['orderTotal'] as $key => &$entry) {
			$entry['orders_id'] = $this->cur['OrderID'];
			if (defined($entry['title'])) {
				$entry['title'] = constant($entry['title']);
			}
			$entry['text'] = $this->simplePrice->setPrice($entry['value'])->format();
			switch($entry['class']) {
				case('ot_subtotal'): {
					if (defined('MODULE_ORDER_TOTAL_SUBTOTAL_SORT_ORDER')) $entry['sort_order'] = MODULE_ORDER_TOTAL_SUBTOTAL_SORT_ORDER;
					break;
				}
				case('ot_shipping'): {
					if (defined('MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER')) $entry['sort_order'] = MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER;
					break;
				}
				case('ot_total'): {
					if (defined('MODULE_ORDER_TOTAL_TOTAL_SORT_ORDER')) $entry['sort_order'] = MODULE_ORDER_TOTAL_TOTAL_SORT_ORDER;
					break;
				}
			}
			$this->insert(TABLE_ORDERS_TOTAL, $entry);
		}

		// Gambio specific "Kleinunternehmer Regelung"
		if (defined('MAGNA_GAMBIO_PLUGIN_GM_TAX_FREE_STATUS') && MAGNA_GAMBIO_PLUGIN_GM_TAX_FREE_STATUS) {
			$this->insert(TABLE_ORDERS_TOTAL, array(
				'orders_id' => $this->cur['OrderID'],
				'title' => MODULE_ORDER_TOTAL_GM_TAX_FREE_TEXT,
				'class' => 'ot_gm_tax_free',
				'sort_order' => MODULE_ORDER_TOTAL_GM_TAX_FREE_SORT_ORDER
			));
		}
		// echo 'DELETE FROM '.TABLE_ORDERS_TOTAL.' WHERE orders_id="'.$this->cur['OrderID'].'";'."\n\n";	
		if (($hp = magnaContribVerify('MagnaCompatibleImportOrders_PostInsertOrdersTotal', 1)) !== false) {
			require($hp);
		}
	}
	
	/**
	 * Returns an array with the replacement keys and the content for the promotion mail.
	 * @return array
	 */
	protected function generatePromoMailContent() {
		return array (
			'#FIRSTNAME#' => $this->o['order']['billing_firstname'],
			'#LASTNAME#' => $this->o['order']['billing_lastname'],
			'#EMAIL#' => $this->o['customer']['customers_email_address'],
			'#PASSWORD#'  => $this->cur['customer']['Password'],
			'#MORDERID#' => $this->o['orderInfo']['MOrderID'],
			'#ORDERSUMMARY#' => $this->mailOrderSummary,
			'#MARKETPLACE#' => $this->marketplaceTitle,
			'#SHOPURL#' => HTTP_SERVER.DIR_WS_CATALOG,
			'#KUNDE#' => $this->cur['customer']['CID'],
			'#SHOPORDERID#' => $this->cur['OrderID'],
		);
	}
	
	protected function sendPromoMail() {
		if (($this->config['MailSend'] != 'true') || (get_class($this->db) == 'MagnaTestDB')) {
			// echo print_m($this->generatePromoMailContent());
			return;
		}
		sendSaleConfirmationMail(
			$this->mpID,
			$this->o['customer']['customers_email_address'],
			$this->generatePromoMailContent()
		);
	}

	private function createGambioOrderConfirmation() {
		if (!$this->blGambioOrderConfirmation) return;
		try {
			$coo_recreate_order = MainFactory::create_object('RecreateOrder', array($this->cur['OrderID']));
		} catch (Exception $e) {
			if ((MAGNA_CALLBACK_MODE == 'STANDALONE') || $this->verbose) {
				echo print_m($e->getErrorArray(), 'Error: '.$e->getMessage(), true);
			}
			return;
		}
	}

	/**
	 * send out of stock warning e-mail
	 * if configured so
	 *
	 * Gambio only (uses Gambio functionality)
	 */
	private function sendGambioOutOfStockNotification() {
		if (!defined('STOCK_REORDER_LEVEL')) return;
		if (!defined('GM_OUT_OF_STOCK_NOTIFY_TEXT')) return;
		if ($this->p['products_id'] == 0) return; // foreign item
		$blUseCheckoutProcessProcess = false; // don't create object if not necessary (performance)
		if (   (!function_exists('xtc_php_mail'))
		    || (!function_exists('htmlentities_wrapper'))) {
			if (file_exists(DIR_FS_CATALOG.'system/classes/checkout/CheckoutProcessProcess.inc.php')) {
				require_once(DIR_FS_CATALOG.'system/classes/checkout/CheckoutProcessProcess.inc.php');
				$blUseCheckoutProcessProcess = true;
			} else {
				return;
			}
		}
		if (STOCK_CHECK == false) return;
		if (SEND_EMAILS == false) return;
		if ($this->stock_left > STOCK_REORDER_LEVEL) return;
		$iLanguageId = MagnaDB::gi()->fetchOne("
			SELECT languages_id FROM ".TABLE_LANGUAGES." WHERE directory = '".$this->language."'
		");
		$sProductName = MagnaDB::gi()->fetchOne("
			SELECT products_name FROM ".TABLE_PRODUCTS_DESCRIPTION."
			 WHERE products_id = ".$this->p['products_id']." AND language_id = $iLanguageId
		");
		$sSubject = GM_OUT_OF_STOCK_NOTIFY_TEXT . ' ' . $sProductName;
		$sBody = $sProductName . "\n" . $this->p['products_model'] . "\n"
			. GM_OUT_OF_STOCK_NOTIFY_TEXT . ': ' . (double)$this->stock_left . "\n" . HTTP_SERVER
			. DIR_WS_CATALOG . 'product_info.php?info=p' . xtc_get_prid($this->p['products_id'])
			. "\n" . HTTP_SERVER . DIR_WS_CATALOG . 'admin/categories.php?pID='
			. xtc_get_prid($this->p['products_id']) . '&action=new_product';

		if ($blUseCheckoutProcessProcess) {
			$coo_cpp = MainFactory::create_object('CheckoutProcessProcess'); 
			if (!method_exists($coo_cpp, 'send_mail')) return;
			$coo_cpp->send_mail($sSubject, $sBody);
		} else {
		xtc_php_mail(STORE_OWNER_EMAIL_ADDRESS, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, STORE_NAME, '',
			STORE_OWNER_EMAIL_ADDRESS, STORE_NAME, '', '', $sSubject,
			nl2br(htmlentities_wrapper($sBody)), $sBody);
		}
	}

	/*
	 * For newer Gambio versions: set total weight in the orders table
	 */
	protected function setOrderWeight() {
		if (!MagnaDB::gi()->columnExistsInTable('order_total_weight', TABLE_ORDERS)) {
			return;
		}
		$iTotalWeight = 0.0;
		foreach ($this->o['products'] as $p) {
			$iCurrSingleWeight = MagnaDB::gi()->fetchOne('SELECT products_weight
				FROM '.TABLE_PRODUCTS.'
				WHERE products_id = '.magnaSKU2pID($p['products_id']));
			$iTotalWeight += $iCurrSingleWeight * $p['products_quantity'];
			// consider also Variations
			if ($iCurrAid = magnaSKU2aID($p['products_id'], false, $this->multivariationsEnabled)) { // function returns false if none
				if ($this->gambioPropertiesEnabled) {
					$iCurrSinglePropertyWeight = (float)MagnaDB::gi()->fetchOne('SELECT combi_weight
						FROM products_properties_combis
						WHERE products_properties_combis_id = '.(int)$iCurrAid);
					$iTotalWeight += $iCurrSinglePropertyWeight * $p['products_quantity'];
				} else {
					if (!is_array($iCurrAid)) $iCurrAid = array($iCurrAid); // multiple attributes can occur
					foreach ($iCurrAid as $iAttrId) {
						$iCurrSingleAttrWeight = MagnaDB::gi()->fetchOne('SELECT IF((weight_prefix=\'-\'), (-1.0*options_values_weight), options_values_weight)
						FROM '.TABLE_PRODUCTS_ATTRIBUTES.'
						WHERE products_id = '.magnaSKU2pID($p['products_id']).'
						AND products_attributes_id = '.$iAttrId);
						$iTotalWeight += $iCurrSingleAttrWeight * $p['products_quantity'];
					}
				}
			}
		}
		$this->db->update(TABLE_ORDERS, array('order_total_weight' => $iTotalWeight),
			array('orders_id' => $this->cur['OrderID']));
	}

	/*
	 * For newer Gambio versions
	 */
	protected function setTransportConditionsAccepted() {
		if (
			class_exists('IdType') && class_exists('StringType')
			&& class_exists('StaticGXCoreLoader') && method_exists('StaticGXCoreLoader', 'getService')
		) {
			try {
				$orderId           = new IdType($this->cur['OrderID']);
				$orderReadService  = StaticGXCoreLoader::getService('OrderRead');
				$order             = $orderReadService->getOrderById($orderId);
				$transportConditions = $order->setAddonValue(new StringType('transportConditions'), new StringType('accepted'));
			} catch (Exception $ex) {
				//something happen perhaps OrderRead-Service dont exists
			}
		}
	}
	
	protected function processSingleOrder() {
		if ($this->verbose) echo print_m($this->o, 'order');
		if (SHOPSYSTEM == 'gambio') {
			/**
			 * check if state(s) exists in table zones
			 * if not add state to city ( - separated) and clean state
			 * - after consultation with gambio, not needed
			$sArrayKey = $sStateKey = $sCityKey = '';
			foreach (array(
				array('adress', 'entry_state', 'entry_city'),
				array('order', 'billing_state', 'billing_city'),
				array('order', 'delivery_state', 'delivery_city'),
				array('order', 'customers_state', 'customers_city'),
			) as $aCheckStateData) {
				list ($sArrayKey, $sStateKey, $sCityKey) = $aCheckStateData;
				if (
					array_key_exists($sArrayKey, $this->o)
					&& array_key_exists($sStateKey, $this->o[$sArrayKey])
					&& !empty($this->o[$sArrayKey][$sStateKey])
					&& !MagnaDB::gi()->recordExists(TABLE_ZONES, array('zone_name' => $this->o[$sArrayKey][$sStateKey]))
				) {
					$this->o[$sArrayKey][$sCityKey] = (
						array_key_exists($sCityKey, $this->o[$sArrayKey])
						? $this->o[$sArrayKey][$sCityKey].' - '
						: ''
					).$this->o[$sArrayKey][$sStateKey];
					$this->o[$sArrayKey][$sStateKey] = '';
				}
			}*/
			if (    (!MagnaDB::gi()->columnExistsInTable('entry_house_number', TABLE_ADDRESS_BOOK)) 
			     || (!defined('ACCOUNT_SPLIT_STREET_INFORMATION'))
			     || (ACCOUNT_SPLIT_STREET_INFORMATION !== 'true')) {
				if (!empty($this->o['adress']['entry_house_number'])) {
					$this->o['adress']['entry_street_address'] .= ' '.$this->o['adress']['entry_house_number'];
					unset($this->o['adress']['entry_house_number']);
				}
				foreach(array('customers','billing','delivery') as $addrPurpose) {
					if (!empty($this->o['order'][$addrPurpose.'_house_number'])) {
						$this->o['order'][$addrPurpose.'_street_address'] .= ' '.$this->o['order'][$addrPurpose.'_house_number'];
						unset($this->o['order'][$addrPurpose.'_house_number']);
					}
				}
			}

			if (    (!MagnaDB::gi()->columnExistsInTable('entry_additional_info', TABLE_ADDRESS_BOOK)) 
			     || (!defined('ACCOUNT_ADDITIONAL_INFO'))
			     || (    (ACCOUNT_ADDITIONAL_INFO !== 'true')
			          && (defined(ACCOUNT_SUBURB))
			          && (ACCOUNT_SUBURB == 'true')
			        )
			) {
				if (!empty($this->o['adress']['entry_additional_info'])) {
					$this->o['adress']['entry_suburb'] = $this->o['adress']['entry_additional_info'];
					unset($this->o['adress']['entry_additional_info']);
				}
				foreach(array('customers','billing','delivery') as $addrPurpose) {
					if (!empty($this->o['order'][$addrPurpose.'_additional_info'])) {
						if (!isset($this->o['order'][$addrPurpose.'_suburb'])) {
							$this->o['order'][$addrPurpose.'_suburb'] = $this->o['order'][$addrPurpose.'_additional_info'];
						} else {
							$this->o['order'][$addrPurpose.'_suburb'] .= ' '.$this->o['order'][$addrPurpose.'_additional_info'];
						}
						unset($this->o['order'][$addrPurpose.'_additional_info']);
					}
				}
			}
		}
		$this->o['_processingData'] = array();
		if (!$this->updateOrderCurrency($this->o['order']['currency'])) {
			/* Currency is not available in this shop or 
			   the currency value can't be determined. */
			if ($this->verbose) echo '!updateOrderCurrency'."\n";
			return;
		}
		/* Reset order specific class atributes */
		$this->cur = array();
		$this->taxValues = array();
		$this->mailOrderSummary = array();
		
		/* Prepare order specific informations */
		$this->prepareOrderInfo();

		// adjust timezone
		$this->o['order']['date_purchased'] = magnaTimeToLocalTime($this->o['order']['date_purchased']);
		
		$this->processCustomer();
		#echo print_m($this->cur['customer'], '$customer');
		
		if ($this->orderExists()) {
			return;
		}
		$this->insertOrder();
	
		$this->o['_processingData']['ProductsCount'] = 0;
		foreach ($this->o['products'] as $p) {
			$this->p = $p;
			$this->insertProduct();
		}
		//echo 'DELETE FROM '.TABLE_ORDERS_PRODUCTS.' WHERE orders_id="'.$this->cur['OrderID'].'";'."\n\n";
		
		$this->processShippingTax();
		
		$this->addTaxValuesToOrdersTotal();
		
		$this->insertOrdersTotal();

		/* Gambio: create order confirmation PDF */
		if (SHOPSYSTEM == 'gambio') {
			$this->createGambioOrderConfirmation();
			$this->setOrderWeight();
			$this->setTransportConditionsAccepted();
		}
		
		$this->sendPromoMail();
		
		$this->addCurrentOrderToProcessed();
		
		$this->lastOrderDate = $this->o['order']['date_purchased'];

		$this->deleteGuestCustomer();
	}
	
	protected function acknowledgeImportedOrders() {
		if (empty($this->processedOrders)) return;
		/* Acknowledge imported orders */
		$request = array(
			'ACTION' => 'AcknowledgeImportedOrders',
			'SUBSYSTEM' => $this->marketplace,
			'MARKETPLACEID' => $this->mpID,
			'DATA' => $this->processedOrders,
		);
		if (get_class($this->db) == 'MagnaTestDB') {
			if ($this->verbose) echo print_m($request);
			$this->processedOrders = array();
			return;
		}
		try {
			$res = MagnaConnector::gi()->submitRequest($request);
			$this->processedOrders = array();
		} catch (MagnaException $e) {
			if ((MAGNA_CALLBACK_MODE == 'STANDALONE') || $this->verbose) {
				echo print_m($e->getErrorArray(), 'Error: '.$e->getMessage(), true);
			}
			if ($e->getCode() == MagnaException::TIMEOUT) {
				$e->saveRequest();
				$e->setCriticalStatus(false);
			}
		}
	}

	/**
	 * ensure that orders_total data are complete
	 */
	protected function fixOrdersTotal() {
		require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/MagnaRecalcOrdersTotal.php');
		$mfot = new MagnaRecalcOrdersTotal();
		ob_start();
		$mfot->execute($this->marketplace, 100);
		ob_end_clean();
	}
	
	protected function submitSyncBatch() {
		if (get_class($this->db) != 'MagnaTestDB') {
			require_once(DIR_MAGNALISTER_CALLBACK.'inventoryUpdate.php');
			magnaInventoryUpdateByOrderImport(array_values($this->syncBatch), $this->mpID);
		}
	}
	
	final public function process() {
		while (($orders = $this->getOrders()) !== false) {
			#if ($this->verbose) echo print_m($orders, 'orders');
			while ($order = array_shift($orders)) {
				$this->cur = array();
				$this->o = $order;
				
				$continue = false;
				/* {Hook} "MagnaCompatibleImportOrders_PreOrderImport": Is called before the order in <code>$this->o</code> is imported.
					Variables that can be used:
					<ul><li>$this->o: The order that is going to be imported. The order is an 
					        associative array representing the structures of the order and customer related shop tables.</li>
					    <li>$this->mpID: The ID of the marketplace.</li>
					    <li>$this->marketplace: The name of the marketplace.</li>
					    <li>$this->db: Instance of the magnalister database class. USE THIS for writing or changing data in the database during the
					        order import. DO NOT USE the shop functions or MagnaDB::gi() for this purpose!</li>
						<li>$continue (bool): Set this to true to skip the processing of current order.</li>
					</ul>
				*/
				if (($hp = magnaContribVerify('MagnaCompatibleImportOrders_PreOrderImport', 1)) !== false) {
					require($hp);
				}
				if ($continue) {
					continue;
				}
				
				$this->processSingleOrder();
				
				/* {Hook} "MagnaCompatibleImportOrders_PostOrderImport": Is called after the order in <code>$this->o</code> is imported.
					Usefull to manipulate some of the data in the database
					Variables that can be used:
					<ul><li>$this->o: The order that has just been imported. The order is an associative array representing the
					        structures of the order and customer related shop tables.</li>
					    <li>$this->mpID: The ID of the marketplace.</li>
					    <li>$this->marketplace: The name of the marketplace.</li>
					    <li>$this->cur['OrderID']: The Order ID of the shop (<code>orders_id</code>).</li>
					    <li>$this->cur['customer']['ID']: The Customers ID of the shop (<code>customers_id</code>).</li>
					    <li>$this->db: Instance of the magnalister database class. USE THIS for writing or changing data in the database during the
					        order import. DO NOT USE the shop functions or MagnaDB::gi() for this purpose!</li>
					</ul>
				*/
				if (($hp = magnaContribVerify('MagnaCompatibleImportOrders_PostOrderImport', 1)) !== false) {
					require($hp);
				}
			}
			$this->acknowledgeImportedOrders();
		}
		
		$this->submitSyncBatch();
		
		if (get_class($this->db) != 'MagnaTestDB') {
			if (!empty($this->lastOrderDate)) {
				setDBConfigValue($this->marketplace.'.orderimport.lastrun', $this->mpID, $this->lastOrderDate, true);
			}
			$this->fixOrdersTotal();
		}
		
	}


    /**
     * Gambio specific check when it's a variation product if only main product stock or variation stock should be deducted
     *
     *  0 = Default deduct both
     *  1 = only main product
     *  2 = only variation
     *  3 = no deduction (not supported by magnalister - customer need to change magnalister config if he does not want to reduce stock during order import)
     *
     * @param $aOptions - Variations regardless of attributes or gambio properties
     * @return int
     */
    private function getGambioPropertiesQuantityOption($aOptions) {
        $gambioPropertiesQuantityOption = 0;

        if (   $this->gambioPropertiesEnabled
            && !empty($aOptions)
            && MagnaDB::gi()->columnExistsInTable('use_properties_combis_quantity', TABLE_PRODUCTS)
        ) {
            $gambioPropertiesQuantityOption = (int)MagnaDB::gi()->fetchOne("
                SELECT `use_properties_combis_quantity`
                  FROM `".TABLE_PRODUCTS."`
                 WHERE `products_id` = '".MagnaDB::gi()->escape($this->p['products_id'])."'
                 LIMIT 1
            ");
        }

        return $gambioPropertiesQuantityOption;
    }

}
