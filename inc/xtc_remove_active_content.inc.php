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

    // the lightbox scripts of the templates load the address of an element with one of these
    // classes into the page, unless they take the address for an image
    class xtc_lightbox_class_transform extends HTMLPurifier_AttrTransform {
      // class => image types of its script; none for cboxElement, which colorbox sets itself
      // and opens with its default options wherever it finds it
      public static $images = array('cbimages' => 'gif|png|jpe?g|bmp|webp', 'thickbox' => 'gif|png|jpe?g|bmp', 'cboxelement' => '');
      protected $link = false;

      public function __construct($link = false) {
        $this->link = $link;
      }

      public function transform($attr, $config, $context) {
        if (!isset($attr['class'])) {
          return $attr;
        }

        $href = (($this->link && isset($attr['href'])) ? $attr['href'] : '');
        // a path below a host or below the shop, no fragment, no quote: script and browser read the same
        $chars = 'a-z0-9_.~!$&()*+,;=:@\/%-';
        $path = '/^(?:(?:https?:)?\/\/[a-z0-9.-]+(?::[0-9]+)?\/|\/(?!\/)|(?!\/)(?=[^:]*$))['.$chars.']*\.(?:';
        $query = ')(?:\?['.$chars.'?]*)?$/Di';

        $classes = array();
        foreach (preg_split('/\s+/', $attr['class'], -1, PREG_SPLIT_NO_EMPTY) as $class) {
          $key = strtolower($class);
          if (isset(self::$images[$key])) {
            if (self::$images[$key] == '' || !preg_match($path.self::$images[$key].$query, $href)) {
              continue;
            }
            if ($key == 'thickbox') {
              // thickbox pages on to every link with the same rel
              unset($attr['rel']);
            }
          }
          $classes[] = $class;
        }

        if (count($classes) > 0) {
          $attr['class'] = implode(' ', $classes);
        } else {
          unset($attr['class']);
        }

        return $attr;
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
      $attribute_names = array_unique(array_map('strtolower', array_map('trim', $matches[0])));
      // colorbox reads data-cbox-* as options that win over those of the template, the guarantee
      // labels read from data-guarantee-label-* where to fetch the markup of a label
      $attribute_names = array_slice(preg_grep('/^data-(cbox|guarantee-label)-/', $attribute_names, PREG_GREP_INVERT), 0, 100);
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



  /**
   * xtc_remove_active_content_keep()
   *
   * like xtc_remove_active_content(), for short texts such as names, labels and codes that are stored in
   * narrow columns or passed on as plain text: what the cleaner only spells its own way stays as typed,
   * because the cleaner writes &amp;, quotes and tag names its own way and the text would grow
   *
   * @param mixed $value text or array of texts
   * @param boolean $removed set to true when something was removed
   * @param mixed $max_length characters the text may have where it is stored, true: not more than it has
   * @return mixed
   */
  function xtc_remove_active_content_keep($value, &$removed = false, $max_length = 0) {
    if (is_array($value)) {
      foreach ($value as $key => $val) {
        $value[$key] = xtc_remove_active_content_keep($val, $removed, $max_length);
      }
      return $value;
    }

    if (!is_string($value) || strpos($value, '<') === false || !xtc_remove_active_content_available()) {
      return $value;
    }

    $changed = false;
    $clean = xtc_remove_active_content($value, $changed);
    if ($changed === true) {
      $removed = true;
    }
    $charset = xtc_remove_active_content_charset($value);

    // nothing removed and no tag with an attribute, comment or declaration: the text as it is,
    // unless it ends in an open tag, which the text that follows it in a page would complete
    if ($changed === false && !preg_match('/<[a-z\/][^>]*=|<[!?]|<(?:[a-z\/][^>]*)?$/i', $value)) {
      $kept = $value;
    } else {
      $kept = xtc_remove_active_content_merge($value, $clean, $charset);
    }

    $max_length = (($max_length === true) ? xtc_remove_active_content_length($value, $charset) : (int)$max_length);
    if ($max_length > 0 && xtc_remove_active_content_length($kept, $charset) > $max_length) {
      // first every part in its shorter spelling, then rather without the tags than cut off by the column
      $kept = xtc_remove_active_content_merge($value, $clean, $charset, true);
      if (xtc_remove_active_content_length($kept, $charset) > $max_length) {
        $kept = xtc_remove_active_content_text($value, $clean, $charset, $max_length);
        $removed = true;
      }
    }

    return $kept;
  }


  // the charset xtc_remove_active_content() reads a text in
  function xtc_remove_active_content_charset($value) {
    return ((!preg_match('/[\x80-\xFF]/', $value) || !function_exists('mb_check_encoding') || mb_check_encoding($value, 'UTF-8')) ? 'UTF-8' : 'ISO-8859-15');
  }


  // characters as the database counts them
  function xtc_remove_active_content_length($value, $charset = '') {
    if ($charset == '') {
      $charset = xtc_remove_active_content_charset($value);
    }

    return (($charset == 'UTF-8' && function_exists('mb_strlen')) ? mb_strlen($value, 'UTF-8') : strlen($value));
  }


  // whether two texts between tags say the same
  function xtc_remove_active_content_same_text($typed, $clean, $charset) {
    if ($typed === $clean) {
      return true;
    }
    if ($charset != 'UTF-8') {
      // the cleaner writes a character where the typed text has an entity
      $typed = xtc_remove_active_content_convert($typed, 'UTF-8', $charset);
      $clean = xtc_remove_active_content_convert($clean, 'UTF-8', $charset);
    }

    return (html_entity_decode($typed, ENT_QUOTES | ENT_HTML5, 'UTF-8') === html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
  }


  /**
   * xtc_remove_active_content_merge()
   *
   * the text of the cleaner with everything as typed that the cleaner only spells its own way: a browser may
   * read a tag differently than the cleaner, so a tag is taken as typed only when it is plainly written and
   * the cleaner kept its attributes and values; the text between the tags when the cleaner read the same
   *
   * @param string $value typed text
   * @param string $clean text of the cleaner
   * @param string $charset see xtc_remove_active_content_charset()
   * @param boolean $short of two spellings of a part the shorter one instead of the typed one
   * @return string
   */
  function xtc_remove_active_content_merge($value, $clean, $charset, $short = false) {
    $parts = preg_split('/(<[^>]*>)/', $clean, -1, PREG_SPLIT_DELIM_CAPTURE);
    if (!is_array($parts)) {
      return $clean;
    }

    // outside of tags an & before anything but a letter, a digit or # is read as the character itself
    foreach ($parts as $i => $part) {
      if ($i % 2 == 0) {
        $text = preg_replace('/&amp;(?![a-z0-9#])/i', '&', $part);
        $parts[$i] = ((is_string($text)) ? $text : $part);
      }
    }

    $typed = preg_split('/(<[^>]*>)/', $value, -1, PREG_SPLIT_DELIM_CAPTURE);
    $tags = xtc_remove_active_content_tags($value);
    if (is_array($typed) && is_array($tags) && count($typed) == count($parts)) {
      $merged = array();
      foreach ($parts as $i => $part) {
        if ($i % 2 == 0) {
          $piece = ((xtc_remove_active_content_same_text($typed[$i], $part, $charset)) ? $typed[$i] : false);
        } else {
          $piece = xtc_remove_active_content_merge_tag($typed[$i], $tags[$i], $part);
        }
        // the cleaner moved text or wrote another element: its text as a whole
        if ($piece === false) {
          return implode('', $parts);
        }
        if ($short === true && xtc_remove_active_content_length($part, $charset) < xtc_remove_active_content_length($piece, $charset)) {
          $piece = $part;
        }
        $merged[] = $piece;
      }

      return implode('', $merged);
    }

    return implode('', $parts);
  }


  /**
   * xtc_remove_active_content_merge_tag()
   *
   * a tag of the typed text and the tag the cleaner made of it as one tag
   *
   * @param string $typed tag as typed
   * @param array $tag the same tag from xtc_remove_active_content_tags()
   * @param string $clean tag of the cleaner
   * @return mixed tag, false when the cleaner wrote another element
   */
  function xtc_remove_active_content_merge_tag($typed, $tag, $clean) {
    if ($tag['tag'] === $clean) {
      return $typed;
    }
    if (!preg_match('/^<[a-z0-9]+/', $tag['tag'], $name)
        || !preg_match('/^(<[a-z0-9]+)((?: [a-z][a-z0-9:_-]*="[^"]*")*)(?: \/)?>$/D', $clean, $match)
        || $name[0] !== $match[1]
        )
    {
      return false;
    }
    preg_match_all('/ ([a-z][a-z0-9:_-]*)="([^"]*)"/', $match[2], $attributes, PREG_SET_ORDER);

    // an attribute the cleaner kept with its value is written as typed, the others as the cleaner writes them
    $merged = '';
    $same = (count($attributes) == count($tag['attributes']));
    $next = 0;
    foreach ($attributes as $k => $attribute) {
      $piece = $attribute[0];
      $kept = false;
      for ($i = $next, $n = count($tag['attributes']); $i < $n; $i++) {
        if ($tag['attributes'][$i][0] === $attribute[1]) {
          if (xtc_remove_active_content_same_value($attribute[1], $tag['attributes'][$i][1], $attribute[2])) {
            $piece = $tag['attributes'][$i][2];
            $kept = ($i == $k);
          }
          $next = $i + 1;
          break;
        }
      }
      if ($kept === false) {
        $same = false;
      }
      $merged .= $piece;
    }

    return (($same) ? $typed : $tag['open'].$merged.'>');
  }


  /**
   * xtc_remove_active_content_same_value()
   *
   * whether the cleaner changed nothing but the spelling of an attribute value
   *
   * @param string $name attribute name
   * @param string $typed value from xtc_remove_active_content_tags()
   * @param string $clean value of the cleaner
   * @return boolean
   */
  function xtc_remove_active_content_same_value($name, $typed, $clean) {
    if ($typed === $clean) {
      return true;
    }
    $space = '/[ \t\n\f\r]+/';
    if ($name == 'class') {
      return (preg_split($space, $typed, -1, PREG_SPLIT_NO_EMPTY) === preg_split($space, $clean, -1, PREG_SPLIT_NO_EMPTY));
    }
    // a word where the cleaner writes the number of a colour
    $colour = '/^[a-z]+\x01#[0-9a-f]{6}$/Di';
    if ($name == 'color' || $name == 'bgcolor') {
      return (preg_match($colour, $typed."\x01".$clean) == 1);
    }
    if ($name != 'style') {
      return false;
    }

    // the same declarations: the cleaner writes no spaces, lower case and a ; at the end
    $styles = array();
    foreach (array($typed, $clean) as $style) {
      $declarations = array();
      foreach (explode(';', $style) as $declaration) {
        $declaration = trim((string)preg_replace($space, ' ', $declaration), ' ');
        if ($declaration === '') {
          continue;
        }
        $pos = strpos($declaration, ':');
        if ($pos === false) {
          return false;
        }
        $declarations[] = array(strtolower(rtrim(substr($declaration, 0, $pos), ' ')), ltrim(substr($declaration, $pos + 1), ' '));
      }
      $styles[] = $declarations;
    }
    if (count($styles[0]) != count($styles[1])) {
      return false;
    }
    foreach ($styles[0] as $i => $declaration) {
      if ($declaration[0] !== $styles[1][$i][0]
          || (strtolower($declaration[1]) !== strtolower($styles[1][$i][1]) && !preg_match($colour, $declaration[1]."\x01".$styles[1][$i][1]))
          )
      {
        return false;
      }
    }

    return true;
  }


  /**
   * xtc_remove_active_content_text()
   *
   * the text between the tags, for a place that has no room for the text with its tags
   *
   * @param string $value typed text
   * @param string $clean text of the cleaner
   * @param string $charset see xtc_remove_active_content_charset()
   * @param int $max_length characters
   * @return string
   */
  function xtc_remove_active_content_text($value, $clean, $charset, $max_length) {
    $texts = array();
    foreach (array($clean, $value) as $html) {
      // a space where a line or a block ends, so that two words do not become one
      $text = preg_replace('/<\/?(?:br|hr|p|div|li|dd|dt|tr|td|th|h[1-6]|blockquote|pre|center|table|ul|ol|dl)(?![a-z0-9])[^>]*>/i', ' ', $html);
      $text = ((is_string($text)) ? preg_replace('/<[^>]*>/', '', $text) : $text);
      $text = ((is_string($text)) ? preg_replace('/ {2,}/', ' ', $text) : $text);
      $texts[] = ((is_string($text)) ? trim($text, ' ') : false);
    }
    // the cleaner writes a < only where a tag starts
    $text = (($texts[0] !== false) ? preg_replace('/&amp;(?![a-z0-9#])/i', '&', $texts[0]) : strip_tags($clean));
    if (!is_string($text)) {
      $text = strip_tags($clean);
    }
    // as typed when the cleaner read the same text
    if ($texts[1] !== false && strpos($texts[1], '<') === false && xtc_remove_active_content_same_text($texts[1], $text, $charset)) {
      $text = $texts[1];
    }

    if (xtc_remove_active_content_length($text, $charset) > $max_length) {
      $text = (($charset == 'UTF-8' && function_exists('mb_substr')) ? mb_substr($text, 0, $max_length, 'UTF-8') : substr($text, 0, $max_length));
      // not in the middle of an entity
      $short = preg_replace('/&[#a-z0-9]*$/i', '', $text);
      $text = ((is_string($short)) ? $short : $text);
    }

    return $text;
  }


  /**
   * xtc_remove_active_content_tags()
   *
   * the tags of a text in the spelling of the cleaner: names in lower case, one space, values in double quotes
   *
   * @param string $value
   * @return mixed array of tags by their position in the text, false when a tag is not a plainly written formatting tag
   */
  function xtc_remove_active_content_tags($value) {
    // elements whose content is plain text for every browser
    $plain = '(?:a|abbr|b|big|blockquote|br|center|cite|code|dd|del|div|dl|dt|em|font|h[1-6]|hr|i|img|ins|li|ol|p|pre|q|s|small|span|strike|strong|sub|sup|table|tbody|td|tfoot|th|thead|tr|tt|u|ul)';
    // the characters a browser takes as space inside a tag; \s would add the vertical tab
    $space = '[ \t\n\f\r]';
    // name="value", name='value' or name=value; a value without quotes ends at a space or at the end of the tag
    $attribute = $space.'+([a-z][a-z0-9-]*)'.$space.'*='.$space.'*(?:"([^"]*)"|\'([^\'"]*)\'|([^\s"\'=<>`]+))';

    $parts = preg_split('/(<[^>]*>)/', $value, -1, PREG_SPLIT_DELIM_CAPTURE);
    if (!is_array($parts)) {
      return false;
    }

    $tags = array();
    foreach ($parts as $i => $part) {
      if ($i % 2 == 0) {
        // a < that opens no tag
        if (strpos($part, '<') !== false) {
          return false;
        }
        continue;
      }
      if (preg_match('/^<\/('.$plain.')'.$space.'*>$/Di', $part, $match)) {
        $tags[$i] = array('tag' => '</'.strtolower($match[1]).'>', 'attributes' => array());
      } elseif (preg_match('/^<('.$plain.')((?:'.$attribute.')*)'.$space.'*\/?>$/Di', $part, $match)) {
        $tag = '<'.strtolower($match[1]);
        $attributes = array();
        if ($match[2] != '' && preg_match_all('/'.$attribute.'/i', $match[2], $found_attributes, PREG_SET_ORDER)) {
          foreach ($found_attributes as $found) {
            $text = ((isset($found[4]) && $found[4] != '') ? $found[4] : ((isset($found[3]) && $found[3] != '') ? $found[3] : $found[2]));
            // an & is the character itself before anything but a letter, a digit or #, and in a value also before name=
            $text = preg_replace('/&(?![a-z0-9#])|&(?=[a-z0-9]+=)/i', '&amp;', $text);
            // name, value, attribute as typed
            $attributes[] = array(strtolower($found[1]), $text, $found[0]);
            $tag .= ' '.strtolower($found[1]).'="'.$text.'"';
          }
        }
        $tags[$i] = array('tag' => $tag.'>', 'open' => '<'.$match[1], 'attributes' => $attributes);
      } else {
        return false;
      }
    }

    return $tags;
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
          // a lightbox class that an element loses counts as a removal as well
          if ($attribute == 'class') {
            foreach (preg_split('/\s+/', strtolower($value), -1, PREG_SPLIT_NO_EMPTY) as $class) {
              if (isset(xtc_lightbox_class_transform::$images[$class])) {
                $items[] = $name.'@class.'.$class;
              }
            }
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
    // jquery ui tabs of xtc5 start on this id and load the address of a tab into the page
    $config->set('Attr.IDBlacklistRegexp', '/^tabbed_product_info$/i');
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
    $config->set('HTML.DefinitionRev', 5);

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
    // a lightbox class stays only on a link to an image, see xtc_lightbox_class_transform
    foreach ($html->info as $tag => $element) {
      $html->info[$tag]->attr_transform_post[] = new xtc_lightbox_class_transform($tag == 'a');
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
