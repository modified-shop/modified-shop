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
 * (c) 2013 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once (DIR_MAGNALISTER_INCLUDES . 'lib/classes/SimplePrice.php');
require_once (DIR_MAGNALISTER_INCLUDES.'lib/classes/VariationsCalculator.php');

class HoodInventoryView {

	protected $marketplace = '';
	protected $mpID = 0;
	protected $settings = array();
	protected $sort = array();
	protected $numberofitems = 0;
	protected $offset = 0;
	protected $renderableData = array();
	protected $simplePrice = null;
	protected $url = array();
	protected $magnasession = array();
	protected $magnaShopSession = array();
	protected $search = '';

	public function __construct($settings = array()) {
		global $_MagnaShopSession, $_MagnaSession, $_url, $_modules;

		$this->magnasession = &$_MagnaSession;
		$this->mpID = $this->magnasession['mpID'];
		$this->magnaShopSession = &$_MagnaShopSession;

		if (isset($_GET['itemsPerPage'])) {
			$this->magnasession[$this->mpID]['InventoryView']['ItemLimit'] = (int) $_GET['itemsPerPage'];
		}
		if (!isset($this->magnasession[$this->mpID]['InventoryView']['ItemLimit']) || ($this->magnasession[$this->mpID]['InventoryView']['ItemLimit'] <= 0)
		) {
			$this->magnasession[$this->mpID]['InventoryView']['ItemLimit'] = 50;
		}

		$this->settings = array_merge(array(
			'maxTitleChars' => 85,
			'itemLimit' => $this->magnasession[$this->mpID]['InventoryView']['ItemLimit'],
			'language' => getDBConfigValue('hood.lang', $this->mpID),
		), $settings);
		
		$this->simplePrice = new SimplePrice();
		$this->simplePrice->setCurrency('EUR');
		$this->url = $_url;
		$this->url['view'] = 'inventory';


		if (array_key_exists('tfSearch', $_POST) && !empty($_POST['tfSearch'])) {
			$this->search = $_POST['tfSearch'];
		} else if (array_key_exists('search', $_GET) && !empty($_GET['search'])) {
			$this->search = $_GET['search'];
		}
	}

	private function getInventory() {
		try {
			$request = array(
				'ACTION' => 'GetInventory',
				'LIMIT' => $this->settings['itemLimit'],
				'OFFSET' => $this->offset,
				'ORDERBY' => $this->sort['order'],
				'SORTORDER' => $this->sort['type']
			);
			if (!empty($this->search)) {
				#$request['SEARCH'] = (!magnalisterIsUTF8($this->search)) ? utf8_encode($this->search) : $this->search;
				$request['SEARCH'] = $this->search;
			}
			MagnaConnector::gi()->setTimeOutInSeconds(1800);
			$result = MagnaConnector::gi()->submitRequest($request);
			MagnaConnector::gi()->resetTimeOut();
			$this->numberofitems = (int) $result['NUMBEROFLISTINGS'];
			return $result;
		} catch (MagnaException $e) {
			return false;
		}
	}

	protected function sortByType($type) {
		$tmpURL = $this->url;
		if (!empty($this->search)) {
			$tmpURL['search'] = urlencode($this->search);
		}
		return '
			<span class="nowrap">
				<a href="' . toURL($tmpURL, array('sorting' => $type . '')) . '" title="' . ML_LABEL_SORT_ASCENDING . '" class="sorting">
					<img alt="' . ML_LABEL_SORT_ASCENDING . '" src="' . DIR_MAGNALISTER_WS_IMAGES . 'sort_up.png" />
				</a>
				<a href="' . toURL($tmpURL, array('sorting' => $type . '-desc')) . '" title="' . ML_LABEL_SORT_DESCENDING . '" class="sorting">
					<img alt="' . ML_LABEL_SORT_DESCENDING . '" src="' . DIR_MAGNALISTER_WS_IMAGES . 'sort_down.png" />
				</a>
			</span>';
	}

