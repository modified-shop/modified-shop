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
  $(document).ready(function(){
    var option = $('#suggestions');
    $(document).click(function(e){
      var target = $(e.target);
      if(!(target.is(option) || option.find(target).length)){
        ac_closing();
      }
    });
  });
  <?php } ?>  

  <?php if (SEARCH_AC_STATUS == 'true') { ?>
  var ac_pageSize = 8;
  var ac_page = 1;
  var ac_result = 0;
  var ac_show_page = '<?php echo AC_SHOW_PAGE; ?>';
  var ac_show_page_of = '<?php echo AC_SHOW_PAGE_OF; ?>';
  
  function ac_showPage(ac_page) {
    ac_result = Math.ceil($("#autocomplete_main").children().length/ac_pageSize);
    $('.autocomplete_content').hide();   
    $('.autocomplete_content').each(function(n) {    
      if (n >= (ac_pageSize * (ac_page - 1)) && n < (ac_pageSize * ac_page)) {
        $(this).show();
      }
    });
    $('#autocomplete_next').css('visibility', 'hidden');
    $('#autocomplete_prev').css('visibility', 'hidden');
    if (ac_page > 1) {
      $('#autocomplete_prev').css('visibility', 'visible');
    }
    if (ac_page < ac_result && ac_result > 1) {
      $('#autocomplete_next').css('visibility', 'visible');
    }
    $('#autocomplete_count').html(ac_show_page+ac_page+ac_show_page_of+ac_result);
  }
  function ac_prevPage() {
    if (ac_page == 1) {
      ac_page = ac_result;
    } else {
      ac_page--;
    }
    if (ac_page < 1) {
      ac_page = 1;
    }
    ac_showPage(ac_page);
  }
  function ac_nextPage() {
    if (ac_page == ac_result) {
      ac_page = 1;
    } else {
      ac_page++;
    }
    ac_showPage(ac_page);
  }
	function ac_lookup(inputString) {
		if(inputString.length == 0) {
			$('#suggestions').hide();
		} else {
      var post_params = $('#quick_find').serialize();
      post_params = post_params.replace("keywords=", "queryString=");
		  
			$.post("<?php echo xtc_href_link('api/autocomplete/autocomplete.php', '', $request_type); ?>", post_params, function(data) {
				if(data.length > 0) {
					$('#suggestions').slideDown();
					$('#autoSuggestionsList').html(data);
					ac_showPage(1);
					$('#autocomplete_prev').click(ac_prevPage);
          $('#autocomplete_next').click(ac_nextPage);
				}
			});
		}
	}
	$('#cat_search').on('change', function () {
	  $('#inputString').val('');
	});	
  <?php } ?>
	<?php if (SEARCH_AC_STATUS == 'true' || (!strstr($PHP_SELF, FILENAME_SHOPPING_CART) && !strstr($PHP_SELF, 'checkout'))) { ?>	
	function ac_closing() {
		setTimeout("$('#suggestions').slideUp();", 100);
		ac_page = 1;
	}
  <?php } ?>
</script>  
