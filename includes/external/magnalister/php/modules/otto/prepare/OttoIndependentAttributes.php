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
 * (c) 2010 - 2021 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'otto/OttoHelper.php');

class OttoIndependentAttributes {
    const OTTO_CAT_VALIDITY_PERIOD = 86400; # Nach welcher Zeit werden Kategorien ungueltig (Sekunden)

    /**
     * @var array|string[]
     */
    protected $url;

    /**
     * @var string
     */
    protected $currentLanguageCode = '';

    public function __construct($request = 'view') {
        global $_url, $_MagnaSession;

        $this->currentLanguageCode = strtolower($_SESSION['language_code']);
        $this->request = $request;
        $this->url = $_url;
        $this->mpID = $_MagnaSession['mpID'];
        $this->marketplace = $_MagnaSession['currentPlatform'];
        $this->hasPlatformCol = true;
        $this->columns = MagnaDB::gi()->getTableColumns(TABLE_MAGNA_OTTO_CATEGORIES_MARKETPLACE);
    }

    public function getCategoryIndependentAttributes() {
        try {
            $result = MagnaConnector::gi()->submitRequest(array(
                'SUBSYSTEM' => 'otto',
                'ACTION' => 'GetCategoryIndependentAttributes'
            ));

            $this->cacheOttoBrands($result['DATA']);

            return $result['DATA'];
        } catch (MagnaException $e) {
            return array();
        }
    }

    public static function renderIndependentAttributesTable() {

        $html = '<tr class="headline">
        <td class="ottoDarkGreyBackground" colspan="3"><h4>'.ML_OTTO_CATEGORY_INDEPENDENT_ATTRIBUTES.'</h4>
            <p>'.ML_OTTO_CATEGORY_INDEPENDENT_ATTRIBUTES_INFO.'</p>
        </td>
        </tr>
        <tbody id="tbodyDynamicIndependentMatchingHeadline">
                <tr class="even">
                    <th class="ottoGreyBackground"><h4>'.ML_OTTO_CATEGORY_INDEPENDENT_ATTRIBUTES_REQUIRED.'</h4></th>
                    <td class="ottoGreyBackground" colspan="3"><h4>'.ML_OTTO_CATEGORY_INDEPENDENT_ATTRIBUTES_REQUIRED_INFO.'</h4></td>
                </tr>
        </tbody>
        <tbody id="tbodyDynamicIndependentMatchingInput">
            <tr>
                <th></th>
                <td class="input">'.ML_GENERAL_VARMATCH_SELECT_CATEGORY.'</td>
                <td class="info"></td>
            </tr>
        </tbody>
        <tbody id="tbodyDynamicIndependentMatchingOptionalHeadline">
                <tr class="even">
                    <th class="ottoGreyBackground" ><h4>'.ML_OTTO_CATEGORY_INDEPENDENT_ATTRIBUTES_OTIONAL.'</h4></th>
                    <td class="ottoGreyBackground" colspan="3"><h4>'.ML_OTTO_CATEGORY_INDEPENDENT_ATTRIBUTES_OTIONAL_INFO.'</h4></td>
                </tr>
                </tbody>
                <tbody id="tbodyDynamicIndependentMatchingOptionalInput">
                    <tr>
                        <th></th>
                        <td class="input">'.ML_GENERAL_VARMATCH_SELECT_CATEGORY.'</td>
                        <td class="info"></td>
                    </tr>
                </tbody>';

        return $html;
    }

    private function cacheOttoBrands($result) {
        foreach ($result['attributes'] as $key => $value) {
            if ($value['mandatory']) {
                $requiredAttributes[$key] = $value;
                if($value['name'] == 'Brand') {
                    $brandCacheFile = DIR_MAGNALISTER_FS_CACHE.'ottoBrandCache.json';
                    file_put_contents($brandCacheFile, json_encode($value['values']));
                } 
            }
        }
    }
}
