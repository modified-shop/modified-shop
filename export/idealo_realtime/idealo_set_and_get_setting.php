<?php
/* -----------------------------------------------------------------------------------------

   Extended by

   - Christoph Zurek (Idealo Internet GmbH, http://www.idealo.de)

	Please note that this extension is provided as is and without any warranty. It is recommended to always backup your installation prior to use. Use at your own risk.

   ---------------------------------------------------------------------------------------*/

 
 /**
  * get and set settings at congig table from $_POST
  */

define ( 'COMMENTLENGTH', 100 );

// check if certifate is already in db
$certificate_query = xtc_db_query( "select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_REALTIME_CERTIFICATE' LIMIT 1" );
$certificate_db = xtc_db_fetch_array( $certificate_query ); // false if 'MODULE_IDEALO_REALTIME_CERTIFICAT' doesn't exist

// check if testmode active is already in db
$testmode_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_REALTIME_TESTMODE' LIMIT 1");
$testmode_db = xtc_db_fetch_array($testmode_query); // false if 'MODULE_IDEALO_REALTIME_TESTMODE' doesn't exist


// check if campaign is already in db
$campaign_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_CAMPAIGN' LIMIT 1");
$campaign_db = xtc_db_fetch_array($campaign_query); // false if 'MODULE_IDEALO_CAMPAIGN' doesn't exist

// check if URL is already in db
$url_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_REALTIME_URL' LIMIT 1");
$url_db = xtc_db_fetch_array($url_query); // false if 'MODULE_IDEALO_REALTIME_url' doesn't exist

// check if shop_id is already in db
$shop_id_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_REALTIME_SHOP_ID' LIMIT 1");
$shop_id_db = xtc_db_fetch_array($shop_id_query); // false if 'MODULE_IDEALO_REALTIME_SHOP_ID' doesn't exist

// check if a password character is already in db
$password_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_REALTIME_PASSWORD' LIMIT 1");
$password_db = xtc_db_fetch_array($password_query); // false if 'MODULE_IDEALO_REALTIME_PASSWORD doesn't exist

// check if a pagesize character is already in db
$pagesize_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_REALTIME_PAGESIZE' LIMIT 1");
$pagesize_db = xtc_db_fetch_array($pagesize_query); // false if 'MODULE_IDEALO_REALTIME_PAGESIZE doesn't exist

// check if shippinglimit_input is already in db
$shipping_input_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_REALTIME_SHIPPINGCOMMENT' LIMIT 1");
$shipping_comment_db = xtc_db_fetch_array($shipping_input_query); // false if 'MODULE_IDEALO_REALTIME_SHIPPINGCOMMENT' doesn't exist


// check if catfilter is already in db
$_realtime_cat_filter_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_REALTIME_CAT_FILTER' LIMIT 1");
$_realtime_cat_filter_db = xtc_db_fetch_array($_realtime_cat_filter_query); // false if 'MODULE_IDEALO_REALTIME_CAT_FILTER' doesn't exist

// check if catfilter is already in db
$_realtime_cat_filter_value_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_REALTIME_CAT_FILTER_VALUE' LIMIT 1");
$_realtime_cat_filter_value_db = xtc_db_fetch_array($_realtime_cat_filter_value_query); // false if 'MODULE_IDEALO_REALTIME_CAT_FILTER' doesn't exist

// check if brandfilter is already in db
$_realtime_brand_filter_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_REALTIME_BRAND_FILTER' LIMIT 1");
$_realtime_brand_filter_db = xtc_db_fetch_array($_realtime_brand_filter_query); // false if 'MODULE_IDEALO_REALTIME_BRAND_FILTER' doesn't exist

// check if brandfilter is already in db
$_realtime_brand_filter_value_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_REALTIME_BRAND_FILTER_VALUE' LIMIT 1");
$_realtime_brand_filter_value_db = xtc_db_fetch_array($_realtime_brand_filter_value_query); // false if 'MODULE_IDEALO_REALTIME_BRAND_FILTER' doesn't exist


