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
class Shopgate_Helper_Redirect_Redirector implements Shopgate_Helper_Redirect_RedirectorInterface
{
    /** @var Shopgate_Helper_Redirect_SettingsManagerInterface */
    protected $settingsManager;

    /** @var Shopgate_Helper_Redirect_KeywordsManagerInterface */
    protected $keywordManager;

    /** @var Shopgate_Helper_Redirect_LinkBuilderInterface */
    protected $linkBuilder;

    /** @var string */
    protected $userAgent;

    /**
     * @param Shopgate_Helper_Redirect_SettingsManagerInterface $settingsManager
     * @param Shopgate_Helper_Redirect_KeywordsManagerInterface $keywordManager
     * @param Shopgate_Helper_Redirect_LinkBuilderInterface     $linkBuilder
     * @param string                                            $userAgent
     */
    public function __construct(
        Shopgate_Helper_Redirect_SettingsManagerInterface $settingsManager,
        Shopgate_Helper_Redirect_KeywordsManagerInterface $keywordManager,
        Shopgate_Helper_Redirect_LinkBuilderInterface $linkBuilder,
        $userAgent = ''
    ) {
        $this->settingsManager = $settingsManager;
        $this->keywordManager  = $keywordManager;
        $this->linkBuilder     = $linkBuilder;
        $this->userAgent       = $userAgent;
    }

    public function redirectDefault()
    {
        if ($this->settingsManager->isDefaultRedirectDisabled()) {
            return;
        }

        // don't send the "Vary" HTTP header because this doesn't redirect to a different version, it's just a fall back
        $this->redirect($this->linkBuilder->buildDefault(), false);
    }

    public function redirectHome()
    {
        $this->redirect($this->linkBuilder->buildHome());
    }

    public function redirectCategory($uid)
    {
        $this->redirect($this->linkBuilder->buildCategory($uid));
    }

    public function redirectProduct($uid)
    {
        $this->redirect($this->linkBuilder->buildProduct($uid));
    }

    public function redirectCms($pageUid)
    {
        $this->redirect($this->linkBuilder->buildCms($pageUid));
    }

    public function redirectBrand($brandName)
    {
        $this->redirect($this->linkBuilder->buildBrand($brandName));
    }

    public function redirectSearch($searchString)
    {
        $this->redirect($this->linkBuilder->buildSearch($searchString));
    }

    public function redirect($url, $sendVary = true)
    {
        if ($this->settingsManager->isRedirectDisabled()) {
            $this->settingsManager->setCookie();

            return;
        }

        if (!$this->isMobile()) {
            return;
        }

        if ($sendVary) {
            header('Vary: User-Agent');
        }

        header("Location: " . $url, true, 301);
        exit;
    }

    public function isMobile()
    {
        return (bool)preg_match($this->keywordManager->toRegEx(), $this->userAgent);
    }
}
