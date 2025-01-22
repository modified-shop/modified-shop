<?php
  /* --------------------------------------------------------------
   $Id: orders_edit.php,v 1.0

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(orders.php,v 1.27 2003/02/16); www.oscommerce.com 
   (c) 2003	 nextcommerce (orders.php,v 1.7 2003/08/14); www.nextcommerce.org
   (c) 2003 XT-Commerce

   Released under the GNU General Public License 
   --------------------------------------------------------------
   Third Party contribution:

   XTC-Bestellbearbeitung:
   http://www.xtc-webservice.de / Matthias Hinsche
   info@xtc-webservice.de

   Released under the GNU General Public License
  --------------------------------------------------------------*/
  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

  define('ATTR_EQ_PREFIX', (defined('MODULE_PRICE_WEIGHT_PREFIX_STATUS') && MODULE_PRICE_WEIGHT_PREFIX_STATUS == 'true'  ? true : false));

  $prefix_array = array(
    array('id' => '+', 'text' => '&nbsp;+&nbsp;'),
    array('id' => '-', 'text' => '&nbsp;-&nbsp;')
  );
  
  if (ATTR_EQ_PREFIX === true) {
    $prefix_array[] = array('id' => '=', 'text' => '&nbsp;=&nbsp;');
  }

  $attributes_query = xtc_db_query("SELECT * 
                                      FROM " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " 
                                     WHERE orders_id = '" . (int)$_GET['oID'] . "' 
                                       AND orders_products_id = '" . (int)$_GET['opID'] . "'");
  if (xtc_db_num_rows($attributes_query) > 0) {
    ?>
    <!-- Optionsbearbeitung Anfang //-->
    <table class="tableBoxCenter collapse">
      <tr class="dataTableHeadingRow">
        <td class="dataTableHeadingContent"><b><?php echo TEXT_PRODUCT_OPTION;?></b></td>
        <td class="dataTableHeadingContent"><b><?php echo TEXT_PRODUCT_OPTION_VALUE;?></b></td>
        <td class="dataTableHeadingContent txta-c"><b><?php echo TEXT_WEIGHT_PREFIX;?></b></td>
        <td class="dataTableHeadingContent txta-c"><b><?php echo TEXT_WEIGHT;?></b></td>
        <td class="dataTableHeadingContent txta-c"><b><?php echo TEXT_PRICE_PREFIX;?></b></td>
        <td class="dataTableHeadingContent"><b><?php echo TEXT_PRICE . TEXT_SMALL_NETTO;?></b></td>
        <td class="dataTableHeadingContent">&nbsp;</td>
      </tr>
      <?php
        while($attributes = xtc_db_fetch_array($attributes_query)) {
          echo xtc_draw_form('product_option_edit', FILENAME_ORDERS_EDIT, 'action=product_option', 'post');
            echo xtc_draw_hidden_field('oID', (int)$_GET['oID']);
            echo xtc_draw_hidden_field('opID', (int)$_GET['opID']);
            echo xtc_draw_hidden_field('pID', (int)$_GET['pID']);
            echo xtc_draw_hidden_field('opAID', (int)$attributes['orders_products_attributes_id']);
            ?>
            <tr class="dataTableRow">
              <td class="dataTableContent"><?php echo xtc_draw_input_field('products_options', $attributes['products_options'], 'size="20"');?></td>
              <td class="dataTableContent"><?php echo xtc_draw_input_field('products_options_values', $attributes['products_options_values'], 'size="20"');?></td>
              <td class="dataTableContent txta-c"><?php echo xtc_draw_pull_down_menu('weight_prefix', $prefix_array, $attributes['weight_prefix']); ?></td>
              <td class="dataTableContent"><?php echo xtc_draw_input_field('options_values_weight',$attributes['options_values_weight'], 'size="10"');?></td>
              <td class="dataTableContent txta-c"><?php echo xtc_draw_pull_down_menu('price_prefix', $prefix_array, $attributes['price_prefix']); ?></td>
              <td class="dataTableContent"><?php echo xtc_draw_input_field('options_values_price',$attributes['options_values_price'], 'size="10"');?></td>
              <td class="dataTableContent txta-c">
                <?php
                  echo '<input type="submit" name="product_option_edit" class="button" onclick="this.blur();" value="' . BUTTON_SAVE . '"/>';
                  echo '<input type="submit" name="product_option_delete" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"/>';
                ?>
              </td>
            </tr>
          </form>
          <?php
        }
      ?>
    </table>
    <br /><br />
    <!-- Optionsbearbeitung Ende //-->
    <?php
  }

  $attributes_query = xtc_db_query("SELECT *
                                      FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
                                     WHERE products_id = '" . (int)$_GET['pID'] . "'
                                  ORDER BY sortorder");
  ?>
  <!-- Artikel Einfügen Anfang //-->
  <table class="tableBoxCenter collapse">
    <tr class="dataTableHeadingRow">
      <td class="dataTableHeadingContent"><b><?php echo TEXT_PRODUCT_ID;?></b></td>
      <td class="dataTableHeadingContent"><b><?php echo TEXT_PRODUCT_OPTION;?></b></td>
      <td class="dataTableHeadingContent"><b><?php echo TEXT_PRODUCT_OPTION_VALUE;?></b></td>
      <td class="dataTableHeadingContent"><b><?php echo TEXT_PRODUCTS_QTY;?></b></td>                                                                                                                                                 
      <td class="dataTableHeadingContent"><b><?php echo TEXT_PRICE . TEXT_SMALL_NETTO;?></b></td>
      <td class="dataTableHeadingContent">&nbsp;</td>
    </tr>
    <?php
    while ($attributes = xtc_db_fetch_array($attributes_query)) {
      echo xtc_draw_form('product_option_ins', FILENAME_ORDERS_EDIT, 'action=product_option_ins', 'post');
        echo xtc_draw_hidden_field('oID', (int)$_GET['oID']);
        echo xtc_draw_hidden_field('opID', (int)$_GET['opID']);
        echo xtc_draw_hidden_field('pID', (int)$_GET['pID']);
        echo xtc_draw_hidden_field('aID', (int)$attributes['products_attributes_id']);
        echo xtc_draw_hidden_field('options_values_price', $attributes['options_values_price']);
        ?>
        <tr class="dataTableRow">
          <td class="dataTableContent"><?php echo $attributes['products_attributes_id'];?></td>
          <td class="dataTableContent"><?php echo xtc_oe_get_options_name($attributes['options_id']);?></td>
          <td class="dataTableContent"><?php echo xtc_oe_get_options_values_name($attributes['options_values_id']);?></td>
          <td class="dataTableContent"><?php echo $attributes['attributes_stock'];?></td>                                                                                                                                                       
          <td class="dataTableContent"><?php echo $xtPrice->xtcFormat($xtPrice->xtcCalculateCurr(xtc_round($attributes['options_values_price'], PRICE_PRECISION)), true);?></td>
          <td class="dataTableContent txta-c">
            <?php
              echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_INSERT . '"/>';
            ?>
          </td>
        </tr>
      </form>
      <?php
    }
    ?>
  </table>
  <br /><br />
  <!-- Artikel Einfügen Ende //-->
