<?php
/*
 * $Id: get_states.js.php 7777 2012-06-15 20:20:00Z h-h-h $
 *
 * modified eCommerce Shopsoftware
 * http://www.modified-shop.org
 *
 * Copyright (c) 2009 - 2013 [www.modified-shop.org]
 *
 *
 * Hacker Solutions - AJAX GET STATES
 * web: www.hackersolutions.com 
 * mail: support@hackersolutions.com
 * 
 * Released under the GNU General Public License
 */

$state_pages = array(
  'address_book_process.php',
  'create_account.php',
  'create_guest_account.php',
  'checkout_shipping_address.php',
  'checkout_payment_address.php'
);
if (ACCOUNT_STATE == 'true' && in_array(basename($PHP_SELF), $state_pages)) {
  $query = xtc_db_query("
      SELECT GROUP_CONCAT(countries_id) AS ids
        FROM ".TABLE_COUNTRIES."
       WHERE required_zones = 1
    ");
  $countries = xtc_db_fetch_array($query);
  if (!empty($countries['ids'])) {
?>
<script type="text/javascript">
/* <![CDATA[ */
var req_states = [<?php echo $countries['ids']; ?>];
var state = '';
function load_state() {
  var selection = $("select[name='country']").val();
  if ($.inArray(parseInt(selection), req_states) == -1) {
<?php if (ENTRY_STATE_MIN_LENGTH > 0) { ?>
    $("select[name='state']").replaceWith('<input type="text" name="state"></input>');
<?php } else { ?>
    $("[name='state']").parent().parent().hide();
<?php } ?>
    return;
  }
  $.get('ajax.php', {ext: 'get_states', country: selection, speed: 1}, function(data) {
    if (data != '' && data != undefined) { 
      $("[name='state']").replaceWith('<select name="state"></select>');
      var stateSelect = $("[name='state']");
      $.each(data, function(id, arr) {
        //console.log('id:' + id + '|text:' + arr.name);
        $("<option />", {
          "value"   : arr.id,
          "text"    : arr.name
        }).appendTo(stateSelect);
      });
      $("[name='state']").val(state);
<?php if (ENTRY_STATE_MIN_LENGTH > 0) { ?>
    } else {
      $("[name='state']").replaceWith('<input type="text" name="state"></input>');
<?php } else { ?>
      stateSelect.parent().parent().show();
    } else {
      $("[name='state']").parent().parent().hide();
<?php } ?>
    }
  });
}
$(function() {
  if ($("[name=state]").length) {
    if ($("[name=state_zone_id]").length) {
      state = $("[name='state_zone_id']").val();
    } else {
      state = $("[name='state']").val();
    }
    //console.log('state: ' + state);
    $("select[name='country']").change(function() { load_state(); });
    if ($('div.errormessage').length == 0 && $("select[type=state] option:selected").length == 0) {
      load_state();
    }
  }
});
/*]]>*/
</script>
<?php 
  }
} 
?>