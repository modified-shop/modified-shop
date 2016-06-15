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
class Shopgate_Helper_Redirect_KeywordsManager implements Shopgate_Helper_Redirect_KeywordsManagerInterface
{
	/** @var ShopgateMerchantApi */
	protected $merchantApi;
	
	/** @var string */
	protected $whitelistCacheFilePath;
	
	/** @var string */
	protected $blacklistCacheFilePath;
	
	/** @var string[] */
	protected $whitelist;
	
	/** @var string[] */
	protected $blacklist;
	
	/** @var int */
	protected $cacheTimeout;
	
	/** @var bool */
	protected $disableUpdate;
	
	/**
	 * @param ShopgateMerchantApiInterface $merchantApi
	 * @param string                       $whitelistCacheFilePath
	 * @param string                       $blacklistCacheFilePath
	 * @param int                          $cacheTimeout
	 * @param bool                         $disableUpdate
	 */
	public function __construct(
		ShopgateMerchantApiInterface $merchantApi,
		$whitelistCacheFilePath,
		$blacklistCacheFilePath,
		$cacheTimeout = self::DEFAULT_CACHE_TIME,
		$disableUpdate = false
	) {
		$this->merchantApi            = $merchantApi;
		$this->whitelistCacheFilePath = $whitelistCacheFilePath;
		$this->blacklistCacheFilePath = $blacklistCacheFilePath;
		$this->cacheTimeout           = $cacheTimeout;
		$this->disableUpdate          = $disableUpdate;
		
		$this->whitelist = array();
		$this->blacklist = array();
		
		$this->init();
	}
	
	public function toRegEx()
	{
		return
			'/^' .
			
			// negative lookahead for the blacklist
			'((?!' . implode('|', array_map(array($this, 'prepareKeyword'), $this->blacklist)) . ').)*' .
			
			// positive lookahead for the whitelist
			'(?=' . implode('|', array_map(array($this, 'prepareKeyword'), $this->whitelist)) . ')' .
			
			// negative lookahead for the blacklist
			'((?!' . implode('|', array_map(array($this, 'prepareKeyword'), $this->blacklist)) . ').)*' .
			
			// modfiers: case-insensitive
			'$/i';
	}
	
	public function getWhitelist()
	{
		return $this->whitelist;
	}
	
	public function getBlacklist()
	{
		return $this->blacklist;
	}
	
	public function update()
	{
		$this->initFromApi();
		$newUpdateTimestamp = time();
		
		// update the cache files
		$this->saveAllKeywordsToFiles($newUpdateTimestamp);
	}
	
	/**
	 * Initializes the whitelist and blacklist from the Shopgate Merchant API or cache files.
	 */
	protected function init()
	{
		try {
			$lastUpdate = $this->initFromFiles();
		} catch (ShopgateLibraryException $e) {
			// If this fails with an exception we have a most likely permanent problem.
			// In that case the lists _MUST NOT_ be fetched from the API as chances are high this would happen for
			// every request to the merchant's desktop site.
			return;
		}
		
		if ($this->disableUpdate || !$this->expired($lastUpdate)) {
			return;
		}
		
		try {
			$this->update();
		} catch (Exception $e) {
			// if fetching from API fails, try again in 5 minutes; meanwhile use the cached keywords
			$this->saveAllKeywordsToFiles($newUpdateTimestamp = (time() - ($this->cacheTimeout * 3600)) + 300);
		}
	}
	
	/**
	 * @return int The timestamp of the earlier last update from both files.
	 *
	 * @throws ShopgateLibraryException
	 */
	protected function initFromFiles()
	{
		$whitelistMeta = $this->loadKeywordsFromFile($this->whitelistCacheFilePath);
		$blacklistMeta = $this->loadKeywordsFromFile($this->blacklistCacheFilePath);
		
		$this->whitelist = $whitelistMeta['keywords'];
		$this->blacklist = $blacklistMeta['keywords'];
		
		return min($whitelistMeta['lastUpdate'], $blacklistMeta['lastUpdate']);
	}
	
	/**
	 * @throws ShopgateLibraryException
	 */
	protected function initFromApi()
	{
		$response        = $this->merchantApi->getMobileRedirectUserAgents();
		$this->whitelist = $response['keywords'];
		$this->blacklist = $response['skip_keywords'];
	}
	
	/**
	 * @param string $filePath
	 *
	 * @return array An array with indices 'lastUpdate' (int, timestamp) and 'keywords' (string[]).
	 * @throws ShopgateLibraryException
	 */
	protected function loadKeywordsFromFile($filePath)
	{
		$defaultReturn = array(
			'lastUpdate' => 0,
			'keywords'   => array()
		);
		
		$cacheFile = @fopen($filePath, 'a+');
		if (empty($cacheFile)) {
			// exception without logging
			throw new ShopgateLibraryException(ShopgateLibraryException::FILE_READ_WRITE_ERROR,
				'Could not read file "' . $filePath . '".', false, false);
		}
		
		$keywordsFromFile = explode("\n", @fread($cacheFile, filesize($filePath)));
		@fclose($cacheFile);
		
		return (empty($keywordsFromFile))
			? $defaultReturn
			: array(
				'lastUpdate' => (int)array_shift($keywordsFromFile), // strip timestamp in first line
				'keywords'   => $keywordsFromFile,
			);
	}
	
	/**
	 * Saves the internal black and white lists to files.
	 *
	 * @param int $lastUpdate The timestamp to use or null to use time().
	 */
	protected function saveAllKeywordsToFiles($lastUpdate = null)
	{
		if ($lastUpdate === null) {
			$lastUpdate = time();
		}
		
		$this->saveKeywordsToFile($this->whitelist, $this->whitelistCacheFilePath, $lastUpdate);
		$this->saveKeywordsToFile($this->blacklist, $this->blacklistCacheFilePath, $lastUpdate);
	}
	
	/**
	 * Saves redirect keywords to file.
	 *
	 * @param string[] $keywords      The list of keywords to write to the file.
	 * @param string   $cacheFilePath The path to the file.
	 * @param int      $lastUpdate    The timestamp to use or null to use time().
	 */
	protected function saveKeywordsToFile($keywords, $cacheFilePath, $lastUpdate = null)
	{
		if ($lastUpdate === null) {
			$lastUpdate = time();
		}
		
		// add timestamp to first line
		array_unshift($keywords, $lastUpdate);
		
		// save without logging - this could end up in spamming the logs
		@file_put_contents($cacheFilePath, implode("\n", $keywords));
	}
	
	/**
	 * @param int $lastUpdate The timestamp to be examined.
	 * @param int $now        The time of "now" or null to use time().
	 *
	 * @return bool
	 */
	protected function expired($lastUpdate, $now = null)
	{
		if ($now === null) {
			$now = time();
		}
		
		return ($now - ($lastUpdate + ($this->cacheTimeout * 3600)) > 0);
	}
	
	/**
	 * A callback for array_map that prepares keywords for the regex.
	 *
	 * Currently this means the keyword is made lower-case and preg_quote()'ed with a slash (/) as escape character.
	 *
	 * @param string $keyword
	 *
	 * @return string
	 */
	protected function prepareKeyword($keyword)
	{
		return preg_quote(strtolower($keyword), '/');
	}
}