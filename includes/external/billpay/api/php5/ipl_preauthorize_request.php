<?php

require_once(dirname(__FILE__) . '/ipl_xml_request.php');
require_once(dirname(__FILE__) . '/common/BpyFlightInformation.php');
require_once(dirname(__FILE__) . '/common/BpyTripData.php');
require_once(dirname(__FILE__) . '/common/BpyTripTraveler.php');

/**
 * @author Unknown Artist (support@billpay.de)
 * @copyright Copyright 2010 BillPay GmbH
 * @license commercial
 */
class ipl_preauthorize_request extends ipl_xml_request
{
    private $_customer_details = array();
    private $_shippping_details = array();
    private $_totals = array();
    private $_bank_account = array();
    private $_rate_request_data = array();

    private $_article_data = array();
    private $_order_history_attr = array();
    private $_order_history_data = array();
    private $_company_details = array();

    private $_payment_info_params = array();
    private $_fraud_detection = array();

    private $_preauth_params = array();
    private $_async_capture_params = array();

    private $_trip_data = array();

    private $_payment_type;

    private $bptid;

    private $corrected_street;
    private $corrected_street_no;
    private $corrected_zip;
    private $corrected_city;
    private $corrected_country;

    // parameters needed for auto-capture
    private $account_holder;
    private $account_number;
    private $bank_code;
    private $bank_name;
    private $invoice_reference;
    private $invoice_duedate;
    private $activation_performed;

    private $_terms_accepted = false;
    private $_capture_request_necessary = true;
    private $_expected_days_till_shipping = 0;

    private $standard_information_pdf;
    private $email_attachment_pdf;

    private $payment_info_html;
    private $payment_info_plain;

    // rate payment specific
    private $instalment_count;
    private $duration;
    private $fee_percent;
    private $fee_total;
    private $total_amount;
    private $effective_annual;
    private $nominal_annual;
    private $base_amount;
    private $cart_amount;
    private $surcharge;
    private $interest;
    private $dues = array();

    // pre approved specific
    private $async_amount;
    private $rate_plan_url;
    private $external_redirect_url;
    private $campaign_type;
    private $campaign_display_text;
    private $campaign_display_image_url;

    // parameters needed for prescore
    private $is_prescored = 0;

    // ctr
    function __construct($ipl_request_url, $payment_type)
    {
        $this->_payment_type = $payment_type;
        parent::__construct($ipl_request_url);
    }

    public function setTraceShopType($sShopType)
    {
        $this->aTraceData['shop_type'] = $sShopType;

        return $this;
    }

    public function setTraceShopVersion($sVersion)
    {
        $this->aTraceData['shop_version'] = $sVersion;

        return $this;
    }

    public function setTraceShopDomain($sShopDomain)
    {
        $this->aTraceData['shop_domain'] = $sShopDomain;

        return $this;
    }

    public function setTracePluginVersion($sVersion)
    {
        $this->aTraceData['plugin_version'] = $sVersion;

        return $this;
    }

    protected function getTraceData()
    {
        $aTraceData = parent::getTraceData();

        if (isset($aTraceData['shop_domain']) === false) {
            $aTraceData['shop_domain'] = $_SERVER['SERVER_NAME'];
        }

        $aTraceData['php_version'] = phpversion();
        $aTraceData['os_version'] = @php_uname('a');
        $aTraceData['api_version'] = IPL_CORE_API_VERSION;

        ksort($aTraceData);

        return $aTraceData;
    }

    public function get_terms_accepted()
    {
        return $this->_terms_accepted;
    }

    public function set_terms_accepted($val)
    {
        $this->_terms_accepted = $val;
    }

    public function set_expected_days_till_shipping($val)
    {
        $this->_expected_days_till_shipping = $val;
    }

    public function set_capture_request_necessary($val)
    {
        $this->_capture_request_necessary = $val;
    }

    public function get_expected_days_till_shipping()
    {
        return $this->_expected_days_till_shipping;
    }

    public function get_capture_request_nesessary()
    {
        return $this->_capture_request_necessary;
    }

    public function get_payment_type()
    {
        return $this->_payment_type;
    }

