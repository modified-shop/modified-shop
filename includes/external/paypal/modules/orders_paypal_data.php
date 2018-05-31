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
  if ($order->info['payment_method'] == 'paypalclassic' 
      || $order->info['payment_method'] == 'paypalcart'
      || $order->info['payment_method'] == 'paypalplus'
      || $order->info['payment_method'] == 'paypallink'
      || $order->info['payment_method'] == 'paypalpluslink'
      || $order->info['payment_method'] == 'paypalinstallment'
      ) 
  {
    require_once (DIR_FS_INC.'xtc_format_price_order.inc.php');

    require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalInfo.php');
    $paypal = new PayPalInfo($order->info['payment_method']);
      
    // payment
    $admin_info_array = $paypal->order_info($order->info['order_id']);
  
    if (count($admin_info_array) > 0) {
      ?>          
      <table border="0" width="100%" cellspacing="0" cellpadding="2" class="dataTableRow paypal_data" style="display:none;">
        <tr>
          <td width="100%" valign="top">
            <div class="pp_transactions pp_box">
              <div class="pp_boxheading"><?php echo TEXT_PAYPAL_TRANSACTION; ?></div>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_PAYPAL_TRANSACTION_ADDRESS; ?></dt>
                <dd><?php echo xtc_address_format($order->customer['address_format_id'], $admin_info_array['address'], 1, '', '<br />'); ?></dd>
              </dl>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_PAYPAL_TRANSACTION_METHOD; ?></dt>
                <dd><?php echo $admin_info_array['payment_method']; ?></dd>
              </dl>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_PAYPAL_TRANSACTION_EMAIL; ?></dt>
                <dd><?php echo $admin_info_array['email_address']; ?></dd>
              </dl>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_PAYPAL_TRANSACTION_ACCOUNT_STATE; ?></dt>
                <dd><?php echo $admin_info_array['account_status']; ?></dd>
              </dl>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_PAYPAL_TRANSACTION_INTENT; ?></dt>
                <dd><?php echo $admin_info_array['intent']; ?></dd>
              </dl>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_PAYPAL_TRANSACTIONS_TOTAL; ?></dt>
                <dd><?php echo xtc_format_price_order($admin_info_array['total'], 1, $admin_info_array['transactions'][0]['relatedResource'][0]['currency'], 1); ?></dd>
              </dl>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_PAYPAL_TRANSACTION_STATE; ?></dt>
                <dd><?php echo $admin_info_array['state']; ?></dd>
              </dl>
            </div>

            <div class="pp_txstatus pp_box">
              <div class="pp_boxheading"><?php echo TEXT_PAYPAL_TRANSACTIONS_STATUS; ?></div>
              <?php
              $status_array = array();
              $type_array = array();
              for ($t=0, $z=count($admin_info_array['transactions']); $t<$z; $t++) {
                for ($i=0, $n=count($admin_info_array['transactions'][$t]['relatedResource']); $i<$n; $i++) {
                  $status_array[] = $admin_info_array['transactions'][$t]['relatedResource'][$i]['state'];
                  $type_array[] = $admin_info_array['transactions'][$t]['relatedResource'][$i]['type'];
                
                  $amount_array[$admin_info_array['transactions'][$t]['relatedResource'][$i]['type']] += (($admin_info_array['transactions'][$t]['relatedResource'][$i]['total'] < 0) ? ($admin_info_array['transactions'][$t]['relatedResource'][$i]['total'] * (-1)) : $admin_info_array['transactions'][$t]['relatedResource'][$i]['total']);
                  ?>
                  <div class="pp_txstatus">
                    <div class="pp_txstatus_received pp_received_icon">
                      <?php echo xtc_datetime_short($admin_info_array['transactions'][$t]['relatedResource'][$i]['date']) . ' ' . $admin_info_array['transactions'][$t]['relatedResource'][$i]['type']; ?>
                    </div>
                    <div class="pp_txstatus_data">
                      <?php
                      if ($admin_info_array['transactions'][$t]['relatedResource'][$i]['payment'] != '') {
                      ?>
                        <dl class="pp_txstatus_data_list">
                          <dt><?php echo TEXT_PAYPAL_TRANSACTIONS_PAYMENT; ?></dt>
                          <dd><?php echo $admin_info_array['transactions'][$t]['relatedResource'][$i]['payment']; ?></dd>
                        </dl>
                      <?php
                      }
                      if ($admin_info_array['transactions'][$t]['relatedResource'][$i]['reason'] != '') {
                      ?>
                        <dl class="pp_txstatus_data_list">
                          <dt><?php echo TEXT_PAYPAL_TRANSACTIONS_REASON; ?></dt>
                          <dd><?php echo $admin_info_array['transactions'][$t]['relatedResource'][$i]['reason']; ?></dd>
                        </dl>
                      <?php
                      }
                      ?>
                      <dl class="pp_txstatus_data_list">
                        <dt><?php echo TEXT_PAYPAL_TRANSACTIONS_STATE; ?></dt>
                        <dd><?php echo $admin_info_array['transactions'][$t]['relatedResource'][$i]['state']; ?></dd>
                      </dl>
                      <dl class="pp_txstatus_data_list">
                        <dt><?php echo TEXT_PAYPAL_TRANSACTIONS_TOTAL; ?></dt>
                        <dd><?php echo xtc_format_price_order($admin_info_array['transactions'][$t]['relatedResource'][$i]['total'], 1, $admin_info_array['transactions'][$t]['relatedResource'][$i]['currency'], 1); ?></dd>
                      </dl>
                      <?php
                      if ($admin_info_array['transactions'][$t]['relatedResource'][$i]['valid'] != '') {
                      ?>
                        <dl class="pp_txstatus_data_list">
                          <dt><?php echo TEXT_PAYPAL_TRANSACTIONS_VALID; ?></dt>
                          <dd><?php echo xtc_datetime_short($admin_info_array['transactions'][$t]['relatedResource'][$i]['valid']); ?></dd>
                        </dl>
                      <?php
                      }
                      ?>
                      <dl class="pp_txstatus_data_list">
                        <dt><?php echo TEXT_PAYPAL_TRANSACTIONS_ID; ?></dt>
                        <dd><?php echo $admin_info_array['transactions'][$t]['relatedResource'][$i]['id']; ?></dd>
                      </dl>
                    </div>
                  </div>
                  <?php
                }
              }
              ?>
            </div>
            <div style="clear:both;"></div>

            <?php
            if (isset($admin_info_array['instruction'])) {
              ?>
              <div class="pp_transactions pp_box">
                <div class="pp_boxheading"><?php echo TEXT_PAYPAL_INSTRUCTIONS; ?></div>
                <dl class="pp_transaction">
                  <dt><?php echo TEXT_PAYPAL_INSTRUCTIONS_AMOUNT; ?></dt>
                  <dd><?php echo $admin_info_array['instruction']['amount']['total'].' '.$admin_info_array['instruction']['amount']['currency']; ?></dd>
                </dl>
                <dl class="pp_transaction">
                  <dt><?php echo TEXT_PAYPAL_INSTRUCTIONS_REFERENCE; ?></dt>
                  <dd><?php echo $admin_info_array['instruction']['reference']; ?></dd>
                </dl>
                <dl class="pp_transaction">
                  <dt><?php echo TEXT_PAYPAL_INSTRUCTIONS_PAYDATE; ?></dt>
                  <dd><?php echo $admin_info_array['instruction']['date']; ?></dd>
                </dl>
                <dl class="pp_transaction">
                  <dt><?php echo TEXT_PAYPAL_INSTRUCTIONS_ACCOUNT; ?></dt>
                  <dd><?php echo $admin_info_array['instruction']['bank']['name']; ?></dd>
                </dl>
                <dl class="pp_transaction">
                  <dt><?php echo TEXT_PAYPAL_INSTRUCTIONS_HOLDER; ?></dt>
                  <dd><?php echo $admin_info_array['instruction']['bank']['holder']; ?></dd>
                </dl>
                <dl class="pp_transaction">
                  <dt><?php echo TEXT_PAYPAL_INSTRUCTIONS_IBAN; ?></dt>
                  <dd><?php echo $admin_info_array['instruction']['bank']['iban']; ?></dd>
                </dl>
                <dl class="pp_transaction">
                  <dt><?php echo TEXT_PAYPAL_INSTRUCTIONS_BIC; ?></dt>
                  <dd><?php echo $admin_info_array['instruction']['bank']['bic']; ?></dd>
                </dl>
              </div>
              <?php
            }

            $count = array_count_values($type_array);
            if ($admin_info_array['intent'] == 'authorize' && $admin_info_array['total'] > $amount_array['capture']) {
              ?>
              <div class="pp_capture pp_box">
                <div class="pp_boxheading"><?php echo TEXT_PAYPAL_CAPTURE; ?></div>
                <?php 
                  if (defined('RUN_MODE_ADMIN')) {
                    echo xtc_draw_form('capture', FILENAME_ORDERS, xtc_get_all_get_params(array('action','subaction')).'action=custom&subaction=paypalaction');
                  } else {
                    echo xtc_draw_form('capture', xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action','subaction', 'ext')).'action=custom&subaction=paypalaction', 'NONSSL'), 'post');
                    if (CSRF_TOKEN_SYSTEM == 'true' && isset($_SESSION['CSRFToken']) && isset($_SESSION['CSRFName'])) {
                      echo xtc_draw_hidden_field($_SESSION['CSRFName'], $_SESSION['CSRFToken']);
                    }
                  }
                  echo xtc_draw_hidden_field('cmd', 'capture');

                  echo '<div class="refund_row">';
                  echo '<div class="'.(((10 - $count['capture']) > 0) ? 'info_message' : 'error_message').'">'.TEXT_PAYPAL_CAPTURE_LEFT . ' ' . (10 - $count['capture']).'</div>';
                  echo '<br/>';
                  echo '<label for="final_capture">'.TEXT_PAYPAL_CAPTURE_IS_FINAL.'</label>';
                  echo xtc_draw_checkbox_field('final_capture', '1', '', 'id="final_capture"');
                  echo '<br/>';
                  echo '<label for="capture_price">'.TEXT_PAYPAL_CAPTURE_AMOUNT.'</label>';
                  echo xtc_draw_input_field('capture_price', '', 'id="capture_price" style="width: 135px"');
                  echo '</div>';
                ?>
                <br />
                <input type="submit" class="button" name="capture_submit" value="<?php echo TEXT_PAYPAL_CAPTURE_SUBMIT; ?>">
                </form>
              </div>
              <?php 
            } 

            if ((in_array('captured', $status_array)
                 || in_array('completed', $status_array)
                 ) && $admin_info_array['total'] > $amount_array['refund']
                )
            {
              ?>
              <div class="pp_capture pp_box">
                <div class="pp_boxheading"><?php echo TEXT_PAYPAL_REFUND; ?></div>
                <?php 
                  if (defined('RUN_MODE_ADMIN')) {
                    echo xtc_draw_form('capture', FILENAME_ORDERS, xtc_get_all_get_params(array('action','subaction')).'action=custom&subaction=paypalaction');
                  } else {
                    echo xtc_draw_form('capture', xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action','subaction', 'ext')).'action=custom&subaction=paypalaction', 'NONSSL'), 'post');
                    if (CSRF_TOKEN_SYSTEM == 'true' && isset($_SESSION['CSRFToken']) && isset($_SESSION['CSRFName'])) {
                      echo xtc_draw_hidden_field($_SESSION['CSRFName'], $_SESSION['CSRFToken']);
                    }
                  }
                  echo xtc_draw_hidden_field('cmd', 'refund');

                  echo '<div class="refund_row">';
                  echo '<div class="'.(((10 - $count['refund']) > 0) ? 'info_message' : 'error_message').'">'.TEXT_PAYPAL_REFUND_LEFT . ' ' . (10 - $count['refund']).'</div>';
                  echo '<br/>';
                  echo '<label for="refund_comment" style="vertical-align: top; margin-top: 5px;">'.TEXT_PAYPAL_REFUND_COMMENT.'</label>';
                  echo xtc_draw_textarea_field('refund_comment', '', '60', '8', '', 'id="refund_comment" maxlength="127"');
                  echo '<br/>';
                  echo '<label for="refund_price">'.TEXT_PAYPAL_REFUND_AMOUNT.'</label>';
                  echo xtc_draw_input_field('refund_price', '', 'id="refund_price" style="width: 135px"');
                  echo '</div>';
                ?>
                <br />
                <input type="submit" class="button" name="refund_submit" value="<?php echo TEXT_PAYPAL_REFUND_SUBMIT; ?>">
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
      <table border="0" width="100%" cellspacing="0" cellpadding="2" class="dataTableRow paypal_data" style="display:none;">
        <tr>
          <td width="100%" valign="top">
            <div class="info_message"><?php echo TEXT_PAYPAL_NO_INFORMATION; ?></div>
          </td>
        </tr>
      </table>
    <?php
    }
  }
  if (is_file(DIR_FS_CATALOG.'includes/extra/ajax/get_paypal_data.php')) {
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
}
?>