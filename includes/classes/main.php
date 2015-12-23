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
  function __construct() {
    
    //new module support
    require_once (DIR_FS_CATALOG.'includes/classes/mainModules.class.php');
    $this->mainModules = new mainModules();
    
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
  function getShippingStatusName($id, $link=false) {
    global $request_type;
    if (!defined('SHIPPING_STATUS_INFOS') || $link === false) {
      return (isset($this->SHIPPING[$id]['name']) ? $this->SHIPPING[$id]['name'] : '');
    }
    $link_parameters = defined('TPL_POPUP_SHIPPING_LINK_PARAMETERS') ? TPL_POPUP_SHIPPING_LINK_PARAMETERS : POPUP_SHIPPING_LINK_PARAMETERS;
    $link_class = defined('TPL_POPUP_SHIPPING_LINK_CLASS') ? TPL_POPUP_SHIPPING_LINK_CLASS : POPUP_SHIPPING_LINK_CLASS;
    return '<a rel="nofollow" target="_blank" href="'.xtc_href_link(FILENAME_POPUP_CONTENT, 'coID='.SHIPPING_STATUS_INFOS.$link_parameters, $request_type).'" title="Information" class="'.$link_class.'">'.(isset($this->SHIPPING[$id]['name']) ? $this->SHIPPING[$id]['name'] : '').'</a>';
  }

  /**
   * getShippingStatusImage
   *
   * @param integer $id
   * @return  string
   */
  function getShippingStatusImage($id) {
    if (isset($this->SHIPPING[$id]['image']) && $this->SHIPPING[$id]['image'] != '') {
      return DIR_WS_CATALOG.DIR_WS_IMAGES.$this->SHIPPING[$id]['image'];
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
    $link_parameters = defined('TPL_POPUP_SHIPPING_LINK_PARAMETERS') ? TPL_POPUP_SHIPPING_LINK_PARAMETERS : POPUP_SHIPPING_LINK_PARAMETERS;
    $link_class = defined('TPL_POPUP_SHIPPING_LINK_CLASS') ? TPL_POPUP_SHIPPING_LINK_CLASS : POPUP_SHIPPING_LINK_CLASS;
    
    if (SHOW_SHIPPING == 'true') {
      return ' '.((SHOW_SHIPPING_EXCL == 'false') ? SHIPPING_INCL : SHIPPING_EXCL).' <a rel="nofollow" target="_blank" href="'.xtc_href_link(FILENAME_POPUP_CONTENT, 'coID='.SHIPPING_INFOS.$link_parameters, $request_type).'" title="Information" class="'.$link_class.'">'.SHIPPING_COSTS.'</a>';
    }
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
    
    if (MODULE_SMALL_BUSINESS == 'true') {
      $tax_info = TAX_INFO_SMALL_BUSINESS;
    }
    
    //new module support
    $tax_info = $this->mainModules->getTaxInfo($tax_info,$tax_rate);
    
    return $tax_info;
  }

  /**
   * getShippingNotice
   *
   * @return string
   */
  function getShippingNotice() {
    $shippingNotice = '';
    if (SHOW_SHIPPING == 'true') {
      $shippingNotice = ' '.SHIPPING_EXCL.'<a href="'.xtc_href_link(FILENAME_CONTENT, 'coID='.SHIPPING_INFOS).'">'.SHIPPING_COSTS.'</a>';
    }
    //new module support
    $shippingNotice = $this->mainModules->getShippingNotice($shippingNotice);
    
    return $shippingNotice;
  }

  /**
   * getContentLink
   *
   * @param integer $coID
   * @param string $text, $ssl
   * @return string
   */
  function getContentLink($coID, $text, $ssl = 'NONSSL', $class_more = true) {
    if (!defined('POPUP_CONTENT_LINK_PARAMETERS')) {
      define('POPUP_CONTENT_LINK_PARAMETERS', '&KeepThis=true&TB_iframe=true&height=400&width=600');
    }
    if (!defined('POPUP_CONTENT_LINK_CLASS')) {
      define('POPUP_CONTENT_LINK_CLASS', 'thickbox');
    }
    $link_parameters = defined('TPL_POPUP_CONTENT_LINK_PARAMETERS') ? TPL_POPUP_CONTENT_LINK_PARAMETERS : POPUP_CONTENT_LINK_PARAMETERS;
    $link_class = defined('TPL_POPUP_CONTENT_LINK_CLASS') ? TPL_POPUP_CONTENT_LINK_CLASS : POPUP_CONTENT_LINK_CLASS;
    
    $contentLink = '<a target="_blank" href="'.xtc_href_link(FILENAME_POPUP_CONTENT, 'coID='.$coID.$link_parameters, $ssl).'" title="Information" class="'.(($class_more === true) ? 'color_more ' : '').$link_class.'">'.$text.'</a>';
    
    //new module support
    $contentLink = $this->mainModules->getShippingNotice($contentLink, $coID, $text, $ssl, $class_more);
    
    return $contentLink;
  }
  
  /**
   * getContentData
   *
   * @param integer $coID
   * @return array
   */
  function getContentData($coID, $lang_id = '', $customers_status = '', $get_inactive = true, $add_select= '') {
    $lang_id = !empty($lang_id) ? $lang_id : $_SESSION['languages_id'];
    $customers_status = $customers_status != '' ? $customers_status : $_SESSION['customers_status']['customers_status_id'];
    $group_check = (GROUP_CHECK == 'true') ? "AND group_ids LIKE '%c_" . (int)$customers_status . "_group%'" : '';
    $where = (($get_inactive === true) ? '' : " AND content_active = '1'");
    $content_data_query = xtDBquery("-- includes/classes/main.php
                                       SELECT ".$add_select."
                                              content_id,
                                              content_title,
                                              content_heading,
                                              content_text,
                                              content_file
                                         FROM " . TABLE_CONTENT_MANAGER . "
                                        WHERE content_group='". (int)$coID ."'
                                              " . $group_check . "
                                              " . $where . "
                                          AND trim(content_title) != ''
                                          AND languages_id='" . (int)$lang_id . "'
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
    
    //new module support
    $content_data_array = $this->mainModules->getContentData($content_data_array, $coID, $lang_id, $customers_status, $get_inactive, $add_select);
    
    return $content_data_array;    
  }

  /**
   * getVPEtext
   *
   * @param unknown_type $product
   * @param unknown_type $price
   * @return unknown
   */
  function getVPEtext($products, $price) {
    global $xtPrice, $product;
    $vpeText = '';
    require_once (DIR_FS_INC.'xtc_get_vpe_name.inc.php');
    if (!is_array($products)) {
      $products = $product->data;
    }
    $this->vpe_name = '';
    if (isset($products['products_vpe_status']) && $products['products_vpe_status'] == 1 && $products['products_vpe_value'] != 0.0 && $price > 0) {
      $this->vpe_name = xtc_get_vpe_name($products['products_vpe']);
      //echo $this->vpe_name; //only for debugging
      $vpeText = $xtPrice->xtcFormat($price * (1 / $products['products_vpe_value']), true).TXT_PER.$this->vpe_name;
    }
    
    //new module support
    $vpeText = $this->mainModules->getVPEtext($vpeText, $products, $price, $this->vpe_name);
    
    return $vpeText;
  }

  /**
   * getProductPopupLink
   *
   * @param integer $pID
   * @param string $text, $class
   * @return string
   */
  function getProductPopupLink($pID, $text, $class = '', $add_params = '') {
    global $request_type;
    $productPopupLink = '';
    if (!defined('POPUP_PRODUCT_LINK_PARAMETERS')) {
      define('POPUP_PRODUCT_LINK_PARAMETERS', '&KeepThis=true&TB_iframe=true&height=450&width=750');
    }
    if (!defined('POPUP_PRODUCT_LINK_CLASS')) {
      define('POPUP_PRODUCT_LINK_CLASS', 'thickbox');
    }
    $link_parameters = defined('TPL_POPUP_PRODUCT_LINK_PARAMETERS') ? TPL_POPUP_PRODUCT_LINK_PARAMETERS : POPUP_PRODUCT_LINK_PARAMETERS;
    $link_class = defined('TPL_POPUP_PRODUCT_LINK_CLASS') ? TPL_POPUP_PRODUCT_LINK_CLASS : POPUP_PRODUCT_LINK_CLASS;
    if ($class == 'image') {
      require_once (DIR_FS_INC . 'xtc_get_products_image.inc.php');
      $products_image = xtc_get_products_image($pID);
      $product = new product($pID);
      $products_image = $product->productImage($products_image, 'thumbnail');      
      $productPopupLink = '<a target="_blank" href="'.xtc_href_link('print_product_info.php', 'pID='.$pID.$link_parameters, $request_type).'" class="'.$link_class.'">'.'<img class="'.$class.'" alt="" src="'.$products_image.'" />'.'</a>';
    } else {
      $productPopupLink = '<a target="_blank" href="'.xtc_href_link('print_product_info.php', 'pID='.$pID.$link_parameters.$add_params, $request_type).'" class="'.$link_class.' '.$class.'">'.$text.'</a>';
    }
    
    //new module support
    $productPopupLink = $this->mainModules->getProductPopupLink($productPopupLink, $pID, $text, $class, $add_params);
    
    return $productPopupLink;
  }

  /**
   * getDeliveryDutyInfo
   *
   * @param string $iso2code
   * @return boolean, string
   */
  function getDeliveryDutyInfo($iso2code) {
    $countries = array();
    $geo_zone_array = array();

    $geo_zone_query = xtDBquery("SELECT geo_zone_id 
                                   FROM ".TABLE_GEO_ZONES." 
                                  WHERE geo_zone_info = '1'");
    if (xtc_db_num_rows($geo_zone_query, true) > 0) {
      while ($geo_zone = xtc_db_fetch_array($geo_zone_query, true)) {
        $geo_zone_array[] = $geo_zone['geo_zone_id'];
      }
      $duty_countries_query = xtDBquery("SELECT c.countries_iso_code_2
                                           FROM ".TABLE_COUNTRIES." c
                                           JOIN " . TABLE_ZONES_TO_GEO_ZONES . " gz ON c.countries_id = gz.zone_country_id
                                          WHERE gz.geo_zone_id IN ('".implode("', '", $geo_zone_array)."')");
      if (xtc_db_num_rows($duty_countries_query, true)) {
        while ($duty_countries = xtc_db_fetch_array($duty_countries_query, true)) {
          $countries[] = $duty_countries['countries_iso_code_2'];
        }
      }
    }
    
    if (in_array($iso2code, $countries)) {
      return true;
    }
    
    return false;
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
    $attributes = xtc_db_query("SELECT ".$add_select."
                                       popt.products_options_name,
                                       poval.products_options_values_name,
                                       pa.*
                                  FROM ".TABLE_PRODUCTS_ATTRIBUTES." pa
                                  JOIN ".TABLE_PRODUCTS_OPTIONS." popt
                                       ON popt.products_options_id = pa.options_id
                                          AND popt.language_id = '".(int) $_SESSION['languages_id']."'
                                  JOIN ".TABLE_PRODUCTS_OPTIONS_VALUES." poval
                                       ON poval.products_options_values_id = pa.options_values_id
                                          AND poval.language_id = '".(int) $_SESSION['languages_id']."'
                                       ".$left_join."
                                 WHERE pa.products_id = '".(int)$products_id."'
                                   AND pa.options_id = '".(int)$option_id."'
                                   AND pa.options_values_id = '".(int)$value_id."'");
    return xtc_db_fetch_array($attributes);  
  }
}
?>