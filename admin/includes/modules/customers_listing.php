<?php
 /*-------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/
  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

  //display per page
  $cfg_max_display_results_key = 'MAX_DISPLAY_LIST_CUSTOMERS';
  $page_max_display_results = xtc_cfg_save_max_display_results($cfg_max_display_results_key);

 ?>

        <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_customers.png'); ?></div>
        <div class="flt-l">
          <div class="pageHeading"><?php echo HEADING_TITLE; ?></div>
          <div class="main pdg2"><?php echo BOX_HEADING_CUSTOMERS; ?></div>
        </div>
        <div class="pageHeading flt-l" style="margin: 3px 40px;"><?php echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CREATE_ACCOUNT) . '">' . BUTTON_CREATE_ACCOUNT . '</a>'; ?></div>

        <?php echo xtc_draw_form('status', FILENAME_CUSTOMERS, '', 'get');
          $select_data = array(
            array('id' => '', 'text' => ((!isset($_GET['status']) || $_GET['status'] == '') ? TEXT_SELECT : TEXT_ALL_CUSTOMERS)), 
          );
        ?>
        <div class="main mrg5"><?php echo HEADING_TITLE_STATUS . ' ' . xtc_draw_pull_down_menu('status', array_merge($select_data, $customers_statuses_array), isset($_GET['status']) ? $_GET['status'] : '', 'onChange="this.form.submit();"'); ?></div>
        </form>

        <div class="clear"></div>

        <table class="tableCenter">
          <tr>
            <td class="boxCenterLeft">
              <table class="tableBoxCenter collapse">
                <tr class="dataTableHeadingRow">
                  <td class="dataTableHeadingContent" style="width:40px;"><?php echo TABLE_HEADING_ACCOUNT_TYPE; ?></td>
                  <td class="dataTableHeadingContent" style="width:80px;"><?php echo TABLE_HEADING_CUSTOMERSCID.xtc_sorting(FILENAME_CUSTOMERS,'customers_cid'); ?></td>
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_LASTNAME.xtc_sorting(FILENAME_CUSTOMERS,'customers_lastname'); ?></td>
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_FIRSTNAME.xtc_sorting(FILENAME_CUSTOMERS,'customers_firstname'); ?></td>
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_EMAIL.xtc_sorting(FILENAME_CUSTOMERS,'customers_email_address'); ?></td>
                  <td class="dataTableHeadingContent"><?php echo TEXT_INFO_COUNTRY.xtc_sorting(FILENAME_CUSTOMERS,'customers_country'); ?></td>
                   <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_UMSATZ; ?></td>
                  <td class="dataTableHeadingContent"><?php echo HEADING_TITLE_STATUS; ?></td>
                  <?php
                  if (ACCOUNT_COMPANY_VAT_CHECK == 'true' && ACCOUNT_COMPANY == 'true') {
                  ?>
                  <td class="dataTableHeadingContent"><?php echo HEADING_TITLE_VAT; ?></td>
                  <?php
                  }
                  if (ACTIVATE_GIFT_SYSTEM=='true') {
                  ?>
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_AMOUNT; ?></td>
                  <?php
                  }
                  ?>
                  <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACCOUNT_CREATED.xtc_sorting(FILENAME_CUSTOMERS,'date_account_created'); ?></td>
                  <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
                </tr>
                <?php
                $search = '';
                if (isset($_GET['search']) && (xtc_not_null($_GET['search']))) {
                  $keywords = xtc_db_input(xtc_db_prepare_input($_GET['search']));
                  $search = "WHERE (c.customers_lastname LIKE '%".$keywords."%'
                                    OR c.customers_firstname LIKE '%".$keywords."%'
                                    OR CONCAT(c.customers_firstname,' ',c.customers_lastname) LIKE '%".$keywords."%'
                                    OR CONCAT(c.customers_lastname,' ',c.customers_firstname) LIKE '%".$keywords."%'
                                    OR c.customers_email_address LIKE '%".$keywords."%'
                                    OR c.customers_cid LIKE '%".$keywords."%'
                                    OR ab.entry_company LIKE '%".$keywords."%'
                                    OR ab1.entry_company LIKE '%".$keywords."%'
                                    OR ab1.entry_firstname LIKE '%".$keywords."%'
                                    OR ab1.entry_lastname LIKE '%".$keywords."%'
                                    OR CONCAT(ab1.entry_firstname,' ',ab1.entry_lastname) LIKE '%".$keywords."%'
                                    OR CONCAT(ab1.entry_lastname,' ',ab1.entry_firstname) LIKE '%".$keywords."%'
                                   )";
                }
                if (isset($_GET['search_email']) && (xtc_not_null($_GET['search_email']))) {
                  $keywords = xtc_db_input(xtc_db_prepare_input($_GET['search_email']));
                  $search = "WHERE (c.customers_email_address LIKE '%".$keywords."%')";
                }
                if (isset($_GET['status']) && $_GET['status'] != '') {
                  $search = "WHERE c.customers_status = '".(int)$_GET['status']."'";
                }

                if (isset($_GET['sorting']) && xtc_not_null($_GET['sorting'])) {
                  switch ($_GET['sorting']) {
                    case 'customers_firstname' :
                      $sort = 'ORDER BY c.customers_firstname';
                      break;
                    case 'customers_firstname-desc' :
                      $sort = 'ORDER BY c.customers_firstname DESC';
                      break;
                    case 'customers_lastname' :
                      $sort = 'ORDER BY c.customers_lastname';
                      break;
                    case 'customers_lastname-desc' :
                      $sort = 'ORDER BY c.customers_lastname DESC';
                      break;
                    case 'customers_country' :
                      $sort = 'ORDER BY ab.entry_country_id';
                      break;
                    case 'customers_country-desc' :
                      $sort = 'ORDER BY ab.entry_country_id DESC';
                      break;
                    case 'date_account_created' :
                      $sort = 'ORDER BY c.customers_date_added';
                      break;
                    case 'date_account_created-desc' :
                      $sort = 'ORDER BY c.customers_date_added DESC';
                      break;
                    case 'customers_cid' :
                      $sort = 'ORDER BY c.customers_cid';
                      break;
                    case 'customers_cid-desc' :
                      $sort = 'ORDER BY c.customers_cid DESC';
                      break;
                    case 'customers_email_address-desc' :
                      $sort = 'ORDER BY c.customers_email_address DESC';
                      break;
                    case 'customers_email_address' :
                      $sort = 'ORDER BY c.customers_email_address';
                      break;
                  }
                } else {
                  $sort = 'ORDER BY c.customers_date_added DESC';
                }

                $customers_query_raw = "SELECT c.customers_id,
                                               c.customers_cid,
                                               c.customers_vat_id,
                                               c.customers_vat_id_status,
                                               c.customers_status,
                                               c.customers_firstname,
                                               c.customers_lastname,
                                               c.customers_email_address,
                                               c.customers_default_address_id,
                                               c.customers_date_added as date_account_created,
                                               c.customers_last_modified as date_account_last_modified,
                                               c.member_flag,
                                               c.account_type,
                                               ab.entry_company,
                                               ab.entry_country_id,
                                               cgc.amount
                                          FROM ".TABLE_CUSTOMERS." c
                                          JOIN ".TABLE_ADDRESS_BOOK." ab
                                               ON c.customers_id = ab.customers_id
                                                  AND c.customers_default_address_id = ab.address_book_id
                                          JOIN ".TABLE_ADDRESS_BOOK." ab1
                                               ON c.customers_id = ab1.customers_id
                                     LEFT JOIN ".TABLE_COUPON_GV_CUSTOMER." cgc
                                               ON c.customers_id = cgc.customer_id
                                               ".$search."
                                      GROUP BY c.customers_id
                                               ".$sort;

                $customers_split = new splitPageResults($_GET['page'], $page_max_display_results, $customers_query_raw, $customers_query_numrows, 'c.customers_id');
                $customers_query = xtc_db_query($customers_query_raw);
                while ($customers = xtc_db_fetch_array($customers_query)) {
                  $umsatz_query = xtc_db_query("SELECT SUM(op.final_price) as ordersum
                                                  FROM ".TABLE_ORDERS_PRODUCTS." op
                                                  JOIN ".TABLE_ORDERS." o ON o.orders_id = op.orders_id
                                                 WHERE o.customers_id = '".(int)$customers['customers_id']."'");
                  $umsatz = xtc_db_fetch_array($umsatz_query);

                  if ((!isset($_GET['cID']) || (@$_GET['cID'] == $customers['customers_id'])) && !isset($cInfo)) {
                    $country_query = xtc_db_query("SELECT countries_name 
                                                     FROM ".TABLE_COUNTRIES." 
                                                    WHERE countries_id = '".(int)$customers['entry_country_id']."'");
                    $country = xtc_db_fetch_array($country_query);
                    $customers = array_merge($customers, (array)$country);                    
                    
                    $reviews_query = xtc_db_query("SELECT count(*) as number_of_reviews 
                                                     FROM ".TABLE_REVIEWS." 
                                                     WHERE customers_id = '".(int)$customers['customers_id']."'");
                    $reviews = xtc_db_fetch_array($reviews_query);
                    $customers = array_merge($customers, (array)$reviews);

                    $customers_info_query = xtc_db_query("SELECT customers_info_date_of_last_logon as date_last_logon,
                                                                 customers_info_number_of_logons as number_of_logons 
                                                            FROM ".TABLE_CUSTOMERS_INFO." 
                                                           WHERE customers_info_id = '".(int)$customers['customers_id']."'");
                    $customers_info = xtc_db_fetch_array($customers_info_query);
                    $customers = array_merge($customers, (array)$customers_info);

                    $cInfo = new objectInfo($customers);
                  }

                  if (isset($cInfo) && is_object($cInfo) && ($customers['customers_id'] == $cInfo->customers_id)) {
                    echo '          <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\''.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action', 'edit')).'cID='.$cInfo->customers_id.'&action=edit').'\'">'."\n";
                  } else {
                    echo '          <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\''.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'edit', 'action')).'cID='.$customers['customers_id']).'\'">'."\n";
                  }

                  $account_type = ($customers['account_type'] == 1) ? TEXT_GUEST : TEXT_ACCOUNT;
                  ?>

                  <td class="dataTableContent"><?php echo $account_type; ?></td>
                  <td class="dataTableContent"><?php echo $customers['customers_cid']; ?>&nbsp;</td>
                  <td class="dataTableContent"><?php echo $customers['customers_lastname']; ?></td>
                  <td class="dataTableContent"><?php echo $customers['customers_firstname']; ?></td>
                  <td class="dataTableContent"><?php echo $customers['customers_email_address']; ?></td>
                  <td class="dataTableContent"><?php echo xtc_get_country_name($customers['entry_country_id']); ?></td>
                  <?php
                  if ($umsatz['ordersum'] !='') {
                  ?>
                  <td class="dataTableContent"><?php if ($umsatz['ordersum']>0) { echo $currencies->format($umsatz['ordersum']);} ?></td>
                  <?php
                  } else {
                  ?>
                  <td class="dataTableContent"> --- </td>
                  <?php
                  }
                  ?>
                  <td class="dataTableContent"><?php echo $customers_statuses_id_array[$customers['customers_status']]['text'] . ' (' . $customers['customers_status'] . ')' ; ?></td><?php // web28 - 2011-10-31 - change  $customers_statuses_array  to $customers_statuses_id_array ?>
                  <?php
                    if (ACCOUNT_COMPANY_VAT_CHECK == 'true' && ACCOUNT_COMPANY == 'true') {
                      echo '<td class="dataTableContent">';
                      if ($customers['customers_vat_id']) {
                        $vatid_title = strip_tags(xtc_validate_vatid_status($customers['customers_id']));
                        $vatid_color = ($customers['customers_vat_id_status'] == 1 ? 'green' : 'red');
                        echo '<span title="'.$vatid_title.'" style="color:'.$vatid_color.';cursor:help;">' .
                             $customers['customers_vat_id'] .
                             '</span>';
                      }
                      echo '</td>';
                      /* with vatid status check icon
                      echo '<td class="dataTableContent" align="left" style="white-space: nowrap">';
                      if ($customers['customers_vat_id']) {
                        echo $customers['customers_vat_id'].'&nbsp;<img title="'.strip_tags(xtc_validate_vatid_status($customers['customers_id'])).'" alt="[i]" src="images/icon_status_'.($customers['customers_vat_id_status'] == 1 ? 'green' : 'red').'_light.gif" />';
                      }
                      echo '</td>';
                      /* old vatid status check
                      ?>
                      <td class="dataTableContent" align="left">
                        <?php
                        if ($customers['customers_vat_id']) {
                          if (xtc_not_null(xtc_validate_vatid_status($customers['customers_id']))) {
                            echo $customers['customers_vat_id'].'<br /><span style="font-size:8pt"><nobr>('.xtc_validate_vatid_status($customers['customers_id']).')</nobr></span>';
                          } else {
                            echo $customers['customers_vat_id'];
                          }
                        }
                        ?>
                      </td>
                      <?php
                      */
                    }
                    if (ACTIVATE_GIFT_SYSTEM=='true') {
                    ?>
                      <td class="dataTableContent"><?php if ($customers['amount']>0) { echo $currencies->format($customers['amount']);} ?></td>
                    <?php
                    }
                  ?>
                  <td class="dataTableContent txta-r"><?php echo xtc_date_short($customers['date_account_created']); ?>&nbsp;</td>
                  <td class="dataTableContent txta-r"><?php if (isset($cInfo) && is_object($cInfo) && ($customers['customers_id'] == $cInfo->customers_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array('cID')) . 'cID=' . $customers['customers_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
                </tr>
                <?php
                  }
                ?>
              </table>
              <div class="smallText pdg2 flt-l"><?php echo $customers_split->display_count($customers_query_numrows, $page_max_display_results, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_CUSTOMERS); ?></div>
              <div class="smallText pdg2 flt-r"><?php echo $customers_split->display_links($customers_query_numrows, $page_max_display_results, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], xtc_get_all_get_params(array('page', 'info', 'x', 'y', 'cID'))); ?></div>
              <?php echo draw_input_per_page($PHP_SELF,$cfg_max_display_results_key,$page_max_display_results); ?>
              <?php
              if (isset($_GET['search'])) {
              ?>
                <div class="clear"></div>
                <div class="smallText pdg2 flt-r"><?php echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CUSTOMERS) . '">' . BUTTON_RESET . '</a>'; ?></div>
              <?php
              }
              ?>
            </td>
              <?php
                $heading = array ();
                $contents = array ();
                switch ($action) {
                  case 'confirm' :
                    $heading[] = array ('text' => '<b>'.TEXT_INFO_HEADING_DELETE_CUSTOMER.'</b>');

                    $contents = array ('form' => xtc_draw_form('customers', FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action')).'cID='.$cInfo->customers_id.'&action=deleteconfirm'));
                    $contents[] = array ('text' => TEXT_DELETE_INTRO.'<br /><br /><b>'.$cInfo->customers_firstname.' '.$cInfo->customers_lastname.'</b>');
                    if ($cInfo->number_of_reviews > 0) {
                      $contents[] = array ('text' => '<br />'.xtc_draw_checkbox_field('delete_reviews', 'on', true).' '.sprintf(TEXT_DELETE_REVIEWS, $cInfo->number_of_reviews));
                    }
                    $contents[] = array ('align' => 'center', 'text' => '<br /><input type="submit" class="button" value="'.BUTTON_DELETE.'"><a class="button" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action')).'cID='.$cInfo->customers_id).'">'.BUTTON_CANCEL.'</a>');
                    break;

                  case 'address_book' :
                    $heading[] = array ('text' => '<b>'.TEXT_INFO_HEADING_ADRESS_BOOK.'</b>');

                    $contents = array ();
                    require_once (DIR_FS_INC.'xtc_get_address_format_id.inc.php');
                    require_once (DIR_FS_INC.'xtc_count_customer_address_book_entries.inc.php');

                    $addresses_query = xtc_db_query("-- admin/customers.php
                                                     SELECT address_book_id,
                                                            entry_firstname as firstname,
                                                            entry_lastname as lastname,
                                                            entry_company as company,
                                                            entry_street_address as street_address,
                                                            entry_suburb as suburb,
                                                            entry_city as city,
                                                            entry_postcode as postcode,
                                                            entry_state as state,
                                                            entry_zone_id as zone_id,
                                                            entry_country_id as country_id
                                                       FROM ".TABLE_ADDRESS_BOOK."
                                                      WHERE customers_id = '".(int) $cInfo->customers_id."'
                                                   ORDER BY address_book_id
                                                   ");

                    while ($addresses = xtc_db_fetch_array($addresses_query)) {
                      $format_id = xtc_get_address_format_id($addresses['country_id']);

                      if (isset($_GET['delete']) && $_GET['delete'] != '') {
                          if ($addresses['address_book_id'] == $_GET['delete']) {
                            if ($_GET['delete'] != $cInfo->customers_default_address_id) {
                              $contents[] = array ('text' => '<br/>');
                              $contents[] = array ('align' => 'left', 'text' => TEXT_INFO_DELETE);
                              $contents[] = array ('text' => '<br/>');
                              $contents[] = array ('text' =>  '<table style="font-size:11px; margin-left:20px;"><tr><td>' . xtc_address_format($format_id, $addresses, true, ' ', '<br />') . '</td></tr></table>');
                              $contents[] = array ('text' => '<br/>');
                              $contents[] = array ('align' => 'left', 'text' => '<a class="button" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'delete')).'cID='.$cInfo->customers_id).'">'.BUTTON_CANCEL.'</a>&nbsp;<a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action', 'delete')).'cID='.$cInfo->customers_id.'&action=delete_confirm_adressbook&address_book_id='.$addresses['address_book_id']).'">'.BUTTON_DELETE.'</a>');
                              $contents[] = array ('text' => '<br/>');
                            } else {
                              $contents[] = array ('text' => '<br/>');
                              $contents[] = array ('align' => 'left', 'text' => TEXT_INFO_DELETE_DEFAULT);
                              $contents[] = array ('text' => '<br/>');
                              $contents[] = array ('align' => 'left', 'text' => '<a class="button" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'delete')).'cID='.$cInfo->customers_id).'">'.BUTTON_BACK.'</a>');
                              $contents[] = array ('text' => '<br/>');
                            }
                          }
                      } else {
                        $contents[] = array ('text' => '<br/>');
                        $contents[] = array ('text' =>  '<table style="font-size:11px; margin-left:20px;"><tr><td>' . xtc_address_format($format_id, $addresses, true, ' ', '<br />') . '</td></tr></table>');
                        $contents[] = array ('text' => '<br/>');
                        $contents[] = array ('align' => 'left', 'text' => '<a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action', 'edit')).'cID='.$cInfo->customers_id.'&action=edit&edit='.$addresses['address_book_id']).'">'.BUTTON_EDIT.'</a>&nbsp;<a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action', 'delete', 'edit')).'cID='.$cInfo->customers_id.'&action=address_book&delete='.$addresses['address_book_id']).'">'.BUTTON_DELETE.'</a>'. (($cInfo->customers_default_address_id != $addresses['address_book_id'])?'&nbsp;<a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action', 'delete', 'default')).'cID='.$cInfo->customers_id.'&action=update_default_adressbook&default='.$addresses['address_book_id']).'">'.TEXT_SET_DEFAULT.'</a>':'') );
                        $contents[] = array ('text' =>  '<hr size="1"/>');
                      }

                    }
                    if (!isset($_GET['delete'])) {
                      $contents[] = array ('align' => 'right', 'text' => (xtc_count_customer_address_book_entries() < MAX_ADDRESS_BOOK_ENTRIES) ? '<a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action', 'edit')).'cID='.$cInfo->customers_id.'&action=edit&edit=0').'">'.BUTTON_INSERT.'</a>&nbsp;<a class="button" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action', 'delete')).'cID='.$cInfo->customers_id).'">'.BUTTON_CANCEL.'</a>' : '<a class="button" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action', 'delete')).'cID='.$cInfo->customers_id).'">'.BUTTON_CANCEL.'</a>');
                      $contents[] = array ('text' => '<br/>');
                    }
                    break;

                  case 'editstatus' :
                    if ($_GET['cID'] != 1) {
                      $customers_history_query = xtc_db_query("SELECT new_value, 
                                                                      old_value, 
                                                                      date_added, 
                                                                      customer_notified 
                                                                 FROM ".TABLE_CUSTOMERS_STATUS_HISTORY." 
                                                                WHERE customers_id = '".xtc_db_input($_GET['cID'])."' 
                                                             ORDER BY customers_status_history_id desc");
                      $heading[] = array ('text' => '<b>'.TEXT_INFO_HEADING_STATUS_CUSTOMER.'</b>');
                      $contents = array ('form' => xtc_draw_form('customers', FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action')).'cID='.$cInfo->customers_id.'&action=statusconfirm'));
                      $contents[] = array ('text' => '<br />'.xtc_draw_pull_down_menu('customers_status', $customers_statuses_array, $cInfo->customers_status));
                      $contents[] = array ('text' => '<table nowrap border="0" cellspacing="0" cellpadding="0"><tr><td style="border-bottom: 1px solid; border-color: #000000;" nowrap class="smallText" align="center"><b>'.TABLE_HEADING_NEW_VALUE.' </b></td><td style="border-bottom: 1px solid; border-color: #000000;" nowrap class="smallText" align="center"><b>'.TABLE_HEADING_DATE_ADDED.'</b></td></tr>');

                      if (xtc_db_num_rows($customers_history_query)) {
                        while ($customers_history = xtc_db_fetch_array($customers_history_query)) {
                          $contents[] = array ('text' => '<tr>'."\n".'<td class="smallText">'.$customers_statuses_id_array[$customers_history['new_value']]['text'].'</td>'."\n".'<td class="smallText" align="center">'.xtc_datetime_short($customers_history['date_added']).'</td>'."\n".'<td class="smallText" align="center">');// web28 - 2011-10-31 - change  $customers_statuses_array  to $customers_statuses_id_array
                          $contents[] = array ('text' => '</tr>'."\n");
                        }
                      } else {
                        $contents[] = array ('text' => '<tr>'."\n".' <td class="smallText" colspan="2">'.TEXT_NO_CUSTOMER_HISTORY.'</td>'."\n".' </tr>'."\n");
                      }
                      $contents[] = array ('text' => '</table>');
                      $contents[] = array ('align' => 'center', 'text' => '<br /><input type="submit" class="button" value="'.BUTTON_UPDATE.'"><a class="button" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action')).'cID='.$cInfo->customers_id).'">'.BUTTON_CANCEL.'</a>');
                    }
                    break;

                  case 'new_order' :
                    if (trim(MODULE_PAYMENT_INSTALLED) != '') {
                      $payments = explode(';', MODULE_PAYMENT_INSTALLED);
                      for ($i=0; $i<count($payments); $i++) {
                        if (file_exists(DIR_FS_LANGUAGES . $_SESSION['language'] . '/modules/payment/' . $payments[$i])) {
                          require_once(DIR_FS_LANGUAGES . $_SESSION['language'] . '/modules/payment/' . $payments[$i]);
                        }
                        $payment_modul = substr($payments[$i], 0, strrpos($payments[$i], '.'));
                        $payment_text = constant('MODULE_PAYMENT_'.strtoupper($payment_modul).'_TEXT_TITLE');
                        $payment_array[] = array('id' => $payment_modul,
                                                 'text' => $payment_text);
                      }
                    }
                    $shippings = explode(';', MODULE_SHIPPING_INSTALLED);
                    for ($i=0; $i<count($shippings); $i++) {
                      if (file_exists(DIR_FS_LANGUAGES . $_SESSION['language'] . '/modules/shipping/' . $shippings[$i])) {
                        require_once(DIR_FS_LANGUAGES . $_SESSION['language'] . '/modules/shipping/' . $shippings[$i]);
                      }
                      $shipping_modul = substr($shippings[$i], 0, strrpos($shippings[$i], '.'));
                      $shipping_text = constant('MODULE_SHIPPING_'.strtoupper($shipping_modul).'_TEXT_TITLE');
                      $shipping_array[] = array('id' => $shipping_modul,
                                                'text' => $shipping_text);
                    }
                    $heading[] = array ('text' => '<b>'.TEXT_INFO_HEADING_STATUS_NEW_ORDER.'</b>');
                    $contents = array ('form' => xtc_draw_form('customers', FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action')).'cID='.$cInfo->customers_id.'&action=new_order_confirm'));
                    $contents[] = array ('text' => TEXT_INFO_PAYMENT.'<br />'.xtc_draw_pull_down_menu('payment', $payment_array));
                    $contents[] = array ('text' => TEXT_INFO_SHIPPING.'<br />'.xtc_draw_pull_down_menu('shipping', $shipping_array));
                    $contents[] = array ('align' => 'center', 'text' => '<br /><input type="submit" class="button" value="'.BUTTON_SAVE.'"><a class="button" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action')).'cID='.$cInfo->customers_id).'">'.BUTTON_CANCEL.'</a>');
                    break;

                  default :
                    if (isset($cInfo) && is_object($cInfo)) {
                      $heading[] = array ('text' => '<b>'.$cInfo->customers_firstname.' '.$cInfo->customers_lastname.'</b>');
                      if ($cInfo->customers_id != 1 || ($cInfo->customers_id == 1 && $_SESSION['customer_id'] == 1)) {
                        $contents[] = array ('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action')).'cID='.$cInfo->customers_id.'&action=edit').'">'.BUTTON_EDIT.'</a>');
                      }
                      if ($cInfo->customers_id != 1) {
                        $contents[] = array ('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action')).'cID='.$cInfo->customers_id.'&action=confirm').'">'.BUTTON_DELETE.'</a>');
                      }
                      if ($cInfo->customers_id != 1) {
                        $contents[] = array ('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action')).'cID='.$cInfo->customers_id.'&action=editstatus').'">'.BUTTON_STATUS.'</a>');
                      }
                      if (($cInfo->customers_id != 1 || ($cInfo->customers_id == 1 && $_SESSION['customer_id'] == 1)) && $cInfo->customers_status == 0) {
                        $contents[] = array ('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_ACCOUNTING, xtc_get_all_get_params(array ('cID', 'action')).'cID='.$cInfo->customers_id).'">'.BUTTON_ACCOUNTING.'</a>');
                      }
                      if (($cInfo->customers_id != 1 || ($cInfo->customers_id == 1 && $_SESSION['customer_id'] == 1)) && $cInfo->customers_status == 0) {
                        $contents[] = array ('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action')).'cID='.$cInfo->customers_id.'&action=address_book').'">'.TEXT_INFO_HEADING_ADRESS_BOOK.'</a>');
                      }
                      $contents[] = array ('align' => 'center',
                                           'text' => '<a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_ORDERS, 'cID='.$cInfo->customers_id).'">'.BUTTON_ORDERS.'</a>
                                                      <a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_MAIL, 'customer='.$cInfo->customers_email_address).'">'.BUTTON_EMAIL.'</a>'
                                           );
                      $contents[] = array ('align' => 'center',
                                           'text' => '<a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action')).'cID='.$cInfo->customers_id.'&action=iplog').'">'.BUTTON_IPLOG.'</a>
                                                      <a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action')).'cID='.$cInfo->customers_id.'&action=new_order').'">'.BUTTON_NEW_ORDER.'</a>'
                                          );
                      if (ACTIVATE_GIFT_SYSTEM == 'true' && $cInfo->customers_status != DEFAULT_CUSTOMERS_STATUS_ID_GUEST) {
                        $contents[] = array ('align' => 'center', 'text' => '<a class="button" href="'.xtc_href_link(FILENAME_GV_MAIL, 'cID='.$cInfo->customers_id).'">'.BUTTON_SEND_COUPON.'</a>');
                      }
                      
                      $contents[] = array ('text' => '<br />'.TEXT_DATE_ACCOUNT_CREATED.' '.xtc_datetime_short($cInfo->date_account_created));
                      $contents[] = array ('text' => '<br />'.TEXT_DATE_ACCOUNT_LAST_MODIFIED.' '.xtc_datetime_short($cInfo->date_account_last_modified));
                      $contents[] = array ('text' => '<br />'.TEXT_INFO_DATE_LAST_LOGON.' '.xtc_datetime_short($cInfo->date_last_logon));
                      $contents[] = array ('text' => '<br />'.TEXT_INFO_NUMBER_OF_LOGONS.' '.$cInfo->number_of_logons);
                      $contents[] = array ('text' => '<br />'.TEXT_INFO_COUNTRY.' '.$cInfo->countries_name);
                      $contents[] = array ('text' => '<br />'.TEXT_INFO_NUMBER_OF_REVIEWS.' '.$cInfo->number_of_reviews);
                    }

                    if ($action == 'iplog') {
                      if (isset ($_GET['cID'])) {
                        $contents[] = array ('text' => '<br /><b>IPLOG:');
                        $customers_id = xtc_db_prepare_input($_GET['cID']);
                        $customers_log_info_array = xtc_get_user_info($customers_id);
                        if (xtc_db_num_rows($customers_log_info_array)) {
                          while ($customers_log_info = xtc_db_fetch_array($customers_log_info_array)) {
                            $contents[] = array ('text' => '<tr>'."\n".'<td class="smallText">'.xtc_datetime_short($customers_log_info['customers_ip_date']).' '.$customers_log_info['customers_ip'].' '.$customers_log_info['customers_advertiser']);
                          }
                        }
                      }
                    }
                    break;
                }

                if ((xtc_not_null($heading)) && (xtc_not_null($contents))) {
                  echo '            <td class="boxRight">'."\n";
                  $box = new box;
                  echo $box->infoBox($heading, $contents);
                  echo '          </td>'."\n";
                }
              ?>
          </tr>
        </table>