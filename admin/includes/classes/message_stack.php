<?php
/* --------------------------------------------------------------
   $Id$   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(message_stack.php,v 1.5 2002/11/22); www.oscommerce.com 
   (c) 2003	 nextcommerce (message_stack.php,v 1.6 2003/08/18); www.nextcommerce.org

   Released under the GNU General Public License 

   Example usage:

   $messageStack = new messageStack();
   $messageStack->add('Error: Error 1', 'error');
   $messageStack->add('Error: Error 2', 'warning');
   if ($messageStack->size > 0) echo $messageStack->output();
  
   --------------------------------------------------------------*/

  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

  class messageStack {

    var $size = 0;
    var $errors;
    
    function __construct() {
      $this->errors = array();
      if (isset($_SESSION['messageToAdminStack']) && count($_SESSION['messageToAdminStack']) > 0) {
        foreach ($_SESSION['messageToAdminStack'] as $type => $message_stack) {
          foreach ($message_stack as $message) {
            $this->add($message, $type);
          }
        }
        unset($_SESSION['messageToAdminStack']);
      }
    }

    function add($message, $type = 'error') {
      $this->errors[$type][md5($message)] = $message;
      $this->size++;
    }

    function add_session($message, $type = 'error') {
      if (!isset($_SESSION['messageToAdminStack'])) {
        $_SESSION['messageToAdminStack'] = array();
      }
      $_SESSION['messageToAdminStack'][$type][md5($message)] = $message;
    }

    function reset() {
      $this->errors = array();
      $this->size = 0;
    }

    function output() {
      $output = '';
      if ($this->size > 0) {
        foreach ($this->errors as $type => $message_stack) {
          $output .= '<div class="'.$type.'_message">';
          $output .= implode('<br/>', $message_stack);
          $output .= '</div>';   
          
          foreach ($message_stack as $message) {
            unset($_SESSION['messageToAdminStack'][$type][md5($message)]);
          }
        }
      }
      
      return $output;
    }
  }
