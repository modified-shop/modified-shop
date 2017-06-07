# -----------------------------------------------------------------------------------------
#  $Id: update_1.0.5.0_to_1.0.6.0.sql 3813 2012-10-29 11:54:40Z Tomcraft1980 $
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#Tomcraft - 2010-07-19 - changed database_version
UPDATE database_version SET version = 'MOD_1.0.6.0';

#DokuMan - 2010-08-05 - mark out of stock products red by default
UPDATE configuration SET configuration_value = '<span style="color:red">***</span>', last_modified = NOW() WHERE configuration_key = 'STOCK_MARK_PRODUCT_OUT_OF_STOCK';

#DokuMan - 2011-03-28 - Added address_format for Taiwan, Ireland, China and Great Britain
# 1 - Default, 2 - USA, 3 - Spain, 4 - Singapore, 5 - Germany , 6 - Ireland/Taiwan, 7 - China, 8 - UK/GB
INSERT INTO address_format (address_format_id, address_format, address_summary) VALUES (6, '$firstname $lastname$cr$streets$cr$city $state $postcode$cr$country','$country / $city');
INSERT INTO address_format (address_format_id, address_format, address_summary) VALUES (7, '$firstname $lastname$cr$streets, $city$cr$postcode $state$cr$country','$country / $city');
INSERT INTO address_format (address_format_id, address_format, address_summary) VALUES (8, '$firstname $lastname$cr$streets$cr$city$cr$state$cr$postcode$cr$country','$postcode / $country');

UPDATE countries SET address_format_id = 6 WHERE countries_id = 206;
UPDATE countries SET address_format_id = 6 WHERE countries_id = 103;
UPDATE countries SET address_format_id = 7 WHERE countries_id = 44;
UPDATE countries SET address_format_id = 8 WHERE countries_id = 222;

#DokuMan - 2010-09-21 - listing_template needs a default value
ALTER TABLE categories MODIFY listing_template varchar(64) NOT NULL DEFAULT '';
#DokuMan - 2010-10-13 - enlarge field 'manufacturers_name' from 32 characters to 64
ALTER TABLE manufacturers MODIFY manufacturers_name varchar(64) NOT NULL;
#DokuMan - 2010-10-13 - enlarge field 'comments' from varchar(255) to text
ALTER TABLE orders MODIFY comments text;

#DokuMan - 2010-10-13 - add index idx_categories_id
ALTER TABLE products_to_categories
  ADD INDEX idx_categories_id (categories_id);

#DokuMan - 2010-10-14 - keep index naming convention (idx_)
ALTER TABLE orders_products
  DROP INDEX orders_id,
  DROP INDEX products_id,
  ADD INDEX idx_orders_id (orders_id),
  ADD INDEX idx_products_id (products_id);

ALTER TABLE products_attributes
  DROP INDEX products_id,
  DROP INDEX options,
  ADD INDEX idx_products_id (products_id),
  ADD INDEX idx_options (options_id, options_values_id);

#DokuMan - 2010-11-08 - remove unsupported payment module qenta
DROP TABLE IF EXISTS payment_qenta;

#Web28 - 2010-11-13 - add missing listproducts to admin_access
ALTER TABLE admin_access
  ADD listproducts INT(1) NOT NULL DEFAULT 0 AFTER coupon_admin;
UPDATE admin_access SET listproducts = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET listproducts = 3 WHERE customers_id = 'groups' LIMIT 1;

#DokuMan - 2011-02-02 - added support for passwort+salt (SHA1)
#ALTER TABLE customers MODIFY customers_password varchar(50) NOT NULL; # Not needed any more with the new /inc/xtc_validate_password.inc.php

