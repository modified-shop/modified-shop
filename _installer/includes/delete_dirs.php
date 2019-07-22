<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


  // set all directories to be deleted                     
  $unlink_dir = array(
    '_installer/buttons', // neu
    '_installer/images/buttons', // neu
    '_installer/inc', // neu
    '_installer/includes/css', // neu
    '_installer/includes/javascript', // neu
    '_installer/includes/templates', // neu
    '_installer/language', // neu
    DIR_ADMIN.'includes/local', // neu
    DIR_ADMIN.'includes/modules/carp',
    DIR_ADMIN.'includes/modules/export/idealo_lib', // neu
    DIR_ADMIN.'includes/modules/fckeditor',
    DIR_ADMIN.'includes/modules/kcfinder',
    DIR_ADMIN.'includes/modules/magpierss',
    DIR_ADMIN.'includes/modules/magpierss/extlib',
    DIR_ADMIN.'includes/safeterms/css',
    DIR_ADMIN.'includes/safeterms/images',
    DIR_ADMIN.'includes/safeterms',
    DIR_ADMIN.'includes/xsbooster', // neu
    DIR_ADMIN.'rss',
    'api/easybill',  // neu
    'api/easymarketing/includes',
    'api/easymarketing',
    'callback/pn_sofortueberweisung', // neu
    'callback/xtbooster', // neu
    'export/easybill', // neu
    'export/idealo', // neu
    'export/idealo_realtime', // neu
    'images/infobox', // neu
    'includes/classes/nusoap', // neu
    'includes/classes/Smarty_2.6.22',
    'includes/classes/Smarty_2.6.26',
    'includes/classes/Smarty_2.6.27', // neu
    'includes/external/easybill',  // neu
    'includes/external/billsafe',  // neu
    'includes/external/paypal/lib/Psr',
    'includes/external/phpfastcache/3.0.0',  // neu
    'includes/external/phpfastcache/_extensions',  // neu
    'includes/external/phpmailer/language', // neu
    'includes/external/sofort/core',
    'includes/external/sofort/unittests',
    'includes/econda', // neu
    'includes/iclear',
    'includes/janolaw',
    'includes/masterpayment', // neu
    'includes/nusoap', // neu
    'includes/shopgate', // neu
    'shopstat',
    'sseq-filter', // neu
    'sseq-lib', // neu
    'callback/sofort/library', // neu
    'callback/sofort/ressources', // neu
    'xtc_installer', // neu
  );


  if (!isset($unlinked_files)) {
    $unlinked_files = array(
      'error' => array(
        'files' => array(),
        'dir' => array(),
      ),
      'success' => array(
        'files' => array(),
        'dir' => array(),
      ),
    );
  }
  foreach ($unlink_dir as $unlink) {
    if (trim($unlink) != '' && is_dir(DIR_FS_DOCUMENT_ROOT.$unlink)) {  
      rrmdir($unlink);
    }
  }
?>