<?php
/**
 * 888888ba                 dP  .88888.                    dP                
 * 88    `8b                88 d8'   `88                   88                
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b. 
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88 
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88 
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P' 
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * $Id: functionLib.php 3133 2013-08-21 22:06:22Z derpapst $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

function _is_plain_text() {
	$headers = headers_list();
	if (empty($headers)) {
		return false;
	}
	foreach ($headers as $line) {
		if (stripos($line, 'Content-Type: text/plain') !== false) {
			return true;
		}
	}
	return false;
}

function print_m($arr, $label = '', $text = false) {
	if ($label === true) {
		$label = '';
		$text = true;
	}
	if (!$text) {
		$text = _is_plain_text();
	}
	$arr = print_r($arr, true);
	return (!$text ? '<pre>' : '') . (($label != "") ? $label." :: " : '') . (!$text ? htmlspecialchars($arr).'</pre>' : $arr);
}

function var_dump_pre($obj, $label = "", $text = false) {
	if ($label === true) {
		$label = '';
		$text = true;
	}
	if (!$text) {
		$text = _is_plain_text();
	}
	ob_start();
	var_dump($obj);
	$r = (!$text ? '<pre>' : '') . (($label != "") ? $label." :: " : '').(!$text ? htmlspecialchars(ob_get_contents()).'</pre>' : ob_get_contents());
	ob_end_clean();
	return $r;
}

function var_export_pre($obj, $label = "", $text = false) {
	if ($label === true) {
		$label = '';
		$text = true;
	}
	if (!$text) {
		$text = _is_plain_text();
	}
	$arr = var_export($obj, true).';';
	return (!$text ? '<pre>' : '') . (($label != "") ? $label." = " : '') . (!$text ? htmlspecialchars($arr).'</pre>' : $arr);
}

function eempty($v) {
	return empty($v);
}

function initArrayIfNecessary(&$array, $path) {
	$var = &$array;
	if (!is_array($var)) $var = array();
	if (!is_array($path)) {
		$path = explode('|', $path);
	}
	if (empty($path)) return;
	foreach ($path as $component) {
		if (empty($component)) continue;
		if (!array_key_exists($component, $var) || !is_array($var[$component])) {
			$var[$component] = array();
		}
		$var = &$var[$component];
	}
}

function test(&$var, $function) {
    if(!isset($var)) return false;
    if (!empty($function) && function_exists($function)){
        return $function($var);
    }
    if ($function == 'empty') {
        return empty($var);
    }
    if (!function_exists($function)) {
    	$caller = @debug_backtrace();
    	if (!empty($caller)) {
			$caller = current($caller);
	        echo (
	        	'Notice: Call to undefined function '.$function.'() in '.$caller['function'].'() '.
	        	'called from '.$caller['file'].' on line '.$caller['line'].'.'.nl2br("\n")
	        );
	    } else {
	        echo (
	        	'Notice: Call to undefined function '.$function.'() called in '.__FUNCTION__.'.'.nl2br("\n")
	        );	    	
	    }
    }
    return false;
}

function mergeArrays(&$sourceArray, &$copyArray){
	//merge copy array into source array
	$i = 0;
	while (isset($copyArray[$i])){
		$sourceArray[] = $copyArray[$i];
		unset($copyArray[$i]);
		$i++;
	}
}

function array_is_associative($array) {
	return is_array($array) && (array_keys($array) !== range(0, sizeof($array) - 1));
}

function array_merge_recursive_simple() {
    if (func_num_args() < 2) {
        trigger_error(__FUNCTION__ .' needs two or more array arguments', E_USER_WARNING);
        return;
    }
    $arrays = func_get_args();
    $merged = array();
    while ($arrays) {
        $array = array_shift($arrays);
        if (!is_array($array)) {
            trigger_error(__FUNCTION__ .' encountered a non array argument', E_USER_WARNING);
            return;
        }
        if (!$array) {
            continue;
        }
        if (array_is_associative($array)) {
            foreach ($array as $key => $value) {
                if (is_array($value) && array_key_exists($key, $merged) && is_array($merged[$key])) {
                    $merged[$key] = call_user_func(__FUNCTION__, $merged[$key], $value);
                } else {
                    $merged[$key] = $value;
                }
            }
        } else {
            foreach ($array as $key => $value) {
               $merged[] = $value;
            }
        }
    }
    return $merged;
}

function isNumericArray($var) {
	return is_array($var) && (array_keys($var) === range(0, sizeof($var) - 1));
}

function array_push_array(&$arr) {
    $args = func_get_args();
    array_shift($args);

    if (!is_array($arr)) {
        trigger_error(sprintf("%s: Cannot perform push on something that isn't an array!", __FUNCTION__), E_USER_WARNING);
        return false;
    }

    foreach($args as $v) {
        if (is_array($v) && !empty($v)) {
        	foreach($v as $item) {
        		$arr[] = $item;
        	}
        } else {
            $arr[] = $v;
        }
    }
    return count($arr);
}

function array_remove(&$array, $value) {
	if (!is_array($value)) $value = array($value);
	foreach ($value as $v) {
		$key = array_search($v, $array);
		if ($key) unset($array[$key]);
	}
}

function found_in_array($keys, $array) {
	$c = 0;
	foreach ($keys as $key) {
		if (array_key_exists($key, $array)) ++$c;
	}
	return $c;
}

function find_in_array_by_key($needle, $haystack, $key) {
	if (empty($haystack)) return false;
	foreach ($haystack as $k => $v) {
		if ($v[$key] == $needle) {
			return $k;
		}
	}
	return false;
}

function in_array_key($needle, $haystack, $key) {
	if (empty($haystack)) return false;
	foreach ($haystack as $item) {
		if ($item[$key] == $needle) {
			return true;
		}
	}
	return false;
}

function array_invert($arr) {
	$flipped = array();
	foreach ( $arr as $k => $a ) {
		# put the value in the key, with a throw-away value.  dups are inherently avoided,
		# though overwritten.  not sure if prefixing with if ( !isset($flipped[$a][$k]) )
		# would speed this up or slow it down.  probably depends on quantity of dups.
		$flipped[$a][$k] = null;
	}
	foreach ( $flipped as $k => $fl ) {
		# now make the keys the values.
		$flipped[$k] = array_keys($fl);
	}
	return $flipped;
}

function array_search_by_key($needle, $haystack, $key) {
	$count = 0;
	foreach ($haystack as $k => $item) {
		if ($item[$key] == $needle) {
			break;
		}
		++$count;
	}
	return ($count < count($haystack)) ? $k : false;
}

function find_in_array($needle, $haystack) {
	foreach ($haystack as $key => $value) {
		if ($value == $needle) {
			return $key;
		}
	}
	return false;
}

function array_first($array) {
	/* da eine kopie uebergeben wird, wird das original array nicht veraendert. */
	return array_shift($array);
}

