<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   xtcModified - community made shopping
   http://www.xtc-modified.org

   Copyright (c) 2009 - 2012 xtcModified
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_get_top_level_domain.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce (xtc_get_top_level_domain.inc.php 1535 2006-08-20)

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
function xtc_get_top_level_domain($url) {
	if (strpos($url, '://')) {
		$url = parse_url($url);
		$url = $url['host'];
	}
	$domain_array = explode('.', $url);
	$domain_size = sizeof($domain_array);
	if ($domain_size > 1) {
		if (is_numeric($domain_array[$domain_size -2]) && is_numeric($domain_array[$domain_size -1])) {
			return false;
    //BOF - DokuMan - 2010-09-30 - support for TLD like ".co.uk"
    } elseif ($domain_size > 3) {
			return $domain_array[$domain_size - 3] . '.' . $domain_array[$domain_size - 2] . '.' . $domain_array[$domain_size - 1];
		} else {
			return $domain_array[$domain_size - 2] . '.' . $domain_array[$domain_size - 1];
    //EOF - DokuMan - 2010-09-30 - support for TLD like ".co.uk"
		}
	} else {
		return false;
	}
}
?>