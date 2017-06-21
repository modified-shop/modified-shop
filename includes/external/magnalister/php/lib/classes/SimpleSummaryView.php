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
require_once (DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');

class SimpleSummaryView {
	protected $settings;
	protected $_magnasession;
	protected $mpID = 0;
	protected $marketplace = '';
	protected $selection = array();
	protected $url = array();
	protected $magnaConfig;
	
	protected $simplePrice = null;
	protected $additionalActions = '&nbsp;';
	protected $template = array();
	
	protected $loadedTmpl = array();
	
	private $dbQuery = '';
	private $saveStatus = null;

	/* pagination */
	private $numberofitems = 0;
	private $pages = 0;
	private $currentPage = 0;
	private $offset = 0;
	
	protected $ajaxReply = array();
	
	public function __construct($settings = array()) {
		global $_MagnaSession, $_url, $magnaConfig;
		$this->_magnasession = &$_MagnaSession;

		$this->mpID = $this->_magnasession['mpID'];
		$this->marketplace = $this->_magnasession['currentPlatform'];
		$this->isAjax = isset($_GET['kind']) && ($_GET['kind'] == 'ajax');

		$this->settings = array_merge(array(
			'showCheckboxes'  => true,
			'selectionName'   => 'general',
			'mode'			  => 'normal',
			'itemLimit'       => 20,
			'currency'        => DEFAULT_CURRENCY,
		), $settings);
		
		$this->url = $_url;
		$this->magnaConfig = $magnaConfig;

		$this->updateCurrency();

		$this->simplePrice = new SimplePrice();
		$this->simplePrice->setCurrency($this->settings['currency']);

		/* Verarbeite POST Requests */
		//if (!$this->isAjax) echo print_m($_POST);

		if ($this->isAjax) {
			$_timer = microtime(true);
		}

		$this->updateSelectionState();
		#echo print_m($this->selection, '$this->selection ('.__METHOD__.'['.__LINE__.']'.')');
		$this->processAdditionalPost();
		#echo print_m($this->selection, '$this->selection ('.__METHOD__.'['.__LINE__.']'.')');

		if (!$this->isAjax || isset($_GET['abort'])) {
			$this->loadSelection();

			/* Auswahl-Templates */
			initArrayIfNecessary($this->_magnasession, array($this->mpID, 'checkinTemplate'));
			$this->loadedTmpl = &$this->_magnasession[$this->mpID]['checkinTemplate'];
	
			$this->additionalInitialisation();
			
			/* VorlagenVerwaltung */
			/* Neue Vorlage */
			if (array_key_exists('newTemplate', $_POST)) {
				$tmpl = $this->saveNewTemplate();
				if (!empty($tmpl)) {
					$this->loadedTmpl = $tmpl;
				}

			/* Vorlage ueberschreiben */
			} else if (array_key_exists('oldTemplate', $_POST)) {
				$tmpl = $this->saveOldTemplate();
				if (!empty($tmpl)) {
					$this->loadedTmpl = $tmpl;
				}

			/* Geladene Vorlage aendern */
			} else if (array_key_exists('tmpl', $_POST)) {
				$tmpl = $this->saveLoadedTemplate();
				if (!empty($tmpl)) {
					$this->loadedTmpl = $tmpl;
				}
			}

		} else { // kind == ajax
			if (isset($_POST['reset']) && isset($_POST['limit']) && is_array($_POST['limit'])) {
				$this->resetSelectionAttributes($_POST['reset'], $_POST['limit']);
			}
			$this->ajaxReply['timer'] = microtime2human(microtime(true) -  $_timer);
		}
	}

	public function __destruct() {
		#echo print_m($this->selection, '$this->selection ('.__METHOD__.'['.__LINE__.']'.')');
		if (!empty($this->selection)) {
			$batch = array();
			foreach ($this->selection as $pID => $data) {
				$batch[] = array(
					'pID' => $pID,
					'data' => serialize($data),
					'mpID' => $this->mpID,
					'selectionname' => $this->settings['selectionName'],
					'session_id' => session_id(),
					'expires' => gmdate('Y-m-d H:i:s')
				);
			}
			#echo print_m($batch, '$batch ('.__METHOD__.'['.__LINE__.']'.')');
			MagnaDB::gi()->batchinsert(TABLE_MAGNA_SELECTION, $batch, true);
			unset($batch);
		}
	}
	
	protected function loadSelection() {
		/* Paginierung */
		$this->numberofitems = (int)MagnaDB::gi()->fetchOne('
			SELECT count(*)
			  FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID=\''.$this->mpID.'\' AND
			       selectionname=\''.$this->settings['selectionName'].'\' AND
			       session_id=\''.session_id().'\'
		  GROUP BY selectionname
		');

		$page = (isset($_POST['page']) && ctype_digit($_POST['page'])) ? 
					(int)$_POST['page'] : (
						(isset($_GET['page']) && ctype_digit($_GET['page'])) ? 
							(int)$_GET['page'] : 1
					);

		//echo var_dump_pre($page, '$page');

		$this->pages = ceil($this->numberofitems / $this->settings['itemLimit']);
		$this->currentPage = 1;

		if ($page > $this->pages) {
			$this->currentPage = $this->pages;
		} else if ((1 <= $page) && ($page <= $this->pages)) {
			$this->currentPage = $page;
		}

		$this->offset = ($this->currentPage - 1) * $this->settings['itemLimit'];
		
		$selectionResult = MagnaDB::gi()->query('
			SELECT pID, data
			  FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID=\''.$this->mpID.'\' AND
			       selectionname=\''.$this->settings['selectionName'].'\' AND
			       session_id=\''.session_id().'\'
			 LIMIT '.$this->offset.','.$this->settings['itemLimit'].'
		');

		$this->selection = array();
		while ($row = MagnaDB::gi()->fetchNext($selectionResult)) {
			$this->selection[$row['pID']] = unserialize($row['data']);
		}
	}
	
	protected function updateCurrency() {
		$updateExchangeRate = getDBConfigValue(array($this->marketplace.'.exchangerate', 'update'), $this->mpID, false);
		if (!$updateExchangeRate || ($this->settings['currency'] == DEFAULT_CURRENCY)) {
			return;
		}
		try {
			$result = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'GetExchangeRate',
				'SUBSYSTEM' => 'Core',
				'FROM' => strtoupper(DEFAULT_CURRENCY),
				'TO' => strtoupper($this->settings['currency']),
			));
			#echo print_m($result, '$result');
			if ($result['EXCHANGERATE'] > 0) {
				require_once (DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');
				$sp = new SimplePrice();
				$sp->setCurrency($this->settings['currency'])->updateCurrency($result['EXCHANGERATE']);
				#echo print_m($sp, '$sp');
			}
		} catch (MagnaException $e) { }
	}

	protected function loadItemToSelection($pID) {
		$selectionResult = MagnaDB::gi()->query('
			SELECT pID, data
			  FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID=\''.$this->mpID.'\' AND
			       selectionname=\''.$this->settings['selectionName'].'\' AND
			       session_id=\''.session_id().'\' AND
			       pID=\''.$pID.'\'
			 LIMIT 1
		');

		while ($row = MagnaDB::gi()->fetchNext($selectionResult)) {
			$this->selection[$row['pID']] = unserialize($row['data']);
		}
		MagnaDB::gi()->freeResult($selectionResult);
	}

	private function updateSelectionState() {
		if (array_key_exists('tmplcb', $_POST) && is_array($_POST['tmplcb']) && !empty($_POST['tmplcb'])) {
			/* selected in data speichern */
			$pIDs = array_keys($_POST['tmplcb']);
			$itemsResult = MagnaDB::gi()->query('
				SELECT pID, data
				  FROM '.TABLE_MAGNA_SELECTION.'
				 WHERE pID IN (\''.implode('\', \'', $pIDs).'\') AND
				       mpID=\''.$this->mpID.'\' AND
				       selectionname=\''.$this->settings['selectionName'].'\' AND
				       session_id=\''.session_id().'\'
			');
			while ($row = MagnaDB::gi()->fetchNext($itemsResult)) {
				$row['data'] = unserialize($row['data']);
				$row['data']['selected'] = ($_POST['tmplcb'][$row['pID']] == 'true') ? true : false;
				//echo var_dump_pre($row['data']['selected'], $row['pID']);
				MagnaDB::gi()->insert(TABLE_MAGNA_SELECTION, array (
					'pID' => $row['pID'],
					'data' => serialize($row['data']),
					'mpID' => $this->mpID,
					'selectionname' => $this->settings['selectionName'],
					'session_id' => session_id(),
					'expires' => gmdate('Y-m-d H:i:s')
				), true);
			}
			MagnaDB::gi()->freeResult($itemsResult);
		}
	}
	
	protected function processAdditionalPost() {
		// Macht nichts. Wird von erbenden Klassen ueberlagert falls noetig
	}
	
	public function prepareAllProductsForSubmit() {
		MagnaDB::gi()->delete(
			TABLE_MAGNA_SELECTION, 
			array(
				'mpID' => $this->mpID,
				'selectionname' => $this->settings['selectionName'],
				'session_id' => session_id()
			),
			'AND data LIKE \'%s:8:"selected";b:0;%\''
		);
		/* Auch aus der Selektion loeschen, sonst werden die bei __desctruct wieder eingetragen */
		if (!empty($this->selection)) {
			foreach ($this->selection as $pID => $data) {
				if (isset($data['selected']) && !$data['selected']) {
					unset($this->selection[$pID]);
					continue;
				}
			}
		}

		$limit = 100;
		$offset = 0;
		$itemsInserted = false;

		for (; $offset < $this->numberofitems; $offset += $limit) {
			$selectionResult = MagnaDB::gi()->query('
				SELECT pID, data
				  FROM '.TABLE_MAGNA_SELECTION.'
				 WHERE mpID=\''.$this->mpID.'\' AND
				       selectionname=\''.$this->settings['selectionName'].'\' AND
				       session_id=\''.session_id().'\'
			  ORDER BY pID ASC
				 LIMIT '.$offset.','.$limit.'
			');
			$tmplSelection = array();

			while ($row = MagnaDB::gi()->fetchNext($selectionResult)) {
				$row['data'] = unserialize($row['data']);

				/* Get rid of Selection */
				unset($row['data']['selected']);
				
				$this->extendProductAttributes($row['pID'], $row['data']);
				$tmplSelection[] = array (
					'pID' => $row['pID'],
					'data' => serialize($row['data']),
					'mpID' => $this->mpID,
					'selectionname' => $this->settings['selectionName'],
					'session_id' => session_id(),
					'expires' => gmdate('Y-m-d H:i:s')
				);
			}
			if (!empty($tmplSelection)) {
				MagnaDB::gi()->batchinsert(TABLE_MAGNA_SELECTION, $tmplSelection, true);
			}
			unset($tmplSelection);
		}
	}

	private function getNumberOfDeselectedItems() {
		return (int)MagnaDB::gi()->fetchOne('
			SELECT count(*)
			  FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID=\''.$this->mpID.'\' AND
			       selectionname=\''.$this->settings['selectionName'].'\' AND
			       session_id=\''.session_id().'\' AND
			       data LIKE \'%s:8:"selected";b:0;%\'
		  GROUP BY selectionname
		');	
	}
	
	private function saveTemplateEntries($tID) {
		$limit = 100;
		$offset = 0;
		$itemsInserted = false;

		for (; $offset < $this->numberofitems; $offset += $limit) {
			$selectionResult = MagnaDB::gi()->query('
				SELECT ms.pID, ms.data, p.products_model
				  FROM '.TABLE_MAGNA_SELECTION.' ms, '.TABLE_PRODUCTS.' p
				 WHERE ms.mpID=\''.$this->mpID.'\' AND
				       ms.selectionname=\''.$this->settings['selectionName'].'\' AND
				       ms.session_id=\''.session_id().'\' AND
				       ms.pID=p.products_id
			  ORDER BY ms.pID ASC
				 LIMIT '.$offset.','.$limit.'
			');
			$tmplSelection = array();
			while ($row = MagnaDB::gi()->fetchNext($selectionResult)) {
				$row['data'] = unserialize($row['data']);
				if ( !isset($row['data']['selected']) || ($row['data']['selected'] == true) ) {
					unset($row['data']['selected']);
					$this->extendProductAttributes($row['pID'], $row['data']);
					$tmplSelection[] = array (
						'tID' => $tID,
						'pID' => $row['pID'],
						'products_model' => $row['products_model'],
						'data' => serialize($row['data']),
					);
				}
			}
			MagnaDB::gi()->freeResult($selectionResult);
			if (!empty($tmplSelection)) {
				MagnaDB::gi()->batchinsert(TABLE_MAGNA_SELECTION_TEMPLATE_ENTRIES, $tmplSelection, true);
				$itemsInserted = true;
			}
		}
		return $itemsInserted;
	}
	
	private function saveNewTemplate() {
		if (($templateTitle = trim($_POST['saveTemplate']['templateName'])) == '') {
			$this->saveStatus = '<p class="noticeBox">'.ML_LABEL_TITLE_FOR_TEMPLATE.'</p>';
			return array();
		}

		if (MagnaDB::gi()->recordExists(
				TABLE_MAGNA_SELECTION_TEMPLATES,
				array(
					'mpID' => $this->mpID,
					'title' => $templateTitle
				)
			)
		) {
			$this->saveStatus = '<p class="noticeBox">'.ML_LABEL_TEMPLATE_EXISTS.'</p>';
			return array();
		}
		
		if ($this->getNumberOfDeselectedItems() >= $this->numberofitems) {
			$this->saveStatus = '<p class="noticeBox">'.ML_LABEL_CANT_SAVE_TEMPLATE_BC_NO_PROD.'</p>';
			return array();
		}
		
		$tmpl = array();
		$tmpl['title'] = trim($_POST['saveTemplate']['templateName']);
		$tmpl['mpID'] = $_POST['saveTemplate']['mpID'];

		MagnaDB::gi()->insert(TABLE_MAGNA_SELECTION_TEMPLATES, array(
			'title' => $tmpl['title'],
			'mpID' => $tmpl['mpID']
		));

		$tmpl['tID'] = MagnaDB::gi()->getLastInsertID();

		if ($this->saveTemplateEntries($tmpl['tID'])) {
			$this->saveStatus = '<p class="successBox">'.sprintf(
				ML_LABEL_TEMPLATE_X_SAVED, 
				trim($_POST['saveTemplate']['templateName'])
			).'</p>';		
			
			return $tmpl;

		} else {
			$this->saveStatus = '<p class="noticeBox">'.ML_UNKOWN_ERROR.'</p>';
			return array();
		}
	}

	private function saveOldTemplate() {
		if ($this->getNumberOfDeselectedItems() >= $this->numberofitems) {
			$this->saveStatus = '<p class="noticeBox">'.ML_LABEL_CANT_SAVE_TEMPLATE_BC_NO_PROD.'</p>';
			return array();
		}
		$tmpl = MagnaDB::gi()->fetchRow('
			SELECT * FROM '.TABLE_MAGNA_SELECTION_TEMPLATES.' 
			 WHERE tID=\''.MagnaDB::gi()->escape($_POST['saveTemplate']['selectTemplate']).'\'
		');

		if (!is_array($tmpl) || empty($tmpl)) {
			$this->saveStatus = '<p class="noticeBox">'.ML_UNKOWN_ERROR.'</p>';
			return array();
		}

		MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION_TEMPLATE_ENTRIES, array(
			'tID' => $tmpl['tID']
		));
		if ($this->saveTemplateEntries($tmpl['tID'])) {
			$this->saveStatus = '<p class="successBox">'.sprintf(ML_LABEL_TEMPLATE_X_OVERWRITTEN, $tmpl['title']).'</p>';
			return $tmpl;

		} else {
			$this->saveStatus = '<p class="noticeBox">'.ML_UNKOWN_ERROR.'</p>';
			return array();
		}
	}

	private function saveLoadedTemplate() {
		if ($this->getNumberOfDeselectedItems() >= $this->numberofitems) {
			$this->saveStatus = '<p class="noticeBox">'.ML_LABEL_CANT_SAVE_TEMPLATE_BC_NO_PROD.'</p>';
			return array();
		}

		$tmpl = array();
		$tmpl['tID'] = $this->loadedTmpl['tID'];
		$tmpl['title'] = trim($_POST['tmpl']['title']);
		$tmpl['mpID'] = $this->loadedTmpl['mpID'];
		
		if ($tmpl['title'] == '') {
			$this->saveStatus = '<p class="noticeBox">'.ML_LABEL_TITLE_MAY_NOT_BE_EMPTY.'</p>';
			return array();
		}
		
		MagnaDB::gi()->update(
			TABLE_MAGNA_SELECTION_TEMPLATES,
			array(
				'title' => $tmpl['title']
			),
			array(
				'tID' => $tmpl['tID']
			)
		);

		MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION_TEMPLATE_ENTRIES, array(
			'tID' => $tmpl['tID']
		));
		
		if ($this->saveTemplateEntries($tmpl['tID'])) {
			$this->saveStatus = '<p class="successBox">'.sprintf(ML_LABEL_TEMPLATE_X_OVERWRITTEN, $tmpl['title']).'</p>';
			return $tmpl;

		} else {
			$this->saveStatus = '<p class="noticeBox">'.ML_UNKOWN_ERROR.'</p>';
			return array();
		}		
		return array();
	}

	protected function setupQuery($addFields = '', $addFrom = '', $addWhere = '') {
		$this->dbQuery = '
		    SELECT p.products_id, p.products_price, p.products_image, p.products_quantity, p.products_tax_class_id, 
		           p.products_model,
		           pd.products_name
		           '.(($addFields != '') ? (', '.$addFields.' ') : ' ').'
		      FROM '.TABLE_PRODUCTS.' p
		INNER JOIN '.TABLE_PRODUCTS_DESCRIPTION.' pd ON (
		                p.products_id = pd.products_id 
		                AND pd.language_id = \''.$_SESSION['languages_id'].'\' 
		           )
		'.$addFrom.'
		     WHERE p.products_id IN (\''.implode('\', \'', array_keys($this->selection)).'\') 
		           '.$addWhere.' 
		     ORDER BY pd.products_name ASC';
		//echo print_m($this->dbQuery, '$this->dbQuery');
	}
	
	protected function provideResetFunction($headline, $resetItem, $formatFunction = '') {
		if (empty($resetItem)) {
			trigger_error(__METHOD__.': $resetItem may not be empty', E_USER_ERROR);
		}
		$headline = explode(' ', $headline);
		$line = '';
		$inTag = false;
		foreach ($headline as $key => &$word) {
			if ((strpos($word, '<') === 0) && (strrpos($word, '>') !== (strlen($word) - 1))) {
				$inTag = true;
			}
			if ((strpos($word, '<') !== 0) && (strrpos($word, '>') === (strlen($word) - 1))) {
				$inTag = false;
				$word = $line.' '.$word;
				$line = '';
			}
			
			if ($inTag) {
				$line .= $word;
				unset($headline[$key]);
			}
		}
		array_push($headline, '<span class="nowrap">'.array_pop($headline));
		$html = implode(' ', $headline).'&nbsp;<span class="gfxbutton small refresh" id="reset_'.$resetItem.'" title="'.ML_LABEL_REFRESH.'"></span></span>
			<div id="errordiag" class="dialog2" title="'.ML_ERROR_LABEL.'"></div>';
		
		if (!empty($formatFunction) && (strpos($formatFunction, '#VAL#') !== false)) {
			$formatFunction = str_replace('#VAL#', 'data.changedData[pID][resetItem]', $formatFunction);
		} else {
			$formatFunction = 'data.changedData[pID][resetItem]';
		}
		
		ob_start();
?>
<script type="text/javascript">/*<![CDATA[*/
var <?php echo $resetItem; ?>ResetBatchRuns = false;

function run<?php echo ucfirst($resetItem); ?>ResetBatch(resetItem, limit) {
	jQuery.ajax({
		type: 'post',
		url: '<?php echo toURL($this->url, array('kind' => 'ajax'), true); ?>',
		data: {
			'reset': resetItem,
			'limit': limit,
			'page': '<?php echo $this->currentPage; ?>'
		},
		success: function(data) {
			if (!is_object(data)) {
				return;
			}
			if (data.error !== false) {
				jQuery.unblockUI();
				<?php echo $resetItem; ?>ResetBatchRuns = false;
				$('#errordiag').html(data.error).jDialog();
			} else {
				for (var pID in data.changedData) {
					$('#'+resetItem+'_'+pID).val(<?php echo $formatFunction; ?>);
					$('#old_'+resetItem+'_'+pID).val(data.changedData[pID][resetItem]);
				}
				if (data.proceed) {
					run<?php echo ucfirst($resetItem); ?>ResetBatch(resetItem, data.limit);
				} else {
					jQuery.unblockUI();
					<?php echo $resetItem; ?>ResetBatchRuns = false;
				}
			}
		},
		dataType: 'json'
	});
}

$(document).ready(function() {
	$('#reset_<?php echo $resetItem; ?>').click(function() {
		if (!<?php echo $resetItem; ?>ResetBatchRuns) {
			jQuery.blockUI(blockUILoading);
			run<?php echo ucfirst($resetItem); ?>ResetBatch('<?php echo $resetItem; ?>', [0, 100]);
			<?php echo $resetItem; ?>ResetBatchRuns = true;
		}
	});
});
/*]]>*/</script>
<?php
		$html .= ob_get_contents();
		ob_end_clean();
		return $html;
	}
	
	protected function resetSelectionAttributes($reset, $limit) {
		$limit[0] = (int)$limit[0];
		$limit[1] = (int)$limit[1];
		
		if ($limit[1] <= 0) {
			$this->ajaxReply['proceed'] = false;
			$this->ajaxReply['error'] = 'Invalid LIMIT parameter';
			return;
		}
	
		$itemsResult = MagnaDB::gi()->query('
			SELECT pID, data
			  FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID=\''.$this->mpID.'\' AND
			       selectionname=\''.$this->settings['selectionName'].'\' AND
			       session_id=\''.session_id().'\'
			 LIMIT '.$limit[0].','.$limit[1].'
		');
		$fetchedElements = 0;
		while ($row = MagnaDB::gi()->fetchNext($itemsResult)) {
			++$fetchedElements;

			$row['data'] = unserialize($row['data']);

			if (!array_key_exists($reset, $row['data'])) {
				$this->ajaxReply['skiped'][] = $row['pID'];
				continue;
			}
			unset($row['data'][$reset]);
			$this->extendProductAttributes($row['pID'], $row['data']);
			
			$this->ajaxReply['changedData'][$row['pID']][$reset] = $row['data'][$reset];

			MagnaDB::gi()->insert(TABLE_MAGNA_SELECTION, array (
				'pID' => $row['pID'],
				'data' => serialize($row['data']),
				'mpID' => $this->mpID,
				'selectionname' => $this->settings['selectionName'],
				'session_id' => session_id(),
				'expires' => gmdate('Y-m-d H:i:s')
			), true);
		}
		$this->ajaxReply['proceed'] = $fetchedElements >= $limit[1];
		$this->ajaxReply['error'] = false;
		$this->ajaxReply['limit'] = array($limit[0] + $limit[1], $limit[1]);
	}
	
	public function renderSelection() {
		if ($this->dbQuery == '') {
			$this->setupQuery();
		}
		$selectedProductsDetails = MagnaDB::gi()->fetchArray(
			$this->dbQuery
		);

		$infoText = $this->getTopInfoBox();
		$html = '
		'.(!empty($infoText) ? '<p class="successBox">'.$infoText.'</p>' : '').'
		<form action="'.toURL($this->url).'" method="POST" id="summaryForm">
			<table class="ml-pagination"><tbody><tr>
				<td class="ml-pagination">
					<span class="bold">'.ML_LABEL_CURRENT_PAGE.' &nbsp;&nbsp; '.$this->currentPage.'</span>
				</td>
				<td class="textright">
					'.renderPagination($this->currentPage, $this->pages, $this->url, 'submit').'
				</td>
			</tr></tbody></table>
			<table class="datagrid"><thead>
				<tr>
					<td class="smallCell edit"'.($this->settings['showCheckboxes'] ? ' colspan="2"' : '').'>
						'.($this->settings['showCheckboxes'] ? 
							('<input type="checkbox" id="selectAll" checked="checked"/><label for="selectAll">'.ML_LABEL_CHOICE.'</label>') : 
							''
						).'
					</td>
					<td>'.ML_LABEL_PRODUCT_NAME.'<br /><span class="lighter">'.ML_LABEL_ART_NR.'</span></td>
					<td>'.ML_LABEL_CATEGORY_PATH.'</td>
					<td>'.ML_LABEL_SHOP_PRICE.'</td>
					'.$this->getAdditionalHeadlines().'
				</tr>
			</thead>
			<tbody>';
		$isOdd = true;
		foreach ($selectedProductsDetails as $key => $prod) {
			$this->simplePrice->setPrice($prod['products_price']);
			if (getDBConfigValue(array($this->marketplace.'.price.usespecialoffer', 'val'), $this->mpID, false)) {
				$specialPrice = $this->simplePrice->getSpecialOffer($prod['products_id']);
			} else {
				$specialPrice = 0;
			}
			$html .= '
				<tr class="'.(($isOdd = !$isOdd) ? 'odd' : 'even').'">
					'.($this->settings['showCheckboxes'] ? '<td class="edit">
						<input type="hidden" name="tmplcb['.$prod['products_id'].']" value="false"/>
						<input type="checkbox" name="tmplcb['.$prod['products_id'].']" value="true" '.
							((
								isset($this->selection[$prod['products_id']]['selected']) && 
								($this->selection[$prod['products_id']]['selected'] == false)
							) ? '' : 'checked="checked"').
						'/>
					</td>' : '').'
					<td class="image">'.generateProductCategoryThumb($prod['products_image'], 20, 20).'</td>
					<td>
						'.fixHTMLUTF8Entities($prod['products_name']).$this->getAdditionalProductNameStuff($prod).'<br />
						<span class="artNr">'.ML_LABEL_ART_NR_SHORT.': '.(!empty($prod['products_model']) ? $prod['products_model'] : '&mdash;').'</span>
					</td>
					<td><ul><li>'.preg_replace('/<br[^>]*>/', '</li><li>', renderCategoryPath($prod['products_id'], 'product')).'</li></ul></td>
					<td>
						<table class="nostyle"><tbody>
							<tr><td'.(($specialPrice > 0) ? ' rowspan="2"' : '').'>'.ML_LABEL_BRUTTO.':&nbsp;</td>
								<td class="textright'.(($specialPrice > 0) ? ' strike' : '').'">
									'.str_replace(' ', '&nbsp;',
										$this->simplePrice->setPrice($prod['products_price'])->addTaxByTaxID($prod['products_tax_class_id'])->format(true)
									).'
								</td>
								'.(($specialPrice > 0) 
									? '</tr><tr>
										<td class="textright'.(($specialPrice > 0) ? ' offer' : '').'">
											'.str_replace(' ', '&nbsp;',
												$this->simplePrice->setPrice($specialPrice)->addTaxByTaxID($prod['products_tax_class_id'])->format(true)
											).'
										</td>'
									: ''
								).'
							</tr>
							<tr><td>'.ML_LABEL_NETTO.':&nbsp;</td><td class="textright">'.str_replace(' ', '&nbsp;', 
								$this->simplePrice->removeTaxByTaxID($prod['products_tax_class_id'])->format(true)
							).'</td></tr>
						</tbody></table>
					</td>
					'.$this->getAdditionalItemCells($key, $prod).'
				</tr>
			';
		}
		
		$html .= '
			</tbody></table>
			<table class="ml-pagination listingInfo"><tbody><tr>
				<td class="ml-pagination">
					<span class="bold">'.ML_LABEL_CURRENT_PAGE.' &nbsp;&nbsp; '.$this->currentPage.'</span>
				</td>
				<td class="textright">
					'.renderPagination($this->currentPage, $this->pages, $this->url, 'submit').'
				</td>
			</tr></tbody></table>
			'.$this->saveStatus;
		
		$templates = MagnaDB::gi()->fetchArray('
			SELECT * FROM `'.TABLE_MAGNA_SELECTION_TEMPLATES.'`
			 WHERE mpID=\''.$this->mpID.'\'
		');
		$tplSelect = '';
		if (!empty($templates)) {
			$tplSelect .= '<tr>
				<td><label>'.ML_LABEL_OVERWRITE_OLD_TEMPLATE.'</label></td>
				<td><select name="saveTemplate[selectTemplate]">';
				
			foreach ($templates as $template) {
				$tplSelect .= '<option value="'.$template['tID'].'"'.(
					(isset($this->loadedTmpl['tID']) && ($this->loadedTmpl['tID'] == $template['tID'])) ? 
						(' selected="selected"') : ''
					).'>'.
					$template['title'].
				'</option>';
			}
			$tplSelect .= '</select></td>
				<td><input type="submit" class="ml-button smallmargin" value="'.ML_BUTTON_LABEL_OK.'" name="oldTemplate"/></td>
			</tr>';
		}
		$html .= '
			<table class="actions"><thead><tr><th>'.ML_LABEL_ACTIONS.'</th></tr></thead><tbody><tr><td>
				<table><tbody><tr><td>
					';

		if ($this->settings['mode'] == 'administrate') {
			$html .= '<a class="ml-button" href="'.toURL($this->url).'" title="'.ML_BUTTON_LABEL_BACK_TO_TEMPLATEADMIN.'">'.ML_BUTTON_LABEL_BACK_TO_TEMPLATEADMIN.'</a>';
		} else {
			$backURL = $this->url;
			unset($backURL['view']);
			$html .= '<a class="ml-button" href="'.toURL($backURL).'" title="'.ML_BUTTON_LABEL_BACK_TO_CHECKIN.'">'.ML_BUTTON_LABEL_BACK_TO_CHECKIN.'</a>';
		}

		$html .= '
				</td>';
		if ($this->settings['mode'] == 'administrate') {
			$html .= '
				<td class="firstChild">
					<label for="templateTitle">'.ML_LABEL_TEMPLATE_TITLE.' </label>
					<input type="text" name="tmpl[title]" id="templateTitle" value="'.$this->loadedTmpl['title'].'"/>
				</td>';
		} else {
			$html .= '
				<td>
					<input type="hidden" name="saveTemplate[mpID]" value="'.$this->mpID.'" />
					<table><tbody><tr>
						<td><label>'.ML_LABEL_TEMPLATE_SAVE_AS_NEW.'</label></td>
						<td><input type="text" name="saveTemplate[templateName]"/></td>
						<td><input type="submit" class="ml-button smallmargin" value="'.ML_BUTTON_LABEL_OK.'" name="newTemplate"/></td>
					</tr>'.$tplSelect.'</tbody></table>
				</td>';
		}
		$html .= '
				<td class="lastChild">
					<input type="hidden" name="selectionName" value="'.$this->settings['selectionName'].'"/>
					<input type="hidden" name="timestamp" value="'.time().'"/>
					';
		if ($this->settings['mode'] == 'administrate') {
			$html .= '<input type="submit" class="ml-button" value="'.ML_BUTTON_LABEL_SAVE_DATA.'"/>';
		} else {
			$html .= $this->additionalActions;
		}
		$items = (int)MagnaDB::gi()->fetchOne('
			SELECT count(*)
			  FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID=\''.$this->mpID.'\' AND
			       selectionname=\''.$this->settings['selectionName'].'\' AND
			       session_id=\''.session_id().'\'
		  GROUP BY selectionname
		') - $this->getNumberOfDeselectedItems();
		$html .= '
				</td></tr></tbody></table>
			</td></tr><tr>
				<td colspan="2"><div class="h4">'.ML_LABEL_INFO.'</div><span>'.ML_LABEL_AMOUNT_SELECTED_PRODUCTS.'</span><span id="amountSelectedProducts"> '.$items.'</span></td>
			</tr></tbody></table>
		</form>
		';
		if ($this->settings['showCheckboxes']) {
			ob_start();
?>
<script type="text/javascript">/*<![CDATA[*/
//var runningRequests = 0;
$(document).ready(function() {
	$('form#summaryForm').submit(function() {
		$.blockUI(blockUILoading); 
	});

	$('#selectAll').click(function() {
		var state = $(this).attr('checked') == 'checked';
		console.log(state, $('#summaryForm tbody input[type="checkbox"]'));
		$('#summaryForm tbody input[type="checkbox"]').each(function () {
			$(this).attr('checked', state);
		});
	});
	/*$('#summaryForm').ajaxSend(function () {
		++runningRequests;
	});
	$('#summaryForm').ajaxComplete(function () {
		--runningRequests;
	});
	$('#summaryForm').submit(function () {
		if (runningRequests > 0) {
			myConsole.log('runningRequests: '+runningRequests);
			return false
		}
		return true;
	});	//*/
});
/*]]>*/</script>
<?php
			$html .= ob_get_contents();
			ob_end_clean();
		}
		return $html;
	}

	protected function additionalInitialisation() { }

	protected function getAdditionalHeadlines() { return ''; }
	protected function getAdditionalItemCells($key, $dbRow) { return ''; }
	
	protected function getAdditionalProductNameStuff($prod) { return ''; }
	
	protected function extendProductAttributes($pID, &$data) { }
	
	protected function getTopInfoBox() { }
	
	public function setAdditionalActions($html) {
		$this->additionalActions = $html;
	}
	
	public function renderAjaxReply() {
		return json_encode($this->ajaxReply);
	}
}