/* For alert and confirm boxes. Works in combination with unescape() */
function html2url($str) {
	return str_replace('+', ' ', urlencode(html_entity_decode($str)));
}

function resizeImage($resource_file, $max_width, $max_height, $destination_file, $compression=80) {
	$src = array();
	$dst = array();
	$dimensions = getimagesize($resource_file);
	if (is_array($dimensions)) {
		$src['w'] = $dimensions[0];
		$src['h'] = $dimensions[1];
		$src['type'] = $dimensions[2];

        if ($max_width == '0') {
           $max_width = ($src['w'] / ($src['h'] / $max_height));
        }

		$thiso = ($src['w'] / $max_width);
		$thisp = ($src['h'] / $max_height);
		$dst['w'] = ($thiso > $thisp) ? $max_width : round($src['w'] / $thisp); // width
		$dst['h'] = ($thiso > $thisp) ? round($src['h'] / $thiso) : $max_height; // height
	}
	$src['image'] = @imagecreatefromstring(@file_get_contents($resource_file));

	if (!is_resource($src['image'])) {
		unset($src);
		unset($dst);
		return false;
	}

	$success = true;
	if (function_exists('imagecreatetruecolor')) {
		$dst['image'] = imagecreatetruecolor($dst['w'], $dst['h']); // created thumbnail reference GD2
	} else {
		$dst['image'] = imagecreate($dst['w'], $dst['h']); // created thumbnail reference GD1
	}
	if (imagecopyresampled($dst['image'], $src['image'], 0, 0, 0, 0, $dst['w'], $dst['h'], $src['w'], $src['h'])) {
		$success = @imagejpeg($dst['image'], $destination_file, $compression);
	} else {
		$success = false;
	}
	imagedestroy($src['image']);
	imagedestroy($dst['image']);

	unset($src);
	unset($dst);
	return $success;
}

