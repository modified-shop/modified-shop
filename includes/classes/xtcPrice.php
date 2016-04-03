<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtcPrice.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

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
   modified by:
   2006 - Gunnar Tillmann - http://www.gunnart.de
   
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
   * Constructor initialises all required values like currencies, tax classes, tax zones etc.
   *
   * @param String $currency
   * @param Integer $cGroup
   * @return xtcPrice
   */
  function __construct($currency, $cGroup) {

    //new module support
    require_once (DIR_FS_CATALOG.'includes/classes/xtcPriceModules.class.php');
    $this->priceModules = new priceModules();
    
    $this->currencies = array();
    $this->cStatus = array();
    $this->actualGroup = (int) $cGroup;
    $this->actualCurr = $currency;
    $this->TAX = array();
    $this->SHIPPING = array();
    $this->showFrom_Attributes = true;
    
    $this->show_price_tax = 0;

    if (!defined('HTTP_CATALOG_SERVER') && isset($_SESSION['cart'])) {
      if (is_object($_SESSION['cart'])) {
        $this->content_type = $_SESSION['cart']->get_content_type();
      }
    }

    // select Currencies
    $currencies_query = xtDBquery("SELECT * FROM " . TABLE_CURRENCIES . " WHERE status = '1'");
    while ($currencies = xtc_db_fetch_array($currencies_query, true)) {
      // direct array assignment
      $this->currencies[$currencies['code']] = $currencies;
    }
    // if the currency in user's preference is not existing use default
    if (!isset($this->currencies[$this->actualCurr])) {
      $this->actualCurr = DEFAULT_CURRENCY;
    }

    // select Customers Status data
    $customers_status_query = xtDBquery("SELECT *
                                           FROM " . TABLE_CUSTOMERS_STATUS . "
                                          WHERE customers_status_id = '" . $this->actualGroup . "'
                                            AND language_id = '" . (int) $_SESSION['languages_id'] . "'");
    // direct array assignment
    $this->cStatus = xtc_db_fetch_array($customers_status_query, true);    
    
    // prefetch tax rates for standard zone
    $zones_query = xtDBquery("SELECT tax_class_id as class FROM " . TABLE_TAX_CLASS);
    while ($zones_data = xtc_db_fetch_array($zones_query, true)) {
      // calculate tax based on shipping or deliverey country (for downloads)
      if (isset($_SESSION['billto']) && isset($_SESSION['sendto'])) {
        $tax_address_query = xtc_db_query("SELECT ab.entry_country_id,
                                                  ab.entry_zone_id
                                             FROM " . TABLE_ADDRESS_BOOK . " ab
                                        LEFT JOIN " . TABLE_ZONES . " z on (ab.entry_zone_id = z.zone_id)
                                            WHERE ab.customers_id = '" . $_SESSION['customer_id'] . "'
                                              AND ab.address_book_id = '" . ($this->content_type == 'virtual' ? $_SESSION['billto'] : $_SESSION['sendto']) . "'");
        $tax_address = xtc_db_fetch_array($tax_address_query);
        $this->TAX[$zones_data['class']] = xtc_get_tax_rate($zones_data['class'], $tax_address['entry_country_id'], $tax_address['entry_zone_id']);
      } else {
        // BOF VERSANDKOSTEN IM WARENKORB
        //$this->TAX[$zones_data['class']]=xtc_get_tax_rate($zones_data['class']);
        $country_id = -1;
        if (isset($_SESSION['country'])) { // && !isset($_SESSION['customer_id'])) {  //Steuerberechnung nach Versandland, auch bei eingeloggten Kunden
          $country_id = $_SESSION['country'];
        }
        $this->TAX[$zones_data['class']]= xtc_get_tax_rate($zones_data['class'], $country_id);        
        // EOF VERSANDKOSTEN IM WARENKORB
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
    if ($this->cStatus['customers_status_show_price'] == '0') {
      return $this->xtcShowNote($vpeStatus);
    }
    
    $this->show_price_tax = ($tax_class == '') ? 0 : $this->cStatus['customers_status_show_price_tax'];
 
    // get Tax rate
    if ($cedit_id != 0) {
      if (defined('HTTP_CATALOG_SERVER')) {
        global $order; // edit orders in admin guest account
        $cinfo = get_c_infos($order->customer['ID'], trim($order->delivery['country_iso_2']));
      } else {
        $cinfo = xtc_oe_customer_infos($cedit_id);
      }
      if ($this->cStatus['customers_status_show_price_tax'] == 1
          && $this->cStatus['customers_status_add_tax_ot'] == 0
          && $this->get_content_type_product($pID) == 'virtual'
          ) 
      {
        $tax_class = xtc_get_tax_class($tax_class, $cinfo['country_id'], $cinfo['zone_id']);
      }
      $products_tax = xtc_get_tax_rate($tax_class, $cinfo['country_id'], $cinfo['zone_id']);
    } else {
      if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 1
          && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 0
          && $this->get_content_type_product($pID) == 'virtual'
          ) 
      {
        $tax_class = xtc_get_tax_class($tax_class);
      }
      $products_tax = isset($this->TAX[$tax_class]) ? $this->TAX[$tax_class] : 0;
    }
    
    if ($this->cStatus['customers_status_show_price_tax'] == '0') {
      $products_tax = '';
    }
    
    // add taxes
    if ($pPrice == 0) {
      $pPrice = $this->getPprice($pID);
    }
    $pPrice = $this->xtcAddTax($pPrice, $products_tax);

    // xs:booster check bid price
    if ($sPrice = $this->xtcCheckXTBAuction($pID)) {
      return $this->xtcFormatSpecial($pID, $sPrice, $pPrice, $format, $vpeStatus);
    }
    
    // check specialprice
    if ($sPrice = $this->xtcCheckSpecial($pID)) {
      return $this->xtcFormatSpecial($pID, $this->xtcAddTax($sPrice, $products_tax), $pPrice, $format, $vpeStatus);
    }
    
    // check graduated
    if ($this->cStatus['customers_status_graduated_prices'] == '1') {
      if ($sPrice = $this->xtcGetGraduatedPrice($pID, $qty)) {
        return $this->xtcFormatSpecialGraduated($pID, $this->xtcAddTax($sPrice, $products_tax), $pPrice, $format, $vpeStatus, $tax_class);
      }
    } else {
      // check Group Price
      if ($sPrice = $this->xtcGetGroupPrice($pID, 1)) {
        return $this->xtcFormatSpecialGraduated($pID, $this->xtcAddTax($sPrice, $products_tax), $pPrice, $format, $vpeStatus, $tax_class);
      }
    }

    // check Product Discount
    if ($discount = $this->xtcCheckDiscount($pID)) {
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
    return $this->show_price_tax ? round($price, $this->currencies[$this->actualCurr]['decimal_places']) : $price;
  }

  /**
   * xs:booster (v1.041, 2009-11-28)
   *
   * @param Integer $pID product id
   * @return Mixed
   */
  function xtcCheckXTBAuction($pID) {
    $pID = xtc_get_prid($pID);
    if (!isset($_SESSION['xtb0']) && !isset($_SESSION['xtb0']['tx'])) {
      return false;
    }
    for ($i=0, $n=sizeof($_SESSION['xtb0']['tx']); $i<$n; $i++) {
      if ($_SESSION['xtb0']['tx'][$i]['products_id'] == $pID && $_SESSION['xtb0']['tx'][$i]['XTB_QUANTITYPURCHASED'] != 0) {
        $this->actualCurr = $_SESSION['xtb0']['tx'][$i]['XTB_AMOUNTPAID_CURRENCY'];
        return round($_SESSION['xtb0']['tx'][$i]['XTB_AMOUNTPAID'], $this->currencies[$this->actualCurr]['decimal_places']);
      }
    }
    return false;
  }
  
  /**
   * Returns the product sepcific discount
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
      if ($this->cStatus['customers_status_discount'] < $discount) {
        $discount = $this->cStatus['customers_status_discount'];
      }
      if ($discount == '0.00') {
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
  function xtcGetGraduatedPrice($pID, $qty, $graduated = true) {
    if (defined('GRADUATED_ASSIGN') && GRADUATED_ASSIGN == 'true' && $graduated === true) {
      $actual_content_qty = xtc_get_qty($pID);
      $qty = $actual_content_qty > $qty ? $actual_content_qty : $qty;
    }
    
    if (empty($this->actualGroup)) {
      $this->actualGroup = DEFAULT_CUSTOMERS_STATUS_ID_GUEST;
    }
    
    $graduated_price_query = xtDBquery("SELECT max(quantity) AS qty
                                          FROM " . TABLE_PERSONAL_OFFERS_BY . $this->actualGroup . "
                                         WHERE products_id = '" . $pID . "'
                                           AND quantity <= '" . $qty . "'");
    if (xtc_db_num_rows($graduated_price_query, true) > 0) {
      $graduated_price_data  = xtc_db_fetch_array($graduated_price_query, true);

      if ($graduated_price_data['qty'] > 0) {
        $graduated_price_query = xtDBquery("SELECT personal_offer
                                              FROM " . TABLE_PERSONAL_OFFERS_BY . $this->actualGroup . "
                                             WHERE products_id = '" . $pID . "'
                                               AND quantity = '" . $graduated_price_data['qty'] . "'");
        $graduated_price_data  = xtc_db_fetch_array($graduated_price_query, true);
        $sPrice = $graduated_price_data['personal_offer'];

        if ($sPrice != 0.00) {
          return $sPrice;
        }
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
    return $this->xtcGetGraduatedPrice($pID, $qty, false);
  }

  /**
   * Returns the option price of a selected option
   *
   * @param Integer $pID product id
   * @param Integer $option option id
   * @param Integer $value value id
   * @return Double option price
   */
  function xtcGetOptionPrice($pID, $option, $value, $qty = 1) {
    $price = $discount = $attributes_weight = 0;
    
    $dataArr = array(
        'weight' => 0,
        'price' => 0,
        'discount' => 0,
        'qty' => $qty
      );
      
    $attribute_query = xtDBquery(
       "SELECT p.products_discount_allowed,
               p.products_tax_class_id,
               p.products_price,
               p.products_weight,
               pa.options_values_price,
               pa.price_prefix,
               pa.options_values_weight,
               pa.weight_prefix
          FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
          JOIN " . TABLE_PRODUCTS . " p
               ON p.products_id = pa.products_id
         WHERE pa.products_id = '" . (int)$pID . "'
           AND pa.options_id = '" . (int)$option . "'
           AND pa.options_values_id = '" . (int)$value . "'
       ");
    
    if (xtc_db_num_rows($attribute_query, true) > 0) {
      $attribute_data  = xtc_db_fetch_array($attribute_query, true);
      
      // calculate weight
      $attributes_weight = $attribute_data['options_values_weight'];
      if ($attribute_data['weight_prefix'] != '+') {
        $attributes_weight *= -1;
      }
      
      // calculate discount
      if ($this->cStatus['customers_status_discount_attributes'] == '1' && $this->cStatus['customers_status_discount'] != 0.00) {
        $discount = $this->cStatus['customers_status_discount'];
        if ($attribute_data['products_discount_allowed'] < $this->cStatus['customers_status_discount']) {
          $discount = $attribute_data['products_discount_allowed'];
        }
      }
      
      // calculate price and several currencies on product attributes
      $CalculateCurr = (($attribute_data['products_tax_class_id'] == 0) ? true : false);
      $price = $this->xtcFormat($attribute_data['options_values_price'], false, $attribute_data['products_tax_class_id'], $CalculateCurr);
      if ($attribute_data['price_prefix'] == '+') {
        $price = $price - ($price / 100 * $discount);
      } else {
        $price *= -1;
      }
    
      $dataArr = array(
        'weight' => $attributes_weight,
        'price' => $price,
        'discount' => $discount,
        'qty' => $qty
      );
      
      //new module support
      $dataArr = $this->priceModules->GetOptionPrice($dataArr,$attribute_data,$pID, $option, $value);
    }
    return $dataArr;
  }
  
  /**
   * Returns the text info for customers, whose customer group isn't allowed to see prices
   *
   * @param Integer $vpeStatus
   * @param Boolean $format
   * @return String / Array of String
   */
  function xtcShowNote($vpeStatus = 0) {
    if ($vpeStatus == 1)
      return array(
        'formated' => NOT_ALLOWED_TO_SEE_PRICES,
        'not_allowed' => NOT_ALLOWED_TO_SEE_PRICES,
        'plain' => 0,
        'from' =>  '',
        'flag' => 'NotAllowed'
      );
    return NOT_ALLOWED_TO_SEE_PRICES;
  }
  
  /**
   * Returns the special offer price of a product
   *
   * @param Integer $pID product id
   * @return Double special offer
   */
  function xtcCheckSpecial($pID) {
    if ($this->cStatus['customers_status_specials'] == '1') {
      $product_query = xtDBquery("SELECT specials_new_products_price
                                    FROM ".TABLE_SPECIALS."
                                   WHERE products_id = '".$pID."'
                                     AND status = 1
                                     AND (start_date IS NULL 
                                          OR start_date <= NOW())
                                 ");
      if (xtc_db_num_rows($product_query, true) > 0) {
        $product = xtc_db_fetch_array($product_query, true);
        return $product['specials_new_products_price'];
      }
    }
  }
  
  /**
   * Converts the price  with the currency factor
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
    if (DEFAULT_CURRENCY != $this->actualCurr) {
      if ($this->currencies[$this->actualCurr]['value'] > 0) {
        return $price * (1 / $this->currencies[$this->actualCurr]['value']);
      }
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
    $price = ($price / (($tax + 100) / 100));
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
  function xtcRemoveDC($price, $dc) {
    $price = $price - ($price / 100 * $dc);
    return $price;
  }
  
  /**
   * Returns the discount part of a price
   *
   * @param Double $price price
   * @param Double $dc discount
   * @return Double discount part
   */
  function xtcGetDC($price, $dc) {
    $dc = $price / 100 * $dc;
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
    if (!$this->showFrom_Attributes || $pID == 0) return;
    $products_attributes_query = "SELECT count(*) as total 
                                    FROM " . TABLE_PRODUCTS_OPTIONS . " popt,
                                         " . TABLE_PRODUCTS_ATTRIBUTES . " patrib
                                   WHERE patrib.products_id = '" . $pID . "'
                                     AND patrib.options_id = popt.products_options_id
                                     AND popt.language_id = '" . (int) $_SESSION['languages_id'] . "'
                                     AND patrib.options_values_price > 0";
    $products_attributes = xtDBquery($products_attributes_query);
    $products_attributes = xtc_db_fetch_array($products_attributes, true);
    if ($products_attributes['total'] > 0) {
      return ' ' . FROM . ' ';
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
    $from = $this->checkAttributes($pID);
    if ($format) {
      $sQuery = xtDBquery("SELECT max(po.quantity) AS qty,
                                  p.products_tax_class_id
                             FROM " . TABLE_PERSONAL_OFFERS_BY . $this->actualGroup . " po
                             JOIN " . TABLE_PRODUCTS . " p
                                  ON po.products_id = p.products_id
                            WHERE po.products_id='" . $pID . "'");
      $sQuery = xtc_db_fetch_array($sQuery, true);
      if (($this->cStatus['customers_status_graduated_prices'] == '1') && ($sQuery['qty'] > 1)) {
        $from = ' ' . FROM . ' ';
        $price = $this->xtcGetGraduatedPrice($pID, $sQuery['qty']);
        if ($curr) {
          $price = $this->xtcCalculateCurr($price);
        }
        if ($sQuery['products_tax_class_id'] != 0) {
          $products_tax = ($this->cStatus['customers_status_show_price_tax'] == '0') ? '' : $this->TAX[$sQuery['products_tax_class_id']];
          $price = $this->xtcAddTax($price, $products_tax);
        }
      }       
      $Pprice = number_format(floatval($price), $decimal_places, $this->currencies[$this->actualCurr]['decimal_point'], $this->currencies[$this->actualCurr]['thousands_point']);
      $Pprice = $this->currencies[$this->actualCurr]['symbol_left'] . ' ' . $Pprice . ' ' . $this->currencies[$this->actualCurr]['symbol_right'];
      if ($vpeStatus == 0) {
        return $from.$Pprice;
      } else {
        return array(
          'formated' => $from.$Pprice,
          'standard_price' => $Pprice,
          'plain' => $price,
          'from' =>  $from,
          'flag' => 'standard'
        );
      }
    } else {
      return $this->show_price_tax ? round($price, $decimal_places) : $price;
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
      $old_price = $this->xtcFormat($pPrice, $format);
      $special_price = $this->xtcFormat($sPrice, $format);
      $save_percent = round(($pPrice - $sPrice) / $pPrice * 100);
      $save_diff = $this->xtcFormat($pPrice - $sPrice, $format);
      $from = $this->checkAttributes($pID);
      $price = '<span class="productOldPrice"><small>' . INSTEAD . '</small><del>' . $old_price . '</del></span><br /><span class="productNewPrice">' . ONLY . $from . $special_price . '</span><br /><small class="productSavePrice">' . YOU_SAVE . $save_percent . ' % /' . $save_diff;
      if ($discount != 0) {
        // customer group discount
        $price .= '<br />' . BOX_LOGINBOX_DISCOUNT . ': ' . round($discount) . ' %';
      }
      $price .= '</small>';
      if ($vpeStatus == 0) {
        return $price;
      } else {
        return array(
          'formated' => $price,
          'plain' => $sPrice,
          'special_price' =>  $special_price,
          'old_price' =>  $old_price,
          'save_percent' =>  $save_percent,
          'save_diff' =>  $save_diff,
          'group_discount' => round($discount),
          'from' =>  $from,
          'flag' => 'SpecialDiscount'          
        );
      }
    } else {
      return $this->show_price_tax ? round($sPrice, $this->currencies[$this->actualCurr]['decimal_places']) : $sPrice;
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
      if (!isset($pPrice) || $pPrice == 0) {
        $discount = 0;
      } else {
        $discount = ($pPrice - $sPrice) / $pPrice * 100;
      }
      $old_price = $this->xtcFormat($pPrice, $format);
      $special_price = $this->xtcFormat($sPrice, $format);
      $save_percent = round($discount);
      $save_diff = $this->xtcFormat($pPrice - $sPrice, $format);
      $from = $this->checkAttributes($pID);
      $price = '<span class="productOldPrice"><small>' . INSTEAD . '</small><del>' . $old_price . '</del></span><br /><span class="productNewPrice">' . ONLY . $from . $special_price . '</span><br /><small class="productSavePrice">' . YOU_SAVE . $save_percent . ' % /' . $save_diff . '</small>';
      if ($vpeStatus == 0) {
        return $price;
      } else {
        return array(
          'formated' => $price,
          'plain' => $sPrice,
          'special_price' =>  $special_price,
          'old_price' =>  $old_price,
          'save_percent' =>  $save_percent,
          'save_diff' =>  $save_diff,
          'from' =>  $from,
          'flag' => 'Special' 
        );
      }
    } else {
      return $this->show_price_tax ? round($sPrice, $this->currencies[$this->actualCurr]['decimal_places']) : $sPrice;
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
  function xtcFormatSpecialGraduated($pID, $sPrice, $pPrice, $format, $vpeStatus = 0, $tax_class) {
    if ($pPrice == 0) {
      return $this->xtcFormat($sPrice, $format, 0, false, $vpeStatus);
    }
    if ($discount = $this->xtcCheckDiscount($pID)) {
      $sPrice -= $sPrice / 100 * $discount;
    }
    if ($format) {
      $sQuery = xtDBquery("SELECT max(quantity) AS qty
                             FROM " . TABLE_PERSONAL_OFFERS_BY . $this->actualGroup . "
                            WHERE products_id='" . $pID . "'");
      $sQuery = xtc_db_fetch_array($sQuery, true);
      $old_price = '';
      $special_price = '';
      $from = '';
      $uvp = '';
      if (($this->cStatus['customers_status_graduated_prices'] == '1') && ($sQuery['qty'] > 1)) {
        $bestPrice = $this->xtcGetGraduatedPrice($pID, $sQuery['qty']);
        if ($discount) {
          $bestPrice -= $bestPrice / 100 * $discount;
        }
        $old_price = $this->xtcFormat($bestPrice, $format, $tax_class);
        $special_price = $this->xtcFormat($sPrice, $format);
        $price = FROM . $old_price . ' <br /><small>' . UNIT_PRICE . $special_price . '</small>';
      } else if ($sPrice != $pPrice) {
        $old_price = $this->xtcFormat($pPrice, $format);
        $special_price = $this->xtcFormat($sPrice, $format);
        $from = $this->checkAttributes($pID);
        $uvp = MSRP;
        $price = '<span class="productOldPrice">' . $uvp . ' ' . $old_price . '</span><br />' . YOUR_PRICE . $from . $special_price;
      } else {
        return $this->xtcFormat($sPrice, $format, 0, false, $vpeStatus, $pID);
        //$price = $this->xtcFormat($sPrice, $format);
      }
      
      if ($vpeStatus == 0) {
        return $price;
      } else {
        return array(
          'formated' => $price,
          'plain' => $sPrice,
          'special_price' =>  $special_price,
          'old_price' =>  $old_price,
          'from' =>  $from,
          'uvp' =>  $uvp,
          'flag' => 'SpecialGraduated' 
        );
      }
    } else {
      return $this->show_price_tax ? round($sPrice, $this->currencies[$this->actualCurr]['decimal_places']) : $sPrice;
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

  /**
   * get_content_type_product
   *
   * @return unknown
   */
  function get_content_type_product($products_id) {
    $this->content_type_product = array(); 

    if (DOWNLOAD_ENABLED == 'true') {
      if (defined('DOWNLOAD_MULTIPLE_ATTRIBUTES_ALLOWED') && DOWNLOAD_MULTIPLE_ATTRIBUTES_ALLOWED == 'true') {
        // new routine for multiple attributes for downloads
        $virtual_check_query = xtc_db_query("SELECT pa.products_attributes_id
                                               FROM ".TABLE_PRODUCTS_ATTRIBUTES." pa
                                               JOIN ".TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD." pad
                                                    ON pa.products_attributes_id = pad.products_attributes_id
                                              WHERE pa.products_id = '".(int)$products_id."'");
        if (xtc_db_num_rows($virtual_check_query) > 0) {
          $this->content_type_product[$products_id] = 'virtual';
        } else {
          $this->content_type_product[$products_id] = 'physical';
        }
      } else {
        // old routine as standard
        $virtual_check_query1 = xtc_db_query("SELECT products_attributes_id
                                               FROM ".TABLE_PRODUCTS_ATTRIBUTES."
                                              WHERE products_id = '".(int)$products_id."'");
        $total_attributes = xtc_db_num_rows($virtual_check_query1);

        $virtual_check_query = xtc_db_query("SELECT pa.products_attributes_id
                                               FROM ".TABLE_PRODUCTS_ATTRIBUTES." pa
                                               JOIN ".TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD." pad
                                                    ON pa.products_attributes_id = pad.products_attributes_id
                                              WHERE pa.products_id = '".(int)$products_id."'
                                           GROUP BY pa.options_values_id");
        $total_virtual = xtc_db_num_rows($virtual_check_query);
        
        if ($total_virtual == 0) {
          $this->content_type_product[$products_id] = 'physical';
        } elseif ($total_attributes == $total_virtual) {
          $this->content_type_product[$products_id] = 'virtual';
        } elseif ($total_attributes > $total_virtual) {
          $this->content_type_product[$products_id] = 'mixed';
        }          
      }
    } else {
      $this->content_type_product[$products_id] = 'physical';
    }
    
    return $this->content_type_product[$products_id];
  }
  
}
?>