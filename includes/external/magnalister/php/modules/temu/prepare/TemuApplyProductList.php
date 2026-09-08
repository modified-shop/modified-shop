<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

class TemuApplyProductList extends MLProductListTemuAbstract {

	public function __construct() {
		parent::__construct();
		$this->addDependency('MLProductListDependencyTemuApplyFormAction', array('selectionname' => $this->getSelectionName()));
		$this->addDependency('MLProductListDependencyTemuPrepareStatusFilter');
	}

	protected function getSelectionName() {
		return 'apply';
	}

	protected function buildQuery() {
		/* buildQuery() returns $this, not an SQL string: concatenating onto it
		 * would stringify the product list (__toString -> init()) and silently
		 * drop the filter. Add the condition to the query object instead. */
		parent::buildQuery()->oQuery->where("
			p.products_id NOT IN (
				SELECT products_id FROM ".TABLE_MAGNA_TEMU_PREPARE."
				WHERE mpID = '".(int)$this->aMagnaSession['mpID']."'
					AND PrepareType = 'Apply'
					AND Verified = 'OK'
			)
		");
		return $this;
	}
}
