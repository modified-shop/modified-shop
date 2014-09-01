<?php
/* -----------------------------------------------------------------------------------------
   $Id: paypal_checkout.php 3137 2012-06-29 15:05:12Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2003-2007 xt:Commerce (Winger/Zanier), http://www.xt-commerce.com
   @copyright Copyright 2003-2007 xt:Commerce (Winger/Zanier), www.xt-commerce.com
   @copyright based on Copyright 2002-2003 osCommerce; www.oscommerce.com
   @copyright based on Copyright 2003 nextcommerce; www.nextcommerce.org
   @license http://www.xt-commerce.com.com/license/2_0.txt GNU Public License V2.0

   ab 15.08.2008 Teile vom Hamburger-Internetdienst geändert
   Hamburger-Internetdienst Support Forums at www.forum.hamburger-internetdienst.de
   Stand 12.06.2012

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

include('includes/application_top.php');
// create smarty elements
$smarty = new Smarty;
require(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');
// include needed functions
require_once(DIR_FS_INC.'xtc_address_label.inc.php');
require_once(DIR_FS_INC.'xtc_get_address_format_id.inc.php');
require_once(DIR_FS_INC.'xtc_count_shipping_modules.inc.php');
require_once(DIR_FS_INC . 'xtc_check_stock.inc.php');
require_once(DIR_FS_INC . 'xtc_calculate_tax.inc.php');
require_once(DIR_FS_INC . 'xtc_check_stock.inc.php');
require_once(DIR_FS_INC . 'xtc_display_tax_value.inc.php');
require_once(DIR_FS_INC . 'xtc_get_attributes_model.inc.php');

require(DIR_WS_CLASSES.'http_client.php');
unset($_SESSION['tmp_oID']);

if (isset($_GET['error_message'])) { //Dokuman - 2012-05-31 - fix paypal_checkout notices
  switch($_GET['error_message']) {
    case "1":
      $message = str_replace('\n', '', ERROR_CONDITIONS_NOT_ACCEPTED);
      $messageStack->add('checkout_payment', $message);
      break;
    case "2":
      $message = str_replace('\n', '', ERROR_ADDRESS_NOT_ACCEPTED);
      $messageStack->add('checkout_payment', $message);
      break;
    case "12":
      $message = str_replace('\n', '', ERROR_CONDITIONS_NOT_ACCEPTED);
      $messageStack->add('checkout_payment', $message);
      $message = str_replace('\n', '', ERROR_ADDRESS_NOT_ACCEPTED);
      $messageStack->add('checkout_payment', $message);
      break;
  }
} //Dokuman - 2012-05-31 - fix paypal_checkout notices

// Kein Token mehr da durch Back im Browser auf die Seite
if(!$_SESSION['reshash']['TOKEN']) {
  unset($_SESSION['payment']);
  unset($_SESSION['nvpReqArray']);
  unset($_SESSION['reshash']);
  unset($_SESSION['sendto']);
  xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART));
}

// Get Customer Data and Check for existing Account.
$o_paypal->paypal_get_customer_data();

if(!isset($_SESSION['customer_id'])) {
  if(ACCOUNT_OPTIONS == 'guest') {
    xtc_redirect(xtc_href_link(FILENAME_CREATE_GUEST_ACCOUNT, '', 'SSL'));
  } else {
    xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
  }
}
//// zahlungsweise in session schreiben
$_SESSION['payment'] = 'paypalexpress';

if(isset($_POST['act_shipping']))
  $_SESSION['act_shipping'] = 'true';

if(isset($_POST['act_payment']))
  $_SESSION['act_payment'] = 'true';

if(isset($_POST['payment']))
  $_SESSION['payment'] = xtc_db_prepare_input($_POST['payment']);

if(!empty($_POST['comments_added'])) //Dokuman - 2012-05-31 - fix paypal_checkout notices
  $_SESSION['comments'] = xtc_db_prepare_input($_POST['comments']);

//-- TheMedia Begin check if display conditions on checkout page is true
if(isset($_POST['cot_gv']))
  $_SESSION['cot_gv'] = true;

// if there is nothing in the customers cart, redirect them to the shopping cart page
if($_SESSION['cart']->count_contents() < 1)
  xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART));

// Kein Token mehr da durch Back im Browser auf die Seite
if( !($_SESSION['nvpReqArray']['TOKEN']) OR !($_SESSION['reshash']['PAYERID']) ) {
  unset($_SESSION['payment']);
  unset($_SESSION['nvpReqArray']);
  unset($_SESSION['reshash']);
  unset($_SESSION['sendto']);
  xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART));
}

if(isset($_SESSION['credit_covers']))
  unset($_SESSION['credit_covers']); //ICW ADDED FOR CREDIT CLASS SYSTEM

// Stock Check
if((STOCK_CHECK == 'true') && (STOCK_ALLOW_CHECKOUT != 'true')) {
  $products = $_SESSION['cart']->get_products();
  $any_out_of_stock = 0;
  //BOF - DokuMan - 2011-12-19 - precount for performance
  //for($i = 0, $n = sizeof($products); $i < $n; $i++) {
  $n=sizeof($products);
  for ($i=0; $i<$n; $i++) {
  //EOF - DokuMan - 2011-12-19 - precount for performance
    if(xtc_check_stock($products[$i]['id'], $products[$i]['quantity']))
      $any_out_of_stock = 1;
  }
  if($any_out_of_stock == 1)
    xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART));
}

// if no shipping destination address was selected, use the customers own address as default
if(!isset($_SESSION['sendto'])) {
  $_SESSION['sendto'] = $_SESSION['customer_default_address_id'];
} else {
  // verify the selected shipping address
  $check_address_query = xtc_db_query("select count(*) as total from ".TABLE_ADDRESS_BOOK." where customers_id = '".(int) $_SESSION['customer_id']."' and address_book_id = '".(int) $_SESSION['sendto']."'");
  $check_address = xtc_db_fetch_array($check_address_query);
  if($check_address['total'] != '1') {
    $_SESSION['sendto'] = $_SESSION['customer_default_address_id'];
    if(isset($_SESSION['shipping']))
      unset($_SESSION['shipping']);
  }
}

// if no billing destination address was selected, use the customers own address as default
if(!isset($_SESSION['billto'])) {
  $_SESSION['billto'] = $_SESSION['customer_default_address_id'];
} else {
  // verify the selected billing address
  $check_address_query = xtc_db_query("select count(*) as total from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int) $_SESSION['customer_id'] . "' and address_book_id = '" . (int) $_SESSION['billto'] . "'");
  $check_address = xtc_db_fetch_array($check_address_query);
  if($check_address['total'] != '1') {
    $_SESSION['billto'] = $_SESSION['customer_default_address_id'];
    if(isset($_SESSION['payment']))
      unset($_SESSION['payment']);
  }
}

require(DIR_WS_CLASSES.'order.php');
$order = new order();
if($order->delivery['country']['iso_code_2'] != '') {
  $_SESSION['delivery_zone'] = $order->delivery['country']['iso_code_2'];
}
$kein_versand=0;
if ($order->content_type == 'virtual' || ($order->content_type == 'virtual_weight') || ($_SESSION['cart']->count_contents_virtual() == 0)) { // GV Code added
  $kein_versand=1;
}
$total_weight = $_SESSION['cart']->show_weight();
$total_count = $_SESSION['cart']->count_contents();

// load all enabled shipping modules
require(DIR_WS_CLASSES.'shipping.php');
$shipping_modules = new shipping;
if(defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true')) {
  switch(MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
    case 'national' :
      if($order->delivery['country_id'] == STORE_COUNTRY)
        $pass = true;
      break;
    case 'international' :
      if($order->delivery['country_id'] != STORE_COUNTRY)
        $pass = true;
      break;
    case 'both' :
      $pass = true;
      break;
    default :
      $pass = false;
      break;
  }
  $free_shipping = false;
  if(($pass == true) && ($order->info['total'] - $order->info['shipping_cost'] >= $xtPrice->xtcFormat(MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER, false, 0, true))) {
    $free_shipping = true;
    include(DIR_WS_LANGUAGES.$_SESSION['language'].'/modules/order_total/ot_shipping.php');
  }
} else {
  $free_shipping = false;
}

// process the selected shipping method
if(isset($_POST['action']) && ($_POST['action'] == 'process')) {
  if((xtc_count_shipping_modules() > 0) || ($free_shipping == true)) {
    if((isset($_POST['shipping'])) && (strpos($_POST['shipping'], '_'))) {
      $_SESSION['shipping'] = $_POST['shipping'];
      list($module, $method) = explode('_', $_SESSION['shipping']);
      if(is_object($$module) || ($_SESSION['shipping'] == 'free_free')) {
        if($_SESSION['shipping'] == 'free_free') {
          $quote[0]['methods'][0]['title'] = FREE_SHIPPING_TITLE;
          $quote[0]['methods'][0]['cost'] = '0';
        } else {
          $quote = $shipping_modules->quote($method, $module);
        }
        if(isset($quote['error'])) {
          unset($_SESSION['shipping']);
        } else {
          if((isset($quote[0]['methods'][0]['title'])) && (isset($quote[0]['methods'][0]['cost']))) {
            $_SESSION['shipping'] = array('id' => $_SESSION['shipping'], 'title' => (($free_shipping == true) ? $quote[0]['methods'][0]['title'] : $quote[0]['module'].' ('.$quote[0]['methods'][0]['title'].')'), 'cost' => $quote[0]['methods'][0]['cost']);
            xtc_redirect(xtc_href_link(FILENAME_PAYPAL_CHECKOUT, '', 'SSL'));
          }
        }
      } else {
        unset($_SESSION['shipping']);
      }
    }
  } else {
    $_SESSION['shipping'] = false;
    xtc_redirect(xtc_href_link(FILENAME_PAYPAL_CHECKOUT, '', 'SSL'));
  }
}

if($kein_versand==1)$_SESSION['shipping'] = false;
// get all available shipping quotes
$quotes = $shipping_modules->quote();
// if no shipping method has been selected, automatically select the cheapest method.
// if the modules status was changed when none were available, to save on implementing
// a javascript force-selection method, also automatically select the cheapest shipping
// method if more than one module is now enabled
if(!isset($_SESSION['shipping']) || (isset($_SESSION['shipping']) && ($_SESSION['shipping'] == false) && (xtc_count_shipping_modules() > 1)))
  $_SESSION['shipping'] = $shipping_modules->cheapest();
if($kein_versand==1)$_SESSION['shipping'] = false;
$order = new order();
// load all enabled payment modules
require(DIR_WS_CLASSES . 'payment.php');

$payment_modules = new payment($_SESSION['payment']);
$payment_modules->update_status();

require(DIR_WS_CLASSES . 'order_total.php'); // GV Code ICW ADDED FOR CREDIT CLASS SYSTEM
$order_total_modules = new order_total();
$order_total_modules->process();

// GV Code Start
$order_total_modules->collect_posts();
$order_total_modules->pre_confirmation_check();
// GV Code End

if(is_array($payment_modules->modules))
  $payment_modules->pre_confirmation_check();

$breadcrumb->add(NAVBAR_TITLE_PAYPAL_CHECKOUT, xtc_href_link(FILENAME_PAYPAL_CHECKOUT, '', 'SSL'));
require(DIR_WS_INCLUDES.'header.php');
if(SHOW_IP_LOG == 'true') {
  $smarty->assign('IP_LOG', 'true');
  if($_SERVER['HTTP_X_FORWARDED_FOR']) {
    $customers_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
  } else {
    $customers_ip = $_SERVER['REMOTE_ADDR'];
  }
  $smarty->assign('CUSTOMERS_IP',$customers_ip);
}

$smarty->assign('FORM_SHIPPING_ACTION', xtc_draw_form('checkout_shipping', xtc_href_link(FILENAME_PAYPAL_CHECKOUT, '', 'SSL')).xtc_draw_hidden_field('action', 'process'));
$smarty->assign('ADDRESS_SHIPPING_LABEL', xtc_address_label($_SESSION['customer_id'], $_SESSION['sendto'], true, ' ', '<br />'));
$smarty->assign('BUTTON_CONTINUE', xtc_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE));
$smarty->assign('FORM_END', '</form>');
$smarty->assign('ADDRESS_PAYMENT_LABEL', xtc_address_label($_SESSION['customer_id'], $_SESSION['billto'], true, ' ', '<br />'));
if(PAYPAL_EXPRESS_ADDRESS_CHANGE == 'true') {
  $smarty->assign('BUTTON_SHIPPING_ADDRESS', '<a href="'.xtc_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL').'">'.xtc_image_button('button_change_address.gif', IMAGE_BUTTON_CHANGE_ADDRESS).'</a>');
  $smarty->assign('BUTTON_PAYMENT_ADDRESS', '<a href="' . xtc_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', 'SSL') . '">' . xtc_image_button('button_change_address.gif', IMAGE_BUTTON_CHANGE_ADDRESS) . '</a>');
}
$module_smarty = new Smarty;
if(xtc_count_shipping_modules() > 0) {
  $showtax = $_SESSION['customers_status']['customers_status_show_price_tax'];
  $module_smarty->assign('FREE_SHIPPING', $free_shipping);
  # free shipping or not...
  if($free_shipping == true) {
    $module_smarty->assign('FREE_SHIPPING_TITLE', FREE_SHIPPING_TITLE);
    $module_smarty->assign('FREE_SHIPPING_DESCRIPTION', sprintf(FREE_SHIPPING_DESCRIPTION, $xtPrice->xtcFormat(MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER, true, 0, true)).xtc_draw_hidden_field('shipping', 'free_free'));
    $module_smarty->assign('FREE_SHIPPING_ICON', $quotes[$i]['icon']);
  } else {
    $radio_buttons = 0;
    #loop through installed shipping methods...
    //BOF - DokuMan - 2011-12-19 - precount for performance
    //for($i = 0, $n = sizeof($quotes); $i < $n; $i ++) {
    $n=sizeof($quotes);
    for ($i=0; $i<$n; $i++) {
    //EOF - DokuMan - 2011-12-19 - precount for performance
      if(!isset($quotes[$i]['error'])) {
        //BOF - DokuMan - 2011-12-19 - precount for performance
        //for($j = 0, $n2 = sizeof($quotes[$i]['methods']); $j < $n2; $j ++) {
        $n2=sizeof($quotes[$i]['methods']);
        for ($j=0; $j<$n2; $j++) {
        //EOF - DokuMan - 2011-12-19 - precount for performance
          # set the radio button to be checked if it is the method chosen
          $quotes[$i]['methods'][$j]['radio_buttons'] = $radio_buttons;
          $checked = (($quotes[$i]['id'].'_'.$quotes[$i]['methods'][$j]['id'] == $_SESSION['shipping']['id']) ? true : false);
          if(($checked == true) || ($n == 1 && $n2 == 1)) {
            $quotes[$i]['methods'][$j]['checked'] = 1;
          }
          if(($n > 1) || ($n2 > 1)) {
            if($_SESSION['customers_status']['customers_status_show_price_tax'] == 0)
              $quotes[$i]['tax'] = '';
            if($_SESSION['customers_status']['customers_status_show_price_tax'] == 0)
              $quotes[$i]['tax'] = 0;
            //BOF - DokuMan - 2012-05-31 - fix undefined index 'tax'
            //$quotes[$i]['methods'][$j]['price'] = $xtPrice->xtcFormat(xtc_add_tax($quotes[$i]['methods'][$j]['cost'], $quotes[$i]['tax']), true, 0, true);
            $quotes[$i]['methods'][$j]['price'] = $xtPrice->xtcFormat(xtc_add_tax($quotes[$i]['methods'][$j]['cost'], isset($quotes[$i]['tax'])?$quotes[$i]['tax']:''), true, 0, true);
            //EOF - DokuMan - 2012-05-31 - fix undefined index 'tax'
            $quotes[$i]['methods'][$j]['radio_field'] = xtc_draw_hidden_field('act_shipping', 'true').xtc_draw_radio_field('shipping', $quotes[$i]['id'].'_'.$quotes[$i]['methods'][$j]['id'], $checked, 'onclick="this.form.submit();"');
          } else {
            if($_SESSION['customers_status']['customers_status_show_price_tax'] == 0)
              $quotes[$i]['tax'] = 0;
            $quotes[$i]['methods'][$j]['price'] = $xtPrice->xtcFormat(xtc_add_tax($quotes[$i]['methods'][$j]['cost'], $quotes[$i]['tax']), true, 0, true).xtc_draw_hidden_field('shipping', $quotes[$i]['id'].'_'.$quotes[$i]['methods'][$j]['id']);
          }
          $radio_buttons ++;
        }
      }
    }
    $module_smarty->assign('module_content', $quotes);
  }
  $module_smarty->caching = 0;
  $shipping_block = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/checkout_shipping_block.html');
}

if($order->info['total'] > 0) {
  if(isset($_GET['payment_error']) && is_object(${ $_GET['payment_error'] }) && ($error = ${$_GET['payment_error']}->get_error())) {
    $smarty->assign('error', encode_htmlspecialchars($error['error']));
  }
  $selection = $payment_modules->selection();
  $radio_buttons = 0;
  //BOF - DokuMan - 2011-12-19 - precount for performance
  //for($i = 0, $n = sizeof($selection); $i < $n; $i++) {
  $n=sizeof($selection);
  for ($i=0; $i<$n; $i++) {
  //EOF - DokuMan - 2011-12-19 - precount for performance
    $selection[$i]['radio_buttons'] = $radio_buttons;
    if (isset($payment)) { //Dokuman - 2012-05-31 - fix paypal_checkout notices
      if(($selection[$i]['id'] == $payment) || ($n == 1)) {
        $selection[$i]['checked'] = 1;
      }
    }
    if($n > 1) { //DokuMan - 2011-12-19 - precount for performance
      $selection[$i]['selection'] = xtc_draw_radio_field('payment', $selection[$i]['id'], ($selection[$i]['id'] == $_SESSION['payment']), 'onclick="this.form.submit();"').xtc_draw_hidden_field('act_payment', 'true');
    } else {
      $selection[$i]['selection'] = xtc_draw_hidden_field('payment', $selection[$i]['id']).xtc_draw_hidden_field('act_payment', 'true');
    }
    if(isset($selection[$i]['error'])) {

    } else {
      $radio_buttons++;
    }
  }
  $module_smarty->assign('module_content', $selection);
} else {
  $smarty->assign('GV_COVER', 'true');
}

if(ACTIVATE_GIFT_SYSTEM == 'true') {
  $smarty->assign('module_gift', $order_total_modules->credit_selection());
}

$module_smarty->caching = 0;
$payment_block = $module_smarty->fetch(CURRENT_TEMPLATE . '/module/checkout_payment_block.html');

if($messageStack->size('checkout_payment') > 0) {
  $smarty->assign('error', $messageStack->output('checkout_payment'));
}

if($order->info['payment_method'] != 'no_payment' && $order->info['payment_method'] != '') {
  include_once(DIR_WS_LANGUAGES . '/' . $_SESSION['language'] . '/modules/payment/' . $order->info['payment_method'] . '.php'); //Dokuman - 2012-05-31 - fix paypal_checkout notices
  $smarty->assign('PAYMENT_METHOD', constant('MODULE_PAYMENT_' . strtoupper($order->info['payment_method']) . '_TEXT_TITLE'));
}

require_once (DIR_FS_INC . 'xtc_get_products_image.inc.php');
$temp_prods=$order->products;
//BOF - DokuMan - 2011-12-19 - precount for performance
//for ($i=0, $n=sizeof($temp_prods); $i<$n; $i++) {
$n=sizeof($temp_prods);
for ($i=0; $i<$n; $i++) {
//EOF - DokuMan - 2011-12-19 - precount for performance
//	$temp_prods[$i]['details']='&nbsp;&#187;<a style="cursor:pointer" onclick="javascript:window.open(\''.xtc_href_link(FILENAME_PRINT_PRODUCT_INFO, 'products_id='.$temp_prods[$i]['id']).'\', \'popup\', \'toolbar=0, width=640, height=600\')" alt="" /><small>Details</small></a>';
	$temp_prods[$i]['details']='&nbsp;&#187;<a href="'.xtc_href_link(FILENAME_PRODUCT_INFO, xtc_product_link($temp_prods[$i]['id'], $temp_prods[$i]['name'])).'" target="_blank"><small>Details</small></a>';
	$image = xtc_get_products_image($temp_prods[$i]['id']);
	if ($image!= '') {
		$temp_prods[$i]['image']='<img height="60px" src="'.DIR_WS_THUMBNAIL_IMAGES.$image.'" alt="'.$temp_prods[$i]['name'].'" title="'.$temp_prods[$i]['name'].'" />';
	}
  if (isset($temp_prods[$i]['attributes'])) { //Dokuman - 2012-05-31 - fix paypal_checkout notices
    $attributes_model='';
    reset($temp_prods[$i]['attributes']);
    while (list($option, $value) = each($temp_prods[$i]['attributes'])) {
      $attributes_model .= xtc_get_attributes_model($temp_prods[$i]['id'], $value['value'], $value['option'] );
    }
    if ($attributes_model) $temp_prods[$i]['model'].=$attributes_model;
  }
}
$smarty->assign('products_data', $temp_prods);

if(MODULE_ORDER_TOTAL_INSTALLED) {
  $smarty->assign('total_block', $order_total_modules->pp_output());
}

if(isset($checkout_payment_modules->modules) && is_array($checkout_payment_modules->modules)) { //Dokuman - 2012-05-31 - fix paypal_checkout notices
  if($confirmation = $checkout_payment_modules->confirmation()) {
    for($i = 0, $n = sizeof($confirmation['fields']); $i < $n; $i++) {
      $payment_info[] = array('TITLE'=>$confirmation['fields'][$i]['title'],
                              'FIELD'=>stripslashes($confirmation['fields'][$i]['field']));
    }
    $smarty->assign('PAYMENT_INFORMATION', $payment_info);
  }
}

if(isset($$_SESSION['payment']->form_action_url) && !$$_SESSION['payment']->tmpOrders) {
  $form_action_url = $$_SESSION['payment']->form_action_url;
} else {
  $form_action_url = xtc_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL');
}
$smarty->assign('CHECKOUT_FORM', xtc_draw_form('checkout_confirmation', $form_action_url, 'post'));
$checkout_payment_button = '';
if(isset($checkout_payment_modules->modules) && is_array($checkout_payment_modules->modules)) {
  $checkout_payment_button .= $checkout_payment_modules->process_button();
}
$smarty->assign('MODULE_BUTTONS', $checkout_payment_button);
$smarty->assign('CHECKOUT_BUTTON', xtc_image_submit('button_confirm_order.gif', IMAGE_BUTTON_CONFIRM_ORDER) . "\n");

if($order->info['shipping_method']) {
  $smarty->assign('SHIPPING_METHOD', $order->info['shipping_method']);
  //$smarty->assign('SHIPPING_EDIT', xtc_href_link(FILENAME_PAYPAL_CHECKOUT_SHIPPING, '', 'SSL'));
  $smarty->assign('SHIPPING_EDIT', xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL')); //DokuMan - 2012-05-29 - assume FILENAME_CHECKOUT_SHIPPING here
}
$smarty->assign('COMMENTS', xtc_draw_textarea_field('comments', 'soft', '60', '5', isset($_SESSION['comments'])?$_SESSION['comments']:'') . xtc_draw_hidden_field('comments_added', 'YES')); //Dokuman - 2012-05-31 - fix paypal_checkout notices
$smarty->assign('ADR_checkbox', '<input type="checkbox" value="address" name="check_address" />');
//check if display conditions on checkout page is true
if(DISPLAY_CONDITIONS_ON_CHECKOUT == 'true') {
  if(GROUP_CHECK == 'true') {
    $group_check = "and group_ids LIKE '%c_" . $_SESSION['customers_status']['customers_status_id'] . "_group%'";
  }
  $shop_content_query = xtc_db_query("SELECT
                                      content_title,
                                      content_heading,
                                      content_text,
                                      content_file
                                      FROM " . TABLE_CONTENT_MANAGER . "
                                      WHERE content_group='3' " . $group_check . "
                                      AND languages_id='" . $_SESSION['languages_id'] . "'");
  $shop_content_data = xtc_db_fetch_array($shop_content_query);
  if($shop_content_data['content_file'] != '') {
    /* BOF - Hetfield - 2010-01-22 - Bugfix including contentfiles at SSL-Proxy */
    //$conditions = '<iframe SRC="' . DIR_WS_CATALOG . 'media/content/' . $shop_content_data['content_file'] . '" width="100%" height="300">';
    //$conditions .= '</iframe>';
    $conditions = '<div class="agbframe">' . file_get_contents(DIR_FS_DOCUMENT_ROOT . 'media/content/' . $shop_content_data['content_file']) . '</div>';
    /* EOF - Hetfield - 2010-01-22 - Bugfix including contentfiles at SSL-Proxy */
  } else {
    /* BOF - Hetfield - 2010-01-22 - Remove agb-textarea from checkout_payment */
    //$conditions = '<textarea name="blabla" cols="60" rows="10" readonly="readonly">' . strip_tags(str_replace('<br />', "\n", $shop_content_data['content_text'])) . '</textarea>';
    $conditions = '<div class="agbframe">' . $shop_content_data['content_text'] . '</div>';
    /* EOF - Hetfield - 2010-01-22 - Remove agb-textarea from checkout_payment */
  }
  $smarty->assign('AGB', $conditions);
  //BOF - Hetfield - 2010-01-22 - SSL for Content-Links per getContentLink
  //$smarty->assign('AGB_LINK', $main->getContentLink(3, MORE_INFO));
  $smarty->assign('AGB_LINK', $main->getContentLink(3, MORE_INFO,'SSL'));
    //EOF - Hetfield - 2010-01-22 - SSL for Content-Links per getContentLink
  if(isset($_GET['step']) && $_GET['step'] == 'step2') {
    $smarty->assign('AGB_checkbox', '<input type="checkbox" value="conditions" name="conditions" checked />');
  } else {
    $smarty->assign('AGB_checkbox', '<input type="checkbox" value="conditions" name="conditions" />');
  }
}

