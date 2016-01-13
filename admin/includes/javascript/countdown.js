$(document).ready(function(){
    $('[maxlength]').each(function(){
        var maxLength = parseInt($(this).attr('maxlength'));
        if(maxLength > 0){
            var countdownBox = $('<div style="font-size:11px; font-family:Verdana, Arial, sans-serif; color:#666;" />');
            $(this).after(countdownBox);
            $(this).keyup(function(){
                var t = maxLength-$(this).val().length;
                countdownBox.html(lang_chars_left+': '+t+' ('+lang_chars_max+' '+maxLength+')');
            });
            $(this).trigger('keyup');
        }
    });
});
