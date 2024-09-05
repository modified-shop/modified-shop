<?PHP
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(freeamount.php,v 1.01 2002/01/24); www.oscommerce.com 
   (c) 2003	 nextcommerce (freeamount.php,v 1.12 2003/08/24); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   selfpickup         	Autor:	sebthom

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

class selfpickup
{
    var $code;
    var $title;
    var $description;
    var $sort_order;
    var $icon;
    var $tax_class;
    var $enabled;
    var $quotes;
    var $_check;

    function __construct()
    {        
        $this->code = 'selfpickup';
        $this->title = MODULE_SHIPPING_SELFPICKUP_TEXT_TITLE;
        $this->description = MODULE_SHIPPING_SELFPICKUP_TEXT_DESCRIPTION;
        $this->icon = '';   // change $this->icon =  DIR_WS_ICONS . 'shipping_ups.gif'; to some freeshipping icon
        $this->tax_class = ((defined('MODULE_SHIPPING_SELFPICKUP_TAX_CLASS')) ? MODULE_SHIPPING_SELFPICKUP_TAX_CLASS : '');
        $this->sort_order = ((defined('MODULE_SHIPPING_SELFPICKUP_SORT_ORDER')) ? MODULE_SHIPPING_SELFPICKUP_SORT_ORDER : '');
        $this->enabled = ((defined('MODULE_SHIPPING_SELFPICKUP_STATUS') && MODULE_SHIPPING_SELFPICKUP_STATUS == 'True') ? true : false);

        if ($this->enabled == true) {
          $check_flag = true;
          if ($address = $this->address()) {
            $check_flag = false;
            if ($address['firstname'] != ''
                && $address['lastname'] != ''
                && $address['street_address'] != ''
                && $address['city'] != ''
                && $address['postcode'] != ''                
                )
            {
              $check_flag = true;
            }
          }
          
          if ($check_flag == false) {
            $this->enabled = false;
          }
        }

        if ($this->check() > 0) {
          if (defined('RUN_MODE_ADMIN')) {
            $this->check_install('MODULE_SHIPPING_SELFPICKUP_FIRSTNAME');
            $this->check_install('MODULE_SHIPPING_SELFPICKUP_LASTNAME');
            $this->check_install('MODULE_SHIPPING_SELFPICKUP_STREET_ADDRESS');
            $this->check_install('MODULE_SHIPPING_SELFPICKUP_POSTCODE');
            $this->check_install('MODULE_SHIPPING_SELFPICKUP_CITY');
          }
          
          if (!defined('MODULE_SHIPPING_SELFPICKUP_TAX_CLASS')) {
            xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_SHIPPING_SELFPICKUP_TAX_CLASS', '0', '6', '0', 'xtc_get_tax_class_title', 'xtc_cfg_pull_down_tax_classes(', now())");
          }
        }
    }

    function quote($method = '')
    {
        global $PHP_SELF;
        
        $address_format = '';
        if (basename($PHP_SELF) != FILENAME_SHOPPING_CART) {
          $address = $this->address();
          if ($address !== false) {
            $address_format = '<span class="address_pickup" style="display:block;margin-top:10px;">'.xtc_address_format($address['format_id'], $address, true, ' ', '<br>').'</span>';
          }
        }
        
        $this->quotes = array(
            'id' => $this->code,
            'module' => MODULE_SHIPPING_SELFPICKUP_TEXT_TITLE
        );

        $this->quotes['methods'] = array(array(
            'id'    => $this->code,
            'title' => MODULE_SHIPPING_SELFPICKUP_TEXT_WAY.$address_format,
            'cost'  => 0
        ));

        if(xtc_not_null($this->icon))
        {
            $this->quotes['icon'] = xtc_image($this->icon, $this->title);
        }

        return $this->quotes;
    }
    
    function ignore_cheapest()
    {
        return true;
    }

    function display_free()
    {
        return true;
    }
    
    function address()
    {
        $address = false;
        
        if (defined('MODULE_SHIPPING_SELFPICKUP_COUNTRY')
            && (int)MODULE_SHIPPING_SELFPICKUP_COUNTRY > 0
            )
        {
          $country_query =  xtc_db_query("SELECT *
                                            FROM ".TABLE_COUNTRIES." 
                                           WHERE countries_id = '".(int)MODULE_SHIPPING_SELFPICKUP_COUNTRY."'");
          $country = xtc_db_fetch_array($country_query);
        
          $address = array(
            'gender' => '',
            'firstname' => MODULE_SHIPPING_SELFPICKUP_FIRSTNAME,
            'lastname' => MODULE_SHIPPING_SELFPICKUP_LASTNAME,
            'company' => MODULE_SHIPPING_SELFPICKUP_COMPANY,
            'street_address' => MODULE_SHIPPING_SELFPICKUP_STREET_ADDRESS,
            'suburb' => MODULE_SHIPPING_SELFPICKUP_SUBURB,
            'city' => MODULE_SHIPPING_SELFPICKUP_CITY,
            'postcode' => MODULE_SHIPPING_SELFPICKUP_POSTCODE,
            'zone_id' => -1,
            'country' => array(
              'id' => $country['countries_id'],
              'title' => $country['countries_name'],
              'iso_code_2' => $country['countries_iso_code_2'],
              'iso_code_3' => $country['countries_iso_code_3'],
            ),
            'country_id' => $country['countries_id'],
            'format_id' => $country['address_format_id'],
          );
        }
        
        return $address;
    }
    
    function session($method, $module, $quote)
    {
        $_SESSION['shipping']['title'] = $quote[0]['module'].((trim(MODULE_SHIPPING_SELFPICKUP_TEXT_WAY) != '') ? ' ('.MODULE_SHIPPING_SELFPICKUP_TEXT_WAY.')' : '');
    }
    
    function check()
    {
      if (!isset($this->_check)) {
        if (defined('MODULE_SHIPPING_SELFPICKUP_STATUS')) {
          $this->_check = true;
        } else {
          $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_SELFPICKUP_STATUS'");
          $this->_check = xtc_db_num_rows($check_query);
        }
      }
      return $this->_check;
    }

    function install() 
    {
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SHIPPING_SELFPICKUP_STATUS', 'True', '6', '7', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_SELFPICKUP_ALLOWED', '', '6', '0', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_SELFPICKUP_SORT_ORDER', '0', '6', '4', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, date_added) values ('MODULE_SHIPPING_SELFPICKUP_FIRSTNAME', '', '6', '4', 'xtc_cfg_check_not_empty', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, date_added) values ('MODULE_SHIPPING_SELFPICKUP_LASTNAME', '', '6', '4', 'xtc_cfg_check_not_empty', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_SELFPICKUP_COMPANY', '', '6', '4', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_SELFPICKUP_SUBURB', '', '6', '4', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, date_added) values ('MODULE_SHIPPING_SELFPICKUP_STREET_ADDRESS', '', '6', '4', 'xtc_cfg_check_not_empty', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, date_added) values ('MODULE_SHIPPING_SELFPICKUP_POSTCODE', '', '6', '4', 'xtc_cfg_check_not_empty', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, date_added) values ('MODULE_SHIPPING_SELFPICKUP_CITY', '', '6', '4', 'xtc_cfg_check_not_empty', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_SHIPPING_SELFPICKUP_COUNTRY', '".STORE_COUNTRY."', '6', '7', 'xtc_get_country_name', 'xtc_cfg_pull_down_country_list(', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_SHIPPING_SELFPICKUP_TAX_CLASS', '0', '6', '0', 'xtc_get_tax_class_title', 'xtc_cfg_pull_down_tax_classes(', now())");
    }

    function check_install($key) {
      $check_query = xtc_db_query("SELECT *
                                     FROM ".TABLE_CONFIGURATION."
                                    WHERE configuration_key = '".xtc_db_input($key)."'");
      if (xtc_db_num_rows($check_query) > 0) {
        $check = xtc_db_fetch_array($check_query);
        if ($check['use_function'] == '') {
          xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                           SET use_function = 'xtc_cfg_check_not_empty'
                         WHERE configuration_key = '".xtc_db_input($key)."'");
        }
      }
    }
    
    function remove()
    {
        xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key LIKE 'MODULE_SHIPPING_SELFPICKUP_%'");
    }

    function keys()
    {
        return array(
          'MODULE_SHIPPING_SELFPICKUP_STATUS',
          'MODULE_SHIPPING_SELFPICKUP_SORT_ORDER',
          'MODULE_SHIPPING_SELFPICKUP_ALLOWED',
          'MODULE_SHIPPING_SELFPICKUP_COMPANY',
          'MODULE_SHIPPING_SELFPICKUP_FIRSTNAME',
          'MODULE_SHIPPING_SELFPICKUP_LASTNAME',
          'MODULE_SHIPPING_SELFPICKUP_STREET_ADDRESS',
          'MODULE_SHIPPING_SELFPICKUP_SUBURB',
          'MODULE_SHIPPING_SELFPICKUP_POSTCODE',
          'MODULE_SHIPPING_SELFPICKUP_CITY',
          'MODULE_SHIPPING_SELFPICKUP_COUNTRY',
        );
    }
}
