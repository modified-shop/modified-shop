<?php
require_once('class.KlarnaLanguagePack.php');
/**
 * The Klarna API class. This class handles all the API functions send by the GUI.
 *
 * @package     Klarna Standard Kassa API
 * @version     ##KLARNA_CHECKOUT_VERSION##
 * @since       ##KLARNA_CHECKOUT_DATE##
 * @link        http://integration.klarna.com/
 * @copyright   Copyright (c) 2011 Klarna AB (http://klarna.com)
 */
class KlarnaAPI {
    /**
     * Array with different input values
     *
     * @var array
     */
    private $aInputParameters = array();

    /**
     * Array with different input value values
     *
     * @var array
     */
    private $aInputValues = array();

    /**
     * The county code
     *
     * @var string
     */
    private $sCountryCode;

    /**
     * The type of class loaded. Either part or invoice or spec
     *
     * @var string
     */
    private $sType;

    /**
     * The ISO for language (e.g. sv, da, nb, en, de)
     *
     * @var string
     */
    private $sLangISO;

    /**
     * The setup values.
     *
     * @var array
     */
    private $aSetupSettings = array();

    /**
     * The PClasses
     *
     * @var array
     */
    public $aPClasses;

    /**
     * The klarna object
     *
     * @var Klarna
     */
    private $oKlarna;

    /**
     * The klarna language, set from KlarnaLanguage object
     *
     * @var integer
     */
    private $iKlarnaLanguage;

    /**
     * The klarna currency, set from KlarnaCurrency object
     *
     * @var integer
     */
    private $iKlarnaCurrency;

    /**
     * The klarna country, set from KlarnaCountry object
     *
     * @var integer
     */
    private $iKlarnaCountry;

    /**
     * The path where the API and Standard register is located
     *
     * @var string
     */
    private $sPath;

    /**
     * The ILT questions
     */
    private $aIltQuestions = array();

    /**
     * Klarna language pack
     */
    private $languagePack;
    /**
     * Klarna Formatter
     */
    private $formatter;

