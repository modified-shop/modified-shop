<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(Coding Standards); www.oscommerce.com 
   (c) 2006 XT-Commerce (afterbuy.php 1287 2005-10-07)

   changes (xtsell.de):
   2012-07-07 changed $ShopInterface to 'api.afterbuy.de', changed $ShopInterface_path to '/afterbuy/ShopInterface.aspx'
   2012-07-08 added billing_suburb, delivery suburb to query and pass to Kstrasse2, KLstrasse2
   2012-07-08 added Kundenerkennung
   2012-07-08 added test for billing address equals delivery address and set Lieferanschrift=0 if equal
   2012-07-08 added Artikelerkennung, Bestandart, set ArtikelStammID_x to products_model
   2012-07-10 added SetPay=1 for ZahlartFID=5
   2012-07-10 added handling for ot_payment
   2012-07-10 use $error for error mail
   2012-07-11 extract AID and UID, store in orders_ident_key
   2012-07-11 added CheckVID=1
   2012-07-14 set ZahlartFID=1 for eustandardtransfer
   2012-07-15 set VMemo= and MarkierungID= for SetPay

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

class xtc_afterbuy_functions {
  var $order_id;
  var $PartnerID = AFTERBUY_PARTNERID;
  var $PartnerPass = AFTERBUY_PARTNERPASS;
  var $UserID = AFTERBUY_USERID;
  var $order_status = AFTERBUY_ORDERSTATUS;
  var $ShopInterface = 'api.afterbuy.de';
  var $ShopInterface_path = '/afterbuy/ShopInterface.aspx';
  var $user_agent = 'Mozilla/5.0 (compatible, cURL, PHP5) - Afterbuy API';

  /** ---------------------------------------------------------------------------------
   *
   * xtc_afterbuy_functions::xtc_afterbuy_functions
   *
   * @param int $order_id
   */
  function xtc_afterbuy_functions($order_id) {
     require_once (DIR_FS_INC.'xtc_product_link.inc.php');
     $this->order_id = $order_id;
     $this->template_path_afterbuy = DIR_FS_CATALOG . 'templates/' . CURRENT_TEMPLATE . '/afterbuy';
     $this->afterbuy_crt = $this->template_path_afterbuy . '/curl-auth.pem';
     if(AFTERBUY_DEALERS) {
       $this->dealers = explode("," , AFTERBUY_DEALERS);
     }
     if(AFTERBUY_IGNORE_GROUPE) {
       $this->ignore = explode("," , AFTERBUY_IGNORE_GROUPE);
     }
  }
  
