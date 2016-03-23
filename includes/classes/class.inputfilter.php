<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2005 Daniel Morris dan@rootcube.com
   contributors: Gianpaolo Racca, Ghislain Picard, Marco Wandschneider, 
                 Chris, Tobin, Andrew Eddie.
   Modification: Louis Landry
   
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

class InputFilter {
	var $tagsArray; // default = empty array
	var $attrArray; // default = empty array

	var $tagsMethod; // default = 0
	var $attrMethod; // default = 0

	var $xssAuto; // default = 1

	/** 
	  * Constructor for inputFilter class. Only first parameter is required.
	  * @access constructor
	  * @param Array $tagsArray - list of user-defined tags
	  * @param Array $attrArray - list of user-defined attributes
	  * @param int $tagsMethod - 0= allow just user-defined, 1= allow all but user-defined
	  * @param int $attrMethod - 0= allow just user-defined, 1= allow all but user-defined
	  * @param int $xssAuto - 0= only auto clean essentials, 1= allow clean blacklisted tags/attr
	  */
	function __construct($tagsArray = array (), $attrArray = array (), $tagsMethod = 0, $attrMethod = 0, $xssAuto = 1) {
		// make sure user defined arrays are in lowercase
    for ($i = 0; $i < count($tagsArray); $i ++) {
      $tagsArray[$i] = strtolower($tagsArray[$i]);
    }
		for ($i = 0; $i < count($attrArray); $i ++) {
			$attrArray[$i] = strtolower($attrArray[$i]);
		}
		// assign to member vars
		$this->tagsArray = (array) $tagsArray;
		$this->attrArray = (array) $attrArray;
		$this->tagsMethod = $tagsMethod;
		$this->attrMethod = $attrMethod;
		$this->xssAuto = $xssAuto;
	}

	/** 
	  * Method to be called by another php script. Processes for XSS and specified bad code.
	  * @access public
	  * @param Mixed $source - input string/array-of-string to be 'cleaned'
	  * @return String $source - 'cleaned' version of input parameter
	  */
	function process($source)
	{
		// clean all elements in this array    
		if (is_array($source)) {
			foreach ($source as $key => $value) {
				// filter element for XSS and other 'bad' code etc.
				$tmp_key = $key;
				unset ($source[$key]);
				$key = $this->remove($this->decode($key));
				if ($key != $tmp_key) {
					return $source;
				} else {
					if (is_string($value)) {
						$source[$key] = $this->remove($this->decode($value));
					} elseif (is_array($value)) {
						$source[$key] = $this->process($value); 
					}
				}
			}
			return $source;
			// clean this string
		} else {
			if (is_string($source)) {
				// filter source for XSS and other 'bad' code etc.
				return $this->remove($this->decode($source));
				// return parameter as given
			} else {
				return $source;
			}
		}
	}

	/** 
	  * Internal method to iteratively remove all unwanted tags and attributes
	  * @access protected
	  * @param String $source - input string to be 'cleaned'
	  * @return String $source - 'cleaned' version of input parameter
	  */
	function remove($source) {
		$loopCounter = 0;
		// provides nested-tag protection
		while ($source != $this->filterTags($source)) {
			$source = $this->filterTags($source);
			$loopCounter ++;
		}
		return $source;
	}

	/** 
	  * Internal method to strip a string of certain tags
	  * @access protected
	  * @param String $source - input string to be 'cleaned'
	  * @return String $source - 'cleaned' version of input parameter
	  */
	function filterTags($source) {
    //fix null byte injection
    if (strpos($source,"\0")!== false) {return '';}
    if (strpos($source,"\x00")!== false) {return '';}
    if (strpos($source,"\u0000")!== false) {return '';}
    if (strpos($source,"\000")!== false) {return '';}

    if (strtolower(trim($source)) == 'null') { return ''; }
    if (strtolower(trim($source)) == 'now()') { return ''; }
    
    return strip_tags($source);
	}

	/** 
	  * Try to convert to plaintext
	  * @access protected
	  * @param String $source
	  * @return String $source
	  */
	function decode($source='') {
		if ($source!='') {
      // url decode
      $source = decode_htmlentities($source, ENT_QUOTES); 
      // convert decimal
      $source = preg_replace_callback(
        '/&#(\d+);/m',
        create_function (
          '$m',
          'return chr($m[1]);'
        ),
        $source
      );
    
      // convert hex
      $source = preg_replace_callback(
        '/&#x([a-f0-9]+);/mi',
        create_function (
          '$m',
          'return chr(hexdec($m[1]));'
        ),
        $source
      );
		}
		return $source;
	}

	/** 
	  * Method to be called by another php script. Processes for SQL injection
	  * @access public
	  * @param Mixed $source - input string/array-of-string to be 'cleaned'
	  * @return String $source - 'cleaned' version of input parameter
	  */
	function safeSQL($source) {	  
		// clean all elements in this array
	  if (is_array($source)) {
			foreach ($source as $key => $value) {
				// filter element for SQL injection
				if (is_string($value)) {
					$source[$key] = $this->quoteSmart($this->decode($value));
				}
			}
			return $source;
			// clean this string
		} else {
			if (is_string($source)) {
				// filter source for SQL injection
				if (is_string($source))
					return $this->quoteSmart($this->decode($source));
				// return parameter as given
			} else {
				return $source;
			}
		}
	}

	/** 
	  * @author Chris Tobin
	  * @author Daniel Morris
	  * @access protected
	  * @param String $source
	  * @return String $source
	  */
	function quoteSmart($source) {
		// strip slashes
		$source = stripslashes($source);
		
		return $source;
	}
}
?>