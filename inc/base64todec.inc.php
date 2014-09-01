<?php
/*------------------------------------------------------------------------------
  $Id: base64todec.inc.php 899 2005-04-29 02:40:57Z hhgag $

  XTC-CC - Contribution for XT-Commerce http://www.xt-commerce.com
  modified by http://www.netz-designer.de

  Copyright (c) 2003 netz-designer
  -----------------------------------------------------------------------------
  based on:
  credit card encryption functions for the catalog module

  BMC 2003 for the CC CVV Module

  (c) Mainframes 2003 http://www.mainframes.co.uk
------------------------------------------------------------------------------*/

function base64todec($base64_value){

   // convert base64 value into decimal value

   switch($base64_value){
	case "A":$decimal_value=0;break;
	case "B":$decimal_value=1;break;
	case "C":$decimal_value=2;break;
	case "D":$decimal_value=3;break;
	case "E":$decimal_value=4;break;
	case "F":$decimal_value=5;break;
	case "G":$decimal_value=6;break;
	case "H":$decimal_value=7;break;
	case "I":$decimal_value=8;break;
	case "J":$decimal_value=9;break;
	case "K":$decimal_value=10;break;
	case "L":$decimal_value=11;break;
	case "M":$decimal_value=12;break;
	case "N":$decimal_value=13;break;
	case "O":$decimal_value=14;break;
	case "P":$decimal_value=15;break;
	case "Q":$decimal_value=16;break;
	case "R":$decimal_value=17;break;
	case "S":$decimal_value=18;break;
	case "T":$decimal_value=19;break;
	case "U":$decimal_value=20;break;
	case "V":$decimal_value=21;break;
	case "W":$decimal_value=22;break;
	case "X":$decimal_value=23;break;
	case "Y":$decimal_value=24;break;
	case "Z":$decimal_value=25;break;
	case "a":$decimal_value=26;break;
	case "b":$decimal_value=27;break;
	case "c":$decimal_value=28;break;
	case "d":$decimal_value=29;break;
	case "e":$decimal_value=30;break;
	case "f":$decimal_value=31;break;
	case "g":$decimal_value=32;break;
	case "h":$decimal_value=33;break;
	case "i":$decimal_value=34;break;
	case "j":$decimal_value=35;break;
	case "k":$decimal_value=36;break;
	case "l":$decimal_value=37;break;
	case "m":$decimal_value=38;break;
	case "n":$decimal_value=39;break;
	case "o":$decimal_value=40;break;
	case "p":$decimal_value=41;break;
	case "q":$decimal_value=42;break;
	case "r":$decimal_value=43;break;
	case "s":$decimal_value=44;break;
	case "t":$decimal_value=45;break;
	case "u":$decimal_value=46;break;
	case "v":$decimal_value=47;break;
	case "w":$decimal_value=48;break;
	case "x":$decimal_value=49;break;
	case "y":$decimal_value=50;break;
	case "z":$decimal_value=51;break;
	case "0":$decimal_value=52;break;
	case "1":$decimal_value=53;break;
	case "2":$decimal_value=54;break;
	case "3":$decimal_value=55;break;
	case "4":$decimal_value=56;break;
	case "5":$decimal_value=57;break;
	case "6":$decimal_value=58;break;
	case "7":$decimal_value=59;break;
	case "8":$decimal_value=60;break;
	case "9":$decimal_value=61;break;
	case "+":$decimal_value=62;break;
	case "/":$decimal_value=63;break;
	case "=":$decimal_value=64;break;
	default: $decimal_value=0;break;
   }

   return $decimal_value;
}
?>
