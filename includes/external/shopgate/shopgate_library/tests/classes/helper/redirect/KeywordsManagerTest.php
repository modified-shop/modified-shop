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
class Shopgate_Helper_Redirect_KeywordsManagerTest extends PHPUnit_Framework_TestCase
{
	/** @var ShopgateMerchantApiInterface|PHPUnit_Framework_MockObject_MockObject $merchantApi */
	protected $merchantApi;
	
	/** @var string[] */
	protected $matchingUserAgents;
	
	/** @var string[] */
	protected $nonMatchingUserAgents;
	
	public function setUp()
	{
		/** @var ShopgateMerchantApiInterface|PHPUnit_Framework_MockObject_MockObject $merchantApi */
		$this->merchantApi = $this->getMockForAbstractClass('ShopgateMerchantApiInterface');
		
		$this->merchantApi->method('getMobileRedirectUserAgents')->will($this->returnValue(
			array(
				'keywords'      => array(
					'redirectbot',
					'iphone',
					'ipod',
					'ipad',
					'android',
					'windows phone 8',
				),
				'skip_keywords' => array(
					'shopgate',
					'nexus 7',
				),
			)
		))
		;
		
		$this->matchingUserAgents = array(
			'redirectbot',
			'iphone',
			'ipod',
			'ipad',
			'android',
			'windows phone 8',
			'redirectbotiphoneipodipadandroid',
			'redirectbotiphoneipodipadandroidwindows phone 8',
			'erdirectbotiphoneipodipadandroidwindows phone 8',
			'windows phone 8redirectbotiphoneipodipadandroid',
			'Mozilla/5.0 (Linux; Android 4.3; Nxs 7 Build/JSS15Q) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.23 Safari/537.36',
			'Mozilla/5.0 (Windows; Windows Phone 8; Windows Phone 8 Build/JSS15Q) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.23 Safari/537.36',
		);
		
		$this->nonMatchingUserAgents = array(
			'',
			'shopgate',
			'nexus 7',
			'shopgatenexus 7',
			'nexus 7shopgate',
			'shopgaterandom',
			'randomshopgate',
			'randomshopgaterandom',
			'nexus 7random',
			'randomnexus 7',
			'randomnexus 7random',
			'Mozilla/5.0 (Linux; Android 4.3; Nexus 7 Build/JSS15Q) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.23 Safari/537.36',
			'Mozilla/5.0 (Linux; Nexus 7 Android 4.3; Build/JSS15Q) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.23 Safari/537.36',
			'Mozilla/5.0 (Linux; Nexus 7 Android 4.3; Nexus 7 Build/JSS15Q) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.23 Safari/537.36',
			'Mozilla/5.0 (Linux; Build/JSS15Q) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.23 Safari/537.36',
			'Mozilla/5.0 (Linux; Nexus 7 Build/JSS15Q) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.23 Safari/537.36',
			'Mozilla/5.0 (Windows; Windows Phone 8; Nexus 7; Windows Phone 8 Build/JSS15Q) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.23 Safari/537.36',
		);
	}
	
	public function testRegexMatchesWhitelistedUserAgents()
	{
		$keywordsManager = new Shopgate_Helper_Redirect_KeywordsManager(
			$this->merchantApi,
			'/dev/null',
			'/dev/null'
		);
		
		$regEx = $keywordsManager->toRegEx();
		
		foreach ($this->matchingUserAgents as $ua) {
			$this->assertRegExp(
				$regEx,
				$ua
			);
		}
	}
	
	public function testRegexDoesNotMatchBlacklistedUserAgents()
	{
		$keywordsManager = new Shopgate_Helper_Redirect_KeywordsManager(
			$this->merchantApi,
			'/dev/null',
			'/dev/null'
		);
		
		$regEx = $keywordsManager->toRegEx();
		
		foreach ($this->nonMatchingUserAgents as $ua) {
			$this->assertNotRegExp(
				$regEx,
				$ua
			);
		}
	}
}