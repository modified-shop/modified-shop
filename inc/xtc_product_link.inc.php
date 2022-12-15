<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2005 XT-Commerce


   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function xtc_product_link($pID, $name = '') {
    $params = 'products_id='.$pID;
    if (SEARCH_ENGINE_FRIENDLY_URLS == 'true' && $name != '') {
      $params .= '&name='.base64_encode(strip_tags($name));
    }

    return $params;
  }
