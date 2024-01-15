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
 * (c) 2010 - 2019 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/listings/MagnaCompatibleInventoryView.php');

class RicardoInventoryView extends MagnaCompatibleInventoryView {
	public function __construct($settings = array()) {
		global $_MagnaShopSession, $_MagnaSession, $_url, $_modules;

		$this->marketplace = $_MagnaSession['currentPlatform'];
		$this->mpID = $_MagnaSession['mpID'];

		$this->settings = array_merge(array(
			'maxTitleChars'	=> 40,
			'itemLimit'		=> 50,
			'language'		=> getDBConfigValue($this->marketplace.'.lang', $this->mpID, false),
		), $settings);

		if ($this->settings['language'] === false) {
			$this->settings['language'] = mlLanguageIDFromCode($_SESSION['magna']['selected_language']);
		}

		$this->simplePrice = new SimplePrice();
		$this->mpCurrency = getCurrencyFromMarketplace($this->mpID);
		$this->simplePrice->setCurrency($this->mpCurrency);
		$this->url = $_url;
		$this->url['view'] = 'inventory';
		$this->magnasession = &$_MagnaSession;
		$this->magnaShopSession = &$_MagnaShopSession;

		if (array_key_exists('tfSearch', $_POST) && !empty($_POST['tfSearch'])) {
			$this->search = $_POST['tfSearch'];
		} else if (array_key_exists('search', $_GET) && !empty($_GET['search'])) {
			$this->search = $_GET['search'];
		}

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

	public function renderActionBox() {
		global $_modules;

		$js = '';
		$left = (!empty($this->renderableData) ?
			'<input type="button" class="ml-button" value="'.ML_BUTTON_LABEL_DELETE.'" id="listingDelete" name="listing[delete]"/>' :
			''
		);
		$right = '<table class="right"><tbody>
			'.(in_array(getDBConfigValue($this->magnasession['currentPlatform'] . '.stocksync.tomarketplace', $this->mpID), array('abs', 'auto', 'auto_reduce'))
				? '<tr><td><input type="submit" class="ml-button fullWidth smallmargin" name="refreshStock" value="'.ML_BUTTON_REFRESH_STOCK.'"/></td></tr>'
				: ''
			).'
		</tbody></table>';

		ob_start();?>
<script type="text/javascript">/*<![CDATA[*/
$(document).ready(function() {
	$('#listingDelete').click(function() {
		var me = this;
		if (($('#csinventory input[type="checkbox"]:checked').length > 0) &&
			confirm(unescape(<?php echo "'".html2url(sprintf(ML_GENERIC_DELETE_LISTINGS, $_modules[$this->marketplace]['title']))."'"; ?>))
		) {
			var d = $('#afterdelete').html();
			$('#infodiag').html(d).jDialog(
				{'width': (d.length > 1000) ? '700px' : '500px'},
				function() {
					$('#action').val('delete');
					$(me).parents('form').submit();
				})
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
			<div id="infodiag" class="dialog2" title="' . ML_LABEL_INFORMATION . '"></div>
			<span id="afterdelete" style="display: none">' . ML_RICARDO_AFTER_DELETE . '</span>
			'.$js;
	}

	protected function prepareInventoryItemData(&$item) {
		$item['MarketplaceTitle'] = $item['Title'];
		$item['MarketplaceTitleShort'] = (mb_strlen($item['MarketplaceTitle'], 'UTF-8') > $this->settings['maxTitleChars'] + 2)
			? (fixHTMLUTF8Entities(mb_substr($item['MarketplaceTitle'], 0, $this->settings['maxTitleChars'], 'UTF-8')).'&hellip;')
			: fixHTMLUTF8Entities($item['MarketplaceTitle']);
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
				'Getter' => 'getTitle',
				'Field' => null,
			),
			'MarketplaceTitle' => array (
				'Label' => ML_RICARDO_LABEL_TEXT.' '.ML_LABEL_TITLE,
				'Sorter' => 'title',
				'Getter' => 'getMarketplaceTitle',
				'Field' => null,
			),
			'ItemId' => array(
				'Label' => ML_RICARDO_LABEL_ITEM_ID,
				'Sorter' => 'ItemId',
				'Getter' => 'getItemId',
				'Field' => null
			),
			'Price' => array (
				'Label' => ML_GENERIC_PRICE,
				'Sorter' => 'price',
				'Getter' => 'getItemPrice',
				'Field' => null
			),
			'Quantity' => array (
				'Label' => ML_LABEL_QUANTITY,
				'Sorter' => 'quantity',
				'Getter' => 'getQuantities',
				'Field' => null,
			),
			'BidCount' => array(
				'Label' => ML_RICARDO_LABEL_BID_COUNT,
				'Sorter' => null,
				'Getter' => 'getBidCount',
				'Field' => null,
			),
			'LastModified' => array (
				'Label' => ML_LAST_SYNC,
				'Sorter' => 'lastmodified',
				'Getter' => 'getLastModified',
				'Field' => null
			),
			'StartEndDate' => array (
				'Label' => ML_GENERIC_LABEL_LISTINGTIME,
				'Sorter' => 'startenddate',
				'Getter' => 'getStartEndDate',
				'Field' => null
			),
			/*
			'Status' => array (
				'Label' => ML_GENERIC_LABEL_STATUS,
				'Getter' => 'getStatus',
				'Field' => null
			),*/
		);
	}

	protected function getSortOpt() {
		if (isset($_GET['sorting'])) {
			$sorting = $_GET['sorting'];
		} else {
			$sorting = 'blabla'; // fallback for default
		}
		$sortFlags = array (
			'sku' => 'SKU',
			'price' => 'Price',
			'quantity' => 'Quantity',
			'dateadded' => 'DateAdded',
			'startenddate' => 'StartEndDate',
		);
		$order = 'ASC';
		if (strpos($sorting, '-desc') !== false) {
			$order = 'DESC';
			$sorting = str_replace('-desc', '', $sorting);
		}
		if (array_key_exists($sorting, $sortFlags)) {
            $this->sort['order'] = $sortFlags[$sorting];
            $this->sort['type']  = $order;
		} else {
            $this->sort['order'] = 'DateAdded';
            $this->sort['type']  = 'DESC';
		}
	}

	protected function getMarketplaceTitle($item) {
		return '<td title="'.fixHTMLUTF8Entities($item['MarketplaceTitle'], ENT_COMPAT).'">'.$item['MarketplaceTitleShort'].'</td>';
	}

	/**
	 * If auction has bids, renders text 'Auction with bids'.
	 *
	 * @param array $item Item from listing table
	 * @return string Rendered table cell.
	 */
	protected function getLastModified($item) {
		if ($item['BidCount'] > 0 && $item['BuyingMode'] === 'auction') {
			return '<td title="' . ML_RICARDO_AUCTION_HAS_BIDS_TOOLTIP . '">' . ML_RICARDO_AUCTION_HAS_BIDS . '</td>';
		}

		if ($item['LastSync'] === '0000-00-00 00:00:00') {
			$item['LastSync'] = 0;
		} else {
			$item['LastSync'] = strtotime($item['LastSync']);
		}

		if ($item['LastSync'] <= 0) {
			return '<td>-</td>';
		}

		return '<td>'.date("d.m.Y", $item['LastSync']).' &nbsp;&nbsp;<span class="small">'.date("H:i", $item['LastSync']).'</span>'.'</td>';
	}

	protected function getItemId($item) {
		if (empty($item['ItemUrl']) === true) {
			return '<td>' . $item['ItemId'] . '</td>';
		}

		return '<td><a href="' . $item['ItemUrl'] . '" target="_blank">' . $item['ItemId'] . '</a></td>';
	}

	protected function getStartEndDate($item) {
		if ($item['StartTime'] === '0000-00-00 00:00:00') {
            $startTime = '-';
        } else {
            $startTimeUnixTs = strtotime($item['StartTime']);
            $startTime = date("d.m.Y", $startTimeUnixTs) . ' &nbsp;&nbsp;<span class="small">' . date("H:i", $startTimeUnixTs) . '</span>';
        }

        if ($item['EndDate'] === '0000-00-00 00:00:00') {
            $endTime = '-';
        } else {
            $endTimeUnixTs = strtotime($item['EndDate']);
            $endTime = date("d.m.Y", $endTimeUnixTs) . ' &nbsp;&nbsp;<span class="small">' . date("H:i", $endTimeUnixTs) . '</span>';
        }

		return "<td>$startTime<br>$endTime</td>";
	}

	protected function getStatus($item) {
		$html = '<td>';
		$status = $item['Status'];
		$itemId = $item['ItemId'];
		if ($status == 'active') {
			$html .= html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/green_dot.png', ML_HOOD_PRODUCT_PREPARED_OK, 9, 9);
		} elseif ($status == 'pending' && $itemId == '') {
			$html .= html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/grey_dot.png', ML_HOOD_PRODUCT_MATCHED_NO, 9, 9);
		} elseif ($status == 'pending' && $itemId != '') {
			$html .= html_image(DIR_MAGNALISTER_WS_IMAGES . 'status/blue_dot.png', ML_EBAY_PRODUCT_PREPARED_FAULTY_BUT_MP, 9, 9);
		}

		return $html . '</td>';
	}

	protected function getQuantities($item) {
		if (getDBConfigValue('general.keytype', '0') == 'artNr') {
			$where = 'sku';
		} else {
			$where = 'id';
		}

		$shopQuantity = (int)MagnaDB::gi()->fetchOne("
			SELECT variation_quantity
			FROM " . TABLE_PRODUCTS_VARIATIONS . "
			WHERE marketplace_" . $where . " = '" . $item['SKU'] . "'
		");

		if (!$shopQuantity) {
			$shopQuantity = (int)MagnaDB::gi()->fetchOne("
				SELECT products_quantity
				  FROM " . TABLE_PRODUCTS . "
				 WHERE products_id = '" . magnaSKU2pID($item['SKU']) . "'
			");
		}

		if (!$shopQuantity) {
			$shopQuantity = '-';
		}

		return '<td>' . $shopQuantity . ' / ' . $item['Quantity'] . '</td>';
	}

	protected function getBidCount($item) {
		if ($item['BuyingMode'] === 'buy_it_now') {
			$item['BidCount'] = '&mdash;';
		}

		return '<td>' . $item['BidCount'] . '</td>';
	}

	protected function postDelete() {
		MagnaConnector::gi()->submitRequest(array(
			'ACTION' => 'UploadItems'
		));
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
					$(\'#ricardoInfo\').click(function () {
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
							<tr><td>' . ML_RICARDO_LABEL_LAST_REPORT . '
									<div id="ricardoInfo" class="desc"></div>:
								</td>
								<td>' . (($latestReport > 0) ? date("d.m.Y &\b\u\l\l; H:i:s", $latestReport) : ML_LABEL_UNKNOWN) . '</td></tr>
							</tbody>
						</table>
					</td></tr></tbody>
				</table>
				<div id="infodiag" class="dialog2" title="' . ML_LABEL_NOTE . '">' . ML_RICARDO_TEXT_CHECKIN_DELAY. '</div>';
	}

	protected function getInventory() {
		try {
			$request = array(
				'ACTION' => 'GetInventory',
				'LIMIT' => $this->settings['itemLimit'],
				'OFFSET' => $this->offset,
				'ORDERBY' => $this->sort['order'],
				'SORTORDER' => $this->sort['type'],
				#'EXTRA' => 'ShowPending',
			);
			if (!empty($this->search)) {
				#$request['SEARCH'] = (!magnalisterIsUTF8($this->search)) ? utf8_encode($this->search) : $this->search;
				$request['SEARCH'] = $this->search;
			}
			$result = MagnaConnector::gi()->submitRequest($request);
			$this->numberofitems = (int)$result['NUMBEROFLISTINGS'];
			return $result;

		} catch (MagnaException $e) {
			return false;
		}
	}

	public function prepareInventoryData() {
		global $magnaConfig;

		$result = $this->getInventory();
		if (($result !== false) && !empty($result['DATA'])) {
			$this->renderableData = $result['DATA'];
			foreach ($this->renderableData as &$item) {
				if (isset($item['ItemTitle'])) {
					$item['Title'] = $item['ItemTitle'];
					unset($item['ItemTitle']);
				}
				$this->prepareInventoryItemData($item);
				$pID = magnaSKU2pID($item['SKU']);
				if (is_array($this->settings['language'])) {
					$iLanguageId = current($this->settings['language']);
				} else {
					$iLanguageId = $this->settings['language'];
				}
				$sTitle = (string)MagnaDB::gi()->fetchOne("
					SELECT products_name
					  FROM ".TABLE_PRODUCTS_DESCRIPTION."
					 WHERE     products_id = '".$pID."'
					       AND language_id = '".$iLanguageId."'
				");
				if (!empty($sTitle)) {
					$item['Title'] = $sTitle;
				} else {
					$item['Title'] = '&mdash;';
				}

				if ($item['SKU'] === $item['ItemId']) {
					$item['SKU'] = '&mdash;';
				}

				$item['TitleShort'] = (mb_strlen($item['Title'], 'UTF-8') > $this->settings['maxTitleChars'] + 2)
					? (fixHTMLUTF8Entities(mb_substr($item['Title'], 0, $this->settings['maxTitleChars'], 'UTF-8')).'&hellip;')
					: fixHTMLUTF8Entities($item['Title']);
				$item['DateAdded'] = ((isset($item['DateAdded'])) ? strtotime($item['DateAdded']) : '');
			}
			unset($result);
		}

	}

	protected function renderDataGrid($id = '') {
		global $magnaConfig;

		$html = '
			<table'.(($id != '') ? ' id="'.$id.'"' : '').' class="datagrid">
				<thead class="small"><tr>
					<td class="nowrap" style="width: 5px;">
						<input type="checkbox" id="selectAll"/><label for="selectAll">'.ML_LABEL_CHOICE.'</label>
					</td>';
		$fieldsDesc = $this->getFields();
		foreach ($fieldsDesc as $fdesc) {
			$html .= '
					<td>'.$fdesc['Label'].((isset($fdesc['Sorter']) && ($fdesc['Sorter'] != null)) ? ' '.$this->sortByType($fdesc['Sorter']) : '').'</td>';
		}
		$html .= '
				</tr></thead>
				<tbody>
		';
		$oddEven = false;
		foreach ($this->renderableData as $item) {
			$details = htmlspecialchars(str_replace('"', '\\"', serialize(array(
			 	'SKU' => $item['SKU'],
			 	'Price' => $item['Price'],
			 	'Currency' => isset($item['Currency']) ? $item['Currency'] : $this->mpCurrency,
			))));
			$addStyle = ($item['Title'] === '&mdash;' && $item['SKU'] !== '&mdash;')
				|| ($item['BidCount'] > 0 && $item['BuyingMode'] === 'auction')
				? 'style="color:#900;"'
				: '';
			$html .= '
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'" '.$addStyle.'>
					<td><input type="checkbox" name="SKUs[]" value="'.$item['SKU'].'">
						<input type="hidden" name="details['.$item['SKU'].']" value="'.$details.'"></td>';
			foreach ($fieldsDesc as $fdesc) {
				if ($fdesc['Field'] != null) {
					$html .= '
					<td>'.$item[$fdesc['Field']].'</td>';

				} else {
					$html .= '
					'.call_user_func(array($this, $fdesc['Getter']), $item);
				}
			}
			$html .= '
				</tr>';
		}
		$html .= '
				</tbody>
			</table>';

		return $html;
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
						'.renderPagination($currentPage, $pages, $tmpURL).'
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
		state = $(this).attr('checked');
		$('#csinventory input[type="checkbox"]:not([disabled])').each(function() {
			$(this).attr('checked', state);
		});
	});
});
/*]]>*/</script>
<?php
		$html .= ob_get_contents();
		ob_end_clean();

		return $html;
	}
}
