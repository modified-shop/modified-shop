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
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
require_once(DIR_MAGNALISTER_INCLUDES . 'lib/classes/productIdFilter/AbstractProductIdFilter.php');

abstract class DeletedInMarketplaceFilter extends AbstractProductIdFilter {

	/**
	 * filter-value comes from request
	 * @var string 
	 */
	protected $sFilter = null;

	/**
	 * use cache, or ever do api sync.
	 * cache time is defined in session
	 * @var bool
	 */
	protected $blCache = true;

	/**
	 * previous setted product ids
	 * @var array
	 */
	protected $aCurrentIds = null;
	
	protected $iLimit = 500;

	abstract protected function getPropertiesTableName();

	public function getUrlParams() {
		return array(get_class($this) => $this->sFilter);
	}

	public function __construct() {
		if (isset($_POST[get_class($this)])) {
			$sFilterBy = $_POST[get_class($this)];
		} elseif (isset($_GET[get_class($this)])) {
			$sFilterBy = $_GET[get_class($this)];
		} else {
			$sFilterBy = null;
		}
		$this->sFilter = ($sFilterBy == '') ? null : $sFilterBy;
	}

	public function isActive() {
		return $this->sFilter !== null;
	}
	
	public function canExecute($sFilter) {
		global $_MagnaSession;
		$mpID = $_MagnaSession['mpID'];
		return 
		    !isset($_SESSION['magna_deletedFilter'][$mpID][$sFilter])
			||
			$_SESSION['magna_deletedFilter'][$mpID][$sFilter] + 1800 < time()
			||
			!$this->blCache
		;
	}
	
	/*
	 * Helper function for getHtml
	 * (child classes can define it differently)
	 */
	protected function getFilterValues() {
		return array(
			'' => ML_OPTION_FILTER_ARTICLES_ALL,
			'notActive' => ML_OPTION_FILTER_ARTICLES_NOTACTIVE,
			'notTransferred' => ML_OPTION_FILTER_ARTICLES_NOTTRANSFERRED,
			'active' => ML_OPTION_FILTER_ARTICLES_ACTIVE,
			'sync' => ML_OPTION_FILTER_ARTICLES_DELETEDBY_SYNC,
			'button' => ML_OPTION_FILTER_ARTICLES_DELETEDBY_BUTTON,
			'expired' => ML_OPTION_FILTER_ARTICLES_DELETEDBY_EXPIRED,
		);
	}

