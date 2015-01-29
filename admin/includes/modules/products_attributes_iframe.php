<?php
/* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

if (!defined('NEW_ATTRIBUTES_IFRAME_FILENAME')) {
	define ('NEW_ATTRIBUTES_IFRAME_FILENAME','new_attributes.php');
}

if (!defined('USE_ATTRIBUTES_IFRAME')) {
	define ('USE_ATTRIBUTES_IFRAME','true');
}

if (defined('USE_ATTRIBUTES_IFRAME') && USE_ATTRIBUTES_IFRAME == 'true') {

	function attributes_iframe_link($pID, $icon=false)
	{
		global $icon_padding;
		if ($icon) {
			$link = '<a href="javascript:showproduct_attribute(' . $pID . ');">' . xtc_image(DIR_WS_ICONS . 'icon_edit_attr.gif', BUTTON_EDIT_ATTRIBUTES,'', '', $icon_padding). '</a>';
		} else {
			$link = '<a href="javascript:showproduct_attribute(' . $pID . ');" class="button">'. BUTTON_EDIT_ATTRIBUTES.'</a>';
		}
		return $link;
	}
?>
		
	<style>
		#bgboxshow {
				display:none; background:#ccc; position:fixed; width:100%; height:100%; top:0px; left:0px; z-index:9000; opacity:0.8; filter:alpha(opacity=80); /* For IE8 and earlier */
			}
		#bgboxshow_product_attribute {
				width:95%; height:900px; max-height:900px; margin:auto; position:fixed; top:50%; left:50%; margin-top:-450px; margin-left:-48%; display:none; z-index:9001; 
			}
		@media screen and (max-height: 920px) { #bgboxshow_product_attribute{max-height:800px; height:800px; margin-top:-400px;} }
		@media screen and (max-height: 820px) { #bgboxshow_product_attribute{max-height:700px; height:700px; margin-top:-350px;} }
		@media screen and (max-height: 720px) { #bgboxshow_product_attribute{max-height:600px; height:600px; margin-top:-300px;} }	
		@media screen and (max-height: 620px) { #bgboxshow_product_attribute{max-height:500px; height:500px; margin-top:-250px;} }
		@media screen and (max-height: 520px) { #bgboxshow_product_attribute{max-height:400px; height:400px; margin-top:-200px;} }		
		#bgboxshow_edit {
				width:100%; height:100%; background:#FFFFFF; border:#000000 1px solid; padding:1px;  
				border-radius:5px; -moz-border-radius:5px; -webkit-border-radius:5px; box-shadow: 0 0 10px #000000; -moz-box-shadow: 0 0 10px #000000; -webkit-box-shadow: 0 0 10px #000000); 
			}
		.bgboxshow_title { 
				font-family: Verdana,Arial, Helvetica, sans-serif; font-size: 13px; background:#597BC6; color:#FFFFFF; font-weight:bold; padding:3px; margin-bottom:1px; border-top-left-radius:5px; border-top-right-radius:5px; -moz-border-radius-topleft:5px; -moz-border-radius-topright:5px; -webkit-border-top-right-radius:5px;  -webkit-border-top-left-radius:5px;
			}
		.bgboxshow_close {
				width: 30px; height: 30px; background-image: url('images/close.png'); position: absolute; top:-10px; right:-17px; z-index: 1103; cursor: pointer; 
			}
	</style>
	
	<div id="bgboxshow" onclick="bgboxshow_close()"></div> 
		<div id="bgboxshow_product_attribute">
			<div id="bgboxshow_edit">
		</div>
	</div>
	
	<script type="text/javascript">
		function showproduct_attribute(pID) {
				if (document.getElementById("bgboxshow_product_attribute").style.display =="none" || document.getElementById("bgboxshow_product_attribute").style.display == "") {
						document.getElementById("bgboxshow").style.display = "block";
						document.getElementById("bgboxshow_product_attribute").style.display = "block";	
				    document.getElementById('bgboxshow_edit').innerHTML = 
			       '<div class="bgboxshow_title"><?php echo BUTTON_EDIT_ATTRIBUTES; ?></div><div class="bgboxshow_close" onclick="bgboxshow_close()"> </div>' +
			       '<iframe name="new_attributes_iframe" src="<?php echo NEW_ATTRIBUTES_IFRAME_FILENAME; ?>?iframe=1&action=edit&current_product_id=' + pID + '" marginwidth="0" marginheight="0" width="100%" height="94%" border="0" frameborder="0"> </iframe>';
        }	else {
            document.getElementById('bgboxshow_edit').innerHTML= '';
        }			 	       	  
		}
		function bgboxshow_close(){
				document.getElementById("bgboxshow").style.display="none";
				document.getElementById("bgboxshow_product_attribute").style.display="none";
		}
	</script>
<?php 
	}
?>
