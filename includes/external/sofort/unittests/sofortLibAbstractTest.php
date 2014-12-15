<?php
require_once(dirname(__FILE__).'/../core/sofortLibAbstract.inc.php');
require_once('sofortLibTest.php');

/**
 * Class constructed just to test the methods of the abstract class
 * @author mm
 */
class SofortLibAbstractMock extends SofortLibAbstract {}

class Unit_SofortLibAbstractTest extends SofortLibTest {

	protected $_classToTest = 'SofortLibAbstractMock';
	
	private $_handledErrors = array(
		'global' => array(
			0 => array(
				'code' => 8068,
				'message' => 'Payment type invoice not available for business customers.',
				'field' => '',
			),
			1 => array(
				'code' => 8054,
				'message' => 'All products deactivated due to errors, initiation aborted.',
				'field' => '',
			),
		),
		'su' => array(
			0 => array(
				'code' => 8068,
				'message' => 'Payment type invoice not available for business customers.',
				'field' => 'invoice_address.salutation',
			),
			1 => array(
				'code' => 8068,
				'message' => 'Payment type invoice not available for business customers.',
				'field' => '',
			),
		),
	);
	
	private $_handledErrorsRoot = array(
		0 => array(
			'code' => 8068,
			'message' => 'Payment type invoice not available for business customers.',
			'field' => '',
		),
		1 => array(
			'code' => 8054,
			'message' => 'All products deactivated due to errors, initiation aborted.',
			'field' => '',
		),
		2 => array(
			'code' => '8068.invoice_address.salutation',
			'message' => 'Payment type invoice not available for business customers.',
			'field' => 'invoice_address.salutation',
		),
		3 => array(
			'code' => 8068,
			'message' => 'Payment type invoice not available for business customers.',
			'field' => '',
		),
	);
	
	private $_handledWarnings = array(
		'global' => array(
			0 => array(
				'code' => 8068,
				'message' => 'Payment type invoice not available for business customers.',
				'field' => 'invoice_address.salutation',
			),
			1 => array(
				'code' => 8054,
				'message' => 'All products deactivated due to errors, initiation aborted.',
				'field' => '',
			),
		),
		'su' => array(
			0 => array(
				'code' => 9007,
				'message' => 'Comfortably Numb.',
				'field' => '',
			),
			1 => array(
				'code' => 9008,
				'message' => 'Lorem Ipsim.',
				'field' => '',
			),
		),
	);
	
