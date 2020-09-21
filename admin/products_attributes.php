<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  require('includes/application_top.php');

  //display per page
  $cfg_max_display_options_key = 'MAX_DISPLAY_NUMBER_OF_OPTIONS';
  $page_max_display_options_results = xtc_cfg_save_max_display_results($cfg_max_display_options_key);

  $cfg_max_display_values_key = 'MAX_DISPLAY_NUMBER_OF_VALUES';
  $page_max_display_values_results = xtc_cfg_save_max_display_results($cfg_max_display_values_key);

  $languages = xtc_get_languages();

  function xtc_get_attributes_values_detail($products_options_values_id, $languages_id, $db_field) {
    $values_query = xtc_db_query("SELECT ".$db_field." 
                                    FROM ".TABLE_PRODUCTS_OPTIONS_VALUES."
                                   WHERE products_options_values_id = '".$products_options_values_id."'
                                     AND language_id = '".$languages_id."'");
    $values = xtc_db_fetch_array($values_query);

    return $values[$db_field];
  }

  function xtc_get_attributes_options_detail($products_options_id, $languages_id, $db_field) {
    $options_query = xtc_db_query("SELECT ".$db_field." 
                                     FROM ".TABLE_PRODUCTS_OPTIONS."
                                    WHERE products_options_id = '".$products_options_id."'
                                      AND language_id = '".$languages_id."'");
    $options = xtc_db_fetch_array($options_query);

    return $options[$db_field];
  }


  function xtc_check_attributes_options($options_id) {
    $products_array = array();
    $products_query = xtc_db_query("SELECT p.products_id,
                                           pd.products_name,
                                           pov.products_options_values_name
                                      FROM " . TABLE_PRODUCTS . " p
                                      JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                           ON pd.products_id = p.products_id
                                              AND pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                                      JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                           ON pa.products_id = p.products_id
                                              AND pa.options_id = '" . (int)$options_id . "'
                                      JOIN " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov
                                           ON pov.products_options_values_id = pa.options_values_id
                                              AND pov.language_id = '" . (int)$_SESSION['languages_id'] . "'
                                  GROUP BY p.products_id
                                  ORDER BY pd.products_name");
    if (xtc_db_num_rows($products_query) > 0) {
      while ($products = xtc_db_fetch_array($products_query)) {
        $products_array[] = $products;
      }
    }

    return $products_array;
  }

  function xtc_check_attributes_values($options_values_id) {
    $products_array = array();
    $products_query = xtc_db_query("SELECT p.products_id,
                                           pd.products_name,
                                           po.products_options_name
                                      FROM " . TABLE_PRODUCTS . " p
                                      JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                           ON pd.products_id = p.products_id
                                              AND pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                                      JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                           ON pa.products_id = p.products_id
                                              AND pa.options_values_id = '" . (int)$options_values_id . "'
                                      JOIN " . TABLE_PRODUCTS_OPTIONS . " po
                                           ON po.products_options_id = pa.options_id
                                              AND po.language_id = '" . (int)$_SESSION['languages_id'] . "'
                                  GROUP BY p.products_id
                                  ORDER BY pd.products_name");
    if (xtc_db_num_rows($products_query) > 0) {
      while ($products = xtc_db_fetch_array($products_query)) {
        $products_array[] = $products;
      }
    }

    return $products_array;
  }


  if (isset($_GET['saction'])) {
    switch ($_GET['saction']) {
  
      case 'insert_values':
        $oID = (int)$_GET['oID'];
        $next_id_query = xtc_db_query("SELECT max(products_options_values_id) as products_options_values_id 
                                         FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . "");
        $next_id = xtc_db_fetch_array($next_id_query);
        $values_id = $next_id['products_options_values_id'] + 1;

        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {     
          $sql_data_array = array('products_options_values_id' => $values_id,
                                  'products_options_values_name' => xtc_db_prepare_input($_POST['products_options_values_name'][$languages[$i]['id']]),
                                  'language_id' => $languages[$i]['id'],
                                  'products_options_values_sortorder' => (int)$_POST['products_options_values_sortorder'],
                                  );
          xtc_db_perform(TABLE_PRODUCTS_OPTIONS_VALUES, $sql_data_array);
        }

        $sql_data_array = array('products_options_id' => $oID,
                                'products_options_values_id' => $values_id,
                                );
        xtc_db_perform(TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS, $sql_data_array);

        xtc_redirect(xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action', 'vID', 'saction')) . 'list=detail&vID=' . $values_id));
        break;

      case 'save_values':
        $vID = (int)$_GET['vID'];
        $oID = (int)$_GET['oID'];
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {     
          $sql_data_array = array('products_options_values_id' => $vID,
                                  'products_options_values_name' => xtc_db_prepare_input($_POST['products_options_values_name'][$languages[$i]['id']]),
                                  'language_id' => $languages[$i]['id'],
                                  'products_options_values_sortorder' => (int)$_POST['products_options_values_sortorder'],
                                  );
          $values_description_query = xtc_db_query("SELECT * 
                                                      FROM ".TABLE_PRODUCTS_OPTIONS_VALUES." 
                                                     WHERE language_id = '".$languages[$i]['id']."' 
                                                       AND products_options_values_id = '".$vID."'");
          if (xtc_db_num_rows($values_description_query) == 0) {
            xtc_db_perform(TABLE_PRODUCTS_OPTIONS_VALUES, $sql_data_array);
          } else {
            xtc_db_perform(TABLE_PRODUCTS_OPTIONS_VALUES, $sql_data_array, 'update', "products_options_values_id = '".$vID."' AND language_id = '".$languages[$i]['id']."'");                    
          }      
        }

        xtc_redirect(xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action', 'saction')) . 'list=detail'));
        break;

      case 'deleteconfirm_values':
        $vID = (int)$_GET['vID'];
        $oID = (int)$_GET['oID'];

        $products_array = xtc_check_attributes_values($vID);
        if (count($products_array) > 0) {
          $messageStack->add_session(TEXT_WARNING_OF_DELETE, 'error');
        } else {
          xtc_db_query("DELETE FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . " WHERE products_options_values_id = '" . $vID . "'");
          xtc_db_query("DELETE FROM " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " WHERE products_options_values_id = '" . $vID . "' AND products_options_id = '" . $oID . "'");
        }

        xtc_redirect(xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action', 'vID', 'saction')) . 'list=detail'));
        break;
    }
  }

  if (isset($_GET['action'])) {
    switch ($_GET['action']) {

      case 'insert_options':
        $next_id_query = xtc_db_query("SELECT max(products_options_id) as products_options_id 
                                         FROM " . TABLE_PRODUCTS_OPTIONS . "");
        $next_id = xtc_db_fetch_array($next_id_query);
        $options_id = $next_id['products_options_id'] + 1;

        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {     
          $sql_data_array = array('products_options_id' => $options_id,
                                  'products_options_name' => xtc_db_prepare_input($_POST['products_options_name'][$languages[$i]['id']]),
                                  'language_id' => $languages[$i]['id'],
                                  'products_options_sortorder' => (int)$_POST['products_options_sortorder'],
                                  );
          xtc_db_perform(TABLE_PRODUCTS_OPTIONS, $sql_data_array);
        }                  

        xtc_redirect(xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action', 'oID', 'search')) . 'oID=' . $options_id));
        break;

      case 'save_options':
        $oID = (int)$_GET['oID'];
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {     
          $sql_data_array = array('products_options_id' => $oID,
                                  'products_options_name' => xtc_db_prepare_input($_POST['products_options_name'][$languages[$i]['id']]),
                                  'language_id' => $languages[$i]['id'],
                                  'products_options_sortorder' => (int)$_POST['products_options_sortorder'],
                                  );
          $options_name_query = xtc_db_query("SELECT * 
                                                FROM ".TABLE_PRODUCTS_OPTIONS." 
                                               WHERE language_id = '".$languages[$i]['id']."' 
                                                 AND products_options_id = '".$oID."'");
          if (xtc_db_num_rows($options_name_query) == 0) {
            xtc_db_perform(TABLE_PRODUCTS_OPTIONS, $sql_data_array);
          } else {
            xtc_db_perform(TABLE_PRODUCTS_OPTIONS, $sql_data_array, 'update', "products_options_id = '".$oID."' AND language_id = '".$languages[$i]['id']."'");                    
          }
        }

        xtc_redirect(xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action'))));
        break;

      case 'deleteconfirm_options':
        $oID = (int)$_GET['oID'];

        $products_array = xtc_check_attributes_options($oID);
        if (count($products_array) > 0) {
          $messageStack->add_session(TEXT_WARNING_OF_DELETE, 'error');
        } else {
          xtc_db_query("DELETE FROM " . TABLE_PRODUCTS_OPTIONS . " WHERE products_options_id = '" . $oID . "'");

          $options_query = xtc_db_query("SELECT products_options_values_id
                                           FROM " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " 
                                          WHERE products_options_id = '" . $oID . "'");
          if (xtc_db_num_rows($options_query) > 0) {
            while ($options = xtc_db_fetch_array($options_query)) {
              xtc_db_query("DELETE FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . " WHERE products_options_values_id = '" . $options['products_options_values_id'] . "'");
              xtc_db_query("DELETE FROM " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " WHERE products_options_values_id = '" . $options['products_options_values_id'] . "' AND products_options_id = '" . $oID . "'");
            }
          }
        }

        xtc_redirect(xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action', 'oID'))));
        break;

    }
  }

  $option_sort = 'po.products_options_sortorder, po.products_options_name';
  $value_sort = 'pov.products_options_values_sortorder, pov.products_options_values_name';

  // get sorting option and switch accordingly
  $sorting = (isset($_GET['sorting']) ? $_GET['sorting'] : '');
  if (xtc_not_null($sorting)) {
    switch ($sorting) {
      case 'id':
        $option_sort = 'po.products_options_id ASC, po.products_options_sortorder';
        $value_sort  = 'pov.products_options_values_id ASC, pov.products_options_values_sortorder';
        break;
      case 'id-desc':
        $option_sort = 'po.products_options_id DESC, po.products_options_sortorder';
        $value_sort  = 'pov.products_options_values_id DESC, pov.products_options_values_sortorder';
        break;

      case 'sort':
        $option_sort = 'po.products_options_sortorder ASC, po.products_options_name';
        $value_sort  = 'pov.products_options_values_sortorder ASC, pov.products_options_values_name';
        break;
      case 'sort-desc':
        $option_sort = 'po.products_options_sortorder DESC, po.products_options_name';
        $value_sort  = 'pov.products_options_values_sortorder DESC, pov.products_options_values_name';
        break;

      case 'name':
        $option_sort = 'po.products_options_name ASC, po.products_options_sortorder';
        $value_sort  = 'pov.products_options_values_name ASC, pov.products_options_values_sortorder';
        break;
      case 'name-desc':
        $option_sort = 'po.products_options_name DESC, po.products_options_sortorder';
        $value_sort  = 'pov.products_options_values_name DESC, pov.products_options_values_sortorder';
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
        <div class="pageHeading"><?php echo ((isset($_GET['list']) && $_GET['list'] == 'detail') ? HEADING_TITLE_VAL : HEADING_TITLE_OPT); ?></div>       
        <div class="main pdg2 flt-l"><?php echo ((isset($_GET['list']) && $_GET['list'] == 'detail') ? xtc_get_attributes_options_detail((int)$_GET['oID'], $_SESSION['languages_id'], 'products_options_name') : 'Configuration'); ?></div>
        <div>
          <div class="main pdg2 flt-l" style="margin-left:100px;margin-top:-20px;height: 25px;min-width: 200px;">&nbsp;
          <?php 
            if (!isset($_GET['saction'])) {
              if (isset($_GET['list']) && $_GET['list'] == 'detail') {
                echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action', 'list', 'spage', 'vID'))) . '">' . BUTTON_BACK . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('saction', 'search', 'vID')) . 'saction=new_value') . '">' . BUTTON_INSERT . '</a>'; 
              } elseif (!isset($_GET['action'])) {
                echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action', 'search')) . 'action=new_option') . '">' . BUTTON_INSERT . '</a>';
              }
            }
            ?>
          </div>
          <div class="main flt-l pdg2 mrg5" style="margin-left:20px;margin-top:-17px;">
            <?php echo xtc_draw_form('search', FILENAME_PRODUCTS_ATTRIBUTES, '', 'get'); ?>
            <?php echo TEXT_INFO_SEARCH . xtc_draw_input_field('search', ((isset($_GET['search']) && $_GET['search'] != '') ? $_GET['search'] : '')) . ((isset($_GET['search']) && $_GET['search'] != '') ? ' <a style="margin-top:-4px;" class="button" href="'.xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('search', 'page', 'spage'))).'">'.BUTTON_RESET.'</a>' : ''); ?>
            </form>
          </div>
        </div>
        <table class="tableCenter">
          <tr>
            <td class="boxCenterLeft">
            <?php
            if (isset($_GET['list']) && $_GET['list'] == 'detail') {
            ?>
            <table class="tableBoxCenter collapse">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ID.xtc_sorting(FILENAME_PRODUCTS_ATTRIBUTES, 'id'); ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SORTORDER.xtc_sorting(FILENAME_PRODUCTS_ATTRIBUTES, 'sort'); ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_OPT_VALUE.xtc_sorting(FILENAME_PRODUCTS_ATTRIBUTES, 'name'); ?></td>
                <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
              <?php
              $values_query_raw = "SELECT pov.*
                                     FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov
                                     JOIN " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " pov2po
                                          ON pov2po.products_options_values_id = pov.products_options_values_id
                                    WHERE pov2po.products_options_id = '".$_GET['oID']."'
                                      AND pov.language_id = '".(int)$_SESSION['languages_id']."'
                                 ORDER BY ".$value_sort;

              $values_split = new splitPageResults($_GET['spage'], $page_max_display_values_results, $values_query_raw, $values_query_numrows);
              $values_query = xtc_db_query($values_query_raw);
              while ($values = xtc_db_fetch_array($values_query)) {
                if (((!$_GET['vID']) || (@$_GET['vID'] == $values['products_options_values_id'])) && (!$vInfo) && (substr($_GET['saction'], 0, 3) != 'new_value')) {
                  $vInfo = new objectInfo($values);
                }
                if ( (is_object($vInfo)) && ($values['products_options_values_id'] == $vInfo->products_options_values_id) ) {
                  echo '<tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action', 'saction', 'vID')) . 'vID=' . $vInfo->products_options_values_id . '&saction=edit_value') . '\'">' . "\n";
                } else {
                  echo '<tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action', 'vID', 'list')) . 'list=detail&vID=' . $values['products_options_values_id']) . '\'">' . "\n";
                }
                ?>
                <td class="dataTableContent" style="width:50px;"><?php echo $values['products_options_values_id']; ?></td>
                <td class="dataTableContent" style="width:50px;"><?php echo $values['products_options_values_sortorder']; ?></td>
                <td class="dataTableContent"><?php echo $values['products_options_values_name']; ?></td>
                <td class="dataTableContent txta-r"><?php if ( (is_object($vInfo)) && ($values['products_options_values_id'] == $vInfo->products_options_values_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('vID', 'action', 'saction', 'list')) . 'list=detail&vID=' . $values['products_options_values_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
              <?php
              }
              ?>
            </table>
  
            <div class="smallText pdg2 flt-l"><?php echo $values_split->display_count($values_query_numrows, $page_max_display_values_results, $_GET['spage'], TEXT_DISPLAY_NUMBER_OF_VALUES); ?></div>
            <div class="smallText pdg2 flt-r"><?php echo $values_split->display_links($values_query_numrows, $page_max_display_values_results, MAX_DISPLAY_PAGE_LINKS, $_GET['spage'], xtc_get_all_get_params(array('page', 'vID', 'action', 'saction', 'list', 'spage')) . 'list=detail', 'spage'); ?></div>
            <div class="clear"></div>
            <?php echo draw_input_per_page($PHP_SELF.'?'.xtc_get_all_get_params(array('spage', 'saction')),$cfg_max_display_values_key,$page_max_display_values_results); ?>
            <div class="smallText pdg2 flt-r"><?php if (!$_GET['saction']) echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action', 'spage', 'vID', 'list'))) . '">' . BUTTON_BACK . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action', 'saction', 'vID', 'list')) . 'list=detail&vID=' . $vInfo->products_options_values_id . '&saction=new_value') . '">' . BUTTON_INSERT . '</a>'; ?></div>
            <?php
            } else {
            ?>
            <table class="tableBoxCenter collapse">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ID.xtc_sorting(FILENAME_PRODUCTS_ATTRIBUTES, 'id'); ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SORTORDER.xtc_sorting(FILENAME_PRODUCTS_ATTRIBUTES, 'sort'); ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_OPT_NAME.xtc_sorting(FILENAME_PRODUCTS_ATTRIBUTES, 'name'); ?></td>
                <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
              <?php
                $join = '';
                $where = '';
                if (isset($_GET['search']) && $_GET['search'] != '') {
                  $join = "LEFT JOIN ".TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS." pov2po
                                     ON pov2po.products_options_id = po.products_options_id
                           LEFT JOIN ".TABLE_PRODUCTS_OPTIONS_VALUES." pov
                                     ON pov.products_options_values_id = pov2po.products_options_values_id
                                        AND pov.language_id = '".(int)$_SESSION['languages_id']."'";
                  $where = "AND (pov.products_options_values_name LIKE ('%".xtc_db_input($_GET['search'])."%')
                                 OR po.products_options_name LIKE ('%".xtc_db_input($_GET['search'])."%'))";
                }
                $options_query_raw = "SELECT po.*
                                        FROM " . TABLE_PRODUCTS_OPTIONS . " po
                                             ".$join."
                                       WHERE po.language_id = '".(int)$_SESSION['languages_id']."'
                                             ".$where."
                                    GROUP BY po.products_options_id
                                    ORDER BY ".$option_sort;
                $options_split = new splitPageResults($_GET['page'], $page_max_display_options_results, $options_query_raw, $options_query_numrows, 'po.products_options_id');
                $options_query = xtc_db_query($options_query_raw);
                while ($options = xtc_db_fetch_array($options_query)) {
                  if (((!$_GET['oID']) || (@$_GET['oID'] == $options['products_options_id'])) && (!isset($oInfo)) && (substr($_GET['action'], 0, 3) != 'new_value')) {
                    $num_options_query = xtc_db_query("SELECT count(*) as num_options 
                                                         FROM " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " 
                                                        WHERE products_options_id = '" . $options['products_options_id'] . "' 
                                                     GROUP BY products_options_id");
                    if (xtc_db_num_rows($num_options_query) > 0) {
                      $num_options = xtc_db_fetch_array($num_options_query);
                      $options['num_options'] = $num_options['num_options'];
                    } else {
                      $options['num_options'] = 0;
                    }
                    $oInfo = new objectInfo($options);
                  }
                  if (isset($oInfo) && (is_object($oInfo)) && ($options['products_options_id'] == $oInfo->products_options_id) ) {
                    echo '<tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action', 'oID', 'list')) . 'oID=' . $oInfo->products_options_id . '&list=detail') . '\'">' . "\n";
                  } else {
                    echo '<tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action', 'oID')) . 'oID=' . $options['products_options_id']) . '\'">' . "\n";
                  }
                  ?>
                  <td class="dataTableContent" style="width:50px;"><?php echo $options['products_options_id']; ?></td>
                  <td class="dataTableContent" style="width:50px;"><?php echo $options['products_options_sortorder']; ?></td>
                  <td class="dataTableContent"><?php echo '<a href="' . xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action', 'oID', 'list')) . 'oID=' . $options['products_options_id'] . '&list=detail') . '">' . xtc_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER) . '</a>&nbsp;' . $options['products_options_name']; ?></td>
                  <td class="dataTableContent txta-r"><?php if (isset($oInfo) && (is_object($oInfo)) && ($options['products_options_id'] == $oInfo->products_options_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action', 'oID')) . 'oID=' . $options['products_options_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
                </tr>
                <?php
                }
              ?>
            </table>
  
            <div class="smallText pdg2 flt-l"><?php echo $options_split->display_count($options_query_numrows, $page_max_display_options_results, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_OPTIONS); ?></div>
            <div class="smallText pdg2 flt-r"><?php echo $options_split->display_links($options_query_numrows, $page_max_display_options_results, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], xtc_get_all_get_params(array('page', 'oID', 'action')), 'page'); ?></div>
            <div class="clear"></div>
            <?php echo draw_input_per_page($PHP_SELF.'?'.xtc_get_all_get_params(array('page', 'action')),$cfg_max_display_options_key,$page_max_display_options_results); ?> 
            <div class="smallText pdg2 flt-r">
              <?php if (!$_GET['action']) echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action', 'oID')) . 'oID=' . $oInfo->products_options_id . '&action=new_option') . '">' . BUTTON_INSERT . '</a>'; ?>
            </div>
            <?php
            }
            ?>
            </td>
              <?php
              $heading = array();
              $contents = array();

              if (isset($_GET['list']) && $_GET['list'] == 'detail') {
                switch ($_GET['saction']) {
                  case 'new_value':
                    $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_VALUE . '</b>');

                    $contents = array('form' => xtc_draw_form('values', FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action', 'vID', 'saction', 'list')) . 'list=detail&vID=' . $_GET['vID'] . '&saction=insert_values', 'post', 'enctype="multipart/form-data"'));
                    $contents[] = array('text' => TEXT_INFO_NEW_VALUE_INTRO);
                    $contents[] = array('text' => '<br />' . TEXT_INFO_VALUE_NAME . '<br />');
                    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                      $contents[] = array('text' => xtc_image(DIR_WS_LANGUAGES.$languages[$i]['directory'].'/admin/images/'.$languages[$i]['image']) . '&nbsp;' . xtc_draw_input_field('products_options_values_name[' . $languages[$i]['id'] . ']'));
                    }
                    $contents[] = array('text' => '<br />' . TEXT_INFO_VALUE_SORT . '<br />' . xtc_draw_input_field('products_options_values_sortorder'));
                    $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_INSERT . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action', 'saction', 'list')) . 'list=detail') . '">' . BUTTON_CANCEL . '</a>');
                    break;

                  case 'edit_value':
                    $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_VALUE . '</b>');

                    $contents = array('form' => xtc_draw_form('values', FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action', 'vID', 'saction', 'list')) . 'list=detail&vID=' . $vInfo->products_options_values_id . '&saction=save_values', 'post', 'enctype="multipart/form-data"'));
                    $contents[] = array('text' => TEXT_INFO_EDIT_VALUE_INTRO);
                    $contents[] = array('text' => '<br />' . TEXT_INFO_VALUE_NAME . '<br />');
                    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                      $contents[] = array('text' => xtc_image(DIR_WS_LANGUAGES.$languages[$i]['directory'].'/admin/images/'.$languages[$i]['image']) . '&nbsp;' . xtc_draw_input_field('products_options_values_name[' . $languages[$i]['id'] . ']', xtc_get_attributes_values_detail($vInfo->products_options_values_id, $languages[$i]['id'], 'products_options_values_name')));
                    }
                    $contents[] = array('text' => '<br />' . TEXT_INFO_VALUE_SORT . '<br />' . xtc_draw_input_field('products_options_values_sortorder', $vInfo->products_options_values_sortorder));
                    $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_UPDATE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action', 'vID', 'saction', 'list')) . 'list=detail&vID=' . $vInfo->products_options_values_id) . '">' . BUTTON_CANCEL . '</a>');
                    break;

                  case 'delete_value':
                    $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_VALUE . '</b>');

                    $contents = array('form' => xtc_draw_form('values', FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action', 'vID', 'saction', 'list')) . 'list=detail&vID=' . $vInfo->products_options_values_id . '&saction=deleteconfirm_values'));
                    $contents[] = array('text' => TEXT_INFO_DELETE_VALUE_INTRO);
                    $contents[] = array('text' => '<br /><b>' . xtc_get_attributes_values_detail($vInfo->products_options_values_id, $_SESSION['languages_id'], 'products_options_values_name') . '</b>');
                    $products_array = xtc_check_attributes_values($_GET['vID']);
                    if (count($products_array) > 0) {
                      $contents[] = array('text' => TEXT_WARNING_OF_DELETE);
                      $products_content = '<ul>';
                      foreach ($products_array as $products) {
                        $products_content .= '<li>'.$products['products_name'].'</li>';
                      }
                      $products_content .= '<ul>';
                      $contents[] = array('text' => $products_content);
                      $contents[] = array('align' => 'center', 'text' => '<br /><a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action', 'vID', 'saction', 'list')) . 'list=detail&vID=' . $vInfo->products_options_values_id) . '">' . BUTTON_CANCEL . '</a>');
                    } else {
                      $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action', 'vID', 'list')) . 'list=detail&vID=' . $vInfo->products_options_values_id) . '">' . BUTTON_CANCEL . '</a>');
                    }
                    break;

                  default:
                    if (is_object($vInfo)) {
                      $heading[] = array('text' => '<b>' . xtc_get_attributes_values_detail($vInfo->products_options_values_id, $_SESSION['languages_id'], 'products_options_values_name') . '</b>');

                      $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action', 'vID', 'saction', 'list')) . 'list=detail&vID=' . $vInfo->products_options_values_id . '&saction=edit_value') . '">' . BUTTON_EDIT . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action', 'vID', 'saction', 'list')) . 'list=detail&vID=' . $vInfo->products_options_values_id . '&saction=delete_value') . '">' . BUTTON_DELETE . '</a>');
                    }
                    break;
                }
              } else {
                switch ($_GET['action']) {
                  case 'new_option':
                    $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_OPTION . '</b>');

                    $contents = array('form' => xtc_draw_form('options', FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action')) . 'action=insert_options'));
                    $contents[] = array('text' => TEXT_INFO_NEW_OPTION_INTRO);
                    $contents[] = array('text' => '<br />' . TEXT_INFO_OPTION_NAME . '<br />');
                    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                      $contents[] = array('text' => xtc_image(DIR_WS_LANGUAGES.$languages[$i]['directory'].'/admin/images/'.$languages[$i]['image']) . '&nbsp;' . xtc_draw_input_field('products_options_name[' . $languages[$i]['id'] . ']'));
                    }
                    $contents[] = array('text' => '<br />' . TEXT_INFO_OPTION_SORT . '<br />' . xtc_draw_input_field('products_options_sortorder'));
                    $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_INSERT . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action'))) . '">' . BUTTON_CANCEL . '</a>');
                    break;

                  case 'edit_option':
                    $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_OPTION . '</b>');

                    $contents = array('form' => xtc_draw_form('options', FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action', 'oID')) . 'oID=' . $oInfo->products_options_id . '&action=save_options'));
                    $contents[] = array('text' => TEXT_INFO_EDIT_OPTION_INTRO);
                    $contents[] = array('text' => '<br />' . TEXT_INFO_OPTION_NAME . '<br />');
                    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                      $contents[] = array('text' => xtc_image(DIR_WS_LANGUAGES.$languages[$i]['directory'].'/admin/images/'.$languages[$i]['image']) . '&nbsp;' . xtc_draw_input_field('products_options_name[' . $languages[$i]['id'] . ']', xtc_get_attributes_options_detail($oInfo->products_options_id, $languages[$i]['id'], 'products_options_name')));
                    }
                    $contents[] = array('text' => '<br />' . TEXT_INFO_OPTION_SORT . '<br />' . xtc_draw_input_field('products_options_sortorder', $oInfo->products_options_sortorder));
                    $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_UPDATE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action', 'oID')) . 'oID=' . $oInfo->products_options_id) . '">' . BUTTON_CANCEL . '</a>');
                    break;

                  case 'delete_option':
                    $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_OPTION . '</b>');

                    $contents = array('form' => xtc_draw_form('options', FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action', 'oID')) . 'oID=' . $oInfo->products_options_id . '&action=deleteconfirm_options'));
                    $contents[] = array('text' => TEXT_INFO_DELETE_OPTION_INTRO);
                    $contents[] = array('text' => '<br /><b>' . xtc_get_attributes_options_detail($oInfo->products_options_id, $_SESSION['languages_id'], 'products_options_name') . '</b>');

                    $products_array = xtc_check_attributes_options($oInfo->products_options_id);
                    if (count($products_array) > 0) {
                      $contents[] = array('text' => TEXT_WARNING_OF_DELETE);
                      $products_content = '<ul>';
                      foreach ($products_array as $products) {
                        $products_content .= '<li>'.$products['products_name'].'</li>';
                      }
                      $products_content .= '<ul>';
                      $contents[] = array('text' => $products_content);
                      $contents[] = array('align' => 'center', 'text' => '<br /><a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action', 'oID')) . 'oID=' . $oInfo->products_options_id) . '">' . BUTTON_CANCEL . '</a>');
                    } else {
                      $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action', 'oID')) . 'oID=' . $oInfo->products_options_id) . '">' . BUTTON_CANCEL . '</a>');
                    }
                    break;

                  default:
                    if (is_object($oInfo)) {
                      $heading[] = array('text' => '<b>' . xtc_get_attributes_options_detail($oInfo->products_options_id, $_SESSION['languages_id'], 'products_options_name') . '</b>');

                      $contents[] = array('align' => 'center', 'text' => '<a class="button btnbox" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action', 'oID')) . 'oID=' . $oInfo->products_options_id . '&action=edit_option') . '">' . BUTTON_EDIT . '</a> <a class="button btnbox" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action', 'oID')) . 'oID=' . $oInfo->products_options_id . '&action=delete_option') . '">' . BUTTON_DELETE . '</a>' . ' <a class="button btnbox" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, xtc_get_all_get_params(array('action', 'oID', 'list')) . 'oID=' . $oInfo->products_options_id . '&list=detail') . '">' . BUTTON_VALUES . '</a>');
                      $contents[] = array('text' => '<br />' . TEXT_INFO_NUMBER_OPTION . ' ' . $oInfo->num_options . '<br /><br />');
                    }
                    break;
                }
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