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

require_once(DIR_MAGNALISTER_MODULES.'otto/OttoHelper.php');
require_once(DIR_MAGNALISTER_MODULES.'otto/prepare/OttoIndependentAttributes.php');

class OttoCategoryMatching {
    const OTTO_CAT_VALIDITY_PERIOD = 86400; # Nach welcher Zeit werden Kategorien ungueltig (Sekunden)
    /**
     * @var string
     */
    private $request = 'view';

    /**
     * @var integer
     */
    private $mpID = 0;

    /**
     * @var array|string[]
     */
    private $url;

    /**
     * @var string
     */
    protected $currentLanguageCode = '';

    public function __construct($request = 'view') {
        global $_url, $_MagnaSession;

        $this->currentLanguageCode = strtolower($_SESSION['language_code']);
        $this->request = $request;
        $this->url = $_url;
        $this->mpID = $_MagnaSession['mpID'];
        $this->marketplace = $_MagnaSession['currentPlatform'];
        $this->hasPlatformCol = true;
        $this->columns = MagnaDB::gi()->getTableColumns(TABLE_MAGNA_OTTO_CATEGORIES_MARKETPLACE);
    }

    public function renderMatching() {
        return $this->renderView();
    }

    public function renderView() {
        $result = MagnaDB::gi()->fetchRow("SELECT Expires FROM ".TABLE_MAGNA_OTTO_CATEGORIES_MARKETPLACE." ORDER BY Expires DESC LIMIT 1");
        if ($result) {
            $date = new DateTime($result['Expires']);
            $date->modify("-1 day");
            $lastImportTime = $date->format("F d, Y, H:i a");
        }
        $html = '<div id="ottoCategorySelector" data-lastimport="'.$lastImportTime.'" class="dialog2" title="'.ML_OTTO_LABEL_SELECT_CATEGORY.'">
                <div class="ml-searchable-select ml-category-selecr2-search" lang="'.$this->currentLanguageCode.'" >
                    <select id="slect2OttoCategory" name="ottoCategorySelect2">
                        <option selected disabled>
                            <i class="select2-search-image"></i> '.ML_OTTO_LABEL_SELECT_CATEGORY_PLACEHOLDER.'
                        </option>
                    </select>
                </div>
				<div id="messageDialog" class="dialog2"></div>
			</div>
		';

        if ($this->url['view'] == 'varmatch') {
            $html .= '<form method="post" id="matchingForm" action="magnalister.php?mp='.$this->url['mp'].'&mode=prepare&view=varmatch">';
            $html .= '<input type="hidden" value="false" name="pID" id="pID"/>';
            $html .= '<table class="attributesTable">';
            $html .= OttoIndependentAttributes::renderIndependentAttributesTable();
            $html .= '</table>';
        }

        ob_start();
        ?>
        <script type="text/javascript">/*<![CDATA[*/
            $(document).ajaxStart(function () {
                // myConsole.log('ajaxStart');
                jQuery.blockUI(blockUILoading);
            }).ajaxStop(function () {
                // myConsole.log('ajaxStop');
                jQuery.unblockUI();
            });
            $("#slect2OttoCategory").select2({
                ajax: {
                    type: "POST",
                    delay: 250, // wait 250 milliseconds before triggering the request
                    url : "<?php echo toURL($this->url, array('where' => 'prepareView', 'kind' => 'ajax'), true);?>",
                    data: function (params) {
                        return {
                            'action': 'getOttoCategories',
                            'categoryfilterSearch': params.term,
                            'categoryfilterPage': params.page || 1,
                        };
                    },
                    dataType: 'json'
                }
            });
            var selectedOttoCategory = '';
            var madeChanges = false;
            var isStoreCategory = false;

            function addOttoCategoriesEventListener(elem) {
                $('div.catelem span.toggle:not(.leaf)', $(elem)).each(function () {
                    $(this).click(function () {
                        myConsole.log($(this).attr('id'));
                        if ($(this).hasClass('plus')) {
                            tmpElem = $(this);
                            tmpElem.removeClass('plus').addClass('minus');

                            if (tmpElem.parent().children('div.catname').children('div.catelem').length == 0) {
                                jQuery.ajax({
                                    type: 'POST',
                                    url: '<?php echo toURL($this->url, array('where' => 'prepareView', 'kind' => 'ajax'), true);?>',
                                    data: {
                                        'action': 'getOttoCategories',
                                        'objID': tmpElem.attr('id'),
                                        'isStoreCategory': isStoreCategory
                                    },
                                    success: function (data) {
                                        appendTo = tmpElem.parent().children('div.catname');
                                        appendTo.append(data);
                                        addOttoCategoriesEventListener(appendTo);
                                        appendTo.children('div.catelem').css({display: 'block'});
                                    },
                                    error: function () {
                                    },
                                    dataType: 'html'
                                });
                            } else {
                                tmpElem.parent().children('div.catname').children('div.catelem').css({display: 'block'});
                            }
                        } else {
                            $(this).removeClass('minus').addClass('plus');
                            $(this).parent().children('div.catname').children('div.catelem').css({display: 'none'});
                        }
                    });
                });
                $('div.catelem span.toggle.leaf', $(elem)).each(function () {
                    $(this).click(function () {
                        clickOttoCategory($(this).parent().children('div.catname').children('span.catname'));
                    });
                    $(this).parent().children('div.catname').children('span.catname').each(function () {
                        $(this).click(function () {
                            clickOttoCategory($(this));
                        });
                        if ($(this).parent().attr('id') == selectedOttoCategory) {
                            $(this).addClass('selected').css({'font-weight': 'bold'});
                        }
                    });
                });
            }

            function startCategorySelector(callback) {

                $('#ottoCategorySelector').jDialog({
                    width: '450px',
                    buttons: [
                        {
                            "text": "<?php echo ML_BUTTON_LABEL_ABORT; ?>",
                            "class": 'mlbtnreset',
                            "click": function () {
                                $(this).dialog("close");
                            }
                        },
                        {
                            "text": "<?php echo ML_BUTTON_LABEL_OK; ?>",
                            "class": 'mlbtnok',
                            "click": function () {
                                var select2Value = $("#slect2OttoCategory").select2('data');
                                var eSelect = $('#PrimaryCategory');
                                if ($("#slect2OttoCategory").val() !== null) {
                                    if (eSelect.find("option[value=" + select2Value[0].id + "]").length == 0) {
                                        eSelect.append('<option value="' + select2Value[0].id + '">' + select2Value[0].text + '</option>');
                                    }
                                    eSelect.val(select2Value[0].id).change();
                                    $(this).dialog("close");
                                } else {
                                    $('#messageDialog').html(
                                        '<?php echo ML_OTTO_LABEL_SELECT_CATEGORY_POPUP_WARNING; ?>'
                                    ).jDialog({
                                        title: '<?php echo ML_LABEL_NOTE; ?>'
                                    });
                                }

                            }
                        }
                    ],
                    open: function (event, ui) {
                        var tbar = $('#ottoCategorySelector').parent().find('.ui-dialog-titlebar');
                        if (tbar.find('.ui-icon-arrowrefresh-1-n').length == 0) {
                            var rlBtn = $('<span class="last-sync-category">Data synchronized: <br> ' +
                                '<span class="last-category-import-date"></span> ' +
                                '</span>' +
                                '<a class="ui-dialog-titlebar-close ui-corner-all ui-state-focus" ' +
                                'role="button" href="#" style="right: 2em; padding: 0px;">' +
                                '<span class="ui-icon ui-icon-arrowrefresh-1-n">reload</span>' +
                                '</a><br><br>')
                            tbar.append(rlBtn);
                            rlBtn.click(function (event) {
                                event.preventDefault();
                                importOttoCategories()
                            });
                        }
                    }
                });
                $('.last-category-import-date').text($('#ottoCategorySelector').data('lastimport'));
            }

            function importOttoCategories() {
                $.ajax({
                    type: 'get',
                    url: '<?php echo toURL($this->url, array('do' => 'ImportCategories', 'kind' => 'ajax', 'MLDEBUG' => 'true'), true);?>',
                    success: function (data) {
                        $.ajax({
                            type: "POST",
                            url: '<?php echo toURL($this->url, array('where' => 'prepareView', 'kind' => 'ajax'), true);?>',
                            data:  {
                                'action': 'updateImportDate',
                            },
                            dataType: 'json',
                            success: function (data) {
                                $('.last-category-import-date').text(data);
                            }
                        });
                    },
                });
            }

            // new 20190131
            var mpCategorySelector = (function () {
                return {
                    addCategoriesEventListener: addOttoCategoriesEventListener,
                    getCategoryPath: function (e) {
                        e.html(finalOttoCategoryPath);
                    },
                    startCategorySelector: startCategorySelector
                }
            })();

            // end new 20190131
            $(document).ready(function () {
                //addOttoCategoriesEventListener($('#ottoCats'));
                mpCategorySelector.addCategoriesEventListener($('#ottoCats')); // new 20190131
            });
            /*]]>*/</script>
        <?php
        $html .= ob_get_contents();
        ob_end_clean();

        return $html;
    }

