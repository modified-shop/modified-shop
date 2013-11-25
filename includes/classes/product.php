<?php
/* -----------------------------------------------------------------------------------------
   $Id: product.php 2696 2012-03-04 10:44:41Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(Coding Standards); www.oscommerce.com
   (c) 2006 XT-Commerce (product.php 1316 2005-10-21)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

class product {

  /**
  *
  * Constructor
   *
   * @param integer $pID
   * @return product
   */
  function product($pID = 0) {
    $this->pID = (int)$pID;
    
    //set default select, using in function getAlsoPurchased, getCrossSells, getReverseCrossSells
    $this->default_select ='p.products_fsk18,
                            p.products_id,
                            p.products_price,
                            p.products_tax_class_id,
                            p.products_image,
                            p.products_quantity,
                            p.products_vpe,
                            p.products_vpe_status,
                            p.products_vpe_value,
                            p.products_model,
                            pd.products_name,
                            pd.products_short_description';

    // default products image
    $this->useStandardImage=true;
    $this->standardImage='noimage.gif';

    if ($pID == 0) {
      $this->isProduct = false;
      return;
    }
    // query for Product
    $product_query = "SELECT *
                        FROM ".TABLE_PRODUCTS." AS p
                        JOIN ".TABLE_PRODUCTS_DESCRIPTION." AS pd ON p.products_status = '1'
                         AND p.products_id = '".$this->pID."'
                         AND pd.products_id = p.products_id
                         " . PRODUCTS_CONDITIONS_P . "
                         AND pd.language_id = '".(int)$_SESSION['languages_id']."'";

    $product_query = xtDBquery($product_query);

    if (!xtc_db_num_rows($product_query, true)) {
      $this->isProduct = false;
    } else {
      $this->isProduct = true;
      $this->data = xtc_db_fetch_array($product_query, true);
    }
  }

  /**
   * Query for attributes count
   *
   * @return integer
   */
  function getAttributesCount() {
    $products_attributes_query = xtDBquery("SELECT count(*) AS total
                                              FROM ".TABLE_PRODUCTS_OPTIONS." popt,
                                                   ".TABLE_PRODUCTS_ATTRIBUTES." patrib
                                             WHERE patrib.products_id=".$this->pID."
                                               AND patrib.options_id = popt.products_options_id
                                               AND popt.language_id = ".(int) $_SESSION['languages_id']
                                          );
    $products_attributes = xtc_db_fetch_array($products_attributes_query, true);
    return $products_attributes['total'];
  }

  /**
   * Query for reviews count
   *
   * @return integer
   */
  function getReviewsCount() {
    $reviews_query = xtDBquery("SELECT count(*) AS total
                                  FROM ".TABLE_REVIEWS." r,
                                       ".TABLE_REVIEWS_DESCRIPTION." rd
                                 WHERE r.products_id = ".$this->pID."
                                   AND r.reviews_id = rd.reviews_id
                                   AND rd.languages_id = ".(int)$_SESSION['languages_id']."
                                   AND rd.reviews_text !=''
                              ");
    $reviews = xtc_db_fetch_array($reviews_query, true);
    return $reviews['total'];
  }

  /**
   * getReviewsAverage
   *
   * @return array
   */
  function getReviews() {
    $reviews_query = xtDBquery("SELECT r.reviews_rating,
                                       r.reviews_id,
                                       r.customers_name,
                                       r.date_added,
                                       r.last_modified,
                                       r.reviews_read,
                                       rd.reviews_text
                                  FROM ".TABLE_REVIEWS." r,
                                       ".TABLE_REVIEWS_DESCRIPTION." rd
                                 WHERE r.products_id = '".$this->pID."'
                                   AND r.reviews_id = rd.reviews_id
                                   AND rd.languages_id = '".(int)$_SESSION['languages_id']."'
                              ORDER BY reviews_id DESC
                              ");
    if (xtc_db_num_rows($reviews_query, true)) {
      $data_reviews = array ();
      while ($reviews = xtc_db_fetch_array($reviews_query, true)) {
        $data_reviews[] = array (
            'AUTHOR' => $reviews['customers_name'],
            'DATE' => xtc_date_short($reviews['date_added']),
            'RATING' => xtc_image('templates/'.CURRENT_TEMPLATE.'/img/stars_'.$reviews['reviews_rating'].'.gif', sprintf(TEXT_OF_5_STARS, $reviews['reviews_rating']),'','','itemprop="rating"'),
            'TEXT' => nl2br($reviews['reviews_text'])
          );
        if (count($data_reviews) == PRODUCT_REVIEWS_VIEW) break;
      }
    }
    return $data_reviews;
  }

  /**
   * return name if set, else return model
   *
   * @return string
   */
  function getBreadcrumbModel() {
    if (($this->data['products_model'] != "") && DISPLAY_BREADCRUMB_OPTION == 'model') {
      return $this->data['products_model'];
    }
    return $this->data['products_name'];
  }

  /**
   * get also purchased products related to current
   *
   * @return array
   */
  function getAlsoPurchased() {
    global $xtPrice;

    $module_content = array ();

    $orders_query = "SELECT ".$this->default_select."
                       FROM ".TABLE_ORDERS_PRODUCTS." op1
                       JOIN ".TABLE_ORDERS_PRODUCTS." op2 on op2.orders_id = op1.orders_id
                       JOIN ".TABLE_ORDERS." o on o.orders_id = op2.orders_id
                       JOIN ".TABLE_PRODUCTS." p on p.products_id = op2.products_id
                       JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd on pd.products_id = op2.products_id
                      WHERE op1.products_id = ".$this->pID."
                        AND op2.products_id != ".$this->pID."
                        AND p.products_status = 1
                        AND trim(pd.products_name) != ''
                        AND pd.language_id = ".(int) $_SESSION['languages_id']."
                        " . PRODUCTS_CONDITIONS_P . "
                   GROUP BY p.products_id
                   ORDER BY o.date_purchased desc
                      LIMIT ".MAX_DISPLAY_ALSO_PURCHASED;

    $orders_query = xtDBquery($orders_query);
    while ($orders = xtc_db_fetch_array($orders_query, true)) {
      $module_content[] = $this->buildDataArray($orders);
    }
    return $module_content;
  }

  /**
   * Get Cross sells
   *
   * @return array
   */
  function getCrossSells() {
    global $xtPrice;

    $cs_groups = "SELECT products_xsell_grp_name_id
                    FROM ".TABLE_PRODUCTS_XSELL."
                   WHERE products_id = '".$this->pID."'
                GROUP BY products_xsell_grp_name_id";
    $cs_groups = xtDBquery($cs_groups);
    $cross_sell_data = array ();
    if (xtc_db_num_rows($cs_groups, true) > 0) {
      while ($cross_sells = xtc_db_fetch_array($cs_groups, true)) {
        $cross_query = "SELECT ".$this->default_select.",
                               xp.sort_order
                          FROM ".TABLE_PRODUCTS_XSELL." xp,
                               ".TABLE_PRODUCTS." p,
                               ".TABLE_PRODUCTS_DESCRIPTION." pd
                         WHERE xp.products_id = ".$this->pID."
                           AND xp.xsell_id = p.products_id
                           " . PRODUCTS_CONDITIONS_P . "
                           AND p.products_id = pd.products_id
                           AND xp.products_xsell_grp_name_id='".$cross_sells['products_xsell_grp_name_id']."'
                           AND pd.language_id = ".(int)$_SESSION['languages_id']."
                           AND trim(pd.products_name) != ''
                           AND p.products_status = 1
                      ORDER BY xp.sort_order asc";
        $cross_query = xtDBquery($cross_query);
        if (xtc_db_num_rows($cross_query, true) > 0)
          $cross_sell_data[$cross_sells['products_xsell_grp_name_id']] = array (
              'GROUP' => xtc_get_cross_sell_name($cross_sells['products_xsell_grp_name_id']),
              'PRODUCTS' => array ()
            );
        while ($xsell = xtc_db_fetch_array($cross_query, true)) {
          $cross_sell_data[$cross_sells['products_xsell_grp_name_id']]['PRODUCTS'][] = $this->buildDataArray($xsell);
        }
      }
      return $cross_sell_data;
    }
  }

  /**
   * get reverse cross sells
   *
   * @return array
   */
  function getReverseCrossSells() {
    global $xtPrice;

    $cross_query = xtDBquery("SELECT ".$this->default_select.",
                                     xp.sort_order
                                FROM ".TABLE_PRODUCTS_XSELL." xp,
                                     ".TABLE_PRODUCTS." p,
                                     ".TABLE_PRODUCTS_DESCRIPTION." pd
                               WHERE xp.xsell_id = '".$this->pID."'
                                 AND xp.products_id = p.products_id
                                 " . PRODUCTS_CONDITIONS_P . "
                                 AND p.products_id = pd.products_id
                                 AND pd.language_id = ".(int)$_SESSION['languages_id']."
                                 AND trim(pd.products_name) != ''
                                 AND p.products_status = 1
                            ORDER BY xp.sort_order asc");

    $cross_sell_data = array();
    while ($xsell = xtc_db_fetch_array($cross_query, true)) {
      $cross_sell_data[] = $this->buildDataArray($xsell);
    }
    return $cross_sell_data;
  }

  /**
   * getGraduated
   *
   * @return array
   */
  function getGraduated() {
    global $xtPrice;

    $discount = $xtPrice->xtcCheckDiscount($this->pID);
    $staffel_query = xtDBquery("SELECT quantity,
                                       personal_offer
                                  FROM ".TABLE_PERSONAL_OFFERS_BY.(int) $_SESSION['customers_status']['customers_status_id']."
                                 WHERE products_id = ".$this->pID."
                              ORDER BY quantity ASC");
    $staffel = array ();
    while ($staffel_values = xtc_db_fetch_array($staffel_query, true)) {
      $staffel[] = array (
          'stk' => $staffel_values['quantity'],
          'price' => $staffel_values['personal_offer']
        );
    }
    $staffel_data = array ();
    for ($i = 0, $n = sizeof($staffel); $i < $n; $i ++) {
      if ($staffel[$i]['stk'] == 1 || (array_key_exists($i +1, $staffel) && $staffel[$i +1]['stk'] != '')){ //DokuMan - 2010-10-13 - added array_key_exists()
        $quantity = $staffel[$i]['stk'];
        if (array_key_exists($i + 1, $staffel) && $staffel[$i +1]['stk'] != '' && $staffel[$i +1]['stk'] != $staffel[$i]['stk'] + 1) //DokuMan - 2010-10-13 - added array_key_exists()
          $quantity .= ' - '. ($staffel[$i +1]['stk'] - 1);
      } else {
        $quantity = GRADUATED_PRICE_MAX_VALUE.' '.$staffel[$i]['stk'];
      }
      $vpe = '';
      if (isset($this->data) && $this->data['products_vpe_status'] == 1 && $this->data['products_vpe_value'] != 0.0 && $staffel[$i]['price'] > 0) {
        $vpe = $staffel[$i]['price'] - $staffel[$i]['price'] / 100 * $discount;
        $vpe = $vpe * (1 / $this->data['products_vpe_value']);
        $vpe = BASICPRICE_VPE_TEXT.$xtPrice->xtcFormat($vpe, true, $this->data['products_tax_class_id']).TXT_PER.xtc_get_vpe_name($this->data['products_vpe']);
      }
      $staffel_data[$i] = array (
          'QUANTITY' => $quantity,
          'PLAIN_QUANTITY' => $staffel[$i]['stk'],
          'VPE' => $vpe,
          'PRICE' => $xtPrice->xtcFormat($staffel[$i]['price'] - $staffel[$i]['price'] / 100 * $discount, true, $this->data['products_tax_class_id']),
          'PLAIN_PRICE' => $xtPrice->xtcFormat($staffel[$i]['price'] - $staffel[$i]['price'] / 100 * $discount, false, $this->data['products_tax_class_id'])
        );
    }
    return $staffel_data;
  }

  /**
   * valid flag
   *
   * @return boolean
   */
  function isProduct() {
    return $this->isProduct;
  }

  /**
   * getBuyNowButton
   *
   * @param integer $id
   * @param string $name
   * @return string
   */
  function getBuyNowButton($id, $name) {
    global $PHP_SELF;
    return '<a href="'.xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array ('action')).'action=buy_now&BUYproducts_id='.$id, 'NONSSL').'">'.xtc_image_button('button_buy_now.gif', TEXT_BUY.$name.TEXT_NOW).'</a>';
  }

  /**
   * getVPEtext
   *
   * @param unknown_type $product
   * @param unknown_type $price
   * @return unknown
   */
  function getVPEtext($product, $price) {
    global $main;
    return $main->getVPEtext($product, $price); //change to main class
  }

  /**
   * buildDataArray
   *
   * @param array $array
   * @return array
   */
  function buildDataArray(&$array,$image='thumbnail') {
    global $xtPrice, $main;

    //get tax rate
    $tax_rate = isset($xtPrice->TAX[$array['products_tax_class_id']]) ? $xtPrice->TAX[$array['products_tax_class_id']] : 0;

    //get products price , returns array
    $products_price = $xtPrice->xtcGetPrice($array['products_id'], $format = true, 1, $array['products_tax_class_id'], $array['products_price'], 1);

    //create buy now button
    $buy_now = '';
    if ($_SESSION['customers_status']['customers_status_show_price'] != '0' && defined('SHOW_BUTTON_BUY_NOW') && SHOW_BUTTON_BUY_NOW != 'false'
        && ($_SESSION['customers_status']['customers_fsk18'] != '1' || (isset($array['products_fsk18']) && $array['products_fsk18'] == '0')) ) {
      $buy_now = $this->getBuyNowButton($array['products_id'], $array['products_name']);
    }

    //get $shipping_status_name, $shipping_status_image
    $shipping_status_name = $shipping_status_image = '';
    if (isset($array['products_shippingtime']) && ACTIVATE_SHIPPING_STATUS == 'true') {
      $shipping_status_name = $main->getShippingStatusName($array['products_shippingtime']);
      $shipping_status_image = $main->getShippingStatusImage($array['products_shippingtime']);
    }
    
    //get products image, imageinfo array
    $products_image = $this->productImage($array['products_image'], $image);    
    $p_img = substr($products_image, strlen(DIR_WS_BASE));
    $img_attr = '';
    if (file_exists($p_img)) {
      list($width, $height, $type, $img_attr) = getimagesize($p_img);
    }

    //products data array
    $productData = array();
    foreach((array)$array as $key => $entry) {                  
      $productData[strtoupper($key)] = $entry;
    }

    $productDataAdds = array (
        'PRODUCTS_PRICE' => $products_price['formated'],
        'COUNT' => isset($array['ID']) ? $array['ID'] : 0,
        'PRODUCTS_VPE' => $main->getVPEtext($array, $products_price['plain']),
        'PRODUCTS_VPE_VALUE' => $array['products_vpe_value'],
        'PRODUCTS_VPE_NAME' => $main->vpe_name,
        'PRODUCTS_IMAGE' => $products_image,
        'PRODUCTS_IMAGE_SIZE' => $img_attr,
        'PRODUCTS_IMAGE_TITLE' => str_replace('"','',$array['products_name']),
        'PRODUCTS_LINK' => xtc_href_link(FILENAME_PRODUCT_INFO, xtc_product_link($array['products_id'], $array['products_name'])),
        'PRODUCTS_TAX_INFO' => $main->getTaxInfo($tax_rate),
        'PRODUCTS_SHIPPING_LINK' => $main->getShippingLink(),
        'PRODUCTS_BUTTON_BUY_NOW' => $buy_now,
        'PRODUCTS_SHIPPING_NAME'=>$shipping_status_name,
        'PRODUCTS_SHIPPING_IMAGE'=>$shipping_status_image,
        'PRODUCTS_EXPIRES' => isset($array['expires_date']) ? $array['expires_date'] : 0,
        'PRODUCTS_CATEGORY_URL' => isset($array['cat_url']) ? $array['cat_url'] : '',
        'PRODUCTS_BUTTON_DETAILS' => '<a href="'.xtc_href_link(FILENAME_PRODUCT_INFO, xtc_product_link($array['products_id'], $array['products_name'])).'">'.xtc_image_button('button_product_more.gif', $array['products_name'].TEXT_INFO_DETAILS).'</a>'
      );

    $productData = array_merge($productData,$productDataAdds);                     

    foreach((array)$products_price as $key => $entry) {                  
      $productData['PRODUCTS_PRICE_'.strtoupper($key)] = $entry;
    }

    //echo '<pre>'.print_r($productData,true).'</pre>';
    return $productData;
  }

  /**
   * productImage
   *
   * @param string $name
   * @param string $type
   * @return string
   */
  function productImage($name, $type) {
    switch ($type) {
      case 'info' :
        $path = DIR_WS_INFO_IMAGES;
        break;
      case 'thumbnail' :
        $path = DIR_WS_THUMBNAIL_IMAGES;
        break;
      case 'popup' :
        $path = DIR_WS_POPUP_IMAGES;
        break;
    }

    if (empty($name)) {
      //if ($this->useStandardImage == 'true' && $this->standardImage != '') // comment in when "noimage.gif" should be displayed when there is no image defined in the database
      //  return $path.$this->standardImage; // comment in when "noimage.gif" should be displayed when there is no image defined in the database
      return $name; // comment out when "noimage.gif" should be displayed when there is no image defined in the database
    } else {
      // check if image exists
      if (!file_exists($path.$name)) {
        if ($this->useStandardImage == 'true' && $this->standardImage != '') {
          $name = $this->standardImage;
        }
      }
      return $path.$name;
    }
  }
}
?>