<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


defined('DIR_FS_EXTERNAL') OR define('DIR_FS_EXTERNAL', DIR_FS_CATALOG.'includes/external/');
defined('DIR_FS_LOG') OR define('DIR_FS_LOG', DIR_FS_CATALOG.'log/');
defined('DIR_WS_BASE') OR define('DIR_WS_BASE', '');

// needed functions
require_once(DIR_FS_INC.'html_encoding.php');
if (!function_exists('xtc_date_short')) {
  require_once(DIR_FS_INC.'xtc_date_short.inc.php');
}

// needed classes
require_once(DIR_FS_EXTERNAL.'easycredit/autoload.php');
require_once(DIR_FS_EXTERNAL.'easycredit/classes/EasyCreditProcess.php');

use EasyCredit\Api\ApiClient                 as ecClient;
use EasyCredit\Api\DataMapper                as ecMapper;
use EasyCredit\Config                        as ecConfig;
use EasyCredit\Transfer\PersonData           as ecPerson;
use EasyCredit\Transfer\BillingAddress       as ecBillingAddress;
use EasyCredit\Transfer\DeliveryAddress      as ecDeliveryAddress;
use EasyCredit\Transfer\AdditionalPersonData as ecAdditionalData;
use EasyCredit\Transfer\CartInfoCollection   as ecCartCollection;
use EasyCredit\Transfer\CartInfo             as ecCartItem;
use EasyCredit\Transfer\CallbackUrls         as ecCallbackUrls;
use EasyCredit\Transfer\ArticleIdCollection  as ecArticleIdCollection;
use EasyCredit\Transfer\ArticleId            as ecArticleId;
use EasyCredit\Log\Logger                    as ecLogger;
use EasyCredit\Log\Handler\FileHandler       as ecFileHandler;
use EasyCredit\Transfer\InstallmentPlan;
use EasyCredit\Transfer\TechnicalShopParams;


class easycredit {
  var $code, $title, $description, $enabled;

  function __construct() {
    global $order;

    $this->code = 'easycredit';
    $this->title = MODULE_PAYMENT_EASYCREDIT_TEXT_TITLE;
    $this->description = MODULE_PAYMENT_EASYCREDIT_TEXT_DESCRIPTION;
    $this->info = MODULE_PAYMENT_EASYCREDIT_TEXT_INFO;
    $this->sort_order = defined('MODULE_PAYMENT_EASYCREDIT_SORT_ORDER') ? MODULE_PAYMENT_EASYCREDIT_SORT_ORDER : '';
    $this->enabled = ((defined('MODULE_PAYMENT_EASYCREDIT_STATUS') && MODULE_PAYMENT_EASYCREDIT_STATUS == 'True') ? true : false);
    if ($this->enabled === true) {
      if ((int) MODULE_PAYMENT_EASYCREDIT_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_EASYCREDIT_ORDER_STATUS_ID;
      }
      if ((int) MODULE_PAYMENT_EASYCREDIT_ORDER_STATUS_SUCCESS_ID > 0) {
        $this->order_status_success = MODULE_PAYMENT_EASYCREDIT_ORDER_STATUS_SUCCESS_ID;
      }
      $this->webshopId = MODULE_PAYMENT_EASYCREDIT_SHOP_ID;
      $this->token = MODULE_PAYMENT_EASYCREDIT_SHOP_TOKEN;

      $logLevel = MODULE_PAYMENT_EASYCREDIT_LOG_LEVEL;
      $logPath = DIR_FS_LOG.'mod_easycredit_'.date("Y-m-d").'.log';
      $fileHandler = new ecFileHandler($logPath);
      $logger = new ecLogger($fileHandler, $logLevel);

      $adapter = new \EasyCredit\Http\Adapter\Curl();
      $adapter->setLogger($logger);
    
      $this->config = new ecConfig();
    
      $this->request = new EasyCredit\Http\Request(
        \EasyCredit\Config::EASYCREDIT_API_HOSTNAME,
        \EasyCredit\Config::EASYCREDIT_API_PORT,
        $adapter
      );    

      $this->apiClient = new ecClient(
        $this->webshopId,
        $this->token,
        $this->request,
        new ecMapper()
      );
    
      try {
        $this->ecProcess = EasyCreditProcess::createInstance($this->apiClient);
      } catch (Exception $e) {
        $this->ecProcess = EasyCreditProcess::getInstance();
      }

      if (is_object($order)) {
        $this->update_status();
      }

      if (!defined('POPUP_CONTENT_LINK_PARAMETERS')) {
        define('POPUP_CONTENT_LINK_PARAMETERS', '&KeepThis=true&TB_iframe=true&height=400&width=600');
      }
      if (!defined('POPUP_CONTENT_LINK_CLASS')) {
        define('POPUP_CONTENT_LINK_CLASS', 'thickbox');
      }
      $this->link_parameters = ltrim(defined('TPL_POPUP_CONTENT_LINK_PARAMETERS') ? TPL_POPUP_CONTENT_LINK_PARAMETERS : POPUP_CONTENT_LINK_PARAMETERS, '&');
      $this->link_class = defined('TPL_POPUP_CONTENT_LINK_CLASS') ? TPL_POPUP_CONTENT_LINK_CLASS : POPUP_CONTENT_LINK_CLASS;
    }
  }