function microtime2human($time) {
	$str = '';
	if ($time > 3600) {
		$hours = floor($time / 3600);
		$str .= $hours.'h';
		$time -= $hours * 3600;
	}
	if ($time > 60) {
		$minutes = floor($time / 60);
		$str .= ' '.$minutes.'m';
		$time -= $minutes * 60;
		round($time % 60, 2).'s';
	}
	if ($time > 1) {
		$seconds = $time % 60;
		$str .= ' '.$seconds.'s';
		$time -= $seconds;
	}
	return trim(trim($str).' '.round($time * 1000, 2).'ms');
}

function memory_usage() {
	if (!function_exists('memory_get_peak_usage')) return false;
    $mem_usage = memory_get_peak_usage(true);
    if ($mem_usage < 1024)
        return $mem_usage." bytes";
    elseif ($mem_usage < 1048576)
        return round($mem_usage/1024, 2)." kilobytes";
    return round($mem_usage/1048576, 2)." megabytes";
}

function filesize2human($size, $decimals = 2) {
	$suffix = array('B','KB','MB','GB','TB','PB','EB','ZB','YB','NB','DB');
	$suffixLength = count($suffix) - 1;
	$i = 0;

	while (($size >= 1024) && ($i < $suffixLength)){
		$size /= 1024;
		++$i;
	}
	return round($size, $decimals).' '.$suffix[$i];
}

function convert2Bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    switch($last) {
        // The 'G' modifier is available since PHP 5.1.0
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    return $val;
}

function randomString($length = 8) {
	$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	$poolLength = strlen($pool) - 1;
	$return = '';
	for ($i = 0; $i < $length; ++$i) {
		$return .= $pool[rand(0, $poolLength)];
	}
	return $return;
}

function eecho($str, $print = false) {
	if ($print) {
		if (!_is_plain_text()) {
			echo '<pre>'.$str.'</pre>';
		} else {
			echo $str;
		}
	}
	return $str;
}

function isUTF8($str) {
    $len = strlen($str);
    for($i = 0; $i < $len; ++$i){
        $c = ord($str[$i]);
        if ($c > 128) {
            if (($c > 247)) return false;
            elseif ($c > 239) $bytes = 4;
            elseif ($c > 223) $bytes = 3;
            elseif ($c > 191) $bytes = 2;
            else return false;
            if (($i + $bytes) > $len) return false;
            while ($bytes > 1) {
                ++$i;
                $b = ord($str[$i]);
                if ($b < 128 || $b > 191) return false;
                --$bytes;
            }
        }
    }
    return true;
}

function isNotIso8859_1($inputstring) {
	$not_iso_chars = utf8_encode (
		"\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0a\x0b\x0c\x0d\x0e\x0f".
		"\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1a\x1b\x1c\x1d\x1e\x1f".
		"\x7f".
		"\x80\x81\x82\x83\x84\x85\x86\x87\x88\x89\x8a\x8b\x8c\x8d\x8e\x8f".
		"\x90\x91\x92\x93\x94\x95\x96\x97\x98\x99\x9a\x9b\x9c\x9d\x9e\x9f"
	);
	return (strpbrk($inputstring, $not_iso_chars) !== false);
}

function arrayEntitiesToUTF8(&$array) {
	if (empty($array)) return;
	foreach ($array as &$item) {
		if (is_array($item)) arrayEntitiesToUTF8($item);
		if (!is_string($item)) continue;
		$item = (isUTF8($item) ? $item : utf8_encode($item));
	}
}

function arrayEntitiesToLatin1(&$array) {
	if (empty($array)) return;
	foreach ($array as &$item) {
		if (is_array($item)) arrayEntitiesToLatin1($item);
		if (!is_string($item)) continue;
		$item = ((isUTF8($item) || isNotIso8859_1($item)) ? utf8_decode($item) : $item);
	}
}

function stringToUTF8($string) {
		 return (isUTF8($string) ? $string : utf8_encode($string));
}

