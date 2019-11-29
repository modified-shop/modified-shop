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
<script type="text/javascript">
  function alert(message, title) {
    title = title || "<?php echo TEXT_LINK_TITLE_INFORMATION; ?>";
    $.alertable.alert('<span id="alertable-title"></span><span id="alertable-content"></span>', { 
      html: true 
    });
    $('#alertable-content').html(message);
    $('#alertable-title').html(title);
  }

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
