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
class MLAmazonServiceShipping {

	protected $aOrders = array();

	public function __construct() {
		global $_MagnaSession;
		$this->aMagnaSession = &$_MagnaSession;
	}

	public function setOrders($aOrders) {
		$this->aOrders = $aOrders;
		return $this;
	}

	public function getOrders() {
		return $this->aOrders;
	}

	public function getShippingService() {
		$aData = array();
		$aList = array();
		$aOrders = $this->getOrders();
		$aGlobalData = array();
		foreach ($aOrders as $aOrder) {
			$iOrderId = $aOrder['element_id'];
			$aData[$iOrderId] = $aOrder['data'];
			$aGlobalData[$iOrderId] = $aData[$iOrderId]['globalinfo'];
			unset($aData[$iOrderId]['globalinfo']);
			if (isset($aData['ShippingServiceId'])) {
				unset($aData['ShippingServiceId']);
			}
			$aData[$iOrderId]["PackageDimensions"]['Unit'] = getDBConfigValue($this->aMagnaSession['currentPlatform'] . '.shippinglabel.size.unit', $this->aMagnaSession['mpID']);
		}

		foreach ($aData as $sOrderId => $aOrder) {
			$aList[$sOrderId] = $aGlobalData[$sOrderId];
//			for testing you can uncomment this line
//			$aList[$sOrderId]['shippingservice'] = $this->testShippingData();continue;
			try {
				$aResponse = MagnaConnector::gi()->submitRequest(
					array(
						'ACTION' => 'MFS_GetShippingServices',
						'DATA' => $aOrder,
				));
				if (!isset($aResponse['DATA']) || $aResponse['STATUS'] != 'SUCCESS' || !is_array($aResponse['DATA'])) {
					throw new Exception('There is a problem to get list of orders');
				} else {
					$aList[$sOrderId]['shippingservice'] = $aResponse['DATA']['ShippingServices'];
				}
			} catch (MagnaException $ex) {
				$aList[$sOrderId]['shippingservice'] = array();
			} catch (Exception $ex) {
				$aList[$sOrderId]['shippingservice'] = array();
			}
		}
		return $aList;
	}

	public function confirmShipping() {
		foreach ($this->aOrders as &$aOrder) {
			$aData = $aOrder['data'];
			unset($aData['globalinfo']);

			$aData['ShippingServiceOptions']["DeliveryExperience"] = getDBConfigValue($this->aMagnaSession['currentPlatform'] . '.shippingservice.deliveryexpirience', $this->aMagnaSession['mpID']);
			$aData['ShippingServiceOptions']["CarrierWillPickUp"] = getDBConfigValue($this->aMagnaSession['currentPlatform'] . '.shippingservice.carrierwillpickup', $this->aMagnaSession['mpID']) == 'true';
			$aData["PackageDimensions"]['Unit'] = getDBConfigValue($this->aMagnaSession['currentPlatform'] . '.shippinglabel.size.unit', $this->aMagnaSession['mpID']);

			try {
				$aRequest = array(
					'ACTION' => 'MFS_CreateShipment',
					'DATA' => $aData,
				);
				$aResponse = MagnaConnector::gi()->submitRequest(
					$aRequest
				);
				if (!isset($aResponse['DATA']) || $aResponse['STATUS'] != 'SUCCESS' || !is_array($aResponse['DATA'])) {
					throw new Exception('There is a problem to get list of orders');
				} else {
					$aOrder['data']['originaldata']['ShipmentId'] = $aResponse['DATA']['ShipmentId'];
				}
			} catch (MagnaException $ex) {
				
			} catch (Exception $ex) {
				
			}
		}
	}

	public function downloadShippingLabel() {
		$sDownloadLink = null;
		$aData = array();
		foreach ($this->aOrders as $aOrder) {
			$aOrderData = $aOrder['data'];
			$aData['ShipmentIds'][] = isset($aOrderData['originaldata']['ShipmentId']) ? $aOrderData['originaldata']['ShipmentId'] : $aOrder['element_id'];
		}
		try {
			$aRequest = array(
				'ACTION' => 'MFS_DownloadShipment',
				'DATA' => $aData,
			);

			$aResponse = MagnaConnector::gi()->submitRequest(
				$aRequest
			);

			if (isset($aResponse['DATA']) && $aResponse['STATUS'] == 'SUCCESS' && is_array($aResponse['DATA']) && isset($aResponse['DATA']['DownloadLink'])) {
				$sDownloadLink = $aResponse['DATA']['DownloadLink'];
			}
		} catch (MagnaException $ex) {
			
		} catch (Exception $ex) {
			
		}
		return $sDownloadLink;
	}

	/**
	 * @todo Description
	 * @return boolean
	 */
	public function haveError() {
		return false;
	}

	protected function testShippingData() {
		return json_decode('[
            {
                "ShippingServiceName": "DHL Paket bis 5 kg",
                "CarrierName": "DHL",
                "ShippingServiceId": "DHL_PAKET_5KG",
                "ShippingServiceOfferId": "....",
                "ShipDate": "2016-02-15 18:11:53",
                "EarliestEstimatedDeliveryDate": "2016-02-17 00:00:00",
                "LatestEstimatedDeliveryDate": "2016-02-18 00:00:00",
                "Rate": {
                    "CurrencyCode": "EUR",
                    "Amount": "5.99"
                },
                "ShippingServiceOptions": {
                    "DeliveryExperience": "DeliveryConfirmationWithoutSignature",
                    "CarrierWillPickUp": "false"
                }
            },
            {
                "ShippingServiceName": "DHL Paket bis 2 kg",
                "CarrierName": "DHL",
                "ShippingServiceId": "DHL_PAKET_2KG",
                "ShippingServiceOfferId": "......",
                "ShipDate": "2016-02-15 18:11:53",
                "EarliestEstimatedDeliveryDate": "2016-02-17 00:00:00",
                "LatestEstimatedDeliveryDate": "2016-02-18 00:00:00",
                "Rate": {
                    "CurrencyCode": "EUR",
                    "Amount": "4.99"
                },
                "ShippingServiceOptions": {
                    "DeliveryExperience": "DeliveryConfirmationWithoutSignature",
                    "CarrierWillPickUp": "false"
                }
            }
        ]', true);
	}

}
