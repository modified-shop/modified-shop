<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  /**
   * check_installed_modules()
   *
   * the lists of installed modules end up in include paths
   *
   * @param string $key configuration key
   * @param string $value configuration value
   * @return string the value, for a module list only its plain file names
   */
  function check_installed_modules($key, $value) {
    static $keys = array(
      'MODULE_CATEGORIES_INSTALLED' => true,
      'MODULE_CHECKOUT_INSTALLED' => true,
      'MODULE_EXPORT_INSTALLED' => true,
      'MODULE_MAIN_INSTALLED' => true,
      'MODULE_ORDER_INSTALLED' => true,
      'MODULE_ORDER_TOTAL_INSTALLED' => true,
      'MODULE_PAYMENT_INSTALLED' => true,
      'MODULE_PRODUCT_INSTALLED' => true,
      'MODULE_SHIPPING_INSTALLED' => true,
      'MODULE_SHOPPING_CART_INSTALLED' => true,
      'MODULE_SYSTEM_INSTALLED' => true,
      'MODULE_XTCPRICE_INSTALLED' => true,
    );

    if (!isset($keys[$key]) || !is_string($value) || $value == '') {
      return $value;
    }

    $modules = array();
    foreach (explode(';', $value) as $module) {
      if (preg_match('/^[A-Za-z0-9_]+\.php$/D', $module)) {
        $modules[] = $module;
      }
    }

    return implode(';', $modules);
  }
