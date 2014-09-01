UPDATE products_xsell_grp_name SET language_id = 999 WHERE products_xsell_grp_name.language_id = 2;
UPDATE products_xsell_grp_name SET language_id = 2 WHERE products_xsell_grp_name.language_id = 1;
UPDATE products_xsell_grp_name SET language_id = 1 WHERE products_xsell_grp_name.language_id = 999;

UPDATE categories_description SET language_id = 999 WHERE categories_description.language_id = 2;
UPDATE categories_description SET language_id = 2 WHERE categories_description.language_id = 1;
UPDATE categories_description SET language_id = 1 WHERE categories_description.language_id = 999;

UPDATE customers_status SET language_id = 999 WHERE customers_status.language_id = 2;
UPDATE customers_status SET language_id = 2 WHERE customers_status.language_id = 1;
UPDATE customers_status SET language_id = 1 WHERE customers_status.language_id = 999;

UPDATE orders_status SET language_id = 999 WHERE orders_status.language_id = 2;
UPDATE orders_status SET language_id = 2 WHERE orders_status.language_id = 1;
UPDATE orders_status SET language_id = 1 WHERE orders_status.language_id = 999;

UPDATE shipping_status SET language_id = 999 WHERE shipping_status.language_id = 2;
UPDATE shipping_status SET language_id = 2 WHERE shipping_status.language_id = 1;
UPDATE shipping_status SET language_id = 1 WHERE shipping_status.language_id = 999;

UPDATE products_description SET language_id = 999 WHERE products_description.language_id = 2;
UPDATE products_description SET language_id = 2 WHERE products_description.language_id = 1;
UPDATE products_description SET language_id = 1 WHERE products_description.language_id = 999;

UPDATE products_options SET language_id = 999 WHERE products_options.language_id = 2;
UPDATE products_options SET language_id = 2 WHERE products_options.language_id = 1;
UPDATE products_options SET language_id = 1 WHERE products_options.language_id = 999;

UPDATE products_options_values SET language_id = 999 WHERE products_options_values.language_id = 2;
UPDATE products_options_values SET language_id = 2 WHERE products_options_values.language_id = 1;
UPDATE products_options_values SET language_id = 1 WHERE products_options_values.language_id = 999;

UPDATE products_vpe SET language_id = 999 WHERE products_vpe.language_id = 2;
UPDATE products_vpe SET language_id = 2 WHERE products_vpe.language_id = 1;
UPDATE products_vpe SET language_id = 1 WHERE products_vpe.language_id = 999;

UPDATE coupons_description SET language_id = 999 WHERE coupons_description.language_id = 2;
UPDATE coupons_description SET language_id = 2 WHERE coupons_description.language_id = 1;
UPDATE coupons_description SET language_id = 1 WHERE coupons_description.language_id = 999;

UPDATE languages SET languages_id = 999 WHERE languages.languages_id = 2;
UPDATE languages SET languages_id = 2 WHERE languages.languages_id = 1;
UPDATE languages SET languages_id = 1 WHERE languages.languages_id = 999;

UPDATE manufacturers_info SET languages_id = 999 WHERE manufacturers_info.languages_id = 2;
UPDATE manufacturers_info SET languages_id = 2 WHERE manufacturers_info.languages_id = 1;
UPDATE manufacturers_info SET languages_id = 1 WHERE manufacturers_info.languages_id = 999;

UPDATE reviews_description SET languages_id = 999 WHERE reviews_description.languages_id = 2;
UPDATE reviews_description SET languages_id = 2 WHERE reviews_description.languages_id = 1;
UPDATE reviews_description SET languages_id = 1 WHERE reviews_description.languages_id = 999;

UPDATE content_manager SET languages_id = 999 WHERE content_manager.languages_id = 2;
UPDATE content_manager SET languages_id = 2 WHERE content_manager.languages_id = 1;
UPDATE content_manager SET languages_id = 1 WHERE content_manager.languages_id = 999;

UPDATE products_content SET languages_id = 999 WHERE products_content.languages_id = 2;
UPDATE products_content SET languages_id = 2 WHERE products_content.languages_id = 1;
UPDATE products_content SET languages_id = 1 WHERE products_content.languages_id = 999;
