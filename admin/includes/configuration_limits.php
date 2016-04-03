<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   (c) 2012 by www.rpa-com.de
   
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
   
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

$value_limits['SESSION_LIFE_ADMIN'] = array('min' => 1440, 'max' => 14400);
$value_limits['SESSION_LIFE_CUSTOMERS'] = array('min' => 1440, 'max' => 14400);
$value_limits['WHOS_ONLINE_TIME_LAST_CLICK'] = array('min' => 900, 'max' => 43200);

$value_limits['MAX_DISPLAY_SPECIAL_PRODUCTS'] = array('min' => 1);
$value_limits['MAX_DISPLAY_SEARCH_RESULTS'] = array('min' => 1);
$value_limits['MAX_DISPLAY_ADVANCED_SEARCH_RESULTS'] = array('min' => 1);

$value_limits['STORE_DB_SLOW_QUERY_TIME'] = array('min' => 0);
$value_limits['STORE_PAGE_PARSE_TIME_THRESHOLD'] = array('min' => 0);
$value_limits['SECURITY_CODE_LENGTH'] = array('min' => 0);

$value_limits['REVIEW_TEXT_MIN_LENGTH'] = array('min' => 0);
