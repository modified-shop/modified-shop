<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  if (isset($order) && is_object($order)) {
    // include needed functions
    require_once (DIR_FS_INC.'xtc_format_price_order.inc.php');
    if (!function_exists('xtc_date_short')) {
      require_once(DIR_FS_INC.'xtc_date_short.inc.php');
    }

    // include needed classes
    require_once(DIR_FS_EXTERNAL.'Teambank/classes/TeambankPayment.php');

    $TeambankPayment = new TeambankPayment();
    $TeambankPayment->init($order->info['payment_method']);
      
    $admin_info_data = $TeambankPayment->get_order_info($order->info['order_id']);
    
    if (is_object($admin_info_data)) {
      ?>
      <table border="0" width="100%" cellspacing="0" cellpadding="2" class="dataTableRow teambank_data" style="display:none;">
        <tr>
          <td width="100%" valign="top">
          <?php
          if (is_array($admin_info_data) && count($admin_info_data) > 0) {} elseif (is_object($admin_info_data)) {
            ?>
            <div class="ec_transactions ec_box">
              <div class="ec_boxheading"><?php echo TEXT_TEAMBANK_TRANSACTION; ?></div>
              <dl class="ec_transaction">
                <dt><?php echo TEXT_TEAMBANK_TRANSACTION_STATE; ?></dt>
                <dd><?php echo $admin_info_data->getStatus(); ?></dd>
              </dl>
              <dl class="ec_transaction">
                <dt><?php echo TEXT_TEAMBANK_TRANSACTION_CUSTOMER; ?></dt>
                <dd><?php echo $admin_info_data->getCustomer()->getFirstName() . ' ' . $admin_info_data->getCustomer()->getLastName(); ?></dd>
              </dl>
              <dl class="ec_transaction">
                <dt><?php echo TEXT_TEAMBANK_TRANSACTION_TOTAL; ?></dt>
                <dd><?php echo xtc_format_price_order($admin_info_data->getOrderDetails()->getOriginalOrderValue(), 1, $order->info['currency'], 1); ?></dd>
              </dl>
              <dl class="ec_transaction">
                <dt><?php echo TEXT_TEAMBANK_TRANSACTION_REFUNDED; ?></dt>
                <dd><?php echo xtc_format_price_order($admin_info_data->getRefundsTotalValue(), 1, $order->info['currency'], 1); ?></dd>
              </dl>
              <dl class="ec_transaction">
                <dt><?php echo TEXT_TEAMBANK_TRANSACTION_BALANCE; ?></dt>
                <dd><?php echo xtc_format_price_order($admin_info_data->getOrderDetails()->getCurrentOrderValue(), 1, $order->info['currency'], 1); ?></dd>
              </dl>
              <?php if (is_object($admin_info_data->getOrderDetails()->getClearingDate())) { ?>
              <dl class="ec_transaction">
                <dt><?php echo TEXT_TEAMBANK_TRANSACTION_CLEARING; ?></dt>
                <dd><?php echo xtc_date_short($admin_info_data->getOrderDetails()->getClearingDate()->format("Y-m-d")); ?></dd>
              </dl>
              <?php } ?>
              <dl class="ec_transaction">
                <dt><?php echo TEXT_TEAMBANK_TRANSACTION_ID; ?></dt>
                <dd><?php echo $admin_info_data->getTransactionId(); ?></dd>
              </dl>
              <?php if (is_object($admin_info_data->getExpirationDateTime())) { ?>
              <dl class="ec_transaction">
                <dt><?php echo TEXT_TEAMBANK_TRANSACTION_VALID; ?></dt>
                <dd><?php echo xtc_date_short($admin_info_data->getExpirationDateTime()->format("Y-m-d")); ?></dd>
              </dl>
              <?php } ?>
            </div>
            <?php 
          }
          
          if (is_array($admin_info_data->getBookings())
              && count($admin_info_data->getBookings()) > 0
              )
          {
            ?>
            <div class="ec_txstatus ec_box">
            <div class="ec_boxheading"><?php echo TEXT_TEAMBANK_TRANSACTIONS_STATUS; ?></div>
            <?php
              foreach ($admin_info_data->getBookings() as $booking) {
                ?>
                <div class="ec_txstatus">
                  <div class="ec_txstatus_received ec_received_icon">
                    <?php echo xtc_datetime_short($booking->getCreated()->format('Y-m-d H:i:s')) . ' ' . $booking->getType(); ?>
                  </div>
                  <div class="ec_txstatus_data">
                    <?php
                    if (method_exists($booking, 'getAmount')) {
                    ?>
                      <dl class="ec_txstatus_data_list">
                        <dt><?php echo TEXT_TEAMBANK_TRANSACTIONS_AMOUNT; ?></dt>
                        <dd><?php echo xtc_format_price_order($booking->getAmount(), 1, $order->info['currency'], 1); ?></dd>
                      </dl>
                    <?php
                    }
                    ?>
                    <dl class="ec_txstatus_data_list">
                      <dt><?php echo TEXT_TEAMBANK_TRANSACTIONS_STATE; ?></dt>
                      <dd><?php echo $booking->getStatus(); ?></dd>
                    </dl>
                    <dl class="ec_txstatus_data_list">
                      <dt><?php echo TEXT_TEAMBANK_TRANSACTIONS_ID; ?></dt>
                      <dd><?php echo $booking->getUuid(); ?></dd>
                    </dl>
                  </div>
                </div>
                <?php
              }
            ?>
            </div>
            <div style="clear:both;"></div>
            <?php
          }

          if ($admin_info_data->getStatus() == \Teambank\EasyCreditApiV3\Model\TransactionResponse::STATUS_REPORT_CAPTURE
              || $admin_info_data->getStatus() == \Teambank\EasyCreditApiV3\Model\TransactionResponse::STATUS_REPORT_CAPTURE_EXPIRING
              )
          {
            $tracking_array = array();
            $tracking_query = xtc_db_query("SELECT *
                                              FROM ".TABLE_ORDERS_TRACKING."
                                             WHERE orders_id = '".(int)$order->info['order_id']."'");
            if (xtc_db_num_rows($tracking_query)) {
              while ($tracking = xtc_db_fetch_array($tracking_query)) {
                if (!isset($tracker_array[$tracking['parcel_id']])) {
                  $tracking_array[$tracking['parcel_id']] = $tracking;
                }
              }
            }
            ?>
            <div class="ec_tracking ec_box">
              <?php                                
                echo '<div class="ec_boxheading">'.TEXT_TEAMBANK_TRACKING_TRACE.'</div>';
                echo xtc_draw_form('tracking', xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action','subaction', 'ext', 'sec')).'action=custom&subaction=teambankaction', 'NONSSL'), 'post');
                if (CSRF_TOKEN_SYSTEM == 'true' && isset($_SESSION['CSRFToken']) && isset($_SESSION['CSRFName'])) {
                  echo xtc_draw_hidden_field($_SESSION['CSRFName'], $_SESSION['CSRFToken']);
                }
                echo xtc_draw_hidden_field('cmd', 'capture');
                echo xtc_draw_hidden_field('transactionId', $admin_info_data->getTransactionId());
        
                if (count($tracking_array) > 0) {
                  $i = 0;
                  foreach ($tracking_array as $tracking) {
                    echo '<div class="tracking_row">';
                    echo '<label for="track_'.$tracking['tracking_id'].'" style="width:100%">';
                    echo xtc_datetime_short($tracking['date_added']) . ' ' . $tracking['parcel_id'] . xtc_draw_radio_field('tracking', $tracking['parcel_id'], ($i == 0) , 'style="float:right;" id="track_'.$tracking['tracking_id'].'"');
                    echo '</label>';     
                    echo '</div>';               
                    $i++;
                  }
                } else {
                  echo '<div class="tracking_row">'.TEXT_TEAMBANK_TRACKING_NO_INFO.'</div>';               
                }
              ?>
              <br />
              <input type="submit" class="button" name="tracking_submit" value="<?php echo TEXT_TEAMBANK_TRACKING_SUBMIT; ?>">
              </form>
            </div>
            <?php 
          }

          if (($admin_info_data->getStatus() == \Teambank\EasyCreditApiV3\Model\TransactionResponse::STATUS_IN_BILLING
               || $admin_info_data->getStatus() == \Teambank\EasyCreditApiV3\Model\TransactionResponse::STATUS_BILLED
               ) && $admin_info_data->getOrderDetails()->getCurrentOrderValue() > 0
              )
          {
            ?>
            <div class="ec_capture ec_box">
              <div class="ec_boxheading"><?php echo TEXT_TEAMBANK_REFUND; ?></div>
              <?php 
                echo xtc_draw_form('capture', xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action','subaction', 'ext', 'sec')).'action=custom&subaction=teambankaction', 'NONSSL'), 'post');
                if (CSRF_TOKEN_SYSTEM == 'true' && isset($_SESSION['CSRFToken']) && isset($_SESSION['CSRFName'])) {
                  echo xtc_draw_hidden_field($_SESSION['CSRFName'], $_SESSION['CSRFToken']);
                }
                echo xtc_draw_hidden_field('cmd', 'refund');
                echo xtc_draw_hidden_field('transactionId', $admin_info_data->getTransactionId());

                echo '<div class="refund_row">';
                echo '<label for="refund_price">'.TEXT_TEAMBANK_REFUND_AMOUNT.'</label>';
                echo xtc_draw_input_field('refund_price', '', 'id="refund_price" style="width: 135px"');
                echo '</div>';
              ?>
              <br />
              <input type="submit" class="button" name="refund_submit" value="<?php echo TEXT_TEAMBANK_REFUND_SUBMIT; ?>">
              </form>
            </div>
            <?php 
          }

          ?>
          </td>
        </tr>
      </table>      
      <?php
    } else {
      ?>
      <table border="0" width="100%" cellspacing="0" cellpadding="2" class="dataTableRow teambank_data" style="display:none;">
        <tr>
          <td width="100%" valign="top">
            <div class="info_message"><?php echo TEXT_TEAMBANK_NO_INFORMATION; ?></div>
          </td>
        </tr>
      </table>
      <?php
    }
    ?>
    <script type="text/javascript">
      $(function() {
        $('div.ec_txstatus_received').not('.ec_txstatus_open').click(function(e) {
          if ($(this).hasClass('ec_txstatus_open')) {
            $('div.ec_txstatus_received').removeClass('ec_txstatus_open');
            $('div.ec_txstatus_data', $(this).parent()).hide();
          } else {
            $('div.ec_txstatus_received').removeClass('ec_txstatus_open');
            $(this).addClass('ec_txstatus_open');
            $('div.ec_txstatus_data').hide();
            $('div.ec_txstatus_data', $(this).parent()).show();
          }
        });
      });
    </script>
    <?php
  }
