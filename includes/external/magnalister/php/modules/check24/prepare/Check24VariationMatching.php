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
 * (c) 2010 - 2016 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
// äöüß

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
defined('TABLE_MAGNA_CHECK24_VARIANTMATCHING') OR define('TABLE_MAGNA_CHECK24_VARIANTMATCHING', 'magnalister_check24_variantmatching');

require_once(DIR_MAGNALISTER_MODULES . 'magnacompatible/prepare/VariationMatching.php');
require_once(DIR_MAGNALISTER_MODULES . 'check24/Check24Helper.php');
require_once(DIR_MAGNALISTER_MODULES.'check24/classes/Check24TopTenCategories.php');


class Check24VariationMatching extends VariationMatching
{
    /**
     * @return Check24Helper
     */
    protected function getAttributesMatchingHelper()
    {
        return Check24Helper::gi();
    }
    public function renderAjax($independentShopVariation = false) {
        if (isset($_GET['where']) && ($_GET['where'] == 'prepareView')
                && isset($_GET['view']) && ($_GET['view'] == 'varmatch')) {

        } else {

            if (isset($_GET['where']) && ($_GET['where'] == 'catMatchView')) {
                if ($this->oCategoryMatching) {

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
                    $independentAttributesClass = new Check24IndependentAttributes;
                    $independentAttributes = $independentAttributesClass->getCategoryIndependentAttributes();
                    echo json_encode(Check24Helper::gi()->getCategoryIndependentAttributes($independentAttributes, $_POST['SelectValue'], true, true));
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

    public function process()
    {
        $this->saveMatching();
        echo $this->renderJs();
        echo $this->renderMatchingTable('1');
    }

    protected function renderMatchingTable($categoryId = '')
    {
        return $this->getAttributesMatchingHelper()->renderMatchingTable($this->resources['url'],
                $this->renderCategoryOptions('MarketplaceCategories', $categoryId));
    }

    public function renderCategoryOptions($sType, $sCategory)
    {
            $opt = '<option value="' . 1 . '"' . (' selected="selected"') . '>' . 1 . '</option>' ;
        return $opt;
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
                handleCategoryChange: <?php echo  'true' ?>,
                i18n: <?php echo json_encode($this->getAttributesMatchingHelper()->getVarMatchTranslations());?>,
                shopVariations: <?php echo json_encode($this->getAttributesMatchingHelper()->getShopVariations()); ?>
            };
        </script>
        <?php

        return ob_get_clean();
    }

    public function renderMatching() {
        return $this->renderView();
    }
    public static function renderIndependentAttributesTable() {

        $html = '<tr class="headline">
        <td class="ottoDarkGreyBackground" colspan="3"><h4>'.ML_OTTO_CATEGORY_INDEPENDENT_ATTRIBUTES.'</h4>
            <p>'.ML_OTTO_CATEGORY_INDEPENDENT_ATTRIBUTES_INFO.'</p>
        </td>
        </tr>
        <tbody id="tbodyDynamicIndependentMatchingHeadline">
                <tr class="even">
                    <th class="ottoGreyBackground"><h4>'.ML_CHECK24_CATEGORY_INDEPENDENT_ATTRIBUTES_REQUIRED.'</h4></th>
                    <td class="ottoGreyBackground" colspan="3"><h4>'.ML_CHECK24_CATEGORY_INDEPENDENT_ATTRIBUTES_REQUIRED_INFO.'</h4></td>
                </tr>
        </tbody>
        <tbody id="tbodyDynamicIndependentMatchingInput">
            <tr>
                <th></th>
                <td class="input">'.ML_GENERAL_VARMATCH_SELECT_CATEGORY.'</td>
                <td class="info"></td>
            </tr>
        </tbody>
        <tbody id="tbodyDynamicIndependentMatchingOptionalHeadline">
                <tr class="even">
                    <th class="ottoGreyBackground" ><h4>'.ML_CHECK24_CATEGORY_INDEPENDENT_ATTRIBUTES_OTIONAL.'</h4></th>
                    <td class="ottoGreyBackground" colspan="3"><h4>'.ML_CHECK24_CATEGORY_INDEPENDENT_ATTRIBUTES_OTIONAL_INFO.'</h4></td>
                </tr>
                </tbody>
                <tbody id="tbodyDynamicIndependentMatchingOptionalInput">
                    <tr>
                        <th></th>
                        <td class="input">'.ML_GENERAL_VARMATCH_SELECT_CATEGORY.'</td>
                        <td class="info"></td>
                    </tr>
                </tbody>';

        return $html;
    }
    public function renderView() {
        $html = '<div id="check24CategorySelector"  class="dialog2" title="'.ML_OTTO_LABEL_SELECT_CATEGORY.'">        
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
            $html .= $this->renderIndependentAttributesTable();
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

                $('#check24CategorySelector').jDialog({
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
                        var tbar = $('#check24CategorySelector').parent().find('.ui-dialog-titlebar');
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
                $('.last-category-import-date').text($('#check24CategorySelector').data('lastimport'));
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

    protected function getCategoryMatchingHandler() {
        return new Cehck24CategoryMatching();
    }



    protected function saveMatching($redirect = true)
    {



        if (isset($_POST['ml']['match'])) {

            $sIdentifier = $_POST['PrimaryCategory'];
            $sCustomIdentifier = isset($_POST['CustomIdentifier']) ? $_POST['CustomIdentifier'] : '';
            $matching = $_POST['ml']['match'];
            $iMatching['ShopVariation'] = $_POST['ml']['match']['CategoryIndependentShopVariation'];

//            MagnaDB::gi()->delete($this->getVariantMatchingTableName(), array(
//                'MpId' => $this->mpId,
//                'MpIdentifier' => $sIdentifier,
//                'CustomIdentifier' => $sCustomIdentifier,
//            ));

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

    protected function getTopTenCategoriesHandler() {
        return new Check24TopTenCategories();
    }

}
