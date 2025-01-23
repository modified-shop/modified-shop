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

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/configure.php');

class HitmeisterConfigure extends MagnaCompatibleConfigure {

	protected function getAuthValuesFromPost() {
		$nClientKey = trim($_POST['conf'][$this->marketplace.'.clientkey']);
		$nSecretKey = trim($_POST['conf'][$this->marketplace.'.secretkey']);
		$nSecretKey = $this->processPasswordFromPost('secretkey', $nSecretKey);
		
		
		

		if (empty($nClientKey)) {
			unset($_POST['conf'][$this->marketplace.'.clientkey']);
		}
		
		if ($nSecretKey === false) {
			unset($_POST['conf'][$this->marketplace.'.secretkey']);
			return false;
		}
							
		$data = array (
			'CLIENTKEY' => $nClientKey,
			'SECRETKEY' => $nSecretKey,
			
		);
		#echo print_m($data);
		return $data;
	}

	protected function getFormFiles() {
		$forms = parent::getFormFiles();
		$forms[] = 'prepareadd';
		$forms[] = 'orderStatus';
		$forms[] = 'invoices';

		return $forms;
	}
	
	public function confShippingtimeMatching($args, &$value = '') {
		if (!defined('TABLE_SHIPPING_STATUS') || !MagnaDB::gi()->tableExists(TABLE_SHIPPING_STATUS)) {
			setDBConfigValue('hitmeister.shippingtimematching.prefer', $this->mpID, false, true);
			return ML_ERROR_NO_SHIPPINGTIME_MATCHING.'
<script type="text/javascript">/*<![CDATA[*/
	$(document).ready(function() {
		$(\'input[id="conf_hitmeister.shippingtimematching.prefer_val"]\').prop(\'checked\', false);
		$(\'input[id="conf_hitmeister.shippingtimematching.prefer_val"]\').prop(\'disabled\', true);
	});
/*]]>*/</script>';
		}
		$shippingtimes = MagnaDB::gi()->fetchArray('
		    SELECT shipping_status_id as id, shipping_status_name as name
		      FROM '.TABLE_SHIPPING_STATUS.'
		     WHERE language_id = '.$_SESSION['languages_id'].' 
		  ORDER BY shipping_status_id ASC
		');
		#$shippingtimeMatch = getDBConfigValue($args['key'], $this->mpID, array());
		#$opts = HitmeisterHelper::GetShippingTimes();
                $handlingtimeMatch = getDBConfigValue('hitmeister.handlingtimematching.values', $this->mpID, array());
                $defaultHandlingTime = getDBConfigValue('hitmeister.handlingtime', $this->mpID, 1);
		$html = '<table class="nostyle" style="float: left; margin-right: 2em;">
			<thead><tr>
				<th>'.ML_LABEL_SHIPPING_TIME_SHOP.'</th>
				<th>'.ML_HITMEISTER_HANDLINGTIME_HM.'</th>
			</tr></thead>
			<tbody>';
		foreach ($shippingtimes as $st) {
			$html .= '
				<tr>
					<td class="nowrap">'.$st['name'].'</td>'
					#<td><select name="conf['.$args['key'].']['.$st['id'].']">';
			#foreach ($opts as $key => $val) {
				#$html .= '<option value="'.$key.'" '.(
					#(array_key_exists($st['id'], $shippingtimeMatch) && ($shippingtimeMatch[$st['id']] == $key))
						#? 'selected="selected"'
						#: ''
				#).'>'.$val.'</option>';
			#}
			#$html .= '
					#</select></td>
					.'<td><input type="text" name="conf[hitmeister.handlingtimematching.values]['.$st['id'].']" value="'.(array_key_exists($st['id'], $handlingtimeMatch) ? $handlingtimeMatch[$st['id']] : $defaultHandlingTime).'">
					</td>
				</tr>';
		}
		$html .= '</tbody></table>';
	
		#$html .= print_m(func_get_args(), 'func_get_args');
	
		#$html .= print_m($shippingtimes, '$shippingtimes');
		#$html .= print_m($shippingtimeMatch, 'shippingtimeMatch');
		return $html;
	}
	
	protected function loadChoiseValues() {
		parent::loadChoiseValues();
		if ($this->isAuthed) {
			HitmeisterHelper::GetSitesConfig($this->form['sites']['fields']['site']);
			HitmeisterHelper::GetCurrenciesConfig($this->form['sites']['fields']['currency']);
			HitmeisterHelper::GetDeliveryCountriesConfig($this->form['prepare']['fields']['location']);
			HitmeisterHelper::GetConditionTypesConfig($this->form['prepare']['fields']['condition']);
			HitmeisterHelper::GetShippingTimesConfig($this->form['prepare']['fields']['shippingtime']);
			HitmeisterHelper::GetHandlingTimesConfig($this->form['prepare']['fields']['handlingtime']);
			HitmeisterHelper::GetShippingGroupsConfig($this->form['prepare']['fields']['shippinggroup']);
			if ($this->form['prepare']['fields']['shippinggroup']['values'] === false) {
				unset($this->form['prepare']['fields']['shippinggroup']);
			}

			$this->form['prepare']['fields']['shippingtimeMatching']['procFunc'] = array($this, 'confShippingtimeMatching');
			mlGetOrderStatus($this->form['orders']['fields']['fbkstatus']);
			mlGetOrderStatus($this->form['orderSyncState']['fields']['shippedstatus']);
			mlGetOrderStatus($this->form['orderSyncState']['fields']['cancelstatus']);
            mlPresetTrackingCodeMatching($this->mpID, 'hitmeister.orderstatus.carrier.dbmatching', 'hitmeister.orderstatus.trackingcode.dbmatching');

			try {
				$orderStatusConditions = MagnaConnector::gi()->submitRequest(array('ACTION' => 'GetOrderStatusData'));
			} catch (MagnaException $me) {
				$orderStatusConditions = array (
					'DATA' => array(
						'CarrierCodes' => ML_ERROR_LABEL,
						'Reasons' => array ('null' => ML_ERROR_LABEL)
					)
				);
			}
			
			$this->form['orderSyncState']['fields']['carrier']['values'] = $orderStatusConditions['DATA']['CarrierCodes'];
			$this->form['orderSyncState']['fields']['cancelreason']['values'] = $orderStatusConditions['DATA']['Reasons'];

			unset($this->form['checkin']['fields']['leadtimetoship']);
			unset($this->form['erpinvoice']['fields']['invoice.erpReversalInvoiceSource']);
			unset($this->form['erpinvoice']['fields']['invoice.erpReversalInvoiceDestination']);
			if (    (!MagnaDB::gi()->tableExists('invoices'))
			     || ('commerceseo' == SHOPSYSTEM)) { // commerceseo has the table, but doesn't use it
				unset($this->form['invoice']['fields']['invoice.option']['values']['webshop']);
			}
		}
	}
	
	protected function finalizeForm() {
		parent::finalizeForm();
		if (!$this->isAuthed) {
			$this->form = array (
				'login' => $this->form['login']
			);
			return;
		}
	}

	protected function loadChoiseValuesAfterProcessPOST() {
		if (!$this->isAuthed) {
			global $magnaConfig;

			unset($magnaConfig['db'][$this->mpID]['hitmeister.secretkey']);			
		}
	}
	
	public function process() {
		parent::process();
        echo $this->invoiceOptionJS();
		if (!$this->isAjax) {
			$cG = new MLConfigurator($this->form, $this->mpID, 'conf_magnacompat');
			echo $cG->checkboxAlert('conf_hitmeister.multipleeans_val',
				ML_HITMEISTER_TITLE_WARNING_ALLOW_MULTIPLE_EAN,
				ML_HITMEISTER_TEXT_WARNING_ALLOW_MULTIPLE_EAN,
				ML_BUTTON_LABEL_YES,
				ML_BUTTON_LABEL_NO);
			$curSite = getDBConfigValue('hitmeister.site', $this->mpID, false);
?>
<script type="text/javascript">/*<![CDATA[*/
		$(document).ready(function() {
			$('#config_hitmeister_currency_key').val($('#config_hitmeister_site').val());
			$('#config_hitmeister_currency_key').prop('disabled', true);
		});
		jQuery(function ($) {
			$('form').bind('submit', function () {
			$('#config_hitmeister_currency_key').prop('disabled', false);
                        });
		});
		$('#config_hitmeister_site').change(function() {
			var s = $(this);
			if (s.val() == '<?php echo $curSite; ?>') return true;
			$('<div></div>').html('<?php echo str_replace(array("\n", "\r"), ' ', ML_HITMEISTER_TEXT_CHANGE_SITE); ?>').jDialog({
				title: '<?php echo ML_HITMEISTER_LABEL_CHANGE_SITE ?>',
				buttons: {
					'<?php echo ML_BUTTON_LABEL_NO; ?>': function() {
						s.val('<?php echo $curSite; ?>');
						jQuery(this).dialog('close');
					},
					'<?php echo ML_BUTTON_LABEL_YES; ?>': function() {
						$('#config_hitmeister_currency_key').val($('#config_hitmeister_site').val());
						$('#conf_magnacompat').submit();
					}
				}
			});
		});
/*]]>*/</script>
<?php
		}
	}
}
