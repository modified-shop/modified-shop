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
 * $Id$
 *
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

class MagnaCompatibleHelper {
	public static function processCheckinErrors($result, $mpID) {
		$fieldname = 'MARKETPLACEERRORS';
		$dbCharSet = MagnaDB::gi()->mysqlVariableValue('character_set_connection');
		if (('utf8mb3' == $dbCharSet) || ('utf8mb4' == $dbCharSet)) {
			# means the same for us
			$dbCharSet = 'utf8';
		}
		if ($dbCharSet != 'utf8') {
			arrayEntitiesToLatin1($result[$fieldname]);
		}
		if (!isset($result[$fieldname]) || empty($result[$fieldname])) {
			return;
		}
		foreach ($result[$fieldname] as $err) {
			if (!isset($err['AdditionalData'])) {
				$err['AdditionalData'] = array();
			}
			$err = array (
				'mpID' => $mpID,
				'errormessage' => $err['ErrorMessage'],
				'dateadded' => $err['DateAdded'],
				'additionaldata' => serialize($err['AdditionalData']),
			);
			MagnaDB::gi()->insert(TABLE_MAGNA_COMPAT_ERRORLOG, $err);
		}
	}
	
	public static function checkProductSaveJsonArray($aCheckArray) {
		foreach ($aCheckArray as $sKey => &$sEntry) {
			if (empty($sEntry)) {
				unset($aCheckArray[$sKey]);
			}
		}
		
		if (0 < count($aCheckArray)) {
			return json_encode($aCheckArray);
		} else {
			return '';
		}
	}

	public static function encodeData($mValue) {
		if (is_array($mValue)) {
			$sValue = json_encode($mValue);
		} elseif (is_object($mValue)) {
			$sValue = serialize($mValue);
		} elseif ($mValue !== null) {
			$sValue = (string) $mValue;
		} else {
			$sValue = null;
		}
		return $sValue;
	}

	/**
	 * Check length of the title slice it and adds dots if is needed.
	 *
	 * @param $text
	 * @param $length
	 * @return string
	 */
	public static function sanitizeTitle($text, $length)
	{
		if (isset($text) && mb_strlen($text, 'UTF-8') > $length) {
			$text = mb_substr($text, 0, $length - 3, 'UTF-8') . '...';
		}

		return $text;
	}

	/**
	 * Sanitazes description and preparing it if marketplace doesn't allow html tags.
	 *
	 * @param $sDescription
	 * @param $length
	 * @return mixed|string
	 */
	public static function sanitizeDescription($sDescription, $length = false)
	{
		# preg_replace could return NULL at 5.2.0 to 5.3.6 - "/(\s*<br[^>]*>\s*)*$/"
		# tested at: http://3v4l.org/WGcod
		if (version_compare(PHP_VERSION, '5.2.0', '>=') && version_compare(PHP_VERSION, '5.3.6', '<=')) {
			@ini_set('pcre.backtrack_limit', '10000000');
			@ini_set('pcre.recursion_limit', '10000000');
		}
		$sDescription = preg_replace("#(<\\?div>|<\\?li>|<\\?p>|<\\?h1>|<\\?h2>|<\\?h3>|<\\?h4>|<\\?h5>|<\\?blockquote>)([^\n])#i", "$1\n$2", $sDescription);
		$sDescription = preg_replace('/&nbsp;/', " ", $sDescription);
		// Replace <br> tags with new lines
		$sDescription = preg_replace('/<[h|b]r[^>]*>/i', "\n", $sDescription);
		$sDescription = trim(strip_tags($sDescription));
		// Normalize space
		$sDescription = str_replace("\r", "\n", $sDescription);
		$sDescription = preg_replace("/\n{3,}/", "\n\n", $sDescription);

		if ($length) {
			if (strlen($sDescription) > $length) {
				$sDescription = mb_substr($sDescription, 0, $length - 3, 'UTF-8') . '...';
			} else {
				$sDescription = mb_substr($sDescription, 0, $length, 'UTF-8');
			}
		}

		return $sDescription;
	}

	/**
	 * Truncates HTML text without breaking HTML structure.
	 * Source: https://dodona.wordpress.com/2009/04/05/how-do-i-truncate-an-html-string-without-breaking-the-html-code
	 *
	 * @param string $text String to truncate.
	 * @param integer $length Length of returned string, including ellipsis.
	 * @param string $ending Ending to be appended to the trimmed string.
	 * @param boolean $exact If false, $text will not be cut mid-word
	 * @param boolean $considerHtml If true, HTML tags would be handled correctly
	 * @return string Trimmed string.
	 */
	public static function truncateString($text, $length = 100, $ending = '...', $exact = false, $considerHtml = true) {
		if ($considerHtml) {
			// if the plain text is shorter than the maximum length, return the whole text
			if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
				return $text;
			}

			// splits all html-tags to scannable lines
			preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
			$total_length = strlen($ending);
			$open_tags = array();
			$truncate = '';
			foreach ($lines as $line_matchings) {
				// if there is any html-tag in this line, handle it and add it (uncounted) to the output
				if (!empty($line_matchings[1])) {
					// if it's an "empty element" with or without xhtml-conform closing slash
					if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
						// do nothing
						// if tag is a closing tag
					} else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
						// delete tag from $open_tags list
						$pos = array_search($tag_matchings[1], $open_tags);
						if ($pos !== false) {
							unset($open_tags[$pos]);
						}
						// if tag is an opening tag
					} else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
						// add tag to the beginning of $open_tags list
						array_unshift($open_tags, strtolower($tag_matchings[1]));
					}
					// add html-tag to $truncate'd text
					$truncate .= $line_matchings[1];
				}
				// calculate the length of the plain text part of the line; handle entities as one character
				$content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
				if ($total_length + $content_length > $length) {
					// the number of characters which are left
					$left = $length - $total_length;
					$entities_length = 0;
					// search for html entities
					if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
						// calculate the real length of all entities in the legal range
						foreach ($entities[0] as $entity) {
							if ($entity[1] + 1 - $entities_length <= $left) {
								$left--;
								$entities_length += strlen($entity[0]);
							} else {
								// no more characters left
								break;
							}
						}
					}
					$truncate .= substr($line_matchings[2], 0, $left + $entities_length);
					// maximum length is reached, so get off the loop
					break;
				} else {
					$truncate .= $line_matchings[2];
					$total_length += $content_length;
				}
				// if the maximum length is reached, get off the loop
				if ($total_length >= $length) {
					break;
				}
			}
		} else {
			if (strlen($text) <= $length) {
				return $text;
			} else {
				$truncate = substr($text, 0, $length - strlen($ending));
			}
		}

		// if the words shouldn't be cut in the middle...
		if (!$exact) {
			// ...search the last occurrence of a space...
			$spacepos = strrpos($truncate, ' ');
			if (isset($spacepos)) {
				// ...and cut the text in this position
				$truncate = substr($truncate, 0, $spacepos);
			}
		}

		// add the defined ending to the text
		$truncate .= $ending;
		if ($considerHtml) {
			// delete unclosed tags in the end of string
			$truncate = preg_replace('/]*$/', '', $truncate);

			// close all unclosed html-tags
			foreach ($open_tags as $tag) {
				$truncate .= '</' . $tag . '>';
			}
		}

		return $truncate;
	}
}
