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

class RicardoBuyingModeProcessor {
	private $args = array();
	private $mainKey = '';
	private $mpID = 0;

	public function __construct($args, $mainKey) {
		global $_MagnaSession;
		
		$this->args = $args;
		$this->mpID = $_MagnaSession['mpID'];		
		$this->mainKey = $mainKey;
	}

	protected static function getBuyingModes() {
		$result = MagnaConnector::gi()->submitRequest(array('ACTION' => 'GetBuyingModes'));
		return $result['DATA'];
	}
	
	public function renderView($buyingModeDefault = '') {
		$nameKey = 'conf[' . $this->args['key'] . ']';
		$buyingModes = self::getBuyingModes();

		$idkey = 'config_'.(isset($this->args['key']) ? str_replace('.', '_', $this->args['key']) : '');

		$buyingModeSelect = '<select id="'.$idkey.'" name="'.$nameKey.'">'."\n";
		foreach ($buyingModes as $key => $buyingMode) {
			$buyingModeSelect .= '<option value="'.$key.'"'.(
				($buyingModeDefault === $key)
					? ' selected="selected"'
					: ''
			).'>'.$buyingMode.'</option>'."\n";
		}
		$buyingModeSelect .= '</select>';

		ob_start();?>
		<script type="text/javascript">/*<![CDATA[*/
			$(document).ready(function() {
				$('#<?php echo $idkey; ?>').change(function () {
					if (this.value === 'auction') {
						jQuery('#config_ricardo_checkin_maxrelistcount option').last().remove();
					} else {
						jQuery('#config_ricardo_checkin_maxrelistcount option').last().after('<option value="2147483647"><?php echo ML_RICARDO_MAX_RELIST_COUNT_UNLIMITED ?></option>');
					}
				});
			});
		/*]]>*/</script><?php
		$buyingModeSelect .= ob_get_contents();
		ob_end_clean();
		
		return $buyingModeSelect;
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
					$buyingMode = $this->args['content']['BuyingMode'];
				} else {
					$buyingMode = getDBConfigValue($this->args['key'], $this->mpID, array());
				}

				return $this->renderView($buyingMode);
			}
		}
	}

	public static function buyingMode($args, &$value = '') {
		global $_MagnaSession;
		$shipProc = new self($args, 'conf['.$args['key'].']', array(
			'mp' => $_MagnaSession['mpID'],
			'mode' => 'conf'
		), $value);
		return $shipProc->process();
	}
	
}
