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

// pre-selection the cheapest shipping option
defined('CHECK_CHEAPEST_SHIPPING_MODUL') or define('CHECK_CHEAPEST_SHIPPING_MODUL', 'false'); // default: 'false'

// show selfpickup on free shipping
defined('SHOW_SELFPICKUP_FREE') or define('SHOW_SELFPICKUP_FREE', 'false'); // default: 'false'

// pre-selection the first payment option
defined('CHECK_FIRST_PAYMENT_MODUL') or define('CHECK_FIRST_PAYMENT_MODUL', 'false'); // default: 'false'

// create smarty elements
$smarty = new Smarty;

// include boxes
require(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

// include needed functions
require_once(DIR_FS_INC . 'xtc_address_label.inc.php');
require_once(DIR_FS_INC . 'xtc_get_address_format_id.inc.php');
require_once(DIR_FS_INC . 'xtc_count_shipping_modules.inc.php');
require_once(DIR_FS_INC . 'xtc_calculate_tax.inc.php');
require_once(DIR_FS_INC . 'xtc_display_tax_value.inc.php');
require_once(DIR_FS_INC . 'xtc_get_attributes_model.inc.php');

unset($_SESSION['tmp_oID']);

require_once (DIR_WS_CLASSES.'order.php');

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

require (DIR_WS_INCLUDES.'checkout_requirements.php');

// zahlungsweise in session schreiben
$_SESSION['payment'] = 'paypalexpress';

if(isset($_POST['act_shipping']))
  $_SESSION['act_shipping'] = 'true';

if(isset($_POST['act_payment']))
  $_SESSION['act_payment'] = 'true';

if(isset($_POST['payment']))
  $_SESSION['payment'] = xtc_db_prepare_input($_POST['payment']);

if(!empty($_POST['comments_added']))
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

// if no shipping destination address was selected, use the customers own address as default
if (!isset($_SESSION['sendto'])) {
	$_SESSION['sendto'] = $_SESSION['customer_default_address_id'];
} else {
	// verify the selected shipping address
	$check_address_query = xtc_db_query("SELECT count(*) as total 
	                                       FROM ".TABLE_ADDRESS_BOOK." 
	                                      WHERE customers_id = '".(int) $_SESSION['customer_id']."' 
	                                        AND address_book_id = '".(int) $_SESSION['sendto']."'");
	$check_address = xtc_db_fetch_array($check_address_query);
	if ($check_address['total'] != '1') {
		$_SESSION['sendto'] = $_SESSION['customer_default_address_id'];
		if (isset($_SESSION['shipping']))
			unset ($_SESSION['shipping']);
	}
}

// if no billing destination address was selected, use the customers own address as default
if (!isset($_SESSION['billto'])) {
  $_SESSION['billto'] = $_SESSION['customer_default_address_id'];
} else {
  // verify the selected billing address
  $check_address_query = xtc_db_query("SELECT count(*) AS total
                                         FROM " . TABLE_ADDRESS_BOOK . "
                                        WHERE customers_id = '" . (int) $_SESSION['customer_id'] . "'
                                          AND address_book_id = '" . (int) $_SESSION['billto'] . "'");
  $check_address = xtc_db_fetch_array($check_address_query);
  if ($check_address['total'] != '1') {
    $_SESSION['billto'] = $_SESSION['customer_default_address_id'];
    if (isset($_SESSION['payment'])) {
      unset ($_SESSION['payment']);
    }
  }
}

### -- SHIPPING ---###
$order = new order();

if($order->delivery['country']['iso_code_2'] != '') {
  $_SESSION['delivery_zone'] = $order->delivery['country']['iso_code_2'];
}

$no_shipping = false;
if ($order->content_type == 'virtual' || ($order->content_type == 'virtual_weight') || ($_SESSION['cart']->count_contents_virtual() == 0)) {
  $no_shipping = true;
}

$total_weight = $_SESSION['cart']->show_weight();
$total_count = $_SESSION['cart']->count_contents();

// load all enabled shipping modules
require (DIR_WS_CLASSES.'shipping.php');
$shipping_modules = new shipping;

$free_shipping = false;
require (DIR_WS_MODULES.'order_total/ot_shipping.php');
include (DIR_WS_LANGUAGES.$_SESSION['language'].'/modules/order_total/ot_shipping.php');
$ot_shipping = new ot_shipping;
$ot_shipping->process();

// process the selected shipping method
if (isset($_POST['action']) && ($_POST['action'] == 'process')) {

	if ((xtc_count_shipping_modules() > 0) || ($free_shipping == true)) {
		if ((isset($_POST['shipping'])) && (strpos($_POST['shipping'], '_'))) {
			$_SESSION['shipping'] = $_POST['shipping'];

			list ($module, $method) = explode('_', $_SESSION['shipping']);
			if ((isset(${$module}) && is_object(${$module}) ) || ($_SESSION['shipping'] == 'free_free')) {
				if ($_SESSION['shipping'] == 'free_free') {
					$quote[0]['methods'][0]['title'] = FREE_SHIPPING_TITLE;
					$quote[0]['methods'][0]['cost'] = '0';
				} else {
					$quote = $shipping_modules->quote($method, $module);
				}
				if (isset($quote['error'])) {
					unset ($_SESSION['shipping']);
				} else {
					if ((isset($quote[0]['methods'][0]['title'])) && (isset($quote[0]['methods'][0]['cost']))) {
						$_SESSION['shipping'] = array (
                'id' => $_SESSION['shipping'], 
                'title' => (($free_shipping == true) ? $quote[0]['methods'][0]['title'] : $quote[0]['module'].' ('.$quote[0]['methods'][0]['title'].')'), 
                'cost' => $quote[0]['methods'][0]['cost']
              );
						xtc_redirect(xtc_href_link(FILENAME_PAYPAL_CHECKOUT, '', 'SSL'));
					}
				}
			} else {
				unset ($_SESSION['shipping']);
			}
    }
	} else {
		$_SESSION['shipping'] = false;
		xtc_redirect(xtc_href_link(FILENAME_PAYPAL_CHECKOUT, '', 'SSL'));
	}
}

if ($no_shipping === true) $_SESSION['shipping'] = false;

// get all available shipping quotes
$quotes = $shipping_modules->quote();

// if no shipping method has been selected, automatically select the cheapest method.
// if the modules status was changed when none were available, to save on implementing
// a javascript force-selection method, also automatically select the cheapest shipping
// method if more than one module is now enabled
if ((!isset($_SESSION['shipping']) && CHECK_CHEAPEST_SHIPPING_MODUL == 'true') || (isset($_SESSION['shipping']) && ($_SESSION['shipping'] == false) && (xtc_count_shipping_modules() > 1))) {
	$_SESSION['shipping'] = $shipping_modules->cheapest();
}

if ($no_shipping === true) $_SESSION['shipping'] = false;

$breadcrumb->add(NAVBAR_TITLE_PAYPAL_CHECKOUT, xtc_href_link(FILENAME_PAYPAL_CHECKOUT, '', 'SSL'));

require(DIR_WS_INCLUDES.'header.php');

$smarty->assign('FORM_SHIPPING_ACTION', xtc_draw_form('checkout_shipping', xtc_href_link(FILENAME_PAYPAL_CHECKOUT, '', 'SSL')).xtc_draw_hidden_field('action', 'process'));
$smarty->assign('ADDRESS_SHIPPING_LABEL', xtc_address_label($_SESSION['customer_id'], $_SESSION['sendto'], true, ' ', '<br />'));
$smarty->assign('BUTTON_CONTINUE', xtc_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE));
$smarty->assign('FORM_END', '</form>');
$smarty->assign('ADDRESS_PAYMENT_LABEL', xtc_address_label($_SESSION['customer_id'], $_SESSION['billto'], true, ' ', '<br />'));

if (SHOW_SELFPICKUP_FREE == 'true') {
  if ($free_shipping == true) {
    $free_shipping = false;
    $quotes = array_merge($ot_shipping->quote(), $shipping_modules->quote('selfpickup', 'selfpickup'));
  }                    
}

$module_smarty = new Smarty;
$shipping_block = '';
if (xtc_count_shipping_modules() > 0) {
	$showtax = $_SESSION['customers_status']['customers_status_show_price_tax'];
	$module_smarty->assign('FREE_SHIPPING', $free_shipping);
	# free shipping or not...
	if ($free_shipping == true) {
		$module_smarty->assign('FREE_SHIPPING_TITLE', FREE_SHIPPING_TITLE);
		$module_smarty->assign('FREE_SHIPPING_DESCRIPTION', sprintf(FREE_SHIPPING_DESCRIPTION, $xtPrice->xtcFormat(MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER, true, 0, true)).xtc_draw_hidden_field('shipping', 'free_free'));
		$module_smarty->assign('FREE_SHIPPING_ICON', $quotes[$i]['icon']);
	} else {
		$radio_buttons = 0;
		#loop through installed shipping methods...
		for ($i = 0, $n = sizeof($quotes); $i < $n; $i ++) {
			if (!isset($quotes[$i]['error'])) {
				for ($j = 0, $n2 = sizeof($quotes[$i]['methods']); $j < $n2; $j ++) {
					# set the radio button to be checked if it is the method chosen
					$quotes[$i]['methods'][$j]['radio_buttons'] = $radio_buttons;
					$checked = ((isset($_SESSION['shipping']) && $quotes[$i]['id'].'_'.$quotes[$i]['methods'][$j]['id'] == $_SESSION['shipping']['id']) ? true : false);
					if (($checked == true) || ($n == 1 && $n2 == 1)) {
						$quotes[$i]['methods'][$j]['checked'] = 1;
					}
					if (($n > 1) || ($n2 > 1)) {
						if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 || !isset($quotes[$i]['tax'])) {
							$quotes[$i]['tax'] = 0;
            }
						$quotes[$i]['methods'][$j]['price'] = $xtPrice->xtcFormat(xtc_add_tax($quotes[$i]['methods'][$j]['cost'], $quotes[$i]['tax']), true, 0, true);						
            $quotes[$i]['methods'][$j]['radio_field'] = xtc_draw_radio_field('shipping', $quotes[$i]['id'].'_'.$quotes[$i]['methods'][$j]['id'], $checked, 'id="rd-'.($i+1).'" onclick="this.form.submit();"');
            $quotes[$i]['methods'][$j]['id'] = 'rd-'.($i+1);
					} else {
						if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0) {
							$quotes[$i]['tax'] = 0;
            }
            $quotes[$i]['methods'][$j]['price'] = $xtPrice->xtcFormat(xtc_add_tax($quotes[$i]['methods'][$j]['cost'], isset($quotes[$i]['tax']) ? $quotes[$i]['tax'] : 0), true, 0, true).xtc_draw_hidden_field('shipping', $quotes[$i]['id'].'_'.$quotes[$i]['methods'][$j]['id']);
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

if ($no_shipping === false) {
  $smarty->assign('SHIPPING_BLOCK', $shipping_block);
}

### -- PAYMENT ---###
$order = new order();

if ($order->billing['country']['iso_code_2'] != '' && $order->delivery['country']['iso_code_2'] == '') {
  $_SESSION['delivery_zone'] = $order->billing['country']['iso_code_2'];
} else {
  $_SESSION['delivery_zone'] = $order->delivery['country']['iso_code_2'];
}

// load all enabled payment modules
require_once (DIR_WS_CLASSES . 'payment.php');
$payment_modules = new payment($_SESSION['payment']);
$payment_modules->update_status();

require (DIR_WS_CLASSES . 'order_total.php');
// GV Code Start
$order_total_modules = new order_total();
$order_total_modules->collect_posts();
$order_total_modules->pre_confirmation_check();
// GV Code End

$credit_amount = 0;
if (ACTIVATE_GIFT_SYSTEM == 'true') {
  $credit_selection = $order_total_modules->credit_selection();
  for ($i = 0, $n = sizeof($credit_selection); $i < $n; $i++) {
    if ((isset($_SESSION['c'.$credit_selection[$i]['id']]) && $credit_selection[$i]['id'] == $_SESSION['c'.$credit_selection[$i]['id']])) {
      $credit_selection[$i]['checked'] = 1;
    } else {
      $credit_selection[$i]['checked'] = 0;
    }
    $credit_amount =  $credit_selection[$i]['credit_amount'];
    $credit_order_total = $credit_selection[$i]['credit_order_total'];
    $credit_selection[$i]['selection'] = xtc_draw_checkbox_field('c'.$credit_selection[$i]['id'], $credit_amount, $credit_selection[$i]['checked'], 'id="rd-'.'c'.$credit_selection[$i]['id'].'"');
    $credit_selection[$i]['selection'] .= '<input type="hidden" name="credit_order_total"  id="cot-'.'c'.$credit_selection[$i]['id'].'" value="'.$credit_order_total.'">';
    $credit_selection[$i]['credit_amount'] = $xtPrice->xtcFormat($credit_amount, true);
    $module_smarty->assign('credit_amount_payment_info', $credit_amount >= $credit_order_total ? GV_NO_PAYMENT_INFO : GV_ADD_PAYMENT_INFO);
  }
  $module_smarty->assign('module_gift', $credit_selection);  
}

$total = $xtPrice->xtcFormat($order->info['total'], false);
if ($total > 0 || ($credit_amount && $total > 0) || (isset($_SESSION['credit_covers']) && $_SESSION['credit_covers'] == 1 && $total > 0)) {
  if (isset($_GET['payment_error']) && is_object(${ $_GET['payment_error'] }) && ($error = ${$_GET['payment_error']}->get_error())) {
    $smarty->assign('error',  encode_htmlspecialchars($error['error']));
  }
  ### Paypal Express Modul
  if(isset($_SESSION['reshash']['FORMATED_ERRORS'])) {
    $smarty->assign('error', $_SESSION['reshash']['FORMATED_ERRORS']);
  }
  ### Paypal Express Modul

  $radio_buttons = 0;
  $selection = $payment_modules->selection();
  for ($i = 0, $n = sizeof($selection); $i < $n; $i++) {
    //ot_payment Anzeige Zahlungsrabatt bei Zahlungsauswahl
    if (isset($GLOBALS['ot_payment']) && !isset($selection[$i]['module_cost'])) {
      $selection[$i]['module_cost'] = $GLOBALS['ot_payment']->get_module_cost($selection[$i]);
    }
    $selection[$i]['radio_buttons'] = $radio_buttons;
    if ((isset($_SESSION['payment']) && $selection[$i]['id'] == $_SESSION['payment']) || (!isset($_SESSION['payment']) && $i == 0 && CHECK_FIRST_PAYMENT_MODUL == 'true')) { // pre-selection the first payment option
      $selection[$i]['checked'] = 1;
    } else {
      $selection[$i]['checked'] = 0;
    }

    if (sizeof($selection) > 1) {
      $selection[$i]['selection'] = xtc_draw_radio_field('payment', $selection[$i]['id'], ($selection[$i]['checked']), 'id="rd-'.($i+1).'" onclick="this.form.submit();"').xtc_draw_hidden_field('act_payment', 'true'); // pre-selection the first payment option
    } else {
      $selection[$i]['selection'] = xtc_draw_hidden_field('payment', $selection[$i]['id']).xtc_draw_hidden_field('act_payment', 'true');
    }

    if (!isset($selection[$i]['error'])) {
      $radio_buttons++;
    }
  }
  $module_smarty->assign('module_content', $selection);
} 
//Coupon 100%
elseif (isset($_SESSION['cc_id']) && $total <= 0) {
  $order_total_modules->pre_confirmation_check();
  $smarty->assign('GV_COVER', 'true');
} 
//Guthaben
elseif (!isset($_SESSION['cot_gv'])) {
  $order_total_modules->pre_confirmation_check();
  //$smarty->assign('GV_COVER', 'true');
}

$module_smarty->caching = 0;
$payment_block = $module_smarty->fetch(CURRENT_TEMPLATE . '/module/checkout_payment_block.html');

if($messageStack->size('checkout_payment') > 0) {
  $smarty->assign('error', $messageStack->output('checkout_payment'));
}

if (SHOW_IP_LOG == 'true') {
  $smarty->assign('IP_LOG', 'true');
  $smarty->assign('CUSTOMERS_IP', $_SESSION['tracking']['ip']);
}

//allow duty-note in checkout_confirmation
$smarty->assign('DELIVERY_DUTY_INFO', $main->getDeliveryDutyInfo($order->delivery['country']['iso_code_2']));

$smarty->assign('DELIVERY_LABEL', xtc_address_format($order->delivery['format_id'], $order->delivery, 1, ' ', '<br />'));
if (!isset($_SESSION['credit_covers']) || $_SESSION['credit_covers'] != '1') {
  $smarty->assign('BILLING_LABEL', xtc_address_format($order->billing['format_id'], $order->billing, 1, ' ', '<br />'));
}

if(PAYPAL_EXPRESS_ADDRESS_CHANGE == 'true') {
  $smarty->assign('SHIPPING_ADDRESS_EDIT', xtc_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL'));
  $smarty->assign('BILLING_ADDRESS_EDIT', xtc_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', 'SSL'));
}

if ($_SESSION['sendto'] != false) {
  if ($order->info['shipping_method']) {
    $smarty->assign('SHIPPING_METHOD', $order->info['shipping_method']);
    $smarty->assign('SHIPPING_EDIT', xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  }
}

//new output array, set in includes/classes/order.php function cart
$smarty->assign('PRODUCTS_ARRAY', $order->products);

if ($order->info['payment_method'] != 'no_payment' && $order->info['payment_method'] != '') {
  include_once (DIR_WS_LANGUAGES . '/' . $_SESSION['language'] . '/modules/payment/' . $order->info['payment_method'] . '.php');
  $smarty->assign('PAYMENT_METHOD', constant('MODULE_PAYMENT_' . strtoupper($order->info['payment_method']) . '_TEXT_TITLE'));
}
if (isset($_SESSION['credit_covers']) && $order->info['payment_method'] == 'no_payment') {
  include_once (DIR_WS_LANGUAGES . '/' . $_SESSION['language'] . '/modules/order_total/ot_gv.php');
  $smarty->assign('PAYMENT_METHOD', constant('MODULE_ORDER_TOTAL_GV_TITLE'));
}

if (MODULE_ORDER_TOTAL_INSTALLED) {
  $order_total_modules->process();
  $total_block = $order_total_modules->output();
  $smarty->assign('TOTAL_BLOCK', $total_block);
}

if (is_array($payment_modules->modules) && ($confirmation = $payment_modules->confirmation())) { // $confirmation['title'];
  $smarty->assign('PAYMENT_INFORMATION', (isset($confirmation['fields']) ? $confirmation['fields'] : ''));
}

if (isset(${$_SESSION['payment']}->form_action_url) && (!isset(${$_SESSION['payment']}->tmpOrders) || !${$_SESSION['payment']}->tmpOrders)) {
  $form_action_url = ${$_SESSION['payment']}->form_action_url;
} else {
  $form_action_url = xtc_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL');
}
$smarty->assign('CHECKOUT_FORM', xtc_draw_form('checkout_confirmation', $form_action_url, 'post'));
$smarty->assign('MODULE_BUTTONS', (is_array($payment_modules->modules) ? $payment_modules->process_button() : ''));
$smarty->assign('CHECKOUT_BUTTON', xtc_image_submit('button_confirm_order.gif', IMAGE_BUTTON_CONFIRM_ORDER) . '</form>' . "\n");

//check if display conditions on checkout page is true
if (DISPLAY_REVOCATION_ON_CHECKOUT == 'true') {
  //revocation  
  $shop_content_data = $main->getContentData(REVOCATION_ID);
  $smarty->assign('REVOCATION', $shop_content_data['content_text']);
  $smarty->assign('REVOCATION_TITLE', $shop_content_data['content_heading']);
  $smarty->assign('REVOCATION_LINK', $main->getContentLink(REVOCATION_ID, MORE_INFO, 'SSL'));
  //agb
  $shop_content_data = $main->getContentData(3);
  $smarty->assign('AGB_TITLE', $shop_content_data['content_heading']);
  $smarty->assign('AGB_LINK', $main->getContentLink(3, MORE_INFO,'SSL'));
  $smarty->assign('TEXT_AGB_CHECKOUT', sprintf(TEXT_AGB_CHECKOUT,$main->getContentLink(3, MORE_INFO,'SSL') , $main->getContentLink(REVOCATION_ID, MORE_INFO,'SSL')));
}

//check if display conditions on checkout page is true
if (DISPLAY_CONDITIONS_ON_CHECKOUT == 'true') {
  $shop_content_data = $main->getContentData(3);
  $smarty->assign('AGB', '<div class="agbframe">' . $shop_content_data['content_text'] . '</div>');
  $smarty->assign('AGB_LINK', $main->getContentLink(3, MORE_INFO,'SSL'));
  $smarty->assign('AGB_checkbox', '<input type="checkbox" value="conditions" name="conditions" id="conditions"'.(isset($_GET['step']) && $_GET['step'] == 'step2' ? ' checked="checked"' : '').' />');
}

$smarty->assign('COMMENTS', xtc_draw_textarea_field('comments', 'soft', '60', '5', isset($_SESSION['comments'])?$_SESSION['comments']:'') . xtc_draw_hidden_field('comments_added', 'YES')); //Dokuman - 2012-05-31 - fix paypal_checkout notices
$smarty->assign('ADR_checkbox', '<input type="checkbox" value="address" name="check_address" id="address" />');

$smarty->assign('language', $_SESSION['language']);
$smarty->assign('PAYMENT_HIDDEN', xtc_draw_hidden_field('payment','paypalexpress') . xtc_draw_hidden_field('act_payment','true'));

$smarty->caching = 0;
$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/checkout_paypal.html');
$smarty->assign('main_content', $main_content);
if(!defined('RM')) {
  $smarty->load_filter('output', 'note');
}
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include('includes/application_bottom.php');
?>
