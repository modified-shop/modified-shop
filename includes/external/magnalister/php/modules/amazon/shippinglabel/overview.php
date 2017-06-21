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

class MLOrderlistAmazonShippingLabelOverview extends MLOrderlistAmazonAbstract {

	public function __construct() {
		parent::__construct();
		$languages = getDBConfigValue($this->aMagnaSession['currentPlatform'] . '.lang', $this->aMagnaSession['mpID'], $_SESSION['languages_id']);
		MLProduct::gi()->setLanguage($languages);
	}

	
	protected $sMessage = null;
	protected $sDownloadLink = null;
	protected $aListConfig = array(
		array(
			'head' => array(
				'attributes' => 'class="nowrap edit"',
				'content' => '',
			),
			'field' => array('selection')
		),
		array(
			'head' => array(
				'attributes' => '',
				'fieldname' => 'CreatedDate',
				'content' => 'ML_AMAZON_SHIPPINGLABEL_OVERVIEW_CREATEDDATE'
			),
			'field' => array('absolute_data'),
		),
		array(
			'head' => array(
				'attributes' => '',
				'fieldname' => 'ShippingDate',
				'content' => 'ML_GENERIC_SHIPPING',
			),
			'field' => array('absolute_data'),
		),
		array(
			'head' => array(
				'attributes' => '',
				'fieldname' => 'ShipmentId',
				'content' => 'Shipping ID',
			),
			'field' => array('absolute_data'),
		),
		array(
			'head' => array(
				'attributes' => '',
				'fieldname' => 'AmazonOrderId',
				'content' => 'ML_AMAZON_SHIPPINGLABEL_ORDERLIST_AMAZONORDERID',
			),
			'field' => array('absolute_data'),
		),
		array(
			'head' => array(
				'attributes' => '',
				'fieldname' => 'CustomerName',
				'content' => 'ML_AMAZON_SHIPPINGLABEL_ORDERLIST_BUYERNAME',
			),
			'field' => array('absolute_data'),
		),
		array(
			'head' => array(
				'attributes' => '',
				'fieldname' => 'Product',
				'content' => 'ML_LABEL_PRODUCTS',
			),
			'field' => array('absolute_data'),
		),
		array(
			'head' => array(
				'attributes' => '',
				'fieldname' => 'ShippingCost',
				'content' => 'ML_GENERIC_SHIPPING_COST',
			),
			'field' => array('absolute_data'),
		),
		array(
			'head' => array(
				'attributes' => '',
				'fieldname' => 'SenderAndTrackingId',
				'content' => 'ML_AMAZON_SHIPPINGLABEL_OVERVIEW_SENDERANDTRACKINGID',
			),
			'field' => array('absolute_data'),
		),
	);

	protected function buildRequest() {
		$this->oApiRequest = MLApiRequest::factoryApiRequestClass()->set(array(
				'ACTION' => 'MFS_GetShipmentList'
			))->limit(($this->getCurrentPage() - 1) * $this->iRowsPerPage, $this->iRowsPerPage);
		return $this;
	}

	
	protected function getSelectionKey() {
		return 'ShipmentId';
	}
	
	protected function getOrders() {
		$aOrders = parent::getOrders();
		foreach ($aOrders as &$aOrder) {
                    if(isset($aOrder['ShippingService']) && !empty($aOrder['ShippingService'])){
                        $oPrice = $this->getPrice()->setPrice($aOrder['ShippingService']['Rate']['Amount']);
			$aOrder['ShippingCost'] = $oPrice->setCurrency($aOrder['ShippingService']['Rate']['CurrencyCode'])->format(true);
                        $aOrder['SenderAndTrackingId'] = $aOrder['ShippingService']['CarrierName'] . ' <br>' . $aOrder['TrackingId'];
                        $aOrder['CustomerName'] = $aOrder['Address']['ShipToAddress']['Name'];
                        
                    }
                    if(isset($aOrder['ShippingService']) && !empty($aOrder['ShippingService'])){
                        $aOrder['CustomerName'] = $aOrder['Address']['ShipToAddress']['Name'];
                        
                    }
                            
                    if(isset($aOrder['ItemList']) && !empty($aOrder['ItemList'])){
                        $aProduct = current($aOrder['ItemList']);
                        $aOrder['Product'] = isset($aProduct['ProductName'])?$aProduct['ProductName']:'---';                        
                    }
                }
		return $aOrders;
	}


	protected function addDependencies() {
		$this
			->addDependency('MLOrderlistDependencySelectionAction', array('selectionname' => $this->getSelectionName(), 'selectiontablename' => $this->getSelectionTableName()))
			->addDependency('MLOrderlistDependencyDownloadAction', array())
			->addDependency('MLOrderlistDependencyCancelAction', array())
			->addDependency('MLOrderlistDependencyDeleteAction', array())
		;
	}

	protected function getSelectionName() {
		return 'amazon_shippinglabel_overview';
	}

	protected function getMainTemplateName() {
		return 'overview';
	}

	protected function cancelShipping() {
		$aOrders = $this->getSelectionData();
		$aOrderIds = array();
		foreach ($aOrders as $oOrder) {
			$aOrderIds[] = $oOrder['element_id'];
		}
		try {
			$aResponse = MagnaConnector::gi()->submitRequest(
				array(
					'ACTION' => 'MFS_CancelShipment',
					'DATA' => array(
						'ShipmentIds' => $aOrderIds
					)
			));
			return ML_AMAZON_SHIPPINGLABEL_OVERVIEW_CANCELSHIPPINGLABLE;
		} catch (MagnaException $oExc) {
			
		}
		return null;
	}

	protected function deleteShipping() {
		$aOrders = $this->getSelectionData();
		$aOrderIds = array();
		foreach ($aOrders as $oOrder) {
			$aOrderIds[] = $oOrder['element_id'];
		}
		try {
			$aResponse = MagnaConnector::gi()->submitRequest(
				array(
					'ACTION' => 'MFS_DeleteShipmentFromList',
					'DATA' => array(
						'ShipmentIds' => $aOrderIds
					)
			));
			return ML_AMAZON_SHIPPINGLABEL_OVERVIEW_DELETESHIPPINGLABLE;
		} catch (MagnaException $oExc) {
			
		}
		return null;
	}

	public function render() {
		$sMethod = $this->getRequest('method');
		if ($sMethod != null) {
			$this->sMessage = $this->{$sMethod . 'Shipping'}();
		}
		return parent::render();
	}

	protected function downloadShipping() {
		require_once(DIR_MAGNALISTER_MODULES . 'amazon/classes/MLAmazonServiceShipping.php');
		$oService = new MLAmazonServiceShipping();
		$aOrders = $this->getSelectionData();
		$this->sDownloadLink = $oService
			->setOrders($aOrders)
			->downloadShippingLabel();
	}

	public function getDownloadLink() {
		return $this->sDownloadLink;
	}
	
	
	public function getMessage() {
		return $this->sMessage;
	}

}

$oOrderlist = new MLOrderlistAmazonShippingLabelOverview();
echo $oOrderlist->render();