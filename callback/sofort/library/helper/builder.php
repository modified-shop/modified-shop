<?php
abstract class Element {
	public abstract function render();
}


class Text extends Element {
	
	public $text;
	
	public $escape = false;
	
	
	public function __construct($text, $escape = false, $trim = true) {
		$this->text = $trim ? trim($text) : $text;
		$this->escape = $escape;
	}
	
	public function render() {
		return $this->escape ? htmlspecialchars($this->text) : $this->text;
	}
}


class Tag extends Element {
	
	public $tagname = '';
	
	public $attributes = array();
	
	public $children = array();
	
	
	public function __construct($tagname, array $attributes = array(), $children = array()) {
		$this->tagname = $tagname;
		$this->attributes = $attributes;
		$this->children = is_array($children) ? $children : array($children);
	}
	
	
	public function render() {
		$output = '';
		$attributes = '';
		
		foreach ($this->children as $child) {
			$output .= is_object($child) ? $child->render() : $child;
		}
		
		foreach ($this->attributes as $key => $value) {
			$attributes .= " $key=\"$value\"";
		}
		
		return $this->_render($output, $attributes);
	}
	
	
	protected function _render($output, $attributes) {
		return $output !== '' ? "<{$this->tagname}{$attributes}>{$output}</{$this->tagname}>" : "<{$this->tagname}{$attributes} />";
	}
}


class HtmlTag extends Tag {
	
	private static $selfClosingTags = array('base', 'meta', 'link', 'hr', 'br', 'param', 'img', 'area', 'input', 'col');
	
	
	public function __construct($tagname, array $attributes = array(), $children = array()) {
		$tagname = strtolower($tagname);
		$loweredAttributes = array();
		
		foreach ($attributes as $key => $value) {
			$loweredAttributes[strtolower($key)] = $value;
		}
		
		parent::__construct($tagname, $loweredAttributes, $children);
	}
	
	
	protected function _render($output, $attributes) {
		return in_array($this->tagname, self::$selfClosingTags) ? "<{$this->tagname}{$attributes} />" : "<{$this->tagname}{$attributes}>{$output}</{$this->tagname}>";
	}
}
?>