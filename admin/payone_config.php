<?php
/* --------------------------------------------------------------
   payone_config.php 2013-07-25 mabr
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2013 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------


   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(ot_cod_fee.php,v 1.02 2003/02/24); www.oscommerce.com
   (C) 2001 - 2003 TheMedia, Dipl.-Ing Thomas Plänkers ; http://www.themedia.at & http://www.oscommerce.at
   (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com ($Id: ot_cod_fee.php 1003 2005-07-10 18:58:52Z mz $)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

require_once 'includes/application_top.php';

// include language
require_once (DIR_FS_EXTERNAL.'payone/lang/'.$_SESSION['language'].'.php');

require_once (DIR_FS_EXTERNAL.'payone/classes/PayoneModified.php');
$payone = new PayoneModified();

$messages_ns = 'messages_'.basename(__FILE__);
if (!isset($_SESSION[$messages_ns])) {
	$_SESSION[$messages_ns] = array();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	if (isset($_POST['cmd'])) {
		if ($_POST['cmd'] == 'save_config') {
			$new_config = $_POST['config'];
			$old_config = $payone->getConfig();
			$config = $payone->mergeConfigs($old_config, $new_config);
			if (empty($new_config['credit_risk']['checkforgenre'])) {
				$config['credit_risk']['checkforgenre'] = array();
			} else {
				$config['credit_risk']['checkforgenre'] = $new_config['credit_risk']['checkforgenre'];
			}

      foreach ($config['orders_status_redirect']['timeout'] as $key => $value) {
        if ($value != '') {
          $config['orders_status_redirect']['timeout'][$key] = (int) $value;
        }
      }

			if (!empty($_POST['remove_pg'])) {
				foreach($_POST['remove_pg'] as $topkey) {
					unset($config[$topkey]);
				}
			}
			$payone->setConfig($config);
			$_SESSION[$messages_ns][] = CONFIGURATION_SAVED;
		}
		
		if ($_POST['cmd'] == 'add_paygenre') {
			foreach($payone->getPaymentTypes() as $genre => $types) {
				if (isset($_POST[$genre])) {
					$payone->addPaymentGenreConfig($genre);
				}
			}
			$_SESSION[$messages_ns][] = PAYMENTGENRE_ADDED;
		}
		
		if ($_POST['cmd'] == 'dump_config') {
			$t_filename = $payone->dumpConfig();
			if ($t_filename === false) {
				$_SESSION[$messages_ns][] = ERROR_DUMPING_CONFIGURATION;
			} else {
				$_SESSION[$messages_ns][] = CONFIGURATION_DUMPED_TO .' '. $t_filename;
			}
		}
		
		if ($_POST['cmd'] == 'install_config') {
		  $payone->installConfig();
		}
	}

	xtc_redirect(xtc_href_link(basename($PHP_SELF)));
}

$messages = $_SESSION[$messages_ns];
$_SESSION[$messages_ns] = array();


function formpartGlobalConfig($identifier, $config, $parent_identifier = '') {
	$id_prefix = $identifier;
	$name_prefix = '';
	if (!empty($parent_identifier)) {
		$id_prefix = $parent_identifier.'_'.$id_prefix;
		$name_prefix = '['.$parent_identifier.']';
	}
	?> 
	<dl class="adminform">
    <div class="dlrow cf">  
      <dt><label for="<?php echo $id_prefix ?>_merchant_id"><?php echo MERCHANT_ID; ?></label></dt>
      <dd>
        <input type="text" id="<?php echo $id_prefix ?>_merchant_id" name="config<?php echo $name_prefix ?>[<?php echo $identifier ?>][merchant_id]" value="<?php echo $config['merchant_id'] ?>">
      </dd>
    </div>
    <div class="dlrow cf">  
      <dt><label for="<?php echo $id_prefix ?>_portal_id"><?php echo PORTAL_ID; ?></label></dt>
      <dd>
        <input type="text" id="<?php echo $id_prefix ?>_portal_id" name="config<?php echo $name_prefix ?>[<?php echo $identifier ?>][portal_id]" value="<?php echo $config['portal_id'] ?>">
      </dd>
    </div>
    <div class="dlrow cf">  
      <dt><label for="<?php echo $id_prefix ?>_subaccount_id"><?php echo SUBACCOUNT_ID; ?></label></dt>
      <dd>
        <input type="text" id="<?php echo $id_prefix ?>_subaccount_id" name="config<?php echo $name_prefix ?>[<?php echo $identifier ?>][subaccount_id]" value="<?php echo $config['subaccount_id'] ?>">
      </dd>
    </div>
    <div class="dlrow cf">  
      <dt><label for="<?php echo $id_prefix ?>_key"><?php echo KEY; ?></label></dt>
      <dd>
        <input type="text" id="<?php echo $id_prefix ?>_key" name="config<?php echo $name_prefix ?>[<?php echo $identifier ?>][key]" value="<?php echo $config['key'] ?>">
      </dd>
    </div>
    <div class="dlrow cf">  
      <dt><label for="<?php echo $id_prefix ?>_operating_mode"><?php echo OPERATING_MODE; ?></label></dt>
      <dd>
        <input type="radio" name="config<?php echo $name_prefix ?>[<?php echo $identifier ?>][operating_mode]" value="test" id="<?php echo $id_prefix ?>_opmode_test" <?php echo $config['operating_mode'] == 'test' ? 'checked="checked"' : '' ?>>
        <label for="<?php echo $id_prefix ?>_opmode_test"><?php echo OPMODE_TEST; ?></label>
        <input type="radio" name="config<?php echo $name_prefix ?>[<?php echo $identifier ?>][operating_mode]" value="live" id="<?php echo $id_prefix ?>_opmode_live" <?php echo $config['operating_mode'] == 'live' ? 'checked="checked"' : '' ?>>
        <label for="<?php echo $id_prefix ?>_opmode_live"><?php echo OPMODE_LIVE; ?></label>
      </dd>
    </div>
    <div class="dlrow cf">  
      <dt><label for="<?php echo $id_prefix ?>_authorization_method"><?php echo AUTHORIZATION_METHOD; ?></label></dt>
      <dd>
        <input type="radio" name="config<?php echo $name_prefix ?>[<?php echo $identifier ?>][authorization_method]" value="auth" id="<?php echo $id_prefix ?>_authmethod_auth" <?php echo $config['authorization_method'] == 'auth' ? 'checked="checked"' : '' ?>>
        <label for="<?php echo $id_prefix ?>_authmethod_auth"><?php echo AUTHMETHOD_AUTH; ?></label>
        <input type="radio" name="config<?php echo $name_prefix ?>[<?php echo $identifier ?>][authorization_method]" value="preauth" id="<?php echo $id_prefix ?>_authmethod_preauth" <?php echo $config['authorization_method'] == 'preauth' ? 'checked="checked"' : '' ?>>
        <label for="<?php echo $id_prefix ?>_authmethod_preauth"><?php echo AUTHMETHOD_PREAUTH; ?></label>
      </dd>
    </div>
    <div class="dlrow cf">  
      <dt><label for="<?php echo $id_prefix ?>_send_cart"><?php echo SEND_CART; ?></label></dt>
      <dd>
        <input type="radio" name="config<?php echo $name_prefix ?>[<?php echo $identifier ?>][send_cart]" value="true" id="<?php echo $id_prefix ?>_sendcart_true" <?php echo $config['send_cart'] == 'true' ? 'checked="checked"' : '' ?>>
        <label for="<?php echo $id_prefix ?>_sendcart_true"><?php echo SENDCART_TRUE; ?></label>
        <input type="radio" name="config<?php echo $name_prefix ?>[<?php echo $identifier ?>][send_cart]" value="false" id="<?php echo $id_prefix ?>_sendcart_false" <?php echo $config['send_cart'] == 'false' ? 'checked="checked"' : '' ?>>
        <label for="<?php echo $id_prefix ?>_sendcart_false"><?php echo SENDCART_FALSE; ?></label>
      </dd>
    </div>
  </dl>
<?php
}

function formpartPaymentGenreConfig($topkey, $config) {
  global $payone;
	?>
	<h4><?php echo PAYMENT_GENRE . ' - ' . $config['name'] ?></h4>
  <fieldset class="paymentgenre subblock">
		<legend>##payment_genre <?php echo $config['name'] ?></legend>
		<dl class="adminform">
      <div class="dlrow cf"> 
        <dt><label for="pg_active_<?php echo $topkey ?>"><?php echo PG_ACTIVE; ?></label></dt>
        <dd>
          <input id="pg_active_<?php echo $topkey ?>_true" name="config[<?php echo $topkey ?>][active]" type="radio" value="true" <?php echo ($config['active'] == 'true' ? 'checked="checked"' : '') ?>>
          <label for="pg_active_<?php echo $topkey ?>_true"><?php echo TEXT_YES; ?></label>
          <input id="pg_active_<?php echo $topkey ?>_false" name="config[<?php echo $topkey ?>][active]" type="radio" value="false" <?php echo ($config['active'] == 'false' ? 'checked="checked"' : '') ?>>
          <label for="pg_active_<?php echo $topkey ?>_false"><?php echo TEXT_NO; ?></label>
        </dd>
      </div>
      <div class="dlrow cf"> 
        <dt><?php echo REMOVE_PAYMENT_GENRE; ?></dt>
        <dd>
          <input type="checkbox" name="remove_pg[]" value="<?php echo $topkey ?>" id="remove_<?php echo $topkey ?>">
          <label for="remove_<?php echo $topkey ?>"><strong><?php echo REMOVE_THIS_GENRE; ?></strong></label>
        </dd>
      </div>
      <div class="dlrow cf"> 
        <dt><label for="pg_order_<?php echo $topkey ?>"><?php echo PG_ORDER; ?></label></dt>
        <dd>
          <input id="pg_order_<?php echo $topkey ?>" name="config[<?php echo $topkey ?>][order]" type="text" value="<?php echo $config['order'] ?>">
        </dd>
      </div>
      <div class="dlrow cf"> 
        <dt><label for="pg_name_<?php echo $topkey ?>"><?php echo PG_NAME; ?></label></dt>
        <dd>
          <input id="pg_name_<?php echo $topkey ?>" name="config[<?php echo $topkey ?>][name]" type="text" value="<?php echo $config['name'] ?>">
        </dd>
      </div>
      <div class="dlrow cf"> 
        <dt><label for="pg_min_cart_value_<?php echo $topkey ?>"><?php echo PG_MIN_CART_VALUE; ?></label></dt>
        <dd>
          <input id="pg_min_cart_value_<?php echo $topkey ?>" name="config[<?php echo $topkey ?>][min_cart_value]" type="text" value="<?php echo $config['min_cart_value'] ?>">
        </dd>
      </div>
      <div class="dlrow cf"> 
        <dt><label for="pg_max_cart_value_<?php echo $topkey ?>"><?php echo PG_MAX_CART_VALUE; ?></label></dt>
        <dd>
          <input id="pg_max_cart_value_<?php echo $topkey ?>" name="config[<?php echo $topkey ?>][max_cart_value]" type="text" value="<?php echo $config['max_cart_value'] ?>">
        </dd>
      </div>
      <div class="dlrow cf"> 
        <dt><label for="pg_operating_mode_<?php echo $topkey ?>"><?php echo PG_OPERATING_MODE; ?></label></dt>
        <dd>
          <input id="pg_operating_mode_<?php echo $topkey ?>_test" name="config[<?php echo $topkey ?>][operating_mode]" type="radio" value="test" <?php echo ($config['operating_mode'] == 'test' ? 'checked="checked"' : '') ?>>
          <label for="pg_operating_mode_<?php echo $topkey ?>_test"><?php echo OPMODE_TEST; ?></label>
          <input id="pg_operating_mode_<?php echo $topkey ?>_live" name="config[<?php echo $topkey ?>][operating_mode]" type="radio" value="live" <?php echo ($config['operating_mode'] == 'live' ? 'checked="checked"' : '') ?>>
          <label for="pg_operating_mode_<?php echo $topkey ?>_live"><?php echo OPMODE_LIVE; ?></label>
        </dd>
      </div>
      <div class="dlrow cf"> 
        <dt><label for="pg_global_override_<?php echo $topkey ?>"><?php echo PG_GLOBAL_OVERRIDE; ?></label></dt>
        <dd>
          <input class="go_trigger" id="pg_global_override_<?php echo $topkey ?>_true" name="config[<?php echo $topkey ?>][global_override]" type="radio" value="true" <?php echo ($config['global_override'] == 'true' ? 'checked="checked"' : '') ?>>
          <label for="pg_global_override_<?php echo $topkey ?>_true"><?php echo TEXT_YES; ?></label>
          <input class="go_trigger" id="pg_global_override_<?php echo $topkey ?>_false" name="config[<?php echo $topkey ?>][global_override]" type="radio" value="false" <?php echo ($config['global_override'] == 'false' ? 'checked="checked"' : '') ?>>
          <label for="pg_global_override_<?php echo $topkey ?>_false"><?php echo TEXT_NO; ?></label>
        </dd>
      </div>
      <div class="dlrow cf override"> 
        <dt class="global_override">
          <?php echo OVERRIDE_DATA; ?>
        </dt>
        <dd class="global_override">
          <?php
            //$config = $payone->mergeConfigs($config['global'], $payone->getConfig('global')); ?>
          <?php echo formpartGlobalConfig('global', $config['global'], $topkey); ?>
        </dd>
      </div>
      <div class="dlrow cf"> 
        <dt><?php echo PG_COUNTRIES; ?></dt>
        <dd class="countries_list">
          <button class="select_all"><?php echo SELECT_ALL_COUNTRIES; ?></button>
          <button class="select_none"><?php echo SELECT_NO_COUNTRY; ?></button><br>
          <ul class="countrylist">
            <?php $config['countries'] = is_array($config['countries']) ? $config['countries'] : array(); ?>
            <?php foreach(getActiveCountries() as $country) { ?>
              <li>
              <input id="pg_countries_<?php echo $topkey.'_'.$country['countries_iso_code_2'] ?>" name="config[<?php echo $topkey ?>][countries][]" type="checkbox"
                value="<?php echo $country['countries_iso_code_2']?>" <?php echo (in_array($country['countries_iso_code_2'], $config['countries']) ? 'checked="checked"' : ''); ?>>
              <label for="pg_countries_<?php echo $topkey.'_'.$country['countries_iso_code_2'] ?>"><?php echo $country['countries_name'] ?></label>
              </li>
            <?php } ?>
          </ul>
        </dd>
      </div>
      <div class="dlrow cf"> 
        <dt><?php echo PG_SCORING_ALLOWED; ?></dt>
        <dd>
          <input id="pg_scoring_allowed_red_<?php echo $topkey ?>" name="config[<?php echo $topkey ?>][allow_red]" type="checkbox"
            value="true" <?php echo ($config['allow_red'] == 'true' ? 'checked="checked"' : '') ?>>
          <label for="pg_scoring_allowed_red_<?php echo $topkey ?>"><?php echo PG_RED; ?></label>
          <input id="pg_scoring_allowed_yellow_<?php echo $topkey ?>" name="config[<?php echo $topkey ?>][allow_yellow]" type="checkbox"
            value="true" <?php echo ($config['allow_yellow'] == 'true' ? 'checked="checked"' : '') ?>>
          <label for="pg_scoring_allowed_yellow_<?php echo $topkey ?>"><?php echo PG_YELLOW; ?></label>
          <input id="pg_scoring_allowed_green_<?php echo $topkey ?>" name="config[<?php echo $topkey ?>][allow_green]" type="checkbox"
            value="true" <?php echo ($config['allow_green'] == 'true' ? 'checked="checked"' : '') ?>>
          <label for="pg_scoring_allowed_green_<?php echo $topkey ?>"><?php echo PG_GREEN; ?></label>
        </dd>
      </div>
  
      <?php echo formpartPaymentGenreSpecific($topkey, $config); ?>

      <div class="dlrow cf"> 
        <dt><?php echo PG_PAYMENT_TYPES; ?></dt>
        <dd>
          <dl class="paymenttypes">
            <?php foreach($config['types'] as $type => $typedata) { ?>
            <dt><?php echo constant('paymenttype_'.$type); ?></dt>
            <dd>
              <input id="pg_paymenttype_active_<?php echo $type.'_'.$topkey ?>" name="config[<?php echo $topkey ?>][types][<?php echo $type ?>][active]"
                type="checkbox" value="true" <?php echo ($config['types'][$type]['active'] == 'true' ? 'checked="checked"' : '') ?>>
              <label for="pg_paymenttype_active_<?php echo $type.'_'.$topkey ?>"><?php echo PG_TYPE_ACTIVE; ?></label>
            </dd>
            <?php } ?>
          </dl>
        </dd>
      </div>
    </dl>
  </fieldset>
<?php
}

function formpartPaymentGenreSpecific($topkey, $config) {
	if ($config['genre'] == 'creditcard') {
	?>
    <div class="dlrow cf">
      <dt><?php echo PG_CHECK_CAV; ?></dt>
      <dd>
        <input id="pg_genre_specific_check_cav_<?php echo $topkey ?>_true" name="config[<?php echo $topkey ?>][genre_specific][check_cav]" type="radio" value="true" <?php echo ($config['genre_specific']['check_cav'] == 'true' ? 'checked="checked"' : '') ?>>
        <label for="pg_genre_specific_check_cav_<?php echo $topkey ?>_true"><?php echo TEXT_YES; ?></label>
        <input id="pg_genre_specific_check_cav_<?php echo $topkey ?>_false" name="config[<?php echo $topkey ?>][genre_specific][check_cav]" type="radio" value="false" <?php echo ($config['genre_specific']['check_cav'] == 'false' ? 'checked="checked"' : '') ?>>
        <label for="pg_genre_specific_check_cav_<?php echo $topkey ?>_false"><?php echo TEXT_NO; ?></label>
      </dd>
    </div>
	<?php
	}
	
	if ($config['genre'] == 'installment') {
	?>
    <div class="dlrow cf">
      <dt><?php echo KLARNA_STOREID; ?></dt>
      <dd>
        <input id="klarna_storeid_<?php echo $topkey ?>" name="config[<?php echo $topkey ?>][genre_specific][klarna][storeid]" type="text" value="<?php echo $config['genre_specific']['klarna']['storeid'] ?>">
      </dd>
    </div>
    <div class="dlrow cf">
      <dt><?php echo KLARNA_COUNTRIES; ?></dt>
      <dd class="countries_list">
        <button class="select_all"><?php echo SELECT_ALL_COUNTRIES; ?></button>
        <button class="select_none"><?php echo SELECT_NO_COUNTRY; ?></button><br>
        <ul class="countrylist">
        <?php 
        $countries = getActiveCountries();
        $config['genre_specific']['klarna']['countries'] = ((is_array($config['genre_specific']['klarna']['countries'])) ? $config['genre_specific']['klarna']['countries'] : array());
        foreach($GLOBALS['payone']->_getKlarnaCountries() as $country) { 
        ?>
          <li>
            <input id="klarna_countries_<?php echo $topkey.'_'.$country; ?>" name="config[<?php echo $topkey ?>][genre_specific][klarna][countries][]" type="checkbox" value="<?php echo $country; ?>" <?php echo (in_array($country, $config['genre_specific']['klarna']['countries']) ? 'checked="checked"' : ''); ?>>
            <label for="klarna_countries_<?php echo $topkey.'_'.$country; ?>"><?php echo $countries[$country]['countries_name']; ?></label>
          </li>
        <?php } ?>
        </ul>
      </dd>
    </div>
	<?php
	}
	
	if ($config['genre'] == 'accountbased'){
	?>
    <div class="dlrow cf">
      <dt><label for="check_bankdata_<?php echo $topkey ?>"><?php echo CHECK_BANKDATA; ?></label></dt>
      <dd>
        <div class="rbuttons">
          <input class="" id="check_bankdata_<?php echo $topkey ?>_none" name="config[<?php echo $topkey ?>][genre_specific][check_bankdata]" type="radio" value="none" <?php echo ($config['genre_specific']['check_bankdata'] == 'none' ? 'checked="checked"' : '') ?>>
          <label for="check_bankdata_<?php echo $topkey ?>_none"><?php echo DONT_CHECK; ?></label>
          <input class="" id="check_bankdata_<?php echo $topkey ?>_basic" name="config[<?php echo $topkey ?>][genre_specific][check_bankdata]" type="radio" value="basic" <?php echo ($config['genre_specific']['check_bankdata'] == 'basic' ? 'checked="checked"' : '') ?>>
          <label for="check_bankdata_<?php echo $topkey ?>_basic"><?php echo CHECK_BASIC; ?></label>
          <input class="" id="check_bankdata_<?php echo $topkey ?>_pos" name="config[<?php echo $topkey ?>][genre_specific][check_bankdata]" type="radio" value="pos" <?php echo ($config['genre_specific']['check_bankdata'] == 'pos' ? 'checked="checked"' : '') ?>>
          <label for="check_bankdata_<?php echo $topkey ?>_pos"><?php echo CHECK_POS; ?></label>
        </div>
      </dd>
    </div>
    <div class="dlrow cf">
      <dt><?php echo SEPA_COUNTRIES; ?></dt>
      <dd class="countries_list">
        <button class="select_all"><?php echo SELECT_ALL_COUNTRIES; ?></button>
        <button class="select_none"><?php echo SELECT_NO_COUNTRY; ?></button><br>
        <ul class="countrylist">
          <?php $config['genre_specific']['sepa_account_countries'] = is_array($config['genre_specific']['sepa_account_countries']) ? $config['genre_specific']['sepa_account_countries'] : array(); ?>
          <?php foreach($GLOBALS['payone']->getSepaCountries() as $country) { ?>
          <li>
            <input id="sepa_countries_<?php echo $topkey.'_'.$country['countries_iso_code_2'] ?>" name="config[<?php echo $topkey ?>][genre_specific][sepa_account_countries][]" type="checkbox"
              value="<?php echo $country['countries_iso_code_2']?>" <?php echo (in_array($country['countries_iso_code_2'], $config['genre_specific']['sepa_account_countries']) ? 'checked="checked"' : ''); ?>>
            <label for="sepa_countries_<?php echo $topkey.'_'.$country['countries_iso_code_2'] ?>"><?php echo $country['countries_name'] ?></label>
          </li>
        <?php } ?>
        </ul>
      </dd>
    </div>
    <div class="dlrow cf">
      <dt><label for="sepa_display_ktoblz_<?php echo $topkey ?>"><?php echo SEPA_DISPLAY_KTOBLZ; ?></label></dt>
      <dd>
        <div class="rbuttons">
          <input class="" id="sepa_display_ktoblz_<?php echo $topkey ?>_true" name="config[<?php echo $topkey ?>][genre_specific][sepa_display_ktoblz]" type="radio" value="true" <?php echo ($config['genre_specific']['sepa_display_ktoblz'] == 'true' ? 'checked="checked"' : '') ?>>
          <label for="sepa_display_ktoblz_<?php echo $topkey ?>_true"><?php echo TEXT_YES; ?></label>
          <input class="" id="sepa_display_ktoblz_<?php echo $topkey ?>_false" name="config[<?php echo $topkey ?>][genre_specific][sepa_display_ktoblz]" type="radio" value="false" <?php echo ($config['genre_specific']['sepa_display_ktoblz'] == 'false' ? 'checked="checked"' : '') ?>>
          <label for="sepa_display_ktoblz_<?php echo $topkey ?>_false"><?php echo TEXT_NO; ?></label>
        </div>
        <div class="note"><?php echo SEPA_DISPLAY_KTOBLZ_NOTE; ?>_note</div>
      </dd>
    </div>
    <div class="dlrow cf">
      <dt><label for="sepa_use_managemandate_<?php echo $topkey ?>"><?php echo SEPA_USE_MANAGEMANDATE; ?></label></dt>
      <dd>
        <div class="rbuttons">
          <input class="" id="sepa_use_managemandate_<?php echo $topkey ?>_true" name="config[<?php echo $topkey ?>][genre_specific][sepa_use_managemandate]" type="radio" value="true" <?php echo ($config['genre_specific']['sepa_use_managemandate'] == 'true' ? 'checked="checked"' : '') ?>>
          <label for="sepa_use_managemandate_<?php echo $topkey ?>_true"><?php echo TEXT_YES; ?></label>
          <input class="" id="sepa_use_managemandate_<?php echo $topkey ?>_false" name="config[<?php echo $topkey ?>][genre_specific][sepa_use_managemandate]" type="radio" value="false" <?php echo ($config['genre_specific']['sepa_use_managemandate'] == 'false' ? 'checked="checked"' : '') ?>>
          <label for="sepa_use_managemandate_<?php echo $topkey ?>_false"><?php echo TEXT_NO; ?></label>
        </div>
        <div class="note"><?php echo SEPA_USE_MANAGEMANDATE_NOTE; ?></div>
      </dd>
    </div>
    <div class="dlrow cf">
      <dt><label for="sepa_download_pdf_<?php echo $topkey ?>"><?php echo SEPA_DOWNLOAD_PDF; ?></label></dt>
      <dd>
        <div class="rbuttons">
          <input class="" id="sepa_download_pdf_<?php echo $topkey ?>_true" name="config[<?php echo $topkey ?>][genre_specific][sepa_download_pdf]" type="radio" value="true" <?php echo ($config['genre_specific']['sepa_download_pdf'] == 'true' ? 'checked="checked"' : '') ?>>
          <label for="sepa_download_pdf_<?php echo $topkey ?>_true"><?php echo TEXT_YES; ?></label>
          <input class="" id="sepa_download_pdf_<?php echo $topkey ?>_false" name="config[<?php echo $topkey ?>][genre_specific][sepa_download_pdf]" type="radio" value="false" <?php echo ($config['genre_specific']['sepa_download_pdf'] == 'false' ? 'checked="checked"' : '') ?>>
          <label for="sepa_download_pdf_<?php echo $topkey ?>_false"><?php echo TEXT_NO; ?></label>
        </div>
        <div class="note"><?php echo SEPA_DOWNLOAD_PDF_NOTE; ?></div>
      </dd>
    </div>
	<?php
	}
}


function getActiveCountries() {
	$query = "SELECT * FROM `countries` WHERE `status` = 1";
	$result = xtc_db_query($query);
	$countries = array();
	while($row = xtc_db_fetch_array($result)) {
		$countries[$row['countries_iso_code_2']] = $row;
	}
	return $countries;
}

function getOrdersStatus($include_hidden = false) {
	$query = "SELECT * FROM `orders_status` WHERE language_id = ".(int)$_SESSION['languages_id']." ORDER BY orders_status_id ASC";
	$result = xtc_db_query($query);
	$status = array();
	if ($include_hidden == true) {
		$status[-1] = 'unsichtbar';
	}
	while($row = xtc_db_fetch_array($result)) {
		$status[$row['orders_status_id']] = $row['orders_status_name'];
	}
	return $status;
}

require (DIR_WS_INCLUDES.'head.php');
?>
    <script type="text/javascript" src="includes/javascript/jquery-1.8.3.min.js"></script>
		<link rel="stylesheet" type="text/css" href="<?php echo DIR_WS_EXTERNAL.'payone/css/payone_config.css'; ?>">
		<script type="text/javascript">
      $(function() {
        $('input.go_trigger').click(function(e) {
          var val = $(this).val();
          var is_active = val == 'true';
          $('.global_override', $(this).closest('dl')).toggle(is_active);
          $('.override').toggle(is_active);
        });
        $('input.go_trigger:checked').click();

        $('button.select_all').click(function(e) {
          e.preventDefault();
          var checkboxes = $('input[type="checkbox"]', $(this).parent());
          checkboxes.attr('checked', 'checked');
        });
        $('button.select_none').click(function(e) {
          e.preventDefault();
          var checkboxes = $('input[type="checkbox"]', $(this).parent());
          checkboxes.removeAttr('checked');
        });

        $('h3').click(function(e) {
          var the_block = $(this).next('.subblock');
          var the_active_block = $(this);
          
          $('h3 + .subblock').not(the_block).hide();
          $('h3').not(the_active_block).removeClass('active');
          the_active_block.toggleClass('active');
          
          if (the_active_block.hasClass('active')) {
            the_block.show();
          } else {
            the_block.hide();
          }          
        });

        $('h4').click(function(e) {
          var the_block = $(this).next('.subblock');
          var the_active_block = $(this);
          
          $('h4 + .subblock').not(the_block).hide();
          $('h4').not(the_active_block).removeClass('active');
          the_active_block.toggleClass('active');
          
          if (the_active_block.hasClass('active')) {
            the_block.show();
          } else {
            the_block.hide();
          }          
        });
      });
		</script>
	</head>
  <body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
		<!-- header //-->
		<?php require DIR_WS_INCLUDES . 'header.php'; ?>
		<!-- header_eof //-->

		<!-- body //-->
		<table border="0" width="100%" cellspacing="2" cellpadding="2">
      <tr>
        <?php //left_navigation
        if (USE_ADMIN_TOP_MENU == 'false') {
          echo '<td class="columnLeft2">'.PHP_EOL;
          echo '<!-- left_navigation //-->'.PHP_EOL;       
          require_once(DIR_WS_INCLUDES . 'column_left.php');
          echo '<!-- left_navigation eof //-->'.PHP_EOL; 
          echo '</td>'.PHP_EOL;      
        }
        ?>
        <!-- body_text //-->
				<td class="boxCenter" width="100%" valign="top">
					<table border="0" width="100%" cellspacing="0" cellpadding="0" class="">
						<tr>
							<td>
								<table border="0" width="100%" cellspacing="0" cellpadding="0">
									<tr>
										<td rowspan="2" width="40px"><?php echo xtc_image(DIR_WS_ICONS.'heading_configuration.gif'); ?></td>
									</tr>
									<tr>
										<td class="pageHeading" style="padding: 4px 0;" valign="top"><?php echo PAYONE_CONFIG_TITLE; ?></td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td class="main payone_config">
								<?php foreach($messages as $msg) { ?>
								<p class="message"><?php echo $msg ?></p>
								<?php }; ?>
                
                <?php if ($payone->checkConfig() === false) { ?>
                
                  <?php echo xtc_draw_form('payone_install', basename($PHP_SELF), xtc_get_all_get_params()); ?>
                    <input type="hidden" name="cmd" value="install_config">
                    <h3><?php echo INSTALL_CONFIG; ?></h3>
                    <dl class="adminform subblock">
                      <dd>
                        <input class="button btn_wide" type="submit" name="installconfig" value="<?php echo INSTALL_CONFIG; ?>">
                      </dd>
                    </dl>
                  </form>
                
                <?php } else { ?>
                
                  <?php 
                  $genres_config = $payone->getGenresConfig();
                  $config = $payone->getConfig();
                  ?>
                
                  <?php echo xtc_draw_form('payone_config', basename($PHP_SELF), xtc_get_all_get_params()); ?>
									<input type="hidden" name="cmd" value="save_config">

									<h3><?php echo ORDERS_STATUS_CONFIGURATION; ?></h3>
									<?php
									$orders_status_hidden = getOrdersStatus(true);
									$orders_status = getOrdersStatus(false);
									?>
									<dl class="adminform subblock">
                    <div class="dlrow cf">
                      <dt>
                        <label for="orders_status_tmp"><?php echo ORDERS_STATUS_TMP; ?></label>
                      </dt>
                      <dd>
                        <select name="config[orders_status][tmp]">
                          <?php foreach($orders_status_hidden as $orders_status_id => $orders_status_name) { ?>
                            <option value="<?php echo $orders_status_id ?>" <?php echo $config['orders_status']['tmp'] == $orders_status_id ? 'selected="selected"' : '' ?>>
                              <?php echo $orders_status_name ?>
                            </option>
                          <?php } ?>
                        </select>
                      </dd>
                      <dt>
                        <label for="orders_status_redirect_url[tmp]"><?php echo TEXT_EXTERN_CALLBACK_URL; ?></label>
                      </dt>
                      <dd>
                        <input id="orders_status_redirect_url[tmp]" name="config[orders_status_redirect][url][tmp]" value="<?php echo $config['orders_status_redirect']['url']['tmp']; ?>" type="text">
                      </dd>
                      <dt>
                        <label for="orders_status_redirect_timeout[tmp]"><?php echo TEXT_EXTERN_CALLBACK_TIMEOUT; ?></label>
                      </dt>
                      <dd>
                        <input id="orders_status_redirect_timeout[tmp]" name="config[orders_status_redirect][timeout][tmp]" value="<?php echo $config['orders_status_redirect']['timeout']['tmp']; ?>" type="text">
                      </dd>
                    </div>
										<?php foreach($payone->getStatusNames() as $p1_status) { ?>
                    <div class="dlrow cf">
											<dt>
												<label for="orders_status_<?php echo $p1_status ?>"><?php echo constant('ORDERS_STATUS_'.strtoupper($p1_status)); ?></label>
											</dt>
											<dd>
												<select name="config[orders_status][<?php echo $p1_status ?>]">
													<?php foreach($orders_status as $orders_status_id => $orders_status_name) { ?>
														<option value="<?php echo $orders_status_id ?>" <?php echo $config['orders_status'][$p1_status] == $orders_status_id ? 'selected="selected"' : '' ?>>
															<?php echo $orders_status_name ?>
														</option>
													<?php } ?>
												</select>
											</dd>
											<dt>
                        <label for="orders_status_redirect_url[<?php echo $p1_status ?>]"><?php echo TEXT_EXTERN_CALLBACK_URL; ?></label>
											</dt>
											<dd>
                        <input id="orders_status_redirect_url[<?php echo $p1_status ?>]" name="config[orders_status_redirect][url][<?php echo $p1_status ?>]" value="<?php echo $config['orders_status_redirect']['url'][$p1_status]; ?>" type="text">
                      </dd>
											<dt>
											  <label for="orders_status_redirect_timeout[<?php echo $p1_status ?>]"><?php echo TEXT_EXTERN_CALLBACK_TIMEOUT; ?></label>
											</dt>
											<dd>
                        <input id="orders_status_redirect_timeout[<?php echo $p1_status ?>]" name="config[orders_status_redirect][timeout][<?php echo $p1_status ?>]" value="<?php echo $config['orders_status_redirect']['timeout'][$p1_status]; ?>" type="text">
											</dd>
										</div>
                    <?php } ?>
									</dl>

									<h3><?php echo GLOBAL_CONFIGURATION; ?></h3>
									<div class="subblock">
										<?php echo formpartGlobalConfig('global', $payone->getConfig('global')); ?>
									</div>

									<h3><?php echo ADDRESS_CHECK_CONFIGURATION; ?></h3>
									<dl class="adminform subblock">
                    <div class="dlrow cf">
										<dt>
											<label for="ac_active"><?php echo AC_ACTIVE; ?></label>
										</dt>
										<dd>
											<input id="ac_active_true" name="config[address_check][active]" type="radio" value="true" <?php echo ($config['address_check']['active'] == 'true' ? 'checked="checked"' : '') ?>>
											<label for="ac_active_true"><?php echo TEXT_YES; ?></label><br>
											<input id="ac_active_false" name="config[address_check][active]" type="radio" value="false" <?php echo ($config['address_check']['active'] == 'false' ? 'checked="checked"' : '') ?>>
											<label for="ac_active_false"><?php echo TEXT_NO; ?></label><br>
										</dd>
                    </div>
                    <div class="dlrow cf">
										<dt>
											<label for="ac_operating_mode"><?php echo AC_OPERATING_MODE; ?></label>
										</dt>
										<dd>
											<input type="radio" name="config[address_check][operating_mode]" value="test" id="address_check_opmode_test" <?php echo $config['address_check']['operating_mode'] == 'test' ? 'checked="checked"' : '' ?>>
											<label for="address_check_opmode_test"><?php echo OPMODE_TEST; ?></label><br>
											<input type="radio" name="config[address_check][operating_mode]" value="live" id="address_check_opmode_live" <?php echo $config['address_check']['operating_mode'] == 'live' ? 'checked="checked"' : '' ?>>
											<label for="address_check_opmode_live"><?php echo OPMODE_LIVE; ?></label><br>
										</dd>
                    </div>
                    <div class="dlrow cf">
										<dt>
											<label for="ac_billing_address"><?php echo AC_BILLING_ADDRESS; ?></label>
										</dt>
										<dd>
											<input type="radio" name="config[address_check][billing_address]" value="none" id="address_check_billing_address_none" <?php echo $config['address_check']['billing_address'] == 'none' ? 'checked="checked"' : '' ?>>
											<label for="address_check_billing_address_none"><?php echo AC_BACHECK_NONE; ?></label><br>
											<input type="radio" name="config[address_check][billing_address]" value="basic" id="address_check_billing_address_basic" <?php echo $config['address_check']['billing_address'] == 'basic' ? 'checked="checked"' : '' ?>>
											<label for="address_check_billing_address_basic"><?php echo AC_BACHECK_BASIC; ?></label><br>
											<input type="radio" name="config[address_check][billing_address]" value="person" id="address_check_billing_address_person" <?php echo $config['address_check']['billing_address'] == 'person' ? 'checked="checked"' : '' ?>>
											<label for="address_check_billing_address_person"><?php echo AC_BACHECK_PERSON; ?></label><br>
										</dd>
                    </div>
                    <div class="dlrow cf">
										<dt>
											<label for="ac_delivery_address"><?php echo AC_DELIVERY_ADDRESS; ?></label>
										</dt>
										<dd>
											<input type="radio" name="config[address_check][delivery_address]" value="none" id="address_check_delivery_address_none" <?php echo $config['address_check']['delivery_address'] == 'none' ? 'checked="checked"' : '' ?>>
											<label for="address_check_delivery_address_none"><?php echo AC_BACHECK_NONE; ?></label><br>
											<input type="radio" name="config[address_check][delivery_address]" value="basic" id="address_check_delivery_address_basic" <?php echo $config['address_check']['delivery_address'] == 'basic' ? 'checked="checked"' : '' ?>>
											<label for="address_check_delivery_address_basic"><?php echo AC_BACHECK_BASIC; ?></label><br>
											<input type="radio" name="config[address_check][delivery_address]" value="person" id="address_check_delivery_address_person" <?php echo $config['address_check']['delivery_address'] == 'person' ? 'checked="checked"' : '' ?>>
											<label for="address_check_delivery_address_person"><?php echo AC_BACHECK_PERSON; ?></label><br>
										</dd>
                    </div>
                    <div class="dlrow cf">
										<dt>
											<label for="ac_automatic_correction"><?php echo AC_AUTOMATIC_CORRECTION; ?></label>
										</dt>
										<dd>
											<input type="radio" name="config[address_check][automatic_correction]" value="no" id="address_check_automatic_correction_no" <?php echo $config['address_check']['automatic_correction'] == 'no' ? 'checked="checked"' : '' ?>>
											<label for="address_check_automatic_correction_no"><?php echo AC_AUTOMATIC_CORRECTION_NO; ?></label><br>
											<input type="radio" name="config[address_check][automatic_correction]" value="yes" id="address_check_automatic_correction_yes" <?php echo $config['address_check']['automatic_correction'] == 'yes' ? 'checked="checked"' : '' ?>>
											<label for="address_check_automatic_correction_yes"><?php echo AC_AUTOMATIC_CORRECTION_YES; ?></label><br>
											<input type="radio" name="config[address_check][automatic_correction]" value="user" id="address_check_automatic_correction_user" <?php echo $config['address_check']['automatic_correction'] == 'user' ? 'checked="checked"' : '' ?>>
											<label for="address_check_automatic_correction_user"><?php echo AC_AUTOMATIC_CORRECTION_USER; ?></label><br>
										</dd>
                    </div>
                    <div class="dlrow cf">
										<dt>
											<label for="ac_error_mode"><?php echo AC_ERROR_MODE; ?></label>
										</dt>
										<dd>
											<input type="radio" name="config[address_check][error_mode]" value="abort" id="address_check_error_mode_abort" <?php echo $config['address_check']['error_mode'] == 'abort' ? 'checked="checked"' : '' ?>>
											<label for="address_check_error_mode_abort"><?php echo AC_ERROR_MODE_ABORT; ?></label><br>
											<input type="radio" name="config[address_check][error_mode]" value="reenter" id="address_check_error_mode_reenter" <?php echo $config['address_check']['error_mode'] == 'reenter' ? 'checked="checked"' : '' ?>>
											<label for="address_check_error_mode_reenter"><?php echo AC_ERROR_MODE_REENTER; ?></label><br>
											<input type="radio" name="config[address_check][error_mode]" value="check" id="address_check_error_mode_check" <?php echo $config['address_check']['error_mode'] == 'check' ? 'checked="checked"' : '' ?>>
											<label for="address_check_error_mode_check"><?php echo AC_ERROR_MODE_CHECK; ?></label><br>
											<input type="radio" name="config[address_check][error_mode]" value="continue" id="address_check_error_mode_continue" <?php echo $config['address_check']['error_mode'] == 'continue' ? 'checked="checked"' : '' ?>>
											<label for="address_check_error_mode_continue"><?php echo AC_ERROR_MODE_CONTINUE; ?></label><br>
										</dd>
                    </div>
                    <div class="dlrow cf">
										<dt>
											<label for="ac_min_cart_value"><?php echo AC_MIN_CART_VALUE; ?></label>
										</dt>
										<dd>
											<input id="ac_min_cart_value" name="config[address_check][min_cart_value]" value="<?php echo $config['address_check']['min_cart_value'] ?>" type="text">
										</dd>
                    </div>
                    <div class="dlrow cf">
										<dt>
											<label for="ac_max_cart_value"><?php echo AC_MAX_CART_VALUE; ?></label>
										</dt>
										<dd>
											<input id="ac_max_cart_value" name="config[address_check][max_cart_value]" value="<?php echo $config['address_check']['max_cart_value'] ?>" type="text">
										</dd>
                    </div>
                    <div class="dlrow cf">
										<dt>
											<label for="ac_validity"><?php echo AC_VALIDITY; ?></label>
										</dt>
										<dd>
											<input id="ac_validity" name="config[address_check][validity]" value="<?php echo $config['address_check']['validity'] ?>" type="text">
											<?php echo DAYS; ?>
										</dd>
                    </div>
                    <div class="dlrow cf">
										<dt>
											<label for="ac_pstatus_mapping"><?php echo AC_PSTATUS_MAPPING; ?></label>
										</dt>
										<dd>
											<dl class="adminform">
												<dt><label for="ac_pstatus_nopcheck"><?php echo AC_PSTATUS_NOPCHECK; ?></label></dt>
												<dd>
													<select id="ac_pstatus_nopcheck" name="config[address_check][pstatus][nopcheck]">
														<option <?php echo $config['address_check']['pstatus']['nopcheck'] == 'green' ? 'selected="selected"' : '' ?> value="green"><?php echo PG_GREEN; ?></option>
														<option <?php echo $config['address_check']['pstatus']['nopcheck'] == 'yellow' ? 'selected="selected"' : '' ?> value="yellow"><?php echo PG_YELLOW; ?></option>
														<option <?php echo $config['address_check']['pstatus']['nopcheck'] == 'red' ? 'selected="selected"' : '' ?> value="red"><?php echo PG_RED; ?></option>
													</select>
												</dd>
												<dt><label for="ac_pstatus_fullnameknown"><?php echo AC_PSTATUS_FULLNAMEKNOWN; ?></label></dt>
												<dd>
													<select id="ac_pstatus_fullnameknown" name="config[address_check][pstatus][fullnameknown]">
														<option <?php echo $config['address_check']['pstatus']['fullnameknown'] == 'green' ? 'selected="selected"' : '' ?> value="green"><?php echo PG_GREEN; ?></option>
														<option <?php echo $config['address_check']['pstatus']['fullnameknown'] == 'yellow' ? 'selected="selected"' : '' ?> value="yellow"><?php echo PG_YELLOW; ?></option>
														<option <?php echo $config['address_check']['pstatus']['fullnameknown'] == 'red' ? 'selected="selected"' : '' ?> value="red"><?php echo PG_RED; ?></option>
													</select>
												</dd>
												<dt><label for="ac_pstatus_lastnameknown"><?php echo AC_PSTATUS_LASTNAMEKNOWN; ?></label></dt>
												<dd>
													<select id="ac_pstatus_lastnameknown" name="config[address_check][pstatus][lastnameknown]">
														<option <?php echo $config['address_check']['pstatus']['lastnameknown'] == 'green' ? 'selected="selected"' : '' ?> value="green"><?php echo PG_GREEN; ?></option>
														<option <?php echo $config['address_check']['pstatus']['lastnameknown'] == 'yellow' ? 'selected="selected"' : '' ?> value="yellow"><?php echo PG_YELLOW; ?></option>
														<option <?php echo $config['address_check']['pstatus']['lastnameknown'] == 'red' ? 'selected="selected"' : '' ?> value="red"><?php echo PG_RED; ?></option>
													</select>
												</dd>
												<dt><label for="ac_pstatus_nameunknown"><?php echo AC_PSTATUS_NAMEUNKNOWN; ?></label></dt>
												<dd>
													<select id="ac_pstatus_nameunknown" name="config[address_check][pstatus][nameunknown]">
														<option <?php echo $config['address_check']['pstatus']['nameunknown'] == 'green' ? 'selected="selected"' : '' ?> value="green"><?php echo PG_GREEN; ?></option>
														<option <?php echo $config['address_check']['pstatus']['nameunknown'] == 'yellow' ? 'selected="selected"' : '' ?> value="yellow"><?php echo PG_YELLOW; ?></option>
														<option <?php echo $config['address_check']['pstatus']['nameunknown'] == 'red' ? 'selected="selected"' : '' ?> value="red"><?php echo PG_RED; ?></option>
													</select>
												</dd>
												<dt><label for="ac_pstatus_nameaddrambiguity"><?php echo AC_PSTATUS_NAMEADDRAMBIGUITY; ?></label></dt>
												<dd>
													<select id="ac_pstatus_nameaddrambiguity" name="config[address_check][pstatus][nameaddrambiguity]">
														<option <?php echo $config['address_check']['pstatus']['nameaddrambiguity'] == 'green' ? 'selected="selected"' : '' ?> value="green"><?php echo PG_GREEN; ?></option>
														<option <?php echo $config['address_check']['pstatus']['nameaddrambiguity'] == 'yellow' ? 'selected="selected"' : '' ?> value="yellow"><?php echo PG_YELLOW; ?></option>
														<option <?php echo $config['address_check']['pstatus']['nameaddrambiguity'] == 'red' ? 'selected="selected"' : '' ?> value="red"><?php echo PG_RED; ?></option>
													</select>
												</dd>
												<dt><label for="ac_pstatus_undeliverable"><?php echo AC_PSTATUS_UNDELIVERABLE; ?></label></dt>
												<dd>
													<select id="ac_pstatus_undeliverable" name="config[address_check][pstatus][undeliverable]">
														<option <?php echo $config['address_check']['pstatus']['undeliverable'] == 'green' ? 'selected="selected"' : '' ?> value="green"><?php echo PG_GREEN; ?></option>
														<option <?php echo $config['address_check']['pstatus']['undeliverable'] == 'yellow' ? 'selected="selected"' : '' ?> value="yellow"><?php echo PG_YELLOW; ?></option>
														<option <?php echo $config['address_check']['pstatus']['undeliverable'] == 'red' ? 'selected="selected"' : '' ?> value="red"><?php echo PG_RED; ?></option>
													</select>
												</dd>
												<dt><label for="ac_pstatus_dead"><?php echo AC_PSTATUS_DEAD; ?></label></dt>
												<dd>
													<select id="ac_pstatus_dead" name="config[address_check][pstatus][dead]">
														<option <?php echo $config['address_check']['pstatus']['dead'] == 'green' ? 'selected="selected"' : '' ?> value="green"><?php echo PG_GREEN; ?></option>
														<option <?php echo $config['address_check']['pstatus']['dead'] == 'yellow' ? 'selected="selected"' : '' ?> value="yellow"><?php echo PG_YELLOW; ?></option>
														<option <?php echo $config['address_check']['pstatus']['dead'] == 'red' ? 'selected="selected"' : '' ?> value="red"><?php echo PG_RED; ?></option>
													</select>
												</dd>
												<dt><label for="ac_pstatus_postalerror"><?php echo AC_PSTATUS_POSTALERROR; ?></label></dt>
												<dd>
													<select id="ac_pstatus_postalerror" name="config[address_check][pstatus][postalerror]">
														<option <?php echo $config['address_check']['pstatus']['postalerror'] == 'green' ? 'selected="selected"' : '' ?> value="green"><?php echo PG_GREEN; ?></option>
														<option <?php echo $config['address_check']['pstatus']['postalerror'] == 'yellow' ? 'selected="selected"' : '' ?> value="yellow"><?php echo PG_YELLOW; ?></option>
														<option <?php echo $config['address_check']['pstatus']['postalerror'] == 'red' ? 'selected="selected"' : '' ?> value="red"><?php echo PG_RED; ?></option>
													</select>
												</dd>
											</dl>
										</dd>
                    </div>
									</dl>

									<h3><?php echo CREDIT_RISK_CONFIGURATION; ?></h3>
									<dl class="adminform credit_risk subblock">
                    <div class="dlrow cf">
										<dt>
											<label for="cr_active"><?php echo CR_ACTIVE; ?></label>
										</dt>
										<dd>
											<input id="cr_active_true" name="config[credit_risk][active]" type="radio" value="true" <?php echo ($config['credit_risk']['active'] == 'true' ? 'checked="checked"' : '') ?>>
											<label for="cr_active_true"><?php echo TEXT_YES; ?></label><br>
											<input id="cr_active_false" name="config[credit_risk][active]" type="radio" value="false" <?php echo ($config['credit_risk']['active'] == 'false' ? 'checked="checked"' : '') ?>>
											<label for="cr_active_false"><?php echo TEXT_NO; ?></label><br>
										</dd>
                    </div>
                    <div class="dlrow cf">
										<dt>
											<label for="cr_operating_mode"><?php echo CR_OPERATING_MODE; ?></label>
										</dt>
										<dd>
											<input type="radio" name="config[credit_risk][operating_mode]" value="test" id="credit_risk_opmode_test" <?php echo $config['credit_risk']['operating_mode'] == 'test' ? 'checked="checked"' : '' ?>>
											<label for="credit_risk_opmode_test"><?php echo OPMODE_TEST; ?></label><br>
											<input type="radio" name="config[credit_risk][operating_mode]" value="live" id="credit_risk_opmode_live" <?php echo $config['credit_risk']['operating_mode'] == 'live' ? 'checked="checked"' : '' ?>>
											<label for="credit_risk_opmode_live"><?php echo OPMODE_LIVE; ?></label><br>
										</dd>
                    </div>
                    <div class="dlrow cf">
										<dt>
											<label for="cr_timeofcheck"><?php echo CR_TIMEOFCHECK; ?></label>
										</dt>
										<dd>
											<input type="radio" name="config[credit_risk][timeofcheck]" value="before" id="credit_risk_timeofcheck_before" <?php echo $config['credit_risk']['timeofcheck'] == 'before' ? 'checked="checked"' : '' ?>>
											<label for="credit_risk_timeofcheck_before"><?php echo CR_TIMEOFCHECK_BEFORE; ?></label><br>
											<input type="radio" name="config[credit_risk][timeofcheck]" value="after" id="credit_risk_timeofcheck_after" <?php echo $config['credit_risk']['timeofcheck'] == 'after' ? 'checked="checked"' : '' ?>>
											<label for="credit_risk_timeofcheck_after"><?php echo CR_TIMEOFCHECK_AFTER; ?></label><br>
										</dd>
                    </div>
                    <div class="dlrow cf">
										<dt>
											<label for="cr_typeofcheck"><?php echo CR_TYPEOFCHECK; ?></label>
										</dt>
										<dd>
											<input type="radio" name="config[credit_risk][typeofcheck]" value="iscorehard" id="credit_risk_typeofcheck_iscorehard" <?php echo $config['credit_risk']['typeofcheck'] == 'iscorehard' ? 'checked="checked"' : '' ?>>
											<label for="credit_risk_typeofcheck_iscorehard"><?php echo CR_TYPEOFCHECK_ISCOREHARD; ?></label><br>
											<input type="radio" name="config[credit_risk][typeofcheck]" value="iscoreall" id="credit_risk_typeofcheck_iscoreall" <?php echo $config['credit_risk']['typeofcheck'] == 'iscoreall' ? 'checked="checked"' : '' ?>>
											<label for="credit_risk_typeofcheck_iscoreall"><?php echo CR_TYPEOFCHECK_ISCOREALL; ?></label><br>
											<input type="radio" name="config[credit_risk][typeofcheck]" value="iscorebscore" id="credit_risk_typeofcheck_iscorebscore" <?php echo $config['credit_risk']['typeofcheck'] == 'iscorebscore' ? 'checked="checked"' : '' ?>>
											<label for="credit_risk_typeofcheck_iscorebscore"><?php echo CR_TYPEOFCHECK_ISCOREBSCORE; ?></label><br>
										</dd>
                    </div>
                    <div class="dlrow cf">
										<dt>
											<label for="cr_newclientdefault"><?php echo CR_NEWCLIENTDEFAULT; ?></label>
										</dt>
										<dd>
											<select id="cr_newclientdefault" name="config[credit_risk][newclientdefault]">
												<option <?php echo $config['credit_risk']['newclientdefault'] == 'green' ? 'selected="selected"' : '' ?> value="green"><?php echo PG_GREEN; ?></option>
												<option <?php echo $config['credit_risk']['newclientdefault'] == 'yellow' ? 'selected="selected"' : '' ?> value="yellow"><?php echo PG_YELLOW; ?></option>
												<option <?php echo $config['credit_risk']['newclientdefault'] == 'red' ? 'selected="selected"' : '' ?> value="red"><?php echo PG_RED; ?></option>
											</select>
										</dd>
                    </div>
                    <div class="dlrow cf">
										<dt>
											<label for="cr_validity"><?php echo CR_VALIDITY; ?></label>
										</dt>
										<dd>
											<input id="cr_validity" name="config[credit_risk][validity]" type="text" value="<?php echo $config['credit_risk']['validity'] ?>">
										</dd>
                    </div>
                    <div class="dlrow cf">
										<dt>
											<label for="cr_min_cart_value"><?php echo CR_MIN_CART_VALUE; ?></label>
										</dt>
										<dd>
											<input id="cr_min_cart_value" name="config[credit_risk][min_cart_value]" value="<?php echo $config['credit_risk']['min_cart_value'] ?>" type="text">
										</dd>
                    </div>
                    <div class="dlrow cf">
										<dt>
											<label for="cr_max_cart_value"><?php echo CR_MAX_CART_VALUE; ?></label>
										</dt>
										<dd>
											<input id="cr_max_cart_value" name="config[credit_risk][max_cart_value]" value="<?php echo $config['credit_risk']['max_cart_value'] ?>" type="text">
										</dd>
                    </div>
                    <div class="dlrow cf">
										<dt>
											<label for="cr_checkforgenre"><?php echo CR_CHECKFORGENRE; ?></label>
										</dt>
										<dd>
										<?php /*
											<select name="config[credit_risk][checkforgenre][]" multiple size="5">
												<?php foreach($genres_config as $topkey => $gconfig) { ?>
													<option value="<?php echo $topkey ?>" <?php echo in_array($topkey, $config['credit_risk']['checkforgenre']) ? 'selected="selected"' : '' ?>><?php echo $gconfig['name'] ?></option>
												<?php } ?>
											</select>
										*/ ?>
											<?php foreach($genres_config as $topkey => $gconfig) { ?>
												<input type="checkbox" id="cr_checkforgenre_<?php echo $topkey ?>" name="config[credit_risk][checkforgenre][]"
												value="<?php echo $topkey ?>"
												<?php echo in_array($topkey, $config['credit_risk']['checkforgenre']) ? 'checked="checked"' : '' ?>
												>&nbsp;<label for="cr_checkforgenre_<?php echo $topkey ?>"><?php echo $gconfig['name'] ?></label><br/>
											<?php } ?>
										</dd>
                    </div>
                    <div class="dlrow cf">
										<dt>
											<label for="cr_error_mode"><?php echo CR_ERROR_MODE; ?></label>
										</dt>
										<dd>
											<input type="radio" name="config[credit_risk][error_mode]" value="abort" id="credit_risk_error_mode_abort" <?php echo $config['credit_risk']['error_mode'] == 'abort' ? 'checked="checked"' : '' ?>>
											<label for="credit_risk_error_mode_abort"><?php echo CR_ERROR_MODE_ABORT; ?></label><br>
											<input type="radio" name="config[credit_risk][error_mode]" value="continue" id="credit_risk_error_mode_continue" <?php echo $config['credit_risk']['error_mode'] == 'continue' ? 'checked="checked"' : '' ?>>
											<label for="credit_risk_error_mode_continue"><?php echo CR_ERROR_MODE_CONTINUE; ?></label><br>
										</dd>
                    </div>
                    <?php /*
                    <div class="dlrow cf">
										<dt>
											<label for="cr_notice"><?php echo CR_NOTICE; ?></label>
										</dt>
										<dd>
											<input type="radio" name="config[credit_risk][notice][active]" value="true" id="cr_notice_active" <?php echo $config[credit_risk][notice][active] == 'true' ? 'checked="checked"' : '' ?>>
											<label for="cr_notice_active"><?php echo TEXT_YES; ?></label><br>
											
											<input type="radio" name="config[credit_risk][notice][active]" value="false" id="cr_notice_inactive" <?php echo $config[credit_risk][notice][active] == 'false' ? 'checked="checked"' : '' ?>>
											<label for="cr_notice_inactive"><?php echo TEXT_NO; ?></label><br>
										</dd>
                    </div>
                    */ ?>
                    <div class="dlrow cf">
										<dt>
											<label for="cr_confirmation"><?php echo CR_CONFIRMATION; ?></label>
										</dt>
										<dd>
											<input type="radio" name="config[credit_risk][confirmation][active]" value="true" id="cr_confirmation_active" <?php echo $config[credit_risk][confirmation][active] == 'true' ? 'checked="checked"' : '' ?>>
											<label for="cr_confirmation_active"><?php echo TEXT_YES; ?></label><br>
											
											<input type="radio" name="config[credit_risk][confirmation][active]" value="false" id="cr_confirmation_inactive" <?php echo $config[credit_risk][confirmation][active] == 'false' ? 'checked="checked"' : '' ?>>
											<label for="cr_confirmation_inactive"><?php echo TEXT_NO; ?></label><br>
										</dd>
                    </div>
                    <div class="dlrow cf">
										<dt>
											<label for="cr_abtest"><?php echo CR_ABTEST; ?></label>
										</dt>
										<dd>
											<input type="checkbox" id="cr_abtest_active" name="config[credit_risk][abtest][active]" value="true" <?php echo $config['credit_risk']['abtest']['active'] == 'true' ? 'checked="checked"' : '' ?>>
											<label for="cr_abtest_active"><?php echo ACTIVE; ?></label>
											<input type="text" id="cr_abtest_value" name="config[credit_risk][abtest][value]"
												value="<?php echo $config['credit_risk']['abtest']['value'] ?>">
										</dd>
                    </div>
									</dl>

									<h3><?php echo PAYMENTGENRE_CONFIGURATION; ?></h3>
									<div class="adminform paymentgenres subblock">
										<?php
											if (!empty($genres_config)) {
												foreach($genres_config as $topkey => $gconfig) {
													echo formpartPaymentGenreConfig($topkey, $gconfig);
												}
											} else {
												?>
													<p><?php echo NO_PAYMENTGENRE_CONFIGURED; ?></p>
												<?php
											}
										?>
									</div>
                  <br/>
									<input class="button btn_wide" type="submit" value="<?php echo CONFIG_SAVE; ?>">
								</form>

								<?php echo xtc_draw_form('payone_add_genre', basename($PHP_SELF), xtc_get_all_get_params()); ?>
                  <h3 style="margin-top: 2em"><?php echo ADD_PAYMENT_GENRE; ?></h3>
                  <dl class="adminform subblock">
                    <input type="hidden" name="cmd" value="add_paygenre">
                    <dd>
                      <?php foreach($payone->getPaymentTypes() as $genre => $types) { ?>
                      <input type="submit" class="button btn_wide" id="addpaygenre_<?php echo $genre ?>" name="<?php echo $genre ?>" value="<?php echo constant('PAYGENRE_'.strtoupper($genre)); ?>">
                      <?php } ?>
                    </dd>
                  </dl>
								</form>

								<?php echo xtc_draw_form('payone_dump_config', basename($PHP_SELF), xtc_get_all_get_params()); ?>
                  <h3><?php echo DUMP_CONFIG; ?></h3>
                  <dl class="adminform subblock">
                    <input type="hidden" name="cmd" value="dump_config">
                    <dd>
                      <input class="button btn_wide" type="submit" name="dumpconfig" value="<?php echo DUMP_CONFIG; ?>">
                    </dd>
									</dl>
								</form>

                <?php } ?>
 
							</td>
						</tr>
					</table>
				</td>
				<!-- body_text_eof //-->
			</tr>
		</table>
		<!-- body_eof //-->

		<!-- footer //-->
		<?php require DIR_WS_INCLUDES . 'footer.php'; ?>
		<!-- footer_eof //-->
	</body>
</html>
<?php
require DIR_WS_INCLUDES . 'application_bottom.php';
?>