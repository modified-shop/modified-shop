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
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

$_url['mode'] = 'catmatch';
$prepareSetting = array(
	'selectionName' => 'catmatch'
);

if (array_key_exists('saveMatching', $_POST)) {
	MagnaDB::gi()->query('
		REPLACE INTO '.TABLE_MAGNA_MEINPAKET_CATEGORYMATCHING.'
			SELECT DISTINCT ms.mpID, p.products_id, p.products_model, 
			       \''.MagnaDB::gi()->escape($_POST['mpCategory']).'\' as mp_category_id,
			       \''.MagnaDB::gi()->escape($_POST['storeCategory']).'\' as store_category_id
			  FROM '.TABLE_MAGNA_SELECTION.' ms, '.TABLE_PRODUCTS.' p
			 WHERE ms.mpID=\''.$_MagnaSession['mpID'].'\' AND
			       ms.selectionname=\''.$prepareSetting['selectionName'].'\' AND
			       ms.session_id=\''.session_id().'\' AND
			       ms.pID=p.products_id
	');
	MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
		'mpID' => $_MagnaSession['mpID'],
		'selectionname' => $prepareSetting['selectionName'],
		'session_id' => session_id()
	));
}

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

			MagnaDB::gi()->delete(TABLE_MAGNA_MEINPAKET_CATEGORYMATCHING, $where);
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

if (isset($_POST['prepare']) || (isset($_GET['where']) && ($_GET['where'] == 'catMatchView'))) {
	require_once(DIR_MAGNALISTER_MODULES.'meinpaket/catmatch/MeinpaketCatMatcher.php');
	$cM = new MeinpaketCatMatcher($prepareSetting);
	echo $cM->run();
} else {
	require_once(DIR_MAGNALISTER_MODULES.'meinpaket/catmatch/MeinpaketPrepareCategoryView.php');
	$pV = new MeinpaketPrepareCategoryView($current_category_id, $prepareSetting, $_GET['sorting'], $_POST['tfSearch']); /* $current_category_id is a global variable from xt:Commerce */
	if (isset($_GET['kind']) && ($_GET['kind'] == 'ajax')) {
		echo $pV->renderAjaxReply();
	} else {
		echo $pV->printForm();
	}
}
