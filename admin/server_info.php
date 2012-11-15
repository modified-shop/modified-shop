<?php
  /* --------------------------------------------------------------
   $Id$

   xtcModified - community made shopping
   http://www.xtc-modified.org

   Copyright (c) 2009 - 2012 xtcModified
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(server_info.php,v 1.4 2003/03/17); www.oscommerce.com
   (c) 2003 nextcommerce (server_info.php,v 1.7 2003/08/18); www.nextcommerce.org
   (c) 2006 XT-Commerce (server_info.php 899 2005-04-29)

   Released under the GNU General Public License
   --------------------------------------------------------------*/

require('includes/application_top.php');
if (isset($_REQUEST['phpInfo'])) {
  phpinfo();
  exit;
}
$system = xtc_get_system_information();

require (DIR_WS_INCLUDES.'head.php');
?>
</head>
<body>
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
        <td class="boxCenter" width="100%" valign="top">
          <table border="0" width="100%" cellspacing="0" cellpadding="2">
            <tr>
              <td>
                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                  <tr>
                    <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
                    <td class="pageHeading" align="right"><?php echo xtc_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
                  </tr>
                </table>
              </td>
            </tr>
            <tr>
              <td>
                <table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td align="left">
                      <table border="0" cellspacing="0" cellpadding="3">
                        <tr>
                          <td class="smallText"><strong><?php echo TITLE_SERVER_HOST; ?></strong></td>
                          <td class="smallText"><?php echo $system['host'] . ' (' . $system['ip'] . ')'; ?></td>
                          <td class="smallText">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><?php echo TITLE_DATABASE_HOST; ?></strong></td>
                          <td class="smallText"><?php echo $system['db_server'] . ' (' . $system['db_ip'] . ')'; ?></td>
                        </tr>
                        <tr>
                          <td class="smallText"><strong><?php echo TITLE_SERVER_OS; ?></strong></td>
                          <td class="smallText"><?php echo $system['system'] . ' ' . $system['kernel']; ?></td>
                          <td class="smallText">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><?php echo TITLE_DATABASE; ?></strong></td>
                          <td class="smallText"><?php echo $system['db_version']; ?></td>
                        </tr>
                        <tr>
                          <td class="smallText"><strong><?php echo TITLE_SERVER_DATE; ?></strong></td>
                          <td class="smallText"><?php echo $system['date']; ?></td>
                          <td class="smallText">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><?php echo TITLE_DATABASE_DATE; ?></strong></td>
                          <td class="smallText"><?php echo $system['db_date']; ?></td>
                        </tr>
                        <tr>
                          <td class="smallText"><strong><?php echo TITLE_SERVER_UP_TIME; ?></strong></td>
                          <td colspan="3" class="smallText"><?php echo $system['uptime']; ?></td>
                        </tr>
                        <tr>
                          <td colspan="4"><?php echo xtc_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
                        </tr>
                        <tr>
                          <td class="smallText"><strong><?php echo TITLE_HTTP_SERVER; ?></strong></td>
                          <td colspan="3" class="smallText"><?php echo $system['http_server']; ?></td>
                        </tr>
                        <tr>
                          <td class="smallText"><strong><?php echo TITLE_PHP_VERSION; ?></strong></td>
                          <td colspan="3" class="smallText"><?php echo $system['php'] . ' (' . TITLE_ZEND_VERSION . ' ' . $system['zend'] . ')'; ?></td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
            <tr>
              <td><?php echo xtc_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
            </tr>
            <tr>
              <!--td>
              <iframe src="?phpInfo" width="100%" height="700" style="border: solid 1px #a3a3a3;">
              <p>Der verwendete Browser kann leider nicht mit inline Frames (iframe)
              umgehen:
              <a href="?phpInfo" target="_blank">Hier geht es zur phpinfo()
              Seite vom System</a>
              </p>
              </iframe>
              </td-->
              <td align="center">
                <div style="width:980px; border: solid 1px #a3a3a3; padding:10px; overflow:auto; height:600px;">
                  <?php
                  if (function_exists('ob_start')) {
                    ob_start();
                    phpinfo();
                    $phpinfo = ob_get_contents();
                    ob_end_clean();
                    $phpinfo = str_replace('border: 1px', '', $phpinfo);
                    //BOF - DokuMan - 2010-09-16 - replace ereg by preg_match
                    /*
                    ereg("(<style type=\"text/css\">{1})(.*)(</style>{1})", $phpinfo, $regs);
                    //BOF css border and link  correction
                    $regs[2] = str_replace('.e {', '.e {border: 1px solid #000000; ', $regs[2]);
                    $regs[2] = str_replace('.v {', '.v {border: 1px solid #000000; ', $regs[2]);
                    $regs[2] = str_replace('.h {', '.h {border: 1px solid #000000; ', $regs[2]);
                    $regs[2] = str_replace('.h {', '.h {border: 1px solid #000000; ', $regs[2]);
                    $regs[2] = str_replace('a:link', 'a.phpinfo:link', $regs[2]);
                    $regs[2] = str_replace('a:hover', 'a.phpinfo:hover', $regs[2]);
                    //EOF css border and link correction
                    echo '<style type="text/css">' . $regs[2] . '</style>';
                    ereg("(<body>{1})(.*)(</body>{1})", $phpinfo, $regs);
                    echo $regs[2];
                    */
                    preg_match('/<style type=\"text\/css\">(.*)<\/style>/msi', $phpinfo, $regs);
                    //BOF css border and link correction
                    $regs[1] = str_replace('.e {', '.e {border: 1px solid #000000; ', $regs[1]);
                    $regs[1] = str_replace('.v {', '.v {border: 1px solid #000000; ', $regs[1]);
                    $regs[1] = str_replace('.h {', '.h {border: 1px solid #000000; ', $regs[1]);
                    $regs[1] = str_replace('.h {', '.h {border: 1px solid #000000; ', $regs[1]);
                    $regs[1] = str_replace('img {float: right; border: 0px;}', '', $regs[1]);
                    $regs[1] = str_replace('a:link', 'a.phpinfo:link', $regs[1]);
                    $regs[1] = str_replace('a:hover', 'a.phpinfo:hover', $regs[1]);
                    //EOF css border and link correction
                    echo '<style type="text/css">' . $regs[1] . '</style>';
                    preg_match('/<body>(.*)<\/body>/msi', $phpinfo, $regs);
                    echo $regs[1];
                    //EOF - DokuMan - 2010-09-16 - replace ereg by preg_match
                  } else {
                    phpinfo();
                  }
                  ?>
                </div>
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
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>