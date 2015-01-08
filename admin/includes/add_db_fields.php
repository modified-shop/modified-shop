<?php
/* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2014 [www.modified-shop.org]

   Released under the GNU General Public License
   --------------------------------------------------------------*/

/* NEUE FUNKTION  by web28 - www.rpa-com.de
Hier koennen neue Zusatzfelder definiert werden
Bezeichnung genauso wie das neue Tabellenfeld, kommagetrennt ohne Leerzeichen
Die neuen Felder werden automatisch gespeichert bzw. mitkopiert
Beispiel neueTabellenfelder in der Tabelle products: products_manufacturer_model, products_shipping_class
define('ADD_PRODUCTS_FIELDS','products_manufacturer_model,products_shipping_class');
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
