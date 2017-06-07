# -----------------------------------------------------------------------------------------
#  $Id: update_xtc3.0.4sp2.1_to_1.00.sql 2113 2011-08-26 16:37:30Z Tomcraft1980 $
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

# Execute the following SQL-queries to update the database schema
# from xt:Commerce 3.0.4 SP2.1 to modified eCommerce Shopsoftware 1.0.1.0

CREATE TABLE IF NOT EXISTS database_version (
  version VARCHAR(32) NOT NULL
);

# Set database Version to minimum Version 1.0.1.0 (1.0.0.0 not allowed)
DELETE FROM database_version;
INSERT INTO database_version (version) VALUES ('MOD_1.0.1.0');
 
UPDATE configuration SET configuration_value = 'xtc5', last_modified = NOW()
WHERE configuration_key = 'CURRENT_TEMPLATE';
 
ALTER TABLE products MODIFY products_discount_allowed decimal(4,2) DEFAULT '0' NOT NULL;

# Keep an empty line at the end of this file for the db_updater to work properly
