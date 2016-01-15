<?php
  /* --------------------------------------------------------------
   $Id: head.php 5065 2013-07-15 12:22:56Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project (earlier name of osCommerce)
   (c) 2002-2003 osCommerce, www.oscommerce.com
   (c) 2003  nextcommerce, www.nextcommerce.org
   (c) 2006      xt:Commerce, www.xt-commerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------*/
  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );
  
  define('NEW_ADMIN_STYLE',true);
  
  if (defined('ADMIN_HEADER_X_FRAME_OPTIONS') && ADMIN_HEADER_X_FRAME_OPTIONS == 'true') { 
   header('X-Frame-Options: SAMEORIGIN'); // only in an iframe of the same site 
  }	  
?>
<!DOCTYPE html>
<html <?php echo HTML_PARAMS; ?>>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_SESSION['language_charset']; ?>">
  <title><?php echo TITLE; ?></title>
  <meta http-equiv="pragma" content="no-cache">
  <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">  
  <link rel="stylesheet" type="text/css" href="includes/searchbar_menu/searchbar_menu.css" />
  <link rel="stylesheet" type="text/css" href="includes/css/tooltip.css">
  <link rel="stylesheet" type="text/css" href="includes/css/jquery.alerts.css" />
  <?php if (NEW_SELECT_CHECKBOX == 'true') { ?>
  <link rel="stylesheet" type="text/css" href="includes/css/sumoselect_mod.css" />
  <link rel="stylesheet" type="text/css" href="includes/css/checks.css" />
  <link rel="stylesheet" type="text/css" href="includes/css/fileinput.css" />
  <?php } ?>
  <?php 
  if (USE_ADMIN_TOP_MENU != 'false') {
    echo '<link rel="stylesheet" type="text/css" href="includes/css/topmenu.css" />'. PHP_EOL;
  } else {
    echo '<link rel="stylesheet" type="text/css" href="includes/css/liststyle_left.css" />'. PHP_EOL;
  }
  if (USE_ADMIN_FIXED_TOP != 'true') {
    echo '<link rel="stylesheet" type="text/css" href="includes/css/fixed_top_none.css" />'. PHP_EOL;
  }
  foreach(auto_include(DIR_FS_ADMIN.'includes/extra/css/','php') as $file) require ($file);
  ?>

  <!--[if lt IE 9]><script src="includes/javascript/html5.js"></script><![endif]-->
  
  <script type="text/javascript" src="includes/javascript/jquery-1.8.3.min.js"></script>  
  <?php if (NEW_SELECT_CHECKBOX == 'true') { ?>
  <script type="text/javascript" src="includes/javascript/jquery.sumoselect_mod.js"></script>
  <script type="text/javascript">
    $(document).ready(function () {
      $('.SlectBox').not('.noStyling').SumoSelect({ createElems: 'mod', placeholder: '-'});
      $('.disableInputField').val('');
      $('.fileInput').change(function() {
        var basename_val = $(this).val().split(/[\\/]/).pop();
        $('#finput_'+ $(this).attr('id')).val(basename_val);
      });
    });
   </script>
  <?php } ?>
  <script type="text/javascript" src="includes/javascript/jquery.alerts.min.js"></script>
  <script type="text/javascript">
  /* <![CDATA[ */
    var js_button_yes = '<?php echo YES;?>';
    var js_button_no = '<?php echo NO;?>';
    var js_button_cancel = '<?php echo BUTTON_CANCEL;?>';
    var js_button_ok = '<?php echo BUTTON_REVIEW_APPROVE;?>';
    var js_submit;

    $.alerts.okButton = js_button_ok;
    $.alerts.overlayOpacity = .2;
    $.alerts.overlayColor = '#000';
    
    function ButtonClicked(button) {
      js_submit = button ;
    }    
    function alert(message, title) {
      title = title || 'Information';
      jAlert(message, title);
    }
    //confirmSubmit
    function confirmSubmit(message, title, form) { 
        title = title || 'Information';    
        jConfirm(message, title, function(r) {
          if (r) {      
            var addElement = $("<input type='hidden'/>");
            addElement.attr("name", js_submit.name).val(js_submit.value).appendTo(form);
            form.submit();
            addElement.remove();
            r.preventDefault();
          }   
        }, js_button_yes, js_button_no);            
        return false;
    }    
  /*]]>*/
  </script>
  <?php 
  foreach(auto_include(DIR_FS_ADMIN.'includes/extra/javascript/','php') as $file) require ($file);
  ?>