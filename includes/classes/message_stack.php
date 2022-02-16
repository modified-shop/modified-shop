<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(message_stack.php,v 1.1 2003/05/19); www.oscommerce.com 
   (c) 2003	 nextcommerce (message_stack.php,v 1.9 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License
   Example usage:
   $messageStack = new messageStack();
   $messageStack->add('general', 'Error: Error 1', 'error');
   $messageStack->add('general', 'Error: Error 2', 'warning');
   if ($messageStack->size('general') > 0) echo $messageStack->output('general');
   ---------------------------------------------------------------------------------------*/

  class messageStack {
    var $global_array = array(
      'account',
      'categorie_listing',
      'checkout_confirmation',
      'checkout_payment',
      'checkout_shipping',
      'content',
      'default',
      'product_info', 
      'product_listing',  
      'product_reviews',
      'shopping_cart',
    );
    
    function __construct() {
      if (!isset($_SESSION['messageToStack'])) {
        $_SESSION['messageToStack'] = array();
      }
    }

    function add($class, $message, $type = 'error') {
      $this->addStack($class, $message, $type);
    }

    function add_session($class, $message, $type = 'error') {
      $this->addStack($class, $message, $type);
    }

    function addStack($class, $message, $type) {
      $_SESSION['messageToStack'][$class][$type][md5($message)] = $message;
    }
    
    function reset() {
      $_SESSION['messageToStack'] = array();
    }

    function size($class, $type = 'error') {
      $count = 0;
      if (isset($_SESSION['messageToStack'][$class][$type])) {
        $count += count($_SESSION['messageToStack'][$class][$type]);
      }
            
      if (in_array($class, $this->global_array) && isset($_SESSION['messageToStack']['global'][$type])) {
        $count += count($_SESSION['messageToStack']['global'][$type]);
      }
      
      return $count;
    }

    function output($class, $type = 'error') {
      $output = '';
      if ($this->size($class, $type) > 0) {
        if (in_array($class, $this->global_array)
            && isset($_SESSION['messageToStack']['global'][$type])
            )
        {
          if (!isset($_SESSION['messageToStack'][$class][$type])) {
            $_SESSION['messageToStack'][$class][$type] = array();
          }
          $_SESSION['messageToStack'][$class][$type] = array_merge($_SESSION['messageToStack'][$class][$type], $_SESSION['messageToStack']['global'][$type]);
          unset($_SESSION['messageToStack']['global'][$type]);
        }

        if (defined('CURRENT_TEMPLATE')
            && is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/message_stack.html')
            )
        {
          $smarty = new Smarty();
          $smarty->caching = 0;
          $smarty->assign('type', $type);
          $smarty->assign('language', $_SESSION['language']);
          $smarty->assign('messages', $_SESSION['messageToStack'][$class][$type]);
          $output = $smarty->fetch(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/message_stack.html');
        } else {
          foreach ($_SESSION['messageToStack'][$class][$type] as $message) {
            $output .= '<p>'.$message.'</p>';
          }
        }
        
        unset($_SESSION['messageToStack'][$class][$type]);
      }
      
      return $output;
    }
    
  }
?>