//check if display conditions on checkout page is true
if(DISPLAY_REVOCATION_ON_CHECKOUT == 'true') {
  if(GROUP_CHECK == 'true') {
    $group_check = "and group_ids LIKE '%c_" . $_SESSION['customers_status']['customers_status_id'] . "_group%'";
  }
  $shop_content_query = "SELECT
                        content_title,
                        content_heading,
                        content_text,
                        content_file
                        FROM " . TABLE_CONTENT_MANAGER . "
                        WHERE content_group='" . REVOCATION_ID . "' " . $group_check . "
                        AND languages_id='" . $_SESSION['languages_id'] . "'";
  $shop_content_query = xtc_db_query($shop_content_query);
  if($shop_content_query) {
    $shop_content_data = xtc_db_fetch_array($shop_content_query);
    if($shop_content_data['content_file'] != '') {
      ob_start();
      if(strpos($shop_content_data['content_file'], '.txt'))
        echo '<pre>';
      include(DIR_FS_CATALOG . 'media/content/' . $shop_content_data['content_file']);
      if(strpos($shop_content_data['content_file'], '.txt'))
        echo '</pre>';
      $revocation = ob_get_contents();
      ob_end_clean();
    } else {
      $revocation = $shop_content_data['content_text'];
    }
    $smarty->assign('REVOCATION', $revocation);
    $smarty->assign('REVOCATION_TITLE', $shop_content_data['content_heading']);
    $smarty->assign('REVOCATION_LINK', $main->getContentLink(REVOCATION_ID, MORE_INFO));
  }
}

