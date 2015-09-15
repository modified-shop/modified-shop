<?PHP
/* -----------------------------------------------------------------------------------------
   $Id: selfpickup.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

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
    var $code, $title, $description, $icon, $enabled;

    function __construct()
    {
        $this->code        = 'selfpickup';
        $this->title       = MODULE_SHIPPING_SELFPICKUP_TEXT_TITLE;
        $this->description = MODULE_SHIPPING_SELFPICKUP_TEXT_DESCRIPTION;
        $this->icon        = '';   // change $this->icon =  DIR_WS_ICONS . 'shipping_ups.gif'; to some freeshipping icon
        $this->sort_order  = MODULE_SHIPPING_SELFPICKUP_SORT_ORDER;
        $this->enabled = ((MODULE_SHIPPING_SELFPICKUP_STATUS == 'True') ? true : false);
    }

    function quote($method = '')
    {
        $this->quotes = array(
            'id' => $this->code,
            'module' => MODULE_SHIPPING_SELFPICKUP_TEXT_TITLE
        );

        $this->quotes['methods'] = array(array(
            'id'    => $this->code,
            'title' => MODULE_SHIPPING_SELFPICKUP_TEXT_WAY,
            'cost'  => 0
        ));

        if(xtc_not_null($this->icon))
        {
            $this->quotes['icon'] = xtc_image($this->icon, $this->title);
        }

        return $this->quotes;
    }

    function check()
    {
        $check = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_SELFPICKUP_STATUS'");
        $check = xtc_db_num_rows($check);

        return $check;
    }

    function install() 
    {
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SHIPPING_SELFPICKUP_STATUS', 'True', '6', '7', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_SELFPICKUP_ALLOWED', '', '6', '0', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_SELFPICKUP_SORT_ORDER', '0', '6', '4', now())");
    }

    function remove()
    {
        xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys()
    {
        return array('MODULE_SHIPPING_SELFPICKUP_STATUS','MODULE_SHIPPING_SELFPICKUP_SORT_ORDER','MODULE_SHIPPING_SELFPICKUP_ALLOWED');
    }
}
?>