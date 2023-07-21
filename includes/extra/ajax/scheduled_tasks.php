<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  include_once (DIR_FS_CATALOG.'includes/application_top_callback.php');

  function scheduled_tasks() {
    require_once(DIR_FS_CATALOG.'api/scheduled_tasks/cronjob.php');    
  }
