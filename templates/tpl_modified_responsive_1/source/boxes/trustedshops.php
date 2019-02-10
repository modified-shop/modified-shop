<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

// include smarty
include(DIR_FS_BOXES_INC . 'smarty_default.php');

// set cache id
$cache_id = md5($_SESSION['language']);

if (!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_trustedshops.html', $cache_id) || !$cache) {
  $link_array = array(
    'DE' => 'https://www.trustedshops.de/bewertung/info_',
    'AT' => 'https://www.trustedshops.at/bewertung/info_',
    'CH' => 'https://www.trustedshops.ch/bewertung/info_',
    'GB' => 'https://www.trustedshops.co.uk/buyerrating/info_',
    'ES' => 'https://www.trustedshops.es/evaluacion/info_',
    'FR' => 'https://www.trustedshops.fr/evaluation/info_',
    'PL' => 'https://www.trustedshops.pl/opinia/info_',
    'IT' => 'https://www.trustedshops.it/valutazione-del-negozio/info_',
    'NL' => 'https://www.trustedshops.nl/verkopersbeoordeling/info_'
  );
  if (MODULE_TS_WIDGET == '1' && is_file(DIR_FS_CATALOG.'cache/'.MODULE_TS_TRUSTEDSHOPS_ID.'.gif')) {
    $box_smarty->assign('IMAGE_EXISTS', true);
    $box_smarty->assign('IMAGE', DIR_WS_CATALOG.'cache/'.MODULE_TS_TRUSTEDSHOPS_ID.'.gif?t='.filemtime(DIR_FS_CATALOG.'cache/'.MODULE_TS_TRUSTEDSHOPS_ID.'.gif'));
  }
  if (MODULE_TS_REVIEW_STICKER != '' && MODULE_TS_REVIEW_STICKER_STATUS == '1') {
    $box_smarty->assign('STICKER_EXISTS', true);
    $box_smarty->assign('STICKER_CODE', sprintf(MODULE_TS_REVIEW_STICKER, MODULE_TS_TRUSTEDSHOPS_ID));
  }
  $box_smarty->assign('LINK', ((isset($link_array[strtoupper($_SESSION['language_code'])])) ? $link_array[strtoupper($_SESSION['language_code'])] : $link_array[strtoupper(DEFAULT_LANGUAGE)]).MODULE_TS_TRUSTEDSHOPS_ID.'.html');
}

if (!$cache) {
  $box_trustedshops = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_trustedshops.html');
} else {
  $box_trustedshops = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_trustedshops.html', $cache_id);
}

$smarty->assign('box_TRUSTEDSHOPS', $box_trustedshops);
?>