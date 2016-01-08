<?php
/**
 * Shopgate GmbH
 *
 * URHEBERRECHTSHINWEIS
 *
 * Dieses Plugin ist urheberrechtlich geschützt. Es darf ausschließlich von Kunden der Shopgate GmbH
 * zum Zwecke der eigenen Kommunikation zwischen dem IT-System des Kunden mit dem IT-System der
 * Shopgate GmbH über www.shopgate.com verwendet werden. Eine darüber hinausgehende Vervielfältigung, Verbreitung,
 * öffentliche Zugänglichmachung, Bearbeitung oder Weitergabe an Dritte ist nur mit unserer vorherigen
 * schriftlichen Zustimmung zulässig. Die Regelungen der §§ 69 d Abs. 2, 3 und 69 e UrhG bleiben hiervon unberührt.
 *
 * COPYRIGHT NOTICE
 *
 * This plugin is the subject of copyright protection. It is only for the use of Shopgate GmbH customers,
 * for the purpose of facilitating communication between the IT system of the customer and the IT system
 * of Shopgate GmbH via www.shopgate.com. Any reproduction, dissemination, public propagation, processing or
 * transfer to third parties is only permitted where we previously consented thereto in writing. The provisions
 * of paragraph 69 d, sub-paragraphs 2, 3 and paragraph 69, sub-paragraph e of the German Copyright Act shall remain unaffected.
 *
 * @author Shopgate GmbH <interfaces@shopgate.com>
 */


include_once DIR_FS_CATALOG
    . 'includes/external/shopgate/shopgate_library/shopgate.php';

define('SHOPGATE_SETTING_EXPORT_DESCRIPTION', 0);
define('SHOPGATE_SETTING_EXPORT_SHORTDESCRIPTION', 1);
define('SHOPGATE_SETTING_EXPORT_DESCRIPTION_SHORTDESCRIPTION', 2);
define('SHOPGATE_SETTING_EXPORT_SHORTDESCRIPTION_DESCRIPTION', 3);

define('TABLE_ORDERS_SHOPGATE_ORDER', 'orders_shopgate_order');
define('TABLE_CUSTOMERS_SHOPGATE_CUSTOMER', 'customers_shopgate_customer');

class ShopgateConfigModified extends ShopgateConfig
{
    
    protected $redirect_languages;
    
    protected $shipping;
    
    protected $tax_zone_id;
    
    protected $customer_price_group;
    
    protected $order_status_open;
    protected $order_status_shipped;
    protected $order_status_shipping_blocked;
    protected $order_status_canceled;
    protected $payment_name_mapping;
    
    protected $reverse_categories_sort_order;
    protected $reverse_items_sort_order;
    protected $export_description_type;
    
    protected $shopgate_table_version;
    
    protected $maximum_category_export_depth;
    protected $send_order_confirmation_mail;
    protected $export_new_products_category;
    protected $export_new_products_category_id;
    protected $export_special_products_category;
    protected $export_special_products_category_id;
    protected $export_option_as_input_field;
    
