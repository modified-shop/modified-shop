<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  if (defined('MODULE_PRODUCTS_TARIFF_STATUS')
      && MODULE_PRODUCTS_TARIFF_STATUS == 'true'
      )
  {
    $countries_array = array(array('id' => '', 'text' => TEXT_NONE));
    $countries_query = xtc_db_query("SELECT countries_iso_code_2,
                                            countries_name
                                       FROM ".TABLE_COUNTRIES."
                                   ORDER BY countries_name");
    while ($countries = xtc_db_fetch_array($countries_query)) {
      $countries_array[] = array('id' => $countries['countries_iso_code_2'], 'text' => $countries['countries_name']);
    }
    ?>
    <div style="clear:both;"></div>
    <div class="main div_header"><b><?php echo TEXT_PRODUCTS_TARIFF_HEADING; ?></b></div>
    <div class="clear div_box mrg5">
      <table class="tableInput border0">
        <tr>
          <td style="width:250px; line-height: 35px;"><span class="main"><?php echo TEXT_PRODUCTS_TARIFF; ?></span></td>
          <td><span class="main"><?php echo xtc_draw_input_field('products_tariff', $pInfo->products_tariff, 'style="width: 155px"'); ?></span></td>
        </tr>
        <tr>
          <td><span class="main"><?php echo TEXT_PRODUCTS_ORIGIN; ?></span></td>
          <td><span class="main"><?php echo xtc_draw_pull_down_menu('products_origin', $countries_array, $pInfo->products_origin); ?></span></td>
        </tr>
        <tr>
          <td><span class="main"><?php echo TEXT_PRODUCTS_TARIFF_TITLE; ?></span></td>
          <td><span class="main"><?php echo xtc_draw_input_field('products_tariff_title', $pInfo->products_tariff_title, 'style="width: 100%"'); ?></span></td>
        </tr>
      </table>
    </div>
    <?php
  }