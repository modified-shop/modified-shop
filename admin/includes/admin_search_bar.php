<?php
  /* --------------------------------------------------------------
   $Id: admin_search_bar.php 4387 2013-02-01 12:20:50Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   Admin Search Bar (ASB)

   Released under the GNU General Public License
   --------------------------------------------------------------*/
  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

  $page_filename = basename($_SERVER['SCRIPT_FILENAME']);
  $search_cus = '';
  $search_email = '';
  $search_ord = '';
  $search_cat = '';
  if (strpos($page_filename, 'customers.php') !== false) {
    $search_cus = htmlentities(isset($_GET['search']) ? $_GET['search'] : ''); //DokuMan - 2010-09-08 - set undefined index
    $search_email = htmlentities(isset($_GET['search_email']) ? $_GET['search_email'] : ''); //DokuMan - 2010-09-08 - set undefined index
  }
  if (strpos($page_filename, 'orders.php') !== false) {
    $search_ord = htmlentities(isset($_GET['oID']) ? $_GET['oID'] : ''); //DokuMan - 2010-09-08 - set undefined index
  }
  if (strpos($page_filename, 'categories.php') !== false){
    $search_cat = htmlentities(isset($_GET['search']) ? $_GET['search'] : ''); //DokuMan - 2010-09-08 - set undefined index
  }
  
  if (!defined('NEW_ADMIN_STYLE')) {   
  ?>
  <link href="includes/searchbar_menu/searchbar_menu.css" rel="stylesheet" type="text/css" />
  <?php
  }
  ?>
  
<div class="searchbar">
  <ul id="topmenu_search">
    <li>
      <form action="<?php echo xtc_href_link('customers.php'); ?>" method="get">
        <?php echo ASB_QUICK_SEARCH_CUSTOMER; ?>
        <input name="search" type="text" value="<?php echo $search_cus;?>" size="15" />
        <input name="asb" type="hidden" value="asb" />
        <input name="<?php echo xtc_session_name(); ?>" type="hidden" value="<?php echo xtc_session_id(); ?>" />
      </form>
    </li>
    <li>
      <form action="<?php echo xtc_href_link('customers.php'); ?>" method="get">
        <?php echo ASB_QUICK_SEARCH_EMAIL; ?>
        <input name="search_email" type="text" value="<?php echo $search_email;?>" size="15" />
        <input name="<?php echo xtc_session_name(); ?>" type="hidden" value="<?php echo xtc_session_id(); ?>" />
      </form>
    </li>
    <li>
      <form action="<?php echo xtc_href_link('orders.php'); ?>" method="get">
        <?php echo ASB_QUICK_SEARCH_ORDER_ID; ?>
        <input name="oID" type="text" value="<?php echo $search_ord;?>" size="7" />
        <input type="hidden" name="action" value="search" />
        <input name="<?php echo xtc_session_name(); ?>" type="hidden" value="<?php echo xtc_session_id(); ?>" />
      </form>
    </li>
    <li>
      <form action="<?php echo xtc_href_link('categories.php'); ?>" method="get">
        <?php echo ASB_QUICK_SEARCH_ARTICLE; ?>
        <input name="search" type="text" value="<?php echo $search_cat;?>" size="15" />
        <input name="<?php echo xtc_session_name(); ?>" type="hidden" value="<?php echo xtc_session_id(); ?>" />
      </form>
    </li>
  </ul>
</div>