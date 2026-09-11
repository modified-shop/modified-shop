<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // an error page and an offline shop carry no data worth describing
  if (defined('MODULE_JSON_LD_STATUS') && MODULE_JSON_LD_STATUS == 'true'
      && $shop_is_offline === false
      && !isset($site_error)
      )
  {
    require_once(DIR_FS_CATALOG.'includes/classes/json_ld_graph.php');

    $json_ld = new json_ld_graph();

    if (MODULE_JSON_LD_ORGANIZATION == 'true') {
      $json_ld->addOrganization();
    }

    if (MODULE_JSON_LD_WEBSITE == 'true'
        && basename($PHP_SELF) === FILENAME_DEFAULT
        && !isset($_GET['cPath'])
        && !isset($_GET['manufacturers_id'])
        )
    {
      $json_ld->addWebSite();
    }

    if (MODULE_JSON_LD_PRODUCT == 'true'
        && basename($PHP_SELF) === FILENAME_PRODUCT_INFO
        && isset($product)
        )
    {
      $json_ld->addProduct($product);
    }

    // $listing_split exists only where a listing was really split, which is what separates a
    // category, search or specials page from the new products block of the start page
    if (isset($listing_split) && isset($module_content)) {
      $json_ld->addItemList($module_content, $listing_split);
    }

    if (MODULE_JSON_LD_BREADCRUMB == 'true' && isset($breadcrumb)) {
      $json_ld->addBreadcrumb($breadcrumb);
    }

    // the shop specific nodes the ticket asks for: each file may call $json_ld->add()
    foreach (auto_include(DIR_FS_CATALOG.'includes/extra/json_ld/', 'php') as $file) {
      require($file);
    }

    echo $json_ld->render();
  }
