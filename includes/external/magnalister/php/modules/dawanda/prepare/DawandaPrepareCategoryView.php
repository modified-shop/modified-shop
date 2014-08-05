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
 * $Id: DawandaPrepareCategoryView.php 3830 2014-05-06 13:00:00Z tim.neumann $
 *
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/QuickCategoryView.php');

class DawandaPrepareCategoryView extends QuickCategoryView {

	public function __construct($cPath = 0, $settings = array(), $sorting = false, $search = '', $productIDs = array()) {
		if ($search != '') {
			$this->blUseParent = true;
		}
		parent::__construct($cPath, $settings, $sorting, $search, $productIDs);
		//$this->action = array('action' => 'matching');
		if (!isset($_GET['kind']) || ($_GET['kind'] != 'ajax')) {
			$this->simplePrice->setCurrency(getCurrencyFromMarketplace($this->_magnasession['mpID']));
		}
	}

	protected function init() {
		parent::init();

		if (isset($_POST['action']) && ($_POST['action'] == 'uncheckSelection')) {
			MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array (
				'mpID' => $this->_magnasession['mpID'],
				'selectionname' => $this->settings['selectionName'],
				'session_id' => session_id(),
			));
		}

		$this->productIdFilterRegister('ManufacturerFilter', array());
	}

	public function getAdditionalHeadlines() {
	}

	protected function getProductsCountOfCategoryInfo($iId) {

	}

	public function getFunctionButtons() {
		$mmatch = true;

		return '
			<input type="hidden" value="'.$this->settings['selectionName'].'" name="selectionName"/>
			<input type="hidden" value="_" id="actionType"/>
			<table class="right"><tbody>
				<tr>
					<td id="match_settings" rowspan="2" class="textleft inputCell">
						<input id="match_all_rb" type="radio" name="match" value="all" '.($mmatch ? 'checked="checked"' : '').'/>
						<label for="match_all_rb">'.ML_LABEL_ALL.'</label><br />
						<input id="match_notmatched_rb" type="radio" name="match" value="notmatched" '.(!$mmatch ? 'checked="checked"' : '').'/>
						<label for="match_notmatched_rb">'.ML_DAWANDA_LABEL_ONLY_NOT_PREPARED.'</label>
					</td>
					<td class="texcenter inputCell">
						<table class="right"><tbody>
							<tr><td><input type="submit" class="fullWidth ml-button smallmargin" value="'.ML_DAWANDA_BUTTON_PREPARE.'" id="prepare" name="prepare"/></td></tr>
						</tbody></table>
					</td>
					<td>
						<div class="desc" id="desc_man_match" title="'.ML_LABEL_INFOS.'"><span>'.ML_DAWANDA_LABEL_PREPARE.'</span></div>
					</td>
				</tr>
			</tbody></table>
		';

	}

	public function getLeftButtons() {
		return '
			<input type="submit" class="ml-button" value="'.ML_DAWANDA_BUTTON_UNPREPARE.'" id="unprepare" name="unprepare"/><br/>
			<input type="submit" class="ml-button" value="'.ML_DAWANDA_BUTTON_RESET_DESCRIPTION.'" id="reset_description" name="reset_description"/>';
	}
}