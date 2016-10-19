<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   Released under the GNU General Public License
   --------------------------------------------------------------*/
   
@ini_set('display_errors', false);
error_reporting(0);

define('_MODIFIED_SHOP_LOGIN',1);

if ((isset($_GET['repair']) && !empty($_GET['repair'])) || (isset($_GET['show_error']) && $_GET['show_error']!='')) {
  include('includes/login_admin.php');
} else {
  include('includes/login_shop.php');
}
