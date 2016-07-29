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
 * $Id: apply.php 6005 2015-09-17 08:12:42Z masoud.khodaparast $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_MODULES . 'amazon/amazonFunctions.php');

function amazonSanitizeDesc($str) {
	# preg_replace could return NULL at 5.2.0 to 5.3.6 - "/(\s*<br[^>]*>\s*)*$/"
	# tested at: http://3v4l.org/WGcod
	if (version_compare(PHP_VERSION, '5.2.0', '>=') && version_compare(PHP_VERSION, '5.3.6', '<=')) {
		@ini_set('pcre.backtrack_limit', '10000000');
		@ini_set('pcre.recursion_limit', '10000000');
	}
	$str = str_replace(array('&nbsp;', html_entity_decode('&nbsp;')), ' ', $str);
	$str = sanitizeProductDescription(
		$str,
		'',
		'_keep_all_'
	);
	$str = str_replace(array('<br />', '<br/>'), '<br>', $str);
	$str = preg_replace('/(\s*<br[^>]*>\s*)*$/', '', $str);
	$str = preg_replace('/\s\s+/', ' ', $str);
	return substr($str, 0, 2000);
}

function populateGenericData($pID, $edit = false) {
	global $_MagnaSession;

	$genericDataStructure = array(
		'MainCategory' => '',
		'ProductType' => '',
		'BrowseNodes' => array(),
		'ItemTitle' => '',
		'Manufacturer' => '',
		'Brand' => '',
		'ManufacturerPartNumber' => '',
		'EAN' => '',
		'Images' => array(),
		'BulletPoints' => array('', '', '', '', ''),
		'Description' => '',
		'Keywords' => array('', '', '', '', ''),
		'Attributes' => array(),
		'LeadtimeToShip' => getDBConfigValue('amazon.leadtimetoship', $_MagnaSession['mpID'], '0'),
		'ConditionType' => getDBConfigValue('amazon.itemCondition', $_MagnaSession['mpID'], '0'),
		'ConditionNote' => '',
	);
	if ($pID === 0) {
		if ($edit) {
			$genericDataStructure['LeadtimeToShip'] = 'X';
		}
		return $genericDataStructure;
	}
	$product = MLProduct::gi()->getProductByIdOld(
		$pID, getDBConfigValue('amazon.lang', $_MagnaSession['mpID'], $_SESSION['languages_id'])
	);
	if ($product === false) {
		return $genericDataStructure;
	}
	if ($product['manufacturers_id'] > 0) {
		$genericDataStructure['Manufacturer'] = $genericDataStructure['Brand'] = MagnaDB::gi()->fetchOne('
			SELECT manufacturers_name 
			  FROM ' . TABLE_MANUFACTURERS . '
			 WHERE manufacturers_id=\'' . $product['manufacturers_id'] . '\'
		');
	}
	if (empty($genericDataStructure['Manufacturer'])) {
		$genericDataStructure['Manufacturer'] = $genericDataStructure['Brand'] = getDBConfigValue(
			'amazon.prepare.manufacturerfallback', $_MagnaSession['mpID'], ''
		);
	}
	$mfrmd = getDBConfigValue('amazon.prepare.manufacturerpartnumber.table', $_MagnaSession['mpID'], false);
	if (is_array($mfrmd) && !empty($mfrmd['column']) && !empty($mfrmd['table'])) {
		$pIDAlias = getDBConfigValue('amazon.prepare.manufacturerpartnumber.alias', $_MagnaSession['mpID']);
		if (empty($pIDAlias)) {
			$pIDAlias = 'products_id';
		}
		$genericDataStructure['ManufacturerPartNumber'] = MagnaDB::gi()->fetchOne('
			SELECT `' . $mfrmd['column'] . '`
			  FROM `' . $mfrmd['table'] . '`
			 WHERE `' . $pIDAlias . '`=\'' . MagnaDB::gi()->escape($pID) . '\'
			 LIMIT 1
		');
	}
	if (!empty($product['products_allimages'])) {
		foreach ($product['products_allimages'] as $img) {
			$genericDataStructure['Images'][$img] = 'true';
		}
	}
	$genericDataStructure['ItemTitle'] = $product['products_name'];
	$genericDataStructure['EAN'] = $product[MAGNA_FIELD_PRODUCTS_EAN];
	$genericDataStructure['Description'] = amazonSanitizeDesc($product['products_description']);

	$trimFunc = create_function('&$v, $k', '$v = trim($v);');

	$product['products_meta_description'] = explode(',', $product['products_meta_description']);
	array_walk($product['products_meta_description'], $trimFunc);
	$genericDataStructure['BulletPoints'] = array_slice($product['products_meta_description'], 0, 5);
	# Laenge auf 500 Zeichen beschraenken
	if (!empty($genericDataStructure['BulletPoints'])) {
		foreach ($genericDataStructure['BulletPoints'] as &$bullet) {
			$bullet = trim($bullet);
			if (empty($bullet)) continue;
			$bullet = substr($bullet, 0, strpos(wordwrap($bullet, 500, "\n", true) . "\n", "\n"));
		}
	}
	$genericDataStructure['BulletPoints'] = array_pad($genericDataStructure['BulletPoints'], 5, '');

	$product['products_meta_keywords'] = explode(',', $product['products_meta_keywords']);
	array_walk($product['products_meta_keywords'], $trimFunc);
	$genericDataStructure['Keywords'] = array_slice($product['products_meta_keywords'], 0, 5);
	# Laenge auf 50 Zeichen beschraenken
	if (!empty($genericDataStructure['Keywords'])) {
		foreach ($genericDataStructure['Keywords'] as &$keyword) {
			$keyword = trim($keyword);
			if (empty($keyword)) continue;
			$keyword = substr($keyword, 0, strpos(wordwrap($keyword, 50, "\n", true) . "\n", "\n"));
		}
	}
	$genericDataStructure['Keywords'] = array_pad($genericDataStructure['Keywords'], 5, '');

	$prepData = MagnaDB::gi()->fetchRow('
		SELECT category, data, leadtimeToShip, ConditionType, ConditionNote
		  FROM ' . TABLE_MAGNA_AMAZON_APPLY . '
		 WHERE mpID=\'' . $_MagnaSession['mpID'] . '\' AND
		       ' . ((getDBConfigValue('general.keytype', '0') == 'artNr')
			? 'products_model=\'' . MagnaDB::gi()->escape($product['products_model']) . '\''
			: 'products_id = \'' . $pID . '\''
		) . '
		 LIMIT 1
	');
	$dbData = false;
	if (!empty($prepData)) {
		$dbData = $prepData['data'];
	}

	# Folgende 3 Zeilen auskommentieren, falls die bereits gespeicherten Produktdaten zur Beantragung
	# nicht ueberschrieben werden sollen.
	if (!$edit) {
		$dbData = false;
	}

	if ($dbData !== false) {
		$prepData['category'] = base64_decode($prepData['category']);
		$prepData['category'] = unserialize($prepData['category']);
		$dbData = base64_decode($dbData);
		$dbData = unserialize($dbData);
		if (is_array($prepData['category']) && !empty($prepData['category'])
			&& is_array($dbData) && !empty($dbData)
		) {
			$existingImages = $genericDataStructure['Images'];
			$genericDataStructure = array_merge(
				$genericDataStructure,
				$prepData['category'],
				$dbData
			);
			$savedImages = $genericDataStructure['Images'];
			if (!empty($existingImages)) {
				foreach ($existingImages as $img => $checked) {
					$genericDataStructure['Images'][$img] = (
					(is_array($savedImages) && array_key_exists($img, $savedImages)) ? $savedImages[$img] : 'false'
					);
				}
			}
		}
		$genericDataStructure['LeadtimeToShip'] = $prepData['leadtimeToShip'];
		$genericDataStructure['ConditionType'] = $prepData['ConditionType'];
		$genericDataStructure['ConditionNote'] = $prepData['ConditionNote'];
	}

	/* {Hook} "AmazonApply_populateGenericData": Enables you to extend or modifiy the product data.<br>
	   Variables that can be used: 
	   <ul><li>$pID: The ID of the product (Table <code>products.products_id</code>).</li>
		   <li>$product: The data of the product (Tables <code>products</code>, <code>products_description</code>,
			           <code>products_images</code> and <code>products_vpe</code>).</li>
		   <li>$genericDataStructure: The additional recommenced data of the product for Amazon (MainCategory, ProductType, BrowseNodes, ItemTitle, Manufacture, Brand, ManufacturerPartNumber, EAN, Images, BulletPoints, Description, Keywords, Attributes, LeadtimeToShip)</li>
	   </ul>
	 */
	if (($hp = magnaContribVerify('AmazonApply_populateGenericData', 1)) !== false) {
		require($hp);
	}


	//echo print_m($genericDataStructure);
	return $genericDataStructure;
}

$_url['view'] = 'apply';
$applySetting = array(
	'selectionName' => 'apply'
);

$applyAction = 'categoryview';

setDBConfigValue(
	array(
		$_MagnaSession['currentPlatform'] . '.' . $applySetting['selectionName'] . '.status', 'val'
	),
	$_MagnaSession['mpID'],
	getDBConfigValue(array($_MagnaSession['currentPlatform'] . '.matching.status', 'val'), $_MagnaSession['mpID'], false)
);

if (!empty($_POST) && (!isset($_GET['kind']) || ($_GET['kind'] != 'ajax'))) {
//	echo print_m($_POST);
//	echo var_export_pre($_POST, '$_POST');
}

if (!empty($_POST) && isset($_GET['kind']) && ($_GET['kind'] == 'ajax')) {
	if (isset($_GET['applyAction'])) {
		$applyAction = $_GET['applyAction'];
	}
}

/**
 * Daten speichern
 */
if (array_key_exists('saveApplyData', $_POST)) {
	$requiredData = array(
		'MainCategory' => ML_LABEL_MAINCATEGORY,
		#'ProductType' => ML_LABEL_SUBCATEGORY,
		'BrowseNodes' => ML_AMAZON_LABEL_APPLY_BROWSENODES,
		'ItemTitle' => ML_LABEL_PRODUCT_NAME,
		'Manufacturer' => ML_GENERIC_MANUFACTURER_NAME,
	);

	$itemDetails = $_POST;
	unset($itemDetails['saveApplyData']);

	$leadtimePost = $itemDetails['LeadtimeToShip'];
	unset($itemDetails['LeadtimeToShip']);

	$pIDs = MagnaDB::gi()->fetchArray('
		SELECT pID FROM ' . TABLE_MAGNA_SELECTION . '
		 WHERE mpID=\'' . $_MagnaSession['mpID'] . '\' AND
		       selectionname=\'' . $applySetting['selectionName'] . '\' AND
		       session_id=\'' . session_id() . '\'
	', true);
	if (!empty($pIDs)) {
		$missingItems = array();
		$preparedTs = date('Y-m-d H:i:s');
		foreach ($pIDs as $pID) {
			$data = array_merge(
				populateGenericData($pID),
				$itemDetails
			);
			arrayEntitiesToUTF8($data);

			$leadtimeToShip = $leadtimePost;
			if ($leadtimeToShip == 'X') {
				$leadtimeToShip = $data['LeadtimeToShip'];
			}

			foreach ($requiredData as $key => $title) {
				if (empty($data[$key]) || ($data[$key] == 'null')) {
					$missingItems[$pID][] = $title;
				}
			}

			$productModel = MagnaDB::gi()->fetchOne('
				SELECT products_model
				  FROM ' . TABLE_PRODUCTS . '
				 WHERE products_id=' . $pID
			);

			$c = array(
				'MainCategory' => $data['MainCategory'],
				'ProductType' => $data['ProductType'],
				'BrowseNodes' => $data['BrowseNodes'],
				'ConditionType' => $data['ConditionType'],
				'ConditionNote' => $data['ConditionNote']
			);
			unset($data['MainCategory']);
			unset($data['ProductType']);
			unset($data['BrowseNodes']);
			unset($data['ConditionType']);
			unset($data['ConditionNote']);

			#echo print_m($missingItems);
			$data = array(
				'mpID' => $_MagnaSession['mpID'],
				'products_id' => $pID,
				'products_model' => $productModel,
				'category' => base64_encode(serialize($c)),
				'data' => base64_encode(serialize($data)),
				'is_incomplete' => array_key_exists($pID, $missingItems) ? 'true' : 'false',
				'leadtimeToShip' => $leadtimeToShip,
				'topMainCategory' => $c['MainCategory'] == null ? '' : $c['MainCategory'],
				'topProductType' => $c['ProductType'] == null ? '' : $c['ProductType'],
				'topBrowseNode1' => $c['BrowseNodes'][0] == null ? '' : $c['BrowseNodes'][0],
				'topBrowseNode2' => (!isset($c['BrowseNodes'][1]) || ($c['BrowseNodes'][1] == null)) ? '' : $c['BrowseNodes'][1],
				'ConditionType' => $c['ConditionType'],
				'ConditionNote' => $c['ConditionNote'],
				'PreparedTs' => $preparedTs,
			);
			$where = (getDBConfigValue('general.keytype', '0') == 'artNr')
				? array('products_model' => MagnaDB::gi()->escape($data['products_model']))
				: array('products_id' => $data['products_id']);
			$where['mpID'] = $_MagnaSession['mpID'];

			$swhere = 'WHERE ';
			foreach ($where as $key => $value) {
				$swhere .= '`' . $key . '` = \'' . $value . '\' AND ';
			}
			$swhere = rtrim($swhere, ' AND ');

			if (($count = (int)MagnaDB::gi()->fetchOne('SELECT count(*) FROM `' . TABLE_MAGNA_AMAZON_APPLY . '` ' . $swhere)) > 0) {
				if ($count > 1) {
					MagnaDB::gi()->query('DELETE FROM `' . TABLE_MAGNA_AMAZON_APPLY . '` ' . $swhere . ' LIMIT ' . ($count - 1));
				}
				MagnaDB::gi()->update(TABLE_MAGNA_AMAZON_APPLY, $data, $where);
			} else {
				MagnaDB::gi()->insert(TABLE_MAGNA_AMAZON_APPLY, $data);
			}
			if (!array_key_exists($pID, $missingItems)) {
				MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
					'pID' => $pID,
					'mpID' => $_MagnaSession['mpID'],
					'selectionname' => $applySetting['selectionName'],
					'session_id' => session_id()
				));
			}
		}
		if (!empty($missingItems)) {
			echo '
				<p class="noticeBox">' . ML_AMAZON_TEXT_APPLY_DATA_INCOMPLETE . '</p>
				<table class="datagrid">
					<thead><tr>
						<th>' . ML_LABEL_PRODUCTS_ID . '</th>
						<th>' . ML_LABEL_ARTICLE_NUMBER . '</th>
						<th>' . ML_LABEL_PRODUCT_NAME . '</th>
						<th>' . ML_AMAZON_LABEL_MISSING_FIELDS . '</th>
						<th>&nbsp;</th>
					</tr></thead>
					<tbody>';
			$oddEven = true;
			$i = 0;
			foreach ($missingItems as $pID => $items) {
				if ($i > 20) {
					echo '
						<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
							<td colspan="5" class="textcenter bold">&hellip;</td>
						</tr>';
					break;
				}
				$product = MLProduct::gi()->getProductByIdOld($pID);
				echo '
						<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
							<td>' . $pID . '</td>
							<td>' . $product['products_model'] . '</td>
							<td>' . $product['products_name'] . '</td>
							<td>' . implode(',', $items) . '</td>
							<td><a class="gfxbutton edit" title="bearbeiten" target="_blank" href="' . toURL($_url, array('edit' => $pID)) . '">&nbsp;</a></td>
						</tr>';
				++$i;
			}
			echo '
					</tbody>
				</table>';
		}
	}
}

if (!defined('MAGNA_DEV_PRODUCTLIST') || MAGNA_DEV_PRODUCTLIST !== true) { // will be done in MLProductListDependencyAmazonApplyFormAction
	/**
	 * Daten loeschen
	 */
	if (array_key_exists('removeapply', $_POST)) {
		$pIDs = MagnaDB::gi()->fetchArray('
			SELECT pID FROM ' . TABLE_MAGNA_SELECTION . '
			 WHERE mpID=\'' . $_MagnaSession['mpID'] . '\' AND
				   selectionname=\'' . $applySetting['selectionName'] . '\' AND
				   session_id=\'' . session_id() . '\'
		', true);
		if (!empty($pIDs)) {
			foreach ($pIDs as $pID) {
				$where = (getDBConfigValue('general.keytype', '0') == 'artNr')
					? array('products_model' => MagnaDB::gi()->fetchOne('
								SELECT products_model
								  FROM ' . TABLE_PRODUCTS . '
								 WHERE products_id=' . $pID
						))
					: array('products_id' => $pID);
				$where['mpID'] = $_MagnaSession['mpID'];

				MagnaDB::gi()->delete(TABLE_MAGNA_AMAZON_APPLY, $where);
				MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
					'pID' => $pID,
					'mpID' => $_MagnaSession['mpID'],
					'selectionname' => $applySetting['selectionName'],
					'session_id' => session_id()
				));
			}
		}
	}

	/**
	 * Daten zuruecksetzen
	 */
	if (array_key_exists('resetapply', $_POST)) {
		$pIDs = MagnaDB::gi()->fetchArray('
			SELECT pID FROM ' . TABLE_MAGNA_SELECTION . '
			 WHERE mpID=\'' . $_MagnaSession['mpID'] . '\' AND
				   selectionname=\'' . $applySetting['selectionName'] . '\' AND
				   session_id=\'' . session_id() . '\'
		', true);
		if (!empty($pIDs)) {
			if (getDBConfigValue('general.keytype', '0') == 'artNr') {
				$aProducts = MagnaDB::gi()->fetchArray('
					SELECT aa.products_id AS PID, aa.products_model AS PModel, aa.* 
					  FROM ' . TABLE_MAGNA_AMAZON_APPLY . ' aa
					 WHERE aa.products_id IN (\'' . implode('\', \'', $pIDs) . '\')
				');
			} else {
				$aProducts = MagnaDB::gi()->fetchArray('
					SELECT p.products_id AS PID, p.products_model AS PModel, aa.*
					  FROM ' . TABLE_MAGNA_AMAZON_APPLY . ' aa
				INNER JOIN ' . TABLE_PRODUCTS . ' p ON p.products_model=aa.products_model
					 WHERE p.products_id IN (\'' . implode('\', \'', $pIDs) . '\')
				');
			}
			foreach ($aProducts as $aRow) {
				$aRow['category'] = unserialize(base64_decode($aRow['category']));
				$aRow['data'] = unserialize(base64_decode($aRow['data']));
				if (!is_array($aRow['data']) || empty($aRow['data'])) {
					continue;
				}
				#echo print_m($aRow);

				$aNewRow = populateGenericData($aRow['PID']);
				#echo print_m($aNewRow);

				unset($aNewRow['MainCategory']);
				unset($aNewRow['ProductType']);
				unset($aNewRow['BrowseNodes']);

				$aNewRow['Attributes'] = $aRow['data']['Attributes'];
				if ($aRow['leadtimeToShipFrozen'] > 0) {
					$aNewRow['LeadtimeToShip'] = $aRow['leadtimeToShip'];
				}

				$where = (getDBConfigValue('general.keytype', '0') == 'artNr')
					? array('products_model' => $aRow['PModel'])
					: array('products_id' => $aRow['PID']);
				$where['mpID'] = $_MagnaSession['mpID'];

				MagnaDB::gi()->update(TABLE_MAGNA_AMAZON_APPLY, array(
					'products_id' => $aRow['PID'],
					'products_model' => $aRow['PModel'],
					'data' => base64_encode(serialize($aNewRow)),
					'leadtimeToShip' => $aNewRow['LeadtimeToShip']
				), $where);

				MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
					'pID' => $aRow['PID'],
					'mpID' => $_MagnaSession['mpID'],
					'selectionname' => $applySetting['selectionName'],
					'session_id' => session_id()
				));
			}
		}
	}
}

if (isset($_GET['edit']) && MagnaDB::gi()->recordExists(TABLE_MAGNA_AMAZON_APPLY, array(
		'mpID' => $_MagnaSession['mpID'],
		'products_id' => (int)$_GET['edit']
	))
) {
	MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
		'mpID' => $_MagnaSession['mpID'],
		'selectionname' => $applySetting['selectionName'],
		'session_id' => session_id()
	));
	MagnaDB::gi()->insert(TABLE_MAGNA_SELECTION, array(
		'pID' => (int)$_GET['edit'],
		'data' => serialize(array()),
		'mpID' => $_MagnaSession['mpID'],
		'selectionname' => $applySetting['selectionName'],
		'session_id' => session_id(),
		'expires' => gmdate('Y-m-d H:i:s')
	));
	$_POST['apply'] = 'EDITMODE';
}

/**
 * Beantragen Vorbereitung
 */
if (array_key_exists('apply', $_POST) && (!empty($_POST['apply']))) {
	$itemCount = (int)MagnaDB::gi()->fetchOne('
		SELECT count(*) FROM ' . TABLE_MAGNA_SELECTION . '
		 WHERE mpID=\'' . $_MagnaSession['mpID'] . '\' AND
		       selectionname=\'' . $applySetting['selectionName'] . '\' AND
		       session_id=\'' . session_id() . '\'
	  GROUP BY selectionname
	');

	if ($itemCount == 1) {
		$applyAction = 'singleapplication';
	} else if ($itemCount > 1) {
		$applyAction = 'multiapplication';
	}
}

if (($applyAction == 'singleapplication') || ($applyAction == 'multiapplication')) {
	include_once(DIR_MAGNALISTER_MODULES . 'amazon/application/applicationviews.php');

} else {
	if (defined('MAGNA_DEV_PRODUCTLIST') && MAGNA_DEV_PRODUCTLIST === true) {
		require_once(DIR_MAGNALISTER_MODULES . 'amazon/prepare/AmazonApplyProductList.php');
		$o = new AmazonApplyProductList();
		echo $o;
	} else {
		require_once(DIR_MAGNALISTER_MODULES . 'amazon/classes/ApplyCategoryView.php');

		$aCV = new ApplyCategoryView(
			$current_category_id, $applySetting, /* $current_category_id is a global variable from xt:Commerce */
			(isset($_GET['sorting']) ? $_GET['sorting'] : ''),
			(isset($_POST['tfSearch']) ? $_POST['tfSearch'] : '')
		);
		if (isset($_GET['kind']) && ($_GET['kind'] == 'ajax')) {
			echo $aCV->renderAjaxReply();
		} else {
			echo $aCV->printForm();
		}
	}
}
