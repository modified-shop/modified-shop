<?php

/* SEQ_LIB : Security library
 * Version 0.8.3 - PRE; 31.07.2011
 *
 * Main Configuration is in a separate file.
 *
 * This software is open-source. License: GNUv3
 * Autor: Erich Kachel; info@erich-kachel.de
 * http://www.erich-kachel.de/seq_lib
 * http://code.google.com/p/sseq-lib/
*/

define('_SEQ_DEBUG', 0); /* 1: show; 0: hide */
restore_error_handler();

/**
 * Error reporting is disabled to avoid informative output.
 */
if (_SEQ_DEBUG || _SEQ_ERRORS) {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT); //Hide E_DEPRECATED and E_STRICT errors from SSEQ - do not include E_NOTICE here!
    set_error_handler('seq_error_handler_');
} else {
    error_reporting(0);
}

define('_SEQ_TOKEN_NAME','SEQ_TOKEN_');

/* one only global */
/**
 * if set, there was a filter violation on the way
 */
$_filter_violation = false;


SEQ_APP_SALT_();

/**
 * @private
 * Generates unique SALT value to be used with all MD5 hashes.
 * Salt is valid until salt file is removed (on new installations or path changes)
 * DO NOT USE THIS KEY TO ENCRYPT SOMETHING! IT CAN BE CHANGED OR REMOVED WITHOUT
 * WARNING AND THEN YOU WILL NOT HAVE ACCESS TO THE DATA ENCRYPTED WITH IT AGAIN!
 */
function SEQ_APP_SALT_() {
    $saltfile = _SEQ_BASEDIR . "seq_lib/app_salt.php";
    if (!file_exists($saltfile)) {
        $application_salt = "<?php\n" . 'define("_SEQ_APP_SALT", "' . md5(uniqid(rand(), TRUE)) . '");' . "\n" .
        'define("_SEQ_APP_PATH", "' . _SEQ_BASEDIR . '");' . "?>";
        $fh = fopen($saltfile, 'w');
        fwrite($fh, $application_salt);
        fclose($fh);
    }
    if (file_exists(_SEQ_BASEDIR . 'seq_lib/app_salt.php')) {
        include_once(_SEQ_BASEDIR . 'seq_lib/app_salt.php');

        if (!defined('_SEQ_APP_PATH') || (defined('_SEQ_APP_PATH') && (_SEQ_APP_PATH != _SEQ_BASEDIR))) {
            /* sseq-lib has been moved. requires new key. */
            unlink($saltfile);
            /* yes, new key will be generated with the very next click. */
        }
    } else {
        // SALT could not be created! WEAK SECURITY! USE default salt
        define("_SEQ_APP_SALT", "2bowkclkr40kmyohjeisha0shpb3rl4a");
    }
}

/**
 * @private
 * Main function to handle session lifetime and security.
 */
function SEQ_HANDLE_SESSION_() {

    if (!_SEQ_SECURE_SESSION) { return false; }

    SEQ_SECURE_SESSION();
}

/**
 * Sets additional security to session data and session cookie.
 * Has to be called after the application fully initiates its session.
*/
function SEQ_SECURE_SESSION() {

    global $HTTP_SESSION_VARS;

    if (!_SEQ_SECURE_SESSION) { return false; }

    if (!isset($_SESSION) && !isset($HTTP_SESSION_VARS)) {
        seq_log_('SEQ_SECURE_SESSION: no SESSION found at execution time. Call SEQ_SECURE_SESSION after session start.', '');
        return false;
    }

    $seq_sessid = session_id();

    // get session data // what if these two differ?!
    $SESSIONDATA = '';
    if (ini_get('register_long_arrays') && isset($HTTP_SESSION_VARS)) {
        $SESSIONDATA = $HTTP_SESSION_VARS;
    } else {
        $SESSIONDATA = $_SESSION;
    }

    if (!isset($SESSIONDATA['SEQ'])) {
        $SESSIONDATA['SEQ'] = array();
    }

    if (!isset($SESSIONDATA['SEQ']['session_touchtime'])) {
        $session_data = $SESSIONDATA;
        if (_SEQ_SECURE_COOKIES) {

            /* will only work if PHPSESSION is used and session hase not been already started. */
            if (function_exists('ini_set')) {
                ini_set('session.cookie_lifetime', _SEQ_SESSIONABSOLUTELIFETIME);
                ini_set('session.cookie_httponly', true);
            }
            if (function_exists('session_set_cookie_params')) {
                $cookie_data_ = session_get_cookie_params();
                session_set_cookie_params(_SEQ_SESSIONABSOLUTELIFETIME,
                                          $cookie_data_['path'],
                                          $cookie_data_['domain'],
                                          $cookie_data_['secure'], true);
            }
        }
        session_regenerate_id(true);

        $SESSIONDATA = $session_data;
        $SESSIONDATA['SEQ']['session_touchtime'] = time();
        $SESSIONDATA['SEQ']['session_creationtime'] = time();

        if (_SEQ_SESSION_HEADERSCHECK) {
            $SESSIONDATA['SEQ']['agent_key'] = seq_useragent_fingerprint_();
        }

    } else if (_SEQ_SESSIONREFRESH == 0 || isset($SESSIONDATA['SEQ']['session_touchtime'])) {

        if (isset($SESSIONDATA['SEQ']['session_creationtime']) &&
           (time() - $SESSIONDATA['SEQ']['session_creationtime']) > _SEQ_SESSIONABSOLUTELIFETIME)
        {
            seq_log_('SESSION TERMINATED: absolute sessionlifetime expired', '');
            SEQ_TERMINATE_SESSION_();
        }

        if (isset($SESSIONDATA['SEQ']['agent_key'])) {
            if ($SESSIONDATA['SEQ']['agent_key'] != seq_useragent_fingerprint_()) {
                seq_log_('SESSION TERMINATED: AGENT FINGERPRINT CHANGED', '');
                SEQ_TERMINATE_SESSION_();
            }
        }

        $session_age = time() - $SESSIONDATA['SEQ']['session_touchtime'];
        if (_SEQ_SESSIONREFRESH == 0 || $session_age > _SEQ_SESSIONREFRESH) {
            $session_data = $SESSIONDATA;

            if (!headers_sent()) {
                session_regenerate_id(true);
            }
            $SESSIONDATA = $session_data;
        }
    }

    $SESSIONDATA['SEQ']['session_touchtime'] = time();

    if (ini_get('register_long_arrays') && isset($HTTP_SESSION_VARS)) {
        $HTTP_SESSION_VARS = $SESSIONDATA;
    }

    $_SESSION = $SESSIONDATA;

}

/**
 * @private
 * Terminates current session and unsets all session content.
 */
function SEQ_TERMINATE_SESSION_($redir_exit = true) {
    global $HTTP_COOKIE_VARS;

    $seq_sessname = _SEQ_SESSION_NAME ? _SEQ_SESSION_NAME : session_name();

    // expire cookie
    if (_SEQ_SECURE_COOKIES && ($_COOKIE || $HTTP_COOKIE_VARS)
        && isset($_COOKIE[$seq_sessname]) && !headers_sent())
    {
        // could we be too early to know 'path' or 'domain' settings?
        $cookie_data_ = session_get_cookie_params();
        setcookie($seq_sessname, '', time()-_SEQ_SESSIONLIFETIME, $cookie_data_['path'], $cookie_data_['domain']);

        if (isset($_SESSION)) {
            $_COOKIE = array();
        }
        if (isset($HTTP_COOKIE_VARS)) {
            $HTTP_COOKIE_VARS = array();
        }
    }

    // unset session variables
    if (isset($_SESSION)) {
        $_SESSION = array();
    }
    if (isset($HTTP_SESSION_VARS)) {
        $HTTP_SESSION_VARS = array();
    }
    session_unset();
    //session_write_close();

    if ($redir_exit) {
        // redirect to location OR
        seq_terminate_('redirect');
        die;
    }
}

