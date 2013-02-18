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

$content_data = $main->getContentData($_GET['coID']);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html <?php echo HTML_PARAMS; ?>>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_SESSION['language_charset']; ?>" />
  <meta http-equiv="Content-Style-Type" content="text/css" />
  <meta name="robots" content="noindex, nofollow, noodp" />'
  <title><?php echo htmlspecialchars($content_data['content_heading'], ENT_QUOTES, strtoupper($_SESSION['language_charset']); ?></title>
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
        echo $content_data['content_text'];
        ?>
      </td>
    </tr>
  </table>
</body>
</html>
