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
 * $Id: HitmeisterSummaryView.php 1018 2011-04-29 11:20:46Z derpapst $
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
	protected $handlingtimeMatching = array();
	
	public function __construct($settings = array()) {
		parent::__construct($settings);
	}
	
	protected function initShippingtimeConfig() {
		$this->shippingTimes = HitmeisterHelper::GetShippingTimes();
		
		$this->defaultShippingtime  = getDBConfigValue($this->marketplace.'.shippingtime', $this->mpID, 0); 
		$this->shippingtimeMatching = getDBConfigValue($this->marketplace.'.shippingtimematching.values', $this->mpID, array()); 
		$this->handlingtimeMatching = getDBConfigValue($this->marketplace.'.handlingtimematching.values', $this->mpID, array()); 
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
							'hp.MarketplaceCategories, hp.MarketplaceCategoriesName, hp.ConditionType, hp.ShippingTime, hp.HandlingTime, '.
							'hp.Comment
		              ';
		$addFrom   = 'LEFT JOIN '.TABLE_MAGNA_HITMEISTER_PREPARE.' hp ON (
							hp.mpID=\''.$this->mpID.'\' 
							AND '.
			('artNr' == getDBConfigValue('general.keytype', '0')
			 ? 'hp.products_model=p.products_model'
			 : 'hp.products_id=p.products_id'
			).'
					  )
                      '.$addFrom;
		parent::setupQuery($addFields, $addFrom, $addWhere);
	}

	protected function processAdditionalPost() {
		parent::processAdditionalPost();
		if ($this->isAjax) {
			if (!isset($_POST['productID'])) {
				return;
			}
			$pID = $this->ajaxReply['pID'] = substr($_POST['productID'], strpos($_POST['productID'], '_') + 1);
			if (!array_key_exists($pID, $this->selection)) {
				$this->loadItemToSelection($pID);
			}
			$this->extendProductAttributes($pID, $this->selection[$pID]);
		}
		#if (!$this->isAjax) echo print_m($_POST, '$_POST');

	}

	protected function extendProductAttributes($pID, &$data) {
		parent::extendProductAttributes($pID, $data);
	}

	protected function getAdditionalHeadlines() {
		return parent::getAdditionalHeadlines().'<td>'.ML_AMAZON_LABEL_LEADTIME_TO_SHIP.'</td>';
	}
	
	protected function getAdditionalItemCells($key, $dbRow) {
		$this->extendProductAttributes($dbRow['products_id'], $this->selection[$dbRow['products_id']]);
		$shippingTime = 
			($this->useShippingtimeMatching || $dbRow['ShippingTime'] === 'm') && array_key_exists($dbRow['products_shippingtime'], $this->shippingtimeMatching)
			? $this->shippingtimeMatching[$dbRow['products_shippingtime']]
			: $dbRow['ShippingTime']
		;
		if (!array_key_exists($shippingTime, $this->shippingTimes)) {
			$shippingTime = $this->defaultShippingtime;
		}
		$handlingTime = 
			($this->useShippingtimeMatching || $dbRow['ShippingTime'] === 'm') && array_key_exists($dbRow['products_shippingtime'], $this->handlingtimeMatching)
			? $this->handlingtimeMatching[$dbRow['products_shippingtime']]
			: $dbRow['HandlingTime']
		;
		$html .= '<td>'.$handlingTime.'</td>';

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
