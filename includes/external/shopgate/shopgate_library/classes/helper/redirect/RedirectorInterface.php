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
 *
 * Offers functions to redirect requesting entities to the mobile website, depending on the type.
 */
interface Shopgate_Helper_Redirect_RedirectorInterface
{
	/**
	 * @post If enabled, a location header to the mobile default/fallback page is sent to the requesting entity.
	 */
	public function redirectDefault();
	
	/**
	 * @post A location header to the mobile home page is sent to the requesting entity.
	 */
	public function redirectHome();
	
	/**
	 * @param string $uid
	 *
	 * @post A location header to the mobile category detail page is sent to the requesting entity.
	 */
	public function redirectCategory($uid);
	
	/**
	 * @param string $uid
	 *
	 * @post A location header to the mobile product page is sent to the requesting entity.
	 */
	public function redirectProduct($uid);
	
	/**
	 * @param string $pageUid
	 *
	 * @post A location header to the mobile CMS page is sent to the requesting entity.
	 */
	public function redirectCms($pageUid);
	
	/**
	 * @param string $brandName
	 *
	 * @post A location header to the mobile brand search is sent to the requesting entity.
	 */
	public function redirectBrand($brandName);
	
	/**
	 * @param string $searchString
	 *
	 * @post A location header to the mobile searchpage is sent to the requesting entity.
	 */
	public function redirectSearch($searchString);
	
	/**
	 * @param string $url      The URL to redirect to.
	 * @param bool   $sendVary True to send the "Vary: User-Agent" header.
	 */
	public function redirect($url, $sendVary = true);

	/**
	 * Checks current browser user agent string
	 * against allowed mobile keywords, e.g. Iphone, Android, etc
	 * 
	 * @return bool
	 */
	public function isMobile();
}