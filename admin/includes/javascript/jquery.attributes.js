/*-----------------------
  jquery.attribute.js Vers. 1.00
  (c) 2013 by noRiddle - www.revilonetz.de
  (c) 2013 by web28 - www.rpa-com.de
-------------------------*/

$(document).ready(function($) {
    var table = $("#attributes");
    table.find('input[type=text],input.cb[type="checkbox"], select').each(function() {
      $(this).attr("disabled", "disabled");
    });
    $('.button_save').show();
    $('input[name="button_submit"]').hide();

    $('input.cb[type="checkbox"]').parent().parent().hide(); //checkboxes
    $('select.dl').parent().parent().hide(); //select downloads
    var flag  = true;
    var $bsave = $('.button_save');

    var $dthr = $('.dataTableHeadingRow');
    $dthr.css('cursor', 'pointer');
    
    $dthr.click(function() {
        //alert ($(this).attr('id'));
        var input_n_ch = $('input.cb[type="checkbox"],select.dl');
        var ch_this = $(this).nextUntil('.dataTableHeadingRow').find(input_n_ch);
        var input = ch_this.parent().parent();
        var input_dl = $('.downloads');
        var $className = $(this).attr('class');
        var input_fields = $('input,select');
        ch_this = $(this).nextUntil('.dataTableHeadingRow').find(input_fields);
        //check / uncheck all
        var ckb_all = $(this).find('input.select_all');
        ckb_all.click(function() {
           flag = false;
           var id = ckb_all.attr('value');
           var checkboxes = $('input.check_'+id);
           var is_check = ckb_all.is(':checked');
           checkboxes.each(function() {
              if (is_check) {
                $(this).attr("checked", "checked");
              } else {
                $(this).removeAttr("checked");
              }
           });
        });
        if (flag) {
            input.toggle();
            $(this).find('input.select_all').click(function(){select_all()});
            if ($className == 'dataTableHeadingRow att-green' || $className == 'dataTableHeadingRow') {
                $(this).removeClass('att-green').addClass('att-red');
                $(this).find('td').addClass('active');
                ckb_all.show();
                $('form[name="SUBMIT_ATTRIBUTES"]').append('<input type="hidden" name="options_id[]" value="' + $(this).attr('id') + '" />');
                $(ch_this).removeAttr('disabled');
            } else if ($className == 'dataTableHeadingRow att-red') {
                $(this).removeClass('att-red').addClass('att-green');
                $(this).find('input.select_all').hide();
                $('input[type="hidden"][value="' + $(this).attr('id') + '"]').remove();
                $(this).find('td').removeClass('active');
                $(ch_this).attr('disabled','disabled');
            }
        }
        flag = true;
    });

    $('.button_save').click(function() {
        var input_n_ch = $('input[type="checkbox"]').not(':checked');
        var input = input_n_ch.parent().parent();
        table.hide();
        input.remove();
        //$(input).find('input,select').attr('disabled','disabled');
        //return;
        $('form[name="SUBMIT_ATTRIBUTES"]').submit();
        //$(input).removeAttr('disabled');
    });
    
    //$('form[name="SUBMIT_ATTRIBUTES"]').submit(function() {
      //alert('Form is submitting');
      //return false;
    //});
});