    private function updateImportDate() {
        $lastImportTime = '';
        $result = MagnaDB::gi()->fetchRow("SELECT Expires FROM ".TABLE_MAGNA_OTTO_CATEGORIES_MARKETPLACE." ORDER BY Expires DESC LIMIT 1");
        if ($result) {
            $date = new DateTime($result['Expires']);
            $date->modify("-1 day");
            $lastImportTime = $date->format("F d, Y, H:i a");
        }

        return json_encode($lastImportTime);
    }

    private function getOttoCategories($categoryfilterSearch = '', $categoryfilterPage = 0) {

        $iCategoriesCount = (int)MagnaDB::gi()->fetchOne('SELECT COUNT(*) FROM '. TABLE_MAGNA_OTTO_CATEGORIES_MARKETPLACE);
        if ($iCategoriesCount == 0) {
            require_once(DIR_MAGNALISTER_MODULES.'otto/crons/OttoImportCategories.php');
            $oOIC = new OttoImportCategories($this->mpID, 'otto');
            $oOIC->process();
        }

        // Constraint result count, too big result sets can cause errors
        $iMaxRowsPerQuery = 16384;
        $iRowOffset = 0;
        $results = array();
        $partResultsCount = 0;
        do {
            $sql = "SELECT SQL_CALC_FOUND_ROWS DISTINCT * FROM ". TABLE_MAGNA_OTTO_CATEGORIES_MARKETPLACE." LIMIT $iRowOffset, $iMaxRowsPerQuery";
            $partResults = MagnaDB::gi()->fetchArray($sql);
            $partResultsCount = (int)MagnaDB::gi()->foundRows();
            $results = array_merge($results, $partResults);
            $iRowOffset  += $iMaxRowsPerQuery;
        } while ($partResultsCount == $iMaxRowsPerQuery);

        foreach ($results as $aCategory) {
            // display only leaf categories (otto has only one leaf)
            if ($aCategory['LeafCategory'] == 1) {
                $aFinalCategories[] = array(
                    'id'   => $aCategory['CategoryID'],
                    'text' => html_entity_decode($aCategory['CategoryName'], null, 'UTF-8'),
                );
            }
        }

        $sSearch = $categoryfilterSearch;
        if (!empty($sSearch)) {
            foreach ($aFinalCategories as $sKey => &$aCategory) {
                if (stripos($aCategory['text'], $sSearch) === false) {
                    unset($aFinalCategories[$sKey]);
                }
            }
        }

        // Pagination
        $iLength = 50;
        $iPageLength = (int)$categoryfilterPage * $iLength;
        $iOffset = (($iPageLength) - $iLength);

        // response
        $result = array(
            'results' => array_slice($aFinalCategories, $iOffset, $iLength),
            'pagination' => array(
                'more' => (count($aFinalCategories) > $iPageLength) ? true : false,
            )
        );

        return json_encode($result);
    }

