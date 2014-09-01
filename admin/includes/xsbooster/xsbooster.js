//
// Copyright (c) 2009 xt:booster Ltd.
// http://www.xsbooster.com
//
// Licensed under GNU/GPL

function confirmThis(link, text) { return confirm(text); }
function isdefined( variable) { return (typeof(window[variable]) == "undefined")?  false: true; }

var xtb_innerHeight=0;
var xtb_pageYOffset=0;

function xtb_dimensions() {
	if(Prototype.Browser.IE) {
		xtb_innerHeight = document.body.clientHeight;
		xtb_pageYOffset = document.body.scrollLeft;
	} else {
		xtb_innerHeight = window.innerHeight;
		xtb_pageYOffset = window.pageYOffset;
	}
}

if(isdefined("jQuery")) { jQuery.noConflict(); }

// convert a named array (object) to xml
function xmlize(r){
        if('undefined'==typeof(r)) return false;
        var rr=new Object;
        var rrstack=new Object;
        rrstack[0]=r;
        rr=rrstack[0];
        var mykeys=new Object;
        mykeys[0]=Object.keys(r);
        var istack=new Array;
        istack[0]=0;
        var i=istack[0];
        var current_depth=0;
        var myresult='';
        while(('undefined'!=typeof(rr[mykeys[current_depth][i]])) || current_depth>0)
        {
                //keine datentragenden blaetter mehr am ast, tag abschliessen
                if(   ('undefined'==typeof(rr[mykeys[current_depth][i]]))
	           ||(('each'==mykeys[current_depth][i])
                   || ('prototype'==mykeys[current_depth][i])))
                {
                        --current_depth;
                        i=istack[current_depth];
                        myresult += '</'+mykeys[current_depth][i]+'>';
                        rr=rrstack[current_depth];
                        i++;
                        continue;
                }
                //tag oeffnen
                myresult += '<'+mykeys[current_depth][i]+'>';
                if(  (Object.isString(rr[mykeys[current_depth][i]]))
                   ||(Object.isNumber(rr[mykeys[current_depth][i]])))
                        {
                        //blatt
                        myresult += rr[mykeys[current_depth][i]];
                        myresult += '</'+mykeys[current_depth][i]+'>';
                        i++;
                        }
                else
                        {
                        //ast, heruntergehen
                         mykeys[current_depth+1]=new Object;
                         mykeys[current_depth+1]=Object.keys(rr[mykeys[current_depth][i]]);
                         rrstack[current_depth+1]=rr[mykeys[current_depth][i]];
                         rr=rrstack[current_depth+1];
                         istack[current_depth]=i;
                         current_depth++;
                         i = 0;
                        }
        }
        return myresult;
}

// convert a xml string of the form <0>item0</0><1>item1</1> etc. to an array
function simpleXmlToArray(r)
{
    var result = new Array;
    var current_part = '';
    var i=0;
    var pos=0;
    while((pos = r.search('<'+i+'>')) != -1)
    {
      r = r.slice(pos);
      current_part = r.slice(0, endpos=r.search('</'+i+'>'));
      result[i] = current_part.slice(current_part.indexOf('>')+1,endpos);
      i++;
    }
    return result; 
}

// get the instance of FCKeditor
function FCKeditor_OnComplete( editorInstance )
{
   articleDescriptionEditor = FCKeditorAPI.GetInstance( editorInstance.Name );
}

var callbacks = 0;
var callback_errors = 0;