    /**
     * The class constructor. Initiates the Klarna Api class
     *
     * @ignore Do not show this in PHPDoc.
     * @return void
     */
    public function __construct ($a_sCountry, $a_sLangISO, $a_sType, $a_iSum, $a_iFlag, &$a_oKlarna = null, $aTypes = null, $sPath = null)
    {
        $this->sPath = $sPath;
        $this->formatter = null;

        if ($a_sLangISO == null)
        {
            $aLangArray    = array("se" => "sv", "de" => "de", "dk" => "da", "nl" => "nl", "no" => "nb", "fi" => "fi", "en" => "en");

            $a_sLangISO    = @$aLangArray[strtolower($a_sCountry)];
        }

        // Set the klarna object
        $this->oKlarna    = &$a_oKlarna;

        // Validate the submitted values
        $this->setCountry($a_sCountry);
        $this->setLanguage($a_sLangISO);
        $this->validateType($a_sType);

        // Set the default input names
        $this->aInputParameters['street'] = "street";
        $this->aInputParameters['homenumber'] = "homenumber";
        $this->aInputParameters['paymentPlan'] = "paymentPlan";
        $this->aInputParameters['gender'] = "gender";
        $this->aInputParameters['male'] = "male";
        $this->aInputParameters['female'] = "female";
        $this->aInputParameters['birth_day'] = "birth_day";
        $this->aInputParameters['birth_month'] = "birth_month";
        $this->aInputParameters['birth_year'] = "birth_year";
        $this->aInputParameters['bd_jan'] = "1";
        $this->aInputParameters['bd_feb'] = "2";
        $this->aInputParameters['bd_mar'] = "3";
        $this->aInputParameters['bd_apr'] = "4";
        $this->aInputParameters['bd_may'] = "5";
        $this->aInputParameters['bd_jun'] = "6";
        $this->aInputParameters['bd_jul'] = "7";
        $this->aInputParameters['bd_aug'] = "8";
        $this->aInputParameters['bd_sep'] = "9";
        $this->aInputParameters['bd_oct'] = "10";
        $this->aInputParameters['bd_nov'] = "11";
        $this->aInputParameters['bd_dec'] = "12";
        $this->aInputParameters['socialNumber'] = "socialNumber";
        $this->aInputParameters['phoneNumber'] = "phoneNumber";
        $this->aInputParameters['year_salary'] = "year_salary";
        $this->aInputParameters['house_extension'] = "house_extension";
        $this->aInputParameters['shipmentAddressInput'] = "shipment_address";
        $this->aInputParameters['emailAddress'] = "emailAddress";
        $this->aInputParameters['invoiceType'] = "invoiceType";
        $this->aInputParameters['reference'] = "reference";
        $this->aInputParameters['companyName'] = "companyName";
        $this->aInputParameters['firstName'] = "firstName";
        $this->aInputParameters['lastName'] = "lastName";
        $this->aInputParameters['consent'] = "consent";
        $this->aInputParameters['city'] = "city";
        $this->aInputParameters['zipcode'] = "zipcode";


        // Set the default setup values
        $this->aSetupSettings['langISO']        = $this->sLangISO;
        $this->aSetupSettings['countryCode']    = $this->sCountryCode;
        $this->aSetupSettings['sum']            = $a_iSum;
        $this->aSetupSettings['flag']           = $a_iFlag;
        $this->aSetupSettings['payment_id']     = "payment";
        $this->aSetupSettings['ajax_path']      = "klarnaAjax.php";
        $this->aSetupSettings['invoice_name']   = 'klarna_invoice';
        $this->aSetupSettings['part_name']      = 'klarna_partPayment';
        $this->aSetupSettings['spec_name']      = 'klarna_SpecCamp';

        $this->aSetupSettings['web_root']       = "/";

        $this->setPaths();

        // Fetch PClasses in case type is invoice
        if (($this->sType == 'part' || $this->sType == 'spec') && $this->oKlarna != null)
        {
            $this->fetchPClasses($a_iSum, $a_iFlag, $aTypes);
        }
    }

    public function setPaths ($js='js/', $css='', $img='')
    {
        $web_root = $this->aSetupSettings['web_root'];

        $this->aSetupSettings['path_js'] = $web_root . $js;
        $this->aSetupSettings['path_css'] = $web_root . $css;
        $this->aSetupSettings['path_img'] = $web_root . $img;
    }

    /**
     * Add/Overwrite extra setup values.
     *
     * @param string $sName The name of the value
     * @param string $sValue The value
     * @return void
     */
    public function addSetupValue ($sName, $sValue)
    {
        $this->aSetupSettings[$sName] = $sValue;
    }

    /**
     * Add multiple setup values at once
     *
     * @param array $aSetupValues The setup values as array. Key is name, value is value.
     * @return void
     */
    public function addMultipleSetupValues ($aSetupValues)
    {
        foreach ($aSetupValues as $sName => $sValue)
        {
            $this->aSetupSettings[$sName] = $sValue;
        }
    }

    /**
     * Add/Overwrite input values.
     *
     * @param string $sName The name of the value
     * @param string $sValue The value
     * @return void
     */
    public function addInputValue ($sName, $sValue)
    {
        $this->aInputValue[$sName] = $sValue;
    }

    /**
     * Add multiple input values at once
     *
     * @param array $aSetupValues The setup values as array. Key is name, value is value.
     * @return void
     */
    public function addMultipleInputValues ($aInputValues)
    {
        foreach ($aInputValues as $sName => $sValue)
        {
            $this->aInputValue[$sName] = $sValue;
        }
    }

    public function getInputValues () {
        return $this -> aInputValues;
    }

