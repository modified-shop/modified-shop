<?php
/* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2014 [www.modified-shop.org]

   Released under the GNU General Public License
   --------------------------------------------------------------*/

/* NEUE FUNKTION  by web28 - www.rpa-com.de
IN DIESER DATEI MUSS NICHTS GEÄNDERT WERDEN!!!!

Hier koennen neue Zusatzfelder definiert werden, in dieses Verzeichnis einfach ein neue PHP Datei einfügen:

admin/includes/extra/modules/add_db_fields/

Inhalt der PHP Datei (Beispiel):
Bezeichnung genauso wie das neue Tabellenfeld als Array Wert

Beispiel neues Feld in Tabelle products:
$add_products_fields[] = 'products_neues_feld'; 

Beispiel neues Feld in Tabelle products_description:
$add_products_description_fields[] = 'products_desc_neues_feld'; 

Beispiel neues Feld in Tabelle categories:
$add_categories_fields[] = 'categories_neues_feld'; 

Beispiel neues Feld in Tabelle categories_description:
$add_categories_description_fields[] = 'categories_desc_neues_feld'; 

*/

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

//init arrays
$add_products_fields = $add_products_description_fields = $add_categories_fields = $add_categories_description_fields = array();

//ADD_PRODUCTS_FIELDS
$add_products_fields[] = 'products_manufacturers_model'; 

//ADD_PRODUCTS_DESCRIPTION_FIELDS
$add_products_description_fields[] = 'products_order_description';

//ADD_CATEGORIES_FIELDS


//ADD_CATEGORIES_DESCRIPTION_FIELDS


//CUSTOM ADDS
//autoload new product addons 
require_once(DIR_FS_INC.'auto_include.inc.php');
foreach(auto_include(DIR_FS_ADMIN.'includes/extra/modules/add_db_fields/','php') as $file) require ($file);

define('ADD_PRODUCTS_FIELDS', implode(',',$add_products_fields) );
define('ADD_PRODUCTS_DESCRIPTION_FIELDS', implode(',',$add_products_description_fields) ); 

define('ADD_CATEGORIES_FIELDS', implode(',',$add_categories_fields) );
define('ADD_CATEGORIES_DESCRIPTION_FIELDS', implode(',',$add_categories_description_fields) );
