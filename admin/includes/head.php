<?php
  /* --------------------------------------------------------------
   $Id: head.php 5065 2013-07-15 12:22:56Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project (earlier name of osCommerce)
   (c) 2002-2003 osCommerce, www.oscommerce.com
   (c) 2003  nextcommerce, www.nextcommerce.org
   (c) 2006      xt:Commerce, www.xt-commerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------*/
  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );
  
  define('NEW_ADMIN_STYLE',true);
?>
<!DOCTYPE html>
<html <?php echo HTML_PARAMS; ?>>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_SESSION['language_charset']; ?>">
  <title><?php echo TITLE; ?></title>
  <meta http-equiv="pragma" content="no-cache">
  <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">  
  <link rel="stylesheet" type="text/css" href="includes/searchbar_menu/searchbar_menu.css" />
  <link rel="stylesheet" type="text/css" href="includes/css/tooltip.css">
  
  <?php 
  if (USE_ADMIN_TOP_MENU != 'false') {
    echo '<link rel="stylesheet" type="text/css" href="includes/css/topmenu.css" />';
  } else {
    echo '<link rel="stylesheet" type="text/css" href="includes/css/liststyle_left.css" />';
  }
  if (USE_ADMIN_FIXED_TOP != 'true') {
    echo '<link rel="stylesheet" type="text/css" href="includes/css/fixed_top_none.css" />';
  }
  ?>

  <!--[if lt IE 9]><script src="includes/javascript/html5.js"></script><![endif]-->
  
