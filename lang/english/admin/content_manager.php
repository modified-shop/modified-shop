<?php
/* --------------------------------------------------------------
   $Id: content_manager.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on:
   (c) 2003	 nextcommerce (content_manager.php,v 1.8 2003/08/25); www.nextcommerce.org
   
   Released under the GNU General Public License 
   --------------------------------------------------------------*/
   
 define('HEADING_TITLE','Content Manager');
 define('HEADING_CONTENT','Site content');
 define('HEADING_PRODUCTS_CONTENT','Products content');
 define('TABLE_HEADING_CONTENT_ID','LinkI D');
 define('TABLE_HEADING_CONTENT_TITLE','Title');
 define('TABLE_HEADING_CONTENT_FILE','File');
 define('TABLE_HEADING_CONTENT_STATUS','Visible in box');
 define('TABLE_HEADING_CONTENT_BOX','Box');
 define('TABLE_HEADING_PRODUCTS_ID','ID');
 define('TABLE_HEADING_PRODUCTS','Product');
 define('TABLE_HEADING_PRODUCTS_CONTENT_ID','ID');
 define('TABLE_HEADING_LANGUAGE','Language');
 define('TABLE_HEADING_CONTENT_NAME','Name/Filename');
 define('TABLE_HEADING_CONTENT_LINK','Link');
 define('TABLE_HEADING_CONTENT_HITS','Viewed');
 define('TABLE_HEADING_CONTENT_GROUP','Group');
 define('TABLE_HEADING_CONTENT_SORT','Sort Order');
 define('TEXT_YES','Yes');
 define('TEXT_NO','No');
 define('TABLE_HEADING_CONTENT_ACTION','Action');
 define('TEXT_DELETE','Delete');
 define('TEXT_EDIT','Edit');
 define('TEXT_PREVIEW','Preview');
 define('CONFIRM_DELETE','Delete Content ?');
 define('CONTENT_NOTE','Content marked with <font color="#ff0000">*</font> is a part of the system and cannot be deleted!');


 // edit
 define('TEXT_LANGUAGE','Language:');
 define('TEXT_STATUS','Visible:');
 define('TEXT_STATUS_DESCRIPTION','If checked, a link will be displayed in the information box');
 define('TEXT_TITLE','Title:');
 define('TEXT_TITLE_FILE','Title/Filename:');
 define('TEXT_SELECT','-Select-');
 define('TEXT_HEADING','Heading:');
 define('TEXT_CONTENT','Text:');
 define('TEXT_UPLOAD_FILE','Upload File:');
 define('TEXT_UPLOAD_FILE_LOCAL','(from local system)');
 define('TEXT_CHOOSE_FILE','Choose File:');
 define('TEXT_CHOOSE_FILE_DESC','You also can choose an existing file from the list.');
 define('TEXT_NO_FILE','Delete Selection');
 define('TEXT_CHOOSE_FILE_SERVER','(If you uploaded your files already via FTP to <i>(media/content)</i>, you can select the file here.');
 define('TEXT_CURRENT_FILE','Current File:');
 define('TEXT_FILE_DESCRIPTION','<b>Info:</b><br />You also got the opportunity to upload a <b>.html</b> or <b>.htm</b> file and display it as shop content.<br /> If you select or upload a file, the text in the box will be ignored.<br /><br />');
 define('ERROR_FILE','Wrong file format (only .html or .htm)');
 define('ERROR_TITLE','Please enter a title');
 define('ERROR_COMMENT','Please enter a file description!');
 define('TEXT_FILE_FLAG','Box:');
 define('TEXT_PARENT','Main Document:');
 define('TEXT_PARENT_DESCRIPTION','Assign to this document');
 define('TEXT_PRODUCT','Product:');
 define('TEXT_LINK','Link:');
 define('TEXT_SORT_ORDER','Sort:'); 
 define('TEXT_GROUP','Language Group:');
 define('TEXT_GROUP_DESC','With this ID you link togehther similar subjects from different languages.');

 define('TEXT_CONTENT_DESCRIPTION','With this Content Manager you can add any filetype to a product, like technical sheets, productdetails, videos. These elements will be displayed on the products detailpage.<br /><br />');
 define('TEXT_FILENAME','Used File:');
 define('TEXT_FILE_DESC','Description:');
 define('USED_SPACE','Used Space:');
 define('TABLE_HEADING_CONTENT_FILESIZE','Filesize');


 ?>