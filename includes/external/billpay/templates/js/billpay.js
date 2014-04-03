/**
 * billpay payment module javascript
 * @author Jascha Schiffer <jascha.schiffer@billpay.de>
 */

var _bpyQry;
var _bpyQueryLoaded = false;
var _bpyQueryQueue = [];

/**
 * cross browser compatible event listener appending
 *
 * @param element
 * @param type
 * @param callback
 * @private
 */
function _bpyAddEvent(element, type, callback) {
    if (element.addEventListener) {
        element.addEventListener(type, callback, false);
    } else {
        element['e' + type + callback] = callback;
        element[type + callback] = function() { element["e" + type + callback](window.event) };
        element.attachEvent("on" + type, element[type + callback]);
    }
}

/**
 * @param src
 * @param callback
 * @private
 */
function _bpyLoadScript(src, callback) {
    var script = document.createElement('script');
    script.setAttribute('src', src);
    script.setAttribute('id', 'bpyQuery');
    _bpyAddEvent(script, 'load', function() {
        _bpyQry = $.noConflict();
        _bpyQueryLoaded = true;
        if (_bpyQueryQueue.length > 0) {
            var _callback;
            while(_callback = _bpyQueryQueue.shift()) {
                _callback(_bpyQry);
            }
        }
    });
    _bpyAddEvent(script, 'load', callback);
    var _scriptElements = document.getElementsByTagName('head')[0].getElementsByTagName('script');
    if (_scriptElements.length > 0) {
        document.getElementsByTagName('head')[0].insertBefore(script, document.getElementsByTagName('script')[0]);
    } else {
        document.getElementsByTagName('head')[0].appendChild(script);
    }

}

_bpyLoadScript('//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js');

/**
 * executes a callback in the jquery context. parameter of the callback must accept the jquery object
 * @param callback
 */
function bpyQuery(callback) {
    if (_bpyQueryLoaded === true) {
        callback(_bpyQry);
    } else {
        _bpyQueryQueue.push(callback);
    }
}

bpyQuery(function($) {

    function bpyPopup(content) {
        return $(document.createElement('div'))
            .addClass('bpy-popup')
            .html(content)
            .hide()
            .appendTo($('body'))
            .fadeIn('fast');
    }

    function bpyExternalPopup(url, callback) {
        var containerClass = 'bpy-popup-' + url.replace(/[^\w\d]/g,'');
        var containerElement = $('.' + containerClass);
        if (containerElement.length > 0) {
            containerElement.remove();
        }
        var popupId = 'bpy_popup_' + Math.floor(Math.random() * 10001);
        var count = 0;
        while($('#' + popupId).length > 0 && count++ < 20) {
            popupId = 'bpy_popup_' + Math.floor(Math.random() * 10001);
        }
        var content = $(document.createElement("div"))
            .append(
                $(document.createElement('div'))
                    .addClass('bpy-loader')
            )
            .append(
                $(document.createElement('iframe'))
                    .attr('src', url)
                    .css('border', 'none')
                    .css('display', 'none')
                    .attr('frameborder', 0)
                    .attr('scrolling', 'auto')
            )
            .append(
                $(document.createElement('a'))
                    .addClass('bpy-remove-aware bpy-popup-close')
                    .attr('data-remove-target', '#' + popupId)
                    .text('X')
            );

        content.find('iframe').bind('load', function(event) {
            var parent = content;
            parent.find('.bpy-loader').hide();
            $(event.target).fadeIn('fast');
        });

        var element = bpyPopup(content)
            .addClass('bpy-external-popup')
            .addClass(containerClass)
            .attr('id', popupId);
        if (callback) {
            callback(element);
        }

        return element;
    }

    function bpyShowHide(element) {
        if (element.is(':visible')) {
            element.hide();
        } else {
            element.show();
        }
    }

    function bpyHide(element) {
        if (element.is(':visible')) {
            element.fadeOut();
        }
    }

    function bpyShow(element) {
        if (element.is(':hidden')) {
            element.slideDown();
        }
    }

    function bpyRemove(element) {
        element.remove();
    }

    $(function() {
        $('body')
            .delegate('.bpy-btn-sepa-info-popup', 'click', function(event) {
                event.stopPropagation();
                event.preventDefault();

                var callback;
                var eventTarget = $(event.target);
                if (eventTarget.attr('data-popup-target')) {
                    if (eventTarget.attr('data-popup-target') == 'auto') {
                        callback = function(element) {
                            element
                                .addClass('bpy-popup-sepa-converter')
                                .css({
                                    top: eventTarget.offset().top - 140
                                });
                        }
                    } else {
                        callback = function(element) {
                            eventTarget.addClass('bpy-popup-sepa-converter')
                                .parents(eventTarget.attr('data-popup-target'))
                                    .css('position', 'relative')
                                    .append(element);
                        }
                    }
                }
                bpyExternalPopup('https://www.billpay.de/api/sepa/converter', callback);
            })
            .delegate('.bpy-btn-details', 'click', function(event) {
                event.stopPropagation();
                event.preventDefault();

                var infoBox = $(event.target).parents('.bpy-eula-label').siblings('.bpy-additional-information-block');
                if (infoBox.is(':visible')) {
                    infoBox.slideUp('fast');
                } else {
                    infoBox.slideDown('slow');
                }
            })
            // prevent the opening of our api link -> quick'n dirty but very elegant i think
            .delegate('a[href^="https://www.billpay.de/api/ratenkauf/zahlungsbedingungen"]', 'click', function(event) {
                event.stopPropagation();
                event.preventDefault();

                var element = bpyExternalPopup($(event.target).attr('href'));
                element.find('iframe').css({height: '580px', width: '580px'});
                element.css({height: '580px', width:  '580px'});
                element.css('margin-left', (element.width() / 2) * -1);

                $('html, body').animate({
                    scrollTop: element.offset().top
                }, 'slow');
            })
            .delegate('.bpy-show-hide-aware', 'click', function(event) {
                event.stopPropagation();
                event.preventDefault();

                var eventTarget = $(event.target);
                if (eventTarget.attr('data-show-hide-target')) {
                    eventTarget = $(eventTarget.attr('data-show-hide-target'));
                }
                bpyShowHide(eventTarget);
            })
            .delegate('.bpy-hide-aware', 'click', function(event) {
                event.stopPropagation();
                event.preventDefault();

                var eventTarget = $(event.target);
                if (eventTarget.attr('data-hide-target')) {
                    eventTarget = $(eventTarget.attr('data-hide-target'));
                }
                bpyHide(eventTarget);
            })
            .delegate('.bpy-show-aware', 'click', function(event) {
                event.stopPropagation();
                event.preventDefault();

                var eventTarget = $(event.target);
                if (eventTarget.attr('data-show-target')) {
                    eventTarget = $(eventTarget.attr('data-show-target'));
                }
                bpyShow(eventTarget);
            })
            .delegate('.bpy-remove-aware', 'click', function(event) {
                event.stopPropagation();
                event.preventDefault();

                var eventTarget = $(event.target);
                if (eventTarget.attr('data-remove-target')) {
                    eventTarget = $(eventTarget.attr('data-remove-target'));
                }
                bpyRemove(eventTarget);
            })
        ;
    })
});