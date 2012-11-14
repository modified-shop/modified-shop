<?php
  /* --------------------------------------------------------------
   $Id$

   modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(popup_image.php,v 1.6 2002/05/20); www.oscommerce.com
   (c) 2003 nextcommerce (popup_image.php,v 1.7 2003/08/18); www.nextcommerce.org
   (c) 2006 XT-Commerce (popup_image.php 899 2005-04-29)

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  require('includes/application_top.php');

  reset($_GET);
  while (list($key, ) = each($_GET)) {
    switch ($key) {
      case 'banner':
        $banners_id = xtc_db_prepare_input($_GET['banner']);

        $banner_query = xtc_db_query("select banners_title, banners_image, banners_html_text from " . TABLE_BANNERS . " where banners_id = '" . (int)$banners_id . "'");
        $banner = xtc_db_fetch_array($banner_query);

        $page_title = $banner['banners_title'];

        if ($banner['banners_html_text']) {
          $image_source = $banner['banners_html_text'];
        } elseif ($banner['banners_image']) {
          $image_source = xtc_image(HTTP_CATALOG_SERVER . DIR_WS_CATALOG_IMAGES . $banner['banners_image'], $page_title);
        }
        break;
    }
  }
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_SESSION['language_charset']; ?>" />
    <title><?php echo $page_title; ?></title>
    <script type="text/javascript">
      <!--
      var i=0;
      function resize() {
        if (navigator.appName == 'Netscape') i = 40;
        window.resizeTo(document.images[0].width + 30, document.images[0].height + 60 - i);
      }
      //-->
    </script>
  </head>
  <body onload="resize();">
    <?php echo $image_source; ?>
  </body>
</html>