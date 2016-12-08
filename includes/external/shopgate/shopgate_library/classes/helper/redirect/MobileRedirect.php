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
 * @deprecated 3.0.0 - deprecated as of 2.9.51
 */
class Shopgate_Helper_Redirect_MobileRedirect
	extends ShopgateObject
	implements Shopgate_Helper_Redirect_MobileRedirectInterface
{
	/** @var Shopgate_Helper_Redirect_RedirectorInterface */
	protected $redirector;

	/** @var Shopgate_Helper_Redirect_TagsGeneratorInterface */
	protected $tagsGenerator;

	/** @var Shopgate_Helper_Redirect_SettingsManagerInterface */
	protected $settingsManager;

	/** @var Shopgate_Helper_Redirect_TemplateParserInterface */
	protected $templateParser;

	/** @var string */
	protected $jsTemplateFilePath;

	/** @var string */
	protected $shopNumber;

	/** @var bool */
	protected $suppressRedirectHttp;

	/** @var bool */
	protected $suppressRedirectJavascript;

	/**
	 * @var array [string, mixed] Parameters that should be replaced in the HTML tags, indexed by their name.
	 */
	protected $siteParameters;

	/**
	 * @var array [string, string] An array with the page names as indices and the "old" JS redirect types as values, if different.
	 */
	protected $pageTypeToRedirectMapping;

	/**
	 * ShopgateMobileRedirect constructor.
	 *
	 * @param Shopgate_Helper_Redirect_RedirectorInterface      $redirector
	 * @param Shopgate_Helper_Redirect_TagsGeneratorInterface   $tagsGenerator
	 * @param Shopgate_Helper_Redirect_SettingsManagerInterface $settingsManager
	 * @param Shopgate_Helper_Redirect_TemplateParserInterface  $templateParser
	 * @param string                                            $jsTemplateFilePath
	 * @param string                                            $shopNumber
	 */
	public function __construct(
		Shopgate_Helper_Redirect_RedirectorInterface $redirector,
		Shopgate_Helper_Redirect_TagsGeneratorInterface $tagsGenerator,
		Shopgate_Helper_Redirect_SettingsManagerInterface $settingsManager,
		Shopgate_Helper_Redirect_TemplateParserInterface $templateParser,
		$jsTemplateFilePath,
		$shopNumber
	) {
		$this->redirector         = $redirector;
		$this->tagsGenerator      = $tagsGenerator;
		$this->settingsManager    = $settingsManager;
		$this->templateParser     = $templateParser;
		$this->jsTemplateFilePath = $jsTemplateFilePath;
		$this->shopNumber         = $shopNumber;

		$this->suppressRedirectHttp       = false;
		$this->suppressRedirectJavascript = false;
		$this->siteParameters             = array();

		$this->pageTypeToRedirectMapping = array(
			Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_HOME    => 'start',
			Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_PRODUCT => 'item',
		);

		try {
			$htmlTags = $this->settingsManager->getHtmlTags();
			$this->tagsGenerator->setHtmlTagsFromJson($htmlTags);
		} catch (ShopgateLibraryException $e) {
			$this->tagsGenerator->setHtmlTagsFromJson($this->getFallBackTags());
		}
	}

	/**
	 * Suppresses the redirect via JavaScript without disabling the mobile header.
	 *
	 * @deprecated Use supressRedirectTechniques() instead.
	 */
	public function suppressRedirect()
	{
		$this->suppressRedirectJavascript = true;
	}

	public function supressRedirectTechniques($http = false, $javascript = false)
	{
		$this->suppressRedirectHttp       = $http;
		$this->suppressRedirectJavascript = $javascript;
	}

	public function addSiteParameter($name, $value)
	{
		$this->siteParameters[$name] = $value;
	}

	public function redirect($url, $sendVary = true)
	{
		if ($this->suppressRedirectHttp) {
			return;
		}

		$this->redirector->redirect($url, $sendVary);
	}

	public function buildScriptDefault()
	{
		$this->redirector->redirectDefault();

		return $this->buildTags(Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_DEFAULT);
	}

	public function buildScriptShop()
	{
		$this->redirector->redirectHome();

		return $this->buildTags(Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_HOME);
	}

	public function buildScriptItem($itemNumber)
	{
		$this->redirector->redirectProduct($itemNumber);

		return $this->buildTags(
			Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_PRODUCT,
			array('product_uid' => $itemNumber)
		);
	}

	public function buildScriptItemPublic($itemNumberPublic)
	{
		return $this->buildScriptItem($itemNumberPublic);
	}

	public function buildScriptCategory($categoryNumber)
	{
		$this->redirector->redirectCategory($categoryNumber);

		return $this->buildTags(
			Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_CATEGORY,
			array('category_uid' => $categoryNumber)
		);
	}

	public function buildScriptCms($cmsPage)
	{
		$this->redirector->redirectCms($cmsPage);

		return $this->buildTags(
			Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_CMS,
			array('page_uid' => $cmsPage)
		);
	}

	public function buildScriptBrand($manufacturerName)
	{
		$this->redirector->redirectBrand($manufacturerName);

		return $this->buildTags(
			Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_BRAND,
			array('brand_name' => $manufacturerName)
		);
	}

	public function buildScriptSearch($searchQuery)
	{
		$this->redirector->redirectSearch($searchQuery);

		return $this->buildTags(
			Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_SEARCH,
			array('search_query' => $searchQuery)
		);
	}

	/**
	 * @param string $pageType
	 * @param array  $parameters [string, string]
	 *
	 * @return string
	 */
	protected function buildTags($pageType, $parameters = array())
	{
		if ($this->settingsManager->isMobileHeaderDisabled()) {
			return '';
		}

		$parameters = $this->siteParameters + $parameters;

		$parameters['link_tags']     = $this->tagsGenerator->getTagsFor($pageType, $parameters);
		$parameters['redirect_code'] = $this->getRedirectCode($pageType);
		$parameters['shop_number']   = $this->shopNumber;
		$parameters += $this->settingsManager->getShopgateStaticUrl();

		// pre-process the additional parameters to add in the correct JS variables
		$variable = new Shopgate_Model_Redirect_HtmlTagVariable();
		$variable->setName('additional_parameters');
		$jsTemplate = $this->templateParser->process(
			@file_get_contents($this->jsTemplateFilePath),
			$variable,
			$this->getAdditionalParameters($pageType)
		);

		// process all variables left in the template (and those added with the processing of 'additional_parameters'.
		$variables = $this->templateParser->getVariables($jsTemplate);
		foreach ($variables as $variable) {
			$replacement = !empty($parameters[$variable->getName()])
				? $parameters[$variable->getName()]
				: '';

			$jsTemplate = $this->templateParser->process($jsTemplate, $variable, $replacement);
		}

		return $jsTemplate;
	}

	/**
	 * @return string
	 */
	protected function getFallBackTags()
	{
		return $this->jsonEncode(
			array(
				'html_tags' => array(
					array(
						'name'       => 'link',
						'attributes' => array(
							array(
								'name'  => 'rel',
								'value' => 'alternate',
							),
							array(
								'name'  => 'media',
								'value' => 'only screen and (max-width: 640px)',
							),
							array(
								'name'            => 'href',
								'value'           => '{deeplink_suffix}',
								'deeplink_suffix' => $this->settingsManager->getDefaultTemplatesByPageType(),
							),
						),
					),
				),
			)
		);
	}

	/**
	 * @param string $pageType
	 *
	 * @return string
	 */
	protected function getAdditionalParameters($pageType)
	{
		$additionalParameters = '';

		$defaultRedirect = $this->settingsManager->isDefaultRedirectDisabled() ? 'false' : 'true';
		$jsRedirect      = $this->suppressRedirectJavascript ? 'false' : 'true';

		$additionalParameters .= "_shopgate.is_default_redirect_disabled = {$defaultRedirect};\n";
		$additionalParameters .= "    _shopgate.redirect_to_webapp = {$jsRedirect};\n";

		switch ($pageType) {
			case Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_CATEGORY:
				$additionalParameters .= '_shopgate.category_number = "{category_uid}";';
				break;

			case Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_PRODUCT:
				$additionalParameters .= '_shopgate.item_number = "{product_uid}";';
				break;

			case Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_CMS:
				$additionalParameters .= '_shopgate.cms_page = "{page_uid}";';
				break;

			case Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_BRAND:
				$additionalParameters .= '_shopgate.brand_name = "{brand_name}";';
				break;

			case Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_SEARCH:
				$additionalParameters .= '_shopgate.search_query = "{search_query:escaped}";';
				break;
		}

		return $additionalParameters . "\n";
	}

	/**
	 * @param string $pageType
	 *
	 * @return bool
	 */
	protected function getRedirectCode($pageType)
	{
		return isset($this->pageTypeToRedirectMapping[$pageType])
			? $this->pageTypeToRedirectMapping[$pageType]
			: $pageType;
	}
}
