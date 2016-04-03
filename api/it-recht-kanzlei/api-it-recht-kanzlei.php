<?php
/* -----------------------------------------------------------------------------------------
   $Id:$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  chdir('../../');
  require_once ('includes/application_top_callback.php');

  require_once(DIR_FS_CATALOG.'api/it-recht-kanzlei/classes/class.api_it_recht_kanzlei.php');
  $xml_input = file_get_contents('php://input');
  $xml_output = rawurldecode(str_replace(array('xml=', '+'), array('', ' '), $xml_input));
  
  $api_rechtskanzlei = new it_recht_kanzlei($xml_output);
?>