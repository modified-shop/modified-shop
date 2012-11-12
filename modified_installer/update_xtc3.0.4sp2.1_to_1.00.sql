# -----------------------------------------------------------------------------------------
#  $Id$
#
#  xtc-Modified
#  http://www.xtc-modified.org
#
#  Copyright (c) 2011 xtc-Modified
#  -----------------------------------------------------------------------------------------

# Execute the following SQL-queries to update the database schema
# from xt:Commerce 3.0.4 SP2.1 to xtcModified 1.00

CREATE TABLE IF NOT EXISTS database_version (
  version VARCHAR(32) NOT NULL
) ENGINE=myisam DEFAULT CHARSET=latin1 COLLATE latin1_german1_ci;

UPDATE database_version SET version = 'xtcM_1.0.0.0';
 
UPDATE configuration SET configuration_value = 'xtc5', last_modified = NOW()
WHERE configuration_key = 'CURRENT_TEMPLATE';
 
ALTER TABLE products MODIFY products_discount_allowed decimal(4,2) DEFAULT '0' NOT NULL;

# Keep an empty line at the end of this file for the db_updater to work properly
