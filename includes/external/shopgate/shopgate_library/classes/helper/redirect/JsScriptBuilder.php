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
 * of paragraph 69 d, sub-paragraphs 2, 3 and paragraph 69, sub-paragraph e of the German Copyright Act shall remain
 * unaffected.
 *
 * @author Shopgate GmbH <interfaces@shopgate.com>
 */
class Shopgate_Helper_Redirect_JsScriptBuilder extends ShopgateObject
	implements Shopgate_Helper_Redirect_JsScriptBuilderInterface
{
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
	protected $suppressRedirectJavascript = false;

	/** @var array [string, mixed] Parameters that should be replaced in the HTML tags, indexed by their name. */
	protected $siteParameters = array();

	/** @var array [string, string] An array with the page names as indices and the "old" JS redirect types as values, if different. */
	protected $pageTypeToRedirectMapping = array(
		Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_HOME    => 'start',
		Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_PRODUCT => 'item',
	);

	/**
	 * @param Shopgate_Helper_Redirect_TagsGeneratorInterface   $tagsGenerator
	 * @param Shopgate_Helper_Redirect_SettingsManagerInterface $settingsManager
	 * @param Shopgate_Helper_Redirect_TemplateParserInterface  $templateParser
	 * @param string                                            $jsTemplateFilePath
	 * @param string                                            $shopNumber
	 */
	public function __construct(
		Shopgate_Helper_Redirect_TagsGeneratorInterface $tagsGenerator,
		Shopgate_Helper_Redirect_SettingsManagerInterface $settingsManager,
		Shopgate_Helper_Redirect_TemplateParserInterface $templateParser,
		$jsTemplateFilePath,
		$shopNumber
	) {
		$this->tagsGenerator      = $tagsGenerator;
		$this->settingsManager    = $settingsManager;
		$this->templateParser     = $templateParser;
		$this->jsTemplateFilePath = $jsTemplateFilePath;
		$this->shopNumber         = $shopNumber;

		try {
			$htmlTags = $this->settingsManager->getHtmlTags();
			$this->tagsGenerator->setHtmlTagsFromJson($htmlTags);
		} catch (ShopgateLibraryException $e) {
			$this->tagsGenerator->setHtmlTagsFromJson($this->getFallBackTags());
		}
	}

	/**
	 * @param string $pageType
	 * @param array  $parameters [string, string]
	 *
	 * @return string
	 */
	public function buildTags(
		$pageType,
		$parameters = array()
	) {
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
	 * Sets the file path of javascript template
	 * to use
	 *
	 * @param string $filePath
	 *
	 * @return Shopgate_Helper_Redirect_JsScriptBuilder
	 */
	public function setJsTemplateFilePath($filePath)
	{
		$this->jsTemplateFilePath = $filePath;

		return $this;
	}

	/**
	 * Helps set all parameters at once
	 *
	 * @param array $params - array(key => value)
	 *
	 * @return Shopgate_Helper_Redirect_JsScriptBuilder
	 */
	public function setSiteParameters($params)
	{
		foreach ($params as $key => $param) {
			$this->setSiteParameter($key, $param);
		}

		return $this;
	}

	/**
	 * @param string $key
	 * @param string $value
	 *
	 * @return Shopgate_Helper_Redirect_JsScriptBuilder
	 */
	public function setSiteParameter($key, $value)
	{
		$this->siteParameters[$key] = $value;

		return $this;
	}

	/**
	 * @param string $file
	 *
	 * @return Shopgate_Helper_Redirect_JsScriptBuilder
	 */
	public function setTemplateFile($file)
	{
		$this->jsTemplateFilePath = $file;

		return $this;
	}

	/**
	 * Prints a value to JS script to prevent
	 * web app redirect
	 *
	 * @param bool $param
	 *
	 * @return Shopgate_Helper_Redirect_JsScriptBuilder
	 */
	public function suppressWebAppRedirect($param)
	{
		$this->suppressRedirectJavascript = $param;

		return $this;
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
	 * @return bool
	 */
	protected function getRedirectCode($pageType)
	{
		return isset($this->pageTypeToRedirectMapping[$pageType])
			? $this->pageTypeToRedirectMapping[$pageType]
			: $pageType;
	}
}
