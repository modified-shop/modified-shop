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

abstract class TopTen {
	/**
	 * id of current marketplace
	 * @var in $iMarketePlaceId 
	 */
	protected $iMarketPlaceId = 0;
	protected $marketplace = '';
	
	/**
	 * setter
	 * @param int $iId 
	 */
	public function setMarketPlaceId($iId) {
		$this->iMarketPlaceId = $iId;
		$this->marketplace = magnaGetMarketplaceByID($this->iMarketPlaceId);
	}
	
	abstract protected function getTableName();
	abstract public function getTopTenCategories($sType, $aConfig = array());
	abstract public function configCopy();
	
	protected function getMarketPlaceType() {
		global $_modules;
		$className = get_class($this);
		return isset($_modules[$this->marketplace]['title'])
			? $_modules[$this->marketplace]['title']
			: substr($className, 0, strpos($className, 'TopTen'));
	}
	
	abstract protected function getResettableCategoryDescription();
	abstract protected function getResettableCategoryDefinition();
	
	public function configDelete($aDelete) {
		$fields = $this->getResettableCategoryDefinition();
		foreach ($aDelete as $sKey => $aValue) {
			if (empty($aValue)) {
				continue;
			}
			if (isset($fields[$sKey])) {
				MagnaDB::gi()->query('
					UPDATE '.$this->getTableName().'
					   SET '.$fields[$sKey].' = ""
					 WHERE '.$fields[$sKey].' IN ("'.implode('", "', $aValue).'")
				');
			}
		}
	}
	
	public function renderConfigDelete($aDelete = array()) {
		global $_url;
		
		ob_start();
		
		if (count($aDelete) > 0) {
			$this->configDelete($aDelete);
			echo '<p class="successBox">'.ML_TOPTEN_DELETE_INFO.'</p>';
		}
		
		$aCats = array();
		foreach ($this->getResettableCategoryDescription() as $sType => $sName) {
			try {
				$aCats[$sName] = array(
					'type' => $sType,
					'data' => $this->getTopTenCategories($sType)
				);
			} catch (Exception $oEx) {
				//do nothing
			}
		}
		
		?>
			<form method="post" action="<?php echo toURL($_url, array('what' => 'topTenConfig', 'kind' => 'ajax'))?>&amp;tab=delete">
				<p><?php echo ML_TOPTEN_DELETE_DESC ?></p>
				<dl>
					<?php foreach($aCats as $sName => $aTopTenCatIds) { ?>
						<dt><?php echo $sName ?></dt>
						<dd>
							<select name="delete[<?php echo $aTopTenCatIds['type']; ?>][]" style="width:100%" multiple="multiple" size="5">
							<?php foreach ($aTopTenCatIds['data'] as $sKey => $sValue) { ?>
								<option value="<?php echo $sKey ?>"><?php echo strip_tags($sValue); ?></option>
							<?php } ?>
							</select>
						</dd>
					<?php } ?>
				</dl>
				<button type="submit"><?php echo ML_TOPTEN_DELETE_HEAD; ?></button>
			</form>
		<?php
		$sOut = ob_get_contents();
		ob_end_clean();
		return $sOut;
	}
	
	/**
	 * render main-config part and button for dialog + js
	 * @global type $_url
	 * @param string $sKey config-name
	 * @param int $iCurrentValue current config value
	 * @return string html 
	 */
	public function renderMain($sKey, $iCurrentValue) {
		global $_url;
		ob_start();
		
		echo '
			<select name="conf['.$sKey.']">';
		foreach (array (
			10  => '10',
			20  => '20',
			30  => '30',
			40  => '40',
			50  => '50',
			60  => '60',
			70  => '70',
			80  => '80',
			90  => '90',
			100 => '100',
			0   => 'Alle',
		) as $iKey => $sValue) {
			echo '
					<option value="'.$iKey.'"'.(($iKey == $iCurrentValue) ? ' selected="selected"' : '').'>'.$sValue.'</option>';
		}
		echo '
			</select>';
		?>
			<input class="ml-button" type="button" value="<?php echo ML_TOPTEN_MANAGE ?>" id="edit-topTen" />
			<script type="text/javascript">/*<!CDATA[*/
								jQuery(document).ready(function () {
					jQuery("#edit-topTen").click(function () {
						// create dialog
						var eDialog = jQuery('<div class="dialog2" title="<?php echo $this->getMarketPlaceType().' '.ML_TOPTEN_MANAGE_HEAD; ?>"></div>');
						eDialog.bind('ml-init', function (event, argument) { // behavior
							jQuery(this).find('.successBox').each(function () {
								jQuery(this).fadeOut(5000);
							});
							jQuery(this).find('button').button({
								'disabled': false
							});
							jQuery('.ui-widget-overlay').css({
								zIndex: 1001,
								cursor: 'auto'
							});
						});
						eDialog.bind('ml-load', function (event, argument) { // behavior
							jQuery('.ui-widget-overlay').css({
								zIndex: 99999,
								cursor: 'wait'
							});
						});
						jQuery("body").append(eDialog);
						eDialog.jDialog({
							buttons: {},
							position: {
								my: "center center",
								at: "center top+80",
								of: window
							},
							close: function (event, ui) {
								eDialog.remove();
							}
						});
						eDialog.trigger('ml-load');
						jQuery.ajax({
							method: 'get',
							url: '<?php echo toURL($_url, array('what' => 'topTenConfig', 'kind' => 'ajax'), true); ?>',
							success: function (data) {
								//tabs
								var eData = jQuery(data);
								var eTabs = jQuery(eData).find('.ml-tabs').andSelf();
								eTabs.tabs({
									beforeLoad: function (event, ui) {
										if (jQuery.trim(ui.panel.html()) == '') { // have no content
											eDialog.trigger('ml-load');
											return true;
										} else {
											return false;
										}
									},
									load: function (event, ui) {
										eDialog.trigger('ml-init');
										return true;
									}
								});
								eDialog.html(eData);
								jQuery(eDialog).on('submit', 'form', function () {
									var eForm = jQuery(this);
									jQuery(eData).find('button').button('option', 'disabled', true);
									eDialog.trigger('ml-load');
									jQuery.ajax({
										type: this.method,
										url: this.action,
										data: jQuery(this).serialize(),
										success: function (data) {
											if (eForm.attr('id') == 'ml-config-topTen-init-submit') { // clean all other loaded tabs, top ten have changed
												eTabs.find('[role=tabpanel][aria-hidden=true]').html('');
											}
											jQuery(eForm).parents('[role=tabpanel]').html(data); // fill curent tab
											eDialog.trigger('ml-init');
										}
									});
									return false;
								});
							}
						});
					});
				});
			/*]]>*/</script>
		<?php
		$sOut = ob_get_contents();
		ob_end_clean();
		return $sOut;
	}
	
	public function renderConfig() {
		global $_url;
		ob_start();
		?>
			<div id="ml-config-topTen" class="ml-tabs">
				<ul>
					<li>
						<a href="<?php echo toURL($_url, array('what' => 'topTenConfig', 'kind' => 'ajax'), true)?>&tab=delete"><?php echo ML_TOPTEN_DELETE_HEAD ?></a>
					</li>
					<li>
						<a href="<?php echo toURL($_url, array('what' => 'topTenConfig', 'kind' => 'ajax'), true)?>&tab=init"><?php echo ML_TOPTEN_INIT_HEAD ?></a>
					</li>
				</ul>
			</div>
		<?php
		$sOut = ob_get_contents();
		ob_end_clean();
		return $sOut;
	}
	
	public function renderConfigCopy($blExecute = false) {
		global $_url;
		ob_start();
		if ($blExecute) {
			$this->configCopy();
			?><p class="successBox"><?php echo ML_TOPTEN_INIT_INFO ?></p><?php
		}
		?>
			<p><?php echo ML_TOPTEN_INIT_DESC ?></p>
			<form id="ml-config-topTen-init-submit" method="get" action="<?php echo toURL($_url, array('what' => 'topTenConfig', 'kind' => 'ajax'), true)?>&tab=init&execute=true">
				<button type="submit" ><?php echo ML_TOPTEN_INIT_HEAD ?></button>
			</form>
		<?php
		$sOut = ob_get_contents();
		ob_end_clean();
		return $sOut;
	}
	
	public function renderTopTenConfig($aArgs = array(), &$sValue = '') {
		if (isset($_GET['what'])) {
			if(!isset($_GET['tab'])) {
				echo $this->renderConfig();
			} elseif ($_GET['tab'] == 'init') {
				echo $this->renderConfigCopy(isset($_GET['execute']) && ($_GET['execute'] == 'true'));
			} elseif ($_GET['tab'] == 'delete') {
				echo $this->renderConfigDelete(($_POST['delete']) ? $_POST['delete'] : array());
			}
		} else {
			return $this->renderMain(
				$aArgs['key'],
				isset($_POST['conf'][$aArgs['key']])
					? (int)$_POST['conf'][$aArgs['key']]
					: (int)getDBConfigValue($aArgs['key'], $this->iMarketPlaceId, 10)
			);
		}
	}
	
	protected static function runRenderConfigForm($topTen, $method, $args = array(), &$value = '') {
		global $_MagnaSession, $_url;
		
		$_url['action'] = 'extern';
		$_url['function'] = $method;
		
		$topTen->setMarketPlaceId($_MagnaSession['mpID']);
		return $topTen->renderTopTenConfig($args, $value);
	}
	
	/**
	 * Each child class should have a method like this.
	 * However abstract static function is not allowed in PHP
	 */
	//abstract public static function renderConfigForm($args, &$value = '');
}
