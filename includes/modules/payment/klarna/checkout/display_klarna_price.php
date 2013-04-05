<?php
/**
 *  Copyright 2010 KLARNA AB. All rights reserved.
 *
 *  Redistribution and use in source and binary forms, with or without modification, are
 *  permitted provided that the following conditions are met:
 *
 *     1. Redistributions of source code must retain the above copyright notice, this list of
 *        conditions and the following disclaimer.
 *
 *     2. Redistributions in binary form must reproduce the above copyright notice, this list
 *        of conditions and the following disclaimer in the documentation and/or other materials
 *        provided with the distribution.
 *
 *  THIS SOFTWARE IS PROVIDED BY KLARNA AB "AS IS" AND ANY EXPRESS OR IMPLIED
 *  WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND
 *  FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL KLARNA AB OR
 *  CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 *  CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 *  SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 *  ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 *  NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
 *  ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 *  The views and conclusions contained in the software and documentation are those of the
 *  authors and should not be interpreted as representing official policies, either expressed
 *  or implied, of KLARNA AB.
 *
 */
include_once ('inc.config.php');
@include_once('../api/Klarna.php');
@include_once('classes/class.KlarnaAPI.php');
@include_once('classes/class.KlarnaHTTPContext.php');

/**
 * Dependencies from {@link http://phpxmlrpc.sourceforge.net/}
 * 
 * Ungly incude due to problems in XMLRPC lib (external)
 */
@include_once(DIR_FS_DOCUMENT_ROOT.'includes/external/klarna/api/transport/xmlrpc-3.0.0.beta/lib/xmlrpc.inc');
@include_once(DIR_FS_DOCUMENT_ROOT.'includes/external/klarna/api/transport/xmlrpc-3.0.0.beta/lib/xmlrpc_wrappers.inc');

global $kred_sek, $kred_nok, $kred_dkk, $kred_eur, $KRED_ISO3166_FI, $KRED_ISO3166_SE, $KRED_ISO3166_DK, $KRED_ISO3166_NO, $KRED_ISO3166_DE, $KRED_ISO3166_NL;

$terms_link = "#"; //replaced by NL to a different document
$lang_wo_tax = '';
$enabled = true;

//If logged in, grab country iso code 2.
$tmp_customer_country = false;
$hasgerman = $hasdutch = $hasfin = false;
$klarna_pzone = $pctable = $cc = false;

$totalSum = 1000;
$currency = 'sek';

$country    = "";
// Get the country specific settings based on currency
switch(strtolower($currency)) {
    case 'sek':
        $country = "se";
        break;

    case 'nok':
        $country = "no";
        break;

    case 'dkk':
        $country = "dk";
        break;

    case 'eur':
        if($tmp_customer_country === false) {
            if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
                $langarr=explode(",",$_SERVER["HTTP_ACCEPT_LANGUAGE"]);

                foreach ($langarr as $lang) {
                    $country = substr($lang,0,2);
                }
            }
        }
        else {
            $country = $tmp_customer_country;
        }
        break;
    default:
        $enabled = false;
        break;
}

$country = strtolower($country);
$countryCode = $country;

if (strtolower($country) == "" || strtolower($country) == "en")
{
    if (STORE_COUNTRY == '81')
        $country = 'de';
    else if (STORE_COUNTRY == '150')
        $country = 'nl';
    else if (STORE_COUNTRY == '57')
        $country = 'dk';
    else if (STORE_COUNTRY == '72')
        $country = 'fi';
    else if (STORE_COUNTRY == '203')
        $country = 'se';
    else if (STORE_COUNTRY == '160')
        $country = 'no';
    else
        $enabled = false;
}

switch ( strtolower($country) ) {
    case "se":
        $eid         = (int)MODULE_PAYMENT_PCKLARNA_EID_SE;
        $secret    = MODULE_PAYMENT_PCKLARNA_SECRET_SE;
        break;
    case "de":
        $eid         = (int)MODULE_PAYMENT_PCKLARNA_EID_DE;
        $secret    = MODULE_PAYMENT_PCKLARNA_SECRET_DE;
        break;
    case "dk":
        $eid         = (int)MODULE_PAYMENT_PCKLARNA_EID_DK;
        $secret    = MODULE_PAYMENT_PCKLARNA_SECRET_DK;
        break;
    case "nl":
        $eid         = (int)MODULE_PAYMENT_PCKLARNA_EID_NL;
        $secret    = MODULE_PAYMENT_PCKLARNA_SECRET_NL;
        break;
    case "fi":
        $eid         = (int)MODULE_PAYMENT_PCKLARNA_EID_FI;
        $secret    = MODULE_PAYMENT_PCKLARNA_SECRET_FI;
        break;
    case "no":
        $eid         = (int)MODULE_PAYMENT_PCKLARNA_EID_NO;
        $secret    = MODULE_PAYMENT_PCKLARNA_SECRET_NO;
        break;
    case "en":
        if (MODULE_PAYMENT_PCKLARNA_EID_DE > 0 && MODULE_PAYMENT_PCKLARNA_SECRET_DE != "")
        {
            $country    = "de";
            $eid         = (int)MODULE_PAYMENT_PCKLARNA_EID_DE;
            $secret    = MODULE_PAYMENT_PCKLARNA_SECRET_DE;
        }
        else if (MODULE_PAYMENT_PCKLARNA_EID_NL > 0 && MODULE_PAYMENT_PCKLARNA_SECRET_NL != "")
        {
            $country    = "nl";
            $eid         = (int)MODULE_PAYMENT_PCKLARNA_EID_NL;
            $secret    = MODULE_PAYMENT_PCKLARNA_SECRET_NL;
        }
        else if (MODULE_PAYMENT_PCKLARNA_EID_FI > 0 && MODULE_PAYMENT_PCKLARNA_SECRET_FI != "")
        {
            $country    = "fi";
            $eid         = (int)MODULE_PAYMENT_PCKLARNA_EID_FI;
            $secret    = MODULE_PAYMENT_PCKLARNA_SECRET_FI;
        }
        else {
            $enabled = false;
        }
        break;
}

