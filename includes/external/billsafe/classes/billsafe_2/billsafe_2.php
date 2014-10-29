<?php
/* -----------------------------------------------------------------------------------------
   $Id: billsafe_2.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   Copyright (c) 2013 PayPal SE and Bernd Blazynski

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

/*
* id = billsafe_2.php
* location = /includes/external/billsafe/classes/billsafe_2 // DokuMan - 2012-06-19 - move billsafe to external directory
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License, version 2, as
* published by the Free Software Foundation.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* @package BillSAFE_2
* @copyright (C) 2013 PayPal SE and Bernd Blazynski
* @license GPLv2
*/


require_once 'HttpClient.php';
require_once 'Logger.php';
require_once 'LoggerNull.php';

class Billsafe_Sdk {
  const SANDBOX = 'SANDBOX';
  const LIVE = 'LIVE';
//  const SDK_SIGNATURE = 'BillSAFE SDK (PHP) 2011-07-29';

  private $_merchantId;
  private $_merchantLicenseSandbox;
  private $_merchantLicenseLive;
  private $_applicationSignature;
  private $_applicationVersion;
  private $_isLiveMode = false;
  private $_isUtf8Mode = false;
  private $_lastResponse;
  private $_logger;
  private $_apiVersion = '208';
  private $_apiUrlSandbox = 'https://sandbox-nvp.billsafe.de/V{VERSION}';
  private $_apiUrlLive = 'https://nvp.billsafe.de/V{VERSION}';
  private $_gatewayVersion = '200';
  private $_gatewayUrlSandbox = 'https://sandbox-payment.billsafe.de/V{VERSION}';
  private $_gatewayUrlLive = 'https://payment.billsafe.de/V{VERSION}';

  public function __construct($iniFile = '') {
    $this->setLogger(new Billsafe_LoggerNull());
    if (empty($iniFile)) $iniFile = 'ini.php';
    require($iniFile);
    $this->_isLiveMode = (bool)$ini['isLiveMode'];
    $this->_isUtf8Mode = (bool)$ini['isUtf8Mode'];
    if (isset($ini['apiVersion'])) $this->_apiVersion = (int)$ini['apiVersion'];
    if (isset($ini['gatewayVersion'])) $this->_gatewayVersion = (int)$ini['gatewayVersion'];
    $this->setCredentials($ini);
  }

  public function setCredentials(array $credentials) {
    if (!empty($credentials['merchantId'])) $this->_merchantId = (int)$credentials['merchantId'];
    if (!empty($credentials['merchantLicenseSandbox'])) $this->_merchantLicenseSandbox = (string)$credentials['merchantLicenseSandbox'];
    if (!empty($credentials['merchantLicenseLive'])) $this->_merchantLicenseLive = (string)$credentials['merchantLicenseLive'];
    if (!empty($credentials['applicationSignature'])) $this->_applicationSignature = (string)$credentials['applicationSignature'];
    if (!empty($credentials['applicationVersion'])) $this->_applicationVersion = (string)$credentials['applicationVersion'];
  }

  public function setApiUrls(array $urls) {
    if (!empty($urls['sandbox'])) $this->_apiUrlSandbox = (string)$urls['sandbox'];
    if (!empty($urls['live'])) $this->_apiUrlLive = (string)$urls['live'];
  }

  public function setApiVersion($version) {
    $this->_apiVersion = (int)$version;
  }

  public function setGatewayUrls(array $urls) {
    if (!empty($urls['sandbox'])) $this->_gatewayUrlSandbox = (string)$urls['sandbox'];
    if (!empty($urls['live'])) $this->_gatewayUrlLive = (string)$urls['live'];
  }

  public function setGatewayVersion($version) {
    $this->_gatewayVersion = (int)$version;
  }

  public function getLogger() {
    return $this->_logger;
  }

  public function setLogger(Billsafe_Logger $logger) {
    $this->_logger = $logger;
  }

  public function setMode($mode) {
    $this->_isLiveMode = ($mode == self::LIVE);
  }

  public function isLiveMode() {
    return $this->_isLiveMode;
  }

  public function setUtf8Mode($isUtf8Mode = true) {
    $this->_isUtf8Mode = (bool)$isUtf8Mode;
  }

  public function isUtf8Mode() {
    return $this->_isUtf8Mode;
  }

