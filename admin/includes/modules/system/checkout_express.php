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

class checkout_express
{
    var $code, $title, $description, $enabled;

    function __construct() 
    {
        $this->code = 'checkout_express';
        $this->title = MODULE_CHECKOUT_EXPRESS_TEXT_TITLE;
        $this->description = MODULE_CHECKOUT_EXPRESS_TEXT_DESCRIPTION;
        $this->sort_order = MODULE_CHECKOUT_EXPRESS_SORT_ORDER;
        $this->enabled = ((MODULE_CHECKOUT_EXPRESS_STATUS == 'true') ? true : false);

        if (defined('RUN_MODE_ADMIN') && $this->enabled === true && (!defined('MODULE_CHECKOUT_EXPRESS_CONTENT_INSTALLED') || MODULE_CHECKOUT_EXPRESS_CONTENT_INSTALLED != '1')) {
          $this->description .= ((defined('MODULE_'.strtoupper($this->code).'_DESCRIPTION_INSTALL')) ? constant('MODULE_'.strtoupper($this->code).'_DESCRIPTION_INSTALL').'<a class="button btnbox" style="text-align:center;" onclick="this.blur();" href="' . xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=' . $this->code . '&moduleaction=content') . '">' . MODULE_CHECKOUT_EXPRESS_BUTTON_INSTALL . '</a>' : '');
          if (isset($_GET['moduleaction']) && $_GET['moduleaction'] == 'content') {
            $this->content_install();
            unset($_GET['moduleaction']);
          }
        }
    }

    function process($file) 
    {
        //do nothing
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
          $check_query = xtc_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_CHECKOUT_EXPRESS_STATUS'");
          $this->_check = xtc_db_num_rows($check_query);
        }
        return $this->_check;
    }

    function install() 
    {
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_CHECKOUT_EXPRESS_STATUS', 'true',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_CHECKOUT_EXPRESS_CONTENT', '',  '6', '1', 'xtc_cfg_select_content_module(', 'xtc_cfg_display_content', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_CHECKOUT_EXPRESS_CONTENT_INSTALLED', '0',  '6', '1', now())");
        
        xtc_db_query("CREATE TABLE ".TABLE_CUSTOMERS_CHECKOUT." (
                                     customers_id int(11) NOT NULL,
                                     checkout_shipping VARCHAR(128) NOT NULL,
                                     checkout_shipping_address INT(11) NOT NULL,
                                     checkout_payment VARCHAR(128) NOT NULL,
                                     checkout_payment_address INT(11) NOT NULL,
                                     PRIMARY KEY (customers_id)
                                   )");
    }
    
    function content_install() {
        $content_query = xtc_db_query("SELECT MAX(content_id)+1 as content_id,
                                              MAX(content_group)+1 as content_group
                                         FROM ".TABLE_CONTENT_MANAGER);
        $content = xtc_db_fetch_array($content_query);
        
        $sql_data_array = array(
          'content_id' => $content['content_id'],
          'languages_id' => '1',
          'content_title' => 'My quick purchase',
          'content_heading' => 'My quick purchase',
          'content_text' => '<p>With &bdquo;My Quick purchase&ldquo; you can more easily and above all quickly place your order now.</p><p>You will find the button &bdquo;<strong>Activate my quick purchase</strong>&ldquo; on the detail page of every product below the Cart-Button, where you have to store the desired delivery method, payment method, shipping address and billing address to activate the function for the Quick purchase.<br />Afterwards you will find the button for &bdquo;<strong>My quick purchase</strong>&ldquo; ath the following locations:</p><ul><li>Product detail page</li><li>Shopping cart</li><li>Your Account &raquo; My Orders</li><li>Your Account &raquo; My Orders &raquo; Orders detail page</li></ul><p>To change the default settings for &bdquo;My quick purchase&ldquo;, go to &bdquo;Your Account&ldquo; &raquo; &bdquo;<strong>Display/change my quick purchase settings</strong>&ldquo;.</p>',
          'sort_order' => '0',
          'file_flag' => '1',
          'group_ids' => '',
          'content_status' => '0',
          'content_group' => $content['content_group'],
          'content_meta_title' => '',
          'content_meta_description' => '',
          'content_meta_keywords' => '',
          'content_delete' => '1',
          'content_active' => '1',
          'date_added' => 'now()'
          
        );
        
        xtc_db_perform(TABLE_CONTENT_MANAGER, $sql_data_array);
        
        $sql_data_array['content_id'] ++;
        $sql_data_array['languages_id'] = '2';
        $sql_data_array['content_title'] = 'Mein Schnellkauf';
        $sql_data_array['content_heading'] = 'Mein Schnellkauf';
        $sql_data_array['content_text'] = '<p>Mit &bdquo;Mein Schnellkauf&ldquo; k&ouml;nnen Sie Ihre Bestellung jetzt noch einfacher und vor allem schneller t&auml;tigen.</p><p>Sie finden auf der Detailseite eines jeden Artikels unterhalb des Warenkorb-Buttons die Schaltfl&auml;che &bdquo;<strong>Mein Schnellkauf aktivieren</strong>&ldquo;, wo Sie die f&uuml;r den Schnellkauf gew&uuml;nschte Versandart, Bezahlart, Versandadresse und Rechnungsadresse hinterlegen m&uuml;ssen um die Funktion zu aktivieren.<br />Anschlie&szlig;end finden Sie an den folgenden Stellen im Shop den Button zur Bestellung mit &bdquo;<strong>Mein Schnellkauf</strong>&ldquo;:</p><ul><li>Artikel-Detailseite</li><li>Warenkorb</li><li>Mein Konto &raquo; Meine Bestellungen</li><li>Mein Konto &raquo; Meine Bestellungen &raquo; Detailseite der Bestellung</li></ul><p>Um die Voreinstellungen f&uuml;r &bdquo;Mein Schnellkauf&ldquo; zu &auml;ndern, gehen Sie auf &bdquo;Mein Konto&ldquo; &raquo; &bdquo;<strong>Mein Schnellkauf bearbeiten</strong>&ldquo;.</p>';

        xtc_db_perform(TABLE_CONTENT_MANAGER, $sql_data_array);
        
        xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                         SET configuration_value = '".$content['content_group']."'
                       WHERE configuration_key = 'MODULE_CHECKOUT_EXPRESS_CONTENT'");

        xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                         SET configuration_value = '1'
                       WHERE configuration_key = 'MODULE_CHECKOUT_EXPRESS_CONTENT_INSTALLED'");
    }
    
    function remove()
    {
        xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_CHECKOUT_EXPRESS_%'");
        xtc_db_query("DROP TABLE ".TABLE_CUSTOMERS_CHECKOUT);
    }

    function keys() 
    {
        return array('MODULE_CHECKOUT_EXPRESS_STATUS',
                     'MODULE_CHECKOUT_EXPRESS_CONTENT');
    }    
}
?>