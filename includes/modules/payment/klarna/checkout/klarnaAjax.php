<?php
/**
 * Description
 * The AJAX file for JQeury. Preforms numerouse actions for Javascript
 *
 * @author Paul Peelen <Paul.Peelen@klarna.com>
 * @since 1.0 - 16 mar 2011
 */
session_start();
require_once ('inc.config.php');

@include_once('classes/class.KlarnaAPI.php');
@include_once('classes/class.KlarnaHTTPContext.php');
//@include_once('klarnautils.php');
require_once('../api/Klarna.php');
require_once(DIR_FS_DOCUMENT_ROOT.'includes/external/klarna/api/transport/xmlrpc-3.0.0.beta/lib/xmlrpc.inc');
require_once(DIR_FS_DOCUMENT_ROOT.'includes/external/klarna/api/transport/xmlrpc-3.0.0.beta/lib/xmlrpc_wrappers.inc');

@include_once('classes/class.KlarnaAjax.php');
@include_once('classes/class.KlarnaDispatcher.php');

Klarna::$debug = true;

$web_root = (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == null) ? 'http://' : 'https://';
$web_root .= $_SERVER['HTTP_HOST'];
if(!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == null) {
    if($_SERVER['SERVER_PORT'] != "80") {
        $web_root .= ':'.$_SERVER['SERVER_PORT'];
    }
}
else {
    if($_SERVER['SERVER_PORT'] != '443') {
        $web_root .= ':'.$_SERVER['SERVER_PORT'];
    }
}
$web_root .= substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], basename($_SERVER["SCRIPT_FILENAME"])));

$sCountry = 'se';
$aLoginData    = getEidAndSecret($sCountry);
$iMode = (strtolower(MODULE_PAYMENT_KLARNA_LIVEMODE) == "true") ? Klarna::LIVE : Klarna::BETA;

$sEID         = $aLoginData['eid'];
$sSecret     = $aLoginData['secret'];


$oKlarna = new Klarna();
$oKlarna->config($sEID, $sSecret,
    KlarnaCountry::SE, KlarnaLanguage::SV, KlarnaCurrency::SEK,
    $iMode,
    MODULE_PAYMENT_KLARNA_PC_TYPE,
    MODULE_PAYMENT_KLARNA_PC_URI,
    false);

$dispatcher = new KlarnaDispatcher(
    new KlarnaAjax ($oKlarna, $sEID, dirname(__FILE__) . '/', $web_root));
$dispatcher->dispatch ();

function getEidAndSecret ($sCountry)
{
    $aArray    = array();

    switch ( strtolower($sCountry) ) {
        case "se":
            $aArray['eid']         = (int)MODULE_PAYMENT_KLARNA_EID_SE;
            $aArray['secret']    = MODULE_PAYMENT_KLARNA_SECRET_SE;
            break;
        case "de":
            $aArray['eid']         = (int)MODULE_PAYMENT_KLARNA_EID_DE;
            $aArray['secret']    = MODULE_PAYMENT_KLARNA_SECRET_DE;
            break;
        case "dk":
            $aArray['eid']         = (int)MODULE_PAYMENT_KLARNA_EID_DK;
            $aArray['secret']    = MODULE_PAYMENT_KLARNA_SECRET_DK;
            break;
        case "nl":
            $aArray['eid']         = (int)MODULE_PAYMENT_KLARNA_EID_NL;
            $aArray['secret']    = MODULE_PAYMENT_KLARNA_SECRET_NL;
            break;
        case "fi":
            $aArray['eid']         = (int)MODULE_PAYMENT_KLARNA_EID_FI;
            $aArray['secret']    = MODULE_PAYMENT_KLARNA_SECRET_FI;
            break;
        case "no":
            $aArray['eid']         = (int)MODULE_PAYMENT_KLARNA_EID_NO;
            $aArray['secret']    = MODULE_PAYMENT_KLARNA_SECRET_NO;
            break;

        default:
            $aArray['eid'] = null;
            $aArray['secret'] = null;
        break;
    }

    return $aArray;
}
