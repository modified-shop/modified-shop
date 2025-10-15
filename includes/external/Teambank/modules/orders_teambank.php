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
    $orders_array = array(
      'easycredit',
      'easyinvoice',
    );
    
    if (in_array($order->info['payment_method'], $orders_array)) {
      require_once(DIR_FS_EXTERNAL.'Teambank/classes/TeambankPayment.php');
  
      $TeambankPayment = new TeambankPayment();
      $TeambankPayment->init($order->info['payment_method']);
      ?>
      <tr>
        <td colspan="2" style="width:990px;">
          <style type="text/css">
            p.message { margin:0px; padding: 1ex 1em; margin: 5px 1px; color: #A94442; border: 1px solid #DCA7A7; background-color: #F2DEDE; }
            .info_message { font-family: Verdana, Arial, sans-serif; border:solid #b2dba1 1px; padding:10px; font-size:12px !important; line-height:18px; background-color:#d4ebcb; color:#3C763D; }
            div.ec_box { background: #E2E2E2; float: left; padding: 1ex; margin: 1px; min-height: 125px; min-width:48.4%; width:48.4%; }
            .ec_box_full {width:98.3% !important;}
            div.ec_boxheading { font-size: 1.2em; font-weight: bold; background: #CCCCCC; padding: .2ex .5ex;}
            dl.ec_transaction { overflow: auto; margin: 0 0; border-bottom: 1px dotted #999; padding:2px 0px; }
            dl.ec_transaction dt, dl.ec_transaction dd { margin: 0; float: left; }
            dl.ec_transaction dt { clear: left; width: 12em; font-weight: bold; }
            div#teambank { position:relative; cursor: pointer; background: #ccc url(../includes/external/Teambank/css/arrow_down.png) no-repeat 4px 9px; padding:10px 0 10px 30px; }
            .teambank_logo {  position:absolute; top:4px; right:-20px; width:133px; height: 26px; background: transparent url(../includes/external/Teambank/css/logo_teambank.png) no-repeat 0px 0px;}
            .teambank_active { background: #bbb url(../includes/external/Teambank/css/arrow_up.png) no-repeat 4px 9px !important; }
            .teambank_data { font-family: Verdana; font-size:10px !important; }
            div.ec_txstatus {  }
            div.ec_txstatus_received { background: transparent url(../includes/external/Teambank/css/arrow_down_small.png) no-repeat 460px 3px; margin: 0 0; cursor: pointer;  border-bottom: 1px dotted #999; padding:2px 0px; line-height:14px; }
            div.ec_txstatus_open { background: #55b5df url(../includes/external/Teambank/css/arrow_up_small.png) no-repeat 460px 3px !important; font-weight: bold; }
            div.ec_txstatus_data { display: none; }
            dl.ec_txstatus_data_list { overflow: auto; margin:0 0; border-bottom: 1px dotted #ccc; padding:2px 2px; background:#fafafa; }
            dl.ec_txstatus_data_list dt, dl.ec_txstatus_data_list dd { margin: 0; float: left; max-width:270px; }
            dl.ec_txstatus_data_list dt { clear: left; width: 12em; font-weight: bold; }
            div.ec_capture form, div.ec_refund form { display: block; padding: 0.5ex; }
            div.refund_row { border-bottom: 1px dotted #999; padding:3px 0px; }
            div.ec_refund label, div.refund_row label { display: inline-block; width: 12em; }
            #refund_comment { width: 340px; resize: none; }
            div#ec { display:none; min-height: 44px; background: url(../includes/external/Teambank/css/processing.gif) no-repeat; background-position: center center; background-color: #E2E2E2; border-left: 2px solid #bbb; border-right: 2px solid #bbb; border-bottom: 2px solid #bbb;}
            div#ec_error { background: #bbb;padding: 3px; }
            div.ec_tracking .tracking_row { display:flex; align-items:center; border-bottom:1px dotted #999; padding: 3px 0px; }
            div.ec_tracking .tracking_row input { margin-right:5px; margin-top:1px; }
          </style>
          <table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
              <td width="120" class="dataTableHeadingContent" style="padding: 0px !important; border: 0px !important;">
                <div id="teambank"><?php echo TEXT_TEAMBANK_ORDERS_HEADING; ?><div class="teambank_logo"></div></div>
              </td>
            </tr>
          </table>
          <?php
            echo '<div id="ec"></div>';
            echo "<script type=\"text/javascript\">
                    function get_teambank_data() {
                      var order_id = ".$order->info['orders_id'].";
                      var lang = '".$_SESSION['language_code']."';
                      var secret = '".MODULE_PAYMENT_TEAMBANK_SECRET."';
                      $.get('../ajax.php', {ext: 'get_teambank_data', oID: order_id, language: lang, sec: secret}, function(data) {
                        if (data != '' && data != undefined) { 
                          $('#ec').html(decodeEntities(atob(data)));
                          $('.teambank_data').toggleClass('teambank_active');
                          $('.teambank_data').show();
                        }
                      });
                    }
                    function decodeEntities(encodedString) {
                      var textArea = document.createElement('textarea');
                      textArea.innerHTML = encodedString;
                      return textArea.value;
                    }
                  </script>";
          ?>
        </td>
      </tr>
      <script type="text/javascript">
        $(function() {
          $('div#teambank').click(function(e) {  
            $('#ec_error').hide();
            $('#ec').toggle();
            if ($('#ec').is(':empty')) {
              get_teambank_data();
            }
            $('div#teambank').toggleClass('teambank_active');
            $('.teambank_data').toggleClass('teambank_active');
            if ($('.teambank_data').hasClass('teambank_active')) {
              $('.teambank_data').show();
            } else {
              $('.teambank_data').hide();
            }
          });
        });
      </script>
    <?php
    }
  }
