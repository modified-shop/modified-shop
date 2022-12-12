<?php

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

?>

<style>
    .iframeBox {
        display:none; background:#ccc; position:fixed; width:100%; height:100%; top:0px; left:0px; z-index:9000; opacity:0.8; filter:alpha(opacity=80); /* For IE8 and earlier */
      }
    .iframeBox_wrap {
        width:95%; height:900px; max-height:900px; margin:auto; position:fixed; top:50%; left:50%; margin-top:-450px; margin-left:-48%; display:none; z-index:9001; 
      }
    @media screen and (max-height: 920px) { .iframeBox_wrap{max-height:800px; height:800px; margin-top:-400px;} }
    @media screen and (max-height: 820px) { .iframeBox_wrap{max-height:700px; height:700px; margin-top:-350px;} }
    @media screen and (max-height: 720px) { .iframeBox_wrap{max-height:600px; height:600px; margin-top:-300px;} }  
    @media screen and (max-height: 620px) { .iframeBox_wrap{max-height:500px; height:500px; margin-top:-250px;} }
    @media screen and (max-height: 520px) { .iframeBox_wrap{max-height:400px; height:400px; margin-top:-200px;} }    
    .iframeBox_iframe {
        width:100%; height:100%; background:#FFF; border:#000 1px solid; padding:1px;  
        box-shadow: 0 0 10px #000; -moz-box-shadow: 0 0 10px #000; -webkit-box-shadow: 0 0 10px #000; 
      }
    .iframeBox_title { 
        font-family: Verdana,Arial, Helvetica, sans-serif; font-size: 13px; background:#555; color:#FFF; font-weight:bold; padding:3px; margin-bottom:1px;
      }
    .iframeBox_close {
        width: 30px; height: 30px; background-image: url('images/close.png'); position: absolute; top:-10px; right:-17px; z-index: 1103; cursor: pointer; 
      }
  </style>
  
  <div class="iframeBox" onclick="iframeBox_close()"></div> 
    <div class="iframeBox_wrap">
      <div class="iframeBox_iframe">
    </div>
  </div>
  
  <script type="text/javascript">
    function iframeBox_show(pID,title,filename,params) {
        if ($('.iframeBox_wrap').not(':visible') ) {
            params = params || '';
            $('.iframeBox').show();
            $('.iframeBox_wrap').show();
            $('.iframeBox_iframe').html( 
             '<div class="iframeBox_title">' + title + '</div><div class="iframeBox_close" onclick="iframeBox_close()"> </div>' +
             '<iframe name="new_iframe" src="' + filename + '?iframe=1' + params + '&current_product_id=' + pID + '" marginwidth="0" marginheight="0" width="100%" height="94%" border="0" frameborder="0"> </iframe>');
        } else {
            $('.iframeBox_iframe').html('');
        }                 
    }
    function iframeBox_close(){
        $('.iframeBox').hide();
        $('.iframeBox_wrap').hide();
    }
  </script>