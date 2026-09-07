<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

class TemuApplyProductList extends MLProductListTemuAbstract {

	public function __construct() {
		parent::__construct();
		$this->addDependency('MLProductListDependencyTemuApplyFormAction');
		$this->addDependency('MLProductListDependencyTemuPrepareStatusFilter');
	}

	protected function getSelectionName() {
		return 'apply';
	}

	protected function buildQuery() {
		$q = parent::buildQuery();
		$q .= " AND p.products_id NOT IN (
			SELECT products_id FROM ".TABLE_MAGNA_TEMU_PREPARE."
			WHERE mpID = '".$this->aMagnaSession['mpID']."'
				AND PrepareType = 'Apply'
				AND Verified = 'OK'
		)";
		return $q;
	}
}
