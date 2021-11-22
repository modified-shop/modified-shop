<?php

/**
 * Copyright Shopgate Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    Shopgate Inc, 804 Congress Ave, Austin, Texas 78701 <interfaces@shopgate.com>
 * @copyright Shopgate Inc
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */
class Shopgate_Helper_Redirect_TagsGenerator extends ShopgateObject implements Shopgate_Helper_Redirect_TagsGeneratorInterface
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
