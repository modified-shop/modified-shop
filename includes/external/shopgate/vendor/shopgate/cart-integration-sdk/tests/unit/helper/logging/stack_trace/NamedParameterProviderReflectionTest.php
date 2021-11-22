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
class Shopgate_Helper_Logging_Stack_Trace_NamedParameterProviderReflectionTest extends PHPUnit_Framework_TestCase
{
    /** @var Shopgate_Helper_Logging_Stack_Trace_NamedParameterProviderReflection */
    protected $subjectUnderTest;

    public function setUp()
    {
        // load some defined functions for testing; TODO move to some bootstrap.php or the like
        include_once(dirname(__FILE__) . '/../../../../stubs/functions.php');
        include_once(dirname(__FILE__) . '/../../../../stubs/ShopgateTestClass.php');

        $this->subjectUnderTest = new Shopgate_Helper_Logging_Stack_Trace_NamedParameterProviderReflection();
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->subjectUnderTest = null;
    }

    public function testUndefinedFunction()
    {
        $functionName = 'shopgateTestFunctionUndefined';

        $this->assertFalse(function_exists($functionName));

        $this->assertEquals(
            array(123, 456),
            $this->subjectUnderTest->get('', $functionName, array(123, 456))
        );
    }

    public function testUndefinedClass()
    {
        $className = 'ShopgateTestClassUndefined';

        $this->assertFalse(class_exists($className));

        $this->assertEquals(
            array(123, 456),
            $this->subjectUnderTest->get($className, '', array(123, 456))
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
            $this->subjectUnderTest->get($className, $methodName, array(123, 456))
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
            $this->subjectUnderTest->get($className, $methodName, array(123))
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
            $this->subjectUnderTest->get($className, $methodName, array(123))
        );
    }

    public function testDefinedFunction()
    {
        $this->assertEquals(
            array(),
            $this->subjectUnderTest->get('', 'shopgateTestFunctionWithNoParameters', array())
        );

        $this->assertEquals(
            array('unnamed argument 0' => 123, 'unnamed argument 1' => 456),
            $this->subjectUnderTest->get('', 'shopgateTestFunctionWithNoParameters', array(123, 456))
        );

        $this->assertEquals(
            array('one' => 123),
            $this->subjectUnderTest->get('', 'shopgateTestFunctionWithOneParameter', array(123))
        );

        $this->assertEquals(
            array('one' => 123, 'unnamed argument 1' => 456),
            $this->subjectUnderTest->get('', 'shopgateTestFunctionWithOneParameter', array(123, 456))
        );

        $this->assertEquals(
            array('one' => 123, 'two' => '[defaultValue:optional]'),
            $this->subjectUnderTest->get('', 'shopgateTestFunctionWithTwoParameters', array(123))
        );

        $this->assertEquals(
            array('one' => 123, 'two' => 'test'),
            $this->subjectUnderTest->get('', 'shopgateTestFunctionWithTwoParameters', array(123, 'test'))
        );

        $this->assertEquals(
            array('one' => 123, 'two' => 456, 'unnamed argument 2' => 'test'),
            $this->subjectUnderTest->get('', 'shopgateTestFunctionWithTwoParameters', array(123, 456, 'test'))
        );

        $this->assertEquals(
            array('one' => '[defaultValue:true]', 'two' => '[defaultValue:false]'),
            $this->subjectUnderTest->get('', 'shopgateTestFunctionWithDefaultBooleanParameters', array())
        );

        $this->assertEquals(
            array('one' => '[defaultValue:array]'),
            $this->subjectUnderTest->get('', 'shopgateTestFunctionWithDefaultArrayParameter', array())
        );

        $this->assertEquals(
            array('one' => '[defaultValue:null]'),
            $this->subjectUnderTest->get('', 'shopgateTestFunctionWithDefaultNullParameter', array())
        );
    }

    public function testDefinedMethod()
    {
        $this->assertEquals(
            array(),
            $this->subjectUnderTest->get('ShopgateTestClass', 'methodWithNoParameters', array())
        );

        $this->assertEquals(
            array('unnamed argument 0' => 123, 'unnamed argument 1' => 456),
            $this->subjectUnderTest->get('ShopgateTestClass', 'methodWithNoParameters', array(123, 456))
        );

        $this->assertEquals(
            array('one' => 123),
            $this->subjectUnderTest->get('ShopgateTestClass', 'methodWithOneParameter', array(123))
        );

        $this->assertEquals(
            array('one' => 123, 'unnamed argument 1' => 456),
            $this->subjectUnderTest->get('ShopgateTestClass', 'methodWithOneParameter', array(123, 456))
        );

        $this->assertEquals(
            array('one' => 123, 'two' => '[defaultValue:optional]'),
            $this->subjectUnderTest->get('ShopgateTestClass', 'methodWithTwoParameters', array(123))
        );

        $this->assertEquals(
            array('one' => 123, 'two' => 'test'),
            $this->subjectUnderTest->get('ShopgateTestClass', 'methodWithTwoParameters', array(123, 'test'))
        );

        $this->assertEquals(
            array('one' => 123, 'two' => 456, 'unnamed argument 2' => 'test'),
            $this->subjectUnderTest->get('ShopgateTestClass', 'methodWithTwoParameters', array(123, 456, 'test'))
        );

        $this->assertEquals(
            array('one' => '[defaultValue:true]', 'two' => '[defaultValue:false]'),
            $this->subjectUnderTest->get('ShopgateTestClass', 'methodWithDefaultBooleanParameters', array())
        );

        $this->assertEquals(
            array('one' => '[defaultValue:array]'),
            $this->subjectUnderTest->get('ShopgateTestClass', 'methodWithDefaultArrayParameter', array())
        );

        $this->assertEquals(
            array('one' => '[defaultValue:null]'),
            $this->subjectUnderTest->get('ShopgateTestClass', 'methodWithDefaultNullParameter', array())
        );
    }
}
