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
  function xtcPrice($currency, $cGroup) {

    $this->currencies = array();
    $this->cStatus = array();
    $this->actualGroup = (int) $cGroup;
    $this->actualCurr = $currency;
    $this->TAX = array();
    $this->SHIPPING = array();
    $this->showFrom_Attributes = true;

    if (!defined('HTTP_CATALOG_SERVER') && isset($_SESSION['cart'])) {
      if (is_object($_SESSION['cart'])) {
        $this->content_type = $_SESSION['cart']->get_content_type();
      }
    }

    // select Currencies
    $currencies_query = xtDBquery("SELECT * FROM " . TABLE_CURRENCIES);
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
        if (isset($_SESSION['country']) && !isset($_SESSION['customer_id'])) {
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
    
    // get Tax rate
    if ($cedit_id != 0) {
      if (defined('HTTP_CATALOG_SERVER')) {
        global $order; // edit orders in admin guest account
        $cinfo = get_c_infos($order->customer['ID'], trim($order->delivery['country_iso_2']));
      } else {
        $cinfo = xtc_oe_customer_infos($cedit_id);
      }
      $products_tax = xtc_get_tax_rate($tax_class, $cinfo['country_id'], $cinfo['zone_id']);
    } else {
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
  function getPprice($pID)
  {
    $pQuery = "SELECT products_price FROM " . TABLE_PRODUCTS . " WHERE products_id='" . $pID . "'";
    $pQuery = xtDBquery($pQuery);
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

  /**
   * xs:booster (v1.041, 2009-11-28)
   *
   * @param Integer $pID product id
   * @return Mixed
   */
  function xtcCheckXTBAuction($pID) {
    if (($pos = strpos($pID, "{")))
      $pID = substr($pID, 0, $pos);
    if (@!is_array($_SESSION['xtb0']['tx']))
      return false;
    foreach ($_SESSION['xtb0']['tx'] as $tx) {
      if ($tx['products_id'] == $pID && $tx['XTB_QUANTITYPURCHASED'] != 0) {
        $this->actualCurr = $tx['XTB_AMOUNTPAID_CURRENCY'];
        return round($tx['XTB_AMOUNTPAID'], $this->currencies[$this->actualCurr]['decimal_places']);
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
      $discount_query = "SELECT products_discount_allowed FROM " . TABLE_PRODUCTS . " WHERE products_id = '" . $pID . "'";
      $discount_query = xtDBquery($discount_query);
      $dData          = xtc_db_fetch_array($discount_query, true);
      
      $discount = $dData['products_discount_allowed'];
      if ($this->cStatus['customers_status_discount'] < $discount)
        $discount = $this->cStatus['customers_status_discount'];
      if ($discount == '0.00')
        return false;
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
      $actual_content_qty = xtc_get_qty($pID);
      $qty = $actual_content_qty > $qty ? $actual_content_qty : $qty;
    }
    
    if (empty($this->actualGroup)) {
      $this->actualGroup = DEFAULT_CUSTOMERS_STATUS_ID_GUEST;
    }
    
    $graduated_price_query = xtDBquery("SELECT max(quantity) AS qty
                                          FROM " . TABLE_PERSONAL_OFFERS_BY . $this->actualGroup . "
                                         WHERE products_id='" . $pID . "'
                                           AND quantity<='" . $qty . "'");
    $graduated_price_data  = xtc_db_fetch_array($graduated_price_query, true);
    if ($graduated_price_data['qty']) {
      $graduated_price_query = xtDBquery("SELECT personal_offer
                                            FROM " . TABLE_PERSONAL_OFFERS_BY . $this->actualGroup . "
                                           WHERE products_id='" . $pID . "'
                                             AND quantity='" . $graduated_price_data['qty'] . "'");
      $graduated_price_data  = xtc_db_fetch_array($graduated_price_query, true);
      $sPrice = $graduated_price_data['personal_offer'];
      if ($sPrice != 0.00) {
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
    $graduated_price_query = "SELECT max(quantity) as qty
                                FROM " . TABLE_PERSONAL_OFFERS_BY . $this->actualGroup . "
                               WHERE products_id='" . $pID . "'
                                 AND quantity<='" . $qty . "'";
    $graduated_price_query = xtDBquery($graduated_price_query);
    $graduated_price_data = xtc_db_fetch_array($graduated_price_query, true);
    if ($graduated_price_data['qty']) {
      $graduated_price_query = "SELECT personal_offer
                                  FROM " . TABLE_PERSONAL_OFFERS_BY . $this->actualGroup . "
                                 WHERE products_id='" . $pID . "'
                                   AND quantity='" . $graduated_price_data['qty'] . "'";
      $graduated_price_query = xtDBquery($graduated_price_query);
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
    $price = $discount = 0;
    $attribute_price_query = "-- xtcGetOptionPrice
        SELECT pd.products_discount_allowed,
               pd.products_tax_class_id,
               p.options_values_price,
               p.price_prefix,
               p.options_values_weight,
               p.weight_prefix
          FROM " . TABLE_PRODUCTS_ATTRIBUTES . " p,
               " . TABLE_PRODUCTS . " pd
         WHERE p.products_id = '" . $pID . "'
           AND p.options_id = '" . $option . "'
           AND pd.products_id = p.products_id
           AND p.options_values_id = '" . $value . "'";
    $attribute_price_query = xtDBquery($attribute_price_query);
    $attribute_price_data  = xtc_db_fetch_array($attribute_price_query, true);
    if ($this->cStatus['customers_status_discount_attributes'] == 1 && $this->cStatus['customers_status_discount'] != 0.00) {
      $discount = $this->cStatus['customers_status_discount'];
      if ($attribute_price_data['products_discount_allowed'] < $this->cStatus['customers_status_discount'])
        $discount = $attribute_price_data['products_discount_allowed'];
    }
    // several currencies on product attributes
    $CalculateCurr = ($attribute_price_data['products_tax_class_id'] == 0) ? true : false;
    $price = $this->xtcFormat($attribute_price_data['options_values_price'], false, $attribute_price_data['products_tax_class_id'], $CalculateCurr);
    if ($attribute_price_data['weight_prefix'] != '+')
      $attribute_price_data['options_values_weight'] *= -1;
    if ($attribute_price_data['price_prefix'] == '+') {
      $price = $price - $price / 100 * $discount;
    } else {
      $price *= -1;
    }
    return array(
      'weight' => $attribute_price_data['options_values_weight'],
      'price' => $price
    );
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
        'plain' => 0
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
    $product_query = "select specials_new_products_price from " . TABLE_SPECIALS . " where products_id = '" . $pID . "' and status=1";
    $product_query = xtDBquery($product_query);
    $product  = xtc_db_fetch_array($product_query, true);
    
    return $product['specials_new_products_price'];
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
    $products_attributes_query = "SELECT 
                                         count(*) as total 
                                    FROM " . TABLE_PRODUCTS_OPTIONS . " popt,
                                         " . TABLE_PRODUCTS_ATTRIBUTES . " patrib
                                   WHERE patrib.products_id='" . $pID . "'
                                     AND patrib.options_id = popt.products_options_id
                                     AND popt.language_id = '" . (int) $_SESSION['languages_id'] . "'
                                     AND patrib.options_values_price > 0";
    $products_attributes = xtDBquery($products_attributes_query);
    $products_attributes = xtc_db_fetch_array($products_attributes, true);
    if ($products_attributes['total'] > 0) {
      return ' ' . strtolower(FROM) . ' ';
    }
  }
  
  
  
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
      $price        = $this->xtcAddTax($price, $products_tax);
    }
    $decimal_places = ($decimal_places > 0) ? $decimal_places : $this->currencies[$this->actualCurr]['decimal_places'];
    if ($format) {
      $Pprice = number_format(floatval($price), $decimal_places, $this->currencies[$this->actualCurr]['decimal_point'], $this->currencies[$this->actualCurr]['thousands_point']);
      $Pprice = $this->checkAttributes($pID) . $this->currencies[$this->actualCurr]['symbol_left'] . ' ' . $Pprice . ' ' . $this->currencies[$this->actualCurr]['symbol_right'];
      if ($vpeStatus == 0) {
        return $Pprice;
      } else {
        return array(
          'formated' => $Pprice,
          'plain' => $price
        );
      }
    } else {
      return round($price, $decimal_places);
    }
  }
  
  function xtcFormatSpecialDiscount($pID, $discount, $pPrice, $format, $vpeStatus = 0) {
    $sPrice = $pPrice - ($pPrice / 100) * $discount;
    if ($format) {
      $price = '<span class="productOldPrice"><small>' . INSTEAD . '</small><del>' . $this->xtcFormat($pPrice, $format) . '</del></span><br />' . ONLY . $this->checkAttributes($pID) . $this->xtcFormat($sPrice, $format) . '<br /><small>' . YOU_SAVE . round(($pPrice - $sPrice) / $pPrice * 100) . ' % /' . $this->xtcFormat($pPrice - $sPrice, $format);
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
          'plain' => $sPrice
        );
      }
    } else {
      return round($sPrice, $this->currencies[$this->actualCurr]['decimal_places']);
    }
  }
  
  function xtcFormatSpecial($pID, $sPrice, $pPrice, $format, $vpeStatus = 0) {
    if ($format) {
      if (!isset($pPrice) || $pPrice == 0)
        $discount = 0;
      else
        $discount = ($pPrice - $sPrice) / $pPrice * 100;
      $price = '<span class="productOldPrice"><small>' . INSTEAD . '</small><del>' . $this->xtcFormat($pPrice, $format) . '</del></span><br />' . ONLY . $this->checkAttributes($pID) . $this->xtcFormat($sPrice, $format) . '<br /><small>' . YOU_SAVE . round($discount) . ' % /' . $this->xtcFormat($pPrice - $sPrice, $format) . '</small>';
      if ($vpeStatus == 0) {
        return $price;
      } else {
        return array(
          'formated' => $price,
          'plain' => $sPrice
        );
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
      if (($this->cStatus['customers_status_graduated_prices'] == '1') && ($sQuery['qty'] > 1)) {
        $bestPrice = $this->xtcGetGraduatedPrice($pID, $sQuery['qty']);
        if ($discount) {
          $bestPrice -= $bestPrice / 100 * $discount;
        }
        $price = FROM . $this->xtcFormat($bestPrice, $format, $tax_class) . ' <br /><small>' . UNIT_PRICE . $this->xtcFormat($sPrice, $format) . '</small>';
      } else if ($sPrice != $pPrice) {
        $price = '<span class="productOldPrice">' . MSRP . ' ' . $this->xtcFormat($pPrice, $format) . '</span><br />' . YOUR_PRICE . $this->checkAttributes($pID) . $this->xtcFormat($sPrice, $format);
      } else {
        $price = $this->xtcFormat($sPrice, $format);
      }
      
      if ($vpeStatus == 0) {
        return $price;
      } else {
        return array(
          'formated' => $price,
          'plain' => $sPrice
        );
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