var xsb = {
	validate: function(f)
	{
		if($('TYPE').value=='') { alert(TXT_PLEASE_CHOOSE_AUCTION_TYPE); return false; }
		if(($('TYPE').value=='Chinese')&&($('QUANTITY').value!='1')) { alert(TXT_ONE_UNIT_PER_CHINESE_AUCTION); return false; }
		if(($('TYPE').value=='Dutch')&&($('QUANTITY').value=='1')) { alert(TXT_AT_LEAST_TWO_UNITS_PER_DUTCH_AUCTION); return false; }
		if($('CAT_PRIMARY').value=='') { alert(TXT_PLEASE_CHOOSE_PRIMARY_CATEGORY); return false; }
		if($('STARTPRICE')) {
			if($('STARTPRICE').value=='') { 
				alert(TXT_PLEASE_SET_START_PRICE); return false;
			} else {
				if($('STARTPRICE').value.indexOf(',')!=-1) {
					alert(TXT_PLEASE_USE_POINT_NOT_COMMA); return false;
				}
			}
		}
	
		if($('SHIPPINGCOSTS').value=='') { 
			alert(TXT_PLEASE_SET_SHIPPING_COSTS); return false;
		} else {
			if($('SHIPPINGCOSTS').value.indexOf(',')!=-1) {
				alert(TXT_PLEASE_USE_POINT_NOT_COMMA); return false;
			}
			if($('SHIPPINGSERVICEADDITIONALCOST').value.indexOf(',')!=-1) {
				alert(TXT_PLEASE_USE_POINT_NOT_COMMA); return false;
			}
		}
	
		if($('SHIPPINGCOSTS1').value!='') { 
			if($('SHIPPINGCOSTS1').value.indexOf(',')!=-1) {
				alert(TXT_PLEASE_USE_POINT_NOT_COMMA); return false;
			}
			if($('SHIPPINGSERVICEADDITIONALCOST1').value.indexOf(',')!=-1) {
				alert(TXT_PLEASE_USE_POINT_NOT_COMMA); return false;
			}
		}
	
		if($('SHIPPINGCOSTS2').value!='') { 
			if($('SHIPPINGCOSTS2').value.indexOf(',')!=-1) {
				alert(TXT_PLEASE_USE_POINT_NOT_COMMA); return false;
			}
			if($('SHIPPINGSERVICEADDITIONALCOST2').value.indexOf(',')!=-1) {
				alert(TXT_PLEASE_USE_POINT_NOT_COMMA); return false;
			}
		}
	
		if($('SHIPPINGCOSTS3').value!='') { 
			if($('SHIPPINGCOSTS3').value.indexOf(',')!=-1) {
				alert(TXT_PLEASE_USE_POINT_NOT_COMMA); return false;
			}
			if($('SHIPPINGSERVICEADDITIONALCOST3').value.indexOf(',')!=-1) {
				alert(TXT_PLEASE_USE_POINT_NOT_COMMA); return false;
			}
		}
	
		if($('SHIPPINGCOSTS4').value!='') { 
			if($('SHIPPINGCOSTS4').value.indexOf(',')!=-1) {
				alert(TXT_PLEASE_USE_POINT_NOT_COMMA); return false;
			}
			if($('SHIPPINGSERVICEADDITIONALCOST4').value.indexOf(',')!=-1) {
				alert(TXT_PLEASE_USE_POINT_NOT_COMMA); return false;
			}
		}
	
		if($('SHIPPINGCOSTS5').value!='') { 
			if($('SHIPPINGCOSTS5').value.indexOf(',')!=-1) {
				alert(TXT_PLEASE_USE_POINT_NOT_COMMA); return false;
			}
			if($('SHIPPINGSERVICEADDITIONALCOST5').value.indexOf(',')!=-1) {
				alert(TXT_PLEASE_USE_POINT_NOT_COMMA); return false;
			}
		}
		
		return true;
	},
	
	callback: function(response,requests)
	{
		if(response.search(/SUCCESS/)==-1) {
			$('status').style.color="orange";
			callback_errors++;
		} else
			callbacks++;

		p = (Math.round(((callbacks+callback_errors)*10000)/requests.length))/100;
		$('status').update((callbacks+callback_errors)+" "+TXT_OF+" "+requests.length+" "+TXT_AUCTIONS_SUBMITTED+" "+" ("+p+"%) - ("+callback_errors+" "+TXT_FAILED+")");
		if(p==100)
		{
			if(callback_errors==0)
			{
				$('status').update(TXT_ALL_AUCTIONS_SUBMITTED);
				$('moment').update(TXT_CONGRATULATIONS);
			}
			else if(callbacks==0)
			{
				$('status').style.color="red";
				$('status').update(TXT_ERROR_NO_AUCTIONS_SUBMITTED);
				$('moment').update(TXT_WRONG_DATA+"!");
			}
			else
			{
				$('status').style.color="orange";
				$('status').update(TXT_ONLY+" "+callbacks+" "+TXT_OF+" "+(callbacks+callback_errors)+" "+TXT_AUCTIONS_SUBMITTED+"!");
				$('moment').update(TXT_WARNING_NOT_ALL_AUCTIONS_SUBMITTED+"!");
			}
		}
	},

	putall: function(requests)
	{
		$('content_ajx_in').update("");
		window.scrollTo(0,0);
		callbacks = 0;
		callback_errors = 0;
		$('moment').update(TXT_BE_PATIENT_WHILE_SUBMITTING_AUCTIONS);
		$('status').update(TXT_ZERO_OF+' '+requests.length+' '+TXT_AUCTIONS_SUBMITTED+' (0%)');
		for(var i=0;i<requests.length;i++)
		{
			new Ajax.Updater("content_ajx_in", "xtbooster.php", { 
				method: 'post',
				onSuccess: function(transport) { 
					xsb.callback(transport.responseText,requests);
				},
				parameters: {
					request: requests[i],
					xtb_module: 'add_ajx'
				},
				insertion: Insertion.Bottom
			} );
		}
	},

	post: function(t) {
	
		var inputs = $$("#"+t.id+" input");
		var textareas = $$("#"+t.id+" textarea");
		var selects = $$("#"+t.id+" select");
		var EBAY_SITE = $('EBAY_SITE').value;
		var r = new Object();
		var search = /add\[(\w.+)\]/;

	if(!this.validate(t)) return false;
		for(var i=0;i<inputs.length;i++) {
  			search.exec(inputs[i].name);
  			if(RegExp.$1=='')       continue;
			if(inputs[i].name=='')  continue;
			if(inputs[i].value=='') continue;
			switch(inputs[i].type){
			case('text'):
			case('hidden'):
			if(inputs[i].name.slice(4,14)=='ATTRIBUTES')
			// ATTRIBUTES sehen so aus: add[ATTRIBUTES1][<nummer>]=<wert>
			{
				if(typeof(r[inputs[i].name.slice(4,15)]) == 'undefined')
				{ r[inputs[i].name.slice(4,15)] = new Object; }
				r[inputs[i].name.slice(4,15)][inputs[i].name.slice(17,inputs[i].name.length-1)] = inputs[i].value;
			}
			else
				{ r[RegExp.$1]=inputs[i].value; break; }
			// checkboxes in Arrays einlesen
			case('checkbox'):
				{ if(inputs[i].checked)
					{ if(r[RegExp.$1])
					  { r[RegExp.$1][r[RegExp.$1].length] = inputs[i].value; }
					  else
					  { r[RegExp.$1]=new Array; r[RegExp.$1][0]=inputs[i].value; }
					}
				  break; }
			default:  break;
			}
		}
		for(i=0;i<selects.length;i++) {
  			search.exec(selects[i].name);
  			if(RegExp.$1=='')        continue;
			if(selects[i].name=='')  continue;
			if(selects[i].value=='') continue;
			if(selects[i].name.slice(4,14)=='ATTRIBUTES')
			// ATTRIBUTES sehen so aus: add[ATTRIBUTES1][<nummer>]=<wert>
			{
				if(typeof(r[selects[i].name.slice(4,15)]) == 'undefined')
				{ r[selects[i].name.slice(4,15)] = new Object; }
				r[selects[i].name.slice(4,15)][selects[i].name.slice(17,selects[i].name.length-1)] = selects[i].value;
			}
			// PAYMENTMETHODS: Multiselect-Feld (das einzige)
			else if(selects[i].name=='add[PAYMENTMETHODS][]')
			{
				r['PAYMENTMETHODS'] = $('PAYMENTMETHODS').getValue();
				if('string' == typeof(r['PAYMENTMETHODS']))
				{ r['PAYMENTMETHODS'][0]=r['PAYMENTMETHODS']; }
			}
			else
			{ r[RegExp.$1]=selects[i].value; }
		}
		// FCKeditor:
		if(typeof(articleDescriptionEditor) != 'undefined')
		{ r['DESCRIPTION'] = articleDescriptionEditor.GetHTML(); }
		// Felder die nicht als add[..] definiert sind:
		r['TYPE'] = $('TYPE').value;
		r['PRODUCT_ID'] = $('current_product_id').value;
		r['multi_xtb'] = $('multi_xtb').value;
	
		$('content').hide();
		$('content_ajx').show();
		
		new Ajax.Request("xtbooster.php", { 
				method: 'post',
				onComplete: function(tx) {
					var requests = tx.responseText;
					requests = simpleXmlToArray(requests);
					Effect.Pulsate('moment', { pulses: 3, duration: 7 });
					xsb.putall(requests);
				},
				parameters: {
					xtb_module: 'add_base',
					XTB_VERSION: XTB_VERSION,
					EBAY_SITE: EBAY_SITE,
					add:xmlize(r)
				}
			}
		);
	},
	
	back: function() {
		$('content_ajx').hide();
		$('content').show();
		window.scrollTo(0,0);
	}
}
