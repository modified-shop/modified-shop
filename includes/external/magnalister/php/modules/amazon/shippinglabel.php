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
 * $Id:
 *
 * (c) 2010 - 2016 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
global $_url;
if (!array_key_exists('view', $_GET) || !in_array($_GET['view'], array('upload', 'overview'))) {
	$view = $_GET['view'] = 'upload';
} else {
	$view = $_GET['view'];
}
$_url['mode'] = $_GET['mode'];
$_url['view'] = $view;
define('DIR_MAGNALISTER_MODULES_AMAZON_ORDERLIST', DIR_MAGNALISTER_MODULES.'amazon/classes/Orderlist/');
require_once(DIR_MAGNALISTER_MODULES.'amazon/shippinglabel/'.$view.'.php');