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
            matchingCustomHeadline: '#tbodyDynamicMatchingCustomHeadline',
            matchingInput: '#tbodyDynamicMatchingInput',
            matchingCustomInput: '#tbodyDynamicMatchingCustomInput',
            categoryInfo: '#categoryInfo'
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
                matchingHeadline: '#tbodyDynamicMatchingHeadline',
                matchingInput: '#tbodyDynamicMatchingInput',
                categoryInfo: '#categoryInfo'
            },
            shopVariations: ml_vm_config.shopVariations
        });
    }
});
