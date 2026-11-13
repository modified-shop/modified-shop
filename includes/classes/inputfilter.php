<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

class Inputfilter {
    private $params = false;
    private $active_content_removed = false;
    private $active_content_unfiltered = false;

    // admin fields that the storefront prints as HTML
    private $content_keys = array('products_name',
                                  'products_heading_title',
                                  'products_description',
                                  'products_short_description',
                                  'products_order_description',
                                  'categories_name',
                                  'categories_heading_title',
                                  'categories_description',
                                  'categories_short_description',
                                  'manufacturers_name',
                                  'manufacturers_title',
                                  'manufacturers_description',
                                  'manufacturers_short_description',
                                  'manufacturers_add_description',
                                  'content_title',
                                  'content_heading',
                                  'content_text',
                                  'cont_title',
                                  'file_comment',
                                  'html_text',
                                  'offline_msg',
                                  'cookies_name',
                                  'cookies_description',
                                  'coupon_desc',
                                  'options_name',
                                  'options_description',
                                  'values_name',
                                  'values_description',
                                  'products_options_name',
                                  'products_options_values_name',
                                  'products_model',
                                  'products_manufacturers_model',
                                  'products_vpe_name',
                                  'shipping_status_name',
                                  'orders_status_name',
                                  'cross_sell_group_name',
                                  );

    public function __construct()
    {
        $this->params = array();
    }

    public function removeActiveContent($value)
    {
        require_once(DIR_FS_CATALOG.'inc/xtc_remove_active_content.inc.php');

        if (!xtc_remove_active_content_available()) {
            // the value stays as it is, the admin gets an error
            $this->active_content_unfiltered = true;
            return $value;
        }

        $removed = false;
        $value = xtc_remove_active_content($value, $removed);
        if ($removed === true) {
            $this->active_content_removed = true;
        }

        return $value;
    }

    public function activeContentRemoved()
    {
        return $this->active_content_removed;
    }

    public function activeContentUnfiltered()
    {
        return $this->active_content_unfiltered;
    }

    public function validate($source)
    {
        $this->params = $source;
        $this->inputValidate();
        
        return $this->params;
    }

    public function removeTags($value)
    {
        return strip_tags($value) == $value ? $value : '';
        //return preg_replace('/<[^>]*>/', ' ', $value) == $value ? $value : ''; //alternative zu stip_tags
    }

    public function validateCPath($value)
    {
        return preg_replace('/[^0-9_]/','',$value);
    }

    public function validateNumeric($value)
    {
        return preg_replace('/[^0-9]/','',$value);
    }

    public function validateSigns($value)
    {
        return preg_replace('/[^0-9a-zA-Z_-]/','',$value);
    }

    public function validateSessionID($value)
    {
        return preg_replace('/[^0-9a-zA-Z]/','',$value);
    }

    public function validatePrice($value)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->validatePrice($v);
            }
        } else {
            $value = str_replace(',', '.', preg_replace('/[^0-9,.%]/','',$value));
        }

        return $value;
    }
    
    private function inputValidate()
    {
        if (is_array($this->params)) {
            foreach($this->params as $key => $value ) {
                switch($key) {
                  //remove tags
                  case 'search':
                  case 'search_email':                      
                  case 'searchoption':
                  case 'search_optionsname':
                  case 'product_search':
                      $this->params[$key] = $this->removeTags($value);
                      break;
                  //numeric
                  case 'page':
                  case 'value_page':
                  case 'option_page':
                  case 'option_id':
                  case 'value_id':
                  case 'oID':
                  case 'pID':
                  case 'gID':
                  case 'coID':
                  case 'tID':
                  case 'zID':
                  case 'cID':
                  case 'lID':
                  case 'ID':
                  case 'mID':
                  case 'rID':
                  case 'sID':
                  case 'bID':
                  case 'current_product_id':
                      $this->params[$key] = $this->validateNumeric($value);
                      break;
                  //0-9a-zA-Z _ -
                  case 'action':
                  case 'last_action':
                      $this->params[$key] = $this->validateSigns($value);
                      break;
                  //cPath
                  case 'cPath':
                  case 'cpath':
                      $this->params[$key] = $this->validateCPath($value);
                      break;
                  case 'info':
                  case 'MODsid':
                      $this->params[$key] = $this->validateSessionID($value);
                      break;
                  default:
                    //price
                    if (defined('RUN_MODE_ADMIN')) {
                      $keys = array('products_vpe_value',
                                    'products_uvp',
                                    'products_discount_allowed',
                                    'customers_status_min_order',
                                    'customers_status_max_order',
                                    'customers_status_discount',
                                    'customers_status_ot_discount',
                                    'tax_rate',
                                    'coupon_amount',
                                    'coupon_min_order',
                                    'NEW_SIGNUP_GIFT_VOUCHER_AMOUNT',
                                    );
                      if (in_array($key, $keys) ||
                          substr($key, -6) == '_price' ||
                          substr($key, -7) == '_weight' ||
                          substr($key, 0, 14) == 'products_price' ||
                          substr($key, 0, 14) == 'specials_price' ||
                          substr($key, 0, 16) == 'products_staffel'
                          ) 
                      {
                        $this->params[$key] = $this->validatePrice($value);
                      }

                      //active content
                      if ((!defined('ADMIN_ALLOW_SCRIPT_CONTENT') || !in_array(ADMIN_ALLOW_SCRIPT_CONTENT, array(true, 'true', 1, '1'), true))
                          && in_array($key, $this->content_keys)
                          )
                      {
                        $this->params[$key] = $this->removeActiveContent($value);
                      }
                    }
                  break;
                }
            }
        }
    }
}
?>