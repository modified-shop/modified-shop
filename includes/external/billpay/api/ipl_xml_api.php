<?php
/**
 * @author Unknown Artist (support@billpay.de)
 * @copyright Copyright 2010 BillPay GmbH
 * @license commercial
 */

define('IPL_CORE_XML_PROLOG', "<?xml version=\"1.0\" encoding=\"UTF-8\"?>");
define('IPL_CORE_API_VERSION', "1.6.1");
define('IPL_CORE_HTTP_REQUEST_CHAR_SET', "UTF-8");

# HTTP_CLIENT may be defined by TestRunner
if (!defined('IPL_CORE_HTTP_CLIENT')) {
    define('IPL_CORE_HTTP_CLIENT', "curl");
}

define('IPL_CORE_XML_PARSER', "xmlParser");
define('IPL_CORE_FOLLOW_REDIRECT', true);
define('IPL_CORE_MAX_REDIRECTS', 3);
define('IPL_CORE_SOCKET_TIMEOUT', 10);
define('IPL_CORE_CURL_TIMEOUT', 25);
define('IPL_CORE_CURL_CONNECTION_TIMEOUT', 10);


define('IPL_CORE_PAYMENT_TYPE_INVOICE', 1);
define('IPL_CORE_PAYMENT_TYPE_DIRECT_DEBIT', 2);
define('IPL_CORE_PAYMENT_TYPE_RATE_PAYMENT', 3);
define('IPL_CORE_PAYMENT_TYPE_PAY_LATER', 4);
define('IPL_CORE_PAYMENT_TYPE_PAY_LATER_COLLATERAL_PROMISE', 7);
define('IPL_CORE_PAYMENT_TYPE_INVOICE_COLLATERAL_PROMISE', 8);
define('IPL_CORE_PAYMENT_TYPE_DIRECT_DEBIT_COLLATERAL_PROMISE', 9);

define('IPL_TRAVEL_TYPE_FLIGHT', 1);
define('IPL_TRAVEL_TYPE_PACKAGE_WITH_FLIGHT', 2);
define('IPL_TRAVEL_TYPE_ITEM_COMBINATION_WITH_FLIGHT', 3);
define('IPL_TRAVEL_TYPE_ITEM_COMBINATION_WITHOUT_FLIGHT', 4);
define('IPL_TRAVEL_TYPE_HOTEL', 5);
define('IPL_TRAVEL_TYPE_TRAVEL_INSURANCE', 6);
define('IPL_TRAVEL_TYPE_CAR_RENTAL', 7);

/**
 *  0: Success
 *  1: Timeout
 *  2: Socket error
 *  3: cUrl init error
 *  4: Invalid HTTP response
 *  5: Invalid HTTP header
 *  6: HTTP error code received
 *  7: Request url is empty
 *  8: Unknown HTTP client
 *  9: Unknown XML parser lib
 * 10: Invalid XML response received
 * 11: Feature not implemented
 * 12: Error parsing result
 * 13: cUrl lib not loaded
 * 14: parse function not fount
 * 15: simpleXml lib not loaded
 * 16: redirect response received
 * 17: Unsupported protocol version
 * 18: Too many redirects
 */
$ipl_core_error_code = 0;
$ipl_core_error_msg = '';

$ipl_core_api_error_code = 0;
$ipl_core_api_customer_message = '';
$ipl_core_api_merchant_message = '';

$ipl_core_last_request_url = '';


/**
 * Send a HTTP request
 * @param string $requestUrl
 * @param string $requestData
 * @return array|bool
 */
function ipl_core_send($requestUrl, $requestData)
{
    global $ipl_core_error_code;
    global $ipl_core_error_msg;

    if (empty($requestUrl)) {
        $ipl_core_error_code = 7;
        $ipl_core_error_msg = 'IPL request url is not set';
        return false;
    }

    switch (IPL_CORE_HTTP_CLIENT) {
        case 'fake':
            $resultXml = ipl_fake_send_request($requestUrl, $requestData);
            break;
        case 'curl':
            $resultXml = ipl_core_send_curl_request($requestUrl, $requestData);
            break;
        default:
            $ipl_core_error_code = 8;
            $ipl_core_error_msg = 'Unknown HTTP client: ' . IPL_CORE_HTTP_CLIENT;
            return false;
    }

    if (!$resultXml) {
        return false;
    }

    // load the XML data
    $transformedData = ipl_core_load_xml($resultXml);

    if (!$transformedData) {
        return false;
    }

    return array($resultXml, $transformedData);
}

/**
 * Send a HTTP request using cUrl lib
 * @param string $requestUrl
 * @param string $requestData
 * @return bool|string
 */
function ipl_core_send_curl_request($requestUrl, $requestData)
{
    global $ipl_core_error_code;
    global $ipl_core_error_msg;

    if (!function_exists('curl_init')) {
        $ipl_core_error_code = 13;
        $ipl_core_error_msg = 'cUrl lib has not been loaded';
        return false;
    }

    $ch = curl_init();

    if (!$ch) {
        $ipl_core_error_code = 1;
        $ipl_core_error_msg = 'Cannot initialize cUrl';
        return false;
    }

    // 	set CURL options
    curl_setopt($ch, CURLOPT_URL, $requestUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $requestData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, IPL_CORE_CURL_TIMEOUT);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, IPL_CORE_CURL_CONNECTION_TIMEOUT);

    // This prevents a known issue with CURLOPT_FOLLOWLOCATION
    if (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) {
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, IPL_CORE_FOLLOW_REDIRECT);
        curl_setopt($ch, CURLOPT_MAXREDIRS, IPL_CORE_MAX_REDIRECTS);
    }

    // send request
    $result = curl_exec($ch);

    if (!$result) {
        $ipl_core_error_code = 1;
        $ipl_core_error_msg = 'cUrl error: ' . curl_error($ch);
        return false;
    }

    $info = curl_getinfo($ch);

    curl_close($ch);

    $httpCode = $info['http_code'];
    if ($httpCode != 200) {
        $ipl_core_error_code = 200;
        $ipl_core_error_msg = 'Error connecting to BillPay server (HTTP status code: ' . $httpCode . ')';
        return false;
    }

    return ipl_core_parse_result($result);
}


/**
 *
 */
function ipl_core_parse_result($responseData)
{
    global $ipl_core_error_code;
    global $ipl_core_error_msg;

    $data = '';
    switch (IPL_CORE_HTTP_CLIENT) {
        case 'curl':
            $data = $responseData;
            break;
        default:
            $ipl_core_error_code = 8;
            $ipl_core_error_msg = 'Unknown HTTP client: ' . IPL_CORE_HTTP_CLIENT;
            return false;
    }

    return $data;
}

/**
 * Load the data from the response as xml
 *
 */
function ipl_core_load_xml($xmlDataString)
{
    global $ipl_core_error_code;
    global $ipl_core_error_msg;

    global $ipl_core_api_error_code;
    global $ipl_core_api_customer_message;
    global $ipl_core_api_merchant_message;


    switch (IPL_CORE_XML_PARSER) {
        case 'simpleXml':

            if (!function_exists('simplexml_load_string')) {
                $ipl_core_error_code = 15;
                $ipl_core_error_msg = 'simpleXml lib has not been loaded';
                return false;
            }

            $xml = simplexml_load_string($xmlDataString);

            if (!$xml) {
                $ipl_core_error_code = 10;
                $ipl_core_error_msg = 'Invalid XML reponse received';
                return false;
            } else {
                $attr = $xml->attributes();
                $ipl_core_api_error_code = (int)$attr->error_code;
                $ipl_core_api_customer_message = (string)$attr->customer_message;
                $ipl_core_api_merchant_message = (string)$attr->merchant_message;
            }
            break;

        case 'preg':
            $errorCode = ipl_core_get_xml_attribute_value('data', 'error_code', $xmlDataString);
            if ($errorCode === false) {
                return false;
            }

            if ($errorCode > 0) {
                $ipl_core_api_customer_message = ipl_core_get_xml_attribute_value('data', 'customer_message', $xmlDataString);
                $ipl_core_api_merchant_message = ipl_core_get_xml_attribute_value('data', 'merchant_message', $xmlDataString);
            }
            break;

        case 'xmlParser':
            $parser = new XMLParserIPL($xmlDataString);

            if ($parser->Parse() == false) {
                $xmlError = $parser->getError();
                $ipl_core_error_code = 10;
                $ipl_core_error_msg = "Invalid XML response received: $xmlError";
                return false;
            }

            $xml = $parser->document;

            if (!$xml) {
                $ipl_core_error_code = 10;
                $ipl_core_error_msg = 'Invalid XML response received';
                return false;
            } else {
                $ipl_core_api_error_code = (int)$xml->tagAttrs['error_code'];
                $ipl_core_api_customer_message = ipl_core_decode((string)$xml->tagAttrs['customer_message']);
                $ipl_core_api_merchant_message = ipl_core_decode((string)$xml->tagAttrs['merchant_message']);
            }
            break;

        default:
            $ipl_core_error_code = 9;
            $ipl_core_error_msg = 'Unknown XML parser lib: ' . IPL_CORE_XML_PARSER;
            return false;
            break;
    }
    return $xml;
}

// ERROR HELPER FUNCTIONS
function ipl_core_has_api_error()
{
    global $ipl_core_api_error_code;
    return $ipl_core_api_error_code > 0;
}

function ipl_core_get_api_error_info()
{
    global $ipl_core_api_error_code;
    global $ipl_core_api_customer_message;
    global $ipl_core_api_merchant_message;

    $info = array(
        'error_code' => $ipl_core_api_error_code,
        'customer_message' => $ipl_core_api_customer_message,
        'merchant_message' => $ipl_core_api_merchant_message
    );

    return $info;
}

