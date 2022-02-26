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
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

class MagnaCompatibleErrorView {
	private $errorLog = array();
	
	private $settings = array();
	private $sort = array();
	private $currentPage = 1;
	private $pages = 1;
	
	private $url = array();
	
	protected $mpID = 0;
	protected $marketplace = '';
	protected $blRecommendationColumn = false;
	
	public function __construct($settings = array()) {
		global $_MagnaSession, $_url;
		
		$this->settings = array_merge(array(
			'maxTitleChars' => 90,
			'itemLimit'     => 50,
			'hasImport' => false,
			'hasOrigin' => false,
			'hasBatchId' => false,
		), $settings);

		$this->url = $_url;

		$this->mpID = $_MagnaSession['mpID'];
		$this->marketplace = $_MagnaSession['currentPlatform'];

		/* Delete selected Error Messages*/
		if (isset($_POST['action'])) {
			if ($_POST['action'] == 'deleteall') {
				MagnaDB::gi()->delete(TABLE_MAGNA_COMPAT_ERRORLOG, array(
					'mpID' => $this->mpID
				));
				// set lastdate to now to prevent downloading everything anew
				setDBConfigValue($this->marketplace.'.errorlog.lastdate', $this->mpID, date('Y-m-d H:i:s'), true);
			} else if (($_POST['action'] == 'delete') && isset($_POST['errIDs'])) {
				foreach ($_POST['errIDs'] as $errID) {
					if (ctype_digit($errID)) {
						MagnaDB::gi()->delete(TABLE_MAGNA_COMPAT_ERRORLOG, array(
							'id' => (int)$errID
						));
					}
				}
			}
		}
		
		$this->importErrorLog();
		
		if (isset($_GET['sorting'])) {
			$sorting = $_GET['sorting'];
		} else {
			$sorting = 'blabla'; // fallback for default
		}

		switch ($sorting) {
			case 'errormessage':
				$this->sort['order'] = 'errormessage';
				$this->sort['type']  = 'ASC';
				break;
			case 'errormessage-desc':
				$this->sort['order'] = 'errormessage';
				$this->sort['type']  = 'DESC';
				break;
			case 'dateadded':
				$this->sort['order'] = 'dateadded';
				$this->sort['type']  = 'ASC';
				break;
			case 'dateadded-desc':
			default:
				$this->sort['order'] = 'dateadded';
				$this->sort['type']  = 'DESC';
				break;
		}

		$this->numberofitems = (int)MagnaDB::gi()->fetchOne('
			SELECT DISTINCT count(id) FROM '.TABLE_MAGNA_COMPAT_ERRORLOG.' WHERE mpID='.$this->mpID.'
		');
		$this->pages = ceil($this->numberofitems / $this->settings['itemLimit']);
		$this->currentPage = 1;

		if (isset($_GET['page']) && ctype_digit($_GET['page']) && (1 <= (int)$_GET['page']) && ((int)$_GET['page'] <= $this->pages)) {
			$this->currentPage = (int)$_GET['page'];
		}

		$this->offset = ($this->currentPage - 1) * $this->settings['itemLimit'];

		$this->errorLog = MagnaDB::gi()->fetchArray('
		    SELECT al.id, al.BatchId, al.origin, al.dateadded, al.errormessage, al.recommendation, al.additionaldata
		      FROM '.TABLE_MAGNA_COMPAT_ERRORLOG.' al
		     WHERE al.mpID=\''.$this->mpID.'\'
		  GROUP BY al.id
		  ORDER BY `'.$this->sort['order'].'` '.$this->sort['type'].' 
		     LIMIT '.$this->offset.','.$this->settings['itemLimit'].'
		');
		if (!empty($this->errorLog)) {
			foreach ($this->errorLog as &$item) {
				$item['errormessage'] = fixHTMLUTF8Entities($item['errormessage']);
				if ($this->blRecommendationColumn && !empty($item['recommendation'])) $item['recommendation'] = fixHTMLUTF8Entities($item['recommendation']);
				$item['additionaldata'] = @unserialize($item['additionaldata']);
			}
		}
	}
	
	private function processErrorAdditonalData($data) {
		if (isset($data['MOrderID'])) {
			$o = MagnaDB::gi()->fetchOne('
				SELECT data FROM '.TABLE_MAGNA_ORDERS.'
				 WHERE special=\''.MagnaDB::gi()->escape($data['MOrderID']).'\'
			');
			if ($o === false) return;
			$o = @unserialize($o);
			if (!is_array($o)) {
				$o = array();
			}
			$o['ML_ERROR_LABEL'] = 'ML_GENERIC_ERROR_ORDERSYNC_FAILED';
			#echo print_m($o);
			$o = serialize($o);
			MagnaDB::gi()->update(TABLE_MAGNA_ORDERS, array('data' => $o), array('special' => $data['MOrderID']));
		}
	}
	
	protected function importErrorLog() {
		if (!$this->settings['hasImport']) {
			return;
		}
		$begin = MagnaDB::gi()->fetchOne('
		    SELECT dateadded FROM '.TABLE_MAGNA_COMPAT_ERRORLOG.'
		     WHERE mpID = '.$this->mpID.'
		  ORDER BY dateadded DESC
		     LIMIT 1
		');
		$begin = getDBConfigValue($this->marketplace.'.errorlog.lastdate', $this->mpID, $begin);
		if ($begin === false) {
			$begin = time() - 60 * 60 * 24 * 12;
		} else {
			$begin = strtotime($begin.' +0000') + 1;
		}
		$begin = gmdate('Y-m-d H:i:s', max($begin, time() - 60 * 60 * 24 * 12));
		#$begin = '2013-01-01 00:00:00';
		
		$request = array(
			'ACTION' => 'GetErrorLogForDateRange',
			'BEGIN' => $begin,
			'OFFSET' => array (
				'COUNT' => 1000,
				'START' => 0
			),
		);
		#echo print_m($request, '$request');
		try {
			$result = MagnaConnector::gi()->submitRequest($request);
		} catch (MagnaException $e) {
			$result['DATA'] = array();
		}
		#echo print_m($result, '$result');
		#return;
		$newbegin = '';
		if (array_key_exists('DATA', $result) && !empty($result['DATA'])) {
			foreach ($result['DATA'] as $item) {
				$this->processErrorAdditonalData($item['ErrorData']);
				$data = array (
					'mpID' => isset($item['MpId']) ? $item['MpId'] : $item['MarketplaceId'],
					'BatchId' => !empty($item['BatchId']) ? $item['BatchId'] : null,
					'origin' => isset($item['Origin']) ? $item['Origin'] : '',
					'dateadded' => $item['DateAdded'],
					'errormessage' => $item['ErrorMessage'],
					'additionaldata' => serialize($item['ErrorData']),
				);
				if (isset($item['ErrorRecommendation'])) $data['recommendation'] = $item['ErrorRecommendation'];
				if ($begin < $item['DateAdded']) {
					$begin = $item['DateAdded'];
				}
				if (!MagnaDB::gi()->recordExists(TABLE_MAGNA_COMPAT_ERRORLOG, $data)) {
					MagnaDB::gi()->insert(TABLE_MAGNA_COMPAT_ERRORLOG, $data);
				}
			}
			$newbegin = $item['DateAdded'];
		}
		if (!empty($newbegin)) {
			setDBConfigValue($this->marketplace.'.errorlog.lastdate', $this->mpID, $begin, true);
		}
	}
	
	private function sortByType($type) {
		return '
			<span class="nowrap">
				<a href="'.toURL($this->url, array('sorting' => $type.'')).'" title="'.ML_LABEL_SORT_ASCENDING.'" class="sorting">
					<img alt="'.ML_LABEL_SORT_ASCENDING.'" src="'.DIR_MAGNALISTER_WS_IMAGES.'sort_up.png" />
				</a>
				<a href="'.toURL($this->url, array('sorting' => $type.'-desc')).'" title="'.ML_LABEL_SORT_DESCENDING.'" class="sorting">
					<img alt="'.ML_LABEL_SORT_DESCENDING.'" src="'.DIR_MAGNALISTER_WS_IMAGES.'sort_down.png" />
				</a>
			</span>';
	}

	public function renderActionBox() {
		$left = '<input type="button" class="ml-button ml-js-deleteBtn" value="'.ML_BUTTON_LABEL_DELETE.'" name="delete"/>';
		$right = '<input type="button" class="ml-button ml-js-deleteBtn" value="'.ML_BUTTON_LABEL_DELETE_ENTIRE_PROTOCOL.'" id="errorLogDelete" name="deleteall"/>';

		ob_start();?>
<script type="text/javascript">/*<![CDATA[*/
$(document).ready(function() {
	$('.ml-js-deleteBtn').click(function() {
		var btnAction = $(this).attr('name');
		
		if ((btnAction == 'deleteall')
			&& confirm(unescape(<?php echo "'".html2url(ML_GENERIC_CONFIRM_DELETE_ENTIRE_ERROR_PROTOCOL)."'"; ?>))
		) {
			$('#action').val(btnAction);
			$(this).parents('form').submit();
			
		} else if (($('#errorlog input[type="checkbox"]:checked').length > 0)
			&& confirm(unescape(<?php echo "'".html2url(ML_GENERIC_DELETE_ERROR_MESSAGES)."'"; ?>))
		) {
			$('#action').val(btnAction);
			$(this).parents('form').submit();
		}
	});
});
/*]]>*/</script>
<?php // Durch aufrufen der Seite wird automatisch ein Aktualisierungsauftrag gestartet
		$js = ob_get_contents();	
		ob_end_clean();

		return '
			<input type="hidden" id="action" name="action" value="">
			<input type="hidden" name="timestamp" value="'.time().'">
			<table class="actions">
				<thead><tr><th>'.ML_LABEL_ACTIONS.'</th></tr></thead>
				<tbody><tr><td>
					<table><tbody><tr>
						<td class="firstChild">'.$left.'</td>
						<td class="lastChild">'.$right.'</td>
					</tr></tbody></table>
				</td></tr></tbody>
			</table>
			'.$js;
	}
	
	private function renderAdditionalData($data) {
		if (empty($data)) {
			return '&nbsp;';
		}
		$html = '<table class="nostyle addData fullWidth"><tbody>';
		foreach ($data as $key => $item) {
			$html .= '
				<tr>
					<th>'.str_replace(' ', '&nbsp;', $key).'</th>
					<td>'.$item.'</td>
				</tr>';
		}
		return $html.'</tbody></table>';
	}
	
	protected function additionalDataHandler($data) {
		if (!is_array($data) || empty($data)) {
			return '&nbsp;&nbsp;&mdash;';
		}
		$fData = array();
		if (array_key_exists('SKU', $data)) {
			$fData['SKU'] = htmlspecialchars($data['SKU']);
			$pID = magnaSKU2pID($data['SKU']);

			$title = MagnaDB::gi()->fetchOne('
				SELECT products_name
				  FROM '.TABLE_PRODUCTS_DESCRIPTION.' 
				 WHERE products_id=\''.(string)$pID.'\' 
				       AND language_id = \''.$_SESSION['languages_id'].'\'
			');
			if (!empty($title)) {
				$fData[ML_LABEL_SHOP_TITLE] = '<a title="'.ML_LABEL_EDIT.'" '.
					'target="_blank" href="categories.php?pID='.$pID.'&action=new_product">'.
					$title.'</a>';
			}
			$fData = array_merge($data, $fData);
		} else {
			$fData = $data;
		}
		return $this->renderAdditionalData($fData);
	}
	
	protected function processErrorMessage($item) {
		$ret = array (
			'long' => $item['errormessage'],
			'short' => '',
		);
		if (preg_match('/^constant\(([A-Z_]*)\)$/', $item['errormessage'], $match)) {
			if (defined($match[1])) {
				$ret['long'] = constant($match[1]);
			} else {
				$ret['long'] = $match[1];
			}
		} else {
			try {
				$jsonerror = @json_decode($ret['long'], true);
			} catch (Exception $e) {
				$jsonerror = false;
			}
			if (is_array($jsonerror)) {
				if (isset($jsonerror['MissingFields'])) {
					$ret['long'] = ML_LABEL_MISSING_DATA.': '.implode(', ', $jsonerror['MissingFields']);
				}
			}
		}
		$ret['short'] = (
			(strlen($ret['long']) > $this->settings['maxTitleChars'] + 2) ? 
				(substr($ret['long'], 0, $this->settings['maxTitleChars']).'&hellip;') : 
				$ret['long']
		);
		if (strpos($ret['long'], '&lt;div') !== false) {
			$ret['long'] = html_entity_decode($ret['long']);
		}
		return $ret;
	}

	protected function processErrorRecommendation($item) {
		$ret = array (
			'long' => $item['recommendation'],
			'short' => '',
		);
		$ret['short'] = (
			(strlen($ret['long']) > $this->settings['maxTitleChars'] + 2) ? 
				(substr($ret['long'], 0, $this->settings['maxTitleChars']).'&hellip;') : 
				$ret['long']
		);
		if (strpos($ret['long'], '&lt;div') !== false) {
			$ret['long'] = html_entity_decode($ret['long']);
		}
		return $ret;
	}

	public function renderView() {
		$html = '';
		if (empty($this->errorLog)) {
			return '<table class="magnaframe"><tbody><tr><td>'.ML_GENERIC_NO_ERRORS_YET.'</td></tr></tbody></table>';
		}

		$tmpURL = $this->url;
		if (isset($_GET['sorting'])) {
			$tmpURL['sorting'] = $_GET['sorting'];
		}

		$html .= '
			<form action="'.toURL($this->url).'" method="POST">
				<table class="listingInfo"><tbody><tr>
					<td class="ml-pagination">
						<span class="bold">'.ML_LABEL_CURRENT_PAGE.' &nbsp;&nbsp; '.$this->currentPage.'</span>
					</td>
					<td class="textright">
						'.renderPagination($this->currentPage, $this->pages, $tmpURL).'
					</td>
				</tr></tbody></table>
				<table class="datagrid" id="errorlog">
					<thead><tr>
						<td class="nowrap" style="width: 5px;"><input type="checkbox" id="selectAll"/><label for="selectAll">'.ML_LABEL_CHOICE.'</label></td>
						'.($this->settings['hasBatchId'] ? '<td>'.ML_AMAZON_LABEL_BATCHID.'</td>' : '').'
						<td>'.ML_AMAZON_LABEL_ADDITIONAL_DATA.'</td>
						<td>'.ML_GENERIC_ERROR_MESSAGES.'&nbsp;'.$this->sortByType('errormessage').'</td>' ;
		if ($this->blRecommendationColumn) $html .= '
						<td>'.ML_GENERIC_LABEL_ADDITIONAL_HELP.'</td>';
		$html .= '
						'.($this->settings['hasOrigin'] ? '<td>'.ML_GENERIC_LABEL_ORIGIN.'</td>' : '').'
						<td>'.ML_GENERIC_COMMISSIONDATE.'&nbsp;'.$this->sortByType('commissiondate').'</td>
					</tr></thead>
					<tbody>';
		$oddEven = false;
		foreach ($this->errorLog as $item) {
			$dateadded = strtotime($item['dateadded']);
			$hdate = date("d.m.Y", $dateadded).' &nbsp;&nbsp;<span class="small">'.date("H:i", $dateadded).'</span>';
			$message = $this->processErrorMessage($item);
			if ($this->blRecommendationColumn) {
				$recommendation = $this->processErrorRecommendation($item);
			}
			$html .= '
						<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '"><td><input type="checkbox" name="errIDs[]" value="' . $item['id'] . '"></td>';
			if ($this->settings['hasBatchId']) {
				if (!empty($item['BatchId'])) {
					$html .= '
							<td>' . $item['BatchId'] . '</td>';
				} else {
					$html .= '
							<td>&nbsp;&nbsp;&mdash;</td>';
				}
			}
			$html .= '
							<td class="nopadding" style="width: 1px">' . $this->additionalDataHandler($item['additionaldata']) . '</td>
							<td class="errormessage">' . $message['short'] . '<span>' . $message['long'] . '</span></td>';
			if ($this->blRecommendationColumn) $html .= '
							<td class="errorrecommendation">' . $recommendation['short'] . '<span style="display:none;">' . $recommendation['long'] . '</span></td>';
			$html .= '
							' . ($this->settings['hasOrigin'] ? '<td>' . $item['origin'] . '</td>' : '') . '
							<td>' . $hdate . '</td>
						</tr>';
		}
		$html .= '
					</tbody>
				</table>
				<div id="errordetails" class="dialog2" title="'.ML_GENERIC_ERROR_DETAILS.'"></div>
				<div id="recommendationdetails" class="dialog2" title="'.ML_GENERIC_ERROR_RECOMMENDATIONS.'"></div>';
		ob_start(); ?>
<script type="text/javascript">/*<![CDATA[*/
	$(document).ready(function() {
		$('table#errorlog tbody td.errormessage').click(function() {
			$('#errordetails').html($('span', this).html()).jDialog();
		});
		
		$('table#errorlog tbody td.errorrecommendation').click(function() {
			$('#recommendationdetails').html($('span', this).html()).jDialog();
		});
		
		$('#selectAll').click(function() {
			state = $(this).attr('checked');
			$('#errorlog input[type="checkbox"]:not([disabled])').each(function() {
				$(this).attr('checked', state);
			});
		});
	});
	/*]]>*/</script>
<?php
		$html .= ob_get_contents();
		ob_end_clean();
		$html .= $this->renderActionBox().'
			</form>';
		return $html;
	}
}
