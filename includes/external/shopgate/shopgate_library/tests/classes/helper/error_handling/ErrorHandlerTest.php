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
class Shopgate_Helper_Error_Handling_ErrorHandlerTest extends PHPUnit_Framework_TestCase
{
    /** @var PHPUnit_Framework_MockObject_MockObject|Shopgate_Helper_Logging_Stack_Trace_GeneratorInterface */
    protected $stackTraceGenerator;
    
    /** @var PHPUnit_Framework_MockObject_MockObject|Shopgate_Helper_Logging_Strategy_LoggingInterface */
    protected $logging;
    
    public function setUp()
    {
        $this->stackTraceGenerator = $this
            ->getMockBuilder('Shopgate_Helper_Logging_Stack_Trace_GeneratorInterface')
            ->getMock()
        ;
        
        $this->logging = $this
            ->getMockBuilder('Shopgate_Helper_Logging_Strategy_LoggingInterface')
            ->getMock()
        ;
    }
    
    public function testStackTraceGeneratorAndLoggerCalled()
    {
        $stackTrace = 'Dummy Stack Trace';
        
        $this->stackTraceGenerator
            ->expects($this->once())
            ->method('generate')
            ->with(new PHPUnit_Framework_Constraint_Exception('Exception'))
            ->willReturn($stackTrace)
        ;
        
        $this->logging
            ->expects($this->once())
            ->method('log')
            ->with(
                new PHPUnit_Framework_Constraint_IsAnything(), // message
                Shopgate_Helper_Logging_Strategy_LoggingInterface::LOGTYPE_ERROR,
                $stackTrace
            )
            ->willReturn(true)
        ;
        
        $SUT = new Shopgate_Helper_Error_Handling_ErrorHandler($this->stackTraceGenerator, $this->logging);
        $SUT->handle(123, 'a message', '/var/www/failingscript.php', 100, array());
    }
    
    public function testStackTraceGeneratorAndLoggerNotCalledOnErrorSupression()
    {
        $this->stackTraceGenerator
            ->expects($this->never())
            ->method('generate')
        ;
        
        $this->logging
            ->expects($this->never())
            ->method('log')
        ;
        
        $SUT = new Shopgate_Helper_Error_Handling_ErrorHandler($this->stackTraceGenerator, $this->logging);
        $SUT->handle(0, 'a message', '/var/www/failingscript.php', 100, array());
    }
    
    public function testUseInternalErrorHandler()
    {
        // from php.net about the error handling function:
        // "If the function returns FALSE then the normal error handler continues."
        
        // internal error handler should be used by default
        $SUT = new Shopgate_Helper_Error_Handling_ErrorHandler($this->stackTraceGenerator, $this->logging);
        $this->assertFalse($SUT->handle(123, 'a message', '/var/www/failingscript.php', 100, array()));
        
        // internal error handler explicitly used
        $SUT = new Shopgate_Helper_Error_Handling_ErrorHandler($this->stackTraceGenerator, $this->logging, false);
        $this->assertFalse($SUT->handle(123, 'a message', '/var/www/failingscript.php', 100, array()));
        
        // internal error handler disabled
        $SUT = new Shopgate_Helper_Error_Handling_ErrorHandler($this->stackTraceGenerator, $this->logging, true);
        $this->assertTrue($SUT->handle(123, 'a message', '/var/www/failingscript.php', 100, array()));
    }
}