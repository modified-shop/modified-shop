<?php
/**
 * The Mobile payment api core functionality file.
 * This class handles all requests send by the GUI/ajax
 *
 * @package       Klarna Standard Mobile Kassa API
 * @version     1.0
 * @since         1.0 - 4 maj 2011
 * @link        http://integration.klarna.com/
 * @copyright    Copyright (c) 2011 Klarna AB (http://klarna.com)
 */
require_once ("class.KlarnaMobile.php");

abstract class KlarnaMobileImpl {
    protected $oKlarna;
    
    private $sTemplate;
    
    private $sPath;
    
    private $sCountryCode;
    
    private $aInputParameters = array();
    
    private $aSetupSettings = array();
    
    protected $iSum = 0;
    
    /**
     * The telephone number for the request
     * 
     * @var $sTelNo string
     */
    private $sTelNo;
    
    private $sLangISO;
        
    function __construct (&$oKlarna, $sCountry = "se", $sPath = null, $sTemplate = null) {
        $this->oKlarna = &$oKlarna;
        $this->sTemplate = $sTemplate;
        $this->sPath = $sPath;
        $this->sCountryCode = $sCountry;
        $this->sLangISO = 'sv';
    }
    
    /**
     * Fetches the product and adds it as an article to the klarna class. No need to return any data.
     * Articles need to be set for fraud purpose, incorrect article means no_risk invoice. Hereby klarna will not take any risks.
     * 
     * @param mixed $mProductId The product identified. Either int or string. Adapted according shop functionality
     * @param Klarna $oKlarna The Klarna class object. Used to set any articles
     * @return void
     */
    abstract protected function fetchProduct($mProductId);
    
    /**
     * When a purchase is made, the script returns an redirect-URL or a message. When the URL is returned, the user is re-directed to this page.
     * This could be an URL to a downloadable file (WARNING! SHOULD ALWAYS BE A DYNAMIC URL!), or a link to a "Thank you"/confirmation page (Comon for donation purposes)
     * 
     * @param mixed $mProductId The product identified. Either int or string. Adapted according shop functionality
     * @return mixed Either NULL or FALSE if no URL is available, or STRING with full URL when URL is available.
     */
    abstract protected function fetchRedirectUrl ($mProductId);
    
    /**
     * When a purchase is made (approved as well) it might needs to be added to the merchants order system. In this function you can define how the order of a product should be handled.
     * 
     * @param mixed $mProductId The product identified. Either int or string. Adapted according shop functionality
     * @param integer $iKlarnaReference The reference returned by Klarna
     * @param string $sTelNo The telephone number used to make a purchase
     * @return void
     */
    abstract protected function processOrder ($mProductId, $iKlarnaReference, $iTelNo);
    
    /**
     * Fetch the HTML from the template theme
     * 
     * @return string The HTML completed as string
     */
    public function retrieveHTML () {
        // Get template
        $sTemplate = ($this->sPath != null ? $this->sPath : "") . '/html/mobile/' . $this->sTemplate . "/" . strtolower($this->sCountryCode) . "/template.html";
        
        return $this->translateInputFields(file_get_contents($sTemplate));
    }
    
    /**
     * Adding an input value
     * 
     * @param string $sParamName The name of an paramteter to set
     * @param mixed $mParamValue The value of the parameter
     * @return void
     */
    public function addInput ($sParamName, $mParamValue) {
        $this->aInputParameters[$sParamName] = $mParamValue;
    }
    
