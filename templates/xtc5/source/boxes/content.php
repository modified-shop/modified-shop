<?php

/* -----------------------------------------------------------------------------------------
   $Id: content.php 1302 2005-10-12 16:21:29Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(information.php,v 1.6 2003/02/10); www.oscommerce.com 
   (c) 2003	 nextcommerce (content.php,v 1.2 2003/08/21); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
$box_smarty = new smarty;
$content_string = '';
//$rebuild = false; //DokuMan - 2010-02-28 - fix Smarty cache error on unlink

$box_smarty->assign('language', $_SESSION['language']);
// set cache ID
if (!CacheCheck()) {
	$cache=false;
	$box_smarty->caching = 0;
} else {
	$cache=true;
	$box_smarty->caching = 1;
	$box_smarty->cache_lifetime = CACHE_LIFETIME;
	$box_smarty->cache_modified_check = CACHE_CHECK;
	$cache_id = $_SESSION['language'].$_SESSION['customers_status']['customers_status_id'];
}

if (!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_content.html', $cache_id) || !$cache) {
	$box_smarty->assign('tpl_path', 'templates/'.CURRENT_TEMPLATE.'/');
	//$rebuild = true; //DokuMan - 2010-02-28 - fix Smarty cache error on unlink

	if (GROUP_CHECK == 'true') {
		$group_check = "and group_ids LIKE '%c_".$_SESSION['customers_status']['customers_status_id']."_group%'";
	}

	$content_query = "SELECT
	 					content_id,
	 					categories_id,
	 					parent_id,
	 					content_title,
	 					content_group
	 					FROM ".TABLE_CONTENT_MANAGER."
	 					WHERE languages_id='".(int) $_SESSION['languages_id']."'
	 					and file_flag=1 ".$group_check." and content_status=1 order by sort_order";

	$content_query = xtDBquery($content_query);

	$content_string='<ul class="contentlist">';
	while ($content_data = xtc_db_fetch_array($content_query, true)) {
		$SEF_parameter = '';
		if (SEARCH_ENGINE_FRIENDLY_URLS == 'true')
			$SEF_parameter = '&content='.xtc_cleanName($content_data['content_title']);

		$content_string .= '<li><a href="'.xtc_href_link(FILENAME_CONTENT, 'coID='.$content_data['content_group'].$SEF_parameter).'"><strong>'.$content_data['content_title'].'</strong></a></li>';
	}
	if ($content_string != '') {
		$content_string.='</ul>';
		$box_smarty->assign('BOX_CONTENT', $content_string);
	}
		
	

}
//BOF - DokuMan - 2010-02-28 - fix Smarty cache error on unlink
//if ($rebuild) $box_smarty->clear_cache(CURRENT_TEMPLATE.'/boxes/box_content.html', $cache_id);
//$box_content = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_content.html',$cache_id);
if (!$cache) {
    $box_content = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_content.html');
} else {
    $box_content = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_content.html', $cache_id);
}
//EOF - DokuMan - 2010-02-28 - fix Smarty cache error on unlink

$smarty->assign('box_CONTENT', $box_content);
?>