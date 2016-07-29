var settings;
    
function getItemsFee(e) {
    var dialogHtml = '<div id="infoDiagFee" class="dialog2" title="Information"></div>';

    $('#infoDiagFee').remove();                           
    $('html').append(dialogHtml);
    $('#infoDiagFee').jDialog({
	buttons: [
	    {
		id: 'getarticlesfee-ok',
		text: settings.i18n.ok,
		click: function() {
		    $(this).dialog('close');
		    settings.addItems(e);
		}
	    },
	    {
		id: 'getarticlesfee-abort',
		text: settings.i18n.abort,
		click: function() {
		    $(this).dialog('close');
		}
	    }
	]
    });

    $('#infoDiagFee').html(settings.i18n.process);
    $('#getarticlesfee-ok').prop('disabled', true);
    $('#getarticlesfee-ok').css('visibility', 'hidden');

    var checkedNames = $('.datagrid tbody input:checkbox:checked').map(function() {
	return this.name;
    }).get();

    var formDataChecked = '';
    if (checkedNames.length !== 0) {
	formDataChecked = checkedNames.join('=true&') + '=true';
    }

    var uncheckedNames = $('.datagrid tbody input:checkbox:not(:checked)').map(function() {
	return this.name;
    }).get();

    var formDataUnchecked = '';
    if (uncheckedNames.length !== 0) {
	formDataUnchecked = '&' + uncheckedNames.join('=false&') + '=false';
    }

    if (formDataChecked === '') {
	formDataUnchecked = formDataUnchecked.substring(1);
    }

    var formData = formDataChecked + formDataUnchecked;

    $.ajax({
	url: $('form#summaryForm').attr('action') + '&where=' + settings.method + '&kind=ajax',
	type: 'POST',
	data: formData,
	success: function(result) {
	    var message = settings.message;
	    message = message.replace('{1}', result.totalfee);
	    message = message.replace('{2}', settings.currency);

	    if (result.status === 'ok') {
		$('#infoDiagFee').html(message);
		$('#getarticlesfee-ok').prop('disabled', false);
		$('#getarticlesfee-ok').css('visibility', '');
	    } else {
		$('#infoDiagFee').html(result.error);
	    }
	}
    });
}

$.fn.itemsFee = function(options) {
    var defaults = {
	mode: 'on',
	addItems: function() {
	    alert('Method addItems must be defined.');
	},
	method: 'getItemsFee',
	message: null,
	currency: null,
	i18n: {
	    ok: 'Ok',
	    abort: 'Abort'
	}
    };

    settings = $.extend({}, defaults, options);

    var e = this;

    if (settings.mode === 'on') {
	getItemsFee(e);
    } else {            
	settings.addItems(e);
    }
};