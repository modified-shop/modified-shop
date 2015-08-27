/*-----------------------
  jquery.attribute.js Vers. 1.30
  (c) 2013 by noRiddle - www.revilonetz.de
  (c) 2013-15 by web28 - www.rpa-com.de
-------------------------*/

var debug = false;

var flag  = true;

$(document).ready(function($) {
    $(".attributes").each(function() {
       $(this).find('input[type=text],input[type="checkbox"], select').attr("disabled", true);
    });
    
    $('.cbx_optval').click(function(){
        en_disabled($(this));
    });
    
    $('.select_all').click(function()
    {
        if (debug) console.log("select_all: "+ $(this).attr('name') +  ' ' + $(this).val() + ' ' + $(this).is(':checked'));
        flag  = false;
        var tableBody = $('#attrtable-'+ $(this).val()).find("tbody");
        var checkboxes = tableBody.find('.cbx_optval');
        var check = $(this).is(':checked');
        checkboxes.each(function() {
            $(this).attr("checked", check);
            en_disabled($(this));
        });
    });
    
    $('.button_save').show();
    $('input[name="button_submit"]').hide();
    //$('.attributes-odd,.attributes-even,.downloads').hide(); //change to css

    var dthr = $('.dataTableHeadingRow');
    dthr.css('cursor', 'pointer');
    
    dthr.click(function() {
        var oid = $(this).attr('id');
        unfold(oid);
    });
    
    var get_options_id = $('input[type="hidden"][name="get_options_id"]').attr('value');
    if (get_options_id != null) {
         if (debug) console.log('options_ids: '+ get_options_id);
         var optionsArray = get_options_id.split(',');
         $.each(optionsArray, function(index, value) {
              if (debug) console.log(index + ': ' + value);
              unfold(value);
         });
    }

    $('.button_save').click(function() {
        $('input[type="hidden"][name="get_options_id"]').remove();
        $('form[name="SUBMIT_ATTRIBUTES"]').submit();
    });

});

function unfold(oid)
{
    var elemID = '#'+oid;
    if (debug) console.log('oid: '+ oid);
    var rows_oid = $('.'+oid);
    var input_types = $('input[type=text],select');
    var input_fields = rows_oid.find(input_types);
    var checkboxes = rows_oid.find('.cbx_optval');
    var cbx_selall = $(elemID).find('input.select_all');

    if (flag) {
        //Ein/Ausklappen
        rows_oid.toggle();
        if ($(elemID).hasClass("att-red")) {
            $(elemID).removeClass('att-red').addClass('att-green');
            if (debug) console.log('className close: '+ $(elemID).attr('class'));
            cbx_selall.attr('disabled',true);
            cbx_selall.hide();
            $('input[type="hidden"][value="' + oid + '"]').remove();
            $(input_fields).attr('disabled',true);
            $(checkboxes).attr('disabled',true);
            //rows_oid.show();
        } else {
            $(elemID).removeClass('att-green').addClass('att-red');
            if (debug) console.log('className open: '+ $(elemID).attr('class'));
            cbx_selall.attr('disabled',false);
            cbx_selall.show();
            $('form[name="SUBMIT_ATTRIBUTES"]').append('<input type="hidden" name="options_id[]" value="' + oid + '" />');
            $(checkboxes).attr('disabled',false);
            checkboxes.each(function() {
                en_disabled($(this));
            });
            //rows_oid.show();
        }
    }
    flag = true;
}

function en_disabled(obj)
{
    obj.closest('tr').find('input[type=text], select').not('input[type=checkbox]').attr('disabled', !obj.is(':checked'));
    obj.closest('tr').find('.SumoSelect').toggleClass('disabled', !obj.is(':checked'));
    
    //download fields
    obj.closest('tr').next('tr').not('[class^=attributes]').find('input[type=text], select').attr('disabled', !obj.is(':checked'));
    obj.closest('tr').next('tr').not('[class^=attributes]').find('.SumoSelect').toggleClass('disabled', !obj.is(':checked'));
}