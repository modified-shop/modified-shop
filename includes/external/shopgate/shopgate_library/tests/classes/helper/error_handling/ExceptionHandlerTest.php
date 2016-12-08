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
class Shopgate_Helper_Error_Handling_ExceptionHandlerTest extends PHPUnit_Framework_TestCase
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
    
    public function testStackTraceGeneratorAndLoggerCalledOnShopgateLibraryException()
    {
        $exception = $this
            ->getMockBuilder('ShopgateLibraryException')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        
        $stackTrace = 'Dummy Stack Trace';
        
        $this->stackTraceGenerator
            ->expects($this->once())
            ->method('generate')
            ->with($exception)
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
        
        $SUT = new Shopgate_Helper_Error_Handling_ExceptionHandler($this->stackTraceGenerator, $this->logging);
        $SUT->handle($exception);
    }
    
    public function testStackTraceGeneratorAndLoggerNotCalledOnNonShopgateLibraryException()
    {
        $this->stackTraceGenerator->expects($this->never())->method('generate');
        $this->logging->expects($this->never())->method('log');
        
        $SUT = new Shopgate_Helper_Error_Handling_ExceptionHandler($this->stackTraceGenerator, $this->logging);
        $SUT->handle(new Exception());
    }
}