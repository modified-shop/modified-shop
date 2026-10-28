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
    function initViewer(root) {
      var pd_image_zoomer = root.querySelector('#pd_image_zoomer');
      // the product page only has the zoomer when the article has an image
      if (!pd_image_zoomer) {
        return;
      }
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
    }

    window.addEventListener('DOMContentLoaded', function () {
      initViewer(document);
    });

    // content that a script has inserted into the page
    document.addEventListener('modified:content-updated', function (event) {
      initViewer(event.detail.root);
    });

    // content that a script is about to replace or remove
    document.addEventListener('modified:before-content-update', function (event) {
      var root = event.detail.root;
      var pd_image_zoomer = (root.id == 'pd_image_zoomer') ? root : root.querySelector('#pd_image_zoomer');
      if (pd_image_zoomer && pd_image_zoomer.viewer) {
        pd_image_zoomer.viewer.destroy();
      }
    });
  </script>
  <?php
}