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
require_once(DIR_MAGNALISTER_MODULES.'dawanda/classes/MLProductListDawandaAbstract.php');

class DawandaPrepareProductList extends MLProductListDawandaAbstract {

	public function __construct() {
		$this->aListConfig[] = array(
			'head' => array(
				'attributes'	=> 'class="matched"',
				'content'		=> 'ML_HOOD_LABEL_PREPARED',
			),
			'field' => array('preparestatusindicator'),
		);
		parent::__construct();
		$this
			->addDependency('MLProductListDependencyDawandaPrepareFormAction', array('selectionname' => $this->getSelectionName()))
			->addDependency('MLProductListDependencyDawandaPrepareStatusFilter')
			
		;
	}

	protected function getSelectionName() {
		return 'apply';
	}

	protected function getPreparedStatusIndicator($aRow){
		$sVerified = $this->getPrepareData($aRow, 'Verified');
		if (empty($sVerified)) {
			return html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/grey_dot.png', ML_HOOD_PRODUCT_MATCHED_NO, 9, 9);
		} elseif ('OK' == $sVerified) {
			return html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/green_dot.png', ML_HOOD_PRODUCT_PREPARED_OK, 9, 9);
		} elseif ('EMPTY' == $sVerified) {
			return html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/white_dot.png', ML_EBAY_PRODUCT_PREPARED_FAULTY_BUT_MP, 9, 9);
		} else {
			return html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/red_dot.png', ML_HOOD_PRODUCT_PREPARED_FAULTY, 9, 9);
		}
	}
}
