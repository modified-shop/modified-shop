<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/
?>
<script type="text/javascript">
  // thickbox.js starts the links of the page, the marker keeps them from a second click handler
  $(function() {
    $('a.thickbox, area.thickbox, input.thickbox').attr('data-thickbox', 'true');
  });

  // content that a script has inserted into the page
  document.addEventListener('modified:content-updated', function(event) {
    var selector = 'a.thickbox, area.thickbox, input.thickbox';
    var links = $(event.detail.root).find(selector).addBack(selector).not('[data-thickbox]');
    tb_init(links.attr('data-thickbox', 'true'));
  });

  // thickbox writes the caption into its markup as it is, the title of a link holds plain text
  $(function() {
    if (typeof window.tb_show === 'function') {
      var tb_show_markup = window.tb_show;
      window.tb_show = function(caption, url, imageGroup) {
        if (typeof caption === 'string') {
          caption = caption.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }
        return tb_show_markup(caption, url, imageGroup);
      };
    }
  });
</script>
