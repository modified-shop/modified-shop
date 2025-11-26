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

$status_array = array(
  array('id' => 1, 'text' => YES),
  array('id' => 0, 'text' => NO),
); 

$logotype_array = array(
  array('id' => 'primary', 'text' => 'Full Logo'),
  array('id' => 'alternative', 'text' => 'Monogram'),
  array('id' => 'inline', 'text' => 'Inline'),
  array('id' => 'none', 'text' => 'Message only'),
);

$logoposition_array = array(
  array('id' => 'left', 'text' => 'Left'),
  array('id' => 'right', 'text' => 'Right'),
  array('id' => 'top', 'text' => 'Top'),
);

$size_array = array(
  array('id' => '1x1', 'text' => '1 x 1'),
  array('id' => '1x4', 'text' => '1 x 4'),
  array('id' => '8x1', 'text' => '8 x 1'),
  array('id' => '20x1', 'text' => '20 x 1'),
); 

$textsize_array = array(
  array('id' => '10', 'text' => 'Small'),
  array('id' => '12', 'text' => 'Medium'),
  array('id' => '16', 'text' => 'Large'),
); 

$color_array = array(
  array('id' => 'blue', 'text' => 'Blue'),
  array('id' => 'grey', 'text' => 'Grey'),
  array('id' => 'black', 'text' => 'Black'),
  array('id' => 'white', 'text' => 'White'),
  array('id' => 'gray', 'text' => 'Gray'),
  array('id' => 'grayscale', 'text' => 'Grayscale'),
  array('id' => 'monochrome', 'text' => 'Monochrome'),
);

$textcolor_array = array(
  array('id' => 'black', 'text' => 'Black / Blue Logo'),
  array('id' => 'white', 'text' => 'White / White Logo'),
  array('id' => 'monochrome', 'text' => 'Monochrome'),
  array('id' => 'grayscale', 'text' => 'Black / Grey Logo'),
);

if ($paypal->check_webhooks() === true) {
  $messageStack->add(TEXT_PAYPAL_ERROR_WEBHOOKS);
}

