<?php
  /* --------------------------------------------------------------
   $Id$   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(logger.php,v 1.2 2002/05/03); www.oscommerce.com 
   (c) 2003	 nextcommerce (logger.php,v 1.5 2003/08/14); www.nextcommerce.org
   (c) 2003-2006 XT-Commerce ; www.xt-commerce.com

   Released under the GNU General Public License 
   --------------------------------------------------------------*/
  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );
  class logger {
    var $timer_start, $timer_stop, $timer_total;

    // class constructor
    function logger() {
      $this->timer_start();
    }

    function timer_start() {
      if (defined("PAGE_PARSE_START_TIME")) {
        $this->timer_start = PAGE_PARSE_START_TIME;
      } else {
        $this->timer_start = microtime(true);
      }
    }

    function timer_stop($display = 'false') {
      $this->timer_stop = microtime(true);

      $this->timer_total = number_format(($this->timer_stop - $this->timer_start), 3);

      $this->write($_SERVER['REQUEST_URI'], $this->timer_total . 's');

      if ($display == 'true') {
        return $this->timer_display();
      }
    }

    function timer_display() {
      return '<span class="smallText">Parse Time: ' . $this->timer_total . 's</span>';
    }

    function write($message, $type) {
      error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' [' . $type . '] ' . $message . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
    }
  }
?>