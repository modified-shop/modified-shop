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
