<?php
/*
 * $Id: get_states.js.php 9721 2016-04-25 20:57:27Z web28 $
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
  
  //Länder mit required_zones = 1 -> Dropdown anzeigen
  $query = xtc_db_query(
    "SELECT GROUP_CONCAT(countries_id) AS ids
        FROM ".TABLE_COUNTRIES."
       WHERE required_zones = 1
    ");
  $countries = xtc_db_fetch_array($query);
  
  //Länder ohne Zonen -> Inputfeld anzeigen
  $query = xtc_db_query(
      "SELECT GROUP_CONCAT(c.countries_id) AS ids
         FROM countries c 
    LEFT JOIN zones z ON c.countries_id = z.zone_country_id
        WHERE z.zone_country_id IS NULL;
      ");

  $countries_without_zones = xtc_db_fetch_array($query);
?>
<script type="text/javascript">
/* <![CDATA[ */
var req_zones = [<?php echo $countries['ids']; ?>];
var no_zones = [<?php echo $countries_without_zones['ids']; ?>];
var state = '';
var min_length = <?php echo (ENTRY_STATE_MIN_LENGTH > 0 ? 1 : 0)?>;

function show_state() {
  $("[name='state']").parent().parent().parent().show();
  $("[name='state']").prop('type', 'text'); //fix for check_form in form_check.js.php
}

function hide_state() {
  $("[name='state']").parent().parent().parent().hide();
  $("[name='state']").prop('type', 'hidden'); //fix for check_form in form_check.js.php
}

function load_state() {
  var selection = $("select[name='country']").val();
  //console.log('SE:'+ selection);
  
  //change select to input
  var tmpParent = $("[name='state']").parent();
  if (tmpParent.attr("class") == "SumoSelect") {
    tmpParent.replaceWith('<input type="text" name="state"></input>');
  } else {
    $("select[name='state']").replaceWith('<input type="text" name="state"></input>');
  }
  
  //countries without zones
  if ($.inArray(parseInt(selection), no_zones) != -1) {
    //console.log('no_zones:'+ min_length);
    if (min_length) {
        show_state();
    } else {
        hide_state();
    } 
    return;
  }
  
  //countries without required_zones
  if ($.inArray(parseInt(selection), req_zones) == -1) {
    //console.log('req_zones');
    hide_state();
    return;
  }
  
  //countries with required_zones
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
      tmpParent = $("[name='state']").parent();
      if(tmpParent.attr("class") == "SumoSelect"){
        tmpParent.replaceWith($("[name='state']"));
      }
      $('select').SumoSelect();
      stateSelect.parent().parent().parent().parent().show();
    } else {
      if (tmpParent.attr("class") == "SumoSelect") {
        tmpParent.replaceWith('<input type="text" name="state"></input>');
      } else {
        $("[name='state']").replaceWith('<input type="text" name="state"></input>');
      }
      if (min_length) {
         show_state();
      } else {
         hide_state();
      }
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
    // console.log('state: ' + state);
  }
  $("select[name='country']").change(function() { load_state(); });
  //if ($('div.errormessage').length == 0 && $("select[type=state] option:selected").length == 0) {
    load_state();
  //}
});
/*]]>*/
</script>
<?php 
} 
?>