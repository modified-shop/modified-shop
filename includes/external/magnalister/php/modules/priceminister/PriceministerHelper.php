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

require_once(DIR_MAGNALISTER_MODULES . 'magnacompatible/AttributesMatchingHelper.php');

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

    public static function GetCountriesConfig(&$types) {
        $types['values'] = self::GetCountries();
    }

    public static function GetConditionTypes()
    {
        return self::submitSessionCachedRequest('GetItemConditions');
    }

    public static function GetCarriers()
    {
        return self::submitSessionCachedRequest('GetCarriers');
    }

    public static function GetCountries() {
        return self::submitSessionCachedRequest('GetCountries');
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

    public static function getTitleAndDescription(&$selection, $mpID)
    {
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
        $substitution = array(
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
        $substitution = array(
            '#TITLE#' => fixHTMLUTF8Entities($selection[0]['Title']),
            '#BASEPRICE#' => $simplePrice->roundPrice()->getPrice(),
        );
        $selection[0]['Title'] = substituteTemplate(
            $titleTemplate, $substitution
        );
    }

    public static function substitutePictures($tmplStr, $pID, $imagePath)
    {
        # Tabelle nur bei xtCommerce- und Gambio- Shops vorhanden (nicht OsC)
        if (defined('TABLE_MEDIA') && MagnaDB::gi()->tableExists(TABLE_MEDIA)
            && defined('TABLE_MEDIA_LINK') && MagnaDB::gi()->tableExists(TABLE_MEDIA_LINK)
        ) {
            $pics = MagnaDB::gi()->fetchArray('SELECT
				id as image_nr, file as image_name
				FROM ' . TABLE_MEDIA . ' m, ' . TABLE_MEDIA_LINK . ' ml
				WHERE m.type=\'images\' AND ml.class=\'product\' AND m.id=ml.m_id AND ml.link_id=' . $pID);
            $i = 2;
            # Ersetze #PICTURE2# usw. (#PICTURE1# ist das Hauptbild und wird vorher ersetzt)
            foreach ($pics as $pic){
                $tmplStr = str_replace('#PICTURE' . $i . '#', "<img src=\"" . $imagePath . $pic['image_name'] . "\" style=\"border:0;\" alt=\"\" title=\"\" />",
                    preg_replace('/(src|SRC|href|HREF)(\s*=\s*)(\'|")(#PICTURE' . $i . '#)/', '\1\2\3' . $imagePath . $pic['image_name'], $tmplStr));
                $i++;
            }
            # Uebriggebliebene #PICTUREx# loeschen
            $str = preg_replace('/#PICTURE\d+#/', '', $tmplStr);
            #		str_replace($find, $replace, $tmplStr));
        } else {
            $str = preg_replace('/#PICTURE\d+#/', '', $tmplStr);
        }
        return $str;
    }

    public function getMPVariations($category, $prepare = false, $getDate = false, $additionalData = null, $customIdentifier = '')
    {
        $result = parent::getMPVariations($category, $prepare, $getDate, $additionalData, $customIdentifier);

        $attributes = $getDate ? $result['Attributes'] : $result;

        $subs = array();
        $subcategories = $this->getSubcategories($category);
        foreach ($subcategories as $attrKey) {
            if (!empty($attributes[$attrKey])) {
                $subs[] = $attributes[$attrKey];
                unset($attributes[$attrKey]);
            }
        }

        if ($getDate) {
            $result['Attributes'] = $attributes;
            $result['Subcategories'] = $subs;
        } else {
            $result = $attributes;
        }

        return $result;
    }

    /**
     * Truncates HTML text without breaking HTML structure.
     * Source: https://dodona.wordpress.com/2009/04/05/how-do-i-truncate-an-html-string-without-breaking-the-html-code
     *
     * @param string $text String to truncate.
     * @param integer $length Length of returned string, including ellipsis.
     * @param string $ending Ending to be appended to the trimmed string.
     * @param boolean $exact If false, $text will not be cut mid-word
     * @param boolean $considerHtml If true, HTML tags would be handled correctly
     * @return string Trimmed string.
     */
    public static function truncateString($text, $length = 100, $ending = '...', $exact = false, $considerHtml = true) {
        if (strlen($text) <= $length) {
            return $text;
        }

        $textLength = min($length, strlen(preg_replace('/<.*?>/', '', $text)));
        $resultText = parent::truncateString($text, $textLength, $ending, $exact, $considerHtml);
        while (strlen($resultText) > $length) {
            $textLength -= 100;
            $resultText = parent::truncateString($text, $textLength, $ending, $exact, $considerHtml);
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

    protected function isProductPrepared($category, $prepare = false)
    {
        if (getDBConfigValue('general.keytype', '0') == 'artNr') {
            $sSQLAnd = ' AND products_model = "'.$prepare.'"';
        } else {
            $sSQLAnd = ' AND products_id = "'. $prepare . '"';
        }
        
        if ($prepare) {
            $productsId = MagnaDB::gi()->fetchOne(eecho('
                    SELECT products_id
                    FROM ' . TABLE_MAGNA_PRICEMINISTER_PREPARE . '
                    WHERE MpId = ' . $this->mpId . '
                        AND MarketplaceCategories = "' . $category . '"
                        ' . $sSQLAnd . '
                    LIMIT 1
                ', false));

            return !empty($productsId);
        }

        return false;
    }

	protected function getPreparedData($category, $prepare = false, $customIdentifier = '')
    {
        if (getDBConfigValue('general.keytype', '0') == 'artNr') {
            $sSQLAnd = ' AND products_model = "'.$prepare.'"';
        } else {
            $sSQLAnd = ' AND products_id = "'. $prepare . '"';
        }
        
        $availableCustomConfigs = array();
        if ($prepare) {
            $availableCustomConfigs = json_decode(MagnaDB::gi()->fetchOne(eecho('
				SELECT DISTINCT CategoryAttributes
				FROM ' . TABLE_MAGNA_PRICEMINISTER_PREPARE . '
				WHERE MpId = ' . $this->mpId . '
					AND MarketplaceCategories = "' . $category . '"
					' . $sSQLAnd . '
			', false)), true);
        }

        return !$availableCustomConfigs ? array() : $availableCustomConfigs;
    }

    /**
     * Gets prepared attributes data for products prepared for given category.
     *
     * @param string $category
     * @param string $customIdentifier
     * @return array|null
     */
    protected function getPreparedProductsData($category, $customIdentifier = '')
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

    protected function getAttributesFromMP($category, $additionalData = null, $customIdentifier = '')
    {
        $data = PriceministerApiConfigValues::gi()->getVariantConfigurationDefinition($category, $additionalData);
        if (!is_array($data) || !isset($data['attributes'])) {
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
}
