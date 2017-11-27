<?php
/*-----------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
  -----------------------------------------------------------
   based on: (c) 2003 - 2006 XT-Commerce (general.js.php)
  -----------------------------------------------------------
   Released under the GNU General Public License
   -----------------------------------------------------------
*/
// this javascriptfile get includes at the BOTTOM of every template page in shop
// you can add your template specific js scripts here
$script_array = array(
  DIR_TMPL_JS.'thickbox.js',
  DIR_TMPL_JS.'jquery.alerts.min.js',
);
$script_min = DIR_TMPL_JS.'tpl_plugins.min.js';

$this_f_time = filemtime(DIR_FS_CATALOG.DIR_TMPL_JS.'general_bottom.js.php');
  
if (COMPRESS_JAVASCRIPT == 'true') {
  require_once(DIR_FS_BOXES_INC.'combine_files.inc.php');
  $script_array = combine_files($script_array,$script_min,false,$this_f_time);
}

foreach ($script_array as $script) {
  $script .= strpos($script,$script_min) === false ? '?v=' . filemtime(DIR_FS_CATALOG.$script) : '';
  echo '<script src="'.DIR_WS_BASE.$script.'" type="text/javascript"></script>'.PHP_EOL;
}
?>

<script type="text/javascript">
  /*BOC jQuery Alerts*/
  $.alerts.overlayOpacity = .2;
  $.alerts.overlayColor = '#000';
  function alert(message, title) {
    title = title || 'Information';
    jAlert(message, title);
  }
  /*EOC jQuery Alerts*/
  $('#button_checkout_confirmation').on('click',function() {
    $(this).hide();
  });
</script>

<script type="text/javascript">
  $(window).on('load',function() {
    $('.show_rating input').change(function () {
      var $radio = $(this);
      $('.show_rating .selected').removeClass('selected');
      $radio.closest('label').addClass('selected');
    });
  });
</script>

<?php if (strstr($PHP_SELF, FILENAME_PRODUCT_INFO )) { // TABS/ACCORDION in product_info - web28 ?>
<script src="<?php echo DIR_WS_BASE.DIR_TMPL_JS; ?>jquery-ui.js" type="text/javascript"></script>
<script type="text/javascript">
/* <![CDATA[ */
  $.get("<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE; ?>"+"/css/javascript.css", function(css) {
    $("head").append("<style type='text/css'>"+css+"<\/style>");
  });
  $(function() {
    $("#tabbed_product_info").tabs();
    $("#accordion_product_info").accordion({ autoHeight: false });
  });
/*]]>*/
</script>
<?php } // TABS/ACCORDION in product_info - web28 ?>

<?php require DIR_FS_CATALOG . DIR_TMPL_JS . 'get_states.js.php'; // Ajax State/District/Bundesland Updater - h-h-h ?>

<?php if (SEARCH_AC_STATUS == 'true') { ?>
<script type="text/javascript">
  var option = $('#suggestions');
  $(document).click(function(e){
    var target = $(e.target);
    if(!(target.is(option) || option.find(target).length)){
      ac_closing();
    }
  });
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
      $.post("<?php echo xtc_href_link('api/autocomplete/autocomplete.php', '', $request_type); ?>", {queryString: ""+inputString+""}, function(data) {
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
</script>
<?php } ?>
<?php if (SEARCH_AC_STATUS == 'true' || (!strstr($PHP_SELF, FILENAME_SHOPPING_CART) && !strstr($PHP_SELF, 'checkout'))) { ?>  
<script type="text/javascript">
  function ac_closing() {
    setTimeout("$('#suggestions').slideUp();", 100);
    ac_page = 1;
  }
</script>
<?php } ?>