    public function getOttoCategoryName($catId) {
      $name = MagnaDB::gi()->fetchRow("SELECT CategoryName FROM `".TABLE_MAGNA_OTTO_CATEGORIES_MARKETPLACE."` WHERE CategoryID = '".$catId. "' ");
      if (!$name) {
          $name = '';
      } else {
          $name = $name['CategoryName'];
      }
      return $name;
    }

    private function getGambioBrands($brandfilterSearch = '', $brandfilterPage = 0) {
        $selectedIds = array();
        $and = '';
        $exists = false;

        if (isset($_POST['pID'])) {
            $sql = 'SELECT CategoryIndependentShopVariation
                FROM '.TABLE_MAGNA_OTTO_PREPARE.'
                WHERE products_id = '.$_POST['pID'].'';
            $dbResult = MagnaDB::gi()->fetchOne($sql);
            if ($dbResult) {
                $exists = true; 
            } else {
                $exists = false;
                $sql = 'SELECT ShopVariation
                          FROM '.TABLE_MAGNA_OTTO_VARIANTMATCHING.'
                         WHERE     MpId = '.$this->mpID.'
                         AND MpIdentifier = "category_independent_attributes"';
                $dbResult = MagnaDB::gi()->fetchOne($sql);
                if ($dbResult) $exists = true;
            }
        } else {
            $sql = 'SELECT ShopVariation
                      FROM '.TABLE_MAGNA_OTTO_VARIANTMATCHING.'
                     WHERE     MpId = '.$this->mpID.'
                     AND MpIdentifier = "category_independent_attributes"';
            $dbResult = MagnaDB::gi()->fetchOne($sql);
            if ($dbResult) $exists = true;
        }

        if ($exists) {
            foreach (json_decode($dbResult) as $key => $exist) {
                if ($key == 'Brand' && is_array($exist->Values)) {
                    foreach ($exist->Values as $key => $value) {
                        array_push($selectedIds, $value->Shop->Key);
                    }
                }
            }
            if (count($selectedIds)) {
                $and = 'AND manufacturers_id NOT IN ('.implode(',', $selectedIds).')';
            }
        }

        $sql = 'SELECT manufacturers_id, manufacturers_name 
            FROM '.TABLE_MANUFACTURERS.'
            WHERE manufacturers_id<>0
            '.$and.'
            ORDER BY manufacturers_name ASC';
        $results = MagnaDB::gi()->fetchArray($sql);

        foreach ($results as $aBrand) {
            $sBrands[] = array(
                'id'   => $aBrand['manufacturers_id'],
                'text' => html_entity_decode($aBrand['manufacturers_name'], null, 'UTF-8'),
            );
        }

        $aFinalBrands = array(
            array('text' => ML_OTTO_LABEL_MATCHING_OPTIONS, 'children' => array(
                array('id' => 'all', 'text' => 'All')
            )),
            array('text' => ML_OTTO_LABEL_SHOP_VALUES, 'children' => $sBrands),
        );

        $sSearch = $brandfilterSearch;
        $iOffset = 0;
        $iLength = 50;
        if (!empty($sSearch)) {
            $sBrands = array();
            foreach ($aFinalBrands[1]['children'] as $sKey => &$aBrand) {
                if (stripos($aBrand['text'], $sSearch) !== false) {
                    array_push($sBrands, $aBrand);
                }
            }
            $aBrandCount = count($aFinalBrands[1]['children']);
            $aFinalBrands[1]['children'] = array_slice($sBrands, $iOffset, $iLength);
        }

        // Pagination
        $iLength = 50;
        $iPageLength = (int)$brandfilterPage * $iLength;
        $iOffset = (($iPageLength) - $iLength);

        $aBrandCount = count($aFinalBrands[1]['children']);
        $aFinalBrands[1]['children'] = array_slice($aFinalBrands[1]['children'], $iOffset, $iLength);
        arrayEntitiesToUTF8($aFinalBrands[1]['children']);

        if(intval($brandfilterPage) !== 1) {
            $aBrandPage = array(array('text' => ML_OTTO_LABEL_OTTO_VALUES));
            $aBrandPage[0]['children'] = $aFinalBrands[1]['children'];

            $aFinalBrands = $aBrandPage;
        }

        // response
        $result = array(
            'results' => $aFinalBrands,
            'pagination' => array(
                'more' => (count($aFinalBrands) > $iPageLength) ? true : false,
            )
        );

        return json_encode($result);
    }

