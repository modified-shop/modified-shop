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

require_once(DIR_MAGNALISTER_INCLUDES . 'lib/classes/productIdFilter/DeletedInMarketplaceFilter.php');

class DeletedInEbayFilter extends DeletedInMarketplaceFilter {

    protected function getPropertiesTableName() {
        return TABLE_MAGNA_EBAY_PROPERTIES;
    }

    protected function getFilterValues() {
        return array(
            '' => ML_OPTION_FILTER_ARTICLES_ALL,
            'notActive' => ML_OPTION_FILTER_ARTICLES_NOTACTIVE,
            'notTransferred' => ML_OPTION_FILTER_ARTICLES_NOTTRANSFERRED_1YEAR,
            'active' => ML_OPTION_FILTER_ARTICLES_ACTIVE,
            'sync' => ML_OPTION_FILTER_ARTICLES_DELETEDBY_SYNC,
            'button' => ML_OPTION_FILTER_ARTICLES_DELETEDBY_BUTTON,
            'expired' => ML_OPTION_FILTER_ARTICLES_DELETEDBY_EXPIRED,
        );
    }

    public function getProductIds() {
        global $_MagnaSession;
        
        // Use an additional query to get unprepared items,
        // cos using LEFT JOIN leaves out all items prepared for other mpID's
        $sSql0 = "SELECT DISTINCT products_id
              FROM " . TABLE_PRODUCTS . "
             WHERE products_model NOT IN (SELECT products_model
                  FROM " . $this->getPropertiesTableName() . "
                 WHERE mpID = '" . $_MagnaSession['mpID'] . ")"; 
        $p0 = MagnaDB::gi()->fetchArray($sSql0, true);

        $sSql1 = "
            SELECT DISTINCT p.products_id
              FROM " . TABLE_PRODUCTS . " p, " . $this->getPropertiesTableName() . " ep
                     WHERE " . ((getDBConfigValue('general.keytype', '0') == 'artNr')
            ? 'p.products_model = ep.products_model'
            : 'p.products_id = ep.products_id'
        ) . "
             AND ep.mpID = '" . $_MagnaSession['mpID'] . "' 
        ";
        
        switch (strtolower($this->sFilter)) {
            case 'notactive' : {
                $sSql1 .= " AND ep.Verified in('OK', 'EMPTY') AND (ep.transferred='0' or ep.deletedBy!='')";
                break;
            }
            case 'nottransferred' : {
                $sSql1 .= " AND ep.Verified in('OK', 'EMPTY') AND ep.transferred='0'";
                break;
            }
            case 'active': {
                $sSql1 .= " AND ep.Verified in('OK', 'EMPTY') AND (ep.transferred='1' and ep.deletedBy='')";
                break;
            }
            case 'sync':
            case 'button':
            case 'expired': {
                $sSql1 .= " AND ep.Verified in('OK', 'EMPTY') AND ep.deletedBy='" . $this->sFilter . "'";
                break;
            }
            default: { // not possible value
                break;
            }
        }
        $p1 = MagnaDB::gi()->fetchArray($sSql1, true);
        return array_unique(array_merge($p0, $p1), SORT_NUMERIC);
    }

}
