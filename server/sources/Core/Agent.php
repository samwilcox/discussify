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
 * Class for managing the user's browser and connection details.
 * 
 * @package Discussify\Core
 */
class Agent {
    /**
     * Singleton instance of this class.
     * @var object
     */
    protected static $instance;

    /**
     * User agent object.
     * @var object
     */
    protected static $agent;

    /**
     * Constructor that gathers the user details.
     */
    public function __construct() {
        self::$agent = new \stdClass();
        self::$agent->ipAddress = $_SERVER['REMOTE_ADDR'];
        self::$agent->hostname = \gethostbyaddr(self::$agent->ipAddress);
        self::$agent->agent = $_SERVER['HTTP_USER_AGENT'];
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
     * Returns the given user detail.
     * 
     * @param string $item - The user detail to return.
     *                       Options: ip, hostname, or agent.
     * @return string - User detail value.
     */
    public static function get($item) {
        switch ($item) {
            case 'ip':
                return self::$agent->ipAddress;
                break;

            case 'hostname':
                return self::$agent->hostname;
                break;

            case 'agent':
                return self::$agent->agent;
                break;
        }
    }
}