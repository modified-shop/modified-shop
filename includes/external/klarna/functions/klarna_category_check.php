<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


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

    return $html;
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

  return $html;
}
