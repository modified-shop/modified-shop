<?php
/* -----------------------------------------------------------------------------------------
   $Id: main.php 3277 2012-07-22 15:18:21Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(Coding Standards); www.oscommerce.com
   (c) 2006 XT-Commerce (main.php 1286 2005-10-07)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

class main {

  /**
   * class constructor function
   */
  function main () {
    $this->SHIPPING = array();

    // prefetch shipping status
    $status_query=xtDBquery("SELECT shipping_status_name,
                                    shipping_status_image,
                                    shipping_status_id
                             FROM ".TABLE_SHIPPING_STATUS."
                             WHERE language_id = ".(int)$_SESSION['languages_id']);

    while ($status_data=xtc_db_fetch_array($status_query,true)) {
      $this->SHIPPING[$status_data['shipping_status_id']] = array(
        'name'=>$status_data['shipping_status_name'],
        'image'=>$status_data['shipping_status_image']
        );
    }
  }

  /**
   * getShippingStatusName
   *
   * @param integer $id
   * @return  string
   */
  // BOF - Tutorial: Umsetzung der EU-Verbraucherrichtlinie vom 13.06.2014
  /*
  function getShippingStatusName($id) {
     return isset($this->SHIPPING[$id]['name']) ? $this->SHIPPING[$id]['name'] : '';
  }
  */
  function getShippingStatusName($id, $link=false) {
    global $request_type;
    if ($link === false) {
      return (isset($this->SHIPPING[$id]['name']) ? $this->SHIPPING[$id]['name'] : '');
    }
    if (!defined('POPUP_SHIPPING_LINK_PARAMETERS')) {
      define('POPUP_SHIPPING_LINK_PARAMETERS', '&KeepThis=true&TB_iframe=true&height=400&width=600');
    }
    if (!defined('POPUP_SHIPPING_LINK_CLASS')) {
      define('POPUP_SHIPPING_LINK_CLASS', 'thickbox');
    }
    return '<a rel="nofollow" target="_blank" href="'.xtc_href_link(FILENAME_POPUP_CONTENT, 'coID='.SHIPPING_STATUS_INFOS.POPUP_SHIPPING_LINK_PARAMETERS, $request_type).'" title="Information" class="'.POPUP_SHIPPING_LINK_CLASS.'">'.(isset($this->SHIPPING[$id]['name']) ? $this->SHIPPING[$id]['name'] : '').'</a>';
  }
  // EOF - Tutorial: Umsetzung der EU-Verbraucherrichtlinie vom 13.06.2014

  /**
   * getShippingStatusImage
   *
   * @param integer $id
   * @return  string
   */
  function getShippingStatusImage($id) {
    if (isset($this->SHIPPING[$id]['image']) && $this->SHIPPING[$id]['image'] != '') {
      return DIR_WS_CATALOG.'admin/images/icons/'.$this->SHIPPING[$id]['image'];
    } else {
      return;
    }
  }

  /**
   * getShippingLink
   *
   * @return  string
   */
  function getShippingLink() {
    global $request_type;
    if (!defined('POPUP_SHIPPING_LINK_PARAMETERS')) {
      define('POPUP_SHIPPING_LINK_PARAMETERS', '&KeepThis=true&TB_iframe=true&height=400&width=600');
    }
    if (!defined('POPUP_SHIPPING_LINK_CLASS')) {
      define('POPUP_SHIPPING_LINK_CLASS', 'thickbox');
    }
    return ' '.SHIPPING_EXCL.' <a rel="nofollow" target="_blank" href="'.xtc_href_link(FILENAME_POPUP_CONTENT, 'coID='.SHIPPING_INFOS.POPUP_SHIPPING_LINK_PARAMETERS, $request_type).'" title="Information" class="'.POPUP_SHIPPING_LINK_CLASS.'">'.SHIPPING_COSTS.'</a>';
  }

  /**
   * getTaxNotice
   *
   * @return  string
   */
  function getTaxNotice() {
    // no prices
    if ($_SESSION['customers_status']['customers_status_show_price'] == 0) {
      return;
    }
    if ($_SESSION['customers_status']['customers_status_show_price_tax'] != 0) {
      return TAX_INFO_INCL_GLOBAL;
    }
    // excl tax + tax at checkout
    if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
      return TAX_INFO_ADD_GLOBAL;
    }
    // excl tax
    if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 0) {
      return TAX_INFO_EXCL_GLOBAL;
    }
    return;
  }

  /**
   * getTaxInfo
   *
   * @param string $tax_rate
   * @return string
   */
  function getTaxInfo($tax_rate) {
    $tax_info = ''; //DokuMan - 2010-08-24 - set undefined variable
    // price incl tax
    if ($tax_rate > 0 && $_SESSION['customers_status']['customers_status_show_price_tax'] != 0) {
      $tax_info = sprintf(TAX_INFO_INCL, $tax_rate.' %');
    }
    // excl tax + tax at checkout
    if ($tax_rate > 0 && $_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
      $tax_info = sprintf(TAX_INFO_ADD, $tax_rate.' %');
    }
    // excl tax
    if ($tax_rate > 0 && $_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 0) {
      $tax_info = sprintf(TAX_INFO_EXCL, $tax_rate.' %');
    }
    // no tax
    if ($tax_rate == 0) {
      $tax_info = sprintf(TAX_INFO_EXCL, '');
    }
    return $tax_info;
  }

  /**
   * getShippingNotice
   *
   * @return string
   */
  function getShippingNotice() {
    if (SHOW_SHIPPING == 'true') {
      return ' '.SHIPPING_EXCL.'<a href="'.xtc_href_link(FILENAME_CONTENT, 'coID='.SHIPPING_INFOS).'">'.SHIPPING_COSTS.'</a>';
    }
    return;
  }

  /**
   * getContentLink
   *
   * @param integer $coID
   * @param string $text, $ssl
   * @return string
   */
  function getContentLink($coID,$text,$ssl='NONSSL') {
    if (!defined('POPUP_CONTENT_LINK_PARAMETERS')) {
      define('POPUP_CONTENT_LINK_PARAMETERS', '&KeepThis=true&TB_iframe=true&height=400&width=600');
    }
    if (!defined('POPUP_CONTENT_LINK_CLASS')) {
      define('POPUP_CONTENT_LINK_CLASS', 'thickbox');
    }
    return '<a target="_blank" href="'.xtc_href_link(FILENAME_POPUP_CONTENT, 'coID='.$coID.POPUP_CONTENT_LINK_PARAMETERS, $ssl).'" title="Information" class="'.POPUP_CONTENT_LINK_CLASS.'"><font color="#ff0000">'.$text.'</font></a>';
  }
  
  /**
   * getContentData
   *
   * @param integer $coID
   * @return array
   */
  function getContentData($coID) { 
    $group_check = (GROUP_CHECK == 'true') ? "AND group_ids LIKE '%c_" . $_SESSION['customers_status']['customers_status_id'] . "_group%'" : '';
    $content_data_query = xtDBquery("-- includes/classes/main.php
                                       SELECT content_id,
                                              content_title,
                                              content_heading,
                                              content_text,
                                              content_file
                                         FROM " . TABLE_CONTENT_MANAGER . "
                                        WHERE content_group='". (int)$coID ."'
                                              " . $group_check . "
                                          AND languages_id='" . (int)$_SESSION['languages_id'] . "'
                                        LIMIT 1
                                      ");
    $content_data_array = xtc_db_fetch_array($content_data_query,true);
    
    // check if content data is a file
    if ($content_data_array['content_file'] != '') {
      unset($content_data_array['content_text']);
      ob_start();      
      include (DIR_FS_DOCUMENT_ROOT.'media/content/'.$content_data_array['content_file']);      
      $content_data_array['content_text'] = @ob_get_contents();
      ob_end_clean();
      //check for txt file and format output
      if (strpos($content_data_array['content_file'], '.txt')) {
        $content_data_array['content_text'] = '<pre>' . $content_data_array['content_text'] . '</pre>';
      }
    }
    
    return $content_data_array;    
  }

  /**
   * getVPEtext
   *
   * @param unknown_type $product
   * @param unknown_type $price
   * @return unknown
   */
  function getVPEtext($product, $price) {
    global $xtPrice;
    require_once (DIR_FS_INC.'xtc_get_vpe_name.inc.php');
    if (!is_array($product))
      $product = $this->data;
    if (isset($product['products_vpe_status']) && $product['products_vpe_status'] == 1 && $product['products_vpe_value'] != 0.0 && $price > 0) {
      return $xtPrice->xtcFormat($price * (1 / $product['products_vpe_value']), true).TXT_PER.xtc_get_vpe_name($product['products_vpe']);
    }
    return;
  }

  /**
   * getProductPopupLink
   *
   * @param integer $pID
   * @param string $text, $class
   * @return string
   */
  function getProductPopupLink($pID,$text,$class='',$add_params='') {
    global $request_type;
    if (!defined('POPUP_PRODUCT_LINK_PARAMETERS')) {
      define('POPUP_PRODUCT_LINK_PARAMETERS', '&KeepThis=true&TB_iframe=true&height=450&width=750');
    }
    if (!defined('POPUP_PRODUCT_LINK_CLASS')) {
      define('POPUP_PRODUCT_LINK_CLASS', 'thickbox');
    }
    if ($class == 'image') {
      require_once (DIR_FS_INC . 'xtc_get_products_image.inc.php');
      $products_image = DIR_WS_THUMBNAIL_IMAGES.xtc_get_products_image($pID);   
      return '<a target="_blank" href="'.xtc_href_link('print_product_info.php', 'pID='.$pID.POPUP_PRODUCT_LINK_PARAMETERS, $request_type).'" class="'.POPUP_PRODUCT_LINK_CLASS.'">'.'<img class="'.$class.'" alt="" src="'.$products_image.'" />'.'</a>';
    }
    return '<a target="_blank" href="'.xtc_href_link('print_product_info.php', 'pID='.$pID.POPUP_PRODUCT_LINK_PARAMETERS.$add_params, $request_type).'" class="'.POPUP_PRODUCT_LINK_CLASS.' '.$class.'">'.$text.'</a>';
  }

  /**
   * getDeliveryDutyInfo
   *
   * @param string $iso2code
   * @return boolean, string
   */
  function getDeliveryDutyInfo($iso2code) {
    $eu_countries_query = xtDBquery("-- includes/classes/main.php
                                     SELECT c.countries_iso_code_2
                                       FROM ".TABLE_COUNTRIES." c
                                       JOIN " . TABLE_ZONES_TO_GEO_ZONES . " gz ON c.countries_id = gz.zone_country_id
                                      WHERE gz.geo_zone_id = 5
                                    ");

    if (xtc_db_num_rows($eu_countries_query, true)) {
      $eu_countries = array ();
      while ($eu_countries_values = xtc_db_fetch_array($eu_countries_query, true)) {
        $eu_countries[] = $eu_countries_values['countries_iso_code_2'];
      }
    }

    if (!in_array($iso2code, $eu_countries)) {
      return true;
    }
    return '';
  }
  
  /**
   * get all attributes information
   *
   * @param integer $products_id
   * @param integer $option_id
   * @param integer $value_id
   * @param string $add_select
   * @param string $left_join
   *
   * @return array
   */
  function getAttributes($products_id, $option_id, $value_id, $add_select= '',$left_join='') {
    $attributes = xtc_db_query("-- shopping_cart.php
                                  SELECT $add_select
                                         popt.products_options_name,
                                         poval.products_options_values_name,
                                         pa.*
                                    FROM ".TABLE_PRODUCTS_OPTIONS." popt
                               LEFT JOIN ".TABLE_PRODUCTS_ATTRIBUTES." pa
                                      ON popt.products_options_id = pa.options_id
                               LEFT JOIN ".TABLE_PRODUCTS_OPTIONS_VALUES." poval
                                      ON pa.options_values_id = poval.products_options_values_id
                                         $left_join
                                   WHERE pa.products_id = ".(int)$products_id."
                                     AND pa.options_id = ".(int)$option_id."
                                     AND pa.options_values_id = ".(int)$value_id."
                                     AND popt.language_id = ".(int) $_SESSION['languages_id']."
                                     AND poval.language_id = ".(int) $_SESSION['languages_id']);
    return xtc_db_fetch_array($attributes);  
  }
}
?>