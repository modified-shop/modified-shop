<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  $pageTitle = TITLE_EDIT.': ' . xtc_get_products_name($_GET['current_product_id']);

  $noStylingClass = '';
  $noStyling = '';
  if (!defined('NEW_ATTRIBUTES_STYLING') || (defined('NEW_ATTRIBUTES_STYLING') && NEW_ATTRIBUTES_STYLING != 'true')) {
    $noStylingClass = ' class="noStyling" ';
    $noStyling = ' noStyling';
  }

  if (isset($_GET['option_order_by']) && $_GET['option_order_by']) {
    $option_order_by = $_GET['option_order_by'];
    $_POST['current_product_id'] = (int)$_GET['current_product_id'];
  } else {
    $option_order_by = 'sort_order,options_id,options_name';
  }
  $options = array();
  $options[] = (array ('id' => 'sort_order', 'text' => TEXT_SORTORDER));
  $options[] = (array ('id' => 'options_id', 'text' => TEXT_OPTION_ID));
  $options[] = (array ('id' => 'options_name', 'text' => TEXT_OPTION_NAME));
  $options_dropdown_order = xtc_draw_pull_down_menu('selected', $options, $option_order_by, $noStylingClass.'onchange="go_option()" ') ."\n";

  $countOptions = $countValues = 0;
  $module_content = array();
  $options_query = xtc_db_query("SELECT *
                                   FROM " . TABLE_PRODUCTS_TAGS_OPTIONS . "
                                  WHERE languages_id = '".(int)$_SESSION['languages_id']."'
                                    AND (filter = '1' OR status = '1')
                               ORDER BY ". $option_order_by);

  $optFlag = false;
  if (xtc_db_num_rows($options_query) > 0) {
    while ($options = xtc_db_fetch_array($options_query)) {
      $values_query = xtc_db_query("SELECT *
                                      FROM " . TABLE_PRODUCTS_TAGS_VALUES . "
                                     WHERE options_id = '".$options['options_id']."'
                                       AND languages_id = '".(int)$_SESSION['languages_id']."'
                                  ORDER BY sort_order, values_name, values_description");

      if (xtc_db_num_rows($values_query) > 0) {        
        $module_values_content = array();
        $flag = false;
        while ($values = xtc_db_fetch_array($values_query)) {
          $data_array = xtc_get_tags_status((int)$_GET['current_product_id'], $options['options_id'], $values['values_id']);
          $is_checked = ((count($data_array ) > 0) ? true : false);
          $flag = ($is_checked ? true : $flag);
          $optFlag = ($is_checked ? true : $optFlag);
          $module_values_content[] = array(
            'checkbox' => xtc_draw_checkbox_field('product_tags['.$options['options_id'].']['.$values['values_id'].']', 'on', $is_checked, '', 'class="cbx_optval cb check_'.$options['options_id'].$noStyling.'"'),
            'sort_order' => xtc_draw_input_field('product_tags_sort['.$options['options_id'].']['.$values['values_id'].']', $data_array['sort_order'], 'size="8"'),
            'title' => (($values['values_name'] != '') ? $values['values_name'] : $values['values_description'])
          );
          
          $countValues ++;
        }                        
        $module_content[] = array(
          'id' => $options['options_id'],
          'text' => (($options['options_name'] != '') ? $options['options_name'] : $options['options_description']),
          'content' => $module_values_content,
          'flag' => ($flag ? ' flag' : '')
        );
      }
      
      $countOptions ++;
    }
  }
  $optFlag = $optFlag ? 'optFlag' : '';

  function xtc_get_tags_status($products_id, $options_id, $values_id) {
    $tags = array();
    $tags_query = xtc_db_query("SELECT *
                                  FROM ".TABLE_PRODUCTS_TAGS."
                                 WHERE products_id = '".$products_id."'
                                   AND options_id = '".$options_id."'
                                   AND values_id = '".$values_id."'");
    if (xtc_db_num_rows($tags_query) > 0) {
      $tags = xtc_db_fetch_array($tags_query);
    }

    return $tags;
  }

  function rowClass($i) {
    $class1 = 'attributes-odd';
    $class2 = 'attributes-even';
    if ($i%2) {
      return $class1;
    } else {
     return $class2;
    }
  }

require (DIR_WS_INCLUDES.'head.php');
?>
  <link rel="stylesheet" type="text/css" href="includes/css/new_attributes.css">
  <script type="text/javascript" src="includes/javascript/jquery.new_attributes.js"></script>
  <script type="text/javascript" src="includes/general.js"></script>
  <script type="text/javascript">
    <!--
    function go_option() {
      if (document.option_order_by.selected.options[document.option_order_by.selected.selectedIndex].value != "none") {
        location = "<?php echo xtc_href_link(FILENAME_PRODUCTS_TAGS, xtc_get_all_get_params(array('option_order_by')).'option_page=' . (isset($_GET['option_page']) ? (int)$_GET['option_page'] : 1)); ?>&option_order_by="+document.option_order_by.selected.options[document.option_order_by.selected.selectedIndex].value;
      }
    }
    //-->
  </script>
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
          <tr>
            <td>
     
              <div class="pageHeading pdg2"><?php echo $pageTitle; ?></div>
              <div class="main pdg2">
                <?php echo SORT_ORDER;
                echo xtc_draw_form('option_order_by', FILENAME_PRODUCTS_TAGS, '', 'post');
                echo $options_dropdown_order; 
                ?>
                </form>
              </div>

              <?php 
              echo xtc_draw_form('SUBMIT_ATTRIBUTES', FILENAME_PRODUCTS_TAGS . str_replace('&','?',$iframe), '', 'post', 'id="SUBMIT_ATTRIBUTES"'); ?>
              <input type="hidden" name="current_product_id" value="<?php echo (int)$_POST['current_product_id']; ?>">
              <input type="hidden" name="action" value="change">
              <?php
                echo '<input type="hidden" name="option_order_by" value="' . $option_order_by . '">';
                $_POST['cpath'] = isset($_GET['cpath']) ? $_GET['cpath'] : (isset($_POST['cpath']) ? $_POST['cpath']: '') ;
                if ($_POST['cpath'] != '') {
                  $param ='cPath='. $_POST['cpath'] . '&current_product_id='. (int)$_POST['current_product_id'] . $oldaction.$oldpage ;
                  echo '<input type="hidden" name="cpath" value="' . $_POST['cpath'] . '">';
                  echo '<input type="hidden" name="oldaction" value="' . str_replace('&oldaction=','',$oldaction) . '">';
                  echo '<input type="hidden" name="page" value="' . str_replace('&page=','',$oldpage) . '">';
                } else {
                  $param = '';
                }
              ?>

              <div class="main" style="margin:10px 0;">
                <a class="button button_save" style="display:none;"><?php echo ATTR_SAVE_ACTIVE;?></a>
                <?php
                  echo xtc_button(BUTTON_SAVE,'submit','name="button_submit"');
                  if (!isset($_GET['iframe'])) {
                    echo '&emsp;' . xtc_button_link(BUTTON_BACK, xtc_href_link(FILENAME_PRODUCTS_TAGS, $param));
                  }
                ?>
              </div>

              <div id="attributes">
              <?php
                if ($countOptions > 0) {
                  for ($i = 0; $i < $countOptions; $i++) {
                    if (is_array($module_content[$i]['content'])) {
                      $output = '<div style="margin-bottom:20px;clear:both;">';
                      $output .= '<table id="attrtable-'.$module_content[$i]['id'].'" class="attributes collapse">'. PHP_EOL;
                      $output .= '<thead>'. PHP_EOL;
                      $output .= '<tr id="oid-' . $module_content[$i]['id'] . '" class="dataTableHeadingRow">'. PHP_EOL;
                      $output .= '<th class="dataTableHeadingContent txta-l nobr" style="width:150px;">'.xtc_draw_checkbox_field('set_'.$module_content[$i]['id'], $module_content[$i]['id'], false, '', 'class="select_all'.$noStyling.'"' .' disabled="disabled"').'&nbsp;&nbsp;<strong style="padding-right:10px;">' . $module_content[$i]['text'] . '</strong></th>'. PHP_EOL;
                      $output .= '<th class="dataTableHeadingContent" style="width:95px"><strong>'.SORT_ORDER.'</strong></th>'. PHP_EOL;
                      $output .= '</tr>'. PHP_EOL;
                      $output .= '</thead>'. PHP_EOL;
                      $output .= '<tbody>'. PHP_EOL;

                      $c = 0;
                      $disable = (($module_content[$i]['flag'] == '') ? ' disabled="true" ' : ' ');
                      foreach ($module_content[$i]['content'] as $content) {

                        $output .= '<tr class="' . rowClass($c) . ' oid-' . $module_content[$i]['id'] . '">'. PHP_EOL;
                        $output .= '<td class="main nobr">'. PHP_EOL;
                        $output .= $content['checkbox'] . ' ' . $content['title']. PHP_EOL;
                        $output .= '</td>'. PHP_EOL;
                        $output .= '<td class="main nobr">'. PHP_EOL;
                        $output .= $content['sort_order'] . PHP_EOL;
                        $output .= '</td>'. PHP_EOL;
                        $output .= '</tr>'. PHP_EOL;

                        $c ++;
                      }

                      $output .= '</tbody>'. PHP_EOL;
                      $output .= '</table></div>'. PHP_EOL;

                      if ($module_content[$i]['flag'] != '') {
                        $output = str_replace('dataTableHeadingContent', 'dataTableHeadingContent attr-chk', $output);
                      }
                      echo $output;

                    }
                  }
                }
                echo isset($_GET['options_id']) ? '<input type="hidden" name="get_options_id" value="'.$_GET['options_id'].'">'. PHP_EOL : '';
                
                echo '<div class="pdg2"><small>Options: ' . $countOptions . ' | Values: ' . $countValues . '</small></div>';
              ?>
              </div>

              <div class="main" style="margin:10px 0;">
                <a class="button button_save" style="display:none;"><?php echo ATTR_SAVE_ACTIVE;?></a>
                <?php
                  echo xtc_button(BUTTON_SAVE,'submit','name="button_submit"');
                  if (!isset($_GET['iframe'])) {
                    echo '&emsp;' . xtc_button_link(BUTTON_BACK, xtc_href_link(FILENAME_PRODUCTS_TAGS, $param));
                  }
                ?>
              </div>
              </form>

            </td>
          </tr>
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