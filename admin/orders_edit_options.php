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

  $products_query = xtc_db_query("select * from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int)$_GET['oID'] . "' and orders_products_id = '" . (int)$_GET['opID'] . "'");
  $products = xtc_db_fetch_array($products_query);
?>
<!-- Optionsbearbeitung Anfang //-->
<?php
  $attributes_query = xtc_db_query("select * from " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " where orders_id = '" . (int)$_GET['oID'] . "' and orders_products_id = '" . (int)$_GET['opID'] . "'");
?>
<table class="tableBoxCenter collapse">
  <tr class="dataTableHeadingRow">
    <td class="dataTableHeadingContent"><b><?php echo TEXT_PRODUCT_OPTION;?></b></td>
    <td class="dataTableHeadingContent"><b><?php echo TEXT_PRODUCT_OPTION_VALUE;?></b></td>
    <td class="dataTableHeadingContent"><b><?php echo TEXT_PRICE . TEXT_SMALL_NETTO;?></b></td>
    <td class="dataTableHeadingContent txta-c"><b><?php echo TEXT_PRICE_PREFIX;?></b></td>
    <td class="dataTableHeadingContent" colspan="2">&nbsp;</td>
  </tr>
  <?php
    while($attributes = xtc_db_fetch_array($attributes_query)) {
      ?>
      <tr class="dataTableRow">
        <?php
        echo xtc_draw_form('product_option_edit', FILENAME_ORDERS_EDIT, 'action=product_option_edit', 'post');
          echo xtc_draw_hidden_field('oID', (int)$_GET['oID']);
          echo xtc_draw_hidden_field('opID', (int)$_GET['opID']);
          echo xtc_draw_hidden_field('pID', (int)$_GET['pID']);
          echo xtc_draw_hidden_field('opAID', (int)$attributes['orders_products_attributes_id']);
          ?>
          <td class="dataTableContent"><?php echo xtc_draw_input_field('products_options', $attributes['products_options'], 'size="20"');?></td>
          <td class="dataTableContent"><?php echo xtc_draw_input_field('products_options_values', $attributes['products_options_values'], 'size="20"');?></td>
          <td class="dataTableContent"><?php echo xtc_draw_input_field('options_values_price',$attributes['options_values_price'], 'size="10"');?></td>
          <td class="dataTableContent txta-c"><?php echo xtc_draw_pull_down_menu('prefix', $prefix_array, $attributes['price_prefix']); ?></td>
          <td class="dataTableContent">
            <?php
              echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_SAVE . '"/>';
            ?>
          </td>
        </form>
        <td class="dataTableContent">
          <?php
          echo xtc_draw_form('product_option_delete', FILENAME_ORDERS_EDIT, 'action=product_option_delete', 'post');
            echo xtc_draw_hidden_field('oID', (int)$_GET['oID']);
            echo xtc_draw_hidden_field('opID',(int) $_GET['opID']);
            echo xtc_draw_hidden_field('opAID', (int)$attributes['orders_products_attributes_id']);
            echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"/>';
            ?>
          </form>
        </td>
      </tr>
      <?php
    }
  ?>
</table>
<br /><br />
<!-- Optionsbearbeitung Ende //-->
<!-- Artikel Einfügen Anfang //-->
<?php
  $products_query = xtc_db_query("SELECT *
                                    FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
                                   WHERE products_id = '" . (int)$_GET['pID'] . "'
                                ORDER BY sortorder");
?>
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
  while($products = xtc_db_fetch_array($products_query)) {
    ?>
    <tr class="dataTableRow">
      <?php
      echo xtc_draw_form('product_option_ins', FILENAME_ORDERS_EDIT, 'action=product_option_ins', 'post');
        echo xtc_draw_hidden_field('oID', (int)$_GET['oID']);
        echo xtc_draw_hidden_field('opID', (int)$_GET['opID']);
        echo xtc_draw_hidden_field('pID', (int)$_GET['pID']);
        echo xtc_draw_hidden_field('aID', (int)$products['products_attributes_id']);
        $options_values_price = xtc_round($products['options_values_price'], PRICE_PRECISION);
        ?>
        <td class="dataTableContent"><?php echo $products['products_attributes_id'];?></td>
        <td class="dataTableContent"><?php echo xtc_oe_get_options_name($products['options_id']);?></td>
        <td class="dataTableContent"><?php echo xtc_oe_get_options_values_name($products['options_values_id']);?></td>
        <td class="dataTableContent"><?php echo $products['attributes_stock'];?></td>                                                                                                                                                       
        <td class="dataTableContent">
          <?php echo xtc_draw_hidden_field('options_values_price', $products['options_values_price']);?>
          <?php echo $xtPrice->xtcFormat($xtPrice->xtcCalculateCurr($options_values_price),true);?>
        </td>
        <td class="dataTableContent txta-r">
          <?php
            echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_INSERT . '"/>'; //web28 -2011-06-08 - change BUTTON_EDIT to BUTTON_INSERT
          ?>
        </td>
      </form>
    </tr>
    <?php
  }
  ?>
</table>
<br /><br />
<!-- Artikel Einfügen Ende //-->