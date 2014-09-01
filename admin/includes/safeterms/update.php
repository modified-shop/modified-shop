<?php

/* --------------------------------------------------------------
   $Id: update.php

   This File starts the update function

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

<div id="main">
	<div id="logo_safeterms">
		<img src="includes/safeterms/images/logo.png" />
	</div>
	<div id="box">
		<div id="content_title_full">
			Safeterms UPDATE (API-Key: <?php echo $safeterms_control["API_KEY"]; ?>)
		</div>
		<div id="content_full">
			<p>Das Update wird gestartet...<br /><br /></p>
			<?php
				$updres = safeterms_update();
				echo $updres[1];

				if ($updres[0]=="1") {
					// Es ist ein Fehler aufgetreten
					echo '<p style="color:#ff0000; font-size:18px;"><br /><br />Es sind Fehler w&auml;hrend des Updates aufgetreten!<br />Bitte versuchen Sie es erenut.</p>';
				} else {
					// Es ist ein Fehler aufgetreten
					echo '<p style="color:green; font-size:18px;"><br /><br />Das Update verlief fehlerfrei, Ihre Rechtstexte wurden erfolgreich importiert</p>';
				}
				
			?>
			<p style="margin:50px;text-align:center; font-size:20px;"><a href="safeterms.php" style="font-size:26px;">Zur&uuml;ck zur &Uuml;bersicht &gt;&gt;&gt;</a></strong></p>
		</div>
			
		<div style="clear:both;"></div>
	</div>
</div>