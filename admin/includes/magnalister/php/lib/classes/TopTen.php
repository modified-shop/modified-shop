<?php
abstract class TopTen{
	/**
	 * id of current marketplace
	 * @var in $iMarketePlaceId 
	 */
	protected $iMarketPlaceId = null;
	/**
	 * setter
	 * @param int $iId 
	 */
	public function setMarketPlaceId($iId){
		$this->iMarketPlaceId = $iId;
	}
	abstract public function getTopTenCategories($sType, $aConfig = array());
	abstract public function configCopy();
	abstract public function configDelete($aDelete);
	abstract public function renderConfigDelete($aDelete = array());
	
	protected function getMarketPlaceType(){
		return substr(get_class($this), 0, strlen(get_class($this))-6);//6=strlen(topTen)
	}
	/**
	 * render main-config part and button for dialog + js
	 * @global type $_url
	 * @param string $sKey config-name
	 * @param int $iCurrentValue current config value
	 * @return string html 
	 */
	public function renderMain($sKey, $iCurrentValue){
		global $_url;
		ob_start();
		?>
			<select name="conf[<?php echo $sKey ?>]">
				<?php foreach(array(
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
				) as $iKey => $sValue){ ?>
					<option value="<?php echo $iKey.'"'.($iKey==$iCurrentValue?' selected="selected"':'') ?>"><?php echo $sValue ?></option>
				<?php } ?>
			</select>
			<input class="button" type="button" value="<?php echo ML_TOPTEN_MANAGE ?>" id="edit-topTen" />
			<script type="text/javascript">/*<!CDATA[*/
				jQuery(document).ready(function(){
					jQuery("#edit-topTen").click(function(){
						//create dialog
						var eDialog = jQuery('<div class="dialog2" title="<?php echo $this->getMarketPlaceType().' '.ML_TOPTEN_MANAGE_HEAD ?>"></div>');
						eDialog.bind('ml-init', function(event, argument){//behavior
							jQuery( this ).find('.successBox').each(function(){
								jQuery(this).fadeOut(5000);
							});
							jQuery( this ).find('button').button({'disabled':false});
							jQuery('.ui-widget-overlay').css({zIndex:1001, cursor:'auto'});
						});
						eDialog.bind('ml-load', function(event, argument){//behavior
							jQuery('.ui-widget-overlay').css({zIndex:99999, cursor:'wait'});
						});
						jQuery("body").append(eDialog);
						eDialog.jDialog({
							buttons: {},
							position: { my: "center center", at: "center top+80", of: window },
							close: function(event, ui){
								eDialog.remove();
							}
						});
						eDialog.trigger('ml-load');
						jQuery.ajax({
							method: 'get',
							url: '<?php echo toURL($_url, array('what' => 'topTenConfig', 'kind' => 'ajax'), true)?>',
							success: function (data) {
								//tabs
								var eData = jQuery(data);
								var eTabs = jQuery( eData ).find('.ml-tabs').andSelf();
								eTabs.tabs({
									beforeLoad: function(event, ui){
										if(jQuery.trim(ui.panel.html()) == ''){//have no content
											eDialog.trigger('ml-load');
											return true;
										}else{
											return false;
										}
									},
									load: function(event, ui){
										eDialog.trigger('ml-init');
										return true;
									}
								});
								eDialog.html(eData);
								jQuery(eDialog).on('submit', 'form', function(){
									var eForm = jQuery(this);
									jQuery(eData).find('button').button('option', 'disabled', true);
									eDialog.trigger('ml-load');
									jQuery.ajax({
										type: this.method,
										url: this.action,
										data: jQuery(this).serialize(),
										success: function (data) {
											if(eForm.attr('id') == 'ml-config-topTen-init-submit'){//clean all other loaded tabs, top ten have changed
												eTabs.find('[role=tabpanel][aria-hidden=true]').html('');
											}
											jQuery(eForm).parents('[role=tabpanel]').html(data);//fill curent tab
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
		$sOut=ob_get_contents();
		ob_end_clean();
		return $sOut;
	}	
	public function renderConfig(){
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
	public function renderConfigCopy($blExecute=false){
		global $_url;
		ob_start();
		if($blExecute){
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
}
