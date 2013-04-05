<?php
class KiTT_Translator
{

    /**
     * @var KiTT_LanguagePack
     */
    private $_languagePack;

    /**
     * @var KiTT_Locale
     */
    private $_locale;


    /**
     * Constructor for KiTT_Translator
     *
     * @param KiTT_LanguagePack $languagePack Translations to use
     * @param KiTT_Locale       $locale       The locale to fetch translations for
     */
    public function __construct($languagePack, $locale)
    {
        $this->_languagePack = $languagePack;
        $this->_locale = $locale;
    }

    /**
     * Get the translation for the specified key
     *
     * @param string $key Translation key
     *
     * @return string
     */
    public function translate($key)
    {
        return $this->_languagePack->fetch($key, $this->_locale->getLanguage());
    }

    /**
     * Get a translated text.
     *
     * alternative interface for the benefit of mustache.
     *
     * @param string $key Translation key to translate
     *
     * @return string The translated text
     */
    public function __get($key)
    {
        return $this->translate($key);
    }

    /**
     * Pretend we can translate anything
     *
     * @param string $key Translation key to pretend we translate
     *
     * @return bool true
     */
    public function __isset($key)
    {
        // let's pretend everything is set to use the fallback in fetch
        return true;
    }

}
