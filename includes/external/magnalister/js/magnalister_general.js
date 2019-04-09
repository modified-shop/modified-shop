/**
 * 888888ba                 dP  .88888.                    dP                
 * 88    `8b                88 d8'   `88                   88                
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b. 
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88 
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88 
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P' 
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * $Id$
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

function in_array (needle, haystack, argStrict) {
	// Checks if the given value exists in the array  
    // 
    // version: 1004.2314
    // discuss at: http://phpjs.org/functions/in_array
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: vlado houba
    // +   input by: Billy
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // *     example 1: in_array('van', ['Kevin', 'van', 'Zonneveld']);
    // *     returns 1: true
    // *     example 2: in_array('vlado', {0: 'Kevin', vlado: 'van', 1: 'Zonneveld'});
    // *     returns 2: false
    // *     example 3: in_array(1, ['1', '2', '3']);
    // *     returns 3: true
    // *     example 3: in_array(1, ['1', '2', '3'], false);
    // *     returns 3: true
    // *     example 4: in_array(1, ['1', '2', '3'], true);
    // *     returns 4: false
    var key = '', strict = !!argStrict; 
    if (strict) {
        for (key in haystack) {
            if (haystack[key] === needle) {
                return true;
            }
        }
    } else {
        for (key in haystack) {
            if (haystack[key] == needle) {
            	return true;
            }
        }
    }
    return false;
}

/* From http://phpjs.org */
function function_exists (function_name) {
    // Checks if the function exists  
    // 
    // version: 1004.1212
    // discuss at: http://phpjs.org/functions/function_exists    
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Steve Clay
    // +   improved by: Legaev Andrey
    // *     example 1: function_exists('isFinite');
    // *     returns 1: true
    if (typeof function_name == 'string'){
        return (typeof this.window[function_name] == 'function');
    } else{
        return (function_name instanceof Function);
    }
}

function is_object (mixed_var) {
    // Returns true if variable is an object  
    // 
    // version: 1008.1718
    // discuss at: http://phpjs.org/functions/is_object
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Legaev Andrey
    // +   improved by: Michael White (http://getsprink.com)
    // *     example 1: is_object('23');
    // *     returns 1: false
    // *     example 2: is_object({foo: 'bar'});
    // *     returns 2: true
    // *     example 3: is_object(null);
    // *     returns 3: false
    if (mixed_var instanceof Array) {
    	return false;
    } else {
        return (mixed_var !== null) && (typeof( mixed_var ) == 'object');
    }
}

function strformat() {
	if (arguments.length <= 0) {
		return '';
	}
	var formatted = arguments[0];
	if (arguments.length == 1) {
		return formatted;
	}
    for (i = 1; i < arguments.length; ++i) {
        formatted = formatted.replace("{" + i + "}", arguments[i]);
    }
    return formatted;
}