    public function get_status()
    {
        return $this->status;
    }

    public function get_bptid()
    {
        return $this->bptid;
    }

    public function get_corrected_street()
    {
        return $this->corrected_street;
    }

    public function get_corrected_street_no()
    {
        return $this->corrected_street_no;
    }

    public function get_corrected_zip()
    {
        return $this->corrected_zip;
    }

    public function get_corrected_city()
    {
        return $this->corrected_city;
    }

    public function get_corrected_country()
    {
        return $this->corrected_country;
    }

    public function get_account_holder()
    {
        return $this->account_holder;
    }

    public function get_account_number()
    {
        return $this->account_number;
    }

    public function get_bank_code()
    {
        return $this->bank_code;
    }

    public function get_bank_name()
    {
        return $this->bank_name;
    }

    public function get_invoice_reference()
    {
        return $this->invoice_reference;
    }

    public function get_invoice_duedate()
    {
        return $this->invoice_duedate;
    }

    public function get_activation_performed()
    {
        return $this->activation_performed;
    }

    public function get_standard_information_pdf()
    {
        return $this->standard_information_pdf;
    }

    public function get_email_attachment_pdf()
    {
        return $this->email_attachment_pdf;
    }

    public function get_payment_info_html()
    {
        return $this->payment_info_html;
    }

    public function get_payment_info_plain()
    {
        return $this->payment_info_plain;
    }

    public function get_async_amount()
    {
        return $this->async_amount;
    }

    public function get_prepayment_amount()
    {
        return $this->async_amount;
    }

    public function get_external_redirect_url()
    {
        return $this->external_redirect_url;
    }

    public function get_rate_plan_url()
    {
        return $this->rate_plan_url;
    }

    public function get_campaign_type()
    {
        return $this->campaign_type;
    }

    public function get_campaign_display_text()
    {
        return $this->campaign_display_text;
    }

    public function get_campaign_display_image_url()
    {
        return $this->campaign_display_image_url;
    }

    // ------------------ paylater specific ------------------ //
    public function get_instalment_count()
    {
        return $this->instalment_count;
    }

    public function get_duration()
    {
        return $this->duration;
    }

    public function get_fee_percent()
    {
        return $this->fee_percent;
    }

    public function get_fee_total()
    {
        return $this->fee_total;
    }

    public function get_total_amount()
    {
        return $this->total_amount;
    }

    public function get_effective_annual()
    {
        return $this->effective_annual;
    }

    public function get_nominal_annual()
    {
        return $this->nominal_annual;
    }

    /**
     * Returns base value of an order (base order + tax)
     * @return int
     */
    public function get_base_amount()
    {
        return (int)$this->base_amount;
    }

    /**
     * Returns cart value (base order + shipping fee + tax)
     * @return int
     */
    public function get_cart_amount()
    {
        return (int)$this->cart_amount;
    }

    /**
     * Returns interest surcharge (how much TC/PL costs)
     * @return int
     */
    public function get_surcharge()
    {
        return (int)$this->surcharge;
    }

    /**
     * Returns interest rate in 0.01 of percent
     * ie. 100 means 1% interest rate
     * @return int
     */
    public function get_interest()
    {
        return (int)$this->interest;
    }

    public function get_dues()
    {
        return $this->dues;
    }

    public function set_customer_details($customer_id, $customer_type, $salutation, $title,
                                         $first_name, $last_name, $street, $street_no, $address_addition, $zip,
                                         $city, $country, $email, $phone, $cell_phone, $birthday, $language, $ip, $customerGroup)
    {

        $this->_customer_details['customerid'] = $customer_id;
        $this->_customer_details['customertype'] = $customer_type;
        $this->_customer_details['salutation'] = $salutation;
        $this->_customer_details['title'] = $title;
        $this->_customer_details['firstName'] = $first_name;
        $this->_customer_details['lastName'] = $last_name;
        $this->_customer_details['street'] = $street;
        $this->_customer_details['streetNo'] = $street_no;
        $this->_customer_details['addressAddition'] = $address_addition;
        $this->_customer_details['zip'] = $zip;
        $this->_customer_details['city'] = $city;
        $this->_customer_details['country'] = $country;
        $this->_customer_details['email'] = $email;
        $this->_customer_details['phone'] = $phone;
        $this->_customer_details['cellPhone'] = $cell_phone;
        $this->_customer_details['birthday'] = $birthday;
        $this->_customer_details['language'] = $language;
        $this->_customer_details['ip'] = $ip;
        $this->_customer_details['customerGroup'] = $customerGroup;
    }

