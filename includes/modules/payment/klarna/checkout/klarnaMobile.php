<?php
/**
 * 
 *
 * 
 */

/**
 * Include a bunch of files
 */ 
require('includes/application_top.php');
include_once('classes/impl.KlarnaMobile.php');
include_once('classes/class.KlarnaHTTPContext.php');
//include_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'klarna/klarnautils.php');
include_once('../api/Klarna.php');
include_once(DIR_FS_DOCUMENT_ROOT.'includes/external/klarna/api/transport/xmlrpc-3.0.0.beta/lib/xmlrpc.inc');
include_once(DIR_FS_DOCUMENT_ROOT.'includes/external/klarna/api/transport/xmlrpc-3.0.0.beta/lib/xmlrpc_wrappers.inc');

$sPage    = KlarnaHTTPContext::toString('page', null);


$sEID        = MODULE_PAYMENT_KLARNA_EID_SE;
$sSecret     = MODULE_PAYMENT_KLARNA_SECRET_SE;

//$iMode = (strtolower(MODULE_PAYMENT_KLARNA_LIVEMODE) == "true") ? Klarna::LIVE : Klarna::BETA;
$iMode = Klarna::BETA;

$oKlarna = new Klarna();
$oKlarna->config($sEID, $sSecret, KlarnaCountry::SE, KlarnaLanguage::SV, KlarnaCurrency::SEK, $iMode, MODULE_PAYMENT_KLARNA_PC_TYPE, MODULE_PAYMENT_KLARNA_PC_URI, false);
$oKlarna->setCountry('se');    

if ($sPage == null)
{
    $pId    = KlarnaHTTPContext::toString('productId', '');
    
    ?>
    <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
      <script src="http://code.jquery.com/jquery-1.5.js" type="text/javascript"></script>
    </head>
    <?php

    $oAPI    = new KlarnaMobile($oKlarna, 'se', DIR_FS_CATALOG . DIR_WS_CLASSES . 'klarna/standardRegister', 'default');
    $oAPI->addInput('placeholder', '0731234567');
    $oAPI->addInput('pId', $pId);
    
    echo $oAPI->retrieveHTML();
}
else if ($sPage == 'ajax'){
    $sSubAction    = KlarnaHTTPContext::toString('subAction', null);
    
    if ($sSubAction == 'sendCode')
    {
        register_shutdown_function('sleepSeconds', 1);  // Sleep one second to give the user some time to react to the SMS message send.
        
        $iPid    = KlarnaHTTPContext::toInteger('productId', 0);
        $sTelNo = KlarnaHTTPContext::toString('phoneNumber', null);
        
        if ($sTelNo == null || $iPid == 0)
        {
            // @TODO Throw exception
            exit("ERROR!");
        }
        
        $oKlarna = new Klarna();
        $oKlarna->config($sEID, $sSecret, KlarnaCountry::SE, KlarnaLanguage::SV, KlarnaCurrency::SEK, $iMode, MODULE_PAYMENT_KLARNA_PC_TYPE, MODULE_PAYMENT_KLARNA_PC_URI, false);
        $oKlarna->setCountry('se');
        
        $oAPI        = new KlarnaMobile(&$oKlarna, 'se', DIR_FS_CATALOG . DIR_WS_CLASSES . 'klarna/standardRegister');
        $mResult    = $oAPI->requestCode($iPid, $sTelNo);
        
        echo $mResult;
    }
    else if ($sSubAction == 'makePurchase')
    {
        $iPid        = KlarnaHTTPContext::toInteger('productId', 0);
        $sPinCode     = KlarnaHTTPContext::toString('pinCode', null);
        $sTelNo        = KlarnaHTTPContext::toString('phoneNumber', null);
        $iRefNo        = KlarnaHTTPContext::toInteger('reservationNumber', 0);
        
        if ($sPinCode == null || $iPid == 0 || $sTelNo == null || $iRefNo == 0)
        {
            // @TODO Throw exception
            exit("ERROR!");
        }
        
        $oKlarna = new Klarna();
        $oKlarna->config($sEID, $sSecret, KlarnaCountry::SE, KlarnaLanguage::SV, KlarnaCurrency::SEK, $iMode, MODULE_PAYMENT_KLARNA_PC_TYPE, MODULE_PAYMENT_KLARNA_PC_URI, false);
        $oKlarna->setCountry('se');
        
        $oAPI        = new KlarnaMobile($oKlarna, 'se', DIR_FS_CATALOG . DIR_WS_CLASSES . 'klarna/standardRegister');
        $mResult    = $oAPI->makePurchase($iPid, $sTelNo, $sPinCode, $iRefNo);
        
        $iStatusCode = -1;
        
        if ($mResult[0] == "ok")
        {
            $iStatusCode = 1;
        }
        
        $sUrl = $mResult[2];
        
        if (strlen($sUrl) > 0)
        {
            $sUrl    = "<redirectUrl>" . $sUrl . "</redirectUrl>";
        }
        
        if ($iStatusCode >= 0)
        {
            $sReturn    = <<<EOD
<?xml version="1.0"?>
<result>
    <statusCode>$iStatusCode</statusCode>
    <statusMessage>$mResult[0]</statusMessage>
    <invoiceNumber>$mResult[1]</message>
    $sUrl
</result>
EOD;
        }
        else {
            $sReturn    = <<<EOD
<?xml version="1.0"?>
<result>
    <statusCode>$iStatusCode</statusCode>
    <message>$mResult[1]</message>
    <errorCode>$mResult[2]</errorCode>
</result>
EOD;
        }

        echo $sReturn;
    }
}

function sleepSeconds ($iSeconds)
{
//    sleep($iSeconds);
}
