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

namespace Discussify\Api;

// This file may not be accessed directly.
if (!defined('APP_ACTIVE')) {
    \header(isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 forbidden');
    exit(1);
}

/**
 * Class for handling users related API requests.
 * 
 * @package Discussify\Api
 */
class Users extends \Discussify\Application {
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
     * Handles the incoming API request.
     * 
     * @param mixed $data - The incoming data from the request.
     */
    public static function handleRequest($data) {
        switch ($data->task) {
            case 'get_username':
                return self::getUsername($data);
                break;
        }
    }

    /**
     * Returns the specified member's username.
     * 
     * @param mixed $data - The incoming API data.
     */
    private function getUsername($data) {
        
    }
}