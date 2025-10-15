# -----------------------------------------------------------------------------------------
#  $Id$
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#GTB - 2025-10-15 - changed database_version
INSERT INTO `database_version` (`version`, `date_added`) VALUES ('MOD_3.2.0', NOW());

#GTB - 2025-10-15 - remove moneybookers / skrill
DELETE FROM configuration_group WHERE configuration_group_id = '31';

# Keep an empty line at the end of this file for the db_updater to work properly