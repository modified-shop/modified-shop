<?php
/* --------------------------------------------------------------
   $Id: products_attributes_iframe.php 7936 2015-03-18 14:30:01Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

if (!defined('NEW_TAGS_IFRAME_FILENAME')) {
  define ('NEW_TAGS_IFRAME_FILENAME','products_tags.php');
}

if (!defined('USE_TAGS_IFRAME')) {
  define ('USE_TAGS_IFRAME','true');
}

if (defined('USE_TAGS_IFRAME') && USE_TAGS_IFRAME == 'true') {

  function tags_iframe_link($pID, $icon=false)
  {
    global $icon_padding;
    if ($icon) {
      $link = '<a href="javascript:showproduct_tags(' . $pID . ');">' . xtc_image(DIR_WS_ICONS . 'icon_edit_tags.gif', TEXT_PRODUCTS_TAGS,'', '', $icon_padding). '</a>';
    } else {
      $link = '<a href="javascript:showproduct_tags(' . $pID . ');" class="button">'. TEXT_PRODUCTS_TAGS.'</a>';
    }
    return $link;
  }
?>
    
  <style>
    #bgboxshowtags {
        display:none; background:#ccc; position:fixed; width:100%; height:100%; top:0px; left:0px; z-index:9000; opacity:0.8; filter:alpha(opacity=80); /* For IE8 and earlier */
      }
    #bgboxshow_product_tags {
        width:95%; height:900px; max-height:900px; margin:auto; position:fixed; top:50%; left:50%; margin-top:-450px; margin-left:-48%; display:none; z-index:9001; 
      }
    @media screen and (max-height: 920px) { #bgboxshow_product_tags{max-height:800px; height:800px; margin-top:-400px;} }
    @media screen and (max-height: 820px) { #bgboxshow_product_tags{max-height:700px; height:700px; margin-top:-350px;} }
    @media screen and (max-height: 720px) { #bgboxshow_product_tags{max-height:600px; height:600px; margin-top:-300px;} }  
    @media screen and (max-height: 620px) { #bgboxshow_product_tags{max-height:500px; height:500px; margin-top:-250px;} }
    @media screen and (max-height: 520px) { #bgboxshow_product_tags{max-height:400px; height:400px; margin-top:-200px;} }    
    #bgboxshowtags_edit {
        width:100%; height:100%; background:#FFF; border:#000 1px solid; padding:1px;  
        box-shadow: 0 0 10px #000; -moz-box-shadow: 0 0 10px #000; -webkit-box-shadow: 0 0 10px #000); 
      }
    .bgboxshow_title { 
        font-family: Verdana,Arial, Helvetica, sans-serif; font-size: 13px; background:#555; color:#FFF; font-weight:bold; padding:3px; margin-bottom:1px;
      }
    .bgboxshow_close {
        width: 30px; height: 30px; background-image: url('images/close.png'); position: absolute; top:-10px; right:-17px; z-index: 1103; cursor: pointer; 
      }
  </style>
  
  <div id="bgboxshowtags" onclick="bgboxshowtags_close()"></div> 
    <div id="bgboxshow_product_tags">
      <div id="bgboxshowtags_edit">
    </div>
  </div>
  
  <script type="text/javascript">
    function showproduct_tags(pID) {
        if (document.getElementById("bgboxshow_product_tags").style.display =="none" || document.getElementById("bgboxshow_product_tags").style.display == "") {
            document.getElementById("bgboxshowtags").style.display = "block";
            document.getElementById("bgboxshow_product_tags").style.display = "block"; 
            document.getElementById('bgboxshowtags_edit').innerHTML = 
             '<div class="bgboxshow_title"><?php echo TEXT_PRODUCTS_TAGS; ?></div><div class="bgboxshow_close" onclick="bgboxshowtags_close()"> </div>' +
             '<iframe name="new_tags_iframe" src="<?php echo NEW_TAGS_IFRAME_FILENAME; ?>?iframe=1&current_product_id=' + pID + '" marginwidth="0" marginheight="0" width="100%" height="94%" border="0" frameborder="0"> </iframe>';
        } else {
            document.getElementById('bgboxshowtags_edit').innerHTML= '';
        }                 
    }
    function bgboxshowtags_close(){
        document.getElementById("bgboxshowtags").style.display="none";
        document.getElementById("bgboxshow_product_tags").style.display="none";
    }
  </script>
<?php 
  }
?>
