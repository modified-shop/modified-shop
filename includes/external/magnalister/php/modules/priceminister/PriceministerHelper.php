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

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/AttributesMatchingHelper.php');

class PriceministerHelper extends AttributesMatchingHelper
{
    public static $TITLE_MAX_LENGTH = 200;
    public static $DESC_MAX_LENGTH = 4000;

    private static $instance;

    public static function gi()
    {
        if (self::$instance === null) {
            self::$instance = new PriceministerHelper();
        }

        return self::$instance;
    }

    public static function loadPriceSettings($mpId)
    {
        $mp = magnaGetMarketplaceByID($mpId);

        $currency = getCurrencyFromMarketplace($mpId);
        $convertCurrency = getDBConfigValue(array($mp . '.exchangerate', 'update'), $mpId, false);

        $config = array(
            'Price' => array(
                'AddKind' => getDBConfigValue($mp . '.price.addkind', $mpId, 'percent'),
                'Factor' => (float)getDBConfigValue($mp . '.price.factor', $mpId, 0),
                'Signal' => getDBConfigValue($mp . '.price.signal', $mpId, ''),
                'Group' => getDBConfigValue($mp . '.price.group', $mpId, ''),
                'UseSpecialOffer' => getDBConfigValue(array($mp . '.price.usespecialoffer', 'val'), $mpId, false),
                'Currency' => $currency,
                'ConvertCurrency' => $convertCurrency,
            ),
            'PurchasePrice' => array(
                'AddKind' => getDBConfigValue($mp . '.purchaseprice.addkind', $mpId, 'percent'),
                'Factor' => (float)getDBConfigValue($mp . '.purchaseprice.factor', $mpId, 0),
                'Signal' => getDBConfigValue($mp . '.purchaseprice.signal', $mpId, ''),
                'Group' => getDBConfigValue($mp . '.purchaseprice.group', $mpId, ''),
                'UseSpecialOffer' => false,
                'Currency' => $currency,
                'ConvertCurrency' => $convertCurrency,
                'IncludeTax' => false,
            ),
        );

        return $config;
    }

    public static function loadQuantitySettings($mpId)
    {
        $mp = magnaGetMarketplaceByID($mpId);

        $config = array(
            'Type' => getDBConfigValue($mp . '.quantity.type', $mpId, 'lump'),
            'Value' => (int)getDBConfigValue($mp . '.quantity.value', $mpId, 0),
            'MaxQuantity' => (int)getDBConfigValue($mp . '.quantity.maxquantity', $mpId, 0),
        );

        return $config;
    }

    public static function processCheckinErrors($result, $mpID)
    {
        $fieldname = 'MARKETPLACEERRORS';
        $dbCharSet = MagnaDB::gi()->mysqlVariableValue('character_set_connection');
        if (('utf8mb3' == $dbCharSet) || ('utf8mb4' == $dbCharSet)) {
            # means the same for us
            $dbCharSet = 'utf8';
        }
        if ($dbCharSet != 'utf8') {
            arrayEntitiesToLatin1($result[$fieldname]);
        }
        $supportedFields = array('ErrorMessage', 'DateAdded', 'AdditionalData');
        if (!isset($result[$fieldname]) || empty($result[$fieldname])) {
            return;
        }
        foreach ($result[$fieldname] as $err) {
            if (!isset($err['AdditionalData'])) {
                $err['AdditionalData'] = array();
            }
            foreach ($err as $key => $value) {
                if (!in_array($key, $supportedFields)) {
                    $err['AdditionalData'][$key] = $value;
                    unset($err[$key]);
                }
            }
            $err = array(
                'mpID' => $mpID,
                'errormessage' => $err['ErrorMessage'],
                'dateadded' => $err['DateAdded'],
                'additionaldata' => serialize($err['AdditionalData']),
            );
            MagnaDB::gi()->insert(TABLE_MAGNA_COMPAT_ERRORLOG, $err);
        }
    }

    public static function GetConditionTypesConfig(&$types)
    {
        $types['values'] = self::GetConditionTypes();
    }

    public static function GetCarriersConfig(&$types)
    {
        $types['values'] = self::GetCarriers();
    }

    public static function GetConditionTypes()
    {
        return self::submitSessionCachedRequest('GetItemConditions');
    }

