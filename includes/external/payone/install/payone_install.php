<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
 	 based on:
	  (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
	  (c) 2002-2003 osCommerce - www.oscommerce.com
	  (c) 2001-2003 TheMedia, Dipl.-Ing Thomas Plänkers - http://www.themedia.at & http://www.oscommerce.at
	  (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com
    (c) 2013 Gambio GmbH - http://www.gambio.de
  
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

$sql = array();
$sql[] = "CREATE TABLE IF NOT EXISTS `payone_config` (
  `path` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`path`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";

$sql[] = "CREATE TABLE IF NOT EXISTS `payone_transactions` (
  `payone_transactions_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `orders_id` int(10) unsigned NOT NULL,
  `status` varchar(255) NOT NULL,
  `txid` varchar(100) NOT NULL,
  `userid` varchar(100) NOT NULL,
  `created` datetime DEFAULT NULL,
  `last_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`payone_transactions_id`),
  KEY `orders_id` (`orders_id`,`txid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";

$sql[] = "CREATE TABLE IF NOT EXISTS `payone_txstatus` (
  `payone_txstatus_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `orders_id` int(11) NOT NULL,
  `received` datetime NOT NULL,
  PRIMARY KEY (`payone_txstatus_id`),
  KEY `orders_id` (`orders_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";

$sql[] = "CREATE TABLE IF NOT EXISTS `payone_txstatus_data` (
  `payone_txstatus_data_id` int(11) NOT NULL AUTO_INCREMENT,
  `payone_txstatus_id` int(11) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`payone_txstatus_data_id`),
  KEY `payone_txstatus_id` (`payone_txstatus_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";

$sql[] = "CREATE TABLE IF NOT EXISTS `payone_clearingdata` (
  `p1_clearingdata_id` int(11) NOT NULL AUTO_INCREMENT,
  `orders_id` int(10) unsigned NOT NULL,
  `bankaccountholder` varchar(255) NOT NULL,
  `bankcountry` varchar(2) NOT NULL,
  `bankaccount` varchar(32) NOT NULL,
  `bankcode` varchar(32) NOT NULL,
  `bankiban` varchar(32) NOT NULL,
  `bankbic` varchar(32) NOT NULL,
  `bankcity` varchar(64) NOT NULL,
  `bankname` varchar(128) NOT NULL,
  PRIMARY KEY (`p1_clearingdata_id`),
  KEY `orders_id` (`orders_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";

$sql[] = "CREATE TABLE IF NOT EXISTS `payone_ac_cache` (
  `address_hash` varchar(32) NOT NULL,
  `address_book_id` int(11) NOT NULL,
  `received` datetime NOT NULL,
  `secstatus` int(11) NOT NULL,
  `status` varchar(7) NOT NULL,
  `personstatus` varchar(4) NOT NULL,
  `street` varchar(255) NOT NULL,
  `streetname` varchar(255) NOT NULL,
  `streetnumber` varchar(255) NOT NULL,
  `zip` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `errorcode` varchar(255) NOT NULL,
  `errormessage` text NOT NULL,
  `customermessage` varchar(255) NOT NULL,
  PRIMARY KEY (`address_hash`),
  KEY `address_book_id` (`address_book_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";

$sql[] = "CREATE TABLE IF NOT EXISTS `payone_cr_cache` (
  `address_hash` varchar(32) NOT NULL,
  `address_book_id` int(11) NOT NULL,
  `scoretype` varchar(2) NOT NULL,
  `received` datetime NOT NULL,
  `secstatus` int(11) NOT NULL,
  `status` varchar(7) NOT NULL,
  `score` varchar(100) NOT NULL,
  `scorevalue` varchar(100) NOT NULL,
  `secscore` varchar(100) NOT NULL,
  `personstatus` varchar(4) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `street` varchar(255) NOT NULL,
  `streetname` varchar(255) NOT NULL,
  `streetnumber` varchar(255) NOT NULL,
  `zip` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `errorcode` varchar(255) NOT NULL,
  `errormessage` text NOT NULL,
  `customermessage` varchar(255) NOT NULL,
  PRIMARY KEY (`address_hash`),
  KEY `address_book_id` (`address_book_id`),
  KEY `scoretype` (`scoretype`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";

$sql[] = "CREATE TABLE IF NOT EXISTS `payone_api_log` (
  `p1_api_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` bigint(20) NOT NULL,
  `date_created` datetime NOT NULL,
  `log_count` int(11) NOT NULL,
  `log_level` int(11) NOT NULL,
  `message` mediumtext NOT NULL,
  `customers_id` int(11) NOT NULL,
  PRIMARY KEY (`p1_api_log_id`),
  KEY `event_id` (`event_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";

$sql[] = "CREATE TABLE IF NOT EXISTS `payone_transactions_log` (
  `p1_transactions_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` bigint(20) NOT NULL,
  `date_created` datetime NOT NULL,
  `log_count` int(11) NOT NULL,
  `log_level` int(11) NOT NULL,
  `message` mediumtext NOT NULL,
  `customers_id` int(11) NOT NULL,
  PRIMARY KEY (`p1_transactions_log_id`),
  KEY `event_id` (`event_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";

$sql[] = "CREATE TABLE IF NOT EXISTS `payone_bankgroups` (
  `p1_bankgroups_id` int(11) NOT NULL AUTO_INCREMENT,
  `identifier` varchar(32) NOT NULL,
  `bank_code` varchar(32) NOT NULL,
  `bank_name` varchar(64) NOT NULL,
  PRIMARY KEY (`p1_bankgroups_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";

$sql[] = "CREATE TABLE IF NOT EXISTS `payone_sepa_countries` (
  `p1_sepa_countries_id` int(11) NOT NULL AUTO_INCREMENT,
  `countries_name` varchar(64) NOT NULL,
  `countries_iso_code_2` varchar(2) NOT NULL,
  `countries_iban_code` varchar(2) NOT NULL,
  `countries_currency_code` varchar(4) NOT NULL,
  PRIMARY KEY (`p1_sepa_countries_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";


$bankgroups = array(
  'eps' => array(
    'ARZ_OVB' => 'Volksbanken',
    'ARZ_BAF' => 'Bank für Ärzte und Freie Berufe',
    'ARZ_NLH' => 'Niederösterreichische Landes-Hypo',
    'ARZ_VLH' => 'Vorarlberger Landes-Hypo',
    'ARZ_BCS' => 'Bankhaus Carl Spängler & Co. AG',
    'ARZ_HTB' => 'Hypo Tirol',
    'ARZ_HAA' => 'Hypo Alpe Adria',
    'ARZ_IKB' => 'Investkreditbank',
    'ARZ_OAB' => 'Österreichische Apothekerbank',
    'ARZ_IMB' => 'Immobank',
    'ARZ_GRB' => 'Gärtnerbank',
    'ARZ_HIB' => 'HYPO Investment',
    'BA_AUS' => 'Bank Austria',
    'BAWAG_BWG' => 'BAWAG',
    'BAWAG_PSK' => 'PSK Bank',
    'BAWAG_ESY' => 'easybank',
    'BAWAG_SPD' => 'Sparda Bank',
    'SPARDAT_EBS' => 'Erste Bank',
    'SPARDAT_BBL' => 'Bank Burgenland',
    'RAC_RAC' => 'Raiffeisen',
    'HRAC_OOS' => 'Hypo Oberösterreich',
    'HRAC_SLB' => 'Hypo Salzburg',
    'HRAC_STM' => 'Hypo Steiermark',
  ),
  'ideal' => array(
    'ABN_AMRO_BANK' => 'ABN Amro',
    'RABOBANK' => 'Rabobank',
    'FRIESLAND_BANK' => 'Friesland Bank',
    'ASN_BANK' => 'ASN Bank',
    'SNS_BANK' => 'SNS Bank',
    'TRIODOS_BANK' => 'Triodos',
    'SNS_REGIO_BANK' => 'SNS Regio Bank',
    'ING_BANK' => 'ING',
  ),
);


$sepa_countries = array(
  array('countries_name' => 'Austria', 'countries_iso_code_2' => 'AT', 'countries_iban_code' => 'AT', 'countries_currency_code' => 'EUR'),
  array('countries_name' => 'Belgium', 'countries_iso_code_2' => 'BE', 'countries_iban_code' => 'BE', 'countries_currency_code' => 'EUR'),
  array('countries_name' => 'Bulgaria', 'countries_iso_code_2' => 'BG', 'countries_iban_code' => 'BG', 'countries_currency_code' => 'BGN'),
  array('countries_name' => 'Saint Barthelemy', 'countries_iso_code_2' => 'BL', 'countries_iban_code' => 'FR', 'countries_currency_code' => 'EUR'),
  array('countries_name' => 'Switzerland', 'countries_iso_code_2' => 'CH', 'countries_iban_code' => 'CH', 'countries_currency_code' => 'CHF'),
  array('countries_name' => 'Cyprus', 'countries_iso_code_2' => 'CY', 'countries_iban_code' => 'CY', 'countries_currency_code' => 'EUR'),
  array('countries_name' => 'Czech Republic', 'countries_iso_code_2' => 'CZ', 'countries_iban_code' => 'CZ', 'countries_currency_code' => 'CZK'),
  array('countries_name' => 'Germany', 'countries_iso_code_2' => 'DE', 'countries_iban_code' => 'DE', 'countries_currency_code' => 'EUR'),
  array('countries_name' => 'Denmark', 'countries_iso_code_2' => 'DK', 'countries_iban_code' => 'DK', 'countries_currency_code' => 'DKK'),
  array('countries_name' => 'Estonia', 'countries_iso_code_2' => 'EE', 'countries_iban_code' => 'EE', 'countries_currency_code' => 'EUR'),
  array('countries_name' => 'Spain', 'countries_iso_code_2' => 'ES', 'countries_iban_code' => 'ES', 'countries_currency_code' => 'EUR'),
  array('countries_name' => 'Finland', 'countries_iso_code_2' => 'FI', 'countries_iban_code' => 'FI', 'countries_currency_code' => 'EUR'),
  array('countries_name' => 'France', 'countries_iso_code_2' => 'FR', 'countries_iban_code' => 'FR', 'countries_currency_code' => 'EUR'),
  array('countries_name' => 'United Kingdom', 'countries_iso_code_2' => 'GB', 'countries_iban_code' => 'GB', 'countries_currency_code' => 'GBP'),
  array('countries_name' => 'French Guiana', 'countries_iso_code_2' => 'GF', 'countries_iban_code' => 'FR', 'countries_currency_code' => 'EUR'),
  array('countries_name' => 'Gibraltar', 'countries_iso_code_2' => 'GI', 'countries_iban_code' => 'GI', 'countries_currency_code' => 'GIP'),
  array('countries_name' => 'Guadeloupe', 'countries_iso_code_2' => 'GP', 'countries_iban_code' => 'FR', 'countries_currency_code' => 'EUR'),
  array('countries_name' => 'Greece', 'countries_iso_code_2' => 'GR', 'countries_iban_code' => 'GR', 'countries_currency_code' => 'EUR'),
  array('countries_name' => 'Croatia2', 'countries_iso_code_2' => 'HR', 'countries_iban_code' => 'HR', 'countries_currency_code' => 'HRK'),
  array('countries_name' => 'Hungary', 'countries_iso_code_2' => 'HU', 'countries_iban_code' => 'HU', 'countries_currency_code' => 'HUF'),
  array('countries_name' => 'Ireland', 'countries_iso_code_2' => 'IE', 'countries_iban_code' => 'IE', 'countries_currency_code' => 'EUR'),
  array('countries_name' => 'Iceland', 'countries_iso_code_2' => 'IS', 'countries_iban_code' => 'IS', 'countries_currency_code' => 'ISK'),
  array('countries_name' => 'Italy', 'countries_iso_code_2' => 'IT', 'countries_iban_code' => 'IT', 'countries_currency_code' => 'EUR'),
  array('countries_name' => 'Liechtenstein', 'countries_iso_code_2' => 'LI', 'countries_iban_code' => 'LI', 'countries_currency_code' => 'CHF'),
  array('countries_name' => 'Lithuania', 'countries_iso_code_2' => 'LT', 'countries_iban_code' => 'LT', 'countries_currency_code' => 'LTL'),
  array('countries_name' => 'Luxembourg', 'countries_iso_code_2' => 'LU', 'countries_iban_code' => 'LU', 'countries_currency_code' => 'EUR'),
  array('countries_name' => 'Latvia', 'countries_iso_code_2' => 'LV', 'countries_iban_code' => 'LV', 'countries_currency_code' => 'LVL'),
  array('countries_name' => 'Monaco', 'countries_iso_code_2' => 'MC', 'countries_iban_code' => 'MC', 'countries_currency_code' => 'EUR'),
  array('countries_name' => 'Saint Martin (French part)', 'countries_iso_code_2' => 'MF', 'countries_iban_code' => 'FR', 'countries_currency_code' => 'EUR'),
  array('countries_name' => 'Martinique', 'countries_iso_code_2' => 'MQ', 'countries_iban_code' => 'FR', 'countries_currency_code' => 'EUR'),
  array('countries_name' => 'Malta', 'countries_iso_code_2' => 'MT', 'countries_iban_code' => 'MT', 'countries_currency_code' => 'EUR'),
  array('countries_name' => 'Netherlands', 'countries_iso_code_2' => 'NL', 'countries_iban_code' => 'NL', 'countries_currency_code' => 'EUR'),
  array('countries_name' => 'Norway', 'countries_iso_code_2' => 'NO', 'countries_iban_code' => 'NO', 'countries_currency_code' => 'NOK'),
  array('countries_name' => 'Poland', 'countries_iso_code_2' => 'PL', 'countries_iban_code' => 'PL', 'countries_currency_code' => 'PLN'),
  array('countries_name' => 'Saint Pierre and Miquelon', 'countries_iso_code_2' => 'PM', 'countries_iban_code' => 'FR', 'countries_currency_code' => 'EUR'),
  array('countries_name' => 'Portugal', 'countries_iso_code_2' => 'PT', 'countries_iban_code' => 'PT', 'countries_currency_code' => 'EUR'),
  array('countries_name' => 'Reunion', 'countries_iso_code_2' => 'RE', 'countries_iban_code' => 'FR', 'countries_currency_code' => 'EUR'),
  array('countries_name' => 'Romania', 'countries_iso_code_2' => 'RO', 'countries_iban_code' => 'RO', 'countries_currency_code' => 'RON'),
  array('countries_name' => 'Sweden', 'countries_iso_code_2' => 'SE', 'countries_iban_code' => 'SE', 'countries_currency_code' => 'SEK'),
  array('countries_name' => 'Slovenia', 'countries_iso_code_2' => 'SI', 'countries_iban_code' => 'SI', 'countries_currency_code' => 'EUR'),
  array('countries_name' => 'Slovakia', 'countries_iso_code_2' => 'SK', 'countries_iban_code' => 'SK', 'countries_currency_code' => 'EUR'),
  array('countries_name' => 'Mayotte', 'countries_iso_code_2' => 'YT', 'countries_iban_code' => 'FR', 'countries_currency_code' => 'EUR'),
);


$sql_configuration_array = array(
  array('configuration_key' => 'MODULE_PAYMENT_PAYONE_AB_TESTING',
        'configuration_value' => '0',
        'configuration_group_id' => '6',
        'date_added' => 'now()'),
);


// action                     
if (count($sql) > 0) {
  foreach($sql as $query) {
    xtc_db_query($query);
  }
}

if (count($bankgroups) > 0) {
  xtc_db_query("TRUNCATE TABLE `payone_bankgroups`");
  foreach ($bankgroups as $identifier => $group) {
    $sql_data_array = array('identifier' => $identifier);
    foreach ($group as $bank_code => $bank_name) {
      $sql_data_array['bank_code'] = $bank_code;
      $sql_data_array['bank_name'] = $bank_name;
      xtc_db_perform('payone_bankgroups', $sql_data_array);
    }
  }	
}

if (count($sepa_countries) > 0) {
  xtc_db_query("TRUNCATE TABLE `payone_sepa_countries`");
  foreach ($sepa_countries as $sql_data_array) {
    xtc_db_perform('payone_sepa_countries', $sql_data_array);
  }
}

if (count($sql_configuration_array) > 0) {
  foreach ($sql_configuration_array as $sql_data_array) {
    xtc_db_perform(TABLE_CONFIGURATION, $sql_data_array);
  }
}
?>