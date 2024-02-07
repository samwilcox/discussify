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
 * Class that handles all incoming HTTP/HTTPS request data.
 * 
 * @package Discussify\Core
 */
class Request extends \Discussify\Application {
    /**
     * Singleton instance of this class.
     * @var object
     */
    protected static $instance;

    /**
     * Collection of incoming data from _GET and _POST.
     * @var array
     */
    protected static $incoming = [];

    /**
     * Object containing information regarding search bots.
     * @var object
     */
    protected static $bot;

    /**
     * Constructor that sets up the Request class.
     */
    public function __construct() {
        self::$bot = new \stdClass();
        self::parseRequest();
        self::detectBots();
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
     * Parses the incoming request data into the incoming array collection.
     * 
     * TO-DO: Use a better and safer filter, for now its set to FILTER_UNSAFE_RAW.
     */
    private function parseRequest() {
        foreach ($_GET as $k => $v) self::$incoming[$k] = \filter_var($v, FILTER_UNSAFE_RAW);
        foreach ($_POST as $k => $v) self::$incoming[$k] = \filter_var($v, FILTER_UNSAFE_RAW);
    }

    /**
     * Checks whether the current request is a search bot or not.
     */
    private function detectBots() {
        self::$bot->name;
        self::$bot->present = false;
        $bots = \unserialize(self::settings()->searchBotList);

        for ($i = 0; $i < \count($bots); $i++) {
            if (\strpos(' ' . \strtolower(self::agent()->get('agent')), \strtolower($bots[$i])) != false) self::$bot->name = $bots[$i];
        }

        self::$bot->present = \strlen(self::$bot->name) > 0 ? true : false;
    }

    /**
     * Returns the search bot data object.
     * 
     * @return object - Bot information object.
     */
    public static function botData() {
        return self::$bot;
    }

    /**
     * Magic function that sets a new key/value.
     * 
     * @param string $key - Key to set.
     * @param mixed $value - Value to set.
     */
    public function __set($key, $value) {
        self::$incoming[$key] = $value;
    }

    /**
     * Magic function that returns the given kay value.
     * 
     * @param string $key - Key to get. Null if key does not exist.
     */
    public function __get($key) {
        if (\array_key_exists($key, self::$incoming)) return self::$incoming[$key];
        return null;
    }

    /**
     * Magic function that returns whether the given key exists.
     * 
     * @param string $key - Key to check for existance.
     */
    public function __isset($key) {
        if (\array_key_exists($key, self::$incoming)) {
            return true;
        }

        return false;
    }
}