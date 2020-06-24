<?php
  /* --------------------------------------------------------------
   $Id: cookie_consent.js.php $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2019 [www.modified-shop.org]
   --------------------------------------------------------------
   Copyright (c) 2019, Andreas Guder [info@andreas-guder.de]     
   --------------------------------------------------------------   
   Released under the GNU General Public License
   --------------------------------------------------------------*/

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

if (!class_exists('cookie_consent')) {
  class cookie_consent {
    var $code, $title, $description, $enabled;

    function __construct() {
      $this->code = 'cookie_consent';
      $this->title = 'Cookie Consent';
      $this->description = 'Cookie Consent-Modul based on oil.js';
      if (defined('MODULE_COOKIE_CONSENT_STATUS') && strtolower(MODULE_COOKIE_CONSENT_STATUS) == 'true') {
        $this->description .= '<br /><br />'.MODULE_COOKIE_CONSENT_EXTENDED_DESCRIPTION;
      }
      $this->description .= '<br /><br /><small>'.MODULE_COOKIE_CONSENT_MORE_INFO.' <a href="https://github.com/as-ideas/oil" target="_blank">https://github.com/as-ideas/oil</a></small>';
      $this->enabled = ((MODULE_COOKIE_CONSENT_STATUS == 'true') ? true : false);
    }

    function process($file) {
    
    }

    function display() {
      return array('text' => MODULE_COOKIE_CONSENT_SET_READABLE_COOKIE_DETAIL.'<br /><br /><br />'.
        xtc_button(BUTTON_REVIEW_APPROVE) . '&nbsp;' .
        xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module='.$this->code))
      );
    }

    function check() {
      if(!isset($this->_check)) {
        $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_COOKIE_CONSENT_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      
      if (!defined('MODULE_COOKIE_CONSENT_STATUS')) {
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_COOKIE_CONSENT_STATUS', 'true',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
      }
      if (!defined('MODULE_COOKIE_CONSENT_VERSION')) {
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) VALUES ('MODULE_COOKIE_CONSENT_VERSION', '0',  '6', '2', now())");
      }
      if (!defined('MODULE_COOKIE_CONSENT_LAST_UPDATE')) {
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) VALUES ('MODULE_COOKIE_CONSENT_LAST_UPDATE', 'now()',  '6', '3', now())");
      }
      if (!defined('MODULE_COOKIE_CONSENT_SET_READABLE_COOKIE')) {
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_COOKIE_CONSENT_SET_READABLE_COOKIE', 'false',  '6', '4', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
      }
      
      
      // load language-data
      $languages = array();
      $qr = xtc_db_query("SELECT `languages_id`,`language_charset` FROM " . TABLE_LANGUAGES . " WHERE `languages_id` IN (1,2)");
      while ($row = xtc_db_fetch_array($qr)) {
        $languages[$row['languages_id']] = $row['language_charset'];
      }
      
      $q = "CREATE TABLE IF NOT EXISTS `".TABLE_COOKIE_CONSENT_CATEGORIES."` (
        `categories_id` int(11) NOT NULL,
        `categories_name` varchar(128) NOT NULL,
        `categories_description` text NOT NULL,
        `sort_order` int(11) NOT NULL DEFAULT '0',
        `languages_id` int(11) NOT NULL,
        `last_modified` datetime DEFAULT NULL,
        `date_added` datetime NOT NULL,
        `fixed` tinyint(1) NOT NULL DEFAULT '0',
        PRIMARY KEY (`categories_id`,`languages_id`),
        KEY `idx_cookie_multi` (`languages_id`,`categories_id`,`sort_order`)
      ) ENGINE=MyISAM";
      xtc_db_query($q);
      
      $defined_categories = array();
      // Essential
      $defined_categories[] = array(
        'id'        => 1,
        'name'      => array(
          1 => 'Essential',
          2 => 'Notwendig'
        ),
        'desc'      => array(
          1 => 'Technically essential Cookies contribute to ensuring the basic functionalities of the website.',
          2 => 'Technisch notwendige Cookies tragen dazu bei, grundlegende Funktionalitäten der Website zu gewährleisten.'
        ),
        'sort_order'=> 1,
        'fixed'     => 1
      );
      // Functional
      $defined_categories[] = array(
        'id'        => 2,
        'name'      => array(
          1 => 'Functional',
          2 => 'Funktional'
        ),
        'desc'      => array(
          1 => 'Functional Cookies help make the user experience more pleasant.',
          2 => 'Funktionale Cookies helfen, das Benutzererlebnis noch angenehmer zu machen.'
        ),
        'sort_order'=> 2,
        'fixed'     => 1
      );
      // Statistics
      $defined_categories[] = array(
        'id'        => 3,
        'name'      => array(
          1 => 'Statistics',
          2 => 'Statistik'
        ),
        'desc'      => array(
          1 => 'Statistical Cookies help us understand how our website is being used and how we can improve it.',
          2 => 'Statistik Cookies helfen uns zu verstehen, wie unsere Website verwendet wird und wie wir sie verbessern können.'
        ),
        'sort_order'=> 3,
        'fixed'     => 1
      );
      // Marketing
      $defined_categories[] = array(
        'id'        => 4,
        'name'      => array(
          1 => 'Marketing',
          2 => 'Marketing'
        ),
        'desc'      => array(
          1 => 'Marketing Cookies help us adjust our marketing measures to the individual interests of our visitors.',
          2 => 'Marketing Cookies helfen dabei, unsere Werbemaßnahmen auf die individuellen Interessen unserer Besucher abzustimmen.'
        ),
        'sort_order'=> 4,
        'fixed'     => 1
      );
      
      foreach ($defined_categories as $row) {
        if (array_key_exists(1, $languages)) {
          $sql_data = array(
            'categories_id'       => $row['id'],
            'categories_name'     => decode_utf8($row['name'][1], $languages[1]['language_charset']),
            'categories_description' => decode_utf8($row['desc'][1], $languages[1]['language_charset']),
            'sort_order'          => $row['sort_order'],
            'languages_id'        => 1,
            'date_added'          => 'now()',
            'fixed'               => $row['fixed']
          );
          xtc_db_perform(TABLE_COOKIE_CONSENT_CATEGORIES, $sql_data);
        }
        if (array_key_exists(2, $languages)) {
          $sql_data = array(
            'categories_id'       => $row['id'],
            'categories_name'     => decode_utf8($row['name'][2], $languages[2]['language_charset']),
            'categories_description' => decode_utf8($row['desc'][2], $languages[2]['language_charset']),
            'sort_order'          => $row['sort_order'],
            'languages_id'        => 2,
            'date_added'          => 'now()',
            'fixed'               => $row['fixed']
          );
          xtc_db_perform(TABLE_COOKIE_CONSENT_CATEGORIES, $sql_data);
        }
      }
      
      $q = "CREATE TABLE IF NOT EXISTS `".TABLE_COOKIE_CONSENT_COOKIES."` (
        `cookies_id` int(11) NOT NULL,
        `categories_id` int(11) NOT NULL,
        `cookies_name` varchar(128) NOT NULL,
        `cookies_description` text NOT NULL,
        `cookies_list` varchar(512) NOT NULL,
        `sort_order` int(11) NOT NULL DEFAULT '0',
        `languages_id` int(11) NOT NULL,
        `status` int(1) NOT NULL DEFAULT '1',
        `last_modified` datetime DEFAULT NULL,
        `date_added` datetime NOT NULL,
        `fixed` tinyint(1) NOT NULL DEFAULT '0',
        PRIMARY KEY (`cookies_id`,`languages_id`),
        KEY `idx_categories_id` (`categories_id`),
        KEY `idx_categories_multi` (`languages_id`,`categories_id`,`sort_order`)
      ) ENGINE=MyISAM";
      xtc_db_query($q);
      
      $defined_cookies = array();
      // session cookie
      $defined_cookies[] = array(
        'id'        => 1,
        'category'  => 1,
        'name'      => array(
          1 => 'Session Cookies',
          2 => 'Session Cookies'
        ),
        'desc'      => array(
          1 => 'A session cookie stores information that assigns online activities to a single browser session. The session cookie is usually deleted when the browser is closed.',
          2 => 'Ein Session Cookie speichert Informationen, die Onlineaktivitäten einer einzelnen Browser-Sitzung zuordnen. Der Session Cookie wird in der Regel beim Schließen des Browsers wieder gelöscht.'
        ),
        'cookies'   => 'MODsid,PHPSESSID',
        'sort_order'=> 1,
        'status'    => 1,
        'fixed'     => 1
      );
      // oil cookie
      $defined_cookies[] = array(
        'id'        => 2,
        'category'  => 1,
        'name'      => array(
          1 => 'Cookies Setting',
          2 => 'Cookie-Entscheidung'
        ),
        'desc'      => array(
          1 => 'Necessary to save your cookie settings.',
          2 => 'Notwendig, um Ihre Cookie-Entscheidung zu speichern.'
        ),
        'cookies'   => 'oil_data',
        'sort_order'=> 2,
        'status'    => 1,
        'fixed'     => 1
      );
      // Google Analytics
      $defined_cookies[] = array(
        'id'        => 3,
        'category'  => 3,
        'name'      => array(
          1 => 'Google Analytics',
          2 => 'Google Analytics'
        ),
        'desc'      => array(
          1 => "_ga,_gid: Contains a randomly generated user ID. Using this ID, Google Analytics can recognize returning users on this website and combine the data from previous visits.  _gat: Certain data is only sent to Google Analytics once a minute. The cookie has a lifespan of one minute. As long as it is set, certain data transfers are prevented.",
          2 => "_ga,_gid: Enthält eine zufallsgenerierte User-ID. Anhand dieser ID kann Google Analytics wiederkehrende User auf dieser Website wiedererkennen und die Daten von früheren Besuchen zusammenführen.  _gat: Bestimmte Daten werden nur maximal einmal pro Minute an Google Analytics gesendet. Das Cookie hat eine Lebensdauer von einer Minute. Solange es gesetzt ist, werden bestimmte Datenübertragungen unterbunden."
        ),
        'cookies'   => '_ga,_gid,_gat',
        'sort_order'=> 1,
        'status'    => 0,
        'fixed'     => 1
      );
      // Google Analytics Classic
      $defined_cookies[] = array(
        'id'        => 4,
        'category'  => 3,
        'name'      => array(
          1 => 'Google Analytics Classic',
          2 => 'Google Analytics Classic'
        ),
        'desc'      => array(
          1 => "__utma: Contains a randomly generated user ID. Using this ID, Google Analytics can recognize returning users on this website and combine the data from previous visits. __utmb: Contains a randomly generated session ID. This cookie has a storage time of only 30 minutes. All actions that a user takes on the website within this period of time are summarized in Google Analytics in a \"visit\" (a session). __utmt: Certain data is only sent to Google Analytics once every 10 minutes. The cookie has a lifespan of 10 minutes. As long as it is set, certain data transfers are prevented. __utmz: This cookie stores information about the source from which a user last came to the website.",
          2 => "__utma: Enthält eine zufallsgenerierte User-ID. Anhand dieser ID kann Google Analytics wiederkehrende User auf dieser Website wiedererkennen und die Daten von früheren Besuchen zusammenführen. __utmb: Enthält eine zufallsgenerierte Session-ID. Dieses Cookie hat eine Speicherdauer von nur 30 Minuten. Alle Aktionen, die ein User innerhalb dieser Zeitspanne auf der Website tätigt, werden in Google Analytics zu einem \"Besuch\" (einer Session) zusammengefasst. __utmt: Bestimmte Daten werden nur maximal einmal pro 10 Minuten an Google Analytics gesendet. Das Cookie hat eine Lebensdauer von 10 Minuten. Solange es gesetzt ist, werden bestimmte Datenübertragungen unterbunden. __utmz: Dieses Cookie speichert Informationen darüber, aus welcher Quelle ein User zuletzt auf die Website gekommen ist."
        ),
        'cookies'   => '__utma,__utmb,__utmc,__utmt,__utmz,__utmv,__utmx',
        'sort_order'=> 2,
        'status'    => 0,
        'fixed'     => 1
      );
      // PayPal
      $defined_cookies[] = array(
        'id'        => 5,
        'category'  => 1,
        'name'      => array(
          1 => 'PayPal',
          2 => 'PayPal'
        ),
        'desc'      => array(
          1 => 'Is required for payment via PayPal.',
          2 => 'Wird für die Zahlung via PayPal benötigt.'
        ),
        'cookies'   => 'paypal',
        'sort_order'=> 3,
        'status'    => 1,
        'fixed'     => 1
      );
      // Facebook
      $defined_cookies[] = array(
        'id'        => 6,
        'category'  => 4,
        'name'      => array(
          1 => 'Facebook',
          2 => 'Facebook'
        ),
        'desc'      => array(
          1 => 'Facebook',
          2 => 'Facebook'
        ),
        'cookies'   => '_fbp',
        'sort_order'=> 1,
        'status'    => 0,
        'fixed'     => 1
      );
      // Matomo (ehem. Piwik)
      $defined_cookies[] = array(
        'id'        => 7,
        'category'  => 3,
        'name'      => array(
          1 => 'Matomo (ehem. Piwik)',
          2 => 'Matomo (ehem. Piwik)'
        ),
        'desc'      => array(
          1 => 'analyses and statistic',
          2 => 'Besucher-Analyse und Statistik'
        ),
        'cookies'   => 'pk_ref,_pk_cvar,_pk_id,_pk_ses',
        'sort_order'=> 3,
        'status'    => 0,
        'fixed'     => 1
      );
      // Google Conversion
      $defined_cookies[] = array(
        'id'        => 8,
        'category'  => 4,
        'name'      => array(
          1 => 'Google Conversion',
          2 => 'Google Conversion'
        ),
        'desc'      => array(
          1 => '',
          2 => ''
        ),
        'cookies'   => 'IDE,__gcl',
        'sort_order'=> 2,
        'status'    => 0,
        'fixed'     => 1
      );
      // Readable Cookie
      $defined_cookies[] = array(
        'id'        => 9,
        'category'  => 1,
        'name'      => array(
          1 => 'readable Cookies Setting',
          2 => 'lesbare Cookie-Entscheidung'
        ),
        'desc'      => array(
          1 => 'Necessary for external services like Google Tag Manager to get your cookie-decision',
          2 => 'Benögtigt um externen Diensten wie dem Google-Tag-Manger Ihre Cookie-Entscheidung mitteilen zu können.'
        ),
        'cookies'   => 'MODOilTrack',
        'sort_order'=> 4,
        'status'    => 0,
        'fixed'     => 1
      );
      
      foreach ($defined_cookies as $row) {
        if (array_key_exists(1, $languages)) {
          $sql_data = array(
            'cookies_id'          => $row['id'],
            'categories_id'       => $row['category'],
            'cookies_name'        => decode_utf8($row['name'][1], $languages[1]['language_charset']),
            'cookies_description' => decode_utf8($row['desc'][1], $languages[1]['language_charset']),
            'cookies_list'        => $row['cookies'],
            'sort_order'          => $row['sort_order'],
            'languages_id'        => 1,
            'status'              => $row['status'],
            'date_added'          => 'now()',
            'fixed'               => $row['fixed']
          );
          xtc_db_perform(TABLE_COOKIE_CONSENT_COOKIES, $sql_data);
        }
        if (array_key_exists(2, $languages)) {
          $sql_data = array(
            'cookies_id'          => $row['id'],
            'categories_id'       => $row['category'],
            'cookies_name'        => decode_utf8($row['name'][2], $languages[2]['language_charset']),
            'cookies_description' => decode_utf8($row['desc'][2], $languages[2]['language_charset']),
            'cookies_list'        => $row['cookies'],
            'sort_order'          => $row['sort_order'],
            'languages_id'        => 2,
            'status'              => $row['status'],
            'date_added'          => 'now()',
            'fixed'               => $row['fixed']
          );
          xtc_db_perform(TABLE_COOKIE_CONSENT_COOKIES, $sql_data);
        }
      }
      
      $query_result = xtc_db_query("SHOW COLUMNS FROM `" . TABLE_ADMIN_ACCESS . "`");
      $db_table_rows = array();
      while ($row = xtc_db_fetch_array($query_result)) {
        $db_table_rows[] = $row['Field'];
      }
      
      if (!in_array('cookie_consent', $db_table_rows)) {
        xtc_db_query("ALTER TABLE `" . TABLE_ADMIN_ACCESS . "` ADD `cookie_consent` INT(1) NOT NULL DEFAULT 0");
        xtc_db_query("UPDATE `" . TABLE_ADMIN_ACCESS . "` SET `cookie_consent` = 1 WHERE `customers_id` = 1");
        xtc_db_query("UPDATE `" . TABLE_ADMIN_ACCESS . "` SET `cookie_consent` = 1 WHERE `customers_id` = ".$_SESSION['customer_id']);
        xtc_db_query("UPDATE `" . TABLE_ADMIN_ACCESS . "` SET `cookie_consent` = 8 WHERE `customers_id`='groups'");
      }

    }

    function remove() {
      xtc_db_query("DROP TABLE `".TABLE_COOKIE_CONSENT_COOKIES."`");
      xtc_db_query("DROP TABLE `".TABLE_COOKIE_CONSENT_CATEGORIES."`");
      xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE `configuration_key` LIKE 'MODULE_COOKIE_CONSENT_%'");
      
      $query_result = xtc_db_query("SHOW COLUMNS FROM `" . TABLE_ADMIN_ACCESS . "`");
      $db_table_rows = array();
      while ($row = xtc_db_fetch_array($query_result)) {
        $db_table_rows[] = $row['Field'];
      }
      if (in_array('cookie_consent', $db_table_rows)) {
        xtc_db_query("ALTER TABLE `".TABLE_ADMIN_ACCESS."` DROP `cookie_consent`;");
      }
    }

    function keys() {
      return array('MODULE_COOKIE_CONSENT_STATUS','MODULE_COOKIE_CONSENT_SET_READABLE_COOKIE');
    }
  }
}
