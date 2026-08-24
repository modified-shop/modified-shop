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
// the PHP html functions accept a different set than mbstring, ASCII and GB18030 are missing there
define('ENCODE_HTML_CHARSETS','UTF-8,ISO-8859-1,ISO-8859-5,ISO-8859-15,cp866,cp1251,cp1252,KOI8-R,BIG5,GB2312,SJIS,EUC-JP');

/**
 * encode_htmlentities
 */
function encode_htmlentities($string, $flags = ENT_COMPAT, $encoding = '')
{
  if ($string !== null && $string !== '') {
    $encoding = get_html_charset($encoding);
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
    $encoding = get_html_charset($encoding);
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
  $language_charset = isset($_SESSION['language_charset']) ? strtolower($_SESSION['language_charset']) : '';

  if ($string !== null && $string !== '' && ($language_charset == 'utf-8' || $force_utf8 === true)) {
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
    $encoding = get_html_charset($encoding);
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
  $language_charset = isset($_SESSION['language_charset']) ? strtolower($_SESSION['language_charset']) : '';

  if ($string !== null && $string !== '' && ($language_charset != 'utf-8' || $force_utf8 === true)) {
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
 * normalize_charset
 */
function normalize_charset($charset)
{
  static $charset_aliases;

  $charset = strtoupper(trim((string)$charset));

  if ($charset === '') {
    return '';
  }

  if (in_array($charset, get_supported_charset())) {
    return $charset;
  }

  if (!isset($charset_aliases)) {
    $charset_aliases = array(
      'LATIN1'      => 'ISO-8859-1',
      'LATIN9'      => 'ISO-8859-15',
      'WINDOWS1251' => 'CP1251',
      'WINDOWS1252' => 'CP1252',
      'SHIFTJIS'    => 'SJIS',
      'USASCII'     => 'ASCII',
    );
    foreach (get_supported_charset() as $supported) {
      $charset_aliases[preg_replace('/[^A-Z0-9]/', '', $supported)] = $supported;
    }
  }

  // "utf8" and other separator-less spellings are rejected by the PHP html functions
  $alias = preg_replace('/[^A-Z0-9]/', '', $charset);

  return isset($charset_aliases[$alias]) ? $charset_aliases[$alias] : '';
}

/**
 * get_html_charset
 */
function get_html_charset($charset = '')
{
  static $html_charsets;

  if (!isset($html_charsets)) {
    $html_charsets = explode(',', strtoupper(ENCODE_HTML_CHARSETS));
  }

  $charset = trim((string)$charset);

  if ($charset === '') {
    $charset = isset($_SESSION['language_charset']) ? trim((string)$_SESSION['language_charset']) : '';
  }

  if ($charset === '') {
    $charset = ENCODE_DEFAULT_CHARSET;
  }

  // an unknown value is kept as it is, it may still be a charset the html functions know
  $normalized = normalize_charset($charset);
  if ($normalized !== '') {
    $charset = $normalized;
  }

  return in_array(strtoupper($charset), $html_charsets) ? $charset : 'UTF-8';
}

/**
 * get_default_charset
 */
function get_default_charset()
{
  $default_charset = isset($_SESSION['language_charset']) ? normalize_charset($_SESSION['language_charset']) : '';
  return ($default_charset !== '') ? $default_charset : ENCODE_DEFAULT_CHARSET;
}

/**
 * get_default_encoding
 */
function get_default_encoding($encoding)
{
  $encoding = normalize_charset($encoding);
  return ($encoding !== '') ? $encoding : get_default_charset();
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
