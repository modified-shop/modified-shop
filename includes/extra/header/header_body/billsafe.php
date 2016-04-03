<?php
// BILLSAFE PAYMENT MODULE
if (defined('MODULE_PAYMENT_BILLSAFE_2_LAYER') && MODULE_PAYMENT_BILLSAFE_2_LAYER == 'True') {
  $bs_error = '';
  if (basename($PHP_SELF) == 'checkout_payment.php') {
    if (isset($_GET['payment_error'])) {
      $bs_error = stripslashes(html_entity_decode('payment_error='.$_GET['payment_error'].'&error_message='.$_GET['error_message']));
    }
    echo '<script type="text/javascript"><!--' .
         ' if (top.lpg) top.lpg.close("'.str_replace('&amp;', '&', xtc_href_link(FILENAME_CHECKOUT_PAYMENT, $bs_error, 'SSL')).'");' .
         '--></script>' . PHP_EOL;
  }
  if (basename($PHP_SELF) == 'checkout_success.php') {
    echo '<script type="text/javascript"><!--' .
         '  if (top.lpg) top.lpg.close("'.xtc_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL').'");' .
         '--></script>' . PHP_EOL;
  }
}
?>
