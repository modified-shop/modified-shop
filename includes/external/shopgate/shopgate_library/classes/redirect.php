<?php
/**
 * Helper class for redirection from shop system to mobile webpage.
 *
 * Provides analyzation of the client's user agent, creation of redirection links for
 * different redirects (e.g. product, category, search), keyword updating and caching,
 * javascript for the "on/off" switch and sending the redirect headers to the client's
 * browser.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 *
 */
interface ShopgateMobileRedirectInterface {
	const SHOPGATE_STATIC = 'http://static.shopgate.com';
	const SHOPGATE_STATIC_SSL = 'https://static-ssl.shopgate.com';
	
	/**
	 * @var string the URL that is appended to the end of a shop alias (aka subdomain) if the shop is live
	 */
	const SHOPGATE_LIVE_ALIAS = '.shopgate.com';

	/**
	 * @var string the URL that is appended to the end of a shop alias (aka subdomain) if the shop is on playground
	 */
	const SHOPGATE_PG_ALIAS = '.shopgatepg.com';

	/**
	 * @var string name of the cookie to set in case a customer turns of mobile redirect
	 */
	const COOKIE_NAME = 'SHOPGATE_MOBILE_WEBPAGE';

	/**
	 * @var int (hours) the minimum time that can be set for updating of the cache
	 */
	const MIN_CACHE_TIME = 1;

	/**
	 * @var int (hours) the default time to be set for updating the cache
	 */
	const DEFAULT_CACHE_TIME = 24;
	
	/**
	 * Sets the description to be displayed to the left of the button.
	 *
	 * @param string $description
	 */
	public function setButtonDescription($description);
	
	/**
	 * Sets the alias of the Shopgate shop
	 *
	 * @param string $alias
	 */
	public function setAlias($alias);
	
	/**
	 * Sets the cname of the shop
	 */
	public function setCustomMobileUrl($cname);
	
	/**
	 * Enables updating of the keywords that identify mobile devices from Shopgate Merchant API.
	 *
	 * @param int $cacheTime Time the keywords are cached in hours. Will be set to at least ShopgateMobileRedirectInterface::MIN_CACHE_TIME.
	 */
	public function enableKeywordUpdate($cacheTime = ShopgateMobileRedirectInterface::DEFAULT_CACHE_TIME);
	
	/**
	 * Disables updating of the keywords that identify mobile devices from Shopgate Merchant API.
	 */
	public function disableKeywordUpdate();
	
	/**
	 * Appends a new keyword to the redirect keywords list.
	 *
	 * @param string $keyword The redirect keyword to append.
	 */
	public function addRedirectKeyword($keyword);
	
	/**
	 * Removes a keyword or an array of redirect keywords from the keywords list.
	 *
	 * @param string|string[] $keyword The redirect keyword or keywords to remove.
	 */
	public function removeRedirectKeyword($keyword);
	
	/**
	 * Replaces the current list of redirect keywords with a given list.
	 *
	 * @param string[] $redirectKeywords The new list of redirect keywords.
	 */
	public function setRedirectKeywords(array $redirectKeywords);
	
	/**
	 * Replaces the current list of skiüp redirect keywords with a given list.
	 *
	 * @param string[] $skipRedirectKeywords The new list of skip redirect keywords.
	 */
	public function setSkipRedirectKeywords(array $skipRedirectKeywords);
	
	/**
	 * Switches to secure connection instead of checking server-side.
	 *
	 * This will cause slower download of nonsensitive material (the mobile header button images) from Shopgate.
	 * Activate only if the secure connection is determined incorrectly (e.g. because of third-party components).
	 */
	public function setAlwaysUseSSL();
	
	/**
	 * Detects by redirect keywords (and skip redirect keywords) if a request was sent by a mobile device.
	 *
	 * @return bool true if a mobile device could be detected, false otherwise.
	 */
	public function isMobileRequest();
	
	/**
	 * Detects whether the customer wants to be redirected.
	 *
	 * @return bool true if the customer wants to be redirected, false otherwise.
	 */
	public function isRedirectAllowed();
	
	/**
	 * Redirects to a given (valid) URL.
	 *
	 * If the $url parameter is no valid URL the method will simply return false and do nothing else.
	 * Otherwise it will output the necessary redirection headers and stop script execution.
	 *
	 * @param string $url the URL to redirect to
	 * @param bool $setCookie true to set the redirection cookie and activate redirection
	 * @return false if the passed $url parameter is no valid URL
	 */
	public function redirect($url);
	
	/**
	 * Returns the javascript and HTML for the mobile redirect button
	 *
	 * @return string
	 */
	public function getMobileHeader();

	/**
	 * Create a mobile-shop-url to the startmenu
	 */
	public function getShopUrl();

	/**
	 * Create a mobile-product-url to a item
	 *
	 * @param string $itemNumber
	 */
	public function getItemUrl($itemNumber);

