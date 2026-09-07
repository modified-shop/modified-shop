<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/listings/MagnaCompatibleInventoryView.php');

class TemuDeletedView extends MagnaCompatibleInventoryView {

	public function __construct($settings = array()) {
		parent::__construct($settings);
		$this->url['view'] = 'deleted';
		$this->additionalParameters['ONLY_DELETED'] = true;
	}

	public function renderInventoryTable() {
		return parent::renderInventoryTable();
	}
}
