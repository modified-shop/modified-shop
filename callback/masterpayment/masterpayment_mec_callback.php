<?php
/******************************************************
 * Masterpayment Modul for modified eCommerce Shopsoftware
 * Version 3.5
 * Copyright (c) 2010-2012 by K-30 | Florian Ressel 
 *
 * support@k-30.de | www.k-30.de
 * ----------------------------------------------------
 *
 * $Id: masterpayment_callback.php 29.04.2012 02:42 $
 *	
 *	The Modul based on:
 *  XT-Commerce - community made shopping
 *  http://www.xt-commerce.com
 *
 *  Copyright (c) 2003 XT-Commerce
 *
 *	Released under the GNU General Public License
 *
 ******************************************************/

chdir('../../');
include('includes/application_top_callback.php');		
require(DIR_FS_EXTERNAL.'masterpayment/MasterpaymentCallback.class.php');	
	
if(isset($_POST) && count($_POST) > 0) 
{	
	$masterpaymentCallback = new MasterpaymentCallback($_POST);		
} else {
	echo 'invalid request';
}

?>