    /**
     * Set the ILT questions
     *
     * @param array $aIltQuestions
     */
    public function setIltQuestions ($aIltQuestions)
    {
        $this->aIltQuestions = $aIltQuestions;
    }

    public function setInvoiceFee ($fee) {
        if ($this -> sType != 'invoice') {
            throw new KlarnaApiException("Invoice fee only supported when payment type is invoice");
        }
        $this -> aSetupSettings['fee'] = $fee;
    }

    /**
     * Retrieve the finished HTML
     *
     * @param array     $a_aParams         The input field names. Only submitted for those that should be different from default values
     * @param string     $a_sHTMLFile     (Optional) The file to import. If not submitted, which HTML file will be decides by the class
     * @return string
     */
    public function retrieveHTML ($a_aParams = null, $a_aValues = null, $a_sHTMLFile = null, $aTemplateData = null)
    {
        if ($a_aValues != null)
            $this->aInputValues = array_merge(
            $this->aInputValues, $a_aValues);

        if ($a_aParams != null)
            $this->aInputParameters = array_merge(
                $this->aInputParameters, $a_aParams);

        // Backwards compability
        // using input values for red baloon is DEPRECATED
        if (array_key_exists ('red_baloon_content', $this -> aInputValues)) {
            $this -> aSetupSettings['red_baloon_content'] = $this -> aInputValues['red_baloon_content'];
        }

        if (array_key_exists ('red_baloon_paymentBox', $this -> aInputValues)) {
            $this -> aSetupSettings['red_baloon_paymentBox'] = $this -> aInputValues['red_baloon_paymentBox'];
        }

        $sTemplate = $this -> loadTemplate($a_sHTMLFile, $aTemplateData);

        Klarna::printDebug(__METHOD__ . ' setup settings', $this->aSetupSettings);
        Klarna::printDebug(__METHOD__ . ' input values', $this->aInputValues);

        return $this->translateInputFields($sTemplate);
    }

    public function loadTemplate($a_sHTMLFile = null, $aTemplateData = null)
    {
        $sFilename = '';

        if ($a_sHTMLFile != null)
        {
            $sFilename = $a_sHTMLFile;
        }
        else
        {
            if ($this->sType != "spec")
            {
                $sFilename    = ($this->sPath != null ? $this->sPath : "") . '/html/' . strtolower($this->sCountryCode) . "/" . $this->sType . ".html";
            }
            else {
                $this->aSetupSettings['conditionsLink']    = $aTemplateData['conditions'];

                $sFilename    = ($this->sPath != null ? $this->sPath : "") . '/html/campaigns/' . $aTemplateData['name'] . "/" . strtolower($this->sCountryCode) . "/" . $this->sType . ".html";
            }
        }

        Klarna::printDebug(__METHOD__ . 'loading template', $sFilename);
        if (!file_exists ($sFilename)) {
            throw new KlarnaApiException("Could not load template file");
        }
        return file_get_contents ($sFilename);
    }

