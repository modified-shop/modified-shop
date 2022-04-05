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
    case 'update':
      $sql_data_array = array();
      foreach ($_POST['config'] as $key => $value) {
        $sql_data_array[] = array(
          'config_key' => $key,
          'config_value' => $value,
        );
      }
      $paypal->save_config($sql_data_array);
      xtc_redirect(xtc_href_link(basename($PHP_SELF)));
      break;
      
    case 'status_install':
      $paypal->status_install();
      xtc_redirect(xtc_href_link(basename($PHP_SELF)));
      break;

    case 'callback':    
      $sql_data_array = array(
        array(
          'config_key' => 'PAYPAL_MERCHANT_ID_'.strtoupper($_GET['mode']),
          'config_value' => $_GET['merchantIdInPayPal']
        ),
        array(
          'config_key' => 'PAYPAL_MERCHANT_EMAIL_'.strtoupper($_GET['mode']),
          'config_value' => $_GET['merchantId']
        ),
      );
      $paypal->save_config($sql_data_array);
      xtc_redirect(xtc_href_link(basename($PHP_SELF)));
      break;
  }
}

$sellerstatus = array();
$mode_array = array('live', 'sandbox');
foreach ($mode_array as $mode) {
  $partner = $paypal->getSellerStatus($mode);

  $status_acdc = $status_pui = 'red';
  if (is_object($partner)) {
    foreach ($partner->getProducts() as $product) {
      if (is_object($product) 
          && $product->getName() == 'PPCP_CUSTOM'
          && $product->getVettingStatus() == 'SUBSCRIBED'
          )
      {
        $p_capabilities = $product->getCapabilities();
        if (is_array($p_capabilities)) {
          if (in_array('CUSTOM_CARD_PROCESSING', $p_capabilities) || in_array('PAY_UPON_INVOICE', $p_capabilities)) {
            $capabilities = $partner->getCapabilities();
          
            if (is_array($capabilities)) {
              foreach ($capabilities as $capability) {
                if ($capability->getName() == 'CUSTOM_CARD_PROCESSING'
                    && $capability->getStatus() == 'ACTIVE'
                    )
                {
                  $status_acdc = 'green';
                  if (isset($capability->limits) && is_array($capability->limits) && count($capability->limits) > 0) $status_acdc = 'yellow';
                }
                if ($capability->getName() == 'PAY_UPON_INVOICE'
                    && $capability->getStatus() == 'ACTIVE'
                    )
                {
                  $status_pui = 'green';
                }
              }
            }
          }
        }
      }
    }
  }
  
  $sellerstatus[$mode] = array(
    'status_acdc' => $status_acdc,
    'status_pui' => $status_pui,
  );
}

