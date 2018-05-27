$(document).ready(function() {
    var config = {
        urlPostfix: '&kind=ajax&where=' + ml_vm_config.viewName,
        i18n: ml_vm_config.i18n,
        elements: {
            newGroupIdentifier: '#newGroupIdentifier',
            customVariationHeaderContainer: '#tbodyVariationConfigurationSelector',
            newCustomGroupContainer: '#newCustomGroup',
            mainSelectElement: '#PrimaryCategory',
            customIdentifierSelectElement: '#CustomIdentifier',
            matchingHeadline: '#tbodyDynamicMatchingHeadline',
            matchingOptionalHeadline: '#tbodyDynamicMatchingOptionalHeadline',
            matchingCustomHeadline: '#tbodyDynamicMatchingCustomHeadline',
            matchingInput: '#tbodyDynamicMatchingInput',
            matchingOptionalInput: '#tbodyDynamicMatchingOptionalInput',
            matchingCustomInput: '#tbodyDynamicMatchingCustomInput',
            categoryInfo: '#categoryInfo',
            customIdentifierWrapper: '#mpCustomIdentifierSelector'
        },
        shopVariations: ml_vm_config.shopVariations
    };

    if (ml_vm_config.formName) {
        $(ml_vm_config.formName).ml_variation_matching(config);
    } else {
        $.widget("ui.prepare_variation_matching", $.ui.ml_variation_matching, {
            _init: function() {
                this._super();
                //myConsole.log('new init');
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
                customIdentifierSelectElement: '#subcat',
                matchingHeadline: '#tbodyDynamicMatchingHeadline',
                matchingOptionalHeadline: '#tbodyDynamicMatchingOptionalHeadline',
                matchingCustomHeadline: '#tbodyDynamicMatchingCustomHeadline',
                matchingInput: '#tbodyDynamicMatchingInput',
                matchingOptionalInput: '#tbodyDynamicMatchingOptionalInput',
                matchingCustomInput: '#tbodyDynamicMatchingCustomInput',
                categoryInfo: '#categoryInfo',
                customIdentifierWrapper: '#subCategory'
            },
            shopVariations: ml_vm_config.shopVariations
        });
    }
});
