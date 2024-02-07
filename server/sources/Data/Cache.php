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
 * Class that determines the set cache method and returns the appropriate instance.
 * 
 * @package Discussify\Data
 */
class Cache {
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
            switch (CACHE) {
                case true:
                    switch (CACHE_METHOD) {
                        case 'filecache':
                            self::$instance = \Discussify\Data\Cache\FileCache::i();
                            break;

                        case 'dbcache':
                            self::$instance = \Discussify\Data\Cache\DbCache::i();
                            break;

                        case 'sqlcache':
                            require (APP_PATH . 'Config.inc.php');
                            $connInfo = isset($cfg) ? $cfg : [];

                            if ($connInfo['dbDriver'] === 'mysqli') {
                                if (\extension_loaded('mysqlnd')) {
                                    self::$instance = \Discussify\Data\Cache\SqlCache::i();
                                } else {
                                    self::$instance = \Discussify\Data\Cache\NoCache::i();
                                }
                            } else {
                                self::$instance = \Discussify\Data\Cache\NoCache::i();
                            }
                            break;

                        case 'memcache':
                            if (\class_exists('Memcache')) {
                                self::$instance = \Discussify\Data\Cache\MemCache::i();
                            } else {
                                self::$instance = \Discussify\Data\Cache\NoCache::i();
                            }
                            break;

                        case 'rediscache':
                            if (\class_exists('Redis')) {
                                self::$instance = \Discussify\Data\Cache\RedisCache::i();
                            } else {
                                self::$instance = \Discussify\Data\Cache\NoCache::i();
                            }
                            break;
                    }
                    break;

                case false:
                    self::$instance = \Discussify\Data\Cache\NoCache::i();
                    break;
            }
        }

        return self::$instance;
    }
}