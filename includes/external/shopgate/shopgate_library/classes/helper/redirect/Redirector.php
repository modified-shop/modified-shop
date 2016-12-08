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
		return (bool) preg_match($this->keywordManager->toRegEx(), $this->userAgent);
	}
}