    /**
     * Fetch the PClasses from file
     *
     * @param    integer    $iSum    The sum of the objects to be bought
     * @param    integer    $iFlag    The KlarnaFlag to be used. Either Checkout or ProductPage flag.
     * @return    void
     */
    public function fetchPClasses ($iSum, $iFlag, $aTypes = null)
    {
        if ($this->oKlarna == null)
        {
          throw new KlarnaApiException("No klarna class is set.", "1000");
        }

        $aPClasses = array();
        $default = null;
        foreach($this->oKlarna->getPClasses() as $pclass) {
            if ($aTypes == null || in_array($pclass->getType(), $aTypes))
            {
                $sType = $pclass->getType();

                if ($sType != KlarnaPClass::SPECIAL) {
                    if ($iSum < $pclass->getMinAmount()) {
                        continue;
                    }

                    if ($pclass->getType() == KlarnaPClass::FIXED) {
                        if ($iFlag == KlarnaFlags::PRODUCT_PAGE) {
                            continue;
                        }
                        $iMonthlyCost = -1;
                    } else {
                        $lowest_payment = KlarnaCalc::get_lowest_payment_for_account($pclass->getCountry());
                        $iMonthlyCost = KlarnaCalc::calc_monthly_cost($iSum, $pclass, $iFlag);
                        if ($iMonthlyCost < 0.01) {
                            continue;
                        }

                        if ($iFlag == KlarnaFlags::CHECKOUT_PAGE && $pclass->getType() == KlarnaPClass::ACCOUNT && $iMonthlyCost < $lowest_payment) {
                            $iMonthlyCost = $lowest_payment;
                        }

                        if ($pclass->getType() == KlarnaPClass::CAMPAIGN && $iMonthlyCost < $lowest_payment) {
                            continue;
                        }
                   }
                } else {
                    $iMonthlyCost = -1;
                }

                if ($this -> sType == 'part') {
                    if ($sType == KlarnaPClass::ACCOUNT) {
                        $default = $pclass;
                    } else if ($sType == KlarnaPClass::CAMPAIGN) {
                        if ($default === null || $default->getType() != KlarnaPClass::ACCOUNT) {
                            $default = $pclass;
                        }
                    } else if ($sType == KlarnaPClass::FIXED) {
                        if ($default === null) {
                            $default = $pclass;
                        }
                    } else {
                        continue;
                    }
                } else if ($this -> sType == 'spec') {
                    if ( $sType != KlarnaPClass::SPECIAL ) {
                        continue;
                    }
                    $default = $pclass;
                }

                $aPClasses[$pclass->getId()]['pclass'] = $pclass;
                $aPClasses[$pclass->getId()]['monthlyCost'] = $iMonthlyCost;
            }
        }

        $this->aPClasses = $aPClasses;
        if ($default != null) {
            $this->aInputValues['paymentPlan'] = $default->getId();
        }
    }

    /**
     * Checks wether the country code is accepted by the API
     *
     * @throws    KlarnaApiException
     * @param    string    $sCountryCode    The country code ISO-2
     * @return    boolean
     */
    private function validateCountry ($sCountryCode)
    {
        if (in_array(strtolower($sCountryCode), array("nl", "se", "de", "dk", "no", "fi")))
        {
            $this->sCountryCode    = strtolower($sCountryCode);

            switch ( $this->sCountryCode ) {
                case "nl":
                    $this->iKlarnaCountry = KlarnaCountry::NL;
                    $this->iKlarnaCurrency = KlarnaCurrency::EUR;
                    break;
                case "se":
                    $this->iKlarnaCountry = KlarnaCountry::SE;
                    $this->iKlarnaCurrency = KlarnaCurrency::SEK;
                    break;
                case "de":
                    $this->iKlarnaCountry = KlarnaCountry::DE;
                    $this->iKlarnaCurrency = KlarnaCurrency::EUR;
                    break;
                case "dk":
                    $this->iKlarnaCountry = KlarnaCountry::DK;
                    $this->iKlarnaCurrency = KlarnaCurrency::DKK;
                    break;
                case "no":
                    $this->iKlarnaCountry = KlarnaCountry::NO;
                    $this->iKlarnaCurrency = KlarnaCurrency::NOK;
                    break;
                case "fi":
                    $this->iKlarnaCountry = KlarnaCountry::FI;
                    $this->iKlarnaCurrency = KlarnaCurrency::EUR;
                    break;
                default:
                    break;
            }

            return true;
        }
        else {
            throw new KlarnaApiException('Error in ' . __METHOD__ . ': Invalid country code submitted!');
        }
    }

    /**
     * Checks wether the country code is accepted by the API
     *
     * @throws    KlarnaApiException
     * @param    string    $sType    The type. Either "part", "spec" or "invoice"
     * @return    boolean
     */
    private function validateType ($sType)
    {
        if (in_array(strtolower($sType), array("part", "invoice", "spec")))
        {
            $this->sType    = strtolower($sType);
            return true;
        }
        else {
            throw new KlarnaApiException('Error in ' . __METHOD__ . ': Invalid type submitted!');
        }
    }

