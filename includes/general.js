/* -----------------------------------------------------------------------------------------
   $Id:$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.js,v 1.3 2003/02/10); www.oscommerce.com
   (c) 2003	nextcommerce (general.js,v 1.3 2003/08/13); www.nextcommerce.org 
   (c) 2003 XT-Commerce (general.js, 2005/04/29);

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

function SetFocus(TargetFormName) {
  var target = 0;
  if (TargetFormName != "") {
    for (i=0; i<document.forms.length; i++) {
      if (document.forms[i].name == TargetFormName) {
        target = i;
        break;
      }
    }
  }

  var TargetForm = document.forms[target];
    
  for (i=0; i<TargetForm.length; i++) {
    if ( (TargetForm.elements[i].type != "image") && (TargetForm.elements[i].type != "hidden") && (TargetForm.elements[i].type != "reset") && (TargetForm.elements[i].type != "submit") ) {
      TargetForm.elements[i].focus();

      if ( (TargetForm.elements[i].type == "text") || (TargetForm.elements[i].type == "password") ) {
        TargetForm.elements[i].select();
      }

      break;
    }
  }
}

function RemoveFormatString(TargetElement, FormatString) {
  if (TargetElement.value == FormatString) {
    TargetElement.value = "";
  }

  TargetElement.select();
}

function CheckDateRange(from, to) {
  if (Date.parse(from.value) <= Date.parse(to.value)) {
    return true;
  } else {
    return false;
  }
}

function IsValidDate(DateToCheck, FormatString) {
  var strDateToCheck;
  var strDateToCheckArray;
  var strFormatArray;
  var strFormatString;
  var strDay;
  var strMonth;
  var strYear;
  var intday;
  var intMonth;
  var intYear;
  var intDateSeparatorIdx = -1;
  var intFormatSeparatorIdx = -1;
  var strSeparatorArray = new Array("-"," ","/",".");
  var strMonthArray = new Array("jan","feb","mar","apr","may","jun","jul","aug","sep","oct","nov","dec");
  var intDaysArray = new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

  strDateToCheck = DateToCheck.toLowerCase();
  strFormatString = FormatString.toLowerCase();
  
  if (strDateToCheck.length != strFormatString.length) {
    return false;
  }

  for (i=0; i<strSeparatorArray.length; i++) {
    if (strFormatString.indexOf(strSeparatorArray[i]) != -1) {
      intFormatSeparatorIdx = i;
      break;
    }
  }

  for (i=0; i<strSeparatorArray.length; i++) {
    if (strDateToCheck.indexOf(strSeparatorArray[i]) != -1) {
      intDateSeparatorIdx = i;
      break;
    }
  }

  if (intDateSeparatorIdx != intFormatSeparatorIdx) {
    return false;
  }

  if (intDateSeparatorIdx != -1) {
    strFormatArray = strFormatString.split(strSeparatorArray[intFormatSeparatorIdx]);
    if (strFormatArray.length != 3) {
      return false;
    }

    strDateToCheckArray = strDateToCheck.split(strSeparatorArray[intDateSeparatorIdx]);
    if (strDateToCheckArray.length != 3) {
      return false;
    }

    for (i=0; i<strFormatArray.length; i++) {
      if (strFormatArray[i] == 'mm' || strFormatArray[i] == 'mmm') {
        strMonth = strDateToCheckArray[i];
      }

      if (strFormatArray[i] == 'dd') {
        strDay = strDateToCheckArray[i];
      }

      if (strFormatArray[i] == 'yyyy') {
        strYear = strDateToCheckArray[i];
      }
    }
  } else {
    if (FormatString.length > 7) {
      if (strFormatString.indexOf('mmm') == -1) {
        strMonth = strDateToCheck.substring(strFormatString.indexOf('mm'), 2);
      } else {
        strMonth = strDateToCheck.substring(strFormatString.indexOf('mmm'), 3);
      }

      strDay = strDateToCheck.substring(strFormatString.indexOf('dd'), 2);
      strYear = strDateToCheck.substring(strFormatString.indexOf('yyyy'), 2);
    } else {
      return false;
    }
  }

  if (strYear.length != 4) {
    return false;
  }

  intday = parseInt(strDay, 10);
  if (isNaN(intday)) {
    return false;
  }
  if (intday < 1) {
    return false;
  }

  intMonth = parseInt(strMonth, 10);
  if (isNaN(intMonth)) {
    for (i=0; i<strMonthArray.length; i++) {
      if (strMonth == strMonthArray[i]) {
        intMonth = i+1;
        break;
      }
    }
    if (isNaN(intMonth)) {
      return false;
    }
  }
  if (intMonth > 12 || intMonth < 1) {
    return false;
  }

  intYear = parseInt(strYear, 10);
  if (isNaN(intYear)) {
    return false;
  }
  if (IsLeapYear(intYear) == true) {
    intDaysArray[1] = 29;
  }

  if (intday > intDaysArray[intMonth - 1]) {
    return false;
  }
  
  return true;
}

function IsLeapYear(intYear) {
  if (intYear % 100 == 0) {
    if (intYear % 400 == 0) {
      return true;
    }
  } else {
    if ((intYear % 4) == 0) {
      return true;
    }
  }

  return false;
}

function check_form() {
  var error_message = unescape("<?php echo xtc_js_lang(JS_ERROR); ?>");
  var error_found = false;
  var error_field;
  var keywords = document.getElementById("advanced_search").keywords.value;
  var pfrom = document.getElementById("advanced_search").pfrom.value;
  var pto = document.getElementById("advanced_search").pto.value;
  var pfrom_float;
  var pto_float;
  if ( (keywords == '' || keywords.length < 1) && (pfrom == '' || pfrom.length < 1) && (pto == '' || pto.length < 1) ) {
    error_message = error_message + unescape("<?php echo xtc_js_lang(JS_AT_LEAST_ONE_INPUT); ?>");
    error_field = document.getElementById("advanced_search").keywords;
    error_found = true;
  }
  if (pfrom.length > 0) {
    pfrom_float = parseFloat(pfrom);
    if (isNaN(pfrom_float)) {
      error_message = error_message + unescape("<?php echo xtc_js_lang(JS_PRICE_FROM_MUST_BE_NUM); ?>");
      error_field = document.getElementById("advanced_search").pfrom;
      error_found = true;
    }
  } else {
    pfrom_float = 0;
  }
  if (pto.length > 0) {
    pto_float = parseFloat(pto);
    if (isNaN(pto_float)) {
      error_message = error_message + unescape("<?php echo xtc_js_lang(JS_PRICE_TO_MUST_BE_NUM); ?>");
      error_field = document.getElementById("advanced_search").pto;
      error_found = true;
    }
  } else {
    pto_float = 0;
  }
  if ( (pfrom.length > 0) && (pto.length > 0) ) {
    if ( (!isNaN(pfrom_float)) && (!isNaN(pto_float)) && (pto_float < pfrom_float) ) {
      error_message = error_message + unescape("<?php echo xtc_js_lang(JS_PRICE_TO_LESS_THAN_PRICE_FROM); ?>");
      error_field = document.getElementById("advanced_search").pto;
      error_found = true;
    }
  }
  if (error_found == true) {
    alert(error_message);
    error_field.focus();
    return false;
  }
}