/**
 * Checks a Token against CSRF-Attacks.
 * Gets Token out of GET/POST-request and checks for validity.
 * If specific name given, Token will only be valid for that named action.
 */
function SEQ_CHECK_TOKEN($originname_ = '') {
    global $HTTP_SESSION_VARS;

    $tokenname = SEQ_CREATE_TOKEN_NAME_($originname_);

    $tokenArray = $_SESSION['SEQ']['SEQ_TOKEN'];

    if (!isset($tokenArray) || !is_array($tokenArray)) {
        seq_log_('SEQ_CHECK_TOKEN: no SESSION found at execution time. Call SEQ_CHECK_TOKEN after session start.', '');
        SEQ_TERMINATE_SESSION_(); /* elsewere with no session there is access */
        return false;
    }

    $tokenvalue = _QB_HTTPVARS2ARRAY($tokenname, 'pg');

    if (strlen($tokenvalue) == 32) {

        if (isset($tokenArray[$tokenname]) && isset($tokenArray[$tokenname]['token']) &&
            $tokenArray[$tokenname]['token'] == $tokenvalue)
        {

            $token_age = time() - $tokenArray[$tokenname]['time'];
            if ($token_age > _SEQ_TOKENLIFETIME) {
                seq_debug_($token_age . ">" . _SEQ_TOKENLIFETIME);
                seq_log_('SEQ_CHECK_TOKEN: CSRF token expired', $token_age - _SEQ_TOKENLIFETIME);
                SEQ_TERMINATE_SESSION_();
            }

            if ($tokenArray[$tokenname]['once']) {
                unset($_SESSION['SEQ']['SEQ_TOKEN'][$tokenname]); // no replay
            }

            // SESSION OK

        } else {
            seq_log_('SEQ_CHECK_TOKEN: wrong CSRF token', '');
            SEQ_TERMINATE_SESSION_();
        }
    } else {
        seq_log_('SEQ_CHECK_TOKEN: CSRF token required', $tokenvalue);
        SEQ_TERMINATE_SESSION_();
    }
}

/**
 * @private
 * Generates Token value.
 */
function SEQ_CREATE_TOKEN_VALUE_($originname_ = '', $once_ = false) {
    global $HTTP_SESSION_VARS;

    $tokenname = SEQ_CREATE_TOKEN_NAME_($originname_);

    if (!isset($_SESSION['SEQ'])) {
        $_SESSION['SEQ'] = array();
        $_SESSION['SEQ']['SEQ_TOKEN'] = array();
    }

    if (!isset($_SESSION['SEQ']['SEQ_TOKEN'][$tokenname])) {
        $token = md5(uniqid(rand(), true));
        $_SESSION['SEQ']['SEQ_TOKEN'][$tokenname] = array('token' => $token, 'time' => time(), 'once' => $once_ ? true : false);
    } else {
        // set single use token
        $_SESSION['SEQ']['SEQ_TOKEN'][$tokenname]['once'] = $once_ ? true : false;
        $token = $_SESSION['SEQ']['SEQ_TOKEN'][$tokenname]['token'];
    }

    return $token;
}

/**
 * @private
 * Generates Token name.
 */
function SEQ_CREATE_TOKEN_NAME_($originname_ = '') {

    $header_hash = '';
    if (_SEQ_SESSION_HEADERSCHECK) {
        $header_hash = seq_useragent_fingerprint_();
    }
    $originname = $originname_ ?
            md5($originname_ . $header_hash . session_id() . _SEQ_APP_SALT) :
            md5($header_hash . session_id() . _SEQ_APP_SALT);
    $tokenname = _SEQ_TOKEN_NAME . $originname;

    return $tokenname;
}

/**
 * Generate a Token against CSRF-Attacks.
 * Generates a Token to be inserted into a Form.
 * If specific name given, Token will only be valid for that named action.
 */
function SEQ_FTOKEN($formname_ = '', $once_ = false) {
    return '<input type="hidden" name="' . SEQ_CREATE_TOKEN_NAME_($formname_) .
    '" value="' . SEQ_CREATE_TOKEN_VALUE_($formname_, $once_) . '" />' . "\n";
}

/**
 * Generate a Token against CSRF-Attacks.
 * Generates a Token to be inserted into a Link.
 * If specific name given, Token will only be valid for that named action.
 */
function SEQ_LTOKEN($linkname_ = '', $once_ = false) {
    return SEQ_CREATE_TOKEN_NAME_($linkname_) . '=' . SEQ_CREATE_TOKEN_VALUE_($linkname_, $once_);
}

/**
 * @private
 * Generates Useragent fingerprint
 */
function seq_useragent_fingerprint_() {
    /* With IE 6.0 HTTP_ACCEPT changes between requests. Not usefull! */
    $fingerprint = $_SERVER['HTTP_USER_AGENT']._SEQ_APP_SALT;
    seq_debug_($fingerprint);
    return md5($fingerprint);
}

/**
 * @private
 * tries to detect whether input was already backslashed or not.
 * Removes slashes only if input was backslashed.
 */
function seq_remove_slashes_($string_ = '') {
    $orig = $string_;
    $stripped = stripslashes($orig);
    if ($orig != $stripped) {
        $escaped = addslashes($stripped);
        if ($orig == $escaped) {
            $sec_value = stripslashes($escaped);
        } else {
            $sec_value = $orig;
        }
    } else {
        $sec_value = $orig;
    }
    return $sec_value;
}

function uniord__($c) {
    $h = ord($c{0});
    if ($h <= 0x7F) {
        return $h;
    } else if ($h < 0xC2) {
        return false;
    } else if ($h <= 0xDF) {
        return ($h & 0x1F) << 6 | (ord($c{1}) & 0x3F);
    } else if ($h <= 0xEF) {
        return ($h & 0x0F) << 12 | (ord($c{1}) & 0x3F) << 6
                                 | (ord($c{2}) & 0x3F);
    } else if ($h <= 0xF4) {
        return ($h & 0x0F) << 18 | (ord($c{1}) & 0x3F) << 12
                                 | (ord($c{2}) & 0x3F) << 6
                                 | (ord($c{3}) & 0x3F);
    } else {
        return false;
    }
}

/**
 * Secures input string against XSS-attacks.
 * Return value can be send to browser securely.
 * supports single & multi byte UTF-8
 */
function SEQ_OUTPUT($string_ = '') {
    $string = mb_convert_encoding($string_, "UTF-8", "7bit, UTF-7, UTF-8, UTF-16, ISO-8859-1, ASCII");
    $string = seq_remove_slashes_($string);
    seq_check_intrusion_($string);

    $output = '';

    for ($i = 0; $i < mb_strlen($string); $i++)  {
        if (preg_match('/([a-zA-Z0-9_.-])/', $string[$i])) {
            $output .= $string[$i];
            continue;
        }
        $byte = ord($string[$i]);
        if ($byte <= 127)  {
            $length = 1;
            $output .= sprintf("&#x%04s;", dechex(uniord__(mb_substr($string, $i, $length))));
        } else if ($byte >= 194 && $byte <= 223)  {
            $length = 2;
            $output .= sprintf("&#x%04s;", dechex(uniord__(mb_substr($string, $i, $length))));
        } else if ($byte >= 224 && $byte <= 239)  {
            $length = 3;
            $output .= sprintf("&#x%04s;", dechex(uniord__(mb_substr($string, $i, $length))));
        } else if ($byte >= 240 && $byte <= 244)  {
            $length = 4;
            $output .= sprintf("&#x%04s;", dechex(uniord__(mb_substr($string, $i, $length))));
        }
    }

    return $output;
}

