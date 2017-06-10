<?php
if (defined('MODULE_ORDER_MAIL_STEP_STATUS')
    && MODULE_ORDER_MAIL_STEP_STATUS == 'true'
    && (!isset($action)
        || $action == 'send_order_mail'
        || !isset($send_by_admin)
        )
    )
{
  $smarty->caching = 0;
  $html_mail = $smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$order->info['language'].'/order_mail_step.html');
  $txt_mail = $smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$order->info['language'].'/order_mail_step.txt');
  
  // create subject
  $order_subject = str_replace('{$nr}', $insert_id, MODULE_ORDER_MAIL_STEP_SUBJECT);
  $order_subject = str_replace('{$date}', xtc_date_long($order->info['date_purchased']), $order_subject);
  $order_subject = str_replace('{$lastname}', $order->customer['lastname'], $order_subject);
  $order_subject = str_replace('{$firstname}', $order->customer['firstname'], $order_subject);
}
?>