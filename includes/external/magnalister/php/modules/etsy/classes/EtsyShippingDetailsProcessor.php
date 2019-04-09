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

class EtsyShippingDetailsProcessor {
	private $args = array();
	private $savedvalue = '';
	private $mainKey = '';

	private $magnasession = array();
	private $mpID = 0;
	private $url = array();

	public function __construct($args, $mainKey, $url, &$value = '') {
		global $_MagnaSession, $_url;
		
		$this->args = $args;
		if (isset($this->args['content'])) {
			foreach ($this->args['content'] as $service) {
				if (strpos($service['Service'], '_nat') === false) {
					$this->args['international'] = true;
				} else {
					$this->args['international'] = false;
				}
				break;
			}
		} else if (strpos($this->args['key'], 'local')) {
			$this->args['international'] = false;
		} else {
			$this->args['international'] = true;
		}
		$this->savedvalue = &$value;

		$this->magnasession = &$_MagnaSession;
		$this->mpID = $_MagnaSession['mpID'];

		$this->mainKey = $mainKey;

		$this->url = $url;
	}

	public function renderView($settings = array()) {
		if ($this->args['international']) {
			$services = EtsyApiConfigValues::gi()->getShippingServicesListInternational();
			$locations = EtsyApiConfigValues::gi()->getShippingLocationsList();
			$settings = array_merge(
				array(
					'Service' => '',
					'Cost' => '',
					'location' => '',
				),
				$settings
			);
			
		} else {
			$services = EtsyApiConfigValues::gi()->getShippingLocationsListLocal();
			$locations = array();
			$settings = array_merge(
				array(
					'Service' => '',
					'Cost' => ''
				),
				$settings
			);
		}
		
		$uniqueKey = (string)mt_rand(0, mt_getrandmax());
		if (isset($this->args['key'])) {
			$nameKey = empty($this->mainKey) ? $this->args['key'] : 'conf[' . $this->args['key'] . ']';
		}
		
		if (isset($this->args['content']) && isset($this->mainKey)) {
			$nameKey = $this->mainKey;
		}
		
		$serviceSelect = '<select name="'.$nameKey.'['.$uniqueKey.'][Service]">'."\n";
		foreach ($services as $key => $service) {
			$serviceSelect .= '<option value="'.$key.'"'.(
				($settings['Service'] == $key)
					? ' selected="selected"'
					: ''
			).'>'.$service.'</option>'."\n";
		}
		$serviceSelect .= '</select>';

		$locationSelect = '';
		if (!empty($locations)) {
			if (empty($settings['location'])) $settings['location'] = 'None';
			$locationSelect .= '<select name="'.$nameKey.'['.$uniqueKey.'][location]">'."\n";
			foreach ($locations as $key => $loc) {
				$locationSelect .= '<option value="'.$key.'"'.(
					($settings['location'] == $key)
						? ' selected="selected"'
						: ''
				).'>'.$loc.'</option>'."\n";
			}
			$locationSelect .= '</select>';
		}
		$shippingCost = '<input type="text" name="'.$nameKey.'['.$uniqueKey.'][Cost]" value="'.(isset($settings['Cost']) ? $settings['Cost'] : '').'">';
		$idkey = (isset($this->args['key'])? str_replace('.', '_', $this->args['key']):'').'_'.$uniqueKey;

		$html = '
			<table id="'.$idkey.'" class="shippingDetails inlinetable nowrap autoWidth"><tbody>
				<tr class="row1">
					<td class="paddingRight" '.(empty($locationSelect) ? ' rowspan="2"' : '').'>'.$serviceSelect.'</td>
					<td class="textright">'.ML_HOOD_LABEL_SHIPPING_COSTS.':&nbsp;</td>
					<td class="paddingRight">'.$shippingCost.'</td>
					<td rowspan="2">
						<input id="" type="button" value="+" class="ml-button plus" />
						'.((array_key_exists('func', $this->args) && ($this->args['func'] == '' || $this->args['func'] == 'addRow'))
							? '<input type="button" value="&#8211;" class="ml-button minus" />'
							: '<input type="button" value="&#8211;" class="ml-button minus" style="display: none" />'
						).'
					</td>
				</tr>
				<tr class="bottomDashed">
					'.(!empty($locationSelect) ? '<td class="paddingRight">'.$locationSelect.'</td>' : '')."\n";/*'
					<td class="textright">'.ML_HOOD_LABEL_EACH_ONE_MORE.':&nbsp;</td>
					<td class="paddingRight">'.$additionalShippingCost."\n";*/
		ob_start();?>
		<script type="text/javascript">/*<![CDATA[*/
			$(document).ready(function() {
				$('#<?php echo $idkey; ?> input.ml-button.plus').click(function () {
					var $tableBox = $('#<?php echo $idkey; ?>');
					if ($tableBox.parent('td').find('table').length == 1) {
						$tableBox.find('input.ml-button.minus').fadeIn(0);
					}
					myConsole.log();
					jQuery.blockUI(blockUILoading); 
					jQuery.ajax({
						type: 'POST',
						url: '<?php echo toURL($this->url, array('kind' => 'ajax'), true); ?>',
						data: <?php echo json_encode(array_merge(
							$this->args,
							array (
								'action' => 'extern',
								'function' => 'EtsyShippingDetailsProcessor::shippingConfig',
								'kind' => 'ajax',
								'func' => 'addRow',
							)
						)); ?>,
						success: function(data) {
							jQuery.unblockUI();
							$tableBox.after(data);
						},
						error: function (xhr, status, error) {
							jQuery.unblockUI();
						},
						dataType: 'html'
					});
				});
				$('#<?php echo $idkey; ?> input.ml-button.minus').click(function () {
					var $tableBox = $('#<?php echo $idkey; ?>'),
						tables = $tableBox.parent('td').find('table');
					$tableBox.detach();
					if (tables.length == 2) {
						tables.find('input.ml-button.minus').fadeOut(0);
					}
				});
			});
		/*]]>*/</script><?php
		$html .= ob_get_contents().'
					</td>
				</tr>
			</tbody></table>';
		ob_end_clean();
		
