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

class RicardoShippingDetailsProcessor {
	private $args = array();
	private $mainKey = '';
	private $mpID = 0;
	private $url = array();

	public function __construct($args, $mainKey, $url) {
		global $_MagnaSession;

		$this->args = $args;
		$this->mpID = $_MagnaSession['mpID'];
		$this->mainKey = $mainKey;
		$this->url = $url;
	}

	protected static function getShippings() {
		$result = MagnaConnector::gi()->submitRequest(array('ACTION' => 'GetDeliveryTypes'));
		return $result['DATA'];
	}

	protected static function getPackageSizes($shippingId) {
		$result = MagnaConnector::gi()->submitRequest(array(
			'ACTION' => 'GetPackageSize',
			'DATA' => array(
				'DeliveryType' => $shippingId,
			),
		));

		return (is_array($result['DATA']) && count($result['DATA']) > 1) ? $result['DATA'] : null;
	}

	public function renderView($shippingDefault = '') {
		$nameKey = 'conf[' . $this->args['key'] . ']';
		$shippings = self::getShippings();
		$shippingByDescription = '';

		$idkey = 'config_' . (isset($this->args['key']) ? str_replace('.', '_', $this->args['key']) : '');

		$shippingSelect = '<select id="' . $idkey . '" name="' . $nameKey . '">' . "\n";
		foreach ($shippings as $key => $shipping) {
			if ($key == 0) {
				$shippingByDescription = $shipping;
			}

			$shippingSelect .= '<option value="' . $key . '"' . (
				($shippingDefault == $key)
					? ' selected="selected"'
					: ''
			) . '>' . $shipping . '</option>' . "\n";
		}
		$shippingSelect .= '</select>';

		if (isset($this->args['content']['PackageSize']) === true) {
			// From prepare form
			$packageSize = $this->getPackageSize($shippingDefault, $this->args['content']['PackageSize']);
		} else if (is_array($shippingDefault) === true) {
			// Doesn't have value in DB
			$packageSize = $this->getPackageSize(1, -1);
		} else {
			// Has value in DB
			$packageSize = $this->getPackageSize($shippingDefault);
		}

		if (isset($this->args['content']['ShippingCost']) === true) {
			$shippingCost = $this->getShippingCost($this->args['content']['ShippingCost']);
		} else {
			$shippingCost = $this->getShippingCost();
		}

		if (isset($this->args['content']['ShippingCumulative']) === true) {
			$shippingCumulative = $this->getShippingCumulative($this->args['content']['ShippingCumulative']);
		} else {
			$shippingCumulative = $this->getShippingCumulative();
		}

		$shippingDesc = $this->getShippingDesc();

		ob_start();?>
		<script type="text/javascript">/*<![CDATA[*/
			$(document).ready(function() {
				var shippingId = jQuery('#<?php echo $idkey ?>').val();
				toggleShippingDesc(shippingId);

				$('#<?php echo $idkey ?>').change(function () {
					jQuery.blockUI(blockUILoading);
					var shippingId = jQuery(this).val();
					var postData = <?php echo json_encode(array_merge(
							$this->args,
							array (
								'action' => 'extern',
								'function' => __CLASS__.'::shippingDetails',
								'kind' => 'ajax',
								'func' => 'packageSize'
							)
						)); ?>;
					postData.shippingId = shippingId;

					toggleShippingDesc(shippingId);

					jQuery.ajax({
						type: 'POST',
						url: '<?php echo toURL($this->url, array('kind' => 'ajax'), true) ?>',
						data: postData,
						success: function(data) {
							jQuery.unblockUI();
							jQuery('#<?php echo $packageSize['id'] ?>').replaceWith(data);
						},
						error: function (xhr, status, error) {
							jQuery.unblockUI();
						},
						dataType: 'html'
					});
				});

				var paymentOptions = $('#config_ricardo_checkin_paymentdetails');
				if (paymentOptions.length === 0) {
					paymentOptions = $('#config_ricardo_paymentdetails');
				}


				var payments = paymentOptions.val();
				var opts = document.getElementById('<?php echo $idkey ?>').options;
				toggleRemoveShippingByDesc(payments, opts);

				paymentOptions.change(function () {
					var payments = $(this).val();
					var opts = document.getElementById('<?php echo $idkey ?>').options;
					toggleRemoveShippingByDesc(payments, opts);
				});
			});

			function toggleShippingDesc(shippingId) {
				if (shippingId === "0") {
					jQuery('#<?php echo $shippingDesc['id'] ?>').show();
				} else {
					jQuery('#<?php echo $shippingDesc['id'] ?>').hide();
				}
			}
			
			function toggleRemoveShippingByDesc(payments, opts) {
				var exists = false;
				for(var i = 0; i < opts.length; ++i) {
					if( opts[i].value === '0' ) {
					   exists = true;
					   break;
					}
				}

				if (typeof(payments) !== 'undefined' && payments !== null && payments.indexOf('262144') !== -1) {
					$("#<?php echo $idkey ?> option[value='0']").remove();
				} else if (exists === false) {
					$("#<?php echo $idkey ?>").append('<option value="0"><?php echo $shippingByDescription ?></option>');
				}
			}

		/*]]>*/</script><?php
		$buyingModeContent = $shippingSelect . '&nbsp' . $packageSize['select'] . '&nbsp' . $shippingCost['text'] . '<br/>' . $shippingCumulative['checkbox'] . '<br/>' . $shippingDesc['table'];
		$buyingModeContent .= ob_get_contents();
		ob_end_clean();

		return $buyingModeContent;
	}

