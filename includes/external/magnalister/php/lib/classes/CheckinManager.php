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
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/TemplateAdmin.php');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimpleSummaryView.php');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimpleCheckinCategoryView.php');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/CheckinSubmit.php');

class CheckinManager {
	protected $_magnasession = array();
	protected $_magnaQuery = array();
	protected $selection = array();
	protected $_url = array();
		
	protected $views = array();
	protected $settings = array();
	
	protected $topFormHtml = '';
	
	protected $isAjax = false;
	
	public function __construct($views = array(), $settings = array()) {
		global $magnaConfig, $_MagnaSession, $_url, $_magnaQuery;

		$this->views = array_merge(array(
			'summaryView'   => 'SimpleSummaryView',
			'checkinView'   => 'SimpleCheckinCategoryView',
			'checkinSubmit' => 'CheckinSubmit'
		), $views);
		
		$this->settings = array_merge(array(
			'selectionName'   => 'checkin',
			'hasPurge'        => true,
		), $settings);

		#initArrayIfNecessary($_MagnaSession, array($_MagnaSession['mpID'], 'selection', $this->settings['selectionName']));
		$this->_magnasession = &$_MagnaSession;	
		$this->_magnaQuery = &$_magnaQuery;

		$_url['mode'] = 'checkin';
		$this->_url = &$_url;
		
		$this->isAjax = isset($_GET['kind']) && ($_GET['kind'] == 'ajax');
	}

