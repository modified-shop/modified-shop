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
 * $Id: CheckinManager.php 628 2015-02-20 18:54:11Z derpapst $
 *
 * (c) 2010 - 2013 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
require_once(DIR_MAGNALISTER_INCLUDES . 'lib/classes/CheckinManager.php');

class RicardoCheckinManager extends CheckinManager {
	
	protected $bDisplayLimitationWarning = true;

	public function mainRoutine() {
		global $magnaConfig;

		$items = $this->getNumberOfSelectedItems();
		$aDisplayLimitationWarning = getDBConfigValue('ricardo.checkin.showlimitationwarning', $this->_magnasession['mpID'], array('val'=>true));
		if (isset($aDisplayLimitationWarning)) {
			$this->bDisplayLimitationWarning = $aDisplayLimitationWarning['val'];
		}
		
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
				if (isset($_GET['where']) && $_GET['where'] === 'getItemsFee') {
					$aViewCheckin = new $this->views['checkinSubmit']($this->settings);
					return $aViewCheckin->submit();
				}
				
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
								ML_TEXT_LISTING_GOING_TO_EXCEED, 
								($listings['used'] + $items - $listings['available']),
								$magnaConfig['maranon']['ShopID']
							).'
						</div>
						<div id="infoDiagFee" class="dialog2" title="'.ML_LABEL_INFORMATION.'"></div>
				';
				ob_start();?>
<script type="text/javascript" src="<?php echo DIR_MAGNALISTER_WS; ?>js/jquery.itemsFee.js"></script>
<script type="text/javascript">/*<![CDATA[*/
var listingsExceeded = <?php echo $listingsExceeded ? 'true' : 'false'; ?>;
var eForm = this;
function  execSubmit(e) {
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
	function okFunction(e) {
		if (listingsExceeded) {
			showListingsExceedConfirmDiag(function() {
				execSubmit(e);
			});
		} else {
			execSubmit(e);
		}
	};
	
	function okPurgeFunction(e) {
		if (listingsExceeded) {
			showListingsExceedConfirmDiag(function () {
				showPurgeConfirmDiag(e);
			});
 		} else {
			showPurgeConfirmDiag(e);
		}
	};
	
	$('#checkin_add').click(function() {
		$(this).itemsFee({
			mode: '<?php echo $this->bDisplayLimitationWarning === true ? 'on' : 'off' ?>',
			addItems: okFunction,
			method: 'getItemsFee',
			message: '<?php echo ML_RICARDO_ARTICLES_FEE ?>',
			currency: '<?php echo ML_RICARDO_CURRENCY ?>',
			i18n: {
				ok: '<?php echo ML_BUTTON_LABEL_OK ?>',
				abort: '<?php echo ML_BUTTON_LABEL_ABORT ?>',
				process: '<?php echo ML_RICARDO_ARTICLE_FEE_CALCULATING ?>',
			}
		});

		return false;
	});
	
	$('#checkin_purge').click(function() {
		$(this).itemsFee({
			mode: '<?php echo $this->bDisplayLimitationWarning === true ? 'on' : 'off' ?>',
			addItems: okPurgeFunction,
			method: 'getItemsFee',
			message: '<?php echo ML_RICARDO_ARTICLES_FEE ?>',
			currency: '<?php echo ML_RICARDO_CURRENCY ?>',
			i18n: {
				ok: '<?php echo ML_BUTTON_LABEL_OK ?>',
				abort: '<?php echo ML_BUTTON_LABEL_ABORT ?>',
				process: '<?php echo ML_RICARDO_ARTICLE_FEE_CALCULATING ?>',
			}
		});

		return false;
	});
	
	$('#checkin_add_debug').click(function() { e = this; execSubmit(e); });
	
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
		
			$this->_url['cPath'] = isset($_GET['cPath']) ? $_GET['cPath'] : '';
			global $current_category_id;
			/* $current_category_id is a global variable from xt:Commerce */
			if (
				defined('MAGNA_DEV_PRODUCTLIST') 
				&& MAGNA_DEV_PRODUCTLIST === true 
				&& strpos(strtolower($this->views['checkinView']), 'productlist') !== false
			) {
				$aCV = new $this->views['checkinView']();
				echo $aCV;
			}else{
				$aCV = new $this->views['checkinView']($current_category_id, array(), isset($_GET['sorting']) ? $_GET['sorting'] : false, '');
				if ($this->isAjax) {
					return $aCV->renderAjaxReply();
				} else {
					$aCV->prependTopHTML($this->renderTemplateSelector());
					return $aCV->printForm();
				}
			}
		}
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
}