	protected function getPackageSize($shippingId, $packageSizeDefault = null) {
		$packageSizeKeys = $this->args['key'] . '.packagesize';
		$packageSizeNameKey = 'conf[' . $packageSizeKeys . ']';
		$packageSizeIdKey = 'config_' . (isset($packageSizeKeys) ? str_replace('.', '_', $packageSizeKeys) : '');

		$packageSizeSelect = null;

		if ($packageSizeDefault === null) {
			$packageSizeDefault = getDBConfigValue($packageSizeKeys, $this->mpID);
		}

		$packageSizes = $this->getPackageSizes($shippingId);
		if ($packageSizes !== null) {
			$packageSizeSelect = '<select style="margin-top: 3px 3px 0 0;" id="' . $packageSizeIdKey . '" name="' . $packageSizeNameKey . '">' . "\n";
			foreach ($packageSizes as $key => $packageSize) {
				$packageSizeSelect .= '<option value="' . $key . '"' . (
					($packageSizeDefault == $key)
						? ' selected="selected"'
						: ''
				).'>'.$packageSize.'</option>'."\n";
			}
			$packageSizeSelect .= '</select>';
		} else {
			$packageSizeSelect = '<select id="' . $packageSizeIdKey . '" style="display: none;"></select>';
		}

		return array(
			'select' => $packageSizeSelect,
			'id' => $packageSizeIdKey,
			'key' => $packageSizeKeys
		);
	}

	protected function getShippingCost($shippingCostDefault = null) {
		$shippingCostKeys = $this->args['key'] . '.shippingcost';
		$shippingCostNameKey = 'conf[' . $shippingCostKeys . ']';
		$shippingCostIdKey = 'config_' . (isset($shippingCostKeys) ? str_replace('.', '_', $shippingCostKeys) : '');

		if ($shippingCostDefault === null) {
			$shippingCostDefault = getDBConfigValue($shippingCostKeys, $this->mpID);
		}
		$shippingCostDefault = mlfloatalize($shippingCostDefault);
		return array(
			'text' => '<label for="' . $shippingCostIdKey . '">' . ML_GENERIC_SHIPPING_COST . ': </label><input type="text" class="autoWidth fullwidth" id="' . $shippingCostIdKey . '" name="' . $shippingCostNameKey . '" value="' . $shippingCostDefault . '"/><span style="margin-left: 3px;">' . ML_RICARDO_CURRENCY . '</span>',
			'id' => $shippingCostIdKey,
			'key' => $shippingCostKeys
		);
	}

