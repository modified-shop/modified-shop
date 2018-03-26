<?php
  /* --------------------------------------------------------------
   $Id: footer.php 3072 2012-06-18 15:01:13Z hhacker $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(footer.php,v 1.12 2003/02/17); www.oscommerce.com 
   (c) 2003	 nextcommerce (footer.php,v 1.11 2003/08/18); www.nextcommerce.org
   (c) 2006 XT-Commerce (footer.php 899 2005-04-29)

   Released under the GNU General Public License 
   --------------------------------------------------------------*/
   defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );
?>
<div id="footer">
    <div class="smallText">
      <?php
      /*
      The following copyright announcement is in compliance
      to section 2c of the GNU General Public License, and
      thus can not be removed, or can only be modified
      appropriately.

      Please leave this comment intact together with the
      following copyright announcement.
  
      Copyright announcement changed due to the permissions
      from LG Hamburg from 28th February 2003 / AZ 308 O 70/03
    */
      ?>
      <a style="text-decoration:none;" href="https://www.modified-shop.org" rel="nofollow noopener" target="_blank"><span style="color:#B0347E;">mod</span><span style="color:#6D6D6D;">ified eCommerce Shopsoftware</span></a><span style="color:#555555;">&nbsp;&copy;2009-<?php echo date("Y"); ?>&nbsp;provides no warranty and is redistributable under the <a style="color:#555555;text-decoration:none;" href="http://www.gnu.org/licenses/gpl-2.0.html" rel="nofollow noopener" target="_blank">GNU General Public License (Version 2)</a><br />eCommerce Engine 2006 based on <a style="text-decoration:none; color:#555555;" href="http://www.xt-commerce.com/" rel="nofollow noopener" target="_blank">xt:Commerce</a></span>
    </div>  
    <?php
      if (DISPLAY_PAGE_PARSE_TIME == 'all' || DISPLAY_PAGE_PARSE_TIME == 'admin') {
        $parse_time = number_format((microtime(true)-PAGE_PARSE_START_TIME), 3);
        echo '<div class="smallText" style="color:#ccc;">Parse Time: ' . $parse_time . 's</div>';
      }
    ?>
</div>
<?php
  require_once(DIR_FS_INC.'auto_include.inc.php');
  foreach(auto_include(DIR_FS_ADMIN.'includes/extra/footer/','php') as $file) require ($file);
?>