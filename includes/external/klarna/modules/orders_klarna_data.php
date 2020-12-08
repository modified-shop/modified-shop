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
  if ($order->info['payment_method'] == 'klarna_checkout' 
      || $order->info['payment_method'] == 'klarna_paylater'
      || $order->info['payment_method'] == 'klarna_payovertime'
      || $order->info['payment_method'] == 'klarna_directdebit'
      || $order->info['payment_method'] == 'klarna_directbanktransfer'
      ) 
  {
    require_once (DIR_FS_INC.'xtc_format_price_order.inc.php');

    require_once (DIR_WS_LANGUAGES.$order->info['language'].'/modules/payment/'.$order->info['payment_method'].'.php');

    require_once(DIR_FS_EXTERNAL.'klarna/classes/KlarnaPayment.php');
    $klarna = new KlarnaPayment($order->info['payment_method']);
    
    $admin_info_array = array();
    
    if ($order_id = $klarna->get_klarna_order($order->info['order_id'])) {
      $admin_info_array = $klarna->fetchOrder($order_id);
    }

    if (count($admin_info_array) > 0) {
      ?>          
      <table border="0" width="100%" cellspacing="0" cellpadding="2" class="dataTableRow klarna_data" style="display:none;">
        <tr>
          <td width="100%" valign="top">
            <div class="pp_transactions pp_box">
              <div class="pp_boxheading"><?php echo TEXT_KLARNA_TRANSACTION; ?></div>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_KLARNA_TRANSACTION_METHOD; ?></dt>
                <dd><?php echo $admin_info_array['initial_payment_method']['description']; ?></dd>
              </dl>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_KLARNA_TRANSACTION_REFERENCE; ?></dt>
                <dd><?php echo $admin_info_array['klarna_reference']; ?></dd>
              </dl>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_KLARNA_TRANSACTION_STATUS; ?></dt>
                <dd><?php echo $admin_info_array['status']; ?></dd>
              </dl>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_KLARNA_TRANSACTION_FRAUD_STATUS; ?></dt>
                <dd><?php echo $admin_info_array['fraud_status']; ?></dd>
              </dl>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_KLARNA_TRANSACTIONS_TOTAL; ?></dt>
                <dd><?php echo xtc_format_price_order($admin_info_array['original_order_amount']/100, 1, $admin_info_array['purchase_currency'], 1); ?></dd>
              </dl>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_KLARNA_TRANSACTIONS_CAPTURED; ?></dt>
                <dd><?php echo xtc_format_price_order($admin_info_array['captured_amount']/100, 1, $admin_info_array['purchase_currency'], 1); ?></dd>
              </dl>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_KLARNA_TRANSACTIONS_REFUNDED; ?></dt>
                <dd><?php echo xtc_format_price_order($admin_info_array['refunded_amount']/100, 1, $admin_info_array['purchase_currency'], 1); ?></dd>
              </dl>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_KLARNA_TRANSACTIONS_REMAINING; ?></dt>
                <dd><?php echo xtc_format_price_order($admin_info_array['remaining_authorized_amount']/100, 1, $admin_info_array['purchase_currency'], 1); ?></dd>
              </dl>
            </div>

            <div class="pp_txstatus pp_box">
              <div class="pp_boxheading"><?php echo TEXT_KLARNA_TRANSACTIONS_STATUS; ?></div>
              <?php
              $transactions = array();
              if (count($admin_info_array['captures']) > 0) {
                foreach ($admin_info_array['captures'] as $capture) {
                  $transactions[strtotime($capture['captured_at'])] = array(
                    'id' => $capture['capture_id'],
                    'date' => $capture['captured_at'],
                    'amount' => $capture['captured_amount'],
                    'reference' => $capture['klarna_reference'],
                    'type' => TEXT_KLARNA_CAPTURE,
                  );
                }
              }
              
              if (count($admin_info_array['refunds']) > 0) {
                foreach ($admin_info_array['refunds'] as $refund) {
                  $transactions[strtotime($refund['refunded_at'])] = array(
                    'id' => $refund['refund_id'],
                    'date' => $refund['refunded_at'],
                    'amount' => $refund['refunded_amount'],
                    'type' => TEXT_KLARNA_REFUND,
                  );
                }
              }
              ksort($transactions);
              
              foreach ($transactions as $transaction) {
                ?>
                <div class="pp_txstatus">
                  <div class="pp_txstatus_received pp_received_icon">
                    <?php echo xtc_datetime_short($transaction['date']).' - '.$transaction['type']; ?>
                  </div>
                  <div class="pp_txstatus_data">
                    <dl class="pp_txstatus_data_list">
                      <dt><?php echo TEXT_KLARNA_TRANSACTION_ID; ?></dt>
                      <dd><?php echo $transaction['id']; ?></dd>
                    </dl>
                    <dl class="pp_txstatus_data_list">
                      <dt><?php echo TEXT_KLARNA_TRANSACTION_TOTAL; ?></dt>
                      <dd><?php echo xtc_format_price_order($transaction['amount']/100, 1, $admin_info_array['purchase_currency'], 1); ?></dd>
                    </dl>
                    <dl class="pp_txstatus_data_list">
                      <dt><?php echo TEXT_KLARNA_TRANSACTION_REFERENCE; ?></dt>
                      <dd><?php echo $transaction['reference']; ?></dd>
                    </dl>
                  </div>
                </div>
                <?php
              }
              ?>
            </div>
            <div style="clear:both;"></div>

            <?php
            if ($admin_info_array['remaining_authorized_amount'] > 0
                && strtolower($admin_info_array['status']) != 'cancelled'
                )
            {
              ?>
              <div class="pp_capture pp_box">
                <div class="pp_boxheading"><?php echo TEXT_KLARNA_CAPTURE; ?></div>
                <?php 
                  echo xtc_draw_form('capture', xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action','subaction', 'ext', 'sec')).'action=custom&subaction=klarnaaction', 'NONSSL'), 'post');
                  if (CSRF_TOKEN_SYSTEM == 'true' && isset($_SESSION['CSRFToken']) && isset($_SESSION['CSRFName'])) {
                    echo xtc_draw_hidden_field($_SESSION['CSRFName'], $_SESSION['CSRFToken']);
                  }
                  echo xtc_draw_hidden_field('cmd', 'capture');
                  ?>
                  <div class="refund_row">
                    <dl class="pp_transaction">
                      <dt><?php echo TEXT_KLARNA_CAPTURE_MAX_AMOUNT; ?></dt>
                      <dd><?php echo xtc_format_price_order($admin_info_array['remaining_authorized_amount']/100, 1, $admin_info_array['purchase_currency'], 1); ?></dd>
                    </dl>
                    <dl class="pp_transaction">
                      <dt style="line-height: 28px;"><?php echo TEXT_KLARNA_CAPTURE_AMOUNT; ?></dt>
                      <dd><?php echo xtc_draw_input_field('amount', '', 'style="width: 135px"'); ?></dd>
                    </dl>
                  </div>
                  <br />
                  <input type="submit" class="button flt-r" name="capture_submit" value="<?php echo TEXT_KLARNA_CAPTURE_SUBMIT; ?>">
                  <?php
                  if ($admin_info_array['captured_amount'] == 0) {
                    ?>
                    <input type="submit" class="button flt-l" name="cancel_submit" value="<?php echo TEXT_KLARNA_CANCEL_SUBMIT; ?>">
                    <?php
                  }
                  ?>
                </form>
              </div>
              <?php 
            } 

            if ($admin_info_array['captured_amount'] > 0
                && $admin_info_array['refunded_amount'] < $admin_info_array['order_amount']
                && strtolower($admin_info_array['status']) != 'cancelled'
                )
            {
              ?>
              <div class="pp_capture pp_box">
                <div class="pp_boxheading"><?php echo TEXT_KLARNA_REFUND; ?></div>
                <?php 
                  echo xtc_draw_form('capture', xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action','subaction', 'ext', 'sec')).'action=custom&subaction=klarnaaction', 'NONSSL'), 'post');
                  if (CSRF_TOKEN_SYSTEM == 'true' && isset($_SESSION['CSRFToken']) && isset($_SESSION['CSRFName'])) {
                    echo xtc_draw_hidden_field($_SESSION['CSRFName'], $_SESSION['CSRFToken']);
                  }
                  echo xtc_draw_hidden_field('cmd', 'refund');
                  ?>
                  <div class="refund_row">
                    <dl class="pp_transaction">
                      <dt><?php echo TEXT_KLARNA_REFUND_MAX_AMOUNT; ?></dt>
                      <dd><?php echo xtc_format_price_order(($admin_info_array['captured_amount']-$admin_info_array['refunded_amount'])/100, 1, $admin_info_array['purchase_currency'], 1); ?></dd>
                    </dl>
                    <dl class="pp_transaction">
                      <dt style="line-height: 28px;"><?php echo TEXT_KLARNA_REFUND_AMOUNT; ?></dt>
                      <dd><?php echo xtc_draw_input_field('amount', '', 'style="width: 135px"'); ?></dd>
                    </dl>
                  </div>
                  <br>
                  <input type="submit" class="button flt-r" name="refund_submit" value="<?php echo TEXT_KLARNA_REFUND_SUBMIT; ?>">
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
      <table border="0" width="100%" cellspacing="0" cellpadding="2" class="dataTableRow klarna_data" style="display:none;">
        <tr>
          <td width="100%" valign="top">
            <div class="info_message"><?php echo TEXT_KLARNA_NO_INFORMATION; ?></div>
          </td>
        </tr>
      </table>
    <?php
    }
  }
  ?>
  <script type="text/javascript">
    $(function() {
      $('div.pp_txstatus_received').not('.pp_txstatus_open').click(function(e) {
        if ($(this).hasClass('pp_txstatus_open')) {
          $('div.pp_txstatus_received').removeClass('pp_txstatus_open');
          $('div.pp_txstatus_data', $(this).parent()).hide();
        } else {
          $('div.pp_txstatus_received').removeClass('pp_txstatus_open');
          $(this).addClass('pp_txstatus_open');
          $('div.pp_txstatus_data').hide();
          $('div.pp_txstatus_data', $(this).parent()).show();
        }
      });
    });
  </script>
<?php
}
