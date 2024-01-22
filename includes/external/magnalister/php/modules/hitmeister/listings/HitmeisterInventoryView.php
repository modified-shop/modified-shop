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

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/listings/MagnaCompatibleInventoryView.php');

class HitmeisterInventoryView extends MagnaCompatibleInventoryView {

	public function __construct($settings = array()) {
		global $_MagnaShopSession, $_MagnaSession, $_url, $_modules;

		$this->marketplace = $_MagnaSession['currentPlatform'];
		$this->mpID = $_MagnaSession['mpID'];

		$this->simplePrice = new SimplePrice();
		$this->mpCurrency = getCurrencyFromMarketplace($this->mpID);
		$this->simplePrice->setCurrency($this->mpCurrency);
		$this->url = $_url;
		$this->url['view'] = 'inventory';
		$this->magnasession = &$_MagnaSession;
		$this->magnaShopSession = &$_MagnaShopSession;

		if (isset($_GET['itemsPerPage'])) {
			$this->magnasession[$this->mpID]['InventoryView']['ItemLimit'] = (int) $_GET['itemsPerPage'];
		}
		if (!isset($this->magnasession[$this->mpID]['InventoryView']['ItemLimit']) || ($this->magnasession[$this->mpID]['InventoryView']['ItemLimit'] <= 0)
		) {
			$this->magnasession[$this->mpID]['InventoryView']['ItemLimit'] = 50;
		}

		if (array_key_exists('tfSearch', $_POST) && !empty($_POST['tfSearch'])) {
			$this->search = $_POST['tfSearch'];
		} else if (array_key_exists('search', $_GET) && !empty($_GET['search'])) {
			$this->search = $_GET['search'];
		}

		$this->settings = array_merge(array(
			'maxTitleChars'	=> 40,
			'itemLimit'	=> $this->magnasession[$this->mpID]['InventoryView']['ItemLimit'],
			'language'      => getDBConfigValue($this->marketplace.'.lang', $this->mpID, $_SESSION['languages_id']),
		), $settings);

		if (isset($_POST['refreshStock'])) {
			try {
				MagnaConnector::gi()->submitRequest(array(
					'ACTION' => 'ImportInventory',
				));

				setDBConfigValue($this->magnasession['currentPlatform'] . '.inventory.import', $this->mpID, time(), true);
			} catch (MagnaException $e) {
				return false;
			}
		}
	}

	public function renderInventoryTable() {
		$html = '';

		if (empty($this->renderableData)) {
			$this->prepareInventoryData();
		}

		$pages = ceil($this->numberofitems / $this->settings['itemLimit']);
		$tmpURL = $this->url;
		if (isset($_GET['sorting'])) {
			$tmpURL['sorting'] = $_GET['sorting'];
		}
		if (!empty($this->search)) {
			$tmpURL['search'] = urlencode($this->search);
		}
		$currentPage = 1;
		if (isset($_GET['page']) && ctype_digit($_GET['page']) && (1 <= (int)$_GET['page']) && ((int)$_GET['page'] <= $pages)) {
			$currentPage = (int)$_GET['page'];
		}

		$itemsPerPageSelect = array(50, 100, 250, 500, 1000, 2500);
		$chooser = '
				<select id="itemsPerPage" name="itemsPerPage" class="">' . "\n";
		foreach ($itemsPerPageSelect as $chc) {
			$chcselected = ($this->settings['itemLimit'] == $chc) ? 'selected' : '';
			$chooser .= '<option value="' . $chc . '" ' . $chcselected . '>' . $chc . '</option>';
		}
		$chooser .= '
				</select>';

		$offset = $currentPage * $this->settings['itemLimit'] - $this->settings['itemLimit'] + 1;
		$limit = $offset + count($this->renderableData) - 1;
		$html .= '<table class="listingInfo"><tbody><tr>
					<td class="ml-pagination">
						'.(($this->numberofitems > 0)
							?	('<span class="bold">'.ML_LABEL_PRODUCTS.':&nbsp; '.
								 $offset.' bis '.$limit.' von '.($this->numberofitems).'&nbsp;&nbsp;&nbsp;&nbsp;</span>'
								)
							:	''
						).'
						<span class="bold">'.ML_LABEL_CURRENT_PAGE.':&nbsp; '.$currentPage.'</span>
					</td>
					<td class="textright">
						'.renderPagination($currentPage, $pages, $tmpURL).'&nbsp;'.$chooser.'
					</td>
				</tr></tbody></table>';

