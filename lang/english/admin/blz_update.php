<?php
/* --------------------------------------------------------------
   $Id: blz_update.php 3499 2012-08-23 09:12:40Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------

   Released under the GNU General Public License
   --------------------------------------------------------------*/

define('HEADING_TITLE', 'Update bank code numbers from German Bundesbank');
define('BLZ_INFO_TEXT', '<p>This form updates the database table for bank code numbers of the modified eCommerce Shopsoftware. The bank code number table is used during the order process for cross-checking the bank transfer details.<br/>The German Bundesbank provides new files all 3 months.</p><p><strong>Update notices:</strong></p><p>Please open the <a href="http://www.bundesbank.de/Redaktion/DE/Standardartikel/Kerngeschaeftsfelder/Unbarer_Zahlungsverkehr/bankleitzahlen_download.html" target="_blank"><strong>bank code number download page of the German Bundesbank</strong></a> in a separate browser tab. Further down on the German Bundesbank website after the headline "Bankleitzahlendateien ungepackt" exists a download link for the latest revision of the bank code number file in text format (TXT). Copy this link (right mouse click on the link, the copy the link) and and enter the copied link in this input field.</p><p>The button "Update" starts the update process.<br/>The update will take a few seconds.</p><p><i>Example link over the period of 03.09.2012 to 02.12.2012:</i></p>');
define('BLZ_LINK_NOT_GIVEN_TEXT', '<span class="messageStackError">No weblink to the bank code number file of the German Bundesbank was provided!</span><br /><br />');
define('BLZ_LINK_INVALID_TEXT', '<span class="messageStackError">Invalid weblink to the bank code number file.<br/><br/>Only TXT-files from the webpage of the German Bundesbank (www.bundesbank.de) are allowed!</span><br /><br />');
define('BLZ_DOWNLOADED_COUNT_TEXT', 'Number of uniquely recognized bank code numbers from<br/>');
define('BLZ_PHP_FILE_ERROR_TEXT', '<p><strong><span class="messageStackError">The PHP parameter "allow_url_fopen" is disabled ("off"). It is necessary for the PHP function <i>file( )</i>. To automatize the update process you have to enable the parameter (set to "on").</span></strong></p>');
define('BLZ_UPDATE_SUCCESS_TEXT', ' datasets written to the database successfully!');
define('BLZ_UPDATE_ERROR_TEXT', 'An error has occured!');
define('BLZ_LINK_ERROR_TEXT', '<span class="messageStackError">The provided download link does not exist! Please check your entries in the input field on the previous page.</span>');
define('BLZ_LINES_PROCESSED_TEXT',' bank code number datasets processed.');
define('BLZ_SOURCE_TEXT','Source: ');
?>