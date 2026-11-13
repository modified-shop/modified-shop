<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  if (is_file(DIR_FS_CATALOG.'includes/external/htmlpurifier/library/HTMLPurifier.auto.php')) {
    require_once(DIR_FS_CATALOG.'includes/external/htmlpurifier/library/HTMLPurifier.auto.php');
  }

  if (class_exists('HTMLPurifier_AttrDef') && !class_exists('xtc_css_layout_value')) {
    // css values the library does not know, e.g. 1fr 1fr or repeat(2, minmax(0, 1fr))
    class xtc_css_layout_value extends HTMLPurifier_AttrDef {
      public function validate($string, $config, $context) {
        $string = strtolower(trim($this->parseCDATA($string)));

        if ($string === '' || strlen($string) > 255) {
          return false;
        }
        // layout values only, no escapes and no quoted strings
        if (!preg_match('/^[a-z0-9 ,.%\/()*+#_-]+$/', $string) || strpos($string, '/*') !== false || strpos($string, '*/') !== false) {
          return false;
        }
        // these functions load content or run code
        if (preg_match('/(url|image|image-set|element|attr|counter|local|src|expression)\s*\(/', $string)) {
          return false;
        }

        return $string;
      }
    }
  }

  if (class_exists('HTMLPurifier_AttrDef_Text') && !class_exists('xtc_data_attribute_value')) {
    // data attributes do nothing by themselves, but shop scripts may put them into a link
    class xtc_data_attribute_value extends HTMLPurifier_AttrDef_Text {
      public function validate($string, $config, $context) {
        $string = parent::validate($string, $config, $context);

        if ($string === false) {
          return false;
        }
        // browsers ignore control characters and whitespace in the scheme
        $url = strtolower(preg_replace('/[\x00-\x20]+/', '', $string));
        if (strpos($url, 'javascript:') === 0
            || strpos($url, 'vbscript:') === 0
            || strpos($url, 'data:text/html') === 0
            )
        {
          return false;
        }

        return $string;
      }
    }
  }


  function xtc_remove_active_content($value, &$removed = false) {
    if (is_array($value)) {
      foreach ($value as $key => $val) {
        $value[$key] = xtc_remove_active_content($val, $removed);
      }
      return $value;
    }

    if (!is_string($value) || strpos($value, '<') === false) {
      return $value;
    }

    // the value is stored after stripslashes and printed after another one
    for ($i = 0; $i < 5; $i++) {
      $plain = stripslashes($value);
      if ($plain === $value) {
        break;
      }
      $value = $plain;
    }

    // the session and its charset do not exist yet, so decide per value
    $charset = ((!function_exists('mb_check_encoding') || mb_check_encoding($value, 'UTF-8')) ? 'UTF-8' : 'ISO-8859-15');
    $html = (($charset != 'UTF-8') ? mb_convert_encoding($value, 'UTF-8', $charset) : $value);

    // data attributes carry no behaviour of their own, so keep the ones in this text
    $data_attributes = array();
    if (preg_match_all('/\sdata-[a-z0-9_-]+/i', $html, $matches)) {
      $data_attributes = array_slice(array_unique(array_map('strtolower', array_map('trim', $matches[0]))), 0, 100);
      sort($data_attributes);
    }

    $purifier = xtc_remove_active_content_purifier($data_attributes);
    if ($purifier === false) {
      $removed = true;
      return htmlspecialchars($value, ENT_QUOTES, $charset);
    }

    $clean = $purifier->purify($html);
    // without a backslash every later stripslashes keeps the value as it is
    $clean = str_replace('\\', '&#92;', $clean);

    if ($clean !== $html) {
      $removed = true;
    }

    return (($charset != 'UTF-8') ? mb_convert_encoding($clean, $charset, 'UTF-8') : $clean);
  }


  function xtc_remove_active_content_purifier($data_attributes = array()) {
    static $purifiers = array();

    $key = md5(implode(' ', $data_attributes));
    if (isset($purifiers[$key])) {
      return $purifiers[$key];
    }

    if (!class_exists('HTMLPurifier')) {
      $purifiers[$key] = false;
      return $purifiers[$key];
    }

    $config = HTMLPurifier_Config::createDefault();
    // no file cache in the admin, the definition costs about 30 ms per request
    $config->set('Cache.DefinitionImpl', null);
    $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
    $config->set('Attr.EnableID', true);
    $config->set('Attr.AllowedFrameTargets', array('_blank'));
    $config->set('HTML.TargetNoreferrer', false);
    $config->set('HTML.TargetNoopener', false);
    // shop texts are written by the shop owner, so layout css and forms stay allowed
    $config->set('CSS.Trusted', true);
    $config->set('HTML.Forms', true);
    $config->set('HTML.AllowedCommentsRegexp', '%^[^<>]*$%s');
    $config->set('HTML.SafeIframe', true);
    $config->set('URI.SafeIframeRegexp', '%^https://%');
    $config->set('URI.AllowedSchemes', array('http' => true, 'https' => true, 'mailto' => true, 'tel' => true, 'data' => true));
    // lazy loading images have no src of their own
    $config->set('Core.RemoveInvalidImg', false);
    $config->set('HTML.DefinitionID', 'modified-shop-content');
    $config->set('HTML.DefinitionRev', 2);

    if ($definition = $config->maybeGetRawHTMLDefinition()) {
      // html5 elements the library does not know
      foreach (array('section', 'article', 'aside', 'header', 'footer', 'nav', 'main', 'figure', 'details', 'summary', 'picture', 'video', 'audio', 'canvas') as $element) {
        $definition->addElement($element, 'Block', 'Flow', 'Common');
      }
      $definition->addElement('figcaption', 'Block', 'Flow', 'Common');
      foreach (array('mark', 'time') as $element) {
        $definition->addElement($element, 'Inline', 'Inline', 'Common');
      }
      $definition->addElement('source', 'Inline', 'Empty', 'Common', array('src' => 'URI', 'srcset' => 'Text', 'sizes' => 'Text', 'type' => 'Text', 'media' => 'Text'));
      $definition->addElement('track', 'Inline', 'Empty', 'Common', array('src' => 'URI', 'kind' => 'Text', 'srclang' => 'Text', 'label' => 'Text', 'default' => 'Bool#default'));

      $definition->addAttribute('img', 'srcset', 'Text');
      $definition->addAttribute('img', 'sizes', 'Text');
      $definition->addAttribute('img', 'loading', 'Enum#lazy,eager');
      $definition->addAttribute('img', 'decoding', 'Enum#sync,async,auto');
      foreach (array('video', 'audio') as $element) {
        $definition->addAttribute($element, 'src', 'URI');
        $definition->addAttribute($element, 'controls', 'Bool#controls');
        $definition->addAttribute($element, 'autoplay', 'Bool#autoplay');
        $definition->addAttribute($element, 'muted', 'Bool#muted');
        $definition->addAttribute($element, 'loop', 'Bool#loop');
        $definition->addAttribute($element, 'preload', 'Enum#none,metadata,auto');
      }
      $definition->addAttribute('video', 'poster', 'URI');
      $definition->addAttribute('video', 'playsinline', 'Bool#playsinline');
      $definition->addAttribute('video', 'width', 'Length');
      $definition->addAttribute('video', 'height', 'Length');

      $data_value = new xtc_data_attribute_value();
      foreach ($data_attributes as $attribute) {
        $definition->info_global_attr[trim($attribute)] = $data_value;
      }
    }

    // the finished definition, the modules add their own rules while it is built
    $html = $config->getHTMLDefinition();
    if (isset($html->info['img'])) {
      // an image may come without src, the lazy loading script fills it in
      $html->info['img']->required_attr = array();
      foreach (array('attr_transform_pre', 'attr_transform_post') as $stage) {
        foreach ($html->info['img']->$stage as $index => $transform) {
          if ($transform instanceof HTMLPurifier_AttrTransform_ImgRequired) {
            unset($html->info['img']->{$stage}[$index]);
          }
        }
      }
    }

    if ($css = $config->getCSSDefinition()) {
      $layout = new xtc_css_layout_value();
      $css->info['display'] = new HTMLPurifier_AttrDef_Enum(array('inline', 'block', 'list-item', 'run-in', 'compact', 'marker', 'table', 'inline-block', 'inline-table', 'table-row-group', 'table-header-group', 'table-footer-group', 'table-row', 'table-column-group', 'table-column', 'table-cell', 'table-caption', 'flex', 'inline-flex', 'grid', 'inline-grid', 'contents', 'flow-root', 'none'));
      foreach (array('gap', 'row-gap', 'column-gap', 'flex', 'flex-basis', 'flex-grow', 'flex-shrink', 'flex-flow', 'order',
                     'grid-template-columns', 'grid-template-rows', 'grid-template-areas', 'grid-template', 'grid-area',
                     'grid-column', 'grid-column-start', 'grid-column-end', 'grid-row', 'grid-row-start', 'grid-row-end',
                     'grid-auto-flow', 'grid-auto-columns', 'grid-auto-rows', 'place-items', 'place-content', 'place-self',
                     'object-fit', 'object-position', 'box-sizing', 'max-inline-size', 'inline-size',
                     'flex-direction', 'flex-wrap', 'justify-content', 'justify-items', 'justify-self',
                     'align-items', 'align-content', 'align-self') as $property) {
        $css->info[$property] = $layout;
      }
    }

    $purifiers[$key] = new HTMLPurifier($config);

    return $purifiers[$key];
  }
