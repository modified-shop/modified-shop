# -----------------------------------------------------------------------------------------
#  $Id$
#
#  xtc-Modified
#  http://www.xtc-modified.org
#
#  Copyright (c) 2010 xtc-Modified
#  -----------------------------------------------------------------------------------------

#Tomcraft - 2010-02-03 - changed database_version
UPDATE database_version SET version = 'xtcM_1.0.4.0';

#vr - 2010-02-02 - Revised English Counties, thx to Chris
DELETE FROM zones WHERE zone_country_id = 222;
INSERT INTO zones VALUES ('',222,'BAS','Bath and North East Somerset');
INSERT INTO zones VALUES ('',222,'BDF','Bedfordshire');
INSERT INTO zones VALUES ('',222,'WBK','Berkshire');
INSERT INTO zones VALUES ('',222,'BBD','Blackburn with Darwen');
INSERT INTO zones VALUES ('',222,'BPL','Blackpool');
INSERT INTO zones VALUES ('',222,'BPL','Bournemouth');
INSERT INTO zones VALUES ('',222,'BNH','Brighton and Hove');
INSERT INTO zones VALUES ('',222,'BST','Bristol');
INSERT INTO zones VALUES ('',222,'BKM','Buckinghamshire');
INSERT INTO zones VALUES ('',222,'CAM','Cambridgeshire');
INSERT INTO zones VALUES ('',222,'CHS','Cheshire');
INSERT INTO zones VALUES ('',222,'CON','Cornwall');
INSERT INTO zones VALUES ('',222,'DUR','County Durham');
INSERT INTO zones VALUES ('',222,'CMA','Cumbria');
INSERT INTO zones VALUES ('',222,'DAL','Darlington');
INSERT INTO zones VALUES ('',222,'DER','Derby');
INSERT INTO zones VALUES ('',222,'DBY','Derbyshire');
INSERT INTO zones VALUES ('',222,'DEV','Devon');
INSERT INTO zones VALUES ('',222,'DOR','Dorset');
INSERT INTO zones VALUES ('',222,'ERY','East Riding of Yorkshire');
INSERT INTO zones VALUES ('',222,'ESX','East Sussex');
INSERT INTO zones VALUES ('',222,'ESS','Essex');
INSERT INTO zones VALUES ('',222,'GLS','Gloucestershire');
INSERT INTO zones VALUES ('',222,'LND','Greater London');
INSERT INTO zones VALUES ('',222,'MAN','Greater Manchester');
INSERT INTO zones VALUES ('',222,'HAL','Halton');
INSERT INTO zones VALUES ('',222,'HAM','Hampshire');
INSERT INTO zones VALUES ('',222,'HPL','Hartlepool');
INSERT INTO zones VALUES ('',222,'HEF','Herefordshire');
INSERT INTO zones VALUES ('',222,'HRT','Hertfordshire');
INSERT INTO zones VALUES ('',222,'KHL','Hull');
INSERT INTO zones VALUES ('',222,'IOW','Isle of Wight');
INSERT INTO zones VALUES ('',222,'KEN','Kent');
INSERT INTO zones VALUES ('',222,'LAN','Lancashire');
INSERT INTO zones VALUES ('',222,'LCE','Leicester');
INSERT INTO zones VALUES ('',222,'LEC','Leicestershire');
INSERT INTO zones VALUES ('',222,'LIN','Lincolnshire');
INSERT INTO zones VALUES ('',222,'LUT','Luton');
INSERT INTO zones VALUES ('',222,'MDW','Medway');
INSERT INTO zones VALUES ('',222,'MER','Merseyside');
INSERT INTO zones VALUES ('',222,'MDB','Middlesbrough');
INSERT INTO zones VALUES ('',222,'MDB','Milton Keynes');
INSERT INTO zones VALUES ('',222,'NFK','Norfolk');
INSERT INTO zones VALUES ('',222,'NTH','Northamptonshire');
INSERT INTO zones VALUES ('',222,'NEL','North East Lincolnshire');
INSERT INTO zones VALUES ('',222,'NLN','North Lincolnshire');
INSERT INTO zones VALUES ('',222,'NSM','North Somerset');
INSERT INTO zones VALUES ('',222,'NBL','Northumberland');
INSERT INTO zones VALUES ('',222,'NYK','North Yorkshire');
INSERT INTO zones VALUES ('',222,'NGM','Nottingham');
INSERT INTO zones VALUES ('',222,'NTT','Nottinghamshire');
INSERT INTO zones VALUES ('',222,'OXF','Oxfordshire');
INSERT INTO zones VALUES ('',222,'PTE','Peterborough');
INSERT INTO zones VALUES ('',222,'PLY','Plymouth');
INSERT INTO zones VALUES ('',222,'POL','Poole');
INSERT INTO zones VALUES ('',222,'POR','Portsmouth');
INSERT INTO zones VALUES ('',222,'RCC','Redcar and Cleveland');
INSERT INTO zones VALUES ('',222,'RUT','Rutland');
INSERT INTO zones VALUES ('',222,'SHR','Shropshire');
INSERT INTO zones VALUES ('',222,'SOM','Somerset');
INSERT INTO zones VALUES ('',222,'STH','Southampton');
INSERT INTO zones VALUES ('',222,'SOS','Southend-on-Sea');
INSERT INTO zones VALUES ('',222,'SGC','South Gloucestershire');
INSERT INTO zones VALUES ('',222,'SYK','South Yorkshire');
INSERT INTO zones VALUES ('',222,'STS','Staffordshire');
INSERT INTO zones VALUES ('',222,'STT','Stockton-on-Tees');
INSERT INTO zones VALUES ('',222,'STE','Stoke-on-Trent');
INSERT INTO zones VALUES ('',222,'SFK','Suffolk');
INSERT INTO zones VALUES ('',222,'SRY','Surrey');
INSERT INTO zones VALUES ('',222,'SWD','Swindon');
INSERT INTO zones VALUES ('',222,'TFW','Telford and Wrekin');
INSERT INTO zones VALUES ('',222,'THR','Thurrock');
INSERT INTO zones VALUES ('',222,'TOB','Torbay');
INSERT INTO zones VALUES ('',222,'TYW','Tyne and Wear');
INSERT INTO zones VALUES ('',222,'WRT','Warrington');
INSERT INTO zones VALUES ('',222,'WAR','Warwickshire');
INSERT INTO zones VALUES ('',222,'WMI','West Midlands');
INSERT INTO zones VALUES ('',222,'WSX','West Sussex');
INSERT INTO zones VALUES ('',222,'WYK','West Yorkshire');
INSERT INTO zones VALUES ('',222,'WIL','Wiltshire');
INSERT INTO zones VALUES ('',222,'WOR','Worcestershire');
INSERT INTO zones VALUES ('',222,'YOR','York');

