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

/**
 * This object encapsulates rules for mapping size to the product.
 * For more info: @see https://support.google.com/merchants/answer/6324492?hl=en&ref_topic=6324338#
 */
abstract class GoogleshoppingSizeAbstract {
    protected function getRequiredCategories() {
        return array(
            1604 => "Apparel & Accessories > Clothing",
            187 => "Apparel & Accessories > Shoes",
        );
    }

    protected function getRequiredCountries() {
        return array(
            'BR' => 'Brazil',
            'FR' => 'France',
            'DE' => 'Germany',
            'JP' => 'Japan',
            'GB' => 'United Kingdom',
            'US' => 'United States',
        );
    }

    /**
     * For exceptional cases where size is not mandatory but satisfies common criteria
     *
     * @return bool
     */
    protected function preFilter() {
        return false;
    }

    /**
     * Strategy to decide if attribute is in fact required.
     * Concrete implementation may as well disregard the encapsulated rules here.
     * It may apply OR or AND strategy and include invariants out of scope of country and categories.
     * Given the pace google is adding new features and adjust its rules this gives us a flexibility to adopt without major rewrite
     *
     * @param bool $category
     * @param bool $country
     *
     * @return bool
     */
    abstract protected function applyStrategy($category, $country);

    /**
     * @param int $categoryId numerical value of category provided by google (it is localized so ids are the same regardless of language)
     * @param string $countryCode 2 letters iso2 country code
     *
     * @return bool
     */
    private function isRequiredForCategoryInCountry($categoryId, $countryCode) {
        if (false === $this->preFilter()) {
            return false;
        }

        $requiredCountry = array_key_exists($countryCode, $this->getRequiredCountries());
        $requiredCategory = array_key_exists($categoryId, $this->getRequiredCategories());

        return $this->applyStrategy($requiredCategory, $requiredCountry);
    }

    /**
     * @param int $categoryId
     * @param string $countryCode
     * @param callable $factory anonymous function, closure or any object implementing ::__invoke() method
     *
     * @return array
     */
    public function provideSizes($categoryId, $countryCode, callable $factory) {
        if (!$this->isRequiredForCategoryInCountry($categoryId, $countryCode)) {
            return array();
        }

        return $factory($categoryId, $countryCode);
    }
}
