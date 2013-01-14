<?php
/* -----------------------------------------------------------------------------------------
   $Id: vat_validation.php 3198 2012-07-11 09:41:52Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2006 XT-Commerce (vat_validation.php 1283 2005-10-05)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// include needed functions
require_once (DIR_FS_INC.'xtc_get_countries.inc.php');

class vat_validation {
  var $vat_info;
  var $vat_mod;

  const SOAP_WSDL_URL = 'http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';

  function __construct($vat_id = '', $customers_id = '', $customers_status = '', $country_id = '', $guest = false) {
    $this->vat_info = array ();
    $this->live_check = ACCOUNT_COMPANY_VAT_LIVE_CHECK;
    if (xtc_not_null($vat_id)) {
      $this->getInfo($vat_id, $customers_id, $customers_status, $country_id, $guest);
    } else {
      if ($guest) {
        $this->vat_info = array ('status' => DEFAULT_CUSTOMERS_STATUS_ID_GUEST);
      } else {
        $this->vat_info = array ('status' => DEFAULT_CUSTOMERS_STATUS_ID);
      }
    }
  }

  function getInfo($vat_id = '', $customers_id = '', $customers_status = '', $country_id = '', $guest = false) {

    if (!$guest) {
      if ($vat_id) {
        $validate_vatid = $this->validate_vatid($vat_id, $country_id);

        switch ($validate_vatid) {

          case '0' :
            if (ACCOUNT_VAT_BLOCK_ERROR == 'true') {
              $error = true;
            }
            $status = DEFAULT_CUSTOMERS_STATUS_ID;
            $vat_id_status = '0';
            break;

          case '1' :
            if ($country_id == STORE_COUNTRY) {
              if (ACCOUNT_COMPANY_VAT_GROUP == 'true') {
                $status = DEFAULT_CUSTOMERS_VAT_STATUS_ID_LOCAL;
              } else {
                $status = DEFAULT_CUSTOMERS_STATUS_ID;
              }
            } else {
              if (ACCOUNT_COMPANY_VAT_GROUP == 'true') {
                $status = DEFAULT_CUSTOMERS_VAT_STATUS_ID;
              } else {
                $status = DEFAULT_CUSTOMERS_STATUS_ID;
              }
            }
            $error = false;
            $vat_id_status = '1';
            break;

          case '2' :
            if (ACCOUNT_VAT_BLOCK_ERROR == 'true') {
              $error = true;
            }
            $status = DEFAULT_CUSTOMERS_STATUS_ID;
            $vat_id_status = $validate_vatid;
            break;

          case '8' :
            if (ACCOUNT_VAT_BLOCK_ERROR == 'true') {
              $error = true;
            }
            $status = DEFAULT_CUSTOMERS_STATUS_ID;
            $vat_id_status = '8';
            break;

          case '99' :
          case '98' :
          case '97' :
          case '96' :
          case '95' :
          case '94' :
            if (ACCOUNT_VAT_BLOCK_ERROR == 'true') {
              $error = true;
            }
            $status = DEFAULT_CUSTOMERS_STATUS_ID;
            $vat_id_status = $validate_vatid;
            break;

          default :
            $status = DEFAULT_CUSTOMERS_STATUS_ID;

        } //end switch

      } else {
        if ($customers_status) {
          $status = $customers_status;
        } else {
          $status = DEFAULT_CUSTOMERS_STATUS_ID;
        }
        $vat_id_status = '';
        $error = false;
      }

    } else {
      if ($vat_id) {
        $validate_vatid = $this->validate_vatid($vat_id, $country_id);

        switch ($validate_vatid) {

          case '0' :
            if (ACCOUNT_VAT_BLOCK_ERROR == 'true') {
              $error = true;
            }
            $status = DEFAULT_CUSTOMERS_STATUS_ID_GUEST;
            $vat_id_status = '0';
            break;

          case '1' :
            if ($country_id == STORE_COUNTRY) {
              if (ACCOUNT_COMPANY_VAT_GROUP == 'true') {
                $status = DEFAULT_CUSTOMERS_VAT_STATUS_ID_LOCAL;
              } else {
                $status = DEFAULT_CUSTOMERS_STATUS_ID_GUEST;
              }
            } else {
              if (ACCOUNT_COMPANY_VAT_GROUP == 'true') {
                $status = DEFAULT_CUSTOMERS_VAT_STATUS_ID;
              } else {
                $status = DEFAULT_CUSTOMERS_STATUS_ID_GUEST;
              }
            }
            $error = false;
            $vat_id_status = '1';
            break;

          case '2' :
            if (ACCOUNT_VAT_BLOCK_ERROR == 'true') {
              $error = true;
            }
            $status = DEFAULT_CUSTOMERS_STATUS_ID_GUEST;
            $vat_id_status = $validate_vatid;
            break;

          case '8' :
            if (ACCOUNT_VAT_BLOCK_ERROR == 'true') {
              $error = true;
            }
            $status = DEFAULT_CUSTOMERS_STATUS_ID_GUEST;
            $vat_id_status = '8';
            break;

          case '99' :
          case '98' :
          case '97' :
          case '96' :
          case '95' :
          case '94' :
            if (ACCOUNT_VAT_BLOCK_ERROR == 'true') {
              $error = true;
            }
            $status = DEFAULT_CUSTOMERS_STATUS_ID_GUEST;
            $vat_id_status = $validate_vatid;
            break;

          default :
            $status = DEFAULT_CUSTOMERS_STATUS_ID_GUEST;

        } //end switch

      } else {
        if ($customers_status) {
          $status = $customers_status;
        } else {
          $status = DEFAULT_CUSTOMERS_STATUS_ID_GUEST;
        }
        $vat_id_status = '';
        $error = false;
      }
    }

    if ($customers_id) {
      $customers_status_query = xtc_db_query("SELECT customers_status FROM ".TABLE_CUSTOMERS." WHERE customers_id = '".$customers_id."'");
      $customers_status_value = xtc_db_fetch_array($customers_status_query);

      if ($customers_status_value['customers_status'] != 0) {
        $status = $status;
      } else {
        $status = $customers_status_value['customers_status'];
      }
    }

    $this->vat_info = array (
      'status' => $status,
      'vat_id_status' => $vat_id_status,
      'error' => $error,
      'validate' => $validate_vatid
    );

  }

  // DokuMan - 2011-08-24 - check VAT via SOAP at http://ec.europa.eu
  function validate_vatid($vat_id, $country_id) {

      // 0 = 'VAT invalid'
      // 1 = 'VAT valid'
      // 2 = 'SOAP ERROR: Connection to host not possible, europe.eu down?'
      // 8 = 'unknown country'
      //94 = 'INVALID_INPUT'       => 'The provided CountryCode is invalid or the VAT number is empty',
      //95 = 'SERVICE_UNAVAILABLE' => 'The SOAP service is unavailable, try again later',
      //96 = 'MS_UNAVAILABLE'      => 'The Member State service is unavailable, try again later or with another Member State',
      //97 = 'TIMEOUT'             => 'The Member State service could not be reached in time, try again later or with another Member State',
      //98 = 'SERVER_BUSY'         => 'The service cannot process your request. Try again later.'
      //99 = 'no PHP5 SOAP support'
      $results = array (0 => '0',
                         1 => '1',
                         2 => '2',
                         8 => '8',
                        94 => '94',
                        95 => '95',
                        96 => '96',
                        97 => '97',
                        98 => '98',
                        99 => '99');

      // check if PHP5 bulit-in SOAP class is available
      if (!class_exists('SoapClient')) {
        return $results[99]; // no PHP5 SOAP support
      }

      // map numeric country id to alphanumeric ISO 3166 country code
      $country_array = array ();
      $country_array = xtc_get_countriesList($country_id, true);
      $country_id = strtoupper($country_array['countries_iso_code_2']);

      // check VAT for EU countries only
      switch ($country_id) {
      // EU countries
        case 'AT':
        case 'BE':
        case 'BG':
        case 'CY':
        case 'CZ':
        case 'DE':
        case 'DK':
        case 'EE':
        case 'GR':
        case 'ES':
        case 'FI':
        case 'FR':
        case 'GB':
        case 'HU':
        case 'IE':
        case 'IT':
        case 'LT':
        case 'LU':
        case 'LV':
        case 'MT':
        case 'NL':
        case 'PL':
        case 'PT':
        case 'RO':
        case 'SE':
        case 'SI':
        case 'SK':
          return $results[$this->checkVatID_EU($vat_id, $country_id)];

        default:
          return $results[8]; // unknown country
      }
  }

  /**
  * Invoke the VIES service to check an EU VAT number
  *
  * @param string $vat_id VAT number
  * @param string $country_id Country Code
  * @return mixed
  */
  function checkVatID_EU($vat_id, $country_id) {

    // remove noise from VAT
    $remove = array (' ', '-', '/', '\\', '.', ':', ',');
    $vat_id = trim(chop($vat_id));
    $vat_id = str_replace($remove, '', $vat_id );
    // split VAT into ISO 3166 ALPHA-2 country code and vat number
    $countryCode = substr($vat_id, 0, 2);
    $vatNumber = substr($vat_id, 2);

    try {
      $options = array(
        'soap_version'=>SOAP_1_1,
        'exceptions'=>true,
        'trace'=>1,
        'cache_wsdl'=>WSDL_CACHE_NONE,
        'user_agent' => 'Mozilla',  // User agent mandatory for this special VAT WSDL-request
        //'proxy_host' => '[2a01:e0b:1:143:62eb:69ff:fe8f:1764]',
        //'proxy_port' => 80
      );
      $client = @new SoapClient(vat_validation::SOAP_WSDL_URL, $options);
    } catch (Exception $e) {
      echo '<span class="messageStackError">'.$e->faultstring.'</span>';
    }
    if($client){
      try {
        $params = array('countryCode' => $country_id, 'vatNumber' => $vatNumber);
        $result = $client->checkVat($params);
        if($result->valid == true){
          return 1;  // VAT-ID is valid
        } else {
          return 0;   // VAT-ID is NOT valid
        }
      } catch (SoapFault $e) {
        //enhanced error reporting
        $errors = array(
          'INVALID_INPUT'       => '94',
          'SERVICE_UNAVAILABLE' => '95',
          'MS_UNAVAILABLE'      => '96',
          'TIMEOUT'             => '97',
          'SERVER_BUSY'         => '98'
          );
        return $errors[$e->faultstring];
      }
    } else {
      return 2;  // Connection to host not possible, europe.eu down?
    }
  } // end checkVatID_EU
}
?>