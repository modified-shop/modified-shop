<?php
/* --------------------------------------------------------------
   $Id: module_newsletter.php 2906 2012-05-24 12:15:42Z gtb-modified $

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

  require_once(DIR_FS_CATALOG.DIR_WS_CLASSES.'class.phpmailer.php');
  require_once(DIR_FS_INC . 'xtc_php_mail.inc.php');
  require_once(DIR_FS_INC . 'xtc_wysiwyg.inc.php');

  switch ($_GET['action']) {  // actions for datahandling

    case 'save': // save newsletter

      $id=xtc_db_prepare_input((int)$_POST['ID']);
      $status_all=xtc_db_prepare_input($_POST['status_all']);
      if ($newsletter_title=='') $newsletter_title='no title';
      $customers_status=xtc_get_customers_statuses();

      $rzp='';
      for ($i=0,$n=sizeof($customers_status);$i<$n; $i++) {
          if (xtc_db_prepare_input($_POST['status'][$i])=='yes') {
              if ($rzp!='') $rzp.=',';
              $rzp.=$customers_status[$i]['id'];
          }
      }

      if (xtc_db_prepare_input($_POST['status_all'])=='yes') $rzp.=',all';

      $error=false; // reset error flag
      if ($error == false) {
        $sql_data_array = array( 'title'=> xtc_db_prepare_input($_POST['title']),
                                 'status' => '0',
                                 'bc'=>$rzp,
                                 'cc'=>xtc_db_prepare_input($_POST['cc']),
                                 'date' => 'now()',
                                 'body' => xtc_db_prepare_input($_POST['newsletter_body']));

        if ($id!='') {
           xtc_db_perform(TABLE_MODULE_NEWSLETTER, $sql_data_array, 'update', "newsletter_id = '" . $id . "'");
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
                            PRIMARY KEY  (id)
                            )");
        } else {
           xtc_db_perform(TABLE_MODULE_NEWSLETTER, $sql_data_array);
           // create temp table
           $id=xtc_db_insert_id();
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
                            PRIMARY KEY  (id)
                            )");
        }

        // filling temp table with data!
        $flag='';
        if (!strpos($rzp,'all')) $flag='true';
        $rzp=str_replace(',all','',$rzp);
        $groups=explode(',',$rzp);
        $sql_data_array='';

        for ($i=0,$n=sizeof($groups);$i<$n;$i++) {
        // check if customer wants newsletter

          if (xtc_db_prepare_input($_POST['status_all'])=='yes') {
          $customers_query=xtc_db_query("SELECT
                                        customers_id,
                                        customers_firstname,
                                        customers_lastname,
                                        customers_email_address
                                        FROM ".TABLE_CUSTOMERS."
                                        WHERE
                                        customers_status='".$groups[$i]."'");
          } else {
            $customers_query=xtc_db_query("SELECT
                                        customers_email_address,
                                        customers_id,
                                        customers_firstname,
                                        customers_lastname,
                                        mail_key
                                        FROM ".TABLE_NEWSLETTER_RECIPIENTS."
                                        WHERE
                                        customers_status='".$groups[$i]."' and
                                        mail_status='1'");
          }
          while ($customers_data=xtc_db_fetch_array($customers_query)) {
                $sql_data_array=array(
                                     'customers_id'=>$customers_data['customers_id'],
                                     'customers_status'=>$groups[$i],
                                     'customers_firstname'=>$customers_data['customers_firstname'],
                                     'customers_lastname'=>$customers_data['customers_lastname'],
                                     'customers_email_address'=>$customers_data['customers_email_address'],
                                     'mail_key'=>$customers_data['mail_key'],
                                     'date'=>'now()');

            xtc_db_perform('module_newsletter_temp_'.$id, $sql_data_array);
          }
        }
        xtc_redirect(xtc_href_link(FILENAME_MODULE_NEWSLETTER));
      }
      break;

    case 'delete':

      xtc_db_query("DELETE FROM ".TABLE_MODULE_NEWSLETTER." WHERE newsletter_id='".(int)$_GET['ID']."'");
      xtc_redirect(xtc_href_link(FILENAME_MODULE_NEWSLETTER));
      break;

    case 'send':
      xtc_redirect(xtc_href_link(FILENAME_MODULE_NEWSLETTER,'send=0&ID='.(int)$_GET['ID']));
  }

  // action for sending mails!
  if (isset($_GET['send']) && is_numeric($_GET['send'])) {

    //Animierte Gif-Datei und Hinweistext
    $ajax_img = '<img src="images/loading.gif"/>' ;
    $ajax = '<script language="javascript" type="text/javascript">setTimeout("document.newsletter_send.submit()",1000);</script>';

    $limits=intval($_GET['send']);
    $limit_query=xtc_db_query("SELECT count(*) as count
                                 FROM module_newsletter_temp_".(int)$_GET['ID']);
    $limit_data=xtc_db_fetch_array($limit_query);

    // select emailrange from db
    $email_query=xtc_db_query("SELECT customers_firstname,
                                      customers_lastname,
                                      customers_email_address,
                                      mail_key,
                                      id
                                 FROM module_newsletter_temp_".(int)$_GET['ID']."
                                LIMIT ".$limits.",".NEWSLETTER_EXECUTE_LIMIT);
    $email_data=array();
    while ($email_query_data=xtc_db_fetch_array($email_query)) {
      $email_data[]=array('id' => $email_query_data['id'],
                          'firstname'=>$email_query_data['customers_firstname'],
                          'lastname'=>$email_query_data['customers_lastname'],
                          'email'=>$email_query_data['customers_email_address'],
                          'key'=>$email_query_data['mail_key']);
    }

    $break=0;
    if ($limit_data['count']<$limits) {
       $break=1;
       unset($ajax);
    }

    $newsletters_query=xtc_db_query("SELECT title,
                                            body,
                                            bc,
                                            cc
                                       FROM ".TABLE_MODULE_NEWSLETTER."
                                      WHERE newsletter_id='".(int)$_GET['ID']."'");
    $newsletters_data=xtc_db_fetch_array($newsletters_query);

    //Image path correction - absolute path needed
    $newsletters_data['body'] = str_replace('src="'.DIR_WS_CATALOG.'images/', 'src="'.HTTP_CATALOG_SERVER.DIR_WS_CATALOG.'/images/', $newsletters_data['body']);

    for ($i=1;$i<=NEWSLETTER_EXECUTE_LIMIT;$i++) {
      if(!empty($email_data[$i-1])) {
        $link1 = chr(13).chr(10).chr(13).chr(10).TEXT_NEWSLETTER_REMOVE.chr(13).chr(10).chr(13).chr(10).HTTP_CATALOG_SERVER.DIR_WS_CATALOG.FILENAME_CATALOG_NEWSLETTER.'?action=remove&email='.$email_data[$i-1]['email'].'&key='.$email_data[$i-1]['key'];
        $link2 = $link2 = '<br /><br /><hr>'.TEXT_NEWSLETTER_REMOVE.'<br /><a href="'.HTTP_CATALOG_SERVER.DIR_WS_CATALOG.FILENAME_CATALOG_NEWSLETTER.'?action=remove&email='.$email_data[$i-1]['email'].'&key='.$email_data[$i-1]['key'].'">' . TEXT_REMOVE_LINK . '</a>';

        xtc_php_mail(EMAIL_SUPPORT_ADDRESS,
                     EMAIL_SUPPORT_NAME,
                     $email_data[$i-1]['email'] ,
                     $email_data[$i-1]['lastname'] . ' ' . $email_data[$i-1]['firstname'] ,
                     '',
                     EMAIL_SUPPORT_REPLY_ADDRESS,
                     EMAIL_SUPPORT_REPLY_ADDRESS_NAME,
                     '',
                     '',
                     $newsletters_data['title'],
                     $newsletters_data['body'].$link2,
                     $newsletters_data['body'].$link1);
        xtc_db_query("UPDATE module_newsletter_temp_".(int)$_GET['ID']." SET comment='send' WHERE id='".$email_data[$i-1]['id']."'");
      }
    }

    if ($break==1) {

      // finished
      $limit1_query=xtc_db_query("SELECT count(*) as count
                                FROM module_newsletter_temp_".(int)$_GET['ID']."
                                WHERE comment='send'");
      $limit1_data=xtc_db_fetch_array($limit1_query);
      if ($limit1_data['count']-$limit_data['count']<=0) {
        xtc_db_query("UPDATE ".TABLE_MODULE_NEWSLETTER." SET status='1' WHERE newsletter_id='".(int)$_GET['ID']."'");
        xtc_redirect(xtc_href_link(FILENAME_MODULE_NEWSLETTER));
      } else {
        echo '<b>'.$limit1_data['count'].'<b> emails send<br />';
        echo '<b>'.$limit1_data['count']-$limit_data['count'].'<b> emails left';
      }
    }
  }

  require (DIR_WS_INCLUDES.'head.php');
?>
<?php
  if (USE_WYSIWYG=='true') {
    $query=xtc_db_query("SELECT code FROM ". TABLE_LANGUAGES ." WHERE languages_id='".$_SESSION['languages_id']."'");
    $data=xtc_db_fetch_array($query);
    if ($_GET['action']!='')
      echo xtc_wysiwyg('newsletter',$data['code']);
  }
?>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<!-- header //-->
<?php
  require(DIR_WS_INCLUDES . 'header.php');
  echo '<form name="newsletter_send" action="'. xtc_href_link(FILENAME_MODULE_NEWSLETTER,'send='.($limits + NEWSLETTER_EXECUTE_LIMIT).'&ID='.(int)$_GET['ID']) .'" method="POST"></form>';
?>
<!-- header_eof //-->
<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td class="columnLeft2" width="<?php echo BOX_WIDTH; ?>" valign="top">
      <table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
      <!-- left_navigation //-->
      <?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
      <!-- left_navigation_eof //-->
      </table>
    </td>
    <!-- body_text //-->
    <td width="100%" valign="top">
    <div style="width:900px">
        <div style="float:left; width:80px;"><?php echo xtc_image(DIR_WS_ICONS.'heading_news.gif'); ?></div>
        <div class="pageHeading"><?php echo HEADING_TITLE; ?><br /></div>
        <div class="main" valign="top">Tools</div>
        <div class="main" style="clear:both; border: 1px red solid; padding:5px; background: #FFD6D6;"><?php echo TEXT_NEWSLETTER_INFO; ?></div>

      <?php
      if ($_GET['send']) {
        echo '<div>Sending...</div>';
      }
      ?>

        <table width="100%" border="0">
          <tr>
            <td>
            <?php
            // Default seite
            switch ($_GET['action']) {

              default:

              // Get Customers Groups
              $customer_group_query=xtc_db_query("SELECT
                                                  customers_status_name,
                                                  customers_status_id,
                                                  customers_status_image
                                             FROM ".TABLE_CUSTOMERS_STATUS."
                                            WHERE language_id='".$_SESSION['languages_id']."'");
              $customer_group=array();
              while ($customer_group_data=xtc_db_fetch_array($customer_group_query)) {

                // get single users
                $group_query=xtc_db_query("SELECT count(*) as count
                                             FROM ".TABLE_NEWSLETTER_RECIPIENTS."
                                            WHERE mail_status='1'
                                              AND customers_status='".$customer_group_data['customers_status_id']."'");
                $group_data=xtc_db_fetch_array($group_query);
                $customer_group[]=array('ID'=>$customer_group_data['customers_status_id'],
                                        'NAME'=>$customer_group_data['customers_status_name'],
                                        'IMAGE'=>$customer_group_data['customers_status_image'],
                                        'USERS'=>$group_data['count']);
              }
              ?>
              <br />
              <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td>
                    <table border="0" width="100%" cellspacing="0" cellpadding="2">
                      <tr class="dataTableHeadingRow">
                        <td class="dataTableHeadingContent" width="150" ><?php echo TITLE_CUSTOMERS; ?></td>
                        <td class="dataTableHeadingContent"  ><?php echo TITLE_STK; ?></td>
                      </tr>
                      <?php
                      for ($i=0,$n=sizeof($customer_group); $i<$n; $i++) {
                      ?>
                      <tr>
                        <td class="dataTableContent" style="border-bottom: 1px solid; border-color: #f1f1f1;" valign="middle" align="left"><?php echo xtc_image(DIR_WS_ICONS . $customer_group[$i]['IMAGE'], ''); ?><?php echo $customer_group[$i]['NAME']; ?></td>
                        <td class="dataTableContent" style="border-bottom: 1px solid; border-color: #f1f1f1;" align="left"><?php echo $customer_group[$i]['USERS']; ?></td>
                      </tr>
                      <?php
                      }
                      ?>
                    </table>
                  </td>
                  <td width="30%" align="right" valign="top"">
                  <?php
                  echo '<a class="button" href="'.xtc_href_link(FILENAME_MODULE_NEWSLETTER,'action=new').'">'.BUTTON_NEW_NEWSLETTER.'</a>';
                  ?>
                  </td>
                </tr>
              </table>
              <br />
              <?php
              // get data for newsletter overwiev
              $newsletters_query=xtc_db_query("SELECT
                                               newsletter_id,date,title
                                          FROM ".TABLE_MODULE_NEWSLETTER."
                                         WHERE status='0'");
              $news_data=array();
              while ($newsletters_data=xtc_db_fetch_array($newsletters_query)) {
                 $news_data[]=array('id' => $newsletters_data['newsletter_id'],
                                    'date'=>$newsletters_data['date'],
                                    'title'=>$newsletters_data['title']);
              }
              ?>
              <table border="0" width="100%" cellspacing="0" cellpadding="2">
                <tr class="dataTableHeadingRow">
                  <td class="dataTableHeadingContent" width="30"><?php echo TITLE_DATE; ?></td>
                  <td class="dataTableHeadingContent" width="80%"><?php echo TITLE_NOT_SEND; ?></td>
                  <td class="dataTableHeadingContent"><?php echo TITLE_ACTION; ?></td>
                </tr>
              <?php
              for ($i=0,$n=sizeof($news_data); $i<$n; $i++) {
                if ($news_data[$i]['id']!='') {
                ?>
                  <tr>
                    <td class="dataTableContent" style="border-bottom: 1px solid; border-color: #f1f1f1;" align="left"><?php echo $news_data[$i]['date']; ?></td>
                    <td class="dataTableContent" style="border-bottom: 1px solid; border-color: #f1f1f1;" valign="middle" align="left"><?php echo xtc_image(DIR_WS_CATALOG.'images/icons/arrow.gif'); ?><a href="<?php echo xtc_href_link(FILENAME_MODULE_NEWSLETTER,'ID='.$news_data[$i]['id']); ?>"><b><?php echo $news_data[$i]['title']; ?></b></a></td>
                    <td class="dataTableContent" style="border-bottom: 1px solid; border-color: #f1f1f1;" align="left"></td>
                  </tr>
                  <?php
                  if ($_GET['ID']!='' && $_GET['ID']==$news_data[$i]['id']) {

                    $total_query=xtc_db_query("SELECT
                                               count(*) as count
                                               FROM module_newsletter_temp_".(int)$_GET['ID']."");
                    $total_data=xtc_db_fetch_array($total_query);
                    ?>
                    <tr>
                      <td class="dataTableContent_products" style="border-bottom: 1px solid; border-color: #f1f1f1;" align="left"></td>
                      <td colspan="2" class="dataTableContent_products" style="border-bottom: 1px solid; border-color: #f1f1f1;" align="left"><?php echo TEXT_SEND_TO.$total_data['count']; ?></td>
                    </tr>
                      <td class="dataTableContent" valign="top" style="border-bottom: 1px solid; border-color: #999999;" align="left">
                        <a class="button" href="<?php echo xtc_href_link(FILENAME_MODULE_NEWSLETTER,'action=delete&ID='.$news_data[$i]['id']); ?>" onclick="return confirm('<?php echo CONFIRM_DELETE; ?>')"><?php echo BUTTON_DELETE.'</a><br />'; ?>
                        <a class="button" href="<?php echo xtc_href_link(FILENAME_MODULE_NEWSLETTER,'action=edit&ID='.$news_data[$i]['id']); ?>"><?php echo BUTTON_EDIT.'</a>'; ?>
                        <br /><br /><div style="height: 1px; background: Black; margin: 3px 0;"></div>
                        <a class="button" href="<?php echo xtc_href_link(FILENAME_MODULE_NEWSLETTER,'action=send&ID='.$news_data[$i]['id']); ?>"><?php echo BUTTON_SEND.'</a>'; ?>
                      </td>
                      <td class="dataTableContent" style="border-bottom: 1px solid; border-color: #999999; text-align: left;">
                      <?php

                      // get data
                      $newsletters_query=xtc_db_query("SELECT
                                                     title,body,cc,bc
                                                    FROM ".TABLE_MODULE_NEWSLETTER."
                                                    WHERE newsletter_id='".(int)$_GET['ID']."'");
                      $newsletters_data=xtc_db_fetch_array($newsletters_query);

                      echo TEXT_TITLE.$newsletters_data['title'].'<br />';

                      $customers_status=xtc_get_customers_statuses();
                      for ($i=0,$n=sizeof($customers_status);$i<$n; $i++) {
                        $newsletters_data['bc']=str_replace($customers_status[$i]['id'],$customers_status[$i]['text'],$newsletters_data['bc']);
                      }

                      echo TEXT_TO.$newsletters_data['bc'].'<br />';
                      echo TEXT_CC.$newsletters_data['cc'].'<br /><br />'.TEXT_PREVIEW;
                      echo '<table style="border-color: #cccccc; border: 1px solid;" width="100%"><tr><td>'.$newsletters_data['body'].'</td></tr></table>';
                      ?>

                      </td>
                      <td class="dataTableContent" style="border-bottom: 1px solid; border-color: #999999; text-align: center; vertical-align:top;">
                      <?php
                      if (isset($ajax_img)) {
                        echo '<p>Bitte Warten, der Newsletter wird versendet. Dies kann eineige Zeit in Anspruch nehmen.</p><br/><br/>' . $ajax_img . '<br/><br/>';
                      } else {
                        echo '&nbsp;';
                      }
                      ?>
                      </td>
                    </tr>
                    <?php
                  }
                }
              }
              ?>
              </table>
              <br /><br />
              <?php
              $newsletters_query=xtc_db_query("SELECT
                                               newsletter_id,date,title
                                          FROM ".TABLE_MODULE_NEWSLETTER."
                                         WHERE status='1'");
              $news_data=array();
              while ($newsletters_data=xtc_db_fetch_array($newsletters_query)) {

              $news_data[]=array('id' => $newsletters_data['newsletter_id'],
                                 'date'=>$newsletters_data['date'],
                                 'title'=>$newsletters_data['title']);
              }
              ?>
              <table border="0" width="100%" cellspacing="0" cellpadding="2">
                <tr class="dataTableHeadingRow">
                  <td class="dataTableHeadingContent" width="80%" ><?php echo TITLE_SEND; ?></td>
                  <td class="dataTableHeadingContent"><?php echo TITLE_ACTION; ?></td>
                </tr>
              <?php
              for ($i=0,$n=sizeof($news_data); $i<$n; $i++) {
                if ($news_data[$i]['id']!='') {
                ?>
                  <tr>
                    <td class="dataTableContent" style="border-bottom: 1px solid; border-color: #f1f1f1;" valign="middle" align="left"><?php echo $news_data[$i]['date'].'    '; ?><b><?php echo $news_data[$i]['title']; ?></b></td>
                    <td class="dataTableContent" style="border-bottom: 1px solid; border-color: #f1f1f1;" align="left">
                      <a href="<?php echo xtc_href_link(FILENAME_MODULE_NEWSLETTER,'action=edit&ID='.$news_data[$i]['id']); ?>">
                      <?php echo xtc_image(DIR_WS_ICONS.'icon_edit.gif', ICON_EDIT,'','').'  '.TEXT_EDIT.'</a>'; ?>
                      <a href="<?php echo xtc_href_link(FILENAME_MODULE_NEWSLETTER,'action=delete&ID='.$news_data[$i]['id']); ?>" onclick="return confirm('<?php echo CONFIRM_DELETE; ?>')">
                      <?php echo xtc_image(DIR_WS_ICONS.'delete.gif', ICON_DELETE,'','','style="cursor:pointer" onclick="return confirm(\''.DELETE_ENTRY.'\')"').'  '.TEXT_DELETE.'</a>&nbsp;&nbsp;'; ?>
                    </td>
                  </tr>
                <?php
                }
              }
              ?>
              </table>
              <?php
              // end default page
              break;

              case 'edit':

                $newsletters_query=xtc_db_query("SELECT title,body,cc,bc FROM ".TABLE_MODULE_NEWSLETTER." WHERE newsletter_id='".(int)$_GET['ID']."'");
                $newsletters_data=xtc_db_fetch_array($newsletters_query);

              case 'safe':
              case 'new':  // action for NEW newsletter!

                $customers_status=xtc_get_customers_statuses();
                echo xtc_draw_form('edit_newsletter',FILENAME_MODULE_NEWSLETTER,'action=save','post','enctype="multipart/form-data"').xtc_draw_hidden_field('ID',$_GET['ID']);
                ?>

                <br /><br />
                <table class="main" width="100%" border="0">
                  <tr>
                    <td width="10%"><?php echo TEXT_TITLE; ?></td>
                    <td width="90%"><?php echo xtc_draw_input_field('title',$newsletters_data['title'],'size=100'); ?></td>
                  </tr>
                  <tr>
                    <td width="10%"><?php echo TEXT_TO; ?></td>
                    <td width="90%"><?php
                    for ($i=0,$n=sizeof($customers_status);$i<$n; $i++) {

                      $group_query=xtc_db_query("SELECT count(*) as count
                                           FROM ".TABLE_NEWSLETTER_RECIPIENTS."
                                          WHERE mail_status='1'
                                            AND customers_status='".$customers_status[$i]['id']."'");
                      $group_data=xtc_db_fetch_array($group_query);

                      $group_query=xtc_db_query("SELECT count(*) as count
                                           FROM ".TABLE_CUSTOMERS."
                                          WHERE customers_status='".$customers_status[$i]['id']."'");
                      $group_data_all=xtc_db_fetch_array($group_query);

                      $bc_array = explode(',', $newsletters_data['bc']);
                      echo xtc_draw_checkbox_field('status['.$i.']','yes', in_array($customers_status[$i]['id'], $bc_array)).' '.$customers_status[$i]['text'].'  <i>(<b>'.$group_data['count'].'</b>'.TEXT_USERS.$group_data_all['count'].TEXT_CUSTOMERS.'<br />';
                    }

                    echo xtc_draw_checkbox_field('status_all', 'yes',in_array('all', $bc_array)).' <b>'.TEXT_NEWSLETTER_ONLY.'</b>';
                    ?></td>
                  </tr>
                  <tr>
                    <td width="10%"><?php echo TEXT_CC; ?></td>
                    <td width="90%"><?php echo xtc_draw_input_field('cc',$newsletters_data['cc'],'size=100'); ?></td>
                  </tr>
                  <tr>
                    <td width="10%" valign="top"><?php echo TEXT_BODY; ?></td>
                    <td width="90%"><?php echo xtc_draw_textarea_field('newsletter_body', 'soft', '150', '45', stripslashes($newsletters_data['body'])); ?></td>
                  </tr>
                </table>
                  <a class="button" onclick="this.blur();" href="<?php echo xtc_href_link(FILENAME_MODULE_NEWSLETTER); ?>"><?php echo BUTTON_BACK; ?></a>
                  <right><?php echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_SAVE . '"/>'; ?></right>
                </form>
                <?php
                // end switch
                break;
              }
              ?>
            </td>
          </tr>
        </table>
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