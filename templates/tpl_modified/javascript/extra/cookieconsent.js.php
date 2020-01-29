<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2019 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/
?>
<script>  
  var consent_type = "<?php echo ((TRACKING_GOOGLEANALYTICS_ACTIVE == 'true' || TRACKING_PIWIK_ACTIVE == 'true' || TRACKING_FACEBOOK_ACTIVE == 'true' || (defined('TRACKING_CUSTOM_ACTIVE') && TRACKING_CUSTOM_ACTIVE == 'true')) ? 'opt-in' : 'info'); ?>";
  $("body").append('<div id="cookieconsent"></div>');
  
  window.cookieconsent.initialise({
    container: document.getElementById("cookieconsent"),
    type: consent_type,
    revokable: ((consent_type == 'info') ? false : true),
    animateRevokable: ((consent_type == 'info') ? true : false),
    content: {
      "message": ((consent_type == 'info') ? "<?php echo TEXT_COOKIECONSENT_MESSAGE_INFO; ?>" : "<?php echo TEXT_COOKIECONSENT_MESSAGE_TRACKING; ?>"),
      "dismiss": "<?php echo TEXT_COOKIECONSENT_DISSMISS; ?>",
      "link": "<?php echo TEXT_COOKIECONSENT_LINK; ?>",
      "href": "<?php echo ((isset($privacy_link)) ? $privacy_link : xtc_href_link(FILENAME_POPUP_CONTENT, 'coID=2', $request_type)); ?>",
      "policy": "<?php echo TEXT_COOKIECONSENT_POLICY; ?>",
      "allow": "<?php echo TEXT_COOKIECONSENT_ALLOW; ?>",
      "deny": "<?php echo TEXT_COOKIECONSENT_DENY; ?>"
    },
    cookie: {
      "name": "MODtrack",
      "path": "<?php echo DIR_WS_CATALOG; ?>",
      "domain": "<?php echo (xtc_not_null($current_domain) ? '.'.$current_domain : ''); ?>",
      "secure": <?php echo ((HTTP_SERVER == HTTPS_SERVER && $request_type == 'SSL') ? "true" : "false"); ?>
    },
    onInitialise: function(status) {
      if (status == cookieconsent.status.allow) {
        TrackingScripts();
      } else if (status != cookieconsent.status.dismiss) {
        DeleteCookies();
      }
    },
    onStatusChange: function(status, chosenBefore) {
      if (this.hasConsented()) {
        TrackingScripts();
      } else {
        DeleteCookies();
      }
    }
  });
  
  function TrackingScripts() {
    if ($.isFunction(window.TrackingGoogle)) {
      TrackingGoogle();
    }
    if ($.isFunction(window.TrackingPiwik)) {
      TrackingPiwik();
    }
    if ($.isFunction(window.TrackingFacebook)) {
      TrackingFacebook();
    }
  }

  function DeleteCookies() {
    var essential = ["MODsid", "MODtest", "MODtrack", "paypal"];
    var cookies = document.cookie.split(";");
    
    for (var c = 0; c < cookies.length; c++) {
      var cookie_name = encodeURIComponent(cookies[c].trim().split("=")[0]);      
      for (var e = 0; e < essential.length; e++) {        
        if (cookie_name.indexOf(essential[e]) >= 0) {
          delete cookies[c];      
        }
      }
    }
    
    var cookies = cookies.filter(function (el) { 
      return el != null; 
    });
    
    if (cookies.length > 0) {
      for (var c = 0; c < cookies.length; c++) {
        var cookie_name = encodeURIComponent(cookies[c].trim().split("=")[0]);
        var d = window.location.hostname.split(".");
        while (d.length > 0) {
          var p = location.pathname.split('/');
          while (p.length > 0) {
            document.cookie = cookie_name + '=; expires=Thu, 01-Jan-1970 00:00:01 GMT; path=' + p.join('/');
            document.cookie = cookie_name + '=; expires=Thu, 01-Jan-1970 00:00:01 GMT; domain=' + d.join('.') + ' ; path=' + p.join('/');
            document.cookie = cookie_name + '=; expires=Thu, 01-Jan-1970 00:00:01 GMT; domain=.' + d.join('.') + ' ; path=' + p.join('/');
            p.pop();
          };
          d.shift();
        }        
      }
    }
  }
</script>
