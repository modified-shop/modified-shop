<?php
class ShopgatePluginApi extends ShopgateObject implements ShopgatePluginApiInterface {
	/**
	 * @var ShopgatePlugin
	 */
	protected $plugin;

	/**
	 * @var ShopgateConfigInterface
	 */
	protected $config;

	/**
	 * @var ShopgateMerchantApiInterface
	 */
	protected $merchantApi;

	/**
	 * @var ShopgateAuthentificationServiceInterface
	 */
	protected $authService;

	/**
	 * Parameters passed along the action (usually per POST)
	 *
	 * @var mixed[]
	 */
	protected $params;

	/**
	 * @var string[]
	 */
	protected $actionWhitelist;
	
	/**
	 * @var mixed
	 */
	protected $responseData;

	/**
	 * @var ShopgatePluginApiResponse
	 */
	protected $response;
	
	/**
	 * @var string The trace ID of the incoming request.
	 */
	protected $trace_id;
	
	public function __construct(
			ShopgateConfigInterface &$config,
			ShopgateAuthentificationServiceInterface &$authService,
			ShopgateMerchantApiInterface &$merchantApi,
			ShopgatePlugin &$plugin,
			ShopgatePluginApiResponse &$response = null
	) {
		$this->config = $config;
		$this->authService = $authService;
		$this->merchantApi = $merchantApi;
		$this->plugin = $plugin;
		$this->response = $response;
		$this->responseData = array();
		
		// initialize action whitelist
		$this->actionWhitelist = array(
				'ping',
				'cron',
				'add_order',
				'update_order',
				'get_customer',
				'get_items_csv',
				'get_categories_csv',
				'get_reviews_csv',
				'get_pages_csv',
				'get_log_file',
				'clear_log_file',
				'check_coupon',
				'redeem_coupon'
		);
	}

	public function handleRequest(array $data = array()) {
		// log incoming request
		$this->log(ShopgateLogger::getInstance()->cleanParamsForLog($data), ShopgateLogger::LOGTYPE_ACCESS);

		// save the params
		$this->params = $data;
		
		// save trace_id
		if (isset($this->params['trace_id'])) {
			$this->trace_id = $this->params['trace_id'];
		}
		
		try {
			$this->authService->checkAuthentification();

			// set error handler to Shopgate's handler if requested
			if (!empty($this->params['use_errorhandler'])) {
				set_error_handler('ShopgateErrorHandler');
			}

			// check if an action to call has been passed, is known and enabled
			if (empty($this->params['action'])) {
				throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_NO_ACTION, 'Passed parameters: '.var_export($data, true));
			}

			// check if the action is white-listed
			if (!in_array($this->params['action'], $this->actionWhitelist)) {
				throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_UNKNOWN_ACTION, "'{$this->params['action']}'");
			}

