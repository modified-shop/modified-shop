if(typeof klarna_invoice_fee == 'undefined') {
    var klarna_invoice_fee = 0;
}
if(typeof global_sum == 'undefined') {
    var global_sum = 0;
}
if(typeof ajax_path == 'undefined') {
    var ajax_path = 'klarnaAjax.php';
}

// Workaround for old jQuery versions
if (typeof jQuery.prototype.focusin == "undefined") {
    jQuery.prototype.focusin = jQuery.prototype.focus;
}
if (typeof jQuery.prototype.focusout == "undefined") {
    jQuery.prototype.focusout = jQuery.prototype.blur;
}
if (typeof jQuery.prototype.closest == "undefined") {
    jQuery.prototype.closest = jQuery.prototype.parents;
}

var klarnaGeneralLoaded = true;
var blue_baloon_busy = false;
var baloons_moved = false;
var flagChange_active = false;
var changeLanguage_busy = false;
var openBox_busy = false;
var gChoice;

var klarna_js_loaded = true;

KlarnaGlobalContext = function(info) {
    this.currentMinHeight_invoice = '';
    this.currentMinHeight_part = '';
    this.currentMinHeight_spec = '';

    for(key in info) {
        this[key] = info[key];
    }
};

KlarnaPaymentOption = function(info) {
    for(key in info) {
        this[key] = info[key];
    }
};

KlarnaErrorHandler = function () {
}

KlarnaErrorHandler.prototype.show = function (parentBox, message, code, type) {
    var errorHTML = '<div class="klarna_errMsg"><span>'+message+'</span></div>';
    errorHTML += '<div class="klarna_errDetails">';
    if (typeof type != 'undefined' && type != '' ) {
        errorHTML += '<span class="klarna_errType">'+type+'</span>';
    }
    if (typeof code != 'undefined' && code != '' ) {
        errorHTML += '<span class="klarna_errCode">#'+code+'</span></div>';
    }

    if (jQuery('#klarna_red_baloon').length == 0) {
        this.create();
    }

    jQuery('#klarna_red_baloon_content').html(errorHTML);
    this.showRedBaloon(parentBox);
};

/**
 * Creates the red baloon used to show error messages
 */
KlarnaErrorHandler.prototype.create = function () {
    jQuery(
        '<div class="klarna_red_baloon" id="klarna_red_baloon">' +
        '<div class="klarna_red_baloon_top"></div>' +
        '<div class="klarna_red_baloon_middle" id="klarna_red_baloon_content"></div>' +
        '<div class="klarna_red_baloon_bottom"></div>' +
        '</div>').appendTo('body');
};

KlarnaErrorHandler.prototype.prepareRedBaloon = function () {
    if ((typeof klarna_red_baloon_content != 'undefined') &&
        (klarna_red_baloon_content != '')
    ) {
        var box;
        if (klarna_red_baloon_box != '') {
            box = jQuery('#' + klarna_red_baloon_box)
        }
        this.show(box, klarna_red_baloon_content);
    }
};

KlarnaErrorHandler.prototype.showRedBaloon = function (box) {
    if (this.busy)
        return;

    this.busy = true;
    var field;
    if (typeof box == 'undefined') {
        if (gChoice == klarna.global.invoice_name) {
            box = jQuery(document).find('#klarna_box_invoice');
        } else if (gChoice == klarna.global.part_name) {
            box = jQuery(document).find('#klarna_box_part');
        } else if (gChoice == klarna.global_spec_name) {
            box = jQuery(document).find('#klarna_box_spec');
        }
    }

    if (typeof box != 'undefined') {
        field = box.find('.klarna_logo');
    }

    if (typeof field == 'undefined' || field.length == 0) {
        field = jQuery('.klarna_logo:visible');
    }

    if (field.length > 0) {
        var callback = this.fadeRedBaloon;
        var position = field.offset();
        var top = (position.top - jQuery('#klarna_red_baloon').height()) + (jQuery('#klarna_red_baloon').height() / 6);
        if (top < 0) top = 10;
        position.top = top;
        var left = (position.left + field.width()) - (jQuery('#klarna_red_baloon').width() / 2);

        position.left = left;

        jQuery('#klarna_red_baloon').css(position);

        jQuery('#klarna_red_baloon').fadeIn('slow', function () {
            setTimeout(callback, 3000);
        });
    } else {
        this.busy = false;
    }
};

