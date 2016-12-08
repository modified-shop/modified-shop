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
class Shopgate_Helper_Error_Handling_ExceptionHandler
{
    /** @var Shopgate_Helper_Logging_Stack_Trace_GeneratorInterface */
    protected $stackTraceGenerator;
    
    /** @var Shopgate_Helper_Logging_Strategy_LoggingInterface */
    protected $logging;
    
    /**
     * @param Shopgate_Helper_Logging_Stack_Trace_GeneratorInterface $stackTraceGenerator
     * @param Shopgate_Helper_Logging_Strategy_LoggingInterface      $logging
     */
    public function __construct(
        Shopgate_Helper_Logging_Stack_Trace_GeneratorInterface $stackTraceGenerator,
        Shopgate_Helper_Logging_Strategy_LoggingInterface $logging
    ) {
        $this->stackTraceGenerator = $stackTraceGenerator;
        $this->logging             = $logging;
    }
    
    /**
     * Handles uncaught exceptions of type ShopgateLibraryException.
     *
     * This handler will take any Exception or Throwable but will only act upon receiving a ShopgateLibraryException.
     * In that case it will log a stack trace to the error log. In all other cases it will return without doing
     * anything.
     *
     * @param Throwable|Exception $e Will accept Throwable for PHP 7 or Exception for PHP < 7.
     *
     * @see http://php.net/manual/en/function.set-exception-handler.php
     */
    public function handle($e)
    {
        if (!($e instanceof ShopgateLibraryException)) {
            return;
        }
        
        $this->logging->log(
            'FATAL: Uncaught ShopgateLibraryException',
            Shopgate_Helper_Logging_Strategy_LoggingInterface::LOGTYPE_ERROR,
            $this->stackTraceGenerator->generate($e)
        );
    }
}