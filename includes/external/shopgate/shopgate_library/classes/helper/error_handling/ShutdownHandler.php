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
class Shopgate_Helper_Error_Handling_ShutdownHandler
{
    /** @var Shopgate_Helper_Logging_Strategy_LoggingInterface */
    protected $logging;
    
    /** @var Shopgate_Helper_Error_Handling_Shutdown_Handler_LastErrorProvider */
    protected $lastErrorProvider;
    
    /**
     * @param Shopgate_Helper_Logging_Strategy_LoggingInterface                 $logging
     * @param Shopgate_Helper_Error_Handling_Shutdown_Handler_LastErrorProvider $lastErrorProvider
     */
    public function __construct(
        Shopgate_Helper_Logging_Strategy_LoggingInterface $logging,
        Shopgate_Helper_Error_Handling_Shutdown_Handler_LastErrorProvider $lastErrorProvider
    ) {
        $this->logging           = $logging;
        $this->lastErrorProvider = $lastErrorProvider;
    }
    
    /**
     * Handles errors upon shutdown of PHP.
     *
     * This will look up if a fatal error caused PHP to shut down. If so, the error will be logged to the error log.
     */
    public function handle()
    {
        $error = $this->lastErrorProvider->get();
        
        if ($error === null) {
            return;
        }
        
        if (!($error['type'] & (E_ERROR | E_USER_ERROR))) {
            return;
        }
        
        $this->logging->log(
            'Script stopped due to FATAL error in ' . $error['file'] .
            ' in line ' . $error['line'] .
            ' with message: ' . $error['message']
        );
    }
}