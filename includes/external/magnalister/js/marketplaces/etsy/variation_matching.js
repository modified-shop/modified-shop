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
        // Override: After base rendering, add a "CustomPropertyName" text input
        // to FreeText optional attributes ("Selbstgewählte Eigenschaft") so users
        // can define what the property is called on Etsy (property_id 513/514).
        _buildShopVariationSelectors: function(data, resetNotice, savePrepare) {
            this._super(data, resetNotice, savePrepare);

            var self = this,
                attributes = data.Attributes,
                nameInputs = {};

            for (var i in attributes) {
                if (!attributes.hasOwnProperty(i)) continue;
                // Only for optional, non-custom, text-type attributes (= Selbstgewählte Eigenschaft)
                if (attributes[i].Required || attributes[i].Custom) continue;
                if (attributes[i].DataType !== 'text') continue;

                var attrCode = attributes[i].AttributeCode,
                    baseName = 'ml[match]' + self.attributesNamePrefix + '[' + attrCode + ']',
                    safeId = self.generateAttributeCodeId(attrCode),
                    currentValue = '';

                if (attributes[i].CurrentValues && attributes[i].CurrentValues.CustomPropertyName) {
                    currentValue = attributes[i].CurrentValues.CustomPropertyName;
                }

                var nameInput = $('<div id="customPropertyName_' + safeId + '">')
                    .css({'margin-top': '5px', 'font-weight': 'normal'})
                    .append($('<input>').attr({
                        type: 'text',
                        name: baseName + '[CustomPropertyName]',
                        value: currentValue,
                        placeholder: 'Eigenschaftsname auf Etsy',
                        maxlength: 45,
                        style: 'width: 100%; padding: 2px 4px; box-sizing: border-box;'
                    }));

                nameInputs[safeId] = nameInput;
                $('#selRow_' + safeId).children('th').append(nameInput);
            }

            // Re-attach when optional attribute selector changes
            // (changeCurrentAttribute clears <th> content)
            var optionalSelector = self.elements.matchingOptionalInput.find('select[name="optional_selector"]');
            if (optionalSelector.length) {
                optionalSelector.on('change.customPropertyName', function() {
                    var selectedId = $(this).val();
                    setTimeout(function() {
                        if (nameInputs[selectedId]) {
                            $('#selRow_' + selectedId).children('th').append(nameInputs[selectedId]);
                        }
                    }, 0);
                });
            }
        },
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
 
