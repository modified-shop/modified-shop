<?php
  /* --------------------------------------------------------------
   $Id: orders_edit_products.php 4310 2013-01-14 13:06:49Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(orders.php,v 1.27 2003/02/16); www.oscommerce.com
   (c) 2003	 nextcommerce (orders.php,v 1.7 2003/08/14); www.nextcommerce.org
   (c) 2006 XT-Commerce (orders_edit.php)

   Released under the GNU General Public License

   XTC-Bestellbearbeitung:
   http://www.xtc-webservice.de / Matthias Hinsche
   info@xtc-webservice.de
   --------------------------------------------------------------*/
   
defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

if( !defined('MAX_DISPLAY_PRODUCTS_SEARCH_RESULTS')) {
  define('MAX_DISPLAY_PRODUCTS_SEARCH_RESULTS', 20);
}

if( defined('USE_ADMIN_THUMBS_IN_LIST_STYLE')) {
  $admin_thumbs_size = 'style="'.USE_ADMIN_THUMBS_IN_LIST_STYLE.'"';
} else {
  $admin_thumbs_size = 'style="max-width: 40px; max-height: 40px;"';
}

require_once (DIR_WS_CLASSES.'currencies.php');
$currencies = new currencies();

?>
<!-- Begin Infotext //-->
<table border="0" cellspacing="0" cellpadding="2" style="border: 1px red solid; padding:5px; background: #FFD6D6; margin: 5px 0 5px 0">
  <tr>
    <td class="main"> 
      <?php echo TEXT_ORDERS_PRODUCT_EDIT_INFO;?>
    </td>
  </tr>
</table>
<!-- End Infotext //-->
<!-- Artikelbearbeitung Anfang //-->
<table border="0" width="100%" cellspacing="0" cellpadding="2">
  <tr class="dataTableHeadingRow">
    <td class="dataTableHeadingContent"><b><?php echo TEXT_PRODUCT_ID;?></b></td>
    <td class="dataTableHeadingContent"><b><?php echo TEXT_QUANTITY;?></b></td>
    <td class="dataTableHeadingContent"><b><?php echo TEXT_PRODUCT;?></b></td>
    <td class="dataTableHeadingContent"><b><?php echo TEXT_PRODUCTS_MODEL;?></b></td>
    <td class="dataTableHeadingContent"><b><?php echo TEXT_TAX;?></b></td>
    <td class="dataTableHeadingContent"><b><?php echo TEXT_PRICE;?></b></td>
    <td class="dataTableHeadingContent"><b><?php echo TEXT_FINAL;?></b></td>
    <td class="dataTableHeadingContent">&nbsp;</td>
    <td class="dataTableHeadingContent">&nbsp;</td>
  </tr>
  <?php
  for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
    ?>
    <tr class="dataTableRow">
      <?php
      echo xtc_draw_form('product_edit', FILENAME_ORDERS_EDIT, 'action=product_edit', 'post');
        //BOF - web28 - 2011-01-16 - FIX missing sessions id
        echo xtc_draw_hidden_field(xtc_session_name(), xtc_session_id());
        //EOF - web28 - 2011-01-16 - FIX missing sessions id
        //BOF - web28 - 2011-03-13 - FIX missing old_qty
        echo xtc_draw_hidden_field('old_qty', $order->products[$i]['qty']);
        //EOF - web28 - 2011-03-13 - FIX missing old_qty
        echo xtc_draw_hidden_field('oID', $_GET['oID']);
        echo xtc_draw_hidden_field('opID', $order->products[$i]['opid']);
        ?>
        <td class="dataTableContent"><?php echo xtc_draw_input_field('products_id', $order->products[$i]['id'], 'size="5"');?></td>
        <td class="dataTableContent"><?php echo xtc_draw_input_field('products_quantity', $order->products[$i]['qty'], 'size="2"');?></td>
        <td class="dataTableContent"><?php echo xtc_draw_input_field('products_name', $order->products[$i]['name'], 'size="20"');?></td>
        <td class="dataTableContent"><?php echo xtc_draw_input_field('products_model', $order->products[$i]['model'], 'size="10"');?></td>
        <td class="dataTableContent"><?php echo xtc_draw_input_field('products_tax', $order->products[$i]['tax'], 'size="6"');?></td>
        <td class="dataTableContent"><?php echo xtc_draw_input_field('products_price', $order->products[$i]['price'], 'size="10"');?></td>
        <td class="dataTableContent"><?php echo $order->products[$i]['final_price'];?></td>
        <td class="dataTableContent">
          <?php
          echo xtc_draw_hidden_field('allow_tax', $order->products[$i]['allow_tax']);
          echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_SAVE . '"/>';
          ?>
        </td>
      </form>
      <td class="dataTableContent">
        <?php
        echo xtc_draw_form('product_delete', FILENAME_ORDERS_EDIT, 'action=product_delete', 'post');
          //BOF - web28 - 2011-01-16 - FIX missing sessions id
          echo xtc_draw_hidden_field(xtc_session_name(), xtc_session_id());
          //EOF - web28 - 2011-01-16 - FIX missing sessions id
          echo xtc_draw_hidden_field('oID', $_GET['oID']);
          echo xtc_draw_hidden_field('opID', $order->products[$i]['opid']);
          //BOF - DokuMan - 2010-09-07 - variables for correct deletion of products (thx to franky_n)
          echo xtc_draw_hidden_field('del_qty', $order->products[$i]['qty']);
          echo xtc_draw_hidden_field('del_pID', $order->products[$i]['id']);
          //EOF - DokuMan - 2010-09-07 - variables for correct deletion of products (thx to franky_n)
          echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"/>';
          ?>
        </form>
      </td>
    </tr>
    <tr class="dataTableRow">
      <td class="dataTableContent" colspan="8">&nbsp;</td>
      <td class="dataTableContent">
        <?php
        echo xtc_draw_form('select_options', FILENAME_ORDERS_EDIT, '', 'GET');
          echo xtc_draw_hidden_field('edit_action', 'options');
          echo xtc_draw_hidden_field('pID', $order->products[$i]['id']);
          echo xtc_draw_hidden_field('oID', $_GET['oID']);
          echo xtc_draw_hidden_field('opID', $order->products[$i]['opid']);
          //BOF - web28 - 2011-01-16 - FIX missing sessions id
          echo xtc_draw_hidden_field(xtc_session_name(), xtc_session_id());
          //EOF - web28 - 2011-01-16 - FIX missing sessions id
          echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_PRODUCT_OPTIONS . '"/>';
          ?>
        </form>
      </td>
    </tr>
    <?php
  }
  ?>
