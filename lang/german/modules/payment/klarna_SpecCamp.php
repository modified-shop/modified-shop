<?php

/**
 * German language controller for Special Campaigns module
 *
 * @category Payment
 * @package  Klarna_Module_XtCommerce
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */

require_once DIR_FS_DOCUMENT_ROOT .
    'includes/external/klarna/class.KlarnaConstantsTranslations.php';

$translator = new KlarnaConstantsTranslations('de');
$translator->translatePaymentModule(KiTT::SPEC);
