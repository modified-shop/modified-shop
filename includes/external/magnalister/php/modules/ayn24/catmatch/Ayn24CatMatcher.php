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
 * $Id: Ayn24CatMatcher.php 2332 2013-04-04 16:12:19Z derpapst $
 *
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

class Ayn24CatMatcher {
	private $_magnasession = array();
	private $mpID = 0;
	
	private $prepareSettings = array();
	
	private $cMDiag = null;
	
	public function __construct($prepareSettings) {
		global $_MagnaSession;
		$this->_magnasession = &$_MagnaSession;
		$this->mpID = $this->_magnasession['mpID'];
		
		$this->prepareSettings = $prepareSettings;
		
		require_once(DIR_MAGNALISTER_MODULES.'ayn24/catmatch/Ayn24CategoryMatching.php');
		$this->cMDiag = new Ayn24CategoryMatching();
	}
	
	private function renderForm() {
		$categories = MagnaDB::gi()->fetchArray('
			SELECT DISTINCT p2c.categories_id
			  FROM '.TABLE_MAGNA_SELECTION.' ms, '.TABLE_PRODUCTS_TO_CATEGORIES.' p2c
			 WHERE ms.mpID=\''.$this->mpID.'\' AND
			       ms.selectionname=\''.$this->prepareSettings['selectionName'].'\' AND
			       ms.session_id=\''.session_id().'\' AND
			       ms.pID=p2c.products_id
		', true);
		$html = $this->cMDiag->renderView() . '
			<table class="datagrid autoOddEven hover">
				<thead>
					<tr><td>'.ML_LABEL_SELECTED_CATEGORIES.'</td></tr>
				</thead>
				<tbody>';
		foreach ($categories as $cID) {
			$html .= '
					<tr><td>
						<ul><li>'.str_replace('<br />', '</li><li>', renderCategoryPath($cID)).'</li></ul>
					</td></tr>';
		}
		$html .= '
				</tbody>
			</table>';
		return $html;
	}
	
	private function renderAjax() {
		return $this->cMDiag->renderAjax();
	}
	
	public function run() {
		if (isset($_GET['kind']) && ($_GET['kind'] == 'ajax')) {
			return $this->renderAjax();
		}
		return $this->renderForm();
	}
	
}