    private function getOttoBrands($brandfilterSearch = '', $brandfilterPage = 0) {
        $brands = array();
        $brandCacheFile = DIR_MAGNALISTER_FS_CACHE.'ottoBrandCache.json';

        if (file_exists($brandCacheFile)) {
            $brands = json_decode(file_get_contents($brandCacheFile), true);
        } else {
            $result = MagnaConnector::gi()->submitRequest(array(
                'SUBSYSTEM' => 'otto',
                'ACTION' => 'GetCategoryIndependentAttributes'
            ));

            foreach ($result['DATA']['attributes'] as $key => $value) {
                if ($value['mandatory']) {
                    $requiredAttributes[$key] = $value;
                    if($value['name'] == 'Brand') {
                        $brandCacheFile = DIR_MAGNALISTER_FS_CACHE.'ottoBrandCache.json';
                        file_put_contents($brandCacheFile, json_encode($value['values']));
                    } 
                }
            }
            $brands = json_decode(file_get_contents($brandCacheFile), true);
        }

        foreach ($brands as $brandId => $brandName) {
            $ottoBrands[] = array(
                'id'   => $brandId,
                'text' => html_entity_decode($brandName, null, 'UTF-8'),
            );
        }

        $aFinalBrands = array(
            array('text' => ML_OTTO_LABEL_MATCHING_OPTIONS, 'children' => array(
                array('id' => 'auto', 'text' => 'Auto-match'),
                array('id' => 'auto', 'text' => 'Undo-matching')
            )),
            array('text' => ML_OTTO_LABEL_OTTO_VALUES, 'children' => $ottoBrands),
        );

        $sSearch = $brandfilterSearch;
        if (!empty($sSearch)) {
            $sBrands = array();
            foreach ($aFinalBrands[1]['children'] as $sKey => &$aBrand) {
                if (stripos($aBrand['text'], $sSearch) !== false) {
                    array_push($sBrands, $aBrand);
                }
            }
            $aBrandCount = count($aFinalBrands[1]['children']);
            $aFinalBrands[1]['children'] = array_slice($sBrands, $iOffset, $iLength);
        }

        // Pagination
        $iLength = 50;
        $iPageLength = intval($brandfilterPage) * $iLength;
        $iOffset = (($iPageLength) - $iLength);

        $aBrandCount = count($aFinalBrands[1]['children']);
        $aFinalBrands[1]['children'] = array_slice($aFinalBrands[1]['children'], $iOffset, $iLength);

        if(intval($brandfilterPage) !== 1) {
            $aBrandPage = array(array('text' => ML_OTTO_LABEL_OTTO_VALUES));
            $aBrandPage[0]['children'] = $aFinalBrands[1]['children'];

            $aFinalBrands = $aBrandPage;
        }

        // response
        $result = array(
            'results' => $aFinalBrands,
            'pagination' => array(
                'more' => ($aBrandCount > $iPageLength) ? true : false,
            )
        );

        return json_encode($result);
    }

