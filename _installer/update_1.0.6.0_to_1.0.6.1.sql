# -----------------------------------------------------------------------------------------
#  $Id: update_1.0.5.0_to_1.0.6.0.sql 3813 2012-10-29 11:54:40Z Tomcraft1980 $
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#Tomcraft - 2016-01-18 - changed database_version
UPDATE database_version SET version = 'MOD_1.0.6.1';

### Subsequent updates for 1.06 rev 4642 to 1.06 rev 4642 SP1
#Web28 - 2013-10-27 - added IBAN and BIC in banktransfer payment module
ALTER TABLE banktransfer ADD banktransfer_iban VARCHAR(34) DEFAULT NULL AFTER banktransfer_blz;
ALTER TABLE banktransfer ADD banktransfer_bic VARCHAR(11) DEFAULT NULL AFTER banktransfer_iban;
ALTER TABLE banktransfer ADD banktransfer_owner_email VARCHAR(96) DEFAULT NULL;

# Keep an empty line at the end of this file for the db_updater to work properly
