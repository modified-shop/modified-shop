<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

require_once(DIR_FS_EXTERNAL.'klarna/functions/klarna_payment_code.php');


// the module klarna serves every category, "klarna" if Klarna returns it, otherwise the first one in Klarna's order
function klarna_choose_category($identifiers) {
  // the code ends up in JavaScript and field names, so only plain identifiers count
  $valid = array();
  foreach ((array) $identifiers as $identifier) {
    if (is_string($identifier) && preg_match('/^[a-z_]{1,32}$/', $identifier)) {
      $valid[] = $identifier;
    }
  }
  if (in_array('klarna', $valid, true)) {
    return 'klarna';
  }

  return ((count($valid) > 0) ? $valid[0] : '');
}


// returned categories against the state of the module klarna, no output
function klarna_category_check_analyze($categories, $installed, $active, $active_old = array()) {
  $result = array(
    'categories' => array(),
    'used_category' => '',
    'installed' => (bool) $installed,
    'active' => (bool) $active,
    'active_old' => array_values((array) $active_old),
  );

  $identifiers = array();
  foreach ((array) $categories as $category) {
    if ($category instanceof ArrayObject) {
      $category = $category->getArrayCopy();
    }
    if (!is_array($category) || !isset($category['identifier']) || !is_string($category['identifier'])) {
      continue;
    }
    $identifiers[] = $category['identifier'];
    $result['categories'][] = array(
      'identifier' => $category['identifier'],
      'name' => ((isset($category['name']) && is_string($category['name'])) ? $category['name'] : ''),
      'used' => false,
    );
  }

  $result['used_category'] = klarna_choose_category($identifiers);
  foreach ($result['categories'] as $key => $category) {
    $result['categories'][$key]['used'] = ($category['identifier'] === $result['used_category']);
  }

  return $result;
}


function klarna_category_check_escape($string) {
  if (function_exists('decode_utf8')) {
    $string = decode_utf8($string);
  }

  return ((function_exists('encode_htmlspecialchars')) ? encode_htmlspecialchars($string, ENT_QUOTES) : htmlspecialchars($string, ENT_QUOTES));
}


// old Klarna codes of a comma separated payment list, none if the list names klarna as well
function klarna_payment_rule_codes($list) {
  $codes = array_filter(explode(',', preg_replace("'[\r\n\s]+'", '', (string) $list)));
  if (in_array('klarna', $codes, true)) {
    return array();
  }

  return array_values(array_intersect($codes, klarna_legacy_modules()));
}


