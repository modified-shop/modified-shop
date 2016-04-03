<?php
/* --------------------------------------------------------------
   $Id: logger.php 950 2005-05-14 16:45:21Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(logger.php,v 1.2 2002/05/03); www.oscommerce.com 
   (c) 2003	 nextcommerce (logger.php,v 1.5 2003/08/14); www.nextcommerce.org

   Released under the GNU General Public License 
   --------------------------------------------------------------*/
  
  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

  class logger {
    var $timer_start, $timer_stop, $timer_total;

    // class constructor
    function __construct() {
      $this->timer_start();
    }

    function timer_start() {
      if (defined("PAGE_PARSE_START_TIME")) {
        $this->timer_start = PAGE_PARSE_START_TIME;
      } else {
        $this->timer_start = microtime();
      }
    }

    function timer_stop($display = 'false') {
      $this->timer_stop = microtime();

      $time_start = explode(' ', $this->timer_start);
      $time_end = explode(' ', $this->timer_stop);

      $this->timer_total = number_format(($time_end[1] + $time_end[0] - ($time_start[1] + $time_start[0])), 3);

      if ($this->timer_total >= STORE_PAGE_PARSE_TIME_THRESHOLD) {
        $this->write($_SERVER['REQUEST_URI'], $this->timer_total . 's');
      }

      if ($display == 'true') {
        return $this->timer_display();
      }
    }

    function timer_display() {
      return '<span class="smallText">Parse Time: ' . $this->timer_total . 's</span>';
    }

    function write($message, $processTime) {
      error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' [' . $processTime . '] ' . $message . "\n", 3, DIR_FS_LOG.'mod_parsetime_'. date('Y-m-d') .'.log');
    }
  }
?>