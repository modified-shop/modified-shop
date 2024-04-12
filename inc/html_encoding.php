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

/**
 * encode_htmlentities
 */
function encode_htmlentities($string, $flags = ENT_COMPAT, $encoding = '')
{
  if (!empty($string)) {
    $encoding = get_default_encoding($encoding)
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
  if (!empty($string)) {
    $encoding = get_default_encoding($encoding)
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
  if (!empty($string) && (strtolower($_SESSION['language_charset']) == 'utf-8' || $force_utf8 === true)) {
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
  if (!empty($string)) {
    $encoding = get_default_encoding($encoding)
    return html_entity_decode($string, $flags , $encoding);
  } else {
    return $string;
  }
}

/**
 * decode_htmlspecialchars
 */
function decode_htmlspecialchars($string, $flags = ENT_COMPAT, $encoding = '')
{
  if (!empty($string)) {
    $encoding = get_default_encoding($encoding)
    return htmlspecialchars_decode($string, $flags , $encoding);
  } else {
    return $string;
  }
}

/**
 * decode_utf8
 */
function decode_utf8($string, $encoding = '', $force_utf8 = false) 
{
  if (strtolower($_SESSION['language_charset']) != 'utf-8' || $force_utf8 === true) {
    $encoding = get_default_encoding($encoding)
    
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
  $supported_charsets = explode(',', strtoupper(ENCODE_DEFINED_CHARSETS));
  return $supported_charsets;
}

/**
 * get_default_charset
 */
function get_default_charset()
{
  $default_charset = isset($_SESSION['language_charset']) && in_array(strtoupper($_SESSION['language_charset']), get_supported_charset()) ? strtoupper($_SESSION['language_charset']) : ENCODE_DEFAULT_CHARSET;
  return $default_charset;
}

/**
 * get_default_encoding
 */
function get_default_encoding($encoding)
{
  $encoding = !empty($encoding) && in_array(strtoupper($encoding), get_supported_charset()) ? strtoupper($encoding) : get_default_charset();
  return $encoding;
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
