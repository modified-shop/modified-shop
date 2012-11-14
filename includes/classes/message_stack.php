<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(message_stack.php,v 1.1 2003/05/19); www.oscommerce.com
   (c) 2003 nextcommerce (message_stack.php,v 1.9 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce (message_stack.php 799 2005-02-23)

   Released under the GNU General Public License
   Example usage:
   $messageStack = new messageStack();
   $messageStack->add('general', 'Error: Error 1', 'error');
   $messageStack->add('general', 'Error: Error 2', 'warning');
   if ($messageStack->size('general') > 0) echo $messageStack->output('general');
   ---------------------------------------------------------------------------------------*/

  class messageStack extends tableBox {
    // class constructor
    function messageStack() {

      $this->messages = array();

      if (isset($_SESSION['messageToStack'])) {
        //BOF - DokuMan - 2011-12-19 - precount for performance
        //for ($i=0, $n=sizeof($_SESSION['messageToStack']); $i<$n; $i++) {
        $n=sizeof($_SESSION['messageToStack']);
        for ($i=0; $i<$n; $i++) {
        //EOF - DokuMan - 2011-12-19 - precount for performance
          if (isset($_SESSION['messageToStack'][$i]['class'])) //DokuMan - 2010-08-31 - set undefined index
          $this->add($_SESSION['messageToStack'][$i]['class'], $_SESSION['messageToStack'][$i]['text'], $_SESSION['messageToStack'][$i]['type']);
        }
        unset($_SESSION['messageToStack']);
      }
    }

    // class methods
    function add($class, $message, $type = 'error') {
      if ($type == 'error') {
        $this->messages[] = array('params' => 'class="messageStackError"', 'class' => $class, 'text' => xtc_image(DIR_WS_ICONS . 'error.gif', ICON_ERROR) . '&nbsp;' . $message);
      } elseif ($type == 'warning') {
        $this->messages[] = array('params' => 'class="messageStackWarning"', 'class' => $class, 'text' => xtc_image(DIR_WS_ICONS . 'warning.gif', ICON_WARNING) . '&nbsp;' . $message);
      } elseif ($type == 'success') {
        $this->messages[] = array('params' => 'class="messageStackSuccess"', 'class' => $class, 'text' => xtc_image(DIR_WS_ICONS . 'success.gif', ICON_SUCCESS) . '&nbsp;' . $message);
      } else {
        $this->messages[] = array('params' => 'class="messageStackError"', 'class' => $class, 'text' => $message);
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

    function output($class) {
      $this->table_data_parameters = 'class="messageBox"';

      $output = array();
      //BOF - DokuMan - 2011-12-19 - precount for performance
      //for ($i=0, $n=sizeof($this->messages); $i<$n; $i++) {
      $n=sizeof($this->messages);
      for ($i=0; $i<$n; $i++) {
      //EOF - DokuMan - 2011-12-19 - precount for performance
        if ($this->messages[$i]['class'] == $class) {
          $output[] = $this->messages[$i];
        }
      }

      return $this->tableBox($output);
    }

    function size($class) {
      $count = 0;

      //BOF - DokuMan - 2011-12-19 - precount for performance
      //for ($i=0, $n=sizeof($this->messages); $i<$n; $i++) {
      $n=sizeof($this->messages);
      for ($i=0; $i<$n; $i++) {
      //EOF - DokuMan - 2011-12-19 - precount for performance
        if ($this->messages[$i]['class'] == $class) {
          $count++;
        }
      }

      return $count;
    }
  }
?>