  function update_status() {
    global $order;
    
    if ($this->enabled === true
        && (!defined('MODULE_ORDER_TOTAL_EASYCREDIT_FEE_STATUS')
            || MODULE_ORDER_TOTAL_EASYCREDIT_FEE_STATUS == 'false'
            )
        )
    {
      $this->enabled = false;
    }
    
    if ($this->enabled === true
        && $_SESSION['sendto'] !== $_SESSION['billto']
        )
    {
      $this->enabled = false;
    }

    if ($this->enabled === true
        && $order->billing['country']['iso_code_2'] != 'DE'
        )
    {
      $this->enabled = false;
    }

    if ($this->enabled === true
        && ($_SESSION['customers_status']['customers_status_show_price_tax'] != '1'
            || $_SESSION['customers_status']['customers_status_add_tax_ot'] != '0'
            )
        )
    {
      $this->enabled = false;
    }
    
    if ($this->enabled === true) {
      $this->total_amount = $this->calculate_total();
      
      if ($this->total_amount < $this->config->getMinOrderAmount()
          || $this->total_amount > $this->config->getMaxOrderAmount()
          )
      {
        $this->enabled = false;
      }
    }
  }

  function javascript_validation() {
    $js = 'if (payment_value == "' . $this->code . '") {' . "\n" .
          '  if (!document.getElementById("checkout_payment").ec_conditions.checked) {' . "\n" .
          '    error_message = error_message + unescape("' . xtc_js_lang(MODULE_PAYMENT_EASYCREDIT_TEXT_ERROR_CHECKBOX) . '");' . "\n" .
          '    error = 1;' . "\n" .
          '  }' . "\n" .
          '}' . "\n";
    
    return $js;
  }

  function selection() {
    global $order;

    //reset
    $this->ecProcess->getProcessData()->initEmpty();
    $this->ecProcess->getProcessData()->save();
    
    //session
    unset($_SESSION['easycredit']);
    
    $presentment = $this->get_presentment($this->total_amount);    

    return array(
      'id' => $this->code, 
      'module' => $this->title, 
      'description' => $presentment,
    );
  }

