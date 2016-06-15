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
class Shopgate_Helper_Redirect_LinkBuilder implements Shopgate_Helper_Redirect_LinkBuilderInterface
{
	/** @var string[] [string, string] A list of templates indexed by their page type. */
	protected $defaultTemplatesByPageType;
	
	/** @var Shopgate_Helper_Redirect_SettingsManagerInterface */
	protected $settingsManager;
	
	/** @var Shopgate_Helper_Redirect_TemplateParserInterface */
	protected $templateParser;
	
	/**
	 * @param Shopgate_Helper_Redirect_SettingsManagerInterface $settingsManager
	 * @param Shopgate_Helper_Redirect_TemplateParserInterface  $templateParser
	 */
	public function __construct(
		Shopgate_Helper_Redirect_SettingsManagerInterface $settingsManager,
		Shopgate_Helper_Redirect_TemplateParserInterface $templateParser
	) {
		$this->settingsManager = $settingsManager;
		$this->templateParser  = $templateParser;
		
		// default templates
		$this->defaultTemplatesByPageType = $this->settingsManager->getDefaultTemplatesByPageType();
	}
	
	public function buildDefault(array $parameters = array())
	{
		return $this->buildScriptFor(self::LINK_TYPE_DEFAULT, '', '');
	}
	
	public function buildHome(array $parameters = array())
	{
		return $this->buildScriptFor(self::LINK_TYPE_HOME, '', '');
	}
	
	public function buildProduct($uid, array $parameters = array())
	{
		return $this->buildScriptFor(self::LINK_TYPE_PRODUCT, 'product_uid', $uid);
	}
	
	public function buildCategory($uid, array $parameters = array())
	{
		return $this->buildScriptFor(self::LINK_TYPE_CATEGORY, 'category_uid', $uid);
	}
	
	public function buildCms($pageUid, array $parameters = array())
	{
		return $this->buildScriptFor(self::LINK_TYPE_CMS, 'page_uid', $pageUid);
	}
	
	public function buildBrand($brandName)
	{
		return $this->buildScriptFor(self::LINK_TYPE_BRAND, 'brand_name', $brandName);
	}
	
	public function buildSearch($searchQuery, array $parameters = array())
	{
		return $this->buildScriptFor(self::LINK_TYPE_SEARCH, 'search_query', $searchQuery);
	}
	
	public function getUrlFor($pageType, array $variables, array $parameters = array(), $overrideTemplate = null)
	{
		/** @var Shopgate_Model_Redirect_HtmlTagVariable[] $variables */
		
		$template = empty($this->defaultTemplatesByPageType[$pageType])
			? ''
			: $this->defaultTemplatesByPageType[$pageType];
		
		if ($overrideTemplate !== null) {
			$template = $overrideTemplate;
		}
		
		if (strstr($template, '{baseUrl}') !== false) {
			$parameters['baseUrl'] = $this->settingsManager->getMobileUrl();
		}
		
		foreach ($variables as $variable) {
			if (!isset($parameters[$variable->getName()])) {
				return '';
			}
			
			$parameter = !isset($parameters[$variable->getName()])
				? ''
				: $parameters[$variable->getName()];
			
			$template = $this->templateParser->process($template, $variable, $parameter);
		}
		
		return $template;
	}
	
	/**
	 * @param string $url
	 *
	 * @return string
	 */
	protected function appendRedirectableGetParameters($url)
	{
		$concat = (parse_url($url, PHP_URL_QUERY) === null)
			? '?'
			: '&';
		
		return
			$url . (
			$this->settingsManager->getRedirectableGetParameters()
				? $concat . $this->settingsManager->getRedirectableGetParameters()
				: ''
			);
	}
	
	/**
	 * @param string $pageType
	 * @param string $variableName
	 * @param string $variableValue
	 *
	 * @return string
	 */
	protected function buildScriptFor($pageType, $variableName, $variableValue)
	{
		$variables = $this->templateParser->getVariables($this->defaultTemplatesByPageType[$pageType]);
		
		return $this->appendRedirectableGetParameters(
			$this->getUrlFor(
				$pageType,
				$variables,
				array($variableName => $variableValue)
			)
		);
	}
}