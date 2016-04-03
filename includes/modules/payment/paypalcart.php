<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


// include needed classes
require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPayment.php');


class paypalcart extends PayPalPayment {
  var $code, $title, $description, $extended_description, $enabled;


  function __construct() {
    global $order;
    
    PayPalPayment::__construct('paypalcart');

		$this->tmpOrders = true;
  }


  function selection() {
    xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'payment_error='.$this->code, 'NONSSL'));
  }
  
  
  function pre_confirmation_check() {
    global $order, $smarty;

    // include needed functions
    require_once (DIR_FS_INC.'xtc_count_shipping_modules.inc.php');

    // process the selected shipping method
    if (isset($_POST['action']) && ($_POST['action'] == 'process')) {

      if ($order->delivery['country']['iso_code_2'] != '') {
        $_SESSION['delivery_zone'] = $order->delivery['country']['iso_code_2'];
      }
      // load all enabled shipping modules
      require_once (DIR_WS_CLASSES.'shipping.php');
      $shipping_modules = new shipping;

      $free_shipping = false;
      require_once (DIR_WS_MODULES.'order_total/ot_shipping.php');
      include_once (DIR_WS_LANGUAGES.$_SESSION['language'].'/modules/order_total/ot_shipping.php');
      $ot_shipping = new ot_shipping;
      $ot_shipping->process();

      if ((xtc_count_shipping_modules() > 0) || ($free_shipping == true)) {
        if ((isset($_POST['shipping'])) && (strpos($_POST['shipping'], '_'))) {
          $_SESSION['shipping'] = $_POST['shipping'];#sec

          list ($module, $method) = explode('_', $_SESSION['shipping']);
          if ((isset($GLOBALS[$module]) && is_object($GLOBALS[$module]) ) || ($_SESSION['shipping'] == 'free_free')) {
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
                    'title' => (($free_shipping == true) ? $quote[0]['methods'][0]['title'] : $quote[0]['module'].(($quote[0]['methods'][0]['title'] != '') ? ' ('.$quote[0]['methods'][0]['title'].')' : '')), 
                    'cost' => $quote[0]['methods'][0]['cost']
                  );
                xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_CONFIRMATION, xtc_get_all_get_params(array('conditions_message')), 'SSL'));
              }
            }
          } else {
            $smarty->assign('error', ERROR_CHECKOUT_SHIPPING_NO_METHOD);
          }
        } else {
          $smarty->assign('error', ERROR_CHECKOUT_SHIPPING_NO_METHOD);
        }
      } else {
        $_SESSION['shipping'] = false;
        $smarty->assign('error', ERROR_CHECKOUT_SHIPPING_NO_MODULE);
      }
    }
  }


  function confirmation() {
    global $order, $smarty, $xtPrice, $main, $messageStack;
        
    if (isset($_GET['conditions_message'])) {
      $message_condition = str_replace('\n', '', ERROR_CONDITIONS_NOT_ACCEPTED);
      $message_address = str_replace('\n', '', ERROR_ADDRESS_NOT_ACCEPTED);
      switch($_GET['conditions_message']) {
        case "1":
          $messageStack->add('conditions_message', $message_condition);
          break;
        case "13":
          $messageStack->add('conditions_message', $message_condition);
          $messageStack->add('shipping_message', ERROR_CHECKOUT_SHIPPING_NO_METHOD);
          break;
        case "2":
          $messageStack->add('conditions_message', $message_address);
          break;
        case "23":
          $messageStack->add('conditions_message', $message_address);
          $messageStack->add('shipping_message', ERROR_CHECKOUT_SHIPPING_NO_METHOD);
          break;
        case "12":
          $messageStack->add('conditions_message', $message_condition);
          $messageStack->add('conditions_message', $message_address);
          break;
        case "123":
          $messageStack->add('conditions_message', $message_condition);
          $messageStack->add('conditions_message', $message_address);
          $messageStack->add('shipping_message', ERROR_CHECKOUT_SHIPPING_NO_METHOD);
          break;
        case "3":
          $messageStack->add('shipping_message', ERROR_CHECKOUT_SHIPPING_NO_METHOD);
          break;
      }
    }

    // include needed functions
    require_once (DIR_FS_INC.'xtc_count_shipping_modules.inc.php');
    
    if ($order->delivery['country']['iso_code_2'] != '') {
      $_SESSION['delivery_zone'] = $order->delivery['country']['iso_code_2'];
    }

    $no_shipping = false;
    if ($order->content_type == 'virtual' || ($order->content_type == 'virtual_weight') || ($_SESSION['cart']->count_contents_virtual() == 0)) {
      $no_shipping = true;
    }

    $total_weight = $_SESSION['cart']->show_weight();
    $total_count = $_SESSION['cart']->count_contents();

    // load all enabled shipping modules
    $shipping_modules = new shipping;

    // add unallowed payment / shipping
    if (defined('MODULE_EXCLUDE_PAYMENT_STATUS') && MODULE_EXCLUDE_PAYMENT_STATUS == 'True') {
      for ($i=1; $i<=MODULE_EXCLUDE_PAYMENT_NUMBER; $i++) {
        $payment_exclude = explode(',', constant('MODULE_EXCLUDE_PAYMENT_PAYMENT_'.$i));
        
        if (in_array($this->code, $payment_exclude)) {
          $shipping_exclude = explode(',', constant('MODULE_EXCLUDE_PAYMENT_SHIPPING_'.$i));
        
          for ($i=0, $n=count($shipping_modules->modules); $i<$n; $i++) {
            if (in_array(substr($shipping_modules->modules[$i], 0, -4), $shipping_exclude)) {
              unset($shipping_modules->modules[$i]);
            }
          }
        
        }
      }
    }

    $free_shipping = false;
    $ot_shipping = new ot_shipping;
    $ot_shipping->process();

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

    if (defined('SHOW_SELFPICKUP_FREE') && SHOW_SELFPICKUP_FREE == 'true') {
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
        $module_smarty->assign('FREE_SHIPPING_DESCRIPTION', sprintf(FREE_SHIPPING_DESCRIPTION, $xtPrice->xtcFormat($free_shipping_value_over, true, 0, true)).xtc_draw_hidden_field('shipping', 'free_free'));
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
                $quotes[$i]['methods'][$j]['radio_field'] = xtc_draw_radio_field('shipping', $quotes[$i]['id'].'_'.$quotes[$i]['methods'][$j]['id'], $checked, 'id="rd-'.($i+1).'" onChange="this.form.submit()"');
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
      $module_smarty->assign('language', $_SESSION['language']);
      $module_smarty->caching = 0;
      $shipping_block = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/checkout_shipping_block.html');
    }
    
    if ($no_shipping === false) {
      $module_smarty->assign('FORM_SHIPPING_ACTION', xtc_draw_form('checkout_shipping', xtc_href_link(FILENAME_CHECKOUT_CONFIRMATION, xtc_get_all_get_params(), 'SSL')).xtc_draw_hidden_field('action', 'process'));
    
      $shipping_found = false;
      for ($i = 0, $n = sizeof($quotes); $i < $n; $i ++) {
        for ($j = 0, $n2 = sizeof($quotes[$i]['methods']); $j < $n2; $j ++) {
          if ($quotes[$i]['id'].'_'.$quotes[$i]['methods'][$j]['id'] == $_SESSION['shipping']['id']) {
            $shipping_found = true;
            break;
          }
        }
      }
      if ($shipping_found === false) {
        $module_smarty->assign('shipping_message', ERROR_CHECKOUT_SHIPPING_NO_METHOD);
        /*
        if (xtc_count_shipping_modules() == 1) {
          $module_smarty->assign('BUTTON_CONTINUE', xtc_image_submit('button_confirm.gif', IMAGE_BUTTON_CONFIRM));
        }
        */
      }
      $module_smarty->assign('BUTTON_CONTINUE', xtc_image_submit('button_confirm.gif', IMAGE_BUTTON_CONFIRM));
      $module_smarty->assign('FORM_END', '</form>');
    
      if ($no_shipping === false) {
        $module_smarty->assign('SHIPPING_BLOCK', $shipping_block);
      }

      if ($messageStack->size('shipping_message') > 0) {
        $module_smarty->assign('shipping_message', $messageStack->output('shipping_message'));
      }

      $module_smarty->assign('language', $_SESSION['language']);
      $module_smarty->caching = 0;
      $shipping_method = $module_smarty->fetch(DIR_FS_EXTERNAL.'/paypal/templates/shipping_block.html');
    
      $smarty->assign('SHIPPING_METHOD', $shipping_method);
    }
    $smarty->assign('SHIPPING_ADDRESS_EDIT', xtc_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, xtc_get_all_get_params(), 'SSL'));
    $smarty->assign('BILLING_ADDRESS_EDIT', xtc_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, xtc_get_all_get_params(), 'SSL'));

    $smarty->clear_assign('SHIPPING_EDIT');
    $smarty->clear_assign('PAYMENT_EDIT');
    //$smarty->clear_assign('PRODUCTS_EDIT');
  }


  function process_button() {
    global $smarty, $main, $messageStack;
    
    $module_smarty = new Smarty;
    
    //check if display conditions on checkout page is true
    if (DISPLAY_REVOCATION_ON_CHECKOUT == 'true') {
      //revocation  
      $shop_content_data = $main->getContentData(REVOCATION_ID);
      $module_smarty->assign('REVOCATION', $shop_content_data['content_text']);
      $module_smarty->assign('REVOCATION_TITLE', $shop_content_data['content_heading']);
      $module_smarty->assign('REVOCATION_LINK', $main->getContentLink(REVOCATION_ID, MORE_INFO, 'SSL'));
      //agb
      $shop_content_data = $main->getContentData(3);
      $module_smarty->assign('AGB_TITLE', $shop_content_data['content_heading']);
      $module_smarty->assign('AGB_LINK', $main->getContentLink(3, MORE_INFO,'SSL'));
      $module_smarty->assign('TEXT_AGB_CHECKOUT', sprintf(TEXT_AGB_CHECKOUT,$main->getContentLink(3, MORE_INFO,'SSL') , $main->getContentLink(REVOCATION_ID, MORE_INFO,'SSL')));
    }

    //check if display conditions on checkout page is true
    if (DISPLAY_CONDITIONS_ON_CHECKOUT == 'true') {
      $shop_content_data = $main->getContentData(3);
      $module_smarty->assign('AGB', '<div class="agbframe">' . $shop_content_data['content_text'] . '</div>');
      $module_smarty->assign('AGB_LINK', $main->getContentLink(3, MORE_INFO,'SSL'));
      $module_smarty->assign('AGB_checkbox', '<input type="checkbox" value="conditions" name="conditions" id="conditions"'.(isset($_GET['step']) && $_GET['step'] == 'step2' ? ' checked="checked"' : '').' />');
    }

    $module_smarty->assign('COMMENTS', xtc_draw_textarea_field('comments', 'soft', '60', '5', isset($_SESSION['comments'])?$_SESSION['comments']:'') . xtc_draw_hidden_field('comments_added', 'YES')); //Dokuman - 2012-05-31 - fix paypal_checkout notices
    $module_smarty->assign('ADR_checkbox', '<input type="checkbox" value="address" name="check_address" id="address" />');

    if ($messageStack->size('conditions_message') > 0) {
      $module_smarty->assign('conditions_message', $messageStack->output('conditions_message'));
    }

    $module_smarty->assign('language', $_SESSION['language']);
    $module_smarty->caching = 0;
    $process_button = $module_smarty->fetch(DIR_FS_EXTERNAL.'/paypal/templates/comments_block.html');
    
    return $process_button;
  }
  

  function before_process() {
    if (isset($_SESSION['payment']) && $_SESSION['payment'] == $this->code) {
      if (isset($_SESSION['paypal']['paymentId'])) {
        if ($_POST['comments_added'] != '') {
          $_SESSION['comments'] = xtc_db_prepare_input($_POST['comments']);
        }
        $error_mess  = '';
        if (DISPLAY_CONDITIONS_ON_CHECKOUT == 'true' && $_POST['conditions'] != 'conditions') {
          $error_mess = '1';
        }
        if ($_POST['check_address'] != 'address') {
          $error_mess .= '2';
        }
        if (!isset($_SESSION['shipping']) 
            || ($_SESSION['shipping'] !== false && !is_array($_SESSION['shipping']))
            ) 
        {
          $error_mess .= '3';
        }
        if($error_mess != '') {
          xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_CONFIRMATION, xtc_get_all_get_params(array('conditions_message')).'conditions=true&conditions_message='.$error_mess, 'SSL', true, false));
        }
      }
    }
  }


  function after_process() {
    $this->complete_cart();
		unset($_SESSION['paypal']);
  }


  function keys() {
		return array('MODULE_PAYMENT_PAYPALCART_STATUS', 
		             'MODULE_PAYMENT_PAYPALCART_ALLOWED', 
		             'MODULE_PAYMENT_PAYPALCART_ZONE',
		             'MODULE_PAYMENT_PAYPALCART_SORT_ORDER'
    );
  }

}
?>