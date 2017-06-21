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
                isCategoryEmpty = true,
                i,
                attributes = data.Attributes;

            self.elements.matchingInput.html('');

            for(i in attributes) {
                if(attributes.hasOwnProperty(i)) {
                    isCategoryEmpty = false;
                    if(attributes[i].Deleted) {
                        self.elements.matchingInput.append($(self._render(deletedAttrTemplate, [attributes[i]])))
                    } else {
                        attributes[i] = self._buildShopVariationSelector(attributes[i]);
                        self.elements.matchingInput.append($(self._render(colTemplate, [attributes[i]])));

                        // add warning box if attribute changed on Marketplace
                        if(attributes[i].ChangeDate && data.ModificationDate
                            && new Date(data.ModificationDate) < new Date(attributes[i].ChangeDate)
                        ) {
                            $('div#extraFieldsInfo_' + attributes[i].id)
                                .append('<span id="' + attributes[i].id + '_warningMatching" class="ml-warning" title="' + self.i18n.attributeChangedOnMp + '">&nbsp;<span>');
                        }

                        // add warning box if attribute is different from one matched in Variation matching tab
                        if(attributes[i].Modified) {
                            $('div#extraFieldsInfo_' + attributes[i].id)
                                .append('<span id="' + attributes[i].id + '_warningMatching" class="ml-warning" title="' + self.i18n.attributeDifferentOnProduct + '">&nbsp;<span>');
                        }
                    }
                }
            }

            self.elements.mainSelectElement.closest('.magnamain').find('.jsNoticeBox').remove();
            if(data.DifferentProducts) {
                var categoryName = self.elements.mainSelectElement.find('option:selected').html();
                self.elements.mainSelectElement.closest('.magnamain')
                    .prepend('<p class="noticeBox jsNoticeBox">'
                        + self.i18n.differentAttributesOnProducts.replace('%category_name%', categoryName)
                        + '</p>');
            }

            if(resetNotice) {
                self.elements.mainSelectElement.closest('.magnamain').find('.notAllAttributeValuesMatched').remove();
            }

            if(data.notice && data.notice.length) {
                for(i = 0; i < data.notice.length; i++) {
                    if(data.notice.hasOwnProperty(i)) {
                        self.elements.mainSelectElement.closest('.magnamain')
                            .prepend('<p class="noticeBox notAllAttributeValuesMatched">'
                                + data.notice[i]
                                + '</p>');
                    }
                }
            }

            data.Attributes = attributes;

            if(isCategoryEmpty) {
                self.elements.matchingInput.append('<tr><th></th><td class="input">'
                    + self.i18n.categoryWithoutAttributesInfo
                    + '</td><td class="info"></td></tr>');
            }

            self.elements.matchingInput.append('<tr class="spacer"><td colspan="3">&nbsp;</td></tr>');
            self.elements.matchingInput.find('select[id^=sel_]').each(function() {
                var previous;
                $(this).on('focus', function() {
                    previous = $(this).val();
                }).change(function() {
                    self._handleAttributeSelectorChange(this, data, previous, savePrepare);
                });
            });

            for(i in attributes) {
                if(attributes.hasOwnProperty(i)) {
                    if(typeof attributes[i].CurrentValues.Code !== 'undefined') {
                        self.elements.matchingInput.find('select[id=sel_' + attributes[i].id + ']').val(attributes[i].CurrentValues.Code).trigger('change');
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
            matchingInput: '#tbodyDynamicMatchingInput',
            matchingCustomInput: '#tbodyDynamicMatchingCustomInput',
            categoryInfo: '#categoryInfo'
        },
        shopVariations: ml_vm_config.shopVariations
    });
});
