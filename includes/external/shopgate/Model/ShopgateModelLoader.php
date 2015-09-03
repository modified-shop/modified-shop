<?php

/**
 * Shopgate GmbH
 *
 * URHEBERRECHTSHINWEIS
 *
 * Dieses Plugin ist urheberrechtlich geschützt. Es darf ausschließlich von Kunden der Shopgate GmbH
 * zum Zwecke der eigenen Kommunikation zwischen dem IT-System des Kunden mit dem IT-System der
 * Shopgate GmbH über www.shopgate.com verwendet werden. Eine darüber hinausgehende Vervielfältigung, Verbreitung,
 * öffentliche Zugänglichmachung, Bearbeitung oder Weitergabe an Dritte ist nur mit unserer vorherigen
 * schriftlichen Zustimmung zulässig. Die Regelungen der §§ 69 d Abs. 2, 3 und 69 e UrhG bleiben hiervon unberührt.
 *
 * COPYRIGHT NOTICE
 *
 * This plugin is the subject of copyright protection. It is only for the use of Shopgate GmbH customers,
 * for the purpose of facilitating communication between the IT system of the customer and the IT system
 * of Shopgate GmbH via www.shopgate.com. Any reproduction, dissemination, public propagation, processing or
 * transfer to third parties is only permitted where we previously consented thereto in writing. The provisions
 * of paragraph 69 d, sub-paragraphs 2, 3 and paragraph 69, sub-paragraph e of the German Copyright Act shall remain unaffected.
 *
 * @author Shopgate GmbH <interfaces@shopgate.com>
 */
class ShopgateModelLoader
{
	/**
	 * @var array
	 */
	private $classWhiteList = array();
	
	/**
	 * @var array
	 */
	private $modelExtraTypes = array("xml", "cart");
	
	const SHOPGATE_MODEL_SUFFIX = "Model";
	const SHOPGATE_MODEL_PREFIX = "Shopgate";
	
	/**
	 * @param array $whiteList
	 */
	public function __construct($whiteList = array())
	{
		foreach ($whiteList as $className) {
			$modelName = self::SHOPGATE_MODEL_PREFIX . ucfirst($className);
			foreach ($this->modelExtraTypes as $extraType) {
				$this->classWhiteList[] = $modelName . ucfirst($extraType)
					. self::SHOPGATE_MODEL_SUFFIX;
			}
			$this->classWhiteList[] = $modelName . self::SHOPGATE_MODEL_SUFFIX;
		}
	}
	
	/**
	 * Sort the whiteList
	 */
	public function sortWhiteList()
	{
		$tmpList = array_flip($this->classWhiteList);
		asort($tmpList);
		$this->classWhiteList = array_flip($tmpList);
	}
	
	/**
	 * @param string $file
	 *
	 * @return bool
	 */
	private function isValidFile($file)
	{
		return (strpos($file, get_class($this)) !== false
			|| in_array(basename($file, ".php"), $this->classWhiteList) == false
			|| $file == "."
			|| $file == "..")
			? false
			: true;
	}
	
	/**
	 * @param $path
	 *
	 * @return bool
	 */
	private function isValidDir($path)
	{
		$fl = substr($path, strrpos($path, '/') + 1);
		
		return ($fl == "." || $fl == ".." || !is_dir($path)) ? false : true;
	}
	
	/**
	 * @param $file
	 *
	 * @return bool
	 */
	private function isPhpFile($file)
	{
		return (pathinfo($file, PATHINFO_EXTENSION) == "php");
	}
	
	/**
	 * include recursively all Shopgate model files
	 *
	 * @param string $dir
	 * @param int    $maxDepth
	 * @param int    $depth
	 */
	public function includeModels($dir = "", $maxDepth = 10, $depth = 0)
	{
		
		if ($dir == "") {
			$dir = dirname(__FILE__);
		}
		
		if ($maxDepth == $depth) {
			return;
		}
		
		$directoryData = scandir($dir);
		foreach ($directoryData AS $data) {
			$completePath = $dir . "/" . $data;
			if ($this->isValidDir($completePath)) {
				$this->includeModels($completePath, $maxDepth, $depth++);
			} elseif ($this->isPhpFile($completePath)
				&& $this->isValidFile(
					$data
				)
			) {
				include $completePath;
			}
			
		}
	}
} 
