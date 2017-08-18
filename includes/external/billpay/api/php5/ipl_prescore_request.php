<?php

require_once(dirname(__FILE__) . '/ipl_xml_request.php');
require_once(dirname(__FILE__) . '/common/BpyFlightInformation.php');
require_once(dirname(__FILE__) . '/common/BpyTripData.php');
require_once(dirname(__FILE__) . '/common/BpyTripTraveler.php');

/**
 * @author    Unknown Artist (support@billpay.de)
 * @copyright Copyright 2010 BillPay GmbH
 * @license   commercial
 */
class ipl_prescore_request extends ipl_xml_request
{
    private $_capture_request_necessary;

    private $_customer_details = array();
    private $_shippping_details = array();
    private $_totals = array();

    private $_article_data = array();
    private $_order_history_attr = array();
    private $_order_history_data = array();
    private $_company_details = array();

    private $_payment_info_params = array();
    private $_fraud_detection = array();

    private $_trip_data = array();

    private $_payment_type;

    private $bptid;

    private $corrected_street;
    private $corrected_street_no;
    private $corrected_zip;
    private $corrected_city;
    private $corrected_country;

    private $_expected_days_till_shipping = 0;

    private $payment_info_html;
    private $payment_info_plain;

    private $_payments_allowed = array();
    private $_rate_info = array();
    private $_payments_allowed_all = array();

    private $_terms = array();

    // ctr
    function __construct($ipl_request_url)
    {
        //$this->_payment_type = $payment_type;
        parent::__construct($ipl_request_url);
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

    public function get_payment_info_html()
    {
        return $this->payment_info_html;
    }

    public function get_payment_info_plain()
    {
        return $this->payment_info_plain;
    }

    public function get_payments_allowed_all()
    {
        return $this->_payments_allowed_all;
    }

    public function get_payments_allowed()
    {
        return $this->_payments_allowed;
    }

    public function get_rate_info()
    {
        return $this->_rate_info;
    }

    public function get_terms()
    {
        return $this->_terms;
    }


    public function set_customer_details(
        $customer_id, $customer_type, $salutation, $title,
        $first_name, $last_name, $street, $street_no, $address_addition, $zip,
        $city, $country, $email, $phone, $cell_phone, $birthday, $language, $ip, $customerGroup
    )
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


    public function set_shipping_details(
        $use_billing_address, $salutation = null, $title = null, $first_name = null, $last_name = null,
        $street = null, $street_no = null, $address_addition = null, $zip = null, $city = null, $country = null,
        $phone = null, $cell_phone = null
    )
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

    public function add_article(
        $articleid, $articlequantity, $articlename, $articledescription,
        $article_price, $article_price_gross, $articleType = null, $flightInformation = null
    )
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


    public function set_total(
        $rebate, $rebate_gross, $shipping_name, $shipping_price,
        $shipping_price_gross, $cart_total_price, $cart_total_price_gross,
        $currency
    )
    {
        $this->_totals['shippingname'] = $shipping_name;
        $this->_totals['shippingprice'] = $shipping_price;
        $this->_totals['shippingpricegross'] = $shipping_price_gross;
        $this->_totals['rebate'] = $rebate;
        $this->_totals['rebategross'] = $rebate_gross;
        $this->_totals['carttotalprice'] = $cart_total_price;
        $this->_totals['carttotalpricegross'] = $cart_total_price_gross;
        $this->_totals['currency'] = $currency;
    }

    public function set_company_details($name, $legalForm, $registerNumber, $holderName, $taxNumber)
    {
        $this->_company_details['name'] = $name;
        $this->_company_details['legalForm'] = $legalForm;
        $this->_company_details['registerNumber'] = $registerNumber;
        $this->_company_details['holderName'] = $holderName;
        $this->_company_details['taxNumber'] = $taxNumber;
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

    public function set_trip_data(ArrayObject $tripData)
    {
        $this->_trip_data = (array)$tripData;
        return $this;
    }

    protected function _send()
    {
        $attributes = array();

        return ipl_core_send_prescore_request(
            $this->_ipl_request_url,
            $attributes,
            $this->getTraceData(),
            $this->_default_params,
            $this->_customer_details,
            $this->_shippping_details,
            $this->_totals,
            $this->_article_data,
            $this->_order_history_attr,
            $this->_order_history_data,
            $this->_company_details,
            $this->_payment_info_params,
            $this->_fraud_detection,
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
