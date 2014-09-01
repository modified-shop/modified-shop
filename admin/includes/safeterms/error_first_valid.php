<?php

/* --------------------------------------------------------------
   $Id: error_first_valid.php

   This File indicates a error by the first valid of an api code

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommercecoding standards (a typical file) www.oscommerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------*/
?>
<!-- BOF HEADER.PHP -->
<div id="main">
	<div id="logo_safeterms">
		<img src="includes/safeterms/images/logo.png" />
	</div>
	<div id="box">
		<center>Der angegebene API Sicherheitsschl&uuml;ssel:<br /><big><strong><?php echo $safeterms_control["API_KEY"]; ?></strong></big><br /><br />
		konnte nicht validiert werden.<br /><br />
		Bitte &uuml;berpr&uuml;fen Sie Ihre Angaben und versuchen Sie es erneut.<br /><br />
		<a href="safeterms.php"><big><big><strong>Zur&uuml;ck</strong></big></big></a>
		<div style="clear:both;"></div>
	</div>
</div>