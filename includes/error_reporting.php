<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


$LogEnabled = true;
$error_files = glob(DIR_FS_CATALOG.'export/_error_reporting\.*');
$error_reporting = array_shift($error_files);
switch (basename($error_reporting)) {
  case '_error_reporting.shop':
    $LogLevel = 'INFO';
    error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
    break;
  case '_error_reporting.admin':
    $LogLevel = 'NONE';
    $LogEnabled = false;
    if (defined('RUN_MODE_ADMIN')) {
      $LogLevel = 'INFO';
      error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
    }
    break;
  case '_error_reporting.all':
    $LogLevel = 'FINE';
    error_reporting(E_ALL);
    break;
  case '_error_reporting.err':
    $LogLevel = 'ERROR';
    error_reporting(E_ALL);
    break;
  case '_error_reporting.dev':
    $LogLevel = 'DEBUG';
    error_reporting(-1);
    break;
  default:
    $LogLevel = 'NONE';
    $LogEnabled = false;
    break;
}

$config = array(
  'LogEnabled' => $LogEnabled,
  'SplitLogging' => true,
  'LogLevel' => $LogLevel, // DEBUG, FINE, INFO, WARN, ERROR, CUSTOM
  'LogThreshold' => '2MB',
  'FileName' => DIR_FS_LOG.'mod_error_'.((defined('RUN_MODE_ADMIN')) ? 'admin_' : '').date('Y-m-d') .'.log',
  'FileName.debug' => DIR_FS_LOG.'mod_notice_'.((defined('RUN_MODE_ADMIN')) ? 'admin_' : '').date('Y-m-d') .'.log',
  'FileName.fine' => DIR_FS_LOG.'mod_deprecated_'.((defined('RUN_MODE_ADMIN')) ? 'admin_' : '').date('Y-m-d') .'.log',
  'FileName.info' => DIR_FS_LOG.'mod_strict_'.((defined('RUN_MODE_ADMIN')) ? 'admin_' : '').date('Y-m-d') .'.log',
  'FileName.warning' => DIR_FS_LOG.'mod_warning_'.((defined('RUN_MODE_ADMIN')) ? 'admin_' : '').date('Y-m-d') .'.log',
  'FileName.error' => DIR_FS_LOG.'mod_error_'.((defined('RUN_MODE_ADMIN')) ? 'admin_' : '').date('Y-m-d') .'.log',
  'FileName.custom' => DIR_FS_LOG.'mod_custom_'.((defined('RUN_MODE_ADMIN')) ? 'admin_' : '').date('Y-m-d') .'.log',
);


// include needed class
require_once(DIR_FS_CATALOG.'includes/classes/class.logger.php');
$LoggingManager = new LoggingManager($config);


/**
 * Error handler, passes flow over the exception logger with new ErrorException.
 */
function log_error($num, $str, $file, $line, $context=null)
{
    log_exception_handler(new ErrorException($str, 0, $num, $file, $line));
}

/**
 * check if exception is an valid object
 */
function log_exception_handler($e) {
  if (is_object($e)) {
    log_exception($e);
  }
}

/**
 * Uncaught exception handler.
 */
function log_exception($e)
{
    global $error_exceptions, $sql_error, $sql_query, $LoggingManager, $config;
    
    if (!is_object($LoggingManager)) {
        $LoggingManager = new LoggingManager($config);
    }
    
    if (strpos($e->getFile(), 'templates_c') !== false
        || strpos($e->getFile(), 'cache') !== false
        || $config['LogEnabled'] === false) return;

    if (!is_array($error_exceptions)) {
      $error_exceptions = array();
    }

    if (is_object($e)) {
        $backtrace = debug_backtrace();
        $error = array();
        $error['number'] = (method_exists($e, 'getseverity') ? $e->getseverity() : 'UNDEFINED_ERROR');
        $error['name'] = (($error['number'] != 'UNDEFINED_ERROR') ? error_level($error['number']) : 'UNDEFINED_ERROR');
        $error['line'] = $e->getLine();
        $error['file'] = $e->getFile();
        $error['message'] = $e->getMessage();
        $index = md5($error['name'].$error['line'].$error['file'].$error['message']);
    
        if (!isset($error_exceptions[$error['name']][$index])) {
            $error_exceptions[$error['name']][$index] = '<table style="width: 1000px; display: inline-block;">' . PHP_EOL .
                                                        '  <tr style="color:#000; background-color:#e6e6e6;"><th style="width:100px;">Type</th><td style="width:900px;">'.$error['name'].'</td></tr>' . PHP_EOL .
                                                        '  <tr style="color:#000; background-color:#F0F0F0;"><th>Message</th><td>'.$error['message'].'</td></tr>' . PHP_EOL .
                                                        '  <tr style="color:#000; background-color:#e6e6e6;"><th>File</th><td>'.$error['file'].'</td></tr>' . PHP_EOL .
                                                        '  <tr style="color:#000; background-color:#F0F0F0;"><th>Line</th><td>'.$error['line'].'</td></tr>' . PHP_EOL;
                                                        $err = 0;
                                                        for ($i=0, $n=count($backtrace); $i<$n; $i++) {
                                                            if (isset($backtrace[$i]['file']) && $backtrace[$i]['file'] != $error['file'] && basename($backtrace[$i]['file']) != 'error_reporting.php') {
                                                                $error_exceptions[$error['name']][$index] .= '  <tr style="color:#000; background-color:#e6e6e6;"><th>Backtrace #'.$err.'</th><td>'.$backtrace[$i]['file'].' called at Line '.$backtrace[$i]['line'].'</td></tr>' . PHP_EOL;
                                                                $err ++;
                                                            }
                                                        }
            $error_exceptions[$error['name']][$index] .= '</table>' . PHP_EOL;

            // write Logfile
            $LoggingManager->log(html_entity_decode($error['message']) . ' in File: ' . $error['file'] . ' on Line: ' . $error['line'], $error['name']);
            $err = 0;
            for ($i=0, $n=count($backtrace); $i<$n; $i++) {
                if (isset($backtrace[$i]['file']) && $backtrace[$i]['file'] != $error['file'] && basename($backtrace[$i]['file']) != 'error_reporting.php') {
                    $LoggingManager->log('Backtrace #'.$err.' - '.$backtrace[$i]['file'].' called at Line '.$backtrace[$i]['line'], $error['name']);
                    $err ++;
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
set_exception_handler('log_exception_handler');
?>