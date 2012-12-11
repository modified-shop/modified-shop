<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   xtcModified - community made shopping
   http://www.xtc-modified.org

   Copyright (c) 2009 - 2012 xtcModified
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2006 XT-Commerce (general.js.php 1262 2005-09-30)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

 // this javascriptfile get includes at the BOTTOM of every template page in shop
 // you can add your template specific js scripts here
?>
<script type="text/javascript">var DIR_WS_BASE="<?php echo DIR_WS_BASE ?>"</script>

<?php //BOF - DokuMan - 2011-05-12 - load jQuery default library jquery.js from faster Google CDN
/*<script src="<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE; ?>/javascript/jquery.js" type="text/javascript"></script>*/
 ?>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js" type="text/javascript"></script>
<script type="text/javascript">
/* <![CDATA[ */
  !window.jQuery && document.write('<script src="<?php echo DIR_WS_BASE; ?>templates/<?php echo CURRENT_TEMPLATE; ?>/javascript/jquery_1.7.2.min.js" type="text/javascript"><\/script>');
/*]]>*/
</script>

<script src="<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE; ?>/javascript/jquery.colorbox-min.js" type="text/javascript"></script>
<script type="text/javascript">
/* <![CDATA[ */
$(document).ready(function() {
  $('a.thickbox').each(function(){
    if(typeof($(this).attr('rel')) != "undefined"
      && $(this).attr('rel') !== null
      && $(this).attr('rel') !== ""
      && $(this).attr('rel') !== "nofollow"
      && $(this).attr('rel') !== "NaN") {
      $(this).colorbox({
        opacity :0.5,
        rel     :$(this).attr('rel')
      });
    } else {
      var cwidth = 650;    // set to standard width
      var cheight = 450;   // set to standard height
      var checkW = getParam($(this).attr("href"), "width");
      if(checkW != "undefined" && checkW !== null && checkW !== "" && checkW !== "NaN") {
        cwidth = parseInt(checkW);
      }
      var checkH = getParam($(this).attr("href"), "height");
      if(checkH != "undefined" && checkH !== null && checkH !== "" && checkH !== "NaN") {
        cheight = parseInt(checkH);
      }
      if(typeof($(this).attr('data-width')) != "undefined") {
        var checkW = $(this).attr('data-width');
        if(checkW !== null && checkW !== "" && checkW !== "NaN") {
          cwidth = parseInt(checkW);
        }
      }
      if(typeof($(this).attr('data-height')) != "undefined") {
        var checkH = $(this).attr('data-height');
        if(checkH !== null && checkH !== "" && checkH !== "NaN") {
          cheight = parseInt(checkH);
        }
      }
      $(this).colorbox({
        iframe      :true,
        fastIframe  :true,
        opacity     :0.5,
        width       :cwidth,
        height      :cheight
      });
    }
  });
});
function getParam(url, name) {
  name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
  var regexS = "[\\?&]"+name+"=([^&#]*)";
  var regex = new RegExp( regexS );
  var results = regex.exec(url);
  if( results == null ) {
    return "";
  } else {
    return results[1];
  }
}
/*]]>*/
</script>

<script src="<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE; ?>/javascript/jquery.alerts.min.js" type="text/javascript"></script>
<script type="text/javascript">
/* <![CDATA[ */
  function alert(message, type) {
    type = type || 'Information';
    jAlert(message, type);
  }
  function confirm(message, type) {
    type = type || 'Information';
    jConfirm(message, type);
  }
/*]]>*/
</script>

<?php
  // BOF - web28 - 2010-07-26 - TABS/ACCORDION in product_info
  if (strstr($PHP_SELF, FILENAME_PRODUCT_INFO )) {
    //BOF - DokuMan - 2011-05-12 - load jQuery default library jquery-ui.js from faster Google CDN
    /*<script src="<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE; ?>/javascript/jquery-ui.js" type="text/javascript"></script>*/
    echo '<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js" type="text/javascript"></script>';
    //EOF - DokuMan - 2011-05-12 - load jQuery default library jquery-ui.js from faster Google CDN
?>
  <script src="<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE; ?>/javascript/jquery.cookie.js" type="text/javascript"></script>
  <script type="text/javascript">
  //<![CDATA[
    //Laden einer CSS Datei mit jquery
    $.get("<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE; ?>"+"/css/javascript.css",
    function(css) {
      $("head").append("<style type='text/css'>"+css+"<\/style>");
    });
    $(function() {
      $('#tabbed_product_info').tabs({
	      fx: { opacity: 'toggle' }
	      cookie: {
          // store cookie for a day, without, it would be a session cookie
          expires: 1
	      }
      });
      $("#accordion_product_info").accordion({ autoHeight: false });
    });
  //]]>
  </script>
<?php
  }
?>