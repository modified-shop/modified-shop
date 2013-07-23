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
    protected $aCatInfo=array();
    
    public function __construct($cPath = 0, $settings = array(), $sorting = false, $search = '', $allowedProductIDs = array()) {
		//$this->blUseParent = $search != ''; //subclasses define this
        parent::__construct($cPath, $settings, $sorting, $search, $allowedProductIDs);
    }

    /**
     * only need products of current cat
     * @param type $cID
     * @return type 
     */
    public function getProductIDsByCategoryID($cID,$blForce=false) {
        return $this->isAjax || $this->blUseParent || $blForce
        	? parent::getProductIDsByCategoryID($cID)
        	: $this->getChildProductsOfThisLevel($cID);
    }
    
    /**
     * count products recursive in categories
     * @param int $iId category-id
     * @return array count of productstocategories recursive
    */
    abstract protected function getProductsCountOfCategoryInfo($iId);

    /**
     * actual cat list 
     */
    protected function retriveList() {
        if ($this->blUseParent || $this->isAjax) {
            return parent::retriveList();
        } else {
            $aSort = $this->getSorting();
            $rQuery = MagnaDB::gi()->query('
                  SELECT c.categories_id, cd.categories_name, c.categories_image, c.parent_id
                    FROM '. TABLE_CATEGORIES . ' c, ' . TABLE_CATEGORIES_DESCRIPTION . ' cd 
                   WHERE c.categories_id = cd.categories_id
                         AND cd.language_id = "' . (int) $_SESSION['languages_id'] . '"
                         AND c.parent_id = "' . (int) $this->cPath . '"
                    ORDER BY ' . $aSort['cat'].'
            ');
            while ($aRow = MagnaDB::gi()->fetchNext($rQuery)) {
                $aInfo=$this->getProductsCountOfCategoryInfo($aRow['categories_id']);
                $iTotal=$aInfo['iTotal'];
                if( $iTotal>0 ){
                    $aRow['iProductsTotal']=$iTotal;
                    $aCat[$aRow['categories_id']] = $aRow;
                    $aCat[$aRow['categories_id']]['allproductsids'] = array();
                }
            }
            $this->list['categories'] = $aCat;
            $this->setupProductsQuery();
            $products = MagnaDB::gi()->fetchArray($this->productsQuery);
            $this->list['products'] = array();
            if (!empty($products)) {
                foreach ($products as $product) {
                    $this->list['products'][$product['products_id']] = $product;
                }
            }
        }
    }
    
    /**
     *
     * @param type $iCatId
     * @param type string   $sCheckQuery (select count,checked were categories_id in (%%catIds%%))
     * @return string 
     */
    public function renderAdditionalCategoryInfo($iCatId) {
        $aInfo=$this->getProductsCountOfCategoryInfo($iCatId);
        $iFailed = $aInfo['iFailed'];
        $iMatched = $aInfo['iMatched'];
        $iTotal=$aInfo['iTotal'];         
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
            $sHtml .= '
	            <td class="nowrap" title="'.ML_EBAY_CATEGORY_PREPARED_NONE.'">
	            	'.sprintf($sTableTemplate, html_image(DIR_MAGNALISTER_IMAGES.'status/grey_dot.png', ML_EBAY_CATEGORY_PREPARED_NONE, 12, 12), $sDebug).'
	            </td>';
        
        } else if($iFailed == $iTotal) { /* Keine gematched */
            $sHtml .= '
	            <td title="'.ML_EBAY_CATEGORY_PREPARED_FAULTY.'">
	            	'.sprintf($sTableTemplate, html_image(DIR_MAGNALISTER_IMAGES . 'status/red_dot.png', ML_EBAY_CATEGORY_PREPARED_FAULTY, 12, 12), $sDebug).'
	            </td>';
        
        } else if ($iMatched == $iTotal) {  /* Alle Items in Category gematched */
            $sHtml .= '
            	<td title="'.ML_EBAY_CATEGORY_PREPARED_ALL.'">
            		'.sprintf($sTableTemplate, html_image(DIR_MAGNALISTER_IMAGES . 'status/green_dot.png', ML_EBAY_CATEGORY_PREPARED_ALL, 12, 12), $sDebug).'
            	</td>';
        
        } else if (($iFailed > 0) || ($iMatched > 0)) { /* Einige nicht erfolgreich gematched */
            $sHtml .= '
            	<td title="'.ML_EBAY_CATEGORY_PREPARED_INCOMPLETE.'">
            		'.sprintf($sTableTemplate, html_image(DIR_MAGNALISTER_IMAGES . 'status/yellow_dot.png', ML_EBAY_CATEGORY_PREPARED_INCOMPLETE, 12, 12), $sDebug).'
            	</td>';
        
        } else {
            $sHtml .= '
            	<td title="'.ML_ERROR_UNKNOWN.' $totalItems:'.$iTotal.' $itemsMatched:'.$iMatched.' $itemsFailed:'.$iFailed.'">
            		'.sprintf(
            				$sTableTemplate, 
            				html_image(DIR_MAGNALISTER_IMAGES . 'status/red_dot.png', ML_ERROR_UNKNOWN, 12, 12).
            		  			html_image(DIR_MAGNALISTER_IMAGES . 'status/red_dot.png', ML_ERROR_UNKNOWN, 12, 12), 
            		  		''
            		).'
            	</td>';
        }
        return $sHtml;
    }
}
