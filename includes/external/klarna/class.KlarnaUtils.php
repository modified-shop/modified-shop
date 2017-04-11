<?php
/**
 * Shared class of utils
 *
 * PHP Version 5.2
 *
 * @category Payment
 * @package  Klarna_Module_XtCommerce
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */

require_once "class.KlarnaCore.php";
require_once "class.KlarnaAddressXtc.php";

/**
 * Klarna Utils Class, containing shared functions.
 *
 * @category Payment
 * @package  Klarna_Module_XtCommerce
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */
class KlarnaUtils
{
    /**
     * @var string
     */
    private $_country;

    /**
     * @var array
     */
    private static $_countryMap = array();

    /**
     * @var array
     */
    private static $_languageMap = array();

    /**
     * @var array
     */
    public static $prefillData;

    /**
     * @var KlarnaDB
     */
    private $_klarnaDB;

    /**
     * The constructor
     *
     * @param string|int $country alpha-2 country code or KlarnaCountry constant.
     *
     * @return void
     */
    public function __construct($country = null)
    {
        if ($country === null) {
            $country = self::getCountryByID(STORE_COUNTRY);
        } else if (is_numeric($country)) {
            $country = KlarnaCountry::getCode($country);
        } else {
            $country = strtolower($country);
        }
        $this->_country = $country;
        $this->_klarnaDB = new XtcKlarnaDB();
    }

    /**
     * Configure KiTT
     *
     * @param string $option payment option
     *
     * @return void
     */
    public static function configureKiTT($option = null)
    {
        // KiTT Configuration
        $mode = KlarnaConstant::mode($option);
        $configuration = array(
            'default' => STORE_COUNTRY,
            'module' => 'modified',
            'version' => KlarnaCore::getCurrentVersion(),
            'api' => array(
                'mode' => ($mode === null) ? Klarna::BETA : $mode,
                'pcStorage' => 'mysql',
                'pcURI' => KlarnaConstant::pcURI()
            ),
            'paths' => array(
                'kitt' => DIR_KLARNA . '/KITT/',
                'lang' => DIR_KLARNA . '/KITT/data/language.xml',
                'extra_templates' => DIR_KLARNA . 'template/',
                'lookup' => DIR_KLARNA . '/KITT/data/lookupTable.json',
                'input' => DIR_KLARNA . '/KITT/data/inputFields.json'
            ),
            'web' => array(
                'root' => self::getWebRoot(),
                'js' => self::getWebRoot() . 'ext/jquery/klarna/',
                'ajax' => self::getWebRoot() . 'klarnaAjax.php'
            ),
            'collapse' => true,
            'selector' => ".moduleRow, .moduleRowSelected, input[name=payment]"
        );

        KiTT::configure($configuration);
        KiTT::setFormatter(new XtcFormatter);
    }

    /**
     * Set an instance of the API.
     *
     * @param Klarna $klarna Klarna API object
     *
     * @return void
     */
    public function setApi($klarna)
    {
        $this->_klarna = $klarna;
    }

    /**
    * Checking for newer version at klarnas website.
    * If a new version is found it outputs information about it as HTML.
    *
    * @return void
    */
    public function checkForLatestVersion()
    {
        $sURL = 'http://static.klarna.com:80/external/msbo/' .
            'xtc304.latestversion.txt';
        $version = KlarnaCore::getCurrentVersion();
        $latest = @file_get_contents($sURL);
        $templateLoader = KiTT::templateLoader(KiTT::Locale($this->_country));
     /*
        if (version_compare($latest, $version, '>')) {
            $latestVersion = $templateLoader->load('newversion.mustache');
            echo $latestVersion->render(
                array(
                    'version' => $version,
                    'latest' => $latest
                )
            );
        }*/
    }

    /**
     * Retrieve the language code chosen/used by the store. Defaults to the
     * store default language.
     *
     * @return string
     */
    public static function getLanguageCode()
    {
        global $lng;
        $languages_id = $_SESSION['languages_id'];

        if (is_array($lng)) {
            foreach ($lng->catalog_languages as $code => $language) {
                if ($language['id'] === $languages_id) {
                    return $code;
                }
            }
        }
        return self::getLanguageByID($languages_id);
    }

    /**
     * Attempt to guess customer country to determine if things should be shown.
     *
     * @param string $option payment option
     *
     * @return string or null
     */
    public static function deduceCountry($option)
    {
        $customer_country_id = (isset($_SESSION['customer_country_id'])?$_SESSION['customer_country_id']:STORE_COUNTRY);
        $currency = $_SESSION['currency'];

        $addr = null;
        if ($customer_country_id !== null) {
            $addr = new KlarnaAddr();
            $addr->setCountry(self::getCountryByID($customer_country_id));
        }

        $lang = self::getLanguageCode();
        self::configureKiTT($option);
        self::configureKlarna($option);

        return KiTT::locator()->locate($currency, $lang, $addr);
    }

    /**
    * Get the WebRoot of the store.
    *
    * @return string   the web root uri.
    */
    public static function getWebRoot()
    {
        global $request_type;
        return (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) .
            DIR_WS_CATALOG;
    }

    /**
    * Get the path to the image dir for the module.
    *
    * @return string   the img root uri.
    */
    public static function getStaticPath()
    {
        return EXTERNAL_KITT . 'res/v1.1/';
    }

    /**
    * Used for error encodings.
    *
    * @param string        $string    string to encode
    * @param boolean|array $translate to translate or not
    * @param boolean       $protected protected or not
    *
    * @return string
    */
    public function klarnaOutputString(
        $string, $translate = false, $protected = false
    ) {
        if ($protected == true) {
            return htmlspecialchars($string);
        }
        if ($translate == false) {
            return xtc_parse_input_field_data(
                $string, array('"' => '&quot;')
            );
        }
        return xtc_parse_input_field_data($string, $translate);
    }

