<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

// Test stub loader — delegates to the REAL resolver so the shaping under test never drifts
// from production code. (DIR_MAGNALISTER_MODULES points at tests/stubs/ during the CLI run.)
require_once(dirname(__FILE__) . '/../../../../classes/TemuVariantSpecResolver.php');
