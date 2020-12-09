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

class ErrorView {
	private $errorLog;
	
	private $settings = array();
	private $sort = array();
	private $currentPage = 1;
	private $pages = 1;
	
	private $url = array();

	private $mpID = '';

	public function __construct($settings = array()) {
		global $_MagnaSession, $_url;

		$this->mpID = $_MagnaSession['mpID'];
		
		$this->settings = array_merge(array(
			'maxTitleChars'	=> 300,
			'itemLimit'		=> 50,
		), $settings);

		$this->url = $_url;
		$this->url['view'] = 'failed';

		/* Download new Error Messages */
		$begin = MagnaDB::gi()->fetchOne('
		    SELECT dateadded FROM '.TABLE_MAGNA_AMAZON_ERRORLOG.'
		     WHERE mpID = '.$this->mpID.'
		  ORDER BY dateadded DESC 
		     LIMIT 1
		');
		$this->deleteFromLog();
		$begin = getDBConfigValue('amazon.errorlog.lastdate', $this->mpID, $begin);
		if ($begin === false) {
			$begin = time() - 60 * 60 * 24 * 12;
		} else {
			$begin = strtotime($begin.' +0000') + 1;
		}
		$begin = gmdate('Y-m-d H:i:s', max($begin, time() - 60 * 60 * 24 * 12));
		
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
				$this->processAdditonalData($item['AdditionalData']);
				$data = array (
					'mpID' => $this->mpID,
					'batchid' => $item['BatchID'],
					'dateadded' => $item['DateAdded'],
					'errorcode' => $item['ErrorCode'],
					'errormessage' => $item['ErrorMessage'],
					'additionaldata' => serialize($item['AdditionalData']),
				);
				if ($begin < $item['DateAdded']) {
					$begin = $item['DateAdded'];
				}
                $data['recommendation'] = $item['ErrorRecommendation'];
				if (!MagnaDB::gi()->recordExists(TABLE_MAGNA_AMAZON_ERRORLOG, $data)) {
					if (isset($item['ErrorLogURL'])) {
						$item['AdditionalData']['ErrorLogURL'] = $item['ErrorLogURL'];
						$data['additionaldata'] = serialize($item['AdditionalData']);
					}
					MagnaDB::gi()->insert(TABLE_MAGNA_AMAZON_ERRORLOG, $data);
				}
			}
			$newbegin = $item['DateAdded'];
		}
		if (!empty($newbegin)) {
			setDBConfigValue('amazon.errorlog.lastdate', $this->mpID, $begin, true);
		}
		
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
			case 'errorcode':
				$this->sort['order'] = 'errorcode';
				$this->sort['type']  = 'ASC';
				break;
			case 'errorcode-desc':
				$this->sort['order'] = 'errorcode';
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

