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

namespace Discussify\Core;

// This file may not be accessed directly.
if (!defined('APP_ACTIVE')) {
    \header(isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 forbidden');
    exit(1);
}

/**
 * Class that stores and manages various temporary application variables.
 * 
 * @package Discussify\Core
 */
class Vars extends \Discussify\Application {
    /**
     * Singleton instance object.
     * @var object
     */
    protected static $instance;

    /**
     * Collection of various application variables.
     * @var array
     */
    protected static $vars = [];

    /**
     * Returns a singleton instance of this class.
     * 
     * @return object - Singleton instance.
     */
    public static function i() {
        if (!self::$instance) self::$instance = new self;
        return self::$instance;
    }

    /**
     * Magic function to set key/value in the application variables.
     * 
     * @param string $key - Key of the variable.
     * @param mixed $value - Value for the key.
     */
    public function __set($key, $value) {
        self::$vars[$key] = $value;
    }

    /**
     * Magic function to get the value for the given key.
     * 
     * @param string $key - Key of variable to return.
     * @return mixed - Value of variable. Null returned if key does not exist.
     */
    public function __get($key) {
        if (\array_key_exists($key, self::$vars)) return self::$vars[$key];
        return null;
    }

    /**
     * Magic function to check if the given key exists.
     * 
     * @param string $key - Key of variable to check.
     * @return bool - returns true if set, false otherwise.
     */
    public function __isset($key) {
        if (\array_key_exists($key, self::$vars)) {
            return true;
        }

        return false;
    }
}