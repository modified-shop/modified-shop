<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2003	 nextcommerce (content_preview.php,v 1.2 2003/08/25); www.nextcommerce.org
   
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
require('includes/application_top.php');
require_once (DIR_FS_INC.'get_content_file_path.inc.php');

// the input filter keeps pID numeric, so the product content comes with set=product
$preview_media = ((isset($_GET['set']) && $_GET['set'] == 'product') || (isset($_GET['pID']) && $_GET['pID'] == 'media'));


if ($preview_media) {
	$content_query=xtc_db_query("SELECT
 					content_file,
 					content_name,
 					file_comment
 					FROM ".TABLE_PRODUCTS_CONTENT."
 					WHERE content_id='".(int)$_GET['coID']."'");
 	$content_data=xtc_db_fetch_array($content_query);
	
} else {
	 $content_query=xtc_db_query("SELECT
 					content_title,
 					content_heading,
 					content_text,
 					content_file
 					FROM ".TABLE_CONTENT_MANAGER."
 					WHERE content_id='".(int)$_GET['coID']."'");
 	$content_data=xtc_db_fetch_array($content_query);
 }

require (DIR_WS_INCLUDES.'head.php');
?>

</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<div class="pageHeading"><?php echo (($preview_media) ? $content_data['content_name'] : $content_data['content_heading']); ?></div><br />
<table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main">
 <?php
 if ($content_data['content_file']!=''){
if (strpos($content_data['content_file'],'.txt') !== false) echo '<pre>';
if ($preview_media) {
	// display image
	// the stored name is checked before any output, the image link takes it encoded;
	// the checked target decides the type, the image URL also needs an image name;
	// the same folders as the shop: media/products/ first, then media/content/
	$content_folder = 'products';
	$content_file_path = get_content_file_path($content_data['content_file'], $content_folder, 'products');
	if ($content_file_path === false) {
	$content_folder = 'content';
	$content_file_path = get_content_file_path($content_data['content_file'], $content_folder, 'products');
	}
	if ($content_file_path !== false && preg_match('/\.(gif|jpe?g|png|tiff?|bmp)$/i', $content_file_path)) {
	if (preg_match('/\.(gif|jpe?g|png|tiff?|bmp)$/i', $content_data['content_file'])) {
	echo xtc_image(DIR_WS_CATALOG.'media/'.$content_folder.'/'.rawurlencode($content_data['content_file']));
	}
	$content_file_path = false;
	}
} else {
$content_file_path = get_content_file_path($content_data['content_file'], 'content');
}
// only PHP files placed on the server run as code, uploaded files are output as they are
if (isset($content_file_path) && $content_file_path !== false) {
  if (strtolower(substr($content_file_path, -4)) == '.php') {
    include ($content_file_path);
  } else {
    readfile($content_file_path);
  }
}
if (strpos($content_data['content_file'],'.txt') !== false) echo '</pre>';
 } else {	      
echo $content_data['content_text'];
}
?>
</td>
          </tr>
        </table>
</body>
</html>