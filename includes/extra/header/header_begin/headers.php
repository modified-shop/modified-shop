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
    header("strict-transport-security: max-age=3600");
  }

  $csp_src_array = array();
  $csp_src_array['script-src'][] = "'self'";
  $csp_src_array['script-src'][] = "'unsafe-inline'";
  $csp_src_array['script-src'][] = '"unsafe-eval"';
  $csp_src_array['img-src'][] = "'self'";
  $csp_src_array['style-src'][] = "'self'";
  $csp_src_array['style-src'][] = "'unsafe-inline'";
  $csp_src_array['connect-src'][] = "'self'";
  
  foreach(auto_include(DIR_FS_CATALOG.'includes/extra/header/header_csp/','php') as $file) require_once ($file);
  
  $csp_header = '';
  foreach ($csp_src_array as $csp_k => $csp) {
    $csp_header .= $csp_k.' '.implode(' ', $csp).'; ';
  }  
   
  header("Content-Security-Policy: default-src 'self'; " . $csp_header);
