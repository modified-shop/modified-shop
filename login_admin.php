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

// Base/PHP_SELF/SSL-PROXY
require_once ('inc/set_php_self.inc.php');
$PHP_SELF = set_php_self();

if (isset($_GET['repair']) || isset($_POST['repair']) || isset($_GET['show_error']) || isset($_POST['show_error'])) {
  include('includes/login_admin.php');
} else {
  include('includes/login_shop.php');
}
