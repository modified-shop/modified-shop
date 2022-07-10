# -----------------------------------------------------------------------------------------
#  $Id$
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#GTB - 2022-07-10 - changed database_version
INSERT INTO `database_version` (`version`) VALUES ('MOD_2.0.8.0');

#GTB - 2022-07-10 - fix wrong function
UPDATE configuration SET set_function = 'xtc_cfg_select_content(\'REVIEWS_PURCHASED_INFOS\',' WHERE configuration_key = 'REVIEWS_PURCHASED_INFOS';

# Keep an empty line at the end of this file for the db_updater to work properly