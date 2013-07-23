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
 * $Id: init.php 1283 2011-09-30 22:52:17Z derpapsst $
 *
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

$mpconfig = array (
	'auth' => array (
		'authkeys' => array (
			'username', 'password',
		),
	),
	'pages' => array(
		'catmatch' => array (
			'resource' => 'categorymatching',
			'class' => 'MagnaCompatCatMatch',
		),
		'prepare' => array (
			'resource' => 'prepare',
			'class' => 'MagnaCompatiblePrepare',
		),
		'checkin' => array (
			'resource' => 'checkin',
			'class' => 'MagnaCompatibleCheckin',
		),
		'listings' => array (
			'resource' => 'listings',
			'class' => 'MagnaCompatibleListingLoader',
		),
		'errorlog' => array (
			'resource' => 'errorlog',
			'class' => 'MagnaCompatibleErrorLog',
		),
		'conf' => array (
			'resource' => 'configure',
			'class' => 'MagnaCompatibleConfigure',
			'params' => array ('authConfigKeys', 'missingConfigKeys'),
		),
	),
	'checkin' => array (
		'Categories' => array (
			'Marketplace' => 'required',
			'Shop' => 'no',
		),
		'Variations' => 'yes',
	),
);