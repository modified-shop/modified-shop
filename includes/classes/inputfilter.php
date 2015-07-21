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
    
    public function __construct()
    {
        $this->params = array();
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
                      $this->params[$key] = $this->validateNumeric($value);
                      break;
                  //0-9a-zA-Z _ -
                  case 'action':
                      $this->params[$key] = $this->validateSigns($value);
                      break;
                  //cPath
                  case 'cPath':
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
                    }
                  break;
                }
            }
        }
    }
}
?>