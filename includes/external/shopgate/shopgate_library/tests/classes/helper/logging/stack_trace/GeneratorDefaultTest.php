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
class Shopgate_Helper_Logging_Stack_Trace_GeneratorDefaultTest extends PHPUnit_Framework_TestCase
{
    /** @var PHPUnit_Framework_MockObject_MockObject|Shopgate_Helper_Logging_Obfuscator */
    protected $obfuscator;
    
    /** @var PHPUnit_Framework_MockObject_MockObject|Shopgate_Helper_Logging_Stack_Trace_NamedParameterProviderInterface */
    protected $namedParameterProvider;
    
    /** @var Shopgate_Helper_Logging_Stack_Trace_GeneratorDefaultTestFixtureBuilder */
    protected $fixtureProvider;
    
    /** @var Shopgate_Helper_Logging_Stack_Trace_GeneratorDefault */
    protected $subjectUnderTest;
    
    public function setUp()
    {
        // workaround for PHP versions below 7: load Throwable interface; TODO move to some bootstrap.php or the like
        include_once(dirname(__FILE__) . '/../../../../stubs/Throwable.php');
        
        $this->obfuscator = $this
            ->getMockBuilder('Shopgate_Helper_Logging_Obfuscator')
            ->getMock()
        ;
        
        $this->namedParameterProvider = $this
            ->getMockBuilder('Shopgate_Helper_Logging_Stack_Trace_NamedParameterProviderInterface')
            ->getMockForAbstractClass()
        ;
        
        $this->subjectUnderTest = new Shopgate_Helper_Logging_Stack_Trace_GeneratorDefault(
            $this->obfuscator,
            $this->namedParameterProvider
        );
        
        include_once(dirname(__FILE__)
            . '/../../../../fixtures/helper/logging/stack_trace/GeneratorDefaultTestFixtureBuilder.php');
        $this->fixtureProvider = new Shopgate_Helper_Logging_Stack_Trace_GeneratorDefaultTestFixtureBuilder($this);
    }
    
    public function testSimpleExceptionProducesProperStackTrace()
    {
        $this->obfuscator
            ->expects($this->any())
            ->method('cleanParamsForLog')
            ->withAnyParameters()
            ->willReturnArgument(0)
        ;
        
        $this->namedParameterProvider->expects($this->any())->method('get')->willReturnArgument(2);
        
        $this->assertEquals(
            $this->fixtureProvider->getSimpleExceptionExpected(),
            $this->subjectUnderTest->generate($this->fixtureProvider->getSimpleException()));
    }
    
    public function testExceptionWithPreviousExceptionsProducesProperStackTrace()
    {
        $this->obfuscator
            ->expects($this->any())
            ->method('cleanParamsForLog')
            ->withAnyParameters()
            ->willReturnArgument(0)
        ;
        
        $this->namedParameterProvider->expects($this->any())->method('get')->willReturnArgument(2);
        
        $this->assertEquals(
            $this->fixtureProvider->getExceptionWithPreviousExceptionsExpected(),
            $this->subjectUnderTest->generate($this->fixtureProvider->getExceptionWithPreviousExceptions())
        );
    }
    
    public function testStackTraceGenerationWithMissingFileAndLine()
    {
        $this->obfuscator
            ->expects($this->any())
            ->method('cleanParamsForLog')
            ->withAnyParameters()
            ->willReturnArgument(0)
        ;
        
        $this->namedParameterProvider->expects($this->any())->method('get')->willReturnArgument(2);
        
        $this->assertEquals(
            $this->fixtureProvider->getExceptionWithMissingFileAndLineExpected(),
            $this->subjectUnderTest->generate($this->fixtureProvider->getExceptionWithMissingFileAndLineFixture())
        );
    }
    
    public function testStackTraceGenerationWithMissingClassAndType()
    {
        $this->obfuscator
            ->expects($this->any())
            ->method('cleanParamsForLog')
            ->withAnyParameters()
            ->willReturnArgument(0)
        ;
        
        $this->namedParameterProvider->expects($this->any())->method('get')->willReturnArgument(2);
        
        $this->assertEquals(
            $this->fixtureProvider->getExceptionWithMissingClassAndTypeExpected(),
            $this->subjectUnderTest->generate($this->fixtureProvider->getExceptionWithMissingClassAndTypeFixture())
        );
    }
    
    public function testStackTraceGenerationWithFunction()
    {
        $this->obfuscator
            ->expects($this->any())
            ->method('cleanParamsForLog')
            ->withAnyParameters()
            ->willReturnArgument(0)
        ;
        
        $this->namedParameterProvider->expects($this->any())->method('get')->willReturnArgument(2);
        
        $this->assertEquals(
            $this->fixtureProvider->getExceptionWithMissingFunctionExpected(),
            $this->subjectUnderTest->generate($this->fixtureProvider->getExceptionWithMissingFunctionFixture())
        );
    }
    
