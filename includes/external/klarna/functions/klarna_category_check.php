<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


// Klarna payment method category => shop module, in the order the admin lists them
function klarna_category_module_map() {
  return array(
    'pay_later' => 'klarna_paylater',
    'pay_now' => 'klarna_paynow',
    'pay_over_time' => 'klarna_payovertime',
    'direct_debit' => 'klarna_directdebit',
    'direct_bank_transfer' => 'klarna_directbanktransfer',
    'klarna' => 'klarna_klarna',
  );
}


// returned categories against the installed and active shop modules, no output
function klarna_category_check_analyze($categories, $installed_codes, $active_codes) {
  $map = klarna_category_module_map();
  $result = array(
    'categories' => array(),
    'not_returned' => array(),
    'klarna_returned' => false,
    'multiple' => false,
  );

  $returned_modules = array();
  foreach ((array) $categories as $category) {
    if ($category instanceof ArrayObject) {
      $category = $category->getArrayCopy();
    }
    if (!is_array($category) || !isset($category['identifier']) || !is_string($category['identifier'])) {
      continue;
    }
    $identifier = $category['identifier'];
    $module = ((isset($map[$identifier])) ? $map[$identifier] : '');
    if ($module != '') {
      $returned_modules[] = $module;
    }
    $result['categories'][] = array(
      'identifier' => $identifier,
      'name' => ((isset($category['name']) && is_string($category['name'])) ? $category['name'] : ''),
      'module' => $module,
      'installed' => ($module != '' && in_array($module, $installed_codes, true)),
      'active' => ($module != '' && in_array($module, $active_codes, true)),
    );
    if ($identifier == 'klarna') {
      $result['klarna_returned'] = true;
    }
  }
  $result['multiple'] = (count($result['categories']) > 1);

  foreach ($map as $module) {
    if (in_array($module, $installed_codes, true) && !in_array($module, $returned_modules, true)) {
      $result['not_returned'][] = $module;
    }
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
           . '<td><b>'.MODULE_PAYMENT_KLARNA_CHECK_COL_MODULE.'</b></td>'
           . '<td><b>'.MODULE_PAYMENT_KLARNA_CHECK_COL_STATE.'</b></td></tr>';
    foreach ($check['categories'] as $category) {
      if ($category['module'] == '') {
        $state = MODULE_PAYMENT_KLARNA_CHECK_STATE_UNKNOWN;
      } elseif ($category['active']) {
        $state = MODULE_PAYMENT_KLARNA_CHECK_STATE_ACTIVE;
      } elseif ($category['installed']) {
        $state = MODULE_PAYMENT_KLARNA_CHECK_STATE_INACTIVE;
      } else {
        $state = MODULE_PAYMENT_KLARNA_CHECK_STATE_MISSING;
      }
      $html .= '<tr><td>'.klarna_category_check_escape($category['identifier'])
             . (($category['name'] != '') ? ' ('.klarna_category_check_escape($category['name']).')' : '').'</td>'
             . '<td>'.klarna_category_check_escape($category['module']).'</td>'
             . '<td>'.$state.'</td></tr>';
    }
    $html .= '</table>';
  }

  if (count($check['not_returned']) > 0) {
    $html .= '<br />'.sprintf(MODULE_PAYMENT_KLARNA_CHECK_NOT_RETURNED, klarna_category_check_escape(implode(', ', $check['not_returned']))).'<br />';
  }

  if ($check['klarna_returned']) {
    $html .= '<br />'.MODULE_PAYMENT_KLARNA_CHECK_RECOMMEND_KLARNA.'<br />';
  }
  if ($check['multiple']) {
    $html .= '<br />'.MODULE_PAYMENT_KLARNA_CHECK_RECOMMEND_MULTIPLE.'<br />';
  }

  return $html;
}
