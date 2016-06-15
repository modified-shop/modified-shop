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
class Shopgate_Helper_Redirect_TagsGenerator
	extends ShopgateObject
	implements Shopgate_Helper_Redirect_TagsGeneratorInterface
{
	/** @var Shopgate_Helper_Redirect_LinkBuilderInterface */
	protected $linkBuilder;
	
	/** @var Shopgate_Helper_Redirect_TemplateParserInterface */
	protected $templateParser;
	
	/** @var Shopgate_Model_Redirect_HtmlTag[] */
	protected $htmlTags;
	
	/**
	 * @param Shopgate_Helper_Redirect_LinkBuilderInterface    $linkBuilder
	 * @param Shopgate_Helper_Redirect_TemplateParserInterface $templateParser
	 */
	public function __construct(
		Shopgate_Helper_Redirect_LinkBuilderInterface $linkBuilder,
		Shopgate_Helper_Redirect_TemplateParserInterface $templateParser
	) {
		$this->linkBuilder    = $linkBuilder;
		$this->templateParser = $templateParser;
		$this->htmlTags       = array();
	}
	
	public function setHtmlTags(array $htmlTags)
	{
		$this->htmlTags = $htmlTags;
	}
	
	public function setHtmlTagsFromJson($htmlTags)
	{
		$this->htmlTags = array();
		$htmlTags       = $this->jsonDecode($htmlTags, true);
		
		if (empty($htmlTags) || empty($htmlTags['html_tags'])) {
			return;
		}
		
		foreach ($htmlTags['html_tags'] as $tag) {
			if (empty($tag['name'])) {
				continue;
			}
			
			$this->htmlTags[] = $this->createHtmlTag($tag);
		}
	}
	
	public function getTagsFor($pageType, array $parameters = array())
	{
		$html = '';
		
		foreach ($this->htmlTags as $tag) {
			try {
				$attributes = $this->getAttributes($pageType, $tag->getAttributes(), $parameters);
			} catch (ShopgateLibraryException $e) {
				continue; // skip tags with missing/unset variables
			}
			
			$html .= "<{$tag->getName()}{$attributes} />\n";
		}
		
		return $html;
	}
	
	/**
	 * @param array $tag
	 *
	 * @return Shopgate_Model_Redirect_HtmlTag
	 */
	protected function createHtmlTag(array $tag)
	{
		$tagModel = new Shopgate_Model_Redirect_HtmlTag();
		$tagModel->setName($tag['name']);
		
		if (empty($tag['attributes'])) {
			return $tagModel;
		}
		
		$attributes = array();
		foreach ($tag['attributes'] as $attribute) {
			if (empty($attribute['name'])) {
				continue;
			}
			
			$attributes[] = $this->createHtmlTagAttribute($attribute);
		}
		
		$tagModel->setAttributes($attributes);
		
		return $tagModel;
	}
	
	/**
	 * @param array $attribute
	 *
	 * @return Shopgate_Model_Redirect_HtmlTagAttribute
	 */
	protected function createHtmlTagAttribute(array $attribute)
	{
		$attributeModel = new Shopgate_Model_Redirect_HtmlTagAttribute();
		$attributeModel->setName($attribute['name']);
		$attributeModel->setValue($attribute['value']);
		$attributeModel->setVariables($this->templateParser->getVariables($attribute['value']));
		
		if (!empty($attribute['deeplink_suffix'])) {
			$attributeModel->setDeeplinkSuffix($this->createDeeplinkSuffixes($attribute['deeplink_suffix']));
		}
		
		return $attributeModel;
	}
	
	/**
	 * @param array $deeplinkSuffix
	 *
	 * @return Shopgate_Model_Redirect_DeeplinkSuffix
	 */
	protected function createDeeplinkSuffixes(array $deeplinkSuffix)
	{
		$suffixModel = new Shopgate_Model_Redirect_DeeplinkSuffix();
		
		foreach ($deeplinkSuffix as $name => $template) {
			$valueModel = new Shopgate_Model_Redirect_DeeplinkSuffixValue();
			$valueModel->setName($name);
			$valueModel->setValue($template);
			
			if ($template === false) {
				$valueModel->setDisabled(true);
			} else {
				$valueModel->setVariables($this->templateParser->getVariables($template));
			}
			
			$suffixModel->addValue($name, $valueModel);
		}
		
		return $suffixModel;
	}
	
	/**
	 * @param string                                     $pageType
	 * @param Shopgate_Model_Redirect_HtmlTagAttribute[] $attributes
	 * @param array                                      $parameters [string, string]
	 *
	 * @return string
	 * @throws ShopgateLibraryException in case a variable is in the template but not set in the parameters.
	 */
	protected function getAttributes($pageType, $attributes, array $parameters = array())
	{
		$attributesString = '';
		foreach ($attributes as $attribute) {
			if ($attribute->getDeeplinkSuffix()) {
				$parameters['deeplink_suffix'] = $this->getDeeplinkSuffix(
					$pageType,
					$attribute->getDeeplinkSuffix(),
					$parameters
				);
			}
			
			$attributesString .= ' ' . $attribute->getName() . '="' .
				$this->getVariables(
					$attribute->getVariables(),
					$attribute->getValue(),
					$parameters
				) . '"';
		}
		
		return $attributesString;
	}
	
	/**
	 * @param Shopgate_Model_Redirect_HtmlTagVariable[] $variables
	 * @param string                                    $template
	 * @param array                                     $parameters [string, string]
	 *
	 * @return string
	 * @throws ShopgateLibraryException in case a variable is in the template but not set in the parameters.
	 */
	protected function getVariables($variables, $template, array $parameters = array())
	{
		foreach ($variables as $variable) {
			if (!isset($parameters[$variable->getName()])) {
				// don't log, this is caught internally
				throw new ShopgateLibraryException(ShopgateLibraryException::CONFIG_INVALID_VALUE, '', false, false);
			}
			
			$parameter = !isset($parameters[$variable->getName()])
				? ''
				: $parameters[$variable->getName()];
			
			$template = $this->templateParser->process(
				$template,
				$variable,
				$parameter
			);
		}
		
		return $template;
	}
	
	/**
	 * @param string                                 $pageType
	 * @param Shopgate_Model_Redirect_DeeplinkSuffix $deeplinkSuffix
	 * @param array                                  $parameters [string, string]
	 *
	 * @return string
	 * @throws ShopgateLibraryException
	 */
	protected function getDeeplinkSuffix(
		$pageType,
		Shopgate_Model_Redirect_DeeplinkSuffix $deeplinkSuffix,
		array $parameters = array()
	) {
		$value = $deeplinkSuffix->getValue($pageType);
		
		if ($value->getUnset()) {
			$value = $deeplinkSuffix->getValue(Shopgate_Helper_Redirect_TagsGeneratorInterface::PAGE_TYPE_DEFAULT);
		}
		
		if ($value->getDisabled()) {
			throw new ShopgateLibraryException(ShopgateLibraryException::CONFIG_INVALID_VALUE, '', false, false);
		}
		
		return $this->linkBuilder->getUrlFor($pageType, $value->getVariables(), $parameters, $value->getValue());
	}
}