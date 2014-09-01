<?php
/**
 * The index file. Demo of project
 *
 * @author Paul Peelen
 * @since 1.0 - 14 mar 2011
 */
session_start();
ob_start ();
include_once ('inc.config.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
       "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" lang="en_US" xml:lang="en_US">
    <head>
        <title>DEMO 1</title>
        <link type="text/css" rel="stylesheet" href="css/demo.css" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <script src="http://code.jquery.com/jquery-1.5.js" type="text/javascript"></script>
    </head>
    <body>
        <div class="page">
            <h1>Demo page Snabbkassa API</h1>
<?php
/**
* Dependencies from {@link http://phpxmlrpc.sourceforge.net/}
*
* Ungly incude due to problems in XMLRPC lib (external)
*/
require_once('../api/Klarna.php');
//~ require_once('../api/transport/xmlrpc-3.0.0.beta/lib/xmlrpc.inc'); // not in this directory
require_once(DIR_FS_DOCUMENT_ROOT.'includes/external/klarna/api/transport/xmlrpc-3.0.0.beta/lib/xmlrpc.inc'); 
require_once(DIR_FS_DOCUMENT_ROOT.'includes/external/klarna/api/transport/xmlrpc-3.0.0.beta/lib/xmlrpc_wrappers.inc');

require_once ('classes/class.KlarnaAPI.php');

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
try {
    $oKlarna = new Klarna();
    $oKlarna->config(EID, SHARED_SECRET,
        null, null, null,
        Klarna::BETA,
        MODULE_PAYMENT_KLARNA_PC_TYPE,
        array(
            'user' => DB_USER,
            'passwd' => DB_PASSWD,
            'dsn' => 'localhost',
            'db' => DB_NAME,
            'table' => DB_TABLE),
        false);
    $oKlarna->setCountry(KlarnaCountry::SE);

} catch (Exception $e) {
    Klarna::printDebug('error creating api instance', $e);
}

?>
            <table>
                <tr>
                    <td><label for="herp_derp">steal it!</label></td>
                    <td align="right">
                    <input type="radio" value="herp_derp" name="payment" id="herp_derp">
                    </td>
                </tr>
            </table>

            <table>
                <tr>
                    <td><label for="klarna_invoice">Invoice</label></td>
                    <td align="right">
                    <input type="radio" value="klarna_invoice" name="payment" id="klarna_invoice">
                    </td>
                </tr>
            </table>
<?php
$oApi = new KlarnaAPI(
        KlarnaCountry::DE, KlarnaLanguage::DE, 'invoice',
        1000,
        KlarnaFlags::CHECKOUT_PAGE,
        $oKlarna,
        null,
        dirname(__FILE__) . "/");
$oApi->addSetupValue('eid', EID);
$oApi->addSetupValue('web_root', $web_root);
$oApi->setPaths();

// Call the PHP API and ask for the ILT questions using function "checkILT"
// ILT Result mocked below
$aIlt = array(
        'children_under_18' => array('text' => 'Question 1',
            'type' => 'dropdown',
            'values' => array(0 => array('name' => 'value 1', 'value' => '1'))),
        'children_under_19' => array('text' => 'Question 1',
            'type' => 'dropdown',
            'values' => array(0 => array('name' => 'value 1', 'value' => '1')))
        );
//$oApi->setIltQuestions($aIlt);

echo $oApi->retrieveHTML();
?>
            <table>
                <tr>
                    <td><label for="klarna_partPayment">Part Payment</label></td>
                    <td align="right">
                        <input type="radio" value="klarna_partPayment" name="payment" id="klarna_partPayment">
                    </td>
                </tr>
            </table>
<?php
$oApi2 = new KlarnaAPI(
    KlarnaCountry::SE, KlarnaLanguage::SV, 'part',
    1000,
    KlarnaFlags::CHECKOUT_PAGE,
    $oKlarna,
    null,
    dirname(__FILE__) . "/");
$oApi2->addSetupValue('eid', EID);
$oApi2->addSetupValue('web_root', $web_root);
$oApi2->setPaths();
echo $oApi2->retrieveHTML(array('gender' => 'part_gender'));
?>
            <br/>
            <br/>
        </div>
    </body>
</html>
