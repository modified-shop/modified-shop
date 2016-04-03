<?php
/* --------------------------------------------------------------
   $Id: categories_view.php 5390 2013-08-15 12:14:27Z Tomcraft $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(categories.php,v 1.140 2003/03/24); www.oscommerce.com
   (c) 2003 nextcommerce (categories.php,v 1.37 2003/08/18); www.nextcommerce.org
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   --------------------------------------------------------------
   Third Party contribution:
   Enable_Disable_Categories 1.3 Autor: Mikel Williams | mikel@ladykatcostumes.com
   New Attribute Manager v4b Autor: Mike G | mp3man@internetwork.net | http://downloads.ephing.com
   Category Descriptions (Version: 1.5 MS2) Original Author: Brian Lowe <blowe@wpcusrgrp.org> | Editor: Lord Illicious <shaolin-venoms@illicious.net>
   Customers Status v3.x (c) 2002-2003 Copyright Elari elari@free.fr | www.unlockgsm.com/dload-osc/ | CVS : http://cvs.sourceforge.net/cgi-bin/viewcvs.cgi/elari/?sortby=date#dirlist

   Released under the GNU General Public License
   --------------------------------------------------------------*/
  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  require_once(DIR_FS_LANGUAGES . $_SESSION['language'] . '/admin/categories_specials.php');

  if (is_file('includes/modules/products_attributes_iframe.php')) {
    include_once("includes/modules/products_attributes_iframe.php");
  } 

  define('CAT_VIEW_DROPDOWN', true); //remove dropdown field due to performance issues on many categories
  
  if (!defined('MAX_DISPLAY_LIST_PRODUCTS')) {
    define('MAX_DISPLAY_LIST_PRODUCTS', 50);     // display products per page
  }

  //BOF - web28 -  2012-08-25 - change imagesize by css size
  define('BOX_CAT_IMAGE_SIZE', '150px');
  $box_cat_image_size = 'style="max-width: '. BOX_CAT_IMAGE_SIZE .'; max-height: '.BOX_CAT_IMAGE_SIZE.';"';
  
  $icon_padding = 'style="padding-right:8px;"';
  
  if( defined('USE_ADMIN_THUMBS_IN_LIST_STYLE')) {
    $admin_thumbs_size = 'style="'.USE_ADMIN_THUMBS_IN_LIST_STYLE.'"';
  } else {
    $admin_thumbs_size = 'style="max-width: 40px; max-height: 40px;"';
  }
  //EOF - web28 - 2012-08-25 - change imagesize by css size

  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  $search = (isset($_GET['search']) ? $_GET['search'] : '');
  $search_id = (isset($_GET['search_id']) ? $_GET['search_id'] : '');

  $search_inactive = (isset($_GET['search_inactive']) && $_GET['search_inactive'] == 1 ? true : false);
  $display_categories = !$search_inactive;

  // get sorting option and switch accordingly
  $sorting = (isset($_GET['sorting']) ? $_GET['sorting'] : '');
  if (xtc_not_null($sorting)) {
    switch ($sorting) {
      case 'model':
        $catsort    = 'c.sort_order ASC';
        $prodsort   = 'p.products_model ASC';
        break;
      case 'model-desc':
        $catsort    = 'c.sort_order ASC';
        $prodsort   = 'p.products_model DESC';
        break;
      case 'sort':
        $catsort    = 'c.sort_order ASC';
        $prodsort   = (isset($_GET['cPath']) && $_GET['cPath'] != '0') ? 'p.products_sort ASC' : 'p.products_startpage_sort ASC';
        break;
      case 'sort-desc':
        $catsort    = 'c.sort_order DESC';
        $prodsort   = (isset($_GET['cPath']) && $_GET['cPath'] != '0') ? 'p.products_sort DESC' : 'p.products_startpage_sort DESC';
        break;
      case 'name':
        $catsort    = 'cd.categories_name ASC';
        $prodsort   = 'pd.products_name ASC';
        break;
      case 'name-desc':
        $catsort    = 'cd.categories_name DESC';
        $prodsort   = 'pd.products_name DESC';
        break;
      case 'status':
        $catsort    = 'c.categories_status ASC';
        $prodsort   = 'p.products_status ASC';
        break;
      case 'status-desc':
        $catsort    = 'c.categories_status DESC';
        $prodsort   = 'p.products_status DESC';
        break;
      case 'price':
        $catsort    = 'c.sort_order ASC'; //default
        $prodsort   = 'p.products_price ASC';
        break;
      case 'price-desc':
        $catsort    = 'c.sort_order ASC'; //default
        $prodsort   = 'p.products_price DESC';
        break;
      case 'stock':
        $catsort    = 'c.sort_order ASC'; //default
        $prodsort   = 'p.products_quantity ASC';
        break;
      case 'stock-desc':
        $catsort    = 'c.sort_order ASC'; //default
        $prodsort   = 'p.products_quantity DESC';
        break;
      case 'discount':
        $catsort    = 'c.sort_order ASC'; //default
        $prodsort   = 'p.products_discount_allowed ASC';
        break;
      case 'discount-desc':
        $catsort    = 'c.sort_order ASC'; //default
        $prodsort   = 'p.products_discount_allowed DESC';
        break;
      default:
        $catsort    = 'cd.categories_name ASC';
        $prodsort   = 'pd.products_name ASC';
        break;
    }
  } else {
        $catsort    = 'c.sort_order, cd.categories_name ASC';
        $prodsort   = (isset($_GET['cPath']) && $_GET['cPath'] != '0') ? 'p.products_sort, pd.products_name ASC' : 'p.products_startpage_sort, pd.products_name ASC';
  }

  // BOF DokuMan - 2011-03-05 - show category name in heading title
  $category_query_name = xtc_db_query("SELECT categories_name
                                         FROM " . TABLE_CATEGORIES_DESCRIPTION . "
                                        WHERE categories_id = '" . $current_category_id . "'
                                          AND language_id = " . (int)$_SESSION['languages_id']);
  $category_name = xtc_db_fetch_array($category_query_name);
  // EOF DokuMan - 2011-03-05 - show category name in heading title
  ?>
  <!-- categories_view HTML part begin -->
      <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_categories.png'); ?></div>
      <div class="flt-l">
        <div class="pageHeading pdg2"><?php echo ($category_name['categories_name'] ? $category_name['categories_name'] : TEXT_TOP); ?></div>
        <div class="main pdg2"><?php echo HEADING_TITLE .sprintf(HEADING_TITLE_CAT_BREADCRUMB,$breadcrumb_html); ?></div>

      </div>
      <?php
      if (CAT_VIEW_DROPDOWN) {
      ?>
        <div class="smallText pdg2 flt-r">
         <?php
            echo xtc_draw_form('goto', FILENAME_CATEGORIES, '', 'get');
            echo '<span style="display:inline-block;line-height:30px;vertical-align:top;">' . HEADING_TITLE_GOTO . '</span> ' . xtc_draw_pull_down_menu('cPath', xtc_get_category_tree(), $current_category_id, 'onchange="this.form.submit();"');
          ?>
          </form>
        </div>
      <?php
      }
      ?> 
      <div class="smallText pdg2 flt-r" style="margin-left:80px;">
        <?php
        echo xtc_draw_form('forminactive', FILENAME_CATEGORIES, '', 'get');
        if (isset($_GET['cPath'])) {
          echo '<input type="hidden" id="cPath" name="cPath" value="' . $cPath . '">';
        }
        echo '<span style="display:inline-block;line-height:30px;vertical-align:top;">'. HEADING_TITLE_ONLY_INACTIVE_PRODUCTS .'</span>';
        echo '<div style="display:inline;margin:0px 10px 0px 5px;">' . draw_on_off_selection('search_inactive', 'checkbox', $search_inactive, 'style="vertical-align:middle;" onclick="this.form.submit();"') . '</div>';
        ?>
        </form>
      </div>

      <?php
        //multi-actions form STARTS
        if ((isset($_POST['multi_categories']) && xtc_not_null($_POST['multi_categories'])) || (isset($_POST['multi_products']) && xtc_not_null($_POST['multi_products']))) {
          $action_multi = xtc_get_all_get_params(array('cPath', 'action')) . 'action=multi_action_confirm' . (isset($_GET['cPath']) ? '&cPath=' . $cPath : '');
        } else {
          $action_multi = xtc_get_all_get_params(array('cPath', 'action')) . 'action=multi_action'  . (isset($_GET['cPath']) ? '&cPath=' . $cPath : '');
        }
        echo xtc_draw_form('multi_action_form', FILENAME_CATEGORIES, $action_multi, 'post', 'onsubmit="javascript:return CheckMultiForm()"');
        //add current category id in $_POST
        if (isset($_GET['cPath'])) {
          echo '<input type="hidden" id="cPath" name="cPath" value="' . $cPath . '">';
        }
      ?>
      <table class="clear tableCenter collapse">
        <tr>
          <!-- categories & products column STARTS -->
          <td class="boxCenterLeft">
            <!-- categories and products table -->
            <table class="tableBoxCenter collapse">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent txta-c" style="width:4%">
                  <?php 
                    echo TABLE_HEADING_EDIT; 
                    echo xtc_draw_checkbox_field('select_all', '1', false, '', 'onclick="javascript:CheckAll(this.checked);"');   
                  ?>
                </td>
                <td class="dataTableHeadingContent txta-c" style="width:10%">
                  <?php echo TABLE_HEADING_PRODUCTS_MODEL.xtc_sorting(FILENAME_CATEGORIES,'model'); ?>
                </td>
                <td class="dataTableHeadingContent txta-c" style="width:10%;">
                  <?php echo TABLE_HEADING_SORT.xtc_sorting(FILENAME_CATEGORIES,'sort'); ?>
                </td>
                <?php
                if( USE_ADMIN_THUMBS_IN_LIST=='true' ) {
                  ?>
                  <td class="dataTableHeadingContent txta-c" style="width:10%">
                    <?php echo TABLE_HEADING_IMAGE ?>
                  </td>
                  <?php
                }
                ?>
                <td class="dataTableHeadingContent txta-c" style="width:30%">
                  <?php echo TABLE_HEADING_CATEGORIES_PRODUCTS.xtc_sorting(FILENAME_CATEGORIES,'name'); ?>
                </td>
                <?php
                // check Produkt and attributes stock
                if (STOCK_CHECK == 'true') {
                  echo '<td class="dataTableHeadingContent txta-c" style="width:20%">' . TABLE_HEADING_STOCK . xtc_sorting(FILENAME_CATEGORIES,'stock') . '</td>';
                }
                ?>
                <td class="dataTableHeadingContent txta-c" style="width:7%">
                  <?php echo TABLE_HEADING_STATUS.xtc_sorting(FILENAME_CATEGORIES,'status'); ?>
               </td>
               <td class="dataTableHeadingContent txta-c" style="width:7%">
                  <?php echo TABLE_HEADING_STARTPAGE.xtc_sorting(FILENAME_CATEGORIES,'startpage'); ?>
               </td>
               <td class="dataTableHeadingContent txta-c" style="width:10%;white-space:nowrap;">
                  <?php echo TABLE_HEADING_PRICE.xtc_sorting(FILENAME_CATEGORIES,'price'); ?>
               </td>
               <td class="dataTableHeadingContent txta-c" style="width:12%;white-space:nowrap;">
                  <?php echo '%&nbsp;max' . xtc_sorting(FILENAME_CATEGORIES,'discount'); ?>
               </td>
               <td class="dataTableHeadingContent txta-c" style="width:10%">
                  <?php echo TABLE_HEADING_ACTION; ?>
               </td>
             </tr>
             <?php

            if($display_categories){
               // ----------------------------------------------------------------------------------------------------- //
               // WHILE loop to display categories STARTS
               // ----------------------------------------------------------------------------------------------------- //
               $categories_count = 0;
               $rows = 0;
               if (xtc_not_null($search) || xtc_not_null($search_id)) { 
                 $where_search = " AND cd.categories_name like '%" . xtc_db_input($search) . "%' ";
                 if (xtc_not_null($search_id)) {
                   $where_search = " AND c.categories_id = '" . (int)$search_id . "' ";
                 }
                 $search_category = $current_category_id != '' ? "AND c.parent_id = '" . (int)$current_category_id ."'" : '';
                 $categories_query = xtc_db_query("SELECT c.categories_id,
                                                          cd.categories_name,
                                                          c.categories_image,
                                                          c.parent_id,
                                                          c.sort_order,
                                                          c.date_added,
                                                          c.last_modified,
                                                          c.categories_status
                                                     FROM " . TABLE_CATEGORIES . " AS c,
                                                          " . TABLE_CATEGORIES_DESCRIPTION . " AS cd
                                                    WHERE c.categories_id = cd.categories_id
                                                          ".$search_category."
                                                          ".$where_search."
                                                      AND cd.language_id = '" . (int)$_SESSION['languages_id'] . "'                                                      
                                                 ORDER BY " . $catsort);
               } else {
                 $categories_query = xtc_db_query("SELECT c.categories_id,
                                                          cd.categories_name,
                                                          c.categories_image,
                                                          c.parent_id,
                                                          c.sort_order,
                                                          c.date_added,
                                                          c.last_modified,
                                                          c.categories_status
                                                     FROM " . TABLE_CATEGORIES . " AS c,
                                                          " . TABLE_CATEGORIES_DESCRIPTION . " AS cd
                                                    WHERE c.parent_id = '" . (int)$current_category_id . "'
                                                      AND c.categories_id = cd.categories_id
                                                      AND cd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                                                 ORDER BY " . $catsort);
               }

              //BOF - web28- 2010-11-24 FIX undefined $cPath_back
              $cPath_back = '';
              if (!isset($cPath_array)) {
                $cPath_array = array();
              }
              if ($cPath_array) {
                for($i = 0, $n = sizeof($cPath_array) - 1; $i < $n; $i++) {
                  if ($cPath_back == '') {
                    $cPath_back .= $cPath_array[$i];
                  } else {
                   $cPath_back .= '_' . $cPath_array[$i];
                  }
                }
              }
              if ($cPath_back != '') {
                $cPath_back = 'cPath=' . $cPath_back;
              }
              //EOF - web28- 2010-11-24 FIX undefined $cPath_back

              // BOC - web28 - 2012-03-11 - added "go to parent category" icon
              if (!xtc_not_null($search) && count($cPath_array) > 0 && $_GET['cPath'] != '0') {
                ?>
                 <tr class="dataTableRow" onmouseover="this.className='dataTableRowOver';this.style.cursor='pointer'" onmouseout="this.className='dataTableRow'">
                   <td class="categories_view_data">--</td>
                   <td class="categories_view_data">--</td>
                   <td class="categories_view_data">--</td>
                   <?php
                   if ( USE_ADMIN_THUMBS_IN_LIST=='true' ) {
                   ?>
                     <td class="categories_view_data txta-c">--</td>
                   <?php
                   }
                   ?>
                   <td class="categories_view_data txta-l" style="padding-left: 5px;">
                     <?php
                     echo '<a href="' . xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('cPath', 'action', 'pID', 'cID')) .$cPath_back). '">'.xtc_image(DIR_WS_ICONS . 'folder_parent.gif', ICON_FOLDER) .' ..</a>';
                     ?>
                   </td>
                   <?php                 
                   if (STOCK_CHECK == 'true') {
                   ?>
                     <td class="categories_view_data">--</td>
                   <?php
                   }
                   ?>
                   <td class="categories_view_data">--</td>
                   <td class="categories_view_data">--</td>
                   <td class="categories_view_data">--</td>
                   <td class="categories_view_data">--</td>
                   <td class="categories_view_data">--</td>
                 </tr>
                 <?php
              }
              // EOC - web28 - 2012-03-11 - added "go to parent category" icon

               while ($categories = xtc_db_fetch_array($categories_query)) {

                 $categories_count++;
                 $rows++;
                 if (xtc_not_null($search)) {
                   $cPath = $categories['parent_id'];
                 }
                 if (((!isset($_GET['cID']) || $_GET['cID'] == '') && !isset($_GET['pID']) || (isset($_GET['cID']) && ($_GET['cID'] == $categories['categories_id']))) && !isset($cInfo) && (substr($action, 0, 4) != 'new_') ) {
                   $cInfo = new objectInfo($categories);
                 }
                 if (isset($cInfo) && is_object($cInfo) && ($categories['categories_id'] == $cInfo->categories_id) ) {
                     echo '<tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'">' . "\n";
                 } else {
                     echo '<tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'">' . "\n";
                 }
                 $checked = isset($_POST['multi_categories']) && is_array($_POST['multi_categories']) && in_array($categories['categories_id'], $_POST['multi_categories']) ? true : false; 
                 ?>
                   <td class="categories_view_data"><?php echo xtc_draw_checkbox_field('multi_categories[]', $categories['categories_id'], $checked); ?></td>
                   <td class="categories_view_data">--</td>
                   <td class="categories_view_data"><?php echo $categories['sort_order']; ?></td>
                   <?php
                   if ( USE_ADMIN_THUMBS_IN_LIST=='true' ) {
                     ?>
                     <td class="categories_view_data">
                       <?php
                       echo xtc_info_image_c($categories['categories_image'], $categories['categories_image'], '','',$admin_thumbs_size);
                       ?>
                     </td>
                     <?php
                   }
                   ?>
                   <td class="categories_view_data txta-l" style="padding-left: 5px;">
                     <?php
                     echo '<a href="' . xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('cPath', 'action', 'pID', 'cID')) . xtc_get_path($categories['categories_id'])) . '">' . xtc_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER, '', '', $icon_padding) . '</a>';
                     echo '<a href="' . xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('cPath', 'action', 'pID', 'cID')) . 'cPath=' . $cPath . '&cID=' . $categories['categories_id']. '&action=edit_category') . '">' . xtc_image(DIR_WS_ICONS . 'icon_edit.gif', ICON_EDIT, '', '', $icon_padding) . '</a>';
                     echo '<b><a href="'.xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('cPath', 'action', 'pID', 'cID')) . 'cPath=' . $cPath . '&cID=' . $categories['categories_id']) .'">' . $categories['categories_name'] . '</a></b>';
                     ?>
                   </td>
                   <?php
                   // check product and attributes stock
                   if (STOCK_CHECK == 'true') {
                     echo '<td class="categories_view_data">--</td>';
                   }
                   ?>
                   <td class="categories_view_data">
                     <?php
                     //show status icons (green & red circle) with links
                     if ($categories['categories_status'] == '1') {
                       echo xtc_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '<a href="' . xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('cPath', 'action', 'pID', 'cID')) . 'action=setcflag&flag=0&cID=' . $categories['categories_id'] . '&cPath=' . $cPath) . '">&nbsp;&nbsp;' . xtc_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>';
                     } else {
                       echo '<a href="' . xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('cPath', 'action', 'pID', 'cID')) . 'action=setcflag&flag=1&cID=' . $categories['categories_id'] . '&cPath=' . $cPath) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . xtc_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
                     }
                     ?>
                   </td>
                   <td class="categories_view_data">--</td>
                   <td class="categories_view_data">--</td>
                   <td class="categories_view_data">--</td>
                   <td class="categories_view_data">
                     <?php
                     //if active category, show arrow, else show symbol with link (action col)
                     if (isset($cInfo) && (is_object($cInfo)) && ($categories['categories_id'] == $cInfo->categories_id) ) {
                       echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT);
                     } else {
                       echo '<a href="' . xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('cPath', 'action', 'pID', 'cID')) . 'cPath=' . $cPath . '&cID=' . $categories['categories_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>';
                     }
                     ?>
                   </td>
                 </tr>
               <?php
               // ----------------------------------------------------------------------------------------------------- //
               } // WHILE loop to display categories ENDS
               // ----------------------------------------------------------------------------------------------------- //
             }

             //get products data
             $products_count = 0;
             if (xtc_not_null($search) || xtc_not_null($search_id)) {
               include(DIR_FS_INC . 'xtc_parse_search_string.inc.php');
               define(ADMIN_SEARCH_IN_ATTR, true); // true = search in attributes
               define(ADMIN_SEARCH_IN_DESC, false); // true = search in description
               //build query
               $add_select = 's.specials_new_products_price,specials_quantity,expires_date,s.status as specials_status,'; 
               
               require_once(DIR_FS_INC.'auto_include.inc.php');
               foreach(auto_include(DIR_FS_ADMIN.'includes/extra/modules/categories_view/products_search/','php') as $file) require ($file);
               
               $add_select .= (defined('ADD_SELECT_PRODUCT') && ADD_SELECT_PRODUCT != '' ? ADD_SELECT_PRODUCT : '');
               
               $select_str = "SELECT DISTINCT $add_select
                                              p.products_tax_class_id,
                                              p.products_id,
                                              pd.products_name,
                                              p.products_sort,
                                              p.products_quantity,
                                              p.products_image,
                                              p.products_model,
                                              p.products_price,
                                              p.products_discount_allowed,
                                              p.products_date_added,
                                              p.products_last_modified,
                                              p.products_date_available,
                                              p.products_status,
                                              p.products_startpage,
                                              p.products_startpage_sort,
                                              p2c.categories_id ";

               $from_str  = " FROM ".TABLE_PRODUCTS." AS p ";
               $from_str .= "LEFT JOIN ".TABLE_PRODUCTS_DESCRIPTION." AS pd ON (p.products_id = pd.products_id) ";
               $from_str .= "JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." AS p2c ON (p.products_id = p2c.products_id) ";
               if (ADMIN_SEARCH_IN_ATTR == 'true') {
                 $from_str .= "LEFT OUTER JOIN ".TABLE_PRODUCTS_ATTRIBUTES." AS pa ON (p.products_id = pa.products_id) ";
                 $from_str .= "LEFT OUTER JOIN ".TABLE_PRODUCTS_OPTIONS_VALUES." AS pov ON (pa.options_values_id = pov.products_options_values_id) ";
               }
               $from_str .= "LEFT OUTER JOIN ".TABLE_SPECIALS." AS s ON (p.products_id = s.products_id) AND s.status = '1'";
               //where-string
               $where_str = " WHERE pd.language_id = '".(int) $_SESSION['languages_id']."'";
               $where_str .= $current_category_id != '' ? " AND p2c.categories_id = '" . (int)$current_category_id ."'" : '';
               
               //go for keywords... this is the main search process
               if (xtc_not_null($search)) {
                 if (xtc_parse_search_string(stripslashes($search), $search_keywords)) {
                   $where_str .= " AND ( ";
                   for ($i = 0, $n = sizeof($search_keywords); $i < $n; $i ++) {
                     switch ($search_keywords[$i]) {
                       case '(' :
                       case ')' :
                       case 'and' :
                       case 'or' :
                         $where_str .= " ".$search_keywords[$i]." ";
                         break;
                       default :
                         $ent_keyword = encode_htmlentities($search_keywords[$i]);
                         $ent_keyword = ($ent_keyword != $search_keywords[$i]) ? xtc_db_input($ent_keyword) : false;
                         $keyword = xtc_db_input($search_keywords[$i]);
                         $where_str .= " ( ";
                         $where_str .= "pd.products_keywords LIKE ('%".$keyword."%') ";
                         $where_str .= ($ent_keyword) ? "OR pd.products_keywords LIKE ('%".$ent_keyword."%') " : '';
                         if (ADMIN_SEARCH_IN_DESC == 'true') {
                           $where_str .= "OR pd.products_description LIKE ('%".$keyword."%') ";
                           $where_str .= ($ent_keyword) ? "OR pd.products_description LIKE ('%".$ent_keyword."%') " : '';
                           $where_str .= "OR pd.products_short_description LIKE ('%".$keyword."%') ";
                           $where_str .= ($ent_keyword) ? "OR pd.products_short_description LIKE ('%".$ent_keyword."%') " : '';
                         }
                         $where_str .= "OR pd.products_name LIKE ('%".$keyword."%') ";
                         $where_str .= ($ent_keyword) ? "OR pd.products_name LIKE ('%".$ent_keyword."%') " : '';
                         $where_str .= "OR p.products_model LIKE ('%".$keyword."%') ";
                         $where_str .= ($ent_keyword) ? "OR p.products_model LIKE ('%".$ent_keyword."%') " : '';
                         if (defined('ADD_WHERE_SEARCH') && ADD_WHERE_SEARCH != '') {
                            $add_where = explode(',',ADD_WHERE_SEARCH);
                            if (count($add_where)) {
                              foreach($add_where as $entry) {
                                $entry = trim($entry);
                                if ($entry != '') {
                                  $where_str .= "OR ". $entry ." LIKE ('%".$keyword."%') ";
                                  $where_str .= $ent_keyword ? "OR ". $entry ." LIKE ('%".$ent_keyword."%') " : '';
                                }
                              }
                            }
                         }
                         if (ADMIN_SEARCH_IN_ATTR == 'true') {
                           $where_str .= "OR pa.attributes_model LIKE ('%".$keyword."%') ";
                           $where_str .= ($ent_keyword) ? "OR pa.attributes_model LIKE ('%".$ent_keyword."%') " : '';
                           $where_str .= "OR (pov.products_options_values_name LIKE ('%".$keyword."%') ";
                           $where_str .= ($ent_keyword) ? "OR pov.products_options_values_name LIKE ('%".$ent_keyword."%') " : '';
                           $where_str .= "AND pov.language_id = '".(int) $_SESSION['languages_id']."')";
                         }
                         $where_str .= " ) ";
                         break;
                     }
                   }
                   $where_str .= " ) GROUP BY p.products_id ORDER BY " . $prodsort;
                 }
               } elseif (xtc_not_null($search_id)) {
                 $where_str .= " AND p.products_id = '" . (int)$search_id . "' ";
                 $where_str .= " GROUP BY p.products_id ORDER BY " . $prodsort;
               }

               //glue together
               $listing_sql = $select_str.$from_str.$where_str;
               $products_split = new splitPageResults($_GET['page'], MAX_DISPLAY_LIST_PRODUCTS, $listing_sql, $products_query_numrows, 'p.products_id');
               $products_query = xtc_db_query($listing_sql);
             } else {
                //display "products on startpage", no entry in table produtcs_to_categories used
                if ($current_category_id == 0) {
                  $add_where = 'WHERE p.products_startpage = 1';
                  $add_join = '';
                //display products in categories
                } else {
                  $add_where = '';
                  $add_join = "JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p.products_id = p2c.products_id AND p2c.categories_id = '" . $current_category_id . "'";
                }
                //display only inactive products
                if ($search_inactive) {
                  $add_where = ' WHERE p.products_status = 0 ';
                  $add_join = '';
                } 
                $add_join .= "LEFT OUTER JOIN ".TABLE_SPECIALS." AS s ON (p.products_id = s.products_id) AND s.status = '1'";
                $add_select = 's.specials_new_products_price,specials_quantity,expires_date,s.status as specials_status,';
                $select_str = "SELECT $add_select
                                      p.products_tax_class_id,
                                      p.products_sort,
                                      p.products_id,
                                      pd.products_name,
                                      p.products_quantity,
                                      p.products_image,
                                      p.products_model,
                                      p.products_price,
                                      p.products_discount_allowed,
                                      p.products_date_added,
                                      p.products_last_modified,
                                      p.products_date_available,
                                      p.products_status,
                                      p.products_startpage,
                                      p.products_startpage_sort
                                 FROM " . TABLE_PRODUCTS . " p
                            LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON p.products_id = pd.products_id AND pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                                      " . $add_join . $add_where ."
                             ORDER BY " . $prodsort;
                $products_split = new splitPageResults($_GET['page'], MAX_DISPLAY_LIST_PRODUCTS, $select_str, $products_query_numrows);
                $products_query = xtc_db_query($select_str);
             }

             // ----------------------------------------------------------------------------------------------------- //
             // WHILE loop to display products STARTS
             // ----------------------------------------------------------------------------------------------------- //

             while ($products = xtc_db_fetch_array($products_query)) {
               $products_count++;
               $rows++;
               // Get categories_id for product if search
               if (xtc_not_null($search)) {
                 $cPath=$products['categories_id'];
               }
               if ( (!isset($_GET['pID']) && !isset($_GET['cID']) || (@$_GET['pID'] == $products['products_id'])) && !isset($pInfo) && !isset($cInfo) && (substr($action, 0, 4) != 'new_') ) {
                 // find out the rating average from customer reviews
                 $reviews_query = xtc_db_query("SELECT (avg(reviews_rating) / 5 * 100) AS average_rating FROM " . TABLE_REVIEWS . " WHERE products_id = '" . (int)$products['products_id'] . "'");
                 $reviews = xtc_db_fetch_array($reviews_query);
                 $pInfo_array = xtc_array_merge($products, $reviews);
                 $pInfo = new objectInfo($pInfo_array);
               }
               if (isset($pInfo) && (is_object($pInfo)) && ($products['products_id'] == $pInfo->products_id) ) {
                 echo '<tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" >' . "\n";
               } else {
                 echo '<tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" >' . "\n";
               }
                 //checkbox again after submit and before final submit
                 $is_checked = false;
                 if (isset($_POST['multi_products']) && is_array($_POST['multi_products'])) {
                   if (in_array($products['products_id'], $_POST['multi_products'])) {
                     $is_checked = true;
                   }
                 }
                 ?>
                 <td class="categories_view_data">
                   <?php echo xtc_draw_checkbox_field('multi_products[]', $products['products_id'], $is_checked); ?>
                 </td>
                 <?php
                 if ($products['products_model'] !='' ){
                   ?>
                   <td class="categories_view_data">
                     <?php echo $products['products_model']; ?>
                   </td>
                   <?php
                 } else {
                   ?>
                    <td class="categories_view_data">--</td>
                   <?php
                 }
                 ?>
                 <td class="categories_view_data">
                   <?php
                   if ($current_category_id == 0){
                       echo $products['products_startpage_sort'];
                   } else {
                       echo $products['products_sort'];
                   }
                   ?>
                 </td>
                 <?php
                 if( USE_ADMIN_THUMBS_IN_LIST=='true' ) { ?>
                   <td class="categories_view_data txta-c">
                     <?php
                     echo xtc_product_thumb_image($products['products_image'], $products['products_name'], '','',$admin_thumbs_size);
                     ?>
                   </td>
                   <?php
                 }
                 ?>
                 <td class="categories_view_data txta-l" style="padding-left: 8px;">
                   <?php
                   echo '<a href="'. xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('cPath', 'action', 'pID', 'cID')) . 'cPath=' . $cPath . '&pID=' . $products['products_id'] ) . '&action=new_product' . '">' . xtc_image(DIR_WS_ICONS . 'icon_edit.gif', ICON_EDIT, '', '', $icon_padding). '</a>';
                   if (function_exists('attributes_iframe_link')) {
                     echo attributes_iframe_link($products['products_id'], true);
                   } else {
                     echo '<a href="'. xtc_href_link(FILENAME_NEW_ATTRIBUTES, xtc_get_all_get_params(array('cPath', 'action', 'pID', 'cID')) . 'cpath=' . $cPath . '&current_product_id=' . $products['products_id'] ) . '&action=edit' . '">' . xtc_image(DIR_WS_ICONS . 'icon_edit_attr.gif', BUTTON_EDIT_ATTRIBUTES,'', '', $icon_padding). '</a>';
                   }
                   echo '<a href="'.xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('cPath', 'action', 'pID', 'cID')) . 'cPath=' . $cPath . '&pID=' . $products['products_id']) .'">' . $products['products_name'] . '</a>';
                   ?>
                 </td>
                 <?php
                 // check product and attributes stock
                 if (STOCK_CHECK == 'true') { ?>
                   <td class="categories_view_data">
                     <?php echo check_stock($products['products_id']);
                     echo '&nbsp;'.$products['products_quantity'];
                     ?>
                   </td>
                   <?php
                 }
                 ?>
                 <td class="categories_view_data">
                   <?php
                   if ($products['products_status'] == '1') {
                     echo xtc_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '<a href="' . xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('cPath', 'action', 'pID', 'cID')) . 'action=setpflag&flag=0&pID=' . $products['products_id'] . '&cPath=' . $cPath) . '">&nbsp;&nbsp;' . xtc_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>';
                   } else {
                     echo '<a href="' . xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('cPath', 'action', 'pID', 'cID')) . 'action=setpflag&flag=1&pID=' . $products['products_id'] . '&cPath=' . $cPath) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '&nbsp;&nbsp;</a>' . xtc_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
                   }
                   ?>
                 </td>
                 <td class="categories_view_data">
                   <?php
                   if ($products['products_startpage'] == '1') {
                     echo xtc_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '<a href="' . xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('cPath', 'action', 'pID', 'cID')) . 'action=setsflag&flag=0&pID=' . $products['products_id'] . '&cPath=' . $cPath) . '">&nbsp;&nbsp;' . xtc_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>';
                   } else {
                     echo '<a href="' . xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('cPath', 'action', 'pID', 'cID')) . 'action=setsflag&flag=1&pID=' . $products['products_id'] . '&cPath=' . $cPath) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '&nbsp;&nbsp;</a>' . xtc_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
                   }
                   ?>
                 </td>
                 <td class="categories_view_data">
                   <?php
                   //show price
                   if ($products['specials_status'] == 1) {
                     echo '<div class="oldPrice">'.$currencies->format($products['products_price']) . '</div>'. 
                     '<div class="specialPrice">'.$currencies->format($products['specials_new_products_price']) . '</div>';
                   } else {
                     echo $currencies->format($products['products_price']);
                   }
                   ?>
                 </td>
                 <td class="categories_view_data">
                   <?php
                   // Show Max Allowed discount
                   echo $products['products_discount_allowed'] . ' %';
                   ?>
                 </td>
                 <td class="categories_view_data">
                   <?php
                   if (isset($pInfo) && (is_object($pInfo)) && ($products['products_id'] == $pInfo->products_id) ) {
                     echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', '');
                   } else {
                     echo '<a href="' . xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('cPath', 'action', 'pID', 'cID')) . 'cPath=' . $cPath . '&pID=' . $products['products_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>';
                   }
                   ?>
                 </td>
               </tr>
               <?php
             // ----------------------------------------------------------------------------------------------------- //
             } //WHILE loop to display products ENDS
             // ----------------------------------------------------------------------------------------------------- //
             ?>              
            </table>             
            <!-- categories and products table ENDS -->
          </td>
          <!-- categories & products column ENDS -->
          <?php
          $heading = array();
          $contents = array();

          switch ($action) {
            case 'multi_action':
              // --------------------
              // multi_move confirm
              // --------------------
              if (xtc_not_null($_POST['multi_move'])) {
                $heading[]  = array('text' => '<b>' . TEXT_INFO_HEADING_MOVE_ELEMENTS . '</b>');
                $contents[] = array('text' => '<table width="100%" border="0">');
                if (is_array($_POST['multi_categories'])) {
                  foreach ($_POST['multi_categories'] AS $multi_category) {
                    $category_query = xtc_db_query("SELECT c.categories_id,
                                                           cd.categories_name,
                                                           c.categories_image,
                                                           c.parent_id,
                                                           c.sort_order,
                                                           c.date_added,
                                                           c.last_modified,
                                                           c.categories_status
                                                      FROM " . TABLE_CATEGORIES . " AS c,
                                                           " . TABLE_CATEGORIES_DESCRIPTION . " AS cd
                                                     WHERE c.categories_id = '" . (int)$multi_category . "'
                                                       AND c.categories_id = cd.categories_id
                                                       AND cd.language_id = '" . (int)$_SESSION['languages_id'] . "'");
                    $category = xtc_db_fetch_array($category_query);
                    $category_childs   = array('childs_count'   => $catfunc->count_category_childs($multi_category));
                    $category_products = array('products_count' => $catfunc->count_category_products($multi_category, true));
                    $cInfo_array = xtc_array_merge($category, $category_childs, $category_products);
                    $cInfo = new objectInfo($cInfo_array);
                    $contents[] = array('text' => '<tr><td style="border-bottom: 1px solid #af417e; margin-bottom: 10px;" class="infoBoxContent"><b>' . $cInfo->categories_name . '</b></td></tr>');
                    if ($cInfo->childs_count > 0) {
                      $contents[] = array('text' => '<tr><td class="infoBoxContent">' . sprintf(TEXT_MOVE_WARNING_CHILDS, $cInfo->childs_count) . '</td></tr>');
                    }
                    if ($cInfo->products_count > 0) {
                      $contents[] = array('text' => '<tr><td class="infoBoxContent">' . sprintf(TEXT_MOVE_WARNING_PRODUCTS, $cInfo->products_count) . '</td></tr>');
                    }
                  }
                  $category_tree = xtc_get_category_tree();
                }

                if (is_array($_POST['multi_products'])) {
                  if ($current_category_id != 0) {
                    foreach ($_POST['multi_products'] AS $multi_product) {
                      $contents[] = array('text' => '<tr><td style="border-bottom: 1px solid #af417e; margin-bottom: 10px;" class="infoBoxContent"><b>' . xtc_get_products_name($multi_product) . '</b></td></tr>');
                      $product_categories_string = '';
                      $product_categories = xtc_output_generated_category_path($multi_product, 'product');
                      $product_categories_string = '<tr><td class="infoBoxContent">' . $product_categories . '</td></tr>';
                      $contents[] = array('text' => $product_categories_string);
                    }
                    $category_tree = xtc_get_category_tree('0','','0');
                    $dispnone = '';
                  } else {
                    $contents[] = array('text' => TEXT_NO_MOVE_POSSIBLE);
                    $dispnone = ' dispnone';
                  }
                }
                if (isset($category_tree) && is_array($category_tree)) {
                  $contents[] = array('text' => '<tr><td class="infoBoxContent"><strong>' . TEXT_MOVE_ALL . '</strong></td></tr><tr><td>' . xtc_draw_pull_down_menu('move_to_category_id', $category_tree, $current_category_id) . '</td></tr>');
                }
                //close list table
                $contents[] = array('text' => '</table>');
                //add current category id, for moving products
                $contents[] = array('text' => '<input type="hidden" name="src_category_id" value="' . $current_category_id . '">');
                $contents[] = array('align' => 'center', 'text' => '<input class="button'.$dispnone.'" type="submit" name="multi_move_confirm" value="' . BUTTON_MOVE . '"> <a class="button" href="' . xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('cPath', 'action', 'pID', 'cID')) . (isset($_GET['cPath']) ? 'cPath=' . $cPath : '') . '&pID=' . $pInfo->products_id . '&cID=' . $cInfo->categories_id) . '">' . BUTTON_CANCEL . '</a>');
              }
              // multi_move confirm ENDS

              // --------------------
              // multi_delete confirm
              // --------------------
              if (xtc_not_null($_POST['multi_delete'])) {
                $heading[]  = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_ELEMENTS . '</b>');
                $contents[] = array('text' => '<a class="button" href="javascript:SwitchCheckDeleteConfirm()" onclick="this.blur()">' . BUTTON_REVERSE_SELECTION . '</a>');
                $contents[] = array('text' => '<table width="100%" border="0">');

                if (is_array($_POST['multi_categories'])) {
                  foreach ($_POST['multi_categories'] AS $multi_category) {
                    $category_query = xtc_db_query("SELECT c.categories_id,
                                                           cd.categories_name,
                                                           c.categories_image,
                                                           c.parent_id,
                                                           c.sort_order,
                                                           c.date_added,
                                                           c.last_modified,
                                                           c.categories_status
                                                      FROM " . TABLE_CATEGORIES . " AS c,
                                                           " . TABLE_CATEGORIES_DESCRIPTION . " AS cd
                                                     WHERE c.categories_id = '" . (int)$multi_category . "'
                                                       AND c.categories_id = cd.categories_id
                                                       AND cd.language_id = '" . (int)$_SESSION['languages_id'] . "'");
                    $category = xtc_db_fetch_array($category_query);
                    $category_childs   = array('childs_count'   => $catfunc->count_category_childs($multi_category));
                    $category_products = array('products_count' => $catfunc->count_category_products($multi_category, true));
                    $cInfo_array = xtc_array_merge($category, $category_childs, $category_products);
                    $cInfo = new objectInfo($cInfo_array);
                    $contents[] = array('text' => '<tr><td style="border-bottom: 1px solid #af417e; margin-bottom: 10px;" class="infoBoxContent"><b>' . $cInfo->categories_name . '</b></td></tr>');
                    if ($cInfo->childs_count > 0) {
                      $contents[] = array('text' => '<tr><td class="infoBoxContent">' . sprintf(TEXT_DELETE_WARNING_CHILDS, $cInfo->childs_count) . '</td></tr>');
                    }
                    if ($cInfo->products_count > 0) {
                      $contents[] = array('text' => '<tr><td class="infoBoxContent">' . sprintf(TEXT_DELETE_WARNING_PRODUCTS, $cInfo->products_count) . '</td></tr>');
                    }
                  }
                }

                if (is_array($_POST['multi_products'])) {
                  foreach ($_POST['multi_products'] AS $multi_product) {
                    $contents[] = array('text' => '<tr><td style="border-bottom: 1px solid #af417e; margin-bottom: 10px;" class="infoBoxContent"><b>' . xtc_get_products_name($multi_product) . '</b></td></tr>');
                    $product_categories_string = '';
                    $product_categories = xtc_generate_category_path($multi_product, 'product');
                    for ($i = 0, $n = sizeof($product_categories); $i < $n; $i++) {
                      $category_path = '';
                      for ($j = 0, $k = sizeof($product_categories[$i]); $j < $k; $j++) {
                        $category_path .= $product_categories[$i][$j]['text'] . '&nbsp;&gt;&nbsp;';
                      }
                      $category_path = substr($category_path, 0, -16);
                      $product_categories_string .= xtc_draw_checkbox_field('multi_products_categories['.$multi_product.'][]', $product_categories[$i][sizeof($product_categories[$i])-1]['id'], true) . '&nbsp;' . $category_path . '<br />';
                    }
                    $product_categories_string = substr($product_categories_string, 0, -4);
                    $product_categories_string = '<tr><td class="infoBoxContent">' . $product_categories_string . '</td></tr>';
                    $contents[] = array('text' => $product_categories_string);
                  }
                }

                //close list table
                $contents[] = array('text' => '</table>');
                $contents[] = array('align' => 'center', 'text' => '<input class="button" type="submit" name="multi_delete_confirm" value="' . BUTTON_DELETE . '"> <a class="button" href="' . xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('cPath', 'action', 'pID', 'cID')) . 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . '&cID=' . $cInfo->categories_id) . '">' . BUTTON_CANCEL . '</a>');
              }
              // multi_delete confirm ENDS

              // --------------------
              // multi_copy confirm
              // --------------------
              if (xtc_not_null($_POST['multi_copy'])) {
                $heading[]  = array('text' => '<b>' . TEXT_INFO_HEADING_COPY_TO . '</b>');
                $contents[] = array('text' => '<a class="button" href="javascript:SwitchCheckCopyConfirm()" onclick="this.blur()">' . BUTTON_REVERSE_SELECTION . '</a>');
                $contents[] = array('text' => '<table width="100%" border="0">');
                if (is_array($_POST['multi_categories'])) {
                  foreach ($_POST['multi_categories'] AS $multi_category) {
                    $category_query = xtc_db_query("SELECT c.categories_id,
                                                           cd.categories_name,
                                                           c.categories_image,
                                                           c.parent_id,
                                                           c.sort_order,
                                                           c.date_added,
                                                           c.last_modified,
                                                           c.categories_status
                                                      FROM " . TABLE_CATEGORIES . " AS c,
                                                           " . TABLE_CATEGORIES_DESCRIPTION . " AS cd
                                                     WHERE c.categories_id = '" . (int)$multi_category . "'
                                                       AND c.categories_id = cd.categories_id
                                                       AND cd.language_id = '" . (int)$_SESSION['languages_id'] . "'");
                    $category = xtc_db_fetch_array($category_query);
                    $category_childs   = array('childs_count'   => $catfunc->count_category_childs($multi_category));
                    $category_products = array('products_count' => $catfunc->count_category_products($multi_category, true));
                    $cInfo_array = xtc_array_merge($category, $category_childs, $category_products);
                    $cInfo = new objectInfo($cInfo_array);
                    $contents[] = array('text' => '<tr><td style="border-bottom: 1px solid #af417e; margin-bottom: 10px;" class="infoBoxContent"><b>' . $cInfo->categories_name . '</b></td></tr>');
                    if ($cInfo->childs_count > 0) {
                      $contents[] = array('text' => '<tr><td class="infoBoxContent">' . sprintf(TEXT_MOVE_WARNING_CHILDS, $cInfo->childs_count) . '</td></tr>');
                    }
                    if ($cInfo->products_count > 0) {
                      $contents[] = array('text' => '<tr><td class="infoBoxContent">' . sprintf(TEXT_MOVE_WARNING_PRODUCTS, $cInfo->products_count) . '</td></tr>');
                    }
                  }
                }

                if (is_array($_POST['multi_products'])) {
                  foreach ($_POST['multi_products'] AS $multi_product) {
                    $contents[] = array('text' => '<tr><td style="border-bottom: 1px solid #af417e; margin-bottom: 10px;" class="infoBoxContent"><b>' . xtc_get_products_name($multi_product) . '</b></td></tr>');
                    $product_categories_string = '';
                    $product_categories = xtc_output_generated_category_path($multi_product, 'product');
                    $product_categories_string = '<tr><td class="infoBoxContent">' . $product_categories . '</td></tr>';
                    $contents[] = array('text' => $product_categories_string);
                  }
                }

                //close list table
                $contents[] = array('text' => '</table>');
                if (QUICKLINK_ACTIVATED=='true') {
                  $contents[] = array('text' => '<hr noshade>');
                  $contents[] = array('text' => '<b>'.TEXT_MULTICOPY.'</b><br />'.TEXT_MULTICOPY_DESC);
                  if (is_array($_POST['multi_products'])) {
                    $cat_tree=xtc_get_category_tree('0','','0');
                  } else {
                    $cat_tree=xtc_get_category_tree();
                  }
                  $tree='';
                  for ($i=0;$n=sizeof($cat_tree),$i<$n;$i++) {
                    $tree .= xtc_draw_checkbox_field('dest_cat_ids[]', $cat_tree[$i]['id']).'<font size="1">'.$cat_tree[$i]['text'].'</font><br />';
                  }
                  $contents[] = array('text' => $tree.'<br /><hr noshade>');
                  $contents[] = array('text' => '<b>'.TEXT_SINGLECOPY.'</b><br />'.TEXT_SINGLECOPY_DESC);
                }
                if (is_array($_POST['multi_products'])) {
                  $category_tree=xtc_get_category_tree('0','','0');
                } else {
                  $category_tree=xtc_get_category_tree();
                }
                $contents[] = array('text' => '<br />' . TEXT_SINGLECOPY_CATEGORY . '<br />' . xtc_draw_pull_down_menu('dest_category_id', $category_tree, $current_category_id) . '<br /><hr noshade>');
                $contents[] = array('text' => '<strong>' . TEXT_HOW_TO_COPY . '</strong><br />' . xtc_draw_radio_field('copy_as', 'link', true) . ' ' . TEXT_COPY_AS_LINK . '<br />' . xtc_draw_radio_field('copy_as', 'duplicate') . ' ' . TEXT_COPY_AS_DUPLICATE . '<br /><hr noshade>');
                $contents[] = array('text' => '<br />' . TEXT_HOW_TO_LINK . '<br />' . xtc_draw_checkbox_field('link_to_product', 'link_to_product', true).'<font size="1">'.TEXT_HOW_TO_LINK_INFO.'</font><br /><hr noshade>');
                $contents[] = array('text' => '<strong>' . TEXT_ATTRIBUTE_COPY . '</strong><br />' . xtc_draw_checkbox_field('attr_copy', 'attr_copy', false).'<font size="1">'.TEXT_ATTRIBUTE_COPY_INFO.'</font><br /><hr noshade>');
                // BOF - Timo Paul (mail[at]timopaul[dot]biz) - 2014-01-17 - duplicate products content and links
                $contents[] = array('text' => '<strong>' . TEXT_CONTENT_COPY . '</strong><br />' . xtc_draw_checkbox_field('cnt_copy', 'cnt_copy', false).'<font size="1">'.TEXT_CONTENT_COPY_INFO.'</font><br /><hr noshade>');
                $contents[] = array('text' => '<strong>' . TEXT_LINKS_COPY . '</strong><br />' . xtc_draw_checkbox_field('links_copy', 'links_copy', false).'<font size="1">'.TEXT_LINKS_COPY_INFO.'</font><br /><hr noshade>');
                // EOF - Timo Paul (mail[at]timopaul[dot]biz) - 2014-01-17 - duplicate products content and links
                $contents[] = array('align' => 'center', 'text' => '<input class="button" type="submit" name="multi_copy_confirm" value="' . BUTTON_COPY . '"> <a class="button" href="' . xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('cPath', 'action', 'pID', 'cID')) . 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . '&cID=' . $cInfo->categories_id) . '">' . BUTTON_CANCEL . '</a>');
              }
              // multi_copy confirm ENDS
              break;

            default:
              if ($rows > 0) {
                if (isset($cInfo) && is_object($cInfo)) {
                  // category info box contents
                  $heading[]  = array('text' => '<b>' . $cInfo->categories_name . '</b>');
                  //Multi Element Actions
                  $contents[] = array('align' => 'center', 'text' => '<div style="padding-top: 5px; font-weight: bold; width: 100%;">' . TEXT_MARKED_ELEMENTS . '</div>');
                  $contents[] = array('align' => 'center', 'text' => '<input type="submit" class="button" name="multi_delete" value="'. BUTTON_DELETE . '">&nbsp;<input type="submit" class="button" name="multi_move" value="' . BUTTON_MOVE . '">&nbsp;<input type="submit" class="button" name="multi_copy" value="' . BUTTON_COPY . '">');
                  $contents[] = array('align' => 'center', 'text' => '<input type="submit" class="button" name="multi_status_on" value="'. BUTTON_STATUS_ON . '">&nbsp;<input type="submit" class="button" name="multi_status_off" value="' . BUTTON_STATUS_OFF . '">');
                  // BOF - Tomcraft - 2009-11-28 - Included xs:booster
                  if (defined('MODULE_XTBOOSTER_STATUS') && MODULE_XTBOOSTER_STATUS == 'True') {
                    $contents[] = array('align' => 'center', 'text' => xtc_button(BUTTON_XTBOOSTER_MULTI, 'submit', 'name="multi_xtb"'));
                  }
                  // EOF - Tomcraft - 2009-11-28 - Included xs:booster
                  //Single Element Actions
                  $contents[] = array('align' => 'center', 'text' => '<div style="padding-top: 5px; font-weight: bold; width: 100%; border-top: 1px solid #aaa; margin-top: 5px;">' . TEXT_ACTIVE_ELEMENT . '</div>');
                  $contents[] = array('align' => 'center', 'text' => '<a class="button" href="' . xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('cPath', 'action', 'pID', 'cID')) . 'cPath=' . $cPath . '&cID=' . $cInfo->categories_id . '&action=edit_category') . '">' . BUTTON_EDIT . '</a>');
                  $contents[] = array('align' => 'center', 'text' => '<a class="button" href="' . xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('cPath', 'action', 'pID', 'cID')) . 'cPath=' . $cPath . '_' . $cInfo->categories_id . '&action=new_category') . '">' . BUTTON_NEW_CATEGORIES . '&nbsp;' . TEXT_IN .'<br />' . $cInfo->categories_name .'</a>
                                                                      <a class="button" href="' . xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('cPath', 'action', 'pID', 'cID')) . 'cPath=' . $cPath . '_' . $cInfo->categories_id . '&action=new_product') . '">' . BUTTON_NEW_PRODUCTS . '&nbsp;' . TEXT_IN .'<br />' . $cInfo->categories_name . '</a>');
                  //Insert new Element Actions
                  $contents[] = array('align' => 'center', 'text' => '<div style="padding-top: 5px; font-weight: bold; width: 100%; border-top: 1px solid #aaa; margin-top: 5px;">' . TEXT_INSERT_ELEMENT . '</div>');
                  if (!xtc_not_null($search)) {
                    $buttons_new_elements = '<a class="button" onclick="this.blur()" href="' . xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('cPath', 'action', 'pID', 'cID')) . 'cPath=' . $cPath . '&action=new_category') . '">' . BUTTON_NEW_CATEGORIES . '</a>';
                    if ($cPath != '0') {
                      $buttons_new_elements .= '&nbsp;';
                      $buttons_new_elements .= '<a class="button" onclick="this.blur()" href="' . xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('cPath', 'action', 'pID', 'cID')) . 'cPath=' . $cPath . '&action=new_product') . '">' . BUTTON_NEW_PRODUCTS . '</a>';
                    }
                    $contents[] = array('align' => 'center', 'text' => $buttons_new_elements);
                  }
                  //Informations
                  $contents[] = array('align' => 'center', 'text' => '<div style="padding-top: 5px; font-weight: bold; width: 100%; border-top: 1px solid #aaa; margin-top: 5px;">' . TEXT_INFORMATIONS . '</div>');
                  $contents[] = array('text'  => '<div style="padding-left: 10px;">' . TEXT_DATE_ADDED . ' ' . xtc_date_short($cInfo->date_added) . '</div>');
                  if (xtc_not_null($cInfo->last_modified)) {
                    $contents[] = array('text' => '<div style="padding-left: 10px;">' . TEXT_LAST_MODIFIED . ' ' . xtc_date_short($cInfo->last_modified) . '</div>');
                  }
                  $contents[] = array('align' => 'center', 'text' => '<div style="padding: 10px;">' . xtc_info_image_c($cInfo->categories_image, $cInfo->categories_name, '','',$box_cat_image_size)   . '</div><div style="padding-bottom: 10px;">' . $cInfo->categories_image . '</div>');
                } elseif (isset($pInfo) && is_object($pInfo)) {
                  // product info box contents
                  $heading[]  = array('text' => '<b>' . xtc_get_products_name($pInfo->products_id, $_SESSION['languages_id']) . '</b>');
                  //Multi Element Actions
                  $contents[] = array('align' => 'center', 'text' => '<div style="padding-top: 5px; font-weight: bold; width: 100%;">' . TEXT_MARKED_ELEMENTS . '</div>');
                  $contents[] = array('align' => 'center', 'text' => xtc_button(BUTTON_DELETE, 'submit', 'name="multi_delete"').'&nbsp;'.xtc_button(BUTTON_MOVE, 'submit', 'name="multi_move"').'&nbsp;'.xtc_button(BUTTON_COPY, 'submit', 'name="multi_copy"'));
                  $contents[] = array('align' => 'center', 'text' => '<input type="submit" class="button" name="multi_status_on" value="'. BUTTON_STATUS_ON . '">&nbsp;<input type="submit" class="button" name="multi_status_off" value="' . BUTTON_STATUS_OFF . '">');
                  // BOF - Tomcraft - 2009-11-28 - Included xs:booster
                  if (defined('MODULE_XTBOOSTER_STATUS') && MODULE_XTBOOSTER_STATUS=='True') {
                    $contents[] = array('align' => 'center', 'text' => xtc_button(BUTTON_XTBOOSTER_MULTI, 'submit', 'name="multi_xtb"'));
                  }
                  // EOF - Tomcraft - 2009-11-28 - Included xs:booster
                  //Single Product Actions
                  $contents[] = array('align' => 'center', 'text' => '<div style="padding-top: 5px; font-weight: bold; width: 100%; border-top: 1px solid #aaa; margin-top: 5px;">' . TEXT_ACTIVE_ELEMENT . '</div>');
                  $contents[] = array('align' => 'center', 
                                      'text' => '<a class="button" href="' . xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('cPath', 'action', 'pID', 'cID')) . 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . '&action=new_product') . '">' . BUTTON_EDIT . '</a>'
                                                 . (function_exists('attributes_iframe_link') ? attributes_iframe_link($pInfo->products_id) :
                                                 '<a href="'.xtc_href_link(FILENAME_NEW_ATTRIBUTES, 'action=edit&current_product_id='.$pInfo->products_id.'&cpath='.$cPath.'&page='.(int)$_GET['page']).'" class="button">' . BUTTON_EDIT_ATTRIBUTES . '</a>'                                                 
                                                 ));
                  $contents[] = array('align' => 'center', 
                                      'text' =>  '<a class="button" href="'.xtc_href_link(FILENAME_CATEGORIES, 'action=edit_crossselling&current_product_id='.$pInfo->products_id.'&cpath='.$cPath).'">' . BUTTON_EDIT_CROSS_SELLING . '</a>
                                                  <a class="button" href="' . xtc_href_link(FILENAME_CONTENT_MANAGER, xtc_get_all_get_params(array('action')) . (isset($_GET['action']) ? 'last_action='.$_GET['action'] : '') .'&action=new_products_content'.'&set=product') . '">' . BUTTON_NEW_CONTENT . '</a>'
                                                 );
                  
                  // BOC Included xs:booster                  
                  if (defined('MODULE_XTBOOSTER_STATUS') && MODULE_XTBOOSTER_STATUS=='True') {
                    $contents[] = array('align' => 'center', 
                                        'text' =>  '<a href="'.xtc_href_link(FILENAME_XTBOOSTER, 'action=edit_xtbooster&xtb_module=add&current_product_id='.$pInfo->products_id.'&cpath='.$cPath).'" class="button">' . BUTTON_EDIT_XTBOOSTER . '</a>'
                                                   );
                  }
                  // EOC Included xs:booster                  
                  //Insert new Element Actions
                  $contents[] = array('align' => 'center', 'text' => '<div style="padding-top: 5px; font-weight: bold; width: 100%; border-top: 1px solid #aaa; margin-top: 5px;">' . TEXT_INSERT_ELEMENT . '</div>');
                  if (!xtc_not_null($search)) {
                    $buttons_new_elements = '<a class="button" onclick="this.blur()" href="' . xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('cPath', 'action', 'pID', 'cID')) . 'cPath=' . $cPath . '&action=new_category') . '">' . BUTTON_NEW_CATEGORIES . '</a>';
                    if ($cPath != '0') {
                      $buttons_new_elements .= '&nbsp;';
                      $buttons_new_elements .= '<a class="button" onclick="this.blur()" href="' . xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('cPath', 'action', 'pID', 'cID')) . 'cPath=' . $cPath . '&action=new_product') . '">' . BUTTON_NEW_PRODUCTS . '</a>';
                    }
                    $contents[] = array('align' => 'center', 'text' => $buttons_new_elements);
                  }
                  //Informations
                  $contents[] = array('align' => 'center', 'text' => '<div style="padding-top: 5px; font-weight: bold; width: 100%; border-top: 1px solid #aaa; margin-top: 5px;">' . TEXT_INFORMATIONS . '</div>');
                  $contents[] = array('text'  => '<div style="padding-left: 10px;">' . TEXT_DATE_ADDED . ' ' . xtc_date_short($pInfo->products_date_added) . '</div>');
                  if (xtc_not_null($pInfo->products_last_modified)) {
                    $contents[] = array('text' => '<div style="padding-left: 10px;">' . TEXT_LAST_MODIFIED . '&nbsp;' . xtc_date_short($pInfo->products_last_modified) . '</div>');
                  }
                  if (date('Y-m-d') < $pInfo->products_date_available) {
                    $contents[] = array('text' => '<div style="padding-left: 10px;">' . TEXT_DATE_AVAILABLE . ' ' . xtc_date_short($pInfo->products_date_available) . '</div>');
                  }

                  // START IN-SOLUTION Berechung des Bruttopreises
                  $price = $pInfo->products_price;
                  $price = xtc_round($price,PRICE_PRECISION);
                  $price_string = '' . TEXT_PRODUCTS_PRICE_INFO . '&nbsp;' . $currencies->format($price);
                  if (PRICE_IS_BRUTTO == 'true' && ((isset($_GET['read']) && $_GET['read'] == 'only') || $action != 'new_product_preview') ){
                    $price_netto = xtc_round($price,PRICE_PRECISION);
                    $price = ($price*(xtc_get_tax_rate($pInfo->products_tax_class_id)+100)/100);
                    $price_string = '' . TEXT_PRODUCTS_PRICE_INFO . '&nbsp;' . $currencies->format($price) . '<br/>' . TXT_NETTO . $currencies->format($price_netto);
                  }
                  $contents[] = array('text' => '<div style="padding-left: 10px;">' . $price_string.  '</div><div style="padding-left: 10px;">' . TEXT_PRODUCTS_DISCOUNT_ALLOWED_INFO . '&nbsp;' . $pInfo->products_discount_allowed . ' %</div><div style="padding-left: 10px;">' .  TEXT_PRODUCTS_QUANTITY_INFO . '&nbsp;' . $pInfo->products_quantity . '</div>');
                  // END IN-SOLUTION

                  //SPECIALS
                  if ($pInfo->specials_status == 1) {
                    $special_price = $pInfo->specials_new_products_price;
                    $special_price = xtc_round($special_price,PRICE_PRECISION);
                    $specials_price_string = '' . TEXT_SPECIALS_SPECIAL_PRICE . '&nbsp;' . $currencies->format($special_price);
                    if (PRICE_IS_BRUTTO == 'true' && ((isset($_GET['read']) && $_GET['read'] == 'only') || $action != 'new_product_preview') ){
                      $special_price_netto = xtc_round($special_price,PRICE_PRECISION);
                      $special_price = ($special_price*(xtc_get_tax_rate($pInfo->products_tax_class_id)+100)/100);
                      $specials_price_string = '' . TEXT_SPECIALS_SPECIAL_PRICE . '&nbsp;' . $currencies->format($special_price) . '<br/>' . TXT_NETTO . $currencies->format($special_price_netto);
                    }
                    $contents[] = array('text' => '<div class="col-red" style="padding-left: 10px;">' . $specials_price_string .  '</div>'.
                                                  '<div class="col-red" style="padding-left: 10px;">' . TEXT_INFO_EXPIRES_DATE . '&nbsp;' . $pInfo->expires_date . '</div>'.
                                                  ($pInfo->specials_quantity > 0 ? '<div class="col-red" style="padding-left: 10px;">' .  TEXT_SPECIALS_SPECIAL_QUANTITY . '&nbsp;' . $pInfo->specials_quantity . '</div>' : '')
                                        );
                  }
                  //$contents[] = array('text' => '<br />' . TEXT_PRODUCTS_PRICE_INFO . ' ' . $currencies->format($pInfo->products_price) . '<br />' . TEXT_PRODUCTS_QUANTITY_INFO . ' ' . $pInfo->products_quantity);
                  $contents[] = array('text' => '<div style="padding-left: 10px; padding-bottom: 10px;">' . TEXT_PRODUCTS_AVERAGE_RATING . ' ' . number_format($pInfo->average_rating, 2) . '</div>');
                  $contents[] = array('text' => '<div style="padding-left: 10px; padding-bottom: 10px;">' . TEXT_PRODUCT_LINKED_TO . '<br />' . xtc_output_generated_category_path($pInfo->products_id, 'product') . '</div>');
                  $contents[] = array('align' => 'center', 'text' => '<div style="padding: 10px;">' . xtc_product_thumb_image($pInfo->products_image, $pInfo->products_name)  . '</div><div style="padding-bottom: 10px;">' . $pInfo->products_image.'</div>');
                }
              } else {
                // create category/product info
                $heading[] = array('text' => '<b>' . EMPTY_CATEGORY . '</b>');
                $contents[] = array('text' => sprintf(TEXT_NO_CHILD_CATEGORIES_OR_PRODUCTS, xtc_get_categories_name($current_category_id, $_SESSION['languages_id'])));
                $buttons_new_elements = '<br /><a class="button" onclick="this.blur()" href="' . xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('cPath', 'action', 'pID', 'cID')) . 'cPath=' . $cPath . '&action=new_category') . '">' . BUTTON_NEW_CATEGORIES . '</a>';
                if ($cPath != '0') {
                  $buttons_new_elements .= '&nbsp;';
                  $buttons_new_elements .= '<a class="button" onclick="this.blur()" href="' . xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('cPath', 'action', 'pID', 'cID')) . 'cPath=' . $cPath . '&action=new_product') . '">' . BUTTON_NEW_PRODUCTS . '</a><br /><br />';
                }
                $contents[] = array('align' => 'center', 'text' => $buttons_new_elements);
              }
              break;
          } //end switch
          if ((xtc_not_null($heading)) && (xtc_not_null($contents))) {
            //display info box
            echo '<td class="boxRight">' . "\n";
            $box = new box;
            echo $box->infoBox($heading, $contents);
            echo '</td>' . "\n";
          }
          ?>
          </form>
        </tr>
        <tr>
          <td>
            <!-- PAGINATION-->
            <div class="smallText pdg2 flt-l"><?php echo $products_split->display_count($products_query_numrows, MAX_DISPLAY_LIST_PRODUCTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></div>
            <div class="smallText pdg2 flt-r"><?php echo $products_split->display_links($products_query_numrows, MAX_DISPLAY_LIST_PRODUCTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], xtc_get_all_get_params(array('page', 'action', 'pID', 'cID')) ); ?></div>
          </td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td>            
            <!-- bottom buttons --> 
            <div style="padding-top:10px;">            
              <div class="smallText flt-l"><?php echo TEXT_CATEGORIES . '&nbsp;' . $categories_count . '<br />' . TEXT_PRODUCTS . '&nbsp;' . $products_count; ?></div>
              <div class="smallText flt-r">
                <?php
                if ($cPath) {
                  echo '<a class="button" onclick="this.blur()" href="' . xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('cPath', 'action', 'pID', 'cID')) .  $cPath_back . '&cID=' . $current_category_id) . '">' . BUTTON_BACK . '</a>&nbsp;';
                }
                echo '<a class="button" href="javascript:SwitchCheck()" onclick="this.blur()">' . BUTTON_REVERSE_SELECTION . '</a>&nbsp;';
                echo '<a class="button" href="javascript:SwitchProducts()" onclick="this.blur()">' . BUTTON_SWITCH_PRODUCTS . '</a>&nbsp;';
                echo '<a class="button" href="javascript:SwitchCategories()" onclick="this.blur()">' . BUTTON_SWITCH_CATEGORIES . '</a>&nbsp;';
                ?>
              </div>
            </div>              
          </td>
          <td>&nbsp;</td>
        </tr>    
      </table>