// August 2012 Zollkosten als Muster mit Group ID 15
/*
if($order->delivery['country_id'] !== STORE_COUNTRY):
	if (GROUP_CHECK == 'true') {
		$group_check = "and group_ids LIKE '%c_" . $_SESSION['customers_status']['customers_status_id'] . "_group%'";
	}
	$shop_content_query = "SELECT
                         content_text
                         FROM " . TABLE_CONTENT_MANAGER . "
                         WHERE content_group='15' " . $group_check . "
                         AND languages_id='" . $_SESSION['languages_id'] . "'";
	$shop_content_query = xtc_db_query($shop_content_query);
	$shop_content_data = xtc_db_fetch_array($shop_content_query);
	$smarty->assign('CHECKOUT_ZOLL', $shop_content_data['content_text']);
endif;
*/

$smarty->assign('language', $_SESSION['language']);
if($kein_versand != 1) {
  $smarty->assign('SHIPPING_BLOCK', $shipping_block);
}
$payment_hidden = xtc_draw_hidden_field('payment','paypalexpress') . xtc_draw_hidden_field('act_payment','true');
$smarty->assign('PAYMENT_HIDDEN', $payment_hidden);
$smarty->caching = 0;
$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/checkout_paypal.html');
$smarty->assign('main_content', $main_content);
if(!defined('RM')) {
  $smarty->load_filter('output', 'note');
}
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include('includes/application_bottom.php');
?>
