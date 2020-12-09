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
    '_installer/buttons',
    '_installer/images/buttons',
    '_installer/inc',
    '_installer/includes/css',
    '_installer/includes/javascript',
    '_installer/includes/templates',
    '_installer/language',
    DIR_ADMIN.'includes/local',
    DIR_ADMIN.'includes/haendlerbund/images/_notes',
    DIR_ADMIN.'includes/modules/carp',
    DIR_ADMIN.'includes/modules/export/idealo_lib',
    DIR_ADMIN.'includes/modules/fckeditor',
    DIR_ADMIN.'includes/modules/filemanager/js/cors',
    DIR_ADMIN.'includes/modules/kcfinder',
    DIR_ADMIN.'includes/modules/magpierss',
    DIR_ADMIN.'includes/safeterms',
    DIR_ADMIN.'includes/xsbooster',
    DIR_ADMIN.'rss',
    'api/easybill', 
    'api/easymarketing',
    'api/findologic',
    'callback/pn_sofortueberweisung',
    'callback/masterpayment',
    'callback/xtbooster',
    'export/easybill',
    'export/idealo',
    'export/idealo_realtime',
    'images/infobox',
    'includes/classes/nusoap',
    'includes/classes/Smarty_2.6.22',
    'includes/classes/Smarty_2.6.26',
    'includes/classes/Smarty_2.6.27',
    'includes/external/easybill', 
    'includes/external/billsafe', 
    'includes/external/findologic',
    'includes/external/klarna/KITT',
    'includes/external/klarna/api',
    'includes/external/klarna/template',
    'includes/external/masterpayment',
    'includes/external/paypal/lib/Psr',
    'includes/external/phpfastcache/3.0.0', 
    'includes/external/phpfastcache/_extensions', 
    'includes/external/phpmailer/language',
    'includes/external/sofort/core',
    'includes/external/sofort/unittests',
    'includes/econda',
    'includes/iclear',
    'includes/janolaw',
    'includes/masterpayment',
    'includes/modules/payment/klarna',
    'includes/nusoap',
    'includes/shopgate',
    'shopstat',
    'sseq-filter',
    'sseq-lib',
    'callback/sofort/library',
    'callback/sofort/ressources',
    'xtc_installer',
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