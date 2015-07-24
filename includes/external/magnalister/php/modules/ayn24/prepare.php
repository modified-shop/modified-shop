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
 
$_url['mode'] = $_magnaQuery['mode'];

$_url['view'] = isset($_GET['view'])
	? $_GET['view']
	: 'apply';

if (!in_array($_url['view'], array('apply', 'varmatch'))) {
	$_url['view'] = 'apply';
}

$resources = array (
	'query' => &$_magnaQuery,
	'session' => &$_MagnaSession,
	'url' => &$_url,
	'moduleConf' => $_modules[$_Marketplace],
);

if ($_url['view'] == 'varmatch') {
	require_once(DIR_MAGNALISTER_MODULES.'ayn24/prepare/Ayn24VariationMatching.php');
	$mlc = new Ayn24VariationMatching($resources);
	$mlc->process();
} else {
	require_once(DIR_MAGNALISTER_MODULES.'ayn24/prepare/Ayn24ProductPrepare.php');
	$mlc = new Ayn24ProductPrepare($resources);
	$mlc->process();
}