function charset_decode_utf_8($string) {
    /* Only do the slow convert if there are 8-bit characters */
    /* avoid using 0xA0 (\240) in ereg ranges. RH73 does not like that */
    if (!preg_match("/[\200-\237]/e", $string) && !preg_match("/[\241-\377]/e", $string)) {
        return $string;
	}

    // decode three byte unicode characters
    $string = preg_replace(
    	"/([\340-\357])([\200-\277])([\200-\277])/e",       
    	"'&#'.((ord('\\1')-224)*4096 + (ord('\\2')-128)*64 + (ord('\\3')-128)).';'",   
    	$string
    );

    // decode two byte unicode characters
    $string = preg_replace(
    	"/([\300-\337])([\200-\277])/e",
    	"'&#'.((ord('\\1')-192)*64+(ord('\\2')-128)).';'",
    	$string
    );

    return $string;
}

function fixHTMLUTF8Entities($str, $quoteStyle = ENT_NOQUOTES) {
    $str = (string)$str;
	# for the case the string was already html coded, and htmlentities() does too much
	$t1 = array('&amp;auml;', '&amp;Auml;', '&amp;ouml;', '&amp;Ouml;',
		'&amp;uuml;', '&amp;Uuml;', '&amp;szlig;',
		'&amp;euro;', '&amp;pound;', '&amp;yen', '&amp;curren;', '&curren;');
	$t2 = array('&auml;', '&Auml;', '&ouml;', '&Ouml;',
		'&uuml;', '&Uuml;', '&szlig;',
		'&euro;', '&pound;', '&yen', '&euro;', '&euro;');
	return (string)str_replace($t1, $t2, htmlentities((isUTF8($str) ? $str : utf8_encode($str)), $quoteStyle, 'UTF-8'));
}

function arrayEntitiesFixHTMLUTF8(&$array) {
	if (empty($array)) return;
	foreach ($array as &$item) {
		if (is_array($item)) arrayEntitiesFixHTMLUTF8($item);
		if (!is_string($item)) continue;
		$item = fixHTMLUTF8Entities($item);
	}
}

function escape_string_for_regex($str) {
    // All regex special chars
    // \ ^ . $ | (
    // ) [ ] * + ? 
    // { } ,
   
    $patterns = array(
    	'/\//', '/\^/', '/\./', '/\$/', '/\|/', '/\(/',
		'/\)/', '/\[/', '/\]/', '/\*/', '/\+/',	'/\?/', 
		'/\{/', '/\}/', '/\,/'
	);
    $replace = array(
    	'\/',   '\^',   '\.',   '\$',   '\|',   '\(', 
    	'\)',   '\[',   '\]',   '\*',   '\+',   '\?', 
    	'\{',   '\}',   '\,'
    );
    return preg_replace($patterns, $replace, $str);
}

function short_str( $str, $len, $cut = false ) {
    if ( strlen( $str ) <= $len ) return $str;
   
    return ( $cut ? substr( $str, 0, $len - 3 ) : substr( $str, 0, strrpos( substr( $str, 0, $len - 3 ), ' ' ) ) ) . '...';
}

/**
 * Only strip slashes if there are any to strip.
 * Author: hawkeye at conreports dot de
 */
function smartstripslashes($str) {
	$cd1 = substr_count($str, "\"");
	$cd2 = substr_count($str, "\\\"");
	$cs1 = substr_count($str, "'");
	$cs2 = substr_count($str, "\\'");
	$tmp = strtr($str, array("\\\"" => "", "\\'" => ""));
	$cb1 = substr_count($tmp, "\\");
	$cb2 = substr_count($tmp, "\\\\");
	if (($cd1 == $cd2) && ($cs1 == $cs2) && ($cb1 == (2 * $cb2))) {
		return stripslashes($str);
	}
	return $str;
}

/**
 * strtolower extended for UTF-8 chars (umlauts, accents, russian, etc.)
 * @param $text	UTF-8 encoded string with mixed case
 *
 * @author: Khigashi, first version
 * @author: MaW, extended charlist
 */
