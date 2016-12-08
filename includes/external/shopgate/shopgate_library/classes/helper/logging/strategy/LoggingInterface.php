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
interface Shopgate_Helper_Logging_Strategy_LoggingInterface
{
    const LOGTYPE_ACCESS  = 'access';
    const LOGTYPE_REQUEST = 'request';
    const LOGTYPE_ERROR   = 'error';
    const LOGTYPE_DEBUG   = 'debug';
    
    /**
     * Enables logging messages to debug log file.
     */
    public function enableDebug();
    
    /**
     * Disables logging messages to debug log file.
     */
    public function disableDebug();
    
    /**
     * @return bool true if logging messages to debug log file is enabled, false otherwise.
     */
    public function isDebugEnabled();
    
    /**
     * Enables logging the stack trace, if available.
     */
    public function enableStackTrace();
    
    /**
     * Disables logging the stack trace.
     */
    public function disableStackTrace();
    
    /**
     * Logs a message to the according log file.
     *
     * Logging to LOGTYPE_DEBUG only is done after $this->enableDebug() has been called and $this->disableDebug() has not
     * been called after that. The debug log file will be truncated on opening by default. To prevent this call
     * $this->keepDebugLog(true).
     *
     * @param string $msg        The error message.
     * @param string $type       The log type, that would be one of the ShopgateLogger::LOGTYPE_* constants.
     * @param string $stackTrace The stack trace that led to the error, if available.
     *
     * @return bool true on success, false on error.
     */
    public function log($msg, $type = self::LOGTYPE_ERROR, $stackTrace = '');
    
    /**
     * Returns the requested number of lines of the requested log file's end.
     *
     * @param string $type  The log file to be read
     * @param int    $lines Number of lines to return
     *
     * @return string The requested log file content
     * @throws ShopgateLibraryException
     *
     * @see http://tekkie.flashbit.net/php/tail-functionality-in-php
     */
    public function tail($type = self::LOGTYPE_ERROR, $lines = 20);
    
    /**
     * If true the debug log of the last request won't be deleted and additional debug output will be appended
     *
     * @param bool $keep
     */
    public function keepDebugLog($keep);
}