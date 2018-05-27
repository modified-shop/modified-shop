$(document).ready(function() {
    /**
     * example for overriding default variation matching js behavior
     *
     * $.widget("ui.crowdfox_variation_matching", $.ui.ml_variation_matching, {
     *     _init: function() {
     *         this._super();
     *         myConsole.log('new init');
     *     }
     * });
     *
     * After this, starting widget should be done like this:
     * $(ml_vm_config.formName).crowdfox_variation_matching({...});
     */

    $.widget('ui.crowdfox_variation_matching', $.ui.ml_variation_matching, {
        _init: function() {
            this._super();
        },

        _initMainSelectElement: function() {
            var self = this;

            self.elements.mainSelectElement.change(function(event, initial) {
                self.elements.matchingHeadline.css('display', 'none');
                self.elements.matchingCustomHeadline.css('display', 'none');
                self.elements.matchingInput.html(self.html.valuesBackup).css('display', 'none');
                self.elements.matchingCustomInput.html(self.html.valuesCustomBackup).css('display', 'none');
                self.elements.categoryInfo.css('display', 'none');

                var val = $(this).val();
                self.elements.mainSelectElement.closest('.magnamain').find('.jsNoticeBox').remove();
                if (val != null && val !== '' && val != 'null') {
                    self.elements.mainSelectElement.closest('.magnamain').find('.successBox').remove();
                    self._loadMPVariation(val, '', initial);
                    self.elements.matchingCustomHeadline.css('display', 'table-row-group');
                    self.elements.matchingCustomInput.css('display', 'table-row-group');
                    self.elements.categoryInfo.css('display', 'table-row-group');
                }
            }).trigger('change', [true]);
        }
    });

    $(ml_vm_config.formName).crowdfox_variation_matching({
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
});