    /**
     * Get language iso-2 code by xtcommerce ID from the database
     *
     * @param int $id language id
     *
     * @return string
     */
    public static function getLanguageByID($id)
    {
        if (isset(self::$_languageMap[$id])) {
            return self::$_languageMap[$id];
        }
        $klarnaDB = new XtcKlarnaDB();
        $query = $klarnaDB->query(
            "SELECT code, languages_id FROM languages"
        );
        while ($res = $query->getArray()) {
            self::$_languageMap[$res['languages_id']] = $res['code'];
        }
        return self::$_languageMap[$id];
    }

    /**
     * Get country iso-2 code by xtcommerce ID from the database
     *
     * @param int $id country id
     *
     * @return string
     */
    public static function getCountryByID($id)
    {
        if (isset(self::$_countryMap[$id])) {
            return self::$_countryMap[$id];
        }
        $klarnaDB = new xtcKlarnaDB();
        $query = $klarnaDB->query(
            "SELECT countries_iso_code_2, countries_id FROM countries"
        );
        while ($res = $query->getArray()) {
            self::$_countryMap[$res['countries_id']] = $res['countries_iso_code_2'];
        }
        return self::$_countryMap[$id];
    }

    /**
    * Get the wanted country.
    * Fallback to getting from database based on $_SESSION variable
    *
    * @param object|array $arg order object/array
    *
    * @return string ISO-3166-1 alpha 2 code
    */
    public static function getCountry($arg)
    {
        if (is_object($arg) && is_array($arg->delivery)) {
            $arg = $arg->delivery;
        }
        if (is_array($arg)) {
            if (is_array($arg['country'])
                && isset($arg['country']['iso_code_2'])
            ) {
                return $arg['country']['iso_code_2'];
            }
            if (!is_array($arg['country'])) {
                return $arg['country'];
            }
        }
        if (isset($arg['country_id'])) {
            return self::getCountryByID($arg['country_id']);
        }
        $customers_id = (int) $_SESSION['customer_id'];
        $address_id = (int) $_SESSION['billto'];
        $klarnaDB = new xtcKlarnaDB();
        $result = $klarnaDB->query(
            "SELECT `entry_country_id` FROM `address_book`
            WHERE `customers_id`={$customers_id} AND
            `address_book_id`={$address_id}"
        )->getArray();

        return self::getCountryByID($result['entry_country_id']);
    }

    /**
     * Get invoice fee for given country.
     *
     * @return int|float invoice fee for given country.
     */
    public function getInvoiceFee()
    {
        $country = strtoupper($this->_country);
        if (MODULE_ORDER_TOTAL_KLARNA_FEE_MODE == 'fixed') {
            if (defined("MODULE_ORDER_TOTAL_KLARNA_FEE_FIXED_{$country}")) {
                return constant("MODULE_ORDER_TOTAL_KLARNA_FEE_FIXED_{$country}");
            }
            return 0;
        }
        if (defined("MODULE_ORDER_TOTAL_KLARNA_FEE_TABLE_{$country}")) {
            $fee_table = constant("MODULE_ORDER_TOTAL_KLARNA_FEE_TABLE_{$country}");
            $table = explode(",", $fee_table);

            $size = sizeof($table);
            $amount = $this->getCartSum();
            foreach ($table as $rule) {
                list($limit, $cost) = explode(":", $rule);
                if ($amount <= $limit) {
                    return $cost;
                }
            }
        }
        return 0;
    }

    /**
     * Clear errors
     *
     * @return void
     */
    public function clearErrors()
    {
        unset($_SESSION['klarna_error']);
    }

    /**
    * Set the Error Message and which box caused it.
    *
    * @param string $errorString error message
    * @param string $errorBox    payment box, invoice, part or spec
    *
    * @return void
    */
    public function setError($errorString, $errorBox)
    {
        $_SESSION['klarna_error']['message'] = addslashes($errorString);
        $_SESSION['klarna_error']['box'] = $errorBox;
    }

    /**
    * Get errors from the Session Variable
    *
    * @return string error message stored in session
    */
    public function getError()
    {
        return htmlentities(
            $_SESSION['klarna_error']['message'], ENT_COMPAT, 'UTF-8'
        );
    }

    /**
    * Get the box where the error occured.
    *
    * @return string payment box that caused the error; invoice, part or spec
    */
    public function getErrorOption()
    {
        return $_SESSION['klarna_error']['box'];
    }

    /**
    * Populate an array with customer information
    *
    * @param object $order osCommerce order object
    *
    * @return array
    */
    public function collectKlarnaData($order)
    {
        $klarna_data = array();

        $klarna_data['phone_number'] = $order->customer['telephone'];
        $klarna_data['email_address'] = $order->customer['email_address'];
        $klarna_data['reference']= $order->delivery['firstname'] . " " .
            $order->delivery['lastname'];

        $address = KiTT_Addresses::splitStreet(
            $order->delivery['street_address'], $this->_country
        );
        $klarna_data = array_merge($klarna_data, $address);

        if (KiTT_CountryLogic::needDateOfBirth($this->_country)) {
            // Get date of birth
            $customer_query = $this->_klarnaDB->query(
                "SELECT DATE_FORMAT(customers_dob, ".
                "'%d%m%Y') AS customers_dob from " .
                TABLE_CUSTOMERS . " where customers_id = '" .
                (int)$_SESSION['customer_id']."'"
            );

            $customer = $customer_query->getArray();
            $dob = $customer['customers_dob'];

            $klarna_data['birth_year'] = substr($dob, 4, 4);
            $klarna_data['birth_month'] = substr($dob, 2, 2);
            $klarna_data['birth_day'] = substr($dob, 0, 2);
        }

        $klarna_data['first_name'] = $order->delivery['firstname'];
        $klarna_data['last_name'] = $order->delivery['lastname'];
        $klarna_data['city'] = $order->delivery['city'];
        $klarna_data['zipcode'] = $order->delivery['postcode'];
        $klarna_data['company_name'] = $order->delivery['company'];
        $klarna_data['gender'] = $order->customer['gender']=='m'?1:0;

        foreach ($klarna_data as $key => $value) {
            $klarna_data[$key] = KiTT_String::encode($value, null, 'UTF-8');
        }

        return $klarna_data;
    }

