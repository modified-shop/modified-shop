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

class RicardoWarrantyProcessor {
	private $args = array();
	private $mainKey = '';
	private $mpID = 0;

	public function __construct($args, $mainKey) {
		global $_MagnaSession;
		
		$this->args = $args;
		$this->mpID = $_MagnaSession['mpID'];		
		$this->mainKey = $mainKey;
	}

	protected static function getWarranties() {
		$result = MagnaConnector::gi()->submitRequest(array('ACTION' => 'GetWarrantyCondition'));
		return $result['DATA'];
	}
	
	public function renderView($warrantyDefault = '') {
		$nameKey = 'conf[' . $this->args['key'] . ']';
		$warranties = self::getWarranties();

		$idkey = 'config_' . (isset($this->args['key']) ? str_replace('.', '_', $this->args['key']) : '');

		$warrantySelect = '<select id="' . $idkey . '" name="' . $nameKey . '">' . "\n";
		foreach ($warranties as $key => $warranty) {
			$warrantySelect .= '<option value="' . $key . '"' . (
				($warrantyDefault == $key)
					? ' selected="selected"'
					: ''
			) . '>' . $warranty . '</option>' . "\n";
		}
		$warrantySelect .= '</select>';

		$warrantyDesc = $this->getWarrantyDescription();

		ob_start();?>
		<script type="text/javascript">/*<![CDATA[*/
			$(document).ready(function() {
				toggleWarrantyDescription(jQuery('#<?php echo $idkey ?>').val());

				$('#<?php echo $idkey; ?>').change(function () {
					toggleWarrantyDescription(this.value);
				});
			});

			function toggleWarrantyDescription(value) {
				if (value === '0') {
					jQuery('#<?php echo $warrantyDesc['id'] ?>').show();
				} else {
					jQuery('#<?php echo $warrantyDesc['id'] ?>').hide();
				}
			}
		/*]]>*/</script><?php
		$content = $warrantySelect . $warrantyDesc['table'];// . $this->getReference();
		$content .= ob_get_contents();
		ob_end_clean();

		return $content;
	}

	private function getReference() {
		$referenceKeys = $this->args['key'] . '.reference';
		$referenceNameKey = 'conf[' . $referenceKeys . ']';
		$referenceIdKey = 'config_'.(isset($referenceKeys) ? str_replace('.', '_', $referenceKeys) : '');

		if (isset($this->args['content']['WarrantyReference']) === true) {
			$warantyReferenceValue = $this->args['content']['WarrantyReference'];
		} else {
			$warantyReferenceValue = getDBConfigValue($referenceKeys, $this->mpID);
		}

		$reference = '<label style="margin-left: 5px;" for="' . $referenceIdKey . '">' . ML_RICARDO_WARRANTY_REFERENCE . ': </label><input type="text" id="' . $referenceIdKey . '" name="' . $referenceNameKey . '" class="autoWidth fullwidth" value="' . $warantyReferenceValue . '"/>';
		return $reference;
	}

	private function getWarrantyDescription() {
		$warrantyDescKeys = $this->args['key'] . '.description';
		$warrantyIdKey = 'config_'.(isset($warrantyDescKeys) ? str_replace('.', '_', $warrantyDescKeys) : '');

		$descriptionTable = '<table id="' . $warrantyIdKey . '" style="width:100%">
								<thead>
									<tr>
										<th>' . ML_RICARDO_LANGUAGE . '</th>
										<th>' . ML_RICARDO_DESCRIPTION . '</th>
									</tr>
								</thead>
								<tbody>';

		$warrantyDescDeKeys = $warrantyDescKeys . '.de';
		$warrantyNameDeKey = 'conf[' . $warrantyDescDeKeys . ']';
		$warrantyIdDeKey = 'config_' . (isset($warrantyDescDeKeys) ? str_replace('.', '_', $warrantyDescDeKeys) : '');

		if (isset($this->args['content']['WarrantyDescriptionDe']) === true) {
			$warantyDescDeValue = $this->args['content']['WarrantyDescriptionDe'];
		} else {
			$warantyDescDeValue = getDBConfigValue($warrantyDescDeKeys, $this->mpID);
		}

		$descriptionTable .= '	<tr class="langde">
									<td>' . ML_RICARDO_LANGUAGE_GERMAN . '</td>
									<td><textarea style="margin-top: 3px; width: 100%; height: 60px;" id="' . $warrantyIdDeKey . '" name="' . $warrantyNameDeKey . '">' . $warantyDescDeValue . '</textarea></td>
								</tr>';

		$warrantyDescFrKeys = $warrantyDescKeys . '.fr';
		$warrantyNameFrKey = 'conf[' . $warrantyDescFrKeys . ']';
		$warrantyIdFrKey = 'config_' . (isset($warrantyDescFrKeys) ? str_replace('.', '_', $warrantyDescFrKeys) : '');

		if (isset($this->args['content']['WarrantyDescriptionFr']) === true) {
			$warantyDescFrValue = $this->args['content']['WarrantyDescriptionFr'];
		} else {
			$warantyDescFrValue = getDBConfigValue($warrantyDescFrKeys, $this->mpID);
		}

		$descriptionTable .= '	<tr class="langfr">
									<td>' . ML_RICARDO_LANGUAGE_FRENCH . '</td>
									<td><textarea style="margin-top: 3px; width: 100%; height: 60px;" id="' . $warrantyIdFrKey . '" name="' . $warrantyNameFrKey . '">' . $warantyDescFrValue . '</textarea></td>
								</tr>';

		$descriptionTable .= '	</tbody>
							</table>';

		return array(
			'table' => $descriptionTable,
			'id' => $warrantyIdKey
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
					$warranty = $this->args['content']['Warranty'];
				} else {
					$warranty = getDBConfigValue($this->args['key'], $this->mpID, array());
				}
				
				return $this->renderView($warranty);
			}
		}
	}

	public static function warranty($args, &$value = '') {
		global $_MagnaSession;
		$shipProc = new self($args, 'conf[' . $args['key'] . ']', array(
			'mp' => $_MagnaSession['mpID'],
			'mode' => 'conf'
		), $value);
		return $shipProc->process();
	}
	
}