</table>
<br /><br />
<!-- Artikelbearbeitung Ende //-->
<!-- Artikel Einfügen Anfang //-->
<table border="0" width="100%" cellspacing="0" cellpadding="2">
  <tr class="dataTableHeadingRow">
    <td class="dataTableHeadingContent" colspan="2"><b><?php echo TEXT_PRODUCT_SEARCH;?></b></td>
  </tr>
  <tr class="dataTableRow">
    <?php
    echo xtc_draw_form('product_search', FILENAME_ORDERS_EDIT, '', 'get');
      echo xtc_draw_hidden_field('edit_action', 'products');
      echo xtc_draw_hidden_field('action', 'product_search');
      echo xtc_draw_hidden_field('oID', $_GET['oID']);
      echo xtc_draw_hidden_field('cID', $_POST['cID']);
      //BOF - web28 - 2011-01-16 - FIX missing sessions id
      //echo xtc_draw_hidden_field(xtc_session_name(), xtc_session_id());
      //EOF - web28 - 2011-01-16 - FIX missing sessions id
      ?>
      <td class="dataTableContent" width="40"><?php echo xtc_draw_input_field('search', $_GET['search'], 'size="30"');?></td>
      <td class="dataTableContent">
        <?php
        echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_SEARCH . '"/>';
        echo TEXT_PRODUCTS_SEARCH_INFO;
        ?>
      </td>
    </form>
  </tr>
