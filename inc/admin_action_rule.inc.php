<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  /**
   * admin_action_rule_pages()
   *
   * the admin pages under the rule with the values of action and saction that only read.
   * Every other value on these pages needs the posted form, a page that is not named is left as it is.
   * A file in admin/includes/extra/admin_action_rule/ can add, change or remove an entry of $pages
   *
   * @return array
   */
  function admin_action_rule_pages() {
    static $pages;

    if (!isset($pages)) {
      $list = array('action' => array('delete', 'edit', 'new'), 'saction' => array());
      $none = array('action' => array(), 'saction' => array());

      $pages = array(
        'backup' => array('action' => array('backup', 'delete', 'download', 'restore', 'restorelocal'), 'saction' => array()),
        'banner_manager' => $list,
        'blacklist_logs' => $list,
        'campaigns' => $list,
        'configuration' => $none,
        'countries' => $list,
        'cross_sell_groups' => $list,
        'csv_backend' => $none,
        'currencies' => $list,
        'customers_group' => $none,
        'customers_status' => $list,
        'econda' => $none,
        'geo_zones' => array('action' => array('delete_zone', 'edit_zone', 'list', 'new_zone'), 'saction' => array('delete', 'edit', 'new')),
        'gv_mail' => $none,
        'languages' => $list,
        'logs' => array('action' => array('delete', 'download'), 'saction' => array()),
        'mail' => $none,
        'manufacturers' => $list,
        'newsletter_recipients' => array('action' => array('delete', 'edit', 'export'), 'saction' => array()),
        'orders_status' => $list,
        'parcel_carriers' => $list,
        'products_vpe' => $list,
        'reviews' => $list,
        'scheduled_tasks' => $list,
        'server_info' => array('action' => array('nginx_config'), 'saction' => array()),
        'shipcloud_pickup' => $none,
        'shipping_status' => $list,
        'specials' => array('action' => array('delete', 'edit', 'multi_action', 'new'), 'saction' => array()),
        'stats_products_purchased' => array('action' => array('orders'), 'saction' => array()),
        'stats_products_viewed' => $none,
        'support' => $none,
        'tax_classes' => $list,
        'tax_rates' => $list,
        'trustedshops' => $list,
        'whos_online' => $none,
        'zones' => $list,
      );

      if (defined('DIR_FS_ADMIN')) {
        foreach (auto_include(DIR_FS_ADMIN.'includes/extra/admin_action_rule/', 'php') as $file) require_once ($file);
      }
    }

    return $pages;
  }


  /**
   * admin_action_rule()
   *
   * an action that is not read-only came from its form: a POST, with the form token when the
   * token system is on. Otherwise action and saction are removed before the page handles them
   *
   * @param string $page  file name of the admin page
   * @return boolean  false when a value was removed
   */
  function admin_action_rule($page) {
    $pages = admin_action_rule_pages();
    $page = basename($page, '.php');

    if (!isset($pages[$page]) || !is_array($pages[$page])) {
      return true;
    }

    require_once(DIR_FS_INC.'admin_form_posted.inc.php');
    if (admin_form_posted()) {
      return true;
    }
    // a page that is excluded from the token check takes a POST without the token, as before
    if (isset($GLOBALS['CSRFOptional'])
        && $GLOBALS['CSRFOptional'] === true
        && isset($_SERVER['REQUEST_METHOD'])
        && $_SERVER['REQUEST_METHOD'] == 'POST'
        )
    {
      return true;
    }

    $allowed = true;
    foreach (array('action', 'saction') as $key) {
      if (!isset($_GET[$key]) || $_GET[$key] === '') {
        continue;
      }
      if (!is_string($_GET[$key])
          || !isset($pages[$page][$key])
          || !is_array($pages[$page][$key])
          || !in_array($_GET[$key], $pages[$page][$key], true)
          )
      {
        unset($_GET[$key]);
        $allowed = false;
      }
    }

    return $allowed;
  }
