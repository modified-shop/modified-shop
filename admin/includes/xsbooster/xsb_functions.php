<?php
## Class-independent helper functions for xs:booster
## Klassenlose PHP-Hilfsfunktionen fuer den xs:booster
##
## Copyright (c) 2009 xt:booster Ltd.
## http://www.xsbooster.com
##
## Licensed under GNU/GPL
##


// Array in XML-String umwandeln
function xmlize($r){
        if(!isset($r)) return false;
        if(is_string($r)) return '<0>'.$r.'</0>';
        if(!is_array($r)) return false;
        $rr= Array();
        $rrstack= Array();
        $rrstack[0]=$r;
        $rr=$rrstack[0];
        $mykeys=Array();
        $mykeys[0]=array_keys($r);
        $istack=Array();
        $istack[0]=0;
        $i=$istack[0];
        $current_depth=0;
        $myresult='';
        while((isset($rr[$mykeys[$current_depth][$i]])) || $current_depth>0)
        {
                //keine blaetter mehr am ast, tag abschliessen
                if(!isset($rr[$mykeys[$current_depth][$i]]))
                {
                        --$current_depth;
                        $i=$istack[$current_depth];
                        $myresult .= '</'.$mykeys[$current_depth][$i].'>';
                        $rr=$rrstack[$current_depth];
                        $i++;
                        continue;
                }
                //tag oeffnen
                $myresult .= '<'.$mykeys[$current_depth][$i].'>';
                if(is_array($rr[$mykeys[$current_depth][$i]]))
                        {
                        //ast, heruntergehen
                         $mykeys[$current_depth+1]=Array();
                         $mykeys[$current_depth+1]=array_keys($rr[$mykeys[$current_depth][$i]]);
                         $rrstack[$current_depth+1]=$rr[$mykeys[$current_depth][$i]];
                         $rr=$rrstack[$current_depth+1];
                         $istack[$current_depth]=$i;
                         $current_depth++;
                         $i = 0;
                        }
                else
                        {
                        //blatt
                        $myresult .= $rr[$mykeys[$current_depth][$i]];
                        $myresult .= '</'.$mykeys[$current_depth][$i].'>';
                        $i++;
                        }
        }
        return $myresult;
}

// Funktioniert NUR fuer XML-Strings mit disjunkten Tags (vorher aus einem Array erzeugt)
function unXmlize($r)
{
  if(!isset($r)) return '';
  if((substr($r,0,1) != '<') || (substr($r,-1,1) != '>')) return $r;
  $result = Array();
  while(!empty($r))
  {
    $current_key = substr($r,1,strpos($r,'>')-1);
    $keylen = strlen($current_key);
    if(strpos($r,'</'.$current_key.'>', $keylen+2) === False)
    // kein xml tag sondern nur text der so aussieht
    { return $r; } 
    $current_part = substr($r, $keylen+2,strpos($r,'</'.$current_key.'>',$keylen+2)-$keylen-2);
    // product description nicht zerteilen
    if('DESCRIPTION' == $current_key)
    { $result["$current_key"] = $current_part; }
    else
    { $result["$current_key"] = unXmlize($current_part); }
    $r = substr($r,strpos($r,'</'.$current_key.'>')+$keylen+3);
  }
  return $result;
}

// String nach ISO-8859-1 umwandeln, wenn er Zeichen enthaelt die nicht in ISO-8859-1 vorkommen

function toIso8859_1($inputstring) {
  	$not_iso_chars = utf8_encode (
		"\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0a\x0b\x0c\x0d\x0e\x0f".
		"\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1a\x1b\x1c\x1d\x1e\x1f".
		"\x7f".
		"\x80\x81\x82\x83\x84\x85\x86\x87\x88\x89\x8a\x8b\x8c\x8d\x8e\x8f".
		"\x90\x91\x92\x93\x94\x95\x96\x97\x98\x99\x9a\x9b\x9c\x9d\x9e\x9f"
	);

	if (strpbrk($inputstring,$not_iso_chars) !== false) {
		$inputstring = utf8_decode($inputstring);
	}
	return $inputstring;
}

// Schauen ob String UTF-8-korrekt ist
function is_utf8($str) {
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

function utf8_substr($str, $start) {
	preg_match_all("/./su", $str, $ar);
	
	if(func_num_args() >= 3) {
		$end = func_get_arg(2);
		return implode('', array_slice($ar[0], $start, $end));
	} else {
		return implode('', array_slice($ar[0], $start));
	}
}

function xsb_db_first($resource) {
	$row = xtc_db_fetch_array($resource);
	if (!is_array($row) || empty($row)) {
		return false;
	}
	foreach ($row as $item) {
		return $item;
	}
}

if (!function_exists('strpbrk')) {
	function strpbrk( $haystack, $char_list ) {
	    $strlen = strlen($char_list);
	    $found = false;
	    for( $i=0; $i<$strlen; $i++ ) {
	        if( ($tmp = strpos($haystack, $char_list{$i})) !== false ) {
	            if( !$found ) {
	                $pos = $tmp;
	                $found = true;
	                continue;
	            }
	            $pos = min($pos, $tmp);
	        }
	    }
	    if( !$found ) {
	        return false;
	    }
	    return substr($haystack, $pos);
	}
}