		return $html;
	}
	
	private function verifyAndFix() {
		$data = $_POST;
		if (!empty($this->mainKey) && array_key_exists($this->mainKey, $data)) {
			$data = $data[$this->mainKey];
		}
		if (!array_key_exists($this->args['key'], $data)) {
			return false;
		}
		$data = $data[$this->args['key']];
		#echo print_m($data);
		if (!empty($data)) {
			foreach ($data as $key => &$item) {
				if (empty($item['Service'])) {
					unset($data[$key]);
				}
				$item['Cost'] = mlFloatalize($item['Cost']);
			}
		}
		$data = array_values($data);
		#echo print_m($data);
		$this->savedvalue = json_encode($data);
		return true;
	}

	public function process() {
		if (!array_key_exists('kind', $this->args)) {
			$this->args['kind'] = 'view';
		}
		switch ($this->args['kind']) {
			case 'ajax': {
				if ($this->args['func'] == 'addRow') {
					return $this->renderView();
				}
				return '';
				break;
			}
			case 'save': {
				return $this->verifyAndFix();
				break;
			}
			default: {
				if (isset($this->args['content'])) {
					$setting = $this->changeShippingArrayKeys($this->args['content']);
				} else {
					$setting = getDBConfigValue($this->args['key'], $this->mpID, array());
				}
				if (!is_array($setting) || empty($setting)) {
					return $this->renderView();
				}
				$html = '';
				foreach ($setting as $key => $item) {
					if (count($setting) > 1) {
						$this->args['func'] = '';
					}
					$html .= $this->renderView($item);
				}
				return $html;
				break;
			}
		}
		return false;
	}

	# Aus dem Eintrag in der properties-Tabelle (Wording fuer die hood-API)
	# einen wie in der config-Tabelle machen (wording wie sonst im plugin)
	# Eingabe muss bereits ein Array sein, Teil fuer lokal oder international

	private function changeShippingArrayKeys($prefilled) {
		require_once(DIR_MAGNALISTER_INCLUDES . 'lib/classes/SimplePrice.php');
		$sp = new SimplePrice(null, getCurrencyFromMarketplace($this->mpID));
		foreach ($prefilled as $key => &$service) {
			if (!isset($service['Cost'])) {
				unset($prefilled[$key]);
				continue;
			}
			$service['Cost'] = $sp->setPrice($service['Cost'])->getPrice();
		}
		return $prefilled;
	}

	public static function shippingConfig($args, &$value = '') {
		global $_MagnaSession;
		$shipProc = new self($args, 'conf', array(
			'mp' => $_MagnaSession['mpID'],
			'mode' => 'conf'
		), $value);
		return $shipProc->process();
	}
	
}
