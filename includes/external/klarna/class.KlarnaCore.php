<?php
/**
 * Handle setting of paths and encoding in one place
 *
 * PHP Version 5.2
 *
 * @category Payment
 * @package  Klarna_Module_XtCommerce
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */
if (!defined('DIR_KLARNA')) {
    define('DIR_KLARNA', dirname(__FILE__) . '/');
}

// Klarna static server URL
define("EXTERNAL_KITT", "//cdn.klarna.com/public/kitt/");

require_once 'class.xtcFormatter.php';
require_once 'class.KlarnaConstants.php';
require_once 'class.KlarnaInstaller.php';
require_once 'class.KlarnaUninstaller.php';
require_once 'class.KlarnaUtils.php';
require_once 'class.xtcKlarnaDB.php';
require_once 'class.xtcDBResult.php';

// This is to prevent XMLRPC from overwriting a variable that osCommerce uses.
if (isset($i)) {
    $_i = $i;
}

/**
 * Dependencies from {@link http://phpxmlrpc.sourceforge.net/}
 *
 * Ugly include due to problems in XMLRPC lib (external)
 */
require_once DIR_KLARNA . 'api/transport/xmlrpc-3.0.0.beta/lib/xmlrpc.inc';
require_once DIR_KLARNA . 'api/transport/xmlrpc-3.0.0.beta/lib/xmlrpc_wrappers.inc';

// Restore OScommerces variable.
if (isset($_i)) {
    $i = $_i;
}

require_once 'api/Klarna.php';
require_once 'api/pclasses/mysqlstorage.class.php';

require_once 'KITT/classes/KiTT.php';

KiTT_String::$platformEncoding = $_SESSION['language_charset'];

class KlarnaCore {
    public static function getCurrentVersion() {
        return '1.0.7';
    }
}