	/**
	 * Create a mobile-category-url to a category
	 *
	 * @param string $categoryNumber
	 */
	public function getCategoryUrl($categoryNumber);

	/**
	 * Create a mobile-cms-url to a cms-page
	 *
	 * @param string $key
	 */
	public function getCmsUrl($key);

	/**
	 * Create a mobile-brand-url to a page with results for a specific manufacturer
	 *
	 * @param string $manufacturer
	 */
	public function getBrandUrl($manufacturerName);

	/**
	 * Create a mobile-search-url to a page with search results
	 *
	 * @param string $searchString
	 */
	public function getSearchUrl($searchString);
}

class ShopgateMobileRedirect extends ShopgateObject implements ShopgateMobileRedirectInterface {
	/**
	 * @var string alias name of shop at Shopgate, e.g. 'yourshop' to redirect to 'https://yourshop.shopgate.com'
	 */
	protected $alias = '';

	/**
	 * @var string your shops cname entry to redirect to
	 */
	protected $cname = '';
	
	/**
	 * @var ShopgateMerchantApiInterface
	 */
	protected $merchantApi;
	
	/**
	 * @var string The server type (live | pg | custom) to use for redirection.
	 */
	protected $serverType;

	/**
	 * @var string[] list of strings that cause redirection if they occur in the client's user agent
	 */
	protected $redirectKeywords = array('iPhone', 'iPod', 'iPad', 'Android');

	/**
	 * @var string[] list of strings that deny redirection if they occur in the client's user agent; overrides $this->redirectKeywords
	 */
	protected $skipRedirectKeywords = array('Shopgate');

	/**
	 * @var string
	 */
	protected $cacheFileBlacklist;

	/**
	 * @var string
	 */
	protected $cacheFileWhitelist;

	/**
	 * @var bool
	 */
	protected $updateRedirectKeywords;

	/**
	 * @var int (hours)
	 */
	protected $redirectKeywordCacheTime;

	/**
	 * @var bool true in case the website is delivered via HTTPS (this will load the Shopgate javascript via HTTPS as well to avoid browser warnings)
	 */
	protected $useSecureConnection;

	/**
	 * @var string
	 */
	protected $mobileHeaderTemplatePath;

	/**
	 * @var string expiration date of the cookie as defined in http://www.ietf.org/rfc/rfc2109.txt
	 */
	protected $cookieLife;

	/**
	 * @var string url to the image for the "switched on" button
	 */
	protected $buttonOnImageSource;

	/**
	 * @var string url to the image for the "switched off" button
	 */
	protected $buttonOffImageSource;

	/**
	 * @var string description to be displayed to the left of the button
	 */
	protected $buttonDescription;

