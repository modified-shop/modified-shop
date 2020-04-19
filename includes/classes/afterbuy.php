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

	// constructor
	function __construct($order_id) {
		$this->order_id = $order_id;
	}

	function process_order() {
    global $xtPrice;

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
    // 2 = EAN (products_ean XT muss gleich EAN Afterbuy sein)
    // sollen keine Stammartikel erkannt werden, muss diese Zeile: $Artikelerkennung = '1';  gelöscht oder auskommentiert werden

    // ######################################

		$oID = $this->order_id;
		$customer = array ();
    if (DB_SERVER_CHARSET == 'utf8') {
      $afterbuy_URL = 'https://api.afterbuy.de/afterbuy/shopinterfaceUTF8.aspx';
    } else {
      $afterbuy_URL = 'https://api.afterbuy.de/afterbuy/ShopInterface.aspx';
    }

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

		// get order data
		$o_query = xtc_db_query("SELECT * FROM ".TABLE_ORDERS." WHERE orders_id='".(int)$oID."'");
		$oData = xtc_db_fetch_array($o_query);

		// customers Address
		$customer['id'] = $oData['customers_id'];
		$customer['firma'] = $oData['billing_company'];
		$customer['vorname'] = $oData['billing_firstname'];
		$customer['nachname'] = $oData['billing_lastname'];
		$customer['strasse'] = preg_replace("/ /", "%20", $oData['billing_street_address']); 
		$customer['strasse2'] = preg_replace("/ /", "%20", $oData['billing_suburb']); 
		$customer['plz'] = $oData['billing_postcode'];
		$customer['ort'] = preg_replace("/ /", "%20", $oData['billing_city']); 
		$customer['tel'] = $oData['customers_telephone'];
		$customer['fax'] = "";
		$customer['mail'] = $oData['customers_email_address'];
		$customer['land'] = $oData['billing_country_iso_code_2'];
    $customer['ustid'] = $oData['customers_vat_id'];
    $customer['customers_status'] = $oData['customers_status'];

		// get gender
    switch ($oData['customers_gender']) {
      case 'm' :
        $customer['gender'] = 'Herr';
        break;
      case 'f' :
        $customer['gender'] = 'Frau';
        break;
      default :
        $customer['gender'] = '';
        break;
    }

		// Delivery Address
		$customer['d_firma'] = $oData['delivery_company'];
		$customer['d_vorname'] = $oData['delivery_firstname'];
		$customer['d_nachname'] = $oData['delivery_lastname'];
		$customer['d_strasse'] = preg_replace("/ /", "%20", $oData['delivery_street_address']); 
		$customer['d_strasse2'] = preg_replace("/ /", "%20", $oData['delivery_suburb']); 
		$customer['d_plz'] = $oData['delivery_postcode'];
		$customer['d_ort'] = preg_replace("/ /", "%20", $oData['delivery_city']); 
		$customer['d_land'] = $oData['delivery_country_iso_code_2'];

		// get products related to order
		$p_query = xtc_db_query("SELECT * FROM ".TABLE_ORDERS_PRODUCTS." WHERE orders_id='".(int)$oID."'");
		$p_count = xtc_db_num_rows($p_query);

		// init GET string
    $DATAstring = "Kundenerkennung=1&";
    $DATAstring .= "Action=new&";
		$DATAstring .= "PartnerID=".$PartnerID."&";
		$DATAstring .= "PartnerPass=".$PartnerPass."&";
		$DATAstring .= "UserID=".$UserID."&";
		$DATAstring .= "Kbenutzername=".$customer['id']."_XTC-ORDER_".$oID."&";
		$DATAstring .= "Kanrede=".$customer['gender']."&";
		$DATAstring .= "KFirma=".$customer['firma']."&";
		$DATAstring .= "KVorname=".$customer['vorname']."&";
		$DATAstring .= "KNachname=".$customer['nachname']."&";
		$DATAstring .= "KStrasse=".$customer['strasse']."&";
		$DATAstring .= "KStrasse2=".$customer['strasse2']."&";
		$DATAstring .= "KPLZ=".$customer['plz']."&";
		$DATAstring .= "KOrt=".$customer['ort']."&";
		$DATAstring .= "Ktelefon=".$customer['tel']."&";
		$DATAstring .= "Kfax=&";
		$DATAstring .= "Kemail=".$customer['mail']."&";
		$DATAstring .= "KLand=".$customer['land']."&";
		$DATAstring .= "Lieferanschrift=1&";

		// Delivery Address
		$DATAstring .= "KLFirma=".$customer['d_firma']."&";
		$DATAstring .= "KLVorname=".$customer['d_vorname']."&";
		$DATAstring .= "KLNachname=".$customer['d_nachname']."&";
		$DATAstring .= "KLStrasse=".$customer['d_strasse']."&";
		$DATAstring .= "KLStrasse2=".$customer['d_strasse2']."&";
		$DATAstring .= "KLPLZ=".$customer['d_plz']."&";
		$DATAstring .= "KLOrt=".$customer['d_ort']."&";
		$DATAstring .= "KLLand=".$customer['d_land']."&";
    $DATAstring .= "UsStID=".$customer['ustid']."&";
    $DATAstring .= "VID=" . $oID . "&";

    $customer_status = $customer['customers_status'];
    switch($customer_status) {
      case '0': //Admin
        $DATAstring .= "Haendler=0&";
        break;
      case '1': //Gast
        $DATAstring .= "Haendler=0&";
        break;
      case '2': //Kunde
        $DATAstring .= "Haendler=0&";
        break;
      case '3': //im Standard B2B
        $DATAstring .= "Haendler=1&";
        break;
      case '4': //eigene Kundengruppe
        $DATAstring .= "Haendler=1&";
        break;
      case '5': //eigene Kundengruppe
        $DATAstring .= "Haendler=1&";
        break;
      case '6': //eigene Kundengruppe
        $DATAstring .= "Haendler=1&";
        break;
      case '7': //eigene Kundengruppe
        $DATAstring .= "Haendler=1&";
        break;
      default: //wenn alles nicht zutrifft
        $DATAstring .= "Haendler=0&";
    }

    $cQuery = xtc_db_query("SELECT customers_status_add_tax_ot, customers_status_show_price_tax FROM " . TABLE_CUSTOMERS_STATUS . " WHERE customers_status_id = '" . $oData['customers_status'] . "' LIMIT 0,1");
    $cData  = xtc_db_fetch_array($cQuery);

    // products_data
    if (isset($Artikelerkennung) && is_numeric($Artikelerkennung)) $DATAstring .= "Artikelerkennung=" . $Artikelerkennung . "&";
		$nr = 0;
		$anzahl = 0;
		if (!class_exists (xtcPrice)) {
		  require_once (DIR_WS_CLASSES . 'xtcPrice.php');
		  $xtPrice = new xtcPrice($oData['currency'],$oData['customers_status']);
		}
		while ($pDATA = xtc_db_fetch_array($p_query)) {
			$nr ++;

      if (!empty($pDATA['products_model']) && is_numeric($pDATA['products_model'])) {
        $artnr = $pDATA['products_model'];
      } else {
        $artnr = $pDATA['products_id'];
      }

      if ($Artikelerkennung == 0) {
        $stammid = $pDATA['products_id'];
      } elseif ($Artikelerkennung == 1 && $pDATA['products_model'] != '') {
        $stammid = $pDATA['products_model'];
      } elseif ($Artikelerkennung == 2 && $pDATA['products_ean'] != '') {
        $stammid = $pDATA['products_ean'];
      } else {
        $stammid = '';
      }

      $DATAstring .= "Artikelnr_".$nr."=".$artnr."&";
      if ($stammid != '') $DATAstring .= "ArtikelStammID_".$nr."=".$stammid."&";
			$DATAstring .= "Artikelname_".$nr."=".preg_replace("/&/", "%38", preg_replace("/\"/", "", preg_replace("/ /", "%20", $pDATA['products_name'])))."&";

      $price = $pDATA['products_price'];
      $tax = $pDATA['products_tax'];
      if ($pDATA['allow_tax'] == 0) {
        if ($cData['customers_status_add_tax_ot'] == 0) {
          $tax = 0;
        } else {
          $price = $xtPrice->xtcAddTax($price, $tax);
        }
      }
      $price = preg_replace("/\./", ",", $price);
      $tax = preg_replace("/\./", ",", $tax);

      $DATAstring .= "ArtikelEPreis_".$nr."=".$price."&";
			$DATAstring .= "ArtikelMwst_".$nr."=".$tax."&";
			$DATAstring .= "ArtikelMenge_".$nr."=".$pDATA['products_quantity']."&";
			$DATAstring .= "ArtikelGewicht_".$nr."=".$this->getProductsWeight($pDATA['products_id'])."&";
			$url = HTTP_SERVER.DIR_WS_CATALOG.'product_info.php?products_id='.$pDATA['products_id'];
			$DATAstring .= "ArtikelLink_".$nr."=".$url."&";

			$a_query = xtc_db_query("SELECT * FROM ".TABLE_ORDERS_PRODUCTS_ATTRIBUTES." WHERE orders_id='".(int)$oID."' AND orders_products_id='".(int)$pDATA['orders_products_id']."'");
			$options = '';
			while ($aDATA = xtc_db_fetch_array($a_query)) {
				if ($options == '') {
					$options = $aDATA['products_options'].":".$aDATA['products_options_values'];
				} else {
					$options .= "|".$aDATA['products_options'].":".$aDATA['products_options_values'];
				}
			}
			if ($options != "") {
				$DATAstring .= "Attribute_".$nr."=".$options."&";
			}
			$anzahl += $pDATA['products_quantity'];
		}

    $customers_status_show_price_tax = $cData['customers_status_show_price_tax'];

		$order_total_query = xtc_db_query("SELECT *
						                             FROM ".TABLE_ORDERS_TOTAL."
						                            WHERE orders_id='".(int)$oID."'
						                         ORDER BY sort_order ASC");

		$order_total = array ();
		$shipping = '0.0000';
		$cod_fee = '';
		$cod_flag = false;
		$discount_flag = false;
		$gv_flag = false;
		$coupon_flag = false;
		$gv = '';
		while ($order_total_values = xtc_db_fetch_array($order_total_query)) {

			$order_total[] = array ('CLASS' => $order_total_values['class'], 'VALUE' => $order_total_values['value']);

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
		if ($cod_flag) {
			// cod tax class
			// MODULE_ORDER_TOTAL_COD_TAX_CLASS
			$nr ++;
      $codtax = xtc_get_tax_rate(MODULE_ORDER_TOTAL_COD_TAX_CLASS);
			$DATAstring .= "Artikelnr_".$nr."=99999999&";
			$DATAstring .= "Artikelname_".$nr."=Nachname&";
      $cod_fee = $this->get_ot_total($customers_status_show_price_tax, $codtax, $cod_fee);
			$DATAstring .= "ArtikelEPreis_".$nr."=".$cod_fee."&";
      $DATAstring .= "ArtikelMwst_".$nr."=".$codtax."&";
			$DATAstring .= "ArtikelMenge_".$nr."=1&";
			$p_count ++;
		}
		// rabatt
		if ($discount_flag) {
			$nr ++;
			$DATAstring .= "Artikelnr_".$nr."=99999998&";
			$DATAstring .= "Artikelname_".$nr."=Rabatt&";
			$discount = preg_replace("/\./", ",", $discount); 
			$DATAstring .= "ArtikelEPreis_".$nr."=".$discount."&";
      $DATAstring .= "ArtikelMwst_".$nr."=0&";
			$DATAstring .= "ArtikelMenge_".$nr."=1&";
			$p_count ++;
		}
		// Gutschein
		if ($gv_flag) {
			$nr ++;
			$gvtax = xtc_get_tax_rate(MODULE_ORDER_TOTAL_GV_TAX_CLASS);
			$DATAstring .= "Artikelnr_".$nr."=99999997&";
			$DATAstring .= "Artikelname_".$nr."=Gutschein&";
      $gv = preg_replace("/\./", ",", ($gv * (-1)));
			$DATAstring .= "ArtikelEPreis_".$nr."=".$gv."&";
			$DATAstring .= "ArtikelMwst_".$nr."=".$gvtax."&";
			$DATAstring .= "ArtikelMenge_".$nr."=1&";
			$p_count ++;
		}
		// Kupon
		if ($coupon_flag) {
			$nr ++;
			$coupontax = xtc_get_tax_rate(MODULE_ORDER_TOTAL_COUPON_TAX_CLASS);
			$DATAstring .= "Artikelnr_".$nr."=99999996&";
			$DATAstring .= "Artikelname_".$nr."=Kupon&";
      $coupon = preg_replace("/\./", ",", ($coupon * (-1)));
			$DATAstring .= "ArtikelEPreis_".$nr."=".$coupon."&";
			$DATAstring .= "ArtikelMwst_".$nr."=".$coupontax."&";
			$DATAstring .= "ArtikelMenge_".$nr."=1&";
			$p_count ++;
		}

		$DATAstring .= "PosAnz=".$p_count."&";

    $vK = preg_replace("/\./", ",", $shipping);

    $s_method = explode('(', $oData['shipping_method']);
		$s_method = str_replace(' ', '%20', $s_method[0]);

    $DATAstring .= "Kommentar=".urlencode($oData['comments'])."&";
    $DATAstring .= "Bestandart=shop&";
		$DATAstring .= "Versandart=".$s_method."&";
    $DATAstring .= "Versandkosten=".$vK."&";
    $this->getPayment($oData['payment_method']);
    $DATAstring .= "Zahlart=" . $this->payment_name . "&";
    $DATAstring .= "ZFunktionsID=" . $this->payment_id . "&";

		//banktransfer data
		if ($oData['payment_method']=='banktransfer') {
		  $b_query = xtc_db_query("SELECT * FROM ".TABLE_BANKTRANSFER." WHERE orders_id='".(int)$oID."'");

      if (xtc_db_numrows($b_query)) {
        $b_data = xtc_db_fetch_array($b_query);
        $DATAstring .= "Bankname=".$b_data['banktransfer_bankname']."&";
        $DATAstring .= "BLZ=".$b_data['banktransfer_blz']."&";
        $DATAstring .= "Kontonummer=".$b_data['banktransfer_number']."&";
        $DATAstring .= "Kontoinhaber=".$b_data['banktransfer_owner']."&";
        $DATAstring .= "BIC=".$b_data['banktransfer_bic']."&";
        $DATAstring .= "IBAN=".$b_data['banktransfer_iban']."&";
      }
		}

		$DATAstring .= "NoVersandCalc=1";

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $DATAstring);
		$result = curl_exec($ch);
    
		if (preg_match("/<success>1<\/success>/", $result)) {
		
			// result ok, mark order
			// extract ID from result
			$cdr = explode('<KundenNr>', $result);
			$cdr = explode('</KundenNr>', $cdr[1]);
			$cdr = $cdr[0];
			xtc_db_query("UPDATE ".TABLE_ORDERS." SET afterbuy_success='1',afterbuy_id='".$cdr."' WHERE orders_id='".(int)$oID."'");

			//set new order status
			if ($order_status != '') {
				xtc_db_query("UPDATE ".TABLE_ORDERS." SET orders_status='".(int)$order_status."' WHERE orders_id='".(int)$oID."'");
			}
		} else {
			// mail to shopowner
			$mail_content_html = 'Fehler beim Senden der Bestellung: '.$this->order_id."<br />\r\n".'Folgende Fehlermeldung wurde von afterbuy.de zur&uuml;ckgegeben:'."<br />\r\n"."<br />\r\n".$result;
      $mail_content_txt = 'Fehler beim Senden der Bestellung: '.$this->order_id."\r\n".'Folgende Fehlermeldung wurde von afterbuy.de zurueckgegeben:'."\r\n\r\n".$result;
     		xtc_php_mail(STORE_OWNER_EMAIL_ADDRESS,STORE_NAME,STORE_OWNER_EMAIL_ADDRESS, STORE_NAME,'',STORE_OWNER_EMAIL_ADDRESS, STORE_NAME,'','', 'Afterbuy-Error', $mail_content_html, $mail_content_txt);
    }
		// close session
		curl_close($ch);
	}

	// Funktion zum ueberpruefen ob Bestellung bereits an Afterbuy gesendet.
	function order_send() {

		$check_query = xtc_db_query("SELECT afterbuy_success FROM ".TABLE_ORDERS." WHERE orders_id='".(int)$this->order_id."'");
		$data = xtc_db_fetch_array($check_query);

		if ($data['afterbuy_success'] == 1)
			return false;
		return true;

	}

	function getProductsWeight($id) {
		$check_query = xtc_db_query("SELECT products_weight FROM ".TABLE_PRODUCTS." WHERE products_id='".(int)$id."'");
		$data = xtc_db_fetch_array($check_query);		
		$weight = number_format($data['products_weight'],2,',','.');
		return $weight;
	}

  function getPayment($payment) {
    switch($payment) {
      case 'banktransfer':
        $this->payment_id   = '7';
        $this->payment_name = "Bankeinzug";
        break;
      case 'cash':
        $this->payment_id   = '2';
        $this->payment_name = "Barzahlung";
        break;
      case 'cod':
        $this->payment_id   = '4';
        $this->payment_name = "Nachnahme";
        break;
      case 'invoice':
        $this->payment_id   = '6';
        $this->payment_name = "Rechnung";
        break;
      case 'moneyorder':
      case 'eustandardtransfer':
        $this->payment_id   = '1';
        $this->payment_name = "Vorkasse";
        break;
      case 'moneybookers':
        $this->payment_id   = '15';
        $this->payment_name = "Moneybookers";
        break;
      case 'moneybookers_cc':
        $this->payment_id   = '15';
        $this->payment_name = "Moneybookers CC";
        break;
      case 'moneybookers_cgb':
        $this->payment_id   = '15';
        $this->payment_name = "Moneybookers CGB";
        break;
      case 'moneybookers_csi':
        $this->payment_id   = '15';
        $this->payment_name = "Moneybookers CSI";
        break;
      case 'moneybookers_elv':
        $this->payment_id   = '15';
        $this->payment_name = "Moneybookers ELV";
        break;
      case 'moneybookers_giropay':
        $this->payment_id   = '15';
        $this->payment_name = "Moneybookers GIROPAY";
        break;
      case 'moneybookers_ideal':
        $this->payment_id   = '15';
        $this->payment_name = "Moneybookers IDEAL";
        break;
      case 'moneybookers_mae':
        $this->payment_id   = '15';
        $this->payment_name = "Moneybookers MAE";
        break;
      case 'moneybookers_netpay':
        $this->payment_id   = '15';
        $this->payment_name = "Moneybookers NETPAY";
        break;
      case 'moneybookers_psp':
        $this->payment_id   = '15';
        $this->payment_name = "Moneybookers PSP";
        break;
      case 'moneybookers_pwy':
        $this->payment_id   = '15';
        $this->payment_name = "Moneybookers PWY";
        break;
      case 'moneybookers_sft':
        $this->payment_id   = '15';
        $this->payment_name = "Moneybookers SFT";
        break;
      case 'moneybookers_wlt':
        $this->payment_id   = '15';
        $this->payment_name = "Moneybookers WLT";
        break;
      case 'paypal':
      case 'paypalplus':
      case 'paypalcart':
      case 'paypalclassic':
      case 'paypallink':
      case 'paypalpluslink':
      case 'paypalinstallment':
        $this->payment_id   = '5';
        $this->payment_name = "Paypal";
        break;

      case 'sofort_sofortueberweisung_gateway':
      case 'sofort_sofortueberweisung_classic':
        $this->payment_id   = '12';
        $this->payment_name = "Sofort";
        break;
      case 'billsafe':
        $this->payment_id   = '18';
        $this->payment_name = "Billsafe";
        break;
      case 'ipayment':
        $this->payment_id   = '99';
        $this->payment_name = "IPayment";
        break;
      case 'cc':
        $this->payment_id   = '99';
        $this->payment_name = "Kreditkarte";
        break;
      default:
        $this->payment_id   = '99';
        $this->payment_name = "sonstige Zahlungsweise";
    }
  }

}
?>