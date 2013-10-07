<?php
/* -----------------------------------------------------------------------------------------
   $Id: general.js.php 1262 2005-09-30 10:00:32Z mz $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


   // this javascriptfile get includes at the BOTTOM of every template page in shop
   // you can add your template specific js scripts here
?>
<script src="<?php echo 'templates/'.CURRENT_TEMPLATE; ?>/javascript/jquery-1.8.3.min.js" type="text/javascript"></script>
<script src="<?php echo 'templates/'.CURRENT_TEMPLATE; ?>/javascript/thickbox.js" type="text/javascript"></script>
<script src="<?php echo 'templates/'.CURRENT_TEMPLATE; ?>/javascript/jquery.easytabs.min.js" type="text/javascript"></script>


<?php if (strstr($PHP_SELF, FILENAME_PRODUCT_INFO )) { // TABS/ACCORDION in product_info - web28 ?>
<script type="text/javascript">
/* <![CDATA[ */
	//Laden einer CSS Datei mit jquery	
    $.get("<?php echo 'templates/'.CURRENT_TEMPLATE; ?>"+"/css/javascript.css", function(css) {
		$("head").append("<style type='text/css'>"+css+"<\/style>");
	});
	
	$(function() {
		$('#tabbed_product_info').easytabs({
      animate: true,
      animationSpeed: 300,
      cycle: 4000,
      updateHash: false //Tells easyTabs whether or not to update the browser window's URL hash, useful for SEO and bookmarking. true, false (true)
    });
		//$("#accordion_product_info").accordion({ autoHeight: false });
	});
/*]]>*/
</script>
<?php } // TABS/ACCORDION in product_info - web28 ?>

<?php // Ajax State/District/Bundesland Updater - h-h-h
$state_pages = array('address_book_process.php','create_account.php','create_guest_account.php','checkout_shipping_address.php','checkout_payment_address.php');
if (ACCOUNT_STATE == 'true' && in_array(basename($PHP_SELF), $state_pages)) { ?>
<script type="text/javascript">
/* <![CDATA[ */
function load_state() {
  var selection = $("select[name='country']").val();
  $.get('ajax.php', {ext: 'get_states', country: selection, speed: 1}, function(data) {
    if (data != '' && data != undefined) {  
      $("[name='state']").replaceWith('<select name="state"></select>');
      var stateSelect = $("[name='state']");
      $.each(data, function(id, text) {
        $("<option />", {
          "value"   : text,
          "text"    : text
        }).appendTo(stateSelect);
      });
    } else {
      $("[name='state']").replaceWith('<input type="text" name="state"></input>');
    }
  });
}
$(function() {
  if ($("[name=state]").length) {
    $("select[name='country']").change(function() { load_state(); });
    if ($('div.errormessage').length == 0 && $("select[type=state] option:selected").length == 0) {
      load_state();
    }
  }
});
/*]]>*/
</script>
<?php } // Ajax State/District/Bundesland Updater - h-h-h ?>

