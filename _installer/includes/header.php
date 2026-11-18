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
  <meta name="referrer" content="same-origin" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
  <title>Installer</title>
  <link rel="stylesheet" type="text/css" href="templates/stylesheet.css?v=<?php echo filemtime(DIR_FS_INSTALLER.'templates/stylesheet.css'); ?>" />
  <link rel="stylesheet" type="text/css" href="templates/css/font-awesome.css?v=<?php echo filemtime(DIR_FS_INSTALLER.'templates/css/font-awesome.css'); ?>">
  <link rel="stylesheet" type="text/css" href="templates/css/prism-coy.min.css?v=<?php echo filemtime(DIR_FS_INSTALLER.'templates/css/prism-coy.min.css'); ?>" />
  <script src="templates/javascript/jquery.min.js" type="text/javascript"></script>
  <base href="<?php echo xtc_href_link(DIR_WS_INSTALLER); ?>" />
  <link rel="icon" type="image/png" href="<?php echo xtc_href_link(DIR_WS_INSTALLER.'images/favicon.ico', '', 'SSL'); ?>">
  <script src="templates/javascript/prism.min.js"></script>
  <script src="templates/javascript/prism-clike.min.js"></script>
  <script src="templates/javascript/prism-markup-templating.min.js"></script>
  <script src="templates/javascript/prism-php.min.js"></script>
  <script src="templates/javascript/prism-css.min.js"></script>
  <script src="templates/javascript/prism-javascript.min.js"></script>
  <script type="text/javascript">
    // every request that does something carries the token of this installer session
    var installer_token = '<?php echo installer_token(); ?>';
    function installerGo(url) {
      if (!/[?&]u?action=/.test(url)) {
        window.location.href = url;
        return;
      }
      var form = $('<form method="post" style="display:none;"></form>').attr('action', url);
      form.append($('<input type="hidden" name="installer_token" />').val(installer_token));
      $('body').append(form);
      form[0].submit();
    }
    $.ajaxSetup({headers: {'X-Installer-Token': installer_token}});
    $(document).on('submit', 'form', function() {
      if (String($(this).attr('method')).toLowerCase() == 'post' && $(this).find('input[name="installer_token"]').length < 1) {
        $(this).append($('<input type="hidden" name="installer_token" />').val(installer_token));
      }
    });
    $(document).on('click', 'a[href]', function(event) {
      if (this.host == window.location.host && /[?&]u?action=/.test(this.href) && $(this).attr('target') != '_blank') {
        event.preventDefault();
        installerGo(this.href);
      }
    });
  </script>
</head>
<body>
