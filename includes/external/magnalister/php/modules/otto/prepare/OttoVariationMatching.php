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

defined('TABLE_MAGNA_OTTO_VARIANTMATCHING') OR define('TABLE_MAGNA_OTTO_VARIANTMATCHING', 'magnalister_otto_variantmatching');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/prepare/VariationMatching.php');
require_once(DIR_MAGNALISTER_MODULES.'otto/OttoHelper.php');
require_once(DIR_MAGNALISTER_MODULES.'otto/prepare/OttoCategoryMatching.php');
require_once(DIR_MAGNALISTER_MODULES.'otto/classes/OttoTopTenCategories.php');
require_once(DIR_MAGNALISTER_MODULES.'otto/prepare/OttoIndependentAttributes.php');

class OttoVariationMatching extends VariationMatching {
    public function renderAjax($independentShopVariation = false) {
        if (isset($_GET['where']) && ($_GET['where'] == 'prepareView')
            && isset($_GET['view']) && ($_GET['view'] == 'varmatch')) {
            $this->oCategoryMatching = $this->getCategoryMatchingHandler();
            echo $this->oCategoryMatching->renderAjax();
        } else {
            $this->oCategoryMatching = $this->getCategoryMatchingHandler();
            if (isset($_GET['where']) && ($_GET['where'] == 'catMatchView')) {
                if ($this->oCategoryMatching) {
                    echo $this->oCategoryMatching->renderAjax();
                }
            } else if (isset($_POST['Action']) && ($_POST['Action'] == 'LoadMPVariations')) {
                $select = $_POST['SelectValue'];
                $customIdentifier = !empty($_POST['CustomIdentifierValue']) ? $_POST['CustomIdentifierValue'] : '';
                $data = $this->getAttributesMatchingHelper()->getMPVariations($select, false, true, null, $customIdentifier);
                echo json_encode($data);
            } else if (isset($_POST['Action']) && ($_POST['Action'] == 'LoadCustomIdentifiers')) {
                $select = $_POST['SelectValue'];
                $data = $this->getAttributesMatchingHelper()->getCustomIdentifiers($select, false, true);
                echo json_encode($data);
            } else if (isset($_POST['Action']) && ($_POST['Action'] == 'SaveMatching')) {
                $params = array();
                parse_str_unlimited($_POST['Variations'], $params);
                $_POST = $params;
                $_POST['Action'] = 'SaveMatching';

                $this->saveMatching(false);

                if ($independentShopVariation) {
                    $independentAttributesClass = new OttoIndependentAttributes;
                    $independentAttributes = $independentAttributesClass->getCategoryIndependentAttributes();
                    echo json_encode(OttoHelper::gi()->getCategoryIndependentAttributes($independentAttributes, $_POST['SelectValue'], true, true));
                } else {
                    $data = $this->getAttributesMatchingHelper()->getMPVariations($params['PrimaryCategory'], false, true, null, $params['CustomIdentifier']);

                    $data['notice'] = array();
                    foreach ($this->aErrors as $error) {
                        if (is_array($error) && ('notice' === $error['type'])) {
                            $data['notice'][] = $error['message'];
                        }
                    }

                    echo json_encode($data);
                }
            } else if (isset($_POST['Action']) && ($_POST['Action'] === 'DBMatchingColumns')) {
                $columns = MagnaDB::gi()->getTableCols($_POST['Table']);
                $editedColumns = array();
                foreach ($columns as $column) {
                    $editedColumns[$column] = $column;
                }

                echo json_encode($editedColumns, JSON_FORCE_OBJECT);
            }
        }
    }

    protected function renderJs()
    {
        ob_start();
        ?>
        <script type="text/javascript" src="<?php echo DIR_MAGNALISTER_WS ?>js/variation_matching.js?<?php echo CLIENT_BUILD_VERSION?>"></script>
        <script type="text/javascript" src="<?php echo DIR_MAGNALISTER_WS ?>js/marketplaces/<?php echo $this->marketplace?>/variation_matching.js?<?php echo CLIENT_BUILD_VERSION?>"></script>
        <script>
            var ml_vm_config = {
                url: '<?php echo toURL($this->resources['url'], array('where' => 'prepareView', 'kind' => 'ajax'), true);?>',
                viewName: 'varmatchView',
                formName: '#matchingForm',
                handleCategoryChange: <?php echo $this->getCategoryMatchingHandler() !== null ? 'true' : 'false'?>,
                i18n: <?php echo json_encode($this->getAttributesMatchingHelper()->getVarMatchTranslations());?>,
                shopVariations: <?php echo json_encode($this->getAttributesMatchingHelper()->getShopVariations()); ?>
            };
        </script>
        <?php
        return ob_get_clean();
    }

    protected function getCategoryMatchingHandler() {
        return new OttoCategoryMatching();
    }

    protected function getAttributesMatchingHelper() {
        return OttoHelper::gi();
    }

    protected function getTopTenCategoriesHandler() {
        return new OttoTopTenCategories();
    }

    public function process()
    {
        $this->oCategoryMatching = $this->getCategoryMatchingHandler();
        $categoryId = '';
        $this->saveMatching();

        if (count($this->aErrors) > 0) {
            $categoryId = $_POST['PrimaryCategory'];
        }

        $this->loadAvailableVariationGroups();
        echo $this->renderJs();
        if ($this->oCategoryMatching) {
            echo $this->oCategoryMatching->renderMatching();
        }

        echo $this->renderMatchingTable($categoryId);
    }

    protected function saveMatching($redirect = true)
    {
        if (isset($_POST['ml']['match'])) {
            $sIdentifier = $_POST['PrimaryCategory'];
            $sCustomIdentifier = isset($_POST['CustomIdentifier']) ? $_POST['CustomIdentifier'] : '';
            $matching = $_POST['ml']['match'];
            $iMatching['ShopVariation'] = $_POST['ml']['match']['CategoryIndependentShopVariation'];

            MagnaDB::gi()->delete($this->getVariantMatchingTableName(), array(
                'MpId' => $this->mpId,
                'MpIdentifier' => $sIdentifier,
                'CustomIdentifier' => $sCustomIdentifier,
            ));

            if (!isset($_POST['Action']) || $_POST['Action'] !== 'ResetMatching') {

                $this->aErrors = array_merge($this->aErrors,
                    $this->getAttributesMatchingHelper()->saveMatching('category_independent_attributes', $iMatching, $redirect, false, false, null, $sCustomIdentifier)
                );

                if ($sIdentifier != '') {
                    $this->aErrors = array_merge($this->aErrors,
                        $this->getAttributesMatchingHelper()->saveMatching($sIdentifier, $matching, $redirect, false, false, null, $sCustomIdentifier)
                    );
                }
            }

            if ($redirect) {
                if (!empty($this->aErrors)) {
                    foreach ($this->aErrors as $error) {
                        $errorCssClass = 'errorBox';
                        $errorMessage = $error;
                        if (is_array($error)) {
                            $errorCssClass = "{$error['type']}Box {$error['additionalCssClass']}";
                            $errorMessage = $error['message'];
                        }

                        echo '<p class="'.$errorCssClass.'">' . $errorMessage . '</p>';
                    }
                } else {
                    echo '<p class="successBox">' . ML_GENERAL_VARMATCH_SAVED_SUCCESSFULLY . '</p>';
                }
            }
        }
    }
}
