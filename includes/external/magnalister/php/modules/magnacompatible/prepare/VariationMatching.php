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
// äöüß

abstract class VariationMatching
{
    public $aErrors = array();
    protected $resources = array();
    protected $mpId = 0;
    protected $marketplace = '';
    protected $isAjax = false;
    protected $catMatch = null;
    protected $topTen = null;
    /** @var MarketplaceCategoryMatching $oCategoryMatching */
    protected $oCategoryMatching = null;
    protected $availableVariationConfigs = array();
    protected $availableCustomConfigs = array();
    protected $languageId = 0;

    public function __construct($params)
    {
        $this->resources = $params['resources'];

        $this->mpId = $this->resources['session']['mpID'];
        $this->marketplace = $this->resources['session']['currentPlatform'];

        $this->isAjax = isset($_GET['kind']) && ($_GET['kind'] == 'ajax');

        $this->languageId = getDBConfigValue($this->marketplace . '.keytype', $this->mpId, 2);
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

    protected function getCategoryMatchingHandler()
    {
        return null;
    }

    protected function saveMatching($redirect = true)
    {
        if (isset($_POST['ml']['match'])) {
            $sIdentifier = $_POST['PrimaryCategory'];
            $sCustomIdentifier = isset($_POST['CustomIdentifier']) ? $_POST['CustomIdentifier'] : '';
            $matching = $_POST['ml']['match'];
            MagnaDB::gi()->delete($this->getVariantMatchingTableName(), array(
                'MpId' => $this->mpId,
                'MpIdentifier' => $sIdentifier,
                'CustomIdentifier' => $sCustomIdentifier,
            ));

            if (!isset($_POST['Action']) || $_POST['Action'] !== 'ResetMatching') {
                $this->aErrors = array_merge($this->aErrors,
                    $this->getAttributesMatchingHelper()->saveMatching($sIdentifier, $matching, $redirect, false, false, null, $sCustomIdentifier)
                );
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

    protected function getVariantMatchingTableName()
    {
        return 'magnalister_' . $this->marketplace . '_variantmatching';
    }

    /**
     * @return AttributesMatchingHelper
     */
    protected abstract function getAttributesMatchingHelper();

    protected function loadAvailableVariationGroups()
    {
        $this->availableVariationConfigs = array();

        $availableCustomConfigs = MagnaDB::gi()->fetchArray('
			SELECT CustomIdentifier, MpIdentifier
			  FROM ' . $this->getVariantMatchingTableName() . '
			 WHERE MpId = ' . $this->mpId . '
			       AND CustomIdentifier<>""
		');
        if (!empty($availableCustomConfigs)) {
            foreach ($availableCustomConfigs as $cfg) {
                $this->availableCustomConfigs[$cfg['CustomIdentifier'] . ':' . ($cfg['MpIdentifier'] == '' ? 'null' : $cfg['MpIdentifier'])] = $cfg['CustomIdentifier'];
            }
            asort($this->availableCustomConfigs);
        }
        #echo print_m($this->availableCustomConfigs);
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

    protected function renderMatchingTable($categoryId = '')
    {
        return $this->getAttributesMatchingHelper()->renderMatchingTable($this->resources['url'],
            $this->renderCategoryOptions('MarketplaceCategories', $categoryId));
    }

    /**
     * Fetches the options for the top 20 category selectors
     * @param string $sType
     *     Type of category (PrimaryCategory, SecondaryCategory, StoreCategory, StoreCategory2, StoreCategory3)
     * @param string $sCategory
     *     the selected category (empty for newly prepared items)
     * @returns string
     *     option tags for the select element
     */
    protected function renderCategoryOptions($sType, $sCategory)
    {
        if ($this->topTen === null) {
            $this->topTen = $this->getTopTenCategoriesHandler();
            $this->topTen->setMarketPlaceId($this->mpId);
        }

        $aTopTenCatIds = $this->topTen->getTopTenCategories($sType, 'getMPCategoryPath');
        if (!empty($aTopTenCatIds)) {
            $opt = '<option value="">&mdash;</option>' . "\n";
        } else {
            $opt = '<option value=""> -- '.ML_GENERIC_USE_CATEGORY_BUTTON.' -- &gt; </option>'."\n";
        }

        if (!empty($sCategory) && !array_key_exists($sCategory, $aTopTenCatIds)) {
            $sCategoryName = $this->oCategoryMatching->getMPCategoryPath($sCategory);
            $opt .= '<option value="' . $sCategory . '" selected="selected">' . $sCategoryName . '</option>' . "\n";
        }

        foreach ($aTopTenCatIds as $sKey => $sValue) {
            $blSelected = (!empty($sCategory) && ($sCategory == $sKey));
            $opt .= '<option value="' . $sKey . '"' . ($blSelected ? ' selected="selected"' : '') . '>' . $sValue . '</option>' . "\n";
        }

        return $opt;
    }

    /**
     * @return TopTen
     */
    protected function getTopTenCategoriesHandler()
    {
        return null;
    }

    public function renderAjax()
    {
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
            $data = $this->getAttributesMatchingHelper()->getMPVariations($params['PrimaryCategory'], false, true, null, $params['CustomIdentifier']);

            $data['notice'] = array();
            foreach ($this->aErrors as $error) {
                if (is_array($error) && ('notice' === $error['type'])) {
                    $data['notice'][] = $error['message'];
                }
            }

            echo json_encode($data);
        } else if (isset($_POST['Action']) && ($_POST['Action'] === 'DBMatchingColumns')) {
            $columns = MagnaDB::gi()->getTableCols($_POST['Table']);
            $editedColumns = array();
            foreach ($columns as $column) {
                $editedColumns[$column] = $column;
            }

            echo json_encode($editedColumns, JSON_FORCE_OBJECT);
        }
    }

    protected function initCatMatching()
    {
        $params = array();
        foreach (array('mpID', 'marketplace', 'marketplaceName') as $attr) {
            if (isset($this->$attr)) {
                $params[$attr] = &$this->$attr;
            }
        }
    }
}