KlarnaErrorHandler.prototype.fadeRedBaloon = function () {
    this.busy = false;
    jQuery('#klarna_red_baloon').addClass('klarna_fading_baloon');
};

KlarnaErrorHandler.prototype.hideRedBaloon = function () {
    this.busy = false;
    if (jQuery('#klarna_red_baloon').is(':visible'))
    {
        jQuery('#klarna_red_baloon').fadeOut('fast', function () {
            this.showing_address_error = false;
            jQuery(this).remove();
        });
    }
};

window.klarna = new function() {
    this.errorHandler = new KlarnaErrorHandler();
    this.address_busy = false;

    if (typeof klarna_invoice != 'undefined') {
        this.invoice = new KlarnaPaymentOption(klarna_invoice);
    }

    if (typeof klarna_part != 'undefined') {
        this.part = new KlarnaPaymentOption(klarna_part);
    }

    if (typeof klarna_spec != 'undefined') {
        this.spec = new KlarnaPaymentOption(klarna_spec);
    }

    if (typeof klarna_global != 'undefined') {
        this.global = new KlarnaGlobalContext(klarna_global);
    } else {
        this.global = new KlarnaGlobalContext({});
    }
};

Address = function (companyName, firstName, lastName, street, zip, city, countryCode) {
    this.companyName = companyName;
    this.firstName = firstName;
    this.lastName = lastName;
    this.street = street;
    this.zip = zip;
    this.city = city;
    this.countryCode = countryCode;
    this.isCompany = (this.companyName.length > 0);
};

Address.fromXML = function (elem) {
    return new Address(
        jQuery(elem).find('companyName').text(),
        jQuery(elem).find('first_name').text(),
        jQuery(elem).find('last_name').text(),
        jQuery(elem).find('street').text(),
        jQuery(elem).find('zip').text(),
        jQuery(elem).find('city').text(),
        jQuery(elem).find('countryCode').text()
    );
};

Address.Mode = function Mode() {}
Address.Single = new Address.Mode();
Address.Multi = new Address.Mode();

Address.prototype.inputValue = function () {
    return [(this.isCompany
                ? this.companyName
                : (this.firstName + '|' + this.lastName)),
        this.street,
        this.zip,
        this.city,
        this.countryCode].join('|');
}

Address.prototype.render = function (mode) {
    if (mode == Address.Single) {
        return '<p>' +
            (this.isCompany
                ? this.companyName
                : (this.firstName + ' ' + this.lastName)) + '</p>' +
            '<p>' + this.street + '</p>' +
            '<p>' + this.zip + ' ' + this.city + '</p>' +
            '<p>' + this.countryCode + '</p>';
    } else if (mode == Address.Multi) {
        return '<option value="' + this.inputValue() + '">' +
            (this.isCompany
                ? this.companyName
                : (this.firstName + ' ' + this.lastName)) +
            ', ' + this.street +
            ', ' + this.zip + ' ' + this.city +
            ', ' + this.countryCode;
    }
}

AddressCollection = function (addresses) {
    this.addresses = addresses;
    this.mode = addresses.length > 1 ? Address.Multi : Address.Single;
}

AddressCollection.fromXML = function (elem) {
    return new AddressCollection(jQuery('address', elem).map(function () {
        var addr = Address.fromXML(this);
        return addr;
    }));
}

AddressCollection.prototype.render = function (to, inputName) {
    var box = jQuery(to).find('.klarna_box_bottom_address_content');
    box.empty();
    if (this.mode == Address.Single) {
        var inputValue = this.addresses[0].inputValue();
        var input = jQuery('<input type="hidden" name="' + inputName + '" value="' + inputValue + '" />')
        box.append(input);
        box.append(this.addresses[0].render(Address.Single));
    } else if (this.mode == Address.Multi) {
        var select = jQuery('<select name="' + inputName + '">')
        box.append(select);

        jQuery.each(this.addresses, function(i, addr) {
            select.append(addr.render(Address.Multi));
        });
    }
}