$orders_statuses = array(array('id' => '-1', 'text' => TEXT_PAYPAL_NO_STATUS_CHANGE));
$orders_status_query = xtc_db_query("SELECT orders_status_id,
                                            orders_status_name
                                       FROM ".TABLE_ORDERS_STATUS."
                                      WHERE language_id = '".$_SESSION['languages_id']."'
                                   ORDER BY sort_order");
while ($orders_status = xtc_db_fetch_array($orders_status_query)) {
  $orders_statuses[] = array ('id' => $orders_status['orders_status_id'], 'text' => $orders_status['orders_status_name']);
}

$status_array = array(
  array('id' => 1, 'text' => YES),
  array('id' => 0, 'text' => NO),
); 

$mode_array = array(
  array('id' => 'live', 'text' => 'Live'),
  array('id' => 'sandbox', 'text' => 'Sandbox'),
); 

$transaction_array = array(
  array('id' => 'sale', 'text' => 'Sale'),
  array('id' => 'authorize', 'text' => 'Authorize'),
); 

$color_array = array(
  array('id' => 'white', 'text' => 'White'),
  array('id' => 'blue', 'text' => 'Blue'),
  array('id' => 'grey', 'text' => 'Grey'),
  array('id' => 'black', 'text' => 'Black'),
);

$log_level_array = array(
  array('id' => 'ERROR', 'text' => 'Error'),
  array('id' => 'WARNING', 'text' => 'Warning'),
  array('id' => 'NOTICE', 'text' => 'Notice'),
  array('id' => 'INFO', 'text' => 'Info'),
  array('id' => 'DEBUG', 'text' => 'Debug'),
); 

$paypal_live = $paypal->getOnboardingLink('live');
$paypal_sandbox = $paypal->getOnboardingLink('sandbox');

require (DIR_WS_INCLUDES.'head.php');
?>
<link rel="stylesheet" type="text/css" href="../includes/external/paypal/css/stylesheet.css"> 
<script>
  function onboardedClose() {
    window.location.reload(false);
  }
  
  function onboardedCallbackLive(authCode, sharedId) {
    onboardedCallback(authCode, sharedId, 'live');
  }

  function onboardedCallbackSandbox(authCode, sharedId) {
    onboardedCallback(authCode, sharedId, 'sandbox');
  }
  
  function onboardedCallback(authCode, sharedId, mode) {
    $.post( "../ajax.php", { 'ext': 'set_paypal_data', 'speed': 1, 'authCode': authCode, 'sharedId': sharedId, 'mode': mode, 'sec': '<?php echo MODULE_PAYMENT_PAYPAL_SECRET; ?>' });
  }
</script> 
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
            <div class="pageHeading pdg2"><?php echo TEXT_PAYPAL_CONFIG_HEADING_TITLE; ?></div>
            <div class="main">v<?php echo $paypal->paypal_version; ?></div>
          </div>
          <?php
            include_once(DIR_FS_EXTERNAL.'paypal/modules/admin_menu.php');
          ?>
          <div class="clear div_box mrg5" style="margin-top:-1px;">
            <table class="clear tableConfig">
              <?php 
                echo xtc_draw_form('config', basename($PHP_SELF), xtc_get_all_get_params(array('action')).'action=update');
              ?>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_CONFIG_CLIENT_LIVE; ?></td>
                <td class="dataTableConfig col-middle"><?php echo xtc_draw_input_field('config[PAYPAL_CLIENT_ID_LIVE]', $paypal->get_config('PAYPAL_CLIENT_ID_LIVE'), 'style="width: 300px;"'); ?></td>
                <td class="dataTableConfig col-right" rowspan="2"><?php echo (($paypal_live != '') ? '<a target="_blank" data-paypal-popup-close="onboardedClose" data-paypal-onboard-complete="onboardedCallbackLive" data-paypal-button="PPLtBlue" href="' . $paypal_live . '">' . TEXT_PAYPAL_APPINATOR_LIVE . '</a><br><br>' : '') . TEXT_PAYPAL_CONFIG_CLIENT_LIVE_INFO; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_CONFIG_SECRET_LIVE; ?></td>
                <td class="dataTableConfig col-middle"><?php echo xtc_draw_input_field('config[PAYPAL_SECRET_LIVE]', $paypal->get_config('PAYPAL_SECRET_LIVE'), 'style="width: 300px;"'); ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_CONFIG_MERCHANT_ID_LIVE; ?></td>
                <td class="dataTableConfig col-middle"><?php echo xtc_draw_input_field('config[PAYPAL_MERCHANT_ID_LIVE]', $paypal->get_config('PAYPAL_MERCHANT_ID_LIVE'), 'style="width: 300px;"'); ?></td>
                <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_CONFIG_MERCHANT_ID_INFO; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_CONFIG_STATUS_ACDC_LIVE; ?></td>
                <td class="dataTableConfig col-middle"><?php echo xtc_image(DIR_WS_IMAGES . 'icon_status_'.$sellerstatus['live']['status_acdc'].'.gif', constant('IMAGE_ICON_STATUS_'.strtoupper($sellerstatus['live']['status_acdc'])), 12, 12); ?></td>
                <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_CONFIG_STATUS_ACDC_INFO; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_CONFIG_STATUS_PUI_LIVE; ?></td>
                <td class="dataTableConfig col-middle"><?php echo xtc_image(DIR_WS_IMAGES . 'icon_status_'.$sellerstatus['live']['status_pui'].'.gif', constant('IMAGE_ICON_STATUS_'.strtoupper($sellerstatus['live']['status_pui'])), 12, 12); ?></td>
                <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_CONFIG_STATUS_PUI_INFO; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_CONFIG_CLIENT_SANDBOX; ?></td>
                <td class="dataTableConfig col-middle"><?php echo xtc_draw_input_field('config[PAYPAL_CLIENT_ID_SANDBOX]', $paypal->get_config('PAYPAL_CLIENT_ID_SANDBOX'), 'style="width: 300px;"'); ?></td>
                <td class="dataTableConfig col-right" rowspan="2"><?php echo (($paypal_sandbox != '') ? '<a target="_blank" data-paypal-popup-close="onboardedClose" data-paypal-onboard-complete="onboardedCallbackSandbox" data-paypal-button="PPLtBlue" href="' . $paypal_sandbox . '">' . TEXT_PAYPAL_APPINATOR_SANDBOX . '</a><br><br>' : '') . TEXT_PAYPAL_CONFIG_CLIENT_SANDBOX_INFO; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_CONFIG_SECRET_SANDBOX; ?></td>
                <td class="dataTableConfig col-middle"><?php echo xtc_draw_input_field('config[PAYPAL_SECRET_SANDBOX]', $paypal->get_config('PAYPAL_SECRET_SANDBOX'), 'style="width: 300px;"'); ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_CONFIG_MERCHANT_ID_SANDBOX; ?></td>
                <td class="dataTableConfig col-middle"><?php echo xtc_draw_input_field('config[PAYPAL_MERCHANT_ID_SANDBOX]', $paypal->get_config('PAYPAL_MERCHANT_ID_SANDBOX'), 'style="width: 300px;"'); ?></td>
                <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_CONFIG_MERCHANT_ID_INFO; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_CONFIG_STATUS_ACDC; ?></td>
                <td class="dataTableConfig col-middle"><?php echo xtc_image(DIR_WS_IMAGES . 'icon_status_'.$sellerstatus['sandbox']['status_acdc'].'.gif', constant('IMAGE_ICON_STATUS_'.strtoupper($sellerstatus['sandbox']['status_acdc'])), 12, 12); ?></td>
                <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_CONFIG_STATUS_ACDC_INFO; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_CONFIG_STATUS_PUI; ?></td>
                <td class="dataTableConfig col-middle"><?php echo xtc_image(DIR_WS_IMAGES . 'icon_status_'.$sellerstatus['sandbox']['status_pui'].'.gif', constant('IMAGE_ICON_STATUS_'.strtoupper($sellerstatus['sandbox']['status_pui'])), 12, 12); ?></td>
                <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_CONFIG_STATUS_PUI_INFO; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_CONFIG_MODE; ?></td>
                <td class="dataTableConfig col-middle"><?php echo xtc_draw_pull_down_menu('config[PAYPAL_MODE]', $mode_array, $paypal->get_config('PAYPAL_MODE')); ?></td>
                <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_CONFIG_MODE_INFO; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_CONFIG_INVOICE_PREFIX; ?></td>
                <td class="dataTableConfig col-middle"><?php echo xtc_draw_input_field('config[PAYPAL_CONFIG_INVOICE_PREFIX]', $paypal->get_config('PAYPAL_CONFIG_INVOICE_PREFIX'), 'style="width: 300px;"'); ?></td>
                <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_CONFIG_INVOICE_PREFIX_INFO; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_CONFIG_TRANSACTION; ?></td>
                <td class="dataTableConfig col-middle"><?php echo xtc_draw_pull_down_menu('config[PAYPAL_TRANSACTION_TYPE]', $transaction_array, $paypal->get_config('PAYPAL_TRANSACTION_TYPE')); ?></td>
                <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_CONFIG_TRANSACTION_INFO; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_CONFIG_CAPTURE; ?></td>
                <td class="dataTableConfig col-middle"><?php echo draw_on_off_selection('config[PAYPAL_CAPTURE_MANUELL]', $status_array, (($paypal->get_config('PAYPAL_CAPTURE_MANUELL') == 1) ? true : false)); ?></td>
                <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_CONFIG_CAPTURE_INFO; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_CONFIG_CART; ?></td>
                <td class="dataTableConfig col-middle"><?php echo draw_on_off_selection('config[PAYPAL_ADD_CART_DETAILS]', $status_array, (($paypal->get_config('PAYPAL_ADD_CART_DETAILS') == 1) ? true : false)); ?></td>
                <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_CONFIG_CART_INFO; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_INSTALLMENT_BANNER_DISPLAY; ?></td>
                <td class="dataTableConfig col-middle"><?php echo draw_on_off_selection('config[PAYPAL_INSTALLMENT_BANNER_DISPLAY]', $status_array, $paypal->get_config('PAYPAL_INSTALLMENT_BANNER_DISPLAY')); ?></td>
                <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_INSTALLMENT_BANNER_DISPLAY_INFO; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_INSTALLMENT_BANNER_COLOR; ?></td>
                <td class="dataTableConfig col-middle"><?php echo xtc_draw_pull_down_menu('config[PAYPAL_INSTALLMENT_BANNER_COLOR]', $color_array, $paypal->get_config('PAYPAL_INSTALLMENT_BANNER_COLOR')); ?></td>
                <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_INSTALLMENT_BANNER_COLOR_INFO; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_CONFIG_STATE_SUCCESS; ?></td>
                <td class="dataTableConfig col-middle"><?php echo xtc_draw_pull_down_menu('config[PAYPAL_ORDER_STATUS_SUCCESS_ID]', $orders_statuses, $paypal->get_config('PAYPAL_ORDER_STATUS_SUCCESS_ID')); ?></td>
                <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_CONFIG_STATE_SUCCESS_INFO; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_CONFIG_STATE_REJECTED; ?></td>
                <td class="dataTableConfig col-middle"><?php echo xtc_draw_pull_down_menu('config[PAYPAL_ORDER_STATUS_REJECTED_ID]', $orders_statuses, $paypal->get_config('PAYPAL_ORDER_STATUS_REJECTED_ID')); ?></td>
                <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_CONFIG_STATE_REJECTED_INFO; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_CONFIG_STATE_PENDING; ?></td>
                <td class="dataTableConfig col-middle"><?php echo xtc_draw_pull_down_menu('config[PAYPAL_ORDER_STATUS_PENDING_ID]', $orders_statuses, $paypal->get_config('PAYPAL_ORDER_STATUS_PENDING_ID')); ?></td>
                <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_CONFIG_STATE_PENDING_INFO; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_CONFIG_STATE_TEMP; ?></td>
                <td class="dataTableConfig col-middle"><?php echo xtc_draw_pull_down_menu('config[PAYPAL_ORDER_STATUS_TMP_ID]', $orders_statuses, $paypal->get_config('PAYPAL_ORDER_STATUS_TMP_ID')); ?></td>
                <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_CONFIG_STATE_TEMP_INFO; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_CONFIG_STATE_CAPTURED; ?></td>
                <td class="dataTableConfig col-middle"><?php echo xtc_draw_pull_down_menu('config[PAYPAL_ORDER_STATUS_CAPTURED_ID]', $orders_statuses, $paypal->get_config('PAYPAL_ORDER_STATUS_CAPTURED_ID')); ?></td>
                <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_CONFIG_STATE_CAPTURED_INFO; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_CONFIG_STATE_REFUNDED; ?></td>
                <td class="dataTableConfig col-middle"><?php echo xtc_draw_pull_down_menu('config[PAYPAL_ORDER_STATUS_REFUNDED_ID]', $orders_statuses, $paypal->get_config('PAYPAL_ORDER_STATUS_REFUNDED_ID')); ?></td>
                <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_CONFIG_STATE_REFUNDED_INFO; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_CONFIG_LOG; ?></td>
                <td class="dataTableConfig col-middle"><?php echo draw_on_off_selection('config[PAYPAL_LOG_ENALBLED]', $status_array, (($paypal->get_config('PAYPAL_LOG_ENALBLED') == 1) ? true : false)); ?></td>
                <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_CONFIG_LOG_INFO; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_CONFIG_LOG_LEVEL; ?></td>
                <td class="dataTableConfig col-middle"><?php echo xtc_draw_pull_down_menu('config[PAYPAL_LOG_LEVEL]', $log_level_array, $paypal->get_config('PAYPAL_LOG_LEVEL')); ?></td>
                <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_CONFIG_LOG_LEVEL_INFO; ?></td>
              </tr>
              <tr>
                <td class="txta-l" colspan="1" style="border:none;">
                  <a class="button" href="<?php echo xtc_href_link(basename($PHP_SELF), 'action=status_install'); ?>""><?php echo BUTTON_PAYPAL_STATUS_INSTALL; ?></a>
                </td>
                <td class="txta-r" colspan="2" style="border:none;">
                  <input type="submit" class="button" name="submit" value="<?php echo BUTTON_UPDATE; ?>">
                </td>
              </tr>
            </table>
          </div>
        </td>
        <!-- body_text_eof //-->
      </tr>
    </table>
    <script id="paypal-js" src="//www.sandbox.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js"></script>
    <!-- body_eof //-->
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>