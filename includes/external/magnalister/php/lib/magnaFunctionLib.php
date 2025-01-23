<?php
/**
 * 888888ba                 dP  .88888.                    dP
 * 88    `8b                88 d8'   `88                   88
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b.
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P'
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * $Id$
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');


/******************************************************************************\
 *                        Magnalister Specific Functions                      *
\******************************************************************************/

function magnaContribVerify($hookname, $version) {
	$path = DIR_MAGNALISTER_FS_CONTRIBS.$hookname.'_'.$version.'.php';
	if (($path[0] != '/') && !preg_match('/^[a-zA-Z]:\\\/', $path)) {
		if (defined('DIR_FS_ADMIN')) {
			$path = rtrim(DIR_FS_ADMIN, '/').'/'.$path;
		} else {
			return false;
		}
	}
	#echo var_dump_pre($path);
	return file_exists($path) ? $path : false;
}

function getCurrentModulePage() {
	global $_modules, $_MagnaSession;
	$modulePages = $_modules[$_MagnaSession['currentPlatform']]['pages'];

	if (isset($_modules[$_MagnaSession['currentPlatform']]['settings']['defaultpage'])) {
		$page = $_modules[$_MagnaSession['currentPlatform']]['settings']['defaultpage'];
	} else {
		$page = array_first(array_keys($modulePages));
	}
	if (array_key_exists('mode', $_GET) && array_key_exists($_GET['mode'], $modulePages)) {
		$page = $_GET['mode'];
	}
	return $page;
}

function html_image($image, $alt = "", $width = "", $height = "") {
	return '<img src="'.$image.'"'.(!empty($alt) ? (' alt="'.$alt.'" title="'.$alt.'"') : '').(!empty($width) ? (' width="'.$width.'"') : '').(!empty($height) ? (' height="'.$height.'"') : '').'>';
}

function generateProductCategoryThumb($fName, $w, $h, $noImageIcon = false) {
    // we will get the umlauts in image as html code from gambio shop so convert them
    $fName = html_entity_decode($fName);
	$retina = ML_RETINA_DISPLY;

	if (!function_exists('imagecreatetruecolor')) {
		return html_image(DIR_MAGNALISTER_WS_RESOURCE.'noimage.png', 'GD Lib missing', $w, $w);
	}
	$imagePath = '';

	if (!eempty(trim($fName)) && !$noImageIcon) {
		if (file_exists(SHOP_FS_PRODUCT_THUMBNAILS.$fName)) {
			$imagePath = SHOP_FS_PRODUCT_THUMBNAILS;
			$cachePath = 'product_';
		} else if (file_exists(SHOP_FS_PRODUCT_IMAGES.$fName)) {
			$imagePath = SHOP_FS_PRODUCT_IMAGES;
			$cachePath = 'product_';
		} else if (file_exists(SHOP_FS_CATEGORY_IMAGES.$fName)) {
			$imagePath = SHOP_FS_CATEGORY_IMAGES;
			$cachePath = 'category_';
		}
	} else if ($noImageIcon) {
		$imagePath = DIR_MAGNALISTER_FS_RESOURCE;
		$cachePath = 'resource_';
	}
	if ($imagePath == '') {
		return generateProductCategoryThumb('noimage.png', $w, $h, true);
	}
	$newfName = basename($fName);
	if (($dotpos = strrpos($newfName, '.')) > 0) {
		$newfName = str_split($newfName, $dotpos);
		$newfName = $newfName[0];
	}

	$newfName .= '_'.$w.'x'.$h.($retina ? '@2x' : '').'.jpg';
	if (!is_dir(DIR_MAGNALISTER_FS_IMAGECACHE)) {
		if (MAGNA_SAFE_MODE) {
			return html_image(DIR_MAGNALISTER_WS_RESOURCE.'noimage.png', 'Safe Mode enabled', $w, $w);
		}
		mkdir(DIR_MAGNALISTER_FS_IMAGECACHE, 0775, true);
	}
	$destFile = DIR_MAGNALISTER_FS_IMAGECACHE.$cachePath.$newfName;
	if (file_exists($destFile)) {
		list($width, $height) = getimagesize($destFile);
		if ((($w * ($retina ? 2 : 1)) == $width) || (($h * ($retina ? 2 : 1)) == $height)) {
			return html_image(DIR_MAGNALISTER_WS_IMAGECACHE.$cachePath.$newfName, $fName, $width * ($retina ? 0.5 : 1), $height * ($retina ? 0.5 : 1));
		}
	}

	$success = resizeImage($imagePath.$fName, $w * ($retina ? 2 : 1), $h * ($retina ? 2 : 1), $destFile);
	if (!$success && !$noImageIcon) {
		return generateProductCategoryThumb('noimage.png', $w, $h, true);
	} else if (!$success && $noImageIcon) {
		return 'X';
	}
	list($width, $height) = getimagesize($destFile);
	return html_image(DIR_MAGNALISTER_WS_IMAGECACHE.$cachePath.$newfName, $fName, $width * ($retina ? 0.5 : 1), $height * ($retina ? 0.5 : 1));
}

function parseShippingStatusName($status, $fallback) {
	/* Extract largest number */
	$largestNumber = 0;
	if (preg_match_all('/([0-9]+)/', $status, $matches)) {
		$numbers = $matches[0];
		foreach ($numbers as $number) {
			if ($number > $largestNumber) {
				$largestNumber = $number;
			}
		}
	}
	if (preg_match('/(Day|Days|Tag|Tage)/i', $status)) {
		return $largestNumber;
	}
	if (preg_match('/(Week|Weeks|Woche|Wochen)/i', $status)) {
		return $largestNumber * 7;
	}
	if (preg_match('/(Month|Months|Monat|Monate)/i', $status)) {
		return $largestNumber * 30;
	}

	return $fallback;
}

function shopAdminDiePage($content) {
	global $_MagnaSession;
	$_MagnaSession['currentPlatform'] = '';
	include_once(DIR_MAGNALISTER_FS_INCLUDES.'admin_view_top.php');
	$content = func_get_args();
	$content = $content[0];
	echo $content;
	include_once(DIR_MAGNALISTER_FS_INCLUDES.'admin_view_bottom.php');
	include_once(DIR_WS_INCLUDES . 'application_bottom.php');
	exit();
}

function sanitizeProductDescription($str, $allowable_tags = '', $allowable_attributes = '') {
	$str = !magnalisterIsUTF8($str) ? utf8_encode($str) : $str;

	$str = stripEvilBlockTags($str);

	/* Convert Gambio-Tabs to H1-Headlines */
	if (getDBConfigValue('gambio.tabs.display', 0, 'h1') == 'none') {
		if (strpos($str, '[TAB:')) {
			$str = substr($str, 0, strpos($str, '[TAB:'));
		}
	} else {
		$str = preg_replace('/\[TAB:([^\]]*)\]/', '<h1>${1}</h1>', $str);
	}

	if (stripos($allowable_tags, '<br') === false) {
		/* Convert (x)html breaks with or without atrributes to newlines. */
		$str = preg_replace('/\<br(\s*)?([[:alpha:]]*=".*")?(\s*)?\/?\>/i', "\n", $str);
	} else {
		$str = str_replace('<br/>', '<br />', $str);
	}
	$str = preg_replace("/<([^([:alpha:]|\/)])/", '&lt;\\1', $str);
	$str = strip_tags_attributes($str, $allowable_tags, $allowable_attributes);

	if ($allowable_tags == '') {
		$str = str_replace(array("\n", "\t", "\v", "|"), " ", $str);
		$str = str_replace(array("&quot;", "&qout;"), " \"", $str);

		$str = str_replace(array("&nbsp;"), " ", $str);

		/* Converts all html entities to "real" characters */
		$str = html_entity_decode($str);
		$str = str_replace(array(";", "'"), ", ", $str);
	}

	/* Strip excess whitespace */
	$str = preg_replace('/\s\s+/', ' ', trim($str));
	return $str;
}

function magnaGetAddressFormatID($country_id) {
	$ret = (int) MagnaDB::gi()->fetchOne(
		"SELECT address_format_id FROM " . TABLE_COUNTRIES . " WHERE countries_id = '" . $country_id . "'"
	);
	if ($ret < 1) {
		return '1';
	}
	return $ret;
}