function ipl_core_reset_error_codes()
{
    global $ipl_core_api_error_code;
    global $ipl_core_error_code;
    global $ipl_core_api_error_code;
    global $ipl_core_api_customer_message;
    global $ipl_core_api_merchant_message;
    global $ipl_core_last_request_url;

    $ipl_core_api_error_code = 0;
    $ipl_core_error_code = 0;

    $ipl_core_error_msg = '';
    $ipl_core_api_customer_message = '';
    $ipl_core_api_merchant_message = '';

    $ipl_core_last_request_url = '';
}

function ipl_core_has_internal_error()
{
    global $ipl_core_error_code;
    return $ipl_core_error_code > 0;
}

function ipl_core_get_internal_error()
{
    global $ipl_core_error_code;
    return $ipl_core_error_code;
}

function ipl_core_get_internal_error_msg()
{
    global $ipl_core_error_msg;
    return $ipl_core_error_msg;
}


// XML PARSING UTILS
function ipl_core_get_attribute_value($s)
{
    if (is_bool($s)) {
        return ($s == true ? '1' : '0');
    } else {
        return ipl_core_xml_escape($s);
    }
}

function ipl_core_append_slash($s)
{
    $s = trim($s);
    if (substr($s, strlen($s) - 1) != "/") {
        $s = $s . "/";
    }
    return $s;
}

function ipl_core_build_list_tag($tag_name, $child_tag_name, $a)
{
    $article_string = "<$tag_name>";
    foreach ($a as $list_item) {
        $attr_str = ipl_core_build_attr_string($list_item);
        $article_string = "$article_string<$child_tag_name $attr_str/>";
    }
    $article_string = "$article_string</$tag_name>";
    return $article_string;
}

/**
 * @param SimpleXmlElement $xmlElement
 * @param SimpleXmlElement $parentNode
 * @param array $content
 * @return SimpleXmlElement
 * @internal param string $parentNodeName
 */
function ipl_core_build_xml_from_array($xmlElement, $parentNode, $content)
{

    foreach ($content as $contentKey => $contentItem) {

        if (is_a($contentItem, 'ArrayObject')) {
            $contentItem = (array)$contentItem;
        }

        if (is_array($contentItem)) {

            if (is_int($contentKey)) {
                $childXml = $parentNode->addChild($xmlElement->getName());
                ipl_core_build_xml_from_array($childXml, $childXml, $contentItem);

                // check if we have a sequential array or not
            } elseif (array_keys($contentItem) === range(0, count($contentItem) - 1)) {
                $childXml = new SimpleXMLElement("<$contentKey></$contentKey>");
                ipl_core_build_xml_from_array($childXml, $parentNode, $contentItem);

            } else {
                $childXml = $parentNode->addChild($contentKey);
                ipl_core_build_xml_from_array($childXml, $childXml, $contentItem);
            }
        } else {
            $xmlElement->addAttribute($contentKey, $contentItem);
        }
    }
    return $xmlElement;
}

function ipl_core_build_attr_list_tag($tagName, $attributes, $childTagName, $childAttributes)
{

    $parentString = ipl_core_build_open_tag($tagName, $attributes)
        . ipl_core_build_close_tag($tagName, ipl_core_build_list($childTagName, $childAttributes));

    return $parentString;
}

function ipl_core_build_list($tagName, $attributes)
{

    $list = '';
    foreach ((array)$attributes as $attribute) {
        $list .= '<' . $tagName . ' ' . ipl_core_build_attr_string($attribute) . '/>';
    }
    return $list;
}

/**
 * Joins associative array keys with values and returns them as a string
 * @param $a Array Values to be joined
 * @return string Joined string
 */
function ipl_core_build_attr_string($a)
{
    $attr_str = "";

    if (count($a) > 0) {
        foreach ($a as $key => $value) {
            $attr_str = "$attr_str$key=\"" . ipl_core_xml_escape($value) . "\" ";
        }
    }
    return $attr_str;
}

function ipl_core_build_request_xml($attributes, $content)
{
    $attributes['api_version'] = IPL_CORE_API_VERSION;

    $xml = "<data";
    foreach ($attributes as $name => $value) {
        $xml .= " $name=\"" . ipl_core_get_attribute_value($value) . "\"";
    }

    $xml .= ">" . implode('', $content) . "</data>";
    return IPL_CORE_XML_PROLOG . $xml;
}

/**
 * Escapes XML characters from a string
 * @param $value string String to escape
 * @return string Escaped string
 */
function ipl_core_xml_escape($value)
{
    $search = array("&", "\"", "<", ">", "'");
    $replace = array("&amp;", "&quot;", "&lt;", "&gt;", "&apos;");

    return str_replace($search, $replace, $value);
}

function ipl_core_build_closed_tag($tagName, $a)
{
    if (!$a || count($a) == 0) {
        return "";
    }

    $s = ipl_core_build_attr_string($a);
    return "<$tagName $s/>";
}

function ipl_core_build_list_ctag($tagName, $a)
{
    $xml = ipl_core_build_open_tag($tagName);
    foreach ($a as $subTagName => $content) {
        $xml = $xml . ipl_core_build_closed_ctag($subTagName, $content);
    }
    return ipl_core_build_close_tag($tagName, $xml);
}

function ipl_core_build_closed_ctag($tagName, $a)
{
    if (!$a || count($a) == 0) {
        return "";
    }
    $s = ipl_core_build_ctag_string($a);
    return "<{$tagName}>{$s}</{$tagName}>";
}

function ipl_core_build_ctag_string($a)
{
    return "<![CDATA[" . $a . "]]>";
}

function ipl_core_build_open_tag($sTagName, $aAttributes = array())
{
    if (empty($aAttributes)) {
        $sAttributes = "";
    } else {
        $sAttributes = ' ' . ipl_core_build_attr_string($aAttributes);
    }

    return '<' . $sTagName . $sAttributes . '>';
}

function ipl_core_build_close_tag($tagName, $xml)
{

    return "$xml </$tagName>";
}

function ipl_core_add_body($xml, $body)
{
    return $xml . $body;
}

function ipl_core_decode($s)
{
    return $s;
}

function ipl_create_hash($valueToHash)
{
    return sha1($_SERVER['SERVER_NAME'] . $valueToHash);
}

function ipl_create_random($forceFallback = false)
{
    // possible safest way to create random strings (PHP > 5.3):
    if ($forceFallback === false && function_exists('openssl_random_pseudo_bytes')) {
        return bin2hex(openssl_random_pseudo_bytes(16)); // returns 128 character hex string
    }

    // fallback for php pre 5.3... we get ugly
    $randString = '';
    for ($i = 0; $i < 32; $i++) {
        mt_srand(mt_rand(0, mt_getrandmax()));
        $char = mt_rand(0, 15);
        if ($char <= 9) {
            $char += 48;
        } else {
            $char += 87;
        }
        $randString .= chr($char);
    }
    return $randString;
}

// Simplified xml parser. tag/attribute pairs must be unique.
function ipl_core_get_xml_attribute_value($tagName, $attributeName, $xml)
{
    global $ipl_core_error_code;
    global $ipl_core_error_msg;
    if (preg_match("/<$tagName([^>]*)\b$attributeName\b=\"([^\"]*)\"/", $xml, $result)) {
        if (count($result) == 0) {
            $ipl_core_error_code = 12;
            $ipl_core_error_msg = "Invalid xml result Attribute $attributeName not found for tag $tagName";
            return false;
        } else {
            return $result[2];
        }
    } else {
        $ipl_core_error_code = 12;
        $ipl_core_error_msg = 'Error parsing xml result with preg';
        return false;
    }
}


// API XML RESPONSE PARSING
function ipl_core_parse_module_config_response($xml)
{
    global $ipl_core_error_code;
    global $ipl_core_error_msg;

    $data = array();
    switch (IPL_CORE_XML_PARSER) {
        case 'simpleXml';
            if ($xml->minvalue) {
                $minAttrs = $xml->minvalue->attributes();
                $data['invoicemin'] = (int)$minAttrs->invoice;
                $data['invoicebusinessmin'] = (int)$minAttrs->invoicebusiness;
                $data['directdebitmin'] = (int)$minAttrs->directdebit;
                $data['hirepurchasemin'] = (int)$minAttrs->hirepurchase;
            }

            if ($xml->limit) {
                $limitAttr = $xml->limit->attributes();
                $data['invoicestatic'] = (int)$limitAttr->invoicestatic;
                $data['invoicebusinessstatic'] = (int)$limitAttr->invoicebusinessstatic;
                $data['directdebitstatic'] = (int)$limitAttr->directdebitstatic;
                $data['hirepurchasestatic'] = (int)$limitAttr->hirepurchasestatic;
            }

            if ($xml->permissions) {
                $permAttr = $xml->permissions->attributes();
                $data['active'] = ((int)$permAttr->active) == 1 ? true : false;
                $data['invoiceallowed'] = ((int)$permAttr->invoiceallowed) == 1 ? true : false;
                $data['invoicebusinessallowed'] = ((int)$permAttr->invoicebusinessallowed) == 1 ? true : false;
                $data['directdebitallowed'] = ((int)$permAttr->directdebitallowed) == 1 ? true : false;
                $data['hirepurchaseallowed'] = ((int)$permAttr->hirepurchaseallowed) == 1 ? true : false;
            }

            if ($xml->hire_purchase) {
                $data['terms'] = array();
                foreach ($xml->hire_purchase->terms->children() as $termTag) {
                    $data['terms'][] = (int)$termTag;
                }
            }
            break;
        case 'xmlParser':
            if (isset($xml->minvalue)) {
                $data['invoicemin'] = (int)$xml->minvalue[0]->tagAttrs['invoice'];
                $data['invoicebusinessmin'] = (int)$xml->minvalue[0]->tagAttrs['invoicebusiness'];
                $data['directdebitmin'] = (int)$xml->minvalue[0]->tagAttrs['directdebit'];
                $data['hirepurchasemin'] = (int)$xml->minvalue[0]->tagAttrs['hirepurchase'];
            }

            if (isset($xml->limit)) {
                $data['invoicestatic'] = (int)$xml->limit[0]->tagAttrs['invoicestatic'];
                $data['invoicebusinessstatic'] = (int)$xml->limit[0]->tagAttrs['invoicebusinessstatic'];
                $data['directdebitstatic'] = (int)$xml->limit[0]->tagAttrs['directdebitstatic'];
                $data['hirepurchasestatic'] = (int)$xml->limit[0]->tagAttrs['hirepurchasestatic'];
            }

            if (isset($xml->permissions)) {
                $data['active'] = ((int)$xml->permissions[0]->tagAttrs['active']) == 1 ? true : false;
                $data['invoicebusinessallowed'] = ((int)$xml->permissions[0]->tagAttrs['invoicebusinessallowed']) == 1 ? true : false;
                $data['invoiceallowed'] = ((int)$xml->permissions[0]->tagAttrs['invoiceallowed']) == 1 ? true : false;
                $data['directdebitallowed'] = ((int)$xml->permissions[0]->tagAttrs['directdebitallowed']) == 1 ? true : false;
                $data['hirepurchaseallowed'] = ((int)$xml->permissions[0]->tagAttrs['hirepurchaseallowed']) == 1 ? true : false;
            }

            if (isset($xml->hire_purchase)) {
                $data['terms'] = array();

                foreach ($xml->hire_purchase[0]->terms[0]->tagChildren as $termTag) {
                    $data['terms'][] = $termTag->tagData;
                }
            }
            break;
        default:
            $ipl_core_error_code = 9;
            $ipl_core_error_msg = 'Unknown XML parser lib: ' . IPL_CORE_XML_PARSER;
            return false;
    }

    return $data;
}

