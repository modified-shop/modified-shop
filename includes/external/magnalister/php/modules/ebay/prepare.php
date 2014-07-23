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
 * $Id: prepare.php 674 2011-01-08 03:21:50Z MaW $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

# return product details for each item in selection
function eBayGetSelection() {
	global $_MagnaSession;
	# Daten aus magnalister_ebay_properties (bereits frueher vorbereitet)
	$keytypeIsArtNr = (getDBConfigValue('general.keytype', '0') == 'artNr');
	
	if ($keytypeIsArtNr) { 
	    $dbOldSelectionQuery = 'SELECT '
		.' ep.products_id products_id, ep.products_model products_model, '
		.' Price, IF(0.0=Price, 0, 1) as priceFrozen, '
		.' ms.mpID mpID, Title, Subtitle, Description, '
		.' pd.products_name products_name, pd.products_description description, PictureURL, GalleryURL, ConditionID,  '
		.' PrimaryCategory, SecondaryCategory, StoreCategory, StoreCategory2, '
		.' Attributes, ItemSpecifics, '
		.' ListingType, ListingDuration, PaymentMethods, ShippingDetails '
		.' FROM '.TABLE_MAGNA_EBAY_PROPERTIES .' ep, '.TABLE_MAGNA_SELECTION.' ms, '
		. TABLE_PRODUCTS .' p, ' . TABLE_PRODUCTS_DESCRIPTION .' pd '
		.' WHERE ep.products_model = p.products_model '
		.' AND p.products_id = ms.pID AND ep.mpID = ms.mpID '
		.' AND pd.products_id = p.products_id '
		.' AND pd.language_id = \''.getDBConfigValue('ebay.lang', $_MagnaSession['mpID']).'\' '
		.' AND selectionname=\'prepare\' '
		.' AND ms.mpID = \''.$_MagnaSession['mpID'].'\' '
		.' AND session_id=\''.session_id().'\'';
	} else {
	    $dbOldSelectionQuery = 'SELECT '
		.' ep.products_id products_id, ep.products_model products_model, '
		.' Price, IF(0.0=Price, 0, 1) as priceFrozen, '
		.' ms.mpID mpID, Title, Subtitle, Description, '
		.' pd.products_name products_name, pd.products_description description, PictureURL, GalleryURL, ConditionID,  '
		.' PrimaryCategory, SecondaryCategory, StoreCategory, StoreCategory2, '
		.' Attributes, ItemSpecifics, '
		.' ListingType, ListingDuration, PaymentMethods, ShippingDetails '
		.' FROM '.TABLE_MAGNA_EBAY_PROPERTIES .' ep, '.TABLE_MAGNA_SELECTION.' ms, '
		. TABLE_PRODUCTS_DESCRIPTION .' pd '
		.' WHERE ep.products_id = ms.pID AND ep.mpID = ms.mpID  AND pd.products_id = ep.products_id '
		.' AND pd.language_id = \''.getDBConfigValue('ebay.lang', $_MagnaSession['mpID']).'\' '
		.' AND selectionname=\'prepare\' '
		.' AND ms.mpID = \''.$_MagnaSession['mpID'].'\' '
		.' AND session_id=\''.session_id().'\'';
	}
	$dbOldSelection = MagnaDB::gi()->fetchArray($dbOldSelectionQuery);
	if (empty($dbOldSelection) && !is_array($dbOldSelection)) {
		$dbOldSelection = array();
	}
	$oldProducts = array();
	foreach ($dbOldSelection as $row) {
		$oldProducts[] = MagnaDB::gi()->escape($keytypeIsArtNr ? $row['products_model'] : $row['products_id']);
	}
	if (empty($oldProducts)) {
		$oldProductsList = "''";
	} else {
		$oldProductsList = '"'.implode('", "', $oldProducts).'"';
	}
	# Daten fuer magnalister_ebay_properties
	# die Namen schon fuer diese Tabelle
	# products_short_description nicht bei OsC, nur bei xtC, Gambio und Klonen
	$dbNewSelectionQuery = 'SELECT '
		.' p.products_id products_id, '
		.' p.products_model products_model, '
		.' p.products_price Price, '
		.' ms.mpID mpID, '
		.' pd.products_name products_name, ';
	if (MagnaDB::gi()->columnExistsInTable('products_short_description', TABLE_PRODUCTS_DESCRIPTION)) {
		$dbNewSelectionQuery .=
		 ' pd.products_short_description Subtitle, ';
	}
	$dbNewSelectionQuery .= 
		 ' pd.products_description description, '
		.' p.products_image PictureURL, '
		.' p.products_weight products_weight '
		.' FROM '.TABLE_PRODUCTS.' p, '.TABLE_PRODUCTS_DESCRIPTION.' pd, '.TABLE_MAGNA_SELECTION.' ms '
		.' WHERE pd.products_id = p.products_id AND ms.pID = p.products_id '
		.' AND '.($keytypeIsArtNr ? 'p.products_model' : 'p.products_id').' NOT IN ('.$oldProductsList.') '
		.' AND pd.language_id = \''.getDBConfigValue('ebay.lang', $_MagnaSession['mpID']).'\' '
		.' AND ms.mpID = \''.$_MagnaSession['mpID'].'\' '
		.' AND selectionname=\'prepare\' '
		.' AND session_id=\''.session_id().'\'';
	$dbNewSelection = MagnaDB::gi()->fetchArray($dbNewSelectionQuery);
	$dbSelection = array_merge($dbOldSelection, $dbNewSelection);
	if (false) { # DEBUG
		echo "dbOldSelectionQuery == \n$dbOldSelectionQuery<br />\n";
		echo "dbNewSelectionQuery == \n$dbNewSelectionQuery<br />\n";
		echo print_m($dbOldSelection, '$dbOldSelection');
		echo print_m($dbNewSelection, '$dbNewSelection');
		echo print_m($dbSelection, '$dbSelection');
	}
	$rowCount = 0;
	$imagePath = getDBConfigValue('ebay.imagepath',$_MagnaSession['mpID']);
	$galleryPath = getDBConfigValue('ebay.gallery.imagepath',$_MagnaSession['mpID']);
	if (empty($galleryPath)) $galleryPath = $imagePath;
	foreach ($dbSelection as &$current_row) {
		++$rowCount;
		# Filter Gambio TABs
		if (SHOPSYSTEM == 'gambio') {
			$current_row['description'] = preg_replace('/\[TAB:([^\]]*)\]/', '<h1>${1}</h1>', $current_row['description']);
		}
		#$current_row['SKU'] = magnaPID2SKU($current_row['products_id']);
		if (isset($current_row['PrimaryCategory'])) continue;
		# Nur bei unvorbereiteten Produkten: Pfade an Bilder dranmachen
		$pictureWithoutPath = $current_row['PictureURL'];
		$current_row['PictureURL'] = empty($current_row['PictureURL'])? '': $imagePath . $pictureWithoutPath;
		$current_row['GalleryURL'] = empty($current_row['PictureURL'])? '': $galleryPath . $pictureWithoutPath;
		if (array_key_exists('Subtitle', $current_row)) {
			$current_row['shortdescription'] = $current_row['Subtitle']; # for eBay Item Description
			$current_row['Subtitle'] = strip_tags($current_row['Subtitle']);
		} else {
			$current_row['shortdescription'] = $current_row['Subtitle'] = '';
		}
	}

	if ((1 == $rowCount) && empty($dbSelection[0]['Description'])) {
		$eBayTemplate = getDBConfigValue('ebay.template.content',$_MagnaSession['mpID']);
		# Template fuellen
		# bei mehreren Artikeln erst beim Speichern fuellen
		# Preis und ggf. VPE wird erst beim Uebermitteln eingesetzt.
		$substitution = array (
			'#TITLE#' => fixHTMLUTF8Entities($dbSelection[0]['products_name']),
			'#ARTNR#' => $dbSelection[0]['products_model'],
			'#PID#' => $dbSelection[0]['products_id'],
			'#SKU#' => magnaPID2SKU($dbSelection[0]['products_id']),
			'#SHORTDESCRIPTION#' => $dbSelection[0]['shortdescription'],
			'#DESCRIPTION#' => stripLocalWindowsLinks($dbSelection[0]['description']),
			'#PICTURE1#' => $dbSelection[0]['PictureURL'],
			'#WEIGHT#' => ((float)$dbSelection[0]['products_weight']>0)?$dbSelection[0]['products_weight']:'',
		);
		$dbSelection[0]['Description'] = substitutePictures(eBaySubstituteTemplate(
			$_MagnaSession['mpID'], $dbSelection[0]['products_id'], $eBayTemplate, $substitution
		), $dbSelection[0]['products_id'], $imagePath);
	}
	if ((1 == $rowCount) && empty($dbSelection[0]['Title'])) {
		$eBayTitleTemplate = getDBConfigValue('ebay.template.name',$_MagnaSession['mpID'], '#TITLE#');
		# Titel-Template fuellen
		# bei mehreren Artikeln erst beim Speichern fuellen
		# Preis und ggf. VPE wird erst beim Uebermitteln eingesetzt.
		$substitution = array (
			'#TITLE#' => fixHTMLUTF8Entities($dbSelection[0]['products_name']),
			'#ARTNR#' => $dbSelection[0]['products_model'],
		);
		$dbSelection[0]['Title'] = eBaySubstituteTemplate(
			$_MagnaSession['mpID'], $dbSelection[0]['products_id'], $eBayTitleTemplate, $substitution
		);
	}
	return $dbSelection;
}

