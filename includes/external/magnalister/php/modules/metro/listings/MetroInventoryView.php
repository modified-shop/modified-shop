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

class MetroInventoryView extends MagnaCompatibleInventoryView {

    public function __construct() {
        parent::__construct();
        $this->saveDeletedLocally = false;
    }

    /*
     * overwritten by eBay-like function
     * to get shop prices and quantities
     * + some performance by doing queries for the entire item list instead of for each item
     */
    public function prepareInventoryData() {
        global $magnaConfig;
        // fetch shop prices and quantities, like in eBay
        $aGetInventoryResult = $this->getInventory();
        if (($aGetInventoryResult === false) || empty($aGetInventoryResult['DATA'])) {
            return;
        }
        $this->renderableData = $aGetInventoryResult['DATA'];
        $language = $magnaConfig['db'][$this->magnasession['mpID']]['metro.lang'];
        $SKUarr = array();
        $SKUlist = '';
        foreach ($this->renderableData as $item) {
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
                $ShopDataForSimpleItems2 = MagnaDB::gi()->fetchArray('
	                    SELECT DISTINCT p.products_id SKU, p.products_id products_id, 
	                           CAST(p.products_quantity AS SIGNED) ShopQuantity, p.products_price ShopPrice,
	                           pd.products_name ShopTitle
	                      FROM '.TABLE_PRODUCTS.' p, '.TABLE_PRODUCTS_DESCRIPTION.' pd
	                     WHERE p.products_id=pd.products_id
	                           AND pd.language_id='.$language.'
	                           AND p.products_id IN ('.$SKUlist.')
	                ');
                if (!empty($ShopDataForSimpleItems2)) {
                    $ShopDataForSimpleItems = array_merge($ShopDataForSimpleItems, $ShopDataForSimpleItems2);
                }
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
                    if ((int)magnaSKU2pID(empty($item['MasterSKU']) ? $item['SKU'] : $item['MasterSKU']) !== 0) {
                        $aSkusWithExistingMaster[] = MagnaDB::gi()->escape($item['SKU']);
                    }
                }
                if (empty($aSkusWithExistingMaster)) {
                    $ShopDataForVariationItems = array();
                } else {
                    if (('utf8' == $character_set_system) && ('utf8' != $character_set_client)) {
                        arrayEntitiesToLatin1($aSkusWithExistingMaster);
                    }
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
                $ShopDataForItemsBySKU[$ShopDataForVariationItem['MasterSKU']] = magnaPID2SKU($ShopDataForItemsBySKU['products_id']);
                $ShopDataForItemsBySKU[$ShopDataForVariationItem['SKU']] = $ShopDataForVariationItem;
                unset($ShopDataForItemsBySKU[$ShopDataForVariationItem['SKU']]['SKU']);
                $ShopDataForItemsBySKU[$ShopDataForVariationItem['SKUDeprecated']] = &$ShopDataForItemsBySKU[$ShopDataForVariationItem['SKU']];
            }
        } else {
            $ShopDataForItemsBySKU = array();
        }

        #echo print_m($this->renderableData, '$this->renderableData');
        #echo print_m($ShopDataForItemsBySKU, '$ShopDataForItemsBySKU');
        #echo print_m($ShopDataForVariationItems, '$ShopDataForVariationItems');

        foreach ($this->renderableData as &$item) {
            $itemProductData = json_decode($item['ProductData'], true);
            $item['MarketplaceTitle'] = $itemProductData[0]['Title'];
            $item['MarketplaceTitleShort'] = (mb_strlen($item['MarketplaceTitle'], 'UTF-8') > $this->settings['maxTitleChars'] + 2)
                ? (fixHTMLUTF8Entities(mb_substr($item['MarketplaceTitle'], 0, $this->settings['maxTitleChars'], 'UTF-8')).'&hellip;')
                : fixHTMLUTF8Entities($item['MarketplaceTitle']);

            if (isset($ShopDataForItemsBySKU[$item['SKU']])) {
                // Pull Prepare Data
                if (getDBConfigValue('general.keytype', '0') == 'artNr') {
                    $checkSKU = $item['SKU'];
                    if (array_key_exists('MasterSKU', $item)) {
                        $checkSKU = $item['MasterSKU'];
                    }
                    $sPropertiesWhere = "products_model = '".MagnaDB::gi()->escape($checkSKU)."'";
                } else {
                    $sPropertiesWhere = "products_id = '".$ShopDataForItemsBySKU[$item['SKU']]['products_id']."'";
                }
                $prepareData = MagnaDB::gi()->fetchRow(eecho("
                    SELECT *
                      FROM ".TABLE_MAGNA_METRO_PREPARE."
                     WHERE     ".$sPropertiesWhere."
                           AND mpID = '".$this->mpID."'
                ", false));

                $item['ProductsID'] = $ShopDataForItemsBySKU[$item['SKU']]['products_id'];
                $item['ShopQuantity'] = $ShopDataForItemsBySKU[$item['SKU']]['ShopQuantity'];
                $item['ShopPrice'] = $ShopDataForItemsBySKU[$item['SKU']]['ShopPrice'];

                // Set SimplePrice to current Product
                $this->simplePrice->setFinalPriceFromDB($item['ProductsID'], $this->mpID);
                $item['ShopNetPrice'] = $this->simplePrice->removeTaxByPID($item['ProductsID'])->getPrice();
                $item['Tax'] = SimplePrice::getTaxByPID($item['ProductsID']);

                //Shipping costs (Gross + Net)
                $shippingPriceConfigValue = getDBConfigValue('metro.shippingprofile.cost', $this->mpID);
                if (!empty($prepareData['ShippingProfile']) && array_key_exists($prepareData['ShippingProfile'], $shippingPriceConfigValue)) {
                    $shippingProfilePrice = $shippingPriceConfigValue[$prepareData['ShippingProfile']];
                    $item['ShippingCost'] = (float)$shippingProfilePrice;
                    $item['NetShippingCost'] = round(($item['ShippingCost'] / ((100 + (float)$item['Tax']) / 100)), 2);
                } else {
                    $item['ShippingCost'] = $item['NetShippingCost'] = null;
                }

                $item['Title'] = $ShopDataForItemsBySKU[$item['SKU']]['ShopTitle'];
                $item['TitleShort'] = (mb_strlen($item['Title'], 'UTF-8') > $this->settings['maxTitleChars'] + 2)
                    ? (fixHTMLUTF8Entities(mb_substr($item['Title'], 0, $this->settings['maxTitleChars'], 'UTF-8')).'&hellip;')
                    : (fixHTMLUTF8Entities($item['Title']));

                $item['ShopVarText'] = isset($ShopDataForItemsBySKU[$item['SKU']]['ShopVarText'])
                    ? $ShopDataForItemsBySKU[$item['SKU']]['ShopVarText']
                    : '&nbsp;';
            } else {
                $item['ShopQuantity'] = $item['ShopPrice'] = $item['Title'] = $item['TitleShort'] = '&mdash;';
                $item['ShopVarText'] = '&nbsp;';
                $item['ProductsID'] = 0;
            }
        }
    }

    protected function getFields() {
        return array(
            'SKU' => array(
                'Label' => ML_LABEL_SKU,
                'Sorter' => 'sku',
                'Getter' => 'getSKU', /** @uses getSKU */
                'Field' => null
            ),
            'ShopTitle' => array(
                'Label' => ML_LABEL_SHOP_TITLE,
                'Sorter' => null,
                'Getter' => 'getTitle', /** @uses getTitle */
                'Field' => null,
            ),
            'Title' => array(
                'Label' => ML_METRO_LABEL_TITLE,
                'Sorter' => 'marketplacetitle',
                'Getter' => 'getMpTitle', /** @uses getMpTitle */
                'Field' => null,
            ),
            'ListingId' => array(
                'Label' => ML_METRO_LISTING_ID,
                'Sorter' => null,
                'Getter' => 'getLinkedListingId', /** @uses getLinkedListingId */
                'Field' => null,
            ),
            'Price' => array(
                'Label' => ML_METRO_PRICE_SHOP_METRO,
                'Sorter' => 'NetPrice',
                'Getter' => 'getItemPrice', /** @uses getItemPrice */
                'Field' => null
            ),
            'Quantity' => array(
                'Label' => ML_METRO_STOCK_SHOP_METRO,
                'Sorter' => 'quantity',
                'Getter' => 'getItemQuantity', /** @uses getItemQuantity */
                'Field' => null,
            ),
            'DateAdded' => array(
                'Label' => ML_GENERIC_CHECKINDATE,
                'Sorter' => 'dateadded',
                'Getter' => 'getItemDateAdded', /** @uses getItemDateAdded */
                'Field' => null
            ),
            'DateUpdated' => array(
                'Label' => ML_LAST_SYNC,
                'Sorter' => 'lastsync',
                'Getter' => 'getItemLastSync', /** @uses getItemLastSync */
                'Field' => null
            ),
            'Status' => array(
                'Label' => ML_GENERIC_STATUS,
                'Sorter' => null,
                'Getter' => 'getItemStatus', /** @uses getItemStatus */
                'Field' => null
            ),
        );
    }

    protected function getSKU($item) {
        return '<td>'.fixHTMLUTF8Entities($item['SKU'], ENT_COMPAT).'</td>';
    }

    protected function getMpTitle($item) {
        return '<td title="'.fixHTMLUTF8Entities($item['MarketplaceTitle'], ENT_COMPAT).'">'.$item['MarketplaceTitleShort'].'</td>';
    }

    protected function getLinkedListingId($item) {
        $blIsLinked = false;
        while (!empty($item['Data'])) {
            $aData = json_decode($item['Data'], true);
            if (!is_array($aData)) break;
            if (!isset($aData['Url'])) break;
            $blIsLinked = true;
            break;
        }
        if ($blIsLinked) {
            return '<td title="'.$item['MetroId'].'"><a href="'.$aData['Url'].'" target="_blank" >'.$item['MetroId'].'</a></td>';
        } else {
            return '<td title="'.$item['MetroId'].'">'.$item['MetroId'].'</td>';
        }
    }

    protected function getItemPrice($item) {
        if ($item['ShopPrice'] > 0) {
            $sShopPrice = $this->simplePrice->setPriceAndCurrency($item['ShopPrice'], $this->mpCurrency)->addTaxByPID($item['ProductsID'])->format();
        } else {
            $sShopPrice = '&mdash;';
        }
        $item['Currency'] = isset($item['Currency']) ? $item['Currency'] : $this->mpCurrency;

        $shippingCost = '';
        $shippingNetCost = '';
        if ($item['ShippingCost'] !== null && $item['NetShippingCost'] !== null) {
            $shippingCost = $this->simplePrice->setPriceAndCurrency($item['ShippingCost'], $item['Currency'])->format();
            $shippingNetCost = $this->simplePrice->setPriceAndCurrency($item['NetShippingCost'], $item['Currency'])->format();
        }

        $sMetroPrice = (isset($item['Price']) && 0 != $item['Price'])
            ? $this->simplePrice->setPriceAndCurrency($item['Price'], $item['Currency'])->format()
                .'<span class="small">('.ML_LABEL_INCL.' '.$shippingCost.' '.ML_GENERIC_SHIPPING.')</span>'
            : '&mdash;';

        $renderedShopNetPrice = (isset($item['ShopNetPrice']) && 0 != $item['ShopNetPrice'])
            ? $this->simplePrice->setPriceAndCurrency($item['ShopNetPrice'], $item['Currency'])->format()
            : '&mdash;';
        $renderedMpNetPrice = ((isset($item['NetPrice']) && 0 != $item['NetPrice'])
            ? $this->simplePrice->setPriceAndCurrency($item['NetPrice'], $item['Currency'])->format()
                .'<span class="small">('.ML_LABEL_INCL.' '.$shippingNetCost.' '.ML_GENERIC_SHIPPING.')</span>'
            : '&mdash;'
        );

        return '<td>'.$sShopPrice.' / '.$sMetroPrice.'<br>'.$renderedShopNetPrice.' / '.$renderedMpNetPrice.'</td>';
    }

    protected function getItemQuantity($item) {
        return '<td>'.$item['ShopQuantity'].' / '.$item['Quantity'].'</td>';
    }

    protected function getItemLastSync($item) {
        if (isset($item['DateUpdated']) && $item['DateUpdated'] != '0000-00-00 00:00:00') {
            $time = strtotime($item['DateUpdated']);
            return '<td>'.date("d.m.Y", $time).' &nbsp;&nbsp;<span class="small">'.date("H:i", $time).'</span>'.'</td>';
        }

        return '<td>&mdash;</td>';
    }

    protected function getItemStatus($item) {
        if ('active' == $item['StatusProduct'] && 'active' == $item['StatusOffer']) {
            return '<td>'.ML_GENERIC_INVENTORY_STATUS_ACTIVE.'</td>';
        } elseif ('waiting' == $item['StatusProduct'] && ('waiting' == $item['StatusOffer'] || 'creating' == $item['StatusOffer'])) {
            return '<td>'.ML_GENERIC_STATUS_PRODUCT_IS_CREATED.'</td>';
        } elseif ('waiting' == $item['StatusProduct'] && 'active' == $item['StatusOffer']) {
            return '<td>'.ML_GENERIC_STATUS_PRODUCT_IS_UPDATED.'</td>';
        } elseif ('active' == $item['StatusProduct'] && 'waiting' == $item['StatusOffer']) {
            return '<td>'.ML_GENERIC_STATUS_OFFER_IS_UPDATED.'</td>';
        } elseif ('active' == $item['StatusProduct'] && 'creating' == $item['StatusOffer']) {
            return '<td>'.ML_GENERIC_STATUS_OFFER_IS_CREATED.'</td>';
        } elseif ('pending_delete' == $item['StatusOffer']) {
            return '<td>'.ML_METRO_STATUS_PRODUCT_IS_PENDING_DELETE.'</td>';
        } else {
            return '<td>&mdash;</td>';
        }
    }
}
