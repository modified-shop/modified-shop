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
require_once(DIR_MAGNALISTER_MODULES . 'magnacompatible/classes/MLProductListMagnaCompatibleAbstract.php');

class MagnaCompatibleCategoryMatchingProductList extends MLProductListMagnaCompatibleAbstract {

	public function __construct() {
		$this->aListConfig[] = array(
			'head' => array(
				'attributes' => 'class="lowestprice"',
				'content' => 'ML_MAGNACOMPAT_LABEL_CATEGORY',
			),
			'field' => array('magnacompatmpcategory'),
		);
		$this->aListConfig[] = array(
			'head' => array(
				'attributes' => 'class="matched"',
				'content' => 'ML_MAGNACOMPAT_LABEL_PREPARED',
			),
			'field' => array('preparestatusindicator'),
		);
		parent::__construct();
		$this
			->addDependency('MLProductListDependencyMagnaCompatiblePrepareFormAction', array('selectionname' => $this->getSelectionName()))
			->addDependency('MLProductListDependencyMagnaCompatiblePrepareStatusFilter')
		;
	}

	protected function getSelectionName() {
		return 'catmatch';
	}

	protected function getMarketPlaceCategory($aRow) {
		$aData = $this->getPrepareData($aRow);
		if ($aData !== false) {
			$matchMPShopCats = !getDBConfigValue(array($this->aMagnaSession['currentPlatform'] . '.catmatch.mpshopcats', 'val'), $this->aMagnaSession['mpID'], false);
			return '<table class="nostyle"><tbody>
			<tr><td>MP:</td><td>' . (empty($aData['mp_category_id']) ? '&mdash;' : $aData['mp_category_id']) . '</td><tr>
			' . ($matchMPShopCats ? ('<tr><td>Store:</td><td>' . (empty($aData['store_category_id']) ? '&mdash;' : $aData['store_category_id']) .
					'</td><tr>') : ''
					) . '
			</tbody></table>';
		}
		return '&mdash;';
	}

	protected function getPreparedStatusIndicator($aRow) {
		$aData = $this->getPrepareData($aRow);
		if ($aData !== false) {
			if ($aData['mp_category_id'] != '') {
				return html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/green_dot.png', ML_MAGNACOMPAT_LABEL_CATMATCH_PREPARE_COMPLETE, 9, 9);
			} else {
				return html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/red_dot.png', ML_MAGNACOMPAT_LABEL_CATMATCH_PREPARE_INCOMPLETE, 9, 9);
			}
		}
		return html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/grey_dot.png', ML_MAGNACOMPAT_LABEL_CATMATCH_NOT_PREPARED, 9, 9);
	}

}
