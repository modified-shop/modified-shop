<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'temu/classes/MLProductListTemuAbstract.php');

class TemuPrepareProductList extends MLProductListTemuAbstract {

	public function __construct() {
		$this->aListConfig[] = array(
			'head'  => array('attributes' => 'class="matched"', 'content' => 'ML_HOOD_LABEL_PREPARED'),
			'field' => array('preparestatusindicator'),
		);
		parent::__construct();
		$this->addDependency('MLProductListDependencyTemuPrepareFormAction', array('selectionname' => $this->getSelectionName()));
		$this->addDependency('MLProductListDependencyTemuPrepareStatusFilter');
	}

	protected function getSelectionName() {
		return 'prepare';
	}
}
