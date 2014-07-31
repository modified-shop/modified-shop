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

// check if $key is already in db
function getConfigurationValue($key){
    $value_query = xtc_db_query("SELECT configuration_value
                                 FROM " . TABLE_CONFIGURATION . "
                                 WHERE configuration_key = '" . $key . "'
                                 LIMIT 1");
    return xtc_db_fetch_array($value_query);// false if $key doesn't exist
}

$_csv_variant_db = getConfigurationValue('MODULE_IDEALO_CSV_VARIANT');
$_csv_warehouse_db = getConfigurationValue('MODULE_IDEALO_CSV_WAREHOUSE');
$_csv_campaign_db = getConfigurationValue('MODULE_IDEALO_CSV_CAMPAIGN');
$_csv_shipping_comment_db = getConfigurationValue('MODULE_IDEALO_CSV_SHIPPINGCOMMENT');
$_csv_separator_db = getConfigurationValue('MODULE_IDEALO_CSV_SEPARATOR');
$_csv_quoting_db = getConfigurationValue('MODULE_IDEALO_CSV_QUOTING');
$_csv_cat_filter_db = getConfigurationValue('MODULE_IDEALO_CSV_CAT_FILTER');
$_csv_cat_filter_value_db = getConfigurationValue('MODULE_IDEALO_CSV_CAT_FILTER_VALUE');
$_csv_brand_filter_db = getConfigurationValue('MODULE_IDEALO_CSV_BRAND_FILTER');
$_csv_brand_filter_value_db = getConfigurationValue('MODULE_IDEALO_CSV_BRAND_FILTER_VALUE');
$_csv_article_filter_db = getConfigurationValue('MODULE_IDEALO_CSV_ARTICLE_FILTER');
$_csv_article_filter_value_db = getConfigurationValue('MODULE_IDEALO_CSV_ARTICLE_FILTER_VALUE');


/*
 * variantexport value
 */

// is variantexport set?
if(isset($_POST['variantexport'])){
    // does a dataset exist?
    if($_csv_variant_db !== false){
        // update value if $_POST['cat_filter_value'] != $_csv_filter_db
        if($_POST['variantexport'] != $_csv_variant_db['configuration_value']){
            xtc_db_query("update " . TABLE_CONFIGURATION . "
					      set configuration_value = '" . $_POST['variantexport'] . "'
					      where configuration_key = 'MODULE_IDEALO_CSV_VARIANT'");
        }
    }else{
        // insert data
        xtc_db_query("insert into " . TABLE_CONFIGURATION . "
					  (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
					  values ('MODULE_IDEALO_CSV_VARIANT', '" . $_POST['variantexport'] . "', 6, 1, '', now()) ");
    }

    $_csv_variant_value = stripcslashes($_POST['variantexport']);
}else{
    $_csv_variant_value = "";
}


/*
 * warehouse value
 */

// is warehouse set?
if(isset($_POST['warehouse'])){
    // does a dataset exist?
    if($_csv_warehouse_db !== false){
        // update value if $_POST['cat_filter_value'] != $_csv_filter_db
        if( $_POST['warehouse'] != $_csv_warehouse_db['configuration_value']){
            xtc_db_query("update " . TABLE_CONFIGURATION . "
					      set configuration_value = '" . $_POST['warehouse'] . "'
					      where configuration_key = 'MODULE_IDEALO_CSV_WAREHOUSE'");
        }
    }else{
        // insert data
        xtc_db_query("insert into " . TABLE_CONFIGURATION . "
					  (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
					  values ('MODULE_IDEALO_CSV_WAREHOUSE', '" . $_POST['warehouse'] . "', 6, 1, '', now()) ");
    }

    $_csv_warehouse_value = stripcslashes($_POST['warehouse']);
}else{
    $_csv_warehouse_value = "";
}


/*
 * cat filter value
 */

// is cat_filter_value set? 
if( isset($_POST['cat_filter_value'])) {
	// does a dataset exist?
	if( $_csv_cat_filter_value_db !== false ) {
		// update value if $_POST['cat_filter_value'] != $_csv_filter_db
		if( $_POST['cat_filter_value'] != $_csv_cat_filter_value_db['configuration_value'] ) {
			xtc_db_query("update " . TABLE_CONFIGURATION . "
					      set configuration_value = '" . $_POST['cat_filter_value'] . "'
					      where configuration_key = 'MODULE_IDEALO_CSV_CAT_FILTER_VALUE'");
		}
	} else {
		// insert data
		xtc_db_query("insert into " . TABLE_CONFIGURATION . "
					  (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
					  values ('MODULE_IDEALO_CSV_CAT_FILTER_VALUE', '" . $_POST['cat_filter_value'] . "', 6, 1, '', now()) ");
	}

	$_csv_cat_filter_value = stripcslashes($_POST['cat_filter_value']);
} else {
	$_csv_cat_filter_value = "";
}


/*
 * cat filter
 */

// is cat_filter set? 
if( isset($_POST['cat_filter'])) {
	// does a dataset exist?
	if( $_csv_cat_filter_db !== false ) {
		// update value if $_POST['cat_filter'] != $_csv_quoting_db
		if( $_POST['cat_filter'] != $_csv_cat_filter_db['configuration_value'] ) {
			xtc_db_query("update " . TABLE_CONFIGURATION . "
					      set configuration_value = '" . $_POST['cat_filter'] . "'
					      where configuration_key = 'MODULE_IDEALO_CSV_CAT_FILTER'");
		}
	} else {
		// insert data
		xtc_db_query("insert into " . TABLE_CONFIGURATION . "
					  (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
					  values ('MODULE_IDEALO_CSV_CAT_FILTER', '" . $_POST['cat_filter'] . "', 6, 1, '', now()) ");
	}

	$_csv_cat_filter = stripcslashes($_POST['cat_filter']);
} else {
	$_csv_cat_filter = "";
}


/*
 * brand filter value
 */

// is brand_filter_value set? 
if( isset($_POST['brand_filter_value'])) {
	// does a dataset exist?
	if( $_csv_brand_filter_value_db !== false ) {
		// update value if $_POST['brand_filter_value'] != $_csv_quoting_db
		if( $_POST['brand_filter_value'] != $_csv_brand_filter_value_db['configuration_value'] ) {
			xtc_db_query("update " . TABLE_CONFIGURATION . "
					      set configuration_value = '" . $_POST['brand_filter_value'] . "'
					      where configuration_key = 'MODULE_IDEALO_CSV_BRAND_FILTER_VALUE'");
		}
	} else {
		// insert data
		xtc_db_query("insert into " . TABLE_CONFIGURATION . "
					  (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
					  values ('MODULE_IDEALO_CSV_BRAND_FILTER_VALUE', '" . $_POST['brand_filter_value'] . "', 6, 1, '', now()) ");
	}

	$_csv_brand_filter_value = stripcslashes($_POST['brand_filter_value']);
} else {
	$_csv_brand_filter_value = "";
}


/*
 * brand filter
 */

// is brand_filter set? 
if( isset($_POST['brand_filter'])) {
	// does a dataset exist?
	if( $_csv_brand_filter_db !== false ) {
		// update value if $_POST['brand_filter'] != $_csv_quoting_db
		if( $_POST['brand_filter'] != $_csv_brand_filter_db['configuration_value'] ) {
			xtc_db_query("update " . TABLE_CONFIGURATION . "
					      set configuration_value = '" . $_POST['brand_filter'] . "'
					      where configuration_key = 'MODULE_IDEALO_CSV_BRAND_FILTER'");
		}
	} else {
		// insert data
		xtc_db_query("insert into " . TABLE_CONFIGURATION . "
					  (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
					  values ('MODULE_IDEALO_CSV_BRAND_FILTER', '" . $_POST['brand_filter'] . "', 6, 1, '', now()) ");
	}

	$_csv_brand_filter = stripcslashes($_POST['brand_filter']);
} else {
	$_csv_brand_filter = "";
}


/*
 * article filter value
 */

// is article_filter_value set? 
if( isset($_POST['article_filter_value'])) {
	// does a dataset exist?
	if( $_csv_article_filter_value_db !== false ) {
		// update value if $_POST['article_filter_value'] != $_csv_quoting_db
		if( $_POST['article_filter_value'] != $_csv_article_filter_value_db['configuration_value'] ) {
			xtc_db_query("update " . TABLE_CONFIGURATION . "
					      set configuration_value = '" . $_POST['article_filter_value'] . "'
					      where configuration_key = 'MODULE_IDEALO_CSV_ARTICLE_FILTER_VALUE'");
		}
	} else {
		
		// insert data
		xtc_db_query("insert into " . TABLE_CONFIGURATION . "
					  (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
					  values ('MODULE_IDEALO_CSV_ARTICLE_FILTER_VALUE', '" . $_POST['article_filter_value'] . "', 6, 1, '', now()) ");
	}

	$_csv_article_filter_value = stripcslashes($_POST['article_filter_value']);
} else {
	$_csv_article_filter_value = "";
}


/*
 * article filter
 */

// is article_filter set? 
if( isset($_POST['article_filter'])) {
	// does a dataset exist?
	if( $_csv_article_filter_db !== false ) {
		// update value if $_POST['article_filter'] != $_csv_quoting_db
		if( $_POST['article_filter'] != $_csv_article_filter_db['configuration_value'] ) {
			xtc_db_query("update " . TABLE_CONFIGURATION . "
					      set configuration_value = '" . $_POST['article_filter'] . "'
					      where configuration_key = 'MODULE_IDEALO_CSV_ARTICLE_FILTER'");
		}
	} else {
		// insert data
		xtc_db_query("insert into " . TABLE_CONFIGURATION . "
					  (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
					  values ('MODULE_IDEALO_CSV_ARTICLE_FILTER', '" . $_POST['article_filter'] . "', 6, 1, '', now()) ");
	}

	$_csv_article_filter = stripcslashes($_POST['article_filter']);
} else {
	$_csv_article_filter = "";
}


/*
 * quoting
 */

// is quoting set? 
if( isset($_POST['idealo_csv_quoting_input'])) {
	// does a dataset exist?
	if( $_csv_quoting_db !== false ) {
		// update value if $_POST['quoting'] != $_csv_quoting_db
		if( $_POST['idealo_csv_quoting_input'] != $_csv_quoting_db['configuration_value'] ) {
			xtc_db_query("update " . TABLE_CONFIGURATION . "
					      set configuration_value = '" . $_POST['idealo_csv_quoting_input'] . "'
					      where configuration_key = 'MODULE_IDEALO_CSV_QUOTING'");
		}
	} else {
		// insert data
		xtc_db_query("insert into " . TABLE_CONFIGURATION . "
					  (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
					  values ('MODULE_IDEALO_CVS_QUOTING', '" . $_POST['idealo_csv_quoting_input'] . "', 6, 1, '', now()) ");
	}

	$_csv_quoting = stripcslashes($_POST['idealo_csv_quoting_input']);
} else {
	$_csv_quoting = "";
}


/*
 * separator
 */

// is separator set? 
if( isset($_POST['idealo_csv_separator_input'])) {
	
	// does a dataset exist?
	if( $_csv_separator_db !== false ) {
		// update value if $_POST['separator'] != $_csv_separator_db
		if( $_POST['idealo_csv_separator_input'] != $_csv_separator_db['configuration_value'] ) {
			xtc_db_query("update " . TABLE_CONFIGURATION . "
					      set configuration_value = '" . $_POST['idealo_csv_separator_input'] . "'
					      where configuration_key = 'MODULE_IDEALO_CSV_SEPARATOR'");
		}
	} else {
		// insert data
		xtc_db_query("insert into " . TABLE_CONFIGURATION . "
					  (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
					  values ('MODULE_IDEALO_CSV_SEPARATOR', '" . $_POST['idealo_csv_separator_input'] . "', 6, 1, '', now()) ");
	}

	$_csv_separator = stripcslashes($_POST['idealo_csv_separator_input']);
} else {
	$_csv_separator = "";
}

/*
 * campaign
 */

// is campaign set? 
if( isset($_POST['campaign'])) {
	// does a dataset exist?
	if( $_csv_campaign_db !== false ) {
		// update value if $_POST['campaign'] != $_csv_campaign_db
		if( $_POST['campaign'] != $_csv_campaign_db['configuration_value'] ) {
			xtc_db_query("update " . TABLE_CONFIGURATION . "
					      set configuration_value = '" . $_POST['campaign'] . "'
					      where configuration_key = 'MODULE_IDEALO_CSV_CAMPAIGN'");
		}
	} else {
		// insert data
		xtc_db_query("insert into " . TABLE_CONFIGURATION . "
					  (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
					  values ('MODULE_IDEALO_CSV_CAMPAIGN', '" . $_POST['campaign'] . "', 6, 1, '', now()) ");
	}

	$_csv_campaign = stripcslashes($_POST['campaign']);
} else {
	$_csv_campaign = "";
}


/*
 * SHIPPING COMMENT 
 */

// is shipping comment set?
// do not exceed COMMENTLENGTH
if( isset( $_POST['shippingcomment_input']) && ( strlen($_csv__POST['shippingcomment_input']) <= COMMENTLENGTH ) ) {

	// does a dataset exist?
	if( $_csv_shipping_comment_db !== false ) {

		// update value if $_POST['freeshippinglimit_input'] != $_csv_freeshipping_comment_db
		if( $_POST['shippingcomment_input'] != $_csv_shipping_comment_db['configuration_value'] ) {
			xtc_db_query("update " . TABLE_CONFIGURATION . "
					      set configuration_value = '" . $_POST['shippingcomment_input'] . "'
					      where configuration_key = 'MODULE_IDEALO_CSV_SHIPPINGCOMMENT'");
		}
	} else {
		// insert data
		xtc_db_query("insert into " . TABLE_CONFIGURATION . "
					  (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
					  values ('MODULE_IDEALO_CSV_SHIPPINGCOMMENT', '" . $_POST['shippingcomment_input'] . "', 6, 1, '', now()) ");
	}

	$_csv_shipping_comment_input = stripslashes($_POST['shippingcomment_input']);

} else {
	$_csv_shipping_comment_input = "";
}  


?>
