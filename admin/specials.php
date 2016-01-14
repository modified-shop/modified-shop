<?php
  /* --------------------------------------------------------------
   $Id: specials.php 4937 2013-06-17 09:19:54Z Tomcraft $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(specials.php,v 1.38 2002/05/16); www.oscommerce.com
   (c) 2003 nextcommerce (specials.php,v 1.9 2003/08/18); www.nextcommerce.org
   (c) 2006 XT-Commerce (specials.php 1125 2005-07-28)

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  require('includes/application_top.php');
  require(DIR_FS_CATALOG.DIR_WS_CLASSES . 'xtcPrice.php');
  $xtPrice = new xtcPrice(DEFAULT_CURRENCY,$_SESSION['customers_status']['customers_status_id']);
  require_once(DIR_FS_INC .'xtc_get_tax_rate.inc.php');
  
  //display per page
  $cfg_max_display_results_key = 'MAX_DISPLAY_SPECIALS_RESULTS';
  $page_max_display_results = xtc_cfg_save_max_display_results($cfg_max_display_results_key);

  $sID = (isset($_GET['sID']) ? (int)$_GET['sID'] : NULL);
  $page_id = (isset($_GET['page']) ? (int)$_GET['page'] : 0);
  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  if (xtc_not_null($action)) {
    switch ($action) {
      case 'setflag':
        xtc_set_specials_status($_GET['id'], $_GET['flag']);
        xtc_redirect(xtc_href_link(FILENAME_SPECIALS, 'page=' . $page_id));
        break;

      case 'insert':
      case 'update':
        if (PRICE_IS_BRUTTO=='true' && substr($_POST['specials_price'], -1) != '%'){
          $sql = "-- /admin/specials.php
                  SELECT tr.tax_rate
                    FROM " . TABLE_TAX_RATES . " tr,
                         " . TABLE_PRODUCTS . " p
                   WHERE tr.tax_class_id = p. products_tax_class_id
                     AND p.products_id = '". (int)$_POST['products_id'] . "'
                 ";

          $tax_query = xtc_db_query($sql);
          $tax = xtc_db_fetch_array($tax_query);
          $_POST['specials_price'] = ($_POST['specials_price']/($tax['tax_rate']+100)*100);
        }
        if (substr($_POST['specials_price'], -1) == '%') {
          $new_special_insert_query = xtc_db_query("-- /admin/specials.php
                                                      SELECT products_id,
                                                             products_tax_class_id,
                                                             products_price
                                                        FROM " . TABLE_PRODUCTS . "
                                                       WHERE products_id = '" . (int)$_POST['products_id'] . "'");
          $new_special_insert = xtc_db_fetch_array($new_special_insert_query);
          $_POST['products_price'] = $new_special_insert['products_price'];
          $_POST['specials_price'] = ($_POST['products_price'] - (($_POST['specials_price'] / 100) * $_POST['products_price']));
        }
        
        $expires_date = isset($_POST['specials_expires']) && !empty($_POST['specials_expires']) ? date('Y-m-d', strtotime($_POST['specials_expires'])).' 23:59:59' : '';
        $start_date = isset($_POST['specials_start']) && !empty($_POST['specials_start']) ? date('Y-m-d', strtotime($_POST['specials_start'])).' 00:00:00' : '';

        $sql_data_array = array('products_id' => (int)$_POST['products_id'],
                                'specials_quantity' => (int)$_POST['specials_quantity'],
                                'specials_new_products_price' => xtc_db_prepare_input($_POST['specials_price']),
                                'specials_date_added' => 'now()',
                                'specials_last_modified' => 'now()',
                                'start_date' => xtc_db_prepare_input($start_date),
                                'expires_date' => xtc_db_prepare_input($expires_date),
                                'status' => '1'
                                );
                                
        if ($action == 'insert') {
          unset($sql_data_array['specials_last_modified']);
          xtc_db_perform(TABLE_SPECIALS, $sql_data_array);
          $specials_id = xtc_db_insert_id();
        } else {
          unset($sql_data_array['specials_date_added']);
          unset($sql_data_array['status']);
          $specials_id = (int)$_POST['specials_id'];
          xtc_db_perform(TABLE_SPECIALS, $sql_data_array, 'update', "specials_id = '".$specials_id."'");
        }
        
        xtc_redirect(xtc_href_link(FILENAME_SPECIALS, 'page=' . $page_id . '&sID=' . $specials_id));
        break;

      case 'deleteconfirm':
        xtc_db_query("delete from " . TABLE_SPECIALS . " where specials_id = '" . xtc_db_prepare_input($sID) . "'");
        xtc_redirect(xtc_href_link(FILENAME_SPECIALS, 'page=' . $page_id));
        break;
    }
  }

require (DIR_WS_INCLUDES.'head.php');
?>
  <script type="text/javascript" src="includes/general.js"></script>
  <?php 
  if ( ($action == 'new') || ($action == 'edit') ) {
    //jQueryDatepicker
    require (DIR_WS_INCLUDES.'javascript/jQueryDateTimePicker/datepicker.js.php');  
  }
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
        <div class="pageHeading pdg2 mrg5"><?php echo HEADING_TITLE; ?></div>          
        <?php
        if ($action == 'new' || $action == 'edit') {
          $form_action = 'insert';
          $expires_date = '';
          if ( ($action == 'edit') && isset($sID) ) {
            $form_action = 'update';
            $product_query = xtc_db_query("-- /admin/specials.php
                                            SELECT
                                                  p.products_id,
                                                  p.products_model,
                                                  p.products_price,
                                                  p.products_tax_class_id,
                                                  s.specials_quantity,
                                                  s.specials_new_products_price,
                                                  s.start_date,
                                                  s.expires_date,
                                                  pd.products_name
                                             FROM " . TABLE_PRODUCTS . " p,
                                                  " . TABLE_PRODUCTS_DESCRIPTION . " pd,
                                                  " . TABLE_SPECIALS . " s
                                            WHERE p.products_id = pd.products_id
                                              AND pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                                              AND p.products_id = s.products_id
                                              AND s.specials_id = '" . $sID ."'");
            $product = xtc_db_fetch_array($product_query);
            $sInfo = new objectInfo($product);
            // build the expires date in the format YYYY-MM-DD
            if ($sInfo->expires_date != 0) {
              $expires_date = date('Y-m-d', strtotime($sInfo->expires_date));
            }	else {
              $expires_date = '';
            }
            // build the start date in the format YYYY-MM-DD
            if ($sInfo->start_date != 0) {
              $start_date = date('Y-m-d', strtotime($sInfo->start_date));
            }	else {
              $start_date = '';
            }
          } else {
            $sInfo = new objectInfo(array());
            // create an array of products on special, which will be excluded from the pull down menu of products
            // (when creating a new product on special)
            $specials_array = array();
            $specials_query = xtc_db_query("-- /admin/specials.php
                                            SELECT p.products_id
                                              FROM " . TABLE_PRODUCTS . " p,
                                                   " . TABLE_SPECIALS . " s
                                             WHERE s.products_id = p.products_id");
            while ($specials = xtc_db_fetch_array($specials_query)) {
              $specials_array[] = $specials['products_id'];
            }
          }

          $price = $sInfo->products_price;
          $new_price = $sInfo->specials_new_products_price;
          $new_price_netto = '';
          $price_netto = '';
          if (PRICE_IS_BRUTTO == 'true'){
            $price_netto = ' ' . TEXT_NETTO.'<strong>'.xtc_round($price,PRICE_PRECISION).'</strong>  ';
            if ($price > 0) {
              $new_price_netto = TEXT_NETTO.'<strong>'.xtc_round($new_price,PRICE_PRECISION).'</strong>';
            }            
            $price = ($price*(xtc_get_tax_rate($sInfo->products_tax_class_id)+100)/100);
            $new_price = ($new_price*(xtc_get_tax_rate($sInfo->products_tax_class_id)+100)/100);
          }
          $price = xtc_round($price,PRICE_PRECISION);
          $new_price = xtc_round($new_price,PRICE_PRECISION);           


          echo xtc_draw_form('new_special', FILENAME_SPECIALS, xtc_get_all_get_params(array('action', 'info', 'sID')) . 'action=' . $form_action);
          if ($form_action == 'update') { 
            echo xtc_draw_hidden_field('specials_id', $sID);                
          }
          ?>
          
          <table class="tableConfig">
            <tr>
              <td class="dataTableConfig col-left"><?php echo TEXT_SPECIALS_PRODUCT; ?></td>
              <td class="dataTableConfig col-middle"><?php echo ((isset($sInfo->products_name)) ? $sInfo->products_name . '<br/><small>(' . $xtPrice->xtcFormat($price,true). ' )' . $price_netto .'</small>'.xtc_draw_hidden_field('products_id', $sInfo->products_id) : xtc_draw_products_pull_down('products_id', 'style="font-size:10px"', $specials_array)); echo xtc_draw_hidden_field('products_price', $sInfo->products_price); ?></td>
              <td class="dataTableConfig col-right">&nbsp;</td>
            </tr>
            <?php
            if ($form_action == 'update') {
            ?>
            <tr>
              <td class="dataTableConfig col-left"><?php echo TEXT_GLOBAL_PRODUCTS_MODEL; ?>:</td>
              <td class="dataTableConfig col-middle"><?php echo $sInfo->products_model;?></td>
              <td class="dataTableConfig col-right">&nbsp;</td>
            </tr>
            <?php
            }
            ?>
            <tr>
              <td class="dataTableConfig col-left"><?php echo TEXT_SPECIALS_SPECIAL_PRICE; ?></td>
              <td class="dataTableConfig col-middle"><?php echo xtc_draw_input_field('specials_price', $new_price).'<br/>' .$new_price_netto;?></td>
              <td class="dataTableConfig col-right"><?php echo TEXT_SPECIALS_PRICE_TIP; ?></td>
            </tr>
            <tr>
              <td class="dataTableConfig col-left"><?php echo TEXT_SPECIALS_SPECIAL_QUANTITY; ?></td>
              <td class="dataTableConfig col-middle"><?php echo xtc_draw_input_field('specials_quantity', $sInfo->specials_quantity);?> </td>
              <td class="dataTableConfig col-right"><?php echo TEXT_SPECIALS_QUANTITY_TIP; ?></td>
            </tr>
            <tr>
              <td class="dataTableConfig col-left"><?php echo TEXT_SPECIALS_START_DATE; ?></td>
              <td class="dataTableConfig col-middle"><?php echo xtc_draw_input_field('specials_start', $start_date ,'id="DatepickerSpecialsStart"'); ?></td>
              <td class="dataTableConfig col-right"><?php echo TEXT_SPECIALS_START_DATE_TIP.SPECIALS_DATE_START_TT; ?>&nbsp;</td>
            </tr>
            <tr>
              <td class="dataTableConfig col-left"><?php echo TEXT_SPECIALS_EXPIRES_DATE; ?></td>
              <td class="dataTableConfig col-middle"><?php echo xtc_draw_input_field('specials_expires', $expires_date ,'id="DatepickerSpecials"'); ?></td>
              <td class="dataTableConfig col-right"><?php echo TEXT_SPECIALS_EXPIRES_DATE_TIP.SPECIALS_DATE_END_TT; ?>&nbsp;</td>
            </tr>
          </table>

          <div class="main mrg5 nobr">
           <?php echo (($form_action == 'insert') ?
           '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_INSERT . '"/>'
           :
           '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_UPDATE . '"/>'). '&nbsp;&nbsp;&nbsp;<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_SPECIALS, 'page=' . $page_id . '&sID=' . $sID) . '">' . BUTTON_CANCEL . '</a>'; ?>
          </div>
         </form>
      </td>                   
        <?php
        // BEGIN LISTING TABLE
        } else {
        ?>              
        <table class="tableCenter">
          <tr>
            <td class="boxCenterLeft">
              <table class="tableBoxCenter collapse">
                <tr class="dataTableHeadingRow">
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
                  <td class="dataTableHeadingContent"><?php echo TEXT_GLOBAL_PRODUCTS_MODEL; ?></td>
                  <td class="dataTableHeadingContent txta-c"><?php echo TABLE_HEADING_PRODUCTS_QUANTITY; ?></td>
                  <td class="dataTableHeadingContent txta-c"><?php echo TABLE_HEADING_SPECIALS_QUANTITY; ?></td>
                  <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_START_DATE; ?></td>
                  <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_EXPIRES_DATE; ?></td>
                  <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_PRODUCTS_PRICE; ?></td>
                  <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_STATUS; ?></td>
                  <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
                </tr>
                <?php
                $specials_query_raw = "-- /admin/specials.php
                                       SELECT p.products_id,
                                              p.products_model,
                                              p.products_quantity,
                                              p.products_price,
                                              p.products_tax_class_id,
                                              s.specials_id,
                                              s.specials_quantity,
                                              s.specials_new_products_price,
                                              s.specials_date_added,
                                              s.specials_last_modified,
                                              s.expires_date,
                                              s.start_date,
                                              s.date_status_change,
                                              s.status,
                                              pd.products_name
                                         FROM " . TABLE_PRODUCTS . " p
                                         JOIN " . TABLE_SPECIALS . " s
                                              ON p.products_id = s.products_id
                                         JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                              ON p.products_id = pd.products_id
                                                 AND pd.language_id = '" .(int) $_SESSION['languages_id'] . "'
                                     ORDER BY pd.products_name";
                                   
                $specials_split = new splitPageResults($page_id, $page_max_display_results, $specials_query_raw, $specials_query_numrows);
                $specials_query = xtc_db_query($specials_query_raw);
                while ($specials = xtc_db_fetch_array($specials_query)) {
                  $price=$specials['products_price'];
                  $new_price=$specials['specials_new_products_price'];
                  if (PRICE_IS_BRUTTO=='true'){
                    $price_netto=xtc_round($price,PRICE_PRECISION);
                    $new_price_netto=xtc_round($new_price,PRICE_PRECISION);
                    $price= ($price*(xtc_get_tax_rate($specials['products_tax_class_id'])+100)/100);
                    $new_price= ($new_price*(xtc_get_tax_rate($specials['products_tax_class_id'])+100)/100);
                  }
                  $specials['products_price']=xtc_round($price,PRICE_PRECISION);
                  $specials['specials_new_products_price']=xtc_round($new_price,PRICE_PRECISION);
                  if ((!isset($sID) || (isset($sID) && ($sID == $specials['specials_id']))) && !isset($sInfo) ) {
                    $products_query = xtc_db_query("select products_image from " . TABLE_PRODUCTS . " where products_id = '" . (int)$specials['products_id'] . "'");
                    $products = xtc_db_fetch_array($products_query);
                    $sInfo_array = xtc_array_merge($specials, $products);
                    $sInfo = new objectInfo($sInfo_array);
                    $sInfo->specials_new_products_price = $specials['specials_new_products_price'];
                    $sInfo->products_price = $specials['products_price'];
                  }
                  if (isset($sInfo) && is_object($sInfo) && ($specials['specials_id'] == $sInfo->specials_id) ) {
                    $tr_attributes = 'class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_SPECIALS, 'page=' . $page_id . '&sID=' . $sInfo->specials_id . '&action=edit') . '\'"';
                  } else {
                    $tr_attributes = 'class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_SPECIALS, 'page=' . $page_id . '&sID=' . $specials['specials_id']) . '\'"';
                  }
                  ?>
                  <tr <?php echo $tr_attributes;?>>
                    <td  class="dataTableContent"><?php echo $specials['products_name']; ?></td>
                    <td  class="dataTableContent"><?php echo $specials['products_model']; ?></td>
                    <td  class="dataTableContent txta-c"><?php echo $specials['products_quantity']; ?></td>
                    <td  class="dataTableContent txta-c"><?php echo $specials['specials_quantity']; ?></td>
                    <td  class="dataTableContent txta-r"><?php echo (isset($specials['start_date']) ? xtc_date_short($specials['start_date']): '&nbsp;'); ?></td>
                    <td  class="dataTableContent txta-r"><?php echo (isset($specials['expires_date']) ? xtc_date_short($specials['expires_date']): '&nbsp;'); ?></td>
                    <td  class="dataTableContent txta-r">
                      <span class="oldPrice">
                        <?php echo $xtPrice->xtcFormat($specials['products_price'],true); ?>
                      </span>
                      &nbsp;
                      <span class="specialPrice">
                        <?php echo $xtPrice->xtcFormat($specials['specials_new_products_price'],true); ?>
                      </span>
                    </td>
                    <td  class="dataTableContent txta-r">
                      <?php
                      if ($specials['status'] == '1') {
                        echo xtc_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10, 'style="margin-right:5px;"') . '<a href="' . xtc_href_link(FILENAME_SPECIALS, 'action=setflag&flag=0&id=' . $specials['specials_id'] . '&page=' . $page_id, 'NONSSL') . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>';
                      } else {
                        echo '<a href="' . xtc_href_link(FILENAME_SPECIALS, 'action=setflag&flag=1&id=' . $specials['specials_id'] . '&page=' . $page_id, 'NONSSL') . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10, 'style="margin-right:5px;"') . '</a>' . xtc_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
                      }
                      ?>
                      </td>
                      <td class="dataTableContent txta-r"><?php if (isset($sInfo) && (is_object($sInfo)) && ($specials['specials_id'] == $sInfo->specials_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_SPECIALS, 'page=' . $page_id . '&sID=' . $specials['specials_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
                    </tr>
                  <?php
                }
                ?>
              </table>
              <div class="smallText flt-l pdg2"><?php echo $specials_split->display_count($specials_query_numrows, $page_max_display_results, $page_id, TEXT_DISPLAY_NUMBER_OF_SPECIALS); ?></div>
              <div class="smallText flt-r pdg2"><?php echo $specials_split->display_links($specials_query_numrows, $page_max_display_results, MAX_DISPLAY_PAGE_LINKS, $page_id); ?></div>
              <?php echo draw_input_per_page($PHP_SELF,$cfg_max_display_results_key,$page_max_display_results); ?>
              <?php
              if (empty($action)) {
              ?>
                <div class="smallText flt-r pdg2"><?php echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_SPECIALS, 'page=' . $page_id . '&action=new') . '">' . BUTTON_NEW_PRODUCTS . '</a>'; ?></div>
              <?php
              }
              ?>                                
            </td>
            <?php
            $heading = array();
            $contents = array();
            switch ($action) {
              case 'delete':
                $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_SPECIALS . '</b>');
                $contents = array('form' => xtc_draw_form('specials', FILENAME_SPECIALS, 'page=' . $page_id . '&sID=' . $sInfo->specials_id . '&action=deleteconfirm'));
                $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
                $contents[] = array('text' => '<br /><b>' . $sInfo->products_name . '</b>');
                $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"/>&nbsp;<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_SPECIALS, 'page=' . $page_id . '&sID=' . $sInfo->specials_id) . '">' . BUTTON_CANCEL . '</a>');
                break;
              default:
                if (isset($sInfo) && is_object($sInfo)) {
                  $heading[] = array('text' => '<b>' . $sInfo->products_name . '</b>');
                  $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_SPECIALS, 'page=' . $page_id . '&sID=' . $sInfo->specials_id . '&action=edit') . '">' . BUTTON_EDIT . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_SPECIALS, 'page=' . $page_id . '&sID=' . $sInfo->specials_id . '&action=delete') . '">' . BUTTON_DELETE . '</a>');
                  $contents[] = array('text' => '<br />' . TEXT_INFO_DATE_ADDED . ' ' . xtc_date_short($sInfo->specials_date_added));
                  $contents[] = array('text' => '' . TEXT_INFO_LAST_MODIFIED . ' ' . xtc_date_short($sInfo->specials_last_modified));
                  $contents[] = array('align' => 'center', 'text' => '<br />' . xtc_product_thumb_image($sInfo->products_image, $sInfo->products_name, defined('SMALL_IMAGE_WIDTH') ? SMALL_IMAGE_WIDTH : '', defined('SMALL_IMAGE_HEIGHT') ? SMALL_IMAGE_HEIGHT : ''));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_ORIGINAL_PRICE . ' ' . $xtPrice->xtcFormat($sInfo->products_price,true));
                  $contents[] = array('text' => '' . TEXT_INFO_NEW_PRICE . ' ' . $xtPrice->xtcFormat($sInfo->specials_new_products_price,true));
                  $contents[] = array('text' => '' . TEXT_INFO_PERCENTAGE . ' ' . number_format(100 - (($sInfo->specials_new_products_price / $sInfo->products_price) * 100)) . '%');
                  $contents[] = array('text' => '<br />' . TEXT_INFO_START_DATE . ' <b>' . xtc_date_short($sInfo->start_date) . '</b>');
                  $contents[] = array('text' => TEXT_INFO_EXPIRES_DATE . ' <b>' . xtc_date_short($sInfo->expires_date) . '</b>');
                  $contents[] = array('text' => TEXT_INFO_STATUS_CHANGE . ' ' . xtc_date_short($sInfo->date_status_change));
                }
                break;
            }
            if ( (xtc_not_null($heading)) && (xtc_not_null($contents)) ) {
              echo '            <td class="boxRight">' . "\n";
              $box = new box;
              echo $box->infoBox($heading, $contents);
              echo '            </td>' . "\n";
            }
          }
          // END LISTING TABLE
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