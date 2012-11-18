<?php
/* -----------------------------------------------------------------------------------------
   $Id$
 -----------------------------------------------------------------------------------------*/
// -----------------------------------------------------------------------------------------
//  clear_language_param.inc.php
// -----------------------------------------------------------------------------------------
//  language parameter der Sprachumschaltung aus html Link entfernen und weiterleiten
// -----------------------------------------------------------------------------------------
//  web28 / http://www.rpa-com.de
//  August 2010
//   
//  Nähere Infos: http://www.rpa-com.de
//  
//  v 0.10
// -----------------------------------------------------------------------------------------

  function clear_language_param() {
    global $request_type;    
    if (strpos($_SERVER['REQUEST_URI'],'.html?language=') !== false) {
      // Bei angefragter Adresse NUR language parameter entfernen, Session-ID bleibt erhalten      
      $RedirectionLink = str_replace('.html?language='.$_GET['language'], '.html?',$_SERVER['REQUEST_URI']);
      $RedirectionLink = str_replace('.html?&', '.html?',$RedirectionLink);
      $RedirectionLink = rtrim($RedirectionLink,'?');
      
      // 301er-Weiterleitung mit Unterscheidung SSL / kein SSL      
      if ( (ENABLE_SSL == true) && ($request_type == 'SSL') ) { // We are loading an SSL page
        if (substr($RedirectionLink, 0, strlen(HTTP_SERVER)) == HTTP_SERVER) { 
          $RedirectionLink = HTTPS_SERVER . substr($RedirectionLink, strlen(HTTP_SERVER)); 
        }
      }
      header('HTTP/1.1 301 Moved Permanently' );    
      header('Location: '.preg_replace("/[\r\n]+(.*)$/i","",$RedirectionLink));      
    }  
  }
  // -----------------------------------------------------------------------------------------
  clear_language_param(); //language parameter entfernen  
  // -----------------------------------------------------------------------------------------
?>