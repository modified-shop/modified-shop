<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

  if (defined('MODULE_TS_TRUSTEDSHOPS_ID')
      && $shop_is_offline === false
      && ((MODULE_TS_REVIEW_STICKER != '' && MODULE_TS_REVIEW_STICKER_STATUS == '1') 
           || (MODULE_TS_PRODUCT_STICKER != '' && MODULE_TS_PRODUCT_STICKER_STATUS == '1')
          )
      )
  {  
    echo '<script src="https://integrations.etrusted.com/applications/widget.js/v2" async defer></script>';
  }