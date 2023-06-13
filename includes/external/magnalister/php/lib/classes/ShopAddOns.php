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
 * $Id: $
 *
 * (c) 2010 - 2015 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

class ML_ShopAddOns {

	/**
	 * Generates javascript popup for AddOn if it is needed
	 * @param $sSKU
	 * @param $sConfigRadioId
	 * @param $sConfigFormId
	 */
	public static function generateConfigPopup($sSKU, $sConfigRadioId, $sConfigFormId, $sType = 'radio') {
		global $_url;
		if (!self::mlAddOnIsBooked($sSKU)) {
			$aAddOnInfo = self::getAddOnInfo($sSKU);
			if (!is_array($aAddOnInfo) || !array_key_exists('DATA', $aAddOnInfo)) {
				return;
			}
			ob_start();?>
			<script type="text/javascript">/*<!CDATA[*/
				<?php if($sType == 'checkbox'){ ?>
				jQuery('input:checkbox[id="<?php echo $sConfigRadioId; ?>"]').change(function () {
					if (jQuery(this).prop('checked')) {
						var $checkbox = jQuery(this);
						jQuery('<div></div>').html(<?php echo json_encode($aAddOnInfo['DATA']['PluginText']['content']); ?>).jDialog({
							title: <?php echo json_encode($aAddOnInfo['DATA']['PluginText']['headline']); ?>,
							close: function(event, ui) {
								if (event.originalEvent) {
									$checkbox.prop('checked', false);
								}
							},
							buttons: {
								'abort': {
									text: '<?php echo ML_BUTTON_LABEL_ABORT; ?>',
									class: 'abort',
									click: function() {
										$checkbox.prop('checked', false);
										jQuery(this).dialog('close');
									}
								},
								'<?php echo ML_BUTTON_LABEL_ACCEPT_COSTS; ?>': function () {
									jQuery.blockUI(blockUILoading);
									jQuery.ajax({
										'method': 'get',
										'url': '<?php echo toURL($_url, array(
											'action' => 'extern',
											'function' => 'mlShopBookAnAddOn',
											'kind' => 'ajax',
											'SKU' => $sSKU,
										), true)?>',
										'success': function (data) {
											jQuery.unblockUI();
											myConsole.log('ajax.success', data);
											if (data.indexOf('<?php echo ML_ADDON_BOOK_SUCCESS; ?>') == -1) {
												$checkbox.prop('checked', false);
												jQuery('<div></div>').html(data).jDialog({
													title: '<?php echo ML_LABEL_NOTE; ?>'
												});
											} else {
												jQuery('<div></div>').html('<?php echo ML_ADDON_BOOK_SUCCESS; ?>').jDialog({
													title: '<?php echo ML_LABEL_NOTE; ?>',
													closeOnEscape: false,
													open: function(event, ui) { $(".ui-dialog-titlebar-close").hide(); },
													buttons: {
														'<?php echo ML_BUTTON_LABEL_OK; ?>': function () {
															jQuery('<?php echo $sConfigFormId; ?>').submit();
														}
													}
												});
											}
										}
									});
									jQuery(this).dialog('close');
								}
							}
						}).on("dialogclose", function(){
							$checkbox.prop('checked', false);
						});
					}
				});
				<?php 
			} else {
				?>
				jQuery('input:radio[id="<?php echo $sConfigRadioId; ?>_true"]').change(function () {
					if (jQuery(this).is(':checked') && jQuery(this).val() == 'true') {
						var $false = $('input:radio[id="<?php echo $sConfigRadioId; ?>_false"]');
						jQuery('<div></div>').html('<?php echo str_replace(array("\n", "\r","'"), array('','',"\\'"), utf8_decode($aAddOnInfo['DATA']['PluginText']['content'])); ?>').jDialog({
							title: '<?php echo utf8_decode($aAddOnInfo['DATA']['PluginText']['headline']); ?>',
							close: function(event, ui) {
								if (event.originalEvent) {
									$false.attr('checked', 'checked');
								}
							},
							buttons: {
								'abort' : {
									text: '<?php echo ML_BUTTON_LABEL_ABORT; ?>',
									class: 'abort',
									click: function () {
										$false.attr('checked', 'checked');
										jQuery(this).dialog('close');
									}
								},
								'<?php echo ML_BUTTON_LABEL_ACCEPT_COSTS; ?>': function () {
									jQuery.blockUI(blockUILoading);
									jQuery.ajax({
										'method': 'get',
										'url': '<?php echo toURL($_url, array(
											'action' => 'extern',
											'function' => 'mlShopBookAnAddOn',
											'kind' => 'ajax',
											'SKU' => $sSKU,
										), true)?>',
										'success': function (data) {
											jQuery.unblockUI();
											myConsole.log('ajax.success', data);
											if (data.indexOf('<?php echo ML_ADDON_BOOK_SUCCESS; ?>') == -1) {
												$false.attr('checked', 'checked');
												jQuery('<div></div>').html(data).jDialog({
													title: '<?php echo ML_LABEL_NOTE; ?>'
												});
											} else {
												jQuery('<div></div>').html('<?php echo ML_ADDON_BOOK_SUCCESS; ?>').jDialog({
													title: '<?php echo ML_LABEL_NOTE; ?>',
													closeOnEscape: false,
													open: function(event, ui) { $(".ui-dialog-titlebar-close").hide(); },
													buttons: {
														'<?php echo ML_BUTTON_LABEL_OK; ?>': function () {
															jQuery('<?php echo $sConfigFormId; ?>').submit();
														}
													}
												});
											}
										}
									});
									jQuery(this).dialog('close');
								}
							}
						}).on("dialogclose", function(){
							$false.attr('checked', 'checked');
						});
					}
				});
			<?php } ?>
				/*]]>*/</script><?php
			$html = ob_get_contents();
			ob_end_clean();
			echo $html;
		}
	}

	public static function generateConfigPopupOnCombobox($sSKU, $sConfigComboboxId, $sConfigFormId, $JSConstraint)
	{
		global $_url;
		if (!self::mlAddOnIsBooked($sSKU)) {
			$aAddOnInfo = self::getAddOnInfo($sSKU);
			ob_start(); ?>
			<script type="text/javascript">/*<!CDATA[*/
				(function () {
					var previousValue;
					jQuery('#<?php echo $sConfigComboboxId; ?>').on('focus', function () {
						previousValue = this.value;
					}).change(function () {
						if (<?= $JSConstraint ?>) {
							var currentComponent = $('#<?php echo $sConfigComboboxId; ?>');
							jQuery('<div></div>').html('<?php echo str_replace(array("\n", "\r", "'"), array('', '', "\\'"), $aAddOnInfo['DATA']['PluginText']['content']); ?>')
								.jDialog({
									title: '<?php echo $aAddOnInfo['DATA']['PluginText']['headline']; ?>',
									close: function (event, ui) {
                                    if (event.originalEvent) {
										currentComponent.val(previousValue);
                                    }
									},
									buttons: {
										'abort': {
											text: '<?php echo ML_BUTTON_LABEL_ABORT; ?>',
											class: 'abort',
											click: function () {
												currentComponent.val(previousValue);
												jQuery(this).dialog('close');
											}
										},
										'<?php echo ML_BUTTON_LABEL_ACCEPT_COSTS; ?>': function () {
											jQuery.blockUI(blockUILoading);
											jQuery.ajax({
												'method': 'get',
												'url': '<?php echo toURL($_url, array(
													'action' => 'extern',
													'function' => 'mlShopBookAnAddOn',
													'kind' => 'ajax',
													'SKU' => $sSKU,
												), true)?>',
												'success': function (data) {
													jQuery.unblockUI();
													if (data.indexOf('<?php echo ML_ADDON_BOOK_SUCCESS; ?>') == -1) {
														previousValue = $(currentComponent).val();
														jQuery('<div></div>').html(data).jDialog({
															title: '<?php echo ML_LABEL_NOTE; ?>'
														});
													} else {
														jQuery('<div></div>').html('<?php echo ML_ADDON_BOOK_SUCCESS; ?>').jDialog({
															title: '<?php echo ML_LABEL_NOTE; ?>',
															closeOnEscape: false,
															open: function (event, ui) {
																$(".ui-dialog-titlebar-close").hide();
															},
															buttons: {
																'<?php echo ML_BUTTON_LABEL_OK; ?>': function () {
																	jQuery('<?php echo $sConfigFormId; ?>').submit();
																	jQuery(this).dialog('close');
																}
															}
														});
													}
												}
											});
											jQuery(this).dialog('close');
										}
									}
								});
						}
					});
				})();

				/*]]>*/</script><?php
			$html = ob_get_contents();
			ob_end_clean();
			echo $html;
		}
	}

	public static function getAddOnInfo($sSKU) {
		try {
			$aResponse = MagnaConnector::gi()->submitRequest(array(
					'SKU' => $sSKU,
					'SUBSYSTEM' => 'Core',
					'ACTION' => 'GetAddonInfo',
				)
			);
		} catch (MagnaException $e) {
			return false;
		}
		if (!array_key_exists('DATA', $aResponse)) {
			return false;
		}
		#echo print_m($aResponse, 'response');
		return $aResponse;
	}

	public static function bookAddOn($sSKU = '') {
		try {
			$aResult = MagnaConnector::gi()->submitRequest(array(
				'SUBSYSTEM' => 'Core',
				'ACTION' => 'AddAddon',
				'SKU' => $sSKU,
				'CHANGE_TARIFF' => true,
			));
			loadMaranonCacheConfig(true);
		} catch (Exception $oEx) {
			return $oEx->getMessage();
		}

		if ($aResult['STATUS'] == 'SUCCESS') {
			return ML_ADDON_BOOK_SUCCESS;
		} else {
			$aErrorMessages = array();
			foreach ($aResult['ERRORS'] as $aError) {
				$aErrorMessages .= $aError['ERRORMESSAGE'];
			}
			return implode('<br>', $aErrorMessages);
		}
	}

	/**
	 * Checking of AddOn is booked by customers shop (GetShopInfo should return this data)
	 * @param $sSKU - SKU of AddOn
	 * @return bool - if AddOn is booked it returns true otherwise false
	 */
	public static function mlAddOnIsBooked($sSKU) {
		global $magnaConfig;

		if (isset($magnaConfig['maranon']['Addons'])) {
			foreach ($magnaConfig['maranon']['Addons'] as $aAddOn) {
				if ($aAddOn['SKU'] == $sSKU) {
					return true;
				}
			}
		}

		return false;
	}
}