			// check if action is enabled in the config
			$configArray = $this->config->toArray();
			if (empty($configArray['enable_'.$this->params['action']])) {
				throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_DISABLED_ACTION, "'{$this->params['action']}'");
			}
			
			// enable debugging if requested
			if (!empty($data['debug_log'])) {
				ShopgateLogger::getInstance()->enableDebug();
			}

			// call the action
			$action = $this->camelize($this->params['action']);
			$this->{$action}();
		} catch (ShopgateLibraryException $e) {
			$error = $e->getCode();
			$errortext = $e->getMessage();
		} catch (ShopgateMerchantApiException $e) {
			$error = ShopgateLibraryException::MERCHANT_API_ERROR_RECEIVED;
			$errortext = ShopgateLibraryException::getMessageFor(ShopgateLibraryException::MERCHANT_API_ERROR_RECEIVED).': "'.$e->getCode() . ' - ' . $e->getMessage().'"';
		} catch (Exception $e) {
			$message  = "\n".get_class($e)."\n";
			$message .= 'with code:   '.$e->getCode()."\n";
			$message .= 'and message: \''.$e->getMessage()."'\n";

			// new ShopgateLibraryException to build proper error message and perform logging
			$se = new ShopgateLibraryException($message);
			$error = $se->getCode();
			$errortext = $se->getMessage();
		}

		// print out the response
		if (!empty($error)) {
			if (empty($this->response)) $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);
			$this->response->markError($error, $errortext);
		}
		
		if (empty($this->response)) {
			trigger_error('No response object defined. This should _never_ happen.', E_USER_ERROR);
		}
		
		$this->response->setData($this->responseData);
		$this->response->send();
		
		// return true or false
		return (empty($error));
	}


	######################################################################
	## Following methods represent the Shopgate Plugin API's actions:   ##
	######################################################################

	/**
	 * Represents the "ping" action.
	 *
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_ping/de
	 */
	protected function ping() {
		// obfuscate data relevant for authentication
		$config = $this->config->toArray();
		$config['customer_number']	= ShopgateLogger::OBFUSCATION_STRING;
		$config['shop_number']		= ShopgateLogger::OBFUSCATION_STRING;
		$config['apikey']			= ShopgateLogger::OBFUSCATION_STRING;

		// prepare response data array
		$this->responseData['pong'] = 'OK';
		$this->responseData['configuration'] = $config;
		$this->responseData['plugin_info'] = $this->plugin->createPluginInfo();
		$this->responseData['permissions'] = $this->getPermissions();
		$this->responseData['php_version'] = phpversion();
		$this->responseData['php_config'] = $this->getSettings();
		$this->responseData['php_curl'] = function_exists('curl_version') ? curl_version() : 'No PHP-CURL installed';
		$this->responseData['php_extensions'] = get_loaded_extensions();
		$this->responseData['shopgate_library_version'] = SHOPGATE_LIBRARY_VERSION;
		$this->responseData['plugin_version'] = defined('SHOPGATE_PLUGIN_VERSION') ? SHOPGATE_PLUGIN_VERSION : 'UNKNOWN';
		
		// set data and return response
		if (empty($this->response)) $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);
	}

	/**
	 * Represents the "add_order" action.
	 *
	 * @throws ShopgateLibraryException
	 */
	protected function cron() {
		if (empty($this->params['jobs']) || !is_array($this->params['jobs'])) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_CRON_NO_JOBS);
		}

		// time tracking
		$starttime = microtime(true);

		// references
		$message = '';
		$errorcount = 0;

		// execute the jobs
		foreach ($this->params['jobs'] as $job) {
			if (empty($job['job_name'])) {
				throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_CRON_NO_JOB_NAME);
			}

			if (empty($job['job_params'])) {
				$job['job_params'] = array();
			}

			try {
				$jobErrorcount = 0;

				// job execution
				$this->plugin->cron($job['job_name'], $job['job_params'], $message, $jobErrorcount);

				// check error count
				if ($jobErrorcount > 0) {
					$message .= 'Errors happend in job: "'.$job['job_name'].'" ('.$jobErrorcount.' errors)';
					$errorcount += $jobErrorcount;
				}
			} catch (Exception $e) {
				$errorcount++;
				$message .= 'Job aborted: "'.$e->getMessage().'"';
			}
		}

		// time tracking
		$endtime = microtime(true);
		$runtime = $endtime - $starttime;
		$runtime = round($runtime, 4);

		// prepare response
		$responses = array();
		$responses['message'] = $message;
		$responses['execution_error_count'] = $errorcount;
		$responses['execution_time'] = $runtime;

		if (empty($this->response)) $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);
		$this->responseData = array_merge($responses);
	}

	/**
	 * Represents the "add_order" action.
	 *
	 * @throws ShopgateLibraryException
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_add_order/de
	 */
	protected function addOrder() {
		if (!isset($this->params['order_number'])) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_NO_ORDER_NUMBER);
		}

		$orders = $this->merchantApi->getOrders(array('order_numbers[0]'=>$this->params['order_number'], 'with_items' => 1))->getData();
		if (empty($orders)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE, '"orders" not set. Response: '.var_export($orders, true));
		}
		if (count($orders) > 1) {
			throw new ShopgateLibraryException(ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE, 'more than one order in response. Response: '.var_export($orders, true));
		}

		if (empty($this->response)) $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);
		
		$orderData = $this->plugin->addOrder($orders[0]);
		if (is_array($orderData)) {
			$this->responseData = array_merge($orderData, $this->responseData);
		} else {
			$this->responseData['external_order_id'] = $orderData;
			$this->responseData['external_order_number'] = null;
		}
	}

	/**
	 * Represents the "update_order" action.
	 *
	 * @throws ShopgateLibraryException
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_update_order/de
	 */
	protected function updateOrder() {
		if (!isset($this->params['order_number'])) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_NO_ORDER_NUMBER);
		}

		$orders = $this->merchantApi->getOrders(array('order_numbers[0]'=>$this->params['order_number']))->getData();

		if (empty($orders)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE, '"order" not set. Response: '.var_export($orders, true));
		}

		if (count($orders) > 1) {
			throw new ShopgateLibraryException(ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE, 'more than one order in response. Response: '.var_export($orders, true));
		}
		
		$payment = 0;
		$shipping = 0;

		if (isset($this->params['payment'])) {
			$payment = (bool) $this->params['payment'];
		}
		if (isset($this->params['shipping'])) {
			$shipping = (bool) $this->params['shipping'];
		}

		$orders[0]->setUpdatePayment($payment);
		$orders[0]->setUpdateShipping($shipping);

		if (empty($this->response)) $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);
		
		$orderData = $this->plugin->updateOrder($orders[0]);
		if (is_array($orderData)) {
			$this->responseData = array_merge($orderData, $this->responseData);
		} else {
			$this->responseData['external_order_id'] = $orderData;
			$this->responseData['external_order_number'] = null;
		}
	}

	/**
	 * Represents the "get_customer" action.
	 *
	 * @throws ShopgateLibraryException
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_customer/de
	 */
	protected function getCustomer() {
		if (!isset($this->params['user'])) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_NO_USER);
		}

		if (!isset($this->params['pass'])) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_NO_PASS);
		}

		$customer = $this->plugin->getCustomer($this->params['user'], $this->params['pass']);
		if (!is_object($customer) || !($customer instanceof ShopgateCustomer)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_API_WRONG_RESPONSE_FORMAT, 'Plugin Response: '.var_export($customer, true));
		}

		$customerData = $customer->toArray();
		$addressList = $customerData['addresses'];
		unset($customerData['addresses']);

		if (empty($this->response)) $this->response = new ShopgatePluginApiResponseAppJson($this->trace_id);
		$this->responseData["user_data"] = $customerData;
		$this->responseData["addresses"] = $addressList;
	}

	/**
	 * Represents the "get_items_csv" action.
	 *
	 * @throws ShopgateLibraryException
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_items_csv/de
	 */
	protected function getItemsCsv() {
		if (isset($this->params['limit']) && isset($this->params['offset'])) {
			$this->plugin->setExportLimit((int) $this->params['limit']);
			$this->plugin->setExportOffset((int) $this->params['offset']);
			$this->plugin->setSplittedExport(true);
		}

		// generate / update items csv file if requested
		$this->plugin->startGetItemsCsv();

		if (empty($this->response)) $this->response = new ShopgatePluginApiResponseTextCsv($this->trace_id);
		$this->responseData = $this->config->getItemsCsvPath();
	}

	/**
	 * Represents the "get_categories_csv" action.
	 *
	 * @throws ShopgateLibraryException
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_categories_csv/de
	 */
	protected function getCategoriesCsv() {
		// generate / update categories csv file
		$this->plugin->startGetCategoriesCsv();

		
		if (empty($this->response)) $this->response = new ShopgatePluginApiResponseTextCsv($this->trace_id);
		$this->responseData = $this->config->getCategoriesCsvPath();
	}

	/**
	 * Represents the "get_reviews_csv" action.
	 *
	 * @throws ShopgateLibraryException
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_reviews_csv/de
	 */
	protected function getReviewsCsv() {
		// generate / update reviews csv file
		$this->plugin->startGetReviewsCsv();

		if (empty($this->response)) $this->response = new ShopgatePluginApiResponseTextCsv($this->trace_id);
		$this->responseData = $this->config->getReviewsCsvPath();
	}

	/**
	 * Represents the "get_pages_csv" action.
	 *
	 * @todo
	 * @throws ShopgateLibraryException
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_pages_csv/de
	 */
	protected function getPagesCsv() {
		if (empty($this->response)) $this->response = new ShopgatePluginApiResponseTextCsv($this->trace_id);
		$this->responseData = $this->config->getPagesCsvPath();
	}
	
	/**
	 * Represents the "get_log_file" action.
	 *
	 * @throws ShopgateLibraryException
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_log_file/de
	 */
	protected function getLogFile() {
		// disable debug log for this action
		$logger = &ShopgateLogger::getInstance();
		$logger->disableDebug();
		$logger->keepDebugLog(true);
		
		$type = (empty($this->params['log_type'])) ? ShopgateLogger::LOGTYPE_ERROR : $this->params['log_type'];
		$lines = (!isset($this->params['lines'])) ? null : $this->params['lines'];

		$log = $logger->tail($type, $lines);

		// return the requested log file content and end the script
		if (empty($this->response)) $this->response = new ShopgatePluginApiResponseTextPlain($this->trace_id);
		$this->responseData = $log;
	}

	/**
	 * Represents the "get_orders" action.
	 *
	 * @throws ShopgateLibraryException
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_orders/de
	 * @todo
	 */
	protected function getOrders() {
		/**** not yet implemented ****/

		//if (!empty($this->params['external_customer_number'])) {
	}

	
	###############
	### Helpers ###
	###############
	
	private function getSettings() {
		$settingDetails = array();

		$allSettings = ini_get_all();

		$settings = array(
				'max_execution_time',
				'memory_limit',
				'allow_call_time_pass_reference',
				'disable_functions',
				'display_errors',
				'file_uploads',
				'include_path',
				'register_globals',
				'safe_mode'
		);

		foreach($settings as $setting) {
				$settingDetails[$setting] = (!empty($allSettings[$setting]))
					? $allSettings[$setting]
					: 'undefined'
				;
		}

		return $settingDetails;
	}

	private function getPermissions() {
		$permissions = array();
		$files = array(
				# default paths
				SHOPGATE_BASE_DIR.'/config/myconfig.php',
				SHOPGATE_BASE_DIR.'/temp/',
				SHOPGATE_BASE_DIR.'/temp/cache/',
				SHOPGATE_BASE_DIR.'/temp/logs/',
				
				# csv files
				$this->config->getItemsCsvPath(),
				$this->config->getCategoriesCsvPath(),
				$this->config->getReviewsCsvPath(),
				
				# log files
				$this->config->getAccessLogPath(),
				$this->config->getRequestLogPath(),
				$this->config->getErrorLogPath(),
				$this->config->getDebugLogPath(),
				
				# cache files
				$this->config->getRedirectKeywordCachePath(),
				$this->config->getRedirectSkipKeywordCachePath(),
		);

		foreach ($files as $file) {
			$permission = array();
			$permission['file'] = $file;
			$permission['exist'] = (bool) file_exists($file);
			$permission['writeable'] = (bool) is_writable($file);
			$permission['permission'] = '-';
			if (file_exists($file)) {
				$permission['last_modification_time'] = date('d.m.Y H:i:s.', filemtime($file));
			}
			
			$fInfo = pathinfo($file);
			if (file_exists($file)) {
				$permission['permission'] = substr(sprintf('%o', fileperms($file)), -4);
			} else {
				if (file_exists($fInfo['dirname'])) {
					$permission['parent_permission'] = substr(sprintf('%o', fileperms($fInfo['dirname'])), -4);
				}
			}

			$permissions[] = $permission;
		}

		return $permissions;
	}
}