    public function testStackTraceGenerationWithArgs()
    {
        $this->obfuscator
            ->expects($this->any())
            ->method('cleanParamsForLog')
            ->withAnyParameters()
            ->willReturnArgument(0)
        ;
        
        $this->namedParameterProvider->expects($this->any())->method('get')->willReturnArgument(2);
        
        $this->assertEquals(
            $this->fixtureProvider->getExceptionWithMissingArgsExpected(),
            $this->subjectUnderTest->generate($this->fixtureProvider->getExceptionWithMissingArgsFixture())
        );
    }
    
    public function testDepthLimitIsHonoured()
    {
        $this->obfuscator
            ->expects($this->any())
            ->method('cleanParamsForLog')
            ->withAnyParameters()
            ->willReturnArgument(0)
        ;
        
        $this->namedParameterProvider->expects($this->any())->method('get')->willReturnArgument(2);
        
        $this->assertEquals(
            $this->fixtureProvider->getExceptionWithPreviousExceptionsDepth2Expected(),
            $this->subjectUnderTest->generate($this->fixtureProvider->getExceptionWithPreviousExceptions(), 2)
        );
    }
    
    public function testNamedParameterProviderAndObfuscatorIsCalled()
    {
        // build arguments for $this->fixtureProvider->get() from ShopgateLibraryException fixture
        list($args1, $args2, $args3, $args4) = $this->fixtureProvider->buildMockFromTraceFixture(
            $this->fixtureProvider->getTraceFixture('ShopgateLibraryExceptionStub')
        );
        
        // build arguments for $this->fixtureProvider->get() from LoginException fixture
        list($args5, $args6, $args7, $args8, $args9) = $this->fixtureProvider->buildMockFromTraceFixture(
            $this->fixtureProvider->getTraceFixture('LoginException')
        );
        
        $this->namedParameterProvider
            ->expects($this->exactly(9))
            ->method('get')
            ->withConsecutive($args1, $args2, $args3, $args4, $args5, $args6, $args7, $args8, $args9)
            ->willReturn(array())
        ;
        
        $this->obfuscator
            ->expects($this->exactly(9))
            ->method('cleanParamsForLog')
            ->willReturn(array())
        ;
        
        $this->subjectUnderTest->generate($this->fixtureProvider->getExceptionExampleForFailedGetCustomer());
    }
    
    public function testIntegrationObfuscation()
    {
        // build arguments for $this->fixtureProvider->get() from ShopgateLibraryException fixture
        list($args1, $args2, $args3, $args4) = $this->fixtureProvider->buildMockFromTraceFixture(
            $this->fixtureProvider->getTraceFixture('ShopgateLibraryExceptionStub')
        );
        
        // build arguments for $this->fixtureProvider->get() from LoginException fixture
        list($args5, $args6, $args7, $args8, $args9) = $this->fixtureProvider->buildMockFromTraceFixture(
            $this->fixtureProvider->getTraceFixture('LoginException')
        );
        
        $this->namedParameterProvider
            ->expects($this->exactly(9))
            ->method('get')
            ->withConsecutive($args1, $args2, $args3, $args4, $args5, $args6, $args7, $args8, $args9)
            ->willReturnOnConsecutiveCalls(
            // for ShopgateLibraryException
                array('user' => 'herp@derp.com', 'pass' => 'herpiderp'), // ShopgatePluginMyCart::getCustomer()
                array(),                                                 // ShopgatePluginApi::getCustomer()
                array('data' => array()),                                // ShopgatePluginApi::handleRequest()
                array('data' => array()),                                // ShopgatePlugin::handleRequest()
                
                // for LoginException
                array('user' => 'herp@derp.com', 'pass' => 'herpiderp'), // User::login()
                array('user' => 'herp@derp.com', 'pass' => 'herpiderp'), // ShopgatePluginMyCart::getCustomer()
                array(),                                                 // ShopgatePluginApi::getCustomer()
                array('data' => array()),                                // ShopgatePluginApi::handleRequest()
                array('data' => array())                                 // ShopgatePlugin::handleRequest()
            )
        ;
        
        // use the real obfuscator as this is an integration test
        $SUT = new Shopgate_Helper_Logging_Stack_Trace_GeneratorDefault(
            new Shopgate_Helper_Logging_Obfuscator(),
            $this->namedParameterProvider
        );
        
        $this->assertEquals(
            $this->fixtureProvider->getExceptionExampleForFailedGetCustomerObfuscationExpected(),
            $SUT->generate($this->fixtureProvider->getExceptionExampleForFailedGetCustomer())
        );
    }
}