    /**
    * Get values for the HTML prefilling from the session if we happen to be
    * returning from a failed purchase.
    *
    * @return array values for the HTML prefilling
    */
    public function getValuesFromSession()
    {
        if (isset($_SESSION['klarna_data'])) {
            self::$prefillData = $_SESSION['klarna_data'];
            unset($_SESSION['klarna_data']);
        }

        $fields = array(
            'first_name',
            'last_name',
            'street',
            'city',
            'zipcode',
            'phone_number',
            'company_name',
            'reference',
            'pno',
            'house_extension',
            'house_number',
            'gender',
            'birth_year',
            'birth_month',
            'birth_day'
        );

        $array = array();

        // split the pno to date of birth
        if (array_key_exists('pno', self::$prefillData)) {
            $dob = self::$prefillData['pno'];
            self::$prefillData['birth_year'] = substr($dob, 4, 4);
            self::$prefillData['birth_month'] = substr($dob, 2, 2);
            self::$prefillData['birth_day'] = substr($dob, 0, 2);
        }

        foreach ($fields as $field) {
            if (array_key_exists($field, self::$prefillData)
                && self::$prefillData[$field] !== ""
                && self::$prefillData[$field] !== null
            ) {
                $array[$field] = KiTT_String::encode(
                    self::$prefillData[$field], null, 'UTF-8'
                );
                if ($field === 'gender') {
                    $array['gender'] = KiTT_String::encode(intval(self::$prefillData['gender']), null, 'UTF-8');
                }
            }
        }

        return $array;
    }

    /**
     * Perform the reserve_amount call.
     *
     * @param int    $paymentPlan pclass id
     * @param object $addrs       KlarnaAddr object
     * @param string $option      invoice, part or spec
     *
     * @return void
     */
    public function performReservation($paymentPlan, $addrs, $option)
    {
        global $order;

        $addrHandler = new KlarnaAddressXtc;
        // Fixes potential security problem.
        $order->delivery = array_merge(
            $order->delivery,
            $addrHandler->klarnaAddrToXtcAddr($addrs)
        );

        // $_POST doesn't have phone number anymore, so it won't be
        // properly set by buildXtCommerceAddress
        $order->delivery['telephone'] = $addrs->getTelno();
        $order->billing['telephone'] = $addrs->getTelno();
        $order->customer['telephone'] = $addrs->getTelno();
        $addrs->setEmail($order->customer['email_address']);

        $pno = $_POST['klarna_pno'];

        $reference = KiTT_String::encode($_POST['klarna_reference']);

        if ($_POST["klarna_{$option}_invoice_type"] == 'company'
            || $addrs->isCompany
        ) {
            // Company purchase, set the firstname in osCommerce to the reference
            // So we don't lose it.
            $order->delivery['firstname'] = KiTT_String::decode($reference);
            // set Ref: comment to make sure KO finds it
            $this->_klarna->setComment("Ref: " . $reference);

            // Set First and Last name so KO doesn't complain one is missing.
            $name = explode(' ', $reference, 2);
            $addrs->setFirstName((strlen($name[0]>0)) ? $name[0] : " ");
            if (strlen($name[1]) > 0) {
                $addrs->setLastName($name[1]);
            } else {
                $addrs->setLastName(" ");
            }

            //Set Company to order
            $order->delivery['company'] = KiTT_String::decode(
                $addrs->getCompanyName()
            );
        } else {
            $order->delivery['company'] = '';
        }
        if (strlen($order->info['comments']) > 0) {
            $this->_klarna->addComment(
                KiTT_String::encode($order->info['comments'])
            );
        }
        $this->_klarna->setReference($reference, "");

        $shipping = $addrs;
        $gender = null;
        if (KiTT_CountryLogic::needGender($this->_country)) {
            $gender = $_POST['klarna_gender'];
        }
        if (KiTT_CountryLogic::shippingSameAsBilling($this->_country)) {
            $billing = $shipping;
            $order->billing = $order->delivery;
        } else {
            $billing = $addrHandler->xtcAddressToKlarnaAddr($order->billing);
        }

        try {
            $this->_klarna->setAddress(KlarnaFlags::IS_SHIPPING, $shipping);
            $this->_klarna->setAddress(KlarnaFlags::IS_BILLING, $billing);

            $result = $this->_klarna->reserveAmount(
                $pno,
                $gender,
                -1,
                KlarnaFlags::NO_FLAG,
                $paymentPlan
            );

            $this->_handleResponse($option, $result, $country);

        } catch(KlarnaException $e) {
            if ($e instanceof Klarna_ArgumentNotSetException
                || $e instanceof Klarna_InvalidPNOException
            ) {
                $this->setError(
                    htmlentities(
                        $this->translate('error_title_2'), ENT_COMPAT, 'UTF-8'
                    ),
                    $option
                );
            } else {
                $this->setError(
                    htmlentities($e->getMessage(), ENT_DISALLOWED, 'ISO-8859-1') . " (#" . $e->getCode() . ")",
                    $option
                );
            }

            xtc_redirect(
                $this->errorLink(
                    FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false
                )
            );
        }
    }

