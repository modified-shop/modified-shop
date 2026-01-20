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
 * $Id: prepare.php 3830 2014-05-06 13:00:00Z tim.neumann $
 *
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_MODULES.'check24/prepare/Check24IndependentAttributes.php');
require_once(DIR_MAGNALISTER_MODULES.'check24/Check24Helper.php');


class Check24Prepare extends MagnaCompatibleBase {

    protected $prepareSettings = array();

    public function __construct(&$params) {

        if (!empty($_POST['FullSerializedForm'])) {
            $newPost = array();
            parse_str_unlimited($_POST['FullSerializedForm'], $newPost);

            $_POST = array_merge($_POST, $newPost);
        }
        parent::__construct($params);

        $this->prepareSettings['selectionName'] = isset($_GET['view']) ? $_GET['view'] : 'prepare';
        $this->resources['url']['mode'] = 'prepare';
        $this->resources['url']['view'] = $this->prepareSettings['selectionName'];
        if ('apply' == $this->prepareSettings['selectionName']) $this->prepareSettings['selectionName'] = 'prepare';
    }



    protected function deleteMatching() {
        if (!(array_key_exists('unprepare', $_POST)) || empty($_POST['unprepare'])) {
            return;
        }
        $pIDs = MagnaDB::gi()->fetchArray('
			SELECT pID
			  FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID = "'.$this->mpID.'" AND
				   selectionname = "'.$this->prepareSettings['selectionName'].'" AND
				   session_id = "'.session_id().'"
		', true);
        if (empty($pIDs)) {
            return;
        }

        foreach ($pIDs as $pID) {
            $where = (getDBConfigValue('general.keytype', '0') == 'artNr')
                ? array ('products_model' => MagnaDB::gi()->fetchOne('
							SELECT products_model
							  FROM '.TABLE_PRODUCTS.'
							 WHERE products_id='.$pID
                ))
                : array ('products_id' => $pID);
            $where['mpID'] = $this->mpID;

            MagnaDB::gi()->delete(TABLE_MAGNA_CHECK24_PROPERTIES, $where);
            MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
                'pID' => $pID,
                'mpID' => $this->mpID,
                'selectionname' => $this->prepareSettings['selectionName'],
                'session_id' => session_id()
            ));
        }
        unset($_POST['unprepare']);
    }

    protected function processMatching($independentShopVariation) {

        #echo "<br />\n".__METHOD__."<br />\n";
        /*if ($this->prepareSettings['selectionName'] === 'match') {
            $className = 'MatchingPrepareView';
        } else*/
        if ($this->prepareSettings['selectionName'] === 'varmatch') {

            $className = 'VariationMatching';
        } else {

            $className = 'PrepareView';
        }

        if (($class = $this->loadResource('prepare', $className)) === false) {
            if ($this->isAjax) {
                echo '{"error": "This is not supported"}';
            } else {
                echo 'This is not supported';
            }

            return;
        }

        $params = array();
        foreach (array('mpID', 'marketplace', 'marketplaceName', 'resources', 'prepareSettings') as $attr) {
            if (isset($this->$attr)) {
                $params[$attr] = &$this->$attr;
            }
        }

        /** @var $cMDiag Check24PrepareView | Check24VariationMatching */
        $cMDiag = new $class($params);
        echo $this->isAjax ? $cMDiag->renderAjax($independentShopVariation) : $cMDiag->process();
    }


    protected function processSelection() {
        if (($class = $this->loadResource('prepare', 'PrepareCategoryView')) === false) {
            if ($this->isAjax) {
                echo '{"error": "'.__METHOD__.' This is not supported"}';
            } else {
                echo __METHOD__.' This is not supported';
            }
            return;
        }
        $pV = new $class(
            null,
            $this->prepareSettings,
            isset($_GET['sorting'])   ? $_GET['sorting']   : false,
            isset($_POST['tfSearch']) ? $_POST['tfSearch'] : ''
        );
        if ($this->isAjax) {
            echo $pV->renderAjaxReply();
        } else {
            echo $pV->printForm();
        }
    }

    protected function getSelectedProductsCount() {
        return (int)MagnaDB::gi()->fetchOne('
			SELECT COUNT(*)
			  FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID = '.$this->mpID.'
			       AND selectionname = "'.$this->prepareSettings['selectionName'].'"
			       AND session_id = "'.session_id().'"
		');
    }

    protected function processProductList($independentShopVariation) {

        if ($this->prepareSettings['selectionName'] === 'match') {

            $className = 'MatchingProductList';
        } elseif ($this->prepareSettings['selectionName'] === 'varmatch'

        ) {

            $this->processMatching($independentShopVariation);
            return;
        } else {

            $className = 'PrepareProductList';
        }

        if (($sClass = $this->loadResource('prepare', 'PrepareProductList')) === false) {
            if ($this->isAjax) {
                echo '{"error": "This is not supported"}';
            } else {
                echo 'This is not supported';
            }
            return;
        }
        $o = new $sClass();
        echo $o;
    }

    public function process() {

        if ((isset($_GET['mode']) && $_GET['mode'] == 'prepare')
            && (isset($_GET['view']) && ($_GET['view'] == 'apply' || $_GET['view'] == 'prepare' || $_GET['view'] == 'varmatch'))
            && (isset($_GET['kind']) && $_GET['kind'] == 'ajax')
            && (isset($_GET['where']) && ($_GET['where'] == 'Check24PrepareView' || $_GET['where'] == 'varmatchView'))
            && (isset($_POST['Action']) && $_POST['Action'] == 'LoadCategoryIndependentAttributes')) {
            $independentAttributesClass = new Check24IndependentAttributes;
            $independentAttributes = $independentAttributesClass->getCategoryIndependentAttributes();

            if ($_GET['where'] == 'varmatchView') {
                $model = true;
            } else {
                $model = Check24Helper::gi()->getProductModel('prepare');
            }

            die(json_encode(Check24Helper::gi()->getCategoryIndependentAttributes($independentAttributes, $_POST['SelectValue'], $model, true)));
        }


        $independentShopVariation = false;
        if (isset($_POST['VariationKind']) && $_POST['VariationKind'] === 'IndependentShopVariation') {
            $independentShopVariation = true;
        }
        /*
         * @todo
         */

        $this->savePrepare();
        $this->deletePrepare();

        if ((isset($_POST['prepare']) ||
                (isset($_GET['where']) && (($_GET['where'] == 'catMatchView') || ($_GET['where'] == 'prepareView') || ($_GET['where'] == 'varmatchView')))) && ($this->getSelectedProductsCount() > 0)
        ) {
            #echo "<br />\n".__METHOD__.' '.__LINE__."<br />\n";
            $this->processMatching($independentShopVariation);
        } else {
            if (defined('MAGNA_DEV_PRODUCTLIST') && MAGNA_DEV_PRODUCTLIST === true) {
                #echo "<br />\n".__METHOD__.' '.__LINE__."<br />\n";
                $this->processProductList($independentShopVariation);
            } else {
                #echo "<br />\n".__METHOD__.' '.__LINE__."<br />\n";
                $this->processSelection();
            }
        }
    }
    protected function deletePrepare() {
        if (!(array_key_exists('unprepare', $_POST)) || empty($_POST['unprepare'])) {
            return;
        }

        $pIDs = MagnaDB::gi()->fetchArray('
			SELECT pID FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID=\''.$this->mpID.'\' AND
			       selectionname=\''.$this->prepareSettings['selectionName'].'\' AND
			       session_id=\''.session_id().'\'
		', true);

        if (empty($pIDs)) {
            return;
        }
        foreach ($pIDs as $pID) {
            $where = (getDBConfigValue('general.keytype', '0') == 'artNr')
                ? array('products_model' => MagnaDB::gi()->fetchOne('
							SELECT products_model
							  FROM '.TABLE_PRODUCTS.'
							 WHERE products_id='.$pID
                ))
                : array('products_id' => $pID);
            $where['mpID'] = $this->mpID;

            MagnaDB::gi()->delete(TABLE_MAGNA_CHECK24_PROPERTIES, $where);
            MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
                'pID' => $pID,
                'mpID' => $this->mpID,
                'selectionname' => $this->prepareSettings['selectionName'],
                'session_id' => session_id()
            ));
        }
        unset($_POST['unprepare']);
    }
    protected function savePrepare() {

        if (!array_key_exists('savePrepareData', $_POST)) {
            if (!isset($_POST['Action']) || $_POST['Action'] !== 'SaveMatching' || $_GET['where'] === 'varmatchView') {
                return;
            }
        }
        require_once(DIR_MAGNALISTER_MODULES.'check24/classes/Check24ProductSaver.php');
        $oProductSaver = new Check24ProductSaver($this->resources['session']);
        $aProductIDs = MagnaDB::gi()->fetchArray("
			SELECT pID
			  FROM ".TABLE_MAGNA_SELECTION."
			 WHERE     mpID = '".$this->mpID."'
				   AND selectionname = '".$this->prepareSettings['selectionName']."'
				   AND session_id = '".session_id()."'
		", true);

        $isSinglePrepare = 1 == count($aProductIDs);
        $shopVariations = $this->saveMatchingAttributes($oProductSaver, $isSinglePrepare);
        $independentShopVariations = $this->saveIndependentMatchingAttributes($oProductSaver, $isSinglePrepare);
        $itemDetails = $_POST;
        $itemDetails['ShopVariation'] = $shopVariations;
        $itemDetails['CategoryIndependentShopVariation'] = json_encode($independentShopVariations);

        if ($isSinglePrepare) {
            $oProductSaver->saveSingleProductProperties($aProductIDs[0], $itemDetails);
        } else if (!empty($aProductIDs)) {
            $oProductSaver->saveMultipleProductProperties($aProductIDs, $itemDetails);
        }

        $savePrepareData = array_key_exists('savePrepareData', $_POST);

        if (count($oProductSaver->aErrors) === 0 || !$savePrepareData) {
            $isAjax = false;
            if (!$savePrepareData) {
                # stay on prepare product form
                $_POST['prepare'] = 'prepare';
                $isAjax = true;
            }
            if (!$isAjax) {
                # prepared successfully, remove from selection
                MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
                    'mpID' => $this->mpID,
                    'selectionname' => $this->prepareSettings['selectionName'],
                    'session_id' => session_id()
                ));
            }
        } else {
            # stay on prepare product form
            $_POST['prepare'] = 'prepare';

            if ($savePrepareData) {
                foreach ($oProductSaver->aErrors as $sError) {
                    echo '<div class="errorBox">'.$sError.'</div>';
                }
            }
        }
    }
    protected function saveIndependentMatchingAttributes($oProductSaver, $isSinglePrepare) {
        if (isset($_POST['Variations'])) {
            parse_str_unlimited($_POST['Variations'], $params);
            $_POST = $params;
        }

        $sIdentifier = 'category_independent_attributes';
        $matching = isset($_POST['ml']['match']) ? $_POST['ml']['match'] : false;
        $variationThemeAttributes = null;

        if (isset($_POST['variationTheme']) && $_POST['variationTheme'] !== 'null') {
            $variationThemes = json_decode($_POST['variationThemes'], true);
            $variationThemeAttributes = $variationThemes[$_POST['variationTheme']]['attributes'];
        }

        $savePrepare = true;

        $oProductSaver->aErrors = array_merge($oProductSaver->aErrors,
            Check24Helper::gi()->saveIndependentMatching($sIdentifier, $matching, $savePrepare, true, $isSinglePrepare, $variationThemeAttributes));
        return $matching['CategoryIndependentShopVariation'];
    }
    protected function saveMatchingAttributes($oProductSaver, $isSinglePrepare) {

        if (isset($_POST['Variations'])) {
            parse_str_unlimited($_POST['Variations'], $params);
            $_POST = $params;
        }

        $sIdentifier = $_POST['PrimaryCategory'];
        $matching = isset($_POST['ml']['match']) ? $_POST['ml']['match'] : false;
        $variationThemeAttributes = null;

        if (isset($_POST['variationTheme']) && $_POST['variationTheme'] !== 'null') {
            $variationThemes = json_decode($_POST['variationThemes'], true);
            $variationThemeAttributes = $variationThemes[$_POST['variationTheme']]['attributes'];
        }

        $savePrepare = true;

        $oProductSaver->aErrors = array_merge($oProductSaver->aErrors,
            Check24Helper::gi()->saveMatching(1, $matching, $savePrepare, true, $isSinglePrepare, $variationThemeAttributes));

        return json_encode($matching['ShopVariation']);
    }

}