    public function render() {
        if ($this->request == 'ajax') {
            return $this->renderAjax();
        } else {
            return $this->renderView();
        }
    }

    public function renderAjax() {
        $id = '';
        if (isset($_POST['id']) && $_POST['id'] !== 'false') {
            if (($pos = strrpos($_POST['id'], '_')) !== false) {
                $id = substr($_POST['id'], $pos + 1);
            } else {
                $id = $_POST['id'];
            }
        }

        $this->isStoreCategory = false;

        switch ($_POST['action']) {
            case 'getOttoCategories':
            {
                return $this->getOttoCategories(
                    isset($_POST['categoryfilterSearch']) ? $_POST['categoryfilterSearch'] : '',
                    isset($_POST['categoryfilterPage']) ? $_POST['categoryfilterPage'] : 0
                );
            }
            case 'updateImportDate':
            {
                return $this->updateImportDate();
            }
            case 'getGambioBrands':
            {
                return $this->getGambioBrands(
                    isset($_POST['brandfilterSearch']) ? $_POST['brandfilterSearch'] : '',
                    isset($_POST['brandfilterPage']) ? $_POST['brandfilterPage'] : 0
                );
            }
            case 'getOttoBrands':
            {
                return $this->getOttoBrands(
                    isset($_POST['brandfilterSearch']) ? $_POST['brandfilterSearch'] : '',
                    isset($_POST['brandfilterPage']) ? $_POST['brandfilterPage'] : 0
                );
            }
            case 'GetMpCategoryAttributes':
                {
                    if (isset($_POST['cId'])) {
                        return json_encode(OttoHelper::gi()->getAttributesFromMP($_POST['cId']));
                    } else {
                        return '';
                    }
                }
            case 'saveCategoryMatching':
                {
                    if (!isset($_POST['selectedShopCategory']) || empty($_POST['selectedShopCategory']) ||
                        (isset($_POST['selectedOttoCategories']) && !is_array($_POST['selectedOttoCategories']))
                    ) {
                        return json_encode(array(
                            'debug' => var_dump_pre($_POST['selectedOttoCategories'], true),
                            'error' => preg_replace('/\s\s+/', ' ', ML_HOOD_ERROR_SAVING_INVALID_HOOD_CATS)
                        ));
                    }

                    $cID = str_replace('s_select_', '', $_POST['selectedShopCategory']);
                    if (!ctype_digit($cID)) {
                        return json_encode(array(
                            'debug' => var_dump_pre($cID, true),
                            'error' => preg_replace('/\s\s+/', ' ', ML_HOOD_ERROR_SAVING_INVALID_SHOP_CAT)
                        ));
                    }

                    if (isset($_POST['selectedOttoCategories']) && !empty($_POST['selectedOttoCategories'])) {
                        $ottoIDs = array();
                        foreach ($_POST['selectedOttoCategories'] as $tmpYID) {
                            $tmpYID = str_replace('y_select_', '', $tmpYID);
                            if (preg_match('/^[0-9]{2}-[0-9]{2}-[0-9]{2}$/', $tmpYID)) {
                                $ottoIDs[] = $tmpYID;
                            }
                        }
                        if (empty($ottoIDs)) {
                            return json_encode(array(
                                'error' => preg_replace('/\s\s+/', ' ', ML_HOOD_ERROR_SAVING_INVALID_HOOD_CATS_ALL)
                            ));
                        }
                    }

                    return json_encode(array(
                        'error' => ''
                    ));
                }
            default:
                {
                    return json_encode(array(
                        //'error' => ML_HOOD_ERROR_REQUEST_INVALID
                        'error' => $_POST['action']
                    ));
                }
        }
    }
}
