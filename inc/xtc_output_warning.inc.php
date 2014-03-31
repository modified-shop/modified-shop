<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_output_warning.inc.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com
   (c) 2003   nextcommerce (xtc_output_warning.inc.php,v 1.3 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function xtc_output_warning($warning) {
    $result = '';

    if (isset($_SESSION['customers_status']['customers_status']) && $_SESSION['customers_status']['customers_status'] == '0' ) {
      $result = '<div class="errormessage shopsystem">' . xtc_image(DIR_WS_ICONS . 'output_warning.gif', ICON_WARNING) . $warning . '</div>';
    }
    echo $result;
  }
 ?>