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
<?php if (basename($PHP_SELF) == FILENAME_PRODUCT_INFO) { ?>
<script>  
  $.get("<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE; ?>"+"/css/jquery.easyTabs.css?v=<?php echo filemtime(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/css/jquery.easyTabs.css'); ?>", function(css) {
    $("head").append("<style type='text/css'>"+css+"<\/style>");
  });
  // easyResponsiveTabs never removes its handlers, each start keeps its new ones for the teardown
  function tabsHandlers(tabs) {
    var windowEvents = $._data(window, 'events') || {};
    var tabsEvents = $._data(tabs, 'events') || {};
    return (windowEvents.resize || []).concat(tabsEvents.tabactivate || []);
  }
  function startTabs(tabs, options) {
    tabs.each(function () {
      var before = tabsHandlers(this);
      $(this).easyResponsiveTabs(options);
      $(this).data('tabsHandlers', tabsHandlers(this).filter(function (handler) {
        return before.indexOf(handler) < 0;
      }));
    });
  }
  // started tabs have the .resp-accordion elements of the plugin
  function initTabs(root) {
    startTabs($(root).find('#horizontalTab').addBack('#horizontalTab').not(':has(.resp-accordion)'), {
      type: 'default' //Types: default, vertical, accordion           
    });
    startTabs($(root).find('#horizontalAccordion').addBack('#horizontalAccordion').not(':has(.resp-accordion)'), {
      type: 'accordion' //Types: default, vertical, accordion           
    });
  }
  $(document).ready(function () {
    initTabs(document);
  });
  // content that a script has inserted into the page
  document.addEventListener('modified:content-updated', function (event) {
    initTabs(event.detail.root);
  });
  // content that a script is about to replace or remove
  document.addEventListener('modified:before-content-update', function (event) {
    $(event.detail.root).find('#horizontalTab, #horizontalAccordion').addBack('#horizontalTab, #horizontalAccordion').each(function () {
      var tabs = this;
      $.each($(tabs).data('tabsHandlers') || [], function (i, handler) {
        $(handler.type == 'resize' ? window : tabs).off(handler.type, handler.handler);
      });
      $(tabs).removeData('tabsHandlers');
    });
  });
</script>
<?php } ?>
<?php if (strpos($PHP_SELF, 'checkout')) { ?>
<script>  
  $.get("<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE; ?>"+"/css/jquery.easyTabs.css?v=<?php echo filemtime(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/css/jquery.easyTabs.css'); ?>", function(css) {
    $("head").append("<style type='text/css'>"+css+"<\/style>");
  });
  $(document).ready(function () {
    $('#horizontalAccordion').easyResponsiveTabs({
      type: 'accordion', //Types: default, vertical, accordion     
      closed: true,     
      activate: function(event) { // Callback function if tab is switched
        $(".resp-tab-active input[type=radio]").prop('checked', true);
      }
    });
   
    $('#horizontalTab').easyResponsiveTabs({
      type: 'default' //Types: default, vertical, accordion           
    });
  });
  $('#button_checkout_confirmation').on('click',function() {
    $(this).hide();
  });
</script>
<?php } ?>