#DokuMan - 2011-02-03 - enlarge field for company names, firstname, lastname, street_address and city to 64 characters (instead of 32)
ALTER TABLE address_book MODIFY entry_company VARCHAR(64);
ALTER TABLE address_book MODIFY entry_firstname VARCHAR(64);
ALTER TABLE address_book MODIFY entry_lastname VARCHAR(64);
ALTER TABLE address_book MODIFY entry_street_address VARCHAR(64);
ALTER TABLE address_book MODIFY entry_city VARCHAR(64);
ALTER TABLE customers MODIFY customers_lastname VARCHAR(64);
ALTER TABLE orders MODIFY customers_company VARCHAR(64);
ALTER TABLE orders MODIFY customers_firstname VARCHAR(64);
ALTER TABLE orders MODIFY customers_lastname VARCHAR(64);
ALTER TABLE orders MODIFY customers_street_address VARCHAR(64);
ALTER TABLE orders MODIFY customers_city VARCHAR(64);
ALTER TABLE orders MODIFY delivery_company VARCHAR(64);
ALTER TABLE orders MODIFY delivery_firstname VARCHAR(64);
ALTER TABLE orders MODIFY delivery_lastname VARCHAR(64);
ALTER TABLE orders MODIFY delivery_street_address VARCHAR(64);
ALTER TABLE orders MODIFY delivery_city VARCHAR(64);
ALTER TABLE orders MODIFY billing_company VARCHAR(64);
ALTER TABLE orders MODIFY billing_firstname VARCHAR(64);
ALTER TABLE orders MODIFY billing_lastname VARCHAR(64);
ALTER TABLE orders MODIFY billing_street_address VARCHAR(64);
ALTER TABLE orders MODIFY billing_city VARCHAR(64);
ALTER TABLE newsletter_recipients MODIFY customers_firstname VARCHAR(64);
ALTER TABLE newsletter_recipients MODIFY customers_lastname VARCHAR(64);

#Web28 - 2011-03-25 - Fix address_format_id Switzerland
UPDATE countries SET address_format_id = 5 WHERE countries_id = 204 LIMIT 1;

#DokuMan - 2011-03-30 - preset text for billing email subject from admin backend (if not already set)
UPDATE configuration SET configuration_value = 'Ihre Bestellung bei uns', last_modified = NOW() WHERE configuration_key = 'EMAIL_BILLING_SUBJECT' AND last_modified IS NULL;

#Tomcraft - 2011-04-17 - Revised Pakistan zones, thx to Ronny aka Webkiste
DELETE FROM zones WHERE zone_country_id = 99;
#India
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'DL','Delhi');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'MH','Maharashtra');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'TN','Tamil Nadu');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'KL','Kerala');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'AP','Andhra Pradesh');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'KA','Karnataka');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'GA','Goa');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'MP','Madhya Pradesh');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'PY','Pondicherry');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'GJ','Gujarat');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'OR','Orrisa');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'CA','Chhatisgarh');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'JH','Jharkhand');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'BR','Bihar');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'WB','West Bengal');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'UP','Uttar Pradesh');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'RJ','Rajasthan');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'PB','Punjab');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'HR','Haryana');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'CH','Chandigarh');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'JK','Jammu & Kashmir');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'HP','Himachal Pradesh');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'UA','Uttaranchal');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'LK','Lakshadweep');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'AN','Andaman & Nicobar');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'MG','Meghalaya');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'AS','Assam');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'DR','Dadra & Nagar Haveli');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'DN','Daman & Diu');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'SK','Sikkim');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'TR','Tripura');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'MZ','Mizoram');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'MN','Manipur');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'NL','Nagaland');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (99,'AR','Arunachal Pradesh');
#Pakistan
DELETE FROM zones WHERE zone_country_id = 162;
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (162,'KHI','Karachi');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (162,'LH','Lahore');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (162,'ISB','Islamabad');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (162,'QUE','Quetta');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (162,'PSH','Peshawar');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (162,'GUJ','Gujrat');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (162,'SAH','Sahiwal');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (162,'FSB','Faisalabad');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (162,'RIP','Rawal Pindi');

