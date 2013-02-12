<?php
  /* --------------------------------------------------------------
   $Id: install_step3.php 3072 2012-06-18 15:01:13Z hhacker $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(install_3.php,v 1.6 2002/08/15); www.oscommerce.com
   (c) 2006 XT-Commerce www.xt-commerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------*/
  require('includes/application.php');

  include('language/'.$lang.'.php');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title>modified eCommerce Shopsoftware Installer - STEP 3 / DB Import</title>
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
  <table width="800" style="border:30px solid #fff;" border="0" align="center" cellpadding="0" cellspacing="0">
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
              <img src="images/step3.gif" width="705" height="180" border="0">
              <br />
              <br />
              <br />
              <div style="border:1px solid #ccc; background:#fff; padding:10px;">
                <?php echo TEXT_WELCOME_STEP3; ?>
              </div>
            </td>
          </tr>
        </table>
        <br />
        <table width="95%" border="0">
          <tr>
            <td>
              <?php 
                if(isset($_POST['install_db']) && $_POST['install_db'] == 1) {
                  $db = array();
                  $db['DB_SERVER'] = trim(stripslashes($_POST['DB_SERVER']));
                  $db['DB_SERVER_USERNAME'] = trim(stripslashes($_POST['DB_SERVER_USERNAME']));
                  $db['DB_SERVER_PASSWORD'] = trim(stripslashes($_POST['DB_SERVER_PASSWORD']));
                  $db['DB_DATABASE'] = trim(stripslashes($_POST['DB_DATABASE']));
                  xtc_db_connect_installer($db['DB_SERVER'], $db['DB_SERVER_USERNAME'], $db['DB_SERVER_PASSWORD']);
                  $sql = 'ALTER DATABASE '.$db['DB_DATABASE'].' DEFAULT CHARACTER SET '.$character_set.' COLLATE '.$collation.";";
                  @mysql_query($sql);
                  $sql = 'SET NAMES '.$character_set.' COLLATE '.$collation.";";
                  @mysql_query($sql);
                  $db_error = false;
                  $sql_file = DIR_FS_CATALOG . DIR_MODIFIED_INSTALLER.'/'.MODIFIED_SQL;
                  xtc_db_install($db['DB_DATABASE'], $sql_file);
                  if ($db_error) {
                ?>
                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                  <tr>
                    <td style="border-bottom: 1px solid; border-color: #CFCFCF">
                      <h1><?php echo TEXT_TITLE_ERROR; ?></h1>
                    </td>
                    <td style="border-bottom: 1px solid; border-color: #CFCFCF">&nbsp;</td>
                  </tr>
                </table>
                <table border="0" cellpadding="0" cellspacing="0" bgcolor="f3f3f3">
                  <tr>
                    <td><b><?php echo $db_error; ?></b></td>
                  </tr>
                </table>
                <form name="install" action="install_step3.php" method="post">
                  <?php echo draw_hidden_fields(); ?>
                  <table border="0" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                      <td align="center"><a href="index.php"><img src="buttons/<?php echo $lang;?>/button_cancel.gif" border="0" alt="Cancel"></a></td>
                      <td align="center"><input type="image" src="buttons/<?php echo $lang;?>/button_retry.gif" border="0" alt="Retry"></td>
                    </tr>
                  </table>
                </form>
                <?php
                  } else {
                ?>
                  <table width="100%" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                      <td align="center"><div style="border:1px solid #ccc; background:#fff; padding:10px;"><h1><?php echo TEXT_TITLE_SUCCESS; ?></h1></div></td>
                    </tr>
                  </table>
                  <form name="install" action="install_step4.php" method="post">
                    <?php echo draw_hidden_fields(); ?>
                    <br />
                    <table border="0" width="100%" cellspacing="0" cellpadding="0">
                      <tr>
                      <?php                        
                        if($_POST['install_cfg'] == 1) {                         
                      ?>
                          <td align="right"><input type="image" src="buttons/<?php echo $lang;?>/button_continue.gif"></td>
                      <?php
                        } else {
                      ?>
                          <td align="right"><a href="index.php"><img src="buttons/<?php echo $lang;?>/button_continue.gif"></a></td>
                      <?php
                        }
                      ?>
                      </tr>
                    </table>
                  </form>
                 <?php
                 }
               }
               ?>
               </td>
             </tr>
           </table>
         </td>
       </tr>
     </table>
    <br />
    <div align="center" style="font-family:Arial, sans-serif; font-size:11px;"><?php echo TEXT_FOOTER; ?></div>
  </body>
</html>
