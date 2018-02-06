# -----------------------------------------------------------------------------------------
#  $Id$
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#Tomcraft - 2018-02-06 - changed database_version
INSERT INTO `database_version` (`version`) VALUES ('MOD_2.0.4.0');

#Tomcraft - 2018-02-06 - change Online Dispute Resolution (odr) links from http to https
UPDATE content_manager SET content_text = REPLACE(content_text, 'http://ec.europa.eu/consumers/odr', 'https://ec.europa.eu/consumers/odr');

# Keep an empty line at the end of this file for the db_updater to work properly