    /**
     * Sets the active country from a ISO country string
     * or a KlarnaCountry constant
     */
    public function setCountry ($country) {
        if (!is_numeric ($country)) {
            $country = KlarnaCountry::fromCode ($country);
        } else {
            $country = intval ($country);
        }

        if ($this -> oKlarna == null) {
            throw new KlarnaApiException('Error in ' . __METHOD__ .
                ': Klarna instance not set');
        }
        $this -> iKlarnaCountry = $country;
        $this -> iKlarnaCurrency = $this -> oKlarna ->
            getCurrencyForCountry ($country);
        $this -> sCountryCode = $this -> oKlarna -> getCountryCode ($country);
    }

    public function getCountry () {
        return $this -> iKlarnaCountry;
    }

    /**
     * Sets the active country from a ISO country string
     * or a KlarnaLanguage constant
     */
    public function setLanguage ($language) {
        if (!is_numeric ($language)) {
            $language = Klarna::getLanguageForCode ($language);
        } else {
            $language = intval ($language);
        }

        $this -> iKlarnaLanguage = $language;
        if ($this -> oKlarna == null) {
            throw new KlarnaApiException('Error in ' . __METHOD__ .
                ': Klarna instance not set');
        }
        $this -> sLangISO = $this -> oKlarna -> getLanguageCode ($language);
    }

    public function getLanguage () {
        return $this -> iKlarnaLanguage;
    }

    /**
     * Checks wether the country code is accepted by the API
     *
     * @throws    KlarnaApiException
     * @param    string    $a_sLangISO    The language in ISO-2 format
     * @return    boolean
     */
    private function validateLangISO ($a_sLangISO)
    {
        if (in_array(strtolower($a_sLangISO), array("sv", "da", "en", "de", "nl", "nb", "fi")))
        {
            $this->sLangISO    = strtolower($a_sLangISO);

            switch ( $this->sLangISO ) {
                case "sv":
                    $this->iKlarnaLanguage = KlarnaLanguage::SV;
                    break;
                case "da":
                    $this->iKlarnaLanguage = KlarnaLanguage::DA;
                    break;
                case "de":
                    $this->iKlarnaLanguage = KlarnaLanguage::DE;
                    break;
                case "nl":
                    $this->iKlarnaLanguage = KlarnaLanguage::NL;
                    break;
                case "nb":
                    $this->iKlarnaLanguage = KlarnaLanguage::NB;
                    break;
                case "fi":
                    $this->iKlarnaLanguage = KlarnaLanguage::FI;
                    break;
                default:
                    break;
            }

            return true;
        }
        else {
            throw new KlarnaApiException('Error in ' . __METHOD__ . ': Invalid language ('.$a_sLangISO.') ISO submitted!');
        }
    }

    /**
     * Translating the fetched HTML agains dynamic values set in this class
     *
     * @param    string    $sHtml    The HTML to translate
     * @return    string
     */
    private function translateInputFields ($sHtml)
    {
        $sHtml = preg_replace_callback("@{{(.*?)}}@", array($this, 'changeText'), $sHtml);

        return $sHtml;
    }

