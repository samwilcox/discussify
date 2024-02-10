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
 * Caching class that caches data into files on the server.
 * 
 * @package Discussify\Data\Cache
 */
class FileCache extends \Discussify\Data\Cache\DataCache implements \Discussify\Data\CacheStructure {
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
    private static function sorting($table) {
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
        $cacheDir = CACHE_DIR;
        $dbCacheDir = CACHE_DB_DIR;

        if (\substr($cacheDir, ( \strlen($cacheDir) - 1), \strlen($cacheDir)) === '/') $cacheDir = \substr($cacheDir, 0, (\strlen($cacheDir) - 1));
        if (\substr($cacheDir, 0, 1) === '/') $cacheDir = \substr($cacheDir, 1, \strlen($cacheDir));
        if (\substr($dbCacheDir, ( \strlen($dbCacheDir) - 1), \strlen($dbCacheDir)) === '/') $dbCacheDir = \substr($dbCacheDir, 0, (\strlen($dbCacheDir) - 1));
        if (\substr($dbCacheDir, 0, 1) === '/') $dbCacheDir = \substr($dbCacheDir, 1, \strlen($dbCacheDir));

        $cacheDir = APP_PATH . $cacheDir . '/' . $dbCacheDir . '/';

        if (!file_exists($cacheDir)) throw new \Discussify\Exceptions\CacheException('Cache directory does not exist.');
        if (!is_readable($cacheDir)) throw new \Discussify\Exceptions\CacheException('Cache directory is not readable.');
        if (!is_writable($cacheDir)) throw new \Discussify\Exceptions\CacheException('Cache directory is not writable.');

        foreach (self::$tables as $table) {
            if (!file_exists($cacheDir . $table . '.cache.php')) {
                self::file()->createFile($cacheDir . $table . '.cache.php');
                self::file()->applyPermissions($cacheDir . $table . '.cache.php', 0655);
            }
        }

        foreach (self::$tables as $table) {
            $cacheFile = $cacheDir . $table . '.cache.php';

            unset($records);

            if (\filesize($cacheFile) === 0) {
                $sorting = self::sorting($table);
                $sql = self::getCache($table, $sorting);

                while ($record = self::db()->fetchAssoc($sql)) $records[] = $record;

                self::db()->freeResult($sql);

                $toCache = \json_encode($records ?? '');

                self::file()->writeFile($cacheFile, $toCache);

                self::$cache[$table] = \json_decode($toCache);
            } else {
                self::$cache[$table] = \json_decode(self::file()->readFile($cacheFile));
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

        $cacheDir = CACHE_DIR;
        $dbCacheDir = CACHE_DB_DIR;

        if (\substr($cacheDir, ( \strlen($cacheDir) - 1), \strlen($cacheDir)) === '/') $cacheDir = \substr($cacheDir, 0, (\strlen($cacheDir) - 1));
        if (\substr($cacheDir, 0, 1) === '/') $cacheDir = \substr($cacheDir, 1, \strlen($cacheDir));
        if (\substr($dbCacheDir, ( \strlen($dbCacheDir) - 1), \strlen($dbCacheDir)) === '/') $dbCacheDir = \substr($dbCacheDir, 0, (\strlen($dbCacheDir) - 1));
        if (\substr($dbCacheDir, 0, 1) === '/') $dbCacheDir = \substr($dbCacheDir, 1, \strlen($dbCacheDir));

        if (!file_exists($cacheDir)) throw new \Discussify\Exceptions\CacheException('Cache directory does not exist.');
        if (!is_readable($cacheDir)) throw new \Discussify\Exceptions\CacheException('Cache directory is not readable.');
        if (!is_writable($cacheDir)) throw new \Discussify\Exceptions\CacheException('Cache directory is not writable.');

        $cacheFile = ROOT_PATH . $cacheDir . '/' . CACHE_DB_DIR . '/' . $table . '.cache.php';

        while ($record = self::db()->fetchAssoc($sql)) $records[] = $record;

        self::db()->freeResult($sql);

        $toCache = \json_encode($records ?? '');

        self::file()->writeFile($cacheFile, $toCache);

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
        return (isset(self::$cache[$table]) && \is_array(self::$cache[$table]) && \count(self::$cache[$table]) > 0) ? self::$cache[$table] : [];
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