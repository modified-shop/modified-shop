<?php
/**
 * 888888ba                 dP  .88888.                    dP                
 * 88    `8b                88 d8'   `88                   88                
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b. 
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88 
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88 
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P' 
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * $Id: 010.sql.php 650 2011-01-08 22:30:52Z MaW $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

# magnalister Variations und images

$queries = array();
$functions = array();

function create_magnalister_variations() {
	MagnaDB::gi()->query("create table if not exists magnalister_variations (
 variation_id int(11) NOT NULL auto_increment,
 products_id int(11) NOT NULL,
 variation_products_model varchar(64) default NULL,
 variation_ean varchar(128) default NULL,
 variation_attributes varchar(63) NOT NULL,
 variation_attributes_text text,
 variation_quantity decimal(15,4) NOT NULL default '0.0000',
 variation_status enum('0','1') default '1',
 variation_price decimal(15,4) NOT NULL default '0.0000',
 variation_weight decimal(15,4) NOT NULL default '0.0000',
 variation_shipping_time int(2) NOT NULL default 1,
 variation_volume decimal(15,4) NOT NULL default '0.0000',
 variation_unit_of_measure varchar(15) NOT NULL default '',
 PRIMARY KEY  (`variation_id`),
 UNIQUE KEY `products_id_variation_attributes` (`products_id`,`variation_attributes`),
 KEY `variation_attributes` (`variation_attributes`),
 KEY `products_id` (`products_id`)
)");
	return;
}

$functions[] = 'create_magnalister_variations';

function create_magnalister_images() {
	MagnaDB::gi()->query("create table if not exists magnalister_images (  
 image_id int(11) NOT NULL auto_increment,
 products_id int(11) NOT NULL,
 variation_id int(11) default NULL,
 image_nr smallint(6)  NOT NULL default 0,
 image_name varchar(254) NOT NULL default '',
 PRIMARY KEY  (`image_id`),
 KEY magnalister_images_products_id (`products_id`)
)");
	return;
}

$functions[] = 'create_magnalister_images';
