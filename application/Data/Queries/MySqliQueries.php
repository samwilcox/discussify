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

namespace Discussify\Data\Queries;

// This file may not be accessed directly.
if (!defined('APP_ACTIVE')) {
    \header(isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 forbidden');
    exit(1);
}

/**
 * Class that manages all the various SQL queries for MySQLi servers.
 * 
 * @package Discussify\Data\Queries
 */
class MySqliQueries implements \Discussify\Data\QueriesStructure {
    /**
     * Singleton instance of this class.
     * @var object
     */
    protected static $instance;

    /**
     * Database connection information.
     * @var array
     */
    protected static $connInfo;

    /**
     * Database prefix.
     * @var string
     */
    protected $prefix = '';

    /**
     * Constructor that initializes class properties.
     */
    public function __construct() {
        require (APP_PATH . 'Config.inc.php');
        self::$connInfo = isset($cfg) ? $cfg : [];
        $this->prefix = self::$connInfo['db_prefix'];
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

// Make sure that the functions below do NOT have any white space before each line.

public function selectForCache($data = []) {
return <<<QUERY
SELECT * FROM `{$this->prefix}{$data['table']}`{$data['sorting']}
QUERY;
}

public function selectForCacheCached($data = []) {
return <<<QUERY
/*qc=on*/SELECT * FROM `{$this->prefix}{$data['table']}`{$data['sorting']}
QUERY;
}

public function updateCacheData() {
return <<<QUERY
UPDATE {$this->prefix}stored_cache SET data = '{{toCache}}' WHERE title = '{{table}}'
QUERY;
}



}