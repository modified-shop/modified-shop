# -----------------------------------------------------------------------------------------
#  $Id$
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#Tomcraft - 2025-05-28 - changed database_version
INSERT INTO `database_version` (`version`, `date_added`) VALUES ('MOD_3.1.5', NOW());

#Tomcraft - 2025-05-28 - replace mail placeholder
UPDATE content_manager SET content_text = REPLACE(content_text, '@muster.de', '@example.com');

# Keep an empty line at the end of this file for the db_updater to work properly