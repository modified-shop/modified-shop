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
      $paypal->delete_profile($_GET['id']);      
      xtc_redirect(xtc_href_link(basename($PHP_SELF)));
      break;

    case 'update':
      $paypal->update_profile($_POST['config']);      
      xtc_redirect(xtc_href_link(basename($PHP_SELF)));
      break;

    case 'insert':
      $paypal->create_profile($_POST['config']);      
      xtc_redirect(xtc_href_link(basename($PHP_SELF)));
      break;
  }
}

$locale_code_array = array(
  array('id' => 'DE', 'text' => 'DE'),
  array('id' => 'AU', 'text' => 'AU'),
  array('id' => 'AT', 'text' => 'AT'),
  array('id' => 'BE', 'text' => 'BE'),
  array('id' => 'BR', 'text' => 'BR'),
  array('id' => 'CA', 'text' => 'CA'),
  array('id' => 'CH', 'text' => 'CH'),
  array('id' => 'CN', 'text' => 'CN'),
  array('id' => 'ES', 'text' => 'ES'),
  array('id' => 'FR', 'text' => 'FR'),
  array('id' => 'GB', 'text' => 'GB'),
  array('id' => 'IT', 'text' => 'IT'),
  array('id' => 'NL', 'text' => 'NL'),
  array('id' => 'PL', 'text' => 'PL'),
  array('id' => 'PT', 'text' => 'PT'),
  array('id' => 'RU', 'text' => 'RU'),
  array('id' => 'US', 'text' => 'US'),
);

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
          <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading_configuration.gif'); ?></div>
          <div class="flt-l">
            <div class="pageHeading pdg2"><?php echo TEXT_PAYPAL_PROFILE_HEADING_TITLE; ?></div>
          </div>
          <?php
            if (!isset($_GET['action'])) {
              echo '<div class="pageHeading flt-l" style="margin: 3px 40px;"><a class="button" href="'.xtc_href_link(basename($PHP_SELF), 'action=new').'">'.BUTTON_INSERT.'</a></div>';
            }
            include_once(DIR_FS_EXTERNAL.'paypal/modules/admin_menu.php');
          ?>
          <div class="clear div_box mrg5" style="margin-top:-1px;">
            <table class="clear tableConfig">
            <?php
              if (isset($_GET['action']) && $_GET['action'] == 'edit') {
                $list = $paypal->get_profile($_GET['id']);
              
                echo xtc_draw_form('config', basename($PHP_SELF), xtc_get_all_get_params(array('action')).'action=update');

                for ($i=0, $n=count($list); $i<$n; $i++) {
                  echo xtc_draw_hidden_field('config[id]', $list[$i]['id']);
                  ?>
                    <tr>
                      <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_PROFILE_STATUS; ?></td>
                      <td class="dataTableConfig col-middle"><?php echo draw_on_off_selection('config[status]', $status_array, $list[$i]['status']); ?></td>
                      <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_PROFILE_STATUS_INFO; ?></td>
                    </tr>
                    <tr>
                      <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_PROFILE_NAME; ?></td>
                      <td class="dataTableConfig col-middle"><?php echo xtc_draw_input_field('config[name]', $list[$i]['name'], 'style="width: 300px;"'); ?></td>
                      <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_PROFILE_NAME_INFO; ?></td>
                    </tr>
                    <tr>
                      <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_PROFILE_BRAND; ?></td>
                      <td class="dataTableConfig col-middle"><?php echo xtc_draw_input_field('config[presentation][brand_name]', $list[$i]['presentation']['brand_name'], 'style="width: 300px;"'); ?></td>
                      <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_PROFILE_BRAND_INFO; ?></td>
                    </tr>
                    <tr>
                      <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_PROFILE_LOGO; ?></td>
                      <td class="dataTableConfig col-middle"><?php echo xtc_draw_input_field('config[presentation][logo_image]', $list[$i]['presentation']['logo_image'], 'style="width: 300px;"'); ?></td>
                      <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_PROFILE_LOGO_INFO; ?></td>
                    </tr>
                    <tr>
                      <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_PROFILE_LOCALE; ?></td>
                      <td class="dataTableConfig col-middle"><?php echo xtc_draw_pull_down_menu('config[presentation][locale_code]', $locale_code_array, $list[$i]['presentation']['locale_code']); ?></td>
                      <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_PROFILE_LOCALE_INFO; ?></td>
                    </tr>
                    <tr>
                      <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_PROFILE_PAGE; ?></td>
                      <td class="dataTableConfig col-middle"><?php echo xtc_draw_pull_down_menu('config[flow_config][landing_page_type]', $landingpage_array, $list[$i]['flow_config']['landing_page_type']); ?></td>
                      <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_PROFILE_PAGE_INFO; ?></td>
                    </tr>
                    <tr>
                      <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_PROFILE_ADDRESS; ?></td>
                      <td class="dataTableConfig col-middle"><?php echo draw_on_off_selection('config[input_fields][address_override]', $status_array, ($list[$i]['input_fields']['address_override'] == '1' ? false : true)); ?></td>
                      <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_PROFILE_ADDRESS_INFO; ?></td>
                    </tr>
                    <tr>
                      <td class="txta-r" colspan="3" style="border:none;">
                        <a class="button" href="<?php echo xtc_href_link(basename($PHP_SELF)); ?>"><?php echo BUTTON_CANCEL; ?></a>
                        <input type="submit" class="button" name="submit" value="<?php echo BUTTON_UPDATE; ?>">
                      </td>
                    </tr>
                 <?php
                }
              
              } elseif (isset($_GET['action']) && $_GET['action'] == 'new') {

                echo xtc_draw_form('config', basename($PHP_SELF), xtc_get_all_get_params(array('action')).'action=insert');
                ?>
                  <tr>
                    <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_PROFILE_NAME; ?></td>
                    <td class="dataTableConfig col-middle"><?php echo xtc_draw_input_field('config[name]', '', 'style="width: 300px;"'); ?></td>
                    <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_PROFILE_NAME_INFO; ?></td>
                  </tr>
                  <tr>
                    <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_PROFILE_BRAND; ?></td>
                    <td class="dataTableConfig col-middle"><?php echo xtc_draw_input_field('config[presentation][brand_name]', '', 'style="width: 300px;"'); ?></td>
                    <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_PROFILE_BRAND_INFO; ?></td>
                  </tr>
                  <tr>
                    <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_PROFILE_LOGO; ?></td>
                    <td class="dataTableConfig col-middle"><?php echo xtc_draw_input_field('config[presentation][logo_image]', '', 'style="width: 300px;"'); ?></td>
                    <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_PROFILE_LOGO_INFO; ?></td>
                  </tr>
                  <tr>
                    <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_PROFILE_LOCALE; ?></td>
                    <td class="dataTableConfig col-middle"><?php echo xtc_draw_pull_down_menu('config[presentation][locale_code]', $locale_code_array, strtoupper(DEFAULT_LANGUAGE)); ?></td>
                    <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_PROFILE_LOCALE_INFO; ?></td>
                  </tr>
                  <tr>
                    <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_PROFILE_PAGE; ?></td>
                    <td class="dataTableConfig col-middle"><?php echo xtc_draw_pull_down_menu('config[flow_config][landing_page_type]', $landingpage_array, ''); ?></td>
                    <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_PROFILE_PAGE_INFO; ?></td>
                  </tr>
                  <tr>
                    <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_PROFILE_ADDRESS; ?></td>
                    <td class="dataTableConfig col-middle"><?php echo draw_on_off_selection('config[input_fields][address_override]', $status_array, false); ?></td>
                    <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_PROFILE_ADDRESS_INFO; ?></td>
                  </tr>
                  <tr>
                    <td class="txta-r" colspan="3" style="border:none;">
                      <a class="button" href="<?php echo xtc_href_link(basename($PHP_SELF)); ?>"><?php echo BUTTON_CANCEL; ?></a>
                      <input type="submit" class="button" name="submit" value="<?php echo BUTTON_SAVE; ?>">
                    </td>
                  </tr>
                <?php
            
              } else {
                $list = $paypal->list_profile();
              
                for ($i=0, $n=count($list); $i<$n; $i++) {
                  ?>
                    <tr>
                      <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_PROFILE_STATUS; ?></td>
                      <td class="dataTableConfig col-middle"><?php echo (($list[$i]['status'] == '1') ? YES : NO); ?></td>
                      <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_PROFILE_STATUS_INFO; ?></td>
                    </tr>
                    <tr>
                      <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_PROFILE_NAME; ?></td>
                      <td class="dataTableConfig col-middle"><?php echo $list[$i]['name']; ?></td>
                      <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_PROFILE_NAME_INFO; ?></td>
                    </tr>
                    <tr>
                      <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_PROFILE_BRAND; ?></td>
                      <td class="dataTableConfig col-middle"><?php echo $list[$i]['presentation']['brand_name']; ?></td>
                      <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_PROFILE_BRAND_INFO; ?></td>
                    </tr>
                    <tr>
                      <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_PROFILE_LOGO; ?></td>
                      <td class="dataTableConfig col-middle"><?php echo (($list[$i]['presentation']['logo_image'] != '') ? '<img src="'.$list[$i]['presentation']['logo_image'].'" style="max-width: 280px;" />' : ''); ?></td>
                      <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_PROFILE_LOGO_INFO; ?></td>
                    </tr>
                    <tr>
                      <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_PROFILE_LOCALE; ?></td>
                      <td class="dataTableConfig col-middle"><?php echo $list[$i]['presentation']['locale_code']; ?></td>
                      <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_PROFILE_LOCALE_INFO; ?></td>
                    </tr>
                    <tr>
                      <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_PROFILE_PAGE; ?></td>
                      <td class="dataTableConfig col-middle"><?php echo $list[$i]['flow_config']['landing_page_type']; ?></td>
                      <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_PROFILE_PAGE_INFO; ?></td>
                    </tr>
                    <tr>
                      <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_PROFILE_ADDRESS; ?></td>
                      <td class="dataTableConfig col-middle"><?php echo (($list[$i]['input_fields']['address_override'] == '0') ? YES : NO); ?></td>
                      <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_PROFILE_ADDRESS_INFO; ?></td>
                    </tr>
                    <tr>
                      <td class="txta-r" colspan="3" style="border:none;">
                        <a class="button" href="<?php echo xtc_href_link(basename($PHP_SELF), 'action=edit&id='.$list[$i]['id']); ?>"><?php echo BUTTON_EDIT; ?></a>
                        <a class="button" href="<?php echo xtc_href_link(basename($PHP_SELF), 'action=delete&id='.$list[$i]['id']); ?>"><?php echo BUTTON_DELETE; ?></a>
                      </td>
                    </tr>
                  <?php
                }
                if (count($list) < 1) {
                  echo '<div class="info_message">'.TEXT_PAYPAL_PROFILE_INFO.'</div>';
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