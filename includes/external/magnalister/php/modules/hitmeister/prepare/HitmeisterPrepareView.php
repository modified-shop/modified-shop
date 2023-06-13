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
 * $Id: checkin.php 1174 2011-07-30 17:49:04Z derpapst $
 *
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'hitmeister/catmatch/HitmeisterCategoryMatching.php');
require_once(DIR_MAGNALISTER_MODULES.'hitmeister/HitmeisterHelper.php');

class HitmeisterPrepareView extends MagnaCompatibleBase {
	
	protected $catMatch = null;
	protected $prepareSettings = array();
	
	protected function initCatMatching() {
		$params = array();
		foreach (array('mpID', 'marketplace', 'marketplaceName', 'prepareSettings') as $attr) {
			if (isset($this->$attr)) {
				$params[$attr] = &$this->$attr;
			}
		}
		
		$this->catMatch = new HitmeisterCategoryMatching($params);
	}

	protected function showItemName() {
		$itemName = MagnaDB::gi()->fetchArray(eecho('
			SELECT pd.products_name
			 FROM  '.TABLE_MAGNA_SELECTION.' ms, '.TABLE_PRODUCTS_DESCRIPTION.' pd
			 WHERE ms.mpID=\''.$this->mpID.'\'
			  AND ms.selectionname = \'prepare\'
			  AND pd.language_id = \''.getDBConfigValue('hitmeister.lang', $this->mpID).'\'
			  AND ms.pID = pd.products_id
		', false));
		if (1 != MagnaDB::gi()->numRows()) {
			return '';
		}
		return '
			<tr class="odd">
				<th>'.ML_COMPARISON_SHOPPING_FIELD_ITEM_TITLE.'</th>
				<td>
					'.$itemName[0]['products_name'].'
				</td>
				<td class="info">&nbsp;</td>
			</tr>
		';
		
	}
	
	public function process() {
		$this->initCatMatching();
		
		$html = '
			<form method="post" action="'.toURL($this->resources['url']).'">
				'.$this->catMatch->renderMatching();

		$shippingTimes         = HitmeisterHelper::GetShippingTimes();
		$defaultShippingTime   = getDBConfigValue('hitmeister.shippingtime', $this->mpID);
		$defaultHandlingTime   = getDBConfigValue('hitmeister.handlingtime', $this->mpID);
		if (getDBConfigValue(array('hitmeister.shippingtimematching.prefer', 'val'), $this->mpID, false)) {
			$shippingTimes['m']  = ML_HITMEISTER_USE_SHIPPINGTIME_MATCHING;
			$defaultShippingTime = 'm';
		}
		$conditions            = HitmeisterHelper::GetConditionTypes();
		$defaultCondition      = getDBConfigValue('hitmeister.itemcondition', $this->mpID);
		$useproductsfsk18      = getDBConfigValue('hitmeister.useproductsfsk18', $this->mpID);
		$defaultMpCategory     = '0';
		$defaultMpCategoryName = '';
		$defaultAgeRating      = '0';
		$showPornCheckbox      = 'checkbox' == getDBConfigValue('hitmeister.pornsetting', $this->mpID, 'checkbox');
		$defaultIsPorn         = '0';
		$defaultComment        = '';

		# Hitmeister-Kat., Art-Name, Zustand, Lieferzeit, Altersbeschr., Porno, Kommentar
		$preselectPreparedValues = MagnaDB::gi()->fetchArray(eecho('
			SELECT mp_category_id, mp_category_name, condition_id, shippingtime, is_porn, age_rating, comment
			 FROM '.TABLE_MAGNA_HITMEISTER_PREPARE.' hp, '.TABLE_MAGNA_SELECTION.' ms
			 WHERE ms.mpID=\''.$this->mpID.'\'
			  AND ms.selectionname = \'prepare\'
			  AND ms.pID = hp.products_id
			  AND ms.mpID = hp.mpID
		', true));
		$numberOfPreparedValues = MagnaDB::gi()->numRows();
		$preselectShopValues = MagnaDB::gi()->fetchArray(eecho('
			SELECT p.products_fsk18, p.products_shippingtime
			 FROM  '.TABLE_MAGNA_SELECTION.' ms, '.TABLE_PRODUCTS.' p
			 WHERE ms.mpID=\''.$this->mpID.'\'
			  AND ms.selectionname = \'prepare\'
			  AND ms.pID = p.products_id
		', true));
		$numberOfItems = MagnaDB::gi()->numRows();
		if (1 == $numberOfPreparedValues) {
			$defaultMpCategory     = $preselectPreparedValues[0]['mp_category_id'];
			$defaultMpCategoryName = $this->catMatch->getMPCategory($defaultMpCategory);
			if (is_array($defaultMpCategoryName)) {
				$defaultMpCategoryName = fixHTMLUTF8Entities($defaultMpCategoryName['CategoryName']);
			}
			$defaultCondition      = $preselectPreparedValues[0]['condition_id'];
			$defaultShippingTime   = $preselectPreparedValues[0]['shippingtime'];
			$defaultHandlingTime   = $preselectPreparedValues[0]['handlingtime'];
			$defaultAgeRating      = $preselectPreparedValues[0]['age_rating'];
			$defaultIsPorn         = $preselectPreparedValues[0]['is_porn'];
			$defaultComment        = $preselectPreparedValues[0]['comment'];
			ob_start();
			?>
			<script type="text/javascript">/*<![CDATA[*/
			(function ($) {
				$(document).ready(function () {
					$('#mpCategory').val('<?php echo $defaultMpCategory; ?>');
					$('#mpCategoryName').val('<?php echo $defaultMpCategoryName; ?>');
					$('#mpCategoryVisual').html('<?php echo $this->catMatch->getMPCategoryPath($defaultMpCategory); ?>');
				});
			}(jQuery))
			/*]]>*/</script>
			<?php
			$html .= ob_get_contents();
			ob_end_clean();
		}
		if (1 == $numberOfItems) {
			# single item
			if ((1 != $numberOfPreparedValues) && $useproductsfsk18) {
				$defaultAgeRating = (isset($preselectShopValues[0]['fsk18']) && ('1' == $preselectShopValues[0]['fsk18'])) ? '18' : '0';
			}
			# 'Lieferzeit Matching bevorzugen'? Wenn Einzel-Art., nehme direkt vom Artikel
			if ('m' == $defaultShippingTime) {
				$products_shippingtime = $preselectShopValues[0]['products_shippingtime'];
				$shippingtimeMatching = getDBConfigValue('hitmeister.shippingtimematching.values', $this->mpID, array());
				if (array_key_exists($products_shippingtime, $shippingtimeMatching)) {
					$defaultShippingTime = $shippingtimeMatching["$products_shippingtime"];
					unset($shippingTimes['m']);
				}
			}
		}
		# multiple items: no pre-filling except default values

		$html .= '
			<table class="attributesTable"><tbody>'.$this->showItemName().'
				<tr class="odd">
					<th>'.ML_HITMEISTER_CONDITION.'</th>
					<td class="input">
					<select name="condition_id" id="condition_id">';
		foreach ($conditions as $condID => $condName) {
			if ($condID == $defaultCondition) {
				$html .= '
					<option selected value="'.$condID.'">'.$condName.'</option>';
			} else {
				$html .= '
					<option value="'.$condID.'">'.$condName.'</option>';
			}
		}
		$html .= '
					</select>
					</td>
					<td class="info">&nbsp;</td>
				</tr>
				<tr class="even">
					<th>'.ML_HITMEISTER_SHIPPINGTIME.'</th>
					<td class="input">
					<select name="shippingtime" id="shippingtime">';
		foreach ($shippingTimes as $shipTimeID => $shipTimeName) {
			if ($shipTimeID == $defaultShippingTime) {
				$html .= '
					<option selected value="'.$shipTimeID.'">'.$shipTimeName.'</option>';
			} else {
				$html .= '
					<option value="'.$shipTimeID.'">'.$shipTimeName.'</option>';
			}
		}
		
		$html .= '
					</select>
					</td>
					<td class="info">&nbsp;</td>
				</tr>
				<tr class="odd">
					<th>'.ML_HITMEISTER_AGE_RATING.'</th>
					<td class="input">
						<select name="age_rating">';
		for ($i = 0; $i <= 21; ++$i) {
			if ($i == $defaultAgeRating) {
			$html .= '
					<option selected value="'.$i.'">'.$i.'</option>';
			} else {
				$html .= '
					<option value="'.$i.'">'.$i.'</option>';
			}
		}
		$html .= '
						</select>
					</td>
					<td class="info">&nbsp;</td>
				</tr>';
		if ($showPornCheckbox) {
			$html .= '
				<tr class="even">
					<th>'.ML_HITMEISTER_IS_PORN.'</th>
					<td class="input">
						<input type="checkbox" name="is_porn" value="1" '.(('1' == $defaultIsPorn) ? ' checked="checked" ' : '').'>
					</td>
					<td class="info">&nbsp;</td>
				</tr>';
		}
		$html .= '
				<tr class="odd">
					<th>'.ML_HITMEISTER_COMMENT.'</th>
					<td class="input">
						<textarea name="comment">'.$defaultComment.'</textarea>
					</td>
					<td class="info">&nbsp;</td>
				</tr>
				<tr class="spacer">
					<td colspan="3">&nbsp;</td>
				</tr>
			</tbody></table>
			<table class="actions">
				<thead><tr><th>'.ML_LABEL_ACTIONS.'</th></tr></thead>
				<tbody>
					<tr><td>
						<table><tbody>
							<tr><td>
								<input type="submit" class="ml-button mlbtn-action" name="saveMatching" value="'.ML_BUTTON_LABEL_SAVE_DATA.'"/>
							</td></tr>
						</tbody></table>
					</td></tr>
				</tbody>
			</table>';
			
		$html .= '
			</form>';
		
		return $html;
	}
	
	public function renderAjax() {
		$this->initCatMatching();
		
		return $this->catMatch->renderAjax();
	}
}
