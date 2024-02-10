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
 * Class for specifying the database tables to cache and special ordering.
 * 
 * @package Discussify\Data\Cache
 */
class DataCache extends \Discussify\Application {
    /**
     * Singleton instance of this class.
     * @var object
     */
    protected static $instance;

    /**
     * Collection of SQL tables.
     * @var array
     */
    protected static $tables = [];

    /**
     * Collection of various sorting for tables.
     * @var array
     */
    protected static $sorting = [];

    /**
     * Constructor that initializes the tables and sorting collections.
     */
    public function __construct() {
        self::$tables = [

        ];

        self::$sorting = [

        ];
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
}