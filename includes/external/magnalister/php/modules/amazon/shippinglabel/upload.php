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
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
global $_url;
if (!array_key_exists('subview', $_GET) || !in_array($_GET['subview'], array('orderlist', 'form', 'shippingmethod', 'summary'))) {
	$_url['subview'] = 'orderlist';
} else {
	$_url['subview'] = $_GET['subview'];
}
require_once(DIR_MAGNALISTER_MODULES.'amazon/shippinglabel/upload/MLOrderlistAmazonShippingLabelUpload'.  ucfirst($_url['subview']).'.php');
$sClassName = 'MLOrderlistAmazonShippingLabelUpload'.ucfirst($_url['subview']);
$oOrderlist = new $sClassName();
echo $oOrderlist->render();
