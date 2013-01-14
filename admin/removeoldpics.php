<?php
/* --------------------------------------------------------------
   $Id: removeoldpics.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(manufacturers.php,v 1.14 2003/02/16); www.oscommerce.com
   (c) 2003 nextcommerce (manufacturers.php,v 1.4 2003/08/14); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  require('includes/application_top.php');

  if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    if (remove_old_pics($_GET['path'])) {
      $messageStack->add_session(sprintf(LINK_MESSAGE,$_GET['path']), 'success');
    } else {
      $messageStack->add_session(sprintf(LINK_MESSAGE_NO_DELETE,$_GET['path']), 'success');
    }
    xtc_redirect(xtc_href_link(FILENAME_REMOVEOLDPICS));    
  }

  function remove_old_pics ($path='') {
    // Images product table
    $pics_array = array();
    $pics_query = xtc_db_query("SELECT products_image FROM ".TABLE_PRODUCTS."");
    while ($pics = xtc_db_fetch_array($pics_query)) {
      if ($pics['products_image'] != '' || $pics['products_image'] != NULL) {
        $pics_array[] = $pics['products_image'];
      }
    }
    // Images product_images table
    $pics_query = xtc_db_query("SELECT image_name FROM ".TABLE_PRODUCTS_IMAGES."");
    while ($pics = xtc_db_fetch_array($pics_query)) {
      if ($pics['image_name'] != '' || $pics['image_name'] != NULL) {
        $pics_array[] = $pics['image_name'];
      }
    }
    switch ($path) {
      case 'original' :
        $path = DIR_FS_CATALOG_ORIGINAL_IMAGES;
        break;
      case 'info' :
        $path = DIR_FS_CATALOG_INFO_IMAGES;
        break;
      case 'thumbnail' :
        $path = DIR_FS_CATALOG_THUMBNAIL_IMAGES;
        break;
      case 'popup' :
        $path = DIR_FS_CATALOG_POPUP_IMAGES;
        break;
    }

    $flag_delete = false;
    if ($path != "") {
      $handle = opendir($path);
      while ($datei = readdir($handle)) {
        if (!in_array($datei,$pics_array) && ($datei!='.') && ($datei != '..') && ($datei != 'index.html') && ($datei != 'noimage.gif')) {
          if(!is_dir($path.$datei) ) { // do not remove (sub)directories
            unlink($path.$datei);
            $flag_delete = true;
          }
        }
      }
      closedir($handle);
    }
    return $flag_delete;
  }

  require (DIR_WS_INCLUDES.'head.php');
?>
  </head>
  <body>
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->
    <!-- body //-->
    <table border="0" width="100%" cellspacing="2" cellpadding="2">
      <tr>
        <td class="columnLeft2" width="<?php echo BOX_WIDTH; ?>" valign="top">
          <table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
            <!-- left_navigation //-->
            <?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
            <!-- left_navigation_eof //-->
          </table>
        </td>
        <!-- body_text //-->
        <td class="boxCenter" width="100%" valign="top">
          <table border="0" width="100%" cellspacing="0" cellpadding="2">
            <tr>
              <td width="100%">
                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                  <tr>
                    <td width="80" rowspan="2"><?php echo xtc_image(DIR_WS_ICONS.'heading_content.gif'); ?></td>
                    <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
                    <td class="pageHeading" align="right"><?php echo xtc_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
                  </tr>
                </table>
              </td>
            </tr>
            <tr>
              <td>
                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                  <tr>
                    <td class="main"><?php echo LINK_INFO_TEXT; ?>
                    </td>
                  </tr>
                  <tr>
                    <td class="main">
                      <a class="button" href="./removeoldpics.php?action=delete&path=original"><?php echo LINK_ORIGINAL; ?></a>&nbsp;|&nbsp;
                      <a class="button" href="./removeoldpics.php?action=delete&path=info"><?php echo LINK_INFO; ?></a>&nbsp;|&nbsp;
                      <a class="button" href="./removeoldpics.php?action=delete&path=thumbnail"><?php echo LINK_THUMBNAIL; ?></a>&nbsp;|&nbsp;
                      <a class="button" href="./removeoldpics.php?action=delete&path=popup"><?php echo LINK_POPUP; ?></a>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </td>
        <!-- body_text_eof //-->
      </tr>
    </table>
    <!-- body_eof //-->
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
    <br />
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>