    /**
     * Changeing the text from a HTML {{VALUE}} to the acual value decided by the array
     *
     * @param    array    $aText    The result from the match in function translateInputFields
     * @return    mixed
     */
    private function changeText ($aText) {
        // Split them
        $aExplode    = explode(".", $aText[1]);
        $sType        = $aExplode[0];
        $sName        = $aExplode[1];

        if ($sType == "input")
        {
            if (array_key_exists($sName, $this->aInputParameters))
                return $this->aInputParameters[$sName];
            else
            {
                throw new KlarnaApiException('Error in ' . __METHOD__ . ': Invalid inputfield value ('.$sName.') found in HTML code!');
                return false;
            }
        }
        else if($sType == "lang")
        {
            return $this->fetchFromLanguagePack($sName);
        }
        else if($sType == "setup")
        {
            if ($sName == "pclasses") {
                return $this->renderPClasses();
            }

            if ($sName == 'threatmetrix') {
                if (!array_key_exists ('threatmetrix', $this -> aSetupSettings)) {
                    $this->aSetupSettings['threatmetrix'] = $this -> oKlarna ->
                        checkoutHTML ();
                }
                return @$this -> aSetupSettings['threatmetrix'];
            }

            if ($sName == 'additional_information') {
                $key = @$this -> aSetupSettings['additional_information'];
                $frmt = @$this -> fetchFromLanguagePack($key);
                return @$this -> translateInputFields ($frmt);
            }

            return @$this->aSetupSettings[$sName];
        }
        else if ($sType == "value")
        {
            return @htmlentities ($this->aInputValues[$sName]);
        }
        else if ($sType == 'pp') {
            return $this->aInputValues[$sName];
        }
        else if ($sType == 'ilt')
        {
            if ($sName == 'box')
            {
                $sHtml    = "";

                if (count($this->aIltQuestions) > 0)
                {
                    foreach($this->aIltQuestions as $sInputValue => $aQuestion)
                    {
                        $sQuestion    = $aQuestion['text'];
                        $sAnswer    = "";
                        $sType        = strtolower($aQuestion['type']);
                        $aValues    = $aQuestion['values'];
                        $bSet        = true;

                        if ($sType == 'dropdown')
                        {
                            $sAnswer    = "<select name=\"$sInputValue\">\n";

                            foreach($aValues as $iNum => $aData)
                            {
                                $sAnswer .= '<option value="'.htmlentities($aData['value']).'">'.htmlentities($aData['name']).'</option>'."\n";
                            }

                            $sAnswer    .= "</select>";
                        }
                        else {
                            $bSet    = false;
                        }

                        if ($bSet)
                        {
                            $aParams    = array('ilt_question' => $sQuestion, 'ilt_answer' => $sAnswer);

                            $sHtml    .= $this->retrieveHTML($aParams, null, ($this->sPath != null ? $this->sPath : "") . '/html/ilt_template.html');
                        }
                    }
                }

                return $sHtml;
            }
            else {
                if (array_key_exists($sName, $this->aInputParameters))
                {
                    return $this->aInputParameters[$sName];
                }
            }
        }
        else {
            throw new KlarnaApiException('Error in ' . __METHOD__ . ': Invalid field name ('.$sType.') found in HTML code!');
            return false;
        }
    }

    /**
     * Redender the PClasses to HTML
     *
     * @return string
     */
    private function renderPClasses ()
    {
        $sString = "";
        $path_img = $this->aSetupSettings['path_img'];
        $defaultId = $this->aInputValues['paymentPlan'];

        foreach ($this->aPClasses as $sPClassId => $aPClassData)
        {
            $default = ($defaultId == $sPClassId);
            $value = $this->getPresentableValuta($aPClassData['monthlyCost']);
            $pm = $this->fetchFromLanguagePack('per_month');
            $sString .= '<li ' . ($default ? 'class="click"' : "") . '>
                <div>'.$aPClassData['pclass']->getDescription() .
                ($aPClassData['monthlyCost'] > 0 ?
                    " - $value $pm" : '') .
                '<img src="' . $path_img . 'checkout/ok.gif" border="0" alt="Chosen" />' .
                '</div>
                <span style="display: none">'.$sPClassId.'</span>
                </li>';
        }

        return $sString;
    }

    /**
     * Set formatter to be used for formatting.
     * @param object    $format     implementation of interface.KlarnaFormatter.php
     */
    public function setFormatter($format) {
        $this->formatter = $format;
    }