	protected function getSortOpt() {
		if (isset($_GET['sorting'])) {
			$sorting = $_GET['sorting'];
		} else {
			$sorting = 'blabla'; // fallback for default
		}

		switch ($sorting) {
			case 'sku':
				$this->sort['order'] = 'SKU';
				$this->sort['type'] = 'ASC';
				break;
			case 'sku-desc':
				$this->sort['order'] = 'SKU';
				$this->sort['type'] = 'DESC';
				break;
			case 'Title':
				$this->sort['order'] = 'Title';
				$this->sort['type'] = 'ASC';
				break;
			case 'itemtitle-desc':
				$this->sort['order'] = 'Title';
				$this->sort['type'] = 'DESC';
				break;
			case 'price':
				$this->sort['order'] = 'Price';
				$this->sort['type'] = 'ASC';
				break;
			case 'price-desc':
				$this->sort['order'] = 'Price';
				$this->sort['type'] = 'DESC';
				break;
			case 'starttime':
				$this->sort['order'] = 'StartTime';
				$this->sort['type'] = 'ASC';
				break;
			case 'starttime-desc':
			default:
				$this->sort['order'] = 'StartTime';
				$this->sort['type'] = 'DESC';
				break;
		}
	}

	private function initInventoryView() {
		//$_POST['timestamp'] = time();
		if (isset($_POST['ItemIDs']) && is_array($_POST['ItemIDs']) && isset($_POST['action']) &&
				($_SESSION['POST_TS'] != $_POST['timestamp']) // Re-Post Prevention
		) {
			$_SESSION['POST_TS'] = $_POST['timestamp'];
			switch ($_POST['action']) {
				case 'delete': {
					$itemIDs = $_POST['ItemIDs'];
					$request = array(
						'ACTION' => 'DeleteItems',
						'DATA' => array(),
					);
					$insertData = array();
					foreach ($itemIDs as $itemID) {
						$request['DATA'][] = array(
							'AuctionId' => $itemID,
						);
					}
					/*
					echo print_m($insertData, '$insertData');
					echo print_m($request, '$request');
					break;
					//*/
					try {
						$result = MagnaConnector::gi()->submitRequest($request);
						$result = MagnaConnector::gi()->submitRequest(array(
							'ACTION' => 'UploadItems',
						));
					} catch (MagnaException $e) {
						$result = array(
							'STATUS' => 'ERROR'
						);
					}
					break;
				}
			}
		}

		$this->getSortOpt();

		if (isset($_GET['page']) && ctype_digit($_GET['page'])) {
			$this->offset = ($_GET['page'] - 1) * $this->settings['itemLimit'];
		} else {
			$this->offset = 0;
		}
	}

	public function prepareInventoryData() {
		$result = $this->getInventory();
		if (($result !== false) && !empty($result['DATA'])) {
			$this->renderableData = $result['DATA'];
			foreach ($this->renderableData as &$item) {
				$item['SKU'] = html_entity_decode(fixHTMLUTF8Entities($item['SKU']));
				$item['ItemTitleShort'] = (strlen($item['Title']) > $this->settings['maxTitleChars'] + 2) ? (fixHTMLUTF8Entities(substr($item['Title'], 0, $this->settings['maxTitleChars'])) . '&hellip;') : fixHTMLUTF8Entities($item['Title']);
				$item['VariationAttributesText'] = '';
				foreach ($item['Variation'] as $variation) {
					$item['VariationAttributesText'] .= fixHTMLUTF8Entities($variation['Name'] . ': ' . $variation['Value']).'<br />';
				}
                $item['VariationAttributesText']= rtrim($item['VariationAttributesText'], '<br />');
				$item['StartTime'] = strtotime($item['StartTime']);
				$item['EndTime'] = ('-1' == $item['ListingDuration'] ? '&mdash;' : strtotime($item['EndTime']));
				$item['LastSync'] = strtotime($item['LastSync']);
			}
			unset($result);
		}
		$this->getShopDataForItems();
	}

