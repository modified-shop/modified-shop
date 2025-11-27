$(document).ready(function() {
    var config = {
        urlPostfix: '&kind=ajax&where=' + ml_vm_config.viewName,
        i18n: ml_vm_config.i18n,
        elements: {
            newGroupIdentifier: '#newGroupIdentifier',
            customVariationHeaderContainer: '#tbodyVariationConfigurationSelector',
            newCustomGroupContainer: '#newCustomGroup',
            mainSelectElement: '#PrimaryCategory',
            matchingHeadline: '#tbodyDynamicMatchingHeadline',
            matchingOptionalHeadline: '#tbodyDynamicMatchingOptionalHeadline',
            matchingCustomHeadline: '#tbodyDynamicMatchingCustomHeadline',
            matchingInput: '#tbodyDynamicMatchingInput',
            matchingOptionalInput: '#tbodyDynamicMatchingOptionalInput',
            matchingCustomInput: '#tbodyDynamicMatchingCustomInput',
            categoryInfo: '#categoryInfo',
        },
        shopVariations: ml_vm_config.shopVariations
    };
    // Single select options
    var singleSelectOptions = {
        width: 'resolve',
        placeholder: config.i18n.pleaseSelect,
    };
    $(config.elements.mainSelectElement).select2(singleSelectOptions);

    if (ml_vm_config.formName) {
        $(ml_vm_config.formName).ml_variation_matching(config);
    } else {
        $.widget("ui.prepare_variation_matching", $.ui.ml_variation_matching, {
            _init: function() {
                this._super();
                //myConsole.log('new init');
            },
            __addSelect2OnCategory: function() {
                var self = this,
                // Single select options
                singleSelectOptions = {
                    width: 'resolve',
                    placeholder: self.i18n.pleaseSelect,
                };
                self.elements.mainSelectElement.select2(singleSelectOptions);
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
                var selectedOption = $('#variation-theme').find('option:selected'),
                variationThemeAttributes = {};
                if (selectedOption.length || selectedOption.val() !== '') {
                    variationThemeAttributes = selectedOption.data('attributes');
                }

                for (var key in attributes) {
                    if (variationThemeAttributes!= null && variationThemeAttributes.indexOf(key) !== -1) {
                        attributes[key].Required = true;
                    }

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
                    variationPatternElement = $('#variation-pattern'),
                    shouldLoadVariationPattern = false;

                if (self.elements.customIdentifierSelectElement) {
                    variationPatternElement.remove();
                    variationPatternElement = [];
                }

                if (variationDetails && self.isPrepareForm && !variationPatternElement.length) {
                    var allVariationOptions = [{key : 'null', value: self.i18n.pleaseSelect}],
                        lastElementInVariationMatcher = self.elements.mainSelectElement.closest('tr'),
                        oddOrEven = lastElementInVariationMatcher.attr('class') === 'odd' ? 'even' : 'odd';

                    for (var property in variationDetails) {
                        if (variationDetails.hasOwnProperty(property)) {
                            var attributesData = variationDetails[property]['attributes'];
                            variationOption = {
                                key : property,
                                value : variationDetails[property]['name'],
                                data : typeof attributesData === 'object' ? JSON.stringify(attributesData) : attributesData

                            };

                            if (selectedVariationTheme === property) {
                                variationOption.selected = 'selected';
                                shouldLoadVariationPattern = true;
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
                        '<select id="variation-theme" name="variationTheme" onchange="" ' + 'style="width:100%;">',
                        self._render('<option data-attributes=\'{data}\' {selected} value="{key}">{value}</option>', allVariationOptions),
                        '</select>',
                        '</td>',
                        '<td class="info">',
                        '</td>',
                        '</tr>'
                    ].join('')).insertAfter(lastElementInVariationMatcher);
                    // Last element is row which represents spacer, so before that element, variation details element should
                    // be inserted.
                    $('#variation-themes').val(JSON.stringify(variationDetails));
                    var singleSelectOptions = {
                        width: 'resolve',
                    };
                    $('#variation-theme').select2(singleSelectOptions);
                    $('#variation-theme').change(function () {
                        var val = self.elements.mainSelectElement.val(),
                        customIdentifierVal = '';
                        $('button.ml-save-matching').trigger('click');
                        self._loadMPVariation(val, customIdentifierVal, false);
                    });
                    if (shouldLoadVariationPattern) {
                        var val = self.elements.mainSelectElement.val(),
                            customIdentifierVal = '';
                        self._loadMPVariation(val, customIdentifierVal, false);
                    }

                }
            }
        });

        $('form[name=apply]').prepare_variation_matching({
            urlPostfix: '&kind=ajax&where=' + ml_vm_config.viewName,
            i18n: ml_vm_config.i18n,
            elements: {
                newGroupIdentifier: '#newGroupIdentifier',
                customVariationHeaderContainer: '#tbodyVariationConfigurationSelector',
                newCustomGroupContainer: '#newCustomGroup',
                mainSelectElement: '#maincat',
                matchingHeadline: '#tbodyDynamicMatchingHeadline',
                matchingOptionalHeadline: '#tbodyDynamicMatchingOptionalHeadline',
                matchingCustomHeadline: '#tbodyDynamicMatchingCustomHeadline',
                matchingInput: '#tbodyDynamicMatchingInput',
                matchingOptionalInput: '#tbodyDynamicMatchingOptionalInput',
                matchingCustomInput: '#tbodyDynamicMatchingCustomInput',
                categoryInfo: '#categoryInfo',
            },
            shopVariations: ml_vm_config.shopVariations
        });
    }
});