function seq_debug_($string_ = '') {
    if (_SEQ_DEBUG) {
        echo "<br>------" .  $string_ . "<br>";
    }
}

/**
 * @Public
 * Check string type
 * returns empty string if type or length dont match
 * returns input string if all OK
 */
function SEQ_TYPE($string_ = '', $type_ = '', $minvalue_ = null, $maxvalue_ = null, $varname_ = '' /*for logging*/, $source_ = ' SRC'/*for logging*/) {
    return seq_check_type_($string_, $type_, $minvalue_, $maxvalue_, $varname_, $source_);
}

/**
 * @Private
 * Check string type
 * returns empty string if type or length dont match
 * returns input string if all OK
 */
function seq_check_type_($string_ = '', $type_ = '', $minvalue_ = null, $maxvalue_ = null, $varname_ = '' /*for logging*/, $source_ = ''/*for logging*/) {
    global $_filter_violation;

    $string = $string_;
    seq_check_intrusion_($string, $source_);

    switch(strtoupper(trim($type_))) {
    case 'NUM' :
    case 'INT' :
        if (!SEQ_ISNUM($string, $minvalue_, $maxvalue_, $varname_, $source_)) {
            $_filter_violation = true;
            return '';
        }
        break;
    case 'STR' :
        if (!SEQ_ISSTR($string, $minvalue_, $maxvalue_, $varname_, $source_)) {
            $_filter_violation = true;
            return '';
        }
        break;
    case 'REX' :
        if (!SEQ_ISREX($string, $minvalue_, $maxvalue_, $varname_, $source_)) {
            $_filter_violation = true;
            return '';
        }
        break;
    default:
        if (!SEQ_ISBETWEEN($string, $minvalue_, $maxvalue_, $varname_, $source_)) {
            $_filter_violation = true;
            return '';
        }
        break;
    }
    return $string;
}

/**
 * @public
 * Prepares input for usage within MYSQL-query
 * Type, min-max Length
 */
function SEQ_MYSQL($string_ = '', $type_ = '', $minvalue_ = null, $maxvalue_ = null, $varname_ = '', $source_ = '') {
    $sec_value = '';

    $string_ = seq_remove_slashes_($string_);

    $orig = $string_;

    seq_check_intrusion_($orig, $source_);

    if ($type_ != '' && $orig != '') {
        $orig = seq_check_type_($orig, $type_, $minvalue_, $maxvalue_, $varname_, $source_);
    }

    $mysql_connection = true;
    try {
        @mysql_select_db('__sseq-lib--nonexistent--db--name__');
        if (mysql_error()) {
            $mysql_connection = false;
        }
    } catch (ErrorException $err) {
        $mysql_connection = false;
    }

    /* automatically choose best function to escape input */
    if (!(mysql_error()) && $mysql_connection !== false) {
        $P_ESCAPE_FUNC = create_function('$match_','return mysql_real_escape_string($match_);');
        $sec_value = $P_ESCAPE_FUNC($orig);
    }
    /* fallback if mysql is not available yet */
    if (mysql_error()) {
        $P_ESCAPE_FUNC = create_function('$match_','return mysql_real_escape_string($match_);');
        $sec_value = $P_ESCAPE_FUNC($orig);
    }

    seq_debug_($sec_value);
    return $sec_value;
}

/**
 * Input must match the regular expression
 *
 */
function SEQ_ISREX($string_ = '', $minvalue_ = null, $maxvalue_ = null, $varname_ = '', $source_ = '') {
    seq_check_intrusion_($string_, $source_);

    if (preg_match($minvalue_, $string_)) {
        return true;
    }

    seq_log_(($varname_ ? $varname_ : 'UNKNOWN VAR') . ': param not matching REGEXP: ' . $minvalue_, $string_, $source_);
    return false;
}

/**
 * Input must be a number and between given values
 *
 */
function SEQ_ISNUM($string_ = '', $minvalue_ = null, $maxvalue_ = null, $varname_ = '', $source_ = '') {
    seq_check_intrusion_($string_, $source_);

    $minvallist = explode(',', $minvalue_);
    if (strlen($string_) == 0) {
        for ($t=0; $t < count($minvallist); $t++) {
            if (strtoupper(trim($minvallist[$t])) == 'NULL') {
                // if zero value allowed, then ok
                return true;
            }
        }
    }

    $typ_numeric = is_numeric($string_);
    if ($typ_numeric) {
        for ($t=0; $t < count($minvallist); $t++) {
            $minvalue = trim($minvallist[$t]);
            if (isset($minvalue) && $minvalue != '' && strtoupper($minvalue) != 'NULL' && $string_ < $minvalue) {
                seq_log_(($varname_ ? $varname_ : 'UNKNOWN VAR') . ': INT below MIN (' . $minvalue . ')', $string_, $source_);
                return false;
            }
        }
        $maxvalue_ = trim($maxvalue_);
        if (isset($maxvalue_) && $maxvalue_ != '' && $string_ > $maxvalue_) {
            seq_log_(($varname_ ? $varname_ : 'UNKNOWN VAR') . ': INT beneath MAX (' . $maxvalue_ . ')', $string_, $source_);
            return false;
        }
        return true;
    }

    seq_log_(($varname_ ? $varname_ : 'UNKNOWN VAR') . ': INT param not INT', $string_, $source_);
    return false;
}

/**
 * Input must be a string and between given values
 *
 */
function SEQ_ISSTR($string_ = '', $minvalue_ = null, $maxvalue_ = null, $varname_ = '', $source_ = '') {
    seq_check_intrusion_($string_, $source_);

    $typ_string = is_string($string_);
    if ($typ_string) {
        $minvalue_ = trim($minvalue_);
        if (isset($minvalue_) && $minvalue_ != '' && strlen($string_) < $minvalue_) {
            seq_log_(($varname_ ? $varname_ : 'UNKNOWN VAR') . ': STR length below MIN (' . $minvalue_ . ')', $string_, $source_);
            return false;
        }
        $maxvalue_ = trim($maxvalue_);
        if (isset($maxvalue_) && $maxvalue_ != '' && strlen($string_) > $maxvalue_) {
            seq_log_(($varname_ ? $varname_ : 'UNKNOWN VAR') . ': STR length beneath MAX (' . $maxvalue_ . ')', $string_, $source_);
            return false;
        }
        return true;
    }
    seq_log_(($varname_ ? $varname_ : 'UNKNOWN VAR') . ': STR Param not STRING', $string_, $source_);
    return false;
}

/**
 * Length of input must be between given values
 *
 */
