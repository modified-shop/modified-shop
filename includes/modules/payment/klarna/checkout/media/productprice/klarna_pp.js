if (typeof jQuery.prototype.closest == "undefined") {
    jQuery.prototype.closest = jQuery.prototype.parents;
}

KlarnaProductInfo = function(info) {
    for(key in info) {
        this[key] = info[key];
    }
};

KlarnaProductInfo.prototype.initPartPaymentBox = function() {
    ppBoxInMotion = false;
    jQuery('.klarna_PPBox_pull,.klarna_PPBox_top').unbind('click').click(function () {
        var kBox = jQuery(this).closest('.klarna_PPBox');
        if(!ppBoxInMotion) {
            ppBoxInMotion = true;
            kBox.find('.klarna_PPBox_bottom').slideToggle('fast', function() {
                if(kBox.find('.nlBanner').length) {
                    var bannerPosition = kBox.find('.nlBanner').offset();
                    bannerPosition.top += kBox.find('.klarna_PPBox_bottom:visible').height();
                    bannerPosition.top -= kBox.find('.klarna_PPBox_bottom').not(':visible').height();
                    kBox.find('.nlBanner').offset(bannerPosition);
                }
                ppBoxInMotion = false;
            });
        }
    });
}

KlarnaProductInfo.prototype.update = function() {
    data = {
        action: 'updateProductPrice',
        country: this.countryCode,
        sum: this.sum,
        type: 'part'
    }
    var init = this.initPartPaymentBox;
    jQuery.ajax({
        type: "GET",
        url: this.ajax_path,
        data: data,
        success: function(response){
            var ppBox = jQuery('div[class="klarna_PPBox"]');
            var newInner = jQuery(response).find('div[class="klarna_PPBox_inner"]');
            ppBox.find('div[class="klarna_PPBox_inner"]').replaceWith(newInner);
            init();
        }
    });
}

window.klarnaPP = new function() {
    if (typeof klarna_product != 'undefined') {
        this.product = new KlarnaProductInfo(klarna_product);
    }
}

jQuery(document).ready(function () {
    klarnaPP.product.initPartPaymentBox();
});

// if version is older than 1.4 we implement
// our own offset ( { left, top }) function.
// shamelessly borrowed from http://manifestwebdesign.com
var re = /(\d+)\.(\d+)\.(\d+)/;
var current = re.exec(jQuery.fn.jquery);
if (current[1] == "1" && parseInt(current[2]) < 4) {
    (function($){

        /**
         * Function for setting offset, created here so it's only created once rather than
         * creating an anonymous function every time offset is called
         */
        function setOffset(el, newOffset){
            var $el = $(el);

            // get the current css position of the element
            var cssPosition = $el.css('position');

            // whether or not element is hidden
            var hidden = false;

            // if element was hidden, show it
            if($el.css('display') == 'none'){
                hidden = true;
                $el.show();
            }

            // get the current offset of the element
            var curOffset = $el.offset();

            // if there is no current jQuery offset, give up
            if(!curOffset){
                // if element was hidden, hide it again
                if(hidden)
                    $el.hide();
                return;
            }

            // set position to relative if it's static
            if (cssPosition == 'static') {
                $el.css('position', 'relative');
                cssPosition = 'relative';
            }

            // get current 'left' and 'top' values from css
            // this is not necessarily the same as the jQuery offset
            var delta = {
                left : parseInt($el.css('left'), 10),
                top: parseInt($el.css('top'), 10)
            };

            // if the css left or top are 'auto', they aren't numbers
            if (isNaN(delta.left)){
                delta.left = (cssPosition == 'relative') ? 0 : el.offsetLeft;
            }
            if (isNaN(delta.top)){
                delta.top = (cssPosition == 'relative') ? 0 : el.offsetTop;
            }

            if (newOffset.left || 0 === newOffset.left){
                $el.css('left', newOffset.left - curOffset.left + delta.left + 'px');
            }
            if (newOffset.top || 0 === newOffset.top){
                $el.css('top', newOffset.top - curOffset.top + delta.top + 'px');
            }

            // if element was hidden, hide it again
            if(hidden)
                $el.hide();
        }
        $.fn.extend({

            /**
             * Store the original version of offset(), so that we don't lose it
             */
            _offset : $.fn.offset,

            /**
             * Set or get the specific left and top position of the matched
             * elements, relative the the browser window by calling setXY
             * @param {Object} newOffset
             */
            offset : function(newOffset){
                return !newOffset ? this._offset() : this.each(function(){
                    setOffset(this, newOffset);
                });
            }
        });

    })(jQuery);
}