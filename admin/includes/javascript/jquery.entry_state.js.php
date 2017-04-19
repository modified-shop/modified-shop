<?php
 /*-------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

?>

<script>

    $(document).ready(function () {
      //console.log($('select[name="entry_country_id"]').val());
      create_states($('select[name="entry_country_id"]').val());
      
      $('select[name="entry_country_id"]').change(function() {
        create_states($(this).val());
      });
    });
    
    function create_states(val) {
        var type = '';
        var zone = '&zone=' + $('[name="entry_state"]').val();
        if ($('select[name="entry_state"]').length) {
          type = '&type=select';
        }
        $('#states_container').html('<img src="images/loading.gif">');
        
        var ajax_url = 'create_account.php<?php echo defined('SID') ? '?'. SID : '';?>';
        var token = '&<?php echo $_SESSION['CSRFName'] . '='.  $_SESSION['CSRFToken']?>';
        var data = 'action=get_states&countryid=' + val + type + zone + token;
        //console.log(data);
        //return;
        jQuery.ajax({
          data:     data,
          url:      ajax_url,
          type:     "POST",
          async:    true,
          success:  function(t_states) {
            $('#states_container').html(t_states);
            /*
            $('#states').show();
            if ($('[name="entry_state"]').attr('type') == 'hidden') {
              $('#states').hide();
            }
            */
            <?php if (NEW_SELECT_CHECKBOX == 'true') { ?>
              $('.select_states').not('.noStyling').SumoSelect({ createElems: 'mod', placeholder: '-'});
            <?php } ?>
          }
        });
    }
    
</script>