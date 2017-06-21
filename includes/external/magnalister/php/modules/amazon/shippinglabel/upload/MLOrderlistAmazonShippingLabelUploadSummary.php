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

class MLOrderlistAmazonShippingLabelUploadSummary extends MLOrderlistAmazonAbstract {

	protected $aListConfig = array(
		array(
			'head' => array(
				'attributes' => '',
				'fieldname' => 'BuyerName',
				'content' => 'ML_AMAZON_SHIPPINGLABEL_RECEIVER',
			),
			'field' => array('absolute_data'),
		),
		array(
			'head' => array(
				'attributes' => '',
				'fieldname' => 'ShippingDate',
				'content' => 'ML_AMAZON_SHIPPINGLABEL_SHIPPINGDATE',
			),
			'field' => array('absolute_data'),
		),
		array(
			'head' => array(
				'attributes' => '',
				'fieldname' => 'Weight',
				'content' => 'ML_AMAZON_SHIPPINGLABEL_WEIGHT',
			),
			'field' => array('absolute_data'),
		),
		array(
			'head' => array(
				'attributes' => 'class="price"',
				'fieldname' => 'CarrierName',
				'content' => 'ML_AMAZON_SHIPPINGLABEL_CARRIERNAME',
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
				'fieldname' => 'UnitPrice',
				'content' => 'ML_AMAZON_SHIPPINGLABEL_UNITPRICE',
			),
			'field' => array('absolute_data'),
		),
		array(
			'head' => array(
				'attributes' => '',
				'fieldname' => 'TotalPrice',
				'content' => 'ML_AMAZON_SHIPPINGLABEL_TOTALPRICE',
			),
			'field' => array('absolute_data'),
		),
	);

	public function __construct() {
		parent::__construct();
		$this->saveData();
	}

	protected function saveData() {
		if ($this->getRequest('shippingserviceid') !== null) {
			foreach ($this->getRequest('shippingserviceid') as $sOrderId => $aShippingService) {
				$aShippingServiceInfo = json_decode($aShippingService, true);
				$aOrderData = $this->getSelectionData(array($this->getSelectionKey() => $sOrderId));

				$aOrderData['data']['ShippingServiceId'] = $aShippingServiceInfo['ShippingServiceId'];
				$aOrderData['data']['globalinfo']['shippingservice'] = $aShippingServiceInfo;

				MagnaDB::gi()->query("
					UPDATE
					 " . $this->getSelectionTableName() . "
						 SET data = '" . json_encode($aOrderData['data']) . "'
					WHERE
						`session_id` = '" . session_id() . "'
						AND `mpID` = '" . $this->aMagnaSession['mpID'] . "'
						AND `selectionname` = '" . $this->getSelectionName() . "'
						AND `element_id` = '" . $sOrderId . "'
					");
			}
		}
		return $this;
	}

	protected function getOrders() {
		$aList = array();
		$aOrders = $this->getSelectionData();
		foreach ($aOrders as $aOrder) {
			$aData = $aOrder['data'];
			//               foreach($aData['globalinfo']['Products'] as  $aProduct){
			//                   $sSku = '';
			
			$oPrice = $this->getPrice()->setPrice($aData['globalinfo']['shippingservice']['Rate']['Amount']);
			$fPrice = $oPrice->setCurrency($aData['globalinfo']['shippingservice']['Rate']['CurrencyCode'])->format(true);
			//                   foreach($aData['ItemList'] as $aItem){
			//                       if($aItem['OrderItemId'] == $aProduct['AmazonOrderItemID']){
			//                           $sSku = $aProduct['SKU'];
			//                       }
			//                   }
			//                   if($sSku == ''){
			//                       continue;
			//                   }
			$aList[] = array(
				'BuyerName' => $aData['globalinfo']['AddressSets']['Shipping']['Firstname'] . ' ' . $aData['globalinfo']['AddressSets']['Shipping']['Lastname'],
				'ShippingDate' => $aData['ShippingDate'],
				'Weight' => $aData['Weight']['Value'] . ' ' . $aData['Weight']['Unit'],
				//                        'SKU' => $sSku,
				'ShippingServiceName' => $aData['globalinfo']['shippingservice']['ShippingServiceName'],
				'CarrierName' => $aData['globalinfo']['shippingservice']['CarrierName'],
				'UnitPrice' => $fPrice,
				'TotalPrice' => $fPrice,
			);
			//               }
		}
		return $aList;
	}

	protected function addDependencies() {
		$this
			->addDependency('MLOrderlistDependencySummaryToShippingmethodAction', array())
			->addDependency('MLOrderlistDependencySubmitSummaryAction', array())
		;
	}

	protected function renderAjax(){
		$timer = microtime(true);
		require_once(DIR_MAGNALISTER_MODULES . 'amazon/classes/MLAmazonServiceShipping.php');
		$oService = new MLAmazonServiceShipping();

		$aOrders = $this->getSelectionData();
		$iCount = count($aOrders);
		$oService->setOrders($aOrders);
		$oService->confirmShipping();
		$sDownload = $oService->downloadShippingLabel();
		if($sDownload != ''){
			MagnaDB::gi()->query("
				DELETE FROM
				 " . TABLE_MAGNA_GLOBAL_SELECTION . "
				WHERE
					`session_id` = '" . session_id() . "'
					AND `mpID` = '" . $this->aMagnaSession['mpID'] . "'
					AND `selectionname` = '".$this->getSelectionName()."'
			");
		}
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
		header('Content-type: application/json');
		$aAjax['itemsPerBatch'] = 10;
		$aAjax['ignoreErrors'] = true;
		$aAjax['state']['total'] = $iCount;
		$aAjax['state']['submitted'] = $iCount;
		$aAjax['state']['success'] = $iCount;
		$aAjax['state']['failed'] = 0;
		$aAjax['proceed'] = false;
		$aAjax['redirect'] = $this->getUrl(false, false, false, array('view' => 'overview', 'subview' => '' , 'kind' => 'ajax'));
		$aAjax['finaldialogs'] = array();
		$aAjax['showWithoutDialog'] ='<a style="display:hidden;"  target="_blank" id="downloadshippinglabel" href="'.$sDownload.'"></a>';		
		$aAjax['timer'] = microtime2human(microtime(true) -  $timer);
		$aAjax['memory'] = memory_usage();
		
		echo json_encode($aAjax);
	}
	
	protected function getSelectionName() {
		return 'amazon_shippinglabel_orderlist';
	}

	protected function getMainTemplateName() {
		return 'summary';
	}

	protected function getPreparedStatusIndicator($aRow) {
		if ($aRow == 'full') {
			return html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/green_dot.png', ML_AMAZON_LABEL_APPLY_PREPARE_COMPLETE, 9, 9);
		} elseif ($aRow == 'partly') {
			return html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/red_dot.png', ML_AMAZON_LABEL_APPLY_PREPARE_INCOMPLETE, 9, 9);
		} else {
			return html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/grey_dot.png', ML_AMAZON_LABEL_APPLY_NOT_PREPARED, 9, 9);
		}
	}
	protected function addRequestSort() {
		
	}
}
