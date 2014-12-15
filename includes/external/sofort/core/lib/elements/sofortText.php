<?php
/**
 * Sofort Text Element
 * 
 * @author SOFORT AG (integration@sofort.com)
 *
 * @copyright 2010-2014 SOFORT AG
 *
 * @license Released under the GNU LESSER GENERAL PUBLIC LICENSE (Version 3)
 * @license http://www.gnu.org/licenses/lgpl.html
 *
 * @version SofortLib 2.1.1
 *
 * @link http://www.sofort.com/ official website
 */

/**
 *
 * Implementation of simple text
 *
 */
class SofortText extends SofortElement {
	
	public $text;
	
	public $escape = false;
	
	
	/**
	 * Constructor for SofortText
	 *
	 * @param string $text
	 * @param bool $escape (default false)
	 * @param bool $trim (default true)
	 * @return \SofortText
	 */
	public function __construct($text, $escape = false, $trim = true) {
		$this->text = $trim ? trim($text) : $text;
		$this->escape = $escape;
	}
	
	
	/**
	 * Renders the element (override)
	 * 
	 * @see SofortElement::render()
	 * @return string
	 */
	public function render() {
		return $this->escape ? htmlspecialchars($this->text) : $this->text;
	}
}