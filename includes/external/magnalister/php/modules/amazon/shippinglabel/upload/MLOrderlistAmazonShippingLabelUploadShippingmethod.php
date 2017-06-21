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

class MLOrderlistAmazonShippinglabelUploadShippingmethod extends MLOrderlistAmazonAbstract {

	protected $oList = null;
	protected $aListConfig = array(
		array(
			'head' => array(
				'attributes' => '',
				'fieldname' => 'CarrierName',
				'content' => 'ML_AMAZON_SHIPPINGLABEL_FORM_CARRIERNAME'
			),
			'field' => array('absolute_data'),
		),
		array(
			'head' => array(
				'attributes' => '',
				'fieldname' => 'ShippingServiceName',
				'content' => 'ML_LABEL_MARKETPLACE_SHIPPING_METHOD',
			),
			'field' => array('absolute_data'),
		),
		array(
			'head' => array(
				'attributes' => '',
				'fieldname' => 'DeliveryTime',
				'content' => 'ML_AMAZON_SHIPPINGLABEL_SHIPPINMETHOD_DELIVERYTIME',
			),
			'field' => array('deliverytime'),
		),
		array(
			'head' => array(
				'attributes' => 'class="price"',
				'fieldname' => 'Amount',
				'content' => 'ML_AMAZON_SHIPPINGLABEL_SHIPPINMETHOD_AMOUNT',
			),
			'field' => array('price'),
		),
		array(
			'head' => array(
				'attributes' => '',
				'fieldname' => 'Comment',
				'content' => 'ML_AMAZON_SHIPPINGLABEL_SHIPPINMETHOD_COMMENT',
			),
			'field' => array('comment'),
		),
	);
	protected function addRequestSort() {
		return $this;
	}

	public function __construct() {
		parent::__construct();
		$this->saveData();
	}

	protected function getOrders() {
		require_once(DIR_MAGNALISTER_MODULES . 'amazon/classes/MLAmazonServiceShipping.php');
		$oService = new MLAmazonServiceShipping();

		$aOrders = $this->getSelectionData();
		$oService->setOrders($aOrders);
		$aList = $oService->getShippingService();
		return $aList;
	}

	protected function saveData() {
		if ($this->getRequest('weight') !== null) {
			$aOrders = array();
			foreach (array(
			'Length',
			'Width',
			'Height',
			) as $sDimention) {

				foreach ($this->getRequest(strtolower($sDimention)) as $sOrderId => $sValue) {
					$aOrders[$sOrderId]['PackageDimensions'][$sDimention] = (float) $sValue;
				}
			}
			foreach ($this->getRequest('weight') as $sOrderId => $sValue) {
				$aOrders[$sOrderId]['Weight']['Value'] = (float) $sValue;
				$aOrders[$sOrderId]['Weight']['Unit'] = getDBConfigValue($this->aMagnaSession['currentPlatform'] . '.shippinglabel.weight.unit', $this->aMagnaSession['mpID']);
				$aOrders[$sOrderId]['AmazonOrderId'] = $sOrderId;
			}

			foreach ($this->getRequest('date') as $sOrderId => $sValue) {
				$aOrders[$sOrderId]['ShippingDate'] = $sValue;
			}
			foreach ($this->getRequest('ItemList') as $sOrderId => $aItems) {
				foreach ($aItems as $sSku => $iQuantity) {
					$aOrders[$sOrderId]['ItemList'][] = array(
						'OrderItemId' => $sSku, //todo here we should have orderitemid instead of sku
						'Quantity' => (float) $iQuantity,
					);
				}
			}

			foreach ($this->getRequest('deliveryexpirience') as $sOrderId => $aItems) {
				$aOrders[$sOrderId]['ShippingServiceOptions']["DeliveryExperience"] = $aItems;
			}
			foreach ($this->getRequest('carrierwillpickup') as $sOrderId => $aItems) {
				$aOrders[$sOrderId]['ShippingServiceOptions']["CarrierWillPickUp"] = $aItems == 'true';
			}

			foreach ($this->getRequest('addressfrom') as $sOrderId => $iAddressId) {
				$aOrders[$sOrderId]["ShipFromAddress"] = $this->getAddressById($iAddressId);
			}
			foreach ($aOrders as $sOrderId => $aData) {
				$aOrderData =  $this->getSelectionData(array($this->getSelectionKey() => $sOrderId));
				$aData['globalinfo'] = $aOrderData['data']['globalinfo'];
				MagnaDB::gi()->query("
					UPDATE
					 " . $this->getSelectionTableName() . "
						 SET data = '".json_encode($aData)."'
					WHERE
						`session_id` = '" . session_id() . "'
						AND `mpID` = '" . $this->aMagnaSession['mpID'] . "'
						AND `selectionname` = '" . $this->getSelectionName() . "'
						AND `element_id` = '" . $sOrderId . "'
					");
				
			}
		} else {
			foreach ($this->getSelectionData() as $sOrderId => $aData) {
				$aOrderData =  $this->getSelectionData(array($this->getSelectionKey() => $sOrderId));
				if (!isset($aOrderData['data']['Weight'])) {
					$this->getUrl(false, false, false, array('view' => 'upload', 'subview' => 'form'));
				}
			}
		}

		return $this;
	}
	protected $aConfigAddress = null;
	public function getAddressById($iAddressId) {
		if($this->aConfigAddress === null){
			$aAddress = array();
			$aConfigAddress = getOneFromMultiOptionConfig($this->aMagnaSession['currentPlatform'] . '.shippinglabel.address', $this->aMagnaSession['mpID'], $iAddressId);
			if (empty($aConfigAddress['name'])) {
				$aAddress["Name"] = $aConfigAddress['company'];
			} else {
				$aAddress["Name"] = $aConfigAddress['name'];
			}

			if (!empty($aConfigAddress['company']) && ($aConfigAddress['company'] != $aConfigAddress['name'])) {
				$aAddress["AddressLine1"] = $aConfigAddress['company'];
				$aAddress["AddressLine2"] = $aConfigAddress['streetandnr'];
			} else {
				$aAddress["AddressLine1"] = $aConfigAddress['streetandnr'];
			}
	//		$aAddress["_DistrictOrCounty"] = $aConfigAddress['state'];
			$aAddress["Email"] = $aConfigAddress['email'];
			$aAddress["City"] = $aConfigAddress['city'];
	//		$aAddress["_StateOrProvinceCode"] = $aConfigAddress['state'];
			$aAddress["PostalCode"] = $aConfigAddress['zip'];
			$aAddress["CountryCode"] = $aConfigAddress['country'];
			$aAddress["Phone"] = $aConfigAddress['phone'];
			$this->aConfigAddress = $aAddress;
		}
		return $this->aConfigAddress;
	}

	public function isSelectable() {
		return true;
	}

	public function showPagination() {
		return false;
	}

	protected function getSelectionName() {
		return 'amazon_shippinglabel_orderlist';
	}

	protected function getMainTemplateName() {
		return 'shippingmethod';
	}

	
	protected function addDependencies() {
		$this
			->addDependency('MLOrderlistDependencyShippingmethodToFormAction', array())
			->addDependency('MLOrderlistDependencyShippingmethodToSummaryAction', array('selectionname' => $this->getSelectionName(), 'selectiontablename' => $this->getSelectionTableName()))
		;
	}
}
