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
 * (c) 2013 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

/**
 * Controls the upload process.
 */
var GenericCheckinSubmitAjaxController = (function (_super, $) {
	JSClass.__extends(GenericCheckinSubmitAjaxController, _super);
	
	function GenericCheckinSubmitAjaxController() {
		this._isRunning = false;
		this._triggerURL = '';
		this._urlPrefix = '';
		this._triggerURLAppend = '';
		
		this._abort = false;
		this._timer = null;
		this._interval = 200;
		this._intervalThrottle = 1500;
		this._ajaxIsRunning = false;
		
		this._result = {};
		
		this._errorCount = 0;
		
		this._i18n = {
			'TitleInformation' : 'Information',
			'TitleAjaxError': 'Ajax Error',
			'LabelStatus': 'Status',
			'LabelError': 'Error',
			'MessageUploadFinal': "{1} of {2} items submitted.",
			'MessageUploadStatus': "{1} of {2} items submitted. {3} total.",
			'MessageUploadFatalError': 'A fatal error occured during the aggregation of the required data.',
			'MessageUploadNotSync': 'Not synchronized.'
		};
	}
	
	/**
	 * Sets the URL that the ajax call to start one submit cycle should use.
	 *
	 * @param {String} triggerURL The final url
	 * @return {this}
	 *
	 * @public
	 */
	GenericCheckinSubmitAjaxController.prototype.setTriggerURL = function (triggerURL) {
		this._triggerURL = triggerURL;
		return this;
	};
	
	/**
	 * @public
	 */
	GenericCheckinSubmitAjaxController.prototype.setURLPrefix = function (urlPrefix) {
		this._urlPrefix = urlPrefix;
		return this;
	};
	
	/**
	 * @public
	 */
	GenericCheckinSubmitAjaxController.prototype.doAbort = function (abort) {
		this._abort = abort;
		return this;
	};
	
	/**
	 * @public
	 */
	GenericCheckinSubmitAjaxController.prototype.addLocalizedMessages = function (i18n) {
		// simple non-recursive merge
		for (var k in i18n) {
			if (!i18n.hasOwnProperty(k)) {
				continue;
			}
			this._i18n[k] = i18n[k];
		}
		return this;
	};
	
	/**
	 * Used to translate strings with the contents of this._i18n
	 * @param {String} k The key
	 * @return {String}
	 *
	 * @private
	 */
	GenericCheckinSubmitAjaxController.prototype.__ = function (k) {
		if (typeof this._i18n[k] !== 'undefined') {
			if (!this._i18n.hasOwnProperty(k)) {
				return '['+k+'] not supported as language key.';
			}
			return this._i18n[k];
		}
		return '['+k+'] missing translation.';
	};
	
	/**
	 * @private
	 */
	GenericCheckinSubmitAjaxController.prototype._buildGetParam = function (k, v) {
		if (this._urlPrefix.length > 0) {
			return '&'+this._urlPrefix+'['+k+']='+v;
		} else {
			return '&'+k+'='+v;
		}
	};
	
	/**
	 * @private
	 */
	GenericCheckinSubmitAjaxController.prototype._dialog = function (dialog) {
		var that = this;
		$('<div></div>').html(dialog.message).dialog({
			width: 500,
			minHeight: 100,
			open: function(event, ui) {
				$(this).closest('.ui-dialog').find('.ui-dialog-titlebar-close').hide();
			},
			closeOnEscape: false,
			modal: (typeof dialog.modal == 'undefined') ? true : dialog.modal,
			title: dialog.title,
			buttons: [{
				text: "OK",
				click: function() {
					var fn = dialog.ok || function () {};
					fn.apply(that);
					$(this).dialog('destroy').remove();
				}
			}]
		});
	};
	
	/**
	 * Generates all final dialogs
	 *
	 * @param {Array} dialogs The dialogs contents
	 * @return {void}
	 *
	 * @private
	 */
	GenericCheckinSubmitAjaxController.prototype._processDialogs = function (dialogs) {
		for (i = 0; i < dialogs.length; ++i) {
			myConsole.log(dialogs[i]);
			this._dialog({
				title: dialogs[i].headline,
				message: dialogs[i].message,
				modal: false
			});
		}
	};
	
	/**
	 * @private
	 */
	GenericCheckinSubmitAjaxController.prototype._startAnimation = function () {
		$('#threeDots').addClass('pulse');
	};
	
	/**
	 * @private
	 */
	GenericCheckinSubmitAjaxController.prototype._stopAnimation = function () {
		$('#threeDots').removeClass('pulse');
	};
	
	/**
	 * @private
	 */
	GenericCheckinSubmitAjaxController.prototype._handleFatalError = function () {
		var btn = $('<button class="ml-button">+</button>').click(function () {
			$('#checkinSubmitDebug').css({'display': 'block'});
		});
		$('#checkinSubmitStatus').html(this.__('MessageUploadFatalError'));
		$('#checkinSubmitStatus').append(btn);
		$('#checkinSubmitDebug').html(
			typeof this._result == "object"
				? (typeof print_m == 'function'
					? print_r(this._result)
					: JSON.stringify(this._result)
				)
				: this._result
		);
	};
	
	/**
	 * @private
	 */
	GenericCheckinSubmitAjaxController.prototype._handleAPIError = function () {
		$('#magnaErrors div:first').append(this._result.api.html);
		if (this._result.api.html != '') {
			$('#magnaErrors').css({'display':'block'});
			$('#apiException').css({'display':'block'});
		}
		if ($('#magnaErrorsCustom').length == 0) {
			$('<div id="magnaErrorsCustom"></div>').insertAfter('#magnaErrors');
		}
		if (typeof this._result.api.customhtml == 'string') {
			$('#magnaErrorsCustom').append(this._result.api.customhtml);
		}
	};
	
	/**
	 * @private
	 */
	GenericCheckinSubmitAjaxController.prototype._updateProgress = function () {
		try {
			var percent = (this._result.state.submitted / this._result.state.total) * 100
			$('#checkinSubmitStatus').html(strformat(
				this.__('MessageUploadStatus'),
				this._result.state.success+'', this._result.state.submitted+'', this._result.state.total+''
			));
			
			$('#uploadprogress .progressPercent').html(new Number(percent).toFixed(1)+'%');
			$('#uploadprogress .progressBar').css({width: percent+'%'});
		} catch (e) {
			myConsole.log(e);
		}
	};
	
	/**
	 * @private
	 */
	GenericCheckinSubmitAjaxController.prototype._finalise = function () {
		var message = this.__('MessageUploadFinal');
		if (!this._result.ignoreErrors) {
			return;
		}
					
		if (this._result.uploadNotSync) {
		    message = this.__('MessageUploadFinal') + '<br><br>' + this.__('MessageUploadNotSync');
		}
		if(this._result.showWithoutDialog !== "undefined" && $('#ml-html-content').length){
                    $('#ml-html-content').html(this._result.showWithoutDialog)
                }else{
                    this._dialog({
                            'title': this.__('TitleInformation'),
			'message': strformat(
                                    message,
                                    this._result.state.success+'', this._result.state.total+''
                            ),
                            'ok': function () {
                                    if (this._result.redirect != undefined) {
                                            window.location.href = this._result.redirect;
                                            $.blockUI(blockUILoading);
                                    }
                            }
                    });
                }
		if (this._result.finaldialogs.length > 0) {
			this.processDialogs(this._result.finaldialogs);
		}
	};
	
	/**
	 * @private
	 */
	GenericCheckinSubmitAjaxController.prototype._onSuccess = function (data, textStatus, jqXHR) {
		myConsole.log('GenericCheckinSubmitAjaxController::_onSuccess()', data, jqXHR);
		
		this._errorCount = 0;
		
		if ((typeof print_m == 'function') && (typeof data.state == 'object'))  {
			//myConsole.log('State :: '+print_m(data.state));
			myConsole.log('State :: '+((typeof JSON != 'undefined') ? JSON.stringify(data.state) : print_m(data.state)));
		}
		
		if (this._abort) {
			datatext = data;
			if (typeof datatext !== 'string') {
				datatext = JSON.stringify(datatext, null, ' ');
			}
			$('#checkinSubmitDebug').html(
				'<a href="' + this._triggerURL + this._triggerURLAppend + '" target="_blank">Rerun</a>\n\n'
				+datatext
			).css({'display': 'block'});
			return false;
		}
		
		this._result = data;
		
		if ((typeof this._result != 'object')
			|| (typeof this._result.state != 'object')
			|| (typeof this._result.proceed == 'undefined')
		) {
			this._handleFatalError();
			return false;
		}
		this._updateProgress();
		if (typeof this._result.api != 'undefined') {
			this._handleAPIError();
			if (!this._result.ignoreErrors) {
				return false;
			}
		}
		
		if (!this._result.proceed) {
			this._finalise();
			return false;
		}
		return true;
	};
	
	/**
	 * @private
	 */
	GenericCheckinSubmitAjaxController.prototype._onError = function (jqXHR, textStatus, errorThrown) {
		var aborted = false;
		myConsole.log(arguments);
		
		if (this._errorCount++ > 5) {
			this._interruptTimer();
			aborted = true;
		} else {
			this._ajaxIsRunning = false;
		}
		
		this._dialog({
			title: this.__('TitleAjaxError'),
			modal: false,
			message:
				 '<b>'+this.__('LabelStatus')+':</b> '+jqXHR.status+' '+jqXHR.statusText+'<br>'
				+(aborted ? 'Vorgang abgebrochen.' : '')
		});
	};
	
	/**
	 * @private
	 */
	GenericCheckinSubmitAjaxController.prototype._loop = function () {
		if (this._ajaxIsRunning) {
			return;
		}
		
		this._ajaxIsRunning = true;
		
		$.ajax({
			type: 'GET',
			url: this._triggerURL + this._triggerURLAppend,
			context: this,
			success: function (data, textStatus, jqXHR) {
				if (this._onSuccess(data, textStatus, jqXHR)) {
					var that = this;
					window.setTimeout(function () {
						that._ajaxIsRunning = false;
					}, this._interval);
				} else {
					this._interruptTimer();
				}
			},
			error: function (jqXHR, textStatus, errorThrown) {
				this._onError.apply(this, arguments);
			}
		});
	};
	
	/**
	 * @private
	 */
	GenericCheckinSubmitAjaxController.prototype._startTimer = function () {
		var interval = (document.visibilityState == 'visible')
			? this._interval 
			: this._intervalThrottle;
		
		myConsole.log('GenericCheckinSubmitAjaxController::_startTimer('+interval+')');
		
		this._isRunning = true;
		this._startAnimation();
		this._timer = window.setInterval(
			(function (that) {         // Self-executing func which takes 'this' as that
				return function() {    // Return a function in the context of 'that'
					that._loop();      // Things that shall run as non-window 'this'
				};
			})(this),
			interval
		);
	};
	
	/**
	 * @private
	 */
	GenericCheckinSubmitAjaxController.prototype._interruptTimer = function () {
		myConsole.log('GenericCheckinSubmitAjaxController::_interruptTimer()');
		try {
			window.clearInterval(this._timer);
			this._stopAnimation();
			this._isRunning = false;
		} catch (e) {
			myConsole.log(e);
		}
	};
	
	/**
	 * @private
	 */
	GenericCheckinSubmitAjaxController.prototype._setupAbortHandler = function () {
		var that = this;
		$('#uploadprogress').off('dblclick').on('dblclick', function () {
			that._interruptTimer();
		});
	};
	
	/**
	 * @private
	 */
	GenericCheckinSubmitAjaxController.prototype._setupThrottlingHandler = function () {
		var that = this;
		
		// the DOMEvent visibilitychange fires if the tab switches active <--> inactive
		$(document).off('visibilitychange.mlcheckin').on('visibilitychange.mlcheckin', function () {
			myConsole.log('DOMEvent::visibilitychange: '+document.visibilityState);
			if (!that._isRunning) {
				return true;
			}
			that._interruptTimer();
			that._startTimer();
		});
	};
	
	/**
	 * @public
	 */
	GenericCheckinSubmitAjaxController.prototype.setInitialUploadStatus = function (totalItems) {
		$('#checkinSubmitStatus').html(strformat(
			this.__('MessageUploadStatus'),
			'0', '0', totalItems+''
		));
		$('#uploadprogress .progressPercent').html('0.0%');
		$('#uploadprogress .progressBar').css({width: '0%'});
		return this;
	};
	
	/**
	 * @public
	 */
	GenericCheckinSubmitAjaxController.prototype.runSubmitBatch = function () {
		this._triggerURLAppend = '';
		if (this._abort) {
			this._triggerURLAppend += this._buildGetParam('abort', 'true');
		}
		
		if (typeof document.visibilityState !== 'string') {
			document.visibilityState = 'visible';
		}
		
		this._setupAbortHandler();
		this._setupThrottlingHandler();
		this._startTimer();
		
		return this;
	};
	
	return GenericCheckinSubmitAjaxController;
})(JSClass, jQuery);