class ShopgateMerchantApi extends ShopgateObject implements ShopgateMerchantApiInterface {
	/**
	 * @var ShopgateAuthentificationServiceInterface
	 */
	private $authService;
	
	/**
	 * @var string
	 */
	private $shopNumber;
	
	/**
	 * @var string
	 */
	private $apiUrl;
	
	private $curlOpt = array();
	
	public function __construct(ShopgateAuthentificationServiceInterface &$authService, $shopNumber, $apiUrl) {
		$this->authService = $authService;
		$this->shopNumber = $shopNumber;
		$this->apiUrl = $apiUrl;
		$this->curlOpt = $this->getCurlOptArray();
	}
	
	/**
	 * Returns an array of curl-options for requests
	 *
	 * @return mixed[]
	 */
	protected function getCurlOptArray() {
		$opt = array();

		$opt[CURLOPT_HEADER] = false;
		$opt[CURLOPT_USERAGENT] = 'ShopgatePlugin/'.(defined('SHOPGATE_PLUGIN_VERSION') ? SHOPGATE_PLUGIN_VERSION : 'called outside plugin');
		$opt[CURLOPT_SSL_VERIFYPEER] = false;
		$opt[CURLOPT_RETURNTRANSFER] = true;
		$opt[CURLOPT_HTTPHEADER] = array(
				'X-Shopgate-Library-Version: '. SHOPGATE_LIBRARY_VERSION,
				'X-Shopgate-Plugin-Version: '.(defined('SHOPGATE_PLUGIN_VERSION') ? SHOPGATE_PLUGIN_VERSION : 'called outside plugin'),
				$this->authService->buildAuthUserHeader(),
				$this->authService->buildMerchantApiAuthTokenHeader()
		);
		
		$opt[CURLOPT_TIMEOUT] = 30; // Default timeout 30sec
		$opt[CURLOPT_POST] = true;
		
		return $opt;
	}
	
