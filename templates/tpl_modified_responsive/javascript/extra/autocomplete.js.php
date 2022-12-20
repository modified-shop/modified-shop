<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2019 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/
?>
<script>
  <?php if (SEARCH_AC_STATUS == 'true') { ?>
  var session_id = '<?php echo xtc_session_id(); ?>';
  $('body').on('keydown paste cut input focus', '#inputString', delay(function() {
    if ($(this).length == 0) {
      $('#suggestions').hide();
    } else {
      var post_params = $('#quick_find').serialize();
      
      $.ajax({
        dataType: "json",
        type: 'post',
        url: '<?php echo DIR_WS_BASE; ?>ajax.php?ext=get_autocomplete&MODsid='+session_id,
        data: post_params,
        cache: false,
        async: true,
        success: function(data) {
          if (data !== null && typeof data === 'object') {
            if (data.result !== null && data.result != undefined && data.result != '') {
              $('#autoSuggestionsList').html(decode_ajax(data.result));
              $('#suggestions').slideDown();
            } else {
              $('#suggestions').slideUp();
            }
          }
        }
      });    
    }
  }, 500));
  
  function delay(fn, ms) {
    let timer = 0;
    return function(args) {
      clearTimeout(timer);
      timer = setTimeout(fn.bind(this, args), ms || 0);
    }
  }

  function decode_ajax(encodedString) {
    var textArea = document.createElement('textarea');
    textArea.innerHTML = encodedString;
  
    return textArea.value;
  }

  $('body').on('click', function (e) {
    if ($(e.target).closest("#suggestions").length === 0) {
      ac_closing();
    }
  });
  <?php } ?>
	<?php if (SEARCH_AC_STATUS == 'true' || (basename($PHP_SELF) != FILENAME_SHOPPING_CART && !strpos($PHP_SELF, 'checkout'))) { ?>	
	function ac_closing() {
		setTimeout("$('#suggestions').slideUp();", 100);
	}
  <?php } ?>
</script>  
