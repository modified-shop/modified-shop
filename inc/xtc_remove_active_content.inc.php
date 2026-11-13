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

    // the session and its charset do not exist yet, so decide per value
    $charset = ((!function_exists('mb_check_encoding') || mb_check_encoding($value, 'UTF-8')) ? 'UTF-8' : 'ISO-8859-15');
    $html = (($charset != 'UTF-8') ? mb_convert_encoding($value, 'UTF-8', $charset) : $value);

    if (class_exists('Dom\HTMLDocument')) {
      // HTML5 parser, parses like the browser
      $doc = Dom\HTMLDocument::createFromString('<!DOCTYPE html><html><head></head><body></body></html>', LIBXML_NOERROR);
      $root = $doc->body;
      $root->innerHTML = $html;
      $scan = $root;
    } elseif (class_exists('DOMDocument')) {
      $doc = new DOMDocument();
      $errors = libxml_use_internal_errors(true);
      $loaded = $doc->loadHTML('<?xml encoding="UTF-8"><html><body>'.$html.'</body></html>', LIBXML_HTML_NODEFDTD | LIBXML_PARSEHUGE);
      $fatal = false;
      foreach (libxml_get_errors() as $error) {
        if ($error->level == LIBXML_ERR_FATAL) {
          $fatal = true;
        }
      }
      libxml_clear_errors();
      libxml_use_internal_errors($errors);
      $root = $doc->getElementsByTagName('body')->item(0);
      if ($loaded !== true || $fatal === true || $root === null) {
        $removed = true;
        return htmlspecialchars($value, ENT_QUOTES, $charset);
      }
      // libxml may move content out of the body, so check the whole document
      $scan = $doc->documentElement;
    } else {
      // no parser, keep the text visible but inactive
      $removed = true;
      return htmlspecialchars($value, ENT_QUOTES, $charset);
    }

    if (!xtc_remove_active_content_nodes($scan)) {
      return $value;
    }
    $removed = true;

    if ($root instanceof DOMElement) {
      foreach ($doc->documentElement->childNodes as $node) {
        if (!$node->isSameNode($root) && !($node->nodeName == 'head' && !$node->hasChildNodes())) {
          // content outside the body would get lost
          return htmlspecialchars($value, ENT_QUOTES, $charset);
        }
      }
      $html = '';
      foreach ($root->childNodes as $node) {
        $html .= $doc->saveHTML($node);
      }
    } else {
      $html = $root->innerHTML;
    }

    return (($charset != 'UTF-8') ? mb_convert_encoding($html, $charset, 'UTF-8') : $html);
  }


  function xtc_remove_active_content_nodes($root) {
    $blocked_elements = array('script', 'noscript', 'template', 'object', 'embed', 'applet', 'base', 'meta', 'link', 'frame', 'frameset', 'svg', 'math');
    $url_attributes = array('href', 'src', 'action', 'formaction', 'data', 'poster', 'background', 'xlink:href');
    $allowed_schemes = array('http', 'https', 'mailto', 'tel');

    $changed = false;
    $elements = array();
    foreach ($root->getElementsByTagName('*') as $element) {
      $elements[] = $element;
    }

    foreach ($elements as $element) {
      if ($element->parentNode === null) {
        continue;
      }
      $tag = strtolower($element->localName);
      if (in_array($tag, $blocked_elements)) {
        $element->parentNode->removeChild($element);
        $changed = true;
        continue;
      }

      $attributes = array();
      foreach ($element->attributes as $attribute) {
        $attributes[] = $attribute->nodeName;
      }
      foreach ($attributes as $name) {
        $attr = strtolower($name);
        $remove = false;
        if (substr($attr, 0, 2) == 'on' || $attr == 'srcdoc') {
          $remove = true;
        } elseif (in_array($attr, $url_attributes)) {
          // browsers ignore control characters and whitespace in the scheme
          $url = strtolower(preg_replace('/[\x00-\x20]+/', '', $element->getAttribute($name)));
          if (preg_match('/^([a-z][a-z0-9+.\-]*):/', $url, $match) && !in_array($match[1], $allowed_schemes)) {
            $remove = !($match[1] == 'data' && $attr == 'src' && in_array($tag, array('img', 'source')) && strpos($url, 'data:image/') === 0);
          }
        }
        if ($remove) {
          $element->removeAttribute($name);
          $changed = true;
        }
      }
    }

    return $changed;
  }