    public function set_shipping_details($use_billing_address, $salutation = null, $title = null, $first_name = null, $last_name = null,
                                         $street = null, $street_no = null, $address_addition = null, $zip = null, $city = null, $country = null, $phone = null, $cell_phone = null)
    {

        $this->_shippping_details['useBillingAddress'] = $use_billing_address ? '1' : '0';
        $this->_shippping_details['salutation'] = $salutation;
        $this->_shippping_details['title'] = $title;
        $this->_shippping_details['firstName'] = $first_name;
        $this->_shippping_details['lastName'] = $last_name;
        $this->_shippping_details['street'] = $street;
        $this->_shippping_details['streetNo'] = $street_no;
        $this->_shippping_details['addressAddition'] = $address_addition;
        $this->_shippping_details['zip'] = $zip;
        $this->_shippping_details['city'] = $city;
        $this->_shippping_details['country'] = $country;
        $this->_shippping_details['phone'] = $phone;
        $this->_shippping_details['cellPhone'] = $cell_phone;
    }

    public function add_article($articleid, $articlequantity, $articlename, $articledescription,
                                $article_price, $article_price_gross, $articleType = null, $flightInformation = null)
    {
        $article = array();
        $article['articleid'] = $articleid;
        $article['articlequantity'] = $articlequantity;
        $article['articlename'] = $articlename;
        $article['articledescription'] = $articledescription;
        $article['articleprice'] = $article_price;
        $article['articlepricegross'] = $article_price_gross;

        if (empty($articleType) === false) {
            $article['articletype'] = $articleType;
        }

        if (empty($flightInformation) === false) {
            $article['flight_information'] = (array)$flightInformation;
        }

        $this->_article_data[] = $article;
    }

    public function add_order_history_attributes($iMerchantCustomerLimit, $iRepeatCustomer)
    {
        $this->_order_history_attr = array(
            'merchant_customer_limit' => (int)$iMerchantCustomerLimit,
            'repeat_customer' => (int)$iRepeatCustomer,
        );

        return $this;
    }

    public function add_order_history($horderid, $hdate, $hamount, $hcurrency, $hpaymenttype, $hstatus)
    {
        $histOrder = array();
        $histOrder['horderid'] = $horderid;
        $histOrder['hdate'] = $hdate;
        $histOrder['hamount'] = $hamount;
        $histOrder['hcurrency'] = $hcurrency;
        $histOrder['hpaymenttype'] = $hpaymenttype;
        $histOrder['hstatus'] = $hstatus;

        $this->_order_history_data[] = $histOrder;
    }

    public function set_total($rebate, $rebate_gross, $shipping_name, $shipping_price,
                              $shipping_price_gross, $cart_total_price, $cart_total_price_gross,
                              $currency, $reference, $reference2 = "")
    {
        $this->_totals['shippingname'] = $shipping_name;
        $this->_totals['shippingprice'] = $shipping_price;
        $this->_totals['shippingpricegross'] = $shipping_price_gross;
        $this->_totals['rebate'] = $rebate;
        $this->_totals['rebategross'] = $rebate_gross;
        $this->_totals['carttotalprice'] = $cart_total_price;
        $this->_totals['carttotalpricegross'] = $cart_total_price_gross;
        $this->_totals['currency'] = $currency;
        $this->_totals['reference'] = $reference;
        $this->_totals['reference2'] = $reference2;
    }

    public function set_bank_account($account_holder, $account_number, $sort_code)
    {
        $this->_bank_account['accountholder'] = $account_holder;
        $this->_bank_account['accountnumber'] = $account_number;
        $this->_bank_account['sortcode'] = $sort_code;
    }

