<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(Coding Standards); www.oscommerce.com 
   (c) 2006 XT-Commerce (afterbuy.php 1287 2005-10-07)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

class xtc_afterbuy_functions {
  var $order_id;
  var $payment_id;
  var $payment_name;
  var $customer_country_id = -1;
  var $customer_zone_id = -1;

  // constructor
  function __construct($order_id, $from='public') {
    $this->order_id = (int)$order_id;
    if ($from == 'admin') {
      $o_query = xtc_db_query("SELECT `delivery_country_iso_code_2`,`delivery_state` FROM " . TABLE_ORDERS . " WHERE orders_id = " . $this->order_id);
      $o_array = xtc_db_fetch_array($o_query);
      if (!empty($o_array['delivery_country_iso_code_2'])) {
        $c_query = xtc_db_query("SELECT `countries_id` FROM " . TABLE_COUNTRIES . " WHERE countries_iso_code_2 = '" . $o_array['delivery_country_iso_code_2'] ."'");
        $c_array = xtc_db_fetch_array($c_query);
        if (!empty($c_array)) {
          $this->customer_country_id = $c_array['countries_id'];
        }
      }
      if (!empty($o_array['delivery_state'])) {
        $this->customer_zone_id = 0;
        $z_query = xtc_db_query("SELECT `zone_id` FROM " . TABLE_ZONES . " WHERE zone_country_id = '" . $this->customer_country_id ."' AND `zone_name`='".$o_array['delivery_state']."'");
        if (!empty($z_query)) {
          $this->customer_zone_id = $c_array['zone_id'];
        } else {
          $this->customer_zone_id = 0;
        }
      } else {
        $this->customer_zone_id = 0;
      }
    }
  }