function SEQ_ISBETWEEN($string_ = '', $minvalue_ = null, $maxvalue_ = null, $varname_ = '', $source_ = '') {
    $minvalue_ = trim($minvalue_);
    if (isset($minvalue_) && $minvalue_ != '' && strlen($string_) < $minvalue_) {
        seq_log_(($varname_ ? $varname_ : 'UNKNOWN VAR') . ': length below MIN (' . $minvalue_ . ')', $string_, $source_);
        return false;
    }
    $maxvalue_ = trim($maxvalue_);
    if (isset($maxvalue_) && $maxvalue_ != '' && strlen($string_) > $maxvalue_) {
        seq_log_(($varname_ ? $varname_ : 'UNKNOWN VAR') . ': length beneath MAX (' . $maxvalue_ . ')', $string_, $source_);
        return false;
    }
    return true;
}

/**
 * Apply urldecode on input until all occurences are decoded.
 * Handles multiple encoded inputs
 */
function SEQ_URLDECODE($string_ = '') {
    $unescaped = mb_convert_encoding($string_, "UTF-8", "auto");

    while(urldecode($unescaped) != $unescaped) {
        $unescaped = urldecode($unescaped);
    }

    return $unescaped;
}

/**
 * Tries to make sure, the file path is local.
 */
function SEQ_LOCFILE($path_ = '') {
    $path = SEQ_URLDECODE($path_);
    $path = realpath($path);
    $path_check = preg_replace('/\\\/', '/', strtolower($path));
    $docpath_check = preg_replace('/\\\/', '/', strtolower($_SERVER['DOCUMENT_ROOT']));
    seq_debug_($path_check . '###' . $docpath_check);
    if ($path && strpos($path_check, $docpath_check) !== 0) {
        seq_log_('SEQ_LOCFILE: Path not in BASEPATH', $path_check);
        seq_reaction_();
        $path = '';
    } else if (empty($path)) {
        seq_log_('SEQ_LOCFILE: Path not local or damaged', $path_);
        seq_reaction_();
    }

    return $path;
}

/**
 * Error output with XSS-prevention.
 * Can be turned off globally to supress informative errors.
 */
function SEQ_ERROR($string_ = '') {
    if (_SEQ_ERRORS) {
        echo SEQ_OUTPUT($string_);
    }
    seq_log_('SEQ_ERROR: ', $string_);
}

function seq_error_handler_($code_ = '', $msg_ = '', $file_ = '', $line_ = '') {
    switch ($code_) {
    case E_ERROR:
        seq_log_('Script Error', "line: $line_ script: $file_ error: $code_ reason: $msg_");
        break;
    case E_WARNING:
        seq_log_('Script Warning', "line: $line_ script: $file_ error: $code_ reason: $msg_");
        break;
    case E_NOTICE:
        seq_log_('Script Notice', "line: $line_ script: $file_ error: $code_ reason: $msg_");
        break;
    default:
        break;
    }
}

/**
 * Logfile output
 */
function seq_log_($message_ = '', $testName_ = '', $source_ = '') {
    if (_SEQ_LOG) {
        $rootdir = _SEQ_LOG_DIR;
        $appendJSSecurity = false;
        if (!is_file($rootdir . "seq_log/log.txt")) {
            $appendJSSecurity = true;
        }
        $logfile = fopen($rootdir . "seq_log/log.txt","a");

        if ($appendJSSecurity) {
            fputs($logfile, 'This file may contain dangerous code! Do NOT load it with a web browser!' . "\n");
            fputs($logfile, '<script language="JavaScript">alert("This file may contain dangerous code!' .
                  ' Do NOT load it with a web browser! \n\nYou will be redirected to Google now to make sure' .
                  ' the content of this page will no be executed.");  location.href="http://www.google.com";</script>' . "\n");

        }
        fputs($logfile, date("d.m.Y, H:i:s",time()) .
              ", " . $_SERVER['REMOTE_ADDR'] .
              ", [" . $source_ . "]" .
              ", " . $message_ .
              ", " . $testName_ .
              ", " . $_SERVER['REQUEST_METHOD'] .
              ", " . $_SERVER['PHP_SELF'] .
              ", " . $_SERVER['HTTP_USER_AGENT'] .
              ", " . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '') .
              "\n");
        fclose($logfile);
    }
}

/**
 * Terminates script execution
 */
function seq_terminate_($reason_ = '') {

    // better to redirect in any case? it is less informative!
    switch ($reason_) {
    case 'err':
        echo "<b>Undefined action.</b>";
        die;
        break;
    case 'redirect':
        if (!headers_sent() && defined('_SEQ_ONERROR_REDIRECT_TO')) {
            header("Location: " . _SEQ_ONERROR_REDIRECT_TO);
            echo "<b>Undefined action.</b>";
        } else {
            seq_log_('Redirect not possible. Headers already sent.');
            echo "<b>Undefined action.</b>";
        }
        die;
        break;
    default:
        echo "<b>Illegal action.</b>";
        die;
    }
}

/**
 * Executes defined reaction on detected security violation.
 */
function seq_reaction_($filter_ = false) {

    $action = defined('_SEQ_IDS_ONATTACK_ACTION') ? _SEQ_IDS_ONATTACK_ACTION : '';

    // call is comming from filter check
    if ($filter_) {
        $action = defined('_SEQ_FILTER_NOMATCH_ACTION') ? _SEQ_FILTER_NOMATCH_ACTION : '';
    }

    $action_array = explode(' ', $action);
    if (in_array('delay', $action_array)) {
        sleep(50);
    }
    if (in_array('logout', $action_array)) {
        SEQ_TERMINATE_SESSION_(false /* do not redirect here */);
    }
    if (in_array('redirect', $action_array)) {
        if (!headers_sent() && defined('_SEQ_ONERROR_REDIRECT_TO')) {
            $save_session = '';

            // if known and found in query string, keep session id when redirect
            if ($_SERVER['QUERY_STRING']) {
                $seq_sessname = _SEQ_SESSION_NAME ? _SEQ_SESSION_NAME : session_name();
                $querypairs = explode('&', $_SERVER['QUERY_STRING']);
                for ($t=0; $t < count($querypairs); $t++) {
                    $pairs = explode('=', $querypairs[$t]);
                   	if ($pairs[0] == $seq_sessname) {
                        $save_session = join($querypairs[$t]);
                   	}
               	}
            }
            print '<head><meta http-equiv="refresh" content="0;url=' . _SEQ_ONERROR_REDIRECT_TO . ($save_session ? '?' . $save_session : '') . '"></head>';
            //header("Location: " . _SEQ_ONERROR_REDIRECT_TO . '?' . $save_session);
            //die;
        }
    }

    // do not stop script execution here. it may be a minor violation and maybe
    // there was no redirect before.
}

function seq_intrusion_sql_($string_ = '', $source_ = '') {
    $scan_value = $string_;
    $matches = false;
    /* scan for SQL-attack pattern
       http://niiconsulting.com/innovation/snortsignatures.html
    */
    if (preg_match("/(\%27)|(\')|(\')|(%2D%2D)|(\/\*)/i", $scan_value) || /*(\-\-)  deleted. no meaning for MySQL*/
                                                                 /* (\/\*) added. Comment sign for MySQL */
        preg_match("/\w*(\%27)|'(\s|\+)*((\%6F)|o|(\%4F))((\%72)|r|(\%52))/i", $scan_value) ||
        preg_match("/((\%27)|')(\s|\+)*union/i", $scan_value)) {
        seq_log_('SQL Injection detected', $scan_value, $source_);
        $matches = true;
    }
}

/**
 * Helper for "globals overwrite" scan
 *
 * @param string $string_
 * @param string $source_
 * @return boolean
 */
