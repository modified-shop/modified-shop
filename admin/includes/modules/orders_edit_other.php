<?php
  /* --------------------------------------------------------------
   $Id$

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

?>
<!-- Sprachen Anfang //-->
<?php echo xtc_draw_form('lang_edit', FILENAME_ORDERS_EDIT, 'action=lang_edit', 'post'); ?>
  <table class="tableBoxCenter collapse">
    <tr class="dataTableHeadingRow">
      <td class="dataTableHeadingContent" style="width:250px"><b><?php echo TEXT_LANGUAGE; ?></b></td>
      <td class="dataTableHeadingContent">&nbsp;</td>
    </tr>
    <?php
      $lang_query = xtc_db_query("SELECT * FROM " . TABLE_LANGUAGES);
      while($lang = xtc_db_fetch_array($lang_query)) {
        ?>
        <tr class="dataTableRow">
          <td class="dataTableContent"><?php echo $lang['name'];?></td>
          <td class="dataTableContent"><?php echo xtc_draw_radio_field('lang', $lang['languages_id'], ($lang['directory']==$order->info['language']) ?'checked' : '');?></td>        
        </tr>
        <?php
      }
    ?>
    <tr class="dataTableRow">
      <td class="dataTableContent">&nbsp;</td>
      <td class="dataTableContent">
        <?php
        echo xtc_draw_hidden_field('oID', $_GET['oID']);
        echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_SAVE . '"/>';
        ?>
      </td>
    </tr>
  </table>
</form>
<br />
<!-- Sprachen Ende //-->

<!-- Währungen Anfang //-->
<br />
<?php echo xtc_draw_form('curr_edit', FILENAME_ORDERS_EDIT, 'action=curr_edit', 'post'); ?>
  <table class="tableBoxCenter collapse">
    <tr class="dataTableHeadingRow">
      <td class="dataTableHeadingContent" style="width:250px"><b><?php echo TEXT_CURRENCIES; ?></b></td>
      <td class="dataTableHeadingContent">&nbsp;</td>
    </tr>
    <?php
      $curr_query = xtc_db_query("SELECT * FROM " . TABLE_CURRENCIES);
      while($curr = xtc_db_fetch_array($curr_query)) {
        ?>
        <tr class="dataTableRow">
          <td class="dataTableContent"><?php echo $curr['title'];?></td>
          <td class="dataTableContent"><?php echo xtc_draw_radio_field('currencies_id', $curr['currencies_id'], ($curr['code']==$order->info['currency']) ? 'checked' : '');?></td>        
        </tr>
        <?php
      }
    ?>
    <tr class="dataTableRow">
      <td class="dataTableContent">&nbsp;</td>
      <td class="dataTableContent">
        <?php
        echo xtc_draw_hidden_field('old_currency', $order->info['currency']);
        echo xtc_draw_hidden_field('oID', $_GET['oID']);
        echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_SAVE . '"/>';
        ?>
      </td>
    </tr>
  </table>
</form>
<br />
<!-- Währungen Ende //-->

<!-- Zahlung Anfang //-->
<br />
<?php
  echo xtc_draw_form('payment_edit', FILENAME_ORDERS_EDIT, 'action=payment_edit', 'post');
  echo xtc_draw_hidden_field('oID', $_GET['oID']);

  $payment_array = array();
  $payment_array[-1][] = array(
    'id' => 'no_payment',
    'text' => TEXT_NO_PAYMENT.' (no_payment)'
  );
  
  if (xtc_not_null(MODULE_PAYMENT_INSTALLED)) {
    $payments = explode(';', MODULE_PAYMENT_INSTALLED);
    for ($i=0; $i<count($payments); $i++) {
      if (is_file(DIR_FS_LANGUAGES . $order->info['language'] . '/modules/payment/' . $payments[$i])) {
        require_once(DIR_FS_LANGUAGES . $order->info['language'] . '/modules/payment/' . $payments[$i]);
      }
      $payment_modul = substr($payments[$i], 0, strrpos($payments[$i], '.'));
      $payment_text = constant('MODULE_PAYMENT_'.strtoupper($payment_modul).'_TEXT_TITLE');

      $sort = 99999;
      if (is_file(DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/' . $payments[$i])) {
        include_once(DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/' . $payments[$i]);
        if (class_exists($payment_modul)) {
          $module = new $payment_modul();
          if ($module->check() > 0) {
            $sort = $module->sort_order;
          }
        }
      }

      $payment_array[$sort][] = array(
        'id' => $payment_modul,
        'text' => $payment_text.' ('.$payment_modul.')'
      );
      array_multisort(array_column($payment_array[$sort], 'text'), SORT_ASC, $payment_array[$sort]);
    }
  }
  ksort($payment_array);
  $payment_array = array_reduce($payment_array, 'array_merge', array());

  $order_payment_text = $order->info['payment_class'];
  if (is_file(DIR_FS_LANGUAGES . $order->info['language'] . '/modules/payment/' . $order->info['payment_class'] .'.php')) {
    require_once(DIR_FS_LANGUAGES . $order->info['language'] . '/modules/payment/' . $order->info['payment_class'] .'.php');
    $order_payment_text = constant('MODULE_PAYMENT_'.strtoupper($order->info['payment_class']).'_TEXT_TITLE').' ('.$order->info['payment_class'].')';
  }
  ?>
  <table class="tableBoxCenter collapse">
    <tr class="dataTableHeadingRow">
      <td class="dataTableHeadingContent" style="width:250px"><b><?php echo TEXT_PAYMENT; ?></b></td>
      <td class="dataTableHeadingContent" style="width:300px"><b><?php echo TEXT_NEW; ?></b></td>
      <td class="dataTableHeadingContent">&nbsp;</td>
    </tr>
    <tr class="dataTableRow">
      <td class="dataTableContent"><?php echo TEXT_ACTUAL . $order_payment_text;?></td>
      <td class="dataTableContent"><?php echo xtc_draw_pull_down_menu('payment', $payment_array, $order->info['payment_class']);?></td> 
      <td class="dataTableContent"><?php echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_SAVE . '"/>';?></td>       
    </tr>
  </table>
</form>
<br />
<!-- Zahlung Ende //-->

<!-- Versand Anfang //-->
<br />
<?php
  echo xtc_draw_form('shipping_edit', FILENAME_ORDERS_EDIT, 'action=shipping_edit', 'post');
  echo xtc_draw_hidden_field('oID', $_GET['oID']);
  
  $shipping_array = array();
  
  if (xtc_not_null(MODULE_SHIPPING_INSTALLED)) {
    $shippings = explode(';', MODULE_SHIPPING_INSTALLED);
    for ($i=0; $i<count($shippings); $i++) {
      if (is_file(DIR_FS_LANGUAGES . $order->info['language'] . '/modules/shipping/' . $shippings[$i])) {
        require_once(DIR_FS_LANGUAGES . $order->info['language'] . '/modules/shipping/' . $shippings[$i]);
      }
      $shipping_modul = substr($shippings[$i], 0, strrpos($shippings[$i], '.'));
      $shipping_text = constant('MODULE_SHIPPING_'.strtoupper($shipping_modul).'_TEXT_TITLE');

      $sort = 99999;
      if (is_file(DIR_FS_CATALOG . DIR_WS_MODULES . 'shipping/' . $shippings[$i])) {
        include_once(DIR_FS_CATALOG . DIR_WS_MODULES . 'shipping/' . $shippings[$i]);
        if (class_exists($shipping_modul)) {
          $module = new $shipping_modul();
          if ($module->check() > 0) {
            $sort = $module->sort_order;
          }
        }
      }

      $shipping_array[$sort][] = array(
        'id' => $shipping_modul,
        'text' => $shipping_text.' ('.$shipping_modul.')'
      );
      array_multisort(array_column($shipping_array[$sort], 'text'), SORT_ASC, $shipping_array[$sort]);
    }
  }
  ksort($shipping_array);
  $shipping_array = array_reduce($shipping_array, 'array_merge', array());

  $order_shipping = explode('_', $order->info['shipping_class']);
  $order_shipping_text = $order_shipping = $order_shipping[0];
  if (is_file(DIR_FS_LANGUAGES . $order->info['language'] . '/modules/shipping/' . $order_shipping .'.php')) {
    require_once(DIR_FS_LANGUAGES . $order->info['language'] . '/modules/shipping/' . $order_shipping .'.php');
    $order_shipping_text = constant('MODULE_SHIPPING_'.strtoupper($order_shipping).'_TEXT_TITLE').' ('.$order_shipping.')';
  }
  ?>
  <table class="tableBoxCenter collapse">
    <tr class="dataTableHeadingRow">
      <td class="dataTableHeadingContent" style="width:250px"><b><?php echo TEXT_SHIPPING; ?></b></td>
      <td class="dataTableHeadingContent" style="width:300px"><?php echo TEXT_NEW; ?></td>
      <td class="dataTableHeadingContent" style="width:200px"><?php echo TEXT_PRICE ; ?></td>
      <td class="dataTableHeadingContent">&nbsp;</td>
    </tr>
    <tr class="dataTableRow">
      <td class="dataTableContent"><?php echo TEXT_ACTUAL . $order_shipping_text; ?></td>
      <td class="dataTableContent"><?php echo xtc_draw_pull_down_menu('shipping', $shipping_array, $order_shipping); ?></td>
      <td class="dataTableContent">
      <?php
        $order_total_query = xtc_db_query("select value from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . $_GET['oID'] . "' and class = 'ot_shipping' ");
        $order_total = xtc_db_fetch_array($order_total_query);
        echo xtc_draw_input_field('value', $order_total['value'], 'class="txta-r"');        
      ?>
      </td>
      <td class="dataTableContent"><?php echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_SAVE . '"/>'; ?></td>
    </tr>
  </form>
</table>
<br />
<!-- Versand Ende //-->

<!-- OT Module Anfang //-->
<br />
<table class="tableBoxCenter collapse">
  <tr class="dataTableHeadingRow">
    <td class="dataTableHeadingContent" style="width:250px"><b><?php echo TEXT_ORDER_TOTAL; ?></b></td>
    <td class="dataTableHeadingContent" style="width:300px"><b><?php echo TEXT_ORDER_TITLE; ?></b></td>
    <td class="dataTableHeadingContent" style="width:200px"><b><?php echo TEXT_ORDER_VALUE; ?></b></td>
    <td class="dataTableHeadingContent">&nbsp;</td>
    <td class="dataTableHeadingContent">&nbsp;</td>
  </tr>
  <?php
  if (xtc_not_null(MODULE_ORDER_TOTAL_INSTALLED)) {
    $totals = explode(';', MODULE_ORDER_TOTAL_INSTALLED);
    for ($i=0; $i<count($totals); $i++) {
      if (is_file(DIR_FS_LANGUAGES . $order->info['language'] . '/modules/order_total/' . $totals[$i])) {
        require_once(DIR_FS_LANGUAGES . $order->info['language'] . '/modules/order_total/' . $totals[$i]);
      }
      $total = substr($totals[$i], 0, strrpos($totals[$i], '.'));
      $total_name = str_replace('ot_','',$total);
      $total_text = constant('MODULE_ORDER_TOTAL_'.strtoupper($total_name).'_TITLE');
    
      $ot_total_query = xtc_db_query("SELECT * 
                                        FROM " . TABLE_ORDERS_TOTAL . " 
                                       WHERE orders_id = '" . (int)$_GET['oID'] . "' 
                                         AND class = '" . xtc_db_input($total) . "' ");
      if (xtc_db_num_rows($ot_total_query) > 0) {
        $ototal_array = array();
        while ($ot_total = xtc_db_fetch_array($ot_total_query)) {
          $ototal_array[] = $ot_total;
        }
      } else {
        $ototal_array = array(
          array(
            'title' => '',
            'value' => '',
            'orders_total_id' => '',
          )
        );
      }
    
      foreach ($ototal_array as $ototal) {
      ?>
      <tr class="dataTableRow">
        <?php echo xtc_draw_form('ot_edit', FILENAME_ORDERS_EDIT, 'action=ot_edit', 'post'); ?>
          <td class="dataTableContent"><?php echo $total_text; ?></td>
          <td class="dataTableContent"><?php echo xtc_draw_input_field('ot_title', $ototal['title'], 'size=40'); ?></td>
          <td class="dataTableContent"><?php echo xtc_draw_input_field('ot_value', $ototal['value'], 'class="txta-r"'); ?></td>
          <td class="dataTableContent">
          <?php
            echo xtc_draw_hidden_field('ot_class', $total);
            echo xtc_draw_hidden_field('ot_sort_order', constant('MODULE_ORDER_TOTAL_'.strtoupper($total_name).'_SORT_ORDER'));
            echo xtc_draw_hidden_field('oID', (int)$_GET['oID']);
            echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_SAVE . '"/>';
          ?>
          </td>
        </form>
        <td class="dataTableContent">
          <?php
            echo xtc_draw_form('ot_delete', FILENAME_ORDERS_EDIT, 'action=ot_delete', 'post');
            echo xtc_draw_hidden_field('oID', (int)$_GET['oID']);
            echo xtc_draw_hidden_field('otID', $ototal['orders_total_id']);
            if ($total != 'ot_total') {
              echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"/>';
            }
          ?>
          </form>
        </td>
      </tr>
      <?php
      }
    }
  }
  ?>
</table>
<br /><br />