function magnaGetCountryFromISOCode($code) {
	$countryRes = MagnaDB::gi()->fetchRow('
		SELECT countries_id, countries_name, countries_iso_code_2
		  FROM '.TABLE_COUNTRIES.'
		 WHERE countries_iso_code_2=\''.$code.'\'
		 LIMIT 1
	');
	if ($countryRes === false) {
		$countryRes = array (
			'countries_id' => 0,
			'countries_name' => $code,
		);
	}
	return $countryRes;
}

function substituteTemplate($tmplStr, $substitution) {
	$find = array();
	$replace = array();

	# Sonderfall: PICTURE1, ersetze durch URL wenn img oder a href Tag angegeben, sonst durch vollst. img Tag.
	if (    isset($substitution['#PICTURE1#'])
		&& !empty($substitution['#PICTURE1#'])) {
		$tmplStr = str_replace(
			'#PICTURE1#',
			"<img src=\"".$substitution['#PICTURE1#']."\" style=\"border:0;\" alt=\"\" title=\"\" />",
			preg_replace(
				'/(src|SRC|href|HREF|rev|REV)(\s*=\s*)(\'|")(#PICTURE1#)/',
				'\1\2\3'.$substitution['#PICTURE1#'],
				preg_replace(
                    '/(url|URL)(\s*)(\()([\'"]{0,1})#PICTURE1#([\'"]{0,1})(\))/', 
                    '\1\2\3\4'.$substitution['#PICTURE1#'].'\5\6', 
                    $tmplStr
                )
			)
		);
	}
	foreach ($substitution as $f => $r) {
		$find[] = $f;
		$replace[] = $r;
	}
	$str = str_replace($find, $replace, $tmplStr);

	# Bild 1 leer? entfernen
	$str = preg_replace('/<img[^>]*src=(""|\'\')[^>]*>/i', '', $str);

	# relative Pfade bei (nicht von uns eingesetzten) Bildern und Links ersetzen
	if (   ('none_none' != getDBConfigValue('general.editor',0,'tinyMCE'))
	    && (preg_match('/(src|SRC|href|HREF|rev|REV)(\s*=\s*)(\'|")(?!http|HTTP|mailto|javascript|data:image|#|\+|\'\+|"\+)/', $str))
	) {
		$str = preg_replace(
			'/(src|SRC|href|HREF|rev|REV)(\s*=\s*)(\'|")(?!http|HTTP|mailto|javascript|data:image|#|\+|\'\+|"\+)/',
			'\1\2\3'.HTTP_CATALOG_SERVER.DIR_WS_CATALOG.'\4',
			preg_replace(
				'/(src|SRC|href|HREF|rev|REV)(\s*=\s*)(\'|")(\/)/',
				'\1\2\3'.HTTP_CATALOG_SERVER.'\4',
				$str
			)
		);
	}
	return $str;
}

function magnaGetMarketplaceByID($mpID) {
	global $magnaConfig;
	$mpID = (string)$mpID;
	if (!ctype_digit((string)$mpID)) {
		if (MAGNA_DEBUG) {
			echo print_m(prepareErrorBacktrace(2));
		}
		trigger_error(__FUNCTION__.': Parameter $mpID ('.trim(var_dump_pre($mpID)).') must be numeric.');
	}
	if (!array_key_exists($mpID, $magnaConfig['maranon']['Marketplaces'])) {
		return false;
	}
	return $magnaConfig['maranon']['Marketplaces'][$mpID];
}

function magnaGetIDsByMarketplace($mp) {
	global $magnaConfig;

	if (!array_key_exists('maranon', $magnaConfig) ||
	    !array_key_exists('Marketplaces', $magnaConfig['maranon']) ||
	    empty($magnaConfig['maranon']['Marketplaces'])
	) {
		return false;
	}
	$ids = array();
	foreach ($magnaConfig['maranon']['Marketplaces'] as $mpID => $marketplace) {
		if ($marketplace == $mp) {
			$ids[] = $mpID;
		}
	}
	sort($ids, SORT_NUMERIC);
	return $ids;
}

function sendSaleConfirmationMail($mpID, $recipientAdress, $substitution) {
	global $magnaConfig;
	$marketplace = magnaGetMarketplaceByID($mpID);
	if ($marketplace === false) {
		return false;
	}

	if (is_array($substitution['#ORDERSUMMARY#']) && !empty($substitution['#ORDERSUMMARY#'])) {
		$mailOrderSummary = '
			<table class="ordersummary">
				<thead>
					<tr>
						<td class="qty">'.ML_GENERIC_QTY.'</td>
						<td class="name">'.ML_GENERIC_ITEM.'</td>
						<td class="price">'.ML_GENERIC_EACH.'</td>
						<td class="fprice">'.ML_GENERIC_TOTAL.'</td>
					</tr>
				</thead>
				<tbody>';
		$isOdd = false;
		foreach ($substitution['#ORDERSUMMARY#'] as $item) {
			$item['price'] = str_replace('&curren;', '&euro;', fixHTMLUTF8Entities($item['price']));
			$item['finalprice'] = str_replace('&curren;', '&euro;', fixHTMLUTF8Entities($item['finalprice']));
			$mailOrderSummary .= '
					<tr class="'.(($isOdd = !$isOdd) ? 'odd' : 'even').'">
						<td class="qty">'.$item['quantity'].'</td>
						<td class="name">'.fixHTMLUTF8Entities($item['name']).'</td>
						<td class="price">'.$item['price'].'</td>
						<td class="fprice">'.$item['finalprice'].'</td>
					</tr>';
		}
		$mailOrderSummary .= '
				</tbody>
			</table>';
		$substitution['#ORDERSUMMARY#'] = $mailOrderSummary;
	}
	$substitution['#ORIGINATOR#'] = getDBConfigValue($marketplace.'.mail.originator.name', $mpID);

	$content = stringToUTF8(substituteTemplate(getDBConfigValue($marketplace.'.mail.content', $mpID), $substitution));
	unset($substitution['#ORDERSUMMARY#']);
	$subject = stringToUTF8(substituteTemplate(getDBConfigValue($marketplace.'.mail.subject', $mpID), $substitution));
/*
	echo print_m(array(
		'SUBJECT' => $subject,
		'CONTENT' => $content,
	), 'mail');
	return true;
//*/
	$orignator = getDBConfigValue($marketplace.'.mail.originator.adress', $mpID);
	try {
		$result = MagnaConnector::gi()->submitRequest(array(
			'ACTION' => 'SendSaleConfirmationMail',
			'SUBSYSTEM' => 'Core',
			'RECIPIENTADRESS' => $recipientAdress,
			'ORIGINATORNAME' => fixHTMLUTF8Entities($substitution['#ORIGINATOR#']),
			'ORIGINATORADRESS' => $orignator,
			'SUBJECT' => fixHTMLUTF8Entities($subject),
			'CONTENT' => $content,
			'BCC' => (
				($recipientAdress == $orignator) ?
					false :	((getDBConfigValue($marketplace.'.mail.copy', $mpID) == 'true') ?
						true : false
					)
			)
		));
		if (MAGNA_DEBUG && array_key_exists('DEBUG', $result)) {
			echo print_m($result['DEBUG'], 'Debug');
		}
		return true;
	} catch (MagnaException $e) {
		return false;
	}
}

function sendTestMail($mpID) {
	global $_modules;

	require_once (DIR_MAGNALISTER_FS_INCLUDES.'lib/classes/SimplePrice.php');
	$simplePrice = new SimplePrice();
	$simplePrice->setCurrency(DEFAULT_CURRENCY);

	$marketplace = magnaGetMarketplaceByID($mpID);

	return sendSaleConfirmationMail(
		$mpID,
		getDBConfigValue($marketplace.'.mail.originator.adress', $mpID),
		array (
			'#FIRSTNAME#' => 'Max',
			'#LASTNAME#' => 'Mustermann',
			'#ORDERSUMMARY#' => array (
				array (
					'quantity' => 2,
					'name' => 'Lorem Ipsum - Das Buch',
					'price' => $simplePrice->setPrice(12.99)->format(),
					'finalprice' => $simplePrice->setPrice(12.99 * 2)->format(),
				),
				array (
					'quantity' => 1,
					'name' => 'Dolor Sit Amet - Das Nachschlagewerk',
					'price' => $simplePrice->setPrice(22.59)->format(),
					'finalprice' => $simplePrice->setPrice(22.59)->format(),
				)
			),
			'#MARKETPLACE#' => $_modules[$marketplace]['title'],
			'#SHOPURL#' => ($marketplace == 'amazon') ? '' : HTTP_SERVER
		)
	);
}

function delete_double_orders() {
	$orders_id_array = MagnaDB::gi()->fetchArray('
		SELECT COUNT(*) cnt, MAX(orders_id) max_orders_id
		  FROM '. TABLE_MAGNA_ORDERS .'
		 GROUP BY data, special
		 HAVING cnt > 1
		ORDER BY orders_id
	');
	$orders_id_list = '';
	foreach($orders_id_array as $current_order)
		$orders_id_list .= ', '.$current_order['max_orders_id'];
	$orders_id_list = trim($orders_id_list, ', ');
	if(empty($orders_id_list)) return true;
	$orders_tables = array( 
		TABLE_ORDERS,
		TABLE_ORDERS_STATUS_HISTORY,
		TABLE_ORDERS_TOTAL,
		TABLE_ORDERS_PRODUCTS,
		TABLE_ORDERS_PRODUCTS_ATTRIBUTES,
		TABLE_MAGNA_ORDERS
	);
	foreach($orders_tables as $t) {
		MagnaDB::gi()->query('
			DELETE FROM '. $t .'
				WHERE orders_id IN ('. $orders_id_list .')
		');
	}
	return true;
}

function delete_double_customers() {
	return true;
	$customers_id_array = MagnaDB::gi()->fetchArray('
		SELECT DISTINCT c2.customers_id customers_id, c2.customers_default_address_id address_book_id
		  FROM '.TABLE_CUSTOMERS.' c1, '.TABLE_CUSTOMERS.' c2
		 WHERE c1.customers_firstname = c2.customers_firstname
		        AND c1.customers_lastname=c2.customers_lastname
		        AND c1.customers_email_address = c2.customers_email_address
		        AND c2.customers_id > c1.customers_id
		ORDER BY c2.customers_id
	');
	$customers_id_list = '';
	$address_book_id_list = '';
	$customers_info_id_list = '';
	foreach ($customers_id_array as $current_customer) {
		$customers_id_list .= ', '.$current_customer['customers_id'];
		$address_book_id_list .= ', '.$current_customer['address_book_id'];
	}
	$customers_id_list = trim($customers_id_list, ', ');
	if (empty($customers_id_list)) {
		return true;
	}
	$address_book_id_list = trim($address_book_id_list, ', ');
	MagnaDB::gi()->query('DELETE FROM '.TABLE_CUSTOMERS.' WHERE customers_id in ('.$customers_id_list.')');
	MagnaDB::gi()->query('DELETE FROM '.TABLE_CUSTOMERS_INFO.' WHERE customers_info_id in ('.$customers_id_list.')');
	MagnaDB::gi()->query('DELETE FROM '.TABLE_ADDRESS_BOOK.' WHERE address_book_id in ('.$address_book_id_list.')');
	return true;

}

function delete_double_ot_lines() {
	// for performance reasons, take only the last 7 days + no more than 50 magnalister orders
	$iStartFrom = MagnaDB::gi()->fetchOne('SELECT MIN(orders_id) FROM '.TABLE_ORDERS.' 
		WHERE UNIX_TIMESTAMP(date_purchased) >= (UNIX_TIMESTAMP() - (7 * 86400))');
	$aMLorders = MagnaDB::gi()->fetchArray ('SELECT orders_id FROM '.TABLE_MAGNA_ORDERS.'
		WHERE orders_id >= '.$iStartFrom.' 
		ORDER BY orders_id DESC LIMIT 50', true);
	if (empty($aMLorders)) return;
	$doubleOTLines = MagnaDB::gi()->fetchArray ('
		SELECT ot.orders_id, MAX(ot.orders_total_id) max_orders_total_id, COUNT(*) as cnt
		 FROM '.TABLE_ORDERS_TOTAL.' ot
		  WHERE ot.orders_id IN ('.implode(',', $aMLorders).')
		   GROUP BY ot.orders_id, ot.class, ot.title, ot.text
		   HAVING cnt > 1');
	if (empty($doubleOTLines) || !is_array($doubleOTLines)) {
		return;
	}
	$surplus_orders_total_ids = '';
	foreach ($doubleOTLines as $OTLine) {
		$surplus_orders_total_ids .= ', '.$OTLine['max_orders_total_id'];
	}
	$surplus_orders_total_ids = trim($surplus_orders_total_ids, ', ');
	MagnaDB::gi()->query('
		DELETE FROM '.TABLE_ORDERS_TOTAL.'
		WHERE orders_total_id IN ('.$surplus_orders_total_ids.')
	');
}

function magnaFixOrders() {
	if (isset($_GET['forceDeleteDoubleOrders']) && ($_GET['forceDeleteDoubleOrders'] == 'true')) {
		setDBConfigValue('deletedoubleorders', 0, 'true', true);
	}
	if (getDBConfigValue('deletedoubleorders', '0', 'false') != 'true') {
		return;
	}
	delete_double_orders();
	delete_double_customers();
	delete_double_ot_lines();
	setDBConfigValue('deletedoubleorders', 0, 'false', true);
}

function priceToFloat($price, $format = array()) {
	$r = '/^([0-9\.,]*)$/';
	if (!preg_match($r, $price)) {
		return -1;
	}
	$frac = array();
	if (preg_match('/([\.,]{1,2}[0-9]{1,2})$/', $price, $frac)) {
		$frac = $frac[0];
		$price = substr($price, 0, -strlen($frac));
	} else {
		$frac = '0';
	}
	$price = str_replace(array('.', ','), '', $price).'.'.str_replace(array('.', ','), '', $frac);
	return (float)$price;
}

function generateUniqueProductModels() {
	MagnaDB::gi()->query('
		UPDATE '.TABLE_PRODUCTS.'
		   SET products_model=CONCAT(\'p\', products_id)
		 WHERE products_model=\'\' OR products_model IS NULL
	');

	$q = MagnaDB::gi()->query('
	    SELECT products_model, COUNT(products_model) as cnt
	      FROM '.TABLE_PRODUCTS.'
	     WHERE products_model <> \'\'
	  GROUP BY products_model
	    HAVING cnt > 1'
	);
	$dblProdModel = array();
	while ($row = MagnaDB::gi()->fetchNext($q)) {
		$dblProdModel[] = MagnaDB::gi()->escape($row['products_model']);
	}

	$q = MagnaDB::gi()->query('
		SELECT products_id, products_model
		  FROM '.TABLE_PRODUCTS.'
		 WHERE products_model IN (\''.implode('\', \'', $dblProdModel).'\')
	');
	$dblProdModel = array();
	while ($row = MagnaDB::gi()->fetchNext($q)) {
		$dblProdModel[$row['products_model']][] = $row['products_id'];
	}
	//echo print_m($dblProdModel);
	if (!empty($dblProdModel)) {
		foreach ($dblProdModel as $pMod => $pIDs) {
			$i = 1;
			foreach ($pIDs as $pID) {
				MagnaDB::gi()->update(TABLE_PRODUCTS, array(
					'products_model' => $pMod.'_'.($i++)
				), array(
					'products_id' => $pID
				));
			}
		}
	}
	# Varianten
	if (MagnaDB::gi()->columnExistsInTable('attributes_model', TABLE_PRODUCTS_ATTRIBUTES)) {
		MagnaDB::gi()->query('
			UPDATE '.TABLE_PRODUCTS_ATTRIBUTES.'
			   SET attributes_model=CONCAT(\'p\', products_id, \'_\', products_attributes_id)
			 WHERE attributes_model=\'\' OR attributes_model IS NULL
		');
		$q = MagnaDB::gi()->query('
		    SELECT attributes_model, COUNT(attributes_model) as cnt
		      FROM '.TABLE_PRODUCTS_ATTRIBUTES.'
		     WHERE attributes_model <> \'\'
		  GROUP BY attributes_model
		    HAVING cnt > 1
		');
		$dblAttrModel = array();
		while ($row = MagnaDB::gi()->fetchNext($q)) {
			$dblAttrModel[] = $row['attributes_model'];
		}
	
		$q = MagnaDB::gi()->query('
			SELECT products_attributes_id, attributes_model
			  FROM '.TABLE_PRODUCTS_ATTRIBUTES.'
			 WHERE attributes_model IN (\''.implode('\', \'', $dblAttrModel).'\')
		');
		$dblAttrModel = array();
		while ($row = MagnaDB::gi()->fetchNext($q)) {
			$dblAttrModel[$row['attributes_model']][] = $row['products_attributes_id'];
		}
		//echo print_m($dblProdModel);
		if (!empty($dblAttrModel)) {
			foreach ($dblAttrModel as $pMod => $pIDs) {
				$i = 1;
				foreach ($pIDs as $pID) {
					MagnaDB::gi()->update(TABLE_PRODUCTS_ATTRIBUTES, array(
						'attributes_model' => $pMod.'_'.($i++)
					), array(
						'products_attributes_id' => $pID
					));
				}
			}
		}
	}
}

function mlGetVariationSkuField() {
	return (getDBConfigValue('general.keytype', '0') == 'artNr')
		? 'marketplace_sku'
		: 'marketplace_id';
}

# update magnalister's variations table for a single product
# return true if variations available, otherwise false
function setProductVariations($pID, $language = false, $resetModelNames = true) {
	require_once(DIR_MAGNALISTER_FS_INCLUDES.'lib/classes/VariationsCalculator.php');

	$skutype = ('artNr' == getDBConfigValue('general.keytype', '0')) ? 'model' : 'id';

	$vc = new VariationsCalculator(array(
		'skubasetype' => $skutype, //  [model | id]
		'skuvartype'  => $skutype, //  [model | id]
	), $language);

	if (!$resetModelNames) {
		$namesArr = MagnaDB::gi()->fetchArray('
			SELECT variation_attributes, '.mlGetVariationSkuField().' AS variation_products_model
			  FROM '.TABLE_MAGNA_VARIATIONS.' WHERE products_id = '.$pID
		);
		if (is_array($namesArr)) {
			$namesByAttr = array();
			foreach ($namesArr as $namesRow) {
				$namesByAttr[$namesRow['variation_attributes']] = $namesRow['variation_products_model'];
			}
			if (empty($namesByAttr)) {
				unset($namesByAttr);
				$namesByAttr = false;
			}
		} else {
			$namesByAttr = false;
		}
	}

	MagnaDB::gi()->query('DELETE FROM '.TABLE_MAGNA_VARIATIONS.' WHERE products_id = '.$pID);

	$permutations = $vc->getVariationsByPID($pID);
	if (!$permutations) return false;

	# preserve variation products model names, if wished
	if (!$resetModelNames) {
		foreach ($permutations as &$permutation) {
			if (isset ($namesByAttr[$permutation['variation_attributes']])) {
				$permutation['variation_products_model'] = $namesByAttr[$permutation['variation_attributes']];
			}
		}
	}

	if (MagnaDB::gi()->batchinsert(TABLE_MAGNA_VARIATIONS, $permutations, true)) {
		return true;
	}
	return false;
}

# show the quantity of all variations of a product,
# without actually filling the variations table
# minus: subtract from each variation's stock, for the case this is set by config
function getProductVariationsQuantity($pID, $minus = 0) {
	$quantity = 0;
	require_once(DIR_MAGNALISTER_FS_INCLUDES.'lib/classes/VariationsCalculator.php');
	$skutype = (getDBConfigValue('general.keytype', '0') == 'artNr') ? 'model' : 'id';
	$vc = new VariationsCalculator(array(
		'skubasetype' => $skutype,
		'skuvartype'  => $skutype,
	));
	return $vc->getProductVariationsTotalQuantity($pID, $minus);
}

# does the product have variations? no matter if there's stock > 0
function variationsExist($pID) {
	if (MagnaDB::gi()->fetchOne('
		SELECT COUNT(*)
		  FROM '.TABLE_MAGNA_VARIATIONS.'
		 WHERE products_id = '.$pID) > 0
	) {
		return true;
	} else {
		return false;
	}
}

function getCurrencyFromMarketplace($mpID) {
	global $magnaConfig, $_modules;

	$mp = magnaGetMarketplaceByID($mpID);
	if ($mp === false) {
		return false;
	}
	if (!array_key_exists($mp, $_modules)) {
		return false;
	}
	$currency = $_modules[$mp]['settings']['currency'];
	if ($currency != '__depends__') {
		return $currency;
	}

	$cur = getDBConfigValue($mp.'.currency', $mpID, false);
	return empty($cur) ? false : $cur;
}

function magnaSKU2pID($sku, $mainOnly = false) {
	$skuOriginal = $sku;
	$pID = 0;

	if (empty($sku)) {
		return $pID;
	}

	# check character sets
	global $_MagnaSession;
	if (    (!array_key_exists('character_set_client', $_MagnaSession))
	     || (!array_key_exists('character_set_system', $_MagnaSession))
	) {
		$_MagnaSession['character_set_client'] = MagnaDB::gi()->mysqlVariableValue('character_set_client');
		if (('utf8mb3' == $_MagnaSession['character_set_client']) || ('utf8mb4' == $_MagnaSession['character_set_client'])) {
			$_MagnaSession['character_set_client'] = 'utf8';
		}
		$_MagnaSession['character_set_system'] = MagnaDB::gi()->mysqlVariableValue('character_set_system');
		if (('utf8mb3' == $_MagnaSession['character_set_system']) || ('utf8mb4' == $_MagnaSession['character_set_system'])) {
			$_MagnaSession['character_set_system'] = 'utf8';
		}
	}
	if (('utf8' == $_MagnaSession['character_set_system']) && ('utf8' != $_MagnaSession['character_set_client'])) {
		$sku = utf8_decode($sku);
	}

	$sku = MagnaDB::gi()->escape($sku);

	/* {Hook} "MagnaSKU2pID": Enables you to implement your own algorithm to identify products in your shop
	   based on their SKU.<br>
	   Variables that can be used:
	   <ul><li>$sku: The SKU string.</li>
	       <li>$pID: The detected products_id. If $pID !== 0 the function returns the value, otherwise it will continue with the identification process.</li>
	   </ul>
	 */
	if (($hp = magnaContribVerify('MagnaSKU2pID', 1)) !== false) {
		require($hp);
	}

	if ($pID !== 0) {
		return $pID;
	}

	if (!$mainOnly) {
		# Assume it's a Variation.
		# if Gambio Properties enabled, check it out
		# use 'while' so that we can 'break'
		if (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties') {
			$mProperty = magnaSKU2GambioProp($sku);
			if (is_array($mProperty) && isset($mProperty['products_id']) && !empty($mProperty['products_id'])) {
				return $mProperty['products_id'];
			}
		} else {
			$aID = magnaSKU2aID($skuOriginal);
			if ($aID !== false) {
				$pID = (int)MagnaDB::gi()->fetchOne('
					SELECT products_id 
					  FROM '.TABLE_PRODUCTS_ATTRIBUTES.'
					 WHERE products_attributes_id = \''.$aID.'\'
					       AND products_id != 0
					 LIMIT 1
				');
				if ($pID > 0)  {
					return $pID;
				}
			}

			# Try a variation from the magnalister variations table
			$pID = (int)MagnaDB::gi()->fetchOne(eecho('
				SELECT MAX(products_id)
				  FROM '.TABLE_MAGNA_VARIATIONS.'
			 	 WHERE    ('.mlGetVariationSkuField().' = "'.$sku.'"
			 	       OR variation_products_model = "'.$sku.'")
			 	 LIMIT 1
			', false));
			if ($pID > 0) {
				return $pID;
			}
		}
	}

	# It wasn't a variation. Try with products_id or products_model
	switch (getDBConfigValue('general.keytype', '0')) {
		case 'artNr': {
			$pID = (int)MagnaDB::gi()->fetchOne('
				SELECT products_id FROM '.TABLE_PRODUCTS.'
				 WHERE products_model = \''.$sku.'\' LIMIT 1
			');
			# ensure that a description is also set
			$pID = (int)MagnaDB::gi()->fetchOne('
				SELECT products_id FROM '.TABLE_PRODUCTS_DESCRIPTION.'
				 WHERE products_id = '.$pID.'
				LIMIT 1
			');
			break;
		}
		case 'pID': {
			if (strpos($sku, 'ML') === 0) {
				// Check if pID exists in table products
				$pID = (int)MagnaDB::gi()->fetchOne('
					SELECT products_id FROM '.TABLE_PRODUCTS.'
					 WHERE products_id = \''.((int)str_replace('ML', '', $sku)).'\' LIMIT 1
				');
				// and in products_description
				$pID = (int)MagnaDB::gi()->fetchOne('
					SELECT products_id FROM '.TABLE_PRODUCTS_DESCRIPTION.'
					 WHERE products_id = '.$pID.' LIMIT 1
				');
			}
			break;
		}
	}
	return $pID;
}

/*
 * !!! watch out this function will called in magnaSKU2pid !!!
 */
function magnaSKU2aID($sku, $pId = false, $multiple = false) {
	$aID = false;

	if (empty($sku)) {
		return $aID;
	}

	# check character sets
	global $_MagnaSession;
	if (    (!array_key_exists('character_set_client', $_MagnaSession))
		|| (!array_key_exists('character_set_system', $_MagnaSession))
	) {
		$_MagnaSession['character_set_client'] = MagnaDB::gi()->mysqlVariableValue('character_set_client');
		if (('utf8mb3' == $_MagnaSession['character_set_client']) || ('utf8mb4' == $_MagnaSession['character_set_client'])) {
			$_MagnaSession['character_set_client'] = 'utf8';
		}
		$_MagnaSession['character_set_system'] = MagnaDB::gi()->mysqlVariableValue('character_set_system');
		if (('utf8mb3' == $_MagnaSession['character_set_system']) || ('utf8mb4' == $_MagnaSession['character_set_system'])) {
			$_MagnaSession['character_set_system'] = 'utf8';
		}
	}
	if (('utf8' == $_MagnaSession['character_set_system']) && ('utf8' != $_MagnaSession['character_set_client'])) {
		$sku = utf8_decode($sku);
	}

	/* {Hook} "MagnaSKU2aID": Enables you to implement your own algorithm to identify variation products in your shop
	   based on their SKU.<br>
	   Variables that can be used:
	   <ul><li>$sku: The SKU string.</li>
	       <li>$aID: The detected products_id. If $pId !== false the function returns the value, otherwise it will continue with the identification process.</li>
	   </ul>
	 */
	if (($hp = magnaContribVerify('MagnaSKU2aID', 1)) !== false) {
		require($hp);
	}

	if ($aID !== false) {
		return $aID;
	}

	# if Gambio Properties enabled, check it out
	if (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties') {
		$mProperty = magnaSKU2GambioProp($sku);
		if (is_array($mProperty) && isset($mProperty['products_properties_combis_id']) && !empty($mProperty['products_properties_combis_id'])) {
			return $mProperty['products_properties_combis_id'];
		}
	} else {
		do {
			if ('artNr' != getDBConfigValue('general.keytype', '0')) {
				break;
			}

			// preg_match might interpret latin1 strings with umlauts as binary string.
			// So we have to convert them to proper utf8 first.
			$utf8Sku = magnalisterIsUTF8($sku) ? $sku : utf8_encode($sku);
			if (preg_match('/(.*)_MLV([0-9]*)_([0-9]*)$/u', $utf8Sku, $match)) {
				$pID = magnaSKU2pID($match[1]);
				return MagnaDB::gi()->fetchOne(eecho('
					SELECT products_attributes_id
					  FROM '.TABLE_PRODUCTS_ATTRIBUTES.'
					 WHERE products_id="'.$pID.'"
						   AND options_id="'.$match[2].'"
						   AND options_values_id="'.$match[3].'"
					 LIMIT 1
				', false));
			} else if (MagnaDB::gi()->columnExistsInTable('attributes_model', TABLE_PRODUCTS_ATTRIBUTES)) {
				$aID = MagnaDB::gi()->fetchOne(eecho('
					SELECT products_attributes_id
					  FROM '.TABLE_PRODUCTS_ATTRIBUTES.'
					 WHERE attributes_model = "'.MagnaDB::gi()->escape($sku).'"
						   '.($pId > 0 ? 'AND products_id = "'.$pId.'"' : 'AND products_id != 0').'
					 LIMIT 1
				', false));
				if ($aID > 0) return $aID;
			}
		} while (false);
	}

	$opt = false;
	$opts = array();
	if (strpos($sku, 'MLV') === 0) {
		$skuTmp = str_replace('MLV', '', $sku);
		$opt = explode('_', $skuTmp);
	} else {
		$multiVar = MagnaDB::gi()->fetchRow(eecho('
			SELECT mv.products_id, mv.products_sku, mv.variation_attributes
			  FROM '.TABLE_MAGNA_VARIATIONS.' mv, '.TABLE_PRODUCTS.' p
			 WHERE (mv.'.mlGetVariationSkuField().'="'.MagnaDB::gi()->escape($sku).'" OR mv.variation_products_model="'.MagnaDB::gi()->escape($sku).'")
			   AND mv.products_id = p.products_id AND mv.products_sku = p.products_model
			 LIMIT 1
		', false));

		if (empty($multiVar)) {
			// if products_model and _id don't match the product table
			// look if at least one matches
			$multiVar = MagnaDB::gi()->fetchRow(eecho('
			SELECT mv.products_id, mv.products_sku, mv.variation_attributes
			  FROM '.TABLE_MAGNA_VARIATIONS.' mv, '.TABLE_PRODUCTS.' p
			 WHERE (mv.'.mlGetVariationSkuField().' = "'.MagnaDB::gi()->escape($sku).'" OR mv.variation_products_model = "'.MagnaDB::gi()->escape($sku).'")
			   AND mv.products_id = p.products_id
			 LIMIT 1
			', false));

            // Do not make (AND (mv.products_id = p.products_id OR mv.products_sku = p.products_model) its really slow...
            if (empty($multiVar)) {
                $multiVar = MagnaDB::gi()->fetchRow(eecho("
                    SELECT mv.products_id, mv.products_sku, mv.variation_attributes
                      FROM ".TABLE_MAGNA_VARIATIONS." mv, ".TABLE_PRODUCTS." p
                     WHERE     (mv.".mlGetVariationSkuField()." = '".MagnaDB::gi()->escape($sku)."' OR mv.variation_products_model = '".MagnaDB::gi()->escape($sku)."')
                           AND mv.products_sku = p.products_model
                    LIMIT 1
                ", false));
            }
		}
		if(!empty($multiVar)) {
			// if key type is product model try to fetch the actual product_id
			if ('artNr' == getDBConfigValue('general.keytype', '0')) {
				$iProductId = magnaSKU2pID($multiVar['products_sku'], true);
				if ($iProductId > 0) {
					$multiVar['products_id'] = $iProductId.''; // cast to string because ctype_digit() needs a string
				}
			}
			$multiVar['variation_attributes'] = explode('|', trim($multiVar['variation_attributes'], '|'));
			#echo print_m($multiVar, '$multiVar');
			if ($multiple) {
				$i = 0;
				while ($mV = array_shift($multiVar['variation_attributes'])) {
					$opts[$i] = explode(',', $mV);
					array_unshift($opts[$i], $multiVar['products_id']);
					++$i;
				}
			} else {
				$multiVar['variation_attributes'] = array_shift($multiVar['variation_attributes']);
				$opt = explode(',', $multiVar['variation_attributes']);
				array_unshift($opt, $multiVar['products_id']);
			}
		}
	}

	if (!$multiple) {
		$opts = array($opt); // to omit code duplication
	}

	$aID = array();
	foreach ($opts as $opt) {
		if (is_array($opt) && array_key_exists(2, $opt)
		    && ctype_digit($opt[0]) && ctype_digit($opt[1]) && ctype_digit($opt[2])
		) {
			/* Neue Version */
			$aID[] = MagnaDB::gi()->fetchOne(eecho('
			    SELECT products_attributes_id
			      FROM '.TABLE_PRODUCTS_ATTRIBUTES.'
			     WHERE products_id="'.$opt[0].'"
			           AND options_id="'.$opt[1].'"
			           AND options_values_id="'.$opt[2].'"
			     LIMIT 1
			', false));
		}
	}
	if (count($aID) == 1) {
		$aID = $aID[0];
	}
	return empty($aID) ? false : $aID;
}

function magnaSkuWithParentSku2aID($sku, $parentSku) {
	$pID = magnaSKU2pID($parentSku);
	return magnaSKU2aID($sku, ($pID > 0) ? $pID : false);
}

function magnaPID2SKU($pID) {
	$sku = '';
	switch (getDBConfigValue('general.keytype', '0')) {
		case 'artNr': {
			$sku = MagnaDB::gi()->fetchOne('
				SELECT products_model FROM '.TABLE_PRODUCTS.'
				 WHERE products_id=\''.$pID.'\' LIMIT 1
			');
			break;
		}
	}

	if (empty($sku)) {
		$sku = 'ML'.$pID;
	}

	return trim($sku);
}

function magnaAID2SKU($aID) {
	if (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties') {
		return magnaGambioPropID2SKU($aID);
	}
	if ('artNr' == getDBConfigValue('general.keytype', '0')) {
		$attrModel = '';
		if (MagnaDB::gi()->columnExistsInTable('attributes_model', TABLE_PRODUCTS_ATTRIBUTES)) {
			$attrModel = (string)MagnaDB::gi()->fetchOne('
				SELECT attributes_model
				  FROM '.TABLE_PRODUCTS_ATTRIBUTES.'
				 WHERE products_attributes_id = \''.$aID.'\'
			');
		}
		if (!empty($attrModel)) {
			return $attrModel;
		}
		$attr = MagnaDB::gi()->fetchRow('
			SELECT p.products_id, p.products_model, options_id, options_values_id
			  FROM '.TABLE_PRODUCTS_ATTRIBUTES.' pa, '.TABLE_PRODUCTS.' p
			 WHERE products_attributes_id = \''.$aID.'\'
			       AND pa.products_id=p.products_id
		');
		return (empty($attr['products_model'])
			? 'MLV'.$attr['products_id'].'_'.$attr['options_id'].'_'.$attr['options_values_id']
			: $attr['products_model'].'_MLV'.$attr['options_id'].'_'.$attr['options_values_id']
		);
	} else {
		$attr = MagnaDB::gi()->fetchRow('
			SELECT products_id, options_id, options_values_id
			  FROM '.TABLE_PRODUCTS_ATTRIBUTES.'
			 WHERE products_attributes_id = \''.$aID.'\'
		');
		return 'MLV'.$attr['products_id'].'_'.$attr['options_id'].'_'.$attr['options_values_id'];
	}
}

/*
 * load the gambio properties data from cobmis table from sku
 * @return array If sku can not be find, it will return false.
 */
function magnaSKU2GambioProp($sSku) {
	if ('artNr' == getDBConfigValue('general.keytype', '0')) {
		return MagnaDB::gi()->fetchRow(eecho("
		    SELECT CONCAT(p.products_model, '-', ppc.combi_model) AS SKU, ppc.*
		      FROM products_properties_combis ppc
		INNER JOIN ".TABLE_PRODUCTS." p ON p.products_id = ppc.products_id
		    HAVING SKU = '".MagnaDB::gi()->escape($sSku)."'
		", false));
	} else {
		$aData = explode('-', $sSku);
		if ($aData === false) {
			return false;
		}
		$sPId = substr(array_shift($aData), 2);
		if (!is_numeric($sPId)) {
			// cannot be found. No shop item, or keytype switched
			return false;
		}

		$mProductPropertiesCombisId = false;
		foreach ($aData as $sPropSet) {
			$sPropSet = explode('.', $sPropSet);

			$mProductPropertiesCombisId = MagnaDB::gi()->fetchArray(eecho("
				SELECT DISTINCT products_properties_combis_id
				  FROM products_properties_index
				 WHERE     products_id = '".$sPId."'
				       AND properties_id = '".$sPropSet[0]."'
				       AND properties_values_id = '".$sPropSet[1]."'
				       ".((!empty($mProductPropertiesCombisId))
				           ? 'AND products_properties_combis_id IN ('.implode(', ', $mProductPropertiesCombisId).')'
				           : ''
				       )."
			", false), true);
		}

		if (is_array($mProductPropertiesCombisId) && (count($mProductPropertiesCombisId) === 1) && ((int)$mProductPropertiesCombisId[0] > 0)) {
			return MagnaDB::gi()->fetchRow(eecho("
			    SELECT CONCAT(p.products_model, '-', ppc.combi_model) AS SKU, ppc.*
			      FROM products_properties_combis ppc
			INNER JOIN products p ON p.products_id = ppc.products_id
			    WHERE products_properties_combis_id = '".((int)$mProductPropertiesCombisId[0])."'
			", false));
		} else {
			return false;
		}
	}
}

function magnaGambioPropID2SKU($combisID) {
	if ('artNr' == getDBConfigValue('general.keytype', '0')) {
		$propModel = (string)MagnaDB::gi()->fetchOne('
			SELECT CONCAT(p.products_model, \'-\', ppc.combi_model) AS SKU
				FROM '.TABLE_PRODUCTS.' p, products_properties_combis ppc
			   WHERE ppc.products_id=p.products_id
				     AND ppc.products_properties_combis_id = '.$combisID);
	}
	if (!empty($propModel)) {
		return $propModel;
	}
	# else keytype = products_id
	$pID = (int)MagnaDB::gi()->fetchOne('
		SELECT DISTINCT products_id FROM products_properties_combis WHERE products_properties_combis_id = '.$combisID);
	if (false === $pID) {
		return false;
	}
	$language_id = magnaGetDefaultLanguageID(); // we need 1 language here, no matter which one
	$propModel = 'ML'.$pID;
	$props = MagnaDB::gi()->fetchArray('
		SELECT CONCAT(\'-\', properties_id, \'.\', properties_values_id)
			FROM products_properties_index
		  WHERE products_properties_combis_id = '.$combisID.'
		        AND products_id = '.$pID.'
		        AND language_id = '.$language_id.'
		  ORDER BY properties_id, properties_values_id
		');
	foreach ($props as $prop) {
		if (!empty($prop)) {
			$propModel .= $prop;
		}
	}
	if (!empty($propModel)) {
		return $propModel;
	} else {
		return false;
	}
}

/*
 * loads the attributes or gambio properties by sku
 * @return array
 * ToDo: auf languages id umstellen
 */
function magnaSKU2ProductOptions($sSku, $iLangId = false, $bMultiVariations = false) {
	$iDefaultLangId = magnaGetDefaultLanguageID();
	if ($iLangId === false) {
		$iLangId = $iDefaultLangId;
	}

	$mAID = magnaSKU2aID($sSku, false,  $bMultiVariations);
	if ($mAID === false) {
		return array();
	} elseif (!is_array($mAID)) {
		$aAID = array($mAID);
	} else {
		$aAID = $mAID;
	}

	$aReturn = array();

	if (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties') {
		// GambioProperties part
		$aProperties = MagnaDB::gi()->fetchArray(eecho("
			SELECT ppi.products_properties_combis_id AS id,
			       ppi.properties_id AS options_id,
			       ppi.properties_name AS options_name,
			       ppi.properties_values_id AS options_values_id,
			       ppi.values_name AS options_values_name
			  FROM products_properties_index ppi
			 WHERE     ppi.products_properties_combis_id = '".$aAID[0]."'
			       AND language_id = '".$iLangId."'
			       AND ppi.properties_name <> ''
			       AND ppi.values_name <> ''
			 UNION
			SELECT ppi.products_properties_combis_id AS id,
			       ppi.properties_id AS options_id,
			       ppi.properties_name AS options_name,
			       ppi.properties_values_id AS options_values_id,
			       ppi.values_name AS options_values_name
			  FROM products_properties_index ppi
			 WHERE     ppi.products_properties_combis_id = '".$aAID[0]."'
			       AND language_id = '".$iDefaultLangId."'
			       AND ppi.properties_name <> ''
			       AND ppi.values_name <> ''
			 UNION
			SELECT ppi.products_properties_combis_id AS id,
			       ppi.properties_id AS options_id,
			       ppi.properties_name AS options_name,
			       ppi.properties_values_id AS options_values_id,
			       ppi.values_name AS options_values_name
			  FROM products_properties_index ppi
			 WHERE     ppi.products_properties_combis_id = '".$aAID[0]."'
			       AND ppi.properties_name <> ''
			       AND ppi.values_name <> ''
		", false));
		if ($aProperties !== false) {
			$aReturn = $aProperties;
		}
	} else {
		// Attributes part
		$aOptions = array();
		$bOptionsAreEmpty = true;
		foreach ($aAID as $iNo => $iAID) {
			$aOptions[$iNo] = MagnaDB::gi()->fetchRow(eecho("
				SELECT options_id,
				       options_values_id,
				       options_values_price,
				       price_prefix,
				       products_attributes_id
				  FROM ".TABLE_PRODUCTS_ATTRIBUTES."
				 WHERE products_attributes_id = '".$iAID."'
				 LIMIT 1
			", false));
			if (false !== $aOptions[$iNo]) {
				$bOptionsAreEmpty = false;
			}
		}
		if ($bOptionsAreEmpty) {
			return array();
		}

		foreach ($aOptions as $aOpt) {
			$sProductOptionName = MagnaDB::gi()->fetchOne(eecho("
				SELECT products_options_name
				  FROM ".TABLE_PRODUCTS_OPTIONS." po
				 WHERE     products_options_id = '".$aOpt['options_id']."'
				       AND language_id = '".$iLangId."'
				       AND products_options_name <> ''
				 UNION
				SELECT products_options_name
				  FROM ".TABLE_PRODUCTS_OPTIONS." po
				 WHERE     products_options_id = '".$aOpt['options_id']."'
				       AND language_id = '".$iDefaultLangId."'
				       AND products_options_name <> ''
				 UNION
				SELECT products_options_name
				  FROM ".TABLE_PRODUCTS_OPTIONS." po
				 WHERE     products_options_id = '".$aOpt['options_id']."'
				       AND products_options_name <> ''
				 LIMIT 1
			", false));

			$sProductOptionValuesName = MagnaDB::gi()->fetchOne(eecho("
				SELECT products_options_values_name
				  FROM ".TABLE_PRODUCTS_OPTIONS_VALUES." pov
				 WHERE     products_options_values_id = '".$aOpt['options_values_id']."'
				       AND language_id = '".$iLangId."'
				       AND products_options_values_name <> ''
				 UNION
				SELECT products_options_values_name
				  FROM ".TABLE_PRODUCTS_OPTIONS_VALUES." pov
				 WHERE     products_options_values_id = '".$aOpt['options_values_id']."'
				       AND language_id = '".$iDefaultLangId."'
				       AND products_options_values_name <> ''
				 UNION
				 SELECT products_options_values_name
				   FROM ".TABLE_PRODUCTS_OPTIONS_VALUES." pov
				  WHERE     products_options_values_id = '".$aOpt['options_values_id']."'
				        AND products_options_values_name <> ''
				 LIMIT 1
			", false));

			$aReturn[] = array(
				'id' => $aOpt['products_attributes_id'],
				'options_id' => $aOpt['options_id'],
				'options_name' => $sProductOptionName,
				'options_values_id' => $aOpt['options_values_id'],
				'options_values_name' => $sProductOptionValuesName,
				'options_values_price' => $aOpt['options_values_price'],
				'price_prefix' => $aOpt['price_prefix']
			);
		}
	}

	return $aReturn;
}

function magnaSKU2pOpt($sku, $language = 'en', $multiple = false) {
	$ret = array(
		'options_id' => 0,
		'options_name' => '',
		'options_values_id' => 0,
		'options_values_name' => '',
		'options_values_price' => 0.00,
		'price_prefix' => ''
	);
	if ($multiple) {
		$ret = array($ret);
	}

	$aID = magnaSKU2aID($sku, false,  $multiple);
	if ($aID === false) {
		return $ret;
	}

	if (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties') {
	// aID ist dann combis_id
		$ret = MagnaDB::gi()->fetchArray('
			SELECT ppi.properties_id AS options_id,
			       ppi.properties_name AS options_name,
                   ppi.properties_values_id AS options_values_id,
			       ppi.values_name AS options_values_name,
			       ABS(ppi.values_price) AS options_values_price,
			       IF(ppi.values_price < 0, \'-\', \'+\') AS price_prefix
			  FROM products_properties_index ppi, '.TABLE_LANGUAGES.' l
			 WHERE ppi.products_properties_combis_id = '.$aID.'
			   AND LOWER(l.code) = LOWER(\''.$language.'\')
			   AND l.languages_id = ppi.language_id
		');
		return $ret;
	}

	if (!is_array($aID)) $aID = array($aID);
	$options = array();
	$optionsAreEmpty = true;
	foreach ($aID as $no => $singleAID) {
		$options[$no] = MagnaDB::gi()->fetchRow('
			SELECT options_id, options_values_id, options_values_price, price_prefix
		  	FROM '.TABLE_PRODUCTS_ATTRIBUTES.'
		 	WHERE products_attributes_id = \''.$singleAID.'\'
		 	LIMIT 1
		');
		if (false !== $options[$no]) $optionsAreEmpty = false;
	}
	if ($optionsAreEmpty) {
		return $ret;
	}

	/* Name der Option und des Wertes (z.B. 'Groesse' und 'M/L') */
	if (empty($language)) {
		$language = 'en';
	}
	$ret = array();
	foreach ($options as $option) {
		$products_options_name = MagnaDB::gi()->fetchOne('
		    SELECT products_options_name
		      FROM '.TABLE_PRODUCTS_OPTIONS.' po, '.TABLE_LANGUAGES.' l
		     WHERE products_options_id = \''.$option['options_id'].'\'
		           AND po.language_id = l.languages_id
		           AND LOWER(code) = LOWER(\''.$language.'\')
		     LIMIT 1
		');
		$products_options_values_name = MagnaDB::gi()->fetchOne('
		    SELECT products_options_values_name
		      FROM '.TABLE_PRODUCTS_OPTIONS_VALUES.' pov, '.TABLE_LANGUAGES.' l
		     WHERE products_options_values_id=\''.$option['options_values_id'].'\'
		           AND pov.language_id = l.languages_id
		           AND LOWER(code) = LOWER(\''.$language.'\')
		     LIMIT 1
		');
		/* Fallback falls Datensaetze fuer gegebene Sprache nicht vorhanden */
		if (empty($products_options_name)) {
			$products_options_name = MagnaDB::gi()->fetchOne('
			    SELECT products_options_name
			      FROM '.TABLE_PRODUCTS_OPTIONS.' po
			     WHERE products_options_id = \''.$option['options_id'].'\'
			     LIMIT 1
			');
		}
		if (empty($products_options_values_name)) {
			$products_options_values_name = MagnaDB::gi()->fetchOne('
			    SELECT products_options_values_name
			      FROM '.TABLE_PRODUCTS_OPTIONS_VALUES.' pov
			     WHERE products_options_values_id=\''.$option['options_values_id'].'\'
			     LIMIT 1
			');
		}
		$ret[] = array(
			'options_id' => $option['options_id'],
			'options_name' => $products_options_name,
			'options_values_id' => $option['options_values_id'],
			'options_values_name' => $products_options_values_name,
			'options_values_price' => $option['options_values_price'],
			'price_prefix' => $option['price_prefix']
		);
	}
	if (1 == count($ret) && !$multiple) {
		$ret = array_shift($ret);
	}
	return $ret;
}

function renderPagination($currentPage, $pages, $baseURL, $type = 'link') {
	$html = '';
	if ($pages > 23) {
		for ($i = 1; $i <= 5; ++$i) {
			$class = ($currentPage == $i) ? 'class="bold"' : '';
			if ($type == 'submit') {
				$html .= ' <input type="submit" '.$class.' name="page" value="'.$i.'" title="'.ML_LABEL_PAGE.' '.$i.'"/>';
			} else {
				$html .= ' <a '.$class.' href="'.toUrl($baseURL, array('page' => $i)).'" title="'.ML_LABEL_PAGE.' '.$i.'">'.$i.'</a>';
			}
		}
		if (($currentPage - 5) < 7) {
			$start = 6;
			$end = 15;
		} else {
			$start = $currentPage - 4;
			$end = $currentPage + 4;
			$html .= ' &hellip; ';
		}
		if (($currentPage + 5) > ($pages - 7)) {
			$start = ($pages - 15);
			$end = $pages;
		}
		for ($i = $start; $i <= $end; ++$i) {
			$class = ($currentPage == $i) ? 'class="bold"' : '';
			if ($type == 'submit') {
				$html .= ' <input type="submit" '.$class.' name="page" value="'.$i.'" title="'.ML_LABEL_PAGE.' '.$i.'"/>';
			} else {
				$html .= ' <a '.$class.' href="'.toUrl($baseURL, array('page' => $i)).'" title="'.ML_LABEL_PAGE.' '.$i.'">'.$i.'</a>';
			}
		}
		if ($end != $pages) {
			$html .= ' &hellip; ';
			for ($i = $pages - 5; $i <= $pages; ++$i) {
				$class = ($currentPage == $i) ? 'class="bold"' : '';
				if ($type == 'submit') {
					$html .= ' <input type="submit" '.$class.' name="page" value="'.$i.'" title="'.ML_LABEL_PAGE.' '.$i.'"/>';
				} else {
					$html .= ' <a '.$class.' href="'.toUrl($baseURL, array('page' => $i)).'" title="'.ML_LABEL_PAGE.' '.$i.'">'.$i.'</a>';
				}
			}
		}
	} else {
		for ($i = 1; $i <= $pages; ++$i) {
			$class = ($currentPage == $i) ? 'class="bold"' : '';
			if ($type == 'submit') {
				$html .= ' <input type="submit" '.$class.' name="page" value="'.$i.'" title="'.ML_LABEL_PAGE.' '.$i.'"/>';
			} else {
				$html .= ' <a '.$class.' href="'.toUrl($baseURL, array('page' => $i)).'" title="'.ML_LABEL_PAGE.' '.$i.'">'.$i.'</a>';
			}
		}
	}
	return $html;
}

function renderCategoryPath($id, $from = 'category', $appendedText = '&nbsp;<span class="cp_next">&gt;</span>&nbsp;') {
	$calculated_category_path_string = '';
	$calculated_category_path = MLProduct::gi()->generateCategoryPath($id, $from);
	for ($i = 0, $n = sizeof($calculated_category_path); $i < $n; $i ++) {
		for ($j = 0, $k = sizeof($calculated_category_path[$i]); $j < $k; $j ++) {
			$calculated_category_path_string .= fixHTMLUTF8Entities($calculated_category_path[$i][$j]['text']).$appendedText;
		}
		$calculated_category_path_string = substr($calculated_category_path_string, 0, -strlen($appendedText)).'<br>';
	}
	$calculated_category_path_string = substr($calculated_category_path_string, 0, -4);

	if (strlen($calculated_category_path_string) < 1) {
		$calculated_category_path_string = ML_LABEL_CATEGORY_TOP;
	}
	return $calculated_category_path_string;
}

function loadConfigForm($lang, $files, $replace = array()) {
    $form = array();
    foreach ($files as $file => $options) {
        $fC = file_get_contents(DIR_MAGNALISTER_FS.'config/'.$lang.'/'.$file);
//        if (json_decode($fC, true) === null) {
//            $pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
//            $baseurlpart = explode('magnalister.php', $_SERVER["REQUEST_URI"]);
//            $sPHPFileName = str_replace('.form', '.php', $file);
//            file_put_contents(DIR_MAGNALISTER_FS.'config/'.$lang.'/'.$sPHPFileName, $fC);
//            $pageURL .= $_SERVER["SERVER_NAME"].($_SERVER["SERVER_PORT"] != "80" ? ":".$_SERVER["SERVER_PORT"] : '').$baseurlpart[0];
//            $pageURL .= DIR_MAGNALISTER_WS.'config/'.$lang.'/'.$sPHPFileName;
//            $fC = file_get_contents($pageURL);
//        }
        $replace['http:\/\/www.IhrShop.de\/'] = trim(json_encode(HTTP_SERVER.DIR_WS_CATALOG), ' "\/').'\/';
        if (!empty($replace)) {
            $fC = str_replace(array_keys($replace), array_values($replace), $fC);
        }
        $fC = json_decode($fC, true);
        if (array_key_exists('unset', $options)) {
            foreach ($options['unset'] as $key) {
                unset($fC[$key]);
            }
        }
        $form = array_merge($form, $fC);
    }
    return $form;
}

function getTinyMCEDefaultConfigObject() {
	global $_langISO;
	$fullpage = getDBConfigValue(array('general.editor.fullpage', 'val'), 0, 'false') == 'true';
	return '
if (typeof tinyMCEMagnaDefaultConfig == "undefined") {
	var tinyMCEMagnaDefaultConfig = {
		language : "'.$_langISO.'",
		selector: "textarea.tinymce",
		plugins: [
			"advlist autolink lists link image charmap print preview anchor",
			"searchreplace visualblocks code fullscreen textcolor",
			"insertdatetime media table contextmenu paste emoticons'.($fullpage ? ' fullpage' : '').'"
		],
		toolbar: "insertfile undo redo | styleselect | fontselect | fontsizeselect | bold italic | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
		//toolbar1: "insertfile undo redo | bold italic | forecolor backcolor | fontselect fontsizeselect | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | styleselect | link image",
		//toolbar_items_size: "small",
		valid_elements : "*[*]",
		invalid_elements : "",
		valid_children : "+body[style]",
		extended_valid_elements : "style[width], a[href|#]",
		relative_urls : false,
		document_base_url : "'.DIR_WS_CATALOG.'",
		remove_script_host : false,
		media_strict: false,
		gecko_spellcheck : true,
		autosave_ask_before_unload : true
	}
}';
}

function magna_wysiwyg($params, $value = '') {
	if (array_key_exists('class', $params)) {
		$params['class'] .= ' tinymce';
	} else {
		$params['class'] = 'tinymce';
	}
	$html = '<textarea';
	foreach ($params as $attr => $val) {
		$html .= ' '.$attr.'="'.$val.'"';
	}
	$html .= '>'.str_replace('<', '&lt;', (string)$value).'</textarea>';

	// tinyMceAlreadySet braucht man momentan nich, aber ggf falls wir die tinymce Version updaten
	if ('tinyMCE' == getDBConfigValue('general.editor', 0, 'tinyMCE') && !defined('tinyMceAlreadySet')) {
		$html .= '<script type="text/javascript" src="'.DIR_MAGNALISTER_WS.'js/tinymce/tinymce.min.js"></script>';
		define('tinyMceAlreadySet', 'true');

		ob_start();?>
		<script type="text/javascript">/*<![CDATA[*/
			<?php echo getTinyMCEDefaultConfigObject(); ?>
			$(window).load(function() {
				tinyMCE.init(tinyMCEMagnaDefaultConfig);
			});
		/*]]>*/</script><?php
		$html .= ob_get_contents();
		ob_end_clean();
	}
	return $html;
}

function magnaFixRamSize() {
	$nr = defined('ML_DEFAULT_RAM') ? ML_DEFAULT_RAM : '256M';
	$ramsize = @ini_get('memory_limit');
	if (!is_string($ramsize) || empty($ramsize)) {
		return @(bool)ini_set('memory_limit', $nr);
	}
	if (convert2Bytes($ramsize) < convert2Bytes($nr)) {
		return @(bool)ini_set('memory_limit', $nr);
	}
	return false;
}

function magnaFixExecutionTime() {
	$to = (int)(defined('ML_DEFAULT_EXECUTIONTIME') ? ML_DEFAULT_EXECUTIONTIME : 0);
	$to = ($to == 0) ? 240 : $to;

	$current = @ini_get('max_execution_time');
	if (!is_string($current) || (empty($current) && ($current !== '0'))) {
		$current = 1;
	} else {
		$current = (int)$current;
	}
	//
	if (($current != 0) && ($current < $to)) {
		@set_time_limit($to);
	}
}

function magnaFooterDebugTimers() {
	if (!defined('MAGNALISTER_PLUGIN') && file_exists(DIR_FS_DOCUMENT_ROOT.'magnaCallback.php')
		&& defined('MAGNA_DEBUG') && MAGNA_DEBUG
	) {
		global $_magnacallbacktimer, $_executionTime;

		echo '
		<style>
		textarea.apiRequestTime {
			border: 2px solid #ccc;
			background: #f8f8f8;
			color: #f8f8f8;
			border-radius: 5px;
			-moz-border-radius: 5px;
			-webkit-border-radius: 5px;
			width: 5px !important;
			height: 5px !important;
			padding: 0 !important;
			margin: 0 0 3px 0;
			overflow: hidden;
			font: 11px/1em monospace;
			display: block;
			-moz-box-sizing: content-box;
			-webkit-box-sizing: content-box;
			resize: none;
		}
		textarea.apiRequestTime:hover {
			color: #333;
			width: 100% !important;
			height: 300px !important;
			margin-top: -298px;
			position: relative;
			overflow: auto;
			padding: 3px 3px 0 3px !important;
			box-shadow: 0 0 15px rgba(0, 0, 0, 0.7), 0 0 2px 2px rgba(0, 0, 0, 0.3);
			-moz-box-shadow: 0 0 15px rgba(0, 0, 0, 0.7), 0 0 2px 2px rgba(0, 0, 0, 0.3);
			-webkit-box-shadow: 0 0 15px rgba(0, 0, 0, 0.7), 0 0 2px 2px rgba(0, 0, 0, 0.3);
		}
		div#magnaErrors {
			position: relative;
			width: 100%;
		}
		div#magnaErrors table {
			border-collapse: separate;
			border-spacing: 2px;
			width: 100%;
			position: relative;
			border: 2px solid #f00;
		}
		div#magnaErrors table th,
		div#magnaErrors table td {
			padding: 3px 5px;
			vertical-align: top;
		}
		div#magnaErrors table thead th {
			background: #444;
			color: #fff;
			font-weight: bold;
		}
		div#magnaErrors table tbody.odd td {
			background: #aaa;
			color: #000;
		}
		div#magnaErrors table tbody.even td {
			background: #ccc;
			color: #000;
		}
		div#magnaErrors table tbody td.level {
			font-weight: bold;
		}
		div#magnaErrors table tbody td.level.notice {
			background: #C9FF48;
			color: #000;
		}
		div#magnaErrors table tbody td.level.warning {
			background: #FFB548;
			color: #000;
		}
		div#magnaErrors table tbody td.level.fatal {
			background: #FF4848;
			color: #000;
		}
		div#magnaErrors table tbody td.message {
			position: relative;
		}
		div#magnaErrors table tbody td.action textarea,
		div#magnaErrors table tbody td.message pre {
			display: none;
			position: absolute;
			background: #fff;
			color: #000;
			border: 2px solid #999;
			z-index: 2;
			padding: 3px;
			margin-top: -2px;
			left: 0;
			width: 100%;
			overflow: auto;
		}
		div#magnaErrors table tbody td.message:hover pre {
			bottom: 1em;
			display: block;
			max-height: 450px;
		}
		div#magnaErrors table tbody td.action:hover textarea {
			display: block;
			color: #333333;
			height: 300px !important;
			overflow: auto;
			padding: 3px 3px 0 !important;
			width: 100% !important;
			font: 11px/1em monospace;
			margin-top: -330px;
		}
		#magnaFootDebugger {
			text-align: left;
			background: #fff;
			border: 2px solid #999;
			font: 11px/1.2em sans-serif;
			padding: 5px;
		}
		#magnaFootDebugger hr {
			border-top: none;
			border-left: none;
			border-right: none;
			border-bottom: 1px solid #999;
		}
		</style>
		<div id="magnaFootDebugger">
			magnaCallback Time: <b>'.microtime2human($_magnacallbacktimer).'</b><br/><hr/>';

		if (class_exists('MagnaDB') && class_exists('MagnaConnector')) {
			$_executionTime = microtime(true) -  $_executionTime;
			$memory = memory_usage();
			echo '
				Entire page served in <b>'.microtime2human($_executionTime).'.</b><br/><hr/>
				API-Request Time: '.microtime2human(MagnaConnector::gi()->getRequestTime()).'. <br/>
				Processing Time: '.microtime2human($_executionTime - MagnaConnector::gi()->getRequestTime()).'. <br/><hr/>
				'.(($memory !== false) ? 'Max. Memory used: <b>'.$memory.'</b>. <br/><hr/>' : '').'
				DB-Stats (only MagnaDB Queries!): <br/>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Queries used: <b>'.MagnaDB::gi()->getQueryCount().'</b><br/>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Query time: '.microtime2human(MagnaDB::gi()->getRealQueryTime()).'<br/><hr/>
			';
		}
		if (class_exists('MagnaConnector')) {
			$tpR = MagnaConnector::gi()->getTimePerRequest();
			if (!empty($tpR)) {
				echo '<textarea class="apiRequestTime" readonly="readonly" spellcheck="false" wrap="off">';
				foreach ($tpR as $item) {
					echo print_m($item['request'], microtime2human($item['time']).' ['.$item['status'].']', true)."\n";
				}
				echo '</textarea>';
			}
		}
		if (class_exists('MagnaDB')) {
			$tpR = MagnaDB::gi()->getTimePerQuery();
			if (!empty($tpR)) {
				echo '<textarea class="apiRequestTime" readonly="readonly" spellcheck="false" wrap="off">';
				foreach ($tpR as $item) {
					echo print_m(ltrim(rtrim($item['query'], "\n"), "\n"), microtime2human($item['time']), true)."\n";
				}
				echo '</textarea>';
			}
		}
		$err = MagnaError::gi()->exceptionsToHTML(false);
		if (!empty($err)) {
			echo '<br/><hr/><div id="magnaErrors"><div>'.$err.'</div></div>';
		}

		echo '</div>';
	}
}

function magnaDumpSqlErrorlog() {
	if (!class_exists('MagnaDB')) {
		return;
	}
	$errors = MagnaDB::gi()->getSqlErrors();
	if (empty($errors)) {
		return;
	}

	echo '
		<style>
table.sqlerror {
	border: 2px solid #f00 !important;
}
table.sqlerror thead th {
	background: #933 !important;
	color: #fff;
	font-weight: bold;
}
table.sqlerror td {
	position: relative;
	vertical-align: top;
}
table.sqlerror td div.debug {
	position: absolute;
	display: none;
	border: 2px solid rgba(0, 0, 0, 0.5);
	background: rgba(255, 255, 255, 0.9);
	padding: 5px;
	z-index: 2;
}
table.sqlerror td.q:hover div.debug,
table.sqlerror td.q div.debug:hover,
table.sqlerror td.q:hover div.debug:hover {
	display: block;
}
		</style>
		<table class="datagrid autoOddEven sqlerror">
			<thead>
				<tr>
					<th>ErrNo</th>
					<th>Error</th>
					<th>Query</th>
				</tr>
			</thead>
			<tbody>';
	foreach ($errors as $e) {
		echo '
				<tr>
					<td>
						'.$e['ErrNo'].'
					</td>
					<td>
						'.$e['Error'].'
					</td>
					<td class="q">
						<pre>'.htmlspecialchars($e['Query']).'</pre>
						<div class="debug">
							<pre>'.htmlspecialchars(print_r($e['Backtrace'], true)).'</pre>
						</div>
					</td>
				</tr>';
	}
	echo '
			</tbody>
		</table>';
}

function magnaGenerateNavStructure() {
	global $magnaConfig, $_modules;

	$alwaysDisplayedModules = array();
	foreach ($_modules as $key => $item) {
		if ($item['displayAlways']) {
			$alwaysDisplayedModules[$key] = true;
		}
	}

	if (!isset($magnaConfig['maranon']['Marketplaces'])
		|| !is_array($magnaConfig['maranon']['Marketplaces'])
	) {
		$magnaConfig['maranon']['Marketplaces'] = array();
	}
	$structure = array();

	$doinavtive = true;

	if (!empty($magnaConfig['maranon']['Marketplaces'])) {
		foreach ($magnaConfig['maranon']['Marketplaces'] as $mpID => $key) {
			$curItem = array ();
			if (!isset($_modules[$key])) {
				continue;
			}

			$item = $_modules[$key];

			$classes = array();
			if (array_key_exists($key, $alwaysDisplayedModules)) {
				unset($alwaysDisplayedModules[$key]);
			}
			if (array_key_exists('mp', $_GET) && ($_GET['mp'] == $mpID)) {
				$classes[] = 'selected';
			}
			$curItem['title'] = isset($item['subtitle'])
							? $item['subtitle']
							: $item['title'];
			$curItem['label'] = getDBConfigValue(array('general.tabident', $mpID), '0', '');
			$curItem['url']   = toURL(array('mp' => $mpID));
			$curItem['image'] = isset($item['logo'])
							? DIR_MAGNALISTER_WS_IMAGES.'logos/'.$item['logo'].'.png'
							: '';
			$curItem['class'] = implode(' ', $classes);
			$curItem['key']   = $key.'_'.$mpID;

			$structure[] = $curItem;
		}
	} else {
		$doinavtive = false;
	}
	if (!empty($alwaysDisplayedModules)) {
		$curModule = isset($_GET['module']) ? $_GET['module'] : '';
		foreach ($alwaysDisplayedModules as $key => $null) {
			$item = $_modules[$key];

			$classes = array();
			if ($curModule == $key) {
				$classes[] = 'selected';
			}
			if ((!isset($item['type']) || ($item['type'] != 'system')) && $doinavtive) {
				$classes[] = 'inactive';
			}
			$curItem = array();

			$curItem['title'] = isset($item['subtitle'])
							? $item['subtitle']
							: $item['title'];
			$curItem['label'] = isset($item['label'])
							? $item['label']
							: '';
			$curItem['url']   = toURL(array('module' => $key));
			$curItem['image'] = isset($item['logo'])
							? DIR_MAGNALISTER_WS_IMAGES.'logos/'.$item['logo'].'_inactive.png'
							: '';
			$curItem['class'] = implode(' ', $classes);
			$curItem['key']   = $key;

			$structure[] = $curItem;
		}
	}
	return $structure;
}

function magnaGenerateSideNav($args) {
	global $magnaConfig;

	$structure = magnaGenerateNavStructure();
	if (empty($structure)) {
		$structure = array (array (
			'class' => '',
			'key' => 'MAIN',
			'url' => toURL(),
			'title' => 'magnalister Admin',
		));
	}
	$html = '';

	switch (SHOPSYSTEM) {
		case 'gambio': {
			if (isset($args['out']) && ($args['out'] == 'xml')) {
				$base = toURL().'?';
				$sort = 0;
				$sLink = 'FILENAME_MAGNALISTER';
				$sLinkParam = '';

				if (defined('ML_GAMBIO_USE_IFRAME') && ML_GAMBIO_USE_IFRAME === true) {
					$sLink = 'admin.php';
					$sLinkParam = 'do=EmbeddedModule/magnalister&amp;';
				}

				foreach ($structure as $item) {
					$html .= '
						<menuitem link="'.$sLink.'" sort="'.(++$sort).'" '.
						         'link_param="'.$sLinkParam.str_replace($base, '', $item['url']).'" '.
						         'title="'.$item['title'].'"'.
						'></menuitem>';
				}
			} else {
				$html .= '
					<div class="leftmenu_head" '.
						'style="background-image:url('.DIR_MAGNALISTER_WS_IMAGES.'magnalister_gambio_icon.png)">'.
						'<a id="FILENAME_MAGNALISTER_TOP" href="' . toURL() . '" class="fav_drag_item ui-draggable">'.
						'Marketing</a></div>
					<div class="leftmenu_collapse leftmenu_collapse_opened"> </div>
					<ul class="leftmenu_box" id="BOX_MAGNALISTER_LINK">';
				foreach ($structure as $item) {
					$item['title'] .= !empty($item['label'])
						? ' :: '.strip_tags(str_replace(
							array('&lt;', '&gt;', '&quot;'),
							array('<', '>', '"'),
							fixHTMLUTF8Entities($item['label'])
						))
						: '';
					$html .= '<li class="leftmenu_body_item '.$item['class'].'">'.
						'<a id="BOX_MAGNA_'.strtoupper($item['key']).'" href="'.$item['url'].'" '.
							'class="fav_drag_item ui-draggable" '.
							'title="'.$item['title'].'">'.$item['title'].'</a></li>';
				}
				$html .= '</ul>';
			}
		}
	}
	return $html;

}

function renderDateTimePicker($key, $default = false, $alwaysDalete = false, $class = '') {
	$html = '';

	if (!empty($default)) {
		$default = strtotime($default);
		if ($default > 0) {
			$default = date('Y/m/d H:i:s', $default);
		} else {
			$default = '';
		}
		$default = date('Y/m/d H:i:s', strtotime($default));
		$deleteButton = false;
	} else {
		$deleteButton = true;
	}
	$idkey = str_replace(array('.', ' ', '[', ']'), '_', $key);

	if (array_key_exists('language_code', $_SESSION)) {
		$langCode = $_SESSION['language_code'];
	}
	if (!isset($langCode)) {
		$langCode = $_SESSION['language_code'] = MagnaDB::gi()->fetchOne('
			SELECT code FROM '.TABLE_LANGUAGES.'
			 WHERE languages_id="'.$_SESSION['languages_id'].'"
		');
	}

	$html .= '
		<script type="text/javascript" src="'.DIR_MAGNALISTER_WS.'js/jquery-ui-timepicker-addon.js"></script>
		<input type="text" id="'.$idkey.'_visual" value="" readonly="readonly" '.$class.'/>
		<input type="hidden" id="'.$idkey.'" name="'.$key.'" value="'.str_replace('/', '-', $default).'"/>
		'.(($deleteButton || $alwaysDalete) ? '<span class="gfxbutton small delete '.$class.'" id="'.$idkey.'_reset"></span>' : '').'
		<script type="text/javascript">/*<![CDATA[*/
			$(document).ready(function() {
				jQuery.datepicker.setDefaults(jQuery.datepicker.regional[\'\']);
				jQuery.timepicker.setDefaults(jQuery.timepicker.regional[\'\']);
				$("#'.$idkey.'_visual").datetimepicker(
					jQuery.extend(
						jQuery.datepicker.regional[\''.$langCode.'\'],
						jQuery.timepicker.regional[\''.$langCode.'\']
					)
				).datetimepicker("option", {
					onClose:  function(dateText, inst) {
						var d = $("#'.$idkey.'_visual").datetimepicker("getDate"),
							s = null;
						if (d === null) {
							return;
						}
						s = jQuery.datepicker.formatDate("yy-mm-dd", d)+\' \'+
							jQuery.datepicker.formatTime("HH:mm:ss", {
								hour: d.getHours(),
								minute: d.getMinutes(),
								second: d.getSeconds()
							}, { ampm: false });
						$("#'.$idkey.'").val(s);
					}
				})'.(!empty($default) ? '.datetimepicker(
					"setDate", new Date(\''.$default.'\')
				)' : '').';
				$("#'.$idkey.'_reset").click(function() {
					$("#'.$idkey.'_visual").val(\'\');
					$("#'.$idkey.'").val(\'\');
				});
			});
		/*]]>*/</script>'."\n";
	return $html;
}

/**
 *clean corrupted data from prepare tables
 * @deprecated since new productlist-filters. they should handle date correctlys
 * @bug queries dont take care about mpid.So prepare data will deleted for other mp's
 */
function cleanPrepareData() {
    return;
	$aQueries = array();
	$sIdent = (getDBConfigValue('general.keytype', '0') == 'artNr')
		? 'products_model'
		: 'products_id';

	/* Amazon */
	// Both amazon prepare tables may have corrupted data - delete the invalid rows.
	// Delete data from magnalister_amazon_apply if incomplete=true and same entry exists in magnalister_amazon_properties
	$aIdents = MagnaDB::gi()->fetchArray("SELECT ".$sIdent." FROM ".TABLE_MAGNA_AMAZON_PROPERTIES, true);
	if (!empty($aIdents)) {
		foreach ($aIdents as $key => $aIdent) {
			$aIdents[$key] = MagnaDB::gi()->escape($aIdent);
		}
		MagnaDB::gi()->query("
			DELETE FROM ".TABLE_MAGNA_AMAZON_APPLY."
			 WHERE is_incomplete='true'
			       AND ".$sIdent." IN ('".implode("', '", $aIdents)."')
		");
	}

	// Delete data from magnalister_properties if same entry exists in magnalister_amazon_apply
	$aIdents = MagnaDB::gi()->fetchArray("SELECT " . $sIdent . " FROM ".TABLE_MAGNA_AMAZON_APPLY, true);
	if (!empty($aIdents)) {
		foreach ($aIdents as $key => $aIdent) {
			$aIdents[$key] = MagnaDB::gi()->escape($aIdent);
		}
		MagnaDB::gi()->query("
			DELETE FROM ".TABLE_MAGNA_AMAZON_PROPERTIES."
			 WHERE ".$sIdent." IN ('".implode("', '", $aIdents)."')
		");
	}

	// Finally delete data from magnalister_amazon_apply again where no ean can be found.
	// Disabled for now. you can specify the ean manually.
	if (false && defined('MAGNA_FIELD_ATTRIBUTES_EAN')) {
		$aAttributes = MagnaDB::gi()->fetchArray("
		    SELECT DISTINCT products_id
		      FROM ".TABLE_PRODUCTS_ATTRIBUTES."
		     WHERE ".MAGNA_FIELD_ATTRIBUTES_EAN."<>''
		           AND ".MAGNA_FIELD_ATTRIBUTES_EAN." IS NOT NULL
		", true);
		$aIdents = MagnaDB::gi()->fetchArray("
		    SELECT DISTINCT ".$sIdent."
		      FROM ".TABLE_PRODUCTS." p
		     WHERE (
		                   p.".MAGNA_FIELD_PRODUCTS_EAN."=''
		                OR p.".MAGNA_FIELD_PRODUCTS_EAN." IS NULL
		           )
		        ".(!empty($aAttributes)
		            ? "AND products_id NOT IN ('".implode("', '", $aAttributes)."')"
		            : ''
		        )
		, true);
		if (!empty($aIdents)) {
			foreach ($aIdents as $key => $aIdent) {
				$aIdents[$key] = MagnaDB::gi()->escape($aIdent);
			}
			MagnaDB::gi()->query("
			    DELETE FROM ".TABLE_MAGNA_AMAZON_APPLY." WHERE ".$sIdent." IN ('".implode("', '", $aIdents)."')
			");
		}
	}
}

/*
 * case: product_model has changed, items were prepared with others,
 * we have multiple entries for one product.
 *
 * @param string $prepareTable
 * @param int $mpID
 *
 * still ToDo: Case when the product has been deleted from shop
 * (does no harm, but then the prepare record is trash)
 */
function removeDoublePrepareEntries($prepareTable, $mpID) {
    $sIdent = (getDBConfigValue('general.keytype', '0') == 'artNr')
        ? 'products_model'
        : 'products_id';

    $sInactiveIdent = ($sIdent == 'products_model') 
        ? 'products_id'
        : 'products_model';
    
    $aDoubleEntries = MagnaDB::gi()->fetchArray('SELECT '.$sIdent.', COUNT(*) cnt
         FROM '.$prepareTable.'
        WHERE mpID='.$mpID.' 
        GROUP BY '.$sIdent.'
        HAVING cnt>1');
    if (empty($aDoubleEntries)) return;

    $aDoubleEntriesList = '';
    if ('products_id' == $sIdent) {
        foreach ($aDoubleEntries as $aDoubleEntry) {
            $aDoubleEntriesList .= ', '.$aDoubleEntry[$sIdent];
        }
    } else {
        foreach ($aDoubleEntries as $aDoubleEntry) {
            $aDoubleEntriesList .= ", '".MagnaDB::gi()->escape($aDoubleEntry[$sIdent])."'";
        }
    }
    $aDoubleEntriesList = ltrim($aDoubleEntriesList, ', ');

    $aProductsIdents = MagnaDB::gi()->fetchArray('SELECT products_id,products_model
         FROM '.TABLE_PRODUCTS.'
        WHERE '.$sIdent.' IN ('.$aDoubleEntriesList.')');

    foreach ($aProductsIdents as $aProductsIdent) {
        // if we have a record with the right idents, delete the other(s)
        if (MagnaDB::gi()->recordExists($prepareTable, array (
                'mpID' => $mpID,
                'products_id' => $aProductsIdent['products_id'],
                'products_model' => $aProductsIdent['products_model'],
            ))) {
             MagnaDB::gi()->query('DELETE FROM '.$prepareTable.'
                 WHERE '.$sIdent.' = \''.MagnaDB::gi()->escape($aProductsIdent[$sIdent]).'\'
                 AND '.$sInactiveIdent.' <> \''.MagnaDB::gi()->escape($aProductsIdent[$sInactiveIdent]).'\'
                 AND mpID ='.$mpID);
        } else {
        // if we don't have a record with the right idents, delete all but one,
        // and update one to the right idents
             // count?
             $entries = 2;
             foreach ($aDoubleEntries as $aDoubleEntry) {
                 if ($aDoubleEntry[$sIdent] == $aProductsIdent[$sIdent]) {
                      $entries = $aDoubleEntry['cnt'];
                      break;
                 }
             }
             $iSurplusEntries = (int)($entries - 1);
             MagnaDB::gi()->query('DELETE FROM '.$prepareTable.'
                 WHERE '.$sIdent.' = \''.MagnaDB::gi()->escape($aProductsIdent[$sIdent]).'\'
                 AND '.$sInactiveIdent.' <> \''.MagnaDB::gi()->escape($aProductsIdent[$sInactiveIdent]).'\'
                 AND mpID ='.$mpID.'
                 LIMIT '.$iSurplusEntries );
             MagnaDB::gi()->query('UPDATE '.$prepareTable.'
                 SET '.$sInactiveIdent.' = \''.MagnaDB::gi()->escape($aProductsIdent[$sInactiveIdent]).'\'
                 WHERE '.$sIdent.' = \''.MagnaDB::gi()->escape($aProductsIdent[$sIdent]).'\'
                 AND mpID ='.$mpID);
        }
    }
}

function magnaGetDefaultLanguageID() {
    if (defined('DEFAULT_LANGUAGE')) {
        $lID = MagnaDB::gi()->fetchOne("
            SELECT `languages_id`
              FROM ".TABLE_LANGUAGES." l
             WHERE l.`code` = '".DEFAULT_LANGUAGE."'
        ");
    } else {
        if (defined('ML_GAMBIO_41_NEW_CONFIG_TABLE')) {
            $lID = MagnaDB::gi()->fetchOne("
                SELECT `languages_id`
                  FROM ".TABLE_LANGUAGES." l, ".TABLE_CONFIGURATION." c
                 WHERE     l.`code` = c.`value`
                       AND c.`key` = 'configuration/DEFAULT_LANGUAGE'
                 LIMIT 1
            ");
        } else {
            $lID = MagnaDB::gi()->fetchOne("
                SELECT `languages_id`
                  FROM ".TABLE_LANGUAGES." l, ".TABLE_CONFIGURATION." c 
                 WHERE     l.`code` = c.`configuration_value` 
                       AND c.`configuration_key` = 'DEFAULT_LANGUAGE'
                LIMIT 1
            ");
        }
	}
	if ($lID == false) {
		/* very bad fallback, but one fallback at least. */
		$lID = MagnaDB::gi()->fetchOne('
			SELECT languages_id
			  FROM '.TABLE_LANGUAGES.'
			 LIMIT 1
		');
	}
	return $lID;
}

/**
 * @param $sCountryIso
 * @return int
 */
function magnaGetLanguageIdByCountryIso($sCountryIso) {
	$sLangCode = getLanguageIsoForCountryIso($sCountryIso);
	$iLangId = MagnaDB::gi()->fetchOne("
		SELECT languages_id
		  FROM ".TABLE_LANGUAGES."
		 WHERE code = '".$sLangCode."'
		 LIMIT 1
	");

	if ($iLangId === false) {
		return magnaGetDefaultLanguageID();
	}

	return $iLangId;
}

/**
 * @return array(int id => string groupname)
 */
function magnaGetCustomerGroups() {
	if (!isset($_SESSION['languages_id'])) {
		$_SESSION['languages_id'] = magnaGetDefaultLanguageID();
	}
	$customers_status_array = MagnaDB::gi()->fetchArray(
		'SELECT customers_status_id, customers_status_name '.
		'FROM '.TABLE_CUSTOMERS_STATUS.' '.
		'WHERE language_id = \''.$_SESSION['languages_id'].'\''
	);
	$aRet = array();
	foreach ($customers_status_array as $item) {
		$aRet[$item['customers_status_id']] = fixHTMLUTF8Entities($item['customers_status_name']);
	}
	return $aRet;
}

/**
 * Returns the offset from the origin timezone to the remote timezone, in seconds.
 * @param $remote_tz
 * @param $origin_tz	If null the servers current timezone is used as the origin.
 * @return 	The offset in seconds as int or false incase of a failure.
 */
function magnaGetTimezoneOffset($remote_tz, $origin_tz = null) {
	if ($origin_tz === null) {
		$origin_tz = @date('e');
	}
	if (!class_exists('DateTimeZone')) {
		global $_MagnaSession;
		if (isset($_MagnaSession['TimezoneOffset'])) {
			return $_MagnaSession['TimezoneOffset'];
		}
		try {
			$response = MagnaConnector::gi()->submitRequest(array (
				'ACTION' => 'GetTimezoneOffset',
				'SUBSYSTEM' => 'Core',
				'FROM' => $remote_tz,
				'TO' => $origin_tz,
			));
			$_MagnaSession['TimezoneOffset'] = (int)$response['DATA'];
			return $_MagnaSession['TimezoneOffset'];
		} catch (MagnaException $me) {
			return false;
		}
	}
	try {
		$origin_dtz = new DateTimeZone($origin_tz);
		$remote_dtz = new DateTimeZone($remote_tz);
	} catch (Exception $e) {
		return false;
	}
	$origin_dt = new DateTime("now", $origin_dtz);
	$remote_dt = new DateTime("now", $remote_dtz);
	$offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);
	return $offset;
}

/**
 * Returns a local time
 * which matches a given time on the magnalister server
 * If offset cannot be determined, return = input
 * @param $magnaTimeval  (YYYY-mm-dd HH:MM:ss)
 * @return $localTimeval (YYYY-mm-dd HH:MM:ss)
 */
function magnaTimeToLocalTime($magnaTimeval, $reverse = false) {
	$offset = magnaGetTimezoneOffset('Europe/Berlin');
	if (false == $offset) {
		return $magnaTimeval;
	}
	if ($reverse) {
		$offset = (int)((-1) * $offset);
	}
	return date('Y-m-d H:i:s', mktime(
		substr($magnaTimeval, 11,2), substr($magnaTimeval, 14,2), substr($magnaTimeval, 17,2),
		substr($magnaTimeval, 5,2),  substr($magnaTimeval, 8,2),  substr($magnaTimeval, 0,4)
		) + $offset);
}

function localTimeToMagnaTime($localTimeval) {
	# call magnaTimeToLocalTime with reverse == true
	return magnaTimeToLocalTime($localTimeval, true);
}

function ml_extractBase64(&$in) {
	$matches = array();
	$out = array();
	if (preg_match_all('/="(data:[^"]+)"/', $in, $matches)) {
		$i = 1;
		foreach ($matches[1] as $m) {
			$phold = '{#Base64_'.($i++).'#}';
			$in = str_replace($m, $phold, $in);
			$out[$phold] = $m;
		}
	}
	return $out;
}

function ml_restoreBase64($in, $baseIndex) {
	if (empty($baseIndex)) {
		return $in;
	}
	return str_replace(array_keys($baseIndex), array_values($baseIndex), $in);
}

function ml_setMinRam($newRam) {
	/* RAM Check */
	$curRam = @ini_get('memory_limit').'';
	$curRamBytes = convert2Bytes($curRam);
	if ($curRam == 0) {
		return false;
	}
	$newRamBytes = convert2Bytes($newRam);

	if ($curRamBytes < $newRamBytes) {
		@ini_set('memory_limit', filesize2human($newRamBytes, 0, true));
	}

	return $newRam;
}

function mlLoadModuleLanguageDefines($sModulePath) {
	if (empty($sModulePath)) {
		return;
	}

	// check for params and its an actual gambio with LanguageTextManager
	if (SHOPSYSTEM == 'gambio' && class_exists('MainFactory') && (($sVersion = mlGetGambioShopSystemVersion()) !== false)) {
		if (version_compare($sVersion, '2.1', '>=')) {
			if (defined('DIR_FS_LANGUAGES')) {
				$aReplace[] = DIR_FS_LANGUAGES;
			}
			$aReplace[] = DIR_MAGNA_LANGUAGES;
			$sSpecialModulePath = str_replace($aReplace, 'lang/', $sModulePath);
			$oLTM = MainFactory::create_object('LanguageTextManager', array(), true);
			if (is_object($oLTM) && method_exists($oLTM, 'init_from_lang_file')) {
				$oLTM->init_from_lang_file($sSpecialModulePath, $_SESSION['languages_id']);
				// return because defines are loaded
				return;
			}
		}
	}

	if (file_exists($sModulePath)) {
		try {
			require_once($sModulePath);
		} catch (Exception $e) {}
	}
}

function mlShopBookAnAddOn($args) {
	if (!isset($args['SKU'])) {
		$args['SKU'] = '';
	}

	require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/ShopAddOns.php');

	return ML_ShopAddOns::bookAddOn($args['SKU']);
}

/**
 *
 * @param string $sUnit
 * @return real|int
 * @throws Exception
 */
function mlGetWeightRate($sUnit) {
	switch (strtolower($sUnit)) {
		case 'g': case 'gr' :case'gm':case'gram': return 1000;
		case 'kg':case 'kilogram': return 1;
		case 'oz':case 'ounce': return 35.273966;
		case 'lb':case 'lbm' :case 'pound': return 2.204623;
		case 'st': return 0.157473;
		default : throw new Exception('unit is not supported');
	}
}

/**
 *
 * @param float $fWeight
 * @param string $sUnitFrom unit should be converted from
 * @param string $sUnitTo unit should be converted to
 * @return float
 */
function mlConvertWeight($fWeight, $sUnitFrom, $sUnitTo) {
	try {
		$fRateFrom = mlGetWeightRate($sUnitFrom);
		$fRateTo = mlGetWeightRate($sUnitTo);
		return $fWeight * $fRateTo / $fRateFrom;
	} catch (Exception $oEx) {
		return null;
	}
}