    public function startup()
    {
        // overwrite some library defaults
        $this->plugin_name                    = 'Modified';
        $this->enable_redirect_keyword_update = 24;
        $this->enable_ping                    = 1;
        $this->enable_add_order               = 1;
        $this->enable_update_order            = 1;
        $this->enable_get_orders              = 1;
        $this->enable_get_customer            = 1;
        $this->enable_get_items_csv           = 1;
        $this->enable_get_items               = 1;
        $this->enable_get_categories_csv      = 1;
        $this->enable_get_categories          = 1;
        $this->enable_get_reviews_csv         = 1;
        $this->enable_get_reviews             = 1;
        $this->enable_get_pages_csv           = 0;
        $this->enable_get_log_file            = 1;
        $this->enable_mobile_website          = 1;
        $this->enable_cron                    = 1;
        $this->enable_clear_log_file          = 1;
        $this->enable_clear_cache             = 1;
        $this->enable_get_settings            = 1;
        $this->enable_check_cart              = 1;
        $this->enable_check_stock             = 1;
        $this->enable_register_customer       = 1;
        $this->shop_is_active                 = 1;
        $this->encoding                       = 'ISO-8859-15';
        
        // default filenames if no language was selected
        $this->items_csv_filename      = 'items-undefined.csv';
        $this->categories_csv_filename = 'categories-undefined.csv';
        $this->reviews_csv_filename    = 'reviews-undefined.csv';
        $this->pages_csv_filename      = 'pages-undefined.csv';
        
        $this->access_log_filename  = 'access-undefined.log';
        $this->request_log_filename = 'request-undefined.log';
        $this->error_log_filename   = 'error-undefined.log';
        $this->debug_log_filename   = 'debug-undefined.log';
        
        $this->redirect_keyword_cache_filename
            = 'redirect_keywords-undefined.txt';
        $this->redirect_skip_keyword_cache_filename
            = 'skip_redirect_keywords-undefined.txt';
        
        // initialize plugin specific stuff
        $this->redirect_languages = array();
        
        $this->shipping = '';
        
        $this->tax_zone_id = 5;
        
        $this->customer_price_group = 0;
        
        $this->order_status_open             = 1;
        $this->order_status_shipped          = 3;
        $this->order_status_shipping_blocked = 1;
        
        $this->order_status_canceled = 0;
        
        $this->reverse_categories_sort_order = false;
        $this->reverse_items_sort_order      = false;
        $this->export_description_type
                                             = SHOPGATE_SETTING_EXPORT_DESCRIPTION;
        
        $this->shopgate_table_version = '';
        
        $this->maximum_category_export_depth       = '';
        $this->send_order_confirmation_mail        = false;
        $this->export_new_products_category        = 0;
        $this->export_new_products_category_id     = 999;
        $this->export_special_products_category    = 0;
        $this->export_special_products_category_id = 1000;
        $this->export_option_as_input_field        = '';
        $this->payment_name_mapping                = '';
        $this->supported_fields_check_cart         = array(
            'customer', 'external_coupons', 'items', 'shipping_methods'
        );
    }
    
    protected function validateCustom(array $fieldList = array())
    {
        $failedFields = array();
        
        foreach ($fieldList as $field) {
            switch ($field) {
                case 'redirect_languages':
                    // at least one redirect language must be selected
                    if (empty($this->redirect_languages)) {
                        $failedFields[] = $field;
                    }
                    break;
            }
        }
        
        return $failedFields;
    }
    
    
    /**
     * @return mixed
     */
    public function getRedirectLanguages()
    {
        return $this->redirect_languages;
    }
    
    /**
     * @return mixed
     */
    public function getShipping()
    {
        return $this->shipping;
    }
    
    /**
     * @return mixed
     */
    public function getTaxZoneId()
    {
        return $this->tax_zone_id;
    }
    
    /**
     * @return mixed
     */
    public function getCustomerPriceGroup()
    {
        return $this->customer_price_group;
    }
    
    /**
     * @return mixed
     */
    public function getOrderStatusOpen()
    {
        return $this->order_status_open;
    }
    
    /**
     * @return mixed
     */
    public function getOrderStatusShipped()
    {
        return $this->order_status_shipped;
    }
    
    /**
     * @return mixed
     */
    public function getOrderStatusShippingBlocked()
    {
        return $this->order_status_shipping_blocked;
    }
    
    /**
     * @return mixed
     */
    public function getOrderStatusCanceled()
    {
        return $this->order_status_canceled;
    }
    
    
    /**
     * @return mixed
     */
    public function getReverseCategoriesSortOrder()
    {
        return $this->reverse_categories_sort_order;
    }
    
    /**
     * @return mixed
     */
    public function getReverseItemsSortOrder()
    {
        return $this->reverse_items_sort_order;
    }
    
    /**
     * @return mixed
     */
    public function getExportDescriptionType()
    {
        return $this->export_description_type;
    }
    
    /**
     * @return mixed
     */
    public function getShopgateTableVersion()
    {
        return $this->shopgate_table_version;
    }
    