function deepLower($text) {
	if (function_exists('mb_strtolower')) {
		return mb_strtolower($text, 'UTF-8');
	}
	$charMap = array (
		'\xc380' => '\xc3a0', '\xc381' => '\xc3a1', '\xc382' => '\xc3a2', '\xc383' => '\xc3a3', '\xc384' => '\xc3a4', 
		'\xc385' => '\xc3a5', '\xc386' => '\xc3a6', '\xc387' => '\xc3a7', '\xc388' => '\xc3a8', '\xc389' => '\xc3a9', 
		'\xc38a' => '\xc3aa', '\xc38b' => '\xc3ab', '\xc38c' => '\xc3ac', '\xc38d' => '\xc3ad', '\xc38e' => '\xc3ae', 
		'\xc38f' => '\xc3af', '\xc390' => '\xc3b0', '\xc391' => '\xc3b1', '\xc392' => '\xc3b2', '\xc393' => '\xc3b3', 
		'\xc394' => '\xc3b4', '\xc395' => '\xc3b5', '\xc396' => '\xc3b6', '\xc398' => '\xc3b8', '\xc399' => '\xc3b9', 
		'\xc39a' => '\xc3ba', '\xc39b' => '\xc3bb', '\xc39c' => '\xc3bc', '\xc39d' => '\xc3bd', '\xd091' => '\xd0b1', 
		'\xd092' => '\xd0b2', '\xd093' => '\xd0b3', '\xd094' => '\xd0b4', '\xd081' => '\xd191', '\xd096' => '\xd0b6', 
		'\xd097' => '\xd0b7', '\xd098' => '\xd0b8', '\xd099' => '\xd0b9', '\xd09a' => '\xd0ba', '\xd09b' => '\xd0bb', 
		'\xd09c' => '\xd0bc', '\xd09d' => '\xd0bd', '\xd09f' => '\xd0bf', '\xd0a2' => '\xd182', '\xd0a3' => '\xd183', 
		'\xd184' => '\xd0a4', '\xd0a5' => '\xd185', '\xd0a6' => '\xd186', '\xd0a7' => '\xd187', '\xd0a8' => '\xd188', 
		'\xd0a9' => '\xd189', '\xd0ab' => '\xd18b', '\xd0aa' => '\xd18a', '\xd0ac' => '\xd18c', '\xd0ad' => '\xd18d', 
		'\xd0ae' => '\xd18e', '\xd0af' => '\xd18f', '\xc484' => '\xc485', '\xc486' => '\xc487', '\xc498' => '\xc499', 
		'\xc581' => '\xc582', '\xc583' => '\xc584', '\xc59a' => '\xc59b', '\xc5b9' => '\xc5ba', '\xc5bb' => '\xc5bc', 
	);
	return strtolower(str_replace(array_keys($charMap), array_values($charMap), $text));
}

function exploreEncoding($str) {
	$row = array(
		'top' => array(),
		'bot' => array(),
	);
	for ($i = 0; $i < strlen($str); ++$i) {
		$row['top'][] = '\x'.bin2hex($str[$i]);
		$row['bot'][] = $str[$i];
	}
	echo print_m($str)."\n";
	echo print_m(implode(' ', $row['top']));
	echo print_m(implode('    ', $row['bot']));
}

/**
 * Returns positive result as used by mathematicians.
 * See http://bugs.php.net/bug.php?id=22527
 */
function mod($val, $modulus) {
	$r = $val % $modulus;
	return $r < 0 ? $r + $modulus : $r;
}

/**
 * Convert an RGB triplet to HSV.
 */
