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
    var links = $(event.detail.root).find('a.thickbox, area.thickbox, input.thickbox').not('[data-thickbox]');
    tb_init(links.attr('data-thickbox', 'true'));
  });
</script>
