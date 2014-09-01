<?php
/* -----------------------------------------------------------------------------------------
   $Id: paypal_checkout.php 3417 2012-08-11 12:09:26Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2003-2007 xt:Commerce (Winger/Zanier), www.xt-commerce.com
   (c) 2002-2003 osCommerce; www.oscommerce.com
   (c) 2003-2007 Zen Cart Development Team
   (c) 2004 DevosC.com

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contribution:

   ab 15.08.2008 Teile vom Hamburger-Internetdienst geändert
   Hamburger-Internetdienst Support Forums at www.forum.hamburger-internetdienst.de
   Stand: 03.05.2012

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

if (defined('PAYPAL_API_VERSION')) {
  require_once(DIR_FS_INC . 'xtc_write_user_info.inc.php');
  define('PROXY_HOST', '127.0.0.1');
  define('PROXY_PORT', '808');
  define('VERSION', PAYPAL_API_VERSION);
  class paypal_checkout {
    var $API_UserName,
        $API_Password,
        $API_Signature,
        $API_Endpoint,
        $version,
        $location_error,
        $NOTIFY_URL,
        $EXPRESS_CANCEL_URL,
        $EXPRESS_RETURN_URL,
        $CANCEL_URL,
        $RETURN_URL,
        $GIROPAY_SUCCESS_URL,
        $GIROPAY_CANCEL_URL,
        $BANKTXN_PENDING_URL,
        $EXPRESS_URL,
        $GIROPAY_URL,
        $IPN_URL,
        $ppAPIec,
        $payPalURL;
  /*************************************************************/
    function paypal_checkout() {
      // Stand: 27.03.2010
      if(PAYPAL_MODE=='sandbox'){
        $this->API_UserName    = PAYPAL_API_SANDBOX_USER;
        $this->API_Password    = PAYPAL_API_SANDBOX_PWD;
        $this->API_Signature  = PAYPAL_API_SANDBOX_SIGNATURE;
        $this->API_Endpoint    = 'https://api-3t.sandbox.paypal.com/nvp';
        $this->EXPRESS_URL    = 'https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=';
        $this->GIROPAY_URL    = 'https://www.sandbox.paypal.com/webscr?cmd=_complete-express-checkout&token=';
        $this->IPN_URL        = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
      }elseif(PAYPAL_MODE=='live'){
        $this->API_UserName    = PAYPAL_API_USER;
        $this->API_Password    = PAYPAL_API_PWD;
        $this->API_Signature  = PAYPAL_API_SIGNATURE;
        $this->API_Endpoint    = 'https://api-3t.paypal.com/nvp';
        $this->EXPRESS_URL    = 'https://www.paypal.com/webscr?cmd=_express-checkout&token=';
        $this->GIROPAY_URL    = 'https://www.paypal.com/webscr?cmd=_complete-express-checkout&token=';
        $this->IPN_URL        = 'https://www.paypal.com/cgi-bin/webscr';
      }
      if(ENABLE_SSL == true){
        $this->NOTIFY_URL = HTTPS_SERVER.DIR_WS_CATALOG.'callback/paypal/ipn.php';
        $this->EXPRESS_CANCEL_URL = HTTPS_SERVER.DIR_WS_CATALOG.FILENAME_SHOPPING_CART.'?'.xtc_session_name().'='.xtc_session_id();
        $this->EXPRESS_RETURN_URL = HTTPS_SERVER.DIR_WS_CATALOG.FILENAME_PAYPAL_CHECKOUT.'?'.xtc_session_name().'='.xtc_session_id();
        $this->PRE_CANCEL_URL = HTTPS_SERVER.DIR_WS_CATALOG.FILENAME_CHECKOUT_PAYMENT.'?'.xtc_session_name().'='.xtc_session_id();
        $this->CANCEL_URL = HTTPS_SERVER.DIR_WS_CATALOG.FILENAME_CHECKOUT_PAYMENT.'?'.xtc_session_name().'='.xtc_session_id().'&error=true&error_message='.PAYPAL_ERROR;
        $this->RETURN_URL = HTTPS_SERVER.DIR_WS_CATALOG.FILENAME_CHECKOUT_PROCESS.'?'.xtc_session_name().'='.xtc_session_id();
        $this->GIROPAY_SUCCESS_URL = HTTPS_SERVER.DIR_WS_CATALOG.FILENAME_CHECKOUT_SUCCESS.'?'.xtc_session_name().'='.xtc_session_id();
        $this->GIROPAY_CANCEL_URL = HTTPS_SERVER.DIR_WS_CATALOG.FILENAME_SHOPPING_CART.'?'.xtc_session_name().'='.xtc_session_id();
        $this->BANKTXN_PENDING_URL = HTTPS_SERVER.DIR_WS_CATALOG.FILENAME_CHECKOUT_SUCCESS.'?'.xtc_session_name().'='.xtc_session_id();
      }else{
        $this->NOTIFY_URL = HTTP_SERVER.DIR_WS_CATALOG.'callback/paypal/ipn.php';
        $this->EXPRESS_CANCEL_URL = HTTP_SERVER.DIR_WS_CATALOG.FILENAME_SHOPPING_CART.'?'.xtc_session_name().'='.xtc_session_id();
        $this->EXPRESS_RETURN_URL = HTTP_SERVER.DIR_WS_CATALOG.FILENAME_PAYPAL_CHECKOUT.'?'.xtc_session_name().'='.xtc_session_id();
        $this->PRE_CANCEL_URL = HTTP_SERVER.DIR_WS_CATALOG.FILENAME_CHECKOUT_PAYMENT.'?'.xtc_session_name().'='.xtc_session_id();
        $this->CANCEL_URL = HTTP_SERVER.DIR_WS_CATALOG.FILENAME_CHECKOUT_PAYMENT.'?'.xtc_session_name().'='.xtc_session_id().'&error=true&error_message='.PAYPAL_ERROR;
        $this->RETURN_URL = HTTP_SERVER.DIR_WS_CATALOG.FILENAME_CHECKOUT_PROCESS.'?'.xtc_session_name().'='.xtc_session_id();
        $this->GIROPAY_SUCCESS_URL = HTTP_SERVER.DIR_WS_CATALOG.FILENAME_CHECKOUT_SUCCESS.'?'.xtc_session_name().'='.xtc_session_id();
        $this->GIROPAY_CANCEL_URL = HTTP_SERVER.DIR_WS_CATALOG.FILENAME_SHOPPING_CART.'?'.xtc_session_name().'='.xtc_session_id();
        $this->BANKTXN_PENDING_URL = HTTP_SERVER.DIR_WS_CATALOG.FILENAME_CHECKOUT_SUCCESS.'?'.xtc_session_name().'='.xtc_session_id();
      }
      $this->version   = VERSION;
      $this->USE_PROXY = FALSE;
      $this->payPalURL = '';
      $this->ppAPIec = $this->buildAPIKey(PAYPAL_API_KEY);
      if(ENABLE_SSL == true) {
        $hdrImg='templates/'.CURRENT_TEMPLATE.'/img/'.PAYPAL_API_IMAGE;
        if(file_exists(DIR_FS_CATALOG.$hdrImg) && PAYPAL_API_IMAGE!='') {
          $hdrSize = getimagesize(DIR_FS_CATALOG.$hdrImg);
          if($hdrSize[0]<=750 && $hdrSize[1]<=90) {
            $this->Image = urlencode(HTTPS_SERVER.DIR_WS_CATALOG.$hdrImg);
          }
        }
      }
      if(preg_match('/^(([a-f]|[A-F]|[0-9]){6})$/',PAYPAL_API_CO_BACK)) {
        $this->BackColor = PAYPAL_API_CO_BACK;
      }
      if(preg_match('/^(([a-f]|[A-F]|[0-9]){6})$/',PAYPAL_API_CO_BORD)) {
        $this->BorderColor = PAYPAL_API_CO_BORD;
      }
    }
  /*************************************************************/
    function build_express_checkout_button(){
      // Stand: 01.06.2009
      global $PHP_SELF;
      if (defined('MODULE_PAYMENT_PAYPALEXPRESS_STATUS')) {
        if($_SESSION['allow_checkout'] == 'true' && $_SESSION['cart']->show_total()>0 && MODULE_PAYMENT_PAYPALEXPRESS_STATUS=='True') {
          $unallowed_modules = explode(',', $_SESSION['customers_status']['customers_status_payment_unallowed']);
          if(!in_array('paypalexpress', $unallowed_modules)) {
            include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/payment/paypalexpress.php');
            $alt=((defined('MODULE_PAYMENT_PAYPALEXPRESS_ALT_BUTTON'))? MODULE_PAYMENT_PAYPALEXPRESS_ALT_BUTTON :'PayPal');
            $source=((strtoupper($_SESSION['language_code'])=='DE')?'epaypal_de.gif':'epaypal_en.gif');
            $button = '<a style="cursor:pointer;" onfocus="if(this.blur) this.blur();" onmouseover="window.status = '."''".'; return true;" href="'.xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('action')).'action=paypal_express_checkout').'"><img src="'.DIR_WS_ICONS.$source.'" alt="'.$alt.'" title="'.$alt.'" /></a>';
            return $button;
          }
        }
      }
      return;
    }
  /*************************************************************/
    function build_express_fehler_button(){
      // Stand: 01.06.2009
      if(MODULE_PAYMENT_PAYPALEXPRESS_STATUS=='True'){
        include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/payment/paypalexpress.php');
        $alt=((defined('MODULE_PAYMENT_PAYPALEXPRESS_ALT_BUTTON'))? MODULE_PAYMENT_PAYPALEXPRESS_ALT_BUTTON :'PayPal');
        $source=((strtoupper($_SESSION['language_code'])=='DE')?'epaypal_de.gif':'epaypal_en.gif');
        $button .= '<a style="cursor:pointer;" onfocus="if(this.blur) this.blur();" onmouseover="window.status = '."''".'; return true;" href="'.$this->EXPRESS_CANCEL_URL.'"><img src="'.DIR_WS_ICONS.$source.'" alt="'.$alt.'" title="'.$alt.'" /></a>';
        return $button;
      }
      return;
    }
  /*************************************************************/
  /******* fürs express als Zahlbedingung **********************/
  /*************************************************************/
    function paypal_auth_call(){
      // aufruf aus paypal.php NICHT für PP Express aus Warenkorb
      // Daten aus der Cart - Order noch nicht gespeichert
      // 1. Call um die Token ID zu bekommen
      // Daten mitgeben, da direkt bestätigung ohne nochmaliges Confirm im Shop
      // Stand: 05.01.2010
      global $xtPrice,$order,$order_totals;
      // Session säubern
      unset($_SESSION['reshash']);
      unset($_SESSION['nvpReqArray']);
      // BOF - franky_n - 2011-12-05 - dont redeclare the class if already declared in checkout_process.php
      if (!class_exists('order_total')) {
        require(DIR_WS_CLASSES.'order_total.php');
        $order_total_modules = new order_total();
        $order_totals = $order_total_modules->process();
      }
      // EOF - franky_n - 2011-12-05 - dont redeclare the class if already declared in checkout_process.php
      $order_tax=0;
      $order_discount=0;
      $order_fee=0;
      $order_gs=0;
      $order_shipping=0;
      for($i = 0, $n = sizeof($order_totals); $i < $n; $i ++) {
        switch($order_totals[$i]['code']) {
          case 'ot_total':
            $paymentAmount=$order_totals[$i]['value'];
            break;
          case 'ot_shipping':
            $order_shipping=$order_totals[$i]['value'];
            break;
          case 'ot_tax':
            $order_tax+=$order_totals[$i]['value'];
            break;
          case 'ot_discount':
            $order_discount+=$order_totals[$i]['value'];
            break;
          case 'ot_coupon':
            $order_gs+= ($order_totals[$i]['value'] < 0) ? $order_totals[$i]['value'] : $order_totals[$i]['value'] *(-1);
            break;
          case 'ot_gv':
            $order_gs+= ($order_totals[$i]['value'] < 0) ? $order_totals[$i]['value'] : $order_totals[$i]['value'] *(-1);
            break;
          ///  customers bonus
          case 'ot_bonus_fee':
            $order_gs+= ($order_totals[$i]['value'] < 0) ? $order_totals[$i]['value'] : $order_totals[$i]['value'] *(-1);
            break;
          case 'ot_payment':
            if($order_totals[$i]['value'] < 0) {
              // Rabatt aus Fremd Modul
              $order_discount+=$order_totals[$i]['value'];
            } else {
              $order_fee+=$order_totals[$i]['value'];
            }
            break;
          case 'ot_cod_fee':
            $order_fee+=$order_totals[$i]['value'];
            break;
          case 'ot_ps_fee':
            $order_fee+=$order_totals[$i]['value'];
            break;
          case 'ot_loworderfee':
            $order_fee+=$order_totals[$i]['value'];
        }
      }
      // AMT
      $paymentAmount = round($paymentAmount, $xtPrice->get_decimal_places($order->info['currency']));
      // Summen der Order
      $order_tax=round($order_tax, $xtPrice->get_decimal_places($order->info['currency']));
      $order_discount=round($order_discount, $xtPrice->get_decimal_places($order->info['currency']));
      $order_gs=round($order_gs, $xtPrice->get_decimal_places($order->info['currency']));
      $order_fee=round($order_fee, $xtPrice->get_decimal_places($order->info['currency']));
      $order_shipping=round($order_shipping, $xtPrice->get_decimal_places($order->info['currency']));
      $nvp_products=$this->paypal_get_products($paymentAmount,$order_tax,$order_discount,$order_fee,$order_shipping,$order_gs);
      $paymentAmount = urlencode(number_format($paymentAmount, $xtPrice->get_decimal_places($order->info['currency']), '.', ','));
      $currencyCodeType = urlencode($order->info['currency']);
      // Payment Type
      $paymentType='Sale';
      // The returnURL is the location where buyers return when a
      // payment has been succesfully authorized.
      // The cancelURL is the location buyers are sent to when they hit the
      // cancel button during authorization of payment during the PayPal flow
      $returnURL =urlencode($this->RETURN_URL);
      $cancelURL =urlencode($this->CANCEL_URL);
      $gpsucssesURL =urlencode($this->GIROPAY_SUCCESS_URL);
      $gpcancelURL =urlencode($this->GIROPAY_CANCEL_URL);
      $bankpending =urlencode($this->BANKTXN_PENDING_URL);
      // Construct the parameter string that describes the PayPal payment
      // the varialbes were set in the web form, and the resulting string
      // is stored in $nvpstr
      $sh_name = urlencode($this->mn_iconv($_SESSION['language_charset'], "UTF-8", $order->delivery['firstname'].' '.$order->delivery['lastname']));
      $sh_street = urlencode($this->mn_iconv($_SESSION['language_charset'], "UTF-8", $order->delivery['street_address']));
      $sh_street_2 = '';
      $sh_city = urlencode($this->mn_iconv($_SESSION['language_charset'], "UTF-8", $order->delivery['city']));
      $sh_zip = urlencode($order->delivery['postcode']);
      $sh_state = urlencode($this->state_code($order->delivery['state']));
      $sh_countrycode = urlencode($order->delivery['country']['iso_code_2']);
      $sh_countryname = urlencode($this->mn_iconv($_SESSION['language_charset'], "UTF-8",$order->delivery['country']['title']));
      $sh_phonenum = urlencode($order->customer['telephone']);
      // String zusammenbauen
      $nvpstr="&AMT=".$paymentAmount.
              "&CURRENCYCODE=".$currencyCodeType.
              "&PAYMENTACTION=".$paymentType.
              "&LOCALECODE=".$_SESSION['language_code'].
              "&RETURNURL=".$returnURL.
              "&CANCELURL=".$cancelURL.
              "&GIROPAYSUCCESSURL=".$gpsucssesURL.
              "&GIROPAYCANCELURL=".$gpcancelURL.
              "&BANKTXNPENDINGURL=".$bankpending.
              "&HDRIMG=".$this->Image.
              "&HDRBORDERCOLOR=".$this->BorderColor.
              "&HDRBACKCOLOR=".$this->BackColor.
              "&CUSTOM=".''.
              "&SHIPTONAME=".$sh_name.
              "&SHIPTOSTREET=".$sh_street.
              "&SHIPTOSTREET2=".$sh_street2.
              "&SHIPTOCITY=".$sh_city.
              "&SHIPTOZIP=".$sh_zip.
              "&SHIPTOSTATE=".$sh_state.
              "&SHIPTOCOUNTRYCODE=".$sh_countrycode.
              "&SHIPTOCOUNTRYNAME=".$sh_countryname.
              "&PHONENUM=".$sh_phonenum.
              "&ALLOWNOTE=0".
              "&ADDROVERRIDE=1";
      // Artikel Details mitgeben
      $nvpstr.=$nvp_products;
      // Senden
      $resArray=$this->hash_call("SetExpressCheckout",$nvpstr);
      $_SESSION['reshash']= $resArray;
      $ack = strtoupper($resArray["ACK"]);
      if($ack!="SUCCESS") {
        if(PAYPAL_ERROR_DEBUG=='true') {
          $this->build_error_message($_SESSION['reshash']);
        } else {
          $_SESSION['reshash']['FORMATED_ERRORS'] = PAYPAL_NOT_AVIABLE;
        }
        xtc_redirect($this->PRE_CANCEL_URL);
      }
      if($ack=="SUCCESS"){
        $token = urldecode($resArray["TOKEN"]);
        $this->payPalURL = $this->EXPRESS_URL.''.$token;
        return $this->payPalURL;
      }
    }
  /*************************************************************/
  /******* fürs express aus dem warenkorb **********************/
  /*************************************************************/
    function paypal_express_auth_call(){
      // aufruf aus cart_actions.php
      // 1. Call um die Token ID zu bekommen
      // Steuer, Artikel usw bei eingeloggt
      // Stand: 03.05.2012
      global $xtPrice,$order;
      // Session säubern
      unset($_SESSION['reshash']);
      unset($_SESSION['nvpReqArray']);
      // Shipping:
      if(!isset($_SESSION['sendto'])) {
        $_SESSION['sendto'] = $_SESSION['customer_default_address_id'];
      } else {
        // verify the selected shipping address
        $check_address_query = xtc_db_query("select count(*) as total from ".TABLE_ADDRESS_BOOK." where customers_id = '".(int) $_SESSION['customer_id']."' and address_book_id = '".(int) $_SESSION['sendto']."'");
        $check_address = xtc_db_fetch_array($check_address_query);
        if($check_address['total'] != '1') {
          $_SESSION['sendto'] = $_SESSION['customer_default_address_id'];
        }
      }
      // Shipping beim 1. Call auf jeden Fall löschen falls Änderungen im WK
      if(isset($_SESSION['shipping']))unset($_SESSION['shipping']);
      // Shipping END
      require(DIR_WS_CLASSES.'order.php');
      $order = new order();
      require(DIR_WS_CLASSES.'order_total.php');
      $order_total_modules = new order_total();
      $order_totals = $order_total_modules->process();
      $order_tax=0;
      $order_discount=0;
      $order_gs=0;
      $order_fee=0;
      $order_shipping=0;
      for($i = 0, $n = sizeof($order_totals); $i < $n; $i ++) {
        switch($order_totals[$i]['code']) {
          case 'ot_discount':
            $order_discount+=$order_totals[$i]['value'];
            break;
          case 'ot_coupon':
          case 'ot_coupon':
            $order_gs+= ($order_totals[$i]['value'] < 0) ? $order_totals[$i]['value'] : $order_totals[$i]['value'] *(-1);
            break;
          case 'ot_gv':
            $order_gs+= ($order_totals[$i]['value'] < 0) ? $order_totals[$i]['value'] : $order_totals[$i]['value'] *(-1);
            break;
          ///  customers bonus
          case 'ot_bonus_fee':
            $order_gs+= ($order_totals[$i]['value'] < 0) ? $order_totals[$i]['value'] : $order_totals[$i]['value'] *(-1);
            break;
          case 'ot_payment':
            if($order_totals[$i]['value'] < 0) {
              // Rabatt aus Fremd Modul
              $order_discount+=$order_totals[$i]['value'];
            } else {
              $order_fee+=$order_totals[$i]['value'];
            }
            break;
          case 'ot_cod_fee':
            $order_fee+=$order_totals[$i]['value'];
            break;
          case 'ot_ps_fee':
            $order_fee+=$order_totals[$i]['value'];
            break;
          case 'ot_loworderfee':
            $order_fee+=$order_totals[$i]['value'];
        }
      }
      // AMT
      $paymentAmount=$_SESSION['cart']->show_total()
                    +$order_discount
                    +$order_gs
                    +$order_fee;
      // Durch Kupon oder irgendwas auf unter 0 -> Kein PP Express sinnvoll
      if($paymentAmount<=0) {
        $_SESSION['reshash']['FORMATED_ERRORS'] = PAYPAL_AMMOUNT_NULL;
        $this->payPalURL = $this->EXPRESS_CANCEL_URL;
        return $this->payPalURL;
      }
      if($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
        $order_tax=$_SESSION['cart']->show_tax(false);
      }
      // Vorläufige Versandkosten
      if(PAYPAL_EXP_VORL!='' && PAYPAL_EXP_VERS!=0) {
        $paymentAmount+=PAYPAL_EXP_VERS;
      }
      // AMT
      $paymentAmount = round($paymentAmount, $xtPrice->get_decimal_places($order->info['currency']));
      // Summen der Order
      $order_tax=round($order_tax, $xtPrice->get_decimal_places($order->info['currency']));
      $order_discount=round($order_discount, $xtPrice->get_decimal_places($order->info['currency']));
      $order_gs=round($order_gs, $xtPrice->get_decimal_places($order->info['currency']));
      $order_fee=round($order_fee, $xtPrice->get_decimal_places($order->info['currency']));
      $nvp_products=$this->paypal_get_products($paymentAmount,$order_tax,$order_discount,$order_fee,$order_shipping,$order_gs,True);
      $paymentAmount = urlencode(number_format($paymentAmount, $xtPrice->get_decimal_places($order->info['currency']), '.', ','));
      $currencyCodeType = urlencode($order->info['currency']);
      // Payment Type
      $paymentType='Sale';
      $returnURL =urlencode($this->EXPRESS_RETURN_URL);
      $cancelURL =urlencode($this->EXPRESS_CANCEL_URL);
      $gpsucssesURL =urlencode($this->GIROPAY_SUCCESS_URL);
      $gpcancelURL =urlencode($this->EXPRESS_CANCEL_URL);
      $bankpending =urlencode($this->BANKTXN_PENDING_URL);
      if(isset($_SESSION['sendto']) && isset($_SESSION['customer_id'])) {
        // User eingeloggt
        $sh_name = urlencode($this->mn_iconv($_SESSION['language_charset'], "UTF-8", $order->delivery['firstname'].' '.$order->delivery['lastname']));
        $sh_street = urlencode($this->mn_iconv($_SESSION['language_charset'], "UTF-8", $order->delivery['street_address']));
        $sh_street_2 = '';
        $sh_city = urlencode($this->mn_iconv($_SESSION['language_charset'], "UTF-8", $order->delivery['city']));
        $sh_zip = urlencode($order->delivery['postcode']);
        $sh_state = urlencode($this->state_code($order->delivery['state']));
        $sh_countrycode = urlencode($order->delivery['country']['iso_code_2']);
        $sh_countryname = urlencode($this->mn_iconv($_SESSION['language_charset'], "UTF-8",$order->delivery['country']['title']));
        $sh_phonenum = urlencode($this->mn_iconv($_SESSION['language_charset'], "UTF-8",$order->customer['telephone']));
        if($_SESSION['paypal_express_new_customer']!='true') {
          $address = "&SHIPTONAME=".$sh_name."&SHIPTOSTREET=".$sh_street."&SHIPTOSTREET2=".$sh_street2."&SHIPTOCITY=".$sh_city."&SHIPTOZIP=".$sh_zip."&SHIPTOSTATE=".$sh_state."&SHIPTOCOUNTRYCODE=".$sh_countrycode."&SHIPTOCOUNTRYNAME=".$sh_countryname."&PHONENUM=".$sh_phonenum;
        }
      }
      // String zusammenbauen
      $nvpstr="&AMT=".$paymentAmount.
              "&CURRENCYCODE=".$currencyCodeType.
              "&PAYMENTACTION=".$paymentType.
              "&LOCALECODE=".$_SESSION['language_code'].
              "&RETURNURL=".$returnURL.
              "&CANCELURL=".$cancelURL.
              "&GIROPAYSUCCESSURL=".$gpsucssesURL.
              "&GIROPAYCANCELURL=".$gpcancelURL.
              "&BANKTXNPENDINGURL=".$bankpending.
              "&HDRIMG=".$this->Image.
              "&HDRBORDERCOLOR=".$this->BorderColor.
              "&HDRBACKCOLOR=".$this->BackColor.
              "&CUSTOM=".''.
              $address.
              "&ALLOWNOTE=0".
              "&ADDROVERRIDE=0";
      // Artikel Details mitgeben
      $nvpstr.=$nvp_products;
      // Make the call to PayPal to set the Express Checkout token
      // If the API call succeded, then redirect the buyer to PayPal
      // to begin to authorize payment.  If an error occured, show the
      // resulting errors
      $resArray=$this->hash_call("SetExpressCheckout",$nvpstr);
      $_SESSION['reshash']= $resArray;
      $ack = strtoupper($resArray["ACK"]);
      if($ack=="SUCCESS"){
        $token = urldecode($resArray["TOKEN"]);
        $this->payPalURL = $this->EXPRESS_URL.''.$token;
        return $this->payPalURL;
      } else  {
        if(PAYPAL_ERROR_DEBUG=='true') {
          $this->build_error_message($_SESSION['reshash']);
        } else {
          $_SESSION['reshash']['FORMATED_ERRORS'] = PAYPAL_NOT_AVIABLE;
        }
        $this->payPalURL = $this->EXPRESS_CANCEL_URL;
        return $this->payPalURL;
      }
    }
  /*************************************************************/
  /******* für abgelehnte Zahlungen **********************/
  /*************************************************************/
    function paypal_second_auth_call($insert_id){
      // aufruf aus shopping_cart.php
      // 1. Call um die Token ID zu bekommen
      // Daten aus der Order !
      // Stand: 17.10.2010
      global $xtPrice,$order;
      // Session säubern
      unset($_SESSION['reshash']);
      unset($_SESSION['nvpReqArray']);
      require(DIR_WS_CLASSES.'order.php');
      $order = new order($insert_id);
      // Amt
      $paymentAmount = round($order->info['pp_total'], $xtPrice->get_decimal_places($order->info['currency']));
      // Summen der Order
      $order_tax = round($order->info['pp_tax'], $xtPrice->get_decimal_places($order->info['currency']));
      $order_discount = round($order->info['pp_disc'], $xtPrice->get_decimal_places($order->info['currency']));
      $order_gs = round($order->info['pp_gs'], $xtPrice->get_decimal_places($order->info['currency']));
      $order_fee = round($order->info['pp_fee'], $xtPrice->get_decimal_places($order->info['currency']));
      $order_shipping = round($order->info['pp_shipping'], $xtPrice->get_decimal_places($order->info['currency']));
      $nvp_products=$this->paypal_get_products($paymentAmount,$order_tax,$order_discount,$order_fee,$order_shipping,$order_gs);
      $paymentAmount = urlencode(number_format($paymentAmount, $xtPrice->get_decimal_places($order->info['currency']), '.', ','));
      $currencyCodeType = urlencode($order->info['currency']);
      // Payment Type
      $paymentType='Sale';
      $returnURL =urlencode($this->EXPRESS_CANCEL_URL);
      $cancelURL =urlencode($this->EXPRESS_CANCEL_URL);
      $gpsucssesURL =urlencode($this->GIROPAY_SUCCESS_URL);
      $gpcancelURL =urlencode($this->EXPRESS_CANCEL_URL);
      $bankpending =urlencode($this->BANKTXN_PENDING_URL);
      $notify_url  = urlencode($this->NOTIFY_URL);
      $inv_num = urlencode(PAYPAL_INVOICE.$insert_id);
      // Versandadresse
      $sh_name = urlencode($this->mn_iconv($_SESSION['language_charset'], "UTF-8", $order->delivery['firstname'].' '.$order->delivery['lastname']));
      $sh_street = urlencode($this->mn_iconv($_SESSION['language_charset'], "UTF-8", $order->delivery['street_address']));
      $sh_street_2 = '';
      $sh_city = urlencode($this->mn_iconv($_SESSION['language_charset'], "UTF-8", $order->delivery['city']));
      $sh_state = urlencode($this->state_code($order->delivery['state']));
      if(is_array($order->delivery['country'])) {
        $sh_countrycode = urlencode($order->delivery['country']['iso_code_2']);
        $sh_countryname = urlencode($this->mn_iconv($_SESSION['language_charset'], "UTF-8",$order->delivery['country']['title']));
      } else {
        $sh_countrycode = urlencode($order->delivery['country_iso_2']);
        $sh_countryname = urlencode($this->mn_iconv($_SESSION['language_charset'], "UTF-8",$order->delivery['country']));
      }
      $sh_phonenum = urlencode($order->customer['telephone']);
      $sh_zip = urlencode($order->delivery['postcode']);
      $address = "&SHIPTONAME=".$sh_name."&SHIPTOSTREET=".$sh_street."&SHIPTOSTREET2=".$sh_street2."&SHIPTOCITY=".$sh_city."&SHIPTOZIP=".$sh_zip."&SHIPTOSTATE=".$sh_state."&SHIPTOCOUNTRYCODE=".$sh_countrycode."&SHIPTOCOUNTRYNAME=".$sh_countryname."&PHONENUM=".$sh_phonenum;
      // String zusammenbauen
      $nvpstr="&AMT=".$paymentAmount.
              "&CURRENCYCODE=".$currencyCodeType.
              "&PAYMENTACTION=".$paymentType.
              "&NOTIFYURL=".$notify_url.
              "&INVNUM=".$inv_num.$adress.
              "&LOCALECODE=".$_SESSION['language_code'].
              "&RETURNURL=".$returnURL.
              "&CANCELURL=".$cancelURL.
              "&GIROPAYSUCCESSURL=".$gpsucssesURL.
              "&GIROPAYCANCELURL=".$gpcancelURL.
              "&BANKTXNPENDINGURL=".$bankpending.
              "&HDRIMG=".$this->Image.
              "&HDRBORDERCOLOR=".$this->BorderColor.
              "&HDRBACKCOLOR=".$this->BackColor.
              "&CUSTOM=".''.
              $address.
              "&ALLOWNOTE=0".
              "&ADDROVERRIDE=1";
      // Artikel Details mitgeben
      $nvpstr.=$nvp_products;
      // Make the call to PayPal to set the Express Checkout token
      // If the API call succeded, then redirect the buyer to PayPal
      // to begin to authorize payment.  If an error occured, show the
      // resulting errors
      $resArray=$this->hash_call("SetExpressCheckout",$nvpstr);
      $_SESSION['reshash']= $resArray;
      $ack = strtoupper($resArray["ACK"]);
      if($ack=="SUCCESS"){
        $token = urldecode($resArray["TOKEN"]);
        $this->payPalURL = $this->EXPRESS_URL.''.$token;
        return $this->payPalURL;
      } else  {
        $this->build_error_message($_SESSION['reshash']);
        if(PAYPAL_ERROR_DEBUG=='true') {
          $_SESSION['reshash']['FORMATED_ERRORS'] = PAYPAL_NOT_AVIABLE;
        } else {
          $this->payPalURL = $this->EXPRESS_CANCEL_URL;
        }
        return $this->payPalURL;
      }
    }
  /*************************************************************/
  /******* für beide Versionen *********************************/
  /*************************************************************/
    function complete_ceckout($insert_id, $data=''){
      // aufruf aus paypal.php oder paypalexpress.php aus Warenkorb
      // 2. Call um die PayPal Aktion abzuschliessen
      // Daten aus der Order
      // Stand: 05.12.2011
      global $xtPrice,$order;
      $order = new order($insert_id);
      // IP Adresse
      if($_SERVER['HTTP_X_FORWARDED_FOR']) {
        $customers_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
      } else {
        $customers_ip = $_SERVER['REMOTE_ADDR'];
      }
      // Amt
      $paymentAmount = round($order->info['pp_total'], $xtPrice->get_decimal_places($order->info['currency']));
      // Summen der Order
      $order_tax = round($order->info['pp_tax'], $xtPrice->get_decimal_places($order->info['currency']));
      $order_discount = round($order->info['pp_disc'], $xtPrice->get_decimal_places($order->info['currency']));
      $order_gs = round($order->info['pp_gs'], $xtPrice->get_decimal_places($order->info['currency']));
      $order_fee = round($order->info['pp_fee'], $xtPrice->get_decimal_places($order->info['currency']));
      $order_shipping = round($order->info['pp_shipping'], $xtPrice->get_decimal_places($order->info['currency']));
      $nvp_products=$this->paypal_get_products($paymentAmount,$order_tax,$order_discount,$order_fee,$order_shipping,$order_gs);
      $paymentAmount = urlencode(number_format($paymentAmount, $xtPrice->get_decimal_places($order->info['currency']), '.', ','));
      $currencyCodeType = urlencode($order->info['currency']);
      $tkn=(($data['token']!='')?$data['token']:$_SESSION['nvpReqArray']['TOKEN']);
      $payer=(($data['PayerID']!='')?$data['PayerID']:$_SESSION['reshash']['PAYERID']);
      $token =urlencode($tkn);
      $payerID = urlencode($payer);
      $paymentType='Sale';
      $notify_url  = urlencode($this->NOTIFY_URL);
      $inv_num = urlencode(PAYPAL_INVOICE.$insert_id);
      $button_source = urlencode($this->ppAPIec);
      // Versandadresse
      $sh_name = urlencode($this->mn_iconv($_SESSION['language_charset'], "UTF-8", $order->delivery['firstname'].' '.$order->delivery['lastname']));
      $sh_street = urlencode($this->mn_iconv($_SESSION['language_charset'], "UTF-8", $order->delivery['street_address']));
      $sh_street_2 = '';
      $sh_city = urlencode($this->mn_iconv($_SESSION['language_charset'], "UTF-8", $order->delivery['city']));
      $sh_state = urlencode($this->state_code($order->delivery['state']));
      if(is_array($order->delivery['country'])) {
        $sh_countrycode = urlencode($order->delivery['country']['iso_code_2']);
        $sh_countryname = urlencode($this->mn_iconv($_SESSION['language_charset'], "UTF-8",$order->delivery['country']['title']));
      } else {
        $sh_countrycode = urlencode($order->delivery['country_iso_2']);
        $sh_countryname = urlencode($this->mn_iconv($_SESSION['language_charset'], "UTF-8",$order->delivery['country']));
      }
      $sh_phonenum = urlencode($order->customer['telephone']);
      $sh_zip = urlencode($order->delivery['postcode']);
      $address = "&SHIPTONAME=".$sh_name."&SHIPTOSTREET=".$sh_street."&SHIPTOSTREET2=".$sh_street2."&SHIPTOCITY=".$sh_city."&SHIPTOZIP=".$sh_zip."&SHIPTOSTATE=".$sh_state."&SHIPTOCOUNTRYCODE=".$sh_countrycode."&SHIPTOCOUNTRYNAME=".$sh_countryname."&PHONENUM=".$sh_phonenum;
      // Versand Ende
      $nvpstr='&TOKEN='.$token.
              '&PAYERID='.$payerID.
              '&PAYMENTACTION='.$paymentType.
              '&AMT='.$paymentAmount.
              '&CURRENCYCODE='.$currencyCodeType.
              '&IPADDRESS='.$customers_ip.
              '&NOTIFYURL='.$notify_url.
              '&INVNUM='.$inv_num.$adress.
              '&BUTTONSOURCE='.$button_source.
              $address;
      // Artikel Details mitgeben
      $nvpstr.=$nvp_products;
      // Make the call to PayPal to finalize payment
      // If an error occured, show the resulting errors
      $resArray=$this->hash_call("DoExpressCheckoutPayment",$nvpstr);
      $_SESSION['reshash'] = array_merge($_SESSION['reshash'], $resArray) ;
      $ack = strtoupper($resArray["ACK"]);
      if($ack!="SUCCESS" && $ack!="SUCCESSWITHWARNING"){
        $this->build_error_message($_SESSION['reshash'],'DoEx');
      }
    }
  /*************************************************************/
  /******* funktionen nur für Warenkorb ************************/
  /*************************************************************/
    function paypal_get_customer_data(){
      // Stand: 09.01.2011
      $nvpstr="&TOKEN=".$_SESSION['reshash']['TOKEN'];
      // Make the API call and store the results in an array.  If the
      // call was a success, show the authorization details, and provide
      // an action to complete the payment.  If failed, show the error
      $resArray=$this->hash_call("GetExpressCheckoutDetails",$nvpstr);
      $_SESSION['reshash'] = array_merge($_SESSION['reshash'], $resArray) ;
      $ack = strtoupper($resArray["ACK"]);
      if($ack=="SUCCESS"){
        $_SESSION['paypal_express_checkout'] = true;
        $_SESSION['paypal_express_payment_modules'] = 'paypalexpress.php';
        if(!$this->check_customer()) {
          $_SESSION['reshash']['FORMATED_ERRORS'] = PAYPAL_ADRESSE.$_SESSION['reshash']['SHIPTOCOUNTRYCODE'];
            xtc_redirect($this->EXPRESS_CANCEL_URL);
        }
      } else  {
        $this->build_error_message($_SESSION['reshash']);
        $this->payPalURL = $this->EXPRESS_CANCEL_URL;
        return $this->payPalURL;
      }
    }
  /*************************************************************/
    function check_customer(){
      // Stand: 09.01.2011
      if($_SESSION['reshash']['SHIPTOCOUNTRYCODE']) {
        $country_query = xtc_db_query("select * from ".TABLE_COUNTRIES." where countries_iso_code_2 = '".xtc_db_input($_SESSION['reshash']['SHIPTOCOUNTRYCODE'])."' ");
        $tmp_country = xtc_db_fetch_array($country_query);
        if($tmp_country['status']!=1) {
          return false;
        }
      }
      if(!isset($_SESSION['customer_id'])) {
        $check_customer_query = xtc_db_query("select * from ".TABLE_CUSTOMERS." where customers_email_address = '".xtc_db_input($_SESSION['reshash']['EMAIL'])."' and account_type = '0'");
        if(!xtc_db_num_rows($check_customer_query)) {
          $this->create_account();
        }else{
          $check_customer = xtc_db_fetch_array($check_customer_query);
          $this->login_customer($check_customer);
          if(PAYPAL_EXPRESS_ADDRESS_OVERRIDE == 'true' && (isset($_SESSION['pp_allow_address_change']) && $_SESSION['pp_allow_address_change']!='true')) { //Dokuman - 2012-05-31 - fix paypal_checkout notices
            $this->create_shipping_address();
          }
        }
      }else{
        if(PAYPAL_EXPRESS_ADDRESS_OVERRIDE == 'true' && (isset($_SESSION['pp_allow_address_change']) && $_SESSION['pp_allow_address_change']!='true')) { //Dokuman - 2012-05-31 - fix paypal_checkout notices
          $check_customer_query = xtc_db_query("select * from ".TABLE_CUSTOMERS." where customers_id = '".xtc_db_input($_SESSION['customer_id'])."' and account_type = '0'");
          $check_customer = xtc_db_fetch_array($check_customer_query);
          $this->create_shipping_address();
        }
      }
      return True;
    }
  /*************************************************************/
    function create_account(){
      // Stand: 16.05.2010
      global $xtPrice;
      $firstname = xtc_db_prepare_input($this->UTF8decode($_SESSION['reshash']['FIRSTNAME']));
      $lastname = xtc_db_prepare_input($this->UTF8decode($_SESSION['reshash']['LASTNAME']));
      $email_address = xtc_db_prepare_input($_SESSION['reshash']['EMAIL']);
      $company = xtc_db_prepare_input($this->UTF8decode($_SESSION['reshash']['BUSINESS']));
      $street_address = xtc_db_prepare_input($this->UTF8decode($_SESSION['reshash']['SHIPTOSTREET'] . $_SESSION['reshash']['SHIPTOSTREET_2']));
      $postcode = xtc_db_prepare_input($_SESSION['reshash']['SHIPTOZIP']);
      $city = xtc_db_prepare_input($this->UTF8decode($_SESSION['reshash']['SHIPTOCITY']));
      $state = xtc_db_prepare_input($_SESSION['reshash']['SHIPTOSTATE']);
      $telephone = xtc_db_prepare_input($_SESSION['reshash']['PHONENUM']);
      $country_query = xtc_db_query("select * from ".TABLE_COUNTRIES." where countries_iso_code_2 = '".xtc_db_input($_SESSION['reshash']['SHIPTOCOUNTRYCODE'])."' ");
      $tmp_country = xtc_db_fetch_array($country_query);
      $country = xtc_db_prepare_input($tmp_country['countries_id']);
      $customers_status = DEFAULT_CUSTOMERS_STATUS_ID;
      $sql_data_array = array(
                        'customers_status' => $customers_status,
                        'customers_firstname' => $firstname,
                        'customers_lastname' => $lastname,
                        'customers_email_address' => $email_address,
                        'customers_telephone' => $telephone,
                        'customers_date_added' => 'now()',
                        'customers_last_modified' => 'now()');
      xtc_db_perform(TABLE_CUSTOMERS, $sql_data_array);
      $_SESSION['paypal_express_new_customer'] = 'true';
      $_SESSION['customer_id'] = xtc_db_insert_id();
      $user_id = xtc_db_insert_id();
      xtc_write_user_info($user_id);
      $sql_data_array = array(
                        'customers_id' => $_SESSION['customer_id'],
                        'entry_firstname' => $firstname,
                        'entry_lastname' => $lastname,
                        'entry_street_address' => $street_address,
                        'entry_postcode' => $postcode,
                        'entry_city' => $city,
                        'entry_country_id' => $country,
                        'entry_company' => $company,
                        'entry_zone_id' => '0',
                        'entry_state' => $state,
                        'address_date_added' => 'now()',
                        'address_last_modified' => 'now()');
      xtc_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);
      $address_id = xtc_db_insert_id();
      $_SESSION['sendto'] = $address_id;
      xtc_db_query("update " . TABLE_CUSTOMERS . " set customers_default_address_id = '" . $address_id . "' where customers_id = '" . (int) $_SESSION['customer_id'] . "'");
      xtc_db_query("insert into " . TABLE_CUSTOMERS_INFO . " (customers_info_id, customers_info_number_of_logons, customers_info_date_account_created) values ('" . (int) $_SESSION['customer_id'] . "', '0', now())");
      if(isset($_SESSION['tracking']['refID'])) {
        // Test ob die refferers_id im Kunden noch die falsche ist (sollte varchar(32) sein)
        $rows = xtc_db_query("SHOW COLUMNS FROM ".TABLE_CUSTOMERS);
        $feld_ist_original=0;
        while ($row=xtc_db_fetch_array($rows)) {
          if($row['Field']=='refferers_id') {
            if(substr($row['Type'],0,3)=='int') {
              $feld_ist_original=1;
            }
          }
        }
        if($feld_ist_original==1) {
          $campaign_check_query_raw = "SELECT *
                                      FROM " . TABLE_CAMPAIGNS . "
                                      WHERE campaigns_refID = '" . $_SESSION['tracking']['refID'] . "'";
          $campaign_check_query = xtc_db_query($campaign_check_query_raw);
          if(xtc_db_num_rows($campaign_check_query) > 0) {
            $campaign = xtc_db_fetch_array($campaign_check_query);
            $refID = $campaign['campaigns_id'];
          } else {
            $refID = 0;
          }
          xtc_db_query("update " . TABLE_CUSTOMERS . " set
                        refferers_id = '" . $refID . "'
                        where customers_id = '" . (int) $_SESSION['customer_id'] . "'");
          $leads = $campaign['campaigns_leads'] + 1;
          xtc_db_query("update " . TABLE_CAMPAIGNS . " set
                        campaigns_leads = '" . $leads . "'
                        where campaigns_id = '" . $refID . "'");
        } else {
          xtc_db_query("UPDATE " . TABLE_CUSTOMERS . " 
                           SET refferers_id = '".$_SESSION['tracking']['refID']."'
                         WHERE customers_id = '".(int) $_SESSION['customer_id']."'");
        }
      }
      if(ACTIVATE_GIFT_SYSTEM == 'true') {
        // GV Code Start
        // ICW - CREDIT CLASS CODE BLOCK ADDED  ******************************************************* BEGIN
        if(NEW_SIGNUP_GIFT_VOUCHER_AMOUNT > 0) {
          $coupon_code = create_coupon_code();
          $insert_query = xtc_db_query("insert into " . TABLE_COUPONS . " (coupon_code, coupon_type, coupon_amount, date_created) values ('" . $coupon_code . "', 'G', '" . NEW_SIGNUP_GIFT_VOUCHER_AMOUNT . "', now())");
          $insert_id = xtc_db_insert_id($insert_query);
          $insert_query = xtc_db_query("insert into " . TABLE_COUPON_EMAIL_TRACK . " (coupon_id, customer_id_sent, sent_firstname, emailed_to, date_sent) values ('" . $insert_id . "', '0', 'Admin', '" . $email_address . "', now() )");
          $_SESSION['reshash']['SEND_GIFT'] = 'true';
          $_SESSION['reshash']['GIFT_AMMOUNT'] = $xtPrice->xtcFormat(NEW_SIGNUP_GIFT_VOUCHER_AMOUNT, true);
          $_SESSION['reshash']['GIFT_CODE'] = $coupon_code;
          $_SESSION['reshash']['GIFT_LINK'] = xtc_href_link(FILENAME_GV_REDEEM, 'gv_no=' . $coupon_code, 'NONSSL', false);
        }
        if(NEW_SIGNUP_DISCOUNT_COUPON != '') {
          $coupon_code = NEW_SIGNUP_DISCOUNT_COUPON;
          $coupon_query = xtc_db_query("select * from " . TABLE_COUPONS . " where coupon_code = '" . $coupon_code . "'");
          $coupon = xtc_db_fetch_array($coupon_query);
          $coupon_id = $coupon['coupon_id'];
          $coupon_desc_query = xtc_db_query("select * from " . TABLE_COUPONS_DESCRIPTION . " where coupon_id = '" . $coupon_id . "' and language_id = '" . (int) $_SESSION['language_id'] . "'");
          $coupon_desc = xtc_db_fetch_array($coupon_desc_query);
          $insert_query = xtc_db_query("insert into " . TABLE_COUPON_EMAIL_TRACK . " (coupon_id, customer_id_sent, sent_firstname, emailed_to, date_sent) values ('" . $coupon_id . "', '0', 'Admin', '" . $email_address . "', now() )");
          $_SESSION['reshash']['SEND_COUPON'] = 'true';
          $_SESSION['reshash']['COUPON_DESC'] = $coupon_desc['coupon_description'];
          $_SESSION['reshash']['COUPON_CODE'] = $coupon['coupon_code'];
        }
        // ICW - CREDIT CLASS CODE BLOCK ADDED  ******************************************************* END
        // GV Code End       // create templates
      }
      $_SESSION['ACCOUNT_PASSWORD'] = 'true';
      // Login Customer
      $check_customer_query = xtc_db_query("select * from ".TABLE_CUSTOMERS." where customers_email_address = '".xtc_db_input($email_address)."' and account_type = '0'");
      $check_customer = xtc_db_fetch_array($check_customer_query);
      $this->login_customer($check_customer);
      if(PAYPAL_EXPRESS_ADDRESS_OVERRIDE == 'true') {
        if($firstname.' '.$lastname != $this->UTF8decode($_SESSION['reshash']['SHIPTONAME']))
          $this->create_shipping_address();
      }
    }
  /*************************************************************/
    function login_customer($check_customer){
      // Stand: 29.04.2009
      global $main,$xtPrice,$econda;
      if(SESSION_RECREATE == 'True') {
        xtc_session_recreate();
      }
      $check_country_query = xtc_db_query("select entry_country_id, entry_zone_id from ".TABLE_ADDRESS_BOOK." where customers_id = '".(int) $check_customer['customers_id']."' and address_book_id = '".$check_customer['customers_default_address_id']."'");
      $check_country = xtc_db_fetch_array($check_country_query);
      $_SESSION['customer_gender'] = $check_customer['customers_gender'];
      $_SESSION['customer_first_name'] = $check_customer['customers_firstname'];
      $_SESSION['customer_last_name'] = $check_customer['customers_lastname'];
      $_SESSION['customer_id'] = $check_customer['customers_id'];
      $_SESSION['customer_vat_id'] = $check_customer['customers_vat_id'];
      $_SESSION['customer_default_address_id'] = $check_customer['customers_default_address_id'];
      $_SESSION['customer_country_id'] = $check_country['entry_country_id'];
      $_SESSION['customer_zone_id'] = $check_country['entry_zone_id'];
      $_SESSION['customer_email_address'] = $check_customer['customers_email_address'];
      $date_now = date('Ymd');
      xtc_db_query("update ".TABLE_CUSTOMERS_INFO." SET customers_info_date_of_last_logon = now(), customers_info_number_of_logons = customers_info_number_of_logons+1 WHERE customers_info_id = '".(int) $_SESSION['customer_id']."'");
      xtc_write_user_info((int) $_SESSION['customer_id']);
      // Falls vorher schon mal eingeloggt und was in der Cart war
      xtc_db_query("delete from ".TABLE_CUSTOMERS_BASKET." where customers_id = '".(int)$_SESSION['customer_id']."'");
      xtc_db_query("delete from ".TABLE_CUSTOMERS_BASKET_ATTRIBUTES." where customers_id = '".(int)$_SESSION['customer_id']."'");
      // Warenkorb restoren
      $_SESSION['cart']->restore_contents();
      if(is_object($econda)) {
        $econda->_loginUser();
      }
      // write customers status in session
      require(DIR_WS_INCLUDES.'write_customers_status.php');
      $xtPrice = new xtcPrice($_SESSION['currency'], $_SESSION['customers_status']['customers_status_id']);
    }
  /*************************************************************/
    function create_shipping_address(){
      // Stand: 28.04.2011
      if(!$_SESSION['reshash']['SHIPTOCITY']) {
        return;
      }
      $pos = strrpos($_SESSION['reshash']['SHIPTONAME'], ' ');
      $lenght = strlen($_SESSION['reshash']['SHIPTONAME']);
      $firstname = $this->UTF8decode(substr($_SESSION['reshash']['SHIPTONAME'], 0, $pos));
      $lastname = $this->UTF8decode(substr($_SESSION['reshash']['SHIPTONAME'], ($pos+1), $lenght));
      $email_address = xtc_db_prepare_input($_SESSION['reshash']['EMAIL']);
      $company = xtc_db_prepare_input($_SESSION['reshash']['BUSINESS']);
      $street_address = xtc_db_prepare_input($this->UTF8decode($_SESSION['reshash']['SHIPTOSTREET'] . $_SESSION['reshash']['SHIPTOSTREET_2']));
      $postcode = xtc_db_prepare_input($_SESSION['reshash']['SHIPTOZIP']);
      $city = xtc_db_prepare_input($this->UTF8decode($_SESSION['reshash']['SHIPTOCITY']));
      $state = xtc_db_prepare_input($_SESSION['reshash']['SHIPTOSTATE']);
      $telephone = xtc_db_prepare_input($_SESSION['reshash']['PHONENUM']);
      $country_query = xtc_db_query("select * from ".TABLE_COUNTRIES." where countries_iso_code_2 = '".xtc_db_input($_SESSION['reshash']['SHIPTOCOUNTRYCODE'])."' ");
      $tmp_country = xtc_db_fetch_array($country_query);
      $country = xtc_db_prepare_input($tmp_country['countries_id']);
      $sql_data_array = array(
                        'customers_id' => $_SESSION['customer_id'],
                        'entry_firstname' => $firstname,
                        'entry_lastname' => $lastname,
                        'entry_street_address' => $street_address,
                        'entry_postcode' => $postcode,
                        'entry_city' => $city,
                        'entry_country_id' => $country,
                        'entry_company' => $company,
                        'entry_zone_id' => '0',
                        'entry_state' => $state,
                        'address_date_added' => 'now()',
                        'address_last_modified' => 'now()',
                        'address_class' => 'paypal');
      $check_address_query = xtc_db_query("select address_book_id from ".TABLE_ADDRESS_BOOK." where customers_id = '".(int) $_SESSION['customer_id']."' and address_class = 'paypal'");
      $check_address = xtc_db_fetch_array($check_address_query);
      if($check_address['address_book_id']!='') {
        xtc_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'update', "address_book_id = '".(int) $check_address['address_book_id']."' and customers_id ='".(int) $_SESSION['customer_id']."'");
        $send_to = $check_address['address_book_id'];
      } else {
        xtc_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);
        $send_to = xtc_db_insert_id();
      }
      $_SESSION['sendto'] = $send_to;
    }
  /*************************************************************/
  /******* funktionen für beide versionen **********************/
  /*************************************************************/
    //  hash_call: Function to perform the API call to PayPal using API signature
    //  @methodName is name of API  method.
    //  @nvpStr is nvp string.
    //  returns an associtive array containing the response from the server.
    //  08.01.2009.ergänzt für PHP ohne cURL von Stefan Kl.
    //  05.01.2010 Verbose auf 0 da bei einigen Hostern sonst zuviel angezeigt wird
    function hash_call($methodName,$nvpStr,$pp_token=''){
      // Stand: 05.01.2010
      if(function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$this->API_Endpoint.$pp_token);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        //turning off the server and peer verification(TrustManager Concept).
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POST, 1);
        //if USE_PROXY constant set to TRUE am Anfang dieser Datei, then only proxy will be enabled.
        //Set proxy name to PROXY_HOST and port number to PROXY_PORT im Anfang dieser Datei
        if($this->USE_PROXY) {
          curl_setopt($ch, CURLOPT_PROXY, PROXY_HOST.":".PROXY_PORT);
        }
        //NVPRequest for submitting to server
        $nvpreq="METHOD=".urlencode($methodName)."&VERSION=".urlencode($this->version)."&PWD=".urlencode($this->API_Password)."&USER=".urlencode($this->API_UserName)."&SIGNATURE=".urlencode($this->API_Signature).$nvpStr;
        //setting the nvpreq as POST FIELD to curl
        curl_setopt($ch,CURLOPT_POSTFIELDS,$nvpreq);
        //getting response from server
        $response = curl_exec($ch);
        //converting NVPResponse to an Associative Array
        $nvpResArray=$this->deformatNVP($response);
        $nvpReqArray=$this->deformatNVP($nvpreq);
        $_SESSION['nvpReqArray']= $nvpReqArray;
        /* Mit cURL Fehleranzeige und nicht Versuch mit file_get_contents
        if(curl_errno($ch)) {
          // moving to display page to display curl errors
          $_SESSION['curl_error_no']=curl_errno($ch) ;
          $_SESSION['curl_error_msg']=curl_error($ch);
          $this->build_error_message($_SESSION['reshash']);
        }
        */
        $curl_fehler=curl_errno($ch);
        //closing the curl
        curl_close($ch);
        //return $nvpResArray;
        if(!$curl_fehler) {
          return $nvpResArray;
        }
      }
      /// Falls cURL nicht da oder Fehlerhaft
      global $API_Endpoint,$version,$API_UserName,$API_Password,$API_Signature,$nvp_Header;
      $nvpreq="METHOD=".urlencode($methodName)."&VERSION=".urlencode($this->version)."&PWD=".urlencode($this->API_Password)."&USER=".urlencode($this->API_UserName)."&SIGNATURE=".urlencode($this->API_Signature).$nvpStr;
      $request_post = array(
                      'http'=>array(
                      'method'=>'POST',
                      'header'=>"Content-type: application/x-www-form-urlencoded\r\n",
                      'content'=>$nvpreq));
      $request = stream_context_create($request_post);
      $response= file_get_contents($this->API_Endpoint.$pp_token, false, $request);
      $nvpResArray=$this->deformatNVP($response);
      $nvpReqArray=$this->deformatNVP($nvpreq);
      $_SESSION['nvpReqArray']= $nvpReqArray;
      return $nvpResArray;
    }
  /*************************************************************/
    //  This function will take NVPString and convert it to an Associative Array and it will decode the response.
    //  It is usefull to search for a particular key and displaying arrays.
    //  @nvpstr is NVPString.
    //  @nvpArray is Associative Array.
    function deformatNVP($nvpstr){
      // Stand: 29.04.2009
      $intial=0;
      $nvpArray = array();
      while(strlen($nvpstr)){
        //postion of Key
        $keypos= strpos($nvpstr,'=');
        //position of value
        $valuepos = strpos($nvpstr,'&') ? strpos($nvpstr,'&'): strlen($nvpstr);
        // getting the Key and Value values and storing in a Associative Array
        $keyval=substr($nvpstr,$intial,$keypos);
        $valval=substr($nvpstr,$keypos+1,$valuepos-$keypos-1);
        //decoding the respose
        $nvpArray[urldecode($keyval)] =urldecode( $valval);
        $nvpstr=substr($nvpstr,$valuepos+1,strlen($nvpstr));
      }
      return $nvpArray;
    }
  /*************************************************************/
    function build_error_message($resArray='',$Aufruf=''){
      // Stand: 29.04.2009
      global $messageStack;
      if(isset($_SESSION['curl_error_no'])) {
        $errorCode= $_SESSION['curl_error_no'] ;
        $errorMessage=$_SESSION['curl_error_msg'] ;
        $error .=  'Error Number: '.  $errorCode . '<br />';
        $error .=  'Error Message: '.  $errorMessage . '<br />';
      } else {
        $error .=  'Ack: '.  $resArray['ACK'] . '<br />';
        $error .=  'Correlation ID: '.  $resArray['CORRELATIONID']  . '<br />';
        $error .=  'Version:'.  $resArray['VERSION'] . '<br />';
        $count=0;
        $redirect=0;
        while(isset($resArray["L_SHORTMESSAGE".$count])) {
          $errorCode    = $resArray["L_ERRORCODE".$count];
          $shortMessage = $resArray["L_SHORTMESSAGE".$count];
          $longMessage  = $resArray["L_LONGMESSAGE".$count];
          if($Aufruf=='DoEx' && ($errorCode=='10422' || $errorCode=='10417'))
            $redirect=1;
          $count=$count+1;
          $error .=  'Error Number:'.  $errorCode . '<br />';
          $error .=  'Error Short Message: '.   $shortMessage . '<br />';
          $error .=  'Error Long Message: '.  $longMessage . '<br />';
        }//end while
        if($redirect==1) {
          $_SESSION['reshash']['REDIRECTREQUIRED']="TRUE";
        }
      }// end else
      $_SESSION['reshash']['FORMATED_ERRORS'] = $error;
    }
  /*************************************************************/
    function paypal_get_products($paymentAmount,$order_tax,$order_discount,$order_fee,$order_shipping,$order_gs,$express_call=False){
      // für beide PayPal Versionen
      // Artikel Details mitgeben incl. Attribute
      // Für den Express Call Vermerk für den Versand + Vorläufige Kosten mitgeben
      // Stand: 19.10.2010
      global $xtPrice,$order;
      require_once(DIR_FS_INC . 'xtc_get_attributes_model.inc.php');
      $products_sum_amt = 0;
      $tmp_products='';
      for($i = 0, $n = sizeof($order->products); $i < $n; $i ++) {
        $products_price = round($order->products[$i]['price'],$xtPrice->get_decimal_places($order->info['currency']));
        $products_sum_amt+=$products_price*$order->products[$i]['qty'];
        $attributes_data = '';
        $attributes_model = '';
        if ((isset ($order->products[$i]['attributes'])) && (sizeof($order->products[$i]['attributes']) > 0)) {
          for ($j = 0, $n2 = sizeof($order->products[$i]['attributes']); $j < $n2; $j++) {
            $attributes_data .= ' - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'];
            $attributes_model .= '-'.xtc_get_attributes_model($order->products[$i]['id'], $order->products[$i]['attributes'][$j]['value'],$order->products[$i]['attributes'][$j]['option']);
        }
        }
        $tmp_products .='&L_NAME'.$i.'='.urlencode($this->mn_iconv($_SESSION['language_charset'], "UTF-8",substr($order->products[$i]['name'].$attributes_data,0,127))).
                      '&L_NUMBER'.$i.'='.urlencode($this->mn_iconv($_SESSION['language_charset'], "UTF-8",substr($order->products[$i]['model'].$attributes_model,0,127))).
                        '&L_QTY'.$i.'='.urlencode($order->products[$i]['qty']).
                        '&L_AMT'.$i.'='.urlencode(number_format($products_price, $xtPrice->get_decimal_places($order->info['currency']), '.', ','));
      }
      if($order_discount!=0) {
        // ist ein - Betrag !
        $products_sum_amt+=$order_discount;
        $tmp_products .='&L_NAME'.$i.'='.urlencode($this->mn_iconv($_SESSION['language_charset'], "UTF-8",substr(SUB_TITLE_OT_DISCOUNT,0,127))).
                        '&L_NUMBER'.$i.'='.
                        '&L_QTY'.$i.'=1'.
                        '&L_AMT'.$i.'='.urlencode(number_format($order_discount, $xtPrice->get_decimal_places($order->info['currency']), '.', ','));
        $i++;
      }
      if($order_gs!=0) {
        // ist ein - Betrag !
        $products_sum_amt+=$order_gs;
        $tmp_products .='&L_NAME'.$i.'='.urlencode($this->mn_iconv($_SESSION['language_charset'], "UTF-8",substr(PAYPAL_GS,0,127))).
                        '&L_NUMBER'.$i.'='.
                        '&L_QTY'.$i.'=1'.
                        '&L_AMT'.$i.'='.urlencode(number_format($order_gs, $xtPrice->get_decimal_places($order->info['currency']), '.', ','));
        $i++;
      }
      if($order_fee!=0) {
        $products_sum_amt+=$order_fee;
        $tmp_products .='&L_NAME'.$i.'='.urlencode($this->mn_iconv($_SESSION['language_charset'], "UTF-8","Handling")).
                        '&L_NUMBER'.$i.'='.
                        '&L_QTY'.$i.'=1'.
                        '&L_AMT'.$i.'='.urlencode(number_format($order_fee, $xtPrice->get_decimal_places($order->info['currency']), '.', ','));
        $i++;
      }
      if($order_shipping!=0) {
        $products_sum_amt+=$order_shipping;
        $tmp_products .='&L_NAME'.$i.'='.urlencode($this->mn_iconv($_SESSION['language_charset'], "UTF-8",substr(SHIPPING_COSTS,0,127))).
                        '&L_NUMBER'.$i.'='.
                        '&L_QTY'.$i.'=1'.
                        '&L_AMT'.$i.'='.urlencode(number_format($order_shipping, $xtPrice->get_decimal_places($order->info['currency']), '.', ','));
        $i++;
      }
      $products_sum_amt = round($products_sum_amt,$xtPrice->get_decimal_places($order->info['currency']));
      if($order_tax!=0 && trim($paymentAmount-$products_sum_amt)>=$order_tax) {
        $products_sum_amt+=$order_tax;
        $tmp_products .='&L_NAME'.$i.'='.urlencode($this->mn_iconv($_SESSION['language_charset'], "UTF-8",substr(PAYPAL_TAX,0,127))).
                        '&L_NUMBER'.$i.'='.
                        '&L_QTY'.$i.'=1'.
                        '&L_AMT'.$i.'='.urlencode(number_format($order_tax, $xtPrice->get_decimal_places($order->info['currency']), '.', ','));
        $i++;
      }
      if($express_call && PAYPAL_EXP_WARN!='') {
        $tmp_products .='&L_NAME'.$i.'='.urlencode($this->mn_iconv($_SESSION['language_charset'], "UTF-8",substr(html_entity_decode(PAYPAL_EXP_WARN),0,127))).
                        '&L_NUMBER'.$i.'='.
                        '&L_QTY'.$i.'=0'.
                        '&L_AMT'.$i.'=0';
        $i++;
      }
      if($express_call && PAYPAL_EXP_VORL!='' && PAYPAL_EXP_VERS!=0) {
        $products_sum_amt+=PAYPAL_EXP_VERS;
        $tmp_products .='&L_NAME'.$i.'='.urlencode($this->mn_iconv($_SESSION['language_charset'], "UTF-8",substr(html_entity_decode(PAYPAL_EXP_VORL),0,127))).
                        '&L_NUMBER'.$i.'='.
                        '&L_QTY'.$i.'=1'.
                        '&L_AMT'.$i.'='.urlencode(number_format(PAYPAL_EXP_VERS, $xtPrice->get_decimal_places($order->info['currency']), '.', ','));
        $i++;
      }
      $products_sum_amt = round($products_sum_amt,$xtPrice->get_decimal_places($order->info['currency']));
      if(trim($paymentAmount)!=trim($products_sum_amt)) {
        $order_diff = round($paymentAmount-$products_sum_amt ,$xtPrice->get_decimal_places($order->info['currency']));
        $products_sum_amt+=$order_diff;
        $tmp_products .='&L_NAME'.$i.'='.urlencode($this->mn_iconv($_SESSION['language_charset'], "UTF-8","Differenz")).
                        '&L_NUMBER'.$i.'='.
                        '&L_QTY'.$i.'=1'.
                        '&L_AMT'.$i.'='.urlencode(number_format($order_diff, $xtPrice->get_decimal_places($order->info['currency']), '.', ','));
      }
      $tmp_products.="&ITEMAMT=".urlencode(number_format($products_sum_amt, $xtPrice->get_decimal_places($order->info['currency']), '.', ','));
      // Artikel Details Ende
      return($tmp_products);
    }
  /*************************************************************/
    function write_status_history($o_id) {
      // Stand: 29.04.2009
      if(empty($o_id)) {
        return false;
      }
      $ack = strtoupper($_SESSION['reshash']["ACK"]);
      if($ack=="SUCCESS"  || $ack=="SUCCESSWITHWARNING") {
        $o_status = PAYPAL_ORDER_STATUS_PENDING_ID;
      } else {
        $o_status = PAYPAL_ORDER_STATUS_REJECTED_ID;
      }
      // Sieht der Kunde auch ...
      if(!($ack=="SUCCESS" || $ack=="SUCCESSWITHWARNING") ) {
        $crlf = "\n";
        while(list($key, $value) = each($_SESSION['reshash'])) {
          $comment .= $key.'='.$value.$crlf;
        }
      }
      $order_history_data = array('orders_id' => $o_id,
                                  'orders_status_id' => $o_status,
                                  'date_added' => 'now()',
                                  'customer_notified' => '0',
                                  'comments' => $comment);
      xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY,$order_history_data);
      xtc_db_query("UPDATE " . TABLE_ORDERS . " SET orders_status = '" . $o_status . "', last_modified = now() WHERE orders_id = '" . xtc_db_prepare_input($o_id) . "'");
      return true;
    }
  /*************************************************************/
    function logging_status($o_id) {
      // Stand: 29.04.2009
      $data = array_merge($_SESSION['nvpReqArray'],$_SESSION['reshash']);
      if(!$data['TRANSACTIONID'] || $data['TRANSACTIONID']=='') {
        $data['TRANSACTIONID']='PayPal Fehler!<br>'.date("d.m.Y - H:i:s");
      }
      $data_array = array('xtc_order_id' => $o_id,
                          'txn_type' => $data['TRANSACTIONTYPE'],
                          'reason_code' => $data['REASONCODE'],
                          'payment_type' => $data['PAYMENTTYPE'],
                          'payment_status' => $data['PAYMENTSTATUS'],
                          'pending_reason' => $data['PENDINGREASON'],
                          'invoice' => $data['INVNUM'],
                          'mc_currency' => $data['CURRENCYCODE'],
                          'first_name' => $_SESSION['customer_first_name'],
                          'last_name' => $_SESSION['customer_last_name'],
                          'payer_business_name' => $this->UTF8decode($data['BUSINESS']),
                          'address_name' => $this->UTF8decode($data['SHIPTONAME']),
                          'address_street' => $this->UTF8decode($data['SHIPTOSTREET']),
                          'address_city' => $this->UTF8decode($data['SHIPTOCITY']),
                          'address_state' => $this->UTF8decode($data['SHIPTOSTATE']),
                          'address_zip' => $data['SHIPTOZIP'],
                          'address_country' => $this->UTF8decode($data['SHIPTOCOUNTRYNAME']),
                          'address_status' => $data['ADDRESSSTATUS'],
                          'payer_email' => $data['EMAIL'],
                          'payer_id' => $data['PAYERID'],
                          'payer_status' => $data['PAYERSTATUS'],
                          'payment_date' => $data['TIMESTAMP'],
                          'business' => '',
                          'receiver_email' => '',
                          'receiver_id' => '',
                          'txn_id' => $data['TRANSACTIONID'],
                          'parent_txn_id' => '',
                          'num_cart_items' => '',
                          'mc_gross' => $data['AMT'],
                          'mc_fee' => $data['FEEAMT'],
                          'mc_authorization' => $data['AMT'],
                          'payment_gross' => '',
                          'payment_fee' => '',
                          'settle_amount' => $data['SETTLEAMT'],
                          'settle_currency' => '',
                          'exchange_rate' => $data['EXCHANGERATE'],
                          'notify_version' => $data['VERSION'],
                          'verify_sign' => '',
                          'last_modified' => '',
                          'date_added' => 'now()',
                          'memo' => $data['DESC']);
      xtc_db_perform(TABLE_PAYPAL,$data_array);
      return true;
    }
  /*************************************************************/
    function giropay_confirm($data='') {
      // Giropay transaction
      // Stand: 29.04.2009
      $tkn = (($data['token']!='') ? $data['token'] : $_SESSION['nvpReqArray']['TOKEN']);
      unset($_SESSION['payment']);
      unset($_SESSION['nvpReqArray']);
      unset($_SESSION['reshash']);
      xtc_redirect($this->GIROPAY_URL.''.urlencode($tkn));
    }
    // end Giropay */
  /*************************************************************/
    function callback_process($data,$charset) {
      // Keine Session da !
      // Stand: 29.06.2011
      global $_GET;
      $this->data = $data;
      //$this->_logTrans($data);
      require_once(DIR_WS_CLASSES . 'class.phpmailer.php');
      if(EMAIL_TRANSPORT == 'smtp') {
        require_once(DIR_WS_CLASSES . 'class.smtp.php');
      }
      require_once(DIR_FS_INC . 'xtc_Security.inc.php');
      $xtc_order_id=(int)substr($this->data['invoice'],strlen(PAYPAL_INVOICE));
      if(isset($xtc_order_id) && is_numeric($xtc_order_id) && ($xtc_order_id > 0)) {
        // order suchen
        $order_query = xtc_db_query("SELECT currency, currency_value
                                    FROM " . TABLE_ORDERS . "
                                    WHERE orders_id = '" . xtc_db_prepare_input($xtc_order_id) . "'");
        if(xtc_db_num_rows($order_query) > 0) {
          // order gefunden
          $ipn_charset=xtc_db_prepare_input($this->data['charset']);
          $ipn_data = array();
          $ipn_data['reason_code'] = xtc_db_prepare_input($this->data['reason_code']);
          $ipn_data['xtc_order_id'] = xtc_db_prepare_input($xtc_order_id);
          $ipn_data['payment_type'] = xtc_db_prepare_input($this->data['payment_type']);
          $ipn_data['payment_status'] = xtc_db_prepare_input($this->data['payment_status']);
          $ipn_data['pending_reason'] = xtc_db_prepare_input($this->data['pending_reason']);
          $ipn_data['invoice'] = xtc_db_prepare_input($this->data['invoice']);
          $ipn_data['mc_currency'] = xtc_db_prepare_input($this->data['mc_currency']);
          $ipn_data['first_name'] = xtc_db_prepare_input($this->IPNdecode($this->data['first_name'],$ipn_charset,$charset));
          $ipn_data['last_name'] = xtc_db_prepare_input($this->IPNdecode($this->data['last_name'],$ipn_charset,$charset));
          $ipn_data['address_name'] = xtc_db_prepare_input($this->IPNdecode($this->data['address_name'],$ipn_charset,$charset));
          $ipn_data['address_street'] = xtc_db_prepare_input($this->IPNdecode($this->data['address_street'],$ipn_charset,$charset));
          $ipn_data['address_city'] = xtc_db_prepare_input($this->IPNdecode($this->data['address_city'],$ipn_charset,$charset));
          $ipn_data['address_state'] = xtc_db_prepare_input($this->IPNdecode($this->data['address_state'],$ipn_charset,$charset));
          $ipn_data['address_zip'] = xtc_db_prepare_input($this->data['address_zip']);
          $ipn_data['address_country'] = xtc_db_prepare_input($this->IPNdecode($this->data['address_country'],$ipn_charset,$charset));
          $ipn_data['address_status'] = xtc_db_prepare_input($this->data['address_status']);
          $ipn_data['payer_email'] = xtc_db_prepare_input($this->data['payer_email']);
          $ipn_data['payer_id'] = xtc_db_prepare_input($this->data['payer_id']);
          $ipn_data['payer_status'] = xtc_db_prepare_input($this->data['payer_status']);
          $ipn_data['payment_date'] = xtc_db_prepare_input($this->datetime_to_sql_format($this->data['payment_date']));
          $ipn_data['business'] = xtc_db_prepare_input($this->IPNdecode($this->data['business'],$ipn_charset,$charset));
          $ipn_data['receiver_email'] = xtc_db_prepare_input($this->data['receiver_email']);
          $ipn_data['receiver_id'] = xtc_db_prepare_input($this->data['receiver_id']);
          $ipn_data['txn_id'] = xtc_db_prepare_input($this->data['txn_id']);
          $ipn_data['txn_type']= $this->ipn_determine_txn_type($this->data['txn_type']);
          $ipn_data['parent_txn_id'] = xtc_db_prepare_input($this->data['parent_txn_id']);
          $ipn_data['mc_gross'] = xtc_db_prepare_input($this->data['mc_gross']);
          $ipn_data['mc_fee'] = xtc_db_prepare_input($this->data['mc_fee']);
          $ipn_data['mc_shipping'] = xtc_db_prepare_input($this->data['mc_shipping']);
          $ipn_data['payment_gross'] = xtc_db_prepare_input($this->data['payment_gross']);
          $ipn_data['payment_fee'] = xtc_db_prepare_input($this->data['payment_fee']);
          $ipn_data['notify_version'] = xtc_db_prepare_input($this->data['notify_version']);
          $ipn_data['verify_sign'] = xtc_db_prepare_input($this->data['verify_sign']);
          $ipn_data['num_cart_items'] = xtc_db_prepare_input($this->data['num_cart_items']);
          if($ipn_data['num_cart_items']>1) {
            $verspos=$ipn_data['num_cart_items'];
            for($p=1;$p<=$verspos;$p++) {
              if($this->data['item_name'.$p] == substr(SUB_TITLE_OT_DISCOUNT,0,127) || $this->data['item_name'.$p] == substr(PAYPAL_GS,0,127) || $this->data['item_name'.$p] == "Handling" || $this->data['item_name'.$p] == substr(PAYPAL_TAX,0,127) || $this->data['item_name'.$p] == "Differenz" ) {
                // Artikel Nummer aus den Details für Sonderzeilen
                $ipn_data['num_cart_items']--;
              }
              if($this->data['item_name'.$p] == substr(SHIPPING_COSTS,0,127)) {
                // Versandkosten
                $ipn_data['mc_shipping']=$this->data['mc_gross_'.$p];
                $ipn_data['num_cart_items']--;
              }
            }
          }
          $_transQuery = "SELECT paypal_ipn_id FROM ".TABLE_PAYPAL." WHERE txn_id = '".$ipn_data['txn_id']."'";
          $_transQuery = xtc_db_query($_transQuery);
          $_transQuery = xtc_db_fetch_array($_transQuery);
          if($_transQuery['paypal_ipn_id']!='') {
            $insert_id = $_transQuery['paypal_ipn_id'];
            $sql_data_array = array('payment_status' => $ipn_data['payment_status'],
                          'pending_reason' => $ipn_data['pending_reason'],
                          'payer_email' => $ipn_data['payer_email'],
                          'num_cart_items' => $ipn_data['num_cart_items'],
                          'mc_fee' => $ipn_data['mc_fee'],
                          'mc_shipping' => $ipn_data['mc_shipping'],
                          'address_name' => $ipn_data['address_name'],
                          'address_street' => $ipn_data['address_street'],
                          'address_city' => $ipn_data['address_city'],
                          'address_state' => $ipn_data['address_state'],
                          'address_zip' => $ipn_data['address_zip'],
                          'address_country' => $ipn_data['address_country'],
                          'address_status' => $ipn_data['address_status'],
                          'payer_status' => $ipn_data['payer_status'],
                          'receiver_email' => $ipn_data['receiver_email'],
                          'last_modified ' => 'now()');
            xtc_db_perform(TABLE_PAYPAL, $sql_data_array, 'update', "paypal_ipn_id = '".(int) $insert_id."'");
          } else {
            $ipn_data['date_added']='now()';
            $ipn_data['last_modified']='now()';
            xtc_db_perform(TABLE_PAYPAL,$ipn_data);
            $insert_id = xtc_db_insert_id();
          }
          $paypal_order_history = array('paypal_ipn_id' => $insert_id,
                                        'txn_id' => $ipn_data['txn_id'],
                                        'parent_txn_id' => $ipn_data['parent_txn_id'],
                                        'payment_status' => $ipn_data['payment_status'],
                                        'pending_reason' => $ipn_data['pending_reason'],
                                        'mc_amount' => $ipn_data['mc_gross'],
                                        'date_added' => 'now()');
          xtc_db_perform(TABLE_PAYPAL_STATUS_HISTORY,$paypal_order_history);
          $crlf = "\n";
          $comment_status = xtc_db_prepare_input($this->data['payment_status']) . ' ' . xtc_db_prepare_input($this->data['mc_gross']) . xtc_db_prepare_input($this->data['mc_currency']) . $crlf;
          $comment_status .= ' ' . xtc_db_prepare_input($this->data['first_name']) . ' ' . xtc_db_prepare_input($this->data['last_name']) . ' ' . xtc_db_prepare_input($this->data['payer_email']);
          if(isset($this->data['payer_status'])) {
            $comment_status .= ' is ' . xtc_db_prepare_input($this->data['payer_status']);
          }
          $comment_status .= '.' . $crlf;
          if(isset($this->data['test_ipn']) && is_numeric($this->data['test_ipn']) && ($_POST['test_ipn'] > 0)) {
            $comment_status .='(Sandbox-Test Mode)'.$crlf;
          }
          $comment_status .= 'Total=' . xtc_db_prepare_input($this->data['mc_gross']) . xtc_db_prepare_input($this->data['mc_currency']);
          if(isset($this->data['pending_reason'])) {
            $comment_status .= $crlf . ' Pending Reason=' . xtc_db_prepare_input($this->data['pending_reason']);
          }
          if(isset($this->data['reason_code'])) {
            $comment_status .= $crlf . ' Reason Code=' . xtc_db_prepare_input($this->data['reason_code']);
          }
          $comment_status .= $crlf . ' Payment=' . xtc_db_prepare_input($this->data['payment_type']);
          $comment_status .= $crlf . ' Date=' . xtc_db_prepare_input($this->data['payment_date']);
          if(isset($this->data['parent_txn_id'])) {
            $comment_status .= $crlf . ' ParentID=' . xtc_db_prepare_input($this->data['parent_txn_id']);
          }
          $comment_status .= $crlf . ' ID=' . xtc_db_prepare_input($_POST['txn_id']);
          //Set status for default (Pending)
          $order_status_id = PAYPAL_ORDER_STATUS_PENDING_ID;
          $parameters = 'cmd=_notify-validate';
          foreach($this->data as $key => $value) {
            $parameters .= '&' . $key . '=' . urlencode(stripslashes($value));
          }
          //$this->_logTransactions($parameters);
          // 08.01.2008 auch ohne cURL
          $mit_curl=0;
          if(function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->IPN_URL);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $result = curl_exec($ch);
            if(!curl_errno($ch)) {
              $mit_curl=1;
            }
            curl_close($ch);
          }
          // cURL fehlt oder ist fehlgeschlagen
          if($mit_curl==0) {
            $request_post = array(
                            'http'=>array(
                            'method'=>'POST',
                            'header'=>"Content-type: application/x-www-form-urlencoded\r\n",
                            'content'=>$parameters));
            $request = stream_context_create($request_post);
            $result= file_get_contents($this->IPN_URL, false, $request);
          }
          if(strtoupper($result) == 'VERIFIED' || $result == '1') {
            // Steht auf Warten
            if(strtolower($this->data['payment_status']) == 'completed') {
              if(PAYPAL_ORDER_STATUS_SUCCESS_ID > 0) {
                $order_status_id = PAYPAL_ORDER_STATUS_SUCCESS_ID;
              }
            //Set status for Denied, Failed
            } elseif((strtolower($this->data['payment_status']) == 'denied') OR (strtolower($this->data['payment_status']) == 'failed')) {
              $order_status_id = PAYPAL_ORDER_STATUS_REJECTED_ID;
            //Set status for Reversed
            } elseif(strtolower($this->data['payment_status']) == 'reversed') {
              $order_status_id = PAYPAL_ORDER_STATUS_PENDING_ID;
            //Set status for Canceled-Reversal
            } elseif(strtolower($this->data['payment_status']) == 'canceled-reversal') {
              $order_status_id = PAYPAL_ORDER_STATUS_SUCCESS_ID;
            //Set status for Refunded
            } elseif(strtolower($this->data['payment_status']) == 'refunded') {
              $order_status_id = DEFAULT_ORDERS_STATUS_ID;
            //Set status for Pendign - eigentlich nicht nötig?
            } elseif(strtolower($this->data['payment_status']) == 'pending') {
              $order_status_id = PAYPAL_ORDER_STATUS_PENDING_ID;
            //Set status for Processed - wann kommt das ?
            } elseif(strtolower($this->data['payment_status']) == 'processed') {
              if(PAYPAL_ORDER_STATUS_SUCCESS_ID > 0) {
                $order_status_id = PAYPAL_ORDER_STATUS_SUCCESS_ID;
              }
            }
          } else {
            $order_status_id = PAYPAL_ORDER_STATUS_REJECTED_ID;
            $error_reason = 'Received INVALID responce but invoice and Customer matched.';
          }
          $xtc_order_id=(int)substr($this->data['invoice'],strlen(PAYPAL_INVOICE));
          xtc_db_query("UPDATE " . TABLE_ORDERS . "
                        SET orders_status = '" . $order_status_id . "', last_modified = now()
                        WHERE orders_id = '" . xtc_db_prepare_input($xtc_order_id) . "'");
          $sql_data_array = array('orders_id' => xtc_db_prepare_input($xtc_order_id),
                                  'orders_status_id' => $order_status_id,
                                  'date_added' => 'now()',
                                  'customer_notified' => '0',
                                  'comments' => 'PayPal IPN ' . $comment_status . '');
          xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        } else {
          $error_reason = 'IPN-Fehler: Keine Order Nr.=' . xtc_db_prepare_input($this->data['invoice']) . ' mit Kunden=' . (int) $this->data['custom'] . ' gefunden.';
        }
      } else {
        $error_reason = 'IPN-Fehler: Keine Order gefunden zu den empfangenen Daten.';
      }
      if(xtc_not_null(EMAIL_SUPPORT_ADDRESS) && strlen($error_reason)) {
        $email_body = $error_reason . "\n\n".'<br>';
        $email_body .= $_SERVER['REQUEST_METHOD'] . " - " . $_SERVER['REMOTE_ADDR'] . " - " . $_SERVER['HTTP_REFERER'] . " - " . $_SERVER['HTTP_ACCEPT'] . "\n\n".'<br>';
        $email_body .= '$_POST:' . "\n\n".'<br>';
        foreach($this->data as $key => $value) {
          $email_body .= $key . '=' . $value . "\n".'<br>';
        }
        $email_body .= "\n" . '$_GET:' . "\n\n".'<br>';
        foreach($_GET as $key => $value) {
          $email_body .= $key . '=' . $value . "\n".'<br>';
        }
        xtc_php_mail(EMAIL_BILLING_ADDRESS, EMAIL_BILLING_NAME, EMAIL_SUPPORT_ADDRESS, EMAIL_SUPPORT_ADDRESS, '', EMAIL_BILLING_ADDRESS, EMAIL_BILLING_NAME, false, false, 'PayPal IPN Invalid Process', $email_body, $email_body);
      }
    }
  /*************************************************************/
    function datetime_to_sql_format($paypalDateTime) {
      //Copyright (c) 2004 DevosC.com
      $months = array('Jan' => '01','Feb' => '02','Mar' => '03','Apr' => '04','May' => '05','Jun' => '06','Jul' => '07','Aug' => '08','Sep' => '09','Oct' => '10','Nov' => '11','Dec' => '12');
      $hour = substr($paypalDateTime, 0, 2);
      $minute = substr($paypalDateTime, 3, 2);
      $second = substr($paypalDateTime, 6, 2);
      $month = $months[substr($paypalDateTime, 9, 3)];
      $day = (strlen($day = preg_replace("/,/", '', substr($paypalDateTime, 13, 2))) < 2) ? '0' . $day : $day;
      $year = substr($paypalDateTime, -8, 4);
      if(strlen($day) < 2) {
        $day = '0' . $day;
      }
      return($year . "-" . $month . "-" . $day . " " . $hour . ":" . $minute . ":" . $second);
    }
  /*************************************************************/
    function buildAPIKey($key){
      // Stand: 29.04.2009
      $key_arr=explode(',',$key);
      $k='';
      for($i=0; $i<count($key_arr);$i++) {
        $k.=chr($key_arr[$i]);
      }
      return $k;
    }
  /*************************************************************/
    function ipn_determine_txn_type($txn_type = 'unknown') {
      // Stand: 29.04.2009
      if(substr($txn_type,0,8) == 'cleared-') {
        return $txn_type;
      }
      if($txn_type == 'send_money') {
        return $txn_type;
      }
      if($txn_type == 'express_checkout' || $txn_type == 'cart') {
        $txn_type = $txn_type;
      }
      // if it's not unique or linked to a parent, then:
      // 1. could be an e-check denied / cleared
      // 2. could be an express-checkout "pending" transaction which has been Accepted in the merchant's PayPal console and needs activation in Zen Cart
      if($this->data['payment_status']=='Completed' && $txn_type=='express_checkout' && $this->data['payment_type']=='echeck') {
        $txn_type = 'express-checkout-cleared';
        return $txn_type;
      }
      if($this->data['payment_status']=='Completed' && $this->data['payment_type']=='echeck') {
        $txn_type = 'echeck-cleared';
        return $txn_type;
      }
      if(($this->data['payment_status']=='Denied' || $this->data['payment_status']=='Failed') && $this->data['payment_type']=='echeck') {
        $txn_type = 'echeck-denied';
        return $txn_type;
      }
      if($this->data['payment_status']=='Denied') {
        $txn_type = 'denied';
        return $txn_type;
      }
      if(($this->data['payment_status']=='Pending') && $this->data['pending_reason']=='echeck') {
        $txn_type = 'pending-echeck';
        return $txn_type;
      }
      if(($this->data['payment_status']=='Pending') && $this->data['pending_reason']=='address') {
        $txn_type = 'pending-address';
        return $txn_type;
      }
      if(($this->data['payment_status']=='Pending') && $this->data['pending_reason']=='intl') {
        $txn_type = 'pending-intl';
        return $txn_type;
      }
      if(($this->data['payment_status']=='Pending') && $this->data['pending_reason']=='multi-currency') {
        $txn_type = 'pending-multicurrency';
        return $txn_type;
      }
      if(($this->data['payment_status']=='Pending') && $this->data['pending_reason']=='verify') {
        $txn_type = 'pending-verify';
        return $txn_type;
      }
      return $txn_type;
    }
  /*************************************************************/
    function IPNdecode($string,$ipncharset='windows-1252',$charset){
      // Keine Session da!
      // Stand: 29.04.2009
      if($ipncharset!=$charset) {
        $string=$this->mn_iconv($ipncharset, $charset, $string);
      }
      return $string;
    }
  /*************************************************************/
    function UTF8decode($string){
      // Session vorhanden
      // Stand: 29.04.2009
      if($this->detectUTF8($string)) {
        $string=$this->mn_iconv('UTF-8', $_SESSION['language_charset'], $string);
      }
      return($string);
    }
  /*************************************************************/
    function detectUTF8($string){
      // Stand: 29.04.2009
      return preg_match('%(?:
          [\xC2-\xDF][\x80-\xBF]
          |\xE0[\xA0-\xBF][\x80-\xBF]
          |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}
          |\xED[\x80-\x9F][\x80-\xBF]
          |\xF0[\x90-\xBF][\x80-\xBF]{2}
          |[\xF1-\xF3][\x80-\xBF]{3}
          |\xF4[\x80-\x8F][\x80-\xBF]{2}
          )+%xs', $string);
    }
  /*************************************************************/
    function state_code($string){
      // Stand: 29.04.2009
      $zone_query = xtc_db_query("select zone_code from " . TABLE_ZONES . " where zone_name = '" . $string . "'");
      if(xtc_db_num_rows($zone_query)) {
        $zone = xtc_db_fetch_array($zone_query);
        return $zone['zone_code'];
      } else {
        return $string;
      }
    }
  /*************************************************************/
    function mn_iconv($t1,$t2,$string){
      // Stand: 29.04.2009
      if(function_exists('iconv')) {
        return iconv($t1, $t2, $string);
      }
      /// Kein iconv im PHP
      if($t2 == "UTF-8") {
        // nur als Ersatz für das iconv und nur in eine richtung 1251 to UTF8
        //ISO 8859-1 to UTF-8
        if(function_exists('utf8_encode')) {
          return utf8_encode($string);
        } else {
          $string=preg_replace("/([\x80-\xFF])/e","chr(0xC0|ord('\\1')>>6).chr(0x80|ord('\\1')&0x3F)",$string);
          return($string);
        }
      } elseif($t1 == "UTF-8") {
        //UTF-8 to ISO 8859-1
        if(function_exists('utf8_decode')) {
          return utf8_decode($string);
        } else {
          $string=preg_replace("/([\xC2\xC3])([\x80-\xBF])/e","chr(ord('\\1')<<6&0xC0|ord('\\2')&0x3F)",$string);
          return($string);
        }
      } else {
        // keine Konvertierung möglich
        return($string);
      }
    }
  }
}
?>
