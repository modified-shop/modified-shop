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
 * $Id: checkin.php 1131 2011-07-06 21:25:39Z derpapst $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/ComparisonShopping/ComparisonShoppingSummaryView.php');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/ComparisonShopping/ComparisonShoppingCategoryView.php');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/CheckinManager.php');
require_once(DIR_MAGNALISTER_MODULES.'idealo/checkin/IdealoCheckinProductList.php');
require_once(DIR_MAGNALISTER_MODULES.'idealo/checkin/IdealoCheckinSubmit.php');

class IdealoSummaryView extends ComparisonShoppingSummaryView {
	protected function getAdditionalProductNameStuff($prod) {
		return '
			<a class="right gfxbutton magnifier" target="_blank" '.
			  'href="http://www.idealo.de/gt/main.asp?suche='.urlencode($prod['products_name']).'" '.
			  'title="'.ML_IDEALO_SAME_PRODUCT_THERE.'"></a>';
	}

	protected function processAdditionalPost() {
		if (isset($_GET['kind']) && ($_GET['kind'] == 'ajax')) {
			if (!isset($_POST['productID'])) {
				return;
			}
			$pID = $this->ajaxReply['pID'] = substr($_POST['productID'], strpos($_POST['productID'], '_') + 1);

			if (!array_key_exists($pID, $this->selection)) {
				$this->loadItemToSelection($pID);
			}
			$this->extendProductAttributes($pID, $this->selection[$pID]);

			if (isset($_POST['changeShippingcost'])) {
				$_POST['shippingcost'][$pID] = $_POST['changeShippingcost'];

				MagnaDB::gi()->update(TABLE_MAGNA_IDEALO_PROPERTIES,
					array('ShippingCost' => $_POST['changeShippingcost']),
					array('mpID' => $this->mpID, 'products_id' => $pID)
				);
			}
		}

		if (array_key_exists('shippingcost', $_POST)) {
			$format = $this->simplePrice->getFormatOptions();
			foreach ($_POST['shippingcost'] as $pID => $price) {
				$price = $_POST['shippingcost'][$pID];
				if (($price == (string)(float)$price)) {
					$price = (float)$price;
				} else {
					$price = priceToFloat($_POST['shippingcost'][$pID], $format);
				}
				if ($price > 0) {
					$this->selection[$pID]['shippingcost'] = $this->ajaxReply['value'] = $price;
				}
			}
		}
	}

	protected function extendProductAttributes($pID, &$data) {
		$product = MagnaDB::gi()->fetchRow('
			SELECT p.products_model, p.products_weight
			FROM '.TABLE_PRODUCTS.' p
			WHERE p.products_id=\''.$pID.'\'
			LIMIT 1
		');

		$aPropertiesRow = MagnaDB::gi()->fetchRow('
				SELECT * FROM ' . TABLE_MAGNA_IDEALO_PROPERTIES . '
				 WHERE ' . (getDBConfigValue('general.keytype', '0') == 'artNr'
				? 'products_model = "' . MagnaDB::gi()->escape($product['products_model']) . '"'
				: 'products_id = "' . $pID . '"'
			) . '
					   AND mpID = ' . $this->_magnasession['mpID']
		);

		if (!empty($aPropertiesRow['ShippingCostMethod'])) {
			if (!empty($aPropertiesRow['ShippingCost']) && (float)$aPropertiesRow['ShippingCost'] > 0
				&& $aPropertiesRow['ShippingCostMethod'] === '__ml_lump') {
				$data['shippingcost'] = $aPropertiesRow['ShippingCost'];
				return;
			} else if ($aPropertiesRow['ShippingCostMethod'] === '__ml_weight') {
				$data['shippingcost'] = $product['products_weight'];
				return;
			}
		}

		parent::extendProductAttributes($pID, $data);
	}
}


$sCheckinView = '';
if (defined('MAGNA_DEV_PRODUCTLIST') && MAGNA_DEV_PRODUCTLIST === true) {
$sCheckinView = 'IdealoCheckinProductList';
} else {
$sCheckinView = 'ComparisonShoppingCategoryView';
}

$cm = new CheckinManager(
	array(
		'summaryView'   => 'IdealoSummaryView',
		'checkinView'   => $sCheckinView,
		'checkinSubmit' => 'IdealoCheckinSubmit'
	), array(
		'marketplace' => $_Marketplace
	)
);

echo $cm->mainRoutine();
