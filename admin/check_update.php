<?php
/* --------------------------------------------------------------
  $Id$

  modified eCommerce Shopsoftware
  http://www.modified-shop.org

  Copyright (c) 2009 - 2013 [www.modified-shop.org]
  --------------------------------------------------------------
  based on:
  (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
  (c) 2002-2003 osCommercecoding standards (a typical file) www.oscommerce.com
  (c) 2003 nextcommerce (start.php,v 1.6 2003/08/19); www.nextcommerce.org
  (c) 2006 XT-Commerce (credits.php 1263 2005-09-30)

  Released under the GNU General Public License
--------------------------------------------------------------*/

require ('includes/application_top.php');

// include needed functions
require_once(DIR_FS_INC.'check_version_update.inc.php');

// include needed classes
require_once(DIR_FS_CATALOG.'includes/classes/modified_api.php');

$action = (isset($_GET['action']) ? $_GET['action'] : '');

function rrmdir($dir) {    
  $dir = rtrim($dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
  if (is_dir(DIR_FS_CATALOG.$dir)) {
    $files = new DirectoryIterator(DIR_FS_CATALOG.$dir);
  
    foreach ($files as $file) {
      $filename = $file->getFilename();

      if ($file->isDot() === false) {
        if(is_dir(DIR_FS_CATALOG.$dir.$filename)) {
          rrmdir($dir.$filename);
        } else {
          unlink(DIR_FS_CATALOG.$dir.$filename);
        }
      }
    }
    rmdir(DIR_FS_CATALOG.$dir);
  }
}

if (isset($_GET['action'])
    && $_GET['action'] == 'autoupdate'
    )
{
  if (class_exists('ZipArchive')) {
    modified_api::reset();
    $response = modified_api::request('modified/version/install/installer');

    // cleanup
    rrmdir('download/tmp');
    rrmdir('_installer');

    // download
    if (mkdir(DIR_FS_CATALOG.'download/tmp', 0755)) {
      // save install
      $fp = fopen (DIR_FS_CATALOG.'download/tmp/'.$response['filename'], 'w+');
      $ch = curl_init($response['download']);
      curl_setopt($ch, CURLOPT_FILE, $fp); 
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_HEADER, false);
      curl_setopt($ch, CURLOPT_TIMEOUT, 10);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_exec($ch);
      curl_close($ch);
      fclose($fp);

      // extract install
      $zip = new ZipArchive();
      if ($zip->open(DIR_FS_CATALOG.'download/tmp/'.$response['filename']) === true) {
        if (is_dir(DIR_FS_CATALOG.'download/tmp/install')) {
          rrmdir('download/tmp/install');
        }
        mkdir(DIR_FS_CATALOG.'download/tmp/install', 0755, true);
    
        $zip->extractTo(DIR_FS_CATALOG.'download/tmp/install');
        $zip->close();
      } else {
        $messageStack->add_session('Corrupted download file');
        xtc_redirect(xtc_href_link(basename($PHP_SELF)));
      }
    
      // delete install
      unlink(DIR_FS_CATALOG.'download/tmp/'.$response['filename']);

      // process
      $shoproot = DIR_FS_CATALOG.'download/tmp/install/_installer';
      if (is_dir($shoproot)) {
        foreach ((new RecursiveIteratorIterator(new RecursiveDirectoryIterator($shoproot, RecursiveDirectoryIterator::SKIP_DOTS))) as $file) {
          $install_path = str_replace($shoproot, DIR_FS_CATALOG.'_installer', $file->getPath());
          $install_path = rtrim($install_path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
          $install_path = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $install_path);
                    
          if (!is_dir($install_path)) {
            mkdir($install_path, 0755, true);
          }    
          rename($file->getPathname(), $install_path.$file->getFilename());
        }
      }

      // cleanup
      rrmdir('download/tmp');
  
      // redirect
      $_SESSION['auth'] = true;
      xtc_redirect(xtc_href_link('../_installer/autoupdate.php'));
    } else {
      $messageStack->add_session('Could not create needed directory');
      xtc_redirect(xtc_href_link(basename($PHP_SELF)));
    }
  } else {
    $messageStack->add_session('Automatisches Update nicht möglich.');
    xtc_redirect(xtc_href_link(basename($PHP_SELF)));
  }
}

$content = check_version_update(false);