	/**
	 * Prepares the request and sends it to the configured Shopgate Merchant API.
	 *
	 * @param mixed[] $data The parameters to send.
	 * @return mixed The JSON decoded response.
	 * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an error occured.
	 */
	protected function sendRequest($data) {
		$data['shop_number'] = $this->shopNumber;
		$data['trace_id'] = 'spa-'.uniqid();

		$this->log('Sending request to "'.$this->apiUrl.'": '.ShopgateLogger::getInstance()->cleanParamsForLog($data), ShopgateLogger::LOGTYPE_REQUEST);

		$this->authService->startNewSession();
		
		$curl = curl_init($this->apiUrl);
		
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt_array($curl, $this->curlOpt);
		
		$response = curl_exec($curl);
		$info = curl_getinfo($curl);
		curl_close($curl);

		// check the result
		if (!$response) {
			// exception without logging - this might cause spamming your logs and we will know when our API is offline anyways
			throw new ShopgateLibraryException(ShopgateLibraryException::MERCHANT_API_NO_CONNECTION, null, false, false);
		}

		$decodedResponse = $this->jsonDecode($response, true);

		if (empty($decodedResponse)) {
			// exception without logging - this might cause spamming your logs and we will know when our API is offline anyways
			throw new ShopgateLibraryException(ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE, 'Response: '.$response, true, false);
		}

		if ($decodedResponse['error'] != 0) {
			throw new ShopgateMerchantApiException($decodedResponse['error'], $decodedResponse['error_text']);
		}

		return $decodedResponse;
	}


	######################################################################
	## Following methods represent the Shopgate Merchant API's actions: ##
	######################################################################

	######################################################################
	## Orders                                                           ##
	######################################################################
	public function getOrders($parameters) {
		$data = array(
				'action' => 'get_orders',
		);

		$data = array_merge($data, $parameters);
		$response = $this->sendRequest($data);

		if (!is_array($response['orders'])) {
			throw new ShopgateLibraryException(ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE, '"orders" is not an array. Response: '.var_export($response, true));
		}

		$orders = array();
		foreach ($response['orders'] as $order) {
			$orders[] = new ShopgateOrder($order);
		}

		$smaResponse = new ShopgateMerchantApiResponse($response);
		$smaResponse->setData($orders);

		return $smaResponse;
	}

	public function addOrderDeliveryNote($orderNumber, $shippingServiceId, $trackingNumber, $markAsCompleted = false) {
		$data = array(
				'action' => 'add_order_delivery_note',
				'order_number' => $orderNumber,
				'shipping_service_id' => $shippingServiceId,
				'tracking_number' => (string) $trackingNumber,
				'mark_as_completed' => $markAsCompleted,
		);

		$response = $this->sendRequest($data);
		$smaResponse = new ShopgateMerchantApiResponse($response);

		return $smaResponse;
	}

	public function setOrderShippingCompleted($orderNumber) {
		$data = array(
				'action' => 'set_order_shipping_completed',
				'order_number' => $orderNumber,
		);

		$response = $this->sendRequest($data);
		$smaResponse = new ShopgateMerchantApiResponse($response);

		return $smaResponse;
	}
	
