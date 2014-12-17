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
 * $Id: PrepareCategoryView.php 699 2011-01-17 23:03:36Z derpapst $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/QuickCategoryView.php');

class HoodPrepareCategoryView extends QuickCategoryView {
	
	protected $priceConfig = array();
	
	public function __construct($cPath = 0, $settings = array(), $sorting = false, $search = '', $productIDs = array()) {
		if ($search != '') {
			$this->blUseParent = true;
		}
		parent::__construct($cPath, $settings, $sorting, $search, $productIDs);
		//$this->action = array('action' => 'matching');
		if (!isset($_GET['kind']) || ($_GET['kind'] != 'ajax')) {
			$this->simplePrice->setCurrency(getCurrencyFromMarketplace($this->_magnasession['mpID']));
		}
		
		$this->priceConfig = HoodHelper::loadPriceSettings($this->_magnasession['mpID']);
	}

	protected function init() {
		parent::init();
		
		if (isset($_POST['action']) && ($_POST['action'] == 'uncheckSelection')) {
			MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array (
				'mpID' => $this->_magnasession['mpID'],
				'selectionname' => $this->settings['selectionName'],
				'session_id' => session_id(),
			));
		}
		
		$this->productIdFilterRegister('ManufacturerFilter', array());
	}

	public function getAdditionalHeadlines() {
		return '
			<td class="lowestprice">'.ML_HOOD_LABEL_HOOD_PRICE.'</td>
			<td class="matched">'.ML_HOOD_LABEL_PREPARED.'</td>';
	}
	
	protected function getProductsCountOfCategoryInfo($iId) {
		if (!isset($this->aCatInfo[$iId])) {
			$aOut = array(
				'iTotal' => 0,
				'iMatched' => 0,
				'iFailed' => 0
			);
			$aCatIds = $this->getAllSubCategoriesOfCategory($iId);
			$aCatIds[] = $iId;
			$sIdent = (getDBConfigValue('general.keytype', '0') == 'artNr') ? 'products_model' : 'products_id';
			$sSql = '
				SELECT DISTINCT p.'.$sIdent.'
				  FROM '.TABLE_PRODUCTS_TO_CATEGORIES.' p2c
				  LEFT JOIN '.TABLE_PRODUCTS.' p ON p2c.products_id=p.products_id
				 WHERE p2c.categories_id IN(' . implode(', ', $aCatIds) . ')
				'.($this->showOnlyActiveProducts
					? 'AND p.products_status<>0'
					: ''
				).'
				'.(getDBConfigValue('general.keytype', '0') == 'artNr'
					? " AND p.products_model!='' AND p.products_model IS NOT NULL"
					: ""
				).'
			';
			$aProducts = MagnaDB::gi()->fetchArray($sSql);
			$aProductIds = array();
			foreach ($aProducts as $aRow) {
				$aProductIds[$aRow[$sIdent]] = MagnaDB::gi()->escape($aRow[$sIdent]);
			}
			$aOut['iTotal'] = count($aProductIds);
			if (count($aProductIds)) {
				$sSql = "
				    SELECT DISTINCT COUNT(products_id) AS count, verified 
				      FROM ".TABLE_MAGNA_HOOD_PROPERTIES." 
				     WHERE ".$sIdent." in('".implode("', '",$aProductIds)."')
				           AND mpid='".$this->_magnasession['mpID']."'
				  GROUP BY verified
				";
				$aArray = MagnaDB::gi()->fetchArray($sSql);
				if(is_array($aArray)){
					foreach ( $aArray as $aInfo) {
						if ($aInfo['verified'] != 'OK') {
							$aOut['iFailed'] += $aInfo['count'];
						} else {
							$aOut['iMatched'] += $aInfo['count'];
						}
					}
				}
			}
			$this->aCatInfo[$iId] = $aOut;
		}
		return $this->aCatInfo[$iId];
	}
	
	public function getAdditionalCategoryInfo($cID, $data = false) {
		return '<td>&mdash;</td>'.parent::renderAdditionalCategoryInfo($cID);
	}

	public function getAdditionalProductInfo($pID, $data = false) {
		$a = MagnaDB::gi()->fetchRow('
			SELECT products_id, StartPrice, Verified, ListingType
			  FROM '.TABLE_MAGNA_HOOD_PROPERTIES.'
			 WHERE '.((getDBConfigValue('general.keytype', '0') == 'artNr')
						? 'products_model="'.MagnaDB::gi()->escape($data['products_model']).'"'
						: 'products_id="'.$pID.'"'
					).'
					AND mpID = '.$this->_magnasession['mpID'].'
		');
		
		if (empty($a)) {
			return '
				<td>&mdash;</td>
				<td>'.html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/grey_dot.png', ML_HOOD_PRODUCT_MATCHED_NO, 9, 9).'</td>';
		}
		
		$listingType = ($a['ListingType'] == 'classic') ? 'Auction' : 'Fixed';
		
		$textHoodPrice = array();
		if ($listingType == 'Auction') {
			if ((float)$a['StartPrice'] > 0) {
				$price = $this->simplePrice->setPrice($a['StartPrice'])->format();
				$textHoodPrice[] = ML_HOOD_LABEL_STARTPRICE.': '.$price;
			}
			if ($this->priceConfig['Auction']['BuyItNow']['UseBuyItNow']) {
				$price = $this->simplePrice
					->setFinalPriceFromDB($pID, $this->_magnasession['mpID'], $this->priceConfig['Auction']['BuyItNow'])
					->format();
				$textHoodPrice[] = ML_HOOD_BUYITNOW.': '.$price;
			}
		} else if ($listingType == 'Fixed') {
			$price = $this->simplePrice
				->setFinalPriceFromDB($pID, $this->_magnasession['mpID'], $this->priceConfig['Fixed'])
				->format();
			$textHoodPrice[] = ML_HOOD_BUYITNOW.': '.$price;
		}
		
		if (empty($textHoodPrice)) {
			$textHoodPrice = '&mdash;';
		} else {
			$textHoodPrice = implode('<br>', $textHoodPrice);
		}
		if ('OK' != $a['Verified']) {
			if ('EMPTY' == $a['Verified']) {
				return '
					<td title="'.ML_HOOD_PRICE_CALCULATED_TOOLTIP.'">'.$textHoodPrice.'</td>
					<td>'.html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/white_dot.png', ML_EBAY_PRODUCT_PREPARED_FAULTY_BUT_MP, 9, 9).'</td>';
			} else {
				return '
					<td title="'.ML_HOOD_PRICE_CALCULATED_TOOLTIP.'">'.$textHoodPrice.'</td>
					<td>'.html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/red_dot.png', ML_HOOD_PRODUCT_PREPARED_FAULTY, 9, 9).'</td>';
			}
		}
		return '
			<td title="'.ML_HOOD_PRICE_CALCULATED_TOOLTIP.'">'.$textHoodPrice.'</td>
			<td>'.html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/green_dot.png', ML_HOOD_PRODUCT_PREPARED_OK, 9, 9).'</td>';
	}
	
	public function getFunctionButtons() {
		$mmatch = true;

		return '
			<input type="hidden" value="'.$this->settings['selectionName'].'" name="selectionName"/>
			<input type="hidden" value="_" id="actionType"/>
			<table class="right"><tbody>
				<tr>
					<td id="match_settings" rowspan="2" class="textleft inputCell">
						<input id="match_all_rb" type="radio" name="match" value="all" '.($mmatch ? 'checked="checked"' : '').'/>
						<label for="match_all_rb">'.ML_LABEL_ALL.'</label><br />
						<input id="match_notmatched_rb" type="radio" name="match" value="notmatched" '.(!$mmatch ? 'checked="checked"' : '').'/>
						<label for="match_notmatched_rb">'.ML_HOOD_LABEL_ONLY_NOT_PREPARED.'</label>
					</td>
					<td class="texcenter inputCell">
						<table class="right"><tbody>
							<tr><td><input type="submit" class="fullWidth ml-button smallmargin" value="'.ML_HOOD_BUTTON_PREPARE.'" id="prepare" name="prepare"/></td></tr>
						</tbody></table>
					</td>
					<td>
						<div class="desc" id="desc_man_match" title="'.ML_LABEL_INFOS.'"><span>'.ML_HOOD_LABEL_PREPARE.'</span></div>
					</td>
				</tr>
			</tbody></table>
		';

	}

	public function getLeftButtons() {
		return '
			<input type="submit" class="ml-button" value="'.ML_HOOD_BUTTON_UNPREPARE.'" id="unprepare" name="unprepare"/><br/>
			<input type="submit" class="ml-button" value="'.ML_HOOD_BUTTON_RESET_DESCRIPTION.'" id="reset_description" name="reset_description"/>';
	}
	
	protected function renderDeletedArticlesSelector() {
		$html = '
			<form id="deletedArticlesSelection" name="deletedArticlesSelection" method="POST" action="'.toURL(
				array('mp' => $this->mpID), array('mode' => 'prepare')
			).'">
				<input type="hidden" name="timestamp" value="'.time().'"/>
				<select name="action">
					 <option value="">'.ML_LABEL_ACTION.'</option>
					 <option value="uncheckSelection">'.ML_LABEL_UNCHECK_SELECTION.'</option>
				</select>
				<script type="text/javascript">/*<![CDATA[*/
					$(document).ready(function() {
						$("form#deletedArticlesSelection").change(function() {
							jQuery.blockUI(blockUILoading);
							this.submit();
						});
					});
				/*]]>*/</script>
			</form>';
		return $html;
	}
	
	public function printForm() {
		$this->appendTopHTML('<div class="right">'.$this->renderDeletedArticlesSelector().'</div>');
		return parent::printForm();
	}
	
}
