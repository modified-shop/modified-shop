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
   (c) 2003 nextcommerce (orders.php,v 1.7 2003/08/14); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License
   v.1.32 - 2012-05-23 (c) by web28 - www.rpa-com.de: Korrektur f�r Nettosumme bei Modulen ohne Steuersatz
   v.1.31 - 2012-05-23 (c) by web28 - www.rpa-com.de: FIX Preisberechnung Kundengruppenwechsel, Optionspreise bei Sonderpreisen
   v.1.30 - 2012-04-05 (c) by web28 - www.rpa-com.de: FIX order tax by $order->delivery['country_iso_2']
   v.1.28 - 2012-03-22 (c) by web28 - www.rpa-com.de: FIX tax guest account, tax ot_payment, tax cod_fee
   v.1.26 - 2011-11-01 (c) by web28 - www.rpa-com.de: L�nderwechsel -> delivery_country_iso_code_2, billing_country_iso_code_2
   TODO Attributpreise und Sonderangebote
        Bei Sonderangeboten wird der Attributpreis nicht zum Artikelpreis addiert
        Anpassung in checkout_process.php
   --------------------------------------------------------------*/


  require ('includes/application_top.php');

  // include needed functions
  require_once (DIR_WS_FUNCTIONS.'orders_functions.php');

  if (!$_GET['oID']) {
    $_GET['oID'] = $_POST['oID'];
  }
  $order = new order((int)$_GET['oID']);

  $xtPrice = new xtcPrice($order->info['currency'], $order->info['status']);

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  switch ($action) {
    case 'payment_edit':
      orders_payment_edit($_POST['oID'], $_POST);
      xtc_redirect(xtc_href_link(FILENAME_ORDERS_EDIT, 'edit_action=other&oID='.(int)$_POST['oID']));
      break;

    case 'shipping_edit':
      orders_shipping_edit($_POST['oID'], $_POST);
      xtc_redirect(xtc_href_link(FILENAME_ORDERS_EDIT, 'edit_action=other&oID='.(int)$_POST['oID']));
      break;

    case 'lang_edit':
      orders_lang_edit($_POST['oID'], $_POST);
      xtc_redirect(xtc_href_link(FILENAME_ORDERS_EDIT, 'edit_action=other&oID='.(int)$_POST['oID']));
      break;

    case 'curr_edit':
      orders_curr_edit($_POST['oID'], $_POST);
      xtc_redirect(xtc_href_link(FILENAME_ORDERS_EDIT, 'edit_action=other&oID='.(int)$_POST['oID']));
      break;

    case 'address_edit':
      orders_address_edit($_POST['oID'], $_POST);
      xtc_redirect(xtc_href_link(FILENAME_ORDERS_EDIT, 'edit_action=address&oID='.(int)$_POST['oID']));
      break;

    case 'product_edit':
      orders_product_edit($_POST['oID'], $_POST);
      xtc_redirect(xtc_href_link(FILENAME_ORDERS_EDIT, 'edit_action=products&oID='.(int)$_POST['oID']));
      break;    
    case 'product_ins':
      orders_product_insert($_POST['oID'], $_POST);
      xtc_redirect(xtc_href_link(FILENAME_ORDERS_EDIT, 'edit_action=products&oID='.(int)$_POST['oID']));
      break;
    case 'product_delete':
      orders_product_delete($_POST['oID'], $_POST);
      xtc_redirect(xtc_href_link(FILENAME_ORDERS_EDIT, 'edit_action=products&oID='.(int)$_POST['oID']));
      break;

    case 'product_option_edit':
      $products_id = orders_product_option_edit($_POST['oID'], $_POST);
      xtc_redirect(xtc_href_link(FILENAME_ORDERS_EDIT, 'edit_action=options&oID='.(int)$_POST['oID'].'&pID='.(int)$products_id.'&opID='.(int)$_POST['opID']));
      break;
    case 'product_option_ins':
      $products_id = orders_product_option_insert($_POST['oID'], $_POST);
      xtc_redirect(xtc_href_link(FILENAME_ORDERS_EDIT, 'edit_action=options&oID='.(int)$_POST['oID'].'&pID='.(int)$products_id.'&opID='.(int)$_POST['opID']));
      break;
    case 'product_option_delete':
      $products_id = orders_product_option_delete($_POST['oID'], $_POST);
      xtc_redirect(xtc_href_link(FILENAME_ORDERS_EDIT, 'edit_action=options&oID='.(int)$_POST['oID'].'&pID='.(int)$products_id.'&opID='.(int)$_POST['opID']));
      break;

    case 'ot_edit':
      if ($_POST['class'] == 'ot_shipping') {
        $module_query = xtc_db_query("SELECT value, 
                                             class 
                                        FROM ".TABLE_ORDERS_TOTAL." 
                                       WHERE orders_id = '".(int)$_POST['oID']."' 
                                         AND class = 'ot_shipping'");
        if (!xtc_db_num_rows($module_query)) {
          $messageStack->add_session(ERROR_INPUT_SHIPPING_TITLE, 'error');
          xtc_redirect(xtc_href_link(FILENAME_ORDERS_EDIT, 'edit_action=other&oID='.(int)$_POST['oID']));
        }
      }
      if ($_POST['value'] != '' && trim($_POST['title']) == '') {
        $messageStack->add_session(ERROR_INPUT_TITLE, 'error');
        xtc_redirect(xtc_href_link(FILENAME_ORDERS_EDIT, 'edit_action=other&oID='.(int)$_POST['oID']));
      }
      if ($_POST['value'] == '' && trim($_POST['title']) == '') {
        $messageStack->add_session(ERROR_INPUT_EMPTY, 'error');
        xtc_redirect(xtc_href_link(FILENAME_ORDERS_EDIT, 'edit_action=other&oID='.(int)$_POST['oID']));
      }
      orders_ot_edit($_POST['oID'], $_POST);
      xtc_redirect(xtc_href_link(FILENAME_ORDERS_EDIT, 'edit_action=other&oID='.(int)$_POST['oID']));
      break;
    case 'ot_delete':
      orders_ot_delete($_POST['oID'], $_POST);
      xtc_redirect(xtc_href_link(FILENAME_ORDERS_EDIT, 'edit_action=other&oID='.(int)$_POST['oID']));
      break;

    case 'save_order':
      orders_save_order($_POST['oID'], $_POST);
      xtc_redirect(xtc_href_link(FILENAME_ORDERS, 'action=edit&oID='.(int)$_POST['oID']));
      break;
  }

  require (DIR_WS_INCLUDES.'head.php');
?>
</head>
<body>
  <!-- header //-->
  <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
  <!-- header_eof //-->
  <!-- body //-->
  <table class="tableBody">
    <tr>
      <?php //left_navigation
      if (USE_ADMIN_TOP_MENU == 'false') {
        echo '<td class="columnLeft2">'.PHP_EOL;
        echo '<!-- left_navigation //-->'.PHP_EOL;       
        require_once(DIR_WS_INCLUDES . 'column_left.php');
        echo '<!-- left_navigation eof //-->'.PHP_EOL; 
        echo '</td>'.PHP_EOL;      
      }
      ?>
      <!-- body_text //-->
      <td class="boxCenter">
        <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_orders.png'); ?></div>
        <div class="pageHeading pdg2"><?php echo TABLE_HEADING; ?></div>
        <table class="tableCenter">           
          <tr>
            <td class="boxCenterLeft">                
              <?php
              if ($_GET['text'] == 'address') {
                if ($_GET['text'] == 'address') {
                  echo TEXT_EDIT_ADDRESS_SUCCESS;
                }
              }
              if (!isset($_GET['edit_action'])) {
                ?>                
                <div class="main important_info">
                  <?php echo TEXT_ORDERS_EDIT_INFO;?>
                </div>
                <?php
              }
              if ($_GET['edit_action'] == 'address') {                  
                include ('orders_edit_address.php');                  
              } elseif ($_GET['edit_action'] == 'products') {
                include ('orders_edit_products.php');
              } elseif ($_GET['edit_action'] == 'other') {
                include ('orders_edit_other.php');
              } elseif ($_GET['edit_action'] == 'options') {
                include ('orders_edit_options.php');
              }
              ?>
              <div class="clear smallText pdg2 flt-r mrg5">
              <?php
                echo TEXT_SAVE_ORDER;
                echo xtc_draw_form('save_order', FILENAME_ORDERS_EDIT, 'action=save_order', 'post');
                  echo xtc_draw_hidden_field('customers_status_id', $address[customers_status]);
                  echo xtc_draw_hidden_field('oID', (int)$_GET['oID']);
                  echo xtc_draw_hidden_field('cID', (int)$_GET['cID']);
                  echo '<input type="submit" class="button" onclick="this.blur();" value="'.BUTTON_SAVE.'"/>';
                  if (isset($_GET['edit_action'])) {
                    echo '&nbsp;&nbsp;&nbsp;';
                    echo '<a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_ORDERS_EDIT, 'oID='.(int)$_GET['oID']).'">'.BUTTON_BACK.'</a>';
                  } else {
                    echo '&nbsp;&nbsp;&nbsp;';
                    echo '<a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_ORDERS, 'action=edit&oID='.(int)$_GET['oID']).'">'.BUTTON_BACK.'</a>';
                  }
                  ?>
                </form>                    
              </div>
            </td>
            <?php
            $heading = array ();
            $contents = array ();
            // KLARNA ORDERSTATUS UPDATE START
            if (is_file(DIR_FS_DOCUMENT_ROOT . 'includes/external/klarna/class.KlarnaCore.php')) {
              require_once (DIR_FS_DOCUMENT_ROOT . 'includes/external/klarna/class.KlarnaCore.php');
              if ($_GET['edit_action'] == "klarna_check_orderstatus") {
                include_once (DIR_FS_ADMIN. 'klarna_check_orderstatus.php');
                $orderStatus = new KlarnaCheckOrder;
                $orderStatus->checkOrder($_GET['oID'], $order->info['payment_method']);
              }
              if ($order->info['payment_method'] == 'klarna_partPayment' || $order->info['payment_method'] == 'klarna_invoice' || $order->info['payment_method'] == 'klarna_SpecCamp') {
                //echo "<link href='" . KlarnaUtils::getStaticPath() . "images.css' type='text/css' rel='stylesheet'/>";
                echo "<link href='" . DIR_WS_BASE . "includes/external/klarna/KITT/css/images.css' type='text/css' rel='stylesheet'/>";
                $contents[] = array ('align' => 'center',
                                     'text' => '<br /><span class="klarna_logo_small"></span><br /><br />'.'<a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_ORDERS_EDIT, 'edit_action=klarna_check_orderstatus&oID='.$_GET['oID']).'">Check Order Status</a><br /><br />'
                                     );
              }
            }
            // KLARNA ORDERSTATUS UPDATE END
            switch ($action) {
              default :
                if (is_object($order)) {
                  $heading[] = array ('text' => '<b>'.TABLE_HEADING_ORDER.(int)$_GET['oID'].'</b>');
                  $contents[] = array ('align' => 'center', 'text' => '<br />'.TEXT_EDIT_ADDRESS.'<br /><a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_ORDERS_EDIT, 'edit_action=address&oID='.(int)$_GET['oID']).'">'.BUTTON_EDIT.'</a><br /><br />');
                  $contents[] = array ('align' => 'center', 'text' => '<br />'.TEXT_EDIT_PRODUCTS.'<br /><a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_ORDERS_EDIT, 'edit_action=products&oID='.(int)$_GET['oID']).'">'.BUTTON_EDIT.'</a><br /><br />');
                  $contents[] = array ('align' => 'center', 'text' => '<br />'.TEXT_EDIT_OTHER.'<br /><a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_ORDERS_EDIT, 'edit_action=other&oID='.(int)$_GET['oID']).'">'.BUTTON_EDIT.'</a><br /><br />');
                }
                break;
            }
            if ((xtc_not_null($heading)) && (xtc_not_null($contents))) {
               echo '            <td class="boxRight">' . "\n";
              $box = new box;
              echo $box->infoBox($heading, $contents);
              echo '            </td>'."\n";
            }
            ?>
          </tr>
        </table>
      </td>            
      <!-- body_text_eof //-->
    </tr>
  </table>
  <!-- body_eof //-->
  <!-- footer //-->
  <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
  <!-- footer_eof //-->
  <br />
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>