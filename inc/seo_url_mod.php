<?php

### shopstat SEO URL
function seo_url_mod($link, $page, $parameters, $connection, $link, $separator) {
  require_once(DIR_FS_INC . 'shopstat_functions.inc.php');
  $mode = !isset($_POST['catalog_link']) ? 'user' : 'admin';
  if($seolink = shopstat_getSEO($page, $parameters, $connection, true, true, $mode)){
    $link      = $seolink;
    $elements  = parse_url($link);
    $separator = (isset($elements['query']) ? '&' : '?');
  }
  return array($link, $separator);
}

?>