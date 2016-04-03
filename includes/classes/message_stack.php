<?php
/* -----------------------------------------------------------------------------------------
   $Id: message_stack.php 799 2005-02-23 18:08:06Z novalis $   

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

    function __construct() {
      $this->messages = array();
      if (isset($_SESSION['messageToStack'])) {
        for ($i=0, $n=sizeof($_SESSION['messageToStack']); $i<$n; $i++) {
          $this->add($_SESSION['messageToStack'][$i]['class'], $_SESSION['messageToStack'][$i]['text'], $_SESSION['messageToStack'][$i]['type']);
        }
        unset($_SESSION['messageToStack']);
      }
    }

    function add($class, $message, $type = 'error') {
      if ($type == 'error') {
        $this->messages[$class]['error'][] = $message;
      } elseif ($type == 'warning') {
        $this->messages[$class]['warning'][] = $message;
      } elseif ($type == 'success') {
        $this->messages[$class]['success'][] = $message;
      } else {
        $this->messages[$class]['warning'][] = $message;
      }
    }

    function add_session($class, $message, $type = 'error') {
      if (!isset($_SESSION['messageToStack'])) {
        $_SESSION['messageToStack'] = array();
      }
      $_SESSION['messageToStack'][] = array('class' => $class, 'text' => $message, 'type' => $type);
    }

    function reset() {
      $this->messages = array();
    }

    function size($class) {
      $count = 0;
      if (isset($this->messages[$class])) {
        foreach ($this->messages[$class] as $key => $messages) {
           $count += count($messages);
        }
      }
      return $count;
    }

    function output($class) {
      $output = '';
      if ($this->size($class) > 0) {
        foreach ($this->messages[$class] as $key => $messages) {
          foreach ($messages as $message) {
            $output .= '<p>'.$message.'</p>';
          }   
        }
      }
      return $output;
    }
  }
?>