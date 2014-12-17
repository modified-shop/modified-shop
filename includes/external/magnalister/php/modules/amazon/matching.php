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
 * $Id: matching.php 4961 2014-12-09 14:10:12Z tim.neumann $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
include_once(DIR_MAGNALISTER_MODULES.'amazon/matching/matchingViews.php');

$_url['view'] = 'match';
$matchingSetting = array(
	'selectionName' => 'matching'
);

$matchAction = 'categoryview';

if (array_key_exists('PreparedTS', $_POST)) {
	$_MagnaSession['amazonLastPreparedTS'] = $_POST['PreparedTS'];
}
/**
 * Save and organize Multimatching
 */
if (array_key_exists('action', $_POST) && ($_POST['action'] == 'multimatching')) {
	include_once(DIR_MAGNALISTER_MODULES.'amazon/matching/saveMatching.php');
	if (ctype_digit($_POST['matching_nextpage'])) {
		/* Noch nicht mit matching fertig */
		$matchAction = 'multimatching';
	} else {
		/* Daten loswerden */
		MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
			'mpID' => $_MagnaSession['mpID'],
			'selectionname' => $matchingSetting['selectionName'],
			'session_id' => session_id()
		));
		unset($_MagnaSession['amazon']['multimatching']);
	}
}

/**
 * Save Singlematching
 */
if (array_key_exists('action', $_POST) && ($_POST['action'] == 'singlematching')) {
	include_once(DIR_MAGNALISTER_MODULES.'amazon/matching/saveMatching.php');
	/* Daten loswerden */
	MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
		'mpID' => $_MagnaSession['mpID'],
		'selectionname' => $matchingSetting['selectionName'],
		'session_id' => session_id()
	));
}
if (!defined('MAGNA_DEV_PRODUCTLIST') || MAGNA_DEV_PRODUCTLIST !== true ) {// will be done in MLProductListDependencyAmazonMatchingFormAction
	/**
	 * Daten loeschen
	 */
	if (array_key_exists('unmatching', $_POST)) {
		$pIDs = MagnaDB::gi()->fetchArray('
			SELECT pID FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID=\''.$_MagnaSession['mpID'].'\' AND
				   selectionname=\''.$matchingSetting['selectionName'].'\' AND
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

				MagnaDB::gi()->delete(TABLE_MAGNA_AMAZON_PROPERTIES, $where);
				MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
					'pID' => $pID,
					'mpID' => $_MagnaSession['mpID'],
					'selectionname' => $matchingSetting['selectionName'],
					'session_id' => session_id()
				));
			}
		}
	}
}
/**
 * Matching Vorbereitung
 */
if (array_key_exists('matching', $_POST) && (!empty($_POST['matching'])) && ($matchAction != 'multimatching')) {
	$itemCount = (int)MagnaDB::gi()->fetchOne('
		SELECT count(*) 
		  FROM '.TABLE_MAGNA_SELECTION.'
		 WHERE mpID=\''.$_MagnaSession['mpID'].'\' AND
		       selectionname=\''.$matchingSetting['selectionName'].'\' AND
		       session_id=\''.session_id().'\'
	  GROUP BY selectionname
	');

	if ($itemCount == 1) {
		$matchAction = 'singlematching';
	} else if ($itemCount > 1) {
		$matchAction = 'multimatching';
	}
}

if ($matchAction == 'singlematching') {
	include_once(DIR_MAGNALISTER_MODULES.'amazon/matching/singlematching.php');
	
} else if ($matchAction == 'multimatching') {
	include_once(DIR_MAGNALISTER_MODULES.'amazon/matching/multimatching.php');
	
} else if (isset($_GET['kind']) && ($_GET['kind'] == 'ajax') && isset($_GET['automatching'])) {
	if ($_GET['automatching'] == 'start') {
		include_once(DIR_MAGNALISTER_MODULES.'amazon/matching/saveMatching.php');
		$_autoMatchingStats = amazonAutoMatching($_MagnaSession['mpID'], $matchingSetting['selectionName']);
		$re = trim(sprintf(
			ML_AMAZON_TEXT_AUTOMATIC_MATCHING_SUMMARY,
			$_autoMatchingStats['success'],
			$_autoMatchingStats['nosuccess'],
			$_autoMatchingStats['almost'],
			microtime2human($_autoMatchingStats['_timer'])
		));
		echo magnalisterIsUTF8($re) ? $re : utf8_encode($re);
	} else {
		echo json_encode(array('x' => MagnaDB::gi()->fetchOne('
	        SELECT count(pID) FROM '.TABLE_MAGNA_SELECTION.'
		     WHERE mpID=\''.$_MagnaSession['mpID'].'\' AND
		           selectionname=\''.$matchingSetting['selectionName'].'\' AND
		           session_id=\''.session_id().'\'
		  GROUP BY mpID
		')));
	}
} else {
	if (defined('MAGNA_DEV_PRODUCTLIST') && MAGNA_DEV_PRODUCTLIST === true ) {
		require_once(DIR_MAGNALISTER_MODULES.'amazon/prepare/AmazonMatchingProductList.php');
		$o = new AmazonMatchingProductList();
		echo $o;
	} else {
		require_once(DIR_MAGNALISTER_MODULES.'amazon/classes/AmazonCategoryView.php');

		$aCV = new AmazonCategoryView(
			$current_category_id, $matchingSetting,  /* $current_category_id is a global variable from xt:Commerce */
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
/*
echo print_m($_MagnaSession, '$_MagnaSession');
echo print_m($_POST, '$_POST');
*/
