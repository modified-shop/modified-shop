<?php
/* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2011 Craig Russell - craig@craig-russell.co.uk
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

defined('POLICY_MIN_LOWER_CHARS') or define('POLICY_MIN_LOWER_CHARS', 1);
defined('POLICY_MIN_UPPER_CHARS') or define('POLICY_MIN_UPPER_CHARS', 1);
defined('POLICY_MIN_NUMERIC_CHARS') or define('POLICY_MIN_NUMERIC_CHARS', 1);
defined('POLICY_MIN_SPECIAL_CHARS') or define('POLICY_MIN_SPECIAL_CHARS', 1);

class password_policy 
{

    private $rules = array();     // Array of policy rules
    private $errors = array();    // Array of errors for the last validation
    
    /**
     * Constructor
     */
    function __construct ()
    {
        /**
         *  Define Rules
         *    Key is rule identifier
         *    Value is rule parameter
         *      false is disabled (default)
         *    Test is php code condition returning true if rule is passed
         *      password string is $p
         *      rule value is $v
         *    Error is rule string definition
         */
        $this->rules['min_length'] = array(
            'value' => ENTRY_PASSWORD_MIN_LENGTH,
            'test'  => 'return strlen($p) >= $v;',
            'error' => ENTRY_PASSWORD_ERROR);
                        
        $this->rules['min_lowercase_chars'] = array(
            'value' => ((POLICY_MIN_LOWER_CHARS > 0) ? POLICY_MIN_LOWER_CHARS : false),
            'test'  => 'return preg_match_all("/[a-z]/", $p, $x) >= $v;',
            'error' => ENTRY_PASSWORD_ERROR_MIN_LOWER);
                        
        $this->rules['min_uppercase_chars'] = array(
            'value' => ((POLICY_MIN_UPPER_CHARS > 0) ? POLICY_MIN_UPPER_CHARS : false),
            'test'  => 'return preg_match_all("/[A-Z]/", $p, $x) >= $v;',
            'error' => ENTRY_PASSWORD_ERROR_MIN_UPPER);
                                    
        $this->rules['min_numeric_chars'] = array(
            'value' => ((POLICY_MIN_NUMERIC_CHARS > 0) ? POLICY_MIN_NUMERIC_CHARS : false),
            'test'  => 'return preg_match_all("/[0-9]/", $p, $x) >= $v;',
            'error' => ENTRY_PASSWORD_ERROR_MIN_NUM);
                                
        $this->rules['min_nonalphanumeric_chars'] = array(
            'value' => ((POLICY_MIN_SPECIAL_CHARS > 0) ? POLICY_MIN_SPECIAL_CHARS : false),
            'test'  => 'return preg_match_all("/[\W]/", $p, $x) >= $v;',
            'error' => ENTRY_PASSWORD_ERROR_MIN_CHAR);
    }
    
    /*
     * Validate a password against the policy
     *
     * @param  string  password The password string to validate
     * @return boolean          1 if password conforms to policy
     *                          0 otherwise
     */
    public function validate($password)
    {
        foreach ($this->rules as $k=>$rule)
        {
            // Aliases for password and rule value
            $p = $password;
            $v = $rule['value'];
            
            // Apply each configured rule in turn
            if ($rule['value'] !== false && !eval($rule['test']))
            {
                $this->errors[$k] = $this->get_rule_error($k);
            }
        }
        return sizeof($this->errors) == 0;
    }
    
    /*
     * Get the errors showing which rules were not matched on the last validation
     *
     * Returns array of strings where each element has a key that is the failed
     * rule identifier and a string value that is a human readable description 
     * of the rule
     *
     * @return array        Array of descriptive strings
     */
    public function get_errors()
    {
        return $this->errors;
    }
        
    /*
     * Get the error description for a rule
     *
     * @param  string   $rule       Identifier for the rule to be applied
     * @return string               Error string for rule if it exists
     *                              false otherwise
     */
    private function get_rule_error($rule)
    {
        if (isset($this->rules[$rule]) && isset($this->rules[$rule]['value']))
        {
            return sprintf($this->rules[$rule]['error'], $this->rules[$rule]['value']);
        }
        return false;
    }
}
?>