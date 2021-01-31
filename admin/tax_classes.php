<?php
/* --------------------------------------------------------------
   $Id$   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(tax_classes.php,v 1.19 2002/03/17); www.oscommerce.com 
   (c) 2003	 nextcommerce (tax_classes.php,v 1.9 2003/08/18); www.nextcommerce.org

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

  require('includes/application_top.php');

  // include needed function
  require_once(DIR_FS_INC.'parse_multi_language_value.inc.php');

  // set languages
  $languages = xtc_get_languages();
  
  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  $page = (isset($_GET['page']) ? (int)$_GET['page'] : 1);
  
  if (xtc_not_null($action)) {
    switch ($action) {
      case 'insert':
        $tax_class_title = xtc_db_prepare_input($_POST['tax_class_title']);
        $tax_class_description = xtc_db_prepare_input($_POST['tax_class_description']);

        $tax_class_title_array = array();
        foreach ($tax_class_title as $key => $value) {
          if (xtc_not_null($value)) {
            $tax_class_title_array[] =  $key . '::' . $value;
          }
        }
        $tax_class_title = implode('||', $tax_class_title_array);

        $tax_class_description_array = array();
        foreach ($tax_class_description as $key => $value) {
          if (xtc_not_null($value)) {
            $tax_class_description_array[] =  $key . '::' . $value;
          }
        }
        $tax_class_description = implode('||', $tax_class_description_array);

        $sql_data_array = array(
          'tax_class_title' => $tax_class_title,
          'tax_class_description' => $tax_class_description,
          'date_added' => 'now()',
        );
        xtc_db_perform(TABLE_TAX_CLASS, $sql_data_array);
        $tax_class_id = xtc_db_insert_id();
        
        xtc_redirect(xtc_href_link(FILENAME_TAX_CLASSES, 'tID=' . $tax_class_id));
        break;

      case 'save':
        $tax_class_id = (int)$_GET['tID'];
        $tax_class_title = xtc_db_prepare_input($_POST['tax_class_title']);
        $tax_class_description = xtc_db_prepare_input($_POST['tax_class_description']);

        $tax_class_title_array = array();
        foreach ($tax_class_title as $key => $value) {
          if (xtc_not_null($value)) {
            $tax_class_title_array[] =  $key . '::' . $value;
          }
        }
        $tax_class_title = implode('||', $tax_class_title_array);

        $tax_class_description_array = array();
        foreach ($tax_class_description as $key => $value) {
          if (xtc_not_null($value)) {
            $tax_class_description_array[] =  $key . '::' . $value;
          }
        }
        $tax_class_description = implode('||', $tax_class_description_array);

        $sql_data_array = array(
          'tax_class_title' => $tax_class_title,
          'tax_class_description' => $tax_class_description,
          'date_added' => 'now()',
        );
        xtc_db_perform(TABLE_TAX_CLASS, $sql_data_array, 'update', "tax_class_id = '" . $tax_class_id . "'");

        xtc_redirect(xtc_href_link(FILENAME_TAX_CLASSES, 'page=' . (int)$page . '&tID=' . $tax_class_id));
        break;

      case 'deleteconfirm':
        $tax_class_id = (int)$_GET['tID'];

        xtc_db_query("DELETE FROM " . TABLE_TAX_CLASS . " WHERE tax_class_id = '" . $tax_class_id . "'");
        xtc_redirect(xtc_href_link(FILENAME_TAX_CLASSES, 'page=' . (int)$page));
        break;
    }
  }
  
  require (DIR_WS_INCLUDES.'head.php');
?>
<script type="text/javascript" src="includes/general.js"></script>
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
        <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_configuration.png'); ?></div>
        <div class="pageHeading"><?php echo HEADING_TITLE; ?></div>       
        <div class="main pdg2 flt-l">Configuration</div>
        <table class="tableCenter">
          <tr>
            <td class="boxCenterLeft">
              <table class="tableBoxCenter collapse">
                <tr class="dataTableHeadingRow">
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TAX_CLASSES; ?></td>
                  <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
                </tr>
                <?php
                $classes_query_raw = "SELECT *
                                        FROM " . TABLE_TAX_CLASS . " 
                                    ORDER BY tax_class_title";
                $classes_split = new splitPageResults($page, '20', $classes_query_raw, $classes_query_numrows);
                $classes_query = xtc_db_query($classes_query_raw);
                while ($classes = xtc_db_fetch_array($classes_query)) {
                  if ((!isset($_GET['tID']) || $_GET['tID'] == $classes['tax_class_id']) && !isset($tcInfo) && (substr($action, 0, 3) != 'new')) {
                    $tcInfo = new objectInfo($classes);
                  }

                  if (isset($tcInfo) && is_object($tcInfo) && $classes['tax_class_id'] == $tcInfo->tax_class_id) {
                    echo '<tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_TAX_CLASSES, 'page=' . $page . '&tID=' . $tcInfo->tax_class_id . '&action=edit') . '\'">' . "\n";
                  } else {
                    echo'<tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_TAX_CLASSES, 'page=' . $page . '&tID=' . $classes['tax_class_id']) . '\'">' . "\n";
                  }
                ?>
                  <td class="dataTableContent"><?php echo parse_multi_language_value($classes['tax_class_title'], $_SESSION['language_code']); ?></td>
                  <td class="dataTableContent txta-r"><?php if (isset($tcInfo) && is_object($tcInfo) && $classes['tax_class_id'] == $tcInfo->tax_class_id) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_TAX_CLASSES, 'page=' . $page . '&tID=' . $classes['tax_class_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
                </tr>
                  <?php
                    }
                  ?>
              </table>
   
              <div class="smallText pdg2 flt-l"><?php echo $classes_split->display_count($classes_query_numrows, '20', $page, TEXT_DISPLAY_NUMBER_OF_TAX_CLASSES); ?></div>  
              <div class="smallText pdg2 flt-r"><?php echo $classes_split->display_links($classes_query_numrows, '20', MAX_DISPLAY_PAGE_LINKS, $page); ?></div>  

              <?php
              if (!xtc_not_null($action)) {
              ?>
                <div class="clear"></div>  
                <div class="smallText pdg2 flt-r"><?php echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_TAX_CLASSES, 'page=' . $page . '&action=new') . '">' . BUTTON_NEW_TAX_CLASS . '</a>'; ?></div>  
              <?php
              }
              ?>
            </td>
            <?php
            $heading = array();
            $contents = array();
            switch ($action) {
              case 'new':
                $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_TAX_CLASS . '</b>');

                $contents = array('form' => xtc_draw_form('classes', FILENAME_TAX_CLASSES, 'page=' . $page . '&action=insert'));
                $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);

                $tax_class_title = '';
                $tax_class_description = '';
                for ($i=0, $n=count($languages); $i<$n; $i++) {
                  $tax_class_title .= xtc_image(DIR_WS_LANGUAGES . $languages[$i]['directory'] .'/admin/images/'. $languages[$i]['image'], $languages[$i]['name'], '18px');
                  $tax_class_title .= xtc_draw_input_field('tax_class_title[' . strtoupper($languages[$i]['code']) . ']', '', 'style="margin-left:2px; width:200px;"').'<br>';
                
                  $tax_class_description .= xtc_image(DIR_WS_LANGUAGES . $languages[$i]['directory'] .'/admin/images/'. $languages[$i]['image'], $languages[$i]['name'], '18px');
                  $tax_class_description .= xtc_draw_input_field('tax_class_description[' . strtoupper($languages[$i]['code']) . ']', '', 'style="margin-left:2px; width:200px;"').'<br>';
                }
                $contents[] = array('text' => '<br />' . TEXT_INFO_CLASS_TITLE . '<br />' . $tax_class_title);
                $contents[] = array('text' => '<br />' . TEXT_INFO_CLASS_DESCRIPTION . '<br />' . $tax_class_description);
                $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_INSERT . '"/>&nbsp;<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_TAX_CLASSES, 'page=' . $page) . '">' . BUTTON_CANCEL . '</a>');
                break;

              case 'edit':
                $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_TAX_CLASS . '</b>');

                $contents = array('form' => xtc_draw_form('classes', FILENAME_TAX_CLASSES, 'page=' . $page . '&tID=' . $tcInfo->tax_class_id . '&action=save'));
                $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);

                $tax_class_title = '';
                $tax_class_description = '';
                for ($i=0, $n=count($languages); $i<$n; $i++) {
                  $tax_class_title .= xtc_image(DIR_WS_LANGUAGES . $languages[$i]['directory'] .'/admin/images/'. $languages[$i]['image'], $languages[$i]['name'], '18px');
                  $tax_class_title .= xtc_draw_input_field('tax_class_title[' . strtoupper($languages[$i]['code']) . ']', parse_multi_language_value($tcInfo->tax_class_title, $languages[$i]['code'], true), 'style="margin-left:2px; width:200px;"').'<br>';
                
                  $tax_class_description .= xtc_image(DIR_WS_LANGUAGES . $languages[$i]['directory'] .'/admin/images/'. $languages[$i]['image'], $languages[$i]['name'], '18px');
                  $tax_class_description .= xtc_draw_input_field('tax_class_description[' . strtoupper($languages[$i]['code']) . ']', parse_multi_language_value($tcInfo->tax_class_description, $languages[$i]['code'], true), 'style="margin-left:2px; width:200px;"').'<br>';
                }
                $contents[] = array('text' => '<br />' . TEXT_INFO_CLASS_TITLE . '<br />' . $tax_class_title);
                $contents[] = array('text' => '<br />' . TEXT_INFO_CLASS_DESCRIPTION . '<br />' . $tax_class_description);
                $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_UPDATE . '"/>&nbsp;<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_TAX_CLASSES, 'page=' . $page . '&tID=' . $tcInfo->tax_class_id) . '">' . BUTTON_CANCEL . '</a>');
                break;

              case 'delete':
                $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_TAX_CLASS . '</b>');

                $contents = array('form' => xtc_draw_form('classes', FILENAME_TAX_CLASSES, 'page=' . $page . '&tID=' . $tcInfo->tax_class_id . '&action=deleteconfirm'));
                $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
                $contents[] = array('text' => '<br /><b>' . parse_multi_language_value($tcInfo->tax_class_title, $_SESSION['language_code']) . '</b>');
                $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"/>&nbsp;<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_TAX_CLASSES, 'page=' . $page . '&tID=' . $tcInfo->tax_class_id) . '">' . BUTTON_CANCEL . '</a>');
                break;

              default:
                if (is_object($tcInfo)) {
                  $heading[] = array('text' => '<b>' . parse_multi_language_value($tcInfo->tax_class_title, $_SESSION['language_code']) . '</b>');

                  $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_TAX_CLASSES, 'page=' . $page . '&tID=' . $tcInfo->tax_class_id . '&action=edit') . '">' . BUTTON_EDIT . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_TAX_CLASSES, 'page=' . $page . '&tID=' . $tcInfo->tax_class_id . '&action=delete') . '">' . BUTTON_DELETE . '</a>');
                  $contents[] = array('text' => '<br />' . TEXT_INFO_DATE_ADDED . ' ' . xtc_date_short($tcInfo->date_added));
                  $contents[] = array('text' => '' . TEXT_INFO_LAST_MODIFIED . ' ' . xtc_date_short($tcInfo->last_modified));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CLASS_DESCRIPTION . '<br />' . parse_multi_language_value($tcInfo->tax_class_description, $_SESSION['language_code']));
                }
                break;
            }
            if ( (xtc_not_null($heading)) && (xtc_not_null($contents)) ) {
              echo '            <td class="boxRight">' . "\n";
              $box = new box;
              echo $box->infoBox($heading, $contents);
              echo '            </td>' . "\n";
            }
            ?>
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