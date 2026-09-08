<?php
/* -----------------------------------------------------------------------------------------
   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Smarty function for resolving normal template assets through the template chain.

   Usage:
   {template_asset path='img/logo_head.png'}
   {template_asset path='img/logo.gif' versioned=true}
   {template_asset path='img/logo.gif' absolute=true versioned=true}

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

function smarty_function_template_asset($params, $smarty) {
  if (empty($params['path'])) {
    return '';
  }

  $path = (string)$params['path'];
  $versioned = !empty($params['versioned']);

  return !empty($params['absolute'])
    ? Template::absoluteUrl($path, $versioned)
    : Template::url($path, $versioned);
}
