<?php

/* -----------------------------------------------------------------------------------------
   $Id$   

   modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com 
   (c) 2003 nextcommerce (xtc_currency_exists.inc.php); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

function xtc_currency_exists($code) {
	$param ='/[^a-zA-Z]/';
	$code=preg_replace($param,'',$code);
	$currency_code = xtc_db_query("SELECT code, currencies_id from " . TABLE_CURRENCIES . " WHERE code = '" . $code . "' LIMIT 1");
	if (xtc_db_num_rows($currency_code)) {
		$curr = xtc_db_fetch_array($currency_code);
		if ($curr['code'] == $code) {
			return $code;
		} else {
			return false;
		}
	} else {
		return false;
	}
}
?>