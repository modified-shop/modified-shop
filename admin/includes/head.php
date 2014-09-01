<?php
  /* --------------------------------------------------------------
   $Id: head.php 4387 2013-02-01 12:20:50Z web28 $

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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html <?php echo HTML_PARAMS; ?>>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_SESSION['language_charset']; ?>">
  <title><?php echo TITLE; ?></title>
  <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">  
  <link rel="stylesheet" type="text/css" href="includes/searchbar_menu/searchbar_menu.css" />
  
  <?php 
  if (USE_ADMIN_TOP_MENU != 'false') {
  ?>
  <script language="javascript">
    <!--
      document.write('<link rel="stylesheet" type="text/css" href="includes/css/topmenu.css" />');
    //-->
  </script>
  <?php
  } else {
    echo '<link href="includes/liststyle_menu/liststyle_left.css" rel="stylesheet" type="text/css" />';
  }
  ?>

  <noscript>
    <link href="includes/liststyle_menu/liststyle_left.css" rel="stylesheet" type="text/css" />
  </noscript>
  