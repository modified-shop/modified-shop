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

abstract class AbstractProductIdFilter {

	/**
	 * @param array $aConfig some additional info for concrete class
	 */
	abstract public function __construct();

	/**
	 * will be called once also, if $this->isActive() returns dalse
	 * @return $this
	 */
	abstract public function init($aConfig);

	/**
	 * @return bool
	 */
	abstract public function isActive();

	/**
	 * @return array $key=>$value
	 */
	abstract public function getUrlParams();

	/**
	 * @return string rendered html
	 */
	abstract public function getHtml();

	/**
	 * @return array shop-product-ids
	 */
	abstract public function getProductIds();

	/**
	 * Not necessary, but even faster to use it
	 * @param $aIds till now only possible ids
	 * @return $this
	 */
	abstract public function setCurrentIds($aIds);
}