  function process_order() {
    global $xtPrice;

    $dealer_groups = defined('AFTERBUY_DEALERS') && AFTERBUY_DEALERS != '' ? explode(",", AFTERBUY_DEALERS) : '';
    $ignore_groups = defined('AFTERBUY_IGNORE_GROUPE') && AFTERBUY_IGNORE_GROUPE != '' ? explode(",", AFTERBUY_IGNORE_GROUPE) : '';

    $testmode = false; // Auf true setzen, wenn keine Übertragung zu Afterbuy erfolgen soll und die Daten nur per Mail gesendet werden sollen zu Entwicklungszwecken

    // ############ SETTINGS ################

    // PartnerID
    $PartnerID = AFTERBUY_PARTNERID;

    // your PASSWORD for your PartnerID
    $PartnerPass = AFTERBUY_PARTNERPASS;

    // Your Afterbuy USERNAME
    $UserID = AFTERBUY_USERID;

    // new Orderstatus ID of processed order
    $order_status = AFTERBUY_ORDERSTATUS;

    //$Artikelerkennung = '2';
    // 0 = Product ID (products_id XT muss gleich Product ID Afterbuy sein)
    // 1 = Artikelnummer (products_model XT muss gleich Arrikelnummer Afterbuy sein)
    // 2 = Afterbuy-externe Artikelnummer
    // 13 = Hersteller EAN (products_ean XT muss gleich EAN Afterbuy sein)
    // sollen keine Stammartikel erkannt werden, muss die Zeile: $DATAstring .= "Artikelerkennung=" . $Artikelerkennung ."&";  gelöscht werden
    // sollen keine Stammartikel erkannt werden, muss die Zeile: $Artikelerkennung = '1';  gelöscht werden

    // ######################################

    $oID = $this->order_id;

    // get order data
    $o_query = xtc_db_query("SELECT * FROM " . TABLE_ORDERS . " WHERE orders_id = " . $oID);
    $oData = xtc_db_fetch_array($o_query);

    $ignore_order = isset($ignore_groups) && is_array($ignore_groups) && array_key_exists($oData['customers_status'], $ignore_groups) ? true : false;

    if ($ignore_order === false) {

      if (DB_SERVER_CHARSET == 'utf8') {
        if (file_exists('includes/local/configure.php') || $testmode === true) {
          $afterbuy_URL = 'https://api.afterbuy.de/afterbuy/ShopInterface_testUTF8.aspx';
        } else {
          $afterbuy_URL = 'https://api.afterbuy.de/afterbuy/ShopInterfaceUTF8.aspx';
        }
      } else {
        if (file_exists('includes/local/configure.php') || $testmode === true) {
          $afterbuy_URL = 'https://api.afterbuy.de/afterbuy/ShopInterface_test.aspx';
        } else {
          $afterbuy_URL = 'https://api.afterbuy.de/afterbuy/ShopInterface.aspx';
        }
      }

      $faxQuery = xtc_db_query("SELECT customers_fax FROM " . TABLE_CUSTOMERS . " WHERE customers_id = " . $oData['customers_id']);
      $faxData = xtc_db_fetch_array($faxQuery);

      $cQuery = xtc_db_query("SELECT customers_status_add_tax_ot, customers_status_show_price_tax FROM " . TABLE_CUSTOMERS_STATUS . " WHERE customers_status_id = " . $oData['customers_status'] . " LIMIT 0,1");
      $cData = xtc_db_fetch_array($cQuery);
      $customers_status_show_price_tax = $cData['customers_status_show_price_tax'];

      // customers Address
      $customer = array();
      $customer['id'] = $oData['customers_id'];
      $customer['firma'] = str_replace('&', '%26', $oData['billing_company']);
      $customer['vorname'] = str_replace('&', '%26', $oData['billing_firstname']);
      $customer['nachname'] = str_replace('&', '%26', $oData['billing_lastname']);
      $customer['strasse'] = str_replace('&', '%26', preg_replace("/ /", "%20", $oData['billing_street_address']));
      $customer['strasse2'] = str_replace('&', '%26', preg_replace("/ /", "%20", $oData['billing_suburb']));
      $customer['plz'] = $oData['billing_postcode'];
      $customer['ort'] = str_replace('&', '%26', preg_replace("/ /", "%20", $oData['billing_city']));
      $customer['tel'] = $oData['customers_telephone'];
      $customer['fax'] = $faxData['customers_fax'];
      $customer['mail'] = $oData['customers_email_address'];
      $customer['land'] = $oData['billing_country_iso_code_2'];
      $customer['ustid'] = $oData['customers_vat_id'];
      $customer['customers_status'] = $oData['customers_status'];

      if ($testmode === false) {
        // connect
        $ch = curl_init();

        // This is the URL that you want PHP to fetch.
        // You can also set this option when initializing a session with the curl_init()  function.
        curl_setopt($ch, CURLOPT_URL, $afterbuy_URL);

        // curl_setopt($ch, CURLOPT_CAFILE, 'D:/curl-ca.crt');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        // Set this option to a non-zero value if you want PHP to do a regular HTTP POST.
        // This POST is a normal application/x-www-form-urlencoded  kind, most commonly used by HTML forms.
        curl_setopt($ch, CURLOPT_POST, 1);
      }

      // get gender
      switch ($oData['customers_gender']) {
        case 'm' :
          $customer['gender'] = 'Herr';
          break;
        case 'f' :
          $customer['gender'] = 'Frau';
          break;
        case 'd' :
          $customer['gender'] = 'Divers';
          break;
        default :
          $customer['gender'] = '';
          break;
      }

      // Delivery Address
      $customer['d_firma'] = str_replace('&', '%26', $oData['delivery_company']);
      $customer['d_vorname'] = str_replace('&', '%26', $oData['delivery_firstname']);
      $customer['d_nachname'] = str_replace('&', '%26', $oData['delivery_lastname']);
      $customer['d_strasse'] = str_replace('&', '%26', preg_replace("/ /", "%20", $oData['delivery_street_address']));
      $customer['d_strasse2'] = str_replace('&', '%26', preg_replace("/ /", "%20", $oData['delivery_suburb']));
      $customer['d_plz'] = $oData['delivery_postcode'];
      $customer['d_ort'] = str_replace('&', '%26', preg_replace("/ /", "%20", $oData['delivery_city']));
      $customer['d_land'] = $oData['delivery_country_iso_code_2'];

      // get products related to order
      $p_query = xtc_db_query("SELECT * FROM " . TABLE_ORDERS_PRODUCTS . " WHERE orders_id = " . $oID);
      $p_count = xtc_db_num_rows($p_query);

      // init GET string
      $DATAstring = "Kundenerkennung=1&";
      $DATAstring .= "Action=new&";
      $DATAstring .= "PartnerID=" . $PartnerID . "&";
      $DATAstring .= "PartnerPass=" . $PartnerPass . "&";
      $DATAstring .= "UserID=" . $UserID . "&";
      $DATAstring .= "Kbenutzername=" . $customer['id'] . "_XTC-ORDER_" . $oID . "&";
      $DATAstring .= "Kanrede=" . $customer['gender'] . "&";
      $DATAstring .= "KFirma=" . $customer['firma'] . "&";
      $DATAstring .= "KVorname=" . $customer['vorname'] . "&";
      $DATAstring .= "KNachname=" . $customer['nachname'] . "&";
      $DATAstring .= "KStrasse=" . $customer['strasse'] . "&";
      $DATAstring .= "KStrasse2=" . $customer['strasse2'] . "&";
      $DATAstring .= "KPLZ=" . $customer['plz'] . "&";
      $DATAstring .= "KOrt=" . $customer['ort'] . "&";
      $DATAstring .= "Ktelefon=" . $customer['tel'] . "&";
      $DATAstring .= "Kfax=" . $customer['fax'] . "&";
      $DATAstring .= "Kemail=" . $customer['mail'] . "&";
      $DATAstring .= "KLand=" . $customer['land'] . "&";
      $DATAstring .= "Lieferanschrift=1&";

      // Delivery Address
      $DATAstring .= "KLFirma=" . $customer['d_firma'] . "&";
      $DATAstring .= "KLVorname=" . $customer['d_vorname'] . "&";
      $DATAstring .= "KLNachname=" . $customer['d_nachname'] . "&";
      $DATAstring .= "KLStrasse=" . $customer['d_strasse'] . "&";
      $DATAstring .= "KLStrasse2=" . $customer['d_strasse2'] . "&";
      $DATAstring .= "KLPLZ=" . $customer['d_plz'] . "&";
      $DATAstring .= "KLOrt=" . $customer['d_ort'] . "&";
      $DATAstring .= "KLLand=" . $customer['d_land'] . "&";
      $DATAstring .= "UsStID=" . $customer['ustid'] . "&";
      $DATAstring .= "VID=" . $oID . "&";

      $customer_status = $customer['customers_status'];
      switch ($customer_status) {
        case '0': // Admin
          $is_merchant = 0;
          break;
        case '1': // Gast
          $is_merchant = 0;
          break;
        case '2': // Kunde
          $is_merchant = 0;
          break;
        case '3': // Merchant
          $is_merchant = 1;
          break;
        case '4': // Merchant EU
          $is_merchant = 1;
          break;
        default: //wenn alles nicht zutrifft, dann diese Voreinstellung verwenden
          $is_merchant = 0;
      }

      $is_merchant = isset($dealer_groups) && !empty($dealer_groups) && array_key_exists($customer_status, $dealer_groups) ? 1 : $is_merchant;

      $DATAstring .= "Haendler=" . $is_merchant . "&";

      // products_data
      if (isset($Artikelerkennung) && is_numeric($Artikelerkennung)) $DATAstring .= "Artikelerkennung=" . $Artikelerkennung . "&";
      $nr = 0;
      $anzahl = 0;
      if (!class_exists('xtcPrice')) {
        require_once((defined('RUN_MODE_ADMIN') ? DIR_FS_CATALOG : '') . DIR_WS_CLASSES . 'xtcPrice.php');
        $xtPrice = new xtcPrice($oData['currency'], $oData['customers_status']);
      }
      while ($pDATA = xtc_db_fetch_array($p_query)) {
        $nr++;

        if (!empty($pDATA['products_model']) && is_numeric($pDATA['products_model'])) {
          $artnr = $pDATA['products_model'];
        } else {
          $artnr = $pDATA['products_id'];
        }

        if (isset($Artikelerkennung) && $Artikelerkennung == 0) {
          $stammid = $pDATA['products_id'];
        } elseif (isset($Artikelerkennung) && $Artikelerkennung == 1 && $pDATA['products_model'] != '') {
          $stammid = $pDATA['products_model'];
        } elseif (isset($Artikelerkennung) && $Artikelerkennung == 13 && $pDATA['products_ean'] != '') {
          $stammid = $pDATA['products_ean'];
        } else {
          $stammid = '';
        }

        $DATAstring .= "Artikelnr_" . $nr . "=" . $artnr . "&";
        if ($stammid != '') $DATAstring .= "ArtikelStammID_" . $nr . "=" . $stammid . "&";
        $DATAstring .= "Artikelname_" . $nr . "=" . preg_replace("/&/", "%26", preg_replace("/\"/", "", preg_replace("/ /", "%20", $pDATA['products_name']))) . "&";

        $price = $pDATA['products_price'];
        $tax = $pDATA['products_tax'];
        if ($pDATA['allow_tax'] != '1') {
          if (in_array($pDATA['allow_tax'], array('0', '4'))) {
            $tax = 0;
          } else {
            $price = $xtPrice->xtcAddTax($price, $tax);
          }
        }
        $price = $this->change_dec_separator($price);
        $tax = $this->change_dec_separator($tax);

        $DATAstring .= "ArtikelEPreis_" . $nr . "=" . $price . "&";
        $DATAstring .= "ArtikelMwst_" . $nr . "=" . $tax . "&";
        $DATAstring .= "ArtikelMenge_" . $nr . "=" . $pDATA['products_quantity'] . "&";
        $DATAstring .= "ArtikelGewicht_" . $nr . "=" . $this->getProductsWeight($pDATA['products_id']) . "&";
        $url = HTTP_SERVER . DIR_WS_CATALOG . 'product_info.php?products_id=' . $pDATA['products_id'];
        $DATAstring .= "ArtikelLink_" . $nr . "=" . $url . "&";

        $a_query = xtc_db_query("SELECT * FROM " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " WHERE orders_id = " . $oID . " AND orders_products_id = " . (int)$pDATA['orders_products_id']);
        $options = '';
        while ($aDATA = xtc_db_fetch_array($a_query)) {
          if ($options == '') {
            $options = $aDATA['products_options'] . ":" . $aDATA['products_options_values'];
          } else {
            $options .= "|" . $aDATA['products_options'] . ":" . $aDATA['products_options_values'];
          }
        }
        if ($options != "") {
          $DATAstring .= "Attribute_" . $nr . "=" . $options . "&";
        }
        $anzahl += $pDATA['products_quantity'];
      }

      $coupon = $gv = $discount = $cod_fee = $shipping = $ot_payment_fee = '0.0000';
      $ot_payment_flag = false;
      $cod_flag = false;
      $discount_flag = false;
      $gv_flag = false;
      $coupon_flag = false;

      $order_total_query = xtc_db_query("SELECT *
						                             FROM " . TABLE_ORDERS_TOTAL . "
						                            WHERE orders_id = " . $oID . "
						                         ORDER BY sort_order ASC");

      while ($order_total_values = xtc_db_fetch_array($order_total_query)) {
        // payment fee
        if ($order_total_values['class'] == 'ot_payment') {
          $ot_payment_flag = true;
          $ot_payment_fee = $order_total_values['value'];
        }
        // shippingcosts
        if ($order_total_values['class'] == 'ot_shipping') {
          $shipping = $order_total_values['value'];
        }
        // nachnamegebuer
        if ($order_total_values['class'] == 'ot_cod_fee') {
          $cod_flag = true;
          $cod_fee = $order_total_values['value'];
        }
        // rabatt
        if ($order_total_values['class'] == 'ot_discount') {
          $discount_flag = true;
          $discount = $order_total_values['value'];
        }
        // Gutschein
        if ($order_total_values['class'] == 'ot_gv') {
          $gv_flag = true;
          $gv = ($order_total_values['value'] * (-1));
        }
        // Coupon
        if ($order_total_values['class'] == 'ot_coupon') {
          $coupon_flag = true;
          $coupon = ($order_total_values['value'] * (-1));
        }
      }

      // add cod as product
      if ($cod_flag !== false) {
        $nr++;
        $cod_tax = defined('MODULE_ORDER_TOTAL_COD_FEE_TAX_CLASS') ? xtc_get_tax_rate(MODULE_ORDER_TOTAL_COD_FEE_TAX_CLASS, $this->customer_country_id, $this->customer_zone_id) : 0;
        $DATAstring .= "Artikelnr_" . $nr . "=99999999&";
        $DATAstring .= "Artikelname_" . $nr . "=Nachname&";
        $cod_fee = $this->get_ot_total($customers_status_show_price_tax, $cod_tax, $cod_fee);
        $DATAstring .= "ArtikelEPreis_" . $nr . "=" . $cod_fee . "&";
        $DATAstring .= "ArtikelMwst_" . $nr . "=" . $cod_tax . "&";
        $DATAstring .= "ArtikelMenge_" . $nr . "=1&";
        $p_count++;
      }
      // rabatt
      if ($discount_flag !== false) {
        $nr++;
        $DATAstring .= "Artikelnr_" . $nr . "=99999998&";
        $DATAstring .= "Artikelname_" . $nr . "=Rabatt&";
        $discount = $this->change_dec_separator($discount);
        $DATAstring .= "ArtikelEPreis_" . $nr . "=" . $discount . "&";
        $DATAstring .= "ArtikelMwst_" . $nr . "=0&";
        $DATAstring .= "ArtikelMenge_" . $nr . "=1&";
        $p_count++;
      }
      // Gutschein
      if ($gv_flag !== false) {
        $nr++;
        $gv_tax = defined('MODULE_ORDER_TOTAL_GV_TAX_CLASS') ? xtc_get_tax_rate(MODULE_ORDER_TOTAL_GV_TAX_CLASS, $this->customer_country_id, $this->customer_zone_id) : 0;
        $DATAstring .= "Artikelnr_" . $nr . "=99999997&";
        $DATAstring .= "Artikelname_" . $nr . "=Gutschein&";
        $gv = $this->change_dec_separator(($gv * (-1)));
        $DATAstring .= "ArtikelEPreis_" . $nr . "=" . $gv . "&";
        $DATAstring .= "ArtikelMwst_" . $nr . "=" . $gv_tax . "&";
        $DATAstring .= "ArtikelMenge_" . $nr . "=1&";
        $p_count++;
      }
      // Coupon
      if ($coupon_flag !== false) {
        $nr++;
        $coupon_tax = defined('MODULE_ORDER_TOTAL_COUPON_TAX_CLASS') ? xtc_get_tax_rate(MODULE_ORDER_TOTAL_COUPON_TAX_CLASS, $this->customer_country_id, $this->customer_zone_id) : 0;
        $DATAstring .= "Artikelnr_" . $nr . "=99999996&";
        $DATAstring .= "Artikelname_" . $nr . "=Kupon&";
        $coupon = $this->change_dec_separator(($coupon * (-1)));
        $DATAstring .= "ArtikelEPreis_" . $nr . "=" . $coupon . "&";
        $DATAstring .= "ArtikelMwst_" . $nr . "=" . $coupon_tax . "&";
        $DATAstring .= "ArtikelMenge_" . $nr . "=1&";
        $p_count++;
      }

      $DATAstring .= "PosAnz=" . $p_count . "&";

      if ($ot_payment_flag !== false) {
        $DATAstring .= "ZahlartenAufschlag=" . $this->change_dec_separator($ot_payment_fee) . "&";
      }
 
      $vK = $this->change_dec_separator($shipping);

      $s_method = explode('(', $oData['shipping_method']);
      $s_method = str_replace(' ', '%20', $s_method[0]);

      $DATAstring .= "Versandart=" . $s_method . "&";
      $DATAstring .= "Versandkosten=" . $vK . "&";

      $this->getPayment($oData['payment_method']);
      $DATAstring .= "Zahlart=" . $this->payment_name . "&";
      $DATAstring .= "ZFunktionsID=" . $this->payment_id . "&";

      if ($this->payment_id == '5') {
        $orders_v1_array = array(
          'paypalclassic',
          'paypalcart',
          'paypalplus',
          'paypallink',
          'paypalpluslink',
          'paypalsubscription',
        );

        $orders_v2_array = array(
          'paypal',
          'paypalacdc',
          'paypalpui',
          'paypalexpress',
          'paypalcard',
          'paypalsepa',
          'paypalsofort',
          'paypaltrustly',
          'paypalprzelewy',
          'paypalmybank',
          'paypalideal',
          'paypalgiropay',
          'paypaleps',
          'paypalblik',
          'paypalbancontact',
        );
   
        if (in_array($oData['payment_method'], $orders_v2_array)) {
          require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPaymentV2.php');
          $paypal = new PayPalPaymentV2($oData['payment_method']);
          $payment_order_info_array = $paypal->GetOrderDetails($oID);
        } else {
          require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalInfo.php');
          $paypal = new PayPalInfo($oData['payment_method']);
          $payment_order_info_array = $paypal->order_info($oID);
         }

        if (isset($payment_order_info_array->status)) {
          $DATAstring .= "PaymentStatus=".$payment_order_info_array->status."&";
          $DATAstring .= "PaymentTransactionId=".$payment_order_info_array->id."&";
          if ($payment_order_info_array->status == 'COMPLETED') $DATAstring .= "SetPay=1&";
        } else {
          if ($payment_order_info_array['transactions']['0']['relatedResource']['0']['state'] == 'completed') {
            $DATAstring .= "PaymentStatus=".$payment_order_info_array['transactions']['0']['relatedResource']['0']['state']."&";
            $DATAstring .= "PaymentTransactionId=".$payment_order_info_array['transactions']['0']['relatedResource']['0']['id']."&";
            $DATAstring .= "SetPay=1&";
          } else {
            $DATAstring .= "PaymentTransactionId=".$payment_order_info_array['id']."&";
            $DATAstring .= "PaymentStatus=0&";
          }
        }
      }

      if ($oData['payment_method'] == 'banktransfer') {
        $b_query = xtc_db_query("SELECT * FROM " . TABLE_BANKTRANSFER . " WHERE orders_id = " . $oID);
        if (xtc_db_num_rows($b_query)) {
          $b_data = xtc_db_fetch_array($b_query);
          $DATAstring .= "Bankname=" . $b_data['banktransfer_bankname'] . "&";
          $DATAstring .= "BLZ=" . $b_data['banktransfer_blz'] . "&";
          $DATAstring .= "Kontonummer=" . $b_data['banktransfer_number'] . "&";
          $DATAstring .= "Kontoinhaber=" . $b_data['banktransfer_owner'] . "&";
          $DATAstring .= "BIC=" . $b_data['banktransfer_bic'] . "&";
          $DATAstring .= "IBAN=" . $b_data['banktransfer_iban'] . "&";
        }
      }

      $DATAstring .= "Kommentar=" . urlencode($oData['comments']) . "&";
      $DATAstring .= "Bestandart=shop&";
      $DATAstring .= "NoVersandCalc=1";

      if ($testmode !== false) {
        $mail_content_html = str_replace("&", "&<br />", urldecode($DATAstring));
        $mail_content_txt = str_replace("&", "&\n", urldecode($DATAstring));
        xtc_php_mail(STORE_OWNER_EMAIL_ADDRESS, STORE_NAME . ' - Afterbuy', STORE_OWNER_EMAIL_ADDRESS, STORE_NAME, '', STORE_OWNER_EMAIL_ADDRESS, STORE_NAME, '', '', 'Afterbuy-Info', $mail_content_html, $mail_content_txt);
      } else {
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $DATAstring);
        $result = curl_exec($ch);

        if (preg_match("/<success>1<\/success>/", $result)) {
          // result ok, mark order
          // extract ID from result
          $cdr = explode('<KundenNr>', $result);
          $cdr = explode('</KundenNr>', $cdr[1]);
          $cdr = $cdr[0];
          xtc_db_query("UPDATE " . TABLE_ORDERS . " SET afterbuy_success = 1, afterbuy_id= '" . $cdr . "' WHERE orders_id = " . $oID);

          $check_ab_orderid = xtc_db_query('DESCRIBE ' . TABLE_ORDERS);
          while ($ab_orderid = xtc_db_fetch_array($check_ab_orderid)) {
            if ($ab_orderid['Field'] == 'ab_orderid') {
              $aid = explode('<AID>', $result);
              $aid = explode('</AID>', $aid[1]);
              $aid = $aid[0];
              xtc_db_query("UPDATE " . TABLE_ORDERS . " SET ab_orderid = " . (int)$aid . " WHERE orders_id = " . $oID);
            }
          }

          //set new order status
          if ($order_status != '') {
            xtc_db_query("UPDATE " . TABLE_ORDERS . " SET orders_status = " . (int)$order_status . " WHERE orders_id= " . $oID);
          }

        } else {
          // mail to shopowner
          $mail_content_html = 'Fehler beim Senden der Bestellung: ' . $this->order_id . "<br />\r\n" . 'Folgende Fehlermeldung wurde von afterbuy.de zur&uuml;ckgegeben:' . "<br />\r\n" . "<br />\r\n" . $result;
          $mail_content_txt = 'Fehler beim Senden der Bestellung: ' . $this->order_id . "\r\n" . 'Folgende Fehlermeldung wurde von afterbuy.de zurueckgegeben:' . "\r\n\r\n" . $result;
          xtc_php_mail(STORE_OWNER_EMAIL_ADDRESS, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, STORE_NAME, '', STORE_OWNER_EMAIL_ADDRESS, STORE_NAME, '', '', 'Afterbuy-Error', $mail_content_html, $mail_content_txt);
        }
        // close session
        curl_close($ch);
      }
    }
  }

