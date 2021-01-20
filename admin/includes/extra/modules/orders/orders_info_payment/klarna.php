<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

if (isset($order) && is_object($order)) {
  if ($order->info['payment_method'] == 'klarna_checkout' 
      || $order->info['payment_method'] == 'klarna_paylater'
      || $order->info['payment_method'] == 'klarna_payovertime'
      || $order->info['payment_method'] == 'klarna_directdebit'
      || $order->info['payment_method'] == 'klarna_directbanktransfer'
      || $order->info['payment_method'] == 'klarna_paynow'
      ) 
  {
    require_once(DIR_FS_EXTERNAL.'klarna/classes/KlarnaPayment.php');
    $klarna = new KlarnaPayment($order->info['payment_method']);
    ?>
    <tr>
      <td colspan="2" style="width:990px;">
        <style type="text/css">
          p.error_message { margin:0px; padding: 1ex 1em; margin: 5px 1px; color: #A94442; border: 1px solid #DCA7A7; background-color: #F2DEDE; }
          p.info_message { margin:0px; padding: 1ex 1em; margin: 5px 1px; color: #3C763D; border: 1px solid #b2dba1; background-color: #d4ebcb; }
          div.pp_box { background: #E2E2E2; float: left; padding: 1ex; margin: 1px; min-height: 162px; min-width:48.4%; width:48.4%; }
          .pp_box_full {width:98.3% !important;}
          div.pp_boxheading { font-size: 1.2em; font-weight: bold; background: #CCCCCC; padding: .2ex .5ex;}
          dl.pp_transaction { overflow: auto; margin: 0 0; border-bottom: 1px dotted #999; padding:2px 0px; }
          dl.pp_transaction dt, dl.pp_transaction dd { margin: 0; float: left; }
          dl.pp_transaction dt { clear: left; width: 12em; font-weight: bold; }
          div#klarna { position:relative; cursor: pointer; background: #ccc url(../includes/external/klarna/css/arrow_down.png) no-repeat 4px 9px; padding:10px 0 10px 30px; }
          .klarna_logo {  position:absolute; top:4px; right:0px; width:85px; height: 26px; background: transparent url(../includes/external/klarna/css/logo_klarna.png) no-repeat 0px 0px;}
          .klarna_active { background: #bbb url(../includes/external/klarna/css/arrow_up.png) no-repeat 4px 9px !important; }
          .klarna_data { font-family: Verdana; font-size:10px !important; }
          div.pp_txstatus_received { background: transparent url(../includes/external/klarna/css/arrow_down_small.png) no-repeat 460px 3px; margin: 0 0; cursor: pointer;  border-bottom: 1px dotted #999; padding:2px 0px; line-height:14px; }
          div.pp_txstatus_open { background: #e6778d url(../includes/external/klarna/css/arrow_up_small.png) no-repeat 460px 3px !important; font-weight: bold; color: #fff; }
          div.pp_txstatus_data { display: none; }
          dl.pp_txstatus_data_list { overflow: auto; margin:0 0; border-bottom: 1px dotted #ccc; padding:2px 2px; background:#fafafa; }
          dl.pp_txstatus_data_list dt, dl.pp_txstatus_data_list dd { margin: 0; float: left; max-width:270px; }
          dl.pp_txstatus_data_list dt { clear: left; width: 12em; font-weight: bold; }
          div.pp_capture form, div.pp_refund form { display: block; padding: 0.5ex; }
          div.refund_row { padding:3px 0px; }
          div.pp_refund label, div.refund_row label { display: inline-block; width: 12em; }
          #refund_comment { width: 340px; resize: none; }
          div#pp { display:none; min-height: 44px; background: url(../includes/external/klarna/css/processing.gif) no-repeat; background-position: center center; background-color: #E2E2E2; border-left: 2px solid #bbb; border-right: 2px solid #bbb; border-bottom: 2px solid #bbb;}
          div#pp_error { background: #bbb;padding: 3px; }
        </style>
        <table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td width="120" class="dataTableHeadingContent" style="padding: 0px !important; border: 0px !important;">
              <div id="klarna"><?php echo TEXT_KLARNA_ORDERS_HEADING; ?><div class="klarna_logo"></div></div>
            </td>
          </tr>
        </table>
        <?php
          $show_error = 0;
          if (isset($_SESSION['klarna_success']) && $_SESSION['klarna_success'] != '') {
            echo '<div id="pp_error"><p class="info_message">'.$_SESSION['klarna_success'].'</p></div>';
            unset($_SESSION['klarna_success']);
            $show_error = 1;
          } 
          if (isset($_SESSION['klarna_error']) && $_SESSION['klarna_error'] != '') {
            echo '<div id="pp_error"><p class="error_message">'.$_SESSION['klarna_error'].'</p></div>';
            unset($_SESSION['klarna_error']);
            $show_error = 1;
          } 
          echo '<div id="pp"></div>';
          echo "<script type=\"text/javascript\">
                  var show_error = ".$show_error.";
                  if (show_error == 1) {
                    $('div#klarna').toggleClass('klarna_active');
                    $('#pp').toggle();
                    get_klarna_data();
                  }
                  function get_klarna_data() {
                    var order_id = ".$order->info['orders_id'].";
                    var lang = '".$_SESSION['language_code']."';
                    var secret = '".MODULE_PAYMENT_KLARNA_AJAX_SECRET."';
                    $.get('../ajax.php', {ext: 'get_klarna_data', oID: order_id, language: lang, sec: secret}, function(data) {                      
                      if (data != '' && data != undefined) { 
                        $('#pp').html(decodeEntities(atob(data)));
                        $('.klarna_data').toggleClass('klarna_active');
                        $('.klarna_data').show();
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
        $('div#klarna').click(function(e) {  
          $('#pp_error').hide();
          $('#pp').toggle();
          if ($('#pp').is(':empty')) {
            get_klarna_data();
          }
          $('div#klarna').toggleClass('klarna_active');
          $('.klarna_data').toggleClass('klarna_active');
          if ($('.klarna_data').hasClass('klarna_active')) {
            $('.klarna_data').show();
          } else {
            $('.klarna_data').hide();
          }
        });
      });
    </script>
  <?php
  }
}
?>