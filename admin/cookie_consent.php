<?php
/* -----------------------------------------------------------------------------------------
   $Id: cookie_consent.php $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
  
  require('includes/application_top.php');

  //display per page
  $cfg_max_display_options_key = 'MAX_DISPLAY_NUMBER_OF_COOKIE_CATEGORIES';
  $page_max_display_options_results = xtc_cfg_save_max_display_results($cfg_max_display_options_key);

  $cfg_max_display_values_key = 'MAX_DISPLAY_NUMBER_OF_COOKIE_VALUES';
  $page_max_display_values_results = xtc_cfg_save_max_display_results($cfg_max_display_values_key);

  $languages = xtc_get_languages();

  function xtc_get_cookies_detail($cookies_id, $languages_id, $db_field) {
    $values_query = xtc_db_query("SELECT ".$db_field." 
                                    FROM ".TABLE_COOKIE_CONSENT_COOKIES."
                                   WHERE cookies_id = '".$cookies_id."'
                                     AND languages_id = '".$languages_id."'");
    $values = xtc_db_fetch_array($values_query);
    
    return $values[$db_field];
  }

  function xtc_get_cookies_categories_detail($categories_id, $languages_id, $db_field) {
    $options_query = xtc_db_query("SELECT ".$db_field." 
                                     FROM ".TABLE_COOKIE_CONSENT_CATEGORIES."
                                    WHERE categories_id = '".$categories_id."'
                                      AND languages_id = '".$languages_id."'");
    $options = xtc_db_fetch_array($options_query);
    
    return $options[$db_field];
  }

  function update_cookie_consent_version_data() {
    xtc_db_perform(TABLE_CONFIGURATION, array('configuration_value'=>MODULE_COOKIE_CONSENT_VERSION+1,'last_modified'=>'now()'), 'update', "`configuration_key`='MODULE_COOKIE_CONSENT_VERSION'");
    xtc_db_perform(TABLE_CONFIGURATION, array('configuration_value'=>'now()','last_modified'=>'now()'), 'update', "`configuration_key`='MODULE_COOKIE_CONSENT_LAST_UPDATE'");
  }
  
  if (isset($_GET['saction'])) {
    switch ($_GET['saction']) {
    
      case 'setvaluesflag':
        $vID = (int)$_GET['vID'];
        $oID = (int)$_GET['oID'];
        $status = (int)$_GET['flag'];
        xtc_db_query("UPDATE " . TABLE_COOKIE_CONSENT_COOKIES . " 
                         SET status = '" . xtc_db_input($status) . "' 
                       WHERE cookies_id = '" . $vID . "'"); 
        update_cookie_consent_version_data();
        xtc_redirect(xtc_href_link(basename($PHP_SELF), 'page=' . (int)$_GET['page'] . '&oID=' . $oID . '&action=list&spage=' . (int)$_GET['spage'] . '&vID=' . $vID));
        break;
        
      case 'insert_values':
        $oID = (int)$_GET['oID'];
        $next_id_query = xtc_db_query("SELECT max(cookies_id) as cookies_id 
                                         FROM " . TABLE_COOKIE_CONSENT_COOKIES . "");
        $next_id = xtc_db_fetch_array($next_id_query);
        $cookies_id = $next_id['cookies_id'] + 1;
        $cookie_list = xtc_db_prepare_input($_POST['cookies_list']);
        if (!empty($cookie_list)) {
          $cookie_list = explode(',',$cookie_list);
          $cookie_list = array_map('trim',$cookie_list);
          $cookie_list = implode(',',$cookie_list);
        }
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $sql_data_array = array('cookies_id' => $cookies_id,
                                  'categories_id' => $oID,
                                  'cookies_name' => xtc_db_prepare_input($_POST['cookies_name'][$languages[$i]['id']]),
                                  'cookies_description' => xtc_db_prepare_input($_POST['cookies_description'][$languages[$i]['id']]),
                                  'cookies_list' => $cookie_list,
                                  'languages_id' => $languages[$i]['id'],
                                  'sort_order' => (int)$_POST['sort_order'],
                                  'date_added' => 'now()');
          xtc_db_perform(TABLE_COOKIE_CONSENT_COOKIES, $sql_data_array);
        }

        update_cookie_consent_version_data();
        xtc_redirect(xtc_href_link(basename($PHP_SELF), 'page=' . (int)$_GET['page'] . '&oID=' . $oID . '&action=list&spage=' . (int)$_GET['spage'] . '&vID=' . $cookies_id));
        break;

      case 'save_values':
        $vID = (int)$_GET['vID'];
        $oID = (int)$_GET['oID'];
        $cookie_list = xtc_db_prepare_input($_POST['cookies_list']);
        if (!empty($cookie_list)) {
          $cookie_list = explode(',',$cookie_list);
          $cookie_list = array_map('trim',$cookie_list);
          $cookie_list = implode(',',$cookie_list);
        }
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {     
          $sql_data_array = array('cookies_id' => $vID,
                                  'categories_id' => $oID,
                                  'cookies_name' => xtc_db_prepare_input($_POST['cookies_name'][$languages[$i]['id']]),
                                  'cookies_description' => xtc_db_prepare_input($_POST['cookies_description'][$languages[$i]['id']]),
                                  'cookies_list' => $cookie_list,
                                  'languages_id' => $languages[$i]['id'],
                                  'sort_order' => (int)$_POST['sort_order']);
          $values_description_query = xtc_db_query("SELECT * 
                                                      FROM ".TABLE_COOKIE_CONSENT_COOKIES." 
                                                     WHERE languages_id = '".$languages[$i]['id']."' 
                                                       AND cookies_id = '".$vID."'");
          if (xtc_db_num_rows($values_description_query) == 0) {
            $sql_data_array['date_added'] = 'now()';
            xtc_db_perform(TABLE_COOKIE_CONSENT_COOKIES, $sql_data_array);
          } else {
            $sql_data_array['last_modified'] = 'now()';
            xtc_db_perform(TABLE_COOKIE_CONSENT_COOKIES, $sql_data_array, 'update', "cookies_id = '".$vID."' AND languages_id = '".$languages[$i]['id']."'");                    
          }      
        }

        update_cookie_consent_version_data();
        xtc_redirect(xtc_href_link(basename($PHP_SELF), 'page=' . (int)$_GET['page'] . '&oID=' . $oID . '&action=list&spage=' . (int)$_GET['spage'] . '&vID=' . $vID));
        break;

      case 'deleteconfirm_values':
        $vID = (int)$_GET['vID'];
        $oID = (int)$_GET['oID'];
        $fixed = xtc_get_cookies_detail($vID, $_SESSION['languages_id'], 'fixed');
        if (!$fixed) {
          xtc_db_query("DELETE FROM " . TABLE_COOKIE_CONSENT_COOKIES . " WHERE cookies_id = '" . $vID . "'");
          xtc_db_query("DELETE FROM " . TABLE_COOKIE_CONSENT_CATEGORIES . " WHERE cookies_id = '" . $vID . "'");
        }
        update_cookie_consent_version_data();
        xtc_redirect(xtc_href_link(basename($PHP_SELF), 'page=' . (int)$_GET['page'] . '&oID=' . $oID . '&action=list&spage=' . (int)$_GET['spage']));
        break;
    }
  }
  
  if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        
      case 'insert_options':      
        $next_id_query = xtc_db_query("SELECT max(categories_id) as categories_id 
                                         FROM " . TABLE_COOKIE_CONSENT_CATEGORIES . "");
        $next_id = xtc_db_fetch_array($next_id_query);
        $categories_id = $next_id['categories_id'] + 1;
        
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {     
          $sql_data_array = array('categories_id' => $categories_id,
                                  'categories_name' => xtc_db_prepare_input($_POST['categories_name'][$languages[$i]['id']]),
                                  'categories_description' => xtc_db_prepare_input($_POST['categories_description'][$languages[$i]['id']]),
                                  'languages_id' => $languages[$i]['id'],
                                  'sort_order' => (int)$_POST['sort_order'],
                                  'date_added' => 'now()'
                                  );
          xtc_db_perform(TABLE_COOKIE_CONSENT_CATEGORIES, $sql_data_array);
        }                  

        update_cookie_consent_version_data();
        xtc_redirect(xtc_href_link(basename($PHP_SELF), 'page=' . (int)$_GET['page'] . '&oID=' . $categories_id));
        break;

      case 'save_options':
        $oID = (int)$_GET['oID'];
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {     
          $sql_data_array = array('categories_id' => $oID,
                                  'categories_name' => xtc_db_prepare_input($_POST['categories_name'][$languages[$i]['id']]),
                                  'categories_description' => xtc_db_prepare_input($_POST['categories_description'][$languages[$i]['id']]),
                                  'languages_id' => $languages[$i]['id'],
                                  'sort_order' => (int)$_POST['sort_order'],
                                  );
          $options_name_query = xtc_db_query("SELECT * 
                                                FROM ".TABLE_COOKIE_CONSENT_CATEGORIES." 
                                               WHERE languages_id = '".$languages[$i]['id']."' 
                                                 AND categories_id = '".$oID."'");
          if (xtc_db_num_rows($options_name_query) == 0) {
            $sql_data_array['date_added'] = 'now()';
            xtc_db_perform(TABLE_COOKIE_CONSENT_CATEGORIES, $sql_data_array);
          } else {
            $sql_data_array['last_modified'] = 'now()';
            xtc_db_perform(TABLE_COOKIE_CONSENT_CATEGORIES, $sql_data_array, 'update', "categories_id = '".$oID."' AND languages_id = '".$languages[$i]['id']."'");                    
          }
        }

        update_cookie_consent_version_data();
        xtc_redirect(xtc_href_link(basename($PHP_SELF), 'page=' . (int)$_GET['page'] . '&oID=' . $oID));
        break;

      case 'deleteconfirm_options':
        $oID = (int)$_GET['oID'];
        $fixed = xtc_get_cookies_categories_detail($oID, $_SESSION['languages_id'], 'fixed');
        if (!$fixed) {
          xtc_db_query("DELETE FROM " . TABLE_COOKIE_CONSENT_COOKIES . " WHERE categories_id = '" . $oID . "'");
          xtc_db_query("DELETE FROM " . TABLE_COOKIE_CONSENT_CATEGORIES . " WHERE categories_id = '" . $oID . "'");
        }
        update_cookie_consent_version_data();
        xtc_redirect(xtc_href_link(basename($PHP_SELF), 'page=' . (int)$_GET['page']));
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
        <div class="pageHeading"><?php echo ((isset($_GET['action']) && $_GET['action'] == 'list') ? HEADING_TITLE_DETAIL : HEADING_TITLE); ?></div>       
        <div class="main pdg2 flt-l">Configuration</div>
        <table class="tableCenter">
          <tr>
            <td class="boxCenterLeft">
            <?php
            if (isset($_GET['action']) && $_GET['action'] == 'list') {
            ?>
            <table class="tableBoxCenter collapse">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SORT; ?></td>
                <td class="dataTableHeadingContent" width="10%"><?php echo TABLE_HEADING_VALUES_NAME; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_VALUES_DESCRIPTION; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_VALUES_COOKIES; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_VALUES_PURPOSEID; ?></td>
                <?php if (MODULE_COOKIE_CONSENT_SET_READABLE_COOKIE == 'true') { ?>
                  <td class="dataTableHeadingContent"><?php echo TEXT_INFO_HEADING_EXTERNAL_TRIGGER; ?></td>
                <?php } ?>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
              <?php
              $values_query_raw = "SELECT ptv.*
                                     FROM " . TABLE_COOKIE_CONSENT_COOKIES . " ptv
                                     JOIN " . TABLE_COOKIE_CONSENT_CATEGORIES . " pto
                                          ON pto.categories_id = ptv.categories_id
                                             AND pto.languages_id = '".(int)$_SESSION['languages_id']."'
                                    WHERE ptv.categories_id = '".$_GET['oID']."'
                                      AND ptv.languages_id = '".(int)$_SESSION['languages_id']."'
                                 ORDER BY ptv.sort_order, ptv.cookies_name";
              $values_split = new splitPageResults($_GET['spage'], $page_max_display_values_results, $values_query_raw, $values_query_numrows);
              $values_query = xtc_db_query($values_query_raw);
              while ($values = xtc_db_fetch_array($values_query)) {
                if (((!$_GET['vID']) || (@$_GET['vID'] == $values['cookies_id'])) && (!$vInfo) && (substr($_GET['saction'], 0, 3) != 'new_value')) {
                  $vInfo = new objectInfo($values);
                }
                if ( (is_object($vInfo)) && ($values['cookies_id'] == $vInfo->cookies_id) ) {
                  echo '<tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(basename($PHP_SELF), 'page=' . $_GET['page'] . '&oID=' . $_GET['oID'] . '&action=list&spage=' . $_GET['spage'] . '&vID=' . $vInfo->cookies_id . '&saction=edit_value') . '\'">' . "\n";
                } else {
                  echo '<tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(basename($PHP_SELF), 'page=' . $_GET['page'] . '&oID=' . $_GET['oID'] . '&action=list&spage=' . $_GET['spage'] . '&vID=' . $values['cookies_id']) . '\'">' . "\n";
                }
                ?>
                <td class="dataTableContent" style="width:50px;"><?php echo $values['sort_order']; ?></td>
                <td class="dataTableContent"><?php echo encode_htmlentities($values['cookies_name']); ?></td>
                <td class="dataTableContent"><?php echo encode_htmlentities($values['cookies_description']); ?></td>
                <td class="dataTableContent"><?php echo encode_htmlentities($values['cookies_list']); ?></td>
                <td class="dataTableContent"><?php echo encode_htmlentities($values['cookies_id']); ?></td>
                <?php if (MODULE_COOKIE_CONSENT_SET_READABLE_COOKIE == 'true') { ?>
                  <td class="dataTableContent" onclick="event.stopPropagation();return false;" style="cursor:text"><pre>&quot;<?php echo $values['cookies_id']; ?>&quot;:true</pre></td>
                <?php } ?>
                <td class="dataTableContent">
                  <?php
                  if ($values['status'] == 1) {
                    echo xtc_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10, 'style="margin-left: 5px;"') . '<a href="' . xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('saction', 'vID')) . 'saction=setvaluesflag&flag=0&vID='.$values['cookies_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10, 'style="margin-left: 5px;"') . '</a>';
                  } else {
                    echo '<a href="' . xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('saction', 'vID')) . 'saction=setvaluesflag&flag=1&vID='.$values['cookies_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10, 'style="margin-left: 5px;"') . '</a>' . xtc_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10, 'style="margin-left: 5px;"');
                  }
                  ?>
                </td>
                <td class="dataTableContent txta-r"><?php if ( (is_object($vInfo)) && ($values['cookies_id'] == $vInfo->cookies_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(basename($PHP_SELF), 'page=' . $_GET['page'] . '&oID=' . $_GET['oID'] . '&action=list&spage=' . $_GET['spage'] . '&vID=' . $values['cookies_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
              <?php
              }
              ?>
            </table>
            
            <div class="smallText pdg2 flt-l"><?php echo $values_split->display_count($values_query_numrows, $page_max_display_values_results, $_GET['spage'], TEXT_DISPLAY_NUMBER_OF_VALUES); ?></div>
            <div class="smallText pdg2 flt-r"><?php echo $values_split->display_links($values_query_numrows, $page_max_display_values_results, MAX_DISPLAY_PAGE_LINKS, $_GET['spage'], 'page=' . $_GET['page'] . '&oID=' . $_GET['oID'] . '&action=list', 'spage'); ?></div>
            <div class="clear"></div>
            <?php echo draw_input_per_page($PHP_SELF.'?'.xtc_get_all_get_params(array('page')),$cfg_max_display_values_key,$page_max_display_values_results); ?>
            <div class="smallText pdg2 flt-r"><?php if (!$_GET['saction']) echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(basename($PHP_SELF), 'page=' . $_GET['page'] . '&oID=' . $_GET['oID']) . '">' . BUTTON_BACK . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link(basename($PHP_SELF), 'page=' . $_GET['page'] . '&oID=' . $_GET['oID'] . '&action=list&spage=' . $_GET['spage'] . '&vID=' . $vInfo->cookies_id . '&saction=new_value') . '">' . BUTTON_INSERT . '</a>'; ?></div>
            <?php
            } else {
            ?>
            <table class="tableBoxCenter collapse">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SORT; ?></td>
                <td class="dataTableHeadingContent" width="20%"><?php echo TABLE_HEADING_OPTIONS_NAME; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_OPTIONS_DESCRIPTION; ?></td>
                <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
              <?php
                $options_query_raw = "SELECT *
                                        FROM " . TABLE_COOKIE_CONSENT_CATEGORIES . " 
                                       WHERE languages_id = '".(int)$_SESSION['languages_id']."'
                                    ORDER BY sort_order, categories_name";
                $options_split = new splitPageResults($_GET['page'], $page_max_display_options_results, $options_query_raw, $options_query_numrows);
                $options_query = xtc_db_query($options_query_raw);
                while ($options = xtc_db_fetch_array($options_query)) {
                  if (((!$_GET['oID']) || (@$_GET['oID'] == $options['categories_id'])) && (!isset($oInfo)) && (substr($_GET['action'], 0, 3) != 'new_value')) {
                    $num_options_query = xtc_db_query("SELECT count(*) as num_options 
                                                         FROM " . TABLE_COOKIE_CONSENT_COOKIES . " 
                                                        WHERE categories_id = '" . $options['categories_id'] . "' 
                                                          AND languages_id = '".(int)$_SESSION['languages_id']."'
                                                     GROUP BY categories_id");
                    if (xtc_db_num_rows($num_options_query) > 0) {
                      $num_options = xtc_db_fetch_array($num_options_query);
                      $options['num_options'] = $num_options['num_options'];
                    } else {
                      $options['num_options'] = 0;
                    }
                    $oInfo = new objectInfo($options);
                  }
                  if (isset($oInfo) && (is_object($oInfo)) && ($options['categories_id'] == $oInfo->categories_id) ) {
                    echo '<tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(basename($PHP_SELF), 'page=' . $_GET['page'] . '&oID=' . $oInfo->categories_id . '&action=list') . '\'">' . "\n";
                  } else {
                    echo '<tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(basename($PHP_SELF), 'page=' . $_GET['page'] . '&oID=' . $options['categories_id']) . '\'">' . "\n";
                  }
                  ?>
                  <td class="dataTableContent" style="width:50px;"><?php echo $options['sort_order']; ?></td>
                  <td class="dataTableContent"><?php echo '<a href="' . xtc_href_link(basename($PHP_SELF), 'page=' . $_GET['page'] . '&oID=' . $options['categories_id'] . '&action=list') . '">' . xtc_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER) . '</a>&nbsp;' . encode_htmlentities($options['categories_name']); ?></td>
                  <td class="dataTableContent"><?php echo encode_htmlentities($options['categories_description']); ?></td>
                  <td class="dataTableContent txta-r"><?php if (isset($oInfo) && (is_object($oInfo)) && ($options['categories_id'] == $oInfo->categories_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(basename($PHP_SELF), 'page=' . $_GET['page'] . '&oID=' . $options['categories_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
                </tr>
                <?php
                }
              ?>
            </table>
            
            <div class="smallText pdg2 flt-l"><?php echo $options_split->display_count($options_query_numrows, $page_max_display_options_results, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_OPTIONS); ?></div>
            <div class="smallText pdg2 flt-r"><?php echo $options_split->display_links($options_query_numrows, $page_max_display_options_results, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], '', 'page'); ?></div>
            <div class="clear"></div>
            <?php echo draw_input_per_page($PHP_SELF,$cfg_max_display_options_key,$page_max_display_options_results); ?> 
            <div class="smallText pdg2 flt-r">
              <?php if (!$_GET['action']) echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(basename($PHP_SELF), 'page=' . $_GET['page'] . '&oID=' . $oInfo->categories_id . '&action=new_option') . '">' . BUTTON_INSERT . '</a>'; ?>
            </div>
            <?php
            }
            ?>
            </td>
              <?php
              $heading = array();
              $contents = array();

              if (isset($_GET['action']) && $_GET['action'] == 'list') {
                switch ($_GET['saction']) {
                  case 'new_value':
                    $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_VALUE . '</b>');

                    $contents = array('form' => xtc_draw_form('values', basename($PHP_SELF), 'page=' . $_GET['page'] . '&oID=' . $_GET['oID'] . '&action=list&spage=' . $_GET['spage'] . '&vID=' . $_GET['vID'] . '&saction=insert_values', 'post', 'enctype="multipart/form-data"'));
                    $contents[] = array('text' => TEXT_INFO_NEW_VALUE_INTRO);
                    $contents[] = array('text' => '<br />' . TEXT_INFO_VALUE_NAME . '<br />');
                    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                      $contents[] = array('text' => xtc_image(DIR_WS_LANGUAGES.$languages[$i]['directory'].'/admin/images/'.$languages[$i]['image']) . '&nbsp;' . xtc_draw_input_field('cookies_name[' . $languages[$i]['id'] . ']'));
                    }
                    $contents[] = array('text' => '<br />' . TEXT_INFO_VALUE_DESCRIPTION . '<br />');
                    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                      $contents[] = array('text' => xtc_image(DIR_WS_LANGUAGES.$languages[$i]['directory'].'/admin/images/'.$languages[$i]['image']) . '&nbsp;' . xtc_draw_textarea_field('cookies_description[' . $languages[$i]['id'] . ']', '', '45', '5'));
                    }
                    $contents[] = array('text' => '<br />' . TEXT_INFO_VALUE_COOKIES . '<br />' . xtc_draw_input_field('cookies_list'));
                    $contents[] = array('text' => '<br />' . TEXT_INFO_VALUE_SORT . '<br />' . xtc_draw_input_field('sort_order'));
                    $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_INSERT . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(basename($PHP_SELF), 'page=' . $_GET['page'] . '&oID=' . $_GET['oID'] . '&action=list&spage=' . $_GET['spage'] . '&vID=' . $_GET['vID']) . '">' . BUTTON_CANCEL . '</a>');
                    break;

                  case 'edit_value':
                    $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_VALUE . '</b>');

                    $contents = array('form' => xtc_draw_form('values', basename($PHP_SELF), 'page=' . $_GET['page'] . '&oID=' . $_GET['oID'] . '&action=list&spage=' . $_GET['spage'] . '&vID=' . $vInfo->cookies_id . '&saction=save_values', 'post', 'enctype="multipart/form-data"'));
                    $contents[] = array('text' => TEXT_INFO_EDIT_VALUE_INTRO);
                    $contents[] = array('text' => '<br />' . TEXT_INFO_VALUE_NAME . '<br />');
                    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                      $contents[] = array('text' => xtc_image(DIR_WS_LANGUAGES.$languages[$i]['directory'].'/admin/images/'.$languages[$i]['image']) . '&nbsp;' . xtc_draw_input_field('cookies_name[' . $languages[$i]['id'] . ']', xtc_get_cookies_detail($vInfo->cookies_id, $languages[$i]['id'], 'cookies_name')));
                    }
                    $contents[] = array('text' => '<br />' . TEXT_INFO_VALUE_DESCRIPTION . '<br />');
                    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                      $contents[] = array('text' => xtc_image(DIR_WS_LANGUAGES.$languages[$i]['directory'].'/admin/images/'.$languages[$i]['image']) . '&nbsp;' . xtc_draw_textarea_field('cookies_description[' . $languages[$i]['id'] . ']', '', '45', '5', xtc_get_cookies_detail($vInfo->cookies_id, $languages[$i]['id'], 'cookies_description')));
                    }
                    $contents[] = array('text' => '<br />' . TEXT_INFO_VALUE_COOKIES . '<br />' . xtc_draw_input_field('cookies_list', $vInfo->cookies_list) . '<br /><small>' . TEXT_INFO_VALUE_COOKIES_DESC . '</small><br />');
                    $contents[] = array('text' => '<br />' . TEXT_INFO_VALUE_SORT . '<br />' . xtc_draw_input_field('sort_order', $vInfo->sort_order));
                    $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_UPDATE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(basename($PHP_SELF), 'page=' . $_GET['page'] . '&oID=' . $_GET['oID'] . '&action=list&spage=' . $_GET['spage'] . '&vID=' . $vInfo->cookies_id) . '">' . BUTTON_CANCEL . '</a>');
                    break;

                  case 'delete_value':
                    $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_VALUE . '</b>');

                    $contents = array('form' => xtc_draw_form('values', basename($PHP_SELF), 'page=' . $_GET['page'] . '&oID=' . $_GET['oID'] . '&action=list&spage=' . $_GET['spage'] . '&vID=' . $vInfo->cookies_id . '&saction=deleteconfirm_values'));
                    $contents[] = array('text' => TEXT_INFO_DELETE_VALUE_INTRO);
                    $contents[] = array('text' => '<br /><b>' . xtc_get_cookies_detail($vInfo->cookies_id, $_SESSION['languages_id'], 'cookies_name') . '</b>');
                    $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(basename($PHP_SELF), 'page=' . $_GET['page'] . '&oID=' . $_GET['oID'] . '&action=list&spage=' . $_GET['spage'] . '&vID=' . $vInfo->cookies_id) . '">' . BUTTON_CANCEL . '</a>');
                    break;

                  default:
                    if (is_object($vInfo)) {
                      $heading[] = array('text' => '<b>' . xtc_get_cookies_detail($vInfo->cookies_id, $_SESSION['languages_id'], 'cookies_name') . '</b>');
                      
                      $consent_buttons = '';
                      $consent_buttons .= '<a class="button" onclick="this.blur();" href="' . xtc_href_link(basename($PHP_SELF), 'page=' . $_GET['page'] . '&oID=' . $_GET['oID'] . '&action=list&spage=' . $_GET['spage'] . '&vID=' . $vInfo->cookies_id . '&saction=edit_value') . '">' . BUTTON_EDIT . '</a> ';
                      if (!$vInfo->fixed) {
                        $consent_buttons .= '<a class="button" onclick="this.blur();" href="' . xtc_href_link(basename($PHP_SELF), 'page=' . $_GET['page'] . '&oID=' . $_GET['oID'] . '&action=list&spage=' . $_GET['spage'] . '&vID=' . $vInfo->cookies_id . '&saction=delete_value') . '">' . BUTTON_DELETE . '</a> ';
                      }
                      $contents[] = array('align' => 'center', 'text' => $consent_buttons);

                      $contents[] = array('text' => '<br />' . TEXT_INFO_DATE_ADDED . ' ' . xtc_date_short($vInfo->date_added));
                      if (xtc_not_null($vInfo->last_modified)) $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . ' ' . xtc_date_short($vInfo->last_modified));
                      
                      $contents[] = array('text' => '<br /><hr /><br />');
                      $js = encode_htmlentities("<script async ".chr(10)."data-type=\"text/javascript\" ".chr(10)."data-src=\"YOUR_SRC_HERE\" ".chr(10)."type=\"as-oil\" ".chr(10)."data-purposes=\"".$vInfo->cookies_id."\" ".chr(10)."data-managed=\"as-oil\"></script>");
                      $contents[] = array('text' => '<b>'.TEXT_INFO_HEADING_JSCRIPT_SRC.'</b><br /><pre>'.$js.'</pre><br /><br />');
                      
                      $js = encode_htmlentities("<script async ".chr(10)."data-type=\"text/javascript\" ".chr(10)."type=\"as-oil\" ".chr(10)."data-purposes=\"".$vInfo->cookies_id."\" ".chr(10)."data-managed=\"as-oil\">".chr(10)."YOUR_CODE_HERE".chr(10)."</script>");
                      $contents[] = array('text' => '<b>'.TEXT_INFO_HEADING_JSCRIPT_DIRECT.'</b><br /><pre>'.$js.'</pre><br /><br />');
                      
                      $js = encode_htmlentities("<img data-managed=\"as-oil\"".chr(10)." data-src=\"YOUR-SRC-HERE\"".chr(10)." data-title=\"Simple Image\"".chr(10)." data-purposes=\"".$vInfo->cookies_id."\" />".chr(10));
                      $contents[] = array('text' => '<b>'.TEXT_INFO_HEADING_JSCRIPT_OTHER_CODE.'</b><br /><pre>'.$js.'</pre><br /><br />');
                      
                      $contents[] = array('text' => 'More information: <a href="https://github.com/as-ideas/oil" target="_blank">https://github.com/as-ideas/oil</a>');
                    }
                    break;
                }
              } else {
                switch ($_GET['action']) {
                  case 'new_option':
                    $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_OPTION . '</b>');

                    $contents = array('form' => xtc_draw_form('options', basename($PHP_SELF), 'page=' . $_GET['page'] . '&oID=' . $_GET['oID'] . '&action=insert_options'));
                    $contents[] = array('text' => TEXT_INFO_NEW_OPTION_INTRO);
                    $contents[] = array('text' => '<br />' . TEXT_INFO_OPTION_NAME . '<br />');
                    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                      $contents[] = array('text' => xtc_image(DIR_WS_LANGUAGES.$languages[$i]['directory'].'/admin/images/'.$languages[$i]['image']) . '&nbsp;' . xtc_draw_input_field('categories_name[' . $languages[$i]['id'] . ']'));
                    }
                    $contents[] = array('text' => '<br />' . TEXT_INFO_OPTION_DESCRIPTION . '<br />');
                    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                      $contents[] = array('text' => xtc_image(DIR_WS_LANGUAGES.$languages[$i]['directory'].'/admin/images/'.$languages[$i]['image']) . '&nbsp;' . xtc_draw_textarea_field('categories_description[' . $languages[$i]['id'] . ']', '', '45', '5'));
                    }
                    $contents[] = array('text' => '<br />' . TEXT_INFO_OPTION_SORT . '<br />' . xtc_draw_input_field('sort_order'));
                    $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_INSERT . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(basename($PHP_SELF), 'page=' . $_GET['page'] . '&oID=' . $_GET['oID']) . '">' . BUTTON_CANCEL . '</a>');
                    break;

                  case 'edit_option':
                    $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_OPTION . '</b>');

                    $contents = array('form' => xtc_draw_form('options', basename($PHP_SELF), 'page=' . $_GET['page'] . '&oID=' . $oInfo->categories_id . '&action=save_options'));
                    $contents[] = array('text' => TEXT_INFO_EDIT_OPTION_INTRO);
                    $contents[] = array('text' => '<br />' . TEXT_INFO_OPTION_NAME . '<br />');
                    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                      $contents[] = array('text' => xtc_image(DIR_WS_LANGUAGES.$languages[$i]['directory'].'/admin/images/'.$languages[$i]['image']) . '&nbsp;' . xtc_draw_input_field('categories_name[' . $languages[$i]['id'] . ']', xtc_get_cookies_categories_detail($oInfo->categories_id, $languages[$i]['id'], 'categories_name')));
                    }
                    $contents[] = array('text' => '<br />' . TEXT_INFO_OPTION_DESCRIPTION . '<br />');
                    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                      $contents[] = array('text' => xtc_image(DIR_WS_LANGUAGES.$languages[$i]['directory'].'/admin/images/'.$languages[$i]['image']) . '&nbsp;' . xtc_draw_textarea_field('categories_description[' . $languages[$i]['id'] . ']', '', '45', '5', xtc_get_cookies_categories_detail($oInfo->categories_id, $languages[$i]['id'], 'categories_description')));
                    }
                    $contents[] = array('text' => '<br />' . TEXT_INFO_OPTION_SORT . '<br />' . xtc_draw_input_field('sort_order', $oInfo->sort_order));
                    $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_UPDATE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(basename($PHP_SELF), 'page=' . $_GET['page'] . '&oID=' . $oInfo->categories_id) . '">' . BUTTON_CANCEL . '</a>');
                    break;

                  case 'delete_option':
                    $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_OPTION . '</b>');

                    $contents = array('form' => xtc_draw_form('options', basename($PHP_SELF), 'page=' . $_GET['page'] . '&oID=' . $oInfo->categories_id . '&action=deleteconfirm_options'));
                    $contents[] = array('text' => TEXT_INFO_DELETE_OPTION_INTRO);
                    $contents[] = array('text' => '<br /><b>' . xtc_get_cookies_categories_detail($oInfo->categories_id, $_SESSION['languages_id'], 'categories_name') . '</b>');
                    $products_query = xtc_db_query("SELECT * 
                                                      FROM ".TABLE_COOKIE_CONSENT_CATEGORIES." 
                                                     WHERE categories_id = '".(int)$oInfo->categories_id."' 
                                                  GROUP BY products_id");
                    $products_total = xtc_db_num_rows($products_query);
                    if ($products_total > 0) {
                      $contents[] = array('text' => '<br />' . sprintf(TEXT_INFO_WARNING_PRODUCTS, $products_total));
                    }
                    $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(basename($PHP_SELF), 'page=' . $_GET['page'] . '&oID=' . $oInfo->categories_id) . '">' . BUTTON_CANCEL . '</a>');
                    break;

                  default:
                    if (is_object($oInfo)) {
                      $heading[] = array('text' => '<b>' . xtc_get_cookies_categories_detail($oInfo->categories_id, $_SESSION['languages_id'], 'categories_name') . '</b>');

                      $consent_buttons = '';
                      $consent_buttons .= '<a class="button btnbox" onclick="this.blur();" href="' . xtc_href_link(basename($PHP_SELF), 'page=' . $_GET['page'] . '&oID=' . $oInfo->categories_id . '&action=edit_option') . '">' . BUTTON_EDIT . '</a> ';
                      if (!$oInfo->fixed) {
                        $consent_buttons .= '<a class="button btnbox" onclick="this.blur();" href="' . xtc_href_link(basename($PHP_SELF), 'page=' . $_GET['page'] . '&oID=' . $oInfo->categories_id . '&action=delete_option') . '">' . BUTTON_DELETE . '</a> ';
                      }
                      $consent_buttons .= '<a class="button btnbox" onclick="this.blur();" href="' . xtc_href_link(basename($PHP_SELF), 'page=' . $_GET['page'] . '&oID=' . $oInfo->categories_id . '&action=list') . '">' . BUTTON_COOKIES . '</a>';
                      $contents[] = array('align' => 'center', 'text' => $consent_buttons);
                      $contents[] = array('text' => '<br />' . TEXT_INFO_NUMBER_OPTION . ' ' . $oInfo->num_options);
                      $contents[] = array('text' => '<br />' . TEXT_INFO_DATE_ADDED . ' ' . xtc_date_short($oInfo->date_added));
                      if (xtc_not_null($oInfo->last_modified)) $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . ' ' . xtc_date_short($oInfo->last_modified));
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