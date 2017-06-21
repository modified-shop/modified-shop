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
	private $settings = array();
	private $sort = array();

	private $renderableData = array();
		
	private $url = array();
	private $magnaSession = array();
	private $magnaShopSession = array();
	
	private $numberofitems = 0;
	
	private $errorLog = array();

	public function __construct($settings = array()) {
		global $_MagnaShopSession, $_MagnaSession, $_url;
		
		$this->settings = array_merge(array(
			'maxTitleChars'	=> 40,
			'itemLimit' => 50
		), $settings);
		
		$this->url = $_url;
		$this->url['view'] = 'failed';
		$this->magnasession = $_MagnaSession;
		$this->magnaShopSession = $_MagnaShopSession;
		
		/* Delete */
		if (isset($_POST['errIDs']) && isset($_POST['action']) && ($_POST['action'] == 'delete')) {
			foreach ($_POST['errIDs'] as $errID) {
				if (ctype_digit($errID)) {
					MagnaDB::gi()->delete(
						TABLE_MAGNA_CS_ERRORLOG,
						array(
							'id' => (int)$errID
						)
					);
				}
			}
		}
		
		if (isset($_GET['sorting'])) {
			$sorting = $_GET['sorting'];
		} else {
			$sorting = 'blabla'; // fallback for default
		}

		switch ($sorting) {
	        case 'itemname':
	            $this->sort['order'] = 'itemname';
	            $this->sort['type']  = 'ASC';
	            break;
	        case 'itemname-desc':
	            $this->sort['order'] = 'itemname';
	            $this->sort['type']  = 'DESC';
	            break;
	        case 'errormessage':
	            $this->sort['order'] = 'errormessage';
	            $this->sort['type']  = 'ASC';
	            break;
	        case 'errormessage-desc':
	            $this->sort['order'] = 'errormessage';
	            $this->sort['type']  = 'DESC';
	            break;
			case 'date':
	            $this->sort['order'] = 'date';
	            $this->sort['type']  = 'ASC';
	            break;
	        case 'date-desc':
	        default:
	            $this->sort['order'] = 'date';
	            $this->sort['type']  = 'DESC';
	            break;
	    }

		$this->numberofitems = (int)MagnaDB::gi()->fetchOne(
			'SELECT DISTINCT count(id) FROM '.TABLE_MAGNA_CS_ERRORLOG.' WHERE `mpID`=\''.$this->magnasession['mpID'].'\''
		);
		$this->pages = ceil($this->numberofitems / $this->settings['itemLimit']);
		$this->currentPage = 1;

		if (isset($_GET['page']) && ctype_digit($_GET['page']) && (1 <= (int)$_GET['page']) && ((int)$_GET['page'] <= $this->pages)) {
			$this->currentPage = (int)$_GET['page'];
		}

		$this->offset = ($this->currentPage - 1) * $this->settings['itemLimit'];

		$artNr = getDBConfigValue('general.keytype', '0') == 'artNr';
		$this->errorLog = MagnaDB::gi()->fetchArray('
			SELECT cse.id, '.($artNr ? 'p.products_id' : 'cse.products_id').', cse.errormessage, 
			       cse.timestamp AS date, pd.products_name AS itemname 
			  FROM '.TABLE_MAGNA_CS_ERRORLOG.' cse, '.($artNr ? TABLE_PRODUCTS.' p, ' : '').TABLE_PRODUCTS_DESCRIPTION.' pd
			 WHERE cse.mpID=\''.$this->magnasession['mpID'].'\' AND 
			       '.($artNr 
			 			? 'cse.products_model=p.products_model AND p.products_id=pd.products_id' 
			 			: 'cse.products_id=pd.products_id'
			 		).' AND
			       pd.language_id = \''.$_SESSION['languages_id'].'\'
			 ORDER BY `'.$this->sort['order'].'` '.$this->sort['type'].' 
			 LIMIT '.$this->offset.','.$this->settings['itemLimit'].'
		');
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
		$right = '<input type="button" class="ml-button" value="'.ML_BUTTON_LABEL_RETRY.'" id="errorLogRetry" name="errorLog[retry]"/>';

		ob_start();
		echo '<div id="infodiag" class="dialog2" title="'.ML_LABEL_NOTE.'">'.ML_HINT_NO_PRODUCTS_SELECTED.'</div>';
		?>
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
	
	$('#errorLogRetry').click(function() {
		if ($('#errorlog input[type="checkbox"]:checked').length == 0) {
			$('#infodiag').jDialog();
		} else {
			$('#action').val('retry');
			$(this).parents('form').submit();
		}
	});
	
	$('table.datagrid tbody tr').click(function() {
		cb = $('input[type="checkbox"]:not(:disabled)', $(this));
		if (cb.length != 1) return;
		if (cb.is(':checked')) {
			cb.removeAttr('checked');
		} else {
			cb.attr('checked', 'checked');
		}
	});
	$('table.datagrid tbody tr td input[type="checkbox"]').click(function () {
		this.checked = !this.checked;
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


	public function renderView() {
		global $_checkinState;

		$html = '';
		if (!empty($this->errorLog)) {
		
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
							<td>'.ML_LABEL_SHOP_TITLE.'&nbsp;'.$this->sortByType('itemname').'</td>
							<td>'.ML_LABEL_CATEGORY_PATH.'</td>
							<td>'.ML_GENERIC_COMMISSIONDATE.'&nbsp;'.$this->sortByType('date').'</td>
							<td class="magnatext">'.ML_COMPARISON_SHOPPING_LABEL_MISSING_FIELDS.'&nbsp;'.$this->sortByType('errormessage').'</td>
						</tr></thead>
						<tbody>';

			if (isset($this->magnasession[$this->magnasession['currentPlatform']]['submit']['state']['failed'])) {
				$failed = (int)$this->magnasession[$this->magnasession['currentPlatform']]['submit']['state']['failed'];
				$failed -= ($this->currentPage - 1) * $this->settings['itemLimit'];
			} else {
				$failed = -1;
			}
			$oddEven = false;
			foreach ($this->errorLog as $item) {
				$commissiondate = strtotime($item['date']);
				$hdate = date("d.m.Y", $commissiondate).' &nbsp;&nbsp;<span class="small">'.date("H:i", $commissiondate).'</span>';
				$item['errormessage'] = json_decode($item['errormessage'], true);
				
				$translatedFields = array();
				foreach ($item['errormessage'] as $mf) {
					$mfC = 'ML_COMPARISON_SHOPPING_FIELD_'.$mf;
					if (defined($mfC)) {
						$translatedFields[] = '<span class="inline"'.(defined($mfC.'_HOVER') ? (' title="'.constant($mfC.'_HOVER').'"') : '').'>'.
													constant($mfC).
											  '</span>';
					} else {
						$translatedFields[] = strtolower($mf);
					}
				}

				if (($failed--) > 0) {
					$failedClass = ' failed';
					if ($failed == 0) {
						$failedClass .= ' flast';
					}
				} else {
					$failedClass = '';
				}
				
				$html .= '
							<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').$failedClass.'">
								<td><input type="checkbox" name="errIDs[]" value="'.$item['id'].'"></td>
								<td>'.fixHTMLUTF8Entities($item['itemname']).
									'<a class="right gfxbutton edit" href="categories.php?pID='.$item['products_id'].'&action=new_product" '.
									   'target="_blank" title="'.ML_LABEL_EDIT.'">'.
								'</a></td>
								<td><ul><li>'.str_replace('<br>', '</li><li>', renderCategoryPath($item['products_id'], 'product')).'</li></ul></td>
								<td>'.$hdate.'</td>
								<td class="errormessage magnatext">'.implode(', ', $translatedFields).'</td>
							</tr>';
			}
			$html .= '
						</tbody>
					</table>';
			ob_start(); ?>
<script type="text/javascript">/*<![CDATA[*/
	$(document).ready(function() {
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
		} else {
			$html .= '<table class="magnaframe"><tbody><tr><td>'.ML_GENERIC_NO_ERRORS_YET.'</td></tr></tbody></table>';
		}

		return $html;
	}
}