require (DIR_WS_INCLUDES.'head.php');
?>
</head>
<body>
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->

    <!-- body //-->
    <table class="tableBody">
      <tr>
        <?php //left_navigation
        if (USE_ADMIN_TOP_MENU == 'false') {
          echo '<td class="columnLeft2">'.PHP_EOL;
          echo '<!-- left_navigation //-->'.PHP_EOL;       
          require_once(DIR_WS_INCLUDES . 'column_left.php');
          echo '<!-- left_navigation eof //-->'.PHP_EOL; 
          echo '</td>'.PHP_EOL;      
        }
        ?>
        <!-- body_text //-->
        <td class="boxCenter">         
          <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_news.png'); ?></div>
          <div class="pageHeading pdg2"><?php echo HEADING_TITLE; ?></div>
          <span class="main"><?php echo HEADING_SUBTITLE; ?></span>
          <div class="clear"></div>
            
          <table class="tableCenter">      
            <tr>
              <td class="boxCenterLeft">
                <table class="tableBoxCenter collapse">
                  <?php            
                  foreach ($content['details'] as $heading => $modules) {
                    ?>
                    <tr class="dataTableHeadingRow">
                      <td class="dataTableHeadingContent"><?php echo $heading; ?></td>
                      <td class="dataTableHeadingContent txta-c" style="width:10%;"><?php echo TEXT_HEADING_INSTALLED; ?></td>
                      <td class="dataTableHeadingContent txta-c" style="width:10%;"><?php echo TEXT_HEADING_STATUS; ?></td>
                      <td class="dataTableHeadingContent txta-r" style="width:15%;"><?php echo TEXT_HEADING_VERSION_INTEGRATED; ?></td>
                      <td class="dataTableHeadingContent txta-r" style="width:15%;"><?php echo TEXT_HEADING_VERSION_AVAILABLE; ?></td>
                      <td class="dataTableHeadingContent txta-r" style="width:10%;"><?php echo TEXT_HEADING_ACTION; ?></td>
                    </tr>
                    <?php
                    foreach ($modules as $module => $data) {
                      $data['module'] = $module;
              
                      if ((!isset($_GET['module']) || (isset($_GET['module']) && ($_GET['module'] == $data['module']))) && !isset($mInfo)) {
                        $mInfo = new objectInfo($data);
                      }

                      if (isset($mInfo) && is_object($mInfo) && ($data['module'] == $mInfo->module) ) {
                        echo '<tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(basename($PHP_SELF), 'module=' . $mInfo->module . '&action=edit') . '\'">' . "\n";
                      } else {
                        echo '<tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(basename($PHP_SELF), 'module=' . $data['module']) . '\'">' . "\n";
                      }
                      ?>
                        <td class="dataTableContent"><?php echo $data['title']; ?></td>
                        <td class="dataTableContent txta-c">
                          <?php 
                          if ($data['installed'] == '1') {
                            echo xtc_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_INSTALLED, 12, 12, 'style="margin-left: 5px;"');
                          } elseif ($data['installed'] == '2') {
                            echo xtc_image(DIR_WS_IMAGES . 'icon_status_yellow.gif', IMAGE_ICON_STATUS_INACTIVE, 12, 12, 'style="margin-left: 5px;"');
                          } else {
                            echo xtc_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_NOT_INSTALLED, 12, 12, 'style="margin-left: 5px;"');
                          }
                          ?>
                        </td>
                        <td class="dataTableContent txta-c">
                          <?php 
                          if ($data['shop'] == 'undefined') {
                            echo xtc_image(DIR_WS_IMAGES . 'icon_status_yellow.gif', IMAGE_ICON_STATUS_UPDATE, 12, 12, 'style="margin-left: 5px;"');
                          } elseif ($data['update']) {
                            echo xtc_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_UPDATE, 12, 12, 'style="margin-left: 5px;"');
                          } else {
                            echo xtc_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_OK, 12, 12, 'style="margin-left: 5px;"');
                          }
                          ?>
                        </td>
                        <td class="dataTableContent txta-r"><?php echo $data['shop']; ?></td>
                        <td class="dataTableContent txta-r"><?php echo $data['version']; ?></td> 
                        <td class="dataTableContent txta-r"><?php if (isset($mInfo) && is_object($mInfo) && ($data['module'] == $mInfo->module) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(basename($PHP_SELF), 'module=' . $data['module']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
                      </tr>
                      <?php
                    }
                    echo '<tr><td colspan="5" style="height:35px;">&nbsp;</td></tr>';
                  }
                  ?>
                </table>
              </td>
              <?php
                $heading = array();
                $contents = array();
                switch ($action) {
                  default:
                    if (isset($mInfo) && is_object($mInfo)) {
                      $heading[] = array('text' => '<b>' . $mInfo->title . '</b>');
                      if ($mInfo->update) {
                        $contents[] = array('align' => 'center', 'text' => TEXT_INFO_UPDATE_NEEDED);
                        if ($mInfo->link != '') {
                          if ($mInfo->module == 'shop') {
                            $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . $mInfo->link . '">' . BUTTON_AUTOUPDATER . '</a>');
                          } else {
                            $contents[] = array('align' => 'center', 'text' => '<a class="button" target="_blank" onclick="this.blur();" href="' . $mInfo->link . '">' . BUTTON_MODULE_DOWNLOAD . '</a>');
                          }
                        }
                        $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link('support.php', 'module='.$mInfo->module) . '">' . BUTTON_OFFER . '</a>');
                      } else {
                        $contents[] = array('align' => 'center', 'text' => TEXT_INFO_UPDATE_OK);
                        if ($mInfo->link != '') {
                          $contents[] = array('align' => 'center', 'text' => '<a class="button" target="_blank" onclick="this.blur();" href="' . $mInfo->link . '">' . BUTTON_MODULE_DOWNLOAD . '</a>');
                        }
                      }
                    }
                    break;
                }

                if ( (xtc_not_null($heading)) && (xtc_not_null($contents)) ) {
                  echo '            <td class="boxRight">' . "\n";
                  $box = new box;
                  echo $box->infoBox($heading, $contents);
                  echo '            </td>' . "\n";
                }
              ?>
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
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>