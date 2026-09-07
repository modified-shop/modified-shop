<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

class TemuShipping {

	protected $mpID = 0;

	public function __construct() {
		global $_MagnaSession;
		$this->mpID = $_MagnaSession['mpID'];
	}

	public function getShippingServices($orderId) {
		try {
			$result = MagnaConnector::gi()->submitRequestCached(array(
				'ACTION' => 'GetShippingServices',
				'SUBSYSTEM' => 'Temu',
				'MARKETPLACEID' => $this->mpID,
				'DATA' => array('OrderId' => $orderId),
			), 30 * 60);
			return isset($result['DATA']) ? $result['DATA'] : array();
		} catch (MagnaException $e) {
			return array();
		}
	}

	public function createShipment($aData) {
		$aRequest = array(
			'ACTION' => 'CreateShipment',
			'SUBSYSTEM' => 'Temu',
			'MARKETPLACEID' => $this->mpID,
			'DATA' => array(
				'MOrderID' => $aData['MOrderID'],
			),
		);

		$blUseBuyShipping = (getDBConfigValue('temu.orderstatus.temu.usebuyshipping', $this->mpID, 'false') === 'true');
		$aRequest['DATA']['FulfillmentMode'] = $blUseBuyShipping ? 'auto' : 'self';

		$sWarehouseId = isset($aData['WarehouseId'])
			? $aData['WarehouseId']
			: getDBConfigValue('temu.orderstatus.temu.warehouseid', $this->mpID, '');
		if (!empty($sWarehouseId)) {
			$aRequest['DATA']['WarehouseId'] = $sWarehouseId;
		}

		if ($blUseBuyShipping) {
			$aRequest['DATA']['BuyShipping'] = array(
				'Weight' => isset($aData['Weight'])
					? $aData['Weight']
					: getDBConfigValue('temu.orderstatus.temu.buyshipping.weightamount', $this->mpID, '1'),
				'WeightUnit' => getDBConfigValue('temu.orderstatus.temu.buyshipping.weightunit', $this->mpID, 'KILOGRAM'),
				'Length' => isset($aData['Length'])
					? $aData['Length']
					: getDBConfigValue('temu.orderstatus.temu.buyshipping.length', $this->mpID, '30'),
				'Width' => isset($aData['Width'])
					? $aData['Width']
					: getDBConfigValue('temu.orderstatus.temu.buyshipping.width', $this->mpID, '20'),
				'Height' => isset($aData['Height'])
					? $aData['Height']
					: getDBConfigValue('temu.orderstatus.temu.buyshipping.height', $this->mpID, '15'),
				'DimensionUnit' => getDBConfigValue('temu.orderstatus.temu.buyshipping.dimensionunit', $this->mpID, 'CENTIMETER'),
				'ShipLater' => (bool)getDBConfigValue('temu.orderstatus.temu.buyshipping.shiplater', $this->mpID, false),
				'ShipLogisticsType' => getDBConfigValue('temu.orderstatus.temu.buyshipping.logisticstype', $this->mpID, ''),
			);
		} else {
			$sCarrierId = isset($aData['CarrierId'])
				? $aData['CarrierId']
				: getDBConfigValue('temu.orderstatus.sendcarrier.select', $this->mpID, '');
			if (!empty($sCarrierId)) {
				$aRequest['DATA']['CarrierId'] = $sCarrierId;
			}
			if (!empty($aData['TrackingNumber'])) {
				$aRequest['DATA']['TrackingNumber'] = $aData['TrackingNumber'];
			}
		}

		try {
			$result = MagnaConnector::gi()->submitRequest($aRequest);
			return $result;
		} catch (MagnaException $e) {
			return array('STATUS' => 'ERROR', 'ERRORS' => array(array('ErrorMessage' => $e->getMessage())));
		}
	}

	public function confirmShipment($orderId) {
		return $this->createShipment(array('MOrderID' => $orderId));
	}

	public function renderOverview() {
		global $_MagnaSession, $_url;
		$mpID = $_MagnaSession['mpID'];

		$sHtml = '<h2>'.ML_TEMU_SHIPPING_OVERVIEW_TITLE.'</h2>';

		try {
			$result = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'GetShipments',
				'SUBSYSTEM' => 'Temu',
				'MARKETPLACEID' => $mpID,
			));
			if (isset($result['DATA']) && is_array($result['DATA'])) {
				$sHtml .= '<table class="magna"><thead><tr>';
				$sHtml .= '<th>Order ID</th><th>Status</th><th>'.ML_TEMU_LABEL_CARRIER.'</th><th>'.ML_TEMU_LABEL_TRACKING_NUMBER.'</th>';
				$sHtml .= '</tr></thead><tbody>';
				foreach ($result['DATA'] as $aShipment) {
					$sHtml .= '<tr>';
					$sHtml .= '<td>'.(isset($aShipment['MOrderID']) ? $aShipment['MOrderID'] : '').'</td>';
					$sHtml .= '<td>'.(isset($aShipment['Status']) ? $aShipment['Status'] : '').'</td>';
					$sHtml .= '<td>'.(isset($aShipment['CarrierName']) ? $aShipment['CarrierName'] : '').'</td>';
					$sHtml .= '<td>'.(isset($aShipment['TrackingNumber']) ? $aShipment['TrackingNumber'] : '').'</td>';
					$sHtml .= '</tr>';
				}
				$sHtml .= '</tbody></table>';
			} else {
				$sHtml .= '<p class="noticeBox">'.ML_GENERIC_NO_INVENTORY_ITEMS.'</p>';
			}
		} catch (MagnaException $e) {
			$sHtml .= '<p class="errorBox">'.$e->getMessage().'</p>';
		}

		return $sHtml;
	}
}
