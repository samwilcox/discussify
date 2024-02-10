<?php

/**
 * Discussify
 * 
 * Version: 0.1.0
 * 
 * by Sam Wilcox <sam@discussify.com>
 * https://www.discussify.com
 * 
 * User-End License Agreement:
 * https://license.discussify.com
 */

namespace Discussify\Data;

// This file may not be accessed directly.
if (!defined('APP_ACTIVE')) {
    \header(isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 forbidden');
    exit(1);
}

/**
 * Class that determines the appropriate instancve to return for the set database driver.
 * 
 * @package Discussify\Data
 */
class Queries {
    /**
     * Singleton instance of this class.
     * @var object
     */
    protected static $instance;

    /**
     * Returns singleton instance of this class.
     * 
     * @return object - Singleton instance.
     */
    public static function i() {
        if (!self::$instance) {
            require (APP_PATH . 'Config.inc.php');
            $connInfo = isset($cfg) ? $cfg : [];

            switch ($connInfo['db_driver']) {
                case 'mysqli':
                    self::$instance = \Discussify\Data\Queries\MySqliQueries::i();
                    break;
            }
        }

        return self::$instance;
    }
}