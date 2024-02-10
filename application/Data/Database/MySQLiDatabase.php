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

namespace Discussify\Data\Database;

// This file may not be accessed directly.
if (!defined('APP_ACTIVE')) {
    \header(isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 forbidden');
    exit(1);
}

/**
 * MySQLi database abstraction class.
 * 
 * @package \Discussify\Data\Database
 */
class MySqliDatabase extends \Discussify\Data\DatabaseStructure {
    /**
     * Singleton instance of this class.
     * @var object
     */
    protected static $instance;

    /**
     * Class properties information object.
     * @var object
     */
    protected static $params;

    /**
     * Constructor that initializes various database related items.
     */
    public function __construct() {
        require (APP_PATH . 'Config.inc.php');

        self::$params = (object) [
            'connInfo' => issset($cfg) ? $cfg : [],
            'dbPrefix' => self::$params->connInfo['db_prefix'],
            'totalQueries' => 0,
            'handle' => null,
            'time' => (object) [
                'start' => null,
                'result' => null
            ],
            'lastQuery' => ''
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

    /**
     * Establishes a connection to the database server.
     */
    public static function connect() {
        self::$params->handle = new \mysqli(
            self::$params->connInfo['db_host'],
            self::$params->connInfo['db_username'],
            self::$params->connInfo['db_password'],
            self::$params->connInfo['db_name'],
            self::$params->connInfo['db_port']
        );

        if (self::$params->handle->connect_error) {
            self::fatalError();
        }

        return self::$params->handle;
    }

    /**
     * Executes a given SQL query on the database.
     * 
     * @param string $query - SQL query statement.
     * @param array $params - Optional associative array of parameters.
     * @return object SQL statement object.
     */
    public static function query($query, $params = null) {
        self::timer('start');
        self::$params->lastQuery = $query;

        if ($params !== null) {
            foreach ($params as $k => $v) {
                $query = \str_replace('{{' . $k . '}}', self::escapeString($v), $query);
            }
        }

        if (!$statement = self::$params->handle->query($query)) {
            self::fatalError();
        }

        self::$params->totalQueries++;
        self::timer('stop');

        return $statement;
    }

    /**
     * Executes a given SQL multi-query statement.
     * 
     * @param string $query - SQL query statement.
     * @param array $params - Optional associative array of paramaters.
     * @return object - SQL statement object.
     */
    public static function multiQuery($query, $params = null) {
        self::time('start');
        self::$params->lastQuery = $query;

        if ($params !== null) {
            foreach ($params as $k => $v) {
                $query = \str_replace('{{' . $k . '}}', self::escapeString($v), $query);
            }
        }

        if (!$statement = self::$params->handle->multi_query($query)) {
            self::fatalError();
        }

        self::$params->totalQueries++;
        self::timer('stop');

        return $statement;
    }

    /**
     * Executes a transaction of queries. If something goes wrong this process can
     * be rolled back.
     * 
     * @param array $queries - Array collection of SQL query statements.
     */
    public static function executeTransaction($queries = []) {
        self::timer('start');
        $totalExecuted = 0;

        try {
            self::$params->handle->autocommit(false);

            foreach ($queries as $item) {
                $query = $item['query'];

                if ($item['params'] != null) {
                    if (\count($item['params']) > 0) {
                        foreach ($item['params'] as $k => $v) {
                            $query = \str_replace('{{' . $k . '}}', self::escapeString($v), $query);
                        }
                    }
                }

                if (!$statement = self::$params->handle->query($query)) {
                    throw new Exception();
                }

                $totalExecuted++;
            }

            self::$params->totalQueries = (self::$params->totalQueries + $totalExecuted);
        } catch (\Exception $e) {
            self::$params->handle->rollBack();
            self::$params->totalQueries = (self::$params->totalQueries - $totalExecuted);
            self::fatalError();
        } finally {
            self::$params->handle->autocommit(true);
        }

        self::timer('stop');
    }

    /**
     * Returns the data from the SQL resource as an object.
     * 
     * @param object $res - SQL resource object.
     * @return object - object of SQL data.
     */
    public static function fetchObject($res) {
        return $res->fetch_object();
    }

    /**
     * Returns the data from the SQL resource as an array.
     * 
     * @param object $res - SQL resource object.
     * @return array - array of SQL data.
     */
    public static function fetchArray($res) {
        return $res->fetch_array();
    }

    /**
     * Returns the data from the SQL resource as an associative array.
     * 
     * @param object $res - SQL resource object.
     * @return array - associative array of SQL data.
     */
    public static function fetchAssoc($res) {
        return $res->fetch_assoc();
    }

    /**
     * Returns the total number of rows returned in the given SQL resource object.
     * 
     * @param object $res - SQL resource object.
     * @return int - Total rows.
     */
    public static function numRows($res) {
        return $res->num_rows;
    }

    /**
     * Frees up memory for the given SQL resource object.
     * 
     * @param object $res - SQL resource object.
     */
    public static function freeResult($res) {
        $res->free_result();
    }

    /**
     * Returns the primary key for the last inserted SQL record.
     * 
     * @return int - Primary key of last inserted record.
     */
    public static function insertId() {
        return self::$params->handle->insert_id;
    }

    /**
     * Returns the total number of rows affected by the last SQL execution.
     * 
     * @return int - Total rows affected.
     */
    public static function affectedRows() {
        return self::$params->handle->affected_rows;
    }

    /**
     * Escapes the string to escape unwanted and dangerous characters.
     * 
     * @param string $str - String to escape.
     * @return string - Escaped string.
     */
    public static function escapeString($str) {
        return $params->handle->real_escape_string($str);
    }

    /**
     * Disconnects from the database.
     */
    public static function disconnect() {
        if (self::$instance) {
            \mysqli_close(self::$params->handle);
            self::$instance = null;
            return;
        }
    }

    /**
     * Starts and/or stops the SQL execution timer.
     * This is used to provide debug information.
     * 
     * @param string $mode - Mode to execute (either start or stop).
     */
    private function timer($mode) {
        switch ($mode) {
            case 'start':
                self::$params->time->start = \microtime(true);
                break;

            case 'stop':
                self::$params->time->result = self::$params->time->result + (\microtime(true) - self::$params->time->start);
                break;
        }
    }

    /**
     * Returns the database prefix.
     * 
     * @return string - Database prefix string.
     */
    public static function dbPrefix() {
        return self::$params->dbPrefix;
    }

    /**
     * Retuens the total executed SQL queries count.
     * 
     * @return int - Total SQL queries executed.
     */
    public static function totalQueries() {
        return self::$params->totalQueries;
    }

    /**
     * Returns the total SQL execution time at point at call to this function.
     * 
     * @return int - Resulting SQL execution time.
     */
    public static function executionTime() {
        return self::$params->time->result;
    }

    /**
     * Helper method to assist with errors that occur within this class.
     */
    private function fatalError() {
        throw new \Discussify\Exceptions\DatabaseException(self::$params->handle->error, self::$params->handle->errno);
    }
}