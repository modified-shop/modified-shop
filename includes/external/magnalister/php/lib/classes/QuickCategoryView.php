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
 * $Id$ QuickCategoryView.php 1646 2012-05-14 17:27:15Z mba $
 *
 * (c) 2012 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES . 'lib/classes/SimpleCategoryView.php');

abstract class QuickCategoryView extends SimpleCategoryView {
	protected $blUseParent = false;
	
	public function __construct($cPath = 0, $settings = array(), $sorting = false, $search = '', $allowedProductIDs = array()) {
		//$this->blUseParent = $search != ''; //subclasses define this
		parent::__construct($cPath, $settings, $sorting, $search, $allowedProductIDs);
	}
	
	protected function useParentClass($blAllProductIds = false) {
		$useParentClass = ($this->isAjax || $this->blUseParent || $blAllProductIds || (count($this->allowedProductIDs) < 1000));
		#echo var_dump_pre($useParentClass, __METHOD__);
		return $useParentClass;
	}
	
	/**
	 * Only fetch the product ids of this category level and do not recurse through
	 * all child categories if the result is only used to render the view.
	 * Still fetch all products_ids if the result will be used in ajax calls.
	 *
	 * @param int $cID
	 *     Id of the category to get the product_ids for.
	 * @return array
	 *     List of products_ids
	 */
	public function getProductIDsByCategoryID($cID, $blAllProductIds = false) {
		return $this->useParentClass($blAllProductIds)
			? parent::getProductIDsByCategoryID($cID)
			: $this->getChildProductsOfThisLevel($cID);
	}
	
	/**
	 * count products recursive in categories
	 * @param int $iId category-id
	 * @return array count of productstocategories recursive
	*/
	abstract protected function getProductsCountOfCategoryInfo($iId);
	
	protected function filterCategoriesList() {
		if ($this->useParentClass()) {
			return parent::filterCategoriesList();
		}
		
		// This is one essential part which makes the QuickCategoryView quick.
		// It does not filter the categories by processing all child categories
		// and their products recursively.
		// @see QuickCategoryView::getProductIDsByCategoryID()
		return '';
	}
	
	protected function retriveCategoriesListAddCategory($category) {
		if ($this->useParentClass()) {
			return parent::retriveCategoriesListAddCategory($category);
		}
		
		$aInfo = $this->getProductsCountOfCategoryInfo($category['categories_id']);
		
		if ($aInfo['iTotal'] > 0) {
			$category['iProductsTotal'] = $aInfo['iTotal'];
			$category['allproductsids'] = array();
			$this->list['categories'][$category['categories_id']] = $category;
		}
	}
	
	/**
	 *
	 * @param type $iCatId
	 * @param type string   $sCheckQuery (select count,checked were categories_id in (%%catIds%%))
	 * @return string 
	 */
	public function renderAdditionalCategoryInfo($iCatId) {
		$aInfo = $this->getProductsCountOfCategoryInfo($iCatId);
		$iFailed = $aInfo['iFailed'];
		$iMatched = $aInfo['iMatched'];
		$iTotal = $aInfo['iTotal'];
		$sDebug = $iMatched.(
			MAGNA_DEBUG 
				? '/<span title="total">'.$iTotal.'</span>'.(
					($iFailed > 0)
						? ' <span style="color:red">('.$iFailed.')</span>'
						: ''
					)
				: ''
			);
		$sTableTemplate = '<table class="nostyle"><tbody><tr><td>%s</td><td class="textright nowrap">&nbsp;%s</td></tr></tbody></table>';
		if (($iFailed == 0) && ($iMatched == 0)) { /* Nichts gematched und auch kein matching probiert */
			$sHtml = '
				<td class="nowrap" title="'.ML_EBAY_CATEGORY_PREPARED_NONE.'">
					'.sprintf($sTableTemplate, html_image(DIR_MAGNALISTER_WS_IMAGES.'status/grey_dot.png', ML_EBAY_CATEGORY_PREPARED_NONE, 9, 9), $sDebug).'
				</td>';
		
		} else if ($iFailed == $iTotal) { /* Keine gematched */
			$sHtml = '
				<td title="'.ML_EBAY_CATEGORY_PREPARED_FAULTY.'">
					'.sprintf($sTableTemplate, html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/red_dot.png', ML_EBAY_CATEGORY_PREPARED_FAULTY, 9, 9), $sDebug).'
				</td>';
		
		} else if ($iMatched == $iTotal) {  /* Alle Items in Category gematched */
			$sHtml = '
				<td title="'.ML_EBAY_CATEGORY_PREPARED_ALL.'">
					'.sprintf($sTableTemplate, html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/green_dot.png', ML_EBAY_CATEGORY_PREPARED_ALL, 9, 9), $sDebug).'
				</td>';
		
		} else if (($iFailed > 0) || ($iMatched > 0)) { /* Einige nicht erfolgreich gematched */
			$sHtml = '
				<td title="'.ML_EBAY_CATEGORY_PREPARED_INCOMPLETE.'">
					'.sprintf($sTableTemplate, html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/yellow_dot.png', ML_EBAY_CATEGORY_PREPARED_INCOMPLETE, 9, 9), $sDebug).'
				</td>';
		
		} else {
			$sHtml = '
				<td title="'.ML_ERROR_UNKNOWN.' $totalItems:'.$iTotal.' $itemsMatched:'.$iMatched.' $itemsFailed:'.$iFailed.'">
					'.sprintf(
							$sTableTemplate, 
							html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/red_dot.png', ML_ERROR_UNKNOWN, 9, 9).
								html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/red_dot.png', ML_ERROR_UNKNOWN, 9, 9), 
							''
					).'
				</td>';
		}
		return $sHtml;
	}
}
