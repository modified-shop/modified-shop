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
 * (c) 2010 - 2015 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
require_once(DIR_MAGNALISTER_MODULES . 'amazon/classes/MLOrderlistAmazonAbstract.php');

class MLOrderlistAmazonShippinglabelUploadForm extends MLOrderlistAmazonAbstract {

	public function __construct() {
		parent::__construct();
		$languages = getDBConfigValue($this->aMagnaSession['currentPlatform'].'.lang', $this->aMagnaSession['mpID'], $_SESSION['languages_id']);
		MLProduct::gi()->setLanguage($languages) ;
	}
	protected $aListConfig = array(
		array(
			'head' => array(
				'attributes' => '',
				'fieldname' => 'ItemTitle',
				'content' => 'ML_AMAZON_SHIPPINGLABEL_FORM_PRDUCTNAME'
			),
			'field' => array('absolute_data'),
		),
		array(
			'head' => array(
				'attributes' => '',
				'fieldname' => 'SKU',
				'content' => 'SKU',

			),
			'field' => array('absolute_data'),
		),
		array(
			'head' => array(
				'attributes' => '',
				'fieldname' => 'QuantitySent',
				'content' => 'ML_AMAZON_SHIPPINGLABEL_FORM_SENT',

			),
			'field' => array('absolute_data'),
		),
		array(
			'head' => array(
				'attributes' => 'class="price"',
				'fieldname' => 'Quantity',
				'content' => 'ML_AMAZON_SHIPPINGLABEL_FORM_QUANTITY',
			),
			'field' => array('quantity'),
		),
	);

	protected function getAllSelectionIds() {
		$aOrderIds = array();
		$aSelectedOrder = MagnaDB::gi()->fetchArray("
				SELECT `element_id`
				FROM " . $this->getSelectionTableName() . "
				WHERE
					`session_id` = '" . session_id() . "'
					AND `mpID` = '" . $this->aMagnaSession['mpID'] . "'
					AND `selectionname` = '" . $this->getSelectionName() . "'
			");
		foreach ($aSelectedOrder as $aOrderId) {
			$aOrderIds[] = $aOrderId['element_id'];
		}
		return $aOrderIds;
	}

	protected function buildRequest() {
		$this->oApiRequest = MLApiRequest::factoryApiRequestClass()->set(array(
			'ACTION' => 'GetOrdersForDateRange',
			'BEGIN' => date('Y-m-d H:i:s', time() - 60 * 60 * 24 * 30),
			"ForceV3" => true,
			"GetMFSDetails" => true,
			'OrderIDs' => $this->getAllSelectionIds()
			)
		);
		return $this;
	}

	protected function getOrders() {

		$aList = $this->oApiRequest->getAll();
		if (is_array($aList)) {
			foreach ($aList as $iOrderKey => $aOrderData) {
				$fTotalWeight = 0;
				foreach ($aOrderData['Products'] as $iProductKey => $aProduct) {
					if (isset($aProduct['QuantitySent']) && $aProduct['QuantitySent'] > 0) {
						$aList[$iOrderKey]['Products'][$iProductKey]['Quantity'] = $aProduct['Quantity'] - $aProduct['QuantitySent'];
					} else {
						$aList[$iOrderKey]['Products'][$iProductKey]['Quantity'] = $aProduct['Quantity'];
					}
					$fWeight = null;
					if (($pID = magnaSKU2pID($aProduct['SKU'])) !== 0) {
						$aProduct = MLProduct::gi()->getProductById(magnaSKU2pID($aProduct['SKU']));
						if (!empty($aProduct['Weight'])) {
							$fWeight = mlConvertWeight($aProduct['Weight']['Value'], $aProduct['Weight']['Unit'], getDBConfigValue($this->aMagnaSession['currentPlatform'] . '.shippinglabel.weight.unit', $this->aMagnaSession['mpID'], 'g'));
						}
					}
					if ($fWeight === null) {
						$fWeight = getDBConfigValue($this->aMagnaSession['currentPlatform'] . '.shippinglabel.fallback.weight', $this->aMagnaSession['mpID'], 0);
					}
					$aList[$iOrderKey]['Products'][$iProductKey]['Weight'] = $fWeight;
					$fTotalWeight += $fWeight * $aList[$iOrderKey]['Products'][$iProductKey]['Quantity'];
				}
				$aList[$iOrderKey]['TotalWeight'] = $fTotalWeight;
				$sWhere = "WHERE
						`session_id` = '" . session_id() . "'
						AND `mpID` = '" . $this->aMagnaSession['mpID'] . "'
						AND `selectionname` = '" . $this->getSelectionName() . "'
						AND `element_id` = '" . $aOrderData['MPSpecific']['MOrderID'] . "'
					";
				if (empty($aOrderData['Products'])) {
					unset($aList[$iOrderKey]);
					MagnaDB::gi()->query("
					DELETE
					FROM " . $this->getSelectionTableName() . "
					".$sWhere);
				} else {

					$aData = MagnaDB::gi()->fetchRow("
					SELECT data
					FROM " . $this->getSelectionTableName() . "
					".$sWhere);
					$aData['data'] = json_decode($aData['data'], true);
					$aData['data']['globalinfo'] = $aOrderData;
					MagnaDB::gi()->query("
					UPDATE
					 " . $this->getSelectionTableName() . "
						 SET data = '".json_encode($aData['data'])."'
					".$sWhere);
				}
			}
		}
		return $aList;
	}

	protected function addDependencies() {
		$this
			->addDependency('MLOrderlistDependencyFormToOrderlistAction', array())
			->addDependency('MLOrderlistDependencyFormToShippingmethodAction', array())
		;
	}

	protected function getSelectionName() {
		return 'amazon_shippinglabel_orderlist';
	}

	protected function getMainTemplateName() {
		return 'form';
	}

}