#DokuMan - 2010-02-11 - set default separator sign to semicolon ';' instead of tabulator '\t'
UPDATE configuration SET configuration_value = ';', last_modified = NOW() WHERE configuration_key = 'CSV_SEPERATOR';

#Tomcraft - 2010-02-16 - Update Countries (delete Yugoslavia, add Serbia and Monetegro)
# Fixed in update_1.0.4.0_to_1.0.5.0.sql

#vr - 2010-03-01 - Additional index on specials, thx to Georg
ALTER TABLE specials
  ADD INDEX idx_specials_products_id (products_id);

#vr - 2010-04-21 - Additional indices on orders_products
ALTER TABLE orders_products
  ADD INDEX orders_id (orders_id),
  ADD INDEX products_id (products_id);

#vr - 2010-04-21 - Additional indices on products_attributes
ALTER TABLE products_attributes
  ADD INDEX products_id (products_id),
  ADD INDEX options (options_id, options_values_id);

#DokuMan - 2010-06-28 - Added http_referer to table whos_online
ALTER TABLE whos_online
  ADD http_referer varchar(255) NOT NULL DEFAULT '' AFTER last_page_url;

#Tomcraft - 2010-06-09 - Added right of revocation
UPDATE content_manager SET content_id = 117 WHERE content_id = 17;
UPDATE content_manager SET content_group = 999 WHERE content_id = 117;
UPDATE content_manager SET content_id = 118 WHERE content_id = 18;
UPDATE content_manager SET content_group = 999 WHERE content_id = 118;
INSERT INTO content_manager VALUES (17, 0, 0, '', 1, 'Right of revocation', 'Right of revocation', '<p><strong>Right of revocation<br /></strong><br />Add your right of revocation here.</p>', 0, 1, '', 1, 9, 0, '', '', '');
INSERT INTO content_manager VALUES (18, 0, 0, '', 2, 'Widerrufsrecht', 'Widerrufsrecht', '<p><strong>Widerrufsrecht<br /></strong><br />F&uuml;gen Sie hier das Widerrufsrecht ein.</p>', 0, 1, '', 1, 9, 0, '', '', '');
UPDATE configuration SET configuration_value = 9, last_modified = NOW() WHERE configuration_key = 'REVOCATION_ID';

# Keep an empty line at the end of this file for the db_updater to work properly
