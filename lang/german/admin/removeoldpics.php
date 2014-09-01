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

define('HEADING_TITLE', 'Alte Artikelbilder l&ouml;schen');
define('LINK_INFO_TEXT', '<p>&Uuml;ber diese Funktion k&ouml;nnen &uuml;berfl&uuml;ssige Artikelbilder in den Verzeichnissen:</p>
- /images/product_images/info_images<br/>
- /images/product_images/original_images<br/>
- /images/product_images/popup_images<br/>
- /images/product_images/thumbnail_images<br/>
<p>vom Webserver gel&ouml;scht werden, wenn in der Datenbank kein Bezug mehr zu diesen Artikelbildern vorhanden ist.<br/>Wenn ein Bild von keinem Produkt mehr genutzt wird, so kann das Bild vom Webserver gefahrlos gel&ouml;scht werden.</p><br/>');
define('LINK_ORIGINAL', 'Alte Originalbilder l&ouml;schen');
define('LINK_INFO', 'Alte Infobilder l&ouml;schen');
define('LINK_THUMBNAIL', 'Alte Thumbnailbilder l&ouml;schen');
define('LINK_POPUP', 'Alte Popupbilder l&ouml;schen');
define('LINK_MESSAGE', '&Uuml;berfl&uuml;ssige Artikelbilder aus dem Verzeichnis "/images/product_images/%s_images" wurden gel&ouml;scht.');
define('LINK_MESSAGE_NO_DELETE', 'Es wurden keine &uuml;berfl&uuml;ssigen Artikelbilder im Verzeichnis "/images/product_images/%s_images" gefunden.');
?>