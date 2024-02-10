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

namespace Discussify\Helpers;

// This file may not be accessed directly.
if (!defined('APP_ACTIVE')) {
    \header(isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 forbidden');
    exit(1);
}

/**
 * Helper class that assists with various utility type routines.
 * 
 * @package Discussify\Helpers
 */
class Utils extends \Discussify\Application {
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
     * Determines which API handling class the request should be handled by.
     * All incoming data from React will have three JSON fields:
     * -> route: the routing API class.
     * -> task: the task to perform.
     * -> data: any additional data.
     * 
     * Route indicates which API class to use.
     * 
     * @param mixed $data - The incoming data.
     */
    public static function determineApiRoute($data) {
        switch ($data->route) {
            case 'forums':
                self::apiForums()->handleRequest($data);
                break;

            case 'users':
                self::apiUsers()->handleRequest($data);
                break;
        }
    }
}