function ipl_core_parse_validation_response($xml)
{
    global $ipl_core_error_code;
    global $ipl_core_error_msg;

    # TODO: this function doesn't do anything

    $data = array();
    switch (IPL_CORE_XML_PARSER) {
        case 'simpleXml';
            // Noting to do here
            break;
        case 'xmlParser';
            // Noting to do here
            break;
        default:
            $ipl_core_error_code = 9;
            $ipl_core_error_msg = 'Unknown XML parser lib: ' . IPL_CORE_XML_PARSER;
            return false;
    }

    return $data;
}

function ipl_core_parse_preauthorize_response($xml)
{
    global $ipl_core_error_code;
    global $ipl_core_error_msg;

    $data = array();
    switch (IPL_CORE_XML_PARSER) {
        case 'simpleXml';
            $attr = $xml->attributes();

            if ($attr) {
                if ($attr->status) {
                    $data['status'] = (string)$attr->status;
                }

                if ($attr->bptid) {
                    $data['bptid'] = (string)$attr->bptid;
                }
            }

            if ($xml->corrected_address) {
                $correctedAttr = $xml->corrected_address->attributes();
                $data['corrected_street'] = (string)$correctedAttr->street;
                $data['corrected_street_no'] = (string)$correctedAttr->streetNo;
                $data['corrected_zip'] = (string)$correctedAttr->zip;
                $data['corrected_city'] = (string)$correctedAttr->city;
                $data['corrected_country'] = (string)$correctedAttr->country;
            }

            if ($xml->invoice_bank_account) {
                $invoiceAttr = $xml->invoice_bank_account->attributes();
                $data['account_holder'] = (string)$invoiceAttr->account_holder;
                $data['account_number'] = (string)$invoiceAttr->account_number;
                $data['bank_code'] = (string)$invoiceAttr->bank_code;
                $data['bank_name'] = (string)$invoiceAttr->bank_name;
                $data['invoice_reference'] = (string)$invoiceAttr->invoice_reference;
                $data['invoice_duedate'] = (string)$invoiceAttr->invoice_duedate;
            }

            break;
        case 'xmlParser';
            if (isset($xml->tagAttrs['status'])) {
                $data['status'] = ipl_core_decode((string)$xml->tagAttrs['status']);
            }

            if (isset($xml->async_capture_params)) {
                $data['async_amount'] = ipl_core_decode((string)$xml->async_capture_params[0]->tagAttrs['amount']);
                $data['external_redirect_url'] = ipl_core_decode((string)$xml->async_capture_params[0]->external_redirect_url[0]->tagData);
                $data['rate_plan_url'] = ipl_core_decode((string)$xml->async_capture_params[0]->rate_plan_url[0]->tagData);
            }
            if (isset($xml->campaign)) {
                $data['campaign_type'] = ipl_core_decode((string)$xml->campaign[0]->type[0]->tagData);
                $data['campaign_display_text'] = ipl_core_decode((string)$xml->campaign[0]->display_text[0]->tagData);
                $data['campaign_display_image_url'] = ipl_core_decode((string)$xml->campaign[0]->display_image_url[0]->tagData);
            }

            if (isset($xml->tagAttrs['bptid'])) {
                $data['bptid'] = ipl_core_decode((string)$xml->tagAttrs['bptid']);
            }

            if (isset($xml->corrected_address)) {
                $data['corrected_street'] = ipl_core_decode((string)$xml->corrected_address[0]->tagAttrs['street']);
                $data['corrected_street_no'] = ipl_core_decode((string)$xml->corrected_address[0]->tagAttrs['streetno']);
                $data['corrected_zip'] = ipl_core_decode((string)$xml->corrected_address[0]->tagAttrs['zip']);
                $data['corrected_city'] = ipl_core_decode((string)$xml->corrected_address[0]->tagAttrs['city']);
                $data['corrected_country'] = ipl_core_decode((string)$xml->corrected_address[0]->tagAttrs['country']);
            }

            if (isset($xml->invoice_bank_account)) {
                $data['account_holder'] = ipl_core_decode((string)$xml->invoice_bank_account[0]->tagAttrs['account_holder']);
                $data['account_number'] = ipl_core_decode((string)$xml->invoice_bank_account[0]->tagAttrs['account_number']);
                $data['bank_code'] = ipl_core_decode((string)$xml->invoice_bank_account[0]->tagAttrs['bank_code']);
                $data['bank_name'] = ipl_core_decode((string)$xml->invoice_bank_account[0]->tagAttrs['bank_name']);
                $data['invoice_reference'] = ipl_core_decode((string)$xml->invoice_bank_account[0]->tagAttrs['invoice_reference']);
                $data['invoice_duedate'] = ipl_core_decode((string)$xml->invoice_bank_account[0]->tagAttrs['invoice_duedate']);
            }

            if (isset($xml->hire_purchase[0])) {
                $data = ipl_core_parse_instalment_information($xml->hire_purchase[0], $data);
            }
            $data = ipl_core_parse_tc_documents($xml, $data);
            $data = ipl_core_parse_payment_infos($xml, $data);

            if (isset($xml->validation_errors[0])) {
                $validation_errors = array(
                    'customer' => array(),
                    'merchant' => array(),
                );
                foreach ($xml->validation_errors[0]->tagChildren as $error) {
                    $validation_errors['customer'][] = $error->tagAttrs['customer_message'];
                    $validation_errors['merchant'][] = $error->tagAttrs['merchant_message'];
                }
                $data['validation_errors'] = $validation_errors;
            }
            break;
        default:
            $ipl_core_error_code = 9;
            $ipl_core_error_msg = 'Unknown XML parser lib: ' . IPL_CORE_XML_PARSER;
            return false;
    }

    return $data;
}


