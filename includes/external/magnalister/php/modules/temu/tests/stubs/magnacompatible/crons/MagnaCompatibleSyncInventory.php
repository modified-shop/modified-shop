<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

/**
 * Test stub — minimal base class so TemuSyncInventory can be loaded standalone (CLI). The real
 * base builds $data['NewQuantity'] = array('Mode' => 'SET', 'Value' => n) and $data['Price'] in
 * updateItem() and then calls updateCustomFields($data); the tests feed that shape directly.
 */
abstract class MagnaCompatibleSyncInventory {
	protected $mpID = 0;
	protected $config = array();
	public function __construct($mpID, $marketplace, $limit = 100) { $this->mpID = (int)$mpID; }
	protected function calcNewQuantity() { return 0; }
	protected function updateCustomFields(&$data) { }
	protected function uploadItems() { }
	public function process() { }
}
