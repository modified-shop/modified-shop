$(document).ready(function() {
    /**
     * example for overriding default variation matching js behavior
     *
     * $.widget("ui.tradoria_variation_matching", $.ui.ml_variation_matching, {
     *     _init: function() {
     *         this._super();
     *         myConsole.log('new init');
     *     }
     * });
     *
     * After this, starting widget should be done like this:
     * $(ml_vm_config.formName).tradoria_variation_matching({...});
     */
    $.widget("ui.etsy_variation_matching", $.ui.ml_variation_matching, {
        _loadMPVariation: function(val, initial) {
            var self = this,
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

            self._load(requestParams, function(data) {
                self._buildShopVariationSelectors(data, !initial, true);
            });
// alert(val); // == cID (wie es sein soll)
//$(ml_vm_config.formName).etsy_variation_matching.elements.mainSelectElementVisual.val('Kategorie '+val);
//self.elements.mainSelectElementVisual.val('Kategorie '+val);
//$('#PrimaryCategoryVisual').val('Kategorie '+val);
/*
            var categoryEl = $('#PrimaryCategory');
            mpCategorySelector.startCategorySelector(function(val) { 
alert(categoryEl.find('[value='+val+']');
                if (categoryEl.find('[value='+val+']').length > 0) {
                    categoryEl.find('[value='+val+']').attr('selected','selected');
                    categoryEl.trigger('change');
                } else {
                    //categoryEl.trigger('change');
                    generateEtsyCategoryPath(val, $('#PrimaryCategoryVisual'));
                }
             }, 'mp');
*/
        }
    });

    $(ml_vm_config.formName).etsy_variation_matching({
        urlPostfix: '&kind=ajax&where=' + ml_vm_config.viewName,
        i18n: ml_vm_config.i18n,
        elements: {
            newGroupIdentifier: '#newGroupIdentifier',
            customVariationHeaderContainer: '#tbodyVariationConfigurationSelector',
            newCustomGroupContainer: '#newCustomGroup',
            mainSelectElement: '#PrimaryCategory',
            mainSelectElementVisual: '#PrimaryCategoryVisual',
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
/*/
    $('#selectPrimaryCategory').click(function() {
        var categoryEl = $('#PrimaryCategory');
        mpCategorySelector.startCategorySelector(function(cID) { 
            if (categoryEl.find('[value='+cID+']').length > 0) {
                categoryEl.find('[value='+cID+']').attr('selected','selected');
                categoryEl.trigger('change');
            } else {
                categoryEl.trigger('change');
                generateEtsyCategoryPath(cID, $('#PrimaryCategoryVisual'));
            }
         }, 'mp');
     });
//*/


});
 
