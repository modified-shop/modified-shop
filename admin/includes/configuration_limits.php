<?php
/* --------------------------------------------------------------
   $Id: configuration_limits.php 3569 2012-08-30 15:39:18Z web28 $
   (c) 2012 by www.rpa-com.de
   --------------------------------------------------------------*/
   
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

$value_limits['SESSION_LIFE_ADMIN'] = array('min' => 1440, 'max' => 14400);
$value_limits['SESSION_LIFE_CUSTOMERS'] = array('min' => 1440, 'max' => 14400);
$value_limits['WHOS_ONLINE_TIME_LAST_CLICK'] = array('min' => 900, 'max' => 43200);

