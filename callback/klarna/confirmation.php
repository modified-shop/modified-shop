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

include('includes/application_top.php');

require (DIR_WS_INCLUDES.'checkout_requirements.php');

$_SESSION['klarna']['payment_modules'] = 'klarna_checkout.php';
$_SESSION['payment'] = 'klarna_checkout';

if ($_SESSION['cart']->get_content_type() == 'virtual'
    && !isset($_SESSION['shipping'])
    )
{
  $_SESSION['shipping'] = false;
}

xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL'));
