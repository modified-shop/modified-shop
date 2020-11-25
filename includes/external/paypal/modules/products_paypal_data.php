<?php
/* -----------------------------------------------------------------------------------------
   $Id: orders_paypal_data.php 12576 2020-02-20 17:14:51Z GTB $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

if (isset($_GET['pID']) && $_GET['pID'] != '') {

    require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalInfo.php');
    $paypal = new PayPalInfo('subscription');
    
    $products_info_array = $paypal->get_product($_GET['pID']);
  
    $product_type_array = array(
      array('id' => 'PHYSICAL', 'text' => 'PHYSICAL'),
      array('id' => 'DIGITAL', 'text' => 'DIGITAL'),
      array('id' => 'SERVICE', 'text' => 'SERVICE'),
    );

    $plan_status_array = array(
      array('id' => 'ACTIVE', 'text' => TEXT_ACTIVE),
      array('id' => 'INACTIVE', 'text' => TEXT_INACTIVE),
    );

    $plan_interval_array = array(
      array('id' => 'DAY', 'text' => TEXT_PAYPAL_PLAN_DAY),
      array('id' => 'WEEK', 'text' => TEXT_PAYPAL_PLAN_WEEK),
      array('id' => 'MONTH', 'text' => TEXT_PAYPAL_PLAN_MONTH),
      array('id' => 'YEAR', 'text' => TEXT_PAYPAL_PLAN_YEAR),
    );
    
    $plan_cycle_array = array();
    for($i=0; $i<=999; $i++) {
      $plan_cycle_array[] = array('id' => $i, 'text' => (($i == 0) ? TEXT_PAYPAL_PLAN_CYCLE_NO_LIMIT : $i));
    }
    
    $plan_tax_array = array (array ('id' => '0', 'text' => TEXT_NONE));
    $tax_class_query = xtc_db_query("SELECT tax_class_id, tax_class_title FROM ".TABLE_TAX_CLASS." ORDER BY tax_class_title");
    while ($tax_class = xtc_db_fetch_array($tax_class_query)) {
      $plan_tax_array[] = array ('id' => number_format(xtc_get_tax_rate($tax_class['tax_class_id']), 2), 'text' => $tax_class['tax_class_title']);
    }

    $plan_tax_include_array = array(
      array('id' => 1, 'text' => TEXT_YES),
      array('id' => 0, 'text' => TEXT_NO),
    );
    
    if (count($products_info_array) > 0) {
    ?>
      <div class="pp-overlay" style="display:none"></div>
      <table border="0" width="100%" cellspacing="0" cellpadding="2" class="dataTableRow paypal_data" style="display:none;">
        <tr>
          <td width="100%" valign="top">
            <div class="pp_txstatus pp_box">
            <div class="pp_boxheading"><?php echo TEXT_PAYPAL_PLANS; ?></div>
            <?php 
            $cnt = 0;     
            $plans = $paypal->get_all_plans($_GET['pID']);
            if (count($plans->getPlans()) > 0) {
              foreach ($plans->getPlans() as $plan) {
                $detail = $paypal->get_plan_details($plan->getId());
                $billing_cycles = $detail->getBillingCycles();
                $frequency = $billing_cycles[0]->getFrequency();
                $fixed_price = $billing_cycles[0]->GetPricingScheme()->getFixedPrice();          
                $setup_fee = $detail->getPaymentPreferences()->getSetupFee();
                $taxes = $detail->getTaxes();                

                $check_query = xtc_db_query("SELECT * 
                                               FROM `paypal_plan` 
                                              WHERE plan_id = '".xtc_db_input($plan->getId())."'");
                if (xtc_db_num_rows($check_query) < 1) {
                  $sql_data_array = array(
                    'plan_id' => $plan->getId(),
                    'products_id' => (int)$_GET['pID'],
                    'plan_status' => (($plan->getStatus() == 'ACTIVE') ? 1 : 0),
                    'plan_name' => $plan->getName(),
                    'plan_interval' => $frequency->getIntervalUnit(),
                    'plan_cycle' => $billing_cycles[0]->getTotalCycles(),
                    'plan_price' => $fixed_price->getValue(),
                    'plan_fee' => $setup_fee->getValue(),
                    'plan_tax' => $taxes->getPercentage(),
                    'plan_tax_included' => $taxes->getInclusive(),
                  );

                  xtc_db_perform('paypal_plan', $sql_data_array);
                }
                ?>
                <div class="pp_txstatus">
                  <div class="pp_txstatus_received pp_received_icon">
                    <?php echo $plan->getName().xtc_draw_hidden_field('paypal_plan_id', $plan->getId(), 'data-id="pp-'.$cnt.'"'); ?>
                  </div>
                  <div class="pp_txstatus_data">
                    <table class="tableInput border0">
                      <tr>
                        <td style="width:250px"><span class="main"><?php echo TEXT_PAYPAL_PLAN_STATUS; ?></span></td>
                        <td><span class="main"><?php echo xtc_draw_pull_down_menu('paypal_plan_status', $plan_status_array, $detail->getStatus(), 'data-id="pp-'.$cnt.'" style="width: 155px"').xtc_draw_hidden_field('paypal_plan_status_old', $detail->getStatus(), 'data-id="pp-'.$cnt.'"'); ?></span></td>
                      </tr>
                      <tr>
                        <td><span class="main"><?php echo TEXT_PAYPAL_PLAN_NAME; ?></span></td>
                        <td><span class="main"><?php echo $detail->getName(); ?></span></td>
                      </tr>
                      <tr>
                        <td><span class="main"><?php echo TEXT_PAYPAL_PLAN_INTERVAL; ?></span></td>
                        <td><span class="main"><?php echo constant('TEXT_PAYPAL_PLAN_'.strtoupper($frequency->getIntervalUnit())); ?></span></td>
                      </tr>
                      <tr>
                        <td><span class="main"><?php echo TEXT_PAYPAL_PLAN_CYCLE; ?></span></td>
                        <td><span class="main"><?php echo $billing_cycles[0]->getTotalCycles() == 0 ? TEXT_PAYPAL_PLAN_CYCLE_NO_LIMIT : $billing_cycles[0]->getTotalCycles(); ?></span></td>
                      </tr>
                      <tr>
                        <td><span class="main"><?php echo TEXT_PAYPAL_PLAN_FIXED_PRICE; ?></span></td>
                        <td><span class="main"><?php echo xtc_draw_input_field('paypal_plan_fixed_price', $fixed_price->getValue(), 'data-id="pp-'.$cnt.'" style="width: 155px"').xtc_draw_hidden_field('paypal_plan_fixed_price_old', $fixed_price->getValue(), 'data-id="pp-'.$cnt.'"').DEFAULT_CURRENCY; ?></span></td>
                      </tr>
                      <tr>
                        <td><span class="main"><?php echo TEXT_PAYPAL_PLAN_SETUP_FEE; ?></span></td>
                        <td><span class="main"><?php echo xtc_draw_input_field('paypal_plan_setup_fee', $setup_fee->getValue(), 'data-id="pp-'.$cnt.'" style="width: 155px"').DEFAULT_CURRENCY; ?></span></td>
                      </tr>
                      <tr>
                        <td><span class="main"><?php echo TEXT_PAYPAL_PLAN_TAX_CLASS; ?></span></td>
                        <td><span class="main"><?php echo xtc_draw_pull_down_menu('paypal_plan_tax', $plan_tax_array, $taxes->getPercentage(), 'data-id="pp-'.$cnt.'" style="width: 155px"'); ?></span></td>
                      </tr>
                      <tr>
                        <td><span class="main"><?php echo TEXT_PAYPAL_PLAN_TAX_INCLUDE; ?></span></td>
                        <td><span class="main"><?php echo (($taxes->getInclusive() == 1) ? TEXT_YES : TEXT_NO); ?></span></td>
                      </tr>
                    </table>
                    <div class="main" style="margin:10px 5px;float:right;">
                      <input type="button" class="button paypal_plan" name="patch_plan" data-action="patch_plan" data-id="pp-<?php echo $cnt; ?>" value="<?php echo TEXT_PAYPAL_PLAN_PATCH; ?>">
                    </div>
                    <div style="clear:both;"></div>
                  </div>
                </div>
                <?php
                $cnt ++;
              }
            }
            ?>          
            <div class="pp_txstatus">
              <div class="pp_txstatus_received pp_received_icon">
                <?php echo TEXT_PAYPAL_NEW_PLAN; ?>
              </div>
              <div class="pp_txstatus_data">
                <table class="tableInput border0">
                  <tr>
                    <td style="width:250px"><span class="main"><?php echo TEXT_PAYPAL_PLAN_STATUS; ?></span></td>
                    <td><span class="main"><?php echo xtc_draw_pull_down_menu('paypal_plan_status', $plan_status_array, 'ACTIVE', 'data-id="pp-'.$cnt.'" style="width: 155px"'); ?></span></td>
                  </tr>
                  <tr>
                    <td><span class="main"><?php echo TEXT_PAYPAL_PLAN_NAME; ?></span></td>
                    <td><span class="main"><?php echo xtc_draw_input_field('paypal_plan_name', '', 'data-id="pp-'.$cnt.'" style="width: 155px"'); ?></span><span class="tooltip"><?php echo xtc_image(DIR_WS_ICONS.'tooltip_icon.png'); ?><em><?php echo TEXT_PAYPAL_PLAN_DAY_NAME_INFO; ?></em></span></td>
                  </tr>
                  <tr>
                    <td><span class="main"><?php echo TEXT_PAYPAL_PLAN_INTERVAL; ?></span></td>
                    <td><span class="main"><?php echo xtc_draw_pull_down_menu('paypal_plan_interval', $plan_interval_array, '', 'data-id="pp-'.$cnt.'" style="width: 155px"'); ?></span><span class="tooltip"><?php echo xtc_image(DIR_WS_ICONS.'tooltip_icon.png'); ?><em><?php echo TEXT_PAYPAL_NO_CHANGE; ?></em></span></td>
                  </tr>
                  <tr>
                    <td><span class="main"><?php echo TEXT_PAYPAL_PLAN_CYCLE; ?></span></td>
                    <td><span class="main"><?php echo xtc_draw_pull_down_menu('paypal_plan_cycle', $plan_cycle_array, '', 'data-id="pp-'.$cnt.'" style="width: 155px"'); ?></span><span class="tooltip"><?php echo xtc_image(DIR_WS_ICONS.'tooltip_icon.png'); ?><em><?php echo TEXT_PAYPAL_NO_CHANGE; ?></em></span></td>
                  </tr>
                  <tr>
                    <td><span class="main"><?php echo TEXT_PAYPAL_PLAN_FIXED_PRICE; ?></span></td>
                    <td><span class="main"><?php echo xtc_draw_input_field('paypal_plan_fixed_price', '', 'data-id="pp-'.$cnt.'" style="width: 155px"').DEFAULT_CURRENCY; ?></span></td>
                  </tr>
                  <tr>
                    <td><span class="main"><?php echo TEXT_PAYPAL_PLAN_SETUP_FEE; ?></span></td>
                    <td><span class="main"><?php echo xtc_draw_input_field('paypal_plan_setup_fee', '', 'data-id="pp-'.$cnt.'" style="width: 155px"').DEFAULT_CURRENCY; ?></span></td>
                  </tr>
                  <tr>
                    <td><span class="main"><?php echo TEXT_PAYPAL_PLAN_TAX_CLASS; ?></span></td>
                    <td><span class="main"><?php echo xtc_draw_pull_down_menu('paypal_plan_tax', $plan_tax_array, '', 'data-id="pp-'.$cnt.'" style="width: 155px"'); ?></span></td>
                  </tr>
                  <tr>
                    <td><span class="main"><?php echo TEXT_PAYPAL_PLAN_TAX_INCLUDE; ?></span></td>
                    <td><span class="main"><?php echo xtc_draw_pull_down_menu('paypal_plan_tax_include', $plan_tax_include_array, '1', 'data-id="pp-'.$cnt.'" style="width: 155px"'); ?></span></td>
                  </tr>
                </table>
                <div class="main" style="margin:10px 5px;float:right;">
                  <input type="button" class="button paypal_plan" name="create_product" data-action="create_plan" data-id="pp-<?php echo $cnt; ?>" value="<?php echo TEXT_PAYPAL_PLAN_SAVE; ?>">
                </div>
                <div style="clear:both;"></div>
              </div> 
            </div>
            </div>       
          </td>
        </tr>
      </table>  
      <?php
    } else {
    ?>
      <div class="pp-overlay" style="display:none"></div>
      <table border="0" width="100%" cellspacing="0" cellpadding="2" class="dataTableRow paypal_data" style="display:none;">
        <tr>
          <td width="100%" valign="top">      
            <div style="background:#f3f3f3;">
              <table class="tableInput border0">
                <tr>
                  <td style="width:250px"><span class="main"><?php echo TEXT_PAYPAL_PRODUCTS_TYPE; ?></span></td>
                  <td><span class="main"><?php echo xtc_draw_pull_down_menu('paypal_products_type', $product_type_array, '', 'id="paypal_products_type" style="width: 155px"'); ?></span></td>
                </tr>
              </table>
              <div class="main" style="margin:10px 5px;float:right;">
                <input type="button" class="button" id="create_product" name="create_product" value="<?php echo TEXT_PAYPAL_CREATE_PRODUCT; ?>">
              </div>
              <div style="clear:both;"></div>
            </div>        
          </td>
        </tr>
      </table>
    <?php
    }
  ?>
  <script type="text/javascript">
    var ajaxcall = false;

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
    
    $('body').on('click', '#create_product', function() {
      $('.pp-overlay').show();

      var action = $(this).attr('id');
      var type = $('#paypal_products_type').val();
      var params = {};
      params['products_type'] = type;
      
      if (ajaxcall == false) {
        paypal_ajax_call(action, params);
      }
    });

    $('body').on('click', '.paypal_plan', function() {
      $('.pp-overlay').show();
      
      var action = $(this).data('action');
      var ppid = $(this).data('id');
      
      var params = {};
      $("[name^=paypal_]").each(function(key, value) {
        if (ppid == $(this).data('id')) {
          params[$(this).attr('name').toString()] = $(this).val();
        }
      });
      
      if (ajaxcall == false) {
        paypal_ajax_call(action, params);
      }
    });
        
    function paypal_ajax_call(action, params) {
      var products_id = "<?php echo (int)$_GET['pID']; ?>";
      var lang = "<?php echo $_SESSION['language_code']; ?>";
      var secret = "<?php echo MODULE_PAYMENT_PAYPAL_SECRET; ?>";
      ajaxcall = true;
        
      $.ajax({
        dataType: 'json',
        type: 'post',
        url: '../ajax.php?ext=get_paypal_products&action='+action+'&pID='+products_id+'&language='+lang+'&sec='+secret,
        data: params,
        cache: false,
        async: true,
        success: function(data) {
          if (data != '' && data != undefined) { 
            $('#pp').html(decodeEntities(atob(data)));
            $('.paypal_data').toggleClass('paypal_active');
            $('.paypal_data').show();
            ajaxcall = false;
          }
          $('.pp-overlay').hide();
        }
      });
    }
  </script>
<?php
}
?>