    public static function GetCarriers()
    {
        return self::submitSessionCachedRequest('GetCarriers');
    }

    public static function SearchOnPriceminister($search = '', $searchBy = 'EAN')
    {
        try {
            $data = MagnaConnector::gi()->submitRequest(array(
                'ACTION' => 'GetItemsFromMarketplace',
                'DATA' => array(
                    $searchBy => $search
                )
            ));
        } catch (MagnaException $e) {
            $data = array(
                'DATA' => false
            );
        }

        if (!is_array($data) || !isset($data['DATA']) || empty($data['DATA'])) {
            return false;
        }

        return $data['DATA'];
    }

    public static function GetWeightFromShop($itemId)
    {
        $result = MagnaDB::gi()->fetchOne('
			SELECT products_weight
			FROM ' . TABLE_PRODUCTS . '
			WHERE products_id = "' . $itemId . '"
		');

        if ($result && (int)$result > 0) {
            $weight = round($result, 2);
            return $weight . 'kg';
        }

        return '';
    }

    public static function GetContentVolumeFromShop($itemId)
    {
        $result = MagnaDB::gi()->fetchRow('
			SELECT p.products_vpe_value AS vpe, pvpe.products_vpe_name AS sufix
			FROM ' . TABLE_PRODUCTS . ' p, ' . TABLE_PRODUCTS_VPE . ' pvpe
			WHERE p.products_id = "' . $itemId . '"
				AND pvpe.products_vpe_id = p.products_vpe
		');
        if ($result && (int)$result > 0) {
            $factor = array();
            if (preg_match('/^([0-9][0-9,.]*)/', $result['sufix'], $factor)) {
                $factor = mlFloatalize($factor[1]);
                $contentValue = round($result['vpe'] * $factor, 2);
                $result['sufix'] = trim(preg_replace('/^[0-9][0-9,.]*/', '', $result['sufix']));
            } else {
                $contentValue = round($result['vpe'], 2);
            }
            return $contentValue . $result['sufix'];
        }

        return '';
    }

    public static function getTitleAndDescription(&$selection, $mpID) {
        $imagePath = getDBConfigValue('priceminister.imagepath', $mpID);
        if (empty($imagePath)) {
            $imagePath = defined('DIR_WS_CATALOG_POPUP_IMAGES')
                ? HTTP_CATALOG_SERVER.DIR_WS_CATALOG_POPUP_IMAGES
                : HTTP_CATALOG_SERVER.DIR_WS_CATALOG_IMAGES;
        }

        $descriptionTemplate = getDBConfigValue('priceminister.template.content', $mpID, '<p>#TITLE#</p>
				<p>#ARTNR#</p>
				<p>#SHORTDESCRIPTION#</p>
				<p>#PICTURE1#</p>
				<p>#PICTURE2#</p>
				<p>#PICTURE3#</p>
				<p>#DESCRIPTION#</p>'
        );

        # Template fuellen
        # bei mehreren Artikeln erst beim Speichern fuellen
        # Preis und ggf. VPE wird erst beim Uebermitteln eingesetzt.
        $substitution = array (
            '#TITLE#' => fixHTMLUTF8Entities($selection[0]['Title']),
            '#ARTNR#' => $selection[0]['products_model'],
            '#PID#' => $selection[0]['products_id'],
            '#SKU#' => magnaPID2SKU($selection[0]['products_id']),
            '#SHORTDESCRIPTION#' => $selection[0]['Subtitle'],
            '#DESCRIPTION#' => stripLocalWindowsLinks($selection[0]['Description']),
            '#PICTURE1#' => $imagePath . $selection[0]['PictureUrl'],
        );
        $selection[0]['Description'] = PriceministerHelper::substitutePictures(substituteTemplate(
            $descriptionTemplate, $substitution
        ), $selection[0]['products_id'], $imagePath);

        $titleTemplate = getDBConfigValue('priceminister.template.name', $mpID, '#TITLE#');

        $simplePrice = new SimplePrice(null, getCurrencyFromMarketplace($mpID));
        $simplePrice->setFinalPriceFromDB($selection[0]['products_id'], $mpID);

        # Titel-Template fuellen
        # bei mehreren Artikeln erst beim Speichern fuellen
        # Preis und ggf. VPE wird erst beim Uebermitteln eingesetzt.
        $substitution = array (
            '#TITLE#' => fixHTMLUTF8Entities($selection[0]['Title']),
            '#BASEPRICE#' => $simplePrice->roundPrice()->getPrice(),
        );
        $selection[0]['Title'] = substituteTemplate(
            $titleTemplate, $substitution
        );
    }

    public static function substitutePictures($tmplStr, $pID, $imagePath) {
        # Tabelle nur bei xtCommerce- und Gambio- Shops vorhanden (nicht OsC)
        if (   defined('TABLE_MEDIA')      && MagnaDB::gi()->tableExists(TABLE_MEDIA)
            && defined('TABLE_MEDIA_LINK') && MagnaDB::gi()->tableExists(TABLE_MEDIA_LINK)
        ) {
            $pics = MagnaDB::gi()->fetchArray('SELECT
				id as image_nr, file as image_name
				FROM '.TABLE_MEDIA.' m, '.TABLE_MEDIA_LINK.' ml
				WHERE m.type=\'images\' AND ml.class=\'product\' AND m.id=ml.m_id AND ml.link_id='.$pID);
            $i = 2;
            # Ersetze #PICTURE2# usw. (#PICTURE1# ist das Hauptbild und wird vorher ersetzt)
            foreach($pics as $pic) {
                $tmplStr = str_replace('#PICTURE'.$i.'#', "<img src=\"".$imagePath.$pic['image_name']."\" style=\"border:0;\" alt=\"\" title=\"\" />",
                    preg_replace( '/(src|SRC|href|HREF)(\s*=\s*)(\'|")(#PICTURE'.$i.'#)/', '\1\2\3'.$imagePath.$pic['image_name'], $tmplStr));
                $i++;
            }
            # Uebriggebliebene #PICTUREx# loeschen
            $str = preg_replace(	'/#PICTURE\d+#/','', $tmplStr);
            #		str_replace($find, $replace, $tmplStr));
        } else {
            $str = preg_replace(	'/#PICTURE\d+#/','', $tmplStr);
        }
        return $str;
    }

    public function getMPVariations($category, $prepare = false, $getDate = false, $onlyAdvert = false)
    {
        $mpData = $this->getAttributesFromMP($category, $onlyAdvert);
        $dbData = $this->getPreparedData($category, $prepare);
        $tableName = $this->getVariationMatchingTableName();
        $subcategories = $this->getSubcategories($category);

        // load default values from Variation Matching tab (global matching)
        $usedGlobal = false;
        $globalMatching = $this->getCategoryMatching($category);

        if ($dbData === false){
            $dbData = $globalMatching;
            $usedGlobal = true;
        }

        arrayEntitiesToUTF8($mpData);
        $attributes = array();
        foreach ($mpData['attributes'] as $code => $value){
            $attributes[$code] = array(
                'AttributeCode' => $code,
                'AttributeName' => $value['title'],
                'AllowedValues' => isset($value['values']) ? $value['values'] : array(),
                'AttributeDescription' => isset($value['desc']) ? $value['desc'] : '',
                'CurrentValues' => isset($dbData[$code]) ? $dbData[$code] : array('Values' => array()),
                'ChangeDate' => isset($value['changed']) ? $value['changed'] : false,
                'Required' => isset($value['mandatory']) ? $value['mandatory'] : false,
            );

            if (isset($dbData[$code])){
                if (!isset($dbData[$code]['Required'])){
                    $dbData[$code]['Required'] = isset($value['mandatory']) ? $value['mandatory'] : true;
                    $dbData[$code]['Code'] = !empty($value['values']) ? 'attribute_value' : 'freetext';
                    $dbData[$code]['AttributeName'] = $value['title'];
                }

                $attributes[$code]['CurrentValues'] = $dbData[$code];
            }
        }

        $this->addAdditionalAttributesMP($attributes, $dbData);

        $hasDifferentlyPreparedProducts = false;
        if (!$usedGlobal && !empty($globalMatching)){
            $this->detectChanges($globalMatching, $attributes);
        } else if (!$prepare && !empty($globalMatching)){
            // on variation matching tab. Check whether some products are prepared differently
            $hasDifferentlyPreparedProducts = $this->areProductsDifferentlyPrepared($category, $globalMatching);
        }

        if(!$onlyAdvert){
            // if there are saved values but they were removed from Marketplace, display warning to user
            foreach ($dbData as $code => $value){
                if (!isset($attributes[$code]) && strpos($code, 'additional_attribute_') === false){
                    $attributes[$code] = array(
                        'Deleted' => true,
                        'AttributeCode' => $code,
                        'AttributeName' => !empty($value['AttributeName']) ? $value['AttributeName'] : $code,
                        'AllowedValues' => array(),
                        'AttributeDescription' => '',
                        'CurrentValues' => array('Values' => array()),
                        'ChangeDate' => '',
                        'Required' => isset($value['mandatory']) ? $value['mandatory'] : false,
                    );
                }
            }
        }

        $subs = array();
        foreach ($subcategories as $attrKey){
            if(!empty($attributes[$attrKey])){
                $subs[] = $attributes[$attrKey];
                unset($attributes[$attrKey]);
            }
        }

        if ($getDate){
            return array(
                'Attributes' => $attributes,
                'ModificationDate' => MagnaDB::gi()->fetchOne(eecho('
                    SELECT ModificationDate
                    FROM ' . $tableName . '
                    WHERE MpId = ' . $this->mpId . '
                        AND MpIdentifier = "' . $category . '"
                ', false), true),
                'DifferentProducts' => $hasDifferentlyPreparedProducts,
                'Subcategories' => $subs
            );
        }

        return $attributes;
    }

    /**
     * Truncates HTML text without breaking HTML structure.
     *
     * @param string $text String to truncate.
     * @param integer $length Length of returned string, including ellipsis.
     *
     * @return string Trimmed string.
     */
    public static function truncateString($text, $length = 100) {
        if (strlen($text) <= $length) {
            return $text;
        }

        $textLength = min($length, strlen(preg_replace('/<.*?>/', '', $text)));
        $resultText = parent::truncateString($text, $textLength);
        while (strlen($resultText) > $length) {
            $textLength -= 100;
            $resultText = parent::truncateString($text, $textLength);
        }

        return $resultText;
    }

    private static function submitSessionCachedRequest($action)
    {
        global $_MagnaSession;
        $mpID = $_MagnaSession['mpID'];
        $data = array(
            'DATA' => false
        );

        if (isset($_MagnaSession[$mpID][$action])) {
            return $_MagnaSession[$mpID][$action];
        }

        try {
            $data = MagnaConnector::gi()->submitRequest(array(
                'ACTION' => $action
            ));
        } catch (MagnaException $e) {
        }

        if (!is_array($data) || !isset($data['DATA'])) {
            return false;
        }

        $_MagnaSession[$mpID][$action] = $data['DATA'];
        return $_MagnaSession[$mpID][$action];
    }

    protected function getPreparedData($category, $prepare = false)
    {
        $availableCustomConfigs = false;
        if ($prepare){
            $availableCustomConfigs = json_decode(MagnaDB::gi()->fetchOne(eecho('
				SELECT DISTINCT CategoryAttributes
				FROM ' . TABLE_MAGNA_PRICEMINISTER_PREPARE . '
				WHERE MpId = ' . $this->mpId . '
					AND products_model IN("' . implode('", "', $prepare) . '")
					AND MarketplaceCategories = "' . $category . '"
			', false), true), true);
        }
        
        return !$availableCustomConfigs ? false : $availableCustomConfigs;
    }

    /**
     * Gets prepared attributes data for products prepared for given category.
     *
     * @param string $category
     * @return array|null
     */
    protected function getPreparedProductsData($category)
    {
        $dataFromDB = MagnaDB::gi()->fetchArray(eecho('
				SELECT `CategoryAttributes`
				FROM ' . TABLE_MAGNA_PRICEMINISTER_PREPARE . '
				WHERE mpID = ' . $this->mpId . '
					AND MarketplaceCategories = "' . $category . '"
			', false), true);

        if ($dataFromDB) {
            $result = array();
            foreach ($dataFromDB as $preparedData) {
                if ($preparedData) {
                    $result[] = json_decode($preparedData, true);
                }
            }

            return $result;
        }

        return null;
    }

    protected function getAttributesFromMP($category, $onlyAdvert = false)
    {
        $data = PriceministerApiConfigValues::gi()->getVariantConfigurationDefinition($category, $onlyAdvert);
        if (!is_array($data) || !isset($data['attributes'])){
            $data = array();
        }

        return $data;
    }

    protected function getSubcategories($category)
    {
        $data = PriceministerApiConfigValues::gi()->getSubcategoryAttributesForCategory($category);
        if (!is_array($data) || empty($data)){
            $data = array();
        }

        return $data;
    }

    public function getProductModel($selectionName)
    {
        $pIDs = MagnaDB::gi()->fetchArray('
             SELECT pID FROM ' . TABLE_MAGNA_SELECTION . '
             WHERE mpID=\'' . $this->mpId . '\' AND
                  selectionname=\'' . $selectionName . '\' AND
                  session_id=\'' . session_id() . '\'
        ', true);

        $productModels = MagnaDB::gi()->fetchArray('
            SELECT products_model
            FROM ' . TABLE_PRODUCTS . '
            WHERE products_id IN("' . implode('", "',$pIDs) . '")
        ', true);

        if ($productModels) {
            return $productModels;
        }

        return false;
    }

    public function renderMatchingTable($url, $categoryOptions, $addCategoryPick = true)
    {
        // $mpTitle = str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERIC_MP_CATEGORY);
        $mpTitle = str_replace('%marketplace%', 'PriceMinister', ML_GENERIC_MP_CATEGORY);
        $mpAttributeTitle = str_replace('%marketplace%', 'PriceMinister', ML_GENERAL_VARMATCH_MP_ATTRIBUTE);

        ob_start();
        ?>
        <form method="post" id="matchingForm" action="<?php echo toURL($url, array(), true); ?>">
            <table id="variationMatcher" class="attributesTable">
                <tbody>
                <tr class="headline">
                    <td colspan="3"><h4><?php echo $mpTitle ?></h4></td>
                </tr>
                <tr id="mpVariationSelector">
                    <th><?php echo ML_LABEL_MAINCATEGORY ?></th>
                    <td class="input">
                        <table class="inner middle fullwidth categorySelect">
                            <tbody>
                            <tr>
                                <td>
                                    <div class="hoodCatVisual" id="PrimaryCategoryVisual">
                                        <select id="PrimaryCategory" name="PrimaryCategory" style="width:100%">
                                            <?php echo $categoryOptions ?>
                                        </select>
                                    </div>
                                </td>
                                <?php if ($addCategoryPick){ ?>
                                    <td class="buttons">
                                        <input class="fullWidth ml-button smallmargin mlbtn-action" type="button"
                                               value="<?php echo ML_GENERIC_CATEGORIES_CHOOSE ?>"
                                               id="selectPrimaryCategory"/>
                                    </td>
                                <?php } ?>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                    <td class="info"></td>
                </tr>
                <tr class="spacer">
                    <td colspan="3">&nbsp;</td>
                </tr>
                </tbody>
                <tbody id="tbodyDynamicMatchingHeadline" style="display:none;">
                <tr class="headline">
                    <td colspan="1"><h4><?php echo $mpAttributeTitle ?></h4></td>
                    <td colspan="2"><h4><?php echo ML_GENERAL_VARMATCH_MY_WEBSHOP_ATTRIB ?></h4></td>
                </tr>
                </tbody>
                <tbody id="tbodyDynamicMatchingInput" style="display:none;">
                <tr>
                    <th></th>
                    <td class="input"><?php echo ML_GENERAL_VARMATCH_SELECT_CATEGORY ?></td>
                    <td class="info"></td>
                </tr>
                </tbody>
            </table>
            <p id="categoryInfo" style="display: none"><?php echo ML_GENERAL_VARMATCH_CATEGORY_INFO ?></p>
            <br><br><br>
            <table class="actions">
                <thead>
                <tr>
                    <th><?php echo ML_LABEL_ACTIONS ?></th>
                </tr>
                </thead>
                <tbody>
                <tr class="firstChild">
                    <td>
                        <table>
                            <tbody>
                            <tr>
                                <td class="firstChild">
                                    <button type="button" class="ml-button ml-reset-matching">
                                        <?php echo ML_GENERAL_VARMATCH_RESET_MATCHING ?></button>
                                </td>
                                <td></td>
                                <td class="lastChild">
                                    <input type="submit" value="<?php echo ML_GENERAL_VARMATCH_SAVE_BUTTON ?>"
                                           class="ml-button mlbtn-action">
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>
        </form>
        <?php
        return ob_get_clean();
    }
}