#Tomcraft - 2011-04-18 - Revised Turkey zones, thx to Ronny aka Webkiste
DELETE FROM zones WHERE zone_country_id = 215;
#Turkey
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'AA', 'Adana');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'AD', 'Adiyaman');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'AF', 'Afyonkarahisar');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'AG', 'Agri');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'AK', 'Aksaray');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'AM', 'Amasya');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'AN', 'Ankara');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'AL', 'Antalya');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'AR', 'Ardahan');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'AV', 'Artvin');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'AY', 'Aydin');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'BK', 'Balikesir');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'BR', 'Bartin');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'BM', 'Batman');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'BB', 'Bayburt');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'BC', 'Bilecik');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'BG', 'Bingöl');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'BT', 'Bitlis');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'BL', 'Bolu' );
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'BD', 'Burdur');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'BU', 'Bursa');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'CK', 'Çanakkale');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'CI', 'Çankiri');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'CM', 'Çorum');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'DN', 'Denizli');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'DY', 'Diyarbakir');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'DU', 'Düzce');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'ED', 'Edirne');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'EG', 'Elazig');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'EN', 'Erzincan');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'EM', 'Erzurum');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'ES', 'Eskisehir');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'GA', 'Gaziantep');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'GI', 'Giresun');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'GU', 'Gümüshane');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'HK', 'Hakkari');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'HT', 'Hatay');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'IG', 'Igdir');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'IP', 'Isparta');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'IB', 'Istanbul');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'IZ', 'Izmir');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'KM', 'Kahramanmaras');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'KB', 'Karabük');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'KR', 'Karaman');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'KA', 'Kars');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'KS', 'Kastamonu');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'KY', 'Kayseri');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'KI', 'Kilis');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'KK', 'Kirikkale');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'KL', 'Kirklareli');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'KH', 'Kirsehir');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'KC', 'Kocaeli');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'KO', 'Konya');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'KU', 'Kütahya');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'ML', 'Malatya');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'MN', 'Manisa');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'MR', 'Mardin');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'IC', 'Mersin');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'MG', 'Mugla');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'MS', 'Mus');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'NV', 'Nevsehir');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'NG', 'Nigde');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'OR', 'Ordu');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'OS', 'Osmaniye');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'RI', 'Rize');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'SK', 'Sakarya');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'SS', 'Samsun');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'SU', 'Sanliurfa');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'SI', 'Siirt');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'SP', 'Sinop');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'SR', 'Sirnak');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'SV', 'Sivas');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'TG', 'Tekirdag');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'TT', 'Tokat');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'TB', 'Trabzon');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'TC', 'Tunceli');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'US', 'Usak');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'VA', 'Van');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'YL', 'Yalova');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'YZ', 'Yozgat');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (215,'ZO', 'Zonguldak');