function getPaymentOption () {
    if (jQuery('input[type=radio][name="' + klarna.global.pid + '"]').length > 0)
        var box = jQuery('input[type=radio][name="' + klarna.global.pid + '"]:checked');
    else
        var box = jQuery('input[type=hidden][name="' + klarna.global.pid + '"]');

    return jQuery(box).val();
}

function hidePaymentOption (box, animate) {
    if (typeof animate == 'undefined') {
        animate = false;
    }

    if (animate) {
        jQuery(box).find('.klarna_box_top_right, .klarna_box_bottom').
            css({'display': 'none'});
    } else {
        jQuery(box).find('.klarna_box_top_right, .klarna_box_bottom').
        fadeOut('fast');
    }

    jQuery(box).animate({'min-height': '55px'}, 200);
    showHideIlt(jQuery(box).find('.klarna_box_ilt'), false, animate);
}

function showPaymentOption (box, animate, currentMinHeight) {
    if (typeof animate == 'undefined') {
        animate = false;
    }

    if (animate) {
        jQuery(box).animate({"min-height": currentMinHeight}, 200, function () {
            showHideIlt(jQuery(this).find('.klarna_box_ilt'), true);
            jQuery(this).find('.klarna_box_bottom').fadeIn('fast', function () {
                jQuery('.klarna_box_bottom_content_loader').fadeOut();

                if (klarna.errorHandler.showing_address_error) {
                    klarna.errorHandler.hideRedBaloon();
                }
            });
            jQuery(this).find('.klarna_box_top_right').fadeIn('fast');

            openBox_busy = false;
        });
    } else {
        jQuery(box).find('.klarna_box_top_right, .klarna_box_bottom').fadeIn('fast');
        showHideIlt(jQuery(box).find('.klarna_box_ilt'), true, animate);
    }
}

function initPaymentSelection () {
    var choice = getPaymentOption();
    gChoice = jQuery(document).find('input[value="'+choice+'"]').attr("id");
    if (choice != klarna.global.invoice_name) {
        hidePaymentOption(jQuery('#klarna_box_invoice'));
    } else {
        showPaymentOption(jQuery('#klarna_box_invoice'));
    }

    if (choice != klarna.global.part_name) {
        hidePaymentOption(jQuery('#klarna_box_part'));
    } else {
        showPaymentOption(jQuery('#klarna_box_part'));
    }

    if (choice != klarna.global.spec_name) {
        hidePaymentOption(jQuery('#klarna_box_spec'));
    } else {
        showPaymentOption(jQuery('#klarna_box_spec'));
    }

    jQuery(document).find('input[type=radio][name="'+klarna.global.pid+'"]').each(function () {
        var value = jQuery(this).val();
        // If value is a number it can't be used so we fallback to id.
        if (!isNaN(value)) {
             var value = jQuery(this).attr('id');
        }
        jQuery(this).parent().parent().click(function (){
            choosePaymentOption(value);
        });
        jQuery(this).click(function (){
            choosePaymentOption(value);
        });
    });
}

//Load when document finished loading
jQuery(document).ready(function (){
    var baloon = jQuery('#klarna_baloon').clone();
    jQuery(document).find('#klarna_baloon').each(function () {
        jQuery(this).remove();
    });

    var baloon3 = jQuery('#klarna_blue_baloon').clone();
    jQuery(document).find('#klarna_blue_baloon').each(function () {
        jQuery(this).remove();
    });

    jQuery('body').append(baloon);
    jQuery('body').append(baloon3);

    doDocumentIsReady();

    jQuery(document).find('.klarna_box_bottom_languageInfo').remove();

    if (!klarna.global.unary_checkout) {
        initPaymentSelection();
    }

    baloons_moved = true;
});