	/**
	 * Instantiates the Shopgate mobile redirector.
	 *
	 * @param string $cacheFileWhitelist The path to the cache file where redirect keywords are saved.
	 * @param string $cacheFileBlacklist The path to the cache file where skip redirect keywords are.
	 * @param string $serverType The server type (live | pg | custom) to use redirection.
	 * @param ShopgateMerchantApiInterface $merchantApi An instance of the ShopgateMerchantApi required for keyword updates or null.
	 */
	public function __construct($cacheFileWhitelist, $cacheFileBlacklist, $serverType, ShopgateMerchantApiInterface &$merchantApi = null) {
		$this->merchantApi = $merchantApi;
		$this->serverType = $serverType;
		
		$this->updateRedirectKeywords = false;
		$this->redirectKeywordCacheTime = ShopgateMobileRedirectInterface::DEFAULT_CACHE_TIME;
		$this->cacheFileWhitelist = $cacheFileWhitelist;
		$this->cacheFileBlacklist = $cacheFileBlacklist;
		
		$this->useSecureConnection = isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] === "on" || $_SERVER["HTTPS"] == "1");

		// mobile header options
		$this->mobileHeaderTemplatePath = dirname(__FILE__).'/../assets/mobile_header.html';
		$this->cookieLife = gmdate('D, d-M-Y H:i:s T', time());
		$this->buttonDescription = 'Mobile Webseite aktivieren';
	}


	####################
	# general settings #
	####################

	public function setButtonDescription($description) {
		if (!empty($description)) $this->buttonDescription = $description;
	}

	public function setAlias($alias){
		$this->alias = $alias;
	}

	public function setCustomMobileUrl($cname){
		$this->cname = $cname;
	}
	
	

	public function enableKeywordUpdate($cacheTime = ShopgateMobileRedirectInterface::DEFAULT_CACHE_TIME) {
		$this->updateRedirectKeywords = true;
		$this->redirectKeywordCacheTime = ($cacheTime >= ShopgateMobileRedirectInterface::MIN_CACHE_TIME) ? $cacheTime : ShopgateMobileRedirectInterface::MIN_CACHE_TIME;
	}

	public function disableKeywordUpdate() {
		$this->updateRedirectKeywords = false;
	}

	public function addRedirectKeyword($keyword){
		if(is_array($keyword)){
			$this->redirectKeywords = array_merge($this->redirectKeywords, $keyword);
		} else {
			$this->redirectKeywords[] = $keyword;
		}
	}

	public function removeRedirectKeyword($keyword){
		if(is_array($keyword)){
			foreach($keyword as $word){
				foreach($this->redirectKeywords as $key => $mobileKeyword){
					if(mb_strtolower($word) == mb_strtolower($mobileKeyword)){
						unset($this->redirectKeywords[$key]);
					}
				}
			}
		} else {
			foreach($this->redirectKeywords as $key => $mobileKeyword){
				if(mb_strtolower($keyword) == mb_strtolower($mobileKeyword)){
					unset($this->redirectKeywords[$key]);
				}
			}
		}
	}

	public function setRedirectKeywords(array $redirectKeywords){
		$this->redirectKeywords = $redirectKeywords;
	}

	public function setSkipRedirectKeywords(array $skipRedirectKeywords){
		$this->skipRedirectKeywords = $skipRedirectKeywords;
	}

	public function setAlwaysUseSSL() {
		$this->useSecureConnection = true;
	}

	public function isMobileRequest() {
		// try loading keywords
		$this->updateRedirectKeywords();
		
		// find user agent
		$userAgent = '';
		if(!empty($_SERVER['HTTP_USER_AGENT'])){
			$userAgent = $_SERVER['HTTP_USER_AGENT'];
		} else {
			return false;
		}
		
		// check user agent for redirection keywords and skip redirection keywords and return the result
		return
			(!empty($this->redirectKeywords)     ?  preg_match('/'.implode('|', $this->redirectKeywords).'/', $userAgent)     : false) &&
			(!empty($this->skipRedirectKeywords) ? !preg_match('/'.implode('|', $this->skipRedirectKeywords).'/', $userAgent) : true);
	}

	public function isRedirectAllowed() {
		// if GET parameter is set create cookie and do not redirect
		if (!empty($_GET['shopgate_redirect'])) {
			setcookie(ShopgateMobileRedirectInterface::COOKIE_NAME, 1, time() + 604800, '/'); // expires after 7 days
			return false;
		}


		return empty($_COOKIE[ShopgateMobileRedirectInterface::COOKIE_NAME]) ? true : false;
	}

	public function redirect($url) {
		// validate url
		if (!preg_match('#^(http|https)\://#', $url)) {
			return false;
		}

		// perform redirect
		header("Location: ". $url, true, 302);
		exit;
	}

	public function getMobileHeader() {
		if (!file_exists($this->mobileHeaderTemplatePath)) {
			return '';
		}

		$html = @file_get_contents($this->mobileHeaderTemplatePath);
		if (empty($html)) {
			return '';
		}

		// set parameters
		$this->buttonOnImageSource = (($this->useSecureConnection) ? ShopgateMobileRedirectInterface::SHOPGATE_STATIC_SSL : ShopgateMobileRedirectInterface::SHOPGATE_STATIC).'/api/mobile_header/button_on.png';
		$this->buttonOffImageSource = (($this->useSecureConnection) ? ShopgateMobileRedirectInterface::SHOPGATE_STATIC_SSL : ShopgateMobileRedirectInterface::SHOPGATE_STATIC).'/api/mobile_header/button_off.png';
		$html = str_replace('{$cookieName}', ShopgateMobileRedirectInterface::COOKIE_NAME, $html);
		$html = str_replace('{$buttonOnImageSource}',  $this->buttonOnImageSource,  $html);
		$html = str_replace('{$buttonOffImageSource}', $this->buttonOffImageSource, $html);
		$html = str_replace('{$buttonDescription}', $this->buttonDescription, $html);

		return $html;
	}


	###############
	### helpers ###
	###############

	/**
	 * Generates the root mobile Url for the redirect
	 */
	protected function getMobileUrl(){
		if(!empty($this->cname)){
			return $this->cname;
		} elseif(!empty($this->alias)){
			return 'https://'.$this->alias.$this->getShopgateUrl();
		}
	}

	/**
	 * Returns the URL to be appended to the alias of a shop.
	 *
	 * The method determines this by the "server" setting in ShopgateConfig. If it's set to
	 * "custom", localdev.cc will be used for Shopgate local development and testing.
	 *
	 * @return string The URL that can be appended to the alias, e.g. ".shopgate.com"
	 */
	protected function getShopgateUrl() {
		switch ($this->serverType) {
			default: // fall through to "live"
			case 'live':	return ShopgateMobileRedirectInterface::SHOPGATE_LIVE_ALIAS;
			case 'pg':		return ShopgateMobileRedirectInterface::SHOPGATE_PG_ALIAS;
			case 'custom':	return '.localdev.cc/php/shopgate/index.php'; // for Shopgate development & testing
		}
	}

	/**
	 * Updates the (skip) keywords array from cache file or Shopgate Merchant API if enabled.
	 */
	protected function updateRedirectKeywords() {
		// load the keywords
		try {
			$redirectKeywordsFromFile = $this->loadKeywordsFromFile($this->cacheFileWhitelist);
			$skipRedirectKeywordsFromFile = $this->loadKeywordsFromFile($this->cacheFileBlacklist);
		} catch (ShopgateLibraryException $e) {
			// if reading the files fails DO NOT UPDATE
			return;
		}
		
		// conditions for updating keywords
		$updateDesired = (
			$this->updateRedirectKeywords &&
			(!empty($this->merchantApi)) && (
				(time() - ($redirectKeywordsFromFile['timestamp'] + ($this->redirectKeywordCacheTime * 3600)) > 0) ||
				(time() - ($skipRedirectKeywordsFromFile['timestamp'] + ($this->redirectKeywordCacheTime * 3600)) > 0)
			)
		);
		
		// strip timestamp, it's not needed anymore
		$redirectKeywords = $redirectKeywordsFromFile['keywords'];
		$skipRedirectKeywords = $skipRedirectKeywordsFromFile['keywords'];
		
		// perform update
		if ($updateDesired) {
			try {
				// fetch keywords from Shopgate Merchant API
				$keywordsFromApi = $this->merchantApi->getMobileRedirectKeywords();
				$redirectKeywords = $keywordsFromApi['keywords'];
				$skipRedirectKeywords = $keywordsFromApi['skipKeywords'];
				
				// save keywords to their files
				$this->saveKeywordsToFile($redirectKeywords, $this->cacheFileWhitelist);
				$this->saveKeywordsToFile($skipRedirectKeywords, $this->cacheFileBlacklist);
			} catch (Exception $e) { /* do not abort */ }
		}
		
		// set keywords
		if (!empty($redirectKeywords)) $this->setRedirectKeywords($redirectKeywords);
		if (!empty($skipRedirectKeywords)) $this->setSkipRedirectKeywords($skipRedirectKeywords);
	}
	
	/**
	 * Saves redirect keywords to file.
	 *
	 * @param string[] $keywords The list of keywords to write to the file.
	 * @param string $file The path to the file.
	 */
	protected function saveKeywordsToFile($keywords, $file) {
		array_unshift($keywords, time()); // add timestamp to first line
		if (!@file_put_contents($file, implode("\n", $keywords))) {
			// no logging - this could end up in spamming the logs
			// $this->log(ShopgateLibraryException::buildLogMessageFor(ShopgateLibraryException::FILE_READ_WRITE_ERROR, 'Could not write to "'.$file.'".'));
		}
	}
	
	/**
	 * Reads redirect keywords from file.
	 *
	 * @param string $file The file to read the keywords from.
	 * @return array<'timestamp' => int, 'keywords' => string[])
	 * 			An array with the 'timestamp' of the last update and the list of 'keywords'.
	 * @throws ShopgateLibraryException in case the file cannot be opened.
	 */
	protected function loadKeywordsFromFile($file) {
		$defaultReturn = array(
				'timestamp' => 0,
				'keywords' => array()
		);
		
		$cacheFile = @fopen($file, 'a+');
		if (empty($cacheFile)) {
			// exception without logging
			throw new ShopgateLibraryException(ShopgateLibraryException::FILE_READ_WRITE_ERROR, 'Could not read file "'.$file.'".', false, false);
		}
		
		$keywordsFromFile = explode("\n", @fread($cacheFile, filesize($file)));
		@fclose($cacheFile);
		
		return (empty($keywordsFromFile))
			? $defaultReturn
			: array(
				'timestamp' => (int) array_shift($keywordsFromFile), // strip timestamp in first line
				'keywords' => $keywordsFromFile,
			);
	}

	#############################
	### mobile url generation ###
	#############################

	public function getShopUrl(){
		return $this->getMobileUrl();
	}

	public function getItemUrl($itemNumber){
		return $this->getMobileUrl().'/item/'.bin2hex($itemNumber);
	}

	public function getCategoryUrl($categoryNumber){
		return $this->getMobileUrl().'/category/'.bin2hex($categoryNumber);
	}

	public function getCmsUrl($key){
		return $this->getMobileUrl().'/cms/'.$key;
	}

	public function getBrandUrl($manufacturerName){
		return $this->getMobileUrl().'/brand/?q='.urlencode($manufacturerName);
	}

	public function getSearchUrl($searchString){
		return $this->getMobileUrl().'/search/?s='.urlencode($searchString);
	}
}