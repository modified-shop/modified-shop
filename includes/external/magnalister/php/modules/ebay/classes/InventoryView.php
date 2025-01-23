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
 * $Id: InventoryView.php 680 2011-01-11 13:54:55Z MaW $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once (DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');
require_once (DIR_MAGNALISTER_INCLUDES.'lib/classes/VariationsCalculator.php');

class InventoryView {
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

	protected $pendingItems = array();
	protected $newErrors = 0;

	protected $search = '';

	public function __construct($marketplace, $settings = array()) {
		global $_MagnaShopSession, $_MagnaSession, $_url, $_modules;
		
		$this->marketplace = $marketplace;
		$this->magnasession = &$_MagnaSession;
		$this->mpID = $this->magnasession['mpID'];
		$this->magnaShopSession = &$_MagnaShopSession;
		
		if (isset($_GET['itemsPerPage'])) {
			$this->magnasession[$this->mpID]['InventoryView']['ItemLimit'] = (int)$_GET['itemsPerPage'];
		}
		if (!isset($this->magnasession[$this->mpID]['InventoryView']['ItemLimit'])
			|| ($this->magnasession[$this->mpID]['InventoryView']['ItemLimit'] <= 0)
		) {
			$this->magnasession[$this->mpID]['InventoryView']['ItemLimit'] = 50;
		}
		
		$this->settings = array_merge(array(
			'maxTitleChars'	=> 80,
			'itemLimit'		=> $this->magnasession[$this->mpID]['InventoryView']['ItemLimit'],
		), $settings);

		$this->simplePrice = new SimplePrice();
		$this->simplePrice->setCurrency(getCurrencyFromMarketplace($_MagnaSession['mpID']));
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
				'SORTORDER' => $this->sort['type'],
				'EXTRA' => 'ShowPending'
			);
			if (!empty($this->search)) {
				#$request['SEARCH'] = (!magnalisterIsUTF8($this->search)) ? utf8_encode($this->search) : $this->search;
				$request['SEARCH'] = $this->search;
			}
			MagnaConnector::gi()->setTimeOutInSeconds(1800);
			$result = MagnaConnector::gi()->submitRequest($request);
			MagnaConnector::gi()->resetTimeOut();
			$this->numberofitems = (int)$result['NUMBEROFLISTINGS'];
			return $result;

		} catch (MagnaException $e) {
			return false;
		}
	}

	/**
	 * Hint: Don't forget to add a define like: ML_EBAY_N_PENDING_UPDATES_TITLE_.strtoupper($sRequest)
	 * @param string $sRequest
	 */
	private function getPendingFunction($sRequest = 'Items') {
		try {
			$result = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'GetPending'.$sRequest,
			));
		} catch (MagnaException $e) {
			$result = array('DATA' => false);
		}
		$waitingItems = 0;
		$maxEstimatedTime = 0;
		if (is_array($result['DATA']) && !empty($result['DATA'])) {
			foreach ($result['DATA'] as $item) {
				$maxEstimatedTime = max($maxEstimatedTime, $item['EstimatedWaitingTime']);
				$waitingItems  += 1;
			}
		}
		$this->pendingItems[$sRequest] = array (
			'itemsCount' => $waitingItems,
			'estimatedWaitingTime' => $maxEstimatedTime
		);
	}

	private function getNewErrorCount() {
		try {
			$result = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'GetNumberOfNewErrors',
				'SECONDS' => 600
			));
		} catch (MagnaException $e) {
			$result = array('Errors' => 0);
		}
		if (!isset($result['DATA'])) {
			$result = array('Errors' => 0);
			return;
		}
		$this->newErrors = $result['DATA']['Errors'];
	}

	protected function sortByType($type) {
		$tmpURL = $this->url;
		if (!empty($this->search)) {
			$tmpURL['search'] = urlencode($this->search);
		}
		return '
			<span class="nowrap">
				<a href="'.toURL($tmpURL, array('sorting' => $type.'')).'" title="'.ML_LABEL_SORT_ASCENDING.'" class="sorting">
					<img alt="'.ML_LABEL_SORT_ASCENDING.'" src="'.DIR_MAGNALISTER_WS_IMAGES.'sort_up.png" />
				</a>
				<a href="'.toURL($tmpURL, array('sorting' => $type.'-desc')).'" title="'.ML_LABEL_SORT_DESCENDING.'" class="sorting">
					<img alt="'.ML_LABEL_SORT_DESCENDING.'" src="'.DIR_MAGNALISTER_WS_IMAGES.'sort_down.png" />
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
				$this->sort['type']  = 'ASC';
				break;
			case 'sku-desc':
				$this->sort['order'] = 'SKU';
				$this->sort['type']  = 'DESC';
				break;
			case 'itemtitle':
				$this->sort['order'] = 'ItemTitle';
				$this->sort['type']  = 'ASC';
				break;
			case 'itemtitle-desc':
				$this->sort['order'] = 'ItemTitle';
				$this->sort['type']  = 'DESC';
				break;
			case 'price':
				$this->sort['order'] = 'Price';
				$this->sort['type']  = 'ASC';
				break;
			case 'price-desc':
				$this->sort['order'] = 'Price';
				$this->sort['type']  = 'DESC';
				break;
			case 'dateadded':
				$this->sort['order'] = 'DateAdded';
				$this->sort['type']  = 'ASC';
				break;
			case 'dateadded-desc':
			default:
				$this->sort['order'] = 'DateAdded';
				$this->sort['type']  = 'DESC';
				break;
		}
	}
	
	protected function postDelete() { /* Nix :-) */ }
	
	private function initInventoryView() {
		//$_POST['timestamp'] = time();
		if (isset($_POST['ItemIDs']) && is_array($_POST['ItemIDs']) && isset($_POST['action']) && 
			($_SESSION['POST_TS'] != $_POST['timestamp']) // Re-Post Prevention
		) {
			$_SESSION['POST_TS'] = $_POST['timestamp'];
			switch ($_POST['action']) {
				case 'delete': {
					$itemIDs = $_POST['ItemIDs'];
					$request = array (
						'ACTION' => 'DeleteItems',
						'DATA' => array(),
					);
					$insertData = array();
					foreach ($itemIDs as $itemID) {
						$request['DATA'][] = array (
							'ItemID' => $itemID,
						);
						/*$pDetails = unserialize(str_replace('\\"', '"', $_POST['details'][$itemID]));
						$pID = magnaSKU2pID($sku);
						$model = '';
						if ($pID > 0) {
							$model = (string)MagnaDB::gi()->fetchOne('SELECT products_model FROM '.TABLE_PRODUCTS.' WHERE products_id=\''.$pID.'\'');
						}
						if (empty($model)) {
							$model = $sku;
						}
						$insertData[$itemID] = array (
							'products_id' => $pID,
							'products_model' => $model,
							'mpID' => $this->magnasession['mpID'],
							'ItemID' => $itemID,
							'price' => $pDetails['Price'],
							'timestamp' => date('Y-m-d H:i:s')
						);*/
					}
					/*
					echo print_m($insertData, '$insertData');
					echo print_m($request, '$request');
					*/
					MagnaConnector::gi()->setTimeOutInSeconds(7200); # 2 hrs, about 1800 Items
					try {
						$result = MagnaConnector::gi()->submitRequest($request);
					} catch (MagnaException $e) {
						$result = array (
							'STATUS' => 'ERROR'
						);
					}
					MagnaConnector::gi()->resetTimeOut();
					/*
					if ($result['STATUS'] == 'SUCCESS') {
						$result['DeletedItemIDs'] = array_keys($insertData);
					}
					echo print_m($result, '$result');
					*/
					if (($result['STATUS'] == 'SUCCESS') 
						&& array_key_exists('DeletedItemIDs', $result) 
						&& is_array($result['DeletedItemIDs'])
						&& !empty($result['DeletedItemIDs'])
					) {
						$this->postDelete();
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
		$this->getPendingFunction('Items');
		$this->getPendingFunction('ProductDetailUpdates');
		$this->getNewErrorCount();
		if (($result !== false) && !empty($result['DATA'])) {
			$this->renderableData = $result['DATA'];
			foreach ($this->renderableData as &$item) {
				$item['SKU'] = html_entity_decode(fixHTMLUTF8Entities($item['SKU']));
				$item['ItemTitleShort'] = (strlen($item['ItemTitle']) > $this->settings['maxTitleChars'] + 2)
						? (fixHTMLUTF8Entities(substr($item['ItemTitle'], 0, $this->settings['maxTitleChars'])).'&hellip;')
						: fixHTMLUTF8Entities($item['ItemTitle']);
				$item['VariationAttributesText'] = fixHTMLUTF8Entities($item['VariationAttributesText']);
				$item['DateAdded'] = strtotime($item['DateAdded']);
				$item['DateEnd']  = ('1'==$item['GTC']?'&mdash;':strtotime($item['End']));
				$item['LastSync'] = ('1970-01-01 00:00:00'==$item['LastSync']?'&mdash;':strtotime($item['LastSync']));
				if (isset($item['estimatedTime'])) {
					if ('0000-00-00 00:00:00' == $item['estimatedTime'])  
						$item['estimatedTime'] = '('.ML_LABEL_NOT_YET_KNOWN.')';
					} else {
					$item['estimatedTime'] = strtotime($item['estimatedTime']);
				}
			}
			unset($result);
		}
		$this->getShopDataForItems();

	}

    private function getShopDataForItems() {
        global $magnaConfig;
        $language = $magnaConfig['db'][$this->magnasession['mpID']]['ebay.lang'];
        $SKUarr = array();
        $SKUlist = '';
        foreach ($this->renderableData as $item) {
            // check each variation if master exists in shop
            if (!empty($item['MasterSKU']) && (int)magnaSKU2pID($item['MasterSKU'], true) === 0) {
                continue;
            }
            $SKUarr[] = $item['SKU'];
        }
        $SKUarr = array_unique($SKUarr);
		$character_set_client = MagnaDB::gi()->mysqlVariableValue('character_set_client');
		$character_set_system = MagnaDB::gi()->mysqlVariableValue('character_set_system');
		if (('utf8mb3' == $character_set_client) || ('utf8mb4' == $character_set_client)) {
			$character_set_client = 'utf8';
		}
		if (('utf8mb3' == $character_set_system) || ('utf8mb4' == $character_set_system)) {
			$character_set_system = 'utf8';
		}
		if (('utf8' == $character_set_system) && ('utf8' != $character_set_client)) {
			arrayEntitiesToLatin1($SKUarr);
		}
        foreach ($SKUarr as $currentSKU) {
            $SKUlist .= ", '".MagnaDB::gi()->escape($currentSKU)."'";
        }
        $SKUlist = ltrim($SKUlist, ', ');
        if (!empty($SKUlist)) {
            if ('artNr' == getDBConfigValue('general.keytype', '0')) {
                $ShopDataForSimpleItems = MagnaDB::gi()->fetchArray('
                    SELECT DISTINCT p.products_model SKU, p.products_id products_id, 
                           CAST(p.products_quantity AS SIGNED) ShopQuantity, p.products_price ShopPrice,
                           pd.products_name ShopTitle
                      FROM '.TABLE_PRODUCTS.' p, '.TABLE_PRODUCTS_DESCRIPTION.' pd
                     WHERE p.products_id=pd.products_id
                           AND pd.language_id='.$language.'
                           AND p.products_model IN ('.$SKUlist.')
                ');
            } else {
                $ShopDataForSimpleItems = MagnaDB::gi()->fetchArray('
                    SELECT DISTINCT CONCAT(\'ML\',p.products_id) SKU, p.products_id products_id, 
                           CAST(p.products_quantity AS SIGNED) ShopQuantity, p.products_price ShopPrice,
                           pd.products_name ShopTitle
                      FROM '.TABLE_PRODUCTS.' p, '.TABLE_PRODUCTS_DESCRIPTION.' pd
                     WHERE p.products_id=pd.products_id
                           AND pd.language_id='.$language.'
                           AND CONCAT(\'ML\',p.products_id) IN ('.$SKUlist.')
                ');
            }
			if (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties') {
				if ('artNr' == getDBConfigValue('general.keytype', '0')) {
					$selectSku = "CONCAT(p.products_model, '-', ppc.combi_model)";
					$ShopDataForVariationItems = MagnaDB::gi()->fetchArray(eecho("
					SELECT DISTINCT ".$selectSku." AS SKU,
					       ".$selectSku." AS SKUDeprecated,
					       ppc.products_id AS products_id, '' AS variation_attributes,
					       CAST(ppc.combi_quantity AS SIGNED) AS ShopQuantity,
					       ppc.combi_price + p.products_price AS ShopPrice,
					       pd.products_name AS ShopTitle
					  FROM products_properties_combis ppc, ".TABLE_PRODUCTS." p, ".TABLE_PRODUCTS_DESCRIPTION." pd
					 WHERE     ppc.products_id = p.products_id
					       AND ppc.products_id = pd.products_id
					       AND pd.language_id = '$language'
					       AND ".$selectSku." IN (".$SKUlist.")", false));
				} else {
					$ShopDataForVariationItems = array();
					foreach ($SKUarr as $sku) {
						$combisId = magnaSKU2aID($sku, false, true);
						$ShopDataForVariationItems[] = MagnaDB::gi()->fetchRow("
							SELECT '$sku' AS SKU, '$sku' AS SKUDeprecated,
					   	ppc.products_id AS products_id, '' AS variation_attributes,
					   	CAST(ppc.combi_quantity AS SIGNED) AS ShopQuantity,
					   	ppc.combi_price + p.products_price AS ShopPrice,
					   	pd.products_name AS ShopTitle
						FROM products_properties_combis ppc, ".TABLE_PRODUCTS." p, ".TABLE_PRODUCTS_DESCRIPTION." pd
				   	WHERE ppc.products_id=p.products_id
							AND ppc.products_id=pd.products_id
							AND pd.language_id='$language'
							AND ppc.products_properties_combis_id = '$combisId'");
					}
				}
			} else {
                // if there are more problems with not existing master sku,
                // we can filter by existing MasterSku where $SKUarr will filled in this method
                // => now here to use magnaSKU2pID only here (performance)
                $aSkusWithExistingMaster = array();
                foreach ($this->renderableData as $item) {
                    if ((int) magnaSKU2pID(empty($item['MasterSKU']) ? $item['SKU'] : $item['MasterSKU']) !== 0) {
                        $aSkusWithExistingMaster[] = MagnaDB::gi()->escape($item['SKU']);
                    }
                }
                if (empty($aSkusWithExistingMaster)) {
                    $ShopDataForVariationItems = array();
                } else {
                    $sSkusWithExistingMaster = '"'.implode('", "', $aSkusWithExistingMaster).'"';
                    $ShopDataForVariationItems = MagnaDB::gi()->fetchArray(eecho('
                        SELECT DISTINCT v.'.mlGetVariationSkuField().' AS SKU, v.variation_products_model AS SKUDeprecated,
                            v.products_id products_id, variation_attributes,
                            CAST(v.variation_quantity AS SIGNED) ShopQuantity, v.variation_price + p.products_price ShopPrice, pd.products_name ShopTitle
                        FROM '.TABLE_MAGNA_VARIATIONS.' v, '.TABLE_PRODUCTS.' p, '.TABLE_PRODUCTS_DESCRIPTION.' pd
                        WHERE v.products_id=p.products_id
                            AND v.products_id=pd.products_id
                            AND pd.language_id='.$language.'
                            AND (
                                    v.'.mlGetVariationSkuField().' IN ('.$sSkusWithExistingMaster.') 
                                    OR v.variation_products_model IN ('.$sSkusWithExistingMaster.')
                            )
                    ', false));
                }
			}
            // ePIDs
            /*if ('artNr' == getDBConfigValue('general.keytype', '0')) {
              $ePIDsForSimpleItems = MagnaDB::gi()->fetchArray('
                   SELECT products_model AS SKU, ePID FROM '.TABLE_MAGNA_EBAY_PROPERTIES.'
                    WHERE products_model IN ('.$SKUlist.')');
              $ePIDsForVariationItems = MagnaDB::gi()->fetchArray('
                   SELECT marketplace_sku AS SKU, ePID FROM magnalister_ebay_variations_epids
                    WHERE marketplace_sku IN ('.$SKUlist.')');
            } else {
              $ePIDsForSimpleItems = MagnaDB::gi()->fetchArray('
                   SELECT products_id AS SKU, ePID FROM '.TABLE_MAGNA_EBAY_PROPERTIES.'
                    WHERE CONCAT(\'ML\', products_id) IN ('.$SKUlist.')');
              $ePIDsForVariationItems = MagnaDB::gi()->fetchArray('
                   SELECT marketplace_id AS SKU, ePID FROM magnalister_ebay_variations_epids
                    WHERE marketplace_id IN ('.$SKUlist.')');
            }
            $ePIDsForSimpleItemsBySKU = array();
            foreach ($ePIDsForSimpleItems as $esRow) {
              if (!empty($esRow['ePID'])) $ePIDsForSimpleItemsBySKU[$esRow['SKU']] = $esRow['ePID'];
            }
            $ePIDsForVariationItemsBySKU = array();
            foreach ($ePIDsForVariationItems as $evRow) {
              if (!empty($evRow['ePID'])) $ePIDsForVariationItemsBySKU[$evRow['SKU']] = $evRow['ePID'];
            }
            $ePIDsBySKU = array_merge($ePIDsForSimpleItemsBySKU, $ePIDsForVariationItemsBySKU);
            */
            
			
            $ShopDataForItemsBySKU = array();
            foreach ($ShopDataForSimpleItems as $ShopDataForSimpleItem) {
                $ShopDataForItemsBySKU[$ShopDataForSimpleItem['SKU']] = $ShopDataForSimpleItem;
                unset ($ShopDataForItemsBySKU[$ShopDataForSimpleItem['SKU']]['SKU']);
                $ShopDataForItemsBySKU[$ShopDataForSimpleItem['SKU']]['ShopVarText'] = '';
            }
            foreach ($ShopDataForVariationItems as &$ShopDataForVariationItem) {
                if (('utf8' == $character_set_system) && ('utf8' != $character_set_client)) {
                    $ShopDataForVariationItem['SKU'] = utf8_encode($ShopDataForVariationItem['SKU']);
                }
                $ShopDataForItemsBySKU[$ShopDataForVariationItem['SKU']] = $ShopDataForVariationItem;
                unset ($ShopDataForItemsBySKU[$ShopDataForVariationItem['SKU']]['SKU']);
                //$ShopDataForVariationItem['ShopVarText'] = VariationsCalculator::generateVariationsAttributesText($ShopDataForVariationItem['variation_attributes'], $language, ', ', ':');
                $ShopDataForItemsBySKU[$ShopDataForVariationItem['SKUDeprecated']] = &$ShopDataForItemsBySKU[$ShopDataForVariationItem['SKU']];
            }
        } else {
            $ShopDataForItemsBySKU = array();
        }
        
        #echo print_m($this->renderableData, '$this->renderableData');
        #echo print_m($ShopDataForItemsBySKU, '$ShopDataForItemsBySKU');
        
        $ShopDataForItemsByLowerCaseSKU = array_change_key_case($ShopDataForItemsBySKU, CASE_LOWER);
        foreach ($this->renderableData as &$item) {
            if (isset($ShopDataForItemsByLowerCaseSKU[strtolower($item['SKU'])])) {
                $sLowerSKU = strtolower($item['SKU']);
                $item['ProductsID']   = $ShopDataForItemsByLowerCaseSKU[$sLowerSKU]['products_id'];
                $item['ShopQuantity'] = $ShopDataForItemsByLowerCaseSKU[$sLowerSKU]['ShopQuantity'];
                $item['ShopPrice']    = $ShopDataForItemsByLowerCaseSKU[$sLowerSKU]['ShopPrice'];
                $item['ShopTitle']    = $ShopDataForItemsByLowerCaseSKU[$sLowerSKU]['ShopTitle'];
                $item['ShopVarText']  = isset($ShopDataForItemsByLowerCaseSKU[$sLowerSKU]['ShopVarText'])
				                        ? $ShopDataForItemsByLowerCaseSKU[$sLowerSKU]['ShopVarText']
				                        : '&nbsp;';
                unset($sLowerSKU);
                /*if (    isset($ePIDsBySKU[$item['SKU']])
		     || isset($item['ePID'])) {
                     if (!isset($item['ePID'])) $item['ePID'] = $ePIDsBySKU[$item['SKU']];
                     if ('variations' == $item['ePID']) {
                       $item['ePID'] = '&mdash;';
                       $item['PrepareKind'] = ML_EBAY_LABEL_CATALOG_VARIATIONS;
                     } else if (isset($ePIDsBySKU[$item['SKU']]) && ('newproduct' == $ePIDsBySKU[$item['SKU']])) {
                       $item['PrepareKind'] = ML_EBAY_LABEL_APPLIED_CATALOG;
                       $item['ePID'] = '&mdash;';
                     } else if ('waiting_catalog' == $item['Status']) {
                       $item['PrepareKind'] = ML_EBAY_LABEL_APPLIED_CATALOG;
                       $item['ePID'] = '&mdash;';
                     } else {
                       $item['PrepareKind'] = ML_EBAY_LABEL_PREPARED_CATALOG;
                     }
                } else {
                     $item['PrepareKind'] = ML_EBAY_LABEL_PREPARED_NO_CATALOG;
                }*/
            } else {
                $item['ShopQuantity'] = $item['ShopPrice'] = $item['ShopTitle'] = '&mdash;';
                $item['ShopVarText']  = '&nbsp;';
                $item['ProductsID']   = 0;
            }
            /*if (isset ($item['Prepared'])) {
            //got data from the API
               switch ($item['Prepared']) {
                 case('matched'): 
                   $item['PrepareKind'] = ML_EBAY_LABEL_PRODUCT_MANUAL_MATCHED;
                   break; 
                 case('automatched'): 
                   $item['PrepareKind'] = ML_EBAY_LABEL_PRODUCT_AUTO_MATCHED;
                   break; 
                 case('applied'): 
                   $item['PrepareKind'] = ML_EBAY_LABEL_PRODUCT_APPLIED;
                   break; 
                 case('autoapplied'): 
                   $item['PrepareKind'] = ML_EBAY_LABEL_PRODUCT_AUTO_APPLIED;
                   break; 
                 default:
                   $item['PrepareKind'] = '&mdash;';
                   break; 
               } 
            }*/
        }
/*
$item['PrepareKind']
Automatisch gematcht ML_EBAY_LABEL_PRODUCT_AUTO_MATCHED
Manuell gematcht ML_EBAY_LABEL_PRODUCT_MANUAL_MATCHED
eBay Katalog-Vorschlag ML_EBAY_LABEL_PRODUCT_SUBMITTED
Eigene Daten 
(keine Katalogpflicht) ML_EBAY_LABEL_PREPARED_NO_CATALOG
*/
    }
	
	private function emptyStr2mdash($str) {
		return (empty($str) || (is_numeric($str) && ($str == 0))) ? '&mdash;' : $str;
	}
	
	protected function additionalHeaders() { }

	protected function additionalValues($item) { }

	private function renderDataGrid($id = '') {
				
		$priceBrutto = !(defined('PRICE_IS_BRUTTO') && (PRICE_IS_BRUTTO == 'false'));
		
		$html = '
			<table'.(($id != '') ? ' id="'.$id.'"' : '').' class="datagrid">
				<thead class="small"><tr>
					<td class="nowrap" style="width: 5px;"><input type="checkbox" id="selectAll"/><label for="selectAll">'.ML_LABEL_CHOICE.'</label></td>
					<td>'.ML_LABEL_SKU.' '.$this->sortByType('sku').'</td>
					<td>Master-SKU</td>
					<td>'.ML_LABEL_SHOP_TITLE.'</td>
					<td>'.ML_LABEL_EBAY_TITLE.' '.$this->sortByType('itemtitle').'</td>
					<td>'.ML_LABEL_EBAY_ITEM_ID.'</td>
					<td>'.($priceBrutto
						? ML_LABEL_SHOP_PRICE_BRUTTO
						: ML_LABEL_SHOP_PRICE_NETTO
					).' / eBay '.$this->sortByType('price').'</td>
					<td>'.ML_STOCK_SHOP_STOCK_EBAY.'<br />'.ML_LAST_SYNC.'</td>
					<td>'.ML_LABEL_EBAY_LISTINGTIME.' '.$this->sortByType('dateadded').'</td>
					<td>'.ML_GENERIC_STATUS.'</td>
				</tr></thead>
				<tbody>
		';
					//<td>'.ML_LABEL_EBAY_EPID.'</td>
					//<td>'.ML_EBAY_LABEL_PREPARE_KIND.'</td>

		$oddEven = false;
        #$this->getShopDataForItems();
		foreach ($this->renderableData as $item) {
			$details = htmlspecialchars(str_replace('"', '\\"', serialize(array(
			 	'SKU' => $item['SKU'],
			 	'Price' => $item['Price'],
			 	'Currency' => $item['Currency'],
			))));
			if (0 != $item['ShopPrice']) {
				$this->simplePrice->setPriceAndCurrency($item['ShopPrice'], $item['Currency']);
				if ($priceBrutto) {
					//$this->simplePrice->addTax(10);
					$this->simplePrice->addTaxByPID($item['ProductsID']);
				}
			}

            $renderedShopPrice = (0 != $item['ShopPrice']) ? $this->simplePrice->format() : '&mdash;';
            if (array_key_exists('OldPrice', $item)) {
                $item['StrikePrice'] = $item['OldPrice'];
            } else if (array_key_exists('ManufacturersPrice', $item)) {
                $item['StrikePrice'] = $item['ManufacturersPrice'];
            }
            if (array_key_exists('StrikePrice', $item)) {
                $renderedStrikePrice = '&nbsp;<span style="font-size:80%;color:red">(<span style="text-decoration:line-through">'.$this->simplePrice->setPriceAndCurrency($item['StrikePrice'], $item['Currency'])->format().'</span>)</span>';
            } else {
                $renderedStrikePrice = '';
            }

            $addStyle = ('&mdash;' == $item['ShopTitle'])?'style="color:#900;"':'';
            $icon = (('ml' == $item['listedBy'])?'&nbsp;<img src="'.DIR_MAGNALISTER_WS_IMAGES.'/magnalister_11px_icon_color.png" width=11 height=11 />':'');
			$html .= '
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'" '.$addStyle.'>
					<td>'.(('active' == $item['Status']) ? '<input type="checkbox" name="ItemIDs[]" value="'.$item['ItemID'].'">
						<input type="hidden" name="details['.$item['ItemID'].']" value="'.$details.'">' : '<input type="checkbox" name="dummy" disabled="disabled"> ')
                        .$icon.'</td>
					<td>'.fixHTMLUTF8Entities($item['SKU'], ENT_COMPAT).'</td>
					<td>'.fixHTMLUTF8Entities($item['MasterSKU'], ENT_COMPAT).'</td>
					<td title="'.fixHTMLUTF8Entities($item['ShopTitle'], ENT_COMPAT).'">'.$item['ShopTitle'].'<br /><span class="small">'.$item['ShopVarText'].'</span></td>
					<td title="'.fixHTMLUTF8Entities($item['ItemTitle'], ENT_COMPAT).'">'.$item['ItemTitleShort'].'<br /><span class="small">'.$item['VariationAttributesText'].'</span></td>
					<td>'.(!empty($item['ItemID']) ? '<a href="'.$item['SiteUrl'].'?ViewItem&item='.$item['ItemID'].'" target="_blank">'.$item['ItemID'].'</a>' : '&mdash;').'</td>';
/*
					<td>';
					if (!empty($item['ePID']) && !empty($item['productWebUrl']))  {
                                            $html .= '<a href="'.$item['productWebUrl'].'" target="_blank">'.$item['ePID'].'</a>' ;
					} else if (!empty($item['ePID'])) {
                                            $html .= $item['ePID'];
					} else {
                                            $html .= '&mdash;';
                                        }
					$html .= '</td>
					<td>'.$item['PrepareKind'].'</td>
*/
					$html .= '
					<td>'.$renderedShopPrice.' / '.$this->simplePrice->setPriceAndCurrency($item['Price'], $item['Currency'])->format().$renderedStrikePrice.'</td>
					<td>'.$item['ShopQuantity'].' / '.$item['Quantity'].'<br />'.('&mdash;' == $item['LastSync']? '&mdash;' : date("d.m.Y", $item['LastSync']).' &nbsp;&nbsp;<span class="small">'.date("H:i", $item['LastSync'])).'</span></td>
					<td>'.date("d.m.Y", $item['DateAdded']).' &nbsp;&nbsp;<span class="small">'.date("H:i", $item['DateAdded']).'</span><br />'.('&mdash;' == $item['DateEnd']? '&mdash;' : date("d.m.Y", $item['DateEnd']).' &nbsp;&nbsp;<span class="small">'.date("H:i", $item['DateEnd']).'</span>').'</td>
					<td>';
					if ('active' == $item['Status']) {
						$html .= ML_GENERIC_INVENTORY_STATUS_ACTIVE;
					} else if ('pending' == $item['Status']) {
						if (!empty($item['ItemID'])) {
							$html .= ML_EBAY_INVENTORY_STATUS_PENDING_UPDATE;
						} else {
							$html .= ML_EBAY_INVENTORY_STATUS_PENDING_UPLOAD;
						}
					} else if ('waiting_catalog' == $item['Status']) {
						$html .= ML_EBAY_INVENTORY_STATUS_WAITING_CATALOG.'<br />'
						      .  ML_EBAY_EST_UNTIL .' '.$item['estimatedTime'];
					}
					/*<td title = "';
					if ('active' == $item['Status']) {
						$html .= ML_AMAZON_LABEL_IN_INVENTORY.'">&nbsp;<img src="'.DIR_MAGNALISTER_WS_IMAGES.'status/green_dot.png" alt="'.ML_AMAZON_LABEL_IN_INVENTORY.'"/></td>';
					} else if ('pending' == $item['Status']) {
						if (!empty($item['ItemID'])) {
							$html .= ML_AMAZON_LABEL_EDIT_WAIT.'">&nbsp;<img src="'.DIR_MAGNALISTER_WS_IMAGES.'status/blue_dot.png" alt="'.ML_AMAZON_LABEL_EDIT_WAIT.'"/></td>';
						} else {
							$html .= ML_AMAZON_LABEL_ADD_WAIT.'">&nbsp;<img src="'.DIR_MAGNALISTER_WS_IMAGES.'status/grey_dot.png" alt="'.ML_AMAZON_LABEL_ADD_WAIT.'"/></td>';
						}
					}*/
			$html .= '</td>
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
		if (isset($_GET['page']) && ctype_digit($_GET['page']) && (1 <= (int)$_GET['page']) && ((int)$_GET['page'] <= $pages)) {
			$currentPage = (int)$_GET['page'];
		}
		
		$itemsPerPageSelect = array(50, 100, 250, 500, 1000, 2500);
        $chooser = '
        		<select id="itemsPerPage" name="itemsPerPage" class="">'."\n";
        foreach ($itemsPerPageSelect as $chc) {
        	$chcselected = ($this->settings['itemLimit'] == $chc)
        		? 'selected' : '';
        	$chooser .= '<option value="'.$chc.'" '.$chcselected.'>'.$chc.'</option>';
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

		if (!empty($this->newErrors)) {
			$html .= '<p class="noticeBox">'.ML_EBAY_NEW_ERRORS_OCCURED_CHECK_ERRORLOG.'</p>';
		}
		if (!empty($this->pendingItems)) {
			foreach ($this->pendingItems as $sKey => $aPendingItems) {
				if (!empty($aPendingItems['itemsCount'])) {
					$html .= '<p class="successBoxBlue">'.constant('ML_EBAY_N_PENDING_UPDATES_TITLE_'.strtoupper($sKey))
						.sprintf(ML_EBAY_N_PENDING_UPDATES_ESTIMATED_TIME_M, $aPendingItems['itemsCount'], $aPendingItems['estimatedWaitingTime'])
						.'</p>';
				}
			}
		}
		if (!empty($this->renderableData)) {
			$html .= $this->renderDataGrid('ebayinventory');
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
		$('#ebayinventory input[type="checkbox"]:not([disabled])').each(function() {
			$(this).attr('checked', state);
		});
	});
	$('#itemsPerPage').change(function() {
		window.location.href = '<?php echo toURL($tmpURL, true);?>&itemsPerPage='+$(this).val();
	});
});
/*]]>*/</script>
<?php
		$html .= ob_get_contents();	
		ob_end_clean();
		
		return $html;
	}
	
	protected function getRightActionButton() { return ''; }
	
	public function renderActionBox() {
		global $_modules;
		$left = (!empty($this->renderableData) ? 
			'<input type="button" class="ml-button" value="'.ML_BUTTON_LABEL_DELETE.'" id="listingDelete" name="listing[delete]"/>' : 
			''
		);
		
		$right = $this->getRightActionButton();

		ob_start();?>
<script type="text/javascript">/*<![CDATA[*/
$(document).ready(function() {
	$('#listingDelete').click(function() {
		if (($('#ebayinventory input[type="checkbox"]:checked').length > 0) &&
			confirm(unescape(<?php echo "'".html2url(sprintf(ML_GENERIC_DELETE_LISTINGS, $_modules[$this->magnasession['currentPlatform']]['title']))."'"; ?>))
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
		$html = '<form action="'.toUrl($this->url).'" id="ebayInventoryView" method="post">';
		$this->initInventoryView();
		$html .= $this->renderInventoryTable();
		return $html.$this->renderActionBox().'
			</form>
			<script type="text/javascript">/*<![CDATA[*/
				$(document).ready(function() {
					$(\'#ebayInventoryView\').submit(function () {
						jQuery.blockUI(blockUILoading);
					});
				});
			/*]]>*/</script>';
	}
	
}
