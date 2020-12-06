<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2019 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

if (defined('MODULE_COOKIE_CONSENT_STATUS') && strtolower(MODULE_COOKIE_CONSENT_STATUS) == 'true') {
  $link_parameters = defined('TPL_POPUP_CONTENT_LINK_PARAMETERS') ? TPL_POPUP_CONTENT_LINK_PARAMETERS : POPUP_CONTENT_LINK_PARAMETERS;
  $lang_links = '';
  if (!isset($lng) || (isset($lng) && !is_object($lng))) {
    require_once(DIR_WS_CLASSES . 'language.php');
    $lng = new language;
  }

  if (count($lng->catalog_languages) > 1) {
    $lang_content = array();
    reset($lng->catalog_languages);
    foreach ($lng->catalog_languages as $key => $value) {
      $lng_link_url = xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('language', 'currency')) . 'language=' . $key, $request_type);
      if ($lng_link_url != '#') {
        $lang_links .= '<a class="as-oil-lang" href="' . $lng_link_url . '">' . $value['name'] . '</a>';
      }
    }
  }
?>
<script id="oil-configuration" type="application/configuration">
{
  "config_version": 1,
  "preview_mode": <?php echo defined('COOKIE_CONSENT_NO_TRACKING') ? 'true' : 'false'; ?>,
  "advanced_settings": true,
  "timeout": 0,
  "iabVendorListUrl": "<?php echo decode_htmlentities(xtc_href_link('ajax.php', 'ext=get_cookie_consent&speed=1&language='.$_SESSION['language_code'], $request_type, false)); ?>",
  "locale": {
    "localeId": "<?php echo $_SESSION['language_code']; ?>",
    "version": 1,
    "texts": {
      "label_intro_heading": "<?php echo TEXT_COOKIE_CONSENT_LABEL_INTRO_HEADING; ?>",
      "label_intro": "<?php echo TEXT_COOKIE_CONSENT_LABEL_INTRO; ?>",
      "label_button_yes": "<?php echo TEXT_COOKIE_CONSENT_LABEL_BUTTON_YES; ?>",
      "label_button_back": "<?php echo TEXT_COOKIE_CONSENT_LABEL_BUTTON_BACK; ?>",
      "label_button_yes_all": "<?php echo TEXT_COOKIE_CONSENT_LABEL_BUTTON_YES_ALL; ?>",
      "label_button_advanced_settings": "<?php echo TEXT_COOKIE_CONSENT_LABEL_BUTTON_ADVANCED_SETTINGS; ?>",
      "label_cpc_heading": "<?php echo TEXT_COOKIE_CONSENT_LABEL_CPC_HEADING; ?>",
      "label_cpc_activate_all": "<?php echo TEXT_COOKIE_CONSENT_LABEL_CPC_ACTIVATE_ALL; ?>",
      "label_cpc_deactivate_all": "<?php echo TEXT_COOKIE_CONSENT_LABEL_CPC_DEACTIVATE_ALL; ?>",
      "label_nocookie_head": "<?php echo TEXT_COOKIE_CONSENT_LABEL_NOCOOKIE_HEAD; ?>",
      "label_nocookie_text": "<?php echo TEXT_COOKIE_CONSENT_LABEL_NOCOOKIE_TEXT; ?>",
      "label_third_party": " ",
      "label_imprint_links": "<?php echo $lang_links; ?><a href='<?php echo xtc_href_link(FILENAME_POPUP_CONTENT, "coID=2".$link_parameters); ?>' onclick='return cc_popup_content(this)'><?php echo TEXT_COOKIE_CONSENT_LABEL_INTRO_TEXT_PRIVACY; ?></a> <a href='<?php echo xtc_href_link(FILENAME_POPUP_CONTENT, "coID=4".$link_parameters); ?>' onclick='return cc_popup_content(this)'><?php echo TEXT_COOKIE_CONSENT_LABEL_INTRO_TEXT_IMPRINT; ?></a>"
    }
  }
}
</script>
<script src="<?php echo DIR_WS_BASE.DIR_TMPL_JS.'oil.min.js'; ?>"></script>
<script>!function(e){var n={};function t(o){if(n[o])return n[o].exports;var r=n[o]={i:o,l:!1,exports:{}};return e[o].call(r.exports,r,r.exports,t),r.l=!0,r.exports}t.m=e,t.c=n,t.d=function(e,n,o){t.o(e,n)||Object.defineProperty(e,n,{configurable:!1,enumerable:!0,get:o})},t.r=function(e){Object.defineProperty(e,"__esModule",{value:!0})},t.n=function(e){var n=e&&e.__esModule?function(){return e.default}:function(){return e};return t.d(n,"a",n),n},t.o=function(e,n){return Object.prototype.hasOwnProperty.call(e,n)},t.p="/",t(t.s=115)}({115:function(e,n,t){"use strict";!function(e,n){e.__cmp||(e.__cmp=function(){function t(e){if(e){var t=!0,r=n.querySelector('script[type="application/configuration"]#oil-configuration');if(null!==r&&r.text)try{var a=JSON.parse(r.text);a&&a.hasOwnProperty("gdpr_applies_globally")&&(t=a.gdpr_applies_globally)}catch(e){}e({gdprAppliesGlobally:t,cmpLoaded:o()},!0)}}function o(){return!(!e.AS_OIL||!e.AS_OIL.commandCollectionExecutor)}var r=[],a=function(n,a,c){if("ping"===n)t(c);else{var i={command:n,parameter:a,callback:c};r.push(i),o()&&e.AS_OIL.commandCollectionExecutor(i)}};return a.commandCollection=r,a.receiveMessage=function(n){var a=n&&n.data&&n.data.__cmpCall;if(a)if("ping"===a.command)t(function(e,t){var o={__cmpReturn:{returnValue:e,success:t,callId:a.callId}};n.source.postMessage(o,n.origin)});else{var c={callId:a.callId,command:a.command,parameter:a.parameter,event:n};r.push(c),o()&&e.AS_OIL.commandCollectionExecutor(c)}},function(n){(e.attachEvent||e.addEventListener)("message",function(e){n.receiveMessage(e)},!1)}(a),function e(){if(!(n.getElementsByName("__cmpLocator").length>0))if(n.body){var t=n.createElement("iframe");t.style.display="none",t.name="__cmpLocator",n.body.appendChild(t)}else setTimeout(e,5)}(),a}())}(window,document)}});</script>
<script>
function cc_popup_content(trgt) {
  tb_show('Information', trgt.href, 'consent');
  return false;
}
(function() {
  // Cross browser event handler definition
  let eventMethod = window.addEventListener ? 'addEventListener' : 'attachEvent';
  let messageEvent = eventMethod === 'attachEvent' ? 'onmessage' : 'message';
  let eventer = window[eventMethod];

  // Callback to be executed when event is fired
  function receiveMessage(event) {
    let eventDataContains = function(str) {
			return JSON.stringify(event.data).indexOf(str) !== -1;
		};
		<?php if (defined('MODULE_COOKIE_CONSENT_SET_READABLE_COOKIE') && strtolower(MODULE_COOKIE_CONSENT_SET_READABLE_COOKIE) == 'true') { ?>
		let oilGtagCookie = function(data) {
			let cookieDate = new Date;
			//  the oil.js cookie expires after 1 month
			cookieDate.setMonth(cookieDate.getMonth() + 1);
			
			let cookieString = 'MODOilTrack=' + JSON.stringify(data.purposeConsents) + ';';
			cookieString += 'expires=' + cookieDate.toUTCString() + ';';
			cookieString += 'path=/;SameSite=Lax;';
			if (typeof SetSecCookie !== 'undefined' && SetSecCookie == true) {
			  cookieString += 'Secure;';
			}
			
			document.cookie = cookieString;
		};
    if (event && event.data && (eventDataContains('oil_optin_done') || eventDataContains('oil_has_optedin'))) {
			__cmp('getVendorConsents', [], oilGtagCookie);
		}
    <?php } ?>
  }

  // Register event handler
  eventer(messageEvent, receiveMessage, false);
  
  $(document).on('click', '[trigger-cookie-consent-panel]',  function () {
	
		window.AS_OIL.showPreferenceCenter();

		if (!$('.as-oil.light').length) {
			$('body').append(
				$('<div/>')
					.addClass('as-oil light')
					.append(
						$('<div/>')
							.attr('id', 'oil-preference-center')
							.addClass('as-oil-content-overlay cpc-dynamic-panel')
					)
			);
		}
	});
})();
</script>
<?php }