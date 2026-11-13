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
      protected $allow_strings = false;

      public function __construct($allow_strings = false) {
        $this->allow_strings = $allow_strings;
      }

      public function validate($string, $config, $context) {
        $string = strtolower(trim($this->parseCDATA($string)));

        if ($string === '' || strlen($string) > 255) {
          return false;
        }
        // layout values only, no escapes; quoted names only for grid-template-areas
        $chars = (($this->allow_strings) ? '/^[a-z0-9 .\'"_-]+$/' : '/^[a-z0-9 ,.%\/()*+#_-]+$/');
        if (!preg_match($chars, $string) || strpos($string, '/*') !== false || strpos($string, '*/') !== false) {
          return false;
        }
        // these functions load content or run code
        if (preg_match('/(url|image|image-set|element|attr|counter|local|src|expression)\s*\(/', $string)) {
          return false;
        }

        return $string;
      }
    }

    // text attributes like class or title: scripts of the templates put them into html strings
    class xtc_plain_attribute_value extends HTMLPurifier_AttrDef {
      protected $inner;

      public function __construct($inner) {
        $this->inner = $inner;
      }

      public function validate($string, $config, $context) {
        $string = $this->inner->validate($string, $config, $context);

        if ($string === false) {
          return false;
        }

        return str_replace(array('<', '>', '"', "'"), '', $string);
      }
    }

    // data attributes: scripts read them as options, parse json in them and turn the result
    // into html or into attributes of an element they build
    class xtc_data_attribute_value extends HTMLPurifier_AttrDef_Text {
      public function validate($string, $config, $context) {
        $string = parent::validate($string, $config, $context);

        if ($string === false) {
          return false;
        }
        // no markup and no json escapes, which JSON.parse would turn into markup
        if (strpos($string, '<') !== false || strpos($string, '>') !== false || strpos($string, '\\') !== false) {
          return false;
        }
        // browsers ignore control characters and whitespace in the scheme
        $flat = strtolower(preg_replace('/[\x00-\x20]+/', '', $string));
        if (strpos($flat, 'javascript:') !== false
            || strpos($flat, 'vbscript:') !== false
            || strpos($flat, 'data:text/html') !== false
            )
        {
          return false;
        }
        // an event name as an option key or as an assignment, e.g. {"onerror": "..."}
        if (preg_match('/on(abort|animation[a-z]+|auxclick|before[a-z]+|blur|cancel|canplay[a-z]*|change|click|close|'
                       .'contextmenu|copy|cuechange|cut|dblclick|drag[a-z]*|drop|durationchange|emptied|ended|error|'
                       .'focus[a-z]*|formdata|fullscreen[a-z]+|gotpointercapture|hashchange|input|invalid|key[a-z]+|'
                       .'load[a-z]*|lostpointercapture|message[a-z]*|mouse[a-z]+|offline|online|page[a-z]+|paste|pause|'
                       .'play[a-z]*|pointer[a-z]+|popstate|progress|ratechange|reset|resize|scroll[a-z]*|seek[a-z]+|'
                       .'select[a-z]*|slotchange|stalled|storage|submit|suspend|timeupdate|toggle|touch[a-z]+|'
                       .'transition[a-z]+|unload|volumechange|waiting|webkit[a-z]+|wheel)\s*(["\']\s*:|=)/i', $string)) {
          return false;
        }

        return $string;
      }
    }
  }


  function xtc_remove_active_content_available() {
    return class_exists('HTMLPurifier');
  }


  function xtc_remove_active_content($value, &$removed = false) {
    static $cache = array();

    if (is_array($value)) {
      foreach ($value as $key => $val) {
        $value[$key] = xtc_remove_active_content($val, $removed);
      }
      return $value;
    }

    if (!is_string($value) || strpos($value, '<') === false || !xtc_remove_active_content_available()) {
      return $value;
    }

    // $_POST and $_REQUEST carry the same values
    $key = md5($value);
    if (isset($cache[$key])) {
      if ($cache[$key][1] === true) {
        $removed = true;
      }
      return $cache[$key][0];
    }

    // a text without special characters says nothing about the charset of the shop, so the
    // library writes every character an entity expansion adds as an entity: correct in both
    $ascii = !preg_match('/[\x80-\xFF]/', $value);
    $charset = (($ascii || !function_exists('mb_check_encoding') || mb_check_encoding($value, 'UTF-8')) ? 'UTF-8' : 'ISO-8859-15');
    $html = (($charset != 'UTF-8') ? xtc_remove_active_content_convert($value, 'UTF-8', $charset) : $value);

    // the data and aria attributes of this text
    $attribute_names = array();
    if (preg_match_all('/\s(data|aria)-[a-z0-9_-]+/i', $html, $matches)) {
      $attribute_names = array_slice(array_unique(array_map('strtolower', array_map('trim', $matches[0]))), 0, 100);
    }

    $purifier = xtc_remove_active_content_purifier($attribute_names, $ascii);
    $clean = $purifier->purify($html);

    // a note only when an element, attribute, style property or comment is gone,
    // not for a mere normalization of the markup
    $before = array_count_values(xtc_remove_active_content_fingerprint($html, $purifier->config));
    $after = array_count_values(xtc_remove_active_content_fingerprint($clean, $purifier->config));
    $changed = false;
    foreach ($before as $item => $count) {
      if (!isset($after[$item]) || $after[$item] < $count) {
        $changed = true;
        break;
      }
    }
    // tags the lexer drops before tokenizing, e.g. a body tag with attributes
    if (count($before) == 0 && $clean !== $html && preg_match('/<[a-z]/i', $html)) {
      $changed = true;
    }
    if ($changed === true) {
      $removed = true;
    }

    $clean = (($charset != 'UTF-8') ? xtc_remove_active_content_convert($clean, $charset, 'UTF-8') : $clean);
    $cache[$key] = array($clean, $changed);

    return $clean;
  }


  function xtc_remove_active_content_fingerprint($html, $config) {
    $items = array();
    $tokens = HTMLPurifier_Lexer::create($config)->tokenizeHTML($html, $config, new HTMLPurifier_Context());

    foreach ($tokens as $token) {
      if ($token instanceof HTMLPurifier_Token_Comment) {
        $items[] = '#comment';
      } elseif ($token instanceof HTMLPurifier_Token_Start || $token instanceof HTMLPurifier_Token_Empty) {
        $name = strtolower($token->name);
        $items[] = $name;
        foreach ($token->attr as $attribute => $value) {
          $attribute = strtolower($attribute);
          $items[] = $name.'@'.$attribute;
          // markup characters that a text attribute loses count as a removal as well
          if ($attribute != 'style' && preg_match('/[<>"\']/', $value)) {
            $items[] = $name.'@'.$attribute.'#markup';
          }
          if ($attribute == 'style') {
            foreach (explode(';', $value) as $declaration) {
              $property = strtolower(trim(strstr($declaration.':', ':', true)));
              if ($property != '') {
                $items[] = $name.'@style:'.$property;
              }
            }
          }
        }
      }
    }

    return $items;
  }


  function xtc_remove_active_content_convert($value, $to, $from) {
    if (!function_exists('mb_convert_encoding')) {
      return $value;
    }
    // a character the target charset has no place for stays readable as an entity
    $substitute = mb_substitute_character();
    mb_substitute_character('entity');
    $value = mb_convert_encoding($value, $to, $from);
    mb_substitute_character($substitute);

    return $value;
  }


  function xtc_remove_active_content_purifier($attribute_names = array(), $escape_non_ascii = false) {
    static $purifiers = array();
    static $known_names = array();

    // one definition per request that grows with the attribute names, not one per text
    $new_names = array_diff($attribute_names, $known_names);
    if (count($new_names) > 0) {
      $known_names = array_values(array_unique(array_merge($known_names, $new_names)));
      $purifiers = array();
    }
    $key = (($escape_non_ascii) ? 'ascii' : 'utf8');
    if (isset($purifiers[$key])) {
      return $purifiers[$key];
    }

    $config = HTMLPurifier_Config::createDefault();
    // no file cache in the admin, the definition costs about 30 ms per request
    $config->set('Cache.DefinitionImpl', null);
    $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
    $config->set('Core.EscapeNonASCIICharacters', $escape_non_ascii);
    $config->set('Attr.EnableID', true);
    $config->set('Attr.AllowedFrameTargets', array('_blank', '_self', '_top', '_parent'));
    $config->set('Attr.AllowedRel', array('alternate', 'author', 'bookmark', 'external', 'help', 'license', 'me', 'next', 'nofollow', 'noopener', 'noreferrer', 'opener', 'prev', 'search', 'sponsored', 'tag', 'ugc'));
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
    $config->set('HTML.DefinitionRev', 3);

    if ($definition = $config->maybeGetRawHTMLDefinition()) {
      // html5 elements the library does not know
      foreach (array('section', 'article', 'aside', 'header', 'footer', 'nav', 'main', 'figure', 'details', 'summary') as $element) {
        $definition->addElement($element, 'Block', 'Flow', 'Common');
      }
      // inline in html5, so they may sit inside a paragraph
      foreach (array('picture', 'video', 'audio', 'canvas') as $element) {
        $definition->addElement($element, 'Inline', 'Flow', 'Common');
      }
      $definition->addElement('figcaption', 'Block', 'Flow', 'Common');
      foreach (array('mark', 'time') as $element) {
        $definition->addElement($element, 'Inline', 'Inline', 'Common');
      }
      $definition->addElement('wbr', 'Inline', 'Empty', 'Common');
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

      $definition->addAttribute('iframe', 'allow', 'Text');
      $definition->addAttribute('iframe', 'allowfullscreen', 'Bool#allowfullscreen');
      $definition->addAttribute('iframe', 'loading', 'Enum#lazy,eager');
      $definition->addAttribute('iframe', 'referrerpolicy', 'Enum#no-referrer,no-referrer-when-downgrade,origin,origin-when-cross-origin,same-origin,strict-origin,strict-origin-when-cross-origin,unsafe-url');
      $definition->addAttribute('iframe', 'sandbox', 'Text');

      // html5 form fields
      $definition->addAttribute('input', 'type', 'Enum#text,password,checkbox,button,radio,submit,reset,file,hidden,image,email,tel,url,number,date,datetime-local,month,week,time,search,range,color');
      foreach (array('input', 'textarea') as $element) {
        $definition->addAttribute($element, 'placeholder', 'Text');
        $definition->addAttribute($element, 'required', 'Bool#required');
        $definition->addAttribute($element, 'autofocus', 'Bool#autofocus');
        $definition->addAttribute($element, 'autocomplete', 'Text');
        $definition->addAttribute($element, 'maxlength', 'Number');
        $definition->addAttribute($element, 'minlength', 'Number');
      }
      foreach (array('min', 'max', 'step', 'pattern', 'inputmode') as $attribute) {
        $definition->addAttribute('input', $attribute, 'Text');
      }
      $definition->addAttribute('select', 'required', 'Bool#required');
      $definition->addAttribute('form', 'novalidate', 'Bool#novalidate');
      $definition->addAttribute('form', 'autocomplete', 'Text');

      // global html5 attributes
      $definition->info_global_attr['role'] = new xtc_plain_attribute_value(new HTMLPurifier_AttrDef_Text());
      $definition->info_global_attr['tabindex'] = new HTMLPurifier_AttrDef_Integer();
      $definition->info_global_attr['itemprop'] = new xtc_plain_attribute_value(new HTMLPurifier_AttrDef_Text());
      $definition->info_global_attr['itemscope'] = new HTMLPurifier_AttrDef_HTML_Bool('itemscope');
      $definition->info_global_attr['itemtype'] = new HTMLPurifier_AttrDef_URI();
      $definition->info_global_attr['itemid'] = new HTMLPurifier_AttrDef_URI();
      $data_value = new xtc_data_attribute_value();
      $aria_value = new xtc_plain_attribute_value(new HTMLPurifier_AttrDef_Text());
      foreach ($known_names as $attribute) {
        $definition->info_global_attr[$attribute] = ((strpos($attribute, 'aria-') === 0) ? $aria_value : $data_value);
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
    if (isset($html->info['a']) && isset($html->info['div'])) {
      // html5 lets a link wrap blocks, the library would split it into several links
      $html->info['a']->child = clone $html->info['div']->child;
    }
    // text attributes lose markup characters, see xtc_plain_attribute_value
    foreach ($html->info as $tag => $element) {
      foreach ($element->attr as $attribute => $def) {
        if (($def instanceof HTMLPurifier_AttrDef_Text || $def instanceof HTMLPurifier_AttrDef_HTML_Nmtokens)
            && !($def instanceof xtc_data_attribute_value)
            )
        {
          $html->info[$tag]->attr[$attribute] = new xtc_plain_attribute_value($def);
        }
      }
    }
    foreach ($html->info_global_attr as $attribute => $def) {
      if (($def instanceof HTMLPurifier_AttrDef_Text || $def instanceof HTMLPurifier_AttrDef_HTML_Nmtokens)
          && !($def instanceof xtc_data_attribute_value)
          )
      {
        $html->info_global_attr[$attribute] = new xtc_plain_attribute_value($def);
      }
    }

    if ($css = $config->getCSSDefinition()) {
      $layout = new xtc_css_layout_value();
      $css->info['display'] = new HTMLPurifier_AttrDef_Enum(array('inline', 'block', 'list-item', 'run-in', 'compact', 'marker', 'table', 'inline-block', 'inline-table', 'table-row-group', 'table-header-group', 'table-footer-group', 'table-row', 'table-column-group', 'table-column', 'table-cell', 'table-caption', 'flex', 'inline-flex', 'grid', 'inline-grid', 'contents', 'flow-root', 'none'));
      foreach (array('gap', 'row-gap', 'column-gap', 'flex', 'flex-basis', 'flex-grow', 'flex-shrink', 'flex-flow', 'order',
                     'grid-template-columns', 'grid-template-rows', 'grid-template', 'grid-area',
                     'grid-column', 'grid-column-start', 'grid-column-end', 'grid-row', 'grid-row-start', 'grid-row-end',
                     'grid-auto-flow', 'grid-auto-columns', 'grid-auto-rows', 'place-items', 'place-content', 'place-self',
                     'object-fit', 'object-position', 'box-sizing', 'max-inline-size', 'inline-size',
                     'flex-direction', 'flex-wrap', 'justify-content', 'justify-items', 'justify-self',
                     'align-items', 'align-content', 'align-self') as $property) {
        $css->info[$property] = $layout;
      }
      $css->info['grid-template-areas'] = new xtc_css_layout_value(true);
    }

    // the data scheme checks the image itself, webp is as safe as the three default types
    $scheme = HTMLPurifier_URISchemeRegistry::instance()->getScheme('data', $config, new HTMLPurifier_Context());
    if (is_object($scheme) && isset($scheme->allowed_types)) {
      $scheme->allowed_types['image/webp'] = true;
    }

    $purifiers[$key] = new HTMLPurifier($config);

    return $purifiers[$key];
  }
