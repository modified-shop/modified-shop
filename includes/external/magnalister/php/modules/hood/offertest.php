<?php
if (!isset($_MagnaSession)) {
	global $_MagnaSession;
}

$pId = false;
if (isset($_GET['SKU'])) {
	$pId = (int)magnaSKU2PID($_GET['SKU']);
} else if (isset($_GET['PID'])) {
	$pId = (int)$_GET['PID'];
}

require_once(DIR_MAGNALISTER_MODULES.'hood/checkin/HoodCheckinSubmit.php');

if ($pId > 0) {
	$data = HoodCheckinSubmit::loadProductByPId($pId);
	if (!empty($data)) {
		echo print_m(json_indent($data), 'HoodCheckinSubmit::loadProductByPId('.$pId.')');
	} else {
		echo print_m('Not prepared, loading offer.');
	}
	if (empty($data) || ($data['ListingType'] != 'shopProduct')) {
		$data = HoodCheckinSubmit::loadOfferByPId($pId, 'shopProduct');
		echo print_m(json_indent($data), 'HoodCheckinSubmit::loadOfferByPId('.$pId.', shopProduct)');
	}
	
} else {
	echo 'Please specify a PID or a SKU (GET-Params).';
}
