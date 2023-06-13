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
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimpleSummaryView.php');

class ComparisonShoppingSummaryView extends SimpleSummaryView {
	private $shippingClass;
	
	public function __construct($settings = array()) {
		global $_MagnaSession, $_modules, $_shippingClass;
		
		$this->shippingClass = $_shippingClass;
		
		$settings = array_merge(array(
			'selectionName'   => 'checkin',
			'currency'        => getCurrencyFromMarketplace($_MagnaSession['mpID']),
		), $settings);
	
		parent::__construct($settings);
		
		if (!isset($_GET['kind']) || ($_GET['kind'] != 'ajax')) {
			$this->setupQuery(
				((SHOPSYSTEM == 'gambio') ? 'p.nc_ultra_shipping_costs, ' : '').'p.products_weight'
			);
		}
	}
	
	protected function processAdditionalPost() {
		if (isset($_GET['kind']) && ($_GET['kind'] == 'ajax')) {
			if (!isset($_POST['productID'])) {
				return;
			}
			$pID = $this->ajaxReply['pID'] = substr($_POST['productID'], strpos($_POST['productID'], '_') + 1);

			if (!array_key_exists($pID, $this->selection)) {
				$this->loadItemToSelection($pID);
			}
			$this->extendProductAttributes($pID, $this->selection[$pID]);

			if (isset($_POST['changeShippingcost'])) {
				$_POST['shippingcost'][$pID] = $_POST['changeShippingcost'];
			}
		}

		if (array_key_exists('shippingcost', $_POST)) {
			$format = $this->simplePrice->getFormatOptions();
			foreach ($_POST['shippingcost'] as $pID => $price) {
				$price = $_POST['shippingcost'][$pID];
				if (($price == (string)(float)$price)) {
					$price = (float)$price;
				} else {
					$price = priceToFloat($_POST['shippingcost'][$pID], $format);
				}
				if ($price > 0) {
					$this->selection[$pID]['shippingcost'] = $this->ajaxReply['value'] = $price;
				}
			}
			#echo print_m($this->selection, '$this->selection ('.__METHOD__.'['.__LINE__.']'.')');
		}
		
	}

	protected function extendProductAttributes($pID, &$data) {
		if (!isset($data['shippingcost']) || ($data['shippingcost'] == null)) {
			$dbRow = MagnaDB::gi()->fetchRow('
				SELECT p.products_id, '.((SHOPSYSTEM == 'gambio') ? 'p.nc_ultra_shipping_costs, ' : '').'p.products_weight
				  FROM '.TABLE_PRODUCTS.' p
				 WHERE p.products_id=\''.$pID.'\'
				 LIMIT 1
			');
			if ($dbRow) {
				$shippingPrice = (float)$this->shippingClass->getShippingCost(
					$dbRow['products_weight'], (
						isset($dbRow['nc_ultra_shipping_costs']) ? $dbRow['nc_ultra_shipping_costs'] : false //gambio
					)
				);
				$data['shippingcost'] = $this->simplePrice->setPrice($shippingPrice);
				
				if (getDBConfigValue($this->marketplace.'.shipping.method', $this->_magnasession['mpID'], '') != '__ml_lump') {
					$this->simplePrice->calculateCurr();
				}
				$data['shippingcost'] = $this->simplePrice->getPrice();
			} else {
				$data['shippingcost'] = 0.0;
			}
		}
	}

	protected function getAdditionalItemCells($key, $dbRow) {
		$this->extendProductAttributes($dbRow['products_id'], $this->selection[$dbRow['products_id']]);
		$this->simplePrice->setPrice($this->selection[$dbRow['products_id']]['shippingcost']);
		return '';
	}

	public function renderSelection() {
		ob_start();
		$formatOptions = $this->simplePrice->getFormatOptions();
		$formatOptions = array('2', '.', '');
		#echo print_m($formatOptions);
?>
<script type="text/javascript">/*<![CDATA[*/
var formatShippingcostOptions = <?php echo json_encode($formatOptions); ?>;

$(document).ready(function() {
	$('#summaryForm input[name^="shippingcost"]').each(function(i, e) {
		//myConsole.log($(e).attr('id'));
		$(e).blur(function() {
			val = jQuery.trim($(e).val());
			tfID = $(e).attr('id');
			price = convertPriceToFloat(val, formatShippingcostOptions);
			myConsole.log(price);
			if (price < 0) {
				alert(unescape(<?php echo "'".html2url(ML_ERROR_INVALID_NUMBER)."'"; ?>));
				$(e).val(formatPriceWoCur($('#backup_'+tfID).val(), formatShippingcostOptions));
			} else {
				jQuery.ajax({
					type: 'POST',
					url: '<?php echo toURL($this->url, array('kind' => 'ajax'), true); ?>',
					dataType: 'json',
					data: {
						'changeShippingcost':price,
						'productID':tfID,
					},
					success: function(data) {
						//myConsole.log(data);
						$('#backup_'+tfID).val(data.value);
						$(e).val(formatPriceWoCur(data.value, formatShippingcostOptions));
					}
				});
			}
			//myConsole.log($(this).attr('id')+': '+val);
		}).keypress(function(event) {
			if (event.keyCode == '13') {
				/* Bei ENTER nicht Form absenden, aber ajax request bei onBlur ausfuehren */
				event.preventDefault();
				$(e).blur();
			}
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
