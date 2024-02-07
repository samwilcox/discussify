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
 * Interface for the proper implementation of the cache class.
 * 
 * @package Discussify\Data
 */
interface CacheStructure {

    /**
     * Builds the cache from the database.
     */
    public static function build();

    /**
     * Updates the given table in the cache.
     * 
     * @param string $table - Database table in which to update the cache for.
     */
    public static function update($table);

    /**
     * Updates multiple given tables in the cache.
     * 
     * @param array $tables - Array collection of tables to update.
     */
    public static function massUpdate($tables = []);

    /**
     * Returns the cache data for the given table.
     * 
     * @param string $table - Database table to get data for.
     * @return object - JSON object of data.
     */
    public static function getData($table);

    /**
     * Returns the cache data for all the provided tables.
     * 
     * @param array $tables - Associative array of tables to get data for.
     * @return object - Object that contains the data.
     */
    public static function massGetData($tables = []);
}