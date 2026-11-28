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
    $(root).find(".cbimages").addBack(".cbimages").colorbox({rel:'cbimages', scalePhotos:true, maxWidth: "90%", maxHeight: "90%", fixed: true});
    $(root).find(".iframe").addBack(".iframe").colorbox({iframe:true, width:"780", height:"560", maxWidth: "90%", maxHeight: "90%", fixed: true});
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
    slideshowStop: "<?php echo TEXT_COLORBOX_SLIDESHOWSTOP; ?>",
    // the title attribute holds plain text, so the lightbox shows it as text and not as markup
    title: function() {
      return $('<div>').text(this.title || '').html();
    },
    // only alt, title and decoding of data-cbox-img-attrs, set as text; without a usable alt the photo takes the one of the preview image
    createImg: function() {
      var img = new Image();
      var attrs = $(this).data('cbox-img-attrs');
      if (!attrs || typeof attrs !== 'object') {
        attrs = {};
      }
      var preview = $(this).find('img').first().attr('alt');
      if (typeof attrs.alt === 'string') {
        img.alt = attrs.alt;
      } else if (typeof preview === 'string') {
        img.alt = preview;
      } else {
        img.alt = this.title || '';
      }
      if (typeof attrs.title === 'string') {
        img.title = attrs.title;
      }
      if (attrs.decoding === 'sync' || attrs.decoding === 'async' || attrs.decoding === 'auto') {
        img.decoding = attrs.decoding;
      }
      return img;
    }
  });
</script>