<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2010 FINDOLOGIC GmbH - Version: 4.1 (216)

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  chdir('../../');
  require('includes/application_top.php');
  require_once (DIR_FS_EXTERNAL.'findologic/findologic_config.inc.php');

  // load needed function
  require_once (DIR_FS_INC.'get_external_content.inc.php');

  // do http-request
  $parameters = $_GET;
  $parameters['shopkey'] = FL_SHOP_ID;
  $parameters['revision'] = FL_REVISION;

  /* manually pass the arg_separator as '&' to avoid problems with different configurations */
  $url = FL_SERVICE_URL."autocomplete.php?" . http_build_query($parameters, '', '&');
  $content = get_external_content($url, FL_ALIVE_TEST_TIMEOUT, false);   
  echo $content;

?>