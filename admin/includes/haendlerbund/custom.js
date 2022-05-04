$(function () {
  // Trim white spaces from input
  $('#haendlerbund_key').change(function(){
    this.value = $.trim(this.value);
  });

  $('.wizard').smartWizard({
    selected: 0,  // Selected Step, 0 = first step
    keyNavigation: false, // Enable/Disable key navigation(left and right keys are used if enabled)
    enableAllSteps: false,  // Enable/Disable all steps on first load
    transitionEffect: 'slideleft', // Effect on navigation, none/fade/slide/slideleft
    contentURL: null, // specifying content url enables ajax content loading
    contentCache: false, // cache step contents, if false content is fetched always from ajax url
    cycleSteps: false, // cycle step navigation
    enableFinishButton: false, // makes finish button enabled always
    errorSteps: [],    // array of step numbers to highlighting as error steps
    labelNext: 'weiter', // label for Next button
    labelPrevious: 'zur&uuml;ck', // label for Previous button
    labelFinish: 'Konfiguration abschlie&szlig;en',  // label for Finish button
    // Events
    onLeaveStep: leaveAStepCallback, // triggers when leaving a step
    onShowStep: true,  // triggers when showing a step
    onFinish: onFinishCallback  // triggers when Finish button is clicked
  });

  function leaveAStepCallback(obj) {
    var step_num = obj.attr('rel'); // get the current step number
    return validateSteps(step_num); // return false to stay on step and true to continue navigation
  }

  function onFinishCallback() {
    var api_ok = document.getElementById('api_check_ok');
    api_ready = api_ok.value;

    if (api_ready == 0) {
      alert(unescape('Ihr API Key ist leider ung%FCltig!'));
    } else {
      $('form').submit();
    }
  }

  // Your Step validation logic
  function validateSteps(stepnumber) {
    var isStepValid = true;

    if (stepnumber == 1) {
      var api = document.getElementById('haendlerbund_key');
      apicheck = api.value;

      if (apicheck == "") {
        //alert("Please select a Customer.");
        // $('.wizard').smartWizard('showMessage', 'Bitte geben Sie Ihren API Key ein!');
        alert('Bitte geben Sie Ihren API Key ein!');
        isStepValid = false;
        return isStepValid;
      } else {
        api_load();
        return isStepValid;
      }
    }

    if (stepnumber == 2) {
      return isStepValid;
    }

    if (stepnumber == 3) {
      return isStepValid;
    }

    if (stepnumber == 4) {
      return isStepValid;
    }
  }

  //===== Accordion =====//
  $('div.menu_body:eq(0)').show();
  $('.acc .head:eq(0)').show().css({color: "#2B6893"});
  $(".acc .head").click(function () {
    $(this).css({color: "#2B6893"}).next("div.menu_body").slideToggle(300).siblings("div.menu_body").slideUp("slow");
    $(this).siblings().css({color: "#404040"});
  });
});


function Ajax() {
  //Eigenschaften deklarieren und initialisieren
  this.url = "";
  this.params = "";
  this.method = "GET";
  this.onSuccess = null;
  this.onError = function (msg) {
    alert(msg)
  }
}

Ajax.prototype.doRequest = function () {
  //Ueberpruefen der Angaben
  if (!this.url) {
    this.onError("Es wurde kein URL angegeben. Der Request wird abgebrochen.");
    return false;
  }

  if (!this.method) {
    this.method = "GET";
  } else {
    this.method = this.method.toUpperCase();
  }

  //Zugriff auf Klasse für readyStateHandler ermoeglichen
  var _this = this;

  //XMLHttpRequest-Objekt erstellen
  var xmlHttpRequest = getXMLHttpRequest();
  if (!xmlHttpRequest) {
    this.onError("Es konnte kein XMLHttpRequest-Objekt erstellt werden.");
    return false;
  }

  //Fallunterscheidung nach Uebertragungsmethode
  switch (this.method) {
    case "GET":
      xmlHttpRequest.open(this.method, this.url + "?" + this.params, true);
      xmlHttpRequest.onreadystatechange = readyStateHandler;
      xmlHttpRequest.send(null);
      break;
    case "POST":
      xmlHttpRequest.open(this.method, this.url, true);
      xmlHttpRequest.onreadystatechange = readyStateHandler;
      xmlHttpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
      xmlHttpRequest.send(this.params);
      break;
  }

  //Private Methode zur Verarbeitung der erhaltenen Daten
  function readyStateHandler() {
    if (xmlHttpRequest.readyState < 4) {
      return false;
    }
    if (xmlHttpRequest.status == 200 || xmlHttpRequest.status == 304) {
      if (_this.onSuccess) {
        _this.onSuccess(xmlHttpRequest.responseText, xmlHttpRequest.responseXML);
      }
    } else {
      if (_this.onError) {
        _this.onError("[" + xmlHttpRequest.status + " " + xmlHttpRequest.statusText + "] Es trat ein Fehler bei der Daten&uuml;bertragung auf.");
      }
    }
  }
}

//Gibt browserunabhaengig ein XMLHttpRequest-Objekt zurueck
function getXMLHttpRequest() {
  if (window.XMLHttpRequest) {
    //XMLHttpRequest fuer Firefox, Opera, Safari, ...
    return new XMLHttpRequest();
  } else if (window.ActiveXObject) {
    try {
      //XMLHTTP (neu) fuer Internet Explorer
      return new ActiveXObject("Msxml2.XMLHTTP");
    } catch (e) {
      try {
        //XMLHTTP (alt) fuer Internet Explorer
        return new ActiveXObject("Microsoft.XMLHTTP");
      } catch (e) {
        return null;
      }
    }
  }
  return false;
}

function api_load() {
  var api_key = document.getElementById('haendlerbund_key').value;
  document.getElementById("text").innerHTML = '<strong id="text"><center><br /><br /><img src="includes/haendlerbund/images/loading.gif" /><br /><br />Einen Moment bitte, es wird geladen!</center></strong>';

  with (new Ajax()) {
    url = "haendlerbund.php";
    method = "GET";
    params = "api_konfiguration=1&api_key=" + api_key;
    onSuccess = successHandler;
    onError = errorHandler;
    doRequest();
  }


//Den Text in die Seite einfuegen
  function successHandler(txt, xml) {
    document.getElementById("text").innerHTML = txt;
  }

//Fehler
  function errorHandler(msg) {
    document.getElementById("text").innerHTML = msg;
  }
}
