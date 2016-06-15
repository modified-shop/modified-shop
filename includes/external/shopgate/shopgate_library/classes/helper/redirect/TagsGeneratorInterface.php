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
interface Shopgate_Helper_Redirect_TagsGeneratorInterface
{
	const PAGE_TYPE_DEFAULT  = Shopgate_Helper_Redirect_LinkBuilderInterface::LINK_TYPE_DEFAULT;
	const PAGE_TYPE_HOME     = Shopgate_Helper_Redirect_LinkBuilderInterface::LINK_TYPE_HOME;
	const PAGE_TYPE_PRODUCT  = Shopgate_Helper_Redirect_LinkBuilderInterface::LINK_TYPE_PRODUCT;
	const PAGE_TYPE_CATEGORY = Shopgate_Helper_Redirect_LinkBuilderInterface::LINK_TYPE_CATEGORY;
	const PAGE_TYPE_CMS      = Shopgate_Helper_Redirect_LinkBuilderInterface::LINK_TYPE_CMS;
	const PAGE_TYPE_BRAND    = Shopgate_Helper_Redirect_LinkBuilderInterface::LINK_TYPE_BRAND;
	const PAGE_TYPE_SEARCH   = Shopgate_Helper_Redirect_LinkBuilderInterface::LINK_TYPE_SEARCH;
	
	const SITE_PARAMETER_SITENAME                  = 'sitename';
	const SITE_PARAMETER_DESKTOP_URL               = 'desktop_url';
	const SITE_PARAMETER_MOBILE_WEB_URL            = 'mobile_web_url';
	const SITE_PARAMETER_TITLE                     = 'title';
	const SITE_PARAMETER_PRODUCT_IMAGE             = 'product_image';
	const SITE_PARAMETER_PRODUCT_NAME              = 'product_name';
	const SITE_PARAMETER_PRODUCT_DESCRIPTION_SHORT = 'product_description_short';
	const SITE_PARAMETER_PRODUCT_EAN               = 'product_ean';
	const SITE_PARAMETER_PRODUCT_AVAILABILITY      = 'product_availability';
	const SITE_PARAMETER_PRODUCT_CATEGORY          = 'product_category';
	const SITE_PARAMETER_PRODUCT_PRICE             = 'product_price';
	const SITE_PARAMETER_PRODUCT_CURRENCY          = 'product_currency';
	const SITE_PARAMETER_PRODUCT_PRETAX_PRICE      = 'product_pretax_price';
	const SITE_PARAMETER_PRODUCT_PRETAX_CURRENCY   = 'product_pretax_currency';
	
	/**
	 * @param Shopgate_Model_Redirect_HtmlTag[] $htmlTags
	 */
	public function setHtmlTags(array $htmlTags);
	
	/**
	 * @param string $htmlTags A JSON encoded string containing the HTML tags.
	 */
	public function setHtmlTagsFromJson($htmlTags);
	
	/**
	 * @param string $pageType
	 * @param array  $parameters [string, string]
	 *
	 * @return string
	 */
	public function getTagsFor($pageType, array $parameters = array());
}