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
class Shopgate_Helper_Redirect_MobileRedirect extends ShopgateObject implements Shopgate_Helper_Redirect_MobileRedirectInterface
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
     * @var array [string, string] An array with the page names as indices and the "old" JS redirect types as values,
     *      if different.
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
        $parameters                  += $this->settingsManager->getShopgateStaticUrl();

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

        $defaultRedirect = $this->settingsManager->isDefaultRedirectDisabled()
            ? 'false'
            : 'true';
        $jsRedirect      = $this->suppressRedirectJavascript
            ? 'false'
            : 'true';

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
