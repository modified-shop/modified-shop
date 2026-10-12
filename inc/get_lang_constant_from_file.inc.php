<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // reads one text of a language file without including it, so no constant gets defined.
  // $variables resolves keys like 'MODULE_PAYMENT_'.$klarna_code.'_TEXT_TITLE'
  function get_lang_constant_from_file($file, $constant, $variables = array()) {
    if (!function_exists('token_get_all') || !is_file($file)) {
      return false;
    }

    // padding with null keeps the lookups at the ends of the file free of notices
    $tokens = array(null, null);
    foreach (token_get_all((string)file_get_contents($file)) as $token) {
      if (!is_array($token) || !in_array($token[0], array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT))) {
        $tokens[] = $token;
      }
    }
    $tokens = array_merge($tokens, array(null, null, null, null));

    // single quotes only know \\ and \', double quotes with escape sequences are not read
    $unquote = function ($literal) {
      $inner = substr($literal, 1, -1);
      if ($literal[0] == "'") {
        return strtr($inner, array("\\\\" => "\\", "\\'" => "'"));
      }
      return (strpos($inner, '\\') === false) ? $inner : false;
    };

    $result = false;
    for ($i = 2, $n = count($tokens) - 5; $i < $n; $i++) {
      if (!in_array($tokens[$i - 1], array('(', ',', '['), true)) {
        continue;
      }

      // the key is one string literal or literals and known variables joined by dots
      $key = '';
      $last = $i;
      while (true) {
        if (is_array($tokens[$last]) && $tokens[$last][0] == T_CONSTANT_ENCAPSED_STRING && ($string = $unquote($tokens[$last][1])) !== false) {
          $key .= $string;
        } elseif (is_array($tokens[$last]) && $tokens[$last][0] == T_VARIABLE && isset($variables[substr($tokens[$last][1], 1)])) {
          $key .= $variables[substr($tokens[$last][1], 1)];
        } else {
          $key = false;
          break;
        }
        if ($tokens[$last + 1] !== '.') {
          break;
        }
        $last += 2;
      }
      if ($key !== $constant) {
        continue;
      }

      // define('NAME', 'value'), 'NAME' => 'value' and $array['NAME'] = 'value'
      if ($tokens[$i - 1] === '(' && $tokens[$last + 1] === ',' && is_array($tokens[$i - 2]) && strtolower(ltrim($tokens[$i - 2][1], '\\')) == 'define') {
        $value = $last + 2;
        $ends = array(')');
      } elseif (is_array($tokens[$last + 1]) && $tokens[$last + 1][0] == T_DOUBLE_ARROW) {
        $value = $last + 2;
        $ends = array(',', ')');
      } elseif ($tokens[$i - 1] === '[' && $tokens[$last + 1] === ']' && $tokens[$last + 2] === '=') {
        $value = $last + 3;
        $ends = array(';');
      } else {
        continue;
      }

      // only a single string literal counts, no concatenation or function call
      if (is_array($tokens[$value]) && $tokens[$value][0] == T_CONSTANT_ENCAPSED_STRING && in_array($tokens[$value + 1], $ends, true)) {
        $string = $unquote($tokens[$value][1]);
        if ($string !== false) {
          $result = $string;
        }
      }
    }

    return $result;
  }
