<?php
/* -----------------------------------------------------------------------------------------
   $Id: order_total.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(order_total.php,v 1.4 2003/02/11); www.oscommerce.com
   (c) 2003 nextcommerce (order_total.php,v 1.6 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce (order_total.php 1029 2005-07-14)

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contribution:

   Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
   http://www.oscommerce.com/community/contributions,282
   Copyright (c) Strider | Strider@oscworks.com
   Copyright (c) Nick Stanko of UkiDev.com, nick@ukidev.com
   Copyright (c) Andre ambidex@gmx.net
   Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

class order_total {
  var $modules;
  
  function __construct() {
    global $order;
    
    $this->order_total = $order->info['total'];
    
    $this->modules = array();
    
    if (defined('MODULE_ORDER_TOTAL_INSTALLED') && xtc_not_null(MODULE_ORDER_TOTAL_INSTALLED)) {
      $modules = explode(';', MODULE_ORDER_TOTAL_INSTALLED);
      $module_directory = DIR_WS_MODULES . 'order_total/';
      foreach($modules as $file) {
        $class = substr($file, 0, strrpos($file, '.'));
        $class = str_replace('ot_','',$class);
        $module_status = (defined('MODULE_ORDER_TOTAL_'. strtoupper($class) .'_STATUS') && strtolower(constant('MODULE_ORDER_TOTAL_'. strtoupper($class) .'_STATUS')) == 'true') ? true : false;
        if (is_file($module_directory . $file) && $module_status) {
          $this->modules[] = $file;
        }
      }
      unset($modules);
      
      while (list (, $value) = each($this->modules)) {
        include_once(DIR_WS_LANGUAGES.$_SESSION['language'].'/modules/order_total/'.$value);
        include_once(DIR_WS_MODULES.'order_total/'.$value);
        $class = substr($value, 0, strrpos($value, '.'));
        $GLOBALS[$class] = new $class ();
      }
    }
  }

  // GV Code Start
  // ICW ORDER TOTAL CREDIT CLASS/GV SYSTEM - START ADDITION
  //
  // This function is called in checkout payment after display of payment methods. It actually calls
  // two credit class functions.
  //
  // use_credit_amount() is normally a checkbox used to decide whether the credit amount should be applied to reduce
  // the order total. Whether this is a Gift Voucher, or discount coupon or reward points etc.
  //
  // The second function called is credit_selection(). This in the credit classes already made is usually a redeem box.
  // for entering a Gift Voucher number. Note credit classes can decide whether this part is displayed depending on
  // E.g. a setting in the admin section.
  //
  function credit_selection() {
    $selection_string = '';
    //$close_string = '';
    //$credit_class_string = '';
    $output_array = array();
    if (MODULE_ORDER_TOTAL_INSTALLED) {
      reset($this->modules);
      $output_string = '';
      while (list (, $value) = each($this->modules)) {
        $class = substr($value, 0, strrpos($value, '.'));
        if ($GLOBALS[$class]->enabled && isset($GLOBALS[$class]->credit_class) && $GLOBALS[$class]->credit_class) {
          $use_credit_string = $GLOBALS[$class]->use_credit_amount();
          if ($selection_string == '') {
            $selection_string = $GLOBALS[$class]->credit_selection();
          }
          if (($use_credit_string != '') || ($selection_string != '')) {
            $output_array[] = array ('id' => $GLOBALS[$class]->code,
                                     'module' => $GLOBALS[$class]->title,
                                     'description' => $GLOBALS[$class]->info,
                                     'credit_amount' => method_exists($GLOBALS[$class], 'get_credit_amount') ? $GLOBALS[$class]->get_credit_amount() : '0',
                                     'credit_order_total' => method_exists($GLOBALS[$class], 'get_order_total') ? $GLOBALS[$class]->get_order_total() : '0'
                                     );
          }
        }
      }
      if (count($output_array)>0) {
        return $output_array;
      }
    }
  
    return $output_array;
  }

  // update_credit_account is called in checkout process on a per product basis. It's purpose
  // is to decide whether each product in the cart should add something to a credit account.
  // e.g. for the Gift Voucher it checks whether the product is a Gift voucher and then adds the amount
  // to the Gift Voucher account.
  // Another use would be to check if the product would give reward points and add these to the points/reward account.
  //
  function update_credit_account($i) {
    if (MODULE_ORDER_TOTAL_INSTALLED) {
      reset($this->modules);
      while (list (, $value) = each($this->modules)) {
        $class = substr($value, 0, strrpos($value, '.'));
        if (($GLOBALS[$class]->enabled && isset($GLOBALS[$class]->credit_class) && $GLOBALS[$class]->credit_class)) {
          $GLOBALS[$class]->update_credit_account($i);
        }
      }
    }
  }
  
  // This function is called in checkout confirmation.
  // It's main use is for credit classes that use the credit_selection() method. This is usually for
  // entering redeem codes(Gift Vouchers/Discount Coupons). This function is used to validate these codes.
  // If they are valid then the necessary actions are taken, if not valid we are returned to checkout payment
  // with an error
  //
  function collect_posts() {
    if (MODULE_ORDER_TOTAL_INSTALLED) {
      reset($this->modules);
      while (list (, $value) = each($this->modules)) {
        $class = substr($value, 0, strrpos($value, '.'));
        if (($GLOBALS[$class]->enabled && isset($GLOBALS[$class]->credit_class) && $GLOBALS[$class]->credit_class)) {
          $post_var = 'c'.$GLOBALS[$class]->code;
          if (isset($_POST[$post_var]) && $_POST[$post_var]) {
            $_SESSION[$post_var] = $_POST[$post_var];
          }
          $GLOBALS[$class]->collect_posts();
        }
      }
    }
  }
  
  // pre_confirmation_check is called on checkout confirmation. It's function is to decide whether the
  // credits available are greater than the order total. If they are then a variable (credit_covers) is set to
  // true. This is used to bypass the payment method. In other words if the Gift Voucher is more than the order
  // total, we don't want to go to paypal etc.
  //
  function pre_confirmation_check() {
    global $order;
    
    // fisrt unset session
    unset ($_SESSION['credit_covers']);
    
    if (MODULE_ORDER_TOTAL_INSTALLED) {
      $total_deductions = 0;
      reset($this->modules);
      while (list (, $value) = each($this->modules)) {
        $class = substr($value, 0, strrpos($value, '.'));
        $order_total = $this->get_order_total_main($class, $this->order_total);
        if (($GLOBALS[$class]->enabled && isset($GLOBALS[$class]->credit_class) && $GLOBALS[$class]->credit_class)) {
          $total_deductions = $total_deductions + $GLOBALS[$class]->pre_confirmation_check($order_total);
          $order_total = $order_total - $GLOBALS[$class]->pre_confirmation_check($order_total);
        }
      }
      if ($this->order_total - $total_deductions <= 0) {
        $_SESSION['credit_covers'] = true;
      } else { // belts and suspenders to get rid of credit_covers variable if it gets set once and they put something else in the cart
        unset ($_SESSION['credit_covers']);
      }
    }
  }
  
  // this function is called in checkout process. it tests whether a decision was made at checkout payment to use
  // the credit amount be applied aginst the order. If so some action is taken. E.g. for a Gift voucher the account
  // is reduced the order total amount.
  //
  function apply_credit() {
    if (MODULE_ORDER_TOTAL_INSTALLED) {
      reset($this->modules);
      while (list (, $value) = each($this->modules)) {
        $class = substr($value, 0, strrpos($value, '.'));
        if (($GLOBALS[$class]->enabled && isset($GLOBALS[$class]->credit_class) && $GLOBALS[$class]->credit_class)) {
          $GLOBALS[$class]->apply_credit();
        }
      }
    }
  }
  
  // Called in checkout process to clear session variables created by each credit class module.
  //
  function clear_posts() {
    if (MODULE_ORDER_TOTAL_INSTALLED) {
      reset($this->modules);
      while (list (, $value) = each($this->modules)) {
        $class = substr($value, 0, strrpos($value, '.'));
        if (($GLOBALS[$class]->enabled && isset($GLOBALS[$class]->credit_class) && $GLOBALS[$class]->credit_class)) {
          $post_var = 'c'.$GLOBALS[$class]->code;
          unset ($_SESSION[$post_var]);
        }
      }
    }
  }
  
  // Called at various times. This function calulates the total value of the order that the
  // credit will be appled aginst. This varies depending on whether the credit class applies
  // to shipping & tax
  //
  function get_order_total_main($class, $order_total) {
    global $credit, $order;
    //      if ($GLOBALS[$class]->include_tax == 'false') $order_total=$order_total-$order->info['tax'];
    //      if ($GLOBALS[$class]->include_shipping == 'false') $order_total=$order_total-$order->info['shipping_cost'];
    return $order_total;
  }

  function process() {
    $order_total_array = array ();
    if (is_array($this->modules)) {
      reset($this->modules);
      while (list (, $value) = each($this->modules)) {
        $class = substr($value, 0, strrpos($value, '.'));
        if ($GLOBALS[$class]->enabled) {
          $GLOBALS[$class]->output = array();
          $GLOBALS[$class]->process();

          for ($i = 0, $n = sizeof($GLOBALS[$class]->output); $i < $n; $i ++) {
            if (xtc_not_null($GLOBALS[$class]->output[$i]['title']) && xtc_not_null($GLOBALS[$class]->output[$i]['text'])) {
              $order_total_array[] = array (
                'code' => $GLOBALS[$class]->code, 
                'title' => $GLOBALS[$class]->output[$i]['title'], 
                'text' => $GLOBALS[$class]->output[$i]['text'], 
                'value' => $GLOBALS[$class]->output[$i]['value'], 
                'sort_order' => $GLOBALS[$class]->sort_order
                );
            }
          }
        }
      }
    }

    return $order_total_array;
  }

  function output() {
    $output_string = '';
    if (is_array($this->modules)) {
      reset($this->modules);
      while (list (, $value) = each($this->modules)) {
        $class = substr($value, 0, strrpos($value, '.'));
        if ($GLOBALS[$class]->enabled) {
          $size = sizeof($GLOBALS[$class]->output);
          for ($i = 0; $i < $size; $i ++) {
            $output_string .= '              <tr>'."\n".'                <td align="right" class="main">'.$GLOBALS[$class]->output[$i]['title'].'</td>'."\n".'                <td align="right" class="main">'.$GLOBALS[$class]->output[$i]['text'].'</td>'."\n".'              </tr>';
          }
        }
      }
    }

    return $output_string;
  }

  function output_array() {
    $arr_output = array();
    if (is_array($this->modules)) {
      reset($this->modules);
      while (list (, $value) = each($this->modules)) {
        $class = substr($value, 0, strrpos($value, '.'));
        if ($GLOBALS[$class]->enabled) {
          $size = sizeof($GLOBALS[$class]->output);
          for ($i = 0; $i < $size; $i ++) {
            $arr_output[] = array(
              'title' => $GLOBALS[$class]->output[$i]['title'], 
              'text' => $GLOBALS[$class]->output[$i]['text'],
              'value' => $GLOBALS[$class]->output[$i]['value'],
              'class' => $class,
            );
          }
        }
      }
    }

    return $arr_output;
  }

  ## PayPal
  function pp_output() {
    return $this->output_array();   
  }

}
?>