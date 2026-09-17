<?php

/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Based on:
   (c) 2001 by the Post-Nuke Development Team - http://www.postnuke.com/
   (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   Original Author of file: Jim McDonald
   Purpose of file: The PostNuke API

   Protects better diverse attempts of Cross-Site Scripting attacks
   thanks to webmedic, Timax, larsneo.
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------*/

//############  KONFIGURATION ##############//
defined('XSS_SEND_LOG') or define('XSS_SEND_LOG', false);
defined('XSS_WRITE_LOG') or define('XSS_WRITE_LOG', true);
defined('XSS_BLACKLIST') or define('XSS_BLACKLIST', true);
defined('XSS_BLACKLIST_TIME') or define('XSS_BLACKLIST_TIME', 3600);
// A single request may have been initiated by someone other than the IP owner.
defined('XSS_AUTO_BLACKLIST') or define('XSS_AUTO_BLACKLIST', false);
//############  KONFIGURATION ##############//

function xss_secure($params_arr, $ip, $type)
{
    foreach ($params_arr as $key => $secvalue) {
        if (is_string($key)) {
            xss_secure_params($key, $ip, $type);
        }
        if (is_array($secvalue)) {
            xss_secure($secvalue, $ip, $type);
        } elseif (is_string($secvalue)) {
            xss_secure_params($secvalue, $ip, $type);
        }
    }
}

function xss_contains_active_content($value)
{
    // Detection supplements, but cannot replace, escaping at the output context.
    $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if (strpos($value, '<') === false) {
        return false;
    }

    preg_match_all('~<\s*/?\s*([a-z][a-z0-9:-]*)(?=[\s/>]|$)([^<>]*)~i', $value, $tags, PREG_SET_ORDER);
    foreach ($tags as $tag) {
        if (in_array(strtolower($tag[1]), array('script', 'object', 'iframe', 'embed', 'applet', 'meta', 'base', 'style', 'svg', 'math'), true)) {
            return true;
        }
        if (preg_match('~(?:^|[\s/])(?:on[a-z][a-z0-9_:-]*|srcdoc)\s*=~i', $tag[2])) {
            return true;
        }
        $attributes = preg_replace('/[\x00-\x20\x7f]/', '', $tag[2]);
        if (
            preg_match('~(?:javascript|vbscript):~i', $attributes)
            || preg_match('~(?:href|src|action|formaction)=["\']?data:~i', $attributes)
        ) {
            return true;
        }
    }
    return false;
}

function xss_secure_params($secvalue, $ip, $type)
{
    if (!in_array($type, array('get', 'post', 'cookie'), true) || !xss_contains_active_content($secvalue)) {
        return;
    }

    if (XSS_WRITE_LOG === true) {
        xss_log_hack_attempt(__FILE__, __LINE__, 'XSS detection', 'Active content in ' . $type . ' parameters.');
    }
    if (XSS_BLACKLIST === true && XSS_AUTO_BLACKLIST === true) {
        xss_add_blacklist($ip);
    }
    header('Location: ' . XSS_BASE . (XSS_BLACKLIST === true ? 'error.html' : 'index.php'));
    exit();
}

function xss_log_hack_attempt(
    $detecting_file = '(no filename available)',
    $detecting_line = 0,
    $hack_type = '(no type given)',
    $message = '(no message given)'
) {
    $method = $_SERVER['REQUEST_METHOD'] ?? '';
    $script = basename($_SERVER['SCRIPT_NAME'] ?? '');
    // Allowlisted metadata keeps credentials and request contents out of every log sink.
    $entry = array(
        'time' => date('c'),
        'type' => $hack_type,
        'message' => $message,
        'file' => basename($detecting_file),
        'line' => (int)$detecting_line,
        'ip' => xtc_get_ip_address(),
        'method' => in_array($method, array('GET', 'POST', 'HEAD', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'), true) ? $method : '',
        'script' => preg_match('/\A[a-z0-9_.-]+\.php\z/i', $script) ? $script : '',
        'get_count' => count($_GET ?? array()),
        'post_count' => count($_POST ?? array()),
        'cookie_count' => count($_COOKIE ?? array())
    );
    return xss_write_log(json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE));
}

function xss_write_log($text)
{
    $written = false;
    if (function_exists('gzencode')) {
        $data = gzencode($text . "\n");
        if ($data !== false) {
            // Append a complete gzip member under one lock to avoid interleaved streams.
            $written = @file_put_contents(XSS_PATH . 'log/xss_attacks_' . date('Y-m-d') . '.log.gz', $data, FILE_APPEND | LOCK_EX) === strlen($data);
        }
    }
    if (!$written) {
        error_log('XSS protection: unable to write the security log.');
    }

    if (XSS_SEND_LOG === true) {
        $directory = realpath(XSS_PATH . 'log');
        $mail_file = $directory !== false && is_writable($directory) ? @tempnam($directory, 'xss_attacks_') : false;
        if ($mail_file === false) {
            error_log('XSS protection: unable to queue the security email.');
            return false;
        }
        $queued = dirname($mail_file) === $directory
                  && @file_put_contents($mail_file, $text, LOCK_EX) === strlen($text)
                  && @rename($mail_file, $mail_file . '.mail');
        if (!$queued) {
            @unlink($mail_file);
            error_log('XSS protection: unable to queue the security email.');
            return false;
        }
    }
    return $written;
}

function xss_normalize_blacklist_ip($ip)
{
    $address = xtc_normalize_ip_address($ip);
    if ($address !== '' || !is_string($ip)) {
        return $address;
    }
    if (preg_match('/\A(?:[0-9]{1,3}\.){3}xxx\z/', $ip)) {
        $address = xtc_normalize_ip_address(substr($ip, 0, -3) . '0');
    } elseif (preg_match('/\A[0-9a-f:]+:xxxx\z/i', $ip)) {
        $address = xtc_normalize_ip_address(substr($ip, 0, -4) . '0');
    }
    return $address !== '' ? ip_clearing($address, 'xxx') : '';
}

function xss_read_blacklist_stream($fp)
{
    $blacklist_arr = array();
    $oldest = time() - XSS_BLACKLIST_TIME;
    while (($entry = @fgetcsv($fp, 4096, ';', '"', '')) !== false) {
        if (count($entry) !== 2) {
            continue;
        }
        $ip = xss_normalize_blacklist_ip($entry[0]);
        $timestamp = filter_var($entry[1], FILTER_VALIDATE_INT, array('options' => array('min_range' => 0)));
        if ($ip !== '' && $timestamp !== false && $timestamp > $oldest) {
            $blacklist_arr[$ip] = $timestamp;
        }
    }
    return feof($fp) ? $blacklist_arr : false;
}

function xss_add_blacklist($ip)
{
    $ip = xtc_normalize_ip_address($ip);
    return $ip !== '' && xss_write_blacklist(array($ip => time()));
}

function xss_write_blacklist($entries, $remove_ips = array())
{
    $fp = @fopen(XSS_PATH . 'log/xss_blacklist.log', 'c+b');
    if ($fp === false) {
        error_log('XSS protection: unable to open the blacklist for writing.');
        return false;
    }

    try {
        if (!@flock($fp, LOCK_EX)) {
            error_log('XSS protection: unable to lock the blacklist.');
            return false;
        }
        // Merge changes into the current file while holding the lock, never a stale snapshot.
        $current = xss_read_blacklist_stream($fp);
        if ($current === false) {
            error_log('XSS protection: unable to read the blacklist.');
            return false;
        }
        foreach ($remove_ips as $ip) {
            unset($current[xss_normalize_blacklist_ip($ip)]);
        }
        foreach ($entries as $ip => $timestamp) {
            $ip = xss_normalize_blacklist_ip($ip);
            $timestamp = filter_var($timestamp, FILTER_VALIDATE_INT, array('options' => array('min_range' => 0)));
            if ($ip !== '' && $timestamp !== false && $timestamp > time() - XSS_BLACKLIST_TIME) {
                $current[$ip] = $timestamp;
            }
        }

        $data = '';
        foreach ($current as $ip => $timestamp) {
            $data .= $ip . ';' . $timestamp . "\r\n";
        }
        if (!@rewind($fp)) {
            error_log('XSS protection: unable to rewind the blacklist.');
            return false;
        }
        $length = strlen($data);
        $offset = 0;
        while ($offset < $length) {
            $written = @fwrite($fp, substr($data, $offset));
            if ($written === false || $written === 0) {
                error_log('XSS protection: unable to write the blacklist.');
                return false;
            }
            $offset += $written;
        }
        if (!@ftruncate($fp, $length) || !@fflush($fp)) {
            error_log('XSS protection: unable to finish writing the blacklist.');
            return false;
        }
        return true;
    } finally {
        @flock($fp, LOCK_UN);
        fclose($fp);
    }
}

function xss_read_blacklist()
{
    $blacklist_file = XSS_PATH . 'log/xss_blacklist.log';
    if (!is_file($blacklist_file)) {
        return array();
    }
    $fp = @fopen($blacklist_file, 'rb');
    if ($fp === false) {
        error_log('XSS protection: unable to open the blacklist for reading.');
        return array();
    }

    try {
        if (!@flock($fp, LOCK_SH)) {
            error_log('XSS protection: unable to lock the blacklist.');
            return array();
        }
        $blacklist_arr = xss_read_blacklist_stream($fp);
        if ($blacklist_arr === false) {
            error_log('XSS protection: unable to read the blacklist.');
            return array();
        }
        return $blacklist_arr;
    } finally {
        @flock($fp, LOCK_UN);
        fclose($fp);
    }
}

define('XSS_PATH', str_replace('\\', '/', dirname(__DIR__)) . '/');
require_once(XSS_PATH . 'inc/set_php_self.inc.php');
require_once(XSS_PATH . 'inc/xtc_get_ip_address.inc.php');
require_once(XSS_PATH . 'inc/ip_clearing.inc.php');

$ssl_proxy = '';
if (
    xtc_is_trusted_proxy($_SERVER['REMOTE_ADDR'] ?? '')
    && isset($_SERVER['HTTP_X_FORWARDED_HOST'], $_SERVER['HTTP_HOST'])
    && preg_match('/\A[a-z0-9.-]+(?::[0-9]+)?\z/i', $_SERVER['HTTP_HOST'])
) {
    $ssl_proxy = '/' . $_SERVER['HTTP_HOST'];
}
define('XSS_BASE', $ssl_proxy . preg_replace('~/+~', '/', str_replace('\\', '/', dirname(set_php_self())) . '/'));

$ip = xtc_get_ip_address();
if (XSS_BLACKLIST === true && $ip !== '') {
    $blacklist_arr = xss_read_blacklist();
    if (isset($blacklist_arr[$ip]) || isset($blacklist_arr[ip_clearing($ip, 'xxx')])) {
        header('Location: ' . XSS_BASE . 'error.html');
        exit();
    }
}

xss_secure($_POST ?? array(), $ip, 'post');
xss_secure($_GET ?? array(), $ip, 'get');
xss_secure($_COOKIE ?? array(), $ip, 'cookie');