$mode = (strtolower(MODULE_PAYMENT_PCKLARNA_LIVEMODE) == "true") ? Klarna::LIVE : Klarna::BETA;

$klarna = new Klarna();
$klarna->config($eid, $secret, $country, null, null, $mode, MODULE_PAYMENT_PCKLARNA_PC_TYPE, MODULE_PAYMENT_PCKLARNA_PC_URI, ($mode == Klarna::LIVE));
$klarna->setCountry($country);

switch ( $countryCode ) {
    case "se":
        $countryCode = "sv";
        break;
    case "dk":
        $countryCode = "db";
        break;
    case "no":
        $countryCode = "nb";
        break;
}

$KlarnaAPI = new KlarnaAPI($country, $countryCode, 'part', $totalSum, KlarnaFlags::PRODUCT_PAGE, $klarna, array(KlarnaPClass::ACCOUNT, KlarnaPClass::CAMPAIGN), '');
$web_root    = "http://localhost/git/standardkassa/";
$KlarnaAPI->addSetupValue('web_root', $web_root);
$KlarnaAPI->addSetupValue('asterisk', ($country == 'de' ? '*' : ''));
$KlarnaAPI->setPaths();

if ($totalSum > 0)
{
    $pclasses = $KlarnaAPI->aPClasses;
    $monthlyCost = array();
    $minRequired = array();
    
    if (is_array($pclasses))
    {
        foreach ($pclasses as $pclass)
        {
            $min = $pclass['pclass']->getMinAmount();
            
            if ($min != "" && $pclass['pclass']->getType() < 2)
                $minRequired[]    = $min;
            
            if ($pclass['monthlyCost'] != "")
                $monthlyCost[] = $pclass['monthlyCost'];
        }
    }
    
    asort($monthlyCost);
    asort($minRequired);
    $firstKey    = array_keys($monthlyCost);
    $minFirstKey= array_keys($minRequired);
    $cheapest    = $monthlyCost[$firstKey[0]];
    $minimum    = $minRequired[$minFirstKey[0]];
}

//$sMonthDefault    = $currencies->format(ceil($cheapest), false);
$sMonthDefault = $cheapest;
$sTableHtml = "";

foreach ($pclasses as $pclass)
{
    $sTableHtml    .= "<tr><td style='text-align: left'>";
     
    if ($pclass['pclass']->getType() == KlarnaPClass::ACCOUNT)
    {
        $sTableHtml .= KlarnaAPI::fetchFromLanguagePack('PPBOX_account', $countryCode, '/');
    }
    else
    {
        $sTableHtml .= $pclass['pclass']->getMonths() . " " . KlarnaAPI::fetchFromLanguagePack('PPBOX_th_month', $countryCode, '/');
    }
    
    $sTableHtml    .= "</td><td class='klarna_PPBox_pricetag'>";
    $sTableHtml    .= $currencies->format(ceil($pclass['monthlyCost']));
    $sTableHtml    .= "</td></tr>";
}

$aInputValues    = array();
$aInputValues['defaultMonth']    = $sMonthDefault;
$aInputValues['monthTable']        = $sTableHtml;
$aInputValues['eid']            = $eid;
$aInputValues['country']        = $country;
$aInputValues['nlBanner']        = ($country == 'nl' ? '<div class="nlBanner"><br /><br /><img src="images/klarna/account/notice_nl.jpg" /></div>' : "");

if ($enabled)
    $enabled    = (strtolower(MODULE_PAYMENT_PCKLARNA_STATUS) == 'true' ? true : false);

if ($enabled == true)
{
    echo $KlarnaAPI->retrieveHTML($aInputValues, null, 'html/productPrice/default/layout.html');
}
