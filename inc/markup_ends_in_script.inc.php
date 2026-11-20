<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // true when a browser reads the text that follows $markup as the content of a script element, false when not;
  // comments, tags with their attributes and elements that hold text are read as the browser reads them,
  // null says that this reading cannot tell
  function markup_ends_in_script($markup) {
    $markup = (string)$markup;
    $length = strlen($markup);
    $space = "\t\n\f\r ";
    $text_elements = array('script', 'style', 'textarea', 'title', 'xmp', 'iframe', 'noembed', 'noframes', 'noscript');

    // svg and math hold elements of their own, $foreign are the open ones
    $foreign = array();
    $foreign_root = '';
    $foreign_scripts = 0;
    // start tags that end svg and math (font with one of these attributes), and their elements whose content is HTML again
    $html_tags = array('b', 'big', 'blockquote', 'body', 'br', 'center', 'code', 'dd', 'div', 'dl', 'dt', 'em', 'embed',
                       'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'head', 'hr', 'i', 'img', 'li', 'listing', 'menu', 'meta', 'nobr', 'ol',
                       'p', 'pre', 'ruby', 's', 'small', 'span', 'strong', 'strike', 'sub', 'sup', 'table', 'tt', 'u', 'ul', 'var');
    $html_font = array('color', 'face', 'size');
    $html_content = array('svg' => array('foreignobject', 'desc', 'title'),
                          'math' => array('mi', 'mo', 'mn', 'ms', 'mtext', 'annotation-xml'));
    // inside a select element browsers differ in what they take as a tag, an open template in it keeps it open
    $select_open = false;
    $select_templates = 0;
    $select_tags = array('style', 'title', 'xmp', 'iframe', 'noembed', 'noframes', 'noscript', 'plaintext', 'svg', 'math');
    // where the reading is not sure: without a script that is open or a script tag behind $start there is still none
    $unsure = function ($start, $open = false) use ($markup) {
      if ($open === true
          || (preg_match('/<script[\t\n\f\r \/>]/i', $markup, $match, PREG_OFFSET_CAPTURE, $start) === 1
              && strpos($markup, '>', $match[0][1] + 7) !== false)
          )
      {
        return null;
      }
      return false;
    };

    // the place behind a tag whose attributes begin at $pos, whether it closes itself and the names of its attributes:
    // a quote counts only where a value begins
    $tag_end = function ($pos) use ($markup, $length, $space) {
      $self_closing = false;
      $names = array();
      while ($pos < $length) {
        $char = $markup[$pos];
        if ($char === '>') {
          return array($pos + 1, $self_closing, $names);
        }
        if ($char === '/') {
          $self_closing = true;
          $pos++;
          continue;
        }
        $self_closing = false;
        if (strpos($space, $char) !== false) {
          $pos++;
          continue;
        }
        $name_length = 1 + strcspn($markup, $space.'/>=', $pos + 1);
        $names[] = strtolower(substr($markup, $pos, $name_length));
        $pos += $name_length;
        $pos += strspn($markup, $space, $pos);
        if ($pos < $length && $markup[$pos] === '=') {
          $pos++;
          $pos += strspn($markup, $space, $pos);
          if ($pos >= $length) {
            break;
          }
          $char = $markup[$pos];
          if ($char === '"' || $char === "'") {
            $end = strpos($markup, $char, $pos + 1);
            if ($end === false) {
              break;
            }
            $pos = $end + 1;
          } elseif ($char !== '>') {
            $pos += strcspn($markup, $space.'>', $pos);
          }
        }
      }
      return false;
    };

    $pos = 0;
    while (($pos = strpos($markup, '<', $pos)) !== false) {
      $next = (string)substr($markup, $pos + 1, 1);

      if ($next === '!' || $next === '?') {
        if (substr($markup, $pos, 4) === '<!--') {
          // the dashes of the beginning may end the comment as well, but not as "--!>"
          $end = false;
          foreach (array($pos + 2, $pos + 4) as $from) {
            if (preg_match('/--!?>/', $markup, $match, PREG_OFFSET_CAPTURE, $from) && ($match[0][0] === '-->' || $match[0][1] >= $pos + 4)) {
              $end = $match[0][1] + strlen($match[0][0]);
              break;
            }
          }
        } elseif (count($foreign) > 0 && substr($markup, $pos, 9) === '<![CDATA[') {
          $end = strpos($markup, ']]>', $pos + 9);
          if ($end === false) {
            return ($foreign_scripts > 0);
          }
          $end += 3;
        } else {
          $end = strpos($markup, '>', $pos);
          if ($end !== false) {
            $end++;
          }
        }
        if ($end === false) {
          return false;
        }
        $pos = $end;
        continue;
      }

      $closing = ($next === '/');
      $name_start = $pos + ($closing ? 2 : 1);
      if (!preg_match('/\G[a-z][^\t\n\f\r \/>]*/i', $markup, $match, 0, $name_start)) {
        if ($closing === false) {
          // behind a last "<" in a script of svg or math the text itself decides whether a tag begins
          if ($pos + 1 >= $length && $foreign_scripts > 0) {
            return null;
          }
          $pos++;
        } elseif (substr($markup, $name_start, 1) === '>') {
          $pos = $name_start + 1;
        } else {
          $end = strpos($markup, '>', $name_start);
          if ($end === false) {
            return false;
          }
          $pos = $end + 1;
        }
        continue;
      }
      $name = strtolower($match[0]);

      $tag_start = $pos;
      $tag = $tag_end($name_start + strlen($match[0]));
      if ($tag === false) {
        return false;
      }
      $pos = $tag[0];
      $self_closing = $tag[1];

      if (count($foreign) > 0) {
        $open = count($foreign) - 1;
        $html = in_array($foreign[$open], $html_content[$foreign_root]);
        $leaves = (($closing === true) ? ($name == 'p' || $name == 'br')
                                       : (in_array($name, $html_tags) || ($name == 'font' && count(array_intersect($tag[2], $html_font)) > 0)));
        if ($leaves === true) {
          // such a tag ends svg and math with all that is open in them, inside content of HTML it changes nothing
          if ($html === true && $closing === false) {
            return $unsure($tag_start, $foreign_scripts > 0);
          }
          if ($html === true) {
            continue;
          }
          $foreign = array();
          $foreign_scripts = 0;
        } elseif ($closing === false) {
          // mglyph and malignmark stay MathML inside its elements for text
          if ($html === true && ($foreign_root != 'math' || $foreign[$open] == 'annotation-xml' || ($name != 'mglyph' && $name != 'malignmark'))) {
            return $unsure($tag_start, $foreign_scripts > 0);
          }
          if ($self_closing === false) {
            $foreign[] = $name;
            $foreign_scripts += (($name == 'script') ? 1 : 0);
          }
          continue;
        } else {
          while ($open >= 0 && $foreign[$open] != $name) {
            $open--;
          }
          if ($open < 0) {
            // the rules of HTML decide whether this end tag closes something around the svg
            return $unsure($tag_start, $foreign_scripts > 0);
          }
          while (count($foreign) > $open) {
            $foreign_scripts -= ((array_pop($foreign) == 'script') ? 1 : 0);
          }
          continue;
        }
      }
      if ($closing === true) {
        if ($name == 'template' && $select_templates > 0) {
          $select_templates--;
        } elseif ($name == 'select' && $select_templates == 0) {
          $select_open = false;
        }
        continue;
      }
      if ($name == 'select') {
        $select_open = true;
      }
      if ($select_open === true && $name == 'template') {
        $select_templates++;
      }
      if ($select_open === true && in_array($name, $select_tags)) {
        return $unsure($tag_start);
      }
      if ($name == 'svg' || $name == 'math') {
        if ($self_closing === false) {
          $foreign = array($name);
          $foreign_root = $name;
        }
        continue;
      }
      if ($name == 'plaintext') {
        return false;
      }
      if (!in_array($name, $text_elements)) {
        continue;
      }

      if ($name == 'script') {
        // a script may hold "<!--" and in it a further script, whose end tag does not end the outer one
        $state = 0;
        $end = false;
        while (preg_match('/<!--|-->|<(\/?)script(?=[\t\n\f\r \/>])/i', $markup, $match, PREG_OFFSET_CAPTURE, $pos)) {
          $at = $match[0][1];
          if ($match[0][0] === '<!--') {
            $state = (($state == 0) ? 1 : $state);
            $pos = $at + 2;
          } elseif ($match[0][0] === '-->') {
            $state = 0;
            $pos = $at + 3;
          } elseif ($match[1][0] === '/') {
            if ($state != 2) {
              $end = $at + 8;
              break;
            }
            $state = 1;
            $pos = $at + 8;
          } else {
            $state = (($state == 1) ? 2 : $state);
            $pos = $at + 7;
          }
        }
        if ($end === false) {
          return true;
        }
      } else {
        if (!preg_match('/<\/'.$name.'(?=[\t\n\f\r \/>])/i', $markup, $match, PREG_OFFSET_CAPTURE, $pos)) {
          return false;
        }
        $end = $match[0][1] + strlen($match[0][0]);
      }
      // the rest of the end tag
      $tag = $tag_end($end);
      if ($tag === false) {
        return false;
      }
      $pos = $tag[0];
    }

    return ($foreign_scripts > 0);
  }