function ipl_core_parse_prescore_response($xml)
{
    global $ipl_core_error_code;
    global $ipl_core_error_msg;

    $data = array();
    switch (IPL_CORE_XML_PARSER) {
        case 'simpleXml':
            $attr = $xml->attributes();

            if ($attr) {
                if ($attr->status) {
                    $data['status'] = (string)$attr->status;
                }

                if ($attr->bptid) {
                    $data['bptid'] = (string)$attr->bptid;
                }
            }

            if ($xml->corrected_address) {
                $correctedAttr = $xml->corrected_address->attributes();
                $data['corrected_street'] = (string)$correctedAttr->street;
                $data['corrected_street_no'] = (string)$correctedAttr->streetNo;
                $data['corrected_zip'] = (string)$correctedAttr->zip;
                $data['corrected_city'] = (string)$correctedAttr->city;
                $data['corrected_country'] = (string)$correctedAttr->country;
            }

            if ($xml->invoice_bank_account) {
                $invoiceAttr = $xml->invoice_bank_account->attributes();
                $data['account_holder'] = (string)$invoiceAttr->account_holder;
                $data['account_number'] = (string)$invoiceAttr->account_number;
                $data['bank_code'] = (string)$invoiceAttr->bank_code;
                $data['bank_name'] = (string)$invoiceAttr->bank_name;
                $data['invoice_reference'] = (string)$invoiceAttr->invoice_reference;
                $data['invoice_duedate'] = (string)$invoiceAttr->invoice_duedate;
            }
            break;

        case 'xmlParser':
            if (isset($xml->tagAttrs['status'])) {
                $data['status'] = ipl_core_decode((string)$xml->tagAttrs['status']);
            }

            if (isset($xml->tagAttrs['bptid'])) {
                $data['bptid'] = ipl_core_decode((string)$xml->tagAttrs['bptid']);
            }

            if (isset($xml->corrected_address)) {
                $data['corrected_street'] = ipl_core_decode((string)$xml->corrected_address[0]->tagAttrs['street']);
                $data['corrected_street_no'] = ipl_core_decode((string)$xml->corrected_address[0]->tagAttrs['streetno']);
                $data['corrected_zip'] = ipl_core_decode((string)$xml->corrected_address[0]->tagAttrs['zip']);
                $data['corrected_city'] = ipl_core_decode((string)$xml->corrected_address[0]->tagAttrs['city']);
                $data['corrected_country'] = ipl_core_decode((string)$xml->corrected_address[0]->tagAttrs['country']);
            }

            if (isset($xml->invoice_bank_account)) {
                $data['account_holder'] = ipl_core_decode((string)$xml->invoice_bank_account[0]->tagAttrs['account_holder']);
                $data['account_number'] = ipl_core_decode((string)$xml->invoice_bank_account[0]->tagAttrs['account_number']);
                $data['bank_code'] = ipl_core_decode((string)$xml->invoice_bank_account[0]->tagAttrs['bank_code']);
                $data['bank_name'] = ipl_core_decode((string)$xml->invoice_bank_account[0]->tagAttrs['bank_name']);
                $data['invoice_reference'] = ipl_core_decode((string)$xml->invoice_bank_account[0]->tagAttrs['invoice_reference']);
                $data['invoice_duedate'] = ipl_core_decode((string)$xml->invoice_bank_account[0]->tagAttrs['invoice_duedate']);
            }

            if (isset($xml->allowed_methods)) {

                $array_payments_allowed = array();
                $array_additional_data = array();
                $_terms = array();

                foreach ($xml->allowed_methods[0]->payment_method as $pam) {

                    $array_payments_allowed[] = ipl_core_decode((string)$pam->name[0]->tagData);

                    $array = array();
                    $array['payment_type'] = ipl_core_decode((string)$pam->payment_type[0]->tagData);
                    $array['name'] = ipl_core_decode((string)$pam->name[0]->tagData);
                    $array['customer_group'] = ipl_core_decode((string)$pam->customer_group[0]->tagData);

                    if (isset($pam->additional_data[0]->rate_options[0])) {

                        foreach ($pam->additional_data[0]->rate_options[0]->tagChildren as $rate_info) {

                            $array_calculation = array();
                            $array_dues = array();

                            foreach ($rate_info->calculation[0]->tagChildren as $tag) {
                                $array_calculation[$tag->tagName] = ipl_core_decode((string)$tag->tagData);
                            }

                            foreach ($rate_info->dues[0]->tagChildren as $tag) {
                                $array_dues[] = array(
                                    'type' => ipl_core_decode((string)$tag->tagAttrs['type']),
                                    'date' => '0',
                                    'value' => ipl_core_decode((string)$tag->tagData)
                                );
                            }

                            $term = ipl_core_decode((string)$rate_info->tagAttrs['term']);
                            $_terms[] = $term;

                            $array_additional_data[$term]['calculation'] = $array_calculation;
                            $array_additional_data[$term]['dues'] = $array_dues;
                        }


                        $array['aditional_data'] = $array_additional_data;
                        $data['_rate_info'] = $array_additional_data;
                    }
                    $data['_payments_allowed_all'][$array['name']] = $array;
                }
                $data['_payments_allowed'] = $array_payments_allowed;
                $data['_terms'] = $_terms;
            }

            $data = ipl_core_parse_tc_documents($xml, $data);
            $data = ipl_core_parse_payment_infos($xml, $data);
            break;

        default:
            $ipl_core_error_code = 9;
            $ipl_core_error_msg = 'Unknown XML parser lib: ' . IPL_CORE_XML_PARSER;
            return false;
    }

    return $data;
}


function ipl_core_parse_capture_response($xml)
{
    global $ipl_core_error_code;
    global $ipl_core_error_msg;

    $data = array();
    switch (IPL_CORE_XML_PARSER) {
        case 'simpleXml';
            if ($xml->invoice_bank_account) {
                $invoiceAttr = $xml->invoice_bank_account->attributes();
                $data['account_holder'] = (string)$invoiceAttr->account_holder;
                $data['account_number'] = (string)$invoiceAttr->account_number;
                $data['bank_code'] = (string)$invoiceAttr->bank_code;
                $data['bank_name'] = (string)$invoiceAttr->bank_name;
                $data['invoice_reference'] = (string)$invoiceAttr->invoice_reference;
                $data['invoice_duedate'] = (string)$invoiceAttr->invoice_duedate;
            }
            break;

        case 'xmlParser';
            if (isset($xml->invoice_bank_account)) {
                $data['account_holder'] = ipl_core_decode((string)$xml->invoice_bank_account[0]->tagAttrs['account_holder']);
                $data['account_number'] = ipl_core_decode((string)$xml->invoice_bank_account[0]->tagAttrs['account_number']);
                $data['bank_code'] = ipl_core_decode((string)$xml->invoice_bank_account[0]->tagAttrs['bank_code']);
                $data['bank_name'] = ipl_core_decode((string)$xml->invoice_bank_account[0]->tagAttrs['bank_name']);
                $data['invoice_reference'] = ipl_core_decode((string)$xml->invoice_bank_account[0]->tagAttrs['invoice_reference']);
                $data['invoice_duedate'] = ipl_core_decode((string)$xml->invoice_bank_account[0]->tagAttrs['invoice_duedate']);
            }

            $data = ipl_core_parse_tc_documents($xml, $data);
            $data = ipl_core_parse_payment_infos($xml, $data);
            break;

        default:
            $ipl_core_error_code = 9;
            $ipl_core_error_msg = 'Unknown XML parser lib: ' . IPL_CORE_XML_PARSER;
            return false;
    }
    return $data;
}

function ipl_core_parse_async_capture_response($xml)
{
    global $ipl_core_error_code;
    global $ipl_core_error_msg;

    $data = array();
    switch (IPL_CORE_XML_PARSER) {
        case 'simpleXml';
            $dataAttrs = $xml->data->attributes();
            $data['error_code'] = (string)$dataAttrs->error_code;
            $data['customer_message'] = (string)$dataAttrs->customer_message;
            $data['merchant_message'] = (string)$dataAttrs->merchant_message;
            $data['status'] = (string)$dataAttrs->status;
            $data['reference'] = (string)$dataAttrs->reference;

            if ($xml->invoice_bank_account) {
                $invoiceAttr = $xml->invoice_bank_account->attributes();
                $data['account_holder'] = (string)$invoiceAttr->account_holder;
                $data['account_number'] = (string)$invoiceAttr->account_number;
                $data['bank_code'] = (string)$invoiceAttr->bank_code;
                $data['bank_name'] = (string)$invoiceAttr->bank_name;
                $data['invoice_reference'] = (string)$invoiceAttr->invoice_reference;
                $data['invoice_duedate'] = (string)$invoiceAttr->invoice_duedate;
            }
            break;
        case 'xmlParser';
            $data['xml'] = $xml;

            $data['error_code'] = ipl_core_decode((string)$xml->tagAttrs['error_code']);
            $data['customer_message'] = ipl_core_decode((string)$xml->tagAttrs['customer_message']);
            $data['merchant_message'] = ipl_core_decode((string)$xml->tagAttrs['merchant_message']);
            $data['status'] = ipl_core_decode((string)$xml->tagAttrs['status']);
            $data['reference'] = ipl_core_decode((string)$xml->tagAttrs['reference']);

            if (isset($xml->invoice_bank_account)) {
                $data['account_holder'] = ipl_core_decode((string)$xml->invoice_bank_account[0]->tagAttrs['account_holder']);
                $data['account_number'] = ipl_core_decode((string)$xml->invoice_bank_account[0]->tagAttrs['account_number']);
                $data['bank_code'] = ipl_core_decode((string)$xml->invoice_bank_account[0]->tagAttrs['bank_code']);
                $data['bank_name'] = ipl_core_decode((string)$xml->invoice_bank_account[0]->tagAttrs['bank_name']);
                $data['invoice_reference'] = ipl_core_decode((string)$xml->invoice_bank_account[0]->tagAttrs['invoice_reference']);
                $data['invoice_duedate'] = ipl_core_decode((string)$xml->invoice_bank_account[0]->tagAttrs['invoice_duedate']);
            }

            if (isset($xml->hire_purchase[0])) {
                $data = ipl_core_parse_instalment_information($xml->hire_purchase[0], $data);
            }

            $data = ipl_core_parse_tc_documents($xml, $data);
            $data = ipl_core_parse_payment_infos($xml, $data);
            break;

        default:
            $ipl_core_error_code = 9;
            $ipl_core_error_msg = 'Unknown XML parser lib: ' . IPL_CORE_XML_PARSER;
            return false;
    }

    return $data;
}

function ipl_core_parse_tc_documents($xml, $data)
{
    if (isset($xml->hire_purchase) &&
        isset($xml->hire_purchase[0]->pdf)
    ) {

        if (isset($xml->hire_purchase[0]->pdf[0]->tagChildren[0])) {
            $data['standard_information_pdf'] = ipl_core_decode((string)$xml->hire_purchase[0]->pdf[0]->tagChildren[0]->tagData);
        }

        if (isset($xml->hire_purchase[0]->pdf[0]->tagChildren[1])) {
            $data['email_attachment_pdf'] = ipl_core_decode((string)$xml->hire_purchase[0]->pdf[0]->tagChildren[1]->tagData);
        }
    }
    return $data;
}

function ipl_core_parse_payment_infos($xml, $data)
{
    if (isset($xml->payment_info)) {
        if (isset($xml->payment_info[0]->html)) {
            $data['payment_info_html'] = ipl_core_decode((string)$xml->payment_info[0]->html[0]->tagData);
        }

        if (isset($xml->payment_info[0]->plain)) {
            $data['payment_info_plain'] = ipl_core_decode((string)$xml->payment_info[0]->plain[0]->tagData);
        }
    }
    return $data;
}

function ipl_core_parse_due_information($xml, $data)
{
    if (isset($xml->dues)) {
        $duesTag = $xml->dues[0];

        $dues = array();
        foreach ($duesTag->tagChildren as $dueTag) {
            $dues[] = array(
                'type' => (string)$dueTag->tagAttrs['type'],
                'date' => (string)$dueTag->tagAttrs['date'],
                'value' => (int)$dueTag->tagData
            );
        }

        $data['dues'] = $dues;
    }

    return $data;
}

