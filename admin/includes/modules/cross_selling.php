<?php
/* --------------------------------------------------------------
   $Id: cross_selling.php 5201 2013-07-22 11:57:22Z Tomcraft $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2006 XT-Commerce (cross_selling.php 799 2005-02-23)

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  $confirm_save_entry = ' onclick="ButtonClicked(this);"';
  $confirm_submit = defined('CONFIRM_SAVE_ENTRY') && CONFIRM_SAVE_ENTRY == 'true' ? ' onsubmit="return confirmSubmit(\'\',\''. SAVE_ENTRY .'\',this)"' : '';

  // select article data
  $article_query = "SELECT products_name
                      FROM ".TABLE_PRODUCTS_DESCRIPTION."
                     WHERE products_id='".(int) $_GET['current_product_id']."'
                       AND language_id = '".(int)$_SESSION['languages_id']."'";
  $article_data = xtc_db_fetch_array(xtc_db_query($article_query));
  $cross_sell_groups = xtc_get_cross_sell_groups();

  function buildCAT($catID) {
    $cat = array ();
    $tmpID = $catID;

    while (getParent($catID) != 0 || $catID != 0) {
      $cat_select = xtc_db_query("SELECT categories_name
                                    FROM ".TABLE_CATEGORIES_DESCRIPTION."
                                   WHERE categories_id='".$catID."'
                                     AND language_id='".(int)$_SESSION['languages_id']."'");
      $cat_data = xtc_db_fetch_array($cat_select);
      $catID = getParent($catID);
      $cat[] = $cat_data['categories_name'];
    }
    $catStr = '';
    for ($i = count($cat); $i > 0; $i --) {
      $catStr .= $cat[$i -1].' > ';
    }
    return $catStr;
  }

  function getParent($catID) {
    $parent_query = xtc_db_query("SELECT parent_id FROM ".TABLE_CATEGORIES." WHERE categories_id='".$catID."'");
    $parent_data = xtc_db_fetch_array($parent_query);
    return $parent_data['parent_id'];
  }
  ?>
    <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_news.png'); ?></div>
    <div class="pageHeading"><?php echo CROSS_SELLING_1; ?></div>
    <div class="main pdg2 flt-l"><?php echo CROSS_SELLING_2 . ' "' . $article_data['products_name'] . '"'; ?></div>
    <div class="clear mrg5">
      <a class="button" onClick="this.blur()" href="<?php echo xtc_href_link(FILENAME_CATEGORIES,'cPath='.$_GET['cpath'].'&pID='.$_GET['current_product_id']); ?>"><?php echo BUTTON_BACK; ?></a>
    </div>

      <?php
      echo xtc_draw_form('cross_selling', FILENAME_CATEGORIES, '', 'GET', '');
        echo xtc_draw_hidden_field('action', 'edit_crossselling');
        echo xtc_draw_hidden_field('special', 'edit');
        echo xtc_draw_hidden_field('current_product_id', $_GET['current_product_id']);
        echo xtc_draw_hidden_field('cpath', $_GET['cpath']);
        ?>
        <table class="tableBoxCenter collapse">
          <tr>
            <td class="dataTableHeadingContent" align="center" style="width:4%"><?php echo HEADING_DEL.'<br/>'.xtc_draw_checkbox_field('deleteall', '', false, '', 'id="deleteall"'); ?></td>
            <td class="dataTableHeadingContent" style="width:5%"><?php echo HEADING_SORTING; ?></td>
            <td class="dataTableHeadingContent" style="width:5%"><?php echo HEADING_GROUP; ?></td>
            <td class="dataTableHeadingContent" style="width:10%"><?php echo HEADING_MODEL; ?></td>
            <td class="dataTableHeadingContent" style="width:34%"><?php echo HEADING_NAME; ?></td>
            <td class="dataTableHeadingContent" style="width:42%"><?php echo HEADING_CATEGORY; ?></td>
          </tr>
        <?php
          $cross_query = "SELECT cs.ID,cs.products_id,
                                 pd.products_name,
                                 cs.sort_order,
                                 p.products_model,
                                 p.products_id,
                                 cs.products_xsell_grp_name_id
                            FROM ".TABLE_PRODUCTS_XSELL." cs,
                                 ".TABLE_PRODUCTS_DESCRIPTION." pd,
                                 ".TABLE_PRODUCTS." p
                           WHERE cs.products_id = '".(int) $_GET['current_product_id']."'
                             AND cs.xsell_id = p.products_id
                             AND p.products_id = pd.products_id
                             AND pd.language_id = '".(int)$_SESSION['languages_id']."'
                        ORDER BY cs.sort_order";
          $cross_query = xtc_db_query($cross_query);
          if (!xtc_db_num_rows($cross_query)) {
            ?>
            <tr>
              <td class="categories_view_data" colspan="6">- NO ENRTY -</td>
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
              <td class="categories_view_data txta-l"><?php echo $cross_data['products_model']; ?></td>
              <td class="categories_view_data txta-l"><?php echo $cross_data['products_name']; ?></td>
              <td class="categories_view_data txta-l"><?php echo buildCAT($categorie_data['categories_id']); ?> </td>
            </tr>
            <?php 
          } 
          ?>
        </table>
        <div class="mrg5">
          <input type="submit" class="button" value="<?php echo BUTTON_SAVE; ?>" onclick="return confirm('<?php echo SAVE_ENTRY; ?>')">
        </div>
      </form>

      <hr>
      <div class="pageHeading pdg2"><?php echo CROSS_SELLING_SEARCH; ?></div>
      <?php
        echo xtc_draw_form('product_search', FILENAME_CATEGORIES, '', 'GET').PHP_EOL;
        echo xtc_draw_hidden_field('action', 'edit_crossselling').PHP_EOL;
        echo xtc_draw_hidden_field('current_product_id', $_GET['current_product_id']).PHP_EOL;
        echo xtc_draw_hidden_field('cpath', $_GET['cpath']).PHP_EOL;
      ?>
      <div class="main pdg2 flt-l"><?php echo xtc_draw_input_field('search', '', 'size="30"');?></div>
      <div class="main pdg2 flt-l">
      <?php
        echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_SEARCH . '"/>';
      ?>
      </div>
      </form>
      <div class="clear"></div>
      <script type="text/javascript">
        $("#deleteall").click(function() {
          var checked = $("#deleteall").is(':checked');
          $('.delete').attr('checked', checked);
        });
      </script>
      
      <hr>
      <?php
      // search results
      if ($_GET['search']) {
        echo xtc_draw_form('product_search', FILENAME_CATEGORIES, '', 'GET', $confirm_submit).PHP_EOL;
        echo xtc_draw_hidden_field('action', 'edit_crossselling').PHP_EOL;
        echo xtc_draw_hidden_field('special', 'add_entries').PHP_EOL;
        echo xtc_draw_hidden_field('current_product_id', $_GET['current_product_id']).PHP_EOL;
        echo xtc_draw_hidden_field('cpath', $_GET['cpath']).PHP_EOL;
        ?>
        <table class="tableBoxCenter collapse">
          <tr>
            <td class="dataTableHeadingContent" align="center" style="width:4%"><?php echo HEADING_ADD.'<br/>'.xtc_draw_checkbox_field('addall', '', false, '', 'id="addall"'); ?></td>
            <td class="dataTableHeadingContent" style="width:10%"><?php echo HEADING_GROUP; ?></td>
            <td class="dataTableHeadingContent" style="width:10%"><?php echo HEADING_MODEL; ?></td>
            <td class="dataTableHeadingContent" style="width:34%"><?php echo HEADING_NAME; ?></td>
            <td class="dataTableHeadingContent" style="width:42%"><?php echo HEADING_CATEGORY; ?></td>
          </tr>
          <?php
/*
            $search_query = "SELECT * 
                               FROM ".TABLE_PRODUCTS_DESCRIPTION." AS pd,
                                    ".TABLE_PRODUCTS." AS p
                              WHERE p.products_id=pd.products_id
                                AND pd.language_id='".$_SESSION['languages_id']."'
                                AND p.products_id!='".$_GET['current_product_id']."'
                                AND (pd.products_name LIKE '%".$_GET['search']."%' OR p.products_model LIKE '%".$_GET['search']."%')";
            $search_query = xtc_db_query($search_query);
*/
            include(DIR_FS_INC . 'xtc_parse_search_string.inc.php');
            define(ADMIN_SEARCH_IN_ATTR, true); // true = search in attributes
            define(ADMIN_SEARCH_IN_DESC, false); // true = search in description
            //build query
            $select_str = "SELECT DISTINCT p.products_tax_class_id,
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
                                           p.products_startpage_sort";

            $from_str  = " FROM ".TABLE_PRODUCTS." AS p ";
            $from_str .= "LEFT JOIN ".TABLE_PRODUCTS_DESCRIPTION." AS pd ON (p.products_id = pd.products_id) ";
            if (ADMIN_SEARCH_IN_ATTR == 'true') {
              $from_str .= "LEFT OUTER JOIN ".TABLE_PRODUCTS_ATTRIBUTES." AS pa ON (p.products_id = pa.products_id) ";
              $from_str .= "LEFT OUTER JOIN ".TABLE_PRODUCTS_OPTIONS_VALUES." AS pov ON (pa.options_values_id = pov.products_options_values_id) ";
            }
            //where-string
            $where_str = " WHERE p.products_id NOT IN (
                                    SELECT xsell_id
                                      FROM ".TABLE_PRODUCTS_XSELL."
                                     WHERE products_id = ".(int)$_GET['current_product_id']."
                                    )";
            $where_str .= " AND p.products_id != ".(int)$_GET['current_product_id'];
            $where_str .= " AND pd.language_id = '".(int) $_SESSION['languages_id']."'";

            //$where_str = '';
            //where-string
            //$where_str = " WHERE pd.language_id = '".(int) $_SESSION['languages_id']."'";
            //go for keywords... this is the main search process
            if (isset ($_GET['search']) && xtc_not_null($_GET['search'])) {
              if (xtc_parse_search_string(stripslashes($_GET['search']), $search_keywords)) {
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
                $where_str .= " ) GROUP BY p.products_id";
              }
            }
            $search_query = xtc_db_query($select_str.$from_str.$where_str);

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
                <td class="categories_view_data txta-l"><?php echo $search_data['products_model']; ?></td>
                <td class="categories_view_data txta-l"><?php echo $search_data['products_name']; ?></td>
                <td class="categories_view_data txta-l"><?php echo buildCAT($categorie_data['categories_id']); ?> </td>
              </tr>
              <?php
            }
            ?>
          </table>
          <div class="mrg5">
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