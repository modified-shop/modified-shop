<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
 	 based on:
	  (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
	  (c) 2002-2003 osCommerce - www.oscommerce.com
	  (c) 2001-2003 TheMedia, Dipl.-Ing Thomas Plänkers - http://www.themedia.at & http://www.oscommerce.at
	  (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com
    (c) 2013 Gambio GmbH - http://www.gambio.de
  
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

function payone_get_order_details() {
  global $order;

  ?>
  <table cellspacing="0" cellpadding="2" class="table" style="width: 813px;">
    <tr class="dataTableHeadingRow">
      <td class="dataTableHeadingContent" colspan="2"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
      <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></td>
      <?php if ($order->products[0]['allow_tax'] == 1) { ?>
      <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRICE_INCLUDING_TAX; ?></td>
      <?php  } else { ?>
      <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRICE_EXCLUDING_TAX; ?></td>
      <?php } ?>
      <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_CHECK; ?></td>
    </tr>
    <?php
      for ($i = 0, $n = sizeof($order->products); $i < $n; $i ++) {
        echo '<tr class="dataTableRow">'.PHP_EOL;
        echo '  <td class="dataTableContent" valign="top" align="left" width="50px">'.xtc_draw_input_field('positions['.$i.'][qty]', $order->products[$i]['qty'], 'Style="width: 30px"').'&nbsp;x</td>'.PHP_EOL;
        echo '  <td class="dataTableContent" valign="top" align="left">'.$order->products[$i]['name'].PHP_EOL;
        if (isset($order->products[$i]['attributes']) && sizeof($order->products[$i]['attributes']) > 0) {
          for ($j = 0, $k = sizeof($order->products[$i]['attributes']); $j < $k; $j ++) {
            echo '<br /><nobr><i>&nbsp; - '.$order->products[$i]['attributes'][$j]['option'].': '.$order->products[$i]['attributes'][$j]['value'].'</i></nobr> ';
          }
        }
        echo '  </td>'.PHP_EOL;
        echo '  <td class="dataTableContent" valign="top">';
        echo ($order->products[$i]['model'] != '') ? $order->products[$i]['model'] : '<br />';
        // attribute models
        if (isset($order->products[$i]['attributes']) && sizeof($order->products[$i]['attributes']) > 0) {
          for ($j = 0, $k = sizeof($order->products[$i]['attributes']); $j < $k; $j ++) {
            $model = xtc_get_attributes_model($order->products[$i]['id'], $order->products[$i]['attributes'][$j]['value'],$order->products[$i]['attributes'][$j]['option'],$lang); //web28 Fix attribute model  language problem
            echo !empty($model) ? $model.'<br />' : '<br />';
          }
        }
        echo '&nbsp;</td>'.PHP_EOL;
        if ($order->products[$i]['allow_tax'] == 1) {
          echo '  <td class="dataTableContent" align="right" valign="top">'.format_price($order->products[$i]['price'], 1, $order->info['currency'], 0, 0).'</td>'.PHP_EOL;
        } else {
          echo '  <td class="dataTableContent" align="right" valign="top">'.format_price($order->products[$i]['price'], 1, $order->info['currency'], $order->products[$i]['allow_tax'], $order->products[$i]['tax']).'</td>'.PHP_EOL;            
        }
          echo '  <td class="dataTableContent" align="right" valign="top">'.xtc_draw_checkbox_field('positions['.$i.'][pid]', $order->products[$i]['opid']).'</td>'.PHP_EOL;
          echo '</tr>'.PHP_EOL;
      }
      for ($i = 0, $n = sizeof($order->totals); $i < $n; $i ++) {
        if (in_array($order->totals[$i]['class'], array('ot_shipping','ot_payment','ot_coupon', 'ot_discount', 'ot_gv', 'ot_ps_fee', 'ot_loworderfee', 'ot_cod_fee', 'ot_shippingfee'))) {
          echo '<tr>'.PHP_EOL;       
          echo '  <td>&nbsp;</td>' .PHP_EOL;            
          echo '  <td align="left" class="dataTableContent" colspan="2">'.$order->totals[$i]['title'].'</td>'.PHP_EOL;
          echo '  <td align="right" class="dataTableContent">'.$order->totals[$i]['text'].'</td>'.PHP_EOL;
          echo '  <td class="dataTableContent" align="right" valign="top">'.xtc_draw_checkbox_field('totals['.$i.'][class]', $order->totals[$i]['class']).'</td>'.PHP_EOL;
          echo '</tr>'.PHP_EOL;
        }
      }
  ?>
  </table>
  <?php
}

function payone_get_refund_countries() {
  global $payone;
  
  $refund_countries = array();
  $available_countries = array('DE', 'AT', 'NL', 'FR', 'CH');
  $sepa_countries = $payone->getSepaCountries();
  for ($i=0, $n=count($sepa_countries); $i<$n; $i++) {
    if (in_array($sepa_countries[$i]['countries_iso_code_2'], $available_countries)) {
      $refund_countries[] = array('id' => $sepa_countries[$i]['countries_iso_code_2'], 'text' => $sepa_countries[$i]['countries_name']);
    }
  }
  
  return $refund_countries;
}

$payone_payment_methods = array('payone', 
                                'payone_cc', 
                                'payone_otrans', 
                                'payone_installment', 
                                'payone_wlt', 
                                'payone_elv', 
                                'payone_prepay', 
                                'payone_cod', 
                                'payone_invoice');
                                
if (in_array($order->info['payment_method'], $payone_payment_methods)) {

  require_once (DIR_FS_EXTERNAL.'payone/lang/'.$order->info['language'].'.php');
  require_once (DIR_FS_EXTERNAL.'payone/classes/PayoneModified.php');
	$payone = new PayoneModified();

	if (!is_array($_SESSION['orders_payone_messages'])) {
		$_SESSION['orders_payone_messages'] = array();
	}

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		if ($_POST['cmd'] == 'capture') {
		  if (isset($_POST['positions'])) {
		    $_POST['capture']['positions'] = $_POST['positions'];
		  }
			$response = $payone->captureAmount($_POST['capture']);
			if ($response->getStatus() == 'ERROR') {
				$_SESSION['orders_payone_messages'][] = ERROR_OCCURED.": ".$response->getErrorcode().' '.$response->getErrormessage();
			} else {
				$_SESSION['orders_payone_messages'][] = AMOUNT_CAPTURED;
			}
		}
		if ($_POST['cmd'] == 'refund') {
		  if (isset($_POST['positions'])) {
		    $_POST['refund']['positions'] = $_POST['positions'];
		  }
			$response = $payone->refundAmount($_POST['refund']);
			if ($response->getStatus() == 'ERROR') {
				$_SESSION['orders_payone_messages'][] = ERROR_OCCURED.": ".$response->getErrorcode().' '.$response->getErrormessage();
			} else {
				$_SESSION['orders_payone_messages'][] = AMOUNT_REFUNDED;
			}
		}
	}

	$payone_messages = $_SESSION['orders_payone_messages'];
	$_SESSION['orders_payone_messages'] = array();

	$orders_data = $payone->getOrdersData((int)$_GET['oID']);
	$capture_data = $payone->getCaptureData((int)$_GET['oID']);

	?>
	<script type="text/javascript" src="includes/javascript/jquery-1.8.3.min.js"></script>
	<tr>
	  <td colspan="2" style="width:840px;">
      <style type="text/css">
        p.message { padding: 1ex 1em; margin: 5px 1px; border: 2px solid red; background-color: #ffa; }
        div.p1_box { background: #E2E2E2; float: left; padding: 1ex; margin: 1px; min-height: 125px; min-width:48.4%; width:48.4%; }
        .p1_box_full {width:98.3% !important;}
        div.p1_boxheading { font-size: 1.2em; font-weight: bold; background: #CCCCCC; padding: .2ex .5ex;}
        dl.p1_transaction { overflow: auto; margin: 0 0; border-bottom: 1px dotted #999; padding:2px 0px; }
        dl.p1_transaction dt, dl.p1_transaction dd { margin: 0; float: left; }
        dl.p1_transaction dt { clear: left; width: 12em; font-weight: bold; }
        div#payone { position:relative; cursor: pointer; background: #ccc url(../includes/external/payone/css/arrow_down.png) no-repeat 4px 7px; padding:8px 0 8px 30px; }
        .payone_logo {  position:absolute; top:8px; right:8px; width:133px; height: 15px; background: transparent url(../includes/external/payone/css/logo_payone.png) no-repeat 0px 0px;}
        .payone_active { background: #bbb url(../includes/external/payone/css/arrow_up.png) no-repeat 4px 7px !important; }
        .payone_data { font-family: Verdana; font-size:10px !important; }
        div.p1_txstatus {  }
        div.p1_txstatus_received { background: transparent url(../includes/external/payone/css/arrow_down_small.png) no-repeat 380px 3px; margin: 0 0; cursor: pointer;  border-bottom: 1px dotted #999; padding:2px 0px; line-height:14px; }
        div.p1_txstatus_open { background: #55b5df url(../includes/external/payone/css/arrow_up_small.png) no-repeat 380px 3px !important; font-weight: bold; }
        div.p1_txstatus_data { display: none; }
        dl.p1_txstatus_data_list { overflow: auto; margin:0 0; border-bottom: 1px dotted #ccc; padding:2px 2px; background:#fafafa; }
        dl.p1_txstatus_data_list dt, dl.p1_txstatus_data_list dd { margin: 0; float: left; max-width:270px; }
        dl.p1_txstatus_data_list dt { clear: left; width: 12em; font-weight: bold; }
        div.p1_capture form, div.p1_refund form { display: block; padding: 0.5ex; }
        div.refund_row { border-bottom: 1px dotted #999; padding:3px 0px; }
        div.p1_refund label, div.refund_row label { display: inline-block; width: 65px; }
      </style>
      <table border="0" width="100%" cellspacing="0" cellpadding="0">
        <tr>
          <td width="120" class="dataTableHeadingContent" style="padding: 0px !important; border: 0px !important;">
            <div id="payone"><?php echo PAYONE_ORDERS_HEADING; ?><div class="payone_logo"></div></div>
          </td>
        </tr>
      </table>
      <table border="0" width="100%" cellspacing="0" cellpadding="2" class="dataTableRow payone_data" style="display:none;">
        <tr>
          <td width="100%" valign="top">
            <?php
            if (!empty($payone_messages)) {
              foreach($payone_messages as $msg) {
                echo '<p class="message">'.$msg.'</p>';
              }
            } 
            ?>
            
            <div class="p1_transactions p1_box">
              <div class="p1_boxheading"><?php echo TRANSACTIONS; ?></div>
              <?php 
                unset($orders_data['transactions'][0]['payone_transactions_id']);
                unset($orders_data['transactions'][0]['orders_id']);
              
                foreach($orders_data['transactions'][0] as $tx_key => $tx_value) {
                  echo '<dl class="p1_transaction">';
                  echo '  <dt>'.constant(strtoupper($tx_key)).'</dt>';
                  echo '  <dd>'.$tx_value.'</dd>';
                  echo '</dl>';
                }
              ?>
            </div>

            <div class="p1_txstatus p1_box">
              <div class="p1_boxheading"><?php echo TRANSACTION_STATUS; ?></div>
              <?php 
                if (empty($orders_data['transaction_status'])) {
                  echo '<p>'.NO_TRANSACTION_STATUS_RECEIVED.'</p>';
                } else {
                  foreach ($orders_data['transaction_status'] as $txstatus) {
                  ?>
                    <div class="p1_txstatus">
                      <div class="p1_txstatus_received p1_received_icon">
                        <?php echo $txstatus['received'] . (($txstatus['data']['txaction'] != '') ? ' ('.$txstatus['data']['txaction'].')' : ''); ?>
                      </div>
                      <div class="p1_txstatus_data">
                          <?php 
                          foreach($txstatus['data'] as $key => $value) {
                            if (strpos($value, '||') !== false) {
                              $exploded = explode('||', $value);
                              for ($i=0, $n=count($exploded); $i<$n; $i++) {
                                echo '<dl class="p1_txstatus_data_list">';
                                echo '<dt>'.$key.'</dt>';
                                echo '<dd>'.$exploded[$i].'</dd>';
                                echo '</dl>';
                              }
                            } else {
                              echo '<dl class="p1_txstatus_data_list">';
                              echo '<dt>'.$key.'</dt>';
                              echo '<dd>'.$value.'</dd>';
                              echo '</dl>';
                            }
                          } 
                          ?>
                      </div>
                    </div>
                  <?php 
                  }
                } 
              ?>
            </div>
            <div style="clear:both;"></div>
        
            <?php 
            if ($capture_data !== false) { 
              ?>
              <div class="p1_capture p1_box <?php echo (($order->info['payment_method'] == 'payone_installment') ? 'p1_box_full' : ''); ?>">
                <div class="p1_boxheading"><?php echo CAPTURE_TRANSACTION; ?></div>
                <?php 
                  echo xtc_draw_form('capture', FILENAME_ORDERS, xtc_get_all_get_params()); 
                  echo xtc_draw_hidden_field('cmd', 'capture').
                       xtc_draw_hidden_field('capture[oID]', (int)$oID).
                       xtc_draw_hidden_field('capture[txid]', $capture_data['txid']).
                       xtc_draw_hidden_field('capture[portalid]', $capture_data['portalid']).
                       xtc_draw_hidden_field('capture[currency]', $capture_data['currency']);
                  if ($order->info['payment_method'] == 'payone_installment') {
                    payone_get_order_details();
                  } else { 
                    echo '<div class="refund_row">';
                    echo '<label for="amount">'.CAPTURE_AMOUNT.'</label>';
                    echo xtc_draw_input_field('capture[amount]', $capture_data['price'], 'id="amount" style="width: 135px"');
                    echo ' ' . $capture_data['currency'];              
                    echo '</div>';
                  } 
                  ?>
                  <br />
                  <input type="submit" class="button" name="capture_submit" value="<?php echo CAPTURE_SUBMIT; ?>">
                </form>
              </div>
              <?php 
            } 
            ?>

            <?php 
            if ($capture_data !== false) { 
              ?>
              <div class="p1_refund p1_box <?php echo (($order->info['payment_method'] == 'payone_installment') ? 'p1_box_full' : ''); ?>">
                <div class="p1_boxheading"><?php echo REFUND_TRANSACTION; ?></div>
                <?php 
                  echo xtc_draw_form('refund', FILENAME_ORDERS, xtc_get_all_get_params());
                  echo xtc_draw_hidden_field('cmd', 'refund').
                       xtc_draw_hidden_field('refund[oID]', (int)$oID).
                       xtc_draw_hidden_field('refund[txid]', $capture_data['txid']).
                       xtc_draw_hidden_field('refund[portalid]', $capture_data['portalid']).
                       xtc_draw_hidden_field('refund[currency]', $capture_data['currency']);
                  if (in_array($order->info['payment_method'], array('payone_invoice', 'payone_prepay', 'payone_cod'))) { 
                  ?>
                    <div class="refund_row">
                      <label for="p1_refund_amount"><?php echo REFUND_AMOUNT; ?></label>
                      <?php echo xtc_draw_input_field('refund[amount]', $capture_data['price'], 'id="amount" style="width: 135px"'); ?>
                      <?php echo $capture_data['currency'] ?>
                    </div>
                    <div class="refund_row">
                      <label for="bankcountry"><?php echo REFUND_BANKCOUNTRY; ?></label>
                      <?php echo xtc_draw_pull_down_menu('refund[bankcountry]', payone_get_refund_countries(), 'DE', 'id="bankcountry" style="width: 139px"'); ?>
                    </div>
                    <div class="refund_row">
                      <label for="bankaccount"><?php echo REFUND_BANKACCOUNT; ?></label>
                      <?php echo xtc_draw_input_field('refund[bankaccount]', '', 'id="bankaccount" style="width: 135px"'); ?>
                    </div>
                    <div class="refund_row">
                      <label for="bankcode"><?php echo REFUND_BANKCODE; ?></label>
                      <?php echo xtc_draw_input_field('refund[bankcode]', '', 'id="bankcode" style="width: 135px"'); ?>
                    </div>
                    <div class="refund_row">
                      <label for="bankbranchcode"><?php echo REFUND_BANKBRANCHCODE; ?></label>
                      <?php echo xtc_draw_input_field('refund[bankbranchcode]', '', 'id="bankbranchcode" style="width: 135px"'); ?>
                    </div>
                    <div class="refund_row">
                      <label for="bankcheckdigit"><?php echo REFUND_BANKCHECKDIGIT; ?></label>
                      <?php echo xtc_draw_input_field('refund[bankcheckdigit]', '', 'id="bankcheckdigit" style="width: 135px"'); ?>
                    </div>
                    <div class="refund_row">
                      <label for="iban"><?php echo REFUND_IBAN; ?></label>
                      <?php echo xtc_draw_input_field('refund[iban]', '', 'id="iban" style="width: 135px"'); ?>
                    </div>
                    <div class="refund_row">
                      <label for="bic"><?php echo REFUND_BIC; ?></label>
                      <?php echo xtc_draw_input_field('refund[bic]', '', 'id="bic" style="width: 135px"'); ?>
                    </div>
                  <?php 
                  } elseif ($order->info['payment_method'] == 'payone_installment') {
                    payone_get_order_details();
                  } else { 
                    echo '<div class="refund_row">';
                    echo '<label for="amount">'.CAPTURE_AMOUNT.'</label>';
                    echo xtc_draw_input_field('refund[amount]', $capture_data['price'], 'id="amount" style="width: 135px"');
                    echo ' ' . $capture_data['currency'];              
                    echo '</div>';
                  }
                  ?>
                  <br />
                  <input type="submit" class="button" name="refund_submit" value="<?php echo REFUND_SUBMIT; ?>">
                </form>
              </div>
            <?php } ?>
          </td>
        </tr>
      </table>
	  </td>
	</tr>
	
  <script type="text/javascript">
    $(function() {
      $('div#payone').click(function(e) {
        $('div#payone').toggleClass('payone_active');
        $('.payone_data').toggleClass('payone_active');
        if ($('.payone_data').hasClass('payone_active')) {
          $('.payone_data').show();
        } else {
          $('.payone_data').hide();
        }
      });
    
      $('div.p1_txstatus_received').not('.p1_txstatus_open').click(function(e) {
        if ($(this).hasClass('p1_txstatus_open')) {
          $('div.p1_txstatus_received').removeClass('p1_txstatus_open');
          $('div.p1_txstatus_data', $(this).parent()).hide();
        } else {
          $('div.p1_txstatus_received').removeClass('p1_txstatus_open');
          $(this).addClass('p1_txstatus_open');
          $('div.p1_txstatus_data').hide();
          $('div.p1_txstatus_data', $(this).parent()).show();
        }
      });
    });
	</script>
		
<?php
}
?>