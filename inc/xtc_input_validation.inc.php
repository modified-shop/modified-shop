<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


   function xtc_input_validation($var,$type,$replace_char) {

      switch($type) {
                case 'cPath':
                        $replace_param='/[^0-9_]/';
                        break;
                case 'int':
                        $replace_param='/[^0-9]/';
                        break;
                case 'char':
                        $replace_param='/[^a-zA-Z]/';
                        break;

      }

    $val=preg_replace($replace_param,$replace_char,$var);

    return $val;
   }



?>