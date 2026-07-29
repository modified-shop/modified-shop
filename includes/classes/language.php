<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(language.php,v 1.4 2003/02/11); www.oscommerce.com
   (c) 2003 nextcommerce (language.php,v 1.6 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce (language.php 962 2005-05-27)

   Copyright phpMyAdmin (select_lang.lib.php3 v1.24 04/19/2002)
   Copyright Stephane Garin <sgarin@sgarin.com> (detect_language.php v0.1 04/02/2002)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
   
  class language {
  
    var $language;
    var $languages;
    var $catalog_languages;
    var $browser_languages;

    private static $definitions = array();
    private static $loaded_files = array();

    function __construct($lng = '') {
      $this->languages = array(
        'ar' => array('ar([-_][[:alpha:]]{2})?|arabic', 'arabic', 'ar'),
        'bg-win1251' => array('bg|bulgarian', 'bulgarian-win1251', 'bg'),
        'bg-koi8r' => array('bg|bulgarian', 'bulgarian-koi8', 'bg'),
        'ca' => array('ca|catalan', 'catala', 'ca'),
        'cs-iso' => array('cs|czech', 'czech-iso', 'cs'),
        'cs-win1250' => array('cs|czech', 'czech-win1250', 'cs'),
        'da' => array('da|danish', 'danish', 'da'),
        'de' => array('de([-_][[:alpha:]]{2})?|german', 'german', 'de'),
        'el' => array('el|greek',  'greek', 'el'),
        'en' => array('en([-_][[:alpha:]]{2})?|english', 'english', 'en'),
        'es' => array('es([-_][[:alpha:]]{2})?|spanish', 'spanish', 'es'),
        'et' => array('et|estonian', 'estonian', 'et'),
        'fi' => array('fi|finnish', 'finnish', 'fi'),
        'fr' => array('fr([-_][[:alpha:]]{2})?|french', 'french', 'fr'),
        'gl' => array('gl|galician', 'galician', 'gl'),
        'he' => array('he|hebrew', 'hebrew', 'he'),
        'hu' => array('hu|hungarian', 'hungarian', 'hu'),
        'id' => array('id|indonesian', 'indonesian', 'id'),
        'it' => array('it|italian', 'italian', 'it'),
        'ja-euc' => array('ja|japanese', 'japanese-euc', 'ja'),
        'ja-sjis' => array('ja|japanese', 'japanese-sjis', 'ja'),
        'ko' => array('ko|korean', 'korean', 'ko'),
        'ka' => array('ka|georgian', 'georgian', 'ka'),
        'lt' => array('lt|lithuanian', 'lithuanian', 'lt'),
        'lv' => array('lv|latvian', 'latvian', 'lv'),
        'nl' => array('nl([-_][[:alpha:]]{2})?|dutch', 'dutch', 'nl'),
        'no' => array('no|norwegian', 'norwegian', 'no'),
        'pl' => array('pl|polish', 'polish', 'pl'),
        'pt-br' => array('pt[-_]br|brazilian portuguese', 'brazilian_portuguese', 'pt-BR'),
        'pt' => array('pt([-_][[:alpha:]]{2})?|portuguese', 'portuguese', 'pt'),
        'ro' => array('ro|romanian', 'romanian', 'ro'),
        'ru-koi8r' => array('ru|russian', 'russian-koi8', 'ru'),
        'ru-win1251' => array('ru|russian', 'russian-win1251', 'ru'),
        'sk' => array('sk|slovak', 'slovak-iso', 'sk'),
        'sk-win1250' => array('sk|slovak', 'slovak-win1250', 'sk'),
        'sr-win1250' => array('sr|serbian', 'serbian-win1250', 'sr'),
        'sv' => array('sv|swedish', 'swedish', 'sv'),
        'th' => array('th|thai', 'thai', 'th'),
        'tr' => array('tr|turkish', 'turkish', 'tr'),
        'uk-win1251' => array('uk|ukrainian', 'ukrainian-win1251', 'uk'),
        'zh-tw' => array('zh[-_]tw|chinese traditional', 'chinese_big5', 'zh-TW'),
        'zh' => array('zh|chinese simplified', 'chinese_gb', 'zh'),
      );

      $this->catalog_languages = $this->get_catalog_languages();
      $this->browser_languages = '';
      $this->language = '';

      if (!empty($lng) && isset($this->catalog_languages[$lng])) {
        $this->language = $this->catalog_languages[$lng];        
      } elseif (isset($this->catalog_languages[DEFAULT_LANGUAGE])) {
        $this->language = $this->catalog_languages[DEFAULT_LANGUAGE];
      } else {
        $this->language = $this->catalog_languages[key($this->catalog_languages)];
      }
    }
  
    function get_catalog_languages() {
      static $languages_array;
    
      if (!isset($languages_array)) {
        $languages_array = array();
      
        $where = !defined('RUN_MODE_ADMIN') ? ((isset($_SESSION['customers_status']['customers_status']) && $_SESSION['customers_status']['customers_status'] == '0') ? "WHERE status_admin = '1'" : "WHERE status = '1'") : '';
        $languages_query = xtDBquery("SELECT * 
                                        FROM " . TABLE_LANGUAGES . " 
                                             ".$where." 
                                    ORDER BY sort_order");
        while ($languages = xtc_db_fetch_array($languages_query, true)) {
          $languages_array[$languages['code']] = $languages;
          $languages_array[$languages['code']]['id'] = $languages['languages_id'];
        }
      }
    
      return $languages_array;
    }
  
    function get_browser_language() {
      $this->browser_languages = explode(',', (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : ''));
      for ($i=0, $n=sizeof($this->browser_languages); $i<$n; $i++) {
        foreach ($this->languages as $key => $value) {
          if (preg_match('/^(' . $value[0] . ')(;q=[0-9]\\.[0-9])?$/i', $this->browser_languages[$i]) && isset($this->catalog_languages[$key])) {
            $this->language = $this->catalog_languages[$key];
            break 2;
          }
        }
      }
    }

    /**
     * Load an array based language file or a legacy language file using define().
     *
     * @param string $file
     * @param string $language_code
     * @param bool   $define_constants
     *
     * @return array
     */
    public static function load($file, $language_code, $define_constants = false) {
      $loaded_file = $language_code.':'.$file;

      if (!isset(self::$loaded_files[$loaded_file])) {
        $constants_before = self::get_user_constants();
        $language_definitions = require_once $file;
        $constants_after = self::get_user_constants();
        $legacy_definitions = array_diff_key($constants_after, $constants_before);

        if (!is_array($language_definitions)) {
          $language_definitions = array();
        }

        self::register(
          $language_code,
          array_replace($legacy_definitions, $language_definitions)
        );
        self::$loaded_files[$loaded_file] = true;
      }

      if ($define_constants === true) {
        self::define_constants($language_code);
      }

      return isset(self::$definitions[$language_code])
        ? self::$definitions[$language_code]
        : array();
    }

    /**
     * Register language definitions without defining global constants.
     *
     * @param string $language_code
     * @param array  $definitions
     *
     * @return void
     */
    public static function register($language_code, array $definitions) {
      if (!isset(self::$definitions[$language_code])) {
        self::$definitions[$language_code] = array();
      }

      self::$definitions[$language_code] = array_replace(
        self::$definitions[$language_code],
        $definitions
      );
    }

    /**
     * Define the constants for the active language.
     *
     * Missing definitions are taken from DEFAULT_LANGUAGE when it has already
     * been loaded. Existing constants are never overwritten.
     *
     * @param string $language_code
     *
     * @return void
     */
    public static function define_constants($language_code) {
      $definitions = array();

      if (defined('DEFAULT_LANGUAGE')
          && isset(self::$definitions[DEFAULT_LANGUAGE])
          )
      {
        $definitions = self::$definitions[DEFAULT_LANGUAGE];
      }

      if (isset(self::$definitions[$language_code])) {
        $definitions = array_replace(
          $definitions,
          self::$definitions[$language_code]
        );
      }

      foreach ($definitions as $key => $value) {
        defined($key) OR define($key, $value);
      }
    }

    /**
     * Get a definition in a specific language without changing active constants.
     *
     * @param string $key
     * @param string $language_code
     *
     * @return mixed
     */
    public static function get($key, $language_code) {
      if (isset(self::$definitions[$language_code])
          && array_key_exists($key, self::$definitions[$language_code])
          )
      {
        return self::$definitions[$language_code][$key];
      }

      if (defined('DEFAULT_LANGUAGE')
          && isset(self::$definitions[DEFAULT_LANGUAGE])
          && array_key_exists($key, self::$definitions[DEFAULT_LANGUAGE])
          )
      {
        return self::$definitions[DEFAULT_LANGUAGE][$key];
      }

      if (defined($key)) {
        return constant($key);
      }

      return $key;
    }

    /**
     * Return all user-defined constants.
     *
     * @return array
     */
    private static function get_user_constants() {
      $constants = get_defined_constants(true);

      return isset($constants['user']) ? $constants['user'] : array();
    }
  
  }