function choosePaymentOption (choice) {
    if (openBox_busy == false)
    {
        klarna.errorHandler.hideRedBaloon();
        hideBlueBaloon();
        openBox_busy = true;
        jQuery(document).find('input[value="'+choice+'"]').attr("checked", "checked");
        jQuery(document).find('input[id="'+choice+'"]').attr("checked", "checked");
        if (choice == klarna.global.invoice_name)
        {
            hidePaymentOption(jQuery('#klarna_box_part'), true);
            hidePaymentOption(jQuery('#klarna_box_spec'), true);
            showPaymentOption(jQuery('#klarna_box_invoice'), true,
                klarna.global.currentMinHeight_invoice);
            invoice_active = true;

        }
        else if (choice == klarna.global.part_name)
        {
            hidePaymentOption(jQuery('#klarna_box_invoice'), true);
            hidePaymentOption(jQuery('#klarna_box_spec'), true);
            showPaymentOption(jQuery('#klarna_box_part'), true,
                klarna.global.currentMinHeight_part);
            part_active = true;
        }
        else if (choice == klarna.global.spec_name)
        {
            hidePaymentOption(jQuery('#klarna_box_invoice'), true);
            hidePaymentOption(jQuery('#klarna_box_part'), true);
            showPaymentOption(jQuery('#klarna_box_spec'), true,
                klarna.global.currentMinHeight_spec);
            spec_active = true;
        }
        else {
            jQuery('#klarna_box_part_top_right').fadeOut('fast');
            jQuery('#klarna_box_invoice_top_right').fadeOut('fast');
            jQuery('#klarna_box_spec_top_right').fadeOut('fast');

            jQuery('.klarna_box_bottom').fadeOut('fast', function () {
                jQuery(this).find('.klarna_box_ilt').fadeOut('fast');
                jQuery('#klarna_box_invoice').animate({"min-height": "55px"}, 200);
                jQuery('#klarna_box_part').animate({"min-height": "55px"}, 200);
                jQuery('#klarna_box_spec').animate({"min-height": "55px"}, 200);

                jQuery('.klarna_box_bottom_languageInfo').fadeOut('fast');

                invoice_active = false;
                openBox_busy = false;
            });
        }
    }
    chosen = choice;
}

function setGender (context, gender) {
    // This should be refactored to not be able to set other non-gender radio buttons
    var value;
    if (gender == 'm' || gender == '1')
    {
        jQuery('.Klarna_radio[value=1]', context).attr('checked', 'checked');
    }
    else if (gender == 'f' || gender == '0')
    {
        jQuery('.Klarna_radio[value=0]', context).attr('checked', 'checked');
    }
}

/**
 * Hook up jQuery callbacks for the given klarna_box_container(s) or
 * all klarna options in the document
 */
