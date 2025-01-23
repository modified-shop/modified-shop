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
 * $Id: $
 *
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/checkin/MagnaCompatibleSummaryView.php');
require_once(DIR_MAGNALISTER_MODULES.'cdiscount/CdiscountHelper.php');

class CdiscountSummaryView extends MagnaCompatibleSummaryView {
	#protected $conditionTypes = array();
	
	public function __construct($settings = array()) {
		parent::__construct($settings);
	}
	
	protected function additionalInitialisation() {
		parent::additionalInitialisation();
	}

	protected function setupQuery($addFields = '', $addFrom = '', $addWhere = '') {
		$addFields .= (empty($addFields) ? '' : ',').
							'hp.PrimaryCategory, hp.MarketplaceCategoriesName, hp.ConditionType, '.
							'hp.Comment
		              ';
		$addFrom   = 'LEFT JOIN '.TABLE_MAGNA_CDISCOUNT_PREPARE.' hp ON (
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
			if (isset($_POST['reset']) && isset($_POST['limit']) && is_array($_POST['limit'])) {
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

		}
		#if (!$this->isAjax) echo print_m($_POST, '$_POST');

	}

	protected function extendProductAttributes($pID, &$data) {
		parent::extendProductAttributes($pID, $data);
	}

	protected function getAdditionalHeadlines() {
		return parent::getAdditionalHeadlines().'<td>'.$this->provideResetFunction('Versanddauer', 'shippingtime').'</td>';
	}
	
	protected function getAdditionalItemCells($key, $dbRow) {
		$this->extendProductAttributes($dbRow['products_id'], $this->selection[$dbRow['products_id']]);
		return parent::getAdditionalItemCells($key, $dbRow);
	}
	
	public function renderSelection() {
		ob_start();
		$formatOptions = $this->simplePrice->getFormatOptions();
		$formatOptions = array('2', '.', '');
?>
<script type="text/javascript">/*<![CDATA[*/
var formatOptions = <?php echo json_encode($formatOptions); ?>;

/*]]>*/</script>
<?php
		$html = ob_get_contents();
		ob_end_clean();
		return parent::renderSelection().$html;
	}
}