require (DIR_WS_INCLUDES.'head.php');
?>
<link rel="stylesheet" type="text/css" href="../includes/external/paypal/css/stylesheet.css"> 
<style type="text/css">
  .tableConfig td a.button { font-size: 10px; }
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
            <div class="pageHeading pdg2"><?php echo TEXT_PAYPAL_BANNER_HEADING_TITLE; ?></div>
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
                <td class="dataTableConfig col-left col-middle txta-c" colspan="3"><?php echo TEXT_PAYPAL_BANNER_HEADING_PRODUCT; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_INSTALLMENT_BANNER_DISPLAY; ?></td>
                <td class="dataTableConfig col-middle"><?php echo draw_on_off_selection('config[PAYPAL_INSTALLMENT_BANNER_PRODUCT_DISPLAY]', $status_array, $paypal->get_config('PAYPAL_INSTALLMENT_BANNER_PRODUCT_DISPLAY')); ?></td>
                <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_INSTALLMENT_BANNER_DISPLAY_INFO; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_INSTALLMENT_BANNER_LOGOTYPE; ?></td>
                <td class="dataTableConfig col-middle"><?php echo xtc_draw_pull_down_menu('config[PAYPAL_INSTALLMENT_BANNER_PRODUCT_LOGOTYPE]', $logotype_array, $paypal->get_config('PAYPAL_INSTALLMENT_BANNER_PRODUCT_LOGOTYPE')); ?></td>
                <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_INSTALLMENT_BANNER_LOGOTYPE_INFO; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_INSTALLMENT_BANNER_LOGOPOSITION; ?></td>
                <td class="dataTableConfig col-middle"><?php echo xtc_draw_pull_down_menu('config[PAYPAL_INSTALLMENT_BANNER_PRODUCT_LOGOPOSITION]', $logoposition_array, $paypal->get_config('PAYPAL_INSTALLMENT_BANNER_PRODUCT_LOGOPOSITION')); ?></td>
                <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_INSTALLMENT_BANNER_LOGOPOSITION_INFO; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_INSTALLMENT_BANNER_TEXTCOLOR; ?></td>
                <td class="dataTableConfig col-middle"><?php echo xtc_draw_pull_down_menu('config[PAYPAL_INSTALLMENT_BANNER_PRODUCT_TEXTCOLOR]', $textcolor_array, $paypal->get_config('PAYPAL_INSTALLMENT_BANNER_PRODUCT_TEXTCOLOR')); ?></td>
                <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_INSTALLMENT_BANNER_TEXTCOLOR_INFO; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_INSTALLMENT_BANNER_TEXTSIZE; ?></td>
                <td class="dataTableConfig col-middle"><?php echo xtc_draw_pull_down_menu('config[PAYPAL_INSTALLMENT_BANNER_PRODUCT_TEXTSIZE]', $textsize_array, $paypal->get_config('PAYPAL_INSTALLMENT_BANNER_PRODUCT_TEXTSIZE')); ?></td>
                <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_INSTALLMENT_BANNER_TEXTSIZE_INFO; ?></td>
              </tr>

              <tr>
                <td class="dataTableConfig col-left col-middle txta-c" colspan="3"><?php echo TEXT_PAYPAL_BANNER_HEADING_CART; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_INSTALLMENT_BANNER_DISPLAY; ?></td>
                <td class="dataTableConfig col-middle"><?php echo draw_on_off_selection('config[PAYPAL_INSTALLMENT_BANNER_CART_DISPLAY]', $status_array, $paypal->get_config('PAYPAL_INSTALLMENT_BANNER_CART_DISPLAY')); ?></td>
                <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_INSTALLMENT_BANNER_DISPLAY_INFO; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_INSTALLMENT_BANNER_COLOR; ?></td>
                <td class="dataTableConfig col-middle"><?php echo xtc_draw_pull_down_menu('config[PAYPAL_INSTALLMENT_BANNER_CART_COLOR]', $color_array, $paypal->get_config('PAYPAL_INSTALLMENT_BANNER_CART_COLOR')); ?></td>
                <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_INSTALLMENT_BANNER_COLOR_INFO; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_INSTALLMENT_BANNER_SIZE; ?></td>
                <td class="dataTableConfig col-middle"><?php echo xtc_draw_pull_down_menu('config[PAYPAL_INSTALLMENT_BANNER_CART_SIZE]', $size_array, $paypal->get_config('PAYPAL_INSTALLMENT_BANNER_CART_SIZE')); ?></td>
                <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_INSTALLMENT_BANNER_SIZE_INFO; ?></td>
              </tr>

              <tr>
                <td class="dataTableConfig col-left col-middle txta-c" colspan="3"><?php echo TEXT_PAYPAL_BANNER_HEADING_CHECKOUT; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_INSTALLMENT_BANNER_DISPLAY; ?></td>
                <td class="dataTableConfig col-middle"><?php echo draw_on_off_selection('config[PAYPAL_INSTALLMENT_BANNER_CHECKOUT_DISPLAY]', $status_array, $paypal->get_config('PAYPAL_INSTALLMENT_BANNER_CHECKOUT_DISPLAY')); ?></td>
                <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_INSTALLMENT_BANNER_DISPLAY_INFO; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_INSTALLMENT_BANNER_COLOR; ?></td>
                <td class="dataTableConfig col-middle"><?php echo xtc_draw_pull_down_menu('config[PAYPAL_INSTALLMENT_BANNER_CHECKOUT_COLOR]', $color_array, $paypal->get_config('PAYPAL_INSTALLMENT_BANNER_CHECKOUT_COLOR')); ?></td>
                <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_INSTALLMENT_BANNER_COLOR_INFO; ?></td>
              </tr>
              <tr>
                <td class="dataTableConfig col-left"><?php echo TEXT_PAYPAL_INSTALLMENT_BANNER_SIZE; ?></td>
                <td class="dataTableConfig col-middle"><?php echo xtc_draw_pull_down_menu('config[PAYPAL_INSTALLMENT_BANNER_CHECKOUT_SIZE]', $size_array, $paypal->get_config('PAYPAL_INSTALLMENT_BANNER_CHECKOUT_SIZE')); ?></td>
                <td class="dataTableConfig col-right"><?php echo TEXT_PAYPAL_INSTALLMENT_BANNER_SIZE_INFO; ?></td>
              </tr>

              <tr>
                <td class="txta-r" colspan="3" style="border:none;">
                  <input type="submit" class="button" name="submit" value="<?php echo BUTTON_UPDATE; ?>">
                </td>
              </tr>
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