// check if articlefilter is already in db
$_realtime_article_filter_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_REALTIME_ARTICLE_FILTER' LIMIT 1");
$_realtime_article_filter_db = xtc_db_fetch_array($_realtime_article_filter_query); // false if 'MODULE_IDEALO_REALTIME_ARTICLE_FILTER' doesn't exist

// check if articlefilter is already in db
$_realtime_article_filter_value_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_REALTIME_ARTICLE_FILTER_VALUE' LIMIT 1");
$_realtime_article_filter_value_db = xtc_db_fetch_array($_realtime_article_filter_value_query); // false if 'MODULE_IDEALO_REALTIME_ARTICLE_FILTER' doesn't exist


/*
 * cat filter value
 */

// is cat_filter_value set? 
if( isset($_POST['cat_filter_value'])) {
	// does a dataset exist?
	if( $_realtime_cat_filter_value_db !== false ) {
		// update value if $_POST['cat_filter_value'] != $_realtime_quoting_db
		if( $_POST['cat_filter_value'] != $_realtime_cat_filter_value_db['configuration_value'] ) {
			xtc_db_query("update " . TABLE_CONFIGURATION . "
					      set configuration_value = '" . $_POST['cat_filter_value'] . "'
					      where configuration_key = 'MODULE_IDEALO_REALTIME_CAT_FILTER_VALUE'");
		}
	} else {
		// insert data
		xtc_db_query("insert into " . TABLE_CONFIGURATION . "
					  (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
					  values ('MODULE_IDEALO_REALTIME_CAT_FILTER_VALUE', '" . $_POST['cat_filter_value'] . "', 6, 1, '', now()) ");
	}

	$_realtime_cat_filter_value = stripcslashes($_POST['cat_filter_value']);
} else {
	$_realtime_cat_filter_value = "";
}


/*
 * cat filter
 */

// is cat_filter set? 
if( isset($_POST['cat_filter'])) {
	// does a dataset exist?
	if( $_realtime_cat_filter_db !== false ) {
		// update value if $_POST['cat_filter'] != $_realtime_quoting_db
		if( $_POST['cat_filter'] != $_realtime_cat_filter_db['configuration_value'] ) {
			xtc_db_query("update " . TABLE_CONFIGURATION . "
					      set configuration_value = '" . $_POST['cat_filter'] . "'
					      where configuration_key = 'MODULE_IDEALO_REALTIME_CAT_FILTER'");
		}
	} else {
		// insert data
		xtc_db_query("insert into " . TABLE_CONFIGURATION . "
					  (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
					  values ('MODULE_IDEALO_REALTIME_CAT_FILTER', '" . $_POST['cat_filter'] . "', 6, 1, '', now()) ");
	}

	$_realtime_cat_filter = stripcslashes($_POST['cat_filter']);
} else {
	$_realtime_cat_filter = "";
}


/*
 * brand filter value
 */

// is brand_filter_value set? 
if( isset($_POST['brand_filter_value'])) {
	// does a dataset exist?
	if( $_realtime_brand_filter_value_db !== false ) {
		// update value if $_POST['brand_filter_value'] != $_realtime_quoting_db
		if( $_POST['brand_filter_value'] != $_realtime_brand_filter_value_db['configuration_value'] ) {
			xtc_db_query("update " . TABLE_CONFIGURATION . "
					      set configuration_value = '" . $_POST['brand_filter_value'] . "'
					      where configuration_key = 'MODULE_IDEALO_REALTIME_BRAND_FILTER_VALUE'");
		}
	} else {
		// insert data
		xtc_db_query("insert into " . TABLE_CONFIGURATION . "
					  (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
					  values ('MODULE_IDEALO_REALTIME_BRAND_FILTER_VALUE', '" . $_POST['brand_filter_value'] . "', 6, 1, '', now()) ");
	}

	$_realtime_brand_filter_value = stripcslashes($_POST['brand_filter_value']);
} else {
	$_realtime_brand_filter_value = "";
}


/*
 * brand filter
 */

// is brand_filter set? 
if( isset($_POST['brand_filter'])) {
	// does a dataset exist?
	if( $_realtime_brand_filter_db !== false ) {
		// update value if $_POST['brand_filter'] != $_realtime_quoting_db
		if( $_POST['brand_filter'] != $_realtime_brand_filter_db['configuration_value'] ) {
			xtc_db_query("update " . TABLE_CONFIGURATION . "
					      set configuration_value = '" . $_POST['brand_filter'] . "'
					      where configuration_key = 'MODULE_IDEALO_REALTIME_BRAND_FILTER'");
		}
	} else {
		// insert data
		xtc_db_query("insert into " . TABLE_CONFIGURATION . "
					  (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
					  values ('MODULE_IDEALO_REALTIME_BRAND_FILTER', '" . $_POST['brand_filter'] . "', 6, 1, '', now()) ");
	}

	$_realtime_brand_filter = stripcslashes($_POST['brand_filter']);
} else {
	$_realtime_brand_filter = "";
}


/*
 * article filter value
 */

// is article_filter_value set? 
if( isset($_POST['article_filter_value'])) {
	// does a dataset exist?
	if( $_realtime_article_filter_value_db !== false ) {
		// update value if $_POST['article_filter_value'] != $_realtime_quoting_db
		if( $_POST['article_filter_value'] != $_realtime_article_filter_value_db['configuration_value'] ) {
			xtc_db_query("update " . TABLE_CONFIGURATION . "
					      set configuration_value = '" . $_POST['article_filter_value'] . "'
					      where configuration_key = 'MODULE_IDEALO_REALTIME_ARTICLE_FILTER_VALUE'");
		}
	} else {
		
		// insert data
		xtc_db_query("insert into " . TABLE_CONFIGURATION . "
					  (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
					  values ('MODULE_IDEALO_REALTIME_ARTICLE_FILTER_VALUE', '" . $_POST['article_filter_value'] . "', 6, 1, '', now()) ");
	}

	$_realtime_article_filter_value = stripcslashes($_POST['article_filter_value']);
} else {
	$_realtime_article_filter_value = "";
}


/*
 * article filter
 */

// is article_filter set? 
if( isset($_POST['article_filter'])) {
	// does a dataset exist?
	if( $_realtime_article_filter_db !== false ) {
		// update value if $_POST['article_filter'] != $_realtime_quoting_db
		if( $_POST['article_filter'] != $_realtime_article_filter_db['configuration_value'] ) {
			xtc_db_query("update " . TABLE_CONFIGURATION . "
					      set configuration_value = '" . $_POST['article_filter'] . "'
					      where configuration_key = 'MODULE_IDEALO_REALTIME_ARTICLE_FILTER'");
		}
	} else {
		// insert data
		xtc_db_query("insert into " . TABLE_CONFIGURATION . "
					  (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
					  values ('MODULE_IDEALO_REALTIME_ARTICLE_FILTER', '" . $_POST['article_filter'] . "', 6, 1, '', now()) ");
	}

	$_realtime_article_filter = stripcslashes($_POST['article_filter']);
} else {
	$_realtime_article_filter = "";
}



/*
 * certificate
 */

// is certificate set? 
if( isset( $_POST [ 'certificate' ] ) ) {
	// does a dataset exist?
	if( $certificate_db !== false ) {
		// update value if $_POST['testmode'] != $quoting_db
		if( $_POST [ 'certificate' ] != $certificate_db [ 'configuration_value' ] ) {
			xtc_db_query( "update " . TABLE_CONFIGURATION . "
					      set configuration_value = '" . $_POST [ 'certificate' ] . "'
					      where configuration_key = 'MODULE_IDEALO_REALTIME_CERTIFICATE'" );
		}
	} else {
		// insert data
		xtc_db_query( "insert into " . TABLE_CONFIGURATION . "
					  (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
					  values ('MODULE_IDEALO_REALTIME_CERTIFICATE', '" . $_POST [ 'certificate' ] . "', 6, 1, '', now()) ");
	}

	$certificate = stripcslashes( $_POST [ 'certificate' ] );
} else {
	$certificate = "";
}


/*
 * testmode
 */

// is testmode_active set? 
if( isset($_POST['testmode'])) {
	// does a dataset exist?
	if( $testmode_db !== false ) {
		// update value if $_POST['testmode'] != $quoting_db
		if( $_POST['testmode'] != $testmode_db['configuration_value'] ) {
			xtc_db_query("update " . TABLE_CONFIGURATION . "
					      set configuration_value = '" . $_POST['testmode'] . "'
					      where configuration_key = 'MODULE_IDEALO_REALTIME_TESTMODE'");
		}
	} else {
		// insert data
		xtc_db_query("insert into " . TABLE_CONFIGURATION . "
					  (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
					  values ('MODULE_IDEALO_REALTIME_TESTMODE', '" . $_POST['testmode'] . "', 6, 1, '', now()) ");
	}

	$testmode = stripcslashes($_POST['testmode']);
} else {
	$testmode = "";
}

/*
 * campaign
 */

// is campaign set? 
if( isset($_POST['campaign'])) {
	// does a dataset exist?
	if( $campaign_db !== false ) {
		// update value if $_POST['campaign'] != $campaign_db
		if( $_POST['campaign'] != $campaign_db['configuration_value'] ) {
			xtc_db_query("update " . TABLE_CONFIGURATION . "
					      set configuration_value = '" . $_POST['campaign'] . "'
					      where configuration_key = 'MODULE_IDEALO_CAMPAIGN'");
		}
	} else {
		// insert data
		xtc_db_query("insert into " . TABLE_CONFIGURATION . "
					  (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
					  values ('MODULE_IDEALO_CAMPAIGN', '" . $_POST['campaign'] . "', 6, 1, '', now()) ");
	}

	$campaign = stripcslashes($_POST['campaign']);
} else {
	$campaign = "";
}


/*
 * URL 
 */
// is a specific url set?
if( isset($_POST['url_input'])) {
	// db does not care for extra slashes
	$dbValue = $_POST['url_input'];

	// check if slashes need to be stripped
	if( $_POST['url_input'] != stripslashes($_POST['url_input']) ) {
		$_POST['url_input'] = stripslashes($_POST['url_input']);
	}

	// hack
	if( $_POST['url_input'] == '\t' ) {
		$_POST['url_input'] = "\t";
	}

	// does a dataset exist?
	if( $url_db !== false ) {

		// update value if $_POST['url_input'] != $url_db
		if( $_POST['url_input'] != $url_db['configuration_value'] ) {
			xtc_db_query("update " . TABLE_CONFIGURATION . "
					      set configuration_value = '" . $dbValue . "'
					      where configuration_key = 'MODULE_IDEALO_REALTIME_URL'");
		}
	} else {
		// insert data
		xtc_db_query("insert into " . TABLE_CONFIGURATION . "
					  (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
					  values ('MODULE_IDEALO_REALTIME_URL', '" . $dbValue . "', 6, 1, '', now()) ");
	}

	$url = $_POST['url_input'];

} else {
	// if nothing is entered by the admin: https://partner.idealo.de/partnerWs/ as default
	$url = "https://partner.idealo.de/partnerWs/";
}


/*
 * shop_id 
 */
// is a specific shop_id set?
if( isset($_POST['shop_id_input'])){// db does not care for extra slashes
	$dbValue = $_POST['shop_id_input'];

	// check if slashes need to be stripped
	if( $_POST['shop_id_input'] != stripslashes($_POST['shop_id_input']) ) {
		$_POST['shop_id_input'] = stripslashes($_POST['shop_id_input']);
	}

	// hack
	if( $_POST['shop_id_input'] == '\t' ) {
		$_POST['shop_id_input'] = "\t";
	}

	// does a dataset exist?
	if( $shop_id_db !== false ) {

		// update value if $_POST['shop_id_input'] != $shop_id_db
		if( $_POST['shop_id_input'] != $shop_id_db['configuration_value'] ) {
			xtc_db_query("update " . TABLE_CONFIGURATION . "
					      set configuration_value = '" . $dbValue . "'
					      where configuration_key = 'MODULE_IDEALO_REALTIME_SHOP_ID'");
		}
	} else {
		// insert data
		xtc_db_query("insert into " . TABLE_CONFIGURATION . "
					  (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
					  values ('MODULE_IDEALO_REALTIME_SHOP_ID', '" . $dbValue . "', 6, 1, '', now()) ");
	}

	$shop_id = $_POST['shop_id_input'];

} else {
	// if nothing is entered by the admin: $shop_id gets 272856 as default
	$shop_id = "272856";
}


/*
 * PASSWORD
 */

// is a specific password character set?define('IDEALO_REALTIME_CAMPAIGN','?ref=idealo');
if( isset($_POST['password_input'])) {
	// does a dataset exist?
	if( $password_db !== false ) {

		// update value if $_POST['password_input'] != $password_db
		if( $_POST['password_input'] != $password_db['configuration_value'] ) {
			xtc_db_query("update " . TABLE_CONFIGURATION . "
					      set configuration_value = '" . $_POST['password_input'] . "'
					      where configuration_key = 'MODULE_IDEALO_REALTIME_PASSWORD'");
		}
	} else {
		// insert data
		xtc_db_query("insert into " . TABLE_CONFIGURATION . "
					  (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
					  values ('MODULE_IDEALO_REALTIME_PASSWORD', '" . $_POST['password_input'] . "', 6, 1, '', now()) ");
	}

	$password = stripcslashes($_POST['password_input']);
} else {
	// if nothing is entered by the admin: testaccount is disabled
	$password = "testaccount";
}



/*
 * pagesize
 */

// is a specific pagesize character set?
if( isset($_POST['pagesize_input'])) {	
	// does a dataset exist?
	if( $pagesize_db !== false ) {

		// update value if $_POST['pagesize_input'] != $pagesize_db
		if( $_POST['pagesize_input'] != $pagesize_db['configuration_value'] ) {
			xtc_db_query("update " . TABLE_CONFIGURATION . "
					      set configuration_value = '" . $_POST['pagesize_input'] . "'
					      where configuration_key = 'MODULE_IDEALO_REALTIME_PAGESIZE'");
		}
	} else {
		// insert data
		xtc_db_query("insert into " . TABLE_CONFIGURATION . "
					  (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
					  values ('MODULE_IDEALO_REALTIME_PAGESIZE', '" . $_POST['pagesize_input'] . "', 6, 1, '', now()) ");
	}

	$pagesize = stripcslashes($_POST['pagesize_input']);
} else {
	// if nothing is entered by the admin: $pagesize 100 is disabled
	$pagesize = "100";
}


/*
 * SHIPPING COMMENT 
 */

// is shipping comment set?
// do not exceed COMMENTLENGTH
if( isset( $_POST['shippingcomment_input']) && ( strlen($_POST['shippingcomment_input']) <= COMMENTLENGTH ) ) {

	// does a dataset exist?
	if( $shipping_comment_db !== false ) {

		// update value if $_POST['freeshippinglimit_input'] != $freeshipping_comment_db
		if( $_POST['shippingcomment_input'] != $shipping_comment_db['configuration_value'] ) {
			xtc_db_query("update " . TABLE_CONFIGURATION . "
					      set configuration_value = '" . $_POST['shippingcomment_input'] . "'
					      where configuration_key = 'MODULE_IDEALO_REALTIME_SHIPPINGCOMMENT'");
		}
	} else {
		// insert data
		xtc_db_query("insert into " . TABLE_CONFIGURATION . "
					  (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
					  values ('MODULE_IDEALO_REALTIME_SHIPPINGCOMMENT', '" . $_POST['shippingcomment_input'] . "', 6, 1, '', now()) ");
	}

	$shipping_comment_input = stripslashes($_POST['shippingcomment_input']);

} else {
	$shipping_comment_input = "";
}  


?>
