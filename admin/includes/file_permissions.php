<?php
/* --------------------------------------------------------------
   $Id: file_permissions.php 3119 2012-06-23 14:45:52Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2003 nextcommerce (security_check.php,v 1.2 2003/08/23); www.nextcommerce.org
   (c) 2006 xt-commerce (security_check.php 1221 2005-09-20); www.xt-commerce.com
   (c) 2011 WEB-Shop Software http://www.webs.de/

   Released under the GNU General Public License
   --------------------------------------------------------------*/

$configFiles = array(
  DIR_FS_CATALOG.'includes/configure.php',
  DIR_FS_CATALOG.'includes/local/configure.php',
  //DIR_FS_ADMIN.'includes/configure.php',
);

$writeableFiles = array(
  DIR_FS_ADMIN.'magnalister.php',
  DIR_FS_CATALOG.'magnaCallback.php',
  DIR_FS_CATALOG.'sitemap.xml',
);

$writeableJanolawFiles = array(
  DIR_FS_CATALOG.'media/content/datenschutzerklaerung.html',
  DIR_FS_CATALOG.'media/content/agb.html',
  DIR_FS_CATALOG.'media/content/impressum.html',
  DIR_FS_CATALOG.'media/content/widerrufsbelehrung.html',
);

$writeableDirs = array(
  DIR_FS_ADMIN.'backups',
  DIR_FS_ADMIN.'images/graphs',
  DIR_FS_ADMIN.'images/icons',
  DIR_FS_CATALOG.'cache',
  DIR_FS_CATALOG.'export',
  DIR_FS_CATALOG.'export/easybill',
  DIR_FS_CATALOG.'export/idealo_realtime',
  DIR_FS_EXTERNAL.'magnalister',
  DIR_FS_EXTERNAL.'shopgate/shopgate_library/config',
  DIR_FS_CATALOG.'images',
  DIR_FS_CATALOG.'images/banner',
  DIR_FS_CATALOG.'images/categories',
  DIR_FS_CATALOG.'images/content',
  DIR_FS_CATALOG.'images/product_images/info_images',
  DIR_FS_CATALOG.'images/product_images/original_images',
  DIR_FS_CATALOG.'images/product_images/popup_images',
  DIR_FS_CATALOG.'images/product_images/thumbnail_images',
  DIR_FS_CATALOG.'images/manufacturers',
  DIR_FS_CATALOG.'images/icons',
  DIR_FS_CATALOG.'import',
  DIR_FS_CATALOG.'log',
  DIR_FS_CATALOG.'media/content/',
  DIR_FS_CATALOG.'media/products',
  DIR_FS_CATALOG.'media/products/backup',
  DIR_FS_CATALOG.'templates/tpl_modified',
  DIR_FS_CATALOG.'templates/xtc5',
  DIR_FS_CATALOG.'templates_c',
);

$nonWriteableDirs = array(
  DIR_FS_ADMIN,
  DIR_FS_ADMIN.'includes',
  DIR_FS_CATALOG.'includes',
);