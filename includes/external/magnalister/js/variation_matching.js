(function($) {
    $.widget("ui.ml_variation_matching", {
        options: {
            urlPostfix: '&ajax=true',
            attributesNamePrefix: '[ShopVariation]',
            i18n: {},
            elements: {}
        },
        i18n: {
            defineName: 'defineName',
            ajaxError: 'ajaxError',
            selectVariantGroup: 'selectVariantGroup',
            pleaseSelect: 'pleaseSelect',
            allSelect: 'allSelect',
            autoMatching: 'autoMatching',
            resetMatching: 'resetMatching',
            manualMatching: 'manualMatching',
            matchingTable: 'matchingTable',
            resetInfo: 'resetInfo',
            shopValue: 'shopValue',
            mpValue: 'mpValue',
            webShopAttribute: 'webShopAttribute',
            chooseCategoryButton: 'chooseCategoryButton',
            beforeAttributeChange: 'beforeAttributeChange',
            mandatoryInfo: 'mandatoryInfo',
            buttonOk: 'OK',
            buttonCancel: 'Cancel',
            note: 'Hinweis',
            variations: 'Variations',
            attributeChangedOnMp: '',
            attributeDifferentOnProduct: '',
            attributeDeletedOnMp: '',
            attributeValueDeletedOnMp: '',
            categoryWithoutAttributesInfo: '',
            differentAttributesOnProducts: '',
            variationTheme: '',
            dbtable: 'dbtable',
            dbcolumn: 'dbcolumn',
            dbalias: 'dbalias',
            alreadyMatched: 'alreadyMatched',
            multiSelect: '',
            multiselectHint: ''
        },
        html: {
            shopVariationsDropDown: '',
            shopVariationsCustomDropDown: '',
            valuesBackup: '',
            valuesCustomBackup: ''
        },
        elements: {
            form: null,
            mainSelectElement: null,
            customIdentifierSelectElement: null,
            newGroupIdentifier: null, //hidden input for transport
            matchingHeadline: null,
            matchingOptionalHeadline: null,
            matchingCustomHeadline: null,
            matchingInput: null,
            matchingOptionalInput: null,
            matchingCustomInput: null,
            categoryInfo: null,
            customIdentifierWrapper: null
        },
        variationValues: {},
        optionalAttributesMaxSize: 5,
        multiSelectDataTypeCode: 'multiselect',
        multiSelectAndTextDataTypeCode: 'multiselectandtext',
        attributesNamePrefix: null,

        _init: function() {
            var self = this,
                i;

            // Reset configuration
            self.attributesNamePrefix = self.options.attributesNamePrefix;
            self.html = {
                shopVariationsDropDown: '',
                shopVariationsCustomDropDown: '',
                valuesBackup: '',
                valuesCustomBackup: ''
            };

            self.elements.form = null;
            self.elements.mainSelectElement = null;
            self.elements.customIdentifierSelectElement = null;
            self.elements.newGroupIdentifier = null; //hidden input for transport
            self.elements.matchingHeadline = null;
            self.elements.matchingOptionalHeadline = null;
            self.elements.matchingCustomHeadline = null;
            self.elements.matchingInput = null;
            self.elements.matchingOptionalInput = null;
            self.elements.matchingCustomInput = null;
            self.elements.categoryInfo = null;
            self.elements.customIdentifierWrapper = null;

            self.variationValues = {};

            for (i in self.options.i18n) {
                if (self.options.i18n.hasOwnProperty(i)) {
                    if (typeof self.i18n[i] !== 'undefined') {
                        self.i18n[i] = $("<div/>").html(self.options.i18n[i]).html();
                    }
                }
            }

            for (i in self.options.elements) {
                if (self.options.elements.hasOwnProperty(i)) {
                    if (typeof self.elements[i] !== 'undefined') {
                        if (typeof self.options.elements[i] === 'string') {
                            self.elements[i] = self.element.find(self.options.elements[i]);
                        } else {
                            self.elements[i] = self.options.elements[i];
                        }
                    }
                }
            }

            if (self.elements.form === null) {
                self.elements.form = self.element.find('form').andSelf().filter('form');
            }

            self.initSubmitFormInSingleParameter();

            // Breaking the reference between the two variables. Setting groupedShopVariations to shopVariations by value.
            self.options.groupedShopVariations = JSON.parse(JSON.stringify(self.options.shopVariations));
            self._flat_attributes(self.options.shopVariations);
            self.html.valuesBackup = self.elements.matchingInput.html();
            self.html.valuesOptionalBackup = self.elements.matchingOptionalInput.html();
            self.html.valuesCustomBackup = self.elements.matchingCustomInput.html();
            self.isPrepareForm = self.elements.form.attr('action').split('view=')[1] === 'apply';

            // Get tbody parent of category select and append on it additional select field for choosing the marketplace
            // variation pattern. This appending is done in buildCategorySelectors method. This should only be done on
            // prepare form.
            self.elements.mainTable = self.elements.mainSelectElement.closest('tbody #variationMatcher');

            self._initMainSelectElement();
            if (self.elements.customIdentifierSelectElement) {
                self._initCustomIdentifierSelectElement();
            }

            $('body')
                .on('click', 'button.ml-save-matching', function() {
                    self._saveMatching(this.value);
                })
                .on('click', 'button.ml-delete-row', function() {
                    self._deleteRow(this);
                })
                .on('click', 'button.ml-delete-matching', function() {
                    var button = this;
                    var d = self.i18n.resetInfo;
                    $('<div class="ml-modal dialog2" title="' + self.i18n.note + '"></div>').html(d).jDialog({
                        width: (d.length > 1000) ? '700px' : '500px',
                        buttons: {
                            Cancel: {
                                'text': self.i18n.buttonCancel,
                                click: function() {
                                    $(this).dialog('close');
                                }
                            },
                            Ok: {
                                'text': self.i18n.buttonOk,
                                click: function() {
                                    var selectId = button.value,
                                        savePrepare = selectId.substr(selectId.lastIndexOf('_') + 1, selectId.length);
                                    $('select#' + selectId).val('');
                                    self._saveMatching(savePrepare);
                                    $(this).dialog('close');
                                }
                            }
                        }
                    });
                })
                .on('click', 'button.ml-collapse', function() {
                    var matchedTable = $('div#match_' + this.value);
                    if (matchedTable.css('display') == 'none') {
                        $('span.ml-collapse[name="' + this.value + '_collapse_span_name"]').css('background-position', '0 -23px');
                        matchedTable.show();
                    } else {
                        $('span.ml-collapse[name="' + this.value + '_collapse_span_name"]').css('background-position', '0 0px');
                        matchedTable.hide();
                    }
                });
        },

        _initMainSelectElement: function() {
            var self = this;

            self.elements.mainSelectElement.change(function(event, initial) {
                self.elements.matchingHeadline.css('display', 'none');
                self.elements.matchingOptionalHeadline.css('display', 'none');
                self.elements.matchingCustomHeadline.css('display', 'none');
                self.elements.matchingInput.html(self.html.valuesBackup).css('display', 'none');
                self.elements.matchingOptionalInput.html(self.html.valuesOptionalBackup).css('display', 'none');
                self.elements.matchingCustomInput.html(self.html.valuesOptionalBackup).css('display', 'none');
                self.elements.categoryInfo.css('display', 'none');

                var val = $(this).val();
                self.elements.mainSelectElement.closest('.magnamain').find('.jsNoticeBox').remove();

                if (val != null && val !== '' && val != 'null') {
                    self.elements.mainSelectElement.closest('.magnamain').find('.successBox').remove();
                    self.elements.matchingHeadline.css('display', 'table-row-group');
                    self.elements.matchingOptionalHeadline.css('display', 'table-row-group');
                    self.elements.matchingCustomHeadline.css('display', 'table-row-group');
                    self.elements.matchingInput.css('display', 'table-row-group');
                    self.elements.matchingOptionalInput.css('display', 'table-row-group');
                    self.elements.matchingCustomInput.css('display', 'table-row-group');
                    self.elements.categoryInfo.css('display', 'table-row-group');

                    if (self.elements.customIdentifierSelectElement) {
                        self._loadCustomIdentifiers(val, initial);
                    } else {
                        self._loadMPVariation(val, '', initial);
                    }
                } else if (self.elements.customIdentifierSelectElement) {
                    self._loadCustomIdentifiers(val, initial);
                }
                var variationPatternElement = self.elements.mainTable.find('#variation-pattern');

                if (variationPatternElement.length > 0) {
                    variationPatternElement.remove();
                }
            }).trigger('change', [true]);
        },

        _loadCustomIdentifiers: function(mainSelectVal, initial) {
            var self = this,
                emptyCustomIdentifierOption = '<option value="">' + self.i18n.pleaseSelect + '</option>';

            if (mainSelectVal == null || mainSelectVal == '' || mainSelectVal == 'null') {
                self.elements.customIdentifierSelectElement.html(emptyCustomIdentifierOption).trigger('change', [initial]);
                return;
            }

            self._load({
                'Action': 'LoadCustomIdentifiers',
                'SelectValue': mainSelectVal,
                'type': 'LoadCustomIdentifiers',
                'apply': 'Apply'
            }, function(data) {
                var key,
                    customIdentifiersHtml = [],
                    previousValue = self.elements.customIdentifierSelectElement.val(),
                    selected;

                for (key in data) {
                    if (data.hasOwnProperty(key)) {
                        selected = (previousValue == key) ? 'selected="selected"' : '';
                        customIdentifiersHtml.push('<option value="'+ key +'" '+ selected +'>' + data[key] + '</option>');
                    }
                }

                if (customIdentifiersHtml.length) {
                    self.elements.customIdentifierSelectElement.html(customIdentifiersHtml.join(''));
                } else {
                    self.elements.customIdentifierSelectElement.html(emptyCustomIdentifierOption);
                }

                self.elements.customIdentifierSelectElement.trigger('change', [initial]);
            });
        },

        _initCustomIdentifierSelectElement: function() {
            var self = this;

            self.elements.customIdentifierSelectElement.change(function(event, initial) {
                self.elements.matchingHeadline.css('display', 'none');
                self.elements.matchingOptionalHeadline.css('display', 'none');
                self.elements.matchingInput.html(self.html.valuesBackup).css('display', 'none');
                self.elements.matchingOptionalInput.html(self.html.valuesOptionalBackup).css('display', 'none');
                self.elements.categoryInfo.css('display', 'none');

                var val = $(this).val();
                self.elements.mainSelectElement.closest('.magnamain').find('.jsNoticeBox').remove();

                if (val != null && val != 'null') {
                    self.elements.mainSelectElement.closest('.magnamain').find('.successBox').remove();
                    self._loadMPVariation(self.elements.mainSelectElement.val(), val, initial);
                    self.elements.matchingHeadline.css('display', 'table-row-group');
                    self.elements.matchingOptionalHeadline.css('display', 'table-row-group');
                    self.elements.matchingInput.css('display', 'table-row-group');
                    self.elements.matchingOptionalInput.css('display', 'table-row-group');
                    self.elements.categoryInfo.css('display', 'table-row-group');
                }

                if (val === '') {
                    if (self.elements.customIdentifierWrapper) {
                        self.elements.customIdentifierWrapper.css('display', 'none');
                    }
                } else if (self.elements.customIdentifierWrapper) {
                    self.elements.customIdentifierWrapper.css('display', 'table-row');
                }
            });
        },

        _render: function(template, data) {
            var out = '',
                current;
            for (var i in data) {
                if (data.hasOwnProperty(i)) {
                    current = template.replace(new RegExp('\{%count%\}', 'g'), i);
                    for (var ii in data[i]) {
                        if (data[i].hasOwnProperty(ii)) {
                            if (
                                typeof data[i][ii] !== 'undefined'
                                && typeof data[i][ii] !== 'object'
                            ) {
                                current = current.replace(new RegExp('\{' + ii + '\}', 'g'), data[i][ii]);
                            }
                        }
                    }
                    out += current;
                }
            }
            return out;
        },

        _getShopVariationsDropDownElement: function(dropDownProperty) {
            var self = this,
                counter = 1,
                numberOfOptGroups = 3,
                // Breaking the reference between the two variables. Setting groupedShopVariations to shopVariations by value.
                shopVariations = JSON.parse(JSON.stringify(self.options.groupedShopVariations));

            if (self.html[dropDownProperty] === '') {
                self.html[dropDownProperty] = '<select class="shopAttrSelector">' +
                    self._render('<option {Disabled} data-custom="{Custom}" value="{Code}">{Name}</option>',
                        {0: {Code: 'null', Name: self.i18n.pleaseSelect, Disabled: '', Custom: ''}});

                if (dropDownProperty === 'shopVariationsCustomDropDown') {
                    for (var property in  shopVariations) {
                        if (shopVariations.hasOwnProperty(property)) {
                            if (counter++ <= numberOfOptGroups) {
                                var options = self._render('<option {Disabled} data-custom="{Custom}" data-type="{Type}" value="{Code}">{Name}</option>',
                                    shopVariations[property]);

                                self.html[dropDownProperty] += '<optgroup label="' + property + '">' + options + '</optgroup>';
                                continue;
                            }
                            self.html[dropDownProperty] += self._render('<option {Disabled} data-custom="{Custom}" value="{Code}">{Name}</option>',
                                shopVariations[property]);
                        }
                    }
                }

                self.html[dropDownProperty] += '</select>';
            }

            return $(self.html[dropDownProperty]);
        },

        _load: function(data, success) {
            var self = this,
            // some systems (Magento) have additional params that must be appended to each request
            // if so, object window.ml_config.postParams should have them all
                additionalParams = window.ml_config ? window.ml_config.postParams : null;

            if (additionalParams) {
                for (var key in additionalParams) {
                    if (additionalParams.hasOwnProperty(key) && !data.hasOwnProperty(key)) {
                        data[key] = additionalParams[key];
                    }
                }
            }

            $.blockUI(blockUILoading);
            $.ajax({
                type: 'POST',
                url: self.elements.form.attr('action') + self.options.urlPostfix,
                dataType: 'json',
                data: data,
                success: function() {
                    $.unblockUI();
                    success.apply(this, arguments);
                },
                error: function() {
                    window.alert(self.options.i18n.ajaxError);
                    $.unblockUI();
                    self._resetMPVariation();
                }
            });
        },

        _resetMPVariation: function() {
            this.variationValues = {};
        },

        _renderOptions: function(options, selectedValue, firstOption, addSeparator) {
            var self = this,
                data = firstOption ? [firstOption] : [],
                i, key, selected;
            if (addSeparator) {
                data.push({
                    key: 'separator_line',
                    value: '------------------------------------------------------------------',
                    disabled: 'disabled',
                    selected: ''
                });
            }

            for (i in options) {
                if (options.hasOwnProperty(i)) {
                    key = options[i].key ? options[i].key : i;
                    var selected = "";
                    if($.isArray(selectedValue)){//multiselect
                        selected = $.inArray(key, selectedValue) >=0 ? 'selected' : '';
                    } else {
                        selected = key === selectedValue ? 'selected' : '';
                    }

                    data.push({
                        key: key,
                        value: options[i].value ? options[i].value : options[i],
                        selected: selected,
                        disabled: ''
                    });
                }
            }

            return self._render('<option {selected} {disabled} value="{key}">{value}</option>', data);
        },

        _buildSelectMatching: function(elem, selector, matchDiv, attributeListDiv) {
            var self = this,
                deleteButton = $('#' + self.generateAttributeCodeId(selector.AttributeCode) + '_deleteMatching'),
                addAfterWarning = false,
                spanWarning = $('span#' + self.generateAttributeCodeId(selector.AttributeCode) + '_warningMatching');

            if (typeof spanWarning.html() !== 'undefined') {
                addAfterWarning = true;
            }

            if (elem.val() === 'freetext') {
                var freetext = '';
                if (elem.val() === selector.CurrentValues.Code) {
                    freetext = selector.CurrentValues.Values;
                    attributeListDiv.attr('style', 'background-color: #e9e9e9');

                    if (!deleteButton.length) {
                        if (addAfterWarning) {
                            spanWarning.before(
                                '<span id="' + self.generateAttributeCodeId(selector.AttributeCode) + '_deleteMatching">' +
                                '<button type="button" class="ml-button mlbtn-action ml-delete-matching" value="' + elem.attr('id') + '">-</button>' +
                                '<span>' + self.i18n.alreadyMatched + '</span>' +
                                '</span>'
                            );
                        } else {
                            $('div#extraFieldsInfo_' + self.generateAttributeCodeId(selector.AttributeCode)).append(
                                '<span id="' + self.generateAttributeCodeId(selector.AttributeCode) + '_deleteMatching">' +
                                '<button type="button" class="ml-button mlbtn-action ml-delete-matching" value="' + elem.attr('id') + '">-</button>' +
                                '<span>' + self.i18n.alreadyMatched + '</span>' +
                                '</span>'
                            );
                        }
                    }
                } else {
                    $('div#extraFieldsInfo_' + self.generateAttributeCodeId(selector.AttributeCode)).children('*:not(.add-matching)').hide();
                }

                matchDiv.css('display', 'inline-block').css('width', '40%');
                return matchDiv.append('<input type="text" style="width:100%" name="ml[match]' + self.attributesNamePrefix + '[' + selector.AttributeCode + '][Values]" value="' + freetext + '">');
            }

            if (elem.val() === 'attribute_value') {
                var attr_value = selector.CurrentValues.Values;
                attributeListDiv.attr('style', 'background-color: #e9e9e9');
                if (!deleteButton.length) {
                    if (addAfterWarning) {
                        spanWarning.before(
                            '<span id="' + self.generateAttributeCodeId(selector.AttributeCode) + '_deleteMatching">' +
                            '<button type="button" class="ml-button mlbtn-action ml-delete-matching" value="' + elem.attr('id') + '">-</button>' +
                            '<span>' + self.i18n.alreadyMatched + '</span>' +
                            '</span>'
                        );
                    } else {
                        $('div#extraFieldsInfo_' + self.generateAttributeCodeId(selector.AttributeCode)).append(
                            '<span id="' + self.generateAttributeCodeId(selector.AttributeCode) + '_deleteMatching">' +
                            '<button type="button" id="selector.CurrentValues.Code" class="ml-button mlbtn-action ml-delete-matching" value="' + elem.attr('id') + '">-</button>' +
                            '<span>' + self.i18n.alreadyMatched + '</span>' +
                            '</span>'
                        );
                    }
                }

                matchDiv.css('display', 'inline-block').css('width', '40%');
                var style = selector.CurrentValues.Error ? ' style="border-color:red;"' : '';

                if ((typeof selector.CurrentValues.Code !== 'undefined') && (elem.val() !== selector.CurrentValues.Code)) {
                    $('div#extraFieldsInfo_' + self.generateAttributeCodeId(selector.AttributeCode)).children('*:not(.add-matching)').hide();
                }

                var multiple = '';
                var name = 'ml[match]' + self.attributesNamePrefix + '[' + selector.AttributeCode + '][Values]';
                var firstOption = { key: '', value: self.i18n.pleaseSelect, selected: '', disabled: ''};
                if (self.isMultiSelectType(selector.DataType)) {
                    multiple = 'multiple';
                    name += '[]';
                    firstOption = false;
                }

                var out = '<select' + style + ' name="'+name+'" '+ multiple +'>'
                + self._renderOptions(selector.AllowedValues, attr_value, firstOption, false)
                + '</select>';

                out += '<script type="text/javascript">'
                    + '     $(document).ready(function() {'
                    + '         $(\'select[name="'+ name +'"]\').select2({});'
                    + '     });'
                    + '</script>';

                if (typeof selector.Limit !== 'undefined' && multiple === 'multiple') {
                    out += '<script type="text/javascript">'
                        + '     $(document).ready(function() {'
                        + '         var last_valid_selection = null;'
                        + '         $(\'select[name="'+ name +'"]\').change(function(event) {'
                        + '             var selectValue = $(this).val();'
                        + '             if (typeof(selectValue) != "undefined" && selectValue !== null && selectValue.length > ' + selector.Limit + ') {'
                        + '                 $(this).val(last_valid_selection);'
                        + '             } else {'
                        + '                 last_valid_selection = $(this).val();'
                        + '             }'
                        + '         });'
                        + '     });'
                        + '</script>';
                }

                return matchDiv.append(out);
            }

            if (elem.val() === 'database_value') {
                var values = self.options.shopVariations[elem.val()],
                    allMatched = true,
                    selectedAlias = '',
                    selectedTable = false;

                if (typeof selector.CurrentValues.Values !== 'undefined') {
                    if (typeof selector.CurrentValues.Values.Alias !== 'undefined') {
                        selectedAlias = selector.CurrentValues.Values.Alias;
                    } else {
                        allMatched = false;
                    }

                    if (typeof selector.CurrentValues.Values.Table !== 'undefined') {
                        selectedTable = selector.CurrentValues.Values.Table;
                    } else {
                        allMatched = false;
                    }
                }

                matchDiv.css('position', 'relative').css('box-sizing', 'border-box');

                var html = matchDiv.append(
                    '<div style="display:inline-block;margin-top:10px">'
                    + self.i18n.dbtable
                    + '<select style="width:180px;" name="ml[match]' + self.attributesNamePrefix + '[' + selector.AttributeCode + '][Values][Table]">'
                    + self._renderOptions(values.Values, selectedTable, {
                        key: '',
                        value: self.i18n.pleaseSelect,
                        selected: '',
                        disabled: ''
                    }, false)
                    + '</select>'
                    + '</div>'
                    + '<div style="display:inline-block;margin-left:10px">'
                    + self.i18n.dbcolumn
                    + '<select style="width:100px;" name="ml[match]' + self.attributesNamePrefix + '[' + selector.AttributeCode + '][Values][Column]">'
                    + '<option value="">Please choose</option>'
                    + '</select>'
                    + '</div>'
                    + '<div style="display:inline-block;margin-left:10px">'
                    + self.i18n.dbalias
                    +   '<input type="text" name="ml[match]' + self.attributesNamePrefix + '[' + selector.AttributeCode + '][Values][Alias]" value="' + selectedAlias + '">'
                    + '</div>'
                );

                $('select[name="ml[match]' + self.attributesNamePrefix + '[' + selector.AttributeCode + '][Values][Table]"]').change(function() {
                    var selectedColumn = '';
                    if (typeof selector.CurrentValues.Values !== 'undefined' && typeof selector.CurrentValues.Values.Column !== 'undefined') {
                        selectedColumn = selector.CurrentValues.Values.Column;
                    } else {
                        allMatched = false;
                    }

                    if (allMatched) {
                        attributeListDiv.attr('style', 'background-color: #e9e9e9');
                        if (!deleteButton.length) {
                            if (addAfterWarning) {
                                spanWarning.before(
                                    '<span id="' + self.generateAttributeCodeId(selector.AttributeCode) + '_deleteMatching">' +
                                    '<button type="button" class="ml-button mlbtn-action ml-delete-matching" value="' + elem.attr('id') + '">-</button>' +
                                    '<span>' + self.i18n.alreadyMatched + '</span>' +
                                    '</span>'
                                );
                            } else {
                                $('div#extraFieldsInfo_' + self.generateAttributeCodeId(selector.AttributeCode)).append(
                                    '<span id="' + self.generateAttributeCodeId(selector.AttributeCode) + '_deleteMatching">' +
                                    '<button type="button" id="selector.CurrentValues.Code" class="ml-button mlbtn-action ml-delete-matching" value="' + elem.attr('id') + '">-</button>' +
                                    '<span>' + self.i18n.alreadyMatched + '</span>' +
                                    '</span>'
                                );
                            }
                        }

                        if ((typeof selector.CurrentValues.Code !== 'undefined') && (elem.val() !== selector.CurrentValues.Code)) {
                            $('div#extraFieldsInfo_' + self.generateAttributeCodeId(selector.AttributeCode)).children('*:not(.add-matching)').hide();
                        }
                    }

                    self._getTableColumns(selector.AttributeCode, selectedColumn, $(this).find(':selected').text());
                }).trigger('change');

                return html;
            }

            return '';
        },

        _getTableColumns: function(code, selectedColumn, table) {
            var self = this;

            self._load({
                'Action': 'DBMatchingColumns',
                'Table': table
            }, function(data) {
                self._addOptionsToSelect(code, selectedColumn, data);
            });
        },

        _addOptionsToSelect: function(code, selectedColumn, data) {
            var self = this;

            $('option', 'select[name="ml[match]' + self.attributesNamePrefix + '[' + code + '][Values][Column]"]').not(':eq(0)').remove();

            $.each(data, function(key, value) {
                var selected = '';
                if (selectedColumn === value) {
                    selected = 'selected="selected"';
                }

                $('select[name="ml[match]' + self.attributesNamePrefix + '[' + code + '][Values][Column]"]')
                    .append($('<option ' + selected + '></option>')
                        .attr("value", key)
                        .text(value));
            })
        },

        _getMatchingTableTemplate: function(attributeCode, valueShopKey, valueMarketplaceKey, isShopMultiSelect,
                                                    isMPMultiSelect) {
            var self = this;

            // Shop and marketplace matching template strings, when there is no multi-matching, extracted into variables.
            var shopMatchingTemplate =
                '<input type="hidden" name="ml[match]' + self.attributesNamePrefix + '[' + attributeCode + '][Values][{key}][Shop][Key]" value="{valueShopKey}">' +
                '<input type="hidden" name="ml[match]' + self.attributesNamePrefix + '[' + attributeCode + '][Values][{key}][Shop][Value]" value="{valueShopValue}">' +
                '{valueShopValue}',

                mpMatchingTemplate =
                    '<select id="ml_matched_value_' + self.generateAttributeCodeId(attributeCode) + '_{valueShopKey}" style="width: 100%">' +
                        '<option {disabled} value="freetext">' + this.i18n.manualMatching + '</option>' +
                        '<option selected="selected" value="{valueMarketplaceKey}">{valueMarketplaceInfo}</option>' +
                    '</select>' +
                    '<input type="hidden" name="ml[match]' + self.attributesNamePrefix + '[' + attributeCode + '][Values][{key}][Marketplace][Key]" value="{valueMarketplaceKey}">' +
                    '<input type="hidden" name="ml[match]' + self.attributesNamePrefix + '[' + attributeCode + '][Values][{key}][Marketplace][Value]" value="{valueMarketplaceValue}">' +
                    '<input type="hidden" name="ml[match]' + self.attributesNamePrefix + '[' + attributeCode + '][Values][{key}][Marketplace][Info]" value="{valueMarketplaceInfo}">';

            // If shop value is multiValue (array of keys and values) shop matching template should be changed. It is
            // changed in a way that it submits values in the same way as for initial saving.
            if (isShopMultiSelect) {

                // Forming shop multi select options which should be submitted.
                var shopMultiOptions = formMultiSelectOptions(valueShopKey);

                // Shop matching template will have hidden select multiple which will carry data about shop keys codes.
                // Shop values will be serialized as array into hidden input.
                shopMatchingTemplate =
                    '<select multiple="multiple" class="ml-hidden" name="ml[match]' + self.attributesNamePrefix + '[' + attributeCode + '][Values][{key}][Shop][Key][]">' +
                        shopMultiOptions +
                    '</select>' +
                    '<input type="hidden" name="ml[match]' + self.attributesNamePrefix + '[' + attributeCode + '][Values][{key}][Shop][Value]" value={valueShopValueSerialized}>' +
                    '{valueShopValue}';
            }

            // If marketplace value is multiValue (array of keys and values) marketplace matching template should be changed.
            // It is changed in a way that it submits values in the same way as for initial saving.
            if (isMPMultiSelect) {
                // Forming MP multi select options which should be submitted.
                var mpMultiOptions = formMultiSelectOptions(valueMarketplaceKey);

                mpMatchingTemplate =
                    '<select id="ml_matched_value_' + self.generateAttributeCodeId(attributeCode) + '_{valueShopKey}" style="width: 100%">' +
                    '   <option {disabled} value="freetext">' + this.i18n.manualMatching + '</option>' +
                    '   <option selected="selected" value="{valueMarketplaceKey}">{valueMarketplaceInfo}</option>' +
                    '</select>' +
                    '<select multiple="multiple" class="ml-hidden" name="ml[match]' + self.attributesNamePrefix + '[' + attributeCode + '][Values][{key}][Marketplace][Key][]">' +
                        mpMultiOptions +
                    '</select>' +
                    '<input type="hidden" name="ml[match]' + self.attributesNamePrefix + '[' + attributeCode + '][Values][{key}][Marketplace][Value]" value={valueMarketplaceValueSerialized}>' +
                    '<input type="hidden" name="ml[match]' + self.attributesNamePrefix + '[' + attributeCode + '][Values][{key}][Marketplace][Info]" value="{valueMarketplaceInfo}">';
            }

            // Helper function for making options in multi select.
            function formMultiSelectOptions(sourceValues) {
                var destination = '';
                $.each(sourceValues, function (index, sourceValue) {
                    destination += '<option value="' + sourceValue + '" selected></option>';
                });

                return destination;
            }

            // Final value of template string for rendering returned.
            return '<tr>' +
                '   <td style="width: 35%">' +
                        shopMatchingTemplate +
                '   </td>' +
                '   <td style="width: 35%">' +
                        mpMatchingTemplate +
                '   </td>' +
                '   <td id="ml_freetext_value_' + self.generateAttributeCodeId(attributeCode) + '_{valueShopKey}" style="border: none; display: none;">' +
                '       <input type="hidden" disabled="disabled" id="ml_key_' + self.generateAttributeCodeId(attributeCode) + '_{valueShopKey}" name="ml[match]' + self.attributesNamePrefix + '[' + attributeCode + '][Values][{key}][Marketplace][Key]" value="manual">' +
                '       <input type="text" id="ml_value_' + self.generateAttributeCodeId(attributeCode) + '_{valueShopKey}" style="width:100%;">' +
                '   </td>' +
                '   <td style="border: none">' +
                '       <button type="button" class="ml-button mlbtn-action ml-save-matching" value="' + attributeCode + '" style="display: none;">+</button>' +
                '       <button type="button" class="ml-button mlbtn-action ml-delete-row" value="' + attributeCode + '">-</button>' +
                '   </td>' +
                '</tr>' +
                '<script>' +
                '   $("#matched_value_' + self.generateAttributeCodeId(attributeCode) + '_{valueShopKey}").change();' +
                '   $("#value_' + self.generateAttributeCodeId(attributeCode) + '_{valueShopKey}").change();' +
                '</script>';
        },

        _getMatchingAttributeColumnTemplate: function() {
            return '	<tr id="selRow_{id}">'
                + '         <th {style}>{AttributeName} {redDot}</th>'
                + '         <td id="selCell_{id}">'
                + '             <div id="attributeList_{id}">'
                + '                 <label>' + this.i18n.webShopAttribute + ':</label>'
                + '                 {shopVariationsDropDown}'
                + '             </div>'
                + '             <div id="match_{id}"></div>'
                + '         </td>'
                + '         <td class="info">{AttributeDescription}</td>'
                + '	</tr>';
        },

        _getMatchingCustomAttributeColumnTemplate: function() {
            return '	<tr id="selRow_{id}">'
                + '         <th {style}>{shopVariationsCustomDropDown}</th>'
                + '         <td id="selCell_{id}">'
                + '             <div id="attributeList_{id}">'
                + '                 <label>' + this.i18n.webShopAttribute + ':</label>'
                + '                 {shopVariationsDropDown}'
                + '             </div>'
                + '             <div id="match_{id}"></div>'
                + '         </td>'
                + '         <td class="info">{AttributeDescription}</td>'
                + '	</tr>';
        },

        _getDeletedAttributeColumnTemplate: function() {
            return '<tr style="color:red;">'
                + '     <th>{AttributeName}</th>'
                + '     <td>'
                + '         <label>' + this.i18n.attributeDeletedOnMp + '</label>'
                + '     </td>'
                + '     <td></td>'
                + '	</tr>';
        },

        _getDeletedAttributeValueColumnTemplate: function() {
            return '<tr style="color:red;">'
                + '     <td style="width: 35%">{AttributeName}</td>'
                + '     <td style="width: 35%">' + this.i18n.attributeValueDeletedOnMp + '</td>'
                + '     <td colspan="2" style="border: none">'
                + '         <button type="button" class="ml-button mlbtn-action ml-delete-row" value="{Code}">-</button>'
                + '     </td>'
                + '	</tr>';
        },

        _buildMPShopMatching: function(elem, selector, savePrepare) {
            var self = this,
                values = self.options.shopVariations[elem.val()],
                matchDiv = $('div#match_' + selector.id),
                attributeListDiv = $('div#attributeList_' + selector.id),
                deleteButton = $('#' + self.generateAttributeCodeId(selector.AttributeCode) + '_deleteMatching'),
                mpValues = $.extend({}, selector.AllowedValues),
                style = '',
                removeFreeTextOption = selector.DataType.toLowerCase().indexOf('text') === -1,
                addAfterWarning = false,
                spanWarning = $('span#' + self.generateAttributeCodeId(selector.AttributeCode) + '_warningMatching'),
                attributeRow = $('#selRow_' + selector.id),
                isDataCustom = elem.find(":selected").attr('data-custom') == "true";

            if (typeof spanWarning.html() !== 'undefined') {
                addAfterWarning = true;
            }

            matchDiv.html('');
            if (typeof values === 'undefined') {
                return;
            }

            attributeListDiv.removeAttr('style');
            matchDiv.removeAttr('style');

            if (attributeRow.hasClass('optionalAttribute') || (attributeRow.hasClass('customAttribute') &&
                !selector.CurrentValues.Code) || attributeRow.hasClass('Attribute')) {

                var saveButton = $('div#extraFieldsInfo_' + self.generateAttributeCodeId(selector.AttributeCode) + ' button.ml-save-matching');
                if (!saveButton.length) {
                    $('div#extraFieldsInfo_' + self.generateAttributeCodeId(selector.AttributeCode)).prepend(
                        '<button type="button" class="ml-button mlbtn-action ml-save-matching add-matching" value="' + selector.AttributeCode + '">+</button>'
                    );
                }
            }

            if (elem.val() === selector.CurrentValues.Code) {
                attributeListDiv.attr('style', 'background-color: #e9e9e9');
                if (!deleteButton.length) {
                    if (addAfterWarning) {
                        spanWarning.before(
                            '<span id="' + self.generateAttributeCodeId(selector.AttributeCode) + '_deleteMatching">' +
                            '<button type="button" class="ml-button mlbtn-action ml-delete-matching" value="' + elem.attr('id') + '">-</button>' +
                            '<span>' + self.i18n.alreadyMatched + '</span>' +
                            '</span>'
                        );
                    } else {
                        $('div#extraFieldsInfo_' + self.generateAttributeCodeId(selector.AttributeCode)).append(
                            '<span id="' + self.generateAttributeCodeId(selector.AttributeCode) + '_deleteMatching">' +
                            '<button type="button" class="ml-button mlbtn-action ml-delete-matching" value="' + elem.attr('id') + '">-</button>' +
                            '<span>' + self.i18n.alreadyMatched + '</span>' +
                            '</span>'
                        );
                    }
                }
                if ((typeof selector.CurrentValues.Code !== 'undefined') && (elem.val() !== selector.CurrentValues.Code)) {
                    $('div#extraFieldsInfo_' + self.generateAttributeCodeId(selector.AttributeCode)).children('*:not(.add-matching)').hide();
                }
            } else {
                if (!attributeRow.hasClass('customAttribute')) {
                    $('div#extraFieldsInfo_' + self.generateAttributeCodeId(selector.AttributeCode)).children('*:not(.add-matching)').hide();
                }
            }

            if (isDataCustom) {
                return matchDiv.append('<input type="hidden" name="ml[match]' + self.attributesNamePrefix + '[' + selector.AttributeCode + '][Values]" value="true">');
            }

            if (elem.val() === 'freetext' || elem.val() === 'attribute_value' || elem.val() === 'database_value') {
                return self._buildSelectMatching(elem, selector, matchDiv, attributeListDiv);
            }

            matchDiv.css('margin-top', '10px');

            if (selector.AllowedValues.length === 0 || removeFreeTextOption === false) {
                // if AllowedValues is empty, it indicates there are no required attribute values from marketplace
                // for this, we use shop's values but do not use keys for attributes because we send to marketplace
                // keys, which in case of shop should be values because marketplace cannot recognize those keys
                // Add shop's values also if we have marketplace values, but free text is allowed as well.
                var AllowedValuesLower = Object.values(selector.AllowedValues).map(v => v.toString().toLowerCase());
                for (var k in values.Values) {
                    if (values.Values.hasOwnProperty(k)) {
                        if (AllowedValuesLower.includes(values.Values[k].toLowerCase())) {
                            continue;
                        }
                        mpValues[values.Values[k]] = values.Values[k];
                    }
                }

                removeFreeTextOption = false;
            }

            matchDiv.append(self._buildMatchingTableSelectors(selector, values.Values, mpValues, selector.CurrentValues.Error))
                .append(self._buildMatchingTableBody(selector, elem, savePrepare));

            self._changeTriggerVariationMarketplace(selector.AttributeCode);
            self._orderSelectOptions(selector, removeFreeTextOption);

            var baseName = 'ml[match]' + this.attributesNamePrefix + '[' + selector.AttributeCode + '][Values]';
            $('select[name="' + baseName + '[0][Shop][Key]"]').select2({});
            $('select[name="' + baseName + '[0][Marketplace][Key]"]').select2({});

            return matchDiv;
        },

        // This will be different for product matching js and class will be input because of the way of displaying.
        _getAppropriateAttributeMatchingTableRowClass: function() {
            return "key";
        },

        _buildMatchingTableSelectors: function(attribute, shopValues, mpValues, error) {
            var self = this,
                baseName = 'ml[match]' + self.attributesNamePrefix + '[' + attribute.AttributeCode + '][Values]',
                style = error ? 'style="border-color:red"' : '',
                out = '<table class="attrTable matchingTable">'
                + '    <tbody>'
                + '        <tr class="headline">'
                + '            <td class="key" style="width: 35%">' + self.i18n.shopValue + '</td>'
                + '            <td class="input" style="width: 35%">' + self.i18n.mpValue + '</td>'
                + '        </tr>'
                + '    </tbody>'
                + '    <tbody>'
                + '        <tr>'
                + '            <td class="' + self._getAppropriateAttributeMatchingTableRowClass() + '" style="width: 35%">'
                + '                <select ' + style + ' name="' + baseName + '[0][Shop][Key]">'
                +                      self._renderOptions(shopValues, '', null, true)
                + '                </select>'
                + '                <div id="' + baseName + '[Shop][Values][Container]" style="display: none">'
                + '                     <select class="multiSelect" multiple="multiple" ' + style + ' id="' + baseName + '[Shop][Keys]"'
                + '                        name="' + baseName + '[0][Shop][Key][]">'
                +                           self._renderOptions(shopValues, '', null, false)
                + '                     </select>'
                + '                </div>'
                + '                <input type="hidden" name="' + baseName + '[0][Shop][Value]">'
                + '            </td>'
                + '            <td class="input" style="width: 35%">'
                + '                <select ' + style + ' name="' + baseName + '[0][Marketplace][Key]">'
                +                      self._renderOptions(mpValues, '', null, true)
                + '                </select>'
                + '                <div id="' + baseName + '[Shop][Values][Container]" style="display: none">'
                + '                     <select class="multiSelect" multiple="multiple" ' + style + ' id="' + baseName + '[Marketplace][Keys]"'
                + '                        name="' + baseName + '[0][Marketplace][Key][]">'
                +                           self._renderOptions(mpValues, '', null, false)
                + '                     </select>'
                + '                </div>'
                + '                <input type="hidden" name="' + baseName + '[0][Marketplace][Value]">'
                + '            </td>'
                + '            <td id="freetext_' + self.generateAttributeCodeId(attribute.AttributeCode) + '" style="border: none; display: none;">'
                + '                <input type="text" name="' + baseName + '[FreeText]" style="width:100%;">'
                + '            </td>'
                + '            <td style="border: none">'
                + '                 <button type="button" class="ml-button mlbtn-action ml-save-matching" value="' + attribute.AttributeCode + '">+'
                + '                 </button>'
                + '            </td>'
                + '        <tr>'
                + '    </tbody>'
                + '</table>';

            if (typeof attribute.Limit !== 'undefined' && self.isMultiSelectType(attribute.DataType)) {
                out += '<script type="text/javascript">'
                    + '     $(document).ready(function() {'
                    + '         var last_valid_selection = null;'
                    + '         $(\'select[name="'+ baseName +'[0][Marketplace][Key][]"]\').change(function(event) {'
                    + '             var selectValue = $(this).val();'
                    + '             if (typeof(selectValue) != "undefined" && selectValue !== null && selectValue.length > ' + attribute.Limit + ') {'
                    + '                 $(this).val(last_valid_selection);'
                    + '             } else {'
                    + '                 last_valid_selection = $(this).val();'
                    + '             }'
                    + '         });'
                    + '     });'
                    + '</script>';
            }

            return $(out);
        },

        _buildMatchingTableBody: function(selector, elem, savePrepare) {
            var self = this,
                deleteButton = $('#' + self.generateAttributeCodeId(selector.AttributeCode) + '_deleteMatching');

            if (typeof selector.CurrentValues.Values !== 'undefined'
                && (selector.CurrentValues.Values.length > 0 || Object.keys(selector.CurrentValues.Values).length > 0)
                && elem.val() === selector.CurrentValues.Code) {
                // reload saved values
                var tableBody = '',
                    i = 1,
                    disableFreeTextOption = selector.DataType.toLowerCase().indexOf('text') === -1 ? 'disabled' : '',
                    addAfterWarning = false,
                    spanWarning = $('span#' + self.generateAttributeCodeId(selector.AttributeCode) + '_warningMatching');

                if (typeof spanWarning.html() !== 'undefined') {
                    addAfterWarning = true;
                }

                for (var code in selector.CurrentValues.Values) {
                    if (selector.CurrentValues.Values.hasOwnProperty(code)) {
                        var entry = selector.CurrentValues.Values[code],
                            isShopMultiValue = entry.Shop.Key.constructor === Array,
                            isMPMultiValue = entry.Marketplace.Key.constructor === Array,
                            notDeletedOnMarketplace = false,
                            checkDeletedOnMarketplace = selector.DataType.toLowerCase().indexOf('text') === -1,
                            matchingTemplateInformation = {
                                key: i++,
                                valueShopKey: entry.Shop.Key,
                                valueShopValue: entry.Shop.Value,
                                valueMarketplaceKey: entry.Marketplace.Key,
                                valueMarketplaceValue: entry.Marketplace.Value,
                                valueMarketplaceInfo: entry.Marketplace.Info,
                                disabled: disableFreeTextOption
                            };

                        // Different conditions for marketplace multi value and single value.
                        if (isMPMultiValue) {
                            var allowedValues = Object.keys(selector.AllowedValues);
                            // ($(entry.Marketplace.Key).not(allowedValues).get().length === 0) is simulation of array_diff in javascript.
                            notDeletedOnMarketplace = allowedValues.length === 0 || $(entry.Marketplace.Key).not(allowedValues).get().length === 0;
                        } else {
                            notDeletedOnMarketplace = selector.AllowedValues.length === 0 || selector.AllowedValues[entry.Marketplace.Key] !== undefined;
                        }

                        if (notDeletedOnMarketplace || !checkDeletedOnMarketplace) {
                            // if there are not predefined values or current value is in predefined values, render regularly
                            // otherwise, attribute value has been deleted from marketplace

                            if (isShopMultiValue) {
                                // When shop value is multi, keys and values should be transformed to string, because
                                // they will be an array. (entry.Shop.Key = ["1", "2", "3"], entry.Shop.Value = ["Red", "Black", "Green"]).
                                // Values should be serialized because they will be hiddens' input value (serialized array).
                                matchingTemplateInformation['valueShopKey'] = entry.Shop.Key.join(', ');
                                matchingTemplateInformation['valueShopValue'] = entry.Shop.Value.join(', ');
                                matchingTemplateInformation['valueShopValueSerialized'] = JSON.stringify(entry.Shop.Value);
                            }

                            if (isMPMultiValue) {
                                // When marketplace value is multi, keys and values should be transformed to string, because
                                // they will be an array. (entry.Marketplace.Key = ["Autre", "Bouche"],
                                // entry.Marketplace.Value = ["Autre", "Bouche"]). Values should be serialized because they will
                                // be hiddens' input value (serialized array).
                                matchingTemplateInformation['valueMarketplaceKey'] = entry.Marketplace.Key.join(', ');
                                matchingTemplateInformation['valueMarketplaceValue'] = entry.Marketplace.Key.join(', ');
                                matchingTemplateInformation['valueMarketplaceValueSerialized'] = JSON.stringify(entry.Marketplace.Value);
                            }

                            tableBody += self._render(self._getMatchingTableTemplate(selector.AttributeCode,
                                entry.Shop.Key, entry.Marketplace.Key, isShopMultiValue, isMPMultiValue), [matchingTemplateInformation]);

                        } else {
                            tableBody += self._render(self._getDeletedAttributeValueColumnTemplate(), [{
                                AttributeName: entry.Shop.Value
                            }]);
                        }
                    }
                }

                $('div#attributeList_' + selector.id).attr('style', 'background-color: #e9e9e9');
                if (!deleteButton.length) {
                    if (addAfterWarning) {
                        spanWarning.before(
                            '<span id="' + self.generateAttributeCodeId(selector.AttributeCode) + '_deleteMatching">' +
                            '<button type="button" class="ml-button mlbtn-action ml-delete-matching" value="' + elem.attr('id') + '">-</button>' +
                            '<span>' + self.i18n.alreadyMatched + '</span>' +
                            '</span>'
                        );
                    } else {
                        $('div#extraFieldsInfo_' + self.generateAttributeCodeId(selector.AttributeCode)).append(
                            '<span id="' + self.generateAttributeCodeId(selector.AttributeCode) + '_deleteMatching">' +
                            '<button type="button" class="ml-button mlbtn-action ml-delete-matching" value="' + elem.attr('id') + '">-</button>' +
                            '<span>' + self.i18n.alreadyMatched + '</span>' +
                            '</span>'
                        );
                    }
                }

                if (self.isPrepareForm) {
                    $('div#extraFieldsInfo_' + self.generateAttributeCodeId(selector.AttributeCode)).append(
                        '<span id="' + self.generateAttributeCodeId(selector.AttributeCode) + '_collapseMatching" style="float: right">' +
                        '<button type="button" class="ml-collapse" value="' + self.generateAttributeCodeId(selector.AttributeCode) + '" name="' + self.generateAttributeCodeId(selector.AttributeCode) + '_collapse_button_name">' +
                        '<span class="ml-collapse" name="' + self.generateAttributeCodeId(selector.AttributeCode) + '_collapse_span_name"></span>' +
                        '</button>' +
                        '</span>'
                    );
                }

                if (savePrepare === selector.AttributeCode || !self.isPrepareForm) {
                    $('span.ml-collapse[name="' + self.generateAttributeCodeId(selector.AttributeCode) + '_collapse_span_name"]').css('background-position', '0 -23px');
                    $('div#match_' + self.generateAttributeCodeId(selector.AttributeCode)).show();
                } else {
                    $('span.ml-collapse[name="' + self.generateAttributeCodeId(selector.AttributeCode) + '_collapse_span_name"]').css('background-position', '0 0px');
                    $('div#match_' + self.generateAttributeCodeId(selector.AttributeCode)).hide();
                }

                if ((typeof selector.CurrentValues.Code !== 'undefined') && (elem.val() !== selector.CurrentValues.Code)) {
                    $('div#extraFieldsInfo_' + self.generateAttributeCodeId(selector.AttributeCode)).children('*:not(.add-matching)').hide();
                }

                return $(
                    '<span id="spanMatchingTable" style="padding-right:2em;">' +
                    '   <div style="font-weight: bold; background-color: #e9e9e9">' + self.i18n.matchingTable + '</div>' +
                    '   <table id="' + self.generateAttributeCodeId(selector.AttributeCode) + '_button_matched_table" style="width:100%; background-color: #e9e9e9">' +
                    '       <tbody>' +
                                tableBody +
                    '       </tbody>' +
                    '   </table>' +
                    '</span>');
            }

            return '';
        },

        _changeTriggerVariationMarketplace: function(attributeCode) {
            var self = this,
                shopKeySelector = '[name="ml[match]' + self.attributesNamePrefix + '[' + attributeCode + '][Values][0][Shop][Key]"]',
                shopValueSelector = '[name="ml[match]' + self.attributesNamePrefix + '[' + attributeCode + '][Values][0][Shop][Value]"]',
                shopKeysSelector = '[name="ml[match]' + self.attributesNamePrefix + '[' + attributeCode + '][Values][0][Shop][Key][]"]',
                mpKeySelector = '[name="ml[match]' + self.attributesNamePrefix + '[' + attributeCode + '][Values][0][Marketplace][Key]"]',
                mpValueSelector = '[name="ml[match]' + self.attributesNamePrefix + '[' + attributeCode + '][Values][0][Marketplace][Value]"]',
                mpKeysSelector = '[name="ml[match]' + self.attributesNamePrefix + '[' + attributeCode + '][Values][0][Marketplace][Key][]"]';

            $(shopKeySelector).change(function() {
                $(shopValueSelector).val($(shopKeySelector + ' option:selected').html());
                $(shopKeysSelector).parent().hide();

                if ($(this).val() === 'multiSelect') {
                    $(shopKeysSelector).parent().show();
                }
            }).trigger('change');

            $(mpKeySelector).change(function() {
                $(mpValueSelector).val($(mpKeySelector + ' option:selected').html());

                var oldValue = $(mpKeySelector).defaultValue;
                if ($(this).val() === 'reset') {
                    var d = self.i18n.resetInfo;
                    $('<div class="ml-modal dialog2" title="' + self.i18n.note + '"></div>').html(d).jDialog({
                        width: (d.length > 1000) ? '700px' : '500px',
                        buttons: {
                            Cancel: {
                                'text': self.i18n.buttonCancel,
                                click: function() {
                                    $(mpKeySelector).val(oldValue);
                                    $(this).dialog('close');
                                }
                            },
                            Ok: {
                                'text': self.i18n.buttonOk,
                                click: function() {
                                    self._saveMatching(true);
                                    $(this).dialog('close');
                                }
                            }
                        }
                    });
                }

                if ($(this).val() === 'manual') {
                    $('td #freetext_' + self.generateAttributeCodeId(attributeCode)).show();
                } else {
                    $('td #freetext_' + self.generateAttributeCodeId(attributeCode)).hide();
                }

                $(mpKeysSelector).parent().hide();

                if ($(this).val() === 'multiSelect') {
                    $(mpKeysSelector).parent().show();
                }
            }).trigger('change');

            $(shopKeysSelector).change(function() {
                // When value is changed in shop multi select, value should be formed as array and serialized.
                // Then it should be set in hidden input for shop value as value. this = shop multiple select control
                $(shopValueSelector).val(JSON.stringify(formatMultiValues(this)));
            });

            $(mpKeysSelector).change(function() {
                // When value is changed in shop multi select, value should be formed as array and serialized.
                // Then it should be set in hidden input for shop value as value. this = mp multiple select control
                $(mpValueSelector).val(JSON.stringify(formatMultiValues(this)));
            });

            // Helper function for forming values array both for shop and marketplace attributes.
            function formatMultiValues(multipleSelect) {
                var allCheckedValues = [];

                $.each($(multipleSelect).find('option:checked'), function(index, element) {
                    allCheckedValues.push($(element).text());
                });

                return allCheckedValues;
            }
        },

        generateAttributeCodeId: function(AttributeCode) {
            var self = this;
            return ('' + self.attributesNamePrefix + AttributeCode).replace(/[^A-Za-z0-9_]/g, '_');
        },

        _buildShopVariationSelector: function(attributes, attributesName) {
            var self = this,
                data = attributes[attributesName],
                allowedValuesNotEmpty = (data.AllowedValues.length > 0 || Object.keys(data.AllowedValues).length > 0),
                kind = allowedValuesNotEmpty ? 'Matching' : 'FreeText',
                baseName = 'ml[match]' + self.attributesNamePrefix + '[' + data.AttributeCode + ']',
                mpDataType = data.DataType.toLowerCase();

            data.AttributeCode = data.AttributeCode + '';
            data.id = self.generateAttributeCodeId(data.AttributeCode); // css selector-save.
            data.AttributeName = data.AttributeName || data.AttributeCode;
            data.AttributeDescription = data.AttributeDescription || ' ';
            self.variationValues[data.AttributeCode] = data.AllowedValues;
            var variationsDropDown =
                    self._getShopVariationsDropDownElement('shopVariationsDropDown')
                        .attr('id', 'sel_' + data.id)
                        .attr('name', baseName + '[Code]'),
                variationsCustomDropDown =
                    self._getShopVariationsDropDownElement('shopVariationsCustomDropDown')
                        .attr('id', 'sel_' + data.id + '_custom_name')
                        .attr('name', baseName + '[CustomAttributeValue]');

            if (data.CurrentValues.Error == true) {
                variationsDropDown.attr('style', 'border-color:red');
                variationsCustomDropDown.attr('style', 'border-color:red');
                data.style = 'style="color:red"';
            } else {
                data.style = '';
            }

            // If attribute is already matched add options
            if (typeof data.CurrentValues.Values !== 'undefined'
                && (data.CurrentValues.Values.length > 0 || Object.keys(data.CurrentValues.Values).length > 0)) {
                self._addShopOptions(self, variationsDropDown, data, attributes, mpDataType);
            }

            if (data.AttributeCode.substring(0, 20) === 'additional_attribute') {
                for (var i in attributes) {
                    if (attributes.hasOwnProperty(i)) {
                        var customName = attributes[i].CurrentValues.CustomName,
                            nameToCheck = customName ? customName : attributes[i].AttributeName,
                            valueToCheck = $('<textarea />').html(nameToCheck).text(),
                            optionToCheck = null;

                        $.each(variationsCustomDropDown.find("option"), function(index, option) {
                            option = $(option);
                            if (option.text() === valueToCheck) {
                                optionToCheck = option;
                            }
                        });

                        if (optionToCheck && optionToCheck.text() !== data.CurrentValues.CustomName) {
                            optionToCheck.attr('disabled', 'disabled');
                        }
                    }
                }
            }

            if (data.Required == true) {
                data.redDot = '<span class="bull">&bull;</span>';
            } else {
                data.redDot = '';
            }

            data.shopVariationsDropDown = $('<div>')
                .append(variationsDropDown)
                .append('<div id="extraFieldsInfo_' +  self.generateAttributeCodeId(data.AttributeCode) + '" style="display: inline;"></div>')
                .append('<input type="hidden" name="' + baseName + '[Kind]" value="' + kind + '">')
                .append('<input type="hidden" name="' + baseName + '[Required]" value="' + (data.Required ? 1 : 0) + '">')
                .append('<input type="hidden" name="' + baseName + '[AttributeName]" value="' + data.AttributeName + '">')
                .html()
            ;

            var customAttributeName = $('<input>').attr({
                type: 'text',
                id: 'input_' + self.generateAttributeCodeId(data.AttributeCode) + '_custom_name',
                value: data.CustomName,
                name: baseName + '[CustomName]',
                style: (data.CurrentValues.Error == true) ?
                    'width:100%; padding-left: 3px; display:none; border-color:red' :
                    'width:100%; padding-left: 3px; display:none;'
            });

            data.shopVariationsCustomDropDown = $('<div>')
                .append(variationsCustomDropDown)
                .append(customAttributeName)
                .html()
            ;

            setTimeout(function() {
                var selectElement = document.getElementById('sel_' + data.id);

                if (selectElement !== null) {
                    selectElement.addEventListener('mousedown', function() {
                        if (this.options.length === 1) {
                            self._addShopOptions(self, this, data, attributes, mpDataType);
                        }
                    });
                }
            }, 0);

            return data;
        },

        _addShopOptions: function(self, select, data, attributes, mpDataType) {
            var renderOptions = '',
                counter = 1,
                numberOfOptGroups = 3,
                shopVariations = JSON.parse(JSON.stringify(self.options.groupedShopVariations));

            for (var property in  shopVariations) {
                if (shopVariations.hasOwnProperty(property)) {
                    if (counter++ <= numberOfOptGroups) {
                        var options = self._render('<option {Disabled} data-custom="{Custom}" data-type="{Type}" value="{Code}">{Name}</option>',
                            shopVariations[property]);

                        renderOptions += '<optgroup label="' + property + '">' + options + '</optgroup>';
                        continue;
                    }

                    renderOptions += self._render('<option {Disabled} data-custom="{Custom}" value="{Code}">{Name}</option>',
                        shopVariations[property]);
                }
            }

            $(select).append(renderOptions);

            if (['select', 'multiSelect'.toLowerCase()].indexOf(mpDataType) != -1) {
                $(select).find("option[data-type='text']").attr('disabled', 'disabled');
                $(select).find('option[value=freetext]').attr('disabled', 'disabled');
                $(select).find("option[value='database_value']").attr('disabled', 'disabled');
            }

            if ('text' == mpDataType || 'freetext' == mpDataType) {
                $(select).find('option[value=attribute_value]').attr('disabled', 'disabled');
            }

            /*if (data.AttributeCode.substring(0, 20) === 'additional_attribute') {
                $(select).find("option[value='database_value']").attr('disabled', 'disabled');
            }*/
        },

        _buildShopVariationSelectors: function(data, resetNotice, savePrepare) {
            var self = this,
                colTemplate = self._getMatchingAttributeColumnTemplate(),
                customAttributeColTemplate = self._getMatchingCustomAttributeColumnTemplate(),
                deletedAttrTemplate = self._getDeletedAttributeColumnTemplate(),
                attributeColumnEl = null,
                attributesSelectorOptions = [{key: 'dont_use', value: self.i18n.pleaseSelect}],
                isCategoryEmpty = true,
                i, matchingInputEl,
                attributes = data.Attributes,
                variationDetails = data.variation_details ? data.variation_details : null,
                variationDetailsBlacklist = data.variation_details_blacklist ? data.variation_details_blacklist : null,
                attributesSize = 0;

            self.elements.matchingInput.html('');
            self.elements.matchingOptionalInput.html('');
            self.elements.matchingCustomInput.html('');

            for (var key in attributes) {
                if (attributes.hasOwnProperty(key) && !attributes[key].Required) {
                    attributesSize++;
                }
            }

            for (i in attributes) {
                if (attributes.hasOwnProperty(i)) {
                    var attributeName = attributes[i].AttributeName;
                    isCategoryEmpty = false;
                    if (attributes[i].Deleted) {
                        attributes[i].AttributeName = attributes[i].CustomAttributeValue ?
                            attributes[i].CustomAttributeValue : attributes[i].AttributeName;
                        self.elements.matchingInput.append($(self._render(deletedAttrTemplate, [attributes[i]])))
                    } else {
                        attributes[i] = self._buildShopVariationSelector(attributes, i);
                        matchingInputEl = self.elements.matchingInput;
                        attributes[i].AttributeName = attributes[i].CustomAttributeValue ?
                            attributes[i].CustomAttributeValue : attributes[i].AttributeName;

                        if (self.isMultiSelectType(attributes[i].DataType)) {
                            attributes[i].AttributeDescription = $.trim(attributes[i].AttributeDescription);
                            if (attributes[i].AttributeDescription) {
                                attributes[i].AttributeDescription += '<br>' + self.i18n.multiselectHint;
                            } else {
                                attributes[i].AttributeDescription += self.i18n.multiselectHint;
                            }
                        }

                        attributeColumnEl = $(self._render(colTemplate, [attributes[i]]));

                        if (!attributes[i].Required && !attributes[i].Custom) {
                            matchingInputEl = self.elements.matchingOptionalInput;

                            if (!attributes[i].CurrentValues.Code) {
                                if (attributesSize > self.optionalAttributesMaxSize) {
                                    attributeColumnEl.hide();
                                }

                                attributeColumnEl.addClass('optionalAttribute');
                                attributesSelectorOptions.push({
                                    key: attributes[i].id,
                                    value: attributes[i].AttributeName
                                });
                            }
                        } else if (attributes[i].Custom) {
                            matchingInputEl = self.elements.matchingCustomInput;
                            attributeColumnEl = $(self._render(customAttributeColTemplate, [attributes[i]]));
                            attributeColumnEl.addClass('customAttribute');
                        }
                        matchingInputEl.append(attributeColumnEl);

                        // add warning box if attribute changed on Marketplace
                        if (attributes[i].ChangeDate && data.ModificationDate
                            && new Date(data.ModificationDate) < new Date(attributes[i].ChangeDate)
                        ) {
                            $('div#extraFieldsInfo_' + attributes[i].id)
                                .append('<span id="' + attributes[i].id + '_warningMatching" class="ml-warning" title="' + self.i18n.attributeChangedOnMp + '">&nbsp;<span>');
                        }

                        // add warning box if attribute is different from one matched in Variation matching tab
                        if (attributes[i].Modified) {
                            $('div#extraFieldsInfo_' + attributes[i].id)
                                .append('<span id="' + attributes[i].id + '_warningMatching" class="ml-warning" title="' + self.i18n.attributeDifferentOnProduct + '">&nbsp;<span>');
                        }

                        // add warning box if attribute is different from one matched in Variation matching tab
                        if (attributes[i].IsDeletedOnShop) {
                            var warningSpan = (attributes[i].Modified) ? '<span class="ml-warning" title="' + attributes[i].WarningMessage + '">&nbsp;<span>' :
                                '<span id="' + attributes[i].id + '_warningMatching" class="ml-warning" title="' + attributes[i].WarningMessage + '">&nbsp;<span>';
                            $('div#extraFieldsInfo_' + attributes[i].id).append(warningSpan);
                        }
                    }
                    attributes[i].AttributeName = attributeName;
                }
            }

            self._setVariationThemeField(variationDetails, self, data);

            var variationThemeBlacklistEl = self.elements.form.find('#VariationThemeBlacklist');
            if (!variationThemeBlacklistEl.length) {
                self.elements.form.append('<input type="hidden" name="VariationThemeBlacklist" id="VariationThemeBlacklist">');
            }

            self.elements.form.find('#VariationThemeBlacklist').val(JSON.stringify(variationDetailsBlacklist));

            self.elements.mainSelectElement.closest('.magnamain').find('.jsNoticeBox').remove();
            if (data.DifferentProducts) {
                var categoryName = self.elements.mainSelectElement.find('option:selected').html();
                self.elements.mainSelectElement.closest('.magnamain')
                    .prepend('<p class="noticeBox jsNoticeBox">'
                        + self.i18n.differentAttributesOnProducts.replace('%category_name%', categoryName)
                        + '</p>');
            }

            if (resetNotice) {
                self.elements.mainSelectElement.closest('.magnamain').find('.notAllAttributeValuesMatched').remove();
            }

            if (data.notice && data.notice.length) {
                for (i = 0; i < data.notice.length; i++) {
                    if (data.notice.hasOwnProperty(i)) {
                        self.elements.mainSelectElement.closest('.magnamain')
                            .prepend('<p class="noticeBox notAllAttributeValuesMatched">'
                                + data.notice[i]
                                + '</p>');
                    }
                }
                // scroll to top (modified, etc..) not working in gambio because of iframe
                window.scrollTo(0, 0);
            }

            data.Attributes = attributes;

            if (isCategoryEmpty) {
                self.elements.matchingInput.append('<tr><th></th><td class="input">'
                    + self.i18n.categoryWithoutAttributesInfo
                    + '</td><td class="info"></td></tr>');
                self.elements.matchingOptionalHeadline.css('display', 'none');
                self.elements.matchingOptionalInput.css('display', 'none');
                self.elements.matchingCustomHeadline.css('display', 'none');
                self.elements.matchingCustomInput.css('display', 'none');
            }

            if (!$.trim(self.elements.matchingInput.html())) {
                self.elements.matchingHeadline.css('display', 'none');
                self.elements.matchingInput.css('display', 'none');
            }

            if (!$.trim(self.elements.matchingOptionalInput.html())) {
                self.elements.matchingOptionalHeadline.css('display', 'none');
                self.elements.matchingOptionalInput.css('display', 'none');
            } else if (attributesSize > self.optionalAttributesMaxSize && attributesSelectorOptions.length > 1) {
                self.elements.matchingOptionalInput.append($([
                    '<tr id="selRow_dont_use">',
                        '<th></th>',
                        '<td id="selCell_dont_use">',
                            '<div id="attributeList_dont_use"></div>',
                            '<div id="match_dont_use"></div>',
                        '</td>',
                        '<td class="info"></td>',
                    '</tr>'
                ].join('')));
            }

            if (!$.trim(self.elements.matchingCustomInput.html())) {
                self.elements.matchingCustomHeadline.css('display', 'none');
                self.elements.matchingCustomInput.css('display', 'none');
            }
            self.elements.matchingInput.append('<tr class="spacer"><td colspan="3">&nbsp;</td></tr>');
            self.elements.matchingOptionalInput.append('<tr class="spacer"><td colspan="3">&nbsp;</td></tr>');
            self.elements.matchingCustomInput.append('<tr class="spacer"><td colspan="3">&nbsp;</td></tr>');

            function addShopVariationSelectorChangeListener() {
                var previous;
                $(this).on('focus', function() {
                    previous = $(this).val();
                }).change(function() {
                    self._handleAttributeSelectorChange(this, data, previous, savePrepare);
                });
            }

            self.elements.matchingInput.find('select[id^=sel_]').each(addShopVariationSelectorChangeListener);
            self.elements.matchingOptionalInput.find('select[id^=sel_]').each(addShopVariationSelectorChangeListener);
            self.elements.matchingCustomInput.find('select[id^=sel_]').each(addShopVariationSelectorChangeListener);
            for (i in attributes) {
                if (attributes.hasOwnProperty(i)) {
                    if (typeof attributes[i].CurrentValues.Code !== 'undefined') {
                        var customAttributeValue = (self.options.shopVariations[attributes[i].CurrentValues.CustomAttributeValue]) ?
                            attributes[i].CurrentValues.CustomAttributeValue : 'freetext';
                        self.elements.matchingInput.find('select[id=sel_' + attributes[i].id + ']').val(attributes[i].CurrentValues.Code).trigger('change');
                        self.elements.matchingOptionalInput.find('select[id=sel_' + attributes[i].id + ']').val(attributes[i].CurrentValues.Code).trigger('change');
                        self.elements.matchingCustomInput.find('select[id=sel_' + attributes[i].id + ']').val(attributes[i].CurrentValues.Code).trigger('change');
                        self.elements.matchingCustomInput.find('select[id=sel_' + attributes[i].id + '_custom_name]').val(customAttributeValue).trigger('change');
                    }
                }
            }
            self._prefix_option();
            self._attachAttributeSelector(attributesSelectorOptions, addShopVariationSelectorChangeListener);

            for (i in attributes) {
                $('[id="sel_'+ attributes[i].id +'"]').select2({});
                $('[id="sel_'+ attributes[i].id +'_custom_name').select2({});
                $('[id="sel_'+ attributes[i].id +'"]').on('select2:open', function (e) {
                    if (this.options.length === 1) {
                        var name = $(this).attr('name'),
                            mpDataType = $('input[name="' + $(this).attr('name').replace('[Code]', '[Kind]') + '"]').val(),
                            span = $(this).closest("span"),
                            select = $('select[name="' + name + '"]');

                        span.css("width", "81%");

                        self._addShopOptions(self, this, false, false, mpDataType);

                        $(this).trigger('input');

                        if (mpDataType) {
                            mpDataType = mpDataType.toLowerCase();
                            isSelectAndText = mpDataType === 'selectandtext';
                        }

                        select.find('option[value^=separator]').attr('disabled', 'disabled');

                        if (['select', 'multiselect'].indexOf(mpDataType) != -1) {
                            select.find("option[data-type='text']").attr('disabled', 'disabled');
                            select.find('option[value=freetext]').attr('disabled', 'disabled');
                        }

                        if ('text' == mpDataType || 'freetext' == mpDataType) {
                            select.find('option[value=attribute_value]').attr('disabled', 'disabled');
                        }
                    }
                });
            }
        },

        _setVariationThemeField: function(variationDetails, self, data) {
            var selectedVariationTheme = data.variation_theme_code ? data.variation_theme_code : null,
                variationPatternElement = $('#variation-pattern');

            if (self.elements.customIdentifierSelectElement) {
                variationPatternElement.remove();
                variationPatternElement = [];
            }

            if (variationDetails && self.isPrepareForm && !variationPatternElement.length) {
                var allVariationOptions = [{key : 'null', value: self.i18n.pleaseSelect}],
                    lastElementInVariationMatcher = self.elements.mainTable.children().last(),
                    oddOrEven = lastElementInVariationMatcher.prev('tr').attr('class') === 'odd' ? 'even' : 'odd';

                for (var property in variationDetails) {
                    if (variationDetails.hasOwnProperty(property)) {
                        var variationOption = {key : property, value : variationDetails[property]['name']};

                        if (selectedVariationTheme === property) {
                            variationOption.selected = 'selected';
                        }
                        allVariationOptions.push(variationOption);
                    }
                }

                $(['<tr class="' + oddOrEven + '" id="variation-pattern">',
                        '<th>',
                            self._getVariationThemeHeader(self),
                            '<span class="bull">',
                                '&bull;',
                            '</span>',
                        '</th>',
                        '<td class="input">',
                            '<table class="inner middle fullwidth categorySelect">',
                                '<tbody>',
                                    '<tr>',
                                        '<td>',
                                            '<div class="hoodCatVisual">',
                                                '<select id="variation-theme" name="variationTheme" onchange="" ' + 'style="width:100%;">',
                                                    self._render('<option {selected} value="{key}">{value}</option>', allVariationOptions),
                                                '</select>',
                                                '<input type="hidden" name="variationThemes" id="variation-themes">',
                                            '</div>',
                                        '</td>',
                                        '<td class="buttons">',
                                        '</td>',
                                    '</tr>',
                                '<tbody>',
                            '</table>',
                        '</td>',
                        '<td class="info">',
                        '</td>',
                    '</tr>'
                ].join('')).insertBefore(lastElementInVariationMatcher);
                // Last element is row which represents spacer, so before that element, variation details element should
                // be inserted.

                $('#variation-themes').val(JSON.stringify(variationDetails));
            }
        },

        _getVariationThemeHeader: function (self) {
            return self.i18n.variationTheme;
        },

        _attachAttributeSelector: function(attributesSelectorOptions, addShopVariationSelectorChangeListener) {
            var self = this,
                currentlySelectedAttribute,
                attributesSelectorEl = $([
                    '<select name="optional_selector" style="width: 100%">',
                        self._render('<option value="{key}">{value}</option>', attributesSelectorOptions),
                    '</select>'
                ].join(''));

            function showConfirmationDialog(attributeIdToShow) {
                var d = self.i18n.resetInfo;
                $('<div class="ml-modal dialog2" title="' + self.i18n.note + '"></div>').html(d).jDialog({
                    width: (d.length > 1000) ? '700px' : '500px',
                    buttons: {
                        Cancel: {
                            'text': self.i18n.buttonCancel,
                            click: function() {
                                // Reset attribute selector to previous value silently
                                attributesSelectorEl.val(currentlySelectedAttribute);
                                $(this).dialog('close');
                            }
                        },
                        Ok: {
                            'text': self.i18n.buttonOk,
                            click: function() {
                                $('#sel_' + currentlySelectedAttribute).val('');
                                self._saveMatching(true, function() {
                                    self.elements.matchingOptionalInput.find('select[name="optional_selector"]').val(attributeIdToShow).change();
                                });

                                $(this).dialog('close');
                            }
                        }
                    }
                });
            }

            function changeCurrentAttribute(attributeIdToShow) {
                if ($('#sel_' + attributeIdToShow).hasClass("select2-hidden-accessible")) {
                    $('#sel_' + attributeIdToShow).select2('destroy');
                }

                self.elements.matchingOptionalInput.find('#selRow_' + currentlySelectedAttribute).hide();
                currentlySelectedAttribute = attributeIdToShow;
                var attributeRowEl = self.elements.matchingOptionalInput.find('#selRow_' + currentlySelectedAttribute),
                    selectId = '#sel_' + currentlySelectedAttribute;

                //Minus 1 goes for "Bitte wahlen"
                if (attributesSelectorOptions.length - 1 > self.optionalAttributesMaxSize) {
                    self.elements.matchingOptionalInput.find('.optionalAttribute').hide();
                }

                attributeRowEl.children('th').html('').append(attributesSelectorEl);
                attributeRowEl.remove().show().insertBefore(self.elements.matchingOptionalInput.find('.spacer').last());
                attributeRowEl.find(selectId).each(addShopVariationSelectorChangeListener).change();
                self._prefix_option(selectId);

                attributesSelectorEl.change(attributeSelectorOnChange);

                $('select[name="optional_selector"]').each(function (index, link) {
                    $(this).select2({});
                });

                $(selectId).select2({});

                $(selectId).on('select2:open', function (e) {
                    if (this.options.length === 1) {

                        var name = $(this).attr('name'),
                            mpDataType = $('input[name="' + $(this).attr('name').replace('[Code]', '[Kind]') + '"]').val(),
                            span = $(this).closest("span"),
                            select = $('select[name="' + name + '"]');

                        span.css("width", "81%");

                        self._addShopOptions(self, this, false, false, mpDataType);
                        $(this).trigger('input');

                        if (mpDataType) {
                            mpDataType = mpDataType.toLowerCase();
                            isSelectAndText = mpDataType === 'selectandtext';
                        }

                        select.find('option[value^=separator]').attr('disabled', 'disabled');

                        if (['select', 'multiselect'].indexOf(mpDataType) != -1) {
                            select.find("option[data-type='text']").attr('disabled', 'disabled');
                            select.find('option[value=freetext]').attr('disabled', 'disabled');
                        }

                        if ('text' == mpDataType || 'freetext' == mpDataType) {
                            select.find('option[value=attribute_value]').attr('disabled', 'disabled');
                        }
                    }
                });
            }

            function attributeSelectorOnChange() {
                if (currentlySelectedAttribute) {
                    var attributeValue = $('#sel_' + currentlySelectedAttribute).val();
                    if (attributeValue != null && attributeValue !== '' &&  attributeValue != 'null') {
                        showConfirmationDialog($(this).val());
                        return;
                    }
                }

                changeCurrentAttribute($(this).val());
            }

            attributesSelectorEl.change(attributeSelectorOnChange).change();
        },

        _buildMPShopMatchingCustom: function(selectElement, attribute) {
            var self = this;

            selectElement.parent().find('#input_' + self.generateAttributeCodeId(attribute.AttributeCode) + '_custom_name').hide();
            if (selectElement.val() === 'freetext' || selectElement.val() === 'attribute_value' ||
                selectElement.val() === 'database_value') {
                selectElement.parent().find('#input_' + self.generateAttributeCodeId(attribute.AttributeCode) + '_custom_name')
                    .val(attribute.CurrentValues.CustomName ? attribute.CurrentValues.CustomName : '').show();
            } else {
                var selectedOption = selectElement.find('option:selected'),
                    attributeName = (selectedOption.index() > 0 || selectedOption.parent().is("optgroup")) ?
                    selectedOption.text() : '';
                selectElement.parent().find('#input_' + self.generateAttributeCodeId(attribute.AttributeCode) + '_custom_name').val(attributeName);
            }
        },

        _handleAttributeSelectorChange: function(selectElement, data, lastSelection, savePrepare) {
            var self = this,
                attributes = data.Attributes;
            selectElement = $(selectElement);
            for (var i in attributes) {
                if (attributes.hasOwnProperty(i)) {
                    var matchedTableButton = $('#' + attributes[i].id + '_button_matched_table');

                    if ('sel_' + attributes[i].id === selectElement.attr('id') &&
                        $.trim(matchedTableButton.html())) {
                        var d = self.i18n.beforeAttributeChange;
                        $('<div class="ml-modal dialog2" title="' + self.i18n.note + '"></div>').html(d).jDialog({
                            width: (d.length > 1000) ? '700px' : '500px',
                            buttons: {
                                Ok: {
                                    'text': self.i18n.buttonOk,
                                    click: function() {
                                        var lastSelectedOption = selectElement.find('option[value=' + lastSelection + ']'),
                                            optGroup = lastSelectedOption.closest('optgroup').attr('label'),
                                            optionText = lastSelectedOption.text().split(':')[1] ?
                                            lastSelectedOption.text().split(':')[1] : lastSelectedOption.text();
                                        lastSelectedOption.text(optGroup ? optGroup + ': ' + optionText : optionText);
                                        selectElement.val(lastSelection);
                                        $(this).dialog('close');
                                    }
                                }
                            }
                        });
                    }

                    if (('sel_' + attributes[i].id === selectElement.attr('id') && !$.trim(matchedTableButton.html())) ||
                        ('sel_' + attributes[i].id + '_custom_name' === selectElement.attr('id'))) {

                        if (selectElement.attr('id').indexOf('custom_name') === -1) {
                            self._buildMPShopMatching(selectElement, attributes[i], savePrepare);
                        }
                        else {
                            self._buildMPShopMatchingCustom(selectElement, attributes[i]);
                        }

                        if (typeof attributes[i].CurrentValues.Values == 'undefined' || attributes[i].CurrentValues.Values.constructor === String) {
                            break;
                        }

                        var currentValues = $.map(attributes[i].CurrentValues.Values, function(value) {
                            return [value];
                        });

                        currentValues.forEach(function(entry) {
                            // remove set values but not ones that were deleted on marketplace
                            if (typeof entry.Shop != 'undefined' && !attributes[i].Deleted) {
                                self._removeAttributeFromDropDown(attributes[i].AttributeCode, entry.Shop.Key);
                            }
                        });
                        break;
                    }
                }
            }
        },

        _loadMPVariation: function(val, customIdentifierVal, initial) {
            var self = this;

            self._resetMPVariation();
            if (val === 'null') {
                self.elements.matchingInput.html(self.html.valuesBackup);
                self.elements.matchingOptionalInput.html(self.html.valuesOptionalBackup);
                self.elements.matchingCustomInput.html(self.html.valuesCustomBackup);
                return;
            }

            self._load({
                'Action': 'LoadMPVariations',
                'SelectValue': val,
                'CustomIdentifierValue': customIdentifierVal
            }, function(data) {
                self._buildShopVariationSelectors(data, !initial, true);
            });
        },

        _saveMatching: function(savePrepare, callback) {
            var self = this;
            if (!self.saveInProgress) {
                self.saveInProgress = true;
                self._load({
                    'Action': 'SaveMatching',
                    'AttributeCodeKey' : savePrepare,
                    'Variations': $(self.elements.form).serialize()
                }, function(data) {
                    self._buildShopVariationSelectors(data, true, savePrepare);
                    self.saveInProgress = false;
                    if ($.isFunction(callback)) {
                        callback.call(self);
                    }
                });
            }
        },

        _deleteRow: function(r) {
            $(r).closest('table')[0].deleteRow(r.parentNode.parentNode.rowIndex);
            this._saveMatching(r.value);
        },

        _removeAttributeFromDropDown: function(code, key) {
            var self = this;
            $('select[name="ml[match]' + self.attributesNamePrefix + '[' + code + '][Values][0][Shop][Key]"] option[value="' + key + '"]').hide();
        },

        isMultiSelectType: function(type) {
            var self = this,
                loverCaseType = type.toLowerCase();

            return loverCaseType === self.multiSelectDataTypeCode || loverCaseType === self.multiSelectAndTextDataTypeCode;
        },

        _orderSelectOptions: function(attribute, removeFreeText) {
            var self = this,
                shopKeySelector = 'select[name="ml[match]' + self.attributesNamePrefix + '[' + attribute.AttributeCode + '][Values][0][Shop][Key]"]',
                shopCodeSelector = 'select[name="ml[match]' + self.attributesNamePrefix + '[' + attribute.AttributeCode + '][Code]"]',
                mpKeySelector = 'select[name="ml[match]' + self.attributesNamePrefix + '[' + attribute.AttributeCode + '][Values][0][Marketplace][Key]"]',
                shopAttributeCode = $(shopCodeSelector).val(),
                shopAttributes = self.options.shopVariations,
                shopAttributeDataType = shopAttributes[shopAttributeCode] ? shopAttributes[shopAttributeCode]['Type'] : '',
                isShopMultiSelect = self.isMultiSelectType(shopAttributeDataType),
                isMPMultiSelect = self.isMultiSelectType(attribute.DataType);

            $(shopKeySelector).append($(shopKeySelector + ' option').remove().sort(function(a, b) {
                var at = $(a).text().toLowerCase(), bt = $(b).text().toLowerCase();
                return (at > bt) ? 1 : (at < bt ? -1 : 0);
            }));

            $(shopKeySelector)
                .prepend('<option ' + (!isShopMultiSelect ? 'disabled' : '') +' value="multiSelect">' + self.i18n.multiSelect + '</option>')
                .prepend('<option value="all">' + self.i18n.allSelect + '</option>')
                .prepend('<option selected="selected" value="null">' + self.i18n.pleaseSelect + '</option>');

            $(mpKeySelector).append($(mpKeySelector + ' option').remove().sort(function(a, b) {
                var at = $(a).text().toLowerCase(), bt = $(b).text().toLowerCase();
                return (at > bt)?1:((at < bt)?-1:0);
            }));

            $(mpKeySelector)
                .prepend('<option ' + (!isMPMultiSelect ? 'disabled' : '') +' value="multiSelect">' + self.i18n.multiSelect + '</option>')
                .prepend('<option ' + (removeFreeText ? 'disabled' : '') + ' value="manual">' + self.i18n.manualMatching + '</option>')
                .prepend('<option value="reset">' + self.i18n.resetMatching + '</option>')
                .prepend('<option value="auto">' + self.i18n.autoMatching + '</option>')
                .prepend('<option selected="selected" value="null">' + self.i18n.pleaseSelect + '</option>');
        },

        _flat_attributes: function(attributes) {
            for (var optGroup in attributes) {
                if (attributes.hasOwnProperty(optGroup)) {
                    for (var attributeName in attributes[optGroup]) {
                        if (attributes[optGroup].hasOwnProperty(attributeName)) {
                            attributes[attributeName] = attributes[optGroup][attributeName];
                        }
                    }
                    delete(attributes[optGroup]);
                }
            }
        },

        _prefix_option : function(elementId) {
            var selectBoxes = (elementId === undefined) ? $('.shopAttrSelector') : $(elementId),
                disabledSelect = false;

            selectBoxes.mouseup(addPrefix)
                .mouseleave(addPrefix)
                .keyup(doPrefixing)
                .mousedown(removePrefix);

            $.each(selectBoxes, function(index, selectBox) {
                disabledSelect = false;
                addPrefix.call(selectBox);
            });

            function addPrefix() {
                if (!disabledSelect) {
                    var selectedOption = $(this).find(':selected'),
                        selectedOptionText = selectedOption.text(),
                        optGroup = selectedOption.closest('optgroup').attr('label');

                    $(this).find(':selected').text(optGroup ? optGroup + ': ' + selectedOptionText : selectedOptionText);
                }
                disabledSelect = true;
            }

            function removePrefix() {
                disabledSelect = false;
                $(this).find('option').each(function() {
                    var optionText = $(this).text().split(':');
                    $(this).text(optionText[1]);
                });
            }

            function doPrefixing() {
                removePrefix.call(this);
                addPrefix.call(this);
            }
        },
        initSubmitFormInSingleParameter: function () {
            var self = this;

            self.elements.form.on('click', '[type=submit]', function() {
                $('[type=submit]', self.elements.form).removeAttr('data-clicked');
                $(this).attr('data-clicked', 'true');
            });

            self.elements.form.submit($.proxy(self.onFormSubmit, self));
        },
        onFormSubmit: function(e) {
            e.preventDefault();

            if (typeof tinymce === 'object') {
                // copy values from tinymce iframe to real textarea before serializion
                $('td.input textarea.tinymce').each(function() {
                    $(this).val(tinymce.get($(this).attr('name')).getContent());
                });
            }
            
            var newForm,
                self = this,
                clickedActionButton = $('[type=submit][data-clicked=true]'),
                clickedActionButtonName = clickedActionButton.attr('name') || 'mlSubmitButton',
                clickedActionButtonValue = clickedActionButton.val();
            
            // add index to multipleselects
            $('td.input select[name$="[]"]').each(function() {
                var options = $(this).find('option');
                for (var i in options) {
                    if ($.isNumeric(i) && $(options[i]).is(':selected')) {
                        self.elements.form.prepend('<input type="hidden" value="' + $(options[i]).attr('value') + '" name="' + $(this).attr('name').replace('[]', '['+ i +']') + '">');
                    }
                }
                $(this).removeAttr('name');
            });
            // add index to other inputs
            var settedArrays = {}
            $('td.input [name$="[]"]').each(function() {
                var name = $(this).attr('name');
                settedArrays[name] = settedArrays[name] === undefined ? 0 : settedArrays[name] + 1;
                $(this).attr('name',  name.replace('[]', '['+ settedArrays[name] +']'));
            });
            
            if (clickedActionButtonValue) {
                self.elements.form.append('<input type="hidden" value="' + clickedActionButtonValue + '" name="' + clickedActionButtonName + '">');
            }

            newForm = $([
                '<form action="' + self.elements.form.attr('action') + '" method="' + self.elements.form.attr('method') + '" style="display:none">',
                '<input type="hidden" name="FullSerializedForm" value="' + self.elements.form.serialize() + '">',
                '</form>'
            ].join());

            if (clickedActionButtonValue && clickedActionButtonName.search('ml\\[') === 0) {
                newForm.append('<input type="hidden" value="' + clickedActionButtonValue + '" name="' + clickedActionButtonName + '">');
            }

            self.elements.form.find("input[type='hidden']:not([name^='ml['])").each(function() {
                var newHiddenEl = $('<input type="hidden" name="' + $(this).attr('name') + '">').val($(this).val());
                newForm.append(newHiddenEl);
            });
            if(!!navigator.userAgent.match(/Version\/[\d]+.*Safari/) === true) {//safari 14.0 doesn't submit before remove function
                newForm.appendTo('body').submit()//.remove();
            } else {
                newForm.appendTo('body').submit().remove();
            }

            return false;
        }
    });

    function ml_vm_config_getMpCategoryAttributes(cID, aMode, preselectedValues, url) {
        $.ajax({
            type: 'POST',
            url: url,
            dataType: 'html',
            data: {
                'action': 'GetMpCategoryAttributes',
                'cId': cID,
                'mode': aMode,
                'preselectedValues': preselectedValues || {}
            },
            success: function(data) {
                var el = $('#ml-js-attributes' + aMode);
                el.html(data + '');
                el.css({'display': data == '' ? 'none' : 'table-row-group'});
            },
            error: function() {
            }
        });
    }

    function ml_vm_config_generateCategoryPath(dropDown, categoryPath) {
        dropDown.find('option').attr('selected', '');
        if (dropDown.find('[value='+cID+']').length > 0) {
            dropDown.find('[value='+cID+']').attr('selected','selected');
        } else {
            dropDown.append('<option selected="selected" value="'+cID+'">'+categoryPath+'</option>');
        }
    }

    $(document).ready(function() {
        $('body').on('change', '[id^=ml_matched_value_]', function() {
            var me = $(this),
                row = me.parent().parent(),
                cell = row.find("td[id^=ml_freetext_value_]");
            if (me.val() === "freetext") {
                cell.show();
                cell.find("input[id^=ml_key_]").removeAttr("disabled");
                row.find('button.ml-save-matching').show();
                row.find('button.ml-delete-row').hide();
            } else {
                cell.hide();
                cell.find("input[id^=ml_key_]").attr("disabled", "disabled");
                row.find('button.ml-save-matching').hide();
                row.find('button.ml-delete-row').show();
            }
        }).on('change', '[id^=ml_value_]', function() {
            $(this).parent().parent().find('[name$="[Marketplace][Value]"]').val($(this).val());
        });

        $('#selectPrimaryCategory').click(function() {
            var categoryEl = $('#PrimaryCategory');
            mpCategorySelector.startCategorySelector(function(cID, categoryPath) {
                ml_vm_config_generateCategoryPath(categoryEl, categoryPath);
                categoryEl.trigger('change');
            }, 'mp');
        });

        if (ml_vm_config.handleCategoryChange) {
            $('#PrimaryCategory').change(function() {
                ml_vm_config_getMpCategoryAttributes($(this).val(), 'primary', $('#primaryPreselectedValues').val(), ml_vm_config.url);
            });
        }

        $('button.ml-reset-matching').click(function(){
            var me = $(this),
                d = ml_vm_config.i18n.resetInfo;

            $('<div class="ml-modal dialog2" title="' + ml_vm_config.i18n.resetMatching + '"></div>').html(d).jDialog({
                width: (d.length > 1000) ? '700px' : '500px',
                buttons: {
                    Cancel: {
                        'text': ml_vm_config.i18n.buttonCancel,
                        click: function() {
                            $(this).dialog('close');
                        }
                    },
                    Ok: {
                        'text': ml_vm_config.i18n.buttonOk,
                        click: function() {
                            $.blockUI(blockUILoading);
                            me.closest('form')
                                .append('<input type="hidden" name="Action" value="ResetMatching">')
                                .submit();
                            $(this).dialog('close');
                        }
                    }
                }
            });
        });
    });
})(jQuery);