  public function callMethod($methodName, $parameter) {
    $this->_lastResponse = null;
    if (!is_object($parameter) && !is_array($parameter)) throw new Billsafe_Exception('parameter must be an object or an array');
    $requestString = $this->_destructurize($parameter).'method='.urlencode($methodName).'&format=NVP'.'&merchant_id='.urlencode($this->_merchantId).'&merchant_license='.urlencode($this->isLiveMode() ? $this->_merchantLicenseLive : $this->_merchantLicenseSandbox).'&application_signature='.urlencode($this->_applicationSignature).'&application_version='.urlencode($this->_applicationVersion);
//    $requestString = $this->_destructurize($parameter).'method='.urlencode($methodName).'&format=NVP'.'&merchant_id='.urlencode($this->_merchantId).'&merchant_license='.urlencode($this->isLiveMode() ? $this->_merchantLicenseLive : $this->_merchantLicenseSandbox).'&application_signature='.urlencode($this->_applicationSignature).'&application_version='.urlencode($this->_applicationVersion).'&sdkSignature=' . urlencode(self::SDK_SIGNATURE);
    $httpClient = new Billsafe_HttpClient($this->_getApiUrl());
    $httpClient->setLogger($this->getLogger());
    $response = $httpClient->post($requestString, true, 'text/plain');
    if ($response->statusCode != 200) throw new Billsafe_Exception('invalid server response! Status code is not 200 / OK!');
    $structuredResponse = $this->_structurize($response->body);
    if (!isset($structuredResponse->ack)) throw new Billsafe_Exception('invalid server response! Element "ack" not found!');
    $this->_lastResponse = $structuredResponse;
    return $this->_lastResponse;
  }

  public function redirectToPaymentGateway($token) {
    if (!headers_sent()) {
      header('Location: '.$this->_getGatewayUrl().'?token='.$token);
      exit;
    } else {
      throw new Billsafe_Exception('Redirect to BillSAFE Payment Gateway failed because HTTP headers have already been sent! Make sure to redirect BEFORE any output is sent to the browser!');
    }
  }

  public function callPaymentLayer($token) {
    if (!empty($_GET['layeredPaymentGateway'])) {
      echo '<html><div id="BillSAFE_Token">'.$token.'</div></html>';
      exit;
    } else {
      header('Location: '.$this->_getGatewayUrl().'?token='.$token);
      exit;
    }
  }

  private function _structurize($rawNvpInput) {
    $rawNvpInput = trim(substr($rawNvpInput, strrpos($rawNvpInput, "\n")));
    $pairs = explode('&', $rawNvpInput, 500);
    $input = array();
    $structure = new stdClass();
    foreach ($pairs as $rawPair) {
      $pair = explode('=', $rawPair, 2);
      if (count($pair) == 2) @$this->_putInStructure(explode('_', urldecode($pair[0]), 10), urldecode($pair[1]), $structure);
    }
    return $structure;
  }

  private function _putInStructure(array $parts, $value, &$structure) {
    $part = array_shift($parts);
    if (empty($parts)) {
      if (is_numeric($part)) {
        $structure[$part] = $this->_applyInputEncoding($value);
      } else {
        $structure->$part = $this->_applyInputEncoding($value);
      }
    } else {
      if (is_numeric($part)) {
        $this->_putInStructure($parts, $value, $structure[$part]);
      } else {
        $this->_putInStructure($parts, $value, $structure->$part);
      }
    }
  }

  private function _destructurize($input, $prefix = '') {
    if (is_bool($input)) {
      return urlencode($prefix).'='.($input ? 'TRUE' : 'FALSE')."&";
    } elseif (is_string($input)) {
      return urlencode($prefix).'='. urlencode($this->_applyOutputEncoding($input))."&";
    } else if (is_scalar($input)) {
      return urlencode($prefix).'='.urlencode($input)."&";
    }
    if (is_object($input)) $input = get_object_vars($input);
    if (is_array($input)) {
      $returnString = '';
      foreach ($input as $key => $value) {
        $returnString .= $this->_destructurize($value, empty($prefix) ? $key : $prefix.'_'.$key);
      }
      return $returnString;
    }
  }

  private function _applyOutputEncoding($outgoingData) {
    if (is_string($outgoingData) && !$this->isUtf8Mode()) {
      return utf8_encode($outgoingData);
    } else {
      return $outgoingData;
    }
  }

  private function _applyInputEncoding($incomingData) {
    if (is_string($incomingData) && !$this->isUtf8Mode()) {
      return utf8_decode($incomingData);
    } else {
      return $incomingData;
    }
  }

  private function _getApiUrl() {
    $url = $this->isLiveMode() ? $this->_apiUrlLive : $this->_apiUrlSandbox;
    return str_replace('{VERSION}', $this->_apiVersion, $url);
  }

  private function _getGatewayUrl() {
    $url = $this->isLiveMode() ? $this->_gatewayUrlLive : $this->_gatewayUrlSandbox;
    return str_replace('{VERSION}', $this->_gatewayVersion, $url);
  }
}

?>