		if (!empty($this->renderableData)) {
			$html .= $this->renderDataGrid('csinventory');
		} else {
			$html .= '<table class="magnaframe"><tbody><tr><td>'.
						(empty($this->search) ? ML_GENERIC_NO_INVENTORY : ML_LABEL_NO_SEARCH_RESULTS).
					 '</td></tr></tbody></table>';
		}

		ob_start();
?>
<script type="text/javascript">/*<![CDATA[*/
$(document).ready(function() {
	$('#selectAll').click(function() {
		state = $(this).attr('checked') !== undefined;
		$('#csinventory input[type="checkbox"]:not([disabled])').each(function() {
			$(this).attr('checked', state);
		});
	});
	$('#itemsPerPage').change(function() {
		window.location.href = '<?php echo toURL($tmpURL, true); ?>&itemsPerPage=' + $(this).val();
	});
});
/*]]>*/</script>
<?php
		$html .= ob_get_contents();	
		ob_end_clean();
		
		return $html;
	}
	
	public function renderActionBox() {
		global $_modules;

		$js = '';
		$left = (!empty($this->renderableData) ?
			'<input type="button" class="ml-button" value="'.ML_BUTTON_LABEL_DELETE.'" id="listingDelete" name="listing[delete]"/>' :
			''
		);
		$right = '<table class="right"><tbody>
			'.(in_array(getDBConfigValue($this->magnasession['currentPlatform'] . '.stocksync.tomarketplace', $this->mpID), array('abs', 'auto'))
				? '<tr><td><input type="submit" class="ml-button fullWidth smallmargin" name="refreshStock" value="'.ML_BUTTON_REFRESH_STOCK.'"/></td></tr>'
				: ''
			).'
		</tbody></table>';

		ob_start();?>
		<script type="text/javascript">/*<![CDATA[*/
			$(document).ready(function() {
				$('#listingDelete').click(function() {
					if (($('#csinventory input[type="checkbox"]:checked').length > 0) &&
						confirm(unescape(<?php echo "'".html2url(sprintf(ML_GENERIC_DELETE_LISTINGS, $_modules[$this->marketplace]['title']))."'"; ?>))
					) {
						$('#action').val('delete');
						$(this).parents('form').submit();
					}
				});
			});
			/*]]>*/</script>
		<?php // Durch aufrufen der Seite wird automatisch ein Aktualisierungsauftrag gestartet
		$js = ob_get_contents();
		ob_end_clean();

		if (($left == '') && ($right == '')) {
			return '';
		}
		return '
			<input type="hidden" id="action" name="action" value="">
			<input type="hidden" name="timestamp" value="'.time().'">
			<table class="actions">
				<thead><tr><th>'.ML_LABEL_ACTIONS.'</th></tr></thead>
				<tbody><tr><td>
					<table><tbody><tr>
						<td class="firstChild">'.$left.'</td>
						<td><label for="tfSearch">'.ML_LABEL_SEARCH.':</label>
							<input id="tfSearch" name="tfSearch" type="text" value="'.fixHTMLUTF8Entities($this->search, ENT_COMPAT).'"/>
							<input type="submit" class="ml-button" value="'.ML_BUTTON_LABEL_GO.'" name="search_go" /></td>
						<td class="lastChild">'.$right.'</td>
					</tr></tbody></table>
				</td></tr></tbody>
			</table>
			'.$js;
	}

	public function renderView() {
		$html = $this->renderLatestReport();
		$html .= '<form action="'.toUrl($this->url).'" id="csInventoryView" method="post">';
		$this->initInventoryView();
		$html .= $this->renderInventoryTable();
		return $html.$this->renderActionBox().'
			</form>
			<script type="text/javascript">/*<![CDATA[*/
				$(document).ready(function() {
					$(\'#csInventoryView\').submit(function () {
						jQuery.blockUI(blockUILoading);
					});
					$(\'#hitmeisterInfo\').click(function () {
						$(\'#infodiag\').jDialog();
					});
				});
			/*]]>*/</script>';
	}

	private function renderLatestReport() {
		$latestReport = getDBConfigValue($this->magnasession['currentPlatform'] . '.inventory.import', $this->mpID);

		return '<table class="magnaframe">
					<thead><tr><th>' . ML_LABEL_NOTE . '</th></tr></thead>
					<tbody><tr><td class="fullWidth">
						<table>
							<tbody>
							<tr><td>' . ML_HITMEISTER_LABEL_LAST_REPORT . '
									<div id="hitmeisterInfo" class="desc"></div>:
								</td>
								<td>' . (($latestReport > 0) ? date("d.m.Y &\b\u\l\l; H:i:s", $latestReport) : ML_LABEL_UNKNOWN) . '</td></tr>
							</tbody>
						</table>
					</td></tr></tbody>
				</table>
				<div id="infodiag" class="dialog2" title="' . ML_LABEL_NOTE . '">' . ML_HITMEISTER_TEXT_CHECKIN_DELAY. '</div>';
	}

	protected function getFields() {
		return array(
			'SKU' => array (
				'Label' => ML_LABEL_SKU,
				'Sorter' => 'sku',
				'Getter' => null,
				'Field' => 'SKU'
			),
			'Title' => array (
				'Label' => ML_LABEL_SHOP_TITLE,
				'Sorter' => null,
				'Getter' => 'getTitle',
				'Field' => null,
 			),
			'MarketplaceTitle' => array (
				'Label' => ML_HITMEISTER_LABEL_TITLE,
				'Sorter' => 'title',
				'Getter' => 'getMarketplaceTitle',
				'Field' => null,
			),
			'EAN' => array (
				'Label' => ML_LABEL_EAN,
				'Sorter' => 'ean',
				'Getter' => 'getEANLink',
				'Field' => null,
			),
 			'Price' => array (
 				'Label' => ML_HITMEISTER_PRICE,
 				'Sorter' => 'price',
 				'Getter' => 'getItemPrice',
 				'Field' => null
 			),
 			'Quantity' => array (
				'Label' => ML_STOCK_SHOP_STOCK_HITMEISTER,
				'Sorter' => 'quantity',
				'Getter' => 'getQuantities',
				'Field' => null,
			),
 			'DateAdded' => array (
 				'Label' => ML_GENERIC_CHECKINDATE,
 				'Sorter' => 'dateadded',
 				'Getter' => 'getItemDateAdded',
 				'Field' => null
 			),
			'Status' => array(
				'Label' => ML_HITMEISTER_INVENTORY_STATUS,
 				'Sorter' => 'status',
 				'Getter' => 'getStatus',
 				'Field' => null
			)
		);
	}

	protected function getEANLink($item) {
		if (empty($item['EAN'])) {
			return '<td>&mdash</td>';
		}
		$site = getDBConfigValue($this->magnasession['currentPlatform'] . '.site', $this->mpID, 'de');

		return '<td><a href="https://www.kaufland.'.$site.'/item/search/?search_value='.$item['EAN'].'" target="_blank">'.$item['EAN'].'</a></td>';
	}

	protected function getQuantities($item) {
		$shopQuantity = (int)MagnaDB::gi()->fetchOne("
			SELECT products_quantity
			  FROM ".TABLE_PRODUCTS."
			 WHERE products_id = '".magnaSKU2pID($item['SKU'])."'
		");

		if ($shopQuantity == 0) {
			$shopQuantity = '&mdash;';
		}

		return '<td>'.$shopQuantity.' / '.$item['Quantity'].'</td>';
	}
	
	protected function postDelete() {
		MagnaConnector::gi()->submitRequest(array(
			'ACTION' => 'UploadItems'
		));
	}

	protected function getStatus($item) {
		if (isset($item['Status']) === false) {
			$status = '-';
		} else if ($item['Status'] === 'Active') {
			$status = ML_HITMEISTER_INVENTORY_STATUS_ACTIVE;
		} else if ($item['Status'] === 'UpdateItem' || $item['Status'] === 'WaitingUpdateItem') {
			$status = ML_HITMEISTER_INVENTORY_STATUS_PENDING_UPDATE;
		} else {
			$status = ML_HITMEISTER_INVENTORY_STATUS_PENDING_NEW;
		}
		
		return '<td>' . $status . '</td>';
	}

}
