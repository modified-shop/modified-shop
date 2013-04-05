<?php

if (getenv('CODECOVERAGE') || class_exists('PHP_CodeCoverage_Filter')) {
    $filter = PHP_CodeCoverage_Filter::getInstance();
    $filter->addDirectoryToBlacklist('/usr/share/pear');
    $filter->addDirectoryToBlacklist('/usr/share/php');
    $filter->addDirectoryToBlacklist($_SERVER['PWD'].'/phpunit');
    $filter->addDirectoryToBlacklist($_SERVER['PWD'].'/phpunit/data', 'yml');
    $filter->addDirectoryToBlacklist($_SERVER['PWD'].'/transport/xmlrpc-3.0.0.beta/lib', 'inc');
}

