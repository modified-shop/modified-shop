<?php
/*--------------------------------------------------------------
  $Id: html_encoding.php 4252 2013-01-11 15:25:35Z web28 $

  modified eCommerce Shopsoftware - community made shopping

  copyright (c) 2010-2013 modified www.modified-shop.org

  (c) 2013 rpa-com.de <web28> and hackersolutions.com <h-h-h>

  Released under the GNU General Public License
--------------------------------------------------------------*/

define('ENCODE_DEFINED_CHARSETS','ISO-8859-1,ISO-8859-15,UTF-8,cp866,cp1251,cp1252,KOI8-R,BIG5,GB2312,BIG5-HKSCS,Shift_JIS,EUC-JP'); 
define('ENCODE_DEFAULT_CHARSET', 'ISO-8859-15');

/**
 * encode_htmlentities
 */
function encode_htmlentities ($string, $flags = ENT_COMPAT, $encoding = '')
{
  $supported_charsets = explode(',',strtoupper(ENCODE_DEFINED_CHARSETS));  
  $default_charset = isset($_SESSION['language_charset']) && in_array(strtoupper($_SESSION['language_charset']), $supported_charsets) ? strtoupper($_SESSION['language_charset']) : ENCODE_DEFAULT_CHARSET;
  $encoding = !empty($encoding) && in_array(strtoupper($encoding), $supported_charsets) ? strtoupper($encoding) : $default_charset;  
  return htmlentities($string, $flags , $encoding);
}

/**
 * encode_htmlspecialchars
 */
function encode_htmlspecialchars ($string, $flags = ENT_COMPAT, $encoding = '')
{
  $supported_charsets = explode(',',strtoupper(ENCODE_DEFINED_CHARSETS));  
  $default_charset = isset($_SESSION['language_charset']) && in_array(strtoupper($_SESSION['language_charset']), $supported_charsets) ? strtoupper($_SESSION['language_charset']) : ENCODE_DEFAULT_CHARSET;
  $encoding = !empty($encoding) && in_array(strtoupper($encoding), $supported_charsets) ? strtoupper($encoding) : $default_charset;
  return htmlspecialchars($string, $flags , $encoding);
}

/**
 * encode_utf8
 */
function encode_utf8($in_str) {
  if (strtolower($_SESSION['language_charset']) == 'utf-8') {
    $cur_encoding = mb_detect_encoding($in_str);
    if($cur_encoding == "UTF-8" && mb_check_encoding($in_str,"UTF-8")) {
      return $in_str;
    } else {
      return mb_convert_encoding($in_str,"UTF-8","ISO-8859-15");
    }
  } else {
    return $in_str;
  }
}

/**
 * decode_htmlentities
 */
function decode_htmlentities ($string, $flags = ENT_COMPAT, $encoding = '')
{
  $supported_charsets = explode(',',strtoupper(ENCODE_DEFINED_CHARSETS));  
  $default_charset = isset($_SESSION['language_charset']) && in_array(strtoupper($_SESSION['language_charset']), $supported_charsets) ? strtoupper($_SESSION['language_charset']) : ENCODE_DEFAULT_CHARSET;
  $encoding = !empty($encoding) && in_array(strtoupper($encoding), $supported_charsets) ? strtoupper($encoding) : $default_charset;
  return html_entity_decode($string, $flags , $encoding);
}

/**
 * decode_htmlspecialchars
 */
function decode_htmlspecialchars ($string, $flags = ENT_COMPAT, $encoding = '')
{
  $supported_charsets = explode(',',strtoupper(ENCODE_DEFINED_CHARSETS));  
  $default_charset = isset($_SESSION['language_charset']) && in_array(strtoupper($_SESSION['language_charset']), $supported_charsets) ? strtoupper($_SESSION['language_charset']) : ENCODE_DEFAULT_CHARSET;
  $encoding = !empty($encoding) && in_array(strtoupper($encoding), $supported_charsets) ? strtoupper($encoding) : $default_charset;
  return htmlspecialchars_decode($string, $flags , $encoding);
}

/**
 * decode_utf8
 */
function decode_utf8($in_str) {
  if (strtolower($_SESSION['language_charset']) != 'utf-8') {
    $cur_encoding = mb_detect_encoding($in_str);
    if($cur_encoding == "UTF-8" && mb_check_encoding($in_str,"UTF-8")) {
      return mb_convert_encoding($in_str,"ISO-8859-15","UTF-8");
    } else {
      return $in_str;
    }
  } else {
    return $in_str;
  }
}

/**
 * get_supported_charset
 */
function get_supported_charset($charset = '')
{
  $charset = !empty($charset) ? $charset : (isset($_SESSION['language_charset']) ? $_SESSION['language_charset'] : null);
  $supported_charsets = explode(',',strtoupper(ENCODE_DEFINED_CHARSETS));
  $default_charset = isset($charset) && in_array(strtoupper($charset), $supported_charsets) ? strtoupper($charset) : ENCODE_DEFAULT_CHARSET;
  return $default_charset;
}
