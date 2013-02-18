<?php

/* -----------------------------------------------------------------------------------------
   $Id: popup_content.php 1169 2005-08-22 16:07:09Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2003	 nextcommerce (content_preview.php,v 1.2 2003/08/25); www.nextcommerce.org
   
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

require ('includes/application_top.php');

$content_data = $main->getContentData($_GET['coID']);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php echo HTML_PARAMS; ?>>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_SESSION['language_charset']; ?>" /> 
  <meta http-equiv="Content-Style-Type" content="text/css" />
  <meta name="robots" content="noindex, nofollow, noodp" />
  <title><?php echo htmlspecialchars($content_data['content_heading'], ENT_QUOTES, strtoupper($_SESSION['language_charset'])); ?></title>
  <base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>" />
  <link rel="stylesheet" type="text/css" href="<?php echo 'templates/'.CURRENT_TEMPLATE.'/stylesheet.css'; ?>" />
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