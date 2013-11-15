<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2009 FINDOLOGIC GmbH - Version: 4.1 (120)

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  require ('includes/application_top.php');
  require_once (DIR_FS_EXTERNAL.'findologic/findologic_config.inc.php');
  
  // load needed function
  require_once (DIR_FS_INC.'get_external_content.inc.php');
  require_once (DIR_FS_INC.'xtc_hide_session_id.inc.php');
  
  function is_alive() {
    $url = FL_SERVICE_URL.'alivetest.php?shopkey='.FL_SHOP_ID;
    $status = get_external_content($url, FL_ALIVE_TEST_TIMEOUT, false);   
    
    return strpos($status, 'alive');  
  }

  $do_findologic_search = is_alive();
  if ($_SESSION['language'] == 'german' && $do_findologic_search !== false) {
    $url = FL_SERVICE_URL.'index.php?'.
          'shopkey='.FL_SHOP_ID.
          '&shopurl='.urlencode(FL_SHOP_URL).
          '&userip='.xtc_get_ip_address().
          '&referer='.(isset($_SERVER['HTTP_REFERER']) ? urlencode($_SERVER['HTTP_REFERER']) : '').
          '&revision='.FL_REVISION.
          '&'.utf8_decode($_SERVER['QUERY_STRING']);

    $content = get_external_content($url, FL_REQUEST_TIMEOUT, false);

    $regex = "/<div[\s]+id=\"flResults\">[\z\s]+(.*?)<\/div>/si";
    preg_match($regex, $content, $result);
  }

  if ($do_findologic_search === false) {

    $params = '';
    $action = FILENAME_DEFAULT;
    if ((isset($_GET['search']) && xtc_not_null($_GET['search'])) 
        || (isset($_GET['keywords']) && xtc_not_null($_GET['keywords']))) 
    {
      $action = FILENAME_ADVANCED_SEARCH_RESULT;
      $params = 'f=true&';
      if (isset($_GET['search']) && xtc_not_null($_GET['search'])) {
        $params .= 'keywords='.$_GET['search'];
      } else {
        $params .= 'keywords='.$_GET['keywords'];
      }
    }
    xtc_redirect(xtc_href_link($action, $params, 'NONSSL'));

  } else {

    // create smarty element
    $smarty = new Smarty;

    // build breadcrumb
    $breadcrumb->add(NAVBAR_TITLE1_ADVANCED_SEARCH, xtc_href_link(FILENAME_ADVANCED_SEARCH));
    $breadcrumb->add(NAVBAR_TITLE2_ADVANCED_SEARCH, xtc_href_link(FILENAME_FINDOLOGIC, xtc_get_all_get_params()));

    if (strpos($result[1], '<flProductID>') !== false) {
      $product_id_array = preg_split("/<[^>]*[^\/]>/i" , $result[1], -1, PREG_SPLIT_NO_EMPTY); 
      $product_id_array = array_filter($product_id_array, 'xtc_not_null');

      $products_id = implode("', '", $product_id_array);
      require (DIR_FS_EXTERNAL.'findologic/findologic_listing.php');
  
      $module = '<div style="clear:both;"></div>'.$module;
    } else {
      $error = TEXT_PRODUCT_NOT_FOUND;
      include (DIR_WS_MODULES.FILENAME_ERROR_HANDLER);
    }

    $content = preg_replace($regex, $module, $content);

    // include boxes & header
    require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');
    require (DIR_WS_INCLUDES.'header.php');

    $smarty->assign('main_content', $content);
    $smarty->assign('language', $_SESSION['language']);
    $smarty->caching = 0;
    if (!defined('RM')) {
      $smarty->load_filter('output', 'note');
    }
    $smarty->display(CURRENT_TEMPLATE.'/index.html');
    include('includes/application_bottom.php');
  }
?>