	public function cancelOrder($orderNumber, $cancelCompleteOrder = false, $cancellationItems = array(), $cancelShipping = false, $cancellationNote = '') {
		$data = array(
				'action' => 'cancel_order',
				'order_number' => $orderNumber,
				'cancel_complete_order' => $cancelCompleteOrder,
				'cancellation_items' => $cancellationItems,
				'cancel_shipping' => $cancelShipping,
				'cancellation_note' => $cancellationNote,
		);

		$response = $this->sendRequest($data);
		$smaResponse = new ShopgateMerchantApiResponse($response);

		return $smaResponse;
	}
	
	public function getMobileRedirectKeywords(){
		// Set timeout to 1 second
		$this->curlOpt[CURLOPT_TIMEOUT] = 1;
		
		$data = array(
				'action' => 'get_mobile_redirect_keywords',
		);

		$response = $this->sendRequest($data);

		return $response;
	}

	######################################################################
	## Items                                                            ##
	######################################################################
	/**
	 *
	 * @param mixed[] $data
	 * @return ShopgateMerchantApiResponse
	 * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an error occured.
	 * @see http://wiki.shopgate.com/Shopgate_Merchant_API_get_items/de
	 */
	public function getItems($data) {
		$data['action'] = 'get_items';

		$response = $this->sendRequest($data);
		$smaResponse = new ShopgateMerchantApiResponse($response);

		if (!is_array($response['items'])) {
			throw new ShopgateLibraryException(ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE, '"items" is not an array. Response: '.var_export($response, true));
		}

		$items = array();
		foreach ($response['items'] as $_item) {
			$items[] = new ShopgateItem($_item);
		}

		$smaResponse->setData($items);
		return $smaResponse;
	}

	public function addItem($data) {
		$data['action'] = 'add_item';

		$response = $this->sendRequest($data);
		$smaResponse = new ShopgateMerchantApiResponse($response);
		return $smaResponse;
	}

	public function updateItem($data) {
		$data['action'] = 'update_item';

		$response = $this->sendRequest($data);
		$smaResponse = new ShopgateMerchantApiResponse($response);
		return $smaResponse;
	}

	public function deleteItem($item_number) {
		$data = array(
				'item_number' => $item_number,
				'action' => 'delete_item',
		);

		$response = $this->sendRequest($data);
		$smaResponse = new ShopgateMerchantApiResponse($response);
		return $smaResponse;
	}

	######################################################################
	## Categories                                                       ##
	######################################################################
	public function getCategories($data) {
		$data['action'] = 'get_categories';

		$response = $this->sendRequest($data);
		$smaResponse = new ShopgateMerchantApiResponse($response);

		if (!is_array($response['categories'])) {
			throw new ShopgateLibraryException(ShopgateLibraryException::MERCHANT_API_INVALID_RESPONSE, '"categories" is not an array. Response: '.var_export($response, true));
		}

		$categories = array();
		foreach ($response['categories'] as $category) {
			$categories[] = new ShopgateCategory($category);
		}
		$smaResponse->setData($categories);

		return $smaResponse;
	}

	public function addCategory($data) {
		if ($data instanceof ShopgateCategory) {
			$data = $data->toArray();
		}
		$data['action'] = 'add_category';

		$response = $this->sendRequest($data);
		$smaResponse = new ShopgateMerchantApiResponse($response);

		return $smaResponse;
	}

	public function updateCategory($data) {
		if ($data instanceof ShopgateCategory) {
			$data = $data->toArray();
		}
		$data['action'] = 'update_category';

		$response = $this->sendRequest($data);
		$smaResponse = new ShopgateMerchantApiResponse($response);

		return $smaResponse;
	}

	public function deleteCategory($category_number, $delete_subcategories = false, $delete_items = false) {
		$data = array(
				'action' => 'delete_category',
				'category_number' => $category_number,
				'delete_subcategories' => $delete_subcategories ? 1 : 0,
				'delete_items' => $delete_items ? 1 : 0,
		);

		$response = $this->sendRequest($data);
		$smaResponse = new ShopgateMerchantApiResponse($response);

		return $smaResponse;
	}

	public function addItemToCategory($item_number, $category_number, $order_index = null) {
		$data = array();
		$data['action'] = 'add_item_to_category';
		$data['category_number'] = $category_number;
		$data['item_number'] = $item_number;

		if (isset($order_index))
			$data['order_index'] = $order_index;

		$response = $this->sendRequest($data);
		$smaResponse = new ShopgateMerchantApiResponse($response);

		return $smaResponse;
	}

	public function deleteItemFromCategory($item_number, $category_number) {
		$data = array();
		$data['action'] = 'delete_item_from_category';
		$data['category_number'] = $category_number;
		$data['item_number'] = $item_number;

		$response = $this->sendRequest($data);
		$smaResponse = new ShopgateMerchantApiResponse($response);

		return $smaResponse;
	}
}

class ShopgateAuthentificationService extends ShopgateObject implements ShopgateAuthentificationServiceInterface {
	private $customerNumber;
	private $apiKey;
	private $timestamp;
	
	public function __construct($customerNumber, $apiKey) {
		$this->customerNumber = $customerNumber;
		$this->apiKey = $apiKey;
		
		$this->startNewSession();
	}
	