function number_format(number, decimals, dec_point, thousands_sep) {
    // http://kevin.vanzonneveld.net
    // +   original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +     bugfix by: Michael White (http://getsprink.com)
    // +     bugfix by: Benjamin Lupton
    // +     bugfix by: Allan Jensen (http://www.winternet.no)
    // +    revised by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
    // +     bugfix by: Howard Yeend
    // +    revised by: Luke Smith (http://lucassmith.name)
    // +     bugfix by: Diogo Resende
    // +     bugfix by: Rival
    // +      input by: Kheang Hok Chin (http://www.distantia.ca/)
    // +   improved by: davook
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Jay Klehr
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Amir Habibi (http://www.residence-mixte.com/)
    // +     bugfix by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: Theriault
    // +      input by: Amirouche
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // *     example 1: number_format(1234.56);
    // *     returns 1: '1,235'
    // *     example 2: number_format(1234.56, 2, ',', ' ');
    // *     returns 2: '1 234,56'
    // *     example 3: number_format(1234.5678, 2, '.', '');
    // *     returns 3: '1234.57'
    // *     example 4: number_format(67, 2, ',', '.');
    // *     returns 4: '67,00'
    // *     example 5: number_format(1000);
    // *     returns 5: '1,000'
    // *     example 6: number_format(67.311, 2);
    // *     returns 6: '67.31'
    // *     example 7: number_format(1000.55, 1);
    // *     returns 7: '1,000.6'
    // *     example 8: number_format(67000, 5, ',', '.');
    // *     returns 8: '67.000,00000'
    // *     example 9: number_format(0.9, 0);
    // *     returns 9: '1'
    // *    example 10: number_format('1.20', 2);
    // *    returns 10: '1.20'
    // *    example 11: number_format('1.20', 4);
    // *    returns 11: '1.2000'
    // *    example 12: number_format('1.2000', 3);
    // *    returns 12: '1.200'
    // *    example 13: number_format('1 000,50', 2, '.', ' ');
    // *    returns 13: '100 050.00'
    number = (number+'').replace(',', '').replace(' ', '');
    var n = !isFinite(+number) ? 0 : +number, 
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}

function escape_string_for_regex(str) {
	if (typeof(str) != 'string') {
		return '';
	}
	return str.replace(/\//, '\\/')
	          .replace(/\^/, '\\^')
	          .replace(/\./, '\\.')
	          .replace(/\$/, '\\$')
	          .replace(/\|/, '\\|')
	          .replace(/\(/, '\\(')
	          .replace(/\)/, '\\)')
	          .replace(/\[/, '\\[')
	          .replace(/\]/, '\\]')
	          .replace(/\*/, '\\*')
	          .replace(/\+/, '\\+')
	          .replace(/\?/, '\\?')
	          .replace(/\{/, '\\{')
	          .replace(/\}/, '\\}')
	          .replace(/\,/, '\\,');
}

function convertPriceToFloat(price) {
	r = new RegExp('^([0-9\.,]*)$');
	if (!r.test(price)) {
		return -1
	}
	re = new RegExp('([\.,]{1,2}[0-9]{1,2})$');
    frac = price.match(re);
	if (frac != null) {
	    frac = frac[0];
	    price = price.substr(0, price.length - frac.length);
	} else {
	    frac = "0";
	}
	price = price.replace(".", "").replace(",", "")+"."+frac.replace(".", "").replace(",", "");
	return parseFloat(price);
}

function formatPriceWoCur(price, format) {
	return number_format(price, format[0], format[1], format[2]);
}

function str_replace (search, replace, subject, count) {
    // http://kevin.vanzonneveld.net
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Gabriel Paderni
    // +   improved by: Philip Peterson
    // +   improved by: Simon Willison (http://simonwillison.net)
    // +    revised by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
    // +   bugfixed by: Anton Ongson
    // +      input by: Onno Marsman
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +    tweaked by: Onno Marsman
    // +      input by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   input by: Oleg Eremeev
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Oleg Eremeev
    // %          note 1: The count parameter must be passed as a string in order
    // %          note 1:  to find a global variable in which the result will be given
    // *     example 1: str_replace(' ', '.', 'Kevin van Zonneveld');
    // *     returns 1: 'Kevin.van.Zonneveld'
    // *     example 2: str_replace(['{name}', 'l'], ['hello', 'm'], '{name}, lars');
    // *     returns 2: 'hemmo, mars'

    var i = 0, j = 0, temp = '', repl = '', sl = 0, fl = 0,
            f = [].concat(search),
            r = [].concat(replace),
            s = subject,
            ra = r instanceof Array, sa = s instanceof Array;
    s = [].concat(s);
    if (count) {
        this.window[count] = 0;
    }

    for (i=0, sl=s.length; i < sl; i++) {
        if (s[i] === '') {
            continue;
        }
        for (j=0, fl=f.length; j < fl; j++) {
            temp = s[i]+'';
            repl = ra ? (r[j] !== undefined ? r[j] : '') : r[0];
            s[i] = (temp).split(f[j]).join(repl);
            if (count && s[i] !== temp) {
                this.window[count] += (temp.length-s[i].length)/f[j].length;}
        }
    }
    return sa ? s : s[0];
}

function print_m(array) {
	var pad_char = '\t',
		pad_val = 1,
		getFuncName = function (fn) {
			var name = (/\W*function\s+([\w\$]+)\s*\(/).exec(fn);
			if (!name) {
				return "'', // function [anonymous]";
			}
			return "'', // function "+name[1];
		},
		repeat_char = function (len, pad_char) {
			var str = '';
			for (var i = 0; i < len; i++) {
				str += pad_char;
			}
			return str;
		},
		formatArray = function (obj, cur_depth, pad_val, pad_char) {
			var base_pad = repeat_char(pad_val * cur_depth, pad_char),
				thick_pad = repeat_char(pad_val * (cur_depth + 1), pad_char),
				str = '';
			if (    (typeof obj === 'object') && (obj !== null) && obj.constructor
			     && (getFuncName(obj.constructor) !== 'PHPJS_Resource')
			) {
				str += 'array (\n';
				for (var key in obj) {
					str += thick_pad + "'" + key + "' => " + formatArray(obj[key], cur_depth + 1, pad_val, pad_char) + ',\n';
				}
				str += base_pad + ')';
			} else if (obj === null || obj === undefined) {
				str = 'null';
			} else {
				str = "'"+obj.toString()+"'";
			}
			return str;
		};
	return formatArray(array, 0, pad_val, pad_char);
}

$(document).ready(function() {
	/**
	 * hide all related inputs of select option === lump
	 */
	$('td.input > select option[value="__ml_lump"], td.input > select option[value="lump"], td.input > select option[value="stocksub"]').closest("select").on("change", function() {
		var self = $(this);
		if ($.inArray(self.val(), ["__ml_lump", "lump", "stocksub"]) >= 0) {
			self.closest("td").find(" > * ").not(self).show();
		} else {
			self.closest("td").find(" > * ").not(self).hide();
		}
	}).trigger("change");
});