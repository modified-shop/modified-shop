<?php
/* --------------------------------------------------------------
   $Id: removeoldpics.php 3503 2012-08-23 11:24:07Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(manufacturers.php,v 1.14 2003/02/16); www.oscommerce.com
   (c) 2003 nextcommerce (manufacturers.php,v 1.4 2003/08/14); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------*/

define('HEADING_TITLE','Remove old product pictures');
define('LINK_INFO_TEXT', '<p>With this function you can delete dispensable product pictures from following directories:</p>
- /images/product_images/info_images<br/>
- /images/product_images/original_images<br/>
- /images/product_images/popup_images<br/>
- /images/product_images/thumbnail_images<br/>
<p>if there are no more references to them in the database.<br/>When a picture is no more needed by an article it can be removed savely from the webserver.</p><br/>');
define('LINK_ORIGINAL', 'Remove old original images');
define('LINK_INFO', 'Remove old info images');
define('LINK_THUMBNAIL', 'Remove old thumbnail images');
define('LINK_POPUP', 'Remove old popup images');
define ('LINK_MESSAGE', 'Superfluous item images from the directory "/images/product_images/%s_images" have been deleted.');
define ('LINK_MESSAGE_NO_DELETE', 'There are no superfluous items in the images directory was "/images/product_images/%s_images" found.');
?>