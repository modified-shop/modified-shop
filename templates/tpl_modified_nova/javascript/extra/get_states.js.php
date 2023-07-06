<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2019 [www.modified-shop.org]
   --------------------------------------------------------------
   based on: 
   (c) Hacker Solutions

   Released under the GNU General Public License
   --------------------------------------------------------------*/

$state_pages = array(
  'address_book_process.php',
  'create_account.php',
  'create_guest_account.php',
  'checkout_shipping_address.php',
  'checkout_payment_address.php'
);

if (ACCOUNT_STATE == 'true' && in_array(basename($PHP_SELF), $state_pages)) {
  
  //countries with zones
  $query = xtDBquery("SELECT GROUP_CONCAT(countries_id) AS ids
                        FROM ".TABLE_COUNTRIES."
                       WHERE required_zones = 1");
  $countries = xtc_db_fetch_array($query, true);
  
  //countries without zones
  $query = xtDBquery("SELECT GROUP_CONCAT(c.countries_id) AS ids
                        FROM countries c 
                   LEFT JOIN zones z ON c.countries_id = z.zone_country_id
                       WHERE z.zone_country_id IS NULL;");
  $countries_without_zones = xtc_db_fetch_array($query, true);
?>
<script type="text/javascript">
  var req_zones = [<?php echo $countries['ids']; ?>];
  var no_zones = [<?php echo $countries_without_zones['ids']; ?>];
  var state = '';
  var min_length = <?php echo (ENTRY_STATE_MIN_LENGTH > 0 ? 1 : 0)?>;

  function show_state() {
    $("[name='state']").closest("div.field_item_1").show();
    $("[name='state']").prop('type', 'text'); //fix for check_form in form_check.js.php
  }

  function hide_state() {
    $("[name='state']").closest("div.field_item_1").hide();
    $("[name='state']").prop('type', 'hidden'); //fix for check_form in form_check.js.php
  }

  function load_state() {
    var selection = $("#addressbook select[name='country'], #checkout_address select[name='country'], #create_account select[name='country']").val();
  
    //change select to input
    var tmpParent = $("[name='state']").parent();
  
    if (tmpParent.attr("class") !== undefined && tmpParent.attr("class").indexOf("SumoSelect") > -1) {
      tmpParent.replaceWith('<input type="text" name="state"></input>');
    } else {
      $("select[name='state']").replaceWith('<input type="text" name="state"></input>');
    }
    
    //countries without zones
    if ($.inArray(parseInt(selection), no_zones) != -1) {
      if (min_length) {
        show_state();
      } else {
        hide_state();
      } 
      return;
    }
  
    //countries without required_zones
    if ($.inArray(parseInt(selection), req_zones) == -1) {
      hide_state();
      return;
    }
  
    //countries with required_zones
    $.get('ajax.php', {ext: 'get_states', country: selection, speed: 1}, function(data) {
      if (data != '' && data != undefined) {
        $("[name='state']").replaceWith('<select name="state"></select>');
        var stateSelect = $("[name='state']");
        var isavail = false;
        $.each(data, function(id, arr) {
          if (state != 0 && state == arr.id) { 
            isavail = true;
          }
          $("<option />", {
            "value"   : arr.id,
            "text"    : arr.name
          }).appendTo(stateSelect);
        });
        if (state != 0 && isavail) {
          $("[name='state']").val(state);
        } else {
          $("[name='state']").prop('selectedIndex',0);
        }
        $('select[name=state]').SumoSelect({search: true, searchText: "<?php echo TEXT_SUMOSELECT_SEARCH; ?>", noMatch: "<?php echo TEXT_SUMOSELECT_NO_RESULT; ?>"});
        stateSelect.closest("div.field_item_1").show();      
      } else {
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
    }
    $("select[name='country']").change(function() { 
      load_state();
    });
    load_state();
  });
</script>
<?php 
} 
