<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/MagnaCompatibleBase.php');

class TemuShippingLabel extends MagnaCompatibleBase {

	public function __construct(&$params) {
		parent::__construct($params);
		$this->resources['url']['mode'] = 'shippinglabel';
	}

	public function process() {
		$sView = isset($_GET['view']) ? $_GET['view'] : 'upload';

		switch ($sView) {
			case 'overview':
				require_once(DIR_MAGNALISTER_MODULES.'temu/shippinglabel/overview.php');
				break;
			case 'upload':
			default:
				require_once(DIR_MAGNALISTER_MODULES.'temu/shippinglabel/upload.php');
				break;
		}
	}
}
