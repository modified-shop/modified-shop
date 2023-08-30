<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  header('Referrer-Policy: same-origin');
  header('X-Frame-Options: SAMEORIGIN');
  header('X-XSS-Protection: 1');
  header('X-Content-Type-Options: nosniff');

  if (HTTP_SERVER == HTTPS_SERVER && $request_type == 'SSL') {
    header("strict-transport-security: max-age=2592000");
  }
