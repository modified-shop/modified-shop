<?php
/*
 * FCKeditor - The text editor for Internet - http://www.fckeditor.net
 * Copyright (C) 2003-2010 Frederico Caldeira Knabben
 * Modified for xt:Commerce v3.0.4 SP2.1 by Hetfield - Copyright (c) 2009 by MerZ IT-SerVice http://www.merz-it-service.de
 *
 * == BEGIN LICENSE ==
 *
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 *
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 *
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * == END LICENSE ==
 *
 * Configuration file for the File Manager Connector for PHP.
 */

global $Config ;

// Modified for xt:Commerce v3.0.4 SP2.1 by Hetfield (www.merz-it-service.de) - Begin //
if (file_exists('../../../../../../local/configure.php')) {
    include('../../../../../../local/configure.php');
} else {
    require('../../../../../../configure.php');
}
//BOC web28 security fix
$Config['Enabled'] = false ;
include('xtc_access.php');
//BOC web28 security fix
$Config['UserFilesPath'] = DIR_WS_CATALOG ;
$Config['UserFilesAbsolutePath'] = DIR_FS_DOCUMENT_ROOT ;
// Modified for xt:Commerce v3.0.4 SP2.1 by Hetfield (www.merz-it-service.de) - End //
$Config['ForceSingleExtension'] = true ;
$Config['SecureImageUploads'] = true;
$Config['ConfigAllowedCommands'] = array('QuickUpload', 'FileUpload', 'GetFolders', 'GetFoldersAndFiles', 'CreateFolder') ;
$Config['ConfigAllowedTypes'] = array('File', 'Image', 'Flash', 'Media') ;
$Config['HtmlExtensions'] = array("html", "htm", "xml", "xsd", "txt", "js") ;
$Config['ChmodOnUpload'] = 0777 ;
$Config['ChmodOnFolderCreate'] = 0777 ;

// Modified for xt:Commerce v3.0.4 SP2.1 by Hetfield (www.merz-it-service.de) - Begin //
$Config['AllowedExtensions']['File']	= array('7z', 'aiff', 'asf', 'avi', 'bmp', 'csv', 'doc', 'fla', 'flv', 'gif', 'gz', 'gzip', 'jpeg', 'jpg', 'mid', 'mov', 'mp3', 'mp4', 'mpc', 'mpeg', 'mpg', 'ods', 'odt', 'pdf', 'png', 'ppt', 'pxd', 'qt', 'ram', 'rar', 'rm', 'rmi', 'rmvb', 'rtf', 'sdc', 'sitd', 'swf', 'sxc', 'sxw', 'tar', 'tgz', 'tif', 'tiff', 'txt', 'vsd', 'wav', 'wma', 'wmv', 'xls', 'xml', 'zip') ;
$Config['DeniedExtensions']['File']		= array() ;
$Config['FileTypesPath']['File']		= $Config['UserFilesPath'] ;
$Config['FileTypesAbsolutePath']['File']= ($Config['UserFilesAbsolutePath'] == '') ? '' : $Config['UserFilesAbsolutePath'] ;
$Config['QuickUploadPath']['File']		= $Config['UserFilesPath'] ;
$Config['QuickUploadAbsolutePath']['File']= $Config['UserFilesAbsolutePath'] ;

$Config['AllowedExtensions']['Image']	= array('bmp','gif','jpeg','jpg','png') ;
$Config['DeniedExtensions']['Image']	= array() ;
$Config['FileTypesPath']['Image']		= $Config['UserFilesPath'] . 'images/' ;
$Config['FileTypesAbsolutePath']['Image']= ($Config['UserFilesAbsolutePath'] == '') ? '' : $Config['UserFilesAbsolutePath'] . 'images/' ;
$Config['QuickUploadPath']['Image']		= $Config['UserFilesPath'] . 'images/' ;
$Config['QuickUploadAbsolutePath']['Image']= $Config['UserFilesAbsolutePath'] . 'images/' ;

$Config['AllowedExtensions']['Flash']	= array('swf','flv') ;
$Config['DeniedExtensions']['Flash']	= array() ;
$Config['FileTypesPath']['Flash']		= $Config['UserFilesPath'] . 'media/' ;
$Config['FileTypesAbsolutePath']['Flash']= ($Config['UserFilesAbsolutePath'] == '') ? '' : $Config['UserFilesAbsolutePath'].'media/' ;
$Config['QuickUploadPath']['Flash']		= $Config['UserFilesPath'] . 'media/' ;
$Config['QuickUploadAbsolutePath']['Flash']= $Config['UserFilesAbsolutePath'] . 'media/' ;

$Config['AllowedExtensions']['Media']	= array('aiff', 'asf', 'avi', 'bmp', 'fla', 'flv', 'gif', 'jpeg', 'jpg', 'mid', 'mov', 'mp3', 'mp4', 'mpc', 'mpeg', 'mpg', 'png', 'qt', 'ram', 'rm', 'rmi', 'rmvb', 'swf', 'tif', 'tiff', 'wav', 'wma', 'wmv') ;
$Config['DeniedExtensions']['Media']	= array() ;
$Config['FileTypesPath']['Media']		= $Config['UserFilesPath'] . 'media/' ;
$Config['FileTypesAbsolutePath']['Media']= ($Config['UserFilesAbsolutePath'] == '') ? '' : $Config['UserFilesAbsolutePath'].'media/' ;
$Config['QuickUploadPath']['Media']		= $Config['UserFilesPath'] . 'media/' ;
$Config['QuickUploadAbsolutePath']['Media']= $Config['UserFilesAbsolutePath'] . 'media/' ;
// Modified for xt:Commerce v3.0.4 SP2.1 by Hetfield (www.merz-it-service.de) - End //
?>
