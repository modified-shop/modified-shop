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

class eBayShippingDetailsProcessor {
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
			foreach($this->args['content'] as $service) {
				if (array_key_exists('ShipToLocation', $service)) {
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

		$this->magnasession = $_MagnaSession;
		$this->mpID = $_MagnaSession['mpID'];

		$this->mainKey = $mainKey;

		$this->url = $url;
	}

	public function renderView($settings = array()) {
		global $_MagnaSession;

		if ($this->args['international']) {
			$services = geteBayInternationalShippingServicesList();
			$locations = geteBayShippingLocationsList();
			$settings = array_merge(
				array(
					'service' => '',
					'cost' => '',
					'location' => '',
					#'addcost' => '',
				),
				$settings
			);
		} else {
			$services = geteBayLocalShippingServicesList();
			$locations = array();
			$settings = array_merge(
				array(
					'service' => '',
					'cost' => '',
					#'addcost' => '',
				),
				$settings
			);
		}

		$uniqueKey = (string)mt_rand(0, mt_getrandmax());

		if (empty($this->mainKey)) {
			$nameKey = $this->args['key'];
		} else {
			$nameKey = 'conf['.$this->args['key'].']';
		}

		if (isset($this->args['content']) && isset($this->mainKey)) {
			$nameKey = $this->mainKey;
		}

		$serviceSelect = '<select name="'.$nameKey.'['.$uniqueKey.'][service]">'."\n";
		foreach ($services as $key => $service) {
			$serviceSelect .= '<option value="'.$key.'"'.(
				($settings['service'] == $key)
					? ' selected="selected"'
					: ''
			).'>'.$service.'</option>'."\n";
		}
		$serviceSelect .= '</select>';

		$locationSelect = '';
		if (isset($locations['None'])) unset($locations['None']);
		if (empty($settings['location'])) $settings['location'] = array();
		if (!is_array($settings['location'])) $settings['location'] = array($settings['location']);
		if (!empty($locations)) {
			$maxRows = (int)(ceil(count($locations) / 5));
			$locationSelect .= "<table><tr>\n";
			$currRow=0;
			foreach ($locations as $key => $loc) {
				if (0 == $currRow % $maxRows) {
					$locationSelect .= '<td>';
				}
				$locationSelect .= '<input type="checkbox" name="'.$nameKey.'['.$uniqueKey.'][location][]" value="'.$key.'"';
				if (  ($settings['location'] == $key)
				    ||(is_array($settings['location']) && in_array($key, $settings['location']))) { $locationSelect .= ' checked="checked"';
				}
				$locationSelect .= ' /><nobr>'.$loc."&nbsp;</nobr>\n";
				if (  ($currRow % $maxRows == $maxRows - 1)
				    ||($currRow == count($locations))) {
					$locationSelect .= '</td>';
				} else {
					$locationSelect .= '<br />';
				}
				$currRow++;
			}
			$locationSelect .= "</tr></table>\n";
		}
		$shippingCost = '<input type="text" name="'.$nameKey.'['.$uniqueKey.'][cost]" value="'.$settings['cost'].'">';
		$idkey = str_replace('.', '_', $this->args['key']).'_'.$uniqueKey;

		$html = '
			<table id="'.$idkey.'" class="shippingDetails inlinetable nowrap autoWidth"><tbody>
				<tr class="row1">'. (empty($locationSelect) ? '
					<td class="paddingRight">'.$serviceSelect.'</td>
					<td class="textright">'.ML_EBAY_LABEL_SHIPPING_COSTS.':&nbsp;</td>
					<td class="paddingRight">'.$shippingCost.'</td>
					' : '
					<td class="paddingRight" colspan="3">'.$serviceSelect.'&nbsp;&nbsp;'.ML_EBAY_LABEL_SHIPPING_COSTS.':&nbsp;'.$shippingCost.'</td>').'
					<td rowspan="2">
						<input id="" type="button" value="+" class="ml-button plus" />
						'.((array_key_exists('func', $this->args) && ($this->args['func'] == '' || $this->args['func'] == 'addRow'))
							? '<input type="button" value="&#8211;" class="ml-button minus" />'
							: '<input type="button" value="&#8211;" class="ml-button minus" style="display: none" />'
						).'
					</td>
				</tr>
				<tr class="bottomDashed">
					'.(!empty($locationSelect) ? '<td class="paddingRight" colspan="3">'.$locationSelect.'</td>' : '')."\n";
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
									'function' => 'eBayShippingConfig',
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
		if (!empty($data)) {
			foreach ($data as $key => &$item) {
				if (empty($item['service'])) {
					unset($data[$key]);
				}
				if ('=GEWICHT' == strtoupper($item['cost'])) {
					$item['cost'] = '=GEWICHT';
					if (isset($item['addcost'])) unset($item['addcost']);
				} else {
					$item['cost'] = (float)str_replace(',', '.', trim($item['cost']));
				}
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
	}

	# Aus dem Eintrag in der properties-Tabelle (Wording fuer die eBay-API)
	# einen wie in der config-Tabelle machen (wording wie sonst im plugin)
	# Eingabe muss bereits ein Array sein, Teil fuer lokal oder international
	private function changeShippingArrayKeys($prefilled) {
        require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');
        $sp = new SimplePrice(null, getDBConfigValue('ebay.currency', $this->mpID));
		foreach ($prefilled as &$service) {
			if (empty($service['ShippingService'])) {
				continue;
			}
			if (isset($service['FreeShipping'])) {
				unset($service['FreeShipping']);
			}
			$service['service'] = $service['ShippingService'];
			unset($service['ShippingService']);

			if ('=GEWICHT' == strtoupper($service['ShippingServiceCost'])) {
				$service['cost'] = '=GEWICHT';
			} else {
				$service['cost'] = $sp->setPrice($service['ShippingServiceCost'])->getPrice();
			}
			unset($service['ShippingServiceCost']);

			unset($service['ShippingServiceAdditionalCost']);

			if (isset($service['ShipToLocation'])) {
				$service['location'] = $service['ShipToLocation'];
				unset($service['ShipToLocation']);
			}
		}
		return $prefilled;
	}
}
