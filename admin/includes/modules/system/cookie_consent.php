<?php
  /* --------------------------------------------------------------
   $Id$

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

    var $code;
    var $title;
    var $description;
    var $sort_order;
    var $enabled;
    var $version;
    var $_check;

    function __construct() {
      $this->version = '1.55';
      $this->code = 'cookie_consent';
      $this->title = 'Cookie Consent';
      $this->description = 'Cookie Consent-Modul based on oil.js';
      if (defined('MODULE_COOKIE_CONSENT_STATUS') && strtolower(MODULE_COOKIE_CONSENT_STATUS) == 'true') {
        $this->description .= '<br /><br />'.MODULE_COOKIE_CONSENT_EXTENDED_DESCRIPTION;
      }
      $this->enabled = ((defined('MODULE_COOKIE_CONSENT_STATUS') && MODULE_COOKIE_CONSENT_STATUS == 'true') ? true : false);
      $this->sort_order = 0;
    }

    function process($file) {
      $this->update_content_status();
    }

    function display() {
      return array('text' => MODULE_COOKIE_CONSENT_SET_READABLE_COOKIE_DETAIL.'<br /><br /><br />'.
        xtc_button(BUTTON_REVIEW_APPROVE) . '&nbsp;' .
        xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module='.$this->code))
      );
    }

    function check() {
      if (!isset($this->_check)) {
        if (defined('MODULE_COOKIE_CONSENT_STATUS')) {
          $this->_check = true;
        } else {
          $check_query = xtc_db_query("SELECT configuration_value 
                                         FROM " . TABLE_CONFIGURATION . " 
                                        WHERE configuration_key = 'MODULE_COOKIE_CONSENT_STATUS'");
          $this->_check = xtc_db_num_rows($check_query);
        }
      }
      return $this->_check;
    }

    function install() {
      // the link in the content boxes needs a content page of its own
      $content_group = $this->create_content();

      if (!defined('MODULE_COOKIE_CONSENT_STATUS')) {
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_COOKIE_CONSENT_STATUS', 'true', '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
      }
      if (!defined('MODULE_COOKIE_CONSENT_VERSION')) {
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_COOKIE_CONSENT_VERSION', '0', '6', '2', now())");
      }
      if (!defined('MODULE_COOKIE_CONSENT_LAST_UPDATE')) {
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_COOKIE_CONSENT_LAST_UPDATE', 'now()', '6', '3', now())");
      }
      if (!defined('MODULE_COOKIE_CONSENT_SET_READABLE_COOKIE')) {
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_COOKIE_CONSENT_SET_READABLE_COOKIE', 'false', '6', '4', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
      }
      if (!defined('MODULE_COOKIE_CONSENT_CONTENT')) {
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_COOKIE_CONSENT_CONTENT', '".$content_group."', '6', '5', 'xtc_cfg_select_content_module(', 'xtc_cfg_display_content', now())");
      }
      // holds the content rows the module switched off itself, so only it brings them back
      if (!defined('MODULE_COOKIE_CONSENT_CONTENT_HIDDEN')) {
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_COOKIE_CONSENT_CONTENT_HIDDEN', '', '6', '6', now())");
      }

      // installing is a deliberate act, so it puts the whole page back into the content boxes
      if ($this->own_content_selected()) {
        xtc_db_query("UPDATE ".TABLE_CONTENT_MANAGER."
                         SET content_status = '1'
                       WHERE content_file = 'cookie_consent.php'");
        $this->set_hidden_content(array());
      }
      
      // load language-data
      $languages = array();
      $qr = xtc_db_query("SELECT * FROM " . TABLE_LANGUAGES);
      while ($row = xtc_db_fetch_array($qr)) {
        $languages[$row['languages_id']] = $row;
      }
      
      $check_query = xtc_db_query("SHOW TABLES LIKE '".TABLE_COOKIE_CONSENT_CATEGORIES."'");
      if (xtc_db_num_rows($check_query) < 1) {
        xtc_db_query("CREATE TABLE `".TABLE_COOKIE_CONSENT_CATEGORIES."` (
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
                      )");
      
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
            2 => 'Technisch notwendige Cookies tragen dazu bei, grundlegende Funktionalitäten der Webseite zu gewährleisten.'
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
          foreach ($languages as $language_id => $language) {
            if (array_key_exists($language_id, $row['name'])) {
              $sql_data_array = array(
                'categories_id' => $row['id'],
                'categories_name' => decode_utf8($row['name'][$language_id], $language['language_charset']),
                'categories_description' => decode_utf8($row['desc'][$language_id], $language['language_charset']),
                'sort_order' => $row['sort_order'],
                'languages_id' => $language_id,
                'date_added' => 'now()',
                'fixed' => $row['fixed']
              );
              xtc_db_perform(TABLE_COOKIE_CONSENT_CATEGORIES, $sql_data_array);
            }
          }
        }
      }
      

      $check_query = xtc_db_query("SHOW TABLES LIKE '".TABLE_COOKIE_CONSENT_COOKIES."'");
      if (xtc_db_num_rows($check_query) < 1) {
        xtc_db_query("CREATE TABLE `".TABLE_COOKIE_CONSENT_COOKIES."` (
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
                      )");
      
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
            1 => "_ga,_gid: Contains a randomly generated user ID. Using this ID, Google Analytics can recognize returning users on this website and combine the data from previous visits. _gat: Certain data is only sent to Google Analytics once a minute. The cookie has a lifespan of one minute. As long as it is set, certain data transfers are prevented. _gali: This cookie is used by Google Analytics. It is used to anonymously record the clicked elements within a page.",
            2 => "_ga,_gid: Enthält eine zufallsgenerierte User-ID. Anhand dieser ID kann Google Analytics wiederkehrende User auf dieser Website wiedererkennen und die Daten von früheren Besuchen zusammenführen. _gat: Bestimmte Daten werden nur maximal einmal pro Minute an Google Analytics gesendet. Das Cookie hat eine Lebensdauer von einer Minute. Solange es gesetzt ist, werden bestimmte Datenübertragungen unterbunden. _gali: Dieses Cookie wird von Google Analytics eingesetzt. Es dient zur anonymen Erfassung der angeklickten Elemente innerhalb einer Seite."
          ),
          'cookies'   => '_ga,_gid,_gat,_gali',
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
            1 => 'Matomo (formerly Piwik)',
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
            2 => 'Benötigt um externen Diensten wie dem Google-Tag-Manager Ihre Cookie-Entscheidung mitteilen zu können.'
          ),
          'cookies'   => 'MODOilTrack',
          'sort_order'=> 4,
          'status'    => 0,
          'fixed'     => 1
        );
        // Trustedshops
        $defined_cookies[] = array(
          'id'        => 10,
          'category'  => 2,
          'name'      => array(
            1 => 'Trusted Shops Trustbadge',
            2 => 'Trusted Shops Trustbadge'
          ),
          'desc'      => array(
            1 => 'This is a quality seal. It offers the Trusted Shops guarantee and automatically collects reviews.',
            2 => 'Dies ist ein Qualitätssiegel. Es bietet die Trusted-Shops-Garantie und sammelt automatisch Bewertungen.'
          ),
          'cookies'   => '',
          'sort_order'=> 1,
          'status'    => 0,
          'fixed'     => 1
        );
      
        foreach ($defined_cookies as $row) {
          foreach ($languages as $language_id => $language) {
            if (array_key_exists($language_id, $row['name'])) {
              $sql_data = array(
                'cookies_id'          => $row['id'],
                'categories_id'       => $row['category'],
                'cookies_name'        => decode_utf8($row['name'][$language_id], $language['language_charset']),
                'cookies_description' => decode_utf8($row['desc'][$language_id], $language['language_charset']),
                'cookies_list'        => $row['cookies'],
                'sort_order'          => $row['sort_order'],
                'languages_id'        => $language_id,
                'status'              => $row['status'],
                'date_added'          => 'now()',
                'fixed'               => $row['fixed']
              );
              xtc_db_perform(TABLE_COOKIE_CONSENT_COOKIES, $sql_data);
            }
          }
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

    function update_content_status() {
      if ($this->own_content_selected() === false) {
        $this->hide_content();
      } else {
        $this->restore_content();
      }
    }

    function hide_content() {
      // the content manager keeps a status per language, so only the rows still visible are taken away
      $hidden = array();
      $content_query = xtc_db_query("SELECT content_id
                                       FROM ".TABLE_CONTENT_MANAGER."
                                      WHERE content_file = 'cookie_consent.php'
                                        AND content_status = '1'");
      while ($content = xtc_db_fetch_array($content_query)) {
        $hidden[] = (int)$content['content_id'];
      }

      // saving again while the module stays off must not drop what it noted the first time
      if (count($hidden) < 1) {
        return;
      }

      xtc_db_query("UPDATE ".TABLE_CONTENT_MANAGER."
                       SET content_status = '0'
                     WHERE content_id IN (".implode(',', $hidden).")");

      // a row that turns visible later is taken away too, without losing what an earlier save noted
      $hidden = array_unique(array_merge($this->get_hidden_content(), $hidden));
      sort($hidden);

      $this->set_hidden_content($hidden);
    }

    function restore_content() {
      // a row the shop owner switched off himself was never noted, so it stays hidden
      $hidden = $this->get_hidden_content();
      if (count($hidden) < 1) {
        return;
      }

      xtc_db_query("UPDATE ".TABLE_CONTENT_MANAGER."
                       SET content_status = '1'
                     WHERE content_id IN (".implode(',', $hidden).")
                       AND content_file = 'cookie_consent.php'");

      $this->set_hidden_content(array());
    }

    function get_hidden_content() {
      $hidden_query = xtc_db_query("SELECT configuration_value
                                      FROM ".TABLE_CONFIGURATION."
                                     WHERE configuration_key = 'MODULE_COOKIE_CONSENT_CONTENT_HIDDEN'");
      $hidden = xtc_db_fetch_array($hidden_query);

      if (!isset($hidden['configuration_value']) || trim($hidden['configuration_value']) == '') {
        return array();
      }

      $content_ids = array();
      foreach (explode(',', $hidden['configuration_value']) as $content_id) {
        if ((int)$content_id > 0) {
          $content_ids[] = (int)$content_id;
        }
      }

      return $content_ids;
    }

    function set_hidden_content($content_ids) {
      xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                       SET configuration_value = '".implode(',', $content_ids)."',
                           last_modified = now()
                     WHERE configuration_key = 'MODULE_COOKIE_CONSENT_CONTENT_HIDDEN'");
    }

    function own_content_selected() {
      // the values are already saved when this runs, so read them from the database instead of the stale constants
      $config = array();
      $config_query = xtc_db_query("SELECT configuration_key,
                                           configuration_value
                                      FROM ".TABLE_CONFIGURATION."
                                     WHERE configuration_key IN ('MODULE_COOKIE_CONSENT_STATUS', 'MODULE_COOKIE_CONSENT_CONTENT')");
      while ($configuration = xtc_db_fetch_array($config_query)) {
        $config[$configuration['configuration_key']] = $configuration['configuration_value'];
      }

      if (!isset($config['MODULE_COOKIE_CONSENT_STATUS'])
          || strtolower($config['MODULE_COOKIE_CONSENT_STATUS']) != 'true'
          || !isset($config['MODULE_COOKIE_CONSENT_CONTENT'])
          )
      {
        return false;
      }

      $content_query = xtc_db_query("SELECT content_group
                                       FROM ".TABLE_CONTENT_MANAGER."
                                      WHERE content_file = 'cookie_consent.php'
                                   ORDER BY content_group
                                      LIMIT 1");
      if (xtc_db_num_rows($content_query) < 1) {
        return false;
      }

      $content = xtc_db_fetch_array($content_query);

      return ((int)$content['content_group'] > 0
              && (int)$config['MODULE_COOKIE_CONSENT_CONTENT'] == (int)$content['content_group']);
    }

    function create_content() {
      $content_query = xtc_db_query("SELECT content_group
                                       FROM ".TABLE_CONTENT_MANAGER."
                                      WHERE content_file = 'cookie_consent.php'
                                   ORDER BY content_group
                                      LIMIT 1");
      if (xtc_db_num_rows($content_query) > 0) {
        $content = xtc_db_fetch_array($content_query);
        return (int)$content['content_group'];
      }

      $defined_texts = array(
        'german' => array(
          'title'   => 'Cookie-Einstellungen',
          'heading' => 'Cookie-Einstellungen',
          'text'    => '<p>Hier können Sie Ihre Entscheidung über die eingesetzten Cookies jederzeit einsehen und ändern.</p>'
        ),
        'english' => array(
          'title'   => 'Cookie settings',
          'heading' => 'Cookie settings',
          'text'    => '<p>Here you can review and change your decision about the cookies in use at any time.</p>'
        )
      );

      // the new page gets its own group and goes to the end of the content boxes
      $next_query = xtc_db_query("SELECT MAX(content_group) AS content_group
                                    FROM ".TABLE_CONTENT_MANAGER);
      $next = xtc_db_fetch_array($next_query);
      $content_group = (int)$next['content_group'] + 1;

      $sort_query = xtc_db_query("SELECT MAX(sort_order) AS sort_order
                                    FROM ".TABLE_CONTENT_MANAGER."
                                   WHERE parent_id = '0'");
      $sort = xtc_db_fetch_array($sort_query);
      $sort_order = (int)$sort['sort_order'] + 1;

      // without the customer groups the group check hides the page for everybody
      $group_ids = '';
      $groups_query = xtc_db_query("SELECT DISTINCT customers_status_id
                                      FROM ".TABLE_CUSTOMERS_STATUS);
      while ($group = xtc_db_fetch_array($groups_query)) {
        $group_ids .= 'c_'.(int)$group['customers_status_id'].'_group,';
      }

      $languages_query = xtc_db_query("SELECT languages_id,
                                              directory,
                                              language_charset
                                         FROM ".TABLE_LANGUAGES);
      while ($language = xtc_db_fetch_array($languages_query)) {
        $texts = (isset($defined_texts[$language['directory']]) ? $defined_texts[$language['directory']] : $defined_texts['english']);

        $sql_data_array = array(
          'group_ids'       => $group_ids,
          'languages_id'    => $language['languages_id'],
          'content_title'   => decode_utf8($texts['title'], $language['language_charset']),
          'content_heading' => decode_utf8($texts['heading'], $language['language_charset']),
          'content_text'    => decode_utf8($texts['text'], $language['language_charset']),
          'sort_order'      => $sort_order,
          'file_flag'       => '1',
          'content_file'    => 'cookie_consent.php',
          'content_status'  => '1',
          'content_group'   => $content_group,
          'content_delete'  => '0',
          'content_active'  => '1',
          'date_added'      => 'now()'
        );
        xtc_db_perform(TABLE_CONTENT_MANAGER, $sql_data_array);
      }

      return $content_group;
    }

    function remove() {
      xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE `configuration_key` LIKE 'MODULE_COOKIE_CONSENT_%'");

      xtc_db_query("UPDATE ".TABLE_CONTENT_MANAGER."
                       SET content_status = '0'
                     WHERE content_file = 'cookie_consent.php'");
    }

    function keys() {
      // MODULE_COOKIE_CONSENT_CONTENT_HIDDEN is deliberately absent, it only tracks who hid the content page
      return array(
        'MODULE_COOKIE_CONSENT_STATUS',
        'MODULE_COOKIE_CONSENT_SET_READABLE_COOKIE',
        'MODULE_COOKIE_CONSENT_CONTENT'
      );
    }
  }
}