    /**
     * Make the sum shown presentable
     *
     * @param    integer    $iSum    The sum to present
     * @return    string
     */
    private function getPresentableValuta ($iSum)
    {
        $sBefore    = "";
        $sAfter        = "";
        if (isset($this->formatter) && $this->formatter != null) {
            return $this->formatter->formatPrice($iSum);
        } else {
            switch ( $this->sCountryCode ) {
                case 'se':
                case 'no':
                case 'dk':
                    $sAfter = " kr";
                    break;
                case 'fi';
                case 'de';
                case 'nl';
                    $sBefore = "&#8364;";
                    break;
            }
            return $sBefore . $iSum . $sAfter;
        }
    }

    public function setCurrency($currency, $position = null) {
        if (!is_numeric ($currency)) {
            $currency = KlarnaCurrency::fromCode ($currency);
        } else {
            $currency = intval ($currency);
        }
        switch ($currency) {
            case KlarnaCurrency::SEK:
            case KlarnaCurrency::NOK:
            case KlarnaCurrency::DKK:
                if ($position === null || $position == 'suffix') {
                    $this -> addSetupValue('currency_suffix', ' kr');
                } else {
                    $this -> addSetupValue('currency_prefix', ' kr');
                }
                break;
            case KlarnaCurrency::EUR:
                if ($position === null || $position == 'prefix') {
                    $this -> addSetupValue('currency_prefix', '&#8364;');
                } else {
                    $this -> addSetupValue('currency_suffix', '&#8364;');
                }
                break;
        }
    }

    /**
     * Fetch data from the language pack
     *
     * @param    string    $sText    The text to fech
     * @return    string
     */
    public function fetchFromLanguagePack ($sText, $sISO = null, $sPath = null)
    {
        if ($sISO == null)
        {
            if ($this != null && $this -> sLangISO != null) {
                $sISO = strtolower($this -> sLangISO);
            } else {
                $sISO = KlarnaAPI::getISOCode();
            }
        } else {
            $sISO    = KlarnaAPI::getISOCode($sISO);
        }

        if ($this -> sPath != null)
            $sPath = $this -> sPath;

        if ($this -> languagePack == null) {
            $this -> languagePack = new KlarnaLanguagePack(
                    ($sPath != null ? $sPath : "") .
                    'data/language.xml');
        }

        return $this -> languagePack -> fetch ($sText, $sISO);
    }

    /**
     * Returns the country code for the set country constant.
     *
     * @return string
     */
    public function getISOCode($sCode = null) {
        switch(strtolower($sCode)) {
            case "se":
            case "sv":
                return "sv";
            case "no":
            case "nb":
                return "nb";
            case "dk":
            case "da":
                return "da";
            case "fi":
                return "fi";
            case "de":
                return "de";
            case "nl":
                return "nl";
            case "us":
            case "uk":
            case "en":
            default:
                return "en";
        }
    }

    public function displayError($message, $field = null) {
        // Append message
        if (array_key_exists ('red_baloon_content', $this -> aSetupSettings)) {
            $this -> aSetupSettings['red_baloon_content'] =
                $this -> aSetupSettings['red_baloon_content'] .
                '<br/>' . addslashes ($message);
        } else {
            $this -> aSetupSettings['red_baloon_content'] = addslashes ($message);
        }

        // fall back to logo on multiple messages
        if (array_key_exists ('red_baloon_paymentBox', $this -> aSetupSettings)) {
            $this -> aSetupSettings['red_baloon_paymentBox'] = '';
        } else {
            $this -> aSetupSettings['red_baloon_paymentBox'] = $field;
        }

    }

