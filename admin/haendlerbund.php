<?PHP
/* --------------------------------------------------------------
   $Id: psapi_import.php

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
require('includes/haendlerbund/haendlerbund_importer.php');

if($_GET["api_konfiguration"] == 1) {
  $contentimporter = new haendlerbund_importer();
  echo $contentimporter->process(1);
} else {
  require (DIR_WS_INCLUDES.'head.php');
?>

<link href="includes/haendlerbund/css/main.css" rel="stylesheet" type="text/css" />
<link href="http://fonts.googleapis.com/css?family=Cuprum" rel="stylesheet" type="text/css" />

<?php
// BOF - Tomcraft - 2014-08-13 - jquery-1.8.3.min.js already global available via "/admin/includes/head.php"
/*
<script src="includes/haendlerbund/jquery-1.4.4.js" type="text/javascript"></script>
*/
// EOF - Tomcraft - 2014-08-13 - jquery-1.8.3.min.js already global available via "/admin/includes/head.php"
?>
<script type="text/javascript" src="includes/haendlerbund/jquery.smartWizard.min.js"></script>
<script type="text/javascript" src="includes/haendlerbund/custom.js"></script>

</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
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
				<!-- body_text //-->
				<td width="100%" valign="top">
					<table border="0" width="100%" cellspacing="0" cellpadding="0">
						<tr>
							<td>
								<table border="0" width="100%" cellspacing="0" cellpadding="0">
									<tr>
										<td width="290"><a href="https://partner.haendlerbund.de/partnerdoor.php?partnerid=partner_modified&bannerid=64" target="_blank"><img src="includes/haendlerbund/images/haendlerbund_logo.png" hspace="0" vspace="0" style="padding:10px" /></a></td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td><div style="background-color:#387CB0; height:5px;"></div></td>
						</tr>
						<tr>
							<td width="100%" valign="top">
								<br/>
								<?PHP
								$contentimporter = new haendlerbund_importer();
								echo $contentimporter->process(0);
								echo $contentimporter->getImportForm();
								?>
							</td>
						</tr>
					</table>
				</td>
				<!-- body_text_eof //-->
			</tr>
		</table>
		<!-- body_eof //-->
		<!-- footer //-->
		<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
		<!-- footer_eof //-->
		<br />
	</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php');
}
?>