function seq_globals_overwrite_($string_ = '', $source_ = '') {
    $matches = false;
    $s_globalvars = array('_SERVER',
                          'HTTP_SERVER_VARS',
                          '_ENV',
                          'HTTP_ENV_VARS',
                          '_COOKIE',
                          'HTTP_COOKIE_VARS',
                          '_GET',
                          'HTTP_GET_VARS',
                          '_POST',
                          'HTTP_POST_VARS',
                          '_FILES',
                          'HTTP_POST_FILES',
                          '_REQUEST',
                          '_SESSION',
                          'HTTP_SESSION_VARS',
                          'GLOBALS');

    /*
    security vulneration!
    http://www.securityfocus.com/archive/1/462263/30/0/threaded
    */
    if (preg_match("/^(" . implode("|", $s_globalvars) . ")/", $string_, $match_)) {
        seq_log_('Global VAR overwrite detected', $string_, $source_);
        $matches = true;
    }
    return $matches;
}

/**
 * Helper for Intrusion Detection System
 */
function seq_check_intrusion_($string_ = '', $source_ = '') {

    if (!defined('_SEQ_IDS')) { return false; }

    /* array scan is later required */
    if(is_array($string_)) {return false;}
    $scan_value = $string_;

    $matches = false;
    /* scan for SQL-attack pattern
       http://niiconsulting.com/innovation/snortsignatures.html
    */
    if (preg_match("/(\%27)|(\')|(\')|(%2D%2D)|(\/\*)/i", $scan_value) || /*(\-\-)  deleted. no meaning for MySQL*/
                                                                 /* (\/\*) added. Comment sign for MySQL */
        preg_match("/\w*(\%27)|'(\s|\+)*((\%6F)|o|(\%4F))((\%72)|r|(\%52))/i", $scan_value) ||
        preg_match("/((\%27)|')(\s|\+)*union/i", $scan_value))
    {
        seq_log_('SQL Injection detected', $scan_value, $source_);
        $matches = true;
    }

    /* scan for XSS-attack pattern
       http://niiconsulting.com/innovation/snortsignatures.html
    */
    if (preg_match("/((\%3C)|<)((\%2F)|\/)*[a-z0-9\%]+((\%3E)|>)/i", $scan_value) ||
        preg_match("/((\%3C)|<)((\%69)|i|(\%49))((\%6D)|m|(\%4D))((\%67)|g|(\%47))[^\n]+((\%3E)|>)/i", $scan_value))
    {
        seq_log_('XSS detected', $scan_value, $source_);
        $matches = true;
    }

    /* scan for Mail-Header-attack pattern
    */
    if (preg_match("/(Content-Transfer-Encoding:|MIME-Version:|content-type:|Subject:|to:|cc:|bcc:|from:|reply-to:)/ims", $scan_value))
    {
        seq_log_('Mail-Header Injection detected', $scan_value, $source_);
        $matches = true;
    }

    /* scan for "Special chars" pattern
    */
    if (preg_match("/%0A|\\r|%0D|\\n|%00|\\0|%09|\\t|%01|%02|%03|%04|%05|%06|%07|%08|%09|%0B|%0C|%0E|%0F|%10|%11|%12|%13/i", $scan_value))
    {
        seq_log_('Special Chars detected', $scan_value, $source_);
        $matches = true;
    }

    $matches = seq_globals_overwrite_($scan_value, $source_);

    if ($matches) {
        seq_reaction_();
    }

    return $matches;
}

function _seq_datadump_array($array_, $name_) {
    $appdata = '';

    foreach($array_ as $param=>$value) {
        if (is_array($value)) {
            foreach($value as $arr_name=>$arr_value) {
                $appdata .= $name_ . '   ' . $param . '[' . $arr_name . ']=' . $arr_value . "\n";
            }
        } else {
            $appdata .= $name_ . ' ' . $param . '=' . $value . "\n";
        }
    }

    return $appdata;
}

/**
 * Generates data dump of incomming data.
 * Output is to be analysed to design an appropriate SANITIZE - filter
 */
function SEQ_DATADUMP() {
    global $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS, $HTTP_SESSION_VARS;

    $appdata = '';
    $datafile = _SEQ_DUMP_DIR . "seq_dump/app_data.txt";
    //if (file_exists($datafile)) {

        if (isset($_GET)) {
            $appdata .= _seq_datadump_array($_GET, '[_GET]');
        }

        if (isset($HTTP_GET_VARS)) {
            $appdata .= _seq_datadump_array($HTTP_GET_VARS, '[HGET]');
        }

        if (isset($_POST)) {
            $appdata .= _seq_datadump_array($_POST, '[_POS]');
        }

        if (isset($HTTP_POST_VARS)) {
            $appdata .= _seq_datadump_array($HTTP_POST_VARS, '[HPOS]');
        }

        if (isset($_COOKIE)) {
            $appdata .= _seq_datadump_array($_COOKIE, '[_COO]');
        }

        if (isset($HTTP_COOKIE_VARS)) {
            $appdata .= _seq_datadump_array($HTTP_COOKIE_VARS, '[HCOO]');
        }

        if (isset($_SESSION)) {
            $appdata .= _seq_datadump_array($_SESSION, '[_SES]');
        }

        if (isset($HTTP_SESSION_VARS)) {
            $appdata .= _seq_datadump_array($HTTP_SESSION_VARS, '[HSES]');
        }

        if (isset($_REQUEST)) {
            //$appdata .= _seq_datadump_array($_REQUEST, '[_REQ]');
        }

        if (isset($GLOBALS)) {
            //$appdata .= _seq_datadump_array($GLOBALS, '[ GLO]');
        }

        $appdata .= "====================================================================================================\n";
        $fh = fopen($datafile, 'a');
        if ($fh) {
            fwrite($fh, $appdata);
            fclose($fh);
        }
    //}

}

/**
 * Helper for SANITIZE
 */
function seq_sanitize_var_($string_ = '', $type_ = '', $minvalue_ = null, $maxvalue_ = null, $varname_ = '' /*for logging*/, $source_ = '' /*for logging*/, $sql_ = false, $xss_ = false) {

    $return = seq_check_type_($string_, $type_, $minvalue_, $maxvalue_, $varname_, $source_);

    if ($sql_) {
        $return = SEQ_MYSQL($return, $type_, $minvalue_, $maxvalue_, $varname_, $source_);

    }
    if ($xss_ == 'true' || $xss_ == 'full') {
        $return = SEQ_OUTPUT($return);
    }

    if ($xss_ == 'simple') {
        $return = seq_remove_slashes_($return);
        $return = htmlentities($return, ENT_QUOTES);
    }
    return $return;
}

/**
 * handles single or _array_ field values
 */
function seq_sanitize_block_($value_ = '', $actions_ = array(), $varname_ = '' /*for logging*/, $source_ = '' /*for logging*/, $sql_ = false, $xss_ = false) {
    if (is_array($value_)) {
        $fieldvalue = $value_;
    } else {
        // create fake array to handle both inputs the same
        $fieldvalue = array($value_);
    }
    $returnValue = null;

    // here all input is an array
   foreach ($fieldvalue as $r => $value) {

        /* defining type DEL will delete every single value without any check */
        if ($actions_['type'] == 'DEL') {
            $sanitizedvalue = '';
            seq_log_('DELETED PARAMETER ' . $varname_ . ' FROM: ' . $source_, $value_, $source_);
        } else {
            $sanitizedvalue = seq_sanitize_var_($value, strtoupper(trim($actions_['type'])), $actions_['min'], $actions_['max'], $varname_, $source_, $sql_, $xss_);
        }

        if (is_array($value_)) {
            // restore arrays
            if (!is_array($returnValue)) {
                $returnValue = array();
            }
            $returnValue[$r] = $sanitizedvalue;
        } else {
            // restore nonarrays
            $returnValue = $sanitizedvalue;
        }
    }
    return $returnValue;
}