	public function startNewSession() {
		$this->timestamp = time();
	}
	
	public function buildAuthUser() {
		return $this->customerNumber.'-'.$this->getTimestamp();
	}
	
	public function buildAuthUserHeader() {
		return self::HEADER_X_SHOPGATE_AUTH_USER .': '. $this->buildAuthUser();
	}
	
	public function buildAuthToken($prefix = 'SMA') {
		return $this->buildCustomAuthToken($prefix, $this->customerNumber, $this->getTimestamp(), $this->apiKey);
	}
	
	public function buildAuthTokenHeader($prefix = 'SMA') {
		return self::HEADER_X_SHOPGATE_AUTH_TOKEN.': '.$this->buildAuthToken();
	}
	
	public function buildMerchantApiAuthTokenHeader() {
		return $this->buildAuthTokenHeader('SMA');
	}
	
	public function buildPluginApiAuthTokenHeader() {
		return $this->buildAuthTokenHeader('SPA');
	}

	public function checkAuthentification() {
		if(defined('SHOPGATE_DEBUG') && SHOPGATE_DEBUG === 1) return;

		if (empty($_SERVER[self::PHP_X_SHOPGATE_AUTH_USER]) || empty($_SERVER[self::PHP_X_SHOPGATE_AUTH_TOKEN])){
			throw new ShopgateLibraryException(ShopgateLibraryException::AUTHENTICATION_FAILED, 'No authentication data present.');
		}

		// for convenience
		$name = $_SERVER[self::PHP_X_SHOPGATE_AUTH_USER];
		$token = $_SERVER[self::PHP_X_SHOPGATE_AUTH_TOKEN];

		// extract customer number and timestamp from username
		$matches = array();
		if (!preg_match('/(?P<customer_number>[1-9][0-9]+)-(?P<timestamp>[1-9][0-9]+)/', $name, $matches)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::AUTHENTICATION_FAILED, 'Cannot parse: '.$name.'.');
		}

		// for convenience
		$customer_number = $matches['customer_number'];
		$timestamp = $matches['timestamp'];

		// request shouldn't be older than 30 minutes or more than 30 minutes in the future
		if (abs($this->getTimestamp() - $timestamp) > (30*60)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::AUTHENTICATION_FAILED, 'Request too old or too far in the future.');
		}
		
		// create the authentification-password
		$generatedPassword = $this->buildCustomAuthToken('SPA', $customer_number, $timestamp, $this->apiKey);

		// compare customer-number and auth-password and make sure, the API key was set in the configuration
		if (($customer_number != $this->customerNumber) || ($token != $generatedPassword) || (empty($this->apiKey))) {
			throw new ShopgateLibraryException(ShopgateLibraryException::AUTHENTICATION_FAILED, 'Invalid authentication data.');
		}
	}
	
	/**
	 * Return current timestamp
	 *
	 * @return int
	 */
	protected function getTimestamp() {
		return $this->timestamp;
	}
	
	/**
	 * Generates the auth token with the given parameters.
	 *
	 * @param string $prefix
	 * @param string $customerNumber
	 * @param int $timestamp
	 * @param string $apiKey
	 */
	protected function buildCustomAuthToken($prefix, $customerNumber, $timestamp, $apiKey) {
		return sha1("{$prefix}-{$customerNumber}-{$timestamp}-{$apiKey}");
	}
}

/**
 * Wrapper for responses by the Shopgate Plugin API.
 *
 * Each content type is represented by a subclass.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
abstract class ShopgatePluginApiResponse extends ShopgateObject {
	protected $error;
	protected $error_text;
	protected $trace_id;
	protected $version;
	protected $pluginVersion;
	protected $data;
	
	public function __construct($traceId, $version = SHOPGATE_LIBRARY_VERSION, $pluginVersion = null) {
		$this->error = 0;
		$this->error_text = null;
		$this->trace_id = $traceId;
		$this->version = $version;
		$this->pluginVersion = (empty($pluginVersion) && defined('SHOPGATE_PLUGIN_VERSION')) ? SHOPGATE_PLUGIN_VERSION : $pluginVersion;
	}
	
	/**
	 * Marks the response as error.
	 */
	public function markError($code, $message) {
		$this->error = $code;
		$this->error_text = $message;
	}
	
	public function setData($data) {
		$this->data = $data;
	}
	
	abstract public function send();
}

