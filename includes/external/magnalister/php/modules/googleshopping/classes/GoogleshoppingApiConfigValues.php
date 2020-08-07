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

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/classes/MagnaCompatibleApiConfigValues.php');

class GoogleshoppingApiConfigValues extends MagnaCompatibleApiConfigValues {
    protected static $instance = null;

    public static function gi() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getShippingTimes() {
        $data = $this->fetchDataFromApi('GetShippingTimes');
        return $data;
    }

    public function getShippingServices() {
        $data = $this->fetchDataFromApi('GetShippingServiceDetails');
        if (!is_array($data)) {
            $data = array(
                array(
                    'Name' => ML_DAWANDA_NO_SHIPPING_SERVICE_AVAILABLE,
                    'Info' => array(
                        'Single' => 0,
                        'Combi' => 0
                    )
                )
            );
        }
        return $data;
    }

    public function getMarketplaceColors() {
        $data = $this->fetchDataFromApi('GetColors');
        $data = array('' => ML_DAWANDA_MARKETPLACE_PRODUCT_COLORS_NO_CHOOSE) + $data;
        return $data;
    }

    public function getLanguages() {
        $data = $this->fetchDataFromApi('GetLanguages');
        return $data;
    }

    public function getProductTypes() {
        $data = $this->fetchDataFromApi('GetProductTypes');
        return $data;
    }

    public function getReturnPolicies() {
        $data = $this->fetchDataFromApi('GetReturnPolicies');
        #$data = array();
        if (empty($data)) {
            $data = array(
                '' => array(
                    'Id' => '',
                    'Language' => '',
                    'Title' => ML_LABEL_NONE_DEFINED_ON_MP,
                    'Description' => ML_LABEL_NONE_DEFINED_ON_MP,
                )
            );
        }
        return $data;
    }

    public function getVariantConfigurationDefinition($categoryId, $targetCountry) {
        $data = $this->fetchDataFromApi('GetCategoryDetails', array(
            'DATA' => array(
                'categoryId' => $categoryId,
                'targetCountry' => $targetCountry,
            )
        ));

        return $data;
    }

    public function getShippingTemplates() {
        $data = $this->fetchDataFromApi('GetShippingTemplates', array(
            'DATA' => array(
                'GetShippingTemplates' => time(),
            ),
        ));
        return $data;
    }

    public function addShippingTemplate($formData = array()) {
        $data = $this->fetchDataFromApi('SaveShippingTemplate', array(
            'DATA' => $formData,
        ));

        return $data;
    }
}
