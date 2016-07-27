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
  function __construct($pID = 0) {
    global $xtPrice;

    require_once (DIR_FS_CATALOG.'includes/classes/productModules.class.php');
    $this->productModules = new productModules();
    
    $this->pID = (int)$pID;
    
    //set default select, using in function getAlsoPurchased, getCrossSells, getReverseCrossSells
    $this->default_select = ADD_SELECT_PRODUCT .
                            'p.products_fsk18,
                             p.products_id,
                             p.products_price,
                             p.products_tax_class_id,
                             p.products_image,
                             p.products_quantity,
                             p.products_shippingtime,
                             p.products_vpe,
                             p.products_vpe_status,
                             p.products_vpe_value,
                             p.products_model,
                             pd.products_name,
                             pd.products_short_description';

    // default products image
    $this->useStandardImage = PRODUCT_IMAGE_SHOW_NO_IMAGE;
    $this->standardImage = 'noimage.gif';

    if ($pID == 0) {
      $this->isProduct = false;
      return;
    }
    
    // query for Product
    $product_query = xtDBquery("SELECT *
                                  FROM ".TABLE_PRODUCTS." AS p
                                  JOIN ".TABLE_PRODUCTS_DESCRIPTION." AS pd 
                                       ON p.products_status = '1'
                                          AND p.products_id = '".$this->pID."'
                                          AND pd.products_id = p.products_id
                                          AND trim(pd.products_name) != ''
                                          " . PRODUCTS_CONDITIONS_P . "
                                          AND pd.language_id = '".(int)$_SESSION['languages_id']."'");
    if (!xtc_db_num_rows($product_query, true)) {
      $this->isProduct = false;
    } else {
      $this->isProduct = true;
      $this->data = xtc_db_fetch_array($product_query, true);

      if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 1
          && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 0
          && $xtPrice->get_content_type_product($this->data['products_id']) == 'virtual'
          ) 
      {
        $this->data['products_tax_class_id'] = xtc_get_tax_class($this->data['products_tax_class_id']);
      }

      $this->data = $this->productModules->construct($this->data);
    }
  }

  /**
   * Query for attributes count
   *
   * @return integer
   */
  function getAttributesCount($pID = '') {
    if ($pID == '') {
      $pID = $this->pID;
    }
    $products_attributes_query = xtDBquery("SELECT count(*) AS total
                                              FROM ".TABLE_PRODUCTS_OPTIONS." popt
                                              JOIN ".TABLE_PRODUCTS_ATTRIBUTES." patrib
                                                   ON patrib.options_id = popt.products_options_id
                                                      AND popt.language_id = '".(int) $_SESSION['languages_id']."'
                                             WHERE patrib.products_id = '".(int)$pID."'");
    $products_attributes = xtc_db_fetch_array($products_attributes_query, true);
    return $products_attributes['total'];
  }

  /**
   * Query for reviews count
   *
   * @return integer
   */
  function getReviewsCount($pID = '') {
    if ($pID == '') {
      $pID = $this->pID;
    }
    $reviews_query = xtDBquery("SELECT count(*) AS total
                                  FROM ".TABLE_REVIEWS." r
                                  JOIN ".TABLE_REVIEWS_DESCRIPTION." rd
                                       ON r.reviews_id = rd.reviews_id
                                          AND rd.languages_id = '".(int)$_SESSION['languages_id']."'
                                          AND rd.reviews_text != ''
                                 WHERE r.products_id = '".(int)$pID."'
                                   AND r.reviews_status = '1'");
    $reviews = xtc_db_fetch_array($reviews_query, true);
    return $reviews['total'];
  }


  /**
   * getReviewsAverage
   *
   * @return string
   */
  function getReviewsAverage($pID = '') {
    if ($pID == '') {
      $pID = $this->pID;
    }
    $avg_reviews_query = xtc_db_query("SELECT avg(reviews_rating) AS avg_rating 
                                         FROM ".TABLE_REVIEWS."
                                        WHERE products_id='".(int)$pID."'
                                          AND reviews_status = '1'");
    $avg_reviews = xtc_db_fetch_array($avg_reviews_query);

    return round($avg_reviews['avg_rating'], 0);
  } 


  /**
   * getReviews
   *
   * @return array
   */
  function getReviews($pID = '') {
    if ($pID == '') {
      $pID = $this->pID;
    }
    $reviews_query = xtDBquery("SELECT r.reviews_rating,
                                       r.reviews_id,
                                       r.customers_name,
                                       r.date_added,
                                       r.last_modified,
                                       r.reviews_read,
                                       rd.reviews_text
                                  FROM ".TABLE_REVIEWS." r
                                  JOIN ".TABLE_REVIEWS_DESCRIPTION." rd
                                       ON r.reviews_id = rd.reviews_id
                                          AND rd.languages_id = '".(int)$_SESSION['languages_id']."'
                                 WHERE r.products_id = '".(int)$pID."'
                                   AND r.reviews_status = '1'
                              ORDER BY r.reviews_id DESC");
    $data_reviews = array ();
    if (xtc_db_num_rows($reviews_query, true)) {
      while ($reviews = xtc_db_fetch_array($reviews_query, true)) {
        $img = 'templates/'.CURRENT_TEMPLATE.'/img/stars_'.$reviews['reviews_rating'].'.gif';
        if (!is_file(DIR_FS_CATALOG.$img)) {
          $img = 'templates/'.CURRENT_TEMPLATE.'/img/stars_'.$reviews['reviews_rating'].'.png';        
        }
        $data_reviews[] = array (
            'AUTHOR' => $reviews['customers_name'],
            'DATE' => xtc_date_short($reviews['date_added']),
            'RATING' => xtc_image($img, sprintf(TEXT_OF_5_STARS, $reviews['reviews_rating'])),
            'RATING_MICROTAG' => xtc_image($img, sprintf(TEXT_OF_5_STARS, $reviews['reviews_rating']),'','','itemprop="rating"'),
            'RATING_VOTE' => $reviews['reviews_rating'],
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
	function getAlsoPurchased($pID = '') {
    if ($pID == '') {
      $pID = $this->pID;
    }

		$module_content = array ();
    		
    $orders_query = "SELECT orders_id 
                       FROM ".TABLE_ORDERS_PRODUCTS." 
                      WHERE products_id = '".(int)$pID."'
                   ORDER BY orders_id DESC";
    $orders_query = xtDBquery($orders_query);
    while ($orders = xtc_db_fetch_array($orders_query, true)) {
      $products_query = "SELECT ".$this->default_select."
                           FROM ".TABLE_ORDERS_PRODUCTS." op
                           JOIN ".TABLE_PRODUCTS." p 
                                ON p.products_id = op.products_id
                                   AND p.products_status = '1'
                                   AND p.products_id != '".(int)$pID."'
                           JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd 
                                ON pd.products_id = p.products_id
                                   AND pd.language_id = '".(int) $_SESSION['languages_id']."'
                                   AND trim(pd.products_name) != ''
                          WHERE op.orders_id = '".$orders['orders_id']."'
                                " . PRODUCTS_CONDITIONS_P."
                       GROUP BY p.products_id";
      $products_query = xtDBquery($products_query);
      while ($products = xtc_db_fetch_array($products_query, true)) {
        $module_content[] = $this->buildDataArray($products);
        if (count($module_content) >= MAX_DISPLAY_ALSO_PURCHASED) {
          break 2;
        }
      }
    }    
		return $module_content;
	}

  /**
   * Get Cross sells
   *
   * @return array
   */
  function getCrossSells($pID = '') {
    if ($pID == '') {
      $pID = $this->pID;
    }

    $cross_sells_query = xtDBquery("SELECT px.products_xsell_grp_name_id,
                                           pxg.groupname
                                      FROM ".TABLE_PRODUCTS_XSELL." px
                                 LEFT JOIN ".TABLE_PRODUCTS_XSELL_GROUPS." pxg
                                           ON px.products_xsell_grp_name_id = pxg.products_xsell_grp_name_id
                                              AND pxg.language_id = '".(int)$_SESSION['languages_id']."'
                                     WHERE px.products_id = '".(int)$pID."'
                                  GROUP BY px.products_xsell_grp_name_id");
    $cross_sell_data = array ();
    if (xtc_db_num_rows($cross_sells_query, true) > 0) {
      while ($cross_sells = xtc_db_fetch_array($cross_sells_query, true)) {
        $xsell_query = xtDBquery("SELECT ".$this->default_select.",
                                         xp.sort_order
                                    FROM ".TABLE_PRODUCTS_XSELL." xp
                                    JOIN ".TABLE_PRODUCTS." p
                                         ON xp.xsell_id = p.products_id
                                            AND p.products_status = '1'
                                    JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                         ON p.products_id = pd.products_id
                                            AND pd.language_id = '".(int)$_SESSION['languages_id']."'
                                            AND trim(pd.products_name) != ''
                                   WHERE xp.products_id = '".(int)$pID."'
                                     AND xp.products_xsell_grp_name_id='".$cross_sells['products_xsell_grp_name_id']."'
                                         ".PRODUCTS_CONDITIONS_P."
                                ORDER BY xp.sort_order ASC");
        if (xtc_db_num_rows($xsell_query, true) > 0) {
          $cross_sell_data[$cross_sells['products_xsell_grp_name_id']] = array(
            'GROUP' => $cross_sells['groupname'],
            'PRODUCTS' => array()
          );
        }
        while ($xsell = xtc_db_fetch_array($xsell_query, true)) {
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
  function getReverseCrossSells($pID = '') {
    if ($pID == '') {
      $pID = $this->pID;
    }

    $cross_query = xtDBquery("SELECT ".$this->default_select.",
                                     xp.sort_order
                                FROM ".TABLE_PRODUCTS_XSELL." xp
                                JOIN ".TABLE_PRODUCTS." p
                                     ON xp.products_id = p.products_id
                                        AND p.products_status = 1
                                JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                     ON p.products_id = pd.products_id
                                        AND pd.language_id = '".(int)$_SESSION['languages_id']."'
                                        AND trim(pd.products_name) != ''
                               WHERE xp.xsell_id = '".(int)$pID."'
                                     ".PRODUCTS_CONDITIONS_P."
                            ORDER BY xp.sort_order ASC");
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
  function getGraduated($pID = '') {
    global $xtPrice;

    if ($pID == '') {
      $pID = $this->pID;
    }
    $staffel_data = array ();
    
    if (!$xtPrice->xtcCheckSpecial((int)$pID)) {
      $discount = $xtPrice->xtcCheckDiscount((int)$pID);
      $staffel_query = xtDBquery("SELECT quantity,
                                         personal_offer
                                    FROM ".TABLE_PERSONAL_OFFERS_BY.(int) $_SESSION['customers_status']['customers_status_id']."
                                   WHERE products_id = '".(int)$pID."'
                                ORDER BY quantity ASC");
      $staffel = array ();
      while ($staffel_values = xtc_db_fetch_array($staffel_query, true)) {
        $staffel[] = array('stk' => $staffel_values['quantity'],
                           'price' => $staffel_values['personal_offer']
                           );
      }
      for ($i=0, $n=sizeof($staffel); $i<$n; $i++) {
        $to_quantity = '';
        if ($staffel[$i]['stk'] == 1 || (array_key_exists($i +1, $staffel) && $staffel[$i +1]['stk'] != '')) { 
          if ($staffel[$i]['stk'] == 1 && $staffel[$i]['price'] == '0.0000') {
            $staffel[$i]['price'] = $this->data['products_price'];
          }
          $quantity = $staffel[$i]['stk'];
          if (array_key_exists($i + 1, $staffel) && $staffel[$i +1]['stk'] != '' && $staffel[$i +1]['stk'] != $staffel[$i]['stk'] + 1) {
            $quantity .= ' - '. ($staffel[$i +1]['stk'] - 1);
            $to_quantity = $staffel[$i +1]['stk'] - 1;
          }
        } else {
          $quantity = GRADUATED_PRICE_MAX_VALUE.' '.$staffel[$i]['stk'];
        }
        $vpe = '';
        if (isset($this->data) && $this->data['products_vpe_status'] == 1 && $this->data['products_vpe_value'] != 0.0 && $staffel[$i]['price'] > 0) {
          $vpe = $staffel[$i]['price'] - $staffel[$i]['price'] / 100 * $discount;
          $vpe = $vpe * (1 / $this->data['products_vpe_value']);
          $vpe = $xtPrice->xtcFormat($vpe, true, $this->data['products_tax_class_id']).TXT_PER.xtc_get_vpe_name($this->data['products_vpe']);
        }
        $staffel_data[$i] = array('QUANTITY' => $quantity,
                                  'PLAIN_QUANTITY' => $staffel[$i]['stk'],
                                  'FROM_QUANTITY' => GRADUATED_PRICE_MAX_VALUE,
                                  'TO_QUANTITY' => $to_quantity,
                                  'VPE' => $vpe,
                                  'PRICE' => $xtPrice->xtcFormat($staffel[$i]['price'] - $staffel[$i]['price'] / 100 * $discount, true, $this->data['products_tax_class_id']),
                                  'PLAIN_PRICE' => $xtPrice->xtcFormat($staffel[$i]['price'] - $staffel[$i]['price'] / 100 * $discount, false, $this->data['products_tax_class_id'])
                                  );
      }
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
    return '<a href="'.xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('action','BUYproducts_id')).'action=buy_now&BUYproducts_id='.$id, 'NONSSL').'">'.xtc_image_button('button_buy_now.gif', TEXT_BUY.$name.TEXT_NOW).'</a>';
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
  function buildDataArray(&$array, $image='thumbnail') {
    global $xtPrice, $main;

    if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 1
        && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 0
        && $xtPrice->get_content_type_product($array['products_id']) == 'virtual'
        ) 
    {
      $array['products_tax_class_id'] = xtc_get_tax_class($array['products_tax_class_id']);
    }

    //get tax rate
    $tax_rate = isset($xtPrice->TAX[$array['products_tax_class_id']]) ? $xtPrice->TAX[$array['products_tax_class_id']] : 0;

    //get products price , returns array
    $products_price = $xtPrice->xtcGetPrice($array['products_id'], $format = true, 1, $array['products_tax_class_id'], $array['products_price'], 1);

    //create buy now button
    $buy_now = '';
    $wishlist_now = '';
    $wishlist_now_link = '';
    if ($_SESSION['customers_status']['customers_status_show_price'] != '0' && defined('SHOW_BUTTON_BUY_NOW') && SHOW_BUTTON_BUY_NOW != 'false'
        && ($_SESSION['customers_status']['customers_fsk18'] != '1' || (isset($array['products_fsk18']) && $array['products_fsk18'] == '0')) ) {
      $buy_now = $this->getBuyNowButton($array['products_id'], $array['products_name']);
      if (defined('MODULE_WISHLIST_SYSTEM_STATUS') && MODULE_WISHLIST_SYSTEM_STATUS == 'true') {
        $wishlist_now = $this->getWishlistNowButton($array['products_id'], $array['products_name']);
        $wishlist_now_link = $this->getWishlistNowButton($array['products_id'], $array['products_name'], true);
      }
    }
    
    // check for gift
    if (isset($array['products_model']) && preg_match('/^GIFT/', addslashes($array['products_model']))
        && $_SESSION['customers_status']['customers_status_id'] == DEFAULT_CUSTOMERS_STATUS_ID_GUEST
        && isset($_SESSION['customer_id']))
    {
      $buy_now = '';
      $array['products_gift_forbidden'] = 'true';
    }
    
    //create products link
    $products_link = xtc_href_link(FILENAME_PRODUCT_INFO, xtc_product_link($array['products_id'], $array['products_name']));

    //get $shipping_status_name, $shipping_status_image
    $shipping_status_name = $shipping_status_image = $shipping_status_link = '';
    if (isset($array['products_shippingtime']) && ACTIVATE_SHIPPING_STATUS == 'true') {
      $shipping_status_name = $main->getShippingStatusName($array['products_shippingtime']);
      $shipping_status_image = $main->getShippingStatusImage($array['products_shippingtime']);
      $shipping_status_link = $main->getShippingStatusName($array['products_shippingtime'], true);      
    }
    
    //get products image, imageinfo array
    $products_image = $this->productImage($array['products_image'], $image);    
    $p_img = substr($products_image, strlen(DIR_WS_BASE));
    $img_attr = '';
    if (file_exists($p_img)) {
      list($width, $height, $type, $img_attr) = getimagesize($p_img);
    }

    // exclude some variables
    if (isset($array['products_date_available']) && $array['products_date_available'] < date('Y-m-d H:i:s')) {
      unset($array['products_date_available']);
    }

    //products data array
    $productData = array();
    foreach((array)$array as $key => $entry) {                  
      $productData[strtoupper($key)] = $entry;
    }
    
    $productDataAdds = array (
      'PRODUCTS_PRICE' => $products_price['formated'],
      'PRICE_ALLOWED' => (($_SESSION['customers_status']['customers_status_show_price'] != '0') ? 'true' : 'false'),
      'COUNT' => isset($array['ID']) ? $array['ID'] : 0,
      'PRODUCTS_VPE' => $main->getVPEtext($array, $products_price['plain']),
      'PRODUCTS_VPE_VALUE' => $array['products_vpe_value'],
      'PRODUCTS_VPE_NAME' => $main->vpe_name,
      'PRODUCTS_IMAGE' => $products_image,
      'PRODUCTS_IMAGE_SIZE' => $img_attr,
      'PRODUCTS_IMAGE_TITLE' => str_replace(array('"', "'"), array('&quot;', '&apos;'), $array['products_name']), // Currently not in use
      'PRODUCTS_IMAGE_ALT' => str_replace(array('"', "'"), array('&quot;', '&apos;'), $array['products_name']), // Currently not in use
      'PRODUCTS_LINK' => $products_link,
      'PRODUCTS_TAX_INFO' => $main->getTaxInfo($tax_rate),
      'PRODUCTS_SHIPPING_LINK' => $main->getShippingLink(),
      'PRODUCTS_BUTTON_BUY_NOW' => $buy_now,
      'PRODUCTS_SHIPPING_NAME' => $shipping_status_name,
      'PRODUCTS_SHIPPING_IMAGE' => $shipping_status_image,
      'PRODUCTS_SHIPPING_NAME_LINK' => $shipping_status_link,
      'PRODUCTS_EXPIRES' => isset($array['expires_date']) ? $array['expires_date'] : 0,
      'PRODUCTS_CATEGORY_URL' => isset($array['cat_url']) ? $array['cat_url'] : '',
      'PRODUCTS_BUTTON_DETAILS' => '<a href="'.$products_link.'">'.xtc_image_button('button_product_more.gif', TEXT_INFO_DETAILS).'</a>',
      'PRODUCTS_BUTTON_WISHLIST_NOW' => $wishlist_now,
      'PRODUCTS_LINK_WISHLIST_NOW' => $wishlist_now_link,
    );

    $productData = array_merge($productData,$productDataAdds);                     

    foreach((array)$products_price as $key => $entry) {                  
      $productData['PRODUCTS_PRICE_'.strtoupper($key)] = $entry;
      $productData['PRODUCTS_PRICE_ARRAY'][0]['PRODUCTS_PRICE_'.strtoupper($key)] = $entry;
    }
    $productData['PRODUCTS_PRICE_ARRAY'][0]['PRICE_ALLOWED'] = $productData['PRICE_ALLOWED'];

    //echo '<pre>'.print_r($productData,true).'</pre>';
    $productData = $this->productModules->buildDataArray($productData,$array,$image);
    
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

    $returnName = $name;
    if ($returnName == '' || !is_file($path.$returnName)) {
      $returnName = '';
      if ($this->useStandardImage == 'true' && $this->standardImage != '' && is_file($path.$this->standardImage)) {
        $returnName = $this->standardImage;
      }
    }
    
    $returnName = ($returnName != '') ? DIR_WS_BASE.$path.$returnName : '';

    $returnName = $this->productModules->productImage($returnName, $name, $type ,$path);
    
    return $returnName;
  }

  /**
   * getWishlistNowButton
   *
   * @param integer $id
   * @param string $name
   * @param boolean $plain
   * @return string
   */
  function getWishlistNowButton($id, $name, $plain = false) {
    global $PHP_SELF;
    
    if (defined('MODULE_WISHLIST_SYSTEM_STATUS') && MODULE_WISHLIST_SYSTEM_STATUS == 'true') {
      $link = xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('action','BUYproducts_id')).'action=buy_now&wishlist=true&BUYproducts_id='.$id, 'NONSSL');
      if ($plain === false) {
        $link = '<a href="'.$link.'">'.xtc_image_button('button_in_wishlist.gif', $name.' '.TEXT_TO_WISHLIST).'</a>';
      }
    
      return $link;
    }
  }

  /**
   * getWishlistToCartButton
   *
   * @param integer $id
   * @param string $name
   * @return string
   */
  function getWishlistToCartButton($id, $name, $cart = false) {
    global $PHP_SELF;
    
    if (defined('MODULE_WISHLIST_SYSTEM_STATUS') && MODULE_WISHLIST_SYSTEM_STATUS == 'true') {
      return '<a href="'.xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('action','BUYproducts_id')).'action=wishlist_cart&BUYproducts_id='.$id, 'NONSSL').'">'.xtc_image_button((($cart == true) ? 'button_in_cart.gif' : 'button_buy_now.gif'), TEXT_BUY.$name.TEXT_NOW).'</a>';
    }
  }
  
  /**
   * getCartToWishlistLink
   *
   * @param integer $id
   * @param string $name
   * @return string
   */
  function getCartToWishlistLink($id, $name) {
    global $PHP_SELF;
    
    if (defined('MODULE_WISHLIST_SYSTEM_STATUS') && MODULE_WISHLIST_SYSTEM_STATUS == 'true') {
      return '<a href="'.xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('action','BUYproducts_id')).'action=cart_wishlist&BUYproducts_id='.$id, 'NONSSL').'">'.TEXT_TO_WISHLIST.'</a>';
    }
  }
}
?>