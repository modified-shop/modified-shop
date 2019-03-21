<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

class shopvote
{
    var $code, $title, $description, $enabled;

    function __construct() 
    {
        $this->code = 'shopvote';
        $this->title = MODULE_SHOPVOTE_TEXT_TITLE;
        $this->description = MODULE_SHOPVOTE_TEXT_DESCRIPTION;
        $this->sort_order = ((defined('MODULE_SHOPVOTE_SORT_ORDER')) ? MODULE_SHOPVOTE_SORT_ORDER : '');
        $this->enabled = ((defined('MODULE_SHOPVOTE_STATUS') && MODULE_SHOPVOTE_STATUS == 'true') ? true : false);
    }

    function process($file) 
    {
        if (is_array($_POST['configuration'])
            && count($_POST['configuration']) > 0
            )
        {
          foreach ($_POST['configuration'] as $key => $value) {
            $value = is_array($_POST['configuration'][$key]) ? implode(',', $_POST['configuration'][$key]) : $value;
            $value = str_replace("'", '"', $value);
            
            xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '" . xtc_db_input(encode_htmlentities($value)) . "' WHERE configuration_key = '" . $key . "'");
          }
        }
    }

    function display() 
    {
        return array('text' => '<br>' . xtc_button(BUTTON_SAVE) . '&nbsp;' .
                               xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module='.$this->code))
                     );
    }

    function check() 
    {
        if(!isset($this->_check)) {
          $check_query = xtc_db_query("SELECT configuration_value 
                                         FROM " . TABLE_CONFIGURATION . " 
                                        WHERE configuration_key = 'MODULE_SHOPVOTE_STATUS'");
          $this->_check = xtc_db_num_rows($check_query);
        }
        return $this->_check;
    }

    function install() 
    {
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHOPVOTE_STATUS', 'false',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPPING_EASYREVIEWS', '', '6', '0', 'xtc_cfg_textarea(', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPPING_RATINGSTARS', '', '6', '0', 'xtc_cfg_textarea(', now())");
    }

    function remove()
    {
        xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_SHOPVOTE_%'");
    }

    function keys() 
    {
        return array(
          'MODULE_SHOPVOTE_STATUS',
          'MODULE_SHIPPING_EASYREVIEWS',
          'MODULE_SHIPPING_RATINGSTARS',
        );
    }    
}
?>