<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // spike only: logs the Klarna Express authorize() result including personal test data
  function klarna_express_spike() {
    if (!defined('MODULE_PAYMENT_KLARNA_EXPRESS_STATUS')
        || MODULE_PAYMENT_KLARNA_EXPRESS_STATUS != 'True'
        || !isset($_SERVER['REQUEST_METHOD'])
        || $_SERVER['REQUEST_METHOD'] != 'POST'
        )
    {
      return array('ok' => false);
    }

    // limit the body to 20 KB
    $body = file_get_contents('php://input', false, null, 0, 20481);
    if ($body === false || $body === '' || strlen($body) > 20480) {
      return array('ok' => false);
    }

    $data = json_decode($body, true);
    if (!is_array($data)) {
      return array('ok' => false);
    }

    require_once(DIR_FS_CATALOG.'includes/classes/class.logger.php');

    $variant = ((isset($data['variant']) && is_string($data['variant'])) ? preg_replace('/[^a-z_]/', '', $data['variant']) : '');
    $kind = ((isset($data['kind']) && is_string($data['kind'])) ? preg_replace('/[^a-z_]/', '', $data['kind']) : '');

    $logger = new LoggingManager(DIR_FS_LOG.'mod_%s_%s.log', 'info', 'error');
    $logger->log('klarna', 'express spike: '.$variant, array(
      'kind' => $kind,
      'result' => ((isset($data['result'])) ? $data['result'] : null),
    ));

    return array('ok' => true);
  }
