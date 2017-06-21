<?php
/**
 * 888888ba                 dP  .88888.                    dP                
 * 88    `8b                88 d8'   `88                   88                
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b. 
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88 
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88 
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P' 
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * $Id$
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/listingsBox.php');

class GenericListings {
	protected $url = array();
	protected $magnaQuery = array();
	protected $views = array();
	
	public function __construct($views) {
		global $_url, $_magnaQuery;
		
		$this->url = &$_url;
		$this->magnaQuery = $_magnaQuery;
		$this->url['mode'] = $this->magnaQuery['mode'];
		
		$this->views = $views;

	}
	
	public function setView($key, $value) {
		$this->views[$key] = $value;
	}

	public function renderView($_shitHappend, $_checkinState) {
		$html = generateListingsBox();
		
		if ($this->magnaQuery['view'] == 'inventory') {
			$html .= '
				<table class="magnaframe">
					<thead><tr><th>'.ML_LABEL_NOTE.'</th></tr></thead>
					<tbody><tr><td class="fullWidth">
						<p>'.ML_CSHOPPING_TEXT_RECONCILIATION_OF_INVENTORY.'</p>
					</td></tr></tbody>
				</table>';
			
			if (isset($_checkinState)) {
				$html .= '<p class="successBox">'.sprintf(ML_SUCCESS_CS_CHECKIN_ALL, $_checkinState['total']).'</p>';
			}
			
			$iV = new $this->views['InventoryView']();
			$html .= $iV->renderView();
			
		} else if ($this->magnaQuery['view'] == 'deleted') {
			$dV = new $this->views['DeletedView']();
			$html .= $dV->renderView();
			
		} else {
			if ($_shitHappend) {
				if ($_checkinState['success'] == 0) {
					$html .= '<p class="noticeBox">'.sprintf(ML_ERROR_CS_CHECKIN_NONE, $_checkinState['total']).'</p>';
				} else {
					$html .= '<p class="noticeBox">'.sprintf(ML_ERROR_CS_CHECKIN_FEW, $_checkinState['success'], $_checkinState['total']).'</p>';
				}
			}
			
			$eV = new $this->views['ErrorView']();
			$html .= $eV->renderView();
		}
		return $html;
	}
}
