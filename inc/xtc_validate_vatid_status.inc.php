<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_validate_vatid_status.inc.php 3198 2012-07-11 09:41:52Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2006 XT-Commerce (xtc_validate_vatid_status.inc.php 899 2005-04-29)
   
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Return all status info values for a customer_id in catalog, need to check session registered customer or will return dafault guest customer status value !
function xtc_validate_vatid_status($customer_id) {

    $customer_status_query = xtc_db_query("SELECT customers_vat_id_status 
                                             FROM " . TABLE_CUSTOMERS . "
                                            WHERE customers_id='" . (int)$customer_id . "'");
    $customer_status_value = xtc_db_fetch_array($customer_status_query);

    // BOF - Dokuman - 2011-09-13 - display correct error code of VAT ID check
    switch ($customer_status_value['customers_vat_id_status']) {
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
      case '0' :
        $entry_vat_error_text = TEXT_VAT_FALSE;
        break;
      case '1' :
        $entry_vat_error_text = TEXT_VAT_TRUE;
        break;
      case '2' :
        $entry_vat_error_text = TEXT_VAT_CONNECTION_NOT_POSSIBLE;
        break;
      case '8' :
        $entry_vat_error_text = TEXT_VAT_UNKNOWN_COUNTRY;
        break;
      case '94' :
        $entry_vat_error_text = TEXT_VAT_INVALID_INPUT;
        break;
      case '95' :
        $entry_vat_error_text = TEXT_VAT_SERVICE_UNAVAILABLE;
        break;
      case '96' :
        $entry_vat_error_text = TEXT_VAT_MS_UNAVAILABLE;
        break;
      case '97' :
        $entry_vat_error_text = TEXT_VAT_TIMEOUT;
        break;
      case '98' :
        $entry_vat_error_text = TEXT_VAT_SERVER_BUSY;
        break;
      case '99' :
        $entry_vat_error_text = TEXT_VAT_NO_PHP5_SOAP_SUPPORT;
        break;
      default:
        $entry_vat_error_text = '';
        break;
    }
    // EOF - Dokuman - 2011-09-13 - display correct error code of VAT ID check

   return $entry_vat_error_text;
}
?>