<?php
  /* --------------------------------------------------------------
   $Id: new_attributes.php 3212 2012-07-14 09:41:44Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(new_attributes); www.oscommerce.com
   (c) 2003	 nextcommerce (new_attributes.php,v 1.13 2003/08/21); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------
   Third Party contributions:
   New Attribute Manager v4b				Autor: Mike G | mp3man@internetwork.net | http://downloads.ephing.com
   copy attributes                          Autor: Hubi | http://www.netz-designer.de

   Released under the GNU General Public License
   --------------------------------------------------------------*/

require('includes/application_top.php');
require(DIR_WS_MODULES.'new_attributes_config.php');
require(DIR_FS_INC .'xtc_findTitle.inc.php');
require_once(DIR_FS_INC . 'xtc_format_filesize.inc.php');

$oldaction = isset($_GET['oldaction']) ? '&oldaction='.$_GET['oldaction'] : (isset($_POST['oldaction']) ? '&oldaction='.$_POST['oldaction']: '');
$oldpage = isset($_GET['page']) ? '&page='.$_GET['page'] : (isset($_POST['page']) ? '&page='.$_POST['page']: '') ;

$iframe = (isset($_GET['iframe']) ? $iframe = '&iframe=1' : '');

//nach Speichern zur Kontrolle neu laden
if (isset($_POST['products_options_id']) && $_POST['action'] == 'change') {
   include(DIR_WS_MODULES.'new_attributes_change.php');
   $options_id = isset($_POST['options_id']) ? '&options_id='.implode(',',$_POST['options_id']) : '';
   xtc_redirect(xtc_href_link(FILENAME_NEW_ATTRIBUTES, 'cpath='. $_POST['cpath'].'&current_product_id='. $_POST['current_product_id'].'&option_order_by='.$_POST['option_order_by'].'&products_options_id=' .$_POST['products_options_id'].$oldaction.$oldpage.$options_id.$iframe));
}

//nach Abbrechen zurück zur Kategorie
if (isset($_GET['cPath'])) {
   include(DIR_WS_MODULES.'new_attributes_change.php');
   xtc_redirect(xtc_href_link(FILENAME_CATEGORIES, 'cPath=' . $_GET['cPath'] . '&pID=' . $_GET['current_product_id'] . str_replace('old','',$oldaction). $oldpage));
}
//Aufruf über Icon aus Katgorie/Artikelübersicht
if (isset($_GET['action']) && !isset($_POST['action'])) {
  $_POST = $_GET;
}

require (DIR_WS_INCLUDES.'head.php');

?>
<link rel="stylesheet" type="text/css" href="includes/css/new_attributes.css">
<script type="text/javascript" src="includes/javascript/jquery.new_attributes.js"></script>
<script type="text/javascript" src="includes/general.js"></script>
</head>
<body>
  <!-- header //-->
  <?php 
	if (!isset($_GET['iframe'])) {
	  require(DIR_WS_INCLUDES . 'header.php'); 
	}
	?>
  <!-- header_eof //-->
  <!-- body //-->
  <table class="tableBody">
    <tr>
      <?php //left_navigation
      if (USE_ADMIN_TOP_MENU == 'false' && !isset($_GET['iframe'])) {
        echo '<td class="columnLeft2">'.PHP_EOL;
        echo '<!-- left_navigation //-->'.PHP_EOL;       
        require_once(DIR_WS_INCLUDES . 'column_left.php');
        echo '<!-- left_navigation eof //-->'.PHP_EOL; 
        echo '</td>'.PHP_EOL;      
      }
      ?>
      <!-- body_text //-->
      <td class="boxCenter">
        <table class="tableCenter collapse">
          <?php
          // BOF - Tomcraft - 2009-11-11 - NEW SORT SELECTION
          if (isset($_GET['option_order_by']) && $_GET['option_order_by'] && !isset($_POST['action'])) {
            $pageTitle = TITLE_EDIT.': ' . xtc_findTitle($_GET['current_product_id'], $languageFilter);
            include(DIR_WS_MODULES.'new_attributes_include.php');
          }
          if (!isset($_GET['option_order_by'])) {
            $_POST['action'] = isset($_POST['action']) ? $_POST['action'] : '';
            // EOF - Tomcraft - 2009-11-11 - NEW SORT SELECTION
            switch($_POST['action']) {
              case 'edit':
                if ($_POST['copy_product_id'] != 0) {
                  //new copy handling by web28
                  $attrib_query = xtc_db_query("SELECT *
                                                  FROM ".TABLE_PRODUCTS_ATTRIBUTES."
                                                 WHERE products_id = " . $_POST['copy_product_id']);
                  while ($attrib_res_array = xtc_db_fetch_array($attrib_query)) {
                    //set new data (overrides)
                    unset($attrib_res_array['products_attributes_id']);
                    $attrib_res_array['products_id'] = $_POST['current_product_id'];
                    //write data to DB
                    xtc_db_perform(TABLE_PRODUCTS_ATTRIBUTES, $attrib_res_array);
                  }
                }
                $pageTitle = TITLE_EDIT.': ' . xtc_findTitle($_POST['current_product_id'], $languageFilter);
                include(DIR_WS_MODULES.'new_attributes_include.php');
                break;
              case 'change':
                $pageTitle = TITLE_UPDATED;
                include(DIR_WS_MODULES.'new_attributes_change.php');
                include(DIR_WS_MODULES.'new_attributes_select.php');
                break;
              default:
                $pageTitle = TITLE_EDIT;
                include(DIR_WS_MODULES.'new_attributes_select.php');
                break;
            }
            // BOF - Tomcraft - 2009-11-11 - NEW SORT SELECTION
          }
          // EOF - Tomcraft - 2009-11-11 - NEW SORT SELECTION
        ?>
        </table>
      </td>
      <!-- body_text_eof //-->
    </tr> 
  </table> 
  <!-- body_eof //-->
  <!-- footer //-->
  <?php 
	if (!isset($_GET['iframe'])) {
	  require(DIR_WS_INCLUDES . 'footer.php');
  }		
  ?>
  <!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>