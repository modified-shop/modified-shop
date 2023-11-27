<?php
/* --------------------------------------------------------------
   $Id$

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

  function remove_old_pics ($path = '') {
    // Images product table
    $pics_array = array();
    $pics_query = xtc_db_query("SELECT products_image 
                                  FROM ".TABLE_PRODUCTS."
                                 WHERE products_image != ''
                                   AND products_image IS NOT NULL");
    while ($pics = xtc_db_fetch_array($pics_query)) {
      if (!in_array($pics['products_image'], $pics_array)) {
        $pics_array[] = $pics['products_image'];
        
        if (IMAGE_TYPE_EXTENSION != 'default') {
          $pics_array[] = substr($pics['products_image'], 0, strrpos($pics['products_image'], '.')).'.'.IMAGE_TYPE_EXTENSION;
        }
      }
    }
    
    // Images product_images table
    $pics_query = xtc_db_query("SELECT image_name 
                                  FROM ".TABLE_PRODUCTS_IMAGES."
                                 WHERE image_name != ''
                                   AND image_name IS NOT NULL");
    while ($pics = xtc_db_fetch_array($pics_query)) {
      if (!in_array($pics['image_name'], $pics_array)) {
        $pics_array[] = $pics['image_name'];

        if (IMAGE_TYPE_EXTENSION != 'default') {
          $pics_array[] = substr($pics['image_name'], 0, strrpos($pics['image_name'], '.')).'.'.IMAGE_TYPE_EXTENSION;
        }
      }
    }
    
    switch ($path) {
      case 'original' :
        $path = DIR_FS_CATALOG_ORIGINAL_IMAGES;
        break;
      case 'popup' :
        $path = DIR_FS_CATALOG_POPUP_IMAGES;
        break;
      case 'info' :
        $path = DIR_FS_CATALOG_INFO_IMAGES;
        break;
      case 'midi' :
        $path = DIR_FS_CATALOG_MIDI_IMAGES;
        break;
      case 'thumbnail' :
        $path = DIR_FS_CATALOG_THUMBNAIL_IMAGES;
        break;
      case 'mini' :
        $path = DIR_FS_CATALOG_MINI_IMAGES;
        break;
    }

    $flag_delete = false;
    if ($path != '') {
      $handle = opendir($path);
      while ($image = readdir($handle)) {
        if (!in_array($image, $pics_array)
            && is_file($path.$image)
            && $image != 'index.html'
            && $image != 'noimage.gif'
            )
        {
          unlink($path.$image);
          $flag_delete = true;
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
  <table class="tableBody">
    <tr>
      <?php //left_navigation
      if (USE_ADMIN_TOP_MENU == 'false') {
        echo '<td class="columnLeft2">'.PHP_EOL;
        echo '<!-- left_navigation //-->'.PHP_EOL;       
        require_once(DIR_WS_INCLUDES . 'column_left.php');
        echo '<!-- left_navigation eof //-->'.PHP_EOL; 
        echo '</td>'.PHP_EOL;      
      }
      ?>
      <!-- body_text //-->
      <td class="boxCenter">
        <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_content.png'); ?></div>
        <div class="pageHeading"><?php echo HEADING_TITLE; ?><br /></div>
        <div class="main pdg2 flt-l">Tools</div>
        <div class="clear main mrg5"><?php echo LINK_INFO_TEXT; ?></div>
        <div class="main mrg5">
        <?php
          echo '<a class="button" href="'.xtc_href_link('removeoldpics.php', 'action=delete&path=original').'">'.LINK_ORIGINAL.'</a>';
          echo '<a class="button" href="'.xtc_href_link('removeoldpics.php', 'action=delete&path=popup').'">'.LINK_POPUP.'</a>';
          echo '<a class="button" href="'.xtc_href_link('removeoldpics.php', 'action=delete&path=info').'">'.LINK_INFO.'</a>';
          echo '<a class="button" href="'.xtc_href_link('removeoldpics.php', 'action=delete&path=midi').'">'.LINK_MIDI.'</a>';
          echo '<a class="button" href="'.xtc_href_link('removeoldpics.php', 'action=delete&path=thumbnail').'">'.LINK_THUMBNAIL.'</a>';
          echo '<a class="button" href="'.xtc_href_link('removeoldpics.php', 'action=delete&path=mini').'">'.LINK_MINI.'</a>';
        ?>
        </div>
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