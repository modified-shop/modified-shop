$(document).ready(function() {
    /**
     * example for overriding default variation matching js behavior
     *
     * $.widget("ui.metro_variation_matching", $.ui.ml_variation_matching, {
     *     _init: function() {
     *         this._super();
     *         myConsole.log('new init');
     *     }
     * });
     *
     * After this, starting widget should be done like this:
     * $(ml_vm_config.formName).metro_variation_matching({...});
     */

    $.widget("ui.otto_variation_matching", $.ui.ml_variation_matching, {
        _init: function() {
            this._super();
            var self = this,
                independentData = {};
            self._initCategoryIndependentAttributes();

            $('body')
                .on('click', 'button.ml-save-independent-matching', function() {
                    self._saveIndependentMatching(this.value);
                })
                .on('click', 'button.ml-delete-independent-matching', function() {
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
                                    self._saveIndependentMatching(savePrepare);
                                    $(this).dialog('close');
                                }
                            }
                        }
                    });
                })
                .on('click', 'button.ml-collapse ml-independent-collapse', function() {
                    var matchedTable = $('div#match_' + this.value);
                    if (matchedTable.css('display') == 'none') {
                        $('span.ml-collapse ml-independent-collapse[name="' + this.value + '_collapse_span_name"]').css('background-position', '0 -23px');
                        matchedTable.show();
                    } else {
                        $('span.ml-collapse ml-independent-collapse[name="' + this.value + '_collapse_span_name"]').css('background-position', '0 0px');
                        matchedTable.hide();
                    }
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

        _saveIndependentMatching: function(savePrepare, callback) {
            var self = this;
            if (!self.saveInProgress) {
                self.saveInProgress = true;
                self._load({
                    'Action': 'SaveMatching',
                    'VariationKind': 'IndependentShopVariation',
                    'AttributeCodeKey' : savePrepare,
                    'Variations': $(self.elements.form).serialize()
                }, function(data) {
                    self._buildShopIndependentVariationSelectors(data, true, savePrepare);
                    self.saveInProgress = false;
                    if ($.isFunction(callback)) {
                        callback.call(self);
                    }
                });
            }
        },

        _initCategoryIndependentAttributes: function() {
            var self = this;
            $('#categoryIndependentAttributes').append(self._loadCategoryIndependentAttributes());
        },

        _loadCategoryIndependentAttributes: function() {
            var self = this;
            self._load({
                'where': 'prepareView',
                'Action': 'LoadCategoryIndependentAttributes'
            }, function(data) {
                self._buildShopIndependentVariationSelectors(data, false, true);
            });
        },

        _buildShopIndependentVariationSelectors: function(data, resetNotice, savePrepare) {
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

            $('#tbodyDynamicIndependentMatchingInput').html('');
            $('#tbodyDynamicIndependentMatchingOptionalInput').html('');

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
                        $('#tbodyDynamicIndependentMatchingInput').append($(self._render(deletedAttrTemplate, [attributes[i]])))
                    } else {
                        attributes[i] = self._buildShopIndependentVariationSelector(attributes, i);
                        matchingInputEl = $('#tbodyDynamicIndependentMatchingInput');
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
                            matchingInputEl = $('#tbodyDynamicIndependentMatchingOptionalInput');

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
                $('#tbodyDynamicIndependentMatchingInput').append('<tr><th></th><td class="input">'
                    + self.i18n.categoryWithoutAttributesInfo
                    + '</td><td class="info"></td></tr>');
                $('#tbodyDynamicIndependentMatchingHeadline').css('display', 'none');
                $('#tbodyDynamicIndependentMatchingOptionalInput').css('display', 'none');
            }

            if (!$.trim($('#tbodyDynamicIndependentMatchingInput').html())) {
                self.elements.matchingHeadline.css('display', 'none');
                $('#tbodyDynamicIndependentMatchingInput').css('display', 'none');
            }

            if (!$.trim($('#tbodyDynamicIndependentMatchingOptionalInput').html())) {
                $('#tbodyDynamicIndependentMatchingHeadline').css('display', 'none');
                $('#tbodyDynamicIndependentMatchingOptionalInput').css('display', 'none');
            } else if (attributesSize > self.optionalAttributesMaxSize && attributesSelectorOptions.length > 1) {
                $('#tbodyDynamicIndependentMatchingOptionalInput').append($([
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

            $('#tbodyDynamicIndependentMatchingInput').append('<tr class="spacer"><td colspan="3">&nbsp;</td></tr>');
            $('#tbodyDynamicIndependentMatchingOptionalInput').append('<tr class="spacer"><td colspan="3">&nbsp;</td></tr>');

            function addShopVariationSelectorChangeListener() {
                var previous;
                $(this).on('focus', function() {
                    previous = $(this).val();
                }).change(function() {
                    self._handleIndependentAttributeSelectorChange(this, data, previous, savePrepare);
                });
            }

            $('#tbodyDynamicIndependentMatchingInput').find('select[id^=sel_]').each(addShopVariationSelectorChangeListener);
            $('#tbodyDynamicIndependentMatchingOptionalInput').find('select[id^=sel_]').each(addShopVariationSelectorChangeListener);
            for (i in attributes) {
                if (attributes.hasOwnProperty(i)) {
                    if (typeof attributes[i].CurrentValues.Code !== 'undefined') {
                        var customAttributeValue = (self.options.shopVariations[attributes[i].CurrentValues.CustomAttributeValue]) ?
                            attributes[i].CurrentValues.CustomAttributeValue : 'freetext';
                        $('#tbodyDynamicIndependentMatchingInput').find('select[id=sel_' + attributes[i].id + ']').val(attributes[i].CurrentValues.Code).trigger('change');
                        $('#tbodyDynamicIndependentMatchingOptionalInput').find('select[id=sel_' + attributes[i].id + ']').val(attributes[i].CurrentValues.Code).trigger('change');
                    }
                }
            }
            self._attachIndependentAttributeSelector(attributesSelectorOptions, addShopVariationSelectorChangeListener);

            for (i in attributes) {
                $('[id="sel_'+attributes[i].id+'"]').select2({});
                $('[id="sel_'+attributes[i].id+'"]').on('select2:open', function (e) {
                    if (this.options.length === 1) {
                        var name = $(this).attr('name'),
                            mpDataType = $('input[name="' + $(this).attr('name').replace('[Code]', '[Kind]') + '"]').val(),
                            span = $(this).closest("span"),
                            select = $('select[name="' + name + '"]');

                        span.css("width", "81%");
                        // $(this).find('option').remove().end();

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

        _handleIndependentAttributeSelectorChange: function(selectElement, data, lastSelection, savePrepare) {
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
                            self._buildMPShopIndependentMatching(selectElement, attributes[i], savePrepare, data.MarketplaceId);
                        }
                        else {
                            self._buildMPShopIndependentMatchingCustom(selectElement, attributes[i]);
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

        _buildMPShopIndependentMatching: function(elem, selector, savePrepare, mpId) {
            var self = this,
                values = self.options.shopVariations[elem.val()],
                matchDiv = $('div#match_' + selector.id),
                attributeListDiv = $('div#attributeList_' + selector.id),
                deleteButton = $('#' + self.generateIndependentAttributeCodeId(selector.AttributeCode) + '_deleteMatching'),
                mpValues = $.extend({}, selector.AllowedValues),
                style = '',
                removeFreeTextOption = selector.DataType.toLowerCase().indexOf('text') === -1,
                addAfterWarning = false,
                spanWarning = $('span#' + self.generateIndependentAttributeCodeId(selector.AttributeCode) + '_warningMatching'),
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

                var saveButton = $('div#extraFieldsInfo_' + self.generateIndependentAttributeCodeId(selector.AttributeCode) + ' button.ml-save-independent-matching');
                if (!saveButton.length) {
                    $('div#extraFieldsInfo_' + self.generateIndependentAttributeCodeId(selector.AttributeCode)).prepend(
                        '<button type="button" class="ml-button mlbtn-action ml-save-independent-matching add-matching" value="' + selector.AttributeCode + '">+</button>'
                    );
                }
            }

            if (elem.val() === selector.CurrentValues.Code) {
                attributeListDiv.attr('style', 'background-color: #e9e9e9');
                if (!deleteButton.length) {
                    if (addAfterWarning) {
                        spanWarning.before(
                            '<span id="' + self.generateIndependentAttributeCodeId(selector.AttributeCode) + '_deleteMatching">' +
                            '<button type="button" class="ml-button mlbtn-action ml-delete-independent-matching" value="' + elem.attr('id') + '">-</button>' +
                            '<span>' + self.i18n.alreadyMatched + '</span>' +
                            '</span>'
                        );
                    } else {
                        $('div#extraFieldsInfo_' + self.generateIndependentAttributeCodeId(selector.AttributeCode)).append(
                            '<span id="' + self.generateIndependentAttributeCodeId(selector.AttributeCode) + '_deleteMatching">' +
                            '<button type="button" class="ml-button mlbtn-action ml-delete-independent-matching" value="' + elem.attr('id') + '">-</button>' +
                            '<span>' + self.i18n.alreadyMatched + '</span>' +
                            '</span>'
                        );
                    }
                }
                if ((typeof selector.CurrentValues.Code !== 'undefined') && (elem.val() !== selector.CurrentValues.Code)) {
                    $('div#extraFieldsInfo_' + self.generateIndependentAttributeCodeId(selector.AttributeCode)).children('*:not(.add-matching)').hide();
                }
            } else {
                if (!attributeRow.hasClass('customAttribute')) {
                    $('div#extraFieldsInfo_' + self.generateIndependentAttributeCodeId(selector.AttributeCode)).children('*:not(.add-matching)').hide();
                }
            }

            if (isDataCustom) {
                return matchDiv.append('<input type="hidden" name="ml[match][CategoryIndependentShopVariation]' + '[' + selector.AttributeCode + '][Values]" value="true">');
            }

            if (elem.val() === 'freetext' || elem.val() === 'attribute_value' || elem.val() === 'database_value') {
                self._buildIndependentSelectMatching(elem, selector, matchDiv, attributeListDiv);
                $('select[name="ml[match][CategoryIndependentShopVariation][' + selector.AttributeCode + '][Values]"]').select2({});
                return self;
            }

            matchDiv.css('margin-top', '10px');

            if (selector.AllowedValues.length === 0) {
                // if AllowedValues is empty, it indicates there are no required attribute values from marketplace
                // for this, we use shop's values but do not use keys for attributes because we send to marketplace
                // keys, which in case of shop should be values because marketplace cannot recognize those keys
                for (var k in values.Values) {
                    if (values.Values.hasOwnProperty(k)) {
                        mpValues[values.Values[k]] = values.Values[k];
                    }
                }

                removeFreeTextOption = false;
            }

            if (elem[0].value == 'manufacturer' && selector.AttributeCode == 'Brand') {
                matchDiv.append(self._buildIndependentBrandMatchingTableSelectors(selector, values.Values, mpValues, selector.CurrentValues.Error, mpId, true))
                    .append(self._buildIndependentMatchingTableBody(selector, elem, savePrepare))
                    .append(self._buildBrandPaginations(selector));
            } else if (selector.AttributeCode == 'Brand') {
                matchDiv.append(self._buildIndependentBrandMatchingTableSelectorsOnNonBrandMatching(selector, values.Values, mpValues, selector.CurrentValues.Error, mpId, true))
                    .append(self._buildIndependentMatchingTableBody(selector, elem, savePrepare));
            } else {
                matchDiv.append(self._buildIndependentMatchingTableSelectors(selector, values.Values, mpValues, selector.CurrentValues.Error))
                    .append(self._buildIndependentMatchingTableBody(selector, elem, savePrepare));
            }

            self._changeTriggerIndependentVariationMarketplace(selector.AttributeCode);
            self._orderIndependentSelectOptions(selector, removeFreeTextOption);

            var baseName = 'ml[match][CategoryIndependentShopVariation][' + selector.AttributeCode + '][Values]';
            if (elem[0].value != 'manufacturer' && selector.AttributeCode != 'Brand') {
                $('select[name="' + baseName + '[0][Shop][Key]"]').select2({});
                $('select[name="' + baseName + '[0][Marketplace][Key]"]').select2({});
            } else if (elem[0].value != 'manufacturer' && selector.AttributeCode == 'Brand') {
                $('select[name="' + baseName + '[0][Shop][Key]"]').select2({});
            } else if (elem[0].value == 'manufacturer' && selector.AttributeCode != 'Brand') {
                $('select[name="' + baseName + '[0][Shop][Key]"]').select2({});
                $('select[name="' + baseName + '[0][Marketplace][Key]"]').select2({});
            }

            return matchDiv;
        },

        _buildIndependentMatchingTableBody: function(selector, elem, savePrepare) {
            var self = this,
                deleteButton = $('#' + self.generateIndependentAttributeCodeId(selector.AttributeCode) + '_deleteMatching');
            if (Object.keys(selector.CurrentValues).length > 0 && (selector.CurrentValues.Values != undefined && selector.CurrentValues.Values != 'undefined')
                && (selector.CurrentValues.Values.length > 0 || Object.keys(selector.CurrentValues.Values).length > 0)
                && elem.val() === selector.CurrentValues.Code) {
                // reload saved values
                var tableBody = '',
                    i = 1,
                    disableFreeTextOption = selector.DataType.toLowerCase().indexOf('text') === -1 ? 'disabled' : '',
                    addAfterWarning = false,
                    spanWarning = $('span#' + self.generateIndependentAttributeCodeId(selector.AttributeCode) + '_warningMatching');

                if (typeof spanWarning.html() !== 'undefined') {
                    addAfterWarning = true;
                }

                var iPage = 1;
                // for (var code in selector.CurrentValues.Values && code !== 'FreeText') {
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
                            tableBody += self._render(self._getIndependentMatchingTableTemplate(selector.AttributeCode,
                                entry.Shop.Key, entry.Marketplace.Key, isShopMultiValue, isMPMultiValue, iPage), [matchingTemplateInformation]);

                            iPage = ++iPage;
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
                            '<span id="' + self.generateIndependentAttributeCodeId(selector.AttributeCode) + '_deleteMatching">' +
                            '<button type="button" class="ml-button mlbtn-action ml-delete-independent-matching" value="' + elem.attr('id') + '">-</button>' +
                            '<span>' + self.i18n.alreadyMatched + '</span>' +
                            '</span>'
                        );
                    } else {
                        $('div#extraFieldsInfo_' + self.generateIndependentAttributeCodeId(selector.AttributeCode)).append(
                            '<span id="' + self.generateIndependentAttributeCodeId(selector.AttributeCode) + '_deleteMatching">' +
                            '<button type="button" class="ml-button mlbtn-action ml-delete-independent-matching" value="' + elem.attr('id') + '">-</button>' +
                            '<span>' + self.i18n.alreadyMatched + '</span>' +
                            '</span>'
                        );
                    }
                }

                if (self.isPrepareForm) {
                    $('div#extraFieldsInfo_' + self.generateIndependentAttributeCodeId(selector.AttributeCode)).append(
                        '<span id="' + self.generateIndependentAttributeCodeId(selector.AttributeCode) + '_collapseMatching" style="float: right">' +
                        '<button type="button" class="ml-collapse ml-independent-collapse" value="' + self.generateIndependentAttributeCodeId(selector.AttributeCode) + '" name="' + self.generateIndependentAttributeCodeId(selector.AttributeCode) + '_collapse_button_name">' +
                        '<span class="ml-collapse ml-independent-collapse" name="' + self.generateIndependentAttributeCodeId(selector.AttributeCode) + '_collapse_span_name"></span>' +
                        '</button>' +
                        '</span>'
                    );
                }

                if (savePrepare === selector.AttributeCode || !self.isPrepareForm) {
                    $('span.ml-collapse ml-independent-collapse[name="' + self.generateIndependentAttributeCodeId(selector.AttributeCode) + '_collapse_span_name"]').css('background-position', '0 -23px');
                    $('div#match_' + self.generateIndependentAttributeCodeId(selector.AttributeCode)).show();
                } else {
                    $('span.ml-collapse ml-independent-collapse[name="' + self.generateIndependentAttributeCodeId(selector.AttributeCode) + '_collapse_span_name"]').css('background-position', '0 0px');
                    $('div#match_' + self.generateIndependentAttributeCodeId(selector.AttributeCode)).hide();
                }

                if ((typeof selector.CurrentValues.Code !== 'undefined') && (elem.val() !== selector.CurrentValues.Code)) {
                    $('div#extraFieldsInfo_' + self.generateIndependentAttributeCodeId(selector.AttributeCode)).children('*:not(.add-matching)').hide();
                }

                return $(
                    '<span id="spanMatchingTable" style="padding-right:2em;">' +
                    '   <div style="font-weight: bold; background-color: #e9e9e9">' + self.i18n.matchingTable + '</div>' +
                    '   <table id="' + self.generateIndependentAttributeCodeId(selector.AttributeCode) + '_button_matched_table" style="width:100%; background-color: #e9e9e9">' +
                    '       <tbody>' +
                                tableBody +
                    '       </tbody>' +
                    '   </table>' +
                    '</span>');
            }

            return '';
        },

        _getIndependentMatchingTableTemplate: function(attributeCode, valueShopKey, valueMarketplaceKey, isShopMultiSelect,
                                                    isMPMultiSelect, iPage) {
            var self = this;

            // Shop and marketplace matching template strings, when there is no multi-matching, extracted into variables.
            var shopMatchingTemplate =
                '<input type="hidden" name="ml[match][CategoryIndependentShopVariation]' + '[' + attributeCode + '][Values][{key}][Shop][Key]" value="{valueShopKey}">' +
                '<input type="hidden" name="ml[match][CategoryIndependentShopVariation]' + '[' + attributeCode + '][Values][{key}][Shop][Value]" value="{valueShopValue}">' +
                '{valueShopValue}',

                mpMatchingTemplate =
                    '<select id="ml_matched_value_' + self.generateIndependentAttributeCodeId(attributeCode) + '_{valueShopKey}" style="width: 100%">' +
                        '<option {disabled} value="freetext">' + this.i18n.manualMatching + '</option>' +
                        '<option selected="selected" value="{valueMarketplaceKey}">{valueMarketplaceInfo}</option>' +
                    '</select>' +
                    '<input type="hidden" name="ml[match][CategoryIndependentShopVariation]' + '[' + attributeCode + '][Values][{key}][Marketplace][Key]" value="{valueMarketplaceKey}">' +
                    '<input type="hidden" name="ml[match][CategoryIndependentShopVariation]' + '[' + attributeCode + '][Values][{key}][Marketplace][Value]" value="{valueMarketplaceValue}">' +
                    '<input type="hidden" name="ml[match][CategoryIndependentShopVariation]' + '[' + attributeCode + '][Values][{key}][Marketplace][Info]" value="{valueMarketplaceInfo}">';

            // If shop value is multiValue (array of keys and values) shop matching template should be changed. It is
            // changed in a way that it submits values in the same way as for initial saving.
            if (isShopMultiSelect) {

                // Forming shop multi select options which should be submitted.
                var shopMultiOptions = formMultiSelectOptions(valueShopKey);

                // Shop matching template will have hidden select multiple which will carry data about shop keys codes.
                // Shop values will be serialized as array into hidden input.
                shopMatchingTemplate =
                    '<select multiple="multiple" class="ml-hidden" name="ml[match][CategoryIndependentShopVariation]' + '[' + attributeCode + '][Values][{key}][Shop][Key][]">' +
                        shopMultiOptions +
                    '</select>' +
                    '<input type="hidden" name="ml[match][CategoryIndependentShopVariation]' + '[' + attributeCode + '][Values][{key}][Shop][Value]" value={valueShopValueSerialized}>' +
                    '{valueShopValue}';
            }

            // If marketplace value is multiValue (array of keys and values) marketplace matching template should be changed.
            // It is changed in a way that it submits values in the same way as for initial saving.
            if (isMPMultiSelect) {
                // Forming MP multi select options which should be submitted.
                var mpMultiOptions = formMultiSelectOptions(valueMarketplaceKey);

                mpMatchingTemplate =
                    '<select id="ml_matched_value_' + self.generateIndependentAttributeCodeId(attributeCode) + '_{valueShopKey}" style="width: 100%">' +
                    '   <option {disabled} value="freetext">' + this.i18n.manualMatching + '</option>' +
                    '   <option selected="selected" value="{valueMarketplaceKey}">{valueMarketplaceInfo}</option>' +
                    '</select>' +
                    '<select multiple="multiple" class="ml-hidden" name="ml[match][CategoryIndependentShopVariation]' + '[' + attributeCode + '][Values][{key}][Marketplace][Key][]">' +
                        mpMultiOptions +
                    '</select>' +
                    '<input type="hidden" name="ml[match][CategoryIndependentShopVariation]' + '[' + attributeCode + '][Values][{key}][Marketplace][Value]" value={valueMarketplaceValueSerialized}>' +
                    '<input type="hidden" name="ml[match][CategoryIndependentShopVariation]' + '[' + attributeCode + '][Values][{key}][Marketplace][Info]" value="{valueMarketplaceInfo}">';
            }

            // Helper function for making options in multi select.
            function formMultiSelectOptions(sourceValues) {
                var destination = '';
                $.each(sourceValues, function (index, sourceValue) {
                    destination += '<option value="' + sourceValue + '" selected></option>';
                });

                return destination;
            }

            var tableStyle = '';
            // add pagination styles for Brand
            if (attributeCode == 'Brand') {
                tableStyle = 'class="brand-page'+ Math.ceil(iPage/10)  +'" style="display: none"';
            }

            // Final value of template string for rendering returned.
            return '<tr ' + tableStyle + '>' +
                '   <td style="width: 35%">' +
                        shopMatchingTemplate +
                '   </td>' +
                '   <td style="width: 35%">' +
                        mpMatchingTemplate +
                '   </td>' +
                '   <td id="ml_freetext_value_' + self.generateIndependentAttributeCodeId(attributeCode) + '_{valueShopKey}" style="border: none; display: none;">' +
                '       <input type="hidden" disabled="disabled" id="ml_key_' + self.generateIndependentAttributeCodeId(attributeCode) + '_{valueShopKey}" name="ml[match][CategoryIndependentShopVariation]' + '[' + attributeCode + '][Values][{key}][Marketplace][Key]" value="manual">' +
                '       <input type="text" id="ml_value_' + self.generateIndependentAttributeCodeId(attributeCode) + '_{valueShopKey}" style="width:100%;">' +
                '   </td>' +
                '   <td style="border: none">' +
                '       <button type="button" class="ml-button mlbtn-action ml-save-independent-matching" value="' + attributeCode + '" style="display: none;">+</button>' +
                '       <button type="button" class="ml-button mlbtn-action ml-delete-row" value="' + attributeCode + '">-</button>' +
                '   </td>' +
                '</tr>' +
                '<script>' +
                '   $("#matched_value_' + self.generateIndependentAttributeCodeId(attributeCode) + '_{valueShopKey}").change();' +
                '   $("#value_' + self.generateIndependentAttributeCodeId(attributeCode) + '_{valueShopKey}").change();' +
                '</script>';
        },

        _buildBrandPaginations(selector) {
            if (Object.keys(selector.CurrentValues).length > 0 && selector.CurrentValues.Values !== undefined &&
                    (selector.CurrentValues.Values.length > 0 || Object.keys(selector.CurrentValues.Values).length > 0)) {
                var elCount = Object.keys(selector.CurrentValues.Values).length,
                paginationPerPage = 10,
                page = 1,
                html = '';

                var totalPages = Math.ceil(elCount / paginationPerPage);

                while (page <= totalPages) {
                    html += '<a href="#" onclick="event.preventDefault(); showPage('+page+')" id="brand-page'+page+'"  class="brand-pagination-link">'+page+'</a>';
                    page = ++page;
                }

                var html = '<div class="brand-pagination ml-js-noBlockUi">'
                    + '    <a href="#" onclick="event.preventDefault(); showPage(1)" id="brand-page-first" class="brand-pagination-link ml-js-noBlockUi">First</a>'
                    +       html
                    + '    <a href="#" onclick="event.preventDefault(); showPage('+totalPages+')" id="brand-page-last" class="brand-pagination-link ml-js-noBlockUi">Last</a>'
                    + '</div>'
                    + '<script>'
                    + 'var show = document.getElementsByClassName("brand-page1");'
                    + 'var currentPage = 1;'
                    + 'for (var k = 0; k < show.length; k++) {'
                    + '    show[k].style.display = "table-row";'
                    + '}'
                    + 'function showPage(id) { '
                    + '    var totalNumberOfPages = '+totalPages+';'
                    + '    for (var i = 1; i < (totalNumberOfPages + 1); i++) {'
                    + '        var hide = document.getElementsByClassName("brand-page"+i);'
                    + '        document.getElementById("brand-page-first").removeAttribute("disabled");'
                    + '        document.getElementById("brand-page-last").removeAttribute("disabled");'
                    + '        document.getElementById("brand-page"+i).removeAttribute("disabled");'
                    + '        for (var j = 0; j < hide.length; j++) {'
                    + '            hide[j].style.display = "none";'
                    + '        }'
                    + '    }'
                    + '        var show = document.getElementsByClassName("brand-page"+id);'
                    + '        document.getElementById("brand-page"+id).setAttribute("disabled","disabled");'
                    + '        if (id == 1) document.getElementById("brand-page-first").setAttribute("disabled","disabled");'
                    + '        if (id == totalNumberOfPages) document.getElementById("brand-page-last").setAttribute("disabled","disabled");'
                    + '        for (var k = 0; k < show.length; k++) {'
                    + '            show[k].style.display = "table-row";'
                    + '        }'
                    + '    }'
                    + '</script>';
                return html;
            }
            return '';
        },

        _buildMPShopIndependentMatchingCustom: function(selectElement, attribute) {
            var self = this;

            selectElement.parent().find('#input_' + self.generateIndependentAttributeCodeId(attribute.AttributeCode) + '_custom_name').hide();
            if (selectElement.val() === 'freetext' || selectElement.val() === 'attribute_value' ||
                selectElement.val() === 'database_value') {
                selectElement.parent().find('#input_' + self.generateIndependentAttributeCodeId(attribute.AttributeCode) + '_custom_name')
                    .val(attribute.CurrentValues.CustomName ? attribute.CurrentValues.CustomName : '').show();
            } else {
                var selectedOption = selectElement.find('option:selected'),
                    attributeName = (selectedOption.index() > 0 || selectedOption.parent().is("optgroup")) ?
                    selectedOption.text() : '';
                selectElement.parent().find('#input_' + self.generateIndependentAttributeCodeId(attribute.AttributeCode) + '_custom_name').val(attributeName);
            }
        },

        _buildIndependentSelectMatching: function(elem, selector, matchDiv, attributeListDiv) {
            var self = this,
                deleteButton = $('#' + self.generateIndependentAttributeCodeId(selector.AttributeCode) + '_deleteMatching'),
                addAfterWarning = false,
                spanWarning = $('span#' + self.generateIndependentAttributeCodeId(selector.AttributeCode) + '_warningMatching');

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
                                '<span id="' + self.generateIndependentAttributeCodeId(selector.AttributeCode) + '_deleteMatching">' +
                                '<button type="button" class="ml-button mlbtn-action ml-delete-independent-matching" value="' + elem.attr('id') + '">-</button>' +
                                '<span>' + self.i18n.alreadyMatched + '</span>' +
                                '</span>'
                            );
                        } else {
                            $('div#extraFieldsInfo_' + self.generateIndependentAttributeCodeId(selector.AttributeCode)).append(
                                '<span id="' + self.generateIndependentAttributeCodeId(selector.AttributeCode) + '_deleteMatching">' +
                                '<button type="button" class="ml-button mlbtn-action ml-delete-independent-matching" value="' + elem.attr('id') + '">-</button>' +
                                '<span>' + self.i18n.alreadyMatched + '</span>' +
                                '</span>'
                            );
                        }
                    }
                } else {
                    $('div#extraFieldsInfo_' + self.generateIndependentAttributeCodeId(selector.AttributeCode)).children('*:not(.add-matching)').hide();
                }

                matchDiv.css('display', 'inline-block').css('width', '40%');
                return matchDiv.append('<input type="text" style="width:100%" name="ml[match][CategoryIndependentShopVariation]' + '[' + selector.AttributeCode + '][Values]" value="' + freetext + '">');
            }

            if (elem.val() === 'attribute_value') {
                var attr_value = selector.CurrentValues.Values;
                attributeListDiv.attr('style', 'background-color: #e9e9e9');
                if (!deleteButton.length) {
                    if (addAfterWarning) {
                        spanWarning.before(
                            '<span id="' + self.generateIndependentAttributeCodeId(selector.AttributeCode) + '_deleteMatching">' +
                            '<button type="button" class="ml-button mlbtn-action ml-delete-independent-matching" value="' + elem.attr('id') + '">-</button>' +
                            '<span>' + self.i18n.alreadyMatched + '</span>' +
                            '</span>'
                        );
                    } else {
                        $('div#extraFieldsInfo_' + self.generateIndependentAttributeCodeId(selector.AttributeCode)).append(
                            '<span id="' + self.generateIndependentAttributeCodeId(selector.AttributeCode) + '_deleteMatching">' +
                            '<button type="button" id="selector.CurrentValues.Code" class="ml-button mlbtn-action ml-delete-independent-matching" value="' + elem.attr('id') + '">-</button>' +
                            '<span>' + self.i18n.alreadyMatched + '</span>' +
                            '</span>'
                        );
                    }
                }

                matchDiv.css('display', 'inline-block').css('width', '40%');
                var style = selector.CurrentValues.Error ? ' style="border-color:red;"' : '';

                if ((typeof selector.CurrentValues.Code !== 'undefined') && (elem.val() !== selector.CurrentValues.Code)) {
                    $('div#extraFieldsInfo_' + self.generateIndependentAttributeCodeId(selector.AttributeCode)).children('*:not(.add-matching)').hide();
                }

                var multiple = '';
                var name = 'ml[match][CategoryIndependentShopVariation]' + '[' + selector.AttributeCode + '][Values]';
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
                    + '<select style="width:180px;" name="ml[match][CategoryIndependentShopVariation]' + '[' + selector.AttributeCode + '][Values][Table]">'
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
                    + '<select style="width:100px;" name="ml[match][CategoryIndependentShopVariation]' + '[' + selector.AttributeCode + '][Values][Column]">'
                    + '<option value="">Please choose</option>'
                    + '</select>'
                    + '</div>'
                    + '<div style="display:inline-block;margin-left:10px">'
                    + self.i18n.dbalias
                    +   '<input type="text" name="ml[match][CategoryIndependentShopVariation]' + '[' + selector.AttributeCode + '][Values][Alias]" value="' + selectedAlias + '">'
                    + '</div>'
                );

                $('select[name="ml[match][CategoryIndependentShopVariation]' + '[' + selector.AttributeCode + '][Values][Table]"]').change(function() {
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
                                    '<span id="' + self.generateIndependentAttributeCodeId(selector.AttributeCode) + '_deleteMatching">' +
                                    '<button type="button" class="ml-button mlbtn-action ml-delete-independent-matching" value="' + elem.attr('id') + '">-</button>' +
                                    '<span>' + self.i18n.alreadyMatched + '</span>' +
                                    '</span>'
                                );
                            } else {
                                $('div#extraFieldsInfo_' + self.generateIndependentAttributeCodeId(selector.AttributeCode)).append(
                                    '<span id="' + self.generateIndependentAttributeCodeId(selector.AttributeCode) + '_deleteMatching">' +
                                    '<button type="button" id="selector.CurrentValues.Code" class="ml-button mlbtn-action ml-delete-independent-matching" value="' + elem.attr('id') + '">-</button>' +
                                    '<span>' + self.i18n.alreadyMatched + '</span>' +
                                    '</span>'
                                );
                            }
                        }

                        if ((typeof selector.CurrentValues.Code !== 'undefined') && (elem.val() !== selector.CurrentValues.Code)) {
                            $('div#extraFieldsInfo_' + self.generateIndependentAttributeCodeId(selector.AttributeCode)).children('*:not(.add-matching)').hide();
                        }
                    }

                    self._getIndependentTableColumns(selector.AttributeCode, selectedColumn, $(this).find(':selected').text());
                }).trigger('change');

                return html;
            }

            return '';
        },

        _changeTriggerIndependentVariationMarketplace: function(attributeCode) {
            var self = this,
                shopKeySelector = '[name="ml[match][CategoryIndependentShopVariation]' + '[' + attributeCode + '][Values][0][Shop][Key]"]',
                shopValueSelector = '[name="ml[match][CategoryIndependentShopVariation]' + '[' + attributeCode + '][Values][0][Shop][Value]"]',
                shopKeysSelector = '[name="ml[match][CategoryIndependentShopVariation]' + '[' + attributeCode + '][Values][0][Shop][Key][]"]',
                mpKeySelector = '[name="ml[match][CategoryIndependentShopVariation]' + '[' + attributeCode + '][Values][0][Marketplace][Key]"]',
                mpValueSelector = '[name="ml[match][CategoryIndependentShopVariation]' + '[' + attributeCode + '][Values][0][Marketplace][Value]"]',
                mpKeysSelector = '[name="ml[match][CategoryIndependentShopVariation]' + '[' + attributeCode + '][Values][0][Marketplace][Key][]"]';

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
                                    self._saveIndependentMatching(true);
                                    $(this).dialog('close');
                                }
                            }
                        }
                    });
                }

                if ($(this).val() === 'manual') {
                    $('td #freetext_' + self.generateIndependentAttributeCodeId(attributeCode)).show();
                } else {
                    $('td #freetext_' + self.generateIndependentAttributeCodeId(attributeCode)).hide();
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

        _orderIndependentSelectOptions: function(attribute, removeFreeText) {
            var self = this,
                shopKeySelector = 'select[name="ml[match][CategoryIndependentShopVariation]' + '[' + attribute.AttributeCode + '][Values][0][Shop][Key]"]',
                shopCodeSelector = 'select[name="ml[match][CategoryIndependentShopVariation]' + '[' + attribute.AttributeCode + '][Code]"]',
                mpKeySelector = 'select[name="ml[match][CategoryIndependentShopVariation]' + '[' + attribute.AttributeCode + '][Values][0][Marketplace][Key]"]',
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

        _buildIndependentBrandMatchingTableSelectorsOnNonBrandMatching: function(attribute, shopValues, mpValues, error, mpId, independent = false) {
            var independentCode = '';
            if (independent) {
                independentCode = '[CategoryIndependentShopVariation]';
            }

            var self = this,
                pID = $("input#pID").val(),
                baseName = 'ml[match]' + independentCode + '[' + attribute.AttributeCode + '][Values]',
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
                + '            <td class="input" style="width: 35%" id="ottoBrands">'
                + '                <select ' + style + ' name="' + baseName + '[0][Marketplace][Key]" id="select2MarketplceBrandValues">'
                + '                </select>'
                + '                 <input type="hidden" name="' + baseName + '[0][Marketplace][Value]">'
                + '            </td>'
                + '            <td id="freetext_' + self.generateIndependentAttributeCodeId(attribute.AttributeCode) + '" style="border: none; display: none;">'
                + '                <input type="text" name="' + baseName + '[FreeText]" style="width:100%;">'
                + '            </td>'
                + '            <td style="border: none">'
                + '                 <button type="button" class="ml-button mlbtn-action ml-save-independent-matching" value="' + attribute.AttributeCode + '">+'
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

            out += '<script type="text/javascript">'
                    + '     $(document).ready(function() {'
                    + '         $("#select2MarketplceBrandValues").select2('
                    + '                {'
                    + '                    ajax: {'
                    + '                        type: "POST",'
                    + '                        delay: 250, '
                    + '                        url : "magnalister.php?mp='+mpId+'&mode=prepare&view=apply&where=prepareView&kind=ajax&action=getOttoBrands",'
                    + '                        data: function (params) {'
                    + '                            return {'
                    + '                                "action": "getOttoBrands",'
                    + '                                "brandfilterSearch": params.term,'
                    + '                                "brandfilterPage": params.page || 1,'
                    + '                            };'
                    + '                        },'
                    + '                        dataType: "json"'
                    + '                    }'
                    + '                });'
                    + '        var ottoBrands = "";'
                    + '        var selectedGambioBrand = "";'
                    + '        var madeChanges = false;'
                    + '        var isStoreCategory = false;'
                    + '        function addShopBrandEventListener(elem) {'
                    + '            $("div.catelem span.toggle:not(.leaf)", $(elem)).each(function () {'
                    + '                $(this).click(function () {'
                    + '                    myConsole.log($(this).attr("id"));'
                    + '                    if ($(this).hasClass("plus")) {'
                    + '                        tmpElem = $(this);'
                    + '                        tmpElem.removeClass("plus").addClass("minus");'
                    + '                        if (tmpElem.parent().children("div.catname").children("div.catelem").length == 0) {'
                    + '                            jQuery.ajax({'
                    + '                                type: "POST",'
                    + '                                url: "magnalister.php?mp='+mpId+'&mode=prepare&view=apply&where=prepareView&kind=ajax&action=getGambioBrands",'
                    + '                                data: {'
                    + '                                   "action": "getGambioBrands",'
                    + '                                    "objID": tmpElem.attr("id"),'
                    + '                                    "isStoreCategory": isStoreCategory'
                    + '                                },'
                    + '                                success: function (data) {'
                    + '                                    appendTo = tmpElem.parent().children("div.catname");'
                    + '                                    appendTo.append(data);'
                    + '                                    addShopBrandEventListener(appendTo);'
                    + '                                    appendTo.children("div.catelem").css({display: "block"});'
                    + '                                },'
                    + '                                error: function () {'
                    + '                                },'
                    + '                                dataType: "html"'
                    + '                           });'
                    + '                        } else {'
                    + '                            tmpElem.parent().children("div.catname").children("div.catelem").css({display: "block"});'
                    + '                        }'
                    + '                     } else {'
                    + '                         $(this).removeClass("minus").addClass("plus");'
                    + '                         $(this).parent().children("div.catname").children("div.catelem").css({display: "none"});'
                    + '                     }'
                    + '                 });'
                    + '             });'
                    + '             $("div.catelem span.toggle.leaf", $(elem)).each(function () {'
                    + '                 $(this).click(function () {'
                    + '                     clickOttoCategory($(this).parent().children("div.catname").children("span.catname"));'
                    + '                 });'
                    + '                 $(this).parent().children("div.catname").children("span.catname").each(function () {'
                    + '                     $(this).click(function () {'
                    + '                         clickOttoCategory($(this));'
                    + '                     });'
                    + '                     if ($(this).parent().attr("id") == selectedGambioBrand) {'
                    + '                         $(this).addClass("selected").css({"font-weight": "bold"});'
                    + '                     }'
                    + '                 });'
                    + '             });'
                    + '         }'
                    + '        function addOttoBrandEventListener(elem) {'
                    + '            $("div.catelem span.toggle:not(.leaf)", $(elem)).each(function () {'
                    + '                $(this).click(function () {'
                    + '                    myConsole.log($(this).attr("id"));'
                    + '                    if ($(this).hasClass("plus")) {'
                    + '                        tmpElem = $(this);'
                    + '                        tmpElem.removeClass("plus").addClass("minus");'
                    + '                        if (tmpElem.parent().children("div.catname").children("div.catelem").length == 0) {'
                    + '                            jQuery.ajax({'
                    + '                                type: "POST",'
                    + '                                url: "magnalister.php?mp='+mpId+'&mode=prepare&view=apply&where=prepareView&kind=ajax&action=getOttoBrands",'
                    + '                                data: {'
                    + '                                    "action": "getOttoBrands",'
                    + '                                    "objID": tmpElem.attr("id"),'
                    + '                                    "isStoreCategory": isStoreCategory'
                    + '                                },'
                    + '                                success: function (data) {'
                    + '                                    appendTo = tmpElem.parent().children("div.catname");'
                    + '                                    appendTo.append(data);'
                    + '                                    addOttoBrandEventListener(appendTo);'
                    + '                                    appendTo.children("div.catelem").css({display: "block"});'
                    + '                                },'
                    + '                                error: function () {'
                    + '                                },'
                    + '                                dataType: "html"'
                    + '                            });'
                    + '                        } else {'
                    + '                            tmpElem.parent().children("div.catname").children("div.catelem").css({display: "block"});'
                    + '                        }'
                    + '                    } else {'
                    + '                        $(this).removeClass("minus").addClass("plus");'
                    + '                        $(this).parent().children("div.catname").children("div.catelem").css({display: "none"});'
                    + '                    }'
                    + '                });'
                    + '            });'
                    + '            $("div.catelem span.toggle.leaf", $(elem)).each(function () {'
                    + '                $(this).click(function () {'
                    + '                    clickOttoCategory($(this).parent().children("div.catname").children("span.catname"));'
                    + '                });'
                    + '                $(this).parent().children("div.catname").children("span.catname").each(function () {'
                    + '                    $(this).click(function () {'
                    + '                        clickOttoCategory($(this));'
                    + '                    });'
                    + '                    if ($(this).parent().attr("id") == selectedGambioBrand) {'
                    + '                        $(this).addClass("selected").css({"font-weight": "bold"});'
                    + '                    }'
                    + '                });'
                    + '           });'
                    + '        }'
                    + '        var shopBrandSelector = (function () {'
                    + '            return {'
                    + '                addShopBrandEventListener: addShopBrandEventListener,'
                    + '                addOttoBrandEventListener: addOttoBrandEventListener'
                    + '            }'
                    + '        })();'
                    + '        $(document).ready(function () {'
                    + '            shopBrandSelector.addShopBrandEventListener($("#gambioBrands"));'
                    + '            shopBrandSelector.addOttoBrandEventListener($("#ottoBrands"));'
                    + '        });'
                    + '     });'
                    + '</script>'

            return $(out);
        },

        _buildIndependentBrandMatchingTableSelectors: function(attribute, shopValues, mpValues, error, mpId, independent = false) {
            var independentCode = '';
            if (independent) {
                independentCode = '[CategoryIndependentShopVariation]';
            }

            var self = this,
                pID = $("input#pID").val(),
                baseName = 'ml[match]' + independentCode + '[' + attribute.AttributeCode + '][Values]',
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
                    + '            <td class="' + self._getAppropriateAttributeMatchingTableRowClass() + '" id="gambioBrands" style="width: 35%">'
                    + '                <select id="select2ShopBrandValues" name="' + baseName + '[0][Shop][Key]">'
                    +'                  </select>'
                    + '                <input type="hidden" name="' + baseName + '[0][Shop][Value]">'
                    + '            </td>'
                    + '            <td class="input" style="width: 35%" id="ottoBrands">'
                    + '                <select ' + style + ' name="' + baseName + '[0][Marketplace][Key]" id="select2MarketplceBrandValues">'
                    + '                </select>'
                    + '                 <input type="hidden" name="' + baseName + '[0][Marketplace][Value]">'
                    + '            </td>'
                    + '            <td id="freetext_' + self.generateIndependentAttributeCodeId(attribute.AttributeCode) + '" style="border: none; display: none;">'
                    + '                <input type="text" name="' + baseName + '[FreeText]" style="width:100%;">'
                    + '            </td>'
                    + '            <td style="border: none">'
                    + '                 <button type="button" class="ml-button mlbtn-action ml-save-independent-matching" value="' + attribute.AttributeCode + '">+'
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

            out += '<script type="text/javascript">'
                + '     $(document).ready(function() {'
                + '         $("#select2ShopBrandValues").select2('
                + '              {'
                + '                  ajax: {'
                + '                      type: "POST",'
                + '                      delay: 250,'
                + '                      url : "magnalister.php?mp='+mpId+'&mode=prepare&view=apply&where=prepareView&kind=ajax&action=getGambioBrands",'
                + '                      data: function (params) {'
                + '                          return {'
                + '                              "action": "getGambioBrands",'
                + '                              "pID": '+pID+','
                + '                              "brandfilterSearch": params.term,'
                + '                              "brandfilterPage": params.page || 1,'
                + '                          };'
                + '                      },'
                + '                      dataType: "json"'
                + '                  }'
                + '         });'
                + '         $("#select2MarketplceBrandValues").select2('
                + '                {'
                + '                    ajax: {'
                + '                        type: "POST",'
                + '                        delay: 250, '
                + '                        url : "magnalister.php?mp='+mpId+'&mode=prepare&view=apply&where=prepareView&kind=ajax&action=getOttoBrands",'
                + '                        data: function (params) {'
                + '                            return {'
                + '                                "action": "getOttoBrands",'
                + '                                "brandfilterSearch": params.term,'
                + '                                "brandfilterPage": params.page || 1,'
                + '                            };'
                + '                        },'
                + '                        dataType: "json"'
                + '                    }'
                + '                });'

                + '        var ottoBrands = "";'
                + '        var selectedGambioBrand = "";'
                + '        var madeChanges = false;'
                + '        var isStoreCategory = false;'

                + '        function addShopBrandEventListener(elem) {'
                + '            $("div.catelem span.toggle:not(.leaf)", $(elem)).each(function () {'
                + '                $(this).click(function () {'
                + '                    myConsole.log($(this).attr("id"));'
                + '                    if ($(this).hasClass("plus")) {'
                + '                        tmpElem = $(this);'
                + '                        tmpElem.removeClass("plus").addClass("minus");'

                + '                        if (tmpElem.parent().children("div.catname").children("div.catelem").length == 0) {'
                + '                            jQuery.ajax({'
                + '                                type: "POST",'
                + '                                url: "magnalister.php?mp='+mpId+'&mode=prepare&view=apply&where=prepareView&kind=ajax&action=getGambioBrands",'
                + '                                data: {'
                + '                                   "action": "getGambioBrands",'
                + '                                    "objID": tmpElem.attr("id"),'
                + '                                    "isStoreCategory": isStoreCategory'
                + '                                },'
                + '                                success: function (data) {'
                + '                                    appendTo = tmpElem.parent().children("div.catname");'
                + '                                    appendTo.append(data);'
                + '                                    addShopBrandEventListener(appendTo);'
                + '                                    appendTo.children("div.catelem").css({display: "block"});'
                + '                                },'
                + '                                error: function () {'
                + '                                },'
                + '                                dataType: "html"'
                + '                           });'
                + '                        } else {'
                + '                            tmpElem.parent().children("div.catname").children("div.catelem").css({display: "block"});'
                + '                        }'
                + '                     } else {'
                + '                         $(this).removeClass("minus").addClass("plus");'
                + '                         $(this).parent().children("div.catname").children("div.catelem").css({display: "none"});'
                + '                     }'
                + '                 });'
                + '             });'
                + '             $("div.catelem span.toggle.leaf", $(elem)).each(function () {'
                + '                 $(this).click(function () {'
                + '                     clickOttoCategory($(this).parent().children("div.catname").children("span.catname"));'
                + '                 });'
                + '                 $(this).parent().children("div.catname").children("span.catname").each(function () {'
                + '                     $(this).click(function () {'
                + '                         clickOttoCategory($(this));'
                + '                     });'
                + '                     if ($(this).parent().attr("id") == selectedGambioBrand) {'
                + '                         $(this).addClass("selected").css({"font-weight": "bold"});'
                + '                     }'
                + '                 });'
                + '             });'
                + '         }'

                + '        function addOttoBrandEventListener(elem) {'
                + '            $("div.catelem span.toggle:not(.leaf)", $(elem)).each(function () {'
                + '                $(this).click(function () {'
                + '                    myConsole.log($(this).attr("id"));'
                + '                    if ($(this).hasClass("plus")) {'
                + '                        tmpElem = $(this);'
                + '                        tmpElem.removeClass("plus").addClass("minus");'

                + '                        if (tmpElem.parent().children("div.catname").children("div.catelem").length == 0) {'
                + '                            jQuery.ajax({'
                + '                                type: "POST",'
                + '                                url: "magnalister.php?mp='+mpId+'&mode=prepare&view=apply&where=prepareView&kind=ajax&action=getOttoBrands",'
                + '                                data: {'
                + '                                    "action": "getOttoBrands",'
                + '                                    "objID": tmpElem.attr("id"),'
                + '                                    "isStoreCategory": isStoreCategory'
                + '                                },'
                + '                                success: function (data) {'
                + '                                    appendTo = tmpElem.parent().children("div.catname");'
                + '                                    appendTo.append(data);'
                + '                                    addOttoBrandEventListener(appendTo);'
                + '                                    appendTo.children("div.catelem").css({display: "block"});'
                + '                                },'
                + '                                error: function () {'
                + '                                },'
                + '                                dataType: "html"'
                + '                            });'
                + '                        } else {'
                + '                            tmpElem.parent().children("div.catname").children("div.catelem").css({display: "block"});'
                + '                        }'
                + '                    } else {'
                + '                        $(this).removeClass("minus").addClass("plus");'
                + '                        $(this).parent().children("div.catname").children("div.catelem").css({display: "none"});'
                + '                    }'
                + '                });'
                + '            });'
                + '            $("div.catelem span.toggle.leaf", $(elem)).each(function () {'
                + '                $(this).click(function () {'
                + '                    clickOttoCategory($(this).parent().children("div.catname").children("span.catname"));'
                + '                });'
                + '                $(this).parent().children("div.catname").children("span.catname").each(function () {'
                + '                    $(this).click(function () {'
                + '                        clickOttoCategory($(this));'
                + '                    });'
                + '                    if ($(this).parent().attr("id") == selectedGambioBrand) {'
                + '                        $(this).addClass("selected").css({"font-weight": "bold"});'
                + '                    }'
                + '                });'
                + '           });'
                + '        }'

                + '        var shopBrandSelector = (function () {'
                + '            return {'
                + '                addShopBrandEventListener: addShopBrandEventListener,'
                + '                addOttoBrandEventListener: addOttoBrandEventListener'
                + '            }'
                + '        })();'

                + '        $(document).ready(function () {'
                + '            shopBrandSelector.addShopBrandEventListener($("#gambioBrands"));'
                + '            shopBrandSelector.addOttoBrandEventListener($("#ottoBrands"));'
                + '        });'
                + '     });'
                + '</script>'

                +'        <style>'
                +'            .select2-container {'
                +'                        width: 100% !important;'
                +'                        margin-top: 0px !important;'
                +'                    }'
                +'                    .select2-selection {'
                +'                        height: 30px !important;'
                +'                    }'
                +'                    .select2-selection__arrow {'
                +'                        top: 3px !important;'
                +'                        right: 10px !important;'
                +'                    }'
                +'                    .mlbtn.action {'
                +'                        background-color: #E31A1C;'
                +'                        color: #ffffff !important;'
                +'                        text-shadow: none;'
                +'                       border-color: #E31A1C;'
                +'                        -moz-border-bottom-colors: none;'
                +'                        -moz-border-left-colors: none;'
                +'                        -moz-border-right-colors: none;'
                +'                        -moz-border-top-colors: none;'
                +'                       background-image: none;'
                +'                       background-repeat: repeat-x;'
                +'                        border-color: rgba(0, 0, 0, 0.25) rgba(0, 0, 0, 0.25) rgba(0, 0, 0, 0.35);'
                +'                        border-image: none;'
                +'                        border-style: solid;'
                +'                        border-width: 1px;'
                +'                        cursor: pointer;'
                +'                        display: inline-block;'
                +'                       font-size: 10px;'
                +'                        font-weight: bold;'
                +'                       font-family: Arial, sans-serif;'
                +'                        line-height: 17px;'
                +'                        margin-bottom: 0;'
                +'                        padding: 2px 10px;'
                +'                        text-align: center;'
                +'                        vertical-align: middle;'
                +'                        text-transform: uppercase;'
                +'                        text-decoration: none;'
                +'                    }'

                +'                    .brand-pagination {'
                +'                        text-align: right;'
                +'                        background: none repeat scroll 0 0 #F3F3F3;'
                +'                        border-bottom: 1px solid #AAAAAA;'
                +'                        border-top: 1px solid #AAAAAA;'
                +'                        padding: 2px 5px;'
                +'                        margin-bottom: 4px;'
                +'                        margin-top: 4px;'
                +'                    }'

                +'                    .brand-pagination a {'
                +'                        background: none;'
                +'                        border: none;'
                +'                        outline: none;'
                +'                        cursor: pointer;'
                +'                        color: gray; text-decoration: none; font-size: 13.33px; padding: 1px 6px;'
                +'                    }'

                +'            .brand-pagination a:hover {'
                +'                color: gray !important;'
                +'            }'

                +'            .brand-pagination a[disabled] {'
                +'                color: inherit;'
                +'                cursor: inherit;'
                +'            }'
                +'                    .mlCollapse {'
                +'                        border: none;'
                +'                        -webkit-appearance: button;'
                +'                        cursor: pointer;'
                +'                            text-transform: none;'
                +'                    }'

                +'                    .mlCollapse span {'
                +'                        background: url(includes/magnalister/images/collapse_arrow.png) no-repeat scroll 0 0px transparent;'
                +'                       height: 22px;'
                +'                        width: 25px;'
                +'                       margin-top: 5px;'
                +'                        float: right;'
                +'                        border: none;'
                +'                    }'
                +'                </style>';

            return $(out);
        },

        _buildIndependentMatchingTableSelectors: function(attribute, shopValues, mpValues, error) {
            var self = this,
                baseName = 'ml[match][CategoryIndependentShopVariation]' + '[' + attribute.AttributeCode + '][Values]',
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
                    + '            <td id="freetext_' + self.generateIndependentAttributeCodeId(attribute.AttributeCode) + '" style="border: none; display: none;">'
                    + '                <input type="text" name="' + baseName + '[FreeText]" style="width:100%;">'
                    + '            </td>'
                    + '            <td style="border: none">'
                    + '                 <button type="button" class="ml-button mlbtn-action ml-save-independent-matching" value="' + attribute.AttributeCode + '">+'
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

        _getIndependentTableColumns: function(code, selectedColumn, table) {
            var self = this;

            self._load({
                'Action': 'DBMatchingColumns',
                'Table': table
            }, function(data) {
                self._addIndependentOptionsToSelect(code, selectedColumn, data);
            });
        },

        _addIndependentOptionsToSelect: function(code, selectedColumn, data) {
            var self = this;
            $('option', 'select[name="ml[match][CategoryIndependentShopVariation]' + '[' + code + '][Values][Column]"]').not(':eq(0)').remove();

            $.each(data, function(key, value) {
                var selected = '';
                if (selectedColumn === value) {
                    selected = 'selected="selected"';
                }

                $('select[name="ml[match][CategoryIndependentShopVariation]' + '[' + code + '][Values][Column]"]')
                    .append($('<option ' + selected + '></option>')
                        .attr("value", key)
                        .text(value));
            })
        },

        _attachIndependentAttributeSelector: function(attributesSelectorOptions, addShopVariationSelectorChangeListener) {
            var self = this,
                currentlySelectedAttribute,
                attributesSelectorEl = $([
                    '<select name="optional_independent_selector" style="width: 100%">',
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
                                self._saveIndependentMatching(true, function() {
                                    $('#tbodyDynamicIndependentMatchingOptionalInput').find('select[name="optional_independent_selector"]').val(attributeIdToShow).change();
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

                $('#tbodyDynamicIndependentMatchingOptionalInput').find('#selRow_' + currentlySelectedAttribute).hide();
                currentlySelectedAttribute = attributeIdToShow;
                var attributeRowEl = $('#tbodyDynamicIndependentMatchingOptionalInput').find('#selRow_' + currentlySelectedAttribute),
                    selectId = '#sel_' + currentlySelectedAttribute;

                //Minus 1 goes for "Bitte wahlen"
                if (attributesSelectorOptions.length - 1 > self.optionalAttributesMaxSize) {
                    $('#tbodyDynamicIndependentMatchingOptionalInput').find('.optionalAttribute').hide();
                }

                attributeRowEl.children('th').html('').append(attributesSelectorEl);
                attributeRowEl.remove().show().insertBefore($('#tbodyDynamicIndependentMatchingOptionalInput').find('.spacer').last());
                attributeRowEl.find(selectId).each(addShopVariationSelectorChangeListener).change();
                // self._prefix_option(selectId);
                attributesSelectorEl.change(attributeSelectorOnChange);

                $('select[name="optional_selector"]').each(function (index, link) {
                    $(this).select2({});
                });

                $('select[name="optional_independent_selector"]').each(function (index, link) {
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
                    // var selectedOption = $(this).find(':selected'),
                    //     selectedOptionText = selectedOption.text(),
                    //     optGroup = selectedOption.closest('optgroup').attr('label');

                    // $(this).find(':selected').text(optGroup ? optGroup + ': ' + selectedOptionText : selectedOptionText);
                }
                disabledSelect = true;
            }

            function removePrefix() {
                disabledSelect = false;
                // $(this).find('option').each(function() {
                //     var optionText = $(this).text().split(':');
                //     $(this).text(optionText[1]);
                // });
            }

            function doPrefixing() {
                removePrefix.call(this);
                addPrefix.call(this);
            }
        },

        _buildShopIndependentVariationSelector: function(attributes, attributesName) {
            var self = this,
                data = attributes[attributesName],
                allowedValuesNotEmpty = (data.AllowedValues.length > 0 || Object.keys(data.AllowedValues).length > 0),
                kind = allowedValuesNotEmpty ? 'Matching' : 'FreeText',
                baseName = 'ml[match][CategoryIndependentShopVariation]'  + '[' + data.AttributeCode + ']',
                mpDataType = data.DataType.toLowerCase();

            data.AttributeCode = data.AttributeCode + '';
            data.id = self.generateIndependentAttributeCodeId(data.AttributeCode); // css selector-save.
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
                .append('<div id="extraFieldsInfo_' +  self.generateIndependentAttributeCodeId(data.AttributeCode) + '" style="display: inline;"></div>')
                .append('<input type="hidden" name="' + baseName + '[Kind]" value="' + kind + '">')
                .append('<input type="hidden" name="' + baseName + '[Required]" value="' + (data.Required ? 1 : 0) + '">')
                .append('<input type="hidden" name="' + baseName + '[AttributeName]" value="' + data.AttributeName + '">')
                .html()
            ;

            var customAttributeName = $('<input>').attr({
                type: 'text',
                id: 'input_' + self.generateIndependentAttributeCodeId(data.AttributeCode) + '_custom_name',
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
                // added check because we removed custom category attributes from OTTO
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
                // added check because we removed custom category attributes from OTTO
                if (selectElement !== null) {
                    selectElement.addEventListener('mousedown', function () {
                        if (this.options.length === 1) {
                            self._addShopOptions(self, this, data, attributes, mpDataType);
                        }
                    });
                }
            }, 0);

            return data;
        },

        generateIndependentAttributeCodeId: function(AttributeCode) {
            var self = this;
            return ('' + '[CategoryIndependentShopVariation]' + AttributeCode).replace(/[^A-Za-z0-9_]/g, '_');
        }
    });

    $(ml_vm_config.formName).otto_variation_matching({
        urlPostfix: '&kind=ajax&where=' + ml_vm_config.viewName,
        i18n: ml_vm_config.i18n,
        elements: {
            newGroupIdentifier: '#newGroupIdentifier',
            customVariationHeaderContainer: '#tbodyVariationConfigurationSelector',
            newCustomGroupContainer: '#newCustomGroup',
            mainSelectElement: '#PrimaryCategory',
            matchingHeadline: '#tbodyDynamicMatchingHeadline',
            matchingOptionalHeadline: '#tbodyDynamicMatchingOptionalHeadline',
            matchingCustomHeadline: '',
            matchingInput: '#tbodyDynamicMatchingInput',
            matchingCustomInput: '',
            matchingOptionalInput: '#tbodyDynamicMatchingOptionalInput',
            categoryInfo: '#categoryInfo',
            matchingIndependentHeadline: '#tbodyDynamicIndependentMatchingHeadline',
            matchingIndependentInput: '#tbodyDynamicIndependentMatchingInput',
            matchingIndependentOptionalHeadline: '#tbodyDynamicIndependentMatchingOptionalHeadline',
            matchingIndependentOptionalInput: '#tbodyDynamicIndependentMatchingOptionalInput'
        },
        shopVariations: ml_vm_config.shopVariations
    });
});
