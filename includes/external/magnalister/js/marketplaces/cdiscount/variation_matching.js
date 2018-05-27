$(document).ready(function() {

    $.widget("ui.cdiscount_variation_matching", $.ui.ml_variation_matching, {
        _getVariationThemeHeader: function (self) {
            return self.i18n.variations;
        }
    });

    $(ml_vm_config.formName).cdiscount_variation_matching({
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
            matchingOptionalHeadline: '#tbodyDynamicMatchingOptionalHeadline',
            matchingInput: '#tbodyDynamicMatchingInput',
            matchingOptionalInput: '#tbodyDynamicMatchingOptionalInput',
            matchingCustomInput: '#tbodyDynamicMatchingCustomInput',
            matchingOptionalInput: '#tbodyDynamicMatchingOptionalInput',
            categoryInfo: '#categoryInfo'
        },
        shopVariations: ml_vm_config.shopVariations
    });
});
