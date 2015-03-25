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

  // include Database functions for installer
  require_once(DIR_FS_INC_INSTALLER.'xtc_db_connect_installer.inc.php');
  require_once(DIR_FS_INC_INSTALLER.'xtc_db_select_db.inc.php');
  require_once(DIR_FS_INC_INSTALLER.'xtc_db_query_installer.inc.php');
  require_once(DIR_FS_INC_INSTALLER.'xtc_db_test_create_db_permission.inc.php');
  require_once(DIR_FS_INC_INSTALLER.'xtc_db_test_connection.inc.php');
  require_once(DIR_FS_INC_INSTALLER.'xtc_db_install.inc.php');

  include('language/'.$lang.'.php');

  require ('includes/header.php');
?>
  <table width="803" style="border:10px solid #fff;" bgcolor="#ffffff" border="0" align="center" cellpadding="0" cellspacing="0">
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
      <td align="left" valign="top">
        <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
          <tr>
            <td>
              <ul id="navigation" class="cf">
                <li class="inactive"><span class="number">&raquo;</span> <span class="title"><?php echo NAV_TITLE_INDEX; ?></span><br /><span class="description"><?php echo NAV_DESC_INDEX; ?></span></li>
                <li class="inactive"><span class="number">1.</span> <span class="title"><?php echo NAV_TITLE_STEP1; ?></span><br /><span class="description"><?php echo NAV_DESC_STEP1; ?></span></li>
                <li class="inactive"><span class="number">2.</span> <span class="title"><?php echo NAV_TITLE_STEP2; ?></span><br /><span class="description"><?php echo NAV_DESC_STEP2; ?></span></li>
                <li class="active last"><span class="number">3.</span> <span class="title"><?php echo NAV_TITLE_STEP3; ?></span><br /><span class="description"><?php echo NAV_DESC_STEP3; ?></span></li>
                <li class="inactive second_line"><span class="number">4.</span> <span class="title"><?php echo NAV_TITLE_STEP4; ?></span><br /><span class="description"><?php echo NAV_DESC_STEP4; ?></span></li>
                <li class="inactive second_line"><span class="number">5.</span> <span class="title"><?php echo NAV_TITLE_STEP5; ?></span><br /><span class="description"><?php echo NAV_DESC_STEP5; ?></span></li>
                <li class="inactive second_line"><span class="number">6.</span> <span class="title"><?php echo NAV_TITLE_STEP6; ?></span><br /><span class="description"><?php echo NAV_DESC_STEP6; ?></span></li>
                <!--
                <li class="inactive second_line"><span class="number">7.</span> <span class="title"><?php echo NAV_TITLE_STEP7; ?></span><br /><span class="description"><?php echo NAV_DESC_STEP7; ?></span></li>
                //-->
                <li class="inactive second_line last"><span class="number">&raquo;</span> <span class="title"><?php echo NAV_TITLE_FINISHED; ?></span><br /><span class="description"><?php echo NAV_DESC_FINISHED; ?></span></li>
              </ul>
              <br />
              <div style="border:1px solid #ccc; background:#f4f4f4; padding:10px;">
                <?php echo TEXT_WELCOME_STEP3; ?>
              </div>
            </td>
          </tr>
        </table>
        <br />
        <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
          <tr>
            <td>
              <?php 
                if(isset($_POST['install_db']) && $_POST['install_db'] == 1) {
                  $db = array();
                  $db['DB_MYSQL_TYPE'] = trim(stripslashes($_POST['DB_MYSQL_TYPE']));
                  $db['DB_SERVER'] = trim(stripslashes($_POST['DB_SERVER']));
                  $db['DB_SERVER_USERNAME'] = trim(stripslashes($_POST['DB_SERVER_USERNAME']));
                  $db['DB_SERVER_PASSWORD'] = trim(stripslashes($_POST['DB_SERVER_PASSWORD']));
                  $db['DB_DATABASE'] = trim(stripslashes($_POST['DB_DATABASE']));
                  xtc_db_connect_installer($db['DB_SERVER'], $db['DB_SERVER_USERNAME'], $db['DB_SERVER_PASSWORD'], $db['DB_MYSQL_TYPE']);
                  
                  @xtc_db_query_installer('ALTER DATABASE '.$db['DB_DATABASE'].' DEFAULT CHARACTER SET '.$character_set.' COLLATE '.$collation, $db['DB_MYSQL_TYPE']);
                  @xtc_db_query_installer('SET NAMES '.$character_set.' COLLATE '.$collation, $db['DB_MYSQL_TYPE']);

                  $db_error = false;
                  $sql_file_array = array(MODIFIED_SQL, 'includes/sql/banktransfer_blz.sql');
                  foreach ($sql_file_array as $sql_file) {
                    xtc_db_install($db['DB_DATABASE'], $db['DB_MYSQL_TYPE'], DIR_FS_CATALOG . DIR_MODIFIED_INSTALLER.'/'.$sql_file);
                  }
                  if ($db_error) {
                  ?>
                  <table width="100%" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                      <td style="border-bottom: 1px solid; border-color: #CFCFCF">
                        <h1><?php echo TEXT_TITLE_ERROR; ?></h1>
                      </td>
                    </tr>
                  </table>
                  <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="f4f4f4">
                    <tr>
                      <td><div style="background:#F2DEDE; color:#A94442; padding:10px; border:1px solid #DCA7A7"><b><?php echo $db_error; ?></b></div></td>
                    </tr>
                  </table>
                  <br />
                  <form name="install" action="install_step3.php" method="post">
                  <?php echo $input_lang; 
                        echo draw_hidden_fields(); ?>
                    <table border="0" width="100%" cellspacing="0" cellpadding="0">
                      <tr>
                        <td align="center"><a href="index.php?lg=<?php echo $lang .'&char='.INSTALL_CHARSET; ?>"><img src="images/buttons/<?php echo $lang;?>/button_cancel.gif" border="0" alt="Cancel"></a></td>
                        <td align="center"><input type="image" src="images/buttons/<?php echo $lang;?>/button_retry.gif" border="0" alt="Retry"></td>
                      </tr>
                    </table>
                  </form>
                  <?php
                  } else {
                  ?>
                    <table width="100%" border="0" cellpadding="0" cellspacing="0">
                      <tr>
                        <td align="center"><div style="border:1px solid #ccc; background:#f4f4f4; padding:10px;"><h1><?php echo TEXT_TITLE_SUCCESS; ?></h1></div></td>
                      </tr>
                    </table>
                    <form name="install" action="install_step4.php" method="post">
                    <?php echo $input_lang; 
                          echo draw_hidden_fields(); ?>
                      <br />
                      <table border="0" width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                        <?php                        
                          if($_POST['install_cfg'] == 1) {                         
                        ?>
                            <td align="right"><input type="image" src="images/buttons/<?php echo $lang;?>/button_continue.gif"></td>
                        <?php
                          } else {
                        ?>
                            <td align="right"><a href="index.php?lg=<?php echo $lang .'&char='.INSTALL_CHARSET; ?>"><img src="images/buttons/<?php echo $lang;?>/button_continue.gif"></a></td>
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