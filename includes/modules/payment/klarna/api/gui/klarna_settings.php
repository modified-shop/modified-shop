<?php

include("estore.cfg.php"); // Contains eid, secret and host

//Change me before using the test files.
define('KCONFIG', '/var/www/php2/test/config.json');

//Dependencies from http://phpxmlrpc.sourceforge.net/
require_once(DIR_WS_INCLUDES.'external/klarna/api/transport/xmlrpc-3.0.0.beta/lib/xmlrpc.inc');
require_once(DIR_WS_INCLUDES.'external/klarna/api/transport/xmlrpc-3.0.0.beta/lib/xmlrpc_wrappers.inc');

require_once('../Klarna.php');
require_once('../KlarnaExperimental.php');

$KLARNA_HOSTS = array(
    'live' => array(
        'host' => 'LIVE',
        'mode' => 0
    ),

    'beta' => array(
        'host' => 'BETA',
        'mode' => 1
    ),

    'test' => array(
        'host' => 'TESTING',
        'mode' => 2
    )
);