// read only: places where a payment rule names old Klarna codes but not klarna, the rule would not apply to klarna
function klarna_payment_rules_read() {
  $rules = array();

  $group_array = array();
  $group_codes = array();
  $group_query = xtc_db_query("SELECT customers_status_id,
                                      customers_status_name,
                                      customers_status_payment_unallowed
                                 FROM ".TABLE_CUSTOMERS_STATUS."
                                WHERE customers_status_payment_unallowed LIKE '%klarna%'
                             ORDER BY customers_status_id, language_id");
  while ($group = xtc_db_fetch_array($group_query)) {
    $codes = klarna_payment_rule_codes($group['customers_status_payment_unallowed']);
    if (count($codes) > 0 && !isset($group_array[$group['customers_status_id']])) {
      $group_array[$group['customers_status_id']] = $group['customers_status_name'];
      $group_codes = array_merge($group_codes, $codes);
    }
  }
  if (count($group_array) > 0) {
    $rules[] = array('place' => 'GROUP', 'detail' => implode(', ', $group_array), 'codes' => array_values(array_unique($group_codes)));
  }

  // only the number of customers, the single accounts are not listed
  $customers = 0;
  $customers_codes = array();
  $customers_query = xtc_db_query("SELECT payment_unallowed,
                                          COUNT(*) AS total
                                     FROM ".TABLE_CUSTOMERS."
                                    WHERE payment_unallowed LIKE '%klarna%'
                                 GROUP BY payment_unallowed");
  while ($customer = xtc_db_fetch_array($customers_query)) {
    $codes = klarna_payment_rule_codes($customer['payment_unallowed']);
    if (count($codes) > 0) {
      $customers += (int) $customer['total'];
      $customers_codes = array_merge($customers_codes, $codes);
    }
  }
  if ($customers > 0) {
    $rules[] = array('place' => 'CUSTOMERS', 'detail' => (string) $customers, 'codes' => array_values(array_unique($customers_codes)));
  }

  $place_array = array();
  $config_query = xtc_db_query("SELECT configuration_key,
                                       configuration_value
                                  FROM ".TABLE_CONFIGURATION."
                                 WHERE (configuration_key IN ('DOWNLOAD_UNALLOWED_PAYMENT', 'MODULE_ORDER_TOTAL_GV_UNALLOWED_PAYMENT')
                                        OR configuration_key LIKE 'MODULE\_EXCLUDE\_PAYMENT\_PAYMENT\_%'
                                        OR configuration_key LIKE 'MODULE\_ORDER\_TOTAL\_PAYMENT\_TYPE%')
                                   AND configuration_value LIKE '%klarna%'
                              ORDER BY configuration_key");
  while ($config = xtc_db_fetch_array($config_query)) {
    $codes = klarna_payment_rule_codes($config['configuration_value']);
    if (count($codes) < 1) {
      continue;
    }
    $place = '';
    $detail = '';
    if ($config['configuration_key'] == 'DOWNLOAD_UNALLOWED_PAYMENT') {
      $place = 'DOWNLOAD';
    } elseif ($config['configuration_key'] == 'MODULE_ORDER_TOTAL_GV_UNALLOWED_PAYMENT') {
      $place = 'GV';
    } elseif (preg_match('/^MODULE_EXCLUDE_PAYMENT_PAYMENT_(\d+)$/', $config['configuration_key'], $match)) {
      $place = 'SHIPPING';
      $detail = $match[1];
    } elseif (preg_match('/^MODULE_ORDER_TOTAL_PAYMENT_TYPE(\d+)$/', $config['configuration_key'], $match)) {
      $place = 'FEE';
      $detail = $match[1];
    }
    if ($place == '') {
      continue;
    }
    if (!isset($place_array[$place])) {
      $place_array[$place] = array('place' => $place, 'detail' => array(), 'codes' => array());
    }
    if ($detail != '') {
      $place_array[$place]['detail'][] = $detail;
    }
    $place_array[$place]['codes'] = array_merge($place_array[$place]['codes'], $codes);
  }
  foreach (array('DOWNLOAD', 'GV', 'SHIPPING', 'FEE') as $place) {
    if (isset($place_array[$place])) {
      $rules[] = array('place' => $place, 'detail' => implode(', ', $place_array[$place]['detail']), 'codes' => array_values(array_unique($place_array[$place]['codes'])));
    }
  }

  return $rules;
}


// the status settings that stay 0, fraud review and rejected orders then look like normal new orders
function klarna_status_settings_zero() {
  $zero = array();
  foreach (array('MODULE_PAYMENT_KLARNA_PENDING_STATUS_ID', 'MODULE_PAYMENT_KLARNA_REJECTED_STATUS_ID') as $key) {
    if (!defined($key) || (int) constant($key) < 1) {
      $zero[] = $key;
    }
  }

  return $zero;
}


// warnings about the settings, they do not depend on the Klarna answer
function klarna_category_check_notes_html($check) {
  $html = '';

  if (isset($check['zero_status']) && is_array($check['zero_status'])) {
    foreach ($check['zero_status'] as $key) {
      $constant = 'MODULE_PAYMENT_KLARNA_CHECK_WARN_'.(($key == 'MODULE_PAYMENT_KLARNA_PENDING_STATUS_ID') ? 'PENDING' : 'REJECTED');
      if (defined($constant)) {
        $html .= '<br />'.constant($constant).'<br />';
      }
    }
  }

  if (isset($check['rules']) && is_array($check['rules']) && count($check['rules']) > 0 && defined('MODULE_PAYMENT_KLARNA_CHECK_RULES_HEADING')) {
    $html .= '<br />'.MODULE_PAYMENT_KLARNA_CHECK_RULES_HEADING.'<br />';
    foreach ($check['rules'] as $rule) {
      $constant = 'MODULE_PAYMENT_KLARNA_CHECK_RULE_'.$rule['place'];
      if (defined($constant)) {
        $html .= '- '.sprintf(constant($constant), klarna_category_check_escape($rule['detail']), klarna_category_check_escape(implode(', ', $rule['codes']))).'<br />';
      }
    }
  }

  return $html;
}


// $check: context (country, currency, amount, mode), error_type/error_message or the analyze result
function klarna_category_check_html($check) {
  $context = $check['context'];
  $scope = sprintf(
    MODULE_PAYMENT_KLARNA_CHECK_SCOPE,
    klarna_category_check_escape($context['country']),
    klarna_category_check_escape($context['currency']),
    klarna_category_check_escape($context['amount']),
    klarna_category_check_escape($context['mode'])
  );

  $html = '<b>'.MODULE_PAYMENT_KLARNA_CHECK_HEADING.'</b><br />';

  if ($check['error_type'] != '') {
    switch ($check['error_type']) {
      case 'credentials':
        $html .= MODULE_PAYMENT_KLARNA_CHECK_ERROR_CREDENTIALS;
        break;
      case 'country':
        $html .= MODULE_PAYMENT_KLARNA_CHECK_ERROR_COUNTRY;
        break;
      default:
        $html .= sprintf(MODULE_PAYMENT_KLARNA_CHECK_ERROR_API, klarna_category_check_escape($check['error_message']));
        break;
    }

    return $html.klarna_category_check_notes_html($check);
  }

  $html .= $scope.'<br /><br />';

  if (count($check['categories']) < 1) {
    $html .= MODULE_PAYMENT_KLARNA_CHECK_NONE.'<br />';
  } else {
    $html .= '<table cellpadding="3" cellspacing="0" border="0">'
           . '<tr><td><b>'.MODULE_PAYMENT_KLARNA_CHECK_COL_CATEGORY.'</b></td>'
           . '<td><b>'.MODULE_PAYMENT_KLARNA_CHECK_COL_USED.'</b></td></tr>';
    foreach ($check['categories'] as $category) {
      $html .= '<tr><td>'.klarna_category_check_escape($category['identifier'])
             . (($category['name'] != '') ? ' ('.klarna_category_check_escape($category['name']).')' : '').'</td>'
             . '<td>'.(($category['used']) ? MODULE_PAYMENT_KLARNA_CHECK_USED_YES : MODULE_PAYMENT_KLARNA_CHECK_USED_NO).'</td></tr>';
    }
    $html .= '</table>';

    $html .= '<br />'.sprintf(MODULE_PAYMENT_KLARNA_CHECK_CATEGORY, klarna_category_check_escape($check['used_category'])).'<br />';
  }

  if ($check['active']) {
    $state = MODULE_PAYMENT_KLARNA_CHECK_STATE_ACTIVE;
  } elseif ($check['installed']) {
    $state = MODULE_PAYMENT_KLARNA_CHECK_STATE_INACTIVE;
  } else {
    $state = MODULE_PAYMENT_KLARNA_CHECK_STATE_MISSING;
  }
  $html .= '<br />'.sprintf(MODULE_PAYMENT_KLARNA_CHECK_MODULE, $state).'<br />';

  // the checkout hides them while klarna is on, they only clutter the module list
  if ($check['active'] && count($check['active_old']) > 0) {
    $html .= '<br />'.sprintf(MODULE_PAYMENT_KLARNA_CHECK_WARN_OLD, klarna_category_check_escape(implode(', ', $check['active_old']))).'<br />';
  }

  return $html.klarna_category_check_notes_html($check);
}
