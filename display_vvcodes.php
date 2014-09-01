<?php

/* -----------------------------------------------------------------------------------------
  $Id: display_vvcodes.php 831 2005-03-13 10:16:09Z mz $   

   Copyright (c) 2004 XT-Commerce
   -----------------------------------------------------------------------------------------
  
   Released under the GNU General Public License  
   ---------------------------------------------------------------------------------------*/

require ('includes/application_top.php');
require_once (DIR_FS_INC.'xtc_render_vvcode.inc.php');
require_once (DIR_FS_INC.'xtc_random_charcode.inc.php');

$visual_verify_code = xtc_random_charcode(6);
$_SESSION['vvcode'] = $visual_verify_code;
$vvimg = vvcode_render_code($visual_verify_code);
?>