function initPaymentOptions(opts) {

    if (typeof opts == 'undefined') {
        opts = jQuery(document);
    }

    // Initialise the special campaigns link
    if (typeof klarna.spec != "undefined") {
        InitKlarnaSpecialPaymentElements('specialCampaignPopupLink', klarna.global.eid, klarna.global.countryCode);
    }

    // Bind the click action on the pclass selection
    jQuery('.klarna_box', opts).find('ol li').click(function (){
        // Reset list and move chosen icon to newly selected pclass
        jQuery(this).parent('ol.paymentPlan').find('li').removeClass('click');
        jQuery(this).addClass('click');

        // Update input field with pclass id
        var value = jQuery(this).find('span').html();
        var name = jQuery(this).parent("ol").attr("id");

        jQuery(this).closest('.klarna_box').find("input.paymentPlan").attr("value", value);
    });

    // Select gender radio from preselect value
    if ((klarna.global.countryCode == "de")
        || (klarna.global.countryCode == "nl"))
    {
        setGender(opts, klarna.global.gender);
    }

    // Display informative baloon on input field focus
    jQuery('.klarna_box', opts).find('input').focusin(function () {
        setBaloonInPosition(jQuery(this), false);
    }).focusout(function () {
        hideBaloon();
    });

    // Initialise language picker slide down
    jQuery('.box_active_language', opts).click(function () {
        if (flagChange_active == false)
        {
            flagChange_active = true;

            jQuery(this).parent().find('.klarna_box_top_flag_list').slideToggle('fast', function () {
                if (jQuery(this).is(':visible'))
                {
                    jQuery(this).parent('.klarna_box_top_flag').animate({opacity: 1.0}, 'fast');
                }
                else {
                    jQuery(this).parent('.klarna_box_top_flag').animate({opacity: 0.4}, 'fast');
                }

                flagChange_active = false;
            });
        }
    });

    // Change language when clicking a flag
    jQuery('.klarna_box_top_flag_list img', opts).click(function (){
        if (changeLanguage_busy == false)
        {
            changeLanguage_busy = true;

            var box = jQuery(this).parents('.klarna_box_container');
            var newIso = jQuery(this).attr("alt");

            jQuery('.box_active_language', box).attr("src", jQuery(this).attr("src"));

            var params;
            var type;

            if (box.find('.klarna_box').attr("id") == "klarna_box_invoice")
            {
                params = klarna.invoice.params;
                type = "invoice";
            }
            else if (box.find('.klarna_box').attr("id") == "klarna_box_part")
            {
                params = klarna.part.params;
                type = "part";
            }
            else if (box.find('.klarna_box').attr("id") == "klarna_box_spec")
            {
                params = klarna.spec.params;
                type = "spec";
            }
            else {
                return ;
            }

            changeLanguage(box, params, newIso, klarna.global.countryCode, type);
        }
    });

    setTimeout(function(){
        klarna.errorHandler.prepareRedBaloon()}, 1000);

    // hovering the language tear display the blue baloon
    jQuery('.klarna_box_bottom_languageInfo', opts).mousemove(function (e) {
        showBlueBaloon(e.pageX, e.pageY, jQuery(this).find('img').attr("alt"));
    }).mouseout(function () {
        hideBlueBaloon();
    });

    // Sex change radio
    jQuery('input.gender.Klarna_radio', opts).bind('change', function () {
        klarna.global.gender = jQuery(this).val();
    });

    // Bind pno field to call get addresses (swe only)
    jQuery('.Klarna_pnoInputField', opts).each(function (){
        var pnoField = jQuery(this);

        jQuery(this).bind("keyup change blur focus", function (){
            pnoUpdated(jQuery(this),
                (jQuery(this).parents('.klarna_box').attr("id") == "klarna_box_invoice"));
        });
    });

    // Company/Private purchase toggle
    jQuery('input.klarna_invoice_type', opts).change(function () {
        var val = jQuery(this).val();
        var box = jQuery(this).closest('.klarna_box')

        if (val == "private")
        {
            jQuery('.klarna_per_title', box).show();
            jQuery('.klarna_org_title', box).hide();
            jQuery('.klarna_box_private', box).slideDown('fast');
            jQuery('.klarna_box_company', box).slideUp('fast');
        }
        else if (val == "company")
        {
            jQuery('.klarna_per_title', box).hide();
            jQuery('.klarna_org_title', box).show();
            jQuery('.klarna_box_private', box).slideUp('fast');
            jQuery('.klarna_box_company', box).slideDown('fast');
        }
    });
}

function initDateSelectors (opts) {
    if (typeof opts == 'undefined') {
        opts = jQuery(document);
    }

    // Select birthdate and fill years box
    if (klarna.global.countryCode == "de" || klarna.global.countryCode == "nl")
    {
        var date = new Date();
        for (i = date.getFullYear(); i >= 1900; i--)
        {
            jQuery('#selectBox_year, #selectBox_part_year, #selectBox_spec_year', opts).each(function () {
                jQuery('<option/>').val(i).text(i).appendTo(this);
            });
        }

        if(typeof klarna.part != "undefined") {
            jQuery('#selectBox_part_bday', opts).val(klarna.part.select_bday);
            jQuery('#selectBox_part_bmonth', opts).val(klarna.part.select_bmonth);
            jQuery('#selectBox_part_year', opts).val(klarna.part.select_byear);
        }

        if(typeof klarna.spec != "undefined") {
            jQuery('#selectBox_spec_bday', opts).val(klarna.spec.select_bday);
            jQuery('#selectBox_spec_bmonth', opts).val(klarna.spec.select_bmonth);
            jQuery('#selectBox_spec_year', opts).val(klarna.spec.select_byear);
        }

        if(typeof klarna.invoice != "undefined") {
            jQuery('#selectBox_bday', opts).val(klarna.invoice.select_bday);
            jQuery('#selectBox_bmonth', opts).val(klarna.invoice.select_bmonth);
            jQuery('#selectBox_year', opts).val(klarna.invoice.select_byear);
        }
    }
}

