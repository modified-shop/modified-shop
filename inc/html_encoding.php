<?php
/*--------------------------------------------------------------
  $Id$

  modified eCommerce Shopsoftware - community made shopping

  copyright (c) 2010-2013 modified www.modified-shop.org

  (c) 2013 rpa-com.de <web28> and hackersolutions.com <h-h-h>

  Released under the GNU General Public License
--------------------------------------------------------------*/

define('ENCODE_DEFINED_CHARSETS','ASCII,UTF-8,ISO-8859-1,ISO-8859-15,cp866,cp1251,cp1252,KOI8-R,GB18030,SJIS,EUC-JP');
define('ENCODE_DEFAULT_CHARSET', 'ISO-8859-15');
// the only charsets a language may use, ENCODE_DEFINED_CHARSETS stays the mb_detect_encoding list
define('ENCODE_LANGUAGE_CHARSETS','UTF-8,ISO-8859-15');

/**
 * encode_htmlentities
 */
function encode_htmlentities($string, $flags = ENT_COMPAT, $encoding = '')
{
  if ($string !== null && $string !== '') {
    $encoding = get_default_encoding($encoding);
    return htmlentities($string, $flags , $encoding);
  } else {
    return $string;
  }
}

/**
 * encode_htmlspecialchars
 */
function encode_htmlspecialchars($string, $flags = ENT_COMPAT, $encoding = '')
{
  if ($string !== null && $string !== '') {
    $encoding = get_default_encoding($encoding);
    return htmlspecialchars($string, $flags , $encoding);
  } else {
    return $string;
  }
}

/**
 * encode_utf8
 */
function encode_utf8($string, $encoding = '', $force_utf8 = false)
{
  if ($string !== null && $string !== '' && (get_default_charset() === 'UTF-8' || $force_utf8 === true)) {
    $cur_encoding = !empty($encoding) && in_array(strtoupper($encoding), get_supported_charset()) ? strtoupper($encoding) : detect_encoding($string);
    if ($cur_encoding == 'UTF-8' && mb_check_encoding($string, 'UTF-8')) {
      return $string;
    } else {
      return mb_convert_encoding($string, 'UTF-8', $cur_encoding);
    }
  } else {
    return $string;
  }
}

/**
 * decode_htmlentities
 */
function decode_htmlentities($string, $flags = ENT_COMPAT, $encoding = '')
{
  if ($string !== null && $string !== '') {
    $encoding = get_default_encoding($encoding);
    return html_entity_decode($string, $flags , $encoding);
  } else {
    return $string;
  }
}

/**
 * decode_htmlspecialchars
 */
function decode_htmlspecialchars($string, $flags = ENT_COMPAT)
{
  if ($string !== null && $string !== '') {
    return htmlspecialchars_decode($string, $flags);
  } else {
    return $string;
  }
}

/**
 * decode_utf8
 */
function decode_utf8($string, $encoding = '', $force_utf8 = false)
{
  if ($string !== null && $string !== '' && (get_default_charset() !== 'UTF-8' || $force_utf8 === true)) {
    $encoding = get_default_encoding($encoding);

    $cur_encoding = detect_encoding($string, 'UTF-8');
    if ($cur_encoding == 'UTF-8' && mb_check_encoding($string, 'UTF-8')) {
      return mb_convert_encoding($string, $encoding, 'UTF-8');
    } else {
      return $string;
    }
  } else {
    return $string;
  }
}

/**
 * get_supported_charset
 */
function get_supported_charset()
{
  static $supported_charsets;

  if (!isset($supported_charsets)) {
    $supported_charsets = explode(',', strtoupper(ENCODE_DEFINED_CHARSETS));
  }

  return $supported_charsets;
}

/**
 * get_language_charsets
 */
function get_language_charsets()
{
  static $language_charsets;

  if (!isset($language_charsets)) {
    $language_charsets = explode(',', strtoupper(ENCODE_LANGUAGE_CHARSETS));
  }

  return $language_charsets;
}

/**
 * normalize_charset
 */
function normalize_charset($charset)
{
  static $charset_aliases;

  $charset = strtoupper(trim((string)$charset));

  if ($charset === '') {
    return '';
  }

  if (in_array($charset, get_language_charsets())) {
    return $charset;
  }

  if (!isset($charset_aliases)) {
    $charset_aliases = array('LATIN9' => 'ISO-8859-15');
    foreach (get_language_charsets() as $supported) {
      $charset_aliases[preg_replace('/[^A-Z0-9]/', '', $supported)] = $supported;
    }
  }

  // "utf8" and other separator-less spellings are rejected by the PHP html functions
  $alias = preg_replace('/[^A-Z0-9]/', '', $charset);

  return isset($charset_aliases[$alias]) ? $charset_aliases[$alias] : '';
}

/**
 * get_language_charset
 */
function get_language_charset($charset = '')
{
  $charset = normalize_charset($charset);

  if ($charset === '') {
    $charset = normalize_charset(isset($_SESSION['language_charset']) ? $_SESSION['language_charset'] : '');
  }

  // ENCODE_DEFAULT_CHARSET is part of ENCODE_LANGUAGE_CHARSETS, so the result is always allowed
  return ($charset !== '') ? $charset : ENCODE_DEFAULT_CHARSET;
}

/**
 * get_html_charset
 */
function get_html_charset($charset)
{
  // both are part of ENCODE_DEFINED_CHARSETS, but the PHP html functions reject them and assume UTF-8
  static $html_charset_aliases = array('ASCII' => 'UTF-8', 'GB18030' => 'GB2312');

  $charset = strtoupper(trim((string)$charset));

  if (isset($html_charset_aliases[$charset])) {
    return $html_charset_aliases[$charset];
  }

  // a legacy charset the shop still knows keeps working, only the admin choice is limited
  if ($charset !== '' && in_array($charset, get_supported_charset())) {
    return $charset;
  }

  return normalize_charset($charset);
}

/**
 * get_default_charset
 */
function get_default_charset()
{
  $charset = get_html_charset(isset($_SESSION['language_charset']) ? $_SESSION['language_charset'] : '');

  return ($charset !== '') ? $charset : ENCODE_DEFAULT_CHARSET;
}

/**
 * get_default_encoding
 */
function get_default_encoding($encoding)
{
  $encoding = get_html_charset($encoding);

  return ($encoding !== '') ? $encoding : get_default_charset();
}

/**
 * set_session_charset
 */
function set_session_charset()
{
  $charset = isset($_SESSION['language_charset']) ? trim((string)$_SESSION['language_charset']) : '';

  // resolve an alias like "utf8", but keep an unknown value, it may be a charset this shop really uses
  if ($charset !== '' && !in_array(strtoupper($charset), get_supported_charset())) {
    $normalized_charset = normalize_charset($charset);
    $charset = ($normalized_charset !== '') ? $normalized_charset : $charset;
  }

  $_SESSION['language_charset'] = ($charset !== '') ? $charset : get_default_charset();

  // PHP derives the Content-Type header from this, so it has to stay the real response charset
  @ini_set('default_charset', $_SESSION['language_charset']);
}

/**
 * detect_encoding
 */
function detect_encoding($string, $encodings = ENCODE_DEFINED_CHARSETS, $strict = true)
{
  $encoding = mb_detect_encoding($string, $encodings, $strict);
  if ($encoding === false) {
    $encoding = mb_detect_encoding($string, $encodings, false);
  }
  return $encoding;
}