    /**
     *
     * @param <type> $address
     * @return <type>
     */
    public static function splitAddress( $address ) {
        $numbers = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        $characters = array('-', '/', ' ', '#', '.', 'a', 'b', 'c', 'd', 'e',
                        'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p',
                        'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A',
                        'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L',
                        'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W',
                        'X', 'Y', 'Z');
        $specialchars = array('-', '/', ' ', '#', '.');

        //Where do the numbers start? Allow for leading numbers
        $numpos = self::strpos_arr($address,$numbers,2);
        //Get the streetname by splitting off the from the start of the numbers
        $streetname = substr($address, 0, $numpos);
        //Strip off spaces at the end
        $streetname = trim($streetname);

        //Get the housenumber+extension
        $numberpart = substr($address, $numpos);
        //and strip off spaces
        $numberpart = trim($numberpart);

        //Get the start position of the extension
        $extpos = self::strpos_arr($numberpart, $characters, 0);

        //See if there is one, if so
        if ($extpos != '') {
            //get the housenumber
            $housenumber = substr($numberpart, 0, $extpos);
            // and the extension
            $houseextension = substr($numberpart, $extpos);
            // and strip special characters from it
            $houseextension = str_replace ($specialchars, '', $houseextension);
        }
        else {
            //Otherwise, we already have the housenumber
            $housenumber = $numberpart;
        }

        return array( $streetname, $housenumber, $houseextension  );
    }

    /**
     *
     * @param <type> $haystack
     * @param <type> $needle
     * @param <type> $where
     * @return <type>
     */
    private static function strpos_arr($haystack, $needle, $where) {

        $defpos = 10000;
        if(!is_array($needle)) $needle = array($needle);
        foreach($needle as $what) {
            if(($pos = strpos($haystack, $what, $where))!==false) {
                if ($pos < $defpos) $defpos = $pos;
            }
        }
        return $defpos;
    }

    public function setAddress(KlarnaAddr $addr) {
        if (! $addr instanceof KlarnaAddr) {
            throw new KlarnaApiException(__METHOD__ . ': must be passed a KlarnaAddr');
        }

        $reference = @($addr -> getFirstName () . ' ' . $addr -> getLastName ());

        $cellno = $addr -> getCellno ();
        $telno = $addr -> getTelno ();
        $phone = (strlen ($cellno) > 0) ? $cellno : $telno;

        $values = &$this -> aInputValues;
        $values['companyName'] = $addr -> getCompanyName ();
        $values['firstName'] = $addr -> getFirstName ();
        $values['lastName'] = $addr -> getLastName ();
        $values['phoneNumber'] = $phone;
        $values['zipcode'] = $addr -> getZipCode ();
        $values['city'] = $addr -> getCity ();
        $values['street'] = $addr -> getStreet ();
        $values['homenumber'] = $addr -> getHouseNumber ();
        $values['house_extension'] = $addr -> getHouseExt ();
        $values['reference'] = $reference;
    }

    /**
     * Given a ISO 8601 date string (YYYY-MM-DD) sets birth_year, birth_month
     * and birth_day
     */
    public function setBirthday($dob) {
        $values = &$this -> aInputValues;
        $splitbday = explode('-', $dob);
        $values['birth_year'] = @$splitbday[0];
        $values['birth_month'] = @$splitbday[1];
        $values['birth_day'] = @$splitbday[2];
    }
}

/**
 * KlarnaApiException class, only used so it says "KlarnaApiException" instead of Exception.
 *
 * @package       Klarna Standard Kassa API
 * @author         Paul Peelen
 * @version     1.0
 * @since         1.0 - 14 mar 2011
 * @link        http://integration.klarna.com/
 * @copyright    Copyright (c) 2011 Klarna AB (http://klarna.com)
 */
class KlarnaApiException extends Exception
{
    public function __construct($sMessage, $code=0)
    {
        parent::__construct($sMessage,$code);
    }

    public function __toString()
    {
        return __CLASS__ . ":<p><font style='font-family: Arial, Verdana; font-size: 11px'>[Error: {$this->code}]: {$this->message}</font></p>\n";
    }
}