    public function set_company_details($name, $legalForm, $registerNumber, $holderName, $taxNumber)
    {
        $this->_company_details['name'] = $name;
        $this->_company_details['legalForm'] = $legalForm;
        $this->_company_details['registerNumber'] = $registerNumber;
        $this->_company_details['holderName'] = $holderName;
        $this->_company_details['taxNumber'] = $taxNumber;
    }

    /**
     * Sets rate info for TC and PL.
     *      Usually, term is the same as rate count, so it's not sent
     *      In case of big TC CHF order, rate count is always "four" and we need to set real term
     * @param int $rate_count
     * @param int $total_amount
     * @param int $term (optional) Set, if different than $rate_count.
     */
    public function set_rate_request($rate_count, $total_amount, $term = 0)
    {
        $this->_rate_request_data['ratecount'] = $rate_count;
        $this->_rate_request_data['totalamount'] = $total_amount;
        if ($term) {
            $this->_rate_request_data['term'] = $term;
        }
    }

    public function set_payment_info_params($showhtmlinfo, $showplaininfo)
    {
        $this->_payment_info_params['htmlinfo'] = $showhtmlinfo ? "1" : "0";
        $this->_payment_info_params['plaininfo'] = $showplaininfo ? "1" : "0";
    }

    public function set_fraud_detection($session_id)
    {
        $this->_fraud_detection['session_id'] = $session_id;
    }

    public function set_prescore_enable($is_prescored, $bptid)
    {
        if ($is_prescored == true) {
            $this->is_prescored = 1;
            $this->bptid = $bptid;
            $this->_preauth_params['is_prescored'] = 1;
            $this->_preauth_params['bptid'] = $bptid;
        } else {
            $this->is_prescored = 0;
            $this->_preauth_params['is_prescored'] = 0;
        }
    }

    public function set_async_capture($redirect_url, $notify_url)
    {
        $this->_async_capture_params['redirect_url'] = $redirect_url;
        $this->_async_capture_params['notify_url'] = $notify_url;
    }


    public function set_trip_data(ArrayObject $tripData)
    {
        $this->_trip_data = (array)$tripData;
        return $this;
    }

    protected function _send()
    {
        $attributes = array();
        $attributes['tcaccepted'] = $this->_terms_accepted;
        $attributes['expecteddaystillshipping'] = $this->_expected_days_till_shipping;
        $attributes['capturerequestnecessary'] = $this->_capture_request_necessary;
        $attributes['paymenttype'] = $this->_payment_type;

        return ipl_core_send_preauthorize_request(
            $this->_ipl_request_url,
            $attributes,
            $this->getTraceData(),
            $this->_default_params,
            $this->_preauth_params,
            $this->_customer_details,
            $this->_shippping_details,
            $this->_bank_account,
            $this->_totals,
            $this->_article_data,
            $this->_order_history_attr,
            $this->_order_history_data,
            $this->_rate_request_data,
            $this->_company_details,
            $this->_payment_info_params,
            $this->_fraud_detection,
            $this->_async_capture_params,
            $this->_trip_data
        );
    }

    protected function _process_response_xml($data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    protected function _process_error_response_xml($data)
    {
        if (isset($data['status'])) {
            $this->status = $data['status'];
        }
        if (isset($data['validation_errors'])) {
            $this->_validation_errors = $data['validation_errors'];
        }
    }

    /** HIGHER FUNCTIONS */


    /**
     * Sets expected shipping date.
     * Requires PHP >= 5.3
     * @param DateTime|string $date ie. "2015-12-30" or new DateTime("2015-12-30")
     * @throws Exception
     */
    public function set_shipping_date($date)
    {
        if (is_string($date)) {
            $date = new DateTime($date);
        }
        if (get_class($date) !== 'DateTime') {
            throw new Exception('Method `set_shipping_date` requires DateTime as an argument.');
        }
        $diff = $date->diff(new DateTime('today'));
        $days = $diff->days;
        if ($days < 0) {
            throw new Exception('Method `set_shipping_date` requires DateTime as an argument.');
        }
        $this->set_expected_days_till_shipping($days);
    }
}