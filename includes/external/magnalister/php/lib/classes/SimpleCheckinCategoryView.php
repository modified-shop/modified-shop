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
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimpleCategoryView.php');

class SimpleCheckinCategoryView extends SimpleCategoryView {
	/**
	 * @param $cPath	Selected Category. 0 == top category
	 * @param $sorting	How should the list be sorted? false == default sorting
	 * @param $search   Searchstring for Product
	 * @param $allowedProductIDs	Limit Products to a list of specified IDs, if empty show all Products
	 */
	public function __construct($cPath = 0, $settings = array(), $sorting = false, $search = '', $allowedProductIDs = array()) {
		$settings = array_merge(array(
			'selectionName'   => 'checkin',
		), $settings);
		
		parent::__construct($cPath, $settings, $sorting, $search, $allowedProductIDs);
	}
	
	protected function init() {
		parent::init();
		
		$this->productIdFilterRegister('ManufacturerFilter', array());
	}
	
	public function getFunctionButtons() {
		global $_url;

		$new_url = $_url;
		unset($new_url['cPath']);
		
		return '<a class="ml-button" href="'.toURL($new_url, array('view' => 'summary')).'" title="'.ML_BUTTON_LABEL_SUMMARY.'">'.ML_BUTTON_LABEL_SUMMARY.'</a>';
	}
	
	public function getInfoText() {
		//return '<span>'.ML_LABEL_AMOUNT_SELECTED_PRODUCTS.'</span><span id="amountSelectedProducts"> '.count($this->selection).'</span>';
	}
}
