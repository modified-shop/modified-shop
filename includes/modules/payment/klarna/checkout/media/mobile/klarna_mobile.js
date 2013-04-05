var getCodeBusy = false;
var makePurchaseBusy = false;
var reservationNumber = null;

jQuery(document).ready(function () {
    jQuery('.klarnaMobile_boxInputField_left input').bind('keyup change click load', function (){
        showHidePlaceHolder(jQuery(this));
    });

    jQuery('.klarnaMobile_boxInputField_left_inputPlaceholder input').bind('keyup change click load', function (){
        showHidePlaceHolder(jQuery(this));
    });

    jQuery('.klarnaMobile_boxInputField_left input').bind('blur', function (){
        showHidePlaceHolder(jQuery(this));
        fillPlaceHolder(jQuery(this));
    });

    jQuery('.klarnaMobile_boxInputField_left_inputPlaceholder input').bind('blur', function (){
        showHidePlaceHolder(jQuery(this));
        fillPlaceHolder(jQuery(this));
    });

    jQuery('.klarnaMobile_boxInputField_right').click (function () {
        var id = jQuery(this).attr("id");

        if (id == "getCode")
        {
            getCode();
            jQuery('.klarnaMobile_boxInputField_right_send').css({"background-image":"url(klarna/mobile/default/loader.gif)"});
        }
        else if (id == "makePurchase")
        {
            makePurchase();
            jQuery('.klarnaMobile_boxInputField_right_buy').css({"background-image":"url(klarna/mobile/default/loader.gif)"});
        }
    });

    jQuery('.klarnaMobile_errorClose').click (function () {
        jQuery('#klarnaMobile_error').fadeOut('fast');
        jQuery('.klarnaMobile_error_Inner').fadeOut('fast');
    });
});

function getCode (callBack)
{
    jQuery.ajax({
        type: "GET",
        url: "klarnaMobile.php",
        data: "page=ajax&subAction=sendCode&phoneNumber="+jQuery('input[name=mobile_no]').val()+'&productId='+pId,
        success: function(xml){
            var statusCode = jQuery(xml).find('statusCode').text();
            var errorCode    = jQuery(xml).find('errorCode').text();

            if (statusCode < 0 || !IsNumeric(statusCode))
            {
                var text;

                if (IsNumeric(statusCode))
                    text = textToLink(jQuery(xml).find('message').text(), (errorCode == '2401'));
                else
                    text = textToLink(xml, (errorCode == '2401'));

                // Whoops, an error!
                jQuery('#klarnaMobile_errorText').html(text);

                jQuery('#klarnaMobile_error').css({'opacity': '0.0', "filter": "alpha(opacity=0)", "display":"block"});
                jQuery('#klarnaMobile_error').animate({'opacity': '0.7', "filter": "alpha(opacity=70)", "display":"block"}, 200, function () {
                    jQuery('.klarnaMobile_error_Inner').fadeIn('fast');
                });

                jQuery('.klarnaMobile_boxInputField_right_send').css({"background-image":"url(klarna/mobile/default/icon_send.png)"});
            }
            else {
                jQuery('input[name=mobile_code]').focus();
                jQuery('.klarnaMobile_boxInputField_right_send').css({"background-image":"url(klarna/mobile/default/done.png)"});

                reservationNumber = statusCode;
            }

            if(typeof callBack == 'function'){
                callBack.call();
            }
        }
    });
}

function makePurchase (callBack)
{
    jQuery.ajax({
        type: "GET",
        url: "klarnaMobile.php",
        data: "page=ajax&subAction=makePurchase&phoneNumber="+jQuery('input[name=mobile_no]').val()+'&productId='+pId+"&pinCode="+jQuery('input[name=mobile_code]').val()+'&reservationNumber='+reservationNumber,
        success: function(xml){
            var statusCode = jQuery(xml).find('statusCode').text();
            var redirectURL = jQuery(xml).find('redirectUrl').html();

            if (statusCode < 0 || !IsNumeric(statusCode))
            {
                // Whoops, an error!
                if (IsNumeric(statusCode))
                    jQuery('#klarnaMobile_errorText').html(jQuery(xml).find('message').text());
                else
                    jQuery('#klarnaMobile_errorText').html(xml);

                jQuery('#klarnaMobile_error').css({'opacity': '0.0', "filter": "alpha(opacity=0)", "display":"block"});
                jQuery('#klarnaMobile_error').animate({'opacity': '0.7', "filter": "alpha(opacity=70)", "display":"block"}, 200, function () {
                    jQuery('.klarnaMobile_error_Inner').fadeIn('fast');
                });

                jQuery('.klarnaMobile_boxInputField_right_buy').css({"background-image":"url(klarna/mobile/default/icon_buy.png)"});
            }
            else {
                jQuery('input[name=mobile_code]').focus();
                jQuery('.klarnaMobile_boxInputField_right_buy').css({"background-image":"url(klarna/mobile/default/done.png)"});

                if (typeof redirectURL != "undefined" && redirectURL != "")
                    window.location = redirectURL;
            }

            if(typeof callBack == 'function'){
                callBack.call();
            }
        }
    });
}

function closeErrorBox ()
{

}

function showHidePlaceHolder (field)
{
    var name = jQuery(field).attr("name");
    var ph = placeHolderText[name];

    if (typeof ph != 'undefined')
    {
        if (jQuery(field).val() == "")
        {
            jQuery(field).parent().attr("class", "klarnaMobile_boxInputField_left_inputPlaceholder");
        }
        else if (ph != jQuery(field).val())
        {
            jQuery(field).parent().attr("class", "klarnaMobile_boxInputField_left");
        }
        else {
            jQuery(field).parent().attr("class", "klarnaMobile_boxInputField_left_inputPlaceholder");
            jQuery(field).val("");
        }
    }
}

function fillPlaceHolder (field)
{
    var name = jQuery(field).attr("name");
    var ph = placeHolderText[name];

    if (typeof ph != 'undefined')
    {
        if (jQuery(field).val() =="")
        {
            jQuery(field).parent().attr("class", "klarnaMobile_boxInputField_left_inputPlaceholder");
            jQuery(field).val(ph);
        }
    }
}

function IsNumeric(input)
{
   return (input - 0) == input && input.length > 0;
}

function textToLink(text, regUrl)
{
    if( !text ) return text;

    text = text.replace(/((https?\:\/\/|ftp\:\/\/)|(www\.))(\S+)(\w{2,4})(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/gi,function(url){
        nice = url;
        if( url.match('^https?:\/\/') )
        {
            nice = nice.replace(/^https?:\/\//i,'')
        }
        else
            url = 'http://'+url;

        return '<a target="_blank" rel="nofollow" href="'+ url +'">'+ nice.replace(/^www./i,'') +'</a>';
    });

    text = text.replace(/(klarna.se)?/gi,function(url){
        return '<a target="_blank" rel="nofollow" href="'+(regUrl == true ? 'https://klarna.com/sv/privat/tjaenster/mobil/registrera' : 'http://www.klarna.se')+'">'+ url +'</a>';
    });

    return text;
}
