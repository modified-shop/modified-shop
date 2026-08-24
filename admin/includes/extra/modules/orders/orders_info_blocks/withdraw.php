<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  if (defined('MODULE_WITHDRAW_STATUS')
      && MODULE_WITHDRAW_STATUS == 'true'
      )
  {
    $orders_withdraw_products_query = xtc_db_query("SELECT op.*,
                                                           ow.*,
                                                           owp.products_quantity
                                                      FROM ".TABLE_ORDERS_PRODUCTS." op
                                                      JOIN ".TABLE_ORDERS_WITHDRAW." ow
                                                           ON ow.orders_id = op.orders_id
                                                      JOIN ".TABLE_ORDERS_WITHDRAW_PRODUCTS." owp
                                                           ON owp.orders_withdraw_id = ow.orders_withdraw_id
                                                              AND owp.orders_products_id = op.orders_products_id
                                                     WHERE op.orders_id = '".(int)$oID."'");
    if (xtc_db_num_rows($orders_withdraw_products_query) > 0) {
      ?>
      <div id="withdraw_block">
      <div class="heading"><?php echo TABLE_HEADING_WITHDRAW; ?></div>
      <table cellspacing="0" cellpadding="2" class="table">
        <tr class="dataTableHeadingRow">
          <td class="dataTableHeadingContent" colspan="2"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
          <td class="dataTableHeadingContent" style="width:20%"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></td>
          <td class="dataTableHeadingContent" style="width:15%"><?php echo TABLE_HEADING_DATE; ?></td>
        </tr>
        <?php
        while ($orders_withdraw_products = xtc_db_fetch_array($orders_withdraw_products_query)) {
          echo '<tr class="dataTableRow">'.PHP_EOL;
          echo '  <td class="dataTableContent" valign="top" align="right">'.$orders_withdraw_products['products_quantity'].'&nbsp;x&nbsp;</td>'.PHP_EOL;
          echo '  <td class="dataTableContent" valign="top">'.$orders_withdraw_products['products_name'];
          $attributes_query = xtc_db_query("SELECT *
                                              FROM ".TABLE_ORDERS_PRODUCTS_ATTRIBUTES."
                                             WHERE orders_id = '".(int)$oID."'
                                               AND orders_products_id = '".$orders_withdraw_products['orders_products_id']."'");
          if (xtc_db_num_rows($attributes_query) > 0) {
            while ($attribtues = xtc_db_fetch_array($attributes_query)) {
              echo '<br /><nobr><i>&nbsp; - '.$attribtues['products_options'].': '.$attribtues['products_options_values'].'</i></nobr> ';
            }
          }
          echo '  </td>'.PHP_EOL;
          echo '  <td class="dataTableContent" valign="top">'.$orders_withdraw_products['products_model'].'</td>'.PHP_EOL;
          echo '  <td class="dataTableContent" valign="top">'.xtc_datetime_short($orders_withdraw_products['date_added']).'</td>'.PHP_EOL;
          echo '</tr>'.PHP_EOL;
        }
        ?>
      </table>
      </div>
      <?php
    }
  }
