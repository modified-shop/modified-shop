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
      ) 
  {
    require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalInfo.php');
    $paypal = new PayPalInfo($order->info['payment_method']);
        
    // payment
    $admin_info_array = $paypal->order_info($order->info['order_id']);
    
    ?>
    <tr>
      <td colspan="2" style="width:990px;">
        <style type="text/css">
          p.message { padding: 1ex 1em; margin: 5px 1px; color: #A94442; border: 1px solid #DCA7A7; background-color: #F2DEDE; }
          .info_message { font-family: Verdana, Arial, sans-serif; border:solid #b2dba1 1px; padding:10px; font-size:12px !important; line-height:18px; background-color:#d4ebcb; color:#3C763D; }
          div.pp_box { background: #E2E2E2; float: left; padding: 1ex; margin: 1px; min-height: 125px; min-width:48.4%; width:48.4%; }
          .pp_box_full {width:98.3% !important;}
          div.pp_boxheading { font-size: 1.2em; font-weight: bold; background: #CCCCCC; padding: .2ex .5ex;}
          dl.pp_transaction { overflow: auto; margin: 0 0; border-bottom: 1px dotted #999; padding:2px 0px; }
          dl.pp_transaction dt, dl.pp_transaction dd { margin: 0; float: left; }
          dl.pp_transaction dt { clear: left; width: 12em; font-weight: bold; }
          div#paypal { position:relative; cursor: pointer; background: #ccc url(../includes/external/paypal/css/arrow_down.png) no-repeat 4px 9px; padding:10px 0 10px 30px; }
          .paypal_logo {  position:absolute; top:4px; right:-25px; width:133px; height: 26px; background: transparent url(../includes/external/paypal/css/logo_paypal.png) no-repeat 0px 0px;}
          .paypal_active { background: #bbb url(../includes/external/paypal/css/arrow_up.png) no-repeat 4px 9px !important; }
          .paypal_data { font-family: Verdana; font-size:10px !important; }
          div.pp_txstatus {  }
          div.pp_txstatus_received { background: transparent url(../includes/external/paypal/css/arrow_down_small.png) no-repeat 460px 3px; margin: 0 0; cursor: pointer;  border-bottom: 1px dotted #999; padding:2px 0px; line-height:14px; }
          div.pp_txstatus_open { background: #55b5df url(../includes/external/paypal/css/arrow_up_small.png) no-repeat 460px 3px !important; font-weight: bold; }
          div.pp_txstatus_data { display: none; }
          dl.pp_txstatus_data_list { overflow: auto; margin:0 0; border-bottom: 1px dotted #ccc; padding:2px 2px; background:#fafafa; }
          dl.pp_txstatus_data_list dt, dl.pp_txstatus_data_list dd { margin: 0; float: left; max-width:270px; }
          dl.pp_txstatus_data_list dt { clear: left; width: 12em; font-weight: bold; }
          div.pp_capture form, div.pp_refund form { display: block; padding: 0.5ex; }
          div.refund_row { border-bottom: 1px dotted #999; padding:3px 0px; }
          div.pp_refund label, div.refund_row label { display: inline-block; width: 12em; }
          #refund_comment { width: 340px; resize: none; }
        </style>
        <table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td width="120" class="dataTableHeadingContent" style="padding: 0px !important; border: 0px !important;">
              <div id="paypal"><?php echo TEXT_PAYPAL_ORDERS_HEADING; ?><div class="paypal_logo"></div></div>
            </td>
          </tr>
        </table>
        <?php
        
        if (count($admin_info_array) > 0) {
          ?>          
          <table border="0" width="100%" cellspacing="0" cellpadding="2" class="dataTableRow paypal_data" style="display:none;">
            <tr>
              <td width="100%" valign="top">
                <?php
                if ($admin_info_array['message'] != '' || $_SERVER['REQUEST_METHOD'] == 'POST') {
                  if ($admin_info_array['message'] != '') {
                    echo '<p class="message">'.$admin_info_array['message'].'</p>';
                  }
                  ?>
                  <script type="text/javascript">
                    $('div#paypal').toggleClass('paypal_active');
                    $('.paypal_data').toggleClass('paypal_active');
                    $('.paypal_data').show();
                  </script>
                  <?php
                } 
                ?>

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
                    <dd><?php echo format_price($admin_info_array['total'], 1, $admin_info_array['transactions'][0]['relatedResource'][0]['currency'], 0, 0); ?></dd>
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
                            <dd><?php echo format_price($admin_info_array['transactions'][$t]['relatedResource'][$i]['total'], 1, $admin_info_array['transactions'][$t]['relatedResource'][$i]['currency'], 0, 0); ?></dd>
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
                      echo xtc_draw_form('capture', FILENAME_ORDERS, xtc_get_all_get_params(array('action','subaction')).'action=custom&subaction=paypalaction');
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
                      echo xtc_draw_form('capture', FILENAME_ORDERS, xtc_get_all_get_params(array('action','subaction')).'action=custom&subaction=paypalaction');
                      echo xtc_draw_hidden_field('cmd', 'refund');

                      echo '<div class="refund_row">';
                      echo '<div class="'.(((10 - $count['refund']) > 0) ? 'info_message' : 'error_message').'">'.TEXT_PAYPAL_REFUND_LEFT . ' ' . (10 - $count['refund']).'</div>';
                      echo '<br/>';
                      echo '<label for="refund_comment" style="vertical-align: top; margin-top: 5px;">'.TEXT_PAYPAL_REFUND_COMMENT.'</label>';
                      echo xtc_draw_textarea_field('refund_comment', '', '60', '8', '', 'id="refund_comment"');
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
      ?>
      </td>
    </tr>
    <script type="text/javascript">
      $(function() {
        $('div#paypal').click(function(e) {
          $('div#paypal').toggleClass('paypal_active');
          $('.paypal_data').toggleClass('paypal_active');
          if ($('.paypal_data').hasClass('paypal_active')) {
            $('.paypal_data').show();
          } else {
            $('.paypal_data').hide();
          }
        });

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