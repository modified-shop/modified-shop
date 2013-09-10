<?php

require_once(DIR_FS_CATALOG.'includes/janolaw/janolaw.php');
$coo_janolaw = new janolaw();
if($coo_janolaw->get_status() == true) {
  $coo_janolaw->get_page_content('agb', true, true, 'checkout-agb');
  $coo_janolaw->get_page_content('datenschutzerklaerung', true, true, 'checkout-datenschutzerklaerung');
  $coo_janolaw->get_page_content('impressum', true, true, 'checkout-impressum');
  $coo_janolaw->get_page_content('widerrufsbelehrung', true, true, 'checkout-widerrufsbelehrung');
}