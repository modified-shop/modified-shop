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

require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/MarketplaceCategoryMatching.php');

class GoogleshoppingCategoryMatching extends MarketplaceCategoryMatching {


    protected function getTableName() {
        return TABLE_MAGNA_GOOGLESHOPPING_CATEGORIES;
    }

    public function getMPCategory($categoryID, $secondCall = false) {
        $mpID = '0';

        $yCP = MagnaDB::gi()->fetchRow(eecho('
			SELECT CategoryID, CategoryName, ParentID, Selectable
			  FROM '.$this->getTableName().'
			 WHERE CategoryID="'.$categoryID.'"
			       AND mpID="'.$mpID.'"
			       '.($this->hasPlatformCol ? 'AND platform="'.$this->marketplace.'"' : '').'
			 LIMIT 1
		'));

        if ($yCP === false) {
            if (!$secondCall) {
                return $this->getMPCategory($categoryID, true);
            }

            return false;
        }

        return $yCP;
    }

    protected function getMPCategories($parentID = 0, $purge = false) {
        if ($purge) {
            $where = array (
                'mpID' => '0'
            );
            if ($this->hasPlatformCol) {
                $where['platform'] = $this->marketplace;
            }
            MagnaDB::gi()->delete($this->getTableName(), $where);
        }
        $validTo = gmdate('Y-m-d H:i:s', time() - $this->getCategoryValidityPeriod());

        $mpCategories = MagnaDB::gi()->fetchArray('
		    SELECT DISTINCT CategoryID, CategoryName,
		           ParentID, LeafCategory, Selectable
		      FROM '.$this->getTableName().'
		     WHERE ParentID="'.$parentID.'"
		           AND mpID="0"
		           '.($this->hasPlatformCol ? 'AND platform="'.$this->marketplace.'"' : '').'
		           AND InsertTimestamp > "'.$validTo.'" AND Language = "'.$_SESSION['language_code'].'"
		  ORDER BY CategoryName ASC
		');
        # nichts gefunden? vom Server abrufen
        if (empty($mpCategories) && $this->loadMPCategories($parentID)) {
            # Wenn Daten bekommen, noch mal select
            $mpCategories = MagnaDB::gi()->fetchArray('
			    SELECT DISTINCT CategoryID, CategoryName,
			           ParentID, LeafCategory, Selectable
			      FROM '.$this->getTableName().'
			     WHERE ParentID="'.$parentID.'"
			           AND mpID="0"
			           '.($this->hasPlatformCol ? 'AND platform="'.$this->marketplace.'"' : '').' AND Language = "'.$_SESSION['language_code'].'"
			  ORDER BY CategoryName ASC
			');
        }

        if (empty($mpCategories)) {
            return false;
        }
        return $mpCategories;
    }

    private function loadMPCategories($parentID) {
        try {
            $categories = MagnaConnector::gi()->submitRequest(array(
                'ACTION' => 'GetChildCategories',
                'DATA' => array('ParentID' => $parentID)
            ));
        } catch (MagnaException $e) {
            $categories = array(
                'DATA' => false
            );
        }
        if (!is_array($categories['DATA']) || empty($categories['DATA'])) {
            return false;
        }
        // echo print_m($categories);
        # Cast both to string because PHP thinks 'X' == 0 is true.
        if ($parentID.'' == (0).'') {
            # Tabelle leeren, wenn oberste Ebene abgefragt
            $w = array (
                'mpID' => '0',
                'Language' => $_SESSION['language_code'],
            );
            if ($this->hasPlatformCol) {
                $w['platform'] = $this->marketplace;
            }
            MagnaDB::gi()->delete($this->getTableName(), $w);
        }
        $this->insertMpCategories($categories['DATA']);
        return true;
    }
}
