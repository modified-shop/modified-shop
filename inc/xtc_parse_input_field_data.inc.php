<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(html_output.php,v 1.52 2003/03/19); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_parse_input_field_data.inc.php,v 1.3 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  // Parse the data used in the html tags to ensure the tags will not break
  function xtc_parse_input_field_data($data, $parse) {
    if (is_array($data)) {
      foreach ($data as $k => $v) {
        unset($data[$k]);
        $data[xtc_parse_input_field_data($k, $parse)] = xtc_parse_input_field_data($v, $parse);
      }
      return $data;
    } else {
      return strtr(trim($data), $parse);
    }
  }
