<?php
/* --------------------------------------------------------------
   $Id: message_stack.php 950 2005-05-14 16:45:21Z mz $   

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

    function __construct() {
      $this->errors = array();
      if (isset($_SESSION['messageToStack'])) {
        for ($i = 0, $n = sizeof($_SESSION['messageToStack']); $i < $n; $i++) {
          $this->add($_SESSION['messageToStack'][$i]['text'], $_SESSION['messageToStack'][$i]['type']);
        }
        unset($_SESSION['messageToStack']);
      }
    }

    function add($message, $type = 'error') {
      if ($type == 'error') {
        $this->errors['error'][] = $message;
      } elseif ($type == 'warning') {
        $this->errors['warning'][] = $message;
      } elseif ($type == 'success') {
        $this->errors['success'][] = $message;
      } else {
        $this->errors['error'][] = $message;
      }
      
      $this->size++;
    }

    function add_session($message, $type = 'error') {
      if (!isset($_SESSION['messageToStack'])) {
        $_SESSION['messageToStack'] = array();
      }
      $_SESSION['messageToStack'][] = array('text' => $message, 'type' => $type);
    }

    function reset() {
      $this->errors = array();
      $this->size = 0;
    }

    function output() {
      $output = '';
      if ($this->size > 0) {
        foreach ($this->errors as $key => $message) {
          $output .= '<div class="'.$key.'_message">';
          $output .= implode('<br/>', $message);
          $output .= '</div>';   
        }
      }
      return $output;
    }
  }
?>