$_url['mode'] = 'prepare';
$prepareSetting = array(
	'selectionName' => 'prepare'
);

/**
 * Daten speichern
 */
if (array_key_exists('savePrepareData', $_POST)) {
	@set_time_limit(300);
	$itemDetails = $_POST;
	unset($itemDetails['savePrepareData']);
	#echo print_m($itemDetails, '$itemDetails');

	$pIDs = MagnaDB::gi()->fetchArray('
		SELECT pID FROM '.TABLE_MAGNA_SELECTION.'
		 WHERE mpID=\''.$_MagnaSession['mpID'].'\' AND
			   selectionname=\''.$prepareSetting['selectionName'].'\' AND
			   session_id=\''.session_id().'\'
	', true);
	if (1 == count($pIDs)) {
		SaveEBaySingleProductProperties($pIDs[0], $itemDetails);
	} else if (!empty($pIDs)) {
		SaveEBayMultipleProductProperties($pIDs, $itemDetails);
	}
	require_once(DIR_MAGNALISTER_MODULES.'ebay/classes/eBayCheckinSubmit.php');
	$ecs = new eBayCheckinSubmit(array(
		'itemsPerBatch'   => 1,
		'selectionName'   => $prepareSetting['selectionName'],
		'marketplace'     => 'ebay',
	));
	$verified = $ecs->verifyOneItem();
	#echo print_m($verified, '$ecs->verifyOneItem()');

	if('SUCCESS' == $verified['STATUS']) {
		MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array (
			'mpID' => $_MagnaSession['mpID'],
			'selectionname' => $prepareSetting['selectionName'],
			'session_id' => session_id()
		));
		if (isset($verified['RESPONSEDATA'][0]['DATA']['Fees']['ListingFee'])) {
			echo '<p class="successBox">'.sprintf(ML_EBAY_LABEL_ADDITEM_COSTS, $verified['RESPONSEDATA'][0]['DATA']['Fees']['ListingFee']).'</p>';
		}
	} else if('ERROR' == $verified['STATUS']) {
		# noch mal in der Maske bleiben
		$_POST['prepare'] = 'prepare';

		/* Letzte Exception holen */
		$ex = $ecs->getLastException();
		/* Wenns eine Exception war und es sich nicht um einen Fehler in der API handelt... */
		if (is_object($ex) && ($ex->getSubsystem() != 'PHP')
			&& ($errors = $ex->getErrorArray()) && isset($errors['RESPONSEDATA'][0]['ERRORS'][0]['ERRORCODE'])
		) {
			$supportsUTF8 = (stripos($_SESSION['language_charset'], 'utf') !== false);
			/* ... als unkrittisch markieren. */
			$ex->setCriticalStatus(false);
			foreach ($errors['RESPONSEDATA'] as $ebayItemErrors) {
				foreach ($ebayItemErrors['ERRORS'] as $ebayError) {
					if (($ebayError['ERRORCLASS'] != 'RequestError') || ($ebayError['ERRORLEVEL'] != 'Error')) continue;
					if (!$supportsUTF8) arrayEntitiesToLatin1($ebayError);
					echo '<div class="ebay errorBox"><span class="error">'.sprintf(ML_EBAY_LABEL_EBAYERROR, $ebayError['ERRORCODE']).'</span>: '.
						$ebayError['ERRORMESSAGE'].'</div>';
					//echo print_m($ebayError);
				}
			}
		}
	}
	echo "\n\n<!--\n".str_replace(array('<!--', '-->'), array('<!- -', '- ->'), json_indent($ecs->getLastRequest()))."-->\n\n";
}

