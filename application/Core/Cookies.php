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
 * Class for managing the various cookies our application uses.
 * 
 * @package Discussify\Core
 */
class Cookies {
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
        if (!self::$instance) self::$instance = new self;
        return self::$instance;
    }

    /**
     * Creates a new cookie on the user's system.
     * 
     * @param string $name - Cookie name.
     * @param mixed $value - Cookie value.
     * @param int $expires - Optional expiration date for the cookie.
     */
    public static function createCookie($name, $value, $expires = null) {
        \setcookie($name, $value, $expires !== null ? $expires : (\time() * 60 * 60), COOKIE_PATH, COOKIE_DOMAIN);
    }

    /**
     * Deletes the given cookie from the user's system.
     * 
     * @param string $name - Cookie name to delete.
     * @param bool $phpCookie - Optional whether the cookie to delete is a PHP specific cookie.
     */
    public static function deleteCookie($name, $phpCookie = false) {
        unset($_COOKIE[$name]);
        \setcookie($name, '', time() - 3600, $phpCookie ? '' : COOKIE_PATH, $phpCookie ? '' : COOKIE_DOMAIN);
    }

    /**
     * Returns whether the given cookie currently exists.
     * 
     * @param string $name - The cookie name to check.
     * @return bool - True if exists, false otherwise.
     */
    public static function exists($name) {
        if (isset($_COOKIE[$name])) {
            return true;
        }

        return false;
    }

    /**
     * Magic function to return the value for the given cookie.
     * 
     * @param string $name - The cookie name.
     * @return mixed - Value of given cookie; null if doesn't exist.
     */
    public function __get($name) {
        if (self::exists($name)) {
            return $_COOKIE[$name];
        }

        return null;
    }
}