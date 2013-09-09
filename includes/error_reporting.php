<?php

/**
 * Error handler, passes flow over the exception logger with new ErrorException.
 */
function log_error($num, $str, $file, $line, $context=null)
{
    log_exception(new ErrorException($str, 0, $num, $file, $line));
}

/**
 * Uncaught exception handler.
 */
function log_exception(Exception $e)
{
    global $error_exceptions;
    
    if (strpos($e->getFile(), 'templates_c') !== false) return;

    if (!is_array($error_exceptions)) {
      $error_exceptions = array();
    }

    $error_exceptions[] = '<table style="width: 1000px; display: inline-block;">' . PHP_EOL .
                          '  <tr style="color:#000; background-color:rgb(230,230,230);"><th style="width:100px;">Type</th><td style="width:900px;">' . error_level($e->getseverity()) . '</td></tr>' . PHP_EOL .
                          '  <tr style="color:#000; background-color:rgb(240,240,240);"><th>Message</th><td>'.$e->getMessage().'</td></tr>' . PHP_EOL .
                          '  <tr style="color:#000; background-color:rgb(230,230,230);"><th>File</th><td>'.$e->getFile().'</td></tr>' . PHP_EOL .
                          '  <tr style="color:#000; background-color:rgb(240,240,240);"><th>Line</th><td>'.$e->getLine().'</td></tr>' . PHP_EOL .
                          '</table>' . PHP_EOL .
                          '<div style="height:1px; border-top:1px dotted #000; margin:10px 0px;"></div>';

}

/**
 * Checks for a fatal error, work around for set_error_handler not working on fatal errors.
 */
function check_for_fatal()
{
    $error = error_get_last();
    if ($error['type'] == E_ERROR) {
        log_error($error['type'], $error['message'], $error['file'], $error['line']);
    }
}

/**
 * translate error number.
 */
function error_level($type)
{
    switch($type) {
        case E_ERROR: // 1 //
            return 'E_ERROR';
        case E_WARNING: // 2 //
            return 'E_WARNING';
        case E_PARSE: // 4 //
            return 'E_PARSE';
        case E_NOTICE: // 8 //
            return 'E_NOTICE';
        case E_CORE_ERROR: // 16 //
            return 'E_CORE_ERROR';
        case E_CORE_WARNING: // 32 //
            return 'E_CORE_WARNING';
        case E_CORE_ERROR: // 64 //
            return 'E_COMPILE_ERROR';
        case E_CORE_WARNING: // 128 //
            return 'E_COMPILE_WARNING';
        case E_USER_ERROR: // 256 //
            return 'E_USER_ERROR';
        case E_USER_WARNING: // 512 //
            return 'E_USER_WARNING';
        case E_USER_NOTICE: // 1024 //
            return 'E_USER_NOTICE';
        case E_STRICT: // 2048 //
            return 'E_STRICT';
        case E_RECOVERABLE_ERROR: // 4096 //
            return 'E_RECOVERABLE_ERROR';
        case E_DEPRECATED: // 8192 //
            return 'E_DEPRECATED';
        case E_USER_DEPRECATED: // 16384 //
            return 'E_USER_DEPRECATED';
    }
    return $type;
}

/**
 * set error functions.
 */
register_shutdown_function('check_for_fatal');
set_error_handler('log_error');
set_exception_handler('log_exception');
?>