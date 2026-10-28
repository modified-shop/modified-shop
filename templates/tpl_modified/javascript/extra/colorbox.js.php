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
  function initColorBox(root) {
    $(root).find(".cbimages").colorbox({rel:'cbimages', scalePhotos:true, maxWidth: "90%", maxHeight: "90%", fixed: true});
    $(root).find(".iframe").colorbox({iframe:true, width:"780", height:"560", maxWidth: "90%", maxHeight: "90%", fixed: true});
  }

  // content that a script has inserted into the page
  document.addEventListener('modified:content-updated', function(event) {
    initColorBox(event.detail.root);
  });

  $(document).ready(function(){
    initColorBox(document);
    $("#print_order_layer").on('submit', function(event) {
      $.colorbox({iframe:true, width:"780", height:"560", maxWidth: "90%", maxHeight: "90%", href:$(this).attr("action") + '&' + $(this).serialize()});
      return false;
    });
  });

  $(document).bind('cbox_complete', function(){
    if($('#cboxTitle').height() > 20){
      $("#cboxTitle").hide();
      $("<div>"+$("#cboxTitle").html()+"</div>").css({color: $("#cboxTitle").css('color')}).insertAfter("#cboxPhoto");
      //$.fn.colorbox.resize(); // Tomcraft - 2016-06-05 - Fix Colorbox resizing
    }
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