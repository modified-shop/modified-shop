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

class MLOrderlistAmazonShippinglabelUploadOrderlist extends MLOrderlistAmazonAbstract {

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
				'fieldname' => 'PurchaseDate',
				'content' => 'ML_AMAZON_SHIPPINGLABEL_ORDERLIST_PURCHASEDATE',
				'sort' => array('param' => 'PurchaseDate', 'field' => 'PurchaseDate'),
			),
			'field' => array('absolute_data'),
		),
		array(
			'head' => array(
				'attributes' => '',
				'fieldname' => 'AmazonOrderID',
				'content' => 'ML_AMAZON_SHIPPINGLABEL_ORDERLIST_AMAZONORDERID',
				'sort' => array('param' => 'AmazonOrderID', 'field' => 'AmazonOrderID'),
			),
			'field' => array('absolute_data'),
		),
		array(
			'head' => array(
				'attributes' => '',
				'fieldname' => 'BuyerName',
				'content' => 'ML_AMAZON_SHIPPINGLABEL_ORDERLIST_BUYERNAME',
				'sort' => array('param' => 'BuyerName', 'field' => 'BuyerName'),
			),
			'field' => array('absolute_data'),
		),
		array(
			'head' => array(
				'attributes' => 'class="price"',
				'fieldname' => 'Value',
				'content' => 'ML_AMAZON_SHIPPINGLABEL_ORDERLIST_PRICE',
			),
			'field' => array('price'),
		),
		array(
			'head' => array(
				'attributes' => '',
				'fieldname' => 'CurrentStatus',
				'content' => 'ML_AMAZON_SHIPPINGLABEL_ORDERLIST_CURRENTSTATUS',
				'sort' => array('param' => 'CurrentStatus', 'field' => 'CurrentStatus'),
			),
			'field' => array('absolute_data'),
		),
		array(
			'head' => array(
				'attributes' => '',
				'fieldname' => 'CompletelyShipped',
				'content' => 'ML_AMAZON_SHIPPINGLABEL_ORDERLIST_SHIPPINGSTATUS',
			),
			'field' => array('preparestatus'),
		),
	);

	protected function buildRequest() {
		$this->oApiRequest = MLApiRequest::factoryApiRequestClass()->set(array(
				'ACTION' => 'GetOrdersAcknowledgeStateForDateRange',
				'BEGIN' => date('Y-m-d H:i:s', time() - 60 * 60 * 24 * 30),
				'CompletelyShipped' => 'all',
			))->limit(($this->getCurrentPage() - 1) * $this->iRowsPerPage, $this->iRowsPerPage);
		return $this;
	}

	protected function addDependencies() {
		$this
			->addDependency('MLOrderlistDependencySearchFilter', array())
			->addDependency('MLOrderlistDependencySelectionAction', array('selectionname' => $this->getSelectionName(), 'selectiontablename' => $this->getSelectionTableName()))
			->addDependency('MLOrderlistDependencyStatusFilter', array('selectionname' => $this->getSelectionName()))
            ->addDependency('MLOrderlistDependencyShippingFilter', array('selectionname' => $this->getSelectionName()))
			->addDependency('MLOrderlistDependencyOrderlistToFormAction', array())
		;
	}

	protected function getSelectionName() {
		return 'amazon_shippinglabel_orderlist';
	}

	protected function getMainTemplateName() {
		return 'orderlist';
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

}
