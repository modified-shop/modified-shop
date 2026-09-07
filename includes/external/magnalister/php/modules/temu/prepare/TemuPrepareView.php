<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

class TemuPrepareView extends MagnaCompatibleBase {

	protected $catMatch = null;
	protected $prepareSettings = array();

	protected function initCatMatching() {
		$this->catMatch = new TemuCategoryMatching();
	}

	public function process() {
		$this->initCatMatching();
		echo $this->catMatch->renderCategoryAjax();
	}

	public function renderAjax() {
		$this->initCatMatching();
		return $this->catMatch->renderCategoryAjax();
	}
}
