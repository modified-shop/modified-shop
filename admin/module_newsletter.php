<?php
/* --------------------------------------------------------------
   $Id$

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommercecoding standards www.oscommerce.com
   (c) 2003  nextcommerce (templates_boxes.php,v 1.14 2003/08/18); www.nextcommerce.org

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  // max email package  -> should be in admin area!
  define('NEWSLETTER_EXECUTE_LIMIT', '10'); // on each reload sending

  require('includes/application_top.php');

  require_once (DIR_FS_INC.'xtc_php_mail.inc.php');
  require_once (DIR_FS_INC.'xtc_wysiwyg.inc.php');
  
  require_once (DIR_FS_CATALOG.DIR_WS_CLASSES.'class.newsletter.php');
  
  $limits = 0;
  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  switch ($action) {
    case 'save':
      $id = xtc_db_prepare_input((int)$_POST['ID']);
      $customers_status = xtc_get_customers_statuses();

      $title = xtc_db_prepare_input($_POST['title']);
      if ($title == '') $title = 'no title';
      $cc = xtc_db_prepare_input($_POST['title']);
      $newsletter_body = xtc_db_prepare_input($_POST['title']);

      $rzp = '';
      if (isset($_POST['status'])) {
        for ($i=0,$n=count($customers_status); $i<$n; $i++) {
          if (isset($_POST['status'][$i]) && $_POST['status'][$i] == 'yes') {
            if ($rzp!='') $rzp .= ',';
            $rzp .= $customers_status[$i]['id'];
          }
        }
      }
      
      if (isset($_POST['status_all']) && $_POST['status_all'] == 'yes') $rzp.=',all';

      $error = false;
      if ($error == false) {
        $sql_data_array = array(
          'title' => $title,
          'status' => '0',
          'bc' => $rzp,
          'cc' => $cc,
          'date' => 'now()',
          'body' => $newsletter_body
        );

        if ($id != 0) {
           xtc_db_perform(TABLE_MODULE_NEWSLETTER, $sql_data_array, 'update', "newsletter_id = '" . $id . "'");
        } else {
           xtc_db_perform(TABLE_MODULE_NEWSLETTER, $sql_data_array);
           $id = xtc_db_insert_id();
        }
        
        // create temp table
        xtc_db_query("DROP TABLE IF EXISTS module_newsletter_temp_".$id);
        xtc_db_query("CREATE TABLE module_newsletter_temp_".$id."
                      (
                        id int(11) NOT NULL auto_increment,
                        customers_id int(11) NOT NULL default '0',
                        customers_status int(11) NOT NULL default '0',
                        customers_firstname varchar(64) NOT NULL default '',
                        customers_lastname varchar(64) NOT NULL default '',
                        customers_email_address text NOT NULL,
                        mail_key varchar(32) NOT NULL,
                        date datetime NOT NULL default '0000-00-00 00:00:00',
                        comment varchar(64) NOT NULL default '',
                        PRIMARY KEY (id)
                      )");

        // filling temp table with data!
        $flag = '';
        if (!strpos($rzp,'all')) $flag = 'true';
        $rzp = str_replace(',all', '', $rzp);
        $groups = explode(',', $rzp);

        for ($i=0,$n=count($groups); $i<$n; $i++) {
          // check if customer wants newsletter
          if (isset($_POST['status_all']) && $_POST['status_all'] == 'yes') {
            $customers_query = xtc_db_query("SELECT customers_id,
                                                    customers_firstname,
                                                    customers_lastname,
                                                    customers_email_address
                                               FROM ".TABLE_CUSTOMERS."
                                              WHERE customers_status='".$groups[$i]."'");
          } else {
            $customers_query = xtc_db_query("SELECT customers_email_address,
                                                    customers_id,
                                                    customers_firstname,
                                                    customers_lastname,
                                                    mail_key
                                               FROM ".TABLE_NEWSLETTER_RECIPIENTS."
                                              WHERE customers_status='".$groups[$i]."' 
                                                AND mail_status='1'");
          }
          while ($customers_data=xtc_db_fetch_array($customers_query)) {
            $sql_data_array = array(
              'customers_id' => $customers_data['customers_id'],
              'customers_status' => $groups[$i],
              'customers_firstname' => $customers_data['customers_firstname'],
              'customers_lastname' => $customers_data['customers_lastname'],
              'customers_email_address' => $customers_data['customers_email_address'],
              'mail_key' => $customers_data['mail_key'],
              'date' => 'now()'
            );

            xtc_db_perform('module_newsletter_temp_'.$id, $sql_data_array);
          }
        }
        xtc_redirect(xtc_href_link(FILENAME_MODULE_NEWSLETTER));
      }
      break;

    case 'delete':
      xtc_db_query("DELETE FROM ".TABLE_MODULE_NEWSLETTER." WHERE newsletter_id = '".(int)$_GET['ID']."'");
      xtc_db_query("DROP TABLE IF EXISTS module_newsletter_temp_".(int)$_GET['ID']);
      xtc_redirect(xtc_href_link(FILENAME_MODULE_NEWSLETTER));
      break;

    case 'send':
      xtc_redirect(xtc_href_link(FILENAME_MODULE_NEWSLETTER,'send=0&ID='.(int)$_GET['ID']));
      break;
  }

  // action for sending mails!
  if (isset($_GET['send']) && is_numeric($_GET['send'])) {
    $ajax_img = '<img src="images/loading.gif"/>' ;
    $ajax = '<script language="javascript" type="text/javascript">setTimeout("document.newsletter_send.submit()",1000);</script>';

    $limits = (int)$_GET['send'];
    $limit_query = xtc_db_query("SELECT count(*) as count
                                   FROM module_newsletter_temp_".(int)$_GET['ID']);
    $limit_data = xtc_db_fetch_array($limit_query);

    // select emailrange from db
    $email_query=xtc_db_query("SELECT *
                                 FROM module_newsletter_temp_".(int)$_GET['ID']."
                                LIMIT ".$limits.",".NEWSLETTER_EXECUTE_LIMIT);
    $email_data=array();
    while ($email_query_data=xtc_db_fetch_array($email_query)) {
      $email_data[] = array(
        'id' => $email_query_data['id'],
        'firstname' => $email_query_data['customers_firstname'],
        'lastname' => $email_query_data['customers_lastname'],
        'email' => $email_query_data['customers_email_address'],
        'key' => $email_query_data['mail_key']
      );
    }

    $break = 0;
    if ($limit_data['count'] < $limits) {
      $break = 1;
      unset($ajax);
    }

    $newsletters_query = xtc_db_query("SELECT *
                                         FROM ".TABLE_MODULE_NEWSLETTER."
                                        WHERE newsletter_id = '".(int)$_GET['ID']."'");
    $newsletters_data = xtc_db_fetch_array($newsletters_query);

    //Image path correction - absolute path needed
    $newsletters_data['body'] = str_replace('src="'.DIR_WS_CATALOG.'images/', 'src="'.((ENABLE_SSL === true) ? HTTPS_CATALOG_SERVER : HTTP_CATALOG_SERVER).DIR_WS_CATALOG.'images/', $newsletters_data['body']);
    
    $newsletter = new newsletter();
    
    for ($i=1; $i<=NEWSLETTER_EXECUTE_LIMIT; $i++) {
      if (!empty($email_data[$i-1])) {
        $remove_link = $newsletter->RemoveLinkAdmin($email_data[$i-1]['key'], $email_data[$i-1]['email']);
        
        $link1 = chr(13).chr(10).chr(13).chr(10).TEXT_NEWSLETTER_REMOVE.chr(13).chr(10).chr(13).chr(10).$remove_link;
        $link2 = '<br /><br /><hr>'.TEXT_NEWSLETTER_REMOVE.'<br /><a href="'.$remove_link.'">' . TEXT_REMOVE_LINK . '</a>';

        xtc_php_mail(EMAIL_SUPPORT_ADDRESS,
                     EMAIL_SUPPORT_NAME,
                     $email_data[$i-1]['email'],
                     $email_data[$i-1]['firstname'] . ' ' . $email_data[$i-1]['lastname'],
                     $newsletters_data['cc'],
                     EMAIL_SUPPORT_REPLY_ADDRESS,
                     EMAIL_SUPPORT_REPLY_ADDRESS_NAME,
                     '',
                     '',
                     $newsletters_data['title'],
                     $newsletters_data['body'].$link2,
                     $newsletters_data['body'].$link1
                     );
                     
        xtc_db_query("UPDATE module_newsletter_temp_".(int)$_GET['ID']." SET comment='send' WHERE id='".$email_data[$i-1]['id']."'");
      }
    }

    if ($break == 1) {
      $limit1_query = xtc_db_query("SELECT count(*) as count
                                      FROM module_newsletter_temp_".(int)$_GET['ID']."
                                     WHERE comment = 'send'");
      $limit1_data = xtc_db_fetch_array($limit1_query);
      if (($limit1_data['count'] - $limit_data['count']) <= 0) {
        xtc_db_query("UPDATE ".TABLE_MODULE_NEWSLETTER." 
                         SET status='1'
                       WHERE newsletter_id = '".(int)$_GET['ID']."'");
        xtc_redirect(xtc_href_link(FILENAME_MODULE_NEWSLETTER));
      } else {
        echo '<b>'.$limit1_data['count'].'<b> emails send<br />';
        echo '<b>'.$limit1_data['count']-$limit_data['count'].'<b> emails left';
      }
    }
  }

  require (DIR_WS_INCLUDES.'head.php');

  if (USE_WYSIWYG == 'true') {
    $query = xtc_db_query("SELECT code 
                             FROM ". TABLE_LANGUAGES ." 
                            WHERE languages_id='".(int)$_SESSION['languages_id']."'");
    $data = xtc_db_fetch_array($query);
    if ($action != '') {
      echo xtc_wysiwyg('newsletter',$data['code']);
    }
  }
?>
</head>
<body>
<!-- header //-->
<?php
  require(DIR_WS_INCLUDES . 'header.php');
  
  if (isset($_GET['ID'])) {
    echo xtc_draw_form('newsletter_send', FILENAME_MODULE_NEWSLETTER, 'send='.($limits + NEWSLETTER_EXECUTE_LIMIT).'&ID='.(int)$_GET['ID'], 'post');
    echo '</form>';
  }
?>
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
        <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_news.png'); ?></div>
        <div class="pageHeading"><?php echo HEADING_TITLE; ?><br /></div>
        <div class="main pdg2 flt-l">Tools</div>
        
        <div class="clear div_box brd-none pdg2">
          <div class="main important_info"><?php echo TEXT_NEWSLETTER_INFO; ?></div>
          <?php
          switch ($action) {
            default:
            // Get Customers Groups
            $customer_group_query = xtc_db_query("SELECT customers_status_name,
                                                         customers_status_id,
                                                         customers_status_image
                                                    FROM ".TABLE_CUSTOMERS_STATUS."
                                                   WHERE language_id='".(int)$_SESSION['languages_id']."'");
            $customer_group = array();
            while ($customer_group_data = xtc_db_fetch_array($customer_group_query)) {

              // get single users
              $group_query = xtc_db_query("SELECT count(*) as count
                                             FROM ".TABLE_NEWSLETTER_RECIPIENTS."
                                            WHERE mail_status='1'
                                              AND customers_status='".$customer_group_data['customers_status_id']."'");
              $group_data = xtc_db_fetch_array($group_query);
              $customer_group[] = array(
                'ID' => $customer_group_data['customers_status_id'],
                'NAME' => $customer_group_data['customers_status_name'],
                'IMAGE' => $customer_group_data['customers_status_image'],
                'USERS' => $group_data['count']
              );
            }
            ?>
            <div style="margin: 10px 2px"><?php echo '<a class="button" href="'.xtc_href_link(FILENAME_MODULE_NEWSLETTER,'action=new').'">'.BUTTON_NEW_NEWSLETTER.'</a>'; ?></div>
            <table class="tableConfig borderall">
              <tr>
                <tr class="dataTableHeadingRow">
                  <td class="dataTableHeadingContent" style="width:15%" ><?php echo TITLE_CUSTOMERS; ?></td>
                  <td class="dataTableHeadingContent" style="width:70%" ><?php echo TITLE_STK; ?></td>
                  <td class="dataTableHeadingContent txta-c" style="width:15%"><?php echo TITLE_ACTION; ?></td>
                </tr>
                <?php
                for ($i=0,$n=count($customer_group); $i<$n; $i++) {
                  ?>
                  <tr>
                    <td class="dataTableContent"><?php echo xtc_image(DIR_WS_ICONS . $customer_group[$i]['IMAGE'], ''); ?><?php echo $customer_group[$i]['NAME']; ?></td>
                    <td class="dataTableContent"><?php echo $customer_group[$i]['USERS']; ?></td>
                    <td class="dataTableContent txta-c">&nbsp;</td>
                  </tr>
                  <?php
                }
              ?>
            </table>
            <br />
            <?php
            // get data for newsletter overwiev
            $newsletters_query = xtc_db_query("SELECT *
                                                 FROM ".TABLE_MODULE_NEWSLETTER."
                                                WHERE status='0'");
            $news_data = array();
            while ($newsletters_data = xtc_db_fetch_array($newsletters_query)) {
               $news_data[] = array(
                'id' => $newsletters_data['newsletter_id'],
                'date' => $newsletters_data['date'],
                'title' => $newsletters_data['title']
              );
            }
            ?>
            <table class="tableBoxCenter collapse">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent" style="width:15%"><?php echo TITLE_DATE; ?></td>
                <td class="dataTableHeadingContent" style="width:70%"><?php echo TITLE_NOT_SEND; ?></td>
                <td class="dataTableHeadingContent txta-c" style="width:15%"><?php echo TITLE_ACTION; ?></td>
              </tr>
              <?php
                for ($i=0,$n=count($news_data); $i<$n; $i++) {
                  if ($news_data[$i]['id'] != '') {
                  ?>
                    <tr>
                      <td class="dataTableContent"><?php echo $news_data[$i]['date']; ?></td>
                      <td class="dataTableContent"><?php echo xtc_image(DIR_WS_CATALOG.'images/icons/arrow.gif'); ?>&nbsp;<a href="<?php echo xtc_href_link(FILENAME_MODULE_NEWSLETTER,'ID='.$news_data[$i]['id']); ?>"><b><?php echo $news_data[$i]['title']; ?></b></a></td>
                      <td class="dataTableContent txta-c">
                        <a href="<?php echo xtc_href_link(FILENAME_MODULE_NEWSLETTER,'action=edit&ID='.$news_data[$i]['id']); ?>">
                        <?php echo xtc_image(DIR_WS_ICONS.'icon_edit.gif', ICON_EDIT,'','').'</a>&nbsp;'; ?>
                        <a href="<?php echo xtc_href_link(FILENAME_MODULE_NEWSLETTER,'action=delete&ID='.$news_data[$i]['id']); ?>" onclick="return confirmLink('<?php echo DELETE_ENTRY; ?>', '', this)">
                        <?php echo xtc_image(DIR_WS_ICONS.'delete.gif', ICON_DELETE,'','','style="cursor:pointer"').'</a>&nbsp;&nbsp;'; ?>
                      </td>
                    </tr>
                    <?php
                    if (isset($_GET['ID']) && $_GET['ID'] != '' && $_GET['ID'] == $news_data[$i]['id']) {
                      $total_query = xtc_db_query("SELECT count(*) as count
                                                     FROM module_newsletter_temp_".(int)$_GET['ID']);
                      $total_data = xtc_db_fetch_array($total_query);
                      ?>
                      <tr>
                        <td class="dataTableContent_products" style="border-bottom: 1px solid; border-color: #f1f1f1;" align="left"></td>
                        <td colspan="2" class="dataTableContent_products" style="border-bottom: 1px solid; border-color: #f1f1f1;" align="left"><?php echo TEXT_SEND_TO.$total_data['count']; ?></td>
                      </tr>
                      <tr>
                        <td class="dataTableContent" style="border-bottom: 1px solid #999999;">&nbsp;</td>
                        <td class="dataTableContent" style="border-bottom: 1px solid #999999;">
                        <?php
                          // get data
                          $newsletters_query = xtc_db_query("SELECT *
                                                               FROM ".TABLE_MODULE_NEWSLETTER."
                                                              WHERE newsletter_id = '".(int)$_GET['ID']."'");
                          $newsletters_data = xtc_db_fetch_array($newsletters_query);

                          echo TEXT_TITLE.$newsletters_data['title'].'<br />';

                          $customers_status = xtc_get_customers_statuses();
                          for ($j=0,$k=count($customers_status); $j<$k; $j++) {
                            $newsletters_data['bc'] = str_replace($customers_status[$j]['id'], $customers_status[$j]['text'], $newsletters_data['bc']);
                          }

                          echo TEXT_TO.$newsletters_data['bc'].'<br />';
                          echo TEXT_CC.$newsletters_data['cc'].'<br /><br />'.TEXT_PREVIEW;
                          echo '<table style="border: 1px solid #a3a3a3; width:100%"><tr><td>'.$newsletters_data['body'].'</td></tr></table>';
                        ?>
                        </td>
                        <td class="dataTableContent txta-c" style="border-bottom: 1px solid; border-color: #999999; vertical-align: bottom!important;">
                          <?php if (!isset($ajax_img)) { ?>
                          <div class="pdg2"><a class="button" href="<?php echo xtc_href_link(FILENAME_MODULE_NEWSLETTER,'action=send&ID='.$news_data[$i]['id']); ?>"><?php echo BUTTON_SEND.'</a>'; ?></div>
                          <?php } ?>
                        </td>
                      </tr>
                      <?php
                      if (isset($ajax_img)) {
                        ?>
                        <tr>
                          <td class="dataTableContent_products" style="border-bottom: 1px solid #999999;"></td>
                          <td colspan="2" class="dataTableContent_products" style="border-bottom: 1px solid #999999;">
                            <?php echo TEXT_INFO_SENDING; ?>
                            <br/><br/><?php echo $ajax_img; ?><br/><br/>
                          </td>
                        </tr>
                        <?php
                      }
                    }
                  }
                }
                ?>
                </table>
                <br />
                <?php
                $newsletters_query = xtc_db_query("SELECT *
                                                     FROM ".TABLE_MODULE_NEWSLETTER."
                                                    WHERE status = '1'");
                $news_data = array();
                while ($newsletters_data=xtc_db_fetch_array($newsletters_query)) {
                  $news_data[] = array(
                    'id' => $newsletters_data['newsletter_id'],
                    'date'=>$newsletters_data['date'],
                    'title'=>$newsletters_data['title']
                  );
                }
                ?>
                <table class="tableBoxCenter collapse">
                  <tr class="dataTableHeadingRow">
                    <td class="dataTableHeadingContent" style="width:85%"><?php echo TITLE_SEND; ?></td>
                    <td class="dataTableHeadingContent txta-c" style="width:15%"><?php echo TITLE_ACTION; ?></td>
                  </tr>
                  <?php
                    for ($i=0,$n=count($news_data); $i<$n; $i++) {
                      if ($news_data[$i]['id'] != '') {
                      ?>
                        <tr>
                          <td class="dataTableContent" style="border-bottom: 1px solid; border-color: #f1f1f1;"><?php echo $news_data[$i]['date'].'    '; ?><b><?php echo $news_data[$i]['title']; ?></b></td>
                          <td class="dataTableContent txta-c" style="border-bottom: 1px solid; border-color: #f1f1f1;">
                            <a href="<?php echo xtc_href_link(FILENAME_MODULE_NEWSLETTER,'action=edit&ID='.$news_data[$i]['id']); ?>">
                            <?php echo xtc_image(DIR_WS_ICONS.'icon_edit.gif', ICON_EDIT,'','').'</a>&nbsp;'; ?>
                            <a href="<?php echo xtc_href_link(FILENAME_MODULE_NEWSLETTER,'action=delete&ID='.$news_data[$i]['id']); ?>" onclick="return confirmLink('<?php echo DELETE_ENTRY; ?>', '', this)">
                            <?php echo xtc_image(DIR_WS_ICONS.'delete.gif', ICON_DELETE,'','','style="cursor:pointer"').'</a>&nbsp;&nbsp;'; ?>
                          </td>
                        </tr>
                      <?php
                      }
                    }
                  ?>
                </table>
                <?php
                break;

              case 'edit':
                $newsletters_query = xtc_db_query("SELECT * 
                                                     FROM ".TABLE_MODULE_NEWSLETTER." 
                                                    WHERE newsletter_id = '".(int)$_GET['ID']."'");
                $newsletters_data = xtc_db_fetch_array($newsletters_query);
              
              case 'safe':
              case 'new':
                $customers_status = xtc_get_customers_statuses();
                echo xtc_draw_form('edit_newsletter',FILENAME_MODULE_NEWSLETTER,'action=save','post','enctype="multipart/form-data"').xtc_draw_hidden_field('ID', (isset($_GET['ID'])) ? (int)$_GET['ID'] : 0);
                ?>
                <table class="tableConfig borderall">
                  <tr>
                    <td class="dataTableConfig col-left"><?php echo TEXT_TITLE; ?></td>
                    <td class="dataTableConfig col-single-right"><?php echo xtc_draw_input_field('title', ((isset($newsletters_data)) ? $newsletters_data['title'] : ''), 'size=100'); ?></td>
                  </tr>
                  <tr>
                    <td class="dataTableConfig col-left"><?php echo TEXT_TO; ?></td>
                    <td class="dataTableConfig col-single-right">
                    <?php
                      for ($i=0,$n=count($customers_status); $i<$n; $i++) {
                        $group_query = xtc_db_query("SELECT count(*) as count
                                                       FROM ".TABLE_NEWSLETTER_RECIPIENTS."
                                                      WHERE mail_status = '1'
                                                        AND customers_status = '".$customers_status[$i]['id']."'");
                        $group_data = xtc_db_fetch_array($group_query);

                        $group_query = xtc_db_query("SELECT count(*) as count
                                                       FROM ".TABLE_CUSTOMERS."
                                                      WHERE customers_status = '".$customers_status[$i]['id']."'");
                        $group_data_all = xtc_db_fetch_array($group_query);

                        $bc_array = explode(',', (isset($newsletters_data)) ? $newsletters_data['bc'] : '');
                        echo xtc_draw_checkbox_field('status['.$i.']','yes', in_array($customers_status[$i]['id'], $bc_array)).' '.$customers_status[$i]['text'].'  <i>(<b>'.$group_data['count'].'</b>'.TEXT_USERS.$group_data_all['count'].TEXT_CUSTOMERS.'<br />';
                      }
                      echo xtc_draw_checkbox_field('status_all', 'yes',in_array('all', $bc_array)).' <b>'.TEXT_NEWSLETTER_ONLY.'</b>';
                    ?>
                    </td>
                  </tr>
                  <tr>
                    <td class="dataTableConfig col-left"><?php echo TEXT_CC; ?></td>
                    <td class="dataTableConfig col-single-right"><?php echo xtc_draw_input_field('cc', ((isset($newsletters_data)) ? $newsletters_data['cc'] : ''), 'size=100'); ?></td>
                  </tr>
                  <tr>
                    <td class="dataTableConfig col-left"><?php echo TEXT_BODY; ?></td>
                    <td class="dataTableConfig col-single-right"><?php echo xtc_draw_textarea_field('newsletter_body', 'soft', '150', '45', ((isset($newsletters_data)) ? stripslashes($newsletters_data['body']) : '')); ?></td>
                  </tr>
                </table>
                <div class="smallText flt-r mrg5"><a class="button" onclick="this.blur();" href="<?php echo xtc_href_link(FILENAME_MODULE_NEWSLETTER); ?>"><?php echo BUTTON_BACK; ?></a>
                <?php echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_SAVE . '"/>'; ?></div>
                </form>
                <?php
                break;
            }
          ?>
        </div>
     </td>
    <!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->
<?php if (isset($ajax)) echo $ajax;	?>
<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>