#DokuMan - 2011-09-29 - Revised Italian zones
DELETE FROM zones WHERE zone_country_id = 105;
#Italy
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'AG','Agrigento');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'AL','Alessandria');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'AN','Ancona');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'AO','Aosta');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'AR','Arezzo');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'AP','Ascoli Piceno');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'AT','Asti');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'AV','Avellino');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'BA','Bari');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'BT','Barletta-Andria-Trani');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'BL','Belluno');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'BN','Benevento');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'BG','Bergamo');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'BI','Biella');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'BO','Bologna');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'BZ','Bolzano');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'BS','Brescia');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'BR','Brindisi');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'CA','Cagliari');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'CL','Caltanissetta');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'CB','Campobasso');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'CI','Carbonia-Iglesias');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'CE','Caserta');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'CT','Catania');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'CZ','Catanzaro');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'CH','Chieti');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'CO','Como');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'CS','Cosenza');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'CR','Cremona');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'KR','Crotone');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'CN','Cuneo');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'EN','Enna');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'FM','Fermo');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'FE','Ferrara');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'FI','Firenze');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'FG','Foggia');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'FC','Forlì-Cesena');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'FR','Frosinone');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'GE','Genova');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'GO','Gorizia');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'GR','Grosseto');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'IM','Imperia');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'IS','Isernia');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'SP','La Spezia');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'AQ','Aquila');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'LT','Latina');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'LE','Lecce');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'LC','Lecco');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'LI','Livorno');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'LO','Lodi');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'LU','Lucca');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'MC','Macerata');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'MN','Mantova');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'MS','Massa-Carrara');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'MT','Matera');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'ME','Messina');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'MI','Milano');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'MO','Modena');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'MB','Monza e della Brianza');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'NA','Napoli');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'NO','Novara');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'NU','Nuoro');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'OT','Olbia-Tempio');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'OR','Oristano');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'PD','Padova');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'PA','Palermo');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'PR','Parma');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'PV','Pavia');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'PG','Perugia');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'PU','Pesaro e Urbino');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'PE','Pescara');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'PC','Piacenza');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'PI','Pisa');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'PT','Pistoia');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'PN','Pordenone');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'PZ','Potenza');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'PO','Prato');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'RG','Ragusa');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'RA','Ravenna');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'RC','Reggio di Calabria');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'RE','Reggio Emilia');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'RI','Rieti');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'RN','Rimini');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'RM','Roma');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'RO','Rovigo');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'SA','Salerno');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'VS','Medio Campidano');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'SS','Sassari');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'SV','Savona');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'SI','Siena');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'SR','Siracusa');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'SO','Sondrio');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'TA','Taranto');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'TE','Teramo');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'TR','Terni');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'TO','Torino');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'OG','Ogliastra');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'TP','Trapani');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'TN','Trento');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'TV','Treviso');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'TS','Trieste');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'UD','Udine');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'VA','Varese');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'VE','Venezia');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'VB','Verbania');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'VC','Vercelli');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'VR','Verona');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'VV','Vibo Valentia');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'VI','Vicenza');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (105,'VT','Viterbo');

#DokuMan - 2011-05-09 - Fix Australian Dollar currency from 'AUS' to 'AUD'
UPDATE countries SET countries_iso_code_3 = 'AUD' WHERE countries_id = 13 LIMIT 1;
UPDATE payment_moneybookers_countries SET mb_cID = 'AUD' WHERE osc_cID = 13 LIMIT 1;

#DokuMan - 2011-06-06 - Create the database table for storing the bank code
DROP TABLE IF EXISTS banktransfer_blz;
CREATE TABLE IF NOT EXISTS banktransfer_blz (
  blz int(10) NOT NULL DEFAULT 0,
  bankname varchar(255) NOT NULL DEFAULT '',
  prz char(2) NOT NULL DEFAULT '',
  PRIMARY KEY (blz)
);

#DokuMan - 2011-07-26 - allow 5 characters, so language code like 'zh-CN' can be entered
ALTER TABLE languages MODIFY code char(5) NOT NULL;

#DokuMan - 2011-08-31 - enlarge field for products_weight to 6,3 decimals (instead of 5,2) to allow gramm exact entries up to 999,999kg
ALTER TABLE products MODIFY products_weight DECIMAL(6,3) NOT NULL;

#Tomcraft - 2011-09-22 - Moved ADVANCED_SEARCH_DEFAULT_OPERATOR to configuration_group_id 22
UPDATE configuration SET configuration_group_id = 22 WHERE configuration_key = 'ADVANCED_SEARCH_DEFAULT_OPERATOR';

#Tomcraft - 2011-11-08 - enlarge field 'products_name' from 64 characters to 255
ALTER TABLE orders_products MODIFY products_name varchar(255) NOT NULL;
ALTER TABLE products_description MODIFY products_name varchar(255) NOT NULL;

#franky_n - 2011-11-09 - added manufacturers model no.
ALTER TABLE products ADD products_manufacturers_model varchar(64) AFTER manufacturers_id;

