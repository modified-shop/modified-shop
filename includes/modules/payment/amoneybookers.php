<?php

/* -----------------------------------------------------------------------------------------
   $Id: amoneybookers.php 150 2007-01-24 09:54:08Z mzanier $

   xt:Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2006 xt:Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(moneybookers.php,v 1.00 2003/10/27); www.oscommerce.com

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   Moneybookers v1.0                       Autor:    Gabor Mate  <gabor(at)jamaga.hu>

   Released under the GNU General Public License
   
   // Version History
    * 2.0 xt:Commerce Adaption
    * 2.1 new workflow, tmp orders
   
   
   ---------------------------------------------------------------------------------------*/

class amoneybookers {
	var $code, $title, $description, $enabled, $auth_num, $transaction_id;
	var $mbLanguages, $mbCurrencies, $aCurrencies, $defCurr, $defLang;

	// class constructor
	function __construct() {
		global $order, $language;

		$this->code = 'amoneybookers';
		$this->version = '2.1';
		$this->title = MODULE_PAYMENT_AMONEYBOOKERS_TEXT_TITLE;
		$this->description = MODULE_PAYMENT_AMONEYBOOKERS_TEXT_DESCRIPTION;
		$this->sort_order = MODULE_PAYMENT_AMONEYBOOKERS_SORT_ORDER;
		$this->enabled = ((MODULE_PAYMENT_AMONEYBOOKERS_STATUS == 'True') ? true : false);
		$this->info = MODULE_PAYMENT_AMONEYBOOKERS_TEXT_INFO;
		$this->logo = xtc_image(DIR_WS_ICONS . 'logo_moneybookers.jpg');
		$this->landingPage = 'http://www.moneybookers.com/ecommerce_btc/de/index.html';
		$this->tmpOrders = true;
		$this->tmpStatus = MODULE_PAYMENT_AMONEYBOOKERS_TMP_STATUS_ID;
		$this->icons_available = xtc_image(DIR_WS_ICONS . 'cc_amex_small.jpg') . ' ' .
		xtc_image(DIR_WS_ICONS . 'cc_mastercard_small.jpg') . ' ' .
		xtc_image(DIR_WS_ICONS . 'cc_visa_small.jpg') . ' ' .
		xtc_image(DIR_WS_ICONS . 'cc_diners_small.jpg') . ' ' .
		xtc_image(DIR_WS_ICONS . 'giropay_small.jpg') . ' ' .
		xtc_image(DIR_WS_ICONS . 'visa_electron_small.jpg') . ' ' .
		xtc_image(DIR_WS_ICONS . 'swift_small.jpg') . ' ' .
		xtc_image(DIR_WS_ICONS . 'elv_small.jpg') . ' ' .
		xtc_image(DIR_WS_ICONS . 'cheque_small.jpg');

		$this->repost = false;
		$this->Error = '';
		$this->oID = 0;

		$this->debug = false;

		$this->transaction_id = '';

		$this->mbLanguages = array (
			"EN",
			"DE",
			"ES",
			"FR"
		);

		if ($this->enabled) {

			$result = xtc_db_query("SELECT mb_currID FROM payment_AMONEYBOOKERS_currencies");
			while (list ($currID) = xtc_db_fetch_row($result)) {
				$this->mbCurrencies[] = $currID;
			}

			$result = xtc_db_query("SELECT code FROM currencies");
			while (list ($currID) = xtc_db_fetch_row($result)) {
				$this->aCurrencies[] = $currID;
			}


			$this->defCurr = DEFAULT_CURRENCY;

			$this->defLang = DEFAULT_LANGUAGE;
			$this->defLang = strtoupper($this->defLang);
			if (!in_array($this->defLang, $this->mbLanguages)) {
				$this->defLang = "EN";
			}
		}

				if ((int) MODULE_PAYMENT_AMONEYBOOKERS_ORDER_STATUS_ID > 0) {
					$this->order_status = MODULE_PAYMENT_AMONEYBOOKERS_ORDER_STATUS_ID;
				}
		//
		if (is_object($order))
			$this->update_status();

		$this->form_action_url = 'https://www.moneybookers.com/app/payment.pl';
	}

