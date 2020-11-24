<?php
/* -----------------------------------------------------------------------------------------
   $Id: orders_paypal.php 12576 2020-02-20 17:14:51Z GTB $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

if (isset($_GET['pID']) && $_GET['pID'] != '') {
  if (defined('MODULE_PAYMENT_PAYPALSUBSCRIPTION_STATUS')
      && MODULE_PAYMENT_PAYPALSUBSCRIPTION_STATUS == 'True'
      ) 
  {
    require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalInfo.php');
    $paypal = new PayPalInfo('subscriptions');        
    ?>
    <div style="padding:5px;">
      <style type="text/css">
        p.message { margin:0px; padding: 1ex 1em; margin: 5px 1px; color: #A94442; border: 1px solid #DCA7A7; background-color: #F2DEDE; }
        .info_message { font-family: Verdana, Arial, sans-serif; border:solid #b2dba1 1px; padding:10px; font-size:12px !important; line-height:18px; background-color:#d4ebcb; color:#3C763D; }
        div.pp_box { background: #E2E2E2; float: left; padding: 1ex; margin: 1px; min-height: 125px; width: 98.6%; }
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
        div.pp_txstatus_received { background: transparent url(../includes/external/paypal/css/arrow_down_small.png) no-repeat 955px 3px; margin: 0 0; cursor: pointer;  border-bottom: 1px dotted #999; padding:2px 0px; line-height:14px; }
        div.pp_txstatus_open { background: #55b5df url(../includes/external/paypal/css/arrow_up_small.png) no-repeat 955px 3px !important; font-weight: bold; }
        div.pp_txstatus_data { display: none; }
        dl.pp_txstatus_data_list { overflow: auto; margin:0 0; border-bottom: 1px dotted #ccc; padding:2px 2px; background:#fafafa; }
        dl.pp_txstatus_data_list dt, dl.pp_txstatus_data_list dd { margin: 0; float: left; max-width:270px; }
        dl.pp_txstatus_data_list dt { clear: left; width: 12em; font-weight: bold; }
        div.pp_capture form, div.pp_refund form { display: block; padding: 0.5ex; }
        div.refund_row { border-bottom: 1px dotted #999; padding:3px 0px; }
        div.pp_refund label, div.refund_row label { display: inline-block; width: 12em; }
        #refund_comment { width: 340px; resize: none; }
        div#pp { display:none; min-height: 44px; background: url(../includes/external/paypal/css/processing.gif) no-repeat; background-position: center center; background-color: #E2E2E2; border-left: 2px solid #bbb; border-right: 2px solid #bbb; border-bottom: 2px solid #bbb;}
        div#pp_error { background: #bbb;padding: 3px; }

        div#pp { position:relative; }
        .pp-overlay { z-index:3;opacity:0.5; position:absolute; top:2px; bottom:2px; left:2px; right:2px;display:none; background: url(../includes/external/paypal/css/processing.gif) no-repeat; background-position: center center; background-color: #E2E2E2; border-left: 2px solid #bbb; border-right: 2px solid #bbb; border-bottom: 2px solid #bbb;}

      </style>
      <table border="0" width="100%" cellspacing="0" cellpadding="0">
        <tr>
          <td width="120" class="dataTableHeadingContent" style="padding: 0px !important; border: 0px !important;">
            <div id="paypal"><?php echo TEXT_PAYPAL_SUBSCRIPTIONS_HEADING; ?><div class="paypal_logo"></div></div>
          </td>
        </tr>
      </table>
      <?php
        $show_error = 0;
        if (isset($_SESSION['pp_error']) && $_SESSION['pp_error'] != '') {
          echo '<div id="pp_error"><p class="message">'.$_SESSION['pp_error'].'</p></div>';
          unset($_SESSION['pp_error']);
          $show_error = 1;
        } 
        echo '<div id="pp"></div>';
        echo "<script type=\"text/javascript\">
                var show_error = ".$show_error.";
                if (show_error == 1) {
                  $('div#paypal').toggleClass('paypal_active');
                  $('#pp').toggle();
                  get_paypal_data();
                }
                function get_paypal_data() {
                  var products_id = ".(int)$_GET['pID'].";
                  var lang = '".$_SESSION['language_code']."';
                  var secret = '".MODULE_PAYMENT_PAYPAL_SECRET."';
                  $.get('../ajax.php', {ext: 'get_paypal_products', pID: products_id, language: lang, sec: secret}, function(data) {
                    if (data != '' && data != undefined) { 
                      $('#pp').html(decodeEntities(atob(data)));
                      $('.paypal_data').toggleClass('paypal_active');
                      $('.paypal_data').show();
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
    </div>
    <script type="text/javascript">
      $(function() {
        $('div#paypal').click(function(e) {  
          $('#pp_error').hide();
          $('#pp').toggle();
          if ($('#pp').is(':empty')) {
            get_paypal_data();
          }
          $('div#paypal').toggleClass('paypal_active');
          $('.paypal_data').toggleClass('paypal_active');
          if ($('.paypal_data').hasClass('paypal_active')) {
            $('.paypal_data').show();
          } else {
            $('.paypal_data').hide();
          }
        });
      });
    </script>
  <?php
  }
}
?>