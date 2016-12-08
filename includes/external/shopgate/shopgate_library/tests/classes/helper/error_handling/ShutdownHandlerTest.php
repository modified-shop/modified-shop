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
class Shopgate_Helper_Error_Handling_ShutdownHandlerTest extends PHPUnit_Framework_TestCase
{
    /** @var PHPUnit_Framework_MockObject_MockObject|Shopgate_Helper_Logging_Strategy_LoggingInterface */
    protected $logging;
    
    /** @var PHPUnit_Framework_MockObject_MockObject|Shopgate_Helper_Error_Handling_Shutdown_Handler_LastErrorProvider */
    protected $lastErrorProvider;
    
    public function setUp()
    {
        $this->logging = $this
            ->getMockBuilder('Shopgate_Helper_Logging_Strategy_LoggingInterface')
            ->getMock()
        ;
        
        $this->lastErrorProvider = $this
            ->getMockBuilder('Shopgate_Helper_Error_Handling_Shutdown_Handler_LastErrorProvider')
            ->setMethods(array('get'))
            ->getMock()
        ;
    }
    
    public function testLoggerCalledOnShutdownFatalErrors()
    {
        $this->logging
            ->expects($this->exactly(2))
            ->method('log')
            ->with(
                new PHPUnit_Framework_Constraint_IsAnything(), // message
                Shopgate_Helper_Logging_Strategy_LoggingInterface::LOGTYPE_ERROR,
                ''
            )
            ->willReturnOnConsecutiveCalls(true)
        ;
        
        // E_ERROR
        $this->lastErrorProvider
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturnOnConsecutiveCalls(
                array(
                    'type'    => E_ERROR,
                    'message' => 'Call to member function on a non-object.',
                    'file'    => '/var/www/failing_script.php',
                    'line'    => 99
                ),
                array(
                    'type'    => E_USER_ERROR,
                    'message' => 'Call to member function on a non-object.',
                    'file'    => '/var/www/failing_script.php',
                    'line'    => 99
                )
            )
        ;
        
        $SUT = new Shopgate_Helper_Error_Handling_ShutdownHandler($this->logging, $this->lastErrorProvider);
        $SUT->handle(); // E_ERROR
        $SUT->handle(); // E_USER_ERROR
    }
    
    public function testLoggerNotCalledOnShutdownNonFatalError()
    {
        $this->logging->expects($this->never())->method('log');
        
        $this->lastErrorProvider
            ->expects($this->once())
            ->method('get')
            ->willReturn(
                array(
                    'type'    => E_WARNING,
                    'message' => 'Illegal string offset \'bla\'.',
                    'file'    => '/var/www/failing_script.php',
                    'line'    => 99
                )
            )
        ;
        
        $SUT = new Shopgate_Helper_Error_Handling_ShutdownHandler($this->logging, $this->lastErrorProvider);
        $SUT->handle();
    }
    
    public function testLoggerNotCalledOnRegularShutdownOrErrorGetLastNotAvailable()
    {
        $this->logging->expects($this->never())->method('log');
        
        $this->lastErrorProvider
            ->expects($this->once())
            ->method('get')
            ->willReturn(null)
        ;
        
        $SUT = new Shopgate_Helper_Error_Handling_ShutdownHandler($this->logging, $this->lastErrorProvider);
        $SUT->handle();
    }
}