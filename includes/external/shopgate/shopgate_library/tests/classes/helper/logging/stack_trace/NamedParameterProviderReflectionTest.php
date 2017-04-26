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
class Shopgate_Helper_Logging_Stack_Trace_NamedParameterProviderReflectionTest extends PHPUnit_Framework_TestCase
{
    /** @var Shopgate_Helper_Logging_Stack_Trace_NamedParameterProviderReflection */
    protected $SUT;
    
    public function setUp()
    {
        // load some defined functions for testing; TODO move to some bootstrap.php or the like
        include_once(dirname(__FILE__) . '/../../../../stubs/functions.php');
        include_once(dirname(__FILE__) . '/../../../../stubs/ShopgateTestClass.php');
        
        $this->SUT = new Shopgate_Helper_Logging_Stack_Trace_NamedParameterProviderReflection();
    }
    
    public function tearDown()
    {
        parent::tearDown();
        
        $this->SUT = null;
    }
    
    public function testUndefinedFunction()
    {
        $functionName = 'shopgateTestFunctionUndefined';
        
        $this->assertFalse(function_exists($functionName));
        
        $this->assertEquals(
            array(123, 456),
            $this->SUT->get('', $functionName, array(123, 456))
        );
    }
    
    public function testUndefinedClass()
    {
        $className = 'ShopgateTestClassUndefined';
        
        $this->assertFalse(class_exists($className));
        
        $this->assertEquals(
            array(123, 456),
            $this->SUT->get($className, '', array(123, 456))
        );
    }
    
    public function testUndefinedMethod()
    {
        $className  = 'ShopgateTestClass';
        $methodName = 'methodUndefined';
        
        $this->assertTrue(class_exists($className));
        $this->assertFalse(method_exists($className, $methodName));
        
        $this->assertEquals(
            array(123, 456),
            $this->SUT->get($className, $methodName, array(123, 456))
        );
    }
    
    public function testPrivateMethod()
    {
        $className  = 'ShopgateTestClass';
        $methodName = 'methodPrivate';
        
        $this->assertTrue(class_exists($className));
        $this->assertTrue(method_exists($className, $methodName));
        
        $this->assertEquals(
            array('one' => 123),
            $this->SUT->get($className, $methodName, array(123))
        );
    }
    
    public function testProtectedMethod()
    {
        $className  = 'ShopgateTestClass';
        $methodName = 'methodProtected';
        
        $this->assertTrue(class_exists($className));
        $this->assertTrue(method_exists($className, $methodName));
        
        $this->assertEquals(
            array('one' => 123),
            $this->SUT->get($className, $methodName, array(123))
        );
    }
    
    public function testDefinedFunction()
    {
        $this->assertEquals(
            array(),
            $this->SUT->get('', 'shopgateTestFunctionWithNoParameters', array())
        );
        
        $this->assertEquals(
            array('unnamed argument 0' => 123, 'unnamed argument 1' => 456),
            $this->SUT->get('', 'shopgateTestFunctionWithNoParameters', array(123, 456))
        );
        
        $this->assertEquals(
            array('one' => 123),
            $this->SUT->get('', 'shopgateTestFunctionWithOneParameter', array(123))
        );
        
        $this->assertEquals(
            array('one' => 123, 'unnamed argument 1' => 456),
            $this->SUT->get('', 'shopgateTestFunctionWithOneParameter', array(123, 456))
        );
        
        $this->assertEquals(
            array('one' => 123, 'two' => '[defaultValue:optional]'),
            $this->SUT->get('', 'shopgateTestFunctionWithTwoParameters', array(123))
        );
        
        $this->assertEquals(
            array('one' => 123, 'two' => 'test'),
            $this->SUT->get('', 'shopgateTestFunctionWithTwoParameters', array(123, 'test'))
        );
        
        $this->assertEquals(
            array('one' => 123, 'two' => 456, 'unnamed argument 2' => 'test'),
            $this->SUT->get('', 'shopgateTestFunctionWithTwoParameters', array(123, 456, 'test'))
        );
    }
    
    public function testDefinedMethod()
    {
        $this->assertEquals(
            array(),
            $this->SUT->get('ShopgateTestClass', 'methodWithNoParameters', array())
        );
        
        $this->assertEquals(
            array('unnamed argument 0' => 123, 'unnamed argument 1' => 456),
            $this->SUT->get('ShopgateTestClass', 'methodWithNoParameters', array(123, 456))
        );
        
        $this->assertEquals(
            array('one' => 123),
            $this->SUT->get('ShopgateTestClass', 'methodWithOneParameter', array(123))
        );
        
        $this->assertEquals(
            array('one' => 123, 'unnamed argument 1' => 456),
            $this->SUT->get('ShopgateTestClass', 'methodWithOneParameter', array(123, 456))
        );
        
        $this->assertEquals(
            array('one' => 123, 'two' => '[defaultValue:optional]'),
            $this->SUT->get('ShopgateTestClass', 'methodWithTwoParameters', array(123))
        );
        
        $this->assertEquals(
            array('one' => 123, 'two' => 'test'),
            $this->SUT->get('ShopgateTestClass', 'methodWithTwoParameters', array(123, 'test'))
        );
        
        $this->assertEquals(
            array('one' => 123, 'two' => 456, 'unnamed argument 2' => 'test'),
            $this->SUT->get('ShopgateTestClass', 'methodWithTwoParameters', array(123, 456, 'test'))
        );
    }
}