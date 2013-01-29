#Web28 - 2012-12-30 - set new sort_order by configuration_group_id 5 , Customer Details
UPDATE configuration SET configuration_group_id = '5', sort_order = '10', last_modified = NOW() WHERE configuration_key = 'ACCOUNT_GENDER';
UPDATE configuration SET configuration_group_id = '5', sort_order = '20', last_modified = NOW() WHERE configuration_key = 'ACCOUNT_DOB';
UPDATE configuration SET configuration_group_id = '5', sort_order = '30', last_modified = NOW() WHERE configuration_key = 'ACCOUNT_COMPANY';
UPDATE configuration SET configuration_group_id = '5', sort_order = '50', last_modified = NOW() WHERE configuration_key = 'ACCOUNT_SUBURB';
UPDATE configuration SET configuration_group_id = '5', sort_order = '60', last_modified = NOW() WHERE configuration_key = 'ACCOUNT_STATE';
UPDATE configuration SET configuration_group_id = '5', sort_order = '100', last_modified = NOW() WHERE configuration_key = 'ACCOUNT_OPTIONS';
UPDATE configuration SET configuration_group_id = '5', sort_order = '110', last_modified = NOW() WHERE configuration_key = 'DELETE_GUEST_ACCOUNT';

#Web28 - 2012-12-31 - add comments_sent for correct representation of the comments in the customers account
ALTER TABLE orders_status_history ADD comments_sent INT( 1 )  NULL DEFAULT '0';
UPDATE orders_status_history SET comments_sent = '1' WHERE customer_notified = '1';

DELETE FROM `configuration` WHERE `configuration_key` = 'AFTERBUY_DEALERS';
DELETE FROM `configuration` WHERE `configuration_key` = 'AFTERBUY_IGNORE_GROUPE';