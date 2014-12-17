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

require_once(DIR_MAGNALISTER_MODULES.'bepado/classes/BepadoApiConfigValues.php');

class BepadoShippingDetailsProcessor {
	private $args = array();
	private $savedvalue = '';
	private $mainKey = '';

	private $magnasession = array();
	private $mpID = 0;
	private $url = array();

	public function __construct($args, $mainKey, $url, &$value = '') {
		global $_MagnaSession, $_url;
		
		#echo print_m(func_get_args(), __METHOD__);
		
		$this->args = $args;
		
		$this->savedvalue = &$value;
		
		$this->magnasession = &$_MagnaSession;
		$this->mpID = $_MagnaSession['mpID'];
		
		$this->mainKey = $mainKey;
		
		$this->url = $url;
	}

	protected static function getCountries() {
		$data = BepadoApiConfigValues::gi()->getCountries();
		if (!empty($data)) {
			return $data;
		}
		$countriesTmp = MagnaDB::gi()->fetchArray('
			  SELECT countries_iso_code_2, countries_name
			    FROM countries
			ORDER BY countries_name ASC
		');
		$countries = array();
		foreach ($countriesTmp as $c) {
			$countries[$c['countries_iso_code_2']] = $c['countries_name'];
		}
		return $countries;
	}
	
	public function renderView($settings = array()) {
		$uniqueKey = (string)mt_rand(0, mt_getrandmax());
		if (empty($this->mainKey) && isset($this->args['key'])) {
			$nameKey = 'conf[' . $this->args['key'] . ']';
		} else {
			$nameKey = empty($this->mainKey) ? __CLASS__ : $this->mainKey;
		}
/*
form.config table.conftbl tr.conf td table.bepadoShippingConfig {
    border-bottom: 1px dashed #ccc;
    margin-bottom: 0em;
}
form.config table.conftbl tr.conf td table.bepadoShippingConfig:last-child {
    border-bottom: none;
}
*/
		if (isset($this->args['content']) && isset($this->mainKey)) {
			$nameKey = $this->mainKey;
		}
		$countries = self::getCountries();
		
		$settings = array_replace(array(
			'ShippingCountry' => 'DE',
			'ShippingCost' => 0.0,
			'ShippingService' => '',
		), $settings);
		
		$counrySelect = '<select style="width: 100%" name="'.$nameKey.'['.$uniqueKey.'][ShippingCountry]">'."\n";
		foreach ($countries as $key => $countryName) {
			$counrySelect .= '<option value="'.$key.'"'.(
				($settings['ShippingCountry'] == $key)
					? ' selected="selected"'
					: ''
			).'>'.$countryName.'</option>'."\n";
		}
		$counrySelect .= '</select>';

		$shippingCost = '<input type="text" name="'.$nameKey.'['.$uniqueKey.'][ShippingCost]" value="'.(isset($settings['ShippingCost']) ? $settings['ShippingCost'] : '').'">';
		$shippingService = '<input type="text" name="'.$nameKey.'['.$uniqueKey.'][ShippingService]" value="'.(isset($settings['ShippingService']) ? $settings['ShippingService'] : '').'">';
		
		
		$idkey = (isset($this->args['key']) ? str_replace('.', '_', $this->args['key']) : '').'_'.$uniqueKey;
		$html = '
			<table id="'.$idkey.'" class="bepadoShippingConfig inlinetable nowrap fullWidth"><tbody>
				<tr class="row1">
					<td class="textright">'.'Versandland'.':&nbsp;</td>
					<td class="paddingRight" style="width: 150px;">'.$counrySelect.'</td>
					<td class="textright">'.'Spediteur'.':&nbsp;</td>
					<td class="paddingRight">'.$shippingService.'</td>
					<td class="textright">'.ML_HOOD_LABEL_SHIPPING_COSTS.':&nbsp;</td>
					<td class="paddingRight">'.$shippingCost.'</td>
					<td>
						<input id="" type="button" value="+" class="ml-button plus" />
						'.((array_key_exists('func', $this->args) && (($this->args['func'] == '') || $this->args['func'] == 'addRow'))
							? '<input type="button" value="&ndash;" class="ml-button minus" />'
							: '<input type="button" value="&ndash;" class="ml-button minus" style="display: none" />'
						).'
					</td>';
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
								'function' => __CLASS__.'::shippingConfig',
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
		if (!isset($this->args['key']) || !isset($data[$this->args['key']])) {
			return false;
		}
		$data = $data[$this->args['key']];
		#echo print_m($data);
		if (!empty($data)) {
			foreach ($data as $key => &$item) {
				$item['ShippingCost'] = mlFloatalize($item['ShippingCost']);
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
				} else if (isset($this->args['key'])) {
					$setting = getDBConfigValue($this->args['key'], $this->mpID, array());
				} else {
					$setting = array();
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
		#echo print_m($prefilled);
		require_once(DIR_MAGNALISTER_INCLUDES . 'lib/classes/SimplePrice.php');
		$sp = new SimplePrice(null, getCurrencyFromMarketplace($this->mpID));
		foreach ($prefilled as $key => &$service) {
			if (!isset($service['ShippingCost'])) {
				unset($prefilled[$key]);
				continue;
			}
			$service['ShippingCost'] = $sp->setPrice($service['ShippingCost'])->getPrice();
		}
		return $prefilled;
	}

	public static function shippingConfig($args, &$value = '') {
		global $_MagnaSession;
		$shipProc = new self($args, 'conf['.$args['key'].']', array(
			'mp' => $_MagnaSession['mpID'],
			'mode' => 'conf'
		), $value);
		return $shipProc->process();
	}
	
}