    /**
     * Handle the result from the reserveAmount call
     *
     * @param string $option  payment option
     * @param array  $result  response array
     * @param string $country country
     *
     * @return void
     */
    private function _handleResponse($option, $result, $country)
    {
        global $order, $customer_id, $sendto, $billto;

        switch ($option) {
        case KiTT::PART:
            $module = "MODULE_PAYMENT_KLARNA_PARTPAYMENT";
            break;
        case KiTT::SPEC:
            $module = "MODULE_PAYMENT_KLARNA_SPECCAMP";
            break;
        case KiTT::INVOICE:
            $module = "MODULE_PAYMENT_KLARNA_INVOICE";
            break;
        }

        $orderStatusQuery = null;
        $orderStatusId = (int) constant("{$module}_ORDER_STATUS_PENDING_ID");
        if ($result[1] == KlarnaFlags::PENDING && $orderStatusId > 0) {
            $orderStatusQuery = $this->_klarnaDB->query(
                "SELECT orders_status_name FROM " . TABLE_ORDERS_STATUS .
                " WHERE orders_status_id = {$orderStatusId}"
            );
        } else {
            $orderStatusId = (int) constant("{$module}_ORDER_STATUS_ID");
            $orderStatusQuery = $this->_klarnaDB->query(
                "SELECT orders_status_name FROM " . TABLE_ORDERS_STATUS .
                " WHERE orders_status_id = {$orderStatusId}"
            );
        }

        $orderStatus = $orderStatusQuery->getArray();
        $_SESSION['klarna_orderstatus'] = $orderStatus['orders_status_name'];

        // insert address in address book to get correct address in
        // confirmation mail (or fetch correct address from address book
        // if it exists)
        $q = "SELECT countries_id FROM " . TABLE_COUNTRIES .
            " WHERE countries_iso_code_2 = '{$country}'";

        $check_country_query = $this->_klarnaDB->query($q);
        $check_country = $check_country_query->getArray();

        $cid = $check_country['countries_id'];

        $q = "SELECT address_book_id FROM " . TABLE_ADDRESS_BOOK .
            " WHERE customers_id = '" . (int)$customer_id .
            "' AND entry_firstname = '" .
            xtc_db_input($order->delivery['firstname']) .
            "' AND entry_lastname = '" .
            xtc_db_input($order->delivery['lastname']) .
            "' AND entry_street_address = '" .
            xtc_db_input($order->delivery['street_address']) .
            "' AND entry_postcode = '" .
            xtc_db_input($order->delivery['postcode']) .
            "' AND entry_city = '" .
            xtc_db_input($order->delivery['city']) .
            "' AND entry_company = '" .
            xtc_db_input($order->delivery['company']) . "'";
        $check_address_query = $this->_klarnaDB->query($q);
        $check_address = $check_address_query->getArray();

        if (is_array($check_address) && $check_address_query->count() > 0) {
            $sendto = $billto = $check_address['address_book_id'];
        } else {
            $sql_data_array = array(
                'customers_id' => $customer_id,
                'entry_firstname' => $order->delivery['firstname'],
                'entry_lastname' => $order->delivery['lastname'],
                'entry_company' => $order->delivery['company'],
                'entry_street_address' => $order->delivery['street_address'],
                'entry_postcode' => $order->delivery['postcode'],
                'entry_city' => $order->delivery['city'],
                'entry_country_id' => $cid
            );

            xtc_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);
            $sendto = $billto = xtc_db_insert_id();
        }

