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

require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/ComparisonShopping/InventoryView.php');

class YategoInventoryView extends InventoryView {
	
	public function __construct($settings = array()) {
		parent::__construct($settings);
		if (isset($_POST['refreshStock'])) {
			require_once (DIR_MAGNALISTER_MODULES.'yatego/crons/YategoSyncInventory.php');
			$asi = new YategoSyncInventory($this->magnasession['mpID'], 'yatego');
			$asi->disableMarker(true);
			$asi->process();
		}
	}
	
	protected function getSortOpt() {
		parent::getSortOpt();
		if (isset($_GET['sorting'])) {
			$sorting = $_GET['sorting'];
		} else {
			$sorting = 'blabla'; // fallback for default
		}		if (isset($_GET['sorting'])) {
			$sorting = $_GET['sorting'];
		} else {
			$sorting = 'blabla'; // fallback for default
		}

		switch ($sorting) {
			case 'quantity':
				$this->sort['order'] = 'Quantity';
				$this->sort['type']  = 'ASC';
				break;
			case 'quantity-desc':
				$this->sort['order'] = 'Quantity';
				$this->sort['type']  = 'DESC';
				break;
		}
	}

	protected function postDelete() {
		try {
			$result = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'UploadItems',
			));
		} catch (MagnaException $e) { }
	}
	
	protected function additionalHeaders() {
		return '<td>'.ML_LABEL_QUANTITY.' '.$this->sortByType('quantity').'</td>';
	}

	protected function additionalValues($item) {
		return '<td>'.$item['Quantity'].'</td>';
	}

	protected function getRightActionButton() {
		if (!in_array(getDBConfigValue('yatego.stocksync.tomarketplace', $this->magnasession['mpID']), array('abs', 'auto'))) {
			return '';
		}
		return '<input type="submit" class="ml-button" name="refreshStock" value="'.ML_BUTTON_REFRESH_STOCK.'"/>'; 
	}
}
