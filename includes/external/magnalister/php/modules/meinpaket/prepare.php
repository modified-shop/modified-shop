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
 * (c) 2011 - 2013 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
 
$_url['mode'] = 'prepare';

if (!array_key_exists('view', $_GET) || !in_array($_GET['view'], array('apply', 'varmatch'))) {
	$_url['view'] = $_GET['view'] = 'apply';
} else {
	$_url['view'] = $_GET['view'];
}

$resources = array (
	'query' => &$_magnaQuery,
	'session' => &$_MagnaSession,
	'url' => &$_url,
	'moduleConf' => $_modules[$_Marketplace],
);

	require_once(DIR_MAGNALISTER_MODULES.'meinpaket/prepare/MeinpaketProductPrepare.php');
	$mlc = new MeinpaketProductPrepare($resources);
	$mlc->process();