function _seq_traverse_n_check_(&$mainstruct_, $varname_, $actions_, $sql_ = false, $xss_ = false, $origin_name_ = '') {

    $mainstruct = &$mainstruct_;

    if (isset($mainstruct)) {
        // detect array structure. it is delimited by DOTs
        $array_structure = explode('.', $varname_);
        /* varname is last name in array */
        $varname = $array_structure[count($array_structure) - 1];

        /* traversing array one level before varname is expected to be found in array */
        for ($t = 0; $t < count($array_structure) - 1; $t++) {
            $last_array = $array_structure[$t];
            if (preg_match('/^\/.*?\/$/', $last_array)) {
                $previous_struct = &$mainstruct;
                foreach($previous_struct as $matchname=>$value) {
                    if (preg_match($last_array, $matchname)) {
                        $mainstruct = &$previous_struct[$last_array];
                    }
                }
            } else {
                $mainstruct = &$mainstruct[$last_array];
            }
        }

        $var_exists_ = false;
        // check first and last char to be "/" to detect regexp
        if (preg_match('/^\/.*?\/$/', $varname)) {
            // match regexp in array
            foreach($mainstruct as $matchname=>$value) {
                $var_ = &$mainstruct[$matchname];

                if (preg_match($varname, $matchname) || is_array($var_)) {
                    if (is_array($var_)) {
                        //echo "DIGG INTO ARRAY: " . $var_ . "\n";
                        _seq_traverse_n_check_($var_, $varname_, $actions_, $sql_, $xss_, $origin_name_);
                    } else {
                        $mainstruct[$matchname] = seq_sanitize_block_($var_, $actions_, $varname_ /* with underline because we need original name */, $origin_name_, $sql_, $xss_);
                    }
                }
            }
        } elseif (isset($mainstruct) && isset($mainstruct[$varname])) {
                $var_ = $mainstruct[$varname];
                $mainstruct[$varname] = seq_sanitize_block_($var_, $actions_, $varname_ /* with underline because we need original name */, $origin_name_, $sql_, $xss_);
        }
    }
}

/**
 * SANITIZE checks variables in common global locations to match a defined type
 * Non matching variables are rewritten with an empty string.
 * Filter can be loaded from a file.
 */
function SEQ_SANITIZE($sanitizeList_ = '', $isFile_ = false) {
    global $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS, $HTTP_SESSION_VARS, $_filter_violation;

    SEQ_CHECK_GLOBALS_OVERWRITE_();

    if ($isFile_) {
        if (file_exists($sanitizeList_)) {
            $sanitizeList_ = file_get_contents($sanitizeList_, 'r');
        } else {
            /* could not load file */
            seq_log_('SANITIZE: Could not load file. No filter definition available!', '', '');
        }
    }

    // reading config: version=x.x
    $configuration = preg_match_all('/\/\/.+\[([^\]]+)\]/', $sanitizeList_, $configMatch_);

    /* comment lines */
    $sanitizeList_ = preg_replace('/\/\/+.*/', '', $sanitizeList_);
    $sanitizeList_ = preg_replace('/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/', '', $sanitizeList_);
    $sanitizeList_ = preg_replace('/\s{2,}/', ' ', $sanitizeList_);

    $hash = array();
    if (!is_array($sanitizeList_)) {
        $lines = explode('&', $sanitizeList_);
        for($l=0; $l < count($lines); $l++) {
            $line = trim($lines[$l]);
            $params = explode('#', $line);
            for($p=0; $p < count($params); $p++) {
                $params[$p] = trim($params[$p]);
            }
            if (!$params[0] || $params[0] == '') {
                continue;
            }
            $hash[$params[0]] = array('source'=>$params[1],
                                     'type'=>$params[2],
                                     'min'=>$params[3],
                                     'max'=>$params[4],
                                     'xss'=>$params[5] != '' ? $params[5] : 'false',
                                     'sql'=>$params[6] != '' ? $params[6] : 'false');
        }
        $sanitizeList_ = $hash;
    }

    if ($sanitizeList_) {
        foreach($sanitizeList_ as $varname=>$actions) {

            if (!$varname) { continue; }

            $paramSource_ = $actions['source'];

            /* search for "!" as sign for exclusive source */
            $exclusiveSource_ = strpos($paramSource_, '!') !== false ? true : false;

            $xss = ($actions['xss'] != '') ? $actions['xss'] : false;
            $sql = ($actions['sql'] == 'true') ? true : false;

            if (!$paramSource_) {$paramSource_ = ini_get('variables_order');}

            $source = strtolower($paramSource_); //required for modified eCommerce Shopsoftware thx to GTB

            if (strpos($source, 'g') !== false) {
                _seq_traverse_n_check_($_GET, $varname, $actions, $sql, $xss, '_GET');
                _seq_traverse_n_check_($HTTP_GET_VARS, $varname, $actions, $sql, $xss, 'HGET');
            } elseif ($exclusiveSource_) {
                _seq_traverse_n_check_($_GET, $varname, array('type'=>'DEL'));
                _seq_traverse_n_check_($HTTP_GET_VARS, $varname, array('type'=>'DEL'));
            }

            if (strpos($source, 'p') !== false) {
                _seq_traverse_n_check_($_POST, $varname, $actions, $sql, $xss, '_POS');
                _seq_traverse_n_check_($HTTP_POST_VARS, $varname, $actions, $sql, $xss, 'HPOS');
            } elseif ($exclusiveSource_) {
                _seq_traverse_n_check_($_POST, $varname, array('type'=>'DEL'), null, null, '_POS');
                _seq_traverse_n_check_($HTTP_POST_VARS, $varname, array('type'=>'DEL'));
            }

            if (strpos($source, 'c') !== false) {
                _seq_traverse_n_check_($_COOKIE, $varname, $actions, $sql, $xss, '_COO');
                _seq_traverse_n_check_($HTTP_COOKIE_VARS, $varname, $actions, $sql, $xss, 'HCOO');
            } elseif ($exclusiveSource_) {
                _seq_traverse_n_check_($_COOKIE, $varname, array('type'=>'DEL'));
                _seq_traverse_n_check_($HTTP_COOKIE_VARS, $varname, array('type'=>'DEL'));
            }

            if (strpos($source, 's') !== false) {
                _seq_traverse_n_check_($_SESSION, $varname, $actions, $sql, $xss, '_SES');
                _seq_traverse_n_check_($HTTP_SESSION_VARS, $varname, $actions, $sql, $xss, 'HSES');
            } elseif ($exclusiveSource_) {
                _seq_traverse_n_check_($_SESSION, $varname, array('type'=>'DEL'));
                _seq_traverse_n_check_($HTTP_SESSION_VARS, $varname, array('type'=>'DEL'));
            }

            if (strpos($source, 'g') !== false ||
                strpos($source, 'p') !== false ||
                strpos($source, 'c') !== false)
            {
                _seq_traverse_n_check_($_REQUEST, $varname, $actions, $sql, $xss, '_REQ');
            }

            // checking in GLOBALS means that we make no difference of the source because here
            // variables from all sources will be checked and filtered. changes here will be
            // reflected in the original source, so removing a variable here because of bad filter
            // match will remove it also from $_GET if set there.

            if (strpos($source, 'S') !== false) {
                _seq_traverse_n_check_($_SERVER, $varname, $actions, $sql, $xss, '_SER');
            }

            if (strpos($source, 'E') !== false) {
                _seq_traverse_n_check_($_ENV, $varname, $actions, $sql, $xss, '_ENV');
            }

            if (strpos($source, 'F') !== false) {
                _seq_traverse_n_check_($_FILES, $varname, $actions, $sql, $xss, '_FIL');
            }

        }

        if (false && defined('_DENY_UNKNOWN_INPUT') && _DENY_UNKNOWN_INPUT) {
            foreach($_GET as $varname=>$value) {
                if (!is_array($value)) {
                        if (!isset($sanitizeList_[$varname]) &&
                            strpos($varname, _SEQ_TOKEN_NAME) === false &&
                            $varname != _SEQ_SESSION_NAME &&
                            $varname != 'PHPSESSID'
                            )
                        {
                            _seq_traverse_n_check_($_GET, $varname, array('type'=>'DEL'), null, null, '_GET');
                            _seq_traverse_n_check_($HTTP_GET_VARS, $varname, array('type'=>'DEL'), null, null, 'HGET');
                            _seq_traverse_n_check_($_REQUEST, $varname, array('type'=>'DEL'), null, null, '_REQ');
                        }
                } else {
                    $array_struct = _seq_array_to_list($varname, $value);
                    for ($r = 0; $r < count($array_struct); $r++) {
                        $varname = $array_struct[$r];
                    if (!isset($sanitizeList_[$varname]) &&
                        strpos($varname, _SEQ_TOKEN_NAME) === false &&
                        $varname != _SEQ_SESSION_NAME &&
                        $varname != 'PHPSESSID'
                        )
                    {
                        _seq_traverse_n_check_($_GET, $varname, array('type'=>'DEL'), null, null, '_GET');
                        _seq_traverse_n_check_($HTTP_GET_VARS, $varname, array('type'=>'DEL'), null, null, 'HGET');
                        _seq_traverse_n_check_($_REQUEST, $varname, array('type'=>'DEL'), null, null, '_REQ');
                    }
                }
            }
            }

            foreach($_POST as $varname=>$value) {
                        if (!isset($sanitizeList_[$varname]) &&
                            strpos($varname, _SEQ_TOKEN_NAME) === false &&
                            $varname != _SEQ_SESSION_NAME &&
                            $varname != 'PHPSESSID'
                            )
                        {
                            _seq_traverse_n_check_($_POST, $varname, array('type'=>'DEL'), null, null, '_POS');
                    _seq_traverse_n_check_($HTTP_POST_VARS, $varname, array('type'=>'DEL', null, null, 'HPOS'));
                            _seq_traverse_n_check_($_REQUEST, $varname, array('type'=>'DEL'), null, null, '_REQ');
                        }
                if (is_array($value)) {

                    $array_struct = _seq_array_to_list($varname, $value);
                    for ($r = 0; $r < count($array_struct); $r++) {
                        $varname = $array_struct[$r];
                    if (!isset($sanitizeList_[$varname]) &&
                        strpos($varname, _SEQ_TOKEN_NAME) === false &&
                        $varname != _SEQ_SESSION_NAME &&
                        $varname != 'PHPSESSID'
                        )
                    {
                        _seq_traverse_n_check_($_POST, $varname, array('type'=>'DEL'), null, null, '_POS');
                            _seq_traverse_n_check_($HTTP_POST_VARS, $varname, array('type'=>'DEL'), null, null, 'HPOS');
                        _seq_traverse_n_check_($_REQUEST, $varname, array('type'=>'DEL'), null, null, '_REQ');
                        }
                    }
                }
            }
        }
    }

    if ($_filter_violation) {
        seq_reaction_(true /* filter */);
    }
}


