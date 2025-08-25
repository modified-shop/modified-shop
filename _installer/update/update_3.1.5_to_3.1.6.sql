# -----------------------------------------------------------------------------------------
#  $Id$
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#Tomcraft - 2025-05-28 - changed database_version
INSERT INTO `database_version` (`version`, `date_added`) VALUES ('MOD_3.1.6', NOW());

#GTB - 2025-08-25 - change min length to 0 for state dropdown
UPDATE configuration SET configuration_value = '0' WHERE configuration_key = 'ENTRY_STATE_MIN_LENGTH';

# Keep an empty line at the end of this file for the db_updater to work properly