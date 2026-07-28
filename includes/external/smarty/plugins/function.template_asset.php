<?php
/* -----------------------------------------------------------------------------------------
   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Smarty function for resolving normal template assets through the template chain.

   Usage:
   {template_asset path='img/logo_head.png'}

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

function smarty_function_template_asset($params, $smarty) {
  if (empty($params['path'])) {
    return '';
  }

  return Template::url((string)$params['path']);
}
