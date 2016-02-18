<?php
  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  /* magnalister v1.0.0 */
  if (function_exists('magnaExecute')) echo magnaExecute('magnaRenderOrderDetails', array('oID' => $oID), array('order_details.php'));
  /* END magnalister */
?>