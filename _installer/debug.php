<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   Released under the GNU General Public License
   --------------------------------------------------------------*/
   
define('DIR_MODIFIED_INSTALLER','_installer');
   
$strato = array();
   
// Determine Document Root
  function detectDocumentRoot() {
    $dir_fs_www_root = realpath(dirname(basename(__FILE__)) . "/..");
    if ($dir_fs_www_root == '') $dir_fs_www_root = '/';
    $dir_fs_www_root = str_replace(array('\\','//'), '/', $dir_fs_www_root);
    return $dir_fs_www_root;
  }

  //BOF - web28 - 2011-05-06 - NEW Strato document-root function
  function strato_document_root() {
    global $strato;
    // subdomain entfernen
    $domain = $_SERVER["HTTP_HOST"];
    $tmp = explode ('.',$domain);
    if (count($tmp) > 2) {
      $domain = str_replace($tmp[0].'.','',$domain);
      $strato['domain'] = $domain;
    }
    $document_root = str_replace($_SERVER["PHP_SELF"],'',$_SERVER["SCRIPT_FILENAME"]);
    $strato['document_root'] = $document_root;
    //Unterverzeichnis ermitteln
    $tmp = explode(DIR_MODIFIED_INSTALLER, $_SERVER["PHP_SELF"]);
    $subdir = $tmp[0];
    $strato['subdir'] = $subdir;
    //Prüfen ob Domain im Pfad enthalten ist, wenn nein Pfad Stratopfad erzeugen: /home/strato/www/ersten zwei_buchstaben/www.wunschname.de/htdocs/
    if(stristr($document_root, $domain) === FALSE) {
      //Erste 2 Buchstaben der Domain ermittlen
      $domain2 = substr($tmp[count($tmp)-2], 0, 2);
      //Korrektur Unterverzeichnis
      $htdocs = str_replace($_SERVER["SCRIPT_NAME"],'',$_SERVER["SCRIPT_FILENAME"]);
      $htdocs = '/htdocs' . str_replace($_SERVER["DOCUMENT_ROOT"],'',$htdocs);      
      //MUSTER: /home/strato/www/wu/www.wunschname.de/htdocs/
      $document_root = '/home/strato/www/'.$domain2. '/www.'.$domain.$htdocs.$subdir;
      $strato['document_root_fix1'] = $document_root;
    } else {
      $document_root .= $subdir;
      $strato['document_root_fix2'] = $document_root;
    }
    return $document_root;
  }
  //EOF - web28 - 2011-05-06 - NEW Strato document-root function



   
   
if (!defined('DIR_FS_DOCUMENT_ROOT')) {   
  if (strpos($_SERVER['DOCUMENT_ROOT'],'strato') !== false) {
    define('DIR_FS_DOCUMENT_ROOT', rtrim(strato_document_root(),'/') . '/');
  } else {
    define('DIR_FS_DOCUMENT_ROOT', rtrim(detectDocumentRoot(),'/') .'/');
  }    
  define('DIR_FS_CATALOG', DIR_FS_DOCUMENT_ROOT);
}

$support  = 'URL: ' . $_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']. '<br />';
$support .= '$_SERVER[PHP_SELF]: ' . $_SERVER['PHP_SELF']. '<br />';
$support .= '$_SERVER[DOCUMENT_ROOT]: ' . $_SERVER['DOCUMENT_ROOT']. '<br />';
$support .= '$_SERVER[SCRIPT_NAME]: ' . $_SERVER['SCRIPT_NAME']. '<br />';
$support .= '$_SERVER[SCRIPT_FILENAME]: ' . $_SERVER['SCRIPT_FILENAME']. '<br />';
$support .= 'DIR_FS_DOCUMENT_ROOT: ' . DIR_FS_DOCUMENT_ROOT. '<br />';

if(count($strato) > 0) {
  $support .= '<br />'. 'STRATO: ' . '<br />';
  foreach($strato as $key => $entry) {
    $support .= $key . ': ' . $entry . '<br />';
  }
}

echo $support;
?>