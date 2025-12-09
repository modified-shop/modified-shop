<?php
/* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2012 Gambio GmbH - vat_validation.php 2012-05-10 gm
   (c) 2005 xtc_validate_vatid_status.inc.php 899 2005-04-29
   (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com ($Id$)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// include needed functions
include_once(DIR_FS_INC . 'xtc_get_countries.inc.php');


class vat_validation {
  
  var $vat_info;
  var $vat_errors;
  var $live_check;
  var $live_check_url = 'https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';
  
  
  function __construct($vat_id = '', $customers_id = '', $customers_status = '', $country_id = '', $guest = false) {
    $vat_id = str_replace(' ', '', $vat_id);
    $this->vat_info = array ();
    $this->vat_errors = array(
      'MS_MAX_CONCURRENT_REQ' => '93',
      'INVALID_INPUT' => '94',
      'SERVICE_UNAVAILABLE' => '95',
      'MS_UNAVAILABLE' => '96',
      'TIMEOUT' => '97',
      'SERVER_BUSY' => '98',
      );
    $this->live_check = ACCOUNT_COMPANY_VAT_LIVE_CHECK;
    
    if (xtc_not_null($vat_id)) {
      $this->getInfo($vat_id, $customers_id, $customers_status, $country_id, $guest);
    } else {
      if ($guest === true) {
        $this->vat_info = array ('status' => DEFAULT_CUSTOMERS_STATUS_ID_GUEST);
      } else {
        $this->vat_info = array ('status' => DEFAULT_CUSTOMERS_STATUS_ID);
      }
    }
  }


  function getInfo($vat_id = '', $customers_id = '', $customers_status = '', $country_id = '', $guest = false) {
    $customers_status_id = DEFAULT_CUSTOMERS_STATUS_ID;
    $customers_vat_status_id = DEFAULT_CUSTOMERS_VAT_STATUS_ID;
    $customers_vat_status_id_local = DEFAULT_CUSTOMERS_VAT_STATUS_ID_LOCAL;
    
    $error = false;
    if ($vat_id != '') {
      $validate_vatid = $this->validate_vatid($vat_id, $country_id, false);
      if ($this->live_check == 'true' && $validate_vatid == '1') {
        $validate_vatid = $this->validate_vatid($vat_id, $country_id, true);
      }
      $vat_id_status = $validate_vatid;

      switch ($validate_vatid) {
        case '0' :
          if (ACCOUNT_VAT_BLOCK_ERROR == 'true') {
            $error = true;
          }
          $status = $customers_status_id;
          break;
        case '1' :
          if ($country_id == STORE_COUNTRY) {
            if (ACCOUNT_COMPANY_VAT_GROUP == 'true') {
              $status = $customers_vat_status_id_local;
            } else {
              $status = $customers_status_id;
            }
          } else {
            if (ACCOUNT_COMPANY_VAT_GROUP == 'true') {
              $status = $customers_vat_status_id;
            } else {
              $status = $customers_status_id;
            }
          }
          break;
        case '8' :
          if (ACCOUNT_VAT_BLOCK_ERROR == 'true') {
            $error = true;
          }
          $status = $customers_status_id;
          break;
        case '9' :
          if (ACCOUNT_VAT_BLOCK_ERROR == 'true') {
            $error = true;
          }
          $status = $customers_status_id;
          break;
        case '99' :
        case '98' :
        case '97' :
        case '96' :
        case '95' :
        case '94' :
        case '93' :
          if (ACCOUNT_VAT_BLOCK_ERROR == 'true') {
            $error = true;
          }
          $status = $customers_status_id;
          break;
        default :
          $status = $customers_status_id;
          break;
      }
    }
    
    if ($guest === true) {
      $status = DEFAULT_CUSTOMERS_STATUS_ID_GUEST;
    }

    // check if is admin
    if ($customers_id != '') {
      $customers_status_query = xtc_db_query("SELECT customers_status 
                                                FROM ".TABLE_CUSTOMERS." 
                                               WHERE customers_id = '".(int)$customers_id."'");
      $customers_status_value = xtc_db_fetch_array($customers_status_query);
      if ($customers_status_value['customers_status'] == '0') {
        $status = '0';
      }
    }

    $this->vat_info = array(
      'status' => $status, 
      'vat_id_status' => $vat_id_status, 
      'error' => $error, 
      'validate' => $validate_vatid,
    );
  }


  function validate_vatid($vat_id, $country_id, $live_check = false) {
    static $country_check;
    
    // remove special chars
    $remove = array (' ', '-', '/', '\\', '.', ':', ',');
    $vat_id = trim(chop($vat_id));
    $vat_id = str_replace($remove, '', $vat_id);
    
    $vatNumber = substr($vat_id, 2);
    $country = strtolower(substr($vat_id, 0, 2));
        
    // 0 = 'invalid'
    // 1 = 'valid'
    // 8 = 'unknown country'
    // 9 = 'unknown algorithm'
    //93 = 'MS_MAX_CONCURRENT_REQ' => 'The maximum number of concurrent requests has been reached'
    //94 = 'INVALID_INPUT'         => 'The provided CountryCode is invalid or the VAT number is empty'
    //95 = 'SERVICE_UNAVAILABLE'   => 'The SOAP service is unavailable, try again later'
    //96 = 'MS_UNAVAILABLE'        => 'The Member State service is unavailable, try again later or with another Member State'
    //97 = 'TIMEOUT'               => 'The Member State service could not be reached in time, try again later or with another Member State'
    //98 = 'SERVER_BUSY'           => 'The service cannot process your request. Try again later.'
    //99 = 'no PHP5 SOAP support'
    $results = array(
      0 => '0',
      1 => '1',
      8 => '8',
      9 => '9',
      93 => '93',
      94 => '94',
      95 => '95',
      96 => '96',
      97 => '97',
      98 => '98',
      99 => '99',
    );
    
    // check country 
    if (!isset($country_check)) {
      $country_check = xtc_get_countriesList($country_id, true);
    }
    
    // fix for Greece
    $search_array = array('gr');
    $replace_array = array('el');
    $country = str_replace($search_array, $replace_array, $country);
    $country_check['countries_iso_code_2'] = str_replace($search_array, $replace_array, strtolower($country_check['countries_iso_code_2']));

    if (strtoupper($country_check['countries_iso_code_2']) != strtoupper($country)) {
      return $results[0];
    }
    
    // check store vatid
    if (STORE_OWNER_VAT_ID != '') {
      $vat_id_store_owner = trim(chop(STORE_OWNER_VAT_ID));
      $vat_id_store_owner = str_replace($remove, '', $vat_id_store_owner);
      $vat_id_store_owner = substr($vat_id_store_owner, 2);
      if ($vat_id_store_owner == $vatNumber) {
        return $results[0];
      }
    }
        
    $country_iso_code = strtoupper($country);
    
    if ($live_check === true) {
      
      //Check VAT for EU countries only
      switch ($country_iso_code) {
        case 'AT':
        case 'BE':
        case 'BG':
        case 'CY':
        case 'CZ':
        case 'DE':
        case 'DK':
        case 'EE':
        case 'EL':
        case 'ES':
        case 'FI':
        case 'FR':
        case 'GB':
        case 'HU':
        case 'HR':
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
          $t_result = $this->checkVatID($vatNumber, $country_iso_code);
          break;
        default:
          $t_result = 8; //unknown country
          break;
      }
    } else {
      switch ($country_iso_code) {
        case 'BE':
          // fix for old vat_id
          if (strlen($vatNumber) == 9) {
            $vatNumber = str_pad($vatNumber, 10, '0', STR_PAD_LEFT);
          }
          break;
      }
      $vat_id = $country_iso_code . $vatNumber;
      $t_result = $this->checkVatIDSyntax($country, $vat_id);
    }

    return $results[$t_result];
  }
  
  
  function checkVatID($vatNumber, $country_iso_code) {
    $params = array(
      'countryCode' => $country_iso_code, 
      'vatNumber' => $vatNumber
    );

    try {
      $options = array(
        'soap_version' => SOAP_1_1,
        'exceptions' => true,
        'trace' => 1,
        'cache_wsdl' => WSDL_CACHE_NONE,
      );
      $client = new SoapClient($this->live_check_url, $options);
    } catch (Exception $e) {
      trigger_error('SOAP-Fehler: (Fehlernummer: '. $e->faultcode .', Fehlermeldung: '. $e->faultstring .')', E_USER_WARNING);
    }

    if ($client) {
      try {
        $result = $client->checkVat($params);
        if ($result->valid == true){
          return 1;  // VAT-ID is valid
        } else {
          return 0;   // VAT-ID is NOT valid
        }
      } catch (SoapFault $e) {
        return $this->vat_errors[$e->faultstring];
      }
    }
    
    return 95;
  }
  
  
  function checkVatIDSyntax($country, $vat_id) {
    switch ($country) {
      // oesterreich
      case 'at' :
        if (strlen($vat_id) != 11 && strtoupper($vat_id[2]) != 'U') {
          return 0;
        }

        $number = substr(str_replace($country, '', strtolower($vat_id)), 1);

        if (strlen($number) == 8 && is_numeric($number)) {
          return 1;
        } else {
          return 0;
        }
        break;

      // belgien
      case 'be' :
        if (strlen($vat_id) != 12) {
          return 0;
        }

        $number = str_replace($country, '', strtolower($vat_id));

        if (strlen($number) == 10 && is_numeric($number)) {
          return 1;
        } else {
          return 0;
        }
        break;

      // bulgarien
      case 'bg' :
        $number = str_replace($country, '', strtolower($vat_id));

        if (strlen($vat_id) == 11) {
          if (strlen($number) == 9 && is_numeric($number)) {
            return 1;
          } else {
            return 0;
          }
        } elseif (strlen($vat_id) == 12) {
          if (strlen($number) == 10 && is_numeric($number)) {
            return 1;
          } else {
            return 0;
          }
        } else {
          return 0;
        }
        break;

      // zypern
      case 'cy' :
        if (strlen($vat_id) != 11) {
          return 0;
        }

        $number = str_replace($country, '', strtolower($vat_id));

        if (strlen($number) == 9) {
          return 1;
        } else {
          return 0;
        }
        break;

      // tschechische republik
      case 'cz' :
        $number = str_replace($country, '', strtolower($vat_id));
        if (strlen($vat_id) == 10) {
          if (strlen($number) == 8 && is_numeric($number)) {
            return 1;
          } else {
            return 0;
          }
        } elseif (strlen($vat_id) == 11) {
          if (strlen($number) == 9 && is_numeric($number)) {
            return 1;
          } else {
            return 0;
          }
        } elseif (strlen($vat_id) == 12) {
          if (strlen($number) == 10 && is_numeric($number)) {
            return 1;
          } else {
            return 0;
          }
        } else {
          return 0;
        }
        break;

      // deutschland
      case 'de' :
        $number = str_replace($country, '', strtolower($vat_id));
        if (strlen($vat_id) == 11) {
          if (strlen($number) == 9 && is_numeric($number)) {
            return 1;
          } else {
            return 0;
          }
        } else {
          return 0;
        }
        break;

      // dänemark
      case 'dk' :
        $number = str_replace($country, '', strtolower($vat_id));
        if (strlen($vat_id) == 10) {
          if (strlen($number) == 8 && is_numeric($number)) {
            return 1;
          } else {
            return 0;
          }
        } else {
          return 0;
        }
        break;

      // estland
      case 'ee' :
        $number = str_replace($country, '', strtolower($vat_id));
        if (strlen($vat_id) == 11) {
          if (strlen($number) == 9 && is_numeric($number)) {
            return 1;
          } else {
            return 0;
          }
        } else {
          return 0;
        }
        break;

      // griechenland
      case 'el' :
        $number = str_replace($country, '', strtolower($vat_id));
        if (strlen($vat_id) == 11) {
          if (strlen($number) == 9 && is_numeric($number)) {
            return 1;
          } else {
            return 0;
          }
        } else {
          return 0;
        }
        break;

      // spanien
      case 'es' :
        $number = str_replace($country, '', strtolower($vat_id));
        if (strlen($vat_id) == 11) {
          if (strlen($number) == 9) {
            return 1;
          } else {
            return 0;
          }
        } else {
          return 0;
        }
        break;

      // finnland
      case 'fi' :
        $number = str_replace($country, '', strtolower($vat_id));
        if (strlen($vat_id) == 10) {
          if (strlen($number) == 8 && is_numeric($number)) {
            return 1;
          } else {
            return 0;
          }
        } else {
          return 0;
        }
        break;

      // frankreich
      case 'fr' :
        $number = substr(str_replace($country, '', strtolower($vat_id)),2);
        if (strlen($vat_id) == 13) {
          if (strlen($number) == 9 && is_numeric($number)) {
            return 1;
          } else {
            return 0;
          }
        } else {
          return 0;
        }
        break;

      // england
      case 'gb' :
        $number = str_replace($country, '', strtolower($vat_id));
        if (strlen($vat_id) == 11) {
          if (strlen($number) == 9) {
            return 1;
          } else {
            return 0;
          }
        } elseif (strlen($vat_id) == 14) {
          if (strlen($number) == 12) {
            return 1;
          } else {
            return 0;
          }
        } else {
          return 0;
        }
        break;

      // ungarn
      case 'hu' :
        $number = str_replace($country, '', strtolower($vat_id));
        if (strlen($vat_id) == 10) {
          if (strlen($number) == 8 && is_numeric($number)) {
            return 1;
          } else {
            return 0;
          }
        } else {
          return 0;
        }
        break;

      // irland
      case 'ie' :
        $number = str_replace($country, '', strtolower($vat_id));
        if (strlen($vat_id) == 10) {
          if (strlen($number) == 8) {
            return 1;
          } else {
            return 0;
          }
        } else {
          return 0;
        }
        break;

      // italien
      case 'it' :
        $number = str_replace($country, '', strtolower($vat_id));
        if (strlen($vat_id) == 13) {
          if (strlen($number) == 11 && is_numeric($number)) {
            return 1;
          } else {
            return 0;
          }
        } else {
          return 0;
        }
        break;

      // litauen
      case 'lt' :
        $number = str_replace($country, '', strtolower($vat_id));
        if (strlen($vat_id) == 11) {
          if (strlen($number) == 9 && is_numeric($number)) {
            return 1;
          } else {
            return 0;
          }
        } elseif (strlen($vat_id) == 14) {
          if (strlen($number) == 12 && is_numeric($number)) {
            return 1;
          } else {
            return 0;
          }
        } else {
          return 0;
        }
        break;

      // luxemburg
      case 'lu' :
        $number = str_replace($country, '', strtolower($vat_id));
        if (strlen($vat_id) == 10) {
          if (strlen($number) == 8 && is_numeric($number)) {
            return 1;
          } else {
            return 0;
          }
        } else {
          return 0;
        }
        break;

      // lettland
      case 'lv' :
        $number = str_replace($country, '', strtolower($vat_id));
        if (strlen($vat_id) == 13) {
          if (strlen($number) == 11 && is_numeric($number)) {
            return 1;
          } else {
            return 0;
          }
        } else {
          return 0;
        }
        break;

      // malta
      case 'mt' :
        $number = str_replace($country, '', strtolower($vat_id));
        if (strlen($vat_id) == 10) {
          if (strlen($number) == 8 && is_numeric($number)) {
            return 1;
          } else {
            return 0;
          }
        } else {
          return 0;
        }
        break;
        
      // niederlande
      case 'nl' :
        $number = str_replace($country, '', strtolower($vat_id));
        if (strlen($vat_id) == 14) {
          if (strlen($number) == 12) {
            return 1;
          } else {
            return 0;
          }
        } else {
          return 0;
        }
        break;

      // polen
      case 'pl' :
        $number = str_replace($country, '', strtolower($vat_id));
        if (strlen($vat_id) == 12) {
          if (strlen($number) == 10 && is_numeric($number)) {
            return 1;
          } else {
            return 0;
          }
        } else {
          return 0;
        }
        break;

      // portugal
      case 'pt' :
        $number = str_replace($country, '', strtolower($vat_id));
        if (strlen($vat_id) == 11) {
          if (strlen($number) == 9 && is_numeric($number)) {
            return 1;
          } else {
            return 0;
          }
        } else {
          return 0;
        }
        break;

      // rumänien
      case 'ro' :
        $number = str_replace($country, '', strtolower($vat_id));

        if (strlen($vat_id) > 1 && strlen($vat_id) < 11) {
          if (is_numeric($number)) {
            return 1;
          } else {
            return 0;
          }
        } else {
          return 0;
        }
        break;

      // schweden
      case 'se' :
        $number = str_replace($country, '', strtolower($vat_id));
        if (strlen($vat_id) == 14) {
          if (strlen($number) == 12 && is_numeric($number)) {
            return 1;
          } else {
            return 0;
          }
        } else {
          return 0;
        }
      break;

      // slowenien
      case 'si' :
        $number = str_replace($country, '', strtolower($vat_id));
        if (strlen($vat_id) == 10) {
          if (strlen($number) == 8 && is_numeric($number)) {
            return 1;
          } else {
            return 0;
          }
        } else {
          return 0;
        }
        break;

      // slowakei
      case 'sk' :
        $number = str_replace($country, '', strtolower($vat_id));
        if (strlen($vat_id) == 12) {
          if (strlen($number) == 10 && is_numeric($number)) {
            return 1;
          } else {
            return 0;
          }
        } else {
          return 0;
        }
        break;

      default:
        return 8;
    }
  }

}
