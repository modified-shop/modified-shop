<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2019 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

if (strpos(basename($PHP_SELF), 'checkout') === false 
    || strpos(basename($PHP_SELF), 'account_checkout_express') !== false
    )
{
  ?>
  <script>

  let mobilemenu = document.getElementById('mobile_menu');
  if (mobilemenu !== null) { 

    document.addEventListener(
      "DOMContentLoaded", () => {
        const menu = new MmenuLight(
          document.querySelector( "#mobile_menu" ),
          'all'
        );

        const navigator = menu.navigation({
          selectedClass: 'Selected',
          slidingSubmenus: true,
          theme: 'light',
          title: '<?php echo TEXT_MENU_TITLE; ?>'
        });
        const drawer = menu.offcanvas({
          position: 'left'
        });

        document.querySelector( 'div[id="#mobile_menu"]' )
        .addEventListener( "click", ( evnt ) => {
          evnt.preventDefault();
          drawer.open();
        });

        document.querySelector( 'div[id="menu_closer"]' )
        .addEventListener( "click", ( evnt ) => {
          evnt.preventDefault();
          drawer.close();
        });

      }
    );
  }    
    
  </script>
  <?php
}
