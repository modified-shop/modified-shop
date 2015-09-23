<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

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
    global $error_exceptions, $sql_error, $sql_query;
    
    if (strpos($e->getFile(), 'templates_c') !== false
        || strpos($e->getFile(), 'cache') !== false) return;

    if (!is_array($error_exceptions)) {
      $error_exceptions = array();
    }

    if (is_object($e)) {
        $backtrace = debug_backtrace();
        $error_number = (method_exists($e, 'getseverity') ? $e->getseverity() : 'UNDEFINED_ERROR');
        $error_name = (($error_number != 'UNDEFINED_ERROR') ? error_level($error_number) : 'UNDEFINED_ERROR');
        $error_line = $e->getLine();
        $error_file = $e->getFile();
        $error_message = $e->getMessage();
        $index = md5($error_name.$error_line.$error_file.$error_message);
    
        if (!isset($error_exceptions[$index])) {
            $error_exceptions[$index] = '<table style="width: 1000px; display: inline-block;">' . PHP_EOL .
                                        '  <tr style="color:#000; background-color:#e6e6e6;"><th style="width:100px;">Type</th><td style="width:900px;">'.$error_name.'</td></tr>' . PHP_EOL .
                                        '  <tr style="color:#000; background-color:#F0F0F0;"><th>Message</th><td>'.$error_message.'</td></tr>' . PHP_EOL .
                                        '  <tr style="color:#000; background-color:#e6e6e6;"><th>File</th><td>'.$error_file.'</td></tr>' . PHP_EOL .
                                        '  <tr style="color:#000; background-color:#F0F0F0;"><th>Line</th><td>'.$error_line.'</td></tr>' . PHP_EOL;
                                        $err = 0;
                                        for ($i=0, $n=count($backtrace); $i<$n; $i++) {
                                            if (isset($backtrace[$i]['file']) && $backtrace[$i]['file'] != $error_file && basename($backtrace[$i]['file']) != 'error_reporting.php') {
                                                $error_exceptions[$index] .= '  <tr style="color:#000; background-color:#e6e6e6;"><th>Backtrace #'.$err.'</th><td>'.$backtrace[$i]['file'].' called at Line '.$backtrace[$i]['line'].'</td></tr>' . PHP_EOL;
                                                $err ++;
                                            }
                                        }
            $error_exceptions[$index] .= '</table>' . PHP_EOL .
                                         '<div style="height:1px; border-top:1px dotted #000; margin:10px 0px;"></div>';

            // write Logfile
            if ($error_number != E_NOTICE && $error_number != E_STRICT && $error_number != E_WARNING) {
                error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' ' . $error_name . ' - ' . html_entity_decode($error_message) . ' in File: ' . $error_file . ' on Line: ' . $error_line . "\n", 3, DIR_FS_LOG.'mod_error_' .date('Y-m-d') .'.log');
                $err = 0;
                for ($i=0, $n=count($backtrace); $i<$n; $i++) {
                    if (isset($backtrace[$i]['file']) && $backtrace[$i]['file'] != $error_file && basename($backtrace[$i]['file']) != 'error_reporting.php') {
                        error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' Backtrace #'.$err.' - '.$backtrace[$i]['file'].' called at Line '.$backtrace[$i]['line'] . "\n", 3, DIR_FS_LOG.'mod_error_' .date('Y-m-d') .'.log');
                        $err ++;
                    }
                }
            }
        }
    }
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