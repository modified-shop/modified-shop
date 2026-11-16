<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  /**
   * filter_external_html()
   *
   * builds the markup of an external source again from a fixed set of elements and attributes
   *
   * @param string $html
   * @param string $charset charset of $html, the result is plain ASCII
   * @param bool $forms keep input and textarea fields
   * @param string $css_scope selector that every rule of a delivered style block is bound to
   * @return string
   */
  function filter_external_html($html, $charset = 'UTF-8', $forms = false, $css_scope = '') {
    if (!is_string($html) || trim($html) === '') {
      return '';
    }

    // a less-than sign before a blank is text, the parser would read it as a tag
    $html = preg_replace('/<(?=\s)/', '&lt;', filter_external_utf8($html, $charset));

    if (!class_exists('DOMDocument') || strlen($html) > 1048576) {
      return filter_external_text(strip_tags($html));
    }

    $previous = libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    // plain ASCII, so the parser has no charset to guess
    $loaded = $dom->loadHTML('<html><body>'.mb_encode_numericentity($html, array(0x80, 0x10FFFF, 0, 0x1FFFFF), 'UTF-8').'</body></html>', LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    $body = ($loaded === true) ? $dom->getElementsByTagName('body')->item(0) : null;
    if ($body === null) {
      return filter_external_text(strip_tags($html));
    }

    $state = array(
      'forms' => ($forms === true),
      'classes' => array(),
      'count' => 0,
    );

    $css = '';
    if (preg_match('/^\.[a-zA-Z_][a-zA-Z0-9_-]*$/D', (string)$css_scope)) {
      foreach ($dom->getElementsByTagName('style') as $style) {
        $css .= filter_external_css($style->textContent, $css_scope, $state['classes']);
      }
    }

    $output = '';
    foreach ($body->childNodes as $child) {
      $output .= filter_external_node($child, $state, 0);
    }

    if ($css != '') {
      $output = '<style>'.$css.'</style>'.$output;
    }

    return mb_encode_numericentity($output, array(0x80, 0x10FFFF, 0, 0x1FFFFF), 'UTF-8');
  }


  /**
   * filter_external_text()
   *
   * @param string $text
   * @param string $charset charset of $text, the result is plain ASCII
   * @param bool $double_encode false keeps entities the text already contains
   * @return string
   */
  function filter_external_text($text, $charset = 'UTF-8', $double_encode = true) {
    if (!is_scalar($text) || (string)$text === '') {
      return '';
    }

    $text = htmlspecialchars(filter_external_utf8((string)$text, $charset), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', $double_encode);

    return mb_encode_numericentity($text, array(0x80, 0x10FFFF, 0, 0x1FFFFF), 'UTF-8');
  }


  /**
   * filter_external_url()
   *
   * @param string $url
   * @param array $schemes
   * @param bool $own_host accept modified-shop.org and its subdomains only
   * @return string empty if the url is not accepted
   */
  function filter_external_url($url, $schemes = array('http', 'https'), $own_host = false) {
    if (!is_string($url)) {
      return '';
    }

    // a browser encodes a blank the same way, but drops tabs and line breaks
    $url = str_replace(' ', '%20', trim($url));
    if ($url === ''
        || strlen($url) > 2000
        || preg_match('/[\x00-\x20\x7f"\'<>`\\\\]/', $url)
        )
    {
      return '';
    }

    if (strpos($url, '//') === 0) {
      $url = 'https:'.$url;
    }

    if (!preg_match('/^([a-zA-Z][a-zA-Z0-9+.-]*):(.*)$/D', $url, $parts)) {
      return '';
    }

    $scheme = strtolower($parts[1]);
    if (!in_array($scheme, $schemes, true)) {
      return '';
    }

    if ($scheme == 'mailto') {
      return (preg_match('/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+$/D', $parts[2])) ? 'mailto:'.$parts[2] : '';
    }

    if ($scheme != 'http' && $scheme != 'https') {
      return '';
    }

    // no user info, the host ends at the first slash, question mark or hash
    if (!preg_match('/^\/\/([A-Za-z0-9.-]+)(?::[0-9]{1,5})?(?:[\/?#]|$)/', $parts[2], $host)) {
      return '';
    }

    $host = strtolower($host[1]);
    if ($own_host === true
        && $host != 'modified-shop.org'
        && substr($host, -18) != '.modified-shop.org'
        )
    {
      return '';
    }

    return $scheme.':'.$parts[2];
  }


  /**
   * filter_external_utf8()
   *
   * @param string $string
   * @param string $charset
   * @return string valid UTF-8 without control characters
   */
  function filter_external_utf8($string, $charset = 'UTF-8') {
    $charset = strtoupper(trim((string)$charset));

    if ($charset != 'UTF-8' && $charset != 'UTF8') {
      $string = mb_convert_encoding($string, 'UTF-8', (in_array($charset, array('ISO-8859-1', 'ISO-8859-15', 'WINDOWS-1252'), true) ? $charset : 'ISO-8859-15'));
    } elseif (!mb_check_encoding($string, 'UTF-8')) {
      $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
    }

    return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $string);
  }


  /**
   * filter_external_node()
   *
   * @param DOMNode $node
   * @param array $state
   * @param int $depth
   * @return string
   */
  function filter_external_node($node, &$state, $depth) {
    static $elements, $form_elements, $void_elements, $removed_elements;

    if (!isset($elements)) {
      $elements = array_flip(array(
        'a', 'abbr', 'b', 'big', 'blockquote', 'br', 'caption', 'center', 'cite', 'code', 'dd', 'del', 'div', 'dl', 'dt', 'em',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'i', 'img', 'ins', 'kbd', 'label', 'li', 'ol', 'p', 'pre', 'q', 's', 'small',
        'span', 'strike', 'strong', 'sub', 'sup', 'table', 'tbody', 'td', 'tfoot', 'th', 'thead', 'tr', 'tt', 'u', 'ul',
      ));
      $form_elements = array_flip(array('input', 'textarea'));
      $void_elements = array_flip(array('br', 'hr', 'img', 'input'));
      // these go together with their content, every other unknown element only loses its tags
      $removed_elements = array_flip(array(
        'applet', 'audio', 'base', 'button', 'canvas', 'embed', 'frame', 'frameset', 'head', 'iframe', 'link', 'listing', 'math',
        'meta', 'noembed', 'noframes', 'noscript', 'object', 'option', 'plaintext', 'script', 'select', 'style', 'svg', 'template',
        'title', 'video', 'xmp',
      ));
    }

    if ($depth > 100 || ++$state['count'] > 20000) {
      return '';
    }

    if ($node->nodeType == XML_TEXT_NODE || $node->nodeType == XML_CDATA_SECTION_NODE) {
      return htmlspecialchars((string)$node->nodeValue, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    if ($node->nodeType != XML_ELEMENT_NODE) {
      return '';
    }

    $name = strtolower($node->nodeName);
    $is_form_element = isset($form_elements[$name]);

    if (isset($removed_elements[$name])
        || ($is_form_element && $state['forms'] !== true)
        )
    {
      return '';
    }

    $content = '';
    if ($name == 'textarea') {
      $content = htmlspecialchars((string)$node->textContent, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    } elseif (!isset($void_elements[$name])) {
      foreach ($node->childNodes as $child) {
        $content .= filter_external_node($child, $state, $depth + 1);
      }
    }

    if (!isset($elements[$name]) && !$is_form_element) {
      return $content;
    }

    $attributes = filter_external_attributes($node, $name, $state);
    if ($attributes === false) {
      // a link without a target keeps its text, an image or a field without a source or name is useless
      return ($name == 'a') ? $content : '';
    }

    $tag = '<'.$name;
    foreach ($attributes as $attribute => $value) {
      $tag .= ' '.$attribute.'="'.htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'"';
    }
    $tag .= '>';

    if (isset($void_elements[$name])) {
      return $tag;
    }

    return $tag.$content.'</'.$name.'>';
  }


  /**
   * filter_external_attributes()
   *
   * @param DOMElement $node
   * @param string $name
   * @param array $state
   * @return array|false unescaped attributes, false if the element has to go
   */
  function filter_external_attributes($node, $name, &$state) {
    $attributes = array();

    switch ($name) {
      case 'a':
        $href = filter_external_url($node->getAttribute('href'), array('http', 'https', 'mailto'));
        if ($href == '') {
          return false;
        }
        $attributes['href'] = $href;
        $attributes['target'] = '_blank';
        $attributes['rel'] = 'noopener noreferrer';
        break;

      case 'img':
        $src = filter_external_image($node->getAttribute('src'));
        if ($src == '') {
          return false;
        }
        $attributes['src'] = $src;
        $attributes['alt'] = mb_substr($node->getAttribute('alt'), 0, 500, 'UTF-8');
        $attributes['referrerpolicy'] = 'no-referrer';
        foreach (array('width', 'height') as $attribute) {
          if (preg_match('/^[0-9]{1,4}%?$/D', $node->getAttribute($attribute))) {
            $attributes[$attribute] = $node->getAttribute($attribute);
          }
        }
        break;

      case 'input':
        $type = strtolower($node->getAttribute('type'));
        if ($type == '') {
          $type = 'text';
        }
        if (!in_array($type, array('text', 'email', 'tel', 'number', 'checkbox'), true)) {
          return false;
        }
        $attributes['type'] = $type;
        // no break

      case 'textarea':
        if (!preg_match('/^[A-Za-z0-9_]{1,40}$/D', $node->getAttribute('name'))) {
          return false;
        }
        $attributes['name'] = $node->getAttribute('name');
        if ($name == 'input' && $node->hasAttribute('value')) {
          $attributes['value'] = $node->getAttribute('value');
        }
        foreach (array('size', 'maxlength', 'rows', 'cols') as $attribute) {
          if (preg_match('/^[0-9]{1,5}$/D', $node->getAttribute($attribute))) {
            $attributes[$attribute] = $node->getAttribute($attribute);
          }
        }
        foreach (array('checked', 'required') as $attribute) {
          if ($node->hasAttribute($attribute)) {
            $attributes[$attribute] = $attribute;
          }
        }
        if ($node->getAttribute('placeholder') != '') {
          $attributes['placeholder'] = mb_substr($node->getAttribute('placeholder'), 0, 500, 'UTF-8');
        }
        break;

      case 'td':
      case 'th':
        foreach (array('colspan', 'rowspan') as $attribute) {
          if (preg_match('/^[0-9]{1,3}$/D', $node->getAttribute($attribute))) {
            $attributes[$attribute] = $node->getAttribute($attribute);
          }
        }
        break;
    }

    if (in_array($name, array('div', 'p', 'td', 'th', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'), true)
        && in_array(strtolower($node->getAttribute('align')), array('left', 'right', 'center', 'justify'), true)
        )
    {
      $attributes['align'] = strtolower($node->getAttribute('align'));
    }

    if ($node->getAttribute('title') != '') {
      $attributes['title'] = mb_substr($node->getAttribute('title'), 0, 500, 'UTF-8');
    }

    $classes = array();
    foreach (preg_split('/\s+/', $node->getAttribute('class'), 20, PREG_SPLIT_NO_EMPTY) as $class) {
      // classes of the forum markup, of code samples and those a delivered style block defines
      if (preg_match('/^(?:bbc_[a-z_]{1,30}|language-[a-z0-9]{1,20})$/D', $class) || isset($state['classes'][$class])) {
        $classes[] = $class;
      }
    }
    if (count($classes) > 0) {
      $attributes['class'] = implode(' ', $classes);
    }

    $style = filter_external_style($node->getAttribute('style'));
    if ($style != '') {
      $attributes['style'] = $style;
    }

    return $attributes;
  }


  /**
   * filter_external_image()
   *
   * @param string $src
   * @return string an admin image or an image on modified-shop.org, empty otherwise
   */
  function filter_external_image($src) {
    $src = trim((string)$src);

    if (strpos($src, '..') === false
        && preg_match('/^images\/[A-Za-z0-9_.\/-]+\.(?:png|gif|jpe?g|webp)$/D', $src)
        )
    {
      return $src;
    }

    if (stripos($src, 'http://') === 0) {
      $src = 'https://'.substr($src, 7);
    }

    return filter_external_url($src, array('https'), true);
  }


  /**
   * filter_external_style()
   *
   * @param string $style declarations of a style attribute or of a rule
   * @param bool $layout allow positioning, spacing and negative lengths
   * @return string
   */
  function filter_external_style($style, $layout = false) {
    static $properties, $layout_properties;

    if (!isset($properties)) {
      $properties = array_flip(array(
        'background', 'background-color', 'border', 'border-bottom', 'border-collapse', 'border-color', 'border-left', 'border-radius',
        'border-right', 'border-spacing', 'border-style', 'border-top', 'border-width', 'box-sizing', 'color', 'display', 'font-size',
        'font-style', 'font-weight', 'height', 'line-height', 'list-style-type', 'max-height', 'max-width', 'min-height', 'min-width',
        'text-align', 'text-decoration', 'text-transform', 'vertical-align', 'white-space', 'width',
      ));
      $layout_properties = array_flip(array(
        'bottom', 'clear', 'float', 'left', 'margin', 'margin-bottom', 'margin-left', 'margin-right', 'margin-top', 'opacity', 'overflow',
        'padding', 'padding-bottom', 'padding-left', 'padding-right', 'padding-top', 'position', 'right', 'top', 'transition',
      ));
    }

    $style = (string)$style;
    if ($style === '' || strlen($style) > 4000) {
      return '';
    }

    // colours, keywords, lengths and rgb(), so neither url() nor an escape can pass
    $number = (($layout === true) ? '-?' : '').'(?:[0-9]{1,3}(?:\.[0-9]{1,4})?|\.[0-9]{1,4})(?:px|em|rem|%|pt|s|ms)?';
    $token = '(?:#[0-9a-fA-F]{3,8}|[a-zA-Z]+(?:-[a-zA-Z]+)*|'.$number.'|rgba?\(\s*[0-9.]+%?(?:\s*,\s*[0-9.]+%?){2,3}\s*\))';
    $pattern = '/^'.$token.'(?:[ ,]+'.$token.')*(\s*!important)?$/D';

    $declarations = array();
    foreach (explode(';', $style) as $declaration) {
      $declaration = explode(':', $declaration, 2);
      if (count($declaration) != 2) {
        continue;
      }

      $property = strtolower(trim($declaration[0]));
      $value = trim($declaration[1]);

      $base_property = preg_replace('/^-(?:moz|webkit|o|ms)-/', '', $property);
      if (!isset($properties[$base_property])
          && ($layout !== true || !isset($layout_properties[$base_property]))
          )
      {
        continue;
      }

      if (strlen($value) > 100 || !preg_match($pattern, $value)) {
        continue;
      }

      if ($base_property == 'position' && !preg_match('/^(?:relative|absolute|static)$/iD', $value)) {
        continue;
      }

      $declarations[] = $property.':'.$value;
    }

    return implode(';', $declarations);
  }


  /**
   * filter_external_css()
   *
   * @param string $css content of a style element
   * @param string $scope selector every rule is bound to
   * @param array $classes collects the class names the accepted rules use
   * @return string
   */
  function filter_external_css($css, $scope, &$classes) {
    $css = (string)$css;

    // at-rules, escapes, comments and markup are not part of the accepted subset
    if (strlen($css) > 65536 || preg_match('/[@\\\\<]|\/\*/', $css)) {
      return '';
    }

    $compound = '(?:[a-zA-Z][a-zA-Z0-9]*|\.[a-zA-Z_][a-zA-Z0-9_-]*)(?:\.[a-zA-Z_][a-zA-Z0-9_-]*)*(?::hover)?';
    $pattern = '/^'.$compound.'(?:\s*>\s*'.$compound.'|\s+'.$compound.')*$/D';

    $output = '';
    foreach (array_slice(explode('}', $css), 0, 500) as $rule) {
      $rule = explode('{', $rule);
      if (count($rule) != 2) {
        continue;
      }

      $selectors = array();
      foreach (explode(',', $rule[0]) as $selector) {
        $selector = trim($selector);
        if (!preg_match($pattern, $selector)) {
          continue 2;
        }
        $selectors[] = $scope.' '.$selector;
      }

      $declarations = filter_external_style($rule[1], true);
      if ($declarations == '') {
        continue;
      }

      preg_match_all('/\.([a-zA-Z_][a-zA-Z0-9_-]*)/', $rule[0], $matches);
      foreach ($matches[1] as $class) {
        $classes[$class] = true;
      }

      $output .= implode(',', $selectors).'{'.$declarations.'}';
    }

    return $output;
  }
