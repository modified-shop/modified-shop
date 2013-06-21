<?PHP
/* --------------------------------------------------------------
   $Id: safeterms.php

   This File is to configure the Import of Law Relevat Text from
   the Platform Safeterms.de 

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommercecoding standards (a typical file) www.oscommerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------*/
  //VERSION

$modulVersion = "1.0";
require('includes/application_top.php');
require (DIR_WS_INCLUDES.'head.php');

// BOF INCLUDES FOR MODULE 
require ('includes/safeterms/talkto.php');
require ('includes/safeterms/functions.php');
// EOF INCLUDES FOR MODUL
?>

<!-- BOF Links to CSS & Fonts -->
<link href="includes/safeterms/css/main.css" rel="stylesheet" type="text/css" />
<link href="http://fonts.googleapis.com/css?family=Cuprum" rel="stylesheet" type="text/css" />
<!-- EOF Links to CSS & Fonts -->
<!-- BOF Links to JScripts -->
<!-- EOF Links to JScripts -->

</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">

	<!-- BOF Standard Admin Header -->

		<!-- header //-->
		<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
		<!-- header_eof //-->
		<!-- body //-->
		<table border="0" width="100%" cellspacing="2" cellpadding="2">
			<tr>
				<td class="columnLeft2" width="<?php echo BOX_WIDTH; ?>" valign="top">
					<table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
						<!-- left_navigation //-->
						<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
						<!-- left_navigation_eof //-->
					</table>
				</td>
				<td width="100%" valign="top">
	<!-- EOF Standard Admin Header -->

	<!-- BOF Safeterms Module Content -->

<?php
	// init the control data
	$safeterms_control = array();
	require('includes/safeterms/module_status.php');
	if ($safeterms_control["API_INSTALLED"]==1) {
		require('includes/safeterms/check_apikey.php');
	}

	// Looking for Action via POST
	if (@$_POST["action"]) {
		$action = $_POST["action"];
		if ($action == "new_apikey" && @$_POST["apikey"]<>"") {
			// a new API Key was added, lets checkit
			$safeterms_control["API_KEY"] = $_POST["apikey"];
			$nosave=1;
			require('includes/safeterms/check_apikey.php');
			if (@$safeterms_control["API_LASTVALID_RESULT"] == "FALSE") {
				// Including the Error Document for an API Verfication
				include("includes/safeterms/error_first_valid.php");
				print_r($safeterms_control);
			} elseif (@$safeterms_control["API_LASTVALID_RESULT"] == "TRUE") {
				// Including the Validation Document
				include("includes/safeterms/first_valid.php");
			}
		} elseif ($action == "new_apikey" && @!$_POST["apikey"]<>"") {
			$action="";
		} elseif ($action == "set_apikey" && @$_POST["apikey"]<>"") {
			// The new API KEY was valid and should be SET
			$safeterms_control["API_KEY"] = $_POST["apikey"];
			$safeterms_control["API_LASTVALID_RESULT"] = "TRUE";
			$safeterms_control["API_LASTVALID_TIME"] = time();
			$safeterms_control["LANGS"] = $_POST["lang"];
			include("includes/safeterms/set_apikey.php");
		} elseif ($action == "set_apikey" && @!$_POST["apikey"]<>"") {
			$action="";
		} elseif ($action == "start_update") {
			include("includes/safeterms/update.php");
		}
	}


	// IF THE API ISN't installed
	if ($safeterms_control["API_INSTALLED"]==0 && !$action) {
		// NEW CUSTOMER TEMPLATE
		include("includes/safeterms/new_customer.php");
	} elseif ($safeterms_control["API_INSTALLED"]==1 && !$action) {
		// NEW CUSTOMER TEMPLATE
		include("includes/safeterms/overview.php");
	}

?>

	<!-- EOF Safeterms Module Content -->

	<div id="disclaimer">
		Dieses Module wurde entwickelt und getestet unter der modified eCommerce Shopsoftware Version 1.06 und ist zur freien Nutzung freigegeben. Die Nutzung erfolgt auf eigene Gefahr! Es wird keine Haftung &uuml;bernommen. Mit der Nutzung stimmen Sie dem zu! Sollten Sie Fragen, W&uuml;nsche oder Kritik zum Modul haben, dann schreiben Sie uns: <a href="mailto:technik@safeterms.de">technik@safeterms.de</a>
	</div>

	<!-- BOF Standard Admin Footer -->
				</td>
			</tr>
		</table>
		<!-- body_eof //-->
		<!-- footer //-->
		<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
		<!-- footer_eof //-->
		<br />
	</body>
</html>
<?php
	require(DIR_WS_INCLUDES . 'application_bottom.php'); 
?>