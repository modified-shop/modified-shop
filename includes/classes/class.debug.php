<?php
/* -----------------------------------------------------------------------------------------
  $Id: class.debug.php 2172 2011-09-06 17:44:57Z dokuman $

  modified eCommerce Shopsoftware
  http://www.modified-shop.org

  Copyright (c) 2009 - 2013 [www.modified-shop.org]
  -----------------------------------------------------------------------------------------
  based on:
  Debug log class by Franky, Firebug/FirePHP/DeveloperCompanion debugger by DokuMan

  How to use:
  A new $log object is created by default in /includes/application_top.php so debugging
  can be used everywhere in the code.

  Usage #1 (Debug-Log to file)
  put the following expression anywhere in the code to write a variable to /log/log.txt file:
   $log->logfile($variable);

  Usage #2 ("FirePHP/DeveloperCompanion" extension for Firefox)
  This debugging method requires:
   - Firefox 5+
   - Firefox Extension "Firebug" 1.8+
   - Firefox Extension "FirePHP" 0.6+

  put the following expressions anywhere in the code:
   $log->firephp_command('vardump', $_SERVER);
   $log->firephp_command('trace');
   $log->firephp_command('sqltime', $sql_query);

  Released under the GNU General Public License
  ---------------------------------------------------------------------------------------*/

define('DEBUGLOGFILE', DIR_FS_CATALOG.'/log/log.txt'); // log directory with chmod 777
define('FIREPHPFILE', DIR_WS_CLASSES.'class.debug.firephp.php');

/**
 * debug
 *
 * @author Franky
 * @access public
 * log is a logging class
 * Initialize the class so that the data is in a known state.
 * class.debug.php is included in /includes/application_top.php
*/
class debug {

  /**
   * debug::logfile()
   *
   * Usage: put following expression anywhere in the code:
   * $log->logfile($variable);
   * @access public
   * @return void
   */
  public function logfile($data) {

    if (file_exists(DEBUGLOGFILE)) {
      $f = @fopen(DEBUGLOGFILE, 'a+');
    } else {
      $f = @fopen(DEBUGLOGFILE, 'w+');
    }
    flock($f, 2);
    fputs($f, $data."\n");
    flock($f, 3);
    fclose($f);
    chmod(DEBUGLOGFILE, 0777);
  }

  /**
   * debug::GetFirePHP()
   *
   * @access private
   * @return object
   */
  private function GetFirePHP() {

    if (empty($this->firephp)) {
      if (file_exists(FIREPHPFILE)) {
        include (FIREPHPFILE);
        //ob_start();  //activate GZIP in shop administration!
        $this->firephp = FirePHP::getInstance(true);
        return $this->firephp;
      }
    } else {
      return $this->firephp;
    }
  }

  /**
   * debug::firephp_command()
   *
   * Usage: put following expression anywhere in the code:
   * $log->firephp_command('vardump', $_SERVER);
   * $log->firephp_command('trace');
   * $log->firephp_command('sqltime', $sql_query);
   * @access public
   * @return void
   */
  public function firephp_command($command, $variables = array()) {

    $firephp = $this->GetFirePHP();

    if (isset($firephp) && is_object($firephp)) {
      //show all(!) php errors - firephp will grab them to console with $firephp->registerErrorHandler()
      //error_reporting(E_ALL | E_STRICT); //set error_reporting() in application_top.php

      //convert E_WARNING, E_NOTICE, E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE and E_RECOVERABLE_ERROR errors
      //to ErrorExceptions and send all Exceptions to Firebug automatically if desired
      //only from the moment on, $log->firephp_command() is called
      $firephp->registerErrorHandler($throwErrorExceptions = false);
      $firephp->registerExceptionHandler();
      $firephp->registerAssertionHandler($convertAssertionErrorsToExceptions = true, $throwAssertionExceptions = false);

      switch ($command) {

        case 'vardump': {
          try {
            if (!is_array($variables)) {
              $firephp->log('VAR_DUMP: => '.$variables);
            } else {
              $firephp->dump('ARRAY_DUMP',$variables);
            }
          }
          catch (Exception $e) {
            $firephp->error($e);
          }
          break;
        }

        case 'trace': {
          //$firephp->setObjectFilter('debug',array());
          $firephp->trace('FB_BACKTRACE');
          break;
        }

        case 'sqltime': {
          try {
            if (xtc_db_get_server_info() >= '5.0.37') { //Mysql from Version 5.0.37 required for this feature
              //Start mysql profiling before executing a query
              xtc_db_query("SET profiling = 1");
            }

            //Measure the time PHP requires for the SQL-Query
            $phptime_start = microtime(true);
            //for ($i = 1; $i <= 100; $i++) {         //uncomment for 100x SQL iterations
              $sql_result = xtc_db_query($variables);
            //}                                       //uncomment for 100x SQL iterations
            $phptime_end = microtime(true);
            $phptime = round($phptime_end - $phptime_start, 8); //round precision 10^-8

            if (!empty($sql_result)) {
              $firephp->group('MYSQL_DUMP ('. xtc_db_get_server_info().') => '.$sql_result);
              $firephp->info($variables, 'SQL-Query');
              $firephp->info($phptime, 'SQL-Query Time (+PHP Overhead)');

              //Display the measured time, SQL requires for the SQL-Query
              if (xtc_db_get_server_info() >= '5.0.37') {
                $sql_profile = xtc_db_query("SHOW PROFILES"); //precision is 10^-8
                while($sql_time_row = xtc_db_fetch_array($sql_profile)) {
                  $firephp->info($sql_time_row['Duration'], 'SQL-Query Time (-PHP Overhead)');
                  //$firephp->info($sql_time);
                }
                //Show SQL ExPLAIN results (e.g. check for used index usage)
                /*
                $sql_explain = 'EXPLAIN '.$variables;
                $sql_expl_result = xtc_db_query($sql_explain);
                while($sql_explain_row = xtc_db_fetch_array($sql_expl_result)) {
                  $firephp->info($sql_explain_row,'SQL-EXPLAIN');
                }
                */
              }
              //show actual SQL-Result (rows)
              $line = 0;
              while ($sql_row = xtc_db_fetch_array($sql_result)) {
                $firephp->info($sql_row, 'Result #'.++$line);
              }
              $firephp->groupEnd();

            } else {
              throw new Exception('SQL-Error (no result)');
            }
          }
          catch (Exception $e) {
            $firephp->error($e);
          }
          break;
        }
      } // end switch
    } //end isset
  } //end function firephp_command

} //end class
?>