<?php

/* -----------------------------------------------------------------------------------------
   $Id: media_content.php 831 2005-03-13 10:16:09Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce 
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2003	 nextcommerce (media_content.php,v 1.2 2003/08/25); www.nextcommerce.org
   
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

require ('includes/application_top.php');

$content_query = xtc_db_query("SELECT
	 				content_name,
	 				content_read,
 					content_file
 					FROM ".TABLE_PRODUCTS_CONTENT."
 					WHERE content_id='".(int) $_GET['coID']."'");
$content_data = xtc_db_fetch_array($content_query);

// update file counter

xtc_db_query("UPDATE 
			".TABLE_PRODUCTS_CONTENT." 
			SET content_read='". ($content_data['content_read'] + 1)."'
			WHERE content_id='".(int) $_GET['coID']."'");
?>

<html <?php echo HTML_PARAMS; ?>>
<head>
<title><?php echo $content_data['content_name']; ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo 'templates/'.CURRENT_TEMPLATE.'/stylesheet.css'; ?>">
<script type="text/javascript"><!--
var i=0;
function resize() {
  if (navigator.appName == 'Netscape') i=40;
  if (document.images[0]) window.resizeTo(document.images[0].width +30, document.images[0].height+60-i);
  self.focus();
}
//--></script>

</head>
<body onLoad="resize();">
 <?php

if ($content_data['content_file'] != '') {
	if (strpos($content_data['content_file'], '.txt'))
		echo '<pre>';

	if (preg_match('/.gif/i', $content_data['content_file']) or preg_match('/.jpg/i', $content_data['content_file']) or preg_match('/.png/i', $content_data['content_file']) or preg_match('/.tif/i', $content_data['content_file']) or preg_match('/.bmp/i', $content_data['content_file'])) { // Hetfield - 2009-08-19 - replaced deprecated function eregi with preg_match to be ready for PHP >= 5.3
//BOF - Dokuman - 2009-06-05 - replace table with div
	/*	echo '<table align="center" valign="middle" width="100%" height="100%" border=0><tr><td class="main" align="middle" valign="middle">';

		echo xtc_image(DIR_WS_CATALOG.'media/products/'.$content_data['content_file']);
		echo '</td></tr></table>';
	} else {
		echo '<table border="0" width="100%" cellspacing="0" cellpadding="2">
		          <tr>
		            <td class="main">';
		include (DIR_FS_CATALOG.'media/products/'.$content_data['content_file']);
		echo '</td>
		          </tr>
		        </table>';
	*/
		echo '<div>';
		echo xtc_image(DIR_WS_CATALOG.'media/products/'.$content_data['content_file']);
		echo '</div>';
	} else {
		echo '<div>';
		include (DIR_FS_CATALOG.'media/products/'.$content_data['content_file']);
		echo '</div>';
//EOF - Dokuman - 2009-06-05 - replace table with div
	}

	if (strpos($content_data['content_file'], '.txt'))
		echo '</pre>';
}
?>
</body>
</html>