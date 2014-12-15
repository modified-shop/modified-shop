<?php
require_once(dirname(__FILE__).'/../core/lib/arrayToXml.php');

class Unit_ArrayToXmlTest extends SofortLibTest {
	
	protected $_classToTest = 'ArrayToXml';
	
	public function testCheckDepth () {
		$this->setExpectedException('ArrayToXmlException');
		$ArrayToXml = new ArrayToXml(array(array(1)));
		$checkDepth = self::_getMethod('_checkDepth', $this->_classToTest);
		$checkDepth->invoke($ArrayToXml, array(11));
		
	}
	
	
	public function testConstruct () {
		$ArrayToXml = new ArrayToXml(array());
		$this->assertAttributeEquals('', '_parsedData', $ArrayToXml);
		
		$ArrayToXml = new ArrayToXml(array(), 5, false);
		$this->assertAttributeEquals(5, '_maxDepth', $ArrayToXml);
	}
	
	
	public function testConstructInputSizeException () {
		$this->setExpectedException('ArrayToXmlException');
		$ArrayToXml = new ArrayToXml(array(1,2));
	}
	
	
	public function testConstructMaxDepthException () {
		$this->setExpectedException('ArrayToXmlException');
		$ArrayToXml = new ArrayToXml(array(1), 55);
	}
	
	
	public function testCreateNode () {
		$ArrayToXml = new ArrayToXml(array(array(1)));
		$SofortTag = new SofortTag('node', array('attribute1' => 1), array());
		$createNode = self::_getMethod('_createNode', $this->_classToTest);
		$this->assertEquals($createNode->invoke($ArrayToXml, 'node', array('attribute1' => 1), array()), $SofortTag);
	}
	
	
	public function testCreateTextNode () {
		$ArrayToXml = new ArrayToXml(array(array(1)));
		$SofortText = new SofortText('node', true, false);
		$createTextNode = self::_getMethod('_createTextNode', $this->_classToTest);
		$this->assertEquals($createTextNode->invoke($ArrayToXml, 'node', false), $SofortText);
	}
	
	
	public function testExtractAttributesSection () {
		$ArrayToXml = new ArrayToXml(array(array(1)));
		$extractAttributesSection = self::_getMethod('_extractAttributesSection', $this->_classToTest);
		
		$node = array('@attributes' => 'test');
		$attributes = array('test');
		$this->assertEquals($extractAttributesSection->invoke($ArrayToXml, &$node), $attributes);
		
		$node = array('@attributes' => array('test'));
		$attributes = array('test');
		$this->assertEquals($extractAttributesSection->invoke($ArrayToXml, &$node), $attributes);
		
		$node = array('@attributes' => false);
		$attributes = array();
		$this->assertEquals($extractAttributesSection->invoke($ArrayToXml, &$node), $attributes);
	}
	
	
	public function testExtractDataSection () {
		$ArrayToXml = new ArrayToXml(array(array(1)));
		$extractDataSection = self::_getMethod('_extractDataSection', $this->_classToTest);
		$SofortText = new SofortText('node', true, false);
		
		$node = array('@data' => 'node');
		$this->assertEquals($extractDataSection->invoke($ArrayToXml, &$node, true), array($SofortText));
		
		$node = array('@data' => false);
		$data = array();
		$this->assertEquals($extractDataSection->invoke($ArrayToXml, &$node, true), $data);
	}
	
	
	public function testPrivateRender () {
		$ArrayToXml = new ArrayToXml(array(array(1)));
		$render = self::_getMethod('_render', $this->_classToTest);
		$SofortTag = new SofortTag('node', array('attribute1' => 1), array());
		$render->invoke($ArrayToXml, array('test'), $SofortTag, 5, true);
		$render->invoke($ArrayToXml, 'test', $SofortTag, 5, true);
	}
	
	
	public function testRender () {
		$ArrayToXml = new ArrayToXml(array(array(1)));
		$this->assertEquals(
			"<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n<test>1</test>",
			$ArrayToXml->render(array('test' => 1))
		);
	}
	
	
	public function testToXml () {
		$ArrayToXml = new ArrayToXml(array(array(1)));
		$parsedData = self::_getProperty('_parsedData', $this->_classToTest);
		$test = 'Test';
		$parsedData->setValue($ArrayToXml, $test);
		$this->assertEquals($test, $ArrayToXml->toXml(false, false));
		$this->assertEquals("<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\nTest", $ArrayToXml->toXml());
	}
}