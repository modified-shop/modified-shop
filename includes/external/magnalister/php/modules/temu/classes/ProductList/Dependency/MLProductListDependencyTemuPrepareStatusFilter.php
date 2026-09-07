<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/ProductList/Dependency/MLProductListDependencyPrepareStatusFilter.php');

class MLProductListDependencyTemuPrepareStatusFilter extends MLProductListDependencyPrepareStatusFilter {

	protected function getPrepareCondition() {
		return array(
			'failed' => "AND Verified <> 'OK' AND Verified <> 'EMPTY' ",
			'prepared' => "AND Verified = 'OK' ",
			'notprepared' => "AND Verified != 'EMPTY' ",
		);
	}

	protected function getPrepareTable() {
		return TABLE_MAGNA_TEMU_PREPARE;
	}
}