		$this->numberofitems = (int)MagnaDB::gi()->fetchOne("
			SELECT DISTINCT count(al.id)
			  FROM ".TABLE_MAGNA_AMAZON_ERRORLOG." al
			 WHERE al.mpID = '".$this->mpID."'
		");
		$this->pages = ceil($this->numberofitems / $this->settings['itemLimit']);
		$this->currentPage = 1;

		if (isset($_GET['page']) && ctype_digit($_GET['page']) && (1 <= (int)$_GET['page']) && ((int)$_GET['page'] <= $this->pages)) {
			$this->currentPage = (int)$_GET['page'];
		}

		$this->offset = ($this->currentPage - 1) * $this->settings['itemLimit'];

		$this->errorLog = MagnaDB::gi()->fetchArray('
		    SELECT al.id, al.batchid, al.dateadded, al.errorcode, al.errormessage, al.recommendation, al.additionaldata
		      FROM '.TABLE_MAGNA_AMAZON_ERRORLOG.' al
		     WHERE al.mpID=\''.$this->mpID.'\'
		  GROUP BY al.id
		  ORDER BY `'.$this->sort['order'].'` '.$this->sort['type'].' 
		     LIMIT '.$this->offset.','.$this->settings['itemLimit'].'
		');
		if (!empty($this->errorLog)) {
			foreach ($this->errorLog as &$item) {
                $item['recommendation'] = fixHTMLUTF8Entities($item['recommendation']);
				$item['additionaldata'] = @unserialize($item['additionaldata']);
			}
		}
	}
	
	private function deleteFromLog () {
		/* Delete selected Error Messages*/
		if (isset($_POST['errIDs']) && isset($_POST['action']) && ($_POST['action'] == 'delete')) {
			foreach ($_POST['errIDs'] as $errID) {
				if (ctype_digit($errID)) {
					MagnaDB::gi()->delete(
						TABLE_MAGNA_AMAZON_ERRORLOG,
						array(
							'id' => (int)$errID
						)
					);
				}
			}
		}
		if (isset($_POST['deleteall'])) {
			MagnaDB::gi()->delete(
				TABLE_MAGNA_AMAZON_ERRORLOG, array('mpID' => $this->mpID)
			);
		}
	}
	
	private function processAdditonalData($data) {
		if (isset($data['AmazonOrderID'])) {
			$o = MagnaDB::gi()->fetchOne('
				SELECT data FROM '.TABLE_MAGNA_ORDERS.'
				 WHERE special=\''.MagnaDB::gi()->escape($data['AmazonOrderID']).'\'
			');
			if ($o === false) return;
			$o = @unserialize($o);
			if (!is_array($o)) {
				$o = array();
			}
			$o['ML_ERROR_LABEL'] = 'ML_AMAZON_ERROR_ORDERSYNC_FAILED';
			#echo print_m($o);
			$o = serialize($o);
			MagnaDB::gi()->update(TABLE_MAGNA_ORDERS, array('data' => $o), array('special' => $data['AmazonOrderID']));
		}
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
		$left = '<input type="button" class="ml-button" value="'.ML_BUTTON_LABEL_DELETE.'" id="errorLogDelete" name="errorLog[delete]"/>';
		$right = '<input type="submit" class="ml-button" value="'.ML_BUTTON_LABEL_DELETE_COMPLETE_LOG.'" name="deleteall"/>';

		ob_start();?>
<script type="text/javascript">/*<![CDATA[*/
$(document).ready(function() {
	$('#errorLogDelete').click(function() {
		if (($('#errorlog input[type="checkbox"]:checked').length > 0) &&
			confirm(unescape(<?php echo "'".html2url(ML_GENERIC_DELETE_ERROR_MESSAGES)."'"; ?>))
		) {
			$('#action').val('delete');
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
	
	private function additionalDataHandler($data) {
        $data = is_array($data) ? $data: array();
		$fData = array();
		if ((array_key_exists('SKU', $data) && !empty($data['SKU'])) || (array_key_exists('PID', $data) && !empty($data['PID']))) {
			if (isset($data['PID'])) {
				$fData[ML_LABEL_PRODUCT_ID] = $data['PID'];
				$pID = $data['PID'];
			}

			if (isset($data['SKU'])) {
				$fData['SKU'] = htmlspecialchars($data['SKU']);
				if (!isset($pID)) {
					$pID = magnaSKU2pID($data['SKU']);
				}
			}

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
		} elseif (array_key_exists('AmazonOrderID', $data)) {
			if (!empty($data['AmazonOrderID'])) {
				$fData['AmazonOrderID'] = $data['AmazonOrderID'];
				$oID = MagnaDB::gi()->fetchOne('
					SELECT orders_id FROM '.TABLE_MAGNA_ORDERS.'
					 WHERE special=\''.$data['AmazonOrderID'].'\'
				');
				if (!empty($oID)) {
					$fData[ML_LABEL_ORDER_ID] = '<a title="'.ML_LABEL_EDIT.'" '.
						'target="_blank" href="orders.php?oID='.$oID.'&action=edit">'.
						$oID.'</a>';
				}
			}
		} else if (!array_key_exists('SKU', $data)) {
			$fData = $data;
		}
		return $this->renderAdditionalData($fData);
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
						<td class="nowrap"><input type="checkbox" id="selectAll"/><label for="selectAll">'.ML_LABEL_CHOICE.'</label></td>
						<td>BatchID</td>
						<td>'.ML_AMAZON_LABEL_ADDITIONAL_DATA.'</td>
						<td>'.ML_GENERIC_ERROR_CODE.'&nbsp;'.$this->sortByType('errorcode').'</td>
						<td>'.ML_GENERIC_ERROR_MESSAGES.'&nbsp;'.$this->sortByType('errormessage').'</td>
						<td>'.ML_GENERIC_LABEL_ADDITIONAL_HELP.'</td>
						<td>'.ML_GENERIC_COMMISSIONDATE.'&nbsp;'.$this->sortByType('commissiondate').'</td>
					</tr></thead>
					<tbody>';
		$oddEven = false;
		$batchErrorlogURL = array(
			'ID' => '',
			'URL' => '',
		);
		foreach ($this->errorLog as $item) {
			if (isset($item['additionaldata']['ErrorLogURL'])) {
				$batchErrorlogURL = $item['additionaldata']['ErrorLogURL'];
				unset($item['additionaldata']['ErrorLogURL']);
			} else {
				$batchErrorlogURL = '';
			}
			$item['errormessage'] = fixHTMLUTF8Entities($item['errormessage']);
            $recommendation = $this->processErrorRecommendation($item);
			$dateadded = strtotime($item['dateadded']);
			$hdate = date("d.m.Y", $dateadded).' &nbsp;&nbsp;<span class="small">'.date("H:i", $dateadded).'</span>';
			$shortMessage = (
				(strlen($item['errormessage']) > $this->settings['maxTitleChars'] + 2) ? 
					(substr($item['errormessage'], 0, $this->settings['maxTitleChars'])) : 
					false
			);
			$html .= '
						<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
							<td><input type="checkbox" name="errIDs[]" value="'.$item['id'].'"></td>
							<td>'.(!empty($batchErrorlogURL)
								? '<a target="_blank" href="'.$batchErrorlogURL.'">'.$item['batchid'].'</a>'
								: $item['batchid']
							).'</td>
							<td class="nopadding" style="width: 1px">'.$this->additionalDataHandler($item['additionaldata']).'</td>
							<td class="errorcode">'.(empty($item['errorcode']) ? '&mdash;' : $item['errorcode']).'</td>
							<td class="errormessage">'.(
								($shortMessage !== false) 
									? $shortMessage.'&hellip;<span>'.$item['errormessage'].'</span>' 
									: $item['errormessage']
							).'</td>
							<td class="errorrecommendation">' . $recommendation['short'] . '<span style="display:none;">' . $recommendation['long'] . '</span></td>
							<td>'.$hdate.'</td>
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
			if ($('span', this).length == 0) return;
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