  /** ---------------------------------------------------------------------------------
   *
   * xtc_afterbuy_functions::process_order
   *
   */
  function process_order() {
    if ( @!array_key_exists($_SESSION['customers_status']['customers_status_id'], $this->ignore) ) {
      $nr = 0;
      $anzahl = 0;
      // get order-data
      $o_query = xtc_db_query("SELECT   customers_id,
                                 billing_gender,
                                 billing_company,
                                 billing_firstname,
                                 billing_lastname,
                                 billing_street_address,
                                 billing_suburb,
                                 billing_postcode,
                                 billing_city,
                                 customers_telephone AS billing_telephone,
                                 customers_email_address,
                                 billing_country_iso_code_2,
                                 delivery_gender,
                                 delivery_company,
                                 delivery_firstname,
                                 delivery_lastname,
                                 delivery_street_address,
                                 delivery_suburb,
                                 delivery_postcode,
                                 delivery_city,
                                 delivery_country_iso_code_2,
                                 payment_method,
                                 shipping_method,
                                 orders_status,
                                 comments
                           FROM ".TABLE_ORDERS."
                           WHERE orders_id='".$this->order_id."'
                           LIMIT 1;");
      $oData = xtc_db_fetch_array($o_query);


      $afterbuy = array();
      // Start Auth
      $afterbuy[] = "Action=new";
      $afterbuy[] = "PartnerID=".$this->iconv($this->PartnerID);
      $afterbuy[] = "PartnerPass=".$this->iconv($this->PartnerPass);
      $afterbuy[] = "UserID=".$this->iconv($this->UserID);
      
      // Kundenerkennung (0=Kbenutzername, 1=Kemail, 2=EKundenNr)
      $afterbuy[] = "Kundenerkennung=1";
      
      // User IDs
      $afterbuy[] = "Kbenutzername=".$this->iconv($oData['customers_id'])."_XTC-ORDER_".$this->iconv($this->order_id);
      $afterbuy[] = "VID=".$this->iconv($this->order_id);
      $afterbuy[] = "CheckVID=1";
      
      // billing Address
      $afterbuy[] = "KVorname=".$this->iconv($oData['billing_firstname']);
      $afterbuy[] = "KNachname=".$this->iconv($oData['billing_lastname']);
      $afterbuy[] = "KStrasse=".$this->iconv($oData['billing_street_address']);
      $afterbuy[] = "KStrasse2=".$this->iconv($oData['billing_suburb']);
      $afterbuy[] = "KPLZ=".$this->iconv($oData['billing_postcode']);
      $afterbuy[] = "KOrt=".$this->iconv($oData['billing_city']);
      $afterbuy[] = "Kemail=".$this->iconv($oData['customers_email_address']);
      $afterbuy[] = "KLand=".$this->iconv($oData['billing_country_iso_code_2']);
      if($oData['billing_company']) {
        $afterbuy[] = "KFirma=".$this->iconv($oData['billing_company']);
      }
      if($oData['billing_gender']) {
        switch($oData['billing_gender']) {
        case 'm':
          $oData['billing_gender'] = 'Herr';
          break;
        default:
          $oData['billing_gender'] = 'Frau';
        }
        $afterbuy[] = "Kanrede=".$this->iconv($oData['billing_gender']);
      }
      if($oData['billing_telephone']) {
        $afterbuy[] = "Ktelefon=".$this->iconv($oData['billing_telephone']);
      }
      
      // check for billing_address equals delivery_address
      if( ($oData['billing_company'] == $oData['delivery_company']) &&
          ($oData['billing_firstname'] == $oData['delivery_firstname']) &&
          ($oData['billing_lastname'] == $oData['delivery_lastname']) &&
          ($oData['billing_street_address'] == $oData['delivery_street_address']) &&
          ($oData['billing_suburb'] == $oData['delivery_suburb']) &&
          ($oData['billing_postcode'] == $oData['delivery_postcode']) &&
          ($oData['billing_city'] == $oData['delivery_city']) &&
          ($oData['billing_country_iso_code_2'] == $oData['delivery_country_iso_code_2'])) {
        $afterbuy[] = "Lieferanschrift=0";
      } else {
        $afterbuy[] = "Lieferanschrift=1";
        // Delivery Address
        $afterbuy[] = "KLFirma=".$this->iconv($oData['delivery_company']);
        $afterbuy[] = "KLVorname=".$this->iconv($oData['delivery_firstname']);
        $afterbuy[] = "KLNachname=".$this->iconv($oData['delivery_lastname']);
        $afterbuy[] = "KLStrasse=".$this->iconv($oData['delivery_street_address']);
        $afterbuy[] = "KLStrasse2=".$this->iconv($oData['delivery_suburb']);
        $afterbuy[] = "KLPLZ=".$this->iconv($oData['delivery_postcode']);
        $afterbuy[] = "KLOrt=".$this->iconv($oData['delivery_city']);
        $afterbuy[] = "KLLand=".$this->iconv($oData['delivery_country_iso_code_2']);
      }
      
      // Artikelerkennung (0 = ProduktID, 1 = Artikelnummer, 2 = externe Artikelnummer, 13 = Hersteller EAN
      $afterbuy[] = "Artikelerkennung=0";
      
      // Bestandart (auktion/shop)
      $afterbuy[] = "Bestandart=shop";
      
      // products_data
      // get products related to order
      $p_query = xtc_db_query("SELECT products_model,
                                 products_name,
                                 products_id,
                                 products_tax,
                                 products_price,
                                 products_quantity
                           FROM ".TABLE_ORDERS_PRODUCTS."
                           WHERE orders_id='".$this->order_id."'");

      $p_count = xtc_db_num_rows($p_query);
      while ($pDATA = xtc_db_fetch_array($p_query)) {
        $nr ++;
        if ( empty($pDATA['products_model']) ) {
          $pDATA['products_model'] = $pDATA['products_id'];
        }
        $afterbuy[] = "Artikelnr_".$nr."=".$pDATA['products_model'];
        $afterbuy[] = "ArtikelStammID_".$nr."=".$pDATA['products_model'];
        $afterbuy[] = "Artikelname_".$nr."=".$this->iconv( $pDATA['products_name']);
        
        if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 &&
            $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
          $pDATA['products_price'] = $pDATA['products_price'] + $pDATA['products_tax'];
        }
        if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 &&
            $_SESSION['customers_status']['customers_status_add_tax_ot'] == 0) {
          $pDATA['products_tax'] = 0;
        }
        $price = $this->currency( $pDATA['products_price'] );
        $tax = $this->currency( $pDATA['products_tax']);
        $afterbuy[] = "ArtikelEPreis_".$this->iconv($nr)."=".$this->iconv($price);
        $afterbuy[] = "ArtikelMwst_".$this->iconv($nr)."=".$this->iconv($tax);
        $afterbuy[] = "ArtikelMenge_".$this->iconv($nr)."=".$this->iconv($pDATA['products_quantity']);
        $url = $this->url('product_info.php',xtc_product_link($pDATA['products_id'],$pDATA['products_name']));
        $afterbuy[] = "ArtikelLink_".$this->iconv($nr)."=".$this->iconv($url);
        
        $a_query = xtc_db_query("SELECT products_options_values,
                                    products_options
                              FROM ".TABLE_ORDERS_PRODUCTS_ATTRIBUTES."
                              WHERE orders_id='".$this->order_id."'
                                 AND orders_products_id='".$pDATA['orders_products_id']."'");
        $options = array();
        while ($aDATA = xtc_db_fetch_array($a_query)) {
          $options[] = $aDATA['products_options'].":".$aDATA['products_options_values'];
        }
        if ($options) {
          $options = implode('|',$options);
          $afterbuy[] = "Attribute_".$this->iconv($nr)."=".$this->iconv($options);
        }
      }
      
      $order_total_query = xtc_db_query("SELECT
                                      class,
                                      value,
                                      sort_order
                                      FROM ".TABLE_ORDERS_TOTAL."
                                      WHERE orders_id='".$this->order_id."'
                                      ORDER BY sort_order ASC");

      while ($order_total_values = xtc_db_fetch_array($order_total_query)) {
        
        $order_total[] = array ('CLASS' => $order_total_values['class'], 'VALUE' => $order_total_values['value']);
        // shippingcosts
        if ($order_total_values['class'] == 'ot_shipping') {
          $vK = $this->currency( $order_total_values['value'] );
          $afterbuy[] = "Versandkosten=".$this->iconv($vK);
        }
        // nachnamegebuer
        if ($order_total_values['class'] == 'ot_cod_fee') {
          $cod_fee = $this->currency( $order_total_values['value']);
          $afterbuy[] = "Zahlartenaufschlag=".$this->iconv($cod_fee);
        }
        // payment fee
        if ($order_total_values['class'] == 'ot_payment') {
          $pay_fee = $this->currency( $order_total_values['value']);
          $afterbuy[] = "Zahlartenaufschlag=".$this->iconv($pay_fee);
        }
        // rabatt
        if ($order_total_values['class'] == 'ot_discount') {
          $nr ++;
          $p_count ++;
          $afterbuy[] = "Artikelnr_".$this->iconv($nr)."=99999998";
          $afterbuy[] = "Artikelname_".$this->iconv($nr)."=Rabatt";
          $discount = $this->currency( $order_total_values['value'] );
          $afterbuy[] = "ArtikelEPreis_".$this->iconv($nr)."=".$this->iconv($discount);
          $afterbuy[] = "ArtikelMwst_".$this->iconv($nr)."=".$this->iconv($tax);
          $afterbuy[] = "ArtikelMenge_".$this->iconv($nr)."=1";
        }
        // Gutschein
        if ($order_total_values['class'] == 'ot_gv') {
          $nr ++;
          $afterbuy[] = "Artikelnr_".$this->iconv($nr)."=99999997";
          $afterbuy[] = "Artikelname_".$this->iconv($nr)."=Gutschein";
          $gv = $this->currency( ($order_total_values['value'] * (-1)));
          $afterbuy[] = "ArtikelEPreis_".$this->iconv($nr)."=".$this->iconv($gv);
          $afterbuy[] = "ArtikelMwst_".$this->iconv($nr)."=0";
          $afterbuy[] = "ArtikelMenge_".$this->iconv($nr)."=1";
          $p_count ++;
        }
        if ($order_total_values['class'] == 'ot_coupon') {
          $nr ++;
          $afterbuy[] = "Artikelnr_".$this->iconv($nr)."=99999996";
          $afterbuy[] = "Artikelname_".$this->iconv($nr)."=Kupon";
          $coupon = $this->currency( ($order_total_values['value'] * (-1)));
          $afterbuy[] = "ArtikelEPreis_".$this->iconv($nr)."=".$this->iconv($coupon);
          $afterbuy[] = "ArtikelMwst_".$this->iconv($nr)."=0";
          $afterbuy[] = "ArtikelMenge_".$this->iconv($nr)."=1";
          $p_count ++;
        }
      }

      $afterbuy[] = "PosAnz=".$this->iconv($p_count);

      if($oData['comments']) {
        $afterbuy[] = "kommentar=".$this->iconv($oData['comments']);
      }
      $s_method = explode( '(' , $oData['shipping_method'] );
      $s_method['0'] = trim($s_method['0']);
      $afterbuy[] = "Versandart=".$this->iconv($s_method['0']);
      $afterbuy[] = "Zahlart=".$this->iconv( $this->payment($oData['payment_method']) );
      $ZFID = $this->iconv( $this->payment_FID($oData['payment_method']) );
      $afterbuy[] = "ZFunktionsID=".$ZFID;
      if($ZFID == 5) {
        $afterbuy[] = "SetPay=1";
                $afterbuy[] = "VMemo=".$this->iconv("ACHTUNG: Vorgang wurde als bezahlt übergeben. Überprüfung erforderlich!");
        $afterbuy[] = "MarkierungID=324";
      }

      //banktransfer data
      if ($oData['payment_method']=='banktransfer') {
        $b_query = xtc_db_query("SELECT banktransfer_bankname,
                                    banktransfer_blz,
                                    banktransfer_number,
                                    banktransfer_owner
                              FROM ".TABLE_BANKTRANSFER."
                              WHERE orders_id='".$this->order_id."'
                              LIMIT 1;");
        if (xtc_db_numrows($b_query)) {
          $b_data = xtc_db_fetch_array($b_query);
          $afterbuy[] = "Bankname=".$this->iconv($b_data['banktransfer_bankname']);
          $afterbuy[] = "BLZ=".$this->iconv($b_data['banktransfer_blz']);
          $afterbuy[] = "Kontonummer=".$this->iconv($b_data['banktransfer_number']);
          $afterbuy[] = "Kontoinhaber=".$this->iconv($b_data['banktransfer_owner']);
        }
      }
      $afterbuy[] = $this->extra();
      $afterbuy[] = "NoVersandCalc=1";
      if(isset($this->dealers) && !empty($this->dealers) &&
         @array_key_exists($_SESSION['customers_status']['customers_status_id'], $this->dealers) ) {
        $afterbuy[] = 'Haendler=1';
      }

      if($xml = $this->submit($afterbuy)) {
        $result = simplexml_load_string ( $xml );

        if ($result->success == '1') {
          $cdr = $result->data->KundenNr;
          //build string of AID/UID/KundenNr
          $AB = $result->data->AID . $result->data->UID . $cdr;
          xtc_db_query("UPDATE ".TABLE_ORDERS." SET afterbuy_success='1',afterbuy_id='".$cdr."',orders_ident_key='".$AB."' WHERE orders_id='".$this->order_id."'");

          //set new order status
          if ($this->order_status != '') {
            xtc_db_query("UPDATE ".TABLE_ORDERS." SET orders_status='".$this->order_status."' WHERE orders_id='".$this->order_id."'");
          }

        } else {
          $error = array();
          foreach($result->errorlist->error as $row) {
            $error[] = $row;
          }
          // mail to shopowner
          $mail_content_html = 'Fehler bei Uebertragung der Bestellung: '.$this->order_id."<br />\r\n".'Folgende Fehlermeldung wurde vom afterbuy.de zurueckgegeben:'."<br />\r\n"."<br />\r\n".implode("<br />\r\n",$error);
          $mail_content_txt = 'Fehler bei Uebertragung der Bestellung: '.$this->order_id."\r\n".'Folgende Fehlermeldung wurde vom afterbuy.de zurueckgegeben:'."\r\n\r\n".implode("\r\n",$error);
          xtc_php_mail(EMAIL_BILLING_ADDRESS,STORE_NAME.'-Afterbuy',EMAIL_BILLING_ADDRESS, STORE_NAME,'',EMAIL_BILLING_ADDRESS, STORE_NAME,'','', "Afterbuy-Error", $mail_content_html,$mail_content_txt);

        }
      }
    }
  }

  /** ---------------------------------------------------------------------------------
   *
   * xtc_afterbuy_functions::order_send
   *
   * @return boolean
   */
  function order_send() {

    $check_query = xtc_db_query("SELECT afterbuy_success FROM ".TABLE_ORDERS." WHERE orders_id='".$this->order_id."' LIMIT 1;");
    $data = xtc_db_fetch_array($check_query);

    if ($data['afterbuy_success'] == 1) {
      return false;
    }
    return true;

  }

  /** ---------------------------------------------------------------------------------
   *
   * xtc_afterbuy_functions::iconv
   *
   * convert strings to iso-8859-1 for afterbuy and urlencoded
   *
   * @param string $str
   * @return string
   */
  private function iconv( $str ) {
    if(defined('DB_SERVER_CHARSET')) {
      if(function_exists('iconv')) {
        return urlencode( iconv( charset_mapper(DB_SERVER_CHARSET) , 'ISO-8859-1' , $str ) );
      } elseif( DB_SERVER_CHARSET == 'utf8' ) {
        return urlencode( utf8_decode( $str ) );
      } else {
        return urlencode( $str );
      }
    } else {
      return urlencode( $str );
    }
  }

  /** ---------------------------------------------------------------------------------
   *
   * xtc_afterbuy_functions::submit
   *
   * connector to Afterbuy API
   *
   * @param string $str
   * @return string
   */
  private function submit( $POST_ARRAY ) {
    $POST_ARRAY = implode('&',$POST_ARRAY);
    if(function_exists('curl_init')) {
      $ch = curl_init();
      @curl_setopt($ch, CURLOPT_URL, 'https://'.$this->ShopInterface.$this->ShopInterface_path);
      if(file_exists($this->afterbuy_crt) && is_readable($this->afterbuy_crt)) {
        @curl_setopt($ch, CURLOPT_CAFILE, $this->afterbuy_crt);
        @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
      }
      @curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
      @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      @curl_setopt($ch, CURLOPT_POST, 1);
      @curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      @curl_setopt($ch, CURLOPT_POSTFIELDS, $POST_ARRAY);
      $return = @curl_exec($ch);
      if(curl_errno($ch)) {
        $error = @curl_error($ch);
        $error = strip_tags($error);
        $s = array("\r","\n","\t");
        $r = array(""," ","   ");
        $error = str_replace($s,$r,$error);
        $return = '<?xml version="1.0" encoding="UTF-8" ?>'."\n<result>\n<success>0</success>\n<errorlist>\n<error>" . $error . "</error>\n</errorlist>\n</result>";
      }
      @curl_close($ch);
    } else {
      $return = '<?xml version="1.0" encoding="UTF-8" ?>'."\n<result>\n<success>0</success>\n<errorlist>\n<error>Die Funktionen von cURL konnten nicht gefunden werden.</error>\n<error>Um diesen API zu nutzen, wird PHP cURL benoetigt: http://www.php.net/curl</error>\n</errorlist>\n</result>";
    }
    return $return;
  }

  /** ---------------------------------------------------------------------------------
   *
   * xtc_afterbuy_functions::url
   *
   * create URLs like xtc_href_link
   *
   * @param string $page
   * @param string $params (optional)
   * @param string $connect (optioal [only NONSSL or SSL])
   * @return url address
   */
  private function url($page='index.php' , $params='' , $connect='NONSSL') {
    switch($connect) {
    case 'NONSSL':
      $connect = 'NONSSL';
      break;
    default:
      $connect = 'SSL';
    }
    if(!function_exists('xtc_catalog_href_link')) {
      return xtc_href_link($page , $params , $connect);
    } else {
      return xtc_catalog_href_link($page , $params , $connect);
    }
  }

  /** ---------------------------------------------------------------------------------
   *
   * xtc_afterbuy_functions::payment
   *
   * english to german
   *
   * @param string $payment
   * @return string
   */
  private function payment($payment) {
    switch($payment) {
    case 'banktransfer':
      $payment = 'Lastschrift';
      break;
    case 'cash':
      $payment = 'Barzahlung';
      break;
    case 'cc':
      $payment = 'Kreditkarte';
      break;
    case 'cod':
      $payment = 'Nachnahme';
      break;
    case 'eustandardtransfer':
      $payment = 'Ãœberweisung/Vorkasse';
      break;
    case 'iclear':
      $payment = 'iClear Payment System';
      break;
    case 'invoice':
      $payment = 'Rechnung';
      break;
    case 'ipayment':
      $payment = 'iPayment';
      break;
    case 'ipaymentelv':
      $payment = 'iPayment Lastschriftverfahren';
      break;
    case 'ogone':
      $payment = 'Ogone - Payment Service Provider';
      break;
    case 'moneyorder':
      $payment = 'Ãœberweisung/Vorkasse';
      break;
    case 'paypal':
      $payment = 'PayPal';
      break;
    case 'paypalexpress':
      $payment = 'PayPal Express';
      break;
    case 'sofortueberweisung':
    case 'sofortueberweisung_direct':
    case 'sofortueberweisungredirect':
    case 'sofortueberweisungvorkasse':
      $payment = 'SofortÃ¼berweisung';
      break;
    case 'worldpay':
      $payment = 'Secure Credit Card Payment';
      break;
    }
    return $payment;
  }

  /** ---------------------------------------------------------------------------------
   *
   * xtc_afterbuy_functions::payment_FID
   *
   * payment names to intengers by afterbuy
   *
   * @param string $payment
   * @return int
   */
  private function payment_FID($payment) {
    switch($payment) {
    case 'banktransfer':
      $payment = '7';
      break;
    case 'cash':
      $payment = '2';
      break;
    case 'cod':
      $payment = '4';
      break;
    case 'invoice':
      $payment = '6';
      break;
    case 'eustandardtransfer':
    case 'moneyorder':
      $payment = '1';
      break;
    case 'paypal':
      $payment = '5';
      break;
    case 'paypalexpress':
      $payment = '5';
      break;
    case 'sofortueberweisung':
      $payment = '12';
      break;
    case 'sofortueberweisungredirect':
      $payment = '12';
      break;
    case 'sofortueberweisungvorkasse':
      $payment = '12';
      break;
    default:
      $payment = '99';
    }
    return $payment;
  }

  /** ---------------------------------------------------------------------------------
   *
   * xtc_afterbuy_functions::currency
   *
   * US dez. numbers to DE dez. numbers
   *
   * @param flood $flood
   * @return flood
   */
  private function currency( $flood ) {
    return str_replace('.', ',', $flood );
  }

  function extra() {

  }

}
?>