function ipl_core_parse_instalment_information($xml, $data)
{
    // Transaction Credit
    if (isset($xml->option)) {
        $option = $xml->option[0];
        $data['instalment_count'] = (int)$option->tagAttrs['ratecount'];
        $data['duration'] = (int)$option->tagAttrs['term'];

        $calculation = $option->calculation[0];
        $data['base_amount'] = (int)$calculation->base[0]->tagData;
        $data['cart_amount'] = (int)$calculation->cart[0]->tagData;
        $data['surcharge'] = (int)$calculation->surcharge[0]->tagData;
        $data['intermediate'] = (int)$calculation->intermediate[0]->tagData;
        $data['total_amount'] = (int)$calculation->total[0]->tagData;
        $data['interest'] = (int)$calculation->interest[0]->tagData;
        $data['nominal_annual'] = (int)$calculation->anual[0]->tagData;
        $data['fee_total'] = (int)$calculation->fee[0]->tagData;
        $data['dues'] = array();
        $data = ipl_core_parse_due_information($option, $data);
    }

    // PayLater
    if (isset($xml->instl_plan)) {

        $instalmentPlan = $xml->instl_plan[0];
        $data['instalment_count'] = (int)$instalmentPlan->tagAttrs['num_inst'];
        $data['duration'] = (int)$instalmentPlan->calc[0]->duration[0]->tagData;
        $data['fee_percent'] = (float)$instalmentPlan->calc[0]->fee_percent[0]->tagData;
        $data['fee_total'] = (int)$instalmentPlan->calc[0]->fee_total[0]->tagData;
        $data['pre_payment_amount'] = (int)$instalmentPlan->calc[0]->pre_payment[0]->tagData;
        $data['total_amount'] = (int)$instalmentPlan->calc[0]->total_amount[0]->tagData;
        $data['effective_annual'] = (float)$instalmentPlan->calc[0]->eff_anual[0]->tagData;
        // TODO: this should be (int)round(100*float) to be consistent
        $data['nominal_annual'] = (float)$instalmentPlan->calc[0]->nominal[0]->tagData;
        // TODO: response should include surcharge, base_amount and cart_amount to be consistent

        // parse the instalment list
        $instalmentList = $instalmentPlan->instl_list[0];
        $instalments = array();

        foreach ($instalmentList->instl as $instalment) {
            $instalments[] = array(
                'type' => (string)$instalment->tagAttrs['type'],
                'date' => (string)$instalment->tagAttrs['date'],
                'value' => (int)$instalment->tagData,
            );
        }

        $data['dues'] = $instalments;
    }

    return $data;
}

function ipl_core_parse_invoice_response($xml)
{
    global $ipl_core_error_code;
    global $ipl_core_error_msg;

    $data = array();
    switch (IPL_CORE_XML_PARSER) {
        case 'simpleXml';
            if ($xml->invoice_bank_account) {
                $invoiceAttr = $xml->invoice_bank_account->attributes();
                $data['account_holder'] = (string)$invoiceAttr->account_holder;
                $data['account_number'] = (string)$invoiceAttr->account_number;
                $data['bank_code'] = (string)$invoiceAttr->bank_code;
                $data['bank_name'] = (string)$invoiceAttr->bank_name;
                $data['invoice_reference'] = (string)$invoiceAttr->invoice_reference;
                $data['invoice_duedate'] = (string)$invoiceAttr->invoice_duedate;
                $data['activation_performed'] = (int)$invoiceAttr->activation_performed;
            }
            break;
        case 'xmlParser';
            if (isset($xml->invoice_bank_account)) {
                $data['account_holder'] = ipl_core_decode((string)$xml->invoice_bank_account[0]->tagAttrs['account_holder']);
                $data['account_number'] = ipl_core_decode((string)$xml->invoice_bank_account[0]->tagAttrs['account_number']);
                $data['bank_code'] = ipl_core_decode((string)$xml->invoice_bank_account[0]->tagAttrs['bank_code']);
                $data['bank_name'] = ipl_core_decode((string)$xml->invoice_bank_account[0]->tagAttrs['bank_name']);
                $data['invoice_reference'] = ipl_core_decode((string)$xml->invoice_bank_account[0]->tagAttrs['invoice_reference']);
                $data['invoice_duedate'] = ipl_core_decode((string)$xml->invoice_bank_account[0]->tagAttrs['invoice_duedate']);
                $data['activation_performed'] = ipl_core_decode((int)$xml->invoice_bank_account[0]->tagAttrs['activation_performed']);
            }
            // Transaction Credit
            if (isset($xml->option[0])) {
                $data = ipl_core_parse_instalment_information($xml, $data);
            }
            // PayLater
            if (isset($xml->hire_purchase[0])) {
                $data = ipl_core_parse_instalment_information($xml->hire_purchase[0], $data);
            }
            $data = ipl_core_parse_due_information($xml, $data);
            $data = ipl_core_parse_payment_infos($xml, $data);
            break;
        default:
            $ipl_core_error_code = 9;
            $ipl_core_error_msg = 'Unknown XML parser lib: ' . IPL_CORE_XML_PARSER;
            return false;
    }

    return $data;
}

function ipl_core_parse_cancel_response($xml)
{
    global $ipl_core_error_code;
    global $ipl_core_error_msg;

    # TODO: this function doesn't do anything

    $data = array();
    switch (IPL_CORE_XML_PARSER) {
        case 'simpleXml';
            // Nothing to do here
            break;
        case 'xmlParser';
            // Nothing to do here
            break;
        default:
            $ipl_core_error_code = 9;
            $ipl_core_error_msg = 'Unknown XML parser lib: ' . IPL_CORE_XML_PARSER;
            return false;
    }

    return $data;
}

function ipl_core_parse_get_billpay_bank_data_response($xml)
{
    global $ipl_core_error_code;
    global $ipl_core_error_msg;

    $data = array();
    switch (IPL_CORE_XML_PARSER) {
        case 'simpleXml';
            if ($xml->invoice_bank_account) {
                $invoiceAttr = $xml->invoice_bank_account->attributes();
                $data['account_holder'] = (string)$invoiceAttr->account_holder;
                $data['account_number'] = (string)$invoiceAttr->account_number;
                $data['bank_code'] = (string)$invoiceAttr->bank_code;
                $data['bank_name'] = (string)$invoiceAttr->bank_name;
                $data['invoice_reference'] = (string)$invoiceAttr->invoice_reference;
                $data['invoice_duedate'] = (string)$invoiceAttr->invoice_duedate;
                $data['activation_performed'] = (int)$invoiceAttr->activation_performed;
            }
            break;
        case 'xmlParser';
            if (isset($xml->bank_account)) {
                $data['account_holder'] = ipl_core_decode((string)$xml->bank_account [0]->tagAttrs['account_holder']);
                $data['account_number'] = ipl_core_decode((string)$xml->bank_account [0]->tagAttrs['account_number']);
                $data['bank_code'] = ipl_core_decode((string)$xml->bank_account [0]->tagAttrs['bank_code']);
                $data['bank_name'] = ipl_core_decode((string)$xml->bank_account [0]->tagAttrs['bank_name']);
                $data['invoice_reference'] = ipl_core_decode((string)$xml->bank_account [0]->tagAttrs['invoice_reference']);
                if (isset($xml->bank_account [0]->tagAttrs['invoice_duedate'])) {
                    $data['invoice_duedate'] = ipl_core_decode((string)$xml->bank_account [0]->tagAttrs['invoice_duedate']);
                }
            }

            $data = ipl_core_parse_payment_infos($xml, $data);

            break;
        default:
            $ipl_core_error_code = 9;
            $ipl_core_error_msg = 'Unknown XML parser lib: ' . IPL_CORE_XML_PARSER;
            return false;
    }

    return $data;
}

function ipl_core_parse_partialcancel_response($xml)
{
    global $ipl_core_error_code;
    global $ipl_core_error_msg;

    $data = array();
    switch (IPL_CORE_XML_PARSER) {
        case 'simpleXml';
            // Nothing to do here
            // TODO: transaction credit
            break;
        case 'xmlParser';
            if (isset($xml->due_update)) {
                $res = ipl_core_parse_transaction_credit_option($xml->due_update[0]);
                $data['due_update'] = $res['value'];
                $data['number_of_rates'] = $res['key'];
            }

            break;
        default:
            $ipl_core_error_code = 9;
            $ipl_core_error_msg = 'Unknown XML parser lib: ' . IPL_CORE_XML_PARSER;
            return false;
    }

    return $data;
}

function ipl_core_parse_edit_cart_content_response($xml)
{
    global $ipl_core_error_code;
    global $ipl_core_error_msg;

    $data = array();
    switch (IPL_CORE_XML_PARSER) {
        case 'simpleXml';
            // Nothing to do here
            // TODO: transaction credit
            break;
        case 'xmlParser';
            if (isset($xml->due_update)) {
                $res = ipl_core_parse_transaction_credit_option($xml->due_update[0]);
                $data['due_update'] = $res['value'];
                $data['number_of_rates'] = $res['key'];
            }
            if (isset($xml->hire_purchase[0])) {
                $data = ipl_core_parse_instalment_information($xml->hire_purchase[0], $data);
            }
            break;
        default:
            $ipl_core_error_code = 9;
            $ipl_core_error_msg = 'Unknown XML parser lib: ' . IPL_CORE_XML_PARSER;
            return false;
    }

    return $data;
}

function ipl_core_parse_update_order_response($xml)
{
    global $ipl_core_error_code;
    global $ipl_core_error_msg;

    # TODO: this function doesn't do anything

    $data = array();
    switch (IPL_CORE_XML_PARSER) {
        case 'simpleXml';
            // Nothing to do here
            break;
        case 'xmlParser';
            // Nothing to do here
            break;
        default:
            $ipl_core_error_code = 9;
            $ipl_core_error_msg = 'Unknown XML parser lib: ' . IPL_CORE_XML_PARSER;
            return false;
    }

    return $data;
}

