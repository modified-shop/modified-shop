<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  if (defined('MODULE_SEMKNOX_SYSTEM_STATUS')
      && MODULE_SEMKNOX_SYSTEM_STATUS == 'true'
      && defined('MODULE_SEMKNOX_SYSTEM_PROJECT_'.$_SESSION['languages_id'])
      && constant('MODULE_SEMKNOX_SYSTEM_PROJECT_'.$_SESSION['languages_id']) != ''
      )
  {
    $module_smarty = new Smarty();

    $module_smarty->assign('language', $_SESSION['language']);
    $module_smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');

    // set cache ID
    if (!CacheCheck()) {
      $cache=false;
      $module_smarty->caching = 0;
      $cache_id = null;
    } else {
      $cache=true;
      $module_smarty->caching = 1;
      $module_smarty->cache_lifetime = CACHE_LIFETIME;
      $module_smarty->cache_modified_check = CACHE_CHECK;
    }

    // set cache id
    $cache_id = md5($_SESSION['language'].'_'.$_SESSION['customers_status']['customers_status_id']);

    if (is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/semknox_suggest.html')) {
      $template_suggest_file = CURRENT_TEMPLATE.'/module/semknox_suggest.html';
    } else {
      $template_suggest_file = DIR_FS_EXTERNAL.'semknox/templates/semknox_suggest.html';
    }

    if (is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/semknox_result.html')) {
      $template_result_file = CURRENT_TEMPLATE.'/module/semknox_container.html';
    } else {
      $template_result_file = DIR_FS_EXTERNAL.'semknox/templates/semknox_result.html';
    }

    if (!$module_smarty->is_cached($template_result_file, $cache_id) || !$module_smarty->is_cached($template_suggest_file, $cache_id) || !$cache) {

      $module_smarty->assign('language', $_SESSION['language']);
      $module_smarty->assign('TAG_TEXT', '{{%s}}');
      $module_smarty->assign('TAG_HTML', '{{{%s}}}');
      $module_smarty->assign('SHIPPING_INFO', $main->getShippingLink());
  
      $tax_array = array();
      foreach ($xtPrice->TAX as $tax_rate) {
        $tax_array[] = array(
          'tax_rate' => $tax_rate,
          'tax_info' => $main->getTaxInfo($tax_rate),
        );
      }
      $module_smarty->assign('TAX_DATA', $tax_array);
      $module_smarty->caching = 0;
    }

    if (!$cache) {
      $template_suggest = $module_smarty->fetch($template_suggest_file);
      $template_result = $module_smarty->fetch($template_result_file);
    } else {
      $template_suggest = $module_smarty->fetch($template_suggest_file, $cache_id);
      $template_result = $module_smarty->fetch($template_result_file, $cache_id);
    }

    require_once(DIR_FS_EXTERNAL.'compactor/compactor.php');
    $compactor = new Compactor(array('compress_css' => true));
    $template_suggest = $compactor->squeeze($template_suggest);  
    $template_result = $compactor->squeeze($template_result);
    
    $callback_js = '';
    if (is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/javascript/semknox_callback.js')) {
      $callback_js = file_get_contents(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/javascript/semknox_callback.js');
    } 
    
    if (MODULE_SEMKNOX_SYSTEM_DEFAULT_CSS != 'true') {
      $css_file = DIR_WS_EXTERNAL.'semknox/css/stylesheet.css';
      if (is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/css/semknox.css')) {
        $css_file = DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/css/semknox.css';
      }
      echo '<link rel="stylesheet" property="stylesheet" href="'.DIR_WS_BASE.$css_file.'?'.time().'" type="text/css" media="screen" />';
    }
  ?>
  <script>
    (function() {
      const projectId = "<?php echo constant('MODULE_SEMKNOX_SYSTEM_PROJECT_'.$_SESSION['languages_id']); ?>";
      const userGroup = "<?php echo $_SESSION['customers_status']['customers_status_id']; ?>";
      window.ss360Config = {
        siteId: projectId,
        showErrors: false,
        allowCookies: true,
        baseUrl: 'https://api-v3.semknox.com/search?userGroup=' + userGroup + '&projectId=' + projectId,
        suggestBaseUrl: 'https://api-v3.semknox.com/search/suggestions?userGroup=' + userGroup + '&projectId=' + projectId,
        language: "<?php echo $_SESSION['language_code']; ?>",
        searchBox: {
          selector: "#inputString",
          searchButton: '#inputStringSubmit'
        },
        style: {
          defaultCss: <?php echo MODULE_SEMKNOX_SYSTEM_DEFAULT_CSS; ?>,
          accentColor: "<?php echo MODULE_SEMKNOX_SYSTEM_COLOR; ?>"
        },
        suggestions: {
          minChars: <?php echo SEARCH_MIN_LENGTH; ?>,
          highlight: true,
          searchHistoryLabel: "<?php echo TEXT_SEMKNOX_RECENTLY_SEARCHED; ?>",
          viewAllLabel: "<?php echo TEXT_SEMKNOX_ALL_RESULTS; ?>",
          suggestTemplate: {
            template: <?php echo ((MODULE_SEMKNOX_SYSTEM_DEFAULT_CSS == 'true') ? undefined : "'".$template_suggest."'"); ?>
          },
        },
        results: {
          resultTemplate: {
            template: <?php echo ((MODULE_SEMKNOX_SYSTEM_DEFAULT_CSS == 'true') ? undefined : "'".$template_result."'"); ?>
          },
          embedConfig: {
            'url': "<?php echo xtc_href_link(FILENAME_ADVANCED_SEARCH_RESULT, '', 'NONSSL', false); ?>",
            contentBlock: "#search_result"
          },
          moreResultsButton: "<?php echo TEXT_SEMKNOX_MORE_RESULTS; ?>",
          noResultsText: "<?php echo TEXT_SEMKNOX_NO_RESULT; ?>",
          queryCorrectionText: "<?php echo TEXT_SEMKNOX_CORRECTION; ?>",
          moreResultsPagingSize: <?php echo MAX_DISPLAY_ADVANCED_SEARCH_RESULTS; ?>,
          showContentGroupHeadings: false,
          searchQueryParamName: 'q',
        },
        filters: {
          enabled: true,
          position: "top",
          label: "Filter",
          showCounts: true,
          showQuickDelete: true,
          deleteAllLabel: "<?php echo BUTTON_RESET; ?>",
        },
        layout: {
          mobile: {
            type: 'grid',
          },
          desktop: {
            type: 'grid',
          },
        },
        callbacks: {
          postSearch: function (event) {
            $('#search_keyword').html(event.interpretedQuery.corrected);
            
            <?php echo $callback_js; ?>
            if (typeof colorbox == 'object') {               
              $(".iframe").colorbox({iframe:true, width:"780", height:"560", maxWidth: "90%", maxHeight: "90%", fixed: true, close: '<i class="fas fa-times"></i>'});
            }
          }
        },
      };

      var r = document.createElement('script');
      r.src = "https://cdn.sitesearch360.com/v13/sitesearch360-v13.min.js";
      r.setAttribute('async', 'async');
      document.body.appendChild(r);
    })();
  </script>
  <?php
  }