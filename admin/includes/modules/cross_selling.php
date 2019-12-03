<?php
/* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2006 XT-Commerce (cross_selling.php 799 2005-02-23)

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  //BOF - web28 -  2012-08-25 - change imagesize by css size  
  if( defined('USE_ADMIN_THUMBS_IN_LIST_STYLE')) {
    $admin_thumbs_size = 'style="'.USE_ADMIN_THUMBS_IN_LIST_STYLE.'"';
  } else {
    $admin_thumbs_size = 'style="max-width: 40px; max-height: 40px;"';
  }
  //EOF - web28 - 2012-08-25 - change imagesize by css size

  $confirm_save_entry = ' onclick="ButtonClicked(this);"';
  $confirm_submit = defined('CONFIRM_SAVE_ENTRY') && CONFIRM_SAVE_ENTRY == 'true' ? ' onsubmit="return confirmSubmit(\'\',\''. SAVE_ENTRY .'\',this)"' : '';

  // select article data
  $article_query = xtc_db_query(
     "SELECT products_name
        FROM ".TABLE_PRODUCTS_DESCRIPTION."
       WHERE products_id='".(int) $_GET['current_product_id']."'
         AND language_id = '".(int)$_SESSION['languages_id']."'
     ");
  $article_data = xtc_db_fetch_array($article_query);
  $cross_sell_groups = xtc_get_cross_sell_groups();

  function buildCAT($catID) {
    static $categories;

    if (!isset($categories)) {
      $categories = array();
    }
  
    if (!isset($categories[$catID])) {
      $cat = array();
      while (getParent($catID) != 0 || $catID != 0) {
        $cat_select = xtc_db_query("SELECT categories_name
                                      FROM ".TABLE_CATEGORIES_DESCRIPTION."
                                     WHERE categories_id='".(int)$catID."'
                                       AND language_id='".(int)$_SESSION['languages_id']."'");
        $cat_data = xtc_db_fetch_array($cat_select);
        $catID = getParent($catID);
        $cat[] = $cat_data['categories_name'];
      }
      $categories[$catID] = implode(' > ', $cat);
    }
  
    return $categories[$catID];
  }

  function getParent($catID) {
    static $parent;
  
    if (!isset($parent)) {
      $parent = array();
    }
  
    if (!isset($parent[$catID])) {
      $parent_query = xtc_db_query("SELECT parent_id 
                                      FROM ".TABLE_CATEGORIES." 
                                     WHERE categories_id = '".(int)$catID."'");
      $parent_data = xtc_db_fetch_array($parent_query);
      $parent[$catID] = $parent_data['parent_id'];
    }
  
    return $parent[$catID];
  }
  ?>
    <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_news.png'); ?></div>
    <div class="pageHeading"><?php echo CROSS_SELLING_1; ?></div>
    <div class="main pdg2 flt-l"><?php echo CROSS_SELLING_2 . ' "' . $article_data['products_name'] . '"'; ?></div>
    <div class="clear mrg5">
      <a class="button" onClick="this.blur()" href="<?php echo xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('action', 'current_product_id', 'cpath')).'cPath='.$_GET['cpath'].'&pID='.$_GET['current_product_id']); ?>"><?php echo BUTTON_BACK; ?></a>
    </div>

      <?php
      echo xtc_draw_form('cross_selling', FILENAME_CATEGORIES, xtc_get_all_get_params(array('search', 'special')), 'POST', $confirm_submit);
        echo xtc_draw_hidden_field('special', 'edit');
        ?>
        <table class="tableBoxCenter collapse">
          <tr>
            <td class="dataTableHeadingContent" align="center" style="width:4%"><?php echo HEADING_DEL.'<br/>'.xtc_draw_checkbox_field('deleteall', '', false, '', 'id="deleteall"'); ?></td>
            <td class="dataTableHeadingContent" style="width:5%"><?php echo HEADING_SORTING; ?></td>
            <td class="dataTableHeadingContent" style="width:5%"><?php echo HEADING_GROUP; ?></td>
            <?php
            if( USE_ADMIN_THUMBS_IN_LIST=='true' ) { ?>
              <td class="dataTableHeadingContent" style="width:5%"><?php echo HEADING_IMAGE; ?></td>
              <?php
            }
            ?>
            <td class="dataTableHeadingContent" style="width:10%"><?php echo HEADING_MODEL; ?></td>
            <td class="dataTableHeadingContent" style="width:34%"><?php echo HEADING_NAME; ?></td>
            <td class="dataTableHeadingContent" style="width:<?php echo (( USE_ADMIN_THUMBS_IN_LIST=='true' ) ? '37%' : '42%'); ?>"><?php echo HEADING_CATEGORY; ?></td>
          </tr>
        <?php
          $cross_query = xtc_db_query("SELECT cs.ID,
                                              cs.products_id,
                                              cs.sort_order,
                                              cs.products_xsell_grp_name_id,
                                              p.products_model,
                                              p.products_id,
                                              p.products_image,
                                              pd.products_name
                                         FROM ".TABLE_PRODUCTS_XSELL." cs
                                         JOIN ".TABLE_PRODUCTS." p
                                              ON cs.xsell_id = p.products_id
                                         JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                              ON p.products_id = pd.products_id
                                                 AND pd.language_id = '".(int)$_SESSION['languages_id']."'
                                        WHERE cs.products_id = '".(int)$_GET['current_product_id']."'                             
                                     ORDER BY cs.sort_order");
          if (!xtc_db_num_rows($cross_query)) {
            ?>
            <tr>
              <td class="categories_view_data" colspan="<?php echo (( USE_ADMIN_THUMBS_IN_LIST=='true' ) ? '7' : '6'); ?>">- NO ENRTY -</td>
            </tr>
            <?php
          }
          while ($cross_data = xtc_db_fetch_array($cross_query)) {
            $categorie_query = xtc_db_query("SELECT categories_id
                                               FROM ".TABLE_PRODUCTS_TO_CATEGORIES."
                                              WHERE products_id='".$cross_data['products_id']."'
                                              LIMIT 0,1");
            $categorie_data = xtc_db_fetch_array($categorie_query);
            ?>
            <tr>
              <td class="categories_view_data"><?php echo xtc_draw_checkbox_field('ids[]', $cross_data['ID'], false, '', 'class="delete"'); ?></td>
              <td class="categories_view_data txta-l"><input name="sort[<?php echo $cross_data['ID']; ?>]" type="text" size="3" value="<?php echo $cross_data['sort_order']; ?>"></td>
              <td class="categories_view_data txta-l"><?php echo xtc_draw_pull_down_menu('group_name['.$cross_data['ID'].']',$cross_sell_groups,$cross_data['products_xsell_grp_name_id']); ?></td>
              <?php
              if( USE_ADMIN_THUMBS_IN_LIST=='true' ) { ?>
               <td class="categories_view_data txta-l">
                 <?php
                 echo xtc_product_thumb_image($cross_data['products_image'], $cross_data['products_name'], '','',$admin_thumbs_size);
                 ?>
               </td>
               <?php
              }
              ?>
              <td class="categories_view_data txta-l"><?php echo $cross_data['products_model']; ?></td>
              <td class="categories_view_data txta-l"><?php echo $cross_data['products_name']; ?></td>
              <td class="categories_view_data txta-l"><?php echo buildCAT($categorie_data['categories_id']); ?> </td>
            </tr>
            <?php 
          } 
          ?>
        </table>
        <div class="mrg5">
          <div class="flt-r">
            <?php if (!isset($_GET['search']) || $_GET['search'] == '') { ?>
              <input type="submit" class="button" value="<?php echo BUTTON_SAVE; ?>" <?php echo $confirm_save_entry;?>>
            <?php } ?>
          </div>
        </div>
      </form>
      
      <div class="mrg5">
        <div class="flt-l">
          <div class="main" style="display:inline-block; padding: 5px; vertical-align:top;">
            <?php
            echo xtc_draw_form('product_search', FILENAME_CATEGORIES, '', 'GET').PHP_EOL;
            echo xtc_draw_hidden_field('action', 'edit_crossselling').PHP_EOL;
            echo xtc_draw_hidden_field('current_product_id', $_GET['current_product_id']).PHP_EOL;
            echo xtc_draw_hidden_field('cpath', $_GET['cpath']).PHP_EOL;
            echo xtc_draw_hidden_field('page', $_GET['page']).PHP_EOL;
            echo CROSS_SELLING_SEARCH.'&nbsp;'.xtc_draw_input_field('search', ((isset($_GET['search'])) ? $_GET['search'] : ''), 'size="30"');
            echo '&nbsp;<input type="submit" class="button no_top_margin"  style="vertical-align:top;" onclick="this.blur();" value="' . BUTTON_SEARCH . '"/>';
            if (isset($_GET['search']) && $_GET['search'] != '') {
              echo '<a class="button no_top_margin" style="vertical-align:top;" href="'.xtc_href_link(FILENAME_CATEGORIES, xtc_get_all_get_params(array('search'))).'">'.BUTTON_RESET.'</a>';
            }
            ?>
            </form>
          </div>
        </div>
      </div>
      <div class="clear"></div>
      
      <script type="text/javascript">
        $("#deleteall").click(function() {
          var checked = $("#deleteall").is(':checked');
          $('.delete').attr('checked', checked);
        });
      </script>
    
      <?php
      // search results
      if ($_GET['search']) {
        echo xtc_draw_form('product_search', FILENAME_CATEGORIES, xtc_get_all_get_params(array('search', 'special')), 'POST', $confirm_submit).PHP_EOL;
        echo xtc_draw_hidden_field('special', 'add_entries').PHP_EOL;
        ?>
        <br>
        <table class="tableBoxCenter collapse">
          <tr>
            <td class="dataTableHeadingContent" align="center" style="width:4%"><?php echo HEADING_ADD.'<br/>'.xtc_draw_checkbox_field('addall', '', false, '', 'id="addall"'); ?></td>
            <td class="dataTableHeadingContent" style="width:10%"><?php echo HEADING_GROUP; ?></td>
            <?php
            if( USE_ADMIN_THUMBS_IN_LIST=='true' ) { ?>
              <td class="dataTableHeadingContent" style="width:5%"><?php echo HEADING_IMAGE; ?></td>
              <?php
            }
            ?>
            <td class="dataTableHeadingContent" style="width:10%"><?php echo HEADING_MODEL; ?></td>
            <td class="dataTableHeadingContent" style="width:34%"><?php echo HEADING_NAME; ?></td>
            <td class="dataTableHeadingContent" style="width:<?php echo (( USE_ADMIN_THUMBS_IN_LIST=='true' ) ? '37%' : '42%'); ?>"><?php echo HEADING_CATEGORY; ?></td>
          </tr>
          <?php
            $keywords = $_GET['search'] = !empty($_GET['search']) ? stripslashes(trim(urldecode($_GET['search']))) : false;

            $from_str = ''; 
            if (SEARCH_IN_MANU == 'true') {
              $from_str .= " LEFT OUTER JOIN ".TABLE_MANUFACTURERS." AS m ON (p.manufacturers_id = m.manufacturers_id) ";
            }
    
            if (SEARCH_IN_FILTER == 'true') {
              $from_str .= " LEFT JOIN ".TABLE_PRODUCTS_TAGS." pt ON (pt.products_id = p.products_id)
                             LEFT JOIN ".TABLE_PRODUCTS_TAGS_VALUES." ptv ON (ptv.options_id = pt.options_id AND ptv.values_id = pt.values_id AND ptv.status = '1' AND ptv.languages_id = '".(int)$_SESSION['languages_id']."') ";
            }

            if (SEARCH_IN_ATTR == 'true') {
              $from_str .= " LEFT JOIN ".TABLE_PRODUCTS_ATTRIBUTES." AS pa ON (p.products_id = pa.products_id) 
                             LEFT JOIN ".TABLE_PRODUCTS_OPTIONS_VALUES." AS pov ON (pa.options_values_id = pov.products_options_values_id) ";
            }
    
            $where_str = " WHERE p.products_id NOT IN (
                                    SELECT xsell_id
                                      FROM ".TABLE_PRODUCTS_XSELL."
                                     WHERE products_id = ".(int)$_GET['current_product_id']."
                                    )";
            $where_str .= " AND p.products_id != ".(int)$_GET['current_product_id'];

            if ($keywords) {
              require_once (DIR_FS_INC.'xtc_parse_search_string.inc.php');
              $keywordcheck = xtc_parse_search_string($_GET['search'], $search_keywords);

              if ($keywordcheck) {
                include(DIR_FS_CATALOG.DIR_WS_INCLUDES.'build_search_query.php');
                $where_str .= " ) GROUP BY p.products_id";
              }
            }
    
            $search_query_raw = "SELECT p.products_id,
                                        p.products_model,
                                        p.products_ean,
                                        p.products_quantity,
                                        p.products_image,
                                        p.products_price,
                                        p.products_discount_allowed,
                                        p.products_tax_class_id,
                                        p.products_date_available,
                                        p.products_status,
                                        s.specials_quantity,
                                        s.specials_new_products_price,
                                        s.expires_date,
                                        pd.products_name                                         
                                   FROM " . TABLE_PRODUCTS . " p
                                   JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                        ON p.products_id = pd.products_id 
                                           AND pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                              LEFT JOIN " . TABLE_SPECIALS . " s
                                        ON p.products_id = s.products_id 
                                           AND s.status = 1 
                                           AND (now() >= s.start_date OR s.start_date IS NULL)                      
                                        ".$from_str."
                                        ".$where_str."
                               ORDER BY pd.products_name";
            
            $search_query = xtc_db_query($search_query_raw);

            while ($search_data = xtc_db_fetch_array($search_query)) {
              $categorie_query = xtc_db_query("SELECT categories_id
                                                 FROM ".TABLE_PRODUCTS_TO_CATEGORIES."
                                                WHERE products_id='".$search_data['products_id']."'
                                                LIMIT 0,1");
              $categorie_data = xtc_db_fetch_array($categorie_query);
              ?>
              <tr>
                <td class="categories_view_data"><?php echo xtc_draw_checkbox_field('ids[]', $search_data['products_id'], false, '', 'class="add"'); ?></td>
                <td class="categories_view_data txta-l"><?php echo xtc_draw_pull_down_menu('group_name['.$search_data['products_id'].']',$cross_sell_groups); ?></td>
                <?php
                if( USE_ADMIN_THUMBS_IN_LIST=='true' ) { ?>
                 <td class="categories_view_data txta-l">
                   <?php
                   echo xtc_product_thumb_image($search_data['products_image'], $search_data['products_name'], '','',$admin_thumbs_size);
                   ?>
                 </td>
                 <?php
                }
                ?>
                <td class="categories_view_data txta-l"><?php echo $search_data['products_model']; ?></td>
                <td class="categories_view_data txta-l"><?php echo $search_data['products_name']; ?></td>
                <td class="categories_view_data txta-l"><?php echo buildCAT($categorie_data['categories_id']); ?> </td>
              </tr>
              <?php
            }
            ?>
          </table>
          <div class="mrg5 txta-r">
            <input type="submit" class="button" value="<?php echo BUTTON_SAVE; ?>" <?php echo $confirm_save_entry;?>>
          </div>
        </form>
        <script type="text/javascript">
          $("#addall").click(function() {
            var checked = $("#addall").is(':checked');
            $('.add').attr('checked', checked);
          });
        </script>
        <?php
      } 
?>