function ipl_core_parse_calculate_rates_response($xml)
{
    global $ipl_core_error_code;
    global $ipl_core_error_msg;

    $options = array();
    switch (IPL_CORE_XML_PARSER) {
        case 'simpleXml';
            if ($xml->option) {

                foreach ($xml->option as $optionTag) {
                    $option = array();

                    if ($optionTag->calculation) {
                        $calcTag = $optionTag->calculation;

                        $calculation = array();
                        foreach ($calcTag->children() as $calcChildTag) {
                            $name = $calcChildTag->getName();
                            $value = (string)$calcChildTag;
                            $calculation[$name] = $value;
                        }

                        $option['calculation'] = $calculation;
                    }

                    if ($optionTag->dues) {
                        $duesTag = $optionTag->dues;

                        $dues = array();
                        foreach ($duesTag->children() as $dueTag) {
                            $dues[] = array(
                                'type' => (string)$dueTag['type'],
                                'value' => (int)$dueTag
                            );
                        }

                        $option['dues'] = $dues;
                    }

                    $term = (int)$optionTag['term'];
                    $options[$term] = $option;
                }
            }

            break;
        case 'xmlParser';
            if (isset($xml->option)) {
                foreach ($xml->option as $optionTag) {
                    $res = ipl_core_parse_transaction_credit_option($optionTag);
                    $options[$res['key']] = $res['value'];
                }
            }
            break;
        default:
            $ipl_core_error_code = 9;
            $ipl_core_error_msg = 'Unknown XML parser lib: ' . IPL_CORE_XML_PARSER;
            return false;
    }

    $data = array('options' => $options);

    return $data;
}

function ipl_core_parse_transaction_credit_option($optionTag)
{
    $option = array();

    if (isset($optionTag->calculation)) {
        $calcTag = $optionTag->calculation[0];

        $calculation = array();
        foreach ($calcTag->tagChildren as $calcChildTag) {
            $name = $calcChildTag->tagName;
            $value = $calcChildTag->tagData;
            $calculation[$name] = $value;
        }

        $option['calculation'] = $calculation;
    }

    if (isset($optionTag->dues)) {
        $duesTag = $optionTag->dues[0];

        $dues = array();
        foreach ($duesTag->tagChildren as $dueTag) {
            $dues[] = array(
                'type' => (string)$dueTag->tagAttrs['type'],
                'date' => (string)$dueTag->tagAttrs['date'],
                'value' => (int)$dueTag->tagData
            );
        }

        $option['dues'] = $dues;
    }

    $term = (int)$optionTag->tagAttrs['term'];
    $option['rateCount'] = (int)$optionTag->tagAttrs['ratecount'];
    return array('key' => $term, 'value' => $option);
}


// SEND AND PARSE API CALLS
function ipl_core_send_module_config_request($requestUrlBase, $aTraceData, $defaultParams, $locale)
{

    $sTraceDataXml = ipl_core_build_closed_tag('trace', $aTraceData);
    $defaultParamsXml = ipl_core_build_closed_tag("default_params", $defaultParams);
    $localeXml = ipl_core_build_closed_tag('locale', $locale);

    return ipl_core_generic_send_request(
        $requestUrlBase,
        'moduleConfig',
        array(),
        array(
            $sTraceDataXml,
            $defaultParamsXml,
            $localeXml,
        ),
        'ipl_core_parse_module_config_response'
    );
}

function ipl_core_send_preauthorize_request($requestUrlBase, $attributes, $aTraceData, $defaultParams, $preauthParams,
                                            $customerDetails, $shippingDetails, $bankAccount, $totals, $articleData, $orderHistoryData, $orderHistoryDataContent,
                                            $rateRequestData, $companyDetails, $paymentInfoParams, $fraudDetectionParams, $asyncCaptureParams, $travelData
)
{
    $sTraceDataXml = ipl_core_build_closed_tag('trace', $aTraceData);
    $defaultParamsXml = ipl_core_build_closed_tag("default_params", $defaultParams);
    $preauthParamsXml = ipl_core_build_closed_tag("preauth_params", $preauthParams);
    $customerDetailsXml = ipl_core_build_closed_tag("customer_details", $customerDetails);
    $shippingDetailsXml = ipl_core_build_closed_tag("shipping_details", $shippingDetails);
    $bankAccountXml = ipl_core_build_closed_tag("bank_account", $bankAccount);
    $totalsXml = ipl_core_build_closed_tag("total", $totals);
    $rateRequestXml = ipl_core_build_closed_tag("rate_request", $rateRequestData);
    $companyDetailsXml = ipl_core_build_closed_tag("company_details", $companyDetails);

    $parentNode = new SimpleXMLElement('<article_data></article_data>');
    $articleDataXml = ipl_core_build_xml_from_array(
        $parentNode,
        $parentNode,
        array('article' => $articleData)
    )->asXML();
    $articleDataXml = preg_replace('~^<\?xml version="1.0"\?>\s*(.*)\s*$~', '$1', $articleDataXml);

    $historyDataXml = ipl_core_build_attr_list_tag(
        'order_history_data',
        $orderHistoryData,
        'order_history',
        $orderHistoryDataContent
    );
    $paymentInfoXml = ipl_core_build_closed_tag("payment_info", $paymentInfoParams);
    $fraudDetectionXml = ipl_core_build_closed_tag("fraud_detection", $fraudDetectionParams);
    $asyncCaptureXml = ipl_core_build_list_ctag("async_capture_request", $asyncCaptureParams);

    $travelDataXml = "";
    $parentNode = new SimpleXMLElement('<trip_data></trip_data>');
    if (empty($travelData) === false) {
        $travelDataXml = ipl_core_build_xml_from_array(
            $parentNode,
            $parentNode,
            $travelData
        )->asXML();
        $travelDataXml = preg_replace('~^<\?xml version="1.0"\?>\s*(.*)\s*$~', '$1', $travelDataXml);
    }

    return ipl_core_generic_send_request(
        $requestUrlBase,
        'preauthorize',
        $attributes,
        array(
            $sTraceDataXml,
            $defaultParamsXml,
            $preauthParamsXml,
            $customerDetailsXml,
            $shippingDetailsXml,
            $rateRequestXml,
            $bankAccountXml,
            $companyDetailsXml,
            $totalsXml,
            $articleDataXml,
            $historyDataXml,
            $paymentInfoXml,
            $fraudDetectionXml,
            $asyncCaptureXml,
            $travelDataXml
        ),
        'ipl_core_parse_preauthorize_response'
    );
}

function ipl_core_send_prescore_request($requestUrlBase, $attributes, $aTraceData, $defaultParams, $customerDetails,
                                        $shippingDetails, $totals, $articleData, $orderHistoryData, $orderHistoryDataContent,
                                        $companyDetails, $paymentInfoParams, $fraudDetectionParams, $travelData
)
{
    $sTraceDataXml = ipl_core_build_closed_tag('trace', $aTraceData);
    $defaultParamsXml = ipl_core_build_closed_tag("default_params", $defaultParams);
    $customerDetailsXml = ipl_core_build_closed_tag("customer_details", $customerDetails);
    $shippingDetailsXml = ipl_core_build_closed_tag("shipping_details", $shippingDetails);
    $totalsXml = ipl_core_build_closed_tag("total", $totals);
    $companyDetailsXml = ipl_core_build_closed_tag("company_details", $companyDetails);

    $parentNode = new SimpleXMLElement('<article_data></article_data>');
    $articleDataXml = ipl_core_build_xml_from_array(
        $parentNode,
        $parentNode,
        array('article' => $articleData)
    )->asXML();
    $articleDataXml = preg_replace('~^<\?xml version="1.0"\?>\s*(.*)\s*$~', '$1', $articleDataXml);

    $historyDataXml = ipl_core_build_attr_list_tag(
        'order_history_data',
        $orderHistoryData,
        'order_history',
        $orderHistoryDataContent
    );
    $paymentInfoXml = ipl_core_build_closed_tag("payment_info", $paymentInfoParams);
    $fraudDetectionXml = ipl_core_build_closed_tag("fraud_detection", $fraudDetectionParams);

    $travelDataXml = "";
    $parentNode = new SimpleXMLElement('<trip_data></trip_data>');
    if (empty($travelData) === false) {
        $travelDataXml = ipl_core_build_xml_from_array(
            $parentNode,
            $parentNode,
            $travelData
        )->asXML();
        $travelDataXml = preg_replace('~^<\?xml version="1.0"\?>\s*(.*)\s*$~', '$1', $travelDataXml);
    }

    return ipl_core_generic_send_request(
        $requestUrlBase,
        'prescore',
        $attributes,
        array(
            $sTraceDataXml,
            $defaultParamsXml,
            $customerDetailsXml,
            $shippingDetailsXml,
            $companyDetailsXml,
            $totalsXml,
            $articleDataXml,
            $historyDataXml,
            $paymentInfoXml,
            $fraudDetectionXml,
            $travelDataXml
        ),
        'ipl_core_parse_prescore_response'
    );
}

function ipl_core_send_validation_request($requestUrlBase, $aTraceData, $defaultParams, $customerDetails,
                                          $shipppingDetails
)
{
    $sTraceDataXml = ipl_core_build_closed_tag('trace', $aTraceData);
    $defaultParamsXml = ipl_core_build_closed_tag("default_params", $defaultParams);
    $customerDetaisXml = ipl_core_build_closed_tag("customer_details", $customerDetails);
    $shippingDetailsXml = ipl_core_build_closed_tag("shipping_details", $shipppingDetails);

    return ipl_core_generic_send_request(
        $requestUrlBase,
        'validate',
        array(),
        array(
            $sTraceDataXml,
            $defaultParamsXml,
            $customerDetaisXml,
            $shippingDetailsXml,
        ),
        'ipl_core_parse_validation_response'
    );
}