/* transform array recursively to special array structure list.
 * example: $_GET['selection']['3']
 * will become: selection.3
*/
function _seq_array_to_list($arrayname_, $array_ = array()) {
    $arraylist = array();
    if (is_array($array_)) {
        foreach($array_ as $array_name=>$array_value) {
            if (is_array($array_value)) {
                $arraylist = array_merge(_seq_array_to_list($arrayname_ . '.' . $array_name, $array_value), $arraylist);
            } else {
                $arraylist[] = $arrayname_ . '.' . $array_name;
            }
        }
    } else {
        $arraylist[] = $arrayname_;
    }
    return $arraylist;
}

/**
 * Implemented light Intrusion Detection System
 */
function SEQ_CHECK_GLOBALS_OVERWRITE_($paramSource_ = '') {
    global $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS;

    $matches = false;

    if (!$paramSource_) {$paramSource_ = ini_get('variables_order');} /* what about: request_order ?*/
    $method = '';

    for($t=0; $t < strlen($paramSource_); $t++) {
        $method = $paramSource_[$t];

        $sec_value = '';

        if (strtolower($method) == 'g') {
            foreach($_GET as $name=>$value) {
                if(seq_globals_overwrite_($name, '_GET')) {
                    unset($_GET[$name]);
                }
            }
            if (ini_get('register_long_arrays') && isset($HTTP_GET_VARS)) {
                foreach($HTTP_GET_VARS as $name=>$value) {
                    if(seq_globals_overwrite_($name, 'HGET')) {
                        unset($HTTP_GET_VARS[$name]);
                    }
                }
            }
        }

        if (strtolower($method) == 'p') {
            foreach($_POST as $name=>$value) {
                if(seq_globals_overwrite_($name, '_POS')) {
                    unset($_POST[$name]);
                }
            }
            if (ini_get('register_long_arrays') && isset($HTTP_POST_VARS)) {
                foreach($HTTP_POST_VARS as $name=>$value) {
                    if(seq_globals_overwrite_($name, 'HPOS')) {
                        unset($HTTP_POST_VARS[$name]);
                    }
                }
            }
        }

        if (strtolower($method) == 'c') {
            foreach($_COOKIE as $name=>$value) {
                if(seq_globals_overwrite_($name, '_COO')) {
                    unset($_COOKIE[$name]);
                }
            }
            if (ini_get('register_long_arrays') && isset($HTTP_COOKIE_VARS)) {
                foreach($HTTP_COOKIE_VARS as $name=>$value) {
                    if(seq_globals_overwrite_($name, 'HCOO')) {
                        unset($HTTP_COOKIE_VARS[$name]);
                    }
                }
            }
        }
    }

    if (isset($_SESSION)) {
        foreach($_SESSION as $name=>$value) {
            if(seq_globals_overwrite_($name, '_SES')) {
                unset($_SESSION[$name]);
            }
        }
        if (ini_get('register_long_arrays') && isset($HTTP_SESSION_VARS)) {
            foreach($HTTP_SESSION_VARS as $name=>$value) {
                if(seq_globals_overwrite_($name, 'HSES')) {
                    unset($HTTP_SESSION_VARS[$name]);
                }
            }
        }
    }

    if (isset($_REQUEST)) {
        foreach($_REQUEST as $name=>$value) {
            if(seq_globals_overwrite_($name, '_REQ')) {
                unset($_REQUEST[$name]);
            }
        }
    }

    /* $GLOBALS cannot be checked because it contains all globals names! */
    /* _ENV, _SERVER, _FILES are not checked yet. Should they? */
}

