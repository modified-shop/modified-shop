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

        _buildShopVariationSelectors: function(data, resetNotice, savePrepare) {
            var self = this,
                i;

            self._super(data, resetNotice, savePrepare);

            if(data.Subcategories && data.Subcategories.length > 0) {
                $('#tbodySubcategoriesHeadline').css('display', 'table-row-group');
                var subcategories = $('#tbodySubcategoriesInput');
                subcategories.css('display', 'table-row-group');
                subcategories.html('');

                for(i in data.Subcategories) {
                    if (data.Subcategories.hasOwnProperty(i)) {
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


                        var options = '<option value>' + self.i18n.pleaseSelect + '</option>';
                        for (var j in subcategory.AllowedValues) {
                            if (subcategory.AllowedValues.hasOwnProperty(j)) {
                                var selected = subcategory.CurrentValues && subcategory.CurrentValues.Values == j ? 'selected ' : '';
                                options += '<option value="' + j + '" ' + selected + ' >' + subcategory.AllowedValues[j] + ' </option>';
                            }
                        }

                        template = template.replace(new RegExp('\{options\}', 'g'), options);
                        subcategories.append(template);
                    }
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
