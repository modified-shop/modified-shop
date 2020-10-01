<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  require_once(DIR_FS_EXTERNAL.'internetmarke/autoload.php');
  require_once(DIR_FS_CATALOG.'includes/classes/class.logger.php');


  class mod_internetmarke {
    public $error = false;
    private $service;
    private $user_token;
    private $order;
    
    function __construct($oID = '') {
      global $messageStack;
      
      $this->logger = new LoggingManager(DIR_FS_LOG.'mod_%s_internetmarke_%s.log', 'internetmarke', 'debug');

      $partner_info = new \Internetmarke\PartnerInformation('AMAMO', 1, '13RJboRMCxUxMWltIRruVsNmu9oCBtzr');
      $this->service = new \Internetmarke\Service($partner_info);
      
      if (MODULE_INTERNETMARKE_PORTO_USER != ''
          && MODULE_INTERNETMARKE_PORTO_PASS != ''
          )
      {
        try {
          $this->user_token = $this->service->authenticateUser(MODULE_INTERNETMARKE_PORTO_USER, MODULE_INTERNETMARKE_PORTO_PASS)->getUserToken();
        } catch (exception $ex) {
          $this->error = true;
          $messageStack->add_session($ex->getMessage(), 'error');
          $this->logger->log('DEBUG', 'authenticateUser', (array)$ex->detail->AuthenticateUserException);
        }
      }
      
      if ($oID != '') {
        $this->order = new order($oID);
      }
    }
    
    
    function createLabel($data) {
      global $messageStack;
      
      if ($this->user_token == '') {
        $messageStack->add_session('invalid token', 'error');
        return false;
      }
      
      $price_query = xtc_db_query("SELECT PROPR
                                     FROM `internetmarke`
                                    WHERE PROID = '".$data['product']."'");
      $price = xtc_db_fetch_array($price_query);
      
      $sender = new \Internetmarke\NamedAddress(
          new \Internetmarke\Name(null, new \Internetmarke\CompanyName($this->encode_utf8(MODULE_INTERNETMARKE_COMPANY), new \Internetmarke\PersonName('', '', $this->encode_utf8(MODULE_INTERNETMARKE_FIRSTNAME), $this->encode_utf8(MODULE_INTERNETMARKE_LASTNAME)))),
          new \Internetmarke\Address($this->encode_utf8(MODULE_INTERNETMARKE_SUBURB), $this->encode_utf8(MODULE_INTERNETMARKE_STREET), '', $this->encode_utf8(MODULE_INTERNETMARKE_PLZ), $this->encode_utf8(MODULE_INTERNETMARKE_CITY), 'Country')
      );
      
      $person = new \Internetmarke\PersonName('', '', $this->encode_utf8($this->order->delivery['firstname']), $this->encode_utf8($this->order->delivery['lastname']));
      $receiver = new \Internetmarke\NamedAddress(
          new \Internetmarke\Name((($this->order->delivery['company'] == '') ? $person : null), (($this->order->delivery['company'] != '') ? new \Internetmarke\CompanyName($this->encode_utf8($this->order->delivery['company']), $person) : null)),
          new \Internetmarke\Address($this->encode_utf8($this->order->delivery['suburb']), $this->encode_utf8($this->order->delivery['street_address']), '', $this->encode_utf8($this->order->delivery['postcode']), $this->encode_utf8($this->order->delivery['city']), $this->get_country_iso_3($this->order->delivery['country_iso_2']))
      );
      
      $address_binding = new \Internetmarke\AddressBinding($sender, $receiver);

      $order_item = new \Internetmarke\OrderItem($data['product'], null, $address_binding, new \Internetmarke\Position($data['column'],$data['row'],1), 'AddressZone');

      $order_id = $this->service->createShopOrderId($this->user_token);
      
      try {
        $label = $this->service->checkoutShoppingCartPdf($this->user_token, $data['format'], array($order_item), (string)($price['PROPR'] * 100), $order_id, null, true, 2);
        
        foreach ($label->shoppingCart->voucherList->voucher as $voucher) {
          $sql_data_array = array(
            'orders_id' => $this->order->info['orders_id'],
            'carrier_id' => MODULE_INTERNETMARKE_CARRIER,
            'parcel_id' => $voucher->trackId,
            'date_added' => 'now()',
            'external' => '1',
            'im_orders_id' => $label->shoppingCart->shopOrderId,
            'im_url' => $label->link,
          );
          xtc_db_perform(TABLE_ORDERS_TRACKING, $sql_data_array);
        }
        
        $messageStack->add_session(TEXT_IM_LABEL_CREATED, 'success');
      } catch (exception $ex) {
        $this->error = true;
        $messageStack->add_session($ex->getMessage(), 'error');
        $this->logger->log('DEBUG', 'checkoutShoppingCartPdf', (array)$ex->detail->ShoppingCartValidationException->errors);
      }
    }

    
    function getPageFormats($id = '', $single = false) {
      global $messageStack;
      
      $formats_array = array();

      try {
        $formats = $this->service->retrievePageFormats();
        
        foreach ($formats as $PageFormat) {
          $PageLayout = $PageFormat->getPageLayout();
          $LabelCount = $PageLayout->getLabelCount();
          
          $formats_array[$PageFormat->getId()] = array(
            'id' => (string)$PageFormat->getId(),
            'text' => $PageFormat->getName(),
            'labelX' => $LabelCount->getLabelX(),
            'labelY' => $LabelCount->getLabelY(),
          );
        }

      } catch (exception $ex) {
        $this->error = true;
        $messageStack->add_session($ex->getMessage(), 'error');
      }
      
      ksort($formats_array);
      
      if ($id != '') {
        $id_array = explode(',', $id);
                
        if ($single === false) {
          $selected_formats_array = array();
          foreach ($id_array as $id) {
            $selected_formats_array[$id] = $formats_array[$id];
          }
          return $selected_formats_array;
        } else {
          return $formats_array[$id_array[0]];
        }
      }
      return $formats_array;
    }
    
    
    function get_country_iso_3($country_iso_2) {
      $countries_query = xtc_db_query("SELECT countries_iso_code_3
                                         FROM ".TABLE_COUNTRIES."
                                        WHERE countries_iso_code_2 = '".xtc_db_input($country_iso_2)."'");
      if (xtc_db_num_rows($countries_query) > 0) {
        $countries = xtc_db_fetch_array($countries_query);
        return $countries['countries_iso_code_3'];
      }
      
      return $country_iso_2;
    }
    

    function encode_utf8($string) {
      if (is_array($string)) {
        foreach ($string as $key => $value) {
          $string[$key] = $this->encode_utf8($value);
        }
      } else {
        $string = decode_htmlentities($string);
        $cur_encoding = mb_detect_encoding($string);
        if ($cur_encoding == "UTF-8" && mb_check_encoding($string, "UTF-8")) {
          return $string;
        } else {
          return mb_convert_encoding($string, "UTF-8", $_SESSION['language_charset']);
        }
      }
    
      return $string;  
    }

    
    function getError() {
      return $this->error;
    }
  }
?>