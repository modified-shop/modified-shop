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

require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/productIdFilter/AbstractProductIdFilter.php');

class PreparedGoogleshoppingFilter extends AbstractProductIdFilter {

    /**
     * already setted product-ids
     * @var array
     */
    protected $aCurrentIds = null;

    public function __construct() {
    }

    public function getUrlParams() {
        return array();
    }

    public function getHtml() {
        return '';
    }

    public function setCurrentIds($aIds) {
        $this->aCurrentIds = $aIds;
        return $this;
    }

    public function getProductIds() {
        global $_MagnaSession;

        // To increase the performace add an index to products.products_model
        // ALTER TABLE `products` ADD INDEX `products_model` ( `products_model` )

        $sSql = "
			SELECT DISTINCT p.products_id
			  FROM ".TABLE_PRODUCTS." p
		INNER JOIN ".TABLE_MAGNA_GOOGLESHOPPING_PREPARE." ep on ".(
            (getDBConfigValue('general.keytype', '0') == 'artNr')
                ? 'p.products_model = ep.products_model'
                : 'p.products_id = ep.products_id'
            )."
			 WHERE ep.Verified = 'OK'
				   AND ep.mpID = '".$_MagnaSession['mpID']."'
		";

        // Makes simple filters perform worse
        //        ($this->aCurrentIds === null ? '' : " AND p.products_id in('" . implode("', '", $this->aCurrentIds) . "')")

        return MagnaDB::gi()->fetchArray($sSql, true);
    }

    public function init($aConfig) {
        return $this;
    }

    public function isActive() {
        return true;
    }
}