  function pre_confirmation_check() {  
    if (!isset($_SESSION['easycredit']['decision'])
        && isset($_SESSION['easycredit']['TbaId'])
        ) 
    {
      $this->ecProcess->getProcessData()->setStatus('SAVED');
      $decision = $this->ecProcess->getDecision();
      $_SESSION['easycredit']['decision'] = $decision->getDecision()->getResult() == 'GRUEN' ? true : false;
    }

    if (isset($_SESSION['easycredit']['decision'])) {
      if ($_SESSION['easycredit']['decision'] === true
          && $this->ecProcess->getProcessData()->getValidUntil()->format('U') > time()
          )
      {
        $FinancingDetails = $this->ecProcess->getFinancingDetails();
        $_SESSION['easycredit']['total_cost'] = $FinancingDetails->getInstallmentPlan()->getAmount();
        $_SESSION['easycredit']['total_interest'] = $FinancingDetails->getInstallmentPlan()->getInterestRate()->getAccruingInterest();
        
        return true;
      } else {
        $this->ecProcess->destroy();
        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
      }
    }
    
    if ((!isset($_POST['ec_conditions']) || $_POST['ec_conditions'] == false) && !isset($_GET['ec_conditions'])) {
      $error = str_replace('\n', '<br />', MODULE_PAYMENT_PAYPALINSTALLMENT_TEXT_ERROR_CHECKBOX);
      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode($error), 'SSL', true, false));
    }

    // load the selected shipping module
    require_once (DIR_WS_CLASSES . 'shipping.php');
    $shipping_modules = new shipping($_SESSION['shipping']);
    
    if (isset($_POST['ec_term'])) {
      $_SESSION['easycredit']['term'] = $_POST['ec_term'];
    }
    $this->payment_redirect();
  }

  function confirmation() {
    $payment_info = $this->get_payment_info();
    
    $confirmation = array(
      'title' => $this->title,
      'fields' => array(
        array(
          'title' => $payment_info,
        ),
      )
    );
    return $confirmation;                          
  }

  function process_button() {
    
    return false;
  }

  function before_process() {
    if (!isset($_SESSION['easycredit']['decision'])
        || $this->ecProcess->getProcessData()->getValidUntil()->format('U') < time()
        ) 
    {
      $this->ecProcess->destroy();
      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
    }
    return false;
  }

  function after_process() {
    global $insert_id;
    
    if ($this->ecProcess->agree() === true) {
      if (isset($this->order_status) && $this->order_status) {
        xtc_db_query("UPDATE ".TABLE_ORDERS_STATUS_HISTORY." SET orders_status_id='".$this->order_status."' WHERE orders_id='".$insert_id."'");
        xtc_db_query("UPDATE ".TABLE_ORDERS." SET orders_status='".$this->order_status_success."' WHERE orders_id='".$insert_id."'");
        
        $FinancingDetails = $this->ecProcess->getFinancingDetails();
        $sql_data_array = array (
          'orders_id' => $insert_id,
          'orders_status_id' => $this->order_status_success,
          'date_added' => 'now()',
          'customer_notified' => 0,
          'comments' => 'Vorgangskennung: '.$this->ecProcess->getProcessData()->getTechnicalTbaId(),
        );
        xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        
        $sql_data_array = array (
          'orders_id' => $insert_id,
          'tbaId' => $this->ecProcess->getProcessData()->getTbaId(),
          'technicalTbaId' => $this->ecProcess->getProcessData()->getTechnicalTbaId(),
        );
        xtc_db_perform('easycredit', $sql_data_array);
      }
    }
    
    $this->ecProcess->destroy();
    unset($_SESSION['easycredit']);
  }
  
  function get_presentment($amount) {
    global $xtPrice;
    
    if ($amount >= $this->config->getMinOrderAmount()
        && $amount <= $this->config->getMaxOrderAmount()
        )
    {
      $modelCalculation = $this->ecProcess->getModelCalculation($amount);
      $results = $modelCalculation->getResults();
    
      $presentment_array = array();
      if (is_array($results) || is_object($results)) {
        foreach ($results as $installmentPlan) {
          $interestRate = $installmentPlan->getInterestRate();
          $paymentSchedule = $installmentPlan->getPaymentSchedule();
      
          $presentment_array[] = array(
            'effective_rate' => $interestRate->getEffectiveInterest(),
            'nominal_rate' => $interestRate->getNominalInterest(),
            'accruing_rate' => $interestRate->getAccruingInterest(),
            'number_of_rates' => $paymentSchedule->getNumberOfRates(),
            'first_rate_date' => $paymentSchedule->getFirstRateDate(),
            'last_rate_date' => $paymentSchedule->getLastRateDate(),
            'total_payment' => $xtPrice->xtcFormat($installmentPlan->getAmount(), true),
            'monthly_payment' => $xtPrice->xtcFormat($paymentSchedule->getAmountOfRate(), true),
            'last_payment' => $xtPrice->xtcFormat($paymentSchedule->getAmountOfLastRate(), true),
          );
        }
      }
      
      $ec_smarty = new Smarty();
      $ec_smarty->assign('presentment', $presentment_array);
      $ec_smarty->assign('language', $_SESSION['language']);
      $ec_smarty->assign('conditions_text', decode_utf8($this->ecProcess->getLegislativeText()->getDataProcessingPaymentPage()));
      $ec_smarty->assign('conditions', '<input type="checkbox" value="ec_conditions" name="ec_conditions" id="ec_conditions" />');
      $presentment = $ec_smarty->fetch(DIR_FS_EXTERNAL.'easycredit/templates/presentment.html');
    
      return $presentment;
    }
  }
  
  function get_presentment_product($amount) {
    global $xtPrice;
    
    if ($amount >= $this->config->getMinOrderAmount()
        && $amount <= $this->config->getMaxOrderAmount()
        )
    {
      $monthlyCosts = $this->apiClient->getBest($amount);
      $calculator_link = sprintf(ecConfig::EXAMPLE_CALCULATION_LINK, $this->webshopId, $amount);
      
      $separator = '?';
      if (strpos($calculator_link, $separator) !== false) {
        $separator = '&';
      }
      $calculator_link .= (($this->link_parameters != '') ? $separator.$this->link_parameters : '');

      $presentment_array = array(
        'link_class' => $this->link_class,
        'monthly_payment' => $xtPrice->xtcFormat($monthlyCosts->getAmountOfRate(), true),
        'calculator_link' => $calculator_link,
      );
      
      $ec_smarty = new Smarty();
      $ec_smarty->assign('presentment', $presentment_array);
      $ec_smarty->assign('language', $_SESSION['language']);
      $presentment = $ec_smarty->fetch(DIR_FS_EXTERNAL.'easycredit/templates/presentment_product.html');
    
      return $presentment;
    }
  }
  
  function payment_redirect() {
    global $order;
    
    $this->total_amount = $this->calculate_total();

    //basedata
    $this->ecProcess->getProcessData()->setOrderTotal(floatval($this->total_amount));
    $this->ecProcess->getProcessData()->setTerm(intval($_SESSION['easycredit']['term']));

    //Personal Data
    $ecCustomer = $this->ecProcess->getProcessData()->getCustomer();
    $ecPerson = $this->ecProcess->getProcessData()->getCustomer()->getPersonData();
    $ecPerson->setFirstName($this->data_encoding($order->billing['firstname']));
    $ecPerson->setLastName($this->data_encoding($order->billing['lastname']));
    $ecPerson->setSalutation(($order->billing['gender'] == 'm') ? ecPerson::SALUTATION_MR : ecPerson::SALUTATION_MRS);
    $ecCustomer->setPersonData($ecPerson);

    //contact data
    $ecCustomer->getContact()->setEmail($order->customer['email_address']);
    $ecCustomer->getContact()->setMobilphoneVerify(false);
    $this->ecProcess->getProcessData()->setCustomer($ecCustomer);

    //Billing Address
    $ecBilling = new ecBillingAddress();
    $ecBilling->setStreet($this->data_encoding($order->billing['street_address']));
    $ecBilling->setAddressAdditional($this->data_encoding($order->billing['suburb']));
    $ecBilling->setZip($this->data_encoding($order->billing['postcode']));
    $ecBilling->setCity($this->data_encoding($order->billing['city']));
    $ecBilling->setCountryCode($order->billing['country']['iso_code_2']);
    $this->ecProcess->getProcessData()->setBillingAddress($ecBilling);

    //alternative Delivery Address if present
    $ecDelivery = new ecDeliveryAddress();
    $ecDelivery->setFirstName($this->data_encoding($order->delivery['firstname']));
    $ecDelivery->setLastName($this->data_encoding($order->delivery['lastname']));
    $ecDelivery->setStreet($this->data_encoding($order->delivery['street_address']));
    $ecDelivery->setAddressAdditional($this->data_encoding($order->delivery['suburb']));
    $ecDelivery->setZip($this->data_encoding($order->delivery['postcode']));
    $ecDelivery->setCity($this->data_encoding($order->delivery['city']));
    $ecDelivery->setCountryCode($order->delivery['country']['iso_code_2']);
    $this->ecProcess->getProcessData()->setDeliveryAddress($ecDelivery);

    //Risk related info
    $riskRelatedInfo = new \EasyCredit\Transfer\RiskRelatedInfo();
    $riskRelatedInfo->setNegativePaymentInformation(
      \EasyCredit\Transfer\RiskRelatedInfo::NEGATIVE_PAYMENT_INFORMATION_NO_INFORMATION
    );

    $riskRelatedInfo->setCustomerRegistrated(false);
    if ($_SESSION['account_type'] == '0') {
      $riskRelatedInfo->setCustomerRegistrated(true);
      
      $check_query = xtc_db_query("SELECT c.customers_date_added,
                                          count(o.orders_id) as total
                                     FROM ".TABLE_CUSTOMERS." c
                                     JOIN ".TABLE_ORDERS." o
                                          ON o.customers_id = c.customers_id
                                    WHERE c.customers_id = '".(int)$_SESSION['customer_id']."'");
      $check = xtc_db_fetch_array($check_query);
      
      $customerRegistratedAt = new \DateTime(date('Y-m-d'));
      if (strtotime($check['customers_date_added']) > 0) {
        $customerRegistratedAt = new \DateTime($check['customers_date_added']);
      }
      $riskRelatedInfo->setCustomerRegistrationDate($customerRegistratedAt);
      $riskRelatedInfo->setOrderCount($check['total']);
    }
    $riskRelatedInfo->setRiskItemInCart(false);
    $riskRelatedInfo->setCartItemsCount(count($order->products));
    $this->ecProcess->getProcessData()->setRiskInfo($riskRelatedInfo);

    //shopping cart
    $ecCartCollection = new ecCartCollection();
    for ($i = 0, $n = sizeof($order->products); $i < $n; $i ++) {
      $ecCartItem = new ecCartItem();
      $ecCartItem->setName($this->data_encoding($order->products[$i]['name']));
      $ecCartItem->setQuantity((int)$order->products[$i]['quantity']);
      $ecCartItem->setPrice(floatval($order->products[$i]['final_price']));

      $skuCollection = new ecArticleIdCollection();
      $sku = new ecArticleId();

      $sku->setType(ecArticleId::TYPE_SKU);
      $sku->setId($order->products[$i]['id']);
      $skuCollection->addItem($sku);

      $ecCartItem->setArticleId($skuCollection);
      //$ecCartItem->setManufacture($item->Artikel->cHersteller);

      $ecCartCollection->addItem($ecCartItem);
    }
    $this->ecProcess->getProcessData()->setProducts($ecCartCollection);
    
    //callback
    $callbackUrls = new ecCallbackUrls();
    $callbackUrls->setUrlSucceeded($this->link_encoding(xtc_href_link(FILENAME_CHECKOUT_CONFIRMATION, 'conditions=true&ec_conditions=true', 'SSL')));
    $callbackUrls->setUrlCancelled($this->link_encoding(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL')));
    $callbackUrls->setUrlDenied($this->link_encoding(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL')));
    $this->ecProcess->getProcessData()->setCallbackUrls($callbackUrls);

    $this->ecProcess->getProcessData()->save();

    $initialize = true;
    $processData = $this->ecProcess->getProcessData();
    if ($processData->getStatus() === \EasyCredit\Process\Status::NONE) {
      try {
        $TechnicalShopParams = new TechnicalShopParams();
        $TechnicalShopParams->setShopPlatformManufacturer('modified eCommerce');
        $TechnicalShopParams->setShopPlatformModuleVersion('1.0');

        $initialize = $this->ecProcess->initialize(
          \EasyCredit\Transfer\ProcessInitialize::INTEGRATION_TYPE_PAYMENT_PAGE,
          $TechnicalShopParams
        );
      } catch (\EasyCredit\Process\Exception\InvalidTransitionException $e) {
        $initialize = false;
      }
    }

    if ($initialize) {
      $response = $this->ecProcess->getVerificationSnipped()->getHtml();
    } else {
      $response = array('valid' => false, 'messages' => $processData->getMessages());
    }

    if ($_SESSION['easycredit']['TbaId'] = $this->ecProcess->getProcessData()->getTbaId()) {
      $redirect = sprintf(ecConfig::PAYMENT_PAGE_URL, $_SESSION['easycredit']['TbaId']);
    } else {
      $error = array();
      foreach ($processData->getMessages() as $key => $message) {
        $error[] = $message;
      }
      $redirect = xtc_href_link(FILENAME_CHECKOUT_PAYMENT, ((count($error) > 0) ? 'error_message=' . urlencode(encode_htmlentities(decode_utf8(implode('<br/>', $error)))) : 'payment_error='.$this->code), 'SSL', true, false);
    }
    
    xtc_redirect($redirect);
  }

  function link_encoding($string) {
    $string = str_replace('&amp;', '&', $string);
    
    return $string;
  }

  function data_encoding($string) {
    $string = decode_htmlentities($string);
    $cur_encoding = mb_detect_encoding($string);
    if ($cur_encoding == "UTF-8" && mb_check_encoding($string, "UTF-8")) {
      return $string;
    } else {
      return mb_convert_encoding($string, "UTF-8", $_SESSION['language_charset']);
    }
  }

  function calculate_total() {
    global $order;
    
    $order_backup = $order;
    
    if (isset($_SESSION['shipping'])) {
      if (!class_exists('shipping')) {
        require_once (DIR_WS_CLASSES . 'shipping.php');
      }
      $shipping_modules = new shipping($_SESSION['shipping']);
    }
    
    if (!class_exists('order')) {
      require_once (DIR_WS_CLASSES . 'order.php');
    }
    $order = new order();
    
    if (!class_exists('order_total')) {
      require_once (DIR_WS_CLASSES . 'order_total.php');
    }
    $order_total_modules = new order_total();
    $order_total = $order_total_modules->process();
    
    $total = $order->info['total'];

    $order = $order_backup;
    
    return $total;
  }

  function get_payment_info() {    
    $CommonProcessData = $this->ecProcess->getCommonProcessData();
    $ContractInfoURL = $CommonProcessData->getCommonProcessData()->getContractInfoURL();
    
    $separator = '?';
    if (strpos($ContractInfoURL, $separator) !== false) {
      $separator = '&';
    }
    $ContractInfoURL .= (($this->link_parameters != '') ? $separator.$this->link_parameters : '');
    
    $string = $CommonProcessData->getPaymentPlanText();
        
    $text_array = array();
    $array = explode(').', $string);

    foreach ($array as $text) {
      if (trim($text) != '') {
        $part1 = explode(':', $text, 2);
        $text_array[] = trim($part1[0]);
  
        $part2 = explode('),', $part1[1], 2);
        $part3 = explode(',', trim($part2[0]).')', 2);
  
        $text_array[] = $part3[0];
    
        $part4 = explode('(', trim($part3[1]));
        $text_array[] = trim($part4[0]);
        $text_array[] = '('.trim($part4[1]);

        $part5 = explode('(', trim($part2[1]));
        $text_array[] = trim($part5[0]);
        $text_array[] = '('.trim($part5[1]).')';
      }
    }

    if (count($text_array) == 12) {
      $text_array = array_chunk($text_array, 6);
  
      $module_content = array();
      foreach ($text_array as $text) {
        $module_content[] = array(
          'title' => $text[0],
          'number_of_rates' => $text[1],
          'monthly_payment' => $text[2],
          'monthly_plan' => $text[3],
          'last_payment' => $text[4],
          'last_plan' => $text[5],
        );
      }
      
      $ec_smarty = new Smarty();
      $ec_smarty->assign('module_content', $module_content);
      $ec_smarty->assign('contract_info_url', $ContractInfoURL);
      $ec_smarty->assign('link_class', $this->link_class);
      $ec_smarty->assign('language', $_SESSION['language']);
      $string = $ec_smarty->fetch(DIR_FS_EXTERNAL.'easycredit/templates/payment_info.html');
    } else {
      $string .= '<br><br><a class="'.$this->link_class.'" href="'.$ContractInfoURL.'">'.MODULE_PAYMENT_EASYCREDIT_TEXT_LEGAL.'</a>';
    }
    
    return $string;
  }

  function get_error() {
    if (isset($_GET['payment_error'])) {
      $error = array(
        'title' => MODULE_PAYMENT_EASYCREDIT_TEXT_ERROR_HEADING,
        'error' => MODULE_PAYMENT_EASYCREDIT_TEXT_ERROR_MESSAGE,
      );
      return $error;
    }
  }

  function check() {
    if (!isset ($this->_check)) {
      $check_query = xtc_db_query("select configuration_value from ".TABLE_CONFIGURATION." where configuration_key = 'MODULE_PAYMENT_EASYCREDIT_STATUS'");
      $this->_check = xtc_db_num_rows($check_query);
    }
    return $this->_check;
  }

  function install() {
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_EASYCREDIT_STATUS', 'True', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_EASYCREDIT_ALLOWED', 'DE', '6', '0', now())");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_PAYMENT_EASYCREDIT_ZONE', '0', '6', '2', 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(', now())");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_EASYCREDIT_SORT_ORDER', '0',  '6', '0', now())");
    
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_EASYCREDIT_ORDER_STATUS_SUCCESS_ID', '".DEFAULT_ORDERS_STATUS_ID."', '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_EASYCREDIT_ORDER_STATUS_ID', '".DEFAULT_ORDERS_STATUS_ID."', '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
    
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_EASYCREDIT_SHOP_ID', '', '6', '0', now())");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_EASYCREDIT_SHOP_TOKEN', '', '6', '0', now())");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_EASYCREDIT_LOG_LEVEL', 'error', '6', '1', 'xtc_cfg_select_option(array(\'debug\', \'error\'), ', now())");
    
    xtc_db_query("CREATE TABLE IF NOT EXISTS `easycredit` (
                  `orders_id` INT( 11 ) NOT NULL ,
                  `tbaId` VARCHAR( 512 ) NOT NULL ,
                  `technicalTbaId` VARCHAR( 512 ) NOT NULL ,
                  PRIMARY KEY ( `orders_id` )
                  )");
                  
    include_once(DIR_FS_LANGUAGES.$_SESSION['language'].'/modules/order_total/ot_easycredit_fee.php');
    require_once(DIR_FS_CATALOG.'includes/modules/order_total/ot_easycredit_fee.php');
    $ot_easycredit_fee = new ot_easycredit_fee();
    if ($ot_easycredit_fee->check() != 1) {
      $ot_easycredit_fee->install();
    }
  }

  function remove() {
    xtc_db_query("delete from ".TABLE_CONFIGURATION." where configuration_key LIKE ('MODULE_PAYMENT_EASYCREDIT_%')");
  }

  function keys() {
    return array(
      'MODULE_PAYMENT_EASYCREDIT_STATUS', 
      'MODULE_PAYMENT_EASYCREDIT_SHOP_ID',
      'MODULE_PAYMENT_EASYCREDIT_SHOP_TOKEN',
      'MODULE_PAYMENT_EASYCREDIT_ORDER_STATUS_ID', 
      'MODULE_PAYMENT_EASYCREDIT_ORDER_STATUS_SUCCESS_ID', 
      'MODULE_PAYMENT_EASYCREDIT_SORT_ORDER',
      'MODULE_PAYMENT_EASYCREDIT_LOG_LEVEL',
    );

  }
}
?>