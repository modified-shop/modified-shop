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
 * $Id: checkin.php 3975 2014-06-16 16:02:59Z masoud.khodaparast $
 *
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/CheckinManager.php');
require_once(DIR_MAGNALISTER_MODULES.'ayn24/checkin/CheckinCategoryView.php');
require_once(DIR_MAGNALISTER_MODULES.'ayn24/checkin/Ayn24SummaryView.php');
require_once(DIR_MAGNALISTER_MODULES.'ayn24/checkin/Ayn24CheckinSubmit.php');

$sCheckinView = '';
if (defined('MAGNA_DEV_PRODUCTLIST') && MAGNA_DEV_PRODUCTLIST === true ) {  
            require_once(DIR_MAGNALISTER_MODULES.'ayn24/checkin/Ayn24CheckinProductList.php');
            $sCheckinView = 'Ayn24CheckinProductList';
}else{
            $sCheckinView = 'Ayn24CheckinCategoryView';
}
$cm = new CheckinManager(
	array(
		'summaryView'   => 'Ayn24SummaryView',
		'checkinView'   => $sCheckinView,
		'checkinSubmit' => 'Ayn24CheckinSubmit'
	), array(
		'marketplace' => $_Marketplace,
		'hasPurge' => true,
	)
);
echo $cm->mainRoutine();
