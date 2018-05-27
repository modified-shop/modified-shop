(function($) {
    $.widget("ui.ayn24variationmatching", {
        options: {
            urlPostfix: '&ajax=true',
            i18n: {},
            elements: {}
        },
        i18n: {
            defineName: 'defineName',
            ajaxError: 'ajaxerror',
            selectVariantGroup: 'selectVariantGroup',
            allAttributsMustDefined: 'allAttributsMustDefined',
            pleaseSelect: 'pleaseSelect',
            shopValue: 'shopValue',
            mpValue: 'mpValue',
            dontTransmit: 'dontTransmit',
            webShopAttribute: 'webShopAttribute',
            deleteCustomGroupButtonTitle: 'deleteCustomGroupButtonTitle',
            deleteCustomGroupButtonContent: 'deleteCustomGroupButtonContent',
            deleteCustomGroupButtonOk: 'ok',
            deleteCustomGroupButtonCancel: 'cancel'
        },
        html: {
            shopVariationsDropDown: '',
            valuesBackup: ''
        },
        elements: {
            form: null,
            mainSelectElement: null,
            newGroupIdentifier: null, //hidden input for transport 
            newCustomGroupContainer: null,
            newCustomGroupButton: null, //input type = button inside newCustomGroupContainer
            newCustomGroupText: null, //input type = text inside newCustomGroupContainer
            deleteCustomGroupButton: null, //input type = submit inside newCustomGroupContainer
            customVariationHeaderContainer: null, // header for custom variation
            customVariationHeaderSelect: null, //select inside customVariationHeaderContainer
            matchingHeadline: null,
            matchingCustomHeadline: null,
            matchingInput: null,
            matchingCustomInput: null
        },
        variationValues: {},
        self: null,

        _create: function() {
            myConsole.log('_create()');
            myConsole.log({'options': this.options});
        },

        _init: function() {
            self = this;
            myConsole.log('_init()');
            //i18n
            for (var i in self.options.i18n) {
                if (typeof self.i18n[i] !== 'undefined') {
                    self.i18n[i] = $("<div/>").html(self.options.i18n[i]).html();
                }
            }
            for (var i in self.options.elements) {
                if (typeof self.elements[i] !== 'undefined') {
                    if (typeof self.options.elements[i] === 'string') {
                        self.elements[i] = self.element.find(self.options.elements[i]);
                    } else {
                        self.elements[i] = self.options.elements[i];
                    }
                }
            }
            if (self.elements.form === null) {
                self.elements.form = self.element.find('form').andSelf().filter('form');
            }

            self.options.groupedShopVariations = JSON.parse(JSON.stringify(self.options.shopVariations));
            self._flat_attributes(self.options.shopVariations);

            self.html.valuesBackup = self.elements.matchingInput.html();
            self._initCustomVariationContainer();
            self._initNewCustomGroupElement();
            self._initMainSelectElement();
            self._initFormSubmit();
        },

        _initCustomVariationContainer: function() {
            self.elements.customVariationHeaderSelect =
                self.elements.customVariationHeaderSelect === null
                    ? self.elements.customVariationHeaderContainer.find('select')
                    : self.elements.customVariationHeaderSelect
            ;
            self.elements.customVariationHeaderSelect.change(function() {
                self._loadMPVariation($(this).val());
            }).val('null');
        },

        _initFormSubmit: function() {
            self.element.find('input[type="submit"]').click(function(e) {
                if (!self._matchingIsValid()) {
                    e.preventDefault();
                    return false;
                }
                return true;
            });
        },

        _initNewCustomGroupElement: function() {
            self.elements.newCustomGroupButton =
                self.elements.newCustomGroupButton === null
                    ? self.elements.newCustomGroupContainer.find('input[type="button"]')
                    : self.elements.newCustomGroupButton
            ;
            self.elements.newCustomGroupText =
                self.elements.newCustomGroupText === null
                    ? self.elements.newCustomGroupContainer.find('input[type="text"]')
                    : self.elements.newCustomGroupText
            ;
            self.elements.newCustomGroupButton.click(function() {
                var ident = $.trim(self.elements.newCustomGroupText.val());
                if (
                    ident + '' === ''
                    && self.elements.mainSelectElement.val() === 't:new'
                ) {
                    alert(self.i18n.defineName);
                    // @todo: call some kind of reset method.
                    return;
                }
                self.elements.customVariationHeaderContainer.css('display', 'table-row-group');
                self.elements.matchingHeadline.css('display', 'table-row-group');
                self.elements.matchingInput.css('display', 'table-row-group');
            });
            self.elements.newCustomGroupText.keydown(function(event) {
                if (event.which === 13) {
                    self.elements.newCustomGroupButton.trigger('click');
                    return false;
                } else {
                    return true;
                }
            });

            self.elements.deleteCustomGroupButton =
                self.elements.deleteCustomGroupButton === null
                    ? self.elements.newCustomGroupContainer.find('input[type="submit"]')
                    : self.elements.deleteCustomGroupButton
            ;
            self.elements.deleteCustomGroupButton.on("click", function(event, sure) {
                if(typeof sure !== 'undefined' && sure){
                    return true;
                }else{
                    var eDialog = jQuery('<div class="dialog2" title="'+self.i18n.deleteCustomGroupButtonTitle+'">'+self.i18n.deleteCustomGroupButtonContent+'</div>');
                    jQuery("body").append(eDialog);
                    eDialog.jDialog({
                        buttons: {
                            Ok: {
                                'text':self.i18n.deleteCustomGroupButtonOk,
                                click: function() {
                                    jQuery(this).dialog('close');
                                    self.elements.deleteCustomGroupButton.trigger('click', true);
                                }
                            },		
                            Cancel: {
                                'text':self.i18n.deleteCustomGroupButtonCancel,
                                click: function() {
                                    jQuery(this).dialog('close');
                                }
                            },
                        },
//			position: { my: "center center", at: "center top+80", of: window },
                        close: function(event, ui){
                            jQuery(this).dialog('close');
                        }
                    });
                    return false;
                }
            });
        },

        _initMainSelectElement: function() {
            self.elements.mainSelectElement.change(function() {
                self.elements.customVariationHeaderSelect.val('null');
                self.elements.newCustomGroupContainer.css('display', 'none');
                self.elements.newCustomGroupButton.css('display', 'inline');
                self.elements.newCustomGroupText.css('display', 'inline');
                self.elements.newCustomGroupButton.css('display', 'inline');
                self.elements.newCustomGroupText.val('');
                self.elements.newGroupIdentifier.val('');
                self.elements.deleteCustomGroupButton.css('display', 'none');
                self.elements.customVariationHeaderContainer.css('display', 'none');
                self.elements.matchingHeadline.css('display', 'none');
                self.elements.matchingInput.html(self.html.valuesBackup).css('display', 'none');

                val = $(this).val().split(':');
                myConsole.log('val', val);
                switch (val[0]) {
                    case 't': {
                        myConsole.log('Handle T');
                        if (val[1] === 'new') {
                            self.elements.newCustomGroupContainer.css('display', 'inline');
                        }
                        break;
                    }
                    case 'mp': {
                        self._loadMPVariation(val[1]);
                        self.elements.matchingHeadline.css('display', 'table-row-group');
                        self.elements.matchingInput.css('display', 'table-row-group');
                        myConsole.log('Handle MP');
                        break;
                    }
                    case 'ct': {
                        myConsole.log('Handle CT');
                        self.elements.deleteCustomGroupButton.css('display', 'inline');
                        self.elements.newCustomGroupText.css('display', 'none');
                        self.elements.newCustomGroupButton.css('display', 'none');
                        self.elements.newCustomGroupContainer.css('display', 'inline');
                        self.elements.newCustomGroupText.val($(this).find(':selected').text());
                        self.elements.newCustomGroupButton.trigger('click');
                        self.elements.customVariationHeaderSelect.val(val[2]).trigger('change');
                        break;
                    }
                }
            }).trigger('change');
        },

        _render: function(template, data) {
            var out = '';
            var current = '';
            for (var i in data) {
                current = template;
                current = current.replace(new RegExp('\{%count%\}', 'g'), i);
                for (var ii in data[i]) {
                    if (
                        typeof data[i][ii] !== 'undefined'
                        && typeof data[i][ii] !== 'object'
                    ) {
                        current = current.replace(new RegExp('\{' + ii + '\}', 'g'), data[i][ii]);
                    }
                }
                out += current;
            }
            return out;
        },

        _getShopVariationsDropDownElement: function () {
            var self = this,
                counter = 1,
                numberOfOptGroups = 2;

            if (self.html.shopVariationsDropDown === '') {
                self.html.shopVariationsDropDown = 
                    '<select class="shopAttrSelector">' + self._render('<option value="{Code}">{Name}</option>',
                        {0: {Code: 'null', Name: self.i18n.pleaseSelect}});

                for (var property in self.options.groupedShopVariations) {
                    if (self.options.groupedShopVariations.hasOwnProperty(property)) {
                        if (counter++ <= numberOfOptGroups) {
                            var options = self._render('<option value="{Code}">{Name}</option>',
                                self.options.groupedShopVariations[property]);
    
                            self.html.shopVariationsDropDown += '<optgroup label="' + property +'"> '
                                + options +
                                '</optgroup>';
                            continue;
                        }
                        self.html.shopVariationsDropDown += self._render('<option value="{Code}">{Name}</option>',
                            self.options.groupedShopVariations[property]);
                    }
                }

                self.html.shopVariationsDropDown += '</select>';
            }
            return $(self.html.shopVariationsDropDown);
        },

        _buildFreetextInfoTable: function(attributeCode, values) {
            myConsole.log('attributeCode', attributeCode);
            var data = [];
            for (var i in values) {
                data.push({key: i, value: values[i]});
            }
            var out = '<table class="attrTable matchingTable">';
            out += '    <tbody>';
            out += '        <tr class="headline">';
            out += '            <td class="key">' + self.i18n.shopValue + '</td>';
            out += '            <td class="input">' + self.i18n.mpValue + '</td>';
            out += '        </tr>';
            out += '    </tbody>';
            out += '    <tbody>';
            var template = '    <tr>';
            template += '        <td class="key">{value}</td>';
            template += '        <td class="input">';
            template += '            <select name="ml[match][ShopVariation][' + attributeCode + '][Values][{key}]">';
            template += '                <option value="null">' + self.i18n.dontTransmit + '</option>';
            template += '                <option value="{key}" selected="">{value}</option>';
            template += '            </select>';
            template += '        </td>';
            template += '    <tr>';
            out += self._render(template, data)
            out += '';
            out += '    </tbody>';
            out += '</table>';
            return $(out);
        },

        _load: function(data, success) {
            $.blockUI(blockUILoading);
            $.ajax({
                type: 'POST',
                url: self.elements.form.attr('action') + self.options.urlPostfix,
                dataType: 'json',
                data: data,
                success: function() {
                    $.unblockUI();
                    success.apply(this, arguments);
                },
                error: function(xhr, status, error) {
                    myConsole.log(arguments);
                    alert(options.i18n.ajaxError);
                    $.unblockUI();
                    self._resetMPVariation();
                }
            });
        },

        _resetMPVariation: function() {
            self.variationValues = {};
        },

        _buildMPShopMatching: function(elem, selector) {
            myConsole.log('_buildMPShopMatching', selector, values);
            var values = self.options.shopVariations[elem.val()];
            if (typeof values === 'undefined') {
                $('div#match_' + selector.id).html('');
                return;
            }
            if (selector.AllowedValues.length === 0) {
                $('div#match_' + selector.id).html('<input type="hidden" name="ml[match][ShopVariation][' + selector.AttributeCode + '][Kind]" value="FreeText">');
                var freeTextTable = self._buildFreetextInfoTable(selector.AttributeCode, values.Values);
                if (typeof selector.CurrentValues.Values !== 'undefined') {//saved values
                    for (var i in selector.CurrentValues.Values) {
                        freeTextTable.find('select[name="ml[match][ShopVariation][' + selector.AttributeCode + '][Values][' + i + ']"]').val(
                            selector.CurrentValues.Values[i]
                        );
                    }
                }
                $('div#match_' + selector.id).append(freeTextTable);
                return;
            }
            var mpDD = 
                '<select name="ml[match][ShopVariation][' + selector.AttributeCode + '][Values][{key}]">'
                + self._render(
                    '<option value="{key}">{value}</option>',
                    function() {
                        var out = [{key: 'null', value: self.i18n.pleaseSelect}];
                        for (var i in selector.AllowedValues) {
                                out.push({key: i, value: selector.AllowedValues[i]});
                        }
                        return out;
                    }()
                )
                + '</select>'
            ;
            var tbody = $(
                '<tbody>'
                + self._render(
                    '<tr><td class="key">{value}</td><td class="input">' + mpDD + '</td></tr>'
                    , function() {
                        var out = [];
                        for (var i in values.Values) {
                                out.push({key: i, value: values.Values[i]});
                        }
                        return out;
                    }()
                )
                + '</tbody>'
            );
            if (typeof selector.CurrentValues.Values !== 'undefined') {//saved values
                for (var i in selector.CurrentValues.Values) {
                    tbody.find('select[name="ml[match][ShopVariation][' + selector.AttributeCode + '][Values][' + i + ']"]').val(
                        selector.CurrentValues.Values[i]
                    );
                }
            }
            return $('div#match_' + selector.id).html(
                $('<table class="attrTable matchingTable"></table>')
                .append(
                    '<tbody><tr class="headline"><td class="key">Shop-Wert</td><td class="input">' + self.i18n.mpValue + '</td></tr></tbody>'
                )
                .append(tbody)
            );
        },

        _buildShopVariationSelectors: function(data) {
            self.elements.matchingInput.html('');
            var colTemplate = '	<tr id="selRow_{id}">'
                        + '         <th>{AttributeName}</th>'
                        + '         <td id="selCell_{id}">'
                        + '             <label>' + self.i18n.webShopAttribute + ':</label>'
                        + '             {shopVariationsDropDown}'
                        + '             <input type="hidden" name="ml[match][ShopVariation][{AttributeCode}][Kind]" value="Matching">'
                        + '             <div id="match_{id}"></div>'
                        + '         </td>'
                        + '         <td class="info"></td>'
                        + '	</tr>'
            ;
            for (var i in data) {
                data[i].id = data[i].AttributeCode.replace(/[^A-Za-z0-9_]/g, '_'); // css selektor-save.
                data[i].AttributeName = data[i].AttributeName || data[i].AttributeCode;
                self.variationValues[data[i].AttributeCode] = data[i].AllowedValues;
                data[i].shopVariationsDropDown = $('<div>')
                    .append(
                        self._getShopVariationsDropDownElement()
                        .attr('id', 'sel_' + data[i].id)
                        .attr('name', 'ml[match][ShopVariation][' + data[i].AttributeCode + '][Code]')
                    )
                    .html()
                ;
            }
            self.elements.matchingInput.append($(self._render(colTemplate, data)));
            self.elements.matchingInput.find('select[id^=sel_]').each(function() {
                $(this).change(function() {
                    for (i in data) {
                        if ('sel_' + data[i].id === $(this).attr('id')) {
                            self._buildMPShopMatching($(this), data[i]);
                            break;
                        }
                    }
                });
            });
            for (var i in data) {
                if (typeof data[i].CurrentValues.Code !== 'undefined') {
                    self.elements.matchingInput.find('select[id^=sel_' + data[i].id + ']').val(data[i].CurrentValues.Code).trigger('change');
                }
            }
            self._prefix_option();
        },

        _loadMPVariation: function(val) {
            self._resetMPVariation();
            if (val === 'null') {
                self.elements.matchingInput.html(self.html.valuesBackup);
                return;
            }
            self.elements.newGroupIdentifier.val(val);
            self._load({
                'Action': 'LoadMPVariations',
                'SelectValue': self.elements.mainSelectElement.val(),
                'MPVariation': val
            }, function(data) {
                self._buildShopVariationSelectors(data);
            });
        },

        _matchingIsValid: function() {
            var isValid = true,
                mpActionSelectValue = self.elements.mainSelectElement.val()
            ;
            if (mpActionSelectValue === 't:null') {
                alert(self.i18n.selectVariantGroup);
                return false;
            }
            if ($('.shopAttrSelector').length == 0) {
                isValid = false;
                return false;
            }
            $('.shopAttrSelector').each(function() {
                if ($(this).val() === 'null') {
                    isValid = false;
                    return false;
                }
                return true;
            });
            if (!isValid) {
		self.elements.newCustomGroupContainer.find('#isMatchingValid').val('false');
            }
            return true;
        }
    });
//  $.extend($.ml.ayn24variationmatching, {
//      _create: function(){alert('D');}
//  });
})(jQuery);
