<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/errorlog/MagnaCompatibleErrorView.php');

class TemuErrorView extends MagnaCompatibleErrorView {

	public function __construct($settings = array()) {
		$settings['hasImport'] = true;
		$settings['hasOrigin'] = true;
		parent::__construct($settings);
        $this->url['mode'] = 'errorlog';
    }
}
