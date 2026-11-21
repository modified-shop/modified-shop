<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_get_tax_description.inc.php); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  // include needed function
  require_once(DIR_FS_INC.'parse_multi_language_value.inc.php');
  require_once(DIR_FS_INC.'parse_multi_language_html.inc.php');

  function xtc_get_tax_description($class_id, $country_id= -1, $zone_id= -1) {
    global $PHP_SELF;
    static $tax_description_array;
    
    if (!isset($tax_description_array)) {
      $tax_description_array = array();
    }
        
    if (isset($_SESSION['country']) && strpos(basename($PHP_SELF), 'checkout') === false) {
      $country_id = $_SESSION['country'];
    }
  	
    if ($country_id == -1 && $zone_id == -1) {
      if (!isset($_SESSION['customer_id'])) {
        $country_id = STORE_COUNTRY;
        $zone_id = STORE_ZONE;
      } else {
        $country_id = $_SESSION['customer_country_id'];
        $zone_id = $_SESSION['customer_zone_id'];
      }
    }
  	
  	if (!isset($tax_description_array[$country_id][$zone_id][$class_id])) {
      $where = "AND (z2gz.zone_id = 0 ";
      if ($zone_id >= 0) {
        $where .= "OR z2gz.zone_id = '" . (int)$zone_id . "'";
      }
      $where .= ")";

      $tax_query = xtDBquery("SELECT sum(tax_rate) as tax_rate,
                                     tr.tax_description
                                FROM " . TABLE_TAX_RATES . " tr 
                                JOIN " . TABLE_GEO_ZONES . " tz 
                                     ON tz.geo_zone_id = tr.tax_zone_id
                                JOIN " . TABLE_ZONES_TO_GEO_ZONES . " z2gz 
                                     ON tr.tax_zone_id = z2gz.geo_zone_id
                                        AND z2gz.zone_country_id = '" . (int)$country_id . "'
                                        ".$where."
                               WHERE tr.tax_class_id = '" . (int)$class_id . "' 
                            GROUP BY tr.tax_priority");
      if (xtc_db_num_rows($tax_query,true)) {
        $tax_description = '';
        $tax_description_html = '';
        while ($tax = xtc_db_fetch_array($tax_query,true)) {
          $tax_description .= parse_multi_language_value($tax['tax_description'], $_SESSION['language_code']) . ' + ';
          $tax_description_html .= parse_multi_language_html_value($tax['tax_description'], $_SESSION['language_code']) . ' + ';
        }
        $tax_description = substr($tax_description, 0, -3);
        xtc_get_tax_description_html($tax_description, substr($tax_description_html, 0, -3));

        $tax_description_array[$country_id][$zone_id][$class_id] = $tax_description;
      } else {
        $tax_description_array[$country_id][$zone_id][$class_id] = TEXT_UNKNOWN_TAX_RATE;
      }
    }
    
    return $tax_description_array[$country_id][$zone_id][$class_id];
  }

  /**
   * xtc_get_tax_description_html()
   *
   * the text for a page that belongs to a description of xtc_get_tax_description(): the description is the key
   * of the tax group and is searched for the tax rate, so it stays the decoded text
   *
   * @param string $key description, also with TAX_ADD_TAX or TAX_NO_TAX in front
   * @param string $html text for a page, set by xtc_get_tax_description()
   * @return string
   */
  function xtc_get_tax_description_html($key, $html = null) {
    static $html_array = array();

    if (!is_string($key)) {
      return $key;
    }
    if ($html !== null) {
      if ($html !== $key) {
        // two rates can share a key, their text must not depend on which one comes first
        $merged = ((isset($html_array[$key])) ? xtc_merge_tax_description_html($key, $html_array[$key], $html) : $html);
        if ($merged === false) {
          $merged = ((strcmp($html_array[$key], $html) >= 0) ? $html_array[$key] : $html);
        }
        $html_array[$key] = $merged;
      }
      return $html;
    }
    foreach (array('', ((defined('TAX_ADD_TAX')) ? TAX_ADD_TAX : ''), ((defined('TAX_NO_TAX')) ? TAX_NO_TAX : '')) as $prefix) {
      if ($prefix == '' || strpos($key, $prefix) === 0) {
        $description = substr($key, strlen($prefix));
        if (isset($html_array[$description])) {
          return $prefix.$html_array[$description];
        }
      }
    }

    return $key;
  }

  /**
   * xtc_merge_tax_description_html()
   *
   * one text for a page from two texts that belong to the same description: an & that one of them
   * keeps encoded stays encoded
   *
   * @param string $key description
   * @param string $one text for a page, the description with some & written as &amp;
   * @param string $two the same for another rate
   * @return mixed text, false when one of the two is not the description with some & encoded
   */
  function xtc_merge_tax_description_html($key, $one, $two) {
    $merged = '';
    $a = 0;
    $b = 0;
    for ($i = 0, $n = strlen($key); $i < $n; $i++) {
      $piece = $key[$i];
      // an & in front of amp; is a decoded &amp; and encoded in no text
      if ($piece == '&' && substr($key, $i, 5) != '&amp;') {
        $kept_one = (substr($one, $a, 5) == '&amp;');
        $kept_two = (substr($two, $b, 5) == '&amp;');
        if ($kept_one || $kept_two) {
          $piece = '&amp;';
        }
        $a += (($kept_one) ? 4 : 0);
        $b += (($kept_two) ? 4 : 0);
      }
      $merged .= $piece;
      $a++;
      $b++;
    }
    if ($a != strlen($one) || $b != strlen($two)) {
      return false;
    }

    return $merged;
  }
