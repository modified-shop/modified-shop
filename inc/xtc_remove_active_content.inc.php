<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

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

    $purifier = xtc_remove_active_content_purifier();
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


  function xtc_remove_active_content_purifier() {
    static $purifier = null;

    if ($purifier !== null) {
      return $purifier;
    }

    $library = DIR_FS_CATALOG.'includes/external/htmlpurifier/library/HTMLPurifier.auto.php';
    if (!is_file($library)) {
      $purifier = false;
      return $purifier;
    }
    require_once($library);

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
    $config->set('HTML.DefinitionID', 'modified-shop-content');
    $config->set('HTML.DefinitionRev', 1);

    if ($definition = $config->maybeGetRawHTMLDefinition()) {
      // html5 elements the library does not know
      foreach (array('section', 'article', 'aside', 'header', 'footer', 'nav', 'main', 'figure', 'details', 'summary', 'picture', 'video', 'audio', 'canvas') as $element) {
        $definition->addElement($element, 'Block', 'Flow', 'Common');
      }
      $definition->addElement('figcaption', 'Block', 'Flow', 'Common');
      foreach (array('mark', 'time') as $element) {
        $definition->addElement($element, 'Inline', 'Inline', 'Common');
      }
      $definition->addElement('source', 'Inline', 'Empty', 'Common', array('src' => 'URI', 'srcset' => 'Text', 'type' => 'Text', 'media' => 'Text'));
      $definition->addElement('track', 'Inline', 'Empty', 'Common', array('src' => 'URI', 'kind' => 'Text', 'srclang' => 'Text', 'label' => 'Text'));
      $definition->addAttribute('img', 'srcset', 'Text');
      $definition->addAttribute('img', 'loading', 'Enum#lazy,eager');
      $definition->addAttribute('video', 'src', 'URI');
      $definition->addAttribute('video', 'poster', 'URI');
      $definition->addAttribute('video', 'controls', 'Bool#controls');
      $definition->addAttribute('audio', 'src', 'URI');
      $definition->addAttribute('audio', 'controls', 'Bool#controls');
    }

    $purifier = new HTMLPurifier($config);

    return $purifier;
  }

