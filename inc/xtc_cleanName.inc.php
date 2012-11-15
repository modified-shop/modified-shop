<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   xtcModified - community made shopping
   http://www.xtc-modified.org

   Copyright (c) 2009 - 2012 xtcModified

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