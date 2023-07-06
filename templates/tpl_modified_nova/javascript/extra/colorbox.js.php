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
  $(document).ready(function(){
    $(".cbimages").colorbox({rel:'cbimages', scalePhotos:true, maxWidth: "96%", maxHeight: "96%", fixed: true, close: '<i class="fa-solid fa-xmark"></i>', next: '<i class="fa-solid fa-angle-right"></i>', previous: '<i class="fa-solid fa-angle-left"></i>'});
    $(".iframe").colorbox({iframe:true, width:"780", height:"560", maxWidth: "96%", maxHeight: "96%", fixed: true, close: '<i class="fa-solid fa-xmark"></i>'});
    $(".inline").colorbox({inline:true, width:"780", maxWidth: "96%", maxHeight: "96%", fixed: true, close: '<i class="fa-solid fa-xmark"></i>'});
    $("#print_order_layer").on('submit', function(event) {
      $.colorbox({iframe:true, width:"780", height:"560", maxWidth: "90%", maxHeight: "90%", close: '<i class="fa-solid fa-xmark"></i>', href:$(this).attr("action") + '&' + $(this).serialize()});
      return false;
    });
  });

  jQuery.extend(jQuery.colorbox.settings, {
    current: "<?php echo TEXT_COLORBOX_CURRENT; ?>",
    previous: "<?php echo TEXT_COLORBOX_PREVIOUS; ?>",
    next: "<?php echo TEXT_COLORBOX_NEXT; ?>",
    close: "<?php echo TEXT_COLORBOX_CLOSE; ?>",
    xhrError: "<?php echo TEXT_COLORBOX_XHRERROR; ?>",
    imgError: "<?php echo TEXT_COLORBOX_IMGERROR; ?>",
    slideshowStart: "<?php echo TEXT_COLORBOX_SLIDESHOWSTART; ?>",
    slideshowStop: "<?php echo TEXT_COLORBOX_SLIDESHOWSTOP; ?>"
  });
</script>