if (!defined('MAGNA_DEV_PRODUCTLIST') || MAGNA_DEV_PRODUCTLIST === false) {// will be done in MLProductListDependencyEbayPrepareFormAction
	/**
	 * Daten loeschen
	 */
	if ((array_key_exists('unprepare', $_POST)) && (!empty($_POST['unprepare']))) {
		$pIDs = MagnaDB::gi()->fetchArray('
			SELECT pID FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID=\''.$_MagnaSession['mpID'].'\' AND
				   selectionname=\''.$prepareSetting['selectionName'].'\' AND
				   session_id=\''.session_id().'\'
		', true);
		if (!empty($pIDs)) {
			foreach ($pIDs as $pID) {
				$where = (getDBConfigValue('general.keytype', '0') == 'artNr')
					? array ('products_model' => MagnaDB::gi()->fetchOne('
								SELECT products_model
								  FROM '.TABLE_PRODUCTS.'
								 WHERE products_id='.$pID
							))
					: array ('products_id'    => $pID);
				$where['mpID'] = $_MagnaSession['mpID'];

				MagnaDB::gi()->delete(TABLE_MAGNA_EBAY_PROPERTIES, $where);
				MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
					'pID' => $pID,
					'mpID' => $_MagnaSession['mpID'],
					'selectionname' => $prepareSetting['selectionName'],
					'session_id' => session_id()
				));
			}
		}
		unset($_POST['unprepare']);
	}

	/**
	 * Nur Artikelbeschreibung loeschen
	 */
	if ((array_key_exists('reset_description', $_POST)) && (!empty($_POST['reset_description']))) {
		$pIDs = MagnaDB::gi()->fetchArray('
			SELECT pID FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID=\''.$_MagnaSession['mpID'].'\' AND
				   selectionname=\''.$prepareSetting['selectionName'].'\' AND
				   session_id=\''.session_id().'\'
		', true);
		if (!empty($pIDs)) {
			foreach ($pIDs as $pID) {
				$where = (getDBConfigValue('general.keytype', '0') == 'artNr')
					? array ('products_model' => MagnaDB::gi()->fetchOne('
								SELECT products_model
								  FROM '.TABLE_PRODUCTS.'
								 WHERE products_id='.$pID
							))
					: array ('products_id'    => $pID);
				$where['mpID'] = $_MagnaSession['mpID'];

				MagnaDB::gi()->update(TABLE_MAGNA_EBAY_PROPERTIES, array('Description' => ''), $where);
				MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
					'pID' => $pID,
					'mpID' => $_MagnaSession['mpID'],
					'selectionname' => $prepareSetting['selectionName'],
					'session_id' => session_id()
				));
			}
		}
		unset($_POST['reset_description']);
	}
}
/**
 * Matching Vorbereitung
 */
