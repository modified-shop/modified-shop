$(document).ready(function() {
    /**
     * example for overriding default variation matching js behavior
     *
     * $.widget("ui.priceminister_variation_matching", $.ui.ml_variation_matching, {
     *     _init: function() {
     *         this._super();
     *         myConsole.log('new init');
     *     }
     * });
     *
     * After this, starting widget should be done like this:
     * $(ml_vm_config.formName).priceminister_variation_matching({...});
     */

    $.widget('ui.priceminister_variation_matching', $.ui.ml_variation_matching, {
        _init: function() {
            this._super();
        },

        _buildShopVariationSelectors: function(data, resetNotice, savePrepare) {
            var self = this,
                colTemplate = self._getMatchingAttributeColumnTemplate(),
                deletedAttrTemplate = self._getDeletedAttributeColumnTemplate(),
                attributeColumnEl = null,
                attributesSelectorOptions = [{key: 'dont_use', value: self.i18n.pleaseSelect}],
                isCategoryEmpty = true,
                i, matchingInputEl,
                attributes = data.Attributes;

            self.elements.matchingInput.html('');
            self.elements.matchingOptionalInput.html('');

            var attributesSize = 0, key;
            for (key in attributes) {
                if (attributes.hasOwnProperty(key) && !attributes[key].Required) {
                    attributesSize++;
                }
            }

            for (i in attributes) {
                if (attributes.hasOwnProperty(i)) {
                    isCategoryEmpty = false;
                    if (attributes[i].Deleted) {
                        self.elements.matchingInput.append($(self._render(deletedAttrTemplate, [attributes[i]])))
                    } else {
                        attributes[i] = self._buildShopVariationSelector(attributes[i]);

                        matchingInputEl = self.elements.matchingInput;
                        attributeColumnEl = $(self._render(colTemplate, [attributes[i]]));

                        if (!attributes[i].Required) {
                            matchingInputEl = self.elements.matchingOptionalInput;

                            if (!attributes[i].CurrentValues.Code) {
                                if (attributesSize > self.optionalAttributesMaxSize) {
                                    attributeColumnEl.hide();
                                }

                                attributeColumnEl.addClass('optionalAttribute');
                                attributesSelectorOptions.push({key: attributes[i].id, value: attributes[i].AttributeName});
                            }
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
                    }
                }
            }

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
            }

            data.Attributes = attributes;

            if (isCategoryEmpty) {
                self.elements.matchingInput.append('<tr><th></th><td class="input">'
                    + self.i18n.categoryWithoutAttributesInfo
                    + '</td><td class="info"></td></tr>');
                self.elements.matchingOptionalHeadline.css('display', 'none');
                self.elements.matchingOptionalInput.css('display', 'none');
            }

            if (!$.trim(self.elements.matchingInput.html())) {
                self.elements.matchingHeadline.css('display', 'none');
                self.elements.matchingInput.css('display', 'none');
            }

            if (!$.trim(self.elements.matchingOptionalInput.html())) {
                self.elements.matchingOptionalHeadline.css('display', 'none');
                self.elements.matchingOptionalInput.css('display', 'none');
            } else if (attributesSize > self.optionalAttributesMaxSize) {
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

            self.elements.matchingInput.append('<tr class="spacer"><td colspan="3">&nbsp;</td></tr>');
            self.elements.matchingOptionalInput.append('<tr class="spacer"><td colspan="3">&nbsp;</td></tr>');

            function addShopVariationSelectorChangeListener() {
                var previous;
                $(this).on('focus', function () {
                    previous = $(this).val();
                }).change(function () {
                    self._handleAttributeSelectorChange(this, data, previous, savePrepare);
                });
            }

            self.elements.matchingInput.find('select[id^=sel_]').each(addShopVariationSelectorChangeListener);
            self.elements.matchingOptionalInput.find('select[id^=sel_]').each(addShopVariationSelectorChangeListener);

            for (i in attributes) {
                if (attributes.hasOwnProperty(i)) {
                    if (typeof attributes[i].CurrentValues.Code !== 'undefined') {
                        self.elements.matchingInput.find('select[id=sel_' + attributes[i].id + ']').val(attributes[i].CurrentValues.Code).trigger('change');
                        self.elements.matchingOptionalInput.find('select[id=sel_' + attributes[i].id + ']').val(attributes[i].CurrentValues.Code).trigger('change');
                    }
                }
            }

            if(data.Subcategories && data.Subcategories.length > 0) {
                $('#tbodySubcategoriesHeadline').css('display', 'table-row-group');
                var subcategories = $('#tbodySubcategoriesInput');
                subcategories.css('display', 'table-row-group');
                subcategories.html('');

                for(i in data.Subcategories) {
                    var subcategory = data.Subcategories[i];
                    var template = self._getSubcategoryTemplate();
                    template = template.replace(new RegExp('\{id\}', 'g'), subcategory['AttributeCode']);
                    template = template.replace(new RegExp('\{AttributeName\}', 'g'), subcategory['AttributeName']);
                    template = template.replace(new RegExp('\{AttributeDescription\}', 'g'), subcategory['AttributeDescription']);
                    template = template.replace(new RegExp('\{redDot\}', 'g'), subcategory['Required'] ? '<span class="bull">&bull;</span>' : '');
                    template = template.replace(new RegExp('\{required\}', 'g'), subcategory['Required'] ? '1' : '0');

                    var error = subcategory.CurrentValues && subcategory.CurrentValues.Error;
                    template = template.replace(new RegExp('\{labelStyle\}', 'g'), error ? ' style="color:red" ' : '');
                    template = template.replace(new RegExp('\{selectStyle\}', 'g'), error ? ' style="border-color:red" ' : '');


                    var options = '<option value>'+self.i18n.pleaseSelect+'</option>';
                    for(j in subcategory.AllowedValues){
                        var selected = subcategory.CurrentValues && subcategory.CurrentValues.Values == j? 'selected ': '';
                        options += '<option value="' + j + '" ' + selected +' >' + subcategory.AllowedValues[j] +' </option>';
                    }

                    template = template.replace(new RegExp('\{options\}', 'g'), options);
                    subcategories.append(template);
                }

                subcategories.append('<tr class="spacer"><td colspan="3">&nbsp;</td></tr>');
            } else {
                $('#tbodySubcategoriesHeadline').css('display', 'none');
                $('#tbodySubcategoriesInput').html('');
            }

            self._attachAttributeSelector(attributesSelectorOptions, addShopVariationSelectorChangeListener);
        },

        _getSubcategoryTemplate: function() {
            return '<tr id="selRow_{id}">'
                + '         <th {labelStyle}>{AttributeName} {redDot}</th>'
                + '         <td id="selCell_{id}">'
                + '             <div id="match_{id}">'
                + '                 <input type="hidden" name="ml[match][ShopVariation][{id}][Kind]" value="Matching">'
                + '                 <input type="hidden" name="ml[match][ShopVariation][{id}][Required]" value="{required}">'
                + '                 <input type="hidden" name="ml[match][ShopVariation][{id}][AttributeName]" value="{AttributeName}">'
                + '                 <input type="hidden" name="ml[match][ShopVariation][{id}][Code]" value="attribute_value">'
                + '                 <select name="ml[match][ShopVariation][{id}][Values]" {selectStyle}>'
                + '                     {options}'
                + '                 </select> '
                + '</div>'
                + '         </td>'
                + '         <td class="info">{AttributeDescription}</td>'
                + '	</tr>';
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
                                    self.elements.matchingOptionalInput.find('select[name="optional_selector"]').val(attributeIdToShow).change();//trigger('change', [attributeIdToShow]);
                                });

                                $(this).dialog('close');
                            }
                        }
                    }
                });
            }

            function changeCurrentAttribute(attributeIdToShow) {
                // Minus 1 goes for "Bitte wahlen"
                if (attributesSelectorOptions.length - 1 > self.optionalAttributesMaxSize) {
                    self.elements.matchingOptionalInput.find('.optionalAttribute').hide();
                }

                currentlySelectedAttribute = attributeIdToShow;

                var attributeRowEl = self.elements.matchingOptionalInput.find('#selRow_' + currentlySelectedAttribute);

                attributeRowEl.children('th').html('').append(attributesSelectorEl);
                attributeRowEl.remove().show().insertBefore(self.elements.matchingOptionalInput.find('.spacer').last());
                attributeRowEl.find('#sel_' + currentlySelectedAttribute).each(addShopVariationSelectorChangeListener).change();

                attributesSelectorEl.change(attributeSelectorOnChange);
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
        }
    });


    $(ml_vm_config.formName).priceminister_variation_matching({
        urlPostfix: '&kind=ajax&where=' + ml_vm_config.viewName,
        i18n: ml_vm_config.i18n,
        elements: {
            newGroupIdentifier: '#newGroupIdentifier',
            customVariationHeaderContainer: '#tbodyVariationConfigurationSelector',
            newCustomGroupContainer: '#newCustomGroup',
            mainSelectElement: '#PrimaryCategory',
            matchingHeadline: '#tbodyDynamicMatchingHeadline',
            matchingCustomHeadline: '#tbodyDynamicMatchingCustomHeadline',
            matchingOptionalHeadline: '#tbodyDynamicMatchingOptionalHeadline',
            matchingInput: '#tbodyDynamicMatchingInput',
            matchingCustomInput: '#tbodyDynamicMatchingCustomInput',
            matchingOptionalInput: '#tbodyDynamicMatchingOptionalInput',
            categoryInfo: '#categoryInfo'
        },
        shopVariations: ml_vm_config.shopVariations
    });
});
