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

// include needed classes
require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalAdmin.php');
$paypal = new PayPalAdmin();

if (isset($_GET['action'])) {
  switch ($_GET['action']) {
    case 'delete':
      $paypal->delete_webhook($_GET['id']);      
      xtc_redirect(xtc_href_link(basename($PHP_SELF)));
      break;

    case 'update':
      $paypal->update_webhook($_POST['config']);      
      xtc_redirect(xtc_href_link(basename($PHP_SELF)));
      break;

    case 'insert':
      $paypal->create_webhook($_POST['config']);      
      xtc_redirect(xtc_href_link(basename($PHP_SELF)));
      break;
  }
}

$orders_statuses = array(array('id' => '-1', 'text' => TEXT_PAYPAL_NO_STATUS_CHANGE));
$orders_status_array = array('-1' => TEXT_PAYPAL_NO_STATUS_CHANGE);
$orders_status_query = xtc_db_query("SELECT orders_status_id,
                                            orders_status_name
                                       FROM ".TABLE_ORDERS_STATUS."
                                      WHERE language_id = '".$_SESSION['languages_id']."'
                                   ORDER BY sort_order");
while ($orders_status = xtc_db_fetch_array($orders_status_query)) {
  $orders_statuses[] = array ('id' => $orders_status['orders_status_id'], 'text' => $orders_status['orders_status_name']);
  $orders_status_array[$orders_status['orders_status_id']] = $orders_status['orders_status_name'];
}

$status_array = array(
  array('id' => '1', 'text' => YES),
  array('id' => '0', 'text' => NO),
); 

$landingpage_array = array(
  array('id' => 'Login', 'text' => 'Login'),
  array('id' => 'Payment', 'text' => 'Payment'),
); 

//$locale_code = array(
require (DIR_WS_INCLUDES.'head.php');
?>
<link rel="stylesheet" type="text/css" href="../includes/external/paypal/css/stylesheet.css">  
<style type="text/css">
  .check { width: 40px; float: left; padding-top: 3px; }
  .drop { width: 230px; float: left; }
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
          <div class="flt-l">
            <div class="pageHeading pdg2"><?php echo TEXT_PAYPAL_WEBHOOK_HEADING_TITLE; ?></div>
            <div class="main">v<?php echo $paypal->paypal_version; ?></div>
          </div>
          <?php
            $list = $paypal->list_webhooks();
            $new = true;
            if (count($list) > 0) {
              foreach ($list as $data) {
                if ($data['url'] == xtc_catalog_href_link('callback/paypal/webhook.php', '', 'SSL', false)) {
                  $new = false;
                  break;
                }
              }
            }
            if (!isset($_GET['action']) && $new != false) {
              echo '<div class="pageHeading flt-l" style="margin: 3px 40px;"><a class="button" href="'.xtc_href_link(basename($PHP_SELF), 'action=new').'">'.BUTTON_INSERT.'</a></div>';
            }
            include_once(DIR_FS_EXTERNAL.'paypal/modules/admin_menu.php');
          ?>
          <div class="clear div_box mrg5" style="margin-top:-1px;">
            <table class="clear tableConfig">
            <?php
              if (isset($_GET['action']) && $_GET['action'] == 'edit') {
                $list = $paypal->edit_webhook($_GET['id']);
              
                echo xtc_draw_form('config', basename($PHP_SELF), xtc_get_all_get_params(array('action')).'action=update');
                echo xtc_draw_hidden_field('config[id]', $_GET['id']);

                for ($i=0, $t=count($list); $i<$t; $i++) {
                  ?>
                  <tr>
                    <td class="dataTableConfig col-left"><?php echo $list[$i]['name']; ?></td>
                    <td class="dataTableConfig col-middle">
                      <?php 
                        echo '<div class="check">'.xtc_draw_checkbox_field('config[data]['.$i.'][name]', $list[$i]['name'], (($list[$i]['status'] === true) ? 'checked="checked"' : '')).'</div>'; 
                        echo '<div class="drop">'.xtc_draw_pull_down_menu('config[data]['.$i.'][orders_status]', $orders_statuses, $list[$i]['orders_status'], 'style="width: 300px;"').'</div>';
                      ?>
                    </td>
                    <td class="dataTableConfig col-right"><?php echo $list[$i]['description']; ?></td>
                  </tr>
                  <?php
                }
                ?>
                <tr>
                  <td class="txta-r" colspan="3" style="border:none;">
                    <a class="button" href="<?php echo xtc_href_link(basename($PHP_SELF)); ?>"><?php echo BUTTON_CANCEL; ?></a>
                    <input type="submit" class="button" name="submit" value="<?php echo BUTTON_UPDATE; ?>">
                  </td>
                </tr>
                <?php              
              } elseif (isset($_GET['action']) && $_GET['action'] == 'new') {
              
                $list = $paypal->available_webhooks();
                
                if (is_array($list) && count($list) > 0) {
                  echo xtc_draw_form('config', basename($PHP_SELF), xtc_get_all_get_params(array('action')).'action=insert');
                  for ($i=0, $t=count($list); $i<$t; $i++) {
                    ?>
                    <tr>
                      <td class="dataTableConfig col-left"><?php echo $list[$i]['name']; ?></td>
                      <td class="dataTableConfig col-middle">
                        <?php 
                          echo xtc_draw_checkbox_field('config[data]['.$i.'][name]', $list[$i]['name'], 'checked="checked"'); 
                          echo xtc_draw_pull_down_menu('config[data]['.$i.'][orders_status]', $orders_statuses, '-1', 'style="width: 300px;"');
                        ?>
                      </td>
                      <td class="dataTableConfig col-right"><?php echo $list[$i]['description']; ?></td>
                    </tr>
                    <?php
                  }
                  ?>
                  <tr>
                    <td class="txta-r" colspan="3" style="border:none;">
                      <a class="button" href="<?php echo xtc_href_link(basename($PHP_SELF)); ?>"><?php echo BUTTON_CANCEL; ?></a>
                      <input type="submit" class="button" name="submit" value="<?php echo BUTTON_SAVE; ?>">
                    </td>
                  </tr>
                  <?php
                } else {
                  echo '<div class="info_message">'.TEXT_PAYPAL_WEBHOOK_CREDENTIAL_INFO.'</div>';
                }
              } else {
                if (count($list) > 0) {
                  for ($i=0, $n=count($list); $i<$n; $i++) {
                    ?>
                      <tr class="dataTableHeadingRow">
                        <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_URL; ?></td>
                        <td class="dataTableHeadingContent" colspan="2"><?php echo $list[$i]['url']; ?></td>
                      </tr> 
                      <tr class="dataTableHeadingRow">
                        <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_WEBHOOK; ?></td>
                        <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_STATUS; ?></td>
                        <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_DESCRIPTION; ?></td>
                      </tr> 
                    <?php
                    for ($z=0, $t=count($list[$i]['data']); $z<$t; $z++) {
                      ?>
                      <tr>
                        <td class="dataTableConfig col-left"><?php echo $list[$i]['data'][$z]['name']; ?></td>
                        <td class="dataTableConfig col-middle"><?php echo ((isset($orders_status_array[$list[$i]['data'][$z]['orders_status']])) ? $orders_status_array[$list[$i]['data'][$z]['orders_status']] : TEXT_PAYPAL_WEBHOOK_STATUS_NOT_DEFINED); ?></td>
                        <td class="dataTableConfig col-right"><?php echo $list[$i]['data'][$z]['description']; ?></td>
                      </tr>
                      <?php
                    }
                    ?>
                    <tr>
                      <td class="txta-r" colspan="3" style="border:none;">
                        <a class="button" href="<?php echo xtc_href_link(basename($PHP_SELF), 'action=edit&id='.$list[$i]['id']); ?>"><?php echo BUTTON_EDIT; ?></a>
                        <a class="button" href="<?php echo xtc_href_link(basename($PHP_SELF), 'action=delete&id='.$list[$i]['id']); ?>"><?php echo BUTTON_DELETE; ?></a>
                      </td>
                    </tr>
                    <?php
                  }
                } else {
                  echo '<div class="info_message">'.TEXT_PAYPAL_WEBHOOK_INFO.'</div>';
                }
              }
            ?>
            </table>
          </div>
        </td>
        <!-- body_text_eof //-->
      </tr>
    </table>
    <!-- body_eof //-->
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>