	protected function getShippingCumulative($shippingCumulativeDefault = null) {
		$shippingCumulativeKeys = $this->args['key'] . '.shippingcumulative';
		$shippingCumulativeNameKey = 'conf[' . $shippingCumulativeKeys . ']';
		$shippingCumulativeIdKey = 'config_' . (isset($shippingCumulativeKeys) ? str_replace('.', '_', $shippingCumulativeKeys) : '');

		if ($shippingCumulativeDefault === null) {
			$shippingCumulativeDefault = getDBConfigValue($shippingCumulativeKeys, $this->mpID);
		}

		$checked = $shippingCumulativeDefault === 'true' ? 'checked' : '';

		return array(
			'checkbox' => '
				<input type="hidden" name="' . $shippingCumulativeNameKey . '" value="false"/>
				<input type="checkbox" id="' . $shippingCumulativeIdKey . '" name="' . $shippingCumulativeNameKey . '" value="true" ' . $checked . '/><label for="' . $shippingCumulativeIdKey . '">' . ML_RICARDO_CUMULATIVE . '</label>
			',
			'id' => $shippingCumulativeIdKey,
			'key' => $shippingCumulativeKeys
		);
	}

	protected function getShippingDesc($shippingDescDefaulf = null) {
		$shippingDescKeys = $this->args['key'] . '.description';
		$shippingIdKey = 'config_' . (isset($shippingDescKeys) ? str_replace('.', '_', $shippingDescKeys) : '');

		$descriptionTable = '<table id="' . $shippingIdKey . '" style="width:100%" >
								<thead>
									<tr>
										<th>' . ML_RICARDO_LANGUAGE . '</th>
										<th>' . ML_RICARDO_DESCRIPTION . '</th>
									</tr>
								</thead>
								<tbody>';
		$shippingDescDeKeys = $shippingDescKeys . '.de';
		$shippingNameDeKey = 'conf[' . $shippingDescDeKeys . ']';
		$shippingIdDeKey = 'config_' . (isset($shippingDescDeKeys) ? str_replace('.', '_', $shippingDescDeKeys) : '');

		if (isset($this->args['content']['ShippingDescriptionDe']) === true) {
			$shippingDescDeValue = $this->args['content']['ShippingDescriptionDe'];
		} else {
			$shippingDescDeValue = getDBConfigValue($shippingDescDeKeys, $this->mpID);
		}

		$descriptionTable .= '	<tr class="langde">
									<td>' . ML_RICARDO_LANGUAGE_GERMAN . '</td>
									<td><textarea style="margin-top: 3px; width: 100%; height: 60px;" id="' . $shippingIdDeKey . '" name="' . $shippingNameDeKey . '">' . $shippingDescDeValue . '</textarea/></td>
								</tr>';

		$shippingDescFrKeys = $shippingDescKeys . '.fr';
		$shippingNameFrKey = 'conf[' . $shippingDescFrKeys . ']';
		$shippingIdFrKey = 'config_'.(isset($shippingDescFrKeys) ? str_replace('.', '_', $shippingDescFrKeys) : '');

		if (isset($this->args['content']['ShippingDescriptionFr']) === true) {
			$shippingDescFrValue = $this->args['content']['ShippingDescriptionFr'];
		} else {
			$shippingDescFrValue = getDBConfigValue($shippingDescFrKeys, $this->mpID);
		}

		$descriptionTable .= '	<tr class="langfr">
									<td>' . ML_RICARDO_LANGUAGE_FRENCH . '</td>
									<td><textarea style="margin-top: 3px; width: 100%; height: 60px;" id="' . $shippingIdFrKey . '" name="' . $shippingNameFrKey . '">' . $shippingDescFrValue . '</textarea/></td>
								</tr>';

		$descriptionTable .= '	</tbody>
							</table>';

		return array(
			'table' => $descriptionTable,
			'id' => $shippingIdKey
		);
	}

	public function process() {
		if (isset($this->args['kind']) && $this->args['kind'] === 'save') {
			return true;
		}

		if (isset($this->args['func']) && $this->args['func'] === 'packageSize') {
			$packageSize = $this->getPackageSize($this->args['shippingId']);
			return $packageSize['select'];
		} else {
			if (isset($this->args['content'])) {
				$shippingDefault = $this->args['content']['ShippingDetails'];
			} else {
				$shippingDefault = getDBConfigValue($this->args['key'], $this->mpID, array());
			}

			return $this->renderView($shippingDefault);
		}
	}

	public static function shippingDetails($args, &$value = '') {
		global $_MagnaSession;
		$shipProc = new self($args, 'conf[' . $args['key'] . ']', array(
			'mp' => $_MagnaSession['mpID'],
			'mode' => 'conf'
		), $value);
		return $shipProc->process();
	}

}
