<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // whos_online keeps only the hash, the key is found by hashing the keys on every server of session.save_path
  function get_redis_session_by_hash($session_hash) {
    if (!class_exists('Redis')) {
      return '';
    }

    foreach (explode(',', (string)ini_get('session.save_path')) as $server) {
      $server = trim($server);
      if ($server == '') {
        continue;
      }

      $params = array();
      if (($pos = strpos($server, '?')) !== false) {
        parse_str(substr($server, $pos + 1), $params);
        $server = substr($server, 0, $pos);
      }

      $port = 0;
      if (strpos($server, 'unix://') === 0) {
        $host = substr($server, 7);
      } elseif (substr($server, 0, 1) == '/') {
        $host = $server;
      } else {
        $url = parse_url(((strpos($server, '://') === false) ? 'tcp://' : '').$server);
        if (!isset($url['host'])) {
          continue;
        }
        $host = ((in_array($url['scheme'], array('tls', 'ssl'))) ? $url['scheme'].'://' : '').$url['host'];
        $port = ((isset($url['port'])) ? (int)$url['port'] : 6379);
      }

      // phpredis default prefix of the session keys
      $prefix = ((isset($params['prefix']) && is_string($params['prefix'])) ? $params['prefix'] : 'PHPREDIS_SESSION:');

      try {
        $redis = new Redis();
        if (!$redis->connect($host, $port, ((isset($params['timeout'])) ? (float)$params['timeout'] : 2.5))) {
          continue;
        }
        if (isset($params['auth']) && $params['auth'] !== '') {
          $redis->auth($params['auth']);
        }
        if (isset($params['database']) && (int)$params['database'] > 0) {
          $redis->select((int)$params['database']);
        }

        $redis->setOption(Redis::OPT_SCAN, Redis::SCAN_RETRY);
        $iterator = null;
        while (($keys = $redis->scan($iterator, addcslashes($prefix, '*?[]\\').'*', 1000)) !== false) {
          foreach ($keys as $key) {
            if (xtc_session_hash(substr($key, strlen($prefix))) === $session_hash) {
              $session_data = $redis->get($key);
              $redis->close();

              return (is_string($session_data) ? $session_data : '');
            }
          }
        }
        $redis->close();
      } catch (Exception $e) {
        // an unreachable server only means no cart for this visitor
      }
    }

    return '';
  }