    /**
     * @return mixed
     */
    public function getMaximumCategoryExportDepth()
    {
        return $this->maximum_category_export_depth;
    }
    
    /**
     * @return mixed
     */
    public function getSendOrderConfirmationMail()
    {
        return $this->send_order_confirmation_mail;
    }
    
    /**
     * @return mixed
     */
    public function getExportNewProductsCategory()
    {
        return $this->export_new_products_category;
    }
    
    /**
     * @return mixed
     */
    public function getExportNewProductsCategoryId()
    {
        return $this->export_new_products_category_id;
    }
    
    /**
     * @return mixed
     */
    public function getExportSpecialProductsCategory()
    {
        return $this->export_special_products_category;
    }
    
    /**
     * @return mixed
     */
    public function getExportSpecialProductsCategoryId()
    {
        return $this->export_special_products_category_id;
    }

    /**
     * @return string
     */
    public function getExportOptionAsInputField()
    {
        return $this->export_option_as_input_field;
    }

    /**
     * @return string
     */
    public function getPaymentNameMapping()
    {
        return $this->payment_name_mapping;
    }
    
    /**
     * @param $value
     */
    public function setRedirectLanguages($value)
    {
        $this->redirect_languages = $value;
    }
    
    
    /**
     * @param $value
     */
    public function setShipping($value)
    {
        $this->shipping = $value;
    }
    
    /**
     * @param $value
     */
    public function setTaxZoneId($value)
    {
        $this->tax_zone_id = $value;
    }
    
    /**
     * @param $value
     */
    public function setCustomerPriceGroup($value)
    {
        $this->customer_price_group = $value;
    }
    
    
    /**
     * @param $value
     */
    public function setOrderStatusOpen($value)
    {
        $this->order_status_open = $value;
    }
    
    /**
     * @param $value
     */
    public function setOrderStatusShipped($value)
    {
        $this->order_status_shipped = $value;
    }
    
    /**
     * @param $value
     */
    public function setOrderStatusShippingBlocked($value)
    {
        $this->order_status_shipping_blocked = $value;
    }
    
    /**
     * @param $value
     */
    public function setOrderStatusCanceled($value)
    {
        $this->order_status_canceled = $value;
    }
    
    /**
     * @param $value
     */
    public function setReverseCategoriesSortOrder($value)
    {
        $this->reverse_categories_sort_order = $value;
    }
    
    /**
     * @param $value
     */
    public function setReverseItemsSortOrder($value)
    {
        $this->reverse_items_sort_order = $value;
    }
    
    /**
     * @param $value
     */
    public function setExportDescriptionType($value)
    {
        $this->export_description_type = $value;
    }
    
    /**
     * @param $value
     */
    public function setShopgateTableVersion($value)
    {
        $this->shopgate_table_version = $value;
    }
    
    /**
     * @param $value
     */
    public function setMaximumCategoryExportDepth($value)
    {
        $this->maximum_category_export_depth = $value;
    }
    
    /**
     * @param $value
     */
    public function setSendOrderConfirmationMail($value)
    {
        $this->send_order_confirmation_mail = $value;
    }
    
    /**
     * @param $value
     */
    public function setExportNewProductsCategory($value)
    {
        $this->export_new_products_category = $value;
    }
    
    /**
     * @param $value
     */
    public function setExportNewProductsCategoryId($value)
    {
        $this->export_new_products_category_id = $value;
    }
    
    /**
     * @param $value
     */
    public function setExportSpecialProductsCategory($value)
    {
        $this->export_special_products_category = $value;
    }
    
    /**
     * @param $value
     */
    public function setExportSpecialProductsCategoryId($value)
    {
        $this->export_special_products_category_id = $value;
    }
    
    /**
     * @param string $value
     */
    public function setExportOptionAsInputField($value)
    {
        $this->export_option_as_input_field = $value;
    }

    /**
     * @param string $value
     */
    public function setPaymentNameMapping($value)
    {
        $this->payment_name_mapping = $value;
    }
}
