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
	<?php if (SEARCH_AC_STATUS == 'true' || (basename($PHP_SELF) != FILENAME_SHOPPING_CART && !strpos($PHP_SELF, 'checkout'))) { ?>	
	function ac_closing() {
		setTimeout("$('#suggestions').slideUp();", 100);
	}
  <?php } ?>
  <?php if (SEARCH_AC_STATUS == 'true') { ?>
  var session_id = '<?php echo xtc_session_id(); ?>';
  
  function ac_ajax_call(post_params) {
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
            $('#autoSuggestionsList').html(ac_decode(data.result));
            $('#suggestions').slideDown();
          } else {
            $('#suggestions').slideUp();
          }
        }
      }
    });    
  }
  
  function ac_delay(fn, ms) {
    let timer = 0;
    return function(args) {
      clearTimeout(timer);
      timer = setTimeout(fn.bind(this, args), ms || 0);
    }
  }

  function ac_decode(encodedString) {
    var textArea = document.createElement('textarea');
    textArea.innerHTML = encodedString;
  
    return textArea.value;
  }

  $('body').on('keydown paste cut input focus', '#inputString', ac_delay(function() {
    if ($(this).length == 0) {
      $('#suggestions').hide();
    } else {
      let post_params = $('#quick_find').serialize();
      ac_ajax_call(post_params);
    }
  }, 500));

  $('body').on('click', function (e) {    
    if ($(e.target).closest("#suggestions").length === 0
        && $(e.target).closest("#quick_find").length === 0
        )
    {
      ac_closing();
    }
  });

  <?php if(defined('SEARCH_AC_CATEGORIES') && SEARCH_AC_CATEGORIES == 'true') { ?>
  $('body').on('change', '#cat_search', ac_delay(function() {
    let post_params = $('#quick_find').serialize();
    ac_ajax_call(post_params);
  }, 500));
  <?php } ?>
  <?php } ?>
</script>  
