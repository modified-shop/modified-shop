$(document).ready(function() {

    $.widget("ui.cdiscount_variation_matching", $.ui.ml_variation_matching, {
        _init: function() {
            this._super();

            // This function overrides submit button
            this._popupInfo();
        },

        _popupInfo: function() {
            var isSafari = /^((?!chrome).)*safari/i.test(navigator.userAgent);
            $(".magnamain :submit").unbind('click');
            $(ml_vm_config.formName)[0].onsubmit = function (e) {
                var me = this,
                    d = $('#mandatoryfieldsinfo').html();
                $.unblockUI();
                $('#infodiagmandatory').html(d).jDialog({
                    width: (d.length > 1000) ? '700px' : '500px',
                    buttons: {
                        'ABBRECHEN': function() {
                            $(this).dialog('close');
                            return false;
                        },
                        'OK': function() {
                            $(this).dialog('close');
                            var button = $('input[name="saveMatching"]');
                            if (isSafari) {
                                e.preventDefault();
                                var tehForm = $(button).parents('form'),
                                    btnName = $(button).attr('name') || '';
                                // Pass the information which button has been pressed. For some forms it is important
                                if (btnName != '') {
                                    tehForm.append($('<input>').attr({
                                        'type': 'hidden',
                                        'name': btnName,
                                        'value': $(button).attr('value') || ''
                                    }));
                                }
                                $.blockUI(jQuery.extend(blockUILoading, {
                                    onBlock: function () {
                                        //console.log('Submit');
                                        tehForm.submit();
                                    }
                                }));
                                return false;
                            } else {
                                setTimeout(function() { $.blockUI(blockUILoading); }, 1000);
                                me.submit();
                                return true;
                            }
                        }
                    }
                });

                return false;
            };
        },

        _buildShopVariationSelector: function (data) {
            var self = this,
                kind = 'FreeText',
                baseName = 'ml[match][ShopVariation][' + data.AttributeCode + ']',
                isSelectAndText = data.DataType === 'selectAndText';

            data.id = data.AttributeCode.replace(/[^A-Za-z0-9_]/g, '_'); // css selector-save.
            data.AttributeName = data.AttributeName || data.AttributeCode;
            data.AttributeDescription = data.AttributeDescription || ' ';
            self.variationValues[data.AttributeCode] = data.AllowedValues;
            var variationsDropDown =
                self._getShopVariationsDropDownElement()
                    .attr('id', 'sel_' + data.id)
                    .attr('name', baseName + '[Code]');

            if (data.CurrentValues.Error == true) {
                variationsDropDown.attr('style', 'border-color:red');
                data.style = 'style="color:red"';
            } else {
                data.style = '';
            }

            // If attribute is already matched add options
            if (typeof data.CurrentValues.Values !== 'undefined'
                && (data.CurrentValues.Values.length > 0 || Object.keys(data.CurrentValues.Values).length > 0)) {
                self._addShopOptions(self, variationsDropDown, data, isSelectAndText);
            }

            if (data.Required == true) {
                data.redDot = '<span class="bull">&bull;</span>';
            } else {
                data.redDot = '';
            }

            if (data.AllowedValues.length > 0 || Object.keys(data.AllowedValues).length > 0) {
                kind = 'Matching';
            }

            data.shopVariationsDropDown = $('<div>')
                .append(variationsDropDown)
                .append('<div id="extraFieldsInfo_' +  data.AttributeCode + '" style="display: inline;"></div>')
                .append('<input type="hidden" name="' + baseName + '[Kind]" value="' + kind + '">')
                .append('<input type="hidden" name="' + baseName + '[Required]" value="' + (data.Required ? 1 : 0) + '">')
                .append('<input type="hidden" name="' + baseName + '[AttributeName]" value="' + data.AttributeName + '">')
                .append('<div id="infodiagmandatory" class="ml-modal dialog2" title="Hinweis"></div><span id="mandatoryfieldsinfo" style="display: none">' + self.i18n.mandatoryInfo + '</span>')
                .html()
            ;

            setTimeout(function() {
                var selectElement = document.getElementById('sel_' + data.id);
                selectElement.addEventListener('mousedown', function() {
                    if (this.options.length === 1) {
                        self._addShopOptions(self, this, data, isSelectAndText);
                    }
                });
            }, 0);

            return data;
        },
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
            matchingCustomHeadline: '#tbodyDynamicMatchingCustomHeadline',
            matchingOptionalHeadline: '#tbodyDynamicMatchingOptionalHeadline',
            matchingInput: '#tbodyDynamicMatchingInput',
            matchingCustomInput: '#tbodyDynamicMatchingCustomInput',
            matchingOptionalInput: '#tbodyDynamicMatchingOptionalInput',
            categoryInfo: '#categoryInfo'
        },
        shopVariations: ml_vm_config.shopVariations
    });
});
