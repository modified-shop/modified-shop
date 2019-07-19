<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


$error_files = glob(DIR_FS_CATALOG."export/_error_reporting\.{dev,all,err,shop,admin,none}", GLOB_BRACE);
$LogLevel = mod_get_log_level($error_files);

// include needed class
require_once(DIR_FS_CATALOG.'includes/classes/class.logger.php');
$LoggingManager = new LoggingManager(DIR_FS_LOG.'mod_%s_'.((defined('RUN_MODE_ADMIN')) ? 'admin_' : '').'%s.log', 'modified', strtolower($LogLevel));


/**
 * check for LogLevel
 */
function mod_get_log_level($error_reporting_array) {
  $error_reporting = basename(array_shift($error_reporting_array));
    
  switch ($error_reporting) {
    case '_error_reporting.err':
      $LogLevel = 'ERROR';
      error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_WARNING);
      break;
    case '_error_reporting.shop':
    case '_error_reporting.admin':
      if (($error_reporting == '_error_reporting.admin' && defined('RUN_MODE_ADMIN')) 
          || ($error_reporting == '_error_reporting.shop' && !defined('RUN_MODE_ADMIN'))
          )
      {
        $LogLevel = 'WARNING';
        error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
      } else {
        $LogLevel = mod_get_log_level($error_reporting_array);
      }
      break;
    case '_error_reporting.dev':
      $LogLevel = 'DEBUG';
      error_reporting(-1);
      break;
    case '_error_reporting.none':
      $LogLevel = 'NONE';
      error_reporting(0);
      break;
    default:
      $LogLevel = 'NOTICE';
      error_reporting(E_ALL);
      break;
  }
  
  return $LogLevel;
}

/**
 * Error handler, passes flow over the exception logger with new ErrorException.
 */
function mod_log_error($num, $str, $file, $line, $context=null)
{
    mod_log_exception_handler(new ErrorException($str, 0, $num, $file, $line));
}

/**
 * check if exception is an valid object
 */
function mod_log_exception_handler($e) {
  if (is_object($e)) {
    mod_log_exception($e);
  }
}

/**
 * Uncaught exception handler.
 */
function mod_log_exception($e)
{
    global $error_exceptions, $sql_error, $sql_query, $LoggingManager, $LogLevel;
    
    if (!is_object($LoggingManager)) {
        $LoggingManager = new LoggingManager(DIR_FS_LOG.'mod_%s_'.((defined('RUN_MODE_ADMIN')) ? 'admin_' : '').'%s.log', 'modified', strtolower($LogLevel));
    }
    
    if (strpos($e->getFile(), 'templates_c') !== false
        || strpos($e->getFile(), 'cache') !== false
        || $LogLevel === 'NONE') return;

    if (!is_array($error_exceptions)) {
      $error_exceptions = array();
    }

    if (is_object($e)) {
        $backtrace = debug_backtrace();
        $error = array();
        $error['number'] = (method_exists($e, 'getseverity') ? $e->getseverity() : 'UNDEFINED_ERROR');
        $error['name'] = (($error['number'] != 'UNDEFINED_ERROR') ? mod_error_level($error['number']) : 'ERROR');
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
            $LoggingManager->log($error['name'], html_entity_decode($error['message']) . ' in File: ' . $error['file'] . ' on Line: ' . $error['line']);
            $err = 0;
            for ($i=0, $n=count($backtrace); $i<$n; $i++) {
                if (isset($backtrace[$i]['file']) && $backtrace[$i]['file'] != $error['file'] && basename($backtrace[$i]['file']) != 'error_reporting.php') {
                    $LoggingManager->log($error['name'], 'Backtrace #'.$err.' - '.$backtrace[$i]['file'].' called at Line '.$backtrace[$i]['line']);
                    $err ++;
                }
            }
        }
    }
}

/**
 * Checks for a fatal error, work around for set_error_handler not working on fatal errors.
 */
function mod_check_for_fatal()
{
    $error = error_get_last();
    if ($error['type'] == E_ERROR) {
        mod_log_error($error['type'], $error['message'], $error['file'], $error['line']);
    }
}

/**
 * translate error number.
 */
function mod_error_level($type)
{
    switch($type) {
        case E_ERROR: // 1 //
            return 'ERROR';
        case E_WARNING: // 2 //
            return 'WARNING';
        case E_PARSE: // 4 //
            return 'INFO';
        case E_NOTICE: // 8 //
            return 'NOTICE';
        case E_CORE_ERROR: // 16 //
            return 'ERROR';
        case E_CORE_WARNING: // 32 //
            return 'WARNING';
        case E_CORE_ERROR: // 64 //
            return 'ERROR';
        case E_CORE_WARNING: // 128 //
            return 'WARNING';
        case E_USER_ERROR: // 256 //
            return 'ERROR';
        case E_USER_WARNING: // 512 //
            return 'WARNING';
        case E_USER_NOTICE: // 1024 //
            return 'CUSTOM';
        case E_STRICT: // 2048 //
            return 'INFO';
        case E_RECOVERABLE_ERROR: // 4096 //
            return 'ERROR';
        case E_DEPRECATED: // 8192 //
            return 'DEBUG';
        case E_USER_DEPRECATED: // 16384 //
            return 'DEBUG';
    }
    return $type;
}

/**
 * set error functions.
 */
register_shutdown_function('mod_check_for_fatal');
set_error_handler('mod_log_error');
set_exception_handler('mod_log_exception_handler');
?>