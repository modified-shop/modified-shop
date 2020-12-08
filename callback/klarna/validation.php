<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

chdir('../../');

// needed define
define('SESSION_FORCE_COOKIE_USE', 'False');

$json = file_get_contents('php://input');
$klarna_data = json_decode($json, true);

include('includes/application_top.php');

require (DIR_WS_INCLUDES.'checkout_requirements.php');

// include needed classes
require_once(DIR_WS_MODULES.'payment/klarna_checkout.php');

if (!isset($_SESSION['klarna'])
    || !array_key_exists('html_snippet', $_SESSION['klarna'])
    )
{
  $messageStack->add_session('shopping_cart', TEXT_KLARNA_CHECKOUT_ERROR);
  xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
}
