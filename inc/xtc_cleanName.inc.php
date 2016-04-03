<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_cleanName.inc.php 1319 2005-10-23 10:35:15Z mz $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


  function xtc_cleanName($name) {
    $search_array=array('ä','Ä','ö','Ö','ü','Ü','ß','&auml;','&Auml;','&ouml;','&Ouml;','&uuml;','&Uuml;','&szlig;');
    $replace_array=array('ae','Ae','oe','Oe','ue','Ue','ss','ae','Ae','oe','Oe','ue','Ue','ss');
    $name=str_replace($search_array,$replace_array,$name);   	
  
    $replace_param='/[^a-zA-Z0-9]/';
    $name=preg_replace($replace_param,'-',$name);
    
    return $name;
  }
?>