function doDocumentIsReady ()
{
    klarna.global.currentMinHeight_invoice = jQuery('#klarna_box_invoice').height();
    klarna.global.currentMinHeight_part = jQuery('#klarna_box_part').height();
    klarna.global.currentMinHeight_spec = jQuery('#klarna_box_spec').height();

    initPaymentOptions();
    initDateSelectors();
}

function pnoUpdated (box, companyAllowed) {
    var pno_value = jQuery.trim(jQuery(box).val());

    if (klarna_global.countryCode != 'se') {
        return;
    }

    // Set the PNO to the other fields
    jQuery(document).find('.Klarna_pnoInputField').each(function () {
        jQuery(this).val(pno_value);
    });

    // Do check
    if (pno_value != "") {
        jQuery(document).find('.klarna_box_bottom_content_loader').each(function () {
            if (!jQuery(this).is(":visible"))
                jQuery(this).fadeIn('fast');
        });

        if (!validateSocialSecurity(pno_value)) {
            jQuery(document).find('.klarna_box_bottom_content_loader').each(function () {
                jQuery(this).fadeOut('fast');
            });

            if (jQuery('.klarna_box_bottom_address').is(":visible"))
                jQuery('.klarna_box_bottom_address').slideUp('fast');
        } else {
            getAddress (jQuery(box).closest('.klarna_box'), pno_value, companyAllowed);
        }
    } else {
        jQuery(document).find('.referenceDiv').each(function (){
            if (jQuery(this).is(":visible"))
            {
                jQuery(this).slideUp('fast');
            }
            else {
                jQuery(this).css({"display":"none"});
            }
        });

        jQuery('.klarna_box_bottom_content_loader').fadeOut('fast');

        jQuery(document).find('.klarna_box_bottom_address').each(function () {
            if (jQuery(this).is(":visible"))
            {
                jQuery(this).slideUp('fast');
            }
            else {
                jQuery(this).css({"display":"none"});
            }
        });
    }
}

/**
 * Showing and hiding the ILT questions
 *
 * @param field
 * @param show
 * @param animate
 */
function showHideIlt (field, show, animate)
{
    if (show == false)
    {
        if (animate == true)
            field.slideUp('fast');
        else
            field.hide();
    }
    else {
        var length = field.find('.klarna_box_iltContents').find('.klarna_box_ilt_question').length;

        if (length > 0)
        {
            if (animate == true)
                field.slideDown('fast');
            else
                field.show();
        }

    }
}

