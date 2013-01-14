<?php
defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

if(strpos(MODULE_PAYMENT_INSTALLED, 'shopgate.php') !== false){

	echo ('<li>');
	echo ('<div class="dataTableHeadingContent"><b>'.BOX_SHOPGATE.'</b></div>');
	echo ('<ul>');
	if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['shopgate'] == '1'))
		echo '<li><a href="' . xtc_href_link(FILENAME_SHOPGATE."?sg_option=info", '', 'NONSSL') . '" class="menuBoxCon"> -' . BOX_SHOPGATE_INFO . '</a></li>';
	if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['shopgate'] == '1'))
		echo '<li><a href="' . xtc_href_link(FILENAME_SHOPGATE."?sg_option=help", '', 'NONSSL') . '" class="menuBoxCon"> -'.BOX_SHOPGATE_HELP.'</a></li>';
	if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['shopgate'] == '1'))
		echo '<li><a href="' . xtc_href_link(FILENAME_SHOPGATE."?sg_option=register", '', 'NONSSL') . '" class="menuBoxCon"> -'.BOX_SHOPGATE_REGISTER.'</a></li>';
	if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['shopgate'] == '1'))
		echo '<li><a href="' . xtc_href_link(FILENAME_SHOPGATE."?sg_option=config", '', 'NONSSL') . '" class="menuBoxCon"> -'.BOX_SHOPGATE_CONFIG.'</a></li>';
	if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['shopgate'] == '1'))
		echo '<li><a href="' . xtc_href_link(FILENAME_SHOPGATE."?sg_option=config_ext", '', 'NONSSL') . '" class="menuBoxCon"> -'.BOX_SHOPGATE_CONFIG_EXTENDED.'</a></li>';
	if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['shopgate'] == '1'))
		echo '<li><a href="' . xtc_href_link(FILENAME_SHOPGATE."?sg_option=merchant", '', 'NONSSL') . '" class="menuBoxCon"> -'.BOX_SHOPGATE_MERCHANT.'</a></li>';
	
	echo ('</ul>');
	echo ('</li>');

}