	protected function loadTemplate($tmplID) {
		if ($tmplID == -1) {
			/* Actively select no template --> delete current selection. */
			MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
				'mpID' => $this->_magnasession['mpID'],
				'selectionname' => $this->settings['selectionName'],
				'session_id' => session_id(),
			));
			$this->_magnasession[$this->_magnasession['mpID']]['checkinTemplate'] = '';
			return true;
		}
		
		$template = MagnaDB::gi()->fetchRow(
			"SELECT * FROM ".TABLE_MAGNA_SELECTION_TEMPLATES." WHERE tID='".MagnaDB::gi()->escape($tmplID)."'"
		);
		if (empty($template)) {
			/* not a valid template */
			return false;
		}
		
		MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION, array(
			'mpID' => $this->_magnasession['mpID'],
			'selectionname' => $this->settings['selectionName'],
			'session_id' => session_id(),
		));

		$offset = 0;
		$limit = 100;

		if (getDBConfigValue('general.keytype', '0') == 'artNr') {
			$baseQuery = '
				SELECT p.products_id AS pID, msa.data
				  FROM '.TABLE_MAGNA_SELECTION_TEMPLATE_ENTRIES.' msa, '.TABLE_PRODUCTS.' p
				 WHERE tID='.$template['tID'].'
				       AND p.products_model=msa.products_model
			  ORDER BY pID ASC
				 LIMIT %d,'.$limit.'
			';
		} else {
			$baseQuery = '
				SELECT pID, data
				  FROM '.TABLE_MAGNA_SELECTION_TEMPLATE_ENTRIES.'
				 WHERE tID='.$template['tID'].'
			  ORDER BY pID ASC
				 LIMIT %d,'.$limit.'
			';
		}

		//echo print_m($baseQuery, 'baseQuery');
		while ($chunk = MagnaDB::gi()->fetchArray(sprintf($baseQuery, $offset))) {
			$batch = array();
			foreach ($chunk as $item) {
				$batch[] = array(
					'pID' => $item['pID'],
					'data' => $item['data'],
					'mpID' => $this->_magnasession['mpID'],
					'selectionname' => $this->settings['selectionName'],
					'session_id' => session_id(),
					'expires' => gmdate('Y-m-d H:i:s')
				);
			}
			MagnaDB::gi()->batchinsert(TABLE_MAGNA_SELECTION, $batch, true);
			unset($batch);
			$offset += $limit;
		}
		unset($chunk);
		$this->_magnasession[$this->_magnasession['mpID']]['checkinTemplate'] = $template;
		return true;
	}
	
	protected function renderTemplateSelector () {
		$templates = MagnaDB::gi()->fetchArray('
			SELECT * FROM '.TABLE_MAGNA_SELECTION_TEMPLATES.'
			 WHERE mpID=\''.$this->_magnasession['mpID'].'\'
		');
		$html = '
				<form id="templateSelection" name="templateSelection" method="POST" action="'.toURL($this->_url, array('mode'=>'checkin')).'">
					<select name="selectTemplate">';
		if (!empty($templates)) {
			$html .= '
						<option value="-1">'.ML_LABEL_USE_TEMPLATE.'</option>';
			initArrayIfNecessary($this->_magnasession, array($this->_magnasession['mpID'], 'checkinTemplate'));
			$loadedTmpl = &$this->_magnasession[$this->_magnasession['mpID']]['checkinTemplate'];
			foreach ($templates as $template) {
				$html .= '
						<option value="'.$template['tID'].'"'.(
							(isset($loadedTmpl['tID']) && ($loadedTmpl['tID'] == $template['tID'])) ? (' selected="selected"') : ''
						).'>'.ML_LABEL_TEMPLATE.': '.$template['title'].'</option>';
			}
		} else {
			$html .= '
						<option value="-1">'.ML_LABEL_NO_TEMPLATES_YET.'</option>';
		}
		$html .= '
					</select>
					<script type="text/javascript">/*<![CDATA[*/
						$(document).ready(function() {
							$(\'#templateSelection select\').change(function() {
								$(this).parent(\'form\').submit();
							});
						});
					/*]]>*/</script>
					<a id="editTemplates" class="gfxbutton medium border visiblebg cog valignbottom" '.
						'href="'.toURL($this->_url, array('view'=>'administrate')).'" '.
						'title="'.ML_BUTTON_LABEL_ADMINISTRATE_TEMPLATES.'"></a>
				</form>
				<div class="right">'.$this->topFormHtml.'</div>
				<div id="templateInfoDiag" class="dialog2" title="'.ML_LABEL_INFORMATION.'">'.ML_TEXT_TEMPLATE_INFO.'</div>
				<script type="text/javascript">/*<![CDATA[*/
				$(document).ready(function() {
					$(\'#template_info\').click(function() {
						$(\'#templateInfoDiag\').jDialog();
					});
				});
				/*]]>*/</script>';
		return $html;
	}

	private function getNumberOfSelectedItems() {
		return (int)MagnaDB::gi()->fetchOne('
			SELECT count(*)
			  FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID=\''.$this->_magnasession['mpID'].'\' AND
			       selectionname=\''.$this->settings['selectionName'].'\' AND
			       session_id=\''.session_id().'\'
		  GROUP BY selectionname
		');
	}

	private function getNumberOfSubmitableItems() {
		return (int)MagnaDB::gi()->fetchOne('
			SELECT count(*)
			  FROM '.TABLE_MAGNA_SELECTION.'
			 WHERE mpID=\''.$this->_magnasession['mpID'].'\' AND
			       selectionname=\''.$this->settings['selectionName'].'\' AND
			       session_id=\''.session_id().'\' AND
			       data NOT LIKE \'%s:8:"selected";b:0;%\'
		  GROUP BY selectionname
		');
	}
	
	public function extendTopFormHtml($html) {
		$this->topFormHtml = $html;
	}

	public function mainRoutine() {
		global $magnaConfig;

		$items = $this->getNumberOfSelectedItems();
		
		/* Are we in the holy sumbit cycle? */
		if (
			(array_key_exists('checkin_add', $_POST) 
				|| array_key_exists('checkin_purge', $_POST)
				|| array_key_exists('checkin_add_debug', $_POST)
			) 
			&& (!isset($_SESSION['post_timestamp']) || ($_SESSION['post_timestamp'] != $_POST['timestamp']))
		) {
			/* we are... */
			$_SESSION['post_timestamp'] = $_POST['timestamp'];
			$this->_magnaQuery['view'] = 'submit';
		}

		/* Set the view */
		if (array_key_exists('view', $_GET) && !empty($_GET['view']) && !isset($this->_magnaQuery['view'])) {
			$this->_magnaQuery['view'] = $_GET['view'];
		} else if (!isset($this->_magnaQuery['view'])) {
			$this->_magnaQuery['view'] = '';
		}

		/* Regular Summary View with Check-In Buttons */
		if (($this->_magnaQuery['view'] == 'summary') && ($items > 0)) {
			$this->_url['view'] = 'summary';

			$aV = new $this->views['summaryView'](array('selectionName' => $this->settings['selectionName']));

			if ($this->isAjax) {
				return $aV->renderAjaxReply();
			
			} else {
				try {
					$result = MagnaConnector::gi()->submitRequest(array(
						'ACTION' => 'GetUsedListingsCountForDateRange',
						'SUBSYSTEM' => 'Core',
						'BEGIN' => date("Y-m-d H:i:s", mktime(0, 0, 0, date('m'), 1, date('Y'))),
						'END' => date("Y-m-d H:i:s"),
					));
			
					$usedListings = (int)$result['DATA']['UsedListings'];
				} catch (MagnaException $e) {
					$usedListings = 0;
				}
				$listings = array (
					'used' => $usedListings,
					'available' => $magnaConfig['maranon']['IncludedListings']
				);

                $sExceedPopupText = ML_TEXT_LISTING_GOING_TO_EXCEED;
                if (defined('SHOPSYSTEM_GAMBIO_CLOUD') && SHOPSYSTEM_GAMBIO_CLOUD === true && $usedListings <= 20) {
                    $listings['available'] = 20;
                    $sExceedPopupText = ML_TEXT_LISTING_GOING_TO_EXCEED_GAMBIO_CLOUD;
                }

				$listingsExceeded = (($listings['available'] > 0) && (($listings['used'] + $items) > $listings['available']));

				$addActions = '
						<table class="right"><tbody>
							<tr>
								<td class="textleft">
									<input type="button" class="fullWidth ml-button smallmargin mlbtn-action" value="'.ML_BUTTON_LABEL_CHECKIN_ADD.'" id="checkin_add" name="checkin_add"/>
									'.(MAGNA_DEBUG || (isset($_GET['MLDEBUG']) && ($_GET['MLDEBUG'] == 'true'))
										? '<input type="button" class="ml-button smallmargin" style="margin-top: -26px; position: absolute; right: 30px;" '.
									             'value=" " id="checkin_add_debug" name="checkin_add_debug"/>'
									    : '').'
								</td>
								<td>
									<div class="desc" id="desc_ci_add" title="'.ML_LABEL_INFOS.'"><span>'.ML_TEXT_BUTTON_CHECKIN_ADD.'</span></div>
								</td>
							</tr>
							'.(($this->settings['hasPurge']) ? '
							<tr>
								<td class="textleft">
									<input type="button" class="fullWidth ml-button smallmargin" value="'.ML_BUTTON_LABEL_CHECKIN_PURGE.'" id="checkin_purge" name="checkin_purge"/>
								</td>
								<td>
									<div class="desc" id="desc_ci_purge" title="'.ML_LABEL_INFOS.'"><span>'.ML_TEXT_BUTTON_CHECKIN_PURGE.'</span></div>
								</td>
							</tr>' : '').'
						</tbody></table>
						<div id="confirmPurgeDiag" class="dialog2" title="'.ML_HINT_HEADLINE_CONFIRM_PURGE.'">'.ML_TEXT_CONFIRM_PURGE.'</div>
						<input type="hidden" id="actionType" value="_" name="checkin"/>
						<div id="confirmDiag" class="dialog2" title="'.ML_HINT_HEADLINE_EXCEEDING_INCLUSIVE_LISTINGS.'">
							'.sprintf(
								$sExceedPopupText,
								($listings['used'] + $items - $listings['available']),
								$magnaConfig['maranon']['ShopID']
							).'
						</div>
						<div id="infoDiag" class="dialog2" title="'.ML_LABEL_INFORMATION.'"></div>
				';
				ob_start();?>
<script type="text/javascript">/*<![CDATA[*/
var listingsExceeded = <?php echo $listingsExceeded ? 'true' : 'false'; ?>;
function execSubmit(e) {
	$('#actionType').attr('name', $(e).attr('id'));
	$(e).parents('form').submit();
}
function showPurgeConfirmDiag(e) {
	$('#confirmPurgeDiag').jDialog({
		buttons: {
			'<?php echo ML_BUTTON_LABEL_ABORT; ?>': function() {
				$(this).dialog('close');
			},
			'<?php echo ML_BUTTON_LABEL_OK; ?>': function() {
				execSubmit(e);
				$(this).dialog('close');
			}
		}
	});
}

function showListingsExceedConfirmDiag(callback) {
	$('#confirmDiag').jDialog({
		buttons: {
			'<?php echo ML_BUTTON_LABEL_ABORT; ?>': function() {
				$(this).dialog('close');
			},
			'<?php echo ML_BUTTON_LABEL_OK; ?>': function() {
				callback();
				$(this).dialog('close');
			}
		}
	});
}
$(document).ready(function() {
	$('#checkin_add').click(function() {
		e = this;
		if (listingsExceeded) {
			showListingsExceedConfirmDiag(function() {
				execSubmit(e);
			});
		} else {
			execSubmit(e);
		}
	});
	$('#checkin_add_debug').click(function() { e = this; execSubmit(e); });
	$('#checkin_purge').click(function() {
		e = this;
		if (listingsExceeded) {
			showListingsExceedConfirmDiag(function () {
				showPurgeConfirmDiag(e);
			});
 		} else {
			showPurgeConfirmDiag(e);
		}
	});
	$('#desc_ci_add').click(function() {
		$('#infoDiag').html($(this, 'span').html()).jDialog();
	});
	$('#desc_ci_purge').click(function() {
		$('#infoDiag').html($(this, 'span').html()).jDialog();
	});
});
/*]]>*/</script>
<?php
				$addActions .= ob_get_contents();	
				ob_end_clean();

				$aV->setAdditionalActions($addActions);
				return $aV->renderSelection();
			}
		/* Summary View to administrate the currently selected Template */
		} else if ($this->_magnaQuery['view'] == 'administrate') {
			
			if (array_key_exists('edit', $_POST)) {
				$tmplID = array_keys($_POST['edit']);
				$tmplID = $tmplID[0];
			} else if (array_key_exists('tmpl', $_POST)) {
				$tmplID = $_POST['tmpl']['tID'];
			}
		
			if ((isset($tmplID) && $this->loadTemplate($tmplID)) || 
				(array_key_exists('tmpl', $_POST) && array_key_exists('title', $_POST['tmpl']))
			) {
				$this->_url['view'] = 'administrate';
				
				$aV = new $this->views['summaryView'](
					array(
						'selectionName'   => $this->settings['selectionName'],
						'mode'			  => 'administrate'
					)
				);
				return $aV->renderSelection();
		
			} else {
				$tA = new TemplateAdmin();
				return $tA->renderTemplateList();
			}
			
		/* Friggin' Submit Action */
		} else if (($this->_magnaQuery['view'] == 'submit') && ($this->getNumberOfSubmitableItems() > 0)) {
			$this->_url['view'] = $this->_magnaQuery['view'];

			if (!$this->isAjax || (isset($_GET['abort']))) {
				/* Do this only at the beginning of the holy submit process */
				$aV = new $this->views['summaryView'](array('selectionName' => $this->settings['selectionName'])); /* Process the current POST */
				$aV->prepareAllProductsForSubmit(); /* Get rid of deselected items and populate any attributes if necessary */
			}
			
			$cS = new $this->views['checkinSubmit']($this->settings);

			if ($this->isAjax) {
				echo $cS->submit();
			} else {
				if (array_key_exists('checkin_add_debug', $_POST)) {
					$_GET['abort'] = 'true';
				}
				$cS->init(array_key_exists('checkin_purge', $_POST) ? 'PURGE' : 'ADD');
				echo $cS->renderBasicHTMLStructure();
			}

		/* Category - Product - Overview View */
		} else {
			if (array_key_exists('selectTemplate', $_POST)) {
				$this->loadTemplate($_POST['selectTemplate']);
			}
			
			if (isset($_GET['cPath'])) {
				$this->_url['cPath'] = $_GET['cPath'];
			}
			
			if (
					defined('MAGNA_DEV_PRODUCTLIST') 
					&& (MAGNA_DEV_PRODUCTLIST === true)
					&& (strpos(strtolower($this->views['checkinView']), 'productlist') !== false)
			) {
				$aCV = new $this->views['checkinView']();
				echo $aCV;
			}else{
				$aCV = new $this->views['checkinView'](null, array(), isset($_GET['sorting']) ? $_GET['sorting'] : false, '');
				if ($this->isAjax) {
					return $aCV->renderAjaxReply();
				} else {
					$aCV->prependTopHTML($this->renderTemplateSelector());
					return $aCV->printForm();
				}
			}
		}
	}

}
