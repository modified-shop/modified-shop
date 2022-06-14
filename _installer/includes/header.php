<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language_code']; ?>">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
  <title>Installer</title>
  <link rel="stylesheet" type="text/css" href="templates/stylesheet.css?v=<?php echo filemtime(DIR_FS_INSTALLER.'templates/stylesheet.css'); ?>" />
  <link rel="stylesheet" type="text/css" href="templates/css/font-awesome.css?v=<?php echo filemtime(DIR_FS_INSTALLER.'templates/css/font-awesome.css'); ?>">
  <script src="templates/javascript/jquery.min.js" type="text/javascript"></script>
  <base href="<?php echo xtc_href_link(DIR_WS_INSTALLER); ?>" />
  <link rel="icon" type="image/png" href="<?php echo xtc_href_link(DIR_WS_INSTALLER.'images/favicon.ico', '', 'SSL'); ?>">
</head>
<body>
