<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty close_tags modifier plugin
 *
 * Type:     modifier<br>
 * Name:     close_tags<br>
 * Purpose:  close html tags in text
 * @link     http://www.gunnart.de?p=353
 * @author   dima.exe <dima dot exe at gmail dot com>
 *           Gunnar Tillmann <info at gunnart dot de>
 * @param string
 * @return string
 */
function smarty_modifier_close_tags($string)
{

	$tags = 'a|b|p|i|u|h1|h2|h3|h4|h5|h6|em|strong|code|pre|del|font|span|div|center|table|tr|td|th|tb|form|ul|ol|li|caption|small|dd|dl|dt|fieldset|option|select'; 

	// match opened tags
	if(preg_match_all('/<('.$tags.')[^\/>]*>/i', $string, $start_tags)){
		$start_tags = $start_tags[1];
	
		// match closed tags
		if(preg_match_all('/<\/('.$tags.')>/i', $string, $end_tags)){
			$complete_tags = array();
			$end_tags = $end_tags[1];
	
			foreach($start_tags as $key => $val){   
				$posb = array_search($val, $end_tags);
				if(is_integer($posb)){
					unset($end_tags[$posb]);
				} else {
					$complete_tags[] = $val;
				}
			}

		} else {

			$complete_tags = $start_tags;
		}

		$complete_tags = array_reverse($complete_tags);

		for($i = 0; $i < count($complete_tags); $i++){
			$string .= '</' . $complete_tags[$i] . '>';
		}
	}
	return $string;
}
?>