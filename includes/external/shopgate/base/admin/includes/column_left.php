<?php
defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

if(strpos(MODULE_PAYMENT_INSTALLED, 'shopgate.php') !== false){

// determine configuration language: $_GET > $_SESSION > global
$sg_language_get = (!empty($_GET['sg_language'])
	? '&sg_language='.$_GET['sg_language']
	: ''
);

	echo ('<li>');
	echo ('<div class="dataTableHeadingContent"><b>'.BOX_SHOPGATE.'</b></div>');
	echo ('<ul>');
	if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['shopgate'] == '1'))
		echo '<li><a href="' . xtc_href_link(FILENAME_SHOPGATE."?sg_option=info{$sg_language_get}", '', 'NONSSL') . '" class="menuBoxCon"> -' . BOX_SHOPGATE_INFO . '</a></li>';
	if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['shopgate'] == '1'))
		echo '<li><a href="' . xtc_href_link(FILENAME_SHOPGATE."?sg_option=help{$sg_language_get}", '', 'NONSSL') . '" class="menuBoxCon"> -'.BOX_SHOPGATE_HELP.'</a></li>';
	if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['shopgate'] == '1'))
		echo '<li><a href="' . xtc_href_link(FILENAME_SHOPGATE."?sg_option=register{$sg_language_get}", '', 'NONSSL') . '" class="menuBoxCon"> -'.BOX_SHOPGATE_REGISTER.'</a></li>';
	if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['shopgate'] == '1'))
		echo '<li><a href="' . xtc_href_link(FILENAME_SHOPGATE."?sg_option=config{$sg_language_get}", '', 'NONSSL') . '" class="menuBoxCon"> -'.BOX_SHOPGATE_CONFIG.'</a></li>';
	if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['shopgate'] == '1'))
		echo '<li><a href="' . xtc_href_link(FILENAME_SHOPGATE."?sg_option=merchant{$sg_language_get}", '', 'NONSSL') . '" class="menuBoxCon"> -'.BOX_SHOPGATE_MERCHANT.'</a></li>';
	
	echo ('</ul>');
	echo ('</li>');

}