	private $_handledWarningsRoot = array(
		0 => array(
			'code' => 8068,
			'message' => 'Payment type invoice not available for business customers.',
			'field' => 'invoice_address.salutation',
		),
		1 => array(
			'code' => 8054,
			'message' => 'All products deactivated due to errors, initiation aborted.',
			'field' => '',
		),
		2 => array(
			'code' => 9007,
			'message' => 'Comfortably Numb.',
			'field' => '',
		),
		3 => array(
			'code' => 9008,
			'message' => 'Lorem Ipsim.',
			'field' => '',
		),
	);
	
	
	public function testGetData() {
		$SofortLibAbstractMock = new SofortLibAbstractMock(self::$configkey);
		$rootTag = self::_getProperty('_rootTag', $this->_classToTest);
		$rootTag->setValue($SofortLibAbstractMock, 'multipay');
		$SofortLibAbstractMock->setParameters(array('test'));
		$expected = array(
			'multipay' => array(0 => 'test', 'project_id' => '67890', '@attributes' => array ('version' => '1.0'))
		);
		$this->assertEquals($expected, $SofortLibAbstractMock->getData());
	}
	
	
	public function testGetDataHandler() {
		$SofortLibAbstractMock = new SofortLibAbstractMock(self::$configkey);
		$AbstractDataHandler = $this->getMockForAbstractClass('AbstractDataHandler', array(), '', FALSE);
		$SofortLibAbstractMock->setDataHandler($AbstractDataHandler);
		$this->assertEquals($AbstractDataHandler, $SofortLibAbstractMock->getDataHandler());
	}
	
	
	public function providerGetError () {
		return array(
			array(array('test'), 'Error: -1:test',),
			array(array('test'),  'Error: -1:test',),
			array(array('test', 'su'), 'Error: -1:test',),
			array(array('test', 'sr', 4711), 'Error: 4711:test',),
			array(array('test', 'sr', 4711, 'zip'), 'Error: 4711:test',),
			array(array('test', 'not', 4711, 'zip'), 'Error: 4711:test',),
			array(array('', 'not', 4711, 'zip'), 'Error: 4711:',),
		);
	}
	
	
	public function testGetParameters() {
		$SofortLibAbstractMock = new SofortLibAbstractMock(self::$configkey);
		$expected = array('test', 'test2');
		$SofortLibAbstractMock->setParameters($test_array = array('test', 'test2'));
		$this->assertEquals($expected, $SofortLibAbstractMock->getParameters());
	}
	
	
	public function providerIsWarning() {
		return array(
				array(
						array('test'),
						array('global' => array(0 => array('code' => -1, 'message' =>'test', 'field' => ''))),
				),
				array(
						array('test', 'global'),
						array('global' => array(0 => array('code' => -1, 'message' =>'test', 'field' => ''))),
				),
				array(
						array('test', 'su'),
						array('su' => array(0 => array('code' => -1, 'message' =>'test', 'field' => ''))),
				),
				array(
						array('test', 'sr'),
						array('sr' => array(0 => array('code' => 4711, 'message' =>'test', 'field' => ''))),
				),
				array(
						array('test', 'sr'),
						array('sr' => array(0 => array('code' => 4711, 'message' =>'test', 'field' => 'zip'))),
				),
				array(
						array('test', 'not'),
						array('global' => array(0 => array('code' => 4711, 'message' =>'test', 'field' => 'zip'))),
				),
				array(
						array('', 'not'),
						array('global' => array(0 => array('code' => 4711, 'message' =>'', 'field' => 'zip'))),
				),
		);
	}
	
	
	public function providerSetAbortUrl () {
		return array(
			array('http://www.google.de'),
			array('http://www.sofort.com'),
		);
	}
	
	
	public function providerSetCurrency () {
		return array(
			array('EUR'),
			array('GBP'),
		);
	}
	
	
	public function providerSetError() {
		return array(
			array(
				array('test'),
				array('global' => array(0 => array('code' => -1, 'message' =>'test', 'field' => ''))),
			),
			array(
				array('test', 'global'),
				array('global' => array(0 => array('code' => -1, 'message' =>'test', 'field' => ''))),
			),
			array(
				array('test', 'su'),
				array('su' => array(0 => array('code' => -1, 'message' =>'test', 'field' => ''))),
			),
			array(
				array('test', 'sr', 4711),
				array('sr' => array(0 => array('code' => 4711, 'message' =>'test', 'field' => ''))),
			),
			array(
				array('test', 'sr', 4711, 'zip'),
				array('sr' => array(0 => array('code' => 4711, 'message' =>'test', 'field' => 'zip'))),
			),
			array(
				array('test', 'not', 4711, 'zip'),
				array('global' => array(0 => array('code' => 4711, 'message' =>'test', 'field' => 'zip'))),
			),
			array(
				array('', 'not', 4711, 'zip'),
				array('global' => array(0 => array('code' => 4711, 'message' =>'', 'field' => 'zip'))),
			),
		);
	}
	
	
	public function providerSetNotificationEmail () {
		return array(
			array('test@test.de'),
			array(array('test@test.de', "loss")),
		);
	}
	
	
	public function providerSetNotificationUrl () {
		return array(
			array('http://www.google.de'),
			array(array('http://www.google.de', "loss")),
		);
	}
	
	
	public function providerSetSuccessUrl () {
		return array(
			array('http://www.google.de'),
			array('http://www.sofort.com', false),
		);
	}
	
	
	public function providerSetTimeoutUrl () {
		return array(
			array('http://www.google.de'),
			array('http://www.sofort.com'),
		);
	}
	
	
	public function testAbstractSofortLib() {
		$SofortLibAbstractMock = new SofortLibAbstractMock(self::$configkey);
		
		$SofortLibAbstractMock->setParameters(array(array('miep' => 'moep')));
		$this->assertEquals(array(array('miep' => 'moep')), $SofortLibAbstractMock->getParameters());
		
		$SofortLibAbstractMock->setConfigKey(self::$configkey);
		$this->assertEquals(self::$configkey, $SofortLibAbstractMock->getConfigKey());
	}
	
	
	/**
	 * @dataProvider providerIsWarning
	 */
	public function testIsWarning ($provided, $expected) {
		$SofortLibAbstractMock = new SofortLibAbstractMock(self::$configkey);
		$SofortLibAbstractMock->warnings = $expected;
		
		if(isset($provided[1]) && in_array($provided[1], array('global', 'su', 'sr', 'not'))) {
			$provided[1] = ($provided[1] == 'not') ? 'all' : $provided[1];
			$this->assertTrue($SofortLibAbstractMock->isWarning($provided[1]));
			$this->assertFalse($SofortLibAbstractMock->isWarning($provided[1], 'test'));
		} else {
			$this->assertTrue($SofortLibAbstractMock->isWarning());
		}
		
		$SofortLibAbstractMock->warnings = 'test';
		$this->assertEquals('test', $SofortLibAbstractMock->warnings);
		
		$SofortLibAbstractMock->warnings = NULL;
		$this->assertFalse($SofortLibAbstractMock->isWarning('all'));
	}
	
	
	public function testGetConfigKey() {
		$configKey = '12345:12345:abcdefghijklmnopqrstuvewxyz123456';
		$SofortLibAbstractMock = new SofortLibAbstractMock(self::$configkey);
		$SofortLibAbstractMock->setConfigKey($configKey);
		$this->assertEquals($SofortLibAbstractMock->getConfigKey(), $configKey);
	}
	
	
	/**
	 * @dataProvider providerGetError
	 */
	public function testGetError($provided, $expected) {
		$SofortLibAbstractMock = new SofortLibAbstractMock(self::$configkey);
		
		if(count($provided) == 4) {
			$SofortLibAbstractMock->setError($provided[0], $provided[1], $provided[2], $provided[3]);
		} else if (count($provided) == 3) {
			$SofortLibAbstractMock->setError($provided[0], $provided[1], $provided[2]);
		} else if (count($provided) == 2) {
			$SofortLibAbstractMock->setError($provided[0], $provided[1]);
		} else {
			$SofortLibAbstractMock->setError($provided[0]);
		}
		
		if(isset($provided[1]) && in_array($provided[1], array('global', 'su', 'sr', 'not',))) {
			$provided[1] = ($provided[1] == 'not') ? 'all' : $provided[1];
			$this->assertFalse($SofortLibAbstractMock->getError($provided[1], 'test'));
			$this->assertEquals($expected, $SofortLibAbstractMock->getError($provided[1]));
		} else {
			$this->assertEquals($expected, $SofortLibAbstractMock->getError());
		}
		
		$this->assertFalse($SofortLibAbstractMock->getError('su', array('testen' => 'test')));
	}
	
	
	public function testGetErrors() {
		$SofortLibAbstractMock = new SofortLibAbstractMock(self::$configkey);
		
		$SofortLibAbstractMock->errors = $this->_handledErrors;
		$this->assertEquals($this->_handledErrorsRoot, $SofortLibAbstractMock->getErrors());
		
		unset($SofortLibAbstractMock->errors);
		$this->assertEquals(array(), $SofortLibAbstractMock->getErrors('all', 'something'));
	}
	
	
	public function testGetLogger() {
		$SofortLibAbstractMock = new SofortLibAbstractMock(self::$configkey);
		$FileLoggerHandler = $this->getMockForAbstractClass('fileLogger');
		$SofortLibAbstractMock->setLogger($FileLoggerHandler);
		$this->assertEquals($SofortLibAbstractMock->getLogger(), $FileLoggerHandler);
	}
	
	
	public function testGetRawRequest() {
		$SofortLibAbstractMock = new SofortLibAbstractMock(self::$configkey);
		$AbstractDataHandler = $this->getMockForAbstractClass('AbstractDataHandler',
				array(),
				'',
				FALSE,
				TRUE,
				TRUE,
				array('getRawRequest'));
		
		$AbstractDataHandler->expects($this->any())->method('getRawRequest');
		$SofortLibAbstractMock->setDataHandler($AbstractDataHandler);
		$this->assertEquals(NULL, $SofortLibAbstractMock->getRawRequest());
	}
	
	
	public function testGetRawResponse() {
		$SofortLibAbstractMock = new SofortLibAbstractMock(self::$configkey);
		$AbstractDataHandler = $this->getMockForAbstractClass('AbstractDataHandler',
				array(),
				'',
				FALSE,
				TRUE,
				TRUE,
				array('getRawResponse'));
		
		$AbstractDataHandler->expects($this->any())->method('getRawResponse');
		$SofortLibAbstractMock->setDataHandler($AbstractDataHandler);
		$this->assertEquals(NULL, $SofortLibAbstractMock->getRawResponse());
	}
	
	
	public function testGetRequest() {
		$SofortLibAbstractMock = new SofortLibAbstractMock(self::$configkey);
		$request = self::_getProperty('_request', $this->_classToTest);
		$testdata = 'sometestdata';
		$request->setValue($SofortLibAbstractMock, $testdata);
		$this->assertEquals($testdata, $SofortLibAbstractMock->getRequest());
	}
	
	
	public function testGetWarnings() {
		$SofortLibAbstractMock = new SofortLibAbstractMock(self::$configkey);
		$SofortLibAbstractMock->warnings = $this->_handledWarnings;
		$this->assertEquals($this->_handledWarningsRoot, $SofortLibAbstractMock->getWarnings());
	}
	
	
	public function testLog() {
		$SofortLibAbstractMock = new SofortLibAbstractMock(self::$configkey);
		$FileLoggerHandler = $this->getMockForAbstractClass('fileLogger');
		$FileLoggerHandler->expects($this->any())->method('log')->with('log')->will($this->returnValue('log'));
		
		$SofortLibAbstractMock->setLogger($FileLoggerHandler);
		$SofortLibAbstractMock->setLogEnabled();
		$this->assertNull($SofortLibAbstractMock->log('log'));
	}
	
	
	public function testLogError() {
		$SofortLibAbstractMock = new SofortLibAbstractMock(self::$configkey);
		$FileLoggerHandler = $this->getMockForAbstractClass('fileLogger');
		$FileLoggerHandler->expects($this->any())->method('log')->with('error')->will($this->returnValue('error'));
		
		$SofortLibAbstractMock->setLogger($FileLoggerHandler);
		$SofortLibAbstractMock->setLogEnabled();
		$this->assertNull($SofortLibAbstractMock->logError('error'));
	}
	
	
	public function testLogWarning() {
		$SofortLibAbstractMock = new SofortLibAbstractMock(self::$configkey);
		
		$FileLoggerHandler = $this->getMockForAbstractClass('fileLogger');
		$FileLoggerHandler->expects($this->any())->method('log')->with('warning')->will($this->returnValue('warning'));
		
		$SofortLibAbstractMock->setLogger($FileLoggerHandler);
		$SofortLibAbstractMock->setLogEnabled();
		$this->assertNull($SofortLibAbstractMock->logWarning('warning'));
	}
	
	
	public function testSendRequest () {
		$validate_only = self::_getProperty('_validateOnly', $this->_classToTest);
		$SofortLibAbstractMock = new SofortLibAbstractMock(self::$configkey);
		$AbstractDataHandler = $this->getMockForAbstractClass('AbstractDataHandler',
				array(),
				'',
				FALSE,
				TRUE,
				TRUE,
				array('handle', 'getRequest', 'getRawResponse'));
		
		$validate_only->setValue($SofortLibAbstractMock, true);
		$SofortLibAbstractMock->setDataHandler($AbstractDataHandler);
		$SofortLibAbstractMock->sendRequest();
	}
	
	
	/**
	 * @dataProvider providerSetAbortUrl
	 */
	public function testSetAbortUrl ($provided) {
		$SofortLibAbstractMock = new SofortLibAbstractMock(self::$configkey);
		$SofortLibAbstractMock->setAbortUrl($provided);
		$received = $SofortLibAbstractMock->getParameters();
		$this->assertEquals($provided, $received['abort_url']);
	}
	
	
	public function testSetConfigKey() {
		$SofortLibAbstractMock = new SofortLibAbstractMock(self::$configkey);
		$configKey = '12345:12345:abcdefghijklmnopqrstuvewxyz123456';
		$SofortLibAbstractMock->setConfigKey($configKey);
		$this->assertEquals($SofortLibAbstractMock->getConfigKey(), $configKey);
	}
	
	
	/**
	 * @dataProvider providerSetCurrency
	 */
	public function testSetCurrencyCode ($provided) {
		$SofortLibAbstractMock = new SofortLibAbstractMock(self::$configkey);
		$SofortLibAbstractMock->setCurrencyCode($provided);
		$received = $SofortLibAbstractMock->getParameters();
		$this->assertEquals($provided, $received['currency_code']);
	}
	
	
	public function testSetDataHandler() {
		$SofortLibAbstractMock = new SofortLibAbstractMock(self::$configkey);
		$AbstractDataHandler = $this->getMockForAbstractClass('AbstractDataHandler', array(), '', FALSE);
		$SofortLibAbstractMock->setDataHandler($AbstractDataHandler);
		$this->assertEquals($AbstractDataHandler, $SofortLibAbstractMock->getDataHandler());
	}
	
	
	/**
	 * @dataProvider providerSetError
	 */
	public function testSetError($provided, $expected) {
		$SofortLibAbstractMock = new SofortLibAbstractMock(self::$configkey);
		$this->assertFalse($SofortLibAbstractMock->isError());
		
		if(count($provided) == 4) {
			$SofortLibAbstractMock->setError($provided[0], $provided[1], $provided[2], $provided[3]);
		} else if (count($provided) == 3) {
			$SofortLibAbstractMock->setError($provided[0], $provided[1], $provided[2]);
		} else if (count($provided) == 2) {
			$SofortLibAbstractMock->setError($provided[0], $provided[1]);
		} else {
			$SofortLibAbstractMock->setError($provided[0]);
		}
		
		$this->assertEquals($expected, $SofortLibAbstractMock->errors);
		
		if(isset($provided[1]) && in_array($provided[1], array('global', 'su', 'sr', 'not'))) {
			$provided[1] = ($provided[1] == 'not') ? 'all' : $provided[1];
			$this->assertTrue($SofortLibAbstractMock->isError($provided[1]));
			$this->assertFalse($SofortLibAbstractMock->isError($provided[1], 'test'));
		} else {
			$this->assertTrue($SofortLibAbstractMock->isError());
		}
	}
	
	
	public function testSetLogDisabled() {
		$SofortLibAbstractMock = new SofortLibAbstractMock(self::$configkey);
		$SofortLibAbstractMock->setLogEnabled();
		$SofortLibAbstractMock->setLogDisabled();
		$this->assertFalse($SofortLibAbstractMock->enableLogging);
	}
	
	
	public function testSetLogEnabled() {
		$SofortLibAbstractMock = new SofortLibAbstractMock(self::$configkey);
		$SofortLibAbstractMock->setLogDisabled();
		$SofortLibAbstractMock->setLogEnabled();
		$this->assertTrue($SofortLibAbstractMock->enableLogging);
	}
	
	
	public function testSetLogger() {
		$SofortLibAbstractMock = new SofortLibAbstractMock(self::$configkey);
		$AbstractLoggerHandler = $this->getMockForAbstractClass('AbstractLoggerHandler');
		$SofortLibAbstractMock->setLogger($AbstractLoggerHandler);
		$this->assertAttributeEquals($AbstractLoggerHandler, '_Logger', $SofortLibAbstractMock);
	}
	
	
	/**
	 * @dataProvider providerSetNotificationEmail
	 */
	public function testSetNotificationEmail ($provided) {
		$SofortLibAbstractMock = new SofortLibAbstractMock(self::$configkey);
		
		if(!is_array($provided)) {
			$SofortLibAbstractMock->setNotificationEmail($provided);
			$received = $SofortLibAbstractMock->getParameters();
			$this->assertEquals($provided, $received['notification_emails']['notification_email'][0]['@data']);
		} else {
			$SofortLibAbstractMock->setNotificationEmail($provided[0], $provided[1]);
			$received = $SofortLibAbstractMock->getParameters();
			$this->assertEquals($provided[0], $received['notification_emails']['notification_email'][0]['@data']);
		}
	}
	
	
	/**
	 * @dataProvider providerSetNotificationUrl
	 */
	public function testSetNotificationUrl ($provided) {
		$SofortLibAbstractMock = new SofortLibAbstractMock(self::$configkey);
		
		if(!is_array($provided)) {
			$SofortLibAbstractMock->setNotificationUrl($provided);
			$received = $SofortLibAbstractMock->getParameters();
			$this->assertEquals($provided, $received['notification_urls']['notification_url'][0]['@data']);
		} else {
			$SofortLibAbstractMock->setNotificationUrl($provided[0], $provided[1]);
			$received = $SofortLibAbstractMock->getParameters();
			$this->assertEquals($provided[0], $received['notification_urls']['notification_url'][0]['@data']);
		}
	}
	
	
	/**
	 * @dataProvider providerSetSuccessUrl
	 */
	public function testSetSuccessUrl ($provided) {
		$SofortLibAbstractMock = new SofortLibAbstractMock(self::$configkey);
		
		if(isset($provided[1])) {
			$SofortLibAbstractMock->setSuccessUrl($provided[0], $provided[1]);
		} else {
			$SofortLibAbstractMock->setSuccessUrl($provided[0]);
		}
		
		$received = $SofortLibAbstractMock->getParameters();
		$this->assertEquals($provided[0], $received['success_url']);
		$this->assertEquals($provided[1], $received['success_link_redirect']);
	}
	
	
	public function testSetParameters() {
		$SofortLibAbstractMock = new SofortLibAbstractMock(self::$configkey);
		$expected = array('test', 'test2');
		$SofortLibAbstractMock->setParameters($test_array = array('test', 'test2'));
		$this->assertEquals($expected, $SofortLibAbstractMock->getParameters());
	}
	
	
	/**
	 * @dataProvider providerSetTimeoutUrl
	 */
	public function testSetTimeoutUrl ($provided) {
		$SofortLibAbstractMock = new SofortLibAbstractMock(self::$configkey);
		$SofortLibAbstractMock->setTimeoutUrl($provided);
		$received = $SofortLibAbstractMock->getParameters();
		$this->assertEquals($provided, $received['timeout_url']);
	}
}