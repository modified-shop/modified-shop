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
require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalInfo.php');
$paypal = new PayPalInfo('paypal');

//display per page
$cfg_max_display_results_key = 'MAX_DISPLAY_PAYPAL_PAYMENTS_RESULTS';
$page_max_display_results = xtc_cfg_save_max_display_results($cfg_max_display_results_key);
$page_max_display_results = (($page_max_display_results > 10) ? '10' : $page_max_display_results);

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
          <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_configuration.png'); ?></div>
          <div class="flt-l">
            <div class="pageHeading pdg2"><?php echo TEXT_PAYPAL_PAYMENT_HEADING_TITLE; ?></div>
          </div>
          <?php
            include_once(DIR_FS_EXTERNAL.'paypal/modules/admin_menu.php');
          ?>
          <div class="clear div_box mrg5" style="margin-top:-1px;">
          <?php
            $list = $paypal->get_payments($page_max_display_results, ((isset($_GET['page']) && $_GET['page'] > 0) ? ($_GET['page'] * $page_max_display_results) : 0));
            
            if (count($list) > 0) {
            ?>              
            <table class="tableBoxCenter collapse">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ORDER; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_NAME; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_EMAIL; ?></td>
                <td class="dataTableHeadingContent txta-r" rowspan="2"><?php echo TABLE_HEADING_INTENT; ?></td>
                <td class="dataTableHeadingContent txta-r" rowspan="2"><?php echo TABLE_HEADING_STATUS; ?></td>
              </tr>
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_DATE; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ID; ?></td>
                <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_TOTAL; ?></td>
              </tr>
              <?php
              for ($i=0, $n=count($list); $i<$n; $i++) {
                ?>
                  <tr class="dataTableRow">
                    <td class="dataTableContent"><?php echo (($list[$i]['orders_id'] != '') ? '<a href="'.xtc_href_link(FILENAME_ORDERS, 'action=edit&oID='.$list[$i]['orders_id']).'"><b>'.$list[$i]['orders_id'].'</b></a>' : 'n/a'); ?></td>
                    <td class="dataTableContent"><b><?php echo $list[$i]['address']['name']; ?></b></td>
                    <td class="dataTableContent"><b><?php echo $list[$i]['email_address']; ?></b></td>
                    <td class="dataTableContent txta-r"><b><?php echo $list[$i]['intent']; ?></b></td>
                    <td class="dataTableContent txta-r"><b><?php echo $list[$i]['state']; ?></b></td>
                  </tr>
                <?php
                for ($t=0, $x=count($list[$i]['transactions']); $t<$x; $t++) {
                  for ($r=0, $z=count($list[$i]['transactions'][$t]['relatedResource']); $r<$z; $r++) {
                    ?>
                      <tr class="dataTableRow">
                        <td class="dataTableContent"><?php echo xtc_datetime_short($list[$i]['transactions'][$t]['relatedResource'][$r]['date']); ?></td>
                        <td class="dataTableContent"><?php echo $list[$i]['transactions'][$t]['relatedResource'][$r]['id']; ?></td>
                        <td class="dataTableContent txta-r"><?php echo format_price($list[$i]['transactions'][$t]['relatedResource'][$r]['total'], 1, $list[$i]['transactions'][$t]['relatedResource'][$r]['currency'], 0, 0); ?></td>
                        <td class="dataTableContent txta-r"><?php echo $list[$i]['transactions'][$t]['relatedResource'][$r]['type']; ?></td>
                        <td class="dataTableContent txta-r"><?php echo $list[$i]['transactions'][$t]['relatedResource'][$r]['state']; ?></td>
                      </tr>
                    <?php
                  }
                }
              }
              ?>
              <tr>
                <td colspan="5" style="border:none;">
                  <?php
                  if (isset($_GET['page']) && $_GET['page'] > 0) {
                    echo '<a class="button flt-l" href="'.xtc_href_link(basename($PHP_SELF), 'page='.($_GET['page'] - 1)).'">&laquo;</a>';
                  }
                  if (!isset($_GET['page']) || count($list) == $page_max_display_results) {
                    echo '<a class="button flt-r" href="'.xtc_href_link(basename($PHP_SELF), 'page='.((isset($_GET['page'])) ? ($_GET['page'] + 1) : 1)).'">&raquo;</a>';
                  }
                  ?>
                </td>
              </tr>
              <tr>
                <td colspan="5" style="border:none;">
                  <?php echo draw_input_per_page($PHP_SELF, $cfg_max_display_results_key, $page_max_display_results); ?>
                </td>
              </tr>
            </table>
            <?php
            } else {
              echo '<div class="info_message">'.TEXT_PAYPAL_PAYMENT_INFO.'</div>';
            }
          ?>
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