/**
 * Simulates prepared statements for older MYSQL
 * Replaces placeholder with variables after checking for type and length
 */
class SEQ_SQL_SANITIZE {
    var $query = '';
    function SEQ_SQL_SANITIZE($query_ = '') {
        $this->query = $query_;
    }
    /* > 5.2.1*/
    function __toString() {
        return $this->query;
    }
    /* ALL VERSIONS */
    function READY() {
        return $this->query;
    }
    function INSERT($key_ = '', $var_ = '', $type_ = 'STR', $minvalue_ = null, $maxvalue_ = null) {
        $key = ltrim($key_, ':');
        $var = $var_;
        $var = SEQ_MYSQL($var, strtoupper(trim($type_)), $minvalue_, $maxvalue_);
        if (strtoupper(trim($type_)) == 'STR') {
            $var = "'" . $var . "'";
        }
        $this->query = preg_replace('/:' . preg_quote($key) . '/', $var, $this->query);
    }
}

/**
 * Checks variables to avoid Mail Header Injection
 * Set second param to "false" when checking mail body elsewere all line breaks
 * and carriage returns will be deleted.
 */
function SEQ_EMAIL($param_ = '', $lbcr_ = true) {
    seq_check_intrusion_($param_);

    $filtered = null;
    /* replace until done */
    while ($param_ != $filtered || !isset($filtered)) {
        if (isset($filtered)) {
            $param_ = $filtered;
        }
        $filtered = preg_replace("/(Content-Transfer-Encoding:|MIME-Version:|content-type:|" .
                           "Subject:|to:|cc:|bcc:|from:|reply-to:)/ims", "", $param_);
    }
    unset($filtered);

    if ($lbcr_) {
        /* replace until done */
        while (!isset($filtered) || $param_ != $filtered) {
            if (isset($filtered)) {
                $param_ = $filtered;
            }
            $filtered = preg_replace("/(%0A|\\\\r|%0D|\\\\n|%00|\\\\0|%09|\\\\t|%01|%02|%03|%04|%05|" .
                                   "%06|%07|%08|%09|%0B|%0C|%0E|%0F|%10|%11|%12|%13)/ims", "", $param_);
        }
    }
    return $param_;
}

/**
 * Checks variables to avoid HTTP Header Injection
 */
function SEQ_HEADER($param_ = '') {
    seq_check_intrusion_($param_);

    /* replace until done */
    while (!isset($filtered) || $param_ != $filtered) {
        if (isset($filtered)) {
            $param_ = $filtered;
        }
        $filtered = preg_replace("/(%0A|\\\\r|%0D|\\\\n|%00|\\\\0|%09|\\\\t|%01|%02|%03|%04|%05|" .
                           "%06|%07|%08|%09|%0B|%0C|%0E|%0F|%10|%11|%12|%13)/ims", "", $param_);
    }
    return $param_;
}

function _QB_SPECIAL_PARAM_DELIMITER() {
    /*
      osCommerce, Open Source E-Commerce Solutions
      http://www.oscommerce.com

      Copyright (c) 2003 osCommerce

      Released under the GNU General Public License
    */

    // set the HTTP GET parameters manually if search_engine_friendly_urls is enabled
    $params = array();
    if (strlen(getenv('PATH_INFO')) > 1) {
      $GET_array = array();
      $PHP_SELF = str_replace(getenv('PATH_INFO'), '', $PHP_SELF);
      $vars = explode('/', substr(getenv('PATH_INFO'), 1));
      //BOF - DokuMan - 2011-12-19 - precount for performance
      //for ($i=0, $n=sizeof($vars); $i<$n; $i++) {
      $n=sizeof($vars);
      for ($i=0; $i<$n; $i++) {
      //EOF - DokuMan - 2011-12-19 - precount for performance
        if (strpos($vars[$i], '[]')) {
          $GET_array[substr($vars[$i], 0, -2)][] = $vars[$i+1];
        } else {
          $params[$vars[$i]] = $vars[$i+1];
        }
        $i++;
      }

      if (sizeof($GET_array) > 0) {
        while (list($key, $value) = each($GET_array)) {
          $params[$key] = $value;
        }
      }
    }

    return $params;
}

function _QB_HTTPVARS2ARRAY(
    $var_ = '',       /* eine explizite variable abfragen */
    $selection_ = 'ps'  /* p - nur POST /// g - nur GET /// s - nur SESSION*/
    ) {

    global $_QB_VERBOSE;

    $data = null;
    if ($var_) {
        if (ini_get('register_long_arrays')) {
            if (isset($HTTP_POST_VARS) && array_key_exists($var_, $HTTP_POST_VARS) && (strpos(strtolower($selection_), 'p') > -1 || !$selection_)) {
                $data = $HTTP_POST_VARS[$var_];
            } else if (isset($HTTP_GET_VARS) && array_key_exists($var_, $HTTP_GET_VARS) && (strpos(strtolower($selection_), 'g') > -1 || !$selection_)) {
                $data = $HTTP_GET_VARS[$var_];
            } else if (isset($HTTP_SESSION_VARS) && array_key_exists($var_, $HTTP_SESSION_VARS) && (strpos(strtolower($selection_), 's') > -1 || !$selection_)) {
                $data = $HTTP_SESSION_VARS[$var_];
            }
        }

        if (!isset($data)) {
            if (array_key_exists($var_, $_POST) && (strpos(strtolower($selection_), 'p') > -1 || !$selection_)) {
                $data = $_POST[$var_];
            } else if (array_key_exists($var_, $_GET) && (strpos(strtolower($selection_), 'g') > -1 || !$selection_)) {
                $data = $_GET[$var_];
            } else if ($_SESSION && array_key_exists($var_, $_SESSION) && (strpos(strtolower($selection_), 's') > -1 || !$selection_)) {
                $data = $_SESSION[$var_];
            }
        }

        if (!isset($data) && function_exists('_QB_SPECIAL_PARAM_DELIMITER') && array_key_exists($var_, _QB_SPECIAL_PARAM_DELIMITER())) {
            $data = _QB_SPECIAL_PARAM_DELIMITER();
            $data = $data[$var_];
        }
    } else {
        //$data = array();
        if (ini_get('register_long_arrays')) {
            if(isset($HTTP_SESSION_VARS) && (strpos(strtolower($selection_), 's') > -1 || !$selection_)) {
                $data = $HTTP_SESSION_VARS;
            }
            if(isset($HTTP_GET_VARS) && (strpos(strtolower($selection_), 'g') > -1 || !$selection_)) {
                $data = $HTTP_GET_VARS;
            }
            if(isset($HTTP_POST_VARS) && (strpos(strtolower($selection_), 'p') > -1 || !$selection_)) {
                $data = $HTTP_POST_VARS;
            }
        }

        if (isset($data)) {
            if(isset($_SESSION) && (strpos(strtolower($selection_), 's') > -1 || !$selection_)) {
                $data = $_SESSION;
            }
            if(isset($_GET) && (strpos(strtolower($selection_), 'g') > -1 || !$selection_)) {
                $data = $_GET;
            }
            if(isset($_POST) && (strpos(strtolower($selection_), 'p') > -1 || !$selection_)) {
                $data = $_POST;
            }
        }

        if (!isset($data) && function_exists('_QB_SPECIAL_PARAM_DELIMITER')) {
            $data = _QB_SPECIAL_PARAM_DELIMITER();
        }
    }

    return $data;
}
?>
