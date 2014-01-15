<?php
/* --------------------------------------------------------------
   $Id: group_prices.php 5442 2013-08-28 11:00:33Z Tomcraft $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(based on original files from OSCommerce CVS 2.2 2002/08/28 02:14:35); www.oscommerce.com
   (c) 2003 nextcommerce (group_prices.php,v 1.16 2003/08/21); www.nextcommerce.org
   (c) 2006 xt-commerce (group_prices.php 1307 2005-10-14); www.xt-commerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------
   based on Third Party contribution:
   Customers Status v3.x  (c) 2002-2003 Copyright Elari elari@free.fr | www.unlockgsm.com/dload-osc/ | CVS : http://cvs.sourceforge.net/cgi-bin/viewcvs.cgi/elari/?sortby=date#dirlist

   Released under the GNU General Public License
   --------------------------------------------------------------*/
   
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once (DIR_FS_INC.'xtc_get_tax_rate.inc.php');

require (DIR_FS_CATALOG.DIR_WS_CLASSES.'xtcPrice.php');
$xtPrice = new xtcPrice(DEFAULT_CURRENCY, $_SESSION['customers_status']['customers_status_id']);

$group_array = array();
$group_query = xtc_db_query("SELECT customers_status_image,
                                    customers_status_id,
                                    customers_status_name
                               FROM ".TABLE_CUSTOMERS_STATUS."
                              WHERE language_id = '".$_SESSION['languages_id']."'
                              AND customers_status_id != '0'");
while ($group_values = xtc_db_fetch_array($group_query)) {
  // load data into array
  $group_array[] = array ('STATUS_NAME' => $group_values['customers_status_name'],
                           'STATUS_IMAGE' => $group_values['customers_status_image'],
                           'STATUS_ID' => $group_values['customers_status_id']);
}
?>
<div class="main div_header"><?php echo HEADING_PRICES_OPTIONS; ?></div>
<table class="tableInput">
  <tr>
    <td style="width:140px;" class="main"><?php echo TEXT_PRODUCTS_PRICE; ?></td>
      <?php
      // calculate brutto price for display
      if (PRICE_IS_BRUTTO == 'true') {
        $products_price = xtc_round($pInfo->products_price * ((100 + xtc_get_tax_rate($pInfo->products_tax_class_id)) / 100), PRICE_PRECISION);
      } else {
        $products_price = xtc_round($pInfo->products_price, PRICE_PRECISION);
      }
      ?>
    <td class="main" style="width:160px;"><?php echo xtc_draw_input_field('products_price', $products_price); ?></td>
    <td class="main" style="width:100px; white-space: nowrap;">
      <?php
      if (PRICE_IS_BRUTTO == 'true') {
        echo TEXT_NETTO.'<strong>'.$xtPrice->xtcFormat($pInfo->products_price, false).'</strong>  ';
      }
      ?>
    </td>
  </tr>
<?php
foreach($group_array as $group_data) {
?>
  <tr>
    <td style="border-top: 1px solid; border-color: #cccccc;" class="main"><?php echo $group_data['STATUS_NAME']; ?></td>
      <?php
        if (PRICE_IS_BRUTTO == 'true') {
          $products_price = xtc_round(get_group_price($group_data['STATUS_ID'], $pInfo->products_id) * ((100 + xtc_get_tax_rate($pInfo->products_tax_class_id)) / 100), PRICE_PRECISION);
        } else {
          $products_price = xtc_round(get_group_price($group_data['STATUS_ID'], $pInfo->products_id), PRICE_PRECISION);
        }
      ?>
    <td style="border-top: 1px solid; border-color: #cccccc;" class="main">
      <?php
        echo xtc_draw_input_field('products_price_'.$group_data['STATUS_ID'], $products_price);
      ?>
    </td>
    <td style="border-top: 1px solid; border-color: #cccccc; white-space: nowrap;" class="main">
      <?php
        if (PRICE_IS_BRUTTO == 'true' && get_group_price($group_data['STATUS_ID'], $pInfo->products_id) != '0') {
          echo TEXT_NETTO.'<strong>'.$xtPrice->xtcFormat(get_group_price($group_data['STATUS_ID'], $pInfo->products_id), false).'</strong>';
        }
      ?>
    </td>
    <td style="border-top: 1px solid; border-color: #cccccc;" class="main">
      <?php
        echo TXT_STAFFELPREIS;

        // ok, lets check if there is already a staffelpreis
        $staffel_query = xtc_db_query("SELECT price_id,
                                              products_id,
                                              quantity,
                                              personal_offer
                                         FROM personal_offers_by_customers_status_".$group_data['STATUS_ID']."
                                        WHERE products_id = '".$pInfo->products_id."'
                                          AND quantity != '1'
                                     ORDER BY quantity ASC");
        ?> 
        <img onMouseOver="javascript:this.style.cursor='pointer';" src="images/<?php echo ((xtc_db_num_rows($staffel_query) > 0) ? 'arrow_down_green.gif' : 'arrow_down.gif'); ?>" height="16" width="16" onclick="javascript:toggleBox('staffel_<?php echo $group_data['STATUS_ID']; ?>');" style="vertical-align: middle;">
        <div id="staffel_<?php echo $group_data['STATUS_ID']; ?>" class="longDescription">
          <table class="tableConfig borderall">
            <tr>
              <td width="55px"><b><?php echo TXT_STK; ?></b></td>
              <td><b><?php echo TXT_STAFFELPREIS; ?></b></td>
              <td width="55px"><b><?php echo BUTTON_DELETE; ?></b></td>
            </tr>
            <?php
            $count = 0;
            while ($staffel_values = xtc_db_fetch_array($staffel_query)) {
              ?>
              <tr>
                <td class="main"><?php echo xtc_draw_input_field('products_staffel['.$group_data['STATUS_ID'].']['.$count.'][quantity]', $staffel_values['quantity'], 'style="width:50px;"'); ?></td>            
                <td class="main">
                  <?php
                  if (PRICE_IS_BRUTTO == 'true') {
                    $tax_query = xtc_db_query("select tax_rate from ".TABLE_TAX_RATES." where tax_class_id = '".$pInfo->products_tax_class_id."' ");
                    $tax = xtc_db_fetch_array($tax_query);
                    $products_price = xtc_round($staffel_values['personal_offer'] * ((100 + $tax['tax_rate']) / 100), PRICE_PRECISION);
                  } else {
                    $products_price = xtc_round($staffel_values['personal_offer'], PRICE_PRECISION);
                  }
                  echo xtc_draw_input_field('products_staffel['.$group_data['STATUS_ID'].']['.$count.'][personal_offer]', $products_price);
                  if (PRICE_IS_BRUTTO == 'true') {
                    echo '&nbsp;'.TEXT_NETTO.'<strong>'.$xtPrice->xtcFormat($staffel_values['personal_offer'], false).'</strong>';
                  }
                  echo xtc_draw_hidden_field('products_staffel['.$group_data['STATUS_ID'].']['.$count.'][price_id]', $staffel_values['price_id']);
                  ?>
                </td>
                <td class="main" align="center"><?php echo xtc_draw_checkbox_field('products_staffel['.$group_data['STATUS_ID'].']['.$count.'][delete]'); ?></td>
              </tr>          
              <?php
              $count++;
            }
            $max_staffel = MIN_GROUP_PRICE_STAFFEL;
            if ($count >= $max_staffel) $max_staffel=$count+1;
            for ($is=$count; $is<$max_staffel; $is++) {
            ?>
            <tr>
              <td class="main"><?php echo xtc_draw_input_field('products_staffel['.$group_data['STATUS_ID'].']['.$is.'][quantity]', '', 'style="width:50px;"'); ?></td>            
              <td class="main"><?php echo xtc_draw_input_field('products_staffel['.$group_data['STATUS_ID'].']['.$is.'][personal_offer]', ''); ?></td>
              <td class="main"></td>
            </tr>
            <?php
            }
            ?>
        </table>
      </div>
    </td>
  </tr>
<?php
}
?>
  <tr>
    <td style="border-top: 1px solid; border-color: #cccccc;" colspan="4"><?php echo xtc_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
  </tr>
  <tr>
    <td class="main"><?php echo TEXT_PRODUCTS_DISCOUNT_ALLOWED; ?></td>
    <td class="main" colspan="3"><?php echo xtc_draw_input_field('products_discount_allowed', $pInfo->products_discount_allowed); ?></td>
  </tr>
  <tr>
    <td class="main"><?php echo TEXT_PRODUCTS_TAX_CLASS; ?></td>
    <td class="main"><?php echo xtc_draw_pull_down_menu('products_tax_class_id', $tax_class_array, $pInfo->products_tax_class_id); ?></td>
  </tr>
</table>