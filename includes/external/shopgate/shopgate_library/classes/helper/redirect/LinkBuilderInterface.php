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
interface Shopgate_Helper_Redirect_LinkBuilderInterface
{
	const LINK_TYPE_DEFAULT  = 'default';
	const LINK_TYPE_HOME     = 'home';
	const LINK_TYPE_PRODUCT  = 'product';
	const LINK_TYPE_CATEGORY = 'category';
	const LINK_TYPE_CMS      = 'cms';
	const LINK_TYPE_BRAND    = 'brand';
	const LINK_TYPE_SEARCH   = 'search';
	
	/**
	 * @param Shopgate_Model_Redirect_DeeplinkSuffix[] $parameters
	 *
	 * @return string
	 */
	public function buildDefault(array $parameters = array());
	
	/**
	 * @param Shopgate_Model_Redirect_DeeplinkSuffix[] $parameters
	 *
	 * @return string
	 */
	public function buildHome(array $parameters = array());
	
	/**
	 * @param string                                   $uid
	 * @param Shopgate_Model_Redirect_DeeplinkSuffix[] $parameters
	 *
	 * @return string
	 */
	public function buildProduct($uid, array $parameters = array());
	
	/**
	 * @param string                                   $uid
	 * @param Shopgate_Model_Redirect_DeeplinkSuffix[] $parameters
	 *
	 * @return string
	 */
	public function buildCategory($uid, array $parameters = array());
	
	/**
	 * @param string                                   $pageUid
	 * @param Shopgate_Model_Redirect_DeeplinkSuffix[] $parameters
	 *
	 * @return string
	 */
	public function buildCms($pageUid, array $parameters = array());
	
	/**
	 * @param string $brandName
	 *
	 * @return string
	 */
	public function buildBrand($brandName);
	
	/**
	 * @param string                                   $searchQuery
	 * @param Shopgate_Model_Redirect_DeeplinkSuffix[] $parameters
	 *
	 * @return string
	 */
	public function buildSearch($searchQuery, array $parameters = array());
	
	/**
	 * @param string                                    $pageType
	 * @param Shopgate_Model_Redirect_HtmlTagVariable[] $variables
	 * @param array                                     $parameters       [string, string]
	 * @param string                                    $overrideTemplate Set to null to not override the default template
	 *
	 * @return string
	 */
	public function getUrlFor($pageType, array $variables, array $parameters = array(), $overrideTemplate = null);
}