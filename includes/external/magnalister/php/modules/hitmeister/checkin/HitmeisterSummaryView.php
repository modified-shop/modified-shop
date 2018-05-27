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
 * $Id: MeinpaketSummaryView.php 1018 2011-04-29 11:20:46Z derpapst $
 *
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/checkin/MagnaCompatibleSummaryView.php');
require_once(DIR_MAGNALISTER_MODULES.'hitmeister/HitmeisterHelper.php');

class HitmeisterSummaryView extends MagnaCompatibleSummaryView {
	#protected $conditionTypes = array();
	protected $shippingTimes = array();
	
	protected $useShippingtimeMatching = false;
	protected $defaultShippingtime = '';
	protected $shippingtimeMatching = array();
	
	public function __construct($settings = array()) {
		parent::__construct($settings);
	}
	
	protected function initShippingtimeConfig() {
		$this->shippingTimes = HitmeisterHelper::GetShippingTimes();
		
		$this->defaultShippingtime  = getDBConfigValue($this->marketplace.'.shippingtime', $this->mpID, 0); 
		$this->shippingtimeMatching = getDBConfigValue($this->marketplace.'.shippingtimematching.values', $this->mpID, array()); 
		$this->useShippingtimeMatching = getDBConfigValue(array($this->marketplace.'.shippingtimematching.prefer', 'val'), $this->mpID, false); 
		
		if (!is_array($this->shippingtimeMatching) || empty($this->shippingtimeMatching)) {
			$this->useShippingtimeMatching = false;
		}
		/*
		echo print_m($this->defaultShippingtime, '$this->defaultShippingtime');
		echo print_m($this->shippingtimeMatching, '$this->shippingtimeMatching');
		echo print_m($this->useShippingtimeMatching, '$this->useShippingtimeMatching');
		//*/
	}
	
	protected function additionalInitialisation() {
		parent::additionalInitialisation();
		$this->initShippingtimeConfig();
	}

