// <![CDATA[
/**
 * @version SOFORT Gateway 5.2.0 - $Date: 2012-09-06 14:27:56 +0200 (Thu, 06 Sep 2012) $
 * @author SOFORT AG (integration@sofort.com)
 * @link http://www.sofort.com/
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * $Id: sofortbox.js 3751 2012-10-10 08:36:20Z gtb-modified $
 */
function sofortOverlay(element, ajaxScript, fromUrl) {
	var overlayObj = {
		id : null,
		overlayElement : null,
		state : 'none',
		content : '',
		init : function(element, ajaxScript, fromUrl) {
			this.overlayElement = element;
			$(document).keyup(function(e) {
				if (e.keyCode == 27 && overlayObj.state == 'block') { 
					overlayObj.trigger();
				}
			});
			$(element).find('.closeButton').bind('click', function() {
				overlayObj.trigger();
			});
			$(element).find('.loader').css('border', '10px solid #C0C0C0');
			this.setContent(ajaxScript, fromUrl, $(element).find('.content'));
			return this;
		},
		setContent : function(ajaxScript, fromUrl, toElement) {
			var content = $.ajax({
				url: ajaxScript,
				type: "post",
				data: "url="+fromUrl,
				success : function(response) {
					$(toElement).html(response);
					$(toElement).show();
					$(toElement).css('white-space', 'normal');
					overlayObj.content = response;
				}
			});
		},
		setOverlayElement : function(state) {
			this.overlayElement.css('display', state);
			this.state = state;
		},
		trigger : function() {
			if(this.state == 'none') {
				this.setOverlayElement('block');
			} else if(this.state == 'block') {
				this.setOverlayElement('none');
			}
		}
	};
	var obj = overlayObj.init(element, ajaxScript, fromUrl);
	return obj;
}
//]]>