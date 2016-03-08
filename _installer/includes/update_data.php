<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/  
  
  
  $modified_sql_array = array(
    'address_book' => array(
      'drop' => array(
        'idx' => array(
          'idx_address_book_customers_id',
        ),
      ),
    ),
    'address_format' => array(),
    'admin_access' => array(
      'drop' => array(
        'col' => array(
          'blacklist',
          'xajax',
        ),
      ),
    ),
    'banktransfer' => array(
      'drop' => array(
        'idx' => array(
          'orders_id',
        ),
      ),
    ),
    'banktransfer_blz' => array(),
    'banners' => array(),
    'banners_history' => array(),
    'campaigns' => array(
      'drop' => array(
        'idx' => array(
          'IDX_CAMPAIGNS_NAME',
        ),
      ),
    ),
    'campaigns_ip' => array(),
    'carriers' => array(),
    'categories' => array(),
    'categories_description' => array(),
    'cm_file_flags' => array(),
    'configuration' => array(),
    'configuration_group' => array(),
    'content_manager' => array(
      'drop' => array(
        'idx' => array(
          'content_meta_title',
          'content_meta_description',
          'content_meta_keywords',
        ),
      ),
    ),
    'countries' => array(
      'drop' => array(
        'idx' => array(
          'IDX_COUNTRIES_NAME',
        ),
      ),
    ),
    'coupon_email_track' => array(),
    'coupon_gv_customer' => array(
      'drop' => array(
        'idx' => array(
          'customer_id',
        ),
      ),
    ),
    'coupon_gv_queue' => array(
      'drop' => array(
        'idx' => array(
          'uid',
        ),
      ),
    ),
    'coupon_redeem_track' => array(),
    'coupons' => array(
      'drop' => array(
        'idx' => array(
          'idx_coupon_code',
        ),
      ),
    ),
    'coupons_description' => array(
      'drop' => array(
        'idx' => array(
          'coupon_id',
        ),
      ),
    ),
    'currencies' => array(),
    'customers' => array(),
    'customers_basket' => array(),
    'customers_basket_attributes' => array(),
    'customers_info' => array(),
    'customers_ip' => array(
      'drop' => array(
        'idx' => array(
          'customers_id',
        ),
      ),
    ),
    'customers_memo' => array(),
    'customers_status' => array(
      'drop' => array(
        'idx' => array(
          'idx_orders_status_name',
        ),
      ),
    ),
    'customers_status_history' => array(),
    'database_version' => array(),
    'geo_zones' => array(),
    'languages' => array(
      'drop' => array(
        'idx' => array(
          'idx_languages_name',
        ),
      ),
    ),
    'manufacturers' => array(
      'drop' => array(
        'idx' => array(
          'IDX_MANUFACTURERS_NAME',
        ),
      ),
    ),
    'manufacturers_info' => array(),
    'module_backup' => array(),
    'newsfeed' => array(),
    'module_newsletter' => array(),
    'newsletter_recipients' => array(),
    'newsletters' => array(),
    'newsletters_history' => array(),
    'orders' => array(
      'drop' => array(
        'col' => array(
          'cc_type',
          'cc_owner',
          'cc_number',
          'cc_expires',
          'cc_start',
          'cc_issue',
          'cc_cvv',
        ),
        'idx' => array(
          'orders_id',
        ),
      ),
    ),
    'orders_products' => array(
      'drop' => array(
        'idx' => array(
          'orders_id',
          'products_id',
        ),
      ),
    ),
    'orders_products_attributes' => array(
      'drop' => array(
        'idx' => array(
          'products_id',
          'options',
        ),
      ),
    ),
    'orders_products_download' => array(),
    'orders_recalculate' => array(),
    'orders_status' => array(),
    'orders_status_history' => array(),
    'orders_total' => array(
      'drop' => array(
        'idx' => array(
          'idx_orders_total_orders_id',
        ),
      ),
    ),
    'orders_tracking' => array(
      'rename' => array(
        'tracking_id' => 'ortra_id',
        'orders_id' => 'ortra_order_id',
        'carrier_id' => 'ortra_carrier_id',
        'parcel_id' => 'ortra_parcel_id',
      ),
      'drop' => array(
        'idx' => array(
          'orders_id',
        ),
      ),
    ),
    'payment_moneybookers' => array(),
    'products' => array(),
    'products_attributes' => array(),
    'products_attributes_download' => array(),
    'products_content' => array(),
    'products_description' => array(
      'drop' => array(
        'idx' => array(
          'products_name',
        ),
      ),
    ),
    'products_graduated_prices' => array(
      'drop' => array(
        'idx' => array(
          'products_id',
        ),
      ),
    ),
    'products_images' => array(),
    'products_notifications' => array(),
    'products_options' => array(),
    'products_options_values' => array(),
    'products_options_values_to_products_options' => array(),
    'products_tags' => array(),
    'products_tags_options' => array(),
    'products_tags_values' => array(),
    'products_to_categories' => array(
      'drop' => array(
        'idx' => array(
          'idx_categories_id',
          'PRIMARY',
        ),
      ),
    ),
    'products_vpe' => array(),
    'products_xsell' => array(),
    'products_xsell_grp_name' => array(),
    'reviews' => array(),
    'reviews_description' => array(),
    'sessions' => array(),
    'shipping_status' => array(),
    'shop_configuration' => array(
      'drop' => array(
        'idx' => array(
          'configuration_key',
        ),
      ),
    ),
    'specials' => array(
      'drop' => array(
        'idx' => array(
          'idx_specials_products_id',
        ),
      ),
    ),
    'tax_class' => array(),
    'tax_rates' => array(),
    'whos_online' => array(),
    'zones' => array(),
    'zones_to_geo_zones' => array(),
  );
  
  
  $modified_drop_table_array = array(
    'counter',
    'counter_history',
    'gls_country_to_postal',
    'gls_postal_to_weight',
    'gls_weight',
    'media_content',
    'payment_moneybookers_currencies',
    'payment_moneybookers_countries',
  );
  
?>