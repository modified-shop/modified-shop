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
interface Shopgate_Helper_Redirect_SettingsManagerInterface
{
	const DISABLE_REDIRECT_COOKIE_EXPIRATION_1_WEEK = 604800;
	
	const DEFAULT_DISABLE_REDIRECT_GET_PARAMETER_NAME = 'shopgate_redirect';
	const DEFAULT_DISABLE_REDIRECT_COOKIE_NAME        = 'SHOPGATE_MOBILE_WEBPAGE';
	
	const SHOPGATE_STATIC     = '//static.shopgate.com';
	const SHOPGATE_STATIC_SSL = self::SHOPGATE_STATIC;
	
	const SHOPGATE_PG_STATIC     = '//static.shopgatepg.com';
	const SHOPGATE_PG_STATIC_SSL = self::SHOPGATE_PG_STATIC;
	
	const SHOPGATE_SL_STATIC     = '//static.shopgatesl.com';
	const SHOPGATE_SL_STATIC_SSL = self::SHOPGATE_SL_STATIC;
	
	const SHOPGATE_DEV_STATIC     = '//shopgatedev-public.s3.amazonaws.com';
	const SHOPGATE_DEV_STATIC_SSL = self::SHOPGATE_DEV_STATIC;
	
	const SHOPGATE_LIVE_ALIAS = '.shopgate.com';
	const SHOPGATE_SL_ALIAS   = '.shopgatesl.com';
	const SHOPGATE_PG_ALIAS   = '.shopgatepg.com';
	const SHOPGATE_DEV_ALIAS  = '.localdev.cc/php/shopgate/index.php';
	
	/**
	 * @return bool
	 */
	public function isDefaultRedirectDisabled();
	
	/**
	 * @return bool
	 */
	public function isRedirectDisabled();
	
	/**
	 * @return bool
	 */
	public function isMobileHeaderDisabled();
	
	/**
	 * Sends the cookie to disable the mobile redirect is sent to the requesting entity.
	 *
	 * @param int $startTime Pass null to use time().
	 */
	public function setCookie($startTime = null);
	
	/**
	 * Generates the root mobile Url for the redirect without trailing slashes.
	 */
	public function getMobileUrl();
	
	/**
	 * Filters the GET parameters that should be passed in the redirect and builds the new query string.
	 *
	 * @return string
	 */
	public function getRedirectableGetParameters();
	
	/**
	 * @return string
	 * @throws ShopgateLibraryException in case the configuration value is null.
	 */
	public function getHtmlTags();
	
	/**
	 * @return array [string, string]
	 */
	public function getDefaultTemplatesByPageType();
	
	/**
	 * @return array [string, string] An array with indices 'ssl_url' and 'non_ssl_url' and the corresponding URLs.
	 */
	public function getShopgateStaticUrl();
}