if (array_key_exists('prepare', $_POST) && (!empty($_POST['prepare']))) {

	/**
	 * Fall 'nur nicht vorbereitete': Fertige aus der selection entfernen
	 */
	if (isset($_POST['match']) && ($_POST['match'] == 'notmatched')) {
		MagnaDB::gi()->query('
			DELETE FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID=\''.$_MagnaSession['mpID'].'\'
				   AND selectionname=\''.$prepareSetting['selectionName'].'\'
				   AND session_id=\''.session_id().'\' 
				   AND pID IN ( SELECT products_id
						FROM '.TABLE_MAGNA_EBAY_PROPERTIES.'
						 WHERE mpID=\''.$_MagnaSession['mpID'].'\'
						 AND Verified = \'OK\' )
		');
	}


	$itemCount = (int)MagnaDB::gi()->fetchOne('
		SELECT count(*) 
		  FROM '.TABLE_MAGNA_SELECTION.'
		 WHERE mpID=\''.$_MagnaSession['mpID'].'\' AND
			   selectionname=\''.$prepareSetting['selectionName'].'\' AND
			   session_id=\''.session_id().'\'
		GROUP BY selectionname
	');

	if ($itemCount == 1) {
		$prepareAction = 'singleprepare';
	} else if ($itemCount > 1) {
		$prepareAction = 'multiprepare';
	}
}

if (isset($prepareAction)
	|| (
		isset($_GET['kind']) && ($_GET['kind'] == 'ajax')
		&& isset($_GET['where']) && ($_GET['where'] == 'prepareView')
	)
) {
	require_once(DIR_MAGNALISTER_MODULES.'ebay/classes/eBayCategoryMatching.php');
	require_once(DIR_MAGNALISTER_MODULES.'ebay/classes/eBayShippingDetailsProcessor.php');
	$kind = (isset($_GET['kind']) && ($_GET['kind'] == 'ajax')) ? 'ajax' : 'view';
	$ycm = new eBayCategoryMatching($kind);
	if ($kind == 'view') {
		require_once(DIR_MAGNALISTER_MODULES.'ebay/prepare/prepareView.php');
		echo renderPrepareView(eBayGetSelection());
		echo $ycm->render();
	} else if (array_key_exists('action', $_POST)) {
		switch ($_POST['action']) {
			case 'getListingDurations': {
				try {
					$result = MagnaConnector::gi()->submitRequest(array(
						'ACTION' => 'GetListingDurations',
						'DATA' => array (
							'ListingType' => $_POST['ListingType']
						)
					));
				} catch (MagnaException $e) {
					echo print_m($e->getErrorArray(), 'Error');
					$result = array (
						'DATA' => array (
							'null' => 'Konnte nicht abgerufen werden.'
						)
					);
				}
				$html = '';
				if (!in_array($_POST['preselected'], $result['DATA']['ListingDurations'])) {
				# Fall: 30 Tage sind default, aber hoechstens 10 verfuegbar
					$highestKeyOfListingDurations = count($result['DATA']['ListingDurations']) - 1;
					$_POST['preselected'] = $result['DATA']['ListingDurations']["$highestKeyOfListingDurations"];
				}
				foreach ($result['DATA']['ListingDurations'] as $duration) {
					$define = 'ML_EBAY_LABEL_LISTINGDURATION_'.strtoupper($duration);
					if ($_POST['preselected'] == $duration)
						$html .= '
						<option selected="selected" value="'.$duration.'">'.(defined($define) ? constant($define) : $duration).'</option>';
					else
						$html .= '
						<option value="'.$duration.'">'.(defined($define) ? constant($define) : $duration).'</option>';
				}
				echo $html;
				break;
			}
			case 'extern': {
				$args = $_POST;
				unset($args['function']);
				unset($args['action']);
				$tmpURL = $_url;
				$tmpURL['where'] = 'prepareView';
				if ('true' == $args['international']) {
					$shipProc = new eBayShippingDetailsProcessor($args, 'ebay.default.shipping.international', $tmpURL);
				} else {
					$shipProc = new eBayShippingDetailsProcessor($args, 'ebay.default.shipping.local', $tmpURL);
				}
				echo $shipProc->process();
				break;
			}
			case 'getEBayAttributes': {
				echo getEBayAttributes($_POST['CategoryID'], $_POST['Mode'], (isset($_POST['preselectedValues'])?$_POST['preselectedValues']:''));
				break;
			}
			case 'makePrice': {
				require_once(DIR_MAGNALISTER_MODULES.'ebay/ebayFunctions.php');
				echo makePrice($_POST['pID'], $_POST['ListingType']);
				break;
			}
			default: {
				echo $ycm->render();
				break;
			}
		}
	}
} else {
	if (defined('MAGNA_DEV_PRODUCTLIST') && MAGNA_DEV_PRODUCTLIST === true ) {
		require_once(DIR_MAGNALISTER_MODULES.'ebay/prepare/EbayPrepareProductList.php');
		$o = new EbayPrepareProductList();
		echo $o;
	} else {
		require_once(DIR_MAGNALISTER_MODULES.'ebay/classes/PrepareCategoryView.php');
		if (!isset($_GET['sorting'])) $_GET['sorting'] = false;
		if (!isset($_POST['tfSearch'])) $_POST['tfSearch'] = '';
		$eCV = new PrepareCategoryView(null, $prepareSetting, $_GET['sorting'], $_POST['tfSearch']); /* $current_category_id is a global variable from xt:Commerce */
		if (isset($_GET['kind']) && ($_GET['kind'] == 'ajax')) {
			echo $eCV->renderAjaxReply();
		} else {
			echo $eCV->printForm();
		}
		unset($_MagnaShopSession['prepareMode']);
	}
}