	public function getHtml() {
		global $_MagnaSession;
		global $_url;
		
		ob_start();
		?>
		<form id="<?php echo get_class($this); ?>" name="<?php echo get_class($this); ?>" method="POST" action="<?php echo toURL(array('mp' => $_url['mp']), array('mode' => $_url['mode'])); ?>">
			<input type="hidden" name="timestamp" value="<?php echo time(); ?>" />
			<input type="hidden" name="filter" value="<?php echo get_class($this); ?>" />
			<select name="<?php echo get_class($this); ?>">
				<?php
				foreach ($this->getFilterValues() as $sKey => $sI18n) {
					?>
					<option value="<?php echo $sKey; ?>"<?php echo($this->sFilter != null && ($this->sFilter == $sKey) ? ' selected="selected"' : '') ?>>
						<?php echo sprintf($sI18n, constant('ML_MODULE_' . strtoupper($_MagnaSession['currentPlatform']))); ?>
					</option>
				<?php } ?>
			</select>
		</form>
		<?php if ($this->canExecute('deleted') || $this->canExecute('default')){ ?>
			<div  style="margin:2em 1em; width:100px" id="<?php echo get_class($this); ?>Dialog" class="dialog2" title="<?php echo ML_STATUS_FILTER_SYNC_ITEM; ?>">
				<?php if ($this->canExecute('deleted')){ ?>
					<div class="progressBarContainer" data-step="deleted" style="margin-bottom:1em">
						<div class="progressBar" style="width: 0;"></div>
						<div class="progressPercent">0%</div>
					</div>
				<?php } ?>
				<?php if ($this->canExecute('default')){ ?>
					<div class="progressBarContainer" data-step="default" style="margin-bottom:1em">
						<div class="progressBar" style="width: 0;"></div>
						<div class="progressPercent">0%</div>
					</div>
				<?php } ?>
				<p class="successBoxBlue"><?php echo ML_STATUS_FILTER_SYNC_CONTENT; ?></p>
				<p class="successBox" style="display:none"><?php echo STATUS_FILTER_SYNC_SUCCESS; ?></p>
			</div>
			<script type="text/javascript">/*<![CDATA[*/
				function mlUpdateFilterData(theform) {
					var iLimit = <?php echo $this->iLimit; ?>;
					var iStep = 0;
					var iOffset = 0;
					var blNext = true;
					var eForm = theform;
					var iInterval = 500;
					var eDialog = $("#<?php echo get_class($this); ?>Dialog");
					var fDebug = function() {
						// console.log(mLog);
					};
					var fAjax = function() {
						fDebug([iLimit, iOffset, async2sync]);
						if (blNext) {
							fDebug("next");
							blNext = false;
							var iTime = new Date().getTime();
							var sCurrentStep = "";
							oSteps = eDialog.find(".progressBarContainer .progressPercent");
							oSteps.each(function() {
								if (parseInt($(this).text().replace("%", "")) < 100 && sCurrentStep === "") {
									sCurrentStep = $(this).parent().attr("data-step");
								}
							});
							$.ajax({
								url: $(eForm).attr("action") + "&kind=ajax",
								type: $(eForm).attr("method"),
								data: $(eForm).serialize() + "&limit=" + iLimit + "&offset=" + iOffset + "&step=" + sCurrentStep,
								success: function(data) {
									var json = $.parseJSON(data);
									if (typeof json.success !== "undefined") {
										var iDuration = 500;
										iOffset = 0;
										iLimit = <?php echo $this->iLimit; ?>;
										iStep++;
									}
									if (typeof json.success !== "undefined" && iStep === oSteps.length) {
										eDialog.find(".successBoxBlue").css("display", "none");
										eDialog.find(".successBox").css("display", "block");
										window.clearInterval(async2sync);
										jQuery.blockUI(blockUILoading);
										var fPercent = 100;
										eForm.submit();
									} else {
										blNext = true;
										if (typeof json.info !== "undefined") {
											var fPercent = (json.info.current / json.info.total) * 100;
											iLimit = json.params.limit;
											iOffset = json.params.offset;
										} else {
											var fPercent = 100;
										}
										var iDuration = new Date().getTime() - iTime;
									}
									eDialog.find("[data-step=\"" + sCurrentStep + "\"] .progressBar").css({
										width: fPercent + "%",
										transitionDuration: iDuration + "ms"
									});
									eDialog.find("[data-step=\"" + sCurrentStep + "\"] .progressPercent").html(Math.round(fPercent) + "%");
								},
								beforeSend: function() {
									if (eDialog.is(":hidden")) {
										fDebug("show");
										eDialog.jDialog({
											buttons: {},
											height: "auto",
											width: 400
										});
									}
								}
							});
						}
					};
					/**
					 * using interval to emulate synchronous (a)jax - problems with webkit-browser
					 * @see http://bugs.jquery.com/ticket/8819
					 */
					var async2sync = window.setInterval(function() {
						fAjax();
					}, iInterval);
				}
				$(document).ready(function() {
					$("form#<?php echo get_class($this); ?>").change(function() {
						mlUpdateFilterData(this);
					});
				});
			/*]]>*/</script>
		<?php } else { ?>
			<script type="text/javascript">/*<![CDATA[*/
				$(document).ready(function() {
					$("form#<?php echo get_class($this); ?>").change(function() {
						this.submit();
					});
				});
			/*]]>*/</script>
		<?php } ?>
		<?php
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}

	public function setCurrentIds($aIds) {
		$this->aCurrentIds = $aIds;
	}

	public function getProductIds() {
		global $_MagnaSession;
		
		$sSql = "
		    SELECT DISTINCT p.products_id
		      FROM " . TABLE_PRODUCTS . " p
		 LEFT JOIN " . $this->getPropertiesTableName() . " ep on " . ((getDBConfigValue('general.keytype', '0') == 'artNr')
			? 'p.products_model = ep.products_model'
			: 'p.products_id = ep.products_id'
		) . "
		     WHERE ep.mpID = '" . $_MagnaSession['mpID'] . "' 
		";
		
