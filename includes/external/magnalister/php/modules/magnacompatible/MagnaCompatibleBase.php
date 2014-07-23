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

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

abstract class MagnaCompatibleBase {
	const GENERICRESOURCE = 'MagnaCompatible';

	protected $marketplace = '';
	protected $mpID = 0;
	protected $isAjax = false;
	protected $resources = array();
	protected $specificResource = '';

	public function __construct(&$params) {
		foreach ($params as $attr => &$v) {
			if (isset($this->$attr)) {
				$this->$attr = &$v;
			}
		}
	}
	
	protected function getResourcePath($dir, $file) {
		$pathTmpl = DIR_MAGNALISTER_MODULES.'%s'.'/%s/%s'.$file.'.php';
		if (is_string($this->specificResource) && !empty($this->specificResource)) {
			$lpath = sprintf($pathTmpl, strtolower($this->specificResource), $dir, ucfirst($this->specificResource));
			//echo $lpath.'<br>';
			if (file_exists($lpath)) {
				return $lpath;
			}
		}
		$lpath = sprintf($pathTmpl, strtolower(self::GENERICRESOURCE), $dir, ucfirst(self::GENERICRESOURCE));
		//echo $lpath.'<br>';
		if (file_exists($lpath)) {
			return $lpath;
		}
		return false;
	}
	
	protected function loadResource($dir, $file) {
		if (($path = $this->getResourcePath($dir, $file)) === false) {
			return false;
		}
		require_once($path);
		$class = substr(basename((string)$path), 0, -4);
		//echo $class.'<br>';
		if (!class_exists($class)) {
			return false;
		}
		return $class;
	}
	
	abstract protected function process();
}
