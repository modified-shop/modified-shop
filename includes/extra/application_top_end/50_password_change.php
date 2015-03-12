<?php
  if (isset($_SESSION['customer_password_change']) 
      && basename($PHP_SELF) != FILENAME_ACCOUNT_PASSWORD 
      && basename($PHP_SELF) != FILENAME_LOGOFF
      ) 
  {
    xtc_redirect(xtc_href_link(FILENAME_ACCOUNT_PASSWORD, 'info_message=need_change_pwd', 'SSL'), 'SSL');
  }
?>