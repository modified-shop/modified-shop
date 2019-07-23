<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  chdir('../../');
  require_once ('includes/application_top_callback.php');
  
  if (defined('MODULE_API_IT_RECHT_KANZLEI_STATUS')
      && MODULE_API_IT_RECHT_KANZLEI_STATUS == 'true'
      )
  {
    require_once(DIR_FS_CATALOG.'api/it-recht-kanzlei/classes/class.api_it_recht_kanzlei.php');
    $api_it_recht_kanzlei = new api_it_recht_kanzlei();

    $xml_input = file_get_contents('php://input');
    $xml_output = rawurldecode(str_replace(array('xml=', '+'), array('', ' '), $xml_input));

    preg_match('/<user_auth_token>(.*)<\/user_auth_token>/', $xml_output, $check);
  
    if (is_array($check)
        && isset($check[1])
        && $check[1] == MODULE_API_IT_RECHT_KANZLEI_TOKEN
        )
    {
      $api_it_recht_kanzlei->process($xml_output);
    } else {
      $api_it_recht_kanzlei->return_error('12');
    }
  }
?>