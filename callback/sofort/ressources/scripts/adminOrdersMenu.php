<?php
/**
 * @version SOFORT Gateway 5.2.0 - $Date: 2012-09-06 13:49:09 +0200 (Thu, 06 Sep 2012) $
 * @author SOFORT AG (integration@sofort.com)
 * @link http://www.sofort.com/
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * $Id: adminOrdersMenu.php 4307 2013-01-14 07:38:50Z Tomcraft1980 $
 * 
 * Should be included in admin/orders.php, line 734 (ca.)
 */

require_once(dirname(__FILE__).'/../../helperFunctions.php');

if(is_object($oInfo)) {
	$sofortPaymentMethods = array('sofort_sofortueberweisung', 'sofort_sofortvorkasse', 'sofort_sofortrechnung', 'sofort_lastschrift', 'sofort_sofortlastschrift');
	
	if (in_array($oInfo->payment_method, $sofortPaymentMethods)) {
		$contents = array();
		
		switch($oInfo->payment_method) {
			case('sofort_sofortvorkasse'):
				$contents[] = array ('align' => 'left', 'text' => '<div align="center"><span><img src="https://images.sofort.com/de/sv/prepayment_small.png" alt="vorkasse" /></span></div>');
				break;
			case('sofort_sofortrechnung'):
				$contents[] = array ('align' => 'left', 'text' => '<div align="center"><span><img src="https://images.sofort.com/de/sr/logo_155x50.png" alt="Rechnung by sofort" /></span></div>');
				break;
			case('sofort_sofortueberweisung'):
				$contents[] = array ('align' => 'left', 'text' => '<div align="center"><span><img src="https://images.sofort.com/de/su/logo_155x50.png" alt="sofortüberweisung" /></span></div>');
				break;
			case('sofort_lastschrift'):
				$contents[] = array ('align' => 'left', 'text' => '<div align="center"><span><img src="https://images.sofort.com/de/ls/logo_155x50.png" alt="Lastschrift by sofort" /></span></div>');
				break;
			case('sofort_sofortlastschrift'):
				$contents[] = array ('align' => 'left', 'text' => '<div align="center"><span><img src="https://images.sofort.com/de/sl/logo_155x50.png" alt="sofortlastschrift" /></span></div>');
				break;
		}
		
		$shopsystem = HelperFunctions::getIniValue('shopsystemVersion');
		
		switch($shopsystem) {
			case 'xtc3_sp2':
				$contents[] = array ('align' => 'center', 'text' => '<a class="button" href="'.xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array ('oID', 'action')).'oID='.$oInfo->orders_id.'&action=edit').'">'.BUTTON_EDIT.'</a>');
				break;
			case 'cseo_2.0':
				$contents[] = array ('align' => 'center', 'text' => '<br /><a class="button" href="'.xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array ('oID', 'action', 'print_oID')).'oID='.$oInfo->orders_id.'&action=edit').'">'.BUTTON_EDIT.'</a><br /><br />');
				break;
			case 'cseo_2.1':
				$contents[] = array ('align' => 'center', 'text' => '<br /><a class="button" href="'.xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array ('oID', 'action', 'print_oID')).'oID='.$oInfo->orders_id.'&action=edit').'">'.BUTTON_EDIT.'</a><br /><br />');
				break;
			case 'modified_1.06':
				$contents[] = array ('align' => 'center', 'text' => '<a class="button" href="'.xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array ('oID', 'action')).'oID='.$oInfo->orders_id.'&action=edit').'">'.BUTTON_EDIT.'</a>');
				break;
		}
	}
}
?>