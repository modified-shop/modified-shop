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
  $(document).ready(function() {

    <?php if (defined('ADVANCED_SUMOSELECT_SEARCHFIELD') && ADVANCED_SUMOSELECT_SEARCHFIELD == true) { ?>
      $('select:not([name=filter_sort]):not([name=filter_set]):not([name=currency]):not([name=categories_id]):not([id^=sel_])').SumoSelect({search: true, searchText: "<?php echo TEXT_SUMOSELECT_SEARCH; ?>", noMatch: "<?php echo TEXT_SUMOSELECT_NO_RESULT; ?>"});
      $('select[name=filter_sort]').SumoSelect();
      $('select[name=filter_set]').SumoSelect();
      $('select[name=currency]').SumoSelect();
      $('select[name=categories_id]').SumoSelect();
      $('select[id^=sel_]').SumoSelect();

    <?php } else { ?>
      $('select:not([name=country])').SumoSelect();
      $('select[name=country]').SumoSelect({search: true, searchText: "<?php echo TEXT_SUMOSELECT_SEARCH; ?>", noMatch: "<?php echo TEXT_SUMOSELECT_NO_RESULT; ?>"});
    <?php } ?>

    /* Mark Selected */
    var tmpStr = '';
    $('.filter_bar .SumoSelect').each(function(index){
      ($(this).find('select').val() == '') ? $(this).find('p').removeClass("Selected") : $(this).find('p').addClass("Selected");
    });
  });
</script>