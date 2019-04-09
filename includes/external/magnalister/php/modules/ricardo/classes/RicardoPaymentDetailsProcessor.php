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

class RicardoPaymentDetailsProcessor {
	private $args = array();
	private $mainKey = '';
	private $mpID = 0;

	public function __construct($args, $mainKey) {
		global $_MagnaSession;
		
		$this->args = $args;
		$this->mpID = $_MagnaSession['mpID'];
		$this->mainKey = $mainKey;
	}

	protected static function getPaymentMethods() {
        try {
            $result = MagnaConnector::gi()->submitRequest(array('ACTION' => 'GetPaymentMethods'));
        } catch (MagnaException $e) {
            throw new Exception('Couldn\'t fetch the payment methods from the ricardo API.', 1540214080);
        }
		return $result['DATA'];
	}
	
	public function renderView($paymentMethodDefault = '') {
		if (is_string($paymentMethodDefault)) {
			$paymentMethodDefault = json_decode($paymentMethodDefault, true);
		}
		if (!is_array($paymentMethodDefault)) {
			$paymentMethodDefault = array();
		}
		
		$nameKey = 'conf[' . $this->args['key'] . ']';
		$paymentMethods = self::getPaymentMethods();
		
		$idkey = 'config_' . (isset($this->args['key']) ? str_replace('.', '_', $this->args['key']) : '');
		$paymentMethodSelect = ' <label for="'.$idkey.'">'.ML_RICARDO_PAYMENT_DETAILS_LABEL.'</label> ';
		$paymentMethodSelect .= '<select id="' . $idkey . '" name="' . $nameKey . '[]" multiple>' . "\n";
		foreach ($paymentMethods as $key => $paymentMethod) {
			$paymentMethodSelect .= '<option value="' . $key . '"' . (
				in_array($key, $paymentMethodDefault)
					? ' selected="selected"'
					: ''
			) . '>' . fixHTMLUTF8Entities($paymentMethod) . '</option>' . "\n";
		}
		$paymentMethodSelect .= '</select>';

		$paymentDetailsDescKeys = $this->args['key'] . '.description';

		if (isset($this->args['content']['PaymentdetailsDescription']) === true) {
			$paymentDetailsValue = $this->args['content']['PaymentdetailsDescription'];
		} else {
			$paymentDetailsValue = getDBConfigValue($paymentDetailsDescKeys, $this->mpID);
		}

		$paymentDesc = $this->getPaymentDescription();

		ob_start();?>
		<script type="text/javascript">/*<![CDATA[*/
			$(document).ready(function() {
				togglePaymentMethodDescription(jQuery('#<?php echo $idkey ?>').val());

				$('#<?php echo $idkey; ?>').change(function () {
					togglePaymentMethodDescription($(this).val());
				});
			});

			function togglePaymentMethodDescription(value) {
				if (value !== null && value.indexOf('0') !== -1) {
					jQuery('#<?php echo $paymentDesc['id'] ?>').show();
				} else {
					jQuery('#<?php echo $paymentDesc['id'] ?>').hide();
				}
			}
		/*]]>*/</script><?php
		$paymentDetailsContent = $paymentMethodSelect . $paymentDesc['table'];
		$paymentDetailsContent .= ob_get_contents();
		ob_end_clean();
		
		return $paymentDetailsContent;
	}

	private function getPaymentDescription() {
		$paymentDescKeys = $this->args['key'] . '.description';
		$paymentIdKey = 'config_' . (isset($paymentDescKeys) ? str_replace('.', '_', $paymentDescKeys) : '');

		$descriptionTable = '<table id="' . $paymentIdKey . '" style="width:100%">
								<thead>
									<tr>
										<th>' . ML_RICARDO_LANGUAGE . '</th>
										<th>' . ML_RICARDO_DESCRIPTION . '</th>
									</tr>
								</thead>
								<tbody>';
		$paymentDescDeKeys = $paymentDescKeys . '.de';
		$paymentNameDeKey = 'conf[' . $paymentDescDeKeys . ']';
		$paymentIdDeKey = 'config_' . (isset($paymentDescDeKeys) ? str_replace('.', '_', $paymentDescDeKeys) : '');

		if (isset($this->args['content']['PaymentdetailsDescriptionDe']) === true) {
			$paymentDescDeValue = $this->args['content']['PaymentdetailsDescriptionDe'];
		} else {
			$paymentDescDeValue = getDBConfigValue($paymentDescDeKeys, $this->mpID);
		}

		$descriptionTable .= '	<tr class="langde">
									<td>' . ML_RICARDO_LANGUAGE_GERMAN . '</td>
									<td><textarea style="margin-top: 3px; width: 100%; height: 60px;" id="' . $paymentIdDeKey . '" name="' . $paymentNameDeKey . '">' . $paymentDescDeValue . '</textarea></td>
								</tr>';

		$paymentDescFrKeys = $paymentDescKeys . '.fr';
		$paymentNameFrKey = 'conf[' . $paymentDescFrKeys . ']';
		$paymentIdFrKey = 'config_' . (isset($paymentDescFrKeys) ? str_replace('.', '_', $paymentDescFrKeys) : '');

		if (isset($this->args['content']['PaymentdetailsDescriptionFr']) === true) {
			$paymentDescFrValue = $this->args['content']['PaymentdetailsDescriptionFr'];
		} else {
			$paymentDescFrValue = getDBConfigValue($paymentDescFrKeys, $this->mpID);
		}

		$descriptionTable .= '	<tr class="langfr">
									<td>' . ML_RICARDO_LANGUAGE_FRENCH . '</td>
									<td><textarea style="margin-top: 3px; width: 100%; height: 60px;" id="' . $paymentIdFrKey . '" name="' . $paymentNameFrKey . '">' . $paymentDescFrValue . '</textarea></td>
								</tr>';

		$descriptionTable .= '	</tbody>
							</table>';

		return array(
			'table' => $descriptionTable,
			'id' => $paymentIdKey
		);
	}

	public function process() {
		if (!array_key_exists('kind', $this->args)) {
			$this->args['kind'] = 'view';
		}

		switch ($this->args['kind']) {
			case 'save': {
				return true;
			}
			default: {
				if (isset($this->args['content'])) {
					$paymentMethodDefault = $this->args['content']['PaymentDetails'];
				} else {
					$paymentMethodDefault = getDBConfigValue($this->args['key'], $this->mpID, array());
				}
				return $this->renderView($paymentMethodDefault);
			}
		}
	}

	public static function paymentDetails($args, &$value = '') {
		global $_MagnaSession;
		$shipProc = new self($args, 'conf[' . $args['key'] . ']', array(
			'mp' => $_MagnaSession['mpID'],
			'mode' => 'conf'
		), $value);
		return $shipProc->process();
	}
	
}
