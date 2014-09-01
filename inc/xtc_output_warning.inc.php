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
  // BOF - Dokuman - 2009-05-27 - display errors only to admins in DIV-Element
  //  new errorBox(array(array('text' => '<table style="width: 100%;"><tr><td style="vertical-align: center; padding-left: 5px;">' . xtc_image(DIR_WS_ICONS . 'output_warning.gif', ICON_WARNING) . ' </td><td style="vertical-align: center; text-align: center;"> ' . $warning . '</td></tr></table>')));

  $result = '';

  if (isset($_SESSION['customers_status']['customers_status_id']) && $_SESSION['customers_status']['customers_status_id'] == '0' ) {
    $result = '<div class="errormessage">' . xtc_image(DIR_WS_ICONS . 'output_warning.gif', ICON_WARNING) . $warning . '</div>';
  }
  echo $result;
  // EOF - Dokuman - 2009-05-27 - display errors only to admins in DIV-Element
  }
 ?>