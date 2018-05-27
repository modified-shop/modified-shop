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
 * $Id: prepare.php 435 2010-10-08 15:08:28Z derpapst $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

$_url['mode'] = 'prepare';

if (!empty($_POST['FullSerializedForm'])) {
	$newPost = array();
	parse_str_unlimited($_POST['FullSerializedForm'], $newPost);

	$_POST = array_merge($_POST, $newPost);
	unset($_POST['FullSerializedForm']);
}

if (!array_key_exists('view', $_GET) || !in_array($_GET['view'], array('apply', 'match', 'varmatch'))) {
	$view = $_GET['view'] = 'apply';
} else {
	$view = $_GET['view'];
}

if ($view == 'match') {
	require_once(DIR_MAGNALISTER_MODULES.'amazon/matching.php');
} else if ($view === 'varmatch') {
	require_once(DIR_MAGNALISTER_MODULES.'amazon/prepare/AmazonVariationMatching.php');
	$varMatch = new AmazonVariationMatching(array(
		'resources' => array(
			'session' => array(
				'mpID' => $_MagnaSession['mpID'],
				'currentPlatform' => $_MagnaSession['currentPlatform'],
			),
			'url' => array(
				'mode' => 'prepare',
				'view' => 'varmatch',
				'mp' => $_MagnaSession['mpID'],
			),
		),
	));
	if (isset($_GET['kind']) && $_GET['kind'] === 'ajax') {
		$varMatch->renderAjax();
	} else {
		$varMatch->process();
	}
} else {
	require_once(DIR_MAGNALISTER_MODULES.'amazon/apply.php');
}