#DokuMan - 2011-12-19 - Change language_id from int to tinyint because 255 languages should be enough
ALTER TABLE products_xsell_grp_name MODIFY language_id TINYINT NOT NULL DEFAULT 1;
ALTER TABLE categories_description MODIFY language_id TINYINT NOT NULL DEFAULT 1;
ALTER TABLE customers_status MODIFY language_id TINYINT NOT NULL DEFAULT 1;
ALTER TABLE orders_status MODIFY language_id TINYINT NOT NULL DEFAULT 1;
ALTER TABLE shipping_status MODIFY language_id TINYINT NOT NULL DEFAULT 1;
ALTER TABLE products_description MODIFY language_id TINYINT NOT NULL DEFAULT 1;
ALTER TABLE products_options MODIFY language_id TINYINT NOT NULL DEFAULT 1;
ALTER TABLE products_options_values MODIFY language_id TINYINT NOT NULL DEFAULT 1;
ALTER TABLE products_vpe MODIFY language_id TINYINT NOT NULL DEFAULT 1;
ALTER TABLE coupons_description MODIFY language_id TINYINT NOT NULL DEFAULT 1;

#DokuMan - 2012-01-04 - remove table card_blacklist since the credit card payment module was also removed
DROP TABLE IF EXISTS card_blacklist;

#DokuMan - 2012-01-05 - minor typo fix on SMTP_Backup_Server
UPDATE configuration SET configuration_key = 'SMTP_BACKUP_SERVER' WHERE configuration_key = 'SMTP_Backup_Server';

#DokuMan - 2012-02-23 - enlarge fields for categories
ALTER TABLE categories MODIFY products_sorting VARCHAR(64);
ALTER TABLE categories MODIFY products_sorting2 VARCHAR(64);
ALTER TABLE categories_description MODIFY categories_name VARCHAR(255);

#DokuMan - 2012-05-26 - BLZ-update support in backend
ALTER TABLE admin_access ADD blz_update INT(1) DEFAULT 0 NOT NULL;
UPDATE admin_access SET blz_update = 1 WHERE customers_id = '1';
UPDATE admin_access SET blz_update = 1 WHERE customers_id = 'groups';

#DokuMan - 2012-07-02 - Avoid deactivation of banners after showing once
ALTER TABLE banners MODIFY expires_impressions INT(7) DEFAULT NULL;

#h-h-h - 2012-07-07 - Get ready for IPv6
ALTER TABLE campaigns_ip MODIFY user_ip VARCHAR (39);
ALTER TABLE coupon_gv_queue MODIFY ipaddr VARCHAR (39);
ALTER TABLE customers_ip MODIFY customers_ip VARCHAR (39);
ALTER TABLE orders MODIFY customers_ip VARCHAR (39);
ALTER TABLE whos_online MODIFY ip_address VARCHAR (39);
ALTER TABLE coupon_redeem_track MODIFY redeem_ip VARCHAR (39);

#Web28 - 2012-07-20 - add attributes_ean
ALTER TABLE products_attributes ADD attributes_ean VARCHAR(64) NULL DEFAULT NULL;

#Web28 - 2012-07-30 - add flag for admin identification
DROP TABLE IF EXISTS sessions;
CREATE TABLE sessions (
  sesskey VARCHAR(32) NOT NULL,
  expiry INT(11) unsigned NOT NULL,
  value text NOT NULL,
  flag VARCHAR(5) NULL DEFAULT NULL,
  PRIMARY KEY (sesskey)
);

#DokuMan - 2012-08-21 - fix default value for customer group merchants
UPDATE customers_status SET customers_status_add_tax_ot  = '1' WHERE customers_status_id = '3';
  
#DokuMan - 2012-08-21 - remove unused pictures in Admin
ALTER TABLE admin_access ADD removeoldpics INT(1) NOT NULL DEFAULT 0;
UPDATE admin_access SET removeoldpics = 1 WHERE customers_id = 1;
UPDATE admin_access SET removeoldpics = 5 WHERE customers_id = 'groups';

