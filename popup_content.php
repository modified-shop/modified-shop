<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
  -----------------------------------------------------------------------------------------
   based on:
   (c) 2003 nextcommerce (content_preview.php,v 1.2 2003/08/25); www.nextcommerce.org
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

require ('includes/application_top.php');

$content_query = xtDBquery("SELECT
                            content_heading,
                            content_title,
                            content_text,
                            content_file
                           FROM ".TABLE_CONTENT_MANAGER."
                           WHERE content_group='".(int) $_GET['coID']."'
                           and languages_id = '".$_SESSION['languages_id']."'
                           LIMIT 1"); //DokuMan - 2011-05-13 - added LIMIT 1
$content_data = xtc_db_fetch_array($content_query, true);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_SESSION['language_charset']; ?>" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<title><?php echo $content_data['content_heading']; ?></title>
<?php /*
//BOF - GTB - 2010-08-03 - Security Fix - Base
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo 'templates/'.CURRENT_TEMPLATE.'/stylesheet.css'; ?>" />
*/ ?>
<link rel="stylesheet" type="text/css" href="<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/stylesheet.css'; ?>" />
<?php
//EOF - GTB - 2010-08-03 - Security Fix - Base
?>
</head>
<body style="background:#fff; font-family:Arial, Helvetica, sans-serif;">
<table width="100%" border="0" cellspacing="5" cellpadding="5">
  <tr>
    <td class="contentsTopics"><?php echo $content_data['content_heading']; ?></td>
  </tr>
</table>
<br />
<table border="0" width="100%" cellspacing="5" cellpadding="5">
  <tr>
    <td class="main" style="font-size:12px">
 <?php
if ($content_data['content_file'] != '') {
  if (strpos($content_data['content_file'], '.txt')) {
    echo '<pre>';
  }
  include (DIR_FS_CATALOG.'media/content/'.$content_data['content_file']);

  if (strpos($content_data['content_file'], '.txt')) {
    echo '</pre>';
  }
} else {
  echo $content_data['content_text'];
}
/*
<!--<br /><br />
<p class="smallText" align="right">

<script type="text/javascript">
document.write("<a href='javascript:window.close()'><?php echo TEXT_CLOSE_WINDOW; ?></a>")
</script>

<noscript><?php echo TEXT_CLOSE_WINDOW_NO_JS; ?></noscript>
</p>-->
*/
?>
    </td>
  </tr>
</table>
</body>
</html>