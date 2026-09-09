<?php
/*
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
 * (c) 2010 - 2026 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/listings/MagnaCompatibleInventoryView.php');

class TemuInventoryView extends MagnaCompatibleInventoryView {

    /** @var bool True when the API signalled that an inventory import is queued. */
    protected $bImportPending = false;

    public function __construct($settings = array()) {
        parent::__construct($settings);
        $this->saveDeletedLocally = false;
    }

    /**
     * Otto-style batch data preparation.
     * Fetches inventory from API, enriches with shop prices/quantities/titles via batch SQL.
     */
    public function prepareInventoryData() {
        global $magnaConfig;

        $aGetInventoryResult = $this->getInventory();

        // Check import-pending flag from API
        if ($aGetInventoryResult !== false && !empty($aGetInventoryResult['IMPORT_PENDING'])) {
            $this->bImportPending = true;
        }

        if (($aGetInventoryResult === false) || empty($aGetInventoryResult['DATA'])) {
            return;
        }

        $this->renderableData = $aGetInventoryResult['DATA'];
        $language = $magnaConfig['db'][$this->magnasession['mpID']]['temu.lang'];

        // Collect unique SKUs for batch query
        $SKUarr = array();
        foreach ($this->renderableData as $item) {
            $SKUarr[] = $item['SKU'];
        }
        $SKUarr = array_unique($SKUarr);

        // Handle character encoding (utf8mb3/mb4 normalization)
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

        // Build escaped SKU list for SQL
        $SKUlist = '';
        foreach ($SKUarr as $currentSKU) {
            $SKUlist .= ", '".MagnaDB::gi()->escape($currentSKU)."'";
        }
        $SKUlist = ltrim($SKUlist, ', ');

        if (!empty($SKUlist)) {
            // Batch query: simple products
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

            // Batch query: variation products
            if (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties') {
                if ('artNr' == getDBConfigValue('general.keytype', '0')) {
                    $selectSku = "CONCAT(p.products_model, '-', ppc.combi_model)";
                    $ShopDataForVariationItems = MagnaDB::gi()->fetchArray("
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
                               AND ".$selectSku." IN (".$SKUlist.")");
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
                    $ShopDataForVariationItems = MagnaDB::gi()->fetchArray('
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
                    ');
                }
            }

            // Merge into lookup by SKU
            $ShopDataForItemsBySKU = array();
            foreach ($ShopDataForSimpleItems as $ShopDataForSimpleItem) {
                $ShopDataForItemsBySKU[$ShopDataForSimpleItem['SKU']] = $ShopDataForSimpleItem;
                unset($ShopDataForItemsBySKU[$ShopDataForSimpleItem['SKU']]['SKU']);
                $ShopDataForItemsBySKU[$ShopDataForSimpleItem['SKU']]['ShopVarText'] = '';
            }
            foreach ($ShopDataForVariationItems as &$ShopDataForVariationItem) {
                if (('utf8' == $character_set_system) && ('utf8' != $character_set_client)) {
                    $ShopDataForVariationItem['SKU'] = utf8_encode($ShopDataForVariationItem['SKU']);
                }
                $ShopDataForItemsBySKU[$ShopDataForVariationItem['SKU']] = $ShopDataForVariationItem;
                unset($ShopDataForItemsBySKU[$ShopDataForVariationItem['SKU']]['SKU']);
                $ShopDataForItemsBySKU[$ShopDataForVariationItem['SKUDeprecated']] = &$ShopDataForItemsBySKU[$ShopDataForVariationItem['SKU']];
            }
        } else {
            $ShopDataForItemsBySKU = array();
        }

        // Enrich each inventory item with shop data
        foreach ($this->renderableData as &$item) {
            // Parse marketplace title from ProductData JSON
            $itemProductData = json_decode($item['ProductData'], true);
            if (is_array($itemProductData) && isset($itemProductData[0]['Title'])) {
                $item['MarketplaceTitle'] = $itemProductData[0]['Title'];
            } elseif (is_array($itemProductData) && isset($itemProductData['productName'])) {
                $item['MarketplaceTitle'] = $itemProductData['productName'];
            } else {
                $item['MarketplaceTitle'] = '---';
            }
            $item['MarketplaceTitleShort'] = (mb_strlen($item['MarketplaceTitle'], 'UTF-8') > $this->settings['maxTitleChars'] + 2)
                ? (fixHTMLUTF8Entities(mb_substr($item['MarketplaceTitle'], 0, $this->settings['maxTitleChars'], 'UTF-8')).'&hellip;')
                : fixHTMLUTF8Entities($item['MarketplaceTitle']);

            // Parse timestamps
            $item['DateAdded'] = isset($item['DateAdded']) ? strtotime($item['DateAdded']) : 0;
            $item['LastSync'] = isset($item['DateUpdated']) ? strtotime($item['DateUpdated']) : 0;

            // Enrich with shop data
            if (isset($ShopDataForItemsBySKU[$item['SKU']])) {
                $item['ProductsID'] = $ShopDataForItemsBySKU[$item['SKU']]['products_id'];
                $item['ShopQuantity'] = $ShopDataForItemsBySKU[$item['SKU']]['ShopQuantity'];
                $item['ShopPrice'] = $ShopDataForItemsBySKU[$item['SKU']]['ShopPrice'];
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

    /**
     * Override to show import-pending message when API signals inventory import is queued.
     */
    public function renderInventoryTable() {
        if (empty($this->renderableData)) {
            $this->prepareInventoryData();
        }

        if ($this->bImportPending && empty($this->renderableData) && empty($this->search)) {
            return '<table class="magnaframe"><tbody><tr><td>'.ML_TEMU_INVENTORY_IMPORT_PENDING.'</td></tr></tbody></table>';
        }

        return parent::renderInventoryTable();
    }

    protected function getFields() {
        return array(
            'SKU' => array(
                'Label' => ML_LABEL_SKU,
                'Sorter' => 'sku',
                'Getter' => 'getSKU',
                'Field' => null,
            ),
            'ShopTitle' => array(
                'Label' => ML_LABEL_SHOP_TITLE,
                'Sorter' => null,
                'Getter' => 'getTitle',
                'Field' => null,
            ),
            'Price' => array(
                'Label' => ML_TEMU_PRICE_SHOP_TEMU,
                'Sorter' => 'price',
                'Getter' => 'getItemPrice',
                'Field' => null,
            ),
            'Quantity' => array(
                'Label' => ML_STOCK_SHOP_STOCK_TEMU.'<br />'.ML_LAST_SYNC,
                'Sorter' => null,
                'Getter' => 'getItemQuantity',
                'Field' => null,
            ),
            'DateAdded' => array(
                'Label' => ML_GENERIC_CHECKINDATE,
                'Sorter' => 'dateadded',
                'Getter' => 'getItemDateAdded',
                'Field' => null,
            ),
            'Status' => array(
                'Label' => ML_GENERIC_STATUS,
                'Sorter' => null,
                'Getter' => 'getItemStatus',
                'Field' => null,
            ),
            'Note' => array(
                'Label' => ML_TEMU_NOTE_LABEL,
                'Sorter' => null,
                'Getter' => 'getItemNote',
                'Field' => null,
            ),
        );
    }

    protected function getSKU($item) {
        if ($item['ProductsID'] > 0) {
            $addStyle = ($item['Title'] === '&mdash;') ? 'style="color:#900;"' : '';
            return '<td><a '.$addStyle.' class="ml-js-noBlockUi" href="categories.php?pID='.$item['ProductsID'].'&action=new_product" target="_blank" title="'.ML_LABEL_EDIT.'">'.fixHTMLUTF8Entities($item['SKU'], ENT_COMPAT).'</a></td>';
        }
        return '<td style="color:#900;">'.fixHTMLUTF8Entities($item['SKU'], ENT_COMPAT).'</td>';
    }

    /**
     * FEAT-015: render the price-review badge for a row (parallel to the product
     * status). Returns '' for price_none / missing. The localized label is HTML
     * (may contain entities) and is NOT escaped; the reject reason is untrusted
     * and IS escaped into the title attribute.
     *
     * @param array $item
     * @return string
     */
    protected function getPriceBadge($item) {
        $status = isset($item['PriceDisplayStatus']) ? $item['PriceDisplayStatus'] : '';
        if ($status === '' || $status === 'price_none') {
            return '';
        }

        $label = $this->localizeApiLabel(isset($item['PriceDisplayLabel']) ? $item['PriceDisplayLabel'] : '');

        if ($status === 'price_pending') {
            $suffix = '';
            if (isset($item['PendingBasePrice']) && $item['PendingBasePrice'] !== null && $item['PendingBasePrice'] !== ''
                && isset($item['PendingCurrency']) && $item['PendingCurrency'] !== null && $item['PendingCurrency'] !== '') {
                $suffix = ' &mdash; '.$this->simplePrice->setPriceAndCurrency($item['PendingBasePrice'], $item['PendingCurrency'])->format();
            }
            return '<div class="ml-temu-price-badge" style="margin-top:3px;font-size:11px;color:#e67e00;">&#9203; '.$label.$suffix.'</div>';
        }

        if ($status === 'price_rejected') {
            $reason = (isset($item['PriceRejectReason']) && $item['PriceRejectReason'] !== null && $item['PriceRejectReason'] !== '')
                ? $item['PriceRejectReason'] : '';
            $title = ($reason !== '') ? ' title="'.htmlspecialchars($reason, ENT_QUOTES).'"' : '';
            return '<div class="ml-temu-price-badge" style="margin-top:3px;font-size:11px;color:#e31e1c;"'.$title.'>&#9940; '.$label.'</div>';
        }

        return '';
    }

    /**
     * Two-line price column, mirroring MetroInventoryView::getItemPrice():
     *
     *     <shop gross> / &mdash;
     *     <shop net>   / <temu net>  [price badge]
     *
     * Temu is a NET marketplace: the API returns only `Price`, which IS the net
     * BasePrice we upload — there is no marketplace gross price, so that cell stays a
     * dash instead of showing a figure Temu never confirmed. METRO's "(inkl. Versand)"
     * hint has no Temu equivalent either: shipping lives in a marketplace-side template
     * and only its opaque CostTemplateId (LFT-…) is known here.
     *
     * addTaxByPID() mutates the shared SimplePrice, so every cell re-sets the price
     * before formatting.
     *
     * @param array $item
     * @return string
     */
    protected function getItemPrice($item) {
        $item['Currency'] = isset($item['Currency']) ? $item['Currency'] : $this->mpCurrency;

        if ($item['ShopPrice'] > 0) {
            // ShopPrice is products_price (net in the shop DB) — the value we upload/sync.
            $sShopNetPrice = $this->simplePrice->setPriceAndCurrency($item['ShopPrice'], $this->mpCurrency)->format();
            $sShopGrossPrice = ($item['ProductsID'] > 0)
                ? $this->simplePrice->setPriceAndCurrency($item['ShopPrice'], $this->mpCurrency)
                    ->addTaxByPID($item['ProductsID'])->format()
                : '&mdash;';
        } else {
            $sShopNetPrice = $sShopGrossPrice = '&mdash;';
        }

        $sTemuNetPrice = (isset($item['Price']) && (0 != $item['Price']))
            ? $this->simplePrice->setPriceAndCurrency($item['Price'], $item['Currency'])->format()
            : '&mdash;';

        return '<td>'.$sShopGrossPrice.' / &mdash;'
              .'<br>'.$sShopNetPrice.' / '.$sTemuNetPrice.$this->getPriceBadge($item).'</td>';
    }

    protected function getItemQuantity($item) {
        $sLastSync = ($item['LastSync'] > 0)
            ? date("d.m.Y", $item['LastSync']).' &nbsp;&nbsp;<span class="small">'.date("H:i", $item['LastSync']).'</span>'
            : '&mdash;';
        return '<td>'.$item['ShopQuantity'].' / '.$item['Quantity'].'<br />'.$sLastSync.'</td>';
    }

    /**
     * Override required: prepareInventoryData() already converts DateAdded to a Unix timestamp,
     * so the parent's strtotime() call would produce wrong results on an integer.
     */
    protected function getItemDateAdded($item) {
        return '<td>'.date("d.m.Y", $item['DateAdded']).' &nbsp;&nbsp;<span class="small">'.date("H:i", $item['DateAdded']).'</span></td>';
    }

    /**
     * Localize an API-provided label of the form "Human Text\n\n[i18n_key]".
     * Resolves the constant ML_TEMU_<UPPER(key)> and falls back to the human
     * text (bracket stripped) when the constant is not defined. Labels with no
     * bracket are returned trimmed and unchanged. Result is HTML (values may
     * contain entities) — do NOT html-escape the return value.
     *
     * @param string $label
     * @return string
     */
    protected function localizeApiLabel($label) {
        if (!is_string($label) || $label === '') {
            return '';
        }
        if (preg_match('/^(.*?)\s*\[([a-z0-9_]+)\]\s*$/s', $label, $aMatch)) {
            $sText  = trim($aMatch[1]);
            $sConst = 'ML_TEMU_'.strtoupper($aMatch[2]);
            if (defined($sConst)) {
                $sTranslated = constant($sConst);
                if ($sTranslated !== '') {
                    return $sTranslated;
                }
            }
            return $sText;
        }
        return trim($label);
    }

    protected function getItemStatus($item) {
        $displayLabel = isset($item['DisplayLabel']) ? $item['DisplayLabel'] : '';
        if ($displayLabel !== '') {
            return '<td>'.$this->localizeApiLabel($displayLabel).'</td>';
        }
        $status = isset($item['Status']) ? $item['Status'] : '';
        $statusMap = array(
            'Active'     => ML_TEMU_STATUS_ACTIVE,
            'InActive'   => ML_TEMU_STATUS_INACTIVE,
            'Incomplete' => ML_TEMU_STATUS_INCOMPLETE,
            'Draft'      => ML_TEMU_STATUS_DRAFT,
            'Deleted'    => ML_TEMU_STATUS_DELETED,
        );
        if (isset($statusMap[$status])) {
            return '<td>'.$statusMap[$status].'</td>';
        }
        return '<td>'.htmlspecialchars($status).'</td>';
    }

    protected function getItemNote($item) {
        $subStatus = isset($item['SubStatus']) ? $item['SubStatus'] : '';
        $noteMap = array(
            'ACTIVE_AT_RISK'           => ML_TEMU_NOTE_ACTIVE_AT_RISK,
            'CLOSE'                    => ML_TEMU_NOTE_CLOSE,
            'BLOCK'                    => ML_TEMU_NOTE_BLOCK,
            'OUT_OF_STOCK'             => ML_TEMU_NOTE_OUT_OF_STOCK,
            'PRICING_UNDER_ASSESSMENT' => ML_TEMU_NOTE_PRICING_UNDER_ASSESSMENT,
            'AUDIT_IN_PROCESS'         => ML_TEMU_NOTE_AUDIT_IN_PROCESS,
            'PRICING_FAILURE'          => ML_TEMU_NOTE_PRICING_FAILURE,
            'PRODUCT_TO_BE_COMPLETE'   => ML_TEMU_NOTE_PRODUCT_TO_BE_COMPLETE,
            'DELETE_PRICE_TERMINATION' => ML_TEMU_NOTE_DELETE_PRICE_TERMINATION,
        );
        if (isset($noteMap[$subStatus])) {
            return '<td>'.$noteMap[$subStatus].'</td>';
        }
        return '<td></td>';
    }
}
