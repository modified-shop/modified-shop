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
 * $Id: checkin.php 1174 2011-07-30 17:49:04Z derpapst $
 *
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

class MagnaCompatibleCheckin extends MagnaCompatibleBase {
	
	protected $checkinView = null;
	protected $summaryView = null;
	protected $chechinSubmit = null;

	protected function loadCheckinView() {
		if (
			defined('MAGNA_DEV_PRODUCTLIST') 
			&& MAGNA_DEV_PRODUCTLIST === true 
			&& ($sResource = $this->loadResource('checkin', 'CheckinProductList'))
		) {
			$this->checkinView = $sResource;
		}else{
			$this->checkinView = $this->loadResource('checkin', 'CheckinCategoryView');
		}
	}
	
	protected function loadSummaryView() {
		$this->summaryView = $this->loadResource('checkin', 'SummaryView');
	}

	protected function loadCheckinSubmit() {
		$this->chechinSubmit = $this->loadResource('checkin', 'CheckinSubmit');
	}

	public function process() {
		require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/CheckinManager.php');
		
		$this->loadCheckinView();
		$this->loadSummaryView();
		$this->loadCheckinSubmit();
		
		if (($this->checkinView === false) || ($this->summaryView === false) || ($this->chechinSubmit === false)) {
			if ($this->isAjax) {
				echo '{"error": "This is not supported"}';
			} else {
				echo 'This is not supported';
			}
			return;
		}

		$cm = new CheckinManager(
			array(
				'checkinView'   => $this->checkinView,
				'summaryView'   => $this->summaryView,
				'checkinSubmit' => $this->chechinSubmit
			), array(
				'marketplace' => $this->marketplace,
				'hasPurge' => true,
			)
		);
		echo $cm->mainRoutine();
	}
	
}

