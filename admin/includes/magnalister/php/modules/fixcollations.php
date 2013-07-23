<?php
/**
 * 888888ba                 dP  .88888.                    dP                
 * 88    `8b                88 d8'   `88                   88                
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b. 
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88 
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88 
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P' 
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * $Id: generictests.php 1381 2011-11-23 20:34:35Z derpapst $
 *
 * (c) 2012 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

function magnaFixCollations() {
	$mySQLVersion = MagnaDB::gi()->fetchOne('SELECT VERSION()');
	if (version_compare($mySQLVersion, '5.0.0', '<')) {
		echo '
			<h2>Fix Collations &mdash; Wrong MySQL Version</h2>
			<p>This script is only for MySQL Version 5 Databases.</p>';
		return;
	}
	
	$tbls = MagnaDB::gi()->getAvailableTables();
	if (empty($tbls)) {
		echo '
			<h2>Fix Collations &mdash; No tables found</h2>
			<p>No tables found...</p>';
		return;
	}
	
	$magnaTables = array();
	
	foreach ($tbls as $tbl) {
		if (strpos($tbl, 'magnalister_') === false) continue;
		$magnaTables[] = $tbl;
	}
	
	$collation = MagnaDB::gi()->fetchRow('
		SELECT `CHARACTER_SET_NAME`, `COLLATION_NAME`
		  FROM `information_schema`.`COLUMNS`
		 WHERE TABLE_SCHEMA=\''.DB_DATABASE.'\' 
		       AND TABLE_NAME=\''.TABLE_PRODUCTS.'\'
		       AND COLUMN_NAME=\'products_model\'
	');
	if (!is_array($collation) || empty($collation)) {
		echo '
			<h2>Fix Collations &mdash; Failed to get default collation</h2>
			<p>The collation for the shop database '.DB_DATABASE.' could not be read.</p>';
		return;
	}
	#echo print_m($collation);
	
	$verbose = isset($_GET['VERBOSE']) && ($_GET['VERBOSE'] == 'true');
	
	echo '
			<h2>Fix Collations &mdash; Processing</h2>
			<p><b>Notice:</b> This process may take a while. Please be patient.</p>';
	flush();
	
	$errors = array();
	$_timer = microtime(true);
	foreach ($magnaTables as $tbl) {
		@set_time_limit(60);
		$res = MagnaDB::gi()->fetchArray('
			SELECT `COLUMN_NAME`, `COLUMN_DEFAULT`, `IS_NULLABLE`, `COLUMN_TYPE`, `CHARACTER_SET_NAME`, `COLLATION_NAME`
			  FROM `information_schema`.`COLUMNS`
			 WHERE TABLE_SCHEMA=\''.DB_DATABASE.'\' 
			       AND TABLE_NAME=\''.$tbl.'\'
			       AND COLLATION_NAME IS NOT NULL
		');
		if (empty($res)) {
			continue;
		}
		$createTable = MagnaDB::gi()->fetchRow('SHOW CREATE TABLE `'.$tbl.'`');
		if ($verbose) {
			echo print_m($createTable['Create Table']);
			echo print_m($res);
			flush();
		}
		foreach ($res as $col) {
			if (($col['COLUMN_DEFAULT'] === null) && ($col['IS_NULLABLE'] == 'NO')) {
				$append = 'NOT NULL';
			} else if (($col['COLUMN_DEFAULT'] === null) && ($col['IS_NULLABLE'] == 'YES')) {
				$append = 'default NULL';
			} else if (($col['COLUMN_DEFAULT'] !== null) && ($col['IS_NULLABLE'] == 'NO')) {
				$append = 'NOT NULL default \''.$col['COLUMN_DEFAULT'].'\'';
			} else if (($col['COLUMN_DEFAULT'] !== null) && ($col['IS_NULLABLE'] == 'YES')) {
				$append = 'default \''.$col['COLUMN_DEFAULT'].'\'';
			} else {
				$append = '';
				$errors[] = 'Unable to determine DEFAULT for table `'.$tbl.'` column '.$col['COLUMN_NAME'].'.';
				if ($verbose) {
					echo var_dump_pre($col);
					echo print_m($createTable['Create Table']);
					flush();
				}
			}
			if (!empty($append)) {
				$query = '
					ALTER TABLE `'.$tbl.'` CHANGE `'.$col['COLUMN_NAME'].'` `'.$col['COLUMN_NAME'].'` '.$col['COLUMN_TYPE'].' 
						CHARACTER SET '.$collation['CHARACTER_SET_NAME'].' COLLATE '.$collation['COLLATION_NAME'].' '.$append;
				if (!MagnaDB::gi()->query($query)) {
					$errors[] = 'Failed to fix table `'.$tbl.'` column '.$col.'.';
				}
				if ($verbose) {
					echo print_m($query);
					flush();
				}
			}
		}
		$query = 'ALTER TABLE `'.$tbl.'` DEFAULT CHARACTER  SET '.$collation['CHARACTER_SET_NAME'].' COLLATE '.$collation['COLLATION_NAME'];
		if (!MagnaDB::gi()->query($query)) {
			$errors[] = 'Failed to fix charset of table `'.$tbl.'.';
		}
		if ($verbose) {
			echo print_m($query).'<br><br>';
			flush();
		}
	}
	$query = 'ALTER DATABASE `'.DB_DATABASE.'` DEFAULT CHARACTER SET '.$collation['CHARACTER_SET_NAME'].' COLLATE '.$collation['COLLATION_NAME'];
	if (!MagnaDB::gi()->query($query)) {
		$errors[] = 'Failed to fix charset of database `'.DB_DATABASE.'.';
	}
	if ($verbose) {
		echo print_m($query).'<br><br>';
		flush();
	}
		
	$time = microtime2human(microtime(true) - $_timer);
	
	if (empty($errors)) {
		echo '
			<h2>Collations fixed</h2>
			<p>All collations have been successfully fixed.</p><p>Time used: '.$time.'</p>';
	} else {
		echo '
			<h2>Error</h2>
			<p>Some errors occured. Please contact the magnalister support team.</p>
			<ul><li>'.implode('</li><li>', $errors).'</li></ul>
			<p>Time used: '.$time.'</p>';
	}
	return;
}

include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_top.php');

magnaFixCollations();

include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_bottom.php');
include_once(DIR_WS_INCLUDES . 'application_bottom.php');
exit();
