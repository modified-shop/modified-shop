<?php
/* --------------------------------------------------------------
	payone.php 2013-08-02 mabr
	Gambio GmbH
	http://www.gambio.de
	Copyright (c) 2013 Gambio GmbH
	Released under the GNU General Public License (Version 2)
	[http://www.gnu.org/licenses/gpl-2.0.html]
	--------------------------------------------------------------


	based on:
	(c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
	(c) 2002-2003 osCommerce(ot_cod_fee.php,v 1.02 2003/02/24); www.oscommerce.com
	(C) 2001 - 2003 TheMedia, Dipl.-Ing Thomas Plänkers ; http://www.themedia.at & http://www.oscommerce.at
	(c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com ($Id: ot_cod_fee.php 1003 2005-07-10 18:58:52Z mz $)

	Released under the GNU General Public License
	---------------------------------------------------------------------------------------*/

//define('MODULE_PAYMENT_PAYONE_TEXT_TITLE', 'PayOne');
//define('MODULE_PAYMENT_PAYONE_TEXT_DESCRIPTION', 'PayOne lorem ipsum');
//define('MODULE_PAYMENT_PAYONE_TEXT_INFO', 'PayOne ...');
define('MODULE_PAYMENT_PAYONE_STATUS_TITLE', 'Enable Module');
define('MODULE_PAYMENT_PAYONE_STATUS_DESC', 'Do you want to accept payments through this module?');
define('MODULE_PAYMENT_PAYONE_ALLOWED_TITLE', 'Allowed zones');
define('MODULE_PAYMENT_PAYONE_ALLOWED_DESC', 'Please enter the zones <b>separately</b> which should be allowed to use this module (e.g. AT,DE (leave empty if you want to allow all zones))');
define('MODULE_PAYMENT_PAYONE_ZONE_TITLE', 'Payment zone');
define('MODULE_PAYMENT_PAYONE_ZONE_DESC', 'If a zone is choosen, the payment method will be valid for this zone only.');
define('MODULE_PAYMENT_PAYONE_TMPORDER_STATUS_ID_TITLE', 'Temporary Order Status');
define('MODULE_PAYMENT_PAYONE_TMPORDER_STATUS_ID_DESC', 'Order for not yet completed transactions');
define('MODULE_PAYMENT_PAYONE_ORDER_STATUS_ID_TITLE', 'Set Order Status');
define('MODULE_PAYMENT_PAYONE_ORDER_STATUS_ID_DESC', 'Set the status of orders made with this payment module to this value');
define('MODULE_PAYMENT_PAYONE_SORT_ORDER_TITLE', 'Sort order');
define('MODULE_PAYMENT_PAYONE_SORT_ORDER_DESC', 'Sort order of display. Lowest is displayed first.');
define('MODULE_PAYMENT_PAYONE_LP', '<br/><br/><a target="_blank" href="https://www.payone.com/en/plattform-integration/extensions/modified-shop/"><strong>Create PAYONE account here now.</strong></a>');
