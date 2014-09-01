<?php
  /* --------------------------------------------------------------
   $Id: install_finished.php 3072 2012-06-18 15:01:13Z hhacker $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2003 nextcommerce (install_finished.php,v 1.5 2003/08/17); www.nextcommerce.org
   (c) 2006 XT-Commerce www.xt-commerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  require('../admin/includes/configure.php');
  require('includes/application.php');  

  //BOF - web28 - 2010.02.11 - NEW LANGUAGE HANDLING IN application.php
  //include('language/'.$_SESSION['language'].'.php');
  include('language/'.$lang.'.php');
  //EOF - web28 - 2010.02.11 - NEW LANGUAGE HANDLING IN application.php

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title>modified eCommerce Shopsoftware Installer - Finished</title>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset;?>" />
    <style type="text/css">
      body { background: #eee; font-family: Arial, sans-serif; font-size: 12px;}
      table,td,div { font-family: Arial, sans-serif; font-size: 12px;}
      h1 { font-size: 18px; margin: 0; padding: 0; margin-bottom: 10px; }
      <!--
      .messageStackError, .messageStackWarning { font-family: Verdana, Arial, sans-serif; font-weight: bold; font-size: 10px; background-color: #; }
      -->
    </style>
  </head>
  <body>
    <table width="800" style="border:30px solid #fff;" bgcolor="#f3f3f3" border="0" align="center" cellpadding="0" cellspacing="0">
      <tr>
        <td height="95" colspan="2" >
          <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr>
              <td><img src="images/logo.png" alt="modified eCommerce Shopsoftware" /></td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td align="center" valign="top">
          <br />
          <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr>
              <td>
                <img src="images/step8.gif" width="705" height="180" border="0"><br />
                <br />
                <br />
                <div style="border:1px solid #ccc; background:#fff; padding:10px;"><?php echo TEXT_WELCOME_FINISHED; ?></div>
              </td>
            </tr>
          </table>
          <br />
          <table width="95%" border="0" cellpadding="0" cellspacing="0">
            <tr>
              <td>
                <div style="border:1px solid #ccc; background:#fff; padding:10px;">
                  <?php echo TEXT_SHOP_CONFIG_SUCCESS; ?><br />
                  <br />
                  <?php echo TEXT_TEAM; ?><br />
                  <br />
                  <a href="http://www.modified-shop.org/spenden" target="_blank"><img src="https://www.paypal.com/de_DE/DE/i/btn/btn_donateCC_LG.gif" alt="<?php echo TEXT_INFO_DONATIONS_IMG_ALT; ?>" border="0"></a>
                </div>
                <br />
                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                  <tr>
                    <td align="center">
                      <a href="<?php echo HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'index.php'; ?>" target="_blank">
                        <img src="buttons/<?php echo $lang;?>/button_catalog.gif" border="0" alt="Catalog">
                      </a>
                    </td>
                  </tr>
                </table>
                <p align="center">
                  <br />
                </p>
              </td>
            </tr>
          </table>
          <br />
        </td>
      </tr>
    </table>
    <br />
    <div align="center" style="font-family:Arial, sans-serif; font-size:11px;"><?php echo TEXT_FOOTER; ?><br /><img src='http://images.modified-shop.org/modified106sp1.gif' border='0' alt='modified eCommerce Shopsoftware'></div>
  </body>
</html>