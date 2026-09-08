<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleUploadInvoices.php');

class TemuUploadInvoices extends MagnaCompatibleUploadInvoices {

	public function __construct($mpID, $marketplace) {
		parent::__construct($mpID, $marketplace);
	}
}