function ipl_core_send_capture_request($requestUrlBase, $aTraceData, $defaultParams, $captureParams, $paymentInfoParams)
{
    $sTraceDataXml = ipl_core_build_closed_tag('trace', $aTraceData);
    $defaultParamsXml = ipl_core_build_closed_tag("default_params", $defaultParams);
    $captureParamsXml = ipl_core_build_closed_tag("capture_params", $captureParams);
    $paymentInfoParamsXml = ipl_core_build_closed_tag("payment_info", $paymentInfoParams);

    return ipl_core_generic_send_request(
        $requestUrlBase,
        'capture',
        array(),
        array(
            $sTraceDataXml,
            $defaultParamsXml,
            $captureParamsXml,
            $paymentInfoParamsXml
        ),
        'ipl_core_parse_capture_response'
    );
}

function ipl_core_send_invoice_request($requestUrlBase, $aTraceData, $defaultParams, $invoiceParams, $paymentInfoParams,
                                       $articleData = null)
{

    $sTraceDataXml = ipl_core_build_closed_tag('trace', $aTraceData);
    $defaultParamsXml = ipl_core_build_closed_tag("default_params", $defaultParams);
    $invoiceParamsXml = ipl_core_build_closed_tag("invoice_params", $invoiceParams);
    $paymentInfoParamsXml = ipl_core_build_closed_tag("payment_info", $paymentInfoParams);

    if ($articleData != null && is_array($articleData)) {
        $articleDataXml = ipl_core_build_list_tag("article_data", "article", $articleData);
        return ipl_core_generic_send_request(
            $requestUrlBase,
            'invoiceCreated',
            array(),
            array(
                $sTraceDataXml,
                $defaultParamsXml,
                $invoiceParamsXml,
                $paymentInfoParamsXml,
                $articleDataXml
            ),
            'ipl_core_parse_invoice_response'
        );

    } else {
        return ipl_core_generic_send_request(
            $requestUrlBase,
            'invoiceCreated',
            array(),
            array(
                $sTraceDataXml,
                $defaultParamsXml,
                $invoiceParamsXml,
                $paymentInfoParamsXml,
            ),
            'ipl_core_parse_invoice_response'
        );
    }
}

function ipl_core_send_update_order_request($requestUrlBase, $aTraceData, $defaultParams, $updateParams, $idUpdateList)
{

    $sTraceDataXml = ipl_core_build_closed_tag('trace', $aTraceData);
    $defaultParamsXml = ipl_core_build_closed_tag("default_params", $defaultParams);
    $udpateParamsXml = ipl_core_build_closed_tag("update_params", $updateParams);
    $idUpdateDataXml = ipl_core_build_list_tag("id_update_list", "id_update", $idUpdateList);

    return ipl_core_generic_send_request(
        $requestUrlBase,
        'updateOrder',
        array(),
        array(
            $sTraceDataXml,
            $defaultParamsXml,
            $udpateParamsXml,
            $idUpdateDataXml,
        ),
        'ipl_core_parse_update_order_response'
    );
}

function ipl_core_send_cancel_request($requestUrlBase, $aTraceData, $defaultParams, $cancelParams)
{

    $sTraceDataXml = ipl_core_build_closed_tag('trace', $aTraceData);
    $defaultParamsXml = ipl_core_build_closed_tag("default_params", $defaultParams);
    $cancelParamsXml = ipl_core_build_closed_tag("cancel_params", $cancelParams);

    return ipl_core_generic_send_request(
        $requestUrlBase,
        'cancel',
        array(),
        array(
            $sTraceDataXml,
            $defaultParamsXml,
            $cancelParamsXml,
        ),
        'ipl_core_parse_cancel_response'
    );
}

function ipl_core_send_get_billpay_bank_data_request($requestUrlBase, $aTraceData, $defaultParams, $order_params)
{

    $sTraceDataXml = ipl_core_build_closed_tag('trace', $aTraceData);
    $defaultParamsXml = ipl_core_build_closed_tag("default_params", $defaultParams);
    $getBillPayBankDataParamsXml = ipl_core_build_closed_tag("order_params", $order_params);

    return ipl_core_generic_send_request(
        $requestUrlBase,
        'getBillPayBankData',
        array(),
        array(
            $sTraceDataXml,
            $defaultParamsXml,
            $getBillPayBankDataParamsXml,
        ),
        'ipl_core_parse_get_billpay_bank_data_response'
    );
}

function ipl_core_send_partialcancel_request($requestUrlBase, $aTraceData, $defaultParams, $cancelParams,
                                             $cancelledArticles
)
{
    $sTraceDataXml = ipl_core_build_closed_tag('trace', $aTraceData);
    $defaultParamsXml = ipl_core_build_closed_tag("default_params", $defaultParams);
    $cancelParamsXml = ipl_core_build_closed_tag("cancel_params", $cancelParams);
    $cancelledArticlesXml = ipl_core_build_list_tag("canceled_articles", "article", $cancelledArticles);

    return ipl_core_generic_send_request(
        $requestUrlBase,
        'partialcancel',
        array(),
        array(
            $sTraceDataXml,
            $defaultParamsXml,
            $cancelParamsXml,
            $cancelledArticlesXml,
        ),
        'ipl_core_parse_partialcancel_response'
    );
}

function ipl_core_send_calculate_rates_request($requestUrlBase, $aTraceData, $defaultParams, $rateParams, $locale)
{

    $sTraceDataXml = ipl_core_build_closed_tag('trace', $aTraceData);
    $defaultParamsXml = ipl_core_build_closed_tag("default_params", $defaultParams);
    $rateParamsXml = ipl_core_build_closed_tag("rate_params", $rateParams);
    $localeXml = ipl_core_build_closed_tag('locale', $locale);

    return ipl_core_generic_send_request(
        $requestUrlBase,
        'calculateRates',
        array(),
        array(
            $sTraceDataXml,
            $defaultParamsXml,
            $rateParamsXml,
            $localeXml,
        ),
        'ipl_core_parse_calculate_rates_response'
    );
}

function ipl_core_send_edit_cart_content_request($requestUrlBase, $aTraceData, $defaultParams, $totals, $articleData,
                                                 $invoiceList = null
)
{
    $sTraceDataXml = ipl_core_build_closed_tag('trace', $aTraceData);
    $defaultParamsXml = ipl_core_build_closed_tag("default_params", $defaultParams);
    $totalsXml = ipl_core_build_closed_tag("total", $totals);
    $articleDataXml = ipl_core_build_list_tag("article_data", "article", $articleData);

    if ($invoiceList != null && is_array($invoiceList)) {
        $invoiceListXml = ipl_core_build_open_tag("invoice_list");
        foreach ($invoiceList as $key => $invoice) {
            $articleInvoiceDataXml = ipl_core_build_list_tag("article_data", "article", $invoice['article_data']);
            unset($invoice['article_data']);
            $invoiceParamsXml = ipl_core_build_closed_tag("invoice_params", $invoice);

            $invoice_tag_atr['invoice_number'] = $key;

            $invoiceXml = ipl_core_build_open_tag("invoice", $invoice_tag_atr);
            $invoiceXml = ipl_core_add_body($invoiceXml, $invoiceParamsXml);
            $invoiceXml = ipl_core_add_body($invoiceXml, $articleInvoiceDataXml);
            $invoiceXml = ipl_core_build_close_tag('invoice', $invoiceXml);

            $invoiceListXml = ipl_core_add_body($invoiceListXml, $invoiceXml);
        }
        $invoiceListXml = ipl_core_build_close_tag('invoice_list', $invoiceListXml);
        return ipl_core_generic_send_request(
            $requestUrlBase,
            'editCartContent',
            array(),
            array(
                $sTraceDataXml,
                $defaultParamsXml,
                $totalsXml,
                $articleDataXml,
                $invoiceListXml,
            ),
            'ipl_core_parse_edit_cart_content_response'
        );

    } else {
        return ipl_core_generic_send_request(
            $requestUrlBase,
            'editCartContent',
            array(),
            array(
                $sTraceDataXml,
                $defaultParamsXml,
                $totalsXml,
                $articleDataXml
            ),
            'ipl_core_parse_edit_cart_content_response'
        );
    }
}


function ipl_core_generic_send_request($requestUrlBase, $requestUrlSuffix, $attributes, $xmlData, $parseFunction)
{
    global $ipl_core_error_code;
    global $ipl_core_error_msg;

    ipl_core_reset_error_codes();

    $requestUrl = ipl_core_append_slash($requestUrlBase) . $requestUrlSuffix;
    $requestXml = ipl_core_build_request_xml($attributes, $xmlData);

    // send the request
    $res = ipl_core_send($requestUrl, $requestXml);

    if ($res) {

        // parse the response
        if (function_exists($parseFunction)) {
            $data = $parseFunction($res[1]);
        } else {
            $ipl_core_error_code = 14;
            $ipl_core_error_msg = "Parse function not found ($parseFunction)";
            return false;
        }

        return array($requestXml, $res[0], $data);
    } else {
        return false;
    }
}

/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * For Support, please visit http://www.criticaldevelopment.net/xml/
 */

/**
 * XML Parser Class (php4)
 *
 * Parses an XML document into an object structure much like the SimpleXML extension.
 *
 * @author Adam A. Flynn <adamaflynn@criticaldevelopment.net>
 * @copyright Copyright (c) 2005-2007, Adam A. Flynn
 *
 * @version 1.3.0
 */
class XMLParserIPL
{
    /**
     * The XML parser
     *
     * @var resource
     */
    var $parser;

    /**
     * The XML document
     *
     * @var string
     */
    var $xml;

    /**
     * Document tag
     *
     * @var object
     */
    var $document;

    /**
     * Current object depth
     *
     * @var array
     */
    var $stack;
    /**
     * Whether or not to replace dashes and colons in tag
     * names with underscores.
     *
     * @var bool
     */
    var $cleanTagNames;
    /**
     * Contains an error description if parsing failed
     * @var string
     */
    var $error;

