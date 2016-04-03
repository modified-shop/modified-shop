<?php
  /* --------------------------------------------------------------
   $Id: languages.php 5069 2013-07-15 12:57:14Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(languages.php,v 1.33 2003/05/07); www.oscommerce.com
   (c) 2003 nextcommerce (languages.php,v 1.10 2003/08/18); www.nextcommerce.org
   (c) 2006 XT-Commerce (languages.php 1180 2005-08-26)

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  require('includes/application_top.php');
  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (xtc_not_null($action)) {
    switch ($action) {
      case 'setlflag':
          $language_id = (int)$_GET['lID'];
          $status = (int)$_GET['flag'];
          xtc_db_query("update " . TABLE_LANGUAGES . " set status = '" . xtc_db_input($status) . "' where languages_id = '" . xtc_db_input($language_id) . "'");
          xtc_redirect(xtc_href_link(FILENAME_LANGUAGES, 'page=' . (int)$_GET['page'] . '&lID=' . $language_id));
        break;
       case 'setladminflag':
          $language_id = xtc_db_prepare_input($_GET['lID']);
          $status_admin = xtc_db_prepare_input($_GET['adminflag']);
          xtc_db_query("update " . TABLE_LANGUAGES . " set status_admin = '" . xtc_db_input($status_admin) . "' where languages_id = '" . xtc_db_input($language_id) . "'");
          xtc_redirect(xtc_href_link(FILENAME_LANGUAGES, 'page=' . (int)$_GET['page'] . '&lID=' . $language_id));
        break;
      case 'insert':
        $sql_data_array = array(
            'name' => xtc_db_prepare_input($_POST['name']), 
            'code' => xtc_db_prepare_input($_POST['code']),  
            'image' => xtc_db_prepare_input($_POST['image']),  
            'directory' => xtc_db_prepare_input($_POST['directory']),  
            //'status' => xtc_db_prepare_input($_POST['status']),  
            'sort_order' => xtc_db_prepare_input($_POST['sort_order']), 
            'language_charset' => xtc_db_prepare_input($_POST['charset']),
            //'status_admin' => xtc_db_prepare_input($_POST['status_admin'])
          );
        xtc_db_perform(TABLE_LANGUAGES, $sql_data_array);      
        $insert_id = xtc_db_insert_id();

        // create additional customers status
        $customers_status_query=xtc_db_query("SELECT DISTINCT customers_status_id
                                                FROM ".TABLE_CUSTOMERS_STATUS
                                            );
        while ($data=xtc_db_fetch_array($customers_status_query)) {

          $customers_status_data_query=xtc_db_query("SELECT *
                                                         FROM ".TABLE_CUSTOMERS_STATUS."
                                                        WHERE customers_status_id='".$data['customers_status_id']."'");
          $c_data = xtc_db_fetch_array($customers_status_data_query);
          $c_data['language_id'] = $insert_id;
          xtc_db_perform(TABLE_CUSTOMERS_STATUS, $c_data);
        }
        if (isset($_POST['default']) && $_POST['default'] == 'on') {
          xtc_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . xtc_db_input($code) . "' where configuration_key = 'DEFAULT_LANGUAGE'");
        }
        xtc_redirect(xtc_href_link(FILENAME_LANGUAGES, 'page=' . (int)$_GET['page'] . '&lID=' . $insert_id));
        break;
      case 'save':
        $lID = (int)$_GET['lID'];
       
        $sql_data_array = array(
            'name' => xtc_db_prepare_input($_POST['name']), 
            'code' => xtc_db_prepare_input($_POST['code']),  
            'image' => xtc_db_prepare_input($_POST['image']),  
            'directory' => xtc_db_prepare_input($_POST['directory']),  
            //'status' => xtc_db_prepare_input($_POST['status']),  
            'sort_order' => xtc_db_prepare_input($_POST['sort_order']), 
            'language_charset' => xtc_db_prepare_input($_POST['charset']),
            //'status_admin' => xtc_db_prepare_input($_POST['status_admin'])
          ); 
        xtc_db_perform(TABLE_LANGUAGES, $sql_data_array, 'update', 'languages_id = \''.$lID.'\'');        
        
        if ($_POST['default'] == 'on') {
          xtc_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . xtc_db_input($code) . "' where configuration_key = 'DEFAULT_LANGUAGE'");
        }
        xtc_redirect(xtc_href_link(FILENAME_LANGUAGES, 'page=' . (int)$_GET['page'] . '&lID=' . $lID));
        break;
      case 'deleteconfirm':
        $lID = (int)$_GET['lID'];
        $lng_query = xtc_db_query("select languages_id from " . TABLE_LANGUAGES . " where code = '" . DEFAULT_CURRENCY . "'");
        $lng = xtc_db_fetch_array($lng_query);
        if ($lng['languages_id'] == $lID) {
          xtc_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '' where configuration_key = 'DEFAULT_CURRENCY'");
        }
        xtc_db_query("delete from " . TABLE_CATEGORIES_DESCRIPTION . " where language_id = '" . $lID . "'");
        xtc_db_query("delete from " . TABLE_PRODUCTS_DESCRIPTION . " where language_id = '" . $lID . "'");
        xtc_db_query("delete from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . $lID . "'");
        xtc_db_query("delete from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where language_id = '" . $lID . "'");
        xtc_db_query("delete from " . TABLE_MANUFACTURERS_INFO . " where languages_id = '" . $lID . "'");
        xtc_db_query("delete from " . TABLE_ORDERS_STATUS . " where language_id = '" . $lID . "'");
        xtc_db_query("delete from " . TABLE_SHIPPING_STATUS . " where language_id = '" . $lID . "'");
        xtc_db_query("delete from " . TABLE_PRODUCTS_XSELL_GROUPS . " where language_id = '" . $lID . "'");
        xtc_db_query("delete from " . TABLE_LANGUAGES . " where languages_id = '" . $lID . "'");
        xtc_db_query("delete from " . TABLE_CONTENT_MANAGER . " where languages_id = '" . $lID . "'");
        xtc_db_query("delete from " . TABLE_PRODUCTS_CONTENT . " where languages_id = '" . $lID . "'");
        xtc_db_query("delete from " . TABLE_CUSTOMERS_STATUS . " where language_id = '" . $lID . "'");
        xtc_redirect(xtc_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page']));
        break;
      case 'delete':
        $lID = (int)$_GET['lID'];
        $lng_query = xtc_db_query("select code from " . TABLE_LANGUAGES . " where languages_id = '" . $lID . "'");
        $lng = xtc_db_fetch_array($lng_query);
        $remove_language = true;
        if ($lng['code'] == DEFAULT_LANGUAGE) {
          $remove_language = false;
          $messageStack->add(ERROR_REMOVE_DEFAULT_LANGUAGE, 'error');
        }
        // BOF - vr - 2009-12-11 - $lng must not be an array when entering header
        unset($lng);
        // EOF - vr - 2009-12-11 - $lng must not be an array when entering header
        break;
      case 'transfer':
        //echo '<pre>'.print_r($_POST,1).'</pre>'; EXIT;
        $lngID_from = (int)$_POST['lngID_from'];
        $lngID_to =(int)$_POST['lngID_to'];
        
        if ($lngID_from != $lngID_to) {
          // create additional categories_description records
          if (isset($_POST['c_desc'])) {
            xtc_db_query("delete from " . TABLE_CATEGORIES_DESCRIPTION . " where language_id = '" . $lngID_to . "'");
            $categories_query = xtc_db_query("select c.categories_id, cd.categories_name, cd.categories_description from " . TABLE_CATEGORIES . " c left join " . TABLE_CATEGORIES_DESCRIPTION . " cd on c.categories_id = cd.categories_id where cd.language_id = '" . $lngID_from . "'");
            while ($categories = xtc_db_fetch_array($categories_query)) {
              $sql_data_array = $categories;
              $sql_data_array['language_id'] = $lngID_to;
              xtc_db_perform(TABLE_CATEGORIES_DESCRIPTION,$sql_data_array);
            }
          }
          // create additional products_description records
          if (isset($_POST['p_desc'])) {
            xtc_db_query("delete from " . TABLE_PRODUCTS_DESCRIPTION . " where language_id = '" . $lngID_to . "'");
            $products_query = xtc_db_query("select p.products_id, pd.products_name, pd.products_description, pd.products_short_description, pd.products_order_description, pd.products_url from " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on p.products_id = pd.products_id where pd.language_id = '" . $lngID_from . "'");
            while ($products = xtc_db_fetch_array($products_query)) {
              $sql_data_array = $products;
              $sql_data_array['language_id'] = $lngID_to;
              xtc_db_perform(TABLE_PRODUCTS_DESCRIPTION,$sql_data_array);
            }
          }
          // create additional products_options records
          if (isset($_POST['p_opt'])) {
            xtc_db_query("delete from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . $lngID_to . "'");
            $products_options_query = xtc_db_query("select products_options_id, products_options_name from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . $lngID_from . "'");
            while ($products_options = xtc_db_fetch_array($products_options_query)) {
              $sql_data_array = $products_options;
              $sql_data_array['language_id'] = $lngID_to;
              xtc_db_perform(TABLE_PRODUCTS_OPTIONS,$sql_data_array);
            }
          }
          // create additional products_options_values records
          if (isset($_POST['p_opt_val'])) {
            xtc_db_query("delete from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where language_id = '" . $lngID_to . "'");
            $products_options_values_query = xtc_db_query("select products_options_values_id, products_options_values_name from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where language_id = '" . $lngID_from . "'");
            while ($products_options_values = xtc_db_fetch_array($products_options_values_query)) {
              $sql_data_array = $products_options_values;
              $sql_data_array['language_id'] = $lngID_to;
              xtc_db_perform(TABLE_PRODUCTS_OPTIONS_VALUES,$sql_data_array);
            }
          }
          // create additional manufacturers_info records
          if (isset($_POST['m_info'])) {
            xtc_db_query("delete from " . TABLE_MANUFACTURERS_INFO . " where languages_id = '" . $lngID_to . "'");
            $manufacturers_query = xtc_db_query("select m.manufacturers_id, mi.manufacturers_url from " . TABLE_MANUFACTURERS . " m left join " . TABLE_MANUFACTURERS_INFO . " mi on m.manufacturers_id = mi.manufacturers_id where mi.languages_id = '" . $lngID_from . "'");
            while ($manufacturers = xtc_db_fetch_array($manufacturers_query)) {
              $sql_data_array = $orders_status;
              $sql_data_array['languages_id'] = $lngID_to;
              xtc_db_perform(TABLE_MANUFACTURERS_INFO,$sql_data_array);              
            }
          }
          // create additional orders_status records
          if (isset($_POST['o_status'])) {
            xtc_db_query("delete from " . TABLE_ORDERS_STATUS . " where language_id = '" . $lngID_to . "'");
            $orders_status_query = xtc_db_query("select * from " . TABLE_ORDERS_STATUS . " where language_id = '" . $lngID_from . "'");
            while ($orders_status = xtc_db_fetch_array($orders_status_query)) {
              $sql_data_array = $orders_status;
              $sql_data_array['language_id'] = $lngID_to;
              xtc_db_perform(TABLE_ORDERS_STATUS,$sql_data_array);               
            }
          }
          // create additional shipping_status records
          if (isset($_POST['s_status'])) {
            xtc_db_query("delete from " . TABLE_SHIPPING_STATUS . " where language_id = '" . $lngID_to . "'");
            $shipping_status_query = xtc_db_query("select * from " . TABLE_SHIPPING_STATUS . " where language_id = '" . $lngID_from . "'");
            while ($shipping_status = xtc_db_fetch_array($shipping_status_query)) {
              $sql_data_array = $shipping_status;
              $sql_data_array['language_id'] = $lngID_to;
              xtc_db_perform(TABLE_SHIPPING_STATUS,$sql_data_array); 
            }
          }
          // create additional xsell_groups records
          if (isset($_POST['x_groups'])) {
            xtc_db_query("delete from " . TABLE_PRODUCTS_XSELL_GROUPS . " where language_id = '" . $lngID_to . "'");
            $xsell_grp_query = xtc_db_query("select products_xsell_grp_name_id,xsell_sort_order, groupname from " . TABLE_PRODUCTS_XSELL_GROUPS . " where language_id = '" . $lngID_from . "'");
            while ($xsell_grp = xtc_db_fetch_array($xsell_grp_query)) {
              $sql_data_array = $xsell_grp;
              $sql_data_array['language_id'] = $lngID_to;
              xtc_db_perform(TABLE_PRODUCTS_XSELL_GROUPS,$sql_data_array); 
            }
          }
          // create additional content_manager records
          if (isset($_POST['c_manager'])) {
            xtc_db_query("delete from " . TABLE_CONTENT_MANAGER . " where languages_id = '" . $lngID_to . "'");
            $content_manager_query = xtc_db_query("select * from " . TABLE_CONTENT_MANAGER . " where languages_id = '" . $lngID_from . "'");
            while ($content_manager = xtc_db_fetch_array($content_manager_query)) {
              $sql_data_array = $content_manager;
              $sql_data_array['languages_id'] = $lngID_to;
              unset($sql_data_array['content_id']);
              xtc_db_perform(TABLE_CONTENT_MANAGER,$sql_data_array);               
            }
          }
          // create additional product_contents records
          if (isset($_POST['p_content'])) {
            xtc_db_query("delete from " . TABLE_PRODUCTS_CONTENT . " where languages_id = '" . $lngID_to . "'");
            $products_content_query = xtc_db_query("select * from " . TABLE_PRODUCTS_CONTENT . " where languages_id = '" . $lngID_from . "'");
            while ($products_content = xtc_db_fetch_array($products_content_query)) {
              $sql_data_array = $products_content;
              $sql_data_array['languages_id'] = $lngID_to;
              unset($sql_data_array['content_id']);
              xtc_db_perform(TABLE_PRODUCTS_CONTENT,$sql_data_array);               
            }
          }
          $messageStack->add_session(TEXT_LANGUAGE_TRANSFER_OK, 'success');
        } else {
          $messageStack->add_session(TEXT_LANGUAGE_TRANSFER_ERR, 'error');
        }
        xtc_redirect(xtc_href_link(FILENAME_LANGUAGES, 'page=' . (int)$_GET['page']));
        break;
    }
  }


require (DIR_WS_INCLUDES.'head.php');
?>
<style>
/*
input[type=checkbox], input[type=radio] {
  vertical-align: middle;
  position: relative;
  bottom: 1px;
  float: left; display: inline;
}
*/
.fieldset{
  border: 1px solid #a3a3a3;
  background: #F1F1F1;
}
.transfer{
  margin-top:20px;
}
</style>
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
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_LANGUAGE_NAME; ?></td>
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_LANGUAGE_CODE; ?></td>
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_LANGUAGE_STATUS; ?></td>
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_LANGUAGE_STATUS_ADMIN; ?></td>
                  <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
                </tr>
                <?php
                $languages_query_raw = "SELECT *
                                          FROM " . TABLE_LANGUAGES . " 
                                      ORDER BY sort_order";
                $languages_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $languages_query_raw, $languages_query_numrows);
                $languages_query = xtc_db_query($languages_query_raw);

                while ($languages = xtc_db_fetch_array($languages_query)) {
                  if ((!isset($_GET['lID']) || (isset($_GET['lID']) && ($_GET['lID'] == $languages['languages_id']))) && !isset($lInfo) && (substr($action, 0, 3) != 'new')) {
                    $lInfo = new objectInfo($languages);
                  }
                  if (isset($lInfo) && (is_object($lInfo)) && ($languages['languages_id'] == $lInfo->languages_id) ) {
                    echo '                  <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_LANGUAGES, 'page=' . (int)$_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=edit') . '\'">' . "\n";
                  } else {
                    echo '                  <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_LANGUAGES, 'page=' . (int)$_GET['page'] . '&lID=' . $languages['languages_id']) . '\'">' . "\n";
                  }

                    if (DEFAULT_LANGUAGE == $languages['code']) {
                      echo '                <td class="dataTableContent"><b>' . $languages['name'] . ' (' . TEXT_DEFAULT . ')</b></td>' . "\n";
                    } else {
                      echo '                <td class="dataTableContent">' . $languages['name'] . '</td>' . "\n";
                    }
                    ?>
                    <td class="dataTableContent"><?php echo $languages['code']; ?></td>                            
                    <td class="dataTableContent">
                      <?php
                      if ($languages['status'] == 1) {
                        echo xtc_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10, 'style="margin-left: 5px;"') . '<a href="' . xtc_href_link(FILENAME_LANGUAGES, xtc_get_all_get_params(array('page', 'action', 'lID')) . 'action=setlflag&flag=0&lID=' . $languages['languages_id'] . '&page='.(int)$_GET['page']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10, 'style="margin-left: 5px;"') . '</a>';
                      } else {
                        echo '<a href="' . xtc_href_link(FILENAME_LANGUAGES, xtc_get_all_get_params(array('page', 'action', 'lID')) . 'action=setlflag&flag=1&lID=' . $languages['languages_id'].'&page='.(int)$_GET['page']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10, 'style="margin-left: 5px;"') . '</a>' . xtc_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10, 'style="margin-left: 5px;"');
                      }
                      ?>
                    </td>
                    <td class="dataTableContent">
                      <?php
                      if ($languages['status_admin'] == 1) {
                        echo xtc_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10, 'style="margin-left: 5px;"') . '<a href="' . xtc_href_link(FILENAME_LANGUAGES, xtc_get_all_get_params(array('page', 'action', 'lID')) . 'action=setladminflag&adminflag=0&lID=' . $languages['languages_id'] . '&page='.(int)$_GET['page']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10, 'style="margin-left: 5px;"') . '</a>';
                      } else {
                        echo '<a href="' . xtc_href_link(FILENAME_LANGUAGES, xtc_get_all_get_params(array('page', 'action', 'lID')) . 'action=setladminflag&adminflag=1&lID=' . $languages['languages_id'].'&page='.(int)$_GET['page']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10, 'style="margin-left: 5px;"') . '</a>' . xtc_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10, 'style="margin-left: 5px;"');
                      }
                      ?>
                    </td>                            
                    <td class="dataTableContent txta-r"><?php if (isset($lInfo) && (is_object($lInfo)) && ($languages['languages_id'] == $lInfo->languages_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $languages['languages_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
                  </tr>
                  <?php
                }
                ?>                                                
              </table>
                          
              <div class="smallText pdg2 flt-l"><?php echo $languages_split->display_count($languages_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, (int)$_GET['page'], TEXT_DISPLAY_NUMBER_OF_LANGUAGES); ?></div>
              <div class="smallText pdg2 flt-r"><?php echo $languages_split->display_links($languages_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, (int)$_GET['page']); ?></div>
             
              <?php
              if (empty($action)) {
                ?>
                <div class="clear"></div>                        
                <div class="smallText pdg2 flt-r"><?php echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_LANGUAGES, 'page=' . (int)$_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=new') . '">' . BUTTON_NEW_LANGUAGE . '</a>'; ?></div>
                
                <div class="clear"></div>                
                <div class="transfer main">
                <?php 
                    echo xtc_draw_form('languages', FILENAME_LANGUAGES, 'action=transfer', 'post', 'onsubmit="return confirmSubmit(\'\',\''. TEXT_LANGUAGE_TRANSFER_BTN .' ?\',this)"').PHP_EOL; 
                    echo '<fieldset class="fieldset">'.PHP_EOL;
                    echo '<legend><b>'. TEXT_LANGUAGE_TRANSFER_INFO . '</b></legend>'.PHP_EOL;
                    $lng_query = xtc_db_query("SELECT languages_id, name FROM ".TABLE_LANGUAGES."  ORDER BY sort_order");
                    while ($lng = xtc_db_fetch_array($lng_query)) {
                      $lng_array[] = array ('id' => $lng['languages_id'], 'text' => $lng['name']);
                    }
                    echo '<div class="mrg5">'. xtc_draw_checkbox_field('c_desc', '1', false) . ' ' . TABLE_CATEGORIES_DESCRIPTION .' <em>(categories_name, categories_description)</em>'.'</div>'.PHP_EOL;
                    echo '<div class="mrg5">'. xtc_draw_checkbox_field('p_desc', '1', false) . ' ' . TABLE_PRODUCTS_DESCRIPTION . ' <em>(products_name, products_description, products_short_description, products_order_description, products_url)</em>'.'</div>'.PHP_EOL;
                    echo '<div class="mrg5">'. xtc_draw_checkbox_field('p_opt', '1', false) . ' ' . TABLE_PRODUCTS_OPTIONS . ' <em>(products_options_name)</em>'.'</div>'.PHP_EOL;
                    echo '<div class="mrg5">'. xtc_draw_checkbox_field('p_opt_val', '1', false) . ' ' . TABLE_PRODUCTS_OPTIONS_VALUES . ' <em>(products_options_values_name)</em>'.'</div>'.PHP_EOL;
                    echo '<div class="mrg5">'. xtc_draw_checkbox_field('m_info', '1', false) . ' ' . TABLE_MANUFACTURERS_INFO . ' <em>(manufacturers_url)</em>'.'</div>'.PHP_EOL;
                    echo '<div class="mrg5">'. xtc_draw_checkbox_field('o_status', '1', false) . ' ' . TABLE_ORDERS_STATUS .' <em>(orders_status_name)</em>'.'</div>'.PHP_EOL;
                    echo '<div class="mrg5">'. xtc_draw_checkbox_field('s_status', '1', false) . ' ' . TABLE_SHIPPING_STATUS .' <em>(shipping_status_name)</em>'.'</div>'.PHP_EOL;
                    echo '<div class="mrg5">'. xtc_draw_checkbox_field('x_groups', '1', false) . ' ' . TABLE_PRODUCTS_XSELL_GROUPS . ' <em>(xsell_sort_order, groupname)</em>'.'</div>'.PHP_EOL;
                    echo '<div class="mrg5">'. xtc_draw_checkbox_field('c_manager', '1', false) . ' ' . TABLE_CONTENT_MANAGER . ' <em></em>'.'</div>'.PHP_EOL;
                    echo '<div class="mrg5">'. xtc_draw_checkbox_field('p_content', '1', false) . ' ' . TABLE_PRODUCTS_CONTENT . ' <em></em>'.'</div>'.PHP_EOL;
                    echo '<br />'.PHP_EOL;
                    echo '<div class="main important_info mrg5">'.TEXT_LANGUAGE_TRANSFER_INFO2.'</div>';
                    echo '<br />'.PHP_EOL;
                    echo '<div class="mrg5 smallText">'.TEXT_LANGUAGE_TRANSFER_FROM.xtc_draw_pull_down_menu('lngID_from', $lng_array, '' , 'style="width: 135px"').PHP_EOL;
                    echo TEXT_LANGUAGE_TRANSFER_TO. xtc_draw_pull_down_menu('lngID_to', $lng_array, '' , 'style="width: 135px"').PHP_EOL;
                    echo '<input type="submit" class="button" value="' . TEXT_LANGUAGE_TRANSFER_BTN . '" />'.PHP_EOL;
                    echo '</div>'.PHP_EOL;
                    echo '</fieldset>'.PHP_EOL;
                    echo '</form>'.PHP_EOL;
                ?>
                </div>
                <?php
              }
              ?>
                   
            </td>
            <?php
            $heading = array();
            $contents = array();
            switch ($action) {
              case 'new':
                $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_LANGUAGE . '</b>');
                $contents = array('form' => xtc_draw_form('languages', FILENAME_LANGUAGES, 'action=insert'));
                $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
                $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_NAME . '<br />' . xtc_draw_input_field('name'));
                $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_CODE . '<br />' . xtc_draw_input_field('code'));
                $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_CHARSET . '<br />' . xtc_draw_input_field('charset'));
                $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_IMAGE . '<br />' . xtc_draw_input_field('image', 'icon.gif'));
                $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_DIRECTORY . '<br />' . xtc_draw_input_field('directory'));
                //$contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_STATUS . '<br />' . xtc_draw_input_field('status'));
                //$contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_STATUS_ADMIN . '<br />' . xtc_draw_input_field('status_admin'));
                $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_SORT_ORDER . '<br />' . xtc_draw_input_field('sort_order'));
                $contents[] = array('text' => '<br />' . xtc_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
                $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" value="' . BUTTON_INSERT . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_LANGUAGES, 'page=' . (int)$_GET['page'] . '&lID=' . (int)$_GET['lID']) . '">' . BUTTON_CANCEL . '</a>');
                break;
              case 'edit':
                $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_LANGUAGE . '</b>');
                $contents = array('form' => xtc_draw_form('languages', FILENAME_LANGUAGES, 'page=' . (int)$_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=save'));
                $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
                $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_NAME . '<br />' . xtc_draw_input_field('name', $lInfo->name));
                $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_CODE . '<br />' . xtc_draw_input_field('code', $lInfo->code));
                $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_CHARSET . '<br />' . xtc_draw_input_field('charset', $lInfo->language_charset));
                $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_IMAGE . '<br />' . xtc_draw_input_field('image', $lInfo->image));
                $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_DIRECTORY . '<br />' . xtc_draw_input_field('directory', $lInfo->directory));
                //$contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_STATUS . '<br />' . xtc_draw_input_field('status', $lInfo->status));
                //$contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_STATUS_ADMIN . '<br />' . xtc_draw_input_field('status_admin', $lInfo->status_admin));
                $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_SORT_ORDER . '<br />' . xtc_draw_input_field('sort_order', $lInfo->sort_order));
                if (DEFAULT_LANGUAGE != $lInfo->code)
                  $contents[] = array('text' => '<br />' . xtc_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
                $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_UPDATE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_LANGUAGES, 'page=' . (int)$_GET['page'] . '&lID=' . $lInfo->languages_id) . '">' . BUTTON_CANCEL . '</a>');
                break;
              case 'delete':
                $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_LANGUAGE . '</b>');
                $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
                $contents[] = array('text' => '<br /><b>' . $lInfo->name . '</b>');
                $contents[] = array('align' => 'center', 'text' => '<br />' . (($remove_language) ? '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_LANGUAGES, 'page=' . (int)$_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=deleteconfirm') . '">' . BUTTON_DELETE . '</a>' : '') . ' <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_LANGUAGES, 'page=' . (int)$_GET['page'] . '&lID=' . $lInfo->languages_id) . '">' . BUTTON_CANCEL . '</a>');
                break;
              default:
                if (is_object($lInfo)) {
                  $heading[] = array('text' => '<b>' . $lInfo->name . '</b>');
                  $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=edit') . '">' . BUTTON_EDIT . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_LANGUAGES, 'page=' . (int)$_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=delete') . '">' . BUTTON_DELETE . '</a>');
                  $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_NAME . ' ' . $lInfo->name);
                  $contents[] = array('text' => TEXT_INFO_LANGUAGE_CODE . ' ' . $lInfo->code);
                  $contents[] = array('text' => TEXT_INFO_LANGUAGE_CHARSET_INFO . ' ' . $lInfo->language_charset);
                  $contents[] = array('text' => 'Language-ID:' . ' ' . $lInfo->languages_id);
                  $contents[] = array('text' => '<br />' . xtc_image(DIR_WS_LANGUAGES . $lInfo->directory . '/' . $lInfo->image, $lInfo->name));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_DIRECTORY . '<br />' . DIR_WS_LANGUAGES . '<b>' . $lInfo->directory . '</b>');
                  //$contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_STATUS . ' ' . $lInfo->status);
                  //$contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_STATUS_ADMIN . ' '. $lInfo->status_admin);
                  $contents[] = array('text' => '<br />' . TEXT_INFO_LANGUAGE_SORT_ORDER . ' ' . $lInfo->sort_order);
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