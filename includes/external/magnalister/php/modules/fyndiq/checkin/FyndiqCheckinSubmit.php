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
 * (c) 2010 - 2013 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_MODULES . 'magnacompatible/checkin/MagnaCompatibleCheckinSubmit.php');
require_once(DIR_MAGNALISTER_MODULES . 'fyndiq/FyndiqHelper.php');
require_once(DIR_MAGNALISTER_MODULES . 'fyndiq/classes/FyndiqProductSaver.php');

class FyndiqCheckinSubmit extends MagnaCompatibleCheckinSubmit
{
    private $oLastException = null;

    public function __construct($settings = array())
    {
        global $_MagnaSession;
        $this->summaryAddText = "<br /><br />\n" . ML_FYNDIQ_UPLOAD_EXPLANATION;

        $settings = array_merge(array(
            'language' => getDBConfigValue($settings['marketplace'] . '.lang', $_MagnaSession['mpID'], ''),
            'currency' => getCurrencyFromMarketplace($_MagnaSession['mpID']),
            'keytype' => getDBConfigValue('general.keytype', '0'),
            'itemsPerBatch' => 100,
            'mlProductsUseLegacy' => false,
        ), $settings);

        parent::__construct($settings);

        $this->settings['SyncInventory'] = array(
            'Price' => getDBConfigValue($settings['marketplace'] . '.inventorysync.price', $this->mpID, '') == 'auto',
            'Quantity' => getDBConfigValue($settings['marketplace'] . '.stocksync.tomarketplace', $this->mpID, '') == 'auto',
        );
    }

    protected function processException($e)
    {
        $this->oLastException = $e;
    }

    public function getLastException()
    {
        return $this->oLastException;
    }

    protected function setUpMLProduct()
    {
        parent::setUpMLProduct();

        // Set Price and Quantity settings
        MLProduct::gi()->setPriceConfig(FyndiqHelper::loadPriceSettings($this->mpID));
        MLProduct::gi()->setQuantityConfig(FyndiqHelper::loadQuantitySettings($this->mpID));
        MLProduct::gi()->setOptions(array(
            'sameVariationsToAttributes' => false,
            'purgeVariations' => true,
            'useGambioProperties' => (getDBConfigValue('general.options', '0', 'old') == 'gambioProperties')
        ));
    }

    protected function appendAdditionalData($iPID, $aProduct, &$aData)
    {
        $aPropertiesRow = MagnaDB::gi()->fetchRow('
			SELECT * FROM ' . TABLE_MAGNA_FYNDIQ_PROPERTIES . '
			 WHERE ' . ((getDBConfigValue('general.keytype', '0') == 'artNr')
                ? 'products_model = "' . MagnaDB::gi()->escape($aProduct['ProductsModel']) . '"'
                : 'products_id = "' . $iPID . '"'
            ) . '
			       AND mpID = ' . $this->_magnasession['mpID']
        );

        // Will not happen in sumbit cycle but can happen in loadProductByPId.
        if (empty($aPropertiesRow)) {
            $aData['submit'] = array();
            return;
        }

        $aData['submit']['SKU'] = ($this->settings['keytype'] == 'artNr') ? $aProduct['MarketplaceSku'] : $aProduct['MarketplaceId'];
        if (empty($aPropertiesRow['Title']) === false) {
            $aData['submit']['ItemTitle'] = $aPropertiesRow['Title'];
        } else {
            $aData['submit']['ItemTitle'] = $aProduct['Title'];
        }

        $aData['submit']['ItemTitle'] = html_entity_decode(fixHTMLUTF8Entities($aData['submit']['ItemTitle']), ENT_COMPAT, 'UTF-8');

        if (empty($aPropertiesRow['Description']) === false) {
            $aData['submit']['Description'] = $aPropertiesRow['Description'];
        } else {
            $aData['submit']['Description'] =  FyndiqHelper::fyndiqSanitizeDesc($aProduct['Description']);
        }

        $aData['submit']['Description'] = html_entity_decode(fixHTMLUTF8Entities($aData['submit']['Description']), ENT_COMPAT, 'UTF-8');

        $imagePath = getDBConfigValue($this->marketplace . '.imagepath', $this->_magnasession['mpID'], SHOP_URL_POPUP_IMAGES);
        $imagePath = trim($imagePath, '/ ') . '/';

        if (empty($aPropertiesRow['PictureUrl']) === false) {
            $pictureUrls = json_decode($aPropertiesRow['PictureUrl']);

            foreach ($pictureUrls as $image => $use) {
                if ($use == 'true') {
                    $aData['submit']['Images'][] = array(
                        'URL' => $imagePath . $image,
                        'id' => $image
                    );
                }
            }
        }

        $taxes = getDBConfigValue($this->marketplace . '.tax', $this->_magnasession['mpID']);

