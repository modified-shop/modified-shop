<?php
	/*  File:       findologic_config.php
	 *  Version:    4.1 (120)
	 *  Date:       08.Sep.2009
	 *
	 *  FINDOLOGIC GmbH
	 */

	require_once("includes/application_top.php");

	// e.g.              "ABCDEFABCDEFABCDEFABCDEFABCDEFAB"
	define("FL_SHOP_ID", MODULE_FINDOLOGIC_SHOP_ID);

	// e.g. "http://www.mein-laden.de/shop/", make sure it starts with "http://" and ends with"/""
	define("FL_SHOP_URL", MODULE_FINDOLOGIC_SHOP_URL);

	// e.g. "http://srvXY.findologic.com/ps/mein-laden.de/"
	define("FL_SERVICE_URL", MODULE_FINDOLOGIC_SERVICE_URL);

	// e.g. true
	define("FL_NET_PRICE", MODULE_FINDOLOGIC_NET_PRICE);

	define("FL_ALIVE_TEST_TIMEOUT", MODULE_FINDOLOGIC_ALIVE_TEST_TIMEOUT);
	define("FL_REQUEST_TIMEOUT", MODULE_FINDOLOGIC_REQUEST_TIMEOUT);
	
	// e.g. "export/findologic.csv"
	define("FL_EXPORT_FILENAME", MODULE_FINDOLOGIC_EXPORT_FILENAME);

	/* uncomment if the shop uses Bluegate SEO URLs */
	// /* Load bluegate SEO Class */
	// require_once(DIR_FS_CATALOG.'inc/bluegate_seo.inc.php');
	// /* Create SEO Object */
	// $bluegateSeo = new BluegateSeo();
	// global $bluegateSeo;

	function get_url($row) {
		//return FL_SHOP_URL.for_url_rewrite($row['products_name'])."::".$row['products_id'].".html";
		//return FL_SHOP_URL."product_info.php?info=p".$row['products_id']."_".for_url_rewrite($row['products_name']).".html";
		//return FL_SHOP_URL."product_info.php/products_id/".$row['products_id'];
		// return FL_SHOP_URL."product_info.php/info/p".$row['products_id']."_".for_url_rewrite($row['products_name']).".html";
        return xtc_href_link(FILENAME_PRODUCT_INFO, xtc_product_link($row['products_id'], $row['products_name']));

		/* uncomment if the shop uses Bluegate SEO URLs DON't FORGET TO UNCOMMENT THE bluegateSeo-LINES ABOVE */
		//global $bluegateSeo;
		//return $productURL = $bluegateSeo->getProductLink(xtc_product_link($row['products_id'], $row['products_name']), null, $_SESSION['languages_id']);
	} 

	// get the revision this was created from
	define("FL_REVISION", preg_replace('/.*(\d+).*/', '$1', '$Revision: '.MODULE_FINDOLOGIC_REVISION.' $'));

	// set language
	define("FL_LANG", MODULE_FINDOLOGIC_LANG);

	/* export prices for the customer group with this ID; defaults to the "Gast" gruppe with ID 1 */
	define('CUSTOMER_GROUP', MODULE_FINDOLOGIC_CUSTOMER_GROUP);

	/* which currency to use for the prices, be sure to use the code (currencies.code in the database) */
	define('CURRENCY', MODULE_FINDOLOGIC_CURRENCY);
