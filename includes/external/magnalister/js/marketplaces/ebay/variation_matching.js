$(document).ready(function() {
    /**
     * example for overriding default variation matching js behavior
     *
     * $.widget("ui.hitmeister_variation_matching", $.ui.ml_variation_matching, {
     *     _init: function() {
     *         this._super();
     *         myConsole.log('new init');
     *     }
     * });
     *
     * After this, starting widget should be done like this:
     * $(ml_vm_config.formName).hitmeister_variation_matching({...});
     */
    $.widget("ui.ebay_variation_matching", $.ui.ml_variation_matching, {
        _init: function() {
            var self = this;

            self.elements.secondarySelectElement = null;

            if (self.options.elements.secondarySelectElement) {
                if (typeof self.options.elements.secondarySelectElement === 'string') {
                    self.elements.secondarySelectElement = self.element.find(self.options.elements.secondarySelectElement);
                } else {
                    self.elements.secondarySelectElement = self.options.elements.secondarySelectElement;
                }

                self.elements.secondarySelectElement.change(function() {
                    self.elements.mainSelectElement.trigger('change');
                });
            }

            self._super();
        },
        _loadMPVariation: function(val, initial) {
            var self = this,
                secondarySelectValue,
                requestParams = {
                    'Action': 'LoadMPVariations',
                    'SelectValue': val
                };

            self._resetMPVariation();
            if (val === 'null') {
                self.elements.matchingInput.html(self.html.valuesBackup);
                self.elements.matchingOptionalInput.html(self.html.valuesOptionalBackup);
                self.elements.matchingCustomInput.html(self.html.valuesCustomBackup);
                return;
            }

            if (self.elements.secondarySelectElement) {
                secondarySelectValue = self.elements.secondarySelectElement.val();

                if (secondarySelectValue != null && secondarySelectValue !== '' && secondarySelectValue != 'null') {
                    requestParams['SecondarySelectValue'] = secondarySelectValue;
                }
            }

            self._load(requestParams, function(data) {
                self._buildShopVariationSelectors(data, !initial, true);
            });
        },
        _buildShopVariationSelector: function(attributes, attributesName) {
            var self = this,
                baseName = 'ml[match]' + self.attributesNamePrefix + '[' + attributes[attributesName].AttributeCode + ']',
                data;

            data = self._super(attributes, attributesName);

            data.shopVariationsDropDown += '<input type="hidden" name="' + baseName + '[CategoryId]" value="' + data.CategoryId + '">';
            data.shopVariationsCustomDropDown += '<input type="hidden" name="' + baseName + '[CategoryId]" value="' + data.CategoryId + '">';

            return data;
        }
    });

    if (ml_vm_config && !ml_vm_config.secondaryCategory) {
        $(ml_vm_config.formName).ebay_variation_matching({
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
                categoryInfo: '#categoryInfo'
            },
            shopVariations: ml_vm_config.shopVariations
        });
    } else {
        $(ml_vm_config.formName).ebay_variation_matching({
            urlPostfix: '&kind=ajax&where=' + ml_vm_config.viewName,
            i18n: ml_vm_config.i18n,
            elements: {
                newGroupIdentifier: '#newGroupIdentifier',
                customVariationHeaderContainer: '#tbodyVariationConfigurationSelector',
                newCustomGroupContainer: '#newCustomGroup',
                mainSelectElement: '#PrimaryCategory',
                secondarySelectElement: '#SecondaryCategory',
                matchingHeadline: '#tbodyDynamicMatchingHeadline',
                matchingOptionalHeadline: '#tbodyDynamicMatchingOptionalHeadline',
                matchingCustomHeadline: '#tbodyDynamicMatchingCustomHeadline',
                matchingInput: '#tbodyDynamicMatchingInput',
                matchingOptionalInput: '#tbodyDynamicMatchingOptionalInput',
                matchingCustomInput: '#tbodyDynamicMatchingCustomInput',
                categoryInfo: '#categoryInfo'
            },
            shopVariations: ml_vm_config.shopVariations
        });

        $('#selectSecondaryCategory').click(function() {
            var categoryEl = $('#SecondaryCategory');
            mpCategorySelector.startCategorySelector(function(cID) {
                categoryEl.find('option').attr('selected', '');
                if (categoryEl.find('[value='+cID+']').length > 0) {
                    categoryEl.find('[value='+cID+']').attr('selected','selected');
                    categoryEl.trigger('change');
                } else {
                    generateEbayCategoryPath(cID, $('#SecondaryCategoryVisual'));
                }
            }, 'mp');
        });

    }

    $('#selectPrimaryCategory').click(function() {
        var categoryEl = $('#PrimaryCategory');
        mpCategorySelector.startCategorySelector(function(cID) {
            categoryEl.find('option').attr('selected', '');
            if (categoryEl.find('[value='+cID+']').length > 0) {
                categoryEl.find('[value='+cID+']').attr('selected','selected');
                categoryEl.trigger('change');
            } else {
                generateEbayCategoryPath(cID, $('#PrimaryCategoryVisual'));
            }
        }, 'mp');
    });
});
