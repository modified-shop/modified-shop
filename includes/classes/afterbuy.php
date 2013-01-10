<?php
/* -----------------------------------------------------------------------------------------
   $Id: afterbuy.php 3072 2012-06-18 15:01:13Z hhacker $ 

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
	function xtc_afterbuy_functions($order_id) {
		$this->order_id = $order_id;
	}

	function process_order() {

		// ############ SETTINGS ################

		// PartnerID
		$PartnerID = AFTERBUY_PARTNERID;

		// your PASSWORD for your PartnerID
		$PartnerPass = AFTERBUY_PARTNERPASS;

		// Your Afterbuy USERNAME
		$UserID = AFTERBUY_USERID;

		// new Orderstatus ID of processed order
		$order_status = AFTERBUY_ORDERSTATUS;

		// ######################################

		$oID = $this->order_id;
		$customer = array ();
		$afterbuy_URL = 'https://api.afterbuy.de/afterbuy/ShopInterface.aspx';

		// connect
		$ch = curl_init();

		// This is the URL that you want PHP to fetch.
		// You can also set this option when initializing a session with the curl_init()  function.
		curl_setopt($ch, CURLOPT_URL, "$afterbuy_URL");

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
		$customer['strasse'] = preg_replace("/ /", "%20", $oData['billing_street_address']); // Hetfield - 2009-08-19 - replaced deprecated function ereg_replace with preg_replace to be ready for PHP >= 5.3
		$customer['plz'] = $oData['billing_postcode'];
		$customer['ort'] = preg_replace("/ /", "%20", $oData['billing_city']); // Hetfield - 2009-08-19 - replaced deprecated function ereg_replace with preg_replace to be ready for PHP >= 5.3
		$customer['tel'] = $oData['billing_telephone'];
		$customer['fax'] = "";
		$customer['mail'] = $oData['customers_email_address'];
		$customer['land'] = $oData['billing_country_iso_code_2'];

		// get gender
		$c_query = xtc_db_query("SELECT customers_gender FROM ".TABLE_CUSTOMERS." WHERE customers_id='".(int)$customer['id']."'");
		$c_data = xtc_db_fetch_array($c_query);
		switch ($c_data['customers_gender']) {
			case 'm' :
				$customer['gender'] = 'Herr';
				break;
			default :
				$customer['gender'] = 'Frau';
				break;
		}

		// Delivery Address
		$customer['d_firma'] = $oData['delivery_company'];
		$customer['d_vorname'] = $oData['delivery_firstname'];
		$customer['d_nachname'] = $oData['delivery_lastname'];
		$customer['d_strasse'] = preg_replace("/ /", "%20", $oData['delivery_street_address']); // Hetfield - 2009-08-19 - replaced deprecated function ereg_replace with preg_replace to be ready for PHP >= 5.3
		$customer['d_plz'] = $oData['delivery_postcode'];
		$customer['d_ort'] = preg_replace("/ /", "%20", $oData['delivery_city']); // Hetfield - 2009-08-19 - replaced deprecated function ereg_replace with preg_replace to be ready for PHP >= 5.3
		$customer['d_land'] = $oData['delivery_country_iso_code_2'];

		// get products related to order
		$p_query = xtc_db_query("SELECT * FROM ".TABLE_ORDERS_PRODUCTS." WHERE orders_id='".(int)$oID."'");

		$p_count = xtc_db_num_rows($p_query);
		// init GET string
		$DATAstring = "Action=new&";
		$DATAstring .= "PartnerID=".$PartnerID."&";
		$DATAstring .= "PartnerPass=".$PartnerPass."&";
		$DATAstring .= "UserID=".$UserID."&";
		$DATAstring .= "Kbenutzername=".$customer['id']."_XTC-ORDER_".$oID."&";
		$DATAstring .= "Kanrede=".$customer['gender']."&";
		$DATAstring .= "KFirma=".$customer['firma']."&";
		$DATAstring .= "KVorname=".$customer['vorname']."&";
		$DATAstring .= "KNachname=".$customer['nachname']."&";
		$DATAstring .= "KStrasse=".$customer['strasse']."&";
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
		$DATAstring .= "KLPLZ=".$customer['d_plz']."&";
		$DATAstring .= "KLOrt=".$customer['d_ort']."&";
		$DATAstring .= "KLLand=".$customer['d_land']."&";

		// products_data
		$nr = 0;
		$anzahl = 0;
		while ($pDATA = xtc_db_fetch_array($p_query)) {
			$nr ++;
			$artnr = $pDATA['products_model'];
			if ($artnr == '')
				$artnr = $pDATA['products_id'];
			$DATAstring .= "Artikelnr_".$nr."=".$artnr."&";
			$DATAstring .= "Artikelname_".$nr."=".preg_replace("/&/", "%38", preg_replace("/\"/", "", preg_replace("/ /", "%20", $pDATA['products_name'])))."&"; // Hetfield - 2009-08-19 - replaced deprecated function ereg_replace with preg_replace to be ready for PHP >= 5.3
			
			if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) $pDATA['products_price']+=$pDATA['products_tax'];
			if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 0) $pDATA['products_tax']=0; 
			$price = preg_replace("/\./", ",", $pDATA['products_price']); // Hetfield - 2009-08-19 - replaced deprecated function ereg_replace with preg_replace to be ready for PHP >= 5.3
			$tax = preg_replace("/\./", ",", $pDATA['products_tax']); // Hetfield - 2009-08-19 - replaced deprecated function ereg_replace with preg_replace to be ready for PHP >= 5.3

			$DATAstring .= "ArtikelEPreis_".$nr."=".$price."&";
			$DATAstring .= "ArtikelMwst_".$nr."=".$tax."&";
			$DATAstring .= "ArtikelMenge_".$nr."=".$pDATA['products_quantity']."&";
			$url = HTTP_SERVER.DIR_WS_CATALOG.'product_info.php?products_id='.$pDATA['products_id'];
			$DATAstring .= "ArtikelLink_".$nr."=".$url."&";

			$a_query = xtc_db_query("SELECT * FROM ".TABLE_ORDERS_PRODUCTS_ATTRIBUTES." WHERE orders_id='".(int)$oID."' AND orders_products_id='".$pDATA['orders_products_id']."'");
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

		$order_total_query = xtc_db_query("SELECT
						                      class,
						                      value,
						                      sort_order
						                      FROM ".TABLE_ORDERS_TOTAL."
						                      WHERE orders_id='".(int)$oID."'
						                      ORDER BY sort_order ASC");

		$order_total = array ();
		$zk = '';
		$cod_fee = '';
		$cod_flag = false;
		$discount_flag = false;
		$gv_flag = false;
		$coupon_flag = false;
		$gv = '';
		while ($order_total_values = xtc_db_fetch_array($order_total_query)) {

			$order_total[] = array ('CLASS' => $order_total_values['class'], 'VALUE' => $order_total_values['value']);

			// shippingcosts
			if ($order_total_values['class'] == 'ot_shipping')
				$shipping = $order_total_values['value'];
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
				$gv = $order_total_values['value'];
			}
			if ($order_total_values['class'] == 'ot_coupon') {
				$coupon_flag = true;
				$coupon = $order_total_values['value'];
			}

		}

		// add cod as product
		if ($cod_flag) {
			// cod tax class
			//    MODULE_ORDER_TOTAL_COD_TAX_CLASS
			$nr ++;
			$DATAstring .= "Artikelnr_".$nr."=99999999&";
			$DATAstring .= "Artikelname_".$nr."=Nachname&";
			$cod_fee = preg_replace("/\./", ",", $cod_fee); // Hetfield - 2009-08-19 - replaced deprecated function ereg_replace with preg_replace to be ready for PHP >= 5.3
			$DATAstring .= "ArtikelEPreis_".$nr."=".$cod_fee."&";
			$DATAstring .= "ArtikelMwst_".$nr."=".$tax."&";
			$DATAstring .= "ArtikelMenge_".$nr."=1&";
			$p_count ++;
		}

		// rabatt
		if ($discount_flag) {
			$nr ++;
			$DATAstring .= "Artikelnr_".$nr."=99999998&";
			$DATAstring .= "Artikelname_".$nr."=Rabatt&";
			$discount = preg_replace("/\./", ",", $discount); // Hetfield - 2009-08-19 - replaced deprecated function ereg_replace with preg_replace to be ready for PHP >= 5.3
			$DATAstring .= "ArtikelEPreis_".$nr."=".$discount."&";
			$DATAstring .= "ArtikelMwst_".$nr."=".$tax."&";
			$DATAstring .= "ArtikelMenge_".$nr."=1&";
			$p_count ++;
		}
		// Gutschein
		if ($gv_flag) {
			$nr ++;
			$DATAstring .= "Artikelnr_".$nr."=99999997&";
			$DATAstring .= "Artikelname_".$nr."=Gutschein&";
			$gv = preg_replace("/\./", ",", ($gv * (-1))); // Hetfield - 2009-08-19 - replaced deprecated function ereg_replace with preg_replace to be ready for PHP >= 5.3
			$DATAstring .= "ArtikelEPreis_".$nr."=".$gv."&";
			$DATAstring .= "ArtikelMwst_".$nr."=0&";
			$DATAstring .= "ArtikelMenge_".$nr."=1&";
			$p_count ++;
		}
		// Kupon
		if ($coupon_flag) {
			$nr ++;
			$DATAstring .= "Artikelnr_".$nr."=99999996&";
			$DATAstring .= "Artikelname_".$nr."=Kupon&";
			$coupon = preg_replace("/\./", ",", ($coupon * (-1))); // Hetfield - 2009-08-19 - replaced deprecated function ereg_replace with preg_replace to be ready for PHP >= 5.3
			$DATAstring .= "ArtikelEPreis_".$nr."=".$coupon."&";
			$DATAstring .= "ArtikelMwst_".$nr."=0&";
			$DATAstring .= "ArtikelMenge_".$nr."=1&";
			$p_count ++;
		}

		$DATAstring .= "PosAnz=".$p_count."&";

		$vK = preg_replace("/\./", ",", $shipping); // Hetfield - 2009-08-19 - replaced deprecated function ereg_replace with preg_replace to be ready for PHP >= 5.3

		if ($oData['payment_method'] == 'cod')
			$oData['payment_method'] = 'Nachnahme';

		$s_method = explode('(', $oData['shipping_method']);
		$s_method = str_replace(' ', '%20', $s_method[0]);

		$DATAstring .= "kommentar=".$oData['comments']."&";
		$DATAstring .= "Versandart=".$s_method."&";
		$DATAstring .= "Versandkosten=".$vK."&";
		$DATAstring .= "Zahlart=".$oData['payment_method']."&";

		//banktransfer data
		if ($oData['payment_method']=='banktransfer') {
		$b_query = xtc_db_query("SELECT * FROM ".TABLE_BANKTRANSFER." WHERE orders_id='".(int)$oID."'");

		if (xtc_db_numrows($b_query)) {
			$b_data = xtc_db_fetch_array($b_query);
			$DATAstring .= "Bankname=".$b_data['banktransfer_bankname']."&";
			$DATAstring .= "BLZ=".$b_data['banktransfer_blz']."&";
			$DATAstring .= "Kontonummer=".$b_data['banktransfer_number']."&";
			$DATAstring .= "Kontoinhaber=".$b_data['banktransfer_owner']."&";
		}
		}

		$DATAstring .= "NoVersandCalc=1";



		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $DATAstring);
		$result = curl_exec($ch);
    
    //BOF - Dokuman - 2010-01-27 - added missing escape Character to preg_match
		//if (preg_match("/<success>1</success>/", $result)) { // Hetfield - 2009-08-19 - replaced deprecated function ereg with preg_match to be ready for PHP >= 5.3
		if (preg_match("/<success>1<\/success>/", $result)) { // Hetfield - 2009-08-19 - replaced deprecated function ereg with preg_match to be ready for PHP >= 5.3
    //EOF - Dokuman - 2010-01-27 - added missing escape Character to preg_match
		
			// result ok, mark order
			// extract ID from result
			$cdr = explode('<KundenNr>', $result);
			$cdr = explode('</KundenNr>', $cdr[1]);
			$cdr = $cdr[0];
			xtc_db_query("update ".TABLE_ORDERS." set afterbuy_success='1',afterbuy_id='".$cdr."' where orders_id='".(int)$oID."'");

			//set new order status
			if ($order_status != '') {
				xtc_db_query("update ".TABLE_ORDERS." set orders_status='".$order_status."' where orders_id='".(int)$oID."'");
			}
		} else {

			// mail to shopowner
			$mail_content = 'Fehler bei &Uuml;bertragung der Bestellung: '.$oID.chr(13).chr(10).'Folgende Fehlermeldung wurde vom afterbuy.de zur&uuml;ckgegeben:'.chr(13).chr(10).$result;

			mail(EMAIL_BILLING_ADDRESS, "Afterbuy-Fehl&uuml;bertragung", $mail_content);

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

}
?>