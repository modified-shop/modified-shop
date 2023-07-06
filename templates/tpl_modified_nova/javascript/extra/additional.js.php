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
  $(function(){
    jQuery.event.special.touchstart = {
      setup: function( _, ns, handle ) {
        this.addEventListener("touchstart", handle, { passive: ns.includes("noPreventDefault") });
      }
    };
    jQuery.event.special.touchmove = {
      setup: function( _, ns, handle ) {
        this.addEventListener("touchmove", handle, { passive: ns.includes("noPreventDefault") });
      }
    };
  });
</script>