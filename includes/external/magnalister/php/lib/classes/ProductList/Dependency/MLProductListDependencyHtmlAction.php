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
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
require_once DIR_MAGNALISTER_INCLUDES.'lib/classes/ProductList/Dependency/MLProductListDependency.php';
class MLProductListDependencyHtmlAction extends MLProductListDependency {
	
	protected $sContent='';
	
	protected function getDefaultConfig() {
		return array(
			'actionTopTemplate'          => '',
			'actionBottomLeftTemplate'   => '',
			'actionBottomCenterTemplate' => '',
			'actionBottomRightTemplate'  => '',
		);
	}
	
	protected function getTemplate($sType) {
		$this->sContent = $this->getConfig($sType.'Template');
		return ($this->getContent() == '') ? '' : 'html';
	}
	
	public function getActionTopTemplate() {
		return $this->getTemplate('actionTop');
	}
	
	public function getActionBottomLeftTemplate() {
		return $this->getTemplate('actionBottomLeft');
	}
	
	public function getActionBottomCenterTemplate() {
		return $this->getTemplate('actionBottomCenter');
	}
	
	public function getActionBottomRightTemplate() {
		return $this->getTemplate('actionBottomRight');
	}
	
	protected function setContent($sContent) {
		$this->sContent = $sContent;
	}
	
	public function getContent() {
		return $this->sContent;
	}

}
