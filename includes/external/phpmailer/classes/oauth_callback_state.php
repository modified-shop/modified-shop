<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

class oauth_callback_state
{
    private const TABLE = 'oauth_callback_state';
    private const LIFETIME = 600;

    public static function install()
    {
        xtc_db_query(
            "CREATE TABLE IF NOT EXISTS `" . self::TABLE . "` (
                `state_hash` char(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
                `session_id` varchar(128) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
                `module` varchar(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
                `expires_at` datetime NOT NULL,
                `consumed_at` datetime DEFAULT NULL,
                PRIMARY KEY (`state_hash`),
                KEY `idx_oauth_callback_state_expires` (`expires_at`)
            )"
        );
    }

    public static function create($module, $sessionId)
    {
        if (!self::isValidModule($module) || !self::isValidSessionId($sessionId)) {
            return false;
        }

        self::install();
        self::cleanup();

        $state = bin2hex(random_bytes(32));
        $stateHash = hash('sha256', $state);
        $sessionHash = hash('sha256', (string)$sessionId);
        $lifetime = (int)self::LIFETIME;

        xtc_db_query(
            "INSERT INTO `" . self::TABLE . "`
                (`state_hash`, `session_id`, `module`, `expires_at`)
             VALUES (
                '" . xtc_db_input($stateHash) . "',
                '" . xtc_db_input($sessionHash) . "',
                '" . xtc_db_input($module) . "',
                DATE_ADD(NOW(), INTERVAL " . $lifetime . " SECOND)
             )"
        );

        return $state;
    }

    public static function consume($state, $sessionId)
    {
        if (!preg_match('/^[a-f0-9]{64}$/', (string)$state)
            || !self::isValidSessionId($sessionId)
            )
        {
            return false;
        }
        if (!self::tableExists()) {
            return false;
        }

        $stateHash = hash('sha256', $state);
        $sessionHash = hash('sha256', (string)$sessionId);
        $stateQuery = xtc_db_query(
            "SELECT `session_id`, `module`
               FROM `" . self::TABLE . "`
              WHERE `state_hash` = '" . xtc_db_input($stateHash) . "'
                AND `session_id` = '" . xtc_db_input($sessionHash) . "'
                AND `expires_at` >= NOW()
                AND `consumed_at` IS NULL"
        );

        if (xtc_db_num_rows($stateQuery) !== 1) {
            return false;
        }

        $stateData = xtc_db_fetch_array($stateQuery);
        if (!self::isValidModule($stateData['module'])
            || !self::isValidSessionId($stateData['session_id'])
            )
        {
            return false;
        }

        xtc_db_query(
            "UPDATE `" . self::TABLE . "`
                SET `consumed_at` = NOW()
              WHERE `state_hash` = '" . xtc_db_input($stateHash) . "'
                AND `session_id` = '" . xtc_db_input($sessionHash) . "'
                AND `expires_at` >= NOW()
                AND `consumed_at` IS NULL"
        );

        if (xtc_db_affected_rows() !== 1) {
            return false;
        }

        return $stateData;
    }

    public static function deleteModuleStates($module)
    {
        if (self::isValidModule($module) && self::tableExists()) {
            xtc_db_query(
                "DELETE FROM `" . self::TABLE . "`
                      WHERE `module` = '" . xtc_db_input($module) . "'"
            );
        }
    }

    private static function cleanup()
    {
        xtc_db_query(
            "DELETE FROM `" . self::TABLE . "`
                  WHERE `expires_at` < NOW()
                     OR `consumed_at` IS NOT NULL"
        );
    }

    private static function tableExists()
    {
        $tableQuery = xtc_db_query(
            "SHOW TABLES LIKE '" . str_replace('_', '\\_', self::TABLE) . "'"
        );

        return xtc_db_num_rows($tableQuery) === 1;
    }

    private static function isValidModule($module)
    {
        return in_array($module, array('google_mail', 'office365_mail'), true);
    }

    private static function isValidSessionId($sessionId)
    {
        return is_string($sessionId)
            && strlen($sessionId) >= 1
            && strlen($sessionId) <= 256
            && preg_match('/^[a-zA-Z0-9,-]+$/', $sessionId);
    }
}
