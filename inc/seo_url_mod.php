<?php

### shopstat SEO URL
function seo_url_mod($page, $parameters, $connection, $link, $separator) {
  require_once(DIR_FS_INC . 'shopstat_functions.inc.php');
  if($seolink = shopstat_getSEO($page, $parameters, $connection)){
    $link      = $seolink;
    $elements  = parse_url($link);
    $separator = (isset($elements['query']) ? '&' : '?');
  }
  return array($link, $separator);
}

?>