    /**
     * Translating the fetched HTML agains dynamic values set in this class
     * 
     * @param    string    $sHtml    The HTML to translate
     * @return    string
     */
    private function translateInputFields ($sHtml) {
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
                throw new KlarnaMobileApiException('Error in ' . __METHOD__ . ': Invalid inputfield value ('.$sName.') found in HTML code!');
                return false;
            }
        }
        else if($sType == "lang")
        {
            return $this->fetchFromLanguagePack($sName);
        }
        else if($sType == "setup")
        {
            return @$this->aSetupSettings[$sName];
        }
        else if ($sType == "value")
        {
            return @$this->aInputValues[$sName]; 
        }
        else {
            throw new KlarnaMobileApiException('Error in ' . __METHOD__ . ': Invalid field name ('.$sType.') found in HTML code!');
            return false;
        }
    }
    
    /**
     * Fetch data from the language pack
     * 
     * @param    string    $sText    The text to fech
     * @return    string
     */
    public function fetchFromLanguagePack ($sText) {
        if ($this != null && $this->sLangISO != null)
            $sISO = strtolower($this->sLangISO);
        else
            $sISO = KlarnaAPI::getISOCode();
                
        $oXml    = simplexml_load_file($this->sPath . '/klarna_files/klarna_language.xml');
        $aResult= (array)@$oXml->xpath("//string[@id='$sText']/$sISO");
        $aResult= (array)@$aResult[0];
        
        return @$aResult[0];
    }
    
    /**
     * Request the code for the mobile phone
     * 
     * @param string $sTelNo The telephone number
     * @param int $iSum The sum to request
     * @return mixed BOOLEAN true if success, message or exception if error
     */
    public function requestCode ($iPid, $sTelNo) {
        $this->sTelNo    = $sTelNo;
        
        try {
            $this->fetchProduct($iPid);
            
            //Transmit all the specified data, from the steps above, to Klarna.
            $aResult = $this->oKlarna->reserveAmount(
                $this->sTelNo,
                null,
                $this->iSum,
                KlarnaFlags::RSRV_PHONE_TRANSACTION+KlarnaFlags::RSRV_SEND_PHONE_PIN,
                -1
            );
            
            return $this->translateSuccesToXml($aResult[0], $aResult[1]);
        }
        catch(Exception $e) {
            return $this->translateErrorToXML($e->getCode(), utf8_encode($e->getMessage()));
        }
    }
    
    
    /**
     * Request the code for the mobile phone
     * 
     * @param string $sTelNo The telephone number
     * @param int $iSum The sum to request
     * @return mixed BOOLEAN true if success, message or exception if error
     */
    public function makePurchase ($iPid, $sTelNo, $sPinCode, $iRefNo) {
        $this->sTelNo    = $sTelNo;
        
        try {
            $this->fetchProduct($iPid);
            
            $this->oKlarna->setExtraInfo('pin', $sPinCode);
            $aResult = $this->oKlarna->activateReservation(
                $this->sTelNo,
                $iRefNo,
                null,
                null,
                KlarnaFlags::RSRV_PHONE_TRANSACTION+KlarnaFlags::RSRV_SEND_PHONE_PIN,
                -1
            );
            
            $sUrl = $this->fetchRedirectUrl($iPid);
            
            $this->processOrder($iPid, $iRefNo, $sTelNo);
            
            array_push($aResult, $sUrl);
            
            return $aResult;
        }
        catch(Exception $e) {
            return array("-1", utf8_encode($e->getMessage()), $e->getCode());
        }
    }
    
    private function translateErrorToXML ($iError, $sErrorMessage) {
        $sReturn    = <<<EOD
            <?xml version="1.0"?>
<result>
<statusCode>-1</statusCode>
<errorCode>$iError</errorCode>
<message>$sErrorMessage</message>
</result>
EOD;
        return $sReturn;
    }
        
    private function translateSuccesToXml ($iStatusCode, $sMessage) {
        $sReturn    = <<<EOD
        <?xml version="1.0"?>
<result>
<statusCode>$iStatusCode</statusCode>
<message>$sMessage</message>
</result>
EOD;
        return $sReturn;
    }
}

/**
 * KlarnaMobileApiException class.
 *
 * @package       Klarna Standard Kassa API
 * @version     1.0
 * @since         1.0 - 14 mar 2011
 * @link        http://integration.klarna.com/
 * @copyright    Copyright (c) 2011 Klarna AB (http://klarna.com)
 */
class KlarnaMobileApiException extends Exception
{
    public function __construct($sMessage, $code=0, Exception $previous = null)
        {
        parent::__construct($sMessage,$code, $previous);
        }   
    
    public function __toString()
        {
        return __CLASS__ . ":<p><font style='font-family: Arial, Verdana; font-size: 11px'>[Error: {$this->code}]: {$this->message}</font></p>\n";
        }    
}