function rgb2hsv($rgb) {
   $var_R = ($rgb[0] / 255);
   $var_G = ($rgb[1] / 255);
   $var_B = ($rgb[2] / 255);

   $var_Min = min($var_R, $var_G, $var_B);
   $var_Max = max($var_R, $var_G, $var_B);
   $del_Max = $var_Max - $var_Min;

   $v = $var_Max;

   if ($del_Max == 0) {
      $h = 0;
      $s = 0;
   } else {
      $s = $del_Max / $var_Max;

      $del_R = ( ( ( $var_Max - $var_R ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
      $del_G = ( ( ( $var_Max - $var_G ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
      $del_B = ( ( ( $var_Max - $var_B ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;

      if      ($var_R == $var_Max) $h = $del_B - $del_G;
      else if ($var_G == $var_Max) $h = ( 1 / 3 ) + $del_R - $del_B;
      else if ($var_B == $var_Max) $h = ( 2 / 3 ) + $del_G - $del_R;

      if ($h < 0) $h++;
      if ($h > 1) $h--;
   }

   return array($h, $s, $v);
}

/**
 * Convert an HSV triplet to RGB.
 */
function hsv2rgb($hsv) {
	list($h, $s, $v) = $hsv;
    if ($s == 0) {
        $r = $g = $b = $v * 255;
    } else {
        $var_H = $h * 6;
        $var_i = floor( $var_H );
        $var_1 = $v * ( 1 - $s );
        $var_2 = $v * ( 1 - $s * ( $var_H - $var_i ) );
        $var_3 = $v * ( 1 - $s * (1 - ( $var_H - $var_i ) ) );

        if       ($var_i == 0) { $var_R = $v    ; $var_G = $var_3; $var_B = $var_1; }
        else if  ($var_i == 1) { $var_R = $var_2; $var_G = $v    ; $var_B = $var_1; }
        else if  ($var_i == 2) { $var_R = $var_1; $var_G = $v    ; $var_B = $var_3; }
        else if  ($var_i == 3) { $var_R = $var_1; $var_G = $var_2; $var_B = $v    ; }
        else if  ($var_i == 4) { $var_R = $var_3; $var_G = $var_1; $var_B = $v    ; }
        else                   { $var_R = $v    ; $var_G = $var_1; $var_B = $var_2; }

        $r = $var_R * 255;
        $g = $var_G * 255;
        $b = $var_B * 255;
    }
    return array((int)$r, (int)$g, (int)$b);
}

function serialize_fix($serialized) {
	return preg_replace_callback(
	    '!(?<=^|;)s:(\d+)(?=:"(.*?)";(?:}|a:|s:|b:|i:|o:|N;))!s',
	     create_function('$match', 'return \'s:\' . strlen($match[2]);'),
	     $serialized
	);
}

function myUnserialize($serialized) {
	$data = @unserialize($serialized);
	if ($data !== false) {
		return $data;
	}
	$serialized = serialize_fix($serialized);
	return unserialize($serialized);
}

function stripHTMLComments($str)
    /* Geschachtelte Kommentare werden nicht unterstuetzt. */
    {
        $str = preg_replace("/(\<\!\-\-.*\-\-\>)/sU", "", $str);
        return $str;
    } 
    
function stripLocalWindowsLinks($str) {
    /* Entferne Dinge wie
    <link rel="File-List" href="http://www.shop.de/file:///C:DOKUME~1ADMINI~1LOKALE~1Tempmsohtml1^@1clip_filelist.xml" />
    verwirren die XML-Verarbeitung und sind ueberfluessig weil sie eh auf nichts zugreifbares zeigen
    */
	while ((($pos = strpos($str, '<link')) !== false)
        && ($posF = strpos($str, 'file:///C:',$pos) !== false)) {
		$end = strpos($str, '/>', $pos);
		if ($end !== false) {
			$end += 2;
		} else {
			$end = 3;
		}
		$str = substr($str, 0, $pos).substr($str, $end);
	}
	return $str;
}

function stripEvilBlockTags($str, $unallowedTags = false) {
	$str = str_replace("\r", "\n", str_replace("\r\n", "\n", $str));
	
	/* HTML Comments */
	$str = stripHTMLComments($str); 

	/* Evil Tags */
	if (!is_array($unallowedTags) || empty($unallowedTags)) {
		$unallowedTags = array('style', 'script', 'form');
	}
	$openCloseRegex = '/(<\/?'.implode('[^>]*>|<\/?', $unallowedTags).'[^>]*>)/i';
	$openRegex = '/(<'.implode('[^>]*>|<', $unallowedTags).'[^>]*>)/i';
	$closeRegex = '/(<\/'.implode('>|<\/', $unallowedTags).'>)/i';	

	$str = preg_replace($openRegex, "\n$1", $str);
	$str = preg_replace($closeRegex, "$1\n", $str);
	
	/* Get rid of 'em */
	$matches = preg_split($openCloseRegex, $str, -1, PREG_SPLIT_DELIM_CAPTURE);
	if (!empty($matches)) {
		//echo print_m($matches, true);
		$inTag = false;
		foreach($matches as $key => $item) {
			if (preg_match($closeRegex, $item)) {
				$inTag = false;
				unset($matches[$key]);
			}
			if (preg_match($openRegex, $item)) {
				$inTag = true;
			}
			//echo var_dump_pre($inTag, true).' '.$matches[$key]."\n";
			if ($inTag) {
				unset($matches[$key]);
			}
		}
		$str = implode("\n", $matches);
	}
	/* Leerzeilen entfernen */
	$str = preg_replace("/(([\s|\t]*)[\n|\r]+)/i", "\n", $str);
	return $str;
}

/**
 * @author Alexander Papst (http://derpapst.eu/)
 * @author nauthiz693 at gmail dot com
 * @author nick AT optixsolutions DOT co DOT uk
 */
function strip_tags_attributes($string, $allowtags = '', $allowattributes = '') {
    $string = strip_tags($string, $allowtags);
    if (empty($allowtags)) {
    	return $string;
    }
    if ($allowattributes == '_keep_all_') {
    	return $string;
    }
    if (empty($allowattributes)) {
		return preg_replace_callback("/<(\/?[a-zA-Z0-9]*)([^>]*)>/i", create_function(
		    '$matches',
		    'return \'<\'.trim(trim(strtolower($matches[1])).\' \'.
		            trim(preg_replace("/.*=(\"[^\"]*\"|\'[^\']*\')/i", "", $matches[2]))).\'>\';'
		), $string);
    }
    if (!is_array($allowattributes)) {
        $allowattributes = explode(",", $allowattributes);
    }
    array_walk($allowattributes, create_function('&$a', '$a = trim($a);'));
    if (is_array($allowattributes)) {
        $allowattributes = "(?<!".implode(")(?<!",$allowattributes).")";;
    }
	$string = preg_replace_callback("/<(\/?[a-zA-Z0-9]*)([^>]*)>/i", create_function(
	    '$matches',
	    'return \'<\'.trim(trim(strtolower($matches[1])).\' \'.
	            trim(preg_replace("/(\s|\n|\t)*[^ =]*'.$allowattributes.'=(\"[^\"]*\"|\'[^\']*\')/i", "", $matches[2]))).
	            \'>\';'
	), $string);

    return $string;
}

function arrayMap($callback, $arr1) {
	$results = array();
	$args = array();
	if (func_num_args() > 2) {
		$args = (array) array_shift(array_slice(func_get_args(), 2));
	}
	foreach($arr1 as $key => $value) {
		$temp = $args;
		array_unshift($temp, $value);
		if (is_array($value)) {
			array_unshift($temp, $callback);
			$results[$key] = call_user_func_array('arrayMap', $temp);
		} else {
			$results[$key] = call_user_func_array($callback, $temp);
		}
	}
	return $results;
}

function stripObjectsAndResources($a, $lv = 0) {
	if (empty($a) || ($lv >= 10)) return $a;
	foreach ($a as $key => &$value) {
		if (is_object($value)) {
			$value = 'OBJECT ('.get_class($value).')';
		} else if (is_resource($value)) {
			$value = 'RESOURCE ('.get_resource_type($value).')';
		} else if (is_array($value)) {
			$value = stripObjectsAndResources($value, $lv + 1);
		}
	}
	return $a;
}

function prepareErrorBacktrace($offset = 0) {
	if (version_compare(PHP_VERSION, '5.2.5', '>=')) {
		$dbt = @debug_backtrace(true);
	} else {
		$dbt = @debug_backtrace();
	}
	if (empty($dbt)) return array();
	return stripObjectsAndResources(array_slice($dbt, $offset));
}

function decodeData(&$array, $fieldName) {
	if (empty($array)) {
		return false;
	}
	foreach ($array as &$item) {
		$data = unserialize($item[$fieldName]);
		unset($item[$fieldName]);
		if (array_key_exists(0, $data)) {
			mergeArrays($item, $data);
		} else {
			$item = array_merge($item, $data);
		}
	}
}

if (!function_exists('is_date')) {
  function is_date($date) {
	  return (bool)preg_match('/^([1-2][0-9]{3})-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $date);
  }
}

if (!function_exists('is_time')) {
  function is_time($time) {
	  return (bool)preg_match('/^([0-1][0-9]|2[0-4]):([0-5][0-9]):([0-5][0-9])$/', $time);
  }
}

if (!function_exists('is_datetime')) {
  function is_datetime($dt) {
	  return (bool)preg_match('/^([1-2][0-9]{3})-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])\s'.
				              '([0-1][0-9]|2[0-4]):([0-5][0-9]):([0-5][0-9])$/', $dt);
  }
}

function unix_timestamp($datetime = null) {
	if (null == $datetime) return time();
	else if(!is_datetime($datetime)) return 0;
	return mktime(substr($datetime,11,2), substr($datetime,14,2), substr($datetime,17,2),
			substr($datetime,5,2), substr($datetime,8,2), substr($datetime,0,4));
}

function json_indent($json) {
	if (is_array($json) || is_object($json)) {
		$json = json_encode($json);
	}
    $result      = '';
    $pos         = 0;
    $strLen      = strlen($json);
    $indentStr   = '    ';
    $newLine     = "\n";
    $prevChar    = '';
    $outOfQuotes = true;

    for ($i = 0; $i <= $strLen; ++$i) {

        // Grab the next character in the string.
        $char = substr($json, $i, 1);

        // Are we inside a quoted string?
        if ($char == '"' && $prevChar != '\\') {
            $outOfQuotes = !$outOfQuotes;
        
        // If this character is the end of an element, 
        // output a new line and indent the next line.
        } else if (($char == '}' || $char == ']') && $outOfQuotes) {
            $result .= $newLine;
            --$pos;
            for ($j = 0; $j < $pos; ++$j) {
                $result .= $indentStr;
            }
        }
        
        // Add the character to the result string.
        $result .= $char;

        if ($outOfQuotes && ($char == ':')) {
        	$result .= ' ';
        }

        // If the last character was the beginning of an element, 
        // output a new line and indent the next line.
        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
            $result .= $newLine;
            if ($char == '{' || $char == '[') {
                ++$pos;
            }
            
            for ($j = 0; $j < $pos; ++$j) {
                $result .= $indentStr;
            }
        }
        
        $prevChar = $char;
    }

    return $result;
}

function renderDataGrid($data) {
	if (empty($data) || !array_key_exists(0, $data)) return false;
	echo '
		<table class="datagrid autoOddEven hover">
			<thead><tr><th>'.implode('</th><th>', array_keys($data[0])).'</th></tr></thead>
			<tbody>';
	foreach ($data as $row) {
		echo '
				<tr>';
		foreach ($row as $key => $item) {
			echo '
					<td class="'.strtolower($key).'">'.$item.'</td>';
		}
		echo '
				</tr>';
	}
	echo '
			</tbody>
		</table>';
	return true;
}

/******************************************************************************\
 *                        Magnalister Specific Functions                      *
\******************************************************************************/

function toURL($a = array(), $b = array(), $forAjax = false) {
	if (is_string($a) && !empty($a)) {
		$linkparams = explode(',', substr($a, 1, -1));
		foreach ($linkparams as $key => $param) {
			if (trim($param) != '') {
				$param = explode(':', $param);
				$linkparams[trim($param[0])] = trim($param[1]);
			}
			unset($linkparams[$key]);
		}
		$a = $linkparams;
	}
	
	if (!is_array($a)) {
		$a = array();
	}

	if (!empty($_GET)) {
		foreach ($_GET as $get_key => $get_value) {
			if ($get_value == session_id()) {
				$a[$get_key] = $get_value;
			}
		}
	}

	if ($b === true) {
		$forAjax = true;
		$b = array();
	}

	$a = array_merge($a, $b);

	if (empty($a)) {
		return FILENAME_MAGNALISTER;
	}
	
	$u = '';
	foreach ($a as $k => $v) {
		$u .= $k.'='.$v.'&'.($forAjax ? '' : 'amp;');
	}
	return FILENAME_MAGNALISTER.'?'.substr($u, 0, ($forAjax ? -1 : -5));
}

function magnaGetLanguageCode($lang) {
	return MagnaDB::gi()->fetchOne('SELECT code FROM '.TABLE_LANGUAGES.' WHERE directory=\''.$lang.'\' LIMIT 1');
}

function magnaGetAvailableLanguages() {
	$handle = opendir(DIR_MAGNALISTER.'lang/');
	$langs = array();
	while (false !== ($file = readdir($handle))) {
		if (@is_dir($file)) continue;
		if (preg_match('/^(.*)\.php$/', $file, $match)) {
			$langs[] = $match[1];
		}
	}
	/* Default language is german */
	if (($pos = find_in_array('german', $langs)) !== false) {
		unset($langs[$pos]);
		$langs = array_merge(array('german'), $langs);
	}
	return $langs;
}
