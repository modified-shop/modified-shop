<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2019 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

if (strstr($PHP_SELF, FILENAME_PRODUCT_INFO )) {
  ?>
  <script>
    window.addEventListener('DOMContentLoaded', function () {
      var pd_image_zoomer = document.getElementById('pd_image_zoomer');
      var viewer = new Viewer(pd_image_zoomer, {
        url: 'data-original',
        title: [0, (image, imageData) => `${image.alt}`],
        maxZoomRatio: 1,
        zIndex: 9999999,
        transition: true,
        toolbar: {
          zoomIn: 1,
          reset: 1,
          zoomOut: 1,
          prev: 1,
          next: 1,
        },
      });
    });    
  </script>
  <?php
}