  function order_send() {
    $check_query = xtc_db_query("SELECT afterbuy_success FROM " . TABLE_ORDERS . " WHERE orders_id = " . $this->order_id);
    $data = xtc_db_fetch_array($check_query);

    if ($data['afterbuy_success'] == 1)
      return false;
    return true;

  }

  function get_ot_total($customers_status_show_price_tax, $tax_rate, $value) {
    $value = ($customers_status_show_price_tax == 1) ? $value : ((($value / 100) * $tax_rate) + $value);
    return $this->change_dec_separator($value);
  }

  function change_dec_separator($value) {
    return preg_replace("/\./", ",", $value);
  }

  function getProductsWeight($id) {
    $check_query = xtc_db_query("SELECT products_weight FROM " . TABLE_PRODUCTS . " WHERE products_id = " . (int)$id);
    $data = xtc_db_fetch_array($check_query);
    $weight = number_format($data['products_weight'], 2, ',', '.');
    return $weight;
  }

  function getPayment($payment) {
    switch ($payment) {
      case 'banktransfer':
        $this->payment_id = '7';
        $this->payment_name = "Bankeinzug";
        break;
      case 'cash':
        $this->payment_id = '2';
        $this->payment_name = "Barzahlung";
        break;
      case 'cod':
        $this->payment_id = '4';
        $this->payment_name = "Nachnahme";
        break;
      case 'invoice':
        $this->payment_id = '6';
        $this->payment_name = "Rechnung";
        break;
      case 'moneyorder':
      case 'eustandardtransfer':
        $this->payment_id = '1';
        $this->payment_name = "Vorkasse";
        break;
      case 'moneybookers':
        $this->payment_id = '15';
        $this->payment_name = "Moneybookers";
        break;
      case 'moneybookers_cc':
        $this->payment_id = '15';
        $this->payment_name = "Moneybookers CC";
        break;
      case 'moneybookers_cgb':
        $this->payment_id = '15';
        $this->payment_name = "Moneybookers CGB";
        break;
      case 'moneybookers_csi':
        $this->payment_id = '15';
        $this->payment_name = "Moneybookers CSI";
        break;
      case 'moneybookers_elv':
        $this->payment_id = '15';
        $this->payment_name = "Moneybookers ELV";
        break;
      case 'moneybookers_giropay':
        $this->payment_id = '15';
        $this->payment_name = "Moneybookers GIROPAY";
        break;
      case 'moneybookers_ideal':
        $this->payment_id = '15';
        $this->payment_name = "Moneybookers IDEAL";
        break;
      case 'moneybookers_mae':
        $this->payment_id = '15';
        $this->payment_name = "Moneybookers MAE";
        break;
      case 'moneybookers_netpay':
        $this->payment_id = '15';
        $this->payment_name = "Moneybookers NETPAY";
        break;
      case 'moneybookers_psp':
        $this->payment_id = '15';
        $this->payment_name = "Moneybookers PSP";
        break;
      case 'moneybookers_pwy':
        $this->payment_id = '15';
        $this->payment_name = "Moneybookers PWY";
        break;
      case 'moneybookers_sft':
        $this->payment_id = '15';
        $this->payment_name = "Moneybookers SFT";
        break;
      case 'moneybookers_wlt':
        $this->payment_id = '15';
        $this->payment_name = "Moneybookers WLT";
        break;
      case 'paypal':
      case 'paypalplus':
      case 'paypalcart':
      case 'paypalclassic':
      case 'paypallink':
      case 'paypalpluslink':
      case 'paypalsubscription':
      case 'paypalacdc':
      case 'paypalpui':
      case 'paypalexpress':
      case 'paypalcard':
      case 'paypalsepa':
      case 'paypalsofort':
      case 'paypaltrustly':
      case 'paypalprzelewy':
      case 'paypalmybank':
      case 'paypalideal':
      case 'paypalgiropay':
      case 'paypaleps':
      case 'paypalblik':
      case 'paypalbancontact':
        $this->payment_id = '5';
        $this->payment_name = "Paypal";
        break;
      case 'sofort_sofortueberweisung_gateway':
      case 'sofort_sofortueberweisung_classic':
        $this->payment_id = '12';
        $this->payment_name = "Sofort";
        break;
      case 'billsafe':
        $this->payment_id = '18';
        $this->payment_name = "Billsafe";
        break;
      case 'cc':
        $this->payment_id = '19';
        $this->payment_name = "Kreditkarte";
        break;
      case 'am_apa':
        $this->payment_id = '99';
        $this->payment_name = "Amazon Payments";
        break;
      case 'ipayment':
        $this->payment_id = '99';
        $this->payment_name = "IPayment";
        break;
      default:
        $this->payment_id = '99';
        $this->payment_name = "sonstige Zahlungsweise";
    }
  }

}

?>