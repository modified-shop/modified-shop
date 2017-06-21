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
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimpleCheckinCategoryView.php');

class ComparisonShoppingCategoryView extends SimpleCheckinCategoryView {
	protected $marketplace;
	
	public function __construct($cPath = 0, $settings = array(), $sorting = false, $search = '') {
		global $_Marketplace;
		
		$this->marketplace = $_Marketplace;
		$settings = array_merge(array(
			'selectionName'   => 'checkin',
			'selectionValues' => array (
				'shippingcost' => null
			)
		), $settings);
		
		parent::__construct($cPath, $settings, $sorting, $search);
		if (!isset($_GET['kind']) || ($_GET['kind'] != 'ajax')) {
			$this->simplePrice->setCurrency(getCurrencyFromMarketplace($this->mpID));
		}
	}
	
}
