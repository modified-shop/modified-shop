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
	//define("FL_SHOP_URL", MODULE_FINDOLOGIC_SHOP_URL);
	define("FL_SHOP_URL", HTTP_SERVER.DIR_WS_CATALOG); // Changed to static value

	// e.g. "http://srvXY.findologic.com/ps/mein-laden.de/"
	define("FL_SERVICE_URL", MODULE_FINDOLOGIC_SERVICE_URL);

	// e.g. true
	//define("FL_NET_PRICE", MODULE_FINDOLOGIC_NET_PRICE);
	define("FL_NET_PRICE", false); // Changed to static value

	//define("FL_ALIVE_TEST_TIMEOUT", MODULE_FINDOLOGIC_ALIVE_TEST_TIMEOUT);
	define("FL_ALIVE_TEST_TIMEOUT", 1); // Changed to static value
	//define("FL_REQUEST_TIMEOUT", MODULE_FINDOLOGIC_REQUEST_TIMEOUT);
	define("FL_REQUEST_TIMEOUT", 3); // Changed to static value
	
	// e.g. "export/findologic.csv"
	define("FL_EXPORT_FILENAME", MODULE_FINDOLOGIC_EXPORT_FILENAME);

	// get the revision this was created from
	//define("FL_REVISION", preg_replace('/.*(\d+).*/', '$1', '$Revision: '.MODULE_FINDOLOGIC_REVISION.' $'));
	define("FL_REVISION", preg_replace('/.*(\d+).*/', '$1', '$Revision: 204 $')); // Changed to static value

	// set language
	define("FL_LANG", MODULE_FINDOLOGIC_LANG);

	/* export prices for the customer group with this ID; defaults to the "Gast" gruppe with ID 1 */
	define('CUSTOMER_GROUP', MODULE_FINDOLOGIC_CUSTOMER_GROUP);

	/* which currency to use for the prices, be sure to use the code (currencies.code in the database) */
	define('CURRENCY', MODULE_FINDOLOGIC_CURRENCY);
