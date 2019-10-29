<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

require('includes/application_top.php');

// include needed functions
require_once (DIR_FS_INC.'xtc_php_mail.inc.php');

require_once(DIR_FS_CATALOG.DIR_WS_CLASSES.'modified_api.php');

$response = modified_api::request('modified/support/'.$_SESSION['language_code']);

$error = false;
$error_field = array();

if (isset($_GET['action'])) {
  switch ($_GET['action']) {
    case 'send':
      foreach ($response['form'] as $data) {
        if ($data['required'] == true
            && (!isset($_POST[$data['name']])
                || $_POST[$data['name']] == ''
                )
            )
        {
          $error = true;
          $error_field[$data['name']] = true;
        }        
      }
      
      if (isset($response['privacy'])
          && $response['privacy']['required'] == true
          && (!isset($_POST[$response['privacy']['name']])
              || $_POST[$response['privacy']['name']] != 'true'
              )
          )
      {
        $error = true;
        $error_field[$response['privacy']['name']] = true;
        
      }

      if ($error === false) {
        $system_informations = array(
          'PHP Version' => phpversion(),
          'Shop Domain' => HTTP_SERVER,
          'Shop Version' => PROJECT_VERSION,
          'DB Version' => DB_VERSION,
        );
        $message_array = array_merge($_POST, $system_informations);
        
        $message = '';
        foreach ($message_array as $k => $v) {
          $message .= $k . ': ' . $v . "\n";
        }
        
        xtc_php_mail(EMAIL_SUPPORT_ADDRESS, 
                     EMAIL_SUPPORT_NAME, 
                     $response['mail']['address'], 
                     $response['mail']['name'], 
                     EMAIL_SUPPORT_FORWARDING_STRING, 
                     EMAIL_SUPPORT_REPLY_ADDRESS, 
                     EMAIL_SUPPORT_REPLY_ADDRESS_NAME, 
                     '', 
                     '', 
                     $response['mail']['subject'].STORE_NAME, 
                     nl2br($message), 
                     $message);
        
        $messageStack->add_session($response['stack']['success'], 'success');
        xtc_redirect(xtc_href_link(basename($PHP_SELF)));
      } else {
        $messageStack->add($response['stack']['error'], 'warning');
      }
      break;
  }
}

require (DIR_WS_INCLUDES.'head.php');
?>
  <style type="text/css">
    .error {
      background:#F2DEDE !important;
    }
    .information_message {
      margin: 10px 0 0 0;
      color: #555;
      background-color: #fff3cd;
      border: 1px solid #efb600;
      padding:10px;
    }
    table.support_table {
      margin: 5px 0 10px 0;    
    }
    table.support_table tr td {
      vertical-align:top;    
    }
    table.support_table tr td:first-child {
      padding-top:15px;    
    }
    table.support_table textarea {
      -moz-box-sizing: border-box;
      -webkit-box-sizing: border-box;
      box-sizing: border-box;
      background-color: #fafafa;
      border-color: #c6c6c6 #dadada #eaeaea;
      -webkit-border-radius: 2px;
      -moz-border-radius: 2px;
      border-radius: 2px;
      border-style: solid;
      border-width: 1px;
      color: #000;
      padding: 6px 4px;
    }
    .privacy {
      margin: 0px 0px 10px 0;    
      padding:8px;
    }
    .privacy label {
      cursor:pointer;    
    }
    .privacy a {
      font-size:12px !important;
      color:#AF417E !important;    
    }
    table.privacy_table tr td {
      padding:0;
      vertical-align:top;    
    }
    table.privacy_table tr td:first-child {
      width:26px;    
    }
    table.privacy_table tr td:first-child input[type=checkbox] {
      margin: 1px 0px 0px -1px;    
    }
  </style>
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
          <div id="support">
            <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_news.png'); ?></div>
            <div class="pageHeading pdg2"><?php echo HEADING_TITLE; ?></div>
            <span class="main"><?php echo HEADING_SUBTITLE; ?></span>
            <div class="clear"></div>
            
            <div class="div_box brd-none pdg2">
              <?php
              if (!is_array($response)) {
                echo '<div class="description">'.TEXT_SUPPORT_ALTERNATIVE.'</div>';
              } else {
                ?>
                <div class="description"><?php echo $response['description']; ?></div>
                <div class="information_message"><?php echo $response['warning']; ?></div>
                <?php echo xtc_draw_form('mail', basename($PHP_SELF), xtc_get_all_get_params(array('action')).'action=send'); ?>
                  <table class="tableConfig borderall support_table">
                  <?php
                    foreach ($response['form'] as $data) {
                      ?>
                      <tr>
                        <td class="dataTableConfig col-left"><?php echo $data['text']; ?></td>
                        <td class="dataTableConfig col-single-right<?php echo ((isset($error_field[$data['name']])) ? ' error' : ''); ?>"><?php echo sprintf($data['field'], $data['name'], ((isset($_POST[$data['name']])) ? xtc_db_input($_POST[$data['name']]) : '')); ?></td>
                      </tr>
                      <?php
                    }
                  ?>
                  </table>
                  
                  <div class="privacy<?php echo ((isset($error_field[$response['privacy']['name']])) ? ' error' : ''); ?>">
                    <table class="privacy_table">
                      <tr>
                        <td><?php echo sprintf($response['privacy']['checkbox'], ((isset($_POST[$response['privacy']['name']])) ? 'id="accept_privacy" checked="checked"' : 'id="accept_privacy"')); ?></td>
                        <td><label for="accept_privacy"><?php echo $response['privacy']['text']; ?></label></td>
                      </tr>
                    </table>
                  </div>
                  
                  <div class="smallText mrg5 txta-r"><?php echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_SEND_EMAIL . '"/>'; ?></div>
                </form>
                <?php
              }
              ?>
            </div>
            
          </div>
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