        if (empty($aProduct['Manufacturer']) === false) {
            $aData['submit']['Brand'] = $aProduct['Manufacturer'];
        } else {
            $manufacturerName = getDBConfigValue($this->marketplace . '.checkin.manufacturerfallback', $this->_magnasession['mpID'], '');
            if (empty($manufacturerName) === false) {
                $aData['submit']['Brand'] = $manufacturerName;
            }
        }

        $aData['submit']['VatPercent'] = $taxes[$aProduct['TaxClass']];
        $aData['submit']['Price'] = $aData['price'];
        $aData['submit']['Currency'] = $this->settings['currency'];
        $aData['submit']['CategoryId'] = $aPropertiesRow['MarketplaceCategory'];
        if (isset($aPropertiesRow['ShippingCost']) && (int)$aPropertiesRow['ShippingCost'] > 0) {
            $aData['submit']['ShippingCost'] = $aPropertiesRow['ShippingCost'];
        }

        if(isset($aProduct['BasePrice']) && !empty($aProduct['BasePrice'])){
            $aData['submit']['BasePrice']['Unit'] = $aProduct['BasePrice']['Unit'];
            $aData['submit']['BasePrice']['Value'] =  number_format((float)$aProduct['BasePrice']['Value'], 2, '.','');
        }

        if (!$this->getFyndiqVariations($aProduct, $aData, $imagePath)) {
            return;
        }

        if (empty($aData['submit']['Variations'])) {
            if (empty($aProduct['ManufacturerPartNumber']) === false) {
                $aData['submit']['ArticleMpn'] = $aProduct['ManufacturerPartNumber'];
            }

            if (empty($aProduct['EAN']) === false) {
                $aData['submit']['ArticleEan'] = $aProduct['EAN'];
            }

            $aData['submit']['Quantity'] = $aData['quantity'];
        }
    }

    protected function getFyndiqVariations($product, &$data, $imagePath)
    {
        if ($this->checkinSettings['Variations'] !== 'yes') {
            return true;
        }

        $variations = array();
        foreach ($product['Variations'] as $v) {
            $this->simpleprice->setPrice($v['Price']['Price']);
            $price = $this->simpleprice->roundPrice()->makeSignalPrice(
                getDBConfigValue($this->marketplace.'.price.signal', $this->mpID, '')
            )->getPrice();

            $vi = array(
                'SKU' => ($this->settings['keytype'] == 'artNr') ? $v['MarketplaceSku'] : $v['MarketplaceId'],
                'Price' => $price,
                'Currency' => $this->settings['currency'],
                'Quantity' => ($this->quantityLumb === false)
                    ? max(0, $v['Quantity'] - (int)$this->quantitySub)
                    : $this->quantityLumb,
                'ArticleEan' => $v['EAN']
            );

            $vi['ArticleName'] = '';
            foreach ($v['Variation'] as $varAttribute) {
                $vi['ArticleName'] .= $varAttribute['Name'] . ' - ' . $varAttribute['Value'] . ' ';
            }

            if (empty($vi['ArticleName'])) {
                $variation = MLProduct::gi()->getProductById($v['VariationId']);
                $vi['ArticleName'] = $variation['Title'];
            }

            if (empty($product['ManufacturerPartNumber']) === false) {
                $vi['ArticleMpn'] = $product['ManufacturerPartNumber'];
            }

            $vi['Images'] = $data['submit']['Images'];
            if (!empty($v['Images'])) {
                foreach ($v['Images'] as $image) {
                    $exist = false;
                    foreach ($vi['Images'] as $viImage) {
                        if ($viImage['id'] === $image) {
                            $exist = true;
                        }
                    }

                    if (!$exist) {
                        $vi['Images'][] = array(
                            'URL' => $imagePath . $image,
                            'id' => $image
                        );
                    }
                }
            }

            if(isset($v['BasePrice']) && !empty($v['BasePrice'])){
                $vi['BasePrice']['Unit'] = $v['BasePrice']['Unit'];
                $vi['BasePrice']['Value'] = number_format((float)$v['BasePrice']['Value'], 2, '.','');
            }

            $variations[] = $vi;
        }

        $data['submit']['Variations'] = $variations;
        return true;
    }

    protected function markAsFailed($sku)
    {
        $iPID = magnaSKU2pID($sku);
        $this->badItems[] = $iPID;
        unset($this->selection[$iPID]);
    }

    protected function postSubmit()
    {
        try {
            //*
            $result = MagnaConnector::gi()->submitRequest(array(
                'ACTION' => 'UploadItems',
            ));
            //*/
        } catch (MagnaException $e) {
            $this->submitSession['api']['exception'] = $e;
            $this->submitSession['api']['html'] = MagnaError::gi()->exceptionsToHTML();
        }
    }

    protected function generateRedirectURL($state)
    {
        return toURL(array(
            'mp' => $this->realUrl['mp'],
            'mode' => ($state == 'fail') ? 'errorlog' : 'listings'
        ), true);
    }

}