#Web28 - 2012-08-21 - add ids for cancelled orders attributes stock handling
ALTER TABLE orders_products_attributes ADD orders_products_options_id INT(11) NOT NULL;
ALTER TABLE orders_products_attributes ADD orders_products_options_values_id INT(11) NOT NULL;

#Web28 - 2012-08-31 - move admin options to new "Adminarea" page
UPDATE configuration SET configuration_group_id = '1000', sort_order = '10', last_modified = NOW() WHERE configuration_key = 'PRICE_IS_BRUTTO';
UPDATE configuration SET configuration_group_id = '1000', sort_order = '20', last_modified = NOW() WHERE configuration_key = 'USE_ADMIN_TOP_MENU';
UPDATE configuration SET configuration_group_id = '1000', sort_order = '21', last_modified = NOW() WHERE configuration_key = 'USE_ADMIN_LANG_TABS';

#Web28 - 2012-09-28 - add image_manipulator_GD2_advanced.php (supports transparent png)
UPDATE configuration SET set_function = 'xtc_cfg_select_option(array(\'image_manipulator_GD2.php\', \'image_manipulator_GD2_advanced.php\', \'image_manipulator_GD1.php\'),' WHERE configuration_key = 'IMAGE_MANIPULATOR';

# vr - 2012-10-26 - add index idx_customers_id
ALTER TABLE orders
  ADD INDEX idx_customers_id (customers_id);
  
#Web28 - 2012-07-16 - New order description using in checkout
ALTER TABLE products_description ADD products_order_description TEXT NULL DEFAULT '';
ALTER TABLE orders_products ADD products_order_description TEXT NULL DEFAULT '';

#Tomcraft - 2012-11-15 - Added janolaw module
ALTER TABLE admin_access ADD janolaw INT(1) NOT NULL DEFAULT 0;
UPDATE admin_access SET janolaw = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET janolaw = 1 WHERE customers_id = 'groups' LIMIT 1;

#Web28 - 2012-11-26 - define set_function to NULL
ALTER TABLE configuration CHANGE set_function set_function VARCHAR( 255 ) NULL;

#Tomcraft - 2012-12-08 - Added haendlerbund module
ALTER TABLE admin_access ADD haendlerbund INT(1) NOT NULL DEFAULT 0;
UPDATE admin_access SET haendlerbund = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET haendlerbund = 1 WHERE customers_id = 'groups' LIMIT 1;

#Web28 - 2012-12-30 - set new sort_order by configuration_group_id 5 , Customer Details
UPDATE configuration SET configuration_group_id = '5', sort_order = '10', last_modified = NOW() WHERE configuration_key = 'ACCOUNT_GENDER';
UPDATE configuration SET configuration_group_id = '5', sort_order = '20', last_modified = NOW() WHERE configuration_key = 'ACCOUNT_DOB';
UPDATE configuration SET configuration_group_id = '5', sort_order = '30', last_modified = NOW() WHERE configuration_key = 'ACCOUNT_COMPANY';
UPDATE configuration SET configuration_group_id = '5', sort_order = '50', last_modified = NOW() WHERE configuration_key = 'ACCOUNT_SUBURB';
UPDATE configuration SET configuration_group_id = '5', sort_order = '60', last_modified = NOW() WHERE configuration_key = 'ACCOUNT_STATE';
UPDATE configuration SET configuration_group_id = '5', sort_order = '100', last_modified = NOW() WHERE configuration_key = 'ACCOUNT_OPTIONS';
UPDATE configuration SET configuration_group_id = '5', sort_order = '110', last_modified = NOW() WHERE configuration_key = 'DELETE_GUEST_ACCOUNT';

#Web28 - 2012-12-31 - add comments_sent for correct representation of the comments in the customers account 
ALTER TABLE orders_status_history ADD comments_sent INT( 1 )  NULL DEFAULT '0';
UPDATE orders_status_history SET comments_sent = '1' WHERE customer_notified = '1';

# Keep an empty line at the end of this file for the db_updater to work properly
