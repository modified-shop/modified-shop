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
interface Shopgate_Helper_Redirect_KeywordsManagerInterface
{
	/**
	 * @var int (hours) the default time to be set for updating the cache
	 */
	const DEFAULT_CACHE_TIME = 24;
	
	/**
	 * Returns a regular expression matching everything on the whitelist and not matching anything on the black list.
	 *
	 * @return string
	 */
	public function toRegEx();
	
	/**
	 * @return string[] A list of keywords that identify a smartphone user.
	 */
	public function getWhitelist();
	
	/**
	 * @return string[] A list keywords that identify a smartphone user but should be ignored in the redirect.
	 */
	public function getBlacklist();
	
	/**
	 * Updates the keyword cache from the merchant API regardless of expiry etc.
	 *
	 * @throws ShopgateLibraryException in case the request to the Shopgate Merchant API fails.
	 */
	public function update();
}