        $_SESSION['klarna_refno'] = $result[0];
    }

    /**
    * Handle the $_POST variable and return a KlarnaAddr object
    *
    * @param string $option payment option, invoice, part or spec
    *
    * @return KlarnaAddr address object
    */
    public function handlePost($option)
    {
        $addrHandler = new KlarnaAddressXtc;
        $errors = array();
        $lang = self::getLanguageCode();

        $address = new KlarnaAddr();

        if (strtolower($this->_country) == 'se') {
            try {
                $address = $addrHandler->getMatchingAddress($errors, $option);
            } catch(Exception $e) {
                $this->setError(
                    htmlentities($e->getMessage()) . " (#" . $e->getCode() . ")",
                    $option
                );
                xtc_redirect(
                    $this->errorLink(
                        FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false
                    )
                );
            }
        }

        if (strtolower($this->_country) != "se") {
            try {
                $aKlarnaAddress = $addrHandler->addressArrayFromPost($option);

                $address = $addrHandler->buildKlarnaAddressFromArray(
                    $aKlarnaAddress, $this->_country
                );
            } catch(Exception $e) {
                $this->setError(
                    htmlentities(
                        $e->getMessage()
                    ) . " (#" . $e->getCode() . ")",
                    $option
                );
                xtc_redirect(
                    $this->errorLink(
                        FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false
                    )
                );
            }

            $_SESSION['klarna_data'] = $aKlarnaAddress;

            if (KiTT_CountryLogic::needConsent($this->_country)
                && $_POST["klarna_{$option}_consent"] != 'consent'
            ) {
                $errors[] = "no_consent";
            }

            if (KiTT_CountryLogic::needDateOfBirth($this->_country)) {
                $_SESSION['klarna_data']["pno"]
                    = $_POST["klarna_{$option}_birth_day"] .
                    $_POST["klarna_{$option}_birth_month"] .
                    $_POST["klarna_{$option}_birth_year"];

                $_SESSION['klarna_data']['gender']
                    = $_POST["klarna_{$option}_gender"];
            }
        }

        if (!empty($errors)) {
            foreach ($errors as $err) {
                $translated[] = $this->translate($err, $lang);
            }

            $this->setError(
                htmlentities(
                    implode(',', $translated),
                    ENT_COMPAT,
                    'UTF-8'
                ),
                $option
            );
            xtc_redirect(
                $this->errorLink(FILENAME_CHECKOUT_PAYMENT, "", "SSL")
            );
        }

        return $address;
    }

    /**
     * Build osCommerce's hidden fields that are required for it to keep
     * it's _POST variable
     *
     * @param object $addr          KlarnaAddr object
     * @param string $invoiceType   invoice type
     * @param int    $paymentPlan   pclass id
     * @param string $email_address email address
     * @param string $reference     reference
     *
     * @return string   the hidden fields string
     */
    public function hiddenFieldString(
        $addr, $invoiceType, $paymentPlan, $email_address, $reference
    ) {
        global $order;

        $pno = $_SESSION['klarna_data']['pno'];
        $gender = $_SESSION['klarna_data']['gender'];

        $process_button_string
            = xtc_draw_hidden_field('addr_num', 1, true, '').
            xtc_draw_hidden_field("klarna_pno", $pno).
            xtc_draw_hidden_field("klarna_street", $addr->getStreet()).
            xtc_draw_hidden_field("klarna_postno", $addr->getZipCode()).
            xtc_draw_hidden_field("klarna_city", $addr->getCity()).
            xtc_draw_hidden_field("klarna_phone", $addr->getTelno()).
            xtc_draw_hidden_field("klarna_phone2", $addr->getCellno()).
            xtc_draw_hidden_field("klarna_email", $email_address).
            xtc_draw_hidden_field("klarna_invoice_type", $invoiceType).
            xtc_draw_hidden_field("klarna_house", $addr->getHouseNumber()) .
            xtc_draw_hidden_field("klarna_houseext", $addr->getHouseExt()) .
            xtc_draw_hidden_field("klarna_reference", $reference) .
            xtc_draw_hidden_field("klarna_gender", $gender).
            xtc_draw_hidden_field("klarna_paymentPlan", $paymentPlan);
        return $process_button_string;

    }

    /**
    * Get the value of the cart
    *
    * @return float
    */
    public function getCartSum()
    {
        global $order;
        $currency = $_SESSION['currency'];

        if (KlarnaConstant::isAdmin()) {
            return 0;
        }

        $shippingCost = $this->_getShippingCost();

        if ($order == null) {
            return $shippingCost + ($_SESSION['cart']->show_total());
        }

        $totalSum = 0;
        foreach ($order->products as $product) {
            if (KlarnaConstant::showPriceTax() === true || KlarnaConstant::addTaxOT() === false) {
              $totalSum += $product['price'] * $product['qty'];
            } else {
              $totalSum += ($product['price']
                * (1 + $product['tax'] / 100)) * $product['qty'];
            }            
            /*
            $totalSum += ($product['price']
                * (1 + $product['tax'] / 100)) * $product['qty'];
            */
        }

        return $shippingCost + $totalSum;
    }

    /**
     * Get the shipping cost. Will return the cost based on the config
     * flag customers_status_show_price_tax session variable
     *
     * @return double
     */
    private function _getShippingCost()
    {
        global $xtPrice;

        $shipping = $_SESSION['shipping'];
        $currency = $_SESSION['currency'];

        // All prices except the shipping cost are multipled by the currency value.
        $cval = $xtPrice->currencies[$currency]['value'];
        $shippingCost = $cval * $shipping["cost"];
        
        /*
        if (KlarnaConstant::showPriceTax() === true) {
            return $shippingCost;
        }
        */
        
        if (KlarnaConstant::showPriceTax() === true || KlarnaConstant::addTaxOT() === true) {
          $taxRate = $this->_getShippingTaxRate($shipping["id"]);
          return ($shippingCost * ($taxRate / 100 + 1));
        } else {
          return $shippingCost;
        }
    }

    /**
     * Get the shipping tax rate. It relies on there beeing a global $order object
     * in order to get the country and zone id used for xtc_get_tax_rate
     *
     * @param array $shippingId The global shipping methods id key
     *
     * @return double
     */
    private function _getShippingTaxRate($shippingId)
    {
        global $order;

        $method = @explode('_', $shippingId);
        $delZoneId = ($order->delivery['zone_id'] > 0)
                    ? $order->delivery['zone_id']
                    : null;
        return xtc_get_tax_rate(
            $this->_getShippingTaxClass($method[0]),
            $order->delivery['country']['id'],
            $delZoneId
        );
    }

    /**
     * Return the shipping tax class
     *
     * @param string $method The pament method
     *
     * @return int
     */
    private function _getShippingTaxClass($method)
    {
        $method = strtoupper($method);
        $constant = "MODULE_SHIPPING_{$method}_TAX_CLASS";
        if (defined($constant)) {
            return constant($constant);
        }
        return 0;
    }

    /**
     * Calculate order total and save it away.
     *
     * We need to access to all additional charges, ie the order_totals list, in
     * the before_process() function but at that point order_totals->process
     * hasn't been run.
     *
     * @return array order_total_array
     */
    public function getOrderTotal()
    {
        global $order_total_modules, $order;

        $klarna_ot = $_SESSION['klarna_ot'];
        $shipping = $_SESSION['shipping'];

        $orderTotalModules = $order_total_modules->modules;

        $klarnaOrderTotals = array();

        if (!is_array($orderTotalModules)) {
            return $klarnaOrderTotals;
        }

        $ignore = array(
            'ot_tax',
            'ot_subtotal',
            'ot_total',
            'ot_subtotal_no_tax'
        );

        $showTax = KlarnaConstant::showPriceTax();

        foreach ($orderTotalModules as $value) {
            $className = substr($value, 0, strrpos($value, '.'));
            $class = $GLOBALS[$className];

            // If the module class isn't an object, move along
            if (!is_object($class)) {
                continue;
            }

            // It this module isn't enabled, move along.
            if (!$class->enabled) {
                continue;
            }

            // Check if the module should be ignored. This is so that we don't add
            // the sub_total, order_total and tax_total to our goods list
            if (in_array($class->code, $ignore)) {
                continue;
            }

            $output = $class->output;
            if (sizeof($output) == 0) {
                continue;
            }

            $taxClass = null;
            foreach ($class->keys() as $constant) {
                if (strlen(strstr($constant, "TAX_CLASS")) > 0) {
                    if (defined($constant)) {
                        $taxClass = constant($constant);
                        continue;
                    }

                }
            }

            $taxRate = 0;
            $delCountryId = $order->delivery['country']['id'];
            $delZoneId = ($order->delivery['zone_id'] > 0)
                            ? $order->delivery['zone_id']
                            : null;

            if ($taxClass !== null) {
                $taxRate = xtc_get_tax_rate(
                    $taxClass, $delCountryId, $delZoneId
                );
            }

            foreach ($output as $orderTotal) {
                $orderTotal["rate"] = $taxRate;
                $klarnaOrderTotals[$className] = $orderTotal;
            }

            //Set Shipping VAT
            if ($className == 'ot_shipping') {
                $taxRate = $this->_getShippingTaxRate($shipping["id"]);
                $klarnaOrderTotals[$className]["rate"] = $taxRate;
            }


            $tax = 0;
            $value = $klarnaOrderTotals[$className]["value"];
            if ($showTax === false) {
                $tax = xtc_add_tax($value, $taxRate) - $value;
            } else {
                $tax = $value - ($value / (1 + ($taxRate / 100)));
            }

            $klarnaOrderTotals[$className]["tax"] = $tax;
        }

        return $klarnaOrderTotals;
    }

    /**
     * Remove $_POST data we don't want.
     *
     * @param string $opt payment option shorthand, inv part or spec
     *
     * @return void
     */
    private function _cleanSpecificPost($opt)
    {
        unset($_POST["klarna_{$opt}_fname"]);
        unset($_POST["klarna_{$opt}_lname"]);
        unset($_POST["klarna_{$opt}_gender"]);
        unset($_POST["klarna_{$opt}_pno"]);
        unset($_POST["klarna_{$opt}_street"]);
        unset($_POST["klarna_{$opt}_house"]);
        unset($_POST["klarna_{$opt}_postno"]);
        unset($_POST["klarna_{$opt}_city"]);
        unset($_POST["klarna_{$opt}_phone"]);
        unset($_POST["klarna_{$opt}_email"]);
        unset($_POST["klarna_{$opt}_paymentPlan"]);
        unset($_POST["klarna_{$opt}_reference"]);
        unset($_POST["klarna_{$opt}_shipment_address"]);
        unset($_POST["klarna_{$opt}_houseext"]);
    }

    /**
     * Remove unwanted data from the POST variable
     *
     * @return void
     */
    public function cleanPost()
    {
        unset($_SESSION['klarna_data']);
        if ($_POST['payment'] != 'klarna_invoice') {
            $this->_cleanSpecificPost('invoice');
        }
        if ($_POST['payment'] != 'klarna_SpecCamp') {
            $this->_cleanSpecificPost('spec');
        }
        if ($_POST['payment'] != 'klarna_partPayment') {
            $this->_cleanSpecificPost('part');
        }
    }

    /**
     * Translate a string from the languagepack.
     *
     * @param string                    $sTitle title of the wanted translation.
     * @param KlarnaLanguage|int|string $lang   language to translate to.
     *
     * @return string the translated string.
     */
    public function translate($sTitle, $lang = null)
    {
        return KiTT::translator(
            KiTT::locale($this->_country, $lang)
        )->translate($sTitle);
    }

    /**
     * Build the cart to be used for the purchase.
     *
     * @param string $estoreUser  estoreUser identifier
     * @param object $order       osCommerce order object
     * @param string $option      invoice, part or spec
     * @param string $code        payment code
     * @param int    $paymentPlan pclass id
     *
     * @return void
     */
    public function buildCart($estoreUser, $order, $option, $code, $paymentPlan)
    {

        if ($option == KiTT::PART) {
            $artno = MODULE_PAYMENT_KLARNA_PARTPAYMENT_ARTNO;
        } else if ($option == KiTT::SPEC) {
            $artno = MODULE_PAYMENT_KLARNA_SPECCAMP_ARTNO;
        } else {
            $artno = MODULE_PAYMENT_KLARNA_INVOICE_ARTNO;
        }

        $flags = KlarnaFlags::INC_VAT;
        if (KlarnaConstant::showPriceTax() === false) {
            $flags = KlarnaFlags::NO_FLAG;
        }

        // Add all the articles to the goodslist
        foreach ($order->products as $product) {

            $attributes = "";
            if (isset($product['attributes'])) {
                foreach ($product['attributes'] as $attr) {
                    $attributes = $attributes . ", " . $attr['option'] . ": " .
                        $attr['value'];
                }
            }

            $artnumber = $product[$artno];
            if ($artno == 'id' || $artno == '') {
                $artnumber = xtc_get_prid($product['id']);
            }

            $this->_klarna->addArticle(
                KiTT_String::encode($product['qty']),
                KiTT_String::encode($artnumber),
                KiTT_String::encode(
                    strip_tags(
                        $product['name'] . $attributes
                    )
                ),
                KiTT_String::encode($product['price']),
                KiTT_String::encode(number_format($product['tax'], 2)),
                0,
                $flags
            );
        }

        // Then the extra charges like shipping and invoicefee and
        // discount.
        $klarna_ot = $_SESSION['klarna_ot'];
        $extra = $klarna_ot['code_entries'];

        // If someone tries to set a pclass value to -1 using firebug, force
        // an invoice fee on them.
        if ($paymentPlan < 0) {
            $code = "klarna";
        }
        // Go over all the order total modules that are active for this order
        // and add them.
        foreach ($klarna_ot as $key => $item) {
        	$flags = KlarnaFlags::INC_VAT;
              if (KlarnaConstant::showPriceTax() === false) {
                $flags = KlarnaFlags::NO_FLAG;
            }
            if ($key === "ot_shipping") {
                $flags |= KlarnaFlags::IS_SHIPMENT;
            } else if ($key === "ot_klarna_fee") {
                $flags |= KlarnaFlags::IS_HANDLING;
            }

			else if ($key === "ot_coupon" || $key === "ot_discount" || $key === "ot_gv") {
				if ($item["value"] > 0) {
				$item["value"] *= -1;
				}
			}

            $title = rtrim($item["title"], ':');
            $this->_klarna->addArticle(
                1,
                "",
                html_entity_decode($title, ENT_COMPAT, KiTT_String::$klarnaEncoding),
                $item["value"],
                $item["rate"],
                0,
                $flags
            );
        }
    }

    /**
     * Configure available Klarna countries in KiTT
     *
     * @param string $option payment option
     *
     * @return void
     */
    public static function configureKlarna($option)
    {
        foreach (KlarnaConstant::getActivated($option) as $country) {
            $eid = KlarnaConstant::merchantID($option, $country);
            $secret = KlarnaConstant::secret($option, $country);
            // if eid or secret is 0, "" or null, ignore this country.
            if (!$eid || !$secret) {
                continue;
            }
            $locale = KiTT::locale($country);
            KiTT::configureKlarna($locale, $eid, $secret);
        }
    }

    /**
     * Show the PClasses
     *
     * @param array  $eid_array array of eids and secrets
     * @param string $option    payment option
     *
     * @return void
     */
    public function showPClasses($eid_array, $option)
    {
        self::configureKlarna($option);

        if ($_GET['get_pclasses'] == true) {
            $pcstorage = new MySQLStorage;
            $pcstorage->clear(KlarnaConstant::pcURI());
        }

        $data = array();
        $country_data = array();
        foreach (KlarnaConstant::getActivated($option) as $country) {
            $country = strtolower($country);
            try {

                if ($_GET['get_pclasses'] == true) {
                    KiTT::api($country)->fetchPClasses();
                }
                foreach (KiTT::api($country)->getPClasses() as $pclass) {
                    $country_data['country'][] = array(
                        'country' => $country,
                        'id' => $pclass->getId(),
                        'months' => $pclass->getMonths(),
                        'interestrate' => $pclass->getInterestRate(),
                        'invoicefee' => $pclass->getInvoiceFee(),
                        'startfee' => $pclass->getStartFee(),
                        'minamount' => $pclass->getMinAmount(),
                        'description' => $pclass->getDescription()
                    );
                }

            } catch (Exception $e) {
                $data['error']['country'][] = array(
                    'country' => $country,
                    'message' => $e->getMessage(),
                    'code' => $e->getCode()
                );
            }
        }

        $data['success'] = $country_data;
        $templateLoader = KiTT::templateLoader(KiTT::Locale($this->_country));
        $fetch = $templateLoader->load('fetched_pclasses.mustache');
        echo $fetch->render($data);
    }

    /**
     * Check if pclasses for given option exists and show warning if it doesn't
     *
     * @param string $option 'part' or 'spec'
     *
     * @return void
     */
    public function checkForPClasses($option)
    {
        $sql = "";
        $module = "";
        if ($option == KiTT::PART) {
            $module = 'Part Payment Module';
            $sql = "type <> 2";
        } else if (KiTT::SPEC) {
            $module = 'Special Campaigns Module';
            $sql = "type = 2";
        } else {
            return;
        }
        if (KlarnaConstant::isEnabled($option, $this->_country)) {
            // instantiate MySQLStorage to ensure the table exists
            $pcURI = KlarnaConstant::pcURI();
            $pcstorage = new MySQLStorage;
            $pcstorage->load($pcURI);
            $count = $this->_klarnaDB->query(
                "SELECT COUNT(type) as num FROM klarna_pclasses WHERE {$sql}"
            )->getArray();
            if ($count['num'] == 0
                && !isset($_GET['get_pclasses'])
                && headers_sent()
            ) {
                $templateLoader = KiTT::templateLoader(
                    KiTT::Locale($this->_country)
                );
                $no_pclasses = $templateLoader->load('no_pclasses.mustache');
                echo $no_pclasses->render(array('module' => $module));
            }
        }
    }

    /**
     * Prepare to fetch pclasses by building an array of eid and secrets
     *
     * @param string $option 'part' or 'spec'
     *
     * @return array
     */
    public function prepareFetch($option)
    {
        $countries = "";
        // Fethcing the pclasses
        if ($option == 'part') {
            $countries = explode(
                ",", strtolower(MODULE_PAYMENT_KLARNA_PARTPAYMENT_ACTIVATED_COUNTRIES)
            );
        } else if ($option == 'spec') {
            $countries = explode(
                ",", strtolower(MODULE_PAYMENT_KLARNA_SPECCAMP_ACTIVATED_COUNTRIES)
            );
        } else {
            return;
        }
        // Set the array
        $eid_array = array();

        foreach ($countries as $country) {
            $eid_array[$country]['eid'] = KlarnaConstant::merchantID(
                $option, $country
            );
            $eid_array[$country]['secret'] = KlarnaConstant::secret(
                $option, $country
            );
        }
        return $eid_array;
    }

    /**
     * protected output string
     *
     * @param string $string string
     *
     * @return string
     */
    public function klarnaOutputStringProtected($string)
    {
        return klarnaOutputString($string, false, true);
    }

    /**
    * Creates a SEO safe error link.
    *
    * @param string $page               page
    * @param string $parameters         parameters
    * @param string $connection         connection
    * @param bool   $add_session_id     add session id
    * @param bool   $search_engine_safe SEO friendly
    *
    * @return string
    */
    public function errorLink(
        $page = '', $parameters = '', $connection = 'NONSSL',
        $add_session_id = true, $search_engine_safe = true
    ) {
        global $request_type, $session_started, $SID;

        if (!xtc_not_null($page)) {
            die(
                '<br><br><font color="#f3014d"><b>Error!</b></font><br><br>'.
                '<b>Unable to determine the page link!<br><br>'
            );
        }

        if ($connection == 'NONSSL') {
            $link = HTTP_SERVER . DIR_WS_HTTP_CATALOG;
        } else if ($connection == 'SSL') {
            if (ENABLE_SSL == true) {
                $link = HTTPS_SERVER . DIR_WS_CATALOG;
            } else {
                $link = HTTP_SERVER . DIR_WS_CATALOG;
            }
        } else {
            die(
                '<br><br><font color="#f3014d"><b>Error!</b></font><br><br>'.
                '<b>Unable to determine connection method on a link!<br><br>'.
                'Known methods: NONSSL SSL</b><br><br>'
            );
        }

        if (xtc_not_null($parameters)) {
            $link .= $page . '?' . $this->klarnaOutputString($parameters);
            $separator = '&';
        } else {
            $link .= $page;
            $separator = '?';
        }
        while ((substr($link, -1) == '&') || (substr($link, -1) == '?')) {
            $link = substr($link, 0, -1);
        }

        // Add the session ID when moving from different HTTP and HTTPS servers,
        // or when SID is defined
        if ( ($add_session_id == true) && ($session_started == true)
            && (SESSION_FORCE_COOKIE_USE == 'False')
        ) {
            if (xtc_not_null($SID)) {
                $_sid = $SID;
            } else if ((($request_type == 'NONSSL') && ($connection == 'SSL')
                && (ENABLE_SSL == true) ) || ( ($request_type == 'SSL')
                && ($connection == 'NONSSL') )
            ) {
                if (HTTP_COOKIE_DOMAIN != HTTPS_COOKIE_DOMAIN) {
                    $_sid = xtc_session_name() . '=' . xtc_session_id();
                }
            }
        }

        if ( (SEARCH_ENGINE_FRIENDLY_URLS == 'true')
            && ($search_engine_safe == true)
        ) {
            while (strstr($link, '&&')) {
                $link = str_replace('&&', '&', $link);
            }

            $link = str_replace('?', '/', $link);
            $link = str_replace('&', '/', $link);
            $link = str_replace('=', '/', $link);

            $separator = '?';
        }

        if (isset($_sid)) {
            $link .= $separator . $_sid;
        }
        return $link;
    }

    /**
     * Update orderstatuses in the database
     *
     * @param int $customer The order status id to show the customer
     * @param int $admin    The order status id to show in the administration page
     *
     * @return void
     */
    public function updateOrderDatabase($customer, $admin)
    {
        global $insert_id;

        $orderid = xtc_db_input($insert_id);
        $refno = xtc_db_input($_SESSION['klarna_refno']);

        $sql_data_arr = array(
            'orders_id' => $orderid,
            'orders_status_id' => $customer,
            'comments' => "Accepted by Klarna. Reference #: {$refno}",
            'customer_notified' => 1,
            'date_added' => date("Y-m-d H:i:s")
        );
        xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_arr);

        $has_ordernum_table = xtc_db_fetch_array(
            xtc_db_query(
                "SELECT COUNT(*) ".
                "FROM information_schema.tables ".
                "WHERE table_schema = '" . DB_DATABASE . "' ".
                "AND table_name = 'klarna_ordernum';"
            )
        );
        $has_ordernum_table = $has_ordernum_table['COUNT(*)'];

        if ($has_ordernum_table > 0) {
            xtc_db_query(
                "INSERT INTO `klarna_ordernum` (orders_id, klarna_ref) ".
                "VALUES ({$orderid}, {$refno})"
            );
        }
        // Set pending status and hide it from customer.
        $status = $_SESSION['klarna_orderstatus'];
        if (isset($status)) {
			$orderStatusQuery = $this->_klarnaDB->query("SELECT orders_status_id FROM " . TABLE_ORDERS_STATUS . " WHERE orders_status_name = '{$status}'");
			$orderStatusID = $orderStatusQuery->getArray();
            $sql_data_arr = array(
                'orders_id' => $orderid,
                'orders_status_id' => $orderStatusID['orders_status_id'],
                'comments' => "Klarna Orderstatus: {$status}",
                'customer_notified' => 0,
                'date_added' => date("Y-m-d H:i:s")
            );
            xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_arr);
            xtc_db_query("UPDATE ".TABLE_ORDERS." SET orders_status='".$orderStatusID['orders_status_id']."' WHERE orders_id='".$orderid."'");
        }
        try {
            $this->_klarna->setEstoreInfo(KiTT_String::encode($orderid));
            $this->_klarna->update($_SESSION['klarna_refno']);
        } catch (Exception $e) {
            Klarna::printDebug(
                __METHOD__, "{$e->getMessage()} #({$e->getCode()})"
            );
        }

        //Delete Session with user details
        unset($_SESSION['klarna_data']);
        unset($_SESSION['klarna_refno']);
        unset($_SESSION['klarna_orderstatus']);
    }

}
