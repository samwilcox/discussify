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
     * Holds incoming GET and POST data.
     * @var array
     */
    protected static $incoming = [];

    /**
     * Bot information object.
     * @var object
     */
    protected static $bot;

    /**
     * Constructor that sets up the class.
     */
    public function __construct() {
        self::$bot = (object) [
            'name' => '',
            'present' => false
        ];

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
     * Parses the incoming request, detecting controllers and actions.
     */
    private static function parseRequest() {
        foreach ($_GET as $k => $v) self::$incoming[$k] = \filter_var($v, FILTER_SANITIZE_STRING);
        foreach ($_POST as $k => $v) self::$incoming[$k] = \filter_var($v, FILTER_SANITIZE_STRING);

        if (!isset(self::$incoming['controller'])) {
            $scriptPos = \strpos($_SERVER['REQUEST_URI'], \pathinfo($_SERVER['SCRIPT_NAME'] . '.php', PATHINFO_FILENAME)) + \strlen(\pathinfo($_SERVER['SCRIPT_NAME'] . '.php', PATHINFO_FILENAME));
            $scriptQPos = \strpos($_SERVER['REQUEST_URI'], \pathinfo($_SERVER['SCRIPT_NAME'] . '.php?', PATHINFO_FILENAME)) + \strlen(\pathinfo($_SERVER['SCRIPT_NAME'] . '.php?', PATHINFO_FILENAME));

            if (\strlen($_SERVER['REQUEST_URI']) > $scriptQPos + 1) {
                $queryString = \substr($_SERVER['REQUEST_URI'], \strpos($_SERVER['REQUEST_URI'], '?') + 1, \strlen($_SERVER['REQUEST_URI']));
                $bits = \explode('/', $queryString);
                $bits = \array_filter($bits);
                $bits = \array_values($bits);

                self::$incoming['controller'] = isset($bits[0]) ? $bits[0] : 'index';
                self::$incoming['action'] = isset($bits[1]) ? $bits[1] : 'index';

                $bits = \array_slice($bits, 2);
                
                for ($i = 0; $i < \count($bits); $i += 2) {
                    if (isset($bits[$i + 1])) {
                        self::$incoming[$bits[$i]] = $bits[$i + 1];
                    }
                }
            }           
        }

        $bits = null;
    }

    /**
     * Checks whether the current request is a search bot or not.
     */
    private function detectBots() {
        $bots = \unserialize(self::settings()->search_bot_list);

        if ($bots && \is_array($bots)) {
            for ($i = 0; $i < \count($bots); $i++) {
                if (\strpos(' ' . \strtolower(self::agent()->get('agent')), \strtolower($bots[$i])) != false) self::$bot->name = $bots[$i];
            }
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