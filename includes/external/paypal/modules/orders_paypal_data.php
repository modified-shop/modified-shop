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
  $orders_v1_array = array(
    'paypalclassic',
    'paypalcart',
    'paypalplus',
    'paypallink',
    'paypalpluslink',
    'paypalsubscription',
  );

  $orders_v2_array = array(
    'paypal',
    'paypalacdc',
    'paypalpui',
    'paypalexpress',
    'paypalapplepay',
    'paypalgooglepay',
    'paypalcard',
    'paypalsepa',
    'paypalsofort',
    'paypaltrustly',
    'paypalprzelewy',
    'paypalmybank',
    'paypalideal',
    'paypalgiropay',
    'paypaleps',
    'paypalblik',
    'paypalbancontact',
  );
  
  if (in_array($order->info['payment_method'], $orders_v1_array)
      || in_array($order->info['payment_method'], $orders_v2_array)
      ) 
  {
    // include needed functions
    require_once (DIR_FS_INC.'xtc_format_price_order.inc.php');
    if (!function_exists('xtc_date_short')) {
      require_once(DIR_FS_INC.'xtc_date_short.inc.php');
    }

    // include needed classes
    require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalInfo.php');
    $paypal = new PayPalInfo($order->info['payment_method']);
      
    if ($order->info['payment_method'] == 'paypalsubscription') {
      $admin_info_data = $paypal->subscription_info($order->info['order_id']);
    } elseif (in_array($order->info['payment_method'], $orders_v2_array)) {
      require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPaymentV2.php');
      $paypalv2 = new PayPalPaymentV2($order->info['payment_method']);
      $admin_info_data = $paypalv2->GetOrderDetails($order->info['order_id']);
    } else {
      // payment
      $admin_info_data = $paypal->order_info($order->info['order_id']);
    }
    
    if (is_object($admin_info_data) 
        || (is_array($admin_info_data) && count($admin_info_data) > 0)
        )
    {
      ?>
      <table border="0" width="100%" cellspacing="0" cellpadding="2" class="dataTableRow paypal_data" style="display:none;">
        <tr>
          <td width="100%" valign="top">
          <?php
          if (is_array($admin_info_data) && count($admin_info_data) > 0) {
            ?>          
            <div class="pp_transactions pp_box">
              <div class="pp_boxheading"><?php echo TEXT_PAYPAL_TRANSACTION; ?></div>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_PAYPAL_TRANSACTION_ADDRESS; ?></dt>
                <dd><?php echo xtc_address_format($order->customer['format_id'], $paypal->decode_utf8($admin_info_data['address']), 1, '', '<br />'); ?></dd>
              </dl>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_PAYPAL_TRANSACTION_METHOD; ?></dt>
                <dd><?php echo $admin_info_data['payment_method']; ?></dd>
              </dl>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_PAYPAL_TRANSACTION_ACCOUNT_OWNER; ?></dt>
                <dd><?php echo $paypal->decode_utf8($admin_info_data['address']['name']); ?></dd>
              </dl>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_PAYPAL_TRANSACTION_EMAIL; ?></dt>
                <dd><?php echo $admin_info_data['email_address']; ?></dd>
              </dl>
              <?php if ($admin_info_data['account_status'] != '') { ?>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_PAYPAL_TRANSACTION_ACCOUNT_STATE; ?></dt>
                <dd><?php echo $admin_info_data['account_status']; ?></dd>
              </dl>
              <?php } ?>
              <?php if ($admin_info_data['intent'] != '') { ?>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_PAYPAL_TRANSACTION_INTENT; ?></dt>
                <dd><?php echo $admin_info_data['intent']; ?></dd>
              </dl>
              <?php } ?>
              <?php if ($admin_info_data['total'] > 0) { ?>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_PAYPAL_TRANSACTIONS_TOTAL; ?></dt>
                <dd><?php echo xtc_format_price_order($admin_info_data['total'], 1, $admin_info_data['transactions'][0]['relatedResource'][0]['currency'], 1); ?></dd>
              </dl>
              <?php } ?>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_PAYPAL_TRANSACTION_STATE; ?></dt>
                <dd><?php echo $admin_info_data['state']; ?></dd>
              </dl>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_PAYPAL_TRANSACTION_STATE; ?></dt>
                <dd><?php echo $admin_info_data['state']; ?></dd>
              </dl>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_PAYPAL_TRANSACTION_ACTION; ?></dt>
                <dd>
                  <?php
                  echo xtc_draw_form('delete', xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action','subaction', 'ext', 'sec')).'action=custom&subaction=paypalaction', 'NONSSL'), 'post');
                  if (CSRF_TOKEN_SYSTEM == 'true' && isset($_SESSION['CSRFToken']) && isset($_SESSION['CSRFName'])) {
                    echo xtc_draw_hidden_field($_SESSION['CSRFName'], $_SESSION['CSRFToken']);
                  }
                  echo xtc_draw_hidden_field('cmd', 'delete');
                  ?>
                  <input type="submit" class="button" name="delete_submit" value="<?php echo TEXT_PAYPAL_TRANSACTION_SUBMIT; ?>">
                </dd>
              </dl>
            </div>
      
            <?php
            if (isset($admin_info_data['billing'])
                && count($admin_info_data['billing']) > 0
                )
            {
              ?>
              <div class="pp_txstatus pp_box">
                <div class="pp_boxheading"><?php echo TEXT_PAYPAL_BILLING; ?></div>
                <dl class="pp_transaction">
                  <dt><?php echo TEXT_PAYPAL_BILLING_OUTSTANDING; ?></dt>
                  <dd><?php echo xtc_format_price_order($admin_info_data['billing']['outstanding_balance'], 1, $admin_info_data['billing']['currency'], 1); ?></dd>
                </dl>
                <dl class="pp_transaction">
                  <dt><?php echo TEXT_PAYPAL_BILLING_CYCLES_COMPLETED; ?></dt>
                  <dd><?php echo $admin_info_data['billing']['cycle_executions']['cycles_completed']; ?></dd>
                </dl>
                <dl class="pp_transaction">
                  <dt><?php echo TEXT_PAYPAL_BILLING_CYCLES_REMAINING; ?></dt>
                  <dd><?php echo $admin_info_data['billing']['cycle_executions']['cycles_remaining']; ?></dd>
                </dl>
                <dl class="pp_transaction">
                  <dt><?php echo TEXT_PAYPAL_BILLING_CYCLES_TOTAL; ?></dt>
                  <dd><?php echo $admin_info_data['billing']['cycle_executions']['total_cycles']; ?></dd>
                </dl>
                <dl class="pp_transaction">
                  <dt><?php echo TEXT_PAYPAL_BILLING_TIME_NEXT; ?></dt>
                  <dd><?php echo xtc_datetime_short($admin_info_data['billing']['next_billing_time']); ?></dd>
                </dl>
                <dl class="pp_transaction">
                  <dt><?php echo TEXT_PAYPAL_BILLING_TIME_FINAL; ?></dt>
                  <dd><?php echo xtc_datetime_short($admin_info_data['billing']['final_payment_time']); ?></dd>
                </dl>
                <dl class="pp_transaction">
                  <dt><?php echo TEXT_PAYPAL_BILLING_FAILED; ?></dt>
                  <dd><?php echo $admin_info_data['billing']['failed_payments_count']; ?></dd>
                </dl>
              </div>
              <div style="clear:both;"></div>
            <?php } ?>
      
            <?php
            if (isset($admin_info_data['transactions'])
                && count($admin_info_data['transactions']) > 0
                )
            {
              ?>
              <div class="pp_txstatus pp_box">
              <div class="pp_boxheading"><?php echo TEXT_PAYPAL_TRANSACTIONS_STATUS; ?></div>
              <?php
              $status_array = array();
              $type_array = array();
              $amount_array = array();
        
              for ($t=0, $z=count($admin_info_data['transactions']); $t<$z; $t++) {
                for ($i=0, $n=count($admin_info_data['transactions'][$t]['relatedResource']); $i<$n; $i++) {
                  $status_array[] = $admin_info_data['transactions'][$t]['relatedResource'][$i]['state'];
                  $type_array[] = $admin_info_data['transactions'][$t]['relatedResource'][$i]['type'];
            
                  if (!isset($amount_array[$admin_info_data['transactions'][$t]['relatedResource'][$i]['type']])) {
                    $amount_array[$admin_info_data['transactions'][$t]['relatedResource'][$i]['type']] = 0;
                  }
                  $amount_array[$admin_info_data['transactions'][$t]['relatedResource'][$i]['type']] += (($admin_info_data['transactions'][$t]['relatedResource'][$i]['total'] < 0) ? ($admin_info_data['transactions'][$t]['relatedResource'][$i]['total'] * (-1)) : $admin_info_data['transactions'][$t]['relatedResource'][$i]['total']);
                  ?>
                  <div class="pp_txstatus">
                    <div class="pp_txstatus_received pp_received_icon">
                      <?php echo xtc_datetime_short($admin_info_data['transactions'][$t]['relatedResource'][$i]['date']) . ' ' . $admin_info_data['transactions'][$t]['relatedResource'][$i]['type']; ?>
                    </div>
                    <div class="pp_txstatus_data">
                      <?php
                      if ($admin_info_data['transactions'][$t]['relatedResource'][$i]['payment'] != '') {
                      ?>
                        <dl class="pp_txstatus_data_list">
                          <dt><?php echo TEXT_PAYPAL_TRANSACTIONS_PAYMENT; ?></dt>
                          <dd><?php echo $admin_info_data['transactions'][$t]['relatedResource'][$i]['payment']; ?></dd>
                        </dl>
                      <?php
                      }
                      if ($admin_info_data['transactions'][$t]['relatedResource'][$i]['reason'] != '') {
                      ?>
                        <dl class="pp_txstatus_data_list">
                          <dt><?php echo TEXT_PAYPAL_TRANSACTIONS_REASON; ?></dt>
                          <dd><?php echo $admin_info_data['transactions'][$t]['relatedResource'][$i]['reason']; ?></dd>
                        </dl>
                      <?php
                      }
                      ?>
                      <dl class="pp_txstatus_data_list">
                        <dt><?php echo TEXT_PAYPAL_TRANSACTIONS_STATE; ?></dt>
                        <dd><?php echo $admin_info_data['transactions'][$t]['relatedResource'][$i]['state']; ?></dd>
                      </dl>
                      <dl class="pp_txstatus_data_list">
                        <dt><?php echo TEXT_PAYPAL_TRANSACTIONS_TOTAL; ?></dt>
                        <dd><?php echo xtc_format_price_order($admin_info_data['transactions'][$t]['relatedResource'][$i]['total'], 1, $admin_info_data['transactions'][$t]['relatedResource'][$i]['currency'], 1); ?></dd>
                      </dl>
                      <?php
                      if ($admin_info_data['transactions'][$t]['relatedResource'][$i]['valid'] != '') {
                      ?>
                        <dl class="pp_txstatus_data_list">
                          <dt><?php echo TEXT_PAYPAL_TRANSACTIONS_VALID; ?></dt>
                          <dd><?php echo xtc_datetime_short($admin_info_data['transactions'][$t]['relatedResource'][$i]['valid']); ?></dd>
                        </dl>
                      <?php
                      }
                      ?>
                      <dl class="pp_txstatus_data_list">
                        <dt><?php echo TEXT_PAYPAL_TRANSACTIONS_ID; ?></dt>
                        <dd><?php echo $admin_info_data['transactions'][$t]['relatedResource'][$i]['id']; ?></dd>
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
            }
                  
            if ($admin_info_data['state'] == 'ACTIVE') {
              ?>
              <div class="pp_capture pp_box">
                <div class="pp_boxheading"><?php echo TEXT_PAYPAL_CANCEL; ?></div>
                <?php 
                  echo xtc_draw_form('capture', xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action','subaction', 'ext', 'sec')).'action=custom&subaction=paypalaction', 'NONSSL'), 'post');
                  if (CSRF_TOKEN_SYSTEM == 'true' && isset($_SESSION['CSRFToken']) && isset($_SESSION['CSRFName'])) {
                    echo xtc_draw_hidden_field($_SESSION['CSRFName'], $_SESSION['CSRFToken']);
                  }
                  echo xtc_draw_hidden_field('cmd', 'cancel');
                ?>
                <br />
                <input type="submit" class="button" name="capture_submit" value="<?php echo TEXT_PAYPAL_CANCEL_SUBMIT; ?>">
                </form>
              </div>
              <?php 
            } 

            $count = array_count_values($type_array);
            if (!isset($count['capture'])) $count['capture'] = 0;
            if (!isset($count['refund'])) $count['refund'] = 0;
      
            if ($admin_info_data['intent'] == 'authorize' 
                && (!isset($amount_array['capture'])
                    || $admin_info_data['total'] > $amount_array['capture']
                    )
                )
            {
              ?>
              <div class="pp_capture pp_box">
                <div class="pp_boxheading"><?php echo TEXT_PAYPAL_CAPTURE; ?></div>
                <?php 
                  echo xtc_draw_form('capture', xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action','subaction', 'ext', 'sec')).'action=custom&subaction=paypalaction', 'NONSSL'), 'post');
                  if (CSRF_TOKEN_SYSTEM == 'true' && isset($_SESSION['CSRFToken']) && isset($_SESSION['CSRFName'])) {
                    echo xtc_draw_hidden_field($_SESSION['CSRFName'], $_SESSION['CSRFToken']);
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
                 ) && (!isset($amount_array['refund'])
                       || $admin_info_data['total'] > $amount_array['refund']
                       )
                )
            {
              ?>
              <div class="pp_capture pp_box">
                <div class="pp_boxheading"><?php echo TEXT_PAYPAL_REFUND; ?></div>
                <?php 
                  echo xtc_draw_form('capture', xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action','subaction', 'ext', 'sec')).'action=custom&subaction=paypalaction', 'NONSSL'), 'post');
                  if (CSRF_TOKEN_SYSTEM == 'true' && isset($_SESSION['CSRFToken']) && isset($_SESSION['CSRFName'])) {
                    echo xtc_draw_hidden_field($_SESSION['CSRFName'], $_SESSION['CSRFToken']);
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
          } elseif (is_object($admin_info_data)) {
            ?>
            <div class="pp_transactions pp_box">
              <div class="pp_boxheading"><?php echo TEXT_PAYPAL_TRANSACTION; ?></div>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_PAYPAL_TRANSACTION_ADDRESS; ?></dt>
                <dd><?php echo xtc_address_format($order->customer['format_id'], $paypal->decode_utf8($admin_info_data->purchase_units[0]->shipping->address_array), 1, '', '<br />'); ?></dd>
              </dl>
              <?php if (isset($admin_info_data->payer)) { ?>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_PAYPAL_TRANSACTION_ACCOUNT_OWNER; ?></dt>
                <dd><?php echo $paypal->decode_utf8($admin_info_data->payer->name->given_name).' '.$paypal->decode_utf8($admin_info_data->payer->name->surname); ?></dd>
              </dl>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_PAYPAL_TRANSACTION_EMAIL; ?></dt>
                <dd><?php echo $admin_info_data->payer->email_address; ?></dd>
              </dl>
              <?php } ?>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_PAYPAL_TRANSACTION_INTENT; ?></dt>
                <dd><?php echo $admin_info_data->intent; ?></dd>
              </dl>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_PAYPAL_TRANSACTIONS_TOTAL; ?></dt>
                <dd><?php echo xtc_format_price_order($admin_info_data->purchase_units[0]->amount->value, 1, $admin_info_data->purchase_units[0]->amount->currency_code, 1); ?></dd>
              </dl>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_PAYPAL_TRANSACTION_STATE; ?></dt>
                <dd><?php echo $admin_info_data->status; ?></dd>
              </dl>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_PAYPAL_TRANSACTION_ID; ?></dt>
                <dd><?php echo $admin_info_data->id; ?></dd>
              </dl>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_PAYPAL_TRANSACTION_ACTION; ?></dt>
                <dd>
                  <?php
                  echo xtc_draw_form('delete', xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action','subaction', 'ext', 'sec')).'action=custom&subaction=paypalaction', 'NONSSL'), 'post');
                  if (CSRF_TOKEN_SYSTEM == 'true' && isset($_SESSION['CSRFToken']) && isset($_SESSION['CSRFName'])) {
                    echo xtc_draw_hidden_field($_SESSION['CSRFName'], $_SESSION['CSRFToken']);
                  }
                  echo xtc_draw_hidden_field('cmd', 'delete');
                  ?>
                  <input type="submit" class="button" name="delete_submit" value="<?php echo TEXT_PAYPAL_TRANSACTION_SUBMIT; ?>">
                </dd>
              </dl>
            </div>

            <?php
            if (isset($admin_info_data->purchase_units[0]->payments)) {
              $capture_reason_array = array();
              $authorize_reason_array = array();
              ?>
              <div class="pp_txstatus pp_box">
              <div class="pp_boxheading"><?php echo TEXT_PAYPAL_TRANSACTIONS_STATUS; ?></div>
              <?php
                $status_array = array();
                $amount_array = array();
                $is_final_capture = false;
              
                foreach ($admin_info_data->purchase_units[0]->payments as $type => $payment) {
                  for ($p=0, $n=count($payment); $p<$n; $p++) {
                    if (!isset($amount_array[$type])) $amount_array[$type] = 0;
                    $amount_array[$type] += $payment[$p]->amount->value;
                    $status_array[$type][] = $payment[$p]->status;
                  
                    if ($type == 'captures') {
                      $capture_reason_array[] = array(
                        'id' => $payment[$p]->id,
                        'text' => xtc_datetime_short($payment[$p]->create_time) . ' ' . xtc_format_price_order($payment[$p]->amount->value, 1, $payment[$p]->amount->currency_code, 1)
                      );
                      if (isset($payment[$p]->final_capture) && $payment[$p]->final_capture == true) {
                        $is_final_capture = true;
                      }
                    } elseif ($type == 'authorizations') {
                      $authorize_reason_array[] = array(
                        'id' => $payment[$p]->id,
                        'text' => xtc_datetime_short($payment[$p]->create_time) . ' ' . xtc_format_price_order($payment[$p]->amount->value, 1, $payment[$p]->amount->currency_code, 1)
                      );
                    }
                    ?>
                    <div class="pp_txstatus">
                      <div class="pp_txstatus_received pp_received_icon">
                        <?php echo xtc_datetime_short($payment[$p]->create_time) . ' ' . $type; ?>
                      </div>
                      <div class="pp_txstatus_data">
                        <?php
                        if (isset($payment[$p]->status_details) && $payment[$p]->status_details->reason != '') {
                        ?>
                          <dl class="pp_txstatus_data_list">
                            <dt><?php echo TEXT_PAYPAL_TRANSACTIONS_REASON; ?></dt>
                            <dd><?php echo $payment[$p]->status_details->reason; ?></dd>
                          </dl>
                        <?php
                        }
                        ?>
                        <dl class="pp_txstatus_data_list">
                          <dt><?php echo TEXT_PAYPAL_TRANSACTIONS_STATE; ?></dt>
                          <dd><?php echo $payment[$p]->status; ?></dd>
                        </dl>
                        <dl class="pp_txstatus_data_list">
                          <dt><?php echo TEXT_PAYPAL_TRANSACTIONS_TOTAL; ?></dt>
                          <dd><?php echo xtc_format_price_order($payment[$p]->amount->value, 1, $payment[$p]->amount->currency_code, 1); ?></dd>
                        </dl>
                        <?php
                        if (isset($payment[$p]->seller_payable_breakdown->paypal_fee->value) && $payment[$p]->seller_payable_breakdown->paypal_fee->value != '') {
                        ?>
                          <dl class="pp_txstatus_data_list">
                            <dt><?php echo TEXT_PAYPAL_TRANSACTIONS_FEE; ?></dt>
                            <dd><?php echo xtc_format_price_order($payment[$p]->seller_payable_breakdown->paypal_fee->value, 1, $payment[$p]->seller_payable_breakdown->paypal_fee->currency_code, 1); ?></dd>
                          </dl>
                        <?php
                        }
                        if (isset($payment[$p]->seller_receivable_breakdown->paypal_fee->value) && $payment[$p]->seller_receivable_breakdown->paypal_fee->value != '') {
                        ?>
                          <dl class="pp_txstatus_data_list">
                            <dt><?php echo TEXT_PAYPAL_TRANSACTIONS_FEE; ?></dt>
                            <dd><?php echo xtc_format_price_order($payment[$p]->seller_receivable_breakdown->paypal_fee->value, 1, $payment[$p]->seller_receivable_breakdown->paypal_fee->currency_code, 1); ?></dd>
                          </dl>
                        <?php
                        }
                        ?>
                        <dl class="pp_txstatus_data_list">
                          <dt><?php echo TEXT_PAYPAL_TRANSACTIONS_ID; ?></dt>
                          <dd><?php echo $payment[$p]->id; ?></dd>
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
            }
          
            $count = array();
            foreach ($status_array as $type => $data) {
              $count[$type] = count($data);
            }
            if (!isset($count['authorizations'])) $count['authorizations'] = 0;
            if (!isset($count['captures'])) $count['captures'] = 0;
            if (!isset($count['refunds'])) $count['refunds'] = 0;
          
            if ($admin_info_data->intent == 'AUTHORIZE' 
                && $is_final_capture !== true
                && (!isset($amount_array['captures'])
                    || $admin_info_data->purchase_units[0]->amount->value > $amount_array['captures']
                    )
                )
            {
              ?>
              <div class="pp_capture pp_box">
                <div class="pp_boxheading"><?php echo TEXT_PAYPAL_CAPTURE; ?></div>
                <?php 
                  echo xtc_draw_form('capture', xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action','subaction', 'ext', 'sec')).'action=custom&subaction=paypalaction', 'NONSSL'), 'post');
                  if (CSRF_TOKEN_SYSTEM == 'true' && isset($_SESSION['CSRFToken']) && isset($_SESSION['CSRFName'])) {
                    echo xtc_draw_hidden_field($_SESSION['CSRFName'], $_SESSION['CSRFToken']);
                  }
                  echo xtc_draw_hidden_field('cmd', 'capture');

                  echo '<div class="refund_row">';
                  echo '<div class="'.(((10 - $count['captures']) > 0) ? 'info_message' : 'error_message').'">'.TEXT_PAYPAL_CAPTURE_LEFT . ' ' . (10 - $count['captures']).'</div>';
                  echo '<br/>';
                  echo '<label for="authorize_id">'.TEXT_PAYPAL_CAPTURE_AUTHORIZE.'</label>';
                  echo xtc_draw_pull_down_menu('authorize_id', $authorize_reason_array, '', 'id="refund_id"');
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

            if ((isset($status_array['captures']) && is_array($status_array['captures'])) 
                && (!isset($amount_array['refunds'])
                    || $admin_info_data->purchase_units[0]->amount->value > $amount_array['refunds']
                    )
                )
            {
              ?>
              <div class="pp_capture pp_box">
                <div class="pp_boxheading"><?php echo TEXT_PAYPAL_REFUND; ?></div>
                <?php 
                  echo xtc_draw_form('capture', xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action','subaction', 'ext', 'sec')).'action=custom&subaction=paypalaction', 'NONSSL'), 'post');
                  if (CSRF_TOKEN_SYSTEM == 'true' && isset($_SESSION['CSRFToken']) && isset($_SESSION['CSRFName'])) {
                    echo xtc_draw_hidden_field($_SESSION['CSRFName'], $_SESSION['CSRFToken']);
                  }
                  echo xtc_draw_hidden_field('cmd', 'refund');

                  echo '<div class="refund_row">';
                  echo '<div class="'.(((10 - $count['refunds']) > 0) ? 'info_message' : 'error_message').'">'.TEXT_PAYPAL_REFUND_LEFT . ' ' . (10 - $count['refunds']).'</div>';
                  echo '<br/>';
                  echo '<label for="refund_comment" style="vertical-align: top; margin-top: 5px;">'.TEXT_PAYPAL_REFUND_COMMENT.'</label>';
                  echo xtc_draw_textarea_field('refund_comment', '', '60', '8', '', 'id="refund_comment" maxlength="127"');
                  echo '<br/>';
                  echo '<label for="refund_id">'.TEXT_PAYPAL_REFUND_CAPTURE.'</label>';
                  echo xtc_draw_pull_down_menu('refund_id', $capture_reason_array, '', 'id="refund_id"');
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
          }

          $instructions_query = xtc_db_query("SELECT *
                                                FROM ".TABLE_PAYPAL_INSTRUCTIONS."
                                               WHERE orders_id = '".(int)$order->info['order_id']."'");
          if (xtc_db_num_rows($instructions_query)) {
            $instructions = xtc_db_fetch_array($instructions_query);
            ?>
            <div class="pp_transactions pp_box">
              <div class="pp_boxheading"><?php echo TEXT_PAYPAL_INSTRUCTIONS; ?></div>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_PAYPAL_INSTRUCTIONS_AMOUNT; ?></dt>
                <dd><?php echo xtc_format_price_order($instructions['amount'], 1, $instructions['currency'], 1); ?></dd>
              </dl>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_PAYPAL_INSTRUCTIONS_REFERENCE; ?></dt>
                <dd><?php echo $instructions['reference']; ?></dd>
              </dl>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_PAYPAL_INSTRUCTIONS_PAYDATE; ?></dt>
                <dd><?php echo xtc_date_short($instructions['date']); ?></dd>
              </dl>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_PAYPAL_INSTRUCTIONS_ACCOUNT; ?></dt>
                <dd><?php echo $instructions['name']; ?></dd>
              </dl>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_PAYPAL_INSTRUCTIONS_HOLDER; ?></dt>
                <dd><?php echo $instructions['holder']; ?></dd>
              </dl>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_PAYPAL_INSTRUCTIONS_IBAN; ?></dt>
                <dd><?php echo $instructions['iban']; ?></dd>
              </dl>
              <dl class="pp_transaction">
                <dt><?php echo TEXT_PAYPAL_INSTRUCTIONS_BIC; ?></dt>
                <dd><?php echo $instructions['bic']; ?></dd>
              </dl>
            </div>
            <?php
          }
          
          $tracker_array = array();
          $tracker_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_PAYPAL_TRACKING."
                                           WHERE orders_id = '".(int)$order->info['order_id']."'");
          if (xtc_db_num_rows($tracker_query)) {
            while ($tracker = xtc_db_fetch_array($tracker_query)) {
              if ($tracker['trackers_id'] == '') {
                $tracker['status'] = $paypal->getTrackingStatus($tracker['transaction_id'], $tracker['tracking_number']);
              } else {              
                if (isset($admin_info_data->purchase_units[0]->shipping->trackers)
                    && is_array($admin_info_data->purchase_units[0]->shipping->trackers)
                    )
                {
                  foreach ($admin_info_data->purchase_units[0]->shipping->trackers as $trackers) {
                                    
                    if ($trackers->id == $tracker['trackers_id']) {
                      $tracker['status'] = $trackers->status;
                    }
                  }
                }
              }
              
              $tracker_array[$tracker['tracking_number']] = $tracker;
            }
          }
          
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
          
          if (count($tracker_array) > 0 || count($tracking_array) > 0) {
            ?>
            <div class="pp_tracking pp_box">
              <?php                
                if (count($tracker_array) > 0) {
                  echo '<div class="pp_boxheading">'.TEXT_PAYPAL_TRACKING.'</div>';
                  foreach ($tracker_array as $tracker) {
                    echo xtc_draw_form('tracking', xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action','subaction', 'ext', 'sec')).'action=custom&subaction=paypalaction', 'NONSSL'), 'post');
                    if (CSRF_TOKEN_SYSTEM == 'true' && isset($_SESSION['CSRFToken']) && isset($_SESSION['CSRFName'])) {
                      echo xtc_draw_hidden_field($_SESSION['CSRFName'], $_SESSION['CSRFToken']);
                    }
                    if ($tracker['trackers_id'] == '') {
                      echo xtc_draw_hidden_field('cmd', 'canceltracking');
                    } else {
                      echo xtc_draw_hidden_field('cmd', 'patchtrack');
                    }
                    echo xtc_draw_hidden_field('tracking_id', $tracker['tracking_id']);
                    ?>
                    <div class="pp_txstatus">
                      <div class="pp_txstatus_received pp_received_icon">
                        <?php echo xtc_datetime_short($tracker['date_added']) . ' ' . $tracker['tracking_number']; ?>
                      </div>
                      <div class="pp_txstatus_data">
                        <?php if (isset($tracker['status'])) { ?>
                        <dl class="pp_txstatus_data_list">
                          <dt><?php echo TEXT_PAYPAL_TRACKING_STATE; ?></dt>
                          <dd><?php echo $tracker['status']; ?></dd>
                        </dl>
                        <?php } ?>
                        <dl class="pp_txstatus_data_list">
                          <dt><?php echo TEXT_PAYPAL_TRACKING_CARRIER; ?></dt>
                          <dd><?php echo $tracker['carrier']; ?></dd>
                        </dl>
                        <dl class="pp_txstatus_data_list">
                          <dt><?php echo TEXT_PAYPAL_TRACKING_ID; ?></dt>
                          <dd><?php echo (($tracker['trackers_id'] != '') ? $tracker['trackers_id'] : $tracker['transaction_id']); ?></dd>
                        </dl>
                        <dl class="pp_txstatus_data_list">
                          <dt><?php echo TEXT_PAYPAL_TRACKING_ACTION; ?></dt>
                          <dd><input type="submit" name="tracking_cancel" value="<?php echo TEXT_PAYPAL_TRACKING_CANCEL; ?>"></dd>
                        </dl>
                      </div>
                    </div>
                    </form>
                    <?php
                  }
                }
                
                if (count($tracking_array) > 0) {
                  if (count($tracker_array) > 0) {
                    echo '<br><br>';
                  }
                  echo '<div class="pp_boxheading">'.TEXT_PAYPAL_TRACKING_TRACE.'</div>';
                  echo xtc_draw_form('tracking', xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action','subaction', 'ext', 'sec')).'action=custom&subaction=paypalaction', 'NONSSL'), 'post');
                  if (CSRF_TOKEN_SYSTEM == 'true' && isset($_SESSION['CSRFToken']) && isset($_SESSION['CSRFName'])) {
                    echo xtc_draw_hidden_field($_SESSION['CSRFName'], $_SESSION['CSRFToken']);
                  }
                  echo xtc_draw_hidden_field('cmd', 'addtrack');
          
                  $i = 0;
                 foreach ($tracking_array as $tracking) {
                    echo '<div class="tracking_row">';
                    echo '<label for="track_'.$tracking['tracking_id'].'" style="width:100%">';
                    echo xtc_datetime_short($tracking['date_added']) . ' ' . $tracking['parcel_id'] . xtc_draw_radio_field('tracking', $tracking['tracking_id'], ($i == 0) , 'style="float:right;" id="track_'.$tracking['tracking_id'].'"');
                    echo '</label>';     
                    echo '</div>';               
            
                    $i++;
                  }
                ?>
                <br />
                <input type="submit" class="button" name="tracking_submit" value="<?php echo TEXT_PAYPAL_TRACKING_SUBMIT; ?>">
                </form>
                <?php
              }
            ?>
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

      $('#delete').submit(function() {
          return confirm("<?php echo decode_htmlentities(TEXT_PAYPAL_TRANSACTION_CONFIRM); ?>");
      });
    });
  </script>
  <?php
}
