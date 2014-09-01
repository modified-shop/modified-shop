<?php
/**
 * Template Fields
 *
 * PHP Version 5.3
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */

/**
 * KiTT_TemplateFields
 *
 * Parse the JSON file with country template settings and turn them into
 * a format understandable by phpstache.
 *
 * Don't use this class outside KiTT. Consider it package-private.
 * It might change without notice.
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
class KiTT_TemplateFields
{

    protected $fields;
    protected $countries;
    private $_locale;
    private $_paymentCode;
    private $_vfs;
    private $_baptizer;

    /**
     * Construct the TemplateFields object.
     *
     * @param KiTT_Locale      $locale      The locale for appropriate translations
     * @param string           $type        'invoice', 'part' or 'spec'
     * @param KiTT_InputValues $inputValues data to fill the input fields with
     * @param KiTT_Translator  $translator  To manage translating the fields.
     * @param KiTT_VFS         $vfs         Handle file-system tasks
     * @param KiTT_Baptizer    $baptizer    Baptizer implementation
     */
    public function __construct(
        KiTT_Locale $locale, $type, KiTT_InputValues $inputValues,
        KiTT_Translator $translator, KiTT_VFS $vfs, KiTT_Baptizer $baptizer
    ) {
        $this->_locale = $locale;
        $this->_paymentCode = $type;
        $this->_inputValues = $inputValues;
        $this->_translator = $translator;
        $this->_vfs = $vfs;
        $this->_baptizer = $baptizer;
        $this->loadDefinitions();
    }

    /**
     * Load the json and store it's values
     *
     * @return void
     */
    protected function loadDefinitions()
    {
        $path = KiTT::configuration()->get('paths/input');
        $fileContent = $this->_vfs->file_get_contents($path);

        $temp = json_decode($fileContent, true);
        $this->fields = $temp['fields'];
        $this->countries = $temp['country_fields'];
    }

    /**
     * Process the JSON data and build an array usable for our KiTT_Template
     * mustache extension, containing the settings we need/have specified.
     *
     * The JSON format consists of a "fields" entry with an object consisting of
     * all input field settings as key-value pairs.
     *
     * Following this there is a "country_fields" entry with a key-value map
     * of a country code (iso-alpha2) and a list of objects declaring the fields.
     * Each object in the list represents a row of input fields, and they are
     * declared in the order they should appear in the templates.
     *
     * @return array representation of the JSON
     */
    public function get()
    {
        $result = array();
        foreach ($this->countries[$this->_locale->getCountryCode()] as $row) {
            // Get the rows class
            $class = $this->_rowClass($row);

            // If the conditional for the row is that it is a company purchase,
            // Don't build this row if companies are not allowed
            if (isset($row['condition'])) {
                if ($row['condition'] == 'company'
                    && !$this->_companyAllowed()
                ) {
                    continue;
                }
                $class .= ' ' . $row['condition'];
            }

            // Assemble all the fields that this row should show
            $fields = array();
            if (array_key_exists('fields', $row)) {
                // Filter company/private selector if company purchases
                // are not allowed
                if (in_array('invoice_type', $row['fields'])
                    && !$this->_companyAllowed()
                ) {
                    continue;
                }

                foreach ($row['fields'] as $field) {
                    $fields[] = $this->_field($field);
                }
            }

            if (isset($row['class'])) {
                $class .= ' ' . $row['class'];
            }

            $input_row = array();
            // Translate the title for the row, if there is one
            $this->_mergeTranslationKeys('title', $row, $input_row);

            if (array_key_exists('text', $row)) {
                $input_row['text'] = $row['text'];
            }

            if (array_key_exists('custom', $row)) {
                $input_row['custom'] = $row['custom'];
            }

            // Store what we have gathered in the holder
            $input_row['fields'] = $fields;
            $input_row['class'] = $class;

            $result[] = $input_row;
        }
        return $result;
    }

    /**
     * translate keys in the array and merge it into the collector. The
     * collector array is by-reference and will be updated with the translated
     * data if applicable.
     *
     * Example:
     *  [{'title':'address'}]
     * Will result in:
     *  [{'address': {
     *      'key: 'address',
     *      'text': 'address_transation'}}]
     * So that the address might be translated on the fly in the checkout.
     *
     * @param string $key        "translation key" key, this key is used to get
     *                           the actual key to translate from the array
     *                           supplied. The "translation key" used to get a
     *                           translation from the language pack is retrieved
     *                           from $array[$key]
     * @param array  $array      the array containing among other things the
     *                           translation keys for the input fields
     * @param string &$collector array to merge the translation into, both the
     *                           translation and it's key will be stored as
     *                           $collector[$key] and $collector[${key}_key]
     *                           respectively.
     *
     * @return void
     */
    private function _mergeTranslationKeys($key, $array, &$collector)
    {
        if (array_key_exists($key, $array)) {
            // Replace the translation key if needed.
            $array[$key] = $this->_swapPnoLabel($array[$key]);
            // Replace any occurance of #country# with the current country code
            $array[$key] = str_replace(
                "#country#",
                strtolower($this->_locale->getCountryCode()),
                $array[$key]
            );
            $translation = $this->_translator->translate($array[$key]);
            // Store the translation and its key in the collector
            $collector[$key] = array(
                'key' => $array[$key],
                'text' => $translation
            );
        }
    }

    /**
     * Replace the Translation Key for the pno label for swedish invoice box.
     *
     * @param string $translateKey translation key to replace
     *
     * @return new translation key to use (same as the one sent in if
     *         no swap should be done).
     */
    private function _swapPnoLabel($translateKey)
    {
        if ($translateKey === 'socialSecurityNumber'
            && $this->_paymentCode === 'invoice'
            && $this->_locale->getCountryCode() === 'SE'
        ) {
            return 'klarna_personalOrOrganisatio_number';
        }
        return $translateKey;
    }

    /**
     * Determine what css-class  a row should have based on the number of
     * fields in the row.
     *
     * @param array $row a row array to check
     *
     * @return string name of a class to apply to the row
     */
    private function _rowClass($row)
    {
        if (!array_key_exists('fields', $row)) {
            return 'input_row_empty';
        }

        switch (count($row['fields'])) {
        case 1:
            return "input_row_one";
        case 2:
            return "input_row_two";
        case 3:
            return "input_row_three";
        }
        return "";
    }

    /**
     * Return the field data associated with the specified field name,
     * frobnicated and populated with default values.
     *
     * @param string $field key of the current field
     *
     * @return array containing all settings for the field
     */
    private function _field($field)
    {
        $current = $this->fields[$field];
        $type = $current['type'];
        $result = array();

        $result['name'] = $this->_baptizer->nameField($field);

        // Default the value to empty.
        $result['value'] = "";
        // If there is a value to prefill, prefill it.
        if (isset($this->_inputValues->$field)) {
            $result['value'] = $this->_inputValues->$field;
        }

        if ($type === 'radio') {
            $result['radio'] = true;
        } else if ($type === 'select') {
            $result['select'] = true;
        } else if ($type === 'checkbox') {
            $result['value'] = $field;
            $result['type'] = $type;
        } else {
            $result['type'] = $type;
        }

        // Translate title and notice (hover balloon)
        $this->_mergeTranslationKeys('title', $current, $result);
        $this->_mergeTranslationKeys('notice', $current, $result);

        // If a size is supplied, set it
        if (array_key_exists('size', $current)) {
            $result['size'] = $current['size'];
        }

        // If there are values, sort them out
        if (array_key_exists("values", $current)) {
            $values = array();
            // If Values is not an array, it is used for a placeholder to be
            // programatically entered instead of stored in the json.
            if (!is_array($current['values'])) {
                $values = $this->_valuePlaceholderReplacer($current);
            } else {
                $default = null;
                if (isset($this->_inputValues->$field)) {
                    $default = $this->_inputValues->$field;
                } else if (array_key_exists('default', $current)) {
                    $default = $current['default'];
                }
                foreach ($current['values'] as $key) {
                    $value = array(
                        'value' => $key['value'],
                        'id' => $this->_baptizer->nameId($key['id'])
                    );
                    if ($key['value'] === $default) {
                        $value['default'] = true;
                    }
                    $this->_mergeTranslationKeys('text', $key, $value);
                    $values[] = $value;
                }
            }
            $result['values'] = $values;
        }
        return $result;
    }

    /**
     * Manage a value placeholder in the json.
     *
     * @param array $current The current field.
     *
     * @return array with the value placeholder replaced
     */
    private function _valuePlaceholderReplacer($current)
    {
        // Translate the title of the combobox
        $default = null;
        $translated = $this->_translator->translate($current['title']);
        switch ($current['values']) {
        case "#years#":
            if (isset($this->_inputValues->birth_year)) {
                $default = $this->_inputValues->birth_year;
            }
            return $this->_years($translated, $default);
        case "#months#":
            if (isset($this->_inputValues->birth_month)) {
                $default = $this->_inputValues->birth_month;
            }
            return $this->_months($translated, $default);
        case "#days#":
            if (isset($this->_inputValues->birth_day)) {
                $default = $this->_inputValues->birth_day;
            }
            return $this->_days($translated, $default);
        }
        return array();
    }

    /**
     * Is company allowed?
     *
     * @return true if companies are allowed to use this payment option.
     */
    private function _companyAllowed()
    {
        return (
            KiTT_CountryLogic::isCompanyAllowed($this->_locale->getCountryCode())
            && $this->_paymentCode === 'invoice'
        );

    }

    /**
     * Get an array of possible year options.
     *
     * @param string $label the label of the selectbox
     * @param int    $value the default value
     *
     * @return array with available year options
     */
    private function _years($label, $value = null)
    {
        $years = array(
            array(
                'value' => '00',
                'text' => $label,
                'default' => ($value == null),
                'disabled' => true
            )
        );
        for ($i = date("Y"); $i >= 1900; $i--) {
            $years[] = array(
                "value" => $i,
                "text" => $i,
                "default" => ($i == $value)
            );
        }
        return $years;
    }

    /**
     * Get view data for day dropdown
     *
     * @param string $label label of dropdown
     * @param string $value option to pre-select
     *
     * @return array view data
     */
    private function _days($label, $value = null)
    {
        $days = array(
            array(
                'value' => '00',
                'text' => $label,
                'default' => ($value == null),
                'disabled' => true
            )
        );
        for ($i = 1; $i <= 31; $i++) {
            $day = sprintf('%02d', $i);
            $days[] = array(
                "value" => $day,
                "text" => $day,
                "default" => ($day == $value)
            );
        }
        return $days;
    }


    /**
     * Get view data for year dropdown
     *
     * @param string $label label of dropdown
     * @param string $value option to pre-select
     *
     * @return array view data
     */
    private function _months($label, $value = null)
    {
        $months = array(
            array(
                'value' => '00',
                'text' => $label,
                'default' => ($value == null),
                'disabled' => true
            )
        );
        for ($i = 1; $i <= 12; $i++) {
            $month = $this->_translator->translate("month_{$i}");
            $months[] = array(
                "value" => sprintf("%02d", $i),
                "text" => $month,
                "default" => (sprintf("%02d", $i) == $value)
            );
        }
        return $months;
    }
}