    /**
     * Constructor. Loads XML document.
     * @param string $xml
     * @param bool $cleanTagNames
     */
    function __construct($xml = '', $cleanTagNames = true)
    {
        //Load XML document
        $this->xml = $xml;

        // Set stack to an array
        $this->stack = array();

        //Set whether or not to clean tag names
        $this->cleanTagNames = $cleanTagNames;
    }

    /**
     * Initiates and runs PHP's XML parser
     */
    function Parse()
    {
        //Create the parser resource
        $this->parser = xml_parser_create(IPL_CORE_HTTP_REQUEST_CHAR_SET);

        //Set the handlers
        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, 'StartElement', 'EndElement');
        xml_set_character_data_handler($this->parser, 'CharacterData');

        //Error handling
        if (!xml_parse($this->parser, $this->xml)) {
            $this->HandleError(xml_get_error_code($this->parser), xml_get_current_line_number($this->parser), xml_get_current_column_number($this->parser));
            return false;
        }

        //Free the parser
        xml_parser_free($this->parser);
        return true;
    }

    function getError()
    {
        return $this->error;
    }

    /**
     * Handles an XML parsing error
     *
     * @param int $code XML Error Code
     * @param int $line Line on which the error happened
     * @param int $col Column on which the error happened
     */
    function HandleError($code, $line, $col)
    {
        $this->error = 'XML Parsing Error at ' . $line . ':' . $col . '. Error ' . $code . ': ' . xml_error_string($code);
    }


    /**
     * Gets the XML output of the PHP structure within $this->document
     *
     * @return string
     */
    function GenerateXML()
    {
        return $this->document->GetXML();
    }

    /**
     * Gets the reference to the current direct parent
     *
     * @return object
     */
    function GetStackLocation()
    {
        $return = '';

        foreach ($this->stack as $stack)
            $return .= $stack . '->';

        return rtrim($return, '->');
    }

    /**
     * Handler function for the start of a tag
     *
     * @param resource $parser
     * @param string $name
     * @param array $attrs
     */
    function StartElement($parser, $name, $attrs = array())
    {
        //Make the name of the tag lower case
        $name = strtolower($name);

        //Check to see if tag is root-level
        if (count($this->stack) == 0) {
            //If so, set the document as the current tag
            $this->document = new XMLTagIPL($name, $attrs);

            //And start out the stack with the document tag
            $this->stack = array('document');
        } //If it isn't root level, use the stack to find the parent
        else {
            //Get the name which points to the current direct parent, relative to $this
            $parent = $this->GetStackLocation();

            //Add the child
            eval('$this->' . $parent . '->AddChild($name, $attrs, ' . count($this->stack) . ', $this->cleanTagNames);');

            //If the cleanTagName feature is on, replace colons and dashes with underscores
            if ($this->cleanTagNames)
                $name = str_replace(array(':', '-'), '_', $name);


            //Update the stack
            eval('$this->stack[] = $name.\'[\'.(count($this->' . $parent . '->' . $name . ') - 1).\']\';');
        }
    }

    /**
     * Handler function for the end of a tag
     *
     * @param resource $parser
     * @param string $name
     */
    function EndElement($parser, $name)
    {
        //Update stack by removing the end value from it as the parent
        array_pop($this->stack);
    }

    /**
     * Handler function for the character data within a tag
     *
     * @param resource $parser
     * @param string $data
     */
    function CharacterData($parser, $data)
    {
        //Get the reference to the current parent object
        $tag = $this->GetStackLocation();

        //Assign data to it
        eval('$this->' . $tag . '->tagData .= trim($data);');
    }
}


/**
 * XML Tag Object (php4)
 *
 * This object stores all of the direct children of itself in the $children array. They are also stored by
 * type as arrays. So, if, for example, this tag had 2 <font> tags as children, there would be a class member
 * called $font created as an array. $font[0] would be the first font tag, and $font[1] would be the second.
 *
 * To loop through all of the direct children of this object, the $children member should be used.
 *
 * To loop through all of the direct children of a specific tag for this object, it is probably easier
 * to use the arrays of the specific tag names, as explained above.
 *
 * @author Adam A. Flynn <adamaflynn@criticaldevelopment.net>
 * @copyright Copyright (c) 2005-2007, Adam A. Flynn
 *
 * @version 1.3.0
 */
class XMLTagIPL
{
    /**
     * Array with the attributes of this XML tag
     *
     * @var array
     */
    var $tagAttrs;

    /**
     * The name of the tag
     *
     * @var string
     */
    var $tagName;

    /**
     * The data the tag contains
     *
     * So, if the tag doesn't contain child tags, and just contains a string, it would go here
     *
     * @var string
     */
    var $tagData;

    /**
     * Array of references to the objects of all direct children of this XML object
     *
     * @var array
     */
    var $tagChildren;

    /**
     * The number of parents this XML object has (number of levels from this tag to the root tag)
     *
     * Used presently only to set the number of tabs when outputting XML
     *
     * @var int
     */
    var $tagParents;

    /**
     * Constructor, sets up all the default values
     *
     * @param string $name
     * @param array $attrs
     * @param int $parents
     * @return XMLTag
     */
    function __construct($name, $attrs = array(), $parents = 0)
    {
        //Make the keys of the attr array lower case, and store the value
        $this->tagAttrs = array_change_key_case($attrs, CASE_LOWER);

        //Make the name lower case and store the value
        $this->tagName = strtolower($name);

        //Set the number of parents
        $this->tagParents = $parents;

        //Set the types for children and data
        $this->tagChildren = array();
        $this->tagData = '';
    }

    /**
     * Adds a direct child to this object
     *
     * @param string $name
     * @param array $attrs
     * @param int $parents
     * @param bool $cleanTagName
     */
    function AddChild($name, $attrs, $parents, $cleanTagName = true)
    {
        //If the tag is a reserved name, output an error
        if (in_array($name, array('tagChildren', 'tagAttrs', 'tagParents', 'tagData', 'tagName'))) {
            trigger_error('You have used a reserved name as the name of an XML tag. Please consult the documentation (http://www.criticaldevelopment.net/xml/) and rename the tag named "' . $name . '" to something other than a reserved name.', E_USER_ERROR);

            return;
        }

        //Create the child object itself
        $child = new XMLTagIPL($name, $attrs, $parents);

        //If the cleanTagName feature is on, replace colons and dashes with underscores
        if ($cleanTagName)
            $name = str_replace(array(':', '-'), '_', $name);

        //Toss up a notice if someone's trying to to use a colon or dash in a tag name
        elseif (strpos($name, ':') || strpos($name, '-'))
            trigger_error('Your tag named "' . $name . '" contains either a dash or a colon. Neither of these characters are friendly with PHP variable names, and, as such, they cannot be accessed and will cause the parser to not work. You must enable the cleanTagName feature (pass true as the second argument of the XMLParser constructor). For more details, see http://www.criticaldevelopment.net/xml/', E_USER_ERROR);

        //If there is no array already set for the tag name being added,
        //create an empty array for it
        if (!isset($this->$name))
            $this->$name = array();

        //Add the reference of it to the end of an array member named for the tag's name
        $this->{$name}[] =& $child;

        //Add the reference to the children array member
        $this->tagChildren[] =& $child;
    }

    /**
     * Returns the string of the XML document which would be generated from this object
     *
     * This function works recursively, so it gets the XML of itself and all of its children, which
     * in turn gets the XML of all their children, which in turn gets the XML of all thier children,
     * and so on. So, if you call GetXML from the document root object, it will return a string for
     * the XML of the entire document.
     *
     * This function does not, however, return a DTD or an XML version/encoding tag. That should be
     * handled by XMLParser::GetXML()
     *
     * @return string
     */
    function GetXML()
    {
        //Start a new line, indent by the number indicated in $this->parents, add a <, and add the name of the tag
        $out = "\n" . str_repeat("\t", $this->tagParents) . '<' . $this->tagName;

        //For each attribute, add attr="value"
        foreach ($this->tagAttrs as $attr => $value)
            $out .= ' ' . $attr . '="' . $value . '"';

        //If there are no children and it contains no data, end it off with a />
        if (empty($this->tagChildren) && empty($this->tagData))
            $out .= " />";

        //Otherwise...
        else {
            //If there are children
            if (!empty($this->tagChildren)) {
                //Close off the start tag
                $out .= '>';

                //For each child, call the GetXML function (this will ensure that all children are added recursively)
                foreach ($this->tagChildren as $child) {
                    if (is_object($child))
                        $out .= $child->GetXML();
                }

                //Add the newline and indentation to go along with the close tag
                $out .= "\n" . str_repeat("\t", $this->tagParents);
            } //If there is data, close off the start tag and add the data
            elseif (!empty($this->tagData))
                $out .= '>' . $this->tagData;

            //Add the end tag
            $out .= '</' . $this->tagName . '>';
        }

        //Return the final output
        return $out;
    }

    /**
     * Deletes this tag's child with a name of $childName and an index
     * of $childIndex
     *
     * @param string $childName
     * @param int $childIndex
     */
    function Delete($childName, $childIndex = 0)
    {
        //Delete all of the children of that child
        $this->{$childName}[$childIndex]->DeleteChildren();

        //Destroy the child's value
        $this->{$childName}[$childIndex] = null;

        //Remove the child's name from the named array
        unset($this->{$childName}[$childIndex]);

        //Loop through the tagChildren array and remove any null
        //values left behind from the above operation
        for ($x = 0; $x < count($this->tagChildren); $x++) {
            if (is_null($this->tagChildren[$x]))
                unset($this->tagChildren[$x]);
        }
    }

    /**
     * Removes all of the children of this tag in both name and value
     */
    function DeleteChildren()
    {
        //Loop through all child tags
        for ($x = 0; $x < count($this->tagChildren); $x++) {
            //Do this recursively
            $this->tagChildren[$x]->DeleteChildren();

            //Delete the name and value
            $this->tagChildren[$x] = null;
            unset($this->tagChildren[$x]);
        }
    }
}
