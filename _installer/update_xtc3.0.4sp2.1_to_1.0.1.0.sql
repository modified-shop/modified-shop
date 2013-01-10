# -----------------------------------------------------------------------------------------
#  $Id$
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
) ENGINE=myisam;

# Set database Version to minimum Version 1.0.1.0 (1.0.0.0 not allowed)
INSERT INTO database_version VALUES ('MOD_1.0.1.0');
 
UPDATE configuration SET configuration_value = 'xtc5', last_modified = NOW()
WHERE configuration_key = 'CURRENT_TEMPLATE';
 
ALTER TABLE products MODIFY products_discount_allowed decimal(4,2) DEFAULT '0' NOT NULL;

# Keep an empty line at the end of this file for the db_updater to work properly
