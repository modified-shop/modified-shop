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

require_once DIR_MAGNALISTER_INCLUDES.'lib/classes/ProductList/Dependency/MLProductListDependency.php';

class MLProductListDependencyGoogleshoppingApplyFormAction extends MLProductListDependency {
    public function getActionBottomLeftTemplate() {
        return 'googleshoppingapplyformleft';
    }

    public function getActionBottomRightTemplate() {
        return 'googleshoppingapplyformright';
    }

    public function getDefaultConfig() {
        return array(
            'selectionname' => 'general'
        );
    }

    public function executeAction() {
        $aRequest = $this->getActionRequest();
        if (isset($aRequest['removeapply'])) {
            $this->removeApply();
        } elseif (isset($aRequest['resetapply'])) {
            $this->resetApply();
        }
        return $this;
    }

    protected function removeApply() {
        $pIDs = MagnaDB::gi()->fetchArray('
			SELECT pID 
			 FROM '.TABLE_MAGNA_SELECTION.'
			WHERE mpID = \''.$this->getMagnaSession('mpID').'\'
			      AND selectionname = \''.$this->getConfig('selectionname').'\'
			      AND session_id = \''.session_id().'\'
		', true);
        if (!empty($pIDs)) {
            foreach ($pIDs as $pID) {
                $aProduct = MLProduct::gi()->setLanguage(getDBConfigValue('googleshopping.lang', $this->getMagnaSession('mpID'), $_SESSION['magna']['selected_language']))->getProductById($pID);
                $sKeyType = (getDBConfigValue('general.keytype', '0') == 'artNr' ? 'MarketplaceSku' : 'VariationId');
                $aProducts = array((($sKeyType == 'MarketplaceSku') ? $aProduct['ProductsModel'] : $aProduct['ProductId']));
                foreach ($aProduct['Variations'] as $aVariant) {
                    $aProducts[] = $aVariant[$sKeyType];
                }
                $sAdd = " AND products_".(($sKeyType == 'MarketplaceSku') ? 'model' : 'id')." in ('".implode("', '", $aProducts)."')";
                $where = array('mpID' => $this->getMagnaSession('mpID'), 'PrepareType' => 'apply');
                $res = MagnaDB::gi()->delete(TABLE_MAGNA_GOOGLESHOPPING_PREPARE, $where, $sAdd);
                MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
                    'pID' => $pID,
                    'mpID' => $this->getMagnaSession('mpID'),
                    'selectionname' => $this->getConfig('selectionname'),
                    'session_id' => session_id()
                ));
            }
        }
    }

    protected function resetApply() {
        $pIDs = MagnaDB::gi()->fetchArray('
			SELECT pID 
			  FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID = \''.$this->getMagnaSession('mpID').'\'
			       AND selectionname = \''.$this->getConfig('selectionname').'\'
			       AND session_id = \''.session_id().'\'
		', true);
        if (!empty($pIDs)) {
            if (getDBConfigValue('general.keytype', '0') == 'artNr') {
                $aProducts = MagnaDB::gi()->fetchArray('
					SELECT aa.products_id AS PID,
					       aa.products_model AS PModel,
					       aa.*
					  FROM '.TABLE_MAGNA_GOOGLESHOPPING_PREPARE.' aa
					 WHERE aa.products_id IN (\''.implode('\', \'', $pIDs).'\')
						 AND PrepareType=\'apply\'
			');
            } else {
                $aProducts = MagnaDB::gi()->fetchArray('
					SELECT p.products_id AS PID,
					       p.products_model AS PModel,
					       aa.*
					  FROM '.TABLE_MAGNA_GOOGLESHOPPING_PREPARE.' aa
				INNER JOIN '.TABLE_PRODUCTS.' p ON p.products_model = aa.products_model
					 WHERE     p.products_id IN (\''.implode('\', \'', $pIDs).'\')
						   AND aa.PrepareType=\'apply\'
				');
            }
            foreach ($aProducts as $aRow) {
                $aNewRow = $this->getArticleFromShop($aRow['PID']);
                if (isset($aNewRow) === false) {
                    break;
                }

                $where = (getDBConfigValue('general.keytype', '0') == 'artNr')
                    ? array('products_model' => $aRow['PModel'])
                    : array('products_id' => $aRow['PID']);
                $where['mpID'] = $this->getMagnaSession('mpID');
                $where['PrepareType'] = 'apply';

                MagnaDB::gi()->update(TABLE_MAGNA_GOOGLESHOPPING_PREPARE, array(
                    'products_id' => $aRow['PID'],
                    'products_model' => $aRow['PModel'],
                    'Title' => $aNewRow['Title'],
                    'Description' => $aNewRow['Description'],
                    'image' => $aNewRow['PictureURL'],
                ), $where);

                MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
                    'pID' => $aRow['PID'],
                    'mpID' => $this->getMagnaSession('mpID'),
                    'selectionname' => $this->getConfig('selectionname'),
                    'session_id' => session_id()
                ));
            }
        }
    }

    private function getArticleFromShop($iProductId) {
        $langID = getDBConfigValue($this->aMagnaSession['currentPlatform'].'.lang', $this->aMagnaSession['mpID']);

        $prod = MagnaDB::gi()->fetchArray(
            '
			SELECT p.products_model,
				   pd.products_name as Title,
				   '.(MagnaDB::gi()->columnExistsInTable('products_short_description', TABLE_PRODUCTS_DESCRIPTION) ? 'pd.products_short_description' : '"" AS Subtitle').',
				   pd.products_description as Description
			  FROM '.TABLE_PRODUCTS.' p
		 LEFT JOIN '.TABLE_PRODUCTS_DESCRIPTION.' pd ON pd.products_id = p.products_id AND pd.language_id = "'.$langID.'"
			 WHERE p.products_id = '.$iProductId
        );

        if (empty($prod)) {
            return;
        }

        $product = array();
        $product['Title'] = $prod[0]['Title'];
        $product['Description'] = $prod[0]['Description'];

        $aProduct = MLProduct::gi()->setLanguage($langID)->getProductById($iProductId);
        $images = array();

        foreach ($aProduct['Images'] as $image) {
            $images[$image] = 'true';
        }

        $product['PictureURL'] = json_encode($images);
        if (empty($product)) {
            return;
        }

        return $product;
    }
}