function getAddress (parentBox, pno_value, companyAllowed)
{
    if (!klarna.address_busy)
    {
        klarna.address_busy = true;

        data = {
            type: jQuery(parentBox).attr('id'),
            action: 'getAddress',
            country: klarna.global.countryCode,
            pno: pno_value
        }

        // Get the new klarna_box
        jQuery.ajax({
            type: "GET",
            url: klarna.global.ajax_path,
            data: data,
            success: function(xml){
                if (jQuery(xml).find('error').length > 0) {
                    var msg = jQuery(xml).find('message').text();
                    var code = jQuery(xml).find('code').text();
                    var type = jQuery(xml).find('type').text();
                    jQuery('.klarna_box_bottom_content_loader').fadeOut('fast', function () {
                        klarna.address_busy = false;
                    });
                    klarna.errorHandler.show(parentBox, msg, '', '');
                    klarna.errorHandler.showing_address_error = true;
                }
                if (jQuery(xml).find('address').length > 0) {
                    addresses = AddressCollection.fromXML(xml);

                    if (typeof klarna.invoice != "undefined")
                        addresses.render('#klarna_box_invoice', klarna.invoice.params['shipmentAddressInput']);

                    if (typeof klarna.part != "undefined")
                        addresses.render('#klarna_box_part', klarna.part.params['shipmentAddressInput']);

                    if (typeof klarna.spec != "undefined")
                        addresses.render('#klarna_box_spec', klarna.spec.params['shipmentAddressInput']);

                    jQuery.each(addresses.addresses, function(i, addr) {
                        if (addr.isCompany) {
                            jQuery('#invoiceType').val("company");
                            jQuery('.referenceDiv').slideDown('fast');

                            if (addresses.mode == Address.Single)
                            {
                                jQuery('.klarna_box_bottom').animate({"min-height": "300px"},'fast');
                            }

                            if (companyAllowed == false && typeof klarna.global.lang_companyNotAllowed != "undefined")
                            {
                                klarna.errorHandler.show(parentBox, klarna.global.lang_companyNotAllowed, '', '');
                                klarna.errorHandler.showing_address_error = true;
                            }
                            else {
                                if (klarna.errorHandler.showing_address_error)
                                    klarna.errorHandler.hideRedBaloon();
                            }
                        } else {
                            jQuery('#invoiceType').val("private");
                            jQuery(document).find('.referenceDiv').slideUp('fast');

                            jQuery('.klarna_box_bottom').animate({"min-height": "250px"},'fast');

                            if (klarna.errorHandler.showing_address_error)
                                klarna.errorHandler.hideRedBaloon();
                        }
                    });

                    jQuery('.klarna_box_bottom_address').slideDown('fast');
                    jQuery('.klarna_box_bottom_content_loader').fadeOut('fast', function () {
                        klarna.address_busy = false;
                    });
                }
                klarna.address_busy = false;
            }
        });
    }
}

function showBlueBaloon (x, y, text)
{
    jQuery('#klarna_blue_baloon_content div').html(text);

    var top = (y - jQuery('#klarna_blue_baloon').height())-5;

    var left = (x - (jQuery('#klarna_blue_baloon').width()/2)+5);

    jQuery('#klarna_blue_baloon').css({"left": left, "top": top});

    jQuery('#klarna_blue_baloon').show();
}

function hideBlueBaloon ()
{
    jQuery('#klarna_blue_baloon').hide();
}

/**
 * This function is only available for swedish social security numbers
 */
function validateSocialSecurity (vPNO)
{
    if (typeof vPNO == 'undefined')
        return false;

    return vPNO.match(/^([1-9]{2})?[0-9]{6}[-\+]?[0-9]{4}$/)
}

function hideBaloon (callback)
{
    if (jQuery('#klarna_baloon').is(":visible"))
    {
        jQuery('#klarna_baloon').fadeOut('fast', function (){
            if( callback ) callback();

            return true;
        });
    }
    else {
        if( callback ) callback();
        return true;
    }
}

function setBaloonInPosition (field, red_baloon)
{
    hideBaloon(function (){
        var position = field.offset();
        var name = field.attr('name');
        var value = field.attr('alt');

        if (!value && !red_baloon)
        {
            return false;
        }

        if (!red_baloon)
        {
            jQuery('#klarna_baloon_content div').html(value);

            var top = position.top - jQuery('#klarna_baloon').height();
            if (top < 0) top = 10;
            position.top = top;

            var left = (position.left + field.width()) - (jQuery('#klarna_baloon').width() - 50);

            position.left = left;

            jQuery('#klarna_baloon').css(position);

            jQuery('#klarna_baloon').fadeIn('fast');
        }
        else {
            var top = position.top - jQuery('#klarna_red_baloon').height();
            if (top < 0) top = 10;
            position.top = top;

            var left = (position.left + field.width()) - (jQuery('#klarna_red_baloon').width() - 50);

            position.left = left;

            jQuery('#klarna_red_baloon').css(position);

            jQuery('#klarna_red_baloon').fadeIn('fast');
        }
    });
}