</table>
<br /><br />
<?php
if ($_GET['action'] =='product_search') {  
  ?>
  <table border="0" width="100%" cellspacing="0" cellpadding="2">
    <tr class="dataTableHeadingRow">
      <td class="dataTableHeadingContent"><b><?php echo TEXT_PRODUCT_ID;?></b></td> 
      <td class="dataTableHeadingContent"><b><?php echo TEXT_PRODUCTS_STATUS;?></b></td>
      <td class="dataTableHeadingContent"><b><?php echo TEXT_PRODUCT;?></b></td>
      <td class="dataTableHeadingContent"><b><?php echo TEXT_PRODUCTS_IMAGE;?></b></td>
      <td class="dataTableHeadingContent"><b><?php echo TEXT_PRODUCTS_MODEL;?></b></td>
      <td class="dataTableHeadingContent"><b><?php echo TEXT_PRODUCTS_EAN;?></b></td>
      <td class="dataTableHeadingContent"><b><?php echo TEXT_PRODUCTS_DATE_AVAILABLE;?></b></td>
      <td class="dataTableHeadingContent"><b><?php echo TEXT_PRICE;?></b></td>
      <?php 
      if (PRICE_IS_BRUTTO == 'true') { 
      ?>
      <td class="dataTableHeadingContent"><b><?php echo TEXT_NETTO ;?></b></td>
      <?php 
      } 
      ?>
      <td class="dataTableHeadingContent"><b><?php echo TEXT_PRODUCTS_TAX_RATE;?></b></td>
      <td class="dataTableHeadingContent"><b><?php echo TEXT_PRODUCTS_QTY;?></b></td>
      <td class="dataTableHeadingContent"><b><?php echo TEXT_QUANTITY;?></b></td>
      <td class="dataTableHeadingContent">&nbsp;</td>
    </tr>
    <?php   
    $products_query_raw = ("SELECT
                                   p.products_id,
                                   p.products_model,
                                   p.products_ean,
                                   p.products_quantity,
                                   p.products_image,
                                   p.products_price,
                                   p.products_discount_allowed,
                                   p.products_tax_class_id,
                                   p.products_date_available,
                                   p.products_status,
                                   pd.products_name                                         
                              FROM " . TABLE_PRODUCTS . " p,
                                   " . TABLE_PRODUCTS_DESCRIPTION . " pd
                             WHERE p.products_id = pd.products_id
                               AND pd.language_id = '" . $_SESSION['languages_id'] . "'
                               AND (pd.products_name LIKE ('%" . $_GET['search'] . "%') OR 
                                    p.products_model LIKE ('%" . $_GET['search'] . "%') OR 
                                    p.products_ean LIKE ('%" . $_GET['search'] . "%')
                                   )
                          ORDER BY pd.products_name");
                                
    $products_split = new splitPageResults($_GET['page'], MAX_DISPLAY_PRODUCTS_SEARCH_RESULTS, $products_query_raw, $products_query_numrows);
    $products_query = xtc_db_query($products_query_raw);
    while($products = xtc_db_fetch_array($products_query)) {
      ?>
      <tr class="dataTableRow">
        <?php
        
          if ($products['products_status'] == '1') {
            $products_status =  xtc_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10);
          } else {
            $products_status =  xtc_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
          }
          
          $products_tax_rate = xtc_get_tax_rate($products['products_tax_class_id']);
          // calculate brutto price for display
          if (PRICE_IS_BRUTTO == 'true') {
            $products_price = xtc_round($products['products_price'] * ((100 + $products_tax_rate) / 100), PRICE_PRECISION);
            $products_price = $currencies->format($products_price);
            $products_price_netto = $currencies->format($products['products_price']);
          } else {
            $products_price = $currencies->format($products['products_price']);
            $products_price_netto = '';
          }
          
          echo xtc_draw_form('product_ins', FILENAME_ORDERS_EDIT, 'action=product_ins', 'post');
          //BOF - web28 - 2011-01-16 - FIX missing sessions id
          //echo xtc_draw_hidden_field(xtc_session_name(), xtc_session_id());
          //EOF - web28 - 2011-01-16 - FIX missing sessions id
          echo xtc_draw_hidden_field('cID', $_POST['cID']);
          echo xtc_draw_hidden_field('oID', $_GET['oID']);
          echo xtc_draw_hidden_field('products_id', $products['products_id']);
          ?>
          <td class="dataTableContent">&nbsp;<?php echo $products['products_id'];?></td>
          <td class="dataTableContent">&nbsp;<?php echo $products_status;?></td>
          <td class="dataTableContent">&nbsp;<?php echo '<a target="_blank" href="'. xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('cPath', 'action', 'pID', 'cID', 'edit_action', 'search', 'page', 'oID')) . 'pID=' . $products['products_id'] ) . '&action=new_product' . '">' . xtc_image(DIR_WS_ICONS . 'icon_edit.gif', ICON_EDIT, '', '', $icon_padding). '</a> '. $products['products_name'];?></td>
          <td class="dataTableContent">&nbsp;<?php echo xtc_product_thumb_image($products['products_image'], $products['products_name'], '','',$admin_thumbs_size);?></td>
          <td class="dataTableContent">&nbsp;<?php echo $products['products_model'];?></td>
          <td class="dataTableContent">&nbsp;<?php echo $products['products_ean'];?></td>
          <td class="dataTableContent">&nbsp;<?php echo xtc_date_short($products['products_date_available']);?></td>
          <td class="dataTableContent"><?php echo $products_price?></td>
          <?php 
          if (PRICE_IS_BRUTTO == 'true') { 
          ?>
          <td class="dataTableContent"><?php echo $products_price_netto;?></td>
          <?php 
          } 
          ?>
          <td class="dataTableContent">&nbsp;<?php echo $products_tax_rate;?></td>
          <td class="dataTableContent">&nbsp;<?php echo $products['products_quantity'];?></td>
          <td class="dataTableContent"><?php echo xtc_draw_input_field('products_quantity', '', 'size="4"');?></td>
          <td class="dataTableContent">
            <?php
            echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_INSERT . '"/>';
            ?>
          </td>
        </form>
      </tr>
      <?php
    }
    ?>    
  </table>
  <table border="0" width="100%" cellspacing="0" cellpadding="2">
    <tr>
      <td class="smallText" valign="top"><?php echo $products_split->display_count($products_query_numrows, MAX_DISPLAY_PRODUCTS_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></td>
      <td class="smallText" align="right"><?php echo $products_split->display_links($products_query_numrows, MAX_DISPLAY_PRODUCTS_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], xtc_get_all_get_params(array('page'))); ?></td>
    </tr>
  </table>
  <?php
}
?>
<br /><br />
<!-- Artikel Einfügen Ende //-->