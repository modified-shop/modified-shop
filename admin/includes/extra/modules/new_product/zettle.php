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
  
  if (defined('MODULE_CATEGORIES_ZETTLE_CATEGORIES_STATUS')
      && MODULE_CATEGORIES_ZETTLE_CATEGORIES_STATUS == 'true'
      )
  {
    $zettle_query = xtc_db_query("SELECT *
                                    FROM ".TABLE_PAYPAL_ZETTLE_TO_PRODUCTS."
                                   WHERE products_id = '".(int)$_GET['pID']."'");
    $zettle = xtc_db_fetch_array($zettle_query);
    ?>
      <div style="clear:both;"></div>
      <div class="main div_header"><b><?php echo TEXT_MODULE_ZETTLE_HEADING; ?></b></div>
      <div class="clear div_box mrg5 cf">
        <div style="float:left; width:57%; vertical-align:top">
          <table class="tableInput border0">
            <tr>
              <td style="width:250px; line-height: 35px;"><span class="main"><?php echo TEXT_MODULE_ZETTLE_STATUS; ?></span></td>
              <td><span class="main"><?php echo draw_on_off_selection('products_zettle_status', 'checkbox', (isset($zettle['products_id']) ? true : false)) ?></span></td>
            </tr>
            <tr>
              <td><span class="main"><?php echo TEXT_MODULE_ZETTLE_STOCK; ?></span></td>
              <td><span class="main"><?php echo draw_on_off_selection('products_zettle_stock', 'checkbox', (isset($zettle['stock']) && $zettle['stock'] == 1 ? true : false)) ?></span></td>
            </tr>
          </table>
        </div>
        <div class="cf" style="float:left;width:43%; vertical-align:top">
          <img style="float:right; margin:10px 10px 0 0; max-width:80px;height:auto;" src="https://cdn.izettle.com/zettle-brand/Zettle_Simple_Positive.svg" />
        </div>
      </div>
      <div style="clear:both;"></div>
    <?php
  }