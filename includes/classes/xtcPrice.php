<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(currencies.php,v 1.15 2003/03/17); www.oscommerce.com
   (c) 2003 nextcommerce (currencies.php,v 1.9 2003/08/17); www.nextcommerce.org
   (c) 2006 XT-Commerce (xtcPrice.php 1316 2005-10-21)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------
   Modified by Gunnar Tillmann (August 2006)
   http://www.gunnart.de

   Everywhere a price is displayed you see any existing kind of discount in percent and
   in saved money in your chosen currency
   ---------------------------------------------------------------------------------------*/

/**
 * This class calculates and formates all prices within the shop frontend
 *
 */
class xtcPrice {
  var $currencies;

  /**
   * Class Constructor initialises all required values like currencies, tax classes, tax zones etc.
   *
   * @param String $currency
   * @param Integer $cGroup
   * @return xtcPrice
   */
  //function xtcPrice($currency, $cGroup) {
  function __construct($currency, $cGroup) {

    $this->currencies = array();
    $this->cStatus = array();
    $this->actualGroup = (int)$cGroup;
    $this->actualCurr = $currency;
    $this->TAX = array();
    $this->SHIPPING = array();
    $this->showFrom_Attributes = true;
    //BOF - DokuMan - 2010-10-28 - added missing content_type definition for $tax_address_query //web28 - 2012-04-17 FIX call by admin
    if (!defined('HTTP_CATALOG_SERVER') && isset($_SESSION['cart'])) {
      if (is_object($_SESSION['cart'])) {
        $this->content_type = $_SESSION['cart']->get_content_type();
      }
    }
    //EOF - DokuMan - 2010-10-28 - added missing content_type definition for $tax_address_query //web28 - 2012-04-17 FIX call by admin

    // select Currencies
    $currencies_query = xtDBquery("SELECT * FROM ".TABLE_CURRENCIES);
    while ($currencies = xtc_db_fetch_array($currencies_query, true)) {
      $this->currencies[$currencies['code']] = array (
        'title' => $currencies['title'],
        'symbol_left' => $currencies['symbol_left'],
        'symbol_right' => $currencies['symbol_right'],
        'decimal_point' => $currencies['decimal_point'],
        'thousands_point' => $currencies['thousands_point'],
        'decimal_places' => $currencies['decimal_places'],
        'value' => $currencies['value']
        );
    }
    //BOF - DokuMan - 2011-01-21 - Fix an issue when the currency in user's preference is not existing
    if (!isset($this->currencies[$this->actualCurr])) {
      $this->actualCurr = DEFAULT_CURRENCY;
    }
    //BOF - DokuMan - 2011-01-21 - Fix an issue when the currency in user's preference is not existing

    // select Customers Status data
    $customers_status_query = xtDBquery( "SELECT *
                                              FROM ".TABLE_CUSTOMERS_STATUS."
                                             WHERE customers_status_id = '".$this->actualGroup."'
                                               AND language_id = '".(int)$_SESSION['languages_id']."'");
    $customers_status_value = xtc_db_fetch_array($customers_status_query, true);
    $this->cStatus = array ('customers_status_id' => $this->actualGroup,
                            'customers_status_name' => $customers_status_value['customers_status_name'],
                            'customers_status_image' => $customers_status_value['customers_status_image'],
                            'customers_status_public' => $customers_status_value['customers_status_public'],
                            'customers_status_discount' => $customers_status_value['customers_status_discount'],
                            'customers_status_ot_discount_flag' => $customers_status_value['customers_status_ot_discount_flag'],
                            'customers_status_ot_discount' => $customers_status_value['customers_status_ot_discount'],
                            'customers_status_graduated_prices' => $customers_status_value['customers_status_graduated_prices'],
                            'customers_status_show_price' => $customers_status_value['customers_status_show_price'],
                            'customers_status_show_price_tax' => $customers_status_value['customers_status_show_price_tax'],
                            'customers_status_add_tax_ot' => $customers_status_value['customers_status_add_tax_ot'],
                            'customers_status_payment_unallowed' => $customers_status_value['customers_status_payment_unallowed'],
                            'customers_status_shipping_unallowed' => $customers_status_value['customers_status_shipping_unallowed'],
                            'customers_status_discount_attributes' => $customers_status_value['customers_status_discount_attributes'],
                            'customers_fsk18' => $customers_status_value['customers_fsk18'],
                            'customers_fsk18_display' => $customers_status_value['customers_fsk18_display']
                           );

    // prefetch tax rates for standard zone
    $zones_query = xtDBquery("SELECT tax_class_id as class FROM ".TABLE_TAX_CLASS);
    while ($zones_data = xtc_db_fetch_array($zones_query,true)) {
      // calculate tax based on shipping or deliverey country (for downloads)
      if (isset($_SESSION['billto']) && isset($_SESSION['sendto'])) {
        $tax_address_query = xtc_db_query("SELECT ab.entry_country_id,
                                                  ab.entry_zone_id
                                             FROM " . TABLE_ADDRESS_BOOK . " ab
                                        LEFT JOIN " . TABLE_ZONES . " z on (ab.entry_zone_id = z.zone_id)
                                            WHERE ab.customers_id = '" . $_SESSION['customer_id'] . "'
                                              AND ab.address_book_id = '" . ($this->content_type == 'virtual' ? $_SESSION['billto'] : $_SESSION['sendto']) . "'"); //DokuMan - leave content_type as it is
        $tax_address = xtc_db_fetch_array($tax_address_query);
        $this->TAX[$zones_data['class']] = xtc_get_tax_rate($zones_data['class'],$tax_address['entry_country_id'], $tax_address['entry_zone_id']);
      } else {
        $this->TAX[$zones_data['class']]=xtc_get_tax_rate($zones_data['class']);
      }
    }
  }

  /**
   * This function searchs the inividual price for a product using the product id $pID
   *
   * @param Integer $pID product id
   * @param Boolean $format Format the result?
   * @param Double $qty quantity
   * @param Integer $tax_class tax class id
   * @param Double $pPrice product price
   * @param Integer $vpeStatus vpe status
   * @param Integer $cedit_id customer specify tax conditions
   * @return String/Array Price (if format = true both plain and formatted)
   */
  function xtcGetPrice($pID, $format = true, $qty, $tax_class, $pPrice, $vpeStatus = 0, $cedit_id = 0) {

    // check if group is allowed to see prices
    if ($this->cStatus['customers_status_show_price'] == '0'){
      return $this->xtcShowNote($vpeStatus);
    }

    // get Tax rate
    if ($cedit_id != 0) {
      //BOC - web28 - 2012-04-07 - FIX edit orders in admin guest account
      if (defined('HTTP_CATALOG_SERVER')) {
        global $order;
        $cinfo = get_c_infos($order->customer['ID'], trim($order->delivery['country_iso_2']));
      } else {
        $cinfo = xtc_oe_customer_infos($cedit_id);
      }
      //EOC - web28 - 2012-04-07 - FIX edit orders in admin guest account
      $products_tax = xtc_get_tax_rate($tax_class, $cinfo['country_id'], $cinfo['zone_id']);
    } else {
      //BOF - DokuMan - 2010-08-23 - set undefined index
      //$products_tax = $this->TAX[$tax_class];
      $products_tax = isset($this->TAX[$tax_class]) ? $this->TAX[$tax_class] : 0;
      //EOF - DokuMan - 2010-08-23 - set undefined index
    }

    if ($this->cStatus['customers_status_show_price_tax'] == '0'){
      $products_tax = '';
    }

    // add taxes
    if ($pPrice == 0) {
      $pPrice = $this->getPprice($pID);
    }
    $pPrice = $this->xtcAddTax($pPrice, $products_tax);

    // BOF - Tomcraft - 2009-11-28 - Included xs:booster
    // xs:booster Auktionspreis pruefen
    if ($sPrice = $this->xtcCheckXTBAuction($pID)){
      return $this->xtcFormatSpecial($pID, $sPrice, $pPrice, $format, $vpeStatus);
    }
    // EOF - Tomcraft - 2009-11-28 - Included xs:booster

    // check specialprice
    if ($sPrice = $this->xtcCheckSpecial($pID)){
      return $this->xtcFormatSpecial($pID, $this->xtcAddTax($sPrice, $products_tax), $pPrice, $format, $vpeStatus);
    }

    // check graduated
    if ($this->cStatus['customers_status_graduated_prices'] == '1') {
      if ($sPrice = $this->xtcGetGraduatedPrice($pID, $qty)){
        return $this->xtcFormatSpecialGraduated($pID, $this->xtcAddTax($sPrice, $products_tax), $pPrice, $format, $vpeStatus, $pID);
      }
    } else {
      // check Group Price
      if ($sPrice = $this->xtcGetGroupPrice($pID, 1)){
        return $this->xtcFormatSpecialGraduated($pID, $this->xtcAddTax($sPrice, $products_tax), $pPrice, $format, $vpeStatus, $pID);
      }
    }

    // check Product Discount
    if ($discount = $this->xtcCheckDiscount($pID)){
      return $this->xtcFormatSpecialDiscount($pID, $discount, $pPrice, $format, $vpeStatus);
    }
    return $this->xtcFormat($pPrice, $format, 0, false, $vpeStatus, $pID);
  }

  /**
   * This function returns the reqular price of a product,
   * no mather if its a special offer or has graduated prices
   *
   * @param Integer $pID product id
   * @return Double price
   */
  function getPprice($pID) {
    $pQuery = xtDBquery("SELECT products_price FROM ".TABLE_PRODUCTS." WHERE products_id='".$pID."'");
    $pData = xtc_db_fetch_array($pQuery, true);
    return $pData['products_price'];
  }

  /**
   * Adding a tax percentage to a price
   * This function also converts the price with currency factor,
   * so take care to avoid double conversions!
   *
   * @param Double $price net price
   * @param Double $tax tax value(%)
   * @return Double gross price
   */
  function xtcAddTax($price, $tax) {
    $price += $price / 100 * $tax;
    $price = $this->xtcCalculateCurr($price);
    return round($price, $this->currencies[$this->actualCurr]['decimal_places']);
  }

  // BOF - Tomcraft - 2009-11-28 - Included xs:booster
  // xs:booster start (v1.041)
  function xtcCheckXTBAuction($pID){
    if(($pos=strpos($pID,"{"))) {
      $pID=substr($pID,0,$pos);
    }
    //BOF - DokuMan - 2010-08-23 - suppress error php-notice on undefined index xtb0
    //if(!is_array($_SESSION['xtb0']['tx'])) return false;
    if(@!is_array($_SESSION['xtb0']['tx'])) {
      return false;
    }
    //EOF - DokuMan - 2010-08-23 - suppress error php-notice on undefined index xtb0
    foreach($_SESSION['xtb0']['tx'] as $tx) {
      if($tx['products_id']==$pID&&$tx['XTB_QUANTITYPURCHASED']!=0) {
        $this->actualCurr=$tx['XTB_AMOUNTPAID_CURRENCY'];
        return round($tx['XTB_AMOUNTPAID'], $this->currencies[$this->actualCurr]['decimal_places']);
      }
    }
    return false;
  }
  // xs:booster end
  // EOF - Tomcraft - 2009-11-28 - Included xs:booster

  /**
   * Returns the product specific discount
   *
   * @param Integer $pID product id
   * @return Mixed boolean false if not found or 0.00, double if found and > 0.00
   */
  function xtcCheckDiscount($pID) {
    // check if group got discount
    if ($this->cStatus['customers_status_discount'] != '0.00') {
      $discount_query = xtDBquery("SELECT products_discount_allowed FROM ".TABLE_PRODUCTS." WHERE products_id = '".$pID."'");
      $dData = xtc_db_fetch_array($discount_query, true);

      $discount = $dData['products_discount_allowed'];
      if ($this->cStatus['customers_status_discount'] < $discount){
        $discount = $this->cStatus['customers_status_discount'];
      }
      if ($discount == '0.00'){
        return false;
      }
      return $discount;
    }
    return false;
  }

  /**
   * Searches the graduated price of a product for a specified quantity
   *
   * @param Integer $pID product id
   * @param Double $qty quantity
   * @return Double graduated price
   */
  function xtcGetGraduatedPrice($pID, $qty) {
    if (defined('GRADUATED_ASSIGN') && GRADUATED_ASSIGN == 'true') {
      if (xtc_get_qty($pID) > $qty) {
        $qty = xtc_get_qty($pID);
      }
    }

    if (empty($this->actualGroup)) {
      $this->actualGroup = DEFAULT_CUSTOMERS_STATUS_ID_GUEST;
    }

    $graduated_price_query = xtDBquery("SELECT max(quantity) AS qty
                                          FROM ".TABLE_PERSONAL_OFFERS_BY.$this->actualGroup."
                                         WHERE products_id='".$pID."'
                                           AND quantity<='".$qty."'");
    $graduated_price_data = xtc_db_fetch_array($graduated_price_query, true);
    if ($graduated_price_data['qty']) {
      $graduated_price_query = xtDBquery("SELECT personal_offer
                                            FROM ".TABLE_PERSONAL_OFFERS_BY.$this->actualGroup."
                                           WHERE products_id='".$pID."'
                                             AND quantity='".$graduated_price_data['qty']."'");
      $graduated_price_data = xtc_db_fetch_array($graduated_price_query, true);

      $sPrice = $graduated_price_data['personal_offer'];
      if ($sPrice != 0.00){
        return $sPrice;
      }
    } else {
      return;
    }
  }

  /**
   * Searches the group price of a product
   *
   * @param Integer $pID product id
   * @param Double $qty quantity
   * @return Double group price
   */
  function xtcGetGroupPrice($pID, $qty) {
    $graduated_price_query = xtDBquery("SELECT max(quantity) AS qty
                                          FROM ".TABLE_PERSONAL_OFFERS_BY.$this->actualGroup."
                                         WHERE products_id='".$pID."'
                                           AND quantity<='".$qty."'");
    $graduated_price_data = xtc_db_fetch_array($graduated_price_query, true);

    if ($graduated_price_data['qty']) {
      $graduated_price_query = xtDBquery("SELECT personal_offer
                                            FROM ".TABLE_PERSONAL_OFFERS_BY.$this->actualGroup."
                                           WHERE products_id='".$pID."'
                                             AND quantity='".$graduated_price_data['qty']."'");
      $graduated_price_data = xtc_db_fetch_array($graduated_price_query, true);

      $sPrice = $graduated_price_data['personal_offer'];
      if ($sPrice != 0.00)
        return $sPrice;
    } else {
      return;
    }

  }
  /**
   * Returns the option price of a selected option
   *
   * @param Integer $pID product id
   * @param Integer $option option id
   * @param Integer $value value id
   * @return Double option price
   */
  function xtcGetOptionPrice($pID, $option, $value) {
    $price = 0; //DokuMan - set variable $price
    $attribute_price_query = xtDBquery("SELECT pd.products_discount_allowed,
                                               pd.products_tax_class_id,
                                               p.options_values_price,
                                               p.price_prefix,
                                               p.options_values_weight,
                                               p.weight_prefix
                                          FROM ".TABLE_PRODUCTS_ATTRIBUTES." p,
                                               ".TABLE_PRODUCTS." pd
                                         WHERE p.products_id = '".$pID."'
                                           AND p.options_id = '".$option."'
                                           AND pd.products_id = p.products_id
                                           AND p.options_values_id = '".$value."'");
    $attribute_price_data = xtc_db_fetch_array($attribute_price_query, true);
    $discount = 0;
    if ($this->cStatus['customers_status_discount_attributes'] == 1 && $this->cStatus['customers_status_discount'] != 0.00) {
      $discount = $this->cStatus['customers_status_discount'];
      if ($attribute_price_data['products_discount_allowed'] < $this->cStatus['customers_status_discount']){
        $discount = $attribute_price_data['products_discount_allowed'];
      }
    }
    //BOC web28 -2012-05-31 several currencies on product attributes
    $CalculateCurr = ($attribute_price_data['products_tax_class_id'] == 0) ? true : false;
    $price = $this->xtcFormat($attribute_price_data['options_values_price'], false, $attribute_price_data['products_tax_class_id'],$CalculateCurr);
    //EOC web28 -2012-05-31 several currencies on product attributes
    if ($attribute_price_data['weight_prefix'] != '+'){
      $attribute_price_data['options_values_weight'] *= -1;
    }
    if ($attribute_price_data['price_prefix'] == '+') {
      $price = $price - $price / 100 * $discount;
    } else {
      $price *= -1;
    }
    return array ('weight' => $attribute_price_data['options_values_weight'], 'price' => $price);
  }

  /**
   * Returns the text info for customers, whose customer group isn't allowed to see prices
   *
   * @param Integer $vpeStatus
   * @param Boolean $format
   * @return String / Array of String
   */
  function xtcShowNote($vpeStatus = 0) {
    if ($vpeStatus == 1) {
      return array ('formated' => NOT_ALLOWED_TO_SEE_PRICES, 'plain' => 0);
    } else {
      return NOT_ALLOWED_TO_SEE_PRICES;
    }
  }

  /**
   * Returns the special offer price of a product
   *
   * @param Integer $pID product id
   * @return Double special offer
   */
  function xtcCheckSpecial($pID) {
    $product_query = xtDBquery("select specials_new_products_price
                                from ".TABLE_SPECIALS."
                                where products_id = '".$pID."'
                                and status=1");
    $product = xtc_db_fetch_array($product_query, true);
    return $product['specials_new_products_price'];
  }

  /**
   * Converts the price with the currency factor
   *
   * @param Double $price
   * @return Double converted price
   */
  function xtcCalculateCurr($price) {
    return $this->currencies[$this->actualCurr]['value'] * $price;
  }

  /**
   * Returns the tax part of a net price
   *
   * @param Double $price price
   * @param Double $tax tax value
   * @return Double tax part
   */
  function calcTax($price, $tax) {
    return $price * $tax / 100;
  }

  /**
   * Removes the currency factor of a price
   *
   * @param Double $price
   * @return Double
   */
  function xtcRemoveCurr($price) {
    // check if used Curr != DEFAULT curr
    if (DEFAULT_CURRENCY != $this->actualCurr) {
      //BOF- web28 - 2011-04-19 - FIX for division by zero
      if ($this->currencies[$this->actualCurr]['value'] > 0) {
        return $price * (1 / $this->currencies[$this->actualCurr]['value']);
      }
      //EOF- web28 - 2011-04-19 - FIX for division by zero
    } else {
      return $price;
    }
  }

  /**
   * Removes the tax from a price, e.g. to calculate a net price from gross price
   *
   * @param Double $price price
   * @param Double $tax tax value
   * @return Double net price
   */
  function xtcRemoveTax($price, $tax) {
    $price = ($price / (($tax +100) / 100));
    return $price;
  }

  /**
   * Returns the tax part of a gross price
   *
   * @param Double $price price
   * @param Double $tax tax value
   * @return Double tax part
   */
  function xtcGetTax($price, $tax) {
    $tax = $price - $this->xtcRemoveTax($price, $tax);
    return $tax;
  }

  /**
   * Removes the discount part of a price
   *
   * @param Double $price price
   * @param Double $dc discount
   * @return Double discount part
   */
  function xtcRemoveDC($price,$dc) {
    $price = $price - ($price/100*$dc);
    return $price;
  }

  /**
   * Returns the discount part of a price
   *
   * @param Double $price price
   * @param Double $dc discount
   * @return Double discount part
   */
  function xtcGetDC($price,$dc) {
    $dc = $price/100*$dc;
    return $dc;
  }

  /**
   * Check if the product has attributes which can modify the price
   * If so, it returns a prefix ' from '
   *
   * @param Integer $pID product id
   * @return String
   */
  function checkAttributes($pID) {
    if (!$this->showFrom_Attributes || $pID == 0) {
      return;
    }
    // BOF - Tomcraft - 2009-10-09 - Bugfix: Don't show "from" in front of price, when all priceoptions are "0".
    //$products_attributes = xtDBquery("select count(*) as total from ".TABLE_PRODUCTS_OPTIONS." popt, ".TABLE_PRODUCTS_ATTRIBUTES." patrib where patrib.products_id='".$pID."' and patrib.options_id = popt.products_options_id and popt.language_id = '".(int) $_SESSION['languages_id']."'");
    // EOF - Tomcraft - 2009-10-09 - Bugfix: Don't show "from" in front of price, when all priceoptions are "0".
    $products_attributes = xtDBquery("select count(*) as total
                                      from ".TABLE_PRODUCTS_OPTIONS." popt,
                                           ".TABLE_PRODUCTS_ATTRIBUTES." patrib
                                      where patrib.products_id='".$pID."'
                                      and patrib.options_id = popt.products_options_id
                                      and popt.language_id = '".(int) $_SESSION['languages_id']."'
                                      and patrib.options_values_price > 0");
    $products_attributes = xtc_db_fetch_array($products_attributes, true);
    if ($products_attributes['total'] > 0) {
      return ' '.strtolower(FROM).' ';
    }
  }

  /**
   * xtcCalculateCurrEx
   *
   * @param double $price
   * @param string $curr
   * @return double
   */
  function xtcCalculateCurrEx($price, $curr) {
    return $price * ($this->currencies[$curr]['value'] / $this->currencies[$this->actualCurr]['value']);
  }

  /**
   * xtcFormat
   *
   * @param double $price
   * @param boolean $format
   * @param integer $tax_class
   * @param boolean $curr
   * @param integer $vpeStatus
   * @param integer $pID
   * @param integer $decimal_places
   * @return unknown
   */
	function xtcFormat($price, $format, $tax_class = 0, $curr = false, $vpeStatus = 0, $pID = 0, $decimal_places = 0) {
		if ($curr) {
			$price = $this->xtcCalculateCurr($price);
    }
		if ($tax_class != 0) {
			$products_tax = ($this->cStatus['customers_status_show_price_tax'] == '0') ? '' : $this->TAX[$tax_class];
			$price = $this->xtcAddTax($price, $products_tax);
		} 
    $decimal_places = ($decimal_places > 0) ? $decimal_places : $this->currencies[$this->actualCurr]['decimal_places'];
		if ($format) {
			$Pprice = number_format(floatval($price), $decimal_places, $this->currencies[$this->actualCurr]['decimal_point'], $this->currencies[$this->actualCurr]['thousands_point']);
			$Pprice = $this->checkAttributes($pID).$this->currencies[$this->actualCurr]['symbol_left'].' '.$Pprice.' '.$this->currencies[$this->actualCurr]['symbol_right'];
			if ($vpeStatus == 0) {
				return $Pprice;
			} else {
				return array ('formated' => $Pprice, 'plain' => $price);
			}
		} else {
			return round($price, $decimal_places);
		}
	}

  /**
   * xtcFormatSpecialDiscount
   *
   * @param integer $pID
   * @param unknown_type $discount
   * @param double $pPrice
   * @param boolean $format
   * @param integer $vpeStatus
   * @return unknown
   */
  function xtcFormatSpecialDiscount($pID, $discount, $pPrice, $format, $vpeStatus = 0) {

    $sPrice = $pPrice - ($pPrice / 100) * $discount;
    if ($format) {
      //BOF - Dokuman - 2009-06-03 - show 'ab' / 'from' for the lowest price, not for the highest!
      //$price = '<span class="productOldPrice">'.INSTEAD.$this->xtcFormat($pPrice, $format).'</span><br />'.ONLY.$this->checkAttributes($pID).$this->xtcFormat($sPrice, $format).'<br />'.YOU_SAVE.$discount.'%';
      $price = '<span class="productOldPrice"><small>'.INSTEAD.'</small><del>'.$this->xtcFormat($pPrice, $format).'</del></span><br />'.ONLY.$this->checkAttributes($pID).$this->xtcFormat($sPrice, $format).'<br /><small>'.YOU_SAVE.round(($pPrice-$sPrice) / $pPrice * 100).' % /'.$this->xtcFormat($pPrice-$sPrice, $format);
      // Ausgabe des gültigen Kundengruppen-Rabatts (sofern vorhanden)
      if ($discount != 0)
          { $price .= '<br />'.BOX_LOGINBOX_DISCOUNT.': '.round($discount).' %'; }
        $price .= '</small>';
      //EOF - Dokuman - 2009-06-03 - show 'ab' / 'from' for the lowest price, not for the highest!
      if ($vpeStatus == 0) {
        return $price;
      } else {
        return array ('formated' => $price, 'plain' => $sPrice);
      }
    } else {
      return round($sPrice, $this->currencies[$this->actualCurr]['decimal_places']);
    }
  }

  /**
   * xtcFormatSpecial
   *
   * @param integer $pID
   * @param double $sPrice
   * @param double $pPrice
   * @param bpplean $format
   * @param integer $vpeStatus
   * @return unknown
   */
  function xtcFormatSpecial($pID, $sPrice, $pPrice, $format, $vpeStatus = 0) {

    if ($format) {
      //BOF - Dokuman - 2009-06-03 - show 'ab' / 'from' for the lowest price, not for the highest!
      //$price = '<span class="productOldPrice">'.INSTEAD.$this->xtcFormat($pPrice, $format).'</span><br />'.ONLY.$this->checkAttributes($pID).$this->xtcFormat($sPrice, $format);
      //BOF - vr - 2009-12-11 avoid div / 0 if product price is 0
      if (!isset($pPrice) || $pPrice == 0) {
        $discount = 0;
      } else {
        $discount = ($pPrice - $sPrice) / $pPrice * 100;
      }
      $price = '<span class="productOldPrice"><small>'.INSTEAD.'</small><del>'.$this->xtcFormat($pPrice, $format).'</del></span><br />'.ONLY.$this->checkAttributes($pID).$this->xtcFormat($sPrice, $format).'<br /><small>'.YOU_SAVE.round($discount).' % /'.$this->xtcFormat($pPrice-$sPrice, $format).'</small>';
      //EOF - vr - 2009-12-11 avoid div / 0 if product price is 0
      //EOF - Dokuman - 2009-06-03 - show 'ab' / 'from' for the lowest price, not for the highest!
      if ($vpeStatus == 0) {
        return $price;
      } else {
        return array ('formated' => $price, 'plain' => $sPrice);
      }
    } else {
      return round($sPrice, $this->currencies[$this->actualCurr]['decimal_places']);
    }
  }

  /**
   * xtcFormatSpecialGraduated
   *
   * @param integer $pID
   * @param double $sPrice
   * @param double $pPrice
   * @param boolean $format
   * @param integer $vpeStatus
   * @param integer $pID
   * @return unknown
   */
  function xtcFormatSpecialGraduated($pID, $sPrice, $pPrice, $format, $vpeStatus = 0, $pID) {
    //BOF - Dokuman - 2009-06-03 - show 'ab' / 'from' for the lowest price, not for the highest! - NEU HINZUGEFÜGT "Steuerklasse ermitteln"
    $tQuery = xtc_db_query("SELECT products_tax_class_id FROM ".TABLE_PRODUCTS." WHERE products_id='".$pID."'");
    $tQuery = xtc_db_fetch_array($tQuery);
    $tax_class = $tQuery['products_tax_class_id'];
    //EOF - Dokuman - 2009-06-03 - show 'ab' / 'from' for the lowest price, not for the highest! - ENDE "Steuerklasse ermitteln"
    if ($pPrice == 0) {
      return $this->xtcFormat($sPrice, $format, 0, false, $vpeStatus);
    }
    if ($discount = $this->xtcCheckDiscount($pID)) {
      $sPrice -= $sPrice / 100 * $discount;
    }
    if ($format) {
      //BOF - Dokuman - 2009-06-03 - show 'ab' / 'from' for the lowest price, not for the highest!
      $sQuery = xtDBquery("SELECT max(quantity) AS qty
                             FROM ".TABLE_PERSONAL_OFFERS_BY.$this->actualGroup."
                            WHERE products_id='".$pID."'");
      $sQuery = xtc_db_fetch_array($sQuery, true);
      // NEU! Damit "UVP"-Anzeige wieder möglich ist
      // if ( ($this->cStatus['customers_status_graduated_prices'] == '1') || ($sQuery['qty'] > 1) ) {
      if ( ($this->cStatus['customers_status_graduated_prices'] == '1') && ($sQuery['qty'] > 1) ) {
        $bestPrice = $this->xtcGetGraduatedPrice($pID, $sQuery['qty']);
        if ($discount) {
          $bestPrice -= $bestPrice / 100 * $discount;
        }
        $price = FROM.$this->xtcFormat($bestPrice, $format, $tax_class)
          .' <br /><small>' . UNIT_PRICE
          .$this->xtcFormat($sPrice, $format)
          .'</small>';
      } else if ($sPrice != $pPrice) { // if ($sPrice != $pPrice) {
        $price = '<span class="productOldPrice">'.MSRP.' '.$this->xtcFormat($pPrice, $format).'</span><br />'.YOUR_PRICE.$this->checkAttributes($pID).$this->xtcFormat($sPrice, $format);
      //EOF - Dokuman - 2009-06-03 - show 'ab' / 'from' for the lowest price, not for the highest!
      } else {
        $price = $this->xtcFormat($sPrice, $format);
      }

      if ($vpeStatus == 0) {
        return $price;
      } else {
        return array ('formated' => $price, 'plain' => $sPrice);
      }
    } else {
      return round($sPrice, $this->currencies[$this->actualCurr]['decimal_places']);
    }
  }

  /**
   * get_decimal_places
   *
   * @param unknown_type $code
   * @return unknown
   */
  function get_decimal_places($code) {
    return $this->currencies[$this->actualCurr]['decimal_places'];
  }

}
?>