	protected function setupQuery($addFields = '', $addFrom = '', $addWhere = '') {
		$addFields .= (empty($addFields) ? '' : ',').
		(MagnaDB::gi()->columnExistsInTable('products_shippingtime', TABLE_PRODUCTS)
		? ' p.products_shippingtime, '
		: '\' '.getDBConfigValue($this->marketplace.'.shippingtime', $this->mpID, 0).'\' AS `p.products_shippingtime`, ').
							'hp.MarketplaceCategories, hp.MarketplaceCategoriesName, hp.ConditionType, hp.ShippingTime, '.
							'hp.Comment
		              ';
		$addFrom   = 'LEFT JOIN '.TABLE_MAGNA_HITMEISTER_PREPARE.' hp ON (
							hp.mpID=\''.$this->mpID.'\' 
							AND hp.products_id=p.products_id
					  )
                      '.$addFrom;
		parent::setupQuery($addFields, $addFrom, $addWhere);
	}

	protected function resetShippingtime() {
		// Get all settings and verify them.
		$this->initShippingtimeConfig();
		
		if ($this->useShippingtimeMatching) {
			/* Check matching and fix it if necessary. */
			$matchedShippingIDs = array_keys($this->shippingtimeMatching);
			$availableShippingIDs = MagnaDB::gi()->fetchArray('
				SELECT DISTINCT shipping_status_id
				  FROM '.TABLE_SHIPPING_STATUS.'
			', true);
			$removedIDs = array_diff($matchedShippingIDs, $availableShippingIDs);
			if (!empty($removedIDs)) {
				foreach ($removedIDs as $id) {
					unset($this->shippingtimeMatching[$id]);
				}
			}
			$addedIDs = array_diff($availableShippingIDs, $matchedShippingIDs);
			if (!empty($addedIDs)) {
				foreach ($addedIDs as $id) {
					$this->shippingtimeMatching[$id] = $this->defaultShippingtime;
				}
			}
			if (!empty($removedIDs) || !empty($addedIDs)) {
				/* Save the updated matching */
				setDBConfigValue($this->marketplace.'.shippingtimematching.values', $this->mpID, $this->shippingtimeMatching, true);
			}

			$shippingtimeTemplate = '
				UPDATE '.TABLE_MAGNA_HITMEISTER_PREPARE.' AS a 
		    INNER JOIN '.TABLE_PRODUCTS.' AS p ON ('.(
			    (getDBConfigValue('general.keytype', '0') == 'artNr')
			    	? 'a.products_model = p.products_model'
			    	: 'a.products_id = p.products_id'
			    ).')
	               SET a.ShippingTime = \'_#_VALUE_#_\'
	             WHERE p.products_shippingtime = \'_#_TIME_#_\'
			';

			foreach ($availableShippingIDs as $id) {
				$shippingtime = $this->shippingtimeMatching[$id];
				MagnaDB::gi()->query(eecho(str_replace(array (
						'_#_VALUE_#_',
						'_#_TIME_#_'
					), array (
						$shippingtime,
						$id
					),
					$shippingtimeTemplate
				), true));
			}

		} else {
			MagnaDB::gi()->update(TABLE_MAGNA_HITMEISTER_PREPARE, array (
				'ShippingTime' => $this->defaultShippingtime
			));
		}
		
		$this->ajaxReply['changedData'] = array();
		
		$this->loadSelection();

		$changedData = MagnaDB::gi()->fetchArray(eecho('
		    SELECT p.products_id AS pID,
		           hp.ShippingTime, p.products_shippingtime
		      FROM '.TABLE_PRODUCTS.' p
		 LEFT JOIN '.TABLE_MAGNA_HITMEISTER_PREPARE.' hp ON (
		               hp.mpID=\''.$this->mpID.'\' 
		               AND hp.products_id=p.products_id
			       )
		     WHERE p.products_id IN (\''.implode('\', \'', array_keys($this->selection)).'\')
		', false));
		if (!empty($changedData)) {
			foreach ($changedData as $row) {
				// echo print_m($row);
				$this->ajaxReply['changedData'][$row['pID']]['shippingtime'] = empty($row['shippingtime'])
					? (($this->useShippingtimeMatching)
						? $this->shippingtimeMatching[$row['products_shippingtime']]
						: $this->defaultShippingtime
					) 
					: $row['shippingtime'];
			}
		}
		
		$this->ajaxReply['proceed'] = false;
		$this->ajaxReply['error'] = false;
		$this->ajaxReply['limit'] = array(0, 0);
	}

	protected function processAdditionalPost() {
		parent::processAdditionalPost();
		if ($this->isAjax) {
			if (isset($_POST['reset']) && ($_POST['reset'] == 'shippingtime') && isset($_POST['limit']) && is_array($_POST['limit'])) {
				$this->resetShippingtime();
				unset($_POST['reset']);
				unset($_POST['limit']);
			}
			if (!isset($_POST['productID'])) {
				return;
			}
			$pID = $this->ajaxReply['pID'] = substr($_POST['productID'], strpos($_POST['productID'], '_') + 1);
			if (!array_key_exists($pID, $this->selection)) {
				$this->loadItemToSelection($pID);
			}
			$this->extendProductAttributes($pID, $this->selection[$pID]);
			
			if (isset($_POST['changeShippingtime'])) {
				$_POST['shippingtime'][$pID] = $_POST['changeShippingtime'];
			}
		}
		#if (!$this->isAjax) echo print_m($_POST, '$_POST');

		if (array_key_exists('shippingtime', $_POST)) {
			if (getDBConfigValue('general.keytype', '0') == 'artNr') {
				$shippingtimeSQL = '
				    UPDATE '.TABLE_MAGNA_HITMEISTER_PREPARE.' AS a 
				INNER JOIN '.TABLE_PRODUCTS.' AS p ON (a.products_model = p.products_model)
				       SET a.ShippingTime = \'_#_VALUE_#_\'
				     WHERE p.products_id = \'_#_ID_#_\'
				';
			} else {
				$shippingtimeSQL = '
					UPDATE '.TABLE_MAGNA_HITMEISTER_PREPARE.'
					   SET ShippingTime = \'_#_VALUE_#_\'
					 WHERE products_id = \'_#_ID_#_\' 
				';
			}
			foreach ($_POST['shippingtime'] as $pID => $shippingtime) {
				/* Save value at selection */
				$this->selection[$pID]['shippingtime'] = $this->ajaxReply['value'] = $shippingtime;
				
				/* Save value for prepared items */
				MagnaDB::gi()->query(eecho(str_replace(array (
						'_#_VALUE_#_',
						'_#_ID_#_'
					), array (
						$shippingtime,
						$pID
					),
					$shippingtimeSQL
				), false));
			}
		}
	}

	protected function extendProductAttributes($pID, &$data) {
		parent::extendProductAttributes($pID, $data);
	}

	protected function getAdditionalHeadlines() {
		return parent::getAdditionalHeadlines().'<td>'.$this->provideResetFunction('Versanddauer', 'shippingtime').'</td>';
	}
	
	protected function getAdditionalItemCells($key, $dbRow) {
		$this->extendProductAttributes($dbRow['products_id'], $this->selection[$dbRow['products_id']]);
		if (empty($dbRow['shippingtime'])) {
			$dbRow['shippingtime'] = (($this->useShippingtimeMatching)
				? $this->shippingtimeMatching[$dbRow['products_shippingtime']]
				: $this->defaultShippingtime
			);
		}
		$html = '
			<td>
				<select id="shippingtime_'.$dbRow['products_id'].'" name="shippingtime['.$dbRow['products_id'].']" class="ml-js-noBlockUi">';
				foreach ($this->shippingTimes as $vk => $vv) {
					$html .= '    <option value="'.$vk.'"'.(($vk == $dbRow['shippingtime']) ? 'selected="selected"' : '').'>'.$vv.'</option>'."\n";
				}
				$html .= '
				</select>
			</td>';

		return parent::getAdditionalItemCells($key, $dbRow).$html;
	}
	
	public function renderSelection() {
		ob_start();
		$formatOptions = $this->simplePrice->getFormatOptions();
		$formatOptions = array('2', '.', '');
?>
<script type="text/javascript">/*<![CDATA[*/
var formatOptions = <?php echo json_encode($formatOptions); ?>;

$(document).ready(function() {
	$('#summaryForm select[name^="shippingtime"]').each(function(i, e) {
		//myConsole.log($(e).attr('id'));
		$(e).change(function() {
			jQuery.ajax({
				type: 'POST',
				url: '<?php echo toURL($this->url, array('kind' => 'ajax'), true); ?>',
				dataType: 'json',
				data: {
					'changeShippingtime': $(this).val(),
					'productID': $(this).attr('id')
				},
				dataType: 'json'
			});
		});
	});
});
/*]]>*/</script>
<?php
		$html = ob_get_contents();
		ob_end_clean();
		return parent::renderSelection().$html;
	}
}