		switch (strtolower($this->sFilter)) {
			case 'notactive' : {
				$sSql .= " AND ep.Verified in('OK', 'EMPTY') AND (ep.transferred='0' or ep.deletedBy!='')";
				break;
			}
			case 'nottransferred' : {
				$sSql .= " AND ep.Verified in('OK', 'EMPTY') AND ep.transferred='0'";
				break;
			}
			case 'active': {
				$sSql .= " AND ep.Verified in('OK', 'EMPTY') AND (ep.transferred='1' and ep.deletedBy='')";
				break;
			}
			case 'sync':
			case 'button':
			case 'expired': {
				$sSql .= " AND ep.Verified in('OK', 'EMPTY') AND ep.deletedBy='" . $this->sFilter . "'";
				break;
			}
			default: { // not possible value
				return array();
			}
		}
		return MagnaDB::gi()->fetchArray($sSql, true);
	}

	public function init($aConfig) {
		if (
			isset($_GET['kind']) && $_GET['kind'] == 'ajax' 
			&& isset($_POST['filter']) && $_POST['filter'] == get_class($this)
		) {
			try {
				$this->apiRequest(
					$_POST['step'] == 'default'
						? null 
						: strtoupper($_POST['step']), 
					(int) $_POST['offset'], 
					(int) $_POST['limit']
				);
				echo json_encode(array('success' => true));
			} catch (Exception $oEx) {
				$oInfo = json_decode($oEx->getMessage());
				echo json_encode($oInfo);
			}
			exit();
		}
		return $this;
	}
	
	protected function updatePropertiesTable($pID, $data) {
		if ($pID == 0) {// product exists
			return;
		}
		$data['transferred'] = 1; //todo check if depends on entry exists
		global $_MagnaSession;
		$mpID = $_MagnaSession['mpID'];
		if( MagnaDB::gi()->recordExists($this->getPropertiesTableName(), array('products_id' => $pID, 'mpID' => $mpID))) {
			MagnaDB::gi()->update(
				$this->getPropertiesTableName(), 
				$data, 
				array(
					'products_id' => $pID,
					'mpID' => $mpID
				),
				'LIMIT 1'
			);
		} else {
			$products_model = MagnaDB::gi()->fetchOne('SELECT products_model FROM '.TABLE_PRODUCTS.' WHERE products_id = '.$pID.'');
			$data['products_id'] = $pID;
			$data['products_model'] = $products_model;
			$data['Verified'] = 'EMPTY';
			$data['mpID'] = $mpID;
			MagnaDB::gi()->insert(
				$this->getPropertiesTableName(), 
				$data
			);
		}
	}
	
	protected function apiRequest($sFilter = null, $offset = 0, $limit = 50) {
		global $_MagnaSession;
		$mpID = $_MagnaSession['mpID'];
		if (strtolower($sFilter) == 'deleted') {
			if (
				(
					$offset != 0
					||
					$this->canExecute('deleted')
				)
				&&
				in_array(strtolower($this->sFilter), array('sync', 'button', 'expired'))
			) {
				$_SESSION['magna_deletedFilter'][$mpID]['deleted'] = time();
				try {
					$request = array(
						'ACTION' => 'GetInventory',
						'SUBSYSTEM' => $_MagnaSession['currentPlatform'],
						'MARKETPLACEID' => $mpID,
						'LIMIT' => $limit,
						'OFFSET' => $offset,
						'ORDERBY' => 'DateAdded',
						'SORTORDER' => 'DESC',
						'FILTER' => 'DELETED',
					);
					$result = MagnaConnector::gi()->submitRequest($request);
					if (!empty($result['DATA'])) {
						if((int)$offset == 0) {
							MagnaDb::gi()->query("OPTIMIZE TABLE ".$this->getPropertiesTableName());
						}
						foreach ($result['DATA'] as $item) {
							if (!empty($item['MasterSKU'])) {
								$pID = magnaSKU2pID($item['MasterSKU']);
							} else {
								$pID = magnaSKU2pID($item['SKU']);
							}
							$this->updatePropertiesTable(
								$pID, 
								array(
									'deletedBy' => $item['deletedBy'],
								)
							);
						}
					}
					$numberofitems = (int) $result['NUMBEROFLISTINGS'];
					if (($numberofitems - $offset - $limit) > 0) { //recursion
						$offset += $limit;
						$limit = (($offset + $limit) >= $numberofitems) ? $numberofitems - $offset : $limit;
						throw new Exception(json_encode(array(
							'params' => array(
								'offset' => $offset,
								'limit' => $limit,
							),
							'info' => array(
								'current' => $offset,
								'total' => $numberofitems,
							)
						)));
					}
				} catch (MagnaException $e) {
					#echo $e->getMessage();
				}
			}
		} else {
		    if ($this->canExecute('default') || $offset != 0) {	
				$_SESSION['magna_deletedFilter'][$mpID]['default'] = time();
				try {
					if ((int)$offset == 0) {
						MagnaDb::gi()->query("OPTIMIZE TABLE ".$this->getPropertiesTableName());
						// set all articles as deleted, after api-request they should be correct not-deleted-value
						MagnaDB::gi()->query("
							UPDATE ".$this->getPropertiesTableName()."
							SET deletedBy = 'notML' 
							WHERE 
								deletedBy = '' 
								AND mpID = '".$mpID."'
						");
					}
					$request = array(
						'ACTION' => 'GetInventoryOnlySKUs',
						'SUBSYSTEM' => $_MagnaSession['currentPlatform'],
						'MARKETPLACEID' => $mpID,
					);
					$result = MagnaConnector::gi()->submitRequest($request);
					if (!empty($result['DATA'])) {
						foreach ($result['DATA'] as $iCount => $item) {
							if ($iCount < $offset ) {
								continue;
							}
							if ($iCount > $offset+$limit) {
								break;
							}
							$pID = magnaSKU2pID($item);
							$this->updatePropertiesTable(
								$pID, 
								array(
									'deletedBy' => '',
								)
							);
						}
						$numberofitems = count($result['DATA']);
						if ($numberofitems - $offset - $limit > 0) { //recursion
							$offset += $limit;
							$limit = (($offset + $limit) >= $numberofitems) ? $numberofitems - $offset : $limit;
							throw new Exception(json_encode(array(
								'params' => array(
									'offset' => $offset,
									'limit' => $limit,
								),
								'info' => array(
									'current' => $offset,
									'total' => $numberofitems,
								)
							)));
						}
					}
				} catch (MagnaException $e) {
					#echo $e->getMessage();
				}
			}
		}
	}

}