    /**
     * @param $aSkuEscapedList - ready for queries
     * @param $aSku - List of the SKUs...
     * @return mixed
     */
    protected function getVariationItemsForGambioProperties($aSkuEscapedList, $aSku) {
        /*
         * SKU, products_id, ShopQuantity, ShopPrice, ShopTitle, ShopVarText
         */
        if ('artNr' == getDBConfigValue('general.keytype', '0')) {
            $sSelectSKU = "CONCAT(p.products_model, '-', ppc.combi_model)";
            $aShopDataForVariationItems = MagnaDB::gi()->fetchArray(eecho("
                SELECT DISTINCT ".$sSelectSKU." AS SKU,
                       ppc.products_id AS products_id, '' AS variation_attributes,
                       CAST(ppc.combi_quantity AS SIGNED) AS ShopQuantity,
                       ppc.combi_price + p.products_price AS ShopPrice,
                       pd.products_name AS ShopTitle,
                       ppc.products_properties_combis_id
                  FROM products_properties_combis ppc, ".TABLE_PRODUCTS." p, ".TABLE_PRODUCTS_DESCRIPTION." pd
                 WHERE     ppc.products_id = p.products_id
                       AND ppc.products_id = pd.products_id
                       AND pd.language_id = '".$this->settings['language']."'
                       AND ".$sSelectSKU." IN (".$aSkuEscapedList.")", false)
            );
        } else {
            $aShopDataForVariationItems = array();
            foreach ($aSku as $sku) {
                $combisId = magnaSKU2aID($sku, false, true);
                $aShopDataForVariationItems[] = MagnaDB::gi()->fetchRow("
                    SELECT '$sku' AS SKU,
                            ppc.products_id AS products_id, '' AS variation_attributes,
                            CAST(ppc.combi_quantity AS SIGNED) AS ShopQuantity,
                            ppc.combi_price + p.products_price AS ShopPrice,
                            pd.products_name AS ShopTitle,
                            ppc.products_properties_combis_id
                      FROM products_properties_combis ppc, ".TABLE_PRODUCTS." p, ".TABLE_PRODUCTS_DESCRIPTION." pd
                     WHERE      ppc.products_id=p.products_id
                            AND ppc.products_id=pd.products_id
                            AND pd.language_id='".$this->settings['language']."'
                            AND ppc.products_properties_combis_id = '$combisId'"
                );
            }
        }

        foreach ($aShopDataForVariationItems as &$aShopDataForVariationItem) {
            $aProperties = MagnaDB::gi()->fetchArray("
                SELECT CONCAT(properties_name, ': ', values_name) AS ShopVarText
                  FROM products_properties_index
                 WHERE     products_id = '".$aShopDataForVariationItem['products_id']."'
                       AND language_id = '".$this->settings['language']."'
                       AND products_properties_combis_id = '".$aShopDataForVariationItem['products_properties_combis_id']."'
            
            ", true);

            if (!empty($aProperties)) {
                $aShopDataForVariationItem['ShopVarText'] = implode('<br />', $aProperties);
            }

            unset($aShopDataForVariationItem['products_properties_combis_id']);
        }

        return $aShopDataForVariationItems;
    }
	
	protected function getVariationItems($sKUlist, $sKUarr) {
        if (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties') {
            return $this->getVariationItemsForGambioProperties($sKUlist, $sKUarr);
        }

		$data = MagnaDB::gi()->fetchArray('
			SELECT DISTINCT v.'.mlGetVariationSkuField().' AS SKU,
			       v.products_id products_id,
			       v.variation_attributes,
			       CAST(v.variation_quantity AS SIGNED) ShopQuantity, 
			       v.variation_price + p.products_price ShopPrice,
			        pd.products_name ShopTitle
			  FROM ' . TABLE_MAGNA_VARIATIONS . ' v, ' . TABLE_PRODUCTS . ' p, ' . TABLE_PRODUCTS_DESCRIPTION . ' pd
			 WHERE v.products_id=p.products_id
			       AND v.products_id=pd.products_id
			       AND pd.language_id=' . $this->settings['language'] . '
			       AND v.'.mlGetVariationSkuField().' IN (' . $sKUlist . ')
		');
		$bEmpty = true;
		$aLoaded = array();
		if (!empty($data)) {
			$bEmpty = false;
			if (count($data) !== count($sKUarr)) {
				$bEmpty = true;
				foreach ($data as $product) {
					$aLoaded[] = $product['SKU'];
				}
			}
		}

		if ($bEmpty) {
			foreach ($sKUarr as $sku) {
				if (in_array($sku, $aLoaded)) {
					continue;
				}
				$aId = magnaSKU2aID($sku);
				if (empty($aId)) {
					continue;
				}
				$set = MagnaDB::gi()->fetchRow("
					SELECT '' AS SKU, v.products_id,
					       CONCAT('|', v.options_id, ',', v.options_values_id, '|') AS variation_attributes,
					       CAST(v.attributes_stock AS SIGNED) ShopQuantity,
					       IF (price_prefix='=', v.options_values_price, (p.products_price + (v.options_values_price * IF(v.price_prefix='+', 1, -1)))) AS ShopPrice,
					       pd.products_name ShopTitle
					  FROM ".TABLE_PRODUCTS_ATTRIBUTES." v, " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
					 WHERE products_attributes_id = ".$aId."
					       AND v.products_id=p.products_id
					       AND v.products_id=pd.products_id
					       AND pd.language_id=" . $this->settings['language'] . "
					 LIMIT 1
				");
				if (!empty($set)) {
					$set['SKU'] = $sku;
					$data[] = $set;
				}
			}
		}
		
		if (!empty($data)) {
			foreach ($data as &$row) {
				$row['ShopVarText'] = VariationsCalculator::generateVariationsAttributesText($row['variation_attributes'], $this->settings['language'], ', ', ': ');
			}
		}
		//echo print_m($data, '$data');
		return $data;
	}
	
	private function getShopDataForItems() {
		$sKUarr = array();
		$sKUarrEsc = array();
		foreach ($this->renderableData as $item) {
			$sKUarr[] = $item['SKU'];
			$sKUarrEsc[] = MagnaDB::gi()->escape($item['SKU']);
		}
		if (!empty($sKUarrEsc)) {
			$sKUlist = "'".implode("', '", $sKUarrEsc)."'";
			if ('artNr' == getDBConfigValue('general.keytype', '0')) {
				$shopDataForSimpleItems = MagnaDB::gi()->fetchArray('
					SELECT DISTINCT p.products_model SKU, p.products_id products_id, 
					       CAST(p.products_quantity AS SIGNED) ShopQuantity, p.products_price ShopPrice,
					       pd.products_name ShopTitle 
					  FROM ' . TABLE_PRODUCTS . ' p, ' . TABLE_PRODUCTS_DESCRIPTION . ' pd
					 WHERE p.products_id=pd.products_id
					       AND pd.language_id=' . $this->settings['language'] . '
					       AND p.products_model IN (' . $sKUlist . ')
				');
			} else {
				$shopDataForSimpleItems = MagnaDB::gi()->fetchArray('
					SELECT DISTINCT CONCAT(\'ML\',p.products_id) SKU, p.products_id products_id, 
					       CAST(p.products_quantity AS SIGNED) ShopQuantity, p.products_price ShopPrice,
					       pd.products_name ShopTitle
					  FROM ' . TABLE_PRODUCTS . ' p, ' . TABLE_PRODUCTS_DESCRIPTION . ' pd
					 WHERE p.products_id=pd.products_id
					       AND pd.language_id=' . $this->settings['language'] . '
					       AND CONCAT(\'ML\',p.products_id) IN (' . $sKUlist . ')
				');
			}
			$shopDataForVariationItems = $this->getVariationItems($sKUlist, $sKUarr);
			$shopDataForItemsBySKU = array();
			if (is_array($shopDataForSimpleItems)) {
				foreach ($shopDataForSimpleItems as $shopDataForSimpleItem) {
					$shopDataForItemsBySKU[$shopDataForSimpleItem['SKU']] = $shopDataForSimpleItem;
					unset($shopDataForItemsBySKU[$shopDataForSimpleItem['SKU']]['SKU']);
					$shopDataForItemsBySKU[$shopDataForSimpleItem['SKU']]['ShopVarText'] = '';
				}
			}
			if (is_array($shopDataForVariationItems)) {
				foreach ($shopDataForVariationItems as &$shopDataForVariationItem) {
					$shopDataForItemsBySKU[$shopDataForVariationItem['SKU']] = $shopDataForVariationItem;
					unset($shopDataForItemsBySKU[$shopDataForVariationItem['SKU']]['SKU']);
				}
			}
		} else {
			$shopDataForItemsBySKU = array();
		}

		foreach ($this->renderableData as &$item) {
			if (isset($shopDataForItemsBySKU[$item['SKU']])) {
				$item['ProductsID'] = $shopDataForItemsBySKU[$item['SKU']]['products_id'];
				$item['ShopQuantity'] = $shopDataForItemsBySKU[$item['SKU']]['ShopQuantity'];
				$item['ShopPrice'] = $shopDataForItemsBySKU[$item['SKU']]['ShopPrice'];
				$item['ShopTitle'] = $shopDataForItemsBySKU[$item['SKU']]['ShopTitle'];
				$item['ShopVarText'] = $shopDataForItemsBySKU[$item['SKU']]['ShopVarText'];
			} else {
				$item['ShopQuantity'] = $item['ShopPrice'] = $item['ShopTitle'] = '&mdash;';
				$item['ShopVarText'] = '&nbsp;';
				$item['ProductsID'] = 0;
			}
		}
	}

	private function emptyStr2mdash($str) {
		return (empty($str) || (is_numeric($str) && ($str == 0))) ? '&mdash;' : $str;
	}

	protected function additionalHeaders() {
		
	}

	protected function additionalValues($item) {
		
	}

	private function renderDataGrid($id = '') {
		$priceBrutto = !(defined('PRICE_IS_BRUTTO') && (PRICE_IS_BRUTTO == 'false'));
		$html = '
			<table' . (($id != '') ? ' id="' . $id . '"' : '') . ' class="datagrid">
				<thead class="small"><tr>
					<td class="nowrap" style="width: 5px;"><input type="checkbox" id="selectAll"/><label for="selectAll">' . ML_LABEL_CHOICE . '</label></td>
					<td>' . ML_LABEL_SKU . ' ' . $this->sortByType('sku') . '</td>
					<td>' . ML_LABEL_SHOP_TITLE . '</td>
					<td>' . ML_LABEL_HOOD_TITLE . ' ' . $this->sortByType('Title') . '</td>
					<td>' . ML_LABEL_HOOD_ITEM_ID . '</td>
					<td>' . ML_HOOD_LISTING_TYPE . '</td>
					<td>' . ($priceBrutto ? ML_LABEL_SHOP_PRICE_BRUTTO : ML_LABEL_SHOP_PRICE_NETTO
				) . ' / hood ' . $this->sortByType('price') . '</td>
					<td>' . ML_STOCK_SHOP_STOCK_HOOD . '<br />' . ML_LAST_SYNC . '</td>
					<td>'.ML_GENERIC_LABEL_LISTINGTIME.' '.$this->sortByType('starttime').'</td>
				</tr></thead>
				<tbody>
		';

		$oddEven = false;
		#$this->getShopDataForItems();
		foreach ($this->renderableData as $item) {
			$details = htmlspecialchars(str_replace('"', '\\"', serialize(array(
				'SKU' => $item['SKU'],
				'Price' => $item['Price'],
				'Currency' => 'EUR',
			))));
			if (0 != $item['ShopPrice']) {
				$this->simplePrice->setPriceAndCurrency($item['ShopPrice'], 'EUR');
				if ($priceBrutto) {
					//$this->simplePrice->addTax(10);
					$this->simplePrice->addTaxByPID($item['ProductsID']);
				}
			}

			$renderedShopPrice = (0 != $item['ShopPrice']) ? $this->simplePrice->format() : '&mdash;';
			$addStyle = ('&mdash;' == $item['ShopTitle']) ? 'style="color:#900;"' : '';
			$icon = ((isset($item["ListingType"]) && "shopProduct" == $item['ListingType']) 
				? '&nbsp;<img src="' . DIR_MAGNALISTER . '/images/magnalister_11px_icon_color.png" width=11 height=11 />' 
				: ''
			);
			$listingDefine = 'ML_HOOD_LISTINGTYPE_'.strtoupper($item['ListingType']);
			$textListingType = (defined($listingDefine) ? constant($listingDefine) : $item['ListingType']);
			$html .= '
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '" ' . $addStyle . '>
					<td><input type="checkbox" name="ItemIDs[]" value="' . $item['AuctionId'] . '">
						<input type="hidden" name="details[' . $item['AuctionId'] . ']" value="' . $details . '">'
					. $icon . '</td>
					<td>' . fixHTMLUTF8Entities($item['SKU'], ENT_COMPAT) . '</td>
					<td title="' . fixHTMLUTF8Entities($item['ShopTitle'], ENT_COMPAT) . '">' . $item['ShopTitle'] . '<br /><span class="small">' . $item['ShopVarText'] . '</span></td>
					<td title="' . fixHTMLUTF8Entities($item['Title'], ENT_COMPAT) . '">' . $item['ItemTitleShort'] . '<br /><span class="small">' . $item['VariationAttributesText'] . '</span></td>
					<td><a href="http://www.hood.de/00' . $item['AuctionId'] . '.htm" target="_blank">' . $item['AuctionId'] . '</a></td>
					<td>'.$textListingType.'</td>
					<td>' . $renderedShopPrice . ' / ' . $this->simplePrice->setPriceAndCurrency($item['Price'], 'EUR')->format() . '</td>
					<td>' . $item['ShopQuantity'] . ' / ' . $item['Quantity'] . '<br />' . date("d.m.Y", $item['LastSync']) . ' &nbsp;&nbsp;<span class="small">' . date("H:i", $item['LastSync']) . '</span></td>
					<td>'.date("d.m.Y", $item['StartTime']).' &nbsp;&nbsp;<span class="small">'.date("H:i", $item['StartTime']).'</span><br />'.('&mdash;' == $item['EndTime']? '&mdash;' : date("d.m.Y", $item['EndTime']).' &nbsp;&nbsp;<span class="small">'.date("H:i", $item['EndTime']).'</span>').'</td>
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
		# echo print_m($this->renderableData, 'renderInventoryTable: $this->renderableData');


		$pages = ceil($this->numberofitems / $this->settings['itemLimit']);

		$tmpURL = $this->url;
		if (isset($_GET['sorting'])) {
			$tmpURL['sorting'] = $_GET['sorting'];
		}
		if (!empty($this->search)) {
			$tmpURL['search'] = urlencode($this->search);
		}
		$currentPage = 1;
		if (isset($_GET['page']) && ctype_digit($_GET['page']) && (1 <= (int) $_GET['page']) && ((int) $_GET['page'] <= $pages)) {
			$currentPage = (int) $_GET['page'];
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
						' . (($this->numberofitems > 0) ? ('<span class="bold">' . ML_LABEL_PRODUCTS . ':&nbsp; ' .
						$offset . ' bis ' . $limit . ' von ' . ($this->numberofitems) . '&nbsp;&nbsp;&nbsp;&nbsp;</span>'
						) : ''
				) . '
						<span class="bold">' . ML_LABEL_CURRENT_PAGE . ':&nbsp; ' . $currentPage . '</span>
					</td>
					<td class="textright">
						' . renderPagination($currentPage, $pages, $tmpURL) . '&nbsp;' . $chooser . '
					</td>
				</tr></tbody></table>';

		if (!empty($this->renderableData)) {
			$html .= $this->renderDataGrid('hoodinventory');
		} else {
			$html .= '<table class="magnaframe"><tbody><tr><td>' .
					(empty($this->search) ? ML_GENERIC_NO_INVENTORY : ML_LABEL_NO_SEARCH_RESULTS) .
					'</td></tr></tbody></table>';
		}

		ob_start();
		?>
		<script type="text/javascript">/*<![CDATA[*/
			$(document).ready(function() {
				$('#selectAll').click(function() {
					state = $(this).attr('checked');
					$('#hoodinventory input[type="checkbox"]:not([disabled])').each(function() {
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

	protected function getRightActionButton() {
		return '';
	}

	public function renderActionBox() {
		global $_modules;
		$left = (!empty($this->renderableData) ?
						'<input type="button" class="ml-button" value="' . ML_BUTTON_LABEL_DELETE . '" id="listingDelete" name="listing[delete]"/>' :
						''
				);

		$right = $this->getRightActionButton();

		ob_start();
		?>
		<script type="text/javascript">/*<![CDATA[*/
			$(document).ready(function() {
				$('#listingDelete').click(function() {
					if (($('#hoodinventory input[type="checkbox"]:checked').length > 0) &&
							confirm(unescape(<?php echo "'" . html2url(sprintf(ML_GENERIC_DELETE_LISTINGS, $_modules[$this->magnasession['currentPlatform']]['title'])) . "'"; ?>))
							) {
						$('#action').val('delete');
						$(this).parents('form').submit();
					}
				});
			});
			/*]]>*/</script>
		<?php
		// Durch aufrufen der Seite wird automatisch ein Aktualisierungsauftrag gestartet
		$js = ob_get_contents();
		ob_end_clean();

		if (($left == '') && ($right == '')) {
			return '';
		}
		return '
			<input type="hidden" id="action" name="action" value="">
			<input type="hidden" name="timestamp" value="' . time() . '">
			<table class="actions">
				<thead><tr><th>' . ML_LABEL_ACTIONS . '</th></tr></thead>
				<tbody><tr><td>
					<table><tbody><tr>
						<td class="firstChild">' . $left . '</td>
						<td><label for="tfSearch">' . ML_LABEL_SEARCH . ':</label>
							<input id="tfSearch" name="tfSearch" type="text" value="' . fixHTMLUTF8Entities($this->search, ENT_COMPAT) . '"/>
							<input type="submit" class="ml-button" value="' . ML_BUTTON_LABEL_GO . '" name="search_go" /></td>
						<td class="lastChild">' . $right . '</td>
					</tr></tbody></table>
				</td></tr></tbody>
			</table>
			' . $js;
	}

	public function renderView() {
		$html = '<form action="' . toUrl($this->url) . '" id="hoodInventoryView" method="post">';
		$this->initInventoryView();
		$html .= $this->renderInventoryTable();
		return $html . $this->renderActionBox() . '
			</form>
			<script type="text/javascript">/*<![CDATA[*/
				$(document).ready(function() {
					$(\'#hoodInventoryView\').submit(function () {
						jQuery.blockUI(blockUILoading);
					});
				});
			/*]]>*/</script>';
	}

}