function saveDates(replaceBox) {
    if (typeof klarna.part != 'undefined') {
        klarna.part.select_bday = jQuery(replaceBox).find('#selectBox_part_bday').val();
        klarna.part.select_bmonth = jQuery(replaceBox).find('#selectBox_part_bmonth').val();
        klarna.part.select_byear = jQuery(replaceBox).find('#selectBox_part_year').val();
    }

    if (typeof klarna.spec != 'undefined') {
        klarna.spec.select_bday = jQuery(replaceBox).find('#selectBox_spec_bday').val();
        klarna.spec.select_bmonth = jQuery(replaceBox).find('#selectBox_spec_bmonth').val();
        klarna.spec.select_byear = jQuery(replaceBox).find('#selectBox_spec_year').val();
    }

    if (typeof klarna.invoice != 'undefined') {
        klarna.invoice.select_bday = jQuery(replaceBox).find('#selectBox_bday').val();
        klarna.invoice.select_bmonth = jQuery(replaceBox).find('#selectBox_bmonth').val();
        klarna.invoice.select_byear = jQuery(replaceBox).find('#selectBox_year').val();
    }
}

function changeLanguage (replaceBox, params, newIso, country, type)
{
    var paramString = "";
    var valueString = "";

    data = {
        action: 'languagepack',
        subAction: 'klarna_box',
        type: type,
        newIso: newIso,
        country: country,
        sum: klarna.global.sum,
        fee: (typeof klarna.invoice != 'undefined' ? klarna.invoice.fee : ''),
        flag: klarna.global.flag
    }

    // include current field values in request so that the values can be used
    // in the translation
    for (var attr in params) {
        data['params[' + attr + ']'] = params[attr];
        var inputValue = jQuery(replaceBox).find('input[name="' + params[attr] + '"]').val();
        if (typeof inputValue != "undefined") {
            data['values[' + attr + ']'] = inputValue;
        }
    }

    saveDates(replaceBox);
    jQuery.ajax({
        type: "GET",
        url: klarna.global.ajax_path,
        data: data,
        success: function(response){
            jQuery(response).find('error').each(function() {
                var msg = jQuery(this).find('message').text();
                var code = jQuery(this).find('code').text();
                var type = jQuery(this).find('type').text();
                replaceBox.find('.klarna_box_top_flag_list').fadeOut('slow', function () {
                    changeLanguage_busy = false;
                });
                klarna.errorHandler.show(replaceBox, msg, code, type);
            });

            if (jQuery(response).find('.klarna_box').length > 0)
            {
                replaceBox.find('.klarna_box').remove();
                replaceBox.append(jQuery(response).find('.klarna_box'));
                if (type == "invoice")
                {
                    if (newIso != klarna.invoice.language)
                        replaceBox.find('.klarna_box_bottom_languageInfo').fadeIn('slow', function () {
                            changeLanguage_busy = false;
                        });
                    else
                        replaceBox.find('.klarna_box_bottom_languageInfo').fadeOut('slow', function () {
                            changeLanguage_busy = false;
                        });
                    pnoUpdated(jQuery('input[name="'+klarna.invoice.params.socialNumber+'"]'), false);
                }
                if (type == "part")
                {
                    if(newIso != klarna.part.language)
                        replaceBox.find('.klarna_box_bottom_languageInfo').fadeIn('slow', function () {
                            changeLanguage_busy = false;
                        });
                    else
                        replaceBox.find('.klarna_box_bottom_languageInfo').fadeOut('slow', function () {
                            changeLanguage_busy = false;
                        });
                    pnoUpdated(jQuery('input[name="'+klarna.part.params.socialNumber+'"]'), false);
                }

                if (type == "spec")
                {
                    if(newIso != klarna.spec.language)
                        replaceBox.find('.klarna_box_bottom_languageInfo').fadeIn('slow', function () {
                            changeLanguage_busy = false;
                        });
                    else
                        replaceBox.find('.klarna_box_bottom_languageInfo').fadeOut('slow', function () {
                            changeLanguage_busy = false;
                        });
                    pnoUpdated(jQuery('input[name="'+klarna.spec.params.socialNumber+'"]'), false);
                }
                initPaymentOptions(replaceBox);
                initDateSelectors(replaceBox);
            }
        }
    });
}
