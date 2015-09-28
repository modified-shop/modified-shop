<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

  if (defined('RUN_MODE_ADMIN')) { 
    $ts_link = 'http://www.trustedshops.de/shopbetreiber/integration/shopsoftware-integration/modified-ecommerce/?shop_id=%s&backend_language=%s&shopsw=MODIFIED&shopsw_version='.DB_VERSION.'&plugin_version=1.0&context=trustbadge&variant=&yOffset=?utm_source=Xtmodified&utm_medium=backend&utm_content=link1&utm_campaign=modultracking&a_aid=55cb437783a78';
    $ts_link_product = 'http://www.trustedshops.de/shopbetreiber/integration/product-reviews/?utm_source=Xtmodified&utm_medium=backend&utm_content=link1&utm_campaign=modultracking&a_aid=55cb437783a78';
    $ts_link_review = 'http://www.trustedshops.de/shopbetreiber/integration/kundenbewertungen-anzeigen/?utm_source=Xtmodified&utm_medium=backend&utm_content=link1&utm_campaign=modultracking&a_aid=55cb437783a78';
  }
   
  $default_trustbadge_code = "
<script type=\"text/javascript\">
  (function () { 
    var _tsid = '%s'; /* do not change this */
    _tsConfig = { 
      'yOffset': '0', /* offset from page bottom */
      'variant': 'reviews', /* text, default, small, reviews, custom, custom_reviews */
      'customElementId': '', /* required for variants custom and custom_reviews */
      'trustcardDirection': '', /* for custom variants: topRight, topLeft, bottomRight, bottomLeft */
      'customBadgeWidth': '', /* for custom variants: 40 - 90 (in pixels) */
      'customBadgeHeight': '', /* for custom variants: 40 - 90 (in pixels) */
      'disableResponsive': 'false', /* deactivate responsive behaviour */
      'disableTrustbadge': 'false', /* deactivate trustbadge */
      'trustCardTrigger': 'mouseenter' /* set to 'click' if you want the trustcard to be opened on click instead */
    };
    var _ts = document.createElement('script');
    _ts.type = 'text/javascript'; 
    _ts.charset = 'utf-8'; 
    _ts.async = true; 
    _ts.src = '//widgets.trustedshops.com/js/' + _tsid + '.js'; 
    var __ts = document.getElementsByTagName('script')[0];
    __ts.parentNode.insertBefore(_ts, __ts);
  })();
</script>
";

  $default_trustbadge_code = "
<script type=\"text/javascript\">
  (function () { 
    var _tsid = '%s'; 
    _tsConfig = { 
      'yOffset': '%s', /* offset from page bottom */
      'variant': '%s', /* text, default, small, reviews, custom, custom_reviews */
      'disableResponsive': 'false', /* deactivate responsive behaviour */
      'disableTrustbadge': 'false', /* deactivate trustbadge */
      'trustCardTrigger': 'mouseenter' /* set to 'click' if you want the trustcard to be opened on click instead */
    };
    var _ts = document.createElement('script');
    _ts.type = 'text/javascript'; 
    _ts.charset = 'utf-8'; 
    _ts.async = true; 
    _ts.src = '//widgets.trustedshops.com/js/' + _tsid + '.js'; 
    var __ts = document.getElementsByTagName('script')[0];
    __ts.parentNode.insertBefore(_ts, __ts);
  })();
</script>
";

  $review_sticker_default = "
<script type=\"text/javascript\">
  _tsRatingConfig = {
    tsid: '%s', /* do not change this */
    variant: 'skyscraper_vertical', /* valid values: skyscraper_vertical, skyscraper_horizontal, vertical */
    theme: 'light',
    reviews: '10', /* default = 10 */
    borderColor: '#C6C6C6', 
    className: 'ts_review_sticker', /* optional - override the whole sticker style with your own css class */
    richSnippets: 'off', /* valid values: on, off */
    introtext: '%s' /* optional, not used in skyscraper variants */
  };
  var scripts = document.getElementsByTagName('SCRIPT'),
  me = scripts[scripts.length - 1];
  var _ts = document.createElement('SCRIPT');
  _ts.type = 'text/javascript';
  _ts.async = true;
  _ts.charset = 'utf-8';
  _ts.src ='//widgets.trustedshops.com/reviews/tsSticker/tsSticker.js'; 
  me.parentNode.insertBefore(_ts, me);
  _tsRatingConfig.script = _ts;
</script>
";

  $product_sticker_default = "
<script type=\"text/javascript\">
  _tsProductReviewsConfig = {
    tsid: '%s', /* do not change this */
    sku: '%s', /* do not change this */
    variant: 'productreviews',
    borderColor: '#6D2551',
    richSnippets: 'off',
    introtext: '%s' /* optional */
  };
  var scripts = document.getElementsByTagName('SCRIPT'), 
  me = scripts[scripts.length - 1];
  var _ts = document.createElement('SCRIPT');
  _ts.type = 'text/javascript';
  _ts.async = true;
  _ts.charset = 'utf-8';
  _ts.src='//widgets.trustedshops.com/reviews/tsSticker/tsProductSticker.js'; 
  me.parentNode.insertBefore(_ts, me); 
  _tsProductReviewsConfig.script = _ts;
</script>
";