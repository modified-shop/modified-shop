<?php
if (!defined('DS')) define('DS', '/');

if( file_exists(dirname(__FILE__).DS.'dev.php') )
	require_once(dirname(__FILE__).DS.'dev.php');

// Library
require_once(dirname(__FILE__).DS.'classes'.DS.'core.php');
require_once(dirname(__FILE__).DS.'classes'.DS.'apis.php');
require_once(dirname(__FILE__).DS.'classes'.DS.'configuration.php');
require_once(dirname(__FILE__).DS.'classes'.DS.'customers.php');
require_once(dirname(__FILE__).DS.'classes'.DS.'orders.php');
require_once(dirname(__FILE__).DS.'classes'.DS.'items.php');
require_once(dirname(__FILE__).DS.'classes'.DS.'redirect.php');

// Shopgate-Vendors
require_once(dirname(__FILE__).DS.'vendors'.DS.'2d_is.php');
require_once(dirname(__FILE__).DS.'vendors'.DS.'mobile_redirect.class.php');

// External-Vendors
include_once(dirname(__FILE__).DS.'vendors'.DS.'JSON.php');
