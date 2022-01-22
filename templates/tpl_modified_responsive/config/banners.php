<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );
?>
<div id="banner" class="admin_contentbox blog_container" style="display:none;">
  <div class="blog_title">
    <div class="blog_header">Bannergruppen f&uuml;r ihr Template</div>
  </div>
  <div class="blogentry">
    <div class="blog_desc">

      <div class="banner_headline">Empfohlene Bannereinstellungen f&uuml;r tpl_modified_responsive</div>
      <div class="banner_config">
        Konfiguration -> Bildeinstellungen<br />
        Breite der Banner-Bilder: 985 Pixel<br /> 
        H&ouml;he der Banner Bilder: 400 Pixel<br /> 
        Breite der Banner Bilder Mobil: 600 Pixel<br />
        H&ouml;he der Banner Bilder Mobil: 400 Pixel 
      </div>  

      <div class="banner_headline">Slider</div>
      <table class="banner">
        <tr>              
          <td style="width:100%">Bannergroup: <b>Slider</b><br />(Breite 100%)<br />Desktop: 985 x 400 Pixel<br />Mobil: 600 x 400 Pixel</td>
        </tr>
      </table>


    </div>
  </div>
</div>
<style>   
  .banner_headline {
    font-weight:bold;
    margin: 5px 0px;
    font-size:12px;
  }
  
  .banner_config {
    margin: 0px 0px 15px 0px;
    font-size:10px;
    line-height:16px;
  }      

  table.banner { 
    border: 4px solid #ccc;
    border-collapse: collapse;
    width:100%;
    margin: 0 0 15px 0;    
    font-size:10px;
    line-height:16px;
  }
  table.banner td { 
    border: 4px solid #ccc;
    background:#f5f5f5;
    border-collapse: collapse;
    text-align:center;
    padding: 10px;
  }

  .blog_title {
    padding: 9px 5px !important;
    margin-bottom:10px;
    border-bottom: 2px solid #af417e;
  }

  .blog_header {
    text-align: center;
    font-size: 12px;
    font-weight: bold;
  }
  .blogentry {
    display:none;
  }
</style>
<script type="text/javascript">
  $( document ).ready(function() {
    $('.boxCenterLeft').prepend($('#banner'));
    $('.tableConfig').before($('#banner'));
           
    $('#banner').show();
    $('#banner').on('click', function() {
      $('.blogentry').slideToggle();
    });
  });
</script>    
