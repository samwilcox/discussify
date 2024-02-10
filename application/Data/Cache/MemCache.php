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

namespace Discussify\Data\Cache;

// This file may not be accessed directly.
if (!defined('APP_ACTIVE')) {
    \header(isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 forbidden');
    exit(1);
}

/**
 * Caching class that caches data using a Memcache server.
 * 
 * @package Discussify\Data\Cache
 */
class MemCache extends \Discussify\Data\DataCache implements \Discussify\Data\CacheStructure {
    /**
     * Singleton instance of this class.
     * @var object
     */
    protected static $instance;

    /**
     * Cache data collection.
     * @var array
     */
    protected static $cache = [];

    /**
     * Memcache server handle object.
     * @var object
     */
    protected static $handle = null;

    /**
     * Constructor that establishes a connection to the Redis server.
     */
    public function __construct() {
        try {
            self::$handle = new Memcache();
            self::$handle->connect(self::settings()->memcache_server_host, self::settings()->memcache_server_port);
        } catch (\Exception $e) {
            throw new \Discussify\Exceptions\CacheException(\sprintf('Could not connect to the configuted Memcache server. Please check your settings. Error: %s', $e));
        }
    }

    /**
     * Returns singleton instance of this class.
     * 
     * @return object - Singleton instance.
     */
    public static function i() {
        if (!self::$instance) self::$instance = new self;
        return self::$instance;
    }

    /**
     * Get's the sorting for the given table.
     * 
     * @param string $table - Table to get sorting for.
     * @return string - sorting string.
     */
    private function sorting($table) {
        $sorting = null;

        foreach (self::$sorting as $k => $v) {
            if ($k == $table) {
                $sorting = $v;
                break;
            }
        }

        return $sorting;
    }

    /**
     * Creates the database object for the given data.
     * 
     * @param string $table - Table to get the database object for.
     * @param string $sorting - The sorting string.
     * @return object - Database object.
     */
    public static function getCache($table, $sorting) {
        return self::db()->query(self::queries()->selectForCache(['table' => $table, 'sorting' => $sorting]));
    } 

    /**
     * Builds the cache from the database.
     */
    public static function build() {
        foreach (self::$tables as $table) {
            unset($records);

            $sorting = self::sorting($table);

            if (self::$handle->get($table) !== null) {
                self::$cache[$table] = \json_decode(self::$handle->get($table));
            } else {
                $sql = self::getCache($table, $sorting);

                while ($record = self::db()->fetchAssoc($sql)) $records[] = $record;

                self::db()->freeResult($sql);

                $toCache =\json_encode($records ?? '');

                self::$handle->set($table, $toCache, 0, self::settings()->memcache_expiration_seconds);
                self::$cache[$table] = \json_decode($toCache);
            }
        }
    }

    /**
     * Updates the given table in the cache.
     * 
     * @param string $table - Database table in which to update the cache for.
     */
    public static function update($table) {
        $sorting = self::sorting($table);
        $sql = self::getCache($table, $sorting);

        while ($record = self::db()->fetchAssoc($sql)) $records[] = $record;

        self::db()->freeResult($sql);

        $toCache =\json_encode($records ?? '');

        self::$handle->set($table, $toCache, 0, self::settings()->memcache_expiration_seconds);
        self::$cache[$table] = \json_decode($toCache);
    }

    /**
     * Updates multiple given tables in the cache.
     * 
     * @param array $tables - Array collection of tables to update.
     */
    public static function massUpdate($tables = []) {
        if (\count($tables) > 0) {
            foreach ($tables as $table) {
                self::update($table);
            }
        }
    }

    /**
     * Returns the cache data for the given table.
     * 
     * @param string $table - Database table to get data for.
     * @return object - JSON object of data.
     */
    public static function getData($table) {
        return (\count(self::$cache[$table] !== null ? self::$cache[$table] : []) > 0) ? self::$cache[$table] : [];
    }

    /**
     * Returns the cache data for all the provided tables.
     * 
     * @param array $tables - Associative array of tables to get data for.
     * @return object - Object that contains the data.
     */
    public static function massGetData($tables = []) {
        $retVal = new \stdClass();

        if (\is_array($tables) && \count($tables) > 0) {
            foreach ($tables as $name => $table) {
                if (\count(self::$cache[$table] !== null ? self::$cache[$table] : []) > 0) {
                    $retVal->$name = self::$cache[$table];
                } else {
                    $retVal->$name = [];
                }
            }
        }

        return $retVal;
    }
}