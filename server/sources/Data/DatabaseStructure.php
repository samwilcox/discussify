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
 * Interface for the proper implementation for the database abstraction classes.
 * 
 * @package Discussify\Data
 */
interface DatabaseStructure {

    /**
     * Establishes a connection to the database server.
     */
    public static function connect();

    /**
     * Executes a given SQL query on the database.
     * 
     * @param string $query - SQL query statement.
     * @param array $params - Optional associative array of parameters.
     * @return object SQL statement object.
     */
    public static function query($query, $params = null);

    /**
     * Executes a transaction of queries. If something goes wrong this process can
     * be rolled back.
     * 
     * @param array $queries - Array collection of SQL query statements.
     */
    public static function executeTransaction($queries = []);

    /**
     * Returns the data from the SQL resource as an object.
     * 
     * @param object $res - SQL resource object.
     * @return object - object of SQL data.
     */
    public static function fetchObject($res);

    /**
     * Returns the data from the SQL resource as an array.
     * 
     * @param object $res - SQL resource object.
     * @return array - array of SQL data.
     */
    public static function fetchArray($res);

    /**
     * Returns the data from the SQL resource as an associative array.
     * 
     * @param object $res - SQL resource object.
     * @return array - associative array of SQL data.
     */
    public static function fetchAssoc($res);

    /**
     * Returns the total number of rows returned in the given SQL resource object.
     * 
     * @param object $res - SQL resource object.
     * @return int - Total rows.
     */
    public static function numRows($res);

    /**
     * Frees up memory for the given SQL resource object.
     * 
     * @param object $res - SQL resource object.
     */
    public static function freeResult($res);

    /**
     * Returns the primary key for the last inserted SQL record.
     * 
     * @return int - Primary key of last inserted record.
     */
    public static function insertId();

    /**
     * Returns the total number of rows affected by the last SQL execution.
     * 
     * @return int - Total rows affected.
     */
    public static function affectedRows();

    /**
     * Escapes the string to escape unwanted and dangerous characters.
     * 
     * @param string $str - String to escape.
     * @return string - Escaped string.
     */
    public static function escapeString($str);

    /**
     * Disconnects from the database.
     */
    public static function disconnect();

    /**
     * Returns the database prefix.
     * 
     * @return string - Database prefix string.
     */
    public static function dbPrefix();

    /**
     * Retuens the total executed SQL queries count.
     * 
     * @return int - Total SQL queries executed.
     */
    public static function totalQueries();

    /**
     * Returns the total SQL execution time at point at call to this function.
     * 
     * @return int - Resulting SQL execution time.
     */
    public static function executionTime();
}