/**
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
class ShopgatePluginApiResponseTextPlain extends ShopgatePluginApiResponse {
	public function send() {
		header('HTTP/1.0 200 OK');
		header('Content-Type: text/plain; charset=UTF-8');
		echo $this->data;
		exit;
	}
}

/**
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
class ShopgatePluginApiResponseTextCsv extends ShopgatePluginApiResponse {
	public function setData($data) {
		if (!file_exists($data)) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_FILE_NOT_FOUND, 'File: '.$data);
		}
		
		$this->data = $data;
	}
	
	public function send() {
		$fp = @fopen($this->data, 'r');
		if (!$fp) {
			throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_FILE_OPEN_ERROR, 'File: '.$this->data);
		}
		
		// output headers ...
		header('HTTP/1.0 200 OK');
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="'.basename($this->data).'"');
		
		// ... and csv file
		while ($line = fgets($fp)) echo $line;
		
		// clean up and leave
		fclose($fp);
		exit;
	}
}

/**
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
class ShopgatePluginApiResponseAppJson extends ShopgatePluginApiResponse {
	public function send() {
		$data = array();
		$data['error'] = $this->error;
		$data['error_text'] = $this->error_text;
		$data['trace_id'] = $this->trace_id;
		$data['version'] = $this->version;
		if (!empty($this->pluginVersion)) {
			$data['plugin_version'] = $this->pluginVersion;
		}
		$this->data = array_merge($data, $this->data);
		
		header("HTTP/1.0 200 OK");
		header("Content-Type: application/json");
		echo $this->jsonEncode($this->data);
	}
}

/**
 * Shopgate Responsecontainer for MerchantApi requests
 *
 * Use the getData()-Function to get the received Data.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
class ShopgateMerchantApiResponse extends ShopgateContainer {
	protected $sma_version = null;
	protected $trace_id = null;
	protected $limit = 1;
	protected $offset = 1;
	protected $has_more_results = false;

	protected $data = null;

	/**
	 *
	 * @param $sma_version
	 */
	protected function setSmaVersion($sma_version) {
		$this->sma_version = $sma_version;
	}

	/**
	 *
	 * @param $trace_id
	 */
	protected function setTraceId($trace_id) {
		$this->trace_id = $trace_id;
	}

	/**
	 *
	 * @param $limit
	 */
	protected function setLimit($limit) {
		$this->limit = $limit;
	}

	/**
	 *
	 * @param $offset
	 */
	protected function setOffset($offset) {
		$this->offset = $offset;
	}

	/**
	 *
	 * @param $has_more_results
	 */
	protected function setHasMoreResults($has_more_results) {
		$this->has_more_results = $has_more_results;
	}

	/**
	 *
	 * @param $data
	 */
	public function setData($data) {
		$this->data = $data;
	}

	/**
	 * The Shopgate-Merchant-API-Version (SMA-Version)
	 *
	 * If Shopgate released a new API-Version the Version-Number increased
	 *
	 * @return string
	 */
	public function getSmaVersion() {
		return $this->sma_version;
	}

	/**
	 * The Trace-ID for the currend Request
	 *
	 * On Errors it will helb to find them
	 *
	 * @return string
	 */
	public function getTraceId() {
		return $this->trace_id;
	}

	/**
	 * The limit of the request
	 *
	 * @return integer
	 */
	public function getLimit() {
		return $this->limit;
	}

	/**
	 * The offset of the request
	 *
	 * @return integer
	 */
	public function getOffset() {
		return $this->offset;
	}

	/**
	 * Are there more results to fetch from Shopgate
	 *
	 * @return boolean
	 */
	public function getHasMoreResults() {
		return $this->has_more_results;
	}

	/**
	 * The received data
	 *
	 * @return ShopgateContainer|mixed
	 */
	public function getData() {
		return $this->data;
	}
	
	public function accept(ShopgateContainerVisitor $v) {
		$v->visit($this);
	}
}

/**
 * This interface represents the Shopgate Plugin API as described in our wiki.
 *
 * It provides all available actions and calls the plugin implementation's callback methods for data retrieval if necessary.
 *
 * @see http://wiki.shopgate.com/Shopgate_Plugin_API/de
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
interface ShopgatePluginApiInterface {
	/**
	 * Inspects an incoming request, performs the requested actions, prepares and prints out the response to the requesting entity.
	 *
	 * Note that the method usually returns true or false on completion, depending on the success of the operation. However, some actions such as
	 * the get_*_csv actions, might stop the script after execution to prevent invalid data being appended to the output.
	 *
	 * @param mixed[] $data The incoming request's parameters.
	 * @return bool false if an error occured, otherwise true.
	 */
	public function handleRequest(array $data = array());
}

/**
 * This class represents the Shopgate Merchant API as described in our wiki.
 *
 * It provides all available actions, calls to the configured API, retrieves, parses and formats the data.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
interface ShopgateMerchantApiInterface {
	/**
	 * Represents the "get_mobile_redirect_keywords" action.
	 *
	 * @return array 'keywords' => string[], 'skipKeywords' => string[]
	 * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an error occured.
	 * @see http://wiki.shopgate.com/Shopgate_Merchant_API_get_mobile_redirect_keywords/de
	 */
	public function getMobileRedirectKeywords();

	/**
	 * Represents the "get_orders" action.
	 *
	 * @param mixed[] $parameters
	 * @return ShopgateMerchantApiResponse
	 * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an error occured.
	 *
	 * @see http://wiki.shopgate.com/Shopgate_Merchant_API_get_orders/de
	 */
	public function getOrders($parameters);

	/**
	 * Represents the "add_order_delivery_note" action.
	 *
	 * @param string $orderNumber
	 * @param string $shippingServiceId
	 * @param int $trackingNumber
	 * @param bool $markAsCompleted
	 * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an error occured.
	 *
	 * @see http://wiki.shopgate.com/Shopgate_Merchant_API_add_order_delivery_note/de
	 */
	public function addOrderDeliveryNote($orderNumber, $shippingServiceId, $trackingNumber, $markAsCompleted = false);

	/**
	 * Represents the "set_order_shipping_completed" action.
	 *
	 * @param string $orderNumber
	 * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an error occured.
	 *
	 * @see http://wiki.shopgate.com/Shopgate_Merchant_API_set_order_shipping_completed/de
	 */
	public function setOrderShippingCompleted($orderNumber);

	/**
	 * Represents the "cancel_order" action.
	 *
	 * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an error occured.
	 * @see http://wiki.shopgate.com/Shopgate_Merchant_API_cancel_order/de
	 */
	public function cancelOrder($orderNumber, $cancelCompleteOrder = false, $cancellationItems = array(), $cancelShipping = false, $cancellationNote = '');
	
	/**
	 * Represents the "get_items" action.
	 *
	 * @param mixed[] $data
	 * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an error occured.
	 * @see http://wiki.shopgate.com/Shopgate_Merchant_API_get_items/de
	 */
	public function getItems($data);

	/**
	 * Represents the "add_item" action.
	 *
	 * @param mixed[] $data
	 * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an error occured.
	 * @see http://wiki.shopgate.com/Shopgate_Merchant_API_add_item/de
	 */
	public function addItem($data);

	/**
	 * Represents the "update_item" action.
	 *
	 * @param mixed[] $data
	 * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an error occured.
	 * @see http://wiki.shopgate.com/Shopgate_Merchant_API_update_item/de
	 */
	public function updateItem($data);

	/**
	 * Represents the "delete_item" action.
	 *
	 * @param string $item_number
	 * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an error occured.
	 * @see http://wiki.shopgate.com/Shopgate_Merchant_API_delete_item/de
	 */
	public function deleteItem($item_number);

	/**
	 * Represents the "get_categories" action.
	 *
	 * @param mixed[] $data
	 * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an error occured.
	 * @see http://wiki.shopgate.com/Shopgate_Merchant_API_get_categories/de
	 */
	public function getCategories($data);

	/**
	 * Represents the "add_category" action.
	 *
	 * @param mixed[] $data
	 * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an error occured.
	 * @see http://wiki.shopgate.com/Shopgate_Merchant_API_add_category/de
	 */
	public function addCategory($data);

	/**
	 * Represents the "update_category" action.
	 *
	 * @param mixed[] $data
	 * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an error occured.
	 * @see http://wiki.shopgate.com/Shopgate_Merchant_API_update_category/de
	 */
	public function updateCategory($data);

	/**
	 * Represents the "delete_category" action.
	 *
	 * @param mixed[] $data
	 * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an error occured.
	 * @see http://wiki.shopgate.com/Shopgate_Merchant_API_delete_category/de
	 */
	public function deleteCategory($category_number, $delete_subcategories = false, $delete_items = false);

	/**
	 * Represents the "add_item_to_category" action.
	 *
	 * @param mixed[] $data
	 * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an error occured.
	 * @see http://wiki.shopgate.com/Shopgate_Merchant_API_add_item_to_category/de
	 */
	public function addItemToCategory($item_number, $category_number, $order_index = null);

	/**
	 * Represents the "delete_item_from_category" action.
	 *
	 * @param mixed[] $data
	 * @throws ShopgateLibraryException in case the connection can't be established, the response is invalid or an error occured.
	 * @see http://wiki.shopgate.com/Shopgate_Merchant_API_delete_item_from_category/de
	 */
	public function deleteItemFromCategory($item_number, $category_number);
}

/**
 * This class provides methods to check and generate authentification strings.
 *
 * It is used internally by the Shopgate Library to send requests or check incoming requests.
 *
 * To check authentication on incoming request it accesses the $_SERVER variable which should contain the required X header fields for
 * authentication.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 */
interface ShopgateAuthentificationServiceInterface {
	const HEADER_X_SHOPGATE_AUTH_USER  = 'X-Shopgate-Auth-User';
	const HEADER_X_SHOPGATE_AUTH_TOKEN = 'X-Shopgate-Auth-Token';
	const PHP_X_SHOPGATE_AUTH_USER  = 'HTTP_X_SHOPGATE_AUTH_USER';
	const PHP_X_SHOPGATE_AUTH_TOKEN = 'HTTP_X_SHOPGATE_AUTH_TOKEN';

	/**
	 * @return string The auth user string.
	 */
	public function buildAuthUser();
	
	/**
	 * @return string The X-Shopgate-Auth-User HTTP header for an outgoing request.
	 */
	public function buildAuthUserHeader();
	
	/**
	 * @param $prefix string SMA|SPA
	 * @return string The auth token string.
	 */
	public function buildAuthToken($prefix = 'SMA');

	/**
	 * @param $prefix string SMA|SPA
	 * @return string The X-Shopgate-Auth-Token HTTP header.
	 */
	public function buildAuthTokenHeader($prefix = 'SMA');

	/**
	 * @return string The X-Shopgate-Auth-Token HTTP header for an outgoing request.
	 */
	public function buildMerchantApiAuthTokenHeader();
	
	/**
	 * @return string The X-Shopgate-Auth-Token HTTP header for an incoming request.
	 */
	public function buildPluginApiAuthTokenHeader();
	
	/**
	 * @throws ShopgateLibraryException if authentication fails
	 */
	public function checkAuthentification();

	/**
	 * Start a new Authentication session
	 */
	public function startNewSession();
}