	////
	// Status update
	function update_status() {
		global $order;

		if (($this->enabled == true) && ((int) MODULE_PAYMENT_AMONEYBOOKERS_ZONE > 0)) {
			$check_flag = false;
			$check_query = xtc_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_AMONEYBOOKERS_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
			while ($check = xtc_db_fetch_array($check_query)) {
				if ($check['zone_id'] < 1) {
					$check_flag = true;
					break;
				}
				elseif ($check['zone_id'] == $order->billing['zone_id']) {
					$check_flag = true;
					break;
				}
			}

			if ($check_flag == false) {
				$this->enabled = false;
			}
		}
	}

	// class methods
	function javascript_validation() {
		return false;
	}

	function selection() {

		$content = array();
		$accepted = '';
		$icons = explode(',', MODULE_PAYMENT_AMONEYBOOKERS_ICONS);
		foreach ($icons as $key => $val)
			$accepted .= xtc_image(DIR_WS_ICONS . $val) . ' ';


		$content = array_merge($content, array (
			array (
				'title' => ' ',
				'field' => '<div align="right"><a href="'.$this->landingPage.'" target="_blank">'.$this->logo.'</a></div>'
			)
		));
		$content = array_merge($content, array (
			array (
				'title' => '',
			'field' => MODULE_PAYMENT_AMONEYBOOKERS_TEXT_INFO_1
		)));
		$content = array_merge($content, array (
			array (
				'title' => ' ',
				'field' => $accepted
			)
		));
		$content = array_merge($content, array (
			array (
				'title' => xtc_image(DIR_WS_ICONS . 'arrow_accepted.jpg'
			),
			'field' => MODULE_PAYMENT_AMONEYBOOKERS_TEXT_INFO_2
		)));
		$content = array_merge($content, array (
			array (
				'title' => xtc_image(DIR_WS_ICONS . 'arrow_accepted.jpg'
			),
			'field' => MODULE_PAYMENT_AMONEYBOOKERS_TEXT_INFO_3
		)));
		$content = array_merge($content, array (
			array (
				'title' => xtc_image(DIR_WS_ICONS . 'arrow_accepted.jpg'
			),
			'field' => MODULE_PAYMENT_AMONEYBOOKERS_TEXT_INFO_4
		)));
		$content = array_merge($content, array (
			array (
				'title' => xtc_image(DIR_WS_ICONS . 'arrow_accepted.jpg'),
				'field' => MODULE_PAYMENT_AMONEYBOOKERS_TEXT_INFO_5
			)
		));
		

		return array (
			'id' => $this->code,
			'module' => $this->title,
			'fields' => $content,
			'description' => $this->info
		);
	}

	function pre_confirmation_check() {
		return false;
	}

	function confirmation() {
		return false;
	}

	function process_button() {
		return false;
	}


	function payment_action() {
		global $order, $xtPrice,$insert_id;

		$result = xtc_db_query("SELECT code FROM languages WHERE languages_id = '" . (int)$_SESSION['languages_id'] . "'");
		list ($lang_code) = xtc_db_fetch_row($result);
		$mbLanguage = strtoupper($lang_code);
		if ($mbLanguage == "US") {
			$mbLanguage = "EN";
		}
		if (!in_array($mbLanguage, $this->mbLanguages)) {
			$mbLanguage = MODULE_PAYMENT_AMONEYBOOKERS_LANGUAGE;
		}

		$mbCurrency = $_SESSION['currency'];
		if (!in_array($_SESSION['currency'], $this->mbCurrencies)) {
			$mbCurrency = MODULE_PAYMENT_AMONEYBOOKERS_CURRENCY;
		}

		$result = xtc_db_query("SELECT mb_cID FROM payment_AMONEYBOOKERS_countries, countries WHERE (xtc_cID = countries_id) AND (countries_id = '{$order->billing['country']['id']}')");
		list ($mbCountry) = xtc_db_fetch_row($result);

		$this->transaction_id = $this->generate_trid();
		$result = xtc_db_query("INSERT INTO payment_moneybookers (mb_TRID, mb_DATE) VALUES ('{$this->transaction_id}', NOW())");
		if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
			$total = $order->info['total'] + $order->info['tax'];
		} else {
			$total = $order->info['total'];
		}
		if ($_SESSION['currency'] == $mbCurrency) {
			$amount = round($total, $xtPrice->get_decimal_places($mbCurrency));
		} else {
			$amount = round($xtPrice->xtcCalculateCurrEx($total, $mbCurrency), $xtPrice->get_decimal_places($mbCurrency));
		}

//		$process_button_string = 
		
		      $params = array('pay_to_email'=>  MODULE_PAYMENT_AMONEYBOOKERS_EMAILID,
		'transaction_id'=> $this->transaction_id,
		'return_url'=> xtc_href_link(FILENAME_CHECKOUT_PROCESS, 'trid=' . $this->transaction_id, 'NONSSL', true, false),
		'cancel_url'=>  xtc_href_link(FILENAME_CHECKOUT_PAYMENT, MODULE_PAYMENT_AMONEYBOOKERS_ERRORTEXT1 . $this->code . MODULE_PAYMENT_AMONEYBOOKERS_ERRORTEXT2, 'SSL', true, false),
		'status_url'=>  xtc_href_link('callback/moneybookers/callback_mb.php'),
		'language'=>  $mbLanguage,
		'pay_from_email'=>  $order->customer['email_address'],
		'amount'=>  $amount,
		'currency'=>  $mbCurrency,
		'detail1_description'=>  'Shop:',
		'detail1_text'=>  STORE_NAME.' Order:'.$insert_id,

		'detail2_description'=>  'Datum:',
		'detail2_text'=>  strftime(DATE_FORMAT_LONG),

		'amount2_description'=>  'Summe:',
		'amount2'=>  $amount,

		'merchant_fields'=>  'Field1,platform',
		'Field1'=>  md5(MODULE_PAYMENT_AMONEYBOOKERS_MERCHANTID),
    'platform' => '87039772',
    
		'firstname'=>  $order->billing['firstname'],
		'lastname'=>  $order->billing['lastname'],
		'address'=>  $order->billing['street_address'],
		'postal_code'=>  $order->billing['postcode'],
		'city'=>  $order->billing['city'],
		'state'=>  $order->billing['state'],
		'country'=>  $mbCountry,
		'confirmation_note'=>  MODULE_PAYMENT_AMONEYBOOKERS_CONFIRMATION_TEXT);
		
		$data = '';
        foreach ($params as $key => $value) {          
          //$value = strtr($value, "áéíóöõúüûÁÉÍÓÖÕÚÜÛ", "aeiooouuuAEIOOOUUU"); //web28 -2011-05-24 - Fix special characters          
          if ($key!='status_url') {
            $value=urlencode(utf8_encode($value)); //web28 -2011-05-24 - Fix special characters
          }
          $data .= $key . '=' . $value . "&";
        }

		// moneyboocers.com payment gateway does not accept accented characters!
		// Please feel free to add any other accented characters to the list.
//		return strtr($process_button_string, "áéíóöõúüûÁÉÍÓÖÕÚÜÛ", "aeiooouuuAEIOOOUUU");

		// insert data
		xtc_db_query("UPDATE payment_moneybookers SET mb_ORDERID = '" . $insert_id . "' WHERE mb_TRID = '" . $this->transaction_id . "'");
		xtc_redirect($this->form_action_url.'?'.$data);
	}

	// manage returning data from moneybookers (errors, failures, success etc.)
	function before_process() {

		return false;

	}

	function after_process() {
		global $insert_id, $_GET;
//		xtc_db_query("UPDATE payment_moneybookers SET mb_ORDERID = '" . $insert_id . "' WHERE mb_TRID = '" . $_GET['trid'] . "'");
//		if ($this->order_status) xtc_db_query("UPDATE ".TABLE_ORDERS." SET orders_status='".$this->order_status."' WHERE orders_id='".$insert_id."'");

	}

	function admin_order($oID) {
		$oID = (int) $oID;

		$query = "SELECT * FROM payment_moneybookers WHERE mb_ORDERID = '" . $oID . "'";
		$query = xtc_db_query($query);

		$data = xtc_db_fetch_array($query);

		$html = '
						<tr>
				            <td class="main">' . MB_TEXT_MBDATE . '</td>
				            <td class="main">' . $data['mb_DATE'] . '</td>
				        </tr>
						<tr>
				            <td class="main">' . MB_TEXT_MBTID . '</td>
				            <td class="main">' . $data['mb_MBTID'] . '</td>
				        </tr>
						<tr>
				            <td class="main">' . MB_TEXT_MBERRTXT . '</td>
				            <td class="main">' . $data['mb_ERRTXT'] . '</td>
				        </tr>';

		echo $html;

	}

	function get_error() {
		global $_GET;

		$error = array (
			'title' => MODULE_PAYMENT_AMONEYBOOKERS_TEXT_ERROR,
			'error' => stripslashes(urldecode($_GET['error']
		)));

		return $error;
	}

	function check() {
		if (!isset ($this->_check)) {
			$check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_AMONEYBOOKERS_STATUS'");
			$this->_check = xtc_db_num_rows($check_query);
		}
		return $this->_check;
	}

	function install() {


		$this->remove();
		

		xtc_db_query("CREATE TABLE payment_AMONEYBOOKERS_currencies (mb_currID char(3) NOT NULL default '',mb_currName varchar(255) NOT NULL default '',PRIMARY KEY  (mb_currID))");

		xtc_db_query("CREATE TABLE payment_AMONEYBOOKERS_countries (xtc_cID int(11) NOT NULL default '0',mb_cID char(3) NOT NULL default '',PRIMARY KEY  (xtc_cID))");
		//
		//
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (2, 'ALB')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (3, 'ALG')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (4, 'AME')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (5, 'AND')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (6, 'AGL')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (7, 'ANG')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (9, 'ANT')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (10, 'ARG')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (11, 'ARM')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (12, 'ARU')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (13, 'AUS')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (14, 'AUT')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (15, 'AZE')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (16, 'BMS')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (17, 'BAH')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (18, 'BAN')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (19, 'BAR')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (20, 'BLR')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (21, 'BGM')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (22, 'BEL')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (23, 'BEN')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (24, 'BER')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (26, 'BOL')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (27, 'BOS')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (28, 'BOT')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (30, 'BRA')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (32, 'BRU')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (33, 'BUL')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (34, 'BKF')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (35, 'BUR')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (36, 'CAM')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (37, 'CMR')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (38, 'CAN')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (39, 'CAP')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (40, 'CAY')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (41, 'CEN')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (42, 'CHA')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (43, 'CHL')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (44, 'CHN')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (47, 'COL')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (49, 'CON')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (51, 'COS')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (52, 'COT')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (53, 'CRO')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (54, 'CUB')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (55, 'CYP')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (56, 'CZE')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (57, 'DEN')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (58, 'DJI')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (59, 'DOM')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (60, 'DRP')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (62, 'ECU')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (64, 'EL_')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (65, 'EQU')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (66, 'ERI')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (67, 'EST')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (68, 'ETH')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (70, 'FAR')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (71, 'FIJ')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (72, 'FIN')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (73, 'FRA')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (75, 'FRE')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (78, 'GAB')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (79, 'GAM')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (80, 'GEO')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (81, 'GER')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (82, 'GHA')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (83, 'GIB')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (84, 'GRC')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (85, 'GRL')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (87, 'GDL')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (88, 'GUM')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (89, 'GUA')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (90, 'GUI')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (91, 'GBS')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (92, 'GUY')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (93, 'HAI')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (95, 'HON')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (96, 'HKG')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (97, 'HUN')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (98, 'ICE')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (99, 'IND')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (101, 'IRN')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (102, 'IRA')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (103, 'IRE')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (104, 'ISR')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (105, 'ITA')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (106, 'JAM')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (107, 'JAP')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (108, 'JOR')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (109, 'KAZ')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (110, 'KEN')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (112, 'SKO')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (113, 'KOR')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (114, 'KUW')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (115, 'KYR')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (116, 'LAO')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (117, 'LAT')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (141, 'MCO')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (119, 'LES')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (120, 'LIB')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (121, 'LBY')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (122, 'LIE')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (123, 'LIT')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (124, 'LUX')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (125, 'MAC')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (126, 'F.Y')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (127, 'MAD')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (128, 'MLW')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (129, 'MLS')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (130, 'MAL')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (131, 'MLI')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (132, 'MLT')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (134, 'MAR')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (135, 'MRT')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (136, 'MAU')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (138, 'MEX')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (140, 'MOL')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (142, 'MON')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (143, 'MTT')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (144, 'MOR')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (145, 'MOZ')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (76, 'PYF')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (147, 'NAM')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (149, 'NEP')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (150, 'NED')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (151, 'NET')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (152, 'CDN')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (153, 'NEW')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (154, 'NIC')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (155, 'NIG')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (69, 'FLK')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (160, 'NWY')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (161, 'OMA')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (162, 'PAK')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (164, 'PAN')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (165, 'PAP')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (166, 'PAR')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (167, 'PER')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (168, 'PHI')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (170, 'POL')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (171, 'POR')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (172, 'PUE')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (173, 'QAT')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (175, 'ROM')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (176, 'RUS')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (177, 'RWA')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (178, 'SKN')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (179, 'SLU')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (180, 'ST.')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (181, 'WES')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (182, 'SAN')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (183, 'SAO')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (184, 'SAU')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (185, 'SEN')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (186, 'SEY')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (187, 'SIE')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (188, 'SIN')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (189, 'SLO')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (190, 'SLV')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (191, 'SOL')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (192, 'SOM')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (193, 'SOU')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (195, 'SPA')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (196, 'SRI')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (199, 'SUD')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (200, 'SUR')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (202, 'SWA')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (203, 'SWE')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (204, 'SWI')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (205, 'SYR')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (206, 'TWN')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (207, 'TAJ')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (208, 'TAN')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (209, 'THA')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (210, 'TOG')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (212, 'TON')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (213, 'TRI')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (214, 'TUN')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (215, 'TUR')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (216, 'TKM')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (217, 'TCI')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (219, 'UGA')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (231, 'BRI')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (221, 'UAE')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (222, 'GBR')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (223, 'UNI')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (225, 'URU')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (226, 'UZB')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (227, 'VAN')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (229, 'VEN')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (230, 'VIE')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (232, 'US_')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (235, 'YEM')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (236, 'YUG')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (238, 'ZAM')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_countries VALUES (239, 'ZIM')");
		//
		//#
		//# Dumping data for table `payment_AMONEYBOOKERS_currencies`
		//#
		//
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_currencies VALUES ('AUD', 'Australian Dollar')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_currencies VALUES ('BGN', 'Bulgarian Lev')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_currencies VALUES ('CAD', 'Canadian Dollar')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_currencies VALUES ('CHF', 'Swiss Franc')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_currencies VALUES ('CZK', 'Czech Koruna')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_currencies VALUES ('DKK', 'Danish Krone')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_currencies VALUES ('EEK', 'Estonian Koruna')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_currencies VALUES ('EUR', 'Euro')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_currencies VALUES ('GBP', 'Pound Sterling')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_currencies VALUES ('HKD', 'Hong Kong Dollar')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_currencies VALUES ('HUF', 'Forint')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_currencies VALUES ('ILS', 'Shekel')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_currencies VALUES ('ISK', 'Iceland Krona')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_currencies VALUES ('JPY', 'Yen')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_currencies VALUES ('KRW', 'South-Korean Won')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_currencies VALUES ('LVL', 'Latvian Lat')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_currencies VALUES ('MYR', 'Malaysian Ringgit')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_currencies VALUES ('NOK', 'Norwegian Krone')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_currencies VALUES ('NZD', 'New Zealand Dollar')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_currencies VALUES ('PLN', 'Zloty')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_currencies VALUES ('SEK', 'Swedish Krona')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_currencies VALUES ('SGD', 'Singapore Dollar')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_currencies VALUES ('SKK', 'Slovak Koruna')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_currencies VALUES ('THB', 'Baht')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_currencies VALUES ('TWD', 'New Taiwan Dollar')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_currencies VALUES ('USD', 'US Dollar')");
		xtc_db_query("INSERT INTO payment_AMONEYBOOKERS_currencies VALUES ('ZAR', 'South-African Rand')");

		$result = xtc_db_query("SELECT mb_currID FROM payment_AMONEYBOOKERS_currencies");
			while (list ($currID) = xtc_db_fetch_row($result)) {
				$this->mbCurrencies[] = $currID;
			}

			$result = xtc_db_query("SELECT code FROM currencies");
			while (list ($currID) = xtc_db_fetch_row($result)) {
				$this->aCurrencies[] = $currID;
			}
			
						$this->defCurr = DEFAULT_CURRENCY;

			$this->defLang = DEFAULT_LANGUAGE;
			$this->defLang = strtoupper($this->defLang);
			if (!in_array($this->defLang, $this->mbLanguages)) {
				$this->defLang = "EN";
			}
			
			$mb_installed = false;
			//BOF - Hetfield - 2010-01-28 - replace mysql_list_tables with query SHOW TABLES -> PHP5.3 deprecated
			//$tables = mysql_list_tables(DB_DATABASE);
			$tables = xtc_db_query("SHOW TABLES LIKE 'payment_moneybookers'");			
			while ($checktables = xtc_db_fetch_array($tables, MYSQL_NUM)) {
				if ($checktables[0] == 'payment_moneybookers')  $mb_installed=true;
			}
			//EOF - Hetfield - 2010-01-28 - replace mysql_list_tables with query SHOW TABLES -> PHP5.3 deprecated

		if ($mb_installed==false) {
		xtc_db_query("CREATE TABLE payment_moneybookers (mb_TRID varchar(255) NOT NULL default '',mb_ERRNO smallint(3) unsigned NOT NULL default '0',mb_ERRTXT varchar(255) NOT NULL default '',mb_DATE datetime NOT NULL default '0000-00-00 00:00:00',mb_MBTID bigint(18) unsigned NOT NULL default '0',mb_STATUS tinyint(1) NOT NULL default '0',mb_ORDERID int(11) unsigned NOT NULL default '0',PRIMARY KEY  (mb_TRID))");
		}

		xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_AMONEYBOOKERS_STATUS', 'True',  '6', '0', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
		xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_AMONEYBOOKERS_EMAILID', '', '6', '1', now())");
		xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_AMONEYBOOKERS_PWD', '',  '6', '2', now())");
		xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_AMONEYBOOKERS_MERCHANTID', '', '6', '3', now())");
		xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_AMONEYBOOKERS_SORT_ORDER', '0',  '6', '4', now())");
		xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_AMONEYBOOKERS_CURRENCY', '" . $this->defCurr . "', '6', '5', 'xtc_cfg_select_option(" . $this->show_array($this->aCurrencies) . "), ', now())");
		xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_AMONEYBOOKERS_LANGUAGE', '" . $this->defLang . "', '6', '6', 'xtc_cfg_select_option(" . $this->show_array($this->mbLanguages) . "), ', now())");
		xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_PAYMENT_AMONEYBOOKERS_ZONE', '0',  '6', '7', 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(', now())");
		xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_AMONEYBOOKERS_ALLOWED', '', '6', '0', now())");
		xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_AMONEYBOOKERS_TMP_STATUS_ID', '0',  '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
		xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_AMONEYBOOKERS_PROCESSED_STATUS_ID', '0',  '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
		xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_AMONEYBOOKERS_PENDING_STATUS_ID', '0',  '6', '8', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
		xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_AMONEYBOOKERS_CANCELED_STATUS_ID', '0',  '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
		xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_AMONEYBOOKERS_ICONS', 'elv.jpg,giropay.gif,cc_visa.jpg,visa_electron.jpg,cc_mastercard.jpg,cc_amex.jpg,cc_diners.jpg,swift.jpg,cheque.jpg',  '6', '0', now())");

		// tables


	}

	function remove() {
		xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
		xtc_db_query("DROP TABLE if EXISTS payment_AMONEYBOOKERS_currencies");
		xtc_db_query("DROP TABLE if EXISTS payment_AMONEYBOOKERS_countries");
	}

	function keys() {
		return array (
			'MODULE_PAYMENT_AMONEYBOOKERS_STATUS',
			'MODULE_PAYMENT_AMONEYBOOKERS_EMAILID',
			'MODULE_PAYMENT_AMONEYBOOKERS_PWD',
			'MODULE_PAYMENT_AMONEYBOOKERS_MERCHANTID',
			'MODULE_PAYMENT_AMONEYBOOKERS_LANGUAGE',
			'MODULE_PAYMENT_AMONEYBOOKERS_CURRENCY',
			'MODULE_PAYMENT_AMONEYBOOKERS_PROCESSED_STATUS_ID',
			'MODULE_PAYMENT_AMONEYBOOKERS_PENDING_STATUS_ID',
			'MODULE_PAYMENT_AMONEYBOOKERS_CANCELED_STATUS_ID',
			'MODULE_PAYMENT_AMONEYBOOKERS_TMP_STATUS_ID',
			'MODULE_PAYMENT_AMONEYBOOKERS_ICONS',
			'MODULE_PAYMENT_AMONEYBOOKERS_SORT_ORDER',
			'MODULE_PAYMENT_AMONEYBOOKERS_ALLOWED',
			'MODULE_PAYMENT_AMONEYBOOKERS_ZONE'
		);
	}

	// If there is no moneybookers accepted currency configured with the shop
	// do not allow the moneybookers payment module installation
	function check_currency($availableCurr) {
		$foundCurr = false;
		foreach ($availableCurr as $currID) {
			if (in_array($currID, $this->mbCurrencies)) {
				$foundCurr = true;
			}
		}
		return $foundCurr;
	}

	// Parse the predefinied array to be 'module install' friendly
	// as it is used for select in the module's install() function
	function show_array($aArray) {
		$aFormatted = "array(";
		foreach ($aArray as $key => $sVal) {
			$aFormatted .= "\'$sVal\', ";
		}
		$aFormatted = substr($aFormatted, 0, strlen($aFormatted) - 2);
		return $aFormatted;
	}

	function generate_trid() {

		do {
			$trid = xtc_create_random_value(16, "digits");
			$trid = 'XTC' . $trid;
			$result = xtc_db_query("SELECT mb_TRID FROM payment_moneybookers WHERE mb_TRID = '".$trid."'");
		} while (xtc_db_num_rows($result));

		return $trid;

	}
}
?>