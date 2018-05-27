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
            categoryInfo: '#categoryInfo'
        },
        shopVariations: ml_vm_config.shopVariations
    };

    if (ml_vm_config.formName === '#prepareForm') {
        $.widget("ui.prepare_variation_matching", $.ui.ml_variation_matching, {
            _init: function() {
                this._super();
                //myConsole.log('new init');
            },

            _setVariationThemeField: function(variationDetails, self, data) {
                var variationPatternElement = $('#variation-pattern');

                if (variationDetails && self.isPrepareForm && !variationPatternElement.length) {
                    var lastElementInVariationMatcher = self.elements.mainTable.children().last(),
                        oddOrEven = lastElementInVariationMatcher.prev('tr').attr('class') === 'odd' ? 'even' : 'odd';

                    $(['<tr class="' + oddOrEven + '" id="variation-pattern">',
                        '<input type="hidden" name="variationThemes" id="variation-themes">',
                        '</tr>'
                    ].join('')).insertBefore(lastElementInVariationMatcher);
                    // Last element is row which represents spacer, so before that element, variation details element should
                    // be inserted.

                    $('#variation-themes').val(JSON.stringify(variationDetails));
                }
            }
        });

        $('form[name=prepareForm]').prepare_variation_matching({
            urlPostfix: '&kind=ajax&where=' + ml_vm_config.viewName,
            i18n: ml_vm_config.i18n,
            elements: {
                newGroupIdentifier: '#newGroupIdentifier',
                customVariationHeaderContainer: '#tbodyVariationConfigurationSelector',
                newCustomGroupContainer: '#newCustomGroup',
                mainSelectElement: '#VariationConfiguration',
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
        $(ml_vm_config.formName).ml_variation_matching(config);
    }
});