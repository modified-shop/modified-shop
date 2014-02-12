<?php


if(MODULE_PAYMENT_SHOPGATE_STATUS=='True') {

	// determine configuration language: $_GET > $_SESSION > global
	$sg_language_get = (!empty($_GET['sg_language'])
		? '&sg_language='.$_GET['sg_language']
		: ''
	);

##### XTCM BOF #####
	$displayCssClass = 'menuBoxContentLink';
	if (defined('NEW_ADMIN_STYLE')) {
		$surroundingHtml = array(
			'start'	=>	'<li>' .
						'<a class="'.$displayCssClass.'" href="#">-'.BOX_SHOPGATE.'<span style="float:right">&raquo;</span></a>' .
						'<ul>',
			'end'	=>	'</ul></li>',
		);
	} else {
		$surroundingHtml = array(
			'start'	=>	'<li>' .
						'<div class="dataTableHeadingContent"><b>'.BOX_SHOPGATE.'</b></div>' .
						'<ul>',
			'end'	=>	'</ul></li>',
		);
	}
	$surroundingTags = array(
		'start'	=>	'<li>',
		'end'	=>	'</li>',
	);
	$hrefIdList = array(
		'basic'		=>	'',
		'merchant'	=>	'',
	);
	$linkNamePrefix = ' -';
##### XTCM EOF #####
	
	echo ($surroundingHtml['start']);
	
	if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['shopgate'] == '1'))
		echo $surroundingTags['start'].'<a '.$hrefIdList['basic'].'href="' . xtc_href_link(FILENAME_SHOPGATE."?sg_option=info{$sg_language_get}", '', 'NONSSL') . '" class="'.$displayCssClass.'">'.$linkNamePrefix.BOX_SHOPGATE_INFO.'</a>'.$surroundingTags['end'];
	if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['shopgate'] == '1'))
		echo $surroundingTags['start'].'<a '.$hrefIdList['basic'].'href="' . xtc_href_link(FILENAME_SHOPGATE."?sg_option=help{$sg_language_get}", '', 'NONSSL') . '" class="'.$displayCssClass.'">'.$linkNamePrefix.BOX_SHOPGATE_HELP.'</a>'.$surroundingTags['end'];
	if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['shopgate'] == '1'))
		echo $surroundingTags['start'].'<a '.$hrefIdList['basic'].'href="' . xtc_href_link(FILENAME_SHOPGATE."?sg_option=register{$sg_language_get}", '', 'NONSSL') . '" class="'.$displayCssClass.'">'.$linkNamePrefix.BOX_SHOPGATE_REGISTER.'</a>'.$surroundingTags['end'];
	if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['shopgate'] == '1'))
		echo $surroundingTags['start'].'<a '.$hrefIdList['basic'].'href="' . xtc_href_link(FILENAME_SHOPGATE."?sg_option=config{$sg_language_get}", '', 'NONSSL') . '" class="'.$displayCssClass.'">'.$linkNamePrefix.BOX_SHOPGATE_CONFIG.'</a>'.$surroundingTags['end'];
	if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['shopgate'] == '1'))
		echo $surroundingTags['start'].'<a '.$hrefIdList['merchant'].'href="' . xtc_href_link(FILENAME_SHOPGATE."?sg_option=merchant{$sg_language_get}", '', 'NONSSL') . '" class="'.$displayCssClass.'">'.$linkNamePrefix.BOX_SHOPGATE_MERCHANT.'</a>'.$surroundingTags['end'];
	
	echo ($surroundingHtml['end']);

}