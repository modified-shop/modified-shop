<?php
/* -----------------------------------------------------------------------------------------
   $Id: get_cross_sell_name.inc.php 1232 2005-09-21 15:29:07Z mz $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2005 XT-Commerce
   -----------------------------------------------------------------------------------------

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
 
 function xtc_get_cross_sell_name($cross_sell_group, $language_id = '') {

	if (!$language_id)
		$language_id = $_SESSION['languages_id'];
	$cross_sell_query = xtc_db_query("select groupname from ".TABLE_PRODUCTS_XSELL_GROUPS." where products_xsell_grp_name_id = '".$cross_sell_group."' and language_id = '".$language_id."'");
	$cross_sell = xtc_db_fetch_array($cross_sell_query);

	return $cross_sell['groupname'];
}
?>
