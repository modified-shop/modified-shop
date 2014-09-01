<?php
/**
 * Pass arguments to ajax.
 *
 * PHP Version 5.2
 *
 * @category Payment
 * @package  Klarna_Module_XtCommerce
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */
require 'includes/application_top.php';
require_once DIR_FS_DOCUMENT_ROOT . 'includes/external/klarna/class.KlarnaCore.php';

/**
* Extend KlarnaUtils to get access to protected methods.
*
* @category Payment
* @package  Klarna_Module_XtCommerce
* @author   MS Dev <ms.modules@klarna.com>
* @license  http://opensource.org/licenses/BSD-2-Clause BSD2
* @link     http://integration.klarna.com
*/
class XtcAjax
{
    /**
    * Constructor.
    * Takes information from GET and sends it to KlarnaAjax and
    * the Dispatcher.
    */
    public function __construct()
    {
        $country    = $_GET['country'];
        $option     = str_replace('klarna_box_', '', $_GET['type']);
        if ($option == 'special') {
            $option = 'spec';
        }
        KlarnaUtils::configureKiTT($option);
        KlarnaUtils::configureKlarna($option);

        $dispatcher = KiTT::ajaxDispatcher(
            new KiTT_Addresses(KiTT::api($country)),
            null
        );
        $dispatcher